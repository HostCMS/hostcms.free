<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Item_Controller_Apply
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Warehouse_Item_Controller_Apply extends Admin_Form_Action_Controller_Type_Apply
{
	/**
	 * Shop_Warehouse_Inventory object.
	 * @var object
	 */
	protected $_oShop_Warehouse_Inventory = NULL;

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
			$oShop_Warehouse_Inventory->description = Core::_('Shop_Warehouse_Inventory.apply_warehouse_item');
			$oShop_Warehouse_Inventory->datetime = Core_Date::timestamp2sql(time());
			$oShop_Warehouse_Inventory->save();

			$oShop_Warehouse_Inventory->number = $oShop_Warehouse_Inventory->id;
			$oShop_Warehouse_Inventory->save();

			$this->_oShop_Warehouse_Inventory = $oShop_Warehouse_Inventory;
		}

		return $this->_oShop_Warehouse_Inventory;
	}

	/**
	 * Items count
	 * @var integer
	 */
	protected $_itemsCount = 0;

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 * @hostcms-event Shop_Warehouse_Item_Controller_Apply.onBeforeExecute
	 * @hostcms-event Shop_Warehouse_Item_Controller_Apply.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($this->_object));

		if (get_class($this->_object) == 'Shop_Warehouse_Item_Model')
		{
			$aAdmin_Form_Fields = $this->_Admin_Form_Action->Admin_Form->Admin_Form_Fields->findAll();

			$bChanged = FALSE;

			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				$columnName = $oAdmin_Form_Field->name;

				if ($columnName == 'count')
				{
					$sInputName = 'apply_check_' . $this->_datasetId . '_' . $this->_object->getPrimaryKey() . '_fv_' . $oAdmin_Form_Field->id;

					$value = Core_Array::getPost($sInputName);

					if (!is_null($value))
					{
						$oShop_Warehouse = $this->_object->Shop_Warehouse;

						if (!is_null($oShop_Warehouse))
						{
							$oShop_Warehouse_Item = $oShop_Warehouse->Shop_Warehouse_Items->getByShop_item_id($this->_object->id, FALSE);
							$fRest = $oShop_Warehouse_Item ? $oShop_Warehouse_Item->count : NULL;

							if ($fRest != $value)
							{
								$oShop_Warehouse_Inventory = $this->_getShopWarehouseInventory($oShop_Warehouse->id);

								$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
								$oShop_Warehouse_Inventory_Item->shop_warehouse_inventory_id = $oShop_Warehouse_Inventory->id;
								$oShop_Warehouse_Inventory_Item->shop_item_id = $this->_object->shop_item_id;
								$oShop_Warehouse_Inventory_Item->count = $value;
								$oShop_Warehouse_Inventory_Item->save();
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

			$bChanged && $this->_object->save()/*->clearCache()*/;

			$this->_itemsCount++;

			$aChecked = $this->_Admin_Form_Controller->getChecked();

			if ($this->_itemsCount == count($aChecked[$this->_datasetId]))
			{
				// Проводки, если есть
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