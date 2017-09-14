<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Company_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Company_Model extends Company_Model
{
	/**
	 * Model name, e.g. 'book' for 'Book_Model'
	 * @var mixed
	 */
	protected $_modelName = 'shop_company';
	
	/**
	 * Table name, e.g. 'books' for 'Book_Model'
	 * @var mixed
	 */
	protected $_tableName = 'companies';
	
	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'shop_company';
}