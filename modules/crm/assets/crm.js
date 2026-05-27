/* global hostcmsBackend i18n uuidv4 */
(function($) {
	"use strict";

	$.extend({
		showCrmIcons: function(object, selector) {
			$.loadingScreen('show');
			var color = $(object).css('background-color');

			$.ajax({
				url: hostcmsBackend + '/crm/project/index.php',
				data: {
					'showCrmIconsModal': 1,
					'color': color,
					'selector': selector
				},
				dataType: 'json',
				type: 'POST',
				success: function(response) {
					$.loadingScreen('hide');
					$('body').append(response.html);
					var $modal = $('#crmProjectIconsModal');
					$modal.modal('show');
					$modal.on('hidden.bs.modal', function() {
						$(this).remove();
					});
				}
			});
		},

		selectCrmIcon: function(object, selector) {
			var $object = $(object),
				value = $object.data('value'),
				id = $object.data('id') || 0,
				$modal = $('#crmProjectIconsModal'),
				$inputSelector = $('input[name = crm_icon_id]');

			$inputSelector.val(id);

			var $specificInput = $('.input-' + selector);
			if ($specificInput.length) {
				$specificInput.val(value);
			}

			var $iconContainer = $('.' + selector);
			if (!$iconContainer.length) {
				$iconContainer = $('.crm-project-icon');
			}
			$iconContainer.find('i').attr('class', value);

			$modal.modal('hide');
		},

		dealChangeProfit: function(object, windowId) {
			var $window = $('#' + windowId),
				amount = parseFloat($window.find('input[name=amount]').val()) || 0,
				expenditure = parseFloat($window.find('input[name=expenditure]').val()) || 0,
				$profit = $window.find('#profit'),
				value = amount - expenditure;

			$profit.val(value);

			if (value > 0) {
				$profit.removeClass('darkorange');
			} else {
				$profit.addClass('darkorange');
			}
		},

		showCrmNoteAttachment: function(object, model) {
			var $object = $(object),
				id = $object.data(model + '-id'),
				crm_note_attachment_id = $object.data('id');

			$.ajax({
				url: hostcmsBackend + '/crm/note/index.php',
				data: {
					'showCrmNoteAttachment': 1,
					'crm_note_attachment_id': crm_note_attachment_id,
					'params': model + '_id=' + id
				},
				dataType: 'json',
				type: 'POST',
				success: function(response) {
					$('body').append(response.html);
					var $modal = $('#crmNoteAttachmentModal' + crm_note_attachment_id);
					$modal.modal('show');
					$modal.on('hidden.bs.modal', function() {
						$(this).remove();
					});
				}
			});
		},

		showDropzone: function(object, windowId) {
			$('#' + windowId + ' .crm-note-attachments-dropzone').toggleClass('hidden');
		},

		showFastDealForm: function(deal_template_id) {
			$('.fast-add-template' + deal_template_id).addClass('hidden');
			$('.fast-add-form-template' + deal_template_id).removeClass('hidden');
		},

		cancelFastDealForm: function(deal_template_id) {
			$('.fast-add-template' + deal_template_id).removeClass('hidden');
			$('.fast-add-form-template' + deal_template_id).addClass('hidden');
		},

		showCounterparty: function(object) {
			var $object = $(object),
				$prev = $object.prev(),
				$parent = $object.parents('.counterparty-block');

			$parent.find('li.hidden').removeClass('hidden');
			$prev.addClass('showed');
			$object.remove();
		},

		showKanbanCounterparty: function(object) {
			var $object = $(object),
				$parent = $object.parents('.row');

			$parent.find('.deal-client-row.hidden').removeClass('hidden');
			$object.remove();
		},

		showAllDescription: function(object) {
			var $object = $(object),
				$parent = $object.parents('.crm-description-wrapper');

			$parent.find('.crm-description.expand').removeClass('expand');
			$object.remove();
		},

		leadStatusBar: function(lead_id, windowId) {
			var wrapperSelector = ".lead-stage-wrapper.lead-stage-wrapper-" + lead_id;

			$(wrapperSelector + " .lead-stage").on("click", function() {
				var $this = $(this);
				if (!$this.hasClass('finish')) {
					var $stages = $(wrapperSelector + " .lead-stage");

					$stages.removeClass("active previous").css({
						'background-color': '',
						'border-color': ''
					});

					$this.addClass("active");
					$this.prevUntil(wrapperSelector).addClass("previous");

					var color = $this.css('background-color'),
						darkerColor = $this.data('dark');

					$(wrapperSelector + " .lead-stage.previous").css({
						'background-color': color,
						'border-color': darkerColor
					});

					$(".lead-status-name.lead-status-name-" + lead_id)
						.text($this.data('name'))
						.css('color', $this.data('color'));

					// Отключаем клик, если провальный этап
					if ($(wrapperSelector).find('.lead-stage.active.failed').length) {
						$stages.each(function() {
							$(this).off('click').css('cursor', 'default');
						});
					}
				}

				var lead_status_id = $this.data('id'),
					id = 'hostcms[checked][0][' + lead_id + ']',
					post = {},
					operation = '';

				post['last_step'] = 0;

				if ($this.hasClass('finish')) {
					operation = 'finish';
					post['last_step'] = 1;
				}

				post[id] = 1;
				post['lead_status_id'] = lead_status_id;

				$.adminLoad({
					path: hostcmsBackend + '/lead/index.php',
					action: 'morphLead',
					operation: operation,
					post: post,
					additionalParams: '',
					windowId: windowId
				});
			});

			// Инициализация активного этапа
			var $activeLi = $(wrapperSelector + " .lead-stage.active");
			if ($activeLi.length) {
				var activeColor = $activeLi.css('background-color'),
					activeDarkerColor = $activeLi.data('dark');

				$activeLi.prevUntil(wrapperSelector).addClass("previous");

				$(wrapperSelector + " .lead-stage.previous").css({
					'background-color': activeColor,
					'border-color': activeDarkerColor
				});

				if ($(wrapperSelector).find('.lead-stage.active.finish, .lead-stage.active.failed').length) {
					$(wrapperSelector + ' .lead-stage').each(function() {
						$(this).off('click').css('cursor', 'default');
					});
				}
			}
		},

		morphLeadChangeType: function(object) {
			var $object = $(object),
				$row = $object.parents('.row');

			$('.lead-exist-client, .lead-deal-template').addClass('hidden');

			// Существующий клиент
			if ($object.val() == 2) {
				$row.find('.lead-exist-client').removeClass('hidden');
				$object.parents('.bootbox.modal').removeAttr('tabindex');
			}

			if ($object.val() == 4) {
				$row.find('.lead-deal-template').removeClass('hidden');
			}
		},

		toggleRepresentativeFields: function(selector) {
			$(selector + ' .hidden-field').toggleClass('hidden');
			$(selector + ' .representative-show-link').parentsUntil('.row').remove();
		},

		showEmails: function(data) {
			$.ajax({
				url: hostcmsBackend + '/printlayout/index.php',
				type: 'POST',
				data: {
					'showEmails': 1,
					'representative': data.id
				},
				dataType: 'json',
				error: function() {},
				success: function(answer) {
					var $select = $(".email-select");
					$select.empty();

					if (answer) {
						var fragment = document.createDocumentFragment();
						$.each(answer, function(id, object) {
							var text = object.email;
							if (object.type !== null) {
								text += ' [' + object.type + ']';
							}
							var newOption = new Option(text, object.email, true, true);
							$select.append(newOption);
						});
						$select.trigger('change');
					}
				}
			});
		},

		templateResultItemResponsibleEmployees: function(data, item) {
			// Инициализируем переменные по умолчанию
			var fio = data.text,
				posts = '',
				avatar = '',
				className;

			// Пытаемся получить данные из JSON (data-info)
			if (data.element) {
				var info = $(data.element).data('info');
				if (info) {
					// Если это строка, парсим в объект
					if (typeof info === 'string') {
						try { info = JSON.parse(info); } catch(e) { info = {}; }
					}
					fio = info.fio || data.text;
					posts = info.posts || '';
					avatar = info.avatar || '';
				}
			}

			if (data.id) {
				var regExp = /select2-([-\w]+)-result-\w+-\d+?/g,
					myArray = regExp.exec(data._resultId);

				if (myArray) {
					var templateResultOptions = $(data.element).closest("#" + myArray[1]).data("templateResultOptions");

					if (templateResultOptions && ~templateResultOptions.excludedItems.indexOf(+data.id)) {
						item.remove();
						return;
					}
				}
			}

			if (data.element) {
				var $element = $(data.element);
				className = $element.attr("class");

				if ($element.attr("style")) {
					($element.is("optgroup") || $element.is("option") && $(item).hasClass("select2-results__option")) && $(item).attr("style", $element.attr("style"));
				}
			}

			var resultHtml = '<span class="' + (className || '') + '">' + $.escapeHtml(fio) + '</span>';

			if (posts) {
				resultHtml += '<span class="user-post">' + $.escapeHtml(posts) + '</span>';
			}

			if (avatar) {
				resultHtml = '<img src="' + $.escapeHtml(avatar) + '" height="30px" class="user-image img-circle">' + resultHtml;
			}

			return resultHtml;
		},

		templateSelectionItemResponsibleEmployees: function(data, item) {
			// Инициализируем переменные
			var fio = data.text,
				department = '',
				posts = '',
				avatar = '',
				className = data.element && $(data.element).attr("class"),
				regExp = /select2-([-\w]+)-result-\w+-\d+?/g,
				myArray = regExp.exec(data._resultId);

			// Получаем данные из JSON
			if (data.element) {
				var info = $(data.element).data('info');
				if (info) {
					if (typeof info === 'string') {
						try { info = JSON.parse(info); } catch(e) { info = {}; }
					}
					fio = info.fio || data.text;
					department = info.department || '';
					posts = info.posts || '';
					avatar = info.avatar || '';
				}
			}

			if (myArray) {
				var selectControlElement = $(data.element).closest("#" + myArray[1]),
					templateSelectionOptions = selectControlElement.data("templateSelectionOptions"),
					selectionSingle = selectControlElement.next('.select2-container').find('.select2-selection--single');

				if (selectionSingle.length) {
					selectionSingle.addClass('user-container');
				}

				if (templateSelectionOptions && ~templateSelectionOptions.unavailableItems.indexOf(+data.id)) {
					item.addClass("bordered-primary event-author")
						.find("span.select2-selection__choice__remove").remove();
				}
			}

			// Формируем HTML
			var resultHtml = '<span class="' + (className || '') + '">' + $.escapeHtml(fio) + '</span>';
			data.title = fio;

			if (department || posts) {
				resultHtml += '<br />';

				if (department) {
					resultHtml += '<span class="company-department">' + $.escapeHtml(department) + '</span>';
					data.title += " - " + department;
				}

				if (posts) {
					resultHtml += (department ? ' → ' : '') + '<span class="user-post">' + $.escapeHtml(posts) + '</span>';
					data.title += " - " + posts;
				}
			}

			resultHtml = '<div class="user-info">' + resultHtml + '</div>';

			if (avatar) {
				resultHtml = '<img src="' + $.escapeHtml(avatar) + '" height="30px" class="user-image pull-left img-circle">' + resultHtml;
			}

			return resultHtml;
		},

		templateResultItemSiteusers: function(data) {
			if (!data.text) return '';

			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class");

			if (typeof className == 'undefined') className = '';

			var resultHtml = '<div class="user-name ' + className + '">' + $.escapeHtml(arraySelectItemParts[0]) + '</div>';

			resultHtml += '<div class="user-post">' + (typeof data.tin != 'undefined' ? $.escapeHtml(data.tin) : '') + (typeof data.login != 'undefined' ? $.escapeHtml(data.login) : '') + '</div>';
			resultHtml = '<div class="user-info">' + resultHtml + '</div>';

			if (arraySelectItemParts[1]) {
				resultHtml = '<img src="' + $.escapeHtml(arraySelectItemParts[1]) + '" height="30px" class="user-image pull-left img-circle">' + resultHtml;
			}

			return resultHtml;
		},

		templateSelectionItemSiteusers: function(data) {
			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class");

			if (typeof className == 'undefined') className = '';

			var resultHtml = '<div class="user-name ' + className + '">' + $.escapeHtml(arraySelectItemParts[0]) + '</div>';

			data.login = $.escapeHtml(arraySelectItemParts[2]);
			data.tin = $.escapeHtml(arraySelectItemParts[3]);

			resultHtml += '<div class="user-post">' + (typeof data.tin != 'undefined' ? $.escapeHtml(data.tin) : '') + (typeof data.login != 'undefined' ? $.escapeHtml(data.login) : '') + '</div>';

			data.title = $.escapeHtml(arraySelectItemParts[0]);
			resultHtml = '<div class="user-info">' + resultHtml + '</div>';

			if (arraySelectItemParts[1]) {
				resultHtml = '<img src="' + $.escapeHtml(arraySelectItemParts[1]) + '" height="30px" class="user-image pull-left img-circle">' + resultHtml;
			}

			return resultHtml;
		},

		joinUser2DealStep: function(settings) {
			settings = $.extend({
				join_user: 1
			}, settings);

			var oButton = $('.join-user a');

			$('i', oButton)
				.removeClass('fa-check fa-xmark')
				.addClass('fa-spinner fa-spin');

			$.ajax({
				url: hostcmsBackend + '/deal/index.php',
				type: "POST",
				dataType: 'json',
				data: settings,
				success: function(result) {
					var buttonIcoClass;

					var dealTemplateStepId = $('#deal-steps .steps').data('template-step-id'),
						$currentStepLi = $('#deal-steps #simplewizardstep' + dealTemplateStepId + ' .step');

					var cssObj = {};

					if (result['success']) {
						buttonIcoClass = 'fa-xmark';
						oButton.addClass('btn-deal-refuse').removeAttr('style');

						cssObj = {
							'color': $currentStepLi.data('refuse-color'),
							'background-color': $currentStepLi.data('refuse-bg-color'),
							'border-color': $currentStepLi.data('refuse-border-color'),
							'border-radius': '15px'
						};
					} else {
						buttonIcoClass = 'fa-check';

						cssObj = {
							'color': $currentStepLi.data('color'),
							'background-color': $currentStepLi.data('bg-color'),
							'border-color': $currentStepLi.data('border-color'),
							'border-radius': '15px'
						};

						oButton.removeClass('btn-darkorange btn-deal-refuse');
					}

					oButton.css(cssObj);
					$('span', oButton).text(result['name']);
					$('i', oButton).removeClass('fa-spinner fa-spin').addClass(buttonIcoClass);

					$.loadDealStepUsers(result.deal_step_id, settings.windowId);
				}
			});
		},

		loadDealStepUsers: function(deal_step_id, windowId) {
			$.ajax({
				url: hostcmsBackend + '/deal/index.php',
				type: "POST",
				dataType: 'json',
				data: {
					'load_deal_step_users': 1,
					'deal_step_id': deal_step_id
				},
				success: function(result) {
					var $list = $('.deal-step-users-list');
					$list.html('');

					if (result['users']) {
						var html = '<div class="row profile-container">' +
							'<div class="col-xs-12"><h6 class="row-title before-azure no-margin-top">' + $.escapeHtml(result['title']) + '</div>' +
							'</div>' +
							'<div class="row">';

						var col = 4;
						if (result['users'].length < 4) {
							col = 12 / result['users'].length;
						}

						$.each(result['users'], function(i, oUser) {
							html += '<div class="col-xs-12 col-sm-' + col + '">' +
								'<div class="databox databox-graded" style="overflow: hidden;">' +
								'<div class="databox-left no-padding">' +
								'<img class="databox-user-avatar" src="' + $.escapeHtml(oUser['avatar']) + '">' +
								'</div>' +
								'<div class="databox-right">' +
								'<div class="orange radius-bordered" style="right: 0; left: 7px">' +
								'<div class="databox-text black semi-bold"><a data-popover="hover" data-user-id="' + oUser['id'] + '" class="black" href="' + hostcmsBackend + '/user/index.php?hostcms[action]=view&hostcms[checked][0][' + oUser['id'] + ']=1" onclick="$.modalLoad({path: \'' + hostcmsBackend + '/user/index.php\', action: \'view\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' + oUser['id'] + ']=1\', windowId: \'id_content\'}); return false">' + $.escapeHtml(oUser['name']) + '</a></div>' +
								'<div class="databox-text darkgray">' + $.escapeHtml(oUser['post']) + '</div>' +
								'</div>' +
								'</div>' +
								'</div>' +
								'</div>';
						});

						html += '</div>';
						$list.append(html).removeClass('hidden');

						$('#' + windowId + ' .deal-step-users-list [data-popover="hover"]').showUserPopover(windowId);
					}
				}
			});
		},

		dealAddUserBlock: function(object, windowId) {
			var id = $.escapeHtml(String(object.id).split('_').pop()),
				name = object.type == 'company' ?
				object.name :
				object.surname + ' ' + object.name + ' ' + object.patronymic,
				dataset = 0,
				avatar = $.escapeHtml(object.avatar),
				phone = $.escapeHtml(object.phone),
				email = $.escapeHtml(object.email),
				safeName = $.escapeHtml(name),
				type = $.escapeHtml(object.type);

			var containerClass = '.deal-users-row';
			var blockClass = 'user-block-' + type + id;

			if (!$('#' + windowId + ' ' + containerClass + ' .' + blockClass).length) {
				var emailHtml = email.length ?
					'<a href="mailto:' + email + '">' + email + '</a>' :
					'';

				var html = '<div class="col-xs-12 col-sm-6 user-block ' + blockClass + '">' +
					'<div class="databox">' +
					'<div class="databox-left no-padding">' +
					'<div class="img-wrapper">' +
					'<img class="databox-user-avatar" src="' + avatar + '"/>' +
					'<a href="' + hostcmsBackend + '/siteuser/representative/index.php?hostcms[action]=view&hostcms[checked][' + dataset + '][' + id + ']=1&show=' + type + '" onclick=\'$.modalLoad({path: "' + hostcmsBackend + '/siteuser/representative/index.php", action: "view", operation: "modal", additionalParams: "hostcms[checked][' + dataset + '][' + id + ']=1&show=' + type + '", windowId: "id_content"}); return false\'>' +
					'</a>' +
					'</div>' +
					'</div>' +
					'<div class="databox-right">' +
					'<div class="databox-text">' +
					'<div class="semi-bold">' + safeName + '</div>' +
					'<div class="darkgray">' + phone + '</div>' +
					'<div>' + emailHtml + '</div>' +
					'</div>' +
					'<div class="delete-responsible-user" onclick="$.dealRemoveUserBlock($(this))">' +
					'<i class="fa-regular fa-circle-xmark"></i>' +
					'</div>' +
					'</div>' +
					'</div>' +
					'<input type="hidden" name="deal_siteusers[]" value="' + object.type + '_' + object.object_id + '"/>' +
					'</div>';

				$('#' + windowId + ' ' + containerClass).append(html);
			}
		},

		dealRemoveUserBlock: function(object) {
			if (confirm(i18n['confirm_delete'])) {
				object.parents('.user-block').remove();
			}
		},

		fillSiteuserCompanyContract: function(windowId, siteuserCompanyContractId, siteuserCompanyName, siteuserCompanyContractName) {
			siteuserCompanyName = siteuserCompanyName || 'siteuser_company_id';
			siteuserCompanyContractName = siteuserCompanyContractName || 'siteuser_company_contract_id';

			var companyId = parseInt($("#" + windowId + " #company_id").val()) || 0,
				oSiteuserCompany = $("#" + windowId + " [name=" + siteuserCompanyName + "]"),
				siteuserCompanyId = oSiteuserCompany.val();

			siteuserCompanyId = siteuserCompanyId ? parseInt(siteuserCompanyId.split("_")[1]) : 0;

			if (companyId && siteuserCompanyId) {
				$.ajax({
					url: hostcmsBackend + '/siteuser/company/contract/index.php?getSiteuserCompanyContracts',
					dataType: 'json',
					data: {
						companyId: companyId,
						siteuserCompanyId: siteuserCompanyId
					},
					success: function(data) {
						var oSiteuserCompanyContract = $("#" + windowId + " #" + siteuserCompanyContractName);
						oSiteuserCompanyContract.empty();

						if (data.contracts && data.contracts.length) {
							var fragment = document.createDocumentFragment();
							var countContracts = data.contracts.length;

							for (var i = 0; i < countContracts; i++) {
								var option = document.createElement('option');
								option.value = data.contracts[i]["id"];
								option.text = data.contracts[i]["name"];
								if (siteuserCompanyContractId == data.contracts[i]["id"]) {
									option.selected = true;
								}
								fragment.appendChild(option);
							}
							oSiteuserCompanyContract.append(fragment);
						}
					}
				});
			}
		},

		addEventChecklist: function(windowId, container) {
			var $wells = $('#' + windowId + ' .event-checklist-wrapper > .well'),
				indexLength = $wells.length,
				dataIndex = $wells.last().data('index');

			var index = indexLength > 0 ? dataIndex + 1 : 0;

			$.loadingScreen('show');

			$.ajax({
				url: hostcmsBackend + '/event/index.php',
				type: "POST",
				data: {
					'add_checklist': 1,
					'index': index
				},
				dataType: 'json',
				error: function() {},
				success: function(result) {
					$.loadingScreen('hide');
					$(container).append(result.html);
					var $newWell = $('#' + windowId + ' .event-checklist-wrapper > .well').last();
					$newWell.find('a.add-checklist-item').click();
					$(container).find('input[name *= new_checklist_item_name' + index + ']').eq(0).focus();
				}
			});
		},

		removeEventChecklist: function($object) {
			if (confirm(i18n.confirm_delete)) {
				$.loadingScreen('show');
				$object.parents('.well').remove();
				$.loadingScreen('hide');
			}
		},

		loadEventChecklists: function(windowId, container, event_id) {
			$.loadingScreen('show');

			$.ajax({
				url: hostcmsBackend + '/event/index.php',
				type: "POST",
				data: {
					'load_checklists': 1,
					'event_id': event_id
				},
				dataType: 'json',
				error: function() {},
				success: function(result) {
					$.loadingScreen('hide');
					var $container = $(container);
					$container.find('.well').remove();
					$container.append(result.html);
				}
			});
		},

		recountEventChecklistProgress: function($object) {
			var $wrapper = $object.parents('.event-cheklist-items-wrapper'),
				$progressbar = $wrapper.find('.progress-bar'),
				$checkboxes = $wrapper.find('.form-group .checkbox-inline input:visible'),
				total = $checkboxes.length,
				completed = $checkboxes.filter(':checked').length;

			var width = total > 0 ? parseFloat((completed * 100) / total).toFixed(2) : 0;

			$progressbar.css('width', width + '%').attr('aria-valuenow', width);
			$wrapper.find('.progress-completed').text(completed);
			$wrapper.find('.progress-total').text(total);
		},

		addEventChecklistItem: function($object, windowId, prefix, index) {
			$.loadingScreen('show');

			var $wrapper = $object.parents('.event-cheklist-items-wrapper'),
				$row = $wrapper.find('.row').eq(0),
				$cloneRow = $row.clone(),
				indexLength = $wrapper.find('.row').length,
				dataIndex = $wrapper.find('.row').last().data('index');

			var newIndex = indexLength > 1 ? dataIndex + 1 : 0;

			$cloneRow.attr('data-index', newIndex);

			var nameBase = prefix + '_item_name' + index + '[' + newIndex + ']',
				completedBase = prefix + '_item_completed' + index + '[' + newIndex + ']',
				importantBase = prefix + '_item_important' + index + '[' + newIndex + ']';

			$cloneRow.find('input[name *= ' + prefix + '_item_completed' + index + ']')
				.removeAttr('disabled').attr('name', completedBase)
				.parents('.form-group').removeClass('hidden');

			$cloneRow.find('input[name *= ' + prefix + '_item_important' + index + ']')
				.removeAttr('disabled').attr('name', importantBase);

			$cloneRow.find('input[name *= ' + prefix + '_item_name' + index + ']')
				.removeAttr('disabled').attr('name', nameBase)
				.parents('.form-group').removeClass('hidden');

			$cloneRow.find('.remove-event-checklist-item').removeClass('hidden');
			$cloneRow.find('.event-checklist-item-important').removeClass('hidden');

			$cloneRow.insertBefore($wrapper.find('.justify-content-between'));

			$wrapper.find('input[name="' + nameBase + '"]').last().focus();

			$.recountEventChecklistProgress($wrapper.find('input[name="' + completedBase + '"]').last());

			$.loadingScreen('hide');
		},

		changeEventItemImportant: function($object) {
			var $parent = $object.parents('.event-checklist-item-row');
			$object.toggleClass('selected');
			$parent.find('.event-checklist-important .checkbox-inline input').prop('checked', $object.hasClass('selected'));
		},

		removeEventChecklistItem: function($object) {
			if (confirm(i18n.confirm_delete)) {
				$.loadingScreen('show');
				var $wrapper = $object.parents('.event-cheklist-items-wrapper');
				$object.parents('.event-checklist-item-row').remove();
				$.recountEventChecklistProgress($wrapper.find('.checkbox-inline input').last());
				$.loadingScreen('hide');
			}
		},

		companyChangeFilterFieldWindowId: function(newFilterFieldWindowId) {
			if (newFilterFieldWindowId) {
				$('input[id ^= "filter_field_id_"]').each(function() {
					var onKeyupText = $(this).attr('onkeyup');
					if (onKeyupText) {
						var pos = onKeyupText.indexOf('oSelectFilter') + 'oSelectFilter'.length,
							suffix = onKeyupText.substr(pos, 1),
							index = 'oSelectFilter' + suffix;

						if (window[index]) {
							window[index].windowId = newFilterFieldWindowId;
						}
					}
				});
			}
		}
	});

	$.fn.extend({
		selectUser: function(settings) {
			settings = $.extend({
				allowClear: true,
				templateResult: $.templateResultItemResponsibleEmployees,
				escapeMarkup: function(m) {
					return m;
				},
				templateSelection: $.templateSelectionItemResponsibleEmployees,
				width: "100%",
				matcher: function(params, data) {
					if ($.trim(params.term) === '') {
						return data;
					}

					if (typeof data.text === 'undefined') {
						return null;
					}

					var term = params.term.toLowerCase();

					// Функция-помощник для проверки конкретного элемента
					function isMatch(element) {
						// Если это optgroup, у него нет data-info, ищем по названию отдела
						if (!element || !$(element).data('info')) {
							return data.text.toLowerCase().indexOf(term) > -1;
						}

						// Получаем распарсенный JSON-объект
						var info = $(element).data('info');

						// На случай, если jQuery не распарсил автоматически (зависит от версии)
						if (typeof info === 'string') {
							try { info = JSON.parse(info); } catch (e) { info = {}; }
						}

						// Склеиваем нужные поля для поиска (ФИО, отдел, должность, логин)
						var searchableText = [
							info.fio || '',
							info.department || '',
							info.posts || '',
							info.login || ''
						].join(' ').toLowerCase();

						return searchableText.indexOf(term) > -1;
					}

					// 1. Проверяем саму группу или пользователя
					if (isMatch(data.element)) {
						return data;
					}

					// 2. Проверяем дочерние элементы (если текущий элемент - optgroup)
					if (data.children && data.children.length > 0) {
						var match = $.extend(true, {}, data);
						var matchedChildren = [];

						for (var c = 0; c < data.children.length; c++) {
							var child = data.children[c];

							if (isMatch(child.element)) {
								matchedChildren.push(child);
							}
						}

						if (matchedChildren.length > 0) {
							match.children = matchedChildren;
							return match;
						}
					}

					return null;
				}
			}, settings);

			return this.each(function() {
				var $this = jQuery(this);
				if (!$this.attr('data-select2-id')) {
					$this.attr('data-select2-id', uuidv4());
				}
				$this.select2(settings);
			});
		},

		selectSiteuser: function(settings) {
			settings = $.extend({
				url: hostcmsBackend + "/siteuser/index.php?loadSiteusers&types[]=siteuser&types[]=person&types[]=company",
				minimumInputLength: 1,
				allowClear: true,
				templateResult: $.templateResultItemSiteusers,
				escapeMarkup: function(m) {
					return m;
				},
				templateSelection: $.templateSelectionItemSiteusers,
				width: "100%",
				dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : null
			}, settings);

			settings = $.extend({
				ajax: {
					url: settings.url,
					dataType: "json",
					type: "GET",
					processResults: function(data) {
						var aResults = [];
						$.each(data, function(index, item) {
							aResults.push(item);
						});
						return {
							results: aResults
						};
					}
				}
			}, settings);

			return this.each(function() {
				jQuery(this).attr('data-select2-id', uuidv4()).select2(settings);
			});
		},

		selectPersonCompany: function(settings) {
			settings = $.extend({
				url: hostcmsBackend + '/siteuser/index.php?loadSiteusers&types[]=siteuser&types[]=person&types[]=company',
				allowClear: true,
				templateResult: $.templateResultItemSiteusers,
				escapeMarkup: function(m) {
					return m;
				},
				templateSelection: $.templateSelectionItemSiteusers,
				width: "100%",
				dropdownParent: $(this).closest('.modal').length ? $(this).closest('.modal') : null
			}, settings);

			settings = $.extend({
				ajax: {
					url: settings.url,
					dataType: "json",
					type: "GET",
					processResults: function(data) {
						var aResults = [];
						$.each(data, function(index, item) {
							aResults.push(item);
						});
						return {
							results: aResults
						};
					}
				}
			}, settings);

			return this.each(function() {
				jQuery(this).attr('data-select2-id', uuidv4()).select2(settings);
			});
		},

		showUserPopover: function(windowId) {
			return this.each(function() {
				var $this = jQuery(this);

				// Очищаем предыдущие обработчики
				$this.off('mouseenter.showUserPopover mouseleave.showUserPopover');

				// Переменные для отслеживания состояния
				var hideTimer = null;
				var $currentPopover = null;
				var currentRequest = null;
				var isShown = false;
				var isMouseOverPopover = false;
				var isMouseOverTrigger = false;

				// Функция для ручного удаления поповера
				function removePopover() {
					if (hideTimer) {
						clearTimeout(hideTimer);
						hideTimer = null;
					}
					if ($currentPopover) {
						$currentPopover.remove();
						$currentPopover = null;
					}
					$this.removeAttr('aria-describedby');
					isShown = false;
					isMouseOverPopover = false;
				}

				// Функция для проверки необходимости скрытия
				function checkAndHide() {
					if (hideTimer) {
						clearTimeout(hideTimer);
						hideTimer = null;
					}

					hideTimer = setTimeout(function() {
						// Проверяем, находится ли мышь над триггером или поповером
						if (!isMouseOverTrigger && !isMouseOverPopover) {
							removePopover();
						}
						hideTimer = null;
					}, 100); // Увеличиваем задержку для удобства копирования
				}

				$this.on('mouseenter.showUserPopover', function() {
					isMouseOverTrigger = true;

					// Отменяем таймер скрытия
					if (hideTimer) {
						clearTimeout(hideTimer);
						hideTimer = null;
					}

					// Если поповер уже показан, не делаем ничего
					if (isShown || $currentPopover) {
						return;
					}

					// Если есть активный запрос, отменяем его
					if (currentRequest) {
						currentRequest.abort();
						currentRequest = null;
					}

					var userId = $this.data('user-id');
					if (!userId) {
						return;
					}

					var container = $this.data('container') || "#" + windowId;
					$this.attr('data-loading', 'true');

					currentRequest = $.ajax({
						url: hostcmsBackend + '/user/index.php',
						data: {
							showPopover: 1,
							user_id: userId
						},
						dataType: 'json',
						type: 'POST',
						success: function(response) {
							currentRequest = null;

							if (!isMouseOverTrigger) {
								$this.removeAttr('data-loading');
								return;
							}

							if (isShown || $currentPopover) {
								$this.removeAttr('data-loading');
								return;
							}

							// Удаляем старый поповер если есть
							removePopover();

							// Генерируем уникальный ID
							var popoverId = 'popover-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

							// Создаем HTML поповера с улучшенной структурой для копирования
							var popoverHtml = '<div class="popover fade top in" role="tooltip" id="' + popoverId + '" style="user-select: text; -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text;">' +
								'<div class="arrow"></div>' +
								'<h3 class="popover-title" style="display: none;"></h3>' +
								'<div class="popover-content" style="user-select: text; -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; cursor: default;">' + response.html + '</div>' +
								'</div>';

							var $container = $(container);
							if (!$container.length) {
								$container = $('body');
							}

							$container.append(popoverHtml);
							$currentPopover = $('#' + popoverId);

							// Добавляем стили для разрешения выделения текста
							$currentPopover.css({
								'user-select': 'text',
								'-webkit-user-select': 'text',
								'-moz-user-select': 'text',
								'-ms-user-select': 'text',
								'pointer-events': 'auto'
							});

							$currentPopover.find('*').css({
								'user-select': 'text',
								'-webkit-user-select': 'text',
								'-moz-user-select': 'text',
								'-ms-user-select': 'text'
							});

							$this.attr('aria-describedby', popoverId);

							// Ручное позиционирование
							var offset = $this.offset();
							var height = $this.outerHeight();
							var width = $this.outerWidth();
							var popoverHeight = $currentPopover.outerHeight();
							var popoverWidth = $currentPopover.outerWidth();

							var top = offset.top - popoverHeight - 5;
							var left = offset.left + (width / 2) - (popoverWidth / 2);

							$currentPopover.css({
								'top': top + 'px',
								'left': left + 'px',
								'display': 'block'
							});

							isShown = true;

							// Обработчики для поповера
							$currentPopover.off('mouseenter.showUserPopover mouseleave.showUserPopover');

							$currentPopover.on('mouseenter.showUserPopover', function() {
								isMouseOverPopover = true;
								if (hideTimer) {
									clearTimeout(hideTimer);
									hideTimer = null;
								}
							});

							$currentPopover.on('mouseleave.showUserPopover', function(e) {
								isMouseOverPopover = false;

								// Проверяем, не перешла ли мышь на триггер
								var relatedTarget = e.relatedTarget;
								if (relatedTarget) {
									if ($this[0] === relatedTarget || $this.has(relatedTarget).length) {
										return;
									}
								}

								checkAndHide();
							});

							$this.removeAttr('data-loading');
						},
						error: function() {
							currentRequest = null;
							$this.removeAttr('data-loading');
						}
					});
				});

				$this.on('mouseleave.showUserPopover', function(e) {
					isMouseOverTrigger = false;

					// Проверяем, не перешла ли мышь на поповер
					var relatedTarget = e.relatedTarget;
					if ($currentPopover && relatedTarget) {
						if ($currentPopover[0] === relatedTarget || $currentPopover.has(relatedTarget).length) {
							return;
						}
					}

					if ($this.attr('data-loading') === 'true') {
						if (currentRequest) {
							currentRequest.abort();
							currentRequest = null;
						}
						$this.removeAttr('data-loading');
						return;
					}

					checkAndHide();
				});

				// Глобальный обработчик кликов для скрытия при клике вне
				$(document).off('click.showUserPopoverGlobal').on('click.showUserPopoverGlobal', function(e) {
					if (isShown && $currentPopover) {
						var $target = $(e.target);
						// Если клик не по триггеру и не по поповеру
						if (!$target.closest($this).length && !$target.closest($currentPopover).length) {
							removePopover();
						}
					}
				});

				$this.on('remove.showUserPopover', function() {
					if (hideTimer) clearTimeout(hideTimer);
					if (currentRequest) currentRequest.abort();
					removePopover();
				});
			});
		},

		showSiteuserPopover: function(windowId) {
			return this.each(function() {
				var $this = jQuery(this);

				$this.off('mouseenter.showSiteuserPopover mouseleave.showSiteuserPopover');

				var hideTimer = null;
				var $currentPopover = null;
				var currentRequest = null;
				var isShown = false;
				var isMouseOverPopover = false;
				var isMouseOverTrigger = false;

				function removePopover() {
					if (hideTimer) {
						clearTimeout(hideTimer);
						hideTimer = null;
					}
					if ($currentPopover) {
						$currentPopover.remove();
						$currentPopover = null;
					}
					$this.removeAttr('aria-describedby');
					isShown = false;
					isMouseOverPopover = false;
				}

				function checkAndHide() {
					if (hideTimer) clearTimeout(hideTimer);

					hideTimer = setTimeout(function() {
						if (!isMouseOverTrigger && !isMouseOverPopover) {
							removePopover();
						}
						hideTimer = null;
					}, 100);
				}

				$this.on('mouseenter.showSiteuserPopover', function() {
					isMouseOverTrigger = true;
					if (hideTimer) clearTimeout(hideTimer);

					if (isShown || $currentPopover) return;

					if (currentRequest) {
						currentRequest.abort();
						currentRequest = null;
					}

					var personId = $this.data('person-id');
					var companyId = $this.data('company-id');

					if (!personId && !companyId) return;

					var container = $this.data('container') || "#" + windowId;
					$this.attr('data-loading', 'true');

					currentRequest = $.ajax({
						url: hostcmsBackend + '/siteuser/index.php',
						data: {
							showPopover: 1,
							person_id: personId,
							company_id: companyId
						},
						dataType: 'json',
						type: 'POST',
						success: function(response) {
							currentRequest = null;

							if (!isMouseOverTrigger) {
								$this.removeAttr('data-loading');
								return;
							}

							if (isShown || $currentPopover) {
								$this.removeAttr('data-loading');
								return;
							}

							removePopover();

							var popoverId = 'popover-siteuser-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

							var popoverHtml = '<div class="popover fade top in" role="tooltip" id="' + popoverId + '">' +
								'<div class="arrow"></div>' +
								'<h3 class="popover-title" style="display: none;"></h3>' +
								'<div class="popover-content" style="user-select: text; -webkit-user-select: text; cursor: default;">' + response.html + '</div>' +
								'</div>';

							var $container = $(container);
							if (!$container.length) $container = $('body');

							$container.append(popoverHtml);
							$currentPopover = $('#' + popoverId);

							$currentPopover.css({
								'user-select': 'text',
								'-webkit-user-select': 'text',
								'pointer-events': 'auto'
							});

							$currentPopover.find('*').css({
								'user-select': 'text',
								'-webkit-user-select': 'text'
							});

							$this.attr('aria-describedby', popoverId);

							var offset = $this.offset();
							var height = $this.outerHeight();
							var width = $this.outerWidth();
							var popoverHeight = $currentPopover.outerHeight();
							var popoverWidth = $currentPopover.outerWidth();

							$currentPopover.css({
								'top': (offset.top - popoverHeight - 30) + 'px',
								'left': (offset.left + (width / 2) - (popoverWidth / 2)) + 'px',
								'display': 'block'
							});

							isShown = true;

							$currentPopover.off('mouseenter.showSiteuserPopover mouseleave.showSiteuserPopover');

							$currentPopover.on('mouseenter.showSiteuserPopover', function() {
								isMouseOverPopover = true;
								if (hideTimer) clearTimeout(hideTimer);
							});

							$currentPopover.on('mouseleave.showSiteuserPopover', function(e) {
								isMouseOverPopover = false;
								var relatedTarget = e.relatedTarget;
								if (relatedTarget && ($this[0] === relatedTarget || $this.has(relatedTarget).length)) return;
								checkAndHide();
							});

							$this.removeAttr('data-loading');
						},
						error: function() {
							currentRequest = null;
							$this.removeAttr('data-loading');
						}
					});
				});

				$this.on('mouseleave.showSiteuserPopover', function(e) {
					isMouseOverTrigger = false;

					var relatedTarget = e.relatedTarget;
					if ($currentPopover && relatedTarget && ($currentPopover[0] === relatedTarget || $currentPopover.has(relatedTarget).length)) {
						return;
					}

					if ($this.attr('data-loading') === 'true') {
						if (currentRequest) currentRequest.abort();
						$this.removeAttr('data-loading');
						return;
					}

					checkAndHide();
				});

				$(document).off('click.showSiteuserPopoverGlobal').on('click.showSiteuserPopoverGlobal', function(e) {
					if (isShown && $currentPopover) {
						var $target = $(e.target);
						if (!$target.closest($this).length && !$target.closest($currentPopover).length) {
							removePopover();
						}
					}
				});

				$this.on('remove.showSiteuserPopover', function() {
					if (hideTimer) clearTimeout(hideTimer);
					if (currentRequest) currentRequest.abort();
					removePopover();
				});
			});
		},

		showCompanyPopover: function(windowId) {
			return this.each(function() {
				var $this = jQuery(this);

				$this.off('mouseenter.showCompanyPopover mouseleave.showCompanyPopover');

				var hideTimer = null;
				var $currentPopover = null;
				var currentRequest = null;
				var isShown = false;
				var isMouseOverPopover = false;
				var isMouseOverTrigger = false;

				function removePopover() {
					if (hideTimer) clearTimeout(hideTimer);
					if ($currentPopover) {
						$currentPopover.remove();
						$currentPopover = null;
					}
					$this.removeAttr('aria-describedby');
					isShown = false;
					isMouseOverPopover = false;
				}

				function checkAndHide() {
					if (hideTimer) clearTimeout(hideTimer);

					hideTimer = setTimeout(function() {
						if (!isMouseOverTrigger && !isMouseOverPopover) {
							removePopover();
						}
						hideTimer = null;
					}, 100);
				}

				$this.on('mouseenter.showCompanyPopover', function() {
					isMouseOverTrigger = true;
					if (hideTimer) clearTimeout(hideTimer);
					if (isShown || $currentPopover) return;

					if (currentRequest) {
						currentRequest.abort();
						currentRequest = null;
					}

					var companyId = $this.data('company-id');
					if (!companyId) return;

					var container = $this.data('container') || "#" + windowId;
					$this.attr('data-loading', 'true');

					currentRequest = $.ajax({
						url: hostcmsBackend + '/company/index.php',
						data: {
							showPopover: 1,
							company_id: companyId
						},
						dataType: 'json',
						type: 'POST',
						success: function(response) {
							currentRequest = null;

							if (!isMouseOverTrigger) {
								$this.removeAttr('data-loading');
								return;
							}

							if (isShown || $currentPopover) {
								$this.removeAttr('data-loading');
								return;
							}

							removePopover();

							var popoverId = 'popover-company-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

							var popoverHtml = '<div class="popover fade top in" role="tooltip" id="' + popoverId + '">' +
								'<div class="arrow"></div>' +
								'<h3 class="popover-title" style="display: none;"></h3>' +
								'<div class="popover-content" style="user-select: text; -webkit-user-select: text; cursor: default;">' + response.html + '</div>' +
								'</div>';

							var $container = $(container);
							if (!$container.length) $container = $('body');

							$container.append(popoverHtml);
							$currentPopover = $('#' + popoverId);

							$currentPopover.css({
								'user-select': 'text',
								'-webkit-user-select': 'text',
								'pointer-events': 'auto'
							});

							$currentPopover.find('*').css({
								'user-select': 'text',
								'-webkit-user-select': 'text'
							});

							$this.attr('aria-describedby', popoverId);

							var offset = $this.offset();
							var height = $this.outerHeight();
							var width = $this.outerWidth();
							var popoverHeight = $currentPopover.outerHeight();
							var popoverWidth = $currentPopover.outerWidth();

							$currentPopover.css({
								'top': (offset.top - popoverHeight - 10) + 'px',
								'left': (offset.left + (width / 2) - (popoverWidth / 2)) + 'px',
								'display': 'block'
							});

							isShown = true;

							$currentPopover.off('mouseenter.showCompanyPopover mouseleave.showCompanyPopover');

							$currentPopover.on('mouseenter.showCompanyPopover', function() {
								isMouseOverPopover = true;
								if (hideTimer) clearTimeout(hideTimer);
							});

							$currentPopover.on('mouseleave.showCompanyPopover', function(e) {
								isMouseOverPopover = false;
								var relatedTarget = e.relatedTarget;
								if (relatedTarget && ($this[0] === relatedTarget || $this.has(relatedTarget).length)) return;
								checkAndHide();
							});

							$this.removeAttr('data-loading');
						},
						error: function() {
							currentRequest = null;
							$this.removeAttr('data-loading');
						}
					});
				});

				$this.on('mouseleave.showCompanyPopover', function(e) {
					isMouseOverTrigger = false;

					var relatedTarget = e.relatedTarget;
					if ($currentPopover && relatedTarget && ($currentPopover[0] === relatedTarget || $currentPopover.has(relatedTarget).length)) {
						return;
					}

					if ($this.attr('data-loading') === 'true') {
						if (currentRequest) currentRequest.abort();
						$this.removeAttr('data-loading');
						return;
					}

					checkAndHide();
				});

				$(document).off('click.showCompanyPopoverGlobal').on('click.showCompanyPopoverGlobal', function(e) {
					if (isShown && $currentPopover) {
						var $target = $(e.target);
						if (!$target.closest($this).length && !$target.closest($currentPopover).length) {
							removePopover();
						}
					}
				});

				$this.on('remove.showCompanyPopover', function() {
					if (hideTimer) clearTimeout(hideTimer);
					if (currentRequest) currentRequest.abort();
					removePopover();
				});
			});
		}
	});
})(jQuery);

