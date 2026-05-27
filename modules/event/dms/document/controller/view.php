<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Dms_Document_Controller_View
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Event_Dms_Document_Controller_View extends Crm_Dms_Document_Controller
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

		$this->_additionalParams = 'secret_csrf=' . Core_Security::getCsrfToken();

		$this->_path = '/{admin}/event/dms/document/index.php';

		$this->_entityName = 'Event';
	}
}