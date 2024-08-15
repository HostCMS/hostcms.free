<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg Module.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Wysiwyg_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'wysiwyg';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 0,
				'block' => -1,
				'ico' => 'fa fa-file-code-o',
			)
		);

		return parent::getMenu();
	}
}