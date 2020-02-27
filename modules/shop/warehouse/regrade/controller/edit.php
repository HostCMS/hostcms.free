<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Regrade Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Regrade_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'))->class('form-control input-lg'), $oMainRow1);

		// Печать
		$printlayoutsButton = '
			<div class="btn-group">
				<a class="btn btn-labeled btn-success" href="javascript:void(0);"><i class="btn-label fa fa-print"></i>' . Core::_('Printlayout.print') . '</a>
				<a class="btn btn-palegreen dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-palegreen">
		';

		$moduleName = $oAdmin_Form_Controller->module->getModuleName();

		$oModule = Core_Entity::factory('Module')->getByPath($moduleName);

		if (!is_null($oModule))
		{
			$printlayoutsButton .= Printlayout_Controller::getPrintButtonHtml($this->_Admin_Form_Controller, $oModule->id, 4, 'hostcms[checked][0][' . $this->_object->id . ']=1&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id);
		}

		$printlayoutsButton .= '
				</ul>
			</div>
		';

		$oMainRow1
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12 col-sm-4 col-lg-3 margin-top-21 text-align-center print-button' . (!$this->_object->id ? ' hidden' : ''))
				->add(
					Admin_Form_Entity::factory('Code')->html($printlayoutsButton)
				)
		);

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		$oAdditionalTab->delete($this->getField('shop_warehouse_id'));

		$oDefault_Shop_Warehouse = $oShop->Shop_Warehouses->getDefault();

		$oShop_Warehouse_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Warehouse_Regrade.shop_warehouse_id'))
			->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-3')
			)
			->options(Shop_Warehouse_Controller_Edit::fillWarehousesList($oShop))
			->class('form-control select-warehouse')
			->name('shop_warehouse_id')
			->value($this->_object->id
				? $this->_object->shop_warehouse_id
				: (!is_null($oDefault_Shop_Warehouse) ? $oDefault_Shop_Warehouse->id : 0)
			);

		$oMainRow2->add($oShop_Warehouse_Select);

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
			->caption(Core::_('Shop_Warehouse_Writeoff.user_id'))
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
					/* ->add(
						Admin_Form_Entity::factory('Div')
							->class('hidden-sm hidden-md hidden-lg padding-top-40')
					) */
					->add($oSelectResponsibleUsers)
					->class('form-group col-xs-12 col-sm-5 col-lg-4')
			)
			->add($oScriptResponsibleUsers);

		$oMainTab->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-lg-3 margin-top-21')), $oMainRow2);

		$oMainTab->delete($this->getField('shop_price_id'));

		$oShop_Price_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Warehouse_Writeoff.shop_price_id'))
			->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-3')
			)
			->options(self::fillPricesList($oShop))
			->class('form-control select-price')
			->name('shop_price_id')
			->value($this->_object->id
				? $this->_object->shop_price_id
				: 0
			);

		$oMainRow3->add($oShop_Price_Select);

		$oRecalcPriceLink = Admin_Form_Entity::factory('Link');
		$oRecalcPriceLink
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21'))
			->a
				->class('btn btn-default')
				->onclick("$.recalcPrice()")
				->value(Core::_('Shop_Warehouse_Regrade.recalc_price'));
		$oRecalcPriceLink
			->icon
				->class('fa fa-recycle');

		$oMainRow3->add($oRecalcPriceLink);

		$placeholder = Core::_('Shop_Warehouse_Regrade.add_item_placeholder');

		$oAddItemLink = Admin_Form_Entity::factory('Link');
		$oAddItemLink
			->divAttr(array('class' => 'add-regrade-item-link'))
			->a
				->class('btn btn-xs btn-azure')
				->onclick("$.addRegradeItem({$oShop->id}, '{$placeholder}')")
				->value(Core::_('Shop_Warehouse_Regrade.add_item'));
		$oAddItemLink
			->icon
				->class('fa fa-plus');

		$oShopItemBlock
			->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
				->class('header bordered-palegreen')
				->value(Core::_('Shop_Warehouse_Regrade.shop_item_header'))
				->add($oAddItemLink)
			)
			->add($oShopItemRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$itemTable = '
			<div class="table-scrollable">
				<table class="table table-striped table-hover shop-item-table deals-aggregate-user-info shop-warehouse-regrade-table">
					<thead>
						<tr>
							<th scope="col" rowspan="2">' . Core::_('Shop_Warehouse_Regrade.position') . '</th>
							<th scope="col" colspan="3" class="regrade-bottom-writeoff">' . Core::_('Shop_Warehouse_Regrade.writeoff_item') . '</th>
							<th scope="col" colspan="3" class="regrade-bottom-incoming">' . Core::_('Shop_Warehouse_Regrade.incoming_item') . '</th>
							<th scope="col" rowspan="2">' . Core::_('Shop_Warehouse_Regrade.quantity') . '</th>
							<th scope="col" rowspan="2">  </th>
						</tr>
						<tr>
							<th scope="col">' . Core::_('Shop_Warehouse_Regrade.name') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Regrade.measure') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Regrade.price') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Regrade.name') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Regrade.measure') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Regrade.price') . '</th>
						</tr>
					</thead>
					<tbody>
		';

		$index = 0;

		$limit = 100;
		$offset = 0;

		do {
			$oShop_Warehouse_Regrade_Items = $this->_object->Shop_Warehouse_Regrade_Items;
			$oShop_Warehouse_Regrade_Items->queryBuilder()
				->limit($limit)
				->offset($offset)
				->clearOrderBy()
				->orderBy('shop_warehouse_regrade_items.id');

			$aShop_Warehouse_Regrade_Items = $oShop_Warehouse_Regrade_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Regrade_Items as $oShop_Warehouse_Regrade_Item)
			{
				$oShop_Item_Writeoff = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Regrade_Item->writeoff_shop_item_id);
				$oShop_Item_Incoming = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Regrade_Item->incoming_shop_item_id);

				if (!is_null($oShop_Item_Writeoff) && !is_null($oShop_Item_Incoming))
				{
					$oShop_Item_Writeoff = $oShop_Item_Writeoff->shortcut_id
						? $oShop_Item_Writeoff->Shop_Item
						: $oShop_Item_Writeoff;

					$oShop_Item_Incoming = $oShop_Item_Incoming->shortcut_id
						? $oShop_Item_Incoming->Shop_Item
						: $oShop_Item_Incoming;

					$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteShopItem', NULL, 0, $oShop_Item_Writeoff->id, "shop_warehouse_regrade_item_id={$oShop_Warehouse_Regrade_Item->id}");

					$itemTable .= '
						<tr id="' . $oShop_Warehouse_Regrade_Item->id . '" data-item-id="' . $oShop_Item_Writeoff->id . ',' . $oShop_Item_Incoming->id . '">
							<td class="index">' . ++$index . '</td>
							<td><input class="writeoff-item-autocomplete form-control" data-type="writeoff" value="' . htmlspecialchars($oShop_Item_Writeoff->name) . '" /></td>
							<td>' . htmlspecialchars($oShop_Item_Writeoff->Shop_Measure->name) . '</td>
							<td><span class="writeoff-price">' . htmlspecialchars($oShop_Warehouse_Regrade_Item->writeoff_price) . '</span></td>
							<td><input class="incoming-item-autocomplete form-control" data-type="incoming" value="' . htmlspecialchars($oShop_Item_Incoming->name) . '" /></td>
							<td>' . htmlspecialchars($oShop_Item_Incoming->Shop_Measure->name) . '</td>
							<td><span class="incoming-price">' . htmlspecialchars($oShop_Warehouse_Regrade_Item->incoming_price) . '</span></td>
							<td width="80"><input class="set-item-count form-control" name="shop_item_quantity_' . $oShop_Warehouse_Regrade_Item->id . '" value="' . $oShop_Warehouse_Regrade_Item->count . '" /></td>
							<td><a class="delete-associated-item" onclick="res = confirm(\'' . Core::_('Shop_Warehouse_Regrade.delete_dialog') . '\'); if (res) {' . $onclick . '} return res;"><i class="fa fa-times-circle darkorange"></i></a></td>
						</tr>
					';
				}
			}

			$offset += $limit;
		}
		while (count($aShop_Warehouse_Regrade_Items));

		$itemTable .= '
					</tbody>
				</table>
			</div>
		';

		// $placeholder = Core::_('Shop_Warehouse_Regrade.add_item_placeholder');

		/*$oAddItemLink = Admin_Form_Entity::factory('Link');
				$oAddItemLink
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
					->a
						->class('btn btn-palegreen')
						->onclick("$.addRegradeItem({$oShop->id}, '{$placeholder}')")
						->value(Core::_('Shop_Warehouse_Regrade.add_item'));
				$oAddItemLink
					->icon
						->class('fa fa-plus');

		$oShopItemRow2->add($oAddItemLink);*/

		$oShopItemRow2
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add(
					Admin_Form_Entity::factory('Code')->html($itemTable)
				)
		);

		$title = $this->_object->id
			? Core::_('Shop_Warehouse_Regrade.form_edit', $this->_object->number)
			: Core::_('Shop_Warehouse_Regrade.form_add');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Warehouse_Regrade_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'shop_warehouse_regrade'
				&& $this->_object->backupRevision();
		}

		$this->addSkipColumn('posted');

		$iOldWarehouse = intval($this->_object->shop_warehouse_id);

		$this->_object->user_id = intval(Core_Array::getPost('user_id'));

		parent::_applyObjectProperty();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		if ($this->_object->id)
		{
			$this->addMessage("<script>$.showPrintButton('{$windowId}', {$this->_object->id})</script>");
		}

		if ($this->_object->number == '')
		{
			$this->_object->number = $this->_object->id;
			$this->_object->save();
		}

		$bNeedsRePost = FALSE;

		// Существующие товары
		$aShop_Warehouse_Regrade_Items = $this->_object->Shop_Warehouse_Regrade_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Regrade_Items as $oShop_Warehouse_Regrade_Item)
		{
			$oShop_Item_Writeoff = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Regrade_Item->writeoff_shop_item_id);
			$oShop_Item_Incoming = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Regrade_Item->incoming_shop_item_id);

			if (!is_null($oShop_Item_Writeoff) && !is_null($oShop_Item_Incoming))
			{
				$oShop_Item_Writeoff = $oShop_Item_Writeoff->shortcut_id
					? $oShop_Item_Writeoff->Shop_Item
					: $oShop_Item_Writeoff;

				$oShop_Item_Incoming = $oShop_Item_Incoming->shortcut_id
					? $oShop_Item_Incoming->Shop_Item
					: $oShop_Item_Incoming;

				$quantity = Core_Array::getPost('shop_item_quantity_' . $oShop_Warehouse_Regrade_Item->id, 0);

				$oShop_Warehouse_Regrade_Item->count != $quantity && $bNeedsRePost = TRUE;

				$writeoff_price = $oShop_Item_Writeoff->loadPrice($this->_object->shop_price_id);
				$incoming_price = $oShop_Item_Incoming->loadPrice($this->_object->shop_price_id);

				$oShop_Warehouse_Regrade_Item->count = $quantity;
				$oShop_Warehouse_Regrade_Item->writeoff_price = $writeoff_price;
				$oShop_Warehouse_Regrade_Item->incoming_price = $incoming_price;
				$oShop_Warehouse_Regrade_Item->save();
			}
		}

		$aWriteoffItems = Core_Array::getPost('writeoff_item', array());
		$aIncomingItems = Core_Array::getPost('incoming_item', array());

		count($aWriteoffItems) && $bNeedsRePost = TRUE;

		// Новые товары
		foreach ($aWriteoffItems as $key => $writeoff_shop_item_id)
		{
			$incoming_shop_item_id = isset($aIncomingItems[$key]) ? $aIncomingItems[$key] : 0;

			$iCount = $this->_object->Shop_Warehouse_Regrade_Items->getCountBywriteoff_shop_item_id($writeoff_shop_item_id);

			if (!$iCount)
			{
				$oShop_Item_Writeoff = Core_Entity::factory('Shop_Item')->getById($writeoff_shop_item_id);
				$oShop_Item_Incoming = Core_Entity::factory('Shop_Item')->getById($incoming_shop_item_id);

				ob_start();

				$script = "$(\"#{$windowId} input[name='writeoff_item\\[\\]']\").eq(0).remove(); $(\"#{$windowId} input[name='incoming_item\\[\\]']\").eq(0).remove();";

				if (!is_null($oShop_Item_Writeoff) && !is_null($oShop_Item_Incoming))
				{
					$oShop_Item_Writeoff = $oShop_Item_Writeoff->shortcut_id
						? $oShop_Item_Writeoff->Shop_Item
						: $oShop_Item_Writeoff;

					$oShop_Item_Incoming = $oShop_Item_Incoming->shortcut_id
						? $oShop_Item_Incoming->Shop_Item
						: $oShop_Item_Incoming;

					$writeoff_price = $oShop_Item_Writeoff->loadPrice($this->_object->shop_price_id);
					$incoming_price = $oShop_Item_Incoming->loadPrice($this->_object->shop_price_id);

					$count = isset($_POST['shop_item_quantity'][$key]) && is_numeric($_POST['shop_item_quantity'][$key])
						? $_POST['shop_item_quantity'][$key]
						: 0;

					$oShop_Warehouse_Regrade_Item = Core_Entity::factory('Shop_Warehouse_Regrade_Item');
					$oShop_Warehouse_Regrade_Item
						->shop_warehouse_regrade_id($this->_object->id)
						->writeoff_shop_item_id($writeoff_shop_item_id)
						->incoming_shop_item_id($incoming_shop_item_id)
						->count($count)
						->writeoff_price($writeoff_price)
						->incoming_price($incoming_price)
						->save();

					$script .= "$(\"#{$windowId} input[name='shop_item_quantity\\[\\]']\").eq(0).attr('name', 'shop_item_quantity_{$oShop_Warehouse_Regrade_Item->id}');";
				}
				else
				{
					$script .= "$(\"#{$windowId} input[name='shop_item_quantity\\[\\]']\").eq(0).remove();";
				}

				Core::factory('Core_Html_Entity_Script')
					->value($script)
					->execute();
				$this->_Admin_Form_Controller->addMessage(ob_get_clean());
			}
		}

		($bNeedsRePost || !Core_Array::getPost('posted')) && $this->_object->unpost();
		Core_Array::getPost('posted') && $this->_object->post();

		if ($iOldWarehouse != $this->_object->shop_warehouse_id)
		{
			$aOld_Shop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse', $iOldWarehouse)->Shop_Warehouse_Entries->getByDocument($this->_object->id, 3);

			foreach ($aOld_Shop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$oShop_Warehouse_Entry->delete();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Fill prices list
	 * @return array
	 */
	public function fillPricesList(Shop_Model $oShop)
	{
		$aReturn = array(Core::_('Shop_Warehouse_Incoming.basic'));

		// if (Core::moduleIsActive('siteuser'))
		// {
			$aShop_Prices = $oShop->Shop_Prices->findAll();

			foreach ($aShop_Prices as $oShop_Price)
			{
				$aReturn[$oShop_Price->id] = $oShop_Price->name . ' [' . $oShop_Price->id . ']';
			}
		// }

		return $aReturn;
	}
}