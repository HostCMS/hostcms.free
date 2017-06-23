/**
 * HostCMS
 *
 * @author Hostmake LLC, http://www.hostcms.ru/
 * @version 6.x
 */

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
		},
		appendInput: function(windowId, ObjectId, InputName, InputValue)
		{
			windowId = getWindowId(windowId);

			var obj = $('#'+windowId+' #'+ObjectId);

			if (obj.length == 1
			&& obj.find("input[name='"+InputName+"']").length === 0)
			{
				$('#'+windowId+' #'+ObjectId).append(
					$('<input>')
					.attr('type', 'hidden')
					.attr('name', InputName)
					.val(InputValue));
			}
		},
		getCurrentWindowId: function()
		{
			return jQuery.data(document.body, 'currentWindowId');
		}
	});

	$.preLoadImages("/admin/images/shadow-b.png",
	"/admin/images/shadow-l.png",
	"/admin/images/shadow-lb.png",
	"/admin/images/shadow-lt.png",
	"/admin/images/shadow-r.png",
	"/admin/images/shadow-rb.png",
	"/admin/images/shadow-rt.png",
	"/admin/images/shadow-t.png",
	"/admin/images/ajax_loader.gif");

	$(document).keydown(function(event) {
		if (event.ctrlKey)
		{
			switch (event.which)
			{
				case 0x25: // Назад
				case 0x27: // Вперед
					var currentWindowId = $.getCurrentWindowId();
					if (event.which == 0x25) {
						$('#' + currentWindowId + ' #id_prev').click();
					}
					else {
						$('#' + currentWindowId + ' #id_next').click();
					}
				break;
			}
		}
	});

	// Тень для окна
	$.fn.extend({
		applyShadow: function()
		{
			return this.each(function(index, object){
				var obj = $(object);

				obj.addClass('shadowed');
				$('<div>').attr('class', 'tl').appendTo(obj);
				$('<div>').attr('class', 't')
				.height(15)
				.appendTo(obj);

				$('<div>').attr('class', 'tr').appendTo(obj);
				$('<div>').attr('class', 'l')
				.width(17)
				.appendTo(obj);

				$('<div>').attr('class', 'r')
				.width(17)
				.appendTo(obj);

				$('<div>').attr('class', 'bl').appendTo(obj);

				$('<div>').attr('class', 'b')
				.height(21)
				.appendTo(obj);

				$('<div>').attr('class', 'br').appendTo(obj);
			});
		}
	});
	$.fx.off = false;

	$.ajaxSetup({
		cache: false,
		error: function(jqXHR, textStatus, errorThrown){
			$.loadingScreen('hide');
			jqXHR.statusText != 'abort' && alert('Ajax error: ' + textStatus + ', ' + errorThrown);
		}
	});
})(jQuery)

function getWindowId(WindowId)
{
	if (typeof WindowId == 'undefined' || WindowId == '')
	{
		WindowId = 'id_content';
	}

	return WindowId;
}

// Установка cookies
// name - имя параметра
// value - значение параметра
// expires - время жизни куки в секундах
// path - путь куки
// domain - домен
function setCookie(name, value, expires, path, domain, secure)
{
	// если истечение передано - устанавливаем время истечения на expires секунд
	// вперед
	if (expires)
	{
		var date = new Date();
		expires = (expires * 1000) + date.getTime();
		date.setTime(expires);
	}

	document.cookie = name + "=" + encodeURIComponent(value) +
	((expires) ? "; expires=" + date.toGMTString() : "") +
	((path) ? "; path=" + path : "") +
	((domain) ? "; domain=" + domain : "") +
	((secure) ? "; secure" : "");
}

// Кроссбраузерная функция получения размеров экрана,
// используется в функции ShowLoadingScreen.
function getPageSize()
{
	var xScroll, yScroll;

	if (window.innerHeight && window.scrollMaxY)
	{
		xScroll = window.innerWidth + window.scrollMaxX;
		yScroll = window.innerHeight + window.scrollMaxY;
	}
	else if (document.body.scrollHeight > document.body.offsetHeight)
	{ // all but Explorer Mac
		xScroll = document.body.scrollWidth;
		yScroll = document.body.scrollHeight;
	}
	else
	{ // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and
		// Safari
		xScroll = document.body.offsetWidth;
		yScroll = document.body.offsetHeight;
	}

	var windowWidth, windowHeight, pageHeight, pageWidth;
	if (self.innerHeight)
	{	// all except Explorer
		if(document.documentElement.clientWidth)
		{
			windowWidth = document.documentElement.clientWidth;
		}
		else
		{
			windowWidth = self.innerWidth;
		}
		windowHeight = self.innerHeight;
	}
	else if (document.documentElement && document.documentElement.clientHeight)
	{ // Explorer 6 Strict Mode
		windowWidth = document.documentElement.clientWidth;
		windowHeight = document.documentElement.clientHeight;
	}
	else if (document.body)
	{ // other Explorers
		windowWidth = document.body.clientWidth;
		windowHeight = document.body.clientHeight;
	}

	// for small pages with total height less then height of the viewport
	if(yScroll < windowHeight)
	{
		pageHeight = windowHeight;
	}
	else
	{
		pageHeight = yScroll;
	}

	// for small pages with total width less then width of the viewport
	if(xScroll < windowWidth)
	{
		pageWidth = xScroll;
	}
	else
	{
		pageWidth = windowWidth;
	}

	return new Array(pageWidth, pageHeight, windowWidth, windowHeight);
}

// Получение информации о позиции скрола
function getScrollXY()
{
	var scrOfX = 0, scrOfY = 0;

	if (typeof(window.pageYOffset ) == 'number' )
	{
		// Netscape
		scrOfY = window.pageYOffset;
		scrOfX = window.pageXOffset;
	}
	else if (document.body && (document.body.scrollLeft || document.body.scrollTop))
	{
		// DOM
		scrOfY = document.body.scrollTop;
		scrOfX = document.body.scrollLeft;
	}
	else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop))
	{
		// IE6
		scrOfY = document.documentElement.scrollTop;
		scrOfX = document.documentElement.scrollLeft;
	}

	return [ scrOfX, scrOfY ];
}

// Скрытие div-а
function HideObject(obj){
	return obj.hideObjects();
}

// Показ div-а
function ShowObject(obj){
	return obj.showObjects();
}

// =============================================
// Функции работы с меню
// =============================================
changeFontSizeTimer = new Array();
menuTimer = new Array();

function HostCMSMenuOver(CurrenElementId, LevelMenu, ChildId)
{
	clearTimeout(menuTimer[CurrenElementId]);

	var CurrenElement = $("#"+CurrenElementId);

	if (CurrenElement.length > 0)
	{
		var jChild = $("#"+ChildId);

		// Оформление еще не было задано
		if (jChild.find('.tl').size() === 0)
		{
			jChild.applyShadow();
		}

		decor(CurrenElement, LevelMenu);

		jChild.css('display', 'block');
	}
}

