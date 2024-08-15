<?php
/**
 * Chartaccount.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'chartaccount');

// Код формы
$iAdmin_Form_Id = 358;
$sAdminFormAction = '/admin/chartaccount/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Chartaccount.title'))
	->pageTitle(Core::_('Chartaccount.title'));

if (Core_Array::getPost('load_subcounts'))
{
// var_dump($_POST);

	$aJSON = array(
		'html' => ''
	);

	$chartaccount_id = Core_Array::getPost('chartaccount_id', 0, 'int');
	$prefix = Core_Array::getPost('prefix', '', 'trim');

	if ($chartaccount_id)
	{
		$oChartaccount = Core_Entity::factory('Chartaccount')->getById($chartaccount_id);

		if (!is_null($oChartaccount))
		{
			$aSubcounts = array();

			for ($i = 0; $i < 3; $i++)
			{
				$subcountName = 'sc' . $i;
				$subcountType = 'sc' . $i . '_type';

				$aSubcounts[Core_Array::getPost($subcountType, 0, 'int')] = Core_Array::getPost($subcountName, '', 'trim');

				/*if ($oChartaccount->$subcountName)
				{
					$aSubcounts[$oChartaccount->$subcountName] = Core_Array::getPost($subcountName, 0, 'trim');
				}*/
			}

			ob_start();

			$parentObject = Admin_Form_Entity::factory('Div')->controller($oAdmin_Form_Controller);
			Chartaccount_Controller::showSubcounts($aSubcounts, $chartaccount_id, $parentObject, $oAdmin_Form_Controller, $_REQUEST, $prefix);
			$parentObject->execute();

			$aJSON['html'] = ob_get_clean();
		}
	}

	Core::showJson($aJSON);
}

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
		->name(Core::_('Chartaccount_Trialbalance.title'))
		->icon('fa-solid fa-scale-balanced')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => '/admin/chartaccount/trialbalance/index.php'))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => '/admin/chartaccount/trialbalance/index.php'))
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Chartaccount.journals'))
		->icon('fa-solid fa-bars')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Chartaccount_Operation.title'))
				->icon('fa-solid fa-book')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref(array('path' => '/admin/chartaccount/operation/index.php'))
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => '/admin/chartaccount/operation/index.php'))
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Chartaccount_Entry.menu'))
				->icon('fa-solid fa-book-open')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref(array('path' => '/admin/chartaccount/entry/index.php'))
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => '/admin/chartaccount/entry/index.php'))
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Chartaccount_Closure_Period.menu'))
				->icon('fa-solid fa-book-bookmark')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref(array('path' => '/admin/chartaccount/closure/period/index.php'))
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => '/admin/chartaccount/closure/period/index.php'))
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Chartaccount_Cashflow.title'))
		->icon('fa-solid fa-arrow-right-arrow-left')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sCashflowsFormPath = '/admin/chartaccount/cashflow/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCashflowsFormPath, NULL, NULL, '')
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Chartaccount.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/chartaccount/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/chartaccount/index.php', NULL, NULL, '')
		)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oChartaccount_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Chartaccount_Controller_Edit', $oAdmin_Form_Action
	);

	$oChartaccount_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oChartaccount_Controller_Edit);
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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Chartaccount')
);

// Забалансовый всегда внизу
$oAdmin_Form_Dataset->addCondition(array('orderBy' => array('off_balance', 'ASC')));

// Доступ только к своим
// $oUser = Core_Auth::getCurrentUser();
// !$oUser->superuser && $oUser->only_access_my_own
// 	&& $oAdmin_Form_Dataset->addUserConditions();

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addFilter('user_id', array($oAdmin_Form_Controller, '_filterCallbackUser'));

// Показ формы
$oAdmin_Form_Controller->execute();