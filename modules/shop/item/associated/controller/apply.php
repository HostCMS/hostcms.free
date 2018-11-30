<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Associated_Controller_Apply extends Admin_Form_Action_Controller_Type_Apply
{
	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		// Получение списка полей объекта
		$aColumns = $this->_object->getTableColumns();

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
	}
}