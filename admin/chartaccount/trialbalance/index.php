<?php
/**
 * Chartaccount.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'chartaccount');

$sAdminFormAction = '/admin/chartaccount/trialbalance/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Chartaccount_Trialbalance.title'));

$oMainTab = Admin_Form_Entity::factory('Tab')->class('mainTab')->name('main');

if (!is_null(Core_Array::getPost('ajaxLoadTabContent')))
{
	$aJson = array();

	ob_start();

	Chartaccount_Trialbalance_Controller::setAdminFormController($oAdmin_Form_Controller);
	Chartaccount_Trialbalance_Controller::showContent($oMainTab, $_POST)->execute();

	Core::showJson(array('content' => ob_get_clean()));
}

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('Chartaccount_Trialbalance.title'))
	;

ob_start();

Chartaccount_Trialbalance_Controller::setAdminFormController($oAdmin_Form_Controller);
Chartaccount_Trialbalance_Controller::showContent($oMainTab);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Chartaccount.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/chartaccount/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/chartaccount/index.php', NULL, NULL, '')
		)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Chartaccount_Trialbalance.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $oAdmin_Form_Controller->getPath()))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $oAdmin_Form_Controller->getPath()))
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

Admin_Form_Entity::factory('Form')
	->class('mainForm')
	->controller($oAdmin_Form_Controller)
	->action($sAdminFormAction)
	->add($oMainTab)
	->execute();

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title(Core::_('Chartaccount_Trialbalance.title'))
	->module($sModule)
	->execute();