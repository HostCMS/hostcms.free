/* global hostcmsBackend, jQuery */
"use strict";

(function($){
	$.extend({
		loadChartaccounts: function(windowId, chartaccount_id, prefix) {
			$.ajax({
				url: hostcmsBackend + '/chartaccount/operation/index.php',
				type: "POST",
				data: { 'load_chartaccounts': 1, 'chartaccount_id': chartaccount_id, 'prefix': prefix },
				dataType: 'json',
				error: function(){},
				success: function (result) {
					// Оптимизация логики переключения префикса
					var targetPrefix = (prefix === 'd') ? 'c' : ((prefix === 'c') ? 'd' : prefix);

					var $object = $('#' + windowId).find('select[name = ' + targetPrefix + 'chartaccount_id]'),
						old_value = $object.val();

					if ($object.length) {
						$object.appendOptions(result);
						// Используем prop вместо attr для selected
						$object.find('option[value = ' + old_value + ']').prop('selected', true);
					}
				}
			});
		},
		getChartaccountSubcouns: function(windowId)
		{
			var $inputs = $('#' + windowId + ' .chartaccount-trialbalance-entry-subcounts').find(':input'),
				iFiltersItemsCount = $inputs.length,
				aSubcounts = [];

			for (var i = 0; i < iFiltersItemsCount; i++)
			{
				var $item = $inputs.eq(i),
					type = $item.data('type'),
					value = $item.val(),
					sc = 'sc' + i;

				aSubcounts.push({
					'sc': sc,
					'type': type,
					'value': value
				});
			}

			return aSubcounts;
		},
		filterChartaccountTrialbalanceEntries: function(object, windowId, code) {
			$.sendRequest({
				path: hostcmsBackend + '/chartaccount/trialbalance/entry/index.php?code=' + code,
				context: $('#' + windowId + ' .mainForm')
			});
		},
		loadSubcounts: function(windowId, chartaccount_id, data, container, prefix) {
			if (chartaccount_id)
			{
				container = container || '.chartaccount-subcounts';
				prefix = prefix || '';

				// Кэшируем поиск элементов
				var $window = $("#" + windowId),
					$sc0 = $window.find("#" + prefix + "sc0"),
					$sc1 = $window.find("#" + prefix + "sc1"),
					$sc2 = $window.find("#" + prefix + "sc2");

				data = $.extend({
					'load_subcounts': 1,
					'hostcms[window]': windowId,
					'chartaccount_id': chartaccount_id,
					'prefix': prefix,
					'sc0': $sc0.val(),
					'sc1': $sc1.val(),
					'sc2': $sc2.val(),
					'sc0_type': $sc0.data('type'),
					'sc1_type': $sc1.data('type'),
					'sc2_type': $sc2.data('type')
				}, data);

				$.ajax({
					url: hostcmsBackend + '/chartaccount/index.php',
					data: data,
					dataType: 'json',
					type: 'POST',
					success: function(answer){
						$(container)
							.empty()
							.html(answer.html);
					}
				});
			}
		},
		fillCompanyCashbox: function($select, companyId, currentValue) {
			if (companyId)
			{
				$.ajax({
					url: hostcmsBackend + '/company/cashbox/index.php?getCashboxes',
					dataType: 'json',
					data: {
						companyId: companyId
					},
					success: function(data) {
						$select.empty();

						if (data.cashboxes && data.cashboxes.length)
						{
							var items = [],
								count = data.cashboxes.length;

							// Формируем массив строк вместо множественных вызовов .append()
							for (var i = 0; i < count; i++)
							{
								var item = data.cashboxes[i],
									selected = (currentValue == item.id) ? ' selected="selected"' : '',
									// Безопасное добавление имени (если $.escapeHtml доступен, иначе просто item.name)
									safeName = $.escapeHtml ? $.escapeHtml(item.name) : item.name;

								items.push('<option value="' + item.id + '"' + selected + '>' + safeName + '</option>');
							}

							$select.append(items.join(''));
						}
					}
				});
			}
		},
		fillCompanyAccount: function($select, companyId, currentValue)
		{
			if (companyId)
			{
				$.ajax({
					url: hostcmsBackend + '/company/account/index.php?getAccounts',
					dataType: 'json',
					data: {
						companyId: companyId
					},
					success: function(data) {
						$select.empty();

						if (data.accounts && data.accounts.length)
						{
							var items = [],
								count = data.accounts.length;

							// Формируем массив строк вместо множественных вызовов .append()
							for (var i = 0; i < count; i++)
							{
								var item = data.accounts[i],
									selected = (currentValue == item.id) ? ' selected="selected"' : '',
									safeName = $.escapeHtml ? $.escapeHtml(item.name) : item.name;

								items.push('<option value="' + item.id + '"' + selected + '>' + safeName + '</option>');
							}

							$select.append(items.join(''));
						}
					}
				});
			}
		}
	});
})(jQuery);