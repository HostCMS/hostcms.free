<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Controller_Change_Attribute
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Controller_Change_Attribute extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title',
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
	}

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

			$oCore_Html_Entity_Form = Core::factory('Core_Html_Entity_Form');

			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form);

			$oCore_Html_Entity_Form
				->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;

			// Select на всплывающем окне должен быть найден через ID нового окна, а не id_content
			$window_Admin_Form_Controller->window($newWindowId);

			$aCurrencies = array(' … ');
			$oShop_Currencies = Core_Entity::factory('Shop_Currency');
			$oShop_Currencies->queryBuilder()
				->orderBy('sorting')
				->orderBy('name');

			$aShop_Currencies = $oShop_Currencies->findAll(FALSE);
			foreach ($aShop_Currencies as $oShop_Currency)
			{
				$aCurrencies[$oShop_Currency->id] = $oShop_Currency->name;
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

			$oAdmin_Form_Entity_Select_Order_Discount = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
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

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Select_Currencies)
						->add($oAdmin_Form_Entity_Select_Measures)
						->add($oAdmin_Form_Entity_Select_Tax)
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
						->add($oAdmin_Form_Entity_Select_Order_Discount)
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
							$("#' . $windowId . ' .shop-item-tags").select2({
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

				$oCore_Html_Entity_Form
					->add(
						Admin_Form_Entity::factory('Div')
							->class('row')
							->add($oAdmin_Form_Entity_Select_Tags)
							->add(Admin_Form_Entity::factory('Code')->html($tagsHtml))
					);
			}

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Input_Min_Quantity)
						->add($oAdmin_Form_Entity_Input_Max_Quantity)
						->add($oAdmin_Form_Entity_Input_Quantity_Step)
				);

			$oAdmin_Form_Entity_Modification_Checkbox = Admin_Form_Entity::factory('Checkbox')
				->name("include_modifications")
				->class('form-control')
				->caption(Core::_('Shop_Item.include_modifications'))
				->divAttr(array('class' => 'form-group col-xs-12'));

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Modification_Checkbox)
				);

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
							 Core::factory('Core_Html_Entity_Input')
								->name('hostcms[checked][' . $datasetKey . '][' . $key . ']')
								->value(1)
								->type('hidden')
						);
					}
				}
			}

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
								->class('form-group col-xs-12')
								->add($oAdmin_Form_Entity_Button)
						)
				);

			$oCore_Html_Entity_Div->execute();

			ob_start();

			Core::factory('Core_Html_Entity_Script')
				->value("$(function() {
					$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . $this->title . "', AppendTo: '#{$windowId}', width: 750, height: 520, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
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
		Core_Array::getPost('apply_purchase_discount') !== '' && $oShop_Item->apply_purchase_discount = intval(Core_Array::getPost('apply_purchase_discount'));

		Core_Array::getPost('min_quantity') !== '' && $oShop_Item->min_quantity = floatval(Core_Array::getPost('min_quantity'));
		Core_Array::getPost('max_quantity') !== '' && $oShop_Item->max_quantity = floatval(Core_Array::getPost('max_quantity'));
		Core_Array::getPost('quantity_step') !== '' && $oShop_Item->quantity_step = floatval(Core_Array::getPost('quantity_step'));

		$oShop_Item->save();

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
}