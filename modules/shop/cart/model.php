<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Cart_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Cart_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Column consist item's name
	 * @var string
	 */
	public $name = NULL;

	/**
	 * Backend property
	 * @var string
	 */
	public $postpone_flag = NULL;

	/**
	 * Column consist item's price
	 * @var string
	 */
	public $price = NULL;

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'postpone' => 0,
		'quantity' => 0
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_item' => array(),
		'shop_warehouse' => array(),
		'siteuser' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_carts.id' => 'ASC',
	);

	/**
	 * Forbidden tags array
	 * @var array
	 */
	protected $_itemsForbiddenTags = array();

	/**
	 * Set forbidden tags array
	 * @param array $aForbiddenTags forbidden tags array
	 * @return self
	 */
	public function setItemsForbiddenTags(array $aForbiddenTags)
	{
		$this->_itemsForbiddenTags = $aForbiddenTags;
		return $this;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$object = $this->Shop_Item->shortcut_id
			? $this->Shop_Item->Shop_Item
			: $this->Shop_Item;

		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div');

		// Зачеркнут в зависимости от статуса родительского товара или своего статуса
		if (!$object->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}

		if (is_null($object->id))
		{
			$oCore_Html_Entity_Div->value(htmlspecialchars($this->name));
		}
		else
		{
			$oCore_Html_Entity_Div->add(
				Core_Html_Entity::factory('A')
					->href(
						$oAdmin_Form_Controller->getAdminActionLoadHref('/admin/shop/item/index.php', 'edit', NULL, 1, $object->id)
					)
					->target('_blank')
					->value(htmlspecialchars($this->name))
					->add(
						Core_Html_Entity::factory('I')->class('fa fa-external-link')
					)
			);
		}

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function restBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Core_Str::hideZeros($this->Shop_Item->getRest());
	}

	/**
	 * Get shop item price
	 * @return float
	 */
	public function getPrice()
	{
		$oSiteuser = $this->Siteuser;

		$Shop_Item_Controller = new Shop_Item_Controller();
		$Shop_Item_Controller
			->siteuser($oSiteuser)
			->count($this->quantity);

		$oShop_Item = $this->Shop_Item;

		$aPrice = $Shop_Item_Controller->getPrices($oShop_Item);

		return $aPrice['price_discount'];
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function priceBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars($this->Shop->Shop_Currency->formatWithCurrency(
			Shop_Controller::instance()->round($this->getPrice())
		));
	}

	/**
	 * Get amount with currency name
	 * @return string
	 */
	public function amount()
	{
		return htmlspecialchars($this->Shop->Shop_Currency->formatWithCurrency(
			$this->getPrice() * $this->quantity
		));
	}

	/**
	 * Get Shop_Cart by item $shop_item_id and site user $siteuser_id
	 * @param int $shop_item_id
	 * @param int $siteuser_id
	 * @param boolean $bCache
	 * @return Shop_Cart_Model|NULL
	 */
	public function getByShopItemIdAndSiteuserId($shop_item_id, $siteuser_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_item_id', '=', $shop_item_id)
			->where('siteuser_id', '=', $siteuser_id)
			->limit(1);

		$aShopCarts = $this->findAll($bCache);

		return isset($aShopCarts[0]) ? $aShopCarts[0] : NULL;
	}

	/**
	 * Get Shop_Cart by site user $siteuser_id
	 * @param int $siteuser_id
	 * @param boolean $bCache
	 * @return array
	 */
	public function getBySiteuserId($siteuser_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('siteuser_id', '=', $siteuser_id);

		return $this->findAll($bCache);
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
	 * Show special prices data in XML
	 * @var boolean
	 */
	protected $_showXmlAssociatedItems = FALSE;

	/**
	 * Add associated items XML to item
	 * @param boolean $showXmlAssociatedItems mode
	 * @return self
	 */
	public function showXmlAssociatedItems($showXmlAssociatedItems = TRUE)
	{
		$this->_showXmlAssociatedItems = $showXmlAssociatedItems;
		return $this;
	}

	/**
	 * Show items count data in XML
	 * @var boolean
	 */
	protected $_showXmlWarehousesItems = TRUE;

	/**
	 * Add warehouse information to XML
	 * @param boolean $showXmlWarehousesItems show status
	 * @return self
	 */
	public function showXmlWarehousesItems($showXmlWarehousesItems = TRUE)
	{
		$this->_showXmlWarehousesItems = $showXmlWarehousesItems;
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
	 * Show media in XML
	 * @var boolean
	 */
	protected $_showXmlMedia = FALSE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlMedia($showXmlMedia = TRUE)
	{
		$this->_showXmlMedia = $showXmlMedia;

		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_cart.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_cart.onBeforeRedeclaredGetStdObject
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
	 * @hostcms-event shop_cart.onBeforeAddShopItem
	 */
	protected function _prepareData()
	{
		$oShop_Item = $this->Shop_Item
			->clearEntities()
			->showXmlBonuses(TRUE)
			->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
			->showXmlSpecialprices($this->_showXmlSpecialprices)
			->showXmlAssociatedItems($this->_showXmlAssociatedItems)
			->showXmlWarehousesItems($this->_showXmlWarehousesItems)
			->showXmlMedia($this->_showXmlMedia)
			->showXmlModifications($this->_showXmlModifications)
			->cartQuantity($this->quantity);

		// Parent item for modification
		if ($this->Shop_Item->modification_id)
		{
			$oModification = Core_Entity::factory('Shop_Item')->find($this->Shop_Item->modification_id);
			!is_null($oModification->id) && $oShop_Item->addEntity(
				$oModification
					->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
					->showXmlAssociatedItems($this->_showXmlAssociatedItems)
			);
		}

		Core_Event::notify($this->_modelName . '.onBeforeAddShopItem', $this, array($oShop_Item));

		$this->applyItemsForbiddenTags($oShop_Item);

		$this->clearXmlTags()
			->addEntity($oShop_Item);

		return $this;
	}

	/**
	 * Apply forbidden xml tags for items
	 * @param Shop_Item_Model $oShop_Item item
	 * @return self
	 */
	public function applyItemsForbiddenTags($oShop_Item)
	{
		if (!is_null($this->_itemsForbiddenTags))
		{
			foreach ($this->_itemsForbiddenTags as $forbiddenTag)
			{
				$oShop_Item->addForbiddenTag($forbiddenTag);
			}
		}

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_cart.onBeforeGetRelatedSite
	 * @hostcms-event shop_cart.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}