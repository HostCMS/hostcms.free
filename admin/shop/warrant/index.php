<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 348;
$sAdminFormAction = '/admin/shop/warrant/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$sFormTitle = Core::_('Shop_Warrant.title');

// Идентификатор магазина
$shop_id = Core_Array::getGet('shop_id', 0, 'int');

// Идентификатор группы товаров
$shop_group_id = Core_Array::getGet('shop_group_id', 0, 'int');

// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);

// Текущая группа магазинов
$oShopDir = $oShop->Shop_Dir;

$printlayout_id = intval(Core_Array::getGet('printlayout_id', 0));

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Warrant.warrant_menu'))
		->icon('fa-solid fa-cash-register')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warrant.add_incoming_cash_warrant'))
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&type=1")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&type=1")
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warrant.add_account_cash_warrant'))
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&type=0")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&type=0")
				)
		)
)
->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Warrant.pays_menu'))
		->icon('fa-solid fa-building-columns')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warrant.add_incoming_pay'))
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&type=2")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&type=2")
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Warrant.add_writeoff_pay'))
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&type=3")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&type=3")
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Представитель класса хлебных крошек
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath = '/admin/shop/index.php', NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, ''))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Добавляем крошки для групп магазинов
if ($oShopDir->id)
{
	$aBreadcrumbs = array();

	$oShopBreadCrumbDir = $oShopDir;

	do
	{
		$additionalParams = "shop_dir_id={$oShopBreadCrumbDir->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopBreadCrumbDir->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, $additionalParams));
	} while ($oShopBreadCrumbDir = $oShopBreadCrumbDir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
	}
}

// Добавляем крошку на форму списка групп товаров и товаров
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, $sAdditionalParams))
);

// Крошки строим только если: мы не в корне или идет редактирование
if ($shop_group_id)
{
	$oShopGroup = Core_Entity::factory('Shop_Group', $shop_group_id);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	$sShopItemFormPath = '/admin/shop/item/index.php';

	do
	{
		$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroup->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, $additionalParams));
	} while ($oShopGroup = $oShopGroup->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
	}
}

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Warrant_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Warrant_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Warrant_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Warrant_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Пересчитать"
$oAdminFormActionRecount = $oAdmin_Form->Admin_Form_Actions->getByName('recountAmount');

if ($oAdminFormActionRecount && $oAdmin_Form_Controller->getAction() == 'recountAmount')
{
	$oShop_Warrant_Controller_Recount = Admin_Form_Action_Controller::factory(
		'Shop_Warrant_Controller_Recount', $oAdminFormActionRecount
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShop_Warrant_Controller_Recount);
}

$oAdminFormActionChangeDefaultStatus = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionChangeDefaultStatus && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$Admin_Form_Action_Controller_Type_Apply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionChangeDefaultStatus
	);

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Apply);
}

$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('print');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'print')
{
	$Shop_Warrant_Controller_Print = Admin_Form_Action_Controller::factory(
		'Shop_Warrant_Controller_Print', $oAdmin_Form_Action
	);

	$Shop_Warrant_Controller_Print
		->title(Core::_('Shop_Warrant.title'))
		->printlayout($printlayout_id);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Warrant_Controller_Print);
}

$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('sendMail');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'sendMail')
{
	$Shop_Warrant_Controller_Print = Admin_Form_Action_Controller::factory(
		'Shop_Warrant_Controller_Print', $oAdmin_Form_Action
	);

	$Shop_Warrant_Controller_Print
		->printlayout($printlayout_id)
		->send(TRUE);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Warrant_Controller_Print);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Warrant')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset->addCondition(
	array(
		'where' => array('shop_id', '=', $shop_id)
	)
);

$oAdmin_Form_Controller
	->addExternalReplace('{shop_group_id}', $shop_group_id)
	->addExternalReplace('{shop_id}', $shop_id)
	;

if (isset($oAdmin_Form_Controller->request['admin_form_filter_2007'])
		&& $oAdmin_Form_Controller->request['admin_form_filter_2007'] != ''
	|| isset($oAdmin_Form_Controller->request['topFilter_2007'])
		&& $oAdmin_Form_Controller->request['topFilter_2007'] != ''
)
{
	$value = isset($oAdmin_Form_Controller->request['admin_form_filter_2007'])
		? $oAdmin_Form_Controller->request['admin_form_filter_2007']
		: $oAdmin_Form_Controller->request['topFilter_2007'];

	// var_dump($oAdmin_Form_Controller->request['admin_form_filter_2007']);

	$aTmpValue = explode('_', $value);

	if (isset($aTmpValue[1]))
	{
		$value = intval($aTmpValue[1]);

		$oAdmin_Form_Dataset->addCondition(
			array(
				'select' => array(
					'shop_warrants.*'
				)
			)
		)->addCondition(
			array('join' => array('chartaccounts', 'shop_warrants.chartaccount_id', '=', 'chartaccounts.id', array(
					array('AND' => array('chartaccounts.deleted', '=', 0))
				))
			)
		)
		->addCondition(
			array('open' => array())
		)
			->addCondition(
				array('where' => array('shop_warrants.sc0', '=', $value))
			)
			->addCondition(
				array('where' => array('chartaccounts.sc0', '=', 2))
			)
			->addCondition(
				array('setOr' => array())
			)
			->addCondition(
				array('where' => array('shop_warrants.sc1', '=', $value))
			)
			->addCondition(
				array('where' => array('chartaccounts.sc1', '=', 2))
			)
			->addCondition(
				array('setOr' => array())
			)
			->addCondition(
				array('where' => array('shop_warrants.sc2', '=', $value))
			)
			->addCondition(
				array('where' => array('chartaccounts.sc2', '=', 2))
			)
		->addCondition(
			array('close' => array())
		);
	}
}

// Только если идет фильтрация, Договор, фильтр по тексту
/*if (isset($oAdmin_Form_Controller->request['admin_form_filter_2062']) && $oAdmin_Form_Controller->request['admin_form_filter_2062'] != '')
{
	$oAdmin_Form_Dataset->addCondition(
		array('select' => array('shop_warrants.*', array('siteuser_company_contracts.name', 'siteuserCompanyContract')))
	)
	->addCondition(
		array(
			'leftJoin' => array('siteuser_company_contracts', 'siteuser_company_contracts.id', '=', 'shop_warrants.siteuser_company_contract_id')
		)
	);
}*/

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// $oAdmin_Form_Controller->addFilterCallback('dataSiteuserCompanyName', 'dataSiteuserCompanyName');
// $oAdmin_Form_Controller->addFilter('user_id', array($oAdmin_Form_Controller, '_filterCallbackUser'));

$oAdmin_Form_Controller->addFilter('counterparty', array($oAdmin_Form_Controller, '_filterCallbackSiteuserCompany'));
$oAdmin_Form_Controller->addFilter('dataCounterparty', array($oAdmin_Form_Controller, '_filterCallbackSiteuserCompany'));

Core_Event::attach('Admin_Form_Controller.onAfterShowContent', array('User_Controller', 'onAfterShowContentPopover'), array($oAdmin_Form_Controller));
Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('User_Controller', 'onAfterRedeclaredPrepareForm'));

if (Core::moduleIsActive('siteuser'))
{
	Core_Event::attach('Admin_Form_Controller.onAfterShowContent', array('Siteuser_Controller', 'onAfterShowContentPopover'), array($oAdmin_Form_Controller));
}

// Показ формы
$oAdmin_Form_Controller->execute();