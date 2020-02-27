<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Delivery_Option_Controller_Tab
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Delivery_Option_Controller_Tab extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_id',
		'shop_item_id',
	);

	/**
	 * Form controller
	 * @var Admin_Form_Controller
	 */
	protected $_Admin_Form_Controller = NULL;

	/**
	 * Constructor.
	 * @param Admin_Form_Controller $Admin_Form_Controller controller
	 */
	public function __construct(Admin_Form_Controller $Admin_Form_Controller)
	{
		parent::__construct();

		$this->_Admin_Form_Controller = $Admin_Form_Controller;
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aShop_Item_Delivery_Options = $this->_getShop_Item_Delivery_Options();

		$oDeliveryOptionDiv = Admin_Form_Entity::factory('Div');

		$oDivOpen = Admin_Form_Entity::factory('Code')->html('<div class="row delivery_options item_div clear" width="600">');
		$oDivClose = Admin_Form_Entity::factory('Code')->html('</div>');

		$oCost = Admin_Form_Entity::factory('Input')
			->caption(Core::_('Shop_Item_Delivery_Option.cost'))
			->name('deliveryOptionCost_[]')
			->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-3'))
			->format(array('lib' => array('value' => 'integer')));

		$oDay = Admin_Form_Entity::factory('Input')
			->caption(Core::_('Shop_Item_Delivery_Option.day'))
			->name('deliveryOptionDay_[]')
			->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-2'));

		$oOrderBefore = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Item_Delivery_Option.order_before'))
			->name('deliveryOptionOrderBefore_[]')
			->options(range(0, 24))
			->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-2'));
			
		$oOrderType = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Item_Delivery_Option.type'))
			->name('deliveryOptionType_[]')
			->options(array(
				0 => Core::_('Shop_Item_Delivery_Option.type0'),
				1 => Core::_('Shop_Item_Delivery_Option.type1')
			))
			->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-2'));

		if (count($aShop_Item_Delivery_Options))
		{
			foreach ($aShop_Item_Delivery_Options as $oShop_Item_Delivery_Option)
			{
				$oCost = clone $oCost;
				$oDay = clone $oDay;
				$oOrderBefore = clone $oOrderBefore;
				$oOrderType = clone $oOrderType;

				$oDeliveryOptionDiv
					->add($oDivOpen)
					->add(
						$oCost
							->value($oShop_Item_Delivery_Option->cost)
							->name("deliveryOptionCost_{$oShop_Item_Delivery_Option->id}")
							->id("deliveryOptionCost_{$oShop_Item_Delivery_Option->id}")
					)
					->add(
						$oDay
							->value($oShop_Item_Delivery_Option->day)
							->name("deliveryOptionDay_{$oShop_Item_Delivery_Option->id}")
							->id("deliveryOptionDay_{$oShop_Item_Delivery_Option->id}")
					)
					->add(
						$oOrderBefore
							->value($oShop_Item_Delivery_Option->order_before)
							->name("deliveryOptionOrderBefore_{$oShop_Item_Delivery_Option->id}")
							->id("deliveryOptionOrderBefore_{$oShop_Item_Delivery_Option->id}")
					)
					->add(
						$oOrderType
							->value($oShop_Item_Delivery_Option->type)
							->name("deliveryOptionType_{$oShop_Item_Delivery_Option->id}")
							->id("deliveryOptionType_{$oShop_Item_Delivery_Option->id}")
					)
					->add($this->imgBox())
					->add($oDivClose)
				;
			}
		}
		else
		{
			$oDeliveryOptionDiv
				->add($oDivOpen)
				->add($oCost)
				->add($oDay)
				->add($oOrderBefore)
				->add($oOrderType)
				->add($this->imgBox())
				->add($oDivClose)
			;
		}

		return $oDeliveryOptionDiv;
	}

	/**
	 * Get delivery options
	 * @return array
	 */
	protected function _getShop_Item_Delivery_Options()
	{
		$aShop_Item_Delivery_Options = array();

		if ($this->shop_id && $this->shop_item_id !== 0)
		{
			$oShop_Item_Delivery_Options = Core_Entity::factory('Shop_Item_Delivery_Option');
			$oShop_Item_Delivery_Options
				->queryBuilder()
				->where('shop_item_delivery_options.shop_id', '=', $this->shop_id)
				->where('shop_item_delivery_options.shop_item_id', '=', !is_null($this->shop_item_id)
					? $this->shop_item_id
					: 0);

			$aShop_Item_Delivery_Options = $oShop_Item_Delivery_Options->findAll();
		}

		return $aShop_Item_Delivery_Options;
	}

	/**
	 * Apply object property
	 */
	public function applyObjectProperty()
	{
		// Доставка, установленные значения
		$aShop_Item_Delivery_Options = $this->_getShop_Item_Delivery_Options();

		foreach ($aShop_Item_Delivery_Options as $oShop_Item_Delivery_Option)
		{
			$cost = Core_Array::getPost("deliveryOptionCost_{$oShop_Item_Delivery_Option->id}");

			if (!is_null($cost) && $cost !== '')
			{
				$oShop_Item_Delivery_Option
					->shop_id(intval($this->shop_id))
					->shop_item_id(intval($this->shop_item_id))
					->day(strval(Core_Array::getPost("deliveryOptionDay_{$oShop_Item_Delivery_Option->id}", 0)))
					->order_before(intval(Core_Array::getPost("deliveryOptionOrderBefore_{$oShop_Item_Delivery_Option->id}", 0)))
					->type(intval(Core_Array::getPost("deliveryOptionType_{$oShop_Item_Delivery_Option->id}", 0)))
					->cost(Shop_Controller::instance()->convertPrice($cost))
					->save();
			}
			else
			{
				$oShop_Item_Delivery_Option->delete();
			}
		}

		// Доставка, новые значения
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		
		$aDeliveryOptions = Core_Array::getPost('deliveryOptionCost_');
		
		if ($aDeliveryOptions)
		{
			$aDeliveryOptionDay = Core_Array::getPost('deliveryOptionDay_');
			$aDeliveryOptionOrderBefore = Core_Array::getPost('deliveryOptionOrderBefore_');
			$aDeliveryOptionType = Core_Array::getPost('deliveryOptionType_');

			foreach ($aDeliveryOptions as $key => $deliveryOption)
			{
				if ($deliveryOption !== '')
				{
					$price = Shop_Controller::instance()->convertPrice($deliveryOption);

					$oShop_Item_Delivery_Option = Core_Entity::factory('Shop_Item_Delivery_Option')
						->shop_id(intval($this->shop_id))
						->shop_item_id(intval($this->shop_item_id))
						->day(strval(Core_Array::get($aDeliveryOptionDay, $key)))
						->order_before(intval(Core_Array::get($aDeliveryOptionOrderBefore, $key)))
						->type(intval(Core_Array::get($aDeliveryOptionType, $key)))
						->cost($price)
						->save();

					ob_start();
					Core::factory('Core_Html_Entity_Script')
						->value("$(\"#{$windowId} input[name='deliveryOptionDay_\\[\\]']\").eq(0).prop('name', 'deliveryOptionDay_{$oShop_Item_Delivery_Option->id}');
						$(\"#{$windowId} select[name='deliveryOptionOrderBefore_\\[\\]']\").eq(0).prop('name', 'deliveryOptionOrderBefore_{$oShop_Item_Delivery_Option->id}');
						$(\"#{$windowId} select[name='deliveryOptionType_\\[\\]']\").eq(0).prop('name', 'deliveryOptionType_{$oShop_Item_Delivery_Option->id}');
						$(\"#{$windowId} input[name='deliveryOptionCost_\\[\\]']\").eq(0).prop('name', 'deliveryOptionCost_{$oShop_Item_Delivery_Option->id}');
						")
						->execute();

					$this->_Admin_Form_Controller->addMessage(ob_get_clean());
				}
			}
		}
	}

	public function imgBox($addFunction = '$.cloneDeliveryOption', $deleteOnclick = '$.deleteNewDeliveryOption(this)')
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		ob_start();
			Admin_Form_Entity::factory('Div')
				->class('no-padding add-remove-property margin-top-20 pull-left')
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