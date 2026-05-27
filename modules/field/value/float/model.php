<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Value_Float_Model
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Field_Value_Float_Model extends Field_Value_Abstract
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'field_value_float';

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
}