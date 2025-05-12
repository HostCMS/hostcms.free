<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Module
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Company_Module extends Core_Module_Abstract
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
	public $date = '2025-04-04';


	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'company';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 140,
				'block' => 3,
				'ico' => 'fa fa-building-o',
				'name' => Core::_('Company.model_name'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/company/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/company/index.php'}); return false")
			)
		);

		return parent::getMenu();
	}
}