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
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE
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
 * - specialprices(TRUE|FALSE) показывать специальные цены для выбранных товаров, по умолчанию FALSE
 * - associatedItems(TRUE|FALSE) показывать сопутствующие товары для выбранных товаров, по умолчанию FALSE
 * - comments(TRUE|FALSE) показывать комментарии для выбранных товаров, по умолчанию FALSE
 * - votes(TRUE|FALSE) показывать рейтинг элемента, по умолчанию TRUE
 * - tags(TRUE|FALSE) выводить метки
 * - calculateCounts(TRUE|FALSE) вычислять общее количество товаров и групп в корневой группе, по умолчанию FALSE
 * - siteuser(TRUE|FALSE) показывать данные о пользователе сайта, связанного с выбранным товаром, по умолчанию TRUE
 * - siteuserProperties(TRUE|FALSE) выводить значения дополнительных свойств пользователей сайта, по умолчанию FALSE
 * - bonuses(TRUE|FALSE) выводить бонусы для товаров, по умолчанию TRUE
 * - comparing(TRUE|FALSE) выводить сравниваемые товары, по умолчанию TRUE
 * - comparingLimit(10) максимальное количество выводимых сравниваемых товаров, по умолчанию 10
 * - favorite(TRUE|FALSE) выводить избранные товары, по умолчанию TRUE
 * - favoriteLimit(10) максимальное количество выводимых избранных товаров, по умолчанию 10
 * - favoriteOrder('ASC'|'DESC'|'RAND') направление сортировки избранных товаров, по умолчанию RAND
 * - viewed(TRUE|FALSE) выводить просмотренные товары, по умолчанию TRUE
 * - viewedLimit(10) максимальное количество выводимых просмотренных товаров, по умолчанию 10
 * - viewedOrder('ASC'|'DESC'|'RAND') направление сортировки просмотренных товаров, по умолчанию DESC
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
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'bonuses',
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
			= $this->modificationsList = $this->filterShortcuts = $this->itemsPropertiesListJustAvailable = FALSE;

		$this->siteuser = $this->cache = $this->itemsPropertiesList = $this->groupsPropertiesList
			= $this->bonuses = $this->comparing = $this->favorite = $this->viewed
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

		if ($this->favorite && isset($_SESSION))
		{
			$hostcmsFavorite = Core_Array::get(Core_Array::getSession('hostcmsFavorite', array()), $oShop->id, array());
			count($hostcmsFavorite) && $this->addCacheSignature('hostcmsFavorite=' . implode(',', $hostcmsFavorite));
		}
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
					->orderBy('shop_items.name', $items_sorting_direction)
					//->orderBy('shop_items.sorting', $items_sorting_direction)
					;
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
					->orderBy('shop_items.datetime', $items_sorting_direction)
					//->orderBy('shop_items.sorting', $items_sorting_direction)
					;
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
	 * Get items set
	 * @return Shop_Item_Model
	 */
	public function shopItems()
	{
		return $this->_Shop_Items;
	}

	/**
	 * Get groups set
	 * @return Shop_Item_Model
	 */
	public function shopGroups()
	{
		return $this->_Shop_Groups;
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
					$this->applyItemsForbiddenTags($oShop_Item);

					$this->itemsProperties && $oShop_Item->showXmlProperties($this->itemsProperties);
					$this->bonuses && $oShop_Item->showXmlBonuses($this->bonuses);

					Core_Event::notify(get_class($this) . '.onBeforeAddFavoriteEntity', $this, array($oShop_Item));

					$oFavouriteEntity->addEntity($oShop_Item);
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
					$this->applyItemsForbiddenTags($oShop_Item);

					$oShop_Item->showXmlProperties($this->itemsProperties);
					$oShop_Item->showXmlComments($this->comments);
					$oShop_Item->showXmlBonuses($this->bonuses);
					$oShop_Item->showXmlSpecialprices($this->specialprices);

					Core_Event::notify(get_class($this) . '.onBeforeAddViewedEntity', $this, array($oShop_Item));

					$oViewedEntity->addEntity($oShop_Item);
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
		Core_Entity::factory('Shop_Item')->getTableColums();

		// Load user BEFORE FOUND_ROWS()
		Core_Entity::factory('User', 0)->getCurrent();

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
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$this->showPanel && Core::checkPanel() && $this->_showPanel();

		$bXsl = !is_null($this->_xsl);

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

		$oShop = $this->getEntity();

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

		// Comparing, favorite and viewed goods
		if (isset($_SESSION))
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
							$this->itemsProperties && $oShop_Item->showXmlProperties($this->itemsProperties);
							$oCartEntity->addEntity($oShop_Item->clearEntities());
						}
					}
				}
			}
		}

		$this->_shownIDs = array();

		if (!$bXsl)
		{
			$this->assign('controller', $this);
			$this->assign('aShop_Items', array());
		}

		if ($this->limit == 0 && $this->page)
		{
			return $this->error404();
		}

		// До вывода свойств групп
		if ($this->limit > 0 || $this->item)
		{
			$this->_itemCondition();

			// Group condition for shop item
			$this->group !== FALSE && $this->_groupCondition();

			!$this->item && $this->_setLimits();

			switch ($this->warehouseMode)
			{
				case 'in-stock':
					$this->_Shop_Items
						->queryBuilder()
						->leftJoin('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', 'shop_items.id')
						->having('SUM(shop_warehouse_items.count)', '>', 0)
						->groupBy('shop_items.id');
				break;
				case 'in-stock-modification':
					$this->_Shop_Items
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
				$oProperty_Dir->clearEntities();
				$this->_aGroup_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			if ($bXsl)
			{
				$Shop_Group_Properties = Core::factory('Core_Xml_Entity')
					->name('shop_group_properties');

				$this->addEntity($Shop_Group_Properties);

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

				if ($bXsl)
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
						$oShop_Item->showXmlWarehousesItems($this->warehousesItems);
						$oShop_Item->showXmlAssociatedItems($this->associatedItems);
						$oShop_Item->showXmlModifications($this->modifications);
						$oShop_Item->showXmlSpecialprices($this->specialprices);
						$oShop_Item->showXmlTags($this->tags);
						$oShop_Item->showXmlVotes($this->votes);

						$oShop_Item->showXmlProperties($mShowPropertyIDs);

						// Siteuser
						$oShop_Item->showXmlSiteuser($this->siteuser)
							->showXmlSiteuserProperties($this->siteuserProperties);

						$this->addEntity($oShop_Item);

						// Parent item for modification
						$this->parentItem && $oShop_Item->addEntity(
							Core_Entity::factory('Shop_Item', $this->parentItem)
								->showXmlProperties($this->itemsProperties)
								->showXmlTags($this->tags)
								->showXmlWarehousesItems($this->warehousesItems)
								->showXmlAssociatedItems($this->associatedItems)
								->showXmlModifications($this->modifications)
								->showXmlSpecialprices($this->specialprices)
								->showXmlVotes($this->votes)
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
	 */
	protected function _itemsProperties()
	{
		$aShowPropertyIDs = array();

		$oShop = $this->getEntity();

		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

		$bXsl = !is_null($this->_xsl);

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

				if ($bXsl)
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

			if ($bXsl)
			{
				$Shop_Item_Properties = Core::factory('Core_Xml_Entity')
					->name('shop_item_properties');

				$this->addEntity($Shop_Item_Properties);

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
	 * Set item's conditions
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
	 * Set item's condition by shop_group_id
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
			$aPath = explode('/', $path);
			foreach ($aPath as $sPath)
			{
				// Attempt to receive Shop_Group
				$oShop_Groups = $oShop->Shop_Groups;

				$this->groupsActivity = strtolower($this->groupsActivity);
				if ($this->groupsActivity != 'all')
				{
					$oShop_Groups
						->queryBuilder()
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
				}
				else
				{
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

					//$this->forbidSelectModifications();
					$oShop_Items->queryBuilder()->where('shop_items.modification_id', '=', 0);

					$oShop_Item = $oShop_Items->getByGroupIdAndPath($this->group, $sPath);

					if (!$this->item && !is_null($oShop_Item))
					{
						if (in_array($oShop_Item->getSiteuserGroupId(), $this->_aSiteuserGroups))
						{
							$this->group = $oShop_Item->shop_group_id;
							$this->item = $oShop_Item->id;
						}
						else
						{
							return $this->error403();
						}
					}
					else
					{
						// Товар был уже определен, по пути ищем модификацию
						if ($this->item)
						{
							$oShop_Modification_Items = $oShop->Shop_Items;
							$oShop_Modification_Items
								->queryBuilder()
								->where('active', '=', 1)
								->where('shop_items.modification_id', '=', $this->item);

							$oShop_Modification_Item = $oShop_Modification_Items->getByGroupIdAndPath(0, $sPath);
							if (!is_null($oShop_Modification_Item))
							{
								// Родительский товар для модификации
								$this->parentItem = $this->item;

								// Модификация в основной товар
								$this->item = $oShop_Modification_Item->id;
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
					}
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

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

		return $this;
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
				// Properties for shop's group entity
				if ($this->groupsProperties
					&& (!$bIsArrayPropertiesForGroups || in_array($oShop_Group->id, $this->propertiesForGroups)))
				{
					$aProperty_Values = $oShop_Group->getPropertyValues(TRUE, $bIsArrayGroupsProperties ? $this->groupsProperties : array());

					foreach ($aProperty_Values as $oProperty_Value)
					{
						$dAdd = $bIsArrayGroupsProperties
							? isset($this->groupsProperties[$oProperty_Value->property_id])
							: TRUE;

						if ($dAdd)
						{
							$type = $oProperty_Value->Property->type;

							if ($type == 8)
							{
								$oProperty_Value->dateFormat($oShop->format_date);
							}
							elseif ($type == 9)
							{
								$oProperty_Value->dateTimeFormat($oShop->format_datetime);
							}

							$oShop_Group->addEntity($oProperty_Value);
						}
					}
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
			foreach ($this->_aItem_Properties[$parent_id] as $oProperty)
			{
				if ($this->itemsPropertiesListJustAvailable
					// 3 - List
					&& $oProperty->type == 3 && $oProperty->list_id
					// 0 - Hide; 1 - Input; 2,3,4 - Select
					&& $oProperty->Shop_Item_Property->filter > 1)
				{
					$shop_group_id = intval($this->group);

					$oCore_QueryBuilder_Select = Core_QueryBuilder::select('property_value_ints.value')
						->from('property_value_ints')
						->join('shop_items', 'shop_items.id', '=', 'property_value_ints.entity_id')
						->open()
						->where('shop_items.active', '=', 1)
						->where('shop_items.modification_id', '=', 0)
						->where('shop_items.shop_group_id', '=', $shop_group_id);

					// Стандартные ограничения для товаров
					$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select);

					// Вывод модификаций на одном уровне в списке товаров
					if ($this->modificationsList)
					{
						$oShop = $this->getEntity();

						$oCore_QueryBuilder_Select_Modifications = Core_QueryBuilder::select('shop_items.id')
							->from('shop_items')
							->where('shop_items.shop_id', '=', $oShop->id)
							->where('shop_items.deleted', '=', 0)
							->where('shop_items.active', '=', 1)
							->where('shop_items.shop_group_id', '=', $shop_group_id);

						// Стандартные ограничения для товаров
						$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Modifications);

						Core_Event::notify(get_class($this) . '.onBeforeSelectModifications', $this, array($oCore_QueryBuilder_Select_Modifications));

						$oCore_QueryBuilder_Select
							->setOr()
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
								->where('shop_items.shop_group_id', '=', $shop_group_id)
								->where('shop_items.shortcut_id', '>', 0);

							$oCore_QueryBuilder_Select
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
			$sTitle = Core::_('Shop.edit_title');

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

		$iCurrentShopGroup = intval($this->group);

		$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll();

		$query_currency_switch = 'price';
		foreach ($aShop_Currencies as $oShop_Currency)
		{
			// Получаем коэффициент пересчета для каждой валюты
			$currency_coefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$oShop_Currency, $oShop->Shop_Currency
			);

			$query_currency_switch = "IF (`shop_items`.`shop_currency_id` = '{$oShop_Currency->id}', IF (shop_discounts.value, IF(shop_discounts.type, price * {$currency_coefficient} - shop_discounts.value, price * (100 - shop_discounts.value) * {$currency_coefficient} / 100), shop_items.price * {$currency_coefficient}), {$query_currency_switch})";
		}

		$current_date = date('Y-m-d H:i:s');

		$oSubMinMaxQueryBuilder = Core_QueryBuilder::select(array(Core_QueryBuilder::expression($query_currency_switch), 'absolute_price'))
			->from('shop_items')
			->where('shop_items.deleted', '=', 0)
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.active', '=', 1)
			->open()
			->where('shop_items.shop_group_id', '=', $iCurrentShopGroup)
			->where('shop_items.shortcut_id', '=', 0);

		if ($this->modificationsList)
		{
			$oCore_QueryBuilder_Select_Modifications = Core_QueryBuilder::select('shop_items.id')
				->from('shop_items')
				->where('shop_items.deleted', '=', 0)
				->where('shop_items.active', '=', 1)
				->where('shop_items.shop_group_id', '=', $iCurrentShopGroup);

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Modifications);

			$oSubMinMaxQueryBuilder
				->setOr()
				->where('shop_items.shop_group_id', '=', 0)
				->where('shop_items.modification_id', 'IN', $oCore_QueryBuilder_Select_Modifications);
		}

		if ($this->filterShortcuts)
		{
			$oCore_QueryBuilder_Select_Shortcuts = Core_QueryBuilder::select('shop_items.shortcut_id')
				->from('shop_items')
				->where('shop_items.deleted', '=', 0)
				->where('shop_items.active', '=', 1)
				->where('shop_items.shop_group_id', '=', $iCurrentShopGroup)
				->where('shop_items.shortcut_id', '>', 0);

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

			$oSubMinMaxQueryBuilder
				->setOr()
				->where('shop_items.id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);
		}

		$oSubMinMaxQueryBuilder
			->close()
			->leftJoin('shop_item_discounts', 'shop_items.id', '=', 'shop_item_discounts.shop_item_id')
			->leftJoin('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id', array(
				array('AND (' => array('shop_discounts.end_datetime', '>=', $current_date)),
				array('OR' => array('shop_discounts.end_datetime', '=', '0000-00-00 00:00:00')),
				array('AND' => array('shop_discounts.start_datetime', '<=', $current_date)),
				array(')' => NULL)
			))
			->groupBy('shop_items.id');

		$oMinMaxQueryBuilder = Core_QueryBuilder::select(
			array(Core_QueryBuilder::expression('MIN(t.absolute_price)'), 'min'),
			array(Core_QueryBuilder::expression('MAX(t.absolute_price)'), 'max')
		)
		->from(array($oSubMinMaxQueryBuilder, 't'));

		$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

		$oShop_Controller = Shop_Controller::instance();

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('min_price')
				->value(
					floor($rows['min'])
				)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('max_price')
				->value(
					ceil($rows['max'])
				)
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
}