<?php
/**
 * SEO.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'seo');

// Код формы
$iAdmin_Form_Id = 141;
$sAdminFormAction = '/admin/seo/index.php';

$sAdminFormQueryAction = '/admin/seo/query/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Seo.title'))
	->pageTitle(Core::_('Seo.title'));

$sFormPath = $oAdmin_Form_Controller->getPath();

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(Admin_Form_Entity::factory('Menu')
	->name(Core::_('Seo.characteristics'))
	->icon('fa fa-gears')
	->add(Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->img('/admin/images/seo_add.gif')
		->href($oAdmin_Form_Controller->getAdminActionLoadHref($sFormPath, 'edit', NULL, 0, 0))
		->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax($sFormPath, 'edit', NULL, 0, 0))
	)
	->add(Admin_Form_Entity::factory('Menu')
		->name(Core::_('Seo.request'))
		->icon('fa fa-globe')
		->img('/admin/images/seo_go.gif')
		->href($oAdmin_Form_Controller->getAdminActionLoadHref($sFormPath, 'define', NULL, 0, 0))
		->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax($sFormPath, 'define', NULL, 0, 0))
	)
)
->add(Admin_Form_Entity::factory('Menu')
	->name(Core::_('Seo.search_requests'))
	->icon('fa fa-search')
	->add(Admin_Form_Entity::factory('Menu')
		->name(Core::_('Seo.management'))
		->icon('fa fa-search')
		->img('/admin/images/query.gif')
		->href($oAdmin_Form_Controller->getAdminActionLoadHref($sAdminFormQueryAction, NULL, NULL, 0, 0))
		->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax($sAdminFormQueryAction, NULL, NULL, 0, 0))
	)
	->add(Admin_Form_Entity::factory('Menu')
		->name(Core::_('Seo.get_position'))
		->icon('fa fa-question')
		->img('/admin/images/query_go.gif')
		->href($oAdmin_Form_Controller->getAdminActionLoadHref($sAdminFormQueryAction, 'define_position', NULL, 0, 0))
		->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax($sAdminFormQueryAction, 'define_position', NULL, 0, 0))
	)
)
->add(Admin_Form_Entity::factory('Menu')
	->name(Core::_('Seo.report_website'))
	->icon('fa fa-list-alt')
	->img('/admin/images/report.gif')
	->href($oAdmin_Form_Controller->getAdminActionLoadHref($sFormPath, 'report', NULL, 0, 0))
	->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax($sFormPath, 'report', NULL, 0, 0))
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Строка навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Seo.title'))
	->href($oAdmin_Form_Controller->getAdminLoadHref($sFormPath, NULL, NULL, ''))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sFormPath, NULL, NULL, ''))
);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oSeo_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Seo_Controller_Edit', $oAdmin_Form_Action
	);

	$oSeo_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSeo_Controller_Edit);
}

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('define');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'define')
{
	$oSeo_Controller_Define = Admin_Form_Action_Controller::factory(
		'Seo_Controller_Define', $oAdmin_Form_Action
	);

	$oAdmin_Form_Controller->addAction($oSeo_Controller_Define);
}

// Определение позиций
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('define_position');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'define_position')
{
	$oSeo_Query_Position_Controller_Define = Admin_Form_Action_Controller::factory(
		'Seo_Query_Position_Controller_Define', $oAdmin_Form_Action
	);

	$oAdmin_Form_Controller->addAction($oSeo_Query_Position_Controller_Define);
}

// Отчет по сайту
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('report');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'report')
{
	$oSeo_Controller_Report = Admin_Form_Action_Controller::factory(
		'Seo_Controller_Report', $oAdmin_Form_Action
	);

	$oAdmin_Form_Entity_Breadcrumbs->add(Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Seo.report_title', Core_Entity::factory('Site', CURRENT_SITE)->name))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sFormPath, 'report', NULL, '0'))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sFormPath, 'report', NULL, '0'))
	);

	$oSeo_Controller_Report->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	$oAdmin_Form_Controller->addAction($oSeo_Controller_Report);
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
	Core_Entity::factory('Seo')
);

// Ограничение по сайту
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->execute();