<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount Module.
 *
 * Типы документов:
 * 70 - Chartaccount_Operation_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Chartaccount_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'chartaccount';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		Core_Skin::instance()->addJs('/modules/chartaccount/assets/chartaccount.js');
	}

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 50,
				'block' => 1,
				'ico' => 'fa-solid fa-file-invoice',
				'name' => Core::_('Chartaccount.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/chartaccount/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/chartaccount/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}