function HostCMSMenuOut(CurrenElementId, LevelMenu, ChildId)
{
	menuTimer[CurrenElementId] = setTimeout(function(){
		var CurrenElement = $("#"+CurrenElementId);

		if (CurrenElement.length > 0)
		{
			unDecor(CurrenElement, LevelMenu);

			var jChild = $("#"+ChildId);
			jChild.css('display', 'none');
		}
	}, 50);
}

// Функция визуального оформления элементов меню
function decor(CurrenElement, LevelMenu)
{
	if (LevelMenu == 1) // для первого уровня вложенности
	{
		CurrenElement
			.css('background-image', "url('/admin/images/line3.gif')")
			.css('background-repeat', 'repeat-x')
			.css('background-position', '0 100%');

		var child = CurrenElement.children;
		var CurrenElementId = CurrenElement.attr('id');

		if (changeFontSizeTimer[CurrenElementId] != '')
		{
			clearTimeout(changeFontSizeTimer[CurrenElementId]);
		}
		changeFontSize(CurrenElement.attr('id'), 1, 13);
	}
}

// Функция визуального оформления элементов меню
function unDecor(CurrenElement, LevelMenu)
{
	if (LevelMenu==1)
	{
		var CurrenElementId = CurrenElement.attr('id');

		clearTimeout(changeFontSizeTimer[CurrenElementId]);

		CurrenElement
			.css('background-image', "url('/admin/images/line1.gif')")
			.css('background-repeat', 'repeat-x')
			.css('background-position', '0 100%');

		changeFontSize(CurrenElement.attr('id'), -1, 10);
	}
}

// Функции оформления
function changeFontSize(CurrenElementId, change, limit)
{
	var CurrenElement = document.getElementById(CurrenElementId);

	if (CurrenElement)
	{
		var CurrFontSize = CurrenElement.style.fontSize ? parseInt(CurrenElement.style.fontSize) : 10;
		if (CurrFontSize != limit)
		{
			CurrenElement.style.fontSize = (CurrFontSize + change) + 'pt';
			changeFontSizeTimer[CurrenElementId] = setTimeout('changeFontSize("'+CurrenElementId+'", '+change+', '+limit+')', 1);
		}
	}
}

/**
 * Создание окна
 *
 * @param windowId идентификатор окна
 * @param windowTitle заголовок окна
 * @param windowWidth ширина окна
 * @param windowHeight высота окна
 * @param type тип закрытия окна, 0 - скрыть, 1 - уничтожить
 */
function CreateWindow(windowId, windowTitle, windowWidth, windowHeight, typeClose)
{
	var removeWindow = (typeof typeClose != 'undefined' && typeClose == 1);

	var windowDiv = document.getElementById(windowId);

	if (windowDiv == undefined)
	{
		// Создаем div для окна
		var fade_div = document.createElement("div");
		fade_div.setAttribute("id", windowId);
		var body = document.getElementsByTagName("body")[0];
		windowDiv = body.appendChild(fade_div);
	}

	if (windowWidth == '')
	{
		windowWidth = '300px';
	}

	windowDiv.style.width = windowWidth;

	if (windowHeight != '')
	{
		windowDiv.style.height = windowHeight;
	}

	$(windowDiv).applyShadow();

	// Верхняя полосочка(для отображения пустого заголовка передать пробел)
	if(windowTitle != '')
	{
		var topbar = document.createElement("div");
		topbar.className = "topbar";
		windowDiv.insertBefore(topbar, windowDiv.childNodes[0]);
	}

	windowDiv.style.display = 'none';

	// Закрыть
	var wclose_img = document.createElement("img");
	wclose_img.src = '/admin/images/wclose.gif';

	windowId = windowId.replace('[','\\[').replace(']','\\]');

	if (removeWindow)
	{
		wclose_img.onclick = function() { $("#"+windowId).remove(); };
	}
	else
	{
		wclose_img.onclick = function() {HideWindow(windowId); };
	}

	if(windowTitle != '')
	{
		topbar.appendChild(wclose_img);

		// Заголовок окна
		var textNode = document.createTextNode(windowTitle);
		topbar.appendChild(textNode);
	}
}

// Отображает/скрывает окно
function SlideWindow(windowId)
{
	if ($("#"+windowId).css("display") == 'block')
	{
		HideWindow(windowId);
	}
	else
	{
		ShowWindow(windowId);
	}
}

var prev_window = 0;

function ShowWindow(windowId)
{
	var windowDiv = document.getElementById(windowId);

	if (windowDiv == undefined)
	{
		return false;
	}

	if (prev_window && prev_window != windowId)
	{
		HideWindow(prev_window);
	}

	prev_window = windowId;

	// 0 - pageWidth, 1 - pageHeight, 2 - windowWidth, 3 - windowHeight
	var arrayPageSize = getPageSize();

	// 0 - scrOfX, 1 - scrOfY
	var arrayScrollXY = getScrollXY();

	// Отображаем до определения размеров div-а
	windowDiv.style.display = 'block';

	var clientHeight = windowDiv.clientHeight;
	var clientWidth = windowDiv.clientWidth;

	// Если высота div-а больше высоты окна
	if (clientHeight > arrayPageSize[3])
	{
		// Положим высоту равной 90% высоты окна
		clientHeight = Math.round(arrayPageSize[3] * 0.9);
	}

	// Если ширина div-а больше ширины окна
	if (clientWidth > arrayPageSize[2])
	{
		// Положим ширину равной 90% высоты окна
		clientWidth = Math.round(arrayPageSize[2] * 0.9);
	}

	windowDiv.style.top = ((arrayPageSize[3] - clientHeight) / 2 + arrayScrollXY[1]) + 'px';
	windowDiv.style.left = ((arrayPageSize[2] - clientWidth) / 2 + arrayScrollXY[0]) + 'px';
}

function HideWindow(windowId)
{
	$("#"+windowId).css('display', 'none');
}

function ShowAttentionWindow()
{
	if(document.forms["EditAddItem"].elements["shop_items_catalog_type"][0].checked || document.forms["EditAddItem"].elements["shop_items_catalog_type"][2].checked)
	{
		res = confirm("ВНИМАНИЕ! Для данного товара указаны электронные товары, при изменении типа данного товара на \"Обычный\", электронные товары для данного товара будут УДАЛЕНЫ! Вы уверены что хотите сменить тип данного товара?");

		if(res && document.forms["EditAddItem"].elements["shop_items_catalog_type"][0].checked)
		{
			document.forms["EditAddItem"].elements["shop_items_catalog_type"][0].checked = 1;
		}
		else if(res && document.forms["EditAddItem"].elements["shop_items_catalog_type"][2].checked)
		{
			document.forms["EditAddItem"].elements["shop_items_catalog_type"][2].checked;
		}
		else
		{
			document.forms["EditAddItem"].elements["shop_items_catalog_type"][1].checked = 1;
		}
	}
}

