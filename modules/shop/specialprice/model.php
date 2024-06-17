<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Specialprice_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Specialprice_Model extends Core_Entity
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
		'shop_item' => array(),
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'price',
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'min_quantity' => 0,
		'max_quantity' => 0,
		'price' => 0,
		'percent' => 0
	);

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_specialprice.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_specialprice.onBeforeRedeclaredGetStdObject
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
		$oShop_Item_Controller = new Shop_Item_Controller();

		// $this->price может быть строкой 0.00
		if ($this->price > 0)
		{
			$price = $this->price;
		}
		else
		{
			Core::moduleIsActive('siteuser') && $oShop_Item_Controller->siteuser(
				Core_Entity::factory('Siteuser')->getCurrent()
			);

			$price = $oShop_Item_Controller->getPrice($this->Shop_Item) * $this->percent / 100;
		}

		$aPrices = $oShop_Item_Controller->calculatePrice($price, $this->Shop_Item);

		$this->clearXmlTags()
			->addXmlTag('price', $aPrices['price_discount']);

		$this->_isTagAvailable('discount') && $this->addXmlTag('discount', $aPrices['discount']);
		$this->_isTagAvailable('tax') && $this->addXmlTag('tax', $aPrices['tax']);
		$this->_isTagAvailable('price_tax') && $this->addXmlTag('price_tax', $aPrices['price_tax']);

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_specialprice.onBeforeGetRelatedSite
	 * @hostcms-event shop_specialprice.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}