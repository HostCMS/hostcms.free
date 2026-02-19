/* global */
(function($) {
	"use strict";

	// http://james.padolsey.com/javascript/regex-selector-for-jquery/
	// Оптимизировано: добавлены проверки безопасности и let/const
	$.expr[':'].regex = function(elem, index, match) {
		const matchParams = match[3].split(',');
		const validLabels = /^(data|css):/;

		const method = matchParams[0].match(validLabels) ? matchParams[0].split(':')[0] : 'attr';
		const property = matchParams.shift().replace(validLabels, '');

		const regexFlags = 'ig';
		// Кеширование регулярки здесь затруднительно из-за динамических параметров,
		// но мы чистим строку эффективнее
		const regex = new RegExp(matchParams.join('').trim(), regexFlags);

		return regex.test($(elem)[method](property));
	};

	// own case in-sensitive function
	$.expr[':'].icontains = function(a, i, m) {
		// Используем textContent для производительности (быстрее, чем .text() jQuery)
		const text = (a.textContent || a.innerText || "").toUpperCase();
		return text.indexOf(m[3].toUpperCase()) >= 0;
	};

	$.fn.extend({
		appendOptions: function(array) {
			return this.each(function() {
				const $select = $(this);
				// Используем DocumentFragment для предотвращения множественных Reflow/Repaint
				const docFrag = document.createDocumentFragment();

				// Поддержка и массивов и объектов
				// Если array - это объект, Object.keys позволит пройтись по ключам
				const keys = Object.keys(array);

				for (const key of keys) {
					const item = array[key];
					let option;

					if (typeof item === 'object' && item !== null) {
						option = document.createElement('option');
						option.value = item.value;
						option.text = item.name;

						if (item.disabled) {
							option.disabled = true;
						}

						if (item.data) {
							for (const [dataName, dataValue] of Object.entries(item.data)) {
								option.setAttribute(`data-${dataName}`, dataValue);
							}
						}

						if (item.attr) {
							for (const [attrName, attrValue] of Object.entries(item.attr)) {
								option.setAttribute(attrName, attrValue);
							}
						}
					} else {
						// Простой формат {value: text}
						option = document.createElement('option');
						option.value = key;
						option.text = item;
					}

					docFrag.appendChild(option);
				}

				$select.empty().append(docFrag);
			});
		},

		insertAtCaret: function(newValue) {
			return this.each(function() {
				// Удалена поддержка IE < 9 (document.selection)

				if (this.selectionStart || this.selectionStart === '0' || this.selectionStart === 0) {
					// Modern browsers
					const startPos = this.selectionStart;
					const endPos = this.selectionEnd;
					const scrollTop = this.scrollTop;

					this.value = this.value.substring(0, startPos) + newValue + this.value.substring(endPos, this.value.length);

					this.focus();
					const newPos = startPos + newValue.length;
					this.selectionStart = newPos;
					this.selectionEnd = newPos;
					this.scrollTop = scrollTop;
				} else {
					// Fallback
					this.value += newValue;
					this.focus();
				}
			});
		},

		toggleDisabled: function() {
			return this.each(function() {
				this.disabled = !this.disabled;
			});
		},

		clearSelect: function() {
			// Оптимизация: создание опции один раз вне цикла, если бы мы вставляли одно и то же,
			// но здесь .each для разных селектов.
			return this.empty().append('<option value="0"> ... </option>');
		},

		toggleHighlight: function() {
			return this.toggleClass('cheked');
		},

		highlightAllRows: function(checked) {
			return this.each(function() {
				const $table = $(this);

				// Устанавливаем checked для групповых чекбоксов
				$table.find("input[type='checkbox'][id^='id_admin_forms_all_check']").prop('checked', checked);

				const $checkboxes = $table.find("input[type='checkbox'][id^='check_']");

				$checkboxes.each(function() {
					const $checkbox = $(this);
					// Используем нативное свойство checked для скорости
					const isChecked = this.checked;

					if (isChecked !== checked) {
						$checkbox.parents('tr').toggleHighlight();
					}

					// Обновляем состояние
					if (isChecked !== checked) {
						this.checked = checked;
					}
				});
			});
		},

		setTopCheckbox: function() {
			return this.each(function() {
				const $table = $(this);
				// Проверяем, есть ли хотя бы один НЕ отмеченный чекбокс
				const hasUnchecked = $table.find("input[type='checkbox'][id^='check_']:not(:checked)").length > 0;

				$table.find("input[type='checkbox'][id^='id_admin_forms_all_check']").prop('checked', !hasUnchecked);
			});
		}
	});

	$.fn.getInputType = function() {
		const el = this[0];
		if (el) {
			const tagName = el.tagName.toLowerCase();
			return tagName === "input" ? el.type.toLowerCase() : tagName;
		}
		return undefined;
	};

})(jQuery);

