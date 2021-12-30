<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ комментариев информационной системы.
 *
 * Доступные методы:
 *
 * - commentsForbiddenTags(array('email')) массив тегов комментария, запрещенных к передаче в генерируемый XML
 * - commentsActivity('active'|'inactive'|'all') отображать комментарии: active - только активные, inactive - только неактивные, all - все, по умолчанию - active
 * - offset($offset) смещение, с которого выводить комментарии. По умолчанию 0
 * - limit($limit) количество выводимых комментариев
 * - page(2) текущая страница, по умолчанию 0, счет ведется с 0
 * - pattern($pattern) шаблон разбора данных в URI, см. __construct()
 * - cache(TRUE|FALSE) использовать кэширование, по умолчанию TRUE
 * - calculateTotal(TRUE|FALSE) вычислять общее количество найденных, по умолчанию TRUE
 *
 * Доступные свойства:
 *
 * - total общее количество доступных для отображения записей
 * - patternParams массив данных, извелеченных из URI при применении pattern
 *
 * <code>
 * $Informationsystem_Comment_Controller_Show = new Informationsystem_Comment_Controller_Show(
 * 	Core_Entity::factory('Informationsystem', 1)
 * );
 *
 * $Informationsystem_Comment_Controller_Show
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('СписокКомментариевНаГлавной')
 * 	)
 * 	->limit(5)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Constructor.
	 * @param Informationsystem_Model $oInformationsystem information system
	 */
	public function __construct(Informationsystem_Model $oInformationsystem)
	{
		parent::__construct($oInformationsystem->clearEntities());

		$this->pattern = rawurldecode(Core_Str::rtrimUri($this->getEntity()->Structure->getPath())) . '/comments({path}/)(page-{page}/)';

		$this->patternExpressions = array(
			'page' => '\d+',
		);

		$this->limit = 5;
		$this->calculateTotal = TRUE;
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

		$this->commentsActivity = strtolower($this->commentsActivity);
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

		$this->_setComments();

		$oInformationsystem = $this->getEntity();

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
				return $this->error404();
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

				$oInformationsystem_Item = $oComment->Informationsystem_Item;

				// Tagged cache
				$bCache && $this->_cacheTags[] = 'informationsystem_item_' . $oInformationsystem_Item->id;

				$oComment->clearEntities();

				$this->applyforbiddenTags($oComment);

				$oComment->addEntity(
					$oInformationsystem_Item->clearEntities()
				);

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

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

		return $this;
	}

	/**
	 * Apply forbidden XML tags for comments
	 * @param Comment_Model $oComment
	 * @return self
	 */
	public function applyforbiddenTags($oComment)
	{
		if (!is_null($this->commentsForbiddenTags))
		{
			foreach ($this->commentsForbiddenTags as $forbiddenTag)
			{
				$oComment->addForbiddenTag($forbiddenTag);
			}
		}

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
