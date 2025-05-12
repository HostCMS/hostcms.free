<?php
/**
 * Chartaccount.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'chartaccount');

$sAdminFormAction = '/{admin}/chartaccount/trialbalance/entry/index.php';

$code = Core_Array::getGet('code', '', 'strval');
$sc = Core_Array::getGet('sc', array(), 'array');

$title = Core::_('Chartaccount_Trialbalance_Entry.title', $code);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($title);

$oMainTab = Admin_Form_Entity::factory('Tab')->class('mainTab')->name('main');

if (!is_null(Core_Array::getPost('ajaxLoadTabContent')))
{
	$aJson = array();

	ob_start();

	Chartaccount_Trialbalance_Entry_Controller::setAdminFormController($oAdmin_Form_Controller);
	Chartaccount_Trialbalance_Entry_Controller::showContent($oMainTab, $_POST)->execute();

	Core::showJson(array('content' => ob_get_clean()));
}

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module_Abstract::factory($sModule))
	->pageTitle($title)
	;

ob_start();

$aOptions = !is_null(Core_Array::getGet('ajaxLoadTabContent'))
	? $_GET
	: array();

Chartaccount_Trialbalance_Entry_Controller::setAdminFormController($oAdmin_Form_Controller);
Chartaccount_Trialbalance_Entry_Controller::showContent($oMainTab, $aOptions);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$additionalParams = "code={$code}";

if (!is_null(Core_Array::getGet('ajaxLoadTabContent')))
{
	foreach ($_GET as $key => $value)
	{
		$additionalParams .= "&{$key}={$value}";
	}
}

foreach ($sc as $scId => $scValue)
{
	$additionalParams .= "&sc[{$scId}]={$scValue}";
}

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Chartaccount.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/chartaccount/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/chartaccount/index.php', NULL, NULL, '')
		)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Chartaccount_Trialbalance.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => '/{admin}/chartaccount/trialbalance/index.php', 'additionalParams' => ''))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => '/{admin}/chartaccount/trialbalance/index.php', 'additionalParams' => ''))
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($title)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $oAdmin_Form_Controller->getPath(), 'additionalParams' => $additionalParams))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $oAdmin_Form_Controller->getPath(), 'additionalParams' => $additionalParams))
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
	->title($title)
	->module($sModule)
	->execute();