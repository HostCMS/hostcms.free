/* global hostcmsBackend, i18n, declension */

(function($) {
	"use strict";

	class FieldChecker {
		constructor() {
			// Map работает быстрее и надежнее для кеша, чем Object.create(null)
			this._regexCache = new Map();
			this._formFields = Object.create(null);

			// Текущее состояние для предотвращения лишних манипуляций с DOM
			this._fieldsState = new Map();

			this._styleCache = {
				error: {
					'border-bottom-style': 'solid',
					'border-width': '1px',
					'border-color': '#ff1861',
					'background-image': `url(${hostcmsBackend}/images/bullet_red.gif)`,
					'background-position': 'center right',
					'background-repeat': 'no-repeat'
				},
				success: {
					'border-style': '',
					'border-width': '',
					'border-color': '',
					'background-image': `url(${hostcmsBackend}/images/bullet_green.gif)`,
					'background-position': 'center right',
					'background-repeat': 'no-repeat'
				}
			};
		}

		check($object) {
			// Получаем нативный элемент для ускорения доступа к свойствам
			const element = $object[0];
			if (!element) return this;

			const $form = $object.closest('form');
			const formId = $form.attr('id');
			const value = element.value;
			const fieldId = element.id;

			// Получаем dataset один раз.
			// .data() в jQuery кэширует значения и может приводить типы,
			// но для производительности лучше читать dataset, если данные не менялись динамически через jQuery
			const data = $object.data();

			let message = '';

			// 1. Проверка минимальной длины
			if (data.min != null && value.length < +data.min) {
				message += this._buildLengthMessage('Minimum', data.min, value.length);
			}

			// 2. Проверка максимальной длины
			if (data.max != null && value.length > +data.max) {
				message += this._buildLengthMessage('Maximum', data.max, value.length);
			}

			// 3. Проверка регулярного выражения
			if (data.reg && value.length) {
				try {
					const regEx = this._getCachedRegExp(data.reg);
					if (!regEx.test(value)) {
						message += data.regMessage || (i18n['wrong_value_format'] + ' ');
					}
				} catch (e) {
					console.warn('Invalid regexp:', data.reg);
				}
			}

			// 4. Проверка соответствия полей (equality)
			if (data.equality) {
				// Используем getElementById внутри формы для скорости, если ID уникальны,
				// или find, если ID могут повторяться (что плохо)
				const field2 = document.getElementById(data.equality);
				if (field2 && value !== field2.value) {
					message += data.equalityMessage || (i18n['different_fields_value'] + ' ');
				}
			}

			// 5. Проверка select
			if (element.tagName === 'SELECT' && value <= 0) {
				message += 'value is empty';
			}

			const hasError = message.length > 0;

			// Обновляем UI только если сообщение изменилось или статус ошибки изменился
			// (Простая оптимизация, чтобы не дёргать DOM постоянно)
			this._updateMessage(fieldId, message);
			this._updateFieldState($object, hasError, formId, fieldId);

			// Проверка кнопок формы
			this.checkFormButtons($form, formId);

			return this;
		}

		checkFormButtons($form, formId) {
			formId = formId || $form.attr('id');
			const fields = this._formFields[formId];

			if (!fields) return;

			// Оптимизация: values() создает массив, some() прерывает перебор при первом true.
			// Это быстрее, чем for loop по всему массиву, если ошибка найдена в начале.
			const disableButtons = Object.values(fields).some(hasError => hasError === true);

			// Примечание: сохранено оригинальное написание 'toogle' (возможно, опечатка в библиотеке HostCMS)
			if (typeof $.toogleInputsActive === 'function') {
				$.toogleInputsActive($form, disableButtons);
			} else if (typeof $.toggleInputsActive === 'function') {
				$.toggleInputsActive($form, disableButtons);
			}
		}

		removeField($object) {
			const fieldId = $object.attr('id');
			const $form = $object.closest('form');
			const formId = $form.attr('id');

			if (this._formFields[formId] && this._formFields[formId][fieldId] !== undefined) {
				// Удаляем поле из отслеживания
				delete this._formFields[formId][fieldId];
				// Также удаляем из стейта стилей
				this._fieldsState.delete(formId + '_' + fieldId);
			}

			this.checkFormButtons($form, formId);
		}

		restoreFieldChange($object) {
			const element = $object[0];
			const defaultValue = $object.prop('defaultValue');

			// Оптимизированный поиск caption
			const $parentGroup = $object.closest('.form-group');
			const $parentCaption = $parentGroup.find('.caption').first();

			let oldValue = defaultValue;

			if (element.tagName === 'SELECT') {
				// Нативный поиск выбранной опции быстрее
				const selectedOption = element.options[element.selectedIndex];
				oldValue = (selectedOption && selectedOption.defaultSelected) ? selectedOption.value : 0;

				// Если defaultValue не сработал для select (бывает в старых браузерах), фоллбэк на jQuery
				if (!oldValue && $object.find('option[selected]').length) {
					oldValue = $object.find('option[selected]').val();
				}
			}

			// Единоразовое установка data-атрибутов
			$object.data({
				'old-value': oldValue,
				'data-old-value': oldValue
			});

			// Автодополнение
			if ($object.hasClass('ui-autocomplete-input')) {
				this._handleAutocompleteRestore($object);
			}

			if (!$parentCaption.find('i.restore-field').length) {
				$parentCaption.append('<i class="fa-solid fa-rotate-left restore-field" onclick="mainFieldChecker.restoreField(this)"></i>');
			}
		}

		restoreField(object) {
			const $object = $(object);
			const $group = $object.closest('.form-group');
			const $input = $group.find('.form-control').first();
			const dataValue = $input.data('old-value');

			$input.val(dataValue);

			// Автодополнение
			if ($input.hasClass('ui-autocomplete-input')) {
				this._handleAutocompleteInputRestore($input);
			}

			const inputType = this._getInputType($input[0]);

			if (inputType === 'text') {
				this.check($input);
				$input.trigger('change');
			} else if (inputType === 'select') {
				$input.trigger('change');
			}

			$object.remove(); // Удаляем саму иконку
		}

		checkAll(windowId, formId) {
			// windowId используется только для поиска, но в оригинале он передавался в $.getWindowId
			// Оставим для совместимости, но работать будем с DOM формой напрямую
			const formElement = document.getElementById(formId);

			if (formElement) {
				// ОПТИМИЗАЦИЯ: form.elements быстрее, чем getElementsByTagName,
				// и возвращает сразу все контролы (input, select, textarea, button)
				const elements = formElement.elements;
				const len = elements.length;

				for (let i = 0; i < len; i++) {
					const el = elements[i];
					// Пропускаем кнопки и fieldset, нужны только поля ввода
					if (el.tagName === 'BUTTON' || el.tagName === 'FIELDSET') continue;

					// Вызываем jQuery blur, так как на нем висят обработчики валидации
					$(el).blur();
				}
			}
		}

		// --- Вспомогательные методы ---

		_buildLengthMessage(type, limit, current) {
			return `${i18n[type]} ${limit} ${declension(limit, i18n['one_letter'], i18n['some_letter2'], i18n['some_letter1'])}. ${i18n['current_length']} ${current}. `;
		}

		_getCachedRegExp(pattern) {
			// Ключ кеша - только паттерн. FieldId не нужен, так как regex одинаковый
			if (!this._regexCache.has(pattern)) {
				this._regexCache.set(pattern, new RegExp(pattern));
			}
			return this._regexCache.get(pattern);
		}

		_updateMessage(fieldId, message) {
			// requestAnimationFrame для группировки DOM операций
			requestAnimationFrame(() => {
				const errorElement = document.getElementById(fieldId + '_error');
				if (errorElement) {
					// Проверка innerHTML предотвращает лишнюю перерисовку, если текст тот же
					if (errorElement.innerHTML !== message) {
						errorElement.innerHTML = message;
					}
				}
			});
		}

		_updateFieldState($object, hasError, formId, fieldId) {
			if (!this._formFields[formId]) {
				this._formFields[formId] = Object.create(null);
			}

			// Записываем логическое состояние
			this._formFields[formId][fieldId] = hasError;

			// Проверка: нужно ли менять стили?
			// Используем композитный ключ для Map
			const stateKey = formId + '_' + fieldId;
			const previousState = this._fieldsState.get(stateKey);

			if (previousState === hasError) {
				return; // Состояние визуализации не изменилось, выходим
			}

			this._fieldsState.set(stateKey, hasError);

			const styles = hasError ? this._styleCache.error : this._styleCache.success;
			$object.css(styles);
		}

		_getInputType(element) {
			if (!element) return 'text';
			const tagName = element.tagName; // В HTML документах tagName всегда UPPERCASE

			if (tagName === 'SELECT') return 'select';
			if (tagName === 'TEXTAREA') return 'textarea';
			if (tagName === 'INPUT') return element.type || 'text';

			return 'text';
		}

		_handleAutocompleteRestore($object) {
			// Кэшируем поиск элементов
			const $group = $object.closest('.form-group');
			const $acInput = $group.next().find('input');

			if ($acInput.length) {
				const acInputOldValue = $acInput.prop('defaultValue');
				$acInput.data({
					'old-value': acInputOldValue,
					'data-old-value': acInputOldValue
				});
			} else {
				const $acSelect = $object.closest('.input-group').find('select').first();
				if ($acSelect.length) {
					// Используем .val() так как prop('defaultValue') для select сложнее
					const $selectedOption = $acSelect.find('option[selected]');
					const acOptionOldValue = $selectedOption.length ? $selectedOption.val() : 0;
					$acSelect.data({
						'old-value': acOptionOldValue,
						'data-old-value': acOptionOldValue
					});
				}
			}
		}

		_handleAutocompleteInputRestore($input) {
			const $group = $input.closest('.form-group');
			const $acInput = $group.next().find('input');

			if ($acInput.length) {
				$acInput.val($acInput.data('old-value'));
			} else {
				const $acSelect = $input.closest('.input-group').find('select').first();
				if ($acSelect.length) {
					$acSelect.val($acSelect.data('old-value'));
				}
			}
		}
	}

	// Экспорт экземпляра
	window.mainFieldChecker = new FieldChecker();

})(jQuery);