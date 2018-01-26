<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 78;
$sAdminFormAction = '/admin/shop/delivery/condition/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$shop_id = intval(Core_Array::getGet('shop_id'));

$shop_delivery_condition_dir_id = Core_Array::getGet('shop_delivery_condition_dir_id', 0);

// Идентификатор группы товаров
$shop_group_id = intval(Core_Array::getGet('shop_group_id', 0));

// Идентификатор типа доставки
$shop_delivery_id = intval(Core_Array::getGet('delivery_id', 0));

// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);

// Текущая группа магазинов
$oShopDir = $oShop->Shop_Dir;

// Текущий тип доставки
$oDelivery = Core_Entity::factory('Shop_Delivery', $shop_delivery_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle = Core::_("Shop_Delivery_Condition.show_cond_of_delivery_title", $oDelivery->name))
	->pageTitle($sFormTitle);

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Delivery_Condition.show_cond_of_delivery'))
		->icon('fa fa-truck')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Delivery_Condition.show_cond_of_delivery_add'))
				->icon('fa fa-plus')
				->img('/admin/images/cond_of_delivery_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Delivery_Condition.show_cond_of_delivery_import'))
				->icon('fa fa-download')
				->img('/admin/images/cond_of_delivery_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'import', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'import', NULL, 0, 0)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Delivery_Condition_Dir.menu_caption'))
		->icon('fa fa-folder-o')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Delivery_Condition_Dir.menu_caption_add'))
				->icon('fa fa-plus')
				->img('/admin/images/cond_of_delivery_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop.menu'))
	->href(
		$oAdmin_Form_Controller->getAdminLoadHref(
			$sShopItemFormPath = '/admin/shop/index.php', NULL, NULL, ''
		)
	)
	->onclick(
		$oAdmin_Form_Controller->getAdminLoadAjax(
			$sShopItemFormPath, NULL, NULL, ''
		)
	)
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
			->href
			(
				$oAdmin_Form_Controller->getAdminLoadHref
				(
					$sShopItemFormPath, NULL, NULL, $additionalParams
				)
			)
			->onclick
			(
				$oAdmin_Form_Controller->getAdminLoadAjax
				(
					$sShopItemFormPath, NULL, NULL, $additionalParams
				)
			)
		;
	}while ($oShopBreadCrumbDir = $oShopBreadCrumbDir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на форму списка групп товаров и товаров
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href
		(
			$oAdmin_Form_Controller->getAdminLoadHref
			(
				'/admin/shop/item/index.php',
				NULL,
				NULL,
				$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0"
			)
		)
		->onclick
		(
			$oAdmin_Form_Controller->getAdminLoadAjax
			(
				'/admin/shop/item/index.php',
				NULL,
				NULL,
				$sAdditionalParams
			)
		)
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
			->href
			(
				$oAdmin_Form_Controller->getAdminLoadHref
				(
					$sShopItemFormPath, NULL, NULL, $additionalParams
				)
			)
			->onclick
			(
				$oAdmin_Form_Controller->getAdminLoadAjax
				(
					$sShopItemFormPath, NULL, NULL, $additionalParams
				)
			);
	} while ($oShopGroup = $oShopGroup->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на форму списка типов доставок
$oAdmin_Form_Entity_Breadcrumbs
	->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_("Shop_Delivery.show_type_of_delivery_title"))
		->href
		(
			$oAdmin_Form_Controller->getAdminLoadHref
			(
				$sPrevFormPath = '/admin/shop/delivery/index.php',
				NULL,
				NULL,
				$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"
			)
		)
		->onclick
		(
			$oAdmin_Form_Controller->getAdminLoadAjax
			(
				$sPrevFormPath,
				NULL,
				NULL,
				$sAdditionalParams
			)
		)
	)
	// Добавляем крошку на текущую форму
	->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href
		(
			$oAdmin_Form_Controller->getAdminLoadHref
			(
				$oAdmin_Form_Controller->getPath(),
				NULL,
				NULL,
				$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&delivery_id={$shop_delivery_id}"
			)
		)
		->onclick
		(
			$oAdmin_Form_Controller->getAdminLoadAjax
			(
				$oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams
			)
		)
);

