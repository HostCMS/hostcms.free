(function($){
	jQuery.extend({
		ajaxCallbackSkin: function(data, status, jqXHR)
		{
			var jObject = jQuery(this);
			if (typeof data.title != 'undefined' && data.title != '' && jObject.hasClass('hostcmsWindow'))
			{
				jObject.HostCMSWindow({title: data.title});
			}
		},
		afterContentLoad: function(jWindow, data)
		{
			var windowId = jWindow.attr('id');

			// Format textarea
			//jQuery('#'+windowId+' textarea').elastic();

			// Tooltip
			jQuery('.tooltip').remove();
			jQuery.applyTooltip(windowId);

			// Stars
			if (jQuery('#'+windowId+' .stars').stars)
			{
				jQuery('#'+windowId+' .stars').stars({
					inputType: "select", disableValue: false
				});
			}
		},
		windowSettings: function(settings)
		{
			return jQuery.extend({
				//path: cmsrequest,
				autoOpen: false,
				addContentPadding: true,
				resizable: true,
				draggable: true,
				Minimize: false, // true
				Closable: true
			}, settings);
		},
		openWindow: function(settings)
		{
			settings = jQuery.windowSettings(
				jQuery.requestSettings(settings)
			);

			var cmsrequest = settings.path;
			if (settings.additionalParams != ' ' && settings.additionalParams != '')
			{
				cmsrequest += '?' + settings.additionalParams;
			}

			var windowCounter = $('body').data('windowCounter');
			if (windowCounter == undefined) { windowCounter = 0 }
			$('body').data('windowCounter', windowCounter + 1);

			var data = jQuery.getData(settings),
				jDivWin = jQuery('<div>')
				.addClass("hostcmsWindow")
				.append('<img src="' + hostcmsBackend + '/images/ajax_loader.gif" style="position: absolute; left: 50%; top: 50%" />')
				.attr("id", "Window" + windowCounter)
				.appendTo(jQuery(document));

			// Настройки
			jDivWin.HostCMSWindow(settings).HostCMSWindow('open');

			// Change window id
			data['hostcms[window]'] = jDivWin.attr('id');

			jQuery.ajax({
				context: jDivWin,
				url: cmsrequest,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: jQuery.ajaxCallback
			});

			return jDivWin;
		},
		widgetRequest: function(settings){
			// add ajax '_'
			var data = jQuery.getData({});

			jQuery.ajax({
				context: settings.context,
				url: settings.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function() {
					jQuery(this).HostCMSWindow('reload');
				}
			});
		},
		widgetLoad: function(settings)
		{
			// add ajax '_'
			var data = jQuery.getData({});

			jQuery.ajax({
				context: jQuery('body'),
				url: settings.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(data){
					this.append(data.form_html);
				}
			});
		},
		UpdateTaskbar: function(){
			var iCount = 5, iconWidth = 42,
				jTasksScroll = jQuery('#tasksScroll'),
				iTasksScrollChildren = jTasksScroll.children('div').length;
			jQuery('#tasks').css('max-width', iCount * iconWidth);
			jQuery('#subTaskBar .nav').css('display', iTasksScrollChildren > iCount ? 'block' : 'none');
			jTasksScroll.width(iTasksScrollChildren * iconWidth);
		},
		tasksScroll: function(delta)
		{
			var obj = jQuery('#tasks');
			obj.scrollLeft(obj.scrollLeft() + delta);
		},
		hideAllWindow: function()
		{
			jQuery('.hostcmsWindow').each(function(){
				jQuery(this).HostCMSWindow('Minimize');
			});
		},
		addTaskbar: function(jDivWin, settings)
		{
			var jDiv = jQuery('<div>')
				.attr("class", 'shortcut')
				.appendTo(jQuery('#subTaskBar #tasksScroll')),
			img = jQuery('<img>')
				.prop('src', settings.shortcutImg)
				.prop('title', settings.shortcutTitle)
				.appendTo(jDiv);

			jQuery.UpdateTaskbar();

			jQuery(img).click(function() {
				jQuery(jDivWin).HostCMSWindow('open')
					.HostCMSWindow('EnableResize')
					.HostCMSWindow('moveToTop');
			})
			.tooltip({position: 'bottom center', tipClass: 'taskbarTooltip'});

			jDivWin.HostCMSWindow({close: function(event, ui){
				if (jDivWin.HostCMSWindow("option", "WindowStatus") != 'minimized')
				{
					jDiv.remove();
					jQuery.UpdateTaskbar();
				}
			}});
		},
		openWindowAddTaskbar: function(settings)
		{
			settings = jQuery.extend({
				shortcutImg: null,
				shortcutTitle: 'undefined'
			}, settings);

			jQuery.addTaskbar(jQuery.openWindow(settings), settings);
		},
		cloneProperty: function(windowId, index)
		{
			var jProperies = jQuery('#' + windowId + ' #property_' + index),
			jNewObject = jProperies.eq(0).clone(),
			iRand = Math.floor(Math.random() * 999999);

			jNewObject.insertAfter(
				jQuery('#' + windowId).find('div[id="property_' + index + '"],div[id^="property_' + index + '_"]').eq(-1)
			);

			jNewObject.attr('id', 'property_' + index + '_' + iRand);

			// Change item_div ID
			jNewObject.find("div[id^='file_']").each(function(index, object){
				jQuery(object).prop('id', jQuery(object).prop('id') + '_' + iRand);
			});

			jNewObject.find("span[id^='file_large_settings_']").data('container', jNewObject.find("span[id^='file_large_settings_']").data('container') + '_' + iRand);
			jNewObject.find("span[id^='file_small_settings_']").data('container', jNewObject.find("span[id^='file_small_settings_']").data('container') + '_' + iRand);

			var aInputId = jProperies.eq(0).find("input[id ^= 'property_" + index + "_']").attr('id').split('property_'),
				suffix = aInputId[1],
				newContent = jNewObject
					.find("span[id^='file_large_settings_']")
					.data('content')
					.replace('_watermark_property_' + suffix, '_watermark_property_' + index + '_'+ iRand)
					.replace(new RegExp(suffix,'g'), index + '[]');

			jNewObject.find("span[id^='file_large_settings_']").attr('data-content', newContent);

			aInputId = jProperies.eq(0).find("input[id ^= 'small_property_" + index + "_']").attr('id').split('small_property_');
			suffix = aInputId[1];

			newContent = jNewObject.find("span[id ^= 'file_small_settings_']").data('content').replace('_watermark_small_property_' + suffix, '_watermark_small_property_' + index + '_'+ iRand).replace(new RegExp(suffix,'g'), index + '[]');
			jNewObject.find("span[id^='file_small_settings_']").attr('data-content', newContent);

			jNewObject.find("input[id^='field_id'],select,textarea").attr('name', 'property_' + index + '[]');
			jNewObject.find("div[id^='file_small'] input[id^='small_field_id']").attr('name', 'small_property_' + index + '[]').val('');
			jNewObject.find("input[id^='field_id'][type!=checkbox],input[id^='property_'][type!=checkbox],select,textarea").val('');

			jNewObject.find("input[id^='create_small_image_from_large_small_property']").attr('checked', true);

			// Change input name
			jNewObject.find(':regex(name, ^\\S+_\\d+_\\d+$)').each(function(index, object){
				var reg = /^(\S+)_(\d+)_(\d+)$/;
				var arr = reg.exec(object.name);
				jQuery(object).prop('name', arr[1] + '_' + arr[2] + '[]');
			});

			jNewObject.find("div.img_control div,div.img_control div").remove();
			jNewObject.find("input[type='text'].description-large").attr('name', 'description_property_' + index + '[]');
			jNewObject.find("input[type='text'].description-small").attr('name', 'description_small_property_' + index + '[]');

			jNewObject.find("img#delete").attr('onclick', "jQuery.deleteNewProperty(this)");

			if (jNewObject.datepicker)
			{
				jNewObject.find('input.hasDatepicker').attr('id', 'date_id_' + iRand).removeClass('hasDatepicker').datepicker();
			}
			else if(jNewObject.datetimepicker)
			{
				jNewObject.find('script').remove();
				jNewObject.find('div[id^="div_field_id_"]').datetimepicker({language: 'ru', useSeconds: true});
			}
			//jNewObject.find('input.hasDatepicker').attr('id', 'date_id_' + iRand).datepicker();

			// После внесения в DOM
			/*
 			jNewObject.find("a[onclick*='watermark_property_'],a[onclick*='watermark_small_property_']").each(function(index, object){
				var jObject = $(object), tmp = $(object).attr('onclick');
				jObject.attr('onclick', tmp.replace('_property_', '_property_' + iRand + '_'));
			});


			jNewObject.find("div[id*='watermark_property_'],div[id*='watermark_small_property_']").each(function(index, object){
				var jObject = $(object), tmp = $(object).attr('id');
				jObject.attr('id', tmp.replace('_property_', '_property_' + iRand + '_'));

				jObject.HostCMSWindow({ autoOpen: false, destroyOnClose: false, AppendTo: '#' + jNewObject.prop('id'), width: 360, height: 230, addContentPadding: true, modal: false, Maximize: false, Minimize: false });
			});

			jNewObject.find("div[aria-labelledby*='watermark_property_'],div[aria-labelledby*='watermark_small_property_']").each(function(index, object){
				var jObject = $(object), tmp = $(object).attr('aria-labelledby');
				jObject.attr('aria-labelledby', tmp.replace('_property_', '_property_' + iRand + '_'));
			});
			*/
		},
		preLoadImages: function()
		{
			var args_len = arguments.length;

			for (var i = args_len; i--;)
			{
				var cacheImage = document.createElement('img');
				cacheImage.src = arguments[i];
				cache.push(cacheImage);
			}
		},
		getCurrentWindowId: function()
		{
			return jQuery.data(document.body, 'currentWindowId');
		},
		reloadDesktop: function(siteId){
			jQuery('#tasksScroll .shortcut,.note,.mbmenu,.ui-dialog-content,#desktop').remove();
			jQuery('#subTaskBar .nav').css('display', 'none');
			$.loadingScreen('show');

			// add ajax '_'
			var data = jQuery.getData({});
			jQuery.ajax({
				context: jQuery('body'),
				url: hostcmsBackend + '/index.php?ajaxDesktopLoad&changeSiteId='+siteId,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(data){
					$.loadingScreen('hide');
					jQuery(this).append(data.form_html);
				}
			});
		},
		applyTooltip: function(windowId)
		{
			jQuery("#"+windowId+" acronym").tooltip({
				tipClass: 'shadowed tooltip',
				offset: [-30, 10],
				position: 'left center',
				predelay: 300,
				effect: 'fade',
				layout: '<div id="tooltip"/>',
				slideOffset: 30,
				onBeforeShow: function(event, position)
				{
					var trigger = this.getTrigger();
					var obj = this.getTip();
					var conf = this.getConf();

					var triggerOuterWidth = trigger.outerWidth();

					var delta = (obj.outerWidth() + triggerOuterWidth) / 2 - triggerOuterWidth + 10;

					// Заменяем смещение, чтобы подсказка была от начала строки
					conf.offset = [conf.offset[0], delta];

					// Оформление еще не было задано
					if (obj.find('.tl').size() === 0)
					{
						obj.applyShadow();

						// Tail
						jQuery('<div>').attr('class', 'shadow_tail')
						.css('left', "3px")
						.css('bottom', "-27px")
						.css('width', "13px")
						.append(jQuery('<img>').attr("src", hostcmsBackend + '/images/shadow_tail.gif'))
						.appendTo(obj);
					}
				}
			});
		},
		/*sendWindowStatus: function(event, ui)
		{
			jQuery.ajax({
				url: hostcmsBackend + '/index.php?' + 'userSettings&moduleId=' + ui.options.moduleId
					+ '&type=' + ui.options.type
					+ '&position_x=' + (ui.position.left + (ui.helper.outerWidth(true) - ui.helper.innerWidth()) / 2)
					+ '&position_y=' + (ui.position.top + (ui.helper.outerHeight(true) - ui.helper.innerHeight()) / 2)
					+ '&width=' + ui.helper.width() + '&height=' + ui.helper.height() + '&active=' + (event.type == 'hostcmswindowbeforeclose' ? 0 : 1),
				type: 'get',
				dataType: 'json',
				success: function(){}
			});
		},*/
		showTab: function(windowId, tabId)
		{
			$("#"+windowId+" div.tab_page[id!='" + tabId + "']").hideObjects();
			$("#"+windowId+" #" + tabId).showObjects();
			$("#"+windowId+" #tab ul li.li_tab").removeClass('current_li');
			$("#"+windowId+" #tab ul #li_"+tabId).addClass('current_li');
		},
		// Созание/пересоздание контекстного меню
		createContextualMenu: function(settings)
		{
			settings = jQuery.extend({
				iconPath: '',
				menuWidth: 200,
				openOnRight: true,
				overflow: 2,
				menuSelector: ".menuContainer",
				hasImages: true,
				fadeInTime: 10,
				fadeOutTime: 100,
				adjustLeft: 0,
				adjustTop: 0,
				submenuTop: 5,
				submenuLeft: 0,
				closeOnMouseOut: true,
				closeAfter: 100,
				onContextualMenu: function(o,e){},
				shadow: true
			}, settings);

			jQuery(document).buildContextualMenu(settings);
			// Удаляем открывшееся контекстное меню при пересоздании
			jQuery(".menuContainer").remove();
		}
	});

	jQuery.fn.extend({
		setLanguage: function(){
			jQuery("#languages img").removeClass('selected');
			jQuery(this).addClass('selected');
			jQuery("#language").val(jQuery(this).prop('alt'));
		},
		linkShortcut: function(settings)
		{
			settings = jQuery.extend({
				actionName: 'click' // dblclick
			}, settings);

			return this.each(function(index, object){
				jQuery(object)
				.unbind(settings.actionName)
				.bind(settings.actionName, function() {
					var jShortcutImg = jQuery(this).find('.shortcut img'),
					jshortcutLabel = jQuery(this).find('.shortcutLabel');
					if (jShortcutImg.length == 1) {settings.shortcutImg = jShortcutImg.prop('src');}
					if (jshortcutLabel.length == 1) {settings.shortcutTitle = jshortcutLabel.text();}

					jQuery.openWindowAddTaskbar(settings);
					return false;
				});

				jQuery(object).find('.shortcutLabel').bind("contextmenu", function(event){
						event.stopPropagation();
						event.cancelBubble=true;
					});

			});
		},
		applyShadow: function()
		{
			return this.each(function(index, object){
				var obj = $(object);

				obj.addClass('shadowed');
				$('<div>').attr('class', 'tl').appendTo(obj);
				$('<div>').attr('class', 't')
				.height(15)
				.appendTo(obj);

				$('<div>').attr('class', 'tr').appendTo(obj);
				$('<div>').attr('class', 'l')
				.width(17)
				.appendTo(obj);

				$('<div>').attr('class', 'r')
				.width(17)
				.appendTo(obj);

				$('<div>').attr('class', 'bl').appendTo(obj);

				$('<div>').attr('class', 'b')
				.height(21)
				.appendTo(obj);

				$('<div>').attr('class', 'br').appendTo(obj);
			});
		},
		// Скрытие объектов
		hideObjects: function()
		{
			return this.each(function(index, object){
				jQuery(object).css('zoom', 1).css('width', 0).css('height', 0).css('visibility', 'hidden').css('overflow', 'hidden');
			});
		},
		// Показ объектов
		showObjects: function()
		{
			return this.each(function(index, object){
				jQuery(object).css('zoom', 1).css('width', 'auto').css('height', 'auto').css('visibility', 'visible').css('overflow', 'visible');
			});
		},
		// Создание заметки
		createNote: function(settings)
		{
			settings = jQuery.extend({
					src: '/modules/skin/default/images/note.png',
					isNew: false
				}, settings);

			return this.each(function(index, object){

				var jObj = jQuery(object), text = jObj.text(), jDesktop = jQuery('#desktop');

				jObj.empty().addClass("cmVoice {cMenu: 'note_conext'} note");

				if (settings.isNew)
				{
					jObj.appendTo(jDesktop)
					.css({
						top: window.mouseYPos - jDesktop.position().top,
						left: window.mouseXPos - jDesktop.position().left
					});
				}

				jObj.draggable({containment: '#desktop', stop: function(event, ui){
					var reg = /note(\d+)/, arr = reg.exec(jQuery(this).prop('id'));

					jQuery.ajax({
						url: hostcmsBackend + '/index.php?' + 'userSettings&moduleId=0'
							+ '&type=98'
							+ '&position_x=' + ui.position.left
							+ '&position_y=' + ui.position.top
							+ '&entity_id=' + arr[1],
						type: 'get',
						dataType: 'json',
						success: function(){}
					});
				}})
				.on('change', function(){ // keydown
					var object = jQuery(this), timer = object.data('timer');

					if (timer){
						clearTimeout(timer);
					}

					jQuery(this).data('timer', setTimeout(function() {
							var reg = /note(\d+)/, arr = reg.exec(object.prop('id')),
							textarea = object.find('textarea').addClass('ajax');

							// add ajax '_'
							var data = jQuery.getData({});
							data['value'] = textarea.val();

							jQuery.ajax({
								context: textarea,
								url: hostcmsBackend + '/index.php?' + 'ajaxNote&action=save'
									+ '&entity_id=' + arr[1],
								type: 'POST',
								data: data,
								dataType: 'json',
								success: function(){
									this.removeClass('ajax');
								}
							});
						}, 1000)
					);
				})
				.append(jQuery('<img>').prop("src", settings.src))
				.append(jQuery('<textarea>').prop("src", settings.src).val(text))
				// Fix IE bug with transparent
				//.dblclick(function() {
				.click(function() {
					jQuery(this).children('textarea').focus();
				});

				if (settings.isNew)
				{
					// add ajax '_'
					var data = jQuery.getData({});

					jQuery.ajax({
						context: jObj,
						url: hostcmsBackend + '/index.php?ajaxCreateNote&position_x='+jObj.position().left+'&position_y='+jObj.position().top,
						data: data,
						dataType: 'json',
						type: 'POST',
						success: function(data) {
							this.prop('id', 'note'+data.form_html)
						}
					});
				}

				// Создаем контекстное меню
				jQuery.createContextualMenu();
			});
		},
		// Удаление заметки
		destroyNote: function()
		{
			return this.each(function(index, object){
				var reg = /note(\d+)/, arr = reg.exec(jQuery(object).prop('id'));

				jQuery.ajax({
					url: hostcmsBackend + '/index.php?' + 'ajaxNote&action=delete'
						+ '&entity_id=' + arr[1],
					type: 'get',
					dataType: 'json',
					success: function(){}
				});

				jQuery(object).remove();
			});
		}/*,
		widgetWindow: function(settings)
		{
			settings = jQuery.extend({
				autoOpen: true,
				Maximize: false,
				Minimize: false,
				Reload: true,
				type: 77,
				dragStop: jQuery.sendWindowStatus,
				resizeStop: jQuery.sendWindowStatus,
				beforeClose: jQuery.sendWindowStatus
			}, settings);

			return this.each(function(index, object){
				jQuery(object).HostCMSWindow(settings);
			});
		}*/
	});

	// Предварительная загрузка изображений
	var cache = [];
	$.preLoadImages(hostcmsBackend + "/images/shadow-b.png",
	hostcmsBackend + "/images/shadow-l.png",
	hostcmsBackend +"/images/shadow-lb.png",
	hostcmsBackend + "/images/shadow-lt.png",
	hostcmsBackend + "/images/shadow-r.png",
	hostcmsBackend + "/images/shadow-rb.png",
	hostcmsBackend + "/images/shadow-rt.png",
	hostcmsBackend + "/images/shadow-t.png",
	hostcmsBackend + "/images/ajax_loader.gif");

	$(document).keydown(function(event) {
		if (event.ctrlKey)
		{
			switch (event.which)
			{
				case 0x25: // Назад
				case 0x27: // Вперед
					var currentWindowId = $.getCurrentWindowId();
					if (event.which == 0x25) {
						$('#' + currentWindowId + ' #id_prev').click();
					}
					else {
						$('#' + currentWindowId + ' #id_next').click();
					}
				break;
			}
		}
	});

	$.fx.off = false;

})(jQuery);

