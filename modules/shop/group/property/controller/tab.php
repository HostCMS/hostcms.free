<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties for shop group.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Group_Property_Controller_Tab extends Property_Controller_Tab
{
	/**
	 * Get properties
	 * @return array
	 */
	protected function _getProperties()
	{
		return $this->linkedObject->Properties;
	}
}