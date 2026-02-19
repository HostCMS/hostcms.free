/*global Notify hostcmsBackend */
(function($) {
	"use strict";

	// Полифилл для rAF, если вдруг используется старый браузер
	var raf = window.requestAnimationFrame || window.mozRequestAnimationFrame ||
		window.webkitRequestAnimationFrame || window.msRequestAnimationFrame ||
		function(f) {
			return setTimeout(f, 1000 / 60);
		};

	$.extend({
		kanbanStepMove: function(windowId, path, data, moveCallback) {
			$.ajax({
				data: data,
				type: "POST",
				dataType: 'json',
				url: path,
				success: moveCallback
			});
		},
		_kanbanStepMoveCallback: function(result) {
			if (result.status == 'success') {
				if (result.update) {
					var $kanban = $('.kanban-board'); // Кешируем родителя

					$.each(result.update, function(id, object) {
						// Оптимизация: ищем элемент один раз
						var $item = $kanban.find('#data-' + id);
						if ($item.length) {
							$item.find('.kanban-deals-count').text(object.data.count ? object.data.count : '');
							$item.find('.kanban-deals-amount').text(typeof object.data.amount !== 'undefined' ? object.data.amount : object.data);
						}
					});
				}
			} else if (result.status == 'error' && result.error_text) {
				Notify('<span>' + $.escapeHtml(result.error_text) + '</span>', '', 'bottom-left', '7000', 'danger', 'fa-check', true);
				$('ul#entity-list-' + result.target_id).addClass('error-drop');
			}
		},
		_kanbanStepMoveLeadCallback: function(result) {
			if (result.status == 'success') {
				if (result.last_step == 1) {
					var id = 'hostcms[checked][0][' + result.lead_id + ']',
						lead_status_id = result.lead_status_id,
						post = {};

					post[id] = 1;
					post['mode'] = 'edit';
					post['lead_status_id'] = lead_status_id;

					$.adminLoad({
						path: hostcmsBackend + '/lead/index.php',
						action: 'morphLead',
						operation: 'finish',
						post: post,
						additionalParams: '',
						windowId: result.window_id
					});
				} else if (result.type == 2) {
					$('li#lead-' + result.lead_id).addClass('failed');
				}

				$._kanbanStepMoveCallback(result);
			}
		},
		sortableKanban: function(options) {
			options = jQuery.extend({
				path: '.',
				container: null,
				updateData: false,
				windowId: 'id_content',
				moveCallback: $._kanbanStepMoveCallback,
				handle: ".drag-handle"
			}, options);

			const $sortableContainer = $(options.container);

			$sortableContainer.on('mousedown', function(e) {
					var sortableLi = $(e.target).closest(".connectedSortable > li:not('.failed'):not('.finish')");
					if (sortableLi.length) {
						$(this).data('mousedownLi', sortableLi.attr('id'));
					}
				})
				.on('mousemove', function() {
					var $this = $(this);
					var mousedownLi = $this.data('mousedownLi');

					if (mousedownLi) {
						var $actionWrapper = $this.find('.kanban-action-wrapper');
						$actionWrapper.removeClass('hidden');

						var $currentSortableList = $this.find('#' + mousedownLi).parents('.connectedSortable');

						// Оптимизация: вычисляем оффсеты только если элемент найден
						if ($currentSortableList.length && $actionWrapper.length) {
							// Расстояние от нижней границы списка до верхней границы блока действий
							var delta = $currentSortableList.offset().top + $currentSortableList.outerHeight() - $actionWrapper.offset().top;

							if (delta > 0) {
								$currentSortableList.outerHeight($currentSortableList.outerHeight() - delta - 5);
							}
						}
					}
				})
				.on('mouseup', function() {
					$(this).removeData('mousedownLi');
				});


			$(options.container + ' .connectedSortable').sortable({
				items: "> li:not('.failed'):not('.finish')",
				connectWith: options.container + ' .connectedSortable',
				placeholder: 'placeholder',
				handle: options.handle,
				helper: "clone",
				tolerance: "pointer",
				scroll: true,
				receive: function(event, ui) {
					var sender_id = ui.sender.data('step-id'),
						target_id = $(this).data('step-id'),
						$element = $(event.target),
						$item = ui.item;

					if ($element.hasClass('kanban-action-item')) {
						$item
							.addClass('hidden')
							.addClass('just-hidden');

						$element.css('opacity', 1);

						target_id = $element.data('id');
						$item.data('sender', target_id);
					}

					$.kanbanStepMove(options.windowId, options.path, {
						id: $item.data('id'),
						sender_id: sender_id,
						target_id: target_id,
						update_data: +options.updateData
					}, options.moveCallback);

					setTimeout(function() {
						if ($element.hasClass('error-drop')) {
							ui.sender.sortable("cancel");
							$element.removeClass('error-drop');
						}
					}, 200);

					ui.sender.removeAttr('style');

					prepareKanbanBoards();
				},
				start: function(event, ui) {
					var $item = ui.item,
						kanbanBoardWrapper = $item.parents('.kanban-board').children('.kanban-wrapper');

					$sortableContainer.data('startKanbanList', event.currentTarget);

					if (kanbanBoardWrapper.hasClass('scrollable')) {
						kanbanBoardWrapper.attr('data-draggingItem', true);
					}

					$(options.container + ' .kanban-action-wrapper').removeClass('hidden');

					$item.removeClass('cancel-' + $item.data('id'));

					// Ghost items
					$(options.container + ' .connectedSortable').find('li:hidden')
						.addClass('ghost-item')
						.addClass('cancel-' + $item.data('id'))
						.css('opacity', .5)
						.show();
				},
				stop: function(event, ui) {
					var $kanbanBoard = ui.item.parents('.kanban-board'),
						kanbanBoardWrapper = $kanbanBoard.children(".kanban-wrapper");

					if ($sortableContainer.data('startKanbanList')) {
						$($sortableContainer.data('startKanbanList')).removeAttr('style');
						$sortableContainer.removeData('startKanbanList');
					}

					if (kanbanBoardWrapper.attr('data-draggingItem')) {
						kanbanBoardWrapper.removeAttr('data-draggingItem');
					}

					var $actionItem = ui.item.parents('.kanban-action-item');
					$actionItem.find('.kanban-action-item-name').addClass('hidden');
					$actionItem.find('.return').removeClass('hidden');

					ui.item.data('target', $(event.target).data('step-id'));

					if ($actionItem.length) {
						setTimeout(function() {
							$.closeActions(options.container, ui);
						}, 3000);
					} else {
						$.closeActions(options.container, ui);
					}

					// Ghost removal
					$(options.container + ' .connectedSortable').find('li.ghost-item')
						.removeClass('ghost-item')
						.css('opacity', 1);
				},
				over: function(event, ui) {
					var $element = $(event.target);
					if ($element.hasClass('kanban-action-item')) {
						$element.css('opacity', 0.6);
						var bg = $element.data('hover-bg');
						ui.helper.find('.well').css('background-color', bg);
					}
				},
				out: function(event, ui) {
					var $element = $(event.target);
					if ($element.hasClass('kanban-action-item')) {
						$element.css('opacity', 1);
						if (ui.helper !== null) {
							ui.helper.find('.well').css('background-color', '#fff');
						}
					}
				},
				sort: function() {
					$('html').removeClass(function(index, css) {
						return (css.match(/\bcancel-\S+/g) || []).join(' ');
					});
				}
			}).disableSelection();

			$(options.container + ' .connectedSortable .return').on('click', function() {
				var jLi = $(options.container + ' .connectedSortable').find('li[class *= "cancel-"]'),
					target_id = jLi.data('target'),
					sender_id = jLi.data('sender');

				if (jLi.length) {
					var itemId = $(jLi[0]).data('id');
					$(options.container + ' ul#entity-list-' + target_id).sortable("cancel");

					$.kanbanStepMove(options.windowId, options.path, {
						id: itemId,
						sender_id: sender_id,
						target_id: target_id,
						update_data: 1
					}, options.moveCallback);

					var $actionItem = $(this).parents('.kanban-action-item');
					$actionItem.find('.kanban-action-item-name').removeClass('hidden');
					$actionItem.find('.return').addClass('hidden');

					$(options.container + ' .connectedSortable').find('.just-hidden').removeClass('hidden');

					prepareKanbanBoards();
				}
			});

			prepareKanbanBoard($sortableContainer);
		},
		closeActions: function(container, ui) {
			$(container + ' .kanban-action-wrapper').slideUp("slow", function() {
				$(this)
					.addClass('hidden')
					.removeAttr('style');

				var $actionItem = ui.item.parents('.kanban-action-item');
				$actionItem.find('.kanban-action-item-name').removeClass('hidden');
				$actionItem.find('.return').addClass('hidden');

				var itemId = $(ui.item[0]).data('id');
				$(ui.item[0]).removeClass('cancel-' + itemId);

				// Remove item
				$('.kanban-actions').find('li.cancel-' + itemId).remove();
				$('.kanban-actions li.just-hidden').remove();
			});
		},
		showKanban: function(container) {
			var $kanban = $(container + ' > .kanban-wrapper:first'),
				$prevNav = $('.horizon-prev', container),
				$nextNav = $('.horizon-next', container);

			// Оптимизация ховера: кешируем размеры
			$kanban.hover(
				function(event) {
					var domKanban = $kanban.get(0);
					if (domKanban.clientWidth < domKanban.scrollWidth - domKanban.scrollLeft &&
						!($prevNav.find(event.relatedTarget).length || $nextNav.find(event.relatedTarget).length)) {
						$nextNav.show();
					}
				},
				function(event) {
					if (!($prevNav.find(event.relatedTarget).length || $nextNav.find(event.relatedTarget).length)) {
						$nextNav.hide();
					}
				}
			);

			$.fn.horizon = function() {
				// Throttled scroll listener using rAF
				var ticking = false;
				$kanban.on({
					'touchmove scroll': function() {
						var self = this;
						if (!ticking) {
							raf(function() {
								showButtons(self.scrollLeft);
								ticking = false;
							});
							ticking = true;
						}
					}
				});

				// Click and hold action on nav buttons
				$nextNav.on({
					'mousedown touchstart': function() {
						if ($.fn.horizon.defaults.interval) clearInterval($.fn.horizon.defaults.interval);
						$.fn.horizon.defaults.interval = setInterval(function() {
							scrollLeft();
						}, 50);
					},
					'mouseup touchend': function() {
						clearInterval($.fn.horizon.defaults.interval);
					}
				});

				$prevNav.on({
					'mousedown touchstart': function() {
						if ($.fn.horizon.defaults.interval) clearInterval($.fn.horizon.defaults.interval);
						$.fn.horizon.defaults.interval = setInterval(function() {
							scrollRight();
						}, 50);
					},
					'mouseup touchend': function() {
						clearInterval($.fn.horizon.defaults.interval);
					}
				});

				showButtons($.fn.horizon.defaults.interval);
			};

			$.fn.horizon.defaults = {
				delta: 0,
				interval: 0
			};

			var scrollLeft = function() {
				var i2 = $.fn.horizon.defaults.delta + 1;
				$kanban.scrollLeft($kanban.scrollLeft() + (i2 * 30));
				// Buttons update handled by scroll listener
			};

			var scrollRight = function() {
				var i2 = $.fn.horizon.defaults.delta - 1;
				$kanban.scrollLeft($kanban.scrollLeft() + (i2 * 30));
				// Buttons update handled by scroll listener
			};

			var showButtons = function(index) {
				var domKanban = $kanban.get(0);
				// Safety check
				if (!domKanban) return;

				var maxScroll = domKanban.scrollWidth - domKanban.scrollLeft;
				var clientWidth = domKanban.clientWidth;

				if (index === 0) {
					if ($.fn.horizon.defaults.interval) {
						$prevNav.hide(function() {
							clearInterval($.fn.horizon.defaults.interval);
						});
					} else {
						$prevNav.hide();
					}

					if (clientWidth < maxScroll) {
						$nextNav.show();
					}
				} else if (clientWidth >= maxScroll) {
					$prevNav.show();
					if ($.fn.horizon.defaults.interval) {
						$nextNav.hide(function() {
							clearInterval($.fn.horizon.defaults.interval);
						});
					} else {
						$nextNav.hide();
					}
				} else {
					$nextNav.show();
					$prevNav.show();
				}
			};

			$kanban.horizon();
		},
	});
})(jQuery);

