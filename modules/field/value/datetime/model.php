<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Value_Datetime_Model
 *
 * @package HostCMS
 * @subpackage Field
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Value_Datetime_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'field_value_datetime';

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'id';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'field' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'entity_id',
		'value'
	);

	/**
	 * Date format.
	 * @var string
	 */
	protected $_dateFormat = '%d.%m.%Y';

	/**
	 * Set date format
	 * @param string $dateFormat
	 * @return self
	 */
	public function dateFormat($dateFormat)
	{
		$this->_dateFormat = $dateFormat;
		return $this;
	}

	/**
	 * DateTime format.
	 * @var string
	 */
	protected $_dateTimeFormat = '%d.%m.%Y %H:%M:%S';

	/**
	 * Set DateTime format
	 * @param string $dateTimeFormat
	 * @return self
	 */
	public function dateTimeFormat($dateTimeFormat)
	{
		$this->_dateTimeFormat = $dateTimeFormat;
		return $this;
	}

	/**
	 * Set value
	 * @param string $value value
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = strval($value);
		return $this;
	}

	/**
	 * Name of tag
	 * @var string
	 */
	protected $_tagName = 'field_value';

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event field_value_datetime.onBeforeRedeclaredGetXml
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
	 * @hostcms-event field_value_datetime.onBeforeRedeclaredGetStdObject
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

		$value = '';
		if ($this->value != '0000-00-00 00:00:00')
		{
			$value = $this->Field->type == 8
				? strftime($this->_dateFormat, Core_Date::sql2timestamp($this->value))
				: strftime($this->_dateTimeFormat, Core_Date::sql2timestamp($this->value));
		}

		$this->addXmlTag('value', $value);

		return $this;
	}
}