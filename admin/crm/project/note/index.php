<?php
/**
 * Crm Projects.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'crm_project');

// Код формы
$iAdmin_Form_Id = 312;
$sAdminFormAction = '/admin/crm/project/note/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$crm_project_id = intval(Core_Array::getGet('crm_project_id', 0));
$oCrm_Project = Core_Entity::factory('Crm_Project', $crm_project_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Crm_Project_Note.notes_title'))
	->pageTitle(Core::_('Crm_Project_Note.notes_title'))
	->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	)
	->addView('note', 'Crm_Project_Controller_Note')
	->view('note');

$oAdmin_Form_Controller->addExternalReplace('{crm_project_id}', $oCrm_Project->id);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oCrm_Project_Note_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Crm_Project_Note_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCrm_Project_Note_Controller_Edit);
}

// Добавление заметки
$oAdmin_Form_Action_Add_Note = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('addNote');

if ($oAdmin_Form_Action_Add_Note && $oAdmin_Form_Controller->getAction() == 'addNote')
{
	$oCrm_Project_Note_Controller_Add = Admin_Form_Action_Controller::factory(
		'Crm_Project_Note_Controller_Add', $oAdmin_Form_Action_Add_Note
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCrm_Project_Note_Controller_Add);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Crm_Project_Note')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('where' => array('crm_project_notes.crm_project_id', '=', $oCrm_Project->id))
	)->addCondition(
		array('orderBy' => array('crm_project_notes.id', 'DESC'))
	);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();