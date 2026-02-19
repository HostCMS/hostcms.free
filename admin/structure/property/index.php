<?php
/**
 * Structure.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'structure');

// Код формы
$iAdmin_Form_Id = 67;
$sAdminFormAction = '/{admin}/structure/property/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Property.title'))
	->pageTitle(Core::_('Property.title'));

$sStructurePath = '/{admin}/structure/index.php';

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
)
;

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$structure_id = Core_Array::getGet('structure_id', 0, 'int');

// Строка навигации для структуры
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Structure.parent_dir'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sStructurePath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sStructurePath, NULL, NULL, '')
	)
);

if ($structure_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oStructure = Core_Entity::factory('Structure')->find($structure_id);

	if (!is_null($oStructure->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'parent_id=' . intval($oStructure->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oStructure->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sStructurePath, NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sStructurePath, NULL, NULL, $additionalParams)
				);
		} while ($oStructure = $oStructure->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

// Строка навигации для свойств
$property_dir_id = Core_Array::getGet('property_dir_id', 0, 'int');

$additionalParams = 'structure_id=' . $structure_id;

// Элементы строки навигации
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

if ($property_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oProperty_Dir = Core_Entity::factory('Property_Dir')->find($property_dir_id);

	if (!is_null($oProperty_Dir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'structure_id=' . $structure_id . '&property_dir_id=' . intval($oProperty_Dir->id);

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
$additionalParams = 'structure_id=' . $structure_id;

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
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oProperty_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Property_Controller_Edit', $oAdmin_Form_Action
	);

	$oProperty_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	$oProperty_Controller_Edit
		// Объект с настроенными связями для получения соответствующих св-в и разделов св-в
		// Для структуры сайта это сайт
		->linkedObject(Core_Entity::factory('Structure_Property_List', CURRENT_SITE))
		;

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oProperty_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oStructureControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oStructureControllerApply);
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

// Действие "Перенести"
$oAdminFormActionMove = $oAdmin_Form->Admin_Form_Actions->getByName('move');

if ($oAdminFormActionMove && $oAdmin_Form_Controller->getAction() == 'move')
{
	$Admin_Form_Action_Controller_Type_Move = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Move', $oAdminFormActionMove
	);

	$Admin_Form_Action_Controller_Type_Move
		->title(Core::_('Property.move_title'))
		->selectCaption(Core::_('Property.move_dir_id'))
		->value($property_dir_id);

	$linkedObject = Core_Entity::factory('Structure_Property_List', CURRENT_SITE);

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
$oAdminFormActionMerge = $oAdmin_Form->Admin_Form_Actions->getByName('merge');

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
		array('join' => array('structure_property_dirs', 'structure_property_dirs.property_dir_id', '=', 'property_dirs.id'))
	)->addCondition(
		array('where' =>
			array('parent_id', '=', $property_dir_id)
		)
	)->addCondition(
		array('where' =>
			array('structure_property_dirs.site_id', '=', CURRENT_SITE)
		)
	)
	->changeField('name', 'type', 4)
	->changeField('name', 'link', "/{admin}/structure/property/index.php?property_dir_id={id}")
	->changeField('name', 'onclick', "$.adminLoad({path: '/{admin}/structure/property/index.php', additionalParams: 'property_dir_id={id}', windowId: '{windowId}'}); return false");

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Property')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

// Ограничение источника 1
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('properties.*'))
)->addCondition(
	array('join' => array('structure_properties', 'structure_properties.property_id', '=', 'properties.id'))
)/*->addCondition(
	array('where' => array('property_dir_id', '=', $property_dir_id))
)*/->addCondition(
	array('where' => array('structure_properties.site_id', '=', CURRENT_SITE))
);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()));

	is_numeric($sGlobalSearch) && $oAdmin_Form_Dataset
			->addCondition(array('where' => array('properties.id', '=', intval($sGlobalSearch))))
			->addCondition(array('setOr' => array()));

	$oAdmin_Form_Dataset
			->addCondition(array('where' => array('properties.name', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('properties.guid', '=', $sGlobalSearch)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('properties.tag_name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset->addCondition(array('where' => array('property_dir_id', '=', $property_dir_id)));
}

$oAdmin_Form_Dataset
	->changeField('multiple', 'link', "/{admin}/structure/property/index.php?hostcms[action]=changeMultiple&hostcms[checked][{dataset_key}][{id}]=1&property_dir_id={property_dir_id}")
	->changeField('multiple', 'onclick', "$.adminLoad({path: '/{admin}/structure/property/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&property_dir_id={property_dir_id}', action: 'changeMultiple', windowId: '{windowId}'}); return false")
	->changeField('indexing', 'link', "/{admin}/structure/property/index.php?hostcms[action]=changeIndexing&hostcms[checked][{dataset_key}][{id}]=1&property_dir_id={property_dir_id}")
	->changeField('indexing', 'onclick', "$.adminLoad({path: '/{admin}/structure/property/index.php', additionalParams: 'hostcms[checked][{dataset_key}][{id}]=1&property_dir_id={property_dir_id}', action: 'changeIndexing', windowId: '{windowId}'}); return false");

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
