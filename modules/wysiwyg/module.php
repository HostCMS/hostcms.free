<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg Module.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Wysiwyg_Module extends Core_Module_Abstract
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '7.1';

	/**
	 * Module date
	 * @var string
	 */
	public $date = '2026-05-12';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'wysiwyg';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 150,
				'block' => 3,
				'ico' => 'fa-regular fa-file-code',
				'name' => Core::_('Wysiwyg.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/wysiwyg/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/wysiwyg/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}