/*
 * jQuery UI Dialog 1.8.x
 *
 * Copyright (c) 2010 AUTHORS.txt (http://jqueryui.com/about)
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * http://docs.jquery.com/UI/Dialog
 *
 * http://jqueryui.com/demos/dialog/
 *
 * Depends:
 *	jquery.ui.core.js
 *	jquery.ui.widget.js
 *  jquery.ui.button.js
 *	jquery.ui.draggable.js
 *	jquery.ui.mouse.js
 *	jquery.ui.position.js
 *	jquery.ui.resizable.js
 */
(function(jQuery) {

var uiDialogClasses =
	'ui-dialog ' +
	'ui-widget ' +
	'ui-widget-content ' +
	'ui-corner-all ';

jQuery.widget("ui.HostCMSWindow", {
	options: {
		autoOpen: true,
		buttons: {},
		closeOnEscape: false,
		closeText: 'close',
		dialogClass: '',
		draggable: true,

		// Для хранения позиций окна
		moduleId: 0,
		type: 0,

		destroyOnClose: true,
		hide: 'mydrop', //'drop', // 'blind'
		hideDirection: {direction: "up"},
		showDirection: {direction: "up", mode: "show"},
		speed: 'fast', // 'normal',
		maxHeight: false,
		maxWidth: false,
		minHeight: 50,
		minWidth: 120,
		modal: false,
		position: 'center',
		resizable: true,
		show: null,
		stack: true,
		title: '',
		// add
		positionOf: window,
		AppendTo: 'body',
		Animation: 'easeOutCubic',
		WindowStatus: 'regular',
		path: '',

		Close: true,
		Maximize: true,
		Minimize: true,
		Reload: false,

		// Показывать заголовок окна
		showTitle: true,

		prevWidth: null,
		prevHeight: null,
		prevPosition: null,

		addContentPadding: false,

		// Позиции при расскрытии на весь экран
		MaximizeLeft: 30, // 0
		MaximizeTop: 70,

		// Изменении размера окна при расскрытии на весь экран относительно размеров окна бразуера
		MaximizeWidthDelta: 60,
		MaximizeHeightDelta: 88, //48

		// Default shortcut
		ShortcutSmall: '',
		ShortcutLarge: '',

		uiDialogTitlebar: null,

		/*resizeStop: function(event, ui) {
			//ui.resizable('option', 'minHeight', this._minHeight());
		},*/

		/*Possible values:
		1) a single string representing position within viewport: 'center', 'left', 'right', 'top', 'bottom'.
		2) an array containing an x,y coordinate pair in pixel offset from left, top corner of viewport (e.g. [350,100])
		3) an array containing x,y position string values (e.g. ['right','top'] for top right corner).*/
		width: jQuery(window).width() * 0.7, //700,
		height: jQuery(window).height() * 0.7, // height: 'auto',
		zIndex: 1000
	},
	_create: function() {

		//console.log('_create begin');
		this.originalTitle = this.element.attr('title');

		// add
		// Сохраняем первоначальные позиции
		this.options.prevWidth = this.options.width;
		this.options.prevHeight = this.options.height;
		//this.options.prevPosition = this.options.position;
		// --add

		var self = this,
			options = self.options,

			title = options.title || self.originalTitle || '&#160;',
			titleId = jQuery.ui.dialog.getTitleId(self.element),

			uiDialog = (self.uiDialog = jQuery('<div></div>'))
				//.appendTo(document.body)
				.appendTo(self.options.AppendTo)
				.hide()
				.addClass(uiDialogClasses + options.dialogClass)
				.css({
					zIndex: options.zIndex
				})
				// setting tabIndex makes the div focusable
				// setting outline to 0 prevents a border on focus in Mozilla
				.attr('tabIndex', -1).css('outline', 0).keydown(function(event) {
					if (options.closeOnEscape && event.keyCode &&
					event.keyCode === jQuery.ui.keyCode.ESCAPE) {
						self.close(event);
						event.preventDefault();
					}
				})
				.attr({
					role: 'dialog',
					'aria-labelledby': titleId
				})
				.mousedown(function(event) {
					self.moveToTop(false, event);
				}),
			uiDialogContent = self.element
				.show()
				.removeAttr('title')
				.addClass(
					'ui-dialog-content ' +
					'ui-widget-content')
				.appendTo(uiDialog)
				;

		// add
		self.uiDialogContent = uiDialogContent;

		// add
		// Отступ для окон
		if (options.addContentPadding){
			uiDialogContent.addClass('contentpadding');
		}

		if (options.showTitle)
		{
			var uiDialogTitlebar = (self.uiDialogTitlebar = jQuery('<div></div>'))
				.addClass(
					'ui-dialog-titlebar ' +
					'ui-widget-header ' +
					'ui-corner-all ' +
					'ui-helper-clearfix'
				)
				.prependTo(uiDialog);

			if (options.Close)
			{
				uiDialogTitlebarClose = jQuery('<a></a>')
					.addClass(
						'ui-dialog-titlebar-close ' +
						'ui-corner-all'
					)
					.attr('role', 'button')
					/*.hover(
						function() {
							uiDialogTitlebarClose.addClass('ui-state-hover');
						},
						function() {
							uiDialogTitlebarClose.removeClass('ui-state-hover');
						}
					)*/
					.focus(function() {
						uiDialogTitlebarClose.addClass('ui-state-focus');
					})
					.blur(function() {
						uiDialogTitlebarClose.removeClass('ui-state-focus');
					})
					.click(function(event) {
						self.close(event);
						return false;
					})
					.appendTo(uiDialogTitlebar),

				uiDialogTitlebarCloseText = (self.uiDialogTitlebarCloseText = jQuery('<span></span>'))
					.addClass(
						'ui-icon ' +
						'ui-icon-closethick'
					)
					.text(options.closeText)
					.appendTo(uiDialogTitlebarClose);
			}

			var uiDialogTitle = jQuery('<span></span>')
				.addClass('ui-dialog-title')
				.attr('id', titleId)
				.html(title)
				.prependTo(uiDialogTitlebar);

			// add
			if (options.Reload)
			{
				var uiDialogTitlebarReload = jQuery('<a></a>') // href="#"
					.addClass(
						'ui-dialog-titlebar-reload ' +
						'ui-corner-all'
					)
					.attr('role', 'button')
					.focus(function() {
						uiDialogTitlebarReload.addClass('ui-state-focus');
					})
					.blur(function() {
						uiDialogTitlebarReload.removeClass('ui-state-focus');
					})
					.click(function(event) {
						self.reload(event);
						return false;
					})
					.appendTo(uiDialogTitlebar),

				uiDialogTitlebarCloseText = (self.uiDialogTitlebarCloseText = jQuery('<span></span>'))
					.addClass(
						'ui-icon ' +
						'ui-icon-reload'
					)
					.text(options.closeText)
					.appendTo(uiDialogTitlebarReload);
			}

			// add
			if (options.Maximize)
			{
				// Двойной клик с заголовка
				uiDialogTitlebar.dblclick(function(event) {
					self.MaximizeMinimize(event);
					return false;
				});

				var uiDialogTitlebarMaximize = jQuery('<a></a>')
					.addClass(
						'ui-dialog-titlebar-maximize ' +
						'ui-corner-all'
					)
					.attr('role', 'button')
					.focus(function() {
						uiDialogTitlebarMaximize.addClass('ui-state-focus');
					})
					.blur(function() {
						uiDialogTitlebarMaximize.removeClass('ui-state-focus');
					})
					.click(function(event) {
						self.MaximizeMinimize(event);
						return false;
					})
					.appendTo(uiDialogTitlebar),

				uiDialogTitlebarCloseText = (self.uiDialogTitlebarCloseText = jQuery('<span></span>'))
					.addClass(
						'ui-icon ' +
						'ui-icon-maximize'
					)
					.text(options.closeText)
					.appendTo(uiDialogTitlebarMaximize);
			}

			if (options.Minimize)
			{
				var uiDialogTitlebarMinimize = jQuery('<a></a>')
					.addClass(
						'ui-dialog-titlebar-minimize ' +
						'ui-corner-all'
					)
					.attr('role', 'button')
					.focus(function() {
						uiDialogTitlebarMinimize.addClass('ui-state-focus');
					})
					.blur(function() {
						uiDialogTitlebarMinimize.removeClass('ui-state-focus');
					})
					.click(function(event) {
						self.Minimize(event);
						return false;
					})
					.appendTo(uiDialogTitlebar),

				uiDialogTitlebarCloseText = (self.uiDialogTitlebarCloseText = jQuery('<span></span>'))
					.addClass(
						'ui-icon ' +
						'ui-icon-minimize'
					)
					.text(options.closeText)
					.appendTo(uiDialogTitlebarMinimize);

				// add
				self.uiDialogTitlebar = uiDialogTitlebar;
			}
			// -- add

			//handling of deprecated beforeclose (vs beforeClose) option
			//Ticket #4669 http://dev.jqueryui.com/ticket/4669
			//TODO: remove in 1.9pre
			if (jQuery.isFunction(options.beforeclose) && !jQuery.isFunction(options.beforeClose)) {
				options.beforeClose = options.beforeclose;
			}

			uiDialogTitlebar.find("*").add(uiDialogTitlebar).disableSelection();
			// add
		}
		// -- add

		if (options.draggable && jQuery.fn.draggable) {
			self._makeDraggable();
		}

		// add
		self._makeResizable();
		// -- add

		if (!options.resizable && jQuery.fn.resizable) {
			//self._makeResizable();
			self.DisableResize();
		}

		self._createButtons(options.buttons);
		self._isOpen = false;

		if (jQuery.fn.bgiframe) {
			uiDialog.bgiframe();
		}

		//console.log('_create end');
	},
	_init: function() {
		//console.log('_init begin');
		if ( this.options.autoOpen ) {
			this.open();
		}
		//console.log('_init end');
	},
	reload: function() {
		//console.log('reload begin');
		var self = this;

		self.uiDialogTitlebar.find('span.ui-icon-reload').addClass('ui-icon-ajax');

		// add ajax '_'
		var data = jQuery.getData({});
		jQuery.ajax({
			context: this.uiDialogContent,
			url: this.options.path,
			data: data,
			dataType: 'json',
			type: 'POST',
			success: [jQuery.ajaxCallback, function(){ self.uiDialogTitlebar.find('span.ui-icon-reload').removeClass('ui-icon-ajax'); }]
		});
		//console.log('reload end');
	},
	destroy: function() {
		//console.log('destroy');
		var self = this;

		if (self.overlay) {
			self.overlay.destroy();
		}
		self.uiDialog.hide();

		self.element
			.unbind('.dialog')
			.removeData('dialog')
			.removeClass('ui-dialog-content ui-widget-content')
			.hide()
			//.appendTo('body')
			.appendTo(self.options.AppendTo);

		self.uiDialog.remove();

		if (self.originalTitle) {
			self.element.attr('title', self.originalTitle);
		}

		return self;
	},
	widget: function() {
		return this.uiDialog;
	},
	MaximizeMinimize: function(event) {
		//console.log('MaximizeMinimize');
		var self = this;

		switch (self.options.WindowStatus) {
			case 'regular':
			  self.Maximize();
			break;
			case 'maximized':
			  self.Regular();
			break;
			case 'minimized':
			  self.Regular();
			break;
			default:
			break;
		  }
	},
	// Сохранение позиций перед мини- и максимизацией
	SavePosition: function() {
		//console.log('SavePosition');
		var self = this;

		// Позиция считается относительно uiDialog
		this.options.prevPosition = self.uiDialog.position();

		// Размеры относительно uiDialogContent
		this.options.prevWidth = self.uiDialogContent.width();
		this.options.prevHeight = self.uiDialogContent.height();
	},
	DisableResize: function() {
		//console.log('DisableResize');
		var self = this;

		// Disable resize
		self.uiDialog.draggable('disable');
		self.uiDialog.resizable('disable');

		// Удаляем класс полупрозрачности
		//self.uiDialog.removeClass('ui-state-disabled');
		self.uiDialog.removeClass('ui-resizable-disabled');
	},
	EnableResize: function() {
		//console.log('EnableResize');
		var self = this;

		// Enable resize
		self.uiDialog.draggable('enable');
		self.uiDialog.resizable('enable');
	},
	Maximize: function() {
		//console.log('Maximize');
		var self = this, jWindow = jQuery(window);

		var uiDialogContentouterWidth = self.uiDialogContent.outerWidth(true),
		mywidth = jQuery(window).width() - self.options.MaximizeWidthDelta
			+ self.uiDialogContent.width()
			- uiDialogContentouterWidth - 26,
		myheight = jQuery(window).height() - self.options.MaximizeHeightDelta
			+ self.uiDialogContent.height()
			- self.uiDialogContent.outerHeight(true) - 69;

		self.SavePosition();
		self.uiDialog.css('visibility', 'visible');
		self.uiDialog.css('height', 'auto');

		// Чтобы uiDialog после width: auto не расползался
		self.uiDialogContent.css('width', uiDialogContentouterWidth);
		self.uiDialog.css('width', 'auto');

		// Disable resize
		self.DisableResize();

		self.uiDialogContent.animate({
			width: mywidth,
			height: myheight}, {
			//queue: false, // Эффект выполняется вне очереди параллельно со следующим
			duration: self.options.speed,
			//easing: self.options.Animation,
			complete: function(){
			/*self.options.width = mywidth;
			self.options.height = myheight;
			self._size();*/ }
		}); //.css('overflow', 'auto'); // overflow: auto чтобы не исчезали бегунки при ресайзе

		//Set new Window Position
		self.uiDialog.animate({
			top: self.options.MaximizeTop,
			left: self.options.MaximizeLeft}, {
			//easing: self.options.Animation,
			duration: self.options.speed,
			complete: function(){
				self.fixWindowSize();
			}
		});

		self.options.WindowStatus = 'maximized';
		return(false);
	},
	Regular: function() {
		//console.log('Regular');
		//alert('Regular');
		var self = this;

		self.uiDialog.css('visibility', 'visible');
		self.uiDialog.css('height', 'auto');
		// Чтобы uiDialog после width: auto не расползался
		//self.uiDialogContent.css('width', self.uiDialogContent.outerWidth(true));
		self.uiDialog.css('width', 'auto');

		self.uiDialogContent.animate({
			width: self.options.prevWidth,
			height: self.options.prevHeight}, {
			queue: false, // Эффект выполняется вне очереди параллельно со следующим
			duration: self.options.speed,
			//easing: self.options.Animation,
			complete: function(){
				self.uiDialog.width(self.uiDialog.width());
				self.uiDialog.height(self.uiDialog.height());
			}
		}).css('overflow', 'auto'); // overflow: auto чтобы не исчезали бегунки при ресайзе

		//Set new Window Position
		self.uiDialog.animate({
			top: self.options.prevPosition.top,
			left: self.options.prevPosition.left}, {
			//easing: self.options.Animation,
			duration: self.options.speed
		});

		// Enable resize
		self.EnableResize();

		self.options.WindowStatus = 'regular';
		return(false);
	},
	Minimize: function() {
		//console.log('Minimize');
		var self = this;

		self.SavePosition();
		self.options.WindowStatus = 'minimized';
		self.DisableResize();
		self.disableTinyMCE();
		self.close();

		return(false);
	},
	fixWindowSize: function() {
		//console.log('fixWindowSize');
		var self = this;
		self.uiDialog.width(self.uiDialog.width()).height(self.uiDialog.height());
	},
	disableTinyMCE: function() {
		//console.log('disableTinyMCE');
		var self = this;
		if (typeof tinyMCE != 'undefined')
		{
			self.uiDialogContent.find('textarea').each(function(){
				if (tinyMCE.getInstanceById(this.id) != null)
				{
					tinyMCE.execCommand('mceRemoveControl', false, this.id);
				}
			});
		}
	},
	enableTinyMCE: function() {
		//console.log('enableTinyMCE');
		var self = this;

		if (typeof tinyMCE != 'undefined')
		{
			self.uiDialogContent.find('textarea[wysiwyg=1]').each(function(){
				if (tinyMCE.getInstanceById(this.id) == null)
				{
					tinyMCE.execCommand('mceAddControl', false, this.id);
				}
			});
		}
	},
	close: function(event) {
		//console.log('close');
		var self = this,
			maxZ, thisZ;

		if (false === self._trigger('beforeClose', event, {
			position: self.uiDialog.position(),
			offset: self.uiDialog.offset(),
			helper: self.uiDialog,
			options: self.options
		})) { return; }

		if (self.overlay) {
			self.overlay.destroy();
		}
		self.uiDialog.unbind('keypress.ui-dialog');
		self._isOpen = false;

		if (self.options.WindowStatus != 'minimized' && self.options.destroyOnClose)
		{
			jQuery.beforeContentLoad(self.element);
		}

		// При fx.off == true функция callback для 4-го аргумента hide() не вызывается
		if (self.options.hide)
		{
			self.fixWindowSize();

			if (!jQuery.fx.off) {
				var hideDirection = (self.options.WindowStatus == 'minimized' || !self.options.destroyOnClose) ? self.options.hideDirection : {direction: "left"};

				self.uiDialog.hide(self.options.hide, hideDirection, self.options.speed, function() {
					self._trigger('close', event);

					if (self.options.WindowStatus != 'minimized' && self.options.destroyOnClose)
					{
						// Уничтожаем окно
						self.destroy();
						self.element.remove();
					}
				});
			} else {
				self.uiDialog.hide();
				self._trigger('close', event);

				if (self.options.WindowStatus != 'minimized' && self.options.destroyOnClose)
				{
					// Уничтожаем окно
					self.destroy();
					self.element.remove();
				}
			}
		}

		jQuery.ui.dialog.overlay.resize();

		// adjust the maxZ to allow other modal dialogs to continue to work (see #4309)
		if (self.options.modal) {
			maxZ = 0;
			jQuery('.ui-dialog').each(function() {
				if (this !== self.uiDialog[0]) {
					thisZ = jQuery(this).css('z-index');
					if(!isNaN(thisZ)) {
						maxZ = Math.max(maxZ, thisZ);
					}
				}
			});
			jQuery.ui.dialog.maxZ = maxZ;
		}

		return self;
	},
	isOpen: function() {
		//console.log('isOpen');
		return this._isOpen;
	},
	// the force parameter allows us to move modal dialogs to their correct
	// position on open
	moveToTop: function(force, event) {
		//console.log('moveToTop');
		var self = this,
			options = self.options,
			saveScroll;

		if ((options.modal && !force) ||
			(!options.stack && !options.modal)) {
			return self._trigger('focus', event);
		}

		if (options.zIndex > jQuery.ui.dialog.maxZ) {
			jQuery.ui.dialog.maxZ = options.zIndex;
		}
		if (self.overlay) {
			jQuery.ui.dialog.maxZ += 1;
			self.overlay.$el.css('z-index', jQuery.ui.dialog.overlay.maxZ = jQuery.ui.dialog.maxZ);
		}

		//Save and then restore scroll since Opera 9.5+ resets when parent z-Index is changed.
		//  http://ui.jquery.com/bugs/ticket/3193
		saveScroll = { scrollTop: self.element.scrollTop(), scrollLeft: self.element.scrollLeft() };
		jQuery.ui.dialog.maxZ += 1;
		self.uiDialog.css('z-index', jQuery.ui.dialog.maxZ);
		self.element.attr(saveScroll);
		self._trigger('focus', event);

		return self;
	},
	open: function() {
		//alert('open');
		//console.log('open');

		if (this._isOpen) { return; }

		var self = this,
			options = self.options,
			uiDialog = self.uiDialog;

		self.overlay = options.modal ? new jQuery.ui.dialog.overlay(self) : null;
		if (uiDialog.next().length) {
			//uiDialog.appendTo('body');
			uiDialog.appendTo(options.AppendTo);
		}

		// add
		// Убираем наведение со всех кнопок uiDialogTitlebar
		//self.uiDialogTitlebar.find(".ui-state-hover").removeClass('ui-state-hover');

		self._size();
		self._position(options.position);

		var show = (self.options.WindowStatus == 'minimized') ? options.hide : options.show;

		if (!jQuery.fx.off) {
			var showDirection = (self.options.WindowStatus == 'minimized') ? self.options.showDirection : {};
			uiDialog.show(show, showDirection, self.options.speed, function() {});
		} else {
			uiDialog.show();
		}

		self.moveToTop(true);

		// prevent tabbing out of modal dialogs
		if (options.modal) {
			uiDialog.bind('keypress.ui-dialog', function(event) {
				if (event.keyCode !== jQuery.ui.keyCode.TAB) {
					return;
				}

				var tabbables = jQuery(':tabbable', this),
					first = tabbables.filter(':first'),
					last  = tabbables.filter(':last');

				if (event.target === last[0] && !event.shiftKey) {
					first.focus(1);
					return false;
				} else if (event.target === first[0] && event.shiftKey) {
					last.focus(1);
					return false;
				}
			});
		}

		// set focus to the first tabbable element in the content area or the first button
		// if there are no tabbable elements, set focus on the dialog itself
		/*jQuery([])
			.add(uiDialog.find('.ui-dialog-content :tabbable:first'))
			.add(uiDialog.find('.ui-dialog-buttonpane :tabbable:first'))
			.add(uiDialog)
			.filter(':first')
			.focus();*/

		self._trigger('open');
		self._isOpen = true;

		// add
		self.fixWindowSize();
		options.WindowStatus = 'regular';
		self.enableTinyMCE();

		return self;
	},
	_createButtons: function(buttons) {
		//console.log('_createButtons');
		var self = this,
			hasButtons = false,
			uiDialogButtonPane = jQuery('<div></div>')
				.addClass(
					'ui-dialog-buttonpane ' +
					'ui-widget-content ' +
					'ui-helper-clearfix'
				);

		// if we already have a button pane, remove it
		self.uiDialog.find('.ui-dialog-buttonpane').remove();

		if (typeof buttons === 'object' && buttons !== null) {
			jQuery.each(buttons, function() {
				return !(hasButtons = true);
			});
		}
		if (hasButtons) {
			jQuery.each(buttons, function(name, fn) {
				var button = jQuery('<button type="button"></button>')
					.text(name)
					.click(function() { fn.apply(self.element[0], arguments); })
					.appendTo(uiDialogButtonPane);
				if (jQuery.fn.button) {
					button.button();
				}
			});
			uiDialogButtonPane.appendTo(self.uiDialog);
		}
	},
	_makeDraggable: function() {
		//console.log('_makeDraggable');
		var self = this,
			options = self.options,
			doc = jQuery(document),
			heightBeforeDrag;

		function filteredUi(ui) {
			return {
				position: ui.position,
				offset: ui.offset,
				helper: ui.helper,
				options: self.options
			};
		}

		// add
		var handle = options.showTitle ? '.ui-dialog-titlebar' : '.ui-dialog';
		// --add

		//self.uiDialog.liveDraggable({
		self.uiDialog.draggable({
			cancel: '.ui-dialog-titlebar-close', /*'.ui-dialog-content, .ui-dialog-titlebar-close',*/
			handle: handle/*'.ui-dialog-titlebar'*/,
			containment: 'document',
			start: function(event, ui) {
				heightBeforeDrag = options.height === "auto" ? "auto" : jQuery(this).height();
				jQuery(this).height(jQuery(this).height()).addClass("ui-dialog-dragging");
				self._trigger('dragStart', event, filteredUi(ui));
			},
			drag: function(event, ui) {
				self._trigger('drag', event, filteredUi(ui));
			},
			stop: function(event, ui) {
				options.position = [ui.position.left - doc.scrollLeft(),
					ui.position.top - doc.scrollTop()];
				jQuery(this).removeClass("ui-dialog-dragging").height(heightBeforeDrag);
				self._trigger('dragStop', event, filteredUi(ui));
				jQuery.ui.dialog.overlay.resize();
			}
		});
	},
	_makeResizable: function(handles) {
		//console.log('_makeResizable');

		handles = (handles === undefined ? this.options.resizable : handles);
		var self = this,
			options = self.options,
			// .ui-resizable has position: relative defined in the stylesheet
			// but dialogs have to use absolute or fixed positioning
			position = self.uiDialog.css('position'),
			resizeHandles = (typeof handles === 'string' ?
				handles	:
				'n,e,s,w,se,sw,ne,nw'
			);

		function filteredUi(ui) {
			return {
				originalPosition: ui.originalPosition,
				originalSize: ui.originalSize,
				position: ui.position,
				size: ui.size,
				offset: ui.position,
				helper: ui.helper,
				options: self.options
			};
		}

		self.uiDialog.resizable({
			cancel: '.ui-dialog-content',
			containment: 'document',
			alsoResize: self.element,
			maxWidth: options.maxWidth,
			maxHeight: options.maxHeight,
			minWidth: options.minWidth,
			minHeight: self._minHeight(),
			handles: resizeHandles,
			start: function(event, ui) {
				jQuery(this).addClass("ui-dialog-resizing");
				self._trigger('resizeStart', event, filteredUi(ui));
			},
			resize: function(event, ui) {
				self._trigger('resize', event, filteredUi(ui));

				// add
				options.height = jQuery(this).height();
				options.width = jQuery(this).width();
				self._size();
				// -- add
			},
			stop: function(event, ui) {
				jQuery(this).removeClass("ui-dialog-resizing");
				options.height = jQuery(this).height();
				options.width = jQuery(this).width();
				self._trigger('resizeStop', event, filteredUi(ui));
				jQuery.ui.dialog.overlay.resize();
				// rm 22-09-11
				//self.uiDialog.resizable('option', 'minHeight', self._minHeight());
			}
		})
		.css('position', position)
		.find('.ui-resizable-se').addClass('ui-icon ui-icon-grip-diagonal-se');
	},
	_minHeight: function() {
		//console.log('_minHeight');
		var options = this.options;

		if (options.height === 'auto') {
			return options.minHeight;
		} else {
			return Math.min(options.minHeight, options.height);
		}
	},
	_position: function(position) {
		//console.log('_position');
		var myAt = [],
			offset = [0, 0],
			isVisible;

		position = position || jQuery.ui.dialog.prototype.options.position;

		// deep extending converts arrays to objects in jQuery <= 1.3.2 :-(
//		if (typeof position == 'string' || jQuery.isArray(position)) {
//			myAt = jQuery.isArray(position) ? position : position.split(' ');

		if (typeof position === 'string' || (typeof position === 'object' && '0' in position)) {
			myAt = position.split ? position.split(' ') : [position[0], position[1]];

			if (myAt.length === 1) {
				myAt[1] = myAt[0];
			}

			jQuery.each(['left', 'top'], function(i, offsetPosition) {
				if (+myAt[i] === myAt[i]) {
					offset[i] = myAt[i];
					myAt[i] = offsetPosition;
				}
			});
		} else if (typeof position === 'object') {
			if ('left' in position) {
				myAt[0] = 'left';
				offset[0] = position.left;
			} else if ('right' in position) {
				myAt[0] = 'right';
				offset[0] = -position.right;
			}

			if ('top' in position) {
				myAt[1] = 'top';
				offset[1] = position.top;
			} else if ('bottom' in position) {
				myAt[1] = 'bottom';
				offset[1] = -position.bottom;
			}
		}

		// need to show the dialog to get the actual offset in the position plugin
		isVisible = this.uiDialog.is(':visible');
		if (!isVisible) {
			this.uiDialog.show();
		}

		this.uiDialog
			// workaround for jQuery bug #5781 http://dev.jquery.com/ticket/5781
			.css({ top: 0, left: 0 })
			.position({
				my: myAt.join(' '),
				at: myAt.join(' '),
				offset: offset.join(' '),
				of: this.options.positionOf, //of: window,
				collision: 'fit',
				// ensure that the titlebar is never outside the document
				using: function(pos) {
					var topOffset = jQuery(this).css(pos).offset().top;
					if (topOffset < 0) {
						jQuery(this).css('top', pos.top - topOffset);
					}
				}
			});
		if (!isVisible) {
			this.uiDialog.hide();
		}
	},
	_setOption: function(key, value){
		//console.log('_setOption');
		var self = this,
			uiDialog = self.uiDialog,
			isResizable = uiDialog.is(':data(resizable)'),
			resize = false;

		switch (key) {
			//handling of deprecated beforeclose (vs beforeClose) option
			//Ticket #4669 http://dev.jqueryui.com/ticket/4669
			//TODO: remove in 1.9pre
			case "beforeclose":
				key = "beforeClose";
				break;
			case "buttons":
				self._createButtons(value);
				break;
			case "closeText":
				// convert whatever was passed in to a string, for text() to not throw up
				self.uiDialogTitlebarCloseText.text("" + value);
				break;
			case "dialogClass":
				uiDialog
					.removeClass(self.options.dialogClass)
					.addClass(uiDialogClasses + value);
				break;
			case "disabled":
				if (value) {
					uiDialog.addClass('ui-dialog-disabled');
				} else {
					uiDialog.removeClass('ui-dialog-disabled');
				}
				break;
			case "draggable":
				if (value) {
					self._makeDraggable();
				} else {
					uiDialog.draggable('destroy');
				}
				break;
			case "height":
				resize = true;
				break;
			case "maxHeight":
				if (isResizable) {
					uiDialog.resizable('option', 'maxHeight', value);
				}
				resize = true;
				break;
			case "maxWidth":
				if (isResizable) {
					uiDialog.resizable('option', 'maxWidth', value);
				}
				resize = true;
				break;
			case "minHeight":
				if (isResizable) {
					uiDialog.resizable('option', 'minHeight', value);
				}
				resize = true;
				break;
			case "minWidth":
				if (isResizable) {
					uiDialog.resizable('option', 'minWidth', value);
				}
				resize = true;
				break;
			case "position":
				self._position(value);
				break;
			case "resizable":
				// currently resizable, becoming non-resizable
				if (isResizable && !value) {
					uiDialog.resizable('destroy');
				}

				// currently resizable, changing handles
				if (isResizable && typeof value === 'string') {
					uiDialog.resizable('option', 'handles', value);
				}

				// currently non-resizable, becoming resizable
				if (!isResizable && value !== false) {
					self._makeResizable(value);
				}
				break;
			case "title":
				// convert whatever was passed in o a string, for html() to not throw up
				jQuery(".ui-dialog-title", self.uiDialogTitlebar).html("" + (value || '&#160;'));
				break;
			case "width":
				resize = true;
				break;
		}

		jQuery.Widget.prototype._setOption.apply(self, arguments);
		if (resize) {
			self._size();
		}
	},

	_size: function() {
		//console.log('_size');
		/* If the user has resized the dialog, the .ui-dialog and .ui-dialog-content
		 * divs will both have width and height set, so we need to reset them
		 */
		var options = this.options,
			nonContentHeight;

		// reset content sizing
		// hide for non content measurement because height: 0 doesn't work in IE quirks mode (see #4350)
		this.element.css({
			width: 'auto',
			minHeight: 0,
			height: 0
		});

		// reset wrapper sizing
		// determine the height of all the non-content elements
		nonContentHeight = this.uiDialog.css({
				height: 'auto',
				width: options.width
			})
			.height();

		this.element
			.css(options.height === 'auto' ? {
					minHeight: Math.max(options.minHeight - nonContentHeight, 0),
					height: 'auto'
				} : {
					minHeight: 0,
					height: Math.max(options.height - nonContentHeight, 0)
			})
			.show();

		if (this.uiDialog.is(':data(resizable)')) {
			this.uiDialog.resizable('option', 'minHeight', this._minHeight());
		}
	}
});

jQuery.extend(jQuery.ui.dialog, {
	version: "1.8.1",

	uuid: 0,
	maxZ: 0,

	getTitleId: function($el) {
		//console.log('getTitleId');
		var id = $el.attr('id');
		if (!id) {
			this.uuid += 1;
			id = this.uuid;
		}
		return 'ui-dialog-title-' + id;
	},

	overlay: function(dialog) {
		//console.log('overlay');
		this.$el = jQuery.ui.dialog.overlay.create(dialog);
	}
});

jQuery.extend(jQuery.ui.dialog.overlay, {
	instances: [],
	// reuse old instances due to IE memory leak with alpha transparency (see #5185)
	oldInstances: [],
	maxZ: 0,
	events: jQuery.map('focus,mousedown,mouseup,keydown,keypress,click'.split(','),
		function(event) { return event + '.dialog-overlay'; }).join(' '),
	create: function(dialog) {
		//console.log('create');
		if (this.instances.length === 0) {
			// prevent use of anchors and inputs
			// we use a setTimeout in case the overlay is created from an
			// event that we're going to be cancelling (see #2804)
			setTimeout(function() {
				// handle jQuery(el).dialog().dialog('close') (see #4065)
				if (jQuery.ui.dialog.overlay.instances.length) {
					jQuery(document).bind(jQuery.ui.dialog.overlay.events, function(event) {
						// stop events if the z-index of the target is < the z-index of the overlay
						// we cannot return true when we don't want to cancel the event (#3523)
						if (jQuery(event.target).zIndex() < jQuery.ui.dialog.overlay.maxZ) {
							return false;
						}
					});
				}
			}, 1);

			// allow closing by pressing the escape key
			jQuery(document).bind('keydown.dialog-overlay', function(event) {
				if (dialog.options.closeOnEscape && event.keyCode &&
					event.keyCode === jQuery.ui.keyCode.ESCAPE) {

					dialog.close(event);
					event.preventDefault();
				}
			});

			// handle window resize
			jQuery(window).bind('resize.dialog-overlay', jQuery.ui.dialog.overlay.resize);
		}

		var $el = (this.oldInstances.pop() || jQuery('<div></div>').addClass('ui-widget-overlay'))
			.appendTo(document.body)
			.css({
				width: this.width(),
				height: this.height()
			});

		if (jQuery.fn.bgiframe) {
			$el.bgiframe();
		}

		this.instances.push($el);
		return $el;
	},

	destroy: function($el) {
		//console.log('destroy');
		var indexOf = jQuery.inArray($el, this.instances);
		if (indexOf != -1){
			this.oldInstances.push(this.instances.splice(indexOf, 1)[0]);
		}

		if (this.instances.length === 0) {
			jQuery([document, window]).unbind('.dialog-overlay');
		}

		$el.remove();

		// adjust the maxZ to allow other modal dialogs to continue to work (see #4309)
		var maxZ = 0;
		jQuery.each(this.instances, function() {
			maxZ = Math.max(maxZ, this.css('z-index'));
		});
		this.maxZ = maxZ;
	},

	height: function() {
		//console.log('height');
		var scrollHeight,
			offsetHeight;
		// handle IE 6
		if (jQuery.browser.msie && jQuery.browser.version < 7) {
			scrollHeight = Math.max(
				document.documentElement.scrollHeight,
				document.body.scrollHeight
			);
			offsetHeight = Math.max(
				document.documentElement.offsetHeight,
				document.body.offsetHeight
			);

			if (scrollHeight < offsetHeight) {
				return jQuery(window).height() + 'px';
			} else {
				return scrollHeight + 'px';
			}
		// handle "good" browsers
		} else {
			return jQuery(document).height() + 'px';
		}
	},

	width: function() {
		//console.log('width');
		var scrollWidth,
			offsetWidth;
		// handle IE
		if ( jQuery.browser.msie ) {
			scrollWidth = Math.max(
				document.documentElement.scrollWidth,
				document.body.scrollWidth
			);
			offsetWidth = Math.max(
				document.documentElement.offsetWidth,
				document.body.offsetWidth
			);

			if (scrollWidth < offsetWidth) {
				return jQuery(window).width() + 'px';
			} else {
				return scrollWidth + 'px';
			}
		// handle "good" browsers
		} else {
			return jQuery(document).width() + 'px';
		}
	},

	resize: function() {
		//console.log('resize');
		/* If the dialog is draggable and the user drags it past the
		 * right edge of the window, the document becomes wider so we
		 * need to stretch the overlay. If the user then drags the
		 * dialog back to the left, the document will become narrower,
		 * so we need to shrink the overlay to the appropriate size.
		 * This is handled by shrinking the overlay before setting it
		 * to the full document size.
		 */

		var $overlays = jQuery([]);
		jQuery.each(jQuery.ui.dialog.overlay.instances, function() {
			$overlays = $overlays.add(this);
		});

		$overlays.css({
			width: 0,
			height: 0
		}).css({
			width: jQuery.ui.dialog.overlay.width(),
			height: jQuery.ui.dialog.overlay.height()
		});
	}
});

jQuery.extend(jQuery.ui.dialog.overlay.prototype, {
	destroy: function() {
		jQuery.ui.dialog.overlay.destroy(this.$el);
	}
});

}(jQuery));