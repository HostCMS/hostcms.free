<?php
/**
 * Company.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

$iAdmin_Form_Id = 356;
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Путь к контроллеру формы ЦА
$sAdminFormAction = '/admin/company/cashbox/index.php';

$pageTitle = Core::_('Company_Cashbox.title');

if (Core_Auth::logged())
{
	Core_Auth::checkBackendBlockedIp();

	if (!is_null(Core_Array::getGet('getCashboxes')))
	{
		$aJSON = array();

		$iCompanyId = Core_Array::getGet('companyId', 0, 'int');

		if ($iCompanyId)
		{
			$aCompany_Cashboxes = Core_Entity::factory('Company_Cashbox')->getAllByCompany_id($iCompanyId);

			$i = 0;

			foreach($aCompany_Cashboxes as $oCompany_Cashbox)
			{
				$aJSON['cashboxes'][$i]['id'] = $oCompany_Cashbox->id;
				$aJSON['cashboxes'][$i++]['name'] = $oCompany_Cashbox->name;
			}
		}

		Core::showJson($aJSON);
	}
}

Core_Auth::authorization($sModule = 'company');

$company_id = Core_Array::getGet('company_id', 0, 'int');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($pageTitle)
	->pageTitle($pageTitle);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

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
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$sAdditionalParams = 'company_id=' . $company_id;

// Построение хлебных крошек
$oAdminFormEntityBreadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdminFormEntityBreadcrumbs
	->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Company.company_show_title2'))
			->href($oAdmin_Form_Controller->getAdminLoadHref($sPath = '/admin/company/index.php', NULL, NULL, ''))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sPath, NULL, NULL, ''))
	)
	->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Company_Cashbox.title'))
			->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
		);

// Хлебные крошки добавляем контроллеру
$oAdmin_Form_Controller->addEntity($oAdminFormEntityBreadcrumbs);

// Действие редактирования
$oAdminFormAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdminFormAction && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oCompany_Cashbox_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Company_Cashbox_Controller_Edit', $oAdminFormAction
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCompany_Cashbox_Controller_Edit);

	// Крошки при редактировании
	$oCompany_Cashbox_Controller_Edit->addEntity($oAdminFormEntityBreadcrumbs);
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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Company_Cashbox')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' => array('company_id', '=', $company_id))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();