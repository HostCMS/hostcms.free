<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Value_Abstract
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Field_Value_Abstract extends Core_Entity
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
		'field' => array(),
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'entity_id'
	);

	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'field_value';

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
		$this->clearXmlTags()
			->addXmlTag('field_dir_id', $this->Field->field_dir_id)
			->addXmlTag('tag_name', $this->Field->tag_name);

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