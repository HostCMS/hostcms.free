<?php
/**
 * Company.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

// Код формы
$iAdmin_Form_Id = 64;
$sAdminFormAction = '/{admin}/company/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

if (Core_Auth::logged())
{
	// Контроллер формы
	$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

	if (!is_null(Core_Array::getGet('loadCompanyAvatar')))
	{
		Core_Session::close();

		$id = Core_Array::getGet('loadCompanyAvatar', 0, 'int');
		$oCompany = Core_Entity::factory('Company')->getById($id);

		$name = $oCompany ? strval($oCompany->name) : NULL;

		if (!is_null($name))
		{
			// Get initials
			$initials = Core_Str::getInitials($name);

			$bgColor = Core_Str::createColor($id);

			Core_Image::avatar($initials, $bgColor, $width = 130, $height = 130);
		}

		die();
	}

	if (!is_null(Core_Array::getPost('showPopover')))
	{
		$aJSON = array(
			'html' => ''
		);

		$oCurrentUser = Core_Auth::getCurrentUser();

		$company_id = Core_Array::getPost('company_id', 0, 'int');

		$oCompany = Core_Entity::factory('Company')->getById($company_id);

		if (!is_null($oCompany) && $oCurrentUser->checkObjectAccess($oCompany))
		{
			$aJSON['html'] = $oCompany->getProfilePopupBlock();
		}

		Core::showJson($aJSON);
	}
}

Core_Auth::authorization($sModule = 'company');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Company.company_show_title2'))
	->pageTitle(Core::_('Company.company_show_title2'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Company.company_show_title_add'))
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
		->name(Core::_('Company.show_posts'))
		->icon('fa fa-user')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($sCompanyPostsFormPath = '/{admin}/company/post/index.php', NULL, NULL, '', 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCompanyPostsFormPath, NULL, NULL, '', 0)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Company.show_email_types'))
		->icon('fa fa-envelope')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($sCompanyEmailTypesFormPath = '/{admin}/directory/email/type/index.php', NULL, NULL, '', 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCompanyEmailTypesFormPath, NULL, NULL, '', 0)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Company.show_phone_types'))
		->icon('fa fa-phone')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($sCompanyAddressTypesFormPath = '/{admin}/directory/phone/type/index.php', NULL, NULL, '', 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCompanyAddressTypesFormPath, NULL, NULL, '', 0)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Company.show_address_types'))
		->icon('fa fa-map-marker')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($sCompanyPhoneTypesFormPath = '/{admin}/directory/address/type/index.php', NULL, NULL, '', 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCompanyPhoneTypesFormPath, NULL, NULL, '', 0)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Company.show_socials'))
		->icon('fa fa-share-alt')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($sCompanySocialsFormPath = '/{admin}/directory/social/type/index.php', NULL, NULL, '', 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCompanySocialsFormPath, NULL, NULL, '', 0)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Company.show_messengers'))
		->icon('fa fa-comments')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($sCompanyMessengersFormPath = '/{admin}/directory/messenger/type/index.php', NULL, NULL, '', 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCompanyMessengersFormPath, NULL, NULL, '', 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Company.company_show_title2'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oCompany_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Company_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$oCompany_Controller_Edit
		->addEntity(
			$oAdmin_Form_Entity_Breadcrumbs
		);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCompany_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form->Admin_Form_Actions->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Удаление файла большого изображения"
$oAction = $oAdmin_Form->Admin_Form_Actions->getByName('deleteImageFile');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteImageFile')
{
	$oDeleteImageFileController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteImageFileController
		->methodName('deleteImageFile')
		->divId(array('preview_large_image', 'delete_large_image'));

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($oDeleteImageFileController);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity
(
	Core_Entity::factory('Company')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset
(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();