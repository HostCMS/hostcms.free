/* global hostcmsBackend, wysiwyg, mainFormLocker, bootbox, readCookie, themeprimary */

(function($) {
	"use strict";

	$.extend({
		currentMenu: function(moduleName) {
			const $sidebarLi = $('#sidebar li');
			$sidebarLi.removeClass('active open');

			const $menuModule = $('#menu-' + moduleName);
			$menuModule.each(function() {
				const $this = $(this);
				$this.addClass('active')
					.parents('li').addClass('active open');

				// Submenu
				if ($this.children('ul').length) {
					$this.addClass('open');
				}
			});

			$('#sidebar li').not('.open').find('ul.submenu').hide();
		},

		loadNavSidebarMenu: function(data) {
			data.loadNavSidebarMenu = 1;

			$.ajax({
				url: hostcmsBackend + '/user/index.php',
				type: "POST",
				data: data,
				dataType: 'json',
				error: function() {},
				success: function(answer) {
					$('.nav.sidebar-menu').html(answer.form_html);

					if (typeof data.moduleName !== 'undefined') {
						const $menuItem = $('li#menu-' + data.moduleName);
						if ($menuItem.length) {
							const menuDropdown = $menuItem.parents('ul').prev();
							if (menuDropdown.length && typeof menuDropdown.effect === 'function') {
								menuDropdown.effect('pulsate', { times: 3 }, 3000);
							}
						}
					}
				}
			});
		},

		afterContentLoad: function(jWindow, data) {
			data = typeof data !== 'undefined' ? data : {};

			if (data.title && jWindow.attr('id') !== 'id_content') {
				const jSpanTitle = jWindow.find('span.ui-dialog-title');
				if (jSpanTitle.length) {
					// Внимание: в оригинале здесь устанавливался data.error вместо data.title.
					// Если это ошибка, замените на data.title. Оставил как в оригинале, но с проверкой.
					jSpanTitle.empty().html(data.error || data.title);
				}
			}

			setResizableAdminTableTh();
		},

		beforeContentLoad: function($object) {
			if (typeof wysiwyg !== 'undefined') {
				wysiwyg.removeAll($object);
			}
		},

		insertContent: function(jObject, content) {
			// Fix blink in FF
			jObject.scrollTop(0).empty().html(content);
		},

		windowSettings: function(settings) {
			return $.extend({
				Closable: true
			}, settings);
		},

		openWindow: function(settings) {
			settings = $.windowSettings($.requestSettings(settings));

			settings = $.extend({
				open: function() {
					const uiDialog = $(this).parent('.ui-dialog');
					// Оптимизация: чтение и запись одной строкой, если значения не меняются - лишнее, но оставил для совместимости
					uiDialog.css({ width: uiDialog.width(), height: uiDialog.height() });
				},
				close: function() {
					$(this).dialog('destroy').remove();
				}
			}, settings);

			let url = settings.path;
			if (settings.additionalParams && settings.additionalParams.trim() !== '') {
				url += '?' + settings.additionalParams;
			}

			const $body = $('body');
			let windowCounter = $body.data('windowCounter');
			if (typeof windowCounter === 'undefined') { windowCounter = 0; }
			$body.data('windowCounter', windowCounter + 1);

			const jDivWin = $('<div>')
				.addClass("hostcmsWindow")
				.attr("id", "Window" + windowCounter)
				.appendTo(document.body)
				.dialog(settings);

			const data = $.getData(settings);
			// Change window id
			data['hostcms[window]'] = jDivWin.attr('id');

			mainFormLocker.saveStatus().unlock();

			$.ajax({
				context: jDivWin,
				url: url,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: [$.ajaxCallback, function() { mainFormLocker.restoreStatus(); }]
			});

			return jDivWin;
		},

		openWindowAddTaskbar: function(settings) {
			return $.adminLoad(settings);
		},

		showWindow: function(windowId, content, settings) {
			settings = $.extend({
				autoOpen: true,
				addContentPadding: false,
				resizable: true,
				draggable: true,
				Minimize: false,
				Closable: true
			}, settings);

			let jWin = $('#' + windowId);

			if (!jWin.length) {
				jWin = $('<div>')
					.addClass('hostcmsWindow')
					.attr('id', windowId)
					.html(content)
					.HostCMSWindow(settings);
			}
			return jWin;
		},

		ajaxCallbackModal: function(data) {
			$.loadingScreen('hide');
			if (data == null || data.form_html == null) {
				alert('AJAX response error.');
				return;
			}

			const jObject = $(this);
			const jBody = jObject.find(".modal-body");

			if (data.form_html !== '') {
				$.beforeContentLoad(jBody);
				$.insertContent(jBody, data.form_html);
				$.afterContentLoad(jBody, data);
			}

			let jMessage = jBody.find("#id_message");

			if (jMessage.length === 0) {
				jMessage = $("<div>").attr('id', 'id_message');
				jBody.prepend(jMessage);
			}

			jMessage.empty().html(data.error);

			if (data.title) {
				jObject.find(".modal-title").text(data.title);
			}
		},

		modalLoad: function(settings) {
			settings = $.requestSettings(settings);

			const modalWindowId = 'Modal_' + Date.now();

			// Клонируем settings и добавляем windowId для получения данных
			const dataConfig = $.extend({}, settings, { windowId: modalWindowId });
			const data = $.getData(dataConfig);

			let path = settings.path;
			settings.additionalParams += '&modalWindowId=' + encodeURIComponent(modalWindowId);

			if (settings.additionalParams && settings.additionalParams.trim() !== '') {
				path += '?' + settings.additionalParams;
			}

			$.loadingScreen('show');

			$.ajax({
				context: $('#' + settings.windowId),
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				abortOnRetry: 1,
				success: [function(returnedData) {
					$.loadingScreen('hide');

					const newSettings = $.extend({
						title: returnedData.title,
						message: `<div id="${modalWindowId}"><div id="id_message"></div>${returnedData.form_html}</div>`,
						width: '80%',
						error: returnedData.error
					}, settings);

					$.modalWindow(newSettings);
				}]
			});

			return false;
		},

		modalWindow: function(settings) {
			mainFormLocker.unlock();

			settings = $.extend({
				title: '',
				message: '',
				error: '',
				className: ''
			}, settings);

			const dialog = bootbox.dialog({
				message: ' ', // Заполняется позже
				title: $.escapeHtml(settings.title),
				className: settings.className,
				onEscape: function() {
					arguments[0].stopImmediatePropagation();
				}
			});

			const modalBody = dialog.find('.modal-body');

			// save global
			window.currentDialog = dialog;

			dialog.on('shown.bs.modal', function() {
				$('html').css('overflow', 'hidden');
			});

			if (settings.onShown) dialog.on('shown.bs.modal', settings.onShown);
			if (settings.onHide) dialog.on('hide.bs.modal', settings.onHide);

			dialog.on('hidden.bs.modal', function() {
				if ($(".modal").hasClass('in')) {
					$('body').addClass('modal-open');
				}
				$('html').css('overflow', '');
				delete window.currentDialog;
			});

			dialog.on('hide.bs.modal', function(event) {
				const triggerReturn = $('body').triggerHandler('beforeHideModal');
				if (triggerReturn === 'break') event.preventDefault();
			});

			const oContentBlock = settings.AppendTo ? $(settings.AppendTo) : $(window);
			const widthContentBlock = oContentBlock.width() - 50;
			const widthModalDialog = (settings.width && $.isNumeric(settings.width) && settings.width > widthContentBlock)
				? widthContentBlock
				: settings.width;

			dialog.find('.modal-dialog')
				.data({ 'originalWidth': settings.width ? settings.width : widthModalDialog })
				.width(widthModalDialog);

			if (typeof settings.height !== 'undefined') {
				modalBody.height(settings.height);
			}

			$.insertContent(modalBody, settings.message);

			if (settings.error !== '') {
				const jMessage = modalBody.find('#id_message');
				$(jMessage[0]).empty().html(settings.error);
				$(jMessage[0]).nextAll().remove();
			}
		},

		resizeIframe: function(object) {
			if (object.contentWindow !== null) {
				setTimeout(function() {
					// Проверка на наличие document, чтобы избежать ошибок кросс-домена или закрытых окон
					if (object.contentWindow.document) {
						object.style.height = (object.contentWindow.document.documentElement.scrollHeight) + 'px';
						object.style.width = (object.contentWindow.document.documentElement.scrollWidth) + 'px';
					}
				}, 100);
			}

			$(object).ready(function() {
				setTimeout(function() {
					try {
						$(object).contents().find('body').on('keyup', function(e) {
							if (e.keyCode === 27) { // ESC
								e.preventDefault();
								$('.hostcmsWindow').dialog("close");
							}
						});
					} catch(e) { /* Cross-origin protection */ }
				}, 50);
			});
		},

		loadSelectOptionsCallback: function(data) {
			$.loadingScreen('hide');

			const $this = $(this);
			const jTopParentDiv = $this.parents('div[id^=property],div[id^=field]');
			const jInput = jTopParentDiv.find('[id^=input_]');
			const jSelectTopParentDiv = $this.parents('div[class^=form-group]');
			const jInputTopParentDiv = jInput.parents('div[class^=form-group]');

			if (data && 'mode' in data) {
				if (data['mode'] === 'select') {
					jInputTopParentDiv.addClass('hidden');
					jSelectTopParentDiv.removeClass('hidden');
					$this.empty().appendOptions(data['values']);
				} else if (data['mode'] === 'input') {
					$this.empty();
					jInput.val(null).trigger("change");
					jSelectTopParentDiv.addClass('hidden');
					jInputTopParentDiv.removeClass('hidden');
				}
			} else {
				$this.empty().appendOptions(data);
			}

			const setOptionId = $this.data('setOptionId') || $this.data('setoptionid');
			if (setOptionId) {
				$this.val(setOptionId).removeData('setOptionId');
			}

			// Call change
			$this.change();
		},

		loadDivContentAjaxCallback: function(data) {
			const $form = $(this);
			const $a = $("a.lib-edit", $form);

			$.loadingScreen('hide');

			if (data.id) {
				$a.attr('href', data.editHref).removeClass('hidden');
			} else {
				$a.addClass('hidden');
			}

			$("#lib_properties", $form).empty().html(data.optionsHtml);
		},

		pasteStandartAnswer: function(data) {
			$.loadingScreen('hide');
			const $this = $(this);
			$this.val($this.val() + data);
		},
	});

	$.fn.extend({
		HostCMSWindow: function(settings) {
			const object = $(this);

			settings = $.extend({
				title: '',
				message: '<div id="' + object.attr('id') + '"><div id="id_message"></div>' + object.html() + '</div>'
			}, settings);

			$.modalWindow(settings);
			object.remove();
		},
	});
})(jQuery);

