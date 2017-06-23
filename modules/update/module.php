<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Update Module.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Update_Module extends Core_Module
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
	public $date = '2017-06-14';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'update';
	
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
				'ico' => 'fa fa-refresh',
				'name' => Core::_('Update.menu'),
				'href' => "/admin/update/index.php",
				'onclick' => "$.adminLoad({path: '/admin/update/index.php'}); return false"
			)
		);
	}
}