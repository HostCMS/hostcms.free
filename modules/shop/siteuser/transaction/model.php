<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Siteuser_Transaction_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Siteuser_Transaction_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $currency_name = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_group' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'siteuser' => array(),
		'shop_currency' => array(),
		'shop_order' => array(),
		'user' => array()
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'description';

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'amount' => 0.00,
		'amount_base_currency' => 0.00,
		'active' => 1,
		'shop_order_id' => 0
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'datetime'
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_siteuser_transactions.datetime' => 'DESC'
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Get transactions by shop ID
	 * @param int $shop_id shop ID
	 * @return array
	 */
	public function getByShop($shop_id)
	{
		$this
			->queryBuilder()
			//->clear()
			->where('shop_id', '=', $shop_id);

		return $this->findAll();
	}

	/**
	 * Change transaction status
	 * @return self
	 * @hostcms-event shop_siteuser_transaction.onBeforeChangeActive
	 * @hostcms-event shop_siteuser_transaction.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_siteuser_transaction.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event shop_siteuser_transaction.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addXmlTag('date', strftime($this->Shop->format_date, Core_Date::sql2timestamp($this->datetime)))
			->addXmlTag('datetime', strftime($this->Shop->format_datetime, Core_Date::sql2timestamp($this->datetime)));

		$this->shop_currency_id && $this->addEntity($this->Shop_Currency);
		$this->shop_order_id && $this->addEntity($this->Shop_Order);

		return $this;
	}

	/**
	 * Backend callback method.
	 * Get amount transactions until current
	 * @return float
	 */
	public function adminTransactionTotalAmount()
	{
		$aTmp = Core_QueryBuilder::select(array('SUM(amount_base_currency)', 'amount'))
			->from('shop_siteuser_transactions')
			->where('shop_id', '=', $this->shop_id)
			->where('siteuser_id', '=', $this->siteuser_id)
			->where('active', '=', 1)
			->where('datetime', '<=', $this->datetime)
			->execute()->asAssoc()->current();

		return round($aTmp['amount'], 2);
	}
}