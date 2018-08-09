(function($){
	$.extend({
		widgetLoad: function(settings)
		{
			// add ajax '_'
			var data = $.getData({});

			settings = $.extend({
				'button': null
			}, settings);

			settings.button && settings.button.addClass('fa-spin');

			$.ajax({
				context: settings.context,
				url: settings.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(data){
					this.html(data.form_html);
				}
			});
		},
		ajaxCallbackSkin: function(data, status, jqXHR)
		{
			if (typeof data.module != 'undefined')
			{
				// Выделить текущий пункт левого бокового меню
				$.currentMenu(data.module);
			}
		},
		currentMenu: function(moduleName)
		{
			$('#sidebar li').removeClass('active').removeClass('open');

			$('#menu-'+moduleName).addClass('active')
				.parents('li').addClass('active').addClass('open');

			$('#sidebar li[class != open] ul.submenu').hide();
		},
		afterContentLoad: function(jWindow, data)
		{
			data = typeof data !== 'undefined' ? data : {};

			if (typeof data.title != 'undefined' && data.title != '' && jWindow.attr('id') != 'id_content')
			{
				var jSpanTitle = jWindow.find('span.ui-dialog-title');
				if (jSpanTitle.length)
				{
					jSpanTitle.empty().html(data.error);
				}
			}
		},
		windowSettings: function(settings)
		{
			return jQuery.extend({
				Closable: true
			}, settings);
		},
		openWindow: function(settings)
		{
			settings = jQuery.windowSettings(
				jQuery.requestSettings(settings)
				//settings
			);

			settings = $.extend({
				open: function( event, ui ) {
					var uiDialog = $(this).parent('.ui-dialog');
					uiDialog.width(uiDialog.width()).height(uiDialog.height());
				},
				close: function( event, ui ) {
					$(this).dialog('destroy').remove();
				}
			}, settings);

			var cmsrequest = settings.path;
			if (settings.additionalParams != ' ' && settings.additionalParams != '')
			{
				cmsrequest += '?' + settings.additionalParams;
			}

			var windowCounter = $('body').data('windowCounter');
			if (windowCounter == undefined) { windowCounter = 0 }
			$('body').data('windowCounter', windowCounter + 1);

			var jDivWin = $('<div>')
				.addClass("hostcmsWindow")
				.attr("id", "Window" + windowCounter)
				.appendTo($(document.body))
				.dialog(settings);

			var data = jQuery.getData(settings);
			// Change window id
			data['hostcms[window]'] = jDivWin.attr('id');

			//console.log(data);

			jQuery.ajax({
				context: jDivWin,
				url: cmsrequest,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: jQuery.ajaxCallback
			});

			return jDivWin;
		},
		openWindowAddTaskbar: function(settings)
		{
			return jQuery.adminLoad(settings);
		},
		ajaxCallbackModal: function(data, status, jqXHR) {
			$.loadingScreen('hide');
			if (data == null || data.form_html == null)
			{
				alert('AJAX response error.');
				return;
			}

			var jObject = jQuery(this),
				jBody = jObject.find(".modal-body")

			if (data.form_html != '')
			{
				jQuery.beforeContentLoad(jBody, data);
				jQuery.insertContent(jBody, data.form_html);
				jQuery.afterContentLoad(jBody, data);
			}

			var jMessage = jBody.find("#id_message");

			if (jMessage.length === 0)
			{
				jMessage = jQuery("<div>").attr('id', 'id_message');
				jBody.prepend(jMessage);
			}

			jMessage.empty().html(data.error);

			if (typeof data.title != 'undefined' && data.title != '')
			{
				jObject.find(".modal-title").html(data.title);
			}
		},
		// Добавление новой заметки
		addNote: function() {
			// add ajax '_'
			var data = jQuery.getData({});

			jQuery.ajax({
				url: '/admin/index.php?ajaxCreateNote',
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(data) {
					$.createNote({'id': data.form_html});
				}
			});
		},
		// Создание заметки по id и value
		createNote: function(settings) {
			settings = $.extend({
				'id': null,
				'value': ''
			}, settings);

			var jClone = $('#default-user-note').clone(),
				noteId = settings.id;

			jClone
				.prop('id', noteId)
				.data('user-note-id', noteId);

			jClone.find('textarea').eq(0).val(settings.value);

			$("#user-notes").prepend(jClone.show());

			jClone.on('change', function(){
				var object = jQuery(this), timer = object.data('timer');

				if (timer){
					clearTimeout(timer);
				}

				jQuery(this).data('timer', setTimeout(function() {
						textarea = object.find('textarea').addClass('ajax');

						// add ajax '_'
						var data = jQuery.getData({});
						data['value'] = textarea.val();

						jQuery.ajax({
							context: textarea,
							url: '/admin/index.php?' + 'ajaxNote&action=save'
								+ '&entity_id=' + noteId,
							type: 'POST',
							data: data,
							dataType: 'json',
							success: function(){
								this.removeClass('ajax');
							}
						});
					}, 1000)
				);
			});
		},
		// Удаление заметки
		destroyNote: function(jDiv) {
			jQuery.ajax({
				url: '/admin/index.php?' + 'ajaxNote&action=delete'
					+ '&entity_id=' + jDiv.data('user-note-id'),
				type: 'get',
				dataType: 'json',
				success: function(){}
			});

			jDiv.remove();
		},
		soundSwitch: function(event) {
			$.ajax({
				url: event.data.path,
				type: "POST",
				data: {'sound_switch_status':1},
				dataType: 'json',
				error: function(){},
				success: function (result) {
					var jSoundSwitch = $("#sound-switch");

					result['answer'] == 0
						? jSoundSwitch.html('<i class="icon fa fa-volume-off"></i>')
						: jSoundSwitch.html('<i class="icon fa fa-volume-up"></i>');
				},
			});
		},
		bookmarksPrepare: function (){
			setInterval($.refreshBookmarksList, 120000);

			var jBookmarksListBox  = $('.navbar-account #bookmarksListBox');

			jBookmarksListBox.on({
				'click': function (event){
					event.stopPropagation();
				},
				'touchstart': function (event) {
					$(this).data({'isTouchStart': true});
				}
			});

			// Показ списка закладок
			$('.navbar li#bookmarks').on('shown.bs.dropdown', function (event){
				// Устанавливаем полосу прокрутки
				$.setBookmarksSlimScroll();
			});
		},
		refreshBookmarksCallback: function(resultData)
		{
			 // Есть новые дела
			if (typeof resultData['Bookmarks'] != 'undefined' && resultData['Bookmarks'].length)
			{
				var jEventUl = $('.navbar-account #bookmarksListBox .scroll-bookmarks > ul');

				$('li[id!="bookmark-0"]', jEventUl).remove();
				$('li[id="bookmark-0"]', jEventUl).hide();

				$.each(resultData['Bookmarks'], function(index, event) {
					// Добавляем закладку в список
					$.addBookmark(event, jEventUl);
				});
			}
		},
		refreshBookmarksList: function (){
			// add ajax '_'
			var data = jQuery.getData({}),
				jBookmarksListBox = $('.navbar-account #bookmarksListBox');

			var bLocalStorage = typeof localStorage !== 'undefined',
				bNeedsRequest = false;

			if (bLocalStorage)
			{
				var storage = localStorage.getItem('bookmarks'),
					storageObj = JSON.parse(storage);

				if (!storageObj || typeof storageObj['expired_in'] == 'undefined')
				{
					storageObj = {expired_in: 0};
				}

				if (Date.now() > storageObj['expired_in'])
				{
					storageObj['expired_in'] = Date.now() + 120000;

					bNeedsRequest = true;
				}
				else
				{
					$.refreshBookmarksCallback(storageObj);
				}
			}
			else
			{
				bNeedsRequest = true;
			}

			if (bNeedsRequest)
			{
				$.ajax({
					url: '/admin/index.php?ajaxWidgetLoad&moduleId=' + jBookmarksListBox.data('moduleId') + '&type=85',
					type: 'POST',
					data: data,
					dataType: 'json',
					error: function(){},
					success: [function(resultData){
						if (bLocalStorage)
						{
							resultData['expired_in'] = storageObj['expired_in'];
						}

						try {
							localStorage.setItem('bookmarks', JSON.stringify(resultData));
						} catch (e) {
							if (e == QUOTA_EXCEEDED_ERR) {
								console.log('localStorage: QUOTA_EXCEEDED_ERR');
							}
						}
					}, $.refreshBookmarksCallback]
				});
			}
		},
		setBookmarksSlimScroll: function (){
			// Сохраняем данные .slimScrollBar
			var jSlimScrollBar = $('#bookmarksListBox .slimScrollBar'),
				slimScrollBarData = !jSlimScrollBar.data() ? {'isMousedown': false} : jSlimScrollBar.data(),
				jScrollBookmarks = $('#bookmarksListBox .scroll-bookmarks');

			// Удаляем slimscroll
			if ($('#bookmarksListBox > .slimScrollDiv').length)
			{
				jScrollBookmarks.slimscroll({destroy: true});
				jScrollBookmarks.attr('style', '');
			}

			// Создаем slimscroll
			jScrollBookmarks.slimscroll({
				height: $('.navbar-account #bookmarksListBox .scroll-bookmarks > ul li[id != "bookmark-0"]').length ? '220px' : '55px',
				//height: 'auto',
				color: 'rgba(0, 0, 0, 0.3)',
				size: '5px',
				wheelStep: 5
			});

			//	Добавляем новому .slimScrollBar данные от удаленного
			jSlimScrollBar
				.data(slimScrollBarData)
				.on({
					'mousedown': function (){
						$(this).data('isMousedown', true);
					},

					'mouseenter': function () {
						$(this).css('width', '8px');
					},

					'mouseout': function () {
						!$(this).data('isMousedown') &&	$(this).css('width', '5px');
					}
				});
		},
		addBookmark: function (oBookmark, jBox){
			jBox.append(
				'<li id="bookmark-' + oBookmark['id'] + '">\
					<a href="' + (oBookmark['href'].length ? oBookmark['href'] : '#') + '" onclick="' + (oBookmark['onclick'].length ? oBookmark['onclick'] : '') + '">\
						<div class="clearfix notification-bookmark">\
							<div class="notification-icon">\
								<i class="' + oBookmark['ico'] + ' bg-darkorange white"></i>\
							</div>\
							<div class="notification-body">\
								<span class="title">' + oBookmark['name'] + '</span>\
								<span class="description">' + oBookmark['href'] + '</span>\
							</div>\
							<div class="notification-extra">\
								<i class="fa fa-times gray bookmark-delete" onclick="$.removeUserBookmark({title: \'' + oBookmark['remove-title'] +'\', submit: \'' + oBookmark['remove-submit'] + '\', cancel: \'' + oBookmark['remove-cancel'] + '\', bookmark_id: ' + oBookmark['id'] + '}); event.stopPropagation(); event.preventDefault();"></i>\
							</div>\
						</div>\
					</a>\
				</li>'
			);

			// Открыт выпадающий список закладок
			if ($('.navbar li#notification-bookmark').hasClass('open'))
			{
				// Если список дел был пуст, устанавливаем полосу прокрутки
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
				callback: function(name){
					if (name)
					{
						$.ajax({
							url: '/admin/user/index.php',
							type: "POST",
							data: {'add_bookmark': 1, 'name': name, 'path': settings.path, 'module_id': settings.module_id},
							dataType: 'json',
							error: function(){},
							success: function (result) {
								if (result.length)
								{
									$.removeLocalStorageItem('bookmarks');
									$.refreshBookmarksList();

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
				if(e.which == 13) {
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
				callback: function (result) {
					if (result)
					{
						$.ajax({
							url: '/admin/user/index.php',
							type: "POST",
							data: {'remove_bookmark': 1, 'bookmark_id': settings.bookmark_id},
							dataType: 'json',
							error: function(){},
							success: function (result) {
								if (result.length && result == 'OK')
								{
									// $('li#bookmark-' + settings.bookmark_id).remove();

									$.removeLocalStorageItem('bookmarks');
									$.refreshBookmarksList();
								}
							}
						});
					}
				}
			});

			$('.delete-bookmark-form').on('keypress', function(e) {
				if(e.which == 13) {
					$('.delete-bookmark-btn').trigger('click');
				}
			});
		},
		removeLocalStorageItem: function(name) {
			if (typeof localStorage !== 'undefined')
			{
				localStorage.removeItem(name);
			}
		},
		toggleWarehouses: function() {
			$(".shop-item-warehouses-list .row:has(input[value ^= 0])").toggleClass('hidden');
		},
		insertSeoTemplate: function(el, text) {
			if (el == undefined) { return; }
			el.insertAtCaret(text);
		},
		filterToggleField: function(object)
		{
			var filterId = object.data('filter-field-id'),
				filterFormGroup = $('#' + filterId);

			filterFormGroup
				// Hide/show filter value
				.toggle()
				// Clear filter value
				.find("input,select,textarea").val('');

			object.find('i').toggleClass('fa-check');
		},
		toggleFilter: function() {
			$('.topFilter').toggle();
			$('tr.admin_table_filter').toggleClass('disabled');
		},
		changeFilterStatus: function(settings) {
			$.ajax({
				url: settings.path,
				data: {'_': Math.round(new Date().getTime()), changeFilterStatus: true, show: settings.show},
				dataType: 'json',
				type: 'POST'
			});
		},
		changeFilterField: function(settings) {

			var li = $(settings.context);

			$.filterToggleField(li);

			//path, filter, field, show
			$.ajax({
				url: settings.path,
				data: {
					'_': Math.round(new Date().getTime()),
					changeFilterField: true,
					tab: settings.tab,
					field: settings.field,
					show: +li.find('i').hasClass('fa-check')
				},
				dataType: 'json',
				type: 'POST'
			});
		},
		filterSaveAs: function(caption, object, additionalParams) {

			bootbox.prompt(caption, function (result) {
				if (result !== null) {

					$.adminSendForm({
						buttonObject: object,
						additionalParams: additionalParams,
						post: {
							'hostcms[filterId]': $('#filterTabs li.active').data('filter-id'),
							filterCaption: result,
							saveFilterAs: true
						}
					});
				}
			});
		},
		filterSave: function(object) {

			$.loadingScreen('show');

			var FormNode = object.closest('form'),
				data = { saveFilter: true, filterId: FormNode.data('filter-id') },
				path = FormNode.attr('action');

			FormNode.ajaxSubmit({
				data: data,
				url: path,
				type: 'POST',
				dataType: 'json',
				cache: false,
				success: function(data, status, jqXHR) {
					$.loadingScreen('hide');
				}
			});
		},
		filterDelete: function(object) {

			$.loadingScreen('show');

			var FormNode = object.closest('form'),
				filterId = FormNode.data('filter-id'),
				data = { deleteFilter: true, filterId: filterId },
				path = FormNode.attr('action');

			FormNode.ajaxSubmit({
				data: data,
				//context: jQuery('#'+settings.windowId),
				url: path,
				type: 'POST',
				dataType: 'json',
				cache: false,
				success: function(data, status, jqXHR) {
					//alert(data.toSource());
					$.loadingScreen('hide');
				}
			});

			$('#filter-li-' + filterId).prev().find('a').tab('show');
			$('#filter-' + filterId + ', #filter-li-' + filterId).remove();

		},
		sortableKanban: function(path) {
			$('.kanban-board .kanban-list').sortable({
				items: "> li",
				connectWith: '.kanban-board .kanban-list',
				placeholder: 'placeholder',
				handle: ".drag-handle",
				// containment: '.kanban-board',
				receive: function (event, ui) {
					$.ajax({
						data: { id: ui.item[0].id, target_status_id: this.id },
						type: "POST",
						url: path,
					});
				}
			}).disableSelection();
		},
		/* -- CHAT -- */
		chatGetUsersList: function(event)
		{
			// add ajax '_'
			var data = $.getData({});

			$.ajax({
				context: event.data.context,
				url: event.data.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(data){

					// Delete users
					$(".contacts-list li.hidden").nextAll().remove();

					$.each(data, function(i, object) {
						// User name
						var name = object.firstName != '' ? object.firstName + " " + object.lastName : object.login,
							// User status
							status = object.online == 1 ?  'online' : 'offline ' + object.lastActivity,
							jClone = $(".contact").eq(0).clone();

						jClone
							.data("user-id", object.id)
							.attr('id', 'chat-user-id-' + object.id);

						// Delete old status class
						var oldClass = jClone.find(".contact-status div").eq(0).attr('class');

						jClone.find(".contact-name").text(name);

						if (object.count_unread > 0)
						{
							jClone.find(".contact-name").addChatBadge(object.count_unread);
						}

						jClone.find(".contact-status div").eq(0).removeClass(oldClass).addClass(status).attr("data-user-id", object.id);
						jClone.find(".contact-status div").eq(1).text(status);
						jClone.find(".contact-avatar img").attr({src: object.avatar});
						jClone.find(".last-chat-time").text(object.lastChatTime);

						$(".contacts-list").append(jClone.removeClass("hidden").show());
					});
				}
			});
		},
		modalWindow: function(settings)
		{
			settings = jQuery.extend({
				title: '',
				message: '',
				className: ''
			}, settings);

			var dialog = bootbox.dialog({
				message: settings.message,
				title: settings.title,
				className: settings.className
			}),
			modalBody = dialog.find('.modal-body'),
			content = dialog.find('.modal-body .bootbox-body div');

			/*windowId = content.attr('id');
			dialog.prop('id', windowId);
			content.removeProp('id');*/

			if (typeof settings.width != 'undefined')
			{
				dialog.find('.modal-dialog').width(settings.width);
			}

			if (typeof settings.height != 'undefined')
			{
				modalBody.height(settings.height);
			}
		},
		chatClearMessagesList: function()
		{
			// Delete messages
			$(".chatbar-messages .messages-list li:not(.hidden)").remove();
			$(".chatbar-messages #messages-none").addClass("hidden");
			$("#unread_messages").remove();
			$(".chatbar-messages .messages-list").data("countNewMessages", 0);
		},
		chatGetUserMessages: function (event)
		{
			// add ajax '_'
			var data = $.getData({});
			data['user-id'] = $(this).data('user-id');

			$.ajax({
				url: event.data.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: [$.chatClearMessagesList, $.chatGetUserMessagesCallback]
			});
		},
		chatGetUserMessagesCallback: function(result)
		{
			// Hide contact list
			$('#chatbar .chatbar-contacts').css("display","none");

			// Show messages
			$('#chatbar .chatbar-messages').css("display","block");

			var recipientUserInfo = result['recipient-user-info'],
				userInfo = result['user-info'],
				recipientName = recipientUserInfo.firstName != ''
					? recipientUserInfo.firstName + " " + recipientUserInfo.lastName
					: recipientUserInfo.login,
				status = recipientUserInfo.online == 1
					? 'online'
					: 'offline ' + recipientUserInfo.lastActivity,
				// Delete old status class
				oldClass = $(".messages-contact .contact-status div").eq(0).attr('class'),
				jMessagesList = $(".chatbar-messages .messages-list")
				.data({'recipientUserId': recipientUserInfo.id, 'countNewMessages': 0});

			$(".messages-contact").data("recipientUserId", recipientUserInfo.id);
			$(".send-message textarea").val('');

			$(".messages-contact .contact-name").text(recipientName);
			$(".messages-contact .contact-status div").eq(0).removeClass(oldClass).addClass(status).attr("data-user-id", recipientUserInfo.id);
			$(".messages-contact .contact-status div").eq(1).text(status);
			$(".messages-contact .contact-avatar img").attr({src: recipientUserInfo.avatar});
			$(".messages-contact .last-chat-time").text(recipientUserInfo.lastChatTime);

			if (result['messages'])
			{
				$.each(result['messages'], function(i, object) {
					$.addChatMessage(recipientUserInfo, userInfo, object, 0);
				});

				// ID верхнего (более раннего) сообщения в списке
				var firstMessage = result['messages'].length - 1;
				jMessagesList.data('firstMessageId', result['messages'][firstMessage]['id']);

				//ID нижнего (более позднего) сообщения в списке
				jMessagesList.data('lastMessageId', result['messages'][0]['id']);

				// Scroll
				$.chatMessagesListScrollDown();

				if (result['count_unread'])
				{
					// Непрочитанные сообщения
					jMessagesList.before('<div id="unread_messages" class="text-align-center">' + result['count_unread_message'] + ' <i class="fa fa-caret-up margin-left-5"></i></div>');
				}

				$("li.message.unread", jMessagesList).each(function(){
					$.showChatMessageAsRead($(this));
				});

				jMessagesList.data('countNewMessages', 0);
			}
			else
			{
				$('#messages-none').removeClass('hidden');
			}

			// Запуск обновления списка сообщений
			$.refreshMessagesList(recipientUserInfo.id);
		},

		showChatMessageAsRead: function(chatMessageElement)
		{
			chatMessageElement
				.addClass('mark-read')
				.delay(1500)
				.toggleClass("unread", false, 2000, "easeOutSine")
				.queue(function () {
					$(this).removeClass("mark-read");
					$(this).dequeue();
				});
		},

		readChatMessage: function(chatMessageElement)
		{
			var jMessagesList = $('.chatbar-messages .messages-list'),
				path = '/admin/index.php?ajaxWidgetLoad&moduleId=' + jMessagesList.data('moduleId') + '&type=83',
				data = $.getData({});

			// Скрываем один маркер новых сообщений под списком и показываем другой внутри списка, перед новыми сообщениями
			$.showChatMessageAsRead(chatMessageElement);

			data['message-id'] = parseInt(chatMessageElement.prop("id").substr(1));

			$.ajax({
				url: path,
				type: "POST",
				data: data,
				dataType: 'json',
				error: function(){},
				success: function (result) {
					if (result['answer'][0])
					{
						jMessagesList.data('countNewMessages', jMessagesList.data('countNewMessages') - 1);

						if (jMessagesList.data('countNewMessages') > 0)
						{
							$(".chatbar-messages #new_messages span.count_new_messages").text(jMessagesList.data("countNewMessages"));
						}
						else
						{
							$(".chatbar-messages #new_messages").addClass('hidden')
						}
					}
				}
			})
		},

		addChatMessage: function(recipientUserInfo, userInfo, object, bDirectOrder) {
			if (recipientUserInfo.id != userInfo.id)
			{
				var jClone = $(".message.hidden").eq(0).clone(),
					jMessagesList = $(".chatbar-messages .messages-list"),
					recipientName = recipientUserInfo.firstName != ''
						? recipientUserInfo.firstName + " " + recipientUserInfo.lastName
						: recipientUserInfo.login,
					currentName = userInfo.firstName != ''
						? userInfo.firstName + " " + userInfo.lastName
						: userInfo.login;

				// Если написали нам - добавляем class="reply"
				object.user_id == recipientUserInfo.id ? jClone.addClass('reply') : '';

				// Добавляем ID сообщения из таблицы сообщений
				jClone.attr('id', 'm' + object.id);

				// Если написали нам - добавляем class="unread"
				if (object.user_id == recipientUserInfo.id && !object.read)
				{
					jClone.addClass("unread");

					// Количество новых сообщений для пользователя
					jMessagesList.data("countNewMessages", jMessagesList.data("countNewMessages") + 1);
				}

				jClone.find(".message-info div").eq(1).text(object.user_id != recipientUserInfo.id ? currentName : recipientName);
				jClone.find(".message-info div").eq(2).text(object.datetime);
				jClone.find(".message-body").html(object.text/*.replace(/\n/g, "<br />")*/);

				jClone.removeClass("hidden").show();

				object.user_id == recipientUserInfo.id && bDirectOrder
					? jMessagesList.append(jClone)
					: jMessagesList.prepend(jClone);
			}
		},

		setSlimScrollBarHeight: function (jList) {

			var //jMessagesList = $('.chatbar-messages .messages-list'),
				jSlimScrollBar = jList.next(".slimScrollBar"),
				minSlimScrollBarHeight = 30,
				barHeight = Math.max((jList.outerHeight() / jList[0].scrollHeight) * jList.outerHeight(), minSlimScrollBarHeight);

			jSlimScrollBar.css('height', barHeight);
		},

		chatMessagesListScrollDown: function() {
			var jMessagesList = $('.chatbar-messages .messages-list'),
				jSlimScrollBar = jMessagesList.next(".slimScrollBar");

			$.setSlimScrollBarHeight(jMessagesList);
			jMessagesList.scrollTop(jMessagesList[0].scrollHeight);

			jSlimScrollBar.css('top', jMessagesList.outerHeight() - jSlimScrollBar.outerHeight() + 'px');
		},
		chatSendMessage: function(event) {
			if (event.keyCode == 13 && !event.shiftKey)
			{
				// Перевод строки
				if(event.ctrlKey)
				{
					var $this = $(this);
					$this.val($this.val() + "\n");
					event.preventDefault();
				}
				else
				{
					var jMessagesList = $('.chatbar-messages .messages-list'),
						data = $.getData({}), // add ajax '_'
						jTextarea = $(".send-message textarea"),
						message = $.trim(jTextarea.val());


					if (message == '')
						return;


					data['message'] = message;
					data['recipient-user-id'] = $(".messages-contact").data('recipientUserId');

					var jClone = $(".message.hidden").clone(),
						messageBox = $(".message-body", jClone);

					messageBox.html(messageBox.text(message).html().replace(/\n/g, "<br />"));

					jMessagesList.append(jClone.removeClass("hidden").addClass("opacity").show());

					jTextarea.val('');

					$.ajax({
						url: event.data.path,
						data: data,
						dataType: 'json',
						type: 'POST',
						error: function(){},
						success: function(data){
							if (data['answer'] == "OK")
							{
								var userInfo = data['user-info'];

								// Current user name
								currentName = userInfo.firstName != '' ? userInfo.firstName + " " + userInfo.lastName : userInfo.login;

								// Hide message
								$(".chatbar-messages #messages-none").addClass("hidden");

								jClone.find(".message-info div").eq(1).text(currentName);
								jClone.find(".message-info div").eq(2).text(data['message'].datetime);

								// Clear opacity
								jClone.removeClass("opacity");
							}
						}
					});

					// Scroll
					$.chatMessagesListScrollDown();
				}
			}
		},
		// Подгрузка новых сообщений в чат
		uploadingMessagesList: function () {
			var jMessagesList = $('.chatbar-messages .messages-list'),
				firstMessageId = jMessagesList.data('firstMessageId'),
				module_id = jMessagesList.data('moduleId'),
				path = '/admin/index.php?ajaxWidgetLoad&moduleId=' + module_id + '&type=78&first_message_id=' + firstMessageId,
				ajaxData = $.getData({});

			ajaxData['user-id'] = jMessagesList.data('recipientUserId');

			jMessagesList.addClass("opacity");

			// Add spinner
			$("i.chatbar-message-spinner").removeClass("hidden");

			$.ajax({
				url: path,
				data: ajaxData,
				dataType: 'json',
				type: 'POST',
				abortOnRetry: 1,
				error: function(){},
				success: function(result){
					var jMessagesList = $(".chatbar-messages .messages-list");

					if (result['messages'])
					{
						var recipientUserInfo = result['recipient-user-info'],
							userInfo = result['user-info'],
							firstMessage = result['messages'].length - 1; // ID верхнего (более раннего) сообщения в списке

						$.each(result['messages'], function(i, object) {
							$.addChatMessage(recipientUserInfo, userInfo, object, 0);
						});

						jMessagesList.data('firstMessageId', result['messages'][firstMessage]['id']);

						if (result['count_unread'])
						{
							jMessagesList.prevAll("#unread_messages").html(result['count_unread_message'] + " <i class='fa fa-caret-up margin-left-5'></i>");
						}
						else
						{
							jMessagesList.prevAll("#unread_messages").remove();
						}

						$("li.message", jMessagesList).delay(3000).toggleClass("unread", false, 2000, "easeOutSine");

						// Меняем высоту полосы прокрутки
						$.setSlimScrollBarHeight(jMessagesList);
					}
					else
					{
						jMessagesList.data('disableUploadingMessagesList', 1);
					}

					jMessagesList.removeClass("opacity");

					// Spinner off
					$("i.chatbar-message-spinner").addClass("hidden");
				},
			});
		},
		refreshMessagesListCallback: function(result)
		{
			var jMessagesList = $('.chatbar-messages .messages-list');

			if (result['messages'])
			{
				$.each(result['messages'], function(i, object) {
					$.addChatMessage(result['recipient-user-info'], result['user-info'], object, 1);
				});

				// ID последнего сообщения в списке
				var lastMessage = result['messages'].length - 1;
				jMessagesList.data('lastMessageId', result['messages'][lastMessage]['id']);

				// Hide message
				$(".chatbar-messages #messages-none").addClass("hidden");

				// Последнее прочитанное сообщение находится выше области ввода сообщений, т.е. скрол находится в нижнем положении
				if ($(".chatbar-messages .send-message").offset().top > $("li.message:not(.unread):not(.hidden):last", jMessagesList).offset().top)
				{

					$("li.message.hidden ~ li.message.unread", jMessagesList).each(function(){
						$.showChatMessageAsRead($(this));
					});

					$.each(result['messages'], function(i, object) {

						path = '/admin/index.php?ajaxWidgetLoad&moduleId=' + jMessagesList.data('moduleId') + '&type=83',
						data = $.getData({});

						data['message-id'] = object.id;

						$.ajax({
							url: path,
							type: "POST",
							data: data,
							dataType: 'json',
							error: function(){},
							success: function (result) {
								if (result['answer'][0])
								{
									jMessagesList.data('countNewMessages', jMessagesList.data('countNewMessages') - 1);
									if (jMessagesList.data('countNewMessages') > 0)
									{
										$(".chatbar-messages #new_messages span.count_new_messages").text(jMessagesList.data("countNewMessages"));
									}
									else
									{
										$(".chatbar-messages #new_messages").addClass('hidden')
									}
								}
							}
						});
					});


					// Scroll
					$.chatMessagesListScrollDown();
				}
				else
				{
					var jDivNewMessages = $(".chatbar-messages #new_messages");
					$("span.count_new_messages", jDivNewMessages).text(jMessagesList.data("countNewMessages"));
					jDivNewMessages.removeClass("hidden");
				}
			}
		},
		refreshMessagesList: function(recipientUserId) {
			var refreshMessagesListIntervalId = setInterval(function () {

				var jMessagesList = $('.chatbar-messages .messages-list'),
					path = '/admin/index.php?ajaxWidgetLoad&moduleId=' + jMessagesList.data('moduleId') + '&type=81',
					data = $.getData({});

				data['last-message-id'] = jMessagesList.data('lastMessageId');
				data['recipient-user-id'] = recipientUserId;

				var bLocalStorage = typeof localStorage !== 'undefined',
					bNeedsRequest = false;

				if (bLocalStorage)
				{
					var storage = localStorage.getItem('chat_messages_list'),
						storageObj = JSON.parse(storage);

					!storage && (storageObj = {expired_in: 0});

					if (Date.now() > storageObj['expired_in'])
					{
						storageObj['expired_in'] = Date.now() + 10000;

						bNeedsRequest = true;
					}
					else
					{
						$.refreshMessagesListCallback(storageObj);
					}
				}
				else
				{
					bNeedsRequest = true;
				}

				if (bNeedsRequest)
				{
					$.ajax({
						url: path,
						type: "POST",
						data: data,
						dataType: 'json',
						abortOnRetry: 1,
						error: function(){},
						success: [function(result){
							if (bLocalStorage)
							{
								result['expired_in'] = storageObj['expired_in'];
							}

							try {
								localStorage.setItem('chat_messages_list', JSON.stringify(result));
							} catch (e) {
								if (e == QUOTA_EXCEEDED_ERR) {
									console.log('localStorage: QUOTA_EXCEEDED_ERR');
								}
							}
						}, $.refreshMessagesListCallback]
					});
				}
			}, 10000);

			$("#chatbar").data("refreshMessagesListIntervalId", refreshMessagesListIntervalId);
		},
		refreshChatCallback: function(data)
		{
			if (data["info"])
			{
				Notify('<img width="24px" height="24px" src="' + data["info"].avatar + '"><span style="padding-left:10px">' + data["info"].text + '</span>', 'bottom-left', '5000', 'blueberry', 'fa-comment-o', true, !!data["info"].sound);

				var user_id = data["info"]['user_id'],
					jContact = $('#chat-user-id-' + user_id + ' .contact-info .contact-name'),
					jBadge = $('span.badge', jContact);

				jContact.addChatBadge(jBadge.length ? + jBadge.text() + 1 : 1);
			}
			else
			{
				$("#chat-link .badge").addClass("hidden").text(data["count"]);
				$("#chat-link").removeClass("wave in");
			}

			if (data["count"] > 0)
			{
				$("#chat-link .badge").removeClass("hidden").text(data["count"]);
				$("#chat-link").addClass("wave in");
			}
		},
		refreshChat: function(settings) {
			setInterval(function () {
				// add ajax '_'
				var data = $.getData({});
					data['alert'] = 1;

				var bLocalStorage = typeof localStorage !== 'undefined',
					bNeedsRequest = false;

				if (bLocalStorage)
				{
					var storage = localStorage.getItem('chat'),
						storageObj = JSON.parse(storage);

					!storage && (storageObj = {expired_in: 0});

					if (Date.now() > storageObj['expired_in'])
					{
						storageObj['expired_in'] = Date.now() + 10000;

						bNeedsRequest = true;
					}
					else
					{
						$.refreshChatCallback(storageObj);
					}
				}
				else
				{
					bNeedsRequest = true;
				}

				if (bNeedsRequest)
				{
					$.ajax({
						url: settings.path,
						type: "POST",
						data: data,
						dataType: 'json',
						abortOnRetry: 1,
						error: function(){},
						success: [function(data){
							if (bLocalStorage)
							{
								data['expired_in'] = storageObj['expired_in'];
							}

							try {
								localStorage.setItem('chat', JSON.stringify(data));
							} catch (e) {
								if (e == QUOTA_EXCEEDED_ERR) {
									console.log('localStorage: QUOTA_EXCEEDED_ERR');
								}
							}
						}, $.refreshChatCallback]
					});
				}
			}, 10000);
		},
		refreshUserStatusesCallback: function(result)
		{
			$(".online[data-user-id], .offline[data-user-id]").each(function(){
				var $this = $(this),
					user_id = +$this.data("userId");

				if (result[user_id])
				{
					var status = result[user_id]['status'] == 1 ?  'online' : 'offline ' + result[user_id]['lastActivity'];

					$this.attr('class', status);
					$this.next('.status').text(status);

					// Обновление количества непрочитанных для каждого пользователя
					if (result[user_id]['count_unread'])
					{
						$('#chat-user-id-' + user_id + ' .contact-info .contact-name').addChatBadge(result[user_id]['count_unread']);
					}
				}
			});
		},
		refreshUserStatuses: function() {
			setInterval(function () {
				var jMessagesList = $('.chatbar-messages .messages-list'),
					path = '/admin/index.php?ajaxWidgetLoad&moduleId=' + jMessagesList.data('moduleId') + '&type=82',
					data = $.getData({});

				var bLocalStorage = typeof localStorage !== 'undefined',
					bNeedsRequest = false;

				if (bLocalStorage)
				{
					var storage = localStorage.getItem('chat_user_statuses'),
						storageObj = JSON.parse(storage);

					!storage && (storageObj = {expired_in: 0});

					if (Date.now() > storageObj['expired_in'])
					{
						storageObj['expired_in'] = Date.now() + 10000;

						bNeedsRequest = true;
					}
					else
					{
						$.refreshUserStatusesCallback(storageObj);
					}
				}
				else
				{
					bNeedsRequest = true;
				}

				if (bNeedsRequest)
				{
					$.ajax({
						url: path,
						type: "POST",
						data: data,
						dataType: 'json',
						abortOnRetry: 1,
						error: function(){},
						success: [function(result){
							if (bLocalStorage)
							{
								result['expired_in'] = storageObj['expired_in'];
							}

							try {
								localStorage.setItem('chat_user_statuses', JSON.stringify(result));
							} catch (e) {
								if (e == QUOTA_EXCEEDED_ERR) {
									console.log('localStorage: QUOTA_EXCEEDED_ERR');
								}
							}
						}, $.refreshUserStatusesCallback]
					});
				}
			}, 60000);
		},
		chatPrepare: function() {
			// Обновление статусов
			$.refreshUserStatuses();

			var position = readCookie("rtl-support") ? 'right' : 'left',
				jMessagesList = $('.chatbar-messages .messages-list'),
				messagesListSlimscrollOptions = {
					position: position,
					size: '4px',
					start: 'bottom',
					color: themeprimary,
					wheelStep: 1,
					//height: $(window).height() - 250,
					height: $(window).height() - $('body > .navbar').outerHeight() - $('#chatbar .messages-contact').outerHeight() - $('#chatbar .send-message').outerHeight(),
					alwaysVisible: true,
					disableFadeOut: true
				};

			jMessagesList.slimscroll(messagesListSlimscrollOptions);

			$('.chatbar-contacts .contacts-list').slimscroll({
				position: position,
				size: messagesListSlimscrollOptions.size,//'4px',
				color: themeprimary,
				//height: $(window).height() - 50,
				height: $(window).height() - $('body > .navbar').outerHeight()
			});

			$("#chat-link").click(function () {
				$('.page-chatbar').toggleClass('open');
				$("#chat-link").toggleClass('open');
			});

			$('.page-chatbar .chatbar-contacts .contact').on('click', function(e) {
				$('.page-chatbar .chatbar-contacts').hide();
				$('.page-chatbar .chatbar-messages').show();
			});

			$('.page-chatbar .chatbar-messages .back').on('click', function (e) {
				$('.page-chatbar .chatbar-contacts').show();
				$('.page-chatbar .chatbar-messages').hide();
				$('.chatbar-messages .messages-list').removeData('disableUploadingMessagesList');
				$.chatClearMessagesList();
			});

			// Отключение refreshMessagesList
			$("#chat-link, div.back").on('click', function() {
				$("#chatbar").data("refreshMessagesListIntervalId") && clearInterval($("#chatbar").data("refreshMessagesListIntervalId"))
			});

			function onWheel(event)
			{
				var jMessagesList = $('.chatbar-messages .messages-list'),
					slimScrollBar = $('.chatbar-messages .slimScrollBar'),
					maxTop = jMessagesList.outerHeight() - slimScrollBar.outerHeight(),
					delta = 0, newTopScroll = 0, percentScroll;

				if (event.wheelDelta)
				{
					delta = -event.wheelDelta / 120;
				}

				if (event.detail)
				{
					delta = event.detail / 3;
				}

				// Прокрутили вверх, уже находясь вверху
				if (delta < 0 && $(this).next(".slimScrollBar").length && $(this).next(".slimScrollBar").position().top == 0 && !jMessagesList.data('disableUploadingMessagesList'))
				{
					$.uploadingMessagesList();
					return;
				}

				// Прокрутили вниз, не находясь при этом в самом низу
				if (delta > 0 && (jMessagesList[0].scrollHeight > jMessagesList.scrollTop() + jMessagesList.outerHeight()))
				{
					delta = parseInt(slimScrollBar.css('top')) + delta * parseInt(messagesListSlimscrollOptions.wheelStep) / 100 * slimScrollBar.outerHeight();
					delta = Math.min(Math.max(delta, 0), maxTop);
					delta = Math.ceil(delta);

					percentScroll = delta / (jMessagesList.outerHeight() - slimScrollBar.outerHeight());
					newTopScroll = percentScroll * (jMessagesList[0].scrollHeight - jMessagesList.outerHeight());

					delta = newTopScroll - jMessagesList.scrollTop();

					// Список новых сообщений
					$("li.message.hidden ~ li.message.unread:not(.mark-read)", jMessagesList).each(function(index){
						var $this = $(this);

						// Показываем новое сообщение
						if ($(".chatbar-messages .send-message").offset().top > (($this.offset().top - delta + 30)) )
						{
							$.readChatMessage($this);
						}
					});
				}
			}

			if (jMessagesList[0])
			{
				if (jMessagesList[0].addEventListener)
				{
					jMessagesList[0].addEventListener('DOMMouseScroll', onWheel, false);
					jMessagesList[0].addEventListener('mousewheel', onWheel, false);
					jMessagesList[0].addEventListener('MozMousePixelScroll', onWheel, false);
				}
				else
				{
					jMessagesList[0].attachEvent("onmousewheel", onWheel);
				}
			}

			jMessagesList.on({
				'slimscroll': function (e, pos) {
					var jMessagesList = $('.chatbar-messages .messages-list');

					if (pos == 'top' && !jMessagesList.data('disableUploadingMessagesList'))
					{
						$.uploadingMessagesList();
					}

					// Достигли нижнего края чата - убираем маркер числа новых сообщений, сбрасываем счетчик новых сообщений
					if (pos == 'bottom')
					{
						!$(".chatbar-messages #new_messages").hasClass('hidden') && $(".chatbar-messages #new_messages").addClass('hidden');
					}
				},

				'touchstart': function (event) {

					$(this).data(
						{
							'isTouchStart': true,
							'touchPositionY': event.originalEvent.touches[0].pageY
						}
					);
				}
			});

			$('#chatbar .slimScrollBar').each(function() {

				$(this)
					.data('isMousedown', false)
					.mousedown(function () {
						$(this).data('isMousedown', true);
						$(this).css('width', '8px')
					})
					.mouseenter(function () {
						$(this).css('width', '8px')
					})
					.mouseout(function () {
						!$(this).data('isMousedown') &&	$(this).css('width', messagesListSlimscrollOptions.size);
					});
			});

			$(document).on({

				'mousemove': function () {
					var slimScrollBar = $('.chatbar-messages .slimScrollBar'),
						jMessagesList = $('.chatbar-messages .messages-list');

					if (slimScrollBar.data('isMousedown'))
					{
						//var deltaY = slimScrollBar.position().top - slimScrollBar.data('top');

						slimScrollBar.data('top', slimScrollBar.position().top);

						// Список новых сообщений
						$("li.message.hidden ~ li.message.unread:not(.mark-read)", jMessagesList).each(function(index){
							var $this = $(this);

							// Показываем новое сообщение
							if ($(".chatbar-messages .send-message").offset().top > ($this.offset().top + 30))
							{
								$.readChatMessage($this);
							}
						});
					}
				},

				'mouseup': function (event) {

					$('#chatbar .slimScrollBar').each(function() {

						var slimScrollBar = $(this);
						// Кнопка мыши была нажата, когда указатель мыши находился над полосой прокрутки
						if (slimScrollBar.data('isMousedown'))
						{
							slimScrollBar.data({'isMousedown': false, 'top': 0});

							// Указатель мыши находится вне полосы прокрутки
							if (event.target != slimScrollBar[0])
							{
								slimScrollBar.css('width', messagesListSlimscrollOptions.size);
							}
						}
					})
				},

				'touchend': function () {

					var jMessagesList = $('.chatbar-messages .messages-list');

					jMessagesList.data('isTouchStart') && jMessagesList.data('isTouchStart', false);
				},

				'touchmove': function (event) {

					var jMessagesList = $('.chatbar-messages .messages-list');
					if (jMessagesList.data('isTouchStart'))
					{
						var lastY = jMessagesList.data('touchPositionY'),
							currentY = event.originalEvent.touches[0].pageY;

						if (jMessagesList.scrollTop() == 0 && !jMessagesList.data('disableUploadingMessagesList'))
						{
							$.uploadingMessagesList();
						}

						// Пролистываем вверх
						if (currentY < lastY)
						{
							// Список новых сообщений
							$("li.message.hidden ~ li.message.unread:not(.mark-read)", jMessagesList).each(function(index){
								var $this = $(this);

								// Показываем новое сообщение
								if ($(".chatbar-messages .send-message").offset().top > ($this.offset().top + 30))
								{
									$.readChatMessage($this);
								}
							});
						}

						jMessagesList.data('touchPositionY', currentY);
					}
				},

				'scroll': function() {

					if (!$('#checkbox_fixednavbar').prop('checked'))
					{

						var documentScrollTop = $(document).scrollTop(),
							navbarHeight = $('body > div.navbar').outerHeight(),
							chatBar = $('div#chatbar'),
							deltaHeight = (documentScrollTop > navbarHeight ? 0 : navbarHeight - documentScrollTop),
							deltaY = parseInt(chatBar.css('top')) - deltaHeight,
							//sendMessageBlock = $('#chatbar .send-message'),

							// Полоса прокрутки списка контактов
							chatbarContactsSlimScrollDiv = $('div#chatbar .chatbar-contacts .slimScrollDiv'),
							// Список контактов
							contactsList = $('div#chatbar .chatbar-contacts .contacts-list'),

							// Полоса прокрутки списка сообщений
							chatbarMessagesSlimScrollDiv = $('div#chatbar .chatbar-messages .slimScrollDiv');
							// Список сообщений
							messagesList = $('div#chatbar .chatbar-messages .messages-list');

						if (deltaY)
						{
							chatBar.css({'top': deltaHeight + 'px', 'height': chatBar.height() + deltaY + 'px'});

							contactsList.css('height', parseInt(contactsList.css('height')) + deltaY + 'px');
							chatbarContactsSlimScrollDiv.css('height', chatbarContactsSlimScrollDiv.outerHeight() + deltaY + 'px');

							messagesList.css('height', parseInt(messagesList.css('height')) + deltaY + 'px');
							chatbarMessagesSlimScrollDiv.css('height', chatbarMessagesSlimScrollDiv.outerHeight() + deltaY + 'px');

							// Изменяем высоту полосы прокрутки списка контактов
							$.setSlimScrollBarHeight(contactsList);

							// Изменяем высоту полосы прокрутки списка сообщений
							$.setSlimScrollBarHeight(messagesList);
						}
					}
				}
			});

			$(window).on({
				'resize': function(event) {

					var documentScrollTop = $(document).scrollTop(),
						navbarHeight = $('body > div.navbar').outerHeight(),
						chatBar = $('div#chatbar'),

						// Меняем позицию чата в зависимости от того зафиксирована полоса навигации или нет
						deltaScrollHeight = $('#checkbox_fixednavbar').prop('checked')
							? navbarHeight
							: ( documentScrollTop > navbarHeight ? 0 : navbarHeight - documentScrollTop),

						chatbarContactsSlimScrollDiv = $('div#chatbar .chatbar-contacts .slimScrollDiv'),
						contactsList = $('div#chatbar .chatbar-contacts .contacts-list'),

						chatbarMessagesSlimScrollDiv = $('div#chatbar .chatbar-messages .slimScrollDiv'),
						messagesList = $('div#chatbar .chatbar-messages .messages-list'),
						sendMessageBlock = $('#chatbar .send-message'),

						chatbarMessagesDeltaHeight = deltaScrollHeight + $('#chatbar .messages-contact').outerHeight() + sendMessageBlock.outerHeight();

					chatBar.css({'height': $(this).height() - deltaScrollHeight + 'px', 'top': deltaScrollHeight + 'px'});

					chatbarContactsSlimScrollDiv.css('height', $(this).height() - deltaScrollHeight + 'px');
					contactsList.css('height', chatbarContactsSlimScrollDiv.outerHeight() + 'px');

					chatbarMessagesSlimScrollDiv.css('height', $(this).height() - chatbarMessagesDeltaHeight + 'px');
					messagesList.css('height', chatbarMessagesSlimScrollDiv.outerHeight() + 'px');

					// Изменяем высоту полосы прокрутки списка контактов
					$.setSlimScrollBarHeight(contactsList);
					// Изменяем высоту полосы прокрутки списка сообщений
					$.setSlimScrollBarHeight(messagesList);
				}
			});

			// Обработчик клика на чекбосе-фиксаторе полосы навигации
			function clickFixedNavbarHandler() {

				var documentScrollTop = $(document).scrollTop(),
					navbarHeight = $('body > div.navbar').outerHeight(),
					chatBar = $('div#chatbar'),

					// Меняем позицию чата в зависимости от того зафиксирована полоса навигации или нет
					deltaScrollHeight = $('#checkbox_fixednavbar').prop('checked')
						? navbarHeight
						: ( documentScrollTop > navbarHeight ? 0 : navbarHeight - documentScrollTop),

					slimScrollDiv = $('div#chatbar .chatbar-messages .slimScrollDiv'),
					messagesList = $('div#chatbar .chatbar-messages .messages-list'),
					sendMessageBlock = $('#chatbar .send-message'),

					deltaHeight = deltaScrollHeight + $('#chatbar .messages-contact').outerHeight() + sendMessageBlock.outerHeight();

					chatBar.css({'height': $(window).height() - deltaScrollHeight + 'px', 'top': deltaScrollHeight + 'px'});

					slimScrollDiv.css('height', $(window).height() - deltaHeight + 'px');
					messagesList.css('height', slimScrollDiv.outerHeight() + 'px');

				$.setSlimScrollBarHeight(messagesList);
			}

			$('#checkbox_fixednavbar').on('click', clickFixedNavbarHandler);
				/*
				.on('click', function () {

					$(this).prop('checked') && !$('#checkbox_fixednavbar').prop('checked') && clickFixedNavbarHandler();
				});*/
		},
		/* -- /CHAT -- */
		loadSiteList: function() {
			// add ajax '_'
			var data = $.getData({});

			$.ajax({
				url: '/admin/index.php?ajaxWidgetLoad&moduleId=0&type=10',
				type: "POST",
				data: data,
				dataType: 'json',
				error: function(){},
				success: function (data) {
					//update count site badge
					$('#sitesListIcon span.badge').text(data['count']);

					// update site list
					$('#sitesListBox').html(data['content']);

					$('.scroll-sites').slimscroll({
					 // height: '215px',
					  height: 'auto',
					  color: 'rgba(0,0,0,0.3)',
					  size: '5px',
					  wheelStep: 2
					});
				}
			});
		},
		loadNavSidebarMenu: function(data) {

			data.loadNavSidebarMenu = 1;

			$.ajax({
				url: '/admin/user/index.php',
				type: "POST",
				data: data,
				dataType: 'json',
				error: function(){},
				success: function (answer) {
					$('.nav.sidebar-menu').html(answer.form_html);

					if (typeof data.moduleName != 'undefined')
					{
						var menuDropdown = $('li#menu-' + data.moduleName).parents('ul').prev();
						menuDropdown.effect('pulsate', {times: 3}, 3000);
					}
				}
			});
		},
		changeWallpaper: function(img) {
			var wallpaper_id =  $(img).data('id'),
				original = $(img).data('original-path');

			createCookie("wallpaper-id", wallpaper_id, 365);

			$.ajax({
				url: '/admin/user/index.php',
				/*url: '/admin/index.php?' + 'userSettings&moduleId=' + $(img).data('moduleId')
					+ '&type=95'
					+ '&width=' + ui.helper.width() + '&height=' + ui.helper.height() + '&active=' + (event.type == 'hostcmswindowbeforeclose' ? 0 : 1),*/
				type: 'POST',
				data: {'wallpaper-id':wallpaper_id},
				dataType: 'json',
				error: function(){},
				success: function (object) {
					$('head').append('<style>body.hostcms-bootstrap1:before{ background-image: url(' + original + ') }</style>');
				},
			});
		},
		refreshClock: function() {
			setInterval( function() {
				// Создаем объект newDate() и показывает минуты
				var minutes = new Date().getMinutes();
				// Добавляем ноль в начало цифры, которые до 10
				$(".clock #min").html(( minutes < 10 ? "0" : "" ) + minutes);
			}, 500);

			setInterval( function() {
				// Создаем объект newDate() и показывает часы
				var hours = new Date().getHours();
				// Добавляем ноль в начало цифры, которые до 10
				$(".clock #hours").html(( hours < 10 ? "0" : "" ) + hours);
			}, 500);
		},
		generatePassword: function() {
			var jFirstPassword = $("[name = 'password_first']"),
				jSecondPassword = $("[name = 'password_second']");

			$.ajax({
				url: '/admin/user/index.php',
				type: 'POST',
				data: {'generate-password':1},
				dataType: 'json',
				error: function(){},
				success: function (answer) {

					jFirstPassword
						.prop('type', 'text')
						.val(answer.password)
						.focus();

					jSecondPassword
						.prop('type', 'text')
						.val(answer.password)
						.focus();

					jFirstPassword.focus();
				}
			});
		},
		eventsPrepare: function (){
			setInterval($.refreshEventsList, 10000);

			var jEventsListBox  = $('.navbar-account #notificationsClockListBox');

			jEventsListBox.on({
				'click': function (event){
					event.stopPropagation();
				},

				'touchstart': function (event) {
					$(this).data({'isTouchStart': true});
				}
			});

			// Показ списка дел
			$('.navbar li#notifications-clock').on('shown.bs.dropdown', function (event){
				// Устанавливаем полосу прокрутки
				$.setEventsSlimScroll();
			});
		},
		refreshEventsCallback: function(resultData)
		{
			 // Есть новые дела
			if (typeof resultData['newEvents'] != 'undefined' && resultData['newEvents'].length)
			{
				var jEventUl = $('.navbar-account #notificationsClockListBox .scroll-notifications-clock > ul');

				$('li[id!="event-0"]', jEventUl).remove();
				$('li[id="event-0"]', jEventUl).hide();

				$.each(resultData['newEvents'], function( index, event ){
					// Добавляем дело в список
					$.addEvent(event, jEventUl);
				});
			}
		},
		refreshEventsList: function (){
			// add ajax '_'
			var data = jQuery.getData({}),
				jNotificationsClockListBox = $('.navbar-account #notificationsClockListBox');

			data['currentUserId'] = jNotificationsClockListBox.data('currentUserId');

			var bLocalStorage = typeof localStorage !== 'undefined',
				bNeedsRequest = false;

			if (bLocalStorage)
			{
				var storage = localStorage.getItem('events'),
					storageObj = JSON.parse(storage);

				if (!storageObj || typeof storageObj['expired_in'] == 'undefined')
				{
					storageObj = {expired_in: 0};
				}

				if (Date.now() > storageObj['expired_in'])
				{
					storageObj['expired_in'] = Date.now() + 10000;

					bNeedsRequest = true;
				}
				else
				{
					$.refreshEventsCallback(storageObj);
				}
			}
			else
			{
				bNeedsRequest = true;
			}

			if (bNeedsRequest)
			{
				$.ajax({
					url: '/admin/index.php?ajaxWidgetLoad&moduleId=' + jNotificationsClockListBox.data('moduleId') + '&type=4',
					type: 'POST',
					data: data,
					dataType: 'json',
					error: function(){},
					success: [function(resultData){
						if (bLocalStorage)
						{
							resultData['expired_in'] = storageObj['expired_in'];
						}

						try {
							localStorage.setItem('events', JSON.stringify(resultData));
						} catch (e) {
							if (e == QUOTA_EXCEEDED_ERR) {
								console.log('localStorage: QUOTA_EXCEEDED_ERR');
							}
						}
					}, $.refreshEventsCallback]
				});
			}
		},
		// Добавление полосы прокрутки для списка дел
		setEventsSlimScroll: function (){
			// Сохраняем данные .slimScrollBar
			var jSlimScrollBar = $('#notificationsClockListBox .slimScrollBar'),
				slimScrollBarData = !jSlimScrollBar.data() ? {'isMousedown': false} : jSlimScrollBar.data(),
				jScrollNotificationClock = $('#notificationsClockListBox .scroll-notifications-clock');

			// Удаляем slimscroll
			if ($('#notificationsClockListBox > .slimScrollDiv').length)
			{
				jScrollNotificationClock.slimscroll({destroy: true});
				jScrollNotificationClock.attr('style', '');
			}

			// Создаем slimscroll
			jScrollNotificationClock.slimscroll({
				height: $('.navbar-account #notificationsClockListBox .scroll-notifications-clock > ul li[id != "notification-0"]').length ? '220px' : '55px',
				//height: 'auto',
				color: 'rgba(0, 0, 0, 0.3)',
				size: '5px',
				wheelStep: 5
			});

			//	Добавляем новому .slimScrollBar данные от удаленного
			jSlimScrollBar
				.data(slimScrollBarData)
				.on({
					'mousedown': function (){
						$(this).data('isMousedown', true);
					},

					'mouseenter': function () {
						$(this).css('width', '8px');
					},

					'mouseout': function () {
						!$(this).data('isMousedown') &&	$(this).css('width', '5px');
					}
				});
		},
		addEvent: function (oEvent, jBox){
			// var jBox = jBox || $('.navbar-account #notificationsClockListBox .scroll-notifications-clock > ul');

			jBox.append(
				'<li id="event-' + oEvent['id'] + '">\
					<a href="' + (oEvent['href'].length ? oEvent['href'] : '#') + '" onclick="' + (oEvent['onclick'].length ? oEvent['onclick'] : '') + '">\
						<div class="clearfix notification-clock">\
							<div class="notification-icon">\
								<i class="' + oEvent['icon'] + ' fa-fw white" style="background-color: ' + oEvent['background-color'] + '"></i>\
							</div>\
							<div class="notification-body">\
								<span class="title">' + oEvent['name'] + '</span>\
								<span class="description"><i class="fa fa-clock-o"></i> ' + oEvent['start'] + ' — <span class="notification-time">' + oEvent['finish'] + '</span>\
							</div>\
						</div>\
					</a>\
				</li>'
			);

			// Открыт выпадающий список дел
			if ($('.navbar li#notifications-clock').hasClass('open'))
			{
				 // Если список дел был пуст, устанавливаем полосу прокрутки
				!$('li', jBox).length && $.setEventsSlimScroll();
			}
		},
		notificationsPrepare: function (){

			setInterval($.refreshNotificationsList, 10000);

			var jNotificationsListBox  = $('.navbar-account #notificationsListBox');

			jNotificationsListBox.on({
				'click': function (event){
					event.stopPropagation();
				},
				'touchstart': function (event) {
					$(this).data({'isTouchStart': true});
				}
			});

			// Показ списка уведомлений
			$('.navbar li#notifications').on('shown.bs.dropdown', function (event){

				// Устанавливаем полосу прокрутки
				$.setNotificationsSlimScroll();

				// Устанавливаем соответствующие уведомления прочитанными
				$.readNotifications();

				var jInputSearch = $('#notification-search', this),
					jButton = jInputSearch.nextAll('.glyphicon-remove'),

					// Кнопка очистки списка уведомлений (кнопка корзины)
					clearListNotificationsButton = $('.navbar-account #notificationsListBox .footer .fa-trash-o'),

					// Поле фильтрации списка уведомлений
					filterListNotificationsField = $('.navbar-account #notificationsListBox .footer #notification-search');

				// Устанавливаем видимость кнопки очистки поля поиска (фильтрации) уведомлений
				setVisibilityInputCleaningButton(jInputSearch, jButton);

				if ($('#notificationsListBox .scroll-notifications li[id != "notification-0"]').length)
				{
					$('.navbar-account #notificationsListBox .footer').show();

					/*filterListNotificationsField.show();
					filterListNotificationsField.next('.glyphicon-search').show();
					clearListNotificationsButton.show();*/
				}
				else
				{
					$('.navbar-account #notificationsListBox .footer').hide();

					/*filterListNotificationsField.hide();
					filterListNotificationsField.next('.glyphicon-search').hide();
					clearListNotificationsButton.hide();*/
				}

			});

			// Обработчик нажатия кнопки очистки списка уведомлений
			jNotificationsListBox.find('.footer .fa-trash-o').on('click', $.clearNotifications);

			$(document).on({
				'mousemove': function (){
					var jSlimScrollBar = $('#notificationsListBox .slimScrollBar');

					// Была нажата кнопка на полосе прокрутки
					if (jSlimScrollBar.data('isMousedown'))
					{
						// Делаем соответствующие уведомления прочитанными
						$.readNotifications();
					}
				},
				'mouseup': function (){
					var jSlimScrollBar = $('#notificationsListBox .slimScrollBar');

					// Была нажата кнопка на полосе прокрутки
					if (jSlimScrollBar.data('isMousedown'))
					{
						// Делаем соответствующие уведомления прочитанными
						$.readNotifications();
						jSlimScrollBar.data({'isMousedown': false});
					}
				},
				'touchend': function () {
					var jNotificationsListBox  = $('.navbar-account #notificationsListBox');

					if (jNotificationsListBox.data('isTouchStart'))
					{
						jNotificationsListBox.data('isTouchStart', false);
					}
				},
				'touchmove': function (event) {
					if ($('.navbar-account #notificationsListBox').data('isTouchStart'))
					{
						// Делаем соответствующие уведомления прочитанными
						$.readNotifications();
					}
				}
			});

			var jNotificationsList = $('.navbar-account #notificationsListBox .scroll-notifications');

			// Функция-обработчик прокрутки списка уведомлений
			function onWheel(event)
			{
				var //jMessagesList = $('.chatbar-messages .messages-list'),
					jNotificationsList = $('#notificationsListBox .scroll-notifications'),
					//slimScrollBar = $('.chatbar-messages .slimScrollBar'),
					slimScrollBar = $('#notificationsListBox .slimScrollBar'),
					maxTop = jNotificationsList.outerHeight() - slimScrollBar.outerHeight(),
					wheelDelta = 0, newTopScroll = 0, percentScroll;

				if (event.wheelDelta)
				{
					wheelDelta = -event.wheelDelta / 120;
				}

				if (event.detail)
				{
					wheelDelta = event.detail / 3;
				}

				wheelStep = 20;

				wheelDelta = parseInt(slimScrollBar.css('top')) + wheelDelta * wheelStep / 100 * slimScrollBar.outerHeight();
				wheelDelta = Math.min(Math.max(wheelDelta, 0), maxTop);
				wheelDelta = Math.ceil(wheelDelta);

				percentScroll = wheelDelta / (jNotificationsList.outerHeight() - slimScrollBar.outerHeight());
				newTopScroll = percentScroll * (jNotificationsList[0].scrollHeight - jNotificationsList.outerHeight());

				wheelDelta = newTopScroll - jNotificationsList.scrollTop();

				$.readNotifications(wheelDelta);
			};

			if (jNotificationsList[0].addEventListener)
			{
				jNotificationsList[0].addEventListener('DOMMouseScroll', onWheel, false);
				jNotificationsList[0].addEventListener('mousewheel', onWheel, false);
				jNotificationsList[0].addEventListener('MozMousePixelScroll', onWheel, false);
			}
			else
			{
				jNotificationsList[0].attachEvent("onmousewheel", onWheel);
			};

			// Установка показа/скрытия кнопки очистки поля
			function setVisibilityInputCleaningButton(jInput, jButton)
			{
				if (jInput.val() == '')
				{
					// !jButton.hasClass('hide') && jButton.addClass('hide');
					jButton.addClass('hide');
				}
				else
				{
					jButton.removeClass('hide');
				}
			}

			// Обработчик нажатия в поле поиска (фильтрации) уведомлений
			$('.navbar-account #notificationsListBox #notification-search').on('keyup', function (event){

				var jInputSearch = $(this),
					// Кнопка очистки списка уведомлений (кнопка корзины)
					clearListNotificationsButton = $('.navbar-account #notificationsListBox .footer .fa-trash-o');

				// Нажали Esc - очищаем поле фильтрации
				event.keyCode == 27 && jInputSearch.val('');

				// Скрываем кнопку очистки списка уведомлений при фильтрации
				if (jInputSearch.val())
				{
					clearListNotificationsButton.hide();
				}
				else
				{
					clearListNotificationsButton.show();
				}

				setVisibilityInputCleaningButton(jInputSearch, jInputSearch.nextAll('.glyphicon-remove'));

				$.filterNotifications(jInputSearch);
			})

			$('.navbar-account #notificationsListBox .glyphicon-remove')
				.on({
					'click': function (){
						$.filterNotifications($(this).prevAll('#notification-search').val(''));
						$(this).addClass('hide');
						$('.navbar-account #notificationsListBox .footer .fa-trash-o').show();
					},
					'mouseover': function (){
						$(this).toggleClass('green palegreen');
					},
					'mouseout': function (){
						$(this).toggleClass('green palegreen');
					}
				});
		},

		// Добавление полосы прокрутки для списка уведомлений
		setNotificationsSlimScroll: function (){

			// Сохраняем данные .slimScrollBar
			var jSlimScrollBar = $('#notificationsListBox .slimScrollBar'),
				slimScrollBarData = !jSlimScrollBar.data() ? {'isMousedown': false} : jSlimScrollBar.data();

			// console.log('jSlimScrollBar = ', jSlimScrollBar);

			// Удаляем slimscroll
			if ($('#notificationsListBox > .slimScrollDiv').length)
			{
				$('#notificationsListBox .scroll-notifications').slimscroll({destroy: true});
				$('#notificationsListBox .scroll-notifications').attr('style', '');
			}

			// Создаем slimscroll
			$('#notificationsListBox .scroll-notifications').slimscroll({
				height: $('.navbar-account #notificationsListBox .scroll-notifications > ul li[id != "notification-0"]').length ? '220px' : '55px',
				//height: 'auto',
				color: 'rgba(0, 0, 0, 0.3)',
				size: '5px'
			});

			// Добавляем новому .slimScrollBar данные от удаленного
			//jSlimScrollBar
			$('#notificationsListBox .slimScrollBar')
				.data(slimScrollBarData)
				.on({
					'mousedown': function (){
						$(this).data('isMousedown', true);
					},
					'mouseenter': function () {
						$(this).css('width', '8px');
					},
					'mouseout': function () {
						!$(this).data('isMousedown') &&	$(this).css('width', '5px');
					}
				});
		},
		// Определение вхождения элемента (element) в область другого элемента (box)
		elementInBox: function (element, box, wheelDelta, delta){
			// wheelDelta - величина прокрутки slimscroll'а
			// delta - минимальный размер вхождения element в область элемента box
			var delta = delta || 10,
				wheelDelta = wheelDelta || 0,
				boxTop = box.offset().top + parseInt(box.css('margin-top')) + parseInt(box.css('padding-top')),
				boxBottom = boxTop + box.height(),
				elementTop = element.offset().top + parseInt(element.css('margin-top')) + parseInt(element.css('padding-top')) - wheelDelta,
				elementBottom = elementTop + element.height();

			return elementTop >= boxTop && elementTop <= (boxBottom - delta) || (elementBottom >= boxTop + delta) && elementBottom <= boxBottom;
		},

		// Добавление уведомления
		addNotification: function (oNotification, jBox, soundEnabled){

			var jBox = jBox || $('.navbar-account #notificationsListBox .scroll-notifications > ul'),
				/*showAlertNotification = showAlertNotification === undefined ? true : showAlertNotification,*/
				notificationExtra = '';

			if (oNotification['extra'].length)
			{
				var jNotificationExtra = $('<div class="notification-extra">');

				oNotification['extra'].forEach(function(item) {
					jNotificationExtra.append('<i class="fa ' + item + ' themeprimary"></i>');
				})

				oNotification['extra']['description'].length && jNotificationExtra.append('<span class="description">' + oNotification['extra']['description'] + '</span>')

				notificationExtra = jNotificationExtra.html();
			}

			jBox.prepend(
				'<li id="notification-' + oNotification['id'] + '" class="' + (oNotification['read'] == 0 ? "unread" : "") + '">\
					<a href="' + (oNotification['href'].length ? oNotification['href'] : '#') + '" onclick="' + (oNotification['onclick'].length ? oNotification['onclick'] : '') + '">\
						<div class="clearfix">\
							<div class="notification-icon">\
								<i class="' + oNotification['icon']['ico'] + ' ' + oNotification['icon']['background-color'] + ' ' + oNotification['icon']['color'] + '"></i>\
							</div>\
							<div class="notification-body">\
								<span class="title">' + oNotification['title'] + '</span>\
								<span class="description"></span>\
							</div>\
							' + notificationExtra +
						'</div>\
					</a>\
				</li>')
				.find('li#notification-' + oNotification['id'] + ' span.description').html((oNotification['description'].length ? (oNotification['description'] + '<br/>') : '') /* oNotification['datetime']*/ );

			// Показываем всплывающее непрочитанное уведомление
			!parseInt(oNotification['read']) && Notify(oNotification['title'], 'bottom-left', '5000', oNotification['notification']['background-color'], oNotification['notification']['ico'], true, soundEnabled);

			// Открыт выпадающий список уведомлений
			if ($('.navbar li#notifications').hasClass('open'))
			{
				 // Если список уведомлений был пуст, устанавливаем полосу прокрутки
				!$('.navbar-account #notificationsListBox .scroll-notifications > ul li').length && $.setNotificationsSlimScroll();

				 // Делаем прочитанными уведомления, находящиеся в видимой части списка
				 $.readNotifications();
			}
		},
		refreshNotificationsCallback: function(resultData)
		{
			var jNotificationsListBox = $('.navbar-account #notificationsListBox');

			// Есть уведомления для сотрудника
			if (resultData['userId'] && resultData['userId'] == jNotificationsListBox.data('currentUserId'))
			{
				// Массив идентификаторов непрочитанных уведомлений в списке уведомлений
				var unreadNotifications = [];

				$('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread').each(function (){
					unreadNotifications.push($(this).attr('id'));
				})

				// Непрочитанные уведомления из БД
				$.each(resultData['unreadNotifications'], function(index, notification ){

					var searchIndex = -1;

					if (~(searchIndex = unreadNotifications.indexOf('notification-' + notification['id'])))
					{
						// Удаляем из массива уведомления, оставшиеся непрочитанными
						unreadNotifications.splice(searchIndex, 1);
					}
				});

				// Отмечаем ранее непрочитанные уведомления как прочитанные в соответствии с данными из БД
				$.each(unreadNotifications, function (index, value){
					$('.navbar-account #notificationsListBox .scroll-notifications > ul li#' + value + '.unread').removeClass('unread');
				});

				 // Есть новые уведомления
				if (resultData['newNotifications'].length)
				{
					// Удаление записи об отсутствии уведомлений
					$('.navbar-account #notificationsListBox .scroll-notifications > ul li[id="notification-0"]').hide();

					if (typeof resultData['localStorage'] == 'undefined' || resultData['localStorage'] == false)
					{
						soundEnabled = $('#sound-switch').data('soundEnabled') === undefined
							? true
							: !!$('#sound-switch').data('soundEnabled');
					}
					else
					{
						soundEnabled = false;
					}

					$.each(resultData['newNotifications'], function(index, notification) {
						// Добавляем уведомление в список
						$.addNotification(notification, $('.navbar-account #notificationsListBox .scroll-notifications > ul'), soundEnabled);
					});

					// Обновление идентификатора последнего загруженного уведомления
					jNotificationsListBox.data('lastNotificationId', resultData['newNotifications'][resultData['newNotifications'].length-1]['id']);

					// Создаем slimscroll для нового списка, если список уведомлений открыт и при этом пуст
					if ($('.navbar li#notifications').hasClass('open')
						&& !$('.navbar-account #notificationsListBox .scroll-notifications > ul li').length)
					{
						$.setNotificationsSlimScroll();
					}

					// Показываем значек корзины - очистки списка уведомлений
					jNotificationsListBox.find('.footer .fa-trash-o').show();
					jNotificationsListBox.find('.footer #notification-search').show();
					jNotificationsListBox.find('.footer .glyphicon-search').show();
				}

				var countUnreadNotifications = $('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread').length;

				// В зависимости от наличия или отсутствия непрочитанных уведомлений добавляем или удаляем "wave in" для значка уведомлений
				$('.navbar li#notifications > a').toggleClass('wave in', !!countUnreadNotifications);

				//  Меняем значение баджа с числом непрочитанных уведомлений
				$('.navbar li#notifications > a > span.badge')
					.html(countUnreadNotifications)
					.toggleClass('hidden', !countUnreadNotifications);
			}
		},
		// Автоматическое обновление списка уведомлений
		refreshNotificationsList: function() {
			// add ajax '_'
			var data = jQuery.getData({}),
				jNotificationsListBox  = $('.navbar-account #notificationsListBox');

			data['lastNotificationId'] = jNotificationsListBox.data('lastNotificationId');
			data['currentUserId'] = jNotificationsListBox.data('currentUserId');

			var bLocalStorage = typeof localStorage !== 'undefined',
				bNeedsRequest = false;

			if (bLocalStorage)
			{
				var storage = localStorage.getItem('notifications'),
					storageObj = JSON.parse(storage);

				if (!storageObj || typeof storageObj['expired_in'] == 'undefined')
				{
					storageObj = {expired_in: 0};
				}

				if (Date.now() > storageObj['expired_in'])
				{
					storageObj['expired_in'] = Date.now() + 10000;
					bNeedsRequest = true;
				}
				else
				{
					storageObj['localStorage'] = true;
					$.refreshNotificationsCallback(storageObj);
				}
			}
			else
			{
				bNeedsRequest = true;
			}

			if (bNeedsRequest)
			{
				$.ajax({
					//context: textarea,
					url: '/admin/index.php?ajaxWidgetLoad&moduleId=' + jNotificationsListBox.data('moduleId') + '&type=0',
					type: 'POST',
					data: data,
					dataType: 'json',
					error: function(){},
					success: [function(resultData){
						if (bLocalStorage)
						{
							resultData['expired_in'] = storageObj['expired_in'];
						}

						try {
							localStorage.setItem('notifications', JSON.stringify(resultData));
						} catch (e) {
							if (e == QUOTA_EXCEEDED_ERR) {
								console.log('localStorage: QUOTA_EXCEEDED_ERR');
							}
						}
					}, $.refreshNotificationsCallback]
				});
			}
		},
		// Метод устанавливает уведомления прочитанными
		readNotifications: function (wheelDelta, delta){

			var masVisibleUnreadNotifications = [];

			// Список непрочитанныных уведомлений
			$('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread > a').each(function (){

				// Непрочитанное уведомление находится в области видимости выпадающего блока - делаем его прочитанным
				if ($.elementInBox($(this), $('.navbar-account div#notificationsListBox .slimScrollDiv'), wheelDelta, delta))
				{
					var notificationBox = $(this).parent('li.unread');
						notificationBox.removeClass('unread');

					masVisibleUnreadNotifications.push(notificationBox.attr('id').split('notification-')[1]);
				}
			});

			// Количество непрочитанных уведомлений
			var countUnreadNotifications = $('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread > a').length;

			// Нет непрочитанных уведомлений
			!countUnreadNotifications && $('.navbar li#notifications > a').removeClass('wave in');

			$('.navbar li#notifications > a > span.badge')
				.html(countUnreadNotifications)
				.toggleClass('hidden', !countUnreadNotifications);

			if (masVisibleUnreadNotifications.length)
			{
				// add ajax '_'
				var data = jQuery.getData({});

				data['notificationsListId'] = masVisibleUnreadNotifications;
				data['currentUserId'] = $('.navbar-account #notificationsListBox').data('currentUserId');

				$.ajax({
					//context: textarea,
					url: '/admin/index.php?ajaxWidgetLoad&moduleId=' + $('.navbar-account #notificationsListBox').data('moduleId')  + '&type=1',
					type: 'POST',
					data: data,
					dataType: 'json'
				});
			}
		},

		filterNotifications: function (jInputElement){

			var jNotifications = $('#notificationsListBox .scroll-notifications li[id != "notification-0"]');

			if (jNotifications.length)
			{
				var searchString = jInputElement.val().toLocaleLowerCase();

				jNotifications.show();

				if (searchString.length)
				{
					jNotifications.each(function(){

						var sourceText = $(this).text().toLocaleLowerCase();

						!~sourceText.indexOf(searchString) && $(this).hide();
					});
				}
			}
		},

		clearNotifications: function (){
			$('.navbar-account #notificationsListBox .scroll-notifications > ul li[id != "notification-0"]').remove();
			$('.navbar-account #notificationsListBox .scroll-notifications > ul li[id = "notification-0"]').show();

			// Нет непрочитанных уведомлений
			$('.navbar li#notifications > a').removeClass('wave in');

			$('.navbar li#notifications > a > span.badge')
				.html(0)
				.toggleClass('hidden', true);

			$('.navbar-account #notificationsListBox .footer .fa-trash-o').hide();
			$('.navbar-account #notificationsListBox .footer #notification-search').hide();
			$('.navbar-account #notificationsListBox .footer .glyphicon-search').hide();
		},
		eventsWidgetPrepare: function (){

			var sSlimscrollBarWidth = '5px';

			$('#eventsAdminPage')
				.on({
						'click': function (){ // Виджит развернут на весь экран

							$('#eventsAdminPage .tasks-list-container').css({'max-height': 'none'});

							$('#eventsAdminPage .tasks-list').slimscroll({destroy: true})
							$('#eventsAdminPage .tasks-list').slimscroll({
								height: $('#eventsAdminPage .widget-body').height(),
								color: 'rgba(0,0,0,0.3)',
								size: '5px'
							});
						}

					}, '[data-toggle = "maximize"] i.fa-expand'
				)
				.on({
						'click': function (){ // Виджет развернут на весь экран

							$('#eventsAdminPage .tasks-list-container').css({'max-height': '500px'});


							$('#eventsAdminPage .tasks-list').slimscroll({destroy: true})
							$('#eventsAdminPage .tasks-list').slimscroll({
									//height: '600px',
									height: 'auto',
									color: 'rgba(0,0,0,0.3)',
									size: '5px'
								});
						}

					}, '[data-toggle = "maximize"] i.fa-compress'
				)
				.on(
					{
						'mouseenter': function (){ // Наведение курсора мыши на полосу прокрутки дел
							$(this).css('width', (parseInt(sSlimscrollBarWidth) + 3) + 'px')
						},
						'mouseleave': function (){ // Уход курсора мыши с полосы прокрутки дел
							$(this).css('width', sSlimscrollBarWidth)
						}
					}, '.slimScrollBar'
				)
				.on(
					{
						'keyup': function (event){ // Фильтрация дел

							var jInputSearch = $(this),
								jEvents = jInputSearch.parents('.task-container').find('.tasks-list .task-item');

							// Нажали Esc
							if (event.keyCode == 27)
							{
								jInputSearch.val('');
							}

							if (jEvents.length)
							{
								var searchString = jInputSearch.val().toLocaleLowerCase();

								jEvents.show();

								if (searchString.length)
								{
									jEvents.each(function(){

										var sourceText = $(this).find('.task-body').text().toLocaleLowerCase();

										!~sourceText.indexOf(searchString) && $(this).hide();
									});
								}
							}

							if (!$('#eventsAdminPage .tasks-list-container').find('.slimScrollDiv').length)
							{
								jInputSearch.parents('.task-container').find('.tasks-list').slimscroll({
									//height: '500px',
									height: 'auto',
									color: 'rgba(0,0,0,0.3)',
									size: '5px'
								});
							}
						}
					}, '.search-event input'
				)
				.on(
					{
						'click': function (){ // Отметить выполненным

							var jEventItem = $(this).find('i').toggleClass('fa-square-o fa-check-square-o').parents('.task-item');

							jEventItem
								.css({'width': '100%'})
								.animate(
									{
										'margin-left': '-100%'
									},
									{
										duration: 700,
										specialEasing:
										{
										  //opacity: 'linear',
										  'margin-left': 'swing'
										},
										complete: function (){

											var jEventsList = $('#eventsAdminPage .tasks-list');
												//jEventsListContainer = $('#eventsAdminPage  .tasks-list-container'),
												//iMaxHeightEventsListContainer = parseInt(jEventsListContainer.css('max-height'));

											// Отмечаем дело как выполненное
											$(this).addClass('mark-completed');

											var ajaxData = $.getData({});

											ajaxData['eventId'] = jEventItem.prop('id').split('event-')[1];

											$.ajax({
												//context: textarea,
												url: '/admin/index.php?ajaxWidgetLoad&moduleId=' + $('#eventsAdminPage').data('moduleId')  + '&type=1',
												type: 'POST',
												data: ajaxData,
												dataType: 'json',
												success: function (resultData){

													if (resultData['eventId'])
													{
														// Удаляем дело из списка
														$('#eventsAdminPage .task-item[id = "event-' + resultData['eventId'] + '"]').remove();

														// Запоминаем положение полосы прокрутки в виджете дел
														//$('#eventsAdminPage').data('slimScrollBarTop', jEventsList.scrollTop() + 'px');

														// Обновляем список дел
														$('#eventsAdminPage [data-toggle="upload"]').click();

														// Нет незавершенных дел
														!jEventsList.find('.task-item[id != "event-0"]:not(.mark-completed)').length && jEventsList.find('.task-item[id = "event-0"]').toggleClass('hidden');
													}
												}
											});
										}
									}
								);
						}
				}, '.task-check'
			)
			.on(
				{
					'click': function (event){ // Обновление списка дел

						var jEventsAdminPage = $(this).parents('#eventsAdminPage'),
							jEventsList = jEventsAdminPage.find('.tasks-list');

						if (!event.isTrigger)
						{
							jEventsAdminPage.data('slimScrollBarTop', '0px');
						}
						else
						{
							jEventsAdminPage.data('slimScrollBarTop', jEventsList.scrollTop() + 'px');
						}

						$(this).find('i').addClass('fa-spin');
						$.widgetLoad({ path: '/admin/index.php?ajaxWidgetLoad&moduleId=' + $(this).data('moduleId') + '&type=0', context: jEventsAdminPage});
					}
				}, '[data-toggle = "upload"]'
			)
			.on(
				{
					'click': function (event){  // Клик на значке переключения действий с делами (добавление/фильтрация)

						//$(this).children('i').toggleClass('fa-plus fa-search');
						$(this).children('i.fa-plus').toggleClass('hidden');
						$(this).children('i.fa-search').toggleClass('hidden');


						$('#eventsAdminPage .task-search .search-event').toggleClass('hidden');
						$('#eventsAdminPage .task-search .add-event')
							.toggleClass('hidden')
							.find('input')
							.focus();

						event.preventDefault();

					}
				}, '[data-toggle = "toggle-actions"]'
			)
			.on(
				{
					'submit': function (event){ // Отправка формы добавления дела

						event.preventDefault();

						var eventName = $.trim($(this).find('input[name="event_name"]').val());

						// Название дела не задано
						if (!eventName.length)
						{
							return;
						}

						$('#sendForm i').toggleClass('fa-spinner fa-spin fa-check');

						var ajaxData = $.getData({}),
							formData = $(this).serializeArray();

						$.each(formData, function (){
							ajaxData[this.name] = $.trim(this.value);
						});

						$.ajax({
							url: '/admin/index.php?ajaxWidgetLoad&moduleId=' + $('#eventsAdminPage').data('moduleId')  + '&type=3',
							type: 'POST',
							data: ajaxData,
							dataType: 'json',
							success: function (resultData){
								$.widgetLoad({ path: '/admin/index.php?ajaxWidgetLoad&moduleId=' + $('#eventsAdminPage').data('moduleId')  + '&type=0', context: $('#eventsAdminPage') });
							}
						});
					}
				}, '.add-event form'
			)
		},

		// Изменение статуса дела в виджете дел
		eventsWidgetChangeStatus: function (dropdownMenu){

			var ajaxData = $.getData({}),
				jEventItem = $(dropdownMenu).parents('.task-item')
				jEventStatus = $('[selected="selected"]', dropdownMenu);

			ajaxData['eventId'] = jEventItem.prop('id');
			ajaxData['eventStatusId'] = jEventStatus.prop('id');

			$.ajax({
				url: '/admin/index.php?ajaxWidgetLoad&moduleId=' + $('#eventsAdminPage').data('moduleId')  + '&type=2',
				type: 'POST',
				data: ajaxData,
				dataType: 'json',
				success: function (resultData){

					// Финальный статус
					if (+resultData['finalStatus'])
					{
						jEventStatus.parents('li.task-item').children('.task-check').click();
					}
				}
			});
		},
		// Обработчики событий календаря
		calendarPrepare: function (){

			$(document)
				.on('shown.bs.popover', 'a.fc-event',  function() {

					$('.popover .calendar-event-description').slimscroll({
						height: '75px',
						//height: 'auto',
						color: 'rgba(0,0,0,0.3)',
						size: '5px',
					});
				})
				// Удаление события календаря
				.on('click', '.popover #deleteCalendarEvent', function () {

					var eventId = $(this).data('eventId'),
						moduleId = $(this).data('moduleId') ;

					if (eventId && moduleId)
					{
						bootbox.confirm({
							message: "Удалить событие?",
							buttons: {
								confirm: {
									label: 'Да',
									className: 'btn-success'
								},
								cancel: {
									label: 'Нет',
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
										url: '/admin/calendar/index.php?eventDelete',
										type: "POST",
										dataType: 'json',
										data: ajaxData,
										success: function (result){

											$.loadingScreen('hide');

											if (!result['error'] && result['message'])
											{
												// Удаляем событие из календаря
												$('#calendar').fullCalendar( 'removeEvents', eventId + '_' + moduleId)
												Notify('<span>' + result['message'] + '</span>', 'top-right', '5000', 'success', 'fa-check', true, true)
											}
											else if (result['message']) // Ошибка, отменяем действие
											{
												result['error'] && revertFunc();
												Notify('<span>' + result['message'] + '</span>', 'top-right', '5000', 'danger', 'fa-warning', true, true)
											}
										}
									})
								}
							}
						});
					}
				})
				// Редактирование события календаря
				.on('click', '.popover #editCalendarEvent', function () {

					var eventId = $(this).data('eventId'),
						moduleId = $(this).data('moduleId'),
						dH = $(window).height(),
						wH = $('#id_content').outerHeight(),
						eventElement = $('[data-event-id="' + eventId + '_' + moduleId + '"]');

						eventElement.popover && eventElement.popover('hide');

					$.openWindow(
						{
							path: '/admin/calendar/index.php?addEntity&eventId=' + eventId + '&moduleId=' + moduleId,
							addContentPadding: false,
							width: $('#id_content').outerWidth() * 0.9, //0.8
							height: (dH < wH ? dH : wH) * 0.9, //0.8
							AppendTo: $('#id_content').parent().get(0),
							positionOf: '#id_content',
							Maximize: false,
							dialogClass: 'hostcms6'
						}
					)
					.addClass('modalwindow');
				})
				.on('click', '.popover-calendar-event button.close' , function(){

					var popoverId = $(this).parents('.popover-calendar-event').attr('id'),
						calendarEvent = $(".fc-event[aria-describedby='" + popoverId +"']");

					calendarEvent.popover('hide');
				})
		},
		widgetRequest: function(settings){
			$.loadingScreen('show');

			// add ajax '_'
			var data = jQuery.getData({});

			jQuery.ajax({
				context: settings.context,
				url: settings.path,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function() {
					//jQuery(this).HostCMSWindow('reload');
					// add ajax '_'
					var data = jQuery.getData({});
					jQuery.ajax({
						context: this,
						url: this.data('hostcmsurl'),
						data: data,
						dataType: 'json',
						type: 'POST',
						//success: jQuery.ajaxCallback
						success: [jQuery.ajaxCallback, function(returnedData)
						{
							if (returnedData == null || returnedData.form_html == null)
							{
								return;
							}

							// Clear widget place
							if (returnedData.form_html == '')
							{
								$(this).empty();
							}
						}]
					});
				}
			});
		},
		cloneProperty: function(windowId, index)
		{
			var jProperies = jQuery('#' + windowId + ' #property_' + index),

			// Объект окна настроек большого изображения
			oSpanFileSettings =  jProperies.find("span[id ^= 'file_large_settings_']");

			// Закрываем окно настроек большого изображения
			if (oSpanFileSettings.length && oSpanFileSettings.children('i').hasClass('fa-times'))
			{
				oSpanFileSettings.click();
			}

			// Объект окна настроек малого изображения
			oSpanFileSettings =  jProperies.find("span[id ^= 'file_small_settings_']");
			// Закрываем окно настроек малого изображения
			if (oSpanFileSettings.length && oSpanFileSettings.children('i').hasClass('fa-times'))
			{
				oSpanFileSettings.click();
			}

			var jNewObject = jProperies.eq(0).clone(),
			iRand = Math.floor(Math.random() * 999999);

			jNewObject.insertAfter(
				jQuery('#' + windowId).find('div.row[id="property_' + index + '"],div.row[id^="property_' + index + '_"]').eq(-1)
			);

			jNewObject.attr('id', 'property_' + index + '_' + iRand);

			// Change item_div ID
			jNewObject.find("div[id^='file_']").each(function(index, object){
				jQuery(object).prop('id', jQuery(object).prop('id') + '_' + iRand);

				// Удаляем скопированные элементы popover'а
				jQuery(object).find("div[id ^= 'popover']").remove();
			});

			jNewObject.find("div[id *='_watermark_property_']").html(jNewObject.find("div[id *='_watermark_property_']").html());
			jNewObject.find("div[id *='_watermark_small_property_']").html(jNewObject.find("div[id *='_watermark_small_property_']").html());

			// Удаляем элементы просмотра и удаления загруженнного изображения
			jNewObject.find("[id ^= 'preview_large_property_'], [id ^= 'delete_large_property_'], [id ^= 'preview_small_property_'], [id ^= 'delete_small_property_']").remove();
			// Удаляем скрипт просмотра загуженного изображения
			jNewObject.find("input[id ^= 'property_" + index + "_'][type='file'] ~ script").remove();

			jNewObject.find("input[id^='field_id'],select,textarea").attr('name', 'property_' + index + '[]');
			jNewObject.find("div[id^='file_small'] input[id^='small_field_id']").attr('name', 'small_property_' + index + '[]').val('');
			jNewObject.find("input[id^='field_id'][type!=checkbox],input[id^='property_'][type!=checkbox],input[id^='small_property_'][type!=checkbox],input[id^='description'][type!=checkbox],select,textarea").val('');

			jNewObject.find("input[id^='create_small_image_from_large_small_property']").attr('checked', true);

			// Change input name
			jNewObject.find(':regex(name, ^\\S+_\\d+_\\d+$)').each(function(index, object){
				var reg = /^(\S+)_(\d+)_(\d+)$/;
				var arr = reg.exec(object.name);
				jQuery(object).prop('name', arr[1] + '_' + arr[2] + '[]');
			});

			jNewObject.find("div.img_control div,div.img_control div").remove();
			jNewObject.find("input[type='text']#description_large").attr('name', 'description_property_' + index + '[]');
			jNewObject.find("input[type='text']#description_small").attr('name', 'description_small_property_' + index + '[]');

			var oDateTimePicker = jProperies.find('div[id ^= "div_property_' + index + '_"], div[id ^= "div_field_id_"]').data('DateTimePicker');

			if(oDateTimePicker)
			{
				jNewObject.find('script').remove();
				jNewObject.find('div[id ^= "div_property_' + index + '_"], div[id ^= "div_field_id_"]').datetimepicker({locale: 'ru', format: oDateTimePicker.format()});
			}
		},
		cloneFormRow: function(cloningElement){
			if (cloningElement)
			{
				var	originalRow = $(cloningElement).closest('.row'),
					newRow = originalRow.clone();

				newRow.find('input').each(function(){
					$(this).val('');
				});

				newRow.find('select').each(function(){
					$(':selected', this).removeAttr("selected");
					$(':first', this).attr("selected", "selected");
				});

				newRow.find('input[name *= "#"], select[name *= "#"]').each(function(){
					this.name = this.name.split('#')[0] + '[]';
				});

				newRow.find('.btn-delete').removeClass('hide');
				newRow.insertAfter(originalRow);

				return newRow;
			}
		},
		deleteFormRow: function(deleteElement){
			if (deleteElement)
			{
				// Удаляемая строка, с элементами формы
				var objectRow = $(deleteElement).closest('.row');

				!objectRow.siblings('.row').size() && $.cloneFormRow(deleteElement).find('.btn-delete').addClass('hide');
				objectRow.remove();
			}
		},

		// Показ сотрудников в списке select2
		templateResultItemResponsibleEmployees: function (data, item){

			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class");

			if (data.id)
			{

				// Регулярное выражение для получения id select-а, на базе которого создан данный select2
				var regExp = /select2-([-\w]+)-result-\w+-\d+?/g,
					myArray = regExp.exec(data._resultId);

				if (myArray)
				{
					// Объект select, на базе которого создан данный select2
					var selectControlElement = $("#" + myArray[1]),
						templateResultOptions = selectControlElement.data("templateResultOptions");

					// Убираем из списка создателя дела, чтобы исключить возможность его удаления
					if (templateResultOptions && ~templateResultOptions.excludedItems.indexOf(+data.id))
					{
						item.remove();
						return;
					}
				}
			}

			if (data.element && $(data.element).attr("style"))
			{
				// Добавляем стили для групп и элементов. Элементам только при показе выпадающего списка
				($(data.element).is("optgroup") || $(data.element).is("option") && $(item).hasClass("select2-results__option")) && $(item).attr("style", $(data.element).attr("style"));
			}

			// Компания, отдел, ФИО сотрудника
			var resultHtml = '<span class="' + className + '">' + arraySelectItemParts[0] + '</span>';

			if (arraySelectItemParts[2])
			{
				// Список должностей через запятую
				resultHtml += '<span class="user-post">' + arraySelectItemParts[2].split('###').join(', ')  + '</span>';
			}

			// Изображение
			if (arraySelectItemParts[3])
			{
				resultHtml = '<img src="' + arraySelectItemParts[3] + '" height="30px" class="pull-left margin-top-5 margin-right-5 img-circle">' + resultHtml;
			}

			// Удаляем часть с названием отдела
			arraySelectItemParts[1] && delete(arraySelectItemParts[1]);

			return resultHtml;
		},

		// Показ выбранных сотрудников в select2
		templateSelectionItemResponsibleEmployees: function (data, item){

			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class"),
				//arraySelectItemIdParts = data.id.split("_"),
				isCreator = false,

				// Регулярное выражение для получения id select-а, на базе которого создан данный select2
				regExp = /select2-([-\w]+)-result-\w+-\d+?/g,
				myArray = regExp.exec(data._resultId);

			if (myArray)
			{
				// Объект select, на базе которого создан данный select2
				var selectControlElement = $("#" + myArray[1]),
					templateSelectionOptions = selectControlElement.data("templateSelectionOptions"),
					selectionSingle = selectControlElement.next('.select2-container').find('.select2-selection--single');

				// Если не мультиселект, добавляем контейнеру выбранного элемента класс
				if (selectionSingle.length)
				{
					selectionSingle.addClass('user-container');
				}

				//console.log('selectionSingle = ', selectionSingle);

				// Убираем элемент удаления (крестик) для создателя дела
				if (templateSelectionOptions && ~templateSelectionOptions.unavailableItems.indexOf(+data.id))
				{
					//item.find("span.select2-selection__choice__remove").remove();
					item
						.addClass("bordered-primary event-author")
						.find("span.select2-selection__choice__remove").remove();

					isCreator = true;
				}
			}

			// Компания, отдел, ФИО сотрудника
			// arraySelectItemParts[0] = \'<span class="\' + className + \'">\' + (className == "user-name" && isCreator ? \'<i class="fa fa-flag"></i> \' : "") + arraySelectItemParts[0] + \'</span>\';
			var resultHtml = '<span class="' + className + '">' + arraySelectItemParts[0] + '</span>';

			// Формируем title элемента
			data.title = arraySelectItemParts[0];

			if (arraySelectItemParts[1])
			{
				resultHtml += '<span class="company-department">' + arraySelectItemParts[1] + '</span>';
				data.title += " - " + arraySelectItemParts[1];
			}

			// Список должностей через запятую
			if (arraySelectItemParts[2])
			{
				var departmentPosts = arraySelectItemParts[2].split('###').join(', ');

				resultHtml += '<span class="user-post">' + departmentPosts  + '</span>';
				data.title += " - " + departmentPosts;
			}

			// Изображение
			if (arraySelectItemParts[3])
			{
				resultHtml = '<img src="' + arraySelectItemParts[3] + '" height="30px" class="pull-left margin-top-5 margin-right-5 img-circle">' + resultHtml;
			}

			return resultHtml;
		},

		// Показ клиентов выпадающего списка select2
		templateResultItemSiteusers: function (data, item){

			if (!data.text)
			{
				return '';
			}

			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class");

			if (data.element && $(data.element).attr("style"))
			{
				// Добавляем стили для групп и элементов. Элементам только при показе выпадающего списка
				($(data.element).is("optgroup") || $(data.element).is("option") && $(item).hasClass("select2-results__option")) && $(item).attr("style", $(data.element).attr("style"));
			}

			// Компания/ФИО клиента
			var resultHtml = '<span class="' + className + '">' + arraySelectItemParts[0] + '</span>';

			if (arraySelectItemParts[1])
			{
				resultHtml = '<img src="' + arraySelectItemParts[1] + '" height="30px" class="margin-right-5 img-circle">' + resultHtml;
			}

			return resultHtml;
		},

		// Формирование результатов выбора клиентов в select2
		templateSelectionItemSiteusers: function (data, item){

			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class");

			// Компания/ФИО клиента
			var resultHtml = '<span class="' + className + '">' + arraySelectItemParts[0] + '</span>';

			// Устанавливает title для элемента
			data.title = arraySelectItemParts[0];

			if (arraySelectItemParts[1])
			{
				resultHtml = '<img src="' + arraySelectItemParts[1] + '" height="30px" class="margin-top-5 margin-right-5 margin-bottom-5 img-circle">' + resultHtml;
			}

			return resultHtml;
		},

		dealsPrepare: function (){

		/*
			$("body").popover({
				container: "body",
				selector: "li[id ^= \'deal_template_step_\']",
				placement: "bottom",
				template: "<div class=\"popover\"><div class=\"arrow\"></div><div class=\"popover-content\"></div></div>",
				html: true,
				trigger: "hover"
			});*/

			$("body").on("click", ".deal-steps li", function (){
				// Идентификатор этапа сделки
				var dealTemplateStepId = parseInt($(this).attr('id').split('deal_template_step_')[1]) || 0,
					dealTemplateStepName;

				if (dealTemplateStepId)
				{
					// Идентификатор сделки
					var dealTemplateSteps = $(this).parent('.deal-steps'),
						dealId = dealTemplateSteps.data('deal-id');

					if (dealTemplateSteps.data('change-by-click') && $(this).children('a.available').length)
					{
						$('#id_content #row_0_' + dealId).toggleHighlight();
						$.adminCheckObject({objectId: 'check_0_' + dealId, windowId: 'id_content'});
						$.adminLoad({path: '/admin/deal/index.php', action: 'changeStep', operation: 'changeStep', additionalParams: 'dealStepId=' + dealTemplateStepId, windowId: 'id_content'});
					}
					// При редактировании сделки
					else
					{
						if ($(this).children('a.available').length)
						{
							$('a.clear-next', dealTemplateSteps).each(function(){
								$(this).removeClass('clear-next')
							});

							// Нажали на шаг уже отмеченный как "следующий",
							// снимаем отметку для перехода
							if ($(this).children('a').hasClass('next'))
							{
								//$(this).children('a').removeClass('next');
								$(this).children('a').toggleClass('next clear-next');

								dealTemplateStepId = parseInt($(this).parent('.deal-steps').data('stepId'));

								dealTemplateStepName = $(this).parent('.deal-steps').find('li#deal_template_step_' + dealTemplateStepId + ' a').prop('title');
							}
							else
							{
								dealTemplateStepName = $(this).children('a').prop('title');

								$('a.next', dealTemplateSteps).each(function(){
									$(this).removeClass('next')
								});

								$(this).children('a.available') && $(this).children('a').addClass('next').removeClass('clear-next');
							}


							// Меняем цвет названия этапа
							dealTemplateSteps.parent('div').next('[name="deal_template_step_id"]').val(dealTemplateStepId);
							$('.deal-template-step-name.deal-template-step-name-inner').text(dealTemplateStepName);

							$.changeDealTemplateName($(this).parent('.deal-steps').find('li#deal_template_step_' + dealTemplateStepId + ' a'));
						}
					}
				}
			})
			.on('mouseover', '.deal-steps li a.available:not(.current):not(.next)',  function (){

				$(this)
					.parents('.deal-steps')
					.find('li a.next')
					.toggleClass('next clear-next');
			})
			.on('mouseout', '.deal-steps li a.available:not(.current):not(.next)',  function (){

				$(this)
					.removeClass('clear-next')
					.parents('.deal-steps')
					.find('li a.clear-next')
					.toggleClass('next clear-next');

			})
			// Добавление дела(события) к сделке
			.on('click', '[id = "addDealEvent"]', function (){

				//console.log('!!! click addDealEvent = ', $(this).data('dealId'));

				var dealId = $(this).data('dealId');

				if (dealId)
				{
					$.modalLoad({path: '/admin/event/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&dealId=' + dealId, windowId: 'id_content'});
				}
			})
			// Добавление комментария к сделке
			.on('click', '[id = "addDealNote"]', function (){

				console.log('click addDealNote dealId = ', $(this).data('dealId'));

				var dealId = $(this).data('dealId');

				if (dealId)
				{
					$.modalLoad({path: '/admin/deal/note/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&dealId=' + dealId, windowId: 'id_content'});
				}
			});
		},
		rgb2hex: function(rgb)
		{
			rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
			function hex(x) {
				return ("0" + parseInt(x).toString(16)).slice(-2);
			}
			return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
		},
		changeDealTemplateName: function (jDeal)
		{
			var rgbCurrent = jDeal.css("background-color"),
				hex = $.rgb2hex(rgbCurrent);

			$(".deal-template-step-name.deal-template-step-name-inner").css("color", hex);
		}
	});

	jQuery.fn.extend({
		insertAtCaret: function(newValue){
		  return this.each(function(i) {
			if (document.selection) {
			  //For browsers like Internet Explorer
			  this.focus();
			  sel = document.selection.createRange();
			  sel.text = newValue;
			  this.focus();
			}
			else if (this.selectionStart || this.selectionStart == '0') {
			  //For browsers like Firefox and Webkit based
			  var startPos = this.selectionStart;
			  var endPos = this.selectionEnd;
			  var scrollTop = this.scrollTop;
			  this.value = this.value.substring(0, startPos) + newValue + this.value.substring(endPos, this.value.length);
			  this.focus();
			  this.selectionStart = startPos + newValue.length;
			  this.selectionEnd = startPos + newValue.length;
			  this.scrollTop = scrollTop;
			} else {
			  this.value += newValue;
			  this.focus();
			}
		  })
		},
		/* --- CHAT --- */
		addChatBadge: function(count)
		{
			return this.each(function(){
				var jSpan = jQuery(this).find('span.badge');

				jSpan.length
					? jSpan.text(count)
					: jQuery(this).append('<span class="badge margin-left-10">' + count + '</span>');
			});
		},
		/* --- /CHAT --- */
		selectSiteuser: function(settings)
		{
			settings = $.extend({
				minimumInputLength: 1,
				allowClear: true,
				ajax: {
					url: "/admin/siteuser/index.php?siteuser",
					dataType: "json",
					type: "GET",
					processResults: function (data) {
						var aResults = [];
						$.each(data, function (index, item) {
							aResults.push({
								"id": item.id,
								"text": item.text
							});
						});
						return {
							results: aResults
						};
					}
				}
			}, settings);

			return this.each(function(){
				jQuery(this).select2(settings);
			});
		},
		autocompleteShopItem: function(shop_id, shop_currency_id, selectOption)
		{
			return this.each(function(){
				 jQuery(this).autocomplete({
					  source: function(request, response) {
						$.ajax({
						  url: '/admin/shop/index.php?autocomplete&shop_id=' + shop_id + '&shop_currency_id=' + shop_currency_id,
						  dataType: 'json',
						  data: {
							queryString: request.term
						  },
						  success: function( data ) {
							response( data );
						  }
						});
					  },
					  minLength: 1,
					  create: function() {
						$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
							return $('<li></li>')
								.data('item.autocomplete', item)
								.append($('<a>').text(item.label))
								.append($('<span>').text(item.price_with_tax + ' ' + item.currency))
								.append($('<span>').text(item.marking))
								.appendTo(ul);
						}

						 $(this).prev('.ui-helper-hidden-accessible').remove();
					  },
					  /*select: function( event, ui ) {
						$('<input type=\'hidden\' name=\'set_item_id[]\'/>')
							.val(typeof ui.item.id !== 'undefined' ? ui.item.id : 0)
							.insertAfter($('.set-item-table'));

						$('.set-item-table > tbody').append(
							$('<tr><td>' + ui.item.label + '</td><td>' + ui.item.marking + '</td><td><input class=\"set-item-count form-control\" name=\"set_count[]\" value=\"1.00\"/></td><td>' + ui.item.price_with_tax + ' ' + ui.item.currency + '</td><td></td></tr>')
						);

						ui.item.value = '';  // it will clear field
					  },*/
					  select: selectOption,
					  open: function() {
						$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
					  },
					  close: function(event, ui) {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					  }
				});
			});
		},
		refreshEditor: function()
		{
			return this.each(function(){
				//this.disabled = !this.disabled;
				jQuery(this).find(".CodeMirror").each(function(){
					this.CodeMirror.refresh();
				});
			});
		},
		HostCMSWindow: function(settings)
		{
			var object = $(this);

			settings = jQuery.extend({
				title: '',
				message: '<div id="' + object.attr('id') + '"><div id="id_message"></div>' + object.html() + '</div>'
				/*message: object.html(),
				windowId: object.attr('id')*/
			}, settings);

			$.modalWindow(settings);

			object.remove();
		}
	});

})(jQuery);

$(function(){
	//$.notificationsPrepare();
	//$.eventsPrepare();
	$.dealsPrepare();

	// $.calendarPrepare();

	/* --- CHAT --- */
	$('#chatbar').length && $.chatPrepare();
	/* --- /CHAT --- */

	$('body').on('click', '[id ^= \'file_\'][id *= \'_settings_\']', function() {
		$(this)
		.popover({
			placement: 'left',
			content:  $(this).nextAll('div[id *= "_watermark_"]').show(),
			container: $(this).parents('div[id ^= "file_large_"], div[id ^= "file_small_"]'),
			html: true,
			trigger: 'manual'
		})
		.popover('toggle');
	});

	//$('.page-content')
	$('body').on('hide.bs.popover', '[id ^= \'file_\'][id *= \'_settings_\']', function () {
		var popoverContent = $(this).data('bs.popover').$tip.find('.popover-content div[id *= "_watermark_"], .popover-content [id *= "_watermark_small_"]');

		if (popoverContent.length)
		{
			$(this).after(popoverContent.hide());
		}
		$(this).find("i.fa").toggleClass("fa-times fa-cog");
	})
	.on('show.bs.popover', '[id ^= \'file_\'][id *= \'_settings_\']', function () {
		$(this).find("i.fa").toggleClass("fa-times fa-cog");
	});

	//$('.page-content')
	$('body').on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
		$(e.target.getAttribute('href')).refreshEditor();
	});

	//$('.page-container')
	$('body').on('touchend', '.page-sidebar.menu-compact .sidebar-menu .submenu > li', function(e) {
		$(this).find('a').click();
	});

	$('.page-container').on('click', '.fa.profile-details', function (){
		$(this).closest('.ticket-item').next('li.profile-details').toggle(400, function() {
			$(this).prev('.ticket-item').find('.fa.profile-details').toggleClass('fa-chevron-down fa-chevron-up')
		});
	});

	$('body')
		.on('shown.bs.dropdown', '.admin-table td div', function (){

			var td = $(this).closest('td').css('overflow', 'visible');
		})
		.on('hidden.bs.dropdown', '.admin-table td div', function (){

			var td = $(this).closest('td').css('overflow', 'hidden');
		})
		// Выбор элемента dropdownlist
		.on('click', '.form-element.dropdown-menu li', function (){

			var li = $(this),
				dropdownMenu = li.parent('.dropdown-menu'),
				containerCurrentChoice = dropdownMenu.prev('[data-toggle="dropdown"]');

			//  Не задан атрибут (current-selection), запрещающий выбирать выбранный элемент списка или он задан и запрещает выбор
			//  при этом выбрали уже выбранный элемент
			if ((!dropdownMenu.attr('current-selection') || dropdownMenu.attr('current-selection') != 'enable') && li.attr('selected'))
			{
				return;
			}

			// Меняем значение связанного с элементом скрытого input'а
			dropdownMenu.next('input[type="hidden"]').val(li.attr('id'));

			containerCurrentChoice.css('color', li.find('i').css('color'));
			containerCurrentChoice.html(li.find('a').html() + '<i class="fa fa-angle-down icon-separator-left"></i>');

			dropdownMenu.find('li[selected][id != ' + li.prop('id') + ']').removeAttr('selected');
			li.attr('selected', 'selected');

			// вызываем у родителя onchange()
			dropdownMenu.trigger('change');
		})
		.on("keyup", ".bootbox.modal", function(event) {

			if (event.which === 13 && $(this).find(event.target).filter('input:not([id *="filer_field"])').length)
			{
				$(this).find('[data-bb-handler = "success"]').click();
			}
		})
		.on("click", "#filter-visibility-switch", function(event) {

			$(".filter-form").slideToggle(500);
		})
		.on("click", '.context-menu a', function(event) {
			 $(this).parents('.context-menu').hide();

			 event.preventDefault();
		})
		.on("click", function(event) {

			 if (!$(event.target).parents('.fc-body').length)
			 {
				 // Убираем контекстные меню
				 $('.context-menu').hide();
			 }
		})
		.on('keyup', function(event) {

			// Нажали Esc - убираем контекстное меню
			if (event.keyCode == 27)
			{
				$('.context-menu').hide();
			}
		})
		.on('click', '[data-action="showListDealTemplateSteps"]', function() {

			$.adminLoad({path: '/admin/deal/template/step/index.php', action: 'addConversion', operation: 'showListDealTemplateSteps', additionalParams: 'deal_template_id=' + $(this).parents('.deal-template-step-conversion').data('deal-template-id') + '&hostcms[checked][0][' + $(this).attr('id').split('adding_conversion_to_')[1] + ']=1', windowId: 'id_content'});

			return false;
		})
		// Удаление перехода сделки
		.on('click', '[id ^= "conversion_"] .close', function() {

			var wrapConversion = $(this).parent('[id ^="conversion_"]'), startAndEndStepId = wrapConversion.attr('id').split('_'), conversionStartStepId = startAndEndStepId[1], conversionEndStepId = startAndEndStepId[2];

			$.adminLoad({path: '/admin/deal/template/step/index.php', action: 'deleteConversion', operation: '', additionalParams: 'deal_template_id=' + $(this).parents('.deal-template-step-conversion').data('deal-template-id') + '&conversion_end_step_id=' + conversionEndStepId  + '&hostcms[checked][0][' + conversionStartStepId + ']=1', windowId: 'id_content'});
		})
		.on('click', '.dropdown-step-list .close', function() {

			var dropdownStepList = $(this).parent('.dropdown-step-list');

			dropdownStepList.prev("[id ^= 'adding_conversion_to_']").show();
			dropdownStepList.remove();
		})
		// Сворачивание/разворачивание списка сотрудников отдела и его дочерних отделов в "окне" установки прав на действия с типом сделок
		.on('click', '.title_department', function() {

			$(this)
				//.toggleClass('collapsed')
				.children('i')
				.toggleClass('fa-caret-right fa-caret-down');

			$(this)
				.parent('.depatment_info')
				.next('.wrap')
				.slideToggle();
		})
		// Сворачивание/разворачивание списка сотрудников отдела в "окне" установки прав на действия с типом сделок
		.on('click', '.title_users', function() {

			$(this)
				//.toggleClass('collapsed')
				.children('i')
				.toggleClass('fa-caret-right fa-caret-down');

			$(this)
				.next('.list_users')
				.slideToggle();
			/*

			if ($(this).hasClass('collapsed'))
			{

			}
			else
			{
				$(this).children('i').toggleClass('fa-caret-down fa-caret-right');
			}*/
		})
		.on(
			{
				'click': function(event) {

					var iconPermissionId = $(this).attr('id'), //department_5_2_3 или user_7_2_3
						aPermissionProperties = iconPermissionId.split('_'),
						objectTypePermission = aPermissionProperties[0] == 'department' ? 0 : 1,
						objectIdPermission = aPermissionProperties[1], // идентификатор объекта (отдел или сотрудник), к которому применяются права
						dealTemplateStepId = aPermissionProperties[2], // получаем идентификатор этапа сделки
						actionType = aPermissionProperties[3], // тип действия (0 - создание, 1 - редактирование, 2 - просмотр, 3 - удаление)
						sUrlParams = document.location.search,
						dealTemplateId;

					// Строка параметров
					if (sUrlParams.length)
					{
						sUrlParams = sUrlParams.slice(1); // Убираем из строки начальный символ "?"

						var aUrlParams = sUrlParams.split('&'),
							aObjUrlParams = [];

						for (var i = 0; i < aUrlParams.length; i++)
						{
							var aUrlParam = aUrlParams[i].split('=');

							aObjUrlParams[aUrlParam[0]] = aUrlParam[1];
						}

						// Идентификатор типа сделки
						dealTemplateId = aObjUrlParams['deal_template_id'];
					}

					//$('#id_content #row_0_9').toggleHighlight();
					$.adminCheckObject({objectId: 'check_0_' + dealTemplateStepId, windowId: 'id_content'}); $.adminLoad({path: '/admin/deal/template/step/index.php', action: 'changeAccess', operation: '', additionalParams: 'deal_template_id=' + dealTemplateId + '&objectType=' + objectTypePermission + '&objectId=' + objectIdPermission + '&actionType=' + actionType, windowId: 'id_content'});
				},

				'mousedown': function(event) {

					$(this).removeClass('changed');
				},

				'mouseover': function(event) {

					if ($(this).hasClass('changed'))
					{
						$(this).toggleClass('fa-circle-o fa-circle');
					}
				},
				'mouseout': function() {

					$(this).removeClass('changed');
				}
			},
			'.icons_permissions i'
		);
});

var methods = {
	show: function() {
		$('body').css('cursor', 'wait');
		$('.loading-container').removeClass('loading-inactive');
	},
	hide: function() {
		$('body').css('cursor', 'auto');
		setTimeout(function () {
			$('.loading-container').addClass('loading-inactive');
		}, 0);
	}
};

function calendarDayClick(oDate, jsEvent)
{
	var contextMenu = $('body #calendarContextMenu').show(),
		windowWidth = $(window).width(),
		contextMenuWidth = contextMenu.outerWidth(),
		positionLeft = (jsEvent.pageX + contextMenuWidth > windowWidth) ? (windowWidth - contextMenuWidth) : jsEvent.pageX;

		contextMenu.css({'top': jsEvent.pageY, left: positionLeft});

		$('ul.dropdown-info').data('timestamp', oDate.unix());

	//


	/*
	 $("body").on("contextmenu", "table tr", function(e) {
		$contextMenu.css({
		  display: "block",
		  left: e.pageX,
		  top: e.pageY
		});
		return false;
	  });
	  */

	// console.log('jsEvent = ', jsEvent);

	 /*
	var dH = $(window).height(),
		wH = $('#id_content').outerHeight();
	$.openWindow(
		{
			path: '/admin/calendar/index.php?addEntity',
			addContentPadding: false,
			width: $('#id_content').outerWidth() * 0.9, //0.8
			height: (dH < wH ? dH : wH) * 0.9, //0.8
			AppendTo: $('#id_content').parent().get(0),
			positionOf: '#id_content',
			Maximize: false,
			dialogClass: 'hostcms6'
		}
	)
	.addClass('modalwindow');*/
}

/*
function calendarEventClick( event, jsEvent, view )
{
	// Убираем контекстные меню
	$('.context-menu').hide();
}*/

function calendarEvents(start, end, timezone, callback)
{
	var ajaxData = $.getData({});

	ajaxData['start'] = start.unix();
	ajaxData['end'] = end.unix();

	$.ajax({
		url: '/admin/calendar/index.php?loadEvents',
		type: 'POST',
		dataType: 'json',
		data: ajaxData,
		success: function(result) {
			var events = (result['events'] && result['events'].length) ? result['events'] : [];

			callback(events);
		}
	});
}

function calendarEventClick(event, jsEvent, view)
{
	var eventIdParts = event.id.split('_'), // Идентификатор события календаря состоит из 2-х частей - id сущности и id модуля, разделенных '_'
		eventId = eventIdParts[0],
		moduleId = eventIdParts[1];

	$.modalLoad({
		path: event.path,
		action: 'edit',
		operation: 'modal',
		additionalParams: 'hostcms[checked][0][' + eventId + ']=1',
		windowId: 'id_content'
	});
}

function calendarEventRender(event, element)
{
	if (event.dragging || event.resizing)
	{
		element.popover('destroy');
		return;
	}

	// Добавляем блоку, связанному с событием, идентификатор этого события для удобства поиска блока в последующей работе с календарем
	element.attr('data-event-id', event.id);

	// $(element).css({'background-image': 'linear-gradient(to bottom,#fff 0,#ededed 100%)'});
	$(element).css({'background-color': '#fbfbfb'});

	/*element.popover({
		title: event.title,
		//placement: 'right',
		content: event.htmlDetails || event.description || event.title,
		html:true,
		trigger: 'click',
		container:'.fc-view .fc-body',
		placement: 'auto right',
		template: '<div class="popover popover-calendar-event " role="tooltip"><div class="arrow"></div><h3 class="popover-title" ' + (event.borderColor ? ('style="border-color: ' + event.borderColor + '"') : '')  + '></h3><button type="button" class="close">×</button><div class="popover-content bg-white"></div></div>'
	});*/
};

function calendarEventDragStart( event, jsEvent, ui, view )
{
	event.dragging = true;
};

function calendarEventResizeStart( event, jsEvent, ui, view )
{
	event.resizing = true;
};

function calendarEventResize( event, delta, revertFunc, jsEvent, ui, view )
{

	$.loadingScreen('show');

	var eventIdParts = event.id.split('_'), // Идентификатор события календаря состоит из 2-х частей - id сущности и id модуля, разделенных '_'
		eventId = eventIdParts[0],
		moduleId = eventIdParts[1],

		ajaxData = $.extend({}, $.getData({}), {'eventId': eventId, 'moduleId': moduleId, 'deltaSeconds': delta.asSeconds()}) ;

		$.ajax({

			url: '/admin/calendar/index.php?eventResize',
			type: "POST",
			dataType: 'json',
			data: ajaxData,
			success: function (result){

				$.loadingScreen('hide');

				if (!result['error'] && result['message'])
				{
					Notify('<span>' + result['message'] + '</span>', 'top-right', '5000', 'success', 'fa-check', true, true)

					$('#calendar').fullCalendar( 'refetchEvents' );
				}
				else if (result['message']) // Ошибка, отменяем действие
				{
					result['error'] && revertFunc();
					Notify('<span>' + result['message'] + '</span>', 'top-right', '5000', 'danger', 'fa-warning', true, true)
				}
			}
		})

};

function calendarEventDrop( event, delta, revertFunc, jsEvent, ui, view )
{

	$.loadingScreen('show');

	var eventIdParts = event.id.split('_'),
		eventId = eventIdParts[0],
		moduleId = eventIdParts[1],

		ajaxData = $.extend({}, $.getData({}), {'eventId': eventId, 'moduleId': moduleId, startTimestamp: event.start.format('X'),  'allDay': +event.allDay}) ;

	$.ajax({

		url: '/admin/calendar/index.php?eventDrop',
		type: "POST",
		dataType: 'json',
		data: ajaxData,
		success: function (result){

			$.loadingScreen('hide');

			if (!result['error'] && result['message'])
			{
				Notify('<span>' + result['message'] + '</span>', 'top-right', '5000', 'success', 'fa-check', true, true)
			}
			else if (result['message']) // Ошибка, отменяем действие
			{
				result['error'] && revertFunc();
				Notify('<span>' + result['message'] + '</span>', 'top-right', '5000', 'danger', 'fa-warning', true, true)
			}

			$('#calendar').fullCalendar( 'refetchEvents' );
		}
	})
}

function calendarEventDestroy( event, element, view )
{
	// Удаляем popover
	element.popover('destroy');
}

// Отмена опции "Весь день"
function cancelAllDay(windowId)
{
	// Если выбран параметр "Весь день", снимаем его
	if ($('#' + windowId + " input[name='all_day']").prop("checked"))
	{
		$('#' + windowId + " input[name='all_day']").prop("checked", false);

		$('#' + windowId +  " select[name='duration_type']").parent("div").removeClass("invisible");
		$('#' + windowId +  " input[name='duration']").parent("div").removeClass("invisible");

		var formatDateTimePicker = "DD.MM.YYYY HH:mm:ss";

		$('#' + windowId +  ' input[name="start"]').parent().data("DateTimePicker").format(formatDateTimePicker);
		$('#' + windowId +  ' input[name="finish"]').parent().data("DateTimePicker").format(formatDateTimePicker);
	}
}

function setDuration(start, end, windowId)
{
	var duration = 0,
		start = Math.floor(start / 1000) * 1000,
		end = Math.floor(end / 1000) * 1000,
		durationInMinutes = (end > start) ? Math.floor((end - start) / 1000 / 60) : 0;

	if (durationInMinutes)
	{
		// Дни
		if ((durationInMinutes / 60) % 24 == 0)
		{
			durationType = 2;
			duration = durationInMinutes / 60 / 24;
		}
		else if (durationInMinutes % 60 == 0 ) // Часы
		{
			durationType = 1;
			duration = durationInMinutes / 60;
		}
		else
		{
			durationType = 0;
			duration = durationInMinutes;
		}

		$('#' + windowId +  " select[name='duration_type']").val(durationType);
	}

	$('#' + windowId +  " input[name='duration']").val(duration);
}

//
function changeDuration(event)
{
	var startTimeCell = +$('#' + event.data.windowId + " #" + event.data.cellId).attr("start_timestamp") - event.data.timeZoneOffset,
		stopTimeCell = startTimeCell + getDurationMilliseconds(event.data.windowId);

	// Изменяем значение поля даты-времени завершения
	$('#' + event.data.windowId + ' input[name="finish"]').parent().data("DateTimePicker").date(new Date(stopTimeCell));
}

// Получение продолжительности события в миллисекундах
function getDurationMilliseconds(windowId)
{
	var duration = +$('#' + windowId + ' input[name="duration"]').val(), // продолжительность
		durationType = +$('#' + windowId + ' select[name="duration_type"]').val(), // тип интервала продолжительности
		durationMillisecondsCoeff = 1000 * 60, // минуты
		additionalForAllDay = $('#' + windowId + " input[name='all_day']").prop("checked") ? (60 * 1000) : 0;

	switch (durationType)
	{
		case 1: // часы

			durationMillisecondsCoeff *= 60;
			additionalForAllDay *= 60
			break;

		case 2: // дни

			durationMillisecondsCoeff *= 60 * 24;
			break;
	}

	if (additionalForAllDay)
	{
		additionalForAllDay -= 1;
	}

	return duration * durationMillisecondsCoeff + additionalForAllDay;
}

function setStartAndFinish(start, end, windowId)
{

	$('#' + windowId + ' input[name="start"]').parent().data("DateTimePicker").date(new Date(start));
	$('#' + windowId + ' input[name="finish"]').parent().data("DateTimePicker").date(new Date(end));

	setEventStartButtons(start, windowId);
}

// Установка быстрых кнопок начала события
function setEventStartButtons(start, windowId)
{
	var oCurrentDate = new Date(),
		millisecondsDay = 3600 * 24 * 1000,
		aDates = []; // массив дат - сегодня, завтра, послезавтра и т.д.

	for (var i = 0; i < 4; i++)
	{
		var oTmpDate = new Date(+oCurrentDate + millisecondsDay * i);

		aDates.push(new Date(oTmpDate.getFullYear(), oTmpDate.getMonth(), oTmpDate.getDate()));
	}

	var oCurrentStartDate = new Date(start),
		oCurrentStartDateWithoutTime = new Date(oCurrentStartDate.getFullYear(), oCurrentStartDate.getMonth(), oCurrentStartDate.getDate());

	if (aDates.length)
	{
		// Дата начала события находится в диапозоне дат "сегодя и через 2 дня",
		if (+oCurrentStartDateWithoutTime >= +aDates[0] && +oCurrentStartDateWithoutTime <= +aDates[aDates.length - 1])
		{
			aDates.forEach(function (date, index){

				if (+date == +oCurrentStartDateWithoutTime)
				{
					var eventButton = $('#' + windowId + ' #eventStartButtonsGroup a[data-start-day=' + index  + ']:not(.active)');

					if (eventButton.length)
					{
						$(eventButton.eq(0))
							.addClass("active")
							.siblings(".active")
							.removeClass("active");
					}
				}
			});
		}
		else
		{
			$('#' + windowId + ' #eventStartButtonsGroup a.active').removeClass("active");
		}
	}
}