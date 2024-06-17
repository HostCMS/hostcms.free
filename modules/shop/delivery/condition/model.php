<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Delivery_Condition_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Delivery_Condition_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img=1;

	/**
	 * Backend property
	 * @var int
	 */
	public $orderfield = '';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_delivery' => array(),
		'shop_delivery_condition_dir' => array(),
		'shop_country' => array(),
		'shop_country_location' => array(),
		'shop_country_location_city' => array(),
		'shop_country_location_city_area' => array(),
		'shop_tax' => array(),
		'shop_currency' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'max_weight' => 0.00,
		'min_weight' => 0.00,
		'max_price' => 0.00,
		'min_price' => 0.00,
		'price' => 0.00,
		'sorting' => 0,
		'active' => 1
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'price',
	);

	/**
	 * Price array
	 * @var array
	 */
	protected $_aPrice = array();

	/**
	 * Get $this->_aPrice
	 * @return array
	 */
	public function getAPrice()
	{
		return $this->_aPrice;
	}

	/**
	 * Set $this->_aPrice
	 * @param array $aPrice
	 * @return array
	 */
	public function setAPrice(array $aPrice)
	{
		$this->_aPrice = $aPrice;
		return $this;
	}

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
	 * Определение цены товара для условия доставки
	 * Determination of the price of goods for delivery terms
	 * @return array возвращает массив значений цен
	 * - $price['tax'] сумма налога
	 * - $price['rate'] размер налога
	 * - $price['price'] цена с учетом валюты без налога
	 * @hostcms-event shop_delivery_condition.onAfterGetPriceArray
	 */
	public function getPriceArray()
	{
		$oShop = $this->Shop_Delivery->Shop;

		$this->_aPrice = array(
			'tax' => 0,
			'rate' => 0,
			'price' => $this->price,
			'price_tax' => 0,
			'discount' => 0,
			'discounts' => array()
		);

		// Определяем коэффициент пересчета
		$fCurrencyCoefficient = $this->Shop_Currency->id > 0 && $oShop->Shop_Currency->id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->Shop_Currency, $oShop->Shop_Currency
			)
			: 0;

		// Умножаем цену товара на курс валюты в базовой валюте
		$this->_aPrice['price'] *= $fCurrencyCoefficient;

		$this->_aPrice['price_tax'] = $this->_aPrice['price_discount'] = $this->_aPrice['price'];

		if ($this->shop_tax_id)
		{
			$oShop_Tax = $this->Shop_Tax;

			if ($oShop_Tax->id)
			{
				$this->_aPrice['rate'] = $oShop_Tax->rate;

				// Если он не входит в цену
				if ($oShop_Tax->tax_is_included == 0)
				{
					// То считаем цену с налогом
					$this->_aPrice['tax'] = $oShop_Tax->rate / 100 * $this->_aPrice['price'];
					$this->_aPrice['price_tax'] = $this->_aPrice['price_discount'] = $this->_aPrice['price'] + $this->_aPrice['tax'];
				}
				else
				{
					$this->_aPrice['tax'] = $this->_aPrice['price'] / (100 + $oShop_Tax->rate) * $oShop_Tax->rate;
					$this->_aPrice['price_tax'] = $this->_aPrice['price'];
					$this->_aPrice['price'] -= $this->_aPrice['tax'];
				}
			}
		}

		$oShop_Controller = Shop_Controller::instance();

		Core_Event::notify($this->_modelName . '.onAfterGetPriceArray', $this);

		// Округляем значения, переводим с научной нотации 1Е+10 в десятичную
		$this->_aPrice['tax'] = $oShop_Controller->round($this->_aPrice['tax']);
		$this->_aPrice['price'] = $oShop_Controller->round($this->_aPrice['price']);
		$this->_aPrice['price_discount'] = $oShop_Controller->round($this->_aPrice['price_discount']);
		$this->_aPrice['price_tax'] = $oShop_Controller->round($this->_aPrice['price_tax']);

		return $this->_aPrice;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_delivery_condition.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_delivery_condition.onBeforeRedeclaredGetStdObject
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
		$aPrices = $this->getPriceArray();

		$this->clearXmlTags()
			->addXmlTag('price', $aPrices['price_tax']);

		return $this;
	}

	/**
	 * Change status
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function time_fromBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return date("H:i", strtotime($this->time_from));
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function time_toBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return date("H:i", strtotime($this->time_to));
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->time_from != '00:00:00' || $this->time_to != '00:00:00')
		{
			?><span class="badge badge-square badge-pink inverted margin-left-5 small"><i class="fas fa-stopwatch"></i> <?php echo date("H:i", strtotime($this->time_from)), ' — ', date("H:i", strtotime($this->time_to))?></span><?php
		}
	}
	
	/**
	 * Backend callback method
	 * @return string
	 */
	public function priceBackend()
	{
		return $this->shop_currency_id
			? $this->Shop_Currency->formatWithCurrency($this->price)
			: $this->price;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_delivery_condition.onBeforeGetRelatedSite
	 * @hostcms-event shop_delivery_condition.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Delivery->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}