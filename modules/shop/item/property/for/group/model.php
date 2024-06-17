<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Property_For_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
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

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_item_property_for_group.onBeforeGetRelatedSite
	 * @hostcms-event shop_item_property_for_group.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}