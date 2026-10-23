<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * IP Address Module.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Ipaddress_Module extends Core_Module_Abstract
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
	public $date = '2026-10-22';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'ipaddress';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		Core_Skin::instance()->addJs('/modules/ipaddress/assets/ipaddress.js');
		Core_Skin::instance()->addCss('/modules/ipaddress/assets/ipaddress.css');
	}
	
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
				'ico' => 'fa-solid fa-link',
				'name' => Core::_('ipaddress.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/ipaddress/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/ipaddress/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}