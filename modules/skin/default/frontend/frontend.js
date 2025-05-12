//(function($){
	// Функции для коллекции элементов
	/*hQuery.fn.extend({
		// http://upshots.org/javascript/jquery-copy-style-copycss
		getStyleObject: function() {
			var dom = this.get(0);
			var style;
			var returns = {};
			if (window.getComputedStyle){
				var camelize = function(a,b){
					return b.toUpperCase();
				};
				style = window.getComputedStyle(dom, null);
				for(var i = 0, l = style.length; i < l; i++){
					var prop = style[i];
					var camel = prop.replace(/\-([a-z])/g, camelize);
					var val = style.getPropertyValue(prop);
					returns[camel] = val;
				};
				return returns;
			};
			if (style = dom.currentStyle){
				for(var prop in style){
					returns[prop] = style[prop];
				};
				return returns;
			};
			if (style = dom.style){
				for(var prop in style){
					if(typeof style[prop] != 'function'){
						returns[prop] = style[prop];
					};
				};
				return returns;
			};
			return returns;
		}
	});*/

	/**
	 * jQuery Cookie plugin
	 *
	 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
	 * Dual licensed under the MIT and GPL licenses:
	 * http://www.opensource.org/licenses/mit-license.php
	 * http://www.gnu.org/licenses/gpl.html
	 *
	 */
	 hQuery.cookie = function (key, value, options) {
		// key and at least value given, set cookie...
		if (arguments.length > 1 && String(value) !== "[object Object]") {
			options = hQuery.extend({}, options);

			if (value === null || value === undefined) {
				options.expires = -1;
			}

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setDate(t.getDate() + days);
			}

			value = String(value);

			return (document.cookie = [
				encodeURIComponent(key), '=',
				options.raw ? value : cookie_encode(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path ? '; path=' + options.path : '',
				options.domain ? '; domain=' + options.domain : '',
				options.secure ? '; secure' : ''
			].join(''));
		}

		// key and possibly options given, get cookie...
		options = value || {};
		var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
		return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
	};

	function cookie_encode(string){
		//full uri decode not only to encode ",; =" but to save uicode charaters
		var decoded = encodeURIComponent(string);
		//encod back common and allowed charaters {}:"#[] to save space and make the cookies more human readable
		var ns = decoded.replace(/(%7B|%7D|%3A|%22|%23|%5B|%5D)/g,function(charater){return decodeURIComponent(charater);});
		return ns;
	}
	/* /jQuery Cookie plugin */

	hQuery.extend({
		lockPanel: function(object) {
			var $object = hQuery(object),
			$parentDiv = $object.parents('.hostcmsInformationPanel'),
			$icon = $object.find('i#hostcmsLock'),
			$locked = parseInt(hQuery.cookie(('lock-panel')));

			if (!$locked && $icon.hasClass('fa-lock-open'))
			{
				// console.log('lock');
				hQuery.cookie('lock-panel', 1, { expires: 365 });
			}
			else if ($locked && $icon.hasClass('fa-lock'))
			{
				// console.log('unlock');
				hQuery.cookie('lock-panel', 0, { expires: 365 });
			}

			$icon.toggleClass('fa-lock-open fa-lock');
			$parentDiv.toggleClass('hostcms-panel-opened hostcms-panel-closed');
		},
		hostcmsEditable: function(settings) {
			settings = hQuery.extend({
				save: function(item, settings){
					var value;

					switch(item.attr('hostcms:type'))
					{
						case 'textarea':
						case 'wysiwyg':
							value = item.html();
						break;
						case 'input':
						default:
							value = item.text();
					}

					var data = {
						'id': item.attr('hostcms:id'),
						'entity': item.attr('hostcms:entity'),
						'field': item.attr('hostcms:field'),
						'value': value
					};
					data['_'] = Math.round(new Date().getTime());

					hQuery.ajax({
						// ajax loader
						context: hQuery('<img>').addClass('img_line').prop('src', '/modules/skin/default/frontend/images/ajax-loader.gif').appendTo(item),
						url: settings.path,
						type: 'POST',
						data: data,
						dataType: 'json',
						success: function(){this.remove();}
					});
				},
				blur: function(jEditInPlace) {
					var item = jEditInPlace.prevAll('.hostcmsEditable').eq(0),
						type = item.attr('hostcms:type');

					switch(type)
					{
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

			hQuery(document).on("click", "*[hostcms\\:id]", function(event) {
				var $object = hQuery(this);

				if (!event.isDefaultPrevented())
				{
					if (!$object.data('timer')) {
						$object.data('timer', setTimeout(function(){
							$object.data('timer', null);
							var href = $object.attr('href');
							if (href != undefined) {
							   window.location = href;
							}
						}, 500));
					}
				}

				event.preventDefault();
			})
			.on("dblclick", "*[hostcms\\:id]", function(event) {
				var $object = hQuery(this);

				clearTimeout($object.data('timer'));
				$object.data('timer', null);

				var data = {
					'id': $object.attr('hostcms:id'),
					'entity': $object.attr('hostcms:entity'),
					'field': $object.attr('hostcms:field'),
					'loadValue': true
				};
				data['_'] = Math.round(new Date().getTime());

				hQuery.ajax({
					// ajax loader
					context: $object,
					url: settings.path,
					type: 'POST',
					data: data,
					dataType: 'json',
					success: function(result) {
						var $object = hQuery(this);

						if (result.status != 'Error')
						{
							var type = $object.attr('hostcms:type'), jEditInPlace;

							switch(type)
							{
								case 'textarea':
								case 'wysiwyg':
									jEditInPlace = hQuery('<textarea>');
								break;
								case 'input':
								default:
									jEditInPlace = hQuery('<input class="hostcmsEditableInput">').prop('type', 'text');
							}

							if (type != 'wysiwyg')
							{
								jEditInPlace.on('blur', function(){
									settings.blur(jEditInPlace)
								});
							}

							jEditInPlace
								.val(result.value/*$object.html()*/)
								.prop('name', $object.parent().prop('id'))
								.height($object.height())
								.width($object.width())
								//.css($object.getStyleObject())
								.insertAfter($object)
								.on('keydown', function(e){
									if (e.keyCode == 13) {
										e.preventDefault();
										this.blur();
									}
									if (e.keyCode == 27) { // ESC
										e.preventDefault();
										var input = hQuery(this), $object = input.prev();
										$object.css('display', '');
										input.remove();
									}
								})/*.width('90%')*/
								.focus();

							if (type == 'wysiwyg')
							{
								setTimeout(function(){
									var aCss = [];

									hQuery("head > link[rel = stylesheet").each(function() {
										var linkHref = hQuery(this).attr('href');
										if (linkHref != 'undefined')
										{
											aCss.push(linkHref);
										}
									});

									jEditInPlace.tinymce(hQuery.extend({
										// theme: "silver",
										// toolbar_items_size: "small",
										language: backendLng,
										language_url: hostcmsBackend + '/wysiwyg/langs/' + backendLng + '.js',
										init_instance_callback: function (editor) {
											editor.on('blur', function (e) {
												settings.blur(jEditInPlace);
											});
										},
										script_url: hostcmsBackend + "/wysiwyg/tinymce.min.js",
										menubar: false,
										plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table  importcss',
										toolbar: 'undo redo | styleselect formatselect | bold italic underline backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
										content_css: aCss
									}, settings.wysiwygConfig));
								}, 300);
							}

							$object.css('display', 'none');
						}
						else
						{
							$object.removeClass('hostcmsEditable');
						}
					}
				});
			});
		},
		createWindow: function(settings) {
			settings = hQuery.extend({
				open: function( event, ui ) {
					var uiDialog = hQuery(this).parent('.ui-dialog');
					uiDialog.width(uiDialog.width()).height(uiDialog.height());

					hQuery(".xmlWindow").children(".ui-dialog-titlebar").append("<button id='btnMaximize' class='ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only ui-dialog-titlebar-maximize' type='button' role='button' title='Maximize'><span class='ui-button-icon-primary ui-icon ui-icon-newwin'></span><span class='ui-button-text'>Maximize</span></button>");

					var max = false,
						original_width = uiDialog.width(),
						original_height = uiDialog.height(),
						original_position = uiDialog.position(),
						textareaBlock = uiDialog.find('textarea'),
						// textareaBlockHeight = textareaBlock.height();
						textareaBlockOuterHeight = textareaBlock.parents('div.hostcmsWindow').outerHeight();

					hQuery("#btnMaximize")
						.hover(function () {
							hQuery(this).addClass('ui-state-hover');
						}, function () {
							hQuery(this).removeClass('ui-state-hover');
						})
						.click(function (e) {
							if (max === false)
							{
								// Maximaze window
								max = true;

								hQuery('body').addClass('bodyMaximize');

								textareaBlock.height(hQuery(window).height() - 25 + 'px');
								textareaBlock.parents('div.hostcmsWindow').height('');

								// console.log('original_height_max', uiDialog.height());

								original_height = uiDialog.height();
								original_width = uiDialog.width();
								original_position = uiDialog.position();

								uiDialog.animate({
									height: hQuery(window).height() - 3 + "px",
									width: hQuery(window).width() + "px",
									top: hQuery(window).scrollTop(),
									left: 0
								}, 200);
							}
							else
							{
								// Restore window
								max = false;

								hQuery('body').removeClass('bodyMaximize');

								// textareaBlock.height(textareaBlockHeight + 'px');
								// textareaBlock.parents('div.hostcmsWindow').height(textareaBlockHeight + 'px');
								textareaBlock.removeAttr('style');
								textareaBlock.parents('div.hostcmsWindow').height(textareaBlockOuterHeight + 'px');

								uiDialog.animate({
								  height: original_height + "px",
								  width: original_width + "px",
								  top: original_position.top + "px",
								  left: original_position.left + "px"
								}, 200);
							}

							return false; // to avoid submit if any form
						});
				},
				close: function( event, ui ) {
					hQuery(this).dialog('destroy').remove();

					hQuery('body').removeClass('bodyMaximize');
				}
			}, settings);

			var windowCounter = hQuery('body').data('windowCounter');
			if (windowCounter == undefined) { windowCounter = 0 }
			hQuery('body').data('windowCounter', windowCounter + 1);

			return hQuery('<div>')
				.addClass("hostcmsWindow")
				.attr("id", "Window" + windowCounter)
				.appendTo(hQuery(document.body))
				.dialog(settings);
		},
		showWindow: function(windowId, content, settings) {
			settings = hQuery.extend({
				autoOpen: false, resizable: true, draggable: true, Minimize: false, Closable: true, dialogClass: 'xmlWindow'
			}, settings);

			var jWin = hQuery('#' + windowId);

			if (!jWin.length)
			{
				jWin = hQuery.createWindow(settings)
					.attr('id', windowId)
					.html(content);
			}

			jWin.dialog('open');

			return jWin;
		},
		openWindow: function(settings) {
			settings = hQuery.extend({
				width: /*'70%',*/hQuery(window).width() * 0.7,
				height: /*500,*/hQuery(window).height() * 0.7,
				path: '',
				additionalParams: ''
			}, settings);

			var jDivWin = hQuery.createWindow(settings), cmsrequest = settings.path;
			if (settings.additionalParams != ' ' && settings.additionalParams != '')
			{
				cmsrequest += '?' + settings.additionalParams;
			}

			jDivWin
				.append('<iframe src="' + cmsrequest + '&hostcmsMode=blank"></iframe>')
				.dialog('open');
			return jDivWin;
		},
		changeActive: function(settings){
			settings = hQuery.extend({
				path: ''
			}, settings);

			var data = '';

			data['_'] = Math.round(new Date().getTime());

			hQuery.ajax({
				context: settings.goal,
				url: settings.path,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: hQuery.refreshSectionCallback
			});
		},
		refreshSection: function(id){
			var data = '';
			data['_'] = Math.round(new Date().getTime());

			hQuery.ajax({
				context: hQuery('#hostcmsSection' + id),
				url: '/template-section.php?hostcmsAction=SHOW_DESIGN&template_section_id=' + id,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: hQuery.refreshSectionCallback
			});
		},
		deleteWidget: function(settings){
			settings = hQuery.extend({
				path: ''
			}, settings);

			var data = '';
			data['_'] = Math.round(new Date().getTime());

			hQuery.ajax({
				context: settings.goal,
				url: settings.path + '&hostcmsAction=SHOW_DESIGN',
				type: 'POST',
				data: data,
				dataType: 'json',
				success: hQuery.refreshSectionCallback
			});
		},
		refreshSectionCallback: function(result)
		{
			var newDiv = hQuery(result);

			document.write = function(str) {
				newDiv.find('script').eq(0).after(str);
			}

			this.replaceWith(newDiv);

			hQuery(".hostcmsPanel,.hostcmsSectionPanel,.hostcmsSectionWidgetPanel", newDiv)
				.draggable({containment: "document"});

			frontendInit(newDiv);
		},
		sortWidget: function()
		{
			hQuery(".hostcmsSection").sortable({
				items: "> .hostcmsSectionWidget",
				handle: ".drag-handle",
				stop: function (event, ui) {
					var data = hQuery(this).sortable("serialize");
					hQuery.ajax({
						data: data,
						type: "POST",
						url: "/template-section.php?hostcmsAction=SHOW_DESIGN",
					});
				}
			})/*.disableSelection()*/;
		},
		toggleSlidePanel: function()
		{
			hQuery('.backendBody .template-settings').toggleClass('show');
			hQuery('.backendBody .template-settings #slidepanel-settings i').toggleClass('fa-cog fa-times');
		},
		reloadStylesheets: function()
		{
			var timestamp = new Date().getTime();
			hQuery('link[rel="stylesheet"]').each(function () {
				var newLink = hQuery('<link rel="stylesheet">').attr('href', this.href.replace(/&\d{9,}|$/, (this.href.indexOf('?') >= 0 ? '&' : '?') + timestamp)),
				oldLink = hQuery(this);
				oldLink.after(newLink);

				setTimeout(function(){ oldLink.remove(); }, 500);
			});
		},
		sendLessVariable: function()
		{
			var object = hQuery(this);
			hQuery.ajax({
				data: {name: object.attr('name'), value: object.val(), template: object.data('template')},
				type: "POST",
				url: "/template-less.php",
				success: function(json){
					var result = JSON.parse(json);
					if (result == 'OK')
					{
						hQuery.reloadStylesheets();
					}
					else
					{
						frontendNotify(result, 'top-left', '5000', 'danger', 'fa-gear', true, false);
					}
				}
			});
		},
		showDesignPanel: function (template_section_lib_id, field, aAttrs)
		{
			aAttrs = aAttrs || [];

			hQuery.ajax({
				data: { 'showDesignPanel': 1, 'template_section_lib_id': template_section_lib_id, 'field': field, 'attributes': aAttrs },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result){
					if (result.status == 'success')
					{
						hQuery('body').append(result.html);
						hQuery('.template-settings.template-section-lib-settings .slidepanel').animate({ left: 0 });
					}
				}
			});
		},
		changePreset: function (object, template_section_lib_id)
		{
			if (template_section_lib_id)
			{
				var $object = hQuery(object),
					background = $object.data('background') || 'white',
					$widget = hQuery('#hostcmsSectionWidget-' + template_section_lib_id),
					$panel = hQuery('#panel' + template_section_lib_id)
					design_id = $panel.data('design-id') || '', //50_caption, 50_icon_2
					aSplit = design_id.split('_', 3),
					field = aSplit[1] + (typeof aSplit[2] != 'undefined' ? '_' + aSplit[2] : '') || '';

				if (design_id != '')
				{
					$widget = hQuery('#' + design_id);
				}

				$panel.find('.background-active').removeClass('background-active');
				$object.addClass('background-active');

				$widget.removeAttr('data-bg');

				$widget.removeClass(function(index, className) {
					return (className.match (/(^|\s)preset-\S+/g) || []).join(' ');
				});

				$widget.attr('data-bg', background);
				$widget.addClass(background);

				if (typeof field == 'undefined' || field == 'undefined')
				{
					field = '';
				}

				hQuery.ajax({
					data: { 'changePreset': 1, 'template_section_lib_id': template_section_lib_id, 'field': field, 'class': $widget.attr('class') },
					type: "POST",
					dataType: 'json',
					url: hostcmsBackend + '/template/index.php',
					success: function(result){
						if (result.status == 'success')
						{
							var aProperties = ['background', 'color', 'opacity'];
							$.each(aProperties, function(index, property){
								hQuery.clearProperty(property);
							});
						}
					}
				});
			}
		},
		clearProperty: function(property)
		{
			hQuery('input[data-property=' + property + ']').parents('.background-block').find('.fa-circle-xmark').click();
		},
		refreshStyle: function (object, value)
		{
			var $object = hQuery(object),
				template_section_lib_id = +$object.data('id') || 0;

			value = value || $object.val();

			if (template_section_lib_id)
			{
				var type = $object.data('type') || '',
					property = $object.data('property') || '',
					$widget = hQuery('#hostcmsSectionWidget-' + template_section_lib_id),
					$panel = hQuery('#panel' + template_section_lib_id)
					design_id = $panel.data('design-id') || '', //50_caption, 50_icon_2
					aSplit = design_id.split('_', 3),
					field = aSplit[1] + (typeof aSplit[2] != 'undefined' ? '_' + aSplit[2] : '') || '';

				if (design_id != '')
				{
					$widget = hQuery('#' + design_id);
				}

				switch (type)
				{
					case 'colorpicker-range':
						var name = $object.data('name') || '';

						if (name != '')
						{
							var aParts = [];
							$.each(hQuery('[data-type=' + type + '][data-name^=' + name + ']'), function() {
								var val = hQuery(this).val();

								val != '' && aParts.push(val);
							});

							value = aParts.length == 3
								? "linear-gradient(" + aParts[2] + "deg," + aParts[0] + "," + aParts[1] + ")"
								: '';
						}
					break;
				}

				if (value == 'auto')
				{
					value = '';
				}

				if (typeof field == 'undefined' || field == 'undefined')
				{
					field = '';
				}

				$widget.css(property, value != '' ? value : '');

				hQuery.ajax({
					url: hostcmsBackend + '/template/index.php',
					data: { 'refreshStyle': 1, 'template_section_lib_id': template_section_lib_id, 'type': type, 'field': field, 'property': property, 'value': value },
					type: "POST",
					dataType: 'json'
				});
			}
		},
		updateMinicolors: function()
		{
			hQuery.refreshStyle(this);
		},
		changeRange: function (object, measure)
		{
			var $object = hQuery(object),
				measure = measure || '';
				value = $object.val() + measure
				unset = +$object.data('unset') || 0;

			if (unset && $object.val() == 0)
			{
				value = 'auto';
			}

			$object.parents('.range-wrapper').find('.range-value').text(value);

			clearTimeout(window.range_timeout);

			window.range_timeout = setTimeout(function () {
				hQuery.refreshStyle(object, value);
			}, 1000);
		},
		clearBlock: function (object, type, default_value, measure)
		{
			var $object = hQuery(object),
				$parent = $object.parents('.background-block');

			$.each($parent.find(':input'), function() {
				var $input = hQuery(this),
					input_type = $input.data('type') || '';

				if (input_type == 'colorpicker' || (input_type != 'colorpicker-range' && $input.hasClass('colorpicker')))
				{
					$input.minicolors('value', default_value);
				}
				else if (input_type == 'colorpicker-range')
				{
					if ($input.hasClass('colorpicker'))
					{
						$input.minicolors('value', '');
					}
					else
					{
						$input.val(default_value);
						$input.parents('.range-wrapper').find('.range-value').text(default_value + measure);
					}
				}
				else
				{
					$input.val(default_value);

					if (input_type == 'range')
					{
						$input.parents('.range-wrapper').find('.range-value').text(default_value + measure);
					}
				}

				hQuery.refreshStyle(this, default_value);
			});
		},
		changeDevice: function (object, type)
		{
			console.log(type);

			var $object = hQuery(object),
				width = 0;

			hQuery('.top-panel .icons > i').removeClass('active');

			switch (type)
			{
				case 'tablet':
					width = 810;
				break;
				case 'tablet-wide':
					width = 1080;
				break;
				case 'mobile':
					width = 390;
				break;
				case 'mobile-wide':
					width = 844;
				break;
			}

			// console.log(width);

			if (width)
			{
				hQuery('#siteFrame').width(width);
			}
			else
			{
				hQuery('#siteFrame').css('width', '');
			}

			$object.addClass('active');
		},
		showWidgetPanel: function (template_section_id, template_section_lib_id)
		{
			template_section_lib_id = template_section_lib_id || 0;

			hQuery.ajax({
				url: hostcmsBackend + '/template/index.php',
				data: { 'showWidgetPanel': 1, 'template_section_id': template_section_id, 'template_section_lib_id': template_section_lib_id },
				type: "POST",
				dataType: 'json',
				success: function(result) {
					if (result.status == 'success')
					{
						hQuery('body').append(result.html);
						hQuery('.template-settings.template-section-settings .slidepanel').animate({ left: 0 });

						setTimeout(function () {
							hQuery('#panel' + template_section_id).find('.dirs-wrapper .dir-item:first-child').click();
						}, 500);
					}
				}
			});
		},
		showWidgets: function (object, template_section_id, template_section_lib_id, lib_dir_id)
		{
			var $object = hQuery(object),
				lib_wrapper = hQuery('#panel' + template_section_id).find('.libs-wrapper');

			hQuery('#panel' + template_section_id).find('.dir-item').removeClass('active');
			hQuery('#panel' + template_section_id).find('.dir-item[data-dir-id=' + lib_dir_id + ']').addClass('active');

			lib_wrapper.empty();

			hQuery.ajax({
				url: hostcmsBackend + '/template/index.php',
				data: { 'showWidgets': 1, 'template_section_id': template_section_id, 'template_section_lib_id': template_section_lib_id, 'lib_dir_id': lib_dir_id },
				type: "POST",
				dataType: 'json',
				success: function(result) {
					if (result.status == 'success')
					{
						lib_wrapper.append(result.html);
					}
				}
			});
		},
		addWidget: function (object, template_section_id, template_section_lib_id, lib_id)
		{
			var $object = hQuery(object),
				$parent = $object.parents('.lib-item');

			hQuery.ajax({
				// ajax loader
				context: hQuery('<img>').addClass('img_line').prop('src', '/modules/skin/default/frontend/images/ajax-loader.gif').appendTo($parent),
				data: { 'addWidget': 1, 'template_section_id': template_section_id, 'template_section_lib_id': template_section_lib_id, 'lib_id': lib_id },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status == 'success')
					{
						hQuery.refreshSection(template_section_id);
						$parent.find('.img_line').remove();
					}
				}
			});
		},
		updateSettings: function(selector, parent, template_section_lib_id, template_section_id)
		{
			event.preventDefault();

			var FormNode = hQuery('form.settings' + template_section_lib_id),
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
					if (result.status == 'success')
					{
						parent.find('.img_line').remove();

						parent.append('<i class="fa-solid fa-check-circle process-status palegreen"></i>');

						hQuery.refreshSection(template_section_id);
						hQuery.refreshSettingsBlock(template_section_lib_id);

						setTimeout(function() {
							parent.find('.process-status').remove();
						}, 2000);
					}
				}
			});
		},
		refreshSettingsBlock: function(template_section_lib_id)
		{
			hQuery.ajax({
				// ajax loader
				data: { 'refreshSettingsBlock': 1, 'template_section_lib_id': template_section_lib_id },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status == 'success')
					{
						var $parent = hQuery('form.settings' + template_section_lib_id).parent();

						$parent.empty();

						$parent.append(result.html);
					}
				}
			});
		},
		saveSettings: function(object, template_section_lib_id, template_section_id)
		{
			var $object = hQuery(object);

			hQuery.updateSettings("#settings :input", $object.parents('.button-wrapper'), template_section_lib_id, template_section_id);
		},
		showSettingsCrmIcons: function(object, input_name)
		{
			hQuery.ajax({
				// ajax loader
				data: { 'showSettingsCrmIcons': 1, 'input_name': input_name },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status == 'success')
					{
						// append to slide panel
						$('.template-settings.template-section-lib-settings').append(result.html);

						var $modal = $('#settingsCrmIcons');

						$modal.modal('show');

						$modal.on('hidden.bs.modal', function () {
							$(this).remove();
						});
					}
				}
			});
		},
		selectSettingsCrmIcon: function (object, input_name)
		{
			var $object = $(object),
				value = $object.data('value'),
				$modal = $('#settingsCrmIcons'),
				$inputSelector = $('input[name = "' + input_name + '"]'),
				$wrapper = $inputSelector.parents('.settings-row-icon-wrapper'),
				input_val = $inputSelector.val();

			var escaped_val = input_val.replace(/fa\-[a-zA-Z0-9\-_]*/g, '');
			escaped_val = escaped_val.trim();

			var new_value = value + (escaped_val.length != '' ? ' ' : '') + escaped_val;

			$wrapper.find('i').attr('class', new_value);
			$inputSelector.val(new_value);

			$modal.modal('hide');
		},
		addPoint: function (object, template_section_lib_id, lib_property_id, varible_name)
		{
			var $wrapper = $(object).parents('.details-wrapper'),
				$item_wrapper = $wrapper.find('.details-item-wrapper[data-block-name = ' + varible_name + ']');

			var count = $item_wrapper.find('.details-item').get().reduce(function (result, item) {
				return Math.max(result, $(item).data("key"));
			}, 0);

			// console.log('addPoint', count);

			hQuery.ajax({
				// ajax loader
				data: { 'addPoint': 1, 'template_section_lib_id': template_section_lib_id, 'lib_property_id': lib_property_id, 'count': count },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status == 'success')
					{
						$item_wrapper.append(result.html);
					}
				}
			});
		},
		copyPoint: function (object, template_section_lib_id, lib_property_id, varible_name, block_id)
		{
			var $wrapper = $(object).parents('.details-wrapper'),
				$item_wrapper = $wrapper.find('.details-item-wrapper[data-block-name = ' + varible_name + ']');

			var count = $item_wrapper.find('.details-item').get().reduce(function (result, item) {
				return Math.max(result, $(item).data("key"));
			}, 0);

			// console.log('copyPoint', count);

			hQuery.ajax({
				// ajax loader
				data: { 'copyPoint': 1, 'template_section_lib_id': template_section_lib_id, 'lib_property_id': lib_property_id, 'count': count, 'block_id': block_id },
				type: "POST",
				dataType: 'json',
				url: hostcmsBackend + '/template/index.php',
				success: function(result) {
					if (result.status == 'success')
					{
						$item_wrapper.append(result.html);
					}
				}
			});
		},
		deletePoint: function (object, varible_name)
		{
			var $wrapper = $(object).parents('.details-wrapper'),
				$item_wrapper = $wrapper.find('.details-item-wrapper[data-block-name = ' + varible_name + ']')
				count = $item_wrapper.find('.details-item').length || 0;

			if (count > 1)
			{
				if (confirm(i18n['confirm_delete']))
				{
					$(object).parents('.details-item').remove();
				}
			}
		}
	});