// --- Global Functions ---

// Настройка отображения заголовка окна
function navbarHeaderCustomization(withoutAnimation) { // eslint-disable-line
	const $navbarAccount = $('.navbar .navbar-inner .navbar-header .navbar-account');

	if (!$navbarAccount.length || $navbarAccount.data('animationProcess')) {
		return;
	}

	const $accountArea = $('.navbar .navbar-inner .navbar-header .account-area');
	const $accountAreaLi = $accountArea.find('li:not(:hidden)');

	let leftNavbarArrow = $navbarAccount.find('#leftNavbarArrow');
	let leftNavbarArrowIsExist = leftNavbarArrow.length;
	let leftNavbarArrowIsShown = leftNavbarArrowIsExist ? !leftNavbarArrow.hasClass('hide') : false;

	const rightNavbarArrow = $navbarAccount.find('#rightNavbarArrow');
	const rightNavbarArrowIsShown = rightNavbarArrow.length ? !rightNavbarArrow.hasClass('hide') : false;

	// Сброс настроек
	if (leftNavbarArrowIsShown || rightNavbarArrowIsShown) {
		$accountArea
			.find('.invisible, .hide')
			.removeClass('invisible hide')
			.css('display', '');

		if (leftNavbarArrowIsShown) leftNavbarArrow.addClass('hide');
		if (rightNavbarArrowIsShown) rightNavbarArrow.addClass('hide');
	}

	let offsetLeftAccountArea = $accountArea.offset().left;

	// Проверяем, уходит ли меню влево за пределы экрана
	if (offsetLeftAccountArea < 0 && Math.abs(offsetLeftAccountArea) >= $accountAreaLi.eq(0).outerWidth(true) * 0.4) {
		let countElementsOffset = 0;

		$accountAreaLi.each(function() {
			const liWidth = $(this).outerWidth(true);
			offsetLeftAccountArea += liWidth;

			$(this).addClass('invisible');

			if (offsetLeftAccountArea > 0) {
				// Если следующий элемент помещается меньше чем на 60%, скрываем и его
				const nextEl = $accountAreaLi.eq(countElementsOffset + 1);
				if (nextEl.length && offsetLeftAccountArea < 0.6 * nextEl.outerWidth(true)) {
					nextEl.addClass('invisible');
					countElementsOffset++;
				}

				if (!$navbarAccount.find('#leftNavbarArrow').length) {
					$navbarAccount.append('<div id="leftNavbarArrow"><a href="#"><i class="icon fa fa-chevron-left"></i></a></div>');
					leftNavbarArrow = $navbarAccount.find('#leftNavbarArrow');
					leftNavbarArrowIsExist = true;
				}

				if (leftNavbarArrowIsExist && leftNavbarArrow.hasClass('hide')) {
					leftNavbarArrow.removeClass('hide');
				}

				leftNavbarArrow.data('countElementsOffset', countElementsOffset);

				if (rightNavbarArrowIsShown) {
					leftNavbarArrow.trigger('touchend', [!!withoutAnimation]);
				}

				return false; // break loop
			}

			countElementsOffset++;
		});
	}
}

