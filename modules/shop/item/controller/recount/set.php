<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Controller_Recount_Set
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Item_Controller_Recount_Set extends Admin_Form_Action_Controller
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
			$oShop_Price_Setting->description = Core::_('Shop_Price_Setting.apply_item_recount_sets');
			$oShop_Price_Setting->datetime = Core_Date::timestamp2sql(time());
			$oShop_Price_Setting->save();

			$oShop_Price_Setting->number = $oShop_Price_Setting->id;
			$oShop_Price_Setting->save();

			$this->_oShop_Price_Setting = $oShop_Price_Setting;
		}

		return $this->_oShop_Price_Setting;
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

		// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
		$aChecked = $this->_Admin_Form_Controller->getChecked();

		// Clear checked list
		$this->_Admin_Form_Controller->clearChecked();

		foreach ($aChecked as $datasetKey => $checkedItems)
		{
			$oAdmin_Form_Dataset_Entity = $this->_Admin_Form_Controller->getDataset($datasetKey);

			if ($oAdmin_Form_Dataset_Entity)
			{
				foreach ($checkedItems as $key => $value)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->getById($key);
					if (!is_null($oShop_Item) && $oShop_Item->shortcut_id == 0 && $oShop_Item->type == 3)
					{
						if ($oShop_Item->price != $oShop_Item->getSetPrice())
						{
							$oShop_Price_Setting = $this->_getShopPriceSetting($oShop_Item->shop_id);

							$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
							$oShop_Price_Setting_Item->shop_price_setting_id = $oShop_Price_Setting->id;
							$oShop_Price_Setting_Item->shop_price_id = 0;
							$oShop_Price_Setting_Item->shop_item_id = $oShop_Item->id;
							$oShop_Price_Setting_Item->old_price = $oShop_Item->price;
							$oShop_Price_Setting_Item->new_price = $oShop_Item->getSetPrice();
							$oShop_Price_Setting_Item->save();
						}
					}
				}
			}
		}

		// Проводки, если есть
		!is_null($this->_oShop_Price_Setting) && $this->_oShop_Price_Setting->post();

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($operation));

		return $this;
	}
}