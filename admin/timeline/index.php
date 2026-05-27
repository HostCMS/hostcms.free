<?php
/**
 * Timeline
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'timeline');

// Код формы
$iAdmin_Form_Id = 401;
$sAdminFormAction = '/{admin}/timeline/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Timeline.menu'))
	->pageTitle(Core::_('Timeline.menu'))
	->addView('entity', 'Timeline_View')
	->view('entity');

$windowId = $oAdmin_Form_Controller->getWindowId();

$additionalParams = Core_Str::escapeJavascriptVariable(
	str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

/*$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Timeline.add_note'))
		->icon('fa-solid fa-plus')
		->onclick(
			"$.modalLoad({path: hostcmsBackend + '/timeline/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&{$additionalParams}', windowId: '{$windowId}'}); return false"
		)
);*/

// Элементы меню
if (Core::moduleIsActive('event'))
{
	$oAdmin_Form_Entity_Menus
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Timeline.add_event'))
				->icon('fa-solid fa-plus')
				->onclick(
					"$.modalLoad({path: hostcmsBackend + '/event/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&{$additionalParams}', windowId: '{$windowId}'}); return false"
				)
		);
}

if (Core::moduleIsActive('deal'))
{
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Timeline.add_deal'))
			->icon('fa-solid fa-plus')
			->onclick(
				"$.modalLoad({path: hostcmsBackend + '/deal/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&{$additionalParams}', windowId: '{$windowId}'}); return false"
			)
	);
}

if (Core::moduleIsActive('dms'))
{
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Timeline.add_document'))
			->icon('fa-solid fa-plus')
			->onclick(
				"$.modalLoad({path: hostcmsBackend + '/dms/document/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][0]=1&{$additionalParams}', windowId: '{$windowId}'}); return false"
			)
	);
}

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Построение хлебных крошек
$oAdminFormEntityBreadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая хлебная крошка будет всегда
$oAdminFormEntityBreadcrumbs
	->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Timeline.menu'))
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath())
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())
			)
	);

// Хлебные крошки добавляем контроллеру
$oAdmin_Form_Controller->addEntity($oAdminFormEntityBreadcrumbs);

// Добавление заметки
$oAdmin_Form_Action_Add_Note = $oAdmin_Form->Admin_Form_Actions->getByName('addNote');

if ($oAdmin_Form_Action_Add_Note && $oAdmin_Form_Controller->getAction() == 'addNote')
{
	$oTimeline_Controller_Add = Admin_Form_Action_Controller::factory(
		'Timeline_Controller_Add', $oAdmin_Form_Action_Add_Note
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTimeline_Controller_Add);
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('deleteEntity');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'deleteEntity')
{
	$oTimeline_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Timeline_Controller_Delete', $oAdmin_Form_Action
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTimeline_Controller_Delete);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Timeline_Dataset(
	Core_Entity::factory('Timeline')
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

Core_Event::attach('Admin_Form_Controller.onAfterShowContent', array('User_Controller', 'onAfterShowContentPopover'), array($oAdmin_Form_Controller));

if (Core::moduleIsActive('siteuser'))
{
	Core_Event::attach('Admin_Form_Controller.onAfterShowContent', array('Siteuser_Controller', 'onAfterShowContentPopover'), array($oAdmin_Form_Controller));
}

// Показ формы
$oAdmin_Form_Controller->execute();