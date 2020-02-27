<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Inventory Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$printlayoutsButton .= Printlayout_Controller::getPrintButtonHtml($this->_Admin_Form_Controller, $oModule->id, 3, 'hostcms[checked][0][' . $this->_object->id . ']=1&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id);
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
			->caption(Core::_('Shop_Warehouse_Inventory.shop_warehouse_id'))
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
			->caption(Core::_('Shop_Warehouse_Inventory.user_id'))
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
					->add($oSelectResponsibleUsers)
					->class('form-group col-xs-12 col-sm-5 col-lg-4')
			)
			->add($oScriptResponsibleUsers);

		$oMainTab->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-lg-3 margin-top-21')), $oMainRow2);

		$oShopItemBlock
			->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
				->class('header bordered-palegreen')
				->value(Core::_('Shop_Warehouse_Inventory.shop_item_header'))
			)
			->add($oShopItemRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$itemTable = '
			<div class="table-scrollable">
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
							<th>' . Core::_('Shop_Warehouse_Inventory.calc') . '</th>
							<th>' . Core::_('Shop_Warehouse_Inventory.fact') . '</th>
							<th>' . Core::_('Shop_Warehouse_Inventory.diff') . '</th>
							<th>' . Core::_('Shop_Warehouse_Inventory.calc') . '</th>
							<th>' . Core::_('Shop_Warehouse_Inventory.fact') . '</th>
							<th>' . Core::_('Shop_Warehouse_Inventory.diff') . '</th>
						</tr>
					</thead>
					<tbody>
		';

		$oSiteAlias = $oShop->Site->getCurrentAlias();
		$sShopUrl = $oSiteAlias
			? ($oShop->Structure->https ? 'https://' : 'http://') . $oSiteAlias->name . $oShop->Structure->getPath()
			: NULL;

		$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

		$index = 0;

		$limit = 100;
		$offset = 0;

		do {
			$oShop_Warehouse_Inventory_Items = $this->_object->Shop_Warehouse_Inventory_Items;
			$oShop_Warehouse_Inventory_Items->queryBuilder()
				->limit($limit)
				->offset($offset)
				->clearOrderBy()
				->orderBy('shop_warehouse_inventory_items.id');

			$aShop_Warehouse_Inventory_Items = $oShop_Warehouse_Inventory_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Inventory_Items as $oShop_Warehouse_Inventory_Item)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Inventory_Item->shop_item_id);

				if (!is_null($oShop_Item))
				{
					$oShop_Item = $oShop_Item->shortcut_id
						? $oShop_Item->Shop_Item
						: $oShop_Item;

					$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteShopItem', NULL, 0, $oShop_Item->id, "shop_warehouse_inventory_item_id={$oShop_Warehouse_Inventory_Item->id}");

					$externalLink = $sShopUrl
						? '<a class="margin-left-5" target="_blank" href="' . $sShopUrl . $oShop_Item->getPath() .  '"><i class="fa fa-external-link"></i></a>'
						: '';

					// Цены
					$old_price = $Shop_Price_Entry_Controller->getPrice(0, $oShop_Item->id, $this->_object->datetime);

					is_null($old_price)
						&& $old_price = $oShop_Item->price;

					// Фактическое наличие
					$rest = $this->_object->Shop_Warehouse->getRest($oShop_Item->id, $this->_object->datetime);
					is_null($rest) && $rest = 0;

					$itemTable .= '
						<tr id="' . $oShop_Warehouse_Inventory_Item->id . '" data-item-id="' . $oShop_Item->id . '">
							<td class="index">' . ++$index . '</td>
							<td>' . htmlspecialchars($oShop_Item->name) . $externalLink . '</td>
							<td>' . htmlspecialchars($oShop_Item->Shop_Measure->name) . '</td>
							<td><span class="price">' . $old_price . '</span></td>
							<td>' . htmlspecialchars($oShop_Item->Shop_Currency->name) . '</td>
							<td class="calc-warehouse-count">' . $rest . '</td>
							<td width="80"><input class="set-item-count form-control" name="shop_item_quantity_' . $oShop_Warehouse_Inventory_Item->id . '" value="' . $oShop_Warehouse_Inventory_Item->count . '" /></td>
							<td class="diff-warehouse-count"></td>
							<td><span class="calc-warehouse-sum"></span></td>
							<td><span class="warehouse-inv-sum"></span></td>
							<td><span class="diff-warehouse-sum"></span></td>
							<td><a class="delete-associated-item" onclick="res = confirm(\'' . Core::_('Shop_Warehouse_Inventory.delete_dialog') . '\'); if (res) {' . $onclick . '} return res;"><i class="fa fa-times-circle darkorange"></i></a></td>
						</tr>
					';
				}
			}

			$offset += $limit;
		}
		while (count($aShop_Warehouse_Inventory_Items));

		$itemTable .= '
					</tbody>
				</table>
			</div>
		';

		$oShopItemRow2->add(
			Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12'))
				->class('add-shop-item form-control')
				->placeholder(Core::_('Shop_Warehouse_Inventory.add_item_placeholder'))
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
			->value("$('.add-shop-item').autocompleteShopItem({ shop_id: {$oShop->id}, shop_currency_id: 0, datetime: '{$this->_object->datetime}' }, function(event, ui) {
				$('.index_value').val((parseInt($('.index_value').val()) + 1));

				var warehouseId = $('select.select-warehouse').val(),
					foundRest = ui.item.aWarehouses.find(x => x.id === warehouseId);

				if (typeof foundRest == 'undefined')
				{
					foundRest = {count: 0};
				}

				$('.shop-item-table > tbody').append(
						$('<tr data-item-id=\"' + ui.item.id + '\"><td class=\"index\">' + $('.index_value').val() + '</td><td>' + $.escapeHtml(ui.item.label) + '<input type=\'hidden\' name=\'shop_item_id[]\' value=\'' + (typeof ui.item.id !== 'undefined' ? ui.item.id : 0) + '\'/>' + '</td><td>' + $.escapeHtml(ui.item.measure) + '</td><td><span class=\"price\">' + ui.item.price_with_tax + '</span></td><td>' + $.escapeHtml(ui.item.currency) + '</td><td><span class=\"calc-warehouse-count\">' + foundRest.count + '</span></td><td width=\"80\"><input class=\"set-item-count form-control\" name=\"shop_item_quantity[]\" value=\"\"/></td><td class=\"diff-warehouse-count\"></td><td><span class=\"calc-warehouse-sum\"></span></td><td><span class=\"warehouse-inv-sum\"></span></td><td><span class=\"diff-warehouse-sum\"></span></td><td><a class=\"delete-associated-item\" onclick=\"$(this).parents(\'tr\').remove()\"><i class=\"fa fa-times-circle darkorange\"></i></a></td></tr>')
					);
					ui.item.value = '';
					$.changeWarehouseCounts($('.set-item-count'), 0);
					$('.set-item-count').change();
					$('.shop-item-table tr:last-child').find('.set-item-count').focus();
					$.focusAutocomplete($('.set-item-count'));
				});

				$.each($('.shop-item-table > tbody tr[data-item-id]'), function (index, item) {
					var jInput = $(this).find('.set-item-count');

					$.changeWarehouseCounts(jInput, 0);
					jInput.change();
				});

				$.focusAutocomplete($('.set-item-count'));

				$('select.select-warehouse').change(function() {
					$.updateWarehouseCounts($(this).val());
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
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'shop_warehouse_inventory'
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
		$aShop_Warehouse_Inventory_Items = $this->_object->Shop_Warehouse_Inventory_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Inventory_Items as $oShop_Warehouse_Inventory_Item)
		{
			$quantity = Core_Array::getPost('shop_item_quantity_' . $oShop_Warehouse_Inventory_Item->id);

			if ($quantity > 0)
			{
				$oShop_Warehouse_Inventory_Item->count != $quantity && $bNeedsRePost = TRUE;

				$oShop_Warehouse_Inventory_Item->count = $quantity;
				$oShop_Warehouse_Inventory_Item->save();
			}
		}

		// Новые товары
		$aAddShopItems = Core_Array::getPost('shop_item_id', array());

		count($aAddShopItems) && $bNeedsRePost = TRUE;

		foreach ($aAddShopItems as $key => $shop_item_id)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->getById($shop_item_id);

			ob_start();

			$script = "$(\"#{$windowId} input[name='shop_item_id\\[\\]']\").eq(0).remove();";

			if (!is_null($oShop_Item))
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

					$script .= "$(\"#{$windowId} input[name='shop_item_quantity\\[\\]']\").eq(0).attr('name', 'shop_item_quantity_{$oShop_Warehouse_Inventory_Item->id}');";
				}
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

		($bNeedsRePost || !Core_Array::getPost('posted')) && $this->_object->unpost();
		Core_Array::getPost('posted') && $this->_object->post();

		if ($iOldWarehouse != $this->_object->shop_warehouse_id)
		{
			$aOld_Shop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse', $iOldWarehouse)->Shop_Warehouse_Entries->getByDocument($this->_object->id, 0);

			foreach ($aOld_Shop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$oShop_Warehouse_Entry->delete();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}