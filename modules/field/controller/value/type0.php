<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Fields. Число
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Field_Controller_Value_Type0 extends Field_Controller_Value_Type
{
	/**
	 * Model name
	 * @var string
	 */
	protected $_modelName = 'Field_Value_Int';

	/**
	 * Table name
	 * @var string
	 */
	protected $_tableName = 'field_value_ints';
}