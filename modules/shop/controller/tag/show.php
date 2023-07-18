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
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			->select(array('COUNT(tag_id)', 'count'), 'tags.*')
			//->from('tag_shop_items')
			//->leftJoin('tags', 'tag_shop_items.tag_id', '=', 'tags.id')
			->join('tag_shop_items', 'tag_shop_items.tag_id', '=', 'tags.id')
			->join('shop_items', 'tag_shop_items.shop_item_id', '=', 'shop_items.id')
			->leftJoin('shop_groups', 'shop_items.shop_group_id', '=', 'shop_groups.id',
				array(
					array('AND' => array('shop_groups.siteuser_group_id', 'IN', $aSiteuserGroups)),
					array('AND' => array('shop_groups.deleted', '=', 0)),
				)
			)
			->where('shop_items.siteuser_group_id', 'IN', $aSiteuserGroups)
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.deleted', '=', 0)
			//->where('tags.deleted', '=', 0)
			->groupBy('tag_shop_items.tag_id')
			->having('count', '>', 0)
			->orderBy('tags.name', 'ASC');

		$this->group = NULL;
		$this->tag_dirs = $this->tag_dir = FALSE;
		$this->offset = 0;
		$this->cache = TRUE;
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

		$oShop = $this->getEntity();

		if ($this->tag_dirs)
		{
			$aTag_Dirs = Core_Entity::factory('Tag_Dir')->findAll();

			foreach ($aTag_Dirs as $oTag_Dir)
			{
				$this->_aTag_Dirs[$oTag_Dir->parent_id][] = $oTag_Dir;
			}

			$this->_addDirsByParentId(0, $this);
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('group')
				->value(intval($this->group)) // FALSE => 0
		)
		->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		if (!is_null($this->group))
		{
			$this->_tags
				->queryBuilder()
				->where('shop_items.shop_group_id', is_array($this->group) ? 'IN' : '=', $this->group);
		}

		if ($this->tag_dir !== FALSE)
		{
			$this->_tags
				->queryBuilder()
				->where('tag_dir_id', is_array($this->tag_dir) ? 'IN' : '=', $this->tag_dir);
		}

		if ($this->limit > 0)
		{
			$this->_tags->queryBuilder()
				->limit($this->offset, $this->limit);
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
}