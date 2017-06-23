/**
* HostCMS
*
* @author Hostmake LLC, http://www.hostcms.ru/
* @version 5.x
*/

// выполняет скрипты из полученного ответа от сервера
function runScripts(scripts)
{
	if (!scripts)
	{
		return false;
	}

	for (var i = 0; i < scripts.length; i++)
	{
		var thisScript = scripts[i];
		var text;

		if (thisScript.src)
		{
			var newScript = document.createElement("script");
			newScript.type = thisScript.type;
			newScript.language = thisScript.language;

			newScript.src = thisScript.src;
			document.getElementsByTagName('head')[0].appendChild(newScript);
		}
		else if (text = (thisScript.text || thisScript.innerHTML))
		{
			var text = (""+text).replace(/^\s*<!\-\-/, '').replace(/\-\->\s*$/, '');

			var newScript = document.createElement("script");
			newScript.setAttribute("type", "text/javascript");
			newScript.text = text;

			var script_node = document.getElementsByTagName('head')[0].appendChild(newScript);
		}
	}
}

// action - адрес страницы для запрос
// method - GET, POST, null - автоматическое определение
// callback_function - функция обратного вызова, которая будет вызвана после получения ответа от backenad-а
function sendRequest(action, method, callback_function)
{
	var req = new JsHttpRequest();

	// Отображаем экран загрузки
	ShowLoadingScreen();

	// Этот код вызовется автоматически, когда загрузка данных завершится.
	req.onreadystatechange = function()
	{
		if (req.readyState == 4)
		{
			// Убираем затемнение.
			HideLoadingScreen();

			if (typeof callback_function != 'undefined')
			{
				callback_function(req.responseJS);
			}

			return true;
		}
	}

	req.open(method, action, true);

	// Отсылаем данные в обработчик.
	req.send(null);
}

//Отправка формы методом Get или Post
//callback_function функция обратного вызова
//AAdditionalParams - внешние переметры, передаваемые в строку запроса. Должны начинаться с &
//ButtonObject - Объект нажатой кнопки
function AjaxSendForm(callback_function, AAdditionalParams, ButtonObject)
{
	// Объект родительской формы по умолчанию
	var FormNode = ButtonObject.parentNode;

	// Пока родительская форма не является формой
	while (FormNode.nodeName.toLowerCase() != 'form')
	{
		var FormNode = FormNode.parentNode;
	}

	// Получим ID формы (не путать с ID формы центра администрирования)
	FormID = FormNode.id;

	// Пытаемся получить скрытый объект для input-а
	var HiddenInput = document.getElementById(ButtonObject.name);

	// Элемента нет, добавим его
	if (null == HiddenInput && undefined == HiddenInput || HiddenInput.type != 'hidden')
	{
		// Создадим скрытй input, т.к. нажатый не передается в форму
		var ElementInput = document.createElement("input");
		ElementInput.setAttribute("type", "hidden");
		ElementInput.setAttribute("id", ButtonObject.name);
		ElementInput.setAttribute("name", ButtonObject.name);

		// Добавим скрытый Input к форме
		var InputNode = FormNode.appendChild(ElementInput);
	}

	// Сохраним из визуальных редакторов данные
	if (typeof tinyMCE != 'undefined')
	{
		tinyMCE.triggerSave();
	}

	var JsHttpRequestSendForm = new JsHttpRequest();

	// Код вызывается, когда загрузка завершена
	JsHttpRequestSendForm.onreadystatechange = function ()
	{
		if (JsHttpRequestSendForm.readyState == 4)
		{
			// Убираем затемнение.
			HideLoadingScreen();

			if (typeof callback_function != 'undefined')
			{
				callback_function(JsHttpRequestSendForm.responseJS);
			}
			
			return true;
		}
	}

	// Определим action у формы
	// fix bug with IE 6 and getAttribute('') return [object]
	var FormAction = FormNode.attributes['action'].value;

	// Определим метод формы
	var FormMethod = FormNode.getAttribute('method');

	// передача параметров AAdditionalParams сделана явно, а не через hostcmsAAdditionalParams
	FormAction += (FormAction.indexOf('?') >= 0 ? '&' : '?') + AAdditionalParams;

	// Prepare request object (automatically choose GET or POST).
	JsHttpRequestSendForm.open(FormMethod, FormAction, true);

	JsHttpRequestSendForm.send( { query: FormNode } );

	// Отображаем экран загрузки
	ShowLoadingScreen();

	return false;
}

// Отображение экрана загрузки AJAX
function ShowLoadingScreen()
{
	$("body").css("cursor", "wait");
	
	var fade_div = $("#id_admin_forms_fade");
	
	if (fade_div.length == 0)
	{
		// Создаем div
		fade_div = $('<div></div>')
			.appendTo(document.body)
			.hide()
			.attr('id', "id_admin_forms_fade")
			.attr('class', "shadowed")
			.applyShadow()
			.css('z-index', "1500")
			.css('position', "absolute")
			.css('left', "50%")
			.css('top', "50%")
			.append('<img src="/hostcmsfiles/images/ajax_loader.gif" id="id_fade_div_img" />')
			.css('width', "32");
	}

	fade_div
		.show()
		.css('top', ($(window).height() - fade_div.outerHeight(true)) / 2 + $(window).scrollTop())
		.css('left', ($(window).width() - fade_div.outerWidth(true)) / 2 + $(window).scrollLeft());
}

// Скрытие экрана загрузки AJAX.
function HideLoadingScreen()
{
	$("body").css("cursor", "auto");
	$("#id_admin_forms_fade").css('display', 'none');
}

function AddLoadFileField(container_id, inpit_prefix)
{
	cbItem = document.getElementById(container_id);

	if (cbItem)
	{
		// Получаем все input-ы
		element_array = cbItem.getElementsByTagName("input");

		count_input = element_array.length;

		// <br/>
		var ElementBr = document.createElement("br");
		cbItem.appendChild(ElementBr);

		//<input
		var ElementInput = document.createElement("input");
		ElementInput.setAttribute("size", "30");
		ElementInput.setAttribute("name", inpit_prefix + (count_input + 1));
		ElementInput.setAttribute("type", "file");
		ElementInput.setAttribute("title", "Прикрепить файл");
		//ElementInput.setAttribute("style", "margin-bottom: 20px");
		cbItem.appendChild(ElementInput);
	}
}

// action - адрес страницы для запрос
// method - GET, POST, null - автоматическое определение
// callback_function - функция обратного вызова, которая будет вызвана после получения ответа от backenad-а
function sendBackgroundRequest(action, method, callback_function)
{
	var req = new JsHttpRequest();

	// Этот код вызовется автоматически, когда загрузка данных завершится.
	req.onreadystatechange = function()
	{
		if (req.readyState == 4)
		{
			if (typeof callback_function != 'undefined')
			{
				callback_function(req.responseJS);
			}

			return true;
		}
	}

	req.open(method, action, true);

	// Отсылаем данные в обработчик.
	req.send(null);

	ShowLoadingScreen();
}