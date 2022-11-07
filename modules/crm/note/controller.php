<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Note_Controller
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Note_Controller extends Core_Servant_Properties
{
	/**
	 * Get completed dropdown
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	static public function getCompletedDropdown($oAdmin_Form_Controller)
	{
		$aCompleted = array(
			1 => array(
				'value' => Core::_('Admin_Form.successfully'),
				'color' => '#a0d468',
				'icon' => 'fa-solid fa-circle-check fa-fw margin-right-5'
			),
			-1 => array(
				'value' => Core::_('Admin_Form.failed'),
				'color' => '#ed4e2a',
				'icon' => 'fa-solid fa-xmark fa-fw margin-right-5'
			)
		);

		return Admin_Form_Entity::factory('Dropdownlist')
			->options($aCompleted)
			->name('completed')
			->divAttr(array('class' => 'margin-left-10 crm-note-completed hidden'))
			->controller($oAdmin_Form_Controller)
			->execute();
	}
}