// Функции для всплывающего блока
function copyright_position(copyright)
{
	var windowDiv = document.getElementById(copyright);
	windowDiv.style.top = 'auto';
	windowDiv.style.left = 'auto';
	windowDiv.style.bottom = 55 + 'px';
	windowDiv.style.right = 25 + 'px';
	clear_timeout_copiright();
}

function clear_timeout_copiright()
{
	clearTimeout(timeout_copiright);
}

function set_timeout_copyright()
{
	timeout_copiright = setTimeout(function(){HideWindow('copyright')}, 500);
}

// Изменение статуса заказа товара
function ChangeOrderStatus()
{
	var date = new Date(), day = date.getDate(), month = date.getMonth() + 1, hours = date.getHours(), minutes = date.getMinutes();

	if (day < 10)
	{
		day = '0' + day;
	}

	if (month < 10)
	{
		month = '0' + month;
	}

	if (hours < 10)
	{
		hours = '0' + hours;
	}

	if (minutes < 10)
	{
		minutes = '0' + minutes;
	}

	document.getElementById('order_change_status_date_time_id').value = day + '.' + month + '.' + date.getFullYear() + ' ' + hours + ':' + minutes + ':' + '00';
}

/**
 * Показ окна со списком флагов тикетов int helpdesk_ticket_id Идентификатор
 * тикета
 */
function TicketFlagChangeWindow(helpdesk_ticket_id)
{
	var oWindowId = 'heldesk_ticket_'+helpdesk_ticket_id+'_flag';

	if (document.getElementById(oWindowId) == undefined)
	{
		// Создаем окно
		CreateWindow(oWindowId, 'Флаг', '215px', '215px', 1);

		var oWindow = document.getElementById(oWindowId);

		// <div id="subdiv">
		var ElementDiv = document.createElement("div");
		ElementDiv.setAttribute("id", "subdiv_small");
		var DivNode = oWindow.appendChild(ElementDiv);

		var ElementForm = document.createElement("form");
		ElementForm.setAttribute("id", "formFlag_"+helpdesk_ticket_id);
		var FormNode = ElementDiv.appendChild(ElementForm);

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

				if (req.responseJS != undefined)
				{
					// Данные.
					if (req.responseJS.result != undefined)
					{
						// oMessageTextarea =
						// document.getElementById(oWindowId);
						FormNode.innerHTML = req.responseJS.result;
					}
				}
				return true;
			}
		}

		req.open('get', "/admin/helpdesk/helpdesk.php?action=get_flags&helpdesk_ticket_id="+helpdesk_ticket_id, true);

		// Отсылаем данные в обработчик.
		req.send(null);
	}

	SlideWindow(oWindowId);
}

/**
 * Применение флага к тикету str windowId Идентификатор окна int ticketId
 * Идентификатр тикета
 */
function SetFlag(windowId, ticketId)
{
	var cbItem = document.getElementById(windowId);

	if (cbItem)
	{
		var flagId = GetRadioValue(cbItem.flag);

		if (flagId != null)
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

					if (req.responseJS != undefined)
					{
						// Данные.
						if (req.responseJS.result != undefined)
						{
							SlideWindow(prev_window);
							if (req.responseJS.result != 'null')
							{
								var oImg = document.getElementById('image_' + ticketId);
								oImg.src = req.responseJS.result;
								oImg.alt = req.responseJS.alt;
								oImg.title = req.responseJS.alt;
							}
						}
					}

					return true;
				}
			}

			req.open('get', "/admin/helpdesk/helpdesk.php?action=set_flag&helpdesk_ticket_id="+ticketId+"&helpdesk_ticket_flags_id="+flagId, true);

			// Отсылаем данные в обработчик.
			req.send(null);
		}
	}
}

/**
 * Определение выбранного переключателя Radio-объекта str radioObject Имя
 * Radio-группы
 */
function GetRadioValue(radioObject)
{
	var value = null;

	for (var i = 0; i < radioObject.length; i++)
	{
		if (radioObject[i].checked)
		{
			value = radioObject[i].value;
			break;
		}
	}

	return value;
}

function SetControlElementsStatus(windowId, ControlElementsStatus)
{
	windowId = getWindowId(windowId);

	$("#"+windowId+" #ControlElements input").attr('disabled', !ControlElementsStatus);
}

function cSelectFilter(windowId, sObjectId)
{
	this.windowId = getWindowId(windowId);
	this.sObjectId = sObjectId;

	// Игнорировать регистр
	this.ignoreCase = true;
	this.timeout = null;
	this.pattern = '';
	this.aOriginalOptions = null;
	this.sSelectedValue = '';

	// Сейчас происходит фильтрация
	this.is_filtering = false;

	// Установка требуемого шаблона фильтрации
	this.Set = function(pattern) {
		this.pattern = pattern;
		this.is_filtering = (pattern.length != 0);
	}

	// Указывает регулярному выражению игнорировать регистр
	this.SetIgnoreCase = function(value) {
		this.ignoreCase = value;
	}

	this.GetCurrentSelectObject = function() {
		this.oCurrentSelectObject = $("#"+this.windowId+" #"+this.sObjectId);
	}

	this.Init = function() {

		this.GetCurrentSelectObject();

		if (this.oCurrentSelectObject.length == 1)
		{
			var jOptions = this.oCurrentSelectObject.children("option"), jOptionItem;

			if (jOptions.length > 0)
			{
				// Сохраняем установленное до фильтрации значение
				this.sSelectedValue = this.oCurrentSelectObject.val();
				this.aOriginalOptions = jOptions;
			}
		}
	}

	this.Filter = function() {
		var self = this;
		var icon = $("#" + this.windowId + " #filer_" + this.sObjectId).prev('span').find('i');

		icon.removeClass('fa-search').addClass('fa-spinner fa-spin');

		setTimeout(function(){
			// Если фильтрация - получаем объект
			if (self.is_filtering) {
				// Заново получаем объект, т.к. при AJAX-запросе на момент Init-а
				// объект мог не существовать
				self.GetCurrentSelectObject();
			}

			if (self.aOriginalOptions == null || self.aOriginalOptions.length === 0) {
				self.Init();
			}

			if (self.oCurrentSelectObject.length == 1)
			{
				// Сбрасываем все значения списка
				self.oCurrentSelectObject.empty();

				if (self.is_filtering) {
					var attributes = self.ignoreCase ? 'i' : '',
						regexp = new RegExp(self.pattern, attributes),
						currentOption, iOriginalOptionsLength = self.aOriginalOptions.length;

					for (var i = 0; i < iOriginalOptionsLength; i++)
					{
						currentOption = $(self.aOriginalOptions[i]);

						if (regexp.test(' ' + currentOption.text()))
						//if (currentOption.text().indexOf(self.pattern) != -1)
						{
							self.oCurrentSelectObject.append(
								currentOption
							);
						}
					}
				}
				else {
					// restore all values
					self.oCurrentSelectObject.append(self.aOriginalOptions);
				}
			}

			icon.removeClass('fa-spinner fa-spin').addClass('fa-search');

			self.oCurrentSelectObject.get(0).options.selectedIndex = 0;
			//self.oCurrentSelectObject.val(self.sSelectedValue);
			//jImg.remove();
		}, 100);
	}
}

