<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm Project Module.
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Crm_Project_Module extends Core_Module_Abstract
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
	protected $_moduleName = 'crm_project';

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
				'ico' => 'fas fa-tasks',
				'name' => Core::_('Crm_Project.menu'),
				'href' => Admin_Form_Controller::correctBackendPath("/{admin}/crm/project/index.php"),
				'onclick' => Admin_Form_Controller::correctBackendPath("$.adminLoad({path: '/{admin}/crm/project/index.php'}); return false")
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
		switch ($type)
		{
			case 6: // Добавлена заметка
				$sIconIco = "fa-comment-o";
				$sIconColor = "white";
				$sBackgroundColor = "bg-azure";
				$sNotificationColor = 'azure';
			break;

			default:
				$sIconIco = "fa-info";
				$sIconColor = "white";
				$sBackgroundColor = "bg-themeprimary";
				$sNotificationColor = 'info';
		}

		return array(
			'icon' => array(
				'ico' => "fa {$sIconIco}",
				'color' => $sIconColor,
				'background-color' => $sBackgroundColor
			),
			'notification' => array(
				'ico' => $sIconIco,
				'background-color' => $sNotificationColor
			),
			'href' => Admin_Form_Controller::correctBackendPath("/{admin}/crm/project/index.php?hostcms[action]=edit&hostcms[operation]=&hostcms[current]=1&hostcms[checked][0][" . $entityId . "]=1"),
			// $(this).parents('li.open').click();
			'onclick' => "$.adminLoad({path: hostcmsBackend + '/crm/project/index.php?hostcms[action]=edit&hostcms[operation]=&hostcms[current]=1&hostcms[checked][0][" . $entityId . "]=1'}); return false",
			'extra' => array(
				'icons' => array(),
				'description' => NULL
			)
		);
	}
}