function setResizableAdminTableTh() {
	const $headers = $(".admin-table th:not(.action-checkbox):not(.sticky-column)");

	$headers.resizable({
		handles: 'e',
		resize: function(event, ui) {
			ui.size.width = ui.size.width + 22;
		},
		start: function(event, ui) {
			const $o = ui.originalElement.eq(0);
			$o.prop('width', $o.outerWidth(true) + 'px');
		},
		stop: function(event, ui) {
			const $table = $('table.admin-table');
			const width = ui.size.width;

			// Сбор данных из data-атрибутов
			const params = {
				'saveAdminFieldWidth': 1,
				'admin_form_id': $table.data('admin-form-id'),
				'admin_form_field_id': ui.originalElement.eq(0).data('admin-form-field-id'),
				'site_id': $table.data('site-id'),
				'modelsNames': $table.data('models-names'),
				'width': width
			};

			$.loadingScreen('show');

			$.ajax({
				url: hostcmsBackend + '/admin_form/index.php',
				data: params,
				dataType: 'json',
				type: 'POST',
				success: function() {
					$.loadingScreen('hide');
				}
			});
		}
	});

	const $visibleHeaders = $('table.admin-table th:not([width]):not(.datetime):visible:not(.action-checkbox):not([class*="filter-action-"])');

	if (!$visibleHeaders.length) return;

	if ($('#checkbox_fixedtables').is(':checked') || readCookie("tables-fixed") === "true") {
		$visibleHeaders
			.width('')
			.removeClass('resizable-th')
			.find('i.th-width-toggle')
			.remove();
		return;
	}

	const $scrollableWrap = $visibleHeaders.parents('.table-scrollable');
	const wrapScrollLeft = $scrollableWrap.scrollLeft();

	const thMinOuterWidth = 90;
	const thMaxOuterWidth = 250;

	$visibleHeaders.width('');

	// Оптимизация: чтение DOM вне цикла или минимизация перекомпоновки
	$visibleHeaders.each(function() {
		const $this = $(this);
		const wideData = $this.data('wide');

		if (wideData > 0) {
			$this.find('i').removeClass('fa-expand').addClass('fa-compress');
			$this.data('prev-width', $this.outerWidth()).css('width', wideData);
		} else {
			$this.find('i').addClass('fa-expand').removeClass('fa-compress');

			let removeResizable = true;
			const currentWidth = $this.width();
			const outerW = $this.outerWidth();
			const thLeftRightPaddings = outerW - currentWidth;
			const thMinContentWidth = thMinOuterWidth - thLeftRightPaddings;
			const thMaxContentWidth = thMaxOuterWidth - thLeftRightPaddings;

			if (currentWidth < thMaxContentWidth) {
				// Измерение реальной ширины контента без создания клона всей таблицы,
				// создаем span с такими же стилями
				const text = $this.text();
				const $testSpan = $('<span>').css({
					'font-family': $this.css('font-family'),
					'font-size': $this.css('font-size'),
					'font-weight': $this.css('font-weight'),
					'visibility': 'hidden',
					'white-space': 'nowrap',
					'position': 'absolute'
				}).text(text).appendTo('body');

				const thContentRealWidth = $testSpan.width() + 25; // +25 запас на иконки/сортування
				$testSpan.remove();

				if (thContentRealWidth > currentWidth || thMinContentWidth > currentWidth) {
					$this.css('width', thLeftRightPaddings + (thMinContentWidth > currentWidth ? thMinContentWidth : currentWidth));
					removeResizable = false;
				}
			}

			if ($this.hasClass('resizable-th')) {
				if (removeResizable) {
					$this.removeClass('resizable-th').find('i.th-width-toggle').remove();
				}
			} else if (!removeResizable) {
				$this.addClass('resizable-th').append('<i class="th-width-toggle fa fa-expand gray"></i>');
			}
		}
	});

	setCursorAdminTableWrap();

	if (wrapScrollLeft) {
		$scrollableWrap.scrollLeft(wrapScrollLeft);
	}
}

