<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field Module.
 *
 * @package HostCMS
 * @subpackage Field
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Module extends Core_Module
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
	public $date = '2021-08-23';

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