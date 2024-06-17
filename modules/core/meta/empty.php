<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Empty Class For Apply Meta-tags templates
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Meta_Empty
{
	public function __isset($name)
	{
		return TRUE;
	}

	public function __get($name)
	{
		return '';
	}
}