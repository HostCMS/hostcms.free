<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Currency_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Currency_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop' => array(),
		'shop_item' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);
	
	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_currencies.sorting' => 'ASC',
		'shop_currencies.name' => 'ASC'
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
			$this->_preloadValues['date'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'exchange_rate' => 0,
		'sorting' => 0
	);

	/**
	 * Set basic currency
	 */
	public function setBasic()
	{
		$this->save();

		$oShop_Currency = Core_Entity::factory('Shop_Currency');
		$oShop_Currency
			->queryBuilder()
			->where('id', '!=', $this->id);

		$aShop_Currencies = $oShop_Currency->findAll();

		foreach($aShop_Currencies as $oShop_Currency)
		{
			$oShop_Currency->default = 0;

			$this->exchange_rate == 0 && $this->exchange_rate = 1;

			$oShop_Currency->exchange_rate /= $this->exchange_rate;

			$oShop_Currency->update();
		}

		$this->default = 1;
		$this->exchange_rate = 1;
		$this->save();
	}

	/**
	 * Get currency by name and code fields
	 * @param string $string name or code
	 * @param boolean $bCache cache mode
	 * @return Shop_Currency_Model|NULL
	 */
	public function getByLike($string, $bCache = TRUE)
	{
		$this->queryBuilder()
			->clear()
			->where('name', 'LIKE', "%{$string}%")
			->setOr()
			->where('code', 'LIKE', "%{$string}%")
			->limit(1);

		$aObjects = $this->findAll($bCache);

		if (count($aObjects) > 0)
		{
			return $aObjects[0];
		}

		return NULL;
	}

	/**
	 * Get default currency
	 * @return Shop_Currency_Model|NULL
	 */
	public function getDefault()
	{
		$this->queryBuilder()
			->clear()
			->where('default', '=', 1)
			->limit(1);

		$aObjects = $this->findAll();

		return count($aObjects) > 0
			? $aObjects[0]
			: NULL;
	}
}