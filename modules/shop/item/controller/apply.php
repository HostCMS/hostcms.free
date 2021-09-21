<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Controller_Apply
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$oShop_Price_Setting->posted = 0;
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
			$oShop_Warehouse_Inventory->posted = 0;
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
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($this->_object));

		if (get_class($this->_object) == 'Shop_Item_Model')
		{
			$aAdmin_Form_Fields = $this->_Admin_Form_Action->Admin_Form->Admin_Form_Fields->findAll();

			$bChanged = FALSE;

			$oShop = $this->_object->Shop;

			$oShop->filter
				&& $oShop_Filter_Controller = new Shop_Filter_Controller($oShop);

			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				$columnName = $oAdmin_Form_Field->name;

				if ($columnName == 'adminPrice')
				{
					$sInputName = 'apply_check_' . $this->_datasetId . '_' . $this->_object->getPrimaryKey() . '_fv_' . $oAdmin_Form_Field->id;

					$value = Core_Array::getPost($sInputName);

					if (!is_null($value) && $this->_object->price != $value)
					{
						$oShop_Price_Setting = $this->_getShopPriceSetting($this->_object->shop_id);

						$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
						$oShop_Price_Setting_Item->shop_price_setting_id = $oShop_Price_Setting->id;
						$oShop_Price_Setting_Item->shop_price_id = 0; // Розничная
						$oShop_Price_Setting_Item->shop_item_id = $this->_object->id;
						$oShop_Price_Setting_Item->old_price = $this->_object->price;
						$oShop_Price_Setting_Item->new_price = $value;
						$oShop_Price_Setting_Item->save();

						$this->_object->clearCache();
					}
				}
				elseif ($columnName == 'adminRest')
				{
					$sInputName = 'apply_check_' . $this->_datasetId . '_' . $this->_object->getPrimaryKey() . '_fv_' . $oAdmin_Form_Field->id;

					$value = Core_Array::getPost($sInputName);

					if (!is_null($value))
					{
						$oShop_Warehouse = $this->_object->Shop->Shop_Warehouses->getDefault();

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

								$this->_object->clearCache();
							}
						}
					}
				}
				else
				{
					$this->_apply($oAdmin_Form_Field)
						&& $bChanged = TRUE;
				}
			}

			$bChanged && $this->_object->save()->clearCache();

			if ($oShop->filter)
			{
				$oShop_Filter_Controller->fill($this->_object);

				// Fast filter for modifications
				$aModifications = $this->_object->Modifications->findAll(FALSE);
				foreach ($aModifications as $oModification)
				{
					$this->_object->active
						? $oShop_Filter_Controller->fill($oModification)
						: $oShop_Filter_Controller->remove($oModification);
				}
			}

			$this->_itemsCount++;

			$aChecked = $this->_Admin_Form_Controller->getChecked();

			if ($this->_itemsCount == count($aChecked[$this->_datasetId]))
			{
				// Проводки, если есть
				!is_null($this->_oShop_Price_Setting) && $this->_oShop_Price_Setting->post();
				!is_null($this->_oShop_Warehouse_Inventory) && $this->_oShop_Warehouse_Inventory->post();
			}

			$return = $this;
		}
		else
		{
			$return = parent::execute($operation);
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($this->_object));

		return $return;
	}
}