<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Note_Controller_Markdeleted.
 *
 * @package HostCMS
 * @subpackage Crm_Project
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Crm_Project_Note_Controller_Markdeleted extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return bool
	 */
	public function execute($operation = NULL)
	{
		$crm_project_id = intval(Core_Array::getGet('crm_project_id', 0));
		$oCrm_Project = Core_Entity::factory('Crm_Project', $crm_project_id);

		if (!is_null($oCrm_Project))
		{
			$this->_object->markDeleted();

			// $this->addMessage("<script>$(function() {
			// 	$.adminLoad({ path: hostcmsBackend + '/crm/project/entity/index.php', additionalParams: 'crm_project_id=" . $oCrm_Project->id . "', windowId: 'id_content' });
			// });</script>");
		}

		return FALSE;
	}
}