//})(hQuery);

window.range_timeout = null;

hQuery(document).on('click', function(e) {
	var $target = hQuery(e.target),
		$container = $target.closest('.template-settings.template-section-lib-settings');
		$slidepanel = $target.closest('.template-settings.template-section-lib-settings .slidepanel');

	if (!$target.is('.fa-trash-can') && ($target.is('.slidepanel-button-close > i') || !$container.length))
	{
		hQuery('body').find(".template-settings.template-section-lib-settings .slidepanel").animate({ left: -1000 }, {
			complete: function() {
				hQuery('body').find(".template-settings.template-section-lib-settings").remove();
			}
		});
	}
});

hQuery(document).on('click', function(e) {
	var $target = hQuery(e.target),
		$container = $target.closest('.template-settings.template-section-settings');
		$slidepanel = $target.closest('.template-settings.template-section-settings .slidepanel');

	if ($target.is('.slidepanel-button-close > i') || !$container.length)
	{
		hQuery('body').find(".template-settings.template-section-settings .slidepanel").animate({ left: -1000 }, {
			complete: function() {
				hQuery('body').find(".template-settings.template-section-settings").remove();
			}
		});
	}
});

// Find by part of data- attribute name
hQuery.expr[':'].hasAttr = hQuery.expr.createPseudo(function(regex) {
	var re = new RegExp(regex);
	return function(obj) {
		var attrs = obj.attributes
		for (var i = 0; i < attrs.length; i++) {
			if (re.test(attrs[i].nodeName)) return true;
		};
		return false;
	};
});

