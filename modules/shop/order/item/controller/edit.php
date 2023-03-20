<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Item_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('hash')
			->addSkipColumn('shop_item_digital_id');

		if (!$object->id)
		{
			$object->shop_order_id = Core_Array::getGet('shop_order_id');
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oShop = Core_Entity::factory('Shop', intval(Core_Array::getGet('shop_id')));

		$oMainTab->move($this->getField('price')
				->id('itemPrice')
				->divAttr(array('class' => 'form-group col-sm-2 col-xs-5')),
			$oMainRow1
		);

		$oMainTab->move($this->getField('quantity')->divAttr(array('class' => 'form-group col-sm-2 col-xs-12')), $oMainRow1);

		$oAdditionalTab->delete($this->getField('shop_measure_id'));

		// Добавляем единицы измерения
		$oMeasuresField = Admin_Form_Entity::factory('Select')
			->id('itemMeasure')
			->name('shop_measure_id')
			->caption(Core::_('Shop_Item.shop_measure_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-2'))
			->options(
				Shop_Controller::fillMeasures()
			)
			->value($this->_object->shop_measure_id);

		$oMainRow1->add($oMeasuresField);

		$oMainTab->move($this->getField('rate')
				->id('itemRate')
				->divAttr(array('class' => 'form-group col-xs-5 col-sm-2')),
			$oMainRow1
		);
		$oMainRow1->add(Admin_Form_Entity::factory('Span')
			->value('%')
			->style("font-size: 200%")
			->divAttr(array(
				'class' => 'form-group col-xs-1',
				'style' => 'padding-top: 20px'
			))
		);

		$this->getField('name')->id('itemInput')->format(array('minlen' => array('value' => 0)));

		$oMainTab->moveAfter($this->getField('rate'), $oMeasuresField);

		$oMainTab->move($this->getField('marking')->id('itemMarking')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);

		$oAdditionalTab->delete($this->getField('shop_warehouse_id'));

		$aWarehousesList = self::fillWarehousesList($oShop);

		if (count($aWarehousesList) < Core::$mainConfig['switchSelectToAutocomplete'])
		{
			$oMainRow2->add(
				Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Order_Item.shop_warehouse_id'))
					->options($aWarehousesList)
					->name('shop_warehouse_id')
					->value($this->_object->shop_warehouse_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-5'))
			);
		}
		else
		{
			$oShopWarehouseInput = Admin_Form_Entity::factory('Input')
				->caption(Core::_('Shop_Order_Item.shop_warehouse_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-5'))
				->name('warehouse_name');

			if ($this->_object->shop_warehouse_id)
			{
				$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse', $this->_object->shop_warehouse_id);
				$oShopWarehouseInput->value('[' . $oShop_Warehouse->id . '] ' . $oShop_Warehouse->name);
			}

			$oShopWarehouseInputHidden = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->name('shop_warehouse_id')
				->value($this->_object->shop_warehouse_id)
				->type('hidden');

			$oCore_Html_Entity_Script_Modification = Core_Html_Entity::factory('Script')
			->value("
				$('#{$windowId} [name = warehouse_name]').autocomplete({
					source: function(request, response) {
						$.ajax({
							url: '/admin/shop/order/item/index.php?autocomplete=1&show_warehouse=1&shop_id={$this->_object->Shop_Item->shop_id}',
							dataType: 'json',
							data: {
								queryString: request.term
							},
							success: function(data) {
								response(data);
							}
						});
					},
					minLength: 1,
					create: function() {
						$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
							return $('<li></li>')
								.data('item.autocomplete', item)
								.append($('<a>').text(item.label))
								.appendTo(ul);
						}
						$(this).prev('.ui-helper-hidden-accessible').remove();
					},
					select: function(event, ui) {
						$('#{$windowId} [name = shop_warehouse_id]').val(ui.item.id);
					},
					open: function() {
						$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
					},
					close: function() {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					}
				});
			");

			$oMainRow2
				->add($oShopWarehouseInput)
				->add($oShopWarehouseInputHidden)
				->add($oCore_Html_Entity_Script_Modification);
		}

		$oMainTab->delete($this->getField('type'));

		$oMainRow1->add(
			Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Order_Item.type'))
				->options(
					array(
						Core::_('Shop_Order_Item.order_item_type_caption0'),
						Core::_('Shop_Order_Item.order_item_type_caption1'),
						Core::_('Shop_Order_Item.order_item_type_caption2'),
						Core::_('Shop_Order_Item.order_item_type_caption3'),
						Core::_('Shop_Order_Item.order_item_type_caption4'),
						Core::_('Shop_Order_Item.order_item_type_caption5'),
						Core::_('Shop_Order_Item.order_item_type_caption6')
					)
				)
				->name('type')
				->value($this->_object->type)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
		);

		// $oAdditionalTab->move($this->getField('shop_item_id'), $oMainTab);
		// $oMainTab->move($this->getField('shop_item_id')->id('itemId')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);
		$this->getField('shop_item_id')->id('itemId');

		$oAdditionalTab->delete($this->getField('shop_order_item_status_id'));

		$oDropdownlistStatuses = Admin_Form_Entity::factory('Dropdownlist')
			->options(Shop_Order_Item_Status_Controller_Edit::getDropdownlistOptions($oShop->id))
			->name('shop_order_item_status_id')
			->value($this->_object->shop_order_item_status_id)
			->caption(Core::_('Shop_Order_Item.shop_order_item_status_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oDropdownlistStatuses);

		$oCore_Html_Entity_Script = Core_Html_Entity::factory('Script')
			// &shop_order_id= может использоваться в хуках, когда цена товара зависит от опций заказа (страна, город и т.д.)
			->value("$('#{$windowId} #itemInput').autocompleteShopItem({ shop_id: '{$oShop->id}', shop_currency_id: '{$oShop->shop_currency_id}', shop_order_id: '{$this->_object->shop_order_id}' }, function(event, ui) {
				$('#{$windowId} #itemId').val(typeof ui.item.id !== 'undefined' ? ui.item.id : 0);
				$('#{$windowId} #itemPrice').val(typeof ui.item.price !== 'undefined' ? ui.item.price : 0);
				$('#{$windowId} #itemMeasure').val(typeof ui.item.measure_id !== 'undefined' ? ui.item.measure_id : 0);
				$('#{$windowId} #itemRate').val(typeof ui.item.rate !== 'undefined' ? ui.item.rate : 0);
				$('#{$windowId} #itemMarking').val(typeof ui.item.marking !== 'undefined' ? ui.item.marking : 0);
			});");

		$oMainTab->add($oCore_Html_Entity_Script);

		$oShop_Order = $this->_object->Shop_Order;

		$this->title($this->_object->id
			? Core::_('Shop_Order_Item.order_items_edit_form_title', $oShop_Order->invoice, FALSE)
			: Core::_('Shop_Order_Item.order_items_add_form_title', $oShop_Order->invoice, FALSE)
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Order_Item_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$this->_object->Shop_Order->backupRevision();
		}

		// New order item
		if (!$this->_object->id)
		{
			$shop_item_id = Core_Array::get($this->_formValues, 'shop_item_id');

			if ($shop_item_id &&
				!is_null($oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id, FALSE)))
			{
				Core_Array::get($this->_formValues, 'name') == '' && $this->_formValues['name'] = $oShop_Item->name;
				floatval(Core_Array::get($this->_formValues, 'quantity')) == 0.0 && $this->_formValues['quantity'] = 1.0;
				floatval(Core_Array::get($this->_formValues, 'price')) == 0.0 && $this->_formValues['price'] = $oShop_Item->price;
				Core_Array::get($this->_formValues, 'marking') == '' && $this->_formValues['marking'] = $oShop_Item->marking;
			}
		}

		if ($this->_object->id)
		{
			$bChangedStatus = $this->_object->shop_order_item_status_id != Core_Array::get($this->_formValues, 'shop_order_item_status_id', 0);
		}
		else
		{
			$bChangedStatus = FALSE;
		}

		parent::_applyObjectProperty();

		// Reset `unloaded`
		$this->_object->Shop_Order
			->unloaded(0)
			->save();

		// Reserved
		$this->_object->Shop_Order->Shop->reserve
			&& !$this->_object->Shop_Order->paid && !$this->_object->Shop_Order->canceled && !$this->_object->Shop_Order->posted
			&& $this->_object->Shop_Order->reserveItems();

		// Reset 'posted'
		if ($this->_object->Shop_Order->posted)
		{
			$this->_object->Shop_Order->posted = 0;
			$this->_object->Shop_Order->post();
		}

		if ($bChangedStatus && $this->_object->shop_order_item_status_id)
		{
			$this->_object->historyPushChangeItemStatus();
		}

		$this->_object->Shop_Order->checkShopOrderItemStatuses();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Fill warehouses list
	 * @param object $oShop shop object
	 * @return array
	 */
	static public function fillWarehousesList($oShop, $like = NULL)
	{
		$aReturn = array(" … ");

		$oShop_Warehouses = $oShop->Shop_Warehouses;
		$oShop_Warehouses->queryBuilder()
			->clearOrderBy()
			->orderBy('shop_warehouses.sorting')
			->orderBy('shop_warehouses.id');

		if ($like != '')
		{
			$like = Core_DataBase::instance()->escapeLike($like);

			$oShop_Warehouses->queryBuilder()
				->open()
					->where('shop_warehouses.name', 'LIKE', '%' . $like . '%')
					->setOr()
					->where('shop_warehouses.id', 'LIKE', '%' . $like . '%')
				->close()
				->limit(10);
		}

		$aShop_Warehouses = $oShop_Warehouses->findAll(FALSE);
		foreach ($aShop_Warehouses as $oShop_Warehouse)
		{
			$aReturn[$oShop_Warehouse->id] = '[' . $oShop_Warehouse->id . '] ' . $oShop_Warehouse->name;
		}

		return $aReturn;
	}
}