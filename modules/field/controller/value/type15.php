<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Fields. Большое целое число
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Field_Controller_Value_Type15 extends Field_Controller_Value_Type
{
	/**
	 * Model name
	 * @var string
	 */
	protected $_modelName = 'Field_Value_Bigint';

	/**
	 * Table name
	 * @var string
	 */
	protected $_tableName = 'field_value_bigints';
}