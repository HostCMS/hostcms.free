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
			var arraySelectItemParts = data.text.split("%%%"),
				className;

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

			var resultHtml = '<span class="' + (className || '') + '">' + $.escapeHtml(arraySelectItemParts[0]) + '</span>';

			if (arraySelectItemParts[2]) {
				resultHtml += '<span class="user-post">' + $.escapeHtml(arraySelectItemParts[2].split('###').join(', ')) + '</span>';
			}

			if (arraySelectItemParts[3]) {
				resultHtml = '<img src="' + $.escapeHtml(arraySelectItemParts[3]) + '" height="30px" class="user-image img-circle">' + resultHtml;
			}

			arraySelectItemParts[1] && delete(arraySelectItemParts[1]);

			return resultHtml;
		},

		templateSelectionItemResponsibleEmployees: function(data, item) {
			var arraySelectItemParts = data.text.split("%%%"),
				className = data.element && $(data.element).attr("class"),
				regExp = /select2-([-\w]+)-result-\w+-\d+?/g,
				myArray = regExp.exec(data._resultId);

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

			var resultHtml = '<span class="' + (className || '') + '">' + $.escapeHtml(arraySelectItemParts[0]) + '</span>';
			data.title = arraySelectItemParts[0];

			if (arraySelectItemParts[1] || arraySelectItemParts[2]) {
				resultHtml += '<br />';
				if (arraySelectItemParts[1]) {
					resultHtml += '<span class="company-department">' + $.escapeHtml(arraySelectItemParts[1]) + '</span>';
					data.title += " - " + arraySelectItemParts[1];
				}

				if (arraySelectItemParts[2]) {
					var departmentPosts = arraySelectItemParts[2].split('###').join(', ');
					resultHtml += (arraySelectItemParts[1] ? ' → ' : '') + '<span class="user-post">' + $.escapeHtml(departmentPosts) + '</span>';
					data.title += " - " + departmentPosts;
				}
			}

			resultHtml = '<div class="user-info">' + resultHtml + '</div>';

			if (arraySelectItemParts[3]) {
				resultHtml = '<img src="' + $.escapeHtml(arraySelectItemParts[3]) + '" height="30px" class="user-image pull-left img-circle">' + resultHtml;
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
				.removeClass('fa-check fa-times')
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
						buttonIcoClass = 'fa-times';
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
			var id = $.escapeHtml(object.id.split('_', 2)[1]),
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
					'<i class="fa fa-times"></i>' +
					'</div>' +
					'</div>' +
					'</div>' +
					'<input type="hidden" name="deal_siteusers[]" value="' + object.id + '"/>' +
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
				width: "100%"
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
				var object = jQuery(this);
				object.on('mouseenter', function() {
					var $this = $(this);

					if (!$this.data("bs.popover") && $(this).data('user-id')) {
						var container = typeof $(this).data('container') !== 'undefined' ?
							$(this).data('container') :
							"#" + windowId;

						// Сначала загружаем данные асинхронно
						$.ajax({
							url: hostcmsBackend + '/user/index.php',
							data: {
								showPopover: 1,
								user_id: $(this).data('user-id')
							},
							dataType: 'json',
							type: 'POST',
							success: function(response) {
								// Если курсор ушел пока грузилось, не показываем
								if (!$this.is(':hover')) return;

								$this.popover({
									placement: 'top',
									trigger: 'manual',
									html: true,
									content: response.html,
									container: container
								});

								$this.attr('data-popoverAttached', true);

								$this.on('hide.bs.popover', function(e) {
									$this.attr('data-popoverAttached') ?
										$this.removeAttr('data-popoverAttached') :
										e.preventDefault();
								})
								.on('show.bs.popover', function(e) {
									!$this.attr('data-popoverAttached') && e.preventDefault();
								})
								.on('shown.bs.popover', function() {
									$('#' + $this.attr('aria-describedby')).on('mouseleave', function(e) {
										!$this.parent().find(e.relatedTarget).length && $this.popover('destroy');
									});
								})
								.on('mouseleave', function(e) {
									!$(e.relatedTarget).parent('#' + $this.attr('aria-describedby')).length &&
										$this.attr('data-popoverAttached') &&
										$this.popover('destroy');
								});

								$this.popover('show');
							}
						});
					}
				});
			});
		},

		showSiteuserPopover: function(windowId) {
			return this.each(function() {
				var object = jQuery(this);
				object.on('mouseenter', function() {
					var $this = $(this);

					if (!$this.data("bs.popover") && ($(this).data('person-id') || $(this).data('company-id'))) {
						$.ajax({
							url: hostcmsBackend + '/siteuser/index.php',
							data: {
								showPopover: 1,
								person_id: $(this).data('person-id'),
								company_id: $(this).data('company-id')
							},
							dataType: 'json',
							type: 'POST',
							success: function(response) {
								if (!$this.is(':hover')) return;

								$this.popover({
									placement: 'top',
									trigger: 'manual',
									html: true,
									content: response.html,
									container: "#" + windowId
								});

								$this.attr('data-popoverAttached', true);

								// Attach handlers similarly to showUserPopover
								$this.on('hide.bs.popover', function(e) { $this.attr('data-popoverAttached') ? $this.removeAttr('data-popoverAttached') : e.preventDefault(); })
									 .on('show.bs.popover', function(e) { !$this.attr('data-popoverAttached') && e.preventDefault(); })
									 .on('shown.bs.popover', function() { $('#' + $this.attr('aria-describedby')).on('mouseleave', function(e) { !$this.parent().find(e.relatedTarget).length && $this.popover('destroy'); }); })
									 .on('mouseleave', function(e) { !$(e.relatedTarget).parent('#' + $this.attr('aria-describedby')).length && $this.attr('data-popoverAttached') && $this.popover('destroy'); });

								$this.popover('show');
							}
						});
					}
				});
			});
		},

		showCompanyPopover: function(windowId) {
			return this.each(function() {
				var object = jQuery(this);
				object.on('mouseenter', function() {
					var $this = $(this);

					if (!$this.data("bs.popover") && $(this).data('company-id')) {
						$.ajax({
							url: hostcmsBackend + '/company/index.php',
							data: {
								showPopover: 1,
								company_id: $(this).data('company-id')
							},
							dataType: 'json',
							type: 'POST',
							success: function(response) {
								if (!$this.is(':hover')) return;

								$this.popover({
									placement: 'top',
									trigger: 'manual',
									html: true,
									content: response.html,
									container: "#" + windowId
								});

								$this.attr('data-popoverAttached', true);

								// Attach handlers
								$this.on('hide.bs.popover', function(e) { $this.attr('data-popoverAttached') ? $this.removeAttr('data-popoverAttached') : e.preventDefault(); })
									 .on('show.bs.popover', function(e) { !$this.attr('data-popoverAttached') && e.preventDefault(); })
									 .on('shown.bs.popover', function() { $('#' + $this.attr('aria-describedby')).on('mouseleave', function(e) { !$this.parent().find(e.relatedTarget).length && $this.popover('destroy'); }); })
									 .on('mouseleave', function(e) { !$(e.relatedTarget).parent('#' + $this.attr('aria-describedby')).length && $this.attr('data-popoverAttached') && $this.popover('destroy'); });

								$this.popover('show');
							}
						});
					}
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
						$(this).toggleClass('fa-circle-o fa-circle');
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