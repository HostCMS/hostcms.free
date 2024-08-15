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
$iAdmin_Form_Id = 75;
$sAdminFormAction = '/admin/shop/order/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$shop_id = Core_Array::getGet('shop_id', 0, 'int');

// Идентификатор группы товаров
$shop_group_id = Core_Array::getGet('shop_group_id', 0, 'int');

// Текущий магазин
$oShop = Core_Entity::factory('Shop')->find($shop_id);

// Текущая группа магазинов
$oShopDir = Core_Entity::factory('Shop_Dir', $oShop->shop_dir_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle = Core::_('Shop_Order.show_order_title', $oShop->name))
	->pageTitle($sFormTitle);

$oAdmin_Form_Controller->showTopFilterTags = 'shop_order';

$windowId = $oAdmin_Form_Controller->getWindowId();

$siteuser_id = intval(Core_Array::getGet('siteuser_id'));
if ($siteuser_id && $windowId != 'id_content')
{
	$oAdmin_Form_Controller
		->Admin_View(
			Admin_View::getClassName('Admin_Internal_View')
		)
		->addView('order', 'Siteuser_Controller_Order')
		->view('order');
}

// Shop Order Print Forms
$shop_print_form_id = intval(Core_Array::getGet('shop_print_form_id'));
if ($shop_print_form_id)
{
	$shop_order_id = intval(Core_Array::getGet('shop_order_id'));

	if ($shop_order_id)
	{
		$oShop_Print_Form = Core_Entity::factory('Shop_Print_Form', $shop_print_form_id);
		$oShop_Order = Core_Entity::factory('Shop_Order')->find($shop_order_id);

		Shop_Print_Form_Handler::factory($oShop_Print_Form)
			->shopOrder($oShop_Order)
			->execute();
	}
	exit();
}

$oUser = Core_Auth::getCurrentUser();

if (!is_null(Core_Array::getPost('showPopover')))
{
	$aJSON = array(
		'html' => ''
	);

	$shop_order_id = Core_Array::getPost('shop_order_id', 0, 'int');

	$oShop_Order = Core_Entity::factory('Shop_Order')->getById($shop_order_id);

	if (!is_null($oShop_Order) && $oUser->checkObjectAccess($oShop_Order))
	{
		$aJSON['html'] = $oShop_Order->orderPopover();
	}

	Core::showJson($aJSON);
}

