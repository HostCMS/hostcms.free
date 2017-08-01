<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * 1PS Module.
 *
 * @package HostCMS
 * @subpackage 1PS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Oneps_Module extends Core_Module
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
	public $date = '2017-07-06';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'oneps';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->menu = array(
			array(
				'sorting' => 260,
				'block' => 1,
				'ico' => 'fa fa-bell-o',
				'name' => Core::_('oneps.menu'),
				'href' => "/admin/oneps/index.php",
				'onclick' => "$.adminLoad({path: '/admin/oneps/index.php'}); return false"
			)
		);
	}
}