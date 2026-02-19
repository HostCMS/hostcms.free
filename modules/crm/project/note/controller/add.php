<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Note_Controller_Add
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Crm_Project_Note_Controller_Add extends Crm_Note_Controller_Add
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		parent::execute();

		$crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');
		$result = Core_Array::getPost('result', 0, 'int');

		$oCrm_Note = $this->_object;

		$oCrm_Project = Core_Entity::factory('Crm_Project', $crm_project_id);
		$oCrm_Project->add($oCrm_Note);

		$aFiles = Core_Array::getFiles('file', array());

		if (is_array($aFiles) && isset($aFiles['name']))
		{
			$oCrm_Note->dir = $oCrm_Project->getHref();
			$oCrm_Note->save();

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
					$oCrm_Note_Attachment = Core_Entity::factory('Crm_Note_Attachment');
					$oCrm_Note_Attachment->crm_note_id = $oCrm_Note->id;

					$oCrm_Note_Attachment
						->setDir(CMS_FOLDER . $oCrm_Note->dir)
						->setHref($oCrm_Project->getHref())
						->saveFile($aFile['tmp_name'], $aFile['name']);
				}
			}
		}

		$this->addMessage("<script>$(function() {
			$.adminLoad({ path: hostcmsBackend + '/crm/project/note/index.php', additionalParams: 'crm_project_id=" . $oCrm_Project->id . "', windowId: 'crm-project-notes' });
		});</script>");
	}
}