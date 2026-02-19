/* global hostcmsBackend bootbox Notify */
(function($) {
	"use strict";

	$.extend({
		bookmarksPrepare: function() {
			// ОПТИМИЗАЦИЯ: Замена setInterval на рекурсивный вызов через setTimeout
			$.refreshBookmarksList();

			var jBookmarksListBox = $('.navbar-account #bookmarksListBox');

			jBookmarksListBox.on({
				'click': function(event) {
					event.stopPropagation();
				},
				'touchstart': function() {
					$(this).data({
						'isTouchStart': true
					});
				}
			});

			// Показ списка закладок
			$('.navbar li#bookmarks').on('shown.bs.dropdown', function() {
				$.setBookmarksSlimScroll();

				$('.scroll-bookmarks .bookmarks-list').sortable({
					connectWith: '.bookmarks-list',
					items: '.bookmark-item',
					scroll: false,
					placeholder: 'placeholder',
					tolerance: 'pointer',
					start: function(evt, ui) {
						var link = ui.item.find('a');
						link.data('click-event', link.attr('onclick'));
						link.attr('onclick', '');
					},
					stop: function(evt, ui) {
						setTimeout(function() {
							var link = ui.item.find('a');
							link.attr('onclick', link.data('click-event'));
						}, 200);

						setTimeout(function() {
							var aIds = $('.bookmarks-list li.bookmark-item').map(function() {
								return $(this).attr('id').split('-')[1];
							}).get();

							$.ajax({
								url: hostcmsBackend + '/user/index.php',
								type: 'POST',
								data: {
									'sortableBookmarks': 1,
									'bookmarks': aIds
								},
								dataType: 'json',
								error: function() {},
								success: function(result) {
									if (result.status == 'success') {
										$.removeLocalStorageItem('bookmarks');
										// Принудительно обновляем список (сброс таймера внутри)
										$.refreshBookmarksList(true);
									}
								}
							});
						}, 500);
					}
				}).disableSelection();
			});
		},

		refreshBookmarksCallback: function(resultData) {
			if (typeof resultData['Bookmarks'] != 'undefined') {
				var jEventUl = $('.navbar-account #bookmarksListBox .scroll-bookmarks > ul');

				// ОПТИМИЗАЦИЯ DOM: Временно убираем кнопку добавления, чистим список,
				// используем фрагмент для вставки новых элементов.
				var $addBtn = $('li[id="bookmark-0"]', jEventUl).detach();
				jEventUl.empty();

				if (resultData['Bookmarks'].length) {
					$addBtn.hide();
					var docFragment = document.createDocumentFragment();

					$.each(resultData['Bookmarks'], function(index, event) {
						docFragment.appendChild($.createBookmarkElement(event));
					});

					jEventUl.append(docFragment);
					jEventUl.append($addBtn);
				} else {
					$addBtn.show();
					jEventUl.append($addBtn);
				}
			}
		},

		createBookmarkElement: function(oBookmark) {
			var li = document.createElement('li');
			li.id = 'bookmark-' + oBookmark['id'];
			li.className = 'bookmark-item';

			var href = oBookmark['href'].length ? $.escapeHtml(oBookmark['href']) : '#';
			var onclick = oBookmark['onclick'].length ? $.escapeHtml(oBookmark['onclick']) : '';

			li.innerHTML = '<a href="' + href + '" onclick="' + onclick + '">' +
				'<div class="clearfix notification-bookmark">' +
				'<div class="notification-icon">' +
				'<i class="' + $.escapeHtml(oBookmark['ico']) + ' bg-darkorange white"></i>' +
				'</div>' +
				'<div class="notification-body">' +
				'<span class="title">' + $.escapeHtml(oBookmark['name']) + '</span>' +
				'<span class="description">' + $.escapeHtml(oBookmark['href']) + '</span>' +
				'</div>' +
				'<div class="notification-extra">' +
				'<i class="fa fa-times gray bookmark-delete"></i>' +
				'</div>' +
				'</div>' +
				'</a>';

			// Навешиваем обработчик удаления через jQuery на созданный элемент
			$(li).find('.bookmark-delete').on('click', function(e) {
				e.stopPropagation();
				e.preventDefault();
				$.removeUserBookmark({
					title: $.escapeHtml(oBookmark['remove-title']),
					submit: $.escapeHtml(oBookmark['remove-submit']),
					cancel: $.escapeHtml(oBookmark['remove-cancel']),
					bookmark_id: oBookmark['id']
				});
			});

			return li;
		},

		refreshBookmarksList: function(force) {
			var jBookmarksListBox = $('.navbar-account #bookmarksListBox');
			if (!jBookmarksListBox.length) return;

			// Если вызвано принудительно, сбрасываем текущий таймер ожидания
			if (force && jBookmarksListBox.data('timerId')) {
				clearTimeout(jBookmarksListBox.data('timerId'));
			}

			var data = $.getData({});
			var bLocalStorage = $.storageAvailable('localStorage');
			var bNeedsRequest = false;

			if (bLocalStorage) {
				try {
					var storage = localStorage.getItem('bookmarks'),
						storageObj = storage ? JSON.parse(storage) : {
							userId: 0,
							expired_in: 0
						};

					if (jBookmarksListBox.data('userId') != storageObj['userId'] || Date.now() > storageObj['expired_in']) {
						bNeedsRequest = true;
					} else {
						$.refreshBookmarksCallback(storageObj);
					}
				} catch (e) {
					bNeedsRequest = true;
				}
			} else {
				bNeedsRequest = true;
			}

			var scheduleNext = function() {
				var timerId = setTimeout($.refreshBookmarksList, 120000);
				jBookmarksListBox.data('timerId', timerId);
			};

			if (bNeedsRequest) {
				$.ajax({
					url: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + jBookmarksListBox.data('moduleId') + '&type=85',
					type: 'POST',
					data: data,
					dataType: 'json',
					error: function() {
						scheduleNext();
					},
					success: function(resultData) {
						if (bLocalStorage) {
							resultData['expired_in'] = Date.now() + 120000;
							try {
								localStorage.setItem('bookmarks', JSON.stringify(resultData));
							} catch (e) {
								console.log('localStorage error: ' + e);
							}
						}
						$.refreshBookmarksCallback(resultData);
						scheduleNext();
					}
				});
			} else {
				scheduleNext();
			}
		},

		setBookmarksSlimScroll: function() {
			var jSlimScrollBar = $('#bookmarksListBox .slimScrollBar'),
				slimScrollBarData = !jSlimScrollBar.data() ? {
					'isMousedown': false
				} : jSlimScrollBar.data(),
				jScrollBookmarks = $('#bookmarksListBox .scroll-bookmarks');

			if ($('#bookmarksListBox > .slimScrollDiv').length) {
				jScrollBookmarks.slimscroll({
					destroy: true
				});
				jScrollBookmarks.attr('style', '');
			}

			jScrollBookmarks.slimscroll({
				height: $('.navbar-account #bookmarksListBox .scroll-bookmarks > ul li[id != "bookmark-0"]').length ? ($(window).height() * 0.7) : '55px',
				color: 'rgba(0, 0, 0, 0.3)',
				size: '5px',
				wheelStep: 5
			});

			$('#bookmarksListBox .slimScrollBar')
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

		addBookmark: function(oBookmark, jBox) {
			// Legacy support if called directly
			jBox.append($.createBookmarkElement(oBookmark));
			if ($('.navbar li#notification-bookmark').hasClass('open')) {
				!$('li', jBox).length && $.setBookmarksSlimScroll();
			}
		},

		addUserBookmark: function(settings) {
			bootbox.prompt({
				title: settings.title,
				value: settings.value,
				className: 'add-bookmark-form',
				buttons: {
					confirm: {
						label: settings.submit,
						className: 'btn-palegreen add-bookmark-btn'
					},
					cancel: {
						label: settings.cancel,
						className: 'btn-default'
					}
				},
				callback: function(name) {
					if (name) {
						$.ajax({
							url: hostcmsBackend + '/user/index.php',
							type: "POST",
							data: {
								'add_bookmark': 1,
								'name': name,
								'path': settings.path,
								'module_id': settings.module_id
							},
							dataType: 'json',
							error: function() {},
							success: function(result) {
								if (result.length) {
									$.removeLocalStorageItem('bookmarks');
									$.refreshBookmarksList(true); // Force refresh

									$('li#bookmarks > a').addClass('wave in');
									$('a#bookmark-toggler').addClass('active');

									setTimeout(function() {
										$('li#bookmarks > a').removeClass('wave in');
									}, 5000);
								}
							}
						});
					}
				}
			});

			$('.add-bookmark-form form').on('keypress', function(e) {
				if (e.which == 13) {
					$('.add-bookmark-btn').trigger('click');
				}
			});
		},

		removeUserBookmark: function(settings) {
			bootbox.confirm({
				message: settings.title,
				className: 'delete-bookmark-form',
				buttons: {
					confirm: {
						label: settings.submit,
						className: 'btn-darkorange delete-bookmark-btn'
					},
					cancel: {
						label: settings.cancel,
						className: 'btn-default'
					}
				},
				callback: function(result) {
					if (result) {
						$.ajax({
							url: hostcmsBackend + '/user/index.php',
							type: "POST",
							data: {
								'remove_bookmark': 1,
								'bookmark_id': settings.bookmark_id
							},
							dataType: 'json',
							error: function() {},
							success: function(result) {
								if (result.length && result == 'OK') {
									$.removeLocalStorageItem('bookmarks');
									$.refreshBookmarksList(true);
								}
							}
						});
					}
				}
			});

			$('.delete-bookmark-form').on('keypress', function(e) {
				if (e.which == 13) {
					$('.delete-bookmark-btn').trigger('click');
				}
			});
		},

		removeLocalStorageItem: function(name) {
			if (typeof localStorage !== 'undefined') {
				localStorage.removeItem(name);
			}
		},

		refreshClock: function() {
			// ОПТИМИЗАЦИЯ: Один интервал для всех часов
			var update = function() {
				var date = new Date();
				var minutes = date.getMinutes();
				var hours = date.getHours();
				$(".clock #min").html((minutes < 10 ? "0" : "") + minutes);
				$(".clock #hours").html((hours < 10 ? "0" : "") + hours);
			};
			update();
			setInterval(update, 1000);
		},

		toggleEventFields: function(object, selector) {
			$(selector).toggleClass('hidden');
			object.parents('.row').eq(0).remove();
		},

		eventsPrepare: function() {
			// ОПТИМИЗАЦИЯ: Рекурсивный setTimeout
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
				'<span class="description"><i class="fa fa-clock-o"></i> ' + $.escapeHtml(oEvent['start']) + ' — <span class="notification-time">' + $.escapeHtml(oEvent['finish']) + '</span>' +
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

		notificationsPrepare: function() {
			// ОПТИМИЗАЦИЯ: Рекурсивный setTimeout
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
					jButton = jInputSearch.nextAll('.glyphicon-remove');

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

			jNotificationsListBox.find('.footer .fa-trash-o').on('click', $.clearNotifications);

			// Оптимизация обработчиков событий
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

				var clearBtn = $('.navbar-account #notificationsListBox .footer .fa-trash-o');
				jInputSearch.val() ? clearBtn.hide() : clearBtn.show();

				var removeIcon = jInputSearch.nextAll('.glyphicon-remove');
				jInputSearch.val() == '' ? removeIcon.addClass('hide') : removeIcon.removeClass('hide');

				$.filterNotifications(jInputSearch);
			});

			$('.navbar-account #notificationsListBox .glyphicon-remove').on('click', function() {
				var input = $(this).prevAll('#notification-search');
				input.val('');
				$.filterNotifications(input);
				$(this).addClass('hide');
				$('.navbar-account #notificationsListBox .footer .fa-trash-o').show();
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
					jNotificationExtra.append('<i class="fa ' + $.escapeHtml(item) + ' themeprimary"></i>');
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
							Notify($.escapeHtml(oNotification['title']), oNotification['description'], 'bottom-left', oNotification['timeout'], oNotification['notification']['background-color'], oNotification['notification']['ico'], true, bSound);
						}
					});
				} else {
					Notify($.escapeHtml(oNotification['title']), $.escapeHtml(oNotification['description']), 'bottom-left', oNotification['timeout'], oNotification['notification']['background-color'], oNotification['notification']['ico'], true, bSound);
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

					jNotificationsListBox.find('.footer .fa-trash-o').show();
					jNotificationsListBox.find('.footer #notification-search').show();
					jNotificationsListBox.find('.footer .glyphicon-search').show();
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

		localStorageGetItem: function(itemName) {
			if (typeof localStorage !== 'undefined') {
				try {
					var storage = localStorage.getItem(itemName);
					return storage ? JSON.parse(storage) : null;
				} catch (e) {
					return null;
				}
			}
			return null;
		},

		localStorageSetItem: function(itemName, object) {
			if (typeof localStorage !== 'undefined') {
				try {
					localStorage.setItem(itemName, JSON.stringify(object));
				} catch (e) {
					console.log('localStorage error: ' + e);
					$.removeLocalStorageItem(itemName);
				}
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
						$.localStorageSetItem('notificationRead', []);
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
					IDs: [],
					expire: 0
				};

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

			$('.navbar-account #notificationsListBox .footer .fa-trash-o').hide();
			$('.navbar-account #notificationsListBox .footer #notification-search').hide();
			$('.navbar-account #notificationsListBox .footer .glyphicon-search').hide();

			$.removeLocalStorageItem('notifications');
			$.removeLocalStorageItem('notificationRead');
		},

		storageAvailable: function(type) {
			try {
				var storage = window[type],
					x = '__storage_test__';
				storage.setItem(x, x);
				storage.removeItem(x);
				return true;
			} catch (e) {
				return false;
			}
		},

		// eventsWidgetPrepare... (без изменений или минимальные правки)
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
						var jEventItem = $(this).find('i').toggleClass('fa-square-o fa-check-square-o').parents('.task-item');
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
						$(this).children('i.fa-search').toggleClass('hidden');
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
		changeUserWorkdayButtons: function(status) {
			var data = {},
				aStatuses = ['ready', 'denied', 'working', 'break', 'completed', 'expired'],
				currentStatusIndex = $('li.workday #workdayControl').data('status');

			if (currentStatusIndex == status) return;

			if (currentStatusIndex == 0 && status == 2) data = {'startUserWorkday': 1};
			else if ((currentStatusIndex == 2 && status == 3) || (currentStatusIndex == 3 && status == 2)) data = {'pauseUserWorkday': 1};
			else if ((currentStatusIndex == 2 || currentStatusIndex == 5) && status == 4) data = {'stopUserWorkday': 1};
			else return false;

			$.ajax({
				url: hostcmsBackend + '/user/index.php',
				type: "POST",
				data: data,
				dataType: 'json',
				error: function() {},
				success: function(answer) {
					if (answer.result) {
						$('li.workday #workdayControl')
							.toggleClass(aStatuses[currentStatusIndex] + ' ' + aStatuses[answer.result])
							.data('status', answer.result);

						if (answer.result != 5) {
							$('#user-info-dropdown .login-area').removeClass('wave in');
						}
						$('span.user-workday-last-date').remove();
						$.blinkColon(answer.result);
					}
				}
			});
		},
		blinkColon: function(workdayStatus) {
			// Логика таймера оставлена (один таймер для двоеточия)
			var toggle = true;
			if ((workdayStatus == 2 || workdayStatus == 5) && !window.timerId) {
				window.timerId = setInterval(function() {
					$('.workday-timer .colon').css({ visibility: toggle ? 'hidden' : 'visible' });
					toggle = !toggle;
				}, 1000);
			}

			if ((workdayStatus != 2 && workdayStatus != 5) && window.timerId) {
				clearInterval(window.timerId);
				window.timerId = undefined;
				$('.workday-timer .colon').css({ visibility: 'visible' });
			}
		},
		toggleBackspace: function() {
			var phone = $('.phone-number').val();
			phone.length ? $('.backspace-button').removeClass('hidden') : $('.backspace-button').addClass('hidden');
		}
	});
})(jQuery);

$(function() {
	$('body').on('click', '.workday #workdayControl > span:not(.user-workday-end-text)', function(e) {
		e.stopPropagation();
		var object = $(this), status = 0;

		if (object.hasClass('user-workday-start') || object.hasClass('user-workday-continue')) status = 2;
		else if (object.hasClass('user-workday-pause')) status = 3;
		else if (object.hasClass('user-workday-stop')) {
			if (confirm($(this).data('confirm'))) status = 4;
		} else if (object.hasClass('user-workday-stop-another-time')) {
			$.modalLoad({
				title: $(this).data('title'),
				path: hostcmsBackend + '/user/index.php',
				additionalParams: 'showAnotherTimeModalForm',
				width: '50%',
				windowId: 'id_content',
				onHide: function() { $(".wickedpicker").remove(); }
			});
			return true;
		}
		$.changeUserWorkdayButtons(status);
	});
});