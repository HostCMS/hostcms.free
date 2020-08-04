<?php
/**
* Online shop.
*
* @package HostCMS
* @version 6.x
* @author Hostmake LLC
* @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
*/
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path('/admin/shop/item/card/index.php');

// Получаем параметры
$oShop = Core_Entity::factory('Shop', Core_Array::getRequest('shop_id', 0));
$oShopDir = $oShop->Shop_Dir;
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getRequest('shop_group_id', 0));

$aOptions = array();

$oModule = Core_Entity::factory('Module')->getByPath($sModule);
if (!is_null($oModule) && Core::moduleIsActive('printlayout'))
{
	$aPrintlayouts = Core_Entity::factory('Printlayout')->getAvailable($oModule->id, 15);

	foreach($aPrintlayouts as $oPrintlayout)
	{
		$aOptions[$oPrintlayout->id] = $oPrintlayout->name . ' [' . $oPrintlayout->id . ']';
	}
}

if (!is_null(Core_Array::getPost('start')) && Core::moduleIsActive('printlayout'))
{
	$oPrintlayout = Core_Entity::factory('Printlayout', intval(Core_Array::getPost('printlayout_id', 0)));

	$fullname = Core_Array::getPost('fullname', '');
	$date = Core_Array::getPost('date', '');

	$horizontal = intval(Core_Array::getPost('horizontal', 3));
	$vertical = intval(Core_Array::getPost('vertical', 5));

	$iParentGroupId = intval(Core_Array::getPost('parent_group', 0));

	if ($iParentGroupId == 0)
	{
		$oShop_Groups = $oShop->Shop_Groups;
		$oShop_Groups->queryBuilder()
			->where('shop_groups.parent_id', '=', 0);
	}
	else
	{
		$oShop_Groups = Core_Entity::factory('Shop_Group', $iParentGroupId)->Shop_Groups;
	}

	$oShop_Groups->queryBuilder()
		->where('shop_groups.shortcut_id', '=', 0);

	$aShopGroupsId = array_merge(array($iParentGroupId), $oShop_Groups->getGroupChildrenId());

	$aAllShopItems = array();

	foreach ($aShopGroupsId as $iShopGroupId)
	{
		$oShop_Items = $oShop->Shop_Items;
		$oShop_Items->queryBuilder()
			->where('shop_items.shop_group_id', '=', $iShopGroupId)
			->where('shop_items.shortcut_id', '=', 0)
			->clearOrderBy()
			->orderBy('shop_items.sorting');

		$aShop_Items = $oShop_Items->findAll(FALSE);

		foreach ($aShop_Items as $oShop_Item)
		{
			$aAllShopItems[] = $oShop_Item;
		}
	}

	$Shop_Item_Controller_Pricetag = new Shop_Item_Controller_Pricetag($oPrintlayout);
	$Shop_Item_Controller_Pricetag
		->fullname($fullname)
		->date($date)
		->horizontal($horizontal)
		->vertical($vertical)
		->execute($aAllShopItems);
}
else
{
	// Первая крошка на список магазинов
	$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php'))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php'))
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
				->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"))
				->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"));
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
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
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
				->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"))
				->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"));
		} while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oBreadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
		}
	}

	// Крошка на текущую форму
	$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Item.item_cards_print'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
	);

	$oAdmin_View = Admin_View::create()
		->module(Core_Module::factory($sModule))
		->pageTitle(Core::_('Shop_Item.item_cards_print'))
		->addChild($oAdmin_Form_Entity_Breadcrumbs);

	$windowId = $oAdmin_Form_Controller->getWindowId();

	// Создаем экземпляры классов
	$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->target('_blank')
		->add(
			Admin_Form_Entity::factory('Tab')->name('main')
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Admin_Form_Entity::factory('Select')
						->name("parent_group")
						->options(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
						->caption(Core::_('Shop_Item.item_cards_print_parent_group'))
						->value($oShopGroup->id)
					)
					->add(Admin_Form_Entity::factory('Select')
						->name("printlayout_id")
						->options($aOptions)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
						->caption(Core::_('Shop_Item.item_cards_printlayout'))
					)
				)
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Admin_Form_Entity::factory('Input')
						->name('fullname')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.item_cards_print_fio'))
						->value($oShop->Shop_Company->accountant_legal_name)
					)
					->add(Admin_Form_Entity::factory('Date')
						->name('date')
						->caption(Core::_('Shop_Item.item_cards_print_date'))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->value(date('d.m.Y'))
					)
					->add(Admin_Form_Entity::factory('Input')
						->name('horizontal')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.item_cards_print_horizontal'))
						->value(3)
					)
					->add(Admin_Form_Entity::factory('Input')
						->name('vertical')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.item_cards_print_vertical'))
						->value(5)
					)
				)
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Core::factory('Core_Html_Entity_Input')
						->type('hidden')
						->name('shop_id')
						->value($oShop->id)
					)
				)
	)->add(
		Core::factory('Admin_Form_Entity_Button')
			->name('start')
			->type('submit')
			->class('applyButton btn btn-blue')
	);

	ob_start();
	$oAdmin_Form_Entity_Form->execute();
	$content = ob_get_clean();

	ob_start();
	$oAdmin_View
		->content($content)
		->show();

	Core_Skin::instance()
		->answer()
		->ajax(Core_Array::getRequest('_', FALSE))
		->content(ob_get_clean())
		->title(Core::_('Shop_Item.item_cards_print'))
		->execute();
}