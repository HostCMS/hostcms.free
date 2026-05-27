<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Syntaxhighlighter Module.
 *
 * @package HostCMS
 * @subpackage Syntaxhighlighter
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Syntaxhighlighter_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'syntaxhighlighter';

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
				'ico' => 'fa-solid fa-highlighter',
				'name' => Core::_('Syntaxhighlighter.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/syntaxhighlighter/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/syntaxhighlighter/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}