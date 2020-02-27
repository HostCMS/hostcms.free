<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Controller_Change_Attribute
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 */
	public function execute($operation = NULL)
	{
		if (is_null($operation))
		{
			// Original windowId
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
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
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
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
				->options($aMeasures)
				->caption(Core::_('Shop_Item.shop_measure_id'))
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
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
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
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
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
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
				->options(array(Core::_('Shop.allgroupsaccess')) + $aSiteuser_Groups)
				->caption(Core::_('Shop_Item.siteuser_group_id'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Active = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
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
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
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
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
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
				->divAttr(array('class' => 'form-group col-xs-12'))
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

			$oCore_Html_Entity_Form
				->add($oAdmin_Form_Entity_Select_Currencies)
				->add($oAdmin_Form_Entity_Select_Measures)
				->add($oAdmin_Form_Entity_Select_Producers)
				->add($oAdmin_Form_Entity_Select_Sellers)
				->add($oAdmin_Form_Entity_Select_Siteuser_Groups)
				->add($oAdmin_Form_Entity_Select_Active)
				->add($oAdmin_Form_Entity_Select_Indexing)
				->add($oAdmin_Form_Entity_Select_Yandex)
				->add($oAdmin_Form_Entity_Select_Order_Discount)
				;

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			foreach ($aChecked as $datasetKey => $checkedItems)
			{
				$oAdmin_Form_Dataset_Entity = $this->_Admin_Form_Controller->getDataset($datasetKey);

				if ($oAdmin_Form_Dataset_Entity && get_class($oAdmin_Form_Dataset_Entity->getEntity()) == 'Shop_Item_Model')
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

			$oAdmin_Form_Entity_Button = Admin_Form_Entity::factory('Button')
				->name('apply')
				->type('submit')
				->class('applyButton btn btn-blue')
				->value($this->buttonName)
				->onclick(
					//'$("#' . $newWindowId . '").parents(".modal").remove(); '
					'bootbox.hideAll(); '
					. $this->_Admin_Form_Controller->getAdminSendForm(NULL, 'apply')
				)
				->controller($this->_Admin_Form_Controller);

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12')
						->add($oAdmin_Form_Entity_Button)
				);

			$oCore_Html_Entity_Div->execute();

			ob_start();

			Core::factory('Core_Html_Entity_Script')
				->value("$(function() {
					$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . $this->title . "', AppendTo: '#{$windowId}', width: 750, height: 400, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		else
		{
			$oShop_Item = $this->_object;

			Core_Array::getPost('shop_currency_id') && $oShop_Item->shop_currency_id = intval(Core_Array::getPost('shop_currency_id'));
			Core_Array::getPost('shop_producer_id') && $oShop_Item->shop_producer_id = intval(Core_Array::getPost('shop_producer_id'));
			Core_Array::getPost('shop_seller_id') && $oShop_Item->shop_seller_id = intval(Core_Array::getPost('shop_seller_id'));
			Core_Array::getPost('shop_measure_id') && $oShop_Item->shop_measure_id = intval(Core_Array::getPost('shop_measure_id'));
			Core_Array::getPost('siteuser_group_id') && $oShop_Item->siteuser_group_id = intval(Core_Array::getPost('siteuser_group_id'));

			Core_Array::getPost('active') !== '' && $oShop_Item->active = intval(Core_Array::getPost('active'));
			Core_Array::getPost('indexing') !== '' && $oShop_Item->indexing = intval(Core_Array::getPost('indexing'));
			Core_Array::getPost('yandex_market') !== '' && $oShop_Item->yandex_market = intval(Core_Array::getPost('yandex_market'));
			Core_Array::getPost('apply_purchase_discount') !== '' && $oShop_Item->apply_purchase_discount = intval(Core_Array::getPost('apply_purchase_discount'));

			$oShop_Item->save();

			$oShop_Item->clearCache();

			// Fast filter
			if ($oShop_Item->Shop->filter)
			{
				$Shop_Filter_Controller = new Shop_Filter_Controller($oShop_Item->Shop);
				$Shop_Filter_Controller->fill($oShop_Item);
			}
		}

		return $this;
	}
}