$(function() {
    $("#datepicker_cart_time_from").wickedpicker({
		twentyFour: true, //Display 24 hour format, defaults to false
		upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
		downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
		close: 'wickedpicker__close', //The close class selector to use, for custom CSS
		hoverState: 'hover-state', //The hover state class to use, for custom CSS
		title: 'Время', //The Wickedpicker's title,
		showSeconds: false, //Whether or not to show seconds,
		timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
		secondsInterval: 1, //Change interval for seconds, defaults to 1,
		minutesInterval: 1, //Change interval for minutes, defaults to 1
		clearable: false //Make the picker's input clearable (has clickable 'x')        
    });
    
    $("#datepicker_cart_time_to").wickedpicker({
        now: new Date(new Date().getTime() + 2 * 60 * 60 * 1000).toLocaleTimeString(), // + 2 hours
		twentyFour: true, //Display 24 hour format, defaults to false
		upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
		downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
		close: 'wickedpicker__close', //The close class selector to use, for custom CSS
		hoverState: 'hover-state', //The hover state class to use, for custom CSS
		title: 'Время', //The Wickedpicker's title,
		showSeconds: false, //Whether or not to show seconds,
		timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
		secondsInterval: 1, //Change interval for seconds, defaults to 1,
		minutesInterval: 1, //Change interval for minutes, defaults to 1
		clearable: false //Make the picker's input clearable (has clickable 'x')        
    });    
});

function AddIntoCart(shop_path, item_id, item_count)
{
	cmsrequest = shop_path + 'cart/?add=' + item_id + '&count=' + item_count;
	sendRequest(cmsrequest, 'get', callbackfunction_AddIntoCart);
	return false;
}

// Функция обратного вызова для AddIntoCart
function callbackfunction_AddIntoCart(responseJS)
{
	// Результат принят
	sended_request = false;

	if (typeof responseJS != 'undefined')
	{
		// Данные.
		if (typeof responseJS.cart != 'undefined')
		{
			var little_cart = document.getElementById('little_cart');

			if (little_cart)
			{
				// Создадим скрытый SPAN для IE, в который поместим текст + скрипт.
				// Если перед <script> не будет текста, нехороший IE не увидит SCRIPT
				var span = document.createElement("span");
				span.style.display = 'none';
				span.innerHTML = "Stupid IE. " + responseJS.cart;

				runScripts(span.getElementsByTagName('SCRIPT'));

				little_cart.innerHTML = responseJS.cart;
			}
			else
			{
				alert('Ошибка! Краткая корзина не найдена');
			}
		}
	}
}

// Отображение экрана загрузки AJAX
function ShowLoadingScreen()
{
	$.loadingScreen('show');
}

// Скрытие экрана загрузки AJAX.
function HideLoadingScreen()
{
	$.loadingScreen('hide');
}

function AddIntoCart(shop_path, item_id, item_count)
{
	cmsrequest = shop_path + 'cart/?ajax_add_item_id=' + item_id + '&count=' + item_count;

	sendRequest(cmsrequest, 'get', callbackfunction_AddIntoCart);

	return false;
}

// Функция обратного вызова для AddIntoCart
function callbackfunction_AddIntoCart(responseJS)
{
	// Результат принят
	sended_request = false;

	if (typeof responseJS != 'undefined')
	{
		// Данные.
		if (typeof responseJS.cart != 'undefined')
		{
			var little_cart = document.getElementById('little_cart');

			if (little_cart)
			{
				// Создадим скрытый SPAN для IE, в который поместим текст + скрипт.
				// Если перед <script> не будет текста, нехороший IE не увидит SCRIPT
				var span = document.createElement("span");
				span.style.display = 'none';
				span.innerHTML = "Stupid IE. " + responseJS.cart;

				runScripts(span.getElementsByTagName('SCRIPT'));

				little_cart.innerHTML = responseJS.cart;
			}
			else
			{
				alert('Ошибка! Краткая корзина не найдена');
			}
		}
	}
}

// Ответ на комментарий
var prev_comment = 0;
function cr(comment_id)
{
	if (prev_comment && prev_comment != comment_id)
	{
		$('#'+prev_comment).toggle('slow');
	}
	$('#'+comment_id).toggle('slow');
	prev_comment = comment_id;
}

var temp_ChildId = '';
var temp_CurrenElementId = '';
var menu_timeout_id = 0;
var filter_timeout_id = 0;

