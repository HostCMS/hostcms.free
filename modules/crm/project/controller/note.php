<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Controller_Note
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Crm_Project_Controller_Note extends Crm_Note_Controller
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

		$this->_additionalParams = 'crm_project_id={crm_project_id}&secret_csrf=' . Core_Security::getCsrfToken();
		$this->_formPath = '/{admin}/crm/project/note/index.php';
		$this->_timelinePath = '/{admin}/crm/project/timeline/index.php';

		$this->_entityName = 'Crm_Project';
	}
}