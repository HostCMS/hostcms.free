<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ магазина.
 *
 * Доступные методы:
 *
 * - group($id|array) идентификатор группы магазина или массив идентификаторов, если FALSE, то вывод товаров осуществляется из всех групп
 * - subgroups(TRUE|FALSE) отображать товары из подгрупп, доступно при указании в group() одного идентификатора родительской группы (не массива), по умолчанию FALSE
 * - groupsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств групп, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести
 * - groupsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств групп товаров, по умолчанию TRUE
 * - propertiesForGroups(array()) устанавливает дополнительное ограничение на вывод значений дополнительных свойств групп для массива идентификаторов групп (каким группам выводить доп. св-ва)
 * - groupsMode('tree') режим показа групп, может принимать следующие значения:
	none — не показывать группы,
	tree — показывать дерево групп и все группы на текущем уровне (по умолчанию),
	all — показывать все группы.
 * - item(123) идентификатор показываемого товара
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE. Ограничения на список свойств в виде массива влияет и на выборку значений свойств товара.
 * - sortPropertiesValues(TRUE|FALSE) сортировать значения дополнительных свойств, по умолчанию TRUE.
 * - commentsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств комментариев, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - commentsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств комментариев, по умолчанию TRUE. Ограничения на список свойств в виде массива влияет и на выборку значений свойств товара.
 * - itemsPropertiesListJustAvailable(TRUE|FALSE) выводить только доступные значения у свойства. При использовании быстрого фильтра и включенном filterCounts(TRUE) будут выводиться доступные значения с учетом заданных фильтру ограничений, в противном случае будут выбираться значения, доступные товарам группы без учета заданных фильтру ограничений, по умолчанию FALSE
 * - groupsMedia(TRUE|FALSE) выводить значения библиотеки файлов для групп, по умолчанию FALSE
 * - itemsMedia(TRUE|FALSE) выводить значения библиотеки файлов для товаров, по умолчанию FALSE
 * - warehouseMode('all'|'in-stock'|'in-stock-modification'|'out-of-stock') режим вывода товаров:
	'all' — все (по умолчанию),
	'in-stock' — на складе,
	'in-stock-modification' — на складе или модификация товара в наличии на складе,
	'out-of-stock' — отсутствует на складе.
 * - parentItem(123) идентификатор родительского товара для отображаемой модификации
 * - modifications(TRUE|FALSE) показывать модификации для выбранных товаров, по умолчанию FALSE
 * - modificationsList(TRUE|FALSE) показывать модификации товаров текущей группы на уровне товаров группы, по умолчанию FALSE
 * - modificationsGroup(TRUE|FALSE) группировать и показывать родительский товар вместо модификаций, необходимо использовать совместно с modificationsList(TRUE), по умолчанию FALSE
 * - filterShortcuts(TRUE|FALSE) выбирать ярлыки товаров текущей группы на уровне товаров группы, по умолчанию FALSE. Используется для фильтрации по дополнительным свойствам
 * - parseFilterInUrl(TRUE|FALSE) разбирать условия фильтра из строки запроса, по умолчанию TRUE
 * - addFilter() добавить условие отобра товаров, может задавать условие отобра по цене ->addFilter('price', '>', 100), по значению свойства ->addFilter('property', 17, '=', 1) или по основному свойству, например, ->addFilter('weight', '>=', 50)
 * - filterCounts(TRUE|FALSE) производить подсчет количества соответсвующих свойству значений в текущей группе при использовании быстрого фильтра, по умолчанию FALSE
 * - filterStrictMode(TRUE|FALSE|array()) фильтровать только по существующим значениям (кроме списков и checkbox), отсутствие значения считать неверным значением. Если указан массив с идентификаторами свойств, то только для них будет использоваться строгий режим, по умолчанию FALSE
 * - specialprices(TRUE|FALSE) показывать специальные цены для выбранных товаров, по умолчанию FALSE
 * - seoFilters(TRUE|FALSE) показывать подходящие для текущей группы SEO-фильтры, по умолчанию FALSE
 * - associatedItems(TRUE|FALSE) показывать сопутствующие товары для выбранных товаров, по умолчанию FALSE
 * - comments(TRUE|FALSE) показывать комментарии для выбранных товаров, по умолчанию FALSE
 * - commentsRating(TRUE|FALSE) показывать оценки комментариев для выбранных товаров, по умолчанию FALSE
 * - tabs(TRUE|FALSE) показывать вкладки для выбранных товаров и групп, по умолчанию FALSE
 * - votes(TRUE|FALSE) показывать рейтинг элемента, по умолчанию TRUE
 * - tags(TRUE|FALSE) выводить метки, по умолчанию FALSE
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
 * - offset($offset) смещение, с которого выводить товары, по умолчанию 0
 * - limit($limit) количество выводимых товаров, по умолчанию 10
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
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 * - getItemDataCallback($callback) callback-функция для обработки массива товаров для метода getData()/getJson(), по умолчанию _getItemData() текущего контроллера
 * - getGroupDataCallback($callback) callback-функция для обработки массива групп для метода getData()/getJson(), по умолчанию _getGroupData() текущего контроллера
 *
 * Устаревшие методы:
 *
 * - groupsForbiddenTags(array('description')) массив тегов групп, запрещенных к передаче в генерируемый XML
 * - itemsForbiddenTags(array('description')) массив тегов товаров, запрещенных к передаче в генерируемый XML
 *
 * Доступные свойства:
 *
 * - total общее количество доступных для отображения записей
 * - patternParams массив данных, извелеченных из URI при применении pattern
 * - filterSeo примененный Shop_Filter_Seo
 * - getShownIDs() получить идентификаторы показанных товаров
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/shop' Магазин
 * - '/shop/shop_group' Группы магазина
 * - '/shop/shop_item' Товары магазина
 * - '/shop/comparing/shop_item' Сравниваемые товары, если не указаны, используются правила для '/shop/shop_item'
 * - '/shop/favorite/shop_item' Избранные товары, если не указаны, используются правила для '/shop/shop_item'
 * - '/shop/viewed/shop_item' Просмотренные товары, если не указаны, используются правила для '/shop/shop_item'
 * - '/shop/items_in_cart/shop_item' Товары в корзине, если не указаны, используются правила для '/shop/shop_item'
 * - '/shop/shop_item/shop_item' Родительский товар модификации, если не указаны, используются правила для '/shop/shop_item'
 * - '/shop/shop_filter_seos/shop_filter_seo' Доступные SEO-фильтры
 * - '/shop/tag' Примененный Tag для фильтрации
 * - '/shop/shop_producer' Примененный производитель для фильтрации
 * - '/shop/shop_filter_seo' Примененный SEO-фильтр
 * - '/shop/shop_group_properties/property' Свойство в списке свойств группы
 * - '/shop/shop_group_properties/property_dir' Раздел свойств в списке свойств группы
 * - '/shop/shop_item_properties/property' Свойство в списке свойств товара
 * - '/shop/shop_item_properties/property_dir' Раздел свойств в списке свойств товара
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
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'group',
		'subgroups',
		'groupsProperties',
		'groupsPropertiesList',
		'propertiesForGroups',
		'groupsMode',
		'groupsForbiddenTags',
		'item',
		'itemsProperties',
		'itemsPropertiesList',
		'sortPropertiesValues',
		'commentsProperties',
		'commentsPropertiesList',
		'itemsPropertiesListJustAvailable',
		'groupsMedia',
		'itemsMedia',
		'itemsForbiddenTags',
		'warehouseMode',
		'parentItem',
		'modifications',
		'modificationsList',
		'modificationsGroup',
		'filterShortcuts',
		'parseFilterInUrl',
		'filterCounts',
		'filterStrictMode',
		'specialprices',
		'seoFilters',
		'associatedItems',
		'comments',
		'commentsRating',
		'tabs',
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
		'patternAffiliate',
		'patternTag',
		'patternProducer',
		'filterSeo',
		'tag',
		'producer',
		'cache',
		'itemsActivity',
		'groupsActivity',
		'commentsActivity',
		'calculateTotal',
		'showPanel',
		'url',
		'getItemDataCallback',
		'getGroupDataCallback'
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
	 * @var boolean
	 */
	protected $_selectModifications = TRUE;

	/**
	 * Main proprties available for filter
	 * @var array
	 */
	protected $_aFilterAvailableMainValues = array('length', 'width', 'height', 'weight');

	/**
	 * Current Tag
	 * @var NULL|Tag_Model
	 */
	protected $_oTag = NULL;

	/**
	 * Error code
	 * @var int|NULL
	 */
	protected $_errorCode = NULL;

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
		$this->groupsProperties = $this->itemsProperties = $this->commentsProperties = $this->propertiesForGroups
			= $this->comments = $this->commentsRating = $this->tags = $this->calculateCounts = $this->siteuserProperties
			= $this->warehousesItems = $this->taxes = $this->cart = $this->modifications
			= $this->specialprices = $this->modificationsList = $this->modificationsGroup = $this->filterShortcuts
			= $this->itemsPropertiesListJustAvailable = $this->barcodes = $this->filterCounts
			= $this->seoFilters = $this->tabs = $this->filterStrictMode = $this->groupsMedia = $this->itemsMedia = FALSE;

		$this->siteuser = $this->cache = $this->itemsPropertiesList = $this->commentsPropertiesList = $this->groupsPropertiesList
			= $this->bonuses = $this->sets = $this->comparing = $this->favorite = $this->viewed
			= $this->votes = $this->showPanel = $this->calculateTotal = $this->sortPropertiesValues
			= $this->patternAffiliate = $this->patternTag = $this->patternProducer = $this->parseFilterInUrl = TRUE;

		$this->viewedLimit = $this->comparingLimit = $this->favoriteLimit = 10;

		$this->favoriteOrder = 'RAND';
		$this->viewedOrder = 'DESC';

		$this->groupsMode = 'tree';
		$this->warehouseMode = 'all';

		$this->itemsActivity = $this->groupsActivity = $this->commentsActivity = 'active'; // inactive, all

		// see parseUrl()
		//$this->pattern = $this->getPattern();

		$this->patternExpressions = array(
			'page' => '\d+',
			'producer' => '\d+',
		);

		$this->url = Core::$url['path'];

		$this->getItemDataCallback = array($this, '_getItemData');
		$this->getGroupDataCallback = array($this, '_getGroupData');
	}

	/**
	 * Clone controller
	 * @return void
	 * @ignore
	 */
	public function __clone()
	{
		$this->_setShopItems()->_setShopGroups();
	}

	/**
	 * Get Pattern
	 * @return string
	 */
	public function getPattern()
	{
		// Named subpatterns {name} can consist of up to 32 alphanumeric characters and underscores, but must start with a non-digit.
		$pattern = rawurldecode(Core_Str::rtrimUri($this->getEntity()->Structure->getPath())) . '({path}/)';

		$this->patternAffiliate && $pattern .= '(user-{user}/)';
		$pattern .= '(page-{page}/)';
		$this->patternTag && $pattern .= '(tag/{tag}/)';
		$this->patternProducer && $pattern .= '(producer-{producer}/)';

		return $pattern;
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
				$this->_sortingDirection = 'DESC';
			break;
			case 0:
			default:
				$this->_sortingDirection = 'ASC';
		}

		$this->_Shop_Items
			->queryBuilder()
			->clearOrderBy();

		// Определяем поле сортировки товаров
		switch ($oShop->items_sorting_field)
		{
			case 1:
				$this->_sortingColumn = 'shop_items.name';

				$this->_Shop_Items
					->queryBuilder()
					->orderBy($this->_sortingColumn, $this->_sortingDirection)
					->orderBy('shop_items.id', 'DESC');
				break;
			case 2:
				$this->_sortingColumn = 'shop_items.sorting';

				$this->_Shop_Items
					->queryBuilder()
					->orderBy($this->_sortingColumn, $this->_sortingDirection)
					->orderBy('shop_items.name', 'ASC');
				break;
			case 0:
			default:
				$this->_sortingColumn = 'shop_items.datetime';

				$this->_Shop_Items
					->queryBuilder()
					->orderBy($this->_sortingColumn, $this->_sortingDirection);
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
	 * @see _applyWarehouseConditionsQueryBuilder()
	 */
	protected function _applyWarehouseConditions(Shop_Item_Model $oShop_Items)
	{
		$fieldName = $this->isShopFilterJoined($oShop_Items->queryBuilder())
			? $this->getFilterTableName() . '.shop_item_id'
			: 'shop_items.id';

		return $this->_applyWarehouseConditionsQueryBuilder(
			$oShop_Items->queryBuilder(), $fieldName, !$this->modificationsGroup
		);
	}

	/**
	 * Apply warehouse's conditions
	 *
	 * @param Core_QueryBuilder_Select $oCore_QueryBuilder_Select
	 * @param string $fieldName, default 'shop_items.id'
	 * @return self
	 */
	protected function _applyWarehouseConditionsQueryBuilder(Core_QueryBuilder_Select $oCore_QueryBuilder_Select, $fieldName = 'shop_items.id', $groupBy = TRUE)
	{
		switch ($this->warehouseMode)
		{
			case 'in-stock':
				if ($this->isShopFilterJoined($oCore_QueryBuilder_Select))
				{
					$oCore_QueryBuilder_Select->where('available', '>', 0);
				}
				else
				{
					$oCore_QueryBuilder_Select
						->join('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', $fieldName)
						->join('shop_warehouses', 'shop_warehouses.id', '=', 'shop_warehouse_items.shop_warehouse_id')
						->where('shop_warehouses.active', '=', 1)
						->where('shop_warehouses.deleted', '=', 0)
						->having('SUM(shop_warehouse_items.count)', '>', 0);

					//!$this->modificationsGroup
					$groupBy
						&& $oCore_QueryBuilder_Select->groupBy($fieldName);
				}
			break;
			case 'in-stock-modification':
				if ($this->isShopFilterJoined($oCore_QueryBuilder_Select))
				{
					$oCore_QueryBuilder_Select->where('available', '>', 0);
				}
				else
				{
					$oCore_QueryBuilder_Select
						// Модификации и остатки на складах модификаций
						->leftJoin(array('shop_items', 'modifications'), 'modifications.modification_id', '=', $fieldName)
						->leftJoin(array('shop_warehouse_items', 'modifications_shop_warehouse_items'), 'modifications_shop_warehouse_items.shop_item_id', '=', 'modifications.id')
						// Остатки на складах основного товара
						->leftJoin('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', $fieldName)
						->havingOpen()
							// есть остатки на основном складе
							->having('SUM(shop_warehouse_items.count)', '>', 0)
							->setOr()
							// или есть остатки на складах у модификаций
							->having('SUM(modifications_shop_warehouse_items.count)', '>', 0)
						->havingClose();

					//!$this->modificationsGroup
					$groupBy
						&& $oCore_QueryBuilder_Select->groupBy($fieldName);
				}
			break;
			case 'out-of-stock':
				if ($this->isShopFilterJoined($oCore_QueryBuilder_Select))
				{
					$oCore_QueryBuilder_Select->where('available', '<=', 0);
				}
				else
				{
					$oCore_QueryBuilder_Select
						->join('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', $fieldName)
						->join('shop_warehouses', 'shop_warehouses.id', '=', 'shop_warehouse_items.shop_warehouse_id')
						->where('shop_warehouses.active', '=', 1)
						->where('shop_warehouses.deleted', '=', 0)
						->having('SUM(shop_warehouse_items.count)', '<=', 0);

					//!$this->modificationsGroup
					$groupBy
						&& $oCore_QueryBuilder_Select->groupBy($fieldName);
				}
			break;
		}

		return $this;
	}

	/**
	 * Apply item's conditions
	 *
	 * @param Shop_Item_Model $oShop_Items
	 * @return self
	 * @see _applyItemConditionsQueryBuilder()
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
	 * @param string $tableName, default 'shop_items'
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

		$aComparing = array();

		$aShop_Compares = Shop_Compare_Controller::instance()->getAll($oShop);
		foreach ($aShop_Compares as $oShop_Compare)
		{
			$aComparing[] = $oShop_Compare->shop_item_id;
		}

		if (count($aComparing))
		{
			// Extract a slice of the array
			$aComparing = array_slice($aComparing, 0, $this->comparingLimit, TRUE);

			if ($this->_mode != 'tpl')
			{
				$this->addEntity(
					$oCompareEntity = Core::factory('Core_Xml_Entity')
						->name('comparing')
				);

				foreach ($aComparing as $shop_item_id)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id);
					if (!is_null($oShop_Item->id))
					{
						$oCompare_Shop_Item = clone $oShop_Item;
						$oCompare_Shop_Item
							->id($oShop_Item->id)
							->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues)
							->showXmlBonuses($this->bonuses)
							->showXmlSpecialprices($this->specialprices);

						// Media
						$this->itemsMedia
							&& $oCompare_Shop_Item->showXmlMedia($this->itemsMedia);

						!$this->sets && $oShop_Item->showXmlSets($this->sets);

						//$this->applyItemsForbiddenTags($oCompare_Shop_Item);
						$this->applyForbiddenAllowedTags('/shop/comparing/shop_item|/shop/shop_item', $oCompare_Shop_Item);

						Core_Event::notify(get_class($this) . '.onBeforeAddCompareEntity', $this, array($oCompare_Shop_Item));

						$oCompareEntity->addEntity($oCompare_Shop_Item);
					}
				}
			}
			else
			{
				$this->append('aComparing', $aComparing);
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

		$aFavorite = array();

		$aShop_Favorites = Shop_Favorite_Controller::instance()->getAll($oShop);
		foreach ($aShop_Favorites as $oShop_Favorite)
		{
			$aFavorite[] = $oShop_Favorite->shop_item_id;
		}

		if (count($aFavorite))
		{
			switch ($this->favoriteOrder)
			{
				case 'RAND':
					shuffle($aFavorite);
				break;
				case 'ASC':
					asort($aFavorite);
				break;
				case 'DESC':
					arsort($aFavorite);
				break;
				default:
					throw new Core_Exception("The favoriteOrder direction '%direction' doesn't allow",
						array('%direction' => $this->favoriteOrder)
					);
			}

			// Extract a slice of the array
			$aFavorite = array_slice($aFavorite, 0, $this->favoriteLimit);

			if ($this->_mode != 'tpl')
			{
				$this->addEntity(
					$oFavouriteEntity = Core::factory('Core_Xml_Entity')
						->name('favorite')
				);

				foreach ($aFavorite as $shop_item_id)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id, FALSE);
					if (!is_null($oShop_Item->id))
					{
						$oFavorite_Shop_Item = clone $oShop_Item;
						$oFavorite_Shop_Item
							->id($oShop_Item->id)
							->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues)
							->showXmlBonuses($this->bonuses)
							->showXmlSpecialprices($this->specialprices);

						!$this->sets && $oFavorite_Shop_Item->showXmlSets($this->sets);

						// Media
						$this->itemsMedia
							&& $oFavorite_Shop_Item->showXmlMedia($this->itemsMedia);

						//$this->applyItemsForbiddenTags($oFavorite_Shop_Item);
						$this->applyForbiddenAllowedTags('/shop/favorite/shop_item|/shop/shop_item', $oFavorite_Shop_Item);

						Core_Event::notify(get_class($this) . '.onBeforeAddFavoriteEntity', $this, array($oFavorite_Shop_Item));

						$oFavouriteEntity->addEntity($oFavorite_Shop_Item);
					}
				}
			}
			else
			{
				$this->append('aFavorite', $aFavorite);
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

		$aViewed = Core_Array::get(Core_Array::getSession('hostcmsViewed', array()), $oShop->id, array());

		if (count($aViewed))
		{
			switch ($this->viewedOrder)
			{
				case 'RAND':
					shuffle($aViewed);
				break;
				case 'ASC':
					ksort($aViewed);
				break;
				case 'DESC':
					krsort($aViewed);
				break;
				default:
					throw new Core_Exception("The viewedOrder direction '%direction' doesn't allow",
						array('%direction' => $this->viewedOrder)
					);
			}

			// Delete current item
			if (($currentKey = array_search($this->item, $aViewed)) !== FALSE)
			{
				unset($aViewed[$currentKey]);
			}

			// Extract a slice of the array
			$aViewed = array_slice($aViewed, 0, $this->viewedLimit);

			if ($this->_mode != 'tpl')
			{
				$this->addEntity(
					$oViewedEntity = Core::factory('Core_Xml_Entity')
						->name('viewed')
				);

				foreach ($aViewed as $view_item_id)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->find($view_item_id, FALSE);

					if (!is_null($oShop_Item->id) /*&& $oShop_Item->id != $this->item*/ && $oShop_Item->active)
					{
						$oViewed_Shop_Item = clone $oShop_Item;
						$oViewed_Shop_Item
							->id($oShop_Item->id)
							->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues)
							->showXmlComments($this->comments)
							->showXmlCommentsRating($this->commentsRating)
							->showXmlModifications($this->modifications)
							->showXmlBonuses($this->bonuses)
							->showXmlSpecialprices($this->specialprices);

						//$this->applyItemsForbiddenTags($oViewed_Shop_Item);
						$this->applyForbiddenAllowedTags('/shop/viewed/shop_item|/shop/shop_item', $oViewed_Shop_Item);

						!$this->sets && $oViewed_Shop_Item->showXmlSets($this->sets);

						// Media
						$this->itemsMedia
							&& $oViewed_Shop_Item->showXmlMedia($this->itemsMedia);

						Core_Event::notify(get_class($this) . '.onBeforeAddViewedEntity', $this, array($oViewed_Shop_Item));

						$oViewedEntity->addEntity($oViewed_Shop_Item);
					}
				}
			}
			else
			{
				$this->append('aViewed', $aViewed);
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
	 * Get SEO Items's H1
	 * @return string|NULL
	 */
	public function getSeoItemH1()
	{
		if ($this->_seoItemH1 != '')
		{
			$oShop = $this->getEntity();

			$oShop_Item = Core_Entity::factory('Shop_Item', $this->item);
			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('shop', $oShop)
				->addObject('group', $oShop_Item->Shop_Group)
				->addObject('item', $oShop_Item)
				->addObject('this', $this);

			return $oCore_Meta->apply($this->_seoItemH1);
		}

		return NULL;
	}

	/**
	 * Get SEO Group's H1
	 * @return string|NULL
	 */
	public function getSeoGroupH1()
	{
		if ($this->_seoGroupH1 != '')
		{
			$oShop = $this->getEntity();

			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('shop', $oShop)
				->addObject('this', $this);

			if ($this->group)
			{
				$oShop_Group = Core_Entity::factory('Shop_Group', $this->group);
				$oCore_Meta->addObject('group', $oShop_Group);
			}

			return $oCore_Meta->apply($this->_seoGroupH1);
		}

		return NULL;
	}

	/**
	 * Prepare date
	 * @return array
	 */
	public function getData()
	{
		$this->total = 0;

		if ($this->_errorCode)
		{
			$aReturn = array(
				'error' => array(
					'code' => $this->_errorCode
				)
			);

			switch ($this->_errorCode)
			{
				case 403:
					$aReturn['error']['message'] = "Access forbidden, url '{$this->url}'";
				break;
				case 410:
				case 404:
					$aReturn['error']['message'] = "Path '{$this->url}' Not Found";
				break;
				default:
					$aReturn['error']['message'] = "Unknown Error";
				break;
			}

			return $aReturn;
		}

		$oShop = $this->getEntity();

		if ($this->item)
		{
			$model = 'shop_item';
			$id = $this->item;
			//$oShop_Item = Core_Entity::factory('')
		}
		elseif ($this->group)
		{
			$model = 'shop_group';
			$id = $this->group;
		}
		else
		{
			$model = 'shop';
			$id = $oShop->id;
		}

		// До applyFilter()
		if (!is_null($this->tag) && Core::moduleIsActive('tag'))
		{
			// Заново получаем $this->_oTag, т.к. он может быть изменен между parseUrl() и show()
			$this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);
		}

		// Независимо от limit, т.к. может использоваться отдельно для фильтра
		!$this->item
			&& $this->applyFilter();

		// До вывода свойств групп
		if ($this->limit > 0 || $this->item)
		{
			$aShop_Items = $this->getShopItems();

			if (!$this->item)
			{
				/*if ($this->page && !count($aShop_Items))
				{
					//return $this->error404();
				}*/

				if ($this->calculateTotal)
				{
					$this->total = Core_QueryBuilder::select()->getFoundRows();
				}
			}
		}
		else
		{
			$aShop_Items = array();
		}

		// Строка навигации
		$aBreadcrumbs = array();
		if ($this->group)
		{
			$shopUrl = $oShop->Structure->getPath();
			$oShop_Group = Core_Entity::factory('Shop_Group', $this->group);

			do {
				$aBreadcrumbs[] = array(
					'id' => $oShop_Group->id,
					'name' => $oShop_Group->name,
					'url' => $shopUrl . $oShop_Group->getPath(),
					'path' => $oShop_Group->path
				);
				$oShop_Group = $oShop_Group->getParent();
			} while ($oShop_Group);
		}

		$aReturn = array(
			'entity' => array(
				'model' => $model,
				'id' => $id
			),
			'pagination' => array(
				'total' => intval($this->total),
				'pages' => $this->limit > 0 ? ceil($this->total / $this->limit) : 0,
				'current' => intval($this->page) + 1,
				'limit' => $this->limit
			),
			'url' => $this->url,
			'breadcrumbs' => array_reverse($aBreadcrumbs),
			'seo' => array(
				'seoItemH1' => $this->getSeoItemH1(),
				'seoGroupH1' => $this->getSeoGroupH1()
			),
			'sorting' => $oShop->items_sorting_field,
			'sorting_direction' => $oShop->items_sorting_direction,
		);

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

		$aShop_Grpoups = call_user_func_array('array_merge', $this->_aShop_Groups);

		// Группы
		$aReturn['groups'] = call_user_func($this->getGroupDataCallback, $aShop_Grpoups);

		// Товары
		$aReturn['items'] = call_user_func($this->getItemDataCallback, $aShop_Items);

		if ($this->itemsProperties || $this->itemsPropertiesList)
		{
			$aShowPropertyIDs = $this->_itemsProperties();

			$aProperties = array();

			foreach ($this->_aItem_Properties as $property_dir_id => $aTmp_Properties)
			{
				foreach ($aTmp_Properties as $oProperty)
				{
					// Свойство разрешено к показу в текущем режиме
					if (in_array($oProperty->id, $aShowPropertyIDs))
					{
						$aProperties[] = $oProperty;
					}
				}
			}

			$aReturn['properties'] = $this->_getPropertyData($aProperties);
		}

		// Filter
		foreach ($this->_aFilterProperties as $property_id => $aTmpProperties)
		{
			foreach ($aTmpProperties as $aTmpProperty)
			{
				list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;

				foreach ($aPropertyValues as $propertyValue)
				{
					$aReturn['filter']['properties'][$property_id][] = array($condition, $propertyValue);
				}
			}
		}

		foreach ($this->_aFilterPrices as $aFilterPrice)
		{
			$aReturn['filter']['prices'][] = array($aFilterPrice[0], $aFilterPrice[1]);
		}

		foreach ($this->_aFilterMainProperties as $mainPropertyName => $aMainPropertyValues)
		{
			foreach ($aMainPropertyValues as $aMainPropertyValue)
			{
				$aReturn['filter'][$mainPropertyName][] = array($aMainPropertyValue[0], $aMainPropertyValue[1]);
			}
		}

		return $aReturn;
	}

	/**
	 * Typical conversion of Shop_Items to an array
	 * @param array $aShop_Items
	 * @return array
	 */
	protected function _getItemData(array $aShop_Items)
	{
		$aReturn = array();
		foreach ($aShop_Items as $oShop_Item)
		{
			$aReturn[] = $oShop_Item->toArray();
		}

		return $aReturn;
	}

	/**
	 * Typical conversion of Shop_Groups to an array
	 * @param array $aShop_Items
	 * @return array
	 */
	protected function _getGroupData(array $aShop_Grpoups)
	{
		$aReturn = array();
		foreach ($aShop_Grpoups as $oShop_Grpoup)
		{
			$aReturn[] = $oShop_Grpoup->toArray();
		}

		return $aReturn;
	}

	/**
	 * Cache values for filterCounts
	 * @var array
	 */
	protected $_itemsPropertiesListCount = array();

	/**
	 * Typical conversion of Property to an array
	 * @param array $aProperties
	 * @return array
	 */
	protected function _getPropertyData(array $aProperties)
	{
		$aReturn = array();
		foreach ($aProperties as $oProperty)
		{
			$aProperty = $oProperty->toArray();

			if ($oProperty->type == 3 && $oProperty->list_id && Core::moduleIsActive('list'))
			{
				if ($this->itemsPropertiesListJustAvailable
					// 3 - List
					//&& $oProperty->type == 3 && $oProperty->list_id
					// 0 - Hide; 1 - Input; 2,3,4 - Select
					&& $oProperty->Shop_Item_Property->filter > 1)
				{
					$this->_setLimitListItems($oProperty);
				}
				elseif ($this->itemsPropertiesListJustAvailable)
				{
					// Запрещаем показ элементов списка
					$oProperty->limitListItems(array());
				}

				$aList_Items = $oProperty->getListItems();
				$aProperty['values'] = $this->_getListItemData($aList_Items);

				// Counts
				if (isset($this->_itemsPropertiesListCount[$oProperty->id]))
				{
					foreach ($this->_itemsPropertiesListCount[$oProperty->id] as $value => $count)
					{
						$aProperty['counts'][$value] = $count;
					}
				}
			}

			$aReturn[] = $aProperty;
		}

		return $aReturn;
	}

	/**
	 * Typical conversion of List_Item to an array
	 * @param array $aList_Items
	 * @return array
	 */
	protected function _getListItemData(array $aList_Items)
	{
		$aReturn = array();
		foreach ($aList_Items as $oList_Item)
		{
			$aReturn[] = $oList_Item->toArray();
		}

		return $aReturn;
	}

	/**
	 * Get JSON
	 * @return string
	 */
	public function getJson()
	{
		return json_encode(
			$this->getData()
		);
	}

	/**
	 * Get Shop_Items
	 * @return array
	 * @hostcms-event Shop_Controller_Show.onBeforeSelectShopItems
	 */
	public function getShopItems()
	{
		$this->applyItemCondition();

		if (!$this->item)
		{
			// Group condition for shop item
			if ($this->group !== FALSE)
			{
				$this->applyGroupCondition();
			}
			else
			{
				// при выборе из всего магазина ярлыки не требуются, так как будут присутствовать оригинальные товары
				$this->forbidSelectShortcuts();

				$this->_Shop_Items
					->queryBuilder()
					->leftJoin(array('shop_groups', 'sg'), 'sg.id', '=', 'shop_items.shop_group_id')
					// Активность группы или группа корневая
					->open()
						->where('sg.active', '=', 1)
						->where('sg.deleted', '=', 0)
						->where('sg.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
						->setOr()
						->where('sg.id', 'IS', NULL)
					->close();
			}

			if ($this->modificationsGroup)
			{
				$this->_Shop_Items
					->queryBuilder()
					->select(array(Core_QueryBuilder::expression('IF(`shop_items`.`modification_id` > 0, `shop_items`.`modification_id`, `shop_items`.`id`)'), 'dataTmpId'))
					->clearGroupBy()
					->groupBy('dataTmpId');
			}

			$this->_setLimits();

			$this->_Shop_Items
				->queryBuilder()
				->where('shop_items.closed', '=', 0);
		}

		// Apply $this->warehouseMode
		$this->_applyWarehouseConditions($this->_Shop_Items);

		Core_Event::notify(get_class($this) . '.onBeforeSelectShopItems', $this, array($this->_Shop_Items));

		// Disable cache (objectWatcher) while modificationsGroup because we use dataTmpId
		return $this->_Shop_Items->findAll(!$this->modificationsGroup);
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Controller_Show.onBeforeRedeclaredShow
	 * @hostcms-event Shop_Controller_Show.onBeforeAddGroupsPropertiesList
	 * @hostcms-event Shop_Controller_Show.onBeforeAddShopItems
	 * @hostcms-event Shop_Controller_Show.onBeforeAddShopItem
	 * @hostcms-event Shop_Controller_Show.onAfterAddShopItems
	 * @hostcms-event Shop_Controller_Show.onBeforeAddCartItem
	 * @hostcms-event Shop_Controller_Show.onBeforeAddShortcut
	 * @hostcms-event Shop_Controller_Show.onBeforeSelectSeoFilters
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		// Backward compatible
		is_array($this->groupsForbiddenTags) && count($this->groupsForbiddenTags)
			&& $this->addForbiddenTags('/shop/shop_group', $this->groupsForbiddenTags);

		is_array($this->itemsForbiddenTags) && count($this->itemsForbiddenTags)
			&& $this->addForbiddenTags('/shop/shop_item', $this->itemsForbiddenTags);

		// Move rules from '/shop' to the root
		$aAllowedTags = $this->getAllowedTags();
		isset($aAllowedTags['/shop']) && $this->addAllowedTags('/', $aAllowedTags['/shop']);

		$aForbiddenTags = $this->getForbiddenTags();
		isset($aForbiddenTags['/shop']) && $this->addForbiddenTags('/', $aForbiddenTags['/shop']);

		// Load user BEFORE FOUND_ROWS()
		$oUser = Core_Auth::getCurrentUser();

		$oShop = $this->getEntity();

		$this->showPanel && Core::checkPanel()
			&& in_array($this->_mode, array('xsl', 'tpl'))
			&& $oUser && $oUser->checkModuleAccess(array('shop'), $oShop->Site)
			&& $this->_showPanel();

		$this->group === 0 && $this->subgroups
			&& $this->group = FALSE;

		$hasSessionId = Core_Session::hasSessionId();

		// Before check cache
		if ($hasSessionId)
		{
			$isActive = Core_Session::isActive();
			!$isActive && Core_Session::start();

			if ($this->favorite)
			{
				$hostcmsFavorite = Core_Array::get(Core_Array::getSession('hostcmsFavorite', array()), $oShop->id, array());
				count($hostcmsFavorite) && $this->addCacheSignature('hostcmsFavorite=' . implode(',', $hostcmsFavorite));
			}

			if ($this->viewed)
			{
				$hostcmsViewed = Core_Array::get(Core_Array::getSession('hostcmsViewed', array()), $oShop->id, array());
				count($hostcmsViewed) && $this->addCacheSignature('hostcmsViewed=' . implode(',', $hostcmsViewed));
			}

			if (isset($_SESSION['hostcmsOrder']['coupon_text']))
			{
				$this->addCacheSignature('coupon=' . $_SESSION['hostcmsOrder']['coupon_text']);
				Shop_Item_Controller::coupon($_SESSION['hostcmsOrder']['coupon_text']);
			}
		}

		$this->item && $this->_incShowed();

		$this->total = 0;

		$bCache = $this->cache && Core::moduleIsActive('cache');
		if ($bCache)
		{
			foreach ($this->_aFilterProperties as $iPropertyId => $aTmpProperties)
			{
				foreach ($aTmpProperties as $aTmpProperty)
				{
					$this->addCacheSignature('property=' . $iPropertyId . ',' . $aTmpProperty[1] . ',' . implode('#', $aTmpProperty[2]));
				}
			}

			foreach ($this->_aFilterPrices as $aTmpPrice)
			{
				$this->addCacheSignature('price' . $aTmpPrice[0] . $aTmpPrice[1]);
			}

			foreach ($this->_aFilterMainProperties as $mainPropertyName => $aMainPropertyValues)
			{
				foreach ($aMainPropertyValues as $aMainPropertyValue)
				{
					$this->addCacheSignature($mainPropertyName . $aMainPropertyValue[0] . $aMainPropertyValue[1]);
				}
			}

			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$inCache = $oCore_Cache->get($cacheKey = strval($this), $this->_cacheName);

			if (is_array($inCache))
			{
				$this->_shownIDs = $inCache['shown'];
				echo $inCache['content'];
				return $this;
			}

			if (is_array($this->group))
			{
				foreach ($this->group as $cacheGroupId)
				{
					$this->_cacheTags[] = 'shop_group_' . intval($cacheGroupId);
				}
			}
			else
			{
				$this->_cacheTags[] = 'shop_group_' . intval($this->group);
			}
		}

		$bTpl = $this->_mode == 'tpl';

		$oShop->showXmlCounts($this->calculateCounts);

		$this->taxes && $oShop->showXmlTaxes(TRUE);

		$oCore_Xml_Entity_Group = Core::factory('Core_Xml_Entity')
			->name('group')
			->value(is_array($this->group) ? Core_Array::first($this->group) : intval($this->group)); // FALSE => 0

		if (is_array($this->group))
		{
			$oCore_Xml_Entity_Group->addAttribute('all', implode(',', $this->group));
		}

		$this->addEntity(
			$oCore_Xml_Entity_Group
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('page')
				->value(intval($this->page))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		if (!is_null($this->producer))
		{
			$aProducers = is_array($this->producer)
				? $this->producer
				: array($this->producer);

			foreach ($aProducers as $producer_id)
			{
				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('producer_id')
						->value(intval($producer_id))
				);
			}
		}

		if ($this->item)
		{
			$seoItemH1 = $this->getSeoItemH1();

			if (!is_null($seoItemH1))
			{
				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('seo_item_h1')
						->value($seoItemH1)
				);
			}
		}
		else
		{
			$seoGroupH1 = $this->getSeoGroupH1();

			if (!is_null($seoGroupH1))
			{
				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('seo_group_h1')
						->value($seoGroupH1)
				);
			}
		}

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
							$oShop_Item_Into_Cart = clone $oShop_Item;
							$oShop_Item_Into_Cart->id($oShop_Item->id);

							Core_Event::notify(get_class($this).'.onBeforeAddCartItem', $this, array($oShop_Item_Into_Cart));

							//$this->applyItemsForbiddenTags($oShop_Item_Into_Cart->clearEntities());
							$this->applyForbiddenAllowedTags('/shop/items_in_cart/shop_item|/shop/shop_item', $oShop_Item_Into_Cart);

							$this->itemsProperties
								&& $oShop_Item_Into_Cart->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues);

							// Media
							$this->itemsMedia
								&& $oShop_Item_Into_Cart->showXmlMedia($this->itemsMedia);

							!$this->sets && $oShop_Item_Into_Cart->showXmlSets($this->sets);

							$oShop_Item_Into_Cart->itemsActivity($this->itemsActivity);

							$oCartEntity->addEntity($oShop_Item_Into_Cart);
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

		if (!$this->checkPaginationConditions())
		{
			return $this->error404();
		}

		// До applyFilter()
		if (!is_null($this->tag) && Core::moduleIsActive('tag'))
		{
			// Заново получаем $this->_oTag, т.к. он может быть изменен между parseUrl() и show()
			$this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

			if ($this->_oTag)
			{
				$this->applyForbiddenAllowedTags('/shop/tag', $this->_oTag);
				$this->addEntity($this->_oTag);
			}
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
			$aShop_Items = $this->getShopItems();

			if (!$this->item)
			{
				if (!count($aShop_Items) && ($this->page || $this->_oTag))
				{
					return $this->error410();
				}

				is_null($this->tag) && $this->_selectModifications = FALSE;

				if ($this->calculateTotal)
				{
					$this->total = Core_QueryBuilder::select()->getFoundRows();

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
				$oProperty->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_group_properties/property', $oProperty);
				$this->_aGroup_Properties[$oProperty->property_dir_id][] = $oProperty;
			}

			$aProperty_Dirs = $oShop_Group_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_group_properties/property_dir', $oProperty_Dir);
				$this->_aGroup_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
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

		// Показывать дополнительные свойства комментариев
		if ($this->commentsProperties || $this->commentsPropertiesList)
		{
			$aShowCommentPropertyIDs = $this->_commentsProperties();
		}

		// Показывать SEO-фильтры
		if ($this->seoFilters && $this->group !== FALSE)
		{
			$oShop_Filter_Seos = $oShop->Shop_Filter_Seos;
			$oShop_Filter_Seos->queryBuilder()
				->select('shop_filter_seos.*')
				->where('shop_filter_seos.active', '=', 1);
				//->where('shop_filter_seos.shop_group_id', is_array($this->group) ? 'IN' : '=', $this->group);

			$this->applyFilterGroupCondition($oShop_Filter_Seos->queryBuilder(), 'shop_filter_seos.shop_group_id');

			Core_Event::notify(get_class($this) . '.onBeforeSelectSeoFilters', $this, array($oShop_Filter_Seos));

			$aShop_Filter_Seos = $oShop_Filter_Seos->findAll(FALSE);

			if (count($aShop_Filter_Seos))
			{
				$this->applyForbiddenAllowedTags('/shop/shop_filter_seos/shop_filter_seo', $aShop_Filter_Seos);

				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('shop_filter_seos')
						->addEntities($aShop_Filter_Seos)
				);
			}
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

			// Ярлык может ссылаться на товар с истекшим или не наступившим сроком публикации
			$iCurrentTimestamp = time();

			Core_Event::notify(get_class($this) . '.onBeforeAddShopItems', $this, array($aShop_Items));

			$aClone_Shop_Items = array();
			foreach ($aShop_Items as $oOriginal_Shop_Item)
			{
				$this->_shownIDs[] = $oOriginal_Shop_Item->id;

				// Tagged cache
				$bCache && $this->_cacheTags[] = 'shop_item_' . $oOriginal_Shop_Item->id;

				// Shortcut
				$iShortcut = $oOriginal_Shop_Item->shortcut_id;

				if ($iShortcut)
				{
					$oShortcut_Item = $oOriginal_Shop_Item;
					$oOriginal_Shop_Item = $oOriginal_Shop_Item->Shop_Item;
				}
				else // Так как ярлык не будет иметь dataTmpId и он будет NULL
				{
					// Group modifications in the parent item, both modificationsList and modificationsGroup need
					!$this->item && $this->modificationsList && $this->modificationsGroup && $oOriginal_Shop_Item->dataTmpId != $oOriginal_Shop_Item->id
						&& $oOriginal_Shop_Item = $oOriginal_Shop_Item->Modification;
				}

				$oShop_Item = clone $oOriginal_Shop_Item;
				$oShop_Item
					->id($oOriginal_Shop_Item->id)
					->clearEntities()
					// shortcodes may clear attached entities
					//->clearEntitiesAfterGetXml(FALSE)
					->cartQuantity(1)
					->itemsActivity($this->itemsActivity);

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
							//$oOriginal_Shop_Item = $oShop_Item;

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

							Core_Event::notify(get_class($this) . '.onBeforeAddShortcut', $this, array($oShop_Item, $oOriginal_Shop_Item));
						}

						$this->applyItemsForbiddenTags($oShop_Item);

						// Comments
						$oShop_Item
							->showXmlComments($this->comments)
							->showXmlCommentsRating($this->commentsRating)
							->commentsActivity($this->commentsActivity);

						$oShop_Item->showXmlTabs($this->tabs);
						$oShop_Item->showXmlBonuses($this->bonuses);
						$oShop_Item->showXmlBarcodes($this->barcodes);
						$oShop_Item->showXmlWarehousesItems($this->warehousesItems);
						$oShop_Item->showXmlAssociatedItems($this->associatedItems);
						$oShop_Item->showXmlModifications($this->modifications);
						$oShop_Item->showXmlSpecialprices($this->specialprices);
						$oShop_Item->showXmlTags($this->tags);
						$oShop_Item->showXmlVotes($this->votes);

						$oShop_Item->showXmlProperties($mShowPropertyIDs, $this->sortPropertiesValues);
						$oShop_Item->showXmlCommentProperties($mShowCommentPropertyIDs);

						!$this->sets && $oShop_Item->showXmlSets($this->sets);

						// Media
						$this->itemsMedia
							&& $oShop_Item->showXmlMedia($this->itemsMedia);

						// Siteuser
						$oShop_Item
							->showXmlSiteuser($this->siteuser)
							->showXmlSiteuserProperties($this->siteuserProperties);

						Core_Event::notify(get_class($this) . '.onBeforeAddShopItem', $this, array($oShop_Item));

						$this->addEntity($oShop_Item);

						// Parent item for modification
						if ($this->parentItem)
						{
							$oParentItem = Core_Entity::factory('Shop_Item', $this->parentItem)
								->showXmlProperties($mShowPropertyIDs, $this->sortPropertiesValues)
								->showXmlCommentProperties($mShowCommentPropertyIDs)
								->showXmlTags($this->tags)
								->showXmlWarehousesItems($this->warehousesItems)
								->showXmlAssociatedItems($this->associatedItems)
								->showXmlModifications($this->modifications)
								->showXmlSpecialprices($this->specialprices)
								->showXmlVotes($this->votes)
								->showXmlSets($this->sets);

							// Media
							$this->itemsMedia
								&& $oParentItem->showXmlMedia($this->itemsMedia);

							$this->applyForbiddenAllowedTags('/shop/shop_item/shop_item|/shop/shop_item', $oParentItem);

							$oShop_Item->addEntity($oParentItem);
						}
					}
				}
				else
				{
					$this->append('aShop_Items', $oShop_Item);
				}

				$aClone_Shop_Items[] = $oShop_Item;
			}

			unset($aShop_Items);

			Core_Event::notify(get_class($this) . '.onAfterAddShopItems', $this, array($aClone_Shop_Items));

			unset($aClone_Shop_Items);
		}

		echo $content = $this->get();

		$bCache && $oCore_Cache->set(
			$cacheKey,
			array('content' => $content, 'shown' => $this->_shownIDs),
			$this->_cacheName,
			$this->_cacheTags
		);

		// Clear
		$this->_aShop_Groups = $this->_aItem_Property_Dirs = $this->_aItem_Properties = $this->_aComment_Property_Dirs = $this->_aComment_Properties
			= $this->_aGroup_Properties = $this->_aGroup_Property_Dirs = $this->_cacheTags = $this->_itemsPropertiesListJustAvailable = array();

		return $this;
	}

	/**
	 * Check pagination conditions
	 * @return boolean
	 */
	public function checkPaginationConditions()
	{
		return !($this->limit == 0 && $this->page);
	}

	/**
	 * Apply Condition By Group, depends on $this->group, $this->subgroups
	 * @param Core_QueryBuilder_Select $oQueryBuilder
	 * @param string $fieldName
	 * @return self
	 */
	public function applyFilterGroupCondition($oQueryBuilder, $fieldName)
	{
		if (!$this->item && !is_null($this->tag) && Core::moduleIsActive('tag'))
		{
			// тег получен в parseUrl()
			is_null($this->_oTag)
				&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

			if ($this->_oTag)
			{
				// В корне при фильтрации по меткам вывод идет из всех групп
				$this->group == 0 && $this->group = FALSE;
			}
		}

		if ($this->group !== FALSE)
		{
			if ($this->subgroups)
			{
				// Fast filter + scalar group
				if ($this->getEntity()->filter && !is_array($this->group))
				{
					$method = $oQueryBuilder->isStraightJoin()
						? 'firstJoin'
						: 'join';

					$tableName = $this->getFilterGroupTableName();
					$oQueryBuilder->$method($tableName, $fieldName, '=', $tableName . '.child_id',
						array(
							array('AND' => array($tableName . '.shop_group_id', '=', $this->group))
						)
					);
				}
				else
				{
					$oQueryBuilder->where($fieldName, 'IN',
						!is_array($this->group)
							? $this->getSubgroups($this->group)
							: $this->group
					);
				}
			}
			else
			{
				$oQueryBuilder->where($fieldName, is_array($this->group) ? 'IN' : '=', $this->group);
			}
		}

		return $this;
	}

	/**
	 * List items's ID by fast filter for $this->itemsPropertiesListJustAvailable
	 */
	protected $_itemsPropertiesListJustAvailable = array();

	/**
	 * Add list of item properties
	 * @return array
	 * @hostcms-event Shop_Controller_Show.onBeforeAddItemsPropertiesList
	 */
	protected function _itemsProperties()
	{
		$aShowPropertyIDs = array();

		$oShop = $this->getEntity();

		$bTpl = $this->_mode == 'tpl';

		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

		//if ($this->itemsProperties)
		//{
		$aProperties = $this->group === FALSE
			? (is_array($this->itemsPropertiesList) && count($this->itemsPropertiesList)
				? $oShop_Item_Property_List->Properties->getAllByid($this->itemsPropertiesList, FALSE, 'IN')
				//: $oShop_Item_Property_List->Properties->findAll()
				: ($this->itemsPropertiesList === FALSE && is_array($this->itemsProperties) && count($this->itemsProperties)
					? $oShop_Item_Property_List->Properties->getAllByid($this->itemsProperties, FALSE, 'IN')
					: $oShop_Item_Property_List->Properties->findAll()
				)
			)
			: $oShop_Item_Property_List->getPropertiesForGroup($this->group && $this->subgroups && !is_array($this->group)
					? $this->getSubgroups($this->group)
					: $this->group
				, $this->itemsPropertiesList
			);

		foreach ($aProperties as $oProperty)
		{
			$oProperty->clearEntities();
			$this->applyForbiddenAllowedTags('/shop/shop_item_properties/property', $oProperty);

			$oShop_Item_Property = $oProperty->Shop_Item_Property;

			if ($oShop_Item_Property->show_in_item && $this->item
				|| $oShop_Item_Property->show_in_group && !$this->item)
			{
				// Используется ниже для ограничение показа значений св-в товара в модели
				$aShowPropertyIDs[] = $oProperty->id;
			}

			$this->_aItem_Properties[$oProperty->property_dir_id][] = $oProperty;

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

				if ($this->filterCounts && $oShop->filter && in_array($oShop_Item_Property->filter, array(2, 3, 4, 5, 6, 7, 99)))
				{
					$tableName = $this->getFilterTableName();
					$columnName = 'property' . $oProperty->id;

					$distinctField = $this->modificationsGroup
						? array(Core_QueryBuilder::raw("IF(`{$tableName}`.`modification_id` > 0, `{$tableName}`.`modification_id`, `{$tableName}`.`shop_item_id`)"), 'dataTmpId')
						: array("{$tableName}.shop_item_id", 'dataTmpId');

					$oQueryBuilder = $this->prepareFastfilterQbForProperty($oProperty, $distinctField, $columnName);

					// Не "от и до"
					if ($oShop_Item_Property->filter != 6)
					{
						if ($this->itemsPropertiesListJustAvailable
							// 3 - List
							&& $oProperty->type == 3 && $oProperty->list_id)
						{
							$this->_itemsPropertiesListJustAvailable[$oProperty->id] = array();
						}

						$this->_itemsPropertiesListCount[$oProperty->id] = array();

						// до clearGroupBy() ! // Перенесено в prepareFastfilterQbForProperty
						/*if ($this->itemsPropertiesListJustAvailable)
						{
							$this->_applyWarehouseConditionsQueryBuilder($oQueryBuilder, "{$tableName}.shop_item_id", !$this->modificationsGroup);
						}*/

						if (!is_null($this->tag) && Core::moduleIsActive('tag'))
						{
							is_null($this->_oTag)
								&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

							if ($this->_oTag)
							{
								$oQueryBuilder
									->join('shop_items', 'shop_items.id', '=', 'dataTmpId')
									->join('tag_shop_items', 'shop_items.id', '=', 'tag_shop_items.shop_item_id')
									->where('tag_shop_items.tag_id', '=', $this->_oTag->id);
							}
						}

						$oQueryBuilder
							//->select(array(Core_QueryBuilder::expression("COUNT(DISTINCT {$distinctField})"), 'count'), $tableName . '.' . $columnName)
							->select(array(Core_QueryBuilder::expression("COUNT(DISTINCT `dataTmpId`)"), 'count'), $columnName)
							->clearGroupBy()
							->groupBy($columnName);
							//->groupBy($tableName . '.' . $columnName);

						$aRows = $oQueryBuilder->asAssoc()->execute()->result();

						//echo "<!-- ===== " . Core_DataBase::instance()->getLastQuery() . ' -->';

						// XML-сущность, к которой будет добавляться количество
						$oFilterCountsXmlEntity = Core::factory('Core_Xml_Entity')->name('filter_counts');

						// Добавляем XML-сущность контроллеру показа
						$oProperty->addEntity($oFilterCountsXmlEntity);

						foreach ($aRows as $aRow)
						{
							if (!is_null($aRow[$columnName]))
							{
								$this->_itemsPropertiesListCount[$oProperty->id][$aRow[$columnName]] = $aRow['count'];

								$oFilterCountsXmlEntity->addEntity(
									Core::factory('Core_Xml_Entity')
										->addAttribute('id', $aRow[$columnName])
										->name('count')->value($aRow['count'])
								);

								if ($this->itemsPropertiesListJustAvailable
									// 3 - List
									&& $oProperty->type == 3 && $oProperty->list_id)
								{
									// Insert list_item_id into array
									$aRow[$columnName]
										&& $this->_itemsPropertiesListJustAvailable[$oProperty->id][] = $aRow[$columnName];
								}
							}
						}
					}
					else
					{
						$oQueryBuilder
							->select(
								array(Core_QueryBuilder::expression('MIN(' . $columnName . ')'), 'min'),
								array(Core_QueryBuilder::expression('MAX(' . $columnName . ')'), 'max')
							);

						$aRow = $oQueryBuilder->asAssoc()->execute()->current();

						if (isset($aRow['min']))
						{
							switch ($oProperty->type)
							{
								case 8: // date
									$min = $aRow['min'] != '0000-00-00 00:00:00'
										? Core_Date::strftime($oShop->format_date, Core_Date::sql2timestamp($aRow['min']))
										: '';

									$max = $aRow['max'] != '0000-00-00 00:00:00'
										? Core_Date::strftime($oShop->format_date, Core_Date::sql2timestamp($aRow['max']))
										: '';
								break;
								case 9: //datetime
									$min = $aRow['min'] != '0000-00-00 00:00:00'
										? Core_Date::strftime($oShop->format_datetime, Core_Date::sql2timestamp($aRow['min']))
										: '';

									$max = $aRow['max'] != '0000-00-00 00:00:00'
										? Core_Date::strftime($oShop->format_datetime, Core_Date::sql2timestamp($aRow['max']))
										: '';
								break;
								default:
									$min = floor($aRow['min']);
									$max = ceil($aRow['max']);
							}

							$oProperty->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('min')
									->value($min)
							)->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('max')
									->value($max)
							);
						}
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
				$this->applyForbiddenAllowedTags('/shop/shop_item_properties/property_dir', $oProperty_Dir);
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
	 * Prepare QueryBuilder for select fast filter by $oProperty
	 * @param Property_Model $oProperty
	 * @return Core_QueryBuilder_Select
	 */
	public function prepareFastfilterQbForProperty(Property_Model $oProperty)
	{
		$args = func_get_args();

		// Remove $oProperty from args
		array_shift($args);

		$tableName = $this->getFilterTableName();

		$oQueryBuilder = Core_QueryBuilder::select()
			//->columns($args)
			//->clearSelect()
			->from($tableName)
			->whereRaw(1);

		call_user_func_array(array($oQueryBuilder, 'columns'), $args);

		$this->producer
			&& $oQueryBuilder->where($tableName . '.shop_producer_id', is_array($this->producer) ? 'IN' : '=', $this->producer);

		!$this->modificationsList
			&& $oQueryBuilder->where($tableName . '.modification_id', '=', 0);

		// Filter by properties
		$this->applyFastFilterProperties($oQueryBuilder, array($oProperty->id));

		// Filter by prices
		$this->applyFastFilterPrices($oQueryBuilder);

		if ($this->itemsPropertiesListJustAvailable)
		{
			$this->_applyWarehouseConditionsQueryBuilder($oQueryBuilder, "{$tableName}.shop_item_id", TRUE);
		}

		if (!$this->filterShortcuts)
		{
			$this->applyFilterGroupCondition($oQueryBuilder, $tableName . '.shop_group_id');
		}
		else
		{
			// При фильтраци по всей таблице дополнительное объединение по модификациям и ярлыкам не требуется
			if ($this->group !== FALSE)
			{
				$this->applyFilterGroupCondition($oQueryBuilder, $tableName . '.shop_group_id');

				// Если не было дополнительных ограничений выше (1 - это whereRaw), то и не имеет смысла использовать OR subquery, работа ведется и так по всей таблице
				// $this->filterCounts при получении количества для списков, ограничения на свойства заданы не будут и условие count($oQueryBuilder->getWhere()) > 1 не выполнится
				// (в корне из всех подгрупп используется обычная выборка по всей таблице фильтров)
				if (count($oQueryBuilder->getWhere()) > 1 || $this->filterCounts)
				{
					$oCore_QueryBuilder_Select_Shortcuts = $this->_subquerySelectShortcuts('shop_item_id', $args);

					// Filter by properties
					$this->applyFastFilterProperties($oCore_QueryBuilder_Select_Shortcuts, array($oProperty->id));

					// Filter by prices
					$this->applyFastFilterPrices($oCore_QueryBuilder_Select_Shortcuts);

					if ($this->itemsPropertiesListJustAvailable)
					{
						$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts, "{$tableName}.shop_item_id", TRUE);
					}

					$oQueryBuilder->unionAll($oCore_QueryBuilder_Select_Shortcuts);

					if ($this->modificationsList)
					{
						$oCore_QueryBuilder_Select_Shortcuts = $this->_subquerySelectShortcuts('modification_id', $args);

						// Filter by properties
						$this->applyFastFilterProperties($oCore_QueryBuilder_Select_Shortcuts, array($oProperty->id));

						// Filter by prices
						$this->applyFastFilterPrices($oCore_QueryBuilder_Select_Shortcuts);

						if ($this->itemsPropertiesListJustAvailable)
						{
							$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts, "{$tableName}.shop_item_id", TRUE);
						}

						$oQueryBuilder->unionAll($oCore_QueryBuilder_Select_Shortcuts);
					}
				}
			}
		}

		return Core_QueryBuilder::select()->from(array($oQueryBuilder, 'filterProperty'));
	}

	protected function _subquerySelectShortcuts($fieldName, $columns)
	{
		$tableName = $this->getFilterTableName();

		$oCore_QueryBuilder_Select_Shortcuts = Core_QueryBuilder::select()
			//->columns($args) // see below
			->from($tableName)
			->join('shop_items', $tableName . '.' . $fieldName, '=', 'shop_items.shortcut_id') // $fieldName is `shop_item_id` OR `modification_id`
			->where('shop_items.deleted', '=', 0)
			->where('shop_items.shortcut_id', '>', 0);

		in_array($fieldName, array('shop_item_id', 'modification_id'))
			&& $oCore_QueryBuilder_Select_Shortcuts->indexHint($tableName, array('FORCE INDEX', $fieldName));

		call_user_func_array(array($oCore_QueryBuilder_Select_Shortcuts, 'columns'), $columns);

		$this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Shortcuts, 'shop_items.shop_group_id');

		// Стандартные ограничения для товаров
		$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

		// Ограничения на активность товаров
		$this->_setItemsActivity($oCore_QueryBuilder_Select_Shortcuts);

		$this->producer
			&& $oCore_QueryBuilder_Select_Shortcuts->where($tableName . '.shop_producer_id', is_array($this->producer) ? 'IN' : '=', $this->producer);

		return $oCore_QueryBuilder_Select_Shortcuts;
	}

	/**
	 * Add list of comment properties
	 * @return array
	 * @hostcms-event Shop_Controller_Show.onBeforeAddCommentsPropertiesList
	 */
	protected function _commentsProperties()
	{
		$aShowPropertyIDs = array();

		$oShop = $this->getEntity();

		$oShop_Comment_Property_List = Core_Entity::factory('Shop_Comment_Property_List', $oShop->id);

		$bTpl = $this->_mode == 'tpl';

		$aProperties = is_array($this->commentsPropertiesList) && count($this->commentsPropertiesList)
			? $oShop_Comment_Property_List->Properties->getAllByid($this->commentsPropertiesList, FALSE, 'IN')
			: $oShop_Comment_Property_List->Properties->findAll();

		foreach ($aProperties as $oProperty)
		{
			$oProperty->clearEntities();
			$aShowPropertyIDs[] = $oProperty->id;
			$this->_aComment_Properties[$oProperty->property_dir_id][] = $oProperty;
		}

		// Список свойств комментариев
		if ($this->commentsPropertiesList)
		{
			$aProperty_Dirs = $oShop_Comment_Property_List->Property_Dirs->findAll();
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
	 * Get Filter Table Name
	 * @return string
	 */
	public function getFilterTableName()
	{
		return 'shop_filter' . $this->getEntity()->id;
	}

	/**
	 * Get Filter Price Table Name
	 * @return string
	 */
	public function getFilterPriceTableName()
	{
		return 'shop_filter_price' . $this->getEntity()->id;
	}

	/**
	 * Get Filter Group Table Name
	 * @return string
	 */
	public function getFilterGroupTableName()
	{
		return 'shop_filter_group' . $this->getEntity()->id;
	}

	/**
	 * Get Count Of Found Shop_Items
	 * @return int
	 */
	public function getCount()
	{
		// Fast Filter
		if ($this->_appliedFilter == 1)
		{
			$tableName = $this->getFilterTableName();
			$idFieldName = 'shop_item_id';
		}
		else
		{
			$tableName = 'shop_items';
			$idFieldName = 'id';
		}

		$distinctField = $this->modificationsGroup
			? Core_QueryBuilder::expression("IF(`{$tableName}`.`modification_id` > 0, `{$tableName}`.`modification_id`, `{$tableName}`.`{$idFieldName}`)")
			: "{$tableName}.{$idFieldName}";

		return intval($this->shopItems()->getCount(FALSE, $distinctField, TRUE));
	}

	/**
	 * Get Count Of Found Shop_Items By Fast Filter
	 * @return int
	 */
	public function getFastFilteredCount()
	{
		$oShop = $this->getEntity();

		if ($oShop->filter)
		{
			// Запрещаем выбор модификаций при выключенном modificationsList
			!$this->modificationsList && $this->forbidSelectModifications();

			foreach ($_POST as $key => $value)
			{
				if (strpos($key, 'property_') === 0)
				{
					$this->removeFilter('property', substr($key, 9));
				}
				elseif (strpos($key, 'price_') === 0)
				{
					$this->removeFilter('price');
				}
			}

			// Remove all checkboxes
			$aFilterProperties = $this->getFilterProperties();
			foreach ($aFilterProperties as $propertyId => $aTmpProperties)
			{
				// Checkboxes or select like checkbox
				if (isset($aTmpProperties[0]) && ($aTmpProperties[0][0]->type == 7 || $aTmpProperties[0][0]->type == 3))
				{
					$this->removeFilter('property', $propertyId);
				}
			}

			// Prices
			$this->setFilterPricesConditions($_POST);

			// Additional properties
			$this->setFilterPropertiesConditions($_POST);

			if (Core_Array::getPost('producer_id'))
			{
				$iProducerId = Core_Array::getPost('producer_id', 0, 'int');
				$this->producer($iProducerId);
			}

			// до applyGroupCondition
			$this->applyFilter();

			// после applyFilter()
			$this->applyItemCondition();

			$this->group !== FALSE && $this->applyGroupCondition();

			// Apply $this->warehouseMode
			$this->_applyWarehouseConditions($this->_Shop_Items);

			$this
				->_Shop_Items
				->queryBuilder()
				->where('shop_items.shortcut_id', '=', 0)
				->clearGroupBy()
				->clearOrderBy();

			return $this->getCount();
		}

		return NULL;
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
	 * Apply tag's conditions for $oQB
	 * @return self
	 */
	protected function _applyTagCondition($oQB, $fieldName = 'shop_items.id')
	{
		$this->_oTag && $oQB
			->join('tag_shop_items', $fieldName, '=', 'tag_shop_items.shop_item_id')
			->where('tag_shop_items.tag_id', '=', $this->_oTag->id);

		return $this;
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
				// тег получен в parseUrl()
				is_null($this->_oTag)
					&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

				if ($this->_oTag)
				{
					// moved to the show()
					//$this->addEntity($this->_oTag);

					$fieldName = $this->isShopFilterJoined($this->_Shop_Items->queryBuilder())
						? $this->getFilterTableName() . '.shop_item_id'
						: 'shop_items.id';

					$this->_applyTagCondition($this->_Shop_Items->queryBuilder(), $fieldName);

					// В корне при фильтрации по меткам вывод идет из всех групп
					$this->group == 0 && $this->group = FALSE;
				}
			}
		}
		elseif (!is_null($this->producer))
		{
			$aProducers = is_array($this->producer)
				? $this->producer
				: array($this->producer);

			foreach ($aProducers as $producer_id)
			{
				$oShop_Producer = Core_Entity::factory('Shop_Producer', $producer_id);
				$this->applyForbiddenAllowedTags('/shop/shop_producer', $oShop_Producer);
				$this->addEntity($oShop_Producer);
			}

			$this->_Shop_Items
				->queryBuilder()
				->where('shop_items.shop_producer_id', is_array($this->producer) ? 'IN' : '=', $this->producer);

			// В корне при фильтрации по производителям вывод идет из всех групп
			$this->group == 0 && $this->group = FALSE;
		}

		$this->_setItemsActivity($this->_Shop_Items->queryBuilder());

		return $this;
	}

	/**
	 * Disable shortcuts
	 * @return self
	 */
	public function forbidSelectShortcuts()
	{
		// Отключаем выбор ярлыков
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

		// Check if modification
		$shop_group_id = !$this->parentItem
			? (is_array($this->group) ? array_map('intval', $this->group) : intval($this->group))
			: 0;

		// В расширенном режиме нет возможности использовать объединение с таблицей групп, так как используется ->setOR и подзапросы
		// При быстром фильтре + modificationsList без filterShortcuts используется таблица быстрого фильтра без ограничения по modification_id = 0
		$bExtendedQuery = $this->modificationsList && !$oShop->filter || $this->filterShortcuts;

		if ($bExtendedQuery)
		{
			$tmp = $shop_group_id && $this->subgroups && !is_array($shop_group_id)
				? $this->getSubgroups($shop_group_id)
				: $shop_group_id;

			$this->_Shop_Items
				->queryBuilder()
				->open()
					->where('shop_items.shop_group_id', is_array($tmp) ? 'IN' : '=', $tmp);
		}
		else
		{
			$tableName = $this->getFilterTableName();

			if ($this->modificationsList && $oShop->filter)
			{
				// Объединяем с быстрыми фильтрами
				$this->_joinShopFilter($this->_Shop_Items->queryBuilder());

				// Ограничение по группе задаем быстрым фильтрам, а не shop_items, $tableName также используется ниже
				$fieldGroupConditions = $tableName;
			}
			else
			{
				$fieldGroupConditions = $this->isShopFilterJoined($this->_Shop_Items->queryBuilder())
					? $tableName
					: 'shop_items';
			}

			$shop_group_id
				? $this->applyFilterGroupCondition($this->_Shop_Items->queryBuilder(), "{$fieldGroupConditions}.shop_group_id")
				: $this->_Shop_Items->queryBuilder()->where("{$fieldGroupConditions}.shop_group_id", '=', $shop_group_id);
		}

		// Отключаем выбор ярлыков, чтобы потом добавить оригинальные товары запросом
		$this->filterShortcuts && $this->forbidSelectShortcuts();

		// Вывод модификаций на одном уровне в списке товаров
		if (!$this->item)
		{
			if (!$bExtendedQuery && $this->modificationsList && $oShop->filter)
			{
				// Ограничение для $this->modificationsList задано выше при $this->_joinShopFilter и затем ограничении по группе без modification_id = 0

				// Если нет ограничений по доп. условиям свойств, то ограничиваем primary
				!count($this->_aFilterProperties)
					&& $this->_Shop_Items->queryBuilder()->where("{$tableName}.primary", '=', 1);
			}
			else
			{
				// Отключаем выбор модификаций
				!$this->_selectModifications /*&& !$this->modificationsList*/ && $this->forbidSelectModifications();

				if ($this->modificationsList)
				{
					$oCore_QueryBuilder_Select_Modifications = Core_QueryBuilder::select('shop_items.id')
						->from('shop_items')
						->where('shop_items.shop_id', '=', $oShop->id)
						->where('shop_items.deleted', '=', 0);
						//->where('shop_items.shop_group_id', is_array($shop_group_id) ? 'IN' : '=', $shop_group_id);

					$shop_group_id
						? $this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Modifications, 'shop_items.shop_group_id')
						: $oCore_QueryBuilder_Select_Modifications->where('shop_items.shop_group_id', '=', $shop_group_id);

					// Стандартные ограничения для товаров
					$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Modifications);

					// Ограничения на активность товаров
					$this->_setItemsActivity($oCore_QueryBuilder_Select_Modifications);

					// Ограничения по остаткам на складах
					$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select_Modifications);

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
							//->where('shop_items.shop_group_id', is_array($shop_group_id) ? 'IN' : '=', $shop_group_id)
							->where('shop_items.shortcut_id', '>', 0);

						$shop_group_id
							? $this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Shortcuts_For_Modifications, 'shop_items.shop_group_id')
							: $oCore_QueryBuilder_Select_Shortcuts_For_Modifications->where('shop_items.shop_group_id', '=', $shop_group_id);

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
						//->where('shop_items.shop_group_id', is_array($shop_group_id) ? 'IN' : '=', $shop_group_id)
						->where('shop_items.shortcut_id', '>', 0);

					$shop_group_id
						? $this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Shortcuts, 'shop_items.shop_group_id')
						: $oCore_QueryBuilder_Select_Shortcuts->where('shop_items.shop_group_id', '=', $shop_group_id);

					// Стандартные ограничения для товаров
					$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

					// Ограничения на активность товаров
					$this->_setItemsActivity($oCore_QueryBuilder_Select_Shortcuts);

					// Ограничения по остаткам на складах
					$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

					$this->_Shop_Items
						->queryBuilder()
						->setOr()
						->where('shop_items.id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);
				}
			}
		}

		if ($bExtendedQuery)
		{
			$this->_Shop_Items
				->queryBuilder()
				->close();
		}

		return $this;
	}

	protected $_seoGroupTitle = NULL;
	protected $_seoGroupDescription = NULL;
	protected $_seoGroupKeywords = NULL;
	protected $_seoGroupH1 = NULL;

	protected $_seoItemTitle = NULL;
	protected $_seoItemDescription = NULL;
	protected $_seoItemKeywords = NULL;
	protected $_seoItemH1 = NULL;

	protected $_filterPath = NULL;

	/**
	 * Get _filterPath
	 * @return string
	 */
	public function getFilterPath()
	{
		return $this->_filterPath;
	}

	/**
	 * Set _filterPath
	 * @param string $filterPath
	 * @return self
	 */
	public function setFilterPath($filterPath)
	{
		$this->_filterPath = $filterPath;
		return $this;
	}

	/**
	 * Parse URL and set controller properties
	 * @return self
	 * @hostcms-event Shop_Controller_Show.onBeforeParseUrl
	 * @hostcms-event Shop_Controller_Show.onAfterParseUrl
	 * @hostcms-event Shop_Controller_Show.onAfterParseUrlItemNotFound
	 * @hostcms-event Shop_Controller_Show.onAfterParseUrlModificationNotFound
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
		$oShop->seo_group_h1_template != ''
			&& $this->_seoGroupH1 = $oShop->seo_group_h1_template;

		// Item: set shop's SEO templates
		$oShop->seo_item_title_template != ''
			&& $this->_seoItemTitle = $oShop->seo_item_title_template;
		$oShop->seo_item_description_template != ''
			&& $this->_seoItemDescription = $oShop->seo_item_description_template;
		$oShop->seo_item_keywords_template != ''
			&& $this->_seoItemKeywords = $oShop->seo_item_keywords_template;
		$oShop->seo_item_h1_template != ''
			&& $this->_seoItemH1 = $oShop->seo_item_h1_template;

		// If no custom pattern has been specified
		is_null($this->pattern) && $this->pattern = $this->getPattern();

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

		if (isset($matches['tag']) && $matches['tag'] != '' && Core::moduleIsActive('tag'))
		{
			$this->tag($matches['tag']);

			$this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);
			if (is_null($this->_oTag))
			{
				return $this->error404();
			}
		}

		if (isset($matches['producer']) && $matches['producer'] != '')
		{
			// /page-/ forbidden with producer
			// Producer has pagination!
			/*if (isset($matches['page']) && $matches['page'] != '')
			{
				return $this->error404();
			}*/

			$this->producer($matches['producer']);

			$oShop_Producer = Core_Entity::factory('Shop_Producer')->find($this->producer);
			if (is_null($oShop_Producer->id) || !$oShop_Producer->active)
			{
				return $this->error410();
			}
		}

		// Cookie для аффилиат-программы
		if (isset($matches['user']))
		{
			setcookie('affiliate_name', $matches['user'], time() + 31536000, '/');
		}

		$path = isset($matches['path']) && $matches['path'] != '/'
			? Core_Str::ltrimUri($matches['path'])
			: NULL;

		$this->group = 0;

		if ($path != '')
		{
			$oProperty = NULL;
			$aPropertyValuesToSet = NULL;

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
								$oShop_Group->seo_group_h1_template != ''
									&& $this->_seoGroupH1 = $oShop_Group->seo_group_h1_template;

								// Item: set shop's SEO templates
								$oShop_Group->seo_item_title_template != ''
									&& $this->_seoItemTitle = $oShop_Group->seo_item_title_template;
								$oShop_Group->seo_item_description_template != ''
									&& $this->_seoItemDescription = $oShop_Group->seo_item_description_template;
								$oShop_Group->seo_item_keywords_template != ''
									&& $this->_seoItemKeywords = $oShop_Group->seo_item_keywords_template;
								$oShop_Group->seo_item_h1_template != ''
									&& $this->_seoItemH1 = $oShop_Group->seo_item_h1_template;
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
						$Shop_Producer = $this->parseFilterInUrl
							? ($oShop->filter_mode == 0
								? $oShop->Shop_Producers->getByName($sPath)
								: $oShop->Shop_Producers->getByPath($sPath))
							: NULL;

						if (!is_null($Shop_Producer))
						{
							$this->_filterPath .= $sPath . '/';

							if (is_null($this->producer))
							{
								$this->producer = $Shop_Producer->id;
							}
							else
							{
								// Convert to array
								!is_array($this->producer)
									&& $this->producer = array($this->producer);

								if (!in_array($Shop_Producer->id, $this->producer))
								{
									$this->producer = array_merge($this->producer, array($Shop_Producer->id));
								}
								else
								{
									return $this->error410();
								}
							}

							break;
						}
						else
						{
							$step = 'price';
						}

					case 'price':
						if ($this->parseFilterInUrl)
						{
							$step = 'mainProperties';

							if ($this->parseFilterInUrl && strpos($sPath, 'price-') === 0)
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
									return $this->error410();
								}

								$this->_filterPath .= $sPath . '/';

								break;
							}
						}
						else
						{
							$step = 'item';
						}

					case 'mainProperties':
						if ($this->parseFilterInUrl)
						{
							foreach ($this->_aFilterAvailableMainValues as $mainPropertyName)
							{
								if (strpos($sPath, $mainPropertyName . '-') === 0)
								{
									$aMainValues = explode('-', substr($sPath, strlen($mainPropertyName) + 1), 2);

									if (count($aMainValues) == 2 && is_numeric($aMainValues[0]) && is_numeric($aMainValues[1])/* && !isset($this->_aFilterMainProperties[$mainPropertyName])*/)
									{
										$this
											->addFilter($mainPropertyName, '>=', $aMainValues[0])
											->addFilter($mainPropertyName, '<=', $aMainValues[1]);
									}
									else
									{
										return $this->error410();
									}

									$this->_filterPath .= $sPath . '/';

									break 2;
								}
							}

							$step = 'filter';
						}
						else
						{
							$step = 'item';
						}

					case 'filterValue':
						if ($this->parseFilterInUrl && !is_null($oProperty))
						{
							!is_array($aPropertyValuesToSet) && $aPropertyValuesToSet = array();

							if ($oProperty->type == 3)
							{
								$oList_Item = $oShop->filter_mode == 0
									? $oProperty->List->List_Items->getByValue($sPath)
									: $oProperty->List->List_Items->getByPath($sPath);

								// Try by value
								is_null($oList_Item) && $oShop->filter_mode == 1
									&& $oList_Item = $oProperty->List->List_Items->getByValue($sPath);

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
						if ($this->parseFilterInUrl)
						{
							if (!is_null($oProperty))
							{
								if (is_array($aPropertyValuesToSet) && count($aPropertyValuesToSet))
								{
									$this->addFilter('property', $oProperty->id,
										count($aPropertyValuesToSet) == 1 ? '=' : 'IN',
										count($aPropertyValuesToSet) == 1 ? $aPropertyValuesToSet[0] : $aPropertyValuesToSet
									);

									// Значения были, остается пустой массив, а не NULL
									$aPropertyValuesToSet = array();

									$oProperty = NULL;

									if ($step === 'finish')
									{
										break;
									}
								}
								else
								{
									return $this->error410();
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

										// Значений для этого фильтра еще не было, сохраняем NULL
										$aPropertyValuesToSet = NULL;
									}
									else
									{
										return $this->error410();
									}
								}
								break;
							}
							// Свойство от-до
							elseif (($pos = strpos($sPath, '-')) !== FALSE)
							{
								$aValue = explode('-', $sPath);

								if (count($aValue) > 2)
								{
									$to = array_pop($aValue);
									$from = array_pop($aValue);

									$tagName = implode('-', $aValue);

									if (isset($aFilterProperties[$tagName]))
									{
										$oProperty = $aFilterProperties[$tagName];

										$this->_filterPath .= $sPath . '/';

										if ($oProperty->type == 8 || $oProperty->type == 9) // date, datetime
										{
											$this
												->addFilter('property', $oProperty->id, '>=', $this->_convertReceivedPropertyValue($oProperty, $from))
												->addFilter('property', $oProperty->id, '<=', $this->_convertReceivedPropertyValue($oProperty, $to));

											$oProperty = NULL;
											$aPropertyValuesToSet = array();

											break;
										}
										elseif (is_numeric($from) && is_numeric($to))
										{
											$this
												->addFilter('property', $oProperty->id, '>=', $from)
												->addFilter('property', $oProperty->id, '<=', $to);

											$oProperty = NULL;
											$aPropertyValuesToSet = array();

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
						$oShop_Items
							->queryBuilder()
							->select('shop_items.*')
							->where('shop_items.modification_id', '=', 0);

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
								Core_Event::notify(get_class($this) . '.onAfterParseUrlModificationNotFound', $this, array($aPath, $sPath));

								$eventResult = Core_Event::getLastReturn();

								if (!is_null($eventResult))
								{
									return $eventResult;
								}

								$this->group = $this->item = FALSE;
								return $this->error410();
							}
						}
						else
						{
							Core_Event::notify(get_class($this) . '.onAfterParseUrlItemNotFound', $this, array($aPath, $sPath));

							$eventResult = Core_Event::getLastReturn();

							if (!is_null($eventResult))
							{
								return $eventResult;
							}

							$this->group = FALSE;
							return $this->error404();
						}
					break;
					case 'finish':
						// Nothing to do
					break;
					default:
						$this->group = $this->item = FALSE;
						return $this->error404();
				}
			}

			// Был указан фильтр, требующий значений, но значения заданы не были
			if ($step == 'filterValue' && !is_null($oProperty) && is_null($aPropertyValuesToSet))
			{
				return $this->error404();
			}
		}
		elseif (is_null($path))
		{
			return $this->error404();
		}

		// Ограничение на список товаров
		!$this->item && is_null($this->tag) && $this->_selectModifications = FALSE;

		$seo_title = $seo_description = $seo_keywords = NULL;

		$this->group === 0 && $this->subgroups
			&& $this->group = FALSE;

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
		else
		{
			//if (count($this->_aFilterProperties))
			//{
			if (!is_array($this->producer)
				&& !$this->tag
				// Цены не указаны или указан один стандартный диапазон
				&& (count($this->_aFilterPrices) == 0 || count($this->_aFilterPrices) == 2 && $this->_aFilterPrices[0][0] == '>=' && $this->_aFilterPrices[1][0] == '<=')
			)
			{
				$oInnerQB = Core_QueryBuilder::select(
						'shop_filter_seo_properties.shop_filter_seo_id',
						array('COUNT(*)', 'dataConditionsCount')
					)
					->from('shop_filter_seo_properties')
					->groupBy('shop_filter_seo_properties.shop_filter_seo_id');

				$iCount = 0;
				foreach ($this->_aFilterProperties as $property_id => $aTmp)
				{
					if (count($aTmp) == 2 && Core_Entity::factory('Property', $property_id)->Shop_Item_Property->filter == 6)
					{
						$oInnerQB
							->setOr()
							->open()
								->where('shop_filter_seo_properties.property_id', '=', $property_id)
								->where('shop_filter_seo_properties.value', '=', $aTmp[0][2][0])
								->where('shop_filter_seo_properties.value_to', '=', $aTmp[1][2][0])
							->close();

						$iCount++;
					}
					else
					{
						foreach ($aTmp as $aValues)
						{
							list($oProperty, $condition, $aPropertiesValue) = $aValues;

							foreach ($aPropertiesValue as $value)
							{
								$oInnerQB
									->setOr()
									->open()
										->where('shop_filter_seo_properties.property_id', '=', $property_id)
										->where('shop_filter_seo_properties.value', '=', $value)
									->close();

								$iCount++;
							}
						}
					}
				}
				$oInnerQB->having('dataConditionsCount', '=', $iCount);

				$joinType = $iCount ? 'join' : 'leftJoin';

				$oCore_QueryBuilder_Select = Core_QueryBuilder::select('shop_filter_seos.*', 't1.dataConditionsCount', array('COUNT(*)', 'dataOriginalCount'))
					->from('shop_filter_seos')
					->$joinType(
						array($oInnerQB, 't1'), 'shop_filter_seos.id', '=', 't1.shop_filter_seo_id'
					)
					->$joinType('shop_filter_seo_properties', 'shop_filter_seo_properties.shop_filter_seo_id', '=', 'shop_filter_seos.id')
					->where('shop_filter_seos.shop_id', '=', $oShop->id)
					->where('shop_filter_seos.active', '=', 1)
					->where('shop_filter_seos.deleted', '=', 0)
					->where('shop_filter_seos.shop_producer_id', '=', intval($this->producer))
					->groupBy('shop_filter_seos.id')
					->clearOrderBy()
					->orderBy('dataOriginalCount', 'DESC')
					->limit(1);

				$oCore_QueryBuilder_Select
					->where('shop_filter_seos.price_from', '=', isset($this->_aFilterPrices[0]) ? $this->_aFilterPrices[0][1] : 0)
					->where('shop_filter_seos.price_to', '=', isset($this->_aFilterPrices[1]) ? $this->_aFilterPrices[1][1] : 0);

				$iCount
					? $oCore_QueryBuilder_Select->having('dataOriginalCount', '=', $iCount)
					: $oCore_QueryBuilder_Select->where('shop_filter_seo_properties.id', 'IS', NULL);

				// при включенном subgroups(TRUE) не должен срабатывать SEO-фильтр у родительских групп, например, в корне
				/*$this->group !== FALSE
					&& $this->applyFilterGroupCondition($oCore_QueryBuilder_Select, 'shop_filter_seos.shop_group_id');*/

				// при FALSE в корне не должны срабатывать все SEO-фильтры
				/*$this->group !== FALSE
					&& */$oCore_QueryBuilder_Select->where('shop_filter_seos.shop_group_id', '=', is_array($this->group) ? reset($this->group) : intval($this->group));

				$oShop_Filter_Seo = $oCore_QueryBuilder_Select->execute()->asObject('Shop_Filter_Seo_Model')->current();
			}
			else
			{
				$oShop_Filter_Seo = NULL;
			}

			if ($oShop_Filter_Seo)
			{
				$this->filterSeo = $oShop_Filter_Seo;

				$this->applyForbiddenAllowedTags('/shop/shop_filter_seo', $oShop_Filter_Seo);

				$this->addEntity($oShop_Filter_Seo);

				$seo_title = $oShop_Filter_Seo->seo_title != ''
					? $oShop_Filter_Seo->seo_title
					: $oShop_Filter_Seo->h1;

				$seo_description = $oShop_Filter_Seo->seo_description != ''
					? $oShop_Filter_Seo->seo_description
					: $oShop_Filter_Seo->h1;

				$seo_keywords = $oShop_Filter_Seo->seo_keywords != ''
					? $oShop_Filter_Seo->seo_keywords
					: $oShop_Filter_Seo->h1;
			}
			else
			{
				if (is_null($this->tag))
				{
					if (is_numeric($this->group) && $this->group)
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
					// Корневая группа
					else
					{
						$oCore_Meta = new Core_Meta();
						$oCore_Meta
							->addObject('shop', $oShop)
							->addObject('this', $this);

						// Title
						$oShop->seo_root_title_template != ''
							&& $seo_title = $oCore_Meta->apply($oShop->seo_root_title_template);

						// Description
						$oShop->seo_root_description_template != ''
							&& $seo_description = $oCore_Meta->apply($oShop->seo_root_description_template);

						// Keywords
						$oShop->seo_root_keywords_template != ''
							&& $seo_keywords = $oCore_Meta->apply($oShop->seo_root_keywords_template);

						// H1
						$oShop->seo_root_h1_template != ''
							&& $this->_seoGroupH1 = $oShop->seo_root_h1_template;
					}
				}
				elseif (!is_null($this->tag) && Core::moduleIsActive('tag'))
				{
					$seo_title = $this->_oTag->seo_title != ''
						? $this->_oTag->seo_title
						: Core::_('Shop.tag', $this->_oTag->name, FALSE);

					$seo_description = $this->_oTag->seo_description != ''
						? $this->_oTag->seo_description
						: $this->_oTag->name;

					$seo_keywords = $this->_oTag->seo_keywords != ''
						? $this->_oTag->seo_keywords
						: $this->_oTag->name;
				}

				// SEO от производителя только при указании в корне (или FALSE) и без фильтрации
				if (is_numeric($this->producer) && $this->group == 0 && !count($this->_aFilterProperties))
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
			}
		}

		$seo_title != '' && Core_Page::instance()->title($seo_title);
		$seo_description != '' && Core_Page::instance()->description($seo_description);
		$seo_keywords != '' && Core_Page::instance()->keywords($seo_keywords);

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

			$mGroups = $this->subgroups
				? $this->getSubgroups($group_id)
				: $group_id;

			$oShop = $this->getEntity();

			$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

			$aProperties = $group_id === FALSE
				? $oShop_Item_Property_List->Properties->findAll()
				: $oShop_Item_Property_List->getPropertiesForGroup($mGroups);

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
		return is_numeric($this->producer)
			? Core_Entity::factory('Shop_Producer', $this->producer)->name
			: '';
	}

	/**
	 * Get properties conditions for meta-tags
	 * @param $nameSeparator property name separator, default ": "
	 * @param $valueSeparator property value separator, default ", "
	 * @param $propertySeparator property separator, default ","
	 * @return string
	 */
	public function seoFilter($nameSeparator = ": ", $valueSeparator = ", ", $propertySeparator = ",")
	{
		$aReturn = $aGroupedValues = array();

		foreach ($this->_aFilterProperties as $property_id => $aTmpProperties)
		{
			foreach ($aTmpProperties as $aTmpProperty)
			{
				list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;

				foreach ($aPropertyValues as $propertyValue)
				{
					if ($oProperty->type == 3)
					{
						$oList_Item = $oProperty->List->List_Items->getById($propertyValue);

						if (!is_null($oList_Item))
						{
							$aGroupedValues[$property_id][] = $oList_Item->value;
						}
					}
					else
					{
						$aGroupedValues[$property_id][] = $propertyValue;
					}
				}
			}
		}

		foreach ($aGroupedValues as $property_id => $aValues)
		{
			$oProperty = Core_Entity::factory('Property', $property_id);

			$aReturn[] = ' ' . $oProperty->name . $nameSeparator . implode($valueSeparator, $aValues);
		}

		return implode($propertySeparator, $aReturn);
	}

	/**
	 * Get prices conditions for meta-tags
	 * @param $priceFrom From-prefix, default "from"
	 * @param $priceTo To-prefix, default "to"
	 * @return string
	 */
	public function seoFilterPrices($priceFrom = "from", $priceTo = "to")
	{
		$sReturn = '';

		if (count($this->_aFilterPrices) == 2)
		{
			$oShop = $this->getEntity();
			$oShop_Currency = $oShop->Shop_Currency;

			foreach ($this->_aFilterPrices as $aFilterPrice)
			{
				switch ($aFilterPrice[0])
				{
					case '>=':
						$sReturn .= " {$priceFrom} " . $oShop_Currency->formatWithCurrency($aFilterPrice[1]);
					break;
					case '<=':
						$sReturn .= " {$priceTo} " . $oShop_Currency->formatWithCurrency($aFilterPrice[1]);
					break;
				}
			}
		}

		return trim($sReturn);
	}

	/**
	 * Define handler for 410 error
	 * @return self
	 */
	public function error410()
	{
		$this->_errorCode = 410;

		!is_null(Core_Page::instance()->response)
			&& Core_Page::instance()->error410();

		return $this;
	}

	/**
	 * Define handler for 404 error
	 * @return self
	 */
	public function error404()
	{
		$this->_errorCode = 404;

		!is_null(Core_Page::instance()->response)
			&& Core_Page::instance()->error404();

		return $this;
	}

	/**
	 * Define handler for 403 error
	 * @return self
	 */
	public function error403()
	{
		$this->_errorCode = 403;

		!is_null(Core_Page::instance()->response)
			&& Core_Page::instance()->error403();

		return $this;
	}

	/**
	 * Apply forbidden xml tags for groups
	 * @param Shop_Group_Model $oShop_Group group
	 * @return self
	 */
	public function applyGroupsForbiddenTags($oShop_Group)
	{
		/*!is_null($this->groupsForbiddenTags)
			&& $oShop_Group->addForbiddenTags($this->groupsForbiddenTags);*/

		$this->applyForbiddenAllowedTags('/shop/shop_group', $oShop_Group);

		return $this;
	}

	/**
	 * Apply forbidden xml tags for items
	 * @param Shop_Item_Model $oShop_Item item
	 * @return self
	 */
	public function applyItemsForbiddenTags($oShop_Item)
	{
		/*!is_null($this->itemsForbiddenTags)
			&& $oShop_Item->addForbiddenTags($this->itemsForbiddenTags);*/

		$this->applyForbiddenAllowedTags('/shop/shop_item', $oShop_Item);

		return $this;
	}

	/**
	 * Add all groups to XML
	 * @return self
	 */
	public function addAllGroups()
	{
		$this->_aShop_Groups = array();

		// Load all values for properties
		if (is_array($this->groupsProperties))
		{
			foreach ($this->groupsProperties as $propertyId)
			{
				Core_Entity::factory('Property', $propertyId)->loadAllValues();
			}
		}

		$aShop_Groups = $this->_Shop_Groups->findAll();
		foreach ($aShop_Groups as $oShop_Group)
		{
			$this->_groupIntoArray($oShop_Group);
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

		$group_id = intval(!$this->parentItem
			? (is_array($this->group)
				? Core_Array::first($this->group)
				: $this->group
			)
			: Core_Entity::factory('Shop_Item', $this->parentItem)->shop_group_id
		);

		// Потомки текущего уровня
		$aShop_Groups = $this->_Shop_Groups->getByParentId($group_id);

		foreach ($aShop_Groups as $oShop_Group)
		{
			$this->_groupIntoArray($oShop_Group);
		}

		if ($group_id != 0)
		{
			$oShop_Group = Core_Entity::factory('Shop_Group', $group_id);

			do {
				$this->_groupIntoArray($oShop_Group);
			} while ($oShop_Group = $oShop_Group->getParent());
		}

		$this->_addGroupsByParentId(0, $this);

		return $this;
	}

	/**
	 * Add group $oOriginal_Shop_Group into $this->_aShop_Groups
	 * @param Shop_Group_Model $oOriginal_Shop_Group
	 * @return self
	 */
	protected function _groupIntoArray($oOriginal_Shop_Group)
	{
		$parent_id = $oOriginal_Shop_Group->parent_id;

		// Shortcut
		if ($oOriginal_Shop_Group->shortcut_id
			&& $oOriginal_Shop_Group->shortcut_id != $oOriginal_Shop_Group->parent_id)
		{
			$oShortcut_Group = $oOriginal_Shop_Group;
			$oOriginal_Shop_Group = $oOriginal_Shop_Group->Shortcut;

			if ($oOriginal_Shop_Group->active
				&& (!$oOriginal_Shop_Group->parent_id || $oOriginal_Shop_Group->getParent()->active)
			)
			{
				$oShop_Group = clone $oOriginal_Shop_Group;

				$oShop_Group
					->id($oOriginal_Shop_Group->id)
					->clearEntities()
					->addForbiddenTag('parent_id')
					->addForbiddenTag('shortcut_id')
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('shortcut_id')
							->value($oShortcut_Group->id)
					)
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('parent_id')
							->value($oShortcut_Group->parent_id)
					);
			}
			else
			{
				$oShop_Group = NULL;
			}
		}
		else
		{
			$oShop_Group = clone $oOriginal_Shop_Group;
			$oShop_Group
				->id($oOriginal_Shop_Group->id)
				->clearEntities();
		}

		if (!is_null($oShop_Group))
		{
			// After check shortcut
			$this->applyGroupsForbiddenTags($oShop_Group);

			$this->_aShop_Groups[$parent_id][] = $oShop_Group;
		}

		return $this;
	}

	/**
	 * Add groups by parent to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 * @hostcms-event Shop_Controller_Show.onAfterAddShopGroups
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

					$oShop_Group->showXmlProperties($this->groupsProperties, $this->sortPropertiesValues);
				}
				else
				{
					$oShop_Group->showXmlProperties(FALSE, $this->sortPropertiesValues);
				}

				// Media
				$this->groupsMedia
					&& $oShop_Group->showXmlMedia($this->groupsMedia);

				$parentObject->addEntity($oShop_Group);

				$this->_addGroupsByParentId($oShop_Group->id, $oShop_Group);
			}

			Core_Event::notify(get_class($this) . '.onAfterAddShopGroups', $this, array($this->_aShop_Groups[$parent_id]));
		}

		return $this;
	}

	/**
	 * Set limitListItems conditions
	 * @param Property_Model $oProperty
	 * @return self
	 */
	protected function _setLimitListItems(Property_Model $oProperty)
	{
		// Ограничение было рассчитано ранее при построении быстрого фильтра при влюченном filterCounts
		if (isset($this->_itemsPropertiesListJustAvailable[$oProperty->id]))
		{
			$oProperty->limitListItems($this->_itemsPropertiesListJustAvailable[$oProperty->id]);
		}
		else
		{
			$oShop = $this->getEntity();

			// С подгруппами быстрее выборка через таблицу свойств
			if ($oShop->filter && !$this->subgroups)
			{
				$columnName = 'property' . $oProperty->id;

				$oCore_QueryBuilder_Select = $this->prepareFastfilterQbForProperty($oProperty, $columnName)
					->select(array(Core_QueryBuilder::expression("DISTINCT {$columnName}"), 'value'));
			}
			else
			{
				$oCore_QueryBuilder_Select = Core_QueryBuilder::select('property_value_ints.value')
					->from('property_value_ints')
					->join('shop_items', 'shop_items.id', '=', 'property_value_ints.entity_id')
					->where('property_value_ints.property_id', '=', $oProperty->id)
					->open()
						->where('shop_items.shop_id', '=', $oShop->id)
						->where('shop_items.deleted', '=', 0)
						->where('shop_items.modification_id', '=', 0);

				// В расширенном режиме нет возможности использовать объединение с таблицей групп, так как используется ->setOR и подзапросы
				// При быстром фильтре + modificationsList без filterShortcuts используется таблица быстрого фильтра без ограничения по modification_id = 0
				$bExtendedQuery = $this->modificationsList /*&& !$oShop->filter*/ || $this->filterShortcuts;

				if ($this->group !== FALSE)
				{
					if ($bExtendedQuery)
					{
						$tmp = $this->group && $this->subgroups && !is_array($this->group)
							? $this->getSubgroups($this->group)
							: $this->group;

						$oCore_QueryBuilder_Select->where('shop_items.shop_group_id', is_array($tmp) ? 'IN' : '=', $tmp);
					}
					else
					{
						/*if ($this->modificationsList && $oShop->filter)
						{
							// Объединяем с быстрыми фильтрами
							$this->_joinShopFilter($oCore_QueryBuilder_Select);

							// Ограничение по группе задаем быстрым фильтрам, а не shop_items. $tableName используется ниже
							$tableName = $this->getFilterTableName();
							$fieldGroupConditions = $tableName;
						}
						else
						{*/
							$fieldGroupConditions = 'shop_items';
						//}

						$this->applyFilterGroupCondition($oCore_QueryBuilder_Select, "{$fieldGroupConditions}.shop_group_id");
					}
				}

				$this->producer
					&& $oCore_QueryBuilder_Select->where('shop_items.shop_producer_id', is_array($this->producer) ? 'IN' : '=', $this->producer);

				// Стандартные ограничения для товаров
				$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select);

				// Ограничения на активность товаров
				$this->_setItemsActivity($oCore_QueryBuilder_Select);

				// Ограничения по остаткам на складах
				$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select);

				if (!is_null($this->tag) && Core::moduleIsActive('tag'))
				{
					is_null($this->_oTag)
						&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

					$this->_oTag
						&& $this->_applyTagCondition($oCore_QueryBuilder_Select);
				}

				/*if (!$bExtendedQuery && $this->modificationsList && $oShop->filter)
				{
					// Ограничение для $this->modificationsList задано выше при $this->_joinShopFilter и затем ограничении по группе без modification_id = 0

					// Если нет ограничений по доп. условиям свойств, то ограничиваем primary
					!count($this->_aFilterProperties)
						&& $oCore_QueryBuilder_Select->where("{$tableName}.primary", '=', 1);
				}
				else
				{*/
					// Вывод модификаций на одном уровне в списке товаров
					if ($this->modificationsList)
					{
						$oCore_QueryBuilder_Select_Modifications = Core_QueryBuilder::select('shop_items.id')
							->from('shop_items')
							->where('shop_items.shop_id', '=', $oShop->id)
							->where('shop_items.deleted', '=', 0);

						$this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Modifications, 'shop_items.shop_group_id');

						$this->producer
							&& $oCore_QueryBuilder_Select_Modifications->where('shop_items.shop_producer_id', is_array($this->producer) ? 'IN' : '=', $this->producer);

						// Стандартные ограничения для товаров
						$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Modifications);

						// Ограничения на активность товаров
						$this->_setItemsActivity($oCore_QueryBuilder_Select_Modifications);

						// Ограничения по остаткам на складах
						$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select_Modifications);

						if (!is_null($this->tag) && Core::moduleIsActive('tag'))
						{
							is_null($this->_oTag)
								&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

							$this->_oTag
								&& $this->_applyTagCondition($oCore_QueryBuilder_Select_Modifications);
						}

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
								->where('shop_items.shortcut_id', '>', 0);

							$this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Shortcuts_For_Modifications, 'shop_items.shop_group_id');

							$this->producer
								&& $oCore_QueryBuilder_Select_Shortcuts_For_Modifications->where('shop_items.shop_producer_id', is_array($this->producer) ? 'IN' : '=', $this->producer);

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
							->where('shop_items.shop_id', '=', $oShop->id)
							->where('shop_items.shortcut_id', '>', 0);

						$this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Shortcuts, 'shop_items.shop_group_id');

						// Стандартные ограничения для товаров
						$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

						// Ограничения на активность товаров
						$this->_setItemsActivity($oCore_QueryBuilder_Select_Shortcuts);

						// Ограничения по остаткам на складах
						$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

						if (!is_null($this->tag) && Core::moduleIsActive('tag'))
						{
							is_null($this->_oTag)
								&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

							$this->_oTag
								&& $this->_applyTagCondition($oCore_QueryBuilder_Select_Shortcuts);
						}

						$oCore_QueryBuilder_Select
							->setOr()
							->where('shop_items.id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);
					}
				//}

				$oCore_QueryBuilder_Select
					->close()
					->groupBy('property_value_ints.value');
			}

			$oProperty->limitListItems($oCore_QueryBuilder_Select);
		}

		return $this;
	}

	/**
	 * Add list of item's properties to XML
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
					$this->_setLimitListItems($oProperty);
				}
				elseif ($this->itemsPropertiesListJustAvailable)
				{
					// Запрещаем показ элементов списка
					$oProperty->limitListItems(array());
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
	 * Show frontend panel
	 * @return $this
	 * @hostcms-event Shop_Controller_Show.onBeforeShowPanel
	 */
	protected function _showPanel()
	{
		$oShop = $this->getEntity();

		// Panel
		$oXslPanel = Core_Html_Entity::factory('Div')
			->class('hostcmsPanel')
			->style('display: none');

		$oXslSubPanel = Core_Html_Entity::factory('Div')
			->class('hostcmsSubPanel hostcmsXsl');

		if ($this->item == 0 && !is_array($this->group))
		{
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=edit&shop_id={$oShop->id}&shop_group_id={$this->group}&hostcms[checked][1][0]=1";
			$sTitle = Core::_('Shop_Item.items_catalog_add_form_title');

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core_Html_Entity::factory('I')
							->title($sTitle)
							->class("fa-solid fa-file-circle-plus fa-fw")
					)
			);

			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=edit&shop_id={$oShop->id}&shop_group_id={$this->group}&hostcms[checked][0][0]=1";
			$sTitle = Core::_('Shop_Group.groups_add_form_title');

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core_Html_Entity::factory('I')
							->title($sTitle)
							->class('fa-solid fa-folder-plus fa-fw')
					)
			);

			if ($this->group)
			{
				$oShop_Group = Core_Entity::factory('Shop_Group', $this->group);

				// Edit
				$sPath = '/admin/shop/item/index.php';
				$sAdditional = "hostcms[action]=edit&shop_id={$oShop->id}&shop_group_id={$oShop_Group->parent_id}&hostcms[checked][0][{$this->group}]=1";
				$sTitle = Core::_('Shop_Group.groups_edit_form_title', $oShop_Group->name, FALSE);

				$oXslSubPanel->add(
					Core_Html_Entity::factory('A')
						->href("{$sPath}?{$sAdditional}")
						->onclick("hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
						->add(
							Core_Html_Entity::factory('I')
								->title($sTitle)
								->class('fa-solid fa-pen folder fa-fw')
						)
				);
			}

			// Folder
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "shop_id={$oShop->id}&shop_group_id={$this->group}";
			$sTitle = Core::_('Shop_Group.links_groups');

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core_Html_Entity::factory('I')
							->title($sTitle)
							->class('fa-solid fa-folder fa-fw')
					)
			);

			if ($this->group)
			{
				// Delete
				$sPath = '/admin/shop/item/index.php';
				$sAdditional = "hostcms[action]=markDeleted&shop_id={$oShop->id}&shop_group_id={$oShop_Group->parent_id}&hostcms[checked][0][{$this->group}]=1&secret_csrf=" . Core_Security::getCsrfToken();
				$sTitle = Core::_('Shop_Group.markDeleted');

				$oXslSubPanel->add(
					Core_Html_Entity::factory('A')
						->href("{$sPath}?{$sAdditional}")
						->onclick("res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'});} return false")
						->add(
							Core_Html_Entity::factory('I')
								->title($sTitle)
								->class('fa-solid fa-trash-can fa-fw')
						)
				);
			}
		}
		elseif ($this->item)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item', $this->item);

			// Edit
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=edit&shop_id={$oShop->id}&shop_group_id={$oShop_Item->shop_group_id}&hostcms[checked][1][{$this->item}]=1";
			$sTitle = Core::_('Shop_Item.items_catalog_edit_form_title', $oShop_Item->name, FALSE);

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core_Html_Entity::factory('I')
							->title($sTitle)
							->class('fa-solid fa-pen item fa-fw')
					)
			);

			// Copy
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=copy&shop_id={$oShop->id}&shop_group_id={$oShop_Item->shop_group_id}&hostcms[checked][1][{$this->item}]=1&secret_csrf=" . Core_Security::getCsrfToken();
			$sTitle = Core::_('Shop_Item.items_catalog_copy_form_title');

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($sTitle))."'); if (res) { hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false } else { return false } ")
					->add(
						Core_Html_Entity::factory('I')
							->title($sTitle)
							->class('fa-solid fa-copy fa-fw')
					)
			);

			// Folder
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "shop_id={$oShop->id}&shop_group_id={$oShop_Item->shop_group_id}";
			$sTitle = Core::_('Shop_Group.links_groups');

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core_Html_Entity::factory('I')
							->title($sTitle)
							->class('fa-solid fa-folder fa-fw')
					)
			);

			// Comments
			$sPath = '/admin/shop/item/comment/index.php';
			$sAdditional = "shop_item_id={$this->item}";
			$sTitle = Core::_('Shop_Item.items_catalog_add_form_comment_link');

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
					->add(
						Core_Html_Entity::factory('I')
							->title($sTitle)
							->class('fa-solid fa-comments fa-fw')
					)
			);

			// Delete
			$sPath = '/admin/shop/item/index.php';
			$sAdditional = "hostcms[action]=markDeleted&shop_id={$oShop->id}&shop_group_id={$oShop_Item->shop_group_id}&hostcms[checked][1][{$this->item}]=1&secret_csrf=" . Core_Security::getCsrfToken();
			$sTitle = Core::_('Shop_Item.markDeleted');

			$oXslSubPanel->add(
				Core_Html_Entity::factory('A')
					->href("{$sPath}?{$sAdditional}")
					->onclick("res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { hQuery.openWindow({path: '{$sPath}', title: '" . Core_Str::escapeJavascriptVariable($sTitle) . "', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'});} return false")
					->add(
						Core_Html_Entity::factory('I')
							->title($sTitle)
							->class('fa-solid fa-trash-can fa-fw')
					)
			);
		}

		Core_Event::notify(get_class($this) . '.onBeforeShowPanel', $this, array($oXslSubPanel));

		$oXslPanel
			->add($oXslSubPanel)
			->execute();

		return $this;
	}

	/**
	 * Set items activity
	 * @return self
	 */
	protected function _setItemsActivity(Core_QueryBuilder_Select $oCore_QueryBuilder_Select, $tableName = 'shop_items')
	{
		$this->itemsActivity = strtolower($this->itemsActivity);

		if ($this->itemsActivity != 'all')
		{
			$oCore_QueryBuilder_Select
				->where($tableName . '.active', '=', $this->itemsActivity == 'inactive' ? 0 : 1);
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

	public function getFilterPriceAvailableFields()
	{
		$aFields = array();

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			if ($oSiteuser)
			{
				$oShop = $this->getEntity();

				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
				foreach ($aSiteuser_Groups as $oSiteuser_Group)
				{
					// Может быть создано несколько цен для одной группы клиентов
					$aShop_Prices = Core_Entity::factory('Shop_Price')->getAllBySiteuserGroupAndShop(
						$oSiteuser_Group->id, $oShop->id
					);

					foreach ($aShop_Prices as $oShop_Price)
					{
						$aFields[] = 'price' . $oShop_Price->id;
					}
				}
			}
		}

		// При наличии цен для групп клиентов $aFields, отдельно работать по price_absolute не имеет смысла,
		// так как цена в спец.полях будет не выше, чем price_absolute
		!count($aFields)
			&& $aFields[] = 'price_absolute';

		return $aFields;
	}

	protected function _getSubMinMaxQueryBuilder()
	{
		$oSubMinMaxQueryBuilder = Core_QueryBuilder::select()
			->from('shop_items')
			->join(array('shop_items', 's'), 'shop_items.id', '=', 's.shortcut_id')
			->where('s.deleted', '=', 0)
			->where('s.shortcut_id', '>', 0);

		$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 's.shop_group_id');

		// Стандартные ограничения для товаров
		$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 's');

		// Ограничения на активность товаров
		$this->_setItemsActivity($oSubMinMaxQueryBuilder, 's');

		if (!is_null($this->tag) && Core::moduleIsActive('tag'))
		{
			is_null($this->_oTag)
				&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

			$this->_oTag
				&& $this->_applyTagCondition($oSubMinMaxQueryBuilder, 's.id');
		}

		return $oSubMinMaxQueryBuilder;
	}

	/**
	 * Add minimum and maximum price
	 * @return self
	 */
	public function addMinMaxPrice()
	{
		$oShop = $this->getEntity();

		// В быстром фильтре min/max для товаров и modificationsList считаем одним запросом
		if ($oShop->filter)
		{
			$tableName = $this->getFilterTableName();
			$priceTableName = $this->getFilterPriceTableName();

			$aMin = $aMax = array();

			// Доступные поля для фильтрации по ценам
			$aPriceFields = $this->getFilterPriceAvailableFields();
			foreach ($aPriceFields as $sPriceField)
			{
				$oSubMinMaxQueryBuilder = Core_QueryBuilder::select()
					->clearSelect()
					->select(array("MIN({$sPriceField})", 'min'), array("MAX({$sPriceField})", 'max'))
					->from($tableName)
					->where($tableName . '.primary', '=', 1);

				$sPriceField != 'price_absolute'
					&& $oSubMinMaxQueryBuilder->join($priceTableName, $tableName . '.shop_item_id', '=', $priceTableName . '.shop_item_id');

				$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, $tableName . '.shop_group_id');

				!$this->modificationsList
					&& $oSubMinMaxQueryBuilder->where($tableName . '.modification_id', '=', 0);

				if (!is_null($this->tag) && Core::moduleIsActive('tag'))
				{
					is_null($this->_oTag)
						&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

					if ($this->_oTag)
					{
						$oSubMinMaxQueryBuilder
							->join('tag_shop_items', $tableName . '.shop_item_id', '=', 'tag_shop_items.shop_item_id')
							->where('tag_shop_items.tag_id', '=', $this->_oTag->id);
					}
				}

				// Ограничение по складу через быстрый фильтр
				if (in_array($this->warehouseMode, array('in-stock', 'in-stock-modification')))
				{
					$oSubMinMaxQueryBuilder->where($tableName . '.available', '>', 0);
				}
				elseif ($this->warehouseMode == 'out-of-stock')
				{
					$oSubMinMaxQueryBuilder->where($tableName . '.available', '<=', 0);
				}

				$rows = $oSubMinMaxQueryBuilder->asAssoc()->execute()->current();

				!is_null($rows['min']) && $aMin[] = $rows['min'];
				$aMax[] = $rows['max'];
			}

			$min = count($aMin) ? min($aMin) : 0;
			$max = count($aMax) ? max($aMax) : 0;
		}
		// Basic Filter
		else
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select()
				->from('shop_items')
				->where('shop_items.deleted', '=', 0)
				->where('shop_items.shop_id', '=', $oShop->id)
				->where('shop_items.shortcut_id', '=', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

			$this->applyAbsolutePrice($oSubMinMaxQueryBuilder);

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder);

			// Ограничения на активность товаров
			$this->_setItemsActivity($oSubMinMaxQueryBuilder);

			if (!is_null($this->tag) && Core::moduleIsActive('tag'))
			{
				is_null($this->_oTag)
					&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

				if ($this->_oTag)
				{
					$oSubMinMaxQueryBuilder
						->join('tag_shop_items', 'shop_items.id', '=', 'tag_shop_items.shop_item_id')
						->where('tag_shop_items.tag_id', '=', $this->_oTag->id);
				}
			}

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.price_absolute)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.price_absolute)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			$min = floatval($rows['min']);
			$max = floatval($rows['max']);

			if ($this->modificationsList)
			{
				$oSubMinMaxQueryBuilder = Core_QueryBuilder::select()
					->from('shop_items')
					->join(array('shop_items', 'm'), 'shop_items.id', '=', 'm.modification_id')
					->where('m.shop_group_id', '=', 0)
					->where('m.modification_id', '>', 0)
					->where('m.deleted', '=', 0)
					->where('shop_items.deleted', '=', 0);

				$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

				// Стандартные ограничения для товаров
				$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 'm');

				// Ограничения на активность товаров
				$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'm');
				$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'shop_items');

				$this->applyAbsolutePrice($oSubMinMaxQueryBuilder, 'm');

				if (!is_null($this->tag) && Core::moduleIsActive('tag'))
				{
					is_null($this->_oTag)
						&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

					if ($this->_oTag)
					{
						$oSubMinMaxQueryBuilder
							->join('tag_shop_items', 'm.id', '=', 'tag_shop_items.shop_item_id')
							->where('tag_shop_items.tag_id', '=', $this->_oTag->id);
					}
				}

				$oMinMaxQueryBuilder = Core_QueryBuilder::select(
						array(Core_QueryBuilder::expression('MIN(t.price_absolute)'), 'min'),
						array(Core_QueryBuilder::expression('MAX(t.price_absolute)'), 'max')
					)
					->from(array($oSubMinMaxQueryBuilder, 't'));

				$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

				!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
				$rows['max'] > $max && $max = $rows['max'];
			}
		}

		if ($this->filterShortcuts)
		{
			if ($oShop->filter)
			{
				// Цикл по всем доступным для клиента ценам
				foreach ($aPriceFields as $sPriceField)
				{
					$tableName = $this->getFilterTableName();

					$oSubMinMaxQueryBuilder = $this->_getSubMinMaxQueryBuilder();

					$oSubMinMaxQueryBuilder
						->clearSelect()
						->select(array("MIN({$sPriceField})", 'min'), array("MAX({$sPriceField})", 'max'))
						->join($tableName, 'shop_items.id', '=', $tableName . '.shop_item_id');

					$sPriceField != 'price_absolute'
						&& $oSubMinMaxQueryBuilder->join($priceTableName, $tableName . '.shop_item_id', '=', $priceTableName . '.shop_item_id');

					$rows = $oSubMinMaxQueryBuilder->asAssoc()->execute()->current();

					!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
					$rows['max'] > $max && $max = $rows['max'];
				}
			}
			// Basic Filter
			else
			{
				$oSubMinMaxQueryBuilder = $this->_getSubMinMaxQueryBuilder();

				$this->applyAbsolutePrice($oSubMinMaxQueryBuilder, 's');

				$oMinMaxQueryBuilder = Core_QueryBuilder::select(
						array(Core_QueryBuilder::expression('MIN(t.price_absolute)'), 'min'),
						array(Core_QueryBuilder::expression('MAX(t.price_absolute)'), 'max')
					)
					->from(array($oSubMinMaxQueryBuilder, 't'));

				$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

				!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
				$rows['max'] > $max && $max = $rows['max'];
			}
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('min_price')
				->value(floor(floatval($min)))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('max_price')
				->value(ceil(floatval($max)))
		);

		return $this;
	}

	/**
	 * Add minimum and maximum width
	 * @return self
	 */
	public function addMinMaxWidth()
	{
		$oShop = $this->getEntity();

		$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('shop_items.width')
			->from('shop_items')
			->where('shop_items.deleted', '=', 0)
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.active', '=', 1)
			->where('shop_items.shortcut_id', '=', 0);

		$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

		$oMinMaxQueryBuilder = Core_QueryBuilder::select(
				array(Core_QueryBuilder::expression('MIN(t.width)'), 'min'),
				array(Core_QueryBuilder::expression('MAX(t.width)'), 'max')
			)
			->from(array($oSubMinMaxQueryBuilder, 't'));

		$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

		$min = floatval($rows['min']);
		$max = floatval($rows['max']);

		if ($this->modificationsList)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('m.width')
				->from('shop_items')
				->join(array('shop_items', 'm'), 'shop_items.id', '=', 'm.modification_id')
				->where('m.shop_group_id', '=', 0)
				->where('m.modification_id', '>', 0)
				->where('m.deleted', '=', 0)
				->where('shop_items.deleted', '=', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 'm');

			// Ограничения на активность товаров
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'm');
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'shop_items');

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.width)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.width)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		if ($this->filterShortcuts)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('shop_items.width')
				->from('shop_items')
				->join(array('shop_items', 't'), 'shop_items.id', '=', 't.shortcut_id')
				->where('t.deleted', '=', 0)
				->where('t.shortcut_id', '>', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 't.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 't');

			// Ограничения на активность товаров
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 't');

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
				array(Core_QueryBuilder::expression('MIN(t.width)'), 'min'),
				array(Core_QueryBuilder::expression('MAX(t.width)'), 'max')
			)
			->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('min_width')
				->value(floor($min))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('max_width')
				->value(ceil($max))
		);

		return $this;
	}

	/**
	 * Add minimum and maximum length
	 * @return self
	 */
	public function addMinMaxLength()
	{
		$oShop = $this->getEntity();

		$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('shop_items.length')
			->from('shop_items')
			->where('shop_items.deleted', '=', 0)
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.active', '=', 1)
			->where('shop_items.shortcut_id', '=', 0);

		$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

		$oMinMaxQueryBuilder = Core_QueryBuilder::select(
				array(Core_QueryBuilder::expression('MIN(t.length)'), 'min'),
				array(Core_QueryBuilder::expression('MAX(t.length)'), 'max')
			)
			->from(array($oSubMinMaxQueryBuilder, 't'));

		$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

		$min = floatval($rows['min']);
		$max = floatval($rows['max']);

		if ($this->modificationsList)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('m.length')
				->from('shop_items')
				->join(array('shop_items', 'm'), 'shop_items.id', '=', 'm.modification_id')
				->where('m.shop_group_id', '=', 0)
				->where('m.modification_id', '>', 0)
				->where('m.deleted', '=', 0)
				->where('shop_items.deleted', '=', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 'm');

			// Ограничения на активность товаров
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'm');
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'shop_items');

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.length)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.length)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		if ($this->filterShortcuts)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('shop_items.length')
				->from('shop_items')
				->join(array('shop_items', 't'), 'shop_items.id', '=', 't.shortcut_id')
				->where('t.deleted', '=', 0)
				->where('t.shortcut_id', '>', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 't.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 't');

			// Ограничения на активность товаров
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 't');

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
				array(Core_QueryBuilder::expression('MIN(t.length)'), 'min'),
				array(Core_QueryBuilder::expression('MAX(t.length)'), 'max')
			)
			->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('min_length')
				->value(floor($min))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('max_length')
				->value(ceil($max))
		);

		return $this;
	}

		/**
	 * Add minimum and maximum height
	 * @return self
	 */
	public function addMinMaxHeight()
	{
		$oShop = $this->getEntity();

		$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('shop_items.height')
			->from('shop_items')
			->where('shop_items.deleted', '=', 0)
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.active', '=', 1)
			->where('shop_items.shortcut_id', '=', 0);

		$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

		$oMinMaxQueryBuilder = Core_QueryBuilder::select(
				array(Core_QueryBuilder::expression('MIN(t.height)'), 'min'),
				array(Core_QueryBuilder::expression('MAX(t.height)'), 'max')
			)
			->from(array($oSubMinMaxQueryBuilder, 't'));

		$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

		$min = floatval($rows['min']);
		$max = floatval($rows['max']);

		if ($this->modificationsList)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('m.height')
				->from('shop_items')
				->join(array('shop_items', 'm'), 'shop_items.id', '=', 'm.modification_id')
				->where('m.shop_group_id', '=', 0)
				->where('m.modification_id', '>', 0)
				->where('m.deleted', '=', 0)
				->where('shop_items.deleted', '=', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 'm');

			// Ограничения на активность товаров
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'm');
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'shop_items');

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.height)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.height)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		if ($this->filterShortcuts)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('shop_items.height')
				->from('shop_items')
				->join(array('shop_items', 's'), 'shop_items.id', '=', 's.shortcut_id')
				->where('s.deleted', '=', 0)
				->where('s.shortcut_id', '>', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 's.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 's');

			// Ограничения на активность товаров
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 's');

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.height)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.height)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('min_height')
				->value(floor($min))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('max_height')
				->value(ceil($max))
		);

		return $this;
	}

	/**
	 * Add minimum and maximum weight
	 * @return self
	 */
	public function addMinMaxWeight()
	{
		$oShop = $this->getEntity();

		$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('shop_items.weight')
			->from('shop_items')
			->where('shop_items.deleted', '=', 0)
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.active', '=', 1)
			->where('shop_items.shortcut_id', '=', 0);

		$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

		$oMinMaxQueryBuilder = Core_QueryBuilder::select(
				array(Core_QueryBuilder::expression('MIN(t.weight)'), 'min'),
				array(Core_QueryBuilder::expression('MAX(t.weight)'), 'max')
			)
			->from(array($oSubMinMaxQueryBuilder, 't'));

		$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

		$min = floatval($rows['min']);
		$max = floatval($rows['max']);

		if ($this->modificationsList)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('m.weight')
				->from('shop_items')
				->join(array('shop_items', 'm'), 'shop_items.id', '=', 'm.modification_id')
				->where('m.shop_group_id', '=', 0)
				->where('m.modification_id', '>', 0)
				->where('m.deleted', '=', 0)
				->where('shop_items.deleted', '=', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 'shop_items.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 'm');

			// Ограничения на активность товаров
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'm');
			$this->_setItemsActivity($oSubMinMaxQueryBuilder, 'shop_items');

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.weight)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.weight)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		if ($this->filterShortcuts)
		{
			$oSubMinMaxQueryBuilder = Core_QueryBuilder::select('shop_items.weight')
				->from('shop_items')
				->join(array('shop_items', 't'), 'shop_items.id', '=', 't.shortcut_id')
				->where('t.deleted', '=', 0)
				->where('t.active', '=', 1)
				->where('t.shortcut_id', '>', 0);

			$this->applyFilterGroupCondition($oSubMinMaxQueryBuilder, 't.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oSubMinMaxQueryBuilder, 't');

			$oMinMaxQueryBuilder = Core_QueryBuilder::select(
					array(Core_QueryBuilder::expression('MIN(t.weight)'), 'min'),
					array(Core_QueryBuilder::expression('MAX(t.weight)'), 'max')
				)
				->from(array($oSubMinMaxQueryBuilder, 't'));

			$rows = $oMinMaxQueryBuilder->asAssoc()->execute()->current();

			!is_null($rows['min']) && $rows['min'] < $min && $min = $rows['min'];
			$rows['max'] > $max && $max = $rows['max'];
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('min_weight')
				->value(floor($min))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('max_weight')
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
				->where('shortcut_items.active', '=', 1)
				->where('shortcut_items.deleted', '=', 0);

		$this->_applyItemConditionsQueryBuilder($this->_Shop_Items->queryBuilder(), 'shortcut_items');

		// Ограничения на активность товаров
		$this->_setItemsActivity($this->_Shop_Items->queryBuilder(), 'shortcut_items');

		$this->_Shop_Items
			->queryBuilder()
			->close();

		return $this;
	}

	/**
	 * Current Sorting Column
	 * @var string|NULL
	 */
	protected $_sortingColumn = NULL;

	/**
	 * Current Sorting Direction
	 * @var string|NULL
	 */
	protected $_sortingDirection = NULL;

	/**
	 * Sets goods sorting, clear all previous sorting
	 * @param $column Column name, e.g. price
	 * @return self
	 */
	public function orderBy($column, $direction = 'ASC')
	{
		$this->_sortingDirection = $direction;

		switch ($column)
		{
			case 'price':
			case 'price_absolute':
			case 'absolute_price':
				$this->addAbsolutePrice();
				$this->_sortingColumn = 'price_absolute';
			break;
			default:
				$this->_sortingColumn = $column;
			break;
		}

		$this->shopItems()
			->queryBuilder()
			->clearOrderBy()
			->orderBy($this->_sortingColumn, $this->_sortingDirection);

		$this->addCacheSignature('orderBy=' . $this->_sortingColumn . $this->_sortingDirection);

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
	 * Array of Main properties conditions, see addFilter()
	 * @var array
	 */
	protected $_aFilterMainProperties = array();

	/**
	 * Add filter condition
	 * ->addFilter('property', 17, '=', 33) // Property 17
	 * ->addFilter('price', '>', 100) // Item Price
	 * ->addFilter('weight', '>', 5) // Main Property
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
					case 7: // checkbox
					case 12:
					case 13:
					case 14:
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
				if (in_array($args[0], $this->_aFilterAvailableMainValues))
				{
					$this->_aFilterMainProperties[$args[0]][] = array($args[1], $args[2]);
				}
				else
				{
					throw new Core_Exception("addFilter(): the option '%option' doesn't allow",
						array('%option' => $args[0])
					);
				}
		}

		return $this;
	}

	/**
	 * Remove filter condition
	 * ->removeFilter('property', 17)
	 * ->removeFilter('price')
	 * ->removeFilter('weight')
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
				if (in_array($args[0], $this->_aFilterAvailableMainValues))
				{
					if (isset($this->_aFilterMainProperties[$args[0]]))
					{
						unset($this->_aFilterMainProperties[$args[0]]);
					}
				}
				else
				{
					throw new Core_Exception("removeFilter(): the option '%option' doesn't allow",
						array('%option' => $args[0])
					);
				}
		}

		return $this;
	}

	/**
	 * Applied Filter. 0 - basic, 1 - fast
	 * @var NULL|int
	 */
	protected $_appliedFilter = NULL;

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

		// Main properties
		foreach ($this->_aFilterMainProperties as $mainPropertyName => $aMainPropertyValues)
		{
			foreach ($aMainPropertyValues as $aMainPropertyValue)
			{
				list($condition, $value) = $aMainPropertyValue;

				$this->shopItems()->queryBuilder()->where($mainPropertyName, $condition, $value);

				$this->_addFilterMainPropertyToXml($mainPropertyName, $condition, $value);
			}
		}

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
			$this->_appliedFilter = 1;

			$tableName = $this->getFilterTableName();

			$QB = $this->shopItems()->queryBuilder();

			$QB
				->distinct()
				//->join($tableName, 'shop_items.id', '=', $tableName . '.shop_item_id')
				;

			$this->_joinShopFilter($QB);

			// Filter by properties
			$this->applyFastFilterProperties($QB);

			foreach ($this->_aFilterProperties as $iPropertyId => $aTmpProperties)
			{
				foreach ($aTmpProperties as $aTmpProperty)
				{
					list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;

					$this->_addFilterPropertyToXml($oProperty, $condition, $aPropertyValues);
				}
			}

			// Filter by prices
			$this->applyFastFilterPrices($QB);

			foreach ($this->_aFilterPrices as $aTmpPrice)
			{
				list($condition, $value) = $aTmpPrice;

				$this->_addFilterPriceToXml($condition, $value);
			}
		}

		return $this;
	}

	/**
	 * Apply Fast Filter Properties
	 * @param Core_QueryBuilder_Select $QB
	 * @param array $excludeIDs
	 * @return self
	 */
	public function applyFastFilterProperties($QB, array $excludeIDs = array())
	{
		$oShop = $this->getEntity();

		$tableName = $this->getFilterTableName();

		$aBasicFilterProperties = array();

		foreach ($this->_aFilterProperties as $iPropertyId => $aTmpProperties)
		{
			if (!in_array($iPropertyId, $excludeIDs))
			{
				foreach ($aTmpProperties as $aTmpProperty)
				{
					list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;

					if ($oProperty->Shop_Item_Property->filter)
					{
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
							// 7 - Checkbox, not '' and not 0
							$oProperty->type == 7 && $aPropertyValues[0] != '' && $aPropertyValues = array(1);

							// Not strict mode and Type is '7 - Checkbox' or '3 - List'
							$bCheckUnset = is_array($this->filterStrictMode)
								? !in_array($oProperty->id, $this->filterStrictMode)
								: !$this->filterStrictMode
									&& $oProperty->type != 7
									&& $oProperty->type != 3;

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
					else
					{
						$aBasicFilterProperties[$oProperty->id][] = $aTmpProperty;
					}
				}
			}
		}

		// Свойства, по которым не построен быстрый фильтр
		if (count($aBasicFilterProperties))
		{
			$this->_basicFilterProperties($aBasicFilterProperties);
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
		if (count($this->_aFilterPrices))
		{
			//$this->addAbsolutePrice();
			$tableName = $this->getFilterTableName();
			$priceTableName = $this->getFilterPriceTableName();

			// Доступные поля для фильтрации по ценам
			$aPriceFields = $this->getFilterPriceAvailableFields();

			// Клиенту доступно более одной цены или одна цена, но не равная price_absolute
			(count($aPriceFields) > 1 || $aPriceFields[0] != 'price_absolute')
				&& $QB->join($priceTableName, $tableName . '.shop_item_id', '=', $priceTableName . '.shop_item_id');

			count($aPriceFields) > 1 && $QB->open();

			foreach ($aPriceFields as $sPriceField)
			{
				foreach ($this->_aFilterPrices as $aTmpPrice)
				{
					list($condition, $value) = $aTmpPrice;

					$QB->where(($sPriceField != 'price_absolute' ? $priceTableName : $tableName) . '.' . $sPriceField, $condition, $value);
				}

				count($aPriceFields) > 1 && $QB->setOr();
			}

			count($aPriceFields) > 1 && $QB->close();
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
			$this->_appliedFilter = 0;

			$this->_basicFilterProperties($this->_aFilterProperties);
		}

		// Filter by prices
		if (count($this->_aFilterPrices))
		{
			$this->_basicFilterPrices($this->_aFilterPrices);
		}

		return $this;
	}

	/**
	 * Apply Basic Filter Properties
	 * @param array $aFilterProperties
	 * @return self
	 */
	protected function _basicFilterProperties(array $aFilterProperties)
	{
		$aTableNames = array();

		$this->shopItems()->queryBuilder()
			->straightJoin(FALSE) // Так как есть дополнительные опции по basicFilter, то оптимизатор работает лучше, чем быстрый фильтр с straightJoin и затем базовым фильтром
			->leftJoin('shop_item_properties', 'shop_items.shop_id', '=', 'shop_item_properties.shop_id')
			->setAnd()
			->open();

		foreach ($aFilterProperties as $iPropertyId => $aTmpProperties)
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

					// Not strict mode and Type is '7 - Checkbox' or '3 - List'
					$bCheckUnset = is_array($this->filterStrictMode)
						? !in_array($oProperty->id, $this->filterStrictMode)
						: !$this->filterStrictMode
							&& $oProperty->type != 7
							&& $oProperty->type != 3;

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
			->close();

		!$this->modificationsGroup
			&& $this->shopItems()->queryBuilder()->groupBy('shop_items.id');

		foreach ($aTableNames as $tableName)
		{
			$this->shopItems()->queryBuilder()
				->leftJoin($tableName, 'shop_items.id', '=', $tableName . '.entity_id',
					array(
						array('AND' => array('shop_item_properties.property_id', '=', Core_QueryBuilder::expression($tableName . '.property_id')))
					)
				);
		}

		$havingCount = count($aFilterProperties);

		$havingCount > 1
			&& $this->shopItems()->queryBuilder()
					->having(Core_Querybuilder::expression('COUNT(DISTINCT `shop_item_properties`.`property_id`)'), '=', $havingCount);

		return $this;
	}

	/**
	 * Apply Basic Filter Prices
	 * @param array $aFilterPrices
	 * @return self
	 */
	protected function _basicFilterPrices(array $aFilterPrices)
	{
		$this->addAbsolutePrice();

		foreach ($aFilterPrices as $aTmpPrice)
		{
			list($condition, $value) = $aTmpPrice;

			$this->shopItems()->queryBuilder()->having('price_absolute', $condition, $value);

			$this->_addFilterPriceToXml($condition, $value);
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
			switch ($oProperty->type)
			{
				case 8: // date
					$propertyValue = $propertyValue == '0000-00-00 00:00:00'
						? ''
						: Core_Date::sql2date($propertyValue);
				break;
				case 9: // datetime
					$propertyValue = $propertyValue == '0000-00-00 00:00:00'
						? ''
						: Core_Date::sql2datetime($propertyValue);
				break;
			}

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->addAttribute('condition', $condition)
					->name($xmlName)
					->value($propertyValue)
			);
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
				->addAttribute('condition', $condition)
				->name($xmlName)
				->value($value)
		);
	}

	/**
	 * Add Filter Main Property to the XML
	 * @param string $mainPropertyName
	 * @param string $condition
	 * @param string $value
	 * @return self
	 */
	protected function _addFilterMainPropertyToXml($mainPropertyName, $condition, $value)
	{
		switch ($condition)
		{
			case '>=':
				$xmlName = $mainPropertyName . '_from';
			break;
			case '<=':
				$xmlName = $mainPropertyName . '_to';
			break;
			default:
				$xmlName = $mainPropertyName;
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->addAttribute('condition', $condition)
				->name($xmlName)
				->value($value)
		);

		return $this;
	}

	/**
	 * AbsolutePrice has been added
	 * @var boolean
	 */
	protected $_addedAbsolutePrice = FALSE;

	/**
	 * Add `price_absolute` to the query
	 * @return self
	 */
	public function addAbsolutePrice()
	{
		if (!$this->_addedAbsolutePrice)
		{
			$this->_addedAbsolutePrice = TRUE;

			$oShop = $this->getEntity();

			$this->applyAbsolutePrice($this->shopItems()->queryBuilder());

			$oShop = $this->getEntity();

			if ($oShop->filter)
			{
				$tableName = $this->getFilterTableName();

				// Если нет ограничений по доп. условиям свойств, то ограничиваем primary
				!count($this->_aFilterProperties)
					? $this->_Shop_Items->queryBuilder()->where("{$tableName}.primary", '=', 1)
					: $this->_Shop_Items->queryBuilder()->groupBy('shop_items.id');
			}
		}

		return $this;
	}

	public function isShopFilterJoined($QB)
	{
		$tableName = $this->getFilterTableName();

		// Check if already joined
		$aFrom = $QB->getFrom();

		return in_array($tableName, $aFrom);
	}

	protected function _joinShopFilter($QB)
	{
		if (!$this->isShopFilterJoined($QB))
		{
			$tableName = $this->getFilterTableName();

			$QB
				->clearFrom()
				->from($tableName)
				->straightJoin()
				->join('shop_items', 'shop_items.id', '=', $tableName . '.shop_item_id');
		}

		return $this;
	}

	/**
	 * Apply `price_absolute` to the Query Builder
	 * @param Core_QueryBuilder_Select $QB
	 * @param string $tableName, default 'shop_items'
	 * @return Core_QueryBuilder_Select
	 */
	public function applyAbsolutePrice($QB, $tableName = 'shop_items')
	{
		$oShop = $this->getEntity();

		if ($oShop->filter)
		{
			$this->_joinShopFilter($QB);
		}
		else
		{
			$tableName = Core_DataBase::instance()->quoteTableName($tableName);

			// Получаем список валют магазина
			$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll();

			$query_tax = "IF(`shop_taxes`.`tax_is_included` IS NULL OR `shop_taxes`.`tax_is_included` = 1, 0, {$tableName}.`price` * `shop_taxes`.`rate` / 100)";
			$query_currency_switch = "{$tableName}.`price` + {$query_tax}";
			foreach ($aShop_Currencies as $oShop_Currency)
			{
				// Получаем коэффициент пересчета для каждой валюты
				$currency_coefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					$oShop_Currency, $oShop->Shop_Currency
				);

				$query_currency_switch = "IF ({$tableName}.`shop_currency_id` = '{$oShop_Currency->id}', IF (COUNT(`shop_discounts`.`id`), (({$tableName}.`price` + {$query_tax}) * (1 - SUM(DISTINCT IF(`shop_discounts`.`type` = 0, `shop_discounts`.`value`, 0)) / 100)) * {$currency_coefficient} - SUM(DISTINCT IF(`shop_discounts`.`type`, `shop_discounts`.`value`, 0)), ({$tableName}.`price`) * {$currency_coefficient}), {$query_currency_switch})";
			}

			$current_date = date('Y-m-d H:i:s');
			$current_time = date('H:i:s');
			$dayFieldName = 'day' . date('N');

			$aSiteuser_Group_IDs = array(0);

			if (Core::moduleIsActive('siteuser'))
			{
				$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
				if ($oSiteuser)
				{
					$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
					foreach ($aSiteuser_Groups as $oSiteuser_Group)
					{
						$aSiteuser_Group_IDs[] = $oSiteuser_Group->id;
					}
				}
			}

			$QB
				->select(array(Core_QueryBuilder::expression($query_currency_switch), 'price_absolute'))
				->leftJoin('shop_item_discounts', 'shop_items.id', '=', 'shop_item_discounts.shop_item_id')
				->leftJoin('shop_discount_siteuser_groups', 'shop_item_discounts.shop_discount_id', '=', 'shop_discount_siteuser_groups.shop_discount_id')
				->leftJoin('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id', array(
					array('AND' => array('shop_discount_siteuser_groups.siteuser_group_id', 'IN', $aSiteuser_Group_IDs)),
					array('AND' => array('shop_discounts.coupon', '=', 0)),
					array('AND' => array('shop_discounts.active', '=', 1)),
					array('AND' => array('shop_discounts.deleted', '=', 0)),
					array('AND' => array('shop_discounts.' . $dayFieldName, '=', 1)),
					array('AND' => array('shop_discounts.start_time', '<=', $current_time)),
					array('AND' => array('shop_discounts.end_time', '>=', $current_time)),
					array('AND' => array('shop_discounts.start_datetime', '<=', $current_date)),
					array('AND (' => array('shop_discounts.end_datetime', '>=', $current_date)),
						array('OR' => array('shop_discounts.end_datetime', '=', '0000-00-00 00:00:00')),
					array(')' => NULL)
				))
				->leftJoin('shop_taxes', 'shop_taxes.id', '=', 'shop_items.shop_tax_id')
				->clearGroupBy()
				->groupBy('shop_items.id');

				/*!$this->modificationsGroup
					&& $QB->groupBy('shop_items.id');*/
		}

		return $QB;
	}

	/**
	 * Backward compatible.
	 * @return Core_QueryBuilder_Select
	 */
	protected function _applyAbsolutePrice($QB, $tableName = 'shop_items')
	{
		return $this->applyAbsolutePrice($QB, $tableName);
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
			? $oShop_Item_Property_List->getPropertiesForGroup($this->group && $this->subgroups && !is_array($this->group)
					? $this->getSubgroups($this->group)
					: $this->group)
			: $oShop_Item_Property_List->Properties->findAll();

		foreach ($aProperties as $oProperty)
		{
			$value = Core_Array::get($aData, 'property_' . $oProperty->id);
			if ($value)
			{
				$this->addFilter('property', $oProperty->id, '=', $this->_convertReceivedPropertyValue($oProperty, $value));
			}
			elseif (!is_null(Core_Array::get($aData, 'property_' . $oProperty->id . '_from')))
			{
				$tmpFrom = Core_Array::get($aData, 'property_' . $oProperty->id . '_from');
				$tmpTo = Core_Array::get($aData, 'property_' . $oProperty->id . '_to');

				$tmpFrom != ''
					&& $this->addFilter('property', $oProperty->id, '>=', $this->_convertReceivedPropertyValue($oProperty, $tmpFrom));

				$tmpTo != ''
					&& $this->addFilter('property', $oProperty->id, '<=', $this->_convertReceivedPropertyValue($oProperty, $tmpTo));
			}
		}

		return $this;
	}

	/**
	 * Convert property value, e.g. '23.11.2020' => '2020-11-23 00:00:00'
	 * @param Property_Model $oProperty
	 * @param mixed $value
	 * @return string
	 */
	protected function _convertReceivedPropertyValue(Property_Model $oProperty, $value)
	{
		switch ($oProperty->type)
		{
			case 7: // checkbox
				$value = 1;
			break;
			case 8: // date
				$value != ''
					&& $value = Core_Date::date2sql($value);
			break;
			case 9: // datetime
				$value != ''
					&& $value = Core_Date::datetime2sql($value);
			break;
		}

		return $value;
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
	 * Get Filter Main Properties
	 * @return array
	 */
	public function getFilterMainProperties()
	{
		return $this->_aFilterMainProperties;
	}

	/**
	 * Set Filter Main Properties
	 * @param array $array
	 * @return self
	 */
	public function setFilterMainProperties(array $array)
	{
		$this->_aFilterMainProperties = $array;
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
			->where('shop_producers.active', '=', 1);

		if ($oShop->filter)
		{
			$tableName = $this->getFilterTableName();

			$oShop_Producers->queryBuilder()
				->clearSelect();

			// При включенном filterShortcuts используется ниже unionAll и он должен совпадать со схемой (shop_item_id, shop_producer_id), а не с shop_producers.*
			if ($this->subgroups || $this->filterShortcuts)
			{
				$oShop_Producers->queryBuilder()
					->select('shop_producers.*', array('tmp_counts.ct', 'dataCount'))
					->join(
						array(
							Core_QueryBuilder::select('tmp_distincts.shop_producer_id', array(Core_QueryBuilder::expression('COUNT(1)'), 'ct'))
								->from(array($oQB = Core_QueryBuilder::select()
										->distinct()
										->select($tableName . '.shop_item_id', $tableName . '.shop_producer_id')
										->from($tableName)
										->where($tableName . '.primary', '=', 1)
									, 'tmp_distincts')
								)
								->groupBy('tmp_distincts.shop_producer_id')
							, 'tmp_counts'
						), 'tmp_counts.shop_producer_id', '=', 'shop_producers.id'
					);
			}
			else
			{
				$oQB = $oShop_Producers->queryBuilder()
					->select('shop_producers.*', array(Core_QueryBuilder::expression('COUNT(DISTINCT ' . $tableName . '.`shop_item_id`)'), 'dataCount'))
					->join($tableName, 'shop_producers.id', '=', $tableName . '.shop_producer_id')
					->groupBy($tableName . '.shop_producer_id');
			}

			$this->applyFilterGroupCondition($oQB, $tableName . '.shop_group_id');

			!$this->modificationsList
				&& $oQB->where($tableName . '.modification_id', '=', 0);

			// Filter by properties
			$this->applyFastFilterProperties($oQB);

			// Filter by prices
			$this->applyFastFilterPrices($oQB);

			if (!is_null($this->tag) && Core::moduleIsActive('tag'))
			{
				is_null($this->_oTag)
					&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

				if ($this->_oTag)
				{
					$oQB
						->join('shop_items', 'shop_items.id', '=', $tableName . '.shop_item_id')
						->join('tag_shop_items', 'shop_items.id', '=', 'tag_shop_items.shop_item_id')
						->where('tag_shop_items.tag_id', '=', $this->_oTag->id);
				}
			}

			// Ограничение по складу через быстрый фильтр
			if (in_array($this->warehouseMode, array('in-stock', 'in-stock-modification')))
			{
				$oQB->where($tableName . '.available', '>', 0);
			}
			elseif ($this->warehouseMode == 'out-of-stock')
			{
				$oQB->where($tableName . '.available', '<=', 0);
			}

			if ($this->filterShortcuts)
			{
				$oCore_QueryBuilder_Select_Shortcuts = Core_QueryBuilder::select('shop_items.shortcut_id')
					->from('shop_items')
					->where('shop_items.deleted', '=', 0)
					->where('shop_items.active', '=', 1)
					->where('shop_items.shortcut_id', '>', 0);

				$this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Shortcuts, 'shop_items.shop_group_id');

				// Стандартные ограничения для товаров
				$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

				// Ограничения на активность товаров
				$this->_setItemsActivity($oCore_QueryBuilder_Select_Shortcuts);

				// Ограничения по остаткам на складах
				$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

				if (!is_null($this->tag) && Core::moduleIsActive('tag'))
				{
					is_null($this->_oTag)
						&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

					if ($this->_oTag)
					{
						$oCore_QueryBuilder_Select_Shortcuts
							->join('tag_shop_items', 'shop_items.id', '=', 'tag_shop_items.shop_item_id')
							->where('tag_shop_items.tag_id', '=', $this->_oTag->id);
					}
				}

				// INNER JOIN с filter_group не позволит выбрать оригинальные товары из других групп через OR ... IN (SELECT ...), поэтому UNION ALL
				/*$oQB
					->setOr()
					->where($oShop->filter ? $tableName . '.shop_item_id' : 'shop_items.id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);*/

				$oQBFS = Core_QueryBuilder::select()
					->distinct()
					->select($tableName . '.shop_item_id', $tableName . '.shop_producer_id')
					->from($tableName)
					->where($tableName . '.primary', '=', 1)
					->where($tableName . '.shop_item_id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);

				$oQB->unionAll($oQBFS);
			}
		}
		else
		{
			$oQB = $oShop_Producers->queryBuilder()
				->distinct()
				->select('shop_producers.*')
				->join('shop_items', 'shop_items.shop_producer_id', '=', 'shop_producers.id')
				->where('shop_items.deleted', '=', 0);

			$this->applyFilterGroupCondition($oShop_Producers->queryBuilder(), 'shop_items.shop_group_id');

			// Стандартные ограничения для товаров
			$this->_applyItemConditionsQueryBuilder($oShop_Producers->queryBuilder());

			// Ограничения на активность товаров
			$this->_setItemsActivity($oShop_Producers->queryBuilder());

			// Ограничения по остаткам на складах
			$this->_applyWarehouseConditionsQueryBuilder($oShop_Producers->queryBuilder());

			!$this->modificationsList
				&& $oShop_Producers->queryBuilder()->where('shop_items.modification_id', '=', 0);

			if (!is_null($this->tag) && Core::moduleIsActive('tag'))
			{
				is_null($this->_oTag)
					&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

				if ($this->_oTag)
				{
					$oShop_Producers
						->queryBuilder()
						->join('tag_shop_items', 'shop_items.id', '=', 'tag_shop_items.shop_item_id')
						->where('tag_shop_items.tag_id', '=', $this->_oTag->id);
				}
			}

			if ($this->filterShortcuts)
			{
				$oCore_QueryBuilder_Select_Shortcuts = Core_QueryBuilder::select('shop_items.shortcut_id')
					->from('shop_items')
					->where('shop_items.deleted', '=', 0)
					->where('shop_items.active', '=', 1)
					->where('shop_items.shortcut_id', '>', 0);

				$this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Shortcuts, 'shop_items.shop_group_id');

				// Стандартные ограничения для товаров
				$this->_applyItemConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

				// Ограничения на активность товаров
				$this->_setItemsActivity($oCore_QueryBuilder_Select_Shortcuts);

				// Ограничения по остаткам на складах
				$this->_applyWarehouseConditionsQueryBuilder($oCore_QueryBuilder_Select_Shortcuts);

				$oQB
					->setOr()
					->where(/*$oShop->filter ? $tableName . '.shop_item_id' : */'shop_items.id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);

				if (!is_null($this->tag) && Core::moduleIsActive('tag'))
				{
					is_null($this->_oTag)
						&& $this->_oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

					if ($this->_oTag)
					{
						$oCore_QueryBuilder_Select_Shortcuts
							->join('tag_shop_items', 'shop_items.id', '=', 'tag_shop_items.shop_item_id')
							->where('tag_shop_items.tag_id', '=', $this->_oTag->id);
					}
				}
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
						->value($oShop_Producer->dataCount)
				);
			}

			// Добавляем производителя потомком XML-сущности
			$oProducersXmlEntity->addEntity($oShop_Producer);
		}

		return $this;
	}

	/**
	 * Groups Tree For fillShopGroups()
	 * @var NULL|array
	 */
	protected $_aGroupTree = NULL;

	/**
	 * Fill $this->_aGroupTree array
	 * @param int $parent_id
	 * @return array
	 */
	public function fillShopGroups($parent_id = 0)
	{
		$parent_id = intval($parent_id);

		if (is_null($this->_aGroupTree))
		{
			$this->_aGroupTree = array();

			$oShop = $this->getEntity();

			$oQB = Core_QueryBuilder::select('id', 'parent_id', 'shortcut_id')
				->from('shop_groups')
				->where('shop_id', '=', $oShop->id)
				//->where('shortcut_id', '=', 0)
				->where('deleted', '=', 0);

			$this->groupsActivity = strtolower($this->groupsActivity);
			if ($this->groupsActivity != 'all')
			{
				$oQB
					->where('active', '=', $this->groupsActivity == 'inactive' ? 0 : 1);
			}

			$aTmp = $oQB->execute()->asAssoc()->result();
			foreach ($aTmp as $aGroup)
			{
				$this->_aGroupTree[$aGroup['parent_id']][] = $aGroup['shortcut_id']
					? $aGroup['shortcut_id']
					: $aGroup['id'];
			}
		}

		$aReturn = array();

		if (isset($this->_aGroupTree[$parent_id]))
		{
			foreach ($this->_aGroupTree[$parent_id] as $shop_group_id)
			{
				$aReturn[] = $shop_group_id;
				$aReturn = array_merge($aReturn, $this->fillShopGroups($shop_group_id));
			}
		}

		return $aReturn;
	}

	/**
	 * Array of subgroups
	 * @var array
	 */
	protected $_subgroups = array();

	/**
	 * Get array of subgroups ID, inc. $group_id
	 * @param int $group_id
	 * @return array
	 */
	public function getSubgroups($group_id)
	{
		if (!isset($this->_subgroups[$group_id]))
		{
			$this->_subgroups[$group_id] = $this->fillShopGroups($group_id);
			// Set first ID as current group
			array_unshift($this->_subgroups[$group_id], $group_id);
		}

		return $this->_subgroups[$group_id];
	}
}