function setCursorAdminTableWrap() {
	$('.admin-table-wrap.table-scrollable').each(function() {
		const $wrap = $(this);
		const $table = $wrap.find('table');

		if ($wrap.outerWidth() < $table.outerWidth()) {
			$wrap.addClass('table-draggable');
		} else {
			$wrap.data({ 'curDown': false }).removeClass('table-draggable');
		}
	});
}

function changeDublicateTables() { // eslint-disable-line
	const $tabContent = $(".tab-content > [id^='company-'][class~='active']");

	if (!$tabContent.length) return;

	const $originalTable = $("table[id^='table-company-']", $tabContent);
	const $leftTable = $(".permissions-table-left table", $tabContent);
	const $leftTableTh = $('thead tr th', $leftTable);
	const $leftTopTable = $('.permissions-table-top-left table', $tabContent);
	const $leftTopTableTh = $('thead tr th', $leftTopTable);
	const $tableHead = $(".permissions-table-head", $tabContent);
	const $tableThHead = $('th', $tableHead);

	let widthLeftTable = 0;

	const needsScroll = $('[id^="table-company-"]', $tabContent).outerWidth() - $('.table-scrollable', $tabContent).innerWidth();

	if (needsScroll) {
		$originalTable.addClass('cursor-grab');
		$tableHead.addClass('cursor-grab');
	} else {
		$originalTable.removeClass('cursor-grab');
		$tableHead.removeClass('cursor-grab');
	}

	$("thead tr th", $originalTable).each(function(index) {
		if (index >= 2) return false;

		const thOuterWidth = $(this).outerWidth();
		widthLeftTable += thOuterWidth;

		$leftTableTh.eq(index).outerWidth(thOuterWidth);
		$leftTopTableTh.eq(index).outerWidth(thOuterWidth);
		$tableThHead.eq(index).outerWidth(thOuterWidth);
	});

	$leftTable.width(widthLeftTable + 1);
	$leftTopTable.width(widthLeftTable);
	$tableHead.outerWidth($originalTable.outerWidth());
}