// --- [Menu] ---

var action = '', aHeights = [];

function SubMenu(divId)
{
	if (action == '')
	{
		var data = {'_': Math.round(new Date().getTime())}, subDivHeight = $("div[id="+divId+"]").height(),
			reg = /id_(\d+)/;

		if ($("div[id="+divId+"]").height() === 0)
		{
			action = 'showing';
			ShowSubMenu(divId, aHeights[divId]);

			var arr = reg.exec(divId);
			data['show_sub_menu'] = arr[1];
		}
		else
		{
			action = 'hiding';
			aHeights[divId] = subDivHeight;

			HideSubMenu(divId);

			var arr = reg.exec(divId);
			data['hide_sub_menu'] = arr[1];
		}

		$.ajax({
			url: '/admin/index.php',
			data: data,
			type: 'POST',
			dataType: 'json',
			success: function(){}
		});
	}
}

function ShowSubMenu (divId, maxHeight)
{
	$("div[id="+divId+"]").animate({
		height: maxHeight,
		opacity: 1.0}, {
		duration: 'normal',
		complete: function(){
			action = '';
		}
	});
}

function HideSubMenu(divId)
{
	$("div[id="+divId+"]").animate({
		height: 0,
		opacity: 0}, {
		duration: 'normal',
		complete: function(){
			action = '';
		}
	});
}

/**
 * Модуль "Структура сайта"
 *
 * @windowId
 * @ASelectedItem код выбранного элемента
 * @structure_id идентификатор структуры
 * @lib_dir_id раздел типовых динамически страниц
 * @lib_id идентификатор типовых динамически страниц
 */
function SetViewStructure(windowId, ASelectedItem, iStructureId, iLibDirId, iLibId)
{
	windowId = getWindowId(windowId);

	var template_id = 'none',
		document_dir = 'none',
		document = 'none',
		//editDocument = 'none',
		url = 'none',
		lib_dir = 'none',
		lib = 'none',
		lib_properties = 'none';

	ASelectedItem = parseInt(ASelectedItem);

	switch (ASelectedItem)
	{
		default:
		case 0: // Страница
			document_dir = 'block';
			document = 'block';
			//editDocument = 'block';
			url = 'block';
			//HideObject($("#"+windowId+" #structure_source"));
			//HideObject($("#"+windowId+" #structure_config_source"));

			$("#"+windowId+" #structure_source").hide();
			$("#"+windowId+" #structure_config_source").hide();

		break;

		case 1: // Динамическая страница
			template_id = 'block';
			// Для CodePress показываем таким образом
			//ShowObject($("#"+windowId+" #structure_source"));
			//ShowObject($("#"+windowId+" #structure_config_source"));
			$("#"+windowId+" #structure_source").show();
			$("#"+windowId+" #structure_config_source").show();
		break;

		case 2: // Типовая дин. страница
			template_id = 'block';
			lib_dir = 'block';
			lib = 'block';
			lib_properties = 'block';
			//HideObject($("#"+windowId+" #structure_source"));
			//HideObject($("#"+windowId+" #structure_config_source"));

			$("#"+windowId+" #structure_source").hide();
			$("#"+windowId+" #structure_config_source").hide();

		break;
	}

	$("#"+windowId+" #template_id").css('display', template_id);
	$("#"+windowId+" #document_dir").css('display', document_dir);
	$("#"+windowId+" #document").css('display', document);
	//$("#"+windowId+" #editDocument").css('display', editDocument);
	$("#"+windowId+" #url").css('display', url);
	$("#"+windowId+" #lib_dir").css('display', lib_dir);
	$("#"+windowId+" #lib").css('display', lib);
	$("#"+windowId+" #lib_properties").css('display', lib_properties);
}

/**
 * Модуль "Helpdesk"
 */
function SetHolidays(windowId, index)
{
	windowId = getWindowId(windowId);

	var week_day = 'none';

	if (index == 0)
	{
		week_day = 'block';
	}

	$("#"+windowId+" #week_day").css('display', week_day);
}

function ShowPropertyRows(windowId, index)
{
	windowId = getWindowId(windowId);

	var default_value = 'none',
		list_id = 'none',
		informationsystem_id = 'none',
		shop_id = 'none',
		default_value_date = 'none',
		default_value_datetime = 'none',
		default_value_checked = 'none';

	index = parseInt(index);

	switch (index)
	{
		case 0: // Число
		case 1: // Строка
		case 4: // Большое текстовое поле
		case 6: // Визуальный редактор
		case 11: // Число с плавающей
			default_value = 'block';
		break;
		case 2: // Файл
		break;
		case 3: // Список
			list_id = 'block';
		break;
		case 5: // Информационная система
			informationsystem_id = 'block';
		break;
		case 7: // Флажок
			default_value_checked = 'block';
		break;
		case 8: // Дата
			default_value_date = 'block';
		break;
		case 9: // ДатаВремя
			default_value_datetime = 'block';
		break;
		case 12: // Магазин
			shop_id = 'block';
		break;
	}

	$("#"+windowId+" #default_value").css('display', default_value);
	$("#"+windowId+" #list_id").css('display', list_id);
	$("#"+windowId+" #informationsystem_id").css('display', informationsystem_id);
	$("#"+windowId+" #shop_id").css('display', shop_id);
	$("#"+windowId+" #default_value_date").css('display', default_value_date);
	$("#"+windowId+" #default_value_datetime").css('display', default_value_datetime);
	$("#"+windowId+" #default_value_checked").css('display', default_value_checked);
}

function ShowRowsAdminForm(windowId, index)
{
	windowId = getWindowId(windowId);
	index = parseInt(index);

	var image = 'none', list = 'none', link = 'none', onclick = 'none';

	switch (index)
	{
		case 4: // Ссылка
		case 10: // Callback
			link = 'block';
			onclick = 'block';
		break;
		case 7: // Картинка-ссылка
			image = 'block';
			link = 'block';
			onclick = 'block';
		break;
		case 8: // Список
			list = 'block';
		break;
	}

	$("#"+windowId+" #image").css('display', image);
	$("#"+windowId+" #list").css('display', list);
	$("#"+windowId+" #link").css('display', link);
	$("#"+windowId+" #onclick").css('display', onclick);
}

