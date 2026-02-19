/* global hostcmsBackend, i18n, bootbox, Notify, jQuery */
"use strict";

(function($){
	$.extend({
		updateCaldav: function() {
			var $calendar = $("#calendar");
			if ($calendar.length)
			{
				$.ajax({
					url: hostcmsBackend + '/calendar/index.php',
					type: 'POST',
					dataType: 'json',
					data: { 'updateCaldav': 1 },
					success: function(){
						$calendar.fullCalendar("refetchEvents");
					},
					error: function(){}
				});
			}
		},
		// Обработчики событий календаря
		calendarPrepare: function (){
			$(document)
				.on('shown.bs.popover', 'a.fc-event', function() {
					$('.popover .calendar-event-description').slimscroll({
						height: '75px',
						color: 'rgba(0,0,0,0.3)',
						size: '5px',
					});
				})
				// Удаление события календаря
				.on('click', '.popover #deleteCalendarEvent', function () {
					var $this = $(this),
						eventId = $this.data('eventId'),
						moduleId = $this.data('moduleId');

					if (eventId && moduleId)
					{
						bootbox.confirm({
							message: i18n['remove_event'],
							buttons: {
								confirm: {
									label: i18n['yes'],
									className: 'btn-success'
								},
								cancel: {
									label: i18n['no'],
									className: 'btn-danger'
								}
							},
							callback: function (result) {
								// Удаление события
								if (result)
								{
									$.loadingScreen('show');
									var ajaxData = $.extend({}, $.getData({}), {'eventId': eventId, 'moduleId': moduleId});

									$.ajax({
										url: hostcmsBackend + '/calendar/index.php?eventDelete',
										type: "POST",
										dataType: 'json',
										data: ajaxData,
										success: function (result){
											$.loadingScreen('hide');

											if (!result['error'] && result['message'])
											{
												// Удаляем событие из календаря
												$('#calendar').fullCalendar('removeEvents', eventId + '_' + moduleId);
												Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'success', 'fa-check', true);
											}
											 // Ошибка
											else if (result['message'])
											{
												Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'danger', 'fa-warning', true);
											}
										}
									});
								}
							}
						});
					}
				})
				// Редактирование события календаря
				.on('click', '.popover #editCalendarEvent', function () {
					var $this = $(this),
						eventId = $this.data('eventId'),
						moduleId = $this.data('moduleId'),
						$content = $('#id_content'),
						dH = $(window).height(),
						wH = $content.outerHeight(),
						eventElement = $('[data-event-id="' + eventId + '_' + moduleId + '"]');

					if (eventElement.popover) {
						eventElement.popover('hide');
					}

					$.openWindow({
						path: hostcmsBackend + '/calendar/index.php?addEntity&eventId=' + eventId + '&moduleId=' + moduleId,
						addContentPadding: false,
						width: $content.outerWidth() * 0.9,
						height: (dH < wH ? dH : wH) * 0.9,
						AppendTo: $content.parent().get(0),
						positionOf: '#id_content',
						Maximize: false,
						dialogClass: 'hostcms6'
					})
					.addClass('modalwindow');
				})
				.on('click', '.popover-calendar-event button.close' , function(){
					var popoverId = $(this).closest('.popover-calendar-event').attr('id'),
						calendarEvent = $(".fc-event[aria-describedby='" + popoverId +"']");

					calendarEvent.popover('hide');
				});
		}
	});
})(jQuery);

/* Глобальные функции (оставлены в global scope для совместимости с вызовами FullCalendar) */
function calendarDayClick(oDate, jsEvent) {
	var contextMenu = $('#calendarContextMenu'),
		windowWidth = $(window).width(),
		contextMenuWidth = contextMenu.outerWidth(),
		// Оптимизация получения координат
		isTouch = jsEvent.type === 'touchend',
		sourceEvent = isTouch ? jsEvent.originalEvent.changedTouches[0] : jsEvent,
		pageX = sourceEvent.pageX,
		pageY = isTouch ? (sourceEvent.pageY + 10) : sourceEvent.pageY,
		positionLeft = (pageX + contextMenuWidth > windowWidth) ? (windowWidth - contextMenuWidth) : pageX;

	contextMenu.show().css({top: pageY, left: positionLeft});
	$('ul.dropdown-info').data('timestamp', oDate.unix());
}

function calendarEvents(start, end, timezone, callback) {
	var ajaxData = $.getData({});
	ajaxData['start'] = start.unix();
	ajaxData['end'] = end.unix();

	$.ajax({
		url: hostcmsBackend + '/calendar/index.php?loadEvents',
		type: 'POST',
		dataType: 'json',
		data: ajaxData,
		success: function(result) {
			var events = (result && result['events'] && result['events'].length)
				? result['events']
				: [];
			callback(events);
		}
	});
}

