<?php
/**
 * Information systems.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'informationsystem');

// Код формы
$iAdmin_Form_Id = 12;
$sAdminFormAction = '/admin/informationsystem/item/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$iInformationsystemId = intval(Core_Array::getGet('informationsystem_id'));
$iInformationsystemGroupId = intval(Core_Array::getGet('informationsystem_group_id', 0));

$oInformationsystem = Core_Entity::factory('Informationsystem')->find($iInformationsystemId);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Informationsystem_Item.show_information_groups_title', $oInformationsystem->name))
	->pageTitle(Core::_('Informationsystem_Item.show_information_groups_title', $oInformationsystem->name));

if (!is_null(Core_Array::getGet('shortcuts')) && !is_null(Core_Array::getGet('term')))
{
	$aJSON = array();

	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('term'))));
	$iInformationsystemId = intval(Core_Array::getGet('informationsystem_id'));
	$oInformationsystem = Core_Entity::factory('Informationsystem', $iInformationsystemId);

	if (strlen($sQuery))
	{
		$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;
		$oInformationsystem_Groups->queryBuilder()
			->where('informationsystem_groups.name', 'LIKE', '%' . $sQuery . '%')
			->limit(Core::$mainConfig['autocompleteItems']);

		$aInformationsystem_Groups = $oInformationsystem_Groups->findAll(FALSE);

		foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
		{
			$sParents = $oInformationsystem_Group->groupPathWithSeparator();

			$aJSON[] = array(
				'id' => $oInformationsystem_Group->id,
				'text' => $sParents . ' [' . $oInformationsystem_Group->id . ']',
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_move_groups'))
	&& !is_null(Core_Array::getGet('queryString'))
	&& Core_Array::getGet('entity_id')
)
{
	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));
	$entity_id = intval(Core_Array::getGet('entity_id'));
	$mode = intval(Core_Array::getGet('mode'));

	$oInformationsystem = Core_Entity::factory('Informationsystem', $entity_id);

	$aExclude = strlen(Core_Array::getGet('exclude'))
		? json_decode(Core_Array::getGet('exclude'), TRUE)
		: array();

	$aJSON = array();

	if (strlen($sQuery))
	{
		$aJSON[0] = array(
			'id' => 0,
			'label' => Core::_('Informationsystem_Item.root') . ' [0]'
		);

		$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;
		$oInformationsystem_Groups->queryBuilder()
			->limit(Core::$mainConfig['autocompleteItems']);

		switch ($mode)
		{
			// Вхождение
			case 0:
			default:
				$oInformationsystem_Groups->queryBuilder()->where('informationsystem_groups.name', 'LIKE', '%' . $sQuery . '%');
			break;
			// Вхождение с начала
			case 1:
				$oInformationsystem_Groups->queryBuilder()->where('informationsystem_groups.name', 'LIKE', $sQuery . '%');
			break;
			// Вхождение с конца
			case 2:
				$oInformationsystem_Groups->queryBuilder()->where('informationsystem_groups.name', 'LIKE', '%' . $sQuery);
			break;
			// Точное вхождение
			case 3:
				$oInformationsystem_Groups->queryBuilder()->where('informationsystem_groups.name', '=', $sQuery);
			break;
		}

		count($aExclude) && $oInformationsystem_Groups->queryBuilder()
			->where('informationsystem_groups.id', 'NOT IN', $aExclude);

		$aInformationsystem_Groups = $oInformationsystem_Groups->findAll();

		foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
		{
			$sParents = $oInformationsystem_Group->groupPathWithSeparator();

			$aJSON[] = array(
				'id' => $oInformationsystem_Group->id,
				'label' => $sParents . ' [' . $oInformationsystem_Group->id . ']'
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_shortcut_groups'))
	&& !is_null(Core_Array::getGet('queryString'))
	&& Core_Array::getGet('entity_id')
)
{
	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));
	$entity_id = intval(Core_Array::getGet('entity_id'));
	$mode = intval(Core_Array::getGet('mode'));

	$oInformationsystem = Core_Entity::factory('Informationsystem', $entity_id);

	$aJSON = array();

	if (strlen($sQuery))
	{
		$aJSON[0] = array(
			'id' => 0,
			'label' => Core::_('Informationsystem_Item.root') . ' [0]'
		);

		$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;
		$oInformationsystem_Groups->queryBuilder()
			->limit(Core::$mainConfig['autocompleteItems']);

		switch ($mode)
		{
			// Вхождение
			case 0:
			default:
				$oInformationsystem_Groups->queryBuilder()->where('informationsystem_groups.name', 'LIKE', '%' . $sQuery . '%');
			break;
			// Вхождение с начала
			case 1:
				$oInformationsystem_Groups->queryBuilder()->where('informationsystem_groups.name', 'LIKE', $sQuery . '%');
			break;
			// Вхождение с конца
			case 2:
				$oInformationsystem_Groups->queryBuilder()->where('informationsystem_groups.name', 'LIKE', '%' . $sQuery);
			break;
			// Точное вхождение
			case 3:
				$oInformationsystem_Groups->queryBuilder()->where('informationsystem_groups.name', '=', $sQuery);
			break;
		}

		$aInformationsystem_Groups = $oInformationsystem_Groups->findAll();

		foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
		{
			$sParents = $oInformationsystem_Group->groupPathWithSeparator();

			$aJSON[] = array(
				'id' => $oInformationsystem_Group->id,
				'label' => $sParents . ' [' . $oInformationsystem_Group->id . ']'
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete')) && !is_null(Core_Array::getGet('queryString')))
{
	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));
	$iInformationsystemId = intval(Core_Array::getGet('informationsystem_id'));
	$oInformationsystem = Core_Entity::factory('Informationsystem', $iInformationsystemId);

	$aJSON = array();

	if (strlen($sQuery))
	{
		if (is_null(Core_Array::getGet('show_group')))
		{
			$iInformationsystemGroupId = intval(Core_Array::getGet('informationsystem_group_id'));

			$oInformationsystem_Items = $oInformationsystem->Informationsystem_Items;
			$oInformationsystem_Items->queryBuilder()
				->where('informationsystem_items.informationsystem_group_id', '=', $iInformationsystemGroupId)
				->where('informationsystem_items.name', 'LIKE', '%' . $sQuery . '%')
				->limit(Core::$mainConfig['autocompleteItems']);

			$aInformationsystem_Items = $oInformationsystem_Items->findAll(FALSE);

			foreach ($aInformationsystem_Items as $oInformationsystem_Item)
			{
				$aJSON[] = array(
					'id' => $oInformationsystem_Item->id,
					'label' => $oInformationsystem_Item->name,
				);
			}
		}
		elseif (!is_null(Core_Array::getGet('show_group')))
		{
			$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;
			$oInformationsystem_Groups->queryBuilder()
				->where('informationsystem_groups.name', 'LIKE', '%' . $sQuery . '%')
				->limit(Core::$mainConfig['autocompleteItems']);

			$aInformationsystem_Groups = $oInformationsystem_Groups->findAll(FALSE);

			foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
			{
				$aParentGroups = array();

				$aTmpGroup = $oInformationsystem_Group;

				// Добавляем все директории от текущей до родителя.
				do {
					$aParentGroups[] = $aTmpGroup->name;
				} while ($aTmpGroup = $aTmpGroup->getParent());

				$sParents = implode(' → ', array_reverse($aParentGroups));

				$aJSON[] = array(
					'id' => $oInformationsystem_Group->id,
					'label' => $sParents . ' [' . $oInformationsystem_Group->id . ']',
				);
			}
		}
	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$sInformationsystemItemProperties = '/admin/informationsystem/item/property/index.php';
$additionalParamsItemProperties = 'informationsystem_id=' . $iInformationsystemId . '&informationsystem_group_id=' . $iInformationsystemGroupId;

$sInformationsystemGroupProperties = '/admin/informationsystem/group/property/index.php';

$sInformationsystemComments = '/admin/informationsystem/item/comment/index.php';
$additionalParamsComments = 'informationsystem_id=' . $iInformationsystemId . '&informationsystem_group_id=' . $iInformationsystemGroupId;

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Informationsystem_Item.information_system_top_menu_items'))
		->icon('fa fa-list-alt')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Informationsystem_Item.show_information_groups_link2'))
				->img('/admin/images/page_add.gif')
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Informationsystem_Item.show_information_groups_link3'))
				->img('/admin/images/page_gear.gif')
				->icon('fa fa-gears')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sInformationsystemItemProperties, NULL, NULL, $additionalParamsItemProperties)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sInformationsystemItemProperties, NULL, NULL, $additionalParamsItemProperties)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Informationsystem_Item.export'))
				->icon('fa fa-upload')
				->img('/admin/images/export.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/informationsystem/item/export/index.php', NULL, NULL, $additionalParamsItemProperties)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/informationsystem/item/export/index.php', NULL, NULL, $additionalParamsItemProperties)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Informationsystem_Item.import'))
				->icon('fa fa-download')
				->img('/admin/images/import.gif')
				->href(
          $oAdmin_Form_Controller->getAdminLoadHref('/admin/informationsystem/item/import/index.php', NULL, NULL, $additionalParamsItemProperties)
				)
				->onclick(
          $oAdmin_Form_Controller->getAdminLoadAjax('/admin/informationsystem/item/import/index.php', NULL, NULL, $additionalParamsItemProperties)
				)
		)
)
->add(
		Admin_Form_Entity::factory('Menu')
		->name(Core::_('Informationsystem_Group.information_system_top_menu_groups'))
		->icon('fa fa-folder-open')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Informationsystem_Group.show_information_groups_link1'))
				->img('/admin/images/folder_add.gif')
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
				->name(Core::_('Informationsystem_Group.show_information_groups_link4'))
				->img('/admin/images/folder_gear.gif')
				->icon('fa fa-gears')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sInformationsystemGroupProperties, NULL, NULL, $additionalParamsItemProperties)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sInformationsystemGroupProperties, NULL, NULL, $additionalParamsItemProperties)
				)
		)
)
->add(
		Admin_Form_Entity::factory('Menu')
		->name(Core::_('Informationsystem_Item.show_all_comments_top_menu'))
		->icon('fa fa-comments')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Informationsystem_Item.show_comments_link_show_all_comments'))
				->img('/admin/images/comments.gif')
				->icon('fa fa-comments')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sInformationsystemComments, NULL, NULL, $additionalParamsComments)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sInformationsystemComments, NULL, NULL, $additionalParamsComments)
				)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$sGlobalSearch = trim(strval(Core_Array::getGet('globalSearch')));

$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html('
			<div class="row search-field margin-bottom-20">
				<div class="col-xs-12">
					<form action="' . $oAdmin_Form_Controller->getPath() . '" method="GET">
						<input type="text" name="globalSearch" class="form-control" placeholder="' . Core::_('Admin.placeholderGlobalSearch') . '" value="' . htmlspecialchars($sGlobalSearch) . '" />
						<i class="fa fa-search no-margin" onclick="$(this).siblings(\'input[type=submit]\').click()"></i>
						<i class="fa fa-times-circle no-margin" onclick="' . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), '', '', $additionalParamsItemProperties) . '"></i>
						<input type="submit" class="hidden" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('', '', $additionalParamsItemProperties) . '" />
					</form>
				</div>
			</div>
		')
);

$sGlobalSearch = Core_DataBase::instance()->escapeLike($sGlobalSearch);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Строка навигации
$iInformationsystemDirId = $oInformationsystem->informationsystem_dir_id;

// Путь к контроллеру формы разделов информационных систем
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
if ($iInformationsystemDirId)
{
	// Если передана родительская группа - строим хлебные крошки
	$oInformationsystemDir = Core_Entity::factory('Informationsystem_Dir')->find($iInformationsystemDirId);

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

$additionalParams = 'informationsystem_id=' . $iInformationsystemId;

// Ссылка на название ИС
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oInformationsystem->name)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
	)
);

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
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
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

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action/* && $oAdmin_Form_Controller->getAction() == 'edit'*/)
{
	$oInformationsystem_Item_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Informationsystem_Item_Controller_Edit', $oAdmin_Form_Action
	);

	$oInformationsystem_Item_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystem_Item_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oInformationsystemItemControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystemItemControllerApply);
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

