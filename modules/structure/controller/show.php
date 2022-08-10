<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ структуры сайта.
 *
 * Доступные методы:
 *
 * - menu($menuId) вывод узлов структуры меню $menu, по умолчанию NULL (вывод из всех меню)
 * - parentId($parentId) идентификатор родительского узла, по умолчанию 0
 * - level($level) выводить узлы структуры только до уровня вложенности $level
 * - showProperties(TRUE|FALSE) выводить значения дополнительных свойств усзлов структуры, по умолчанию FALSE
 * - sortPropertiesValues(TRUE|FALSE) сортировать значения дополнительных свойств, по умолчанию TRUE.
 * - showInformationsystemGroups(TRUE|FALSE) выводить связанные с узлом структуры группы информационной системы, по умолчанию FALSE
 * - showInformationsystemItems(TRUE|FALSE) выводить связанные с узлом структуры информационные элементы, по умолчанию FALSE
 * - showShopGroups(TRUE|FALSE) выводить связанные с узлом структуры группы магазина, по умолчанию FALSE
 * - showShopItems(TRUE|FALSE) выводить связанные с узлом структуры товары, по умолчанию FALSE
 * - showInformationsystemGroupProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств групп информационной системы, по умолчанию FALSE
 * - showInformationsystemItemProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств информационных элементов, по умолчанию FALSE
 * - showShopGroupProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств групп магазина, по умолчанию FALSE
 * - showShopItemProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE
 * - showShopItemAssociated(TRUE|FALSE) выводить сопутствующие товары, по умолчанию FALSE
 * - forbiddenTags(array('name')) массив тегов узла структуры, запрещенных к передаче в генерируемый XML
 * - cache(TRUE|FALSE) использовать кэширование, по умолчанию TRUE
 * - showPanel(TRUE|FALSE) показывать панель быстрого редактирования, по умолчанию TRUE
 * - onStep(3000) количество элементов, выбираемых запросом за 1 шаг, по умолчанию 500
 *
 * Доступные свойства:
 *
 * - currentStructureId идентификатор узла структуры
 *
 * <code>
 * $Structure_Controller_Show = new Structure_Controller_Show(
 * 		Core_Entity::factory('Site', 1)
 * 	);
 *
 * 	$Structure_Controller_Show
 * 		->xsl(
 * 			Core_Entity::factory('Xsl')->getByName('Меню')
 * 		)
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Structure
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Structure_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'menu',
		'parentId',
		'level',
		'showProperties',
		'sortPropertiesValues',
		'showInformationsystemGroups',
		'showInformationsystemItems',
		'showShopGroups',
		'showShopItems',
		'showInformationsystemGroupProperties',
		'showInformationsystemItemProperties',
		'showShopGroupProperties',
		'showShopItemProperties',
		'showShopItemAssociated',
		'forbiddenTags',
		'cache',
		'currentStructureId',
		'showPanel',
		'onStep',
	);

	/**
	 * List of structuries
	 * @var array
	 */
	protected $_aStructures = array();

	/**
	 * List of properties
	 * @var array
	 */
	protected $_aProperties = array();

	/**
	 * List of property directories
	 * @var array
	 */
	protected $_aProperty_Dirs = array();

	/**
	 * Array of siteuser's groups allowed for current siteuser
	 * @var array
	 */
	protected $_aSiteuserGroups = array();

	/**
	 * Cache name
	 * @var string
	 */
	protected $_cacheName = 'structure_show';

	/**
	 * Tags for cache
	 */
	protected $_aTags = array();

	/**
	 * Constructor.
	 * @param Site_Model $oSite site
	 */
	public function __construct(Site_Model $oSite)
	{
		parent::__construct($oSite->clearEntities());

		$this->_Structure = $oSite->Structures;

		$this->_aSiteuserGroups = $this->_getSiteuserGroups();

		$this->_Structure
			->queryBuilder()
			->select('structures.*')
			->where('structures.active', '=', 1)
			->where('structures.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
			->clearOrderBy()
			->orderBy('structures.sorting')
			->orderBy('structures.name');

		$this->showProperties = $this->showInformationsystemGroups = $this->showInformationsystemItems = $this->showShopGroups = $this->showShopItems = $this->showInformationsystemGroupProperties = $this->showInformationsystemItemProperties = $this->showShopGroupProperties = $this->showShopItemProperties = $this->showShopItemAssociated = FALSE;

		$this->showPanel = $this->cache = $this->sortPropertiesValues = TRUE;

		$this->currentStructureId = Core_Page::instance()->structure->id;

		$this->onStep = 500;
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

				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll(FALSE);
				foreach ($aSiteuser_Groups as $oSiteuser_Group)
				{
					$aSiteuserGroups[] = $oSiteuser_Group->id;
				}
			}
		}

		return $aSiteuserGroups;
	}

	/**
	 * Structure object
	 * @var array
	 */
	protected $_Structure = NULL;

	/**
	 * Set Structure
	 * @return Structure_Model
	 */
	public function structure()
	{
		return $this->_Structure;
	}

	/**
	 * List of information systems
	 * @var array
	 */
	protected $_Informationsystems = array();

	/**
	 * List of shops
	 * @var array
	 */
	protected $_Shops = array();

	/**
	 * Get _Informationsystems set
	 * @return array
	 */
	public function getInformationsystems()
	{
		return $this->_Informationsystems;
	}

	/**
	 * Get _Shops set
	 * @return array
	 */
	public function getShops()
	{
		return $this->_Shops;
	}

	/**
	 * Set _Shops set
	 * @param array $array
	 * @return self
	 */
	public function setShops(array $array)
	{
		$this->_Shops = $array;
		return $this;
	}

	/**
	 * Set _Informationsystems set
	 * @param array $array
	 * @return self
	 */
	public function setInformationsystems(array $array)
	{
		$this->_Informationsystems = $array;
		return $this;
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
	 * Show built data
	 * @return self
	 * @hostcms-event Structure_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oSite = $this->getEntity();

		// Load user BEFORE FOUND_ROWS()
		$oUser = Core_Auth::getCurrentUser();

		$this->showPanel && Core::checkPanel()
			&& in_array($this->_mode, array('xsl', 'tpl'))
			&& $oUser && $oUser->checkModuleAccess(array('structure'), $oSite)
			&& $this->_showPanel();

		$bCache = $this->cache && Core::moduleIsActive('cache');
		if ($bCache)
		{
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$inCache = $oCore_Cache->get($cacheKey = strval($this), $this->_cacheName);

			if (!is_null($inCache))
			{
				echo $inCache;
				return $this;
			}

			$this->_aTags = array('structure_' . intval($this->parentId));
		}

		$bTpl = $this->_mode == 'tpl';

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('parent_id')
				->value(intval($this->parentId))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('current_structure_id')
				->value($this->currentStructureId)
		);

		$aStructures = is_null($this->menu)
			? $this->_Structure->findAll()
			: $this->_Structure->getAllByStructure_menu_id($this->menu, TRUE, is_array($this->menu) && count($this->menu) ? 'IN' : '=');

		foreach ($aStructures as $oStructure)
		{
			$this->_aStructures[$oStructure->parent_id][] = $oStructure->clearEntities();
		}

		// Показывать дополнительные свойства
		if ($this->showProperties)
		{
			$oStructure_Property_List = Core_Entity::factory('Structure_Property_List', $oSite->id);

			$aProperties = $oStructure_Property_List->Properties->findAll(FALSE);
			foreach ($aProperties as $oProperty)
			{
				$this->_aProperties[$oProperty->property_dir_id][] = $oProperty;

				// Load all values for property
				$oProperty->loadAllValues();
			}

			$aProperty_Dirs = $oStructure_Property_List->Property_Dirs->findAll(FALSE);
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aProperty_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			$this->_addPropertyList(0, $this);
		}

		is_null($this->parentId) && $this->parentId = 0;

		if (Core::moduleIsActive('informationsystem') && ($this->showInformationsystemGroups || $this->showInformationsystemItems))
		{
			$this->_selectInformationsystems();
		}

		if (Core::moduleIsActive('shop') && ($this->showShopGroups || $this->showShopItems))
		{
			$this->_selectShops();
		}

		// XSL
		if (!$bTpl)
		{
			$this->_addStructuresByParentId($this->parentId, $this);
		}
		// TPL
		else
		{
			$this->assign('controller', $this);
			$this->assign('aStructures', $this->_aStructures);
		}

		echo $content = $this->get();
		$bCache && $oCore_Cache->set($cacheKey, $content, $this->_cacheName, $this->_aTags);

		// Clear
		$this->_aTags = $this->_aStructures = $this->_aProperty_Dirs = $this->_aProperties
			= $this->_Informationsystems = $this->_Shops = array();

		return $this;
	}

	/**
	 * Select informationsystems
	 * @return self
	 * @hostcms-event Structure_Controller_Show.onAfterSelectInformationsystems
	 */
	protected function _selectInformationsystems()
	{
		$oSite = $this->getEntity();

		$aInformationsystems = $oSite->Informationsystems->findAll(FALSE);
		foreach ($aInformationsystems as $oInformationsystem)
		{
			$oInformationsystem->structure_id && $this->_Informationsystems[$oInformationsystem->structure_id] = $oInformationsystem;
		}

		Core_Event::notify(get_class($this) . '.onAfterSelectInformationsystems', $this, array($this->_Informationsystems));

		return $this;
	}

	/**
	 * Select shops
	 * @return self
	 * @hostcms-event Structure_Controller_Show.onAfterSelectShops
	 */
	protected function _selectShops()
	{
		$oSite = $this->getEntity();

		$aShops = $oSite->Shops->findAll(FALSE);
		foreach ($aShops as $oShop)
		{
			$oShop->structure_id && $this->_Shops[$oShop->structure_id] = $oShop;
		}

		Core_Event::notify(get_class($this) . '.onAfterSelectShops', $this, array($this->_Shops));

		return $this;
	}

	/**
	 * Create the tree of structures
	 * @param int $parent_id
	 * @param object $parentObject
	 * @param int $level
	 * @return self
	 */
	protected function _addStructuresByParentId($parent_id, $parentObject, $level = 0)
	{
		if (isset($this->_aStructures[$parent_id]))
		{
			foreach ($this->_aStructures[$parent_id] as $oStructure)
			{
				$this->applyForbiddenTags($oStructure);

				$parentObject->addEntity($oStructure);

				$this->_aTags[] = 'structure_' . $oStructure->id;

				// Properties for structure entity
				$oStructure->showXmlProperties($this->showProperties, $this->sortPropertiesValues);

				if (is_null($this->level) || $level < $this->level)
				{
					$this->_addStructuresByParentId($oStructure->id, $oStructure, $level + 1);
				}
			}
		}

		if (is_null($this->level) || $level < $this->level)
		{
			// Informationsystem
			if (($this->showInformationsystemGroups || $this->showInformationsystemItems) && isset($this->_Informationsystems[$parent_id]))
			{
				$this->_addInformationsystemGroups($parentObject, $this->_Informationsystems[$parent_id], $level + 1);
			}

			// Shop
			if (($this->showShopGroups || $this->showShopItems) && isset($this->_Shops[$parent_id]))
			{
				$this->_addShopGroups($parentObject, $this->_Shops[$parent_id], $level + 1);
			}
		}

		return $this;
	}

	/**
	 * Create the tree of property dirs and properties
	 * @param int $parent_id property group ID
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addPropertyList($parent_id, $parentObject)
	{
		if (isset($this->_aProperty_Dirs[$parent_id]))
		{
			foreach ($this->_aProperty_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addPropertyList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aProperties[$parent_id]))
		{
			$parentObject->addEntities($this->_aProperties[$parent_id]);
		}

		return $this;
	}

	/**
	 * List of groups
	 * @var array
	 */
	protected $_aInformationsystem_Groups = array();

	/**
	 * List of items
	 * @var array
	 */
	protected $_aInformationsystem_Items = array();

	/**
	 * Get _aInformationsystem_Groups set
	 * @return array
	 */
	public function getInformationsystemGroups()
	{
		return $this->_aInformationsystem_Groups;
	}

	/**
	 * Get _aInformationsystem_Items set
	 * @return array
	 */
	public function getInformationsystemItems()
	{
		return $this->_aInformationsystem_Items;
	}

	/**
	 * Fill _aInformationsystem_Groups and _aInformationsystem_Items
	 * @param Informationsystem_Model $oInformationsystem
	 * @param object $parentObject
	 * @hostcms-event Structure_Controller_Show.onBeforeFindInformationsystemGroups
	 * @hostcms-event Structure_Controller_Show.onBeforeFindInformationsystemItems
	 * @return self
	 */
	public function fillInformationsystem($oInformationsystem, $parentObject = NULL)
	{
		$dateTime = Core_Date::timestamp2sql(time());

		$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;
		$oInformationsystem_Groups->queryBuilder()
			->where('informationsystem_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
			->where('informationsystem_groups.active', '=', 1)
			->clearOrderBy();

		switch ($oInformationsystem->groups_sorting_direction)
		{
			case 0:
				$groups_sorting_direction = 'ASC';
				break;
			case 1:
			default:
				$groups_sorting_direction = 'DESC';
		}

		// Определяем поле сортировки информационных групп
		switch ($oInformationsystem->groups_sorting_field)
		{
			case 0:
				$oInformationsystem_Groups
					->queryBuilder()
					->orderBy('informationsystem_groups.name', $groups_sorting_direction);
				break;
			case 1:
			default:
				$oInformationsystem_Groups
					->queryBuilder()
					->orderBy('informationsystem_groups.sorting', $groups_sorting_direction);
				break;
		}

		$this->_aInformationsystem_Groups = array();

		Core_Event::notify(get_class($this) . '.onBeforeFindInformationsystemGroups', $this, array($oInformationsystem_Groups, $parentObject, $oInformationsystem));

		// findAll(FALSE) isn't necessary because Informationsystem_Group needs for getPath()
		$aInformationsystem_Groups = $oInformationsystem_Groups->findAll();
		foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
		{
			$this->_aInformationsystem_Groups[$oInformationsystem_Group->parent_id][] = $oInformationsystem_Group;
		}

		// Informationsystem's items
		$this->_aInformationsystem_Items = array();

		if ($this->showInformationsystemItems)
		{
			$oInformationsystem_Items = $oInformationsystem->Informationsystem_Items;
			$oInformationsystem_Items->queryBuilder()
				->select('informationsystem_items.*')
				->open()
					->where('informationsystem_items.start_datetime', '<', $dateTime)
					->setOr()
					->where('informationsystem_items.start_datetime', '=', '0000-00-00 00:00:00')
				->close()
				->setAnd()
				->open()
					->where('informationsystem_items.end_datetime', '>', $dateTime)
					->setOr()
					->where('informationsystem_items.end_datetime', '=', '0000-00-00 00:00:00')
				->close()
				->where('informationsystem_items.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
				->where('informationsystem_items.active', '=', 1)
				->where('informationsystem_items.closed', '=', 0)
				->clearOrderBy();

			switch ($oInformationsystem->items_sorting_direction)
			{
				case 1:
					$items_sorting_direction = 'DESC';
				break;
				case 0:
				default:
					$items_sorting_direction = 'ASC';
			}

			// Определяем поле сортировки информационных элементов
			switch ($oInformationsystem->items_sorting_field)
			{
				case 1:
					$oInformationsystem_Items
						->queryBuilder()
						->orderBy('informationsystem_items.name', $items_sorting_direction)
						->orderBy('informationsystem_items.sorting', $items_sorting_direction);
					break;
				case 2:
					$oInformationsystem_Items
						->queryBuilder()
						->orderBy('informationsystem_items.sorting', $items_sorting_direction)
						->orderBy('informationsystem_items.name', $items_sorting_direction);
					break;
				case 0:
				default:
					$oInformationsystem_Items
						->queryBuilder()
						->orderBy('informationsystem_items.datetime', $items_sorting_direction)
						->orderBy('informationsystem_items.sorting', $items_sorting_direction);
			}

			Core_Event::notify(get_class($this) . '.onBeforeFindInformationsystemItems', $this, array($oInformationsystem_Items, $parentObject, $oInformationsystem));

			$aInformationsystem_Items = $oInformationsystem_Items->findAll(FALSE);
			foreach ($aInformationsystem_Items as $oInformationsystem_Item)
			{
				$this->_aInformationsystem_Items[$oInformationsystem_Item->informationsystem_group_id][] = $oInformationsystem_Item;
			}
		}

		return $this;
	}

	/**
	 * Add all groups of information system to XML
	 * @param object $parentObject
	 * @param Informationsystem_Model $oInformationsystem
	 * @return self
	 */
	protected function _addInformationsystemGroups($parentObject, $oInformationsystem, $level = 0)
	{
		$this->fillInformationsystem($oInformationsystem, $parentObject);

		$this->_addInformationsystemGroupsByParentId(0, $parentObject);

		return $this;
	}

	/**
	 * Add groups of information system to XML
	 * @param int $parent_id ID of parent group
	 * @param object $parentObject
	 * @return self
	 * @hostcms-event Structure_Controller_Show.onBeforeAddInformationsystemGroups
	 * @hostcms-event Structure_Controller_Show.onAfterAddInformationsystemGroups
	 * @hostcms-event Structure_Controller_Show.onAfterAddInformationsystemGroup
	 * @hostcms-event Structure_Controller_Show.onBeforeAddInformationsystemItems
	 * @hostcms-event Structure_Controller_Show.onAfterAddInformationsystemItems
	 * @hostcms-event Structure_Controller_Show.onAfterAddInformationsystemItem
	 */
	protected function _addInformationsystemGroupsByParentId($parent_id, $parentObject, $level = 0)
	{
		if (isset($this->_aInformationsystem_Groups[$parent_id]))
		{
			Core_Event::notify(get_class($this) . '.onBeforeAddInformationsystemGroups', $this, array($parent_id));

			foreach ($this->_aInformationsystem_Groups[$parent_id] as $oInformationsystem_Group)
			{
				// Shortcut
				if ($oInformationsystem_Group->shortcut_id
					&& $oInformationsystem_Group->shortcut_id != $oInformationsystem_Group->parent_id)
				{
					$oShortcut_Group = $oInformationsystem_Group;
					$oOriginal_Informationsystem_Group = $oInformationsystem_Group->Shortcut;

					$oInformationsystem_Group = clone $oOriginal_Informationsystem_Group;

					$oInformationsystem_Group
						->id($oOriginal_Informationsystem_Group->id)
						->parent_id($oShortcut_Group->parent_id)
						->shortcut_id($oShortcut_Group->id)
						/*->addForbiddenTag('parent_id')
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
						)*/;
				}
				else
				{
					$oOriginal_Informationsystem_Group = $oInformationsystem_Group;
				}

				$this->showInformationsystemGroupProperties
					&& $oInformationsystem_Group->showXmlProperties($this->showInformationsystemGroupProperties);

				$oInformationsystem_Group
					->clearEntities()
					->addForbiddenTag('url')
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('link')
							->value(
								$oInformationsystem_Group->Informationsystem->Structure->getPath() . $oOriginal_Informationsystem_Group->getPath()
							)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('show')
							->value($oInformationsystem_Group->active)
					);

				$this->applyForbiddenTags($oInformationsystem_Group);

				$parentObject->addEntity($oInformationsystem_Group);

				Core_Event::notify(get_class($this) . '.onAfterAddInformationsystemGroup', $this, array($oInformationsystem_Group, $parentObject));

				if (is_null($this->level) || $level < $this->level)
				{
					$this->_addInformationsystemGroupsByParentId($oInformationsystem_Group->id, $oInformationsystem_Group, $level + 1);
				}
			}

			Core_Event::notify(get_class($this) . '.onAfterAddInformationsystemGroups', $this, array($parent_id));
		}

		if ($this->showInformationsystemItems && isset($this->_aInformationsystem_Items[$parent_id]))
		{
			Core_Event::notify(get_class($this) . '.onBeforeAddInformationsystemItems', $this, array($parent_id));

			foreach ($this->_aInformationsystem_Items[$parent_id] as $oInformationsystem_Item)
			{
				// Shortcut
				$oInformationsystem_Item->shortcut_id
					&& $oInformationsystem_Item = $oInformationsystem_Item->Informationsystem_Item;

				$oInformationsystem_Item
					->clearEntities()
					->clearEntitiesAfterGetXml(FALSE)
					->addForbiddenTag('url')
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('link')
							->value(
								$oInformationsystem_Item->Informationsystem->Structure->getPath() . $oInformationsystem_Item->getPath()
							)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('show')
							->value($oInformationsystem_Item->active)
					);

				$this->showInformationsystemItemProperties && $oInformationsystem_Item->showXmlProperties($this->showInformationsystemItemProperties);

				$this->applyForbiddenTags($oInformationsystem_Item);

				$parentObject->addEntity($oInformationsystem_Item);

				Core_Event::notify(get_class($this) . '.onAfterAddInformationsystemItem', $this, array($oInformationsystem_Item, $parentObject));
			}

			Core_Event::notify(get_class($this) . '.onAfterAddInformationsystemItems', $this, array($parent_id));
		}

		return $this;
	}

	/**
	 * List of shop groups
	 * @var array
	 */
	protected $_aShop_Groups = array();

	/**
	 * List of shop items
	 * @var array
	 */
	protected $_aShop_Items = array();

	/**
	 * Get _aShop_Groups set
	 * @return array
	 */
	public function getShopGroups()
	{
		return $this->_aShop_Groups;
	}

	/**
	 * Get _aShop_Items set
	 * @return array
	 */
	public function getShopItems()
	{
		return $this->_aShop_Items;
	}

	/**
	 * Fill Shop
	 * @param Shop_Model $oShop shop
	 * @param object $parentObject
	 * @return self
	 * @hostcms-event Structure_Controller_Show.onBeforeFindShopGroups
	 * @hostcms-event Structure_Controller_Show.onBeforeFindShopItems
	 */
	public function fillShop($oShop, $parentObject = NULL)
	{
		$dateTime = Core_Date::timestamp2sql(time());

		$oShop_Groups = $oShop->Shop_Groups;
		$oShop_Groups->queryBuilder()
			->where('shop_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
			->where('shop_groups.active', '=', 1)
			->clearOrderBy();

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
				$oShop_Groups
					->queryBuilder()
					->orderBy('shop_groups.name', $groups_sorting_direction);
				break;
			case 1:
			default:
				$oShop_Groups
					->queryBuilder()
					->orderBy('shop_groups.sorting', $groups_sorting_direction);
				break;
		}

		$this->_aShop_Groups = array();

		Core_Event::notify(get_class($this) . '.onBeforeFindShopGroups', $this, array($oShop_Groups, $parentObject, $oShop));

		// findAll(FALSE) isn't necessary because Shop_Group needs for getPath()
		$aShop_Groups = $oShop_Groups->findAll();
		foreach ($aShop_Groups as $oShop_Group)
		{
			$this->_aShop_Groups[$oShop_Group->parent_id][] = $oShop_Group;
		}

		// Shop's items
		$this->_aShop_Items = array();

		if ($this->showShopItems)
		{
			$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
			$oCore_QueryBuilder_Select
				->from('shop_items')
				->where('shop_items.shop_id', '=', $oShop->id)
				->where('shop_items.deleted', '=', 0);

			$aRow = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

			$maxId = $aRow['max_id'];

			$iFrom = 0;

			do {
				$oShop_Items = $oShop->Shop_Items;
				$oShop_Items->queryBuilder()
					->select('shop_items.*')
					->where('shop_items.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->onStep))
					->open()
						->where('shop_items.start_datetime', '<', $dateTime)
						->setOr()
						->where('shop_items.start_datetime', '=', '0000-00-00 00:00:00')
					->close()
					->setAnd()
					->open()
						->where('shop_items.end_datetime', '>', $dateTime)
						->setOr()
						->where('shop_items.end_datetime', '=', '0000-00-00 00:00:00')
					->close()
					->where('shop_items.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
					->where('shop_items.active', '=', 1)
					->where('shop_items.modification_id', '=', 0)
					->clearOrderBy();

				switch ($oShop->items_sorting_direction)
				{
					case 1:
						$items_sorting_direction = 'DESC';
					break;
					case 0:
					default:
						$items_sorting_direction = 'ASC';
				}

				// Определяем поле сортировки информационных элементов
				switch ($oShop->items_sorting_field)
				{
					case 1:
						$oShop_Items
							->queryBuilder()
							->orderBy('shop_items.name', $items_sorting_direction)
							->orderBy('shop_items.sorting', $items_sorting_direction);
						break;
					case 2:
						$oShop_Items
							->queryBuilder()
							->orderBy('shop_items.sorting', $items_sorting_direction)
							->orderBy('shop_items.name', $items_sorting_direction);
						break;
					case 0:
					default:
						$oShop_Items
							->queryBuilder()
							->orderBy('shop_items.datetime', $items_sorting_direction)
							->orderBy('shop_items.sorting', $items_sorting_direction);
				}

				Core_Event::notify(get_class($this) . '.onBeforeFindShopItems', $this, array($oShop_Items, $parentObject, $oShop));

				$aShop_Items = $oShop_Items->findAll(FALSE);
				foreach ($aShop_Items as $oShop_Item)
				{
					$this->_aShop_Items[$oShop_Item->shop_group_id][] = $oShop_Item;
				}

				$iFrom += $this->onStep;
			}
			while ($iFrom < $maxId);
		}

		return $this;
	}

	/**
	 * Add all groups of shop to XML
	 * @param object $parentObject
	 * @param Shop_Model $oShop shop
	 * @return self
	 */
	protected function _addShopGroups($parentObject, $oShop, $level = 0)
	{
		$this->fillShop($oShop, $parentObject);

		$this->_addShopGroupsByParentId(0, $parentObject);

		return $this;
	}

	/**
	 * Add shop groups to the object by parent group ID
	 * @param int $parent_id parent group ID
	 * @param object $parentObject
	 * @return self
	 * @hostcms-event Structure_Controller_Show.onBeforeAddShopGroups
	 * @hostcms-event Structure_Controller_Show.onAfterAddShopGroups
	 * @hostcms-event Structure_Controller_Show.onAfterAddShopGroup
	 * @hostcms-event Structure_Controller_Show.onBeforeAddShopItems
	 * @hostcms-event Structure_Controller_Show.onAfterAddShopItems
	 * @hostcms-event Structure_Controller_Show.onAfterAddShopItem
	 */
	protected function _addShopGroupsByParentId($parent_id, $parentObject, $level = 0)
	{
		if (isset($this->_aShop_Groups[$parent_id]))
		{
			Core_Event::notify(get_class($this) . '.onBeforeAddShopGroups', $this, array($parent_id));

			foreach ($this->_aShop_Groups[$parent_id] as $oShop_Group)
			{
				// Shortcut
				if ($oShop_Group->shortcut_id
					&& $oShop_Group->shortcut_id != $oShop_Group->parent_id)
				{
					$oShortcut_Group = $oShop_Group;
					$oOriginal_Shop_Group = $oShop_Group->Shortcut;

					$oShop_Group = clone $oOriginal_Shop_Group;

					$oShop_Group
						->id($oOriginal_Shop_Group->id)
						->parent_id($oShortcut_Group->parent_id)
						->shortcut_id($oShortcut_Group->id)
						/*->addForbiddenTag('parent_id')
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
						)*/;
				}
				else
				{
					$oOriginal_Shop_Group = $oShop_Group;
				}

				$this->showShopGroupProperties
					&& $oShop_Group->showXmlProperties($this->showShopGroupProperties);

				$oShop_Group
					// ->clearEntities()
					->addForbiddenTag('url')
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('link')
							->value(
								$oShop_Group->Shop->Structure->getPath() . $oOriginal_Shop_Group->getPath()
							)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('show')
							->value($oShop_Group->active)
					);

				$this->applyForbiddenTags($oShop_Group);

				$parentObject->addEntity($oShop_Group);

				Core_Event::notify(get_class($this) . '.onAfterAddShopGroup', $this, array($oShop_Group, $parentObject));

				if (is_null($this->level) || $level < $this->level)
				{
					$this->_addShopGroupsByParentId($oShop_Group->id, $oShop_Group, $level + 1);
				}
			}

			Core_Event::notify(get_class($this) . '.onAfterAddShopGroups', $this, array($parent_id));
		}

		if ($this->showShopItems && isset($this->_aShop_Items[$parent_id]))
		{
			Core_Event::notify(get_class($this) . '.onBeforeAddShopItems', $this, array($parent_id));

			foreach ($this->_aShop_Items[$parent_id] as $oShop_Item)
			{
				// Shortcut
				$oShop_Item->shortcut_id
					&& $oShop_Item = $oShop_Item->Shop_Item;

				$oShop_Item
					->clearEntities()
					->clearEntitiesAfterGetXml(FALSE)
					->addForbiddenTag('url')
					->showXmlModifications(TRUE)
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('link')
							->value(
								$oShop_Item->Shop->Structure->getPath() . $oShop_Item->getPath()
							)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('show')
							->value($oShop_Item->active)
					);

				$this->showShopItemProperties && $oShop_Item->showXmlProperties($this->showShopItemProperties);

				$this->showShopItemAssociated && $oShop_Item->showXmlAssociatedItems($this->showShopItemAssociated);

				$this->applyForbiddenTags($oShop_Item);

				$parentObject->addEntity($oShop_Item);

				Core_Event::notify(get_class($this) . '.onAfterAddShopItem', $this, array($oShop_Item, $parentObject));
			}

			Core_Event::notify(get_class($this) . '.onAfterAddShopItems', $this, array($parent_id));
		}

		return $this;
	}

	/**
	 * Apply forbidden tags
	 * @param Core_Entity $object
	 * @return self
	 */
	public function applyForbiddenTags($object)
	{
		if (!is_null($this->forbiddenTags))
		{
			$object->addForbiddenTags($this->forbiddenTags);
		}

		return $this;
	}

	/**
	 * Show frontend panel
	 * @return $this
	 */
	protected function _showPanel()
	{
		// Panel
		$oXslPanel = Core_Html_Entity::factory('Div')
			->class('hostcmsPanel')
			->style('display: none');

		$oXslSubPanel = Core_Html_Entity::factory('Div')
			->class('hostcmsSubPanel hostcmsXsl')
			->add(
				Core_Html_Entity::factory('Img')
					->width(3)->height(16)
					->src('/hostcmsfiles/images/drag_bg.gif')
			);

		$sPath = '/admin/structure/index.php';
		$sAdditional = "hostcms[action]=edit&hostcms[checked][0][0]=1";
		$sTitle = Core::_('Structure.add_title');

		$oXslSubPanel->add(
			Core_Html_Entity::factory('A')
				->href("{$sPath}?{$sAdditional}")
				->onclick("hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditional}', dialogClass: 'hostcms6'}); return false")
				->add(
					Core_Html_Entity::factory('Img')
						->width(16)->height(16)
						->src('/admin/images/structure_add.gif')
						->alt($sTitle)
						->title($sTitle)
				)
		);

		$oXslPanel
			->add($oXslSubPanel)
			->execute();

		return $this;
	}
}