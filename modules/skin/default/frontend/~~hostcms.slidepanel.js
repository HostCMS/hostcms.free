/**
 * Frontend slidepanels
 * (c) 2025 HostCMS LLC
 */
class hostcmsSlidepanel {
	static openPanels = new Set();
	static outsideClickHandler = null;
	static escapeHandler = null;

	constructor(options) {
		this.defaultOptions = {
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
			position: 'left', // left, right, top, bottom
			// Стили по умолчанию для разных позиций
			positionStyles: {
				left: {
					direction: 'horizontal',
					mainAxis: 'left',
					sizeProperty: 'width',
					initialPosition: '-100%',
					finalPosition: '0',
					animateProperty: 'left',
					mainCss: {
						top: 0,
						bottom: 0,
						left: 0
					}
				},
				right: {
					direction: 'horizontal',
					mainAxis: 'right',
					sizeProperty: 'width',
					initialPosition: '-100%',
					finalPosition: '0',
					animateProperty: 'right',
					mainCss: {
						top: 0,
						bottom: 0,
						right: 0
					}
				},
				top: {
					direction: 'vertical',
					mainAxis: 'top',
					sizeProperty: 'height',
					initialPosition: '-100%',
					finalPosition: '0',
					animateProperty: 'top',
					mainCss: {
						top: 0,
						left: 0,
						right: 0
					}
				},
				bottom: {
					direction: 'vertical',
					mainAxis: 'bottom',
					sizeProperty: 'height',
					initialPosition: '-100%',
					finalPosition: '0',
					animateProperty: 'bottom',
					mainCss: {
						bottom: 0,
						left: 0,
						right: 0
					}
				}
			}
		};

		this.options = { ...this.defaultOptions, ...options };

		// Определяем стили для выбранной позиции
		this.positionConfig = this.options.positionStyles[this.options.position] ||
							this.options.positionStyles.left;

		this.element = null;
		this.isOpen = false;
		this.slidepanel = null;
		this.closeButton = null;
		this.bodyElement = null;

		this.handleCloseClick = this.handleCloseClick.bind(this);
		this.handlePanelClick = this.handlePanelClick.bind(this);
		this.handleOutsideClick = this.handleOutsideClick.bind(this);
		this.handleEscapeKey = this.handleEscapeKey.bind(this);
	}

	show() {
		if (this.element) return this.element;

		// Создаем структуру панели
		this.createPanelStructure();

		// Настраиваем панель
		this.configurePanel();

		// Вставляем в DOM
		this.insertElement();

		// Анимируем открытие
		this.animateOpen();

		// Вызываем callback
		this.options.onCreated?.call(this, this.element);

		return this.element;
	}

	createPanelStructure() {
		this.element = $(`
			<div class="hostcms-slidepanel hostcms-slidepanel-${this.options.position}">
				<div class="slidepanel">
					<div class="slidepanel-button-close slidepanel-button-close-${this.options.position}">
						<i class="fa-solid fa-xmark"></i>
					</div>
					<div class="slidepanel-body"></div>
				</div>
			</div>
		`);

		this.slidepanel = this.element.find('.slidepanel');
		this.closeButton = this.element.find(this.options.closeButtonSelector);
		this.bodyElement = this.element.find(this.options.bodySelector);
	}

	configurePanel() {
		const { options, element, bodyElement, slidepanel } = this;

		// Настройка атрибутов и классов
		if (options.id) element.attr('id', options.id);
		if (options.className) element.addClass(options.className);

		// Установка контента
		if (options.html) {
			bodyElement.html(options.html);
		} else if (options.text) {
			bodyElement.text(options.text);
		}

		// Применение базовых стилей для позиции
		slidepanel.css(this.positionConfig.mainCss);

		// Устанавливаем начальное положение
		const { animateProperty, initialPosition } = this.positionConfig;
		slidepanel.css(animateProperty, initialPosition);

		// Применение пользовательских стилей
		if (Object.keys(options.css).length > 0) {
			element.css(options.css);
		}

		// Добавление атрибутов
		if (Object.keys(options.attributes).length > 0) {
			element.attr(options.attributes);
		}

		// Настройка обработчиков событий
		this.setupEventHandlers();
	}