function calendarEventClick(event) {
	var eventIdParts = event.id.split('_'),
		eventId = eventIdParts[0];

	$.modalLoad({
		path: event.path,
		action: 'edit',
		operation: 'modal',
		additionalParams: 'hostcms[checked][0][' + eventId + ']=1&parentWindowId=id_content',
		windowId: 'id_content'
	});
}

var newlineRegex = /\n/g;

function calendarEventRender(event, element) {
	if (event.dragging || event.resizing)
	{
		element.popover('destroy');
		return;
	}

	// Атрибуты и стили
	element.attr('data-event-id', event.id);
	element.css({'background-color': '#fbfbfb'});

	var $element = element.find('.fc-content'),
		htmlBuffer = '';

	// Собираем HTML строку, вместо множественных вызовов append
	if (event.description) {
		htmlBuffer += '<span class="fc-description">' + $.escapeHtml(event.description).replace(newlineRegex, "<br>") + '</span>';
	}

	if (event.place) {
		htmlBuffer += '<span class="fc-place"><i class="fa fa-map-marker black"></i> ' + $.escapeHtml(event.place) + '</span>';
	}

	if (event.amount) {
		htmlBuffer += '<span class="fc-amount semi-bold">' + $.escapeHtml(event.amount) + '</span>';
	}

	if (htmlBuffer) {
		$element.append(htmlBuffer);
	}
}

function calendarEventDragStart(event) {
	event.dragging = true;
}

function calendarEventResizeStart(event) {
	event.resizing = true;
}

function calendarEventResize(event, delta, revertFunc) {
	$.loadingScreen('show');

	var eventIdParts = event.id.split('_'),
		eventId = eventIdParts[0],
		moduleId = eventIdParts[1],
		ajaxData = $.extend({}, $.getData({}), {
			'eventId': eventId,
			'moduleId': moduleId,
			'deltaSeconds': delta.asSeconds()
		});

	$.ajax({
		url: hostcmsBackend + '/calendar/index.php?eventResize',
		type: "POST",
		dataType: 'json',
		data: ajaxData,
		success: function (result){
			$.loadingScreen('hide');

			if (!result['error'] && result['message'])
			{
				Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'success', 'fa-check', true);
				$('#calendar').fullCalendar('refetchEvents');
			}
			else if (result['message'])
			{
				if (result['error'] && typeof revertFunc === 'function') revertFunc();
				Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'danger', 'fa-warning', true);
			}
		}
	});
}

function calendarEventDrop(event, delta, revertFunc) {
	$.loadingScreen('show');

	var eventIdParts = event.id.split('_'),
		eventId = eventIdParts[0],
		moduleId = eventIdParts[1],
		ajaxData = $.extend({}, $.getData({}), {
			'eventId': eventId,
			'moduleId': moduleId,
			'startTimestamp': event.start.format('X'),
			'allDay': +event.allDay
		});

	$.ajax({
		url: hostcmsBackend + '/calendar/index.php?eventDrop',
		type: "POST",
		dataType: 'json',
		data: ajaxData,
		success: function (result){
			$.loadingScreen('hide');

			if (!result['error'] && result['message'])
			{
				Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'success', 'fa-check', true);
			}
			else if (result['message'])
			{
				if (result['error'] && typeof revertFunc === 'function') revertFunc();
				Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'danger', 'fa-warning', true);
			}
			// Обновляем события в любом случае для синхронизации
			$('#calendar').fullCalendar('refetchEvents');
		}
	});
}

function calendarEventDestroy(event, element) {
	element.popover('destroy');
}

// Отмена опции "Весь день"
function cancelAllDay(windowId) {
	var $window = $('#' + windowId),
		$allDayInput = $window.find("input[name='all_day']");

	if ($allDayInput.prop("checked"))
	{
		$allDayInput.prop("checked", false);
		$window.find("select[name='duration_type']").closest("div").removeClass("invisible");

		var formatDateTimePicker = "DD.MM.YYYY HH:mm:ss",
			$start = $window.find('input[name="start"]'),
			$finish = $window.find('input[name="finish"]');

		if ($start.length) $start.parent().data("DateTimePicker").format(formatDateTimePicker);
		if ($finish.length) $finish.parent().data("DateTimePicker").format(formatDateTimePicker);
	}
}