function prepareKanbanBoard(oKanbanBoard) {
	var oWindow = $(window),
		bottomKanbanBoard = oKanbanBoard.offset().top + oKanbanBoard.outerHeight(),
		kanbanBoardWrapper = oKanbanBoard.find('>.kanban-wrapper').filter(':visible'),
		oKanbanBoardMiniatureWrapper, oKanbanBoardMiniature, oMiniatureTransparent, oKanbanBoardMiniatureUl,
		kanbanColumns, aKanbanColumnsUlHeight = [],
		maxHeightKanbanColumnsUl, koef;

	if (kanbanBoardWrapper.length) {
		oKanbanBoardMiniatureWrapper = oKanbanBoard.find('.kanban-board-miniature-wrapper');

		if (kanbanBoardWrapper.get(0).scrollWidth > kanbanBoardWrapper.innerWidth()) {
			kanbanColumns = kanbanBoardWrapper.find('.kanban-col');

			if (oKanbanBoardMiniatureWrapper.length) {
				oKanbanBoardMiniature = oKanbanBoardMiniatureWrapper.find('.kanban-board-miniature');
				oMiniatureTransparent = oKanbanBoardMiniatureWrapper.find('.transparent');
				oKanbanBoardMiniatureUl = oKanbanBoardMiniatureWrapper.find('ul');
			} else {
				oKanbanBoardMiniatureWrapper = $('<div class="kanban-board-miniature-wrapper"></div>');
				oKanbanBoardMiniature = $('<div class="kanban-board-miniature"></div>');
				oMiniatureTransparent = $('<div class="transparent"></div>');
				oKanbanBoardMiniatureUl = $('<ul></ul>');

				oKanbanBoard.append(
					oKanbanBoardMiniatureWrapper.append(
						oKanbanBoardMiniature.append(oMiniatureTransparent, oKanbanBoardMiniatureUl)
					)
				);

				// Оптимизация: генерация строки HTML перед вставкой
				var strLi = '';
				for (var i = 0; i < kanbanColumns.length; i++) {
					strLi += '<li><span class="miniature-col-header" style="background-color: #79cc14;"></span><span class="miniature-col-content"></span></li>';
				}
				oKanbanBoardMiniatureUl.append(strLi);
			}

			oKanbanBoardMiniatureWrapper.css({
				top: bottomKanbanBoard < oWindow.outerHeight() ? bottomKanbanBoard - 65 : oWindow.outerHeight() - 75,
				left: oWindow.outerWidth() - oKanbanBoardMiniatureWrapper.outerWidth() - 35
			});

			var oKanbanBoardMiniatureLi = oKanbanBoardMiniatureUl.find('li');
			var headerColors = [];

			// Шаг 1: Считывание (Read DOM)
			kanbanColumns.each(function(index) {
				var $this = $(this);
				headerColors[index] = $this.find('.kanban-board-header > h5').css('background-color');
				aKanbanColumnsUlHeight[index] = $this.find('ul.kanban-list li').length ? $this.find('ul.kanban-list').outerHeight() : 0;
			});

			// Шаг 2: Запись (Write DOM) - предотвращение layout thrashing
			maxHeightKanbanColumnsUl = Math.max.apply(null, aKanbanColumnsUlHeight);
			if (maxHeightKanbanColumnsUl === 0) maxHeightKanbanColumnsUl = 1; // защита от деления на ноль

			oKanbanBoardMiniatureLi.each(function(index) {
				var $oLi = $(this);
				$oLi.find('span.miniature-col-header').css('background-color', headerColors[index]);

				var heightLi = $oLi.innerHeight() - $oLi.find('.miniature-col-header').outerHeight(true);
				$oLi.find('.miniature-col-content').css('height', aKanbanColumnsUlHeight[index] / maxHeightKanbanColumnsUl * heightLi - 1);
			});

			koef = kanbanBoardWrapper.get(0).scrollWidth / oKanbanBoardMiniature.innerWidth();
			oMiniatureTransparent.outerWidth(kanbanBoardWrapper.innerWidth() / koef - 2);

			if (!oKanbanBoard.data('hasEventListeners')) {
				oKanbanBoardMiniatureWrapper.on('mousedown touchstart', function(e) {
					e.preventDefault();
					e.stopPropagation();

					$(this)
						.data({
							currentMousePositionX: e.type == 'touchstart' ? e.originalEvent.touches[0].pageX : e.pageX,
							currentMousePositionY: e.type == 'touchstart' ? e.originalEvent.touches[0].pageY : e.pageY
						})
						.attr('data-mousedown', true);

					$(window).one('mouseup touchend', function() {
						oKanbanBoardMiniatureWrapper
							.removeData(['currentMousePositionX', 'currentMousePositionY'])
							.removeAttr('data-mousedown');

						$(this).off('mousemove touchmove');
					})
					.on('mousemove touchmove', function(e) {
						// Mousemove для перетаскивания самой миникарты
						if (oKanbanBoardMiniatureWrapper.attr('data-mousedown')) {
							var posX = e.type == 'touchmove' ? e.originalEvent.touches[0].pageX : e.pageX;
							var posY = e.type == 'touchmove' ? e.originalEvent.touches[0].pageY : e.pageY;

							var $wrapper = oKanbanBoardMiniatureWrapper;
							var deltaMousePositionX = $wrapper.data('currentMousePositionX') - posX + $wrapper.scrollLeft(); // Это вероятно ошибка оригинала (scroll у wrapper?), но логику оставил
							var deltaMousePositionY = $wrapper.data('currentMousePositionY') - posY + $wrapper.scrollTop();

							$wrapper.data({
								'currentMousePositionX': posX,
								'currentMousePositionY': posY
							});

							var offset = $wrapper.offset();

							// Используем requestAnimationFrame для визуальных обновлений если возможно, но здесь прямой CSS update
							$wrapper.css({
								'top': offset.top - deltaMousePositionY,
								'left': offset.left - deltaMousePositionX
							});
						}
					});
				});

				oMiniatureTransparent.on('mousedown touchstart', function(e) {
					e.preventDefault();
					e.stopPropagation();

					$(this)
						.data({
							currentMousePositionX: e.type == 'touchstart' ? e.originalEvent.touches[0].pageX : e.pageX
						})
						.attr('data-mousedown', true);

					oKanbanBoard.addClass('noselect');

					$(window).one('mouseup touchend', function() {
						oKanbanBoard.removeClass('noselect');
						oMiniatureTransparent
							.removeData('currentMousePositionX')
							.removeAttr('data-mousedown');
					});
				})
				.on('mousemove touchmove', function(e) {
					e.preventDefault();
					e.stopPropagation();

					var $this = $(this);

					if ($this.attr('data-mousedown')) {
						var posX = e.type == 'touchmove' ? e.originalEvent.touches[0].pageX : e.pageX;
						var deltaMousePosition = $this.data('currentMousePositionX') - posX;

						$this.data('currentMousePositionX', posX);

						var positionLeft = $this.position().left;
						var currentOffset = $this.offset();
						var deltaTransparentMove = 0;
						var oKanbanBoardMiniatureWidth = oKanbanBoardMiniature.innerWidth();

						// Сдвигаем влево
						if (deltaMousePosition > 0 && positionLeft > 0) {
							deltaTransparentMove = positionLeft > deltaMousePosition ? deltaMousePosition : positionLeft;
						} else if (deltaMousePosition < 0) // Сдвигаем вправо
						{
							var difference = oKanbanBoardMiniatureWidth - positionLeft - $this.outerWidth();
							if (difference > 1) {
								deltaTransparentMove = difference > Math.abs(deltaMousePosition) ? deltaMousePosition : -difference;
							}
						}

						if (deltaTransparentMove) {
							var offsetLeft = currentOffset.left - deltaTransparentMove;
							$this.offset({
								top: currentOffset.top,
								left: offsetLeft
							});
							kanbanBoardWrapper.scrollLeft($this.position().left * koef);
						}
					}
				});

				// Оптимизация скролла доски
				kanbanBoardWrapper
					.addClass('scrollable')
					.on('mousedown', function(e) {
						$(this)
							.data({
								currentMousePositionX: e.pageX
							})
							.attr('data-mousedown', true)
							.parent()
							.addClass('noselect');

						$(window).one('mouseup', function() {
							kanbanBoardWrapper
								.removeAttr('data-mousedown')
								.parent()
								.removeClass('noselect');
						});
					})
					.on('mousemove', function(e) {
						var $this = $(this);
						if ($this.attr('data-mousedown') && !$this.attr('data-draggingItem')) {
							var deltaMousePosition = $this.data('currentMousePositionX') - e.pageX;
							var currentScroll = $this.scrollLeft();

							if (deltaMousePosition > 0 && (currentScroll < $this.get(0).scrollWidth - $this.innerWidth())) {
								$this.scrollLeft(currentScroll + deltaMousePosition);
							} else if (currentScroll > 0) {
								$this.scrollLeft(currentScroll + deltaMousePosition);
							}

							$this.data('currentMousePositionX', e.pageX);
						}
					})
					.on('scroll', function() {
						if (!oMiniatureTransparent.attr('data-mousedown')) {
							var self = this;
							// rAF для синхронизации прозрачного ползунка
							window.requestAnimationFrame(function() {
								oMiniatureTransparent.css('left', $(self).scrollLeft() / koef);
							});
						}
					});

				oKanbanBoard.data('hasEventListeners', true);
			}
		} else {
			kanbanBoardWrapper.removeClass('scrollable');
			if (oKanbanBoardMiniatureWrapper.length) oKanbanBoardMiniatureWrapper.remove();
		}
	}
}

function prepareKanbanBoards() {
	$('.kanban-board:visible').each(function() {
		prepareKanbanBoard($(this));
	});
}