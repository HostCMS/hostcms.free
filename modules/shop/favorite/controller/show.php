<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ избранного.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE
 * - sortPropertiesValues(TRUE|FALSE) сортировать значения дополнительных свойств, по умолчанию TRUE.
 * - favoriteList(TRUE|FALSE) показывать списки избранных товаров, по умолчанию TRUE.
 * - modifications(TRUE|FALSE) показывать модификации для выбранных товаров, по умолчанию FALSE
 * - specialprices(TRUE|FALSE) показывать специальные цены для выбранных товаров, по умолчанию FALSE
 * - commentsRating(TRUE|FALSE) показывать оценки комментариев для выбранных товаров, по умолчанию FALSE
 * - itemsMedia(TRUE|FALSE) выводить значения библиотеки файлов для товаров, по умолчанию FALSE
 * - cart(TRUE|FALSE) выводить товары в корзине, по умолчанию FALSE
 * - sets(TRUE|FALSE) показывать состав комплектов товаров, по умолчанию TRUE
 * - limit($limit) количество
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 *
 * Доступные свойства:
 *
 * - total количество товаров в избранном
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/shop' Магазин
 * - '/shop/shop_item_properties/property' Свойство в списке свойств товара
 * - '/shop/shop_item_properties/property_dir' Раздел свойств в списке свойств товара
 * - '/shop/shop_favorite_list' Списки избранного
 * - '/shop/shop_favorite' Избранное
 *
 * <code>
 * $Shop_Favorite_Controller_Show = new Shop_Favorite_Controller_Show(
 * 		Core_Entity::factory('Shop', 1)
 * 	);
 *
 * 	$Shop_Favorite_Controller_Show
 * 		->xsl(
 * 			Core_Entity::factory('Xsl')->getByName('МагазинИзбранное')
 * 		)
 * 		->limit(5)
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Favorite_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'favoriteUrl',
		'itemsProperties',
		'itemsPropertiesList',
		'sortPropertiesValues',
		'favoriteList',
		'modifications',
		'specialprices',
		'commentsRating',
		'itemsMedia',
		'cart',
		'sets',
		'offset',
		'page',
		'total',
		'pattern',
		'patternParams',
		'limit',
		'url'
	);

	/**
	 * List of properties for item
	 * @var array
	 */
	protected $_aItem_Properties = array();

	/**
	 * List of property directories for item
	 * @var array
	 */
	protected $_aItem_Property_Dirs = array();

	/**
	 * Current Siteuser
	 * @var Siteuser_Model|NULL
	 */
	protected $_oSiteuser = NULL;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		if (Core::moduleIsActive('siteuser'))
		{
			// Если есть модуль пользователей сайта, $siteuser_id равен 0 или ID авторизованного
			$this->_oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_id')
				->value($this->_oSiteuser ? $this->_oSiteuser->id : 0)
		);

		$this->itemsProperties = $this->modifications = $this->specialprices = $this->commentsRating = $this->itemsMedia = $this->cart = FALSE;
		$this->itemsPropertiesList = $this->sortPropertiesValues = $this->favoriteList = $this->sets = TRUE;
		$this->limit = 10;

		$this->offset = $this->page = 0;

		$this->favoriteUrl = $oShop->Structure->getPath() . 'favorite/';

		// Named subpatterns {name} can consist of up to 32 alphanumeric characters and underscores, but must start with a non-digit.
		$this->pattern = rawurldecode($this->getEntity()->Structure->getPath()) . '({path})(page-{page}/)';

		$this->url = Core::$url['path'];
	}

	public function parseUrl()
	{
		// $oShop = $this->getEntity();

		$Core_Router_Route = new Core_Router_Route($this->pattern);
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
				return $this->error404();
			}
		}
	}

	/**
	 * Get Shop_Favorite_Controller
	 * @return Shop_Favorite_Controller
	 */
	protected function _getFavoriteController()
	{
		return Shop_Favorite_Controller::instance();
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Favorite_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oShop = $this->getEntity();

		// Активность модуля "Пользователи сайта"
		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_exists')
				->value(Core::moduleIsActive('siteuser') ? 1 : 0)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('limit')
				->value(intval($this->limit))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('page')
				->value(intval($this->page)));

		// Список свойств товаров
		if ($this->itemsPropertiesList)
		{
			$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

			$aProperties = is_array($this->itemsPropertiesList) && count($this->itemsPropertiesList)
					? $oShop_Item_Property_List->Properties->getAllByid($this->itemsPropertiesList, FALSE, 'IN')
					: $oShop_Item_Property_List->Properties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$oProperty->clearEntities();
				$this->_aItem_Properties[$oProperty->property_dir_id][] = $oProperty;

				$this->applyForbiddenAllowedTags('/shop/shop_item_properties/property', $oProperty);

				$oShop_Item_Property = $oProperty->Shop_Item_Property;

				$oShop_Item_Property->shop_measure_id && $oProperty->addEntity(
					$oShop_Item_Property->Shop_Measure
				);
			}

			$aProperty_Dirs = $oShop_Item_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_item_properties/property_dir', $oProperty_Dir);
				$this->_aItem_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			$Shop_Item_Properties = Core::factory('Core_Xml_Entity')
				->name('shop_item_properties');

			$this->addEntity($Shop_Item_Properties);

			$this->_addItemsPropertiesList(0, $Shop_Item_Properties);
		}

		if ($this->favoriteList && $this->_oSiteuser)
		{
			$aShop_Favorite_Lists = $oShop->Shop_Favorite_Lists->getAllBySiteuser_id($this->_oSiteuser->id, FALSE);

			foreach ($aShop_Favorite_Lists as $oShop_Favorite_List)
			{
				$oShop_Favorite_List->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_favorite_list', $oShop_Favorite_List);
				$this->addEntity($oShop_Favorite_List);
			}
		}

		// Товары в корзине
		if ($this->cart)
		{
			// Проверяем наличие товара в корзины
			$Shop_Cart_Controller = Shop_Cart_Controller::instance();
			$aShop_Cart = $Shop_Cart_Controller->getAll($oShop);

			if (count($aShop_Cart))
			{
				$this->addEntity(
					$oCartEntity = Core::factory('Core_Xml_Entity')
						->name('items_in_cart')
				);

				foreach ($aShop_Cart as $oShop_Cart)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Cart->shop_item_id);
					if (!is_null($oShop_Item->id) && $oShop_Item->active)
					{
						$oShop_Item_Into_Cart = clone $oShop_Item;
						$oShop_Item_Into_Cart->id($oShop_Item->id);

						Core_Event::notify(get_class($this).'.onBeforeAddCartItem', $this, array($oShop_Item_Into_Cart, $oShop_Cart));

						//$this->applyItemsForbiddenTags($oShop_Item_Into_Cart->clearEntities());
						$this->applyForbiddenAllowedTags('/shop/items_in_cart/shop_item|/shop/shop_item', $oShop_Item_Into_Cart);

						$this->itemsProperties
							&& $oShop_Item_Into_Cart->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues);

						// Media
						$this->itemsMedia
							&& $oShop_Item_Into_Cart->showXmlMedia($this->itemsMedia);

						!$this->sets && $oShop_Item_Into_Cart->showXmlSets($this->sets);

						// $oShop_Item_Into_Cart->itemsActivity($this->itemsActivity);

						$oShop_Item_Into_Cart->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('quantity')
								->value($oShop_Cart->quantity)
						);

						$oCartEntity->addEntity($oShop_Item_Into_Cart);
					}
				}
			}
		}

		$this->total = 0;

		if ($this->limit > 0)
		{
			$Shop_Favorite_Controller = $this->_getFavoriteController();

			$aShop_Favorites = $Shop_Favorite_Controller->getAll($oShop);

			$this->total = count($aShop_Favorites);

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('total')
					->value($this->total)
			);

			$aShop_Favorites = array_slice($aShop_Favorites, $this->offset, $this->limit);

			foreach ($aShop_Favorites as $oShop_Favorite)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Favorite->shop_item_id);
				if (!is_null($oShop_Item->id))
				{
					$oShop_Favorite
						->clearEntities()
						->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues)
						->showXmlModifications($this->modifications)
						->showXmlSpecialprices($this->specialprices)
						->showXmlCommentsRating($this->commentsRating);

					// Media
					$this->itemsMedia
						&& $oShop_Item->showXmlMedia($this->itemsMedia);

					!$this->sets && $oShop_Item->showXmlSets($this->sets);

					$this->applyForbiddenAllowedTags('/shop/shop_favorite', $oShop_Favorite);

					Core_Event::notify(get_class($this) . '.onBeforeAddShopFavorite', $this, array($oShop_Favorite, $oShop_Item));

					$this->addEntity($oShop_Favorite);
				}
				else
				{
					$oShop_Favorite->delete();
				}
			}
		}

		return parent::show();
	}

	/**
	 * AJAX refresh favorites
	 * @return self
	 */
	public function refreshFavorite()
	{
		if (Core::moduleIsActive('cache'))
		{
			$oShop = $this->getEntity();

			if ($oShop->Site->html_cache_use)
			{
				?><script>
				var parentNode = jQuery('script').last().parent();
				jQuery(function() {
					jQuery.ajax({
						context: parentNode,
						url: '<?php echo Core_Str::escapeJavascriptVariable($this->favoriteUrl)?>',
						type: 'POST',
						dataType: 'json',
						data: {'_': Math.round(new Date().getTime()), 'loadFavorite': 1},
						success: function (ajaxData) {
							jQuery(this).html(ajaxData);
						},
						error: function (){return false}
					});
				});
				</script><?php
			}
		}

		return $this;
	}

	/**
	 * Add items properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addItemsPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aItem_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aItem_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addItemsPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aItem_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aItem_Properties[$parent_id]);
		}

		return $this;
	}
}