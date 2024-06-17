<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ комментариев информационной системы.
 *
 * Доступные методы:
 *
 * - commentsActivity('active'|'inactive'|'all') отображать комментарии: active - только активные, inactive - только неактивные, all - все, по умолчанию - active
 * - offset($offset) смещение, с которого выводить комментарии. По умолчанию 0
 * - limit($limit) количество выводимых комментариев
 * - page(2) текущая страница, по умолчанию 0, счет ведется с 0
 * - pattern($pattern) шаблон разбора данных в URI, см. __construct()
 * - cache(TRUE|FALSE) использовать кэширование, по умолчанию TRUE
 * - calculateTotal(TRUE|FALSE) вычислять общее количество найденных, по умолчанию TRUE
 * - commentsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств комментариев, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - commentsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств комментариев, по умолчанию TRUE.
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 *
 * Доступные свойства:
 *
 * - total общее количество доступных для отображения записей
 * - patternParams массив данных, извелеченных из URI при применении pattern
 *
 * Устаревшие методы:
 *
 * - commentsForbiddenTags(array('email')) массив тегов комментария, запрещенных к передаче в генерируемый XML
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/informationsystem' Информационная система
 * - '/informationsystem/comment' Комментарии
 * - '/informationsystem/informationsystem_item' Информационный элемент
 *
 * <code>
 * $Informationsystem_Comment_Controller_Show = new Informationsystem_Comment_Controller_Show(
 * 	Core_Entity::factory('Informationsystem', 1)
 * );
 *
 * $Informationsystem_Comment_Controller_Show
 * 	->xsl('СписокКомментариевНаГлавной')
 * 	->commentsProperties(TRUE)
 * 	->limit(5)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Informationsystem_Comment_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'offset',
		'limit',
		'page',
		'total',
		'pattern',
		'patternExpressions',
		'patternParams',
		'cache',
		'commentsForbiddenTags',
		'commentsActivity',
		'calculateTotal',
		'commentsProperties',
		'commentsPropertiesList',
		'url'
	);

	/**
	 * Comments QB
	 * @var mixed
	 */
	protected $_Comments = NULL;

	/**
	 * Cache name
	 * @var string
	 */
	protected $_cacheName = 'informationsystem_comment_show';

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
	 * Constructor.
	 * @param Informationsystem_Model $oInformationsystem information system
	 */
	public function __construct(Informationsystem_Model $oInformationsystem)
	{
		parent::__construct($oInformationsystem->clearEntities());

		// Named subpatterns {name} can consist of up to 32 alphanumeric characters and underscores, but must start with a non-digit.
		$this->pattern = rawurldecode(Core_Str::rtrimUri($this->getEntity()->Structure->getPath())) . '/comments({path}/)(page-{page}/)';

		$this->patternExpressions = array(
			'page' => '\d+',
		);

		$this->limit = 5;
		$this->commentsPropertiesList = $this->calculateTotal = TRUE;

		$this->commentsActivity = 'active';

		$this->commentsProperties = FALSE;
		
		$this->url = Core::$url['path'];
	}

	/**
	 * Prepare groups for showing
	 * @return self
	 */
	protected function _setComments()
	{
		$oInformationsystem = $this->getEntity();

		$this->_Comments = Core_Entity::factory('Comment');
		$this->_Comments->queryBuilder()
			->straightJoin()
			->join('comment_informationsystem_items', 'comments.id', '=', 'comment_informationsystem_items.comment_id')
			->join('informationsystem_items', 'comment_informationsystem_items.informationsystem_item_id', '=', 'informationsystem_items.id')
			->where('informationsystem_items.informationsystem_id', '=', $oInformationsystem->id)
			->where('informationsystem_items.deleted', '=', 0)
			->clearOrderBy()
			->orderBy('comments.datetime', 'DESC');

		$this->commentsActivity = strtolower((string) $this->commentsActivity);
		if ($this->commentsActivity != 'all')
		{
			$this->_Comments
				->queryBuilder()
				->where('comments.active', '=', $this->commentsActivity == 'inactive' ? 0 : 1);
		}

		return $this;
	}

	/**
	 * Get items
	 * @return Informationsystem_Item_Model
	 */
	public function comments()
	{
		return $this->_Comments;
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
		Core_Entity::factory('Comment')->getTableColumns();

		// Load user BEFORE FOUND_ROWS()
		Core_Auth::getCurrentUser();

		$this->calculateTotal && $this->_Comments
			->queryBuilder()
			->sqlCalcFoundRows();

		$this->_Comments
			->queryBuilder()
			->offset(intval($this->offset))
			->limit(intval($this->limit));

		return $this;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Informationsystem_Comment_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

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
		}

		$bTpl = $this->_mode == 'tpl';

		// Backward compatible
		is_array($this->commentsForbiddenTags) && count($this->commentsForbiddenTags)
			&& $this->addForbiddenTags('/informationsystem/comment', $this->commentsForbiddenTags);

		$this->_setComments();

		$oInformationsystem = $this->getEntity();

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
				$oProperty->clearEntities();
				$this->_aComment_Properties[$oProperty->property_dir_id][] = $oProperty;
			}

			$aProperty_Dirs = $oInformationsystem_Comment_Property_List->Property_Dirs->findAll();
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

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('page')
				->value(intval($this->page))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		if ($this->limit > 0)
		{
			$this->_setLimits();

			$aComments = $this->_Comments->findAll();

			if ($this->page && !count($aComments))
			{
				return $this->error410();
			}

			if ($this->calculateTotal)
			{
				$this->total = Core_QueryBuilder::select()->getFoundRows();

				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('total')
						->value(intval($this->total))
				);
			}

			$this->_shownIDs = array();

			foreach ($aComments as $oComment)
			{
				$oComment
					->dateFormat($oInformationsystem->format_date)
					->dateTimeFormat($oInformationsystem->format_datetime);

				$oInformationsystem_Item = $oComment->Informationsystem_Item->clearEntities();
				$this->applyForbiddenAllowedTags('/informationsystem/informationsystem_item', $oInformationsystem_Item);

				// Tagged cache
				$bCache && $this->_cacheTags[] = 'informationsystem_item_' . $oInformationsystem_Item->id;

				$oComment->clearEntities();
				$this->applyforbiddenTags($oComment);

				$oComment->showXmlProperties($this->commentsProperties);

				$oComment->addEntity($oInformationsystem_Item);

				$this->addEntity($oComment);

				$this->_shownIDs[] = $oComment->id;
			}

			unset($aComments);
		}

		echo $content = $this->get();

		$bCache && $oCore_Cache->set(
			$cacheKey,
			array('content' => $content, 'shown' => $this->_shownIDs),
			$this->_cacheName,
			$this->_cacheTags
		);

		// Clear
		$this->_cacheTags = array();

		return $this;
	}

	/**
	 * Parse URL and set controller properties
	 * @return Informationsystem_Comment_Controller_Show
	 * @hostcms-event Informationsystem_Comment_Controller_Show.onBeforeParseUrl
	 * @hostcms-event Informationsystem_Comment_Controller_Show.onAfterParseUrl
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
	 * Apply forbidden XML tags for comments
	 * @param Comment_Model $oComment
	 * @return self
	 */
	public function applyforbiddenTags($oComment)
	{
		$this->applyForbiddenAllowedTags('/informationsystem/comment', $oComment);
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

	/**
	 * Define handler for 403 error
	 * @return self
	 */
	public function error403()
	{
		Core_Page::instance()->error403();

		return $this;
	}
}