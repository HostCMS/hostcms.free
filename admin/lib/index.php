<?php
/**
 * Libs.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'lib');

// Код формы
$iAdmin_Form_Id = 32;
$sAdminFormAction = '/admin/lib/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Lib.menu_list'))
	->pageTitle(Core::_('Lib.menu_list'));

$lib_dir_id = intval(Core_Array::getGet('lib_dir_id', 0));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(

	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Lib.lib_page'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
)->add(

	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Lib_Dir.lib_dir_folder'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Lib.import'))
		->icon('fa fa-download')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/lib/import/index.php', NULL, NULL, 'lib_dir_id=' . $lib_dir_id)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/lib/import/index.php', NULL, NULL, 'lib_dir_id=' . $lib_dir_id)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Глобальный поиск
$additionalParams = 'lib_dir_id=' . $lib_dir_id;

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

// Строка навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Lib.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

if ($lib_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oLibDir = Core_Entity::factory('Lib_Dir')->find($lib_dir_id);

	if (!is_null($oLibDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'lib_dir_id=' . intval($oLibDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oLibDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oLibDir = $oLibDir->getParent());

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

$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('importLibs');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'importLibs')
{
	$oUserCurrent = Core_Auth::getCurrentUser();
	if (!$oUserCurrent->read_only)
	{
		if (isset($_FILES['json_file']) && intval($_FILES['json_file']['size']) > 0)
		{
			try {
				$content = Core_File::read($_FILES['json_file']['tmp_name']);

				$oLib_Import_Controller = Admin_Form_Action_Controller::factory(
					'Lib_Import_Controller', $oAdmin_Form_Action
				);

				$oLib_Import_Controller
					->content($content)
					->lib_dir_id($lib_dir_id)
					// ->execute()
					;

				$oAdmin_Form_Controller->addAction($oLib_Import_Controller);
			}
			catch (Exception $exc) {
				Core_Message::show($exc->getMessage(), "error");
			}
		}
	}
}

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oLib_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Lib_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$oLib_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oLib_Controller_Edit);
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

// Действие "Экспорт"
$oAdminFormActionExportLibs = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('exportLibs');

if ($oAdminFormActionExportLibs && $oAdmin_Form_Controller->getAction() == 'exportLibs')
{
	$oLib_Export_Controller = Admin_Form_Action_Controller::factory(
		'Lib_Export_Controller', $oAdminFormActionExportLibs
	);

	$oLib_Export_Controller
		->controller($oAdmin_Form_Controller)
		->export();
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

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Lib_Dir')
);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
		->addCondition(array('where' => array('lib_dirs.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('lib_dirs.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('lib_dirs.parent_id', '=', $lib_dir_id)));
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Lib')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
		->addCondition(array('where' => array('libs.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('libs.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('libs.lib_dir_id', '=', $lib_dir_id)));
}

$oAdmin_Form_Dataset
	->changeField('name', 'type', 1);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
