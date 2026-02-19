<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 76;

$sAdminFormAction = '/{admin}/shop/order/item/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$shop_id = Core_Array::getGet('shop_id', 0, 'int');
$shop_group_id = Core_Array::getGet('shop_group_id', 0, 'int');
$shop_dir_id = Core_Array::getGet('shop_dir_id', 0, 'int');
$shop_order_id = Core_Array::getRequest('shop_order_id', 0, 'int');

$oShop = Core_Entity::factory('Shop')->find($shop_id);
$oShop_Order = Core_Entity::factory('Shop_Order')->getById($shop_order_id);

if (!$oShop_Order || $oShop_Order->Shop->site_id != CURRENT_SITE)
{
	throw new Core_Exception("Order does not exist or access forbidden!");
}

$oShopDir = Core_Entity::factory('Shop_Dir', $shop_dir_id);

$sFormTitle = Core::_('Shop_Order_Item.show_order_items_title', $oShop_Order->invoice, FALSE);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

$siteuser_id = intval(Core_Array::getGet('siteuser_id'));
$siteuser_id && $oAdmin_Form_Controller->Admin_View(
	Admin_View::getClassName('Admin_Internal_View')
);

if (Core_Array::getPost('load_modal') && Core_Array::getPost('shop_order_item_id'))
{
	$aJSON = array();

	$shop_order_item_id = intval(Core_Array::getPost('shop_order_item_id'));

	$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item')->getById($shop_order_item_id);

	if (!is_null($oShop_Order_Item))
	{
		$oShop = $oShop_Order_Item->Shop_Order->Shop;

		$aTypes = array();

		$aShop_Codetypes = Core_Entity::factory('Shop_Codetype')->findAll(FALSE);
		foreach ($aShop_Codetypes as $oShop_Codetype)
		{
			$aTypes[$oShop_Codetype->id] = $oShop_Codetype->name;
		}

		$aShop_Order_Item_Codes = $oShop_Order_Item->Shop_Order_Item_Codes->findAll(FALSE);

		ob_start();
		?>
		<div class="modal fade" id="codes-<?php echo $oShop_Order_Item->id?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<form action="<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/shop/order/item/index.php')?>" method="POST">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
							<h4 class="modal-title" id="myModalLabel"><?php echo Core::_('Shop_Order_Item.item_codes', $oShop_Order_Item->name)?></h4>
						</div>
						<div class="modal-body">
							<?php
							for ($i = 1; $i <= $oShop_Order_Item->quantity; $i++)
							{
								$oShop_Order_Item_Code = count($aShop_Order_Item_Codes)
									? array_shift($aShop_Order_Item_Codes)
									: NULL;

								$value = $oShop_Order_Item_Code
									? $oShop_Order_Item_Code->code
									: '';
								?>
								<div class="row">
									<div class="col-xs-12 col-sm-6 margin-bottom-5">
										<input class="form-control" type="text" name="shop_order_item_code<?php echo $oShop_Order_Item_Code ? $oShop_Order_Item_Code->id : '[]'?>" value="<?php echo htmlspecialchars($value)?>"/>
									</div>
									<div class="col-xs-12 col-sm-6 margin-bottom-5">
										<select name="shop_codetype<?php echo $oShop_Order_Item_Code ? $oShop_Order_Item_Code->id : '[]'?>" class="form-control">
											<option value="0">...</option>
											<?php
											foreach ($aTypes as $value => $name)
											{
												$selected = $oShop_Order_Item_Code && $oShop_Order_Item_Code->shop_codetype_id == $value || is_null($oShop_Order_Item_Code) && $oShop->shop_codetype_id == $value
													? 'selected="selected"'
													: '';

												?><option <?php echo $selected?> value="<?php echo $value?>"><?php echo htmlspecialchars($name)?></option><?php
											}
											?>
										</select>
									</div>
								</div>
								<?php
							}
							?>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary" onclick="mainFormLocker.unlock(); <?php echo $oAdmin_Form_Controller
								->checked(array(0 => array($oShop_Order_Item->id => 1)))
								->getAdminSendForm('setCodes', NULL, 'shop_order_id=' . Core_Array::getRequest('shop_order_id', 0, 'int'))?>"><?php echo Core::_('Admin_Form.apply')?></button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<?php
		$aJSON['html'] = ob_get_clean();
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_warehouse'))
	&& !is_null(Core_Array::getGet('queryString'))
)
{
	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));
	$iShopId = Core_Array::getGet('shop_id', 0, 'int');
	$oShop = Core_Entity::factory('Shop', $iShopId);

	$aJSON[0] = array(
		'id' => 0,
		'label' => '[0] ...'
	);

	if (strlen($sQuery))
	{
		$aTmp = Shop_Order_Item_Controller_Edit::fillWarehousesList($oShop, $sQuery);

		foreach ($aTmp as $key => $value)
		{
			$key && $aJSON[$key] = array(
				'id' => $key,
				'label' => $value
			);
		}
	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Order_Item.links_items_add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Order_Item.recount_discounts'))
		->icon('fa-solid fa-rotate')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'recountDiscount', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'recountDiscount', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopItemFormPath = '/{admin}/shop/index.php', NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopItemFormPath, NULL, NULL, ''))
);

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
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}
}

