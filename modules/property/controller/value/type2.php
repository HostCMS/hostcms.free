<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties. Файл
 *
 * @package HostCMS
 * @subpackage Property
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Property_Controller_Value_Type2 extends Property_Controller_Value_Type
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'Property_Value_File';
	
	/**
	 * Table name
	 * @var string
	 */
	protected $_tableName = 'property_value_files';
}
