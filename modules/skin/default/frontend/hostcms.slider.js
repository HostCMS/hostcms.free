class hostcmsSlider {
	/**
	 * Конструктор карусели объявлений
	 * @param {HTMLElement} container - Контейнер карусели
	 * @param {Object} options - Настройки карусели
	 * @param {number} [options.startIndex=0] - Начальный слайд
	 * @param {number} [options.autoPlayDelay=3000] - Задержка автопрокрутки (мс)
	 * @param {boolean} [options.autoPlay=true] - Включить автопрокрутку
	 * @param {boolean} [options.showDots=true] - Показывать точки-индикаторы
	 * @param {boolean} [options.showNav=true] - Показывать кнопки навигации
	 * @param {string} [options.transitionSpeed='0.5s'] - Скорость перехода
	 * @param {boolean} [options.pauseOnHover=true] - Пауза при наведении
	 * @param {boolean} [options.loop=true] - Зациклить карусель
	 * @param {string} [options.easing='ease'] - Функция плавности анимации
	 */
	constructor(container, options = {}) {
		this.container = container;
		this.content = container.querySelector('.hostcms-slider-bar-content');
		this.items = Array.from(container.querySelectorAll('.hostcms-slider-bar-text'));
		this.dotsContainer = container.querySelector('.hostcms-slider-dots');
		this.prevBtn = container.querySelector('.hostcms-slider-prev');
		this.nextBtn = container.querySelector('.hostcms-slider-next');

		// Хранилище для обработчиков событий
		this._eventHandlers = {
			prevBtn: null,
			nextBtn: null,
			mouseEnter: null,
			mouseLeave: null,
			dots: []
		};

		// Настройки по умолчанию
		const defaultOptions = {
			startIndex: 0,
			autoPlayDelay: 3000,
			autoPlay: true,
			showDots: true,
			showNav: true,
			transitionSpeed: '0.5s',
			pauseOnHover: true,
			loop: true,
			easing: 'ease'
		};

		// Объединяем настройки пользователя с настройками по умолчанию
		this.options = { ...defaultOptions, ...options };

		// Проверка корректности индекса
		this.currentIndex = Math.max(0, Math.min(this.options.startIndex, this.items.length - 1));

		this.autoPlayInterval = null;

		this.init();
	}

	init() {
		// Применяем скорость перехода и функцию плавности
		this.content.style.transition = `transform ${this.options.transitionSpeed} ${this.options.easing}`;

		// Настраиваем видимость элементов управления
		this.setupControls();

		// Создаем точки-индикаторы если включены
		if (this.options.showDots) {
			this.createDots();
		}

		// Добавляем обработчики событий для кнопок навигации если они есть
		if (this.options.showNav) {
			// Сохраняем ссылки на функции обработчиков
			this._eventHandlers.prevBtn = () => this.prevSlide();
			this._eventHandlers.nextBtn = () => this.nextSlide();

			this.prevBtn.addEventListener('click', this._eventHandlers.prevBtn);
			this.nextBtn.addEventListener('click', this._eventHandlers.nextBtn);
		}

		// Устанавливаем начальную позицию
		this.updateSlider();

		// Запускаем автопрокрутку если включена
		if (this.options.autoPlay) {
			this.startAutoPlay();
		}

		// Настройка паузы при наведении если включена
		if (this.options.pauseOnHover) {
			// Сохраняем ссылки на функции обработчиков
			this._eventHandlers.mouseEnter = () => this.stopAutoPlay();
			this._eventHandlers.mouseLeave = () => {
				if (this.options.autoPlay) {
					this.startAutoPlay();
				}
			};

			this.container.addEventListener('mouseenter', this._eventHandlers.mouseEnter);
			this.container.addEventListener('mouseleave', this._eventHandlers.mouseLeave);
		}
	}

	setupControls() {
		// Скрываем/показываем кнопки навигации
		if (!this.options.showNav) {
			this.prevBtn.classList.add('hidden');
			this.nextBtn.classList.add('hidden');
		}

		// Скрываем/показываем точки если они отключены
		if (!this.options.showDots) {
			this.dotsContainer.classList.add('hidden');
		}
	}

	createDots() {
		this.items.forEach((_, index) => {
			const dot = document.createElement('div');
			dot.classList.add('hostcms-slider-dot');
			if (index === this.currentIndex) dot.classList.add('active');

			// Создаем и сохраняем обработчик
			const dotClickHandler = () => this.goToSlide(index);
			this._eventHandlers.dots.push({
				element: dot,
				handler: dotClickHandler
			});

			dot.addEventListener('click', dotClickHandler);
			this.dotsContainer.appendChild(dot);
		});
	}

	updateDots() {
		if (!this.options.showDots) return;

		const dots = this.dotsContainer.querySelectorAll('.hostcms-slider-dot');
		dots.forEach((dot, index) => {
			dot.classList.toggle('active', index === this.currentIndex);
		});
	}

	goToSlide(index) {
		this.currentIndex = index;
		this.updateSlider();
	}

	prevSlide() {
		if (this.options.loop) {
			this.currentIndex = (this.currentIndex - 1 + this.items.length) % this.items.length;
		} else {
			this.currentIndex = Math.max(0, this.currentIndex - 1);
		}
		this.updateSlider();
	}

	nextSlide() {
		if (this.options.loop) {
			this.currentIndex = (this.currentIndex + 1) % this.items.length;
		} else {
			this.currentIndex = Math.min(this.items.length - 1, this.currentIndex + 1);
		}
		this.updateSlider();
	}

	updateSlider() {
		const translateX = -this.currentIndex * 100;
		this.content.style.transform = `translateX(${translateX}%)`;
		this.updateDots();
	}

	startAutoPlay() {
		this.stopAutoPlay();

		if (this.items.length > 1 && this.options.autoPlayDelay > 0) {
			this.autoPlayInterval = setInterval(() => this.nextSlide(), this.options.autoPlayDelay);
		}
	}

	stopAutoPlay() {
		if (this.autoPlayInterval) {
			clearInterval(this.autoPlayInterval);
			this.autoPlayInterval = null;
		}
	}

	/**
	 * Обновить настройки карусели
	 * @param {Object} newOptions - Новые настройки
	 */
	updateOptions(newOptions) {
		this.options = { ...this.options, ...newOptions };

		if (newOptions.autoPlayDelay !== undefined || newOptions.autoPlay !== undefined) {
			this.stopAutoPlay();
			if (this.options.autoPlay) {
				this.startAutoPlay();
			}
		}
	}

	/**
	 * Перейти к следующему слайду
	 */
	next() {
		this.nextSlide();
	}

	/**
	 * Перейти к предыдущему слайду
	 */
	prev() {
		this.prevSlide();
	}

	/**
	 * Перейти к конкретному слайду
	 * @param {number} index - Индекс слайда
	 */
	goTo(index) {
		if (index >= 0 && index < this.items.length) {
			this.goToSlide(index);
		}
	}

	/**
	 * Получить текущий индекс слайда
	 * @returns {number} Текущий индекс
	 */
	getCurrentIndex() {
		return this.currentIndex;
	}

	/**
	 * Получить количество слайдов
	 * @returns {number} Количество слайдов
	 */
	getSlidesCount() {
		return this.items.length;
	}

	/**
	 * Уничтожить карусель (очистить интервалы и обработчики)
	 */
	destroy() {
		this.stopAutoPlay();

		if (this._eventHandlers.prevBtn && this.prevBtn) {
			this.prevBtn.removeEventListener('click', this._eventHandlers.prevBtn);
			this._eventHandlers.prevBtn = null;
		}

		if (this._eventHandlers.nextBtn && this.nextBtn) {
			this.nextBtn.removeEventListener('click', this._eventHandlers.nextBtn);
			this._eventHandlers.nextBtn = null;
		}

		if (this._eventHandlers.mouseEnter && this.container) {
			this.container.removeEventListener('mouseenter', this._eventHandlers.mouseEnter);
			this._eventHandlers.mouseEnter = null;
		}

		if (this._eventHandlers.mouseLeave && this.container) {
			this.container.removeEventListener('mouseleave', this._eventHandlers.mouseLeave);
			this._eventHandlers.mouseLeave = null;
		}

		this._eventHandlers.dots.forEach(dotHandler => {
			if (dotHandler.element && dotHandler.handler) {
				dotHandler.element.removeEventListener('click', dotHandler.handler);
			}
		});
		this._eventHandlers.dots = [];

		if (this.dotsContainer) {
			this.dotsContainer.innerHTML = '';
		}

		this.content.style.transform = 'translateX(0%)';
		this.content.style.transition = 'none';

		const activeDots = this.dotsContainer.querySelectorAll('.hostcms-slider-dot.active');
		activeDots.forEach(dot => dot.classList.remove('active'));

		if (this.prevBtn) this.prevBtn.disabled = true;
		if (this.nextBtn) this.nextBtn.disabled = true;

		this.container = null;
		this.content = null;
		this.items = null;
		this.dotsContainer = null;
		this.prevBtn = null;
		this.nextBtn = null;
	}
}