function ShowRowsBannerControl(windowId, index)
{
	windowId = getWindowId(windowId);

	index = parseInt(index);

	var advertisement_banner_swf_path = 'none',
		advertisement_banner_image_path = 'none',
		advertisement_banner_image_link = 'none',
		advertisement_banner_structure_id = 'none',
		advertisement_banner_text = 'none';

	switch (index)
	{
		case 0: // Изображение
			advertisement_banner_image_path = 'block';
			advertisement_banner_image_link = 'block';
		break;
		case 1: // html
			advertisement_banner_text = 'block';
		break;
		case 2: // Всплывающий
			advertisement_banner_structure_id = 'block';
		break;
		case 3: // Флэш
			advertisement_banner_swf_path = 'block';
			advertisement_banner_image_link = 'block';
		break;
	}

	$("#"+windowId+" #advertisement_banner_swf_path").css('display', advertisement_banner_swf_path);
	$("#"+windowId+" #advertisement_banner_image_path").css('display', advertisement_banner_image_path);
	$("#"+windowId+" #advertisement_banner_image_link").css('display', advertisement_banner_image_link);
	$("#"+windowId+" #advertisement_banner_structure_id").css('display', advertisement_banner_structure_id);
	$("#"+windowId+" #advertisement_banner_text").css('display', advertisement_banner_text);
}

function ShowRowsForms(windowId, index)
{
	windowId = getWindowId(windowId);

	index = parseInt(index);

	var list_id = 'none',
		cols_id = 'none',
		rows_id = 'none',
		checked_id = 'none',
		size_id = 'none',
		default_value_id = 'none',
		obligatory_id = 'none';

	switch (index)
	{
		case 0: // Поле ввода.
		default:
			size_id = 'block';
			default_value_id = 'block';
			obligatory_id = 'block';
			break;
		 case 1: // Пароль.
			size_id = 'block';
			default_value_id = 'block';
			obligatory_id = 'block';
			break;
		 case 2: // Поле загрузки файла.
			size_id = 'block';
			obligatory_id = 'block';
			break;
		 case 3: // Переключатель.
			list_id = 'block';
			obligatory_id = 'block';
			break;
		 case 4: // Флажок.
			checked_id = 'block';
			obligatory_id = 'block';
			break;
		 case 5: // Большое текстовое поле.
			cols_id = 'block';
			rows_id = 'block';
			default_value_id = 'block';
			obligatory_id = 'block';
			break;
		 case 6: // Список.
			list_id = 'block';
			obligatory_id = 'block';
			break;
		case 7: // Скрытое поле.
			default_value_id = 'block';
			obligatory_id = 'block';
			break;
		 case 8: // Надпись.
			default_value_id = 'block';
			obligatory_id = 'block';
			break;
		case 9: // Список флажков
			list_id = 'block';
			obligatory_id = 'block';
			break;
	}
	$("#"+windowId+" #list_id").css('display', list_id);
	$("#"+windowId+" #cols_id").css('display', cols_id);
	$("#"+windowId+" #rows_id").css('display', rows_id);
	$("#"+windowId+" #checked_id").css('display', checked_id);
	$("#"+windowId+" #size_id").css('display', size_id);
	$("#"+windowId+" #default_value_id").css('display', default_value_id);
	$("#"+windowId+" #obligatory_id").css('display', obligatory_id);
}

function ShowRowsGroupPropertys(windowId, index)
{
	windowId = getWindowId(windowId);

	index = parseInt(index);

	var list_id = 'none',
		default_value_id = 'none',
		default_date_value_id = 'none',
		default_datetime_value_id = 'none',
		information_system_id = 'none',
		default_checked_value_id = 'none',
		information_propertys_groups_big_width = 'none',
		information_propertys_groups_big_height = 'none',
		information_propertys_groups_small_width = 'none',
		information_propertys_groups_small_height = 'none';

	switch (index)
	{
		case 0: // Число
		case 1: // Строка
		case 4: // Большое текстовое поле
		case 5: // Визуальный редактор
		default:
			default_value_id = 'block';
		break;
		case 2: // Файл
			information_propertys_groups_big_width = 'block';
			information_propertys_groups_big_height = 'block';
			information_propertys_groups_small_width = 'block';
			information_propertys_groups_small_height = 'block';
		break;
		case 3: // Список
			list_id = 'block';
		break;
		case 6: // Информационная система
			information_system_id = 'block';
		break;
		case 7: // Флажок
			default_checked_value_id = 'block';
		break;
		case 8: // Дата
			default_date_value_id = 'block';
		break;
		case 9: // Дата-время
			default_datetime_value_id = 'block';
		break;
	}

	$("#"+windowId+" #list_id").css('display', list_id);
	$("#"+windowId+" #default_value_id").css('display', default_value_id);
	$("#"+windowId+" #default_date_value_id").css('display', default_date_value_id);
	$("#"+windowId+" #default_datetime_value_id").css('display', default_datetime_value_id);
	$("#"+windowId+" #information_system_id").css('display', information_system_id);
	$("#"+windowId+" #default_checked_value_id").css('display', default_checked_value_id);
	$("#"+windowId+" #information_propertys_groups_big_width").css('display', information_propertys_groups_big_width);
	$("#"+windowId+" #information_propertys_groups_big_height").css('display', information_propertys_groups_big_height);
	$("#"+windowId+" #information_propertys_groups_small_width").css('display', information_propertys_groups_small_width);
	$("#"+windowId+" #information_propertys_groups_small_height").css('display', information_propertys_groups_small_height);
}

function ShowRowsItemPropertys(windowId, index)
{
	windowId = getWindowId(windowId);

	index = parseInt(index);

	var list_id = 'none',
		default_value_id = 'none',
		default_date_value_id = 'none',
		default_datetime_value_id = 'none',
		information_system_id = 'none',
		default_checked_value_id = 'none',
		information_propertys_default_big_width = 'none',
		information_propertys_default_small_width = 'none',
		information_propertys_default_big_height = 'none',
		information_propertys_default_small_height = 'none';

	switch (index)
	{
		case 0: // Число
		case 1: // Строка
		case 4: // Большое текстовое поле
		case 6: // Визуальный редактор
		default:
			default_value_id = 'block';
		break;
		case 2: // Файл
			information_propertys_default_big_width = 'block';
			information_propertys_default_small_width = 'block';
			information_propertys_default_big_height = 'block';
			information_propertys_default_small_height = 'block';
		break;
		case 3: // Список
			list_id = 'block';
		break;
		case 5: // Информационная система
			information_system_id = 'block';
		break;
		case 7: // Флажок
			default_checked_value_id = 'block';
		break;
		case 8: // Дата
			default_date_value_id = 'block';
		break;
		case 9: // Дата-время
			default_datetime_value_id = 'block';
		break;
	}
	$("#"+windowId+" #list_id").css('display', list_id);
	$("#"+windowId+" #default_value_id").css('display', default_value_id);
	$("#"+windowId+" #default_date_value_id").css('display', default_date_value_id);
	$("#"+windowId+" #default_datetime_value_id").css('display', default_datetime_value_id);
	$("#"+windowId+" #information_system_id").css('display', information_system_id);
	$("#"+windowId+" #default_checked_value_id").css('display', default_checked_value_id);
	$("#"+windowId+" #information_propertys_default_big_width").css('display', information_propertys_default_small_height);
	$("#"+windowId+" #information_propertys_default_small_width").css('display', information_propertys_default_small_width);
	$("#"+windowId+" #information_propertys_default_big_height").css('display', information_propertys_default_big_height);
	$("#"+windowId+" #information_propertys_default_small_height").css('display', information_propertys_default_small_height);
}

