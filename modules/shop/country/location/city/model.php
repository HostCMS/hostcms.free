<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Country_Location_City_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Country_Location_City_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $districts = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_country_location_city_area' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_country_location' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_country_location_cities.sorting' => 'ASC',
		'shop_country_location_cities.name' => 'ASC'
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'name_en', 'name_ru', 'name_de',
		'name_fr', 'name_it', 'name_es',
		'name_pt', 'name_ua', 'name_be',
		'name_pl', 'name_lt', 'name_lv',
		'name_cz', 'name_ja', 'name'
	);
	
	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Get name depending on SITE_LNG
	 * @return string
	 */
	public function getName()
	{
		if (defined('SITE_LNG'))
		{
			$lngName = 'name_' . SITE_LNG;
			$name = isset($this->$lngName) && $this->$lngName != ''
				? $this->$lngName
				: $this->name;
		}
		else
		{
			$name = $this->name;
		}
		
		return $name;
	}
	
	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_country_location_city.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);
		
		$this->clearXmlTags()
			->addXmlTag('name', $this->getName());
			
		return parent::getXml();
	}
	
	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_country_location_city.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}
		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));
		
		$this->Shop_Country_Location_City_Areas->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}