<?php
/**
 * Admin forms.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'admin_form');

// Код формы
$iAdmin_Form_Id = 4;
$sAdminFormAction = '/admin/admin_form/action/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$admin_forms_id = intval(Core_Array::getGet('admin_form_id', 0));
$oAdmin_Form_Current = Core_Entity::factory('Admin_Form', $admin_forms_id);
$oAdmin_Word_Value = $oAdmin_Form_Current->Admin_Word->getWordByLanguage(CURRENT_LANGUAGE_ID);
$form_name = $oAdmin_Word_Value
	? $oAdmin_Word_Value->name
	: '';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Admin_Form_Action.show_form_action_title', $form_name))
	->pageTitle(Core::_('Admin_Form_Action.show_form_action_title', $form_name));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// array('path' => $oAdmin_Form_Controller->getPath(), 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => $additionalParams)

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form_Action.show_form_action_menu_add_new_top'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref(array('path' => $oAdmin_Form_Controller->getPath(), 'action' => 'edit', 'datasetKey' => 0, 'datasetValue' => 0))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax(array('path' => $oAdmin_Form_Controller->getPath(), 'action' => 'edit', 'datasetKey' => 0, 'datasetValue' => 0))
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');
$sAdminFormPath = '/admin/admin_form/index.php';

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Admin_Form.show_form_fields_menu_admin_forms'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $sAdminFormPath))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $sAdminFormPath))
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($form_name)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $oAdmin_Form_Controller->getPath(), 'additionalParams' => "admin_form_id={$admin_forms_id}"))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $oAdmin_Form_Controller->getPath(), 'additionalParams' => "admin_form_id={$admin_forms_id}"))
	)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oAdmin_Form_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Edit', $oAdmin_Form_Action
	);

	$oAdmin_Form_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_Form_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oAdmin_FormControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_FormControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Admin_Form_Action')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

$oAdmin_Form_Dataset->addCondition(
	array('select' => array('admin_form_actions.*', array('admin_word_values.name', 'word_name')))
)->addCondition(
	array('leftJoin' => array('admin_words', 'admin_form_actions.admin_word_id', '=', 'admin_words.id'))
)->addCondition(
	array('leftJoin' => array('admin_word_values', 'admin_words.id', '=', 'admin_word_values.admin_word_id'))
)->addCondition(
	array('open' => array())
)->addCondition(
	array('where' => array('admin_word_values.admin_language_id', '=', CURRENT_LANGUAGE_ID))
)->addCondition(
	array('setOr' => array())
)->addCondition(
	array('where' => array('admin_form_actions.admin_word_id', '=', 0))
)->addCondition(
	array('close' => array())
)->addCondition(
	array('where' => array('admin_form_id', '=', $admin_forms_id))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();