function ShowRowsShopItemPropertysType(windowId, index)
{
	windowId = getWindowId(windowId);

	index = parseInt(index);

	var list_of_properties_default_id = 'none',
		list_of_properties_mesures_id = 'none',
		list_of_properties_lists_id = 'none',
		checkbox = 'none',
		date_without_time = 'none',
		date_with_time = 'none',
		shop_list_of_properties_default_big_width = 'none',
		shop_list_of_properties_default_big_height = 'none',
		shop_list_of_properties_default_small_width = 'none',
		shop_list_of_properties_default_small_height = 'none';

	switch (index)
	{
		case 0: // Строка
		case 3: // Большое текстовое поле
		case 4: // Визуальный редактор
		default:
			list_of_properties_default_id = 'block';
			list_of_properties_mesures_id = 'block';
		break;
		case 1: // Файл
			shop_list_of_properties_default_big_width = 'block';
			shop_list_of_properties_default_big_height = 'block';
			shop_list_of_properties_default_small_width = 'block';
			shop_list_of_properties_default_small_height = 'block';
		break;
		case 2: // Список
			list_of_properties_lists_id = 'block';
		break;
		case 5: // Дата
			date_without_time = 'block';
		break;
		case 6: // ДатаВремя
			date_with_time = 'block';
		break;
		case 7: // Флажок
			checkbox = 'block';
		break;
	}
	$("#"+windowId+" #list_of_properties_default_id").css('display', list_of_properties_default_id);
	$("#"+windowId+" #list_of_properties_mesures_id").css('display', list_of_properties_mesures_id);
	$("#"+windowId+" #list_of_properties_lists_id").css('display', list_of_properties_lists_id);
	$("#"+windowId+" #checkbox").css('display', checkbox);
	$("#"+windowId+" #date_without_time").css('display', date_without_time);
	$("#"+windowId+" #date_with_time").css('display', date_with_time);
	$("#"+windowId+" #shop_list_of_properties_default_big_width").css('display', shop_list_of_properties_default_big_width);
	$("#"+windowId+" #shop_list_of_properties_default_big_height").css('display', shop_list_of_properties_default_big_height);
	$("#"+windowId+" #shop_list_of_properties_default_small_width").css('display', shop_list_of_properties_default_small_width);
	$("#"+windowId+" #shop_list_of_properties_default_small_height").css('display', shop_list_of_properties_default_small_height);
}
function ShowImport(windowId, index)
{
	windowId = getWindowId(windowId);
	index = parseInt(index);

	var import_price_encoding = 'none',
		import_price_separator = 'none',
		import_price_stop = 'none',
		import_price_name_field_f = 'none',
		import_price_action_items = 'none',
		import_price_action_delete_image = 'none',
		import_price_max_time = 'none',
		search_event_indexation = 'none',
		import_price_max_count = 'none',
		import_price_separator_text = 'none',
		import_price_stop_text = 'none',
		export_external_properties_allow_groups = 'none';
		import_price_list_separator = 'none';

	if (index == 0)
	{
		import_price_encoding = 'block';
		import_price_separator = 'block';
		import_price_stop = 'block';
		import_price_name_field_f = 'block';
		import_price_action_items = 'block';
		import_price_action_delete_image = 'block';
		import_price_max_time = 'block';
		search_event_indexation = 'block';
		import_price_max_count = 'block';
		import_price_separator_text = 'block';
		import_price_stop_text = 'block';
		export_external_properties_allow_groups = 'block';
		import_price_list_separator = 'block';
	}

	$("#"+windowId+" #import_price_encoding").css('display', import_price_encoding);
	$("#"+windowId+" #import_price_separator").css('display', import_price_separator);
	$("#"+windowId+" #import_price_stop").css('display', import_price_stop);
	$("#"+windowId+" #import_price_name_field_f").css('display', import_price_name_field_f);
	$("#"+windowId+" #import_price_action_items").css('display', import_price_action_items);
	$("#"+windowId+" #import_price_action_delete_image").css('display', import_price_action_delete_image);
	$("#"+windowId+" #import_price_max_time").css('display', import_price_max_time);
	$("#"+windowId+" #search_event_indexation").css('display', search_event_indexation);
	$("#"+windowId+" #import_price_max_count").css('display', import_price_max_count);
	$("#"+windowId+" #import_price_separator_text").css('display', import_price_separator_text);
	$("#"+windowId+" #import_price_stop_text").css('display', import_price_stop_text);
	$("#"+windowId+" #export_external_properties_allow_groups").css('display', export_external_properties_allow_groups);
	$("#"+windowId+" #import_price_list_separator").css('display', import_price_list_separator);
}
function ShowExport(windowId, index)
{
	windowId = getWindowId(windowId);
	index = parseInt(index);

	var import_price_encoding = 'none',
		import_price_separator = 'none',
		import_price_stop = 'none',
		import_price_name_field_f = 'none',
		import_price_action_items = 'none',
		import_price_action_delete_image = 'none',
		import_price_max_time = 'none',
		search_event_indexation = 'none',
		import_price_max_count = 'none',
		import_price_separator_text = 'none',
		import_price_stop_text = 'none',
		export_external_properties_allow_groups = 'none';
		import_price_list_separator = 'none';

	if (index == 0 || index == 1)
	{
		import_price_encoding = 'block';
		import_price_separator = 'block';
		import_price_stop = 'block';
		import_price_name_field_f = 'block';
		import_price_action_items = 'block';
		import_price_action_delete_image = 'block';
		import_price_max_time = 'block';
		search_event_indexation = 'block';
		import_price_max_count = 'block';
		import_price_separator_text = 'block';
		import_price_stop_text = 'block';
		export_external_properties_allow_groups = 'block';
		import_price_list_separator = 'block';
	}

	$("#"+windowId+" #import_price_encoding").css('display', import_price_encoding);
	$("#"+windowId+" #import_price_separator").css('display', import_price_separator);
	$("#"+windowId+" #import_price_stop").css('display', import_price_stop);
	$("#"+windowId+" #import_price_name_field_f").css('display', import_price_name_field_f);
	$("#"+windowId+" #import_price_action_items").css('display', import_price_action_items);
	$("#"+windowId+" #import_price_action_delete_image").css('display', import_price_action_delete_image);
	$("#"+windowId+" #import_price_max_time").css('display', import_price_max_time);
	$("#"+windowId+" #search_event_indexation").css('display', search_event_indexation);
	$("#"+windowId+" #import_price_max_count").css('display', import_price_max_count);
	$("#"+windowId+" #import_price_separator_text").css('display', import_price_separator_text);
	$("#"+windowId+" #import_price_stop_text").css('display', import_price_stop_text);
	$("#"+windowId+" #export_external_properties_allow_groups").css('display', export_external_properties_allow_groups);
	$("#"+windowId+" #import_price_list_separator").css('display', import_price_list_separator);
}

