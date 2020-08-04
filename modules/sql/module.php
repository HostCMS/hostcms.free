<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL Module.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Module extends Core_Module
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
	public $date = '2020-08-04';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'sql';
	
	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
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

		return parent::getMenu();
	}
}