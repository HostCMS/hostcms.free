<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Site_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get list of languages
	 * @return array
	 */
	public function getLngList()
	{
		$queryBuilder = Core_QueryBuilder::select('lng')
			->from('sites')
			->where('lng', '!=', '')
			->groupBy('lng');

		return $queryBuilder->execute()->asAssoc()->result();
	}
}