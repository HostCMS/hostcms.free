<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.9';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2021-05-25';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'seo';

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