<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Price_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item' => array('through' => 'shop_item_price'),
		'shop_item_price' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'siteuser_group' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'percent' => 0
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Get Shop_Price by user group and shop
	 * @param int $siteuser_group_id user group id
	 * @param int $shop_id shop id
	 * @return self|NULL
	 */
	public function getBySiteuserGroupAndShop($siteuser_group_id, $shop_id)
	{
		$this->queryBuilder()
			->where('siteuser_group_id', '=', $siteuser_group_id)
			->where('shop_id', '=', $shop_id)
			->limit(1);

		$aShop_Prices = $this->findAll();

		return isset($aShop_Prices[0]) ? $aShop_Prices[0] : NULL;
	}

	/**
	 * Get all Shop_Prices by user group and shop
	 * @param int $siteuser_group_id user group id
	 * @param int $shop_id shop id
	 * @return array
	 */
	public function getAllBySiteuserGroupAndShop($siteuser_group_id, $shop_id)
	{
		$this->queryBuilder()
			->where('siteuser_group_id', '=', $siteuser_group_id)
			->where('shop_id', '=', $shop_id);

		return $this->findAll();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_price.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Item_Prices->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get price by guid
	 * @param string $guid guid
	 * @return Shop_Price_Model|NULL
	 */
	public function getByGuid($guid)
	{
		$this->queryBuilder()
			//->clear()
			->where('guid', '=', $guid)
			->limit(1);

		$aObjects = $this->findAll(FALSE);

		return isset($aObjects[0])
			? $aObjects[0]
			: NULL;
	}
}