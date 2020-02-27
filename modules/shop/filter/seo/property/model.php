<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Filter_Seo_Property_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Filter_Seo_Property_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_filter_seo' => array(),
		'property' => array(),
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_filter_seo_properties.sorting' => 'ASC',
		'shop_filter_seo_properties.value' => 'ASC'
	);

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;
}