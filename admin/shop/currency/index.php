<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 56;
$sAdminFormAction = '/admin/shop/currency/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Shop_Currency.show_currency_title'))
	->pageTitle(Core::_('Shop_Currency.show_currency_title'));

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

$oCurrenciesMenu = Admin_Form_Entity::factory('Menu')
	->name(Core::_('Shop_Currency.update_currency'))
	->icon('fa-solid fa-rotate');

$aConfig = Core_Config::instance()->get('shop_currency_config')
	+ array(
		'cbrf' => array(
			'driver' => 'cbrf',
			'name' => 'ЦБ РФ'
		),
		'floatrates' => array(
			'driver' => 'floatrates',
			'name' => 'Exchange Rates for USD'
		)
	);

foreach ($aConfig as $key => $aCurrencyConfig)
{
	if (isset($aCurrencyConfig['name']))
	{
		$oCurrenciesMenu->add(
			Admin_Form_Entity::factory('Menu')
				->name($aCurrencyConfig['name'])
				->icon('fa fa-shopping-cart')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'update', $key, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'update', $key, 0, 0)
				)
		);
	}
}

$oAdmin_Form_Entity_Menus->add($oCurrenciesMenu);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sShopFormPath = '/admin/shop/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sShopFormPath, NULL, NULL, '')
		)
);

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Currency.show_currency_title'))
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
	$ShopCurrency_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Currency_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$ShopCurrency_Controller_Edit
		->addEntity(
			$oAdmin_Form_Entity_Breadcrumbs
		);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($ShopCurrency_Controller_Edit);
}

// Действие обновления
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('update');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'update')
{
	$Shop_Currency_Controller_Update = Admin_Form_Action_Controller::factory(
		'Shop_Currency_Controller_Update', $oAdmin_Form_Action
	);

	$oAdmin_Form_Controller->addAction($Shop_Currency_Controller_Update);
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

	// Добавляем контроллер редактирования контроллеру формы
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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Currency')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();