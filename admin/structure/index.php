<?php
/**
 * Structure.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'structure');

// Код формы
$iAdmin_Form_Id = 82;
$sAdminFormAction = '/admin/structure/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oParentStructure = Core_Entity::factory('Structure', Core_Array::getGet('parent_id', 0));

$sFormTitle = $oParentStructure->id
	? $oParentStructure->name
	: Core::_('Structure.title');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

if (!is_null(Core_Array::getGet('loadDocumentText')) && Core_Array::getGet('document_id'))
{
	$oDocument = Core_Entity::factory('Document', intval(Core_Array::getGet('document_id')));

	$aCSS = array();

	if ($oDocument->template_id)
	{
		$oTemplate = $oDocument->Template;

		do{
			$aCSS[] = "/templates/template{$oTemplate->id}/style.css?" . Core_Date::sql2timestamp($oTemplate->timestamp);
		} while ($oTemplate = $oTemplate->getParent());
	}

	$aJson = array(
		'id' => $oDocument->id,
		'document_dir_id' => $oDocument->document_dir_id,
		'template_id' => $oDocument->template_id,
		'editHref' => $oAdmin_Form_Controller->getAdminActionLoadHref('/admin/document/index.php', 'edit', NULL, 1, $oDocument->id, 'document_dir_id=' . intval($oDocument->document_dir_id)),
		'text' => $oDocument->text,
		'css' => $aCSS
	);

	Core::showJSON($aJson);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_shortcuts'))
	&& !is_null(Core_Array::getGet('queryString'))
	&& Core_Array::getGet('entity_id')
)
{
	$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString')))));
	$entity_id = Core_Array::getGet('entity_id', 0, 'int');
	$mode = Core_Array::getGet('mode', 0, 'int');

	// $oShop = Core_Entity::factory('Shop', $entity_id);

	$oSite = Core_Entity::factory('Site', CURRENT_SITE);

	$aJSON = array();

	if (strlen($sQuery))
	{
		$aJSON[0] = array(
			'id' => 0,
			'label' => Core::_('Admin.root') . ' [0]'
		);

		$oStructures = $oSite->Structures;
		$oStructures->queryBuilder()
			->where('structures.shortcut_id', '=', 0)
			->limit(Core::$mainConfig['autocompleteItems']);

		switch ($mode)
		{
			// Вхождение
			case 0:
			default:
				$oStructures->queryBuilder()->where('structures.name', 'LIKE', '%' . str_replace(' ', '%', $sQuery) . '%');
			break;
			// Вхождение с начала
			case 1:
				$oStructures->queryBuilder()->where('structures.name', 'LIKE', $sQuery . '%');
			break;
			// Вхождение с конца
			case 2:
				$oStructures->queryBuilder()->where('structures.name', 'LIKE', '%' . $sQuery);
			break;
			// Точное вхождение
			case 3:
				$oStructures->queryBuilder()->where('structures.name', '=', $sQuery);
			break;
		}

		$aStructures = $oStructures->findAll();

		foreach ($aStructures as $oStructure)
		{
			// $sParents = $oShop_Group->groupPathWithSeparator();

			$aJSON[] = array(
				'id' => $oStructure->id,
				// 'label' => $sParents . ' [' . $oStructure->id . ']'
				'label' => $oStructure->name . ' [' . $oStructure->id . ']'
			);
		}
	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$sMenuPath = '/admin/structure/menu/index.php';
$sPropertyPath = '/admin/structure/property/index.php';

$parent_id = intval(Core_Array::getGet('parent_id', 0));
$additionalParamsProperties = "structure_id={$parent_id}";

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Structure.main_menu'))
		->icon('fa fa-sitemap')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Admin_Form.add'))
				->icon('fa fa-plus')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
				)
		)->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Structure.properties'))
				->icon('fa fa-gears')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sPropertyPath, NULL, NULL, $additionalParamsProperties)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sPropertyPath, NULL, NULL, $additionalParamsProperties)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Structure_Menu.menus'))
		->icon('fa fa-list-ul')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sMenuPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sMenuPath, NULL, NULL, '')
		)
)
;

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

if ($oParentStructure->id)
{
	$href = $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, $oParentStructure->id);
	$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, $oParentStructure->id);

	$oAdmin_Form_Controller->addEntity(
		$oAdmin_Form_Controller->getTitleEditIcon($href, $onclick)
	);
}

// Глобальный поиск
$sGlobalSearch = Core_Array::getGet('globalSearch', '', 'trim');

$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html('
			<div class="row search-field margin-bottom-20">
				<div class="col-xs-12">
					<form action="' . $oAdmin_Form_Controller->getPath() . '" method="GET">
						<input type="text" name="globalSearch" class="form-control" placeholder="' . Core::_('Admin.placeholderGlobalSearch') . '" value="' . htmlspecialchars($sGlobalSearch) . '" />
						<i class="fa fa-times-circle no-margin" onclick="' . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), '', '', $additionalParamsProperties) . '"></i>
						<button type="submit" class="btn btn-default global-search-button" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('', '', $additionalParamsProperties) . '"><i class="fa-solid fa-magnifying-glass fa-fw"></i></button>
					</form>
				</div>
			</div>
		')
);

$sGlobalSearch = str_replace(' ', '%', Core_DataBase::instance()->escapeLike($sGlobalSearch));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Structure.parent_dir'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

if ($parent_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oStructure = Core_Entity::factory('Structure')->find($parent_id);

	if (!is_null($oStructure->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'parent_id=' . intval($oStructure->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oStructure->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oStructure = $oStructure->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}

		// Добавляем все хлебные крошки контроллеру
		$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
	}
}

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oStructure_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Structure_Controller_Edit', $oAdmin_Form_Action
	);

	$oStructure_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oStructure_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oStructureControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oStructureControllerApply);
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

// Действие "Загрузка списка документов"
$oAdminFormActionLoadDocumentList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadDocumentList');

if ($oAdminFormActionLoadDocumentList && $oAdmin_Form_Controller->getAction() == 'loadDocumentList')
{
	$oStructureControllerLoadDocumentList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadDocumentList
	);

	$oStructureControllerLoadDocumentList
		->model(Core_Entity::factory('Document'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('document_dir_id', '=', Core_Array::getGet('document_dir_id')))
		)->addCondition(
			array('where' => array('site_id', '=', CURRENT_SITE))
		)
		->addCondition(
			array('clearOrderBy' => array())
		)
		->addCondition(
			array('orderBy' => array('name'))
		);

	$oAdmin_Form_Controller->addAction($oStructureControllerLoadDocumentList);
}

// Действие "Загрузка списка типовых динамических страниц"
$oAdminFormActionLoadLibList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadLibList');

if ($oAdminFormActionLoadLibList && $oAdmin_Form_Controller->getAction() == 'loadLibList')
{
	$oStructureControllerLoadLibList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadLibList
	);
	$oStructureControllerLoadLibList
		->model(Core_Entity::factory('Lib'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('lib_dir_id', '=', Core_Array::getGet('lib_dir_id')))
		);

	$oAdmin_Form_Controller->addAction($oStructureControllerLoadLibList);
}

// Действие "Загрузка свойств типовых динамических страниц"
$oAdminFormActionLoadLibList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadLibProperties');

if ($oAdminFormActionLoadLibList && $oAdmin_Form_Controller->getAction() == 'loadLibProperties')
{
	$oStructure_Controller_Libproperties = Admin_Form_Action_Controller::factory(
		'Structure_Controller_Libproperties', $oAdminFormActionLoadLibList
	);

	$lib_id = intval(Core_Array::getGet('lib_id'));

	$oStructure_Controller_Libproperties->libId($lib_id);

	$oAdmin_Form_Controller->addAction($oStructure_Controller_Libproperties);
}

// Действие "Загрузка списка XSL-шаблонов для раздела"
$oAdminFormActionLoadXslList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadXslList');

if ($oAdminFormActionLoadXslList && $oAdmin_Form_Controller->getAction() == 'loadXslList')
{
	$oStructureControllerLoadXslList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadXslList
	);
	$oStructureControllerLoadXslList
		->model(Core_Entity::factory('Xsl'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('xsl_dir_id', '=', intval(Core_Array::getGet('xsl_dir_id'))))
		)
		->addIDs(TRUE);

	$oAdmin_Form_Controller->addAction($oStructureControllerLoadXslList);
}

// Действие "Загрузка списка TPL-шаблонов для раздела"
$oAdminFormActionLoadTplList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadTplList');

if ($oAdminFormActionLoadTplList && $oAdmin_Form_Controller->getAction() == 'loadTplList')
{
	$oStructureControllerLoadTplList = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Load_Select_Options', $oAdminFormActionLoadTplList
	);
	$oStructureControllerLoadTplList
		->model(Core_Entity::factory('Tpl'))
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('tpl_dir_id', '=', intval(Core_Array::getGet('tpl_dir_id'))))
		)
		->addIDs(TRUE);

	$oAdmin_Form_Controller->addAction($oStructureControllerLoadTplList);
}

// Действие "Удаление значения свойства"
$oAdminFormActiondeletePropertyValue = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deletePropertyValue');

if ($oAdminFormActiondeletePropertyValue && $oAdmin_Form_Controller->getAction() == 'deletePropertyValue')
{
	$oStructureControllerdeletePropertyValue = Admin_Form_Action_Controller::factory(
		'Property_Controller_Delete_Value', $oAdminFormActiondeletePropertyValue
	);

	$oStructureControllerdeletePropertyValue->linkedObject(
		array(
			Core_Entity::factory('Structure_Property_List', CURRENT_SITE)
		)
	);

	$oAdmin_Form_Controller->addAction($oStructureControllerdeletePropertyValue);
}

$oAdminFormActionRollback = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('rollback');

if ($oAdminFormActionRollback && $oAdmin_Form_Controller->getAction() == 'rollback')
{
	$oControllerRollback = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Rollback', $oAdminFormActionRollback
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerRollback);
}

// Действие "Удаление файла"
$oAdminFormActionDeleteLibFile = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteLibFile');

if ($oAdminFormActionDeleteLibFile && $oAdmin_Form_Controller->getAction() == 'deleteLibFile')
{
	$oLib_Controller_Delete_File = Admin_Form_Action_Controller::factory(
		'Lib_Controller_Delete_File', $oAdminFormActionDeleteLibFile
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLib_Controller_Delete_File);
}

// Действие "Создать ярлык"
$oAdminFormActionShortcut = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('shortcut');

if ($oAdminFormActionShortcut && $oAdmin_Form_Controller->getAction() == 'shortcut')
{
	$oAdmin_Form_Action_Controller_Type_Shortcut = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Shortcut', $oAdminFormActionShortcut
	);

	$oAdmin_Form_Action_Controller_Type_Shortcut
		->title(Core::_('Structure.add_shortcut_title'))
		->selectCaption(Core::_('Structure.add_item_shortcut_structure_id'))
		->value($oParentStructure->id)
		;

	$oSite = Core_Entity::factory('Site', CURRENT_SITE);

	$iCount = $oSite->Structures->getCount();

	if ($iCount < Core::$mainConfig['switchSelectToAutocomplete'])
	{
		$oStructure_Controller_Edit = Admin_Form_Action_Controller::factory(
			'Structure_Controller_Edit', $oAdmin_Form_Action
		);

		// Список директорий генерируется другим контроллером
		$oAdmin_Form_Action_Controller_Type_Shortcut->selectOptions(array(' … ') + $oStructure_Controller_Edit->fillStructureList(CURRENT_SITE));
	}
	else
	{
		$oAdmin_Form_Action_Controller_Type_Shortcut->autocomplete(TRUE);
	}

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_Form_Action_Controller_Type_Shortcut);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Structure')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('structures.id', '=', $sGlobalSearch)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('structures.name', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('structures.path', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('structures.seo_title', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('structures.seo_description', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('structures.seo_keywords', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('structures.parent_id', '=', $parent_id)));
}

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('structures.site_id', '=', CURRENT_SITE)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Список значений для фильтра и поля
$aStructure_Menus = Core_Entity::factory('Structure_Menu')->getAllBySite_id(CURRENT_SITE);
$aList = array();
foreach ($aStructure_Menus as $oStructure_Menu)
{
	$aList[$oStructure_Menu->id] = $oStructure_Menu->name;
}

$oAdmin_Form_Dataset
	->changeField('structure_menu_id', 'type', 8)
	->changeField('structure_menu_id', 'list', $aList);

// Показ формы
$oAdmin_Form_Controller->execute();