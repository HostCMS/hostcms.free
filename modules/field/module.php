<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field Module.
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Field_Module extends Core_Module_Abstract
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
	public $date = '2024-07-09';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'field';


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
				'ico' => 'fas fa-user-cog',
				'name' => Core::_('field.menu'),
				'href' => "/admin/field/index.php",
				'onclick' => "$.adminLoad({path: '/admin/field/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}