// Добавляем крошку на форму списка групп товаров и товаров
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/item/index.php', NULL, NULL,$sAdditionalParams = "shop_id={$oShop->id}&shop_group_id=0&shop_dir_id={$oShopDir->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/index.php', NULL, NULL, $sAdditionalParams))
);

// Крошки строим только если: мы не в корне или идет редактирование
if ($shop_group_id)
{
	$oShopGroup = Core_Entity::factory('Shop_Group', $shop_group_id);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	$sShopItemFormPath = '/{admin}/shop/item/index.php';

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

// Добавляем крошку на форму списка заказов
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Order.orders'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopOrderFormPath = '/{admin}/shop/order/index.php', NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&shop_dir_id={$oShopDir->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopOrderFormPath, NULL, NULL, $sAdditionalParams))
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Order.order_edit', $oShop_Order->invoice, FALSE))
		->href($oAdmin_Form_Controller->getAdminActionLoadHref('/{admin}/shop/order/index.php', 'edit', NULL, 0, $oShop_Order->id))
		->onclick($oAdmin_Form_Controller->getAdminActionLoadAjax('/{admin}/shop/order/index.php', 'edit', NULL, 0, $oShop_Order->id))
);

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($sFormTitle)
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams = "shop_id={$oShop->id}&shop_group_id={$shop_group_id}&shop_dir_id={$oShopDir->id}&shop_order_id={$shop_order_id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $sAdditionalParams))
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Order_Item_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Order_Item_Controller_Edit', $oAdmin_Form_Action
	);

	$Shop_Order_Item_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Order_Item_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Shop_Order_Item_Controller_Apply', $oAdminFormActionApply
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

$oAdminFormActionRecount = $oAdmin_Form->Admin_Form_Actions->getByName('recountDiscount');

if ($oAdminFormActionRecount && $oAdmin_Form_Controller->getAction() == 'recountDiscount')
{
	$Shop_Order_Item_Controller_Recount = Admin_Form_Action_Controller::factory(
		'Shop_Order_Item_Controller_Recount', $oAdminFormActionRecount
	);

	$Shop_Order_Item_Controller_Recount->shopOrder($oShop_Order);

	$oAdmin_Form_Controller->addAction($Shop_Order_Item_Controller_Recount);
}

$oAdminFormActionSplit = $oAdmin_Form->Admin_Form_Actions->getByName('splitOrder');

if ($oAdminFormActionSplit && $oAdmin_Form_Controller->getAction() == 'splitOrder')
{
	$oShop_Order_Item_Controller_Split = Admin_Form_Action_Controller::factory(
		'Shop_Order_Item_Controller_Split', $oAdminFormActionSplit
	);

	$oNew_Shop_Order = clone $oShop_Order;
	$oNew_Shop_Order->guid = Core_Guid::get();
	$oNew_Shop_Order->datetime = Core_Date::timestamp2sql(time());
	$oNew_Shop_Order->payment_datetime = '0000-00-00 00:00:00';
	$oNew_Shop_Order->status_datetime = '0000-00-00 00:00:00';
	$oNew_Shop_Order->canceled = 0;
	$oNew_Shop_Order->paid = 0;
	$oNew_Shop_Order->save();

	$oNew_Shop_Order->createInvoice();
	$oNew_Shop_Order->save();

	$oShop_Order_Item_Controller_Split->shopOrder($oNew_Shop_Order);

	$oAdmin_Form_Controller->addAction($oShop_Order_Item_Controller_Split);
}

// Действие "Установить маркировки"
$oAdminFormActionSetCode = $oAdmin_Form->Admin_Form_Actions->getByName('setCodes');

if ($oAdminFormActionSetCode && $oAdmin_Form_Controller->getAction() == 'setCodes')
{
	$oShopOrderItemControllerCodes = Admin_Form_Action_Controller::factory(
		'Shop_Order_Item_Controller_Code', $oAdminFormActionSetCode
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShopOrderItemControllerCodes);
}

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Order_Item')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset
	->addCondition
	(
		array('select' => array('shop_order_items.*', array(Core_QueryBuilder::expression('ROUND((`price` +  ROUND(`price` * `rate` / 100, 2)) * `quantity`, 2)'), 'sum')))
	)
	->addCondition(
		array('where' =>
			array('shop_order_id', '=', $shop_order_id)
		)
	)
;

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();