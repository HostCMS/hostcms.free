/* global hostcmsBackend, mainFormLocker, i18n, bootbox, Notify, createCookie */

(function($) {
	"use strict";

	$.extend({
		// Добавление новой заметки
		addNote: function() {
			const data = $.getData({});

			$.ajax({
				url: hostcmsBackend + '/index.php?ajaxCreateNote',
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function(response) {
					$.createNote({ 'id': response.form_html });
				}
			});
		},

		// Создание заметки по id и value
		createNote: function(settings) {
			settings = $.extend({
				'id': null,
				'value': ''
			}, settings);

			const $clone = $('#default-user-note').clone();
			const noteId = settings.id;

			$clone
				.prop('id', noteId)
				.data('user-note-id', noteId);

			$clone.find('textarea').eq(0).val(settings.value);

			$("#user-notes").prepend($clone.show());

			// Используем debounce логику для сохранения
			$clone.on('change', function() {
				const $object = $(this);
				const timer = $object.data('timer');

				if (timer) {
					clearTimeout(timer);
				}

				$object.data('timer', setTimeout(function() {
					const $textarea = $object.find('textarea').addClass('ajax');
					const data = $.getData({});

					data.value = $textarea.val();

					$.ajax({
						context: $textarea,
						url: `${hostcmsBackend}/index.php?ajaxNote&action=save&entity_id=${noteId}`,
						type: 'POST',
						data: data,
						dataType: 'json',
						success: function() {
							this.removeClass('ajax');
						}
					});
				}, 1000));
			});
		},

		// Удаление заметки
		destroyNote: function($div) {
			$.ajax({
				url: `${hostcmsBackend}/index.php?ajaxNote&action=delete&entity_id=${$div.data('user-note-id')}`,
				type: 'GET',
				dataType: 'json'
			});

			$div.remove();
		},

		selectMediaFile: function(object, windowId, modalWindowId) {
			const $object = $(object);
			const dataParams = {
				'add_media_file': 1,
				'id': $object.data('id'),
				'type': $object.data('type'),
				'entity_id': $object.data('entity-id')
			};

			$.ajax({
				url: hostcmsBackend + '/media/index.php',
				type: "POST",
				data: dataParams,
				dataType: 'json',
				success: function() {
					$('#' + modalWindowId).parents('.modal').modal('hide');
					mainFormLocker.unlock();

					$.adminLoad({
						path: hostcmsBackend + '/media/index.php',
						additionalParams: `entity_id=${dataParams.entity_id}&type=${dataParams.type}&parentWindowId=${windowId}&_module=0`,
						windowId: windowId,
						loadingScreen: false
					});
				}
			});
		},

		removeMediaFile: function(id, entity_id, type, windowId) {
			$.ajax({
				url: hostcmsBackend + '/media/index.php',
				type: "POST",
				data: { 'remove_media_file': 1, 'id': id, 'type': type, 'entity_id': entity_id },
				dataType: 'json',
				success: function() {
					if (confirm(i18n['confirm_delete'])) {
						mainFormLocker.unlock();
						$.adminLoad({
							path: hostcmsBackend + '/media/index.php',
							additionalParams: `entity_id=${entity_id}&type=${type}&parentWindowId=${windowId}&_module=0`,
							windowId: windowId,
							loadingScreen: false
						});
					}
				}
			});
		},

		refreshMediaSorting: function(windowId, modelName) {
			// Оптимизированный сбор данных
			const inputs = $(`#${windowId} input[name^='media_']`).map(function() {
				return {
					name: this.name,
					value: this.value
				};
			}).get();

			$.ajax({
				url: hostcmsBackend + '/media/index.php',
				type: "POST",
				data: {
					'refresh_sorting_media_file': 1,
					'modelName': modelName,
					'inputs': inputs
				},
				dataType: 'json'
			});
		},

		showCropButton: function(object, id, windowId) {
			const file = object[0].files[0];
			const availableExtensions = ["jpg", "jpeg", "png", "gif", "webp"];

			if (file) {
				const fileName = file.name;
				const extension = fileName.slice(fileName.lastIndexOf(".") + 1).toLowerCase();

				if (availableExtensions.includes(extension)) {
					$(`#${windowId} #crop_${id}`).removeClass("hidden").addClass("input-group-addon control-item");
				}
			}
		},

		showCropModal: function(id, imagePath, imageName) {
			const $input = $('input#' + id);
			const $parent = $input.parents('.input-group');
			const file = $input[0].files[0];

			if (file) {
				imageName = file.name;

				if (window.URL) {
					imagePath = URL.createObjectURL(file);
				} else if (FileReader) {
					const reader = new FileReader();
					reader.onload = function(e) {
						imagePath = e.target.result;
					};
					reader.readAsDataURL(file);
				}
			}

			// Использование Template Literals для чистоты кода
			const modalHtml = `
				<div class="modal fade crop-image-modal" id="modal_${id}" tabindex="-1" role="dialog" aria-labelledby="${id}ModalLabel">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title">${i18n['change_image']}</h4>
							</div>
							<div class="modal-body">
								<div class="img-container"><img class="img-responsive center-block" id="img_${id}" src="${$.escapeHtml(imagePath)}" /></div>
							</div>
							<div class="modal-footer">
								<div class="row">
									<div class="col-md-9 docs-buttons">
										<div class="btn-group">
											<button type="button" class="btn btn-primary" data-method="zoom" data-option="0.1" title="Zoom In"><span class="fa fa-search-plus"></span></button>
											<button type="button" class="btn btn-primary" data-method="zoom" data-option="-0.1" title="Zoom Out"><span class="fa fa-search-minus"></span></button>
										</div>
										<div class="btn-group">
											<button type="button" class="btn btn-warning" data-method="rotate" data-option="-90" title="Rotate Left"><span class="fa fa-rotate-left"></span></button>
											<button type="button" class="btn btn-warning" data-method="rotate" data-option="90" title="Rotate Right"><span class="fa fa-rotate-right"></span></button>
										</div>
										<div class="btn-group">
											<button type="button" class="btn btn-palegreen" data-method="scaleX" data-option="-1" title="Flip Horizontal"><span class="fa fa-arrows-h"></span></button>
											<button type="button" class="btn btn-palegreen" data-method="scaleY" data-option="-1" title="Flip Vertical"><span class="fa fa-arrows-v"></span></button>
										</div>
										<div class="btn-group margin-left-20">
											<span id="dataWidth${id}">0</span> &times; <span id="dataHeight${id}">0</span>
										</div>
									</div>
									<div class="col-md-3 text-align-right">
										<button type="button" class="btn btn-success crop-${id}">${i18n['save']}</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>`;

			$parent.append(modalHtml);

			const $image = $('#img_' + id);
			const $modal = $('#modal_' + id);
			const $dataHeight = $('#dataHeight' + id);
			const $dataWidth = $('#dataWidth' + id);

			const options = {
				autoCrop: false,
				aspectRatio: NaN,
				viewMode: 0,
				crop: function(e) {
					$dataHeight.text(Math.round(e.detail.height));
					$dataWidth.text(Math.round(e.detail.width));
				},
				ready: function() {
					const containerData = $(this).cropper('getContainerData');
					const imageData = $(this).cropper('getImageData');

					if (imageData.naturalWidth < containerData.width && imageData.naturalHeight < containerData.height) {
						$(this).data('cropper').zoomTo(1);
					}
				}
			};

			// Event Delegation для кнопок
			$modal.find('.docs-buttons').on('click', '[data-method]', function() {
				const $this = $(this);
				let data = $this.data();
				const cropper = $image.data('cropper');

				if ($this.prop('disabled') || $this.hasClass('disabled')) return;

				if (cropper && data.method) {
					data = $.extend({}, data);
					let $target;

					if (typeof data.target !== 'undefined') {
						$target = $(data.target);
						if (typeof data.option === 'undefined') {
							try {
								data.option = JSON.parse($target.val());
							} catch (e) {
								console.error(e.message);
							}
						}
					}

					const cropped = cropper.cropped;

					if (data.method === 'rotate' && cropped && options.viewMode > 0) {
						$image.cropper('clear');
					}

					const result = $image.cropper(data.method, data.option, data.secondOption);

					if (data.method === 'rotate' && cropped && options.viewMode > 0) {
						$image.cropper('crop');
					} else if (data.method === 'scaleX' || data.method === 'scaleY') {
						$(this).data('option', -data.option);
					}

					if ($.isPlainObject(result) && $target) {
						try {
							$target.val(JSON.stringify(result));
						} catch (e) {
							console.error(e.message);
						}
					}
				}
			});

			$modal.modal('show');

			$modal.on('shown.bs.modal', function() {
				$image.cropper(options);
			}).on('hidden.bs.modal', function() {
				$image.cropper('destroy');
				$modal.remove();
			});

			$modal.find('.crop-' + id).on('click', function() {
				const cropper = $image.data('cropper');
				if (cropper) {
					cropper.getCroppedCanvas().toBlob(function(blob) {
						const dT = new ClipboardEvent('').clipboardData || new DataTransfer();
						dT.items.add(new File([blob], imageName));
						$input[0].files = dT.files;
					});
				}
				$modal.modal('hide');
			});
		},

		sqlRenameTab: function(event) {
			if (event.type === "touchend") {
				const now = Date.now();
				const timeSince = now - ($(this).data('latestTap') || 0);

				$(this).data('latestTap', now);

				if (!timeSince || timeSince > 600) return;
			}

			const $item = $(this);
			const $ae = $('<a>');
			const $editor = $('<input>').prop('type', 'text').width('95%');

			$item.css('display', 'none');

			$editor.on('blur', function() {
				const $t = $(this);
				const item = $t.parent().prev();

				item.html($.escapeHtml($t.val()) + '<i class="fa-solid fa-xmark" onclick="$.sqlDeleteTab(this);"></i>').css('display', '');
				$t.parent().remove();

				const settings = { path: hostcmsBackend + '/sql/index.php', windowId: '#id_content' };
				const data = $.getData(settings);

				data['hostcms[action]'] = 'rename';
				data.tabid = item.attr('href').split('_')[1];
				data.name = item.text().trim();

				$.ajax({
					url: settings.path,
					type: 'POST',
					data: data,
					dataType: 'json'
				});
			}).on('keydown', function(e) {
				if (e.keyCode === 13) { // Enter
					e.preventDefault();
					this.blur();
				} else if (e.keyCode === 27) { // ESC
					e.preventDefault();
					const item = $(this).parent().prev();
					item.css('display', '');
					$(this).parent().remove();
				}
			});

			$ae.append($editor);
			$ae.insertAfter($item);
			$editor.focus().val($item.text());
		},

		sqlDeleteTab: function(object) {
			const $t = (typeof object.data !== 'undefined' && typeof object.data.elm !== 'undefined')
				? $(object.data.elm)
				: $(object);
			const $a = $t.parents('a');

			if (confirm(i18n['confirm_delete'])) {
				const id = $a.attr('href').split('_')[1];
				$.adminLoad({
					path: hostcmsBackend + '/sql/index.php',
					action: 'delete',
					additionalParams: 'tabid=' + id,
					windowId: 'id_content'
				});
			}
			return false;
		},

		toggleFilter: function() {
			const $button = $('#showTopFilterButton');

			if ($button.length)
			{
				$('.topFilter').toggle();
				$('tr.admin_table_filter').toggleClass('disabled');
				$button.toggleClass('active');
			}
		},

		changeFilterStatus: function(settings) {
			$.ajax({
				url: settings.path,
				data: {
					'_': Date.now(),
					changeFilterStatus: true,
					show: settings.show
				},
				dataType: 'json',
				type: 'POST'
			});
		},

		changeFilterField: function(settings) {
			const $li = $(settings.context);
			$.filterToggleField($li);

			$.ajax({
				url: settings.path,
				data: {
					'_': Date.now(),
					changeFilterField: true,
					tab: settings.tab,
					field: settings.field,
					show: +$li.find('i').hasClass('fa-check')
				},
				dataType: 'json',
				type: 'POST'
			});
		},

		filterSaveAs: function(caption, object, additionalParams) {
			bootbox.prompt(caption, function(result) {
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
			const $form = object.closest('form');

			$form.ajaxSubmit({
				data: {
					saveFilter: true,
					filterId: $form.data('filter-id')
				},
				url: $form.attr('action'),
				type: 'POST',
				dataType: 'json',
				cache: false,
				success: function() {
					$.loadingScreen('hide');
				}
			});
		},

		filterDelete: function(object) {
			$.loadingScreen('show');
			const $form = object.closest('form');
			const filterId = $form.data('filter-id');

			$form.ajaxSubmit({
				data: {
					deleteFilter: true,
					filterId: filterId
				},
				url: $form.attr('action'),
				type: 'POST',
				dataType: 'json',
				cache: false,
				success: function() {
					$.loadingScreen('hide');
				}
			});

			$('#filter-li-' + filterId).prev().find('a').tab('show');
			$('#filter-' + filterId + ', #filter-li-' + filterId).remove();
		},

		filterToggleField: function(object) {
			const filterId = object.data('filter-field-id');
			const $filterFormGroup = $('#' + filterId);

			$filterFormGroup
				.toggle()
				.find("input,select,textarea").val('');

			object.find('i').toggleClass('fa-check');
		},

		clearFilter: function(windowId) {
			const $context = $(`#${windowId}`);

			$context.find(".admin_table_filter input[type!='checkbox']").val('');
			$context.find(".admin_table_filter input:checked").prop("checked", false);
			$context.find(".admin_table_filter select").prop('selectedIndex', 0);
			$context.find(".admin_table_filter select.select2-hidden-accessible").val(null).trigger('change');
			$context.find(".search-field input[name='globalSearch']").val('');
		},

		clearTopFilter: function(windowId) {
			const $context = $(`#${windowId}`);
			$context.find(".topFilter input[type!='checkbox']").val('');
			$context.find(".topFilter input:checked").prop("checked", false);
			$context.find(".topFilter select").prop('selectedIndex', 0);
			$context.find(".topFilter select.select2-hidden-accessible").val(null).trigger("change");
		},

		setCheckbox: function(windowId, checkboxId) {
			$(`#${windowId} input[type='checkbox'][id='${checkboxId}']`).prop('checked', true);
		},

		filterKeyDown: function(e) {
			if (e.keyCode === 13) {
				e.preventDefault();
				$(this).closest('table').find('#admin_forms_apply_button').click();
			}
		},

		blockIp: function(settings) {
			$.loadingScreen('show');

			settings = $.extend({
				block_ip: 1,
				path: hostcmsBackend + '/ipaddress/index.php',
			}, settings);

			$.ajax({
				context: this,
				url: settings.path,
				type: "POST",
				data: settings,
				dataType: 'json',
				success: function(answer) {
					$.loadingScreen('hide');

					if (answer.result === 'ok') {
						Notify('<span>' + i18n['ban_success'] + '</span>', '', 'top-right', '5000', 'success', 'fa-check', true);
					} else if (answer.result === 'error') {
						Notify('<span>' + i18n['ban_error'] + '</span>', '', 'top-right', '5000', 'danger', 'fa-ban', true);
					}
				}
			});
		},

		loadIpaddressFilterNestable: function() {
			$('.ipaddress-filter-conditions').sortable({
				connectWith: '.ipaddress-filter-conditions',
				items: '> .dd',
				scroll: false,
				placeholder: 'placeholder',
				tolerance: 'pointer',
				stop: function() {
					const $container = $('.ipaddress-filter-conditions');
					$container.find('input[type="hidden"]').remove();

					$container.find('li.dd-item').each(function(i) {
						$container.append(`<input type="hidden" name="ipaddress_filter_sorting${$(this).data('id')}" value="${i}"/>`);
					});
				}
			}).disableSelection();

			$('.ipaddress-filter-conditions').on('mousedown', '.dd-handle a, .dd-handle .property-data', function(e) {
				e.stopPropagation();
			});
		},

		addIpaddressFilterCondition: function() {
			$.ajax({
				url: hostcmsBackend + '/ipaddress/filter/index.php',
				data: { 'add_filter': 1 },
				dataType: 'json',
				type: 'POST',
				success: function(result) {
					if (result.status === 'success') {
						const sorting = $('.ipaddress-filter-conditions .dd-item').map(function() {
							return parseFloat($(this).data('sorting'));
						}).get();

						sorting.sort((a, b) => a - b);
						const newSorting = (sorting.length ? sorting[sorting.length - 1] : 0) + 1;

						$('.ipaddress-filter-conditions').append(
							`<div class="dd"><ol class="dd-list"><li class="dd-item bordered-palegreen" data-sorting="${newSorting}"><div class="dd-handle"><div class="form-horizontal"><div class="form-group no-margin-bottom ipaddress-filter-row">${result.html}<a class="delete-associated-item" onclick="if (confirm(i18n['confirm_delete'])) { $(this).parents('.dd-item').remove() } return false "><i class="fa fa-times-circle darkorange"></i></a></div></div></li></ol></div></div><input type="hidden" name="ipaddress_filter_sorting[]" value="${newSorting}"/>`
						);

						$.loadIpaddressFilterNestable();
					}
				}
			});
		},

		changeIpaddressFilterBlockMode: function(object) {
			const $object = $(object);
			const $ban_hours = $('input[name="ban_hours"]').parents('.form-group');

			if (+$object.val()) {
				$ban_hours.addClass('hidden');
			} else {
				$ban_hours.removeClass('hidden');
			}
		},

		changeIpaddressFilterOption: function(object) {
			const $object = $(object);
			const $parent = $object.parents('.ipaddress-filter-row');

			// Кешируем элементы
			const $elements = {
				get_name: $parent.find('.ipaddress-filter-get-name'),
				header_name: $parent.find('.ipaddress-filter-header-name'),
				condition: $parent.find('.ipaddress-filter-condition'),
				value: $parent.find('.ipaddress-filter-value'),
				case_sensitive: $parent.find('.ipaddress-filter-case-sensitive'),
				header_case_sensitive: $parent.find('.ipaddress-filter-header-case-sensitive'),
				days: $parent.find('.filter-days-wrapper')
			};

			const val = $object.val();

			$elements.days.removeClass('hidden');

			// Logic for GET
			if (val === 'get') {
				$elements.get_name.removeClass('hidden');
			} else {
				$elements.get_name.addClass('hidden');
			}

			// Logic for HEADER
			if (val === 'header') {
				$elements.header_name.removeClass('hidden');
				$elements.header_case_sensitive.removeClass('hidden');
				$elements.days.addClass('hidden');
			} else {
				$elements.header_name.addClass('hidden');
				$elements.header_case_sensitive.addClass('hidden');
			}

			// Logic for Delta Mobile Resolution
			if (val === 'delta_mobile_resolution') {
				$elements.condition.addClass('hidden');
				$elements.value.addClass('hidden');
				$elements.case_sensitive.addClass('hidden');
			} else {
				$elements.condition.removeClass('hidden');
				$elements.value.removeClass('hidden');
				$elements.case_sensitive.removeClass('hidden');
			}
		},

		addIpaddressVisitorFilterCondition: function() {
			$.ajax({
				url: hostcmsBackend + '/ipaddress/visitor/filter/index.php',
				data: { 'add_filter': 1 },
				dataType: 'json',
				type: 'POST',
				success: function(result) {
					if (result.status === 'success') {
						const sorting = $('.ipaddress-filter-conditions .dd-item').map(function() {
							return parseFloat($(this).data('sorting'));
						}).get();

						sorting.sort((a, b) => a - b);
						const newSorting = (sorting.length ? sorting[sorting.length - 1] : 0) + 1;

						$('.ipaddress-filter-conditions').append(
							`<div class="dd"><ol class="dd-list"><li class="dd-item bordered-palegreen" data-sorting="${newSorting}"><div class="dd-handle"><div class="form-horizontal"><div class="form-group no-margin-bottom ipaddress-filter-row">${result.html}<a class="delete-associated-item" onclick="if (confirm(i18n['confirm_delete'])) { $(this).parents('.dd-item').remove() } return false"><i class="fa fa-times-circle darkorange"></i></a></div></div></li></ol></div></div><input type="hidden" name="ipaddress_filter_sorting[]" value="${newSorting}"/>`
						);

						$.loadIpaddressFilterNestable();
					}
				}
			});
		},

		soundSwitch: function(event) {
			$.ajax({
				url: event.data.path,
				type: "POST",
				data: { 'sound_switch_status': 1 },
				dataType: 'json',
				success: function(result) {
					const $soundSwitch = $("#sound-switch").data('soundEnabled', result.answer != 0);
					$soundSwitch.html(result.answer == 0 ?
						'<i class="icon fa-solid fa-volume-xmark"></i>' :
						'<i class="icon fa-solid fa-volume-high"></i>');
				}
			});
		},

		loadWallpaper: function(wallpaper_id) {
			$.ajax({
				url: hostcmsBackend + '/user/index.php',
				type: "POST",
				data: { 'loadWallpaper': wallpaper_id },
				dataType: 'json',
				success: function(answer) {
					if (answer.id) {
						const imgTag = answer.src !== '' ? `<img src="${answer.src}" />` : '';
						$('ul.wallpaper-picker').append(
							`<li>
								<span class="colorpick-btn" onclick="$.changeWallpaper(this)" data-id="${answer.id}" data-original-path="${answer.original_path}" data-original-color="${answer.color}" style="background-color: ${answer.color}">
									${imgTag}
								</span>
							</li>`
						);
						$('#user-info-dropdown .login-area').effect('pulsate', { times: 3 }, 3000);
					}
				}
			});
		},

		changeWallpaper: function(node) {
			const $node = $(node);
			const wallpaper_id = $node.data('id');
			const original = $node.data('original-path');
			const color = $node.data('original-color');

			createCookie("wallpaper-id", wallpaper_id, 365);

			$.ajax({
				url: hostcmsBackend + '/user/index.php',
				type: 'POST',
				data: { 'wallpaper-id': wallpaper_id },
				dataType: 'json',
				success: function() {
					const bgImage = original !== '' ? `url(${original})` : 'none';
					const bgColor = color !== '' ? `background-color: ${color};` : '';
					$('head').append(`<style>body.hostcms-bootstrap1:before{ background-image: ${bgImage}; ${bgColor} }</style>`);
				}
			});
		},

		loadSiteList: function() {
			const data = $.getData({});

			$.ajax({
				url: hostcmsBackend + '/index.php?ajaxWidgetLoad&moduleId=0&type=10',
				type: "POST",
				data: data,
				dataType: 'json',
				success: function(data) {
					$('#sitesListIcon span.badge').text(data.count);
					$('#sitesListBox').html(data.content);

					$('.scroll-sites').slimscroll({
						height: 'auto',
						color: 'rgba(0,0,0,0.3)',
						size: '5px',
						wheelStep: 2
					});
				}
			});
		},

		generatePassword: function(length) {
			const $firstPassword = $("[name='password_first']");
			const $secondPassword = $("[name='password_second']");

			$.ajax({
				url: hostcmsBackend + '/user/index.php',
				type: 'POST',
				data: { 'generate-password': 1, 'length': length },
				dataType: 'json',
				success: function(answer) {
					$firstPassword.prop('type', 'text').val(answer.password).focus();
					$secondPassword.prop('type', 'text').val(answer.password).focus();
					$firstPassword.focus();
				}
			});
		}
	});

})(jQuery);

// Helper function
function setIColor(object) { // eslint-disable-line
	const $object = jQuery(object);
	const $i = $object.parents('.import-row').find('i.fa-circle');
	const color = $object.find('option:selected').css('background-color');

	$i.css('color', color);
}