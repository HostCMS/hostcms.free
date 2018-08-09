<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Property_For_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Property_For_Group_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_group' => array(),
		'shop_item_property' => array(),
		'user' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Разрешить дополнительное свойство $oProperty->Shop_Item_Property->id группе $shop_group_id
	 *
	 * <code>
	 * $oShop->Shop_Item_Property_For_Groups->allowAccess($oProperty->Shop_Item_Property->id, $shop_group_id);
	 * </code>
	 * @param int $shop_item_property_id property ID
	 * @param int $shop_group_id group ID
	 * @return self
	 */
	public function allowAccess($shop_item_property_id, $shop_group_id)
	{
		// Разрешаем доп. св-во для группы
		$oShop_Item_Property_For_Group = $this->getByShopItemPropertyIdAndGroupId($shop_item_property_id, $shop_group_id);

		if (is_null($oShop_Item_Property_For_Group))
		{
			$oShop_Item_Property_For_Group = Core_Entity::factory('Shop_Item_Property_For_Group');
			$oShop_Item_Property_For_Group->shop_group_id = $shop_group_id;
			$oShop_Item_Property_For_Group->shop_item_property_id = $shop_item_property_id;
			Core_Entity::factory('Shop_Item_Property', $shop_item_property_id)
				->Shop
				->add($oShop_Item_Property_For_Group);
		}
		return $this;
	}

	/**
	 * Get element by property ID and group ID
	 * @param int $shop_item_property_id property ID
	 * @param int $shop_group_id group ID
	 * @return self|NULL
	 */
	public function getByShopItemPropertyIdAndGroupId($shop_item_property_id, $shop_group_id)
	{
		$shop_item_property_id = intval($shop_item_property_id);
		$shop_group_id = intval($shop_group_id);

		$this
			->queryBuilder()
			->where('shop_group_id', '=', $shop_group_id)
			->where('shop_item_property_id', '=', $shop_item_property_id)
			->limit(1);

		$aShop_Item_Property_For_Group = $this->findAll(FALSE);

		return isset($aShop_Item_Property_For_Group[0])
			? $aShop_Item_Property_For_Group[0]
			: NULL;
	}
}