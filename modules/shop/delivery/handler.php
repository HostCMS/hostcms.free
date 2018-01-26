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
abstract class Shop_Delivery_Handler
{
	/**
	 * customer company
	 * @var object
	 */
	protected $_shopCompany = NULL;

	/**
	 * customer country
	 * @var object
	 */
	protected $_shopCountry = NULL;

	/**
	 * customer location
	 * @var object
	 */
	protected $_shopLocation = NULL;

	/**
	 * customer city
	 * @var object
	 */
	protected $_shopCity = NULL;

	/**
	 * Total Weight
	 * @var mixed
	 */
	protected $_weight = NULL;
	
	/**
	 * Total Amount
	 * @var mixed
	 */
	protected $_amount = NULL;

	/**
	 * postcode
	 * @var string
	 */
	protected $_postcode = NULL;

	/**
	 * volume
	 * @var float
	 */
	protected $_volume = NULL;

	/**
	 * Set Weight
	 * @param string $weight
	 * @return self
	 */
	public function weight($weight)
	{
		$this->_weight = $weight;
		return $this;
	}
	
	/**
	 * Set Amount
	 * @param string $amount
	 * @return self
	 */
	public function amount($amount)
	{
		$this->_amount = $amount;
		return $this;
	}

	/**
	 * Set company
	 * @param Shop_Company_Model $oShop_Company company
	 * @return self
	 */
	public function company(Shop_Company_Model $oShop_Company)
	{
		$this->_shopCompany = $oShop_Company;
		return $this;
	}

	/**
	 * Set postcode
	 * @param string $sPostcode volume
	 * @return self
	 */
	public function postcode($sPostcode)
	{
		$this->_postcode = $sPostcode;
		return $this;
	}

	/**
	 * Set volume
	 * @param float $fVolume volume
	 * @return self
	 */
	public function volume($fVolume)
	{
		$this->_volume = $fVolume;
		return $this;
	}

	/**
	 * Set country
	 * @param int $iCountryID country ID
	 * @return self
	 */
	public function country($iCountryID)
	{
		$this->_shopCountry = Core_Entity::factory('Shop_Country')->find($iCountryID);
		return $this;
	}

	/**
	 * Set location
	 * @param int $iLocationID country ID
	 * @return self
	 */
	public function location($iLocationID)
	{
		$this->_shopLocation = Core_Entity::factory('Shop_Country_Location')->find($iLocationID);
		return $this;
	}

	/**
	 * Set city
	 * @param int $iCityID country ID
	 * @return self
	 */
	public function city($iCityID)
	{
		$this->_shopCity = Core_Entity::factory('Shop_Country_Location_City')->find($iCityID);
		return $this;
	}

	/**
	 * Build Shop_Delivery_Handler class
	 * @param Shop_Delivery_Model $oShop_Delivery_Model shop delivery
	 */
	static public function factory(Shop_Delivery_Model $oShop_Delivery_Model)
	{
		require_once($oShop_Delivery_Model->getHandlerFilePath());

		$name = 'Shop_Delivery_Handler' . $oShop_Delivery_Model->id;
		if (class_exists($name))
		{
			return new $name($oShop_Delivery_Model);
		}
		return NULL;
	}

	/**
	 * Call ->checkPaymentBeforeContent() on each shop's Shop_Delivery_Handler
	 * @param Shop_Model $oShop
	 */
	static public function checkBeforeContent(Shop_Model $oShop)
	{
		self::_check($oShop, 'checkPaymentBeforeContent');
	}

	/**
	 * Call ->checkPaymentAfterContent() on each shop's Shop_Delivery_Handler
	 * @param Shop_Model $oShop
	 */
	static public function checkAfterContent(Shop_Model $oShop)
	{
		return self::_check($oShop, 'checkPaymentAfterContent');
	}

	/**
	 * Protected method to call $methodName on each shop's Shop_Payment_System_Handlers
	 */
	static protected function _check(Shop_Model $oShop, $methodName)
	{
		$oShop_Deliveries = $oShop->Shop_Deliveries;
		$oShop_Deliveries->queryBuilder()
			->where('shop_deliveries.active', '=', 1)
			->where('shop_deliveries.type', '=', 1);

		$aShop_Deliveries = $oShop_Deliveries->findAll(FALSE);

		foreach ($aShop_Deliveries as $oShop_Delivery)
		{
			$oHandler = self::factory($oShop_Delivery);
			if ($oHandler && method_exists($oHandler, $methodName))
			{
				$oHandler->$methodName();
			}
		}
	}

	/**
	 * Delivery
	 * @var Shop_Delivery_Model
	 */
	protected $_Shop_Delivery_Model = NULL;

	/**
	 * Constructor.
	 * @param Shop_Delivery_Model $oShop_Delivery_Model delivery
	 */
	public function __construct(Shop_Delivery_Model $oShop_Delivery_Model)
	{
		$this->_Shop_Delivery_Model = $oShop_Delivery_Model;
	}

	/**
	 * Execute business logic
	 */
	abstract public function execute();

	public function process($position)
	{
		$shopDeliveryInSession = $this->_Shop_Delivery_Model->id . '-' . $position;

		if (isset($_SESSION['hostcmsOrder']['deliveries'][$shopDeliveryInSession]))
		{
			$aTmp = $_SESSION['hostcmsOrder']['deliveries'][$shopDeliveryInSession];

			$_SESSION['hostcmsOrder']['shop_delivery_id'] = $aTmp['shop_delivery_id'];
			$_SESSION['hostcmsOrder']['shop_delivery_price'] = $aTmp['price'];
			$_SESSION['hostcmsOrder']['shop_delivery_rate'] = $aTmp['rate'];
			$_SESSION['hostcmsOrder']['shop_delivery_name'] = $aTmp['name'];
		}
	}
}