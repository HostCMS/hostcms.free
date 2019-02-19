<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Cart_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (is_null($this->Shop_Item->id))
		{
			return htmlspecialchars($this->name);
		}
		else
		{
			$sShopItemPath = '/admin/shop/item/index.php';
			$iShopItemId = $this->Shop_Item->id;

			return sprintf(
				'<a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a>',
				htmlspecialchars($oAdmin_Form_Controller->getAdminActionLoadHref($sShopItemPath, 'edit', NULL, 1, $iShopItemId)),
				htmlspecialchars($this->name)
			);
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function restBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->Shop_Item->getRest();
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
		$price = $this->getPrice();

		return Shop_Controller::instance()->round($price) . ' ' . $this->Shop->Shop_Currency->name;
	}

	/**
	 * Get amount with currency name
	 * @return string
	 */
	public function amount()
	{
		return htmlspecialchars(
			sprintf("%.2f %s", $this->getPrice() * $this->quantity, $this->Shop->Shop_Currency->name)
		);
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
	 * Show properties in XML
	 * @param boolean $showXmlProperties
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE)
	{
		$this->_showXmlProperties = $showXmlProperties;
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
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_cart.onBeforeRedeclaredGetXml
	 * @hostcms-event shop_cart.onBeforeAddShopItem
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$oShop_Item = $this->Shop_Item
			->clearEntities()
			->showXmlBonuses(TRUE)
			->showXmlProperties($this->_showXmlProperties)
			->showXmlSpecialprices($this->_showXmlSpecialprices)
			->showXmlAssociatedItems($this->_showXmlAssociatedItems)
			->showXmlWarehousesItems($this->_showXmlWarehousesItems)
			->cartQuantity($this->quantity);

		// Parent item for modification
		if ($this->Shop_Item->modification_id)
		{
			$oModification = Core_Entity::factory('Shop_Item')->find($this->Shop_Item->modification_id);
			!is_null($oModification->id) && $oShop_Item->addEntity(
				$oModification->showXmlProperties($this->_showXmlProperties)
			);
		}

		Core_Event::notify($this->_modelName . '.onBeforeAddShopItem', $this, array($oShop_Item));

		$this->clearXmlTags()
			->addEntity($oShop_Item);

		return parent::getXml();
	}
}