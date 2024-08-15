<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Favorite_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Favorite_Model extends Core_Entity
{
	/**
	 * Callback property_id
	 * @var int
	 */
	public $img = 1;

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
		'shop_favorite_list' => array(),
		'shop_item' => array(),
		'shop' => array(),
		'siteuser' => array()
	);

	/**
	 * Get Shop_Favorite by item $shop_item_id and site user $siteuser_id
	 * @param int $shop_item_id
	 * @param int $siteuser_id
	 * @param boolean $bCache
	 * @return Shop_Favorite_Model|NULL
	 */
	public function getByShopItemIdAndSiteuserId($shop_item_id, $siteuser_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_item_id', '=', $shop_item_id)
			->where('siteuser_id', '=', $siteuser_id)
			->limit(1);

		$aShop_Favorites = $this->findAll($bCache);

		return isset($aShop_Favorites[0]) ? $aShop_Favorites[0] : NULL;
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Sort properties values in XML
	 * @var mixed
	 */
	protected $_xmlSortPropertiesValues = TRUE;

	/**
	 * Show properties in XML
	 * @param boolean $showXmlProperties
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE, $xmlSortPropertiesValues = TRUE)
	{
		$this->_showXmlProperties = $showXmlProperties;

		$this->_xmlSortPropertiesValues = $xmlSortPropertiesValues;

		return $this;
	}

	/**
	 * Show modifications data in XML
	 * @var boolean
	 */
	protected $_showXmlModifications = FALSE;

	/**
	 * Add modifications XML to item
	 * @param boolean $showXmlModifications mode
	 * @return self
	 */
	public function showXmlModifications($showXmlModifications = TRUE)
	{
		$this->_showXmlModifications = $showXmlModifications;
		return $this;
	}

	/**
	 * Show special prices data in XML
	 * @var boolean
	 */
	protected $_showXmlSpecialprices = FALSE;

	/**
	 * Add special prices XML to item
	 * @param boolean $showXmlSpecialprices mode
	 * @return self
	 */
	public function showXmlSpecialprices($showXmlSpecialprices = TRUE)
	{
		$this->_showXmlSpecialprices = $showXmlSpecialprices;
		return $this;
	}

	/**
	 * Show comments rating data in XML
	 * @var boolean
	 */
	protected $_showXmlCommentsRating = FALSE;

	/**
	 * Add Comments Rating XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlCommentsRating($showXmlCommentsRating = TRUE)
	{
		$this->_showXmlCommentsRating = $showXmlCommentsRating;
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_favorite.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_favorite.onBeforeRedeclaredGetStdObject
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
		$oShop_Item = $this->Shop_Item
			->clearEntities()
			->showXmlWarehousesItems(TRUE)
			->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
			->showXmlModifications($this->_showXmlModifications)
			->showXmlSpecialprices($this->_showXmlSpecialprices)
			->showXmlCommentsRating($this->_showXmlCommentsRating);

		// Parent item for modification
		if ($this->Shop_Item->modification_id)
		{
			$oModification = Core_Entity::factory('Shop_Item')->find($this->Shop_Item->modification_id);
			!is_null($oModification->id) && $oShop_Item->addEntity(
				$oModification->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
			);
		}

		$this->clearXmlTags()
			->addEntity($oShop_Item);

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function dataNameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->shop_item_id
			? htmlspecialchars((string) $this->Shop_Item->name)
			: '';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function dataPriceBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars(
			$this->Shop->Shop_Currency->formatWithCurrency($this->dataPrice)
		);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shopBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->shop_id
			? htmlspecialchars((string) $this->Shop->name)
			: '';
	}

	/**
	 * Delete Favorite Item
	 * @return self
	 */
	public function markDeleted()
	{
		return $this->delete();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_favorite.onBeforeGetRelatedSite
	 * @hostcms-event shop_favorite.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}