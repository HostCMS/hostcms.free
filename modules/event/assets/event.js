/* global hostcmsBackend bootbox Notify */
(function($) {
	"use strict";

	$.extend({
		toggleEventFields: function(object, selector) {
			$(selector).toggleClass('hidden');
			object.parents('.row').eq(0).remove();
		},
		eventsPrepare: function() {
			$.refreshEventsList();

			var jEventsListBox = $('.navbar-account #notificationsClockListBox');

			jEventsListBox.on({
				'click': function(event) {
					event.stopPropagation();
				},
				'touchstart': function() {
					$(this).data({
						'isTouchStart': true
					});
				}
			});

			$('.navbar li#notifications-clock').on('shown.bs.dropdown', function() {
				$.setEventsSlimScroll();
			});
		},
		refreshEventsCallback: function(resultData) {
			if (typeof resultData['newEvents'] != 'undefined' && resultData['newEvents'].length) {
				var jEventUl = $('.navbar-account #notificationsClockListBox .scroll-notifications-clock > ul');

				var $addBtn = $('li[id="event-0"]', jEventUl).detach();
				jEventUl.empty();
				$addBtn.hide();

				var docFragment = document.createDocumentFragment();

				$.each(resultData['newEvents'], function(index, event) {
					docFragment.appendChild($.createEventElement(event));
				});

				jEventUl.append(docFragment);
				jEventUl.append($addBtn);
			}
		},
		eventsWidgetPrepare: function() {
			var sSlimscrollBarWidth = '5px';

			$('#eventsAdminPage')
				.on({
					'click': function() {
						$('#eventsAdminPage .tasks-list-container').css({'max-height': 'none'});
						$('#eventsAdminPage .tasks-list').slimscroll({destroy: true});
						$('#eventsAdminPage .tasks-list').slimscroll({
							height: $('#eventsAdminPage .widget-body').height(),
							color: 'rgba(0,0,0,0.3)',
							size: '5px'
						});
					}
				}, '[data-toggle = "maximize"] i.fa-expand')
				.on({
					'click': function() {
						$('#eventsAdminPage .tasks-list-container').css({'max-height': '500px'});
						$('#eventsAdminPage .tasks-list').slimscroll({destroy: true});
						$('#eventsAdminPage .tasks-list').slimscroll({
							height: 'auto',
							color: 'rgba(0,0,0,0.3)',
							size: '5px'
						});
					}
				}, '[data-toggle = "maximize"] i.fa-compress')
				.on({
					'mouseenter': function() { $(this).css('width', (parseInt(sSlimscrollBarWidth) + 3) + 'px') },
					'mouseleave': function() { $(this).css('width', sSlimscrollBarWidth) }
				}, '.slimScrollBar')
				.on({
					'keyup': function(event) {
						var jInputSearch = $(this),
							jEvents = jInputSearch.parents('.task-container').find('.tasks-list .task-item');

						if (event.keyCode == 27) jInputSearch.val('');

						if (jEvents.length) {
							var searchString = jInputSearch.val().toLocaleLowerCase();
							jEvents.each(function() {
								var sourceText = $(this).find('.task-body').text().toLocaleLowerCase();
								$(this).toggle(sourceText.indexOf(searchString) !== -1);
							});
						}
					}
				}, '.search-event input')
				// ... остальные обработчики
				.on({
					'click': function() {
						var jEventItem = $(this).find('i').toggleClass('fa-square fa-square-check').parents('.task-item');
						jEventItem.css({'width': '100%'}).animate({'margin-left': '-100%'}, {
							duration: 700,
							complete: function() {
								$(this).addClass('mark-completed');
								var ajaxData = $.getData({});
								ajaxData['eventId'] = jEventItem.prop('id').split('event-')[1];

								$.ajax({
									url: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + $('#eventsAdminPage').data('moduleId') + '&type=1',
									type: 'POST',
									data: ajaxData,
									dataType: 'json',
									success: function(resultData) {
										if (resultData['eventId']) {
											$('#eventsAdminPage .task-item[id = "event-' + resultData['eventId'] + '"]').remove();
											$('#eventsAdminPage [data-toggle="upload"]').click();
										}
									}
								});
							}
						});
					}
				}, '.task-check')
				.on({
					'click': function(event) {
						var jEventsAdminPage = $(this).parents('#eventsAdminPage'),
							jEventsList = jEventsAdminPage.find('.tasks-list');

						if (!event.isTrigger) jEventsAdminPage.data('slimScrollBarTop', '0px');
						else jEventsAdminPage.data('slimScrollBarTop', jEventsList.scrollTop() + 'px');

						$(this).find('i').addClass('fa-spin');
						$.widgetLoad({
							path: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + $(this).data('moduleId') + '&type=0',
							context: jEventsAdminPage
						});
					}
				}, '[data-toggle = "upload"]')
				.on({
					'click': function(event) {
						$(this).children('i.fa-plus').toggleClass('hidden');
						$(this).children('i.fa-magnifying-glass').toggleClass('hidden');
						$('#eventsAdminPage .task-search .search-event').toggleClass('hidden');
						$('#eventsAdminPage .task-search .add-event').toggleClass('hidden').find('input').focus();
						event.preventDefault();
					}
				}, '[data-toggle = "toggle-actions"]')
				.on({
					'submit': function(event) {
						event.preventDefault();
						var eventName = $.trim($(this).find('input[name="event_name"]').val());
						if (!eventName.length) return;

						$('#sendForm i').toggleClass('fa-spinner fa-spin fa-check');
						var ajaxData = $.getData({}),
							formData = $(this).serializeArray();

						$.each(formData, function() { ajaxData[this.name] = $.trim(this.value); });

						$.ajax({
							url: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + $('#eventsAdminPage').data('moduleId') + '&type=3',
							type: 'POST',
							data: ajaxData,
							dataType: 'json',
							success: function() {
								$.widgetLoad({
									path: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + $('#eventsAdminPage').data('moduleId') + '&type=0',
									context: $('#eventsAdminPage')
								});
							}
						});
					}
				}, '.add-event form');
		},
		eventsWidgetChangeStatus: function(dropdownMenu) {
			var ajaxData = $.getData({}),
				jEventItem = $(dropdownMenu).parents('.task-item'),
				jEventStatus = $('[selected="selected"]', dropdownMenu);

			ajaxData['eventId'] = jEventItem.prop('id');
			ajaxData['eventStatusId'] = jEventStatus.prop('id');

			$.ajax({
				url: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + $('#eventsAdminPage').data('moduleId') + '&type=2',
				type: 'POST',
				data: ajaxData,
				dataType: 'json',
				success: function(resultData) {
					if (+resultData['finalStatus']) {
						jEventStatus.parents('li.task-item').children('.task-check').click();
					}
				}
			});
		},

		createEventElement: function(oEvent) {
			var li = document.createElement('li');
			li.id = 'event-' + oEvent['id'];

			var href = oEvent['href'].length ? $.escapeHtml(oEvent['href']) : '#';
			var onclick = oEvent['onclick'].length ? oEvent['onclick'] : '';

			li.innerHTML = '<a href="' + href + '" onclick="' + onclick.replace(/"/g, '&quot;') + '">' +
				'<div class="clearfix notification-clock">' +
				'<div class="notification-icon">' +
				'<i class="' + $.escapeHtml(oEvent['icon']) + ' fa-fw white" style="background-color: ' + $.escapeHtml(oEvent['background-color']) + '"></i>' +
				'</div>' +
				'<div class="notification-body">' +
				'<span class="title">' + $.escapeHtml(oEvent['name']) + '</span>' +
				'<span class="description"><i class="fa-regular fa-clock"></i> ' + $.escapeHtml(oEvent['start']) + ' — <span class="notification-time">' + $.escapeHtml(oEvent['finish']) + '</span>' +
				'</div>' +
				'</div>' +
				'</a>';
			return li;
		},

		refreshEventsList: function() {
			var jNotificationsClockListBox = $('.navbar-account #notificationsClockListBox');
			if (!jNotificationsClockListBox.length) return;

			var data = $.getData({});
			data['currentUserId'] = jNotificationsClockListBox.data('currentUserId');

			var bLocalStorage = $.storageAvailable('localStorage');
			var bNeedsRequest = false;

			if (bLocalStorage) {
				try {
					var storage = localStorage.getItem('events'),
						storageObj = storage ? JSON.parse(storage) : {
							expired_in: 0
						};

					if (Date.now() > storageObj['expired_in']) {
						bNeedsRequest = true;
					} else {
						$.refreshEventsCallback(storageObj);
					}
				} catch (e) {
					bNeedsRequest = true;
				}
			} else {
				bNeedsRequest = true;
			}

			var scheduleNext = function() {
				setTimeout($.refreshEventsList, 10000);
			};

			if (bNeedsRequest) {
				$.ajax({
					url: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + jNotificationsClockListBox.data('moduleId') + '&type=4',
					type: 'POST',
					data: data,
					dataType: 'json',
					error: function() {
						scheduleNext();
					},
					success: function(resultData) {
						if (bLocalStorage) {
							resultData['expired_in'] = Date.now() + 10000;
							try {
								localStorage.setItem('events', JSON.stringify(resultData));
							} catch (e) {
								console.log('localStorage error: ' + e);
							}
						}
						$.refreshEventsCallback(resultData);
						scheduleNext();
					}
				});
			} else {
				scheduleNext();
			}
		},

		setEventsSlimScroll: function() {
			var jSlimScrollBar = $('#notificationsClockListBox .slimScrollBar'),
				slimScrollBarData = !jSlimScrollBar.data() ? {
					'isMousedown': false
				} : jSlimScrollBar.data(),
				jScrollNotificationClock = $('#notificationsClockListBox .scroll-notifications-clock');

			if ($('#notificationsClockListBox > .slimScrollDiv').length) {
				jScrollNotificationClock.slimscroll({
					destroy: true
				});
				jScrollNotificationClock.attr('style', '');
			}

			jScrollNotificationClock.slimscroll({
				height: $('.navbar-account #notificationsClockListBox .scroll-notifications-clock > ul li[id != "notification-0"]').length ? '220px' : '55px',
				color: 'rgba(0, 0, 0, 0.3)',
				size: '5px',
				wheelStep: 5
			});

			$('#notificationsClockListBox .slimScrollBar')
				.data(slimScrollBarData)
				.on({
					'mousedown': function() {
						$(this).data('isMousedown', true);
					},
					'mouseenter': function() {
						$(this).css('width', '8px');
					},
					'mouseout': function() {
						!$(this).data('isMousedown') && $(this).css('width', '5px');
					}
				});
		},

		addEvent: function(oEvent, jBox) {
			jBox.append($.createEventElement(oEvent));
			if ($('.navbar li#notifications-clock').hasClass('open')) {
				!$('li', jBox).length && $.setEventsSlimScroll();
			}
		},
	});
})(jQuery);
