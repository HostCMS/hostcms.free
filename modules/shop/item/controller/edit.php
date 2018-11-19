<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		$iShopItemId = Core_Array::getGet('shop_item_id', 0);

		// Магазин
		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));

		if ($oShop->id == 0)
		{
			$oShop = Core_Entity::factory('Shop_Item', $iShopItemId)->Shop;
		}

		switch ($modelName)
		{
			case 'shop_item':
				$this
					->addSkipColumn('image_large')
					->addSkipColumn('image_small')
					->addSkipColumn('shortcut_id');

				if ($object->shortcut_id != 0)
				{
					$object = $object->Shop_Item;
				}

				if (!$object->id)
				{
					$object->shop_id = Core_Array::getGet('shop_id');
					$object->shop_group_id = Core_Array::getGet('shop_group_id', 0);
					$object->shop_currency_id = $oShop->shop_currency_id;
					$object->shop_tax_id = $oShop->shop_tax_id;
				}

				if ($iShopItemId)
				{
					$ShopItemModification = Core_Entity::factory('Shop_Item', $iShopItemId);

					$object->modification_id = $iShopItemId;
					$object->shop_id = $ShopItemModification->Shop->id;

					$this->addSkipColumn('shop_group_id');
				}

				parent::setObject($object);

				$template_id = $this->_object->Shop->Structure->template_id
					? $this->_object->Shop->Structure->template_id
					: 0;

				$title = $this->_object->id
					? Core::_('Shop_Item.items_catalog_edit_form_title', $this->_object->name)
					: Core::_('Shop_Item.items_catalog_add_form_title');

				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				$oAdmin_Form_Controller = $this->_Admin_Form_Controller;

				$oAdditionalTab
					->add($oAdditionalRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				// $oMainTab
					// ->move($this->getField("apply_purchase_discount"), $oAdditionalRow1);

				$this->getField('image_small_height')
					->divAttr(array('style' => 'display: none'));
				$this->getField('image_small_width')
					->divAttr(array('style' => 'display: none'));
				$this->getField('image_large_height')
					->divAttr(array('style' => 'display: none'));
				$this->getField('image_large_width')
					->divAttr(array('style' => 'display: none'));

				// Создаем вкладки
				$oShopItemTabDescription = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Item.tab_description'))
					->name('Description');
				$oShopItemTabExportImport = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Item.tab_export'))
					->name('ExportImport');
				$oShopItemTabSEO = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Item.tab_seo'))
					->name('SEO');
				$oShopItemTabAssociated = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Item.tab_associated'))
					->name('Associateds');
				/*$oShopItemTabSpecialPrices = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Item.tab_special_prices'))
					->name('SpecialPrices');*/

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSetBlock = Admin_Form_Entity::factory('Div')->class('well with-header hidden-0 hidden-1 hidden-2'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShopItemTabDescription
					->add($oShopItemTabDescriptionRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabDescriptionRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabDescriptionRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabDescriptionRow4 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShopItemTabExportImport
					->add($oGuidRow = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oYandexMarketBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));

				$oYandexMarketBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-yellow')
						->value(Core::_("Shop_Item.yandex_market_header"))
					)
					->add($oShopItemTabExportImportRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabExportImportRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabExportImportRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabExportImportRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabExportImportRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabExportImportRow6 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShopItemTabSEO
					->add($oShopItemTabSEORow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabSEORow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabSEORow3 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				/*$oShopItemTabAssociated
					->add($oShopItemTabAssociatedRow1 = Admin_Form_Entity::factory('Div')->class('row'))
				;*/

				// Добавляем вкладки
				$this
					->addTabAfter($oShopItemTabDescription, $oMainTab)
					->addTabAfter($oShopItemTabExportImport, $oShopItemTabDescription)
					->addTabAfter($oShopItemTabSEO, $oShopItemTabExportImport)
					->addTabAfter($oShopItemTabAssociated, $oShopItemTabSEO)
					// ->addTabAfter($oShopItemTabSpecialPrices, $oShopItemTabSEO)
				;

				$oPropertyTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_("Shop_Item.tab_properties"))
					->name('Property');

				$this->addTabBefore($oPropertyTab, $oAdditionalTab);

				// Properties
				Shop_Item_Property_Controller_Tab::factory($this->_Admin_Form_Controller)
					->setObject($this->_object)
					->setDatasetId($this->getDatasetId())
					->linkedObject(Core_Entity::factory('Shop_Item_Property_List', $oShop->id))
					->setTab($oPropertyTab)
					->template_id($template_id)
					->fillTab();

				// Переносим поля на вкладки
				$oMainTab
					->move($oDescriptionField = $this->getField('description'), $oShopItemTabDescription)
					->move($this->getField('yandex_market'), $oShopItemTabExportImport)
					->move($this->getField('vendorcode'), $oShopItemTabExportImport)
					->move($this->getField('yandex_market_bid'), $oShopItemTabExportImport)
					->move($this->getField('yandex_market_cid'), $oShopItemTabExportImport)
					->move($this->getField('manufacturer_warranty'), $oShopItemTabExportImport)
					->move($this->getField('country_of_origin'), $oShopItemTabExportImport)
					->move($this->getField('guid'), $oShopItemTabExportImport)
					->move($this->getField('yandex_market_sales_notes'), $oShopItemTabExportImport)
					->move($this->getField('delivery'), $oShopItemTabExportImport)
					->move($this->getField('pickup'), $oShopItemTabExportImport)
					->move($this->getField('store'), $oShopItemTabExportImport)
					->move($this->getField('adult'), $oShopItemTabExportImport)
					->move($this->getField('cpa'), $oShopItemTabExportImport)
					->move($this->getField('seo_title')->rows(3), $oShopItemTabSEO)
					->move($this->getField('seo_description')->rows(3), $oShopItemTabSEO)
					->move($this->getField('seo_keywords')->rows(3), $oShopItemTabSEO)
				;

				$oShopItemTabExportImport
					->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')), $oGuidRow)
					->move($this->getField('yandex_market')->divAttr(array('class' => 'form-group col-xs-12')), $oShopItemTabExportImportRow1)
					->move($this->getField('yandex_market_bid')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6')), $oShopItemTabExportImportRow2)
					->move($this->getField('yandex_market_cid')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6')), $oShopItemTabExportImportRow2)
					->move($this->getField('manufacturer_warranty')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 margin-top-21')), $oShopItemTabExportImportRow3)
					->move($this->getField('vendorcode')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-8')), $oShopItemTabExportImportRow3)
					->move($this->getField('country_of_origin')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4')), $oShopItemTabExportImportRow4)
					->move($this->getField('yandex_market_sales_notes')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-8')), $oShopItemTabExportImportRow4)
					->move($this->getField('delivery')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4')), $oShopItemTabExportImportRow5)
					->move($this->getField('pickup')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4')), $oShopItemTabExportImportRow5)
					->move($this->getField('store')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4')), $oShopItemTabExportImportRow5)
					->move($this->getField('cpa')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4')), $oShopItemTabExportImportRow6)
					->move($this->getField('adult')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4')), $oShopItemTabExportImportRow6)
				;

				$oShop_Item_Delivery_Option_Controller_Tab = new Shop_Item_Delivery_Option_Controller_Tab($this->_Admin_Form_Controller);

				$oDeliveryOption = $oShop_Item_Delivery_Option_Controller_Tab
					->shop_id($oShop->id)
					->shop_item_id(intval($this->_object->id))
					->execute();

				$oYandexMarketBlock->add($oDeliveryOption);

				$oShopItemTabSEO
					->move($this->getField('seo_title')->divAttr(array('class' => 'form-group col-xs-12')), $oShopItemTabSEORow1)
					->move($this->getField('seo_description')->divAttr(array('class' => 'form-group col-xs-12')), $oShopItemTabSEORow2)
					->move($this->getField('seo_keywords')->divAttr(array('class' => 'form-group col-xs-12')), $oShopItemTabSEORow3)
				;

				$oDescriptionField
					->wysiwyg(TRUE)
					->rows(7)
					->template_id($template_id);

				$oShopItemTabDescription->move($oDescriptionField, $oShopItemTabDescriptionRow1);

				if (Core::moduleIsActive('typograph'))
				{
					$oDescriptionField->value(
						Typograph_Controller::instance()->eraseOpticalAlignment($oDescriptionField->value)
					);

					// поля описания товара
					$oTypographicDescriptionCheckBox = Admin_Form_Entity::factory('Checkbox');
					$oTypographicDescriptionCheckBox
						->value(
							$oShop->typograph_default_items == 1 ? 1 : 0
						)
						->caption(Core::_("Shop_Item.exec_typograph_for_text"))
						->name("exec_typograph_for_description")
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
						//->divAttr(array('style' => 'float: left'))
						;

					$oShopItemTabDescriptionRow2->add($oTypographicDescriptionCheckBox);

					$oOpticalAlignDescriptionCheckBox = Admin_Form_Entity::factory('Checkbox')
						->value(
							$oShop->typograph_default_items == 1 ? 1 : 0
						)
						->name("use_trailing_punctuation_for_description")
						->caption(Core::_("Shop_Item.use_trailing_punctuation_for_text"))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
						;

					$oShopItemTabDescriptionRow2->add($oOpticalAlignDescriptionCheckBox);
				}

				$oMainTab->moveAfter($oTextField = $this->getField('text'), isset($oOpticalAlignDescriptionCheckBox) ? $oOpticalAlignDescriptionCheckBox : $oDescriptionField, $oShopItemTabDescription);

				$oTextField
					->wysiwyg(TRUE)
					->rows(15)
					->template_id($template_id);

				$oShopItemTabDescription->move($oTextField, $oShopItemTabDescriptionRow3);

				if (Core::moduleIsActive('typograph'))
				{
					$oTextField->value(
						Typograph_Controller::instance()->eraseOpticalAlignment($oTextField->value)
					);

					// Добавляем два суррогатных поля текста товара
					$oTypographicTextCheckBox = Admin_Form_Entity::factory('Checkbox');
					$oTypographicTextCheckBox
						->value(
							$oShop->typograph_default_items == 1 ? 1 : 0
						)
						->caption(Core::_("Shop_Item.exec_typograph_for_text"))
						->name("exec_typograph_for_text")
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
						;

					$oShopItemTabDescriptionRow4->add($oTypographicTextCheckBox);

					$oOpticalAlignCheckBox = Admin_Form_Entity::factory('Checkbox');
					$oOpticalAlignCheckBox
						->value(
							$oShop->typograph_default_items == 1 ? 1 : 0
						)
						->name("use_trailing_punctuation_for_text")
						->caption(Core::_("Shop_Item.use_trailing_punctuation_for_text"))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

					$oShopItemTabDescriptionRow4->add($oOpticalAlignCheckBox);
				}

				// Группы ярлыков
				$oAdditionalGroupsSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Item.shortcut_group_tags'))
					->options($this->_fillShortcutGroupList($this->_object))
					->name('shortcut_group_id[]')
					->class('shortcut-group-tags')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-9'));

				$this->addField($oAdditionalGroupsSelect);

				$oMainRow3->add($oAdditionalGroupsSelect);

				$html2 = '
					<script>
						$(function(){
							$(".shortcut-group-tags").select2({
								language: "' . Core_i18n::instance()->getLng() . '",
								minimumInputLength: 2,
								placeholder: "' . Core::_('Shop_Item.select_group') . '",
								tags: true,
								allowClear: true,
								multiple: true,
								ajax: {
									url: "/admin/shop/item/index.php?shortcuts&shop_id=' . $this->_object->shop_id .'",
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
							});
						})</script>
					';

				$oMainRow3->add(Admin_Form_Entity::factory('Code')->html($html2));

				// Удаляем тип товара
				$oMainTab->delete($this->getField('type'));

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oRadioType = Admin_Form_Entity::factory('Radiogroup')
					->name('type')
					->id('shopItemType' . time())
					->caption(Core::_('Shop_Item.type'))
					->value($this->_object->type)
					->divAttr(array('class' => 'form-group col-xs-12 col-lg-10'))
					->radio(array(
						0 => Core::_('Shop_Item.item_type_selection_group_buttons_name_simple'),
						2 => Core::_('Shop_Item.item_type_selection_group_buttons_name_divisible'),
						1 => Core::_('Shop_Item.item_type_selection_group_buttons_name_electronic'),
						3 => Core::_('Shop_Item.item_type_selection_group_buttons_name_set')
					))
					->ico(
						array(
							0 => 'fa-file-text-o',
							2 => 'fa-puzzle-piece',
							1 => 'fa-table',
							3 => 'fa-archive',
					))
					->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1,2,3])");

				// Добавляем тип товара
				$oMainRow4->add($oRadioType);

				$sTmpHtml = '<div class="form-group col-lg-2 col-xs-12 margin-top-21">';

				if ($this->_object->id)
				{
					$additionalParams1 = "shop_item_id={$this->_object->id}&shop_group_id={$this->_object->shop_group_id}";
					$additionalParams2 = "shop_item_id={$this->_object->id}";

					$sTmpHtml .= '<div class="btn-group">
						<a href="' . $this->_Admin_Form_Controller->getAdminLoadHref('/admin/shop/item/associated/index.php', NULL, NULL, $additionalParams1) . '" onclick="' . $this->_Admin_Form_Controller->getAdminLoadAjax('/admin/shop/item/associated/index.php', NULL, NULL, $additionalParams1) . '" class="btn btn-default"><i class="fa fa-magnet no-margin"></i></a>
						<a href="' . $this->_Admin_Form_Controller->getAdminLoadHref('/admin/shop/item/associated/index.php', NULL, NULL, $additionalParams2) . '" onclick="' . $this->_Admin_Form_Controller->getAdminLoadAjax('/admin/shop/item/modification/index.php', NULL, NULL, $additionalParams2) . '" class="btn btn-default"><i class="fa fa-code-fork no-margin"></i></a>
					</div>';
				}
				$sTmpHtml .= "</div>";

				$oMainRow4->add(Admin_Form_Entity::factory('Code')->html($sTmpHtml));

				// Удаляем модификацию
				$oAdditionalTab->delete($this->getField('modification_id'));

				$iShopGroupId = $this->_object->modification_id
					? $this->_object->Modification->shop_group_id
					: $this->_object->shop_group_id;

				$oShop_Items = Core_Entity::factory('Shop_Item');
				$oShop_Items->queryBuilder()
					->where('shop_id', '=', $this->_object->shop_id)
					->where('shop_group_id', '=', $iShopGroupId);

				$iCountModifications = $oShop_Items->getCount();

				if ($iCountModifications < Core::$mainConfig['switchSelectToAutocomplete'])
				{
					$oModificationSelect = Admin_Form_Entity::factory('Select')
						->caption(Core::_('Shop_Item.shop_item_catalog_modification_flag'))
						->options(self::fillModificationList($this->_object))
						->name('modification_id')
						->value($this->_object->modification_id)
						->divAttr(array('class' => 'form-group col-xs-12 col-lg-3'));

					$oMainRow3->add($oModificationSelect);
				}
				else
				{
					$oModificationInput = Admin_Form_Entity::factory('Input')
						->caption(Core::_('Shop_Item.shop_item_catalog_modification_flag'))
						->divAttr(array('class' => 'form-group col-xs-12 col-lg-3'))
						->name('modification_name');

					if ($this->_object->modification_id)
					{
						$oModification = Core_Entity::factory('Shop_Item', $this->_object->modification_id);
						$oModificationInput->value($oModification->name . ' [' . $oModification->id . ']');
					}

					$oModificationInputHidden = Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12 hidden'))
						->name('modification_id')
						->value($this->_object->modification_id)
						->type('hidden');

					$oCore_Html_Entity_Script_Modification = Core::factory('Core_Html_Entity_Script')
					->value("
						$('[name = modification_name]').autocomplete({
							  source: function(request, response) {

								$.ajax({
								  url: '/admin/shop/item/index.php?autocomplete=1&show_modification=1&shop_item_id={$this->_object->id}',
								  dataType: 'json',
								  data: {
									queryString: request.term
								  },
								  success: function( data ) {
									response( data );
								  }
								});
							  },
							  minLength: 1,
							  create: function() {
								$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
									return $('<li></li>')
										.data('item.autocomplete', item)
										.append($('<a>').text(item.label))
										.appendTo(ul);
								}

								 $(this).prev('.ui-helper-hidden-accessible').remove();
							  },
							  select: function( event, ui ) {
								$('[name = modification_id]').val(ui.item.id);
							  },
							  open: function() {
								$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
							  },
							  close: function() {
								$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
							  }
						});
					");

					$oMainRow3
						->add($oModificationInput)
						->add($oModificationInputHidden)
						->add($oCore_Html_Entity_Script_Modification);
				}

				if (!$object->modification_id)
				{
					// Удаляем группу товаров
					$oAdditionalTab->delete($this->getField('shop_group_id'));

					// Добавляем группу товаров
					$aResult = $this->shopGroupShow('shop_group_id');
					foreach ($aResult as $resultItem)
					{
						$oMainRow1->add($resultItem);
					}
				}
				else
				{
					$this->_object->shop_group_id = 0;
				}

				// Комплекты
				$oSetBlock
					->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
						->class('header bordered-sky')
						->value(Core::_('Shop_Item.set_item_header'))
						->add(Admin_Form_Entity::factory('Checkbox')
							->value(0)
							->name('apply_recount_set')
							->divAttr(array('class' => 'pull-right apply-recount-set'))
							->caption(Core::_("Shop_Item.apply_recount_set"))
						)
					)
					->add($oSetRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSetRow2 = Admin_Form_Entity::factory('Div')->class('row'));

				$setTable = '
					<table class="table table-striped table-hover set-item-table">
						<thead>
							<tr>
								<th scope="col">' . Core::_('Shop_Item.name') . '</th>
								<th scope="col">' . Core::_('Shop_Item.marking') . '</th>
								<th scope="col">' . Core::_('Shop_Item.quantity') . '</th>
								<th scope="col">' . Core::_('Shop_Item.associated_item_price') . '</th>
								<th scope="col">  </th>
							</tr>
						</thead>
						<tbody>
				';

				$aShop_Item_Sets = $this->_object->Shop_Item_Sets->findAll(FALSE);

				foreach ($aShop_Item_Sets as $oShop_Item_Set)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Item_Set->shop_item_set_id);

					if (!is_null($oShop_Item))
					{
						$oShop_Item = $oShop_Item->shortcut_id
							? $oShop_Item->Shop_Item
							: $oShop_Item;

						$currencyName = $oShop_Item->Shop_Currency->name;

						$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteSetItem', NULL, 1, $oShop_Item->id, "set_item_id={$oShop_Item_Set->id}");

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

						$setTable .= '
							<tr id="' . $oShop_Item_Set->id . '">
								<td>' . htmlspecialchars($oShop_Item->name) . $externalLink . '</td>
								<td>' . htmlspecialchars($oShop_Item->marking) . '</td>
								<td width="25"><input class="set-item-count form-control" name="set_count_' . $oShop_Item_Set->id . '" value="' . $oShop_Item_Set->count . '" /></td>
								<td>' . htmlspecialchars($oShop_Item->price) . ' ' . htmlspecialchars($currencyName) . '</td>
								<td><a class="delete-associated-item" onclick="' . $onclick . '"><i class="fa fa-times-circle darkorange"></i></a></td>
							</tr>
						';
					}
				}

				$setTable .= '
						</tbody>
					</table>
				';

				$oSetRow1
					->add(Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12')
						->add(
							Admin_Form_Entity::factory('Code')->html($setTable)
						)
				);

				$oSetRow2->add(
					Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12'))
						->class('add-set-item form-control')
						->name('set_item_name')
				);

				$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
					->value("$('.add-set-item').autocompleteShopItem('{$this->_object->shop_id}', 0, function(event, ui) {
						$('.set-item-table > tbody').append(
							$('<tr><td>' + ui.item.label + '<input type=\'hidden\' name=\'set_item_id[]\' value=\'' + (typeof ui.item.id !== 'undefined' ? ui.item.id : 0) + '\'/>' + '</td><td>' + ui.item.marking + '</td><td><input class=\"set-item-count form-control\" name=\"set_count[]\" value=\"1.00\"/></td><td>' + ui.item.price_with_tax + ' ' + ui.item.currency + '</td><td><a class=\"delete-associated-item\" onclick=\"$(this).parents(\'tr\').remove()\"><i class=\"fa fa-times-circle darkorange\"></i></a></td></tr>')
						);

						ui.item.value = '';
					  } );");

				$oSetRow2->add($oCore_Html_Entity_Script);

				$oMainTab
					->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-lg-3 col-sm-6 col-xs-12')), $oMainRow5)
					->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-lg-3 col-sm-6 col-xs-12')), $oMainRow5)
					->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-lg-3 col-sm-6 col-xs-12')), $oMainRow5)
					->move($this->getField('showed')->divAttr(array('class' => 'form-group col-lg-3 col-sm-6 col-xs-12')), $oMainRow5)
				;

				// Добавляем новое поле типа файл
				$oImageField = Admin_Form_Entity::factory('File');

				$oLargeFilePath = is_file($this->_object->getLargeFilePath())
					? $this->_object->getLargeFileHref()
					: '';

				$oSmallFilePath = is_file($this->_object->getSmallFilePath())
					? $this->_object->getSmallFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();

				$oImageField
					->divAttr(array('class' => 'form-group col-xs-12'))
					->name("image")
					->id("image")
					->largeImage(array('max_width' => $oShop->image_large_max_width, 'max_height' => $oShop->image_large_max_height, 'path' => $oLargeFilePath, 'show_params' => TRUE, 'watermark_position_x' => $oShop->watermark_default_position_x, 'watermark_position_y' => $oShop->watermark_default_position_y, 'place_watermark_checkbox_checked' => $oShop->watermark_default_use_large_image, 'delete_onclick' =>
							"$.adminLoad({path: '{$sFormPath}', additionalParams:
							'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteLargeImage', windowId: '{$windowId}'}); return false", 'caption' => Core::_('Shop_Item.items_catalog_image'), 'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio)
					)
					->smallImage(array('max_width' => $oShop->image_small_max_width, 'max_height' => $oShop->image_small_max_height, 'path' => $oSmallFilePath, 'create_small_image_from_large_checked' =>
							$oShop->create_small_image && $this->_object->image_small == '', 'place_watermark_checkbox_checked' =>
							$oShop->watermark_default_use_small_image, 'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams:
							'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteSmallImage', windowId: '{$windowId}'}); return false", 'caption' => Core::_('Shop_Item.items_catalog_image_small'), 'show_params' => TRUE, 'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio_small)
					);

				$oMainRow6->add($oImageField);

				$oMainTab
					->move($this->getField('marking')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow7)
					->move($this->getField('weight')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4')), $oMainRow7);

				// Удаляем единицы измерения
				$oAdditionalTab->delete($this->getField('shop_measure_id'));

				$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);

				// Единицы измерения
				$oMainRow7->add(
					Admin_Form_Entity::factory('Select')
						->caption(Core::_('Shop_Item.shop_measure_id'))
						->divAttr(array('class' => 'form-group col-xs-6 col-sm-4'))
						->options($Shop_Controller_Edit->fillMeasures())
						->name('shop_measure_id')
						->value($this->_object->shop_measure_id)
				);

				$oMainTab
					->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow10 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow11 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oPriceBlock = Admin_Form_Entity::factory('Div')->id('prices')->class('well with-header'))
					->add($oSpecialPriceBlock = Admin_Form_Entity::factory('Div')->id('special_prices')->class('well with-header'))
				;

				// Удаляем группу доступа
				$oAdditionalTab->delete($this->getField('siteuser_group_id'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups(
						$this->_object->Shop->site_id
					);
				}
				else
				{
					$aSiteuser_Groups = array();
				}

				// Удаляем производителей
				$oAdditionalTab->delete($this->getField('shop_producer_id'));

				$oDefault_Shop_Producer = $this->_object->Shop->Shop_Producers->getDefault();

				$oShopProducerSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Item.shop_producer_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
					->options(self::fillProducersList($object->shop_id))
					->name('shop_producer_id')
						->value($this->_object->id
						? $this->_object->shop_producer_id
						: (!is_null($oDefault_Shop_Producer) ? $oDefault_Shop_Producer->id : 0)
					);

				// Добавляем продавцов
				$oMainRow10->add($oShopProducerSelect);

				// Создаем поле групп пользователей сайта как выпадающий список
				$oSiteUserGroupSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_("Shop_Item.siteuser_group_id"))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
					->options(array(-1 => Core::_('Shop_Item.shop_users_group_parrent')) + $aSiteuser_Groups)
					->name('siteuser_group_id')
					->value($this->_object->siteuser_group_id);

				// Добавляем группы пользователей сайта
				$oMainRow10->add($oSiteUserGroupSelect);

				$oAdditionalTab->delete($this->getField('siteuser_id'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser = $this->_object->Siteuser;

					$options = !is_null($oSiteuser->id)
						? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
						: array(0);

					$oSiteuserSelect = Admin_Form_Entity::factory('Select')
						->caption(Core::_('Shop_Item.siteuser_id'))
						->id('object_siteuser_id')
						->options($options)
						->name('siteuser_id')
						->class('siteuser-tag')
						->style('width: 100%')
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oMainRow10
						->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 col-sm-3 no-padding')
								->add($oSiteuserSelect)
						);

					// Show button
					Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
				}

				// Удаляем продавцов
				$oAdditionalTab->delete($this->getField('shop_seller_id'));

				$oDefault_Shop_Seller = $this->_object->Shop->Shop_Sellers->getDefault();

				$oShopSellerSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Item.shop_seller_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
					->options($this->_fillSellersList())
					->name('shop_seller_id')
					->value($this->_object->id
						? $this->_object->shop_seller_id
						: (!is_null($oDefault_Shop_Seller) ? $oDefault_Shop_Seller->id : 0)
					);

				// Добавляем продавцов
				$oMainRow10->add($oShopSellerSelect);

				// Перемещаем цену
				$oPriceBlock
					->add(Admin_Form_Entity::factory('Div')
							->class('header bordered-palegreen')
							->value(Core::_('Shop_Item.price_header'))
						)
					->add($oPriceRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('price')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-2'))
					->id('price');

				$oMainTab->move($this->getField('price'), $oPriceRow1);

				// Удаляем валюты
				$oAdditionalTab->delete($this->getField('shop_currency_id'));

				// Создаем поле валюты как выпадающий список
				$oShopCurrencySelect = Admin_Form_Entity::factory('Select')
					->caption("&nbsp;")
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-2'))
					->options($Shop_Controller_Edit->fillCurrencies())
					->name('shop_currency_id')
					->value($this->_object->shop_currency_id);

				// Добавляем валюты
				$oPriceRow1->add($oShopCurrencySelect);

				// Удаляем налоги
				$oAdditionalTab->delete($this->getField('shop_tax_id'));

				$oShopTaxSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_("Shop_Item.shop_tax_id"))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
					->options($this->fillTaxesList())
					->name('shop_tax_id')
					->value($this->_object->shop_tax_id);

				// Добавляем налоги
				$oPriceRow1->add($oShopTaxSelect);

				//Checkbox применения цен для модификаций
				if ($this->_object->Modifications->getCount())
				{
					$oModificationPrice = Admin_Form_Entity::factory('Checkbox')
						->value(0)
						->name("apply_price_for_modification")
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 margin-top-21'))
						->caption(Core::_("Shop_Item.apply_price_for_modification"));

					$oMainTab->addAfter($oModificationPrice, $oShopTaxSelect);
				}

				if (Core::moduleIsActive('siteuser') || defined('BACKEND_SHOP_PRICES'))
				{
					$oPriceBlock->add($oPricesRowN = Admin_Form_Entity::factory('Div')->class('row'));

					$oShop_Prices = $oShop->Shop_Prices;
					$oShop_Prices->queryBuilder()
						->clearOrderBy()
						->orderBy('shop_prices.sorting', 'ASC');

					$aShopPrices = $oShop_Prices->findAll(FALSE);

					foreach ($aShopPrices as $oShopPrice)
					{
						// Получаем значение специальной цены для товара
						$oShop_Item_Price = $this->_object->Shop_Item_Prices->getByPriceId($oShopPrice->id);

						$value = is_null($oShop_Item_Price)
							? 0
							: $oShop_Item_Price->value;

						$oItemPriceCheckBox = Admin_Form_Entity::factory('Checkbox')
							->caption(htmlspecialchars($oShopPrice->name))
							->id("item_price_id_{$oShopPrice->id}")
							->value(htmlspecialchars($value))
							->name("item_price_id_{$oShopPrice->id}")
							->divAttr(array('class' => 'form-group margin-top-10 col-xs-9 col-sm-6 col-md-4'))
							->onclick("document.getElementById('item_price_value_{$oShopPrice->id}').disabled
						= !this.checked; if (this.checked)
						{document.getElementById('item_price_value_{$oShopPrice->id}').value
						= (document.getElementById('price').value
						* {$oShopPrice->percent} / 100).toFixed(2); }");

						$oItemPriceTextBox = Admin_Form_Entity::factory('Input')
							->id("item_price_value_{$oShopPrice->id}")
							->name("item_price_value_{$oShopPrice->id}")
							->value($value)
							->divAttr(array('class' => 'form-group col-xs-3 col-sm-2'))
						;

						$value == 0 && $oItemPriceTextBox->disabled('disabled');

						$oPricesRowN
							->add($oItemPriceCheckBox)
							->add($oItemPriceTextBox);
					}
				}

				// Перемещаем спеццену
				$oSpecialPriceBlock
					->add(Admin_Form_Entity::factory('Div')
							->class('header bordered-azure')
							->value(Core::_('Shop_Item.special_price_header'))
						)
					->add($oSpecialPriceRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				// Заполняем вкладку специальных цен
				$aShop_Specialprices = $this->_object->Shop_Specialprices->findAll(FALSE);

				// Выводим форму добавления новой спеццены
				$oSpecMinQuantity = Admin_Form_Entity::factory('Input')
					->caption(Core::_("Shop_Item.form_edit_add_shop_special_prices_from"))
					->name('specMinQuantity_[]')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 col-md-3 col-lg-3'))
					->format(array('maxlen' => array('value' => 12), 'lib' => array('value' => 'integer')));

				$oSpecMaxQuantity = Admin_Form_Entity::factory('Input')
					->caption(Core::_("Shop_Item.form_edit_add_shop_special_prices_to"))
					->name('specMaxQuantity_[]')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 col-md-3 col-lg-3'))
					->format(array('maxlen' => array('value' => 12), 'lib' => array('value' => 'integer')));

				$oSpecPrice = Admin_Form_Entity::factory('Input')
					->caption(Core::_("Shop_Item.form_edit_add_shop_special_pricess_price"))
					->name('specPrice_[]')
					->divAttr(array('class' => 'form-group col-xs-4 col-sm-2 col-md-2 col-lg-2'))
					->format(array('maxlen' => array('value' => 12), 'lib' => array('value' => 'decimal')));

				ob_start();
				$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
					->style('float: left; padding-top: 30px;')
					->value(Core::_("Shop_Item.or"))
					->execute();
				$oOR = Admin_Form_Entity::factory('Code')->html(ob_get_clean());

				$oSpecPricePercent = Admin_Form_Entity::factory('Input')
					->caption(Core::_("Shop_Item.form_edit_add_shop_special_pricess_percent"))
					->name('specPercent_[]')
					->divAttr(array('class' => 'form-group col-xs-4 col-sm-2 col-md-2 col-lg-2'))
					->format(array('maxlen' => array('value' => 12), 'lib' => array('value' => 'decimal')));

				$oDivOpen = Admin_Form_Entity::factory('Code')->html('<div class="row spec_prices item_div clear" width="600">');
				$oDivClose = Admin_Form_Entity::factory('Code')->html('</div>');

				$oSpecialPriceRow1->add($oSpecialPriceDiv = Admin_Form_Entity::factory('Div'));

				if (count($aShop_Specialprices) > 0)
				{
					foreach ($aShop_Specialprices as $oShop_Specialprice)
					{
						$oSpecMinQuantity = clone $oSpecMinQuantity;
						$oSpecMaxQuantity = clone $oSpecMaxQuantity;
						$oSpecPrice = clone $oSpecPrice;
						$oSpecPricePercent = clone $oSpecPricePercent;

						$oSpecialPriceDiv
							->class('col-xs-12')
							->add($oDivOpen)
							->add(
								$oSpecMinQuantity
									->value($oShop_Specialprice->min_quantity)
									->name("specMinQuantity_{$oShop_Specialprice->id}")
									->id("specMinQuantity_{$oShop_Specialprice->id}")
							)
							->add(
								$oSpecMaxQuantity
									->value($oShop_Specialprice->max_quantity)
									->name("specMaxQuantity_{$oShop_Specialprice->id}")
									->id("specMaxQuantity_{$oShop_Specialprice->id}")
							)
							->add(
								$oSpecPrice
									->value($oShop_Specialprice->price)
									->name("specPrice_{$oShop_Specialprice->id}")
									->id("specPrice_{$oShop_Specialprice->id}")
							)
							->add($oOR)
							->add(
								$oSpecPricePercent
									->value($oShop_Specialprice->percent)
									->name("specPercent_{$oShop_Specialprice->id}")
									->id("specPercent_{$oShop_Specialprice->id}")
							)
							->add($this->imgBox())
							->add($oDivClose);
					}
				}
				else
				{
					$oSpecialPriceDiv
						->class('col-xs-12')
						->add($oDivOpen)
						->add($oSpecMinQuantity)
						->add($oSpecMaxQuantity)
						->add($oSpecPrice)
						->add($oOR)
						->add($oSpecPricePercent)
						->add($this->imgBox())
						->add($oDivClose);
				}

				// Получаем список складов магазина
				$aWarehouses = $oShop->Shop_Warehouses->findAll();

				if (count($aWarehouses))
				{
					$oMainTab
						->add($oWarehouseBlock = Admin_Form_Entity::factory('Div')->id('warehouses')->class('well with-header shop-item-warehouses-list'));

					$oWarehouseBlock
						->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
							->class('header bordered-pink')
							->value(Core::_("Shop_Item.warehouse_header"))
						);

					if ($this->_object->id)
					{
						$oHeaderDiv
							->add(Admin_Form_Entity::factory('A')
								->value(Core::_("Shop_Item.show_all_warehouses"))
								->class('pull-right')
								->onclick('$.toggleWarehouses()')
							);
					}

					foreach ($aWarehouses as $oWarehouse)
					{
						// Получаем количество товара на текущем складе
						$oWarehouseItem =
							$this->_object->Shop_Warehouse_Items->getByWarehouseId($oWarehouse->id);

						$countItems = is_null($oWarehouseItem)
							? (defined('DEFAULT_REST') ? DEFAULT_REST : 0)
							: $oWarehouseItem->count;

						$rowClass = !$this->_object->id || $countItems != 0
							? 'row'
							: 'row hidden';

						$oWarehouseBlock
							->add($oWarehouseCurrentRow = Admin_Form_Entity::factory('Div')->class($rowClass));

						$oWarehouseCurrentRow
							->add(
								Admin_Form_Entity::factory('Div')
									->value($oWarehouse->name)
									->class('form-group margin-top-10 col-xs-9 col-sm-6 col-md-4')
							)
							->add(
								Admin_Form_Entity::factory('Input')
									//->caption(Core::_("Shop_Item.warehouse_item_count", $oWarehouse->name))
									->value($countItems)
									->name("warehouse_{$oWarehouse->id}")
									->divAttr(array('class' => 'form-group col-xs-3 col-sm-4 col-md-2'))
							);

						if ($this->_object->id)
						{
							$oWarehouseCurrentRow
								->add(Admin_Form_Entity::factory('Div')
									->class('margin-top-10')
									->value(htmlspecialchars(
										$this->_object->Shop_Measure->name
									))
								);
						}
					}
				}

				if ($object->id)
				{
					$oSiteAlias = $oShop->Site->getCurrentAlias();
					if ($oSiteAlias)
					{
						$sItemUrl = ($oShop->Structure->https ? 'https://' : 'http://')
							. $oSiteAlias->name
							. $oShop->Structure->getPath()
							. $this->_object->getPath();

						$this->getField('path')
							->add(
								Admin_Form_Entity::factory('A')
									->target('_blank')
									->href($sItemUrl)
									->class('input-group-addon bg-blue bordered-blue')
									->value('<i class="fa fa-external-link"></i>')
							);
					}
				}

				$oMainTab
					->move($this->getField('path')->divAttr(array('class' => 'form-group col-xs-12 col-sm-8')), $oMainRow8)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow8)
					->move($this->getField('indexing')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow10)
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow10)
					->move($this->getField('apply_purchase_discount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow10);

				// Tags
				if (Core::moduleIsActive('tag'))
				{
					$oAdditionalTagsSelect = Admin_Form_Entity::factory('Select')
						->caption(Core::_('Shop_Item.items_catalog_tags'))
						->options($this->_fillTagsList($this->_object))
						->name('tags[]')
						->class('shop-item-tags')
						->style('width: 100%')
						->multiple('multiple')
						->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

					$oMainRow9->add($oAdditionalTagsSelect);

					$html = '
						<script>
							$(function(){
								$(".shop-item-tags").select2({
									language: "' . Core_i18n::instance()->getLng() . '",
									minimumInputLength: 1,
									placeholder: "' . Core::_('Shop_Item.type_tag') . '",
									tags: true,
									allowClear: true,
									multiple: true,
									ajax: {
										url: "/admin/tag/index.php?hostcms[action]=loadTagsList&hostcms[checked][0][0]=1",
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
								});
							})</script>
						';

					$oMainRow9->add(Admin_Form_Entity::factory('Code')->html($html));
				}

				$barcodeClass = Core::moduleIsActive('tag')
					? 'col-xs-12 col-md-6'
					: 'col-xs-12';

				$oAdditionalBarcodesSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Item.items_catalog_barcodes'))
					->options($this->_fillBarcodesList($this->_object))
					->name('barcodes[]')
					->class('shop-item-barcodes')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group ' . $barcodeClass));

				$oMainRow9->add($oAdditionalBarcodesSelect);

				$html = '
					<script>
						$(function(){
							$(".shop-item-barcodes").select2({
								language: "' . Core_i18n::instance()->getLng() . '",
								minimumInputLength: 1,
								placeholder: "' . Core::_('Shop_Item.type_barcode') . '",
								tags: true,
								allowClear: true,
								multiple: true,
								ajax: {
									url: "/admin/shop/item/index.php?loadBarcodesList&shop_item_id=' . $this->_object->id . '",
									dataType: "json",
									type: "GET",
									processResults: function (data) {
										var aResults = [];
										$.each(data, function (index, item) {
											var jInputName = $("#' . $windowId . ' input[name=\'name\']");
											!jInputName.val() && jInputName.val(item.name).focus();

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
							});
						})</script>
					';

				$oMainRow9->add(Admin_Form_Entity::factory('Code')->html($html));

				$aShop_Item_Associateds = $this->_object->Shop_Item_Associateds->findAll(FALSE);

				$associatedTable = '
					<table class="table table-striped table-hover associated-item-table">
						<thead>
							<tr>
								<th scope="col">' . Core::_('Shop_Item.name') . '</th>
								<th scope="col">' . Core::_('Shop_Item.marking') . '</th>
								<th scope="col">' . Core::_('Shop_Item.quantity') . '</th>
								<th scope="col">' . Core::_('Shop_Item.associated_item_price') . '</th>
								<th scope="col">  </th>
							</tr>
						</thead>
						<tbody>
				';

				foreach ($aShop_Item_Associateds as $oShop_Item_Associated)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Item_Associated->shop_item_associated_id);

					if (!is_null($oShop_Item))
					{
						$oShop_Item = $oShop_Item->shortcut_id
							? $oShop_Item->Shop_Item
							: $oShop_Item;

						$currencyName = $oShop_Item->Shop_Currency->name;

						$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item')->getByShopItemId($oShop_Item->id);

						$link = $oAdmin_Form_Controller->getAdminActionLoadAjax(/*$oAdmin_Form_Controller->getPath()*/'/admin/shop/item/index.php', 'deleteAssociated', NULL, 1, $oShop_Item->id, "associated_item_id={$oShop_Item_Associated->id}");

						$associatedTable .= '
							<tr id="' . $oShop_Item_Associated->id . '">
								<td>' . htmlspecialchars($oShop_Item->name) . '</td>
								<td>' . htmlspecialchars($oShop_Item->marking) . '</td>
								<td>' . (!is_null($oShop_Warehouse_Item) ? $oShop_Warehouse_Item->count : 0) . '</td>
								<td>' . htmlspecialchars($oShop_Item->price) . ' ' . $currencyName . '</td>
								<td><a class="delete-associated-item" onclick="' . $link . '"><i class="fa fa-times-circle darkorange"></i></a></td>
							</tr>
						';
					}
				}

				$associatedTable .= '
						</tbody>
					</table>
				';

				$oShopItemTabAssociated
					->add($oShopItemTabAssociatedRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemTabAssociatedRow2 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShopItemTabAssociatedRow1
					->add(Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12')
						->add(
							Admin_Form_Entity::factory('Code')->html($associatedTable)
						)
					);

				$oShopItemTabAssociatedRow2->add(
					Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12'))
						->class('add-associated-item form-control')
						->name('associated_item_name')
				);

				$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
					->value("$('.add-associated-item').autocompleteShopItem('{$this->_object->shop_id}', 0, function(event, ui) {
						$('<input type=\'hidden\' name=\'associated_item_id[]\'/>')
							.val(typeof ui.item.id !== 'undefined' ? ui.item.id : 0)
							.insertAfter($('.associated-item-table'));

						$('.associated-item-table > tbody').append(
							$('<tr><td>' + ui.item.label + '</td><td>' + ui.item.marking + '</td><td>' + ui.item.count + '</td><td>' + ui.item.price_with_tax + ' ' + ui.item.currency + '</td><td></td></tr>')
						);

						ui.item.value = '';
					  } );"
					);

				$oShopItemTabAssociated->add($oCore_Html_Entity_Script);

				$this->getField('length')
					->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4 no-padding-right'))
					->add(
						Core::factory('Core_Html_Entity_Span')
						->class('input-group-addon dimension_patch')
						->value('×')
					)
					->caption(Core::_('Shop_Item.item_length'));

				$oMainTab->move($this->getField('length'), $oMainRow11);

				$this->getField('width')
					->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4 no-padding'))
					->caption(Core::_('Shop_Item.item_width'))
					->add(
						Core::factory('Core_Html_Entity_Span')
						->class('input-group-addon dimension_patch')
						->value('×')
					);

				$oMainTab->move($this->getField('width'), $oMainRow11);

				$this->getField('height')
					->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4 no-padding'))
					->caption(Core::_('Shop_Item.item_height'))
					->add(
						Core::factory('Core_Html_Entity_Span')
							->class('input-group-addon dimension_patch')
							->value(Core::_('Shop.size_measure_'.$oShop->size_measure))
					);
				$oMainTab->move($this->getField('height'), $oMainRow11);

				$oMainTab
					->move($this->getField('min_quantity')->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4')), $oMainRow11)
					->move($this->getField('max_quantity')->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4')), $oMainRow11)
					->move($this->getField('quantity_step')->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4')), $oMainRow11)
					;

				$oMainTab->add(
					Admin_Form_Entity::factory('Code')
						->html("<script>radiogroupOnChange('{$windowId}', '{$this->_object->type}', [0,1,2,3])</script>")
				);
			break;

			case 'shop_group':
				if (!$object->id)
				{
					$object->shop_id = Core_Array::getGet('shop_id');
					$object->parent_id = Core_Array::getGet('shop_group_id');
				}

				// Пропускаем поля, обработка которых будет вестись вручную ниже
				$this
					->addSkipColumn('image_large')
					->addSkipColumn('image_small')
					->addSkipColumn('image_large_width')
					->addSkipColumn('image_large_height')
					->addSkipColumn('image_small_width')
					->addSkipColumn('image_small_height')
					->addSkipColumn('subgroups_count')
					->addSkipColumn('subgroups_total_count')
					->addSkipColumn('items_count')
					->addSkipColumn('items_total_count')
					;

				parent::setObject($object);

				$template_id = $this->_object->Shop->Structure->template_id
					? $this->_object->Shop->Structure->template_id
					: 0;

				// Получаем стандартные вкладки
				$oMainTab = $this->getTab('main');
				$oAdditionalTab = $this->getTab('additional');

				// Добавляем новые вкладки
				$this->addTabAfter($oShopGroupDescriptionTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Group.tab_group_description'))
					->name('Description'), $oMainTab);

				$this->addTabAfter($oShopGroupSeoTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Group.tab_group_seo'))
					->name('SEO'), $oShopGroupDescriptionTab);

				$this->addTabAfter($oShopTabSeoTemplates = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Group.tab_seo_templates'))
					->name('Seo_Templates'), $oShopGroupSeoTab);

				$oShopTabSeoTemplates
					->add($oShopGroupBlock = Admin_Form_Entity::factory('Div')->class('well with-header'))
					->add($oShopItemBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));

				$oShopGroupBlock
					->add($oShopGroupHeaderDiv = Admin_Form_Entity::factory('Div')
						->class('header bordered-darkorange')
						->value(Core::_("Shop_Group.seo_group_header"))
					)
					->add($oShopGroupBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopGroupBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopGroupBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$oShopGroupHeaderDiv
					->add(Admin_Form_Entity::factory('Code')->html(
						Shop_Controller::showGroupButton()
					));

				$oShopItemBlock
					->add($oShopItemHeaderDiv = Admin_Form_Entity::factory('Div')
						->class('header bordered-palegreen')
						->value(Core::_("Shop_Group.seo_item_header"))
					)
					->add($oShopItemBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopItemBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$oShopItemHeaderDiv
					->add(Admin_Form_Entity::factory('Code')->html(
						Shop_Controller::showItemButton()
					));

				// Seo templates
				$oMainTab
					->move($this->getField('seo_group_title_template')->divAttr(array('class' => 'form-group col-xs-12')), $oShopGroupBlockRow1)
					->move($this->getField('seo_group_description_template')->divAttr(array('class' => 'form-group col-xs-12')), $oShopGroupBlockRow2)
					->move($this->getField('seo_group_keywords_template')->divAttr(array('class' => 'form-group col-xs-12')), $oShopGroupBlockRow3)
					->move($this->getField('seo_item_title_template')->divAttr(array('class' => 'form-group col-xs-12')), $oShopItemBlockRow1)
					->move($this->getField('seo_item_description_template')->divAttr(array('class' => 'form-group col-xs-12')), $oShopItemBlockRow2)
					->move($this->getField('seo_item_keywords_template')->divAttr(array('class' => 'form-group col-xs-12')), $oShopItemBlockRow3);

				$this->addTabAfter($oShopGroupImportExportTab =
					Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Group.tab_yandex_market'))
					->name('ImportExport'), $oShopGroupSeoTab);

				$oPropertyTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_("Shop_Group.tab_properties"))
					->name('Property');

				$this->addTabBefore($oPropertyTab, $oAdditionalTab);

				// Properties
				Property_Controller_Tab::factory($this->_Admin_Form_Controller)
					->setObject($this->_object)
					->setDatasetId($this->getDatasetId())
					->linkedObject(Core_Entity::factory('Shop_Group_Property_List', $oShop->id))
					->setTab($oPropertyTab)
					->template_id($template_id)
					->fillTab();

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShopGroupDescriptionTab
					->add($oShopGroupDescriptionTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopGroupDescriptionTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShopGroupSeoTab
					->add($oShopGroupSeoTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopGroupSeoTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopGroupSeoTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShopGroupImportExportTab
					->add($oShopGroupImportExportTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				// Перемещаем поля на их вкладки
				$oMainTab
					->move($oDescriptionField = $this->getField('description'),
					$oShopGroupDescriptionTab)
					->move($oSeoTitleField = $this->getField('seo_title'), $oShopGroupSeoTab)
					->move($oSeoDescriptionField = $this->getField('seo_description'),
					$oShopGroupSeoTab)
					->move($oSeoKeywordsField = $this->getField('seo_keywords'),
					$oShopGroupSeoTab)
					->move($oGuidField = $this->getField('guid'), $oShopGroupImportExportTab)
				;

				// Удаляем поле parent_id
				$oAdditionalTab->delete($this->getField('parent_id'));

				// Добавляем группу товаров
				$aResult = $this->shopGroupShow('parent_id');
				foreach ($aResult as $resultItem)
				{
					$oMainRow1->add($resultItem);
				}

				// Добавляем новое поле типа файл
				$oImageField = Admin_Form_Entity::factory('File');

				$oLargeFilePath = is_file($this->_object->getLargeFilePath())
					? $this->_object->getLargeFileHref()
					: '';

				$oSmallFilePath = is_file($this->_object->getSmallFilePath())
					? $this->_object->getSmallFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oImageField
					->style("width: 400px;")
					->name("image")
					->id("image")
					->largeImage(array('max_width' => $oShop->group_image_large_max_width, 'max_height' => $oShop->group_image_large_max_height, 'path' => $oLargeFilePath, 'show_params' => TRUE, 'watermark_position_x' => $oShop->watermark_default_position_x, 'watermark_position_y' => $oShop->watermark_default_position_y, 'place_watermark_checkbox_checked' =>
						$oShop->watermark_default_use_large_image, 'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams:
						'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteLargeImage', windowId: '{$windowId}'}); return false", 'caption' => Core::_('Shop_Group.items_catalog_image'), 'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio_group))
					->smallImage(array('max_width' => $oShop->group_image_small_max_width, 'max_height' => $oShop->group_image_small_max_height, 'path' => $oSmallFilePath, 'create_small_image_from_large_checked' =>
						$oShop->create_small_image && $this->_object->image_small == '', 'place_watermark_checkbox_checked' =>
						$oShop->watermark_default_use_small_image, 'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams:
						'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteSmallImage', windowId: '{$windowId}'}); return false", 'caption' => Core::_('Shop_Group.items_catalog_image_small'), 'show_params' => TRUE, 'preserve_aspect_ratio_checkbox_checked' => $oShop->preserve_aspect_ratio_group_small));

				// Добавляем поле картинки группы товаров
				$oMainRow2->add($oImageField);

				$this->getField("sorting")->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));
				$this->getField("indexing")->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$this->getField("active")->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainTab
					->move($this->getField("indexing"), $oMainRow4)
					->move($this->getField("active"), $oMainRow4)
					->move($this->getField("sorting"), $oMainRow5);

				// Удаляем поле siteuser_group_id
				$oAdditionalTab->delete($this->getField('siteuser_group_id'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
				}
				else
				{
					$aSiteuser_Groups = array();
				}

				// Создаем поле групп пользователей сайта как выпадающий список
				$oSiteUserGroupSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_("Shop_Item.siteuser_group_id"))
					->options(array(-1 => Core::_('Shop_Item.shop_users_group_parrent')) + $aSiteuser_Groups)
					->name('siteuser_group_id')
					->value($this->_object->siteuser_group_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				// Добавляем группы пользователей сайта
				$oMainRow5->add($oSiteUserGroupSelect);

				$oAdditionalTab->delete($this->getField('siteuser_id'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser = $this->_object->Siteuser;

					$options = !is_null($oSiteuser->id)
						? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
						: array(0);

					$oSiteuserSelect = Admin_Form_Entity::factory('Select')
						->caption(Core::_('Shop_Group.siteuser_id'))
						->id('object_siteuser_id')
						->options($options)
						->name('siteuser_id')
						->class('siteuser-tag')
						->style('width: 100%')
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oMainRow5
						->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 col-sm-3 no-padding')
								->add($oSiteuserSelect)
						);

					// Show button
					Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
				}

				$oSiteAlias = $oShop->Site->getCurrentAlias();
				if ($oSiteAlias)
				{
					$sGroupUrl = ($oShop->Structure->https ? 'https://' : 'http://')
						. $oSiteAlias->name
						. $oShop->Structure->getPath()
						. $this->_object->getPath();

					$this->getField('path')
						->add(
							Admin_Form_Entity::factory('A')
								->target('_blank')
								->href($sGroupUrl)
								->class('input-group-addon bg-blue bordered-blue')
								->value('<i class="fa fa-external-link"></i>')
						);
				}

				$oMainTab->move($this->getField('path'), $oMainRow3);

				$oDescriptionField = $this->getField('description')
					->rows(15)
					->wysiwyg(TRUE)
					->template_id($template_id);

				$oShopGroupDescriptionTab
					->move($this->getField('description'), $oShopGroupDescriptionTabRow1)
				;

				if (Core::moduleIsActive('typograph'))
				{
					$oDescriptionField->value(
						Typograph_Controller::instance()->eraseOpticalAlignment($oDescriptionField->value)
					);

					$oTypographField = Admin_Form_Entity::factory('Checkbox');

					$oTypographField
						->caption(Core::_("Shop_Group.exec_typograph_for_description"))
						->value(
							$oShop->typograph_default_items == 1 ? 1 : 0
						)
						->name("exec_typograph_for_description")
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					;

					$oShopGroupDescriptionTabRow2->add($oTypographField);

					// и "Оптическое выравнивание"
					$oOpticalAlignmentField = Admin_Form_Entity::factory('Checkbox');

					$oOpticalAlignmentField
						->caption(Core::_("Shop_Group.use_trailing_punctuation_for_text"))
						->name("use_trailing_punctuation_for_text")
						->value(
							$oShop->typograph_default_items == 1 ? 1 : 0
						)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					;

					$oShopGroupDescriptionTabRow2->add($oOpticalAlignmentField);
				}

				$oShopGroupSeoTab->move($oSeoTitleField, $oShopGroupSeoTabRow1);
				$oShopGroupSeoTab->move($oSeoDescriptionField, $oShopGroupSeoTabRow2);
				$oShopGroupSeoTab->move($oSeoKeywordsField, $oShopGroupSeoTabRow3);


				$oShopGroupImportExportTab->move($oGuidField, $oShopGroupImportExportTabRow1);

				$oSeoDescriptionField->rows(5);
				$oSeoTitleField->rows(5);
				$oSeoKeywordsField->rows(5);

				// Выводим заголовок формы
				$title = $this->_object->id
					? Core::_("Shop_Group.groups_edit_form_title", $this->_object->name)
					: Core::_("Shop_Group.groups_add_form_title");

			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Item_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 * @return self
	 */
	protected function _applyObjectProperty()
	{
		$bNewObject = is_null($this->_object->id) && is_null(Core_Array::getPost('id'));

		$this->_formValues['siteuser_id'] = intval(Core_Array::get($this->_formValues, 'siteuser_id'));

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		$oShop = /*$bNewObject
			? Core_Entity::factory('Shop', intval(Core_Array::getGet('shop_id', 0)))
			: */ $this->_object->Shop;

		$modelName = $this->_object->getModelName();

		$aConfig = Core_Config::instance()->get('shop_config', array()) + array(
			'smallImagePrefix' => 'small_',
			'itemLargeImage' => 'item_%d.%s',
			'itemSmallImage' => 'small_item_%d.%s',
			'groupLargeImage' => 'group_%d.%s',
			'groupSmallImage' => 'small_group_%d.%s',
		);

		switch ($modelName)
		{
			case 'shop_item':
				if ($this->_object->modification_id)
				{
					$this->_object->shop_group_id = 0;
				}

				// Проверяем подключен ли модуль типографики.
				if (Core::moduleIsActive('typograph'))
				{
					// Проверяем, нужно ли применять типографику к описанию
					if (Core_Array::getPost('exec_typograph_for_description', 0))
					{
						$this->_object->description = Typograph_Controller::instance()->process
						($this->_object->description, Core_Array::getPost('use_trailing_punctuation_for_description', 0));
					}

					// Проверяем, нужно ли применять типографику к тексту
					if (Core_Array::getPost('exec_typograph_for_text', 0))
					{
						$this->_object->text = Typograph_Controller::instance()->process
						($this->_object->text, Core_Array::getPost('use_trailing_punctuation_for_text', 0));
					}
				}

				if ($this->_object->start_datetime == '')
				{
					$this->_object->start_datetime = '0000-00-00 00:00:00';
				}

				if ($this->_object->end_datetime == '')
				{
					$this->_object->end_datetime = '0000-00-00 00:00:00';
				}

				// Обработка меток
				if (Core::moduleIsActive('tag'))
				{
					$aRecievedTags = Core_Array::getPost('tags', array());
					!is_array($aRecievedTags) && $aRecievedTags = array();

					if (count($aRecievedTags) == 0
						&& $oShop->apply_tags_automatically
						|| $oShop->apply_keywords_automatically && $this->_object->seo_keywords == ''
					)
					{
						// Получаем хэш названия, описания и текста товара
						$array_text = Core_Str::getHashes(Core_Array::getPost('name') .
						Core_Array::getPost('description') . ' ' .
						Core_Array::getPost('text', ''), array('hash_function' => 'crc32'));
						$array_text = array_unique($array_text);

						$coeff_intersect = array ();

						$offset = 0;
						$limit = 100;

						do {
							$oTags = Core_Entity::factory('Tag');

							$oTags->queryBuilder()
								->offset($offset)
								->limit($limit);

							// Получаем список меток
							$aTags = $oTags->findAll(FALSE);

							foreach ($aTags as $oTag)
							{
								// Получаем хэш тэга
								$array_tags = Core_Str::getHashes($oTag->name, 	array('hash_function' => 'crc32'));

								// Получаем коэффициент схожести текста элемента с тэгом
								$array_tags = array_unique($array_tags);

								// Текст метки меньше текста инфоэлемента, т.к. должна
								// входить метка в текст инфоэлемента, а не наоборот
								if (count($array_text) >= count($array_tags))
								{
									// Расчитываем пересечение
									$intersect = count(array_intersect($array_text, $array_tags));

									$coefficient = count($array_tags) != 0
										? $intersect / count($array_tags)
										: 0;

									// Найдено полное вхождение
									if ($coefficient == 1 && !in_array($oTag->id, $coeff_intersect))
									{
										$coeff_intersect[] = $oTag->id;
									}
								}
							}
							$offset += $limit;
						}
						while (count($aTags));
					}

					// Автоматическое применение ключевых слов
					if ($oShop->apply_keywords_automatically && $this->_object->seo_keywords == '')
					{
						// Найдено соответствие с тэгами
						if (count($coeff_intersect))
						{
							$aTmp = array();
							foreach ($coeff_intersect as $tag_id)
							{
								$oTag = Core_Entity::factory('Tag', $tag_id);
								$aTmp[] = $oTag->name;
							}

							$this->_object->seo_keywords = implode(',', $aTmp);
						}
					}
					if (count($aRecievedTags) == 0 && $oShop->apply_tags_automatically && count($coeff_intersect))
					{
						// Получаем список связей меток с товаром
						$this->_object->Tag_Shop_Items->deleteAll();

						// Вставка тэгов автоматически разрешена
						if (count($coeff_intersect) > 0)
						{
							foreach ($coeff_intersect as $tag_id)
							{
								$oTag = Core_Entity::factory('Tag', $tag_id);
								$this->_object->add($oTag);
							}
						}
					}
					else
					{
						$this->_object->applyTagsArray($aRecievedTags);
					}
				}

				// Дополнительные цены для групп пользователей
				if (Core::moduleIsActive('siteuser') || defined('BACKEND_SHOP_PRICES'))
				{
					$aAdditionalPrices = $this->_object->Shop->Shop_Prices->findAll();

					foreach ($aAdditionalPrices as $oAdditionalPrice)
					{
						$oAdditionalPriceValue = $this->_object
							->Shop_Item_Prices
							->getByPriceId($oAdditionalPrice->id);

						if (is_null($oAdditionalPriceValue))
						{
							$oAdditionalPriceValue = Core_Entity::factory('Shop_Item_Price');
							$oAdditionalPriceValue->shop_item_id = $this->_object->id;
							$oAdditionalPriceValue->shop_price_id = $oAdditionalPrice->id;
						}

						if (!is_null(Core_Array::getPost("item_price_id_{$oAdditionalPrice->id}")))
						{
							$oAdditionalPriceValue->value = Core_Array::getPost("item_price_value_{$oAdditionalPrice->id}", 0);
							$oAdditionalPriceValue->save();
						}
						else
						{
							!is_null($oAdditionalPriceValue) && $oAdditionalPriceValue->delete();
						}
					}
				}

				// Сопутствующие товары
				$aAddAssociatedItems = Core_Array::getPost('associated_item_id', array());

				if (count($aAddAssociatedItems))
				{
					foreach ($aAddAssociatedItems as $associated_item_id)
					{
						$iCount = $this->_object->Shop_Item_Associateds->getCountByshop_item_associated_id($associated_item_id);

						if (!$iCount)
						{
							$oShop_Item_Associated = Core_Entity::factory('Shop_Item_Associated');
							$oShop_Item_Associated
								->shop_item_associated_id($associated_item_id)
								->shop_item_id($this->_object->id)
								->count(1)
								->save();
						}
					}
				}

				// Существующие товары из сета
				$aShop_Item_Sets = $this->_object->Shop_Item_Sets->findAll(FALSE);
				foreach ($aShop_Item_Sets as $oShop_Item_Set)
				{
					$count = Core_Array::getPost('set_count_' . $oShop_Item_Set->id);

					if ($count > 0)
					{
						$oShop_Item_Set->count = $count;
						$oShop_Item_Set->save();
					}
					else
					{
						$oShop_Item_Set->delete();
					}
				}

				// Новые товары в сете
				$aAddSetItems = Core_Array::getPost('set_item_id', array());
				foreach ($aAddSetItems as $key => $shop_item_set_id)
				{
					$iCount = $this->_object->Shop_Item_Sets->getCountByshop_item_set_id($shop_item_set_id);

					if (!$iCount)
					{
						$oShop_Item_Set = Core_Entity::factory('Shop_Item_Set');
						$oShop_Item_Set
							->shop_item_set_id($shop_item_set_id)
							->shop_item_id($this->_object->id)
							->count(
								isset($_POST['set_count'][$key]) ? $_POST['set_count'][$key] : 1
							)
							->save();
					}
				}

				// Яндекс.Маркет доставка
				$oShop_Item_Delivery_Option_Controller_Tab = new Shop_Item_Delivery_Option_Controller_Tab($this->_Admin_Form_Controller);
				$oShop_Item_Delivery_Option_Controller_Tab
					->shop_id($oShop->id)
					->shop_item_id(intval($this->_object->id))
					->applyObjectProperty();

				// Специальные цены, установленные значения
				$aShop_Specialprices = $this->_object->Shop_Specialprices->findAll();
				foreach ($aShop_Specialprices as $oShop_Specialprice)
				{
					if (!is_null(Core_Array::getPost("specPrice_{$oShop_Specialprice->id}")))
					{
						$oShop_Specialprice
							->min_quantity(intval(Core_Array::getPost("specMinQuantity_{$oShop_Specialprice->id}", 0)))
							->max_quantity(intval(Core_Array::getPost("specMaxQuantity_{$oShop_Specialprice->id}", 0)))
							->price(Shop_Controller::instance()->convertPrice(Core_Array::getPost("specPrice_{$oShop_Specialprice->id}", 0)))
							->percent(Shop_Controller::instance()->convertPrice(Core_Array::getPost("specPercent_{$oShop_Specialprice->id}", 0)));

						$oShop_Specialprice->price || $oShop_Specialprice->percent
							? $oShop_Specialprice->save()
							: $oShop_Specialprice->delete();
					}
					else
					{
						$oShop_Specialprice->delete();
					}
				}

				// Специальные цены, новые значения
				$windowId = $this->_Admin_Form_Controller->getWindowId();
				$aSpecPrices = Core_Array::getPost('specPrice_');
				if ($aSpecPrices)
				{
					$aSpecMinQuantity = Core_Array::getPost('specMinQuantity_');
					$aSpecMaxQuantity = Core_Array::getPost('specMaxQuantity_');
					$aSpecPercent = Core_Array::getPost('specPercent_');

					foreach ($aSpecPrices as $key => $specPrice)
					{
						$price = Shop_Controller::instance()->convertPrice($specPrice);
						$percent = Shop_Controller::instance()->convertPrice(Core_Array::get($aSpecPercent, $key));

						if ($price || $percent)
						{
							$oShop_Specialprice = Core_Entity::factory('Shop_Specialprice')
								->min_quantity(intval(Core_Array::get($aSpecMinQuantity, $key)))
								->max_quantity(intval(Core_Array::get($aSpecMaxQuantity, $key)))
								->price($price)
								->percent($percent);
							$this->_object->add($oShop_Specialprice);

							ob_start();
							Core::factory('Core_Html_Entity_Script')
								->value("$(\"#{$windowId} input[name='specMinQuantity_\\[\\]']\").eq(0).prop('name', 'specMinQuantity_{$oShop_Specialprice->id}');
								$(\"#{$windowId} input[name='specMaxQuantity_\\[\\]']\").eq(0).prop('name', 'specMaxQuantity_{$oShop_Specialprice->id}');
								$(\"#{$windowId} input[name='specPrice_\\[\\]']\").eq(0).prop('name', 'specPrice_{$oShop_Specialprice->id}');
								$(\"#{$windowId} input[name='specPercent_\\[\\]']\").eq(0).prop('name', 'specPercent_{$oShop_Specialprice->id}');
								")
								->execute();

							$this->_Admin_Form_Controller->addMessage(ob_get_clean());
						}
					}
				}

				// Properties
				Shop_Item_Property_Controller_Tab::factory($this->_Admin_Form_Controller)
					->setObject($this->_object)
					->linkedObject(Core_Entity::factory('Shop_Item_Property_List', $oShop->id))
					->applyObjectProperty();

				// Обработка складов
				$aShopWarehouses = $oShop->Shop_Warehouses->findAll();

				foreach ($aShopWarehouses as $oShopWarehouse)
				{
					$iWarehouseValue = Core_Array::getPost("warehouse_{$oShopWarehouse->id}", 0);

					$oShopItemWarehouse = $this->_object->Shop_Warehouse_Items->getByWarehouseId($oShopWarehouse->id);
					if (is_null($oShopItemWarehouse))
					{
						$oShopItemWarehouse = Core_Entity::factory('Shop_Warehouse_Item');
						$oShopItemWarehouse->shop_warehouse_id = $oShopWarehouse->id;
						$oShopItemWarehouse->shop_item_id = $this->_object->id;
					}

					$oShopItemWarehouse->count = $iWarehouseValue;
					$oShopItemWarehouse->save();
				}

				if (Core_Array::getPost('apply_price_for_modification'))
				{
					$aModifications = $this->_object->Modifications->findAll();
					foreach ($aModifications as $oModification)
					{
						$oModification->price = $this->_object->price;
						$oModification->shop_currency_id = $this->_object->shop_currency_id;
						$oModification->save();
					}
				}

				$aShortcutGroupIds = Core_Array::getPost('shortcut_group_id', array());
				!is_array($aShortcutGroupIds) && $aShortcutGroupIds = array();

				$aTmp = array();

				// Выбранные группы
				$aShortcuts = $oShop->Shop_Items->getAllByShortcut_id($this->_object->id, FALSE);
				foreach ($aShortcuts as $oShortcut)
				{
					!in_array($oShortcut->shop_group_id, $aShortcutGroupIds)
						? $oShortcut->markDeleted()
						: $aTmp[] = $oShortcut->shop_group_id;
				}

				$aNewShortcutGroupIDs = array_diff($aShortcutGroupIds, $aTmp);
				foreach ($aNewShortcutGroupIDs as $iShortcutGroupId)
				{
					$oShop_Group = $oShop->Shop_Groups->getById($iShortcutGroupId);
					if (!is_null($oShop_Group))
					{
						$oShop_ItemShortcut = Core_Entity::factory('Shop_Item');

						$oShop_ItemShortcut->shop_id = $this->_object->shop_id;
						$oShop_ItemShortcut->shortcut_id = $this->_object->id;
						$oShop_ItemShortcut->shop_group_id = $iShortcutGroupId;
						$oShop_ItemShortcut->datetime = $this->_object->datetime;
						$oShop_ItemShortcut->name = '';
						$oShop_ItemShortcut->type = $this->_object->type;
						$oShop_ItemShortcut->path = '';
						$oShop_ItemShortcut->indexing = 0;

						$oShop_ItemShortcut->save()->clearCache();
					}
				}

				// Barcodes
				$aBarcodes = Core_Array::getPost('barcodes', array());
				!is_array($aBarcodes) && $aBarcodes = array();

				$aTmp = array();

				$aShop_Item_Barcodes = $this->_object->Shop_Item_Barcodes->findAll(FALSE);
				foreach ($aShop_Item_Barcodes as $oShop_Item_Barcode)
				{
					!in_array($oShop_Item_Barcode->value, $aBarcodes)
						? $oShop_Item_Barcode->markDeleted()
						: $aTmp[] = $oShop_Item_Barcode->value;
				}

				$aNewBarcodes = array_diff($aBarcodes, $aTmp);
				foreach ($aNewBarcodes as $value)
				{
					$oShop_Item_Barcode = Core_Entity::factory('Shop_Item_Barcode');
					$oShop_Item_Barcode
						->value($value)
						->shop_item_id($this->_object->id)
						->setType()
						->save();
				}

				// Пересчет комплекта
				Core_Array::getPost('apply_recount_set') && $this->_object->recountSet();
			break;
			case 'shop_group':
			default:

				// Проверяем подключен ли модуль типографики.
				if (Core::moduleIsActive('typograph'))
				{
					// Проверяем, нужно ли применять типографику к описанию информационной группы.
					if (Core_Array::getPost('exec_typograph_for_description', 0))
					{
						$this->_object->description =
						Typograph_Controller::instance()->process($this->_object->description, Core_Array::getPost('use_trailing_punctuation_for_text', 0));
					}
				}

				// Properties
				Property_Controller_Tab::factory($this->_Admin_Form_Controller)
					->setObject($this->_object)
					->linkedObject(Core_Entity::factory('Shop_Group_Property_List', $oShop->id))
					->applyObjectProperty();

				if ($bNewObject)
				{
					$aShop_Item_Property_For_Groups = Core_Entity::factory('Shop_Group', $this->_object->parent_id)->Shop_Item_Property_For_Groups->findAll();

					foreach ($aShop_Item_Property_For_Groups as $oShop_Item_Property_For_Group)
					{
						$oShop_Item_Property_For_Group_new = clone $oShop_Item_Property_For_Group;
						$oShop_Item_Property_For_Group_new->shop_group_id = $this->_object->id;
						$oShop_Item_Property_For_Group_new->save();
					}
				}
		}

		// Clear tagged cache
		$this->_object->clearCache();

		// Обработка картинок
		$param = array();

		$large_image = $small_image = '';

		$aCore_Config = Core::$mainConfig;

		$create_small_image_from_large = Core_Array::getPost(
		'create_small_image_from_large_small_image');

		$bLargeImageIsCorrect =
			// Поле файла большого изображения существует
			!is_null($aFileData = Core_Array::getFiles('image', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0;

		if ($bLargeImageIsCorrect)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'], $aCore_Config['availableExtension']))
			{
				// Удаление файла большого изображения
				if ($this->_object->image_large)
				{
					// !! дописать метод
					$this->_object->deleteLargeImage();
				}

				$file_name = $aFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oShop->change_filename)
				{
					$large_image = $file_name;
				}
				else
				{
					// Определяем расширение файла
					$ext = Core_File::getExtension($aFileData['name']);
					//$large_image = 'information_groups_' . $this->_object->id . '.' . $ext;

					$large_image = $modelName == 'shop_item'
						? sprintf($aConfig['itemLargeImage'], $this->_object->id, $ext)
						: sprintf($aConfig['groupLargeImage'], $this->_object->id, $ext);
				}
			}
			else
			{
				$this->addMessage(Core_Message::get(Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])), 'error'));
			}
		}

		$aSmallFileData = Core_Array::getFiles('small_image', NULL);
		$bSmallImageIsCorrect =
			// Поле файла малого изображения существует
			!is_null($aSmallFileData)
			&& $aSmallFileData['size'];

		// Задано малое изображение и при этом не задано создание малого изображения
		// из большого или задано создание малого изображения из большого и
		// при этом не задано большое изображение.

		if ($bSmallImageIsCorrect || $create_small_image_from_large && $bLargeImageIsCorrect)
		{
			// Удаление файла малого изображения
			if ($this->_object->image_small)
			{
				// !! дописать метод
				$this->_object->deleteSmallImage();
			}

			// Явно указано малое изображение
			if ($bSmallImageIsCorrect
				&& Core_File::isValidExtension($aSmallFileData['name'],
				$aCore_Config['availableExtension']))
			{
				// Для инфогруппы ранее задано изображение
				if ($this->_object->image_large != '')
				{
					// Существует ли большое изображение
					$param['large_image_isset'] = true;
					$create_large_image = false;
				}
				else // Для информационной группы ранее не задано большое изображение
				{
					$create_large_image = empty($large_image);
				}

				$file_name = $aSmallFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oShop->change_filename)
				{
					if ($create_large_image)
					{
						$large_image = $file_name;
						$small_image = $aConfig['smallImagePrefix'] . $large_image;
					}
					else
					{
						$small_image = $file_name;
					}
				}
				else
				{
					// Определяем расширение файла
					$ext = Core_File::getExtension($file_name);

					$small_image = $modelName == 'shop_item'
						? sprintf($aConfig['itemSmallImage'], $this->_object->id, $ext)
						: sprintf($aConfig['groupSmallImage'], $this->_object->id, $ext);

				}
			}
			elseif ($create_small_image_from_large && $bLargeImageIsCorrect)
			{
				$small_image = $aConfig['smallImagePrefix'] . $large_image;
			}
			// Тип загружаемого файла является недопустимым для загрузки файла
			else
			{
				$this->addMessage(Core_Message::get(Core::_('Core.extension_does_not_allow', Core_File::getExtension($aSmallFileData['name'])), 'error'));
			}
		}

		if ($bLargeImageIsCorrect || $bSmallImageIsCorrect)
		{
			if ($bLargeImageIsCorrect)
			{
				// Путь к файлу-источнику большого изображения;
				$param['large_image_source'] = $aFileData['tmp_name'];
				// Оригинальное имя файла большого изображения
				$param['large_image_name'] = $aFileData['name'];
			}

			if ($bSmallImageIsCorrect)
			{
				// Путь к файлу-источнику малого изображения;
				$param['small_image_source'] = $aSmallFileData['tmp_name'];
				// Оригинальное имя файла малого изображения
				$param['small_image_name'] = $aSmallFileData['name'];
			}

			if ($modelName == 'shop_group')
			{
				// Путь к создаваемому файлу большого изображения;
				$param['large_image_target'] = !empty($large_image)
					? $this->_object->getGroupPath() . $large_image
					: '';

				// Путь к создаваемому файлу малого изображения;
				$param['small_image_target'] = !empty($small_image)
					? $this->_object->getGroupPath() . $small_image
					: '' ;
			}
			else
			{
				// Путь к создаваемому файлу большого изображения;
				$param['large_image_target'] = !empty($large_image)
					? $this->_object->getItemPath() . $large_image
					: '';

				// Путь к создаваемому файлу малого изображения;
				$param['small_image_target'] = !empty($small_image)
					? $this->_object->getItemPath() . $small_image
					: '' ;
			}

			// Использовать большое изображение для создания малого
			$param['create_small_image_from_large'] = !is_null(Core_Array::getPost('create_small_image_from_large_small_image'));

			// Значение максимальной ширины большого изображения
			$param['large_image_max_width'] = Core_Array::getPost('large_max_width_image', 0);

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = Core_Array::getPost('large_max_height_image', 0);

			// Значение максимальной ширины малого изображения;
			$param['small_image_max_width'] = Core_Array::getPost('small_max_width_small_image');

			// Значение максимальной высоты малого изображения;
			$param['small_image_max_height'] = Core_Array::getPost('small_max_height_small_image');

			// Путь к файлу с "водяным знаком"
			$param['watermark_file_path'] = $oShop->getWatermarkFilePath();

			// Позиция "водяного знака" по оси X
			$param['watermark_position_x'] = Core_Array::getPost('watermark_position_x_image');

			// Позиция "водяного знака" по оси Y
			$param['watermark_position_y'] = Core_Array::getPost('watermark_position_y_image');

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['large_image_watermark'] = !is_null(Core_Array::getPost('large_place_watermark_checkbox_image'));

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['small_image_watermark'] = !is_null(Core_Array::getPost('small_place_watermark_checkbox_small_image'));

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('large_preserve_aspect_ratio_image'));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('small_preserve_aspect_ratio_small_image'));

			$this->_object->createDir();

			$result = Core_File::adminUpload($param);

			if ($result['large_image'])
			{
				$this->_object->image_large = $large_image;
				$this->_object->setLargeImageSizes();
			}

			if ($result['small_image'])
			{
				$this->_object->image_small = $small_image;
				$this->_object->setSmallImageSizes();
			}
		}

		$this->_object->save();

		// Index item
		$this->_object->index();

		if ($modelName == 'shop_item')
		{
			// Index item by schedule
			if (Core::moduleIsActive('schedule')
				&& $this->_object->start_datetime != '0000-00-00 00:00:00'
				&& Core_Date::sql2timestamp($this->_object->start_datetime) > time())
			{
				$oModule = Core_Entity::factory('Module')->getByPath('shop');

				if (!is_null($oModule->id))
				{
					$oSchedule = Core_Entity::factory('Schedule');
					$oSchedule->module_id = $oModule->id;
					$oSchedule->site_id = CURRENT_SITE;
					$oSchedule->entity_id = $this->_object->id;
					$oSchedule->action = 0;
					$oSchedule->start_datetime = $this->_object->start_datetime;
					$oSchedule->save();
				}
			}

			// Unindex item by schedule
			if (Core::moduleIsActive('schedule')
				&& $this->_object->end_datetime != '0000-00-00 00:00:00'
				&& Core_Date::sql2timestamp($this->_object->end_datetime) > time())
			{
				$oModule = Core_Entity::factory('Module')->getByPath('shop');

				if (!is_null($oModule->id))
				{
					$oSchedule = Core_Entity::factory('Schedule');
					$oSchedule->module_id = $oModule->id;
					$oSchedule->site_id = CURRENT_SITE;
					$oSchedule->entity_id = $this->_object->id;
					$oSchedule->action = 2;
					$oSchedule->start_datetime = $this->_object->end_datetime;
					$oSchedule->save();
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$shop_id = Core_Array::getPost('shop_id');
			$path = Core_Array::getPost('path');

			/*if ($path == '')
			{
				$this->_object->name = Core_Array::getPost('name');
				$this->_object->path = Core_Array::getPost('path');
				// id еще не определен, поэтому makePath() не может работать корректно
				//$this->_object->makePath();

				$path = $this->_object->path;

				$this->addSkipColumn('path');
			}*/

			if (strlen($path))
			{
				$modelName = $this->_object->getModelName();

				switch ($modelName)
				{
					case 'shop_item':
						$shop_group_id = Core_Array::getPost('shop_group_id');

						$oSameShopItem = Core_Entity::factory('Shop', $shop_id)
							->Shop_Items
							->getByGroupIdAndPath($shop_group_id, $path);

						if (!is_null($oSameShopItem) && $oSameShopItem->id != Core_Array::getPost('id'))
						{
							$this->addMessage(Core_Message::get(Core::_('Shop_Item.error_URL_shop_item'), 'error')
							);
							return TRUE;
						}

						$oSameShopGroup = Core_Entity::factory('Shop', $shop_id)
							->Shop_Groups
							->getByParentIdAndPath($shop_group_id, $path);

						if (!is_null($oSameShopGroup))
						{
							$this->addMessage(Core_Message::get(Core::_('Shop_Item.error_URL_isset_group') , 'error')
							);
							return TRUE;
						}
					break;
					case 'shop_group':
						$parent_id = Core_Array::getPost('parent_id');

						$oSameShopGroup = Core_Entity::factory('Shop', $shop_id)
							->Shop_Groups
							->getByParentIdAndPath($parent_id, $path);

						if (!is_null($oSameShopGroup) && $oSameShopGroup->id != Core_Array::getPost('id'))
						{
							$this->addMessage(
								Core_Message::get(Core::_('Shop_Group.error_URL_shop_group'), 'error')
							);
							return TRUE;
						}

						$oSameShopItem = Core_Entity::factory('Shop', $shop_id)
							->Shop_Items
							->getByGroupIdAndPath($parent_id, $path);

						if (!is_null($oSameShopItem))
						{
							$this->addMessage(
								Core_Message::get(Core::_('Shop_Group.error_URL_isset_item'), 'error')
							);
							return TRUE;
						}
					break;
				}
			}
		}

		return parent::execute($operation);
	}

	/**
	 * Показ списка групп или поле ввода с autocomplete для большого количества групп
	 * @param string $fieldName имя поля группы
	 * @return array  массив элементов, для доабвления в строку
	 */
	public function shopGroupShow($fieldName)
	{
		$return = array();

		$iCountGroups = $this->_object->Shop->Shop_Groups->getCount();

		switch (get_class($this->_object))
		{
			case 'Shop_Item_Model':
				$i18n = 'Shop_Item';
				$aExclude = array();
			break;
			case 'Shop_Group_Model':
			default:
				$i18n = 'Shop_Group';
				$aExclude = array($this->_object->id);
		}

		if ($iCountGroups < Core::$mainConfig['switchSelectToAutocomplete'])
		{
			$oShopGroupSelect = Admin_Form_Entity::factory('Select');
			$oShopGroupSelect
				->caption(Core::_($i18n . '.' . $fieldName))
				->options(array(' … ') + self::fillShopGroup($this->_object->shop_id, 0, $aExclude))
				->name($fieldName)
				->value($this->_object->$fieldName)
				->divAttr(array('class' => 'form-group col-xs-12'))
				->filter(TRUE);

			$return = array($oShopGroupSelect);
		}
		else
		{
			$oShop_Group = Core_Entity::factory('Shop_Group', $this->_object->$fieldName);

			$oShopGroupInput = Admin_Form_Entity::factory('Input')
				->caption(Core::_($i18n . '.' . $fieldName))
				->divAttr(array('class' => 'form-group col-xs-12'))
				->name('shop_group_name');

			$this->_object->$fieldName
				&& $oShopGroupInput->value($oShop_Group->name . ' [' . $oShop_Group->id . ']');

			$oShopGroupInputHidden = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->name($fieldName)
				->value($this->_object->$fieldName)
				->type('hidden');

			$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
				->value("
					$('[name = shop_group_name]').autocomplete({
						  source: function(request, response) {

							$.ajax({
							  url: '/admin/shop/item/index.php?autocomplete=1&show_group=1&shop_id={$this->_object->shop_id}',
							  dataType: 'json',
							  data: {
								queryString: request.term
							  },
							  success: function( data ) {
								response( data );
							  }
							});
						  },
						  minLength: 1,
						  create: function() {
							$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
								return $('<li></li>')
									.data('item.autocomplete', item)
									.append($('<a>').text(item.label))
									.appendTo(ul);
							}

							 $(this).prev('.ui-helper-hidden-accessible').remove();
						  },
						  select: function( event, ui ) {
							$('[name = {$fieldName}]').val(ui.item.id);
						  },
						  open: function() {
							$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
						  },
						  close: function() {
							$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
						  }
					});
				");

			$return = array($oShopGroupInput, $oShopGroupInputHidden, $oCore_Html_Entity_Script);
		}

		return $return;
	}

	/**
	 * Shop producer dirs tree
	 * @var array
	 */
	static protected $_aProducerDir = array();

	/**
	 * Build visual representation of producer dirs tree
	 * @param int $iShopId shop ID
	 * @param int $iShopProducerDirParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillProducersList($iShopId, $iShopProducerDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShopId = intval($iShopId);
		$iShopProducerDirParentId = intval($iShopProducerDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_producer_dirs')
				->where('shop_id', '=', $iShopId)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aDir)
			{
				self::$_aProducerDir[$aDir['parent_id']][] = $aDir;
			}
		}

		$aReturn = array(' … ');

		if (isset(self::$_aProducerDir[$iShopProducerDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aProducerDir[$iShopProducerDirParentId] as $childrenDir)
			{
				if ($countExclude == 0 || !in_array($childrenDir['id'], $aExclude))
				{
					$aReturn['dir-' . $childrenDir['id']] = array(
						'value' => str_repeat('  ', $iLevel) . $childrenDir['name'] . ' [' . $childrenDir['id'] . ']',
						'attr' => array('disabled' => 'disabled')
					);
					$aReturn += self::fillProducersList($iShopId, $childrenDir['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$oShop_Producers = Core_Entity::factory('Shop_Producer');
		$oShop_Producers->queryBuilder()
			->where('shop_id', '=', $iShopId)
			->where('shop_producer_dir_id', '=', $iShopProducerDirParentId);

		$aShop_Producers = $oShop_Producers->findAll(FALSE);
		foreach ($aShop_Producers as $oShop_Producer)
		{
			$aReturn[$oShop_Producer->id] = str_repeat('  ', $iLevel) . $oShop_Producer->name;
		}

		$iLevel == 0 && self::$_aProducerDir = array();

		return $aReturn;
	}

	/**
	 * Fill taxes list
	 * @return array
	 */
	public function fillTaxesList()
	{
		$oTax = Core_Entity::factory('Shop_Tax');

		$oTax
			->queryBuilder()
			->orderBy('id');

		$aTaxes = $oTax->findAll();

		$aReturn = array(' … ');

		foreach ($aTaxes as $oTax)
		{
			$aReturn[$oTax->id] = $oTax->name;
		}

		return $aReturn;
	}

	/**
	 * Fill sellers list
	 * @return array
	 */
	protected function _fillSellersList()
	{
		$oShopSeller = Core_Entity::factory('Shop_Seller');

		$iShopId = intval(Core_Array::getGet('shop_id', 0));

		!$iShopId && $iShopId = Core_Entity::factory('Shop_Item', intval(Core_Array::getGet('shop_item_id', 0)))->Shop->id;

		$oShopSeller->queryBuilder()
			->where("shop_id", "=", $iShopId);

		$aReturn = array(" … ");

		$aShopSellers = $oShopSeller->findAll();
		foreach ($aShopSellers as $oShopSeller)
		{
			$aReturn[$oShopSeller->id] = $oShopSeller->name;
		}

		return $aReturn;
	}

	/**
	 * Fill shortcut groups list
	 * @param Shop_Item_Model $oShop_Item item
	 * @return array
	 */
	protected function _fillShortcutGroupList($oShop_Item)
	{
		$aReturnArray = array();

		$oShop = $oShop_Item->Shop;

		$aShortcuts = $oShop->Shop_Items->getAllByShortcut_id($oShop_Item->id, FALSE);
		foreach ($aShortcuts as $oShortcut)
		{
			$oShop_Group = $oShortcut->Shop_Group;

			$aParentGroups = array();

			$aTmpGroup = $oShop_Group;

			// Добавляем все директории от текущей до родителя.
			do {
				$aParentGroups[] = $aTmpGroup->name;
			} while ($aTmpGroup = $aTmpGroup->getParent());

			$sParents = implode(' → ', array_reverse($aParentGroups));

			if (!is_null($oShop_Group->id))
			{
				$aReturnArray[$oShop_Group->id] = array(
					'value' => $sParents . ' [' . $oShop_Group->id . ']',
					'attr' => array('selected' => 'selected')
				);
			}
			else
			{
				$aReturnArray[0] = array(
					'value' => Core::_('Shop_Item.root') . ' [0]',
					'attr' => array('selected' => 'selected')
				);
			}
		}

		return $aReturnArray;
	}

	/**
	 * Fill tags list
	 * @param Shop_Item_Model $oShop_Item item
	 * @return array
	 */
	protected function _fillTagsList($oShop_Item)
	{
		$aReturnArray = array();

		$aTags = $oShop_Item->Tags->findAll(FALSE);

		foreach ($aTags as $oTag)
		{
			$aReturnArray[$oTag->name] = array(
				'value' => $oTag->name,
				'attr' => array('selected' => 'selected')
			);
		}

		return $aReturnArray;
	}

	/**
	 * Fill barcodes list
	 * @param Shop_Item_Model $oShop_Item item
	 * @return array
	 */
	protected function _fillBarcodesList($oShop_Item)
	{
		$aReturnArray = array();

		$aShop_Item_Barcodes = $oShop_Item->Shop_Item_Barcodes->findAll(FALSE);

		foreach ($aShop_Item_Barcodes as $oShop_Item_Barcode)
		{
			$aReturnArray[$oShop_Item_Barcode->value] = array(
				'value' => $oShop_Item_Barcode->value,
				'attr' => array('selected' => 'selected')
			);
		}

		return $aReturnArray;
	}

	/**
	 * Fill modifications list
	 * @param Shop_Item_Model $oShop_Item item
	 * @return array
	 */
	static public function fillModificationList($oShop_Item, $like = NULL)
	{
		$aReturnArray = array(' … ');

		$iShopGroupId = $oShop_Item->modification_id
			? $oShop_Item->Modification->shop_group_id
			: $oShop_Item->shop_group_id;

		$oQB = Core_QueryBuilder::select('id', 'name')
			->from('shop_items')
			->where('shop_id', '=', $oShop_Item->shop_id)
			->where('shop_group_id', '=', $iShopGroupId)
			// Self exclusion
			->where('id', '!=', $oShop_Item->id)
			->where('modification_id', '=', 0)
			->where('shortcut_id', '=', 0)
			->where('deleted', '=', 0)
			->clearOrderBy()
			->orderBy('sorting')
			->orderBy('name');

		strlen($like)
			&& $oQB->where('shop_items.name', 'LIKE', '%' . $like . '%')->limit(10);

		$aTmp = $oQB->execute()->asAssoc()->result();

		foreach ($aTmp as $aItem)
		{
			$aReturnArray[$aItem['id']] = $aItem['name'];
		}

		return $aReturnArray;
	}

	/**
	 * Shop groups tree
	 * @var array
	 */
	static protected $_aGroupTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iShopId shop ID
	 * @param int $iShopGroupParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillShopGroup($iShopId, $iShopGroupParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShopId = intval($iShopId);
		$iShopGroupParentId = intval($iShopGroupParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_groups')
				->where('shop_id', '=', $iShopId)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				self::$_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset(self::$_aGroupTree[$iShopGroupParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iShopGroupParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillShopGroup($iShopId, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}

	public function imgBox($addFunction = '$.cloneSpecialPrice', $deleteOnclick = '$.deleteNewSpecialprice(this)')
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		ob_start();
			Admin_Form_Entity::factory('Div')
				->class('no-padding add-remove-property margin-top-23 pull-left')
				->add(
					Admin_Form_Entity::factory('Div')
						->class('btn btn-palegreen')
						->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-plus-circle close"></i>'))
						->onclick("{$addFunction}('{$windowId}', this);")
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('btn btn-darkorange btn-delete')
						->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-minus-circle close"></i>'))
						->onclick($deleteOnclick)
				)
				->execute();

		return Admin_Form_Entity::factory('Code')->html(ob_get_clean());
	}
}