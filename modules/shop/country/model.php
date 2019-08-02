<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Country_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Country_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $locations = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop' => array(),
		'shop_country_location' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
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
		'shop_countries.sorting' => 'ASC',
		'shop_countries.name' => 'ASC'
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
	 * @hostcms-event shop_country.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_country.onBeforeRedeclaredGetStdObject
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
	public function locationsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Country_Locations->getCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-ico badge-azure white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Change active status
	 * @return self
	 * @hostcms-event shop_country.onBeforeChangeActive
	 * @hostcms-event shop_country.onAfterChangeActive
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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_country.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}
		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Country_Locations->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}