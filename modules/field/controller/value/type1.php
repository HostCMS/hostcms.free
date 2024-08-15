<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Fields. Строка
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Field_Controller_Value_Type1 extends Field_Controller_Value_Type
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'Field_Value_String';

	/**
	 * Table name
	 * @var string
	 */
	protected $_tableName = 'field_value_strings';
}