(function(jQuery){
	// Функции без создания коллекции
	jQuery.extend({
		addIntoCart: function(path, shop_item_id, count){
			jQuery.clientRequest({
				path: path,
				data: {add: shop_item_id, count: count},
				callBack: jQuery.addIntoCartCallback,
				context: jQuery('#little_cart')
			});
			return false;
		},
		addIntoCartCallback: function(data, status, jqXHR)
		{
			jQuery.loadingScreen('hide');
			jQuery(this).replaceWith(data);
		},
		addCompare: function(path, shop_item_id, object){
			jQuery(object).toggleClass('current');
			jQuery.clientRequest({path: path + '?compare=' + shop_item_id, 'callBack': function(){
					jQuery.loadingScreen('hide');
				}, context: jQuery(object)});
			jQuery('#compareButton').show();
			return false;
		},
		addFavorite: function(path, shop_item_id, object){
			jQuery(object).toggleClass('favorite_current');

			jQuery.clientRequest({
				path: path,
				data: {favorite: shop_item_id},
				callBack: jQuery.addFavoriteCallback,
				context: jQuery('#little_favorite')
			});

			return false;
		},
		addFavoriteCallback: function(data, status, jqXHR)
		{
			jQuery.loadingScreen('hide');
			jQuery(this).replaceWith(data);
		},

		sendVote: function(id, vote, entity_type){
			jQuery.clientRequest({path: '?id=' + id + '&vote=' + vote + '&entity_type=' + entity_type , 'callBack': jQuery.sendVoteCallback});
			return false;
		},
		sendVoteCallback: function(data, status)
		{
			jQuery.loadingScreen('hide');
			jQuery('#' + data.entity_type + '_id_' + data.item).removeClass("up down");
			if (!data.delete_vote)
			{
				data.value == 1
				? jQuery('#' + data.entity_type + '_id_' + data.item).addClass("up")
				: jQuery('#' + data.entity_type + '_id_' + data.item).addClass("down");
			}

			jQuery('#' + data.entity_type + '_rate_' + data.item).text(data.rate);
			jQuery('#' + data.entity_type + '_likes_' + data.item).text(data.likes);
			jQuery('#' + data.entity_type + '_dislikes_' + data.item).text(data.dislikes);
		}
	});

	jQuery.fn.messageTopicsHostCMS = function(settings) {
		// Настройки
		settings = jQuery.extend({
			timeout: 10000, // Таймаут обновлений
			data: '#messages_data', // блок с данными переписки для обновления
			url: '#url', // значение URL
			page: '#page', // значение total
			message_field: 'textarea', // поле ввода сообщения
			page_link: '.page_link', // ссылки на страницы
			keyToSend: 13 // Отправка сообщения
		}, settings);

		var Obj = jQuery.extend({
				_url :			this.find(settings.url).text(),
				_page :			parseInt(this.find(settings.page).text()) + 1,
				oData :			this.find(settings.data),
				oForm :			this.find('form'),
				oField :		this.find(settings.message_field),	// поле ввода сообщения
				oPage :			this.find(settings.page_link),	// ссылки на страницы
				oTemp :			{} // блок временных данных
			}, this);

		function _start() {
			if (Obj.length == 1) {
				// обновление данных по таймауту
				setInterval(_ajaxLoad, settings.timeout);

				Obj.oField.keydown(function(e) {
					if (e.ctrlKey && e.keyCode == settings.keyToSend) Obj.oForm.submit();
				});

				// отправка формы по Ctrl+Enter
				Obj.oField.keydown(function(e) {
					if (e.ctrlKey && e.keyCode == settings.keyToSend) Obj.oForm.submit();
				});

				// отправка сообщения из формы
				Obj.oForm.submit(function() {
					if (Obj.oField.val().trim().length) {
						_ajaxLoad({form : Obj.oForm.serialize()});
						Obj.oForm.find(':input:not([type=submit],[type=button])').each(function(){jQuery(this).val('')});
					}
					return false;
				});
			}
			return false;
		}

		// Ajax запрос
		function _ajaxLoad(data) {
			if (!data) data = {};
			form = data.form ? '&' + data.form : '';

			return jQuery.ajax({
				url : Obj._url + 'page-' + Obj._page + '/',
				type : 'POST',
				data : 'ajaxLoad=1' + form,
				dataType : 'json',
				success :	function (ajaxData) {
					Obj.oData.html(jQuery(ajaxData.content).find(settings.data).html());
				},
				error : function (){return false}
			});
		}
		return this.ready(_start);
	};

	jQuery.fn.messagesHostCMS = function(settings) {
	//jQuery.extend({
		//messagesHostCMS: function(settings){
			// Настройки
			settings = jQuery.extend({
				chat_height :					465, // Высота чата переписки
				timeout :							10000, // Таймаут обновлений
				load_messages :				'#load_messages', // кнопка подгрузки старых сообщений
				chat_window :					'#chat_window', // окно чата переписки
				messages :						'#messages', // список сообщений чата
				prefix_message_id :		'msg_', // префикс идентификатора сообщения в DOM
				message_field :				'textarea', // поле ввода сообщения
				url :									'#url', // значение URL
				limit :								'#limit', // значение limit
				total :								'#total', // значение total
				topic_id :						'#topic_id', // значение message_topic id
				keyToSend :						13 // Отправка сообщения
			}, settings);

		var Obj = jQuery.extend({
				_activity :		1,
				_autoscroll :	1,
				_url :				this.find(settings.url).text(),
				_limit :			this.find(settings.limit).text(),
				_total :			this.find(settings.total).text(),
				_topic_id :		this.find(settings.topic_id).text(),
				_count_msg :	0, // количество сообщений в чате
				oLoad :				this.find(settings.load_messages), // кнопка подгрузки старых сообщений
				oWindow :			this.find(settings.chat_window), // окно чата переписки
				oMessages :		this.find(settings.messages), // список сообщений чата
				oField :			this.find(settings.message_field),	// поле ввода сообщения
				oForm :				this.find('form'),
				oTemp :				{} // блок временных данных
			}, this);

		function _start() {
			if (Obj.length == 1) {
				_recountChat();

				// обновление данных по таймауту
				setInterval(_ajaxLoad, settings.timeout);

				// проверка активности пользователя
				jQuery("body").mousemove(function(){
					Obj._activity = Obj._autoscroll == 1 ? 1 : 0;
				});

				// отправка формы по Ctrl+Enter
				Obj.oField.keydown(function(e) {
					if (e.ctrlKey && e.keyCode == settings.keyToSend) Obj.oForm.submit();
				});

				Obj.oWindow.scroll(function(){
					Obj._autoscroll = Obj.oMessages.height() == Obj.oWindow.scrollTop() + settings.chat_height ? 1 : 0;
				});

				// отправка сообщения из формы
				Obj.oForm.submit(function() {
					if (Obj.oField.val().trim().length) {
						Obj._autoscroll = 1;
						Obj._activity = 1;
						_ajaxLoad({form : Obj.oForm.serialize()});
						Obj.oField.val('');
					}
					return false;
				});

				Obj.oLoad.click(function(){
					_ajaxLoad({
						preload : true,
						page : 'page-' + parseInt(Obj._count_msg / Obj._limit + 1)
					})
				});
			}
			return false;
		}

		// Ajax запрос
		function _ajaxLoad(data) {
			if (!data) data = {};
			page = data.page ? data.page + '/' : '';
			form = data.form ? '&' + data.form : '';
			return jQuery.ajax({
				url : Obj._url + Obj._topic_id + '/' + page,
				type : 'POST',
				data : 'ajaxLoad=1&activity=' + Obj._activity + form,
				dataType : 'json',
				success :	function (ajaxData) {
					Obj.oTemp = jQuery(ajaxData.content);

					/*if (!data.preload && Obj._count_msg > Obj._limit)
					{
						Obj.oTemp.find(settings.messages + ' > :first[id^='+settings.prefix_message_id+']').remove();
					}*/

					// замена сообщений чата
					Obj.oTemp.find(settings.messages + ' > [id^='+settings.prefix_message_id+']').each(function(){
						oMsg = Obj.oMessages.find('[id="' + jQuery(this).attr('id') +'"]');
						if (oMsg.length == 1) oMsg.replaceWith(jQuery(this));
					});

					newMessages = Obj.oTemp.find(settings.messages + ' > [id^='+settings.prefix_message_id+']');
					if (newMessages.length) {
						if (data.preload){
							Obj.oMessages.prepend(newMessages);
							Obj._autoscroll = 0;
							Obj.oWindow.scrollTop(0);
						}
						else {
							Obj.oMessages.append(newMessages);
						}
						_recountChat();
					}
				},
				error : function (){return false}
			});
		}

		function _recountChat() {
			if (Obj.oWindow.height() > settings.chat_height) Obj.oWindow.height(settings.chat_height + 'px');
			if (Obj._autoscroll == 1) Obj.oWindow.scrollTop(Obj.oMessages.height() - settings.chat_height);
			if (Obj.oTemp.length) Obj._total = Obj.oTemp.find(settings.total).text();
			Obj._count_msg = Obj.oMessages.find('> *[id^='+settings.prefix_message_id+']').length;
			if (Obj._count_msg >= Obj._total && Obj.oLoad.is(':visible')) Obj.oLoad.toggle();
			Obj._activity = 0;
		}

		return this.ready(_start);
	};
})(jQuery);

/**
* Склонение после числительных
* int number числительное
* int nominative Именительный падеж
* int genitive_singular Родительный падеж, единственное число
* int genitive_plural Родительный падеж, множественное число
*/
function declension(number, nominative, genitive_singular, genitive_plural)
{
	var last_digit = number % 10, last_two_digits = number % 100, result;

	if (last_digit == 1 && last_two_digits != 11)
	{
		result = nominative;
	}
	else
	{
		if ((last_digit == 2 && last_two_digits != 12) || (last_digit == 3 && last_two_digits != 13) || (last_digit == 4 && last_two_digits != 14))
		{
			result = genitive_singular;
		}
		else
		{
			result = genitive_plural;
		}
	}

	return result;
}