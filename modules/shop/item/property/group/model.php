<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Property_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Property_Group_Model extends Property_Model
{
	/**
	 * Callback property
	 * @var int
	 */
	public $enable = NULL;

	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'properties';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'property';

	/**
	 * Change access mode
	 * @return self
	 */
	public function changeAccess()
	{
		$shop_group_id = Core_Array::getGet('shop_group_id');

		$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group')->getByShopItemPropertyIdAndGroupId($this->Shop_Item_Property->id, $shop_group_id);

		!is_null($oShop_Item_Property_For_Group)
			? $this->denyAccess($shop_group_id)
			: $this->allowAccess($shop_group_id);

		return $this;
	}

	/**
	 * Allow access to property for group
	 * @param int $shop_group_id group id
	 * @return self
	 */
	public function allowAccess($shop_group_id = NULL)
	{
		is_null($shop_group_id) && $shop_group_id = Core_Array::getGet('shop_group_id');

		$oShop_Item_Property = $this->Shop_Item_Property;

		$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group')->getByShopItemPropertyIdAndGroupId($oShop_Item_Property->id, $shop_group_id);

		if (is_null($oShop_Item_Property_For_Group))
		{
			$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
			$oShop_Item_Property_For_Group->shop_group_id = $shop_group_id;
			$oShop_Item_Property_For_Group->shop_id = $oShop_Item_Property->shop_id;

			$oShop_Item_Property->add($oShop_Item_Property_For_Group);
		}

		return $this;
	}

	/**
	 * Deny access to property for group
	 * @param int $shop_group_id group id
	 * @return self
	 */
	public function denyAccess($shop_group_id = NULL)
	{
		is_null($shop_group_id) && $shop_group_id = Core_Array::getGet('shop_group_id');

		$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group')->getByShopItemPropertyIdAndGroupId($this->Shop_Item_Property->id, $shop_group_id);

		!is_null($oShop_Item_Property_For_Group)
			&& $oShop_Item_Property_For_Group->delete();

		return $this;
	}

	/**
	 * Allow access to properties for children groups
	 * @param int $shop_group_id parent group ID
	 * @return self
	 */
	public function allowAccessChildren($shop_group_id = NULL)
	{
		$this->allowAccess($shop_group_id);

		is_null($shop_group_id) && $shop_group_id = Core_Array::getGet('shop_group_id');

		$shop_id = Core_Array::getGet('shop_id');

		// Дочерние группы текущей группы текущего магазина
		$oShop_Groups = Core_Entity::factory('Shop', $shop_id)->Shop_Groups;
		$oShop_Groups->queryBuilder()
			->where('parent_id', '=', $shop_group_id)
			->where('shortcut_id', '=', 0);

		$aChildrenId = $oShop_Groups->getGroupChildrenId();
		foreach ($aChildrenId as $iGroupId)
		{
			$this->allowAccess($iGroupId);
		}

		return $this;
	}

	/**
	 * Deny access to properties for children groups
	 * @param int $shop_group_id parent group ID
	 * @return self
	 */
	public function denyAccessChildren($shop_group_id = NULL)
	{
		$this->denyAccess($shop_group_id);

		is_null($shop_group_id) && $shop_group_id = Core_Array::getGet('shop_group_id');

		$shop_id = Core_Array::getGet('shop_id');

		// Дочерние группы текущей группы текущего магазина
		$oShop_Groups = Core_Entity::factory('Shop', $shop_id)->Shop_Groups;
		$oShop_Groups->queryBuilder()
			->where('parent_id', '=', $shop_group_id)
			->where('shortcut_id', '=', 0);

		$aChildrenId = $oShop_Groups->getGroupChildrenId();
		foreach ($aChildrenId as $iGroupId)
		{
			$this->denyAccess($iGroupId);
		}

		return $this;
	}
}