<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tag Module.
 *
 * @package HostCMS
 * @subpackage Tag
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Tag_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.1';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2026-02-10';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'tag';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 200,
				'block' => 3,
				'ico' => 'fa fa-tags',
				'name' => Core::_('Tag.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/tag/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/tag/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}