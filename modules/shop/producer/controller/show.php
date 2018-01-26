<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ производителей магазина.
 *
 * Доступные методы:
 *
 * - dirsList(TRUE|FALSE) показывать группы производителей, по умолчанию FALSE
 * - producer($id) идентификатор производителя
 * - offset($offset) смещение, по умолчанию 0
 * - limit($limit) количество
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
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Producer_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'producer',
		'offset',
		'limit',
		'page',
		'total',
		'pattern',
		'patternParams',
		'dirsList',
	);

	/**
	 * Shop's items object
	 * @var array
	 */
	protected $_Shop_Producers = array();

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
		$this->offset = 0;
		$this->page = 0;

		$this->pattern = rawurldecode($this->getEntity()->Structure->getPath()) . 'producers/({path})(page-{page}/)';
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
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

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

		$this->dirsList && $this->addDirs();

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
			Core_Entity::factory('Shop_Producer')->getTableColums();

			// Load user BEFORE FOUND_ROWS()
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();

			$this->_Shop_Producers
				->queryBuilder()
				->sqlCalcFoundRows()
				//->where('shop_producers.active', '=', 1)
				->offset(intval($this->offset))
				->limit(intval($this->limit));

			$aShop_Producers = $this->_Shop_Producers->getAllByActive(1);

			if (!$this->producer)
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

		if ($this->limit > 0)
		{
			foreach ($aShop_Producers as $oShop_Producer)
			{
				$this->addEntity(
					$oShop_Producer->clearEntities()
				);
			}
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

		$Core_Router_Route = new Core_Router_Route($this->pattern);
		$this->patternParams = $matches = $Core_Router_Route->applyPattern(Core::$url['path']);

		if (isset($matches['page']) && $matches['page'] > 1)
		{
			$this->page($matches['page'] - 1)
				->offset($this->limit * $this->page);
		}

		$path = isset($matches['path'])
			? Core_Str::rtrimUri($matches['path'])
			: NULL;

		if ($path != '')
		{
			$aPath = explode('/', $path);

			foreach ($aPath as $sPath)
			{
				// Попытка получения группы
				$oShop_Producer = $oShop->Shop_Producers->getByPath($sPath);
				if (!is_null($oShop_Producer))
				{
					$this->producer = $oShop_Producer->id;
				}
				else
				{
					$oCore_Response = Core_Page::instance()->deleteChild()->response->status(404);

					// Если определена константа с ID страницы для 404 ошибки и она не равна нулю
					$oSite = Core_Entity::factory('Site', CURRENT_SITE);
					if ($oSite->error404)
					{
						$oStructure = Core_Entity::factory('Structure')->find($oSite->error404);

						$oCore_Page = Core_Page::instance();

						// страница с 404 ошибкой не найдена
						if (is_null($oStructure->id))
						{
							throw new Core_Exception('Group not found');
						}

						$oCore_Page->addChild($oStructure->getRelatedObjectByType());
						$oStructure->setCorePageSeo($oCore_Page);
					}
					else
					{
						if (Core::$url['path'] != '/')
						{
							// Редирект на главную страницу
							$oCore_Response->header('Location', '/');
						}
					}
					return ;
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

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