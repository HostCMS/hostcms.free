/* global hostcmsBackend, bootbox, i18n, mainFormLocker, Notify, jQuery */

(function($){
	"use strict";
	
	$.extend({
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
						Notify('<span>' + i18n['ban_success'] + '</span>', '', 'top-right', '5000', 'success', 'fa-solid fa-check', true);
					} else if (answer.result === 'error') {
						Notify('<span>' + i18n['ban_error'] + '</span>', '', 'top-right', '5000', 'danger', 'fa-solid fa-ban', true);
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
							`<div class="dd"><ol class="dd-list"><li class="dd-item bordered-palegreen" data-sorting="${newSorting}"><div class="dd-handle"><div class="form-horizontal"><div class="form-group no-margin-bottom ipaddress-filter-row">${result.html}<a class="delete-associated-item" onclick="if (confirm(i18n['confirm_delete'])) { $(this).parents('.dd-item').remove() } return false "><i class="fa-solid fa-circle-xmark darkorange"></i></a></div></div></li></ol></div></div><input type="hidden" name="ipaddress_filter_sorting[]" value="${newSorting}"/>`
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
							`<div class="dd"><ol class="dd-list"><li class="dd-item bordered-palegreen" data-sorting="${newSorting}"><div class="dd-handle"><div class="form-horizontal"><div class="form-group no-margin-bottom ipaddress-filter-row">${result.html}<a class="delete-associated-item" onclick="if (confirm(i18n['confirm_delete'])) { $(this).parents('.dd-item').remove() } return false"><i class="fa-solid fa-circle-xmark darkorange"></i></a></div></div></li></ol></div></div><input type="hidden" name="ipaddress_filter_sorting[]" value="${newSorting}"/>`
						);

						$.loadIpaddressFilterNestable();
					}
				}
			});
		}
	});
})(jQuery);
