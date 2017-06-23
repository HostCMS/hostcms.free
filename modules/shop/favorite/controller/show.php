<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ избранного.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE
 *
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
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'itemsPropertiesList'
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

		$this->itemsProperties = FALSE;
		$this->itemsPropertiesList = TRUE;

		$this->favoriteUrl = $oShop->Structure->getPath() . 'favorite/';
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
		);

		// Список свойств товаров
		if ($this->itemsPropertiesList)
		{
			$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

			$aProperties = is_array($this->itemsPropertiesList) && count($this->itemsPropertiesList)
					? $oShop_Item_Property_List->Properties->getAllByid($this->itemsPropertiesList, FALSE, 'IN')
					: $oShop_Item_Property_List->Properties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$this->_aItem_Properties[$oProperty->property_dir_id][] = $oProperty->clearEntities();

				$oShop_Item_Property = $oProperty->Shop_Item_Property;

				$oShop_Item_Property->shop_measure_id && $oProperty->addEntity(
					$oShop_Item_Property->Shop_Measure
				);
			}

			$aProperty_Dirs = $oShop_Item_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aItem_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir->clearEntities();
			}

			$Shop_Item_Properties = Core::factory('Core_Xml_Entity')
				->name('shop_item_properties');

			$this->addEntity($Shop_Item_Properties);

			$this->_addItemsPropertiesList(0, $Shop_Item_Properties);
		}

		$Shop_Favorite_Controller = $this->_getFavoriteController();

		$aShop_Favorites = $Shop_Favorite_Controller->getAll($oShop);

		foreach ($aShop_Favorites as $oShop_Favorite)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Favorite->shop_item_id);
			if (!is_null($oShop_Item->id))
			{
				$oShop_Favorite->showXmlProperties($this->itemsProperties);
				$this->addEntity($oShop_Favorite->clearEntities());
			}
			else
			{
				$oShop_Favorite->delete();
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
				?><script type="text/javascript">
				var parentNode = jQuery('script').last().parent();
				jQuery(function() {
					jQuery.ajax({
						context: parentNode,
						url: '<?php echo $this->favoriteUrl?>',
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