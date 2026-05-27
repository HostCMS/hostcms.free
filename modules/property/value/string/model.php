<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Value_String_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Property_Value_String_Model extends Property_Value_Abstract
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'property_value_string';

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
}