function setDuration(start, end, windowId) {
	var duration = 0,
		// Используем Math.trunc или floor для надежности
		durationInMinutes = (end > start) ? Math.floor((end - start) / 60000) : 0,
		durationType = 0; // Default to minutes

	start = Math.floor(start / 1000) * 1000;
	end = Math.floor(end / 1000) * 1000;

	if (durationInMinutes > 0)
	{
		// Дни
		if ((durationInMinutes / 60) % 24 === 0)
		{
			durationType = 2;
			duration = durationInMinutes / 1440; // 60 * 24
		}
		else if (durationInMinutes % 60 === 0) // Часы
		{
			durationType = 1;
			duration = durationInMinutes / 60;
		}
		else
		{
			durationType = 0;
			duration = durationInMinutes;
		}
		$('#' + windowId + " select[name='duration_type']").val(durationType);
	}

	$('#' + windowId + " input[name='duration']").val(duration);
}

function changeDuration(event) {
	var windowSelector = '#' + event.data.windowId,
		$cell = $(windowSelector + " #" + event.data.cellId),
		startTimeCell = +$cell.attr("start_timestamp") - event.data.timeZoneOffset,
		stopTimeCell = startTimeCell + getDurationMilliseconds(event.data.windowId);

	$(windowSelector + ' input[name="deadline"]').parent().data("DateTimePicker").date(new Date(stopTimeCell));
}

// Получение продолжительности события в миллисекундах
function getDurationMilliseconds(windowId) {
	var $window = $('#' + windowId),
		bAllDay = $window.find("input[name='all_day']").prop("checked"),
		duration = bAllDay ? 1 : +$window.find('input[name="duration"]').val(),
		durationType = bAllDay ? 2 : +$window.find('select[name="duration_type"]').val(),
		durationMillisecondsCoeff = 60000; // минуты в мс

	switch (durationType)
	{
		case 1: // часы
			durationMillisecondsCoeff *= 60;
			break;
		case 2: // дни
			durationMillisecondsCoeff *= 1440; // 60 * 24
			break;
	}

	return duration * durationMillisecondsCoeff - (bAllDay ? 1 : 0); // Исправлена логика с bAllDay
}

function setStartAndDeadline(start, end, windowId) {
	var $window = $('#' + windowId),
		$startInput = $window.find('input[name="start"]'),
		$deadlineInput = $window.find('input[name="deadline"]');

	if ($startInput.length) {
		$startInput.parent().data("DateTimePicker").date(new Date(start));
	}

	if (end && $deadlineInput.length)
	{
		$deadlineInput.parent().data("DateTimePicker").date(new Date(end));
	}

	var jTimeSlider = $window.find("#ts");
	var jStartButtons = $("#eventStartButtonsGroup");
	var jAllDay = $("input[name='all_day']");

	if (!(jStartButtons.data("clickStartButton") || jAllDay.data("clickAllDay")
		|| jTimeSlider.data("moveTimeCell") || jTimeSlider.data("rulerRepeating")))
	{
		setEventStartButtons(start, windowId);
	}
}

// Установка быстрых кнопок начала события
function setEventStartButtons(start, windowId) {
	var oCurrentDate = new Date(),
		// Обнуляем время текущей даты для корректного сравнения
		oTodayStart = new Date(oCurrentDate.getFullYear(), oCurrentDate.getMonth(), oCurrentDate.getDate()).getTime(),
		millisecondsDay = 86400000; // 24 * 3600 * 1000

	var oCurrentStartDate = new Date(start),
		oCurrentStartDateWithoutTime = new Date(oCurrentStartDate.getFullYear(), oCurrentStartDate.getMonth(), oCurrentStartDate.getDate()).getTime();

	// Проверяем диапазон: сегодня (0) до +3 дней (всего 4 дня)
	var maxDate = oTodayStart + (millisecondsDay * 3);

	if (oCurrentStartDateWithoutTime >= oTodayStart && oCurrentStartDateWithoutTime <= maxDate)
	{
		// Вычисляем индекс дня разницей времени, избегая цикла
		var diffDays = Math.round((oCurrentStartDateWithoutTime - oTodayStart) / millisecondsDay);

		if (diffDays >= 0 && diffDays < 4)
		{
			var $buttonsGroup = $('#' + windowId + ' #eventStartButtonsGroup');
			var eventButton = $buttonsGroup.find('a[data-start-day=' + diffDays + ']:not(.active)');

			if (eventButton.length)
			{
				eventButton.eq(0)
					.addClass("active")
					.siblings(".active")
					.removeClass("active");
			}
			return; // Нашли и выделили, выходим
		}
	}

	// Если не попали в диапазон, снимаем выделение
	$('#' + windowId + ' #eventStartButtonsGroup a.active').removeClass("active");
}