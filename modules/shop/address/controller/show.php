<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Выбор адреса доставки.
 *
 * Доступные методы:
 *
 * - countries(TRUE|FALSE) выводить в XML данные о странах.
 * - orderProperties(TRUE|FALSE|array()) выводить список дополнительных свойств заказа, по умолчанию TRUE.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Address_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'countries',
		'orderProperties',
	);

	/**
	 * Current user
	 * @var Siteuser_Model
	 */
	protected $_Siteuser = NULL;

	/**
	 * List of properties for order
	 * @var array
	 */
	protected $_aOrder_Properties = array();

	/**
	 * List of property directories for order
	 * @var array
	 */
	protected $_aOrder_Property_Dirs = array();

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
			$this->_Siteuser = Core_Entity::factory('Siteuser')->getCurrent();
			if ($this->_Siteuser)
			{
				$this->addEntity($this->_Siteuser->clearEntities());
			}
		}

		$this->countries = TRUE;
		$this->orderProperties = TRUE;
	}

	/**
	 * Show built data
	 * @return Core_Controller
	 * @hostcms-event Shop_Address_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oShop = $this->getEntity();

		if ($this->orderProperties)
		{
			$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $oShop->id);

			$aProperties = $oShop_Order_Property_List->Properties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$this->_aOrder_Properties[$oProperty->property_dir_id][] = $oProperty->clearEntities();

				$oShop_Order_Property = $oProperty->Shop_Order_Property;
				$oProperty->addEntity(
					Core::factory('Core_Xml_Entity')->name('prefix')->value($oShop_Order_Property->prefix)
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('display')->value($oShop_Order_Property->display)
				);
			}

			$aProperty_Dirs = $oShop_Order_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aOrder_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir->clearEntities();
			}

			// Список свойств товаров
			$Shop_Order_Properties = Core::factory('Core_Xml_Entity')
					->name('shop_order_properties');

			$this->addEntity($Shop_Order_Properties);

			$this->_addOrdersPropertiesList(0, $Shop_Order_Properties);
		}

		$this->countries && $this->addEntities(
			Core_Entity::factory('Shop_Country')->getAllByActive(1, FALSE)
		);

		if (!is_null($this->_Siteuser) && strlen($this->_Siteuser->country))
		{
			$oCurrent_Shop_Country = Core_Entity::factory('Shop_Country')->getByName($this->_Siteuser->country);
			if (!is_null($oCurrent_Shop_Country))
			{
				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('current_shop_country_id')
						->value($oCurrent_Shop_Country->id)
				);

				$oCurrent_Shop_Country_Location_Cities = Core_Entity::factory('Shop_Country_Location_City');
				$oCurrent_Shop_Country_Location_Cities->queryBuilder()
					->join('shop_country_locations', 'shop_country_locations.id', '=', 'shop_country_location_cities.shop_country_location_id')
					->where('shop_country_locations.shop_country_id', '=', $oCurrent_Shop_Country->id);

				$oCurrent_Shop_Country_Location_City = $oCurrent_Shop_Country_Location_Cities->getByName($this->_Siteuser->city);

				if (!is_null($oCurrent_Shop_Country_Location_City))
				{
					// Области
					$this->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('current_shop_country_location_id')
							->value($oCurrent_Shop_Country_Location_City->shop_country_location_id)
					);

					$this->addEntities(
						$oCurrent_Shop_Country
							->Shop_Country_Locations
							->getAllByActive(1)
					);

					// Города
					$this->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('current_shop_country_location_city_id')
							->value($oCurrent_Shop_Country_Location_City->id)
					);

					$this->addEntities(
						$oCurrent_Shop_Country_Location_City
							->Shop_Country_Location
							->Shop_Country_Location_Cities
							->getAllByActive(1)
					);
				}
			}
		}

		return parent::show();
	}

	/**
	 * Add order's properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addOrdersPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aOrder_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aOrder_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addOrdersPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aOrder_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aOrder_Properties[$parent_id]);
		}

		return $this;
	}
}