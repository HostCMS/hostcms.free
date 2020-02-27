function isEmpty(str) {
    return (!str || 0 === str.length);
}

(function($){
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

	$.ajaxSetup({
		cache: false,
		error: function(jqXHR, textStatus, errorThrown){
			$.loadingScreen('hide');
			jqXHR.statusText != 'abort' && alert('AJAX error: ' + textStatus + '! HTTP: ' + jqXHR.status + ' ' + jqXHR.statusText + "\n" + jqXHR.responseText);
		}
	});

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
			if (typeof data.module != 'undefined' && data.module != null)
			{
				// Выделить текущий пункт левого бокового меню
				$.currentMenu(data.module);
			}
		},
		currentMenu: function(moduleName)
		{
			$('#sidebar li').removeClass('active open');

			/*$('#menu-' + moduleName).addClass('active')
				.parents('li').addClass('active open');*/

			$('#menu-' + moduleName).each(function(){
				$(this).addClass('active')
					.parents('li').addClass('active open');

				// Submenu
				if ($(this).children('ul').length)
				{
					$(this).addClass('open');
				}
			});

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

			mainFormLocker.saveStatus().unlock();

			jQuery.ajax({
				context: jDivWin,
				url: cmsrequest,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: [jQuery.ajaxCallback, function() { mainFormLocker.restoreStatus() }]
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
				jObject.find(".modal-title").text(data.title);
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
		changePrintButton: function(object) {
			var print_price_id = $(object).val();

			$.each($('.print-price ul.dropdown-menu li:has(a) > a'), function (i, el) {
				var onclick = $(this).attr('onclick'),
					matches = onclick.match(/(\&\w+\S+\&)/),
					split = matches[0].split('&'),
					text = onclick.replace(split[1], 'shop_price_id=' + print_price_id);

				$(this).attr('onclick', text);
			});
		},
		showPrintButton: function(window_id, id) {
			$('#' + window_id + ' .print-button').removeClass('hidden');

			$.each($('#' + window_id + ' .print-button ul.dropdown-menu li:has(a) > a'), function (i, el) {
				var onclick = $(this).attr('onclick'),
					text = onclick.replace('[]', '[' + id + ']');

				$(this).attr('onclick', text);
			});
		},
		leadStatusBar: function(lead_id, windowId) {
			$(".lead-stage-wrapper.lead-stage-wrapper-" + lead_id + " .lead-stage").on("click", function(){
				if (!$(this).hasClass('finish'))
				{
					$(".lead-stage-wrapper.lead-stage-wrapper-" + lead_id + " .lead-stage").each(function(){
						$(this)
							.removeClass("active")
							.removeClass("previous")
							.css('background-color', '')
							.css('border-color', '');
					});

					$(this).addClass("active");
					$(this).prevUntil(".lead-stage-wrapper.lead-stage-wrapper-" + lead_id).addClass("previous");

					var color = $(this).css('background-color'),
						darkerColor = $(this).data('dark');

					$(".lead-stage-wrapper.lead-stage-wrapper-" + lead_id + " .lead-stage.previous")
						.css('background-color', color)
						.css('border-color', darkerColor);

					$(".lead-status-name.lead-status-name-" + lead_id)
						.text($(this).data('name'))
						.css('color', $(this).data('color'));

					// Отключаем клик, если провальный этап
					if ($('.lead-stage-wrapper.lead-stage-wrapper-' + lead_id).find('.lead-stage.active.failed').length)
					{
						$('.lead-stage-wrapper.lead-stage-wrapper-' + lead_id + ' .lead-stage').each(function(){
							$(this)
								.unbind('click')
								.css('cursor', 'default');
						});
					}
				}

				var lead_status_id = $(this).data('id'),
					id = 'hostcms[checked][0][' + lead_id + ']',
					post = {},
					operation = '';

				post['last_step'] = 0;

				if ($(this).hasClass('finish'))
				{
					operation = 'finish';
					post['last_step'] = 1;
				}

				post[id] = 1;
				post['lead_status_id'] = lead_status_id;

				$.adminLoad({path: '/admin/lead/index.php', action: 'morphLead', operation: operation, post: post, additionalParams: '', windowId: windowId});
			});

			var jActiveLi = $(".lead-stage-wrapper.lead-stage-wrapper-" + lead_id + " .lead-stage.active"),
				color = jActiveLi.css('background-color'),
				darkerColor = jActiveLi.data('dark');

			jActiveLi.prevUntil(".lead-stage-wrapper.lead-stage-wrapper-" + lead_id).addClass("previous");

			$(".lead-stage-wrapper.lead-stage-wrapper-" + lead_id + " .lead-stage.previous")
				.css('background-color', color)
				.css('border-color', darkerColor);

			// Отключаем клик, если финишный этап
			if ($('.lead-stage-wrapper.lead-stage-wrapper-' + lead_id).find('.lead-stage.active.finish').length
				|| $('.lead-stage-wrapper.lead-stage-wrapper-' + lead_id).find('.lead-stage.active.failed').length
			)
			{
				$('.lead-stage-wrapper.lead-stage-wrapper-' + lead_id + ' .lead-stage').each(function(){
					$(this)
						.unbind('click')
						.css('cursor', 'default');
				});
			}
		},
		morphLeadChangeType: function(object) {
			$('.lead-exist-client').addClass('hidden');
			$('.lead-deal-template').addClass('hidden');

			// Существующий клиент
			if ($(object).val() == 2)
			{
				$(object).parents('.row').find('.lead-exist-client').removeClass('hidden');
				$(object).parents('.bootbox.modal').removeAttr('tabindex');
			}

			if ($(object).val() == 4)
			{
				$(object).parents('.row').find('.lead-deal-template').removeClass('hidden');
			}
		},
		showCropButton: function(object, id, windowId) {
			var file = object[0].files[0],
				avialableExtensions = ["jpg", "jpeg", "png", "gif", "webp"];

			if (file)
			{
				var fileName = file.name;
					extension = fileName.substr((fileName.lastIndexOf(".") + 1));

				if ($.inArray(extension, avialableExtensions) > -1)
				{
					$('#' + windowId + ' #crop_' + id).removeClass("hidden").addClass("input-group-addon control-item");
				}
			}
		},
		showCropModal: function(id, imagePath, imageName) {
			var $input = $('input#' + id),
				$parent = $input.parents('.input-group'),
				file = $input[0].files[0];

			// Changed file
			if (file) {
				// Change file name
				imageName = file.name;

				if (URL) {
					// Change file path
					imagePath = URL.createObjectURL(file);
				}
				else if (FileReader) {
					reader = new FileReader();
					reader.onload = function (e) {
						// Change file path
						imagePath = reader.result;
					};
					reader.readAsDataURL(file);
				}
			}

			$parent.append(
				'<div class="modal fade crop-image-modal" id="modal_' + id + '" tabindex="-1" role="dialog" aria-labelledby="' + id + 'ModalLabel">\
					<div class="modal-dialog" role="document">\
						<div class="modal-content">\
							<div class="modal-header">\
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
								<h4 class="modal-title">' + i18n['change_image'] + '</h4>\
							</div>\
							<div class="modal-body">\
								<div class="img-container"><img class="img-responsive center-block" id="img_' + id + '" src="' + $.escapeHtml(imagePath) + '" /></div>\
							</div>\
							<div class="modal-footer">\
								<div class="row">\
									<div class="col-md-9 docs-buttons">\
										<div class="btn-group">\
											<button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In">\
												<span class="fa fa-search-plus"></span>\
											</button>\
											<button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out">\
												<span class="fa fa-search-minus"></span>\
											</button>\
										</div>\
										<div class="btn-group">\
											<button type="button" class="btn btn-warning" data-method="rotate" data-option="-90" title="Rotate Left">\
												<span class="fa fa-rotate-left"></span>\
											</button>\
											<button type="button" class="btn btn-warning" data-method="rotate" data-option="90" title="Rotate Right">\
												<span class="fa fa-rotate-right"></span>\
											</button>\
										</div>\
										<div class="btn-group">\
											<button type="button" class="btn btn-palegreen" data-method="scaleX" data-option="-1" title="Flip Horizontal">\
												<span class="fa fa-arrows-h"></span>\
											</button>\
											<button type="button" class="btn btn-palegreen" data-method="scaleY" data-option="-1" title="Flip Vertical">\
												<span class="fa fa-arrows-v"></span>\
											</button>\
										</div>\
										<div class="btn-group margin-left-20">\
											<span id="dataWidth' + id + '">0</span> &times; <span id="dataHeight' + id + '">0</span>\
										</div>\
									</div>\
									<div class="col-md-3 text-align-right">\
										<button type="button" class="btn btn-success crop-' + id + '">' + i18n['save'] + '</button>\
									</div>\
								</div>\
							</div>\
						</div>\
					</div>\
				</div>'
			);

			var $image = $('#img_' + id),
				$modal = $('#modal_' + id),
				$dataHeight = $('#dataHeight' + id),
				$dataWidth = $('#dataWidth' + id),
				options = {
					autoCrop: false,
					aspectRatio: NaN,
					viewMode: 0,
					crop: function (e) {
						$dataHeight.text(Math.round(e.detail.height));
						$dataWidth.text(Math.round(e.detail.width));
					},
					ready: function (e) {
						var containerData = $(this).cropper('getContainerData'),
							imageData = $(this).cropper('getImageData');

						if (imageData.naturalWidth < containerData.width && imageData.naturalHeight < containerData.height)
						{
							$(this).data('cropper').zoomTo(1);
						}
					}
				};

			// Methods
			$('#modal_' + id + ' .docs-buttons').on('click', '[data-method]', function () {
				var $this = $(this),
					data = $this.data(),
					cropper = $image.data('cropper'),
					cropped,
					$target,
					result;

				if ($this.prop('disabled') || $this.hasClass('disabled')) {
					return;
				}

				if (cropper && data.method) {
					data = $.extend({}, data); // Clone a new one

					if (typeof data.target !== 'undefined') {
						$target = $(data.target);

						if (typeof data.option === 'undefined') {
							try {
								data.option = JSON.parse($target.val());
							} catch (e) {
								console.log(e.message);
							}
						}
					}

					cropped = cropper.cropped;

					switch (data.method) {
						case 'rotate':
							if (cropped && options.viewMode > 0) {
								$image.cropper('clear');
							}
						break;
						case 'getCroppedCanvas':
							if (uploadedImageType === 'image/jpeg') {
								if (!data.option) {
								  data.option = {};
								}

								data.option.fillColor = '#fff';
							}
						break;
					}

					result = $image.cropper(data.method, data.option, data.secondOption);

					switch (data.method) {
						case 'rotate':
						if (cropped && options.viewMode > 0) {
							$image.cropper('crop');
						}
						break;

						case 'scaleX':
						case 'scaleY':
							$(this).data('option', -data.option);
						break;

						case 'getCroppedCanvas':
						if (result) {
							// Bootstrap's Modal
							$('#getCroppedCanvasModal').modal().find('.modal-body').html(result);

							if (!$download.hasClass('disabled')) {
								download.download = uploadedImageName;
								$download.attr('href', result.toDataURL(uploadedImageType));
							}
						}
						break;

						case 'destroy':
						if (uploadedImageURL) {
							URL.revokeObjectURL(uploadedImageURL);
							uploadedImageURL = '';
							$image.attr('src', originalImageURL);
						}
						break;
					}

					if ($.isPlainObject(result) && $target) {
						try {
							$target.val(JSON.stringify(result));
						} catch (e) {
							console.log(e.message);
						}
					}
				}
			});

			$modal.modal('show');

			$modal.on('shown.bs.modal', function () {
				// Cropper
				$image.cropper(options);
			}).on('hidden.bs.modal', function () {
				$image.cropper('destroy');
				$modal.remove();
			});

			$('#modal_' + id + ' .crop-' + id).on('click', function() {
				var cropper = $image.data('cropper');

				if (cropper) {
					var canvas;

					canvas = $image.cropper('getCroppedCanvas');
					canvas.toBlob(function (blob) {
						// Firefox < 62 workaround exploiting https://bugzilla.mozilla.org/show_bug.cgi?id=1422655
						// specs compliant (as of March 2018 only Chrome)
						const dT = new ClipboardEvent('').clipboardData || new DataTransfer();
						dT.items.add(new File([blob], imageName));

						$input[0].files = dT.files;
					});
				}

				$modal.modal('hide');
			});
		},
		escapeHtml: function(str) {
			// This does not escape quotes
			escaped = new Option(str).innerHTML;

			// Replace quotes
			return escaped.replace(/"/g, '&quot;');
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
			if (typeof resultData['Bookmarks'] != 'undefined')
			{
				var jEventUl = $('.navbar-account #bookmarksListBox .scroll-bookmarks > ul');

				$('li[id != "bookmark-0"]', jEventUl).remove();

				if (resultData['Bookmarks'].length)
				{
					$('li[id = "bookmark-0"]', jEventUl).hide();

					$.each(resultData['Bookmarks'], function(index, event) {
						// Добавляем закладку в список
						$.addBookmark(event, jEventUl);
					});
				}
				else
				{
					$('li[id = "bookmark-0"]', jEventUl).show();
				}
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
				try {
					var storage = localStorage.getItem('bookmarks'),
						storageObj = JSON.parse(storage);

					if (!storageObj || typeof storageObj['expired_in'] == 'undefined')
					{
						storageObj = {userId: 0, expired_in: 0};
					}

					if (jBookmarksListBox.data('userId') != storageObj['userId'] || Date.now() > storageObj['expired_in'])
					{
						storageObj['expired_in'] = Date.now() + 120000;

						bNeedsRequest = true;
					}
					else
					{
						$.refreshBookmarksCallback(storageObj);
					}
				} catch(e) {
					if (e.name == "NS_ERROR_FILE_CORRUPTED") {
						alert("Sorry, it looks like your browser storage has been corrupted.");
					}
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
				height: $('.navbar-account #bookmarksListBox .scroll-bookmarks > ul li[id != "bookmark-0"]').length ? ($(window).height() * 0.7) : '55px',
				// height: 'auto',
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
					<a href="' + (oBookmark['href'].length ? $.escapeHtml(oBookmark['href']) : '#') + '" onclick="' + (oBookmark['onclick'].length ? $.escapeHtml(oBookmark['onclick']) : '') + '">\
						<div class="clearfix notification-bookmark">\
							<div class="notification-icon">\
								<i class="' + $.escapeHtml(oBookmark['ico']) + ' bg-darkorange white"></i>\
							</div>\
							<div class="notification-body">\
								<span class="title">' + $.escapeHtml(oBookmark['name']) + '</span>\
								<span class="description">' + $.escapeHtml(oBookmark['href']) + '</span>\
							</div>\
							<div class="notification-extra">\
								<i class="fa fa-times gray bookmark-delete" onclick="$.removeUserBookmark({title: \'' + $.escapeHtml(oBookmark['remove-title']) +'\', submit: \'' + $.escapeHtml(oBookmark['remove-submit']) + '\', cancel: \'' + $.escapeHtml(oBookmark['remove-cancel']) + '\', bookmark_id: ' + oBookmark['id'] + '}); event.stopPropagation(); event.preventDefault();"></i>\
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
		editWarehouses: function(object) {
			$.each( $(".shop-item-warehouses-list .row"), function (index, item) {
				$(this).removeClass('hidden');
				$(this).find('input[name ^= warehouse_]').prop('disabled', false).focus();
				$(this).find('select[name ^= warehouse_shop_price_id_]').removeClass('hidden');
				$(this).find('select[name ^= warehouse_shop_price_id_]').parents('div').prev().removeClass('hidden');
			});
			$(object).addClass('hidden');
		},
		toggleShopPrice: function(shop_price_id) {
			$('.toggle-shop-price-' + shop_price_id)
				.toggleClass('hidden')
				.find('input').prop('disabled', function(i, v) { return !v; });

			$.each($(".shop-item-table tbody tr"), function (index, item) {
				var button = $(this).find('a.delete-associated-item'),
					parentTd = button.parents('td');

				parentTd.detach().appendTo($(this));
			});
		},
		toggleCoupon: function(object) {
			var jInput = $('input[name=coupon_text]'),
				length = jInput.val().length;

			jInput.parents('.form-group').toggleClass('hidden');

			length === 0 && $.generateCoupon(jInput);
		},
		generateCoupon: function(jInput) {
			$.ajax({
				url: '/admin/shop/discount/index.php',
				type: 'POST',
				data: {'generate-coupon': 1},
				dataType: 'json',
				error: function(){},
				success: function (answer) {
					jInput
						.val(answer.coupon)
						.focus();
				}
			});
		},
		showEmails: function(data)
		{
			$.ajax({
				url: '/admin/printlayout/index.php',
				type: 'POST',
				data: {'showEmails': 1, 'representative': data.id},
				dataType: 'json',
				error: function(){},
				success: function (answer) {
					if (answer)
					{
						$(".email-select").empty().trigger("change");

						$.each(answer, function(id, object){
							var text = object.email;

							if (object.type !== null)
							{
								text += ' [' + object.type + ']';
							}

							var newOption = new Option(text, object.email, true, true);
							$(".email-select").append(newOption).trigger('change');
						});
					}
				}
			});
		},
		insertSeoTemplate: function(el, text) {
			el && el.insertAtCaret(text);
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
			$('#showTopFilterButton').toggleClass('active');
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
		kanbanStepMove: function (windowId, path, data, moveCallback) {
			$.ajax({
				data: data,
				type: "POST",
				dataType: 'json',
				url: path,
				success: moveCallback
			});
		},
		_kanbanStepMoveCallback: function(result){
			if (result.status == 'success')
			{
				if (result.update)
				{
					var jKanban = $('.kanban-board .kanban-board-header');

					$.each(result.update, function(id, object){
						jKanban.find('#data-' + id).html(object.data);
						// jKanban.find('#data-' + id + ' .kanban-deals-amount').text(object.amount);
					});
				}
			}
			else if (result.status == 'error' && result.error_text)
			{
				alert(result.error_text);
				$('ul#entity-list-' + result.target_id).addClass('error-drop');
			}
		},
		_kanbanStepMoveLeadCallback: function(result){
			if (result.status == 'success')
			{
				if(result.last_step == 1)
				{
					var id = 'hostcms[checked][0][' + itemObject.id + ']',
						lead_status_id = result.lead_status_id
						post = {};

					post[id] = 1;
					post['mode'] = 'edit';
					post['lead_status_id'] = lead_status_id;

					$.adminLoad({path: '/admin/lead/index.php', action: 'morphLead', operation: 'finish', post: post, additionalParams: '', windowId: windowId});
				}
				else if(result.type == 2)
				{
					$(itemObject).addClass('failed');
				}
			}
		},
		sortableKanban: function(options) {
			var options = jQuery.extend({
				path: '.',
				container: null,
				updateData: false,
				windowId: 'id_content',
				moveCallback: $._kanbanStepMoveCallback
			}, options);

			$(options.container + ' .connectedSortable').sortable({
				items: "> li:not('.failed'):not('.finish')",
				connectWith: options.container + ' .connectedSortable',
				placeholder: 'placeholder',
				handle: ".drag-handle",
				helper: "clone",
				// revert: true,
				// scroll: false,
				receive: function (event, ui) {
					var sender_id = $(ui.sender[0]).data('step-id'),
						target_id = $(this).data('step-id'),
						$element = $(event.target),
						$item = $(ui.item[0]);

					if ($element.hasClass('kanban-action-item'))
					{
						ui.item
							.addClass('hidden')
							.addClass('just-hidden');

						$element.css('background-color', $element.data('old-background'));
						$element.css('color', '#fff');

						target_id = $element.data('id');

						$item.data('sender', target_id);
					}

					$.kanbanStepMove(options.windowId, options.path, {id: $item.data('id'), sender_id: sender_id, target_id: target_id, update_data: +options.updateData}, options.moveCallback);

					setTimeout(function () {
						if ($element.hasClass('error-drop'))
						{
							ui.sender.sortable("cancel");
							$element.removeClass('error-drop');
						}
					}, 200);
				},
				start: function (event, ui) {
					var $item = $(ui.item[0]);

					$(options.container + ' .kanban-action-wrapper').removeClass('hidden');

					$item.removeClass('cancel-' + $item.data('id'));

					// Ghost
					$(options.container + ' .connectedSortable').find('li:hidden')/*.not('.placeholder')*/
						.addClass('ghost-item')
						.addClass('cancel-' + $item.data('id'))
						.css('opacity', .5)
						.show();
				},
				stop: function (event, ui) {
					ui.item.parents('.kanban-action-item').find('.kanban-action-item-name').addClass('hidden');
					ui.item.parents('.kanban-action-item').find('.return').removeClass('hidden');

					$(ui.item[0]).data('target', $(event.target).data('step-id'));

					if (ui.item.parents('.kanban-action-item').length)
					{
						setTimeout(function () {
							$.closeActions(options.container, ui);
						}, 3000);
					}
					else
					{
						$.closeActions(options.container, ui);
					}

					// Ghost
					$(options.container + ' .connectedSortable').find('li.ghost-item')
						.removeClass('ghost-item')
						.css('opacity', 1);

					$(options.container + ' .connectedSortable').sortable("option", "scroll", true);
				},
				over: function (event, ui) {
					var $element = $(event.target);

					if ($element.hasClass('kanban-action-item'))
					{
						$element.css('background-color', $element.data('background'));
						$element.css('color', $element.data('old-background'));
					}
				},
				out: function (event, ui) {
					var $element = $(event.target);

					if ($element.hasClass('kanban-action-item'))
					{
						$element.css('background-color', $element.data('old-background'));
						$element.css('color', '#fff');
					}

					$(options.container + ' .connectedSortable').sortable("option", "scroll", true);
				},
				sort: function (event, ui) {
					// removes anything that starts with "cancel-"
					$('html').removeClass(function (index, css) {
						return (css.match (/\bcancel-\S+/g) || []).join(' ');
					});

					var y = ui.position.top,
						x = ui.position.left;

					if (y > $(options.container).height() || x > $(options.container).width())
					{
						$(options.container + ' .connectedSortable').sortable("option", "scroll", false);
					}
				}
			}).disableSelection();

			$(options.container + ' .connectedSortable .return').on('click', function(){
				var jLi = $(options.container + ' .connectedSortable').find('li[class *= "cancel-"]'),
					target_id = jLi.data('target'),
					sender_id = jLi.data('sender');

				$(options.container + ' ul#entity-list-' + target_id).sortable("cancel");

				$.kanbanStepMove(options.windowId, options.path, {id: $(jLi[0]).data('id'), sender_id: sender_id, target_id: target_id, update_data: 1}, options.moveCallback);

				$(this).parents('.kanban-action-item').find('.kanban-action-item-name').removeClass('hidden');
				$(this).parents('.kanban-action-item').find('.return').addClass('hidden');

				$(options.container + ' .connectedSortable').find('.just-hidden').removeClass('hidden');
			});
		},
		closeActions: function(container, ui) {
			$(container + ' .kanban-action-wrapper').slideUp("slow", function(){
				$(this)
					.addClass('hidden')
					.removeAttr('style');

				ui.item.parents('.kanban-action-item').find('.kanban-action-item-name').removeClass('hidden');
				ui.item.parents('.kanban-action-item').find('.return').addClass('hidden');

				$(ui.item[0]).removeClass('cancel-' + $(ui.item[0]).data('id'));

				// Remove item
				$('.kanban-actions').find('li.cancel-' + $(ui.item[0]).data('id')).remove();
				$('.kanban-actions li.just-hidden').remove();
			});
		},
		showKanban: function(container) {
			var $kanban = $(container + ' > .row:first'),
				$prevNav = $('.horizon-prev', container),
				$nextNav = $('.horizon-next', container);

			$kanban.hover(
				function(){
					// in
					if ($kanban.get(0).clientWidth < $kanban.get(0).scrollWidth - $kanban.get(0).scrollLeft)
					{
						$nextNav.show();
					}
				}, function(){
					// out
					$nextNav.hide();
				}
			);

			$.fn.horizon = function () {
				// Set mousewheel event
				$kanban.mousewheel(function(event, delta) {
					this.scrollLeft -= (delta * 30);

					showButtons(this.scrollLeft);

					event.preventDefault();
				});

				// Click and hold action on nav buttons
				$nextNav.mousedown(function () {
					if ($.fn.horizon.defaults.interval)
					{
						clearInterval($.fn.horizon.defaults.interval);
					}

					$.fn.horizon.defaults.interval = setInterval(function() { scrollLeft(); }, 50);
				}).mouseup(function() {
					clearInterval($.fn.horizon.defaults.interval);
				});

				$prevNav.mousedown(function () {
					if ($.fn.horizon.defaults.interval)
					{
						clearInterval($.fn.horizon.defaults.interval);
					}

					$.fn.horizon.defaults.interval = setInterval(function() { scrollRight(); }, 50);
				}).mouseup(function() {
					clearInterval($.fn.horizon.defaults.interval);
				});

				// Keyboard buttons
				$(window).on('keydown', function (e) {
					if (scrolls[e.which]) {
						scrolls[e.which]();
						e.preventDefault();
					}
				});

				showButtons($.fn.horizon.defaults.interval);
			};

			// Global vars
			$.fn.horizon.defaults = {
				delta: 0,
				interval: 0
			};

			// Left scroll
			var scrollLeft = function () {
				var i2 = $.fn.horizon.defaults.delta + 1;
				$kanban.scrollLeft($kanban.scrollLeft() + (i2 * 30));

				showButtons($kanban.scrollLeft());
			};

			// Right scroll
			var scrollRight = function () {
				var i2 = $.fn.horizon.defaults.delta - 1;
				$kanban.scrollLeft($kanban.scrollLeft() + (i2 * 30));

				showButtons($kanban.scrollLeft());
			};

			// Left-Right buttons
			var showButtons = function (index) {
				if (index === 0) {
					if ($.fn.horizon.defaults.interval)
					{
						$prevNav.hide(function (){
							clearInterval($.fn.horizon.defaults.interval);
						});
					}
					else
					{
						$prevNav.hide();
					}

					if ($kanban.get(0).clientWidth < $kanban.get(0).scrollWidth - $kanban.get(0).scrollLeft)
					{
						$nextNav.show();
					}
				} else if ($kanban.get(0).clientWidth >= $kanban.get(0).scrollWidth - $kanban.get(0).scrollLeft) {
					$prevNav.show();

					if ($.fn.horizon.defaults.interval)
					{
						//console.log('1hide');
						$nextNav.hide(function (){
							clearInterval($.fn.horizon.defaults.interval);
						});
					}
					else
					{
						$nextNav.hide();
					}
				} else {
					$nextNav.show();
					$prevNav.show();
				}
			};

			// Keyboard buttons array
			var scrolls = {
				'right': scrollRight,
				'down': scrollRight,
				'left': scrollLeft,
				'up': scrollLeft,
				37: scrollLeft,
				38: scrollRight,
				39: scrollRight,
				40: scrollLeft
			};

			$kanban.horizon();
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
			mainFormLocker.disable();

			var settings = jQuery.extend({
					title: '',
					message: '',
					className: ''
				}, settings),
				dialog = bootbox.dialog({
					message: settings.message,
					title: $.escapeHtml(settings.title),
					className: settings.className,
					onEscape: function() {}
				}),
				modalBody = dialog.find('.modal-body'),
				content = dialog.find('.modal-body .bootbox-body div');

			dialog.on('shown.bs.modal', function () {
				$('html').css('overflow', 'hidden');
			});

			// before remove:
			settings.onHide && dialog.on('hide.bs.modal', settings.onHide);
			// after remove:
			dialog.on('hidden.bs.modal', function(){
				mainFormLocker.enable();

				if($(".modal").hasClass('in')){
					$('body').addClass('modal-open');
				}

				$('html').css('overflow', '');
			});

			if (typeof settings.width != 'undefined')
			{
				dialog.find('.modal-dialog').width(settings.width);
			}

			if (typeof settings.height != 'undefined')
			{
				modalBody.height(settings.height);
			}

			/*if (settings.error != '')
			{
				var jMessage = modalBody.find('#id_message');
				$(jMessage[0]).empty().html(settings.error);
				$(jMessage[0]).nextAll().remove();
			}*/
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
					try {
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
					} catch(e) {
						if (e.name == "NS_ERROR_FILE_CORRUPTED") {
							alert("Sorry, it looks like your browser storage has been corrupted.");
						}
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
				Notify('<img width="24px" height="24px" src="' + $.escapeHtml(data["info"].avatar) + '"><span style="padding-left:10px">' + $.escapeHtml(data["info"].text) + '</span>', '', 'bottom-left', '7000', 'blueberry', 'fa-comment-o', true, !!data["info"].sound);

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
					try {
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
					} catch(e) {
						if (e.name == "NS_ERROR_FILE_CORRUPTED") {
							alert("Sorry, it looks like your browser storage has been corrupted.");
						}
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
					try {
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
					} catch(e) {
						if (e.name == "NS_ERROR_FILE_CORRUPTED") {
							alert("Sorry, it looks like your browser storage has been corrupted.");
						}
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

			$('#chatbar').resizable({ handles:"w" });

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
		loadWallpaper: function(wallpaper_id) {
			$.ajax({
				url: '/admin/user/index.php',
				type: "POST",
				data: {'loadWallpaper':1, 'wallpaper_id':wallpaper_id},
				dataType: 'json',
				error: function(){},
				success: function (answer) {
					if (answer.id)
					{
						var jWallpapersList = $('ul.wallpaper-picker');
						jWallpapersList.append(
							'<li>\
								<span class="colorpick-btn">\
									<img onclick="$.changeWallpaper(this)" data-id="' + answer.id + '" data-original-path="' + answer.original_path + '" src="' + answer.src + '" />\
								</span>\
							</li>'
						);

						$('#user-info-dropdown .login-area').effect('pulsate', {times: 3}, 3000);
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
				type: 'POST',
				data: {'wallpaper-id': wallpaper_id},
				dataType: 'json',
				error: function(){},
				success: function (object) {
					$('head').append('<style>body.hostcms-bootstrap1:before{ background-image: url(' + original + ') }</style>');
				}
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
		toggleRepresentativeFields: function(selector) {
			$(selector + ' .hidden-field').toggleClass('hidden');
			$(selector + ' .representative-show-link').parents('.row').remove();
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
				try {
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
				} catch(e) {
					if (e.name == "NS_ERROR_FILE_CORRUPTED") {
						alert("Sorry, it looks like your browser storage has been corrupted.");
					}
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
			jBox.append(
				'<li id="event-' + oEvent['id'] + '">\
					<a href="' + (oEvent['href'].length ? $.escapeHtml(oEvent['href']) : '#') + '" onclick="' + (oEvent['onclick'].length ? oEvent['onclick'] : '') + '">\
						<div class="clearfix notification-clock">\
							<div class="notification-icon">\
								<i class="' + $.escapeHtml(oEvent['icon']) + ' fa-fw white" style="background-color: ' + $.escapeHtml(oEvent['background-color']) + '"></i>\
							</div>\
							<div class="notification-body">\
								<span class="title">' + $.escapeHtml(oEvent['name']) + '</span>\
								<span class="description"><i class="fa fa-clock-o"></i> ' + $.escapeHtml(oEvent['start']) + ' — <span class="notification-time">' + $.escapeHtml(oEvent['finish']) + '</span>\
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

			var jNotificationsListBox = $('.navbar-account #notificationsListBox');

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
				}
				else
				{
					$('.navbar-account #notificationsListBox .footer').hide();
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
				notificationExtra = '',
				bUnread = oNotification['read'] == 0;

			if (oNotification['extra'].length)
			{
				var jNotificationExtra = $('<div class="notification-extra">');

				oNotification['extra'].forEach(function(item) {
					jNotificationExtra.append('<i class="fa ' + $.escapeHtml(item) + ' themeprimary"></i>');
				})

				oNotification['extra']['description'].length && jNotificationExtra.append('<span class="description">' + $.escapeHtml(oNotification['extra']['description']) + '</span>')

				notificationExtra = jNotificationExtra.html();
			}

			jBox.prepend(
				'<li id="notification-' + oNotification['id'] + '" class="' + (bUnread ? 'unread' : '') + '">\
					<a href="' + (oNotification['href'].length ? $.escapeHtml(oNotification['href']) : '#') + '" onclick="' + (oNotification['onclick'].length ? oNotification['onclick'] : '') + '">\
						<div class="clearfix">\
							<div class="notification-icon">\
								<i class="' + $.escapeHtml(oNotification['icon']['ico']) + ' ' + $.escapeHtml(oNotification['icon']['background-color']) + ' ' + $.escapeHtml(oNotification['icon']['color']) + '"></i>\
							</div>\
							<div class="notification-body">\
								<span class="title">' + $.escapeHtml(oNotification['title']) + '</span>\
								<span class="description"></span>\
								<span class="site-name">' + (typeof oNotification['site'] !== 'undefined' && oNotification['site'] !== null ? $.escapeHtml(oNotification['site']) : '') + '</span>\
							</div>\
							' + notificationExtra +
						'</div>\
					</a>\
				</li>')
				.find('li#notification-' + oNotification['id'] + ' span.description')
				.html((oNotification['description'].length ? ($.escapeHtml(oNotification['description']) + '<br/>') : '') /* oNotification['datetime']*/ );

			// Показываем всплывающее непрочитанное уведомление
			bUnread && Notify($.escapeHtml(oNotification['title']), $.escapeHtml(oNotification['description']), 'bottom-left', '7000', oNotification['notification']['background-color'], oNotification['notification']['ico'], true, soundEnabled);

			// Открыт выпадающий список уведомлений
			if ($('.navbar li#notifications').hasClass('open'))
			{
				 // Если список уведомлений был пуст, устанавливаем полосу прокрутки
				!$('.navbar-account #notificationsListBox .scroll-notifications > ul li').length && $.setNotificationsSlimScroll();

				 // Делаем прочитанными уведомления, находящиеся в видимой части списка
				 $.readNotifications();
			}
		},
		recountUnreadNotifications: function()
		{
			var countUnreadNotifications = $('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread').length;

			// В зависимости от наличия или отсутствия непрочитанных уведомлений добавляем или удаляем "wave in" для значка уведомлений
			$('.navbar li#notifications > a').toggleClass('wave in', !!countUnreadNotifications);
			//!countUnreadNotifications && $('.navbar li#notifications > a').removeClass('wave in');

			// Меняем значение баджа с числом непрочитанных уведомлений
			$('.navbar li#notifications > a > span.badge')
				.html(countUnreadNotifications > 99 ? countUnreadNotifications = '∞' : countUnreadNotifications)
				.toggleClass('hidden', !countUnreadNotifications);
		},
		refreshNotificationsCallback: function(resultData)
		{
			var jNotificationsListBox = $('.navbar-account #notificationsListBox');

			// Есть уведомления для сотрудника
			if (resultData['userId'] && resultData['userId'] == jNotificationsListBox.data('currentUserId'))
			{
				// Массив идентификаторов непрочитанных уведомлений в списке уведомлений
				/*var unreadNotifications = [];

				$('.navbar-account #notificationsListBox .scroll-notifications > ul li.unread').each(function (){
					unreadNotifications.push($(this).attr('id'));
				})

				// Непрочитанные уведомления из БД
				$.each(resultData['unreadNotifications'], function(index, notification){

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
				});*/

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

				$.recountUnreadNotifications();

				// Обновление продолжительности рабочего дня
				$('.workday-timer').html(resultData['workdayDuration']);

				// Обновление кнопок управления рабочим днем
				var aStatuses = ['ready', 'denied', 'working', 'break', 'completed', 'expired'],
					status = $('li.workday #workdayControl').data('status');

				$('li.workday #workdayControl')
					.toggleClass(aStatuses[status] + ' ' + aStatuses[resultData['workdayStatus']])
					.data('status', resultData['workdayStatus']);

				if (resultData['workdayStatus']	== 5)
				{
					$('#user-info-dropdown .login-area').addClass('wave in');
				}
				else
				{
					$('#user-info-dropdown .login-area').removeClass('wave in');
				}

				$.blinkColon(resultData['workdayStatus']);
			}
		},

		localStorageGetItem: function(itemName) {
			var bLocalStorage = typeof localStorage !== 'undefined';

			if (bLocalStorage)
			{
				try {
					var storage = localStorage.getItem(itemName),
						storageObj = JSON.parse(storage);

					return storageObj;
				} catch(e) {
					if (e.name == "NS_ERROR_FILE_CORRUPTED") {
						alert("Sorry, it looks like your browser storage has been corrupted.");
					}
				}
			}

			return null;
		},

		localStorageSetItem: function(itemName, object) {
			var bLocalStorage = typeof localStorage !== 'undefined';

			if (bLocalStorage)
			{
				try {
					localStorage.setItem(itemName, JSON.stringify(object));
				} catch (e) {
					if (e == QUOTA_EXCEEDED_ERR) {
						console.log('localStorage: QUOTA_EXCEEDED_ERR');
					}
				}
			}
		},

		// Автоматическое обновление списка уведомлений
		refreshNotificationsList: function() {
			// add ajax '_'

			var data = jQuery.getData({}),
				jNotificationsListBox  = $('.navbar-account #notificationsListBox'),
				lastNotificationId = jNotificationsListBox.data('lastNotificationId') ? +jNotificationsListBox.data('lastNotificationId') : 0,
				storageObj = $.localStorageGetItem('notifications'),
				bNeedsRequest = false;

			if (storageObj !== null)
			{
				if (!storageObj || typeof storageObj['expired_in'] == 'undefined')
				{
					storageObj = {expired_in: 0, lastNotificationId: 0};
				}

				// При окрытии новой вкладки (!lastNotificationId) загружаем данные из БД, а не из хранилища
				if (Date.now() > storageObj['expired_in']/* || !lastNotificationId*/)
				{
					bNeedsRequest = true;
				}
				else if(lastNotificationId < storageObj['lastNotificationId'])
				{
					storageObj['localStorage'] = true;
					$.refreshNotificationsCallback(storageObj);
				}

				// Скрываем уведомления, прочитанные на других вкладках, ID которых внесены в хранилище
				var storageObj = $.localStorageGetItem('notificationRead');

				if (storageObj && typeof storageObj['IDs'] !== 'undefined')
				{
					$.each(storageObj['IDs'], function (index, value){
						$('.navbar-account #notificationsListBox .scroll-notifications > ul li#notification-' + value + '.unread').removeClass('unread');
					});

					if (Date.now() > storageObj['expire'])
					{
						$.localStorageSetItem('notificationRead', []);
					}
				}
			}
			else
			{
				bNeedsRequest = true;
			}

			if (bNeedsRequest)
			{
				data['lastNotificationId'] = lastNotificationId;
				data['currentUserId'] = jNotificationsListBox.data('currentUserId');

				$.ajax({
					//context: textarea,
					url: '/admin/index.php?ajaxWidgetLoad&moduleId=' + jNotificationsListBox.data('moduleId') + '&type=0',
					type: 'POST',
					data: data,
					dataType: 'json',
					error: function(){},
					success: [function(resultData){

						//if (bLocalStorage)
						if (storageObj !== null)
						{
							resultData['expired_in'] = Date.now() + 10000;
						}

						$.localStorageSetItem('notifications', resultData);

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
			$.recountUnreadNotifications();

			if (masVisibleUnreadNotifications.length)
			{
				// Добавление информации о прочитанных сообщениях в хранилище
				var storageObj = $.localStorageGetItem('notificationRead');

				if (!storageObj || typeof storageObj['IDs'] == 'undefined')
				{
					storageObj = {IDs: [], expire: 0};
				}

				// Добавляем в массив прочитанных
				storageObj['IDs'] = storageObj['IDs'].concat(masVisibleUnreadNotifications);
				storageObj['expire'] = Date.now() + 60000;

				$.localStorageSetItem('notificationRead', storageObj);

				// add ajax '_'
				var data = jQuery.getData({});

				data['notificationsListId'] = masVisibleUnreadNotifications;
				data['currentUserId'] = $('.navbar-account #notificationsListBox').data('currentUserId');

				$.ajax({
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
			// Mark all current user notifications as read
			$.ajax({
				url: '/admin/user/index.php',
				type: 'POST',
				data: { 'setNotificationsRead': 1 },
				dataType: 'json'
			});

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
							message: i18n['remove_event'],
							buttons: {
								confirm: {
									label: i18n['yes'],
									className: 'btn-success'
								},
								cancel: {
									label: i18n['no'],
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
												Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'success', 'fa-check', true, true)
											}
											else if (result['message']) // Ошибка, отменяем действие
											{
												result['error'] && revertFunc();
												Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'danger', 'fa-warning', true, true)
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
		deleteNewProperty: function(object)
		{
			//jQuery(object).closest('.item_div').remove();
			jQuery(object).closest('[id ^= "property_"]').remove();
		},
		deleteProperty: function(object, settings)
		{
			//var jObject = jQuery(object).siblings('input,select:not([onchange]),textarea');
			var jObject = jQuery(object).parents('div.input-group');

			jObject = jObject.find('input:not([id^="filter_"]),select:not([onchange]),textarea');

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
		cloneProperty: function(windowId, index)
		{
			var jProperies = jQuery('#' + windowId + ' #property_' + index),
				jSourceProperty = jProperies.eq(0);

			// Объект окна настроек большого изображения у родителя
			var oSpanFileSettings =  jSourceProperty.find("span[id ^= 'file_large_settings_']");

			// Закрываем окно настроек большого изображения
			if (oSpanFileSettings.length && oSpanFileSettings.children('i').hasClass('fa-times'))
			{
				oSpanFileSettings.click();
			}

			// Объект окна настроек малого изображения у родителя
			oSpanFileSettings =  jSourceProperty.find("span[id ^= 'file_small_settings_']");
			// Закрываем окно настроек малого изображения
			if (oSpanFileSettings.length && oSpanFileSettings.children('i').hasClass('fa-times'))
			{
				oSpanFileSettings.click();
			}

			var html = jSourceProperty[0].outerHTML, // clone with parent
				iRand = Math.floor(Math.random() * 999999);

			html = html
				.replace(/(id_property_[\d_]*)/g, 'id_property_clone' + iRand);

			// var jNewObject = jSourceProperty.clone();
			var jNewObject = jQuery(jQuery.parseHTML(html, document, true));

			// Clear autocomplete value
			jNewObject.find("input.ui-autocomplete-input")
				.attr('value', '')
				.val('');

			jNewObject.insertAfter(jProperies.eq(-1));

			jNewObject.find("textarea")
				.removeAttr('wysiwyg')
				.css('display', '');

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
			jNewObject.find("input[id^='id_property_'][type!=checkbox],input[id^='small_property_'][type!=checkbox],input[class*='description'][type!=checkbox],select,textarea").val('');

			jNewObject.find("input[id^='create_small_image_from_large_small_property']").attr('checked', true);

			// Change input name
			jNewObject.find(':regex(name, ^\\S+_\\d+_\\d+$)').each(function(index, object){
				var reg = /^(\S+)_(\d+)_(\d+)$/;
				var arr = reg.exec(object.name);
				jQuery(object).prop('name', arr[1] + '_' + arr[2] + '[]');
				var inputId = jQuery(object).prop('id');
				jNewObject.find("a[id='crop_" + inputId + "']").attr('onclick', "$.showCropModal('" + inputId + "', '', '')");
			});

			jNewObject.find("div.img_control div, a[id^='preview_'], a[id^='delete_'], div[role='application']").remove();
			jNewObject.find("input[type='text'].description-large").attr('name', 'description_property_' + index + '[]');
			jNewObject.find("input[type='text'].description-small").attr('name', 'description_small_property_' + index + '[]');

			jNewObject.find(".file-caption-wrapper")
				.addClass('hidden')
				.parents('.input-group').find('input:first-child').removeClass('hidden');

			// For checking field
			jNewObject.find(':input').blur();
		},
		clonePropertyInfSys: function(windowId, index)
		{
			var jProperies = jQuery('#' + windowId + ' #property_' + index),
				html = jProperies[0].outerHTML,
				iRand = Math.floor(Math.random() * 999999); // clone with parent

			html = html
				.replace(/oSelectFilter(\d+)/g, 'oSelectFilter$1clone' + iRand)
				.replace(/(id_group_[\d_]*)/g, 'id_group_clone' + iRand)
				.replace(/(id_property_[\d_]*)/g, 'id_property_clone' + iRand)
				.replace(/(input_property_[\d_]*)/g, 'input_property_clone' + iRand);

			//jNewObject = jProperies.eq(0).clone(),
			var jNewObject = jQuery(jQuery.parseHTML(html, document, true)),
				//iNewId = index + 'group' + Math.floor(Math.random() * 999999),
				jDir = jNewObject.find("select[onchange]"),
				jItem = jNewObject.find("select:not([onchange])");

			jDir
				//.attr('onchange', jDir.attr('onchange').replace(jItem.attr('id'), iNewId))
				.val(jProperies.eq(0).find("select[onchange]").val());

			jItem
				.attr('name', 'property_' + index + '[]')
				//.attr('id', iNewId)
				.val(jProperies.eq(0).find("select:not([onchange])").val());

			jNewObject.find("img#delete").attr('onclick', "jQuery.deleteNewProperty(this)");
			jNewObject.insertAfter(jProperies.eq(-1));
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
		cloneFile: function(windowId)
		{
			var jProperies = jQuery('#' + windowId + ' #file'),
				jNewObject = jProperies.eq(0).clone();

			jNewObject.find("input[type='file']").attr('name', 'file[]').val('');
			jNewObject.find("input[type='text']").attr('name', 'description_file[]').val('');

			jNewObject.insertAfter(jProperies.eq(-1));
		},
		// Показ сотрудников в списке select2
		templateResultItemResponsibleEmployees: function (data, item){

			var arraySelectItemParts = data.text.split("%%%"),
				className;

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

			if (data.element)
			{
				var $element = $(data.element);

				className = $element.attr("class");

				if ($element.attr("style"))
				{
					// Добавляем стили для групп и элементов. Элементам только при показе выпадающего списка
					($element.is("optgroup") || $element.is("option") && $(item).hasClass("select2-results__option")) && $(item).attr("style", $element.attr("style"));
				}
			}

			// Компания, отдел, ФИО сотрудника
			var resultHtml = '<span class="' + className + '">' + $.escapeHtml(arraySelectItemParts[0]) + '</span>';

			if (arraySelectItemParts[2])
			{
				// Список должностей через запятую
				resultHtml += '<span class="user-post">' + $.escapeHtml(arraySelectItemParts[2].split('###').join(', ')) + '</span>';
			}

			// Изображение
			if (arraySelectItemParts[3])
			{
				resultHtml = '<img src="' + $.escapeHtml(arraySelectItemParts[3]) + '" height="30px" class="user-image img-circle">' + resultHtml;
			}

			// Удаляем часть с названием отдела
			arraySelectItemParts[1] && delete(arraySelectItemParts[1]);

			return resultHtml;
		},

		// Показ выбранных сотрудников в select2
		templateSelectionItemResponsibleEmployees: function (data, item){
			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class"),
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
			var resultHtml = '<span class="' + className + '">' + $.escapeHtml(arraySelectItemParts[0]) + '</span>';

			// Формируем title элемента
			data.title = $.escapeHtml(arraySelectItemParts[0]);

			if (arraySelectItemParts[1] || arraySelectItemParts[2])
			{
				resultHtml += '<br />';
				if (arraySelectItemParts[1])
				{
					resultHtml += '<span class="company-department">' + $.escapeHtml(arraySelectItemParts[1]) + '</span>';
					data.title += " - " + $.escapeHtml(arraySelectItemParts[1]);
				}

				// Список должностей через запятую
				if (arraySelectItemParts[2])
				{
					var departmentPosts = arraySelectItemParts[2].split('###').join(', ');

					resultHtml += (arraySelectItemParts[1] ? ' → ' : '') + '<span class="user-post">' + $.escapeHtml(departmentPosts) + '</span>';
					data.title += " - " + $.escapeHtml(departmentPosts);
				}
			}

			// Компания, отдел, ФИО сотрудника
			resultHtml =  '<div class="user-info">' + resultHtml + '</div>';

			// Изображение
			if (arraySelectItemParts[3])
			{
				resultHtml = '<img src="' + $.escapeHtml(arraySelectItemParts[3]) + '" height="30px" class="user-image pull-left img-circle">' + resultHtml;
			}

			return resultHtml;
		},
		// Показ клиентов выпадающего списка select2
		templateResultItemSiteusers: function (data, item)
		{
			if (!data.text)
			{
				return '';
			}

			var arraySelectItemParts = data.text.split("%%%");

			// Компания/ФИО клиента
			var resultHtml = '<span>' + $.escapeHtml(arraySelectItemParts[0]) + '</span>';

			if (arraySelectItemParts[1])
			{
				resultHtml = '<img src="' + $.escapeHtml(arraySelectItemParts[1]) + '" height="20px" class="margin-right-5 img-circle">' + resultHtml;
			}

			return resultHtml;
		},

		// Формирование результатов выбора клиентов в select2
		templateSelectionItemSiteusers: function (data, item)
		{
			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class");

			// Компания/ФИО клиента
			var resultHtml = '<span class="' + className + '">' + $.escapeHtml(arraySelectItemParts[0]) + '</span>';

			// Устанавливает title для элемента
			data.title = $.escapeHtml(arraySelectItemParts[0]);

			if (arraySelectItemParts[1])
			{
				resultHtml = '<img src="' + $.escapeHtml(arraySelectItemParts[1]) + '" height="20px" class="margin-top-5 margin-right-5 margin-bottom-5 img-circle">' + resultHtml;
			}

			return resultHtml;
		},
		joinUser2DealStep: function(settings)
		{
			// {deal_step_id} or {deal_id, deal_template_step_id}
			settings = $.extend({
				join_user: 1
			}, settings);

			var oButton = $('.join-user a');

			$('i', oButton)
				.removeClass('fa-check fa-times')
				.addClass('fa-spinner fa-spin');

			$.ajax({
				url: '/admin/deal/index.php',
				type: "POST",
				dataType: 'json',
				data: settings,
				success: function(result) {
					var buttonIcoClass, dealTemplateStepId, stepColor;

					if (result['success'])
					{
						buttonIcoClass = 'fa-times';

						oButton
							.addClass('btn-darkorange')
							.removeAttr('style');
					}
					else
					{
						buttonIcoClass = 'fa-check';

						dealTemplateStepId = $('#deal-steps .steps').data('template-step-id');
						stepColor = $('#deal-steps #simplewizardstep' + dealTemplateStepId + ' .step' ).css('color');

						oButton
							.removeClass('btn-darkorange')
							.css({'color': '#fff', 'background-color': stepColor, 'border-color': stepColor});
					}

					$('span', oButton).text(result['name']);

					$('i', oButton)
						.removeClass('fa-spinner fa-spin')
						.addClass(buttonIcoClass);

					// Reload users list
					$.loadDealStepUsers(result.deal_step_id);
				}
			});
		},
		loadDealStepUsers: function(deal_step_id)
		{
			$.ajax({
				url: '/admin/deal/index.php',
				type: "POST",
				dataType: 'json',
				data: {'load_deal_step_users': 1, 'deal_step_id': deal_step_id},
				success: function(result) {
					// Clear container
					$('.deal-step-users-list').html('');

					if (result['users'])
					{
						$('.deal-step-users-list').append(
							'<div class="row profile-container">\
								<div class="col-xs-12"><h6 class="row-title before-azure no-margin-top">' + $.escapeHtml(result['title']) + '</div>\
							</div>\
							<div class="row">\
							</div>'
						);

						$.each(result['users'], function(i, oUser){
							$('.deal-step-users-list .row:last-child').append(
								'<div class="col-xs-12 col-sm-3">\
									<div class="databox databox-graded">\
										<div class="databox-left no-padding">\
											<img src="' + $.escapeHtml(oUser['avatar']) + '" style="width:65px; height:65px;">\
										</div>\
										<div class="databox-right bg-whitesmoke">\
											<div class="databox-stat orange radius-bordered" style="right: 0; left: 7px">\
												<div class="databox-text black semi-bold"><a class="black" href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][' + oUser['id'] + ']=1" onclick="$.modalLoad({path: \'/admin/user/index.php\', action: \'view\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' + oUser['id'] + ']=1\', windowId: \'id_content\'}); return false">' + $.escapeHtml(oUser['name']) + '</a></div>\
												<div class="databox-text darkgray">' + $.escapeHtml(oUser['post']) + '</div>\
											</div>\
										</div>\
									</div>\
								</div>'
							);
						});

						$('.deal-step-users-list').removeClass('hidden');
					}
				}
			});
		},
		dealAddUserBlock: function(object)
		{
			var id = object.id.split('_', 2)[1],
				dataset = object.type == 'company' ? 0 : 1;

			$('.deal-users-row').append('<div class="col-xs-12 col-sm-6 col-lg-3 user-block">\
				<div class="databox">\
					<div class="databox-left no-padding">\
						<div class="img-wrapper">\
							<img src="' + $.escapeHtml(object.avatar) + '" style="width:65px; height:65px;"/>\
							<a href="/admin/siteuser/representative/index.php?hostcms[action]=view&hostcms[checked][' + dataset + '][' + id + ']=1" onclick="$.modalLoad({path: \"/admin/siteuser/representative/index.php\", action: \"view\", operation: \"modal\", additionalParams: \"hostcms[checked][' + dataset + '][' + id + ']=1\", windowId: \"id_content\"}); return false">\
								<span class="fa fa-eye fa-2x"></span>\
							</a>\
						</div>\
					</div>\
					<div class="databox-right bg-whitesmoke">\
						<div class="databox-text">\
							<div class="semi-bold">' + $.escapeHtml(object.name) + '</div>\
							<div class="darkgray">' + $.escapeHtml(object.phone) + '</div>\
							<div>' + (object.email.length
								? '<a href="mailto:' + $.escapeHtml(object.email) + '">' + $.escapeHtml(object.email) + '</a>'
								: '') + '</div>\
						</div>\
						<div class="delete-responsible-user" onclick="$.dealRemoveUserBlock($(this))">\
							<i class="fa fa-times"></i>\
						</div>\
					</div>\
				</div>\
				<input type="hidden" name="deal_siteusers[]" value="' + object.id + '"/>\
			</div>');
		},
		dealRemoveUserBlock: function(object)
		{
			if (confirm(i18n['confirm_delete']))
			{
				object.parents('.user-block').remove();
			}
		},		
		rgb2hex: function(rgb)
		{
			if (typeof rgb !== 'undefined')
			{
				rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
				function hex(x) {
					return ("0" + parseInt(x).toString(16)).slice(-2);
				}
				return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
			}
		},
		/* changeDealTemplateName: function (oNewDealStep, oCurrentDealStep)
		{
			var	hexNew = $.rgb2hex(oNewDealStep.css("color")),
				hexCurrent = oCurrentDealStep && $.rgb2hex(oCurrentDealStep.css("color"));

				$(".deal-template-step-name .current-step").css("background-color", hexCurrent);
				$(".deal-template-step-name .new-step").css("background-color", hexNew);


		}, */
		changeUserWorkdayButtons: function(status)
		{
			var data = {},
				aStatuses = ['ready', 'denied', 'working', 'break', 'completed', 'expired'],
				currentStatusIndex = $('li.workday #workdayControl').data('status');

			if (currentStatusIndex == status)
			{
				return;
			}

			// Начинаем рабочий день
			if (currentStatusIndex == 0 && status == 2)
			{
				data = {'startUserWorkday': 1};
			}
			// Перерыв или Продолжаем рабочий день
			else if ((currentStatusIndex == 2 && status == 3) || (currentStatusIndex == 3 && status == 2))
			{
				data = {'pauseUserWorkday': 1};
			}
			// Завершаем рабочий день
			else if ((currentStatusIndex == 2 || currentStatusIndex == 5) && status == 4)
			{
				data = {'stopUserWorkday': 1};
			}
			else
			{
				return false;
			}

			$.ajax({
				url: '/admin/user/index.php',
				type: "POST",
				data: data,
				dataType: 'json',
				error: function(){},
				success: function (answer) {
					if (answer.result)
					{
						$('li.workday #workdayControl')
							.toggleClass(aStatuses[currentStatusIndex] + ' ' + aStatuses[answer.result])
							.data('status', answer.result);

						if (answer.result != 5)
						{
							$('#user-info-dropdown .login-area').removeClass('wave in');
						}

						$('span.user-workday-last-date').remove();

						$.blinkColon(answer.result);
					}
				}
			});
		},
		blinkColon: function(workdayStatus)
		{
			var toggle = true;

			// Если работаем или рабочий день кончился, но не завершен сотрудником
			if ((workdayStatus == 2 || workdayStatus == 5) && !window.timerId)
			{
				window.timerId = setInterval(function() {
					$('.workday-timer .colon').css({ visibility: toggle ? 'hidden' : 'visible' });
					toggle = !toggle;
				}, 1000);
			}

			if ((workdayStatus != 2 && workdayStatus != 5) && window.timerId)
			{
				clearInterval(window.timerId);
				window.timerId = undefined;
				$('.workday-timer .colon').css({ visibility: 'visible' });
			}
		},
		updateWarehouseCounts: function(shop_warehouse_id)
		{
			var aItems = [];

			$.each($('.shop-item-table > tbody tr[data-item-id]'), function (index, item) {
				aItems.push($(this).data('item-id'));
			});

			$.ajax({
				url: '/admin/shop/warehouse/inventory/index.php',
				type: "POST",
				data: {'update_warehouse_counts': 1, 'shop_warehouse_id': shop_warehouse_id, 'items': aItems, 'datetime': $('input[name=datetime]').val()},
				dataType: 'json',
				error: function(){},
				success: function (answer) {
					$.each($('.shop-item-table > tbody tr[data-item-id]'), function (index, item) {
						var id = $(this).data('item-id');

						if (answer[id])
						{
							$(this).find('.calc-warehouse-count').text(answer[id]['count']);

							var jInput = $(this).find('.set-item-count');

							jInput.change();
							$.changeWarehouseCounts(jInput, 0);
						}
					});
				}
			});
		},
		changeWarehouseCounts: function(jInput, type)
		{
			jInput.change(function() {
				// Replace ',' on '.'
				var replace = $(this).val().replace(',', '.');
				$(this).val(replace);

				if ($(this).val() < 0)
				{
					$(this).val(0);
				}

				var parentTr = $(this).parents('tr'),
					quantity = $.isNumeric($(this).val()) && $(this).val() > 0
						? parseFloat($(this).val())
						: 0,
					price = $.isNumeric(parentTr.find('.price').text())
						? parseFloat(parentTr.find('.price').text())
						: 0,
					sum = $.mathRound(quantity * price, 2);

				switch (type)
				{
					// Инвентаризация
					case 0:
						var calcCount = $.isNumeric(parentTr.find('.calc-warehouse-count').text())
							? parseFloat(parentTr.find('.calc-warehouse-count').text())
							: 0,
						diffCount = $.mathRound((quantity - calcCount), 3),
						diffCountTd = parentTr.find('.diff-warehouse-count');

						parentTr.find('.calc-warehouse-sum').text(price * calcCount);

						var calcSum = $.isNumeric(parentTr.find('.calc-warehouse-sum').text())
							? parseFloat(parentTr.find('.calc-warehouse-sum').text())
							: 0,
						invSumSpan = parentTr.find('.warehouse-inv-sum'),
						diffSumSpan = parentTr.find('.diff-warehouse-sum');

						diffCountTd
							.removeClass('palegreen')
							.removeClass('darkorange');

						if (diffCount > 0)
						{
							diffCount = '+' + diffCount;
							diffCountTd.addClass('palegreen');
						}
						else if (diffCount == 0)
						{
							diffCountTd
								.removeClass('palegreen')
								.removeClass('darkorange');
						}
						else
						{
							diffCountTd.addClass('darkorange');
						}

						// Отклонение на складе
						diffCountTd.text(diffCount);

						// Сумма учтенных
						invSumSpan.text(sum);

						var invSum = $.isNumeric(invSumSpan.text())
							? parseFloat(invSumSpan.text())
							: 0,
							diffSum = $.mathRound((sum - calcSum), 2),
							parentDiffTd = diffSumSpan.parents('td');

						parentDiffTd
							.removeClass('palegreen')
							.removeClass('darkorange');

						if (diffSum > 0)
						{
							diffSum = '+' + diffSum;
							parentDiffTd.addClass('palegreen');
						}
						else if (diffSum == 0)
						{
							parentDiffTd
								.removeClass('palegreen')
								.removeClass('darkorange');
						}
						else
						{
							parentDiffTd.addClass('darkorange');
						}

						// Отклонение в сумме
						diffSumSpan.text(diffSum);
					break;
					// Оприходование
					case 1:
					case 2:
						parentTr.find('.calc-warehouse-sum').text(sum);
						parentTr.find('.hidden-shop-price').val(price);
					break;
					case 5:
						parentTr.find('.calc-warehouse-sum').text(sum);
					break;
				}
			});
		},
		prepareShopPrices: function()
		{
			$.each($('.shop-item-table > tbody tr[data-item-id]'), function (index, item) {

				$(this).find('td:first-child').text(index + 1);
				var jInput = $(this).find('.set-item-new-price');

				jInput.change();
				$.changeShopPrices(jInput);
			});
		},
		changeShopPrices: function(jInput) {
			jInput.change(function() {
				// Replace ',' on '.'
				var replace = $(this).val().replace(',', '.');
				$(this).val(replace);

				if ($(this).val() < 0)
				{
					$(this).val(0);
				}

				var parentTr = $(this).parents('tr'),
					newPrice = $.isNumeric($(this).val()) && $(this).val() > 0
						? parseFloat($(this).val())
						: 0,
					shop_price_id = $(this).data('shop-price-id')
					oldPrice = $.isNumeric(parentTr.find('.old-price-' + shop_price_id).text()) && parentTr.find('.old-price-' + shop_price_id).text() > 0
						? parseFloat(parentTr.find('.old-price-' + shop_price_id).text())
						: 0,
					percent = 0,
					diffPersentSpan = parentTr.find('.percent-diff-' + shop_price_id),
					diffPercentValue = (newPrice * 100) / oldPrice;

					diffPersentSpan
						.removeClass('palegreen')
						.removeClass('darkorange');

					if(diffPercentValue > 100)
					{
						percent = '+' + $.mathRound((diffPercentValue - 100), 2);

						diffPersentSpan.addClass('darkorange');
					}
					else
					{
						percent = '-' + $.mathRound((100 - diffPercentValue), 2);

						diffPersentSpan.addClass('palegreen');
					}

					if (percent == '-0')
					{
						percent = 0;
						diffPersentSpan
							.removeClass('darkorange')
							.removeClass('palegreen');
					}

					Number.isFinite(diffPercentValue) && newPrice && diffPersentSpan.text(percent + '%');
			});
		},
		recalcPrice: function()
		{
			// $.loadingScreen('show');

			var jSelect = $('select.select-price'),
				shop_price_id = jSelect.val(),
				aItems = [];

			$.each($('.shop-item-table > tbody tr[data-item-id]'), function (index, item) {
				var id = $(this).data('item-id').toString();

				if (id.includes(','))
				{
					var aIds = $(this).data('item-id').split(',');
					$.each(aIds, function(i, shop_item_id) {
						aItems.push(shop_item_id);
					});
				}
				else
				{
					aItems.push(id);
				}
			});

			$.ajax({
				url: '/admin/shop/warehouse/index.php',
				type: "POST",
				data: {'load_prices': 1, 'shop_price_id': shop_price_id, 'items': aItems},
				dataType: 'json',
				error: function(){},
				success: function (answer) {
					$.each($('.shop-item-table > tbody tr[data-item-id]'), function (index, item) {
						var container = $(this),
							id = container.data('item-id').toString();

						if (id.includes(','))
						{
							var aIds = $(this).data('item-id').split(',');
							$.each(aIds, function(i, shop_item_id) {
								if (answer[shop_item_id])
								{
									var price = answer[shop_item_id]['price'],
										type = !i ? 'writeoff' : 'incoming';

									// container.find('.price-' + shop_item_id).text(price);
									container.find('.' + type + '-price').text(price);

									container.find('input[name = writeoff_price_' + shop_item_id + ']').val(price);
									container.find('input[name = incoming_price_' + shop_item_id + ']').val(price);
								}
							});
						}
						else
						{
							if (answer[id])
							{
								container.find('.price').text(answer[id]['price']);

								var jInput = container.find('.set-item-count');

								jInput.change();
								$.changeWarehouseCounts(jInput, 1);
							}
						}
					});
				}
			});

			// $.loadingScreen('hide');
		},
		addRegradeItem: function(shop_id, placeholder)
		{
			$('.shop-item-table').append(
				'<tr id="" data-item-id="">\
					<td class="index"></td>\
					<td><input class="writeoff-item-autocomplete form-control" data-type="writeoff" placeholder="' + placeholder + '" /><input type="hidden" name="writeoff_item[]" value="" /></td>\
					<td><span class="writeoff-measure"></span></td>\
					<td><span class="writeoff-price"></span></td>\
					<td><input class="incoming-item-autocomplete form-control" data-type="incoming" placeholder="' + placeholder + '"/><input type="hidden" name="incoming_item[]" value="" /></td>\
					<td><span class="incoming-measure"></span></td>\
					<td><span class="incoming-price"></span></td>\
					<td width="80"><input class="set-item-count form-control" name="shop_item_quantity[]" value=""/></td>\
					<td><a class="delete-associated-item" onclick="$(this).parents(\'tr\').remove()"><i class="fa fa-times-circle darkorange"></i></a></td>\
				</tr>'
			);

			var aItemIds = ['',''];

			$('.writeoff-item-autocomplete, .incoming-item-autocomplete').autocompleteShopItem({ 'shop_id': shop_id, 'shop_currency_id': 0}, function(event, ui) {
				var type = $(this).data('type'),
					parentTr = $(this).parents('tr');

				parentTr.find('.' + type + '-measure').text(ui.item.measure);
				parentTr.find('.' + type + '-price').text(ui.item.price_with_tax);

				$(this).next('input').val(ui.item.id);

				parentTr.find('.' + type + '-item-autocomplete').attr('id', ui.item.id);

				aItemIds = $.getIds(aItemIds, $(this));
				parentTr.attr('data-item-id', aItemIds.slice(-2).join(','));
			});

			// recount index
			$('.shop-item-table > tbody tr:last-child td.index').text($('.shop-item-table > tbody tr').length);
		},
		getIds: function(aItemIds, object)
		{
			var type = object.data('type'),
				id = object.attr('id'),
				index = type == 'writeoff' ? 2 : 1;

			aItemIds[aItemIds.length - index] = id;

			return aItemIds;
		},
		focusAutocomplete: function(object)
		{
			$(object).keydown(function(event){
				if(event.keyCode == 13){
					event.preventDefault();
					$('.add-shop-item').focus();
					return false;
				}
			});
		},
		mathRound: function(value, number)
		{
			switch (number)
			{
				case 2:
				default:
					coeff = 100;
				break;
				case 3:
					coeff = 1000;
				break;
			}

			// return parseFloat(value).toFixed(number);
			return Math.round(value * coeff) / coeff;
		},
		appendInput: function(windowId, InputName, InputValue)
		{
			var windowId = $.getWindowId(windowId), obj = $('#' + windowId + ' .adminForm');

			if (obj.length && obj.eq(0).find("input[name='" + InputName + "']").length === 0)
			{
				obj.append(
					$('<input>')
					.attr('type', 'hidden')
					.attr('name', InputName)
					.val(InputValue));
			}
		},
		toogleInputsActive: function(jForm, disableButtons)
		{
			jForm.find('.formButtons input').attr('disabled', disableButtons);
		},
		getWindowId: function(WindowId)
		{
			if (typeof WindowId == 'undefined' || WindowId == '')
			{
				WindowId = 'id_content';
			}

			return WindowId;
		},
		filterKeyDown: function(e) {
			if (e.keyCode == 13) {
				e.preventDefault();
				//jQuery(this).parents('.admin_table').find('#admin_forms_apply_button').click();
				jQuery(this).parentsUntil('table').find('#admin_forms_apply_button').click();
			}
		},
		loadingScreen: function(method) {
			// Method calling logic
			if (methods[method]) {
			  return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
			} else {
			  alert('Method ' +  method + ' does not exist on jQuery.loadingScreen');
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

					var uiDialog = $(this).parent('.ui-dialog');
					uiDialog.width(uiDialog.width()).height(uiDialog.height());
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
				view: '',
				post: {},
				loadingScreen: true
				//callBack: ''
			}, settings);

			return settings;
		},
		modalLoad: function(settings) {
			settings = jQuery.requestSettings(settings);

			var path = settings.path,
				modalId = 'Modal_' + Date.now(),
				data = jQuery.getData(
					jQuery.extend({}, settings, {windowId: modalId})
				);

			if (settings.additionalParams != ' ' && settings.additionalParams != '')
			{
				path += '?' + settings.additionalParams;
			}

			$.loadingScreen('show');

			jQuery.ajax({
				context: jQuery('#' + settings.windowId),
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				abortOnRetry: 1,
				success: [function(returnedData) {
					$.loadingScreen('hide');

					/*var context = $(this),
						modalDiv = $('<div>').attr('id', modalId).html(returnedData.form_html);

					context.append(modalDiv);*/

					settings = jQuery.extend({
						title: returnedData.title,
						message: '<div id="' + modalId + '"><div id="id_message"></div>' + returnedData.form_html + '</div>',
						//windowId: modalId,
						width: '80%',
						error: returnedData.error
					}, settings);

					$.modalWindow(settings);

					/*modalDiv.HostCMSWindow({
						//autoOpen: true,
						//destroyOnClose: false,
						title: returnedData.title,
						//AppendTo: context,
						width: '80%',
						// // height: 140,
						//addContentPadding: true,
						//modal: false,
						//Maximize: false,
						//inimize: false
					});*/
				}]
			});

			return false;
		},
		adminLoad: function(settings) {
			// Call own event
			var triggerReturn = $('body').triggerHandler('beforeAdminLoad', [settings]);

			if (triggerReturn == 'break')
			{
				return false;
			}

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
			for (var jChekedItem, i = 0; i < iChekedItemsCount; i++)
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
			var jFiltersItems = jQuery("#" + settings.windowId + " :input[name^='admin_form_filter_']"),
				iFiltersItemsCount = jFiltersItems.length;

			for (var jFiltersItem, i = 0; i < iFiltersItemsCount; i++)
			{
				jFiltersItem = jFiltersItems.eq(i);

				// Если значение фильтра до 255 символов
				if (jFiltersItem.val().length < 256)
				{
					// Дописываем к передаваемым данным
					data[jFiltersItem.attr('name')] = jFiltersItem.val();
				}
			}

			// Расширенные фильтры
			var filterId = $('.topFilter').is(':visible')
				? $('#filterTabs .active').data('filter-id')
				: null;

			data['hostcms[filterId]'] = filterId;

			var jTopFiltersItems = jQuery("#"+settings.windowId+" #filter-" + filterId + " :input[name^='topFilter_']"),
				iTopFiltersItemsCount = jTopFiltersItems.length;

			for (var jFiltersItem, i=0; i < iTopFiltersItemsCount; i++)
			{
				jFiltersItem = jTopFiltersItems.eq(i);

				// Если значение фильтра до 255 символов
				if ((jFiltersItem.val() || '').length < 256)
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

			// Очистим поле для сообщений
			jQuery("#" + settings.windowId + " #id_message").empty();

			$.loadingScreen('show');

			jQuery.ajax({
				context: jQuery('#'+settings.windowId),
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				abortOnRetry: 1,
				success: [jQuery.ajaxCallback, jQuery.ajaxCallbackSkin, function(returnedData)
				{
					var pjax = window.history && window.history.pushState && window.history.replaceState /*&& !navigator.userAgent.match(/(WebApps\/.+CFNetwork)/)*/;

					/*if (settings.windowId == 'id_content'){*/
					if (pjax && settings.windowId == 'id_content')
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

					// Call own event
					$("#" + settings.windowId).trigger('adminLoadSuccess');
					//}
				}]
			});

			return false;
		},
		adminSendForm: function(settings) {
			// Call own event
			var triggerReturn = $('body').triggerHandler('beforeAdminSendForm', [settings]);

			if (triggerReturn == 'break')
			{
				return false;
			}

			settings = jQuery.requestSettings(settings);

			settings = jQuery.extend({
				buttonObject: ''
			}, settings);

			// Сохраним из визуальных редакторов данные
			if (typeof tinyMCE != 'undefined')
			{
				tinyMCE.triggerSave();
			}

			// CodeMirror
			jQuery("#"+settings.windowId+" .CodeMirror").each(function(){
				this.CodeMirror.save();
			});

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

			if (settings.view != '')
			{
				data['hostcms[view]'] = settings.view;
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
					// if (tinyMCE.getInstanceById(elementId) != null)
					if (tinyMCE.get(elementId) != null)
					{
						// console.log('mceRemoveControl');
						tinyMCE.remove('#' + elementId);
						//tinyMCE.execCommand('mceRemoveControl', false, elementId);
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
			var triggerReturn = $('body').triggerHandler('beforeAjaxCallback', [data]);

			if (triggerReturn == 'break')
			{
				$.loadingScreen('hide');
				return false;
			}

			$.loadingScreen('hide');
			if (data == null)
			{
				alert('AJAX response error.');
				return;
			}

			var jObject = jQuery(this);

			if (data.form_html !== null && data.form_html.length)
			{
				jQuery.beforeContentLoad(jObject, data);
				jQuery.insertContent(jObject, data.form_html);
				jQuery.afterContentLoad(jObject, data);
			}

			if (data.error != '')
			{
				var jMessage = jObject.find('#id_message');

				/*if (jMessage.length === 0)
				{
					jMessage = jQuery('<div>').attr('id', 'id_message');
					jObject.prepend(jMessage);
				}*/

				jMessage.empty().html(data.error);
			}

			if (typeof data.title != 'undefined' && !isEmpty(data.title) && jObject.attr('id') == 'id_content')
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

			if (settings.loadingScreen) { $.loadingScreen('show'); }

			var data = jQuery.getData(settings);
			data['hostcms[checked][' + settings.datasetId + '][' + settings.objectId + ']'] = 1;

			if (typeof settings.additionalData != 'undefined')
			{
				$.each(settings.additionalData, function(index, value){
					data[index] = value;
				})
			}

			var ajaxOptions = {
				context: jQuery('#' + settings.windowId + ' #' + settings.context),
				url: path,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: settings.callBack,
				abortOnRetry: 1
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
		loadDocumentText: function(data, status, jqXHR)
		{
			var jWindow = jQuery(this),
				tinyTextarea = $("textarea[name='document_text']", jWindow);

			$.loadingScreen('hide');

			if ('template_id' in data)
			{
				tinyTextarea.val(data['text']);

				$("select#template_id", jWindow).val(data['template_id']);

				if (typeof tinyMCE != 'undefined')
				{
					var elementId = tinyTextarea.attr('id'),
						editor = tinyMCE.get(elementId);

					if (editor != null)
					{
						/*var settings = editor.settings;
						settings['content_css'] = "...";
						tinyMCE.remove('#' + elementId);
						tinyTextarea.tinymce(settings);*/

						$.each(data['css'], function( index, value ) {
							editor.dom.loadCSS(value);
						});
					}
				}
			}
		},
		loadSelectOptionsCallback: function(data, status, jqXHR)
		{
			$.loadingScreen('hide');

			var jTopParentDiv = jQuery(this).parents('[id ^= property]'),
				jInput = jTopParentDiv.find('[id ^= input_]'),
				jSelectTopParentDiv = jQuery(this).parents('div[class ^= form-group]'),
				jInputTopParentDiv = jInput.parents('div[class ^= form-group]');

			if ('mode' in data)
			{
				if (data['mode'] == 'select')
				{
					jInputTopParentDiv.addClass('hidden');
					jSelectTopParentDiv.removeClass('hidden');

					jQuery(this).empty();
					for (var key in data['values'])
					{
						if (typeof data['values'][key] == 'object')
						{
							jQuery(this)
								.append(jQuery('<option>')
								.attr('value', data['values'][key].value)
								.text(data['values'][key].name));
						}
						else
						{
							jQuery(this)
								.append(jQuery('<option>')
								.attr('value', key)
								.text(data['values'][key]));
						}
					}
				}
				else if(data['mode'] == 'input')
				{
					jSelectTopParentDiv.addClass('hidden');
					jInputTopParentDiv.removeClass('hidden');
				}
			}
			else
			{
				jQuery(this).empty();
				for (var key in data)
				{
					if (typeof data[key] == 'object')
					{
						jQuery(this)
							.append(jQuery('<option>')
							.attr('value', data[key].value)
							.text(data[key].name));
					}
					else
					{
						jQuery(this).append(jQuery('<option>').attr('value', key).text(data[key]));
					}
				}
			}
		},
		loadDivContentAjaxCallback: function(data, status, jqXHR)
		{
			$.loadingScreen('hide');
			jQuery(this).empty().html(data);
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

			jQuery("#" + windowId + " .admin_table_filter select.select2-hidden-accessible").html('').select2({data: [{id: '', text: ''}]}).select2();

			jQuery("#" + windowId + " .search-field input[name = globalSearch]").val('');
		},
		clearTopFilter: function(windowId)
		{
			jQuery("#" + windowId + " .topFilter input").val('');
			jQuery("#" + windowId + " .topFilter select").prop('selectedIndex', 0);

			jQuery("#" + windowId + " .topFilter select.select2-hidden-accessible").val(null).trigger("change");
		},
		setCheckbox: function(windowId, checkboxId)
		{
			jQuery("#"+windowId+" input[type='checkbox'][id='"+checkboxId+"']").attr('checked', true);
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

			jNewObject.insertAfter(jSpecialPrice);
		},
		deleteNewSpecialprice: function(object)
		{
			var jObject = jQuery(object).closest('.spec_prices').remove();
		},
		cloneDeliveryOption: function(windowId, cloneDelete)
		{
			var jDeliveryOption = jQuery(cloneDelete).closest('.delivery_options'),
			jNewObject = jDeliveryOption.clone();

			// Change input name
			jNewObject.find(':regex(name, ^\\S+_\\d+$)').each(function(index, object){
				var reg = /^(\S+)_(\d+)$/;
				var arr = reg.exec(object.name);
				jQuery(object).prop('name', arr[1] + '_' + '[]');
			});
			jNewObject.find("input,select").val('');

			jNewObject.insertAfter(jDeliveryOption);
		},
		deleteNewDeliveryOption: function(object)
		{
			var jObject = jQuery(object).closest('.delivery_options').remove();
		},
		cloneMultipleValue: function(windowId, cloneDelete)
		{
			var jMultipleValue = jQuery(cloneDelete).closest('.multiple_value'),
			jNewObject = jMultipleValue.clone();

			// Change input name
			jNewObject.find(':regex(name, ^\\S+_\\d+$)').each(function(index, object){
				var reg = /^(\S+)_(\d+)$/;
				var arr = reg.exec(object.name);
				jQuery(object).prop('name', arr[1] + '_' + '[]');
			});
			jNewObject.find("input,select").val('');

			jNewObject.insertAfter(jMultipleValue);
		},
		deleteNewMultipleValue: function(object)
		{
			var jObject = jQuery(object).closest('.multiple_value').remove();
		},
		companyChangeFilterFieldWindowId: function(newFilterFieldWindowId)
		{
			if (newFilterFieldWindowId)
			{
				$('input[id ^= \"filter_field_id_\"]').each( function() {
					var onKeyupText = $(this).attr('onkeyup'),
						pos = onKeyupText.indexOf('oSelectFilter') + 'oSelectFilter'.length,
						suffix = onKeyupText.substr(pos, 1),
						index = 'oSelectFilter' + suffix;

						if (window[index])
						{
							window[index].windowId = newFilterFieldWindowId;
						}
					}
				)
			}
		},
		showWindow: function(windowId, content, settings)
		{
			settings = jQuery.extend({
				/*modal: true, */autoOpen: true, addContentPadding: false, resizable: true, draggable: true, Minimize: false, Closable: true
			}, settings);

			var jWin = jQuery('#' + windowId);

			if (!jWin.length)
			{
				jWin = jQuery('<div>')
					.addClass('hostcmsWindow')
					.attr('id', windowId)
					//.appendTo(jQuery(document))
					.html(content)
					.HostCMSWindow(settings)/*
					.HostCMSWindow('open')*/;
			}
			return jWin;
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
		},
		// Установка cookies
		// name - имя параметра
		// value - значение параметра
		// expires - время жизни куки в секундах
		// path - путь куки
		// domain - домен
		setCookie: function(name, value, expires, path, domain, secure)
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
	});

	$.fn.extend({
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
		selectPersonCompany: function(settings)
		{
			settings = $.extend({
				url: '/admin/siteuser/index.php?loadSiteusers&types[]=siteuser&types[]=person&types[]=company',
				allowClear: true,
				templateResult: $.templateResultItemSiteusers,
				escapeMarkup: function(m) { return m; },
				templateSelection: $.templateSelectionItemSiteusers,
				width: "100%",
				dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : null
			}, settings);

			settings = $.extend({
				ajax: {
					url: settings.url,
					dataType: "json",
					type: "GET",
					processResults: function (data) {
						var aResults = [];
						$.each(data, function (index, item) {
							aResults.push(item);
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
		selectUser: function(settings)
		{
			settings = $.extend({
				allowClear: true,
				templateResult: $.templateResultItemResponsibleEmployees,
				escapeMarkup: function(m) { return m; },
				templateSelection: $.templateSelectionItemResponsibleEmployees,
				width: "100%"
			}, settings);

			return this.each(function(){
				jQuery(this).select2(settings);
			});
		},
		selectSiteuser: function(settings)
		{
			settings = $.extend({
				minimumInputLength: 1,
				allowClear: true,
				ajax: {
					url: "/admin/siteuser/index.php?loadSiteusers&types[]=siteuser",
					dataType: "json",
					type: "GET",
					processResults: function (data) {
						var aResults = [];
						$.each(data, function (index, item) {
							aResults.push(item);
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
		autocompleteShopItem: function(options, selectOption)
		{
			return this.each(function(){
				 jQuery(this).autocomplete({
					  source: function(request, response) {
						$.ajax({
						  url: '/admin/shop/index.php?autocomplete&' + $.param(options),
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
							var color = 'default';

							if (item.count > 0)
							{
								color = 'palegreen';
							}
							else if (item.count < 0)
							{
								color = 'darkorange';
							}

							return $('<li class="autocomplete-suggestion"></li>')
								.data('item.autocomplete', item)
								.append($('<div class="image"><img class="backend-thumbnail" src="' + item.image_small + '"></div>'))
								.append($('<div class="name"><a>' + $.escapeHtml(item.label) + '</a></div>'))
								.append($('<div class="count"><span class="label label-' + color + ' white">' + item.count + '</span></div>'))
								.append($('<div class="price">').text(item.price_with_tax + ' ' + item.currency))
								.append($('<div class="marking">').text(item.marking))
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
		},
		toggleDisabled: function()
		{
			return this.each(function(){
				this.disabled = !this.disabled;
			});
		},
		editable: function(settings){
			settings = jQuery.extend({
				save: function(item, settings){

					var data = jQuery.getData(settings), reg = /apply_check_(\d+)_(\S+)_fv_(\d+)/,
					itemId = item.prop('id'), arr = reg.exec(itemId);

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
						item.text(input.val()).css('display', '');
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
							item.css('display', '');
							input.remove();
						}
					}).width('90%').prop('name', item.parent().prop('id'))
					.insertAfter(item).focus().val(item.text());
				});
			});
		},
		clearSelect: function()
		{
			return this.each(function(index, object){
				jQuery(object).empty().append(jQuery('<option>').attr('value', 0).text(' ... '));
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
		var startPop = !popstate && baseURL.split("#")[0] == location.href.split("#")[0];
		popstate = true;
		if (startPop){
			return;
		}

		var state = event.state;
		if (state && state.windowId/* && state.windowId == 'id_content'*/) {
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
		else {
			popstate = false;
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

})(jQuery);

$(function(){


	//$.notificationsPrepare();
	//$.eventsPrepare();
	$(window).on('resize', function(event) {

		// Если ширина окна менее 570px, скрываем чекбоксы с настройками фиксации элеметов системы
		// и показываем пиктограммы, появляющиеся в верхней части окна по умолчанию
		if ($(this).innerWidth() < 570)
		{
			$('.navbar .navbar-inner .navbar-header .navbar-account .account-area').parent('.navbar-account.setting-open').removeClass('setting-open');
		}
	});

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
		})
		.on(
			{
				'click': function(event) {

					$(this).focus();

					// Действие, доступ к которому изменяем, недоступно для сотрудника или авторизованный сотрудник не может менять доступ к действию.
					if ($(this).hasClass('blocked') || $(this).parent('.not-changeable').length)
					{
						return false;
					}

					var iconPermissionId = $(this).attr('id'), //department_5_2_3 или user_7_2_3
						aPermissionProperties = iconPermissionId.split('_'),
						objectTypePermission = aPermissionProperties[0] == 'department' ? 0 : 1,
						objectIdPermission = aPermissionProperties[1], // идентификатор объекта (отдел или сотрудник), к которому применяются права
						dealTemplateStepId = aPermissionProperties[2], // получаем идентификатор этапа сделки
						actionType = aPermissionProperties[3], // тип действия (0 - создание, 1 - редактирование, 2 - просмотр, 3 - удаление)
						sUrlParams = document.location.search,
						dealTemplateId;

					// Не обрабатываем изменение прав доступа для отделов
					if (!objectTypePermission)
					{
						return false;
					}

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

					$.adminLoad({path: '/admin/deal/template/step/index.php', action: 'changeAccess', operation: '', additionalParams: 'deal_template_id=' + dealTemplateId + '&objectType=' + objectTypePermission + '&objectId=' + objectIdPermission + '&actionType=' + actionType + '&hostcms[checked][0][' + dealTemplateStepId + ']=1', windowId: 'id_content'});
				},

				'mousedown': function() {

					$(this).removeClass('changed');
				},

				'mouseover': function() {

					if ($(this).hasClass('changed'))
					{
						$(this).toggleClass('fa-circle-o fa-circle');
					}
				},
				'mouseout': function() {

					$(this).removeClass('changed');
				},
				/* 'focus': function() {

					console.log('focused');

					$(this).addClass('focused');
				},

				'blur': function() {

					console.log('blur');
					$(this).removeClass('focused');
				} */

			},
			'.icons_permissions i'
		)
		.on('click', '.workday #workdayControl > span:not(.user-workday-end-text)', function(e) {
			e.stopPropagation();

			var object = $(this),
				buttonClassName = object.attr('class'),
				status = 0;

			if (object.hasClass('user-workday-start') || object.hasClass('user-workday-continue'))
			{
				status = 2;
			}
			else if (object.hasClass('user-workday-pause'))
			{
				status = 3;
			}
			else if (object.hasClass('user-workday-stop'))
			{
				if (confirm($(this).data('confirm')))
				{
					status = 4;
				}
			}
			else if (object.hasClass('user-workday-stop-another-time'))
			{
				$.modalLoad({title: $(this).data('title'), path: '/admin/user/index.php', additionalParams: 'showAnotherTimeModalForm', width: '50%', windowId: 'id_content', onHide: function(){$(".wickedpicker").remove();}});

				return true;
			}

			/*switch(buttonClassName)
			{
				// Начинаем рабочий день
				case 'user-workday-start':
				case 'user-workday-continue':
					status = 2;
				break;
				// Перерыв
				case 'user-workday-pause':
					status = 3;
				break;
				// Завершаем рабочий день
				case 'user-workday-stop':
					if (confirm($(this).data('confirm')))
					{
						status = 4;
					}
				break;
				// Показ формы запроса на завершение рабочего дня с другим временем
				case 'user-workday-stop-another-time':
					$.modalLoad({title: $(this).data('title'), path: '/admin/user/index.php', additionalParams: 'showAnotherTimeModalForm', width: '50%', windowId: 'id_content', onHide: function(){$(".wickedpicker").remove();}});

					return true;
				break;
			}*/

			$.changeUserWorkdayButtons(status);
		})
		// Перевод сделки на новый этап
		.on("click", "#deal-steps .steps li", function() {
			var $this = $(this),
				dealTemplateStepId = parseInt($this.attr("id").split("simplewizardstep")[1]) || 0,
				dealTemplateSteps = $this.parent(".steps"),
				currentDealTemplateStepId = parseInt(dealTemplateSteps.data("template-step-id"));

			if (dealTemplateStepId && dealTemplateStepId != currentDealTemplateStepId
				&& $this.hasClass("available"))
			{
				// Создание сделки
				if (!dealTemplateSteps.data("dealId"))
				{
					$this.toggleClass("active available");

					dealTemplateSteps
						.find("li#simplewizardstep" + currentDealTemplateStepId)
						.toggleClass("active available");

					dealTemplateSteps.data("template-step-id", dealTemplateStepId);
				}
				else // Редактирование сделки
				{
					// Нажали на шаг уже отмеченный как "следующий",
					// снимаем отметку для перехода
					if ($this.hasClass("next"))
					{
						$(".deal-template-step-comment")
							.parent()
							.addClass("hidden");

						$this.removeClass("next");
						$(".deal-template-step-name").html('');

						dealTemplateStepId = dealTemplateSteps.data("template-step-id");
					}
					else
					{
						$(".deal-template-step-comment")
							.parent()
							.removeClass("hidden");

					 	$(".next", dealTemplateSteps).removeClass("next");
						$this.addClass("next");

						currentStepLi = $("li#simplewizardstep" + currentDealTemplateStepId, dealTemplateSteps);
						currentStepName = $.escapeHtml($("span.title", currentStepLi).text());
						currentStepColor = $('span.step', currentStepLi).css('color');

						newStepName = $.escapeHtml($("span.title", $this).text());
						newStepColor = $('span.step', $this).css('color');

						$(".deal-template-step-name").html('<span class="badge current-step" style="background-color:' + currentStepColor + '">' + currentStepName + '</span><span class="darkgray"> → </span><span class="badge new-step" style="background-color:' + newStepColor + '">' + newStepName + '</span>');
					}

					// Сотрудник не принял сделку или отказался от ее выполнения
					if (!$('.join-user a').hasClass('btn-darkorange')
						&& !$('.join-user a').hasClass('btn-default')
					)
					{
						// stepColor = $('li#simplewizardstep' + dealTemplateSteps.data("template-step-id") + ' span.step', dealTemplateSteps).css('color');
						stepColor = $('li#simplewizardstep' + dealTemplateStepId + ' span.step', dealTemplateSteps).css('color');

						var $joinUserA = $('.join-user a'),
							dealId = $joinUserA.data('deal-id');

						var onclick = !$this.hasClass('next')
							? '{deal_step_id: ' + parseInt(dealTemplateSteps.data("step-id")) + '}'
							: '{deal_id: ' + dealId + ', deal_template_step_id: ' + dealTemplateStepId + '}';

						$joinUserA
							.attr('onclick', '$.joinUser2DealStep(' + onclick + ')')
							.css({'color': '#fff', 'background-color': stepColor, 'border-color': stepColor});
					}
				}

				$("[name='deal_template_step_id']").val(dealTemplateStepId);
			}
		});

	// Sticky actions
	$(document).on("scroll", function () {
		// to bottom
		if ($(window).scrollTop() + $(window).height() == $(document).height()) {
			$('.formButtons').removeClass('sticky-actions');
		}

		// to top
		if ($(window).scrollTop() + $(window).height() < $(document).height()) {
			$('.formButtons').addClass('sticky-actions');
		}
	});

	// For TinyMCE init
	$('body').on('afterTinyMceInit', function(event, editor) {
		editor.on('change', function() { mainFormLocker.lock() });
	});
});

//fix modal force focus
/*$.fn.modal.Constructor.prototype.enforceFocus = function () {
  var that = this;
  $(document).on('focusin.modal', function (e) {
	 if ($(e.target).hasClass('select2-input')) {
		return true;
	 }

	 if (that.$element[0] !== e.target && !that.$element.has(e.target).length) {
		that.$element.focus();
	 }
  });
};*/

// Lazy image load
document.addEventListener("DOMContentLoaded", function() {
	var lazyloadThrottleTimeout;

	function lazyload()
	{
		if(lazyloadThrottleTimeout)
		{
			clearTimeout(lazyloadThrottleTimeout);
		}

		lazyloadThrottleTimeout = setTimeout(function() {
			var scrollTop = window.pageYOffset,
				lazyloadImages = document.querySelectorAll("img.lazy");

			lazyloadImages.forEach(function(img) {
				// if(img.offsetTop < (window.innerHeight + scrollTop))
				if(img.getBoundingClientRect().top < (window.innerHeight + scrollTop))
				{
					img.src = img.dataset.src;
					img.classList.remove('lazy');
				}
			});

			/*if(lazyloadImages.length == 0)
			{
			  document.removeEventListener("scroll", lazyload);
			  window.removeEventListener("resize", lazyload);
			  window.removeEventListener("orientationChange", lazyload);
			}*/
		}, 200);
	}

	document.addEventListener("scroll", lazyload);
	window.addEventListener("resize", lazyload);
	window.addEventListener("orientationChange", lazyload);

	$('#id_content').on('adminLoadSuccess', lazyload);
	$(document).on("shown.bs.modal", lazyload);

	lazyload();
}, false);

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

	if (event.description)
	{
		element.find('.fc-content')
			.append('<span class="fc-description">' + $.escapeHtml(event.description) + '</span>');
	}

	if (event.place)
	{
		element.find('.fc-content')
			.append('<span class="fc-place"><i class="fa fa-map-marker black"></i> ' + $.escapeHtml(event.place) + '</span>');
	}

	if (event.amount)
	{
		element.find('.fc-content')
			.append('<span class="fc-amount semi-bold">' + $.escapeHtml(event.amount) + '</span>');
	}

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
					Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'success', 'fa-check', true, true)

					$('#calendar').fullCalendar( 'refetchEvents' );
				}
				else if (result['message']) // Ошибка, отменяем действие
				{
					result['error'] && revertFunc();
					Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'danger', 'fa-warning', true, true)
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
				Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'success', 'fa-check', true, true)
			}
			else if (result['message']) // Ошибка, отменяем действие
			{
				result['error'] && revertFunc();
				Notify('<span>' + $.escapeHtml(result['message']) + '</span>', '', 'top-right', '7000', 'danger', 'fa-warning', true, true)
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

function formLocker()
{
	this._locked = false;
	this._previousLocked = false;
	this._delay = false;
	this._enabled = true;

	this.lock = function(event) {
		if (!this._delay && this._enabled)
		{
			var keycode = typeof event !== 'undefined' && event.originalEvent instanceof KeyboardEvent && (event.keyCode || event.which),
			aKeycodes = [13, 16, 17, 18, 19, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145];

			if (!this._locked && $.inArray(keycode, aKeycodes) == -1)
			{
				$('body').on('beforeAdminLoad beforeAjaxCallback', $.proxy(this._confirm, this));

				$('h5.row-title').append('<i class="fa fa-lock edit-lock"></i>');

				this._locked = true;
			}
		}

		return this;
	}

	this._confirm = function() {
		if (!confirm(i18n['lock_message']))
		{
			return 'break';
		}
		this.unlock();
	}

	this.unlock = function() {
		this._locked = false;

		$('body')
			.unbind('beforeAdminLoad')
			.unbind('beforeAjaxCallback');

		$('h5.row-title > i.edit-lock').remove();

		if (!this._delay)
		{
			this._delay = true;
			setTimeout($.proxy(this._resetDelay, this), 3000);
		}

		return this;
	}

	this._resetDelay = function() {
		this._delay = false;

		return this;
	}

	this.saveStatus = function() {
		this._previousLocked = this._locked;
		return this;
	}

	this.restoreStatus = function() {
		this._previousLocked ? this.lock() : this.unlock();
		this._previousLocked = false;
		return this;
	}

	this.enable = function() {
		this._enabled = true;
		return this;
	}

	this.disable = function() {
		this._enabled = false;
		return this;
	}
}
mainFormLocker = new formLocker();

// -------------
var loadedMultiContent = [];
$.getMultiContent = function(arr, path) {
	function loadSctriptContent(url) {
		return $.ajax({
		  url: url,
		  dataType: "text",
		  success: function (data, textStatus, jqxhr) {
			loadedMultiContent.push(url);
		  }
		});
	}

    var _arr = $.map(arr, function(url) {
		url = (path || '') + url;
		if ($.inArray(url, loadedMultiContent) == -1)
		{
			//loadedMultiContent.push(url);
			if (url.indexOf('.css') != -1)
			{
				$('<link>', {rel: 'stylesheet', href: url}).appendTo('head');
			}
			else
			{
				return loadSctriptContent(url);
			}
		}

		// Already loaded, delete item from the array
		return null;
    });

    /*_arr.push($.Deferred(function(deferred) {
        $(deferred.resolve);
    }));*/

    return $.when.apply($, _arr).done(function() {
		if (arguments.length)
		{
			// when() with multiple deferred, 'arguments' is aggregate state of all the deferreds
			if (Array.isArray(arguments[0]))
			{
				for (var i=0; i < arguments.length; i++) {
					//contentType = arguments[i][2].getResponseHeader('Content-Type');
					//if (contentType.indexOf('javascript') != -1)
					//console.log(arguments[i]);
					$.globalEval(arguments[i][0]);
				}
			}
			else
			{
				$.globalEval(arguments[0]);
			}
		}
	});
}

function cSelectFilter(windowId, sObjectId)
{
	this.windowId = $.getWindowId(windowId);
	this.sObjectId = sObjectId.replace( /(:|\.|\[|\]|,)/g, "\\$1" );

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
		var icon = $("#" + this.windowId + " #filter_" + this.sObjectId).prev('span').find('i');

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

					self.oCurrentSelectObject.trigger('change');
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

function radiogroupOnChange(windowId, value, values)
{
	var values = values || [0, 1];

	for (var x in values) {
		if (value != values[x])
		{
			$("#"+windowId+" .hidden-"+values[x]).show();
			$("#"+windowId+" .shown-"+values[x]).hide();
		}
	}

	$("#"+windowId+" .hidden-"+value).hide();
	$("#"+windowId+" .shown-"+value).show();
}

// Empty arrays
var fieldsStatus = [];

// -- Проверка ячеек
function backendFieldCheck(event)
{
	var $this = $(this),
		$form = $this.parents('form'),
		value = $this.val(),
		fieldId = $this.attr('id'),
		message = '',
		minlength = $this.data('min'),
		maxlength = $this.data('max'),
		reg = $this.data('reg')
		equality = $this.data('equality');

	// Проверка на минимальную длину
	if (typeof minlength != 'undefined' && minlength && value.length < minlength)
	{
		message += i18n['Minimum'] + ' ' + minlength + ' '
			+ declension(minlength, i18n['one_letter'], i18n['some_letter2'], i18n['some_letter1']) + '. '
			+ i18n['current_length'] + ' ' + value.length + '. ';
	}

	// Проверка на максимальную длину
	if (typeof maxlength != 'undefined' && maxlength && value.length > maxlength)
	{
		message += i18n['Maximum'] + ' ' + maxlength + ' '
			+ declension(maxlength, i18n['one_letter'], i18n['some_letter2'], i18n['some_letter1']) + '. '
			+ i18n['current_length'] + ' ' + value.length + '. ';
	}

	// Проверка на регулярное выражение
	if (typeof reg != 'undefined' && reg.length && value.length)
	{
		var regEx = new RegExp(reg);

		if (!value.match(regEx))
		{
			var reg_message = $this.data('reg-message');

			message += typeof reg_message != 'undefined' && reg_message.length
				? reg_message
				: i18n['wrong_value_format'] + ' ';
		}
	}

	// Проверка на соответствие значений 2-х полей
	if (typeof equality != 'undefined' && equality.length)
	{
		// Пытаемся получить значение поля, которому должны соответствовать
		var $field2 = $form.find('#' + equality);

		if (value != $field2.val())
		{
			var equality_message = $this.data('equality-message');

			message += typeof equality_message != 'undefined' && equality_message.length
				? equality_message
				: i18n['different_fields_value'] + ' ';
		}
	}

	// Проверка на select
	var type = $this.get(0).tagName;

	if (typeof type != 'undefined' && type.toLowerCase() == 'select')
	{
		if (value <= 0)
		{
			message += 'value is empty';
		}
	}

	// Insert message into the message div
	$this.nextAll("#" + fieldId + '_error').html(message);

	// Устанавливаем флаг несоответствия
	var formId = $form.attr('id');

	if (typeof fieldsStatus[formId] == 'undefined')
	{
		fieldsStatus[formId] = [];
	}

	fieldsStatus[formId][fieldId] = (message.length > 0);

	if (fieldsStatus[formId][fieldId])
	{
		$this
			.css('border-style', 'solid')
			.css('border-width', '1px')
			.css('border-color', '#ff1861')
			.css('background-image', "url('/admin/images/bullet_red.gif')")
			.css('background-position', 'center right')
			.css('background-repeat', 'no-repeat');
	}
	else
	{
		$this
			.css('border-style', '')
			.css('border-width', '')
			.css('border-color', '')
			.css('background-image', "url('/admin/images/bullet_green.gif')")
			.css('background-position', 'center right')
			.css('background-repeat', 'no-repeat');
	}

	// Отображать контрольные элементы
	var disableButtons = false;

	for (itemIndex in fieldsStatus[formId])
	{
		// если есть хоть одно несоответствие - выключаем управляющие элементы
		if (fieldsStatus[formId][itemIndex])
		{
			disableButtons = true;
			break;
		}
	}

	$.toogleInputsActive($form, disableButtons);
	//$form.find('.formButtons input').attr('disabled', disableButtons);
}

function CheckAllField(windowId, formId)
{
	var windowId = $.getWindowId(windowId);
	$("#" + windowId + " #" + formId + " :input").each(function(){
		// FieldCheck(windowId, this);
		$(this).blur();
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
function HostCMSFileManager()
{
	//this.fileBrowserCallBack = function(field_name, url, type, win)
	this.fileBrowserCallBack = function(callback, value, meta)
	{
		this.field = value;
		//this.callerWindow = win;
		this.callback = callback;

		var url = this.field.split('\\').join('/');

		var type = meta.filetype,
			cdir = '',
			dir = '',
			lastPos = url.lastIndexOf('/');

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

		var path = "/admin/wysiwyg/filemanager/index.php?field_name=" + this.field + "&cdir=" + cdir + "&dir=" + dir + "&type=" + type, width = screen.width / 1.2, height = screen.height / 1.2;

		var x = parseInt(screen.width / 2.0) - (width / 2.0), y = parseInt(screen.height / 2.0) - (height / 2.0);

		this.win = window.open(path, "FM", "top=" + y + ",left=" + x + ",scrollbars=yes,width=" + width + ",height=" + height + ",resizable=yes");

		return false;
	}

	this.insertFile = function(url)
	{
		url = decodeURIComponent(url);
		url = url.replace(new RegExp(/\\/g), '/');

		/*var field = this.callerWindow.document.getElementById(this.field);

		field.value = url;
		//this.callerWindow.document.forms[0].elements[this.field].value = url;

		try {
			field.onchange();
		}
		catch (e){}*/

		this.callback(url);

		this.win.close();
	}
};

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
/* /jQuery Cookie plugin */
