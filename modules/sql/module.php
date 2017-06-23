<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL Module.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Module extends Core_Module
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
	protected $_moduleName = 'sql';
	
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->menu = array(
			array(
				'sorting' => 270,
				'block' => 3,
				'ico' => 'fa fa-database',
				'name' => Core::_('sql.menu'),
				'href' => "/admin/sql/index.php",
				'onclick' => "$.adminLoad({path: '/admin/sql/index.php'}); return false"
			)
		);
	}
}