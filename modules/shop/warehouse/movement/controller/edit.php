<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Movement_Controller_Edit Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Movement_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
		$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'))->class('form-control input-lg'), $oMainRow1);

		// Дата документа меняется только если документа не проведен.
		$this->_object->id && $this->_object->posted
			&& $this->getField('datetime')->readonly('readonly');

		$oAdditionalTab->delete($this->getField('source_shop_warehouse_id'));

		$oDefault_Shop_Warehouse = $oShop->Shop_Warehouses->getDefault();
		$default_warehouse_id = !is_null($oDefault_Shop_Warehouse) ? $oDefault_Shop_Warehouse->id : 0;

		$oSource_Shop_Warehouse_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Warehouse_Movement.source_shop_warehouse_id'))
			->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-2')
			)
			->options(Shop_Warehouse_Controller_Edit::fillWarehousesList($oShop))
			->class('form-control select-warehouse')
			->name('source_shop_warehouse_id')
			->value($this->_object->id
				? $this->_object->source_shop_warehouse_id
				: $default_warehouse_id
			);

		$oMainRow2->add($oSource_Shop_Warehouse_Select);

		$oAdditionalTab->delete($this->getField('destination_shop_warehouse_id'));

		$oDestination_Shop_Warehouse_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Warehouse_Movement.destination_shop_warehouse_id'))
			->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-2')
			)
			->options(Shop_Warehouse_Controller_Edit::fillWarehousesList($oShop))
			->class('form-control select-warehouse')
			->name('destination_shop_warehouse_id')
			->value($this->_object->id
				? $this->_object->destination_shop_warehouse_id
				: 0
			);

		$oMainRow2->add($oDestination_Shop_Warehouse_Select);

		// Удаляем поле с идентификатором ответственного сотрудника
		$oAdditionalTab->delete($this->getField('user_id'));

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

		$oSelectResponsibleUsers = Admin_Form_Entity::factory('Select')
			->id('user_id')
			->options($aSelectResponsibleUsers)
			->name('user_id')
			->value($this->_object->user_id)
			->caption(Core::_('Shop_Warehouse_Incoming.user_id'))
			->divAttr(array('class' => ''));

		$oScriptResponsibleUsers = Admin_Form_Entity::factory('Script')
			->value('$("#' . $windowId . ' #user_id").selectUser({
					placeholder: "",
					language: "' . Core_I18n::instance()->getLng() . '",
					dropdownParent: $("#' . $windowId . '")
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
					->class('form-group col-xs-12 col-sm-5 col-lg-6')
			)
			->add($oScriptResponsibleUsers);

		$oMainTab->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 col-lg-2 margin-top-21')), $oMainRow2);

		// Печать
		if (Core::moduleIsActive('printlayout'))
		{
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
				$printlayoutsButton .= Printlayout_Controller::getPrintButtonHtml($this->_Admin_Form_Controller, $oModule->id, $this->_object->getEntityType(), 'hostcms[checked][0][' . $this->_object->id . ']=1&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id);
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
		}

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		$oShopItemBlock
			->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
				->class('header bordered-palegreen')
				->value(Core::_('Shop_Warehouse_Incoming.shop_item_header'))
			)
			->add($oShopItemRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$itemTable = '
			<div class="table-scrollable">
				<table class="table table-striped table-hover shop-item-table deals-aggregate-user-info">
					<thead>
						<tr>
							<th scope="col">' . Core::_('Shop_Warehouse_Incoming.position') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Incoming.name') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Incoming.measure') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Incoming.price') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Incoming.currency') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Incoming.quantity') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Incoming.sum') . '</th>
							<th scope="col"> </th>
						</tr>
					</thead>
					<tbody>
		';

		$oSiteAlias = $oShop->Site->getCurrentAlias();
		$sShopUrl = $oSiteAlias
			? ($oShop->Structure->https ? 'https://' : 'http://') . $oSiteAlias->name . $oShop->Structure->getPath()
			: NULL;

		$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();
		$Shop_Item_Controller = new Shop_Item_Controller();

		$index = 0;

		$limit = 100;
		$offset = 0;

		do {
			$oShop_Warehouse_Movement_Items = $this->_object->Shop_Warehouse_Movement_Items;
			$oShop_Warehouse_Movement_Items->queryBuilder()
				->limit($limit)
				->offset($offset)
				->clearOrderBy()
				->orderBy('shop_warehouse_movement_items.id');

			$aShop_Warehouse_Movement_Items = $oShop_Warehouse_Movement_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Movement_Items as $oShop_Warehouse_Movement_Item)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Movement_Item->shop_item_id);

				if (!is_null($oShop_Item))
				{
					$oShop_Item = $oShop_Item->shortcut_id
						? $oShop_Item->Shop_Item
						: $oShop_Item;

					$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteShopItem', NULL, 0, $oShop_Item->id, "shop_warehouse_movement_item_id={$oShop_Warehouse_Movement_Item->id}");

					$externalLink = $sShopUrl
						? '<a class="margin-left-5" target="_blank" href="' . htmlspecialchars($sShopUrl . $oShop_Item->getPath()) . '"><i class="fa fa-external-link"></i></a>'
						: '';

					// Цены
					$price = $Shop_Price_Entry_Controller->getPrice(0, $oShop_Item->id, $this->_object->datetime);
					is_null($price) && $price = $oShop_Item->price;

					$aPrices = $Shop_Item_Controller->calculatePriceInItemCurrency($price, $oShop_Item);

					$itemTable .= '
						<tr id="' . $oShop_Warehouse_Movement_Item->id . '" data-item-id="' . $oShop_Item->id . '">
							<td class="index">' . ++$index . '</td>
							<td>' . htmlspecialchars((string) $oShop_Item->name) . $externalLink . '</td>
							<td>' . htmlspecialchars((string) $oShop_Item->Shop_Measure->name) . '</td>
							<td><span class="price">' . $aPrices['price_tax'] . '</span></td>
							<td>' . htmlspecialchars((string) $oShop_Item->Shop_Currency->sign) . '</td>
							<td width="80"><input class="set-item-count form-control" name="shop_item_quantity_' . $oShop_Warehouse_Movement_Item->id . '" value="' . $oShop_Warehouse_Movement_Item->count . '" /></td>
							<td><span class="calc-warehouse-sum">' . ($oShop_Warehouse_Movement_Item->count * $price) . '</span></td>
							<td><a class="delete-associated-item" onclick="mainFormLocker.unlock(); res = confirm(\'' . Core::_('Shop_Warehouse_Incoming.delete_dialog') . '\'); if (res) { var next = $(this).parents(\'tr\').next(); $(this).parents(\'tr\').remove(); $.recountIndexes(next); ' . $onclick . ' } return res;"><i class="fa fa-times-circle darkorange"></i></a></td>
						</tr>
					';
				}
			}

			$offset += $limit;
		}
		while (count($aShop_Warehouse_Movement_Items));

		$itemTable .= '
					</tbody>
				</table>
			</div>
		';

		$oShopItemRow2->add(
			Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12'))
				->class('add-shop-item form-control')
				->placeholder(Core::_('Shop_Warehouse_Movement.add_item_placeholder'))
				->name('set_item_name')
		);

		$oShopItemRow2
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add(
					Admin_Form_Entity::factory('Code')->html($itemTable)
				)
		);

		$oCore_Html_Entity_Script = Core_Html_Entity::factory('Script')
			->value("var jAddShopItem = $('#{$windowId} .add-shop-item'); jAddShopItem.autocompleteShopItem({ shop_id: '{$oShop->id}', price_mode: 'item', shop_currency_id: 0 }, function(event, ui) {
				var newRow = $('<tr data-item-id=\"' + ui.item.id + '\"><td class=\"index\">' + $('#{$windowId} .index_value').val() + '</td><td>' + $.escapeHtml(ui.item.label) + '<input type=\'hidden\' name=\'shop_item_id[]\' value=\'' + (typeof ui.item.id !== 'undefined' ? ui.item.id : 0) + '\'/>' + '</td><td>' + $.escapeHtml(ui.item.measure) + '</td><td><span class=\"price\">' + ui.item.price_with_tax + '</span></td><td>' + $.escapeHtml(ui.item.currency) + '</td><td width=\"80\"><input class=\"set-item-count form-control\" onsubmit=\"$(\'.add-shop-item\').focus();return false;\" name=\"shop_item_quantity[]\" value=\"\"/></td><td><span class=\"calc-warehouse-sum\"></span></td><td><a class=\"delete-associated-item\" onclick=\"var next = $(this).parents(\'tr\').next(); $(this).parents(\'tr\').remove(); $.recountIndexes(next)\"><i class=\"fa fa-times-circle darkorange\"></i></a></td></tr>'),
				jNewItemCount = newRow.find('.set-item-count');

				$('#{$windowId} .shop-item-table > tbody').append(
					newRow
				);
				ui.item.value = '';

				$.changeWarehouseCounts(jNewItemCount, 5);
				jNewItemCount.change();
				jNewItemCount.focus();
				$.focusAutocomplete(jNewItemCount, jAddShopItem);
				$.recountIndexes(newRow);
			 });

			$.each($('#{$windowId} .shop-item-table > tbody tr[data-item-id]'), function (index, item) {
				var jInput = $(this).find('.set-item-count');

				$.changeWarehouseCounts(jInput, 5);
				jInput.change();

				$.focusAutocomplete(jInput, jAddShopItem);
			});

			//$.focusAutocomplete($('#{$windowId} .set-item-count'), $('#{$windowId} .add-shop-item'));"
		);

		$oShopItemRow2->add($oCore_Html_Entity_Script);

		$this->title($this->_object->id
			? Core::_('Shop_Warehouse_Movement.form_edit', $this->_object->number, FALSE)
			: Core::_('Shop_Warehouse_Movement.form_add')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Warehouse_Incoming_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'shop_warehouse_movement'
				&& $this->_object->backupRevision();
		}

		$this->addSkipColumn('posted');

		$this->_object->user_id = intval(Core_Array::getPost('user_id'));

		$iOldSourceWarehouse = intval($this->_object->source_shop_warehouse_id);
		$iOldDestinationWarehouse = intval($this->_object->destination_shop_warehouse_id);

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
		$aShop_Warehouse_Movement_Items = $this->_object->Shop_Warehouse_Movement_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Movement_Items as $oShop_Warehouse_Movement_Item)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Movement_Item->shop_item_id);

			if (!is_null($oShop_Item))
			{
				$quantity = Core_Array::getPost('shop_item_quantity_' . $oShop_Warehouse_Movement_Item->id, 0);

				$oShop_Warehouse_Movement_Item->count != $quantity && $bNeedsRePost = TRUE;

				$oShop_Warehouse_Movement_Item->count = $quantity;
				$oShop_Warehouse_Movement_Item->save();
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
				$count = isset($_POST['shop_item_quantity'][$key]) && is_numeric($_POST['shop_item_quantity'][$key])
					? $_POST['shop_item_quantity'][$key]
					: 0;

				$oShop_Warehouse_Movement_Item = Core_Entity::factory('Shop_Warehouse_Movement_Item');
				$oShop_Warehouse_Movement_Item
					->shop_warehouse_movement_id($this->_object->id)
					->shop_item_id($oShop_Item->id)
					->count($count)
					->save();

				$script .= "$(\"#{$windowId} input[name='shop_item_quantity\\[\\]']\").eq(0).attr('name', 'shop_item_quantity_{$oShop_Warehouse_Movement_Item->id}');";
			}
			else
			{
				$script .= "$(\"#{$windowId} input[name='shop_item_quantity\\[\\]']\").eq(0).remove();";
			}

			Core_Html_Entity::factory('Script')
				->value($script)
				->execute();
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());
		}

		// Было изменение склада
		$iOldSourceWarehouse != $this->_object->source_shop_warehouse_id || $iOldDestinationWarehouse != $this->_object->destination_shop_warehouse_id
			&& $bNeedsRePost = TRUE;

		($bNeedsRePost || !Core_Array::getPost('posted')) && $this->_object->unpost();
		Core_Array::getPost('posted') && $this->_object->post();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}