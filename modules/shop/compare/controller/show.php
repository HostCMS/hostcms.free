<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ сравнения.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE
 * - modifications(TRUE|FALSE) показывать модификации для выбранных товаров, по умолчанию FALSE
 * - commentsRating(TRUE|FALSE) показывать оценки комментариев для выбранных товаров, по умолчанию FALSE
 * - limit($limit) количество
 *
 * Доступные свойства:
 *
 * - total количество товаров в сравнении
 *
 * <code>
 * $Shop_Compare_Controller_Show = new Shop_Compare_Controller_Show(
 * 		Core_Entity::factory('Shop', 1)
 * 	);
 *
 * 	$Shop_Compare_Controller_Show
 * 		->xsl(
 * 			Core_Entity::factory('Xsl')->getByName('МагазинИзбранное')
 * 		)
 * 		->limit(5)
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Compare_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'compareUrl',
		'itemsProperties',
		'itemsPropertiesList',
		'modifications',
		'commentsRating',
		'offset',
		'page',
		'total',
		'pattern',
		'patternParams',
		'limit'
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

		$this->itemsProperties = $this->modifications = $this->commentsRating = FALSE;
		$this->itemsPropertiesList = TRUE;
		$this->limit = 10;

		$this->offset = $this->page = 0;

		$this->compareUrl = $oShop->Structure->getPath() . 'compare/';

		$this->pattern = rawurldecode($this->getEntity()->Structure->getPath()) . '({path})(page-{page}/)';
	}

	public function parseUrl()
	{
		$oShop = $this->getEntity();

		$Core_Router_Route = new Core_Router_Route($this->pattern);
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
	}

	/**
	 * Get Shop_Compare_Controller
	 * @return Shop_Compare_Controller
	 */
	protected function _getCompareController()
	{
		return Shop_Compare_Controller::instance();
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Compare_Controller_Show.onBeforeRedeclaredShow
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

		$this->total = 0;

		if ($this->limit > 0)
		{
			$Shop_Compare_Controller = $this->_getCompareController();

			$aShop_Compares = $Shop_Compare_Controller->getAll($oShop);

			$this->total = count($aShop_Compares);

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('total')
					->value($this->total)
			);

			$aShop_Compares = array_slice($aShop_Compares, $this->offset, $this->limit);

			foreach ($aShop_Compares as $oShop_Compare)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Compare->shop_item_id);
				if (!is_null($oShop_Item->id))
				{
					$oShop_Compare
						->showXmlProperties($this->itemsProperties)
						->showXmlModifications($this->modifications)
						->showXmlCommentsRating($this->commentsRating);

					$this->addEntity($oShop_Compare->clearEntities());
				}
				else
				{
					$oShop_Compare->delete();
				}
			}
		}

		return parent::show();
	}

	/**
	 * AJAX refresh compare
	 * @return self
	 */
	public function refreshCompare()
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
						url: '<?php echo Core_Str::escapeJavascriptVariable($this->compareUrl)?>',
						type: 'POST',
						dataType: 'json',
						data: {'_': Math.round(new Date().getTime()), 'loadCompare': 1},
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