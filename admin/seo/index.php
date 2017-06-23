<?php
/**
 * Seo
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'seo');

// Код формы
$iAdmin_Form_Id = 215;
$sAdminFormAction = '/admin/seo/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Seo.title'))
	->pageTitle(Core::_('Seo.title'));

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
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Seo.drivers'))
		->icon('fa fa-gear')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref('/admin/seo/driver/index.php', NULL, NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax('/admin/seo/driver/index.php', NULL, NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Seo.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oSeo_Site_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Seo_Site_Controller_Edit', $oAdmin_Form_Action
	);

	$oSeo_Site_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSeo_Site_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oSeoSiteControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSeoSiteControllerApply);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Seo_Site'));

$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
);

$oSeo_Sites = Core_Entity::factory('Seo_Site');
$oSeo_Sites->queryBuilder()
	->where('seo_sites.active', '=', 1)
	->open()
		->where('seo_sites.last_update', '<', Core_Date::timestamp2sql(strtotime('-15 minutes')))
		->setOr()
		->where('seo_sites.last_update', 'IS', NULL)
	->close();

$aSeo_Sites = $oSeo_Sites->findAll(FALSE);

foreach ($aSeo_Sites as $oSeo_Site)
{
	if (strlen($oSeo_Site->token))
	{
		try{
			$oSeo_Driver_Controller = Seo_Controller::instance($oSeo_Site->Seo_Driver->driver);

			$oSeo_Driver_Controller
				->setSeoSite($oSeo_Site)
				->execute();

			// $host_id = $oSeo_Driver_Controller->getHostId();

			// $aSitePopularQueries = $oSeo_Driver_Controller->getSitePopularQueries($host_id);

			// ob_start();
			// print_r($aSitePopularQueries);
			// echo "\n\n";
			// file_put_contents(CMS_FOLDER . 'seo.txt', ob_get_clean(), FILE_APPEND);
		}
		catch (Exception $e){
			$oAdmin_Form_Controller->addMessage(
				Core_Message::get($e->getMessage(), 'error')
			);
		}
	}
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();