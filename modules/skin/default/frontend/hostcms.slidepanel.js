/**
 * Frontend slidepanels - Optimized version
 * (c) 2025 HostCMS LLC
 */
class hostcmsSlidepanel {
	static openPanels = new Set();
	static outsideClickHandler = null;
	static escapeHandler = null;

	// Вынесены константы для переиспользования
	static POSITION_STYLES = {
		left: {
			direction: 'horizontal',
			mainAxis: 'left',
			sizeProperty: 'width',
			initialPosition: '-100%',
			finalPosition: '0',
			animateProperty: 'left',
			mainCss: { top: 0, bottom: 0, left: 0 }
		},
		right: {
			direction: 'horizontal',
			mainAxis: 'right',
			sizeProperty: 'width',
			initialPosition: '-100%',
			finalPosition: '0',
			animateProperty: 'right',
			mainCss: { top: 0, bottom: 0, right: 0 }
		},
		top: {
			direction: 'vertical',
			mainAxis: 'top',
			sizeProperty: 'height',
			initialPosition: '-100%',
			finalPosition: '0',
			animateProperty: 'top',
			mainCss: { top: 0, left: 0, right: 0 }
		},
		bottom: {
			direction: 'vertical',
			mainAxis: 'bottom',
			sizeProperty: 'height',
			initialPosition: '-100%',
			finalPosition: '0',
			animateProperty: 'bottom',
			mainCss: { bottom: 0, left: 0, right: 0 }
		}
	};

	constructor(options = {}) {
		// Используем Object.assign вместо spread для лучшей производительности
		this.options = Object.assign({
			id: '',
			className: '',
			text: '',
			html: '',
			css: {},
			attributes: {},
			field: '',
			appendTo: 'body',
			responsiveWidthBreakpoint: 0,
			animateResize: 'width',
			animateResizePercent: '100%',
			prependTo: null,
			insertAfter: null,
			insertBefore: null,
			events: {},
			onCreated: null,
			onClosed: null,
			closeOnOutsideClick: true,
			closeOnEscape: true,
			animationDuration: 200,
			closeButtonSelector: '.slidepanel-button-close',
			bodySelector: '.slidepanel-body',
			position: 'left'
		}, options);

		// Кешируем конфигурацию позиции
		this.positionConfig = hostcmsSlidepanel.POSITION_STYLES[this.options.position] ||
							  hostcmsSlidepanel.POSITION_STYLES.left;

		this.element = null;
		this.isOpen = false;
		this.slidepanel = null;
		this.closeButton = null;
		this.bodyElement = null;

		// Биндим методы один раз в конструкторе
		this.handleCloseClick = this.handleCloseClick.bind(this);
		this.handlePanelClick = this.handlePanelClick.bind(this);
		this.handleOutsideClick = this.handleOutsideClick.bind(this);
		this.handleEscapeKey = this.handleEscapeKey.bind(this);
	}

	show() {
		if (this.element) return this.element;

		this.createPanelStructure();
		this.configurePanel();
		this.insertElement();
		this.animateOpen();

		// Оптимизированный вызов callback
		if (this.options.onCreated) {
			this.options.onCreated.call(this, this.element);
		}

		return this.element;
	}

	createPanelStructure() {
		const pos = this.options.position;

		// Создаём элемент один раз через template literal
		this.element = $(`<div class="hostcms-slidepanel hostcms-slidepanel-${pos}">
			<div class="slidepanel">
				<div class="slidepanel-button-close slidepanel-button-close-${pos}">
					<i class="fa-solid fa-xmark"></i>
				</div>
				<div class="slidepanel-body"></div>
			</div>
		</div>`);

		// Кешируем все необходимые элементы за один проход
		this.slidepanel = this.element.children('.slidepanel');
		this.closeButton = this.slidepanel.children(this.options.closeButtonSelector);
		this.bodyElement = this.slidepanel.children(this.options.bodySelector);
	}

	configurePanel() {
		const opt = this.options;

		// Группируем операции с атрибутами
		if (opt.id) this.element.attr('id', opt.id);
		if (opt.className) this.element.addClass(opt.className);

		// Устанавливаем контент (приоритет html над text)
		if (opt.html) {
			this.bodyElement.html(opt.html);
		} else if (opt.text) {
			this.bodyElement.text(opt.text);
		}

		// Объединяем стили в один объект для одного вызова css()
		const styles = Object.assign(
			{},
			this.positionConfig.mainCss,
			{ [this.positionConfig.animateProperty]: this.positionConfig.initialPosition }
		);
		this.slidepanel.css(styles);

		// Применяем пользовательские стили если есть
		if (Object.keys(opt.css).length > 0) {
			this.element.css(opt.css);
		}

		// Применяем атрибуты если есть
		if (Object.keys(opt.attributes).length > 0) {
			this.element.attr(opt.attributes);
		}

		this.setupEventHandlers();
	}

	setupEventHandlers() {
		const events = this.options.events;

		// Используем for...in для лучшей производительности с объектами
		for (const event in events) {
			if (events.hasOwnProperty(event)) {
				this.element.on(event, events[event]);
			}
		}

		// Обработчики закрытия
		this.closeButton.on('click', this.handleCloseClick);
		this.slidepanel.on('click', this.handlePanelClick);
	}

