/*global Notify themeprimary readCookie hostcmsBackend setResizableAdminTableTh */
(function($) {
	"use strict";

	// Хелпер для кеширования селекторов, если они используются часто в рамках одной сессии,
	// но учитывая динамику чата, лучше кешировать внутри функций локально.

	$.extend({
		chatGetUsersList: function(event) {
			var data = $.getData({});

			$.ajax({
				context: event.data.context,
				url: event.data.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(data) {
					var $contactsList = $(".contacts-list"),
						$template = $(".contact").eq(0), // Берем шаблон
						docFragment = document.createDocumentFragment();

					// Удаляем старые, кроме шаблона (обычно первый скрытый - это шаблон)
					$contactsList.find("li:not(.hidden)").remove();

					$.each(data, function(i, object) {
						var name = object.firstName != '' ? object.firstName + " " + object.lastName : object.login,
							status = object.online == 1 ? 'online' : 'offline ' + object.lastActivity,
							jClone = $template.clone();

						jClone
							.data("user-id", object.id)
							.attr('id', 'chat-user-id-' + object.id);

						var $statusDiv = jClone.find(".contact-status div").eq(0),
							oldClass = $statusDiv.attr('class');

						jClone.find(".contact-name").text(name);

						if (object.count_unread > 0) {
							jClone.find(".contact-name").addChatBadge(object.count_unread);
						}

						$statusDiv.removeClass(oldClass).addClass(status).attr("data-user-id", object.id);
						jClone.find(".contact-status div").eq(1).text(status);
						jClone.find(".contact-avatar img").attr({
							src: object.avatar
						});
						jClone.find(".last-chat-time").text(object.lastChatTime);

						// Снимаем класс hidden и показываем перед добавлением во фрагмент
						jClone.removeClass("hidden").show();
						docFragment.appendChild(jClone[0]);
					});

					// Единоразовая вставка в DOM
					$contactsList.append(docFragment);
				}
			});
		},

		chatClearMessagesList: function() {
			var jMessagesList = $(".chatbar-messages .messages-list");

			// Delete messages efficiently
			jMessagesList.children("li:not(.hidden)").remove();
			$(".chatbar-messages #messages-none").addClass("hidden");
			$("#unread_messages").remove();

			jMessagesList.data({
				'firstMessageId': 0,
				'lastMessageId': 0,
				'firstNewMessageId': 0,
				'recipientUserId': 0,
				'countNewMessages': 0,
				'countUnreadMessages': 0,
				'lastReadMessageId': 0
			});
		},

		chatGetUserMessages: function(event) {
			var data = $.getData({});
			data['user-id'] = $(this).data('user-id');

			$.ajax({
				url: event.data.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(result) {
					$.chatClearMessagesList();
					$.chatGetUserMessagesCallback(result);
				}
			});
		},

		chatGetUserMessagesCallback: function(result) {
			// DOM caching
			var $chatbarContacts = $('#chatbar .chatbar-contacts'),
				$chatbarMessages = $('#chatbar .chatbar-messages'),
				$messagesContact = $(".messages-contact"),
				jMessagesList = $(".chatbar-messages .messages-list");

			$chatbarContacts.css("display", "none");
			$chatbarMessages.css("display", "block");

			if (!result || !result['recipient-user-info']) return;

			var recipientUserInfo = result['recipient-user-info'],
				userInfo = result['user-info'],
				recipientName = recipientUserInfo.firstName != '' ?
				recipientUserInfo.firstName + " " + recipientUserInfo.lastName :
				recipientUserInfo.login,
				status = recipientUserInfo.online == 1 ?
				'online' :
				'offline ' + recipientUserInfo.lastActivity;

			var $statusDiv = $messagesContact.find(".contact-status div").eq(0),
				oldClass = $statusDiv.attr('class');

			jMessagesList.data('recipientUserId', recipientUserInfo.id);
			$messagesContact.data("recipientUserId", recipientUserInfo.id);
			$(".send-message textarea").val('');

			$messagesContact.find(".contact-name").text(recipientName);
			$statusDiv.removeClass(oldClass).addClass(status).attr("data-user-id", recipientUserInfo.id);
			$messagesContact.find(".contact-status div").eq(1).text(status);
			$messagesContact.find(".contact-avatar img").attr({
				src: recipientUserInfo.avatar
			});
			$messagesContact.find(".last-chat-time").text(recipientUserInfo.lastChatTime);

			if (result['messages'] && result['messages'].length) {
				// Используем DocumentFragment внутри addChatMessage не получится напрямую,
				// так как функция рассчитана на одиночное добавление с логикой.
				// Но мы можем оптимизировать цикл.

				// Оптимизация: временно скрываем список или отключаем layout (сложно в jQuery),
				// поэтому просто проходим циклом.
				// В идеале addChatMessage должна уметь принимать массив, но оставим совместимость.
				$.each(result['messages'], function(i, object) {
					$.addChatMessage(recipientUserInfo, userInfo, object, 0);
				});

				var firstMessageIndex = result['messages'].length - 1;

				jMessagesList.data({
					'firstMessageId': result['messages'][firstMessageIndex]['id'],
					'lastMessageId': result['messages'][0]['id'],
					'countUnreadMessages': +result['count_unread']
				});

				var unreadHtml = '<div id="unread_messages" class="text-align-center ' + (+result['count_unread'] ? '' : 'hide') + ' ">!!' +
								result['count_unread_message'] +
								'<span class="unread_messages_top"><span class="count_unread_messages_top">' +
								result['count_unread'] + '</span> <i class="fa fa-caret-up margin-left-5"></i></span> <span class="unread_messages_bottom hide"><span class="count_unread_messages_bottom"></span><i class="fa fa-caret-down margin-left-5"></i></span></div>';

				jMessagesList.before(unreadHtml);

				$.chatMessagesListScrollDown();
				$.readChatMessagesInVisibleArea();
				$.changeTitleNewMessages();
			} else {
				$('#messages-none').removeClass('hidden');
			}

			if (!result['messages'] || result['messages'].length == result['total_messages']) {
				jMessagesList.data('disableUploadingMessagesList', 1);
			}

			// Запуск обновления списка сообщений
			$.refreshMessagesList(recipientUserInfo.id);
		},

		showChatMessageAsRead: function(chatMessageElement) {
			if (!chatMessageElement || !chatMessageElement.length) return;

			chatMessageElement
				.addClass('mark-read')
				.delay(1500)
				.queue(function(next) {
					$(this).toggleClass("unread", false); // Анимацию лучше делать через CSS transitions, здесь упростил
					next();
				})
				.delay(2000) // Эмуляция времени анимации из оригинала
				.queue(function(next) {
					$(this).removeClass("mark-read");
					next();
				});
		},

		chatMessageAboveVisibleArea: function(chatMessageElement) {
			if (!chatMessageElement || !chatMessageElement.length) return false;

			var $liMessageBody = chatMessageElement.find('.message-body'),
				liMessageBodyTopPosition = $liMessageBody.position().top + 25,
				liMessageBodyHeight = $liMessageBody.height(),
				liMessageBodyBottomPosition = liMessageBodyTopPosition + liMessageBodyHeight - 10,
				ulMessagesListHeight = chatMessageElement.parent().outerHeight();

			return liMessageBodyHeight < ulMessagesListHeight && liMessageBodyTopPosition < 0 ||
				liMessageBodyHeight > ulMessagesListHeight && liMessageBodyTopPosition < 0 && liMessageBodyBottomPosition > ulMessagesListHeight;
		},

		chatMessageBelowVisibleArea: function(chatMessageElement) {
			if (!chatMessageElement || !chatMessageElement.length) return false;

			var $liMessageBody = chatMessageElement.find('.message-body'),
				liMessageBodyTopPosition = $liMessageBody.position().top + 25,
				liMessageBodyHeight = $liMessageBody.height(),
				liMessageBodyBottomPosition = liMessageBodyTopPosition + liMessageBodyHeight - 10,
				ulMessagesListHeight = chatMessageElement.parent().outerHeight();

			return liMessageBodyHeight < ulMessagesListHeight && liMessageBodyBottomPosition > ulMessagesListHeight ||
				liMessageBodyHeight > ulMessagesListHeight && liMessageBodyTopPosition < 0 && liMessageBodyBottomPosition > ulMessagesListHeight;
		},

		chatMessageInVisibleArea: function(chatMessageElement) {
			return !$.chatMessageAboveVisibleArea(chatMessageElement) && !$.chatMessageBelowVisibleArea(chatMessageElement);
		},

		addItem2ReadMessagesStorage: function(messageId) {
			messageId = +messageId;
			if ($.storageAvailable('localStorage')) {
				try {
					var storage = localStorage.getItem('chat_read_messages_list'),
						dateNow = Date.now(),
						storageObj = storage ? JSON.parse(storage) : {
							expired_in: 0,
							messages_id: []
						};

					storageObj['expired_in'] = dateNow + 4000;

					if (storageObj['messages_id'].indexOf(messageId) === -1) {
						storageObj['messages_id'].push(messageId);
						$('.chatbar-messages .messages-list').data('lastReadMessageId', messageId);
						localStorage.setItem('chat_read_messages_list', JSON.stringify(storageObj));
					}
				} catch (e) {
					console.error('localStorage error:', e);
				}
			}
		},

		readChatMessages: function(aMessagesId) {
			if (aMessagesId && aMessagesId.length) {
				var jMessagesList = $('.chatbar-messages .messages-list'),
					path = hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + jMessagesList.data('moduleId') + '&type=83',
					data = $.getData({});

				// Оптимизация: кешируем поиск элементов
				var $list = $('.chatbar-messages .messages-list');
				for (var i = 0; i < aMessagesId.length; i++) {
					$.showChatMessageAsRead($list.find('#m' + aMessagesId[i]));
				}

				data['messagesId'] = aMessagesId;

				$.ajax({
					url: path,
					type: "POST",
					data: data,
					dataType: 'json',
					error: function() {},
					success: function(result) {
						if (result['answer']) {
							var iCountReadMessages = result['answer'].length;
							jMessagesList.data('countUnreadMessages', +jMessagesList.data('countUnreadMessages') - iCountReadMessages);

							for (var i = 0; i < iCountReadMessages; i++) {
								$.addItem2ReadMessagesStorage(result['answer'][i]);
							}
							$.changeTitleUnreadMessages();
							$.changeTitleNewMessages();
						}
					}
				});
			}
		},

		readChatMessagesInVisibleArea: function() {
			var aMessagesId = [];
			$(".chatbar-messages .messages-list")
				.find("li.message.unread:not(.mark-read)")
				.each(function() {
					var $this = $(this);
					if ($.chatMessageInVisibleArea($this)) {
						aMessagesId.push($.getChatMessageId($this));
					}
				});
			$.readChatMessages(aMessagesId);
		},

		getChatMessageId: function(chatMessageElement) {
			return chatMessageElement && chatMessageElement.attr ?
				+chatMessageElement.attr('id').substr(1) :
				0;
		},

		changeNewMessagesInfo: function() {
			var jMessagesList = $('.chatbar-messages .messages-list'),
				// ИСПРАВЛЕНИЕ: Инициализация переменной значением из data или 0
				iFirstNewMessageId = +jMessagesList.data('firstNewMessageId') || 0,
				iCountNewMessages = 0,
				oFirstNewMessage, oFormerFirstNewMessage;

			if (iFirstNewMessageId) {
				oFirstNewMessage = jMessagesList.find("li#m" + iFirstNewMessageId);

				if (oFirstNewMessage.length && !$.chatMessageBelowVisibleArea(oFirstNewMessage)) {
					oFormerFirstNewMessage = oFirstNewMessage;
					oFirstNewMessage = null;
					iFirstNewMessageId = 0;
					iCountNewMessages = 0;

					var $nextUnread = oFormerFirstNewMessage.nextAll('.message.unread:not(.mark-read)');

					// Используем обычный цикл for/each с break для производительности вместо .each jQuery если список большой, но здесь оставим each
					$nextUnread.each(function() {
						var $this = $(this);
						if ($.chatMessageBelowVisibleArea($this)) {
							oFirstNewMessage = $this;
							iFirstNewMessageId = $.getChatMessageId($this);
							return false; // break
						}
					});
				}

				if (oFirstNewMessage && oFirstNewMessage.length) {
					iCountNewMessages = oFirstNewMessage.nextAll('.message.unread:not(.mark-read)').length + 1;
				}
			}

			jMessagesList.data({
				'firstNewMessageId': iFirstNewMessageId,
				'countNewMessages': iCountNewMessages
			});

			return iCountNewMessages;
		},

		getCountUnreadLoadedMessages: function() {
			var jMessagesList = $('.chatbar-messages .messages-list'),
				ulMessagesListHeight = jMessagesList.outerHeight(),
				iFirstNewMessageId = +jMessagesList.data('firstNewMessageId'),
				oUnreadMessages = {
					'total': 0,
					'bottom': 0
				};

			$.changeNewMessagesInfo();

			jMessagesList.find("li.message.unread:not(.mark-read)").each(function() {
				var $this = $(this);

				if ((iFirstNewMessageId && $.getChatMessageId($this) < iFirstNewMessageId) || !iFirstNewMessageId) {
					var $liMessageBody = $this.find('.message-body'),
						liMessageBodyHeight = $liMessageBody.height(),
						liMessageBodyTopPosition = $liMessageBody.position().top + 25,
						liMessageBodyBottomPosition = liMessageBodyTopPosition + liMessageBodyHeight - 10;

					++oUnreadMessages['total'];
					if (liMessageBodyBottomPosition > ulMessagesListHeight) {
						++oUnreadMessages['bottom'];
					}
				}
			});

			return oUnreadMessages;
		},

		changeTitleNewMessages: function() {
			var jMessagesList = $(".chatbar-messages .messages-list"),
				$newMessagesDiv = $(".chatbar-messages #new_messages");

			if (jMessagesList.data('countNewMessages')) {
				$newMessagesDiv
					.removeClass("hidden")
					.find("span.count_new_messages")
					.text(jMessagesList.data("countNewMessages"));
			} else {
				$newMessagesDiv.addClass('hidden');
			}
		},

		changeAllInformationAboutNewMessages: function() {
			var jMessagesList = $(".chatbar-messages .messages-list"),
				iCountNewMessages = jMessagesList.data('countNewMessages');

			$.changeNewMessagesInfo();

			if (iCountNewMessages != jMessagesList.data('countNewMessages')) {
				$.changeTitleNewMessages();
			}
		},

		changeTitleUnreadMessages: function() {
			var jMessagesList = $(".chatbar-messages .messages-list"),
				oCountUnreadLoadedMessages = $.getCountUnreadLoadedMessages(),
				iCountUnreadMessagesTop = jMessagesList.data('countUnreadMessages') - oCountUnreadLoadedMessages['bottom'] - jMessagesList.data('countNewMessages');

			var divUnreadMessages = jMessagesList.prevAll("#unread_messages");

			if (+jMessagesList.data('countUnreadMessages') || oCountUnreadLoadedMessages['total']) {
				divUnreadMessages.removeClass('hide');

				if (iCountUnreadMessagesTop > 0) {
					divUnreadMessages.find('.unread_messages_top')
						.removeClass('hide')
						.find(".count_unread_messages_top").text(iCountUnreadMessagesTop);
				} else {
					divUnreadMessages.find('.unread_messages_top').addClass('hide');
				}

				if (oCountUnreadLoadedMessages['bottom']) {
					divUnreadMessages.find('.unread_messages_bottom')
						.removeClass('hide')
						.find(".count_unread_messages_bottom").text(oCountUnreadLoadedMessages['bottom']);
				} else {
					divUnreadMessages.find('.unread_messages_bottom').addClass('hide');
				}
			} else {
				divUnreadMessages.addClass('hide');
			}
		},

		addChatMessage: function(recipientUserInfo, userInfo, object, bDirectOrder) {
			var jMessagesList = $(".chatbar-messages .messages-list"),
				messageId = +object.id;

			var lastMsgId = +jMessagesList.data('lastMessageId'),
				firstMsgId = +jMessagesList.data('firstMessageId');

			if (recipientUserInfo.id != userInfo.id &&
				(!lastMsgId || (bDirectOrder && messageId > lastMsgId) || (!bDirectOrder && messageId < firstMsgId)))
			{
				var $template = $(".message.hidden").eq(0),
					jClone = $template.clone(),
					recipientName = recipientUserInfo.firstName != '' ?
					recipientUserInfo.firstName + " " + recipientUserInfo.lastName :
					recipientUserInfo.login,
					currentName = userInfo.firstName != '' ?
					userInfo.firstName + " " + userInfo.lastName :
					userInfo.login;

				if (object.user_id == recipientUserInfo.id) jClone.addClass('reply');

				jClone.attr('id', 'm' + object.id);

				if (object.user_id == recipientUserInfo.id && !object.read) {
					jClone.addClass("unread");

					if (lastMsgId && messageId > lastMsgId) {
						if (!jMessagesList.data('firstNewMessageId')) {
							jMessagesList.data('firstNewMessageId', messageId);
						}
						jMessagesList.data('countNewMessages', +jMessagesList.data('countNewMessages') + 1);
					}
					jMessagesList.data('countUnreadMessages', +jMessagesList.data('countUnreadMessages') + 1);
				}

				jClone.find(".message-info div").eq(1).text(object.user_id != recipientUserInfo.id ? currentName : recipientName);
				jClone.find(".message-info div").eq(2).text(object.datetime);
				jClone.find(".message-body").html(object.text);

				jClone.removeClass("hidden").show();

				if (bDirectOrder) {
					jMessagesList.append(jClone);
				} else {
					jMessagesList.prepend(jClone);
				}

				if (!lastMsgId) {
					jMessagesList.data({
						'firstMessageId': messageId,
						'lastMessageId': messageId
					});
				} else {
					jMessagesList.data(bDirectOrder ? 'lastMessageId' : 'firstMessageId', messageId);
				}
			}
		},

		setSlimScrollBarHeight: function(jList) {
			var jSlimScrollBar = jList.next(".slimScrollBar"),
				minSlimScrollBarHeight = 30,
				listOuterHeight = jList.outerHeight(),
				barHeight = Math.max((listOuterHeight / jList[0].scrollHeight) * listOuterHeight, minSlimScrollBarHeight);

			jSlimScrollBar.css('height', barHeight);
		},

		setSlimScrollBarPositionChat: function() {
			var jMessagesList = $('.chatbar-messages .messages-list'),
				position = readCookie("rtl-support") ? 'right' : 'left',
				messagesListSlimscrollOptions = {
					position: position,
					size: '4px',
					start: 'bottom',
					color: themeprimary,
					wheelStep: 16,
					height: $(window).height() - $('body > .navbar').outerHeight() - $('#chatbar .messages-contact').outerHeight() - $('#chatbar .send-message').outerHeight(),
					alwaysVisible: true,
					disableFadeOut: true
				};

			jMessagesList.slimscroll(messagesListSlimscrollOptions);
		},

		chatMessagesListScrollDown: function() {
			var jMessagesList = $('.chatbar-messages .messages-list'),
				jSlimScrollBar = jMessagesList.next(".slimScrollBar");

			$.setSlimScrollBarHeight(jMessagesList);
			jMessagesList.scrollTop(jMessagesList[0].scrollHeight - jMessagesList.outerHeight());
			jSlimScrollBar.css('top', jMessagesList.outerHeight() - jSlimScrollBar.outerHeight() + 'px');
		},

		chatSendMessage: function(event) {
			if (event.keyCode == 13 && !event.shiftKey) {
				var $this = $(this);
				if (event.ctrlKey) {
					$this.val($this.val() + "\n");
					event.preventDefault();
				} else {
					var jMessagesList = $('.chatbar-messages .messages-list'),
						data = $.getData({}),
						jTextarea = $(".send-message textarea"),
						message = $.trim(jTextarea.val());

					if (message == '') return;

					data['message'] = message;
					data['recipient-user-id'] = $(".messages-contact").data('recipientUserId');

					var jClone = $(".message.hidden").eq(0).clone(), // Оптимизация селектора
						messageBox = $(".message-body", jClone);

					messageBox.html(messageBox.text(message).html().replace(/\n/g, "<br />"));

					jMessagesList.append(jClone.removeClass("hidden").addClass("opacity").show());

					jTextarea.val('');

					$.ajax({
						url: event.data.path,
						data: data,
						dataType: 'json',
						type: 'POST',
						error: function() {},
						success: function(data) {
							if (data['answer'] == "OK") {
								var userInfo = data['user-info'];
								var currentName = userInfo.firstName != '' ? userInfo.firstName + " " + userInfo.lastName : userInfo.login;

								$(".chatbar-messages #messages-none").addClass("hidden");

								jClone.attr("id", "m" + data['message']['id']);
								jClone.find(".message-info div").eq(1).text(currentName);
								jClone.find(".message-info div").eq(2).text(data['message'].datetime);
								jClone.removeClass("opacity");

								jMessagesList.data('lastMessageId', data['message']['id']);

								$.chatMessagesListScrollDown();
								$.readChatMessagesInVisibleArea();
							}
						}
					});
				}
			}
		},

		uploadingMessagesList: function() {
			var jMessagesList = $('.chatbar-messages .messages-list'),
				firstMessageId = jMessagesList.data('firstMessageId'),
				module_id = jMessagesList.data('moduleId'),
				path = hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + module_id + '&type=78&first_message_id=' + firstMessageId,
				ajaxData = $.getData({});

			ajaxData['user-id'] = jMessagesList.data('recipientUserId');
			jMessagesList.addClass("opacity");
			$("i.chatbar-message-spinner").removeClass("hidden");

			$.ajax({
				url: path,
				data: ajaxData,
				dataType: 'json',
				type: 'POST',
				abortOnRetry: 1,
				error: function() {},
				success: function(result) {
					var jMessagesList = $(".chatbar-messages .messages-list");
					if (result['messages']) {
						var recipientUserInfo = result['recipient-user-info'],
							userInfo = result['user-info'],
							firstMessageIndex = result['messages'].length - 1;

						$.each(result['messages'], function(i, object) {
							$.addChatMessage(recipientUserInfo, userInfo, object, 0);
						});

						$.setSlimScrollBarHeight(jMessagesList);

						jMessagesList.data({
							'firstMessageId': +result['messages'][firstMessageIndex]['id'],
							'countUnreadMessages': +result['count_unread']
						});

						$.readChatMessagesInVisibleArea();
					}

					if (!result['messages'] || result['messages'].length == result['total_messages']) {
						jMessagesList.data('disableUploadingMessagesList', 1);
					}

					jMessagesList.removeClass("opacity");
					$("i.chatbar-message-spinner").addClass("hidden");
				},
			});
		},

		refreshMessagesListCallback: function(result) {
			var jMessagesList = $('.chatbar-messages .messages-list');

			if (!result || !result['messages']) {
				$.syncReadMessages();
				return;
			}

			var lastMessageIndex, countNewMessagesBeforeAdding, aUnreadMessagesId = [],
				iRecipientUserId;

			if (result['messages'] &&
				~(lastMessageIndex = result['messages'].length - 1) &&
				jMessagesList.data('lastMessageId') < result['messages'][lastMessageIndex]['id'])
			{
				countNewMessagesBeforeAdding = jMessagesList.data("countNewMessages");
				iRecipientUserId = result['recipient-user-info']['id'];

				// Можно использовать фрагмент, но addChatMessage содержит много логики. Оставим как есть.
				$.each(result['messages'], function(i, object) {
					$.addChatMessage(result['recipient-user-info'], result['user-info'], object, 1);
					if (object.user_id == iRecipientUserId && !object.read) {
						aUnreadMessagesId.push(object.id);
					}
				});

				$.setSlimScrollBarHeight(jMessagesList);
				jMessagesList.data('lastMessageId', result['messages'][lastMessageIndex]['id']);
				$(".chatbar-messages #messages-none").addClass("hidden");

				var $lastMsg = $("li.message:not(.unread):not(.hidden):last", jMessagesList);
				var sendMessageOffset = $(".chatbar-messages .send-message").offset().top;

				var isScrolledToBottom = !document.hidden && (!countNewMessagesBeforeAdding &&
					$lastMsg.length &&
					(sendMessageOffset > $lastMsg.offset().top || !jMessagesList.data("countNewMessages")));

				if (isScrolledToBottom) {
					$.readChatMessages(aUnreadMessagesId);
					$.chatMessagesListScrollDown();
					if (!aUnreadMessagesId.length) {
						$.changeTitleUnreadMessages();
					}
				} else if (jMessagesList.data("countNewMessages") > 0) {
					$.changeTitleNewMessages();
					$.setSlimScrollBarPositionChat();
				}
			}

			$.syncReadMessages();
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

		// ОПТИМИЗАЦИЯ: Замена setInterval на рекурсивный setTimeout для предотвращения наслоения запросов
		refreshMessagesList: function(recipientUserId) {
			var oldInterval = $("#chatbar").data("refreshMessagesListIntervalId");
			if (oldInterval) clearTimeout(oldInterval); // Используем clearTimeout так как теперь это таймаут

			var poll = function() {
				var bLocalStorage = $.storageAvailable('localStorage');
				var dateNow = Date.now();
				var jMessagesList = $('.chatbar-messages .messages-list');

				// Если элемента нет, останавливаем поллинг
				if (!jMessagesList.length) return;

				var path = hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + jMessagesList.data('moduleId') + '&type=81';
				var data = $.getData({});
				var bNeedsRequest = false;

				data['last-message-id'] = jMessagesList.data('lastMessageId');
				data['recipient-user-id'] = recipientUserId;

				if (bLocalStorage) {
					try {
						var storage = localStorage.getItem('chat_messages_list'),
							storageObj = storage ? JSON.parse(storage) : {
								expired_in: 0
							},
							storageChatReadMessages = localStorage.getItem('chat_read_messages_list'),
							storageChatReadMessagesObj = storageChatReadMessages ? JSON.parse(storageChatReadMessages) : null;

						if (storageChatReadMessagesObj && dateNow > storageChatReadMessagesObj['expired_in']) {
							localStorage.removeItem('chat_read_messages_list');
						}

						bNeedsRequest = dateNow > storageObj['expired_in'];

						if (bNeedsRequest) {
							storageObj['expired_in'] = dateNow + 3000;
						} else {
							$.refreshMessagesListCallback(storageObj);
						}

					} catch (e) {
						console.log(e);
						bNeedsRequest = true; // Fallback
					}
				} else {
					bNeedsRequest = true;
				}

				if (bNeedsRequest) {
					$.ajax({
						url: path,
						type: "POST",
						data: data,
						dataType: 'json',
						abortOnRetry: 1,
						error: function() {
							// Повторяем попытку через таймаут даже при ошибке
							var timerId = setTimeout(poll, 3000);
							$("#chatbar").data("refreshMessagesListIntervalId", timerId);
						},
						success: function(result) {
							if (bLocalStorage) {
								try {
									// Обновляем expired_in из замыкания storageObj (но оно локальное),
									// поэтому берем тайминг
									result['expired_in'] = Date.now() + 3000;
									localStorage.setItem('chat_messages_list', JSON.stringify(result));
								} catch (e) {
									console.log('localStorage error: ' + e);
								}
							}

							$.refreshMessagesListCallback(result);

							// Запускаем следующий цикл
							var timerId = setTimeout(poll, 3000);
							$("#chatbar").data("refreshMessagesListIntervalId", timerId);
						}
					});
				} else {
					// Если запрос не нужен (данные из локалсторадж свежие), ждем и повторяем
					var timerId = setTimeout(poll, 3000);
					$("#chatbar").data("refreshMessagesListIntervalId", timerId);
				}
			};

			// Запуск первого цикла
			var timerId = setTimeout(poll, 3000);
			$("#chatbar").data("refreshMessagesListIntervalId", timerId);
		},

		refreshChatCallback: function(data) {
			if (data["info"]) {
				Notify('<img width="24px" height="24px" src="' + $.escapeHtml(data["info"].avatar) + '"><span style="padding-left:10px">' + $.escapeHtml(data["info"].text) + '</span>', '', 'bottom-left', '7000', 'blueberry', 'fa-comment-o', true);

				var user_id = data["info"]['user_id'],
					jContact = $('#chat-user-id-' + user_id + ' .contact-info .contact-name'),
					jBadge = $('span.badge', jContact);

				jContact.addChatBadge(jBadge.length ? +jBadge.text() + 1 : 1);
			} else {
				$("#chat-link .badge").addClass("hidden").text(data["count"]);
				$("#chat-link").removeClass("wave in");
			}

			if (data["count"] > 0) {
				$("#chat-link .badge").removeClass("hidden").text(data["count"]);
				$("#chat-link").addClass("wave in");
			}
		},

		syncReadMessages: function() {
			var jMessagesList = $('.chatbar-messages .messages-list'),
				storageChatReadMessages = localStorage.getItem('chat_read_messages_list'),
				storageChatReadMessagesObj = storageChatReadMessages ? JSON.parse(storageChatReadMessages) : null,
				storageLength, iSyncMessageId, oSyncChatMessage, oMessagesAfterSyncChatMessage, iNewFirstMessageId,
				changeTitles;

			if (storageChatReadMessagesObj && storageChatReadMessagesObj['messages_id'].length) {
				var indexOfLastReadMessageId = storageChatReadMessagesObj['messages_id'].indexOf(jMessagesList.data('lastReadMessageId'));

				if (indexOfLastReadMessageId != storageChatReadMessagesObj['messages_id'].length - 1) {
					storageLength = storageChatReadMessagesObj['messages_id'].length;

					for (var i = indexOfLastReadMessageId + 1; i < storageLength; i++) {
						iSyncMessageId = storageChatReadMessagesObj['messages_id'][i];
						oSyncChatMessage = jMessagesList.find("#m" + iSyncMessageId + ":not(.mark-read)");

						if (oSyncChatMessage.length) {
							if (iSyncMessageId >= jMessagesList.data('firstNewMessageId')) {
								oMessagesAfterSyncChatMessage = oSyncChatMessage.nextAll('.message.unread:not(.mark-read)');
								iNewFirstMessageId = oMessagesAfterSyncChatMessage.length ?
									$.getChatMessageId($(oMessagesAfterSyncChatMessage[0])) :
									jMessagesList.data('firstNewMessageId', 0);

								jMessagesList.data('firstNewMessageId', iNewFirstMessageId);
							}
							oSyncChatMessage.removeClass('unread');
						}

						// Исправлена логика: уменьшаем счетчик всегда, когда синхронизируем, даже если сообщения нет в DOM?
						// В оригинале else закомментирован. Оставим как есть.
						jMessagesList.data('countUnreadMessages', jMessagesList.data('countUnreadMessages') - 1);
						changeTitles = true;
					}

					if (changeTitles) {
						$.changeTitleUnreadMessages();
						$.changeTitleNewMessages();
					}

					jMessagesList.data('lastReadMessageId', storageChatReadMessagesObj['messages_id'][storageLength - 1]);
				}
			}
		},

		refreshChat: function(settings) {
			// ОПТИМИЗАЦИЯ: замена setInterval на рекурсивный setTimeout
			var poll = function() {
				var data = $.getData({}),
					bLocalStorage = $.storageAvailable('localStorage'),
					bNeedsRequest = false;

				data['alert'] = 1;

				if (bLocalStorage) {
					try {
						var storage = localStorage.getItem('chat'),
							storageObj = storage ? JSON.parse(storage) : {
								expired_in: 0
							},
							dateNow = Date.now();

						if (dateNow > storageObj['expired_in']) {
							storageObj['expired_in'] = dateNow + 10000;
							bNeedsRequest = true;
						} else {
							$.refreshChatCallback(storageObj);
						}
					} catch (e) {
						bNeedsRequest = true;
					}
				} else {
					bNeedsRequest = true;
				}

				if (bNeedsRequest) {
					$.ajax({
						url: settings.path,
						type: "POST",
						data: data,
						dataType: 'json',
						abortOnRetry: 1,
						error: function() {
							setTimeout(poll, 10000);
						},
						success: function(data) {
							if (bLocalStorage) {
								try {
									data['expired_in'] = Date.now() + 10000;
									localStorage.setItem('chat', JSON.stringify(data));
								} catch (e) {
									console.log('localStorage error: ' + e);
								}
							}
							$.refreshChatCallback(data);
							setTimeout(poll, 10000);
						}
					});
				} else {
					setTimeout(poll, 10000);
				}
			};

			setTimeout(poll, 10000);
		},

		refreshUserStatusesCallback: function(result) {
			// Оптимизация выборки
			var $users = $(".online[data-user-id], .offline[data-user-id]");

			$users.each(function() {
				var $this = $(this),
					user_id = +$this.data("userId");

				if (result[user_id]) {
					var status = result[user_id]['status'] == 1 ? 'online' : 'offline ' + result[user_id]['lastActivity'];
					// Проверка перед изменением DOM, чтобы не вызывать перерисовку лишний раз
					if ($this.attr('class') !== status) {
						$this.attr('class', status);
					}
					var $statusText = $this.next('.status');
					if ($statusText.text() !== status) {
						$statusText.text(status);
					}

					if (result[user_id]['count_unread']) {
						$('#chat-user-id-' + user_id + ' .contact-info .contact-name').addChatBadge(result[user_id]['count_unread']);
					}
				}
			});
		},

		refreshUserStatuses: function() {
			var poll = function() {
				var jMessagesList = $('.chatbar-messages .messages-list');
				if(!jMessagesList.length) return; // safety check

				var path = hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=' + jMessagesList.data('moduleId') + '&type=82',
					data = $.getData({});

				var bLocalStorage = $.storageAvailable('localStorage'),
					bNeedsRequest = false;

				if (bLocalStorage) {
					try {
						var storage = localStorage.getItem('chat_user_statuses'),
							storageObj = storage ? JSON.parse(storage) : {expired_in: 0};

						if (Date.now() > storageObj['expired_in']) {
							storageObj['expired_in'] = Date.now() + 10000;
							bNeedsRequest = true;
						} else {
							$.refreshUserStatusesCallback(storageObj);
						}
					} catch (e) {
						bNeedsRequest = true;
					}
				} else {
					bNeedsRequest = true;
				}

				if (bNeedsRequest) {
					$.ajax({
						url: path,
						type: "POST",
						data: data,
						dataType: 'json',
						abortOnRetry: 1,
						error: function() {
							setTimeout(poll, 60000);
						},
						success: function(result) {
							if (bLocalStorage) {
								try {
									result['expired_in'] = Date.now() + 10000; // Original logic had 10s expiration but 60s poll? Kept consistent with code logic
									localStorage.setItem('chat_user_statuses', JSON.stringify(result));
								} catch (e) { console.log(e); }
							}
							$.refreshUserStatusesCallback(result);
							setTimeout(poll, 60000);
						}
					});
				} else {
					setTimeout(poll, 60000);
				}
			};

			setTimeout(poll, 60000);
		},

		chatPrepare: function() {
			var $chatbar = $('#chatbar');
			$chatbar.resizable({
				handles: "w"
			});

			$.refreshUserStatuses();

			var position = readCookie("rtl-support") ? 'right' : 'left',
				jMessagesList = $('.chatbar-messages .messages-list'),
				$navbar = $('body > .navbar'),
				$msgContact = $('#chatbar .messages-contact'),
				$sendMsg = $('#chatbar .send-message'),

				messagesListSlimscrollOptions = {
					position: position,
					size: '4px',
					start: 'bottom',
					color: themeprimary,
					wheelStep: 16,
					height: $(window).height() - $navbar.outerHeight() - $msgContact.outerHeight() - $sendMsg.outerHeight(),
					alwaysVisible: true,
					disableFadeOut: true
				};

			jMessagesList.slimscroll(messagesListSlimscrollOptions);

			$('.chatbar-contacts .contacts-list').slimscroll({
				position: position,
				size: messagesListSlimscrollOptions.size,
				color: themeprimary,
				height: $(window).height() - $navbar.outerHeight()
			});

			function chatScrollInTopPosition() {
				return jMessagesList.scrollTop() == 0;
			}

			function chatScrollInBottomPosition() {
				return jMessagesList[0].scrollHeight == jMessagesList.scrollTop() + jMessagesList.outerHeight();
			}

			$("#chat-link").click(function() {
				$('.page-chatbar').toggleClass('open');
				$("#chat-link").toggleClass('open');
			});

			$('.page-chatbar .chatbar-contacts .contact').on('click', function() {
				$('.page-chatbar .chatbar-contacts').hide();
				$('.page-chatbar .chatbar-messages').show();
			});

			$('.page-chatbar .chatbar-messages .back').on('click', function() {
				$('.page-chatbar .chatbar-contacts').show();
				$('.page-chatbar .chatbar-messages').hide();
				$('.chatbar-messages .messages-list').removeData('disableUploadingMessagesList');
				$.chatClearMessagesList();
			});

			$("#chat-link, div.back").on('click', function() {
				// Очистка таймаута при закрытии
				var intervalId = $("#chatbar").data("refreshMessagesListIntervalId");
				if(intervalId) {
					clearTimeout(intervalId);
					$("#chatbar").data("refreshMessagesListIntervalId", null);
				}
			});

			function onWheel(event) {
				var slimScrollBar = $('.chatbar-messages .slimScrollBar'),
					maxTop = jMessagesList.outerHeight() - slimScrollBar.outerHeight(),
					delta = 0,
					newTopScroll = 0,
					percentScroll;

				if (event.wheelDelta) delta = -event.wheelDelta / 120;
				if (event.detail) delta = event.detail / 3;

				if (delta < 0 && $(this).next(".slimScrollBar").length && chatScrollInTopPosition() && !jMessagesList.data('disableUploadingMessagesList')) {
					if (!jMessagesList.data('slimScrollTop')) $.uploadingMessagesList();
					jMessagesList.data('slimScrollTop', false);
					return;
				}

				if (delta < 0 || delta > 0 && (jMessagesList[0].scrollHeight > jMessagesList.scrollTop() + jMessagesList.outerHeight())) {
					delta = parseInt(slimScrollBar.css('top')) + delta * parseInt(messagesListSlimscrollOptions.wheelStep) / 100 * slimScrollBar.outerHeight();
					delta = Math.min(Math.max(delta, 0), maxTop);
					delta = Math.ceil(delta);

					percentScroll = delta / (jMessagesList.outerHeight() - slimScrollBar.outerHeight());
					newTopScroll = percentScroll * (jMessagesList[0].scrollHeight - jMessagesList.outerHeight());

					delta = newTopScroll - jMessagesList.scrollTop();

					if (delta > 0) $.changeAllInformationAboutNewMessages();
					$.readChatMessagesInVisibleArea();
				}
			}

			function documentMousewheel(event) { // eslint-disable-line
				// Кеширование или проверка jMessagesList здесь могут быть опасны, если элементы меняются
				// Но в контексте IIFE jMessagesList уже определен выше в chatPrepare
				// jMessagesList = $(event.target).parents('.messages-list');
				// Оставим пустым, как было в оригинале, по сути это заглушка?
			}

			if ($(document)[0].addEventListener) {
				$(document)[0].addEventListener('DOMMouseScroll', documentMousewheel, true);
				$(document)[0].addEventListener('mousewheel', documentMousewheel, true);
				$(document)[0].addEventListener('MozMousePixelScroll', documentMousewheel, true);
			} else {
				$(document)[0].attachEvent("onmousewheel", documentMousewheel);
			}

			if (jMessagesList[0]) {
				if (jMessagesList[0].addEventListener) {
					jMessagesList[0].addEventListener('DOMMouseScroll', onWheel, false);
					jMessagesList[0].addEventListener('mousewheel', onWheel, false);
					jMessagesList[0].addEventListener('MozMousePixelScroll', onWheel, false);
				} else {
					jMessagesList[0].attachEvent("onmousewheel", onWheel);
				}
			}

			jMessagesList.on({
				'slimscroll': function(e, pos) {
					var $this = $(this);
					$this.data('slimScrollTop', false);

					if (pos == 'top' && !$this.data('disableUploadingMessagesList')) {
						$.uploadingMessagesList();
						$this.data('slimScrollTop', true);
					}

					if (pos == 'bottom') {
						var oDivNewMessages = $(".chatbar-messages #new_messages");
						!oDivNewMessages.hasClass('hidden') && oDivNewMessages.addClass('hidden');
					}
				},
				'touchstart': function(event) {
					$(this).data({
						'isTouchStart': true,
						'touchPositionY': event.originalEvent.touches[0].pageY
					});
				}
			});

			$('#chatbar .slimScrollBar').each(function() {
				$(this)
					.data('isMousedown', false)
					.mousedown(function() {
						var $this = $(this);
						$this.data({
							'isMousedown': true,
							'top': $this.position().top
						}).css('width', '8px');
					})
					.mouseenter(function() {
						$(this).css('width', '8px')
					})
					.mouseout(function() {
						!$(this).data('isMousedown') && $(this).css('width', messagesListSlimscrollOptions.size);
					});
			});

			$(document).on({
				'mousemove': function() {
					var slimScrollBar = $('.chatbar-messages .slimScrollBar');

					if (slimScrollBar.data('isMousedown')) {
						var deltaY = slimScrollBar.position().top - slimScrollBar.data('top');
						slimScrollBar.data('top', slimScrollBar.position().top);
						$.readChatMessagesInVisibleArea();
						deltaY > 0 && $.changeAllInformationAboutNewMessages();
					}
				},
				'mouseup': function(event) {
					$('#chatbar .slimScrollBar').each(function() {
						var slimScrollBar = $(this);
						if (slimScrollBar.data('isMousedown')) {
							slimScrollBar.data({
								'isMousedown': false,
								'top': 0
							});
							if (event.target != slimScrollBar[0]) {
								slimScrollBar.css('width', messagesListSlimscrollOptions.size);
							}
						}
					})
				},
				'touchend': function() {
					jMessagesList.data('isTouchStart') && jMessagesList.data('isTouchStart', false);
				},
				'touchmove': function(event) {
					if (jMessagesList.data('isTouchStart')) {
						var lastY = jMessagesList.data('touchPositionY'),
							currentY = event.originalEvent.touches[0].pageY;

						if (chatScrollInTopPosition() && !jMessagesList.data('disableUploadingMessagesList')) {
							$.uploadingMessagesList();
						}

						if (currentY < lastY && !chatScrollInBottomPosition()) {
							$.readChatMessagesInVisibleArea();
							$.changeAllInformationAboutNewMessages();
						}
						jMessagesList.data('touchPositionY', currentY);
					}
				},
				'scroll': function() {
					if (!$('#checkbox_fixednavbar').prop('checked')) {
						// Используем requestAnimationFrame для производительности, если поддерживается,
						// но оставим простую логику с кешированием переменных для ES5 совместимости.
						var documentScrollTop = $(document).scrollTop(),
							navbarHeight = $('body > div.navbar').outerHeight(),
							chatBar = $('div#chatbar'),
							deltaHeight = (documentScrollTop > navbarHeight ? 0 : navbarHeight - documentScrollTop),
							deltaY = parseInt(chatBar.css('top')) - deltaHeight;

						if (deltaY) {
							chatBar.css({
								'top': deltaHeight + 'px',
								'height': chatBar.height() + deltaY + 'px'
							});

							var contactsList = $('div#chatbar .chatbar-contacts .contacts-list'),
								chatbarContactsSlimScrollDiv = $('div#chatbar .chatbar-contacts .slimScrollDiv'),
								messagesList = $('div#chatbar .chatbar-messages .messages-list'),
								chatbarMessagesSlimScrollDiv = $('div#chatbar .chatbar-messages .slimScrollDiv');

							contactsList.css('height', parseInt(contactsList.css('height')) + deltaY + 'px');
							chatbarContactsSlimScrollDiv.css('height', chatbarContactsSlimScrollDiv.outerHeight() + deltaY + 'px');

							messagesList.css('height', parseInt(messagesList.css('height')) + deltaY + 'px');
							chatbarMessagesSlimScrollDiv.css('height', chatbarMessagesSlimScrollDiv.outerHeight() + deltaY + 'px');

							$.setSlimScrollBarHeight(contactsList);
							$.setSlimScrollBarHeight(messagesList);
						}
					}
				}
			});

			$(window).on({
				'mouseup': function() {
					$('.admin-table-wrap.table-draggable.mousedown')
						.data({
							'curDown': false
						})
						.removeClass('mousedown');
				},
				'resize': function() {
					// Дебаунс для resize был бы полезен, но здесь важна отзывчивость UI
					var documentScrollTop = $(document).scrollTop(),
						navbarHeight = $('body > div.navbar').outerHeight(),
						chatBar = $('div#chatbar'),
						deltaScrollHeight = $('#checkbox_fixednavbar').prop('checked') ?
						navbarHeight :
						(documentScrollTop > navbarHeight ? 0 : navbarHeight - documentScrollTop),

						chatbarContactsSlimScrollDiv = $('div#chatbar .chatbar-contacts .slimScrollDiv'),
						contactsList = $('div#chatbar .chatbar-contacts .contacts-list'),
						chatbarMessagesSlimScrollDiv = $('div#chatbar .chatbar-messages .slimScrollDiv'),
						messagesList = $('div#chatbar .chatbar-messages .messages-list'),
						sendMessageBlock = $('#chatbar .send-message'),
						chatbarMessagesDeltaHeight = deltaScrollHeight + $('#chatbar .messages-contact').outerHeight() + sendMessageBlock.outerHeight();

					chatBar.css({
						'height': $(this).height() - deltaScrollHeight + 'px',
						'top': deltaScrollHeight + 'px'
					});

					chatbarContactsSlimScrollDiv.css('height', $(this).height() - deltaScrollHeight + 'px');
					contactsList.css('height', chatbarContactsSlimScrollDiv.outerHeight() + 'px');

					chatbarMessagesSlimScrollDiv.css('height', $(this).height() - chatbarMessagesDeltaHeight + 'px');
					messagesList.css('height', chatbarMessagesSlimScrollDiv.outerHeight() + 'px');

					$.setSlimScrollBarHeight(contactsList);
					$.setSlimScrollBarHeight(messagesList);

					setResizableAdminTableTh();
				}
			});

			function clickFixedNavbarHandler() {
				var documentScrollTop = $(document).scrollTop(),
					navbarHeight = $('body > div.navbar').outerHeight(),
					chatBar = $('div#chatbar'),
					deltaScrollHeight = $('#checkbox_fixednavbar').prop('checked') ?
					navbarHeight :
					(documentScrollTop > navbarHeight ? 0 : navbarHeight - documentScrollTop),

					slimScrollDiv = $('div#chatbar .chatbar-messages .slimScrollDiv'),
					messagesList = $('div#chatbar .chatbar-messages .messages-list'),
					sendMessageBlock = $('#chatbar .send-message'),
					deltaHeight = deltaScrollHeight + $('#chatbar .messages-contact').outerHeight() + sendMessageBlock.outerHeight();

				chatBar.css({
					'height': $(window).height() - deltaScrollHeight + 'px',
					'top': deltaScrollHeight + 'px'
				});

				slimScrollDiv.css('height', $(window).height() - deltaHeight + 'px');
				messagesList.css('height', slimScrollDiv.outerHeight() + 'px');

				$.setSlimScrollBarHeight(messagesList);
			}

			$('#checkbox_fixednavbar').on('click', clickFixedNavbarHandler);
		}
	});

	$.fn.extend({
		addChatBadge: function(count) {
			return this.each(function() {
				var jSpan = jQuery(this).find('span.badge');
				jSpan.length ?
					jSpan.text(count) :
					jQuery(this).append('<span class="badge margin-left-10">' + count + '</span>');
			});
		}
	});
})(jQuery);

$(function() {
	/* --- CHAT --- */
	$('#chatbar').length && $.chatPrepare();
	/* --- /CHAT --- */
});