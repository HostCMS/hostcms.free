(function($) {
	// Предварительная загрузка изображений
	var cache = [];

	$.extend({
	preLoadImages: function()
	{
		var args_len = arguments.length;

		for (var i = args_len; i--;)
		{
			var cacheImage = document.createElement('img');
			cacheImage.src = arguments[i];
			cache.push(cacheImage);
		}
	}
	});

	$.preLoadImages("/hostcmsfiles/images/ajax_loader.gif");

	$(document).keydown(function(event) {
		if (event.ctrlKey && !$(document.activeElement).is(":input"))
		{
			switch (event.which)
			{
				case 0x25: // Назад
					if ($('#id_prev').length)
					{
						window.location = $('#id_prev').attr('href');
					}
				break;
				case 0x27: // Вперед
					if ($('#id_next').length)
					{
						window.location = $('#id_next').attr('href');
					}
				break;
			}
		}
	});

	$.fn.extend({
		applyShadow: function()
		{
			return this.each(function(index, object){
				var obj = $(object);

				$('<div>').attr("class", 'tl').appendTo(obj);
		    	$('<div>').attr("class", 't')
		    	.height(15)
		    	.appendTo(obj);

		    	$('<div>').attr("class", 'tr').appendTo(obj);
		    	$('<div>').attr("class", 'l')
		    	.width(17)
		    	.appendTo(obj);

		    	$('<div>').attr("class", 'r')
		    	.width(17)
		    	.appendTo(obj);

		    	$('<div>').attr("class", 'bl').appendTo(obj);

		    	$('<div>').attr("class", 'b')
		    	.height(21)
		    	.appendTo(obj);

		    	$('<div>').attr("class", 'br').appendTo(obj);
			});
		},
		updateCaptcha: function(captchaKey, captchaHeight) {
			return this.each(function(index, object) {
				jQuery(object).prop('src', "/captcha.php?get_captcha=" + captchaKey + "&height=" + captchaHeight + "&anc=" + Math.floor(Math.random()*100000));
			});
		},
		clearSelect: function()
		{
			return this.each(function(index, object){
				jQuery(object).empty().append(jQuery('<option>').attr('value', 0).text('…'));
			});
		}
	});

	var methods = {
		show : function() {
			$('body').css('cursor', 'wait');
			var fade_div = $('#ajaxLoader'), jWindow = $(window);
			if (fade_div.length === 0)
			{
				fade_div = $('<div></div>')
					.appendTo(document.body)
					.hide()
					.prop('id', 'ajaxLoader')
					.css('z-index', '1500')
					.css('position', 'absolute')
					.append($('<img>').prop('src', '/hostcmsfiles/images/ajax_loader.gif'));
			}

			fade_div.show()
				.css('top', (jWindow.height() - fade_div.outerHeight(true)) / 2 + jWindow.scrollTop())
				.css('left', (jWindow.width() - fade_div.outerWidth(true)) / 2 + jWindow.scrollLeft());
		},
		hide : function( ) {
			$('#ajaxLoader').hide().css('left', -1000);
			$('body').css('cursor', 'auto');
		}
	};

	// Функции без создания коллекции
	jQuery.extend({
		loadingScreen: function(method) {
			// Method calling logic
			if (methods[method] ) {
			  return methods[method].apply(this, Array.prototype.slice.call( arguments, 1 ));
			} else {
			  $.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
			}
		},
		clientSelectOptionsCallback: function(data, status, jqXHR) {
			$.loadingScreen('hide');

			var $object = jQuery(this);

			$object.empty();
			for (var key in data)
			{
				$object.append(jQuery('<option>').attr('value', key.substr(1)).text(data[key]));
			}
			$object.change();
		},
		clientRequest: function(settings) {
			if (typeof settings.callBack == 'undefined')
			{
				alert('Callback function is undefined');
			}

			$.loadingScreen('show');

			var path = settings.path,
				data = (typeof settings.data != 'undefined') ? settings.data : {};

			data['_'] = Math.round(new Date().getTime());

			jQuery.ajax({
				context: settings.context,
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: settings.callBack
			});
			return false;
		},
		loadLocations: function(path, shop_country_id, callback)
		{
			$('#shop_country_location_city_id').clearSelect();
			$('#shop_country_location_city_area_id').clearSelect();
			$.clientRequest({path: path + '?ajaxLoad&shop_country_id=' + shop_country_id, 'callBack': [$.clientSelectOptionsCallback, callback], context: $('#shop_country_location_id')});
		},
		loadCities: function(path, shop_country_location_id, callback)
		{
			$('#shop_country_location_city_area_id').clearSelect();
			$.clientRequest({path: path + '?ajaxLoad&shop_country_location_id=' + shop_country_location_id, 'callBack': [$.clientSelectOptionsCallback, callback], context: $('#shop_country_location_city_id')});
		},
		loadCityAreas: function(path, shop_country_location_city_id, callback)
		{
			$.clientRequest({path: path + '?ajaxLoad&shop_country_location_city_id=' + shop_country_location_city_id, 'callBack': [$.clientSelectOptionsCallback, callback], context: $('#shop_country_location_city_area_id')});
		},
		loadCityByName: function(shopCountryId, cityName, cartUrl)
		{
			$('#shop_country_location_city_area_id').clearSelect();
			$.clientRequest({path: cartUrl + '?ajaxLoad&shop_country_id=' + shopCountryId + '&city_name=' + cityName, 'callBack': $.loadCityByNameCallback, context: $('#shop_country_location_city_id')});
		},
		loadCityByNameCallback: function(data, status, jqXHR) {
			$.loadingScreen('hide');

			if (data.result)
			{
				var $object = jQuery(this);

				$('select[name = shop_country_location_id]')
					.find('option[value = "' + data.result.shop_country_location_id + '"]')
					.prop("selected", true);

				for (var key in data.cities)
				{
					$object.append(jQuery('<option>').attr('value', key.substr(1)).text(data.cities[key]));
				}

				$('select[name = shop_country_location_city_id]')
					.find('option[value = "' + data.result.shop_country_location_city_id + '"]')
					.prop("selected", true);

				$object.change();
			}
		},
		friendOperations: function(data, status, jqXHR) {
			$.loadingScreen('hide');
			var $this = jQuery(this);

			switch (data)
			{
				case 'Added':
					$this.text('Запрос на добавление в друзья отправлен.').prop("onclick", null);
				break;
				case 'Removed':
					$this.text('Пользователь убран из друзей.').prop("onclick", null);
				break;
			}
		}
	});
})(jQuery);


// -- Forum --
function HideShow(id, id1)
{
	$("#"+id).css('display', 'none');
	$("#"+id1).css('display', 'block');
}

// --/ Forum --