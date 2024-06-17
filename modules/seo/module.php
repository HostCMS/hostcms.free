<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Seo_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.0';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2024-06-06';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'seo';

	/**
	 * Options
	 * @var array
	 */
	protected $_options = array(
		'topQueriesLimit' => array(
			'type' => 'int',
			'default' => 10
		),
		'topPagesLimit' => array(
			'type' => 'int',
			'default' => 10
		)
	);

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 150,
				'block' => 3,
				'ico' => 'fa fa-bullseye',
				'name' => Core::_('Seo.menu'),
				'href' => "/admin/seo/index.php",
				'onclick' => "$.adminLoad({path: '/admin/seo/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}