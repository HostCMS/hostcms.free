<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Value_String_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Property_Value_String_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'property_value_string';

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
	 * Set property value
	 * @param string $value value
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = strval($value);
		return $this;
	}

	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'property_value';

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event property_value_string.onBeforeRedeclaredGetXml
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
	 * @hostcms-event property_value_string.onBeforeRedeclaredGetStdObject
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
			->addXmlTag('property_dir_id', $this->Property->property_dir_id)
			->addXmlTag('tag_name', $this->Property->tag_name);

		return $this;
	}

	/**
	 * Get entity description
	 * @return string
	 */
	public function getTrashDescription()
	{
		return htmlspecialchars(
			Core_Str::cut($this->value, 255)
		);
	}
}