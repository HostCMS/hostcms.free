<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ производителей магазина.
 *
 * Доступные методы:
 *
 * - dirsList(TRUE|FALSE) показывать группы производителей, по умолчанию FALSE
 * - group($id) идентификатор группы товаров, для которой необходимо выводить список производителей
 * - producer($id) идентификатор производителя
 * - offset($offset) смещение, по умолчанию 0
 * - limit($limit) количество
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/shop' Магазин
 * - '/shop/shop_producer' Производитель
 * - '/shop/shop_producer_dir' Раздел производителей
 *
 * <code>
 * $oShop_Producer_Controller_Show = new Shop_Producer_Controller_Show(
 * 	Core_Entity::factory('Shop', 1)
 * );
 *
 * $oShop_Producer_Controller_Show
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('МагазинСписокПроизводителей')
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
class Shop_Producer_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'producer',
		'group',
		'offset',
		'limit',
		'page',
		'total',
		'pattern',
		'patternExpressions',
		'patternParams',
		'dirsList',
		'url'
	);

	/**
	 * Shop's items object
	 * @var Shop_Producer_Model
	 */
	protected $_Shop_Producers;

	/**
	 * List of dirs of producers
	 * @var array
	 */
	protected $_aShop_Producer_Dirs = array();

	/**
	 * Shop producer dirs object
	 * @var Shop_Group_Model
	 */
	protected $_Shop_Producer_Dirs = NULL;

	/**
	 * Array of siteuser's groups allowed for current siteuser
	 * @var array
	 */
	protected $_aSiteuserGroups = array();

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		$this->_setShopProducerDirs();

		$this->_Shop_Producers = $oShop->Shop_Producers;

		$this->_Shop_Producers
			->queryBuilder()
			->select('shop_producers.*');

		$this->dirsList = FALSE;

		$this->producer = NULL;
		$this->offset = $this->page = 0;

		$this->_aSiteuserGroups = $this->_getSiteuserGroups();

		// Named subpatterns {name} can consist of up to 32 alphanumeric characters and underscores, but must start with a non-digit.
		$this->pattern = rawurldecode($this->getEntity()->Producer_Structure->getPath()) . '({path})(page-{page}/)';

		$this->patternExpressions = array(
			'page' => '\d+',
		);

		$this->url = Core::$url['path'];
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
	 * Set dirs's conditions
	 * @return self
	 */
	protected function _setShopProducerDirs()
	{
		$oShop = $this->getEntity();

		$this->_Shop_Producer_Dirs = $oShop->Shop_Producer_Dirs;
		$this->_Shop_Producer_Dirs
			->queryBuilder()
			->clearOrderBy()
			->orderBy('shop_producer_dirs.sorting', 'ASC')
			->orderBy('shop_producer_dirs.name', 'ASC');

		return $this;
	}

	/**
	 * Get producers
	 * @return array
	 */
	public function shopProducers()
	{
		return $this->_Shop_Producers;
	}

	/**
	 * Get producer dirs
	 * @return array
	 */
	public function shopProducerDirs()
	{
		return $this->_Shop_Producer_Dirs;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Producer_Controller_Show.onBeforeRedeclaredShow
	 * @hostcms-event Shop_Controller_Show.onAfterAddShopProducers
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$bTpl = $this->_mode == 'tpl';

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('page')
				->value(intval($this->page))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		);

		$this->dirsList && $this->addDirs();

		if ($bTpl)
		{
			$this->assign('controller', $this);
			$this->assign('aShop_Producers', array());
		}

		// До вывода свойств групп
		if ($this->limit > 0)
		{
			// Товары
			if ($this->producer)
			{
				$this->_Shop_Producers
					->queryBuilder()
					->where('shop_producers.id', '=', intval($this->producer));
			}

			// Load model columns BEFORE FOUND_ROWS()
			Core_Entity::factory('Shop_Producer')->getTableColumns();

			// Load user BEFORE FOUND_ROWS()
			$oUserCurrent = Core_Auth::getCurrentUser();

			$this->_Shop_Producers
				->queryBuilder()
				->sqlCalcFoundRows()
				->where('shop_producers.active', '=', 1)
				->offset(intval($this->offset))
				->limit(intval($this->limit));

			if ($this->group)
			{
				$dateTime = Core_Date::timestamp2sql(time());

				$this->_Shop_Producers
					->queryBuilder()
					->select('shop_producers.*')
					->distinct()
					->join('shop_items', 'shop_items.shop_producer_id', '=', 'shop_producers.id')
					->where('shop_items.shop_group_id', '=', $this->group)
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
					->where('shop_items.deleted', '=', 0);
			}

			$aShop_Producers = $this->_Shop_Producers->findAll();

			if (!$this->producer)
			{
				$this->total = Core_QueryBuilder::select()->getFoundRows();

				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('total')
						->value(intval($this->total))
				);
			}

			foreach ($aShop_Producers as $oShop_Producer)
			{
				if (!$bTpl)
				{
					$oShop_Producer->clearEntities();
					$this->applyForbiddenAllowedTags('/shop/shop_producer', $oShop_Producer);
					$this->addEntity($oShop_Producer);
				}
				else
				{
					$this->append('aShop_Producers', $oShop_Producer);
				}
			}

			Core_Event::notify(get_class($this) . '.onAfterAddShopProducers', $this, array($aShop_Producers));
		}

		return parent::show();
	}

	/**
	 * Parse URL and set controller properties
	 * @return Shop_Producer_Controller_Show
	 * @hostcms-event Shop_Controller_Show.onBeforeParseUrl
	 * @hostcms-event Shop_Controller_Show.onAfterParseUrl
	 */
	public function parseUrl()
	{
		Core_Event::notify(get_class($this) . '.onBeforeParseUrl', $this);

		$oShop = $this->getEntity();

		$Core_Router_Route = new Core_Router_Route($this->pattern, $this->patternExpressions);
		$this->patternParams = $matches = $Core_Router_Route->applyPattern($this->url);

		if (isset($matches['page']) && $matches['page'] > 1)
		{
			$this->page($matches['page'] - 1)
				->offset($this->limit * $this->page);
		}

		$path = isset($matches['path']) && $matches['path'] != '/'
			? Core_Str::rtrimUri($matches['path'])
			: NULL;

		if ($path != '')
		{
			$aPath = explode('/', $path);

			foreach ($aPath as $sPath)
			{
				// Попытка получения группы
				$oShop_Producer = $oShop->Shop_Producers->getByPath($sPath);

				if (!is_null($oShop_Producer) && $oShop_Producer->active)
				{
					$this->producer = $oShop_Producer->id;
				}
				else
				{
					return $this->error410();
				}
			}
		}
		elseif (is_null($path))
		{
			return $this->error404();
		}

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

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
	 * Add all dirs to XML
	 * @return self
	 */
	public function addDirs()
	{
		$this->_aShop_Producer_Dirs = array();

		$aShop_Producer_Dirs = $this->_Shop_Producer_Dirs->findAll();

		foreach ($aShop_Producer_Dirs as $oShop_Producer_Dir)
		{
			$oShop_Producer_Dir->clearEntities();
			$this->applyForbiddenAllowedTags('/shop/shop_producer_dir', $oShop_Producer_Dir);
			$this->_aShop_Producer_Dirs[$oShop_Producer_Dir->parent_id][] = $oShop_Producer_Dir;
		}

		$this->_addDirsByParentId(0, $this);

		return $this;
	}

	/**
	 * Add dirs by parent to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addDirsByParentId($parent_id, $parentObject)
	{
		if (isset($this->_aShop_Producer_Dirs[$parent_id]))
		{
			foreach ($this->_aShop_Producer_Dirs[$parent_id] as $oShop_Producer_Dir)
			{
				$parentObject->addEntity($oShop_Producer_Dir);
				$this->_addDirsByParentId($oShop_Producer_Dir->id, $oShop_Producer_Dir);
			}
		}

		return $this;
	}
}