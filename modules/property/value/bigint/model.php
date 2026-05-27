<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property_Value_Bigint_Model
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Property_Value_Bigint_Model extends Property_Value_Abstract
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'property_value_bigint';

	/**
	 * Set property value
	 * @param int $value value
	 * @return self
	 */
	public function setValue($value)
	{
		$this->value = intval($value);
		return $this;
	}
}