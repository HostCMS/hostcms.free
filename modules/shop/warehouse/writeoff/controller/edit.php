<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Writeoff Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Writeoff_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$printlayoutsButton .= Printlayout_Controller::getPrintButtonHtml($this->_Admin_Form_Controller, $oModule->id, 2, 'hostcms[checked][0][' . $this->_object->id . ']=1&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id);
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

		$oMainTab
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('reason')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		$oAdditionalTab->delete($this->getField('shop_warehouse_id'));

		$oDefault_Shop_Warehouse = $oShop->Shop_Warehouses->getDefault();

		$oShop_Warehouse_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Warehouse_Writeoff.shop_warehouse_id'))
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
					/*->add(
						Admin_Form_Entity::factory('Div')
							->class('hidden-sm hidden-md hidden-lg padding-top-40')
					)*/
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
				->onclick('$.recalcPrice()')
				->value(Core::_('Shop_Warehouse_Writeoff.recalc_price'));
		$oRecalcPriceLink
			->icon
				->class('fa fa-recycle');

		$oMainRow3->add($oRecalcPriceLink);

		$oShopItemBlock
			->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
				->class('header bordered-palegreen')
				->value(Core::_('Shop_Warehouse_Writeoff.shop_item_header'))
			)
			->add($oShopItemRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$itemTable = '
			<div class="table-scrollable">
				<table class="table table-striped table-hover shop-item-table deals-aggregate-user-info">
					<thead>
						<tr>
							<th scope="col">' . Core::_('Shop_Warehouse_Writeoff.position') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Writeoff.name') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Writeoff.measure') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Writeoff.price') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Writeoff.currency') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Writeoff.quantity') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Writeoff.sum') . '</th>
							<th scope="col">  </th>
						</tr>
					</thead>
					<tbody>
		';

		$oSiteAlias = $oShop->Site->getCurrentAlias();
		$sShopUrl = $oSiteAlias
			? ($oShop->Structure->https ? 'https://' : 'http://') . $oSiteAlias->name . $oShop->Structure->getPath()
			: NULL;

		$index = 0;

		$limit = 100;
		$offset = 0;

		do {
			$oShop_Warehouse_Writeoff_Items = $this->_object->Shop_Warehouse_Writeoff_Items;
			$oShop_Warehouse_Writeoff_Items->queryBuilder()
				->limit($limit)
				->offset($offset)
				->clearOrderBy()
				->orderBy('shop_warehouse_writeoff_items.id');

			$aShop_Warehouse_Writeoff_Items = $oShop_Warehouse_Writeoff_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Writeoff_Items as $oShop_Warehouse_Writeoff_Item)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Writeoff_Item->shop_item_id);

				if (!is_null($oShop_Item))
				{
					$oShop_Item = $oShop_Item->shortcut_id
						? $oShop_Item->Shop_Item
						: $oShop_Item;

					$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteShopItem', NULL, 0, $oShop_Item->id, "shop_warehouse_writeoff_item_id={$oShop_Warehouse_Writeoff_Item->id}");

					$externalLink = $sShopUrl
						? '<a class="margin-left-5" target="_blank" href="' . $sShopUrl . $oShop_Item->getPath() .  '"><i class="fa fa-external-link"></i></a>'
						: '';

					$sum = $oShop_Warehouse_Writeoff_Item->count * $oShop_Warehouse_Writeoff_Item->price;

					$itemTable .= '
						<tr id="' . $oShop_Warehouse_Writeoff_Item->id . '" data-item-id="' . $oShop_Item->id . '">
							<td class="index">' . ++$index . '</td>
							<td>' . htmlspecialchars($oShop_Item->name) . $externalLink . '</td>
							<td>' . htmlspecialchars($oShop_Item->Shop_Measure->name) . '</td>
							<td><span class="price">' . htmlspecialchars($oShop_Warehouse_Writeoff_Item->price) . '</span></td>
							<td>' . htmlspecialchars($oShop_Item->Shop_Currency->name) . '</td>
							<td width="80"><input class="set-item-count form-control" name="shop_item_quantity_' . $oShop_Warehouse_Writeoff_Item->id . '" value="' . $oShop_Warehouse_Writeoff_Item->count . '" /></td>
							<td><span class="calc-warehouse-sum">' . $sum . '</span></td>
							<td><a class="delete-associated-item" onclick="res = confirm(\'' . Core::_('Shop_Warehouse_Writeoff.delete_dialog') . '\'); if (res) {' . $onclick . '} return res;"><i class="fa fa-times-circle darkorange"></i></a></td>
						</tr>
					';
				}
			}

			$offset += $limit;
		}
		while (count($aShop_Warehouse_Writeoff_Items));

		$itemTable .= '
					</tbody>
				</table>
			</div>
		';

		$oShopItemRow2->add(
			Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12'))
				->class('add-shop-item form-control')
				->placeholder(Core::_('Shop_Warehouse_Writeoff.add_item_placeholder'))
				->name('set_item_name')
		)->add(
			Admin_Form_Entity::factory('Input')
				->class('index_value')
				->type('hidden')
				->name('index')
				->value($index)
		);

		$oShopItemRow2
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add(
					Admin_Form_Entity::factory('Code')->html($itemTable)
				)
		);

		$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
			->value("$('.add-shop-item').autocompleteShopItem({ shop_id: '{$oShop->id}', shop_currency_id: 0 }, function(event, ui) {
				$('.index_value').val((parseInt($('.index_value').val()) + 1));

				$('.shop-item-table > tbody').append(
					$('<tr data-item-id=\"' + ui.item.id + '\"><td class=\"index\">' + $('.index_value').val() + '</td><td>' + $.escapeHtml(ui.item.label) + '<input type=\'hidden\' name=\'shop_item_id[]\' value=\'' + (typeof ui.item.id !== 'undefined' ? ui.item.id : 0) + '\'/>' + '</td><td>' + $.escapeHtml(ui.item.measure) + '</td><td><span class=\"price\">' + ui.item.price_with_tax + '</span><input type=\"hidden\" name=\"shop_item_price[]\" value=\"0.00\"/></td><td>' + $.escapeHtml(ui.item.currency) + '</td><td width=\"80\"><input class=\"set-item-count form-control\" onsubmit=\"$(\'.add-shop-item\').focus();return false;\" name=\"shop_item_quantity[]\" value=\"\"/></td><td><span class=\"calc-warehouse-sum\"></span></td><td><a class=\"delete-associated-item\" onclick=\"$(this).parents(\'tr\').remove()\"><i class=\"fa fa-times-circle darkorange\"></i></a></td></tr>')
				);
				ui.item.value = '';
				$.changeWarehouseCounts($('.set-item-count'), 2);
				$('.set-item-count').change();
				$('.shop-item-table tr:last-child').find('.set-item-count').focus();
				$.focusAutocomplete($('.set-item-count'));
			  });

				$.each($('.shop-item-table > tbody tr[data-item-id]'), function (index, item) {
					var jInput = $(this).find('.set-item-count');

					$.changeWarehouseCounts(jInput, 2);
					jInput.change();
				});

			  $.focusAutocomplete($('.set-item-count'));
			  ");

		$oShopItemRow2->add($oCore_Html_Entity_Script);

		$title = $this->_object->id
			? Core::_('Shop_Warehouse_Writeoff.form_edit', $this->_object->number)
			: Core::_('Shop_Warehouse_Writeoff.form_add');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Warehouse_Writeoff_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'shop_warehouse_writeoff'
				&& $this->_object->backupRevision();
		}

		$this->addSkipColumn('posted');

		$iOldWarehouse = intval($this->_object->shop_warehouse_id);

		$this->_object->user_id = intval(Core_Array::getPost('user_id'));

		parent::_applyObjectProperty();

		if ($this->_object->id)
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();
			$this->addMessage("<script>$.showPrintButton('{$windowId}', {$this->_object->id})</script>");
		}

		if ($this->_object->number == '')
		{
			$this->_object->number = $this->_object->id;
			$this->_object->save();
		}

		$bNeedsRePost = FALSE;

		// Существующие товары
		$aShop_Warehouse_Writeoff_Items = $this->_object->Shop_Warehouse_Writeoff_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Writeoff_Items as $oShop_Warehouse_Writeoff_Item)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Writeoff_Item->shop_item_id);

			if (!is_null($oShop_Item))
			{
				$oShop_Item = $oShop_Item->shortcut_id
					? $oShop_Item->Shop_Item
					: $oShop_Item;

				$quantity = Core_Array::getPost('shop_item_quantity_' . $oShop_Warehouse_Writeoff_Item->id, 0);

				$oShop_Warehouse_Writeoff_Item->count != $quantity && $bNeedsRePost = TRUE;

				$price = $oShop_Item->loadPrice($this->_object->shop_price_id);

				$oShop_Warehouse_Writeoff_Item->count = $quantity;
				$oShop_Warehouse_Writeoff_Item->price = $price;
				$oShop_Warehouse_Writeoff_Item->save();
			}
		}

		// Новые товары
		$aAddShopItems = Core_Array::getPost('shop_item_id', array());

		count($aAddShopItems) && $bNeedsRePost = TRUE;

		foreach ($aAddShopItems as $key => $shop_item_id)
		{
			// $iCount = $this->_object->Shop_Warehouse_Writeoff_Items->getCountByshop_item_id($shop_item_id);

			// if (!$iCount)
			// {
				$oShop_Item = Core_Entity::factory('Shop_Item')->getById($shop_item_id);

				if (!is_null($oShop_Item))
				{
					$oShop_Item = $oShop_Item->shortcut_id
						? $oShop_Item->Shop_Item
						: $oShop_Item;

					$price = $oShop_Item->loadPrice($this->_object->shop_price_id);

					$count = isset($_POST['shop_item_quantity'][$key]) && is_numeric($_POST['shop_item_quantity'][$key])
						? $_POST['shop_item_quantity'][$key]
						: 0;

					$oShop_Warehouse_Writeoff_Item = Core_Entity::factory('Shop_Warehouse_Writeoff_Item');
					$oShop_Warehouse_Writeoff_Item
						->shop_warehouse_writeoff_id($this->_object->id)
						->shop_item_id($oShop_Item->id)
						->count($count)
						->price($price)
						->save();
				}
			// }
		}

		($bNeedsRePost || !Core_Array::getPost('posted')) && $this->_object->unpost();
		Core_Array::getPost('posted') && $this->_object->post();

		if ($iOldWarehouse != $this->_object->shop_warehouse_id)
		{
			$aOld_Shop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse', $iOldWarehouse)->Shop_Warehouse_Entries->getByDocument($this->_object->id, 2);

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

		$aShop_Prices = $oShop->Shop_Prices->findAll();

		foreach ($aShop_Prices as $oShop_Price)
		{
			$aReturn[$oShop_Price->id] = $oShop_Price->name . ' [' . $oShop_Price->id . ']';
		}

		return $aReturn;
	}
}