// -------------
// Оставляем в глобальной области, как в оригинале, но используем для совместимости
// Если есть возможность, лучше перенести внутрь $.getMultiContent как статическое свойство
var loadedMultiContent = loadedMultiContent || [];

$.getMultiContent = function(arr, path) {
	// Вспомогательная функция для загрузки скрипта
	function loadScriptContent(url) {
		return $.ajax({
			url: url,
			dataType: "text",
			cache: true, // Включаем кеш браузера для скриптов
			success: function() {
				loadedMultiContent.push(url);
			}
		});
	}

	const pathPrefix = path || '';
	let cssCount = 0;
	let loadedCssCount = 0;
	const cssDeferred = $.Deferred();

	// Используем Set для быстрого поиска (O(1) вместо O(n))
	// Создаем Set на лету из существующего массива loadedMultiContent для локальных проверок
	const loadedSet = new Set(loadedMultiContent);

	// Фильтрация скриптов, которые нужно загрузить
	const aScripts = arr
		.map(url => pathPrefix + url)
		.filter(url => {
			const isCss = url.indexOf('.css') !== -1;
			const isLoaded = loadedSet.has(url);

			if (!isLoaded && !isCss) {
				return true;
			}
			return false;
		});

	// Обработка CSS
	arr.forEach(item => {
		const url = pathPrefix + item;

		if (!loadedSet.has(url) && url.indexOf('.css') !== -1) {
			$('<link>', { rel: 'stylesheet', href: url }).appendTo('head');

			// Сразу добавляем в массив, чтобы не грузить повторно при быстрых кликах
			loadedMultiContent.push(url);
			loadedSet.add(url);

			cssCount++;

			// Обработчик загрузки CSS
			const $link = $(`link[href='${url}']`);
			$link.on('load error', function() {
				if (++loadedCssCount === cssCount) {
					cssDeferred.resolve();
				}
			});
		}
	});

	// Если CSS не было добавлено, резолвим сразу
	if (cssCount === 0) {
		cssDeferred.resolve();
	}

	// Запускаем загрузку скриптов
	const scriptPromises = aScripts.map(url => loadScriptContent(url));

	// Создаем общий промис: ждем CSS, потом ждем Скрипты
	const masterPromise = cssDeferred.then(function() {
		return $.when.apply($, scriptPromises);
	});

	return masterPromise.done(function(...args) {
		if (args.length > 0) {
			// $.when с массивом deferred возвращает аргументы массивом [data, status, xhr] для каждого запроса
			// Если запрос был один, args это [data, status, xhr]
			// Если запросов не было (пустой массив), args может быть пустым

			// Нормализация аргументов для выполнения
			const responses = Array.isArray(args[0]) && scriptPromises.length > 1 ? args : [args];

			for (const response of responses) {
				// response[0] содержит текст скрипта
				if (response && typeof response[0] === 'string') {
					$.globalEval(response[0]);
				}
			}
		}
	});
};