// обработчик наведения мыши на меню
function TopMenuOver(CurrenElementId, ChildId)
{
	clearTimeout(menu_timeout_id);

	if (temp_CurrenElementId != ''
	&& temp_CurrenElementId != CurrenElementId)
	{
		var oTemp_ChildId = document.getElementById(temp_ChildId);

		if (oTemp_ChildId)
		{
			oTemp_ChildId.style.display = "none";
		}
	}

	temp_ChildId = ChildId;
	temp_CurrenElementId = CurrenElementId;

	if (CurrenElementId == undefined)
	{
		return false;
	}

	if (ChildId != '')
	{
		var oChildId = document.getElementById(ChildId);

		if (oChildId)
		{
			oChildId.style.display = "block";
		}
	}
}

// обработчик уведения мыши с меню
function TopMenuOut(CurrenElementId, ChildId)
{
	if (CurrenElementId == undefined)
	{
		return false;
	}

	if (ChildId != '')
	{
		var oChildId = document.getElementById(ChildId);
		if (oChildId)
		{
			menu_timeout_id = setTimeout(function (){oChildId.style.display = "none"}, 300);
		}
	}
}


// массив для хранения текущих рейтингов звезд
var curr_rate = new Array();

// функция работы со звездами рейтинга
function set_rate(id, new_rate)
{
	// устанавливаем атрибуты
	curr_star = document.getElementById(id);
	parent_id = parseInt(curr_star.parentNode.id);

	// при первом пересчете ставим рейтинг для группы звезд в 0
	if (!curr_rate[parent_id])
	{
		curr_rate[parent_id] = 0;
	}

	// устанавливаем новый рейтинг в массив рейтингов и значение скрытого поля
	if (new_rate != curr_rate[parent_id] && parseInt(new_rate) > 0)
	{
		curr_rate[parent_id] = new_rate;
		var curr_form_id = 'comment_form_0' + (parent_id != 0 ? parent_id : '');
		var comment_grade_value = curr_rate[parent_id].charAt(curr_rate[parent_id].length - 1);
		$("#"+curr_form_id+" input[name=grade], #"+curr_form_id+" input[name=comment_grade], #"+curr_form_id+" input[name=shop_comment_grade]").val(comment_grade_value);
	}

	// пересчет стилей для звезд
	for (i = 1; i < 6; i++)
	{
		if (parent_id != 0)
		{
			j = parent_id + '' + i + '_star_' + i;
		}
		else
		{
			j = i + '_star_' + i;
		}

		temp_obj = document.getElementById(j);

		if (new_rate == 0)
		{
			id = curr_rate[parent_id];
		}

		if (parseInt(j) > parseInt(id))
		{
			temp_obj.className = '';
		}
		else
		{
			temp_obj.className = 'curr';
		}
	}
}

// Функция обратного вызова для CheckBlogUrl
function callbackfunction_CheckBlogUrl(responseJS)
{
	// Результат принят
	sended_request = false;

	if (typeof responseJS != 'undefined')
	{
		// Данные.
		if (typeof responseJS.check_url_result != 'undefined')
		{
			var check_url = document.getElementById('check_url');

			if (responseJS.check_url_result == 0)
			{
				check_url.className = 'error';
				html = 'Адрес занят.';
			}
			else
			{
				check_url.className = 'green';
				html = 'Адрес свободен.';
			}

			if (check_url)
			{
				check_url.innerHTML = html;
			}
			else
			{
				alert('Ошибка! Блок для вывода результатов запроса не найден');
			}
		}
	}
}

function CheckBlogUrl(blog_path, blog_url, parent_id, id)
{
	cmsrequest = blog_path + '?ajax_check_blog_url=' + blog_url + '&group_parent_id=' + parent_id + '&group_id=' + id;

	var check_url = document.getElementById('check_url');

	if (check_url)
	{
		check_url.innerHTML = '<img src="/hostcmsfiles/images/ajax_loader_mini.gif"/>';
	}

	// Отправляем запрос backend-у
	sendBackgroundRequest(cmsrequest, 'get', callbackfunction_CheckBlogUrl);

	return false;
}

// Установка или снятие всех флажков для checkbox'ов элементов.
function SelectAllItemsByPrefix(ASelect, prefix)
{
	element_array = document.getElementsByTagName("input");
	if (element_array.length > 0)
	{
		for (var i = 0; i < element_array.length; i++)
		{
			if (element_array[i].name.search(prefix) != -1)
			{
				// Устанавливаем checked
				element_array[i].checked = ASelect;
			}
		}

	}
}

//Проверка ячейки
function FieldCheckEmail(elementId)
{
	return true;
}