var methods = {
	show: function() {
		$('body').css('cursor', 'wait');
		var fade_div = $('#ajaxLoader'), jWindow = $(window);
		if (fade_div.length === 0)
		{
			fade_div = $('<div></div>')
				.appendTo(document.body)
				.hide()
				.prop('id', 'ajaxLoader')
				.css('z-index', '1500')
				.css('position', 'absolute')
				.append($('<img>').prop('src', hostcmsBackend + '/images/ajax_loader.gif'));
		}

		fade_div.show()
			.css('top', (jWindow.height() - fade_div.outerHeight(true)) / 2 + jWindow.scrollTop())
			.css('left', (jWindow.width() - fade_div.outerWidth(true)) / 2 + jWindow.scrollLeft());
	},
	hide: function( ) {
		$('#ajaxLoader').hide().css('left', -1000);
		$('body').css('cursor', 'auto');
	}
};

jQuery.effects.mydrop = function(o) {
	return this.queue(function() {

		var msie = jQuery.browser.msie, el = jQuery(this),
			props = !msie ? ['position','top','left','opacity'] :  props = ['position','top','left'];

		// Set options
		var mode = jQuery.effects.setMode(el, o.options.mode || 'hide'), // Set Mode
			direction = o.options.direction || 'left'; // Default Direction

		// Adjust
		jQuery.effects.save(el, props);
		el.show(); // Save & Show

		jQuery.effects.createWrapper(el); // Create Wrapper
		var ref = (direction == 'up' || direction == 'down') ? 'top' : 'left';
		var motion = (direction == 'up' || direction == 'left') ? 'pos' : 'neg';
		var distance = o.options.distance || (ref == 'top' ? el.outerHeight({margin:true}) / 2 : el.outerWidth({margin:true}) / 2);
		if (mode == 'show')
		{
			if (!msie)
			{
				el.css('opacity', 0).css(ref, motion == 'pos' ? -distance : distance); // Shift
			}
		}

		// Animation
		var animation = !msie ? {opacity: mode == 'show' ? 1 : 0} : {};

		animation[ref] = (mode == 'show' ? (motion == 'pos' ? '+=' : '-=') : (motion == 'pos' ? '-=' : '+=')) + distance;

		// Animate
		el.animate(animation, { queue: false, duration: o.duration, easing: o.options.easing, complete: function() {
			if(mode == 'hide') {el.hide();} // Hide
			jQuery.effects.restore(el, props);
			jQuery.effects.removeWrapper(el); // Restore
			if(o.callback) {o.callback.apply(this, arguments);} // Callback
			el.dequeue();
		}});

	});
};


