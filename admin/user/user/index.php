<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'user');

// Код формы
$iAdmin_Form_Id = 8;
$sAdminFormAction = '/admin/user/user/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$user_group_id = intval(Core_Array::getGet('user_group_id', 0));
$oUser_Group = Core_Entity::factory('User_Group', $user_group_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('User.ua_show_users_title', $oUser_Group->name))
	->pageTitle(Core::_('User.ua_show_users_title', $oUser_Group->name));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$sUserSiteChoosePath = '/admin/user/site/index.php';
$sAdditionalParam = 'user_group_id=' . $user_group_id;

$sActionAdditionalParam = $sAdditionalParam . '&mode=action';

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('User.ua_link_users'))
		->icon('fa fa-user')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('User.ua_add_user_link'))
				//->img('/admin/images/user_add.gif')
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('User.ua_link_user_access'))
				//->img('/admin/images/shield.gif')
				->icon('fa fa-puzzle-piece')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sUserSiteChoosePath, NULL, NULL, $sAdditionalParam)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sUserSiteChoosePath, NULL, NULL, $sAdditionalParam)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('User.ua_link_user_form_access'))
				//->img('/admin/images/table_shield.gif')
				->icon('fa fa-flash')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sUserSiteChoosePath, NULL, NULL, $sActionAdditionalParam)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sUserSiteChoosePath, NULL, NULL, $sActionAdditionalParam)
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Путь к контроллеру предыдущей формы
$sUserGroupPath = '/admin/user/index.php';

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User_Group.ua_link_users_type'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sUserGroupPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sUserGroupPath, NULL, NULL, '')
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User.ua_show_users_title', $oUser_Group->name))
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
	array('where' =>
		array('user_group_id', '=', $oUser_Group->id)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();