if (Core_Array::getPost('recalcFormula'))
{
	$aJSON = array(
		'status' => 'error'
	);

	$price = Core_Array::getPost('shop_delivery_condition_price');

	if (!is_null($price))
	{
		$shop_order_id = Core_Array::getPost('shop_order_id', 0, 'int');
		$oShop_Order = Core_Entity::factory('Shop_Order')->getById($shop_order_id);

		if (!is_null($oShop_Order) && $oUser->checkObjectAccess($oShop_Order))
		{
			$shop_delivery_condition_name = Core_Array::getPost('shop_delivery_condition_name');


			$oShop_Order_Item_Delivery = $oShop_Order->Shop_Order_Items->getByType(1);
			if (is_null($oShop_Order_Item_Delivery))
			{
				$oShop_Order_Item_Delivery = Core_Entity::factory('Shop_Order_Item');
				$oShop_Order_Item_Delivery->shop_order_id = $oShop_Order->id;
				$oShop_Order_Item_Delivery->type = 1;
			}

			$oShop_Order_Item_Delivery->price = $price;
			$oShop_Order_Item_Delivery->quantity = 1;
			$oShop_Order_Item_Delivery->name = Core::_('Shop_Delivery.delivery_with_condition', $oShop_Order->Shop_Delivery->name, $shop_delivery_condition_name);
			$oShop_Order_Item_Delivery->save();

			$aJSON = array(
				'status' => 'success',
				'shop_order_item_id' => $oShop_Order_Item_Delivery->id,
				'message' => Core::_('Shop_Order.recalc_delivery_success')
			);
		}
	}
	else
	{
		$aJSON['message'] = 'Delivery cost has not been calculated!';
	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

if ($siteuser_id)
{
	$aTmp = array();

	$aShops = Core_Entity::factory('Shop')->getAllBySite_id(CURRENT_SITE);
	foreach ($aShops as $oShop)
	{
		$aTmp[] = '<option value="' . $oShop->id . '">' . htmlspecialchars($oShop->name) . '</option>';
	}

	$sOptions = implode('', $aTmp);

	$oAdmin_Form_Controller->addEntity(
		Admin_Form_Entity::factory('Code')->html('
			<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="gridSystemModalLabel">' . Core::_('Shop_Order.select_shop') . '</h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-xs-12">
									<select id="shop_id" name="shop_id" style="width: 100%;">'
										. $sOptions .
									'</select>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary select-shop">' . Core::_('Shop_Order.select_shop_button') . '</button>
						</div>
					</div>
				</div>
			</div>
			<script>
				$(function (){
					$("#' . $windowId . ' .select-shop").on("click", function(){
						mainFormLocker.unlock();

						var shop_id = parseInt($("#' . $windowId . ' #shop_id").val());

						if (shop_id)
						{
							var path = \'' . Core_Str::escapeJavascriptVariable($oAdmin_Form_Controller->getAdminActionModalLoad($oAdmin_Form_Controller->getPath(), 'edit', 'modal', 0, 0, 'shop_id=###&siteuser_id=' . $siteuser_id . '')) . '\';

							// Replace
							path = path.replace(/\###/g, shop_id)
							path = path.replace(/\; return false/g, ";");

							eval(path);

							$("#' . $windowId . ' .modal").modal("hide");
						}
					});
				});
			</script>
		')
	);

	$href = '#';
	$onclick = "$('#{$windowId} .modal').modal('show');";
}
else
{
	$href = $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0);
	$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0);
}

$btn = $siteuser_id && $windowId != 'id_content'
	? 'btn-gray'
	: 'btn-palegreen';

$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Order.shops_link_order_add'))
		->icon('fa fa-plus')
		->class('btn ' . $btn)
		->href($href)
		->onclick($onclick)
);

if (!$siteuser_id)
{
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Item.items_catalog_add_form_comment_link'))
			->icon('fa fa-comments')
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/comment/index.php', NULL, NULL, "shop_id={$oShop->id}")
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/comment/index.php', NULL, NULL, "shop_id={$oShop->id}")
			)
	)->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Order.property_menu'))
			->icon('fa fa-gears')
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/property/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/property/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$shop_group_id}"))
	);
}

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Глобальный поиск
if (!$siteuser_id)
{
	$additionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}";

	$sGlobalSearch = Core_Array::getGet('globalSearch', '', 'trim');

	$oAdmin_Form_Controller->addEntity(
		Admin_Form_Entity::factory('Code')
			->html('
				<div class="row search-field margin-bottom-20">
					<div class="col-xs-12">
						<form action="' . $oAdmin_Form_Controller->getPath() . '" method="GET">
							<input type="text" name="globalSearch" class="form-control" placeholder="' . Core::_('Admin.placeholderGlobalSearch') . '" value="' . htmlspecialchars($sGlobalSearch) . '" />
							<i class="fa fa-times-circle no-margin" onclick="' . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), '', '', $additionalParams) . '"></i>
							<button type="submit" class="btn btn-default global-search-button" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('', '', $additionalParams) . '"><i class="fa-solid fa-magnifying-glass fa-fw"></i></button>
						</form>
					</div>
				</div>
			')
	);

	$sGlobalSearch = str_replace(' ', '%', Core_DataBase::instance()->escapeLike($sGlobalSearch));
}

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
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, $additionalParams))
		;
	} while ($oShopBreadCrumbDir = $oShopBreadCrumbDir->getParent());

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
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0&shop_dir_id={$oShopDir->id}"))
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
		$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&shop_dir_id={$oShopDir->id}";

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroup->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, $additionalParams));
	} while ($oShopGroup = $oShopGroup->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Order.orders'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&shop_dir_id={$oShopDir->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
);

 // Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Order_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Order_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Order_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	$siteuser_id
		&& $Shop_Order_Controller_Edit
			->tabsClass('tabs-flat')
			->tabClass('tab-palegreen');

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Order_Controller_Edit);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form->Admin_Form_Actions->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Пересчет стоимости доставки"
$oAdminFormActionrecalcDelivery = $oAdmin_Form->Admin_Form_Actions->getByName('recalcDelivery');