$(function() {
	$('body')
		.on('click', '[data-action="showListDealTemplateSteps"]', function() {
			$.adminLoad({
				path: hostcmsBackend + '/deal/template/step/index.php',
				action: 'addConversion',
				operation: 'showListDealTemplateSteps',
				additionalParams: 'deal_template_id=' + $(this).parents('.deal-template-step-conversion').data('deal-template-id') + '&hostcms[checked][0][' + $(this).attr('id').split('adding_conversion_to_')[1] + ']=1',
				windowId: 'id_content'
			});
			return false;
		})
		// Удаление перехода сделки
		.on('click', '[id ^= "conversion_"] .close', function() {
			var wrapConversion = $(this).parent('[id ^="conversion_"]'),
				startAndEndStepId = wrapConversion.attr('id').split('_'),
				conversionStartStepId = startAndEndStepId[1],
				conversionEndStepId = startAndEndStepId[2];

			$.adminLoad({
				path: hostcmsBackend + '/deal/template/step/index.php',
				action: 'deleteConversion',
				operation: '',
				additionalParams: 'deal_template_id=' + $(this).parents('.deal-template-step-conversion').data('deal-template-id') + '&conversion_end_step_id=' + conversionEndStepId + '&hostcms[checked][0][' + conversionStartStepId + ']=1',
				windowId: 'id_content'
			});
		})
		.on('click', '.dropdown-step-list .close', function() {
			var dropdownStepList = $(this).parent('.dropdown-step-list');
			dropdownStepList.prev("[id ^= 'adding_conversion_to_']").show();
			dropdownStepList.remove();
		})
		// Сворачивание/разворачивание списков
		.on('click', '.title_department, .title_users', function() {
			var $this = $(this);
			$this.children('i').toggleClass('fa-caret-right fa-caret-down');

			if ($this.hasClass('title_department')) {
				$this.parent('.depatment_info').next('.wrap').slideToggle();
			} else {
				$this.next('.list_users').slideToggle();
			}
		})
		.on({
			'click': function() {
				var $this = $(this);
				$this.focus();

				if ($this.hasClass('blocked') || $this.parent('.not-changeable').length) {
					return false;
				}

				var iconPermissionId = $this.attr('id'), // department_5_2_3 или user_7_2_3
					aPermissionProperties = iconPermissionId.split('_'),
					objectTypePermission = aPermissionProperties[0] == 'department' ? 0 : 1;

				// Не обрабатываем изменение прав доступа для отделов (если 0 - это department)
				if (!objectTypePermission) {
					return false;
				}

				var objectIdPermission = aPermissionProperties[1],
					dealTemplateStepId = aPermissionProperties[2],
					actionType = aPermissionProperties[3],
					dealTemplateId;

				var urlParams = new URLSearchParams(window.location.search);
				if (urlParams.has('deal_template_id')) {
					dealTemplateId = urlParams.get('deal_template_id');
				} else {
					// Fallback для старых браузеров или специфичных URL
					var matches = document.location.search.match(/deal_template_id=([^&]*)/);
					if (matches && matches.length > 1) dealTemplateId = matches[1];
				}

				$.adminLoad({
					path: hostcmsBackend + '/deal/template/step/index.php',
					action: 'changeAccess',
					operation: '',
					additionalParams: 'deal_template_id=' + dealTemplateId + '&objectType=' + objectTypePermission + '&objectId=' + objectIdPermission + '&actionType=' + actionType + '&hostcms[checked][0][' + dealTemplateStepId + ']=1',
					windowId: 'id_content'
				});
			},
			'mousedown': function() {
				$(this).removeClass('changed');
			},
			'mouseover': function() {
				if ($(this).hasClass('changed')) {
					$(this).toggleClass('fa-solid fa-regular');
				}
			},
			'mouseout': function() {
				$(this).removeClass('changed');
			}
		},
		'.icons_permissions:not(.dms-document-icons-permissions):not(.dms-document-type-icons-permissions) i'
	)
	// Перевод сделки на новый этап
	.on("click", "#deal-steps .steps .lead-step-item-wrapper", function() {
		var $this = $(this),
			dealTemplateStepId = parseInt($this.attr("id").split("simplewizardstep")[1]) || 0,
			dealTemplateSteps = $this.parent(".steps"),
			currentDealTemplateStepId = parseInt(dealTemplateSteps.data("template-step-id"));

		if (dealTemplateStepId && dealTemplateStepId != currentDealTemplateStepId && $this.hasClass("available")) {
			// Создание сделки
			if (!dealTemplateSteps.data("dealId")) {
				$this.toggleClass("active available");
				dealTemplateSteps
					.find("#simplewizardstep" + currentDealTemplateStepId)
					.toggleClass("active available");

				dealTemplateSteps.data("template-step-id", dealTemplateStepId);
			} else {
				// Редактирование сделки
				if ($this.hasClass("next")) {
					$(".deal-template-step-comment").parent().addClass("hidden");
					$this.removeClass("next");
					$(".deal-template-step-name-edit").html('');
					dealTemplateStepId = dealTemplateSteps.data("template-step-id");
				} else {
					$(".deal-template-step-comment").parent().removeClass("hidden");
					$(".next", dealTemplateSteps).removeClass("next");
					$this.addClass("next");

					var currentStepLi = $("#simplewizardstep" + currentDealTemplateStepId, dealTemplateSteps),
						currentStepName = $.escapeHtml($("span.step", currentStepLi).text()),
						currentStepData = $('span.step', currentStepLi).data(),
						newStepName = $.escapeHtml($("span.step", $this).text()),
						newStepData = $('span.step', $this).data();

					$(".deal-template-step-name-edit").html(
						'<span class="badge current-step" style="background-color:' + currentStepData.bgColor + ';color:' + currentStepData.color + ';outline:1px solid ' + currentStepData.borderColor + '">' + currentStepName + '</span>' +
						'<span class="darkgray"> → </span>' +
						'<span class="badge new-step" style="background-color:' + newStepData.bgColor + '; color:' + newStepData.color + ';outline:1px solid ' + newStepData.borderColor + '">' + newStepName + '</span>'
					);
				}

				var $joinUserA = $('.join-user a');
				if (!$joinUserA.hasClass('btn-deal-refuse') && !$joinUserA.hasClass('btn-default')) {
					var $stepSpan = $('#simplewizardstep' + dealTemplateStepId + ' span.step', dealTemplateSteps),
						stepColor = $stepSpan.data('color'),
						stepBorderColor = $stepSpan.data('border-color'),
						stepBgColor = $stepSpan.data('bg-color');

					var dealId = $joinUserA.data('deal-id'),
						windowId = dealTemplateSteps.data("window-id"),
						options;

					if (!$this.hasClass('next')) {
						options = '{deal_step_id: ' + parseInt(dealTemplateSteps.data("step-id")) + ', windowId: "' + windowId + '"}';
					} else {
						options = '{deal_id: ' + dealId + ', deal_template_step_id: ' + dealTemplateStepId + ', windowId: "' + windowId + '"}';
					}

					$joinUserA
						.attr('onclick', '$.joinUser2DealStep(' + options + ')')
						.css({
							'color': stepColor,
							'background-color': stepBgColor,
							'border-color': stepBorderColor
						});
				}
			}

			$("[name='deal_template_step_id']").val(dealTemplateStepId);
		}
	});
});