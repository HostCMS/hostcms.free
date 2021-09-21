<?php
/**
 * Events.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'event');

// Код формы
$iAdmin_Form_Id = 309;
$sAdminFormAction = '/admin/event/note/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iEventId = intval(Core_Array::getGet('event_id', 0));
$oEvent = Core_Entity::factory('Event', $iEventId);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
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
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oEvent_Note_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Event_Note_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Note_Controller_Edit);
}

// Добавление заметки
$oAdmin_Form_Action_Add_Event_Note = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('addEventNote');

if ($oAdmin_Form_Action_Add_Event_Note && $oAdmin_Form_Controller->getAction() == 'addEventNote')
{
	$oEvent_Note_Controller_Add = Admin_Form_Action_Controller::factory(
		'Event_Note_Controller_Add', $oAdmin_Form_Action_Add_Event_Note
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Note_Controller_Add);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Event_Note')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('where' => array('event_notes.event_id', '=', $oEvent->id))
	)->addCondition(
		array('orderBy' => array('event_notes.id', 'DESC'))
	);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();