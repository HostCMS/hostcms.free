<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ облака тегов информационной системы.
 *
 * Доступные методы:
 *
 * - group($id) идентификатор информационной группы или массив идентификаторов
 * - offset($offset) смещение, с которого выводить метки. По умолчанию 0
 * - limit($limit) количество выводимых меток
 *
 * <code>
 * $Informationsystem_Controller_Tag_Show = new Informationsystem_Controller_Tag_Show(
 * 		Core_Entity::factory('Informationsystem', 1)
 * 	);
 *
 * 	$Informationsystem_Controller_Tag_Show
 * 		->xsl(
 * 			Core_Entity::factory('Xsl')->getByName('ОблакоТэговИнформационнойСистемы')
 * 		)
 * 		->limit(30)
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Controller_Tag_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'group',
		'tag_dir',
		'offset',
		'limit',
		'total',
		'cache',
	);

	/**
	 * Tags
	 * @var Tag_Model
	 */
	protected $_tags = NULL;

	/**
	 * Constructor.
	 * @param Informationsystem_Model $oInformationsystem information system
	 */
	public function __construct(Informationsystem_Model $oInformationsystem)
	{
		parent::__construct($oInformationsystem->clearEntities());

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
			//->from('tag_informationsystem_items')
			//->leftJoin('tags', 'tag_informationsystem_items.tag_id', '=', 'tags.id')
			->join('tag_informationsystem_items', 'tag_informationsystem_items.tag_id', '=', 'tags.id')
			->join('informationsystem_items', 'tag_informationsystem_items.informationsystem_item_id', '=', 'informationsystem_items.id')
			->leftJoin('informationsystem_groups', 'informationsystem_items.informationsystem_group_id', '=', 'informationsystem_groups.id',
				array(
					array('AND' => array('informationsystem_groups.deleted', '=', 0)),
					array('AND' => array('informationsystem_groups.siteuser_group_id', 'IN', $aSiteuserGroups))
				)
			)
			->where('informationsystem_items.siteuser_group_id', 'IN', $aSiteuserGroups)
			->where('informationsystem_items.informationsystem_id', '=', $oInformationsystem->id)
			->where('informationsystem_items.deleted', '=', 0)
			//->where('tags.deleted', '=', 0)
			->groupBy('tag_informationsystem_items.tag_id')
			->having('count', '>', 0)
			->orderBy('tags.name', 'ASC');

		$this->group = NULL;
		$this->tag_dir = FALSE;
		$this->offset = 0;
		$this->cache = TRUE;
	}

	/**
	 * Get queryBuilder
	 * @return Core_QueryBuilder_Select
	 */
	public function queryBuilder()
	{
		return $this->_tags->queryBuilder();
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Informationsystem_Controller_Tag_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		if ($this->cache && Core::moduleIsActive('cache'))
		{
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$inCache = $oCore_Cache->get($cacheKey = strval($this), $cacheName = 'informationsystem_tags');

			if (!is_null($inCache))
			{
				echo $inCache;
				return $this;
			}
		}

		$oInformationsystem = $this->getEntity();

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('group')
				->value(intval($this->group)) // FALSE => 0
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		if (!is_null($this->group))
		{
			$this->_tags->queryBuilder()
				->where(
					'informationsystem_items.informationsystem_group_id', is_array($this->group) ? 'IN' : '=', $this->group
				);
		}

		if ($this->tag_dir !== FALSE)
		{
			$this->_tags->queryBuilder()
				->where('tag_dir_id', is_array($this->tag_dir) ? 'IN' : '=', $this->tag_dir);
		}

		if ($this->limit > 0)
		{
			$this->_tags->queryBuilder()
				->limit($this->offset, $this->limit);
		}

		$aTags = $this->_tags->findAll(FALSE);

		$this->addEntities($aTags);

		echo $content = $this->get();
		$this->cache && Core::moduleIsActive('cache') && $oCore_Cache->set($cacheKey, $content, $cacheName);

		return $this;
	}
}