// Действие "Перенести"
$oAdminFormActionMove = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('move');

if ($oAdminFormActionMove && $oAdmin_Form_Controller->getAction() == 'move')
{
	$oInformationsystemItemControllerMove = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Move', $oAdminFormActionMove
	);

	$oInformationsystemItemControllerMove
		->title(Core::_('Informationsystem_Item.move_items_groups_title'))
		->selectCaption(Core::_('Informationsystem_Item.move_items_groups_information_groups_id'))
		->value($iInformationsystemGroupId);

	$iCount = $oInformationsystem->Informationsystem_Groups->getCount();

	if ($iCount < Core::$mainConfig['switchSelectToAutocomplete'])
	{
		$aExclude = array();

		$aChecked = $oAdmin_Form_Controller->getChecked();

		foreach ($aChecked as $datasetKey => $checkedItems)
		{
			// Exclude just dirs
			if ($datasetKey == 0)
			{
				foreach ($checkedItems as $key => $value)
				{
					$aExclude[] = $key;
				}
			}
		}

		// Список директорий генерируется другим контроллером
		$oInformationsystemItemControllerMove
			->selectOptions(array(' … ') + $oInformationsystem_Item_Controller_Edit->fillInformationsystemGroup($iInformationsystemId, 0, $aExclude));
	}
	else
	{
		$oInformationsystemItemControllerMove->autocomplete(TRUE);
	}

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystemItemControllerMove);
}

