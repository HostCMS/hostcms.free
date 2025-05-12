<?php
/**
 * Events.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'event');

// Код формы
$iAdmin_Form_Id = 309;
$sAdminFormAction = '/{admin}/event/note/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iEventId = intval(Core_Array::getGet('event_id', 0));
$oEvent = Core_Entity::factory('Event', $iEventId);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Event_Note.event_notes_title'))
	->pageTitle(Core::_('Event_Note.event_notes_title'))
	->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	)
	->addView('note', 'Event_Controller_Note')
	->view('note');

$oAdmin_Form_Controller->addExternalReplace('{event_id}', $oEvent->id);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oEvent_Note_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Event_Note_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Note_Controller_Edit);
}

// Добавление заметки
$oAdmin_Form_Action_Add_Event_Note = $oAdmin_Form->Admin_Form_Actions->getByName('addNote');

if ($oAdmin_Form_Action_Add_Event_Note && $oAdmin_Form_Controller->getAction() == 'addNote')
{
	$oEvent_Note_Controller_Add = Admin_Form_Action_Controller::factory(
		'Event_Note_Controller_Add', $oAdmin_Form_Action_Add_Event_Note
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Note_Controller_Add);
}

// Действие "Удалить файл"
$oAdminFormActionDeleteFile = $oAdmin_Form->Admin_Form_Actions->getByName('deleteFile');

if ($oAdminFormActionDeleteFile && $oAdmin_Form_Controller->getAction() == 'deleteFile')
{
	$oController_Type_Delete_File = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAdminFormActionDeleteFile
	);

	$oController_Type_Delete_File
		->methodName('deleteFile')
		->dir($oEvent->getPath())
		->divId('file_' . $oAdmin_Form_Controller->getOperation());

	// Добавляем контроллер удаления файла контроллеру формы
	$oAdmin_Form_Controller->addAction($oController_Type_Delete_File);
}

// Действие "Отметить удаленным"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('markDeleted');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'markDeleted')
{
	$oEvent_Note_Controller_Markdeleted = Admin_Form_Action_Controller::factory(
		'Event_Note_Controller_Markdeleted', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Note_Controller_Markdeleted);
}


// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Crm_Note')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('crm_notes.*'))
	)
	->addCondition(
		array('leftJoin' => array('event_crm_notes', 'crm_notes.id', '=', 'event_crm_notes.crm_note_id'))
	)
	->addCondition(
		array('where' => array('event_crm_notes.event_id', '=', $oEvent->id))
	)
	/*->addCondition(
		array('orderBy' => array('event_crm_notes.id', 'DESC'))
	)*/;

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

Core_Event::attach('Admin_Form_Controller.onAfterShowContent', array('User_Controller', 'onAfterShowContentPopover'), array($oAdmin_Form_Controller));
Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('User_Controller', 'onAfterRedeclaredPrepareForm'));

// Показ формы
$oAdmin_Form_Controller->execute();