<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties. Большое целое число
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Property_Controller_Value_Type15 extends Property_Controller_Value_Type
{
	/**
	 * Model name
	 * @var string
	 */
	protected $_modelName = 'Property_Value_Bigint';
	
	/**
	 * Table name
	 * @var string
	 */
	protected $_tableName = 'property_value_bigints';
}