if ($oAdminFormActionrecalcDelivery && $oAdmin_Form_Controller->getAction() == 'recalcDelivery')
{
	$oControllerrecalcDelivery = Admin_Form_Action_Controller::factory(
		'Shop_Order_Controller_Recalc', $oAdminFormActionrecalcDelivery
	);

	$oAdmin_Form_Controller->addAction($oControllerrecalcDelivery);
}

// Действие "Загрузка списка условий доставки"
$oAdminFormActionloadDeliveryConditionsList = $oAdmin_Form->Admin_Form_Actions->getByName('loadDeliveryConditionsList');

if ($oAdminFormActionloadDeliveryConditionsList && $oAdmin_Form_Controller->getAction() == 'loadDeliveryConditionsList')
{
	$oControllerloadDeliveryConditionsList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionloadDeliveryConditionsList
	);
	$oControllerloadDeliveryConditionsList
		->model(Core_Entity::factory('Shop_Delivery_Condition'))
		->defaultValue(' … ')
		->addCondition(
			array('join' => array('shop_deliveries', 'shop_delivery_conditions.shop_delivery_id', '=', 'shop_deliveries.id'))
		)
		->addCondition(
			array('where' => array('shop_delivery_conditions.shop_delivery_id', '=', Core_Array::getGet('delivery_id')))
		)
		->addCondition(
			array('where' => array('shop_deliveries.type', '=', 0))
		);

	$oAdmin_Form_Controller->addAction($oControllerloadDeliveryConditionsList);
}

// Действие "Загрузка списка условий доставки"
$oAdminFormActionloadCompanyAccountList = $oAdmin_Form->Admin_Form_Actions->getByName('loadCompanyAccountList');

if ($oAdminFormActionloadCompanyAccountList && $oAdmin_Form_Controller->getAction() == 'loadCompanyAccountList')
{
	$oControllerloadCompanyAccountList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionloadCompanyAccountList
	);

	$oControllerloadCompanyAccountList
		->model(Core_Entity::factory('Company_Account'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('company_accounts.company_id', '=', Core_Array::getGet('company_id')))
		);

	$oAdmin_Form_Controller->addAction($oControllerloadCompanyAccountList);
}

// Действие "Загрузка списка местоположений"
$oAdminFormActionLoadCountryLocationsList = $oAdmin_Form->Admin_Form_Actions->getByName('loadList2');

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
$oAdminFormActionLoadCountryLocationCitiesList = $oAdmin_Form->Admin_Form_Actions->getByName('loadList3');

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
$oAdminFormActionLoadCountryLocationCitiesList = $oAdmin_Form->Admin_Form_Actions->getByName('loadList4');

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

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Shop_Order_Controller_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form->Admin_Form_Actions->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Удаление значения свойства"
$oAction = $oAdmin_Form->Admin_Form_Actions->getByName('deletePropertyValue');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deletePropertyValue')
{
	$oDeletePropertyValueController = Admin_Form_Action_Controller::factory(
		'Property_Controller_Delete_Value', $oAction
	);

	$oDeletePropertyValueController
		->linkedObject(Core_Entity::factory('Shop_Order_Property_List', $oShop->id));

	$oAdmin_Form_Controller->addAction($oDeletePropertyValueController);
}

$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('print');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'print')
{
	$printlayout_id = Core_Array::getGet('printlayout_id', 0, 'int');

	$Shop_Order_Controller_Print = Admin_Form_Action_Controller::factory(
		'Shop_Order_Controller_Print', $oAdmin_Form_Action
	);

	$Shop_Order_Controller_Print
		->title(Core::_('Shop_Order.orders'))
		->printlayout($printlayout_id);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Order_Controller_Print);
}

