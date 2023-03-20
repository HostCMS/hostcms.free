<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Trash Module.
 *
 * @package HostCMS
 * @subpackage Trash
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Trash_Module extends Core_Module
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
	public $date = '2023-03-01';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'trash';

	/**
	 * Options
	 * @var array
	 */
	protected $_options = array(
		'maxExactCount' => array(
			'type' => 'int',
			'default' => 100000
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
				'sorting' => 260,
				'block' => 3,
				'ico' => 'fa fa-trash-o',
				'name' => Core::_('trash.menu'),
				'href' => "/admin/trash/index.php",
				'onclick' => "$.adminLoad({path: '/admin/trash/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}