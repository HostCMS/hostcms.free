<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'user');

// Код формы
$iAdmin_Form_Id = 8;
$sAdminFormAction = '/admin/user/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('User.ua_show_users_title'))
	->pageTitle(Core::_('User.ua_show_users_title'));

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

if (!is_null(Core_Array::getPost('add_bookmark')) && Core_Array::getPost('name'))
{
	$oUser = Core_Entity::factory('User')->getCurrent();

	if (!is_null($oUser))
	{
		$oUser_Bookmark = Core_Entity::factory('User_Bookmark');
		$oUser_Bookmark->module_id = intval(Core_Array::getPost('module_id', 0));
		$oUser_Bookmark->name = strval(Core_Array::getPost('name'));
		$oUser_Bookmark->path = strval(Core_Array::getPost('path'));
		$oUser_Bookmark->user_id = $oUser->id;
		$oUser_Bookmark->save();
	}

	Core::showJson('OK');
}

if (!is_null(Core_Array::getPost('remove_bookmark')) && Core_Array::getPost('bookmark_id'))
{
	$oUser = Core_Entity::factory('User')->getCurrent();

	$bookmark_id = intval(Core_Array::getPost('bookmark_id'));

	$oUser_Bookmark = $oUser->User_Bookmarks->getById($bookmark_id);

	$message = 'Error';

	if (!is_null($oUser_Bookmark))
	{
		$oUser_Bookmark->markDeleted();

		$message = 'OK';
	}

	Core::showJson($message);
}

if (!is_null(Core_Array::getPost('loadNavSidebarMenu')))
{
	ob_start();

	Core_Skin::instance()->navSidebarMenu();

	$oAdmin_Answer = Core_Skin::instance()->answer();
	$oAdmin_Answer
		->content(ob_get_clean())
		->ajax(TRUE)
		->execute();

	exit();
}

if (!is_null(Core_Array::getPost('generate-password')))
{
	Core::showJson(
		array(
			'password' => Core_Password::get()
		)
	);
}

if (!is_null(Core_Array::getGet('loadUserAvatar')))
{
	$id = intval(Core_Array::getGet('loadUserAvatar'));
	$oUser = Core_Entity::factory('User')->getById($id);
	if ($oUser)
	{
		$name = strlen($oUser->name) && strlen($oUser->surname)
			? $oUser->name . ' ' . $oUser->surname
			: $oUser->login;
	}
	else
	{
		Core_Message::show('Wrong ID', 'error');
	}

	$aBackgounds = array(
		'#f44336',
		'#E91E63',
		'#9C27B0',
		'#673AB7',
		'#3F51B5',
		'#2196F3',
		'#03A9F4',
		'#00BCD4',
		'#009688',
		'#4CAF50',
		'#8BC34A',
		'#CDDC39',
		'#FFC107',
		'#FF9800',
		'#FF5722'
	);

	// Get initials
	$initials = Core_Str::getInitials($name);

	// Choose a background color for the circle
	$bgColor = isset($aBackgounds[$id % 15])
		? $aBackgounds[$id % 15]
		: '#f44336';

	Core_Image::avatar($initials, $bgColor, $width = 130, $height = 130);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$sUserSiteChoosePath = '/admin/user/site/index.php';

$sActionAdditionalParam = '&mode=action';

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('User.wallpaper'))
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

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User.ua_show_users_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath())
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oUser_Controller_Edit = Admin_Form_Action_Controller::factory(
		'User_Controller_Edit', $oAdmin_Form_Action
	);

	$oUser_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUser_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oUserControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserControllerApply);
}

// Действие "Просмотр"
$oAdminFormActionView = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('view');

if ($oAdminFormActionView && $oAdmin_Form_Controller->getAction() == 'view')
{
	$oUserControllerView = Admin_Form_Action_Controller::factory(
		'User_Controller_View', $oAdminFormActionView
	);

	$oUserControllerView
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserControllerView);
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

// Действие "Удалить файл изображения"
$oAdminFormActionDeleteImageFile = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteImageFile');

if ($oAdminFormActionDeleteImageFile && $oAdmin_Form_Controller->getAction() == 'deleteImageFile')
{
	$oUserControllerDeleteImageFile = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAdminFormActionDeleteImageFile
	);

	$oUserControllerDeleteImageFile
		->methodName('deleteImageFile')
		->divId(array('preview_large_image', 'delete_large_image'));

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserControllerDeleteImageFile);
}


// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('User')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array(
		'select' => array('users.*', array(Core_QueryBuilder::expression('CONCAT_WS(" ", `surname`, `name`, `patronymic`)'), 'fullname'))
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();
