<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Seo_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.1';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2025-04-04';

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
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/seo/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/seo/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}