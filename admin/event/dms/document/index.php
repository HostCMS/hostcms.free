<?php
/**
 * Event.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'event');

// Код формы
$iAdmin_Form_Id = 336;
$sAdminFormAction = '/admin/event/dms/document/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iEventId = intval(Core_Array::getGet('event_id', 0));
$oEvent = Core_Entity::factory('Event', $iEventId);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Event_Dms_Document.title'))
	->pageTitle(Core::_('Event_Dms_Document.title'))
	->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	)
	->addView('view', 'Event_Dms_Document_Controller_View')
	->view('view');

$oAdmin_Form_Controller->addExternalReplace('{event_id}', $oEvent->id);

$windowId = $oAdmin_Form_Controller->getWindowId();

$additionalParams = Core_Str::escapeJavascriptVariable(
	str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
);

if (is_null(Core_Array::getGet('hideMenu')))
{
	// Меню формы
	$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

	// Элементы меню
	$oAdmin_Form_Entity_Menus
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Admin_Form.add'))
				->icon('fa fa-plus')
				->class('btn btn-gray')
				->onclick(
					$oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $oAdmin_Form_Controller->getPath(), 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => $additionalParams, 'width' => '90%'))
				)
		);

	// Добавляем все меню контроллеру
	$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);
}

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oDms_Document_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Dms_Document_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oDms_Document_Controller_Edit);
}

// Действие удаления
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('markDeleted');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'markDeleted')
{
	$oEvent_Controller_Markdeleted = Admin_Form_Action_Controller::factory(
		'Event_Controller_Markdeleted', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oEvent_Controller_Markdeleted);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Dms_Document')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('dms_documents.*'))
	)
	->addCondition(
		array('join' => array('event_dms_documents', 'event_dms_documents.dms_document_id', '=', 'dms_documents.id'))
	)
	->addCondition(
		array('where' => array('event_dms_documents.event_id', '=', $oEvent->id))
	)
	->addCondition(
		array('orderBy' => array('event_dms_documents.id', 'DESC'))
	);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

Core_Event::attach('Admin_Form_Controller.onAfterShowContent', array('User_Controller', 'onAfterShowContentPopover'), array($oAdmin_Form_Controller));
Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('User_Controller', 'onAfterRedeclaredPrepareForm'));

// Показ формы
$oAdmin_Form_Controller->execute();