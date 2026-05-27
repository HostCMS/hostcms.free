<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Value_Datetime_Model
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Field_Value_Datetime_Model extends Field_Value_Abstract
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'field_value_datetime';

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
				? Core_Date::strftime($this->_dateFormat, Core_Date::sql2timestamp($this->value))
				: Core_Date::strftime($this->_dateTimeFormat, Core_Date::sql2timestamp($this->value));
		}

		$this->addXmlTag('value', $value);

		return $this;
	}
}