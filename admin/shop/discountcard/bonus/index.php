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
$iAdmin_Form_Id = 275;
$sFormAction = '/{admin}/shop/discountcard/bonus/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0, 'int'));
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0, 'int'));
$oShop_Discountcard = Core_Entity::factory('Shop_Discountcard', intval(Core_Array::getGet('shop_discountcard_id', 0)));
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = Core::_('Shop_Discountcard_Bonus.title', $oShop_Discountcard->number);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

// Списание бонусов
if ($oAdmin_Form_Controller->getAction() == 'writeoff_bonuses')
{
	$writeoffAmount = Core_Array::getPost('writeoff', 0);
	$bonusesAmount = $oShop_Discountcard->getBonusesAmount();

	if ($writeoffAmount > $bonusesAmount)
	{
		$oAdmin_Form_Controller->addMessage(Core_Message::get(Core::_('Shop_Discountcard.backendWrongWriteoffWarning'), 'error'));
	}
	else
	{
		// Списание бонусов
		$writtenOff = 0;

		$aShop_Discountcard_Bonuses = $oShop_Discountcard->getBonuses(FALSE);
		foreach ($aShop_Discountcard_Bonuses as $oShop_Discountcard_Bonus)
		{
			$delta = $oShop_Discountcard_Bonus->amount - $oShop_Discountcard_Bonus->written_off;

			// На текущем этапе будут списаны все необходимые бонусы
			if ($writeoffAmount - $writtenOff <= $delta)
			{
				$oShop_Discountcard_Bonus->written_off += ($writeoffAmount - $writtenOff);
				$oShop_Discountcard_Bonus->save();
				break;
			}
			else
			{
				$oShop_Discountcard_Bonus->written_off += $delta;
				$oShop_Discountcard_Bonus->save();
			}

			$writtenOff += $delta;
		}

		$oAdmin_Form_Controller->addMessage(Core_Message::get(Core::_('Shop_Discountcard.backendWriteoffSuccess'), 'success'));
	}
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}&shop_discountcard_id={$oShop_Discountcard->id}";

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

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$bonusesAmount = $oShop_Discountcard->getBonusesAmount(FALSE);

$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html('
			<div class="widget flat margin-bottom-20">
				<div class="widget-body bordered-left bordered-warning">
					<form class="form-inline" role="form" action="' . $oAdmin_Form_Controller->getPath() . '" method="POST">
						<div class="form-group">
							<input name="writeoff" type="text" class="form-control" placeholder="' . Core::_('Shop_Discountcard_Bonus.available', $bonusesAmount, $oShop->Shop_Currency->sign) . '">
						</div>

						<button type="submit" class="btn btn-warning" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('writeoff_bonuses') . '">' . Core::_('Shop_Discountcard_Bonus.writeoff') . '</button>
					</form>
				</div>
			</div>
		')
);

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
		->name(Core::_('Shop_Discountcard.title'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/discountcard/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/discountcard/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
)->add(
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
	$Shop_Discountcard_Bonus_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Discountcard_Bonus_Controller_Edit', $oAdmin_Form_Action_Edit
	);
	$Shop_Discountcard_Bonus_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	$oAdmin_Form_Controller->addAction($Shop_Discountcard_Bonus_Controller_Edit);
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
	Core_Entity::factory('Shop_Discountcard_Bonus')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$oAdmin_Form_Dataset
	->addCondition(array('where' => array('shop_discountcard_id', '=', $oShop_Discountcard->id)))
;

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();