function setTableWithFixedHeaderAndLeftColumn() { // eslint-disable-line
	$(document).one("ajaxSuccess", function() {

		function settingFixedBlocks($tabContent) {
			const $activeTable = $('.tab-content > [id^="company-"].active [id^="table-company-"]');
			if (!$activeTable.length) return;

			const tableOuterWidth = $activeTable.outerWidth();
			const scrollableInnerWidth = $('.tab-content > [id^="company-"].active .table-scrollable').innerWidth();

			if (tableOuterWidth - scrollableInnerWidth > 0) {
				$activeTable.addClass('cursor-grab');
				$('.permissions-table-head').addClass('cursor-grab');
			}

			if ($tabContent.hasClass('active') && !$tabContent.data('fixedBlocksIsSet')) {
				const $originalTable = $("table[id^='table-company-']", $tabContent);
				const $originalTableThead = $("thead", $originalTable);
				const $tableHead = $(".permissions-table-head", $tabContent);
				const $tableThHead = $('th', $tableHead);

				const $leftBlock = $('<div class="permissions-table-left">');
				const $leftTable = $("<table><thead><tr></tr></thead><tbody></tbody></table>");
				const $topLeftBlock = $('<div class="permissions-table-top-left">');
				const $leftTopTable = $("<table><thead><tr></tr></thead></table>");

				const delta = $(window).scrollTop() - $originalTableThead.offset().top;

				if (delta >= 0) {
					$tableHead.css({ 'top': delta, 'visibility': 'visible' });
					$topLeftBlock.css({ 'top': delta, 'visibility': 'visible' });
				}

				$("tr th", $originalTableThead).each(function(index) {
					$($tableThHead[index]).outerWidth($(this).outerWidth());
				});

				$leftTable.addClass($originalTable.attr('class'));
				$leftBlock.append($leftTable);

				$leftTopTable.addClass($originalTable.attr('class'));
				$topLeftBlock.append($leftTopTable);

				$tabContent.append($leftBlock).append($topLeftBlock);

				// Копирование заголовков
				$("thead tr th", $originalTable).each(function(index) {
					if (index >= 2) return false;

					const outerW = $(this).outerWidth();
					const $thLeft = $(this).clone(false).outerWidth(outerW).addClass('invisible-fixed').css({
						'border-bottom': '1px solid #e9e9e9',
						'height': $('thead', $originalTable).outerHeight()
					});
					$("thead tr", $leftTable).append($thLeft);

					const $thTop = $(this).clone(false).outerWidth(outerW).innerHeight($tableHead.innerHeight());
					$("thead tr", $leftTopTable).append($thTop);

					if (index === 1) $("thead tr", $leftTable).append('<th class="no-padding">');
				});

				// Копирование тела таблицы
				$("tbody tr", $originalTable).each(function() {
					const $fixedTr = $('<tr>');
					let cols = 0;

					$("tbody", $leftTable).append($fixedTr);

					$('td', this).each(function(index) {
						const colspan = $(this).attr('colspan');
						cols += parseInt(colspan ? colspan : 1);

						if (index > 0 && cols >= 3) return false;

						const $newTd = $(this).clone(false)
							.addClass($(this).attr('class'))
							.addClass('invisible-fixed');
						$fixedTr.append($newTd);

						if (index === 1) $fixedTr.append('<td class="no-padding">');
					});
				});

				$tabContent.data('fixedBlocksIsSet', true);
				$tableHead.outerWidth($originalTable.outerWidth());
			}
		}

		settingFixedBlocks($(".tab-content > [id^='company-'][class~='active']"));

		let curYPos = 0; // eslint-disable-line
		let curXPos = 0;
		let curDown = false;
		let curScrollLeft = 0;

		// Делегирование событий для динамических элементов
		const $container = $('.tab-content > [id^="company-"]');

		$container.on('mousedown', '[id^="table-company-"], .permissions-table-head', function() {
			if ($(this).hasClass('cursor-grab')) {
				$(this).toggleClass("cursor-grab cursor-grabbing");
				const target = $(this).attr('id') ? '.permissions-table-head' : '.tab-content > [id^="company-"] [id^="table-company-"]';
				$(target).toggleClass("cursor-grab cursor-grabbing");
			}
		});

		$container.on('mouseup', '[id^="table-company-"], .permissions-table-head', function() {
			if ($(this).hasClass('cursor-grabbing')) {
				$(this).toggleClass("cursor-grabbing cursor-grab");
				const target = $(this).attr('id') ? '.permissions-table-head' : '.tab-content > [id^="company-"] [id^="table-company-"]';
				$(target).toggleClass("cursor-grab cursor-grabbing");
			}
		});

		// Скроллинг
		$container.on('mousedown', '.table-scrollable', function(event) {
			curDown = true;
			curYPos = event.pageY;
			curXPos = event.pageX;
			curScrollLeft = $(this).scrollLeft();
			event.preventDefault();
		});

		$(document).on('mouseup', function() {
			curDown = false;
		});

		$container.on('mousemove', '.table-scrollable', function(event) {
			if (curDown === true) {
				// requestAnimationFrame для плавности
				requestAnimationFrame(() => {
					$(this).scrollLeft(parseInt(curScrollLeft + (curXPos - event.pageX)));
				});
			}
		});

		$container.on('mouseout', '.table-scrollable', function(event) {
			if (!$(this).find(event.relatedTarget).length && curDown) {
				curDown = false;
				$('.tab-content > [id^="company-"] [id^="table-company-"], .permissions-table-head')
					.removeClass("cursor-grabbing cursor-grab")
					.addClass("cursor-grab");
			}
		});

		$container.on('scroll', '.table-scrollable', function() {
			if ($(this).parent().hasClass('active')) {
				const $leftBlock = $('~ .permissions-table-left ', this);
				const scrollValue = $(this).scrollLeft();

				if (scrollValue && $leftBlock.css('visibility') !== 'visible') {
					$leftBlock.css('visibility', 'visible');
					$('.invisible-fixed', $leftBlock).removeClass('invisible-fixed').addClass('visible-fixed');
				} else if (!scrollValue) {
					$leftBlock.css('visibility', 'hidden');
					$('.visible-fixed', $leftBlock).removeClass('visible-fixed').addClass('invisible-fixed');
				}
			}
		});

		// Оптимизированный scroll окна
		let scrollTimeout;
		$(window).on('scroll', function() {
			if (scrollTimeout) return;

			scrollTimeout = requestAnimationFrame(function() {
				const $tabContent = $(".tab-content > [id^='company-'][class~='active']");

				if ($tabContent.length) {
					const $originalTable = $("table[id^='table-company-']", $tabContent);
					const $originalTableThead = $("thead", $originalTable);
					const $tableHead = $(".permissions-table-head", $tabContent);
					const $topLeftBlock = $('.permissions-table-top-left', $tabContent);

					if ($originalTableThead.length) {
						const delta = $(window).scrollTop() - $originalTableThead.offset().top;

						if (delta >= 0) {
							if ($tableHead.css('visibility') !== 'visible') {
								$tableHead.css({ 'visibility': 'visible' });
								$topLeftBlock.css({ 'visibility': 'visible' });
							}
							$tableHead.css({ 'top': delta });
							$topLeftBlock.css({ 'top': delta });
						} else if ($tableHead.css('visibility') === 'visible') {
							$tableHead.css('visibility', 'hidden');
							$topLeftBlock.css('visibility', 'hidden');
						}
					}
				}
				scrollTimeout = null;
			});
		});

		$('#agregate-user-info a[data-toggle="tab"]').on('shown.bs.tab', function() {
			settingFixedBlocks($($(this).attr('href')));
		});
	});
}