if ($shop_delivery_condition_dir_id)
{
	$oShop_Delivery_Condition_Dir = Core_Entity::factory('Shop_Delivery_Condition_Dir', $shop_delivery_condition_dir_id);

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop_Delivery_Condition_Dir->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
				$sAdminFormAction, NULL, NULL, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&delivery_id={$shop_delivery_id}&shop_delivery_condition_dir_id={$shop_delivery_condition_dir_id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
				$sAdminFormAction, NULL, NULL, "shop_id={$shop_id}&shop_group_id={$shop_group_id}&delivery_id={$shop_delivery_id}&shop_delivery_condition_dir_id={$shop_delivery_condition_dir_id}"
		));
	}while ($oShop_Delivery_Condition_Dir = $oShop_Delivery_Condition_Dir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

 // Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Delivery_Condition_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Delivery_Condition_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Delivery_Condition_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Delivery_Condition_Controller_Edit);
}

// Действие "Загрузка списка местоположений"
$oAdminFormActionLoadCountryLocationsList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadList2');

if ($oAdminFormActionLoadCountryLocationsList && $oAdmin_Form_Controller->getAction() == 'loadList2')
{
	$oStructureControllerCountryLocationsList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadCountryLocationsList
	);

	$oStructureControllerCountryLocationsList
		->model(Core_Entity::factory('Shop_Country_Location'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_country_id', '=', Core_Array::getGet('list_id')))
		);

	$oAdmin_Form_Controller->addAction($oStructureControllerCountryLocationsList);
}

// Действие "Загрузка списка городов"
$oAdminFormActionLoadCountryLocationCitiesList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadList3');

if ($oAdminFormActionLoadCountryLocationCitiesList && $oAdmin_Form_Controller->getAction() == 'loadList3')
{
	$oStructureControllerCountryLocationCitiesList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadCountryLocationCitiesList
	);

	$oStructureControllerCountryLocationCitiesList
		->model(Core_Entity::factory('Shop_Country_Location_City'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_country_location_id', '=', Core_Array::getGet('list_id')))
		);

	$oAdmin_Form_Controller->addAction($oStructureControllerCountryLocationCitiesList);
}

// Действие "Загрузка списка районов"
$oAdminFormActionLoadCountryLocationCitiesList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadList4');

if ($oAdminFormActionLoadCountryLocationCitiesList && $oAdmin_Form_Controller->getAction() == 'loadList4')
{
	$oStructureControllerCountryLocationCitiesList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadCountryLocationCitiesList
	);

	$oStructureControllerCountryLocationCitiesList
		->model(Core_Entity::factory('Shop_Country_Location_City_Area'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_country_location_city_id', '=', Core_Array::getGet('list_id')))
		);

	$oAdmin_Form_Controller->addAction($oStructureControllerCountryLocationCitiesList);
}

// Действие "Импорт условий доставки"
$oAdminFormActionImport = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('import');

if ($oAdminFormActionImport && $oAdmin_Form_Controller->getAction() == 'import')
{
	$oControllerImport = Admin_Form_Action_Controller::factory(
		'Shop_Delivery_Condition_Controller_Import', $oAdminFormActionImport
	);

	$oControllerImport
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerImport);
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
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Shop_Delivery_Condition_Dir'));
// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);
$oAdmin_Form_Dataset
	->addCondition(array('where' => array('parent_id', '=', $shop_delivery_condition_dir_id)))
	->addCondition(array('where' => array('shop_delivery_id', '=', $shop_delivery_id)))
	->changeField('min_weight', 'editable', 0)
	->changeField('max_weight', 'editable', 0)
	->changeField('min_price', 'editable', 0)
	->changeField('max_price', 'editable', 0)
	->changeField('price', 'editable', 0)
	->changeField('currency_name', 'editable', 0)
;

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Shop_Delivery_Condition'));
// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);
$oAdmin_Form_Dataset
	->addCondition(array('select' => array('shop_delivery_conditions.*', array('shop_currencies.name', 'currency_name'))))
	->addCondition(array('leftJoin' => array('shop_currencies', 'shop_currencies.id', '=', 'shop_delivery_conditions.shop_currency_id')))
	->addCondition(array('where' => array('shop_delivery_id', '=', $shop_delivery_id)))
	->addCondition(array('where' => array('shop_delivery_condition_dir_id', '=', $shop_delivery_condition_dir_id)))
	->changeField('name', 'type', 1)
;

$oAdmin_Form_Controller->addExternalReplace('{shop_id}', $shop_id);
$oAdmin_Form_Controller->addExternalReplace('{shop_group_id}', $shop_group_id);
$oAdmin_Form_Controller->addExternalReplace('{delivery_id}', $shop_delivery_id);

// Показ формы
$oAdmin_Form_Controller->execute();