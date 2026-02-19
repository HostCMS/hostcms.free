/* global bootbox, i18n, checkRegistration, mainFormAutosave, isEmpty, readCookiesForInitiateSettings, wysiwyg, syntaxhighlighter */

(function($) {
	"use strict"; // Включаем строгий режим

	// Кешируем регулярные выражения для повторного использования
	const REGEX_FILENAME = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
	const REGEX_CHECK_ID = /check_(\d+)_(\S+)/;
	const REGEX_HOSTCMS_PARAMS = /hostcms\[.*?\]=.*?(&|$)/g;

	$.ajaxSetup({
		cache: false,
		error: function(jqXHR, textStatus) {
			if (jqXHR.status === 403 || jqXHR.status === 401) {
				document.documentElement.innerHTML = jqXHR.responseText;
				return;
			}

			if (jqXHR.statusText === 'abort') return;

			$.loadingScreen('hide');

			const errorText = $.escapeHtml(jqXHR.responseText);
			const message = `
				<textarea class="error-text" style="width: 100%" rows="15">${errorText}</textarea>
				<div class="error-clipboard-wrapper">
					<i class="fa-solid fa-clipboard"></i>
					<span onclick="$.copyToClipboard('textarea.error-text'); return false;">
						${i18n['copy_error_clipboard'] || 'Copy to clipboard'}
					</span>
				</div>`;

			bootbox.dialog({
				title: `AJAX error: ${textStatus}! HTTP: ${jqXHR.status} ${jqXHR.statusText}`,
				message: message,
				backdrop: true,
				size: 'large'
			}).modal('show');
		}
	});

	const initialHref = location.href;
	let popstate = ('state' in window.history && window.history.state !== null);

	$(window).on('popstate', function(event) {
		// Игнорируем начальный popstate, который некоторые браузеры запускают при загрузке
		const startPop = !popstate && initialHref.split("#")[0] == location.href.split("#")[0];
		popstate = true;

		if (startPop) return;

		// FIX: jQuery 3.0+ не пробрасывает state в объект события jQuery, берем из originalEvent
		const nativeEvent = event.originalEvent || event;
		const state = nativeEvent.state;

		if (state && state.windowId) {
			if ($.isiOS && $.isiOS()) { // Проверка на существование функции
				$(window).off('beforeunload');
				window.location.reload();
			} else {
				const data = state.data || {};
				data['_'] = Date.now(); // Date.now() быстрее new Date().getTime()

				$.loadingScreen('show');

				$.ajax({
					context: $('#' + state.windowId),
					url: state.url,
					type: 'POST',
					data: data,
					dataType: 'json',
					success: $.ajaxCallback
				});
			}
		} else {
			popstate = false;
		}
	});

	const currentRequests = {};
	$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
		if (options.abortOnRetry) {
			if (currentRequests[options.url]) {
				currentRequests[options.url].abort();
			}
			currentRequests[options.url] = jqXHR;
		}
	});

	$.extend({
		loadingScreen: function(method) {
			const $body = $('body');
			const $container = $('.loading-container');

			if (method === 'show') {
				$body.css('cursor', 'wait');
				$container.removeClass('loading-inactive');
			} else if (method === 'hide') {
				$body.css('cursor', 'auto');
				// setTimeout 0 выносит выполнение в конец стека вызовов, позволяя UI обновиться
				setTimeout(() => {
					$container.addClass('loading-inactive');
				}, 0);
			} else {
				console.warn(`Method ${method} does not exist on jQuery.loadingScreen`);
			}
		},

		getData: function(settings) {
			const data = (typeof settings.post !== 'undefined') ? settings.post : {};

			data['_'] = Date.now();

			// Используем условное добавление свойств, чтобы код был чище
			if (settings.action) data['hostcms[action]'] = settings.action;
			if (settings.operation) data['hostcms[operation]'] = settings.operation;
			if (settings.limit) data['hostcms[limit]'] = settings.limit;
			if (settings.current) data['hostcms[current]'] = settings.current;
			if (settings.sortingFieldId) data['hostcms[sortingfield]'] = settings.sortingFieldId;
			if (settings.sortingDirection) data['hostcms[sortingdirection]'] = settings.sortingDirection;
			if (settings.view) data['hostcms[view]'] = settings.view;

			data['hostcms[window]'] = settings.windowId;

			return data;
		},

		requestSettings: function(settings) {
			return $.extend({
				open: function() {
					const jWindow = $(this).parent();
					const mod = ($('body>.ui-dialog').length - 1) % 5;
					const offset = jWindow.offset();

					jWindow.css({
						top: offset.top + 10 * mod,
						left: offset.left + 10 * mod
					});

					const uiDialog = $(this).parent('.ui-dialog');
					// Исправление: установка ширины/высоты самому себе может быть избыточной, но оставил для совместимости
					uiDialog.width(uiDialog.width()).height(uiDialog.height());
				},
				focus: function() {
					$.data(document.body, 'currentWindowId', $(this).attr('id'));
				},
				path: '',
				context: '',
				action: '',
				operation: '',
				additionalParams: '',
				windowId: 'id_content',
				datasetId: 0,
				objectId: 0,
				limit: '',
				current: '',
				sortingFieldId: '',
				sortingDirection: '',
				view: '',
				post: {},
				loadingScreen: true
			}, settings);
		},

		ajaxRequest: function(settings) {
			settings = $.requestSettings(settings);

			if (typeof settings.callBack === 'undefined') {
				console.error('Callback function is undefined');
				return false;
			}

			let path = settings.path;
			if (settings.additionalParams && settings.additionalParams.trim() !== '') {
				path += '?' + settings.additionalParams;
			}

			if (settings.loadingScreen) {
				$.loadingScreen('show');
			}

			const data = $.getData(settings);
			// Используем шаблонные строки
			data[`hostcms[checked][${settings.datasetId}][${settings.objectId}]`] = 1;

			if (settings.additionalData) {
				$.each(settings.additionalData, function(index, value) {
					data[index] = value;
				});
			}

			// Оптимизация определения контекста
			let context = {};
			if (settings.context) {
				if (jQuery.prototype.isPrototypeOf(settings.context)) { // eslint-disable-line
					context = settings.context;
				} else if (settings.context.length) {
					context = $(`#${settings.windowId} #${settings.context}`);
				}
			}

			const ajaxOptions = {
				context: context,
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: settings.callBack,
				abortOnRetry: true
			};

			if (settings.ajaxOptions) {
				$.extend(ajaxOptions, settings.ajaxOptions);
			}

			$.ajax(ajaxOptions);

			return false;
		},

		ajaxCallback: function(data, textStatus, jqXHR) {
			const triggerReturn = $('body').triggerHandler('beforeAjaxCallback', [data]);

			if (triggerReturn === 'break') {
				$.loadingScreen('hide');
				return false;
			}

			$.loadingScreen('hide');
			if (data == null) {
				bootbox.alert('AJAX response error: Data is null.');
				return;
			}

			if (data.action === 'showWebauthn') {
				checkRegistration(location)
					.then(function(result) {
						if (result === false && typeof _windowSettings !== 'undefined') {
							const settings = _windowSettings; // eslint-disable-line
							settings.additionalParams = settings.additionalParams + '&noWebauthns';
							$.adminLoad(settings);
						}
					})
					.catch((e) => {
						console.error("Webauthn fetch problem: " + e.message);
					});
			}

			const mime = ['application/force-download', 'application/json'];
			const responseHeader = jqXHR.getResponseHeader('content-type');

			if (mime.includes(responseHeader) && jqXHR.getResponseHeader('Content-Disposition')) {
				const url = window.URL.createObjectURL(new Blob([jqXHR.responseText]));
				const a = document.createElement('a');

				a.style.display = 'none';
				a.href = url;

				let filename = '';
				const disposition = jqXHR.getResponseHeader('Content-Disposition');

				if (disposition && disposition.indexOf('attachment') !== -1) {
					const matches = REGEX_FILENAME.exec(disposition);
					if (matches != null && matches[1]) {
						filename = matches[1].replace(/['"]/g, '');
					}
				}

				a.download = decodeURIComponent(filename);
				document.body.appendChild(a);
				a.click();

				// Очистка
				setTimeout(() => {
					document.body.removeChild(a);
					window.URL.revokeObjectURL(url);
				}, 100);

				return;
			}

			const jObject = $(this);

			if (data.form_html) {
				$.beforeContentLoad(jObject);
				$.insertContent(jObject, data.form_html);
				$.afterContentLoad(jObject, data);
				jObject.trigger('adminLoadSuccess');
			}

			if (data.error) {
				let jMessage = jObject.find('#id_message');
				jMessage.empty().html(data.error);
			}

			if (data.title && !isEmpty(data.title) && jObject.attr('id') === 'id_content') {
				document.title = data.title;
			}
		},

		ajaxCallbackSkin: function(data) {
			if (typeof data.module !== 'undefined' && data.module !== null) {
				$.currentMenu(data.module);
			}
		},

		adminLoad: function(settings) {
			const triggerReturn = $('body').triggerHandler('beforeAdminLoad', [settings]);
			if (triggerReturn === 'break') return false;

			if (typeof mainFormAutosave !== 'undefined') {
				mainFormAutosave.clear();
			}

			settings = $.requestSettings(settings);

			let path = settings.path;
			const data = $.getData(settings);

			if (settings.additionalParams && settings.additionalParams.trim() !== '') {
				path += '?' + settings.additionalParams;
			}

			// Кешируем контейнер окна
			const $window = $("#" + settings.windowId);

			// 1. ОПТИМИЗИРОВАННЫЙ СБОР ЧЕКБОКСОВ
			const $checkedItems = $window.find(":input[type='checkbox'][id^='check_']:checked");

			$checkedItems.each(function() {
				const id = this.id;
				const arr = REGEX_CHECK_ID.exec(id);

				if (arr) {
					data[`hostcms[checked][${arr[1]}][${arr[2]}]`] = 1;

					// Поиск связанных значений (apply_...)
					// Используем querySelectorAll внутри контекста (быстрее чем find по ID с экранированием)
					// Но для надежности оставим jQuery селектор, но оптимизируем экранирование
					const elementIdEscaped = $.escapeSelector(id);
					const $itemsValue = $window.find(`:input[id^='apply_${elementIdEscaped}_fv_']`);

					$itemsValue.each(function() {
						const $valItem = $(this);
						let sValue;

						if ($valItem.attr("type") === 'checkbox') {
							sValue = $valItem.prop('checked') ? '1' : '0';
						} else {
							sValue = $valItem.val();
						}
						data[$valItem.attr('name')] = sValue;
					});
				}
			});

			// 2. СТАНДАРТНЫЕ ФИЛЬТРЫ
			const $filtersItems = $window.find(":input[name^='admin_form_filter_']");
			$filtersItems.each(function() {
				const $filter = $(this);
				const val = $filter.val();

				if (typeof val === 'string' && val.length < 256) {
					let admin_filter_value = val;
					// Используем .attr('type') вместо getInputType(), если это не специфичный плагин
					if ($filter.attr('type') === 'checkbox') {
						admin_filter_value = $filter.is(':checked') ? 1 : 0;
					}
					data[$filter.attr('name')] = admin_filter_value;
				}
			});

			// 3. РАСШИРЕННЫЕ ФИЛЬТРЫ (Top Filters)
			const $topFilter = $('.topFilter');
			let filterId = null;

			if ($topFilter.is(':visible')) {
				filterId = $('#filterTabs .active').data('filter-id');
			}
			data['hostcms[filterId]'] = filterId;

			if (filterId) {
				const $topFiltersItems = $window.find(`#filter-${filterId} :input[name^='topFilter_']`);

				$topFiltersItems.each(function() {
					const $filter = $(this);
					const val = $filter.val() || '';

					if (val.length < 256) {
						let filter_value = val;
						if ($filter.attr('type') === 'checkbox') {
							filter_value = $filter.is(':checked') ? 1 : 0;
						}
						data[$filter.attr('name')] = filter_value;
					}
				});
			}

			// Очистка сообщений
			$window.find("#id_message").empty();

			$.loadingScreen('show');

			$.ajax({
				context: $window,
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				abortOnRetry: 1,
				success: [
					function() {
						if (settings.windowId === 'id_content') {
							$.pushHistory(path, data);
						}
					},
					$.ajaxCallback,
					$.ajaxCallbackSkin,
					(typeof readCookiesForInitiateSettings !== 'undefined' ? readCookiesForInitiateSettings : function(){})
				]
			});

			return false;
		},

		pushHistory: function(path, data) {
			if (window.history && window.history.pushState && window.history.replaceState) {
				// Remove all hostcms[]=... options using cached Regex
				path = path.replace(REGEX_HOSTCMS_PARAMS, '');

				if ($.isiOS && $.isiOS()) {
					const aUrlOptions = [];
					$.each(data, function(key, value) {
						if (key.startsWith('hostcms') && value != null) {
							aUrlOptions.push(key + '=' + encodeURIComponent(value));
						}
					});

					if (aUrlOptions.length) {
						path += '&' + aUrlOptions.join('&');
					}
				}

				const state = {
					windowId: 'id_content',
					url: path,
					data: data
				};
				window.history.pushState(state, document.title, path);
			}
		},

		adminSendForm: function(settings) {
			const triggerReturn = $('body').triggerHandler('beforeAdminSendForm', [settings]);
			if (triggerReturn === 'break') return false;

			if (typeof mainFormAutosave !== 'undefined') mainFormAutosave.clear();

			settings = $.requestSettings(settings);
			settings = $.extend({ buttonObject: '' }, settings);

			const $window = $("#" + settings.windowId);

			if (typeof wysiwyg !== 'undefined') {
				wysiwyg.saveAll($window);
			}

			if (typeof syntaxhighlighter !== 'undefined') {
				syntaxhighlighter.saveAll(settings);
			}

			const $button = $(settings.buttonObject);
			const $form = $button.closest('form');
			const data = $.getData(settings);
			let path = $form.attr('action');

			if (settings.additionalParams && settings.additionalParams.trim() !== '') {
				path += ((path.indexOf('?') === -1) ? '?' : '&') + settings.additionalParams;
			}

			$window.find("#id_message").empty();
			$.loadingScreen('show');

			// Проверка на наличие плагина ajaxSubmit
			if (typeof $form.ajaxSubmit === 'function') {
				$form.ajaxSubmit({
					data: data,
					context: $window,
					url: path,
					dataType: 'json',
					cache: false,
					success: [
						function() {
							if (settings.windowId === 'id_content') {
								$.pushHistory(path, data);
							}
						},
						$.ajaxCallback
					]
				});
			} else {
				console.error("Plugin ajaxSubmit is missing!");
				// Fallback to standard ajax if needed, or just alert
			}
		},

		widgetLoad: function(settings) {
			settings = $.extend({ button: null }, settings);

			if (settings.button) {
				settings.button.addClass('fa-spin');
			}

			const data = $.getData({});

			return $.ajax({
				context: settings.context,
				url: settings.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(response) {
					if (this && this.html) {
						this.html(response.form_html);
					}
				},
				complete() {
					if (settings.button) {
						settings.button.removeClass('fa-spin');
					}
				}
			});
		},

		widgetRequest: function(settings) {
			$.loadingScreen('show');
			const data = $.getData({});

			$.ajax({
				context: settings.context,
				url: settings.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function() {
					const dataInner = $.getData({});
					const $ctx = $(this); // Сохраняем контекст

					$.ajax({
						context: $ctx,
						url: $ctx.data('hostcmsurl'),
						data: dataInner,
						dataType: 'json',
						type: 'POST',
						success: [$.ajaxCallback, function(returnedData) {
							if (returnedData == null || returnedData.form_html == null) {
								return;
							}
							if (returnedData.form_html === '') {
								$ctx.empty();
							}
						}]
					});
				}
			});
		}
	});
})(jQuery);