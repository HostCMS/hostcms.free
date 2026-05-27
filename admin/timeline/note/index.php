<?php
/**
 * Timeline.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'crm_project');

// Код формы
$iAdmin_Form_Id = 312;
$sAdminFormAction = '/{admin}/timeline/note/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Timeline.notes_title'))
	->pageTitle(Core::_('Timeline.notes_title'));

$full_view = Core_Array::getGet('full', 0, 'int');

if (!$full_view)
{
	$oAdmin_Form_Controller->Admin_View(
		Admin_View::getClassName('Admin_Internal_View')
	);
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oTimeline_Note_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Timeline_Note_Controller_Edit', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTimeline_Note_Controller_Edit);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Crm_Note')
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();