	setupEventHandlers() {
		const { options, element, closeButton, slidepanel } = this;

		// Пользовательские обработчики событий
		Object.entries(options.events).forEach(([event, handler]) => {
			element.on(event, handler);
		});

		// Обработчики закрытия
		closeButton.on('click', this.handleCloseClick);
		slidepanel.on('click', this.handlePanelClick);
	}

	handleCloseClick(e) {
		e.stopPropagation();
		this.close();
	}

	handlePanelClick(e) {
		e.stopPropagation();
	}

	insertElement() {
		const { options, element } = this;
		const target = $(options.appendTo);

		const insertMethods = {
			insertAfter: () => $(options.insertAfter).after(element),
			insertBefore: () => $(options.insertBefore).before(element),
			prependTo: () => $(options.prependTo).prepend(element)
		};

		const method = Object.keys(insertMethods).find(key => options[key]);
		insertMethods[method]?.() || target.append(element);
	}

	animateOpen() {
		const { slidepanel, options, positionConfig } = this;

		// Полноэкранный режим для мобильных
		if (!options.responsiveWidthBreakpoint || window.innerWidth < options.responsiveWidthBreakpoint) {
			slidepanel.css(positionConfig.sizeProperty, options.animateResizePercent);
		}

		// Анимация открытия
		slidepanel.animate(
			{ [positionConfig.animateProperty]: positionConfig.finalPosition },
			options.animationDuration,
			() => {
				this.isOpen = true;
				hostcmsSlidepanel.openPanels.add(this);

				if (options.closeOnOutsideClick) this.setupGlobalClickHandler();
				if (options.closeOnEscape) this.setupGlobalEscapeHandler();
			}
		);
	}

	close() {
		if (!this.isOpen) return;

		const { slidepanel, options, positionConfig } = this;

		// Закрываем в противоположную сторону
		slidepanel.animate(
			{ [positionConfig.animateProperty]: positionConfig.initialPosition },
			{
				duration: options.animationDuration,
				complete: () => this.destroy()
			}
		);

		// Вызываем callback
		this.options.onClosed?.call(this, this.element);
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
		// Быстрая проверка на клик вне панелей
		if (!$(event.target).closest('.hostcms-slidepanel').length) {
			const panels = Array.from(hostcmsSlidepanel.openPanels);
			const lastPanel = panels[panels.length - 1];
			lastPanel.close();
		}
	}

	handleEscapeKey(event) {
		if (event.key === 'Escape' || event.keyCode === 27) {
			const panels = Array.from(hostcmsSlidepanel.openPanels);
			const lastPanel = panels[panels.length - 1];
			lastPanel.close();
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

	// Геттер для удобства
	getElement() {
		return this.element;
	}

	updateContent(content) {
		this.bodyElement?.html(content);
	}

	updateCss(newCss) {
		this.element?.css(newCss);
	}

	addClass(className) {
		this.element?.addClass(className);
	}

	removeClass(className) {
		this.element?.removeClass(className);
	}

	destroy() {
		if (!this.element) return;

		// Удаляем обработчики
		this.closeButton?.off('click', this.handleCloseClick);
		this.slidepanel?.off('click', this.handlePanelClick);

		// Удаляем элемент
		this.element.remove();

		// Очищаем ссылки
		this.element = null;
		this.slidepanel = null;
		this.closeButton = null;
		this.bodyElement = null;
		this.isOpen = false;

		// Удаляем из коллекции открытых панелей
		hostcmsSlidepanel.openPanels.delete(this);

		// Проверяем глобальные обработчики
		this.cleanupGlobalHandlers();
	}

	static closeAllOpenPanels() {
		const panels = Array.from(hostcmsSlidepanel.openPanels);
		panels.forEach(panel => panel.close());
	}

	static closeBySelector(selector) {
		const panels = Array.from(hostcmsSlidepanel.openPanels);
		const panel = panels.find(p => p.element && p.element.is(selector));

		if (panel) {
			panel.close();
			return true;
		}

		return false;
	}
}