<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Update Module.
 *
 * @package HostCMS
 * @subpackage Update
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Update_Module extends Core_Module
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
	public $date = '2022-04-29';

	/**
	 * Module name
	 * @var string
	 */
	protected $_moduleName = 'update';

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
				'ico' => 'fa fa-refresh',
				'name' => Core::_('Update.menu'),
				'href' => "/admin/update/index.php",
				'onclick' => "$.adminLoad({path: '/admin/update/index.php'}); return false"
			)
		);

		return parent::getMenu();
	}

	/**
	 * Get Notification Design
	 * @param int $type
	 * @param int $entityId
	 * @return array
	 */
	public function getNotificationDesign($type, $entityId)
	{
		$iAdmin_Form_Id = 140;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

		// Контроллер формы
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
		$oAdmin_Form_Controller
			->path('/admin/update/index.php')
			->window('id_content');

		return array(
			'icon' => array(
				'ico' => 'fa fa-refresh',
				'color' => 'white',
				'background-color' => 'bg-warning'
			),
			'notification' => array(
				'ico' => 'fa-refresh',
				'background-color' => 'warning'
			),
			'href' => $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, 0, 0),
			'onclick' => $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, 0, 0),
			'extra' => array(
				'icons' => array(),
				'description' => NULL
			)
		);
	}
}