// Действие "Создать ярлык"
$oAdminFormActionShortcut = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('shortcut');

if ($oAdminFormActionShortcut && $oAdmin_Form_Controller->getAction() == 'shortcut')
{
	$oInformationsystemItemControllerShortcut = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Shortcut', $oAdminFormActionShortcut
	);

	$oInformationsystemItemControllerShortcut
		->title(Core::_('Informationsystem_Item.add_information_item_shortcut_title'))
		->selectCaption(Core::_('Informationsystem_Item.add_item_shortcut_information_groups_id'))
		->value($iInformationsystemGroupId);

	$iCount = $oInformationsystem->Informationsystem_Groups->getCount();

	if ($iCount < Core::$mainConfig['switchSelectToAutocomplete'])
	{
		// Список директорий генерируется другим контроллером
		$oInformationsystemItemControllerShortcut->selectOptions(array(' … ') + $oInformationsystem_Item_Controller_Edit->fillInformationsystemGroup($iInformationsystemId));
	}
	else
	{
		$oInformationsystemItemControllerShortcut->autocomplete(TRUE);
	}

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystemItemControllerShortcut);
}

// Действие "Загрузка элементов ИС"
$oAdminFormActionLoadInformationItemList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadInformationItemList');

if ($oAdminFormActionLoadInformationItemList && $oAdmin_Form_Controller->getAction() == 'loadInformationItemList')
{
	$oInformationsystem_Controller_Load_Select_Options = Admin_Form_Action_Controller::factory(
		'Informationsystem_Controller_Load_Select_Options',  $oAdminFormActionLoadInformationItemList
	);

	$oInformationsystem_Controller_Load_Select_Options
		->model(
			Core_Entity::factory('Informationsystem_Item')->informationsystem_id($iInformationsystemId)
		)
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('informationsystem_group_id', '=', $iInformationsystemGroupId))
		)->addCondition(
			array('where' => array('informationsystem_id', '=', $iInformationsystemId))
		);

	$oAdmin_Form_Controller->addAction($oInformationsystem_Controller_Load_Select_Options);
}

