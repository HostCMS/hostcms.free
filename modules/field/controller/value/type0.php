<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Fields. Число
 *
 * @package HostCMS
 * @subpackage Field
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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