function setSlimScrolling4SidebarMenu() {
	if (!$('.page-sidebar').hasClass('menu-compact')) {
		const position = (readCookie("rtl-support") || location.pathname === "/index-rtl-fa.html" || location.pathname === "/index-rtl-ar.html") ? 'right' : 'left';
		$('.sidebar-menu').slimscroll({
			position: position,
			size: '3px',
			color: themeprimary,
			height: $(window).height() - 90,
		});
	}
}

function readCookiesForInitiateSettings() { // eslint-disable-line
	if (readCookie("navbar-fixed-top") === "true") {
		$('#checkbox_fixednavbar').prop('checked', true);
		$('.navbar').addClass('navbar-fixed-top');
	}

	if (readCookie("sidebar-fixed") === "true") {
		$('#checkbox_fixedsidebar').prop('checked', true);
		$('.page-sidebar').addClass('sidebar-fixed');
		setSlimScrolling4SidebarMenu();
	}

	if (readCookie("breadcrumbs-fixed") === "true") {
		$('#checkbox_fixedbreadcrumbs').prop('checked', true);
		$('.page-breadcrumbs').addClass('breadcrumbs-fixed');
	}

	if (readCookie("page-header-fixed") === "true") {
		$('#checkbox_fixedheader').prop('checked', true);
		$('.page-header').addClass('page-header-fixed');
	}

	// HostCMS
	if (readCookie("tables-fixed") === "true") {
		$('#checkbox_fixedtables').prop('checked', true);
	}
}

