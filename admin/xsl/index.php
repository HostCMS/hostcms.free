<?php
/**
 * XSL.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'xsl');

// Код формы
$iAdmin_Form_Id = 22;
$sAdminFormAction = '/admin/xsl/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Xsl.menu'))
	->pageTitle(Core::_('Xsl.menu'));

// Строка навигации
$xsl_dir_id = intval(Core_Array::getGet('xsl_dir_id', 0));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Xsl.main_menu'))
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
		->name(Core::_('Xsl_Dir.main_menu'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Xsl.import'))
		->icon('fa fa-download')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/xsl/import/index.php', NULL, NULL, 'xsl_dir_id=' . $xsl_dir_id)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/xsl/import/index.php', NULL, NULL, 'xsl_dir_id=' . $xsl_dir_id)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Глобальный поиск
$additionalParams = 'xsl_dir_id=' . $xsl_dir_id;

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

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Xsl.XSL_root_dir'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

if ($xsl_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oXslDir = Core_Entity::factory('Xsl_Dir')->find($xsl_dir_id);

	if (!is_null($oXslDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'xsl_dir_id=' . intval($oXslDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oXslDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oXslDir = $oXslDir->getParent());

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

$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('importXsls');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'importXsls')
{
	$oUserCurrent = Core_Auth::getCurrentUser();
	if (!$oUserCurrent->read_only)
	{
		if (isset($_FILES['json_file']) && intval($_FILES['json_file']['size']) > 0)
		{
			try {
				$content = Core_File::read($_FILES['json_file']['tmp_name']);

				$oXsl_Import_Controller = Admin_Form_Action_Controller::factory(
					'Xsl_Import_Controller', $oAdmin_Form_Action
				);

				$oXsl_Import_Controller
					->content($content)
					->xsl_dir_id($xsl_dir_id)
					// ->execute()
					;

				$oAdmin_Form_Controller->addAction($oXsl_Import_Controller);
			}
			catch (Exception $exc) {
				Core_Message::show($exc->getMessage(), "error");
			}
		}
	}
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oXsl_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Xsl_Controller_Edit', $oAdmin_Form_Action
	);

	$oXsl_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oXsl_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oXslDirControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oXslDirControllerApply);
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

$oAdminFormActionRollback = $oAdmin_Form->Admin_Form_Actions->getByName('rollback');

if ($oAdminFormActionRollback && $oAdmin_Form_Controller->getAction() == 'rollback')
{
	$oControllerRollback = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Rollback', $oAdminFormActionRollback
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerRollback);
}

// Действие "Экспорт"
$oAdminFormActionExportXsl = $oAdmin_Form->Admin_Form_Actions->getByName('exportXsls');

if ($oAdminFormActionExportXsl && $oAdmin_Form_Controller->getAction() == 'exportXsls')
{
	$oXsl_Export_Controller = Admin_Form_Action_Controller::factory(
		'Xsl_Export_Controller', $oAdminFormActionExportXsl
	);

	$oXsl_Export_Controller
		->controller($oAdmin_Form_Controller)
		->export();
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Xsl_Dir')
);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('xsl_dirs.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('xsl_dirs.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('xsl_dirs.parent_id', '=', $xsl_dir_id)));
}

$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Xsl')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('xsls.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('xsls.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('xsls.xsl_dir_id', '=', $xsl_dir_id)));
}

$oAdmin_Form_Dataset->changeField('name', 'type', 1);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
