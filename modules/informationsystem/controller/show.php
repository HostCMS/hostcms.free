<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ информационной системы.
 *
 * Доступные методы:
 *
 * - group($id) идентификатор информационной группы, если FALSE, то вывод информационных элементов осуществляется из всех групп
 * - groupsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств групп, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - groupsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств групп информационных элементов, по умолчанию TRUE
 * - propertiesForGroups(array()) устанавливает дополнительное ограничение на вывод значений дополнительных свойств групп для массива идентификаторов групп.
 * - groupsMode('tree') режим показа групп, может принимать следующие значения:
	none - не показывать группы,
	tree - показывать дерево групп и все группы на текущем уровне (по умолчанию),
	all - показывать все группы.
 * - groupsForbiddenTags(array('description')) массив тегов групп, запрещенных к передаче в генерируемый XML
 * - item(123) идентификатор показываемого информационного элемента
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств информационных элементов, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств информационных элементов, по умолчанию TRUE
 * - commentsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств комментариев, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - commentsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств комментариев, по умолчанию TRUE.
 * - itemsForbiddenTags(array('description')) массив тегов информационных элементов, запрещенных к передаче в генерируемый XML
 * - addFilter() добавить условие отобра информационных элементов, может задавать условие отобра по значению свойства ->addFilter('property', 17, '=', 1)
 * - comments(TRUE|FALSE) показывать комментарии для выбранных информационных элементов, по умолчанию FALSE
 * - votes(TRUE|FALSE) показывать рейтинг элемента, по умолчанию TRUE
 * - tags(TRUE|FALSE) выводить метки
 * - calculateCounts(TRUE|FALSE) вычислять общее количество информационных элементов и групп в корневой группе, по умолчанию FALSE
 * - siteuser(TRUE|FALSE) показывать данные о пользователе сайта, связанного с выбранным информационным элементом, по умолчанию TRUE
 * - siteuserProperties(TRUE|FALSE) выводить значения дополнительных свойств пользователей сайта, по умолчанию FALSE
 * - orderBy('informationsystem_items.name', 'ASC') задает направление сортировки информационных элементов
 * - offset($offset) смещение, с которого выводить информационные элементы. По умолчанию 0
 * - limit($limit) количество выводимых элементов
 * - page(2) текущая страница, по умолчанию 0, счет ведется с 0
 * - part($int) номер отображаемой части информационного элемента
 * - pattern($pattern) шаблон разбора данных в URI, см. __construct()
 * - tag($path) путь тега, с использованием которого ведется отбор информационных элементов
 * - cache(TRUE|FALSE) использовать кэширование, по умолчанию TRUE
 * - itemsActivity('active'|'inactive'|'all') отображать элементы: active - только активные, inactive - только неактивные, all - все, по умолчанию - active
 * - groupsActivity('active'|'inactive'|'all') отображать группы: active - только активные, inactive - только неактивные, all - все, по умолчанию - active
 * - commentsActivity('active'|'inactive'|'all') отображать комментарии: active - только активные, inactive - только неактивные, all - все, по умолчанию - active
 * - calculateTotal(TRUE|FALSE) вычислять общее количество найденных, по умолчанию TRUE
 * - showPanel(TRUE|FALSE) показывать панель быстрого редактирования, по умолчанию TRUE
 *
 * Доступные свойства:
 *
 * - total общее количество доступных для отображения записей
 * - patternParams массив данных, извелеченных из URI при применении pattern
 *
 * <code>
 * $Informationsystem_Controller_Show = new Informationsystem_Controller_Show(
 * 	Core_Entity::factory('Informationsystem', 1)
 * );
 *
 * $Informationsystem_Controller_Show
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('СписокНовостейНаГлавной')
 * 	)
 * 	->limit(5)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Controller_Show extends Core_Controller
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
		'commentsProperties',
		'commentsPropertiesList',
		'itemsForbiddenTags',
		'comments',
		'votes',
		'tags',
		'calculateCounts',
		'siteuser',
		'siteuserProperties',
		'offset',
		'limit',
		'page',
		'part',
		'total',
		'pattern',
		'patternExpressions',
		'patternParams',
		'tag',
		'cache',
		'itemsActivity',
		'groupsActivity',
		'commentsActivity',
		'calculateTotal',
		'showPanel',
	);

	/**
	 * List of groups of information systems
	 * @var array
	 */
	protected $_aInformationsystem_Groups = array();

	/**
	 * Get _aInformationsystem_Groups set
	 * @return array
	 */
	public function getInformationsystemGroups()
	{
		return $this->_aInformationsystem_Groups;
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
	 * Information system's items object
	 * @var Informationsystem_Item_Model
	 */
	protected $_Informationsystem_Items = NULL;

	/**
	 * Information system's group object
	 * @var Informationsystem_Group_Model
	 */
	protected $_Informationsystem_Groups = NULL;

	/**
	 * Array of siteuser's groups allowed for current siteuser
	 * @var array
	 */
	protected $_aSiteuserGroups = array();

	/**
	 * Cache name
	 * @var string
	 */
	protected $_cacheName = 'informationsystem_show';

	/**
	 * Constructor.
	 * @param Informationsystem_Model $oInformationsystem information system
	 */
	public function __construct(Informationsystem_Model $oInformationsystem)
	{
		parent::__construct($oInformationsystem->clearEntities());

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

		$this->_setInformationsystemItems()->_setInformationsystemGroups();

		$this->limit = 10;
		$this->group = $this->offset = $this->page = 0;
		$this->item = NULL;
		$this->groupsProperties = $this->itemsProperties = $this->commentsProperties = $this->propertiesForGroups = $this->comments
			= $this->tags = $this->calculateCounts = $this->siteuserProperties = FALSE;

		$this->siteuser = $this->cache = $this->itemsPropertiesList = $this->commentsPropertiesList = $this->groupsPropertiesList
			= $this->votes = $this->showPanel = $this->calculateTotal = TRUE;

		$this->groupsMode = 'tree';
		$this->part = 1;

		$this->itemsActivity = $this->groupsActivity = $this->commentsActivity = 'active'; // inactive, all

		$this->pattern = rawurldecode(Core_Str::rtrimUri($this->getEntity()->Structure->getPath())) . '({path}/)(part-{part}/)(page-{page}/)(tag/{tag}/)';

		$this->patternExpressions = array(
			'part' => '\d+',
			'page' => '\d+',
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
	 * Prepare items for showing
	 * @return self
	 */
	protected function _setInformationsystemItems()
	{
		$oInformationsystem = $this->getEntity();

		$this->_Informationsystem_Items = $oInformationsystem->Informationsystem_Items;

		switch ($oInformationsystem->items_sorting_direction)
		{
			case 1:
				$items_sorting_direction = 'DESC';
			break;
			case 0:
			default:
				$items_sorting_direction = 'ASC';
		}

		$this->_Informationsystem_Items
			->queryBuilder()
			->clearOrderBy();

		// Определяем поле сортировки информационных элементов
		switch ($oInformationsystem->items_sorting_field)
		{
			case 1:
				$this->_Informationsystem_Items
					->queryBuilder()
					->orderBy('informationsystem_items.name', $items_sorting_direction);
				break;
			case 2:
				$this->_Informationsystem_Items
					->queryBuilder()
					->orderBy('informationsystem_items.sorting', $items_sorting_direction)
					->orderBy('informationsystem_items.name', $items_sorting_direction);
				break;
			case 0:
			default:
				$this->_Informationsystem_Items
					->queryBuilder()
					->orderBy('informationsystem_items.datetime', $items_sorting_direction);
		}

		$this->_Informationsystem_Items
			->queryBuilder()
			->select('informationsystem_items.*')
			//->where('informationsystem_items.active', '=', 1)
			;

		$this->_applyItemConditions($this->_Informationsystem_Items);

		return $this;
	}

	/**
	 * Apply item's conditions
	 *
	 * @param Informationsystem_Item_Model $oInformationsystem_Items
	 * @return self
	 */
	protected function _applyItemConditions(Informationsystem_Item_Model $oInformationsystem_Items)
	{
		$dateTime = Core_Date::timestamp2sql(time());
		$oInformationsystem_Items
			->queryBuilder()
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
			->where('informationsystem_items.siteuser_group_id', 'IN', $this->_aSiteuserGroups);

		return $this;
	}

	/**
	 * Prepare groups for showing
	 * @return self
	 */
	protected function _setInformationsystemGroups()
	{
		$oInformationsystem = $this->getEntity();

		$this->_Informationsystem_Groups = $oInformationsystem->Informationsystem_Groups;
		$this->_Informationsystem_Groups
			->queryBuilder()
			->select('informationsystem_groups.*')
			->where('informationsystem_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
			//->where('informationsystem_groups.active', '=', 1)
			;

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
				$this->_Informationsystem_Groups
					->queryBuilder()
					->orderBy('informationsystem_groups.name', $groups_sorting_direction);
				break;
			case 1:
			default:
				$this->_Informationsystem_Groups
					->queryBuilder()
					->orderBy('informationsystem_groups.sorting', $groups_sorting_direction);
				break;
		}

		return $this;
	}

	/**
	 * Get/set _Informationsystem_Items
	 * @param mixed $object
	 * @return self or _Informationsystem_Items
	 */
	public function informationsystemItems($object = NULL)
	{
		if (is_null($object))
		{
			return $this->_Informationsystem_Items;
		}
		else
		{
			$this->_Informationsystem_Items = $object;
			return $this;
		}
	}

	/**
	 * Get/set _Informationsystem_Groups
	 * @param mixed $object
	 * @return self or _Informationsystem_Groups
	 */
	public function informationsystemGroups($object = NULL)
	{
		if (is_null($object))
		{
			return $this->_Informationsystem_Groups;
		}
		else
		{
			$this->_Informationsystem_Groups = $object;
			return $this;
		}
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
		Core_Entity::factory('Informationsystem_Item')->getTableColumns();

		// Load user BEFORE FOUND_ROWS()
		Core_Auth::getCurrentUser();

		$this->calculateTotal && $this->_Informationsystem_Items
			->queryBuilder()
			->sqlCalcFoundRows();

		$this->_Informationsystem_Items
			->queryBuilder()
			->offset(intval($this->offset))
			->limit(intval($this->limit));

		return $this;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Informationsystem_Controller_Show.onBeforeRedeclaredShow
	 * @hostcms-event Informationsystem_Controller_Show.onBeforeAddGroupsPropertiesList
	 * @hostcms-event Informationsystem_Controller_Show.onBeforeAddItemsPropertiesList
	 * @hostcms-event Informationsystem_Controller_Show.onBeforeAddShortcut
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$this->showPanel && Core::checkPanel() && $this->_showPanel();

		$bTpl = $this->_mode == 'tpl';

		$this->item && $this->_incShowed();

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

			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$inCache = $oCore_Cache->get($cacheKey = strval($this), $this->_cacheName);

			if (is_array($inCache))
			{
				$this->_shownIDs = $inCache['shown'];
				echo $inCache['content'];
				return $this;
			}

			$this->_cacheTags[] = 'informationsystem_group_' . intval($this->group);
		}

		$oInformationsystem = $this->getEntity();

		$oInformationsystem->showXmlCounts($this->calculateCounts);

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
				->name('part')
				->value(intval($this->part) - 1)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		// Независимо от limit, т.к. может использоваться отдельно для фильтра
		if (!$this->item)
		{
			$this->applyFilter();

			/*$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('filter_path')
					->value($this->_filterPath)
			);*/
		}

		// До вывода свойств групп
		if ($this->limit > 0 || $this->item)
		{
			$this->_itemCondition();

			// Group's conditions for information system item
			$this->group !== FALSE && $this->_groupCondition();

			!$this->item && $this->_setLimits();

			$aInformationsystem_Items = $this->_Informationsystem_Items->findAll();

			if (!$this->item)
			{
				if ($this->page && !count($aInformationsystem_Items))
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
			$oInformationsystem_Group_Property_List = Core_Entity::factory('Informationsystem_Group_Property_List', $oInformationsystem->id);

			$oProperties = $oInformationsystem_Group_Property_List->Properties;
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

			$aProperty_Dirs = $oInformationsystem_Group_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aGroup_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			if (!$bTpl)
			{
				$Informationsystem_Group_Properties = Core::factory('Core_Xml_Entity')
					->name('informationsystem_group_properties');

				$this->addEntity($Informationsystem_Group_Properties);

				Core_Event::notify(get_class($this) . '.onBeforeAddGroupsPropertiesList', $this, array($Informationsystem_Group_Properties));

				$this->_addGroupsPropertiesList(0, $Informationsystem_Group_Properties);
			}
		}

		is_array($this->groupsProperties) && $this->groupsProperties = array_combine($this->groupsProperties, $this->groupsProperties);

		// Устанавливаем активность групп
		$this->_setGroupsActivity();

		// Группы информационной системы
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

		// Показывать дополнительные свойства информационного элемента
		if ($this->itemsProperties && $this->itemsPropertiesList)
		{
			$oInformationsystem_Item_Property_List = Core_Entity::factory('Informationsystem_Item_Property_List', $oInformationsystem->id);

			$oProperties = $oInformationsystem_Item_Property_List->Properties;
			if (is_array($this->itemsPropertiesList) && count($this->itemsPropertiesList))
			{
				$oProperties->queryBuilder()
					->where('properties.id', 'IN', $this->itemsPropertiesList);
			}
			$aProperties = $oProperties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$this->_aItem_Properties[$oProperty->property_dir_id][] = $oProperty->clearEntities();
			}

			$aProperty_Dirs = $oInformationsystem_Item_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aItem_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir->clearEntities();
			}

			if (!$bTpl)
			{
				$Informationsystem_Item_Properties = Core::factory('Core_Xml_Entity')
					->name('informationsystem_item_properties');

				$this->addEntity($Informationsystem_Item_Properties);

				Core_Event::notify(get_class($this) . '.onBeforeAddItemsPropertiesList', $this, array($Informationsystem_Item_Properties));

				$this->_addItemsPropertiesList(0, $Informationsystem_Item_Properties);
			}
		}

		// Показывать дополнительные свойства комментариев
		if ($this->commentsProperties && $this->commentsPropertiesList)
		{
			$oInformationsystem_Comment_Property_List = Core_Entity::factory('Informationsystem_Comment_Property_List', $oInformationsystem->id);

			$oProperties = $oInformationsystem_Comment_Property_List->Properties;
			if (is_array($this->commentsPropertiesList) && count($this->commentsPropertiesList))
			{
				$oProperties->queryBuilder()
					->where('properties.id', 'IN', $this->commentsPropertiesList);
			}
			$aProperties = $oProperties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$this->_aComment_Properties[$oProperty->property_dir_id][] = $oProperty->clearEntities();
			}

			$aProperty_Dirs = $oInformationsystem_Comment_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aComment_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir->clearEntities();
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

		$this->_shownIDs = array();

		if ($bTpl)
		{
			$this->assign('controller', $this);
			$this->assign('aInformationsystem_Items', array());
		}

		if ($this->limit > 0)
		{
			//Ярлык может ссылаться на элемент с истекшим или не наступившим сроком публикации
			$iCurrentTimestamp = time();

			foreach ($aInformationsystem_Items as $oInformationsystem_Item)
			{
				$this->_shownIDs[] = $oInformationsystem_Item->id;

				// Tagged cache
				$bCache && $this->_cacheTags[] = 'informationsystem_item_' . $oInformationsystem_Item->id;

				// Shortcut
				$iShortcut = $oInformationsystem_Item->shortcut_id;

				if ($iShortcut)
				{
					$oShortcut_Item = $oInformationsystem_Item;
					$oInformationsystem_Item = $oInformationsystem_Item->Informationsystem_Item;
				}

				$oInformationsystem_Item->clearEntities();

				if (!$bTpl)
				{
					// Ярлык может ссылаться на отключенный элемент
					$desiredActivity = strtolower($this->itemsActivity) == 'active'
						? 1
						: (strtolower($this->itemsActivity) == 'all' ? $oInformationsystem_Item->active : 0);

					// ID оригинального ярлыка
					if ($iShortcut)
					{
						$oOriginal_Informationsystem_Item = $oInformationsystem_Item;

						$oInformationsystem_Item = clone $oInformationsystem_Item;
						$oInformationsystem_Item
							->id($oOriginal_Informationsystem_Item->id)
							->addForbiddenTag('shortcut_id')
							->addForbiddenTag('informationsystem_group_id')
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('shortcut_id')
									->value($oShortcut_Item->id)
							)
							->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('informationsystem_group_id')
									->value($oShortcut_Item->informationsystem_group_id)
							);

						Core_Event::notify(get_class($this) . '.onBeforeAddShortcut', $this, array($oInformationsystem_Item, $oOriginal_Informationsystem_Item));
					}

					if ($oInformationsystem_Item->id // Can be shortcut on markDeleted item
						&& $oInformationsystem_Item->active == $desiredActivity
						&& (!$iShortcut
							|| (Core_Date::sql2timestamp($oInformationsystem_Item->end_datetime) >= $iCurrentTimestamp
								|| $oInformationsystem_Item->end_datetime == '0000-00-00 00:00:00')
							&& (Core_Date::sql2timestamp($oInformationsystem_Item->start_datetime) <= $iCurrentTimestamp
								|| $oInformationsystem_Item->start_datetime == '0000-00-00 00:00:00')
						)
					)
					{
						$this->applyItemsForbiddenTags($oInformationsystem_Item);

						// Comments
						$oInformationsystem_Item
							->showXmlComments($this->comments)
							->commentsActivity($this->commentsActivity);

						// Properties for informationsystem's item entity
						$oInformationsystem_Item->showXmlProperties($this->itemsProperties);
						$oInformationsystem_Item->showXmlCommentProperties($this->commentsProperties);

						// Tags
						$oInformationsystem_Item->showXmlTags($this->tags);

						// votes
						$oInformationsystem_Item->showXmlVotes($this->votes);

						// Siteuser
						$oInformationsystem_Item
							->showXmlSiteuser($this->siteuser)
							->showXmlSiteuserProperties($this->siteuserProperties);

						// <!-- pagebreak -->
						if ($this->part || $this->item)
						{
							$oInformationsystem_Item->showXmlPart($this->part);
						}

						$this->addEntity($oInformationsystem_Item);
					}
				}
				else
				{
					$this->append('aInformationsystem_Items', $oInformationsystem_Item);
				}
			}

			unset($aInformationsystem_Items);
		}

		echo $content = $this->get();

		$bCache && $oCore_Cache->set(
			$cacheKey,
			array('content' => $content, 'shown' => $this->_shownIDs),
			$this->_cacheName,
			$this->_cacheTags
		);

		// Clear
		$this->_aInformationsystem_Groups = $this->_aItem_Property_Dirs = $this->_aItem_Properties
			= $this->_aGroup_Properties = $this->_aGroup_Property_Dirs = $this->_cacheTags = array();

		return $this;
	}

	/**
	 * Inc Informationsystem_Item->showed
	 * @return self
	 */
	protected function _incShowed()
	{
		Core_QueryBuilder::update('informationsystem_items')
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
		// Информационные элементы
		if ($this->item)
		{
			$this->_Informationsystem_Items
				->queryBuilder()
				->where('informationsystem_items.id', '=', intval($this->item));
		}
		elseif (!is_null($this->tag) && Core::moduleIsActive('tag'))
		{
			$oTag = Core_Entity::factory('Tag')->getByPath($this->tag);

			if ($oTag)
			{
				$this->addEntity($oTag);

				$this->_Informationsystem_Items
					->queryBuilder()
					->leftJoin('tag_informationsystem_items', 'informationsystem_items.id', '=', 'tag_informationsystem_items.informationsystem_item_id')
					->where('tag_informationsystem_items.tag_id', '=', $oTag->id);

				// В корне при фильтрации по меткам вывод идет из всех групп ИС
				$this->group == 0 && $this->group = FALSE;
			}
		}

		$this->_setItemsActivity();

		return $this;
	}

	/**
	 * Set item's condition by informationsystem_group_id
	 * @return self
	 */
	protected function _groupCondition()
	{
		$this->_Informationsystem_Items
			->queryBuilder()
			->where('informationsystem_items.informationsystem_group_id', '=', intval($this->group));

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
	 * @return informationsystem_Controller_Show
	 * @hostcms-event Informationsystem_Controller_Show.onBeforeParseUrl
	 * @hostcms-event Informationsystem_Controller_Show.onAfterParseUrl
	 */
	public function parseUrl()
	{
		Core_Event::notify(get_class($this) . '.onBeforeParseUrl', $this);

		$oInformationsystem = $this->getEntity();

		// Group: set informationsystem's SEO templates
		$oInformationsystem->seo_group_title_template != ''
			&& $this->_seoGroupTitle = $oInformationsystem->seo_group_title_template;
		$oInformationsystem->seo_group_description_template != ''
			&& $this->_seoGroupDescription = $oInformationsystem->seo_group_description_template;
		$oInformationsystem->seo_group_keywords_template != ''
			&& $this->_seoGroupKeywords = $oInformationsystem->seo_group_keywords_template;

		// Item: set informationsystem's SEO templates
		$oInformationsystem->seo_item_title_template != ''
			&& $this->_seoItemTitle = $oInformationsystem->seo_item_title_template;
		$oInformationsystem->seo_item_description_template != ''
			&& $this->_seoItemDescription = $oInformationsystem->seo_item_description_template;
		$oInformationsystem->seo_item_keywords_template != ''
			&& $this->_seoItemKeywords = $oInformationsystem->seo_item_keywords_template;

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

		isset($matches['part']) && $this->part($matches['part']);

		if (isset($matches['tag']) && $matches['tag'] != '' && Core::moduleIsActive('tag'))
		{
			$this->tag($matches['tag']);

			$oTag = Core_Entity::factory('Tag')->getByPath($this->tag);
			if (is_null($oTag))
			{
				return $this->error404();
			}
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
				// Attempt to receive Informationsystem_Group
				$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;

				$this->groupsActivity = strtolower($this->groupsActivity);
				if ($this->groupsActivity != 'all')
				{
					$oInformationsystem_Groups
						->queryBuilder()
						->where('shortcut_id', '=', 0)
						->where('active', '=', $this->groupsActivity == 'inactive' ? 0 : 1);
				}

				$oInformationsystem_Group = $oInformationsystem_Groups->getByParentIdAndPath($this->group, $sPath);

				if (!is_null($oInformationsystem_Group))
				{
					if (in_array($oInformationsystem_Group->getSiteuserGroupId(), $this->_aSiteuserGroups))
					{
						$this->group = $oInformationsystem_Group->id;

						// Group: set informationsystem's SEO templates
						$oInformationsystem_Group->seo_group_title_template != ''
							&& $this->_seoGroupTitle = $oInformationsystem_Group->seo_group_title_template;
						$oInformationsystem_Group->seo_group_description_template != ''
							&& $this->_seoGroupDescription = $oInformationsystem_Group->seo_group_description_template;
						$oInformationsystem_Group->seo_group_keywords_template != ''
							&& $this->_seoGroupKeywords = $oInformationsystem_Group->seo_group_keywords_template;

						// Item: set informationsystem's SEO templates
						$oInformationsystem_Group->seo_item_title_template != ''
							&& $this->_seoItemTitle = $oInformationsystem_Group->seo_item_title_template;
						$oInformationsystem_Group->seo_item_description_template != ''
							&& $this->_seoItemDescription = $oInformationsystem_Group->seo_item_description_template;
						$oInformationsystem_Group->seo_item_keywords_template != ''
							&& $this->_seoItemKeywords = $oInformationsystem_Group->seo_item_keywords_template;
					}
					else
					{
						return $this->error403();
					}
				}
				else
				{
					// Attempt to receive Informationsystem_Item
					$oInformationsystem_Items = $oInformationsystem->Informationsystem_Items;

					$this->itemsActivity = strtolower($this->itemsActivity);
					if ($this->itemsActivity != 'all')
					{
						$oInformationsystem_Items
							->queryBuilder()
							->where('informationsystem_items.active', '=', $this->itemsActivity == 'inactive' ? 0 : 1);
					}

					$this->_applyItemConditions($oInformationsystem_Items);

					$Informationsystem_Item = $oInformationsystem_Items->getByGroupIdAndPath($this->group, $sPath);

					if (!$this->item && !is_null($Informationsystem_Item))
					{
						if (in_array($Informationsystem_Item->getSiteuserGroupId(), $this->_aSiteuserGroups))
						{
							$this->group = $Informationsystem_Item->informationsystem_group_id;
							$this->item = $Informationsystem_Item->id;
						}
						else
						{
							return $this->error403();
						}
					}
					else
					{
						$this->group = FALSE;
						$this->item = FALSE;
						return $this->error404();
					}
				}
			}
		}
		elseif (is_null($path) && Core::$url['path'] != '/')
		{
			return $this->error404();
		}

		$seo_title = $seo_description = $seo_keywords = NULL;

		// Apply SEO templates
		if ($this->item)
		{
			$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $this->item);

			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('informationsystem', $oInformationsystem)
				->addObject('group', $oInformationsystem_Item->Informationsystem_Group)
				->addObject('item', $oInformationsystem_Item)
				->addObject('this', $this);

			// Title
			if ($oInformationsystem_Item->seo_title != '')
			{
				$seo_title = $oInformationsystem_Item->seo_title;
			}
			elseif ($this->_seoItemTitle != '')
			{
				$seo_title = $oCore_Meta->apply($this->_seoItemTitle);
			}
			else
			{
				$seo_title = $oInformationsystem_Item->name;
			}

			// Description
			if ($oInformationsystem_Item->seo_description != '')
			{
				$seo_description = $oInformationsystem_Item->seo_description;
			}
			elseif ($this->_seoItemDescription != '')
			{
				$seo_description = $oCore_Meta->apply($this->_seoItemDescription);
			}
			else
			{
				$seo_description = $oInformationsystem_Item->name;
			}

			// Keywords
			if ($oInformationsystem_Item->seo_keywords != '')
			{
				$seo_keywords = $oInformationsystem_Item->seo_keywords ;
			}
			elseif ($this->_seoItemKeywords != '')
			{
				$seo_keywords = $oCore_Meta->apply($this->_seoItemKeywords);
			}
			else
			{
				$seo_keywords = $oInformationsystem_Item->name;
			}
		}
		elseif ($this->group)
		{
			$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $this->group);

			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('informationsystem', $oInformationsystem)
				->addObject('group', $oInformationsystem_Group)
				->addObject('this', $this);

			// Title
			if ($oInformationsystem_Group->seo_title != '')
			{
				$seo_title = $oInformationsystem_Group->seo_title;
			}
			elseif ($this->_seoGroupTitle != '')
			{
				$seo_title = $oCore_Meta->apply($this->_seoGroupTitle);
			}
			else
			{
				$seo_title = $oInformationsystem_Group->name;
			}

			// Description
			if ($oInformationsystem_Group->seo_description != '')
			{
				$seo_description = $oInformationsystem_Group->seo_description;
			}
			elseif ($this->_seoGroupDescription != '')
			{
				$seo_description = $oCore_Meta->apply($this->_seoGroupDescription);
			}
			else
			{
				$seo_description = $oInformationsystem_Group->name;
			}

			// Keywords
			if ($oInformationsystem_Group->seo_keywords != '')
			{
				$seo_keywords = $oInformationsystem_Group->seo_keywords ;
			}
			elseif ($this->_seoGroupKeywords != '')
			{
				$seo_keywords = $oCore_Meta->apply($this->_seoGroupKeywords);
			}
			else
			{
				$seo_keywords = $oInformationsystem_Group->name;
			}
		}
		elseif (!is_null($this->tag) && Core::moduleIsActive('tag'))
		{
			$seo_title = $oTag->seo_title != ''
				? $oTag->seo_title
				: Core::_('Informationsystem.tag', $oTag->name);

			$seo_description = $oTag->seo_description != ''
				? $oTag->seo_description
				: $oTag->name;

			$seo_keywords = $oTag->seo_keywords != ''
				? $oTag->seo_keywords
				: $oTag->name;
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
	 * Apply forbidden tags
	 *
	 * @param Informationsystem_Group $oInformationsystem_Group
	 * @return self
	 */
	public function applyGroupsForbiddenTags($oInformationsystem_Group)
	{
		if (!is_null($this->groupsForbiddenTags))
		{
			foreach ($this->groupsForbiddenTags as $forbiddenTag)
			{
				$oInformationsystem_Group->addForbiddenTag($forbiddenTag);
			}
		}

		return $this;
	}

	/**
	 * Apply forbidden XML tags for items
	 * @param Informationsystem_Item_Model $oInformationsystem_Item item
	 * @return self
	 */
	public function applyItemsForbiddenTags($oInformationsystem_Item)
	{
		if (!is_null($this->itemsForbiddenTags))
		{
			foreach ($this->itemsForbiddenTags as $forbiddenTag)
			{
				$oInformationsystem_Item->addForbiddenTag($forbiddenTag);
			}
		}

		return $this;
	}

	/**
	 * Adding all groups for showing
	 * @return self
	 */
	public function addAllGroups()
	{
		$this->_aInformationsystem_Groups = array();

		$aInformationsystem_Groups = $this->_Informationsystem_Groups->findAll();

		foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
		{
			$this->_groupIntoArray($oInformationsystem_Group);
		}

		$bTpl = $this->_mode == 'tpl';

		if (!$bTpl)
		{
			$this->_addGroupsByParentId(0, $this);
		}

		return $this;
	}

	/**
	 * Add groups tree
	 * @return self
	 */
	public function addTreeGroups()
	{
		$this->_aInformationsystem_Groups = array();

		$group_id = intval($this->group);

		// Потомки текущего уровня
		$aInformationsystem_Groups = $this->_Informationsystem_Groups->getByParentId($group_id);

		foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
		{
			$this->_groupIntoArray($oInformationsystem_Group);
		}

		if ($group_id != 0)
		{
			$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $group_id)
				->clearEntities();

			do {
				$this->applyGroupsForbiddenTags($oInformationsystem_Group);

				$this->_aInformationsystem_Groups[$oInformationsystem_Group->parent_id][] = $oInformationsystem_Group;
			} while ($oInformationsystem_Group = $oInformationsystem_Group->getParent());
		}

		$bTpl = $this->_mode == 'tpl';

		if (!$bTpl)
		{
			$this->_addGroupsByParentId(0, $this);
		}

		return $this;
	}

	/**
	 * Add group $oInformationsystem_Group into $this->_aInformationsystem_Groups
	 * @param Informationsystem_Group_Model $oInformationsystem_Group
	 * @return self
	 */
	protected function _groupIntoArray($oInformationsystem_Group)
	{
		$oInformationsystem_Group->clearEntities();
		$this->applyGroupsForbiddenTags($oInformationsystem_Group);

		$parent_id = $oInformationsystem_Group->parent_id;

		// Shortcut
		if ($oInformationsystem_Group->shortcut_id
			&& $oInformationsystem_Group->shortcut_id != $oInformationsystem_Group->parent_id)
		{
			$oShortcut_Group = $oInformationsystem_Group;
			$oOriginal_Informationsystem_Group = $oInformationsystem_Group->Shortcut;

			$oInformationsystem_Group = clone $oOriginal_Informationsystem_Group;

			$oInformationsystem_Group
				->id($oOriginal_Informationsystem_Group->id)
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

		$this->_aInformationsystem_Groups[$parent_id][] = $oInformationsystem_Group;

		return $this;
	}

	/**
	 * Add groups to object by parent ID
	 * @param int $parent_id parent group ID
	 * @param object $parentObject object
	 * @return self
	 */
	protected function _addGroupsByParentId($parent_id, $parentObject)
	{
		if (isset($this->_aInformationsystem_Groups[$parent_id]))
		{
			$bIsArrayGroupsProperties = is_array($this->groupsProperties);
			$bIsArrayPropertiesForGroups = is_array($this->propertiesForGroups);

			$oInformationsystem = $this->getEntity();

			foreach ($this->_aInformationsystem_Groups[$parent_id] as $oInformationsystem_Group)
			{
				// Properties for informationsystem's group entity
				if ($this->groupsProperties
					&& (!$bIsArrayPropertiesForGroups || in_array($oInformationsystem_Group->id, $this->propertiesForGroups)))
				{
					/*$aProperty_Values = $oInformationsystem_Group->getPropertyValues(TRUE, $bIsArrayGroupsProperties ? $this->groupsProperties : array());

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
								$oProperty_Value->dateFormat($oInformationsystem->format_date);
							}
							elseif ($type == 9)
							{
								$oProperty_Value->dateTimeFormat($oInformationsystem->format_datetime);
							}

							$oInformationsystem_Group->addEntity($oProperty_Value);
						}
					}*/

					$oInformationsystem_Group->showXmlProperties($this->groupsProperties);
				}
				else
				{
					$oInformationsystem_Group->showXmlProperties(FALSE);
				}

				$parentObject->addEntity($oInformationsystem_Group);

				$this->_addGroupsByParentId($oInformationsystem_Group->id, $oInformationsystem_Group);
			}
		}
		return $this;
	}

	/**
	 * Add items properties list to $parentObject
	 * @param int $parent_id parent group ID
	 * @param object $parentObject object
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
			$parentObject->addEntities($this->_aItem_Properties[$parent_id]);
		}

		return $this;
	}

	/**
	 * Add items properties list to $parentObject
	 * @param int $parent_id parent group ID
	 * @param object $parentObject object
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
	 * Add groups properties list to $parentObject
	 * @param int $parent_id parent group ID
	 * @param object $parentObject object
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
	 * @return self
	 */
	protected function _showPanel()
	{
		$oInformationsystem = $this->getEntity();

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
			$sPath = '/admin/informationsystem/item/index.php';
			$sAdditional = "hostcms[action]=edit&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$this->group}&hostcms[checked][1][0]=1";
			$sTitle = Core::_('Informationsystem_Item.information_items_add_form_title');

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

			$sPath = '/admin/informationsystem/item/index.php';
			$sAdditional = "hostcms[action]=edit&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$this->group}&hostcms[checked][0][0]=1";
			$sTitle = Core::_('Informationsystem_Group.information_groups_add_form_title');

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
				$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $this->group);

				$sPath = '/admin/informationsystem/item/index.php';
				$sAdditional = "hostcms[action]=edit&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystem_Group->parent_id}&hostcms[checked][0][{$this->group}]=1";
				$sTitle = Core::_('Informationsystem_Group.information_groups_edit_form_title', $oInformationsystem_Group->name);

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
			$sPath = '/admin/informationsystem/item/index.php';
			$sAdditional = "&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$this->group}";
			$sTitle = Core::_('Informationsystem_Group.information_system_top_menu_groups');

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
				$sPath = '/admin/informationsystem/item/index.php';
				$sAdditional = "hostcms[action]=markDeleted&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystem_Group->parent_id}&hostcms[checked][0][{$this->group}]=1";
				$sTitle = Core::_('Informationsystem_Group.markDeleted');

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

			$sPath = '/admin/informationsystem/index.php';
			$sAdditional = "hostcms[action]=edit&informationsystem_dir_id={$oInformationsystem->informationsystem_dir_id}&hostcms[checked][1][{$oInformationsystem->id}]=1";
			$sTitle = Core::_('Informationsystem.edit_title', $oInformationsystem->name);

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
			$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $this->item);

			// Edit
			$sPath = '/admin/informationsystem/item/index.php';
			$sAdditional = "hostcms[action]=edit&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$this->group}&hostcms[checked][1][{$this->item}]=1";
			$sTitle = Core::_('Informationsystem_Item.information_items_edit_form_title', $oInformationsystem_Item->name);

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
			$sPath = '/admin/informationsystem/item/index.php';
			$sAdditional = "hostcms[action]=copy&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$this->group}&hostcms[checked][1][{$this->item}]=1";
			$sTitle = Core::_('Informationsystem_Item.information_items_copy_form_title');

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
			$sPath = '/admin/informationsystem/item/index.php';
			$sAdditional = "&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$this->group}";
			$sTitle = Core::_('Informationsystem_Group.information_system_top_menu_groups');

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
			$sPath = '/admin/informationsystem/item/comment/index.php';
			$sAdditional = "informationsystem_item_id={$this->item}";
			$sTitle = Core::_('Informationsystem_Item.show_all_comments_top_menu');

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
			$sPath = '/admin/informationsystem/item/index.php';
			$sAdditional = "hostcms[action]=markDeleted&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$this->group}&hostcms[checked][1][{$this->item}]=1";
			$sTitle = Core::_('Informationsystem_Item.markDeleted');

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
			$this->_Informationsystem_Items
				->queryBuilder()
				->where('informationsystem_items.active', '=', $this->itemsActivity == 'inactive' ? 0 : 1);
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
			$this->_Informationsystem_Groups
				->queryBuilder()
				->where('informationsystem_groups.active', '=', $this->groupsActivity == 'inactive' ? 0 : 1);
		}

		return $this;
	}

	/**
	 * Set goods sorting
	 * @param $column Column name
	 * @return self
	 */
	public function orderBy($column, $direction = 'ASC')
	{
		$this->informationsystemItems()
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
	 * Add filter condition
	 * ->addFilter('property', 17, '=', 33)
	 */
	public function addFilter()
	{
		$args = func_get_args();

		$iCountArgs = count($args);

		if ($iCountArgs < 4)
		{
			throw new Core_Exception("addFilter() expected at least 4 arguments");
		}

		switch ($args[0])
		{
			case 'property':
				/*if ($iCountArgs < 4)
				{
					throw new Core_Exception("addFilter('property') expected 4 arguments");
				}*/

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
	 */
	public function removeFilter()
	{
		$args = func_get_args();

		$iCountArgs = count($args);

		if ($iCountArgs < 2)
		{
			throw new Core_Exception("removeFilter() expected at least 2 arguments");
		}

		switch ($args[0])
		{
			case 'property':
				/*if ($iCountArgs < 2)
				{
					throw new Core_Exception("removeFilter('property') expected 2 arguments");
				}*/

				$property_id = $args[1];

				if (isset($this->_aFilterProperties[$property_id]))
				{
					unset($this->_aFilterProperties[$property_id]);
				}
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
		$this->_basicFilter();

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

			$this->informationsystemItems()->queryBuilder()
				->leftJoin('informationsystem_item_properties', 'informationsystem_items.informationsystem_id', '=', 'informationsystem_item_properties.informationsystem_id')
				->setAnd()
				->open();

			foreach ($this->_aFilterProperties as $iPropertyId => $aTmpProperties)
			{
				foreach ($aTmpProperties as $aTmpProperty)
				{
					list($oProperty, $condition, $aPropertyValues) = $aTmpProperty;
					$tableName = $oProperty->createNewValue(0)->getTableName();

					!in_array($tableName, $aTableNames) && $aTableNames[] = $tableName;

					$this->informationsystemItems()->queryBuilder()
						->where('informationsystem_item_properties.property_id', '=', $oProperty->id);

					// Для строк фильтр LIKE %...%
					if ($oProperty->type == 1)
					{
						foreach ($aPropertyValues as $propertyValue)
						{
							$this->informationsystemItems()->queryBuilder()
								->where($tableName . '.value', 'LIKE', "%{$propertyValue}%");
						}
					}
					else
					{
						// 7 - Checkbox
						$oProperty->type == 7 && $aPropertyValues[0] != '' && $aPropertyValues = array(1);

						// 7 - Checkbox, 3 - List
						$bCheckUnset = $oProperty->type != 7 && $oProperty->type != 3;

						$bCheckUnset && $this->informationsystemItems()->queryBuilder()->open();

						$this->informationsystemItems()->queryBuilder()
							->where(
								$tableName . '.value',
								count($aPropertyValues) == 1 ? $condition : 'IN',
								count($aPropertyValues) == 1 ? $aPropertyValues[0] : $aPropertyValues
							);

						$bCheckUnset && $this->informationsystemItems()->queryBuilder()
							->setOr()
							->where($tableName . '.value', 'IS', NULL)
							->close();
					}

					// Между значениями значение по AND (например, значение => 10 и значение <= 99)
					$this->informationsystemItems()->queryBuilder()->setAnd();

					$this->_addFilterPropertyToXml($oProperty, $condition, $aPropertyValues);
				}

				// при смене свойства сравнение через OR
				$this->informationsystemItems()->queryBuilder()->setOr();
			}

			$this->informationsystemItems()->queryBuilder()
				->close()
				->groupBy('informationsystem_items.id');

			foreach ($aTableNames as $tableName)
			{
				$this->informationsystemItems()->queryBuilder()
					->leftJoin($tableName, 'informationsystem_items.id', '=', $tableName . '.entity_id',
						array(
							array('AND' => array('informationsystem_item_properties.property_id', '=', Core_QueryBuilder::expression($tableName . '.property_id')))
						)
					);
			}

			$havingCount = count($this->_aFilterProperties);

			$havingCount > 1
				&& $this->informationsystemItems()->queryBuilder()
						->having(Core_Querybuilder::expression('COUNT(DISTINCT `informationsystem_item_properties`.`property_id`)'), '=', $havingCount);
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
					->name($xmlName)
					->value($propertyValue)
					->addAttribute('condition', $condition)
			);
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
}