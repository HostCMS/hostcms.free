//(function($){
	// Функции для коллекции элементов
	hQuery.fn.extend({
		hostcmsEditable: function(settings){
			settings = hQuery.extend({
				save: function(item, settings){
					var data = {
						'id': item.attr('hostcms:id'),
						'entity': item.attr('hostcms:entity'),
						'field': item.attr('hostcms:field'),
						'value': item.html()
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
					var item = jEditInPlace.prevAll('.hostcmsEditable').eq(0);
					item.html(jEditInPlace.val()).css('display', '');
					jEditInPlace.remove();
					settings.save(item, settings);
				}
			}, settings);

			return this.each(function(index, object){
				hQuery(object).on('click', function(){
					var obj = hQuery(this), href = obj.attr('href');
					if (href != undefined && !obj.data('timer')) {
					   obj.data('timer', setTimeout(function(){window.location = href;}, 500));
					}
					else
					{
						obj.dblclick();
					}
					return false;
				}).on('dblclick', function(){

					var item = hQuery(this);

					clearTimeout(item.data('timer'));
					item.data('timer', null);

					var data = {
						'id': item.attr('hostcms:id'),
						'entity': item.attr('hostcms:entity'),
						'field': item.attr('hostcms:field'),
						'loadValue': true
					};
					data['_'] = Math.round(new Date().getTime());

					hQuery.ajax({
						// ajax loader
						context: item,
						url: settings.path,
						type: 'POST',
						data: data,
						dataType: 'json',
						success: function(result) {
							//console.log(this, result);

							var item = hQuery(this);
							
							if (result.status != 'Error')
							{
								var type = item.attr('hostcms:type'), jEditInPlace;

								switch(type)
								{
									case 'textarea':
									case 'wysiwyg':
										jEditInPlace = hQuery('<textarea>');
									break;
									case 'input':
									default:
										jEditInPlace = hQuery('<input>').prop('type', 'text');
								}

								if (type != 'wysiwyg')
								{
									jEditInPlace.on('blur', function(){
										settings.blur(jEditInPlace)
									});
								}

								jEditInPlace.on('keydown', function(e){
									if (e.keyCode == 13) {
										e.preventDefault();
										this.blur();
									}
									if (e.keyCode == 27) { // ESC
										e.preventDefault();
										var input = hQuery(this), item = input.prev();
										item.css('display', '');
										input.remove();
									}
								})/*.width('90%')*/.prop('name', item.parent().prop('id'))
								.css(hQuery(this).getStyleObject())
								.insertAfter(item)
								.focus()
								.val(result.value/*item.html()*/);

								if (type == 'wysiwyg')
								{
									setTimeout(function(){
										jEditInPlace.tinymce({
											theme: "modern",
											language: backendLng,
											language_url: '/admin/wysiwyg/langs/' + backendLng + '.js',
											init_instance_callback: function (editor) {
												editor.on('blur', function (e) {
													settings.blur(jEditInPlace);
												});
											},
											script_url: "/admin/wysiwyg/tinymce.min.js"});
									}, 300);
								}

								item.css('display', 'none');
							}
							else
							{
								item.removeClass('hostcmsEditable');
							}
						}
					});
				})
				.addClass('hostcmsEditable');
			});
		},
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
	});

	hQuery.extend({
		createWindow: function(settings) {
			settings = hQuery.extend({
				open: function( event, ui ) {
					var uiDialog = hQuery(this).parent('.ui-dialog');
					uiDialog.width(uiDialog.width()).height(uiDialog.height());
				},
				close: function( event, ui ) {
					hQuery(this).dialog('destroy').remove();
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
				autoOpen: false, resizable: true, draggable: true, Minimize: false, Closable: true
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
						Notify(result, 'top-left', '5000', 'danger', 'fa-gear', true, false);
					}
				}
			});
		}
	});
//})(hQuery);

/*Show Notification*/
function Notify(message, position, timeout, theme, icon, closable, sound) {
	toastr.options.positionClass = 'toast-' + position;
	toastr.options.extendedTimeOut = 0; //1000;
	toastr.options.timeOut = timeout;
	toastr.options.closeButton = closable;
	toastr.options.iconClass = icon + ' toast-' + theme;
	toastr.options.playSound = sound;
	toastr['custom'](message);
}