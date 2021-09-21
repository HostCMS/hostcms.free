<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ продавцов магазина.
 *
 * Доступные методы:
 *
 * - seller($id) идентификатор продавца
 * - offset($offset) смещение, по умолчанию 0
 * - limit($limit) количество
 *
 * <code>
 * $Shop_Seller_Controller_Show = new Shop_Seller_Controller_Show(
 * 	Core_Entity::factory('Shop', 1)
 * );
 *
 * $Shop_Seller_Controller_Show
 * 	->xsl(
 * 		Core_Entity::factory('Xsl')->getByName('МагазинСписокПродавцов')
 * 	)
 * 	->limit(5)
 * 	->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Seller_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'seller',
		'offset',
		'limit',
		'page',
		'total',
		'pattern',
		'patternParams',
	);

	/**
	 * Shop's items object
	 * @var array
	 */
	protected $_Shop_Sellers = array();

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		$this->_Shop_Sellers = $oShop->Shop_Sellers;

		$this->_Shop_Sellers
			->queryBuilder()
			->select('shop_sellers.*');

		$this->seller = NULL;
		$this->offset = 0;
		$this->page = 0;

		$this->pattern = rawurldecode($this->getEntity()->Structure->getPath()) . 'sellers/({path})(page-{page}/)';
	}

	/**
	 * Get sellers
	 * @return array
	 */
	public function shopSellers()
	{
		return $this->_Shop_Sellers;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Seller_Controller_Show.onBeforeRedeclaredShow
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

		// До вывода свойств групп
		if ($this->limit > 0)
		{
			// Товары
			if ($this->seller)
			{
				$this->_Shop_Sellers
					->queryBuilder()
					->where('shop_sellers.id', '=', intval($this->seller));
			}

			// Load model columns BEFORE FOUND_ROWS()
			Core_Entity::factory('Shop_Seller')->getTableColumns();

			// Load user BEFORE FOUND_ROWS()
			$oUserCurrent = Core_Auth::getCurrentUser();

			$this->_Shop_Sellers
				->queryBuilder()
				->sqlCalcFoundRows()
				//->where('shop_sellers.active', '=', 1)
				->offset(intval($this->offset))
				->limit(intval($this->limit));

			$aShop_Sellers = $this->_Shop_Sellers->findAll();

			if (!$this->seller)
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
			foreach ($aShop_Sellers as $oShop_Seller)
			{
				//echo "<br>=", $oShop_Seller->id;
				$oShop_Seller->clearEntities();

				$this->addEntity($oShop_Seller);
			}
		}

		return parent::show();
	}

	/**
	 * Parse URL and set controller properties
	 * @return Shop_Seller_ontroller_Show
	 * @hostcms-event Shop_Seller_ontroller_Show.onBeforeParseUrl
	 * @hostcms-event Shop_Seller_ontroller_Show.onAfterParseUrl
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
				$oShop_Seller = $oShop->Shop_Sellers->getByPath($sPath);
				if (!is_null($oShop_Seller))
				{
					$this->seller = $oShop_Seller->id;
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
					return $this;
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

		return $this;
	}
}