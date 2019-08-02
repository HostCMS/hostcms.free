<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'shop_price_entry' => array(),
		'shop_price_setting_item' => array(),
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
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_prices.id' => 'ASC',
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

		$this->Shop_Price_Setting_Items->deleteAll(FALSE);
		$this->Shop_Price_Entries->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Recount prices
	 * @return self
	 */
	public function recount()
	{
		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			Core::isFunctionEnable('set_time_limit') && @set_time_limit(90000);
			@ini_set('max_execution_time', '90000');
		}

		$offset = 0;
		$limit = 100;

		do {
			$oShop_Item_Prices = $this->Shop_Item_Prices;

			$oShop_Item_Prices->queryBuilder()
				->offset($offset)
				->limit($limit);

			$aShop_Item_Prices = $oShop_Item_Prices->findAll(FALSE);

			foreach ($aShop_Item_Prices as $oShop_Item_Price)
			{
				$oShop_Item_Price->value = $oShop_Item_Price->Shop_Item->price / 100 * $this->percent;
				$oShop_Item_Price->save();
			}
			$offset += $limit;
		}
		while (count($aShop_Item_Prices));

		return $this;
	}
}