<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Country_Location_City_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'deleted',
		'user_id',
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

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
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

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event shop_country_location_city.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addXmlTag('name', $this->getName());

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function districtsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Country_Location_City_Areas->getCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-ico badge-azure white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')->value(
			htmlspecialchars($this->name)
		);

		if (!$this->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Change active status
	 * @return self
	 * @hostcms-event shop_country_location_city.onBeforeChangeActive
	 * @hostcms-event shop_country_location_city.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Turn on active status
	 * @return self
	 */
	public function turnOn()
	{
		$this->active = 1;
		$this->save();

		return $this;
	}

	/**
	 * Switch off active status
	 * @return self
	 */
	public function switchOff()
	{
		$this->active = 0;
		$this->save();

		return $this;
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