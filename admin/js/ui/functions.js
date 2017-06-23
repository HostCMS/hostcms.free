(function($) {

// Без создания коллекции
jQuery.extend({
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
			.append('<img src="/admin/images/ajax_loader.gif" style="position: absolute; left: 50%; top: 50%" />')
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
	}
});

jQuery.fn.extend(
{
	linkShortcut: function(settings) {
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
	}
});

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
})(jQuery);