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
										language_url: '/admin/wysiwyg/langs/' + backendLng + '.js',
										init_instance_callback: function (editor) {
											editor.on('blur', function (e) {
												settings.blur(jEditInPlace);
											});
										},
										script_url: "/admin/wysiwyg/tinymce.min.js",
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
				url: '/template-section.php?template_section_id=' + id,
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
				url: settings.path,
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
						url: "/template-section.php",
					});
				}
			});
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
		}
	});
//})(hQuery);

function frontendNotify(message, position, timeout, theme, icon, closable, sound) {
	toastr.options.positionClass = 'toast-' + position;
	toastr.options.extendedTimeOut = 0; //1000;
	toastr.options.timeOut = timeout;
	toastr.options.closeButton = closable;
	toastr.options.iconClass = icon + ' toast-' + theme;
	toastr.options.playSound = sound;
	toastr['custom'](message);
}