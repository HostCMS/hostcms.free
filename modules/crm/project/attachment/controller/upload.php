<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Attachment_Controller_Upload
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Crm_Project_Attachment_Controller_Upload extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');

		$aFiles = Core_Array::getFiles('file', array());

		if (is_array($aFiles) && isset($aFiles['name']))
		{
			$iCount = count($aFiles['name']);

			for ($i = 0; $i < $iCount; $i++)
			{
				$aFile = array(
					'name' => $aFiles['name'][$i],
					'tmp_name' => $aFiles['tmp_name'][$i],
					'size' => $aFiles['size'][$i]
				);

				if (intval($aFile['size']) > 0)
				{
					$oCrm_Project_Attachment = Core_Entity::factory('Crm_Project_Attachment');
					$oCrm_Project_Attachment->crm_project_id = $crm_project_id;

					$oCrm_Project_Attachment->saveFile($aFile['tmp_name'], $aFile['name']);
				}
			}
		}
	}
}