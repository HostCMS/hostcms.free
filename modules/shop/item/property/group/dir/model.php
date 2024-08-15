<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Property_Group_Dir_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Property_Group_Dir_Model extends Property_Dir_Model
{
	/**
	 * Callback property_id
	 * @var int
	 */
	public $enable = NULL;

	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'property_dirs';
	
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'property_dir';

	/**
	 * Allow access to property for group
	 * @param int $shop_group_id group id
	 * @return self
	 */
	public function allowAccess($shop_group_id = NULL)
	{
		$aProperties = $this->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			Core_Entity::factory('Shop_Item_Property_Group', $oProperty->id)->allowAccess($shop_group_id);
		}

		$aProperty_Dirs = $this->Property_Dirs->findAll();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			Core_Entity::factory('Shop_Item_Property_Group_Dir', $oProperty_Dir->id)->allowAccess($shop_group_id);
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
		$aProperties = $this->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			Core_Entity::factory('Shop_Item_Property_Group', $oProperty->id)->denyAccess($shop_group_id);
		}

		$aProperty_Dirs = $this->Property_Dirs->findAll();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			Core_Entity::factory('Shop_Item_Property_Group_Dir', $oProperty_Dir->id)->denyAccess($shop_group_id);
		}

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