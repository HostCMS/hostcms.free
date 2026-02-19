/* global hostcmsBackend i18n */
(function($) {
	"use strict";

	$.extend({
		recountTotal: function() {
			var quantity = 0,
				amount = 0,
				$tbody = $('.shop-item-table.shop-order-items > tbody').eq(0);

			// Оптимизация: один проход по элементам вместо двух
			$tbody.find('tr:not(:last-child)').each(function() {
				var $row = $(this),
					$qtyInput = $row.find('input[name ^= \'shop_order_item_quantity\']'),
					$priceInput = $row.find('input[name ^= \'shop_order_item_price\']');

				if ($qtyInput.length && $priceInput.length) {
					var qtyVal = parseFloat($qtyInput.val()) || 0,
						priceVal = parseFloat($priceInput.val()) || 0;

					quantity += qtyVal;
					amount += priceVal * qtyVal;
				}
			});

			$tbody.find('td.total_quantity').text(Number(quantity.toFixed(3))); // Убираем лишние нули если целое
			$('.shop-item-table.shop-order-items td.total_amount').text($.mathRound(amount, 2));
		},

		selectShopDocumentRelated: function(object, windowId, modalWindowId) {
			var $object = $(object),
				id = $object.data('id'),
				type = $object.data('type'),
				document_id = $object.data('document-id'),
				shop_id = $object.data('shop-id'),
				amount = parseFloat($('input[name = amount]').val()) || 0,
				dataAmount = parseFloat($object.data('amount')) || 0;

			$.ajax({
				url: hostcmsBackend + '/shop/document/relation/add/index.php',
				type: "POST",
				data: {
					'add_shop_document': 1,
					'id': id,
					'type': type,
					'document_id': document_id
				},
				dataType: 'json',
				error: function() {},
				success: function(result) {
					$('#' + modalWindowId).parents('.modal').modal('hide');

					if (result.related_document_id) {
						$.adminLoad({
							path: hostcmsBackend + '/shop/document/relation/index.php',
							additionalParams: 'document_id=' + document_id + '&shop_id=' + shop_id + '&parentWindowId=' + windowId + '&_module=0',
							windowId: windowId,
							loadingScreen: false
						});

						$('input[name = amount]').val($.mathRound(amount + dataAmount, 2));
					}
				}
			});
		},

		refreshSetsSorting: function(aIds) {
			$.ajax({
				url: hostcmsBackend + '/shop/item/index.php',
				type: "POST",
				data: {
					'refresh_sorting_sets': 1,
					'ids': aIds
				},
				dataType: 'json',
				error: function() {},
				success: function() {}
			});
		},

		toggleWarehouses: function() {
			$(".shop-item-warehouses-list tr:has(td):has(input[value ^= 0])").toggleClass('hidden');
		},

		editWarehouses: function(object) {
			var $rows = $(".shop-item-warehouses-list tbody > tr");
			$rows.removeClass('hidden');
			$rows.find('input[name ^= warehouse_]').prop('disabled', false).focus(); // Фокус упадет на последний, но так было в оригинале

			var $selects = $rows.find('select[name ^= warehouse_shop_price_id_]');
			$selects.removeClass('hidden');
			$selects.parents('div').prev().removeClass('hidden');

			$(object).addClass('hidden');
		},

		toggleShopPrice: function(shop_price_id) {
			$('.toggle-shop-price-' + shop_price_id)
				.toggleClass('hidden')
				.find('input').prop('disabled', function(i, v) {
					return !v;
				});

			// Перемещение кнопок удаления
			$(".shop-item-table tbody tr").each(function() {
				var $this = $(this),
					button = $this.find('a.delete-associated-item'),
					parentTd = button.parents('td');

				parentTd.detach().appendTo($this);
			});
		},

		toggleCoupon: function() {
			var jInput = $('input[name=coupon_text]'),
				length = jInput.val().length;

			jInput.parents('.form-group').toggleClass('hidden');

			if (length === 0) $.generateCoupon(jInput);
		},

		generateCoupon: function(jInput) {
			$.ajax({
				url: hostcmsBackend + '/shop/discount/index.php',
				type: 'POST',
				data: {
					'generate-coupon': 1
				},
				dataType: 'json',
				error: function() {},
				success: function(answer) {
					jInput.val(answer.coupon).focus();
				}
			});
		},

		changePrintButton: function(object) {
			var print_price_id = $(object).val();

			$('.print-price ul.dropdown-menu li:has(a) > a').each(function() {
				var $this = $(this),
					onclick = $this.attr('onclick');

				if (onclick) {
					var matches = onclick.match(/(\&\w+\S+\&)/);
					if (matches) {
						var split = matches[0].split('&'),
							text = onclick.replace(split[1], 'shop_price_id=' + print_price_id);
						$this.attr('onclick', text);
					}
				}
			});
		},

		showPrintButton: function(window_id, id) {
			var $window = $('#' + window_id);
			$window.find('.print-button').removeClass('hidden');

			$window.find('.print-button ul.dropdown-menu li:has(a) > a').each(function() {
				var $this = $(this),
					onclick = $this.attr('onclick');

				if (onclick) {
					var text = onclick.replace('[]', '[' + id + ']');
					$this.attr('onclick', text);
				}
			});
		},

		insertSeoTemplate: function(el, text) {
			el && el.insertAtCaret(text);
		},

		getSeoFilterPropertyValues: function(object) {
			$.ajax({
				url: hostcmsBackend + '/shop/filter/seo/index.php',
				data: {
					'get_values': 1,
					'property_id': $(object).val()
				},
				dataType: 'json',
				type: 'POST',
				success: function(result) {
					if (result.status == 'success') {
						$('.property-values').html(result.html);
					}
				}
			});
		},

		saveApplySeoFilterCondition: function(e, windowId) {
			var key = 'which' in e ? e.which : e.keyCode;
			if (key == 13) {
				$.applySeoFilterConditions($('#' + windowId + '-conditionsModal'));
				return false;
			}
		},

		applySeoFilterConditions: function(modalWindow) {
			var property_id = modalWindow.find('select[name = "modal_property_id"]').val(),
				jPropertyValue = modalWindow.find('*[name = "modal_property_value"]'),
				jPropertyValueTo = modalWindow.find('*[name = "modal_property_value_to"]'),
				type = jPropertyValue.attr('type'),
				property_value = null,
				property_value_to = null;

			switch (type) {
				case 'checkbox':
					property_value = +jPropertyValue.is(':checked');
					break;
				default:
					property_value = jPropertyValue.val();
					property_value_to = jPropertyValueTo.val();
			}

			$.ajax({
				url: hostcmsBackend + '/shop/filter/seo/index.php',
				data: {
					'add_property': 1,
					'property_id': property_id,
					'property_value': property_value,
					'property_value_to': property_value_to
				},
				dataType: 'json',
				type: 'POST',
				success: function(result) {
					if (result.status == 'success') {
						var sorting = [];

						$('.filter-conditions .dd-item').each(function() {
							var id = parseFloat($(this).data('sorting'));
							sorting.push(id);
						});
						sorting.sort(function(a, b) {
							return a - b;
						});

						var newSorting = (sorting.length ? sorting[sorting.length - 1] : 0) + 1;

						$('.filter-conditions').append('<div class="dd"><ol class="dd-list"><li class="dd-item bordered-palegreen" data-sorting="' + newSorting + '"><div class="dd-handle"><div class="form-horizontal"><div class="form-group no-margin-bottom">' + result.html + '<a class="delete-associated-item" onclick="$(this).parents(\'.dd-item\').remove()"><i class="fa fa-times-circle darkorange"></i></a></div></div></li></ol></div></div><input type="hidden" name="property_value_sorting[]" value="' + newSorting + '"/>');

						// Reload nestable list
						$.loadSeoFilterNestable();
					}

					modalWindow.modal('hide');
				}
			});
		},

		loadSeoFilterNestable: function() {
			$('.filter-conditions').sortable({
				connectWith: '.filter-conditions',
				items: '> .dd',
				scroll: false,
				placeholder: 'placeholder',
				tolerance: 'pointer',
				stop: function() {
					$('.filter-conditions input[type = "hidden"]').remove();
					$('.filter-conditions li.dd-item').each(function(i, object) {
						$('.filter-conditions').append('<input type="hidden" name="property_value_sorting' + $(object).data('id') + '" value="' + i + '"/>');
					});
				}
			}).disableSelection();

			$('.filter-conditions .dd-handle a, .filter-conditions .dd-handle .property-data').on('mousedown', function(e) {
				e.stopPropagation();
			});
		},

		updateWarehouseCounts: function(shop_warehouse_id) {
			var aItems = [];
			var $rows = $('.shop-item-table > tbody tr[data-item-id]');

			$rows.each(function() {
				aItems.push($(this).data('item-id'));
			});

			$.ajax({
				url: hostcmsBackend + '/shop/warehouse/inventory/index.php',
				type: "POST",
				data: {
					'update_warehouse_counts': 1,
					'shop_warehouse_id': shop_warehouse_id,
					'items': aItems,
					'datetime': $('input[name=datetime]').val()
				},
				dataType: 'json',
				error: function() {},
				success: function(answer) {
					$rows.each(function() {
						var $row = $(this),
							id = $row.data('item-id');

						if (answer[id]) {
							$row.find('.calc-warehouse-count').text(answer[id]['count']);

							var jInput = $row.find('.set-item-count');
							jInput.change(); // Trigger recalc
							$.changeWarehouseCounts(jInput, 0); // Re-bind listener?
						}
					});
				}
			});
		},

		changeWarehouseCounts: function(jInput, type) {
			// Предотвращение дублирования событий через .off()
			jInput.off('change.warehouse').on('change.warehouse', function() {
				var $this = $(this);

				// Replace ',' on '.'
				var val = $this.val().replace(',', '.');
				if (val < 0) val = 0;
				$this.val(val);

				var parentTr = $this.parents('tr'),
					quantity = ($.isNumeric(val) && val > 0) ? parseFloat(val) : 0,
					price = 0,
					sum = 0;

				// Определение цены в зависимости от типа операции
				if (type === 6) { // Заказ поставщику
					var inputPrice = parentTr.find('input.price').val();
					price = $.isNumeric(inputPrice) ? parseFloat(inputPrice) : 0;
				} else {
					var textPrice = parentTr.find('.price').text();
					price = $.isNumeric(textPrice) ? parseFloat(textPrice) : 0;
				}

				sum = $.mathRound(quantity * price, 2);

				switch (type) {
					// Инвентаризация
					case 0:
						var calcCountText = parentTr.find('.calc-warehouse-count').text(),
							calcCount = $.isNumeric(calcCountText) ? parseFloat(calcCountText) : 0,
							diffCount = $.mathRound((quantity - calcCount), 3),
							diffCountTd = parentTr.find('.diff-warehouse-count');

						parentTr.find('.calc-warehouse-sum').text($.mathRound(price * calcCount, 2));

						var calcSumText = parentTr.find('.calc-warehouse-sum').text(),
							calcSum = $.isNumeric(calcSumText) ? parseFloat(calcSumText) : 0,
							invSumSpan = parentTr.find('.warehouse-inv-sum'),
							diffSumSpan = parentTr.find('.diff-warehouse-sum');

						diffCountTd.removeClass('palegreen darkorange');

						if (diffCount > 0) {
							diffCount = '+' + diffCount;
							diffCountTd.addClass('palegreen');
						} else if (diffCount < 0) {
							diffCountTd.addClass('darkorange');
						}
						// Если 0, классы уже удалены

						diffCountTd.text(diffCount);
						invSumSpan.text(sum);

						var diffSum = $.mathRound((sum - calcSum), 2),
							parentDiffTd = diffSumSpan.parents('td');

						parentDiffTd.removeClass('palegreen darkorange');

						if (diffSum > 0) {
							diffSum = '+' + diffSum;
							parentDiffTd.addClass('palegreen');
						} else if (diffSum < 0) {
							parentDiffTd.addClass('darkorange');
						}

						diffSumSpan.text(diffSum);
						break;

						// Оприходование
					case 1:
					case 2:
						parentTr.find('.calc-warehouse-sum').text(sum);
						parentTr.find('.hidden-shop-price').val(price);
						break;
					case 5: // Другие типы
					case 6: // Заказ поставщику
						parentTr.find('.calc-warehouse-sum').text(sum);
						break;
				}
			});
		},

		changeWarehousePrices: function(jInput) {
			jInput.off('change.warehousePrice').on('change.warehousePrice', function() {
				var $this = $(this),
					parentTr = $this.parents('tr'),
					val = $this.val().replace(',', '.');

				if (val < 0) val = 0;
				$this.val(val);

				var price = $.isNumeric(val) ? parseFloat(val) : 0,
					oQuantity = parentTr.find('input.set-item-count'),
					qVal = oQuantity.val().replace(',', '.');

				if (qVal < 0) qVal = 0;
				oQuantity.val(qVal);

				var quantity = ($.isNumeric(qVal) && qVal > 0) ? parseFloat(qVal) : 0,
					sum = $.mathRound(quantity * price, 2);

				parentTr.find('.calc-warehouse-sum').text(sum);
			});
		},

		recountIndexes: function($tr) {
			var $prev = $tr.prev(),
				index = parseInt($prev.find('.index').text()) || 0,
				$allNextIndexes = $prev.length ? $prev.nextAll('tr') : $tr.parent().find('tr');

			$allNextIndexes.each(function() {
				++index;
				$(this).find('td.index').text(index);
			});
		},

		prepareShopPrices: function() {
			$('.shop-item-table > tbody tr[data-item-id]').each(function(index) {
				$(this).find('td:first-child').text(index + 1);
				var jInput = $(this).find('.set-item-new-price');
				jInput.change();
				$.changeShopPrices(jInput);
			});
		},

		changeShopPrices: function(jInput) {
			jInput.off('change.shopPrice').on('change.shopPrice', function() {
				var $this = $(this);
				var val = $this.val().replace(',', '.');
				if (val < 0) val = 0;
				$this.val(val);

				var parentTr = $this.parents('tr'),
					newPrice = ($.isNumeric(val) && val > 0) ? parseFloat(val) : 0,
					shop_price_id = $this.data('shop-price-id'),
					$oldPriceEl = parentTr.find('.old-price-' + shop_price_id),
					oldPrice = ($.isNumeric($oldPriceEl.text()) && $oldPriceEl.text() > 0) ? parseFloat($oldPriceEl.text()) : 0,
					percent = 0,
					diffPersentSpan = parentTr.find('.percent-diff-' + shop_price_id),
					diffPercentValue = oldPrice !== 0 ? (newPrice * 100) / oldPrice : 0;

				diffPersentSpan.removeClass('palegreen darkorange');

				if (oldPrice !== 0 && newPrice !== 0) {
					if (diffPercentValue > 100) {
						percent = '+' + $.mathRound((diffPercentValue - 100), 2);
						diffPersentSpan.addClass('darkorange');
					} else if (diffPercentValue < 100) {
						percent = '-' + $.mathRound((100 - diffPercentValue), 2);
						diffPersentSpan.addClass('palegreen');
					} else {
						// 100% (нет изменений)
					}

					if (percent == '-0') percent = 0;

					if (percent !== 0) {
						diffPersentSpan.text(percent + '%');
					} else {
						diffPersentSpan.text('');
					}
				} else {
					diffPersentSpan.text('');
				}
			});
		},

		recalcPrice: function(windowId) {
			var jSelect = $('#' + windowId + ' select.select-price'),
				shop_price_id = jSelect.val(),
				aItems = [];

			var $rows = $('#' + windowId + ' .shop-item-table > tbody tr[data-item-id]');

			$rows.each(function() {
				var idStr = $(this).data('item-id').toString();

				if (idStr.includes(',')) {
					var aIds = idStr.split(',');
					$.each(aIds, function(i, shop_item_id) {
						aItems.push(shop_item_id);
					});
				} else {
					aItems.push(idStr);
				}
			});

			$.ajax({
				url: hostcmsBackend + '/shop/warehouse/index.php',
				type: "POST",
				data: {
					'load_prices': 1,
					'shop_price_id': shop_price_id,
					'items': aItems
				},
				dataType: 'json',
				error: function() {},
				success: function(answer) {
					$rows.each(function() {
						var container = $(this),
							idStr = container.data('item-id').toString();

						if (idStr.includes(',')) {
							var aIds = idStr.split(',');
							$.each(aIds, function(i, shop_item_id) {
								if (answer[shop_item_id]) {
									var price = answer[shop_item_id]['price'],
										type = !i ? 'writeoff' : 'incoming';

									container.find('.' + type + '-price').text(price);
									container.find('input[name = ' + type + '_price_' + shop_item_id + ']').val(price);
								}
							});
						} else {
							if (answer[idStr]) {
								var jInput = container.find('.set-item-count'),
									jPrice = container.find('.price');

								if (jPrice.is(':input[type="text"]')) {
									jPrice.val(answer[idStr]['price']);
									jPrice.change();
								} else {
									jPrice.text(answer[idStr]['price']);
									jInput.change();
								}
							}
						}
					});
				}
			});
		},

		addRegradeItem: function(shop_id, placeholder) {
			// Оптимизация: создание строки один раз
			var newRow = `
				<tr data-item-id="">
					<td class="index"></td>
					<td><input class="writeoff-item-autocomplete form-control" data-type="writeoff" placeholder="${placeholder}" /><input type="hidden" name="writeoff_item[]" value="" /></td>
					<td><span class="writeoff-measure"></span></td>
					<td><span class="writeoff-price"></span></td>
					<td><span class="writeoff-currency"></span></td>
					<td><input class="incoming-item-autocomplete form-control" data-type="incoming" placeholder="${placeholder}"/><input type="hidden" name="incoming_item[]" value="" /></td>
					<td><span class="incoming-measure"></span></td>
					<td><span class="incoming-price"></span></td>
					<td><span class="incoming-currency"></span></td>
					<td width="80"><input class="set-item-count form-control" name="shop_item_quantity[]" value=""/></td>
					<td><a class="delete-associated-item" onclick="var next = $(this).parents('tr').next(); $(this).parents('tr').remove(); $.recountIndexes(next)"><i class="fa fa-times-circle darkorange"></i></a></td>
				</tr>`;

			$('.shop-item-table').append(newRow);

			// Init autocomplete on new elements
			var $lastRow = $('.shop-item-table > tbody tr:last-child');
			var aItemIds = ['', ''];

			$lastRow.find('.writeoff-item-autocomplete, .incoming-item-autocomplete').autocompleteShopItem({
				'shop_id': shop_id,
				'shop_currency_id': 0
			}, function(event, ui) {
				var type = $(this).data('type'),
					parentTr = $(this).parents('tr');

				parentTr.find('.' + type + '-measure').text(ui.item.measure);
				parentTr.find('.' + type + '-price').text(ui.item.price_with_tax);
				parentTr.find('.' + type + '-currency').text(ui.item.currency);

				$(this).next('input').val(ui.item.id);
				parentTr.find('.' + type + '-item-autocomplete').attr('id', ui.item.id);

				aItemIds = $.getIds(aItemIds, $(this));
				parentTr.attr('data-item-id', aItemIds.slice(-2).join(','));
			});

			$lastRow.find('td.index').text($('.shop-item-table > tbody tr').length);
		},

		getIds: function(aItemIds, object) {
			var type = object.data('type'),
				id = object.attr('id'),
				index = type == 'writeoff' ? 2 : 1;

			aItemIds[aItemIds.length - index] = id;
			return aItemIds;
		},

		cloneSpecialPrice: function(windowId, cloneDelete) {
			var jSpecialPrice = jQuery(cloneDelete).closest('.spec_prices'),
				jNewObject = jSpecialPrice.clone();

			jNewObject.find(':regex(name, ^\\S+_\\d+$)').each(function(index, object) {
				var reg = /^(\S+)_(\d+)$/;
				var arr = reg.exec(object.name);
				if (arr) jQuery(object).prop('name', arr[1] + '_' + '[]');
			});
			jNewObject.find("input").val('');
			jNewObject.insertAfter(jSpecialPrice);
		},

		deleteNewSpecialprice: function(object) {
			jQuery(object).closest('.spec_prices').remove();
		},

		cloneDeliveryOption: function(windowId, cloneDelete) {
			var jDeliveryOption = jQuery(cloneDelete).closest('.delivery_options'),
				jNewObject = jDeliveryOption.clone();

			jNewObject.find(':regex(name, ^\\S+_\\d+$)').each(function(index, object) {
				var reg = /^(\S+)_(\d+)$/;
				var arr = reg.exec(object.name);
				if (arr) jQuery(object).prop('name', arr[1] + '_' + '[]');
			});
			jNewObject.find("input,select").val('');
			jNewObject.insertAfter(jDeliveryOption);
		},

		deleteNewDeliveryOption: function(object) {
			jQuery(object).closest('.delivery_options').remove();
		},

		cloneDeliveryInterval: function(windowId, cloneDelete) {
			var jDeliveryInterval = jQuery(cloneDelete).closest('.delivery_intervals'),
				jNewObject = jDeliveryInterval.clone();

			jNewObject.find(':regex(name, ^\\S+_\\d+$)').each(function(index, object) {
				var reg = /^(\S+)_(\d+)$/;
				var arr = reg.exec(object.name);
				if (arr) jQuery(object).prop('name', arr[1] + '_' + '[]');
			});
			jNewObject.find("input").val('00:00');

			jNewObject.insertAfter(jDeliveryInterval);

			jNewObject.find("input").wickedpicker({
				now: '00 : 00',
				twentyFour: true,
				upArrow: 'wickedpicker__controls__control-up',
				downArrow: 'wickedpicker__controls__control-down',
				close: 'wickedpicker__close',
				hoverState: 'hover-state',
				showSeconds: false,
				timeSeparator: ' : ',
				secondsInterval: 1,
				minutesInterval: 1,
				clearable: false
			});
		},

		deleteNewDeliveryInterval: function(object) {
			if (confirm(i18n['confirm_delete'])) {
				jQuery(object).closest('.delivery_intervals').remove();
			}
		},

		changeOrderStatus: function(windowId) {
			var date = new Date();
			// Оптимизация: использование padStart для форматирования даты
			var day = String(date.getDate()).padStart(2, '0');
			var month = String(date.getMonth() + 1).padStart(2, '0');
			var hours = String(date.getHours()).padStart(2, '0');
			var minutes = String(date.getMinutes()).padStart(2, '0');
			var seconds = '00';

			$("#" + windowId + " #status_datetime").val(`${day}.${month}.${date.getFullYear()} ${hours}:${minutes}:${seconds}`);
		}
	});

	$.fn.extend({
		autocompleteShopItem: function(options, selectOption) {
			return this.each(function() {
				const $this = $(this);
				jQuery(this).autocomplete({
					source: function(request, response) {
						$.ajax({
							url: hostcmsBackend + '/shop/index.php?autocomplete&' + $.param(options),
							dataType: 'json',
							data: {
								queryString: request.term
							},
							success: response
						});
					},
					minLength: 1,
					create: function() {
						const autocomplete = $(this).data('ui-autocomplete');

						autocomplete._renderItem = (ul, item) => {
							const {
								image_small = '',
								count = 0,
								marking = '',
								label,
								price_with_tax_formatWithCurrency
							} = item;

							let badgeColor = 'lightgray';
							if (count > 0) badgeColor = 'green';
							else if (count < 0) badgeColor = 'darkorange';

							const hasImage = image_small.length > 0;

							// Безопасный рендеринг через создание элементов
							const $li = $('<li class="autocomplete-suggestion"></li>')
								.data('item.autocomplete', item);

							const $imgDiv = $('<div class="image"></div>');
							if (hasImage) {
								$imgDiv.append($('<img>', {
									class: 'backend-thumbnail',
									src: image_small
								}));
							}
							$li.append($imgDiv);

							$li.append($('<div class="name"></div>').append($('<a>').text(label))); // Escaping via .text()
							$li.append($('<div class="marking"></div>').text(marking)); // Escaping via .text()

							// price_with_tax_formatWithCurrency содержит HTML (валюту), поэтому оставляем html(), но это поле приходит с сервера
							$li.append($('<div class="price"></div>').html(price_with_tax_formatWithCurrency));

							$li.append(`<div class="count"><span class="badge ${badgeColor} badge-round white">${$.escapeHtml(count)}</span></div>`);

							return $li.appendTo(ul);
						};

						$this.prev('.ui-helper-hidden-accessible').remove();
					},
					select: selectOption,
					open: function() {
						$this.removeClass('ui-corner-all').addClass('ui-corner-top');
					},
					close: function() {
						$this.removeClass('ui-corner-top').addClass('ui-corner-all');
					}
				});
			});
		}
	});
})(jQuery);