<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template Module.
 *
 * @package HostCMS
 * @subpackage Template
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Module extends Core_Module
{
	/**
	 * Module version
	 * @var string
	 */
	public $version = '6.8';

	/**
	 * Module date
	 * @var date
	 */
	public $date = '2018-04-24';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'template';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		if (Core_Auth::logged())
		{
			Core_Router::add('template-section-lib.php', '/template-section-lib.php')
				->controller('Template_Section_Lib_Command_Controller');

			Core_Router::add('template-section.php', '/template-section.php')
				->controller('Template_Section_Command_Controller');

			Core_Router::add('template-less.php', '/template-less.php')
				->controller('Template_Less_Command_Controller');
		}
	}

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 70,
				'block' => 0,
				'ico' => 'fa fa-th',
				'name' => Core::_('template.menu'),
				'href' => "/admin/template/index.php",
				'onclick' => "$.adminLoad({path: '/admin/template/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}
}