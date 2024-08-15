<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 52;
$sAdminFormAction = '/admin/shop/order/comment/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iShopOrderId = Core_Array::getGet('shop_order_id', 0, 'int');
$oShop_Order = Core_Entity::factory('Shop_Order')->find($iShopOrderId);

$shop_id = Core_Array::getGet('shop_id', 0, 'int');
$comment_parent_id = Core_Array::getGet('parent_id', 0, 'int');

$oShop = Core_Entity::factory('Shop')->find($shop_id);
$iShopGroupId = intval(Core_Array::getGet('shop_group_id', 0));

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction);

if (!is_null($oShop_Order->id))
{
	$oAdmin_Form_Controller
		->title(Core::_('Shop_Order.show_item_comment_title', $oShop_Order->invoice))
		->pageTitle(Core::_('Shop_Order.show_item_comment_title', $oShop_Order->invoice));
}
elseif ($oShop->id) // Комментарии магазина
{
	$oAdmin_Form_Controller
		->title(Core::_('Shop_Order.show_orders_comment'))
		->pageTitle(Core::_('Shop_Order.show_orders_comment'));
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

if (!is_null($oShop_Order->id) || $comment_parent_id)
{
	// Элементы меню
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop.items_catalog_add_form_comment_link'))
			->icon('fa fa-plus')
			->href(
				$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
			)
	);
}

if ($oShop->id)
{
	$additionalParamsProperties = 'shop_id=' . $oShop->id . '&shop_group_id=' . $iShopGroupId;

	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Item.property_header'))
			->icon('fa fa-gears')
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/comment/property/index.php', NULL, NULL, $additionalParamsProperties)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/comment/property/index.php', NULL, NULL, $additionalParamsProperties)
			)
	);
}

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Строка навигации
$sShopDirPath = '/admin/shop/index.php';

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($sShopDirPath, NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopDirPath, NULL, NULL, ''))
);

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
				->href($oAdmin_Form_Controller->getAdminLoadHref($sShopDirPath, NULL, NULL, $additionalParams))
				->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopDirPath, NULL, NULL, $additionalParams));
		} while ($oShopDir = $oShopDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add($oAdmin_Form_Entity_Breadcrumb);
		}
	}
}

$additionalParams = 'shop_id=' . $oShop->id;
$sShopPath = '/admin/shop/item/index.php';

if ($oShop->id)
{
	// Ссылка на название магазина
	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name($oShop->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref($sShopPath, NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopPath, NULL, NULL, $additionalParams))
	);

	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Shop_Order.show_order_title', $oShop->name))
			->href($oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/index.php', NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/index.php', NULL, NULL, $additionalParams))
	);
}

if ($iShopGroupId)
{
	$oShopGroup = Core_Entity::factory('Shop_Group')->find($iShopGroupId);

	if (!is_null($oShopGroup->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'shop_id=' . intval($oShopGroup->shop_id) . '&shop_group_id=' . intval($oShopGroup->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oShopGroup->name)
				->href($oAdmin_Form_Controller->getAdminLoadHref($sShopPath, NULL, NULL, $additionalParams))
				->onclick($oAdmin_Form_Controller->getAdminLoadAjax($sShopPath, NULL, NULL, $additionalParams));
		} while ($oShopGroup = $oShopGroup->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$additionalParams = 'shop_id=' . $oShop->id . '&shop_group_id=' . $iShopGroupId;

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->id
			? Core::_('Shop_Order.show_comments_title', $oShop->name, FALSE)
			: Core::_('Shop.comments_title'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
);

if (!is_null($oShop_Order->id))
{
	$additionalParams = 'shop_order_id=' . $oShop_Order->id . '&shop_id=' . $oShop->id . '&shop_group_id=' . $iShopGroupId;

	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Shop_Order.show_item_comment_title', $oShop_Order->invoice))
			->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
	);
}

if ($comment_parent_id)
{
	// Если передан родительский комментарий - строим хлебные крошки
	$oComment = Core_Entity::factory('Comment')->find($comment_parent_id);

	if (!is_null($oComment->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = (!is_null($oShop_Order->id)
				? 'shop_order_id=' . $oShop_Order->id
				: 'shop_id=' . $oShop->id . '&parent_id=' . intval($oComment->id)
			);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oComment->getShortText())
				->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams))
				->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams));
		} while ($oComment = $oComment->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oShop_Order_Comment_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Order_Comment_Controller_Edit', $oAdmin_Form_Action
	);

	$oShop_Order_Comment_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShop_Order_Comment_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oCommentControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oCommentControllerApply);
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

