<?php
/**
 * Information systems.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'informationsystem');

// Код формы
$iAdmin_Form_Id = 52;
$sAdminFormAction = '/admin/informationsystem/item/comment/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iInformationsystemItemId = intval(Core_Array::getGet('informationsystem_item_id', 0));
$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item')->find($iInformationsystemItemId);

$informationsystem_id = intval(Core_Array::getGet('informationsystem_id', 0));

$comment_parent_id = intval(Core_Array::getGet('parent_id', 0));

if (!is_null($oInformationsystem_Item->id))
{
	$oInformationsystem = $oInformationsystem_Item->Informationsystem;
	$iInformationsystemGroupId = $oInformationsystem_Item->informationsystem_group_id;
}
else
{
	$oInformationsystem = Core_Entity::factory('Informationsystem')->find($informationsystem_id);
	$iInformationsystemGroupId = intval(Core_Array::getGet('informationsystem_group_id', 0));
}

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction);

// Комментарии информационного элемента
if (!is_null($oInformationsystem_Item->id))
{
	$oAdmin_Form_Controller
		->title(Core::_('Informationsystem_Item.show_comments_title', $oInformationsystem_Item->name))
		->pageTitle(Core::_('Informationsystem_Item.show_comments_title', $oInformationsystem_Item->name));
}
elseif ($oInformationsystem->id) // Комментарии информационной системы
{
	$oAdmin_Form_Controller
		->title(Core::_('Informationsystem.show_comments_system_title', $oInformationsystem->name))
		->pageTitle(Core::_('Informationsystem.show_comments_system_title', $oInformationsystem->name));
}
else
{
	$oAdmin_Form_Controller
		->title(Core::_('Informationsystem.comments_title'))
		->pageTitle(Core::_('Informationsystem.comments_title'));
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

if (!is_null($oInformationsystem_Item->id) || $comment_parent_id)
{
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
}

if ($oInformationsystem->id)
{
	$additionalParamsProperties = 'informationsystem_id=' . $oInformationsystem->id . '&informationsystem_group_id=' . $iInformationsystemGroupId;

	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Informationsystem_Item.show_information_groups_link3'))
			->icon('fa fa-gears')
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref('/admin/informationsystem/comment/property/index.php', NULL, NULL, $additionalParamsProperties)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax('/admin/informationsystem/comment/property/index.php', NULL, NULL, $additionalParamsProperties)
			)
	);
}

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Строка навигации
$sInformationsystemDirPath = '/admin/informationsystem/index.php';

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Informationsystem.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sInformationsystemDirPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sInformationsystemDirPath, NULL, NULL, '')
	)
);

// Путь по разделам информационных систем
if ($oInformationsystem->informationsystem_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oInformationsystemDir = Core_Entity::factory('Informationsystem_Dir')->find($oInformationsystem->informationsystem_dir_id);

	if (!is_null($oInformationsystemDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'informationsystem_dir_id=' . intval($oInformationsystemDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oInformationsystemDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sInformationsystemDirPath, NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sInformationsystemDirPath, NULL, NULL, $additionalParams)
				);
		} while ($oInformationsystemDir = $oInformationsystemDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$additionalParams = 'informationsystem_id=' . $oInformationsystem->id;
$sInformationsystemPath = '/admin/informationsystem/item/index.php';

if ($oInformationsystem->id)
{
	// Ссылка на название ИС
	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name($oInformationsystem->name)
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($sInformationsystemPath, NULL, NULL, $additionalParams)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($sInformationsystemPath, NULL, NULL, $additionalParams)
		)
	);
}

// Путь по группам информационных элементов
if ($iInformationsystemGroupId)
{
	$oInformationsystemGroup = Core_Entity::factory('Informationsystem_Group')->find($iInformationsystemGroupId);

	if (!is_null($oInformationsystemGroup->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'informationsystem_id=' . intval($oInformationsystemGroup->informationsystem_id) . '&informationsystem_group_id=' . intval($oInformationsystemGroup->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oInformationsystemGroup->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sInformationsystemPath, NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sInformationsystemPath, NULL, NULL, $additionalParams)
				);
		} while ($oInformationsystemGroup = $oInformationsystemGroup->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

$additionalParams = 'informationsystem_id=' . $oInformationsystem->id . '&informationsystem_group_id=' . $iInformationsystemGroupId;

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oInformationsystem->id
			? Core::_('Informationsystem.show_comments_system_title', $oInformationsystem->name, FALSE)
			: Core::_('Informationsystem.comments_title')
		)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
	)
);

if (!is_null($oInformationsystem_Item->id))
{
	$additionalParams = 'informationsystem_item_id=' . $oInformationsystem_Item->id;

	$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Informationsystem_Item.show_comments_title', $oInformationsystem_Item->name, FALSE))
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
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
			$additionalParams = (!is_null($oInformationsystem_Item->id)
				? 'informationsystem_item_id=' . $oInformationsystem_Item->id
				: 'informationsystem_id=' . $oInformationsystem->id .
				($iInformationsystemGroupId
				? '&informationsystem_group_id=' . $iInformationsystemGroupId
				: ''))
				. ('&parent_id=' . intval($oComment->id));

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oComment->getShortText())
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
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

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oInformationsystem_Item_Comment_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Informationsystem_Item_Comment_Controller_Edit', $oAdmin_Form_Action
	);

	$oInformationsystem_Item_Comment_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystem_Item_Comment_Controller_Edit);
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
		->linkedObject(Core_Entity::factory('Informationsystem_Comment_Property_List', $informationsystem_id));

	$oAdmin_Form_Controller->addAction($oCommentControllerdeletePropertyValue);
}

// Источник данных
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Informationsystem_Item_Comment')
);

$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

$bItem = !is_null($oInformationsystem_Item->id);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('comments.*', array(Core_QueryBuilder::expression('CONCAT_WS(\' \', `comments`.`subject`, `comments`.`text`)'), 'fulltext')))
)->addCondition(
	array('join' => array('comment_informationsystem_items', 'comments.id', '=', 'comment_informationsystem_items.comment_id'))
);

if (!$bItem)
{
	$oAdmin_Form_Dataset->addCondition(
		array('straightJoin' => array())
	)->addCondition(
		array('join' => array('informationsystem_items', 'comment_informationsystem_items.informationsystem_item_id', '=', 'informationsystem_items.id')
		)
	)->addCondition(
		array('where' => array('informationsystem_items.deleted', '=', 0))
	);
}

// Ограничения по parent_id делаем только при выводе комментариев конкретного ИЭ
if ($iInformationsystemItemId || $comment_parent_id)
{
	$oAdmin_Form_Dataset->addCondition(array('where' =>
		array('parent_id', '=', $comment_parent_id)
	));
}

$additionalParams = !is_null($oInformationsystem_Item->id)
	? '&informationsystem_item_id=' . $oInformationsystem_Item->id
	: ('&informationsystem_id=' . $oInformationsystem->id .
		($iInformationsystemGroupId
		? '&informationsystem_group_id=' . $iInformationsystemGroupId
		: ''));

$commentLink = $comment_parent_id ? '&parent_id=' . $comment_parent_id : '';

$oAdmin_Form_Dataset
	->changeField('active', 'link', '{path}?hostcms[action]=changeActive&hostcms[checked][0][{id}]=1' . $additionalParams . $commentLink)
	->changeField('active', 'onclick', "$.adminLoad({path: '{path}', additionalParams: 'hostcms[checked][0][{id}]=1" . $additionalParams . $commentLink ."', action: 'changeActive', windowId: '{windowId}'}); return false")
	->changeField('short_text', 'link', '{path}?parent_id={id}' . $additionalParams)
	->changeField('short_text', 'onclick', "$.adminLoad({path: '{path}',additionalParams: 'parent_id={id}" . $additionalParams ."', windowId: '{windowId}'}); return false");

// Комментарии информационного элемента
if ($bItem)
{
	$oAdmin_Form_Dataset->addCondition(
		array('where' => array('comment_informationsystem_items.informationsystem_item_id', '=', $oInformationsystem_Item->id))
	);
}
elseif ($oInformationsystem->id) // Комментарии информационной системы
{
	$oAdmin_Form_Dataset->addCondition(
		array('where' => array('informationsystem_items.informationsystem_id', '=', $oInformationsystem->id))
	);
}
else
{
	$oAdmin_Form_Dataset->addCondition(
		array('join' => array('informationsystems', 'informationsystems.id', '=', 'informationsystem_items.informationsystem_id')
		)
	)->addCondition(
		array('where' => array('informationsystems.site_id', '=', CURRENT_SITE))
	);
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addExternalReplace('{shop_item_id}', 0);
$oAdmin_Form_Controller->addExternalReplace('{informationsystem_item_id}', intval($oInformationsystem_Item->id));

// Показ формы
$oAdmin_Form_Controller->execute();