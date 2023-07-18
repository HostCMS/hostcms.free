<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Empty Class For Apply Meta-tags templates
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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