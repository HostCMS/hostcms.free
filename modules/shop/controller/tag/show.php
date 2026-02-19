<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ облака тегов магазина.
 *
 * Доступные методы:
 *
 * - tag_dirs(TRUE|FALSE) отображать разделы тегов, по умолчанию FALSE
 * - tag_dir($tag_dir_id) идентификатор раздела меток, из которого выводить метки
 * - group($id) идентификатор группы магазина или массив идентификаторов
 * - subgroups(TRUE|FALSE) отображать товары из подгрупп, доступно при указании в group() одного идентификатора родительской группы (не массива), по умолчанию FALSE
 * - offset($offset) смещение, с которого выводить метки. По умолчанию 0
 * - limit($limit) количество выводимых меток
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/shop' Магазин
 * - '/shop/tag' Тег
 * - '/shop/tag_dir' Раздел тегов
 *
 * <code>
 * $Shop_Controller_Tag_Show = new Shop_Controller_Tag_Show(
 * 		Core_Entity::factory('Shop', 1)
 * 	);
 *
 * 	$Shop_Controller_Tag_Show
 * 		->xsl(
 * 			Core_Entity::factory('Xsl')->getByName('ОблакоТэговМагазин')
 * 		)
 * 		->limit(30)
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Controller_Tag_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'tag_dirs',
		'group',
		'subgroups',
		'tag_dir',
		'offset',
		'limit',
		'total',
		'cache',
	);

	/**
	 * List of tags
	 * @var Tag_Model
	 */
	protected $_tags = NULL;

	/**
	 * List of Tag_Dirs
	 * @var array
	 */
	protected $_aTag_Dirs = array();

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		$this->_setTags();

		$this->group = FALSE;
		$this->tag_dirs = $this->tag_dir = FALSE;
		$this->offset = 0;
		$this->cache = TRUE;
	}

	/**
	 * Clone controller
	 * @return void
	 * @ignore
	 */
	public function __clone()
	{
		$this->_setTags();
	}

	/**
	 * Prepare items for showing
	 * @return self
	 */
	protected function _setTags()
	{
		$oShop = $this->getEntity();

		$siteuser_id = 0;
		$aSiteuserGroups = array(0, -1);

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if ($oSiteuser)
			{
				$siteuser_id = $oSiteuser->id;

				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
				foreach ($aSiteuser_Groups as $oSiteuser_Group)
				{
					$aSiteuserGroups[] = $oSiteuser_Group->id;
				}
			}
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_id')
				->value($siteuser_id)
		);

		$this->_tags = Core_Entity::factory('Tag');

		$this->_tags->queryBuilder()
			->clearSelect()
			->straightJoin()
			->select(array('COUNT(tag_id)', 'count'), 'tags.*')
			->join('tag_shop_items', 'tag_shop_items.tag_id', '=', 'tags.id',
				array(
					array('AND' => array('tag_shop_items.site_id', '=', $oShop->site_id))
				)
			)
			->join('shop_items', 'tag_shop_items.shop_item_id', '=', 'shop_items.id')
			->leftJoin('shop_groups', 'shop_items.shop_group_id', '=', 'shop_groups.id',
				array(
					array('AND' => array('shop_groups.siteuser_group_id', 'IN', $aSiteuserGroups)),
					array('AND' => array('shop_groups.deleted', '=', 0))
				)
			)
			->open()
				->where('shop_items.shop_group_id', '=', 0)
				->setOr()
				->where('shop_groups.id', 'IS NOT', NULL)
			->close()
			->where('shop_items.siteuser_group_id', 'IN', $aSiteuserGroups)
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.deleted', '=', 0);

		$this->_tags->queryBuilder()
			->groupBy('tags.id')
			->having('count', '>', 0)
			->orderBy('tags.sorting', 'ASC')
			->orderBy('tags.name', 'ASC');

		return $this;
	}

	/**
	 * Get queryBuilder instance
	 * @return Core_QueryBuilder_Select
	 */
	public function queryBuilder()
	{
		return $this->_tags->queryBuilder();
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Controller_Tag_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$this->group === 0 && $this->subgroups
			&& $this->group = FALSE;

		if ($this->cache && Core::moduleIsActive('cache'))
		{
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$inCache = $oCore_Cache->get($cacheKey = strval($this), $cacheName = 'shop_tags');

			if (!is_null($inCache))
			{
				echo $inCache;
				return $this;
			}
		}

		if ($this->tag_dirs)
		{
			$aTag_Dirs = Core_Entity::factory('Tag_Dir')->findAll();

			foreach ($aTag_Dirs as $oTag_Dir)
			{
				$this->_aTag_Dirs[$oTag_Dir->parent_id][] = $oTag_Dir;
			}

			$this->_addDirsByParentId(0, $this);
		}

		$oCore_Xml_Entity_Group = Core::factory('Core_Xml_Entity')
			->name('group')
			->value(is_array($this->group) ? Core_Array::first($this->group) : intval($this->group)); // FALSE => 0

		if (is_array($this->group))
		{
			$oCore_Xml_Entity_Group->addAttribute('all', implode(',', $this->group));
		}

		$this->addEntity(
			$oCore_Xml_Entity_Group
		)
		->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		$oQueryBuilder = $this->_tags->queryBuilder();

		$this->applyFilterGroupCondition($oQueryBuilder);

		// Ярлыки
		if ($this->group !== FALSE)
		{
			$oShop = $this->getEntity();

			$oCore_QueryBuilder_Select_Shortcuts = Core_QueryBuilder::select('shop_items.shortcut_id')
				->from('shop_items')
				->where('shop_items.shop_id', '=', $oShop->id)
				->where('shop_items.deleted', '=', 0)
				->where('shop_items.shortcut_id', '>', 0);

			$this->group
				? $this->applyFilterGroupCondition($oCore_QueryBuilder_Select_Shortcuts, 'shop_items.shop_group_id')
				: $oCore_QueryBuilder_Select_Shortcuts->where('shop_items.shop_group_id', '=', $this->group);

			$this->_tags
				->queryBuilder()
				->setOr()
				->where('shop_items.id', 'IN', $oCore_QueryBuilder_Select_Shortcuts);
		}

		if ($this->tag_dir !== FALSE)
		{
			$oQueryBuilder->where('tag_dir_id', is_array($this->tag_dir) ? 'IN' : '=', $this->tag_dir);
		}

		if ($this->limit > 0)
		{
			$oQueryBuilder->limit($this->offset, $this->limit);
		}

		$aTags = $this->_tags->findAll(FALSE);

		foreach ($aTags as $oTag)
		{
			$oTag->clearEntities();
			$this->applyForbiddenAllowedTags('/shop/tag', $oTag);
			$this->addEntity($oTag);
		}

		echo $content = $this->get();
		$this->cache && Core::moduleIsActive('cache') && $oCore_Cache->set($cacheKey, $content, $cacheName);

		return $this;
	}

	/**
	 * Apply Condition By Group, depends on $this->group, $this->subgroups
	 * @param Core_QueryBuilder_Select $oQueryBuilder
	 * @return self
	 */
	public function applyFilterGroupCondition($oQueryBuilder)
	{
		if ($this->group !== FALSE)
		{
			if ($this->subgroups)
			{
				// Fast filter + scalar group
				if ($this->getEntity()->filter && !is_array($this->group))
				{
					/*$method = $oQueryBuilder->isStraightJoin()
						? 'firstJoin'
						: 'join';*/

					$tableName = $this->getFilterGroupTableName();
					$oQueryBuilder->join($tableName, 'shop_items.shop_group_id', '=', $tableName . '.child_id',
						array(
							array('AND' => array($tableName . '.shop_group_id', '=', $this->group))
						)
					);
				}
				else
				{
					$oQueryBuilder->where('shop_items.shop_group_id', 'IN',
						!is_array($this->group)
							? $this->getSubgroups($this->group)
							: $this->group
					);
				}
			}
			else
			{
				$oQueryBuilder->where('shop_items.shop_group_id', is_array($this->group) ? 'IN' : '=', $this->group);
			}
		}

		return $this;
	}

	/**
	 * Add dirs to the object by parent ID
	 * @param int $parent_id parent group ID
	 * @param object $parentObject object
	 * @return self
	 */
	protected function _addDirsByParentId($parent_id, $parentObject)
	{
		if (isset($this->_aTag_Dirs[$parent_id]))
		{
			foreach ($this->_aTag_Dirs[$parent_id] as $oTag_Dir)
			{
				$oTag_Dir->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/tag_dir', $oTag_Dir);
				$parentObject->addEntity($oTag_Dir);

				$this->_addDirsByParentId($oTag_Dir->id, $oTag_Dir);
			}
		}
		return $this;
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

			$aTmp = Core_QueryBuilder::select('id', 'parent_id')
				->from('shop_groups')
				->where('shop_id', '=', $oShop->id)
				->where('shortcut_id', '=', 0)
				->where('deleted', '=', 0)
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				$this->_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset($this->_aGroupTree[$parent_id]))
		{
			foreach ($this->_aGroupTree[$parent_id] as $childrenGroup)
			{
				$aReturn[] = $childrenGroup['id'];
				$aReturn = array_merge($aReturn, $this->fillShopGroups($childrenGroup['id']));
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