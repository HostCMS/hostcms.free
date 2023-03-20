<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Controller_Change_Attribute
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Controller_Change_Attribute extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title',
		'modifications',
		'Shop',
		'buttonName',
		'skipColumns'
	);

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->buttonName(Core::_('Admin_Form.apply'));

		$this->modifications = FALSE;
	}

	/**
	 * Shop_Price_Setting object.
	 * @var object
	 */
	protected $_oShop_Price_Setting = NULL;

	/**
	 * Get $oShop_Price_Setting object
	 * @param mixed $shopId shop id
	 * @return object|NULL
	 */
	protected function _getShopPriceSetting($shopId)
	{
		if (is_null($this->_oShop_Price_Setting))
		{
			$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting');
			$oShop_Price_Setting->shop_id = $shopId;
			$oShop_Price_Setting->number = '';
			$oShop_Price_Setting->posted = 0;
			$oShop_Price_Setting->description = Core::_('Shop_Price_Setting.apply_item_change_attributes');
			$oShop_Price_Setting->datetime = Core_Date::timestamp2sql(time());
			$oShop_Price_Setting->save();

			$oShop_Price_Setting->number = $oShop_Price_Setting->id;
			$oShop_Price_Setting->save();

			$this->_oShop_Price_Setting = $oShop_Price_Setting;
		}

		return $this->_oShop_Price_Setting;
	}

	/**
	 * Shop_Warehouse_Inventory object.
	 * @var object
	 */
	protected $_aShop_Warehouse_Inventory = array();

	/**
	 * Get $Shop_Warehouse_Inventory object
	 * @param mixed $shop_warehouse_id shop warehouse id
	 * @return object|NULL
	 */
	protected function _getShopWarehouseInventory($shop_warehouse_id)
	{
		if (!isset($this->_aShop_Warehouse_Inventory[$shop_warehouse_id]))
		{
			$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory');
			$oShop_Warehouse_Inventory->shop_warehouse_id = $shop_warehouse_id;
			$oShop_Warehouse_Inventory->number = '';
			$oShop_Warehouse_Inventory->posted = 0;
			$oShop_Warehouse_Inventory->description = Core::_('Shop_Warehouse_Inventory.apply_item_change_attributes');
			$oShop_Warehouse_Inventory->datetime = Core_Date::timestamp2sql(time());
			$oShop_Warehouse_Inventory->save();

			$oShop_Warehouse_Inventory->number = $oShop_Warehouse_Inventory->id;
			$oShop_Warehouse_Inventory->save();

			$this->_aShop_Warehouse_Inventory[$shop_warehouse_id] = $oShop_Warehouse_Inventory;
		}

		return $this->_aShop_Warehouse_Inventory[$shop_warehouse_id];
	}

	protected $_itemsCount = 0;

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 * @hostcms-event Shop_Item_Controller_Change_Attribute.onBeforeExecute
	 * @hostcms-event Shop_Item_Controller_Change_Attribute.onBeforeAddButton
	 * @hostcms-event Shop_Item_Controller_Change_Attribute.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($operation));

		if (is_null($operation))
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$newWindowId = 'Change_Attribute_' . time();

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;

			$oCore_Html_Entity_Form = Core_Html_Entity::factory('Form');

			$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
				->id($newWindowId)
				->class('tabbable')
				->add(
					$oCore_Html_Entity_Form
					->add(
						Admin_Form_Entity::factory('Code')
							->html('<ul id="changeAttributesTabModal" class="nav nav-tabs">
										<li class="active">
											<a href="#itemTab" data-toggle="tab" aria-expanded="true">' . Core::_('Shop_Item.attribute_item_tab') . '</a>
										</li>
										<li class="tab-red">
											<a href="#groupTab" data-toggle="tab" aria-expanded="false">' . Core::_('Shop_Item.attribute_group_tab') . '</a>
										</li>
									</ul>')
					)
					->add(
						Admin_Form_Entity::factory('Div')
							->class('tab-content')
							->add(
								$oItemTab = Admin_Form_Entity::factory('Div')
									->id('itemTab')
									->class('tabbable tab-pane active')
							)
							->add(
								$oGroupTab = Admin_Form_Entity::factory('Div')
									->id('groupTab')
									->class('tab-pane')
							)
					)
			);

			$oItemTab
				->add(
					Admin_Form_Entity::factory('Code')
						->html('<ul id="changeAttributesItemTabsModal" class="nav nav-tabs">
									<li class="active">
										<a href="#itemMainTab" data-toggle="tab" aria-expanded="true">' . Core::_('Shop_Item.attribute_item_main_tab') . '</a>
									</li>
									<li class="tab-red">
										<a href="#itemPriceTab" data-toggle="tab" aria-expanded="false">' . Core::_('Shop_Item.attribute_item_price_tab') . '</a>
									</li>
									<li class="tab-yellow">
										<a href="#itemWarehouseTab" data-toggle="tab" aria-expanded="false">' . Core::_('Shop_Item.attribute_item_warehouse_tab') . '</a>
									</li>
								</ul>')
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('tab-content')
						->add(
							$oItemMainTab = Admin_Form_Entity::factory('Div')
								->id('itemMainTab')
								->class('tab-pane active')
						)
						->add(
							$oItemPriceTab = Admin_Form_Entity::factory('Div')
								->id('itemPriceTab')
								->class('tab-pane')
						)
						->add(
							$oItemWarehouseTab = Admin_Form_Entity::factory('Div')
								->id('itemWarehouseTab')
								->class('tab-pane')
						)
				);

			$oCore_Html_Entity_Form
				->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));

			// Select на всплывающем окне должен быть найден через ID нового окна, а не id_content
			$window_Admin_Form_Controller->window($newWindowId);

			$aCurrencies = array(' … ');
			$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll(FALSE);
			foreach ($aShop_Currencies as $oShop_Currency)
			{
				$aCurrencies[$oShop_Currency->id] = $oShop_Currency->sign;
			}

			$oAdmin_Form_Entity_Select_Currencies = Admin_Form_Entity::factory('Select')
				->name('shop_currency_id')
				->id('shopCurrencyId')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options($aCurrencies)
				->caption(Core::_('Shop_Item.shop_currency_select_caption'))
				->controller($window_Admin_Form_Controller);

			$aMeasures = array(' … ');
			$oShop_Measures = Core_Entity::factory('Shop_Measure');
			$oShop_Measures->queryBuilder()
				->orderBy('name');

			$aShop_Measures = $oShop_Measures->findAll(FALSE);
			foreach ($aShop_Measures as $oShop_Measure)
			{
				$aMeasures[$oShop_Measure->id] = $oShop_Measure->name;
			}

			$oAdmin_Form_Entity_Select_Measures = Admin_Form_Entity::factory('Select')
				->name('shop_measure_id')
				->id('shopMeasureId')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options($aMeasures)
				->caption(Core::_('Shop_Item.shop_measure_id'))
				->controller($window_Admin_Form_Controller);

			$aTaxes = array(' … ');
			$aShop_Taxes = Core_Entity::factory('Shop_Tax')->findAll(FALSE);
			foreach ($aShop_Taxes as $oShop_Tax)
			{
				$aTaxes[$oShop_Tax->id] = $oShop_Tax->name;
			}

			$oAdmin_Form_Entity_Select_Tax = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('shop_tax_id')
				->id('shopTaxId')
				->options($aTaxes)
				->caption(Core::_('Shop_Item.shop_tax_id'))
				->controller($window_Admin_Form_Controller);

			$aProducers = array(' … ');
			$aShop_Producers = $this->Shop->Shop_Producers->findAll(FALSE);
			foreach ($aShop_Producers as $oShop_Producer)
			{
				$aProducers[$oShop_Producer->id] = $oShop_Producer->name;
			}

			$oAdmin_Form_Entity_Select_Producers = Admin_Form_Entity::factory('Select')
				->name('shop_producer_id')
				->id('shopProducerId')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options($aProducers)
				->caption(Core::_('Shop_Item.shop_producer_id'))
				->controller($window_Admin_Form_Controller);

			$aSellers = array(' … ');
			$aShop_Sellers = $this->Shop->Shop_Sellers->findAll(FALSE);
			foreach ($aShop_Sellers as $oShop_Seller)
			{
				$aSellers[$oShop_Seller->id] = $oShop_Seller->name;
			}

			$oAdmin_Form_Entity_Select_Sellers = Admin_Form_Entity::factory('Select')
				->name('shop_seller_id')
				->id('shopSellerId')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options($aSellers)
				->caption(Core::_('Shop_Item.shop_seller_id'))
				->controller($window_Admin_Form_Controller);

			if (Core::moduleIsActive('siteuser'))
			{
				$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
				$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups(CURRENT_SITE);
			}
			else
			{
				$aSiteuser_Groups = array();
			}

			$oAdmin_Form_Entity_Select_Siteuser_Groups = Admin_Form_Entity::factory('Select')
				->name('siteuser_group_id')
				->id('siteuserGroupId')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options(array(Core::_('Shop.allgroupsaccess')) + $aSiteuser_Groups)
				->caption(Core::_('Shop_Item.siteuser_group_id'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Active = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('active')
				->caption(Core::_('Shop_Item.active'))
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Shop_Item.remove'),
						1 => Core::_('Shop_Item.set')
					)
				)
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Indexing = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('indexing')
				->caption(Core::_('Shop_Item.indexing'))
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Shop_Item.remove'),
						1 => Core::_('Shop_Item.set')
					)
				)
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Yandex = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('yandex_market')
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Shop_Item.remove'),
						1 => Core::_('Shop_Item.set')
					)
				)
				->caption(Core::_('Shop_Item.yandex_market'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Input_Weight = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('weight')
				->caption(Core::_('Shop_Item.weight'))
				->controller($window_Admin_Form_Controller)
				->add(
					Core_Html_Entity::factory('Span')
						->class('input-group-addon dimension_patch')
						->value($oShop->shop_measure_id ? htmlspecialchars((string) $oShop->Shop_Measure->name) : '')
				);

			$oAdmin_Form_Entity_Select_Order_Discount = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('apply_purchase_discount')
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Shop_Item.remove'),
						1 => Core::_('Shop_Item.set')
					)
				)
				->caption(Core::_('Shop_Item.apply_purchase_discount'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Input_Datetime = Admin_Form_Entity::factory('Datetime')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('datetime')
				->caption(Core::_('Shop_Item.datetime'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Input_Min_Quantity = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('min_quantity')
				->caption(Core::_('Shop_Item.min_quantity'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Input_Max_Quantity = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('max_quantity')
				->caption(Core::_('Shop_Item.max_quantity'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Input_Quantity_Step = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('quantity_step')
				->caption(Core::_('Shop_Item.quantity_step'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Input_Length = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4 no-padding-right'))
				->name('length')
				->caption(Core::_('Shop_Item.item_length'))
				->controller($window_Admin_Form_Controller)
				->add(
					Core_Html_Entity::factory('Span')
						->class('input-group-addon dimension_patch')
						->value('×')
				);

			$oAdmin_Form_Entity_Input_Width = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4 no-padding'))
				->name('width')
				->caption(Core::_('Shop_Item.item_width'))
				->controller($window_Admin_Form_Controller)
				->add(
					Core_Html_Entity::factory('Span')
						->class('input-group-addon dimension_patch')
						->value('×')
				);

			$oAdmin_Form_Entity_Input_Height = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-lg-2 col-md-2 col-sm-2 col-xs-4 no-padding'))
				->name('height')
				->caption(Core::_('Shop_Item.item_height'))
				->controller($window_Admin_Form_Controller)
				->add(
					Core_Html_Entity::factory('Span')
						->class('input-group-addon dimension_patch')
						->value(Core::_('Shop.size_measure_' . $oShop->size_measure))
				);

			$oItemMainTab
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						// ->add($oAdmin_Form_Entity_Select_Currencies)
						->add($oAdmin_Form_Entity_Select_Measures)
						// ->add($oAdmin_Form_Entity_Select_Tax)
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Select_Producers)
						->add($oAdmin_Form_Entity_Select_Sellers)
						->add($oAdmin_Form_Entity_Select_Siteuser_Groups)
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Select_Active)
						->add($oAdmin_Form_Entity_Select_Indexing)
						->add($oAdmin_Form_Entity_Select_Yandex)
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Input_Weight)
						->add($oAdmin_Form_Entity_Select_Order_Discount)
						->add($oAdmin_Form_Entity_Input_Datetime)
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Input_Length)
						->add($oAdmin_Form_Entity_Input_Width)
						->add($oAdmin_Form_Entity_Input_Height)
				);

			if (Core::moduleIsActive('tag'))
			{
				$oAdmin_Form_Entity_Select_Tags = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Item.items_catalog_tags'))
					->options(array())
					->name('tags[]')
					->class('shop-item-tags')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$tagsHtml = '
					<script>
						$(function(){
							$("#' . $newWindowId . ' .shop-item-tags").select2({
								language: "' . Core_I18n::instance()->getLng() . '",
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

				$oItemMainTab
					->add(
						Admin_Form_Entity::factory('Div')
							->class('row')
							->add($oAdmin_Form_Entity_Select_Tags)
							->add(Admin_Form_Entity::factory('Code')->html($tagsHtml))
					);
			}

			$oItemMainTab
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Input_Min_Quantity)
						->add($oAdmin_Form_Entity_Input_Max_Quantity)
						->add($oAdmin_Form_Entity_Input_Quantity_Step)
				);

			if (!$this->modifications)
			{
				$oAdmin_Form_Entity_Modification_Checkbox = Admin_Form_Entity::factory('Checkbox')
					->name("include_modifications")
					->class('form-control')
					->caption(Core::_('Shop_Item.include_modifications'))
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oItemMainTab
					->add(
						Admin_Form_Entity::factory('Div')
							->class('row')
							->add($oAdmin_Form_Entity_Modification_Checkbox)
					);
			}

			$oItemPriceTab->add($oPricesRow1 = Admin_Form_Entity::factory('Div')->class('row'));

			$oAdmin_Form_Entity_Input_Main_Price = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('main_price')
				->caption(Core::_('Shop_Item.price'))
				->controller($window_Admin_Form_Controller);

			$oPricesRow1
				->add($oAdmin_Form_Entity_Input_Main_Price)
				->add($oAdmin_Form_Entity_Select_Currencies)
				->add($oAdmin_Form_Entity_Select_Tax);

			if (Core::moduleIsActive('siteuser') || defined('BACKEND_SHOP_PRICES'))
			{
				$oItemPriceTab->add($oPricesRowN = Admin_Form_Entity::factory('Div')->class('row'));

				$oShop_Prices = $oShop->Shop_Prices;
				$oShop_Prices->queryBuilder()
					->clearOrderBy()
					->orderBy('shop_prices.sorting', 'ASC');

				$aShop_Prices = $oShop_Prices->findAll(FALSE);

				foreach ($aShop_Prices as $oShop_Price)
				{
					$oItemPriceDiv = Admin_Form_Entity::factory('Span')
						->value(htmlspecialchars((string) $oShop_Price->name))
						->id("item_price_id_{$oShop_Price->id}")
						->divAttr(array('class' => 'form-group margin-top-10 col-xs-8 col-md-4'))
						->controller($window_Admin_Form_Controller);

					$oItemPriceTextBox = Admin_Form_Entity::factory('Input')
						->id("item_price_value_{$oShop_Price->id}")
						->name("item_price_value_{$oShop_Price->id}")
						->divAttr(array('class' => 'form-group col-xs-4 col-md-2'))
						->controller($window_Admin_Form_Controller);

					$oPricesRowN
						->add($oItemPriceDiv)
						->add($oItemPriceTextBox);
				}
			}

			$aWarehouses = $oShop->Shop_Warehouses->findAll(FALSE);

			if (count($aWarehouses))
			{
				$oItemWarehouseTab->add($oWarehousesRowN = Admin_Form_Entity::factory('Div')->class('row'));

				foreach ($aWarehouses as $oWarehouse)
				{
					$oItemWarehouseDiv = Admin_Form_Entity::factory('Span')
						->value(htmlspecialchars((string) $oWarehouse->name))
						->id("item_warehouse_id_{$oWarehouse->id}")
						->divAttr(array('class' => 'form-group margin-top-10 col-xs-8 col-md-4'))
						->controller($window_Admin_Form_Controller);

					$oItemWarehouseTextBox = Admin_Form_Entity::factory('Input')
						->id("item_warehouse_value_{$oWarehouse->id}")
						->name("item_warehouse_value_{$oWarehouse->id}")
						->divAttr(array('class' => 'form-group col-xs-4 col-md-2'))
						->controller($window_Admin_Form_Controller);

					$oWarehousesRowN
						->add($oItemWarehouseDiv)
						->add($oItemWarehouseTextBox);
				}
			}

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			foreach ($aChecked as $datasetKey => $checkedItems)
			{
				$oAdmin_Form_Dataset_Entity = $this->_Admin_Form_Controller->getDataset($datasetKey);

				if ($oAdmin_Form_Dataset_Entity /*&& get_class($oAdmin_Form_Dataset_Entity->getEntity()) == 'Shop_Item_Model'*/)
				{
					foreach ($checkedItems as $key => $value)
					{
						$oCore_Html_Entity_Form->add(
							 Core_Html_Entity::factory('Input')
								->name('hostcms[checked][' . $datasetKey . '][' . $key . ']')
								->value(1)
								->type('hidden')
						);
					}
				}
			}

			// Группы
			$oAdmin_Form_Entity_Select_Active_Group = Admin_Form_Entity::factory('Select')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->name('group_active')
					->caption(Core::_('Shop_Group.active'))
					->options(
						array(
							'' => ' … ',
							0 => Core::_('Shop_Item.remove'),
							1 => Core::_('Shop_Item.set')
						)
					)
					->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Indexing_Group = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('group_indexing')
				->caption(Core::_('Shop_Group.indexing'))
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Shop_Item.remove'),
						1 => Core::_('Shop_Item.set')
					)
				)
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Group_Siteuser_Groups = Admin_Form_Entity::factory('Select')
				->name('group_siteuser_group_id')
				->id('siteuserGroupId')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options(array(Core::_('Shop.allgroupsaccess')) + $aSiteuser_Groups)
				->caption(Core::_('Shop_Item.siteuser_group_id'))
				->controller($window_Admin_Form_Controller);

			$oGroupTab
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Select_Active_Group)
						->add($oAdmin_Form_Entity_Select_Indexing_Group)
						->add($oAdmin_Form_Entity_Select_Group_Siteuser_Groups)
				);

			Core_Event::notify(get_class($this) . '.onBeforeAddButton', $this, array($oCore_Html_Entity_Form, $oCore_Html_Entity_Div));

			$oAdmin_Form_Entity_Button = Admin_Form_Entity::factory('Button')
				->name('apply')
				->type('submit')
				->class('applyButton btn btn-blue')
				->value($this->buttonName)
				->onclick(
					//'$("#' . $newWindowId . '").parents(".modal").remove(); '
					'bootbox.hideAll(); '
					. $this->_Admin_Form_Controller->getAdminSendForm(array('operation' => 'apply'))
				)
				->controller($this->_Admin_Form_Controller);

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 margin-top-10')
								->add($oAdmin_Form_Entity_Button)
						)
				);

			$oCore_Html_Entity_Div->execute();

			ob_start();

			Core_Html_Entity::factory('Script')
				->value("$(function() {
					$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . Core_Str::escapeJavascriptVariable($this->title) . "', AppendTo: '#{$windowId}', width: 800, height: 720, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		else
		{
			switch (get_class($this->_object))
			{
				case 'Shop_Item_Model':
					$this->_applyItem($this->_object);

					if (!is_null(Core_Array::getPost('include_modifications')))
					{
						$aModifications = $this->_object->Modifications->findAll(FALSE);
						foreach ($aModifications as $oModification)
						{
							$this->_applyItem($oModification);
						}
					}
				break;
				case 'Shop_Group_Model':
					$this->_applyGroup($this->_object);
				break;
			}

			$this->_itemsCount++;

			$aChecked = $this->_Admin_Form_Controller->getChecked();

			if ($this->_itemsCount == count($aChecked[$this->_datasetId]))
			{
				// Проводки, если есть
				!is_null($this->_oShop_Price_Setting) && $this->_oShop_Price_Setting->post();
				foreach ($this->_aShop_Warehouse_Inventory as $oShop_Warehouse_Inventory)
				{
					$oShop_Warehouse_Inventory->post();
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($operation));

		return $this;
	}

	/**
	 * Apply attrubites for items in group
	 * @param Shop_Group_Model $oShop_Group
	 * @return self
	 * @hostcms-event Shop_Item_Controller_Change_Attribute.onBeforeApplyGroup
	 * @hostcms-event Shop_Item_Controller_Change_Attribute.onAfterApplyGroup
	 */
	protected function _applyGroup(Shop_Group_Model $oShop_Group)
	{
		Core_Event::notify(get_class($this) . '.onBeforeApplyGroup', $this, array($oShop_Group));

		$this->_applyGroupAttributes($oShop_Group);

		$aShop_Items = $oShop_Group->Shop_Items->findAll(FALSE);
		foreach ($aShop_Items as $oShop_Item)
		{
			$this->_applyItem($oShop_Item);

			if (!is_null(Core_Array::getPost('include_modifications')))
			{
				$aModifications = $oShop_Item->Modifications->findAll(FALSE);
				foreach ($aModifications as $oModification)
				{
					$this->_applyItem($oModification);
				}
			}
		}

		$aShop_Groups = $oShop_Group->Shop_Groups->findAll(FALSE);
		foreach ($aShop_Groups as $oTmp_Shop_Group)
		{
			$this->_applyGroup($oTmp_Shop_Group);
		}

		Core_Event::notify(get_class($this) . '.onAfterApplyGroup', $this, array($oShop_Group));

		return $this;
	}

	/**
	 * Apply attrubites for group
	 * @param Shop_Group_Model $oShop_Group
	 * @return self
	 */
	protected function _applyGroupAttributes(Shop_Group_Model $oShop_Group)
	{
		Core_Array::getPost('group_active') !== '' && $oShop_Group->active = Core_Array::getPost('group_active', 0, 'int');
		Core_Array::getPost('group_indexing') !== '' && $oShop_Group->indexing = Core_Array::getPost('group_indexing', 0, 'int');
		Core_Array::getPost('group_siteuser_group_id') && $oShop_Group->siteuser_group_id = Core_Array::getPost('group_siteuser_group_id', 0, 'int');

		$oShop_Group->save();

		$oShop_Group->clearCache();

		// Fast filter
		if ($oShop_Group->Shop->filter)
		{
			$oShop_Filter_Group_Controller = $this->_getShop_Filter_Group_Controller($oShop_Group->Shop);
			$oShop_Filter_Group_Controller->fill($oShop_Group->id);
		}

		return $this;
	}

	/**
	 * Apply attrubites for item
	 * @param Shop_Item_Model $oShop_Item
	 * @return self
	 * @hostcms-event Shop_Item_Controller_Change_Attribute.onBeforeApplyItem
	 * @hostcms-event Shop_Item_Controller_Change_Attribute.onAfterApplyItem
	 */
	protected function _applyItem(Shop_Item_Model $oShop_Item)
	{
		Core_Event::notify(get_class($this) . '.onBeforeApplyItem', $this, array($oShop_Item));

		Core_Array::getPost('shop_currency_id') && $oShop_Item->shop_currency_id = intval(Core_Array::getPost('shop_currency_id'));
		Core_Array::getPost('shop_producer_id') && $oShop_Item->shop_producer_id = intval(Core_Array::getPost('shop_producer_id'));
		Core_Array::getPost('shop_seller_id') && $oShop_Item->shop_seller_id = intval(Core_Array::getPost('shop_seller_id'));
		Core_Array::getPost('shop_tax_id') && $oShop_Item->shop_tax_id = intval(Core_Array::getPost('shop_tax_id'));
		Core_Array::getPost('shop_measure_id') && $oShop_Item->shop_measure_id = intval(Core_Array::getPost('shop_measure_id'));
		Core_Array::getPost('siteuser_group_id') && $oShop_Item->siteuser_group_id = intval(Core_Array::getPost('siteuser_group_id'));

		Core_Array::getPost('active') !== '' && $oShop_Item->active = intval(Core_Array::getPost('active'));
		Core_Array::getPost('indexing') !== '' && $oShop_Item->indexing = intval(Core_Array::getPost('indexing'));
		Core_Array::getPost('yandex_market') !== '' && $oShop_Item->yandex_market = intval(Core_Array::getPost('yandex_market'));

		Core_Array::getPost('weight') !== '' && $oShop_Item->weight = floatval(Core_Array::getPost('weight'));
		Core_Array::getPost('apply_purchase_discount') !== '' && $oShop_Item->apply_purchase_discount = intval(Core_Array::getPost('apply_purchase_discount'));
		Core_Array::getPost('datetime') !== '' && $oShop_Item->datetime = strval(Core_Date::datetime2sql(Core_Array::getPost('datetime')));

		Core_Array::getPost('length') !== '' && $oShop_Item->length = floatval(Core_Array::getPost('length'));
		Core_Array::getPost('width') !== '' && $oShop_Item->width = floatval(Core_Array::getPost('width'));
		Core_Array::getPost('height') !== '' && $oShop_Item->height = floatval(Core_Array::getPost('height'));

		Core_Array::getPost('min_quantity') !== '' && $oShop_Item->min_quantity = floatval(Core_Array::getPost('min_quantity'));
		Core_Array::getPost('max_quantity') !== '' && $oShop_Item->max_quantity = floatval(Core_Array::getPost('max_quantity'));
		Core_Array::getPost('quantity_step') !== '' && $oShop_Item->quantity_step = floatval(Core_Array::getPost('quantity_step'));

		$oShop_Item->save();

		$main_price = Core_Array::getPost('main_price');

		if ($main_price !== '' && $oShop_Item->price != $main_price)
		{
			$oShop_Price_Setting = $this->_getShopPriceSetting($oShop_Item->shop_id);

			$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
			$oShop_Price_Setting_Item->shop_price_setting_id = $oShop_Price_Setting->id;
			$oShop_Price_Setting_Item->shop_price_id = 0; // Розничная
			$oShop_Price_Setting_Item->shop_item_id = $oShop_Item->id;
			$oShop_Price_Setting_Item->old_price = $oShop_Item->price;
			$oShop_Price_Setting_Item->new_price = floatval($main_price);
			$oShop_Price_Setting_Item->save();
		}

		if (Core::moduleIsActive('siteuser') || defined('BACKEND_SHOP_PRICES'))
		{
			$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

			$aShop_Prices = $oShop_Item->Shop->Shop_Prices->findAll();
			foreach ($aShop_Prices as $oShop_Price)
			{
				$item_price = Core_Array::getPost('item_price_value_' . $oShop_Price->id);

				$old_price = $Shop_Price_Entry_Controller->getPrice($oShop_Price->id, $oShop_Item->id, Core_Date::timestamp2sql(time()));

				if ($item_price !== '' && $old_price != $item_price)
				{
					$oShop_Price_Setting = $this->_getShopPriceSetting($oShop_Item->shop_id);

					is_null($old_price)
						&& $old_price = $oShop_Item->price;

					$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
					$oShop_Price_Setting_Item
						->shop_price_setting_id($oShop_Price_Setting->id)
						->shop_price_id($oShop_Price->id)
						->shop_item_id($oShop_Item->id)
						->old_price($old_price)
						->new_price(floatval($item_price))
						->save();
				}
			}
		}

		$aShop_Warehouses = $oShop_Item->Shop->Shop_Warehouses->findAll();
		foreach ($aShop_Warehouses as $oShop_Warehouse)
		{
			$warehouse_value = Core_Array::getPost('item_warehouse_value_' . $oShop_Warehouse->id);

			if ($warehouse_value !== '')
			{
				$fRest = $oShop_Warehouse->getRest($oShop_Item->id);

				if ($fRest != $warehouse_value)
				{
					$oShop_Warehouse_Inventory = $this->_getShopWarehouseInventory($oShop_Warehouse->id);

					$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
					$oShop_Warehouse_Inventory_Item->shop_warehouse_inventory_id = $oShop_Warehouse_Inventory->id;
					$oShop_Warehouse_Inventory_Item->shop_item_id = $oShop_Item->id;
					$oShop_Warehouse_Inventory_Item->count = floatval($warehouse_value);
					$oShop_Warehouse_Inventory_Item->save();
				}
			}
		}

		if (Core::moduleIsActive('tag'))
		{
			$aRecievedTags = Core_Array::getPost('tags', array());
			!is_array($aRecievedTags) && $aRecievedTags = array();

			$aTmp = array();

			$aTags = $oShop_Item->Tags->findAll(FALSE);
			foreach ($aTags as $oTag)
			{
				$aTmp[] = $oTag->name;
			}

			foreach ($aRecievedTags as $tag_name)
			{
				$tag_name = trim($tag_name);

				if ($tag_name != '' && !in_array($tag_name, $aTmp))
				{
					$oTag = Core_Entity::factory('Tag')->getByName($tag_name, FALSE);

					if (is_null($oTag))
					{
						$oTag = Core_Entity::factory('Tag');
						$oTag->name = $oTag->path = $tag_name;
						$oTag->save();
					}

					$oShop_Item->add($oTag);
				}
			}
		}

		$oShop_Item->clearCache();

		// Fast filter
		if ($oShop_Item->Shop->filter)
		{
			$oShop_Filter_Controller = $this->_getShop_Filter_Controller($oShop_Item->Shop);
			$oShop_Filter_Controller->fill($oShop_Item);

			// Fast filter for modifications
			$aModifications = $oShop_Item->Modifications->findAll(FALSE);
			foreach ($aModifications as $oModification)
			{
				$oShop_Item->active
					? $oShop_Filter_Controller->fill($oModification)
					: $oShop_Filter_Controller->remove($oModification);
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterApplyItem', $this, array($oShop_Item));

		return $this;
	}

	/**
	 * Shop filter controller object
	 * @var mixed
	 */
	protected $_Shop_Filter_Controller = NULL;

	/**
	 * Get shop filter controller
	 * @param Shop_Model $oShop
	 * @return Shop_Filter_Controller|NULL
	 */
	protected function _getShop_Filter_Controller(Shop_Model $oShop)
	{
		if (is_null($this->_Shop_Filter_Controller))
		{
			$this->_Shop_Filter_Controller = new Shop_Filter_Controller($oShop);
		}

		return $this->_Shop_Filter_Controller;
	}

	/**
	 * Shop filter controller object
	 * @var mixed
	 */
	protected $_Shop_Filter_Group_Controller = NULL;

	/**
	 * Get shop filter group controller
	 * @param Shop_Model $oShop
	 * @return Shop_Filter_Group_Controller|NULL
	 */
	protected function _getShop_Filter_Group_Controller(Shop_Model $oShop)
	{
		if (is_null($this->_Shop_Filter_Group_Controller))
		{
			$this->_Shop_Filter_Group_Controller = new Shop_Filter_Group_Controller($oShop);
		}

		return $this->_Shop_Filter_Group_Controller;
	}
}