function ShowRowsShopGroupPropertysType(windowId, index)
{
	windowId = getWindowId(windowId);

	index = parseInt(index);

	var list_of_properties_default_id = 'none',
		list_of_properties_mesures_id = 'none',
		list_of_properties_lists_id = 'none',
		checkbox = 'none',
		date_without_time = 'none',
		date_with_time = 'none',
		shop_properties_group_default_big_width = 'none',
		shop_properties_group_default_big_height = 'none',
		shop_properties_group_default_small_width = 'none',
		shop_properties_group_default_small_height = 'none';

	switch (index)
	{
		case 0: // Строка
		case 3: // Большое текстовое поле
		case 4: // Визуальный редактор
		default:
			list_of_properties_default_id = 'block';
			list_of_properties_mesures_id = 'block';
		break;
		case 1: // Список - файл
			shop_properties_group_default_big_width = 'block';
			shop_properties_group_default_big_height = 'block';
			shop_properties_group_default_small_width = 'block';
			shop_properties_group_default_small_height = 'block';
		break;
		case 2: // Список
			list_of_properties_lists_id = 'block';
		break;
		case 5: // Дата
			date_without_time = 'block';
		break;
		case 6: // ДатаВремя
			date_with_time = 'block';
		break;
		case 7: // Флажок
			checkbox = 'block';
		break;
	}
	$("#"+windowId+" #list_of_properties_default_id").css('display', list_of_properties_default_id);
	$("#"+windowId+" #list_of_properties_mesures_id").css('display', list_of_properties_mesures_id);
	$("#"+windowId+" #list_of_properties_lists_id").css('display', list_of_properties_lists_id);
	$("#"+windowId+" #checkbox").css('display', checkbox);
	$("#"+windowId+" #date_without_time").css('display', date_without_time);
	$("#"+windowId+" #date_with_time").css('display', date_with_time);
	$("#"+windowId+" #shop_properties_group_default_big_width").css('display', shop_properties_group_default_big_width);
	$("#"+windowId+" #shop_properties_group_default_big_height").css('display', shop_properties_group_default_big_height);
	$("#"+windowId+" #shop_properties_group_default_small_width").css('display', shop_properties_group_default_small_width);
	$("#"+windowId+" #shop_properties_group_default_small_height").css('display', shop_properties_group_default_small_height);
}

function ShowRowsSiteUsers(windowId, index)
{
	windowId = getWindowId(windowId);

	var list_id = 'none',
		rows_textarea_id = 'none',
		cols_textarea_id = 'none',
		checked_id = 'none',
		input_size_id = 'none',
		default_value_id = 'none';

	switch (index)
	{
		case 'text': // Текстовое поле
			input_size_id = 'block';
			default_value_id = 'block';
		break;
		case 'password': // Поле пароля
		case 'file': // Поле загрузки файла
			input_size_id = 'block';
		break;
		case 'radio': // Радиогруппа
		case 'select': // Список
			list_id = 'block';
		break;
		case 'checkbox': // Флажок
			 checked_id = 'block';
		break;
		case 'textarea': // Большое текстовое поле
			rows_textarea_id = 'block';
			cols_textarea_id = 'block';
			default_value_id = 'block';
		break;
		case 'hidden': // Скрытое поле
			default_value_id = 'block';
		break;
	}
	$("#"+windowId+" #list_id").css('display', list_id);
	$("#"+windowId+" #rows_textarea_id").css('display', rows_textarea_id);
	$("#"+windowId+" #cols_textarea_id").css('display', cols_textarea_id);
	$("#"+windowId+" #checked_id").css('display', checked_id);
	$("#"+windowId+" #input_size_id").css('display', input_size_id);
	$("#"+windowId+" #default_value_id").css('display', default_value_id);
}

function ShowRowsLibProperty(windowId, index)
{
	var sql_request = 'none',
		sql_caption_field = 'none',
		sql_value_field = 'none';

	index = parseInt(index);

	switch (index)
	{
		case 4:
			sql_request = sql_caption_field = sql_value_field = 'block';
		break;
	}

	$("#"+windowId+" #sql_request").css('display', sql_request);
	$("#"+windowId+" #sql_caption_field").css('display', sql_caption_field);
	$("#"+windowId+" #sql_value_field").css('display', sql_value_field);
}

function ShowRowsAdvertisementPropertyType(windowId, index)
{
	var source = 'none',
		href = 'none',
		html = 'none',
		popup_structure_id = 'none';

	index = parseInt(index);

	switch (index)
	{
		case 0: // Изображение
		case 3: // Flash
			source = 'block';
			href = 'block';
		break;
		case 1: // HTML
			html = 'block';
		break;
		case 2: // Всплывающий
			popup_structure_id = 'block';
		break;
	}

	$("#"+windowId+" #source").css('display', source);
	$("#"+windowId+" #href").css('display', href);
	$("#"+windowId+" #html").css('display', html);
	$("#"+windowId+" #popup_structure_id").css('display', popup_structure_id);
}

/**
* Изменение видимости объекта
* str field_id Идентификатор объекта
*/
function SlideField(field_id)
{
	var oField = document.getElementById(field_id);

	if (oField != 'undefined')
	{
		if (oField.style.display == "none")
		{
			oField.style.display = "block";
		}
		else
		{
			oField.style.display = "none";
		}
	}
}