$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('sendMail');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'sendMail')
{
	$printlayout_id = Core_Array::getGet('printlayout_id', 0, 'int');

	$Shop_Order_Controller_Print = Admin_Form_Action_Controller::factory(
		'Shop_Order_Controller_Print', $oAdmin_Form_Action
	);

	$Shop_Order_Controller_Print
		->printlayout($printlayout_id)
		->send(TRUE);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Order_Controller_Print);
}

$oAdminFormActionRollback = $oAdmin_Form->Admin_Form_Actions->getByName('rollback');

if ($oAdminFormActionRollback && $oAdmin_Form_Controller->getAction() == 'rollback')
{
	$oControllerRollback = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Rollback', $oAdminFormActionRollback
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerRollback);
}

// Действие "Объединить"
$oAdminFormActionMerge = $oAdmin_Form->Admin_Form_Actions->getByName('merge');

if ($oAdminFormActionMerge && $oAdmin_Form_Controller->getAction() == 'merge')
{
	$oAdmin_Form_Action_Controller_Type_Merge = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Merge', $oAdminFormActionMerge
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_Form_Action_Controller_Type_Merge);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Order')
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Dataset->addCondition(
	array('select' => array('shop_orders.*'))
);

// Доступ только к своим
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

if ($siteuser_id)
{
	$oAdmin_Form_Dataset->addCondition(
		array('where' => array('siteuser_id', '=', $siteuser_id))
	);

	$oAdmin_Form_Dataset->changeAction('edit', 'modal', 1);
}
else
{
	$oAdmin_Form_Dataset->addCondition(
		array('where' => array('shop_id', '=', $oShop->id))
	);

	if (strlen($sGlobalSearch))
	{
		$oAdmin_Form_Dataset
			->addCondition(
				array(
					'select' => array(
						'shop_orders.*'
					)
				)
			)
			->addCondition(
				array('leftJoin' => array('shop_order_items', 'shop_order_items.shop_order_id', '=', 'shop_orders.id'))
			)
			->addCondition(array('open' => array()))
				->addCondition(array('where' => array('shop_orders.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.invoice', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.coupon', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.postcode', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.address', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.surname', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.name', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.patronymic', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.company', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.phone', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_orders.email', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_order_items.name', 'LIKE', '%' . $sGlobalSearch . '%')))
				->addCondition(array('setOr' => array()))
				->addCondition(array('where' => array('shop_order_items.marking', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('close' => array()))
			->addCondition(
				array('groupBy' => array('shop_orders.id'))
			);
	}
}

if (isset($oAdmin_Form_Controller->request['topFilter_filter_tags'])
	&& is_array($oAdmin_Form_Controller->request['topFilter_filter_tags']))
{
	$aValues = $oAdmin_Form_Controller->request['topFilter_filter_tags'];
	$aValues = array_filter($aValues, 'strlen');

	if (count($aValues))
	{
		$oAdmin_Form_Dataset->addCondition(
			array('join' => array('tag_shop_orders', 'shop_orders.id', '=', 'tag_shop_orders.shop_order_id'))
		)->addCondition(
			array('join' => array('tags', 'tags.id', '=', 'tag_shop_orders.tag_id'))
		)->addCondition(
			array('where' => array('tags.name', 'IN', $aValues))
		);
	}
}

// Список значений для фильтра и поля
$aList = array('0' => '—');
if ($shop_id)
{
	$aShop_Order_Statuses = Core_Entity::factory('Shop_Order_Status')->getAllByShop_id($shop_id);
	foreach ($aShop_Order_Statuses as $oShop_Order_Status)
	{
		$aList[$oShop_Order_Status->id] = $oShop_Order_Status->name;
	}
}
elseif (!$shop_id && $siteuser_id)
{
	// Список заказов клиента
	$oSite = Core_Entity::factory('Site', CURRENT_SITE);

	$aShops = $oSite->Shops->findAll(FALSE);
	foreach ($aShops as $oShop)
	{
		$aShop_Order_Statuses = Core_Entity::factory('Shop_Order_Status')->getAllByShop_id($oShop->id);
		foreach ($aShop_Order_Statuses as $oShop_Order_Status)
		{
			$aList[$oShop_Order_Status->id] = $oShop_Order_Status->name;
		}
	}
}

$oAdmin_Form_Dataset
	->changeField('shop_order_status_id', 'type', 8)
	->changeField('shop_order_status_id', 'list', $aList)
	->changeField('paid', 'list', "1=" . Core::_('Admin_Form.yes') . "\n" . "0=" . Core::_('Admin_Form.no'))
	->changeField('posted', 'list', "1=" . Core::_('Admin_Form.yes') . "\n" . "0=" . Core::_('Admin_Form.no'))
	->changeField('canceled', 'list', "1=" . Core::_('Admin_Form.yes') . "\n" . "0=" . Core::_('Admin_Form.no'));

$oAdmin_Form_Controller->addExternalReplace('&{INTERNAL}', $siteuser_id ? "&siteuser_id={$siteuser_id}" : '');

$oAdmin_Form_Controller
	->addExternalReplace('{shop_group_id}', $shop_group_id)
	->addExternalReplace('{shop_dir_id}', $oShopDir->id);

$oAdmin_Form_Controller->addFilter('siteuser_id', array($oAdmin_Form_Controller, '_filterCallbackSiteuser'));
$oAdmin_Form_Controller->addFilter('user_id', array($oAdmin_Form_Controller, '_filterCallbackUser'));

// Список значений типов доставки для фильтра
$aShop_Deliveries = $oShop->Shop_Deliveries->findAll(FALSE);
$aList = array();
foreach ($aShop_Deliveries as $oShop_Delivery)
{
	$aList[$oShop_Delivery->id] = array('value' => $oShop_Delivery->name);
	!$oShop_Delivery->active && $aList[$oShop_Delivery->id]['attr'] = array(
		'class' => 'darkgray line-through'
	);
}

$oAdmin_Form_Dataset
	->changeField('shop_delivery_id', 'type', 8)
	->changeField('shop_delivery_id', 'list', $aList);

// Список значений платежных систем для фильтра
$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->findAll(FALSE);
$aList = array();
foreach ($aShop_Payment_Systems as $oShop_Payment_System)
{
	$aList[$oShop_Payment_System->id] = array('value' => $oShop_Payment_System->name);
	!$oShop_Payment_System->active && $aList[$oShop_Payment_System->id]['attr'] = array(
		'class' => 'darkgray line-through'
	);
}

$oAdmin_Form_Dataset
	->changeField('shop_payment_system_id', 'type', 8)
	->changeField('shop_payment_system_id', 'list', $aList);

Core_Event::attach('Admin_Form_Controller.onAfterShowContent', function($oAdmin_Form_Controller) {
	$windowId = $oAdmin_Form_Controller->getWindowId();
	?>
	<script>
		$('[data-popover="hover"]').on('mouseenter', function(event) {
			var $this = $(this);

			if (!$this.data("bs.popover"))
			{
				$this.popover({
					placement:'left',
					trigger:'manual',
					html:true,
					content: function() {
						var content = '';

						$.ajax({
							url: '/admin/shop/order/index.php',
							data: { showPopover: 1, shop_order_id: $(this).data('id') },
							dataType: 'json',
							type: 'POST',
							async: false,
							success: function(response) {
								content = response.html;
							}
						});

						return content;
					},
					container: "#<?php echo $windowId?>"
				});

				$this.attr('data-popoverAttached', true);

				$this.on('hide.bs.popover', function(e) {
					$this.attr('data-popoverAttached')
						? $this.removeAttr('data-popoverAttached')
						: e.preventDefault();
				})
				.on('show.bs.popover', function(e) {
					!$this.attr('data-popoverAttached') && e.preventDefault();
				})
				.on('shown.bs.popover', function(e) {
					$('#' + $this.attr('aria-describedby')).on('mouseleave', function(e) {
						!$this.parent().find(e.relatedTarget).length && $this.popover('destroy');
					});
				})
				.on('mouseleave', function(e) {
					!$(e.relatedTarget).parent('#' + $this.attr('aria-describedby')).length
					&& $this.attr('data-popoverAttached')
					&& $this.popover('destroy');
				});

				$this.popover('show');
			}
		});
	</script>
	<?php
});

// Показ формы
$oAdmin_Form_Controller->execute();