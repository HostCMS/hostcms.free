<?php
/**
 * Information systems.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'informationsystem');

// Код формы
$iAdmin_Form_Id = 67;
$sAdminFormAction = '/admin/informationsystem/group/property/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$informationsystem_id = intval(Core_Array::getGet('informationsystem_id'));
$informationsystem_group_id = intval(Core_Array::getGet('informationsystem_group_id', 0));

$oInformationsystem = Core_Entity::factory('Informationsystem')->find($informationsystem_id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Informationsystem_Group.show_information_propertys_groups_title', $oInformationsystem->name))
	->pageTitle(Core::_('Informationsystem_Group.show_information_propertys_groups_title', $oInformationsystem->name));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Property.menu'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Property_Dir.menu'))
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

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Строка навигации
$property_dir_id = intval(Core_Array::getGet('property_dir_id', 0));

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

$additionalParams = 'informationsystem_id=' . $informationsystem_id;
$sInformationsystemPath = '/admin/informationsystem/item/index.php';

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

// Путь по группам информационных элементов
if ($informationsystem_group_id)
{
	$oInformationsystemGroup = Core_Entity::factory('Informationsystem_Group')->find($informationsystem_group_id);

	if (!is_null($oInformationsystemGroup->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'informationsystem_id=' . $informationsystem_id . '&informationsystem_group_id=' . $oInformationsystemGroup->id;

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

$additionalParams = 'informationsystem_id=' . $informationsystem_id . '&informationsystem_group_id=' . $informationsystem_group_id;

// Корневой раздел дополнительных свойств
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Property.parent_dir'))
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
			$additionalParams = 'informationsystem_id=' . $informationsystem_id . '&informationsystem_group_id=' . $informationsystem_group_id . '&property_dir_id=' . intval($oProperty_Dir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oProperty_Dir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oProperty_Dir = $oProperty_Dir->getParent());

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

// Глобальный поиск
$additionalParams = 'informationsystem_id=' . $informationsystem_id . '&informationsystem_group_id=' . $informationsystem_group_id;

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

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oProperty_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Property_Controller_Edit', $oAdmin_Form_Action
	);

	$oProperty_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	$oProperty_Controller_Edit
		->linkedObject(Core_Entity::factory('Informationsystem_Group_Property_List', $informationsystem_id));

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oProperty_Controller_Edit);
}

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

// Действие "Перенести"
$oAdminFormActionMove = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('move');

if ($oAdminFormActionMove && $oAdmin_Form_Controller->getAction() == 'move')
{
	$Admin_Form_Action_Controller_Type_Move = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Move', $oAdminFormActionMove
	);

	$Admin_Form_Action_Controller_Type_Move
		->title(Core::_('Property.move_title'))
		->selectCaption(Core::_('Property.move_dir_id'))
		->value($property_dir_id);

	$linkedObject = Core_Entity::factory('Informationsystem_Group_Property_List', $informationsystem_id);

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

	$Admin_Form_Action_Controller_Type_Move
		// Список директорий генерируется другим контроллером
		->selectOptions(array(' … ') + Property_Controller_Edit::fillPropertyDir($linkedObject, 0, $aExclude));

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Move);
}

// Действие "Объединить"
$oAdminFormActionMerge = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('merge');

if ($oAdminFormActionMerge && $oAdmin_Form_Controller->getAction() == 'merge')
{
	$oAdmin_Form_Action_Controller_Type_Merge = new Admin_Form_Action_Controller_Type_Merge($oAdminFormActionMerge);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_Form_Action_Controller_Type_Merge);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Property_Dir')
);

// Ограничение источника 0
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('property_dirs.*'))
)->addCondition(
	array('join' => array('informationsystem_group_property_dirs', 'informationsystem_group_property_dirs.property_dir_id', '=', 'property_dirs.id'))
)/*->addCondition(
	array('where' => array('parent_id', '=', $property_dir_id))
)*/->addCondition(
	array('where' => array('informationsystem_group_property_dirs.informationsystem_id', '=', $informationsystem_id))
)
->changeField('name', 'type', 4)
->changeField('name', 'link', "/admin/informationsystem/group/property/index.php?informationsystem_id=" . $informationsystem_id . "&informationsystem_group_id=" . $informationsystem_group_id . "&property_dir_id={id}")
->changeField('name', 'onclick', "$.adminLoad({path: '/admin/informationsystem/group/property/index.php', additionalParams: 'informationsystem_id=" . $informationsystem_id . "&informationsystem_group_id=" . $informationsystem_group_id ."&property_dir_id={id}', windowId: '{windowId}'}); return false");

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('property_dirs.id', '=', $sGlobalSearch)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('property_dirs.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset->addCondition(array('where' => array('parent_id', '=', $property_dir_id)));
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Property')
);

$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Ограничение источника 1
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('properties.*'))
)->addCondition(
	array('join' => array('informationsystem_group_properties', 'informationsystem_group_properties.property_id', '=', 'properties.id'))
)/*->addCondition(
	array('where' => array('property_dir_id', '=', $property_dir_id))
)*/->addCondition(
	array('where' => array('informationsystem_group_properties.informationsystem_id', '=', $informationsystem_id))
);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('properties.id', '=', $sGlobalSearch)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('properties.name', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('properties.guid', '=', $sGlobalSearch)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('properties.tag_name', '=', $sGlobalSearch)))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset->addCondition(array('where' => array('property_dir_id', '=', $property_dir_id)));
}

$oAdmin_Form_Dataset
->changeField('multiple', 'link', "/admin/informationsystem/group/property/index.php?hostcms[action]=changeMultiple&hostcms[checked][{dataset_key}][{id}]=1&informationsystem_id=" . $informationsystem_id . "&informationsystem_group_id=" . $informationsystem_group_id . "&property_dir_id={property_dir_id}")
->changeField('multiple', 'onclick', "$.adminLoad({path: '/admin/informationsystem/group/property/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&informationsystem_id=" . $informationsystem_id . "&informationsystem_group_id=" . $informationsystem_group_id ."&property_dir_id={property_dir_id}', action: 'changeMultiple', windowId: '{windowId}'}); return false")
->changeField('indexing', 'link', "/admin/informationsystem/group/property/index.php?hostcms[action]=changeIndexing&hostcms[checked][{dataset_key}][{id}]=1&informationsystem_id=" . $informationsystem_id . "&informationsystem_group_id=" . $informationsystem_group_id . "&property_dir_id={property_dir_id}")
->changeField('indexing', 'onclick', "$.adminLoad({path: '/admin/informationsystem/group/property/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&informationsystem_id=" . $informationsystem_id . "&informationsystem_group_id=" . $informationsystem_group_id ."&property_dir_id={property_dir_id}', action: 'changeIndexing', windowId: '{windowId}'}); return false");

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();