function datetimepickerOnShow() { // eslint-disable-line
	const $datetimePickerWidget = $('.bootstrap-datetimepicker-widget.dropdown-menu');
	if ($datetimePickerWidget.length) {
		const offset = $datetimePickerWidget.offset();
		$datetimePickerWidget
			.detach()
			.appendTo('.page-container')
			.offset({
				'top': offset.top,
				'left': offset.left
			})
			.css({ 'bottom': 'auto' });
	}
}

// Конструктор фильтра (Class-like function)
function cSelectFilter(windowId, sObjectId) { // eslint-disable-line
	this.windowId = $.getWindowId(windowId);
	// Экранирование для ID
	this.sObjectId = sObjectId.replace(/(:|\.|\[|\]|,)/g, "\\$1");
	this.ignoreCase = true;
	this.timeout = null;
	this.pattern = '';
	this.is_filtering = false;
	this.oCurrentSelectObject = null;

	this.Set = function(pattern) {
		this.pattern = pattern;
		this.is_filtering = (pattern.length !== 0);
	};

	this.SetIgnoreCase = function(value) {
		this.ignoreCase = value;
	};

	this.GetCurrentSelectObject = function() {
		this.oCurrentSelectObject = $("#" + this.windowId + " #" + this.sObjectId);
	};

	this.Init = function() {
		this.GetCurrentSelectObject();
	};

	this.Filter = function() {
		const self = this;
		const $icon = $("#" + this.windowId + " #filter_" + this.sObjectId).prev('span').find('i');

		$icon.removeClass('fa-search').addClass('fa-spinner fa-spin');

		// Debounce logic moved to caller or handled here by simple timeout
		if (this.timeout) clearTimeout(this.timeout);

		this.timeout = setTimeout(function() {
			if (self.is_filtering) {
				self.GetCurrentSelectObject();
			}

			if (!self.oCurrentSelectObject || !self.oCurrentSelectObject.length) {
				self.Init();
			}

			if (self.oCurrentSelectObject.length === 1) {
				if (self.is_filtering) {
					const attributes = self.ignoreCase ? 'i' : '';
					// Безопасное создание RegExp
					try {
						const safePattern = self.pattern.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
						const regexp = new RegExp(safePattern, attributes);

						const $options = self.oCurrentSelectObject.find("option");

						// Batch DOM updates: add/remove classes
						$options.each(function() {
							const $opt = $(this);
							if (regexp.test(' ' + $opt.text())) {
								$opt.removeClass('hidden');
							} else {
								$opt.addClass('hidden');
							}
						});

						const $shown = self.oCurrentSelectObject.find('option:not(.hidden)');
						if ($shown.length) {
							self.oCurrentSelectObject.val($shown.eq(0).val());
						}
					} catch (e) {
						console.error("Regex error in filter", e);
					}
					self.oCurrentSelectObject.trigger('change');
				} else {
					self.oCurrentSelectObject.find('option').removeClass('hidden');
					self.oCurrentSelectObject.prop('selectedIndex', 0);
					self.oCurrentSelectObject.trigger('change');
				}

				$icon.removeClass('fa-spinner fa-spin').addClass('fa-search');
			}
		}, 100);
	};
}