// Действие "Удаление значения свойства"
$oAdminFormActiondeletePropertyValue = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deletePropertyValue');

if ($oAdminFormActiondeletePropertyValue && $oAdmin_Form_Controller->getAction() == 'deletePropertyValue')
{
	$oCommentControllerdeletePropertyValue = Admin_Form_Action_Controller::factory(
		'Property_Controller_Delete_Value', $oAdminFormActiondeletePropertyValue
	);

	$oCommentControllerdeletePropertyValue
		->linkedObject(Core_Entity::factory('Shop_Order_Comment_Property_List', $shop_id));

	$oAdmin_Form_Controller->addAction($oCommentControllerdeletePropertyValue);
}

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Order_Comment')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$bItem = !is_null($oShop_Order->id);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('comments.*', array(Core_QueryBuilder::expression('CONCAT_WS(\' \', `comments`.`subject`, `comments`.`text`)'), 'fulltext')))
)->addCondition(
	array('join' => array('comment_shop_orders', 'comments.id', '=', 'comment_shop_orders.comment_id'))
);

if (!$bItem)
{
	$oAdmin_Form_Dataset->addCondition(
		array('straightJoin' => array())
	)->addCondition(
		array('join' => array('shop_orders', 'comment_shop_orders.shop_order_id', '=', 'shop_orders.id'))
	)->addCondition(
		array('where' => array('shop_orders.deleted', '=', 0))
	);
}

// Ограничения по parent_id делаем только при выводе комментариев конкретного товара
if ($iShopOrderId || $comment_parent_id)
{
	$oAdmin_Form_Dataset->addCondition(array('where' =>
		array('parent_id', '=', $comment_parent_id)
	));
}

$additionalParams = !is_null($oShop_Order->id)
	? '&shop_order_id=' . $oShop_Order->id
	: '&shop_id=' . $oShop->id;

$commentLink = $comment_parent_id ? '&parent_id=' . $comment_parent_id : '';

$oAdmin_Form_Dataset
	->changeField('active', 'link', '{path}?hostcms[action]=changeActive&hostcms[checked][0][{id}]=1' . $additionalParams . $commentLink)
	->changeField('active', 'onclick', "$.adminLoad({path: '{path}', additionalParams: 'hostcms[checked][0][{id}]=1" . $additionalParams . $commentLink ."', action: 'changeActive', windowId: '{windowId}'}); return false")
	->changeField('short_text', 'link', '{path}?parent_id={id}' . $additionalParams)
	->changeField('short_text', 'onclick', "$.adminLoad({path: '{path}',additionalParams: 'parent_id={id}" . $additionalParams ."', windowId: '{windowId}'}); return false");

if ($bItem)
{
	$oAdmin_Form_Dataset->addCondition(
		array('where' => array('comment_shop_orders.shop_order_id', '=', $oShop_Order->id))
	);
}
elseif ($oShop->id) // Комментарии магазина
{
	$oAdmin_Form_Dataset->addCondition(
		array('where' => array('shop_orders.shop_id', '=', $oShop->id))
	);
}
else
{
	$oAdmin_Form_Dataset->addCondition(
		array('join' => array('shops', 'shops.id', '=', 'shop_orders.shop_id')
		)
	)->addCondition(
		array('where' => array('shops.site_id', '=', CURRENT_SITE))
	);
}

$oAdmin_Form_Controller->addExternalReplace('{shop_order_id}', intval($oShop_Order->id));
$oAdmin_Form_Controller->addExternalReplace('{shop_item_id}', 0);
$oAdmin_Form_Controller->addExternalReplace('{informationsystem_item_id}', 0);

// У заказов нет своей страницы, переход не нужен
$oAdmin_Form_Controller->deleteAdminFormFieldById(884);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();