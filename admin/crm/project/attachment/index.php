<?php
/**
 * Crm Projects.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'crm_project');

// File download
if (Core_Array::getGet('crm_project_attachment_id'))
{
	$oCrm_Project_Attachment = Core_Entity::factory('Crm_Project_Attachment')->getById(Core_Array::getGet('crm_project_attachment_id', 0, 'int'));

	if (!is_null($oCrm_Project_Attachment))
	{
		$crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');
		$oCrm_Project = Core_Entity::factory('Crm_Project')->getById($crm_project_id);
		$bAvailable = !is_null($oCrm_Project) && $oCrm_Project->id == $oCrm_Project_Attachment->crm_project_id;

		if ($bAvailable)
		{
			$filePath = $oCrm_Project_Attachment->getFilePath();

			if (!is_null($filePath))
			{
				Core_File::download($filePath, $oCrm_Project_Attachment->file_name, array('content_disposition' => 'inline'));
			}
			else
			{
				throw new Core_Exception('Wrong file path');
			}
		}
	}
}

// Код формы
$iAdmin_Form_Id = 326;
$sAdminFormAction = '/{admin}/crm/project/attachment/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$crm_project_id = intval(Core_Array::getGet('crm_project_id', 0));
$oCrm_Project = Core_Entity::factory('Crm_Project', $crm_project_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Crm_Project_Note.notes_title'))
	->pageTitle(Core::_('Crm_Project_Attachment.title'))
	->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	)
	// ->addView('attachment', 'Crm_Project_Controller_Attachment')
	// ->view('attachment')
	;

$oAdmin_Form_Controller->addExternalReplace('{crm_project_id}', $oCrm_Project->id);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oCrm_Project_Note_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Crm_Project_Attachment_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCrm_Project_Note_Controller_Edit);
}

$oAdminFormActionUploadFiles = $oAdmin_Form->Admin_Form_Actions->getByName('uploadFiles');

if ($oAdminFormActionUploadFiles && $oAdmin_Form_Controller->getAction() == 'uploadFiles')
{
	$oCrm_Project_Attachment_Controller_Upload = Admin_Form_Action_Controller::factory(
		'Crm_Project_Attachment_Controller_Upload', $oAdminFormActionUploadFiles
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCrm_Project_Attachment_Controller_Upload);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Crm_Project_Attachment')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('where' => array('crm_project_attachments.crm_project_id', '=', $oCrm_Project->id))
	)
	->addCondition(
		array('orderBy' => array('crm_project_attachments.id', 'DESC'))
	);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();