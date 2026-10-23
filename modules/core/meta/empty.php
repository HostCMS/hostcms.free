<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Empty Class For Apply Meta-tags templates
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Meta_Empty
{
	/**
	 * Isset
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name)
	{
		return TRUE;
	}

	/**
	 * Get
	 * @param string $name
	 * @return string
	 */
	public function __get($name)
	{
		return '';
	}

	/**
	 * Call
	 * @param string $name
	 * @param array $params
	 * @return string
	 */
	public function __call($name, $params)
	{
		return '';
	}
}