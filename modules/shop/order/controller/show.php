<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ заказов пользователя в магазине.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств заказов, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - ordersPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств заказов, по умолчанию FALSE
 * - sortPropertiesValues(TRUE|FALSE) сортировать значения дополнительных свойств, по умолчанию TRUE.
 * - offset($offset) смещение, с которого выводить товары. По умолчанию 0
 * - limit($limit) количество выводимых заказов
 * - page(2) текущая страница, по умолчанию 0, счет ведется с 0
 * - pattern($pattern) шаблон разбора данных в URI, см. __construct()
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 * - commentsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств комментариев, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - commentsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств комментариев, по умолчанию TRUE. Ограничения на список свойств в виде массива влияет и на выборку значений свойств товара.
 * - comments(TRUE|FALSE) показывать комментарии для выбранных товаров, по умолчанию FALSE
 * - commentsRating(TRUE|FALSE) показывать оценки комментариев для выбранных товаров, по умолчанию FALSE
 * - commentsActivity('active'|'inactive'|'all') отображать комментарии: active — только активные, inactive — только неактивные, all — все, по умолчанию - active
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/shop' Магазин
 * - '/shop/shop_order_properties/property' Свойство в списке свойств заказов
 * - '/shop/shop_order_properties/property_dir' Раздел свойств в списке свойств заказов
 * - '/shop/shop_payment_system' Платежная система
 * - '/shop/shop_order' Заказ
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
		'sortPropertiesValues',
		'offset',
		'limit',
		'page',
		'total',
		'pattern',
		'patternExpressions',
		'patternParams',
		'commentsProperties',
		'commentsPropertiesList',
		'comments',
		'commentsRating',
		'commentsActivity',
		'url'
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
	 * Get _aOrder_Properties
	 * @return array
	 */
	public function getOrderProperties()
	{
		return $this->_aOrder_Properties;
	}

	/**
	 * Get _aGroup_Property_Dirs set
	 * @return array
	 */
	public function getOrderPropertyDirs()
	{
		return $this->_aOrder_Property_Dirs;
	}

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

		$this->itemsProperties = $this->ordersPropertiesList = $this->commentsProperties = $this->comments = $this->commentsRating = FALSE;

		$this->sortPropertiesValues = $this->commentsPropertiesList = TRUE;

		$this->commentsActivity = 'active'; // inactive, all

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

		// Named subpatterns {name} can consist of up to 32 alphanumeric characters and underscores, but must start with a non-digit.
		$this->pattern = rawurldecode($sPath) . '(page-{page}/)';

		$this->patternExpressions = array(
			'page' => '\d+',
		);
		
		$this->url = Core::$url['path'];
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
	 * @hostcms-event Shop_Order_Controller_Show.onBeforeAddOrdersPropertiesList
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
		$oUserCurrent = Core_Auth::getCurrentUser();

		$this->_Shop_Orders
			->queryBuilder()
			->sqlCalcFoundRows()
			->offset(intval($this->offset))
			->limit(intval($this->limit));

		$aShop_Orders = $this->_Shop_Orders->findAll(FALSE);

		if ($this->page && !count($aShop_Orders))
		{
			return $this->error410();
		}

		$this->total = Core_QueryBuilder::select()->getFoundRows();

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total')
				->value(intval($this->total))
		);

		// Показывать дополнительные свойства заказа
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
				$oProperty->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_order_properties/property', $oProperty);
				$this->_aOrder_Properties[$oProperty->property_dir_id][] = $oProperty;
			}

			$aProperty_Dirs = $oShop_Order_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_order_properties/property_dir', $oProperty_Dir);
				$this->_aOrder_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			$Shop_Order_Properties = Core::factory('Core_Xml_Entity')
				->name('shop_order_properties');

			$this->addEntity($Shop_Order_Properties);

			Core_Event::notify(get_class($this) . '.onBeforeAddOrdersPropertiesList', $this, array($Shop_Order_Properties));

			$this->_addOrdersPropertiesList(0, $Shop_Order_Properties);
		}

		// Показывать дополнительные свойства комментариев
		if ($this->commentsProperties || $this->commentsPropertiesList)
		{
			$aShowCommentPropertyIDs = $this->_commentsProperties();
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
			$oShop_Payment_System->clearEntities();
			$this->applyForbiddenAllowedTags('/shop/shop_payment_system', $oShop_Payment_System);
			$oShopPaymentSystemsEntity->addEntity($oShop_Payment_System);
		}

		if ($this->commentsProperties)
		{
			$mShowCommentPropertyIDs = is_array($this->commentsProperties)
				? $this->commentsProperties
				: $aShowCommentPropertyIDs;

			is_array($mShowCommentPropertyIDs) && !count($mShowCommentPropertyIDs) && $mShowCommentPropertyIDs = FALSE;
		}
		else
		{
			$mShowCommentPropertyIDs = FALSE;
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
				->showXmlOrderStatus(TRUE)
				->showXmlComments($this->comments)
				->showXmlCommentsRating($this->commentsRating)
				->commentsActivity($this->commentsActivity);

			$this->itemsProperties
				&& $oShop_Order->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues);

			$oShop_Order->showXmlCommentProperties($mShowCommentPropertyIDs);

			$this->applyForbiddenAllowedTags('/shop/shop_order', $oShop_Order);

			$this->addEntity($oShop_Order);
		}

		return parent::show();
	}

	/**
	 * List of properties for item
	 * @var array
	 */
	protected $_aComment_Properties = array();

	/**
	 * List of property directories for item
	 * @var array
	 */
	protected $_aComment_Property_Dirs = array();

	/**
	 * Get _aComment_Properties set
	 * @return array
	 */
	public function getCommentProperties()
	{
		return $this->_aComment_Properties;
	}

	/**
	 * Get _aItem_Property_Dirs set
	 * @return array
	 */
	public function getCommentPropertyDirs()
	{
		return $this->_aComment_Property_Dirs;
	}

	/**
	 * Add list of comment properties
	 * @return array
	 * @hostcms-event Shop_Order_Controller_Show.onBeforeAddCommentsPropertiesList
	 */
	protected function _commentsProperties()
	{
		$aShowPropertyIDs = array();

		$oShop = $this->getEntity();

		$oShop_Order_Comment_Property_List = Core_Entity::factory('Shop_Order_Comment_Property_List', $oShop->id);

		$bTpl = $this->_mode == 'tpl';

		$aProperties = is_array($this->commentsPropertiesList) && count($this->commentsPropertiesList)
			? $oShop_Order_Comment_Property_List->Properties->getAllByid($this->commentsPropertiesList, FALSE, 'IN')
			: $oShop_Order_Comment_Property_List->Properties->findAll();

		foreach ($aProperties as $oProperty)
		{
			$oProperty->clearEntities();
			$aShowPropertyIDs[] = $oProperty->id;
			$this->_aComment_Properties[$oProperty->property_dir_id][] = $oProperty;
		}

		// Список свойств комментариев
		if ($this->commentsPropertiesList)
		{
			$aProperty_Dirs = $oShop_Order_Comment_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aComment_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			if (!$bTpl)
			{
				$Comment_Properties = Core::factory('Core_Xml_Entity')
					->name('comment_properties');

				$this->addEntity($Comment_Properties);

				Core_Event::notify(get_class($this) . '.onBeforeAddCommentsPropertiesList', $this, array($Comment_Properties));

				$this->_addCommentsPropertiesList(0, $Comment_Properties);
			}
		}

		return $aShowPropertyIDs;
	}

	/**
	 * Add items properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addCommentsPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aComment_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aComment_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addCommentsPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aComment_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aComment_Properties[$parent_id]);
		}

		return $this;
	}

	/**
	 * Add items properties list to $parentObject
	 * @param int $parent_id parent group ID
	 * @param object $parentObject object
	 * @return self
	 */
	protected function _addOrdersPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aOrder_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aOrder_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addOrdersPropertiesList($oProperty_Dir->id, $oProperty_Dir);
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
	 * @hostcms-event Shop_Order_Controller_Show.onBeforeParseUrl
	 * @hostcms-event Shop_Order_Controller_Show.onAfterParseUrl
	 */
	public function parseUrl()
	{
		Core_Event::notify(get_class($this) . '.onBeforeParseUrl', $this);

		$Core_Router_Route = new Core_Router_Route($this->pattern, $this->patternExpressions);
		$this->patternParams = $matches = $Core_Router_Route->applyPattern($this->url);

		if (isset($matches['page']) && is_numeric($matches['page']))
		{
			if ($matches['page'] > 1)
			{
				$this->page($matches['page'] - 1)
					->offset($this->limit * $this->page);
			}
			else
			{
				return $this->error410();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

		return $this;
	}

	/**
	 * Define handler for 410 error
	 * @return self
	 */
	public function error410()
	{
		Core_Page::instance()->error410();

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