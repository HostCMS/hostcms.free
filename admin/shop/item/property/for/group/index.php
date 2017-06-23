<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 71;
$sAdminFormAction = '/admin/shop/item/property/for/group/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$shop_id = intval(Core_Array::getGet('shop_id'));
$shop_group_id = intval(Core_Array::getGet('shop_group_id', 0));

$oShop = Core_Entity::factory('Shop')->find($shop_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Shop_Item.properties_item_for_groups_root_title'))
	->pageTitle(Core::_('Shop_Item.properties_item_for_groups_root_title'));


// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Строка навигации
$property_dir_id = intval(Core_Array::getGet('property_dir_id', 0));

$sShopDirPath = '/admin/shop/index.php';

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sShopDirPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sShopDirPath, NULL, NULL, '')
	)
);

// Путь по разделам информационных систем
if ($oShop->shop_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oShopDir = Core_Entity::factory('Shop_Dir')->find($oShop->shop_dir_id);

	if (!is_null($oShopDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'shop_dir_id=' . intval($oShopDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oShopDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sShopDirPath, NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sShopDirPath, NULL, NULL, $additionalParams)
				);
		} while($oShopDir = $oShopDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$additionalParams = 'shop_id=' . $shop_id;
$sShopPath = '/admin/shop/item/index.php';

// Ссылка на название ИС
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sShopPath, NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sShopPath, NULL, NULL, $additionalParams)
	)
);

// Путь по группам информационных элементов
if ($shop_group_id)
{
	$oShopGroup = Core_Entity::factory('Shop_Group')->find($shop_group_id);

	if (!is_null($oShopGroup->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'shop_id=' . $shop_id . '&shop_group_id=' . $oShopGroup->id;

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oShopGroup->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sShopPath, NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sShopPath, NULL, NULL, $additionalParams)
				);
		} while($oShopGroup = $oShopGroup->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$additionalParams = 'shop_id=' . $shop_id . '&shop_group_id=' . $shop_group_id;

// Корневой раздел дополнительных свойств
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Item.properties_item_for_groups_link'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
	)

);

// Путь по разделам дополнительных свойств
if ($property_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oProperty_Dir = Core_Entity::factory('Property_Dir')->find($property_dir_id);

	if (!is_null($oProperty_Dir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'shop_id=' . $shop_id . '&shop_group_id=' . $shop_group_id . '&property_dir_id=' . intval($oProperty_Dir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oProperty_Dir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while($oProperty_Dir = $oProperty_Dir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

// Добавляем все хлебные крошки контроллеру
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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Item_Property_Group_Dir')
);

// Ограничение источника 0
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('property_dirs.*'))
)->addCondition(
	array('join' => array('shop_item_property_dirs', 'shop_item_property_dirs.property_dir_id', '=', 'property_dirs.id'))
)->addCondition(
	array('where' =>
		array('parent_id', '=', $property_dir_id)
	)
)->addCondition(
	array('where' =>
		array('shop_item_property_dirs.shop_id', '=', $shop_id)
	)
)

->changeField('name', 'type', 4)
->changeField('name', 'link', "/admin/shop/item/property/for/group/index.php?shop_id=" . $shop_id . "&shop_group_id=" . $shop_group_id . "&property_dir_id={id}")
->changeField('name', 'onclick', "$.adminLoad({path: '/admin/shop/item/property/for/group/index.php', additionalParams: 'shop_id=" . $shop_id . "&shop_group_id=" . $shop_group_id ."&property_dir_id={id}', windowId: '{windowId}'}); return false")
;

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Item_Property_Group')
);

// Ограничение источника 1
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('properties.*', array('NOT(ISNULL(shop_item_property_for_groups.id))', 'enable')))
)->addCondition(
	array('join' => array('shop_item_properties', 'shop_item_properties.property_id', '=', 'properties.id'))
)
->addCondition(
	array('leftJoin' => array('shop_item_property_for_groups', 'shop_item_property_for_groups.shop_item_property_id', '=', 'shop_item_properties.id',
	array(
			array('AND' => array('shop_item_property_for_groups.shop_id ', '=', $shop_id)),
			array('AND' => array('shop_item_property_for_groups.shop_group_id', '=', $shop_group_id))
		)

	))
)
->addCondition(
	array('where' =>
		array('property_dir_id', '=', $property_dir_id)
	)
)->addCondition(
	array('where' =>
		array('shop_item_properties.shop_id', '=', $shop_id)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addExternalReplace('{shop_id}', $shop_id);
$oAdmin_Form_Controller->addExternalReplace('{shop_group_id}', $shop_group_id);

// Показ формы
$oAdmin_Form_Controller->execute();