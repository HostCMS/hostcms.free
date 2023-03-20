<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Purchasereturn Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Purchasereturn_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));

		$windowId = $modalWindowId
			? $modalWindowId
			: $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oCompanyRow = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSiteuserRow = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oRegistrationRow = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemBlock = Admin_Form_Entity::factory('Div')->class('well with-header'))
			->add($oShopDocumentRelationRow = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'))->class('form-control input-lg'), $oMainRow1);

		// Дата документа меняется только если документа не проведен.
		$this->_object->id && $this->_object->posted
			&& $this->getField('datetime')->readonly('readonly');

		$oMainTab->delete($this->getField('company_id'));

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aCompanies = $oSite->Companies->findAll();

		$aTmp = [];

		foreach($aCompanies as $oCompany)
		{
			$aTmp[$oCompany->id] = $oCompany->name;
		}

		$oSelect_Companies = Admin_Form_Entity::factory('Select')
			->options($aTmp)
			->id('company_id')
			->name('company_id')
			->value($this->_object->company_id)
			->caption(Core::_('Shop_Warehouse_Purchasereturn.company_id'))
			->divAttr(array('class'=>'form-group col-xs-12 col-md-6'))
			->onchange("$.fillSiteuserCompanyContract('{$windowId}', " . intval($this->_object->siteuser_company_contract_id) . ");");

		$oCompanyRow->add($oSelect_Companies);

		if (Core::moduleIsActive('siteuser'))
		{
			$oAdditionalTab->delete($this->getField('siteuser_company_id'));

			$aMasSiteusers = array();

			$oSiteuserCompany = Core_Entity::factory('Siteuser_Company')->getById($this->_object->siteuser_company_id);
			$oSiteuser = !is_null($oSiteuserCompany)
				? $oSiteuserCompany->Siteuser
				: NULL;

			if ($oSiteuser)
			{
				$oOptgroupSiteuser = new stdClass();
				$oOptgroupSiteuser->attributes = array('label' => $oSiteuser->login, 'class' => 'siteuser');

				if ($oSiteuserCompany)
				{
					$tin = !empty($oSiteuserCompany->tin)
						? ' ➤ ' . $oSiteuserCompany->tin
						: '';

					$oOptgroupSiteuser->children['company_' . $oSiteuserCompany->id] = array(
						'value' => $oSiteuserCompany->name . $tin . ' 👤 ' . $oSiteuser->login . '%%%' . $oSiteuserCompany->getAvatar(),
						'attr' => array('class' => 'siteuser-company')
					);
				}

				$aMasSiteusers[$oSiteuser->id] = $oOptgroupSiteuser;
			}

			$oSelectSiteusers = Admin_Form_Entity::factory('Select')
				->id('purchasereturn_siteuser_company_id')
				->options($aMasSiteusers)
				->name('siteuser_company_id')
				->value('company_' . $this->_object->siteuser_company_id)
				->caption(Core::_('Shop_Warehouse_Purchasereturn.siteuser_company_id'))
				->style("width: 100%")
				->divAttr(array('class' => 'col-xs-12'))
				->onchange("$.fillSiteuserCompanyContract('{$windowId}', " . intval($this->_object->siteuser_company_contract_id) . ");");

			$oScriptSiteusers = Admin_Form_Entity::factory('Script')
				->value('
					$("#' . $windowId . ' #purchasereturn_siteuser_company_id").select2({
						dropdownParent: $("#' . $windowId . '"),
						minimumInputLength: 1,
						placeholder: "",
						allowClear: true,
						// multiple: true,
						ajax: {
							url: "/admin/siteuser/index.php?loadSiteusers&types[]=company",
							dataType: "json",
							type: "GET",
							processResults: function (data) {
								var aResults = [];
								$.each(data, function (index, item) {
									aResults.push({
										"id": item.id,
										"text": item.text
									});
								});
								return {
									results: aResults
								};
							}
						},
						templateResult: $.templateResultItemSiteusers,
						escapeMarkup: function(m) { return m; },
						templateSelection: $.templateSelectionItemSiteusers,
						language: "' . Core_I18n::instance()->getLng() . '",
						width: "100%"
					})
					.val("company_' . $this->_object->siteuser_company_id . '")
					.trigger("change.select2");
				');



			$oAdditionalTab->delete($this->getField('siteuser_company_contract_id'));

			// Договоры
			$oSelectContracts = Admin_Form_Entity::factory('Select')
				->options(array())
				->id('siteuser_company_contract_id')
				->name('siteuser_company_contract_id')
				->caption(Core::_('Shop_Warehouse_Purchasereturn.siteuser_company_contract_id'))
				->divAttr(array('class'=>'col-xs-12'));

			$oSiteuserRow
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12 col-sm-6 no-padding')
						//->add($oScriptContracts)
						->add($oSelectSiteusers)
						->add($oScriptSiteusers)
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12 col-sm-6 no-padding')
						->add($oSelectContracts)
						//->add($oScriptContracts)
				);
		}

		$oAdditionalTab->delete($this->getField('shop_warehouse_id'));

		$oDefault_Shop_Warehouse = $oShop->Shop_Warehouses->getDefault();

		$oShop_Warehouse_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Warehouse_Purchasereturn.shop_warehouse_id'))
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

		$aSelectResponsibleUsers = $oSite->Companies->getUsersOptions();

		$oSelectResponsibleUsers = Admin_Form_Entity::factory('Select')
			->id('user_id')
			->options($aSelectResponsibleUsers)
			->name('user_id')
			->value($this->_object->user_id)
			->caption(Core::_('Shop_Warehouse_Purchasereturn.user_id'))
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
					->add($oSelectResponsibleUsers)
					->class('form-group col-xs-12 col-sm-5 col-lg-3')
			)
			->add($oScriptResponsibleUsers);

		$oMainTab->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-lg-3 margin-top-21')), $oMainRow2);

		$oMainTab->delete($this->getField('shop_price_id'));

		$oShop_Price_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Warehouse_Purchasereturn.shop_price_id'))
			->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-3')
			)
			->options(Shop_Item_Controller_Edit::fillPricesList($oShop))
			->class('form-control select-price')
			->name('shop_price_id')
			->value($this->_object->id
				? $this->_object->shop_price_id
				: 0
			);

		$oMainRow3->add($oShop_Price_Select);

		$oRecalcPriceLink = Admin_Form_Entity::factory('Link');
		$oRecalcPriceLink
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21 recalc-button'))
			->a
				->class('btn btn-labeled btn-default')
				->onclick("$.recalcPrice('{$windowId}')")
				->value(Core::_('Shop_Warehouse_Purchasereturn.recalc_price'));
		$oRecalcPriceLink
			->icon
				->class('btn-label fa fa-recycle');

		$oMainRow3->add($oRecalcPriceLink);

		// Печать
		if (Core::moduleIsActive('printlayout'))
		{
			$printlayoutsButton = '
				<div class="btn-group btn-group-short1">
					<a class="btn btn-labeled btn-success" data-toggle="dropdown" href="javascript:void(0);"><i class="btn-label fa fa-print"></i><span>' . Core::_('Printlayout.print') . '</span></a>
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
			->add(Admin_Form_Entity::factory('Div')
				->class('header bordered-palegreen')
				->value(Core::_('Shop_Warehouse_Purchasereturn.shop_item_header'))
			)
			->add($oShopItemRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$itemTable = '
			<div class="table-scrollable">
				<table class="table table-striped table-hover shop-item-table deals-aggregate-user-info">
					<thead>
						<tr>
							<th scope="col">' . Core::_('Shop_Warehouse_Purchasereturn.position') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Purchasereturn.name') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Purchasereturn.measure') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Purchasereturn.price') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Purchasereturn.currency') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Purchasereturn.quantity') . '</th>
							<th scope="col">' . Core::_('Shop_Warehouse_Purchasereturn.sum') . '</th>
							<th scope="col"> </th>
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

		// $createFromSupply = intval(Core_Array::getGet('createFromSupply'));

		$createFrom = Core_Array::getGet('createFrom', '', 'trim');
		$createFromId = Core_Array::getGet('createFromId', 0, 'int');

		do {
			if ($createFrom && !$this->_object->id)
			{
				switch ($createFrom)
				{
					case 'supply':
						$oShop_Warehouse_Supply = Core_Entity::factory('Shop_Warehouse_Supply', $createFromId);
						$aShop_Warehouse_Purchasereturn_Items = $oShop_Warehouse_Supply->Shop_Warehouse_Supply_Items->findAll();
					break;
				}
			}
			else
			{
				$oShop_Warehouse_Purchasereturn_Items = $this->_object->Shop_Warehouse_Purchasereturn_Items;
				$oShop_Warehouse_Purchasereturn_Items->queryBuilder()
					->limit($limit)
					->offset($offset)
					->clearOrderBy()
					->orderBy('shop_warehouse_purchasereturn_items.id');

				$aShop_Warehouse_Purchasereturn_Items = $oShop_Warehouse_Purchasereturn_Items->findAll(FALSE);
			}

			foreach ($aShop_Warehouse_Purchasereturn_Items as $oShop_Warehouse_Purchasereturn_Item)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Purchasereturn_Item->shop_item_id);

				if (!is_null($oShop_Item))
				{
					$oShop_Item = $oShop_Item->shortcut_id
						? $oShop_Item->Shop_Item
						: $oShop_Item;

					$onclick = !$createFrom || $this->_object->id
						? $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteShopItem', NULL, 0, $oShop_Item->id, "shop_warehouse_purchasereturn_item_id={$oShop_Warehouse_Purchasereturn_Item->id}")
						: '';

					$externalLink = $sShopUrl
						? '<a class="margin-left-5" target="_blank" href="' . htmlspecialchars($sShopUrl . $oShop_Item->getPath()) . '"><i class="fa fa-external-link"></i></a>'
						: '';

					$itemTable .= '
						<tr ' . ( !$createFrom ? ('id="' . $oShop_Warehouse_Purchasereturn_Item->id) . '"' : '') . ' data-item-id="' . $oShop_Item->id . '">
							<td class="index">' . ++$index . '</td>
							<td>' . htmlspecialchars((string) $oShop_Item->name) . $externalLink . ( $createFrom ? ('<input type="hidden" name="shop_item_id[]" value="' . $oShop_Item->id . '"/>') : '') . '</td>
							<td>' . htmlspecialchars((string) $oShop_Item->Shop_Measure->name) . '</td>
							<td width="110"><input type="text" class="price set-item-price form-control" name="shop_item_price' . (!$createFrom ? ('_' . $oShop_Warehouse_Purchasereturn_Item->id) : '[]') . '" value="' . $oShop_Warehouse_Purchasereturn_Item->price . '" /></td>
							<td>' . htmlspecialchars((string) $oShop_Item->Shop_Currency->sign) . '</td>
							<td width="80"><input class="set-item-count form-control" name="shop_item_quantity' . (!$createFrom ? ('_' . $oShop_Warehouse_Purchasereturn_Item->id) : '[]')  . '" value="' . $oShop_Warehouse_Purchasereturn_Item->count . '" /></td>
							<td><span class="calc-warehouse-sum">' . ($oShop_Warehouse_Purchasereturn_Item->count * $oShop_Warehouse_Purchasereturn_Item->price) . '</span></td>
							<td><a class="delete-associated-item" onclick="mainFormLocker.unlock(); res = confirm(\'' . Core::_('Shop_Warehouse_Purchasereturn.delete_dialog') . '\'); if (res) { var next = $(this).parents(\'tr\').next(); $(this).parents(\'tr\').remove(); $.recountIndexes(next); ' . $onclick . ' } return res;"><i class="fa fa-times-circle darkorange"></i></a></td>
						</tr>
					';
				}
			}

			$offset += $limit;
		}
		while (!$createFrom && count($aShop_Warehouse_Purchasereturn_Items));

		$itemTable .= '
					</tbody>
				</table>
			</div>
		';

		$oShopItemRow1->add(
			Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12'))
				->class('add-shop-item form-control')
				->placeholder(Core::_('Shop_Warehouse_Purchasereturn.add_item_placeholder'))
				->name('set_item_name')
		);

		$oShopItemRow1
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add(
					Admin_Form_Entity::factory('Code')->html($itemTable)
				)
		);

		$oCore_Html_Entity_Script = Core_Html_Entity::factory('Script')
			->value("var jAddShopItem = $('#{$windowId} .add-shop-item'); jAddShopItem.autocompleteShopItem({shop_id: '{$oShop->id}', price_mode: 'item', shop_currency_id: 0}, function(event, ui) {
				var newRow = $('<tr data-item-id=\"' + ui.item.id + '\"><td class=\"index\">' + $('#{$windowId} .index_value').val() + '</td><td>' + $.escapeHtml(ui.item.label) + '<input type=\'hidden\' name=\'shop_item_id[]\' value=\'' + (typeof ui.item.id !== 'undefined' ? ui.item.id : 0) + '\'/>' + '</td><td>' + $.escapeHtml(ui.item.measure) + '</td><td width=\"110\"><input type=\"text\" class=\"price set-item-price form-control\" name=\"shop_item_price[]\" value=\"' + ui.item.price_with_tax +'\"/></td><td>' + $.escapeHtml(ui.item.currency) + '</td><td width=\"80\"><input class=\"set-item-count form-control\"  name=\"shop_item_quantity[]\" value=\"\"/></td>	<td><span class=\"calc-warehouse-sum\"></span></td><td><a class=\"delete-associated-item\" onclick=\"var next = $(this).parents(\'tr\').next(); $(this).parents(\'tr\').remove(); $.recountIndexes(next)\"><i class=\"fa fa-times-circle darkorange\"></i></a></td></tr>'),

				jNewItemCount = newRow.find('.set-item-count'),
				jNewItemPrice = newRow.find('.set-item-price');

				$('#{$windowId} .shop-item-table > tbody').append(
					newRow
				);

				ui.item.value = '';

				$.changeWarehouseCounts(jNewItemCount, 6);
				$.changeWarehousePrices(jNewItemPrice);

				jNewItemCount.change();
				jNewItemCount.focus();

				$.focusAutocomplete(jNewItemCount, jAddShopItem);
				$.focusAutocomplete(jNewItemPrice, jAddShopItem);

				$.recountIndexes(newRow);
			});

			$.each($('#{$windowId} .shop-item-table > tbody tr[data-item-id]'), function (index, item) {
				var jInput = $(this).find('.set-item-count');

				$.changeWarehouseCounts(jInput, 6);
				$.focusAutocomplete(jInput, jAddShopItem);

				jInput = $(this).find('.set-item-price');
				$.changeWarehousePrices(jInput);
				$.focusAutocomplete(jInput, jAddShopItem);
			});"
		);

		$oShopItemRow1->add($oCore_Html_Entity_Script);

		if ($this->_object->id && $oBasicDocument = $this->_object->getBasedOnDocument())
		{
			$oShopDocumentRelationRow->add(
				Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add(
					Admin_Form_Entity::factory('Code')->html($oBasicDocument->getDocumentInfoLink($this->_Admin_Form_Controller))
				)
			);
		}

		$this->title($this->_object->id
			? Core::_('Shop_Warehouse_Purchasereturn.form_edit', $this->_object->number, FALSE)
			: Core::_('Shop_Warehouse_Purchasereturn.form_add')
		);

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		// $createFromSupply = intval(Core_Array::getGet('createFromSupply'));

		$createFrom = Core_Array::getGet('createFrom', '', 'trim');
		$createFromId = Core_Array::getGet('createFromId', 0, 'int');

		// Создаем счет поставщика из формы редактирования заказа поставщику
		if ($createFrom && $createFrom = 'supply' && !$this->_object->id)
		{
			$oShop_Warehouse_Supply = Core_Entity::factory('Shop_Warehouse_Supply');
			$oShopWarehousePurchasereturn = $oShop_Warehouse_Supply->getById($createFromId)->createShopWarehousePurchasereturn();

			$this->_object = $oShopWarehousePurchasereturn;
		}

		$return = parent::execute($operation);

		if ($createFromId)
		{
			if ($operation == 'saveModal' || $operation == 'applyModal')
			{
				if ($this->_object->id)
				{
					$oShop_Warehouse_Supply = Core_Entity::factory('Shop_Warehouse_Supply');
				}

				$related_document_id = Shop_Controller::getDocumentId($createFromId, $oShop_Warehouse_Supply->getEntityType());
				$document_id = Shop_Controller::getDocumentId($this->_object->id, $this->_object->getEntityType());

				$oShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation');
				$oShop_Document_Relations->queryBuilder()
					->where('shop_document_relations.document_id', '=', $document_id)
					->where('shop_document_relations.related_document_id', '=', $related_document_id);

				$oShop_Document_Relation = $oShop_Document_Relations->getLast(FALSE);

				if (is_null($oShop_Document_Relation))
				{
					$oShop_Document_Relation = Core_Entity::factory('Shop_Document_Relation');
					$oShop_Document_Relation->document_id = $document_id;
					$oShop_Document_Relation->related_document_id = $related_document_id;
					$oShop_Document_Relation->save();
				}

				if ($operation == 'applyModal' || $operation == 'saveModal')
				{
					if ($operation == 'applyModal')
					{
						$sJsRefresh = "<script>
							(function($) {
								bootbox.hideAll();
							})(jQuery);
						</script>";

						$this->_Admin_Form_Controller->addMessage($sJsRefresh);
					}

					$this->clearContent();

					return TRUE;
				}
			}
		}

		return $return;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Warehouse_Purchasereturn_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$createFromSupply = intval(Core_Array::getGet('createFromSupply'));

		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'shop_warehouse_purchasereturn'
				&& $this->_object->backupRevision();
		}

		$sSiteuserCompany = Core_Array::getPost('siteuser_company_id', 0, 'strval');
		$aExplodeCompany = explode('_', $sSiteuserCompany);
		$siteuser_company_id = isset($aExplodeCompany[1]) ? intval($aExplodeCompany[1]) : 0;
		$this->_formValues['siteuser_company_id'] = $siteuser_company_id;

		$this->addSkipColumn('posted');

		$iOldWarehouse = intval($this->_object->shop_warehouse_id);

		$this->_object->user_id = intval(Core_Array::getPost('user_id'));

		parent::_applyObjectProperty();

		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));

		$windowId = $modalWindowId
			? $modalWindowId
			: $this->_Admin_Form_Controller->getWindowId();

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

		$Shop_Item_Controller = new Shop_Item_Controller();

		// Существующие товары
		$aShop_Warehouse_Purchasereturn_Items = $this->_object->Shop_Warehouse_Purchasereturn_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Purchasereturn_Items as $oShop_Warehouse_Purchasereturn_Item)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Warehouse_Purchasereturn_Item->shop_item_id);

			if (!is_null($oShop_Item))
			{
				$oShop_Item = $oShop_Item->shortcut_id
					? $oShop_Item->Shop_Item
					: $oShop_Item;

				$quantity = Core_Array::getPost('shop_item_quantity_' . $oShop_Warehouse_Purchasereturn_Item->id, 0);

				$oShop_Warehouse_Purchasereturn_Item->count != $quantity && $bNeedsRePost = TRUE;

				$oShop_Warehouse_Purchasereturn_Item->count = $quantity;
				//$oShop_Warehouse_Purchaseorder_Item->price = $aPrices['price_tax'];
				$oShop_Warehouse_Purchasereturn_Item->price = $quantity = Core_Array::getPost('shop_item_price_' . $oShop_Warehouse_Purchasereturn_Item->id, 0);
				$oShop_Warehouse_Purchasereturn_Item->save();
			}
		}

		// Новые товары
		$aAddShopItems = Core_Array::getPost('shop_item_id', array());

		if (count($aAddShopItems))
		{
			$bNeedsRePost = TRUE;

			$script = "var jShopItemId = $(\"#{$windowId} input[name='shop_item_id\\[\\]']\"),
						jShopItemPrice = $(\"#{$windowId} input[name='shop_item_price\\[\\]']\"),
						jShopItemQuantity = $(\"#{$windowId} input[name='shop_item_quantity\\[\\]']\"),
						aMapShopItemId = {};";

			$oAdmin_Form_Controller = $this->_Admin_Form_Controller;

			ob_start();

			foreach ($aAddShopItems as $key => $shop_item_id)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->getById($shop_item_id);

				if (!is_null($oShop_Item))
				{
					$oShop_Item = $oShop_Item->shortcut_id
						? $oShop_Item->Shop_Item
						: $oShop_Item;

					$price = isset($_POST['shop_item_price'][$key]) && is_numeric($_POST['shop_item_price'][$key])
						? $_POST['shop_item_price'][$key]
						: 0;

					$count = isset($_POST['shop_item_quantity'][$key]) && is_numeric($_POST['shop_item_quantity'][$key])
						? $_POST['shop_item_quantity'][$key]
						: 0;

					$aPrices = $Shop_Item_Controller->calculatePriceInItemCurrency($price, $oShop_Item);

					$oShop_Warehouse_Purchasereturn_Item = Core_Entity::factory('Shop_Warehouse_Purchasereturn_Item');
					$oShop_Warehouse_Purchasereturn_Item
						->shop_warehouse_purchasereturn_id($this->_object->id)
						->shop_item_id($oShop_Item->id)
						->count($count)
						->price($aPrices['price_tax'])
						->save();

					$onclick = 'mainFormLocker.unlock(); res = confirm(\'' . Core::_('Shop_Warehouse_Purchasereturn.delete_dialog') . '\'); if (res) { var next = $(this).parents(\'tr\').next(); $(this).parents(\'tr\').remove(); $.recountIndexes(next); ' . $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteShopItem', NULL, 0, $oShop_Item->id, "shop_warehouse_purchasereturn_item_id={$oShop_Warehouse_Purchasereturn_Item->id}") . ' } return res;';

					$script .= "aMapShopItemId['{$shop_item_id}'] = {
						id: {$oShop_Warehouse_Purchasereturn_Item->id},
						onClickValue: \"{$onclick}\"
					};";

					$script .= "jShopItemPrice.eq({$key}).attr('name', 'shop_item_price_{$oShop_Warehouse_Purchasereturn_Item->id}');
					jShopItemQuantity.eq({$key}).attr('name', 'shop_item_quantity_{$oShop_Warehouse_Purchasereturn_Item->id}');";
				}
				else
				{
					$script .= "jShopItemPrice.eq({$key}).remove(); jShopItemQuantity.eq({$key}).remove();";
				}
			}

			$script .= "jShopItemId.remove();

			$.each(aMapShopItemId, function(index, element){

				var jShopItemTr = $(\"#{$windowId} tr[data-item-id =\" + index + \"]\"),
					jDeleteA = jShopItemTr.find('a.delete-associated-item');

				jShopItemTr.attr('id', element.id);
				jDeleteA.attr('onclick', element.onClickValue);
			})";

			Core_Html_Entity::factory('Script')
					->value($script)
					->execute();
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());
		}

		// Было изменение склада
		/*$iOldWarehouse != $this->_object->shop_warehouse_id
			&& $bNeedsRePost = TRUE;

		($bNeedsRePost || !Core_Array::getPost('posted')) && $this->_object->unpost();
		Core_Array::getPost('posted') && $this->_object->post();*/

		Core_Array::getPost('posted')
			? $this->_object->post()
			: $this->_object->unpost();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}