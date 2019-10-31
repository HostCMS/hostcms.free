<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ магазина.
 *
 * Доступные методы:
 *
 * - group($id) идентификатор группы магазина, если FALSE, то вывод товаров осуществляется из всех групп
 * - groupsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств групп, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести
 * - groupsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств групп товаров, по умолчанию TRUE
 * - propertiesForGroups(array()) устанавливает дополнительное ограничение на вывод значений дополнительных свойств групп для массива идентификаторов групп (каким группам выводить доп. св-ва)
 * - groupsMode('tree') режим показа групп, может принимать следующие значения:
	none — не показывать группы,
	tree — показывать дерево групп и все группы на текущем уровне (по умолчанию),
	all — показывать все группы.
 * - groupsForbiddenTags(array('description')) массив тегов групп, запрещенных к передаче в генерируемый XML
 * - item(123) идентификатор показываемого товара
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE. Ограничения на список свойств в виде массива влияет и на выборку значений свойств товара.
 * - itemsPropertiesListJustAvailable(TRUE|FALSE) выводить только доступные значения у свойства, по умолчанию FALSE
 * - itemsForbiddenTags(array('description')) массив тегов товаров, запрещенных к передаче в генерируемый XML
 * - warehouseMode('all'|'in-stock'|'in-stock-modification') режим вывода товаров:
	'all' — все (по умолчанию),
	'in-stock' — на складе,
	'in-stock-modification' — на складе или модификация товара в наличии на складе.
 * - parentItem(123) идентификатор родительского товара для отображаемой модификации
 * - modifications(TRUE|FALSE) показывать модификации для выбранных товаров, по умолчанию FALSE
 * - modificationsList(TRUE|FALSE) показывать модификации товаров текущей группы на уровне товаров группы, по умолчанию FALSE
 * - filterShortcuts(TRUE|FALSE) выбирать ярлыки товаров текущей группы на уровне товаров группы, по умолчанию FALSE. Используется для фильтрации по дополнительным свойствам.
 * - addFilter() добавить условие отобра товаров, может задавать условие отобра по цене ->addFilter('price', '>', 100) или по значению свойства ->addFilter('property', 17, '=', 1)
 * - specialprices(TRUE|FALSE) показывать специальные цены для выбранных товаров, по умолчанию FALSE
 * - associatedItems(TRUE|FALSE) показывать сопутствующие товары для выбранных товаров, по умолчанию FALSE
 * - comments(TRUE|FALSE) показывать комментарии для выбранных товаров, по умолчанию FALSE
 * - votes(TRUE|FALSE) показывать рейтинг элемента, по умолчанию TRUE
 * - tags(TRUE|FALSE) выводить метки
 * - calculateCounts(TRUE|FALSE) вычислять общее количество товаров и групп в корневой группе, по умолчанию FALSE
 * - siteuser(TRUE|FALSE) показывать данные о пользователе сайта, связанного с выбранным товаром, по умолчанию TRUE
 * - siteuserProperties(TRUE|FALSE) выводить значения дополнительных свойств пользователей сайта, по умолчанию FALSE
 * - sets(TRUE|FALSE) показывать состав комплектов товаров, по умолчанию TRUE
 * - bonuses(TRUE|FALSE) выводить бонусы для товаров, по умолчанию TRUE
 * - barcodes(TRUE|FALSE) выводить штрихкоды для товаров, по умолчанию FALSE
 * - comparing(TRUE|FALSE) выводить сравниваемые товары, по умолчанию TRUE
 * - comparingLimit(10) максимальное количество выводимых сравниваемых товаров, по умолчанию 10
 * - favorite(TRUE|FALSE) выводить избранные товары, по умолчанию TRUE
 * - favoriteLimit(10) максимальное количество выводимых избранных товаров, по умолчанию 10
 * - favoriteOrder('ASC'|'DESC'|'RAND') направление сортировки избранных товаров, по умолчанию RAND
 * - viewed(TRUE|FALSE) выводить просмотренные товары, по умолчанию TRUE
 * - viewedLimit(10) максимальное количество выводимых просмотренных товаров, по умолчанию 10
 * - viewedOrder('ASC'|'DESC'|'RAND') направление сортировки просмотренных товаров, по умолчанию DESC
 * - orderBy('shop_items.name', 'ASC') задает направление сортировки товаров
 * - cart(TRUE|FALSE) выводить товары в корзине, по умолчанию FALSE
 * - warehousesItems(TRUE|FALSE) выводить остаток на каждом складе для товара, по умолчанию FALSE
 * - taxes(TRUE|FALSE) выводить список налогов, по умолчанию FALSE
 * - offset($offset) смещение, с которого выводить товары. По умолчанию 0
 * - limit($limit) количество выводимых товаров
 * - page(2) текущая страница, по умолчанию 0, счет ведется с 0
 * - pattern($pattern) шаблон разбора данных в URI, см. __construct()
 * - tag($path) путь тега, с использованием которого ведется отбор товаров
 * - producer($producer_id) идентификатор производителя, с использованием которого ведется отбор товаров
 * - cache(TRUE|FALSE) использовать кэширование, по умолчанию TRUE
 * - itemsActivity('active'|'inactive'|'all') отображать элементы: active — только активные, inactive — только неактивные, all — все, по умолчанию — active
 * - groupsActivity('active'|'inactive'|'all') отображать группы: active — только активные, inactive — только неактивные, all — все, по умолчанию — active
 * - commentsActivity('active'|'inactive'|'all') отображать комментарии: active — только активные, inactive — только неактивные, all — все, по умолчанию - active
 * - calculateTotal(TRUE|FALSE) вычислять общее количество найденных, по умолчанию TRUE
 * - showPanel(TRUE|FALSE) показывать панель быстрого редактирования, по умолчанию TRUE
 *
 * Доступные свойства:
 *
 * - total общее количество доступных для отображения записей
 * - patternParams массив данных, извелеченных из URI при применении pattern
 *
 * <code>
 * $Shop_Controller_Show = new Shop_Controller_Show(
 * 	Core_Entity::factory('Shop', 1)
 * );
 *
 * $Shop_Controller_Show
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('МагазинКаталогТоваров')
 * 	)
 * 	->limit(5)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'group',
		'groupsProperties',
		'groupsPropertiesList',
		'propertiesForGroups',
		'groupsMode',
		'groupsForbiddenTags',
		'item',
		'itemsProperties',
		'itemsPropertiesList',
		'itemsPropertiesListJustAvailable',
		'itemsForbiddenTags',
		'warehouseMode',
		'parentItem',
		'modifications',
		'modificationsList',
		'filterShortcuts',
		'specialprices',
		'associatedItems',
		'comments',
		'votes',
		'tags',
		'calculateCounts',
		'siteuser',
		'siteuserProperties',
		'sets',
		'bonuses',
		'barcodes',
		'comparing',
		'comparingLimit',
		'favorite',
		'favoriteLimit',
		'favoriteOrder',
		'viewed',
		'viewedLimit',
		'viewedOrder',
		'cart',
		'warehousesItems',
		'taxes',
		'offset',
		'limit',
		'page',
		'total',
		'pattern',
		'patternExpressions',
		'patternParams',
		'tag',
		'producer',
		'cache',
		'itemsActivity',
		'groupsActivity',
		'commentsActivity',
		'calculateTotal',
		'showPanel',
	);

	/**
	 * List of groups of shop
	 * @var array
	 */
	protected $_aShop_Groups = array();

	/**
	 * Get _aShop_Groups set
	 * @return array
	 */
	public function getShopGroups()
	{
		return $this->_aShop_Groups;
	}

	/**
	 * List of properties for item
	 * @var array
	 */
	protected $_aItem_Properties = array();

	/**
	 * List of property directories for item
	 * @var array
	 */
	protected $_aItem_Property_Dirs = array();

	/**
	 * Get _aItem_Properties set
	 * @return array
	 */
	public function getItemProperties()
	{
		return $this->_aItem_Properties;
	}

	/**
	 * Get _aItem_Property_Dirs set
	 * @return array
	 */
	public function getItemPropertyDirs()
	{
		return $this->_aItem_Property_Dirs;
	}

	/**
	 * List of properties for group
	 * @var array
	 */
	protected $_aGroup_Properties = array();

	/**
	 * List of property directories for group
	 * @var array
	 */
	protected $_aGroup_Property_Dirs = array();

	/**
	 * Get _aGroup_Properties set
	 * @return array
	 */
	public function getGroupProperties()
	{
		return $this->_aGroup_Properties;
	}

	/**
	 * Get _aGroup_Property_Dirs set
	 * @return array
	 */
	public function getGroupPropertyDirs()
	{
		return $this->_aGroup_Property_Dirs;
	}

	/**
	 * Shop's items object
	 * @var Shop_Item_Model
	 */
	protected $_Shop_Items = NULL;

	/**
	 * Shop's groups object
	 * @var Shop_Group_Model
	 */
	protected $_Shop_Groups = NULL;

	/**
	 * Array of siteuser's groups allowed for current siteuser
	 * @var array
	 */
	protected $_aSiteuserGroups = array();

	/**
	 * Cache name
	 * @var string
	 */
	protected $_cacheName = 'shop_show';

	/**
	 * Select modififactions, default's TRUE
	 */
	protected $_selectModifications = TRUE;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		$this->_aSiteuserGroups = $this->_getSiteuserGroups();

		$siteuser_id = 0;
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			$oSiteuser && $siteuser_id = $oSiteuser->id;
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_id')
				->value($siteuser_id)
		);

		$this->_setShopItems()->_setShopGroups();

		$this->limit = 10;
		$this->group = $this->offset = $this->page = 0;
		$this->item = $this->producer = NULL;
		$this->groupsProperties = $this->itemsProperties = $this->propertiesForGroups
			= $this->comments = $this->tags = $this->calculateCounts = $this->siteuserProperties
			= $this->warehousesItems = $this->taxes = $this->cart = $this->modifications
			= $this->modificationsList = $this->filterShortcuts = $this->itemsPropertiesListJustAvailable
			= $this->barcodes = FALSE;

		$this->siteuser = $this->cache = $this->itemsPropertiesList = $this->groupsPropertiesList
			= $this->bonuses = $this->sets = $this->comparing = $this->favorite = $this->viewed
			= $this->votes = $this->showPanel = $this->calculateTotal = TRUE;

		$this->viewedLimit = $this->comparingLimit = $this->favoriteLimit = 10;

		$this->favoriteOrder = 'RAND';
		$this->viewedOrder = 'DESC';

		$this->groupsMode = 'tree';
		$this->warehouseMode = 'all';

		$this->itemsActivity = $this->groupsActivity = $this->commentsActivity = 'active'; // inactive, all

		$this->pattern = rawurldecode(Core_Str::rtrimUri($this->getEntity()->Structure->getPath())) . '({path}/)(user-{user}/)(page-{page}/)(tag/{tag}/)(producer-{producer}/)';

		$this->patternExpressions = array(
			'page' => '\d+',
			'producer' => '\d+',
		);
	}

	/**
	 * Get array of siteuser groups for current siteuser. Exists group 0 (all) and -1 (parent)
	 * @return array
	 */
	protected function _getSiteuserGroups()
	{
		$aSiteuserGroups = array(0, -1);
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if ($oSiteuser)
			{
				$this->addCacheSignature('siteuser_id=' . $oSiteuser->id);

				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
				foreach ($aSiteuser_Groups as $oSiteuser_Group)
				{
					$aSiteuserGroups[] = $oSiteuser_Group->id;
				}
			}
		}

		return $aSiteuserGroups;
	}

	/**
	 * Set item's conditions
	 * @return self
	 */
	protected function _setShopItems()
	{
		$oShop = $this->getEntity();

		$this->_Shop_Items = $oShop->Shop_Items;

		switch ($oShop->items_sorting_direction)
		{
			case 1:
				$items_sorting_direction = 'DESC';
			break;
			case 0:
			default:
				$items_sorting_direction = 'ASC';
		}

		$this->_Shop_Items
			->queryBuilder()
			->clearOrderBy();

		// Определяем поле сортировки товаров
		switch ($oShop->items_sorting_field)
		{
			case 1:
				$this->_Shop_Items
					->queryBuilder()
					->orderBy('shop_items.name', $items_sorting_direction);
				break;
			case 2:
				$this->_Shop_Items
					->queryBuilder()
					->orderBy('shop_items.sorting', $items_sorting_direction)
					->orderBy('shop_items.name', $items_sorting_direction);
				break;
			case 0:
			default:
				$this->_Shop_Items
					->queryBuilder()
					->orderBy('shop_items.datetime', $items_sorting_direction);
		}

		$this->_Shop_Items
			->queryBuilder()
			->select('shop_items.*')
			//->where('shop_items.active', '=', 1)
			//->where('shop_items.modification_id', '=', 0)
			;

		$this->_applyItemConditions($this->_Shop_Items);

		return $this;
	}

	/**
	 * Apply warehouse's conditions
	 *
	 * @param Shop_Item_Model $oShop_Items
	 * @return self
	 */
	protected function _applyWarehouseConditions(Shop_Item_Model $oShop_Items)
	{
		switch ($this->warehouseMode)
		{
			case 'in-stock':
				$oShop_Items
					->queryBuilder()
					->leftJoin('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', 'shop_items.id')
					->having('SUM(shop_warehouse_items.count)', '>', 0)
					->groupBy('shop_items.id');
			break;
			case 'in-stock-modification':
				$oShop_Items
					->queryBuilder()
					// Модификации и остатки на складах модификаций
					->leftJoin(array('shop_items', 'modifications'), 'modifications.modification_id', '=', 'shop_items.id')
					->leftJoin(array('shop_warehouse_items', 'modifications_shop_warehouse_items'), 'modifications_shop_warehouse_items.shop_item_id', '=', 'modifications.id')
					// Остатки на складах основного отвара
					->leftJoin('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', 'shop_items.id')
					// Есть остатки на основном складе
					->havingOpen()
					->having('SUM(shop_warehouse_items.count)', '>', 0)
					// Или
					->setOr()
					// Есть остатки на складах у модификаций
					->having('SUM(modifications_shop_warehouse_items.count)', '>', 0)
					->havingClose()
					->groupBy('shop_items.id');
			break;
		}

		return $this;
	}

	/**
	 * Apply item's conditions
	 *
	 * @param Shop_Item_Model $oShop_Items
	 * @return self
	 */
	protected function _applyItemConditions(Shop_Item_Model $oShop_Items)
	{
		return $this->_applyItemConditionsQueryBuilder(
			$oShop_Items->queryBuilder()
		);
	}

	/**
	 * Apply item's conditions
	 *
	 * @param Core_QueryBuilder_Select $oCore_QueryBuilder_Select
	 * @return self
	 */
	protected function _applyItemConditionsQueryBuilder(Core_QueryBuilder_Select $oCore_QueryBuilder_Select, $tableName = 'shop_items')
	{
		$dateTime = Core_Date::timestamp2sql(time());

		$oCore_QueryBuilder_Select
			->open()
				->where($tableName . '.start_datetime', '<', $dateTime)
				->setOr()
				->where($tableName . '.start_datetime', '=', '0000-00-00 00:00:00')
			->close()
			->setAnd()
			->open()
				->where($tableName . '.end_datetime', '>', $dateTime)
				->setOr()
				->where($tableName . '.end_datetime', '=', '0000-00-00 00:00:00')
			->close()
			->where($tableName . '.siteuser_group_id', 'IN', $this->_aSiteuserGroups);

		return $this;
	}

	/**
	 * Set group's conditions
	 * @return self
	 */
	protected function _setShopGroups()
	{
		$oShop = $this->getEntity();

		$this->_Shop_Groups = $oShop->Shop_Groups;
		$this->_Shop_Groups
			->queryBuilder()
			->select('shop_groups.*')
			->where('shop_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
			//->where('shop_groups.active', '=', 1)
			;

		switch ($oShop->groups_sorting_direction)
		{
			case 0:
				$groups_sorting_direction = 'ASC';
				break;
			case 1:
			default:
				$groups_sorting_direction = 'DESC';
		}

		// Определяем поле сортировки групп
		switch ($oShop->groups_sorting_field)
		{
			case 0:
				$this->_Shop_Groups
					->queryBuilder()
					->orderBy('shop_groups.name', $groups_sorting_direction);
				break;
			case 1:
			default:
				$this->_Shop_Groups
					->queryBuilder()
					->orderBy('shop_groups.sorting', $groups_sorting_direction);
				break;
		}

		return $this;
	}

	/**
	 * Get/set _Shop_Items
	 * @param mixed $object
	 * @return self or _Shop_Items
	 */
	public function shopItems($object = NULL)
	{
		if (is_null($object))
		{
			return $this->_Shop_Items;
		}
		else
		{
			$this->_Shop_Items = $object;
			return $this;
		}
	}

	/**
	 * Get/set _Shop_Groups
	 * @param mixed $object
	 * @return self or _Shop_Groups
	 */
	public function shopGroups($object = NULL)
	{
		if (is_null($object))
		{
			return $this->_Shop_Groups;
		}
		else
		{
			$this->_Shop_Groups = $object;
			return $this;
		}
	}

	/**
	 * Add comparing goods
	 * @return self
	 */
	protected function _addComparing()
	{
		$oShop = $this->getEntity();

		$hostcmsCompare = Core_Array::get(Core_Array::getSession('hostcmsCompare', array()), $oShop->id, array());

		if (count($hostcmsCompare))
		{
			$this->addEntity(
				$oCompareEntity = Core::factory('Core_Xml_Entity')
					->name('comparing')
			);

			// Extract a slice of the array
			$hostcmsCompare = array_slice($hostcmsCompare, 0, $this->comparingLimit, TRUE);

			foreach ($hostcmsCompare as $key => $value)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->find($key);
				if (!is_null($oShop_Item->id))
				{
					$this->itemsProperties && $oShop_Item->showXmlProperties($this->itemsProperties);
					!$this->sets && $oShop_Item->showXmlSets($this->sets);
					$oCompareEntity->addEntity($oShop_Item->clearEntities());
				}
			}
		}

		return $this;
	}

	/**
	 * Add favorite goods
	 * @return self
	 * @hostcms-event Shop_Controller_Show.onBeforeAddFavoriteEntity
	 */
	protected function _addFavorite()
	{
		$oShop = $this->getEntity();

		$hostcmsFavorite = array();

		$aShop_Favorites = Shop_Favorite_Controller::instance()->getAll($oShop);
		foreach ($aShop_Favorites as $oShop_Favorite)
		{
			$hostcmsFavorite[] = $oShop_Favorite->shop_item_id;
		}

		if (count($hostcmsFavorite))
		{
			$this->addEntity(
				$oFavouriteEntity = Core::factory('Core_Xml_Entity')
					->name('favorite')
			);

			switch ($this->favoriteOrder)
			{
				case 'RAND':
					shuffle($hostcmsFavorite);
				break;
				case 'ASC':
					asort($hostcmsFavorite);
				break;
				case 'DESC':
					arsort($hostcmsFavorite);
				break;
				default:
					throw new Core_Exception("The favoriteOrder direction '%direction' doesn't allow",
						array('%direction' => $this->favoriteOrder)
					);
			}

			// Extract a slice of the array
			$hostcmsFavorite = array_slice($hostcmsFavorite, 0, $this->favoriteLimit);

			foreach ($hostcmsFavorite as $shop_item_id)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id);
				if (!is_null($oShop_Item->id))
				{
					$oFavorite_Shop_Item = clone $oShop_Item;
					$oFavorite_Shop_Item
						->id($oShop_Item->id)
						->showXmlProperties($this->itemsProperties)
						->showXmlBonuses($this->bonuses)
						->showXmlSpecialprices($this->specialprices);

					!$this->sets && $oFavorite_Shop_Item->showXmlSets($this->sets);

					$this->applyItemsForbiddenTags($oFavorite_Shop_Item);

					Core_Event::notify(get_class($this) . '.onBeforeAddFavoriteEntity', $this, array($oFavorite_Shop_Item));

					$oFavouriteEntity->addEntity($oFavorite_Shop_Item);
				}
			}
		}

		return $this;
	}

	/**
	 * Add viewed goods
	 * @return self
	 * @hostcms-event Shop_Controller_Show.onBeforeAddViewedEntity
	 */
	protected function _addViewed()
	{
		$oShop = $this->getEntity();

		$hostcmsViewed = Core_Array::get(Core_Array::getSession('hostcmsViewed', array()), $oShop->id, array());

		if (count($hostcmsViewed))
		{
			$this->addEntity(
				$oViewedEntity = Core::factory('Core_Xml_Entity')
					->name('viewed')
			);

			switch ($this->viewedOrder)
			{
				case 'RAND':
					shuffle($hostcmsViewed);
				break;
				case 'ASC':
					ksort($hostcmsViewed);
				break;
				case 'DESC':
					krsort($hostcmsViewed);
				break;
				default:
					throw new Core_Exception("The viewedOrder direction '%direction' doesn't allow",
						array('%direction' => $this->viewedOrder)
					);
			}

			// Delete current item
			if (($currentKey = array_search($this->item, $hostcmsViewed)) !== FALSE)
			{
				unset($hostcmsViewed[$currentKey]);
			}

			// Extract a slice of the array
			$hostcmsViewed = array_slice($hostcmsViewed, 0, $this->viewedLimit);

			foreach ($hostcmsViewed as $view_item_id)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->find($view_item_id, FALSE);

				if (!is_null($oShop_Item->id) /*&& $oShop_Item->id != $this->item*/ && $oShop_Item->active)
				{
					$oViewed_Shop_Item = clone $oShop_Item;
					$oViewed_Shop_Item
						->id($oShop_Item->id)
						->showXmlProperties($this->itemsProperties)
						->showXmlComments($this->comments)
						->showXmlModifications($this->modifications)
						->showXmlBonuses($this->bonuses)
						->showXmlSpecialprices($this->specialprices);

					$this->applyItemsForbiddenTags($oViewed_Shop_Item);

					!$this->sets && $oViewed_Shop_Item->showXmlSets($this->sets);

					Core_Event::notify(get_class($this) . '.onBeforeAddViewedEntity', $this, array($oViewed_Shop_Item));

					$oViewedEntity->addEntity($oViewed_Shop_Item);
				}
			}
		}

		return $this;
	}


	/**
	 * Add into viewed list
	 * @return self
	 */
	public function addIntoViewed()
	{
		if ($this->item)
		{
			$oShop = $this->getEntity();

			if (Core_Entity::factory('Shop_Item', $this->item)->shop_id == $oShop->id)
			{
				Core_Session::start();

				// Добавляем если такой товар еще не был просмотрен
				if (!isset($_SESSION['hostcmsViewed'][$oShop->id])
					|| !in_array($this->item, $_SESSION['hostcmsViewed'][$oShop->id]))
				{
					// Cut array
					if (isset($_SESSION['hostcmsViewed'][$oShop->id])
						&& count($_SESSION['hostcmsViewed'][$oShop->id]) > $this->viewedLimit)
					{
						$_SESSION['hostcmsViewed'][$oShop->id] = array_slice($_SESSION['hostcmsViewed'][$oShop->id], -$this->viewedLimit, $this->viewedLimit);
					}

					$_SESSION['hostcmsViewed'][$oShop->id][] = $this->item;
				}
			}
		}

		return $this;
	}

	public function addIntoViwed()
	{
		return $this->addIntoViewed();
	}

	/**
	 * Check if data is cached
	 * @return NULL|TRUE|FALSE
	 */
	public function inCache()
	{
		if ($this->cache && Core::moduleIsActive('cache'))
		{
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			return $oCore_Cache->check($cacheKey = strval($this), $this->_cacheName);
		}

		return FALSE;
	}

	/**
	 * Set offset and limit
	 * @return self
	 */
	protected function _setLimits()
	{
		// Load model columns BEFORE FOUND_ROWS()
		Core_Entity::factory('Shop_Item')->getTableColumns();

		// Load user BEFORE FOUND_ROWS()
		Core_Auth::getCurrentUser();

		$this->calculateTotal && $this->_Shop_Items
			->queryBuilder()
			->sqlCalcFoundRows();

		$this->_Shop_Items
			->queryBuilder()
			->offset(intval($this->offset))
			->limit(intval($this->limit));

		return $this;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Controller_Show.onBeforeRedeclaredShow
	 * @hostcms-event Shop_Controller_Show.onBeforeAddGroupsPropertiesList
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$this->showPanel && Core::checkPanel() && in_array($this->_mode, array('xsl', 'tpl')) && $this->_showPanel();

		$oShop = $this->getEntity();

		$hasSessionId = Core_Session::hasSessionId();

		// Before check cache
		if ($hasSessionId)
		{
			$isActive = Core_Session::isAcive();
			!$isActive && Core_Session::start();

			if ($this->favorite)
			{
				$hostcmsFavorite = Core_Array::get(Core_Array::getSession('hostcmsFavorite', array()), $oShop->id, array());
				count($hostcmsFavorite) && $this->addCacheSignature('hostcmsFavorite=' . implode(',', $hostcmsFavorite));
			}

			if (isset($_SESSION['hostcmsOrder']['coupon_text']))
			{
				$this->addCacheSignature('coupon=' . $_SESSION['hostcmsOrder']['coupon_text']);
				Shop_Item_Controller::coupon($_SESSION['hostcmsOrder']['coupon_text']);
			}
		}

		$this->item && $this->_incShowed();

		$bCache = $this->cache && Core::moduleIsActive('cache');
		if ($bCache)
		{
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$inCache = $oCore_Cache->get($cacheKey = strval($this), $this->_cacheName);

			if (is_array($inCache))
			{
				$this->_shownIDs = $inCache['shown'];
				echo $inCache['content'];
				return $this;
			}

			$aTags = array();
			$aTags[] = 'shop_group_' . intval($this->group);
		}

		$bTpl = $this->_mode == 'tpl';

		$oShop->showXmlCounts($this->calculateCounts);

		$this->taxes && $oShop->showXmlTaxes(TRUE);

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('group')
				->value(intval($this->group)) // FALSE => 0
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('page')
				->value(intval($this->page))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		!is_null($this->producer) && $this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('producer_id')
				->value(intval($this->producer))
		);

		// Comparing, favorite and viewed goods
		if ($hasSessionId)
		{
			// Comparing goods
			$this->comparing && $this->_addComparing();

			// Favorite goods
			$this->favorite && $this->_addFavorite();

			// Viewed goods
			$this->viewed && $this->_addViewed();

			// Товары в корзине
			if ($this->cart)
			{
				// Проверяем наличие товара в корзины
				$Shop_Cart_Controller = Shop_Cart_Controller::instance();
				$aShop_Cart = $Shop_Cart_Controller->getAll($oShop);

				if (count($aShop_Cart))
				{
					$this->addEntity(
						$oCartEntity = Core::factory('Core_Xml_Entity')
							->name('items_in_cart')
					);

					foreach ($aShop_Cart as $oShop_Cart)
					{
						$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Cart->shop_item_id);
						if (!is_null($oShop_Item->id) && $oShop_Item->active)
						{
							$this->applyItemsForbiddenTags($oShop_Item->clearEntities());

							$this->itemsProperties && $oShop_Item->showXmlProperties($this->itemsProperties);
							!$this->sets && $oShop_Item->showXmlSets($this->sets);

							$oCartEntity->addEntity($oShop_Item);
						}
					}
				}
			}

			!$isActive && Core_Session::close();
		}

		$this->_shownIDs = array();

		if ($bTpl)
		{
			$this->assign('controller', $this);
			$this->assign('aShop_Items', array());
		}

		if ($this->limit == 0 && $this->page)
		{
			return $this->error404();
		}

		// Независимо от limit, т.к. может использоваться отдельно для фильтра
		if (!$this->item)
		{
			$this->applyFilter();

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('filter_path')
					->value($this->_filterPath)
			);
		}

		// До вывода свойств групп
		if ($this->limit > 0 || $this->item)
		{
			$this->applyItemCondition();

			// Group condition for shop item
			$this->group !== FALSE && $this->applyGroupCondition();

			!$this->item && $this->_setLimits();

			// Apply $this->warehouseMode
			$this->_applyWarehouseConditions($this->_Shop_Items);

			$aShop_Items = $this->_Shop_Items->findAll();

			if (!$this->item)
			{
				if ($this->page && !count($aShop_Items))
				{
					return $this->error404();
				}

				if ($this->calculateTotal)
				{
					$row = Core_QueryBuilder::select(array('FOUND_ROWS()', 'count'))->execute()->asAssoc()->current();
					$this->total = $row['count'];

					$this->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('total')
							->value(intval($this->total))
					);
				}
			}
		}

		// Показывать дополнительные свойства групп
		if ($this->groupsProperties && $this->groupsPropertiesList)
		{
			$oShop_Group_Property_List = Core_Entity::factory('Shop_Group_Property_List', $oShop->id);

			$oProperties = $oShop_Group_Property_List->Properties;
			if (is_array($this->groupsPropertiesList) && count($this->groupsPropertiesList))
			{
				$oProperties->queryBuilder()
					->where('properties.id', 'IN', $this->groupsPropertiesList);
			}
			$aProperties = $oProperties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$this->_aGroup_Properties[$oProperty->property_dir_id][] = $oProperty;
			}

			$aProperty_Dirs = $oShop_Group_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$this->_aGroup_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir->clearEntities();
			}

			if (!$bTpl)
			{
				$Shop_Group_Properties = Core::factory('Core_Xml_Entity')
					->name('shop_group_properties');

				$this->addEntity($Shop_Group_Properties);
				
				Core_Event::notify(get_class($this) . '.onBeforeAddGroupsPropertiesList', $this, array($Shop_Group_Properties));

				$this->_addGroupsPropertiesList(0, $Shop_Group_Properties);
			}
		}

		is_array($this->groupsProperties) && $this->groupsProperties = array_combine($this->groupsProperties, $this->groupsProperties);

		// Устанавливаем активность групп
		$this->_setGroupsActivity();

		// Группы магазина
		switch ($this->groupsMode)
		{
			case 'none':
			break;
			// По одной группе от корня до текущего раздела, все потомки текущего раздела
			case 'tree':
				$this->addTreeGroups();
			break;
			// Все группы
			case 'all':
				$this->addAllGroups();
			break;
			default:
				throw new Core_Exception('Group mode "%groupsMode" does not allow', array('%groupsMode' => $this->groupsMode));
			break;
		}

		// Показывать дополнительные свойства товара
		if ($this->itemsProperties || $this->itemsPropertiesList)
		{
			$aShowPropertyIDs = $this->_itemsProperties();
		}

		if ($this->limit > 0)
		{
			if ($this->itemsProperties)
			{
				// Показываются свойства, явно указанные пользователем в itemsProperties и разрешенные для товаров
				/*$mShowPropertyIDs = count($aShowPropertyIDs)
					? (array_merge(is_array($this->itemsProperties) ? $this->itemsProperties : array(), $aShowPropertyIDs))
					: $this->itemsProperties;*/

				$mShowPropertyIDs = is_array($this->itemsProperties)
					? $this->itemsProperties
					: $aShowPropertyIDs;

				is_array($mShowPropertyIDs) && !count($mShowPropertyIDs) && $mShowPropertyIDs = FALSE;
			}
			else
			{
				$mShowPropertyIDs = FALSE;
			}

			// Ярлык может ссылаться на товар с истекшим или не наступившим сроком публикации
			$iCurrentTimestamp = time();

			foreach ($aShop_Items as $oShop_Item)
			{
				$this->_shownIDs[] = $oShop_Item->id;

				// Tagged cache
				$bCache && $aTags[] = 'shop_item_' . $oShop_Item->id;

				// Shortcut
				$iShortcut = $oShop_Item->shortcut_id;

				if ($iShortcut)
				{
					$oShortcut_Item = $oShop_Item;
					$oShop_Item = $oShop_Item->Shop_Item;
				}

				$oShop_Item->clearEntities();

				if (!$bTpl)
				{
					// Ярлык может ссылаться на отключенный товар
					$desiredActivity = strtolower($this->itemsActivity) == 'active'
						? 1
						: (strtolower($this->itemsActivity) == 'all' ? $oShop_Item->active : 0);

					if ($oShop_Item->id // Can be shortcut on markDeleted item
						&& $oShop_Item->active == $desiredActivity
						&& (!$iShortcut
							|| (Core_Date::sql2timestamp($oShop_Item->end_datetime) >= $iCurrentTimestamp
								|| $oShop_Item->end_datetime == '0000-00-00 00:00:00')
							&& (Core_Date::sql2timestamp($oShop_Item->start_datetime) <= $iCurrentTimestamp
								|| $oShop_Item->start_datetime == '0000-00-00 00:00:00')
						)
					)
					{
						// ID оригинального ярлыка
						if ($iShortcut)
						{
							$oOriginal_Shop_Item = $oShop_Item;

							$oShop_Item = clone $oShop_Item;
							$oShop_Item
								->id($oOriginal_Shop_Item->id)
								->addForbiddenTag('shortcut_id')
								->addForbiddenTag('shop_group_id')
								->addEntity(
									Core::factory('Core_Xml_Entity')
										->name('shortcut_id')
										->value($oShortcut_Item->id)
								)
								->addEntity(
									Core::factory('Core_Xml_Entity')
										->name('shop_group_id')
										->value($oShortcut_Item->shop_group_id)
								);
						}

						$this->applyItemsForbiddenTags($oShop_Item);

						// Comments
						$oShop_Item
							->showXmlComments($this->comments)
							->commentsActivity($this->commentsActivity);

						$oShop_Item->showXmlBonuses($this->bonuses);
						$oShop_Item->showXmlBarcodes($this->barcodes);
						$oShop_Item->showXmlWarehousesItems($this->warehousesItems);
						$oShop_Item->showXmlAssociatedItems($this->associatedItems);
						$oShop_Item->showXmlModifications($this->modifications);
						$oShop_Item->showXmlSpecialprices($this->specialprices);
						$oShop_Item->showXmlTags($this->tags);
						$oShop_Item->showXmlVotes($this->votes);

						$oShop_Item->showXmlProperties($mShowPropertyIDs);
						!$this->sets && $oShop_Item->showXmlSets($this->sets);

						// Siteuser
						$oShop_Item->showXmlSiteuser($this->siteuser)
							->showXmlSiteuserProperties($this->siteuserProperties);

						$this->addEntity($oShop_Item);

						// Parent item for modification
						$this->parentItem && $oShop_Item->addEntity(
							Core_Entity::factory('Shop_Item', $this->parentItem)
								->showXmlProperties($mShowPropertyIDs)
								->showXmlTags($this->tags)
								->showXmlWarehousesItems($this->warehousesItems)
								->showXmlAssociatedItems($this->associatedItems)
								->showXmlModifications($this->modifications)
								->showXmlSpecialprices($this->specialprices)
								->showXmlVotes($this->votes)
								->showXmlSets($this->sets)
						);
					}
				}
				else
				{
					$this->append('aShop_Items', $oShop_Item);
				}
			}
		}

		echo $content = $this->get();

		$bCache && $oCore_Cache->set(
			$cacheKey,
			array('content' => $content, 'shown' => $this->_shownIDs),
			$this->_cacheName,
			$aTags
		);

		// Clear
		$this->_aShop_Groups = $this->_aItem_Property_Dirs = $this->_aItem_Properties
			= $this->_aGroup_Properties = $this->_aGroup_Property_Dirs = array();

		return $this;
	}

	/**
	 * Add list of item properties
	 * @return array
	 * @hostcms-event Shop_Controller_Show.onBeforeAddItemsPropertiesList
	 */
	protected function _itemsProperties()
	{
		$aShowPropertyIDs = array();

		$oShop = $this->getEntity();

		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

		$bTpl = $this->_mode == 'tpl';

		//if ($this->itemsProperties)
		//{
			$aProperties = $this->group === FALSE
				? (is_array($this->itemsPropertiesList) && count($this->itemsPropertiesList)
					? $oShop_Item_Property_List->Properties->getAllByid($this->itemsPropertiesList, FALSE, 'IN')
					: $oShop_Item_Property_List->Properties->findAll()
				)
				: $oShop_Item_Property_List->getPropertiesForGroup($this->group, $this->itemsPropertiesList);

			foreach ($aProperties as $oProperty)
			{
				$oShop_Item_Property = $oProperty->Shop_Item_Property;

				if ($oShop_Item_Property->show_in_item && $this->item
					|| $oShop_Item_Property->show_in_group && !$this->item)
				{
					// Используется ниже для ограничение показа значений св-в товара в модели
					$aShowPropertyIDs[] = $oProperty->id;
				}

				$this->_aItem_Properties[$oProperty->property_dir_id][] = $oProperty->clearEntities();

				if (!$bTpl)
				{
					$oProperty->addEntity(
						Core::factory('Core_Xml_Entity')->name('prefix')->value($oShop_Item_Property->prefix)
					)
					->addEntity(
						Core::factory('Core_Xml_Entity')->name('filter')->value($oShop_Item_Property->filter)
					)
					->addEntity(
						Core::factory('Core_Xml_Entity')->name('show_in_group')->value($oShop_Item_Property->show_in_group)
					)
					->addEntity(
						Core::factory('Core_Xml_Entity')->name('show_in_item')->value($oShop_Item_Property->show_in_item)
					);

					$oShop_Item_Property->shop_measure_id && $oProperty->addEntity(
						$oShop_Item_Property->Shop_Measure
					);

					if ($oShop->filter && in_array($oShop_Item_Property->filter, array(2, 3, 4, 7)))
					{
						$tableName = 'shop_filter' . $oShop->id;

						$columnName = 'property' . $oProperty->id;

						$oQueryBuilder = Core_QueryBuilder::select()
							->clearSelect()
							->select(array(Core_QueryBuilder::expression('COUNT(DISTINCT shop_item_id)'), 'count'), $tableName . '.' . $columnName)
							->from($tableName)
							->groupBy($tableName . '.' . $columnName);

						$this->group !== FALSE
							&& $oQueryBuilder->where($tableName . '.shop_group_id', '=', $this->group);

						$this->producer
							&& $oQueryBuilder->where($tableName . '.shop_producer_id', '=', $this->producer);

						!$this->modificationsList
							&& $oQueryBuilder->where($tableName . '.modification_id', '=', 0);

						// Filter by properties
						$this->applyFastFilterProperties($oQueryBuilder);

						// Filter by prices
						$this->applyFastFilterPrices($oQueryBuilder);

						$aRows = $oQueryBuilder->asAssoc()->execute()->result();

						// XML-сущность, к которой будет добавляться количество
						$oFilterCountsXmlEntity = Core::factory('Core_Xml_Entity')->name('filter_counts');

						// Добавляем XML-сущность контроллеру показа
						$oProperty->addEntity($oFilterCountsXmlEntity);

						foreach ($aRows as $aRow)
						{
							$oFilterCountsXmlEntity->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('count')->value($aRow['count'])->addAttribute('id', $aRow[$columnName])
							);
						}
					}
				}
			}
		//}

		// Список свойств товаров
		if ($this->itemsPropertiesList)
		{
			$aProperty_Dirs = $oShop_Item_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aItem_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			if (!$bTpl)
			{
				$Shop_Item_Properties = Core::factory('Core_Xml_Entity')
					->name('shop_item_properties');

				$this->addEntity($Shop_Item_Properties);
				
				Core_Event::notify(get_class($this) . '.onBeforeAddItemsPropertiesList', $this, array($Shop_Item_Properties));

				$this->_addItemsPropertiesList(0, $Shop_Item_Properties);
			}
		}

		return $aShowPropertyIDs;
	}

	/**
	 * Inc Shop_Item->showed
	 * @return self
	 */
	protected function _incShowed()
	{
		Core_QueryBuilder::update('shop_items')
			->set('showed', Core_QueryBuilder::expression('`showed` + 1'))
			->where('id', '=', $this->item)
			->execute();

		return $this;
	}

	/**
	 * Apply item's conditions
	 * @return self
	 */
	public function applyItemCondition()
	{
		return $this->_itemCondition();
	}

	/**
	 * Backward compatible. Apply item's conditions.
	 * @return self
	 */
	protected function _itemCondition()
	{
		// Товары
		if ($this->item)
		{
			$this->_Shop_Items
				->queryBuilder()
				->where('shop_items.id', '=', intval($this->item));
		}
		elseif (!is_null($this->tag))
		{
			if (Core::moduleIsActive('tag'))
			{
				$oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

				if ($oTag)
				{
					$this->addEntity($oTag);

					$this->_Shop_Items
						->queryBuilder()
						->leftJoin('tag_shop_items', 'shop_items.id', '=', 'tag_shop_items.shop_item_id')
						->where('tag_shop_items.tag_id', '=', $oTag->id);

					// В корне при фильтрации по меткам вывод идет из всех групп
					$this->group == 0 && $this->group = FALSE;
				}
			}
		}
		elseif (!is_null($this->producer))
		{
			$oShop_Producer = Core_Entity::factory('Shop_Producer', $this->producer);

			$this->addEntity($oShop_Producer);

			$this->_Shop_Items
				->queryBuilder()
				->where('shop_items.shop_producer_id', '=', $this->producer);

			// В корне при фильтрации по производителям вывод идет из всех групп
			$this->group == 0 && $this->group = FALSE;
		}

		$this->_setItemsActivity();

		return $this;
	}

	/**
	 * Disable shortcuts
	 * @return self
	 */
	protected function forbidSelectShortcuts()
	{
		// Отключаем выбор ярлыков из текущей группы
		$this->_Shop_Items
			->queryBuilder()
			->where('shop_items.shortcut_id', '=', 0);

		return $this;
	}

	/**
	 * External forbids to select modifications. Do not execute with ->modificationsList(TRUE)
	 * @return self
	 */
	public function forbidSelectModifications()
	{
		$this->_Shop_Items
			->queryBuilder()
			->where('shop_items.modification_id', '=', 0);

		return $this;
	}

	/**
	 * Apply item's condition by shop_group_id
	 * @return self
	 */
	public function applyGroupCondition()
	{
		return $this->_groupCondition();
	}

	/**
	 * Backward compatible. Apply item's condition by shop_group_id
	 * @return self
	 * @hostcms-event Shop_Controller_Show.onBeforeSelectModifications
	 */
	protected function _groupCondition()
	{
		$oShop = $this->getEntity();

		$shop_group_id = !$this->parentItem
			? intval($this->group)
			: 0;

		$this->_Shop_Items
			->queryBuilder()
			->open()
			->where('shop_items.shop_group_id', '=', $shop_group_id);

		// Отключаем выбор ярлыков
		$this->filterShortcuts && $this->forbidSelectShortcuts();

		// Отключаем выбор модификаций
		!$this->_selectModifications && $this->forbidSelectModifications();

		// Вывод модификаций на одном уровне в списке товаров
		if (!$this->item && $this->modificationsList)
		{
			$oCore_QueryBuilder_Select_Modifications = Core_QueryBuilder::select('shop_items.id')
				->from('shop_items')
				->where('shop_items.shop_id', '=', $oShop->id)
				->where('shop_items.deleted', '=', 0)
				->where('shop_items.active', '=', 1)
				->where('shop_items.shop_group_id', '=', $shop_group_id);

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Modifications);

			Core_Event::notify(get_class($this) . '.onBeforeSelectModifications', $this, array($oCore_QueryBuilder_Select_Modifications));

			$this->_Shop_Items
				->queryBuilder()
				->setOr()
				->where('shop_items.shop_group_id', '=', 0)
				->where('shop_items.modification_id', 'IN', $oCore_QueryBuilder_Select_Modifications);

			// Совместное modificationsList + filterShortcuts
			if ($this->filterShortcuts)
			{
				$oCore_QueryBuilder_Select_Shortcuts_For_Modifications = Core_QueryBuilder::select('shop_items.shortcut_id')
					->from('shop_items')
					->where('shop_items.shop_id', '=', $oShop->id)
					->where('shop_items.deleted', '=', 0)
					->where('shop_items.active', '=', 1)
					->where('shop_items.shop_group_id', '=', $shop_group_id)
					->where('shop_items.shortcut_id', '>', 0);

				$this->_Shop_Items
					->queryBuilder()
					->setOr()
					->where('shop_items.shop_group_id', '=', 0)
					->where('shop_items.modification_id', 'IN', $oCore_QueryBuilder_Select_Shortcuts_For_Modifications);
			}
		}

		if ($this->filterShortcuts)
		{
			$oCore_QueryBuilder_Select_Shortcuts = Core_QueryBuilder::select('shop_items.shortcut_id')
				->from('shop_items')
				->where('shop_items.deleted', '=', 0)
				->where('shop_items.active', '=', 1)
				->where('shop_items.shop_group_id', '=', $shop_group_id)
				->where('shop_items.shortcut_id', '>', 0);

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

			$this->_Shop_Items
				->queryBuilder()
				->setOr()
				->where('shop_items.id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);
		}

		$this->_Shop_Items
			->queryBuilder()
			->close();

		return $this;
	}

	protected $_seoGroupTitle = NULL;
	protected $_seoGroupDescription = NULL;
	protected $_seoGroupKeywords = NULL;

	protected $_seoItemTitle = NULL;
	protected $_seoItemDescription = NULL;
	protected $_seoItemKeywords = NULL;

	protected $_filterPath = NULL;

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

		// Group: set shop's SEO templates
		$oShop->seo_group_title_template != ''
			&& $this->_seoGroupTitle = $oShop->seo_group_title_template;
		$oShop->seo_group_description_template != ''
			&& $this->_seoGroupDescription = $oShop->seo_group_description_template;
		$oShop->seo_group_keywords_template != ''
			&& $this->_seoGroupKeywords = $oShop->seo_group_keywords_template;

		// Item: set shop's SEO templates
		$oShop->seo_item_title_template != ''
			&& $this->_seoItemTitle = $oShop->seo_item_title_template;
		$oShop->seo_item_description_template != ''
			&& $this->_seoItemDescription = $oShop->seo_item_description_template;
		$oShop->seo_item_keywords_template != ''
			&& $this->_seoItemKeywords = $oShop->seo_item_keywords_template;

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

		if (isset($matches['tag']) && $matches['tag'] != '' && Core::moduleIsActive('tag'))
		{
			$this->tag($matches['tag']);

			$oTag = Core_Entity::factory('Tag')->getByPath($this->tag);
			if (is_null($oTag))
			{
				return $this->error404();
			}
		}

		if (isset($matches['producer']) && $matches['producer'] != '')
		{
			$this->producer($matches['producer']);

			$oShop_Producer = Core_Entity::factory('Shop_Producer')->find($this->producer);
			if (is_null($oShop_Producer->id))
			{
				return $this->error404();
			}
		}

		// Cookie для аффилиат-программы
		if (isset($matches['user']))
		{
			setcookie('affiliate_name', $matches['user'], time() + 31536000, '/');
		}

		$path = isset($matches['path'])
			? Core_Str::ltrimUri($matches['path'])
			: NULL;

		$this->group = 0;

		if ($path != '')
		{
			$oProperty = NULL;
			$aPropertyValuesToSet = array();

			$step = 'group';
			$aPath = explode('/', $path);
			$iPathCount = count($aPath);

			foreach ($aPath as $key => $sPath)
			{
				// Attempt to receive Shop_Group
				switch ($step)
				{
					case 'group':
						$oShop_Groups = $oShop->Shop_Groups;

						$this->groupsActivity = strtolower($this->groupsActivity);
						if ($this->groupsActivity != 'all')
						{
							$oShop_Groups
								->queryBuilder()
								->where('shortcut_id', '=', 0)
								->where('active', '=', $this->groupsActivity == 'inactive' ? 0 : 1);
						}

						$oShop_Group = $oShop_Groups->getByParentIdAndPath($this->group, $sPath);

						if (!is_null($oShop_Group))
						{
							if (in_array($oShop_Group->getSiteuserGroupId(), $this->_aSiteuserGroups))
							{
								$this->group = $oShop_Group->id;

								// Group: set shop's SEO templates
								$oShop_Group->seo_group_title_template != ''
									&& $this->_seoGroupTitle = $oShop_Group->seo_group_title_template;
								$oShop_Group->seo_group_description_template != ''
									&& $this->_seoGroupDescription = $oShop_Group->seo_group_description_template;
								$oShop_Group->seo_group_keywords_template != ''
									&& $this->_seoGroupKeywords = $oShop_Group->seo_group_keywords_template;

								// Item: set shop's SEO templates
								$oShop_Group->seo_item_title_template != ''
									&& $this->_seoItemTitle = $oShop_Group->seo_item_title_template;
								$oShop_Group->seo_item_description_template != ''
									&& $this->_seoItemDescription = $oShop_Group->seo_item_description_template;
								$oShop_Group->seo_item_keywords_template != ''
									&& $this->_seoItemKeywords = $oShop_Group->seo_item_keywords_template;
							}
							else
							{
								return $this->error403();
							}

							break;
						}
						else
						{
							$step = 'producer';
						}

					case 'producer':
						$Shop_Producer = $oShop->Shop_Producers->getByName($sPath);

						$step = 'price';

						if (!is_null($Shop_Producer))
						{
							$this->_filterPath .= $sPath . '/';

							$this->producer = $Shop_Producer->id;
							break;
						}
					case 'price':
						$step = 'filter';

						if (strpos($sPath, 'price-') === 0)
						{
							$aPrice = explode('-', substr($sPath, 6), 2);

							if (count($aPrice) == 2 && is_numeric($aPrice[0]) && is_numeric($aPrice[1]))
							{
								$this
									->addFilter('price', '>=', $aPrice[0])
									->addFilter('price', '<=', $aPrice[1]);
							}
							else
							{
								return $this->error404();
							}

							$this->_filterPath .= $sPath . '/';

							break;
						}
					case 'filterValue':
						if (!is_null($oProperty))
						{
							if ($oProperty->type == 3)
							{
								$oList_Item = $oProperty->List->List_Items->getByValue($sPath);

								if (!is_null($oList_Item))
								{
									$this->_filterPath .= $sPath . '/';

									$aPropertyValuesToSet[] = $oList_Item->id;

									// Последние условие выполняется сразу, а не на следующем шаге
									if ($key + 1 == $iPathCount)
									{
										$step = 'finish';
									}
									else
									{
										break;
									}
								}
								else
								{
									$step = 'filter';
								}
							}
							else
							{
								$this->_filterPath .= $sPath . '/';

								$aPropertyValuesToSet[] = $sPath;
								$step = 'filter';

								if ($key + 1 == $iPathCount)
								{
									$step = 'finish';
								}
								else
								{
									break;
								}
							}
						}
					case 'filter':
						if (!is_null($oProperty))
						{
							if (count($aPropertyValuesToSet))
							{
								$this->addFilter('property', $oProperty->id,
									count($aPropertyValuesToSet) == 1 ? '=' : 'IN',
									count($aPropertyValuesToSet) == 1 ? $aPropertyValuesToSet[0] : $aPropertyValuesToSet
								);

								$aPropertyValuesToSet = array();
								$oProperty = NULL;

								if ($step === 'finish')
								{
									break;
								}
							}
							else
							{
								return $this->error404();
							}
						}

						$aFilterProperties = $this->getFilterPropertiesByGroup($this->group);

						if (isset($aFilterProperties[$sPath]))
						{
							$aAvailablePropertyTypes = array(0, 1, 3, 7, 11);

							$oProperty = $aFilterProperties[$sPath];

							$this->_filterPath .= $sPath . '/';

							if (in_array($oProperty->type, $aAvailablePropertyTypes))
							{
								$oShop_Item_Property = $oProperty->Shop_Item_Property;

								// Checkbox
								if ($oProperty->type == 7)
								{
									$this->addFilter('property', $oProperty->id, '=', 1);
									$oProperty = NULL;
								}
								elseif ($oShop_Item_Property->filter != 5 && $oShop_Item_Property->filter != 6)
								{
									$step = 'filterValue';
									$aPropertyValuesToSet = array();
								}
								else
								{
									return $this->error404();
								}
							}
							break;
						}
						// Свойство от-до
						elseif (($pos = strpos($sPath, '-')) !== FALSE)
						{
							$tagName = substr($sPath, 0, $pos);

							if (isset($aFilterProperties[$tagName]))
							{
								$oProperty = $aFilterProperties[$tagName];

								$this->_filterPath .= $sPath . '/';

								$aValue = explode('-', substr($sPath, $pos + 1), 2);

								if (count($aValue) == 2 && is_numeric($aValue[0]) && is_numeric($aValue[1]))
								{
									$this
										->addFilter('property', $oProperty->id, '>=', $aValue[0])
										->addFilter('property', $oProperty->id, '<=', $aValue[1]);
								}
								else
								{
									return $this->error404();
								}

								break;
							}
							else
							{
								$step = 'item';
							}
						}
						else
						{
							$step = 'item';
						}

					case 'item':
						// Attempt to receive Shop_Item
						$oShop_Items = $oShop->Shop_Items;

						$this->itemsActivity = strtolower($this->itemsActivity);
						if ($this->itemsActivity != 'all')
						{
							$oShop_Items
								->queryBuilder()
								->where('shop_items.active', '=', $this->itemsActivity == 'inactive' ? 0 : 1);
						}

						$this->_applyItemConditions($oShop_Items);
						$this->_applyWarehouseConditions($oShop_Items);

						//$this->forbidSelectModifications();
						$oShop_Items->queryBuilder()->where('shop_items.modification_id', '=', 0);

						$oShop_Item = $oShop_Items->getByGroupIdAndPath($this->group, $sPath);

						$step = 'modification';

						if (!is_null($oShop_Item))
						{
							if (in_array($oShop_Item->getSiteuserGroupId(), $this->_aSiteuserGroups))
							{
								$this->group = $oShop_Item->shop_group_id;
								$this->item = $oShop_Item->id;

								break;
							}
							else
							{
								return $this->error403();
							}
						}

					case 'modification':
						// Товар был уже определен, по пути ищем модификацию
						if ($this->item)
						{
							$oShop_Modification_Items = $oShop->Shop_Items;
							$oShop_Modification_Items
								->queryBuilder()
								->where('active', '=', 1)
								->where('shop_items.modification_id', '=', $this->item);

							$oShop_Modification_Item = $oShop_Modification_Items->getByGroupIdAndPath(0, $sPath);

							$step = 'nothing';

							if (!is_null($oShop_Modification_Item))
							{
								// Родительский товар для модификации
								$this->parentItem = $this->item;

								// Модификация в основной товар
								$this->item = $oShop_Modification_Item->id;

								break;
							}
							else
							{
								$this->group = FALSE;
								$this->item = FALSE;
								return $this->error404();
							}
						}
						else
						{
							$this->group = FALSE;
							return $this->error404();
						}
					break;
					case 'finish':
						// Nothing to do
					break;
					default:
						$this->group = FALSE;
						$this->item = FALSE;
						return $this->error404();
				}
			}
		}
		elseif (is_null($path))
		{
			return $this->error404();
		}

		// Ограничение на список товаров
		//!$this->item && is_null($this->tag) && $this->forbidSelectModifications();
		!$this->item && is_null($this->tag) && $this->_selectModifications = FALSE;

		$seo_title = $seo_description = $seo_keywords = NULL;

		// Apply SEO templates
		if ($this->item)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item', $this->item);

			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('shop', $oShop)
				->addObject('group', $oShop_Item->Shop_Group)
				->addObject('item', $oShop_Item)
				->addObject('this', $this);

			// Title
			if ($oShop_Item->seo_title != '')
			{
				$seo_title = $oShop_Item->seo_title;
			}
			elseif ($this->_seoItemTitle != '')
			{
				$seo_title = $oCore_Meta->apply($this->_seoItemTitle);
			}
			else
			{
				$seo_title = $oShop_Item->name;
			}

			// Description
			if ($oShop_Item->seo_description != '')
			{
				$seo_description = $oShop_Item->seo_description;
			}
			elseif ($this->_seoItemDescription != '')
			{
				$seo_description = $oCore_Meta->apply($this->_seoItemDescription);
			}
			else
			{
				$seo_description = $oShop_Item->name;
			}

			// Keywords
			if ($oShop_Item->seo_keywords != '')
			{
				$seo_keywords = $oShop_Item->seo_keywords ;
			}
			elseif ($this->_seoItemKeywords != '')
			{
				$seo_keywords = $oCore_Meta->apply($this->_seoItemKeywords);
			}
			else
			{
				$seo_keywords = $oShop_Item->name;
			}
		}
		elseif ($this->group)
		{
			$oShop_Group = Core_Entity::factory('Shop_Group', $this->group);

			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('shop', $oShop)
				->addObject('group', $oShop_Group)
				->addObject('this', $this);

			// Title
			if ($oShop_Group->seo_title != '')
			{
				$seo_title = $oShop_Group->seo_title;
			}
			elseif ($this->_seoGroupTitle != '')
			{
				$seo_title = $oCore_Meta->apply($this->_seoGroupTitle);
			}
			else
			{
				$seo_title = $oShop_Group->name;
			}

			// Description
			if ($oShop_Group->seo_description != '')
			{
				$seo_description = $oShop_Group->seo_description;
			}
			elseif ($this->_seoGroupDescription != '')
			{
				$seo_description = $oCore_Meta->apply($this->_seoGroupDescription);
			}
			else
			{
				$seo_description = $oShop_Group->name;
			}

			// Keywords
			if ($oShop_Group->seo_keywords != '')
			{
				$seo_keywords = $oShop_Group->seo_keywords ;
			}
			elseif ($this->_seoGroupKeywords != '')
			{
				$seo_keywords = $oCore_Meta->apply($this->_seoGroupKeywords);
			}
			else
			{
				$seo_keywords = $oShop_Group->name;
			}
		}
		elseif (!is_null($this->tag) && Core::moduleIsActive('tag'))
		{
			$seo_title = $oTag->seo_title != ''
				? $oTag->seo_title
				: Core::_('Shop.tag', $oTag->name);

			$seo_description = $oTag->seo_description != ''
				? $oTag->seo_description
				: $oTag->name;

			$seo_keywords = $oTag->seo_keywords != ''
				? $oTag->seo_keywords
				: $oTag->name;
		}
		elseif (!is_null($this->producer))
		{
			$oShop_Producer = Core_Entity::factory('Shop_Producer', $this->producer);

			$seo_title = $oShop_Producer->seo_title != ''
				? $oShop_Producer->seo_title
				: $oShop_Producer->name;

			$seo_description = $oShop_Producer->seo_description != ''
				? $oShop_Producer->seo_description
				: $oShop_Producer->name;

			$seo_keywords = $oShop_Producer->seo_keywords != ''
				? $oShop_Producer->seo_keywords
				: $oShop_Producer->name;
		}

		$seo_title != '' && Core_Page::instance()->title($seo_title);
		$seo_description != '' && Core_Page::instance()->description($seo_description);
		$seo_keywords != '' && Core_Page::instance()->keywords($seo_keywords);

		// var_dump($this->_filterPath);

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

		return $this;
	}

	/**
	 * Cache for getFilterProperties
	 * @var array
	 */
	protected $_cacheGetFilterPropertiesByGroup = array();

	/**
	 * Get array of properties available for filter
	 * @param int $group_id
	 * @return array
	 */
	public function getFilterPropertiesByGroup($group_id)
	{
		if (!isset($this->_cacheGetFilterPropertiesByGroup[$group_id]))
		{
			$this->_cacheGetFilterPropertiesByGroup[$group_id] = array();

			$oShop = $this->getEntity();

			$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

			$aProperties = $group_id === FALSE
				? $oShop_Item_Property_List->Properties->findAll()
				: $oShop_Item_Property_List->getPropertiesForGroup($group_id);

			foreach ($aProperties as $oProperty)
			{
				$oShop_Item_Property = $oProperty->Shop_Item_Property;

				if ($oShop_Item_Property->filter)
				{
					$this->_cacheGetFilterPropertiesByGroup[$group_id][$oProperty->tag_name] = $oProperty;
				}
			}
		}

		return $this->_cacheGetFilterPropertiesByGroup[$group_id];
	}

	/**
	 * Get page number with template $template
	 * @param $template template, e.g. ", page %d"
	 * @return string
	 */
	public function pageNumber($template = "%d")
	{
		return $this->page > 0
			? sprintf($template, $this->page + 1)
			: '';
	}

	/**
	 * Get producer name
	 * @return string
	 */
	public function filterProducer()
	{
		return $this->producer
			? Core_Entity::factory('Shop_Producer', $this->producer)->name
			: '';
	}

	/**
	 * Get properties for seo fields
	 * @param $nameSeparator property name separator
	 * @param $valueSeparator property value separator
	 * @return string
	 */
	public function seoFilter($nameSeparator = ": ", $valueSeparator = ", ")
	{
		$aReturn = array();

		foreach ($this->_aFilterProperties as $property_id => $aTmpProperties)
		{
			foreach ($aTmpProperties as $aTmpProperty)
			{
				list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;

				$line = ' ' . $oProperty->name . $nameSeparator;

				foreach ($aPropertyValues as $propertyValue)
				{
					if ($oProperty->type == 3)
					{
						$oList_Item = $oProperty->List->List_Items->getById($propertyValue);

						if (!is_null($oList_Item))
						{
							$line .= $oList_Item->value;
						}
					}
					else
					{
						$line .= $propertyValue;
					}

					$aReturn[] = $line;
				}
			}
		}

		return implode($valueSeparator, $aReturn);
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

	/**
	 * Define handler for 403 error
	 * @return self
	 */
	public function error403()
	{
		Core_Page::instance()->error403();

		return $this;
	}

	/**
	 * Apply forbidden xml tags for groups
	 * @param Shop_Group_Model $oShop_Group group
	 * @return self
	 */
	public function applyGroupsForbiddenTags($oShop_Group)
	{
		if (!is_null($this->groupsForbiddenTags))
		{
			foreach ($this->groupsForbiddenTags as $forbiddenTag)
			{
				$oShop_Group->addForbiddenTag($forbiddenTag);
			}
		}

		return $this;
	}

	/**
	 * Apply forbidden xml tags for items
	 * @param Shop_Item_Model $oShop_Item item
	 * @return self
	 */
	public function applyItemsForbiddenTags($oShop_Item)
	{
		if (!is_null($this->itemsForbiddenTags))
		{
			foreach ($this->itemsForbiddenTags as $forbiddenTag)
			{
				$oShop_Item->addForbiddenTag($forbiddenTag);
			}
		}

		return $this;
	}

	/**
	 * Add all groups to XML
	 * @return self
	 */
	public function addAllGroups()
	{
		$this->_aShop_Groups = array();

		$aShop_Groups = $this->_Shop_Groups->findAll();

		foreach ($aShop_Groups as $oShop_Group)
		{
			$oShop_Group->clearEntities();
			$this->applyGroupsForbiddenTags($oShop_Group);

			// Shortcut
			if ($oShop_Group->shortcut_id)
			{
				$oShortcut_Group = $oShop_Group;
				$oShop_Group = clone $oShop_Group->Shortcut;

				$oShop_Group
					->id($oShortcut_Group->id)
					->addForbiddenTag('parent_id')
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('original_group_id')
							->value($oShortcut_Group->Shortcut->id)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('parent_id')
							->value($oShortcut_Group->parent_id)
					);
			}

			$this->_aShop_Groups[$oShop_Group->parent_id][] = $oShop_Group;
		}

		$this->_addGroupsByParentId(0, $this);

		return $this;
	}

	/**
	 * Add tree groups to XML
	 * @return self
	 */
	public function addTreeGroups()
	{
		$this->_aShop_Groups = array();

		$group_id = !$this->parentItem
			? $this->group
			: Core_Entity::factory('Shop_Item', $this->parentItem)->shop_group_id;

		// Потомки текущего уровня
		$aShop_Groups = $this->_Shop_Groups->getByParentId($group_id);

		foreach ($aShop_Groups as $oShop_Group)
		{
			$oShop_Group->clearEntities();
			$this->applyGroupsForbiddenTags($oShop_Group);

			// Shortcut
			if ($oShop_Group->shortcut_id)
			{
				$oShortcut_Group = $oShop_Group;
				$oShop_Group = clone $oShop_Group->Shortcut;

				$oShop_Group
					->id($oShortcut_Group->id)
					->addForbiddenTag('parent_id')
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('original_group_id')
							->value($oShortcut_Group->Shortcut->id)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('parent_id')
							->value($oShortcut_Group->parent_id)
					);
			}

			$this->_aShop_Groups[$oShop_Group->parent_id][] = $oShop_Group;
		}

		if ($group_id != 0)
		{
			$oShop_Group = Core_Entity::factory('Shop_Group', $group_id)
				->clearEntities();

			do {
				$this->applyGroupsForbiddenTags($oShop_Group);

				$this->_aShop_Groups[$oShop_Group->parent_id][] = $oShop_Group;
			} while ($oShop_Group = $oShop_Group->getParent());
		}

		$this->_addGroupsByParentId(0, $this);

		return $this;
	}

	/**
	 * Add groups by parent to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addGroupsByParentId($parent_id, $parentObject)
	{
		if (isset($this->_aShop_Groups[$parent_id]))
		{
			$bIsArrayGroupsProperties = is_array($this->groupsProperties);
			$bIsArrayPropertiesForGroups = is_array($this->propertiesForGroups);

			$oShop = $this->getEntity();

			foreach ($this->_aShop_Groups[$parent_id] as $oShop_Group)
			{
				// $bIsArrayGroupsProperties && $oShop_Group->showXmlProperties(FALSE);

				// Properties for shop's group entity
				if ($this->groupsProperties
					&& (!$bIsArrayPropertiesForGroups || in_array($oShop_Group->id, $this->propertiesForGroups)))
				{
					/*$aProperty_Values = $oShop_Group->getPropertyValues(TRUE, $bIsArrayGroupsProperties ? $this->groupsProperties : array());

					foreach ($aProperty_Values as $oProperty_Value)
					{
						$dAdd = $bIsArrayGroupsProperties
							? isset($this->groupsProperties[$oProperty_Value->property_id])
							: TRUE;

						if ($dAdd)
						{
							$oShop_Group->addEntity($oProperty_Value);
						}
					}*/

					$oShop_Group->showXmlProperties($this->groupsProperties);
				}
				else
				{
					$oShop_Group->showXmlProperties(FALSE);
				}

				$parentObject->addEntity($oShop_Group);

				$this->_addGroupsByParentId($oShop_Group->id, $oShop_Group);
			}
		}
		return $this;
	}

	/**
	 * Add items properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addItemsPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aItem_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aItem_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addItemsPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aItem_Properties[$parent_id]))
		{
			$oShop = $this->getEntity();

			foreach ($this->_aItem_Properties[$parent_id] as $oProperty)
			{
				if ($this->itemsPropertiesListJustAvailable
					// 3 - List
					&& $oProperty->type == 3 && $oProperty->list_id
					// 0 - Hide; 1 - Input; 2,3,4 - Select
					&& $oProperty->Shop_Item_Property->filter > 1)
				{
					//$shop_group_id = intval($this->group);

					$oCore_QueryBuilder_Select = Core_QueryBuilder::select('property_value_ints.value')
						->from('property_value_ints')
						->join('shop_items', 'shop_items.id', '=', 'property_value_ints.entity_id')
						->open()
						->where('shop_items.shop_id', '=', $oShop->id)
						->where('shop_items.active', '=', 1)
						->where('shop_items.modification_id', '=', 0);
						//->where('shop_items.shop_group_id', '=', $shop_group_id);

					$this->group !== FALSE
						&& $oCore_QueryBuilder_Select->where('shop_items.shop_group_id', '=', $this->group);

					$this->producer
						&& $oCore_QueryBuilder_Select->where('shop_items.shop_producer_id', '=', $this->producer);

					// Стандартные ограничения для товаров
					$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select);

					// Вывод модификаций на одном уровне в списке товаров
					if ($this->modificationsList)
					{
						$oCore_QueryBuilder_Select_Modifications = Core_QueryBuilder::select('shop_items.id')
							->from('shop_items')
							->where('shop_items.shop_id', '=', $oShop->id)
							->where('shop_items.deleted', '=', 0)
							->where('shop_items.active', '=', 1);
							//->where('shop_items.shop_group_id', '=', $shop_group_id);

						$this->group !== FALSE
							&& $oCore_QueryBuilder_Select_Modifications->where('shop_items.shop_group_id', '=', $this->group);

						$this->producer
							&& $oCore_QueryBuilder_Select_Modifications->where('shop_items.shop_producer_id', '=', $this->producer);

						// Стандартные ограничения для товаров
						$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Modifications);

						Core_Event::notify(get_class($this) . '.onBeforeSelectModifications', $this, array($oCore_QueryBuilder_Select_Modifications));

						$oCore_QueryBuilder_Select
							->setOr()
							->where('shop_items.shop_id', '=', $oShop->id)
							->where('shop_items.shop_group_id', '=', 0)
							->where('shop_items.deleted', '=', 0)
							->where('shop_items.active', '=', 1)
							->where('shop_items.modification_id', 'IN', $oCore_QueryBuilder_Select_Modifications);

						// Совместное modificationsList + filterShortcuts
						if ($this->filterShortcuts)
						{
							$oCore_QueryBuilder_Select_Shortcuts_For_Modifications = Core_QueryBuilder::select('shop_items.shortcut_id')
								->from('shop_items')
								->where('shop_items.shop_id', '=', $oShop->id)
								->where('shop_items.deleted', '=', 0)
								->where('shop_items.active', '=', 1)
								//->where('shop_items.shop_group_id', '=', $shop_group_id)
								->where('shop_items.shortcut_id', '>', 0);

							$this->group !== FALSE
								&& $oCore_QueryBuilder_Select_Shortcuts_For_Modifications->where('shop_items.shop_group_id', '=', $this->group);

							$this->producer
								&& $oCore_QueryBuilder_Select_Shortcuts_For_Modifications->where('shop_items.shop_producer_id', '=', $this->producer);

							$oCore_QueryBuilder_Select
								->setOr()
								->where('shop_items.shop_id', '=', $oShop->id)
								->where('shop_items.shop_group_id', '=', 0)
								->where('shop_items.modification_id', 'IN', $oCore_QueryBuilder_Select_Shortcuts_For_Modifications);
						}
					}

					if ($this->filterShortcuts)
					{
						$oCore_QueryBuilder_Select_Shortcuts = Core_QueryBuilder::select('shop_items.shortcut_id')
							->from('shop_items')
							->where('shop_items.deleted', '=', 0)
							->where('shop_items.active', '=', 1)
							->where('shop_items.shop_id', '=', $oShop->id)
							//->where('shop_items.shop_group_id', '=', $shop_group_id)
							->where('shop_items.shortcut_id', '>', 0);

						$this->group !== FALSE
							&& $oCore_QueryBuilder_Select_Shortcuts->where('shop_items.shop_group_id', '=', $this->group);

						// Стандартные ограничения для товаров
						$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

						$oCore_QueryBuilder_Select
							->setOr()
							->where('shop_items.id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);
					}

					$oCore_QueryBuilder_Select
						->close()
						->where('property_value_ints.property_id', '=', $oProperty->id)
						->groupBy('property_value_ints.value');

					$oProperty->limitListItems($oCore_QueryBuilder_Select);
				}

				$parentObject->addEntity($oProperty);
			}
		}

		return $this;
	}

	/**
	 * Add groups properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addGroupsPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aGroup_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aGroup_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addGroupsPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aGroup_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aGroup_Properties[$parent_id]);
		}

		return $this;
	}

	/**
	 * Show frontend panel
	 * @return $this
	 */
	protected function _showPanel()
	{
		$oShop = $this->getEntity();

		// Panel
		$oXslPanel = Core::factory('Core_Html_Entity_Div')
			->class('hostcmsPanel');

		$oXslSubPanel = Core::factory('Core_Html_Entity_Div')
			->class('hostcmsSubPanel hostcmsXsl')
			->add(
				Core::factory('Core_Html_Entity_Img')
					->width(3)->height(16)
					->src('/hostcmsfiles/images/drag_bg.gif')
			);

		if ($this->item == 0)
		{
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=edit&shop_id={$oShop->id}&shop_group_id={$this->group}&hostcms[checked][1][0]=1";
			$sTitle = Core::_('Shop_Item.items_catalog_add_form_title');

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/page_add.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);

			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=edit&shop_id={$oShop->id}&shop_group_id={$this->group}&hostcms[checked][0][0]=1";
			$sTitle = Core::_('Shop_Group.groups_add_form_title');

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/folder_add.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);

			if ($this->group)
			{
				$oShop_Group = Core_Entity::factory('Shop_Group', $this->group);

				// Edit
				$sPath = '/admin/shop/item/index.php';
				$sAdditional = "hostcms[action]=edit&shop_id={$oShop->id}&shop_group_id={$oShop_Group->parent_id}&hostcms[checked][0][{$this->group}]=1";
				$sTitle = Core::_('Shop_Group.groups_edit_form_title', $oShop_Group->name);

				$oXslSubPanel->add(
					Core::factory('Core_Html_Entity_A')
						->href("{$sPath}?{$sAdditional}")
						->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
						->add(
							Core::factory('Core_Html_Entity_Img')
								->width(16)->height(16)
								->src('/admin/images/folder_edit.gif')
								->alt($sTitle)
								->title($sTitle)
						)
				);
			}

			// Folder
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "shop_id={$oShop->id}&shop_group_id={$this->group}";
			$sTitle = Core::_('Shop_Group.links_groups');

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/folder.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);

			if ($this->group)
			{
				// Delete
				$sPath = '/admin/shop/item/index.php';
				$sAdditional = "hostcms[action]=markDeleted&shop_id={$oShop->id}&shop_group_id={$oShop_Group->parent_id}&hostcms[checked][0][{$this->group}]=1";
				$sTitle = Core::_('Shop_Group.markDeleted');

				$oXslSubPanel->add(
					Core::factory('Core_Html_Entity_A')
						->href("{$sPath}?{$sAdditional}")
						->onclick("res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'});} return false")
						->add(
							Core::factory('Core_Html_Entity_Img')
								->width(16)->height(16)
								->src('/admin/images/delete.gif')
								->alt($sTitle)
								->title($sTitle)
						)
				);
			}

			$sPath = '/admin/shop/index.php';
			$sAdditional = "hostcms[action]=edit&shop_dir_id={$oShop->shop_dir_id}&hostcms[checked][1][{$oShop->id}]=1";
			$sTitle = Core::_('Shop.edit_title', $oShop->name);

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/folder_page_edit.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);
		}
		else
		{
			$oShop_Item = Core_Entity::factory('Shop_Item', $this->item);

			// Edit
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=edit&shop_id={$oShop->id}&shop_group_id={$this->group}&hostcms[checked][1][{$this->item}]=1";
			$sTitle = Core::_('Shop_Item.items_catalog_edit_form_title', $oShop_Item->name);

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/edit.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);

			// Copy
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=copy&shop_id={$oShop->id}&shop_group_id={$this->group}&hostcms[checked][1][{$this->item}]=1";
			$sTitle = Core::_('Shop_Item.items_catalog_copy_form_title');

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/copy.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);

			// Folder
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "shop_id={$oShop->id}&shop_group_id={$this->group}";
			$sTitle = Core::_('Shop_Group.links_groups');

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/folder.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);

			// Comments
			$sPath = '/admin/shop/item/comment/index.php';
			$sAdditional = "shop_item_id={$this->item}";
			$sTitle = Core::_('Shop_Item.items_catalog_add_form_comment_link');

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/comments.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);

			// Delete
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=markDeleted&shop_id={$oShop->id}&shop_group_id={$this->group}&hostcms[checked][1][{$this->item}]=1";
			$sTitle = Core::_('Shop_Item.markDeleted');

			$oXslSubPanel->add(
				Core::factory('Core_Html_Entity_A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'});} return false")
					->add(
						Core::factory('Core_Html_Entity_Img')
							->width(16)->height(16)
							->src('/admin/images/delete.gif')
							->alt($sTitle)
							->title($sTitle)
					)
			);
		}

		$oXslPanel
			->add($oXslSubPanel)
			->execute();

		return $this;
	}

	/**
	 * Set items activity
	 * @return self
	 */
	protected function _setItemsActivity()
	{
		$this->itemsActivity = strtolower($this->itemsActivity);
		if ($this->itemsActivity != 'all')
		{
			$this->_Shop_Items
				->queryBuilder()
				->where('shop_items.active', '=', $this->itemsActivity == 'inactive' ? 0 : 1);
		}

		return $this;
	}

	/**
	 * Set groups activity
	 * @return self
	 */
	protected function _setGroupsActivity()
	{
		$this->groupsActivity = strtolower($this->groupsActivity);
		if ($this->groupsActivity != 'all')
		{
			$this->_Shop_Groups
				->queryBuilder()
				->where('shop_groups.active', '=', $this->groupsActivity == 'inactive' ? 0 : 1);
		}

		return $this;
	}

	/**
	 * Add minimum and maximum price
	 * @return self
	 */
	public function addMinMaxPrice()
	{
		$oShop = $this->getEntity();

		$oSubMinMaxQueryBuilder = Core_QueryBuilder::select()
			->from('shop_items')
			->where('shop_items.deleted', '=', 0)
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.active', '=', 1)
			//->open()
			->where('shop_items.shortcut_id', '=', 0);

		$this->group !== FALSE && $oSubMinMaxQueryBuilder->where('shop_items.shop_group_id', '=', intval($this->group));

		if ($oShop->filter)
		{
			$tableName = 'shop_filter' . $oShop->id;

			$oSubMinMaxQueryBuilder
				->clearSelect()
				->select(array('MIN(price_absolute)', 'min'), array('MAX(price_absolute)', 'max'))
				->join($tableName, 'shop_items.id', '=', $tableName . '.shop_item_id');

			$rows = $oSubMinMaxQueryBuilder->asAssoc()->execute()->current();
		}
		else
		{
			$this->_applyAbsolutePrice($oSubMinMaxQueryBuilder);

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
				array(Core_QueryBuilder::expression('MIN(t.absolute_price)'), 'min'),
				array(Core_QueryBuilder::expression('MAX(t.absolute_price)'), 'max')
			)
			->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();
		}

		$min = $rows['min'];
		$max = $rows['max'];

		if ($this->modificationsList)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select()
				->from('shop_items')
				->where('shop_items.shop_group_id', '=', 0)
				->join(array('shop_items', 't'), 'shop_items.modification_id', '=', 't.id')
				->where('t.deleted', '=', 0)
				->where('t.active', '=', 1);

			$this->group !== FALSE && $oSubMinMaxQueryBuilder->where('t.shop_group_id', '=', intval($this->group));

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 't');

			if ($oShop->filter)
			{
				$tableName = 'shop_filter' . $oShop->id;

				$oSubMinMaxQueryBuilder
					->clearSelect()
					->select(array('MIN(price_absolute)', 'min'), array('MAX(price_absolute)', 'max'))
					->join($tableName, 'shop_items.id', '=', $tableName . '.shop_item_id');

				$rows = $oSubMinMaxQueryBuilder->asAssoc()->execute()->current();
			}
			else
			{
				$this->_applyAbsolutePrice($oSubMinMaxQueryBuilder);

				$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.absolute_price)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.absolute_price)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

				$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();
			}

			$rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		if ($this->filterShortcuts)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select()
				->from('shop_items')
				->where('shop_items.shop_group_id', '=', 0)
				->join(array('shop_items', 't'), 'shop_items.shortcut_id', '=', 't.id')
				->where('t.deleted', '=', 0)
				->where('t.active', '=', 1)
				->where('t.shortcut_id', '>', 0);

			$this->group !== FALSE && $oSubMinMaxQueryBuilder->where('t.shop_group_id', '=', intval($this->group));

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 't');

			if ($oShop->filter)
			{
				$tableName = 'shop_filter' . $oShop->id;

				$oSubMinMaxQueryBuilder
					->clearSelect()
					->select(array('MIN(price_absolute)', 'min'), array('MAX(price_absolute)', 'max'))
					->join($tableName, 'shop_items.id', '=', $tableName . '.shop_item_id');

				$rows = $oSubMinMaxQueryBuilder->asAssoc()->execute()->current();
			}
			else
			{
				$this->_applyAbsolutePrice($oSubMinMaxQueryBuilder);

				$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.absolute_price)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.absolute_price)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

				$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();
			}

			$rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('min_price')
				->value(floor($min))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('max_price')
				->value(ceil($max))
		);

		return $this;
	}

	/**
	 * Add shortcut conditions
	 *
	 * @return self
	 */
	public function addShortcutConditions()
	{
		$this->_Shop_Items
			->queryBuilder()
			->leftJoin(array('shop_items', 'shortcut_items'), 'shortcut_items.id', '=', 'shop_items.shortcut_id')
			->open()
			->where('shortcut_items.id', 'IS', NULL)
			->setOr()
			->where('shortcut_items.active', '=', 1);

		$this->_applyItemConditionsQueryBuilder($this->_Shop_Items->queryBuilder(), 'shortcut_items');

		$this->_Shop_Items
			->queryBuilder()
			->close();

		return $this;
	}

	/**
	 * Set goods sorting
	 * @param $column Column name, e.g. price, absolute_price
	 * @return self
	 */
	public function orderBy($column, $direction = 'ASC')
	{
		switch ($column)
		{
			case 'price':
			case 'absolute_price':
				$this->addAbsolutePrice();

				$column = 'absolute_price';
			break;
		}

		$this->shopItems()
			->queryBuilder()
			->clearOrderBy()
			->orderBy($column, $direction);

		$this->addCacheSignature('orderBy=' . $column . $direction);

		return $this;
	}

	/**
	 * Array of Properties conditions, see addFilter()
	 * @var array
	 */
	protected $_aFilterProperties = array();

	/**
	 * Array of Price conditions, see addFilter()
	 * @var array
	 */
	protected $_aFilterPrices = array();

	/**
	 * Add filter condition
	 * ->addFilter('property', 17, '=', 33)
	 * ->addFilter('price', '>', 100)
	 */
	public function addFilter()
	{
		$args = func_get_args();

		$iCountArgs = count($args);

		if ($iCountArgs < 3)
		{
			throw new Core_Exception("addFilter() expected at least 3 arguments");
		}

		switch ($args[0])
		{
			case 'property':
				if ($iCountArgs < 4)
				{
					throw new Core_Exception("addFilter('property') expected 4 arguments");
				}

				$oProperty = Core_Entity::factory('Property', $args[1]);

				$aPropertiesValue = $args[3];

				!is_array($aPropertiesValue) && $aPropertiesValue = array($aPropertiesValue);

				switch ($oProperty->type)
				{
					case 3:
					case 5:
					case 12:
					case 7:
						$map = 'intval';
					break;
					case 11:
						$map = 'floatval';
					break;
					default:
						$map = 'strval';
				}

				$aPropertiesValue = array_map($map, $aPropertiesValue);

				$this->_aFilterProperties[$oProperty->id][] = array($oProperty, $args[2], $aPropertiesValue);
			break;
			case 'price':
				$this->_aFilterPrices[] = array($args[1], $args[2]);
			break;
			default:
				throw new Core_Exception("The option '%option' doesn't allow",
					array('%option' => $args[0])
				);
		}

		return $this;
	}

	/**
	 * Remove filter condition
	 * ->removeFilter('property', 17)
	 * ->removeFilter('price')
	 */
	public function removeFilter()
	{
		$args = func_get_args();

		$iCountArgs = count($args);

		if ($iCountArgs < 1)
		{
			throw new Core_Exception("removeFilter() expected at least 1 arguments");
		}

		switch ($args[0])
		{
			case 'property':
				if ($iCountArgs < 2)
				{
					throw new Core_Exception("removeFilter('property') expected 2 arguments");
				}

				$property_id = $args[1];

				if (isset($this->_aFilterProperties[$property_id]))
				{
					unset($this->_aFilterProperties[$property_id]);
				}
			break;
			case 'price':
				$this->_aFilterPrices = array();
			break;
			default:
				throw new Core_Exception("The option '%option' doesn't allow",
					array('%option' => $args[0])
				);
		}

		return $this;
	}

	/**
	 * Apply Filter
	 * @return self
	 */
	public function applyFilter()
	{
		$oShop = $this->getEntity();

		$oShop->filter
			? $this->_fastFilter()
			: $this->_basicFilter();

		return $this;
	}

	/**
	 * Apply Fast Filter
	 * @return self
	 */
	protected function _fastFilter()
	{
		$oShop = $this->getEntity();

		if (count($this->_aFilterProperties) || count($this->_aFilterPrices))
		{
			$tableName = 'shop_filter' . $oShop->id;

			$QB = $this->shopItems()->queryBuilder();

			$QB
				->distinct()
				->join($tableName, 'shop_items.id', '=', $tableName . '.shop_item_id');

			// Filter by properties
			$this->applyFastFilterProperties($QB);

			// Filter by prices
			$this->applyFastFilterPrices($QB);

			foreach ($this->_aFilterProperties as $iPropertyId => $aTmpProperties)
			{
				foreach ($aTmpProperties as $aTmpProperty)
				{
					list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;

					$this->_addFilterPropertyToXml($oProperty, $condition, $aPropertyValues);
				}
			}
		}

		return $this;
	}

	/**
	 * Apply Fast Filter Properties
	 * @param Core_QueryBuilder_Select $QB
	 * @return self
	 */
	public function applyFastFilterProperties($QB)
	{
		$oShop = $this->getEntity();

		$tableName = 'shop_filter' . $oShop->id;

		foreach ($this->_aFilterProperties as $iPropertyId => $aTmpProperties)
		{
			foreach ($aTmpProperties as $aTmpProperty)
			{
				list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;

				// Для строк фильтр LIKE %...%
				if ($oProperty->type == 1)
				{
					foreach ($aPropertyValues as $propertyValue)
					{
						$QB
							->where($tableName . '.property' . $oProperty->id, 'LIKE', "%{$propertyValue}%");
					}
				}
				else
				{
					// 7 - Checkbox
					$oProperty->type == 7 && $aPropertyValues[0] != '' && $aPropertyValues = array(1);

					// 7 - Checkbox, 3 - List
					$bCheckUnset = $oProperty->type != 7 && $oProperty->type != 3;

					$bCheckUnset && $QB->open();

					$QB
						->where(
							$tableName . '.property' . $oProperty->id,
							count($aPropertyValues) == 1 ? $condition : 'IN',
							count($aPropertyValues) == 1 ? $aPropertyValues[0] : $aPropertyValues
						);

					$bCheckUnset && $QB
						->setOr()
						->where($tableName . '.property' . $oProperty->id, 'IS', NULL)
						->close();
				}
			}
		}

		return $this;
	}

	/**
	 * Apply Fast Filter Prices
	 * @param Core_QueryBuilder_Select $QB
	 * @return self
	 */
	public function applyFastFilterPrices($QB)
	{
		$oShop = $this->getEntity();

		$tableName = 'shop_filter' . $oShop->id;

		if (count($this->_aFilterPrices))
		{
			$this->addAbsolutePrice();

			foreach ($this->_aFilterPrices as $aTmpPrice)
			{
				list($condition, $value) = $aTmpPrice;

				$QB->where($tableName . '.price_absolute', $condition, $value);

				$this->_addFilterPriceToXml($condition, $value);
			}
		}

		return $this;
	}

	/**
	 * Apply Basic Filter
	 * @return self
	 */
	protected function _basicFilter()
	{
		// Filter by properties
		if (count($this->_aFilterProperties))
		{
			$aTableNames = array();

			$this->shopItems()->queryBuilder()
				->leftJoin('shop_item_properties', 'shop_items.shop_id', '=', 'shop_item_properties.shop_id')
				->setAnd()
				->open();

			foreach ($this->_aFilterProperties as $iPropertyId => $aTmpProperties)
			{
				foreach ($aTmpProperties as $aTmpProperty)
				{
					list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;
					$tableName = $oProperty->createNewValue(0)->getTableName();

					!in_array($tableName, $aTableNames) && $aTableNames[] = $tableName;

					$this->shopItems()->queryBuilder()
						->where('shop_item_properties.property_id', '=', $oProperty->id);

					// Для строк фильтр LIKE %...%
					if ($oProperty->type == 1)
					{
						foreach ($aPropertyValues as $propertyValue)
						{
							$this->shopItems()->queryBuilder()
								->where($tableName . '.value', 'LIKE', "%{$propertyValue}%");
						}
					}
					else
					{
						// 7 - Checkbox
						$oProperty->type == 7 && $aPropertyValues[0] != '' && $aPropertyValues = array(1);

						// 7 - Checkbox, 3 - List
						$bCheckUnset = $oProperty->type != 7 && $oProperty->type != 3;

						$bCheckUnset && $this->shopItems()->queryBuilder()->open();

						$this->shopItems()->queryBuilder()
							->where(
								$tableName . '.value',
								count($aPropertyValues) == 1 ? $condition : 'IN',
								count($aPropertyValues) == 1 ? $aPropertyValues[0] : $aPropertyValues
							);

						$bCheckUnset && $this->shopItems()->queryBuilder()
							->setOr()
							->where($tableName . '.value', 'IS', NULL)
							->close();
					}

					// Между значениями значение по AND (например, значение => 10 и значение <= 99)
					$this->shopItems()->queryBuilder()->setAnd();

					$this->_addFilterPropertyToXml($oProperty, $condition, $aPropertyValues);
				}

				// при смене свойства сравнение через OR
				$this->shopItems()->queryBuilder()->setOr();
			}

			$this->shopItems()->queryBuilder()
				->close()
				->groupBy('shop_items.id');

			foreach ($aTableNames as $tableName)
			{
				$this->shopItems()->queryBuilder()
					->leftJoin($tableName, 'shop_items.id', '=', $tableName . '.entity_id',
						array(
							array('AND' => array('shop_item_properties.property_id', '=', Core_QueryBuilder::expression($tableName . '.property_id')))
						)
					);
			}

			$havingCount = count($this->_aFilterProperties);

			$havingCount > 1
				&& $this->shopItems()->queryBuilder()
						->having(Core_Querybuilder::expression('COUNT(DISTINCT `shop_item_properties`.`property_id`)'), '=', $havingCount);
		}

		// Filter by prices
		if (count($this->_aFilterPrices))
		{
			$this->addAbsolutePrice();

			foreach ($this->_aFilterPrices as $aTmpPrice)
			{
				list($condition, $value) = $aTmpPrice;

				$this->shopItems()->queryBuilder()->having('absolute_price', $condition, $value);

				$this->_addFilterPriceToXml($condition, $value);
			}
		}

		return $this;
	}

	/**
	 * Add Filter Property to the XML
	 * @param Property_Model $oProperty
	 * @param string $condition
	 * @param array $aPropertyValues
	 * @return self
	 */
	protected function _addFilterPropertyToXml($oProperty, $condition, $aPropertyValues)
	{
		switch ($condition)
		{
			case '>=':
				$xmlName = 'property_' . $oProperty->id . '_from';
			break;
			case '<=':
				$xmlName = 'property_' . $oProperty->id . '_to';
			break;
			default:
				$xmlName = 'property_' . $oProperty->id;
		}

		foreach ($aPropertyValues as $propertyValue)
		{
			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name($xmlName)
					->value($propertyValue)
					->addAttribute('condition', $condition)
			);
			$this->addCacheSignature("{$xmlName}{$condition}{$propertyValue}");
		}

		return $this;
	}

	/**
	 * Add Filter Price to the XML
	 * @param string $condition
	 * @param mixed $value
	 * @return self
	 */
	protected function _addFilterPriceToXml($condition, $value)
	{
		switch ($condition)
		{
			case '>=':
				$xmlName = 'price_from';
			break;
			case '<=':
				$xmlName = 'price_to';
			break;
			default:
				$xmlName = 'price';
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name($xmlName)
				->value($value)
				->addAttribute('condition', $condition)
		);
		$this->addCacheSignature($xmlName . $condition . $value);
	}

	/**
	 * AbsolutePrice has been added
	 * @var boolean
	 */
	protected $_addedAbsolutePrice = FALSE;

	/**
	 * Add `absolute_price` to the query
	 * @return self
	 */
	public function addAbsolutePrice()
	{
		if (!$this->_addedAbsolutePrice)
		{
			$this->_addedAbsolutePrice = TRUE;

			$oShop = $this->getEntity();

			$this->_applyAbsolutePrice($this->shopItems()->queryBuilder());
		}

		return $this;
	}

	/**
	 * Apply `absolute_price` to the Query Builder
	 * @param Core_QueryBuilder_Select $oQB
	 * @return Core_QueryBuilder_Select
	 */
	protected function _applyAbsolutePrice($oQB)
	{
		$oShop = $this->getEntity();

		// Получаем список валют магазина
		$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll();

		$query_tax = 'IF(`shop_taxes`.`tax_is_included` IS NULL OR `shop_taxes`.`tax_is_included` = 1, 0, `shop_items`.`price` * `shop_taxes`.`rate` / 100)';
		$query_currency_switch = "`shop_items`.`price` + {$query_tax}";
		foreach ($aShop_Currencies as $oShop_Currency)
		{
			// Получаем коэффициент пересчета для каждой валюты
			$currency_coefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$oShop_Currency, $oShop->Shop_Currency
			);

			$query_currency_switch = "IF (`shop_items`.`shop_currency_id` = '{$oShop_Currency->id}', IF (COUNT(`shop_discounts`.`id`), ((`shop_items`.`price` + {$query_tax}) * (1 - SUM(DISTINCT IF(`shop_discounts`.`type` = 0, `shop_discounts`.`value`, 0)) / 100)) * {$currency_coefficient} - SUM(DISTINCT IF(`shop_discounts`.`type`, `shop_discounts`.`value`, 0)), (`shop_items`.`price`) * {$currency_coefficient}), {$query_currency_switch})";
		}

		$current_date = date('Y-m-d H:i:s');
		$oQB
			->select(array(Core_QueryBuilder::expression($query_currency_switch), 'absolute_price'))
			->leftJoin('shop_item_discounts', 'shop_items.id', '=', 'shop_item_discounts.shop_item_id')
			->leftJoin('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id', array(
				array('AND ' => array('shop_discounts.active', '=', 1)),
				array('AND ' => array('shop_discounts.deleted', '=', 0)),
				array('AND' => array('shop_discounts.start_datetime', '<=', $current_date)),
				array('AND (' => array('shop_discounts.end_datetime', '>=', $current_date)),
				array('OR' => array('shop_discounts.end_datetime', '=', '0000-00-00 00:00:00')),
				array(')' => NULL)
			))
			->leftJoin('shop_taxes', 'shop_taxes.id', '=', 'shop_items.shop_tax_id')
			->groupBy('shop_items.id');

		return $oQB;
	}

	/**
	 * Set Filter Prices Conditions by price_from and price_to
	 * @param array $aData
	 * @return self
	 */
	public function setFilterPricesConditions($aData)
	{
		$price_from = intval(Core_Array::get($aData, 'price_from'));
		$price_to = intval(Core_Array::get($aData, 'price_to'));

		$price_from && $this->addFilter('price', '>=', $price_from);
		$price_to && $this->addFilter('price', '<=', $price_to);

		return $this;
	}

	/**
	 * Set Filter Prices Conditions by $aData
	 * @param array $aData
	 * @return self
	 */
	public function setFilterPropertiesConditions($aData)
	{
		$oShop = $this->getEntity();
		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

		$aProperties = $this->group !== FALSE && is_null($this->tag)
			? $oShop_Item_Property_List->getPropertiesForGroup($this->group)
			: $oShop_Item_Property_List->Properties->findAll();

		foreach ($aProperties as $oProperty)
		{
			$aPropertiesValue = Core_Array::get($aData, 'property_' . $oProperty->id);
			if ($aPropertiesValue)
			{
				$this->addFilter('property', $oProperty->id, '=', $aPropertiesValue);
			}
			elseif (!is_null(Core_Array::get($aData, 'property_' . $oProperty->id . '_from')))
			{
				$tmpFrom = Core_Array::get($aData, 'property_' . $oProperty->id . '_from');
				$tmpTo = Core_Array::get($aData, 'property_' . $oProperty->id . '_to');

				$tmpFrom != ''
					&& $this->addFilter('property', $oProperty->id, '>=', $tmpFrom);

				$tmpTo != ''
					&& $this->addFilter('property', $oProperty->id, '<=', $tmpTo);
			}
		}

		return $this;
	}

	/**
	 * Get Filter Properties
	 * @return array
	 */
	public function getFilterProperties()
	{
		return $this->_aFilterProperties;
	}

	/**
	 * Set Filter Properties
	 * @param array $array
	 * @return self
	 */
	public function setFilterProperties(array $array)
	{
		$this->_aFilterProperties = $array;
		return $this;
	}

	/**
	 * Get Filter Prices
	 * @return array
	 */
	public function getFilterPrices()
	{
		return $this->_aFilterPrices;
	}

	/**
	 * Set Filter Prices
	 * @param array $array
	 * @return self
	 */
	public function setFilterPrices(array $array)
	{
		$this->_aFilterPrices = $array;
		return $this;
	}

	/**
	 * Add Producers to the XML
	 * @return self
	 */
	public function addProducers()
	{
		$oShop = $this->getEntity();

		// XML-сущность, к которой будут добавляться производители
		$oProducersXmlEntity = Core::factory('Core_Xml_Entity')->name('producers');

		// Добавляем XML-сущность контроллеру показа
		$this->addEntity($oProducersXmlEntity);

		// Список производителей
		$oShop_Producers = $oShop->Shop_Producers;
		$oShop_Producers->queryBuilder()
			->select('shop_producers.*')
			->distinct()
			->join('shop_items', 'shop_items.shop_producer_id', '=', 'shop_producers.id')
			->where('shop_items.deleted', '=', 0);

		$this->group !== FALSE
			&& $oShop_Producers->queryBuilder()->where('shop_items.shop_group_id', '=', $this->group);

		!$this->modificationsList
			&& $oShop_Producers->queryBuilder()->where('shop_items.modification_id', '=', 0);

		$aProducerCounts = array();
		if ($oShop->filter)
		{
			$tableName = 'shop_filter' . $oShop->id;
			$oQueryBuilder = Core_QueryBuilder::select()
				->clearSelect()
				->select($tableName . '.shop_producer_id', array(Core_QueryBuilder::expression('COUNT(DISTINCT (shop_item_id))'), 'count'))
				->from($tableName)
				->groupBy($tableName . '.shop_producer_id');

			$this->group !== FALSE
				&& $oQueryBuilder->where($tableName . '.shop_group_id', '=', $this->group);

			!$this->modificationsList
				&& $oQueryBuilder->where($tableName . '.modification_id', '=', 0);

			// Filter by properties
			$this->applyFastFilterProperties($oQueryBuilder);

			// Filter by prices
			$this->applyFastFilterPrices($oQueryBuilder);

			$aRows = $oQueryBuilder->asAssoc()->execute()->result();

			foreach ($aRows as $row)
			{
				$aProducerCounts[$row['shop_producer_id']] = $row['count'];
			}
		}

		$aShop_Producers = $oShop_Producers->findAll(FALSE);
		foreach ($aShop_Producers as $oShop_Producer)
		{
			$oShop_Producer->clearEntities();

			if ($oShop->filter)
			{
				$oShop_Producer->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('count')
						->value(Core_Array::get($aProducerCounts, $oShop_Producer->id, 0))
				);
			}

			// Добавляем производителя потомком XML-сущности
			$oProducersXmlEntity->addEntity($oShop_Producer);
		}

		return $this;
	}
}