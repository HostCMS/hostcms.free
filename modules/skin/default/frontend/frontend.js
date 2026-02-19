/* eslint-disable */
(function(hQuery) {
	'use strict';

	/**
	 * jQuery Cookie plugin (Optimized)
	 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
	 * Dual licensed under the MIT and GPL licenses:
	 */
	hQuery.cookie = function(key, value, options) {
		// key and at least value given, set cookie...
		if (arguments.length > 1 && String(value) !== "[object Object]") {
			options = hQuery.extend({}, options);

			if (value === null || value === undefined) {
				options.expires = -1;
			}

			if (typeof options.expires === 'number') {
				const days = options.expires,
					t = options.expires = new Date();
				t.setDate(t.getDate() + days);
			}

			value = String(value);

			return (document.cookie = [
				encodeURIComponent(key), '=',
				options.raw ? value : cookie_encode(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '',
				options.path ? '; path=' + options.path : '',
				options.domain ? '; domain=' + options.domain : '',
				options.secure ? '; secure' : ''
			].join(''));
		}

		// key and possibly options given, get cookie...
		options = value || {};
		const decode = options.raw ? function(s) { return s; } : decodeURIComponent;
		const result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie);
		return result ? decode(result[1]) : null;
	};

	function cookie_encode(string) {
		// Optimized regex replacement
		return encodeURIComponent(string).replace(/(%7B|%7D|%3A|%22|%23|%5B|%5D)/g, decodeURIComponent);
	}

	// Helper to extract data info without expensive jQuery Selectors
	function getEditableData(element, prefix) {
		const regex = new RegExp('^' + prefix);
		if (!element.attributes) return null;

		for (let i = 0; i < element.attributes.length; i++) {
			const attr = element.attributes[i];
			if (regex.test(attr.name)) {
				const id = attr.name.substr(prefix.length).replace(/-(.)/g, function($0, $1) {
					return $1;
				});
				return {
					'id': id,
					'name': attr.value,
					'value': element.innerHTML,
					'position': element.dataset.position || 0,
					'prefix': element.dataset.prefix || ''
				};
			}
		}
		return null;
	}

	hQuery.extend({
		lockPanel: function(object) {
			const $object = hQuery(object),
				$parentDiv = $object.closest('.hostcmsInformationPanel'), // closest быстрее parents
				$icon = $object.find('i#hostcmsLock'),
				cookieVal = hQuery.cookie('lock-panel'),
				isLocked = cookieVal ? parseInt(cookieVal) : 0;

			if (!isLocked && $icon.hasClass('fa-lock-open')) {
				hQuery.cookie('lock-panel', 1, { expires: 365 });
			} else if (isLocked && $icon.hasClass('fa-lock')) {
				hQuery.cookie('lock-panel', 0, { expires: 365 });
			}

			$icon.toggleClass('fa-lock-open fa-lock');
			$parentDiv.toggleClass('hostcms-panel-opened hostcms-panel-closed');
		},
		hostcmsEditable: function(settings) {
			settings = hQuery.extend({
				save: function(item, settings) {
					let value;
					const type = item.attr('hostcms:type');

					switch (type) {
						case 'textarea':
						case 'wysiwyg':
							value = item.html();
							break;
						case 'input':
						default:
							value = item.text();
					}

					const data = {
						'id': item.attr('hostcms:id'),
						'entity': item.attr('hostcms:entity'),
						'field': item.attr('hostcms:field'),
						'value': value,
						'_': Date.now() // Faster than new Date().getTime()
					};

					hQuery.ajax({
						context: hQuery('<img>').addClass('img_line').prop('src', '/modules/skin/default/frontend/images/ajax-loader.gif').appendTo(item),
						url: settings.path,
						type: 'POST',
						data: data,
						dataType: 'json',
						success: function() { this.remove(); }
					});
				},
				blur: function(jEditInPlace) {
					const item = jEditInPlace.prevAll('.hostcmsEditable').first(), // .first() is clearer
						type = item.attr('hostcms:type');

					switch (type) {
						case 'textarea':
						case 'wysiwyg':
							item.html(jEditInPlace.val());
							break;
						case 'input':
						default:
							item.text(jEditInPlace.val());
					}

					item.css('display', '');
					jEditInPlace.remove();
					settings.save(item, settings);
				}
			}, settings);

			hQuery('*[hostcms\\:id]').addClass('hostcmsEditable');

			// Cached selector for document
			const $doc = hQuery(document);

			$doc.on("click", "*[hostcms\\:id]", function(event) {
				const $object = hQuery(this);

				if (!event.isDefaultPrevented()) {
					if (!$object.data('timer')) {
						$object.data('timer', setTimeout(function() {
							$object.data('timer', null);
							const href = $object.attr('href');
							if (href !== undefined) {
								window.location = href;
							}
						}, 500));
					}
				}
				event.preventDefault();
			});

			$doc.on("dblclick", "*[hostcms\\:id]", function(event) {
				const $object = hQuery(this);

				clearTimeout($object.data('timer'));
				$object.data('timer', null);

				const data = {
					'id': $object.attr('hostcms:id'),
					'entity': $object.attr('hostcms:entity'),
					'field': $object.attr('hostcms:field'),
					'loadValue': true,
					'_': Date.now()
				};

				hQuery.ajax({
					context: $object,
					url: settings.path,
					type: 'POST',
					data: data,
					dataType: 'json',
					success: function(result) {
						const $object = hQuery(this);

						if (result.status !== 'Error') {
							const type = $object.attr('hostcms:type');
							let jEditInPlace;

							switch (type) {
								case 'textarea':
								case 'wysiwyg':
									jEditInPlace = hQuery('<textarea>');
									break;
								case 'input':
								default:
									jEditInPlace = hQuery('<input class="hostcmsEditableInput">').prop('type', 'text');
							}

							if (type !== 'wysiwyg') {
								jEditInPlace.on('blur', function() {
									settings.blur(jEditInPlace);
								});
							}

							const w = $object.width(), h = $object.height();

							jEditInPlace
								.val(result.value)
								.prop('name', $object.parent().prop('id'))
								.height(h)
								.width(w)
								.insertAfter($object)
								.on('keydown', function(e) {
									if (e.keyCode === 13) {
										e.preventDefault();
										this.blur();
									}
									if (e.keyCode === 27) { // ESC
										e.preventDefault();
										const input = hQuery(this);
										const $obj = input.prev();
										$obj.css('display', '');
										input.remove();
									}
								})
								.focus();

							if (type === 'wysiwyg') {
								setTimeout(function() {
									const aCss = [];
									hQuery("head > link[rel='stylesheet']").each(function() {
										const linkHref = this.getAttribute('href');
										if (linkHref && linkHref !== 'undefined') {
											aCss.push(linkHref);
										}
									});
									wysiwyg.frontendDbl(jEditInPlace, settings, aCss);
								}, 300);
							}

							$object.css('display', 'none');
						} else {
							$object.removeClass('hostcmsEditable');
						}
					}
				});
			});
		},
		createWindow: function(settings) {
			settings = hQuery.extend({
				open: function(event, ui) {
					const uiDialog = hQuery(this).parent('.ui-dialog');
					// Avoid Layout Thrashing by reading dims first
					const w = uiDialog.width();
					const h = uiDialog.height();
					uiDialog.width(w).height(h);

					hQuery(".xmlWindow").children(".ui-dialog-titlebar").append("<button id='btnMaximize' class='ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-maximize' type='button' role='button' title='Maximize'><span class='ui-button-icon-primary ui-icon ui-icon-newwin'></span><span class='ui-button-text'>Maximize</span></button>");

					let max = false;
					let original_dims = { w: w, h: h, pos: uiDialog.position() };
					const textareaBlock = uiDialog.find('.dynamicXML-output');
					// Cache parent
					const $parentWin = textareaBlock.closest('div.hostcmsWindow');
					const textareaBlockOuterHeight = $parentWin.outerHeight(); // Read

					hQuery("#btnMaximize")
						.hover(function() { hQuery(this).addClass('ui-state-hover'); },
							function() { hQuery(this).removeClass('ui-state-hover'); })
						.click(function(e) {
							if (max === false) {
								// Maximize
								max = true;
								hQuery('body').addClass('bodyMaximize');

								const winH = hQuery(window).height();
								const winW = hQuery(window).width();
								const scrollT = hQuery(window).scrollTop();

								textareaBlock.height(winH - 25 + 'px');
								$parentWin.height('');

								// Save current state before animating
								original_dims.h = uiDialog.height();
								original_dims.w = uiDialog.width();
								original_dims.pos = uiDialog.position();

								uiDialog.animate({
									height: winH - 3 + "px",
									width: winW + "px",
									top: scrollT,
									left: 0
								}, 200);
							} else {
								// Restore
								max = false;
								hQuery('body').removeClass('bodyMaximize');

								textareaBlock.height(textareaBlockOuterHeight + 'px');
								$parentWin.height(textareaBlockOuterHeight + 'px');

								uiDialog.animate({
									height: original_dims.h + "px",
									width: original_dims.w + "px",
									top: original_dims.pos.top + "px",
									left: original_dims.pos.left + "px"
								}, 200);
							}
							return false;
						});
				},
				close: function(event, ui) {
					hQuery(this).dialog('destroy').remove();
					hQuery('body').removeClass('bodyMaximize');
				}
			}, settings);

			let windowCounter = hQuery('body').data('windowCounter') || 0;
			hQuery('body').data('windowCounter', windowCounter + 1);

			return hQuery('<div>')
				.addClass("hostcmsWindow")
				.attr("id", "Window" + windowCounter)
				.appendTo(document.body)
				.dialog(settings);
		},
		showWindow: function(windowId, content, settings) {
			settings = hQuery.extend({
				autoOpen: false,
				resizable: true,
				draggable: true,
				minimize: false,
				closable: true,
				dialogClass: 'xmlWindow'
			}, settings);

			let jWin = hQuery('#' + windowId);

			if (!jWin.length) {
				jWin = hQuery.createWindow(settings)
					.attr('id', windowId)
					.html(content);
			}

			jWin.dialog('open');
			return jWin;
		},
		openWindow: function(settings) {
			const $win = hQuery(window);
			settings = hQuery.extend({
				width: $win.width() * 0.7,
				height: $win.height() * 0.7,
				path: '',
				additionalParams: ''
			}, settings);

			const jDivWin = hQuery.createWindow(settings);
			let cmsrequest = settings.path;

			if (settings.additionalParams && settings.additionalParams.trim() !== '') {
				cmsrequest += '?' + settings.additionalParams;
			}

			jDivWin
				.append('<iframe src="' + cmsrequest + '&hostcmsMode=blank"></iframe>')
				.dialog('open');
			return jDivWin;
		},
		changeActive: function(settings) {
			settings = hQuery.extend({ path: '' }, settings);
			hQuery.ajax({
				context: settings.goal,
				url: settings.path + '&hostcmsAction=SHOW_DESIGN',
				type: 'POST',
				data: { '_': Date.now() },
				dataType: 'json',
				success: hQuery.refreshSectionCallback
			});
		},
		refreshSection: function(id) {
			hQuery.ajax({
				context: hQuery('#hostcmsSection' + id),
				url: '/template-section.php?hostcmsAction=SHOW_DESIGN&template_section_id=' + id,
				type: 'POST',
				data: { '_': Date.now() },
				dataType: 'json',
				success: hQuery.refreshSectionCallback
			});
		},
		deleteWidget: function(settings) {
			settings = hQuery.extend({ path: '' }, settings);
			hQuery.ajax({
				context: settings.goal,
				url: settings.path + '&hostcmsAction=SHOW_DESIGN',
				type: 'POST',
				data: { '_': Date.now() },
				dataType: 'json',
				success: hQuery.refreshSectionCallback
			});
		},
		refreshSectionCallback: function(result) {
			const newDiv = hQuery(result);

			// Warning: modifying document.write is dangerous
			const oldWrite = document.write;
			document.write = function(str) {
				newDiv.find('script').first().after(str);
			};

			this.replaceWith(newDiv);
			document.write = oldWrite; // Restore safety

			hQuery(".hostcmsPanel,.hostcmsSectionPanel,.hostcmsSectionWidgetPanel", newDiv)
				.draggable({ containment: "document" });

			hQuery('*[hostcms\\:id]').addClass('hostcmsEditable');
			frontendInit(newDiv);
		},
		sortWidget: function() {
			hQuery(".hostcmsSection").sortable({
				items: ".hostcmsSectionWidget",
				handle: ".drag-handle",
				stop: function(event, ui) {
					const data = hQuery(this).sortable("serialize");
					hQuery.ajax({
						data: data,
						type: "POST",
						url: "/template-section.php?hostcmsAction=SHOW_DESIGN",
					});
				}
			});
		},
		toggleSlidePanel: function(ids) {
			hQuery('#right_panel').remove();
			const $body = hQuery('.backendBody .template-settings #slidepanel-settings');

			const options = {
				id: 'right_panel',
				html: hQuery('.scroll-template-settings').html(),
				position: 'right',
				className: 'bootstrap-iso template-settings template-section-settings',
				animateResizePercent: window.innerWidth < 992 ? '90%' : '400px',
				onCreated: function(element) {
					hQuery.ajax({
						data: { 'showLessPanel': 1, 'ids': ids },
						type: "POST",
						dataType: 'json',
						url: hostcmsBackend + '/template/index.php',
						success: function(result) {
							if (result.status === 'success') {
								element.find('.slidepanel-body').append('<div class="background-wrapper">' + result.html + '</div>');
								$body.addClass('hidden');
							}
						}
					});
				},
				onClosed: function(element) {
					$body.removeClass('hidden');
				}
			};

			return new hostcmsSlidepanel(options).show();
		},
		reloadStylesheets: function() {
			const timestamp = Date.now();
			hQuery('link[rel="stylesheet"]').each(function() {
				const href = this.href;
				if (!href) return;

				const newLink = hQuery('<link rel="stylesheet">')
					.attr('href', href.replace(/&\d{9,}|$/, (href.indexOf('?') >= 0 ? '&' : '?') + timestamp));

				const $oldLink = hQuery(this);
				$oldLink.after(newLink);
				setTimeout(function() { $oldLink.remove(); }, 500);
			});
		},
		sendLessVariable: function() {
			const object = hQuery(this);
			hQuery.ajax({
				data: { name: object.attr('name'), value: object.val(), template: object.data('template') },
				type: "POST",
				url: "/template-less.php",
				success: function(json) {
					const result = JSON.parse(json);
					if (result === 'OK') {
						hQuery.reloadStylesheets();
					} else {
						frontendNotify(result, 'top-left', '5000', 'danger', 'fa-gear', true, false);
					}
				}
			});
		},
		showDesignPanel: function(template_section_lib_id, field, attributes) {
			attributes = attributes || {};
			const panelId = 'panel' + template_section_lib_id;
			hQuery('#' + panelId).remove();

			const options = {
				id: panelId,
				className: 'bootstrap-iso template-settings template-section-lib-settings',
				attributes: attributes,
				field: field,
				animateResizePercent: window.innerWidth < 992 ? '90%' : '450px',
				onCreated: function(element) {
					hQuery.ajax({
						data: { 'showDesignPanel': 1, 'template_section_lib_id': template_section_lib_id, 'field': field, 'attributes': attributes },
						type: "POST",
						dataType: 'json',
						url: hostcmsBackend + '/template/index.php',
						success: function(result) {
							if (result.status === 'success') {
								element.find('.slidepanel-body').append(result.html);
							}
						}
					});
				}
			};

			return new hostcmsSlidepanel(options).show();
		},
		changePreset: function(object, template_section_lib_id) {
			if (!template_section_lib_id) return;

			const $object = hQuery(object),
				background = $object.data('background') || 'white',
				$panel = hQuery('#panel' + template_section_lib_id),
				design_id = $panel.data('design-id') || '',
				aSplit = design_id.split('_', 3);

			let field = aSplit[1] + (typeof aSplit[2] !== 'undefined' ? '_' + aSplit[2] : '') || '',
				$widget = (design_id !== '') ? hQuery('#' + design_id) : hQuery('#hostcmsSectionWidget-' + template_section_lib_id);

			if (typeof field === 'undefined' || field === 'undefined') {
				field = '';
			}

			if (!$widget.hasClass(background)) {
				$panel.find('.background-active').removeClass('background-active');
				$object.addClass('background-active');

				$widget.removeAttr('data-bg');
				// Regex in callback is cleaner but make sure not to create new RegExp in loops if possible
				$widget.removeClass(function(index, className) {
					return (className.match(/(^|\s)preset-\S+/g) || []).join(' ');
				});

				$widget.attr('data-bg', background).addClass(background);
			} else {
				$panel.find('.background-active').removeClass('background-active');
				$widget.removeAttr('data-bg').removeClass(background);
			}

			hQuery.ajax({
				data: { 'changePreset': 1, 'template_section_lib_id': template_section_lib_id, 'field': field, 'class': $widget.attr('class') },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status === 'success') {
						const aProperties = ['background', 'color', 'opacity'];
						for (let i = 0; i < aProperties.length; i++) {
							hQuery.clearProperty(aProperties[i]);
						}
					}
				}
			});
		},
		showFontsPanel: function(template_section_lib_id) {
			template_section_lib_id = template_section_lib_id || 0;
			const panelId = 'font_panel' + template_section_lib_id;
			hQuery('#' + panelId).remove();

			const options = {
				id: panelId,
				className: 'bootstrap-iso template-settings template-section-settings',
				animateResizePercent: window.innerWidth < 992 ? '90%' : '800px',
				onCreated: function(element) {
					hQuery.ajax({
						url: hostcmsBackend + '/template/index.php',
						data: { 'showFontsPanel': 1, 'template_section_lib_id': template_section_lib_id },
						type: "POST",
						dataType: 'json',
						success: function(result) {
							if (result.status === 'success') {
								element.find('.slidepanel-body').append(result.html);

								const current_font = hQuery('#panel' + template_section_lib_id).find('div[data-property="font"]').data('current-font') || '';
								if (current_font !== '') {
									hQuery('#' + panelId).find('div[data-font="' + current_font + '"]').addClass('active');
								}
							}
						}
					});
				},
				onClosed: function(element) {
					hQuery('head link[rel="stylesheet"][data-type="panel"]').remove();
				}
			};

			return new hostcmsSlidepanel(options).show();
		},
		changeFont: function(object, template_section_lib_id) {
			if (!template_section_lib_id) return;

			const $object = hQuery(object),
				$widget = hQuery('#hostcmsSectionWidget-' + template_section_lib_id),
				font = $object.data('font') || '',
				font_name = $object.data('font-name') || '';

			hQuery('#font_panel' + template_section_lib_id).find('.font').removeClass('active');

			$widget.removeClass(function(index, className) {
				return (className.match(/(^|\s)h-font-\S+/g) || []).join(' ');
			});

			if (font !== '') {
				$widget.addClass(font);

				const aSplit = font.split('h-font-', 2),
					fontName = aSplit[1] || '';

				if (fontName !== '') {
					// Optimized selector
					const isConnected = hQuery('head link[rel="stylesheet"]:not([data-type="panel"])[href*="' + fontName + '/' + fontName + '.css"]').length > 0;

					if (!isConnected) {
						hQuery('head').append('<link rel="stylesheet" href="/hostcmsfiles/fonts/' + fontName + '/' + fontName + '.css" type="text/css" />');
					}

					const $panelProp = hQuery('#panel' + template_section_lib_id).find('div[data-property="font"]');
					$panelProp.html(font_name).data('current-font', font);
					$object.addClass('active');
				}
			}

			hQuery.ajax({
				data: { 'changeFont': 1, 'template_section_lib_id': template_section_lib_id, 'class': $widget.attr('class') },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php'
			});
		},
		showAllBackgrounds: function(object) {
			const $obj = hQuery(object);
			$obj.closest('.background-block').find('.background-item.hidden').removeClass('hidden');
			$obj.remove();
		},
		clearProperty: function(property) {
			hQuery('input[data-property="' + property + '"]').closest('.background-block').find('.fa-circle-xmark').click();
		},
		refreshStyle: function(object, value) {
			const $object = hQuery(object),
				template_section_lib_id = +$object.data('id') || 0;

			value = value || $object.val();

			if (template_section_lib_id) {
				const type = $object.data('type') || '',
					property = $object.data('property') || '',
					$panel = hQuery('#panel' + template_section_lib_id),
					design_id = $panel.data('design-id') || '',
					aSplit = design_id.split('_', 3);

				let $widget = (design_id !== '') ? hQuery('#' + design_id) : hQuery('#hostcmsSectionWidget-' + template_section_lib_id),
					field = aSplit[1] + (typeof aSplit[2] !== 'undefined' ? '_' + aSplit[2] : '') || '';

				switch (type) {
					case 'colorpicker-range':
						const name = $object.data('name') || '';
						if (name !== '') {
							const aParts = [];
							hQuery('[data-type="' + type + '"][data-name^="' + name + '"]').each(function() {
								const val = this.value;
								if (val !== '') aParts.push(val);
							});

							value = aParts.length === 3 ?
								"linear-gradient(" + aParts[2] + "deg," + aParts[0] + "," + aParts[1] + ")" :
								'';
						}
						break;
				}

				if (value === 'auto') value = '';
				if (typeof field === 'undefined' || field === 'undefined') field = '';

				$widget.css(property, value !== '' ? value : '');

				hQuery('body, .slidepanel').css('cursor', 'wait');

				hQuery.ajax({
					url: hostcmsBackend + '/template/index.php',
					data: { 'refreshStyle': 1, 'template_section_lib_id': template_section_lib_id, 'type': type, 'field': field, 'property': property, 'value': value },
					type: "POST",
					dataType: 'json',
					success: function(result) {
						hQuery('body, .slidepanel').css('cursor', 'default');

						if (type === 'user_css') {
							$widget.find('style[data-type="user_css"]').remove();
							$widget.append('<style data-type="user_css">' + value + '</style>');
						}
					}
				});
			}
		},
		updateMinicolors: function() {
			hQuery.refreshStyle(this);
		},
		changeRange: function(object, measure) {
			const $object = hQuery(object);
			measure = measure || '';
			const unset = +$object.data('unset') || 0;
			let value = $object.val() + measure;

			if (unset && $object.val() == 0) {
				value = 'auto';
			}

			$object.closest('.range-wrapper').find('.range-value').text(value);

			clearTimeout(window.range_timeout);

			hQuery('body, .slidepanel').css('cursor', 'wait');

			window.range_timeout = setTimeout(function() {
				hQuery.refreshStyle(object, value);
			}, 1000);
		},
		clearBlock: function(object, type, default_value, measure) {
			const $object = hQuery(object),
				$parent = $object.closest('.background-block');

			$parent.find('[data-type]').each(function() {
				const $input = hQuery(this),
					input_type = $input.data('type') || '';

				if (input_type === 'colorpicker' || (input_type !== 'colorpicker-range' && $input.hasClass('colorpicker'))) {
					$input.minicolors('value', default_value);
				} else if (input_type === 'colorpicker-range') {
					if ($input.hasClass('colorpicker')) {
						$input.minicolors('value', '');
					} else {
						$input.val(default_value);
						$input.closest('.range-wrapper').find('.range-value').text(default_value + measure);
					}
				} else if (input_type === 'font') {
					$input.html('');
					$input.data('current-font', '');
					hQuery.changeFont($input, $input.data('id'));
				} else {
					$input.val(default_value);
					if (input_type === 'range') {
						$input.closest('.range-wrapper').find('.range-value').text(default_value + measure);
					}
				}

				if (input_type !== 'font') {
					hQuery.refreshStyle(this, default_value + measure);
				}
			});
		},
		changeDevice: function(object, type) {
			const $object = hQuery(object);
			let width = 0, height = 0;

			hQuery('.top-panel .icons > span').removeClass('active');

			switch (type) {
				case 'tablet': width = 810; height = 1080; break;
				case 'tablet-wide': width = 1080; height = 810; break;
				case 'mobile': width = 390; height = 844; break;
				case 'mobile-wide': width = 844; height = 390; break;
			}

			const $siteFrame = hQuery('#siteFrame');
			if (width) $siteFrame.width(width); else $siteFrame.css('width', '');
			if (height) $siteFrame.height(height); else $siteFrame.css('height', '');

			$object.addClass('active');
		},
		showWidgetPanel: function(template_section_id, template_section_lib_id) {
			template_section_lib_id = template_section_lib_id || 0;
			const panelId = 'panel' + template_section_id;
			hQuery('#' + panelId).remove();

			const options = {
				id: panelId,
				className: 'bootstrap-iso template-settings template-section-settings',
				animateResizePercent: window.innerWidth < 992 ? '90%' : '800px',
				onCreated: function(element) {
					hQuery.ajax({
						url: hostcmsBackend + '/template/index.php',
						data: { 'showWidgetPanel': 1, 'template_section_id': template_section_id, 'template_section_lib_id': template_section_lib_id },
						type: "POST",
						dataType: 'json',
						success: function(result) {
							if (result.status === 'success') {
								element.find('.slidepanel-body').append(result.html);
								setTimeout(function() {
									element.find('.dirs-wrapper .dir-item:first-child').click();
								}, 500);
							}
						}
					});
				}
			};

			return new hostcmsSlidepanel(options).show();
		},
		showWidgets: function(object, template_section_id, template_section_lib_id, lib_dir_id) {
			const $panel = hQuery('#panel' + template_section_id);
			const lib_wrapper = $panel.find('.libs-wrapper');

			$panel.find('.dir-item').removeClass('active');
			$panel.find('.dir-item[data-dir-id="' + lib_dir_id + '"]').addClass('active');

			lib_wrapper.empty();

			hQuery.ajax({
				url: hostcmsBackend + '/template/index.php',
				data: { 'showWidgets': 1, 'template_section_id': template_section_id, 'template_section_lib_id': template_section_lib_id, 'lib_dir_id': lib_dir_id },
				type: "POST",
				dataType: 'json',
				success: function(result) {
					if (result.status === 'success') {
						lib_wrapper.append(result.html);
					}
				}
			});
		},
		addWidget: function(object, template_section_id, template_section_lib_id, lib_id) {
			const $object = hQuery(object),
				$parent = $object.closest('.lib-item');

			hQuery.ajax({
				context: hQuery('<img>').addClass('img_line').prop('src', '/modules/skin/default/frontend/images/ajax-loader.gif').appendTo($parent),
				data: { 'addWidget': 1, 'template_section_id': template_section_id, 'template_section_lib_id': template_section_lib_id, 'lib_id': lib_id },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status === 'success') {
						hQuery.refreshSection(template_section_id);
						$parent.find('.img_line').remove();
					}
				}
			});
		},
		updateSettings: function(selector, parent, template_section_lib_id, template_section_id, event) {
			if (event) event.preventDefault();

			const FormNode = hQuery('form.settings' + template_section_lib_id),
				data = { 'saveSettings': 1, 'template_section_lib_id': template_section_lib_id },
				path = FormNode.attr('action');

			FormNode.ajaxSubmit({
				context: hQuery('<img>').addClass('img_line').prop('src', '/modules/skin/default/frontend/images/ajax-loader.gif').appendTo(parent),
				data: data,
				url: path,
				type: 'POST',
				dataType: 'json',
				cache: false,
				success: function(result) {
					if (result.status === 'success') {
						parent.find('.img_line').remove();
						parent.append('<i class="fa-solid fa-check-circle process-status palegreen"></i>');
						hQuery.refreshSection(template_section_id);

						setTimeout(function() {
							parent.find('.process-status').remove();
						}, 2000);
					}
				}
			});
		},
		refreshSettingsBlock: function(template_section_lib_id) {
			hQuery.ajax({
				data: { 'refreshSettingsBlock': 1, 'template_section_lib_id': template_section_lib_id },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status === 'success') {
						const $parent = hQuery('form.settings' + template_section_lib_id).parent();
						$parent.empty();
						$parent.append(result.html);
					}
				}
			});
		},
		saveSettings: function(object, template_section_lib_id, template_section_id, event) {
			const $object = hQuery(object);
			hQuery.updateSettings("#settings :input", $object.parents('.button-wrapper'), template_section_lib_id, template_section_id, event);
		},
		showSettingsCrmIcons: function(object, input_name, template_section_lib_id) {
			hQuery('.template-settings.template-section-lib-settings').find('#settingsCrmIcons').remove();
			const panelId = 'settingsCrmIcons' + template_section_lib_id;

			const options = {
				id: panelId,
				className: 'bootstrap-iso template-settings template-section-settings',
				animateResizePercent: window.innerWidth < 992 ? '90%' : '800px',
				onCreated: function(element) {
					hQuery.ajax({
						url: hostcmsBackend + '/template/index.php',
						data: { 'showSettingsCrmIcons': 1, 'input_name': input_name, 'template_section_lib_id': template_section_lib_id },
						type: "POST",
						dataType: 'json',
						success: function(result) {
							if (result.status === 'success') {
								element.find('.slidepanel-body').append(result.html);
							}
						}
					});
				}
			};

			return new hostcmsSlidepanel(options).show();
		},
		copySettingsRow: function(object, lib_property_id) {
			const $object = hQuery(object),
				$original = $object.closest('.settings-row-item-wrapper'),
				$clone = $original.clone(true);

			let count = hQuery('.settings-row-item-wrapper').filter(function() {
				return hQuery(this).data('lib-property-id') === lib_property_id;
			}).length;

			$clone.find('[name]').each(function() {
				const name = this.getAttribute('name');
				if (name) {
					this.setAttribute('name', name.replace(/\[\d+\]/g, '[' + count + ']'));
				}
			});

			$clone.find('input[type="text"], textarea, select').val('');
			$original.after($clone);
		},
		selectSettingsCrmIcon: function(object, input_name, template_section_lib_id) {
			const $object = hQuery(object),
				value = $object.data('value'),
				$inputSelector = hQuery('input[name = "' + input_name + '"]'),
				$wrapper = $inputSelector.closest('.settings-row-icon-wrapper'),
				input_val = $inputSelector.val();

			let escaped_val = input_val.replace(/fa\-[a-zA-Z0-9\-_]*/g, '').trim();

			const new_value = value + (escaped_val.length !== 0 ? ' ' : '') + escaped_val;

			$wrapper.find('i').attr('class', new_value);
			$inputSelector.val(new_value);

			hostcmsSlidepanel.closeBySelector('#settingsCrmIcons' + template_section_lib_id);
		},
		addPoint: function(object, template_section_lib_id, lib_property_id, varible_name) {
			const $wrapper = hQuery(object).closest('.details-wrapper'),
				$item_wrapper = $wrapper.find('.details-item-wrapper[data-block-name="' + varible_name + '"]');

			const count = $item_wrapper.find('.details-item').get().reduce(function(result, item) {
				return Math.max(result, hQuery(item).data("key"));
			}, 0);

			hQuery.ajax({
				data: { 'addPoint': 1, 'template_section_lib_id': template_section_lib_id, 'lib_property_id': lib_property_id, 'count': count },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status === 'success') {
						$item_wrapper.append(result.html);
					}
				}
			});
		},
		copyPoint: function(object, template_section_lib_id, lib_property_id, varible_name, block_id) {
			const $wrapper = hQuery(object).closest('.details-wrapper'),
				$item_wrapper = $wrapper.find('.details-item-wrapper[data-block-name="' + varible_name + '"]');

			const count = $item_wrapper.find('.details-item').get().reduce(function(result, item) {
				return Math.max(result, hQuery(item).data("key"));
			}, 0);

			hQuery.ajax({
				data: { 'copyPoint': 1, 'template_section_lib_id': template_section_lib_id, 'lib_property_id': lib_property_id, 'count': count, 'block_id': block_id },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status === 'success') {
						$item_wrapper.append(result.html);
					}
				}
			});
		},
		deletePoint: function(object, varible_name) {
			const $wrapper = hQuery(object).closest('.details-wrapper'),
				$item_wrapper = $wrapper.find('.details-item-wrapper[data-block-name="' + varible_name + '"]');
			const count = $item_wrapper.find('.details-item').length || 0;

			if (count > 1) {
				if (confirm(i18n['confirm_delete'])) {
					hQuery(object).closest('.details-item').remove();
				}
			}
		},
		changePosition: function(object, position, template_section_lib_id, template_section_id) {
			const $sectionWidget = hQuery('#hostcmsSectionWidget-' + template_section_lib_id);
			let hasRequest = false;
			let $prevDiv, $nextDiv;

			switch (position) {
				case -1: // prev
					$prevDiv = $sectionWidget.prev();
					if ($prevDiv.hasClass('hostcmsSectionWidget')) {
						$prevDiv.before($sectionWidget);
						hasRequest = true;
					}
					break;
				case 1: // next
					$nextDiv = $sectionWidget.next();
					if ($nextDiv.hasClass('hostcmsSectionWidget')) {
						$nextDiv.after($sectionWidget);
						hasRequest = true;
					}
					break;
			}

			hQuery("#hostcmsSection" + template_section_id).sortable("refresh").sortable("refreshPositions");

			if (hasRequest) {
				const data = hQuery("#hostcmsSection" + template_section_id).sortable("serialize");

				hQuery.ajax({
					data: data,
					type: "POST",
					url: "/template-section.php?hostcmsAction=SHOW_DESIGN",
				});
			}
		}
	});

	window.range_timeout = null;

	function frontendInit(jParent) {
		jParent.on('dblclick', function(e) {
			const target = e.target;
			// Check matching criteria natively
			const data = getEditableData(target, 'data-editable-');
			if (data) {
				const object = hQuery(target);
				setTimeout(function() {
					object.addClass('editing');
					wysiwyg.frontendInit(object);
				}, 300);

				setTimeout(function() {
					object
						.prop('contentEditable', true)
						.focus();
				}, 500);
			}
		});

		jParent.on('blur', function(e) {
			const target = e.target;
			const data = getEditableData(target, 'data-editable-');

			if (data && data.hasOwnProperty('id')) {
				data['_'] = Date.now();
				const object = hQuery(target);

				hQuery.ajax({
					data: { 'saveContent': 1, data: data },
					type: "POST",
					url: hostcmsBackend + '/template/index.php',
					success: function(result) {
						object.removeClass('editing');
					}
				});
			}
		});

		let clicks = 0, timer = null;
		const DELAY = 700;

		jParent.on('click', function(e) {
			const target = e.target;
			// Check if target matches data-design criteria
			const dataDesign = getEditableData(target, 'data-design-');

			if (!dataDesign) return; // Not our element

			if (hQuery(target).hasClass('mce-edit-focus')) {
				return false;
			}

			clicks++;
			const selected = getSelection().toString();

			if (!selected && clicks === 1) {
				timer = setTimeout(function() {
					clicks = 0; //after action performed, reset counter

					if (dataDesign.hasOwnProperty('id') && dataDesign.hasOwnProperty('name')) {
						let design_id = "data-design-id=" + dataDesign.id + "_" + dataDesign.name,
							field = dataDesign.name;

						if (dataDesign.hasOwnProperty('position') && dataDesign.position) {
							design_id += "_" + dataDesign.position;
							field += "_" + dataDesign.position;
						}

						hQuery.showDesignPanel(dataDesign.id, field, [design_id]);
					}
				}, DELAY);
			} else {
				clearTimeout(timer);
				clicks = 0;
			}
		});
	}

	// Initialize on body
	frontendInit(hQuery('body'));

	window.frontendNotify = function(message, position, timeout, theme, icon, closable, sound) {
		toastr.options.positionClass = 'toast-' + position;
		toastr.options.extendedTimeOut = 0;
		toastr.options.timeOut = timeout;
		toastr.options.closeButton = closable;
		toastr.options.iconClass = icon + ' toast-' + theme;
		toastr.options.playSound = sound;
		toastr['custom'](message);
	}

	document.addEventListener('auxclick', function(event) {
		if (event.button === 1 && event.target.closest('[data-confirm-message]')) { // middle click
			event.preventDefault();

			const target = event.target.tagName === 'A' ? event.target : event.target.parentNode,
				message = target.getAttribute('data-confirm-message') || 'Вы уверены?';

			if (confirm(message)) {
				if (target.tagName === 'A') {
					window.open(target.href, '_blank');
				}
			}
		}
	});

	document.addEventListener('keydown', function(e) {
		// Check Ctrl+A and that focus is on our div
		if ((e.ctrlKey || e.metaKey) && e.key === 'a' &&
			document.activeElement.classList.contains('selectable')) {
			e.preventDefault();

			const selection = window.getSelection(),
				range = document.createRange();

			range.selectNodeContents(document.activeElement);
			selection.removeAllRanges();
			selection.addRange(range);
		}
	});

	// Touch device handlers
	document.addEventListener('DOMContentLoaded', function() {
		// Use single delegated listener for touchstart
		document.addEventListener('touchstart', function(e) {
			const target = e.target;

			// Handle widget activation
			const widget = target.closest('.hostcmsSectionWidget');

			// If inside panels, ignore
			if (target.closest('.hostcmsSectionPanel') || target.closest('.hostcmsSectionWidgetPanel')) {
				return;
			}

			// Logic for widgets
			if (widget) {
				const actives = document.querySelectorAll('.hostcmsSectionWidget.touch-active');
				for (let i = 0; i < actives.length; i++) {
					if (actives[i] !== widget) actives[i].classList.remove('touch-active');
				}
				widget.classList.toggle('touch-active');
				// Prevent default if necessary, but careful not to block scroll unless intended
				// e.preventDefault();
			} else {
				// Click outside widgets
				const actives = document.querySelectorAll('.hostcmsSectionWidget.touch-active');
				for (let i = 0; i < actives.length; i++) {
					actives[i].classList.remove('touch-active');
				}
			}
		}, { passive: false });

		// Panel logic
		const panel = document.querySelector('.hostcmsInformationPanel.hostcms-panel-closed'),
			subpanel = document.querySelector('.hostcmsInformationPanel .hostcmsSubPanel');

		if (!panel) return;

		const isTouchDevice = 'ontouchstart' in window ||
			navigator.maxTouchPoints > 0 ||
			navigator.msMaxTouchPoints > 0;

		if (!isTouchDevice) return;

		panel.addEventListener('touchstart', function(event) {
			if (event.target === panel || event.target === subpanel) {
				event.preventDefault();
				panel.classList.add('touch-active');
			}
		}, { passive: false });

		document.addEventListener('touchstart', function(event) {
			if (!panel.contains(event.target)) {
				panel.classList.remove('touch-active');
			}
		}, { passive: true });
	});

})(hQuery);