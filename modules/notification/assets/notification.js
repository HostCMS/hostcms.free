/* global hostcmsBackend bootbox Notify */
(function($) {
	"use strict";

	$.extend({
		notificationsPrepare: function() {
			$.refreshNotificationsList();

			var jNotificationsListBox = $('.navbar-account #notificationsListBox');

			jNotificationsListBox.on({
				'click': function(event) {
					event.stopPropagation();
				},
				'touchstart': function() {
					$(this).data({
						'isTouchStart': true
					});
				}
			});

			$('.navbar li#notifications').on('shown.bs.dropdown', function() {
				$.setNotificationsSlimScroll();
				$.readNotifications();

				var jInputSearch = $('#notification-search', this),
					jButton = jInputSearch.nextAll('.notification-clear-search');

				if (jInputSearch.val() == '') {
					jButton.addClass('hide');
				} else {
					jButton.removeClass('hide');
				}

				if ($('#notificationsListBox .scroll-notifications li[id != "notification-0"]').length) {
					$('.navbar-account #notificationsListBox .footer').show();
				} else {
					$('.navbar-account #notificationsListBox .footer').hide();
				}
			});

			jNotificationsListBox.find('.footer .notification-delete').on('click', $.clearNotifications);

			$(document).on({
				'mouseup': function() {
					var jSlimScrollBar = $('#notificationsListBox .slimScrollBar');
					if (jSlimScrollBar.data('isMousedown')) {
						$.readNotifications();
						jSlimScrollBar.data({
							'isMousedown': false
						});
					}
				},
				'touchend': function() {
					var box = $('.navbar-account #notificationsListBox');
					if (box.data('isTouchStart')) {
						box.data('isTouchStart', false);
					}
				},
				'touchmove': function() {
					if ($('.navbar-account #notificationsListBox').data('isTouchStart')) {
						// Можно добавить троттлинг здесь, если скролл тормозит
						$.readNotifications();
					}
				}
			});

			var jNotificationsList = $('.navbar-account #notificationsListBox .scroll-notifications');

			function onWheel(event) {
				var jNotificationsList = $('#notificationsListBox .scroll-notifications'),
					slimScrollBar = $('#notificationsListBox .slimScrollBar'),
					maxTop = jNotificationsList.outerHeight() - slimScrollBar.outerHeight(),
					wheelDelta = 0,
					newTopScroll = 0,
					percentScroll;

				if (event.wheelDelta) wheelDelta = -event.wheelDelta / 120;
				if (event.detail) wheelDelta = event.detail / 3;

				var wheelStep = 20;

				wheelDelta = parseInt(slimScrollBar.css('top')) + wheelDelta * wheelStep / 100 * slimScrollBar.outerHeight();
				wheelDelta = Math.min(Math.max(wheelDelta, 0), maxTop);
				wheelDelta = Math.ceil(wheelDelta);

				percentScroll = wheelDelta / (jNotificationsList.outerHeight() - slimScrollBar.outerHeight());
				newTopScroll = percentScroll * (jNotificationsList[0].scrollHeight - jNotificationsList.outerHeight());

				var actualScrollDelta = newTopScroll - jNotificationsList.scrollTop();
				$.readNotifications(actualScrollDelta);
			}

			if (jNotificationsList[0].addEventListener) {
				jNotificationsList[0].addEventListener('DOMMouseScroll', onWheel, false);
				jNotificationsList[0].addEventListener('mousewheel', onWheel, false);
				jNotificationsList[0].addEventListener('MozMousePixelScroll', onWheel, false);
			} else {
				jNotificationsList[0].attachEvent("onmousewheel", onWheel);
			}

			// Обработчики поиска
			$('.navbar-account #notificationsListBox #notification-search').on('keyup', function(event) {
				var jInputSearch = $(this);
				if (event.keyCode == 27) jInputSearch.val('');

				var clearBtn = $('.navbar-account #notificationsListBox .footer .notification-delete');
				jInputSearch.val() ? clearBtn.hide() : clearBtn.show();

				var removeIcon = jInputSearch.nextAll('.notification-clear-search');
				jInputSearch.val() == '' ? removeIcon.addClass('hide') : removeIcon.removeClass('hide');

				$.filterNotifications(jInputSearch);
			});

			$('.navbar-account #notificationsListBox .notification-clear-search').on('click', function() {
				var input = $(this).prevAll('#notification-search');
				input.val('');
				$.filterNotifications(input);
				$(this).addClass('hide');
				$('.navbar-account #notificationsListBox .footer .notification-delete').show();
			});
		},

		setNotificationsSlimScroll: function() {
			var jSlimScrollBar = $('#notificationsListBox .slimScrollBar'),
				slimScrollBarData = !jSlimScrollBar.data() ? {
					'isMousedown': false
				} : jSlimScrollBar.data();

			if ($('#notificationsListBox > .slimScrollDiv').length) {
				$('#notificationsListBox .scroll-notifications').slimscroll({
					destroy: true
				});
				$('#notificationsListBox .scroll-notifications').attr('style', '');
			}

			$('#notificationsListBox .scroll-notifications').slimscroll({
				height: $('.navbar-account #notificationsListBox .scroll-notifications > ul li[id != "notification-0"]').length ? '220px' : '55px',
				color: 'rgba(0, 0, 0, 0.3)',
				size: '5px'
			});

			$('#notificationsListBox .slimScrollBar')
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

		elementInBox: function(element, box, wheelDelta, delta) {
			// Оставлено для совместимости, но оптимизированная логика встроена в readNotifications
			delta = delta || 10;
			wheelDelta = wheelDelta || 0;

			var boxTop = box.offset().top + parseInt(box.css('margin-top') || 0) + parseInt(box.css('padding-top') || 0),
				boxBottom = boxTop + box.height(),
				elementTop = element.offset().top + parseInt(element.css('margin-top') || 0) + parseInt(element.css('padding-top') || 0) - wheelDelta,
				elementBottom = elementTop + element.height();

			return elementTop >= boxTop && elementTop <= (boxBottom - delta) || (elementBottom >= boxTop + delta) && elementBottom <= boxBottom;
		},

		addNotification: function(oNotification, jBox) {
			if (!oNotification['show']) {
				$('.toast').remove();
				return false;
			}

			jBox = jBox || $('.navbar-account #notificationsListBox .scroll-notifications > ul');

			var notificationExtra = '',
				bUnread = oNotification['read'] == 0,
				storageNotifications = $.localStorageGetItem('notifications') || {},
				lastWindowNotificationId = window.lastWindowNotificationId ? window.lastWindowNotificationId : 0,
				lastStoredNotificationId = storageNotifications['lastAddedNotificationId'] ? storageNotifications['lastAddedNotificationId'] : 0;

			if (oNotification['extra'].length) {
				var jNotificationExtra = $('<div class="notification-extra">');
				oNotification['extra'].forEach(function(item) {
					jNotificationExtra.append('<i class="' + $.escapeHtml(item) + ' themeprimary"></i>');
				});
				oNotification['extra']['description'].length && jNotificationExtra.append('<span class="description">' + $.escapeHtml(oNotification['extra']['description']) + '</span>')
				notificationExtra = jNotificationExtra.html();
			}

			var li = document.createElement('li');
			li.id = 'notification-' + oNotification['id'];
			li.className = bUnread ? 'unread' : '';

			var href = oNotification['href'].length ? $.escapeHtml(oNotification['href']) : '#';
			var onclick = oNotification['onclick'].length ? oNotification['onclick'] : '';

			li.innerHTML = '<a href="' + href + '" onclick="' + onclick.replace(/"/g, '&quot;') + '">' +
				'<div class="clearfix">' +
				'<div class="notification-icon">' +
				'<i class="' + $.escapeHtml(oNotification['icon']['ico']) + ' ' + $.escapeHtml(oNotification['icon']['background-color']) + ' ' + $.escapeHtml(oNotification['icon']['color']) + '"></i>' +
				'</div>' +
				'<div class="notification-body">' +
				'<span class="title">' + $.escapeHtml(oNotification['title']) + '</span>' +
				'<span class="description">' + (oNotification['description'].length ? ($.escapeHtml(oNotification['description']) + '<br/>') : '') + '</span>' +
				'<span class="site-name">' + (typeof oNotification['site'] !== 'undefined' && oNotification['site'] !== null ? $.escapeHtml(oNotification['site']) : '') + '</span>' +
				'</div>' +
				notificationExtra +
				'</div>' +
				'</a>';

			jBox.prepend(li);

			if (bUnread && oNotification['id'] > lastWindowNotificationId) {
				var bSound = oNotification['id'] > lastStoredNotificationId;

				if (oNotification['ajaxUrl'] != null && oNotification['ajaxUrl'].length) {
					$.ajax({
						url: oNotification['ajaxUrl'],
						type: "POST",
						dataType: 'json',
						success: function(result) {
							oNotification['description'] += result.html;
							Notify($.escapeHtml(oNotification['title']), oNotification['description'], 'bottom-left', oNotification['timeout'], oNotification['notification']['background-color'], 'fa-solid ' + oNotification['notification']['ico'], true, bSound);
						}
					});
				} else {
					Notify($.escapeHtml(oNotification['title']), $.escapeHtml(oNotification['description']), 'bottom-left', oNotification['timeout'], oNotification['notification']['background-color'], 'fa-solid ' + oNotification['notification']['ico'], true, bSound);
				}

				storageNotifications = $.localStorageGetItem('notifications') || {};
				window.lastWindowNotificationId = storageNotifications['lastAddedNotificationId'] = oNotification['id'];
				$.localStorageSetItem('notifications', storageNotifications);
			}

			if ($('.navbar li#notifications').hasClass('open')) {
				!$('.navbar-account #notificationsListBox .scroll-notifications > ul li').length && $.setNotificationsSlimScroll();
				$.readNotifications();
			}
		},

		recountUnreadNotifications: function() {
			var countUnreadNotifications = $('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread').length;

			$('.navbar li#notifications > a').toggleClass('wave in', !!countUnreadNotifications);

			$('.navbar li#notifications > a > span.badge')
				.html(countUnreadNotifications > 99 ? '∞' : countUnreadNotifications)
				.toggleClass('hidden', !countUnreadNotifications);
		},

		refreshNotificationsCallback: function(resultData) {
			var jNotificationsListBox = $('.navbar-account #notificationsListBox'),
				iLastNotificationId = 0;

			if (resultData['userId'] && resultData['userId'] == jNotificationsListBox.data('currentUserId')) {
				var unreadNotifications = [];

				$('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread').each(function() {
					unreadNotifications.push($(this).attr('id'));
				});

				$.each(resultData['unreadNotifications'], function(index, notification) {
					var searchIndex = unreadNotifications.indexOf('notification-' + notification['id']);
					if (searchIndex !== -1) {
						unreadNotifications.splice(searchIndex, 1);
					}
				});

				$.each(unreadNotifications, function(index, value) {
					$('.navbar-account #notificationsListBox .scroll-notifications > ul li#' + value + '.unread').removeClass('unread');
				});

				if (resultData['newNotifications'].length) {
					$('.navbar-account #notificationsListBox .scroll-notifications > ul li[id="notification-0"]').hide();

					// Так как addNotification имеет побочные эффекты (Notify, работа с localStorage),
					// оставляем цикл, но HTML вставка оптимизирована внутри addNotification (prepend).
					// Для полной оптимизации нужно переписывать addNotification, чтобы он возвращал элемент.
					$.each(resultData['newNotifications'], function(index, notification) {
						$.addNotification(notification, $('.navbar-account #notificationsListBox .scroll-notifications > ul'));

						if (iLastNotificationId < notification['id']) {
							iLastNotificationId = notification['id'];
						}
					});

					jNotificationsListBox.data('lastNotificationId', iLastNotificationId);

					if ($('.navbar li#notifications').hasClass('open') &&
						!$('.navbar-account #notificationsListBox .scroll-notifications > ul li').length) {
						$.setNotificationsSlimScroll();
					}

					jNotificationsListBox.find('.footer .notification-delete').show();
					jNotificationsListBox.find('.footer #notification-search').show();
					jNotificationsListBox.find('.footer .fa-magnifying-glass').show();
				}

				$.recountUnreadNotifications();

				$('.workday-timer').html(resultData['workdayDuration']);

				var aStatuses = ['ready', 'denied', 'working', 'break', 'completed', 'expired'],
					status = $('li.workday #workdayControl').data('status');

				$('li.workday #workdayControl')
					.toggleClass(aStatuses[status] + ' ' + aStatuses[resultData['workdayStatus']])
					.data('status', resultData['workdayStatus']);

				if (resultData['workdayStatus'] == 5) {
					$('#user-info-dropdown .login-area').addClass('wave in');
				} else {
					$('#user-info-dropdown .login-area').removeClass('wave in');
				}

				$.blinkColon(resultData['workdayStatus']);
			}
		},

		refreshNotificationsList: function() {
			var jNotificationsListBox = $('.navbar-account #notificationsListBox');
			if (!jNotificationsListBox.length) return;

			var data = $.getData({}),
				lastNotificationId = jNotificationsListBox.data('lastNotificationId') ? +jNotificationsListBox.data('lastNotificationId') : 0,
				storageNotifications = $.localStorageGetItem('notifications'),
				bNeedsRequest = false;

			if (storageNotifications !== null) {
				if (!storageNotifications || typeof storageNotifications['expired_in'] == 'undefined') {
					storageNotifications = {
						expired_in: 0,
						lastNotificationId: 0
					};
				}

				if (Date.now() > storageNotifications['expired_in']) {
					bNeedsRequest = true;
				} else if (lastNotificationId < storageNotifications['lastNotificationId'] ||
					(storageNotifications['unreadNotifications'] && storageNotifications['unreadNotifications'].length)) {
					$.refreshNotificationsCallback(storageNotifications);
				}

				var storageNotificationRead = $.localStorageGetItem('notificationRead');
				if (storageNotificationRead && typeof storageNotificationRead['IDs'] !== 'undefined') {
					$.each(storageNotificationRead['IDs'], function(index, value) {
						$('.navbar-account #notificationsListBox .scroll-notifications > ul li#notification-' + value + '.unread').removeClass('unread');
					});

					if (Date.now() > storageNotificationRead['expire']) {
						$.localStorageSetItem('notificationRead', {IDs: [], expire: 0}); // Исправлено здесь
					}
				}
			} else {
				bNeedsRequest = true;
			}

			var scheduleNext = function() {
				setTimeout($.refreshNotificationsList, 5000);
			};

			if (bNeedsRequest) {
				var ts = Date.now() + 10000;
				if (storageNotifications !== null) {
					storageNotifications['expired_in'] = ts;
					$.localStorageSetItem('notifications', storageNotifications);
				}

				data['lastNotificationId'] = lastNotificationId;
				data['currentUserId'] = jNotificationsListBox.data('currentUserId');

				$.ajax({
					url: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + jNotificationsListBox.data('moduleId') + '&type=0',
					type: 'POST',
					data: data,
					dataType: 'json',
					error: function() {
						scheduleNext();
					},
					success: function(resultData) {
						if (storageNotifications !== null) {
							resultData['expired_in'] = Date.now() + 10000; // Use current time
							resultData['lastAddedNotificationId'] = storageNotifications['lastAddedNotificationId'] ? storageNotifications['lastAddedNotificationId'] : 0;
						}
						$.localStorageSetItem('notifications', resultData);
						$.refreshNotificationsCallback(resultData);
						scheduleNext();
					}
				});
			} else {
				scheduleNext();
			}
		},

		readNotifications: function(wheelDelta, delta) {
			var masVisibleUnreadNotifications = [];
			var $box = $('.navbar-account div#notificationsListBox .slimScrollDiv');

			if (!$box.length) return;

			// ОПТИМИЗАЦИЯ: Вычисление размеров контейнера один раз
			var boxTop = $box.offset().top + parseInt($box.css('margin-top') || 0) + parseInt($box.css('padding-top') || 0);
			var boxHeight = $box.height();
			var boxBottom = boxTop + boxHeight;

			wheelDelta = wheelDelta || 0;
			delta = delta || 10;

			$('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread').each(function() {
				var $this = $(this);
				var elementTop = $this.offset().top + parseInt($this.css('margin-top') || 0) + parseInt($this.css('padding-top') || 0) - wheelDelta;
				var elementBottom = elementTop + $this.height();

				// Проверка вхождения
				var inBox = (elementTop >= boxTop && elementTop <= (boxBottom - delta)) ||
					(elementBottom >= boxTop + delta && elementBottom <= boxBottom);

				if (inBox) {
					$this.removeClass('unread');
					var idStr = $this.attr('id');
					if (idStr) masVisibleUnreadNotifications.push(idStr.split('notification-')[1]);
				}
			});

			$.recountUnreadNotifications();

			if (masVisibleUnreadNotifications.length) {
				var storageNotificationRead = $.localStorageGetItem('notificationRead') || {
					IDs: [],  // Инициализируем пустым массивом по умолчанию
					expire: 0
				};

				// Дополнительная проверка на случай, если IDs все еще undefined
				if (!storageNotificationRead.IDs) {
					storageNotificationRead.IDs = [];
				}

				storageNotificationRead['IDs'] = storageNotificationRead['IDs'].concat(masVisibleUnreadNotifications);
				storageNotificationRead['expire'] = Date.now() + 60000;

				$.localStorageSetItem('notificationRead', storageNotificationRead);

				var data = $.getData({});
				data['notificationsListId'] = masVisibleUnreadNotifications;
				data['currentUserId'] = $('.navbar-account #notificationsListBox').data('currentUserId');

				$.ajax({
					url: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + $('.navbar-account #notificationsListBox').data('moduleId') + '&type=1',
					type: 'POST',
					data: data,
					dataType: 'json'
				});
			}
		},
		filterNotifications: function(jInputElement) {
			var jNotifications = $('#notificationsListBox .scroll-notifications li[id != "notification-0"]');

			if (jNotifications.length) {
				var searchString = jInputElement.val().toLocaleLowerCase();

				if (searchString.length) {
					jNotifications.each(function() {
						var sourceText = $(this).text().toLocaleLowerCase();
						$(this).toggle(sourceText.indexOf(searchString) !== -1);
					});
				} else {
					jNotifications.show();
				}
			}
		},
		clearNotifications: function() {
			$.ajax({
				url: hostcmsBackend + '/user/index.php',
				type: 'POST',
				data: {
					'setNotificationsRead': 1
				},
				dataType: 'json'
			});

			$('.navbar-account #notificationsListBox .scroll-notifications > ul li[id != "notification-0"]').remove();
			$('.navbar-account #notificationsListBox .scroll-notifications > ul li[id = "notification-0"]').show();

			$('.navbar li#notifications > a').removeClass('wave in');

			$('.navbar li#notifications > a > span.badge')
				.html(0)
				.toggleClass('hidden', true);

			$('.navbar-account #notificationsListBox .footer .notification-delete').hide();
			$('.navbar-account #notificationsListBox .footer #notification-search').hide();
			$('.navbar-account #notificationsListBox .footer .fa-magnifying-glass').hide();

			$.removeLocalStorageItem('notifications');
			$.removeLocalStorageItem('notificationRead');
		}
	});
})(jQuery);

