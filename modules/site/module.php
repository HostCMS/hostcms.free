<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site Module.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Site_Module extends Core_Module
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
	public $date = '2017-12-25';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'site';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->menu = array(
			array(
				'sorting' => 140,
				'block' => 3,
				'ico' => 'fa fa-globe',
				'name' => Core::_('Site.menu'),
				'href' => "/admin/site/index.php",
				'onclick' => "$.adminLoad({path: '/admin/site/index.php'}); return false"
			)
		);
	}
}