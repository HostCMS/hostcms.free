<?php
/**
* Online shop.
*
* @package HostCMS
* @version 7.x
* @author Hostmake LLC
* @copyright © 2005-2024, https://www.hostcms.ru
*/
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$path = '/admin/shop/item/barcode/index.php';

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($path);

$oAdmin_View = Admin_View::create()
	->module(Core_Module_Abstract::factory($sModule))
	->pageTitle(Core::_('Shop_Item.item_barcodes'));

// Получаем параметры
$oShop = Core_Entity::factory('Shop', Core_Array::getRequest('shop_id', 0));
$oShopDir = $oShop->Shop_Dir;
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getRequest('shop_group_id', 0));

$formSettings = Core_Array::getPost('hostcms', array())
	+ array(
		'action' => NULL,
		'window' => 'id_content',
	);

$windowId = Core_Str::escapeJavascriptVariable($formSettings['window']);

if ($formSettings['action'] == 'generate')
{
	$oShop_Item_Controller_Barcode = new Shop_Item_Controller_Barcode($oShop);
	$oShop_Item_Controller_Barcode
		->type(Core_Array::getPost('type', 2, 'int'))
		->prefix(Core_Array::getPost('prefix', '200', 'trim'))
		->execute();

	ob_start();

	$oAdmin_View->addMessage(
		Core_Message::show(Core::_('Shop_Item.shop_item_barcode_set'))
	);

	Core::showJson(
		array('error' => ob_get_clean(), 'form_html' => '')
	);
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
		->name(Core::_('Shop_Item.item_barcodes'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
	);

	$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

	$windowId = $oAdmin_Form_Controller->getWindowId();

	// Создаем экземпляры классов
	$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->action($path)
		->target('_blank')
		->add(
			Admin_Form_Entity::factory('Tab')->name('main')
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Admin_Form_Entity::factory('Select')
						->name('type')
						->options(array(
							1 => 'EAN-8',
							2 => 'EAN-13'
						))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.barcode_type'))
						->value(2)
					)
					->add(Admin_Form_Entity::factory('Input')
						->name('prefix')
						->value(200)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
						->caption(Core::_('Shop_Item.barcode_prefix'))
					)
				)
				->add(Admin_Form_Entity::factory('Div')->class('row')
					->add(Core_Html_Entity::factory('Input')
						->type('hidden')
						->name('shop_id')
						->value($oShop->id)
					)
				)
	)->add(
		Admin_Form_Entity::factory('Button')
			->name('generate')
			->type('submit')
			->value(Core::_('Shop_Item.barcode_generate'))
			->class('applyButton btn btn-blue')
			->onclick(
				"$.adminSendForm({buttonObject: this,action: 'generate',operation: '',additionalParams: '',limit: '10',current: '1',sortingFieldId: '',sortingDirection: '',windowId: '{$windowId}'}); return false"
			)
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
		->title(Core::_('Shop_Item.item_barcodes'))
		->execute();
}