<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Value_Abstract
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Property_Value_Abstract extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'id';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'property' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'entity_id'
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	/*protected $_sorting = array(
		'property_value_floats.id' => 'ASC'
	);*/

	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'property_value';

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event modelname.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdClass
	 * @hostcms-event modelname.onBeforeRedeclaredGetStdObject
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
		$oProperty = $this->Property;

		!$oProperty->multiple && $this->addForbiddenTag('sorting');

		$this->clearXmlTags()
			->addXmlTag('property_dir_id', $oProperty->property_dir_id)
			->addXmlTag('tag_name', $oProperty->tag_name);

		return $this;
	}

	/**
	 * Convert Object to Array
	 * @return array
	 * @hostcms-event modelname.onAfterToArray
	 */
	public function toArray()
	{
		$return = parent::toArray();

		$return['__model_name'] = $this->_modelName;

		return $return;
	}
}