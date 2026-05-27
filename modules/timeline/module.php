<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Timeline_Module.
 *
 * @package HostCMS
 * @subpackage Timeline
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Timeline_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'timeline';

	/**
	 * Get Module's Menu
	 * @return array
	 */
	public function getMenu()
	{
		$this->menu = array(
			array(
				'sorting' => 100,
				'block' => 0,
				'ico' => 'fa-solid fa-bars-staggered',
				'name' => Core::_('Timeline.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/timeline/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/timeline/index.php'}); return false")
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
		// Идентификатор формы "Заполненные формы"
		$iAdmin_Form_Id = 401;
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

		// Контроллер формы
		$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
		$oAdmin_Form_Controller
			->path('/{admin}/timeline/note/index.php')
			->window('id_content');

		switch ($type)
		{
			case 0: // Добавлена заметка
				$sIconIco = "fa-solid fa-bars-staggered";
				$sIconColor = "white";
				$sBackgroundColor = "bg-azure";
				$sNotificationColor = 'azure';
			break;
			default:
				$sIconIco = "fa-solid fa-info";
				$sIconColor = "white";
				$sBackgroundColor = "bg-themeprimary";
				$sNotificationColor = 'info';
		}

		// $oCrm_Note = Core_Entity::factory('Crm_Note', $entityId);

		return array(
			'icon' => array(
				'ico' => $sIconIco,
				'color' => $sIconColor,
				'background-color' => $sBackgroundColor
			),
			'notification' => array(
				'ico' => $sIconIco,
				'background-color' => $sNotificationColor
			),
			'href' => $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, intval($entityId), "full=1"),
			'onclick' => $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, intval($entityId), "full=1"),
			'extra' => array(
				'icons' => array(),
				'description' => NULL
			),
			// 'site' => htmlspecialchars($oForm->Site->name) . ' [' . $oForm->Site->id . ']'
			'site' => ''
		);
	}
}