<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ комментариев магазина.
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
 * - '/' или '/shop' Магазин
 * - '/shop/comment' Комментарии
 * - '/shop/shop_item' Товар
 *
 * <code>
 * $Shop_Comment_Controller_Show = new Shop_Comment_Controller_Show(
 * 	Core_Entity::factory('Shop', 1)
 * );
 *
 * $Shop_Comment_Controller_Show
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('СписокКомментариевНаГлавной')
 * 	)
 * 	->limit(5)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Comment_Controller_Show extends Core_Controller
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
	protected $_cacheName = 'shop_comment_show';

	/**
	 * Constructor.
	 * @param Shop_Model $oShop information system
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

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
		$oShop = $this->getEntity();

		$this->_Comments = Core_Entity::factory('Comment');
		$this->_Comments->queryBuilder()
			->straightJoin()
			->join('comment_shop_items', 'comments.id', '=', 'comment_shop_items.comment_id')
			->join('shop_items', 'comment_shop_items.shop_item_id', '=', 'shop_items.id')
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.deleted', '=', 0)
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
	 * @return Shop_Item_Model
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
	 * @hostcms-event Shop_Comment_Controller_Show.onBeforeRedeclaredShow
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

		// Backward compatible
		is_array($this->commentsForbiddenTags) && count($this->commentsForbiddenTags)
			&& $this->addForbiddenTags('/shop/comment', $this->commentsForbiddenTags);

		$this->_setComments();

		$oShop = $this->getEntity();

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
					->dateFormat($oShop->format_date)
					->dateTimeFormat($oShop->format_datetime);

				$oShop_Item = $oComment->Shop_Item->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_item', $oShop_Item);

				// Tagged cache
				$bCache && $this->_cacheTags[] = 'shop_item_' . $oShop_Item->id;

				$oComment->clearEntities();
				$this->applyforbiddenTags($oComment);

				$oComment->addEntity($oShop_Item);

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
	 * @return Shop_Comment_Controller_Show
	 * @hostcms-event Shop_Comment_Controller_Show.onBeforeParseUrl
	 * @hostcms-event Shop_Comment_Controller_Show.onAfterParseUrl
	 */
	public function parseUrl()
	{
		Core_Event::notify(get_class($this) . '.onBeforeParseUrl', $this);

		$oShop = $this->getEntity();

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
		$this->applyForbiddenAllowedTags('/shop/comment', $oComment);
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
