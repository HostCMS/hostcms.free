<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Controller_Apply
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Controller_Apply extends Admin_Form_Action_Controller_Type_Apply
{
	/**
	 * Shop_Price_Setting object.
	 * @var object
	 */
	protected $_oShop_Price_Setting = NULL;

	/**
	 * Shop_Warehouse_Inventory object.
	 * @var object
	 */
	protected $_oShop_Warehouse_Inventory = NULL;

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
			$oShop_Price_Setting->description = Core::_('Shop_Price_Setting.apply_item');
			$oShop_Price_Setting->datetime = Core_Date::timestamp2sql(time());
			$oShop_Price_Setting->save();

			$oShop_Price_Setting->number = $oShop_Price_Setting->id;
			$oShop_Price_Setting->save();

			$this->_oShop_Price_Setting = $oShop_Price_Setting;
		}

		return $this->_oShop_Price_Setting;
	}

	/**
	 * Get $Shop_Warehouse_Inventory object
	 * @param mixed $shop_warehouse_id shop warehouse id
	 * @return object|NULL
	 */
	protected function _getShopWarehouseInventory($shop_warehouse_id)
	{
		if (is_null($this->_oShop_Warehouse_Inventory))
		{
			$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory');
			$oShop_Warehouse_Inventory->shop_warehouse_id = $shop_warehouse_id;
			$oShop_Warehouse_Inventory->number = '';
			$oShop_Warehouse_Inventory->description = Core::_('Shop_Warehouse_Inventory.apply_item');
			$oShop_Warehouse_Inventory->datetime = Core_Date::timestamp2sql(time());
			$oShop_Warehouse_Inventory->save();

			$oShop_Warehouse_Inventory->number = $oShop_Warehouse_Inventory->id;
			$oShop_Warehouse_Inventory->save();

			$this->_oShop_Warehouse_Inventory = $oShop_Warehouse_Inventory;
		}

		return $this->_oShop_Warehouse_Inventory;
	}

	protected $_itemsCount = 0;

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 * @hostcms-event Shop_Item_Controller_Apply.onBeforeExecute
	 * @hostcms-event Shop_Item_Controller_Apply.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		if (get_class($this->_object) == 'Shop_Item_Model')
		{
			$aAdmin_Form_Fields = $this->_Admin_Form_Action->Admin_Form->Admin_Form_Fields->findAll();

			$bChanged = FALSE;

			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				$columnName = $oAdmin_Form_Field->name;

				if ($columnName == 'adminPrice')
				{
					$sInputName = 'apply_check_1_' . $this->_object->getPrimaryKey() . '_fv_' . $oAdmin_Form_Field->id;

					$value = floatval(Core_Array::getPost($sInputName));

					if ($this->_object->price != $value)
					{
						$oShop_Price_Setting = $this->_getShopPriceSetting($this->_object->shop_id);

						$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
						$oShop_Price_Setting_Item->shop_price_setting_id = $oShop_Price_Setting->id;
						$oShop_Price_Setting_Item->shop_price_id = 0; // Розничная
						$oShop_Price_Setting_Item->shop_item_id = $this->_object->id;
						$oShop_Price_Setting_Item->old_price = $this->_object->price;
						$oShop_Price_Setting_Item->new_price = $value;
						$oShop_Price_Setting_Item->save();
					}
				}
				elseif ($columnName == 'adminRest')
				{
					$sInputName = 'apply_check_1_' . $this->_object->getPrimaryKey() . '_fv_' . $oAdmin_Form_Field->id;

					$value = floatval(Core_Array::getPost($sInputName));

					$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse')->getDefault();

					if (!is_null($oShop_Warehouse))
					{
						$fRest = $oShop_Warehouse->getRest($this->_object->id);

						if ($fRest != $value)
						{
							$oShop_Warehouse_Inventory = $this->_getShopWarehouseInventory($oShop_Warehouse->id);

							$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
							$oShop_Warehouse_Inventory_Item->shop_warehouse_inventory_id = $oShop_Warehouse_Inventory->id;
							$oShop_Warehouse_Inventory_Item->shop_item_id = $this->_object->id;
							$oShop_Warehouse_Inventory_Item->count = $value;
							$oShop_Warehouse_Inventory_Item->save();
						}
					}
				}
				else
				{
					$this->_apply($oAdmin_Form_Field)
						&& $bChanged = TRUE;
				}
			}

			$bChanged && $this->_object->save();

			$this->_itemsCount++;

			$aChecked = $this->_Admin_Form_Controller->getChecked();

			if ($this->_itemsCount == count($aChecked[1]))
			{
				// Проводки, если есть
				!is_null($this->_oShop_Price_Setting) && $this->_oShop_Price_Setting->post();
				!is_null($this->_oShop_Warehouse_Inventory) && $this->_oShop_Warehouse_Inventory->post();
			}
		}
		else
		{
			return parent::execute($operation);
		}

		return $this;
	}
}