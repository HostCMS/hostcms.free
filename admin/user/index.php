<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'user');

// Код формы
$iAdmin_Form_Id = 7;
$sAdminFormAction = '/admin/user/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('User.ua_link_users_site'))
	->pageTitle(Core::_('User.ua_link_users_site'));

// Смена бэкграунда
if (!is_null(Core_Array::getPost('wallpaper-id')))
{
	$oUser = Core_Entity::factory('User')->getCurrent();

	if (!is_null($oUser))
	{
		$oModule = Core_Entity::factory('Module')->getByPath($sModule);

		if (!is_null($oModule))
		{
			$type = 95;
			$oUser_Settings = $oUser->User_Settings;
			$oUser_Settings->queryBuilder()
				->where('user_settings.module_id', '=', $oModule->id)
				->where('user_settings.type', '=', $type)
				->where('user_settings.active', '=', 1)
				->limit(1);

			$aUser_Settings = $oUser_Settings->findAll();

			if (isset($aUser_Settings[0]))
			{
				$oUser_Setting = $aUser_Settings[0];
			}
			else
			{
				$oUser_Setting = Core_Entity::factory('User_Setting');
				$oUser_Setting->module_id = $oModule->id;
				$oUser_Setting->type = $type;
				$oUser_Setting->active = 1;
			}

			$oUser_Setting->entity_id = intval(Core_Array::getPost('wallpaper-id'));
			$oUser_Setting->save();
		}
	}

	Core::showJson('OK');
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('User_Group.ua_link_users_type'))
		->icon('fa fa-users')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('User_Group.ua_link_add_users_type'))
				//->img('/admin/images/users_group_add.gif')
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('User_Group.wallpaper'))
		->icon('fa fa-image')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/user/wallpaper/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/user/wallpaper/index.php', NULL, NULL, '')
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oUser_Group_Controller_Edit = Admin_Form_Action_Controller::factory(
		'User_Group_Controller_Edit', $oAdmin_Form_Action
	);

	// Элементы строки навигации
	$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

	// Элементы строки навигации
	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('User_Group.ua_link_users_types'))
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
	);

	$oUser_Group_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUser_Group_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
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

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('User_Group')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();
