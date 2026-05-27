<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Note
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Event_Controller_Note extends Crm_Note_Controller
{
	/**
	 * Constructor
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function __construct($oAdmin_Form_Controller)
	{
		parent::__construct($oAdmin_Form_Controller);

		$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('parentWindowId', '', 'str'));
		$this->_windowId = $parentWindowId ? $parentWindowId : $oAdmin_Form_Controller->getWindowId();

		$this->_tabName = $this->_windowId;

		$this->_additionalParams = 'event_id={event_id}&secret_csrf=' . Core_Security::getCsrfToken();

		$this->_formPath = '/{admin}/event/note/index.php';
		$this->_timelinePath = '/{admin}/event/timeline/index.php';

		$this->_entityName = 'Event';
	}

	/**
	 * Get completed dropdown
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function getCompletedDropdown()
	{
		$event_id = Core_Array::getGet('event_id', 0, 'int');
		$oEvent = Core_Entity::factory('Event', $event_id);

		return $oEvent->getCompletedDropdown($this->_Admin_Form_Controller);
	}
}