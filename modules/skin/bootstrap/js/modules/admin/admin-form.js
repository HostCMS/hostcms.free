/* global hostcmsBackend, syntaxhighlighter, wysiwyg, i18n, mainFieldChecker, prepareKanbanBoards, navbarHeaderCustomization, changeDublicateTables, setCursorAdminTableWrap, setResizableAdminTableTh */

(function($) {
	"use strict";

	$.extend({
		toggleModificationPattern: function(checkbox, propertyId, propertyName, selectName) {
			const $checkbox = $(checkbox);
			const $targetInput = $('input[name="name"]');
			const $selectOptions = $(`select[name="${selectName}"] option`);
			const delimiter = $('input[name="delimiter"]').val() || ' ';
			const str = $targetInput.val();
			const bUsePropertyName = $('input[name="use_property_name"]').is(':checked');
			const pattern = `${delimiter}${bUsePropertyName ? propertyName + ' ' : ''}{P${propertyId}}`;

			if ($checkbox.is(':checked')) {
				if (str.indexOf(pattern) === -1) {
					$targetInput.val(str + pattern);
					if (!$(`select[name="${selectName}"] option:selected`).length) {
						$selectOptions.prop('selected', true);
					}
				}
			} else {
				$targetInput.val(str.replace(pattern, ''));
				$selectOptions.prop('selected', false);
			}
		},

		clearMarkingPattern: function(selector, pattern) {
			$(`input[name="${selector}"]`).val(pattern);
		},

		addModificationValue: function(object, name) {
			const $object = $(object);
			const type = $object.attr('type');
			let value = null;

			switch (type) {
				case 'checkbox':
					value = +$object.is(':checked');
					break;
				case 'text':
					value = $object.val();
					break;
			}

			if ($.cookie(name) !== null) {
				$.cookie(name, value);
			} else {
				$.cookie(name, value, { expires: 365 }); // days
			}
		},

		showAdminFormSettings: function(admin_form_id, site_id, modelsNames) {
			$.ajax({
				url: hostcmsBackend + '/admin_form/index.php',
				data: {
					'showAdminFormSettingsModal': 1,
					'admin_form_id': admin_form_id,
					'site_id': site_id,
					'modelsNames': modelsNames
				},
				dataType: 'json',
				type: 'POST',
				success: function(response) {
					$('body').append(response.html);
					const $modal = $('#adminFormSettingsModal' + admin_form_id);
					$modal.modal('show');
					$modal.on('hidden.bs.modal', function() {
						$(this).remove();
					});
				}
			});
		},

		selectAdminFormSettings: function(admin_form_id, type) {
			const $modal = $('#adminFormSettingsModal' + admin_form_id);

			switch (type) {
				case 0:
					$modal.find('.checkbox-inline input').prop('checked', false);
					$modal.find('.admin-field-checked').removeClass('admin-field-checked');
					break;
				case 1:
					$modal.find('.checkbox-inline input').prop('checked', true);
					$modal.find('.checkbox-inline').each(function() {
						$(this).parents('.form-group')
							.removeClass('admin-field-checked')
							.addClass('admin-field-checked');
					});
					break;
			}
		},

		selectAdminFormSetting: function(object) {
			const $object = $(object);
			const checked = $object.is(':checked');
			const $parent = $object.parents('.form-group');

			if (checked) {
				$parent.addClass('admin-field-checked');
			} else {
				$parent.removeClass('admin-field-checked');
			}
		},

		changeModuleOptionValue: function(object, windowId, name) {
			const checked = $(object).is(':checked');
			const value = checked ? 1 : 0;
			$('#' + windowId + ' *[name="' + name + '"]').val(value);
		},

		changeModuleOption: function(object, windowId, name) {
			const $object = $(object);
			const $field = $('#' + windowId + ' *[id="' + name + '"]');
			const disabled = $field.eq(0).attr('disabled');

			if ($object.parents('.option-row').length && $field.length) {
				$field.attr('disabled', !disabled);
				return;
			}

			const $parent = $object.parents('.option-row-parent');
			if ($parent.length) {
				const p_disabled = $object.is(':checked');

				$parent.find('.form-control').each(function() {
					const $item = $(this);
					const inputType = $item.attr('type');

					switch (inputType) {
						case 'text':
							$item.attr('disabled', !p_disabled);
							break;
						case 'checkbox':
							if (!$item.hasClass('option-check')) {
								$item.attr('disabled', !p_disabled);
							} else {
								$item.prop('checked', p_disabled);
							}
							break;
					}
				});
			}
		},

		scheduleLoadEntityCaption: function(object) {
			const $option = $(object).find(":selected");
			const entityCaption = $option.attr('data-entitycaption') || '';
			const $entity = $('#entity_id');
			const $group = $entity.parents('.form-group');

			$group.removeClass('hidden');

			if (entityCaption !== '') {
				$entity.prev().text(entityCaption);
			} else {
				$group.addClass('hidden');
			}
		},

		showLocked: function($form) {
			const admin_form_id = $form.data('adminformid');

			if (admin_form_id) {
				const dataset = $form.data('datasetid');
				const entity_id = $('input[name="id"]', $form).val();

				$.ajax({
					url: hostcmsBackend + '/admin_form/index.php',
					data: {
						'show_locked': 1,
						'admin_form_id': admin_form_id,
						'dataset': dataset,
						'entity_id': entity_id
					},
					dataType: 'json',
					type: 'POST',
					success: function(answer) {
						if (answer.status === 'success') {
							const $id_message = $form.parents('.widget').prev();
							if ($id_message.length) {
								$id_message.after(answer.text);
							} else {
								$form.parents('.bootbox-body').find('#id_message').eq(0).after(answer.text);
							}

							setTimeout(() => {
								$('.admin-form-locked').fadeIn(150);
							}, 200);
						}
					}
				});
			}
		},

		showAutosave: function($form) {
			const admin_form_id = $form.data('adminformid');

			if ($form.data('autosave') && admin_form_id) {
				const dataset = $form.data('datasetid');
				const entity_id = $('input[name="id"]', $form).val();

				$.ajax({
					url: hostcmsBackend + '/admin_form/index.php',
					data: {
						'show_autosave': 1,
						'admin_form_id': admin_form_id,
						'dataset': dataset,
						'entity_id': entity_id
					},
					dataType: 'json',
					type: 'POST',
					success: function(answer) {
						if (answer.id) {
							const $id_message = $form.parents('.widget').prev();

							if ($id_message.length) {
								$id_message.after(answer.text);
							} else {
								$form.parents('.bootbox-body').find('#id_message').eq(0).after(answer.text);
							}

							setTimeout(() => {
								const $autosave = $('.admin-form-autosave');
								$autosave.fadeIn(150);
								$autosave.find('a').on('click', function() {
									$.loadAutosave(answer.id, answer.json);
								});
							}, 500);
						}
					}
				});
			}
		},

		loadAutosave: function(id, json) {
			const obj = JSON.parse(json);

			$.each(obj, function(key, aValue) {
				if (aValue.name === 'secret_csrf') return;

				const $field = $(`*[name="${aValue.name}"]`);
				if (!$field.length) return;

				const type = $field.getInputType();

				if (type) {
					switch (type) {
						case 'textarea':
							$field.text(aValue.value);
							if (typeof syntaxhighlighter !== 'undefined') {
								syntaxhighlighter.loadAutosave($field);
							}
							break;
						case 'checkbox':
							$field.prop('checked', !!aValue.value);
							break;
						case 'radio':
							return;
						case 'hidden':
							$field.val(aValue.value);
							// checkbox fix
							$field.parent().find(`input[name="${aValue.name}"][type="checkbox"]`).prop('checked', !!+aValue.value);
							break;
						case 'ul':
							// dropdown
							$field.next().val(aValue.value);
							$._changeDropdown($field.find(`li[id="${aValue.value}"]`));
							break;
						default:
							$field.val(aValue.value);
					}
				}
			});

			$.ajax({
				url: hostcmsBackend + '/admin_form/index.php',
				data: { 'delete_autosave': 1, 'admin_form_autosave_id': id },
				dataType: 'json',
				type: 'POST'
			});

			$('.admin-form-autosave').fadeOut(150);
		},

		appendInput: function(windowId, InputName, InputValue) {
			windowId = $.getWindowId(windowId);
			const $adminForm = $('#' + windowId + ' .adminForm');

			if ($adminForm.length) {
				let $input = $adminForm.eq(0).find(`input[name="${InputName}"]`);

				if ($input.length === 0) {
					$input = $('<input>').attr('type', 'hidden').attr('name', InputName);
					$adminForm.append($input);
				}
				$input.val(InputValue);
			}
		},

		addHostcmsChecked: function(windowId, datasetId, value) {
			windowId = $.getWindowId(windowId);
			const $adminForm = $('#' + windowId + ' .adminForm');

			if ($adminForm.length) {
				let action = $adminForm.attr('action');
				action += (action.indexOf('?') >= 0 ? '&' : '?') + `hostcms[checked][${parseInt(datasetId)}][${parseInt(value)}]=1`;
				$adminForm.attr('action', action);
			}
		},

		toogleInputsActive: function(jForm, disableButtons) {
			jForm.find('.formButtons input').attr('disabled', disableButtons);
		},

		getWindowId: function(WindowId) {
			return !WindowId ? 'id_content' : WindowId;
		},

		adminCheckObject: function(settings) {
			settings = $.extend({
				objectId: '',
				windowId: 'id_content'
			}, settings);

			const safeId = $.escapeSelector(settings.objectId);
			const cbItem = $(`#${settings.windowId} #${safeId}`);

			if (cbItem.length > 0) {
				// Uncheck all checkboxes with name like 'check_'
				$(`#${settings.windowId} input[type='checkbox'][id^='check_']:not([name*='_fv_'])`).prop('checked', false);
				// Check checkbox
				cbItem.prop('checked', true);
			} else {
				const Check_0_0 = $('<input>')
					.attr('type', 'checkbox')
					.attr('id', settings.objectId);

				$('<div>')
					.css("display", 'none')
					.append(Check_0_0)
					.appendTo($(`#${settings.windowId}`));

				Check_0_0.prop('checked', true);
			}

			$("#" + settings.windowId).setTopCheckbox();
		},

		loadDocumentText: function(data) {
			const $form = $(this);
			const tinyTextarea = $("textarea[name='document_text']", $form);

			$.loadingScreen('hide');

			$("a.document-edit", $form).attr('href', data.editHref);

			if ('template_id' in data) {
				tinyTextarea.val(data.text);
				$("select#template_id", $form).val(data.template_id);

				if (typeof wysiwyg !== 'undefined') {
					wysiwyg.reloadTextarea(tinyTextarea, data.css);
				}
			}
		},

		addPropertyListItem: function(event, object) {
			event.preventDefault();
			const $object = $(object);
			const list_id = $object.data('list-id');

			$.ajax({
				url: hostcmsBackend + '/list/item/index.php',
				data: { 'showAddListItemModal': 1, 'list_id': list_id },
				dataType: 'json',
				type: 'POST',
				success: function(response) {
					$('body').append(response.html);
					const $modal = $('#listItemModal' + list_id);
					$modal.modal('show');
					$modal.on('hidden.bs.modal', function() {
						$(this).remove();
					});
				}
			});
		},

		savePropertyListItem: function(windowId, list_id, value) {
			$.ajax({
				url: hostcmsBackend + '/list/item/index.php',
				data: { 'addListItem': 1, 'list_id': list_id, 'value': value },
				dataType: 'json',
				type: 'POST',
				success: function(response) {
					$('#listItemModal' + list_id).modal('hide');

					if (response.status === 'success') {
						$('#' + windowId + ' select[data-list-id="' + list_id + '"]').append($('<option>', {
							value: response.list_item_id,
							text: response.value
						}));
					}
				}
			});
		},

		deleteProperty: function(object, settings) {
			let jObject = $(object).parents('div.input-group');
			jObject = jObject.find('input:not([id^="filter_"]),select:not([onchange]),textarea');

			// For files
			if (jObject.length === 0) {
				jObject = $(object).siblings('div,label').children('input');
			}

			mainFieldChecker.removeField(jObject);

			const property_name = jObject.eq(0).attr('name');

			settings = $.extend({
				operation: property_name
			}, settings);

			settings = $.requestSettings(settings);

			const data = $.getData(settings);
			data[`hostcms[checked][${settings.datasetId}][${settings.objectId}]`] = 1;

			$.ajax({
				context: $('#' + settings.windowId),
				url: settings.path,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: $.ajaxCallback
			});

			$.deleteNewProperty(object);
		},

		deleteNewProperty: function(object) {
			const propertyBlock = $(object).closest('[id^="property_"]');

			// Если осталось последнее свойство, то клонируем его перед удалением
			if (!propertyBlock.siblings('#' + propertyBlock.prop('id')).length) {
				propertyBlock.find('.btn-clone').click();
			}

			propertyBlock.remove();
		},

		cloneProperty: function(windowId, index, object) {
			const jCopiedProperty = $(object).parents('#' + windowId + ' #property_' + index);
			const jSourceProperty = $('#' + windowId + ' #property_' + index).eq(0);

			// Закрываем настройки изображений
			jSourceProperty.find("span[id^='file_large_settings_'], span[id^='file_small_settings_']").each(function() {
				const $span = $(this);
				if ($span.children('i').hasClass('fa-times')) {
					$span.click();
				}
			});

			let html = jSourceProperty[0].outerHTML;
			const iRand = Math.floor(Math.random() * 999999);

			html = html.replace(/(id_property(?:_[\d]+)+)/g, 'id_property_clone' + iRand);

			const jNewObject = $($.parseHTML(html, document, true));

			// Clear autocomplete & inputs
			jNewObject.find("input.ui-autocomplete-input").val('');
			jNewObject.addClass('new-property');
			jNewObject.insertAfter(jCopiedProperty);

			jNewObject.find("textarea")
				.removeAttr('wysiwyg')
				.css('display', '');

			// Update IDs
			jNewObject.find("div[id^='file_']").each(function() {
				$(this).prop('id', $(this).prop('id') + '_' + iRand);
				$(this).find("div[id^='popover']").remove();
			});

			// Update Input Names
			jNewObject.find("input[id^='typograph_']").attr('name', `typograph_${index}[]`);
			jNewObject.find("input[id^='trailing_punctuation_']").attr('name', `trailing_punctuation_${index}[]`);

			// Remove preview elements
			jNewObject.find("[id^='preview_large_property_'], [id^='delete_large_property_'], [id^='preview_small_property_'], [id^='delete_small_property_']").remove();
			jNewObject.find(`input[id^='property_${index}_'][type='file'] ~ script`).remove();

			jNewObject.find("input[id^='field_id'],select:not([id$='_mode']),textarea")
				.attr('name', `property_${index}[]`);

			jNewObject.find("div[id^='file_small'] input[id^='small_field_id']")
				.attr('name', `small_property_${index}[]`).val('');

			jNewObject.find("input[id^='id_property_'][type!=checkbox],input[id^='small_property_'][type!=checkbox],input[class*='description'][type!=checkbox],select,textarea")
				.val('');

			jNewObject.find("select[id$='_mode'] option:first").prop('selected', true).change();
			jNewObject.find("input[id^='create_small_image_from_large_small_property']").prop('checked', true);

			// Update dynamic names using regex logic
			jNewObject.find(':regex(name, ^\\S+_\\d+_\\d+$)').each(function(i, obj) {
				const reg = /^(\S+)_(\d+)_(\d+)$/;
				const arr = reg.exec(obj.name);
				const inputId = $(obj).prop('id');

				$(obj).prop('name', `${arr[1]}_${arr[2]}[]`);
				jNewObject.find(`a[id='crop_${inputId}']`)
					.attr('onclick', `$.showCropModal('${inputId}', '', '')`);
			});

			jNewObject.find("div.img_control div, a[id^='preview_'], a[id^='delete_'], div[role='application']").remove();
			jNewObject.find("input[type='text'].description-large").attr('name', `description_property_${index}[]`);
			jNewObject.find("input[type='text'].description-small").attr('name', `description_small_property_${index}[]`);

			jNewObject.find(".file-caption-wrapper")
				.addClass('hidden')
				.parents('.input-group').find('input:first-child').removeClass('hidden');

			jNewObject.find('.add-remove-property > div')
				.addClass('btn-group')
				.find('.btn-delete').removeClass('hide');

			jNewObject.find(':input').blur();

			if ($('.section-' + index).hasClass('ui-sortable')) {
				$('.section-' + index).sortable('refresh');
			}
		},

		clonePropertyInfSys: function(windowId, index, object) {
			const jProperies = $('#' + windowId + ' #property_' + index);
			let html = jProperies[0].outerHTML;
			const iRand = Math.floor(Math.random() * 999999);

			const jSourceProperty = $(object).parents('#' + windowId + ' #property_' + index);

			html = html
				.replace(/oSelectFilter(\d+)/g, 'oSelectFilter$1clone' + iRand)
				.replace(/(id_group_[\d_]*)/g, 'id_group_clone' + iRand)
				.replace(/(id_property_[\d_]*)/g, 'id_property_clone' + iRand)
				.replace(/(input_property_[\d_]*)/g, 'input_property_clone' + iRand);

			const jNewObject = $($.parseHTML(html, document, true));
			const jDir = jNewObject.find("select[onchange]");
			const jItem = jNewObject.find("select:not([onchange])");
			const jItemInput = jNewObject.find("input:not([onchange])");

			if (jDir.length) {
				jDir.val(jProperies.eq(0).find("select[onchange]").val());
				jItem.val(jProperies.eq(0).find("select:not([onchange])").val()); // .val() добавлен
			} else {
				jItem.val(0);
			}

			jItem.attr('name', 'property_' + index + '[]');
			jItemInput.val(null);

			jNewObject.find('.add-remove-property > div')
				.addClass('btn-group')
				.find('.btn-delete').removeClass('hide');

			jNewObject.find("img#delete").attr('onclick', "jQuery.deleteNewProperty(this)");
			jNewObject.insertAfter(jSourceProperty);
			jItemInput.trigger("change").trigger("keyup");
		},

		cloneFile: function(windowId) {
			const jProperies = $('#' + windowId + ' #file');
			const jNewObject = jProperies.eq(0).clone();

			jNewObject.find("input[type='file']").attr('name', 'file[]').val('');
			jNewObject.find("input[type='text']").attr('name', 'description_file[]').val('');
			jNewObject.insertAfter(jProperies.eq(-1));
		},

		cloneFormRow: function(cloningElement) {
			if (cloningElement) {
				const originalRow = $(cloningElement).closest('.row');
				const newRow = originalRow.clone();

				newRow.find('input').each(function() {
					if ($(this).attr('type') === "checkbox") {
						$(this).prop('checked', false);
					} else {
						$(this).val('');
					}
				});

				newRow.find('select').each(function() {
					$(this).find(':selected').removeAttr("selected");
					$(this).find(':first').attr("selected", "selected");
				});

				newRow.find('input[name*="#"], select[name*="#"]').each(function() {
					this.name = this.name.split('#')[0] + '[]';
				});

				newRow.find('#pathLink').attr('href', '/');
				newRow.find('.btn-delete').removeClass('hide');
				newRow.find('.add-remove-property').addClass('btn-group');
				newRow.insertAfter(originalRow);

				return newRow;
			}
		},

		deleteFormRow: function(deleteElement) {
			if (deleteElement) {
				const objectRow = $(deleteElement).closest('.row');
				if (!objectRow.siblings('.row').length) {
					$.cloneFormRow(deleteElement).find('.add-remove-property').removeClass('btn-group').find('.btn-delete').addClass('hide');
				}
				objectRow.remove();
			}
		},

		cloneField: function(windowId, index) {
			const jFields = $('#' + windowId + ' #field_' + index);
			const jSourceField = jFields.eq(0);
			let html = jSourceField[0].outerHTML;
			const iRand = Math.floor(Math.random() * 999999);

			html = html.replace(/(id_field(?:_[\d]+)+)/g, 'id_field_clone' + iRand);

			const jNewObject = $($.parseHTML(html, document, true));

			jNewObject.find("input.ui-autocomplete-input").val('');
			jNewObject.insertAfter(jFields.eq(-1));
			jNewObject.find("textarea").removeAttr('wysiwyg').css('display', '');

			jNewObject.find("div[id^='file_']").each(function() {
				$(this).prop('id', $(this).prop('id') + '_' + iRand);
				$(this).find("div[id^='popover']").remove();
			});

			jNewObject.find("[id^='preview_large_field_'], [id^='delete_large_field_'], [id^='preview_small_field_'], [id^='delete_small_field_']").remove();
			jNewObject.find(`input[id^='field_${index}_'][type='file'] ~ script`).remove();

			jNewObject.find("input[id^='field_id'],select:not([id$='_mode']),textarea").attr('name', `field_${index}[]`);
			jNewObject.find("input[id^='id_field_'][type!=checkbox],input[id^='small_field_'][type!=checkbox],input[class*='description'][type!=checkbox],select,textarea").val('');

			jNewObject.find("select[id$='_mode'] option:first").prop('selected', true).change();

			jNewObject.find(':regex(name, ^\\S+_\\d+_\\d+$)').each(function(i, obj) {
				const reg = /^(\S+)_(\d+)_(\d+)$/;
				const arr = reg.exec(obj.name);
				const inputId = $(obj).prop('id');

				$(obj).prop('name', `${arr[1]}_${arr[2]}[]`);
				jNewObject.find(`a[id='crop_${inputId}']`).attr('onclick', `$.showCropModal('${inputId}', '', '')`);
			});

			jNewObject.find("div.img_control div, a[id^='preview_'], a[id^='delete_'], div[role='application']").remove();
			jNewObject.find("input[type='text'].description-large").attr('name', `description_field_${index}[]`);
			jNewObject.find("input[type='text'].description-small").attr('name', `description_small_field_${index}[]`);

			jNewObject.find(".file-caption-wrapper").addClass('hidden').parents('.input-group').find('input:first-child').removeClass('hidden');
			jNewObject.find('.add-remove-property > div').addClass('btn-group').find('.btn-delete').removeClass('hide');

			jNewObject.find(':input').blur();
		},

		cloneFieldInfSys: function(windowId, index) {
			const jFields = $('#' + windowId + ' #field_' + index);
			let html = jFields[0].outerHTML;
			const iRand = Math.floor(Math.random() * 999999);

			html = html
				.replace(/oSelectFilter(\d+)/g, 'oSelectFilter$1clone' + iRand)
				.replace(/(id_group_[\d_]*)/g, 'id_group_clone' + iRand)
				.replace(/(id_field_[\d_]*)/g, 'id_field_clone' + iRand)
				.replace(/(input_field_[\d_]*)/g, 'input_field_clone' + iRand);

			const jNewObject = $($.parseHTML(html, document, true));
			const jDir = jNewObject.find("select[onchange]");
			const jItem = jNewObject.find("select:not([onchange])");
			const jItemInput = jNewObject.find("input:not([onchange])");

			if (jDir.length) {
				jDir.val(jFields.eq(0).find("select[onchange]").val());
				jItem.val(jFields.eq(0).find("select:not([onchange])").val());
			} else {
				jItem.val(0);
			}

			jItem.attr('name', 'field_' + index + '[]').val();
			jItemInput.val(null).trigger("change");

			jNewObject.find('.add-remove-property > div').addClass('btn-group').find('.btn-delete').removeClass('hide');
			jNewObject.find("img#delete").attr('onclick', "jQuery.deleteNewField(this)");
			jNewObject.insertAfter(jFields.eq(-1));
		},

		deleteField: function(object, settings) {
			let jObject = $(object).parents('div.input-group');
			jObject = jObject.find('input:not([id^="filter_"]),select:not([onchange]),textarea');

			if (jObject.length === 0) {
				jObject = $(object).siblings('div,label').children('input');
			}

			mainFieldChecker.removeField(jObject);
			const field_name = jObject.eq(0).attr('name');

			settings = $.extend({
				operation: typeof settings.prefix !== 'undefined' ? settings.prefix + field_name : field_name
			}, settings);

			settings = $.requestSettings(settings);

			const data = $.getData(settings);
			data[`hostcms[checked][1][${settings.fieldId}]`] = 1;
			data.fieldValueId = settings.fieldValueId;
			data.field_dir_id = settings.fieldDirId;
			data.model = settings.model;

			$.ajax({
				context: $('#' + settings.windowId),
				url: settings.path,
				type: 'POST',
				data: data,
				dataType: 'json',
				success: $.ajaxCallback
			});

			$.deleteNewField(object);
		},

		deleteNewField: function(object) {
			const fieldBlock = $(object).closest('[id^="field_"]');
			if (!fieldBlock.siblings('#' + fieldBlock.prop('id')).length) {
				fieldBlock.find('.btn-clone').click();
			}
			fieldBlock.remove();
		},

		cloneMultipleValue: function(windowId, lib_property_id, object) {
			const jCopiedProperty = $(object).closest('div#lib_property_' + lib_property_id);
			let html = jCopiedProperty[0].outerHTML;
			const iRand = Math.floor(Math.random() * 999999);

			html = html.replace(/(?:id_lib_property|id_lib_property_clone)((?:_\d+)+)/g, 'id_lib_property_clone$1' + iRand);

			const jNewObject = $($.parseHTML(html, document, true));
			jNewObject.addClass('new-lib-property');

			if (typeof wysiwyg !== 'undefined') {
				wysiwyg.clear(jNewObject);
			}

			jNewObject.insertAfter(jCopiedProperty);

			jNewObject.find("div[id^='file_']").each(function() {
				$(this).prop('id', $(this).prop('id') + '_' + iRand);
				$(this).find("div[id^='popover']").remove();
			});

			jNewObject.find("[id^='preview_large_'], [id^='delete_large_'], [id^='preview_small_'], [id^='delete_small_']").remove();
			jNewObject.find(`input[id^='lib_property_${lib_property_id}_'][type='file'] ~ script`).remove();

			jNewObject.find("input[id^='id_lib_property_'][type!=checkbox],input[id^='small_'][type!=checkbox],input[class*='description'][type!=checkbox],select,textarea").val('');

			jNewObject.find(':regex(name, ^\\S+_\\d+(\\[\\])?$)').each(function(i, object) {
				const reg = /^(\S+?_)(\d+)_(\d+)(_\d+$|\[\]$)?$/;
				const arr = reg.exec(object.name);
				const prefix = arr[1];
				let newName;

				if (typeof arr[4] !== 'undefined') {
					if (jCopiedProperty.hasClass('complex-block')) {
						let incremented = parseInt($('.section-' + lib_property_id + ' > div.multiple_value').length - 1);
						if (incremented < 0) incremented = 0;
						newName = prefix + incremented + '_' + arr[3] + '[]';
					} else {
						newName = prefix + arr[2] + '_' + arr[3] + '[]';
					}
				} else {
					newName = prefix + arr[2] + '[]';
				}
				$(object).prop('name', newName);
			});

			jNewObject.find("div.img_control div, a[id^='preview_'], a[id^='delete_'], div[role='application']").remove();
			jNewObject.find(".file-caption-wrapper").addClass('hidden').closest('.input-group').find('input:first-child').removeClass('hidden');
			jNewObject.find('.add-remove-property > div').find('.btn-delete').removeClass('hide');

			jNewObject.find(':input').blur();
			jNewObject.find("input,select,textarea").val('');

			if ($('.section-' + lib_property_id).hasClass('ui-sortable')) {
				$('.section-' + lib_property_id).sortable('refresh');
			}
		},

		deleteNewMultipleValue: function(object, lib_property_id) {
			const libPropertyBlock = $(object).closest('[id^="lib_property_"]');

			if (!libPropertyBlock.siblings('#' + libPropertyBlock.prop('id')).length) {
				libPropertyBlock.find("[id^='preview_large_'], [id^='delete_large_'], [id^='preview_small_'], [id^='delete_small_']").remove();
				libPropertyBlock.find(`input[id^='lib_property_${lib_property_id}_'][type='file'] ~ script`).remove();
				libPropertyBlock.find("input[id^='id_lib_property_'][type!=checkbox],input[id^='small_'][type!=checkbox],input[class*='description'][type!=checkbox],select,textarea").val('');
				libPropertyBlock.find("div.img_control div, a[id^='preview_'], a[id^='delete_'], div[role='application']").remove();
				libPropertyBlock.find(".file-caption-wrapper").addClass('hidden').parents('.input-group').find('input:first-child').removeClass('hidden');
				libPropertyBlock.find('.add-remove-property > div').find('.btn-delete').removeClass('hide');
				libPropertyBlock.find(':input').blur();
			} else {
				libPropertyBlock.remove();
			}
		},

		applyPropertySectionSortable: function(windowId, propertyId) {
			const sortableSelector = $('#' + windowId + ' .section-' + propertyId);
			const jSection = $(sortableSelector);

			jSection.sortable({
				connectWith: sortableSelector,
				items: '> div#property_' + propertyId + ':not(\'.new-property\')',
				scroll: false,
				placeholder: 'placeholder',
				cancel: '.add-remove-property, .form-control',
				tolerance: 'pointer',
				helper: function(event, ui) {
					const clone = $(ui).clone(true);
					$(ui).find('select').each(function(index, object) {
						clone.find('#' + object.id).val($(object).val());
					});
					return clone.css('position', 'absolute').get(0);
				},
				start: function(event, ui) {
					jSection.find('div#property_' + propertyId + ':hidden').addClass('ghost-item').css('opacity', .5).show();
					if (typeof wysiwyg !== 'undefined') {
						wysiwyg.remove($(ui.item).find('textarea'));
					}
				},
				stop: function() {
					const ghostItem = jSection.find('div.ghost-item');
					ghostItem.removeClass('ghost-item').css('opacity', 1);

					const tinyTextarea = ghostItem.find('textarea');
					const script = tinyTextarea.next('script').text();
					try {
						if (script) eval(script);
					} catch (e) { console.warn("Sortable eval error:", e); }
				}
			}).disableSelection();

			jSection.find(':input').on('touchstart', () => jSection.sortable('disable'))
				.on('touchend', () => jSection.sortable('enable'));
		},

		applyLibPropertySortable: function(windowId, libPropertyId) {
			const sortableSelector = $('#' + windowId + ' .section-' + libPropertyId);
			const jSection = $(sortableSelector);

			jSection.sortable({
				connectWith: sortableSelector,
				items: '> div#lib_property_' + libPropertyId + ':not(\'.new-lib-property\')',
				scroll: false,
				placeholder: 'placeholder',
				cancel: '.add-remove-property, .form-control',
				tolerance: 'pointer',
				helper: function(event, ui) {
					const clone = $(ui).clone(true);
					$(ui).find('select').each(function(index, object) {
						clone.find('#' + object.id).val($(object).val());
					});
					return clone.css('position', 'absolute').get(0);
				},
				start: function(event, ui) {
					jSection.find('#lib_property_' + libPropertyId + ':hidden').addClass('ghost-item').css('opacity', .5).show();
					if (typeof wysiwyg !== 'undefined') {
						wysiwyg.remove($(ui.item).find('textarea'));
					}
				},
				stop: function() {
					const ghostItem = jSection.find('div.ghost-item');
					ghostItem.removeClass('ghost-item').css('opacity', 1);
					const tinyTextarea = ghostItem.find('textarea');
					const script = tinyTextarea.next('script').text();
					try {
						if (script) eval(script);
					} catch (e) { console.warn("Lib Sortable eval error:", e); }
				}
			}).disableSelection();

			jSection.find(':input').on('touchstart', () => jSection.sortable('disable'))
				.on('touchend', () => jSection.sortable('enable'));
		},

		// Autocomplete functions... (Cleaned up vars)
		applyInformationsystemGroupAutocomplete: function(windowId, propertyId, informationsystemId) {
			const jInput = $('#' + windowId + ' input[id^="' + propertyId + '"]');
			const url = hostcmsBackend + '/informationsystem/item/index.php?autocomplete=1&show_group=1&informationsystem_id=' + informationsystemId;
			$.applyPropertyAutocomplete(jInput, url, {});
		},

		applyInformationsystemItemAutocomplete: function(windowId, propertyId, informationsystemId) {
			const jInput = $('#' + windowId + ' input[id^="' + propertyId + '"]');
			const settings = {
				source: function(request, response) {
					const selectedVal = jInput.parents('div[id^="property"]').first().find('[id^="id_group_"] :selected').val();
					const url = hostcmsBackend + '/informationsystem/item/index.php?autocomplete=1&informationsystem_id=' + informationsystemId + '&informationsystem_group_id=' + selectedVal;
					$.ajax({
						url: url,
						dataType: 'json',
						data: { queryString: request.term },
						success: function(data) { response(data); }
					});
				}
			};
			$.applyPropertyAutocomplete(jInput, '', settings);
		},

		applyShopGroupAutocomplete: function(windowId, propertyId, shopId) {
			const jInput = $('#' + windowId + ' input[id^="' + propertyId + '"]');
			const url = hostcmsBackend + '/shop/item/index.php?autocomplete=1&show_group=1&shop_id=' + shopId;
			$.applyPropertyAutocomplete(jInput, url, {});
		},

		applyShopItemAutocomplete: function(windowId, propertyId, shopId) {
			const jInput = $('#' + windowId + ' input[id^="' + propertyId + '"]');
			const settings = {
				source: function(request, response) {
					const selectedVal = jInput.parents('div[id^="property"]').first().find('[id^="id_group_"] :selected').val();
					const url = hostcmsBackend + '/shop/item/index.php?autocomplete=1&shop_id=' + shopId + '&shop_group_id=' + selectedVal;
					$.ajax({
						url: url,
						dataType: 'json',
						data: { queryString: request.term },
						success: function(data) { response(data); }
					});
				}
			};
			$.applyPropertyAutocomplete(jInput, '', settings);
		},

		applyListItemAutocomplete: function(windowId, propertyId, listId) {
			const jInput = $('#' + windowId + ' input[id^="' + propertyId + '"]');
			const url = hostcmsBackend + '/list/item/index.php?autocomplete=1&show_parents=1&list_id=' + listId;
			$.applyPropertyAutocomplete(jInput, url, {});
		},

		applyPropertyAutocomplete: function(jInput, url, settings) {
			settings = $.extend({
				source: function(request, response) {
					const $mode = $('#' + jInput.attr('id') + '_mode');
					let currentUrl = url;
					if ($mode.length) {
						currentUrl = currentUrl + '&mode=' + $mode.val();
					}
					$.ajax({
						url: currentUrl,
						dataType: 'json',
						data: { queryString: request.term },
						success: function(data) { response(data); }
					});
				},
				minLength: 1,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
						return $('<li class="autocomplete-suggestion' + (typeof item.active !== 'undefined' && !item.active ? ' line-through' : '') + '"></li>')
							.data('item.autocomplete', item)
							.append($('<div class="name">').html($.escapeHtml(item.label)))
							.append($('<div class="id">').html('[' + $.escapeHtml(item.id) + ']'))
							.appendTo(ul);
					};
					$(this).prev('.ui-helper-hidden-accessible').remove();
				},
				select: function(event, ui) {
					const jSelect = jInput.parents('[id^="property"]').first().find('select[name^="property_"]');
					jSelect.empty().append($('<option>', { value: ui.item.id, text: ui.item.label }).attr('selected', 'selected'));
					jSelect.trigger('appliedPropertyAutocomplete', [event, ui]);
				},
				change: function(event, ui) {
					if (ui.item === null) {
						const jSelect = jInput.parents('[id^="property"]').first().find('select[name^="property_"]');
						jSelect.empty().append($('<option>', { value: '', text: '' }).attr('selected', 'selected'));
						jSelect.trigger('appliedPropertyAutocomplete', [event, ui]);
					}
				},
				open: function() { $(this).removeClass('ui-corner-all').addClass('ui-corner-top'); },
				close: function() { $(this).removeClass('ui-corner-top').addClass('ui-corner-all'); }
			}, settings);

			jInput.autocomplete(settings);
		},

		focusAutocomplete: function(jObject, jFocusedElement) {
			jObject.keydown(function(event) {
				if (event.keyCode === 13) {
					event.preventDefault();
					jFocusedElement.focus();
					return false;
				}
			});
		},

		_changeDropdown: function($li) {
			const $a = $li.find('a');
			const dropdownMenu = $li.parent('.dropdown-menu');
			const containerCurrentChoice = dropdownMenu.prev('[data-toggle="dropdown"]');

			if ((!dropdownMenu.attr('current-selection') || dropdownMenu.attr('current-selection') !== 'enable') && $li.attr('selected')) {
				return;
			}

			dropdownMenu.next('input[type="hidden"]').val($li.attr('id')).trigger('change');
			containerCurrentChoice.css('color', $a.css('color'));
			containerCurrentChoice.html($a.html() + '<i class="fa fa-angle-down icon-separator-left"></i>');

			dropdownMenu.find('li[selected][id!="' + $li.prop('id') + '"]').removeAttr('selected');
			$li.attr('selected', 'selected');
			dropdownMenu.trigger('change');
		},

		cloneSiteFavicon: function(windowId, cloneDelete) {
			const jSiteFavicon = $(cloneDelete).closest('.site_favicons');
			const jNewObject = jSiteFavicon.clone();

			jNewObject.find(':regex(name, ^\\S+_\\d+$)').each(function(index, object) {
				const reg = /^(\S+)_(\d+)$/;
				const arr = reg.exec(object.name);
				$(object).prop('name', arr[1] + '_' + '[]');
			});
			jNewObject.find("input,select").val('');
			jNewObject.find("[id^='preview_large_'], [id^='delete_large_'], [id^='file_preview_large_']").remove();
			jNewObject.find("[type='file'] ~ script").remove();
			jNewObject.find("[type='file']").removeClass('hidden');

			jNewObject.insertAfter(jSiteFavicon);
		},
		deleteNewSiteFavicon: function(object) {
			$(object).closest('.site_favicons').remove();
		}
	});

	$.fn.extend({
		hostcmsEditable: function(settings) {
			settings = $.extend({
				save: function(item, value, settings) {
					const data = $.getData(settings);
					const itemId = item.prop('id');
					const reg = /apply_check_(\d+)_(\S+)_fv_(\S+)/;
					const arr = reg.exec(itemId);

					data[`hostcms[checked][${arr[1]}][${arr[2]}]`] = 1;
					data[itemId] = value;

					$.ajax({
						context: $('<img>').addClass('img_line').prop('src', '/modules/skin/default/js/ui/themes/base/images/ajax-loader.gif').appendTo(item),
						url: settings.path,
						type: 'POST',
						data: data,
						dataType: 'json',
						success: function(answer) {
							this.remove();
							const div = $('<div class="hidden-message hidden">').html(answer.error);
							$(item).append(div);
							div.remove();
						}
					});
				},
				action: 'apply'
			}, settings);

			return this.each(function(index, object) {
				$(object).on('dblclick touchend', function(event) {
					if (event.type === "touchend") {
						const now = Date.now();
						const timeSince = now - ($(this).data('latestTap') || 0);
						$(this).data({ 'latestTap': now });
						if (!timeSince || timeSince > 600) return;
					}

					const $item = $(this);
					const len = $item.text().length;
					const display = $item.css('display') || '';
					const bTextarea = $item.data('editable-type') === 'textarea';
					let $editor;

					if (len > 50 || bTextarea) {
						const $parent = $item.parent();
						let height = $parent.outerHeight();
						const width = $parent.outerWidth();

						if (width > 0) {
							let tmpHeight = len * 140 / width;
							if (tmpHeight > 300) tmpHeight = 300;
							height = height > tmpHeight ? height : tmpHeight;
						}

						$editor = $('<textarea>').css({ resize: 'vertical', width: '95%', height: height });
					} else {
						$editor = $('<input>').prop('type', 'text').width('95%');
					}

					$item.css('display', 'none');

					$editor.on('blur', function() {
							const $ed = $(this);
							const item = $ed.prev();
							const value = $ed.val();

							item.html($.escapeHtml(value).replace(/\n/g, "\n<br />")).css('display', display);
							$ed.remove();
							settings.save(item, value, settings);
						})
						.on('keydown', function(e) {
							if (e.keyCode === 13 && !bTextarea) { // Enter
								e.preventDefault();
								this.blur();
							}
							if (e.keyCode === 27) { // ESC
								e.preventDefault();
								const $ed = $(this);
								$ed.prev().css('display', display);
								$ed.remove();
							}
						})
						.prop('name', $item.parent().prop('id'))
						.insertAfter($item).focus().val($item.text());
				});
			});
		}
	});

})(jQuery);