function radiogroupOnChange(windowId, value, values, hiddenName, shownName) { // eslint-disable-line
	values = values || [0, 1];
	hiddenName = hiddenName || 'hidden';
	shownName = shownName || 'shown';

	// FIX: используем for...of для значений массива, а не индексов
	for (const val of values) {
		if (value != val) {
			$("#" + windowId + " ." + hiddenName + "-" + val).show();
			$("#" + windowId + " ." + shownName + "-" + val).hide();
		}
	}

	$("#" + windowId + " ." + hiddenName + "-" + value).hide();
	$("#" + windowId + " ." + shownName + "-" + value).show();
}

// Lazy image load
document.addEventListener("DOMContentLoaded", function() {
	let lazyloadThrottleTimeout;

	function lazyload(event) {
		if (lazyloadThrottleTimeout) {
			clearTimeout(lazyloadThrottleTimeout);
		}

		lazyloadThrottleTimeout = setTimeout(function() {
			const scrollTop = window.scrollY;
			const lazyloadImages = document.querySelectorAll("img.lazy");

			// Если изображений нет, нет смысла продолжать
			if (lazyloadImages.length === 0) return;

			const triggerHeight = window.innerHeight + scrollTop; // eslint-disable-line

			lazyloadImages.forEach(function(img) {
				const isModalEvent = (typeof event !== 'undefined' && event.data && event.data.modal);

				if (isModalEvent || img.getBoundingClientRect().top < window.innerHeight) {
					img.src = img.dataset.src;
					img.classList.remove('lazy');
				}
			});
		}, 50); // Уменьшен таймаут для большей отзывчивости
	}

	// Modern IntersectionObserver support
	if ("IntersectionObserver" in window) {
		const imageObserver = new IntersectionObserver(function(entries, observer) { // eslint-disable-line
			entries.forEach(function(entry) {
				if (entry.isIntersecting) {
					const image = entry.target;
					image.src = image.dataset.src;
					image.classList.remove("lazy");
					imageObserver.unobserve(image);
				}
			});
		});

		const imgs = document.querySelectorAll("img.lazy");
		imgs.forEach(function(image) {
			imageObserver.observe(image);
		});

		// Fallback triggers for dynamic content
		$('#id_content').on('adminLoadSuccess', function() {
			const newImgs = document.querySelectorAll("img.lazy");
			newImgs.forEach(img => imageObserver.observe(img));
		});
	} else {
		// Fallback for older browsers
		document.addEventListener("scroll", lazyload);
		window.addEventListener("resize", lazyload);
		window.addEventListener("orientationChange", lazyload);
		$('#id_content').on('adminLoadSuccess', lazyload);
		$(document).on("shown.bs.modal", { modal: true }, lazyload);
	}

	// Initial call
	if (!("IntersectionObserver" in window)) lazyload();

}, false);