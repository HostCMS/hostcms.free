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

			jQuery(this).empty();
			for (var key in data)
			{
				jQuery(this).append(jQuery('<option>').attr('value', key.substr(1)).text(data[key]));
			}
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
		loadLocations: function(path, shop_country_id)
		{
			$('#shop_country_location_city_id').clearSelect();
			$('#shop_country_location_city_area_id').clearSelect();
			$.clientRequest({path: path + '?ajaxLoad&shop_country_id=' + shop_country_id, 'callBack': $.clientSelectOptionsCallback, context: $('#shop_country_location_id')});
		},
		loadCities: function(path, shop_country_location_id)
		{
			$('#shop_country_location_city_area_id').clearSelect();
			$.clientRequest({path: path + '?ajaxLoad&shop_country_location_id=' + shop_country_location_id, 'callBack': $.clientSelectOptionsCallback, context: $('#shop_country_location_city_id')});
		},
		loadCityAreas: function(path, shop_country_location_city_id)
		{
			$.clientRequest({path: path + '?ajaxLoad&shop_country_location_city_id=' + shop_country_location_city_id, 'callBack': $.clientSelectOptionsCallback, context: $('#shop_country_location_city_area_id')});
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
				$('select[name = shop_country_location_id]')
					.find('option[value = "' + data.result.shop_country_location_id + '"]')
					.prop("selected", true);

				for (var key in data.cities)
				{
					jQuery(this).append(jQuery('<option>').attr('value', key.substr(1)).text(data.cities[key]));
				}
				
				$('select[name = shop_country_location_city_id]')
					.find('option[value = "' + data.result.shop_country_location_city_id + '"]')
					.prop("selected", true);				
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

function set_count_mod(input_id, step)
{
	var oCountMod = document.getElementById(input_id);

	if (!(iCurrCount = parseInt(oCountMod.value))) {
		iCurrCount = 0;
	}

	if (!(iCurrCount <= 0 && step < 0)) {
			oCountMod.value = iCurrCount + step;
	}
}

// Отображает/скрывает окно
function SlideWindow(windowId)
{
	var windowDiv = document.getElementById(windowId);

	if (windowDiv == undefined)
	{
		return false;
	}

	if (windowDiv.style.display == "block")
	{
		HideWindow(windowId);
	}
	else
	{
		ShowWindow(windowId);
	}
}

// Магазин
function doSetLocation(shop_country_id, path)
{
	var req = new JsHttpRequest();

	// Отображаем экран загрузки
	$.loadingScreen('show');

	req.onreadystatechange = function()
	{
		if (req.readyState == 4)
		{
			// Убираем затемнение.
			$.loadingScreen('hide');

			if (req.responseJS != undefined)
			{
				// Данные.
				if (req.responseJS.result != undefined)
				{
					oSelect = document.getElementById(location_select_id);

					// Очищаем select
					oSelect.options.length = 0;

					// Добавляем значение " ... "
					oSelect.options[oSelect.options.length] = new Option(" ... ", 0);

					for (var key in req.responseJS.result)
					{
						oSelect.options[oSelect.options.length] = new Option(req.responseJS.result[key], key.substr(1));
					}

					// Устанавливаем города
					//doSetCity(oSelect.options[oSelect.selectedIndex].value);
					oCity = document.getElementById(city_select_id);
					oCity.options.length = 0;
					oCity.options[oCity.options.length] = new Option(" ... ", 0);

					oCityarea = document.getElementById(cityarea_select_id);
					oCityarea.options.length = 0;
					oCityarea.options[oCityarea.options.length] = new Option(" ... ", 0);
				}
			}
			return true;
		}
	}

	req.open('get', path + "?action=get_location&shop_country_id="+shop_country_id, true);

	// Отсылаем данные в обработчик.
	req.send(null);
}

function doSetCity(shop_location_id, path)
{
	var req = new JsHttpRequest();

	// Отображаем экран загрузки
	$.loadingScreen('show');

	req.onreadystatechange = function()
	{
		if (req.readyState == 4)
		{
			// Убираем затемнение.
			$.loadingScreen('hide');

			if (req.responseJS != undefined)
			{
				// Данные.
				if (req.responseJS.result != undefined)
				{
					oSelect = document.getElementById(city_select_id);

					// Очищаем select
					oSelect.options.length = 0;

					// Добавляем значение " ... "
					oSelect.options[oSelect.options.length] = new Option(" ... ", 0);

					for (var key in req.responseJS.result)
					{
						oSelect.options[oSelect.options.length] = new Option(req.responseJS.result[key], key.substr(1));
					}

					// Устанавливаем районы
					//doSetCityArea(oSelect.options[oSelect.selectedIndex].value);

					oCityarea = document.getElementById(cityarea_select_id);
					oCityarea.options.length = 0;
					oCityarea.options[oCityarea.options.length] = new Option(" ... ", 0);
				}
			}
			return true;
		}
	}

	req.open('get', path + "?action=get_city&shop_location_id="+shop_location_id, true);

	// Отсылаем данные в обработчик.
	req.send(null);
}

function doSetCityArea(shop_city_id, path)
{
	var req = new JsHttpRequest();

	// Отображаем экран загрузки
	$.loadingScreen('show');

	req.onreadystatechange = function()
	{
		if (req.readyState == 4)
		{
			// Убираем затемнение.
			$.loadingScreen('hide');

			if (req.responseJS != undefined)
			{
				// Данные.
				if (req.responseJS.result != undefined)
				{
					oSelect = document.getElementById(cityarea_select_id);

					// Очищаем select
					oSelect.options.length = 0;

					// Добавляем значение " ... "
					oSelect.options[oSelect.options.length] = new Option(" ... ", 0);

					for (var key in req.responseJS.result)
					{
						oSelect.options[oSelect.options.length] = new Option(req.responseJS.result[key], key.substr(1));
					}
				}
			}
			return true;
		}
	}

	req.open('get', path + "?action=get_cityarea&shop_city_id="+shop_city_id, true);

	// Отсылаем данные в обработчик.
	req.send(null);
}

// Плавающие блоки
// получаем исходную позицию плавающего блока
function GetStyle(drag_object, axis)
{
	var str_value = "";

	if(document.defaultView && document.defaultView.getComputedStyle)
	{
		var css = document.defaultView.getComputedStyle(drag_object, null);
		str_value = css ? css.getPropertyValue(axis) : null;
	}
	else if(drag_object.currentStyle)
	{
		str_value = drag_object.currentStyle[axis];

		if (str_value == 'auto')
		{
			if (axis == 'top')
			{
				str_value = drag_object.offsetTop;
			}
			else
			{
				str_value = drag_object.offsetLeft;
			}
		}
	}

	return str_value;
}

function SetGradeMessage(message_id, grade_val)
{
	// Запрос backend-у
	var req = new JsHttpRequest();

	// Отображаем экран загрузки
	$.loadingScreen('show');

	req.onreadystatechange = function()
	{
		if (req.readyState == 4)
		{
			// Убираем затемнение.
			$.loadingScreen('hide');

			return true;
		}
	}

	req.open('get', "./?action=set_message_grade&helpdesk_message_id="+message_id+"&grade="+grade_val, true);

	// Отсылаем данные в обработчик.
	req.send(null);
}

/**
 * Обновление картинки CAPTCHA
 * captchaKey - идентификатор CAPTCHA
 * captchaHeight - высота картинки с CAPTCHA
 */
function ReNewCaptcha(captchaKey, captchaHeight)
{
	if (document.images['captcha'] != undefined)
	{
		var antiCache = Math.floor(Math.random()*100000);
		document.images['captcha'].src = "/captcha.php?get_captcha=" + captchaKey + "&height=" + captchaHeight + "&anc=" + antiCache;
	}
}

/**
 * Обновление картинки CAPTCHA для картинки по ее ID
 * captchaKey - идентификатор CAPTCHA
 * captchaHeight - высота картинки с CAPTCHA
 */
function ReNewCaptchaById(imageId, captchaKey, captchaHeight)
{
	$('#'+imageId).updateCaptcha(captchaKey, captchaHeight);
}

// Отображает/скрывает блок
function ShowHide(divId)
{
	var windowDiv = document.getElementById(divId);

	if (windowDiv == undefined)
	{
		return false;
	}

	if (windowDiv.style.display == "block")
	{
		windowDiv.style.display = 'none';
	}
	else
	{
		windowDiv.style.display = 'block';
	}
}

//Функция обратного вызова при загрузке формы добавления на доску объявлений
function callbackfunction_showFormAddItem(responseJS)
{
	if (typeof responseJS != 'undefined')
	{
		$.loadingScreen('hide');

		// Данные.
		if (typeof responseJS.result != 'undefined')
		{
			html = responseJS.result;

			document.getElementById('AddItemForm').innerHTML = html;

			// Выполняем скрипты из полученного с сервера HTML-а
			runScripts(document.getElementById('AddItemForm').getElementsByTagName('SCRIPT'));

			// Очищаем поле сообщений
			var div_id_message = document.getElementById('AddItemMessage');

			if (div_id_message)
			{
				div_id_message.innerHTML = '';
			}

			//cr('AddItemForm');
		}
	}
}

//Функция обратного вызова при отправке добавления на доску объявлений
function callbackfunction_SendFormItem(responseJS)
{
	if (typeof responseJS != 'undefined')
	{
		if (responseJS.message != 'undefined')
		{
			var div_id_message = document.getElementById('AddItemMessage');

			if (div_id_message)
			{
				div_id_message.innerHTML = responseJS.message;

				// Выполняем скрипты из полученного с сервера HTML-а
				runScripts(div_id_message.getElementsByTagName('SCRIPT'));

				// Переходим к сообщению
				window.location.href = (window.location.href.indexOf('#') >= 0 ? window.location.href : window.location.href + '#FocusAddItemMessage');
			}
		}
	}
}

function ShowImgWindow(title, src, width, height)
{
	obj = window.open("", "", "scrollbars=0,dialog=0,minimizable=1,modal=1,width="+width+",height="+height+",resizable=0");
	obj.document.write("<html>");
	obj.document.write("<head>");
	obj.document.write("<title>"+title+"</title>");
	obj.document.write("</head>");
	obj.document.write("<body topmargin=0 leftmargin=0 marginwidth=0 marginheight=0>");
	obj.document.write("<img src=\""+src+"\" width=\""+width+"\" height=\""+height+"\" />");
	obj.document.write("</body>");
	obj.document.write("</html>");
	obj.document.close();
}

function getElementsByName_iefix(tag, name)
{
	var elem = document.getElementsByTagName(tag);
	var arr = new Array();

	var iarr = 0;

	for(i = 0; i < elem.length; i++)
	{
		att = elem[i].getAttribute("name");

		if(att == name)
		{
			arr[iarr] = elem[i];
			iarr++;
		}
	}
	return arr;
}

// Изменение высоты блока
function changeHeightFloatBlockBorder (oBorder, iHeightAttribute, iStyleTop)
{
	var iElementHeight = 0;

	for (i = 0; i < oBorder.length; i++)
	{
		iElementHeight = oBorder[i].parentNode.offsetHeight + iHeightAttribute;

		if (iElementHeight > 0)
		{
			oBorder[i].style.height = iElementHeight + 'px';
			oBorder[i].style.top = iStyleTop + 'px';
		}
	}
}

// -- Forum --
function HideShow(id, id1)
{
	$("#"+id).css('display', 'none');
	$("#"+id1).css('display', 'block');
}

// Скрипт открывает/скрывает форумы текущей группы
function ShowForums(up,id,count)
{
	var down,up;
	if (up == 0)
	{
		down = "none";
		up = "";
	}
	else
	{
		down = "";
		up = "none";
	}

	$("#down_"+id).css('display', down);
	$("#up_"+id).css('display', up);

	for(var i=1; i <= count; i++)
	{
		$("#"+id+"_"+i).css('display', up);
	}
}
// --/ Forum --