// -- Проверка ячеек
function FieldCheck(WindowId, field)
{
	WindowId = getWindowId(WindowId);

	if (typeof fieldType == 'undefined')
	{
		return false;
	}

	var value = $(field).val();
	var FiledId = $(field).attr('id');

	if (typeof fieldType[field.id] != 'undefined')
	{
		var message = '';

		// Проверка на минимальную длину
		if (fieldType[FiledId]['minlen'] && value.length < fieldType[FiledId]['minlen'])
		{
			var decl = declension(fieldType[FiledId]['minlen'], i18n['one_letter'], i18n['some_letter2'], i18n['some_letter1']);

			// Есть пользовательское сообщение
			if (fieldMessage[FiledId] && fieldMessage[FiledId]['minlen'])
			{
				message += fieldMessage[FiledId]['minlen'];
			}
			else // Стандартное сообщение
			{
				message += i18n['Minimum'] + ' ' + fieldType[FiledId]['minlen'] + ' ' + decl + '. ' + i18n['current_length'] + ' ' + value.length + '. ';
			}
		}

		// Проверка на максимальную длину
		if (fieldType[FiledId]['maxlen'] && value.length > fieldType[FiledId]['maxlen'])
		{
			var decl = declension(fieldType[FiledId]['maxlen'], i18n['one_letter'], i18n['some_letter2'], i18n['some_letter1']);

			// Есть пользовательское сообщение
			if (fieldMessage[FiledId] && fieldMessage[FiledId]['maxlen'])
			{
				message += fieldMessage[FiledId]['maxlen'];
			}
			else // Стандартное сообщение
			{
				message += i18n['Maximum'] + ' ' + fieldType[FiledId]['maxlen'] + ' ' + decl + '. ' + i18n['current_length'] + ' ' + value.length + '. ';
			}
		}

		// Проверка на регулярное выражение
		if (value.length > 0 && fieldType[FiledId]['reg'] && !value.match(fieldType[FiledId]['reg']))
		{
			// Есть пользовательское сообщение
			if (fieldMessage[FiledId] && fieldMessage[FiledId]['reg'])
			{
				message += fieldMessage[FiledId]['reg'];
			}
			else // Стандартное сообщение
			{
				message += i18n['wrong_value_format'] + ' ';
			}
		}

		// Проверка на соответствие значений 2-х полей
		if (fieldType[FiledId]['fieldEquality'])
		{
			// Пытаемся получить значение поля, которому должны соответствовать
			var jFiled2 = $("#"+WindowId+" #"+fieldType[FiledId]['fieldEquality']);

			if (jFiled2.length > 0
			// Сравниваем значение полей
			&& value != jFiled2.val())
			{
				// Есть пользовательское сообщение
				if (fieldMessage[FiledId] && fieldMessage[FiledId]['fieldEquality'])
				{
					message += fieldMessage[FiledId]['fieldEquality'];
				}
				else // Стандартное сообщение
				{
					message += i18n['different_fields_value'] + ' ';
				}
			}
		}

		FieldCheckShowError(WindowId, FiledId, message);
	}
}

function FieldCheckShowError(WindowId, FiledId, message)
{
	WindowId = getWindowId(WindowId);

	// Insert message into the message div
	$("#" + WindowId + " #"+FiledId + '_error').html(message);

	// Плучаем элемент формы, над которым ведется работа
	var ElementField =	$("#" + WindowId + " #"+FiledId);

	if (ElementField.length > 0)
	{
		// Устанавливаем флаг несоответствия
		fieldsStatus[FiledId] = (message.length > 0);

		if (fieldsStatus[FiledId])
		{
			ElementField
				.css('border-style', 'solid')
				.css('border-width', '1px')
				.css('border-color', '#DB1905')
				.css('background-image', "url('/admin/images/bullet_red.gif')")
				.css('background-position', 'center right')
				.css('background-repeat', 'no-repeat');
		}
		else
		{
			ElementField
				.css('border-style', '')
				.css('border-width', '')
				.css('border-color', '')
				.css('background-image', "url('/admin/images/bullet_green.gif')")
				.css('background-position', 'center right')
				.css('background-repeat', 'no-repeat');
		}
	}

	// Отображать контрольные элементы
	var ControlElementsStatus = true;

	for (ItemIndex in fieldsStatus)
	{
		// если есть хоть одно несоответствие - выключаем управляющие элементы
		if (fieldsStatus[ItemIndex])
		{
			ControlElementsStatus = false;
			break;
		}
	}

	// Активируем-выключаем контрольные элементы формы
	SetControlElementsStatus(WindowId, ControlElementsStatus);
}

function CheckAllField(windowId, formId)
{
	windowId = getWindowId(windowId);
	$("#"+windowId+" #"+formId+" :input").each(function(){
		FieldCheck(windowId, this);
	});
}

/**
* Склонение после числительных
* int number числительное
* int nominative Именительный падеж
* int genitive_singular Родительный падеж, единственное число
* int genitive_plural Родительный падеж, множественное число
*/
function declension(number, nominative, genitive_singular, genitive_plural)
{
	var last_digit = number % 10;
	var last_two_digits = number % 100;

	if (last_digit == 1 && last_two_digits != 11)
	{
		var result = nominative;
	}
	else
	{
		var result = (last_digit == 2 && last_two_digits != 12) || (last_digit == 3 && last_two_digits != 13) || (last_digit == 4 && last_two_digits != 14)
			? genitive_singular
			: genitive_plural;
	}

	return result;
}
// /-- Проверка ячеек

// http://www.tinymce.com/wiki.php/How-to_implement_a_custom_file_browser
function HostCMSFileManager(defaultpath)
{
	this.defaultpath = defaultpath;

	this.fileBrowserCallBack = function(field_name, url, type, win)
	{
		this.field = field_name;
		this.callerWindow = win;

		if (url == '') {
			url = this.defaultpath;
		}
		url = url.split('\\').join('/');

		var cdir = '/', dir = '', lastPos = url.lastIndexOf('/');

		if (lastPos != -1)
		{
			url = url.substr(0, lastPos);
			// => /upload

			lastPos = url.lastIndexOf('/');

			if (lastPos != -1)
			{
				cdir = url.substr(0, lastPos + 1);
				dir = url.substr(lastPos + 1);
			}
		}

		var path = "/admin/wysiwyg/filemanager/index.php?field_name=" + field_name + "&cdir=" + cdir + "&dir=" + dir + "&type=" + type, width = 700, height = 500;

		var x = parseInt(screen.width / 2.0) - (width / 2.0), y = parseInt(screen.height / 2.0) - (height / 2.0);

		this.win = window.open(path, "FM", "top=" + y + ",left=" + x + ",scrollbars=yes,width=" + width + ",height=" + height + ",resizable=yes");

		/*tinyMCE.openWindow({
			file : path,
			title : "File Browser",
			width : 700,
			height : 500,
			close_previous : "no"
		}, {
			window : win,
			input : field_name,
			resizable : "yes",
			inline : "yes",  // This parameter only has an effect if you use the inlinepopups plugin!
			editor_id : tinyMCE.selectedInstance.editorId
		});*/
		return false;
	}

	this.insertFile = function(url)
	{
		url = decodeURIComponent(url);
		url = url.replace(new RegExp(/\\/g), '/');
		this.callerWindow.document.forms[0].elements[this.field].value = url;

		try
		{
			this.callerWindow.document.forms[0].elements[this.field].onchange();
		}
		catch (e){}

		this.win.close();
	}
};