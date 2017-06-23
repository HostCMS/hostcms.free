(function(jQuery){

	// http://james.padolsey.com/javascript/regex-selector-for-jquery/
	jQuery.expr[':'].regex = function(elem, index, match) {
    var matchParams = match[3].split(','),
        validLabels = /^(data|css):/,
        attr = {
            method: matchParams[0].match(validLabels) ?
                        matchParams[0].split(':')[0] : 'attr',
            property: matchParams.shift().replace(validLabels,'')
        },
        regexFlags = 'ig',
        regex = new RegExp(matchParams.join('').replace(/^\s+|\s+$/g,''), regexFlags);
		return regex.test(jQuery(elem)[attr.method](attr.property));
	};

	// Функции для коллекции элементов
	jQuery.fn.extend({
		toggleDisabled: function()
		{
			return this.each(function(){
				this.disabled = !this.disabled;
			});
		},
		// Скрытие объектов
		hideObjects: function()
		{
			return this.each(function(index, object){
				jQuery(object).css('zoom', 1).css('width', 0).css('height', 0).css('visibility', 'hidden').css('overflow', 'hidden');
			});
		},
		// Показ объектов
		showObjects: function()
		{
			return this.each(function(index, object){
				jQuery(object).css('zoom', 1).css('width', 'auto').css('height', 'auto').css('visibility', 'visible').css('overflow', 'visible');
			});
		},
		// Создание заметки
		createNote: function(settings)
		{
			settings = jQuery.extend({
					src: '/modules/skin/default/images/note.png',
					isNew: false
				}, settings);

			return this.each(function(index, object){

				var jObj = jQuery(object), text = jObj.text(), jDesktop = jQuery('#desktop');

				jObj.empty().addClass("cmVoice {cMenu: 'note_conext'} note");

				if (settings.isNew)
				{
					jObj.appendTo(jDesktop)
					.css({
						top: window.mouseYPos - jDesktop.position().top,
						left: window.mouseXPos - jDesktop.position().left
					});
				}

				jObj.draggable({containment: '#desktop', stop: function(event, ui){
					var reg = /note(\d+)/, arr = reg.exec(jQuery(this).prop('id'));

					jQuery.ajax({
						url: '/admin/index.php?' + 'userSettings&moduleId=0'
							+ '&type=98'
							+ '&position_x=' + ui.position.left
							+ '&position_y=' + ui.position.top
							+ '&entity_id=' + arr[1],
						type: 'get',
						dataType: 'json',
						success: function(){}
					});
				}})
				.on('change', function(){ // keydown
					var object = jQuery(this), timer = object.data('timer');

					if (timer){
						clearTimeout(timer);
					}

					jQuery(this).data('timer', setTimeout(function() {
							var reg = /note(\d+)/, arr = reg.exec(object.prop('id')),
							textarea = object.find('textarea').addClass('ajax');

							// add ajax '_'
							var data = jQuery.getData({});
							data['value'] = textarea.val();

							jQuery.ajax({
								context: textarea,
								url: '/admin/index.php?' + 'ajaxNote&action=save'
									+ '&entity_id=' + arr[1],
								type: 'POST',
								data: data,
								dataType: 'json',
								success: function(){
									this.removeClass('ajax');
								}
							});
						}, 1000)
					);
				})
				.append(jQuery('<img>').prop("src", settings.src))
				.append(jQuery('<textarea>').prop("src", settings.src).val(text))
				// Fix IE bug with transparent
				//.dblclick(function() {
				.click(function() {
					jQuery(this).children('textarea').focus();
				});

				if (settings.isNew)
				{
					// add ajax '_'
					var data = jQuery.getData({});

					jQuery.ajax({
						context: jObj,
						url: '/admin/index.php?ajaxCreateNote&position_x='+jObj.position().left+'&position_y='+jObj.position().top,
						data: data,
						dataType: 'json',
						type: 'POST',
						success: function(data) {
							this.prop('id', 'note'+data.form_html)
						}
					});
				}

				// Создаем контекстное меню
				jQuery.createContextualMenu();
			});
		},
		// Удаление заметки
		destroyNote: function()
		{
			return this.each(function(index, object){
				var reg = /note(\d+)/, arr = reg.exec(jQuery(object).prop('id'));

				jQuery.ajax({
					url: '/admin/index.php?' + 'ajaxNote&action=delete'
						+ '&entity_id=' + arr[1],
					type: 'get',
					dataType: 'json',
					success: function(){}
				});

				jQuery(object).remove();
			});
		},
		editable: function(settings){
			settings = jQuery.extend({
				save: function(item, settings){

					var data = jQuery.getData(settings), reg = /apply_check_(\d+)_(\S+)_fv_(\d+)/,
					itemId = item.parent().prop('id'), arr = reg.exec(itemId);

					data['hostcms[checked]['+arr[1]+']['+arr[2]+']'] = 1;
					data[itemId] = item.text();

					jQuery.ajax({
						// ajax loader
						context: jQuery('<img>').addClass('img_line').prop('src', '/modules/skin/default/js/ui/themes/base/images/ajax-loader.gif').appendTo(item),
						url: settings.path,
						type: 'POST',
						data: data,
						dataType: 'json',
						success: function(){this.remove();}
					});
				},
				action: 'apply'
			}, settings);

			return this.each(function(index, object){
				jQuery(object).on('dblclick', function(){
					var item = jQuery(this).css('display', 'none'),
					jInput = jQuery('<input>').prop('type', 'text').on('blur', function() {
						var input = jQuery(this), item = input.prev();
						item.html(input.val()).css('display', 'block');
						input.remove();
						settings.save(item, settings);
					}).on('keydown', function(e){
						if (e.keyCode == 13) {
							e.preventDefault();
							this.blur();
						}
						if (e.keyCode == 27) { // ESC
							e.preventDefault();
							var input = jQuery(this), item = input.prev();
							item.css('display', 'block');
							input.remove();
						}
					}).width('90%').prop('name', item.parent().prop('id'))
					.insertAfter(item).focus().val(item.text());
				});
			});
		},
		/*liveDraggable: function (settings) {
		  jQuery(this).on("mouseover", function() {
			 if (!jQuery(this).data("init")) {
				jQuery(this).data("init", true).draggable(settings);
			 }
		  });
		  return jQuery();
		},*/
		clearSelect: function()
		{
			return this.each(function(index, object){
				jQuery(object).empty().append(jQuery('<option>').attr('value', 0).text(' ... '));
			});
		},
		widgetWindow: function(settings)
		{
			settings = jQuery.extend({
				autoOpen: true,
				Maximize: false,
				Minimize: false,
				Reload: true,
				type: 77,
				dragStop: jQuery.sendWindowStatus,
				resizeStop: jQuery.sendWindowStatus,
				beforeClose: jQuery.sendWindowStatus
			}, settings);

			return this.each(function(index, object){
				jQuery(object).HostCMSWindow(settings);
			});
		},
		toggleHighlight: function()
		{
			return this.each(function(){
				var object = jQuery(this);
				object.toggleClass('cheked');
			});
		},
		highlightAllRows: function(checked)
		{
			return this.each(function(){
				var object = jQuery(this);

				// Устанавливаем checked для групповых чекбоксов
				object.find("input[type='checkbox'][id^='id_admin_forms_all_check']").prop('checked', checked);

				object.find("input[type='checkbox'][id^='check_']").each(function() {
					var object = $(this);

					if (object.prop('checked') != checked)
					{
						object.parents('tr').toggleHighlight();
					}
					// Устанавливаем checked
					object.prop('checked', checked);
				});
			});
		},
		setTopCheckbox: function()
		{
			return this.each(function(){
				var object = jQuery(this), bChecked = !object.find("input[type='checkbox'][id^='check_']").is(':not(:checked)');
				object.find("input[type='checkbox'][id^='id_admin_forms_all_check']").prop('checked', bChecked);
			});
		}
	});

	var baseURL = location.href, popstate = ('state' in window.history && window.history.state !== null);
	jQuery(window).bind('popstate', function(event){
		// Ignore inital popstate that some browsers fire on page load
		var startPop = !popstate && baseURL == location.href;
		popstate = true;
		if (startPop){
			return;
		}

		var state = event.state;
		if (state && state.windowId/* && state.windowId == 'id_content'*/){
			var data = state.data;
			data['_'] = Math.round(new Date().getTime());

			$.loadingScreen('show');

			jQuery.ajax({
				context: jQuery('#'+state.windowId),
				url: state.url,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: jQuery.ajaxCallback
			});
		}
		else{
			  window.location = location.href;
		}
	});

	if (jQuery.inArray('state', jQuery.event.props) < 0){
		jQuery.event.props.push('state');
	}

	var currentRequests = {};
	jQuery.ajaxPrefilter(function(options, originalOptions, jqXHR){

	  if(options.abortOnRetry){
		if(currentRequests[options.url]){
			currentRequests[options.url].abort();
		}
		currentRequests[options.url] = jqXHR;
	  }
	});

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
		reloadDesktop: function(siteId){
			jQuery('#tasksScroll .shortcut,.note,.mbmenu,.ui-dialog-content,#desktop').remove();
			jQuery('#subTaskBar .nav').css('display', 'none');
			$.loadingScreen('show');

			// add ajax '_'
			var data = jQuery.getData({});
			jQuery.ajax({
				context: jQuery('body'),
				url: '/admin/index.php?ajaxDesktopLoad&changeSiteId='+siteId,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(data){
					$.loadingScreen('hide');
					jQuery(this).append(data.form_html);
				}
			});
		},
		filterKeyDown: function(e) {
			if (e.keyCode == 13) {
				e.preventDefault();
				//jQuery(this).parents('.admin_table').find('#admin_forms_apply_button').click();
				jQuery(this).parentsUntil('table').find('#admin_forms_apply_button').click();
			}
		},
		adminCheckObject: function(settings) {
			settings = jQuery.extend({
				objectId: '',
				windowId: 'id_content'
			}, settings);

			var cbItem = jQuery("#"+settings.windowId+" #"+settings.objectId);

			if (cbItem.length > 0)
			{
				// Uncheck all checkboxes with name like 'check_'
				jQuery("#" + settings.windowId + " input[type='checkbox'][id^='check_']:not([name*='_fv_'])").prop('checked', false);

				// Check checkbox
				cbItem.prop('checked', true);
			}
			else
			{
				var Check_0_0 = jQuery('<input>')
					.attr('type', 'checkbox')
					.attr('id', settings.objectId);

				jQuery('<div>')
					.attr("style", 'display: none')
					.append(Check_0_0)
					.appendTo(
						jQuery("#"+settings.windowId)
					);

				// After insert into DOM
				Check_0_0.prop('checked', true);
			}

			$("#"+settings.windowId).setTopCheckbox();
		},
		requestSettings: function(settings) {
			settings = jQuery.extend({
				// position shift
				open: function(type, data) {
					var jWindow = jQuery(this).parent(),
						mod = jQuery('body>.ui-dialog').length % 5;
					jWindow.css('top', jWindow.offset().top + 10 * mod).css('left', jWindow.offset().left + 10 * mod);
				},
				focus: function(event, ui){
					// Текущий window
					jQuery.data(document.body, 'currentWindowId', jQuery(this).attr('id'));
				},
				path: '',
				context: '',
				action: '',
				operation: '',
				additionalParams: '',
				windowId: 'id_content',
				datasetId: 0,
				objectId: 0,
				limit: '',
				current: '',
				sortingFieldId: '',
				sortingDirection: '',
				post: {}
				//callBack: ''
			}, settings);

			return settings;
		},
		adminLoad: function(settings) {
			settings = jQuery.requestSettings(settings);

			var path = settings.path,
				data = jQuery.getData(settings);

			if (settings.additionalParams != ' ' && settings.additionalParams != '')
			{
				path += '?' + settings.additionalParams;
			}

			// Элементы списка
			var jChekedItems = jQuery("#"+settings.windowId+" :input[type='checkbox'][id^='check_']:checked"),
				iChekedItemsCount = jChekedItems.length,
				jItemsValue, iItemsValueCount, sValue;

			var reg = /check_(\d+)_(\S+)/;
			for (var jChekedItem, i=0; i < iChekedItemsCount; i++)
			{
				jChekedItem = jChekedItems.eq(i);

				var arr = reg.exec(jChekedItem.attr('id'));

				data['hostcms[checked]['+arr[1]+']['+arr[2]+']'] = 1;

				// arr[1] - ID источника, arr[2] - ID элемента
				var element_id = jChekedItem.attr('id');

				// Ищем значения записей, ID поля должно начинаться с ID checkbox-а
				jItemsValue = jQuery("#"+settings.windowId+" :input[id^='apply_"+element_id+"_fv_']"),
				iItemsValueCount = jItemsValue.length;

				for (var jValueItem, k = 0; k < iItemsValueCount; k++)
				{
					jValueItem = jItemsValue.eq(k);

					if (jValueItem.attr("type") == 'checkbox')
					{
						sValue = jValueItem.prop('checked') ? '1' : '0';
					}
					else
					{
						sValue = jValueItem.val();
					}

					data[jValueItem.attr('name')] = sValue;
				}
			}

			// Фильтр
			var jFiltersItems = jQuery("#"+settings.windowId+" :input[name^='admin_form_filter_']"),
				iFiltersItemsCount = jFiltersItems.length;

			for (var jFiltersItem, i=0; i < iFiltersItemsCount; i++)
			{
				jFiltersItem = jFiltersItems.eq(i);

				// Если значение фильтра до 255 символов
				if (jFiltersItem.val().length < 256)
				{
					// Дописываем к передаваемым данным
					data[jFiltersItem.attr('name')] = jFiltersItem.val();
				}
			}

			// Текущая страница.
			/*if (ALimit === false)
			{
				ALimit = '';
			}
			else
			{
				ALimit = '&limit=' + ALimit;
			}*/

			$.loadingScreen('show');

			jQuery.ajax({
				context: jQuery('#'+settings.windowId),
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				abortOnRetry: 1,
				success: [jQuery.ajaxCallback, jQuery.ajaxCallbackSkin, function()
				{
					var pjax = window.history && window.history.pushState && window.history.replaceState && !navigator.userAgent.match(/(iPod|iPhone|iPad|WebApps\/.+CFNetwork)/);

					/*if (settings.windowId == 'id_content'){*/
					if (pjax)
					{
						var state = {
							windowId: settings.windowId,
							url: path,
							data: data
						};
						delete data['_'];

						// jQuery.param(data) is too long => 400 bad request
						// Delete empty items
						/*for (var i in data) {
							if (data[i] === '') {
								delete data[i];
							}
						}
						var url = path + (path.indexOf('?') >= 0 ? '&' : '?') + jQuery.param(data);
						*/

						window.history.pushState(state, document.title, path);
					}
					//}
				}]
			});

			return false;
		},
		adminSendForm: function(settings) {
			settings = jQuery.requestSettings(settings);

			settings = jQuery.extend({
				buttonObject: ''
			}, settings);

			// Сохраним из визуальных редакторов данные
			if (typeof tinyMCE != 'undefined')
			{
				tinyMCE.triggerSave();
			}

			//CodePressAction('save');

			var FormNode = jQuery(settings.buttonObject).closest('form'),
				data = jQuery.getData(settings),
				path = FormNode.attr('action');

			if (settings.additionalParams != ' ' && settings.additionalParams != '')
			{
				path += '?' + settings.additionalParams;
			}

			// Очистим поле для сообщений
			jQuery("#"+settings.windowId+" #id_message").empty();

			// Отображаем экран загрузки
			$.loadingScreen('show');

			//FormNode.find(':disabled').removeAttr('disabled');

			FormNode.ajaxSubmit({
				data: data,
				context: jQuery('#'+settings.windowId),
				url: path,
				//type: 'POST',
				dataType: 'json',
				cache: false,
				success: jQuery.ajaxCallback
			});
		},
		getData: function(settings) {
			var data = (typeof settings.post != 'undefined') ? settings.post : {};

			data['_'] = Math.round(new Date().getTime());

			if (settings.action != '')
			{
				data['hostcms[action]'] = settings.action;
			}

			if (settings.operation != '')
			{
				data['hostcms[operation]'] = settings.operation;
			}

			/*if (settings.additionalParams != ' ' && settings.additionalParams != '')
			{
				path += '?' + settings.additionalParams;
			}*/

			if (settings.limit != '')
			{
				data['hostcms[limit]'] = settings.limit;
			}

			if (settings.current != '')
			{
				data['hostcms[current]'] = settings.current;
			}

			if (settings.sortingFieldId != '')
			{
				data['hostcms[sortingfield]'] = settings.sortingFieldId;
			}

			if (settings.sortingDirection != '')
			{
				data['hostcms[sortingdirection]'] = settings.sortingDirection;
			}

			data['hostcms[window]'] = settings.windowId;

			return data;
		},
		beforeContentLoad: function(object)
		{
			if (typeof tinyMCE != 'undefined')
			{
				object.find('textarea').each(function(){
					var elementId = this.id;
					if (tinyMCE.getInstanceById(elementId) != null)
					{
						tinyMCE.execCommand('mceRemoveControl', false, elementId);
						//jQuery('#content').tinymce().execCommand('mceInsertContent',false, elementId);
					}
				});
			}
		},
		insertContent: function(jObject, content)
		{
			// Fix blink in FF
			jObject.scrollTop(0).empty().html(content);
		},
		ajaxCallback: function(data, status, jqXHR)
		{
			$.loadingScreen('hide');
			if (data == null)
			{
				alert('AJAX response error.');
				return;
			}

			var jObject = jQuery(this);

			if (data.form_html !== null && data.form_html.length)
			{
				jQuery.beforeContentLoad(jObject);
				jQuery.insertContent(jObject, data.form_html);
				jQuery.afterContentLoad(jObject);
			}

			var jMessage = jObject.find("#id_message");

			if (jMessage.length === 0)
			{
				jMessage = jQuery("<div>").attr('id', 'id_message');
				jObject.prepend(jMessage);
			}

			jMessage.empty().html(data.error);

			if (typeof data.title != 'undefined' && data.title != '' && jObject.attr('id') == 'id_content')
			{
				document.title = data.title;
			}
		},
		ajaxRequest: function(settings) {

			settings = jQuery.requestSettings(settings);

			if (typeof settings.callBack == 'undefined')
			{
				alert('Callback function is undefined');
			}

			var path = settings.path;

			if (settings.additionalParams != ' ' && settings.additionalParams != '')
			{
				path += '?' + settings.additionalParams;
			}

			$.loadingScreen('show');

			var data = jQuery.getData(settings);
			data['hostcms[checked][' + settings.datasetId + '][' + settings.objectId + ']'] = 1;

			if (typeof settings.additionalData != 'undefined')
			{
				$.each(settings.additionalData, function(index, value){
					data[index] = value;
				})
			}

			var ajaxOptions = {
				context: jQuery('#'+settings.windowId + ' #' + settings.context),
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: settings.callBack,
				abortOnRetry:1
			}

			if (typeof settings.ajaxOptions != 'undefined')
			{
				$.each(settings.ajaxOptions, function(optionName, optionValue){
					ajaxOptions[optionName] = optionValue;
				})
			}

			jQuery.ajax(ajaxOptions);

			return false;
		},
		loadSelectOptionsCallback: function(data, status, jqXHR)
		{
			$.loadingScreen('hide');

			jQuery(this).empty();
			for (var key in data)
			{
				jQuery(this).append(jQuery('<option>').attr('value', key).text(data[key]));
			}
		},
		loadDivContentAjaxCallback: function(data, status, jqXHR)
		{
			$.loadingScreen('hide');
			jQuery(this).empty().html(data);

			//alert(this);
		},
		pasteStandartAnswer: function(data, status, jqXHR)
		{
			$.loadingScreen('hide');
			jQuery(this).val(jQuery(this).val() + data);

		},
		clearFilter: function(windowId)
		{
			jQuery("#" + windowId + " .admin_table_filter input").val('');
			jQuery("#" + windowId + " .admin_table_filter select").prop('selectedIndex', 0);
		},
		deleteNewProperty: function(object)
		{
			//jQuery(object).closest('.item_div').remove();
			jQuery(object).closest('[id ^= "property_"]').remove();
		},
		deleteProperty: function(object, settings)
		{
			var jObject = jQuery(object).siblings('input,select:not([onchange]),textarea');

			// For files
			if (jObject.length === 0)
			{
				jObject = jQuery(object).siblings('div,label').children('input');
			}

			var property_name = jObject.eq(0).attr('name');

			settings = jQuery.extend({
				operation: property_name
			}, settings);

			settings = jQuery.requestSettings(settings);

			var data = jQuery.getData(settings);
			data['hostcms[checked][' + settings.datasetId + '][' + settings.objectId + ']'] = 1;

			var path = settings.path;

			jQuery.ajax({
				context: jQuery('#'+settings.windowId),
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: jQuery.ajaxCallback
			});

			jQuery.deleteNewProperty(object);
		},
		setCheckbox: function(windowId, checkboxId)
		{
			jQuery("#"+windowId+" input[type='checkbox'][id='"+checkboxId+"']").attr('checked', true);
		},
		cloneProperty: function(windowId, index)
		{
			var jProperies = jQuery('#' + windowId + ' #property_' + index),
			jNewObject = jProperies.eq(0).clone(),
			iRand = Math.floor(Math.random() * 999999);

			jNewObject.insertAfter(
				jQuery('#' + windowId).find('div[id="property_' + index + '"],div[id^="property_' + index + '_"]').eq(-1)
			);

			jNewObject.attr('id', 'property_' + index + '_' + iRand);

			// Change item_div ID
			jNewObject.find("div[id^='file_']").each(function(index, object){
				jQuery(object).prop('id', jQuery(object).prop('id') + '_' + jProperies.length);
			});
			jNewObject.find("input[id^='field_id'],select,textarea").attr('name', 'property_' + index + '[]');
			jNewObject.find("div[id^='file_small'] input[id^='small_field_id']").attr('name', 'small_property_' + index + '[]').val('');
			jNewObject.find("input[id^='field_id'][type!=checkbox],input[id^='property_'][type!=checkbox],select,textarea").val('');

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

			jNewObject.find("img#delete").attr('onclick', "jQuery.deleteNewProperty(this)");

			if (jNewObject.datepicker)
			{
				jNewObject.find('input.hasDatepicker').attr('id', 'date_id_' + iRand).removeClass('hasDatepicker').datepicker();
			}
			else if(jNewObject.datetimepicker)
			{
				jNewObject.find('script').remove();
				jNewObject.find('div[id^="div_field_id_"]').datetimepicker({language: 'ru', useSeconds: true});
			}
			//jNewObject.find('input.hasDatepicker').attr('id', 'date_id_' + iRand).datepicker();

			// После внесения в DOM
 			jNewObject.find("a[onclick*='watermark_property_'],a[onclick*='watermark_small_property_']").each(function(index, object){
				var jObject = $(object), tmp = $(object).attr('onclick');
				jObject.attr('onclick', tmp.replace('_property_', '_property_' + iRand + '_'));
			});

			jNewObject.find("div[id*='watermark_property_'],div[id*='watermark_small_property_']").each(function(index, object){
				var jObject = $(object), tmp = $(object).attr('id');
				jObject.attr('id', tmp.replace('_property_', '_property_' + iRand + '_'));

				jObject.HostCMSWindow({ autoOpen: false, destroyOnClose: false, /*title: '',*/ AppendTo: '#' + jNewObject.prop('id'), width: 360, height: 230, addContentPadding: true, modal: false, Maximize: false, Minimize: false });
			});

			jNewObject.find("div[aria-labelledby*='watermark_property_'],div[aria-labelledby*='watermark_small_property_']").each(function(index, object){
				var jObject = $(object), tmp = $(object).attr('aria-labelledby');
				jObject.attr('aria-labelledby', tmp.replace('_property_', '_property_' + iRand + '_'));
			});
		},
		cloneSpecialPrice: function(windowId, cloneDelete)
		{
			var jSpecialPrice = jQuery(cloneDelete).closest('.spec_prices'),
			jNewObject = jSpecialPrice.clone();

			// Change input name
			jNewObject.find(':regex(name, ^\\S+_\\d+$)').each(function(index, object){
				var reg = /^(\S+)_(\d+)$/;
				var arr = reg.exec(object.name);
				jQuery(object).prop('name', arr[1] + '_' + '[]');
			});
			jNewObject.find("input").val('');

			//jNewObject.find("img#delete").attr('onclick', "jQuery.deleteNewProperty(this)");
			jNewObject.insertAfter(jSpecialPrice);
		},
		deleteNewSpecialprice: function(object)
		{
			var jObject = jQuery(object).closest('.spec_prices').remove();
		},
		clonePropertyInfSys: function(windowId, index)
		{
			var jProperies = jQuery('#' + windowId + ' #property_' + index),
			jNewObject = jProperies.eq(0).clone(),
			iNewId = index + 'group' + Math.floor(Math.random() * 999999),
			jDir = jNewObject.find("select[onchange]"),
			jItem = jNewObject.find("select:not([onchange])");

			jDir
				.attr('onchange', jDir.attr('onchange').replace(jItem.attr('id'), iNewId))
				.val(jProperies.eq(0).find("select[onchange]").val());

			jItem
				.attr('name', 'property_' + index + '[]')
				.attr('id', iNewId)
				.val(jProperies.eq(0).find("select:not([onchange])").val());

			jNewObject.find("img#delete").attr('onclick', "jQuery.deleteNewProperty(this)");
			jNewObject.insertAfter(jProperies.eq(-1));
		},
		cloneFile: function(windowId)
		{
			var jProperies = jQuery('#' + windowId + ' #file'),
			jNewObject = jProperies.eq(0).clone();
			jNewObject.find("input").attr('name', 'file[]').val('');
			jNewObject.insertAfter(jProperies.eq(-1));
		},
		showWindow: function(windowId, content, settings)
		{
			settings = jQuery.extend({
				/*modal: true, */autoOpen: false, addContentPadding: false, resizable: true, draggable: true, Minimize: false, Closable: true
			}, settings);

			var jWin = jQuery('#' + windowId);

			if (!jWin.length)
			{
				jWin = jQuery('<div>')
					.addClass('hostcmsWindow')
					.attr('id', windowId)
					.appendTo(jQuery(document))
					.HostCMSWindow(settings)
					.HostCMSWindow('open');
				jWin.html(content);
			}
			return jWin;
		},
		applyTooltip: function(windowId)
		{
			jQuery("#"+windowId+" acronym").tooltip({
				tipClass: 'shadowed tooltip',
				offset: [-30, 10],
				position: 'left center',
				predelay: 300,
				effect: 'fade',
				layout: '<div id="tooltip"/>',
				slideOffset: 30,
				onBeforeShow: function(event, position)
				{
					var trigger = this.getTrigger();
					var obj = this.getTip();
					var conf = this.getConf();

					var triggerOuterWidth = trigger.outerWidth();

					var delta = (obj.outerWidth() + triggerOuterWidth) / 2 - triggerOuterWidth + 10;

					// Заменяем смещение, чтобы подсказка была от начала строки
					conf.offset = [conf.offset[0], delta];

					// Оформление еще не было задано
					if (obj.find('.tl').size() === 0)
					{
						obj.applyShadow();

						// Tail
						jQuery('<div>').attr('class', 'shadow_tail')
						.css('left', "3px")
						.css('bottom', "-27px")
						.css('width', "13px")
						.append(jQuery('<img>').attr("src", '/admin/images/shadow_tail.gif'))
						.appendTo(obj);
					}
				}
			});
		},
		sendWindowStatus: function(event, ui)
		{
			jQuery.ajax({
				url: '/admin/index.php?' + 'userSettings&moduleId=' + ui.options.moduleId
					+ '&type=' + ui.options.type
					+ '&position_x=' + (ui.position.left + (ui.helper.outerWidth(true) - ui.helper.innerWidth()) / 2)
					+ '&position_y=' + (ui.position.top + (ui.helper.outerHeight(true) - ui.helper.innerHeight()) / 2)
					+ '&width=' + ui.helper.width() + '&height=' + ui.helper.height() + '&active=' + (event.type == 'hostcmswindowbeforeclose' ? 0 : 1),
				type: 'get',
				dataType: 'json',
				success: function(){}
			});
		},
		// Созание/пересоздание контекстного меню
		createContextualMenu: function(settings)
		{
			settings = jQuery.extend({
				iconPath: '',
				menuWidth: 200,
				openOnRight: true,
				overflow: 2,
				menuSelector: ".menuContainer",
				hasImages: true,
				fadeInTime: 10,
				fadeOutTime: 100,
				adjustLeft: 0,
				adjustTop: 0,
				submenuTop: 5,
				submenuLeft: 0,
				closeOnMouseOut: true,
				closeAfter: 100,
				onContextualMenu: function(o,e){},
				shadow: true
			}, settings);

			jQuery(document).buildContextualMenu(settings);
			// Удаляем открывшееся контекстное меню при пересоздании
			jQuery(".menuContainer").remove();
		},
		showTab: function(windowId, tabId)
		{
			$("#"+windowId+" div.tab_page[id!='" + tabId + "']").hideObjects();
			$("#"+windowId+" #" + tabId).showObjects();
			$("#"+windowId+" #tab ul li.li_tab").removeClass('current_li');
			$("#"+windowId+" #tab ul #li_"+tabId).addClass('current_li');
		},
		loadTagsListCallback: function(data, status, jqXHR)
		{
			$.loadingScreen('hide');

			var windowId = this.parents("[class='hostcmsWindow ui-dialog-content ui-widget-content contentpadding']").attr('id');

			if(windowId == undefined)
				windowId = 'id_content';

			var that = $('#'+ windowId).data('that');

			that.element.siblings('.tag').removeClass('tag-important');

			that.input.data('typeahead').source = data;
			that.input.data('typeahead').process(data);

			// Добавить удаление that после реализации прерывания AJAX-запроса с помощью Prefilter
			//$('#'+ windowId).removeData('that');
		},
		// Изменение статуса заказа товара
		changeOrderStatus: function(windowId)
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

			$("#"+windowId+" #status_datetime").val(day + '.' + month + '.' + date.getFullYear() + ' ' + hours + ':' + minutes + ':' + '00');
		}
	});
})(jQuery);

/**
 * jQuery Cookie plugin
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
jQuery.cookie = function (key, value, options) {

    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }

        value = String(value);

        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : cookie_encode(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};

function cookie_encode(string){
	//full uri decode not only to encode ",; =" but to save uicode charaters
	var decoded = encodeURIComponent(string);
	//encod back common and allowed charaters {}:"#[] to save space and make the cookies more human readable
	var ns = decoded.replace(/(%7B|%7D|%3A|%22|%23|%5B|%5D)/g,function(charater){return decodeURIComponent(charater);});
	return ns;
}