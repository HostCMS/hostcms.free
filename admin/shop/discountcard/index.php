<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 249;
$sFormAction = '/{admin}/shop/discountcard/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShop = Core_Entity::factory('Shop', intval(Core_Array::getGet('shop_id', 0)));
$oShopGroup = Core_Entity::factory('Shop_Group', intval(Core_Array::getGet('shop_group_id', 0)));
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_('Shop_Discountcard.title');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

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
	)
	->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Discountcard.levels'))
			->icon('fa fa-bars')
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/discountcard/level/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}")
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/discountcard/level/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}")
			)
	)
	->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Discountcard.types'))
			->icon('fa fa-circle icon-separator')
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/discountcard/bonus/type/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}")
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/discountcard/bonus/type/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}")
			)
	)
	->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Discountcard.export'))
			->icon('fa fa-upload')
			->target('_blank')
			->href(
				$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportDiscountcards', NULL, 0, 0)
			)
	)
	->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Discountcard.import'))
			->icon('fa fa-download')
			->href(
				$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'importDiscountcards', NULL, 0, 0)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'importDiscountcards', NULL, 0, 0)
			)
	)
;

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Хлебные крошки
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/index.php'))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/index.php'))
);

// Крошки по директориям магазинов
if ($oShopDir->id)
{
	$oShopDirBreadcrumbs = $oShopDir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopDirBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"));
	} while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
);

// Крошки по группам товаров
if ($oShopGroup->id)
{
	$oShopGroupBreadcrumbs = $oShopGroup;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"));
	} while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Последняя крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath()))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath()))
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие "Редактировать"
$oAdmin_Form_Action_Edit = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action_Edit && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Discountcard_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Discountcard_Controller_Edit', $oAdmin_Form_Action_Edit
	);
	$Shop_Discountcard_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	$oAdmin_Form_Controller->addAction($Shop_Discountcard_Controller_Edit);
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

// Действие импорта
$oAdminFormActionEdit = $oAdmin_Form->Admin_Form_Actions->getByName('importDiscountcards');

if ($oAdminFormActionEdit && $oAdmin_Form_Controller->getAction() == 'importDiscountcards')
{
	$oShopDiscountcardImport = Admin_Form_Action_Controller::factory(
		'Shop_Discountcard_Import_Controller', $oAdminFormActionEdit
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShopDiscountcardImport);

	// Крошки при редактировании
	$oShopDiscountcardImport->addEntity($oAdmin_Form_Entity_Breadcrumbs);
}

// Действие экспорта
$oAdminFormActionExport = $oAdmin_Form->Admin_Form_Actions->getByName('exportDiscountcards');

if ($oAdminFormActionExport && $oAdmin_Form_Controller->getAction() == 'exportDiscountcards')
{
	$Shop_Discountcard_Export_Controller = new Shop_Discountcard_Export_Controller($oShop);
	$Shop_Discountcard_Export_Controller->execute();
}

if (!Core::moduleIsActive('siteuser'))
{
	$oAdmin_Form_Controller->addMessage(Core_Message::get(Core::_('Shop_Discountcard.backendWarning'), 'error'));
}
else
{
	// Источник данных 0
	$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
		Core_Entity::factory('Shop_Discountcard')
	);

	// Доступ только к своим
	$oUser = Core_Auth::getCurrentUser();
	!$oUser->superuser && $oUser->only_access_my_own
		&& $oAdmin_Form_Dataset->addUserConditions();

	$oAdmin_Form_Dataset
		->addCondition(
			array(
				'select' => array(
					'shop_discountcards.*', array('siteusers.login', 'dataLogin')
				)
			)
		)
		->addCondition(array('where' => array('shop_id', '=', $oShop->id)))
		->addCondition(
			array('leftJoin' => array('siteusers', 'shop_discountcards.siteuser_id', '=', 'siteusers.id',
				array(
					array('AND' => array('siteusers.deleted', '=', 0))
				))
			)
		);

	if (isset($oAdmin_Form_Controller->request['admin_form_filter_1399'])
		&& $oAdmin_Form_Controller->request['admin_form_filter_1399'] != ''
	|| isset($oAdmin_Form_Controller->request['topFilter_1399'])
		&& $oAdmin_Form_Controller->request['topFilter_1399'] != ''
	|| $oAdmin_Form_Controller->sortingFieldId == 1399
	)
	{
		$oAdmin_Form_Dataset->addCondition(
			array(
				'select' => array(
					'shop_discountcards.*', array('siteusers.login', 'dataLogin'), array(Core_QueryBuilder::expression('CONCAT_WS(" ", GROUP_CONCAT(`siteuser_companies`.`name`), GROUP_CONCAT(CONCAT_WS(" ", `siteuser_people`.`surname`, `siteuser_people`.`name`, `siteuser_people`.`patronymic`)))'), 'dataSiteuser'),
				)
			)
		)
		->addCondition(
			array('leftJoin' => array('siteuser_companies', 'siteusers.id', '=', 'siteuser_companies.siteuser_id', array(
					array('AND' => array('siteuser_companies.deleted', '=', 0))
				))
			)
		)
		->addCondition(
			array('leftJoin' => array('siteuser_people', 'siteusers.id', '=', 'siteuser_people.siteuser_id',
				array(
					array('AND' => array('siteuser_people.deleted', '=', 0))
				))
			)
		)
		->addCondition(
			array('groupBy' => array('siteusers.id'))
		);
	}

	// Список значений для фильтра и поля
	$aShop_Discountcard_Levels = $oShop->Shop_Discountcard_Levels->findAll();
	$sList = "0=…\n";
	foreach ($aShop_Discountcard_Levels as $oShop_Discountcard_Level)
	{
		$sList .= "{$oShop_Discountcard_Level->id}={$oShop_Discountcard_Level->name}\n";
	}

	$oAdmin_Form_Dataset
		->changeField('shop_discountcard_level_id', 'type', 8)
		->changeField('shop_discountcard_level_id', 'list', trim($sList));

	$oAdmin_Form_Controller->addExternalReplace('{shop_group_id}', $oShopGroup->id);

	$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);
}

// Показ формы
$oAdmin_Form_Controller->execute();