// Кроссбраузерная функция получения размеров экрана,
// используется в функции ShowLoadingScreen.
function getPageSize()
{
	var xScroll, yScroll;

	if (window.innerHeight && window.scrollMaxY)
	{
		xScroll = window.innerWidth + window.scrollMaxX;
		yScroll = window.innerHeight + window.scrollMaxY;
	}
	else if (document.body.scrollHeight > document.body.offsetHeight)
	{ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	}
	else
	{ // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and
		// Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight, pageHeight, pageWidth;
	if (self.innerHeight)
	{	// all except Explorer
		if(document.documentElement.clientWidth)
		{
			windowWidth = document.documentElement.clientWidth;
		}
		else
		{
			windowWidth = self.innerWidth;
		}
		windowHeight = self.innerHeight;
	}
	else if (document.documentElement && document.documentElement.clientHeight)
	{ // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	}
	else if (document.body)
	{ // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight)
	{
		pageHeight = windowHeight;
	}
	else
	{
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth)
	{
		pageWidth = xScroll;
	}
	else
	{
		pageWidth = windowWidth;
	}

	return new Array(pageWidth, pageHeight, windowWidth, windowHeight);
}

// Получение информации о позиции скрола
function getScrollXY()
{
	var scrOfX = 0, scrOfY = 0;

	if (typeof(window.pageYOffset ) == 'number' )
	{
		// Netscape
		scrOfY = window.pageYOffset;
		scrOfX = window.pageXOffset;
	}
	else if (document.body && (document.body.scrollLeft || document.body.scrollTop))
	{
		// DOM
		scrOfY = document.body.scrollTop;
		scrOfX = document.body.scrollLeft;
	}
	else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop))
	{
		// IE6
		scrOfY = document.documentElement.scrollTop;
		scrOfX = document.documentElement.scrollLeft;
	}

	return [ scrOfX, scrOfY ];
}

// Скрытие div-а
function HideObject(obj){
	return obj.hideObjects();
}

// Показ div-а
function ShowObject(obj){
	return obj.showObjects();
}

// =============================================
// Функции работы с меню
// =============================================
changeFontSizeTimer = new Array();
menuTimer = new Array();

function HostCMSMenuOver(CurrenElementId, LevelMenu, ChildId)
{
	clearTimeout(menuTimer[CurrenElementId]);

	var CurrenElement = $("#"+CurrenElementId);

	if (CurrenElement.length > 0)
	{
		var jChild = $("#"+ChildId);

		// Оформление еще не было задано
		if (jChild.find('.tl').size() === 0)
		{
			jChild.applyShadow();
		}

		decor(CurrenElement, LevelMenu);

		jChild.css('display', 'block');
	}
}

function HostCMSMenuOut(CurrenElementId, LevelMenu, ChildId)
{
	menuTimer[CurrenElementId] = setTimeout(function(){
		var CurrenElement = $("#"+CurrenElementId);

		if (CurrenElement.length > 0)
		{
			unDecor(CurrenElement, LevelMenu);

			var jChild = $("#"+ChildId);
			jChild.css('display', 'none');
		}
	}, 50);
}

// Функция визуального оформления элементов меню
function decor(CurrenElement, LevelMenu)
{
	if (LevelMenu == 1) // для первого уровня вложенности
	{
		CurrenElement
			.css('background-image', "url('" + hostcmsBackend + "/images/line3.gif')")
			.css('background-repeat', 'repeat-x')
			.css('background-position', '0 100%');

		var child = CurrenElement.children;
		var CurrenElementId = CurrenElement.attr('id');

		if (changeFontSizeTimer[CurrenElementId] != '')
		{
			clearTimeout(changeFontSizeTimer[CurrenElementId]);
		}
		changeFontSize(CurrenElement.attr('id'), 1, 13);
	}
}

// Функция визуального оформления элементов меню
function unDecor(CurrenElement, LevelMenu)
{
	if (LevelMenu==1)
	{
		var CurrenElementId = CurrenElement.attr('id');

		clearTimeout(changeFontSizeTimer[CurrenElementId]);

		CurrenElement
			.css('background-image', "url('" + hostcmsBackend + "/images/line1.gif')")
			.css('background-repeat', 'repeat-x')
			.css('background-position', '0 100%');

		changeFontSize(CurrenElement.attr('id'), -1, 10);
	}
}

// Функции оформления
function changeFontSize(CurrenElementId, change, limit)
{
	var CurrenElement = document.getElementById(CurrenElementId);

	if (CurrenElement)
	{
		var CurrFontSize = CurrenElement.style.fontSize ? parseInt(CurrenElement.style.fontSize) : 10;
		if (CurrFontSize != limit)
		{
			CurrenElement.style.fontSize = (CurrFontSize + change) + 'pt';
			changeFontSizeTimer[CurrenElementId] = setTimeout('changeFontSize("'+CurrenElementId+'", '+change+', '+limit+')', 1);
		}
	}
}

/**
 * Создание окна
 *
 * @param windowId идентификатор окна
 * @param windowTitle заголовок окна
 * @param windowWidth ширина окна
 * @param windowHeight высота окна
 * @param type тип закрытия окна, 0 - скрыть, 1 - уничтожить
 */
function CreateWindow(windowId, windowTitle, windowWidth, windowHeight, typeClose)
{
	var removeWindow = (typeof typeClose != 'undefined' && typeClose == 1);

	var windowDiv = document.getElementById(windowId);

	if (windowDiv == undefined)
	{
		// Создаем div для окна
		var fade_div = document.createElement("div");
		fade_div.setAttribute("id", windowId);
		var body = document.getElementsByTagName("body")[0];
		windowDiv = body.appendChild(fade_div);
	}

	if (windowWidth == '')
	{
		windowWidth = '300px';
	}

	windowDiv.style.width = windowWidth;

	if (windowHeight != '')
	{
		windowDiv.style.height = windowHeight;
	}

	$(windowDiv).applyShadow();

	// Верхняя полосочка(для отображения пустого заголовка передать пробел)
	if(windowTitle != '')
	{
		var topbar = document.createElement("div");
		topbar.className = "topbar";
		windowDiv.insertBefore(topbar, windowDiv.childNodes[0]);
	}

	windowDiv.style.display = 'none';

	// Закрыть
	var wclose_img = document.createElement("img");
	wclose_img.src = hostcmsBackend + '/images/wclose.gif';

	windowId = windowId.replace('[','\\[').replace(']','\\]');

	if (removeWindow)
	{
		wclose_img.onclick = function() { $("#"+windowId).remove(); };
	}
	else
	{
		wclose_img.onclick = function() {HideWindow(windowId); };
	}

	if(windowTitle != '')
	{
		topbar.appendChild(wclose_img);

		// Заголовок окна
		var textNode = document.createTextNode(windowTitle);
		topbar.appendChild(textNode);
	}
}

// Отображает/скрывает окно
function SlideWindow(windowId)
{
	if ($("#"+windowId).css("display") == 'block')
	{
		HideWindow(windowId);
	}
	else
	{
		ShowWindow(windowId);
	}
}

var prev_window = 0;

function ShowWindow(windowId)
{
	var windowDiv = document.getElementById(windowId);

	if (windowDiv == undefined)
	{
		return false;
	}

	if (prev_window && prev_window != windowId)
	{
		HideWindow(prev_window);
	}

	prev_window = windowId;

	// 0 - pageWidth, 1 - pageHeight, 2 - windowWidth, 3 - windowHeight
	var arrayPageSize = getPageSize();

	// 0 - scrOfX, 1 - scrOfY
	var arrayScrollXY = getScrollXY();

	// Отображаем до определения размеров div-а
	windowDiv.style.display = 'block';

	var clientHeight = windowDiv.clientHeight;
	var clientWidth = windowDiv.clientWidth;

	// Если высота div-а больше высоты окна
	if (clientHeight > arrayPageSize[3])
	{
		// Положим высоту равной 90% высоты окна
		clientHeight = Math.round(arrayPageSize[3] * 0.9);
	}

	// Если ширина div-а больше ширины окна
	if (clientWidth > arrayPageSize[2])
	{
		// Положим ширину равной 90% высоты окна
		clientWidth = Math.round(arrayPageSize[2] * 0.9);
	}

	windowDiv.style.top = ((arrayPageSize[3] - clientHeight) / 2 + arrayScrollXY[1]) + 'px';
	windowDiv.style.left = ((arrayPageSize[2] - clientWidth) / 2 + arrayScrollXY[0]) + 'px';
}

function HideWindow(windowId)
{
	$("#"+windowId).css('display', 'none');
}

// Функции для всплывающего блока
function copyright_position(copyright)
{
	var windowDiv = document.getElementById(copyright);
	windowDiv.style.top = 'auto';
	windowDiv.style.left = 'auto';
	windowDiv.style.bottom = 55 + 'px';
	windowDiv.style.right = 25 + 'px';
	clear_timeout_copiright();
}

function clear_timeout_copiright()
{
	clearTimeout(timeout_copiright);
}

function set_timeout_copyright()
{
	timeout_copiright = setTimeout(function(){HideWindow('copyright')}, 500);
}

// --- [Menu] ---
var action = '', aHeights = [];

function SubMenu(divId)
{
	if (action == '')
	{
		var data = {'_': Math.round(new Date().getTime())}, subDivHeight = $("div[id="+divId+"]").height(),
			reg = /id_(\d+)/;

		if ($("div[id="+divId+"]").height() === 0)
		{
			action = 'showing';
			ShowSubMenu(divId, aHeights[divId]);

			var arr = reg.exec(divId);
			data['show_sub_menu'] = arr[1];
		}
		else
		{
			action = 'hiding';
			aHeights[divId] = subDivHeight;

			HideSubMenu(divId);

			var arr = reg.exec(divId);
			data['hide_sub_menu'] = arr[1];
		}

		$.ajax({
			url: hostcmsBackend + '/index.php',
			data: data,
			type: 'POST',
			dataType: 'json',
			success: function(){}
		});
	}
}

function ShowSubMenu (divId, maxHeight)
{
	$("div[id="+divId+"]").animate({
		height: maxHeight,
		opacity: 1.0}, {
		duration: 'normal',
		complete: function(){
			action = '';
		}
	});
}

function HideSubMenu(divId)
{
	$("div[id="+divId+"]").animate({
		height: 0,
		opacity: 0}, {
		duration: 'normal',
		complete: function(){
			action = '';
		}
	});
}