// Get data-editable- attribute info { 'id', 'name', 'value' }
hQuery.fn.getEditableInfo = function (str) {
	var object = this.get(0),
		data = {},
		regex = new RegExp('^' + str);

    [].forEach.call(object.attributes, function (attr) {
		// console.log('reg', regex);

		if (regex.test(attr.name)) {
			var id = attr.name.substr(str.length).replace(/-(.)/g, function ($0, $1) {
				return $1;
			});

			// console.log(object);

			return data = {
				'id': id,
				'name': attr.value,
				'value': object.innerHTML,
				'position': object.dataset.position || 0,
				'prefix': object.dataset.prefix || ''
			}
		}
    });

    return data;
}

document.addEventListener('focusin', (e) => {
	if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
		e.stopImmediatePropagation();
	}
});

function frontendInit(jParent)
{
	hQuery('*:hasAttr(^data-editable-.+$)', jParent)
		.on('dblclick', function(e){
			// console.log('dblclick', e);
			if (e.hasOwnProperty('type') && e.type == 'dblclick')
			{
				var object = hQuery(this);

				setTimeout(function(){
					// console.log(hQuery(this));

					object.addClass('editing');

					object.tinymce({
						language: backendLng,
						language_url: hostcmsBackend + '/wysiwyg/langs/' + backendLng + '.js',
						init_instance_callback: function (editor) {
							editor.on('blur', function (e) {
								e.stopImmediatePropagation();
								editor.remove();
								object.css('visibility', '');

								object.removeClass('editing');
							});
						},
						script_url: hostcmsBackend + "/wysiwyg/tinymce.min.js",
						menubar: false,
						inline: true,
						plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table  importcss',
						toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
						font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt"
					});
				}, 300);
			}
		})
		.on('blur', function(e){
			var object = hQuery(this),
				data = object.getEditableInfo('data-editable-');

			if (data.hasOwnProperty('id'))
			{
				// console.log(data);

				data['_'] = Math.round(new Date().getTime());

				hQuery.ajax({
					data: { 'saveContent': 1, data: data },
					type: "POST",
					url: hostcmsBackend + '/template/index.php',
					success: function(result){
						// console.log(result);
						object.removeClass('editing');
					}
				});
			}
		});

	var DELAY = 700, clicks = 0, timer = null;

	hQuery('*:hasAttr(^data-design-.+$)', jParent)
		.on('click', function(e){
			if (hQuery(this).hasClass('mce-edit-focus'))
			{
				return false;
			}

			clicks++;

			// console.log('click', e);
			var selected = getSelection().toString();

			if (!selected && clicks === 1)
			{
				var object = hQuery(this),
					data = object.getEditableInfo('data-design-');

				timer = setTimeout(function() {
					clicks = 0;  //after action performed, reset counter

					// console.log(data);

					if (data.hasOwnProperty('id') && data.hasOwnProperty('name'))
					{
						var design_id = "data-design-id=" + data.id + "_" + data.name,
							field = data.name;

						if (data.hasOwnProperty('position') && data.position)
						{
							design_id += "_" + data.position;
							field += "_" + data.position;
						}

						hQuery.showDesignPanel(data.id, field, [design_id]);
					}
				}, DELAY);
			}
			else
			{
				clearTimeout(timer); //prevent single-click action
				clicks = 0;
			}
		});
}

frontendInit(hQuery('body'));

function frontendNotify(message, position, timeout, theme, icon, closable, sound) {
	toastr.options.positionClass = 'toast-' + position;
	toastr.options.extendedTimeOut = 0; //1000;
	toastr.options.timeOut = timeout;
	toastr.options.closeButton = closable;
	toastr.options.iconClass = icon + ' toast-' + theme;
	toastr.options.playSound = sound;
	toastr['custom'](message);
}