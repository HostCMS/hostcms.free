<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Associated_Controller_Apply extends Admin_Form_Action_Controller_Type_Apply
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @hostcms-event Shop_Item_Associated_Controller_Apply.onBeforeExecute
	 * @hostcms-event Shop_Item_Associated_Controller_Apply.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($this->_object));

		$aAdmin_Form_Fields = $this->_Admin_Form_Action->Admin_Form->Admin_Form_Fields->findAll();
		foreach ($aAdmin_Form_Fields as $oAdmin_Form_Fields)
		{
			$sInputName = 'apply_check_' . $this->_datasetId . '_' . $this->_object->getPrimaryKey() . '_fv_' . $oAdmin_Form_Fields->id;

			$value = Core_Array::getPost($sInputName);

			if (!is_null($value))
			{
				$iShopItemId = intval(Core_Array::getGet('shop_item_id', 0));

				$oShopItem = Core_Entity::factory('Shop_Item', $iShopItemId);

				$oShopItemAssociated = $oShopItem->Shop_Item_Associateds->getByAssociatedId($this->_object->id);

				if (is_null($oShopItemAssociated))
				{
					$oShopItemAssociated = Core_Entity::factory('Shop_Item_Associated');
					$oShopItemAssociated->shop_item_id = $iShopItemId;
					$oShopItemAssociated->shop_item_associated_id = $this->_object->id;
				}

				$oShopItemAssociated->count = $value;
				$oShopItemAssociated->save();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($this->_object));
	}
}