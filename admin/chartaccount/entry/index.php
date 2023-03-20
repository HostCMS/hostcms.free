<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'chartaccount');

// Код формы
$iAdmin_Form_Id = 364;
$sAdminFormAction = '/admin/chartaccount/entry/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$sFormTitle = Core::_('Chartaccount_Entry.title');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

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
		->name($sFormTitle)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $oAdmin_Form_Controller->getPath()))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $oAdmin_Form_Controller->getPath()))
	)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Chartaccount_Entry')
);

$oAdmin_Form_Dataset ->addCondition(
	array('select' =>
		array('chartaccount_entries.*')
	)
)->addCondition(
	array('join' =>
		array('company_sites', 'company_sites.company_id', '=', 'chartaccount_entries.company_id')
	)
)->addCondition(array('where' => array('company_sites.site_id', '=', CURRENT_SITE)));

// Фильтр по дебету
if (isset($oAdmin_Form_Controller->request['admin_form_filter_2151']) && ($oAdmin_Form_Controller->request['admin_form_filter_2151'] != '')
	|| isset($oAdmin_Form_Controller->request['topFilter_2151']) && $oAdmin_Form_Controller->request['topFilter_2151'] != ''
)
{
	$value = isset($oAdmin_Form_Controller->request['topFilter_2151'])
		? $oAdmin_Form_Controller->request['topFilter_2151']
		: $oAdmin_Form_Controller->request['admin_form_filter_2151'];

	if ($value != 'HOST_CMS_ALL')
	{
		$mFilterValue = $oAdmin_Form_Controller->convertLike(strval($value));

		$oAdmin_Form_Dataset->addCondition(
			array('select' => array('chartaccount_entries.*'))
		)
		->addCondition(
			array(
				'where' => array('chartaccount_entries.dchartaccount_id', '=', $mFilterValue)
			)
		);
	}
}

// Фильтр по кредиту
if (isset($oAdmin_Form_Controller->request['admin_form_filter_2152']) && ($oAdmin_Form_Controller->request['admin_form_filter_2152'] != '')
	|| isset($oAdmin_Form_Controller->request['topFilter_2152']) && $oAdmin_Form_Controller->request['topFilter_2152'] != ''
)
{
	$value = isset($oAdmin_Form_Controller->request['topFilter_2152'])
		? $oAdmin_Form_Controller->request['topFilter_2152']
		: $oAdmin_Form_Controller->request['admin_form_filter_2152'];

	if ($value != 'HOST_CMS_ALL')
	{
		$mFilterValue = $oAdmin_Form_Controller->convertLike(strval($value));

		$oAdmin_Form_Dataset->addCondition(
			array('select' => array('chartaccount_entries.*'))
		)
		->addCondition(
			array(
				'where' => array('chartaccount_entries.cchartaccount_id', '=', $mFilterValue)
			)
		);
	}
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$aChartaccounts = Core_Entity::factory('Chartaccount')->findAll(FALSE);
$aList = array();
foreach ($aChartaccounts as $oChartaccount)
{
	$aList[$oChartaccount->id] = array('value' => $oChartaccount->code);
}

$oAdmin_Form_Dataset
	->changeField('debit', 'type', 8)
	->changeField('debit', 'list', $aList)
	->changeField('credit', 'type', 8)
	->changeField('credit', 'list', $aList);

// Показ формы
$oAdmin_Form_Controller->execute();