// Действие "Удаление значения свойства"
$oAdminFormActiondeletePropertyValue = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deletePropertyValue');

if ($oAdminFormActiondeletePropertyValue && $oAdmin_Form_Controller->getAction() == 'deletePropertyValue')
{
	$oInformationsystemControllerdeletePropertyValue = Admin_Form_Action_Controller::factory(
		'Property_Controller_Delete_Value', $oAdminFormActiondeletePropertyValue
	);

	$oInformationsystemControllerdeletePropertyValue
		->linkedObject(
			array(
				Core_Entity::factory('Informationsystem_Group_Property_List', $iInformationsystemId),
				Core_Entity::factory('Informationsystem_Item_Property_List', $iInformationsystemId)
			)
		);

	$oAdmin_Form_Controller->addAction($oInformationsystemControllerdeletePropertyValue);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Informationsystem_Group')
);

$oAdmin_Form_Dataset->addCondition(
	array('select' => array('*', array(Core_QueryBuilder::expression("''"), 'datetime')))
)->addCondition(
	array('where' => array('informationsystem_id', '=', $iInformationsystemId))
)
->changeField('name', 'class', 'semi-bold');

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
		->addCondition(array('where' => array('informationsystem_groups.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('informationsystem_groups.path', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('informationsystem_groups.seo_title', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('informationsystem_groups.seo_description', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('informationsystem_groups.seo_keywords', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('informationsystem_groups.parent_id', '=', $iInformationsystemGroupId)));
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Informationsystem_Item')
);

$oAdmin_Form_Dataset
	->addCondition(
		array('where' => array('informationsystem_id', '=', $iInformationsystemId))
	)
	->changeField('active', 'link', '/admin/informationsystem/item/index.php?hostcms[action]=changeActive&hostcms[checked][{dataset_key}][{id}]=1&informationsystem_group_id={informationsystem_group_id}&informationsystem_id={informationsystem_id}')
	->changeField('active', 'onclick', "$.adminLoad({path: '/admin/informationsystem/item/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&informationsystem_group_id={informationsystem_group_id}&informationsystem_id={informationsystem_id}', action: 'changeActive', windowId: '{windowId}'}); return false")
	->changeField('indexing', 'link', '/admin/informationsystem/item/index.php?hostcms[action]=changeIndexation&hostcms[checked][{dataset_key}][{id}]=1&informationsystem_group_id={informationsystem_group_id}&informationsystem_id={informationsystem_id}')
	->changeField('indexing', 'onclick', "$.adminLoad({path: '/admin/informationsystem/item/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&informationsystem_group_id={informationsystem_group_id}&informationsystem_id={informationsystem_id}',action: 'changeIndexation', windowId: '{windowId}'}); return false")
	->changeField('adminComment', 'type', 10)
	//->changeField('name', 'type', 1)
	->changeField('active', 'list', "1=" . Core::_('Admin_Form.yes') . "\n" . "0=" . Core::_('Admin_Form.no'))
	->changeField('indexing', 'list', "1=" . Core::_('Admin_Form.yes') . "\n" . "0=" . Core::_('Admin_Form.no'))
	->changeField('img', 'type', 10);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
		->addCondition(array('where' => array('informationsystem_items.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('informationsystem_items.path', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('informationsystem_items.seo_title', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('informationsystem_items.seo_description', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('informationsystem_items.seo_keywords', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset->addCondition(array('where' => array('informationsystem_items.informationsystem_group_id', '=', $iInformationsystemGroupId)));
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Действие "Удаление файла большого изображения"
$oAdminFormActionDeleteLargeImage = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteLargeImage');

if ($oAdminFormActionDeleteLargeImage && $oAdmin_Form_Controller->getAction() == 'deleteLargeImage')
{
	$oInformationsystemControllerDeleteLargeImage = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAdminFormActionDeleteLargeImage
	);

	$oInformationsystemControllerDeleteLargeImage
		->methodName('deleteLargeImage')
		->divId(array('preview_large_image', 'delete_large_image'));

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystemControllerDeleteLargeImage);
}

// Действие "Удаление файла малого изображения"
$oAdminFormActionDeleteSmallImage = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteSmallImage');

if ($oAdminFormActionDeleteSmallImage && $oAdmin_Form_Controller->getAction() == 'deleteSmallImage')
{
	$oInformationsystemControllerDeleteSmallImage = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAdminFormActionDeleteSmallImage
	);

	$oInformationsystemControllerDeleteSmallImage
		->methodName('deleteSmallImage')
		->divId(array('preview_small_image', 'delete_small_image'));

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($oInformationsystemControllerDeleteSmallImage);
}

// Показ формы
$oAdmin_Form_Controller->execute();