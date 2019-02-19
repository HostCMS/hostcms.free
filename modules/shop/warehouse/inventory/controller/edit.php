<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Inventory Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Inventory_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
		$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))->class('input-lg'), $oMainRow1);

		// Печать
		$moduleName = $oAdmin_Form_Controller->module->getModuleName();

		$oModule = Core_Entity::factory('Module')->getByPath($moduleName);

		if (!is_null($oModule))
		{
			$printlayoutsButton = Printlayout_Controller::getPrintButtonHtml($this->_Admin_Form_Controller, $oModule->id, 3, 'hostcms[checked][0][' . $this->_object->id . ']=1&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id);

			$oMainRow1
				->add(Admin_Form_Entity::factory('Div')
					->class('form-group col-xs-12 col-sm-3 margin-top-21 margin-left-50')
					->add(
						Admin_Form_Entity::factory('Code')->html($printlayoutsButton)
					)
			);
		}

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		$oAdditionalTab->delete($this->getField('shop_warehouse_id'));

		$oDefault_Shop_Warehouse = $oShop->Shop_Warehouses->getDefault();

		$oShop_Warehouse_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Warehouse_Inventory.shop_warehouse_id'))
			->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-3')
			)
			->options(self::fillWarehousesList($oShop))
			->class('form-control select-warehouse')
			->name('shop_warehouse_id')
			->value($this->_object->id
				? $this->_object->shop_warehouse_id
				: (!is_null($oDefault_Shop_Warehouse) ? $oDefault_Shop_Warehouse->id : 0)
			);

		$oMainRow2->add($oShop_Warehouse_Select);

		// $oMainTab->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);

		// Удаляем поле с идентификатором ответственного сотрудника
		$oAdditionalTab->delete($this->getField('user_id'));

		$aSelectResponsibleUsers = array();

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aCompanies = $oSite->Companies->findAll();
		foreach ($aCompanies as $oCompany)
		{
			$oOptgroupCompany = new stdClass();
			$oOptgroupCompany->attributes = array('label' => htmlspecialchars($oCompany->name), 'class' => 'company');
			$oOptgroupCompany->children = $oCompany->fillDepartmentsAndUsers($oCompany->id);

			$aSelectResponsibleUsers[] = $oOptgroupCompany;
		}

		$oSelectResponsibleUsers = Admin_Form_Entity::factory('Select')
			->id('user_id')
			->options($aSelectResponsibleUsers)
			->name('user_id')
			->value($this->_object->user_id)
			->caption(Core::_('Deal.user_id'))
			->divAttr(array('class' => ''));

		$oScriptResponsibleUsers = Admin_Form_Entity::factory('Script')
			->value('$("#user_id").selectUser({
						placeholder: "",
						language: "' . Core_i18n::instance()->getLng() . '"
					});'
			);

		$oMainRow2
			->add(
				Admin_Form_Entity::factory('Div')
					->add(
						Admin_Form_Entity::factory('Div')
							->class('hidden-sm hidden-md hidden-lg padding-top-40')
					)
					->add($oSelectResponsibleUsers)
					->class('form-group col-xs-12 col-sm-4')
			)
			->add($oScriptResponsibleUsers);

		$oMainTab->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2 margin-top-21')), $oMainRow2);

		$oShopItemBlock
			->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
				->class('header bordered-palegreen')
				->value(Core::_('Shop_Warehouse_Inventory.shop_item_header'))
			)
			->add($oShopItemRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$itemTable = '
			<table class="table table-striped table-hover shop-item-table deals-aggregate-user-info">
				<thead>
					<tr>
						<th rowspan="2" scope="col">' . Core::_('Shop_Warehouse_Inventory.position') . '</th>
						<th rowspan="2" scope="col">' . Core::_('Shop_Warehouse_Inventory.name') . '</th>
						<th rowspan="2" scope="col">' . Core::_('Shop_Warehouse_Inventory.measure') . '</th>
						<th rowspan="2" scope="col">' . Core::_('Shop_Warehouse_Inventory.price') . '</th>
						<th rowspan="2" scope="col">' . Core::_('Shop_Warehouse_Inventory.currency') . '</th>
						<th colspan="3" class="border-bottom-success" scope="col">' . Core::_('Shop_Warehouse_Inventory.quantity') . '</th>
						<th colspan="3" scope="col">' . Core::_('Shop_Warehouse_Inventory.sum') . '</th>
						<th rowspan="2" scope="col">  </th>
					</tr>
					<tr>
						<th>Учет</th>
						<th>Факт</th>
						<th>Отклон</th>
						<th>Учет</th>
						<th>Факт</th>
						<th>Отклон</th>
					</tr>
				</thead>
				<tbody>
		';

		$aShop_Warehouse_Inventory_Items = $this->_object->Shop_Warehouse_Inventory_Items->findAll(FALSE);

		foreach ($aShop_Warehouse_Inventory_Items as $key => $oShop_Warehouse_Inventory_Item)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Inventory_Item->shop_item_id);

			if (!is_null($oShop_Item))
			{
				$oShop_Item = $oShop_Item->shortcut_id
					? $oShop_Item->Shop_Item
					: $oShop_Item;

				$currencyName = $oShop_Item->Shop_Currency->name;
				$measureName = $oShop_Item->Shop_Measure->name;

				$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteShopItem', NULL, 0, $oShop_Item->id, "shop_warehouse_inventory_item_id={$oShop_Warehouse_Inventory_Item->id}");

				$externalLink = '';

				$oSiteAlias = $oShop->Site->getCurrentAlias();
				if ($oSiteAlias)
				{
					$sItemUrl = ($oShop->Structure->https ? 'https://' : 'http://')
						. $oSiteAlias->name
						. $oShop->Structure->getPath()
						. $oShop_Item->getPath();

					$externalLink = '<a class="margin-left-5" target="_blank" href="' . $sItemUrl .  '"><i class="fa fa-external-link"></i></a>';
				}

				$itemTable .= '
					<tr id="' . $oShop_Warehouse_Inventory_Item->id . '" data-item-id="' . $oShop_Item->id . '">
						<td class="index">' . ($key + 1) . '</td>
						<td>' . htmlspecialchars($oShop_Item->name) . $externalLink . '</td>
						<td>' . htmlspecialchars($measureName) . '</td>
						<td><span class="price">' . htmlspecialchars($oShop_Item->price) . '</span></td>
						<td>' . htmlspecialchars($currencyName) . '</td>
						<td class="fact-warehouse-count"></td>
						<td width="80"><input class="set-item-count form-control" name="shop_item_quantity_' . $oShop_Warehouse_Inventory_Item->id . '" value="' . $oShop_Warehouse_Inventory_Item->count . '" /></td>
						<td class="diff-warehouse-count"></td>
						<td><span class="fact-warehouse-sum"></span></td>
						<td><span class="warehouse-inv-sum"></span></td>
						<td><span class="diff-warehouse-sum"></span></td>
						<td><a class="delete-associated-item" onclick="' . $onclick . '"><i class="fa fa-times-circle darkorange"></i></a></td>
					</tr>
				';
			}
		}

		$itemTable .= '
				</tbody>
			</table>
		';

		$oShopItemRow2->add(
			Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12'))
				->class('add-shop-item form-control')
				->name('set_item_name')
		);

		$oShopItemRow2
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add(
					Admin_Form_Entity::factory('Code')->html($itemTable)
				)
		);

		$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
			->value("$('.add-shop-item').autocompleteShopItem('{$oShop->id}', 0, function(event, ui) {
				$('.shop-item-table > tbody').append(
					$('<tr data-item-id=\"' + ui.item.id + '\"><td></td><td>' + ui.item.label + '<input type=\'hidden\' name=\'shop_item_id[]\' value=\'' + (typeof ui.item.id !== 'undefined' ? ui.item.id : 0) + '\'/>' + '</td><td>' + ui.item.measure + '</td><td><span class=\"price\">' + ui.item.price_with_tax + '</span></td><td>' + ui.item.currency + '</td><td><span class=\"fact-warehouse-count\"></span></td><td width=\"80\"><input class=\"set-item-count form-control\" name=\"shop_item_quantity[]\" value=\"\"/></td><td class=\"diff-warehouse-count\"></td><td><span class=\"fact-warehouse-sum\"></span></td><td><span class=\"warehouse-inv-sum\"></span></td><td><span class=\"diff-warehouse-sum\"></span></td><td><a class=\"delete-associated-item\" onclick=\"$(this).parents(\'tr\').remove()\"><i class=\"fa fa-times-circle darkorange\"></i></a></td></tr>')
				);
				ui.item.value = '';
				$.setWarehouseCounts($('select.select-warehouse').val());
				$.changeWarehouseCounts($('.set-item-count'), 0);
				$('.shop-item-table tr:last-child').find('.set-item-count').focus();
				$.focusAutocomplete($('.set-item-count'));
			  });

			  $.changeWarehouseCounts($('.set-item-count'), 0);

			  $.setWarehouseCounts($('select.select-warehouse').val());

			  $.focusAutocomplete($('.set-item-count'));

			  $('select.select-warehouse').change(function() {
				$.setWarehouseCounts($(this).val());
			  });
			  ");

		$oShopItemRow2->add($oCore_Html_Entity_Script);

		$title = $this->_object->id
			? Core::_('Shop_Warehouse_Inventory.form_edit', $this->_object->number)
			: Core::_('Shop_Warehouse_Inventory.form_add');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Warehouse_Inventory_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$iOldWarehouse = intval($this->_object->shop_warehouse_id);

		parent::_applyObjectProperty();

		// Существующие товары
		$aShop_Warehouse_Inventory_Items = $this->_object->Shop_Warehouse_Inventory_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Inventory_Items as $oShop_Warehouse_Inventory_Item)
		{
			$quantity = Core_Array::getPost('shop_item_quantity_' . $oShop_Warehouse_Inventory_Item->id);

			if ($quantity > 0)
			{
				$oShop_Warehouse_Inventory_Item->count = $quantity;
				$oShop_Warehouse_Inventory_Item->save();
			}
			else
			{
				$oShop_Warehouse_Inventory_Item->delete();
			}
		}

		// Новые товары
		$aAddShopItems = Core_Array::getPost('shop_item_id', array());
		foreach ($aAddShopItems as $key => $shop_item_id)
		{
			$iCount = $this->_object->Shop_Warehouse_Inventory_Items->getCountByshop_item_id($shop_item_id);

			if (!$iCount)
			{
				$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
				$oShop_Warehouse_Inventory_Item
					->shop_warehouse_inventory_id($this->_object->id)
					->shop_item_id($shop_item_id)
					->count(
						isset($_POST['shop_item_quantity'][$key]) ? $_POST['shop_item_quantity'][$key] : 1
					)
					->save();
			}
		}

		$oShop_Warehouse = $this->_object->Shop_Warehouse;

		$aShop_Warehouse_Entries = $oShop_Warehouse->Shop_Warehouse_Entries->getByDocument($this->_object->id, 0);

		// Не было проведено, проводим документ
		if ($this->_object->posted)
		{
			$aTmp = array();

			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$aTmp[$oShop_Warehouse_Entry->shop_item_id] = $oShop_Warehouse_Entry;
			}

			unset($aShop_Warehouse_Entries);

			$aShop_Warehouse_Inventory_Items = $this->_object->Shop_Warehouse_Inventory_Items->findAll(FALSE);
			foreach ($aShop_Warehouse_Inventory_Items as $oShop_Warehouse_Inventory_Item)
			{
				if (isset($aTmp[$oShop_Warehouse_Inventory_Item->shop_item_id]))
				{
					$oShop_Warehouse_Entry = $aTmp[$oShop_Warehouse_Inventory_Item->shop_item_id];
				}
				else
				{
					$oShop_Warehouse_Entry = Core_Entity::factory('Shop_Warehouse_Entry');
					$oShop_Warehouse_Entry->setDocument($this->_object->id, 0);
					$oShop_Warehouse_Entry->shop_item_id = $oShop_Warehouse_Inventory_Item->shop_item_id;
				}

				$oShop_Warehouse_Entry->shop_warehouse_id = $oShop_Warehouse->id;
				$oShop_Warehouse_Entry->datetime = $this->_object->datetime;
				$oShop_Warehouse_Entry->value = $oShop_Warehouse_Inventory_Item->count;
				$oShop_Warehouse_Entry->save();

				// Recount
				$oShop_Warehouse->setRest($oShop_Warehouse_Inventory_Item->shop_item_id, $oShop_Warehouse->getRest($oShop_Warehouse_Inventory_Item->shop_item_id));
			}
		}
		else
		{
			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$shop_item_id = $oShop_Warehouse_Entry->shop_item_id;
				$oShop_Warehouse_Entry->delete();

				// Recount
				$oShop_Warehouse->setRest($shop_item_id, $oShop_Warehouse->getRest($shop_item_id));
			}
		}

		if ($iOldWarehouse != $oShop_Warehouse->id)
		{
			$aOld_Shop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse', $iOldWarehouse)->Shop_Warehouse_Entries->getByDocument($this->_object->id, 0);

			foreach ($aOld_Shop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$oShop_Warehouse_Entry->delete();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Fill warehouses list
	 * @return array
	 */
	public function fillWarehousesList(Shop_Model $oShop)
	{
		$aReturn = array(' … ');

		$aShop_Warehouses = $oShop->Shop_Warehouses->findAll();

		foreach ($aShop_Warehouses as $oShop_Warehouse)
		{
			$aReturn[$oShop_Warehouse->id] = $oShop_Warehouse->name . ' [' . $oShop_Warehouse->id . ']';
		}

		return $aReturn;
	}
}