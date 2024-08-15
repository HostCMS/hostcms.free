<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Value_Float_Model
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Field_Value_Float_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'field_value_float';

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
	 * Default sorting for models
	 * @var array
	 */
	/*protected $_sorting = array(
		'field_value_floats.id' => 'ASC'
	);*/

	/**
	 * Set field value
	 * @param float $value value
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = floatval($value);
		return $this;
	}

	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'field_value';

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event field_value_float.onBeforeRedeclaredGetXml
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
	 * @hostcms-event field_value_float.onBeforeRedeclaredGetStdObject
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
		$oField = $this->Field;

		$this->clearXmlTags()
			->addXmlTag('field_dir_id', $this->Field->field_dir_id)
			->addXmlTag('tag_name', $oField->tag_name);

		return $this;
	}
}