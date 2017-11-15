<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo.
 *
 * @package HostCMS 6\Seo
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Seo_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.7';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2017-11-10';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'seo';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

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
	}
}