	handleCloseClick(e) {
		e.stopPropagation();
		this.close();
	}

	handlePanelClick(e) {
		e.stopPropagation();
	}

	insertElement() {
		const opt = this.options;

		// Оптимизированная вставка элемента
		if (opt.insertAfter) {
			$(opt.insertAfter).after(this.element);
		} else if (opt.insertBefore) {
			$(opt.insertBefore).before(this.element);
		} else if (opt.prependTo) {
			$(opt.prependTo).prepend(this.element);
		} else {
			$(opt.appendTo).append(this.element);
		}
	}

	animateOpen() {
		const opt = this.options;
		const cfg = this.positionConfig;

		// Устанавливаем размер до анимации
		if (!opt.responsiveWidthBreakpoint || window.innerWidth < opt.responsiveWidthBreakpoint) {
			this.slidepanel.css(cfg.sizeProperty, opt.animateResizePercent);
		}

		// Анимация с колбэком
		this.slidepanel.animate(
			{ [cfg.animateProperty]: cfg.finalPosition },
			opt.animationDuration,
			() => {
				this.isOpen = true;
				hostcmsSlidepanel.openPanels.add(this);

				if (opt.closeOnOutsideClick) this.setupGlobalClickHandler();
				if (opt.closeOnEscape) this.setupGlobalEscapeHandler();
			}
		);
	}

	close() {
		if (!this.isOpen) return;

		const cfg = this.positionConfig;

		this.slidepanel.animate(
			{ [cfg.animateProperty]: cfg.initialPosition },
			{
				duration: this.options.animationDuration,
				complete: () => this.destroy()
			}
		);

		// Вызываем callback если есть
		if (this.options.onClosed) {
			this.options.onClosed.call(this, this.element);
		}
	}

	setupGlobalClickHandler() {
		if (!hostcmsSlidepanel.outsideClickHandler) {
			hostcmsSlidepanel.outsideClickHandler = this.handleOutsideClick;
			$(document).on('click touchstart', hostcmsSlidepanel.outsideClickHandler);
		}
	}

	setupGlobalEscapeHandler() {
		if (!hostcmsSlidepanel.escapeHandler) {
			hostcmsSlidepanel.escapeHandler = this.handleEscapeKey;
			$(document).on('keydown', hostcmsSlidepanel.escapeHandler);
		}
	}

	handleOutsideClick(event) {
		// Оптимизированная проверка клика вне панели
		if (!$(event.target).closest('.hostcms-slidepanel').length) {
			const panels = hostcmsSlidepanel.openPanels;
			if (panels.size > 0) {
				// Получаем последнюю панель без создания массива
				let lastPanel;
				for (lastPanel of panels) {} // Итерируемся до последнего элемента
				lastPanel.close();
			}
		}
	}

	handleEscapeKey(event) {
		if (event.key === 'Escape' || event.keyCode === 27) {
			const panels = hostcmsSlidepanel.openPanels;
			if (panels.size > 0) {
				let lastPanel;
				for (lastPanel of panels) {}
				lastPanel.close();
			}
		}
	}

	cleanupGlobalHandlers() {
		if (hostcmsSlidepanel.openPanels.size === 0) {
			if (hostcmsSlidepanel.outsideClickHandler) {
				$(document).off('click touchstart', hostcmsSlidepanel.outsideClickHandler);
				hostcmsSlidepanel.outsideClickHandler = null;
			}

			if (hostcmsSlidepanel.escapeHandler) {
				$(document).off('keydown', hostcmsSlidepanel.escapeHandler);
				hostcmsSlidepanel.escapeHandler = null;
			}
		}
	}

	getElement() {
		return this.element;
	}

	updateContent(content) {
		if (this.bodyElement) this.bodyElement.html(content);
	}

	updateCss(newCss) {
		if (this.element) this.element.css(newCss);
	}

	addClass(className) {
		if (this.element) this.element.addClass(className);
	}

	removeClass(className) {
		if (this.element) this.element.removeClass(className);
	}

	destroy() {
		if (!this.element) return;

		// Удаляем обработчики
		if (this.closeButton) this.closeButton.off('click', this.handleCloseClick);
		if (this.slidepanel) this.slidepanel.off('click', this.handlePanelClick);

		// Удаляем элемент
		this.element.remove();

		// Обнуляем ссылки
		this.element = null;
		this.slidepanel = null;
		this.closeButton = null;
		this.bodyElement = null;
		this.isOpen = false;

		// Удаляем из коллекции
		hostcmsSlidepanel.openPanels.delete(this);

		this.cleanupGlobalHandlers();
	}

	static closeAllOpenPanels() {
		// Избегаем создания промежуточного массива
		hostcmsSlidepanel.openPanels.forEach(panel => panel.close());
	}

	static closeBySelector(selector) {
		// Оптимизированный поиск панели
		for (const panel of hostcmsSlidepanel.openPanels) {
			if (panel.element && panel.element.is(selector)) {
				panel.close();
				return true;
			}
		}
		return false;
	}
}