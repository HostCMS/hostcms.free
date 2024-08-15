<?php
/**
 * Event timeline.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'event');

// Код формы
$iAdmin_Form_Id = 324;
$sAdminFormAction = '/admin/event/timeline/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iEventId = intval(Core_Array::getRequest('event_id', 0));
$oEvent = Core_Entity::factory('Event', $iEventId);

if (!$oEvent->id)
{
	throw new Core_Exception('Event object does not exist. Check event_id.', array(), 0, FALSE);
}

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Event.timeline_title', $oEvent->name))
	->pageTitle(Core::_('Event.timeline_title', $oEvent->name))
	->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	)
	->addView('timeline', 'Event_Controller_Timeline')
	->view('timeline');

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

// Действие редактирования
/*$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('deleteEntity');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'deleteEntity')
{
	$oEvent_Timeline_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Event_Timeline_Controller_Delete', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Timeline_Controller_Delete);
}*/

// Действие "Отметить удаленным"
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('markDeleted');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'markDeleted')
{
	$oEvent_Timeline_Controller_Markdeleted = Admin_Form_Action_Controller::factory(
		'Event_Timeline_Controller_Markdeleted', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Timeline_Controller_Markdeleted);
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$controller = NULL;

	$aChecked = $oAdmin_Form_Controller->getChecked();

	if (isset($aChecked[0]))
	{
		list($type, $id) = explode('-', key($aChecked[0]));

		switch ($type)
		{
			case 1:
				$controller = 'Event_Note_Controller_Edit';
			break;
			case 2:
				$controller = 'Event_Controller_Edit';
			break;
			case 3:
				$controller = 'Dms_Document_Controller_Edit';
			break;
		}
	}

	if (!is_null($controller))
	{
		$Controller_Edit = Admin_Form_Action_Controller::factory(
			$controller, $oAdmin_Form_Action
		);

		// Добавляем типовой контроллер редактирования контроллеру формы
		$oAdmin_Form_Controller->addAction($Controller_Edit);
	}
}

// Источник данных 0
$oAdmin_Form_Dataset = new Event_Timeline_Dataset($oEvent);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addExternalReplace('{event_id}', $oEvent->id);

Core_Event::attach('Admin_Form_Controller.onAfterShowContent', array('User_Controller', 'onAfterShowContentPopover'), array($oAdmin_Form_Controller));
Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('User_Controller', 'onAfterRedeclaredPrepareForm'));

// Показ формы
$oAdmin_Form_Controller->execute();