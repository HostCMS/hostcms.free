<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ комментариев заказов магазина.
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
 * - commentsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств комментариев, по умолчанию TRUE. Ограничения на список свойств в виде массива влияет и на выборку значений свойств товара.
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
 * - '/shop/shop_order' Заказ
 *
 * <code>
 * $Shop_Order_Comment_Controller_Show = new Shop_Order_Comment_Controller_Show(
 * 	Core_Entity::factory('Shop', 1)
 * );
 *
 * $Shop_Order_Comment_Controller_Show
 * 	->xsl('СписокКомментариевЗаказовНаГлавной')
 * 	->limit(5)
 * 	->commentsProperties(TRUE)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Order_Comment_Controller_Show extends Core_Controller
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
	protected $_cacheName = 'shop_order_comment_show';

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
	 * @param Shop_Model $oShop information system
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		// Named subpatterns {name} can consist of up to 32 alphanumeric characters and underscores, but must start with a non-digit.
		$this->pattern = rawurldecode(Core_Str::rtrimUri(Core_Page::instance()->structure->getPath())) . '/comments({path}/)(page-{page}/)';

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
		$oShop = $this->getEntity();

		$this->_Comments = Core_Entity::factory('Comment');
		$this->_Comments->queryBuilder()
			->straightJoin()
			->join('comment_shop_orders', 'comments.id', '=', 'comment_shop_orders.comment_id')
			->join('shop_orders', 'comment_shop_orders.shop_order_id', '=', 'shop_orders.id')
			->where('shop_orders.shop_id', '=', $oShop->id)
			->where('shop_orders.deleted', '=', 0)
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
	 * @return Shop_Order_Model
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

		// Показывать дополнительные свойства комментариев
		if ($this->commentsProperties || $this->commentsPropertiesList)
		{
			$aShowCommentPropertyIDs = $this->_commentsProperties();
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

			$this->_shownIDs = array();

			foreach ($aComments as $oComment)
			{
				$oComment
					->dateFormat($oShop->format_date)
					->dateTimeFormat($oShop->format_datetime);

				$oShop_Order = $oComment->Shop_Order->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_order', $oShop_Order);

				// Tagged cache
				$bCache && $this->_cacheTags[] = 'shop_order_' . $oShop_Order->id;

				$oComment->clearEntities();
				$this->applyforbiddenTags($oComment);

				$oComment->showXmlProperties($mShowCommentPropertyIDs);

				$oComment->addEntity($oShop_Order);

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
	 * @return Shop_Order_Comment_Controller_Show
	 * @hostcms-event Shop_Order_Comment_Controller_Show.onBeforeParseUrl
	 * @hostcms-event Shop_Order_Comment_Controller_Show.onAfterParseUrl
	 */
	public function parseUrl()
	{
		Core_Event::notify(get_class($this) . '.onBeforeParseUrl', $this);

		$oShop = $this->getEntity();

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
	 * Add list of comment properties
	 * @return array
	 * @hostcms-event Shop_Controller_Show.onBeforeAddCommentsPropertiesList
	 */
	protected function _commentsProperties()
	{
		$aShowPropertyIDs = array();

		$oShop = $this->getEntity();

		$oShop_Order_Comment_Property_List = Core_Entity::factory('Shop_Order_Comment_Property_List', $oShop->id);

		$bTpl = $this->_mode == 'tpl';

		$aProperties = is_array($this->commentsPropertiesList) && count($this->commentsPropertiesList)
			? $oShop_Order_Comment_Property_List->Properties->getAllByid($this->commentsPropertiesList, FALSE, 'IN')
			: $oShop_Order_Comment_Property_List->Properties->findAll();

		foreach ($aProperties as $oProperty)
		{
			$oProperty->clearEntities();
			$aShowPropertyIDs[] = $oProperty->id;
			$this->_aComment_Properties[$oProperty->property_dir_id][] = $oProperty;
		}

		// Список свойств комментариев
		if ($this->commentsPropertiesList)
		{
			$aProperty_Dirs = $oShop_Order_Comment_Property_List->Property_Dirs->findAll();
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
