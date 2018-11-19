<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ заказов пользователя в магазине.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств заказов, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - ordersPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств заказов, по умолчанию FALSE
 * - offset($offset) смещение, с которого выводить товары. По умолчанию 0
 * - limit($limit) количество выводимых заказов
 * - page(2) текущая страница, по умолчанию 0, счет ведется с 0
 * - pattern($pattern) шаблон разбора данных в URI, см. __construct()
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'itemsProperties',
		'ordersPropertiesList',
		'offset',
		'limit',
		'page',
		'total',
		'pattern',
		'patternExpressions',
		'patternParams',
	);

	/**
	 * Shop orders
	 * @var Shop_Orders
	 */
	protected $_Shop_Orders = NULL;

	/**
	 * List of properties for item
	 * @var array
	 */
	protected $_aOrder_Properties = array();

	/**
	 * List of property directories for item
	 * @var array
	 */
	protected $_aOrder_Property_Dirs = array();

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		$this->_Shop_Orders = $oShop->Shop_Orders;

		$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

		if (!is_null($oSiteuser))
		{
			$siteuser_id = $oSiteuser->id;
		}
		else
		{
			throw new Core_Exception('Siteuser does not exist.');
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_id')
				->value($siteuser_id)
		);

		$this->_Shop_Orders
			->queryBuilder()
			->select('shop_orders.*')
			->where('shop_orders.siteuser_id', '=', $siteuser_id)
			->orderBy('shop_orders.datetime', 'DESC');

		$this->itemsProperties = FALSE;
		$this->ordersPropertiesList = FALSE;
		$this->limit = 999;
		$this->offset = 0;
		$this->page = 0;

		$oStructure = Core_Entity::factory('Structure', CURRENT_STRUCTURE_ID);

		$sPath = $oStructure->getPath();

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('path')
				->value($sPath)
		);

		$this->pattern = rawurldecode($sPath) . '(page-{page}/)';
		$this->patternExpressions = array(
			'page' => '\d+',
		);
	}

	/**
	 * Get orders
	 * @return Shop_Order_Model
	 */
	public function shopOrders()
	{
		return $this->_Shop_Orders;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Order_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oShop = $this->getEntity();

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('page')
				->value(intval($this->page))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		// Load model columns BEFORE FOUND_ROWS()
		Core_Entity::factory('Shop_Order')->getTableColumns();

		// Load user BEFORE FOUND_ROWS()
		$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();

		$this->_Shop_Orders
			->queryBuilder()
			->sqlCalcFoundRows()
			->offset(intval($this->offset))
			->limit(intval($this->limit));

		$aShop_Orders = $this->_Shop_Orders->findAll(FALSE);

		if ($this->page && !count($aShop_Orders))
		{
			return $this->error404();
		}

		$row = Core_QueryBuilder::select(array('FOUND_ROWS()', 'count'))->execute()->asAssoc()->current();
		$this->total = $row['count'];

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total')
				->value(intval($this->total))
		);

		// Показывать дополнительные свойства информационного элемента
		if ($this->itemsProperties && $this->ordersPropertiesList)
		{
			$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $oShop->id);

			$oProperties = $oShop_Order_Property_List->Properties;
			if (is_array($this->ordersPropertiesList) && count($this->ordersPropertiesList))
			{
				$oProperties->queryBuilder()
					->where('properties.id', 'IN', $this->ordersPropertiesList);
			}
			$aProperties = $oProperties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$this->_aOrder_Properties[$oProperty->property_dir_id][] = $oProperty->clearEntities();
			}

			$aProperty_Dirs = $oShop_Order_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aOrder_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir->clearEntities();
			}

			$Shop_Order_Properties = Core::factory('Core_Xml_Entity')
				->name('shop_order_properties');

			$this->addEntity($Shop_Order_Properties);

			$this->_addordersPropertiesList(0, $Shop_Order_Properties);
		}

		// Paymentsystems
		$oShopPaymentSystemsEntity = Core::factory('Core_Xml_Entity')
			->name('shop_payment_systems');

		$this->addEntity(
			$oShopPaymentSystemsEntity
		);

		$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->getAllByActive(1);
		foreach ($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$oShopPaymentSystemsEntity->addEntity(
				$oShop_Payment_System->clearEntities()
			);
		}

		foreach ($aShop_Orders as $oShop_Order)
		{
			$oShop_Order
				->clearEntities()
				->showXmlCurrency(TRUE)
				->showXmlCountry(TRUE)
				->showXmlItems(TRUE)
				->showXmlDelivery(TRUE)
				->showXmlPaymentSystem(TRUE)
				->showXmlOrderStatus(TRUE);

			$this->itemsProperties && $oShop_Order->showXmlProperties($this->itemsProperties);

			$this->addEntity($oShop_Order);
		}

		return parent::show();
	}

	/**
	 * Add items properties list to $parentObject
	 * @param int $parent_id parent group ID
	 * @param object $parentObject object
	 * @return self
	 */
	protected function _addordersPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aOrder_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aOrder_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addordersPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aOrder_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aOrder_Properties[$parent_id]);
		}

		return $this;
	}

	/**
	 * Parse URL and set controller properties
	 * @return self
	 * @hostcms-event Shop_Controller_Show.onBeforeParseUrl
	 * @hostcms-event Shop_Controller_Show.onAfterParseUrl
	 */
	public function parseUrl()
	{
		Core_Event::notify(get_class($this) . '.onBeforeParseUrl', $this);

		$oShop = $this->getEntity();

		$Core_Router_Route = new Core_Router_Route($this->pattern, $this->patternExpressions);
		$this->patternParams = $matches = $Core_Router_Route->applyPattern(Core::$url['path']);

		if (isset($matches['page']) && is_numeric($matches['page']))
		{
			if ($matches['page'] > 1)
			{
				$this->page($matches['page'] - 1)
					->offset($this->limit * $this->page);
			}
			else
			{
				return $this->error404();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

		return $this;
	}

	/**
	 * Define handler for 404 error
	 * @return self
	 */
	public function error404()
	{
		Core_Page::instance()->error404();

		return $this;
	}
}