// --- Classes ---

class FormAutosave {
	constructor() {
		this._timer = 0;
	}

	changed($form, event, windowId) {
		const $bVisible = $('#' + windowId + ' .admin-form-autosave').is(':visible');

		if (!$bVisible) {
			const keycode = (event && event.originalEvent instanceof KeyboardEvent) ? (event.keyCode || event.which) : null;
			const aKeycodes = [13, 16, 17, 18, 19, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145];

			if (!keycode || !aKeycodes.includes(keycode)) {
				if (this._timer) clearTimeout(this._timer);
				this._timer = setTimeout(() => this.save($form), 5000);
			}
		}
	}

	save($form) {
		const admin_form_id = $form.data('adminformid');
		const dataset = $form.data('datasetid');
		const entity_id = $('input[name="id"]', $form).val();

		if (typeof syntaxhighlighter !== 'undefined') {
			$("textarea", $form).each(function() {
				syntaxhighlighter.save($(this));
			});
		}

		const ignoreFields = ["secret_csrf"];
		const json = JSON.stringify(
			$form.serializeArray().filter(val => ignoreFields.indexOf(val.name) === -1)
		);

		$.ajax({
			url: hostcmsBackend + '/admin_form/index.php',
			data: {
				'autosave': 1,
				'admin_form_id': admin_form_id,
				'dataset': dataset,
				'entity_id': entity_id,
				'json': json
			},
			dataType: 'json',
			type: 'POST',
			success: function(answer) {
				const date = new Date();
				const $h4 = $('h4.modal-title');
				const $h5 = $('h5.row-title');
				const iconHtml = `<i title="${i18n['autosave_icon_title']}${date.toLocaleString()}" class="fas fa-save autosave-icon azure"></i>`;

				$h5.find('.autosave-icon').remove();
				$h4.find('.autosave-icon').remove();

				if (answer.status === 'success') {
					$h5.append(iconHtml);
					$h5.find('.autosave-icon').fadeOut(300).fadeIn(300);

					if ($h4.length) {
						$h4.eq(0).append(iconHtml);
						$h4.eq(0).find('.autosave-icon').fadeOut(300).fadeIn(300);
					}
				}
			}
		});
	}

	clear() {
		if (this._timer) {
			clearTimeout(this._timer);
		}
		this._timer = 0;
		$('h5.row-title, h4.modal-title').find('.autosave-icon').remove();
		return this;
	}
}
const mainFormAutosave = new FormAutosave(); // eslint-disable-line

class FormLocker {
	constructor() {
		this._locked = false;
		this._previousLocked = false;
		this._delay = false;
		this._enabled = true;
	}

	lock(event) {
		if (!this._delay && this._enabled) {
			const keycode = (event && event.originalEvent instanceof KeyboardEvent) ? (event.keyCode || event.which) : null;
			const aKeycodes = [13, 16, 17, 18, 19, 20, 27, 33, 34, 35, 36, 37, 38, 39, 40, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 144, 145];

			if (!this._locked && (!keycode || !aKeycodes.includes(keycode))) {
				$('body').on('beforeAdminLoad beforeAjaxCallback beforeHideModal', (e) => this._confirm(e));

				$('h5.row-title, h4.modal-title').append('<i class="fa fa-lock edit-lock"></i>');
				this._locked = true;

				if (event && event.delegateTarget && event.delegateTarget.nodeName === 'FORM') {
					const $form = $(event.delegateTarget);
					$.ajax({
						url: hostcmsBackend + '/admin_form/index.php',
						data: {
							'lock': 1,
							'admin_form_id': $form.data('adminformid'),
							'dataset': $form.data('datasetid'),
							'entity_id': $('input[name="id"]', $form).val()
						},
						dataType: 'json',
						type: 'POST'
					});
				}
			}
		}
		return this;
	}

	_confirm(event) {
		if (!confirm(i18n['lock_message'])) {
			return 'break';
		}
		this.unlock();

		if (event && event.delegateTarget && event.delegateTarget.nodeName === 'FORM') {
			const $form = $(event.delegateTarget);
			$.ajax({
				url: hostcmsBackend + '/admin_form/index.php',
				data: {
					'delete_lock': 1,
					'admin_form_id': $form.data('adminformid'),
					'dataset': $form.data('datasetid'),
					'entity_id': $('input[name="id"]', $form).val()
				},
				dataType: 'json',
				type: 'POST'
			});
		}
	}

	unlock() {
		this._locked = false;
		$('body').off('beforeAdminLoad beforeAjaxCallback beforeHideModal');
		$('h5.row-title > i.edit-lock, h4.modal-title > i.edit-lock').remove();

		if (!this._delay) {
			this._delay = true;
			setTimeout(() => this._resetDelay(), 3000);
		}
		return this;
	}

	_resetDelay() {
		this._delay = false;
		return this;
	}

	saveStatus() {
		this._previousLocked = this._locked;
		return this;
	}

	restoreStatus() {
		this._previousLocked ? this.lock() : this.unlock();
		this._previousLocked = false;
		return this;
	}

	enable() {
		this._enabled = true;
		return this;
	}

	disable() {
		this._enabled = false;
		return this;
	}
}
const mainFormLocker = new FormLocker(); // eslint-disable-line

// --- Init ---

$(function() {
	let resizeTimeout;

	$(window).on('resize', function() {
		const $this = $(this);

		if (resizeTimeout) clearTimeout(resizeTimeout);

		resizeTimeout = setTimeout(() => {
			if ($this.innerWidth() < 570) {
				$('.navbar .navbar-inner .navbar-header .navbar-account .account-area')
					.parent('.navbar-account.setting-open').removeClass('setting-open');
			}

			if (typeof changeDublicateTables === 'function') changeDublicateTables();
			if (typeof prepareKanbanBoards === 'function') prepareKanbanBoards();
			if (typeof navbarHeaderCustomization === 'function') navbarHeaderCustomization(true);

			$('.modal-dialog').each(function() {
				const modalDialog = $(this);
				const originalWidth = modalDialog.data('originalWidth');
				if (originalWidth) {
					modalDialog.css('width', ($this.width() > originalWidth + 30) ? originalWidth : '95%');
				}
			});

			if (typeof setResizableAdminTableTh === 'function') setResizableAdminTableTh();

		}, 100); // 100ms debounce
	});

	if (typeof navbarHeaderCustomization === 'function') navbarHeaderCustomization();

	$('.page-container').on('click', '.fa.profile-details', function() {
		$(this).closest('.ticket-item').next('li.profile-details').toggle(400, function() {
			$(this).prev('.ticket-item').find('.fa.profile-details').toggleClass('fa-chevron-down fa-chevron-up');
		});
	});

	$(document)
		.on('show.bs.modal', '.modal', function() {
			const zIndex = 1040 + (10 * $('.modal:visible').length);
			$(this).css('z-index', zIndex);
			setTimeout(function() {
				$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
			}, 0);
		})
		.on('hidden.bs.modal', '.modal', function() {
			if ($('.modal:visible').length) $(document.body).addClass('modal-open');
		});

	let dropdownMenu2;

	$('body')
		.on('click', '[id^="file_"][id*="_settings_"]', function() {
			$(this).popover({
				placement: 'left',
				content: $(this).nextAll('div[id*="_watermark_"]').show(),
				container: $(this).parents('div[id^="file_large_"], div[id^="file_small_"]'),
				template: '<div class="popover popover-filesettings" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
				html: true,
				trigger: 'manual'
			}).popover('toggle');
		})
		.on('hide.bs.popover', '[id^="file_"][id*="_settings_"]', function() {
			const popoverContent = $(this).data('bs.popover').$tip.find('.popover-content div[id*="_watermark_"], .popover-content [id*="_watermark_small_"]');
			if (popoverContent.length) {
				$(this).after(popoverContent.hide());
			}
			$(this).find("i.fa").toggleClass("fa-times fa-cog");
		})
		.on('show.bs.popover', '[id^="file_"][id*="_settings_"]', function() {
			$(this).find("i.fa").toggleClass("fa-times fa-cog");
		})
		.on('shown.bs.tab', 'a[data-toggle="tab"]', function() {
			if (typeof prepareKanbanBoards === 'function') prepareKanbanBoards();
		})
		.on('touchend', '.page-sidebar.menu-compact .sidebar-menu .submenu > li', function() {
			$(this).find('a').click();
		})
		.on('shown.bs.dropdown', '.admin-table td div', function() {
			$(this).closest('td').css('overflow', 'visible');
		})
		.on('hidden.bs.dropdown', '.admin-table td div', function() {
			$(this).closest('td').css('overflow', 'hidden');
		})
		.on('click', '.form-element.dropdown-menu li', function() {
			$._changeDropdown($(this));
		})
		.on("keyup", ".bootbox.modal", function(event) {
			if (event.which === 13 && $(this).find(event.target).filter('input:not([id*="filer_field"])').length) {
				$(this).find('[data-bb-handler="success"]').click();
			}
		})
		.on("click", "#filter-visibility-switch", function() {
			$(".filter-form").slideToggle(500);
		})
		.on("click", '.context-menu a', function(event) {
			$(this).parents('.context-menu').hide();
			event.preventDefault();
		})
		.on("click", function(event) {
			if (!$(event.target).parents('.fc-body').length) {
				$('.context-menu').hide();
			}
			if (!$(event.target).parents('.event-checklist-item-row').length) {
				$('.event-checklist-item-panel').remove();
			}
		})
		.on('keyup', function(event) {
			if (event.keyCode === 27) {
				$('.context-menu').hide();
			}
		})
		.on('click', '.th-width-toggle', function() {
			const $i = $(this);
			const $th = $i.parent();
			const $tr = $th.parent();
			const columnNumber = $tr.children('th').index($th) + 1;

			let $longestTd = null;
			$tr.closest('table').find('tr td:nth-child(' + columnNumber + ')').each(function() {
				if (!$longestTd || $(this).text().length > $longestTd.text().length) {
					$longestTd = $(this);
				}
			});

			if ($longestTd) {
				const $cloneTd = $longestTd.clone()
					.removeClass()
					.css({ display: 'inline', width: 'auto', visibility: 'hidden' })
					.appendTo('body');

				const longestTdouterWidth = $longestTd.outerWidth();
				let longestWidth = $cloneTd.width() + longestTdouterWidth - $longestTd.width() + 5;

				if (longestWidth < longestTdouterWidth) longestWidth = longestTdouterWidth + 20;
				$cloneTd.remove();

				if (longestWidth < 50) longestWidth = 50;
				else if (longestWidth > 250) longestWidth = 250;

				if ($i.hasClass('fa-expand')) {
					$th.data('wide', longestWidth);
				} else {
					$th.removeData('wide');
				}

				if (typeof setCursorAdminTableWrap === 'function') setCursorAdminTableWrap();
				if (typeof setResizableAdminTableTh === 'function') setResizableAdminTableTh();
			}
		})
		.on('mouseover', '.admin-table-wrap:not(.table-draggable)', function() {
			if (!$(this).data('curDown') && typeof setCursorAdminTableWrap === 'function') {
				setCursorAdminTableWrap();
			}
		})
		.on('mouseout', '.admin-table-wrap.table-draggable', function(event) {
			if (!($(this).find(event.relatedTarget).length || $(this).data('curDown')) && typeof setCursorAdminTableWrap === 'function') {
				setCursorAdminTableWrap();
			}
		})
		.on('mousedown', '.admin-table-wrap.table-draggable', function(event) {
			const tagName = event.target.tagName;
			if (!['INPUT', 'SELECT', 'TEXTAREA', 'SPAN', 'A'].includes(tagName)) {
				$(this).addClass('mousedown').data({
					'curDown': true,
					'curYPos': event.pageY,
					'curXPos': event.pageX,
					'curScrollLeft': $(this).scrollLeft()
				});
				event.preventDefault();
			}
		})
		.on('mouseup', '.admin-table-wrap.table-draggable.mousedown', function(event) {
			const tagName = event.target.tagName;
			if (!['INPUT', 'SELECT'].includes(tagName)) {
				$(this).data({ 'curDown': false }).removeClass('mousedown');
			}
		})
		// Optimized mousemove using requestAnimationFrame
		.on('mousemove', '.admin-table-wrap.table-draggable.mousedown', function(event) {
			const $this = $(this);
			if ($this.data('curDown')) {
				requestAnimationFrame(() => {
					const scrollLeft = parseInt($this.data('curScrollLeft') + $this.data('curXPos') - event.pageX);
					$this.scrollLeft(scrollLeft);

					// Update only if scroll changed
					if ($this.scrollLeft() !== scrollLeft) { // Logic check: actually this condition might need review based on original logic intent, but seems like a limit check
						// $this.data({
						//    'curXPos': event.pageX,
						//    'curScrollLeft': $this.scrollLeft()
						// });
					}
				});
			}
		})
		.on('shown.bs.dropdown', '.table-scrollable', function(event) {
			const dropdownToggle = $(event.target).find('[data-toggle="dropdown"][aria-expanded="true"]');
			const dropdownMenu = dropdownToggle.nextAll('.dropdown-menu');
			dropdownMenu2 = $(event.target).find('.dropdown-menu');

			$('body').append(dropdownMenu2.detach());
			const eOffset = $(event.target).offset();

			dropdownMenu2.css({
				'display': 'block',
				'top': $(event.target).hasClass('dropup') ? eOffset.top - dropdownMenu.outerHeight(true) - 2 : eOffset.top + $(event.target).outerHeight(),
				'left': eOffset.left - dropdownMenu2.outerWidth() + $(event.target).outerWidth(),
				'width': dropdownMenu2.outerWidth()
			});
		})
		.on('hide.bs.dropdown', '.table-scrollable', function(event) {
			if (typeof dropdownMenu2 !== 'undefined') {
				$(event.target).append(dropdownMenu2.removeAttr('style').detach());
			}
		})
		.on('click', '.page-selector-show-button', function() {
			$(this).addClass('hide')
				.next('.page-selector').removeClass('hide')
				.find('input').focus()
				.parents('.page-selector').find('a')
				.data('lastPageNumber', +$(this).parents('.pagination').find('.next').prev().find('a').text());
		})
		.on('mousedown', '.page-selector a', function() {
			const $this = $(this);
			let newPageNumber = +$this.parents('.page-selector').find('input').val();
			const currentPageNumber = +$this.parents('.pagination').find('.active a').text();
			let sOnclick, sHref;

			if (!newPageNumber || currentPageNumber === newPageNumber) {
				sOnclick = '';
				sHref = 'javascript:void(0)';
			} else {
				const lastPage = $this.data('lastPageNumber');
				if (newPageNumber < 1) newPageNumber = 1;
				else if (newPageNumber > lastPage) newPageNumber = lastPage;

				sOnclick = $this.data('onclick') || $this.attr('onclick');
				sHref = $this.data('href') || $this.attr('href');

				if (sOnclick) sOnclick = sOnclick.replace(/current:\s*'\d+'/, "current:'" + newPageNumber + "'");
				if (sHref) sHref = sHref.replace(/hostcms\[current\]=\d+/, "hostcms[current]=" + newPageNumber);
			}

			if (!$this.data('onclick')) {
				$this.data({ 'onclick': $this.attr('onclick'), 'href': $this.attr('href') });
			}
			$this.attr({ 'onclick': sOnclick, 'href': sHref });
		})
		.on('keyup', '.page-selector input', function(event) {
			if (event.keyCode === 13) {
				$(this).parent('.page-selector').find('a').mousedown().click();
			}
		})
		.on('click', 'input[type="checkbox"][name$="_public[]"]', function() {
			const $this = $(this);
			$this.closest('.row').find('input[type="hidden"]').val(+$this.prop('checked'));
		})
		.on('show.bs.dropdown', function(e) {
			const $this = $(e.target);
			if ($this.has('ul[data-change-context]').length) {
				const rect = $this.get(0).getBoundingClientRect();
				const offset = $this.offset();

				$this.after($('<div id="tmp-dropdown-div"></div>').css({
					display: 'inline-block',
					height: rect.height,
					width: rect.width,
					margin: $this.css('margin'),
					'vertical-align': 'middle'
				}));

				$('body').append($this.css({
					position: 'absolute',
					left: offset.left,
					top: offset.top,
					'z-index': 9999
				}));
			}
		})
		.on('shown.bs.dropdown', '.account-area li', function() {
			const $this = $(this);
			const dropdownMenu = $this.children('.dropdown-menu');
			const delta = $this.offset().left === 0 ? 0 : window.screen.width - $this.offset().left - dropdownMenu.outerWidth(true);

			if (delta <= 0) {
				dropdownMenu.css({ left: delta, right: 'auto' }).data('changePosition', true);
			}
		})
		.on('hidden.bs.dropdown', '.account-area li', function() {
			const dropdownMenu = $(this).children('.dropdown-menu');
			if (dropdownMenu.data('changePosition')) {
				dropdownMenu.css({ left: '', right: '' });
			}
		})
		.on('hide.bs.dropdown', function(e) {
			const $this = $(e.target);
			if ($this.has('ul[data-change-context]').length) {
				$('#tmp-dropdown-div').after($this.css({
					position: '',
					left: '',
					top: '',
					width: '',
					'z-index': ''
				})).remove();
			}
		})
		.on('touchend', '#leftNavbarArrow', function(event, withoutAnimation) {
			event.preventDefault();
			const navbarAccount = $('.navbar .navbar-inner .navbar-header .navbar-account');
			const accountArea = $('.navbar .navbar-inner .navbar-header .account-area');
			const accountAreaLi = accountArea.children('li:not(:hidden)');
			const countOffset = $(this).data('countElementsOffset');
			const accountAreaRightLi = accountAreaLi.filter(`:gt(${accountAreaLi.length - countOffset - 1})`);

			navbarAccount.data('animationProcess', true);

			accountAreaRightLi.animate({ width: 'hide' }, {
				duration: withoutAnimation ? 0 : 200,
				specialEasing: { width: 'linear' },
				complete: function() {
					$(this).addClass('hide');
					if (this === accountAreaRightLi.get(accountAreaRightLi.length - 1)) {
						if (!navbarAccount.find('#rightNavbarArrow').length) {
							navbarAccount.append('<div id="rightNavbarArrow"><a href="#"><i class="icon fa fa-chevron-right"></i></a></div>');
						}
						const rightNavbarArrow = navbarAccount.find('#rightNavbarArrow');
						rightNavbarArrow.removeClass('hide');
						navbarAccount.data('animationProcess', false);
					}
				}
			}).prev().eq(0).addClass('invisible');

			accountAreaLi.filter('.invisible').removeClass('invisible');
			$(this).addClass('hide');
		})
		.on('touchend', '#rightNavbarArrow', function(event) {
			event.preventDefault();
			const accountArea = $('.navbar .navbar-inner .navbar-header .account-area');
			const accountAreaHiddenLi = accountArea.find('.hide');

			accountArea.find('.invisible').removeClass('invisible');
			accountAreaHiddenLi.removeClass('hide');

			accountAreaHiddenLi.animate({ width: 'show' }, {
				duration: 200,
				specialEasing: { width: 'linear' },
				complete: function() {
					if (this === accountAreaHiddenLi.get(accountAreaHiddenLi.length - 1)) {
						if (typeof navbarHeaderCustomization === 'function') navbarHeaderCustomization();
					}
				}
			});

			$(this).addClass('hide');
		});

	// Sticky actions scroll
	$(document).on("scroll", function() {
		const docHeight = $(document).height();
		const winHeight = $(window).height();
		const scrollTop = $(window).scrollTop();
		const $formButtons = $('.formButtons');

		// Throttling not strictly needed for this simple class toggle, but good practice
		// Using requestAnimationFrame for visual updates
		requestAnimationFrame(() => {
			if (scrollTop + winHeight === docHeight) {
				$formButtons.removeClass('sticky-actions');
			} else if (scrollTop + winHeight < docHeight) {
				$formButtons.addClass('sticky-actions');
			}
		});
	});

	$("#sidebar-collapse").on('click', function() {
		if ($('.navbar').hasClass('navbar-fixed-top') && typeof navbarHeaderCustomization === 'function') navbarHeaderCustomization();
		if (typeof setResizableAdminTableTh === 'function') setResizableAdminTableTh();
		if (typeof prepareKanbanBoards === 'function') prepareKanbanBoards();
	});

	$(".page-content").on('click', '.sidebar-toggler', function() {
		if ($('.navbar').hasClass('navbar-fixed-top') && typeof navbarHeaderCustomization === 'function') navbarHeaderCustomization();
		if (typeof setResizableAdminTableTh === 'function') setResizableAdminTableTh();
		if (typeof changeDublicateTables === 'function') changeDublicateTables();
		if (typeof prepareKanbanBoards === 'function') prepareKanbanBoards();
	});
});