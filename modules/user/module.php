<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User Module.
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Module extends Core_Module
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
	public $date = '2018-01-26';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'user';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		
		$this->menu = array(
			array(
				'sorting' => 10,
				'block' => 2,
				'ico' => 'fa fa-user',
				'name' => Core::_('User.menu'),
				'href' => "/admin/user/index.php",
				'onclick' => "$.adminLoad({path: '/admin/user/index.php'}); return false"
			)
		);
	}
}