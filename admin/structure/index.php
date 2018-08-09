<?php
/**
 * Structure.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'structure');

// Код формы
$iAdmin_Form_Id = 82;
$sAdminFormAction = '/admin/structure/index.php';

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
		'template_id' => $oDocument->template_id,
		'text' => $oDocument->text,
		'css' => $aCSS
	);

	Core::showJSON($aJson);
}

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Structure.title'))
	->pageTitle(Core::_('Structure.title'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$sMenuPath = '/admin/structure/menu/index.php';
$sPropertyPath = '/admin/structure/property/index.php';

$parent_id = intval(Core_Array::getGet('parent_id', 0));

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Structure.main_menu'))
		->icon('fa fa-sitemap')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Admin_Form.add'))
				->img('/admin/images/structure_add.gif')
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
				->img('/admin/images/structure_gear.gif')
				->icon('fa fa-gears')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sPropertyPath, NULL, NULL, "structure_id={$parent_id}")
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sPropertyPath, NULL, NULL, "structure_id={$parent_id}")
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Structure_Menu.menus'))
		->icon('fa fa-list-ul')
		->img('/admin/images/menu.gif')
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

	$oStructure_Controller_Libproperties
		->libId($lib_id);

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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Structure')
);

// Добавляем внешнее поле, доступное для сортировки и фильтрации
$oAdmin_Form_Dataset->addExternalField('menu_name');

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('structures.*', array('structure_menus.name', 'menu_name')))
)->addCondition(
	array('leftJoin' => array('structure_menus', 'structures.structure_menu_id', '=', 'structure_menus.id'))
)->addCondition(
	array('where' =>
		array('parent_id', '=', $parent_id)
	)
)->addCondition(
	array('where' =>
		array('structures.site_id', '=', CURRENT_SITE)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
