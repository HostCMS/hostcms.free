<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Value_Float_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Property_Value_Float_Model extends Property_Value_Abstract
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'property_value_float';

	/**
	 * Set property value
	 * @param float $value value
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = floatval($value);
		return $this;
	}
}