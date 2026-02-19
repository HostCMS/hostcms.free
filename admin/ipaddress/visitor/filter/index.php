<?php
/**
 * Ipaddress.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'ipaddress');

// Код формы
$iAdmin_Form_Id = 385;
$sAdminFormAction = '/{admin}/ipaddress/visitor/filter/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oIpaddress_Visitor_Filter_Dir = Core_Entity::factory('Ipaddress_Visitor_Filter_Dir', Core_Array::getGet('ipaddress_visitor_filter_dir_id', 0));

$sFormTitle = $oIpaddress_Visitor_Filter_Dir->id
	? $oIpaddress_Visitor_Filter_Dir->name
	: Core::_('Ipaddress_Visitor_Filter.title');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

if (Core_Array::getPost('add_filter'))
{
	ob_start();

	Admin_Form_Entity::factory('Select')
		->name('type[]')
		->options(Ipaddress_Visitor_Filter_Controller::getTypes())
		->divAttr(array('class' => 'col-xs-12 col-sm-2 property-data'))
		->onchange('$.changeIpaddressFilterOption(this)')
		->controller($oAdmin_Form_Controller)
		->execute();

	Admin_Form_Entity::factory('Input')
		->name('get_name[]')
		->divAttr(array('class' => 'col-xs-12 col-sm-4 property-data ipaddress-filter-get-name hidden'))
		->placeholder(Core::_('Ipaddress_Filter.placeholder_name'))
		->controller($oAdmin_Form_Controller)
		->execute();

	Admin_Form_Entity::factory('Input')
		->name('header_name[]')
		->divAttr(array('class' => 'col-xs-8 col-sm-6 col-lg-2 property-data ipaddress-filter-header-name hidden'))
		->placeholder(Core::_('Ipaddress_Filter.placeholder_name'))
		->controller($oAdmin_Form_Controller)
		->execute();

	Admin_Form_Entity::factory('Select')
		->name('header_case_sensitive[]')
		->options(array(
			Core::_('Ipaddress_Filter.case_unsensitive'),
			Core::_('Ipaddress_Filter.case_sensitive')
		))
		->divAttr(array('class' => 'col-xs-4 col-sm-2 col-lg-1 property-data ipaddress-filter-header-case-sensitive hidden'))
		->value(0)
		->controller($oAdmin_Form_Controller)
		->execute();

	Admin_Form_Entity::factory('Select')
		->name('condition[]')
		->options(Ipaddress_Visitor_Filter_Controller::getConditions())
		->divAttr(array('class' => 'col-xs-12 col-sm-2 col-lg-1 property-data ipaddress-filter-condition'))
		->controller($oAdmin_Form_Controller)
		->execute();

	Admin_Form_Entity::factory('Input')
		->name('value[]')
		->divAttr(array('class' => 'col-xs-8 col-sm-4 col-lg-3 property-data ipaddress-filter-value'))
		->placeholder(Core::_('Ipaddress_Filter.placeholder_value'))
		->controller($oAdmin_Form_Controller)
		->execute();

	Admin_Form_Entity::factory('Select')
		->name('case_sensitive[]')
		->options(array(
			Core::_('Ipaddress_Filter.case_unsensitive'),
			Core::_('Ipaddress_Filter.case_sensitive')
		))
		->divAttr(array('class' => 'col-xs-3 col-sm-2 col-lg-1 property-data ipaddress-filter-case-sensitive'))
		->value(1)
		->controller($oAdmin_Form_Controller)
		->execute();

	Admin_Form_Entity::factory('Div')
		->class('col-xs-12 col-sm-4 col-lg-3 property-data filter-days-wrapper')
		->add(
			Admin_Form_Entity::factory('Code')->html('
				<input type="text" class="form-control" name="times[]" value="1"/><span> ' . Core::_('Ipaddress_Visitor_Filter.times') . ' </span><input type="text" class="form-control" name="hours[]" value="3"/><span> ' . Core::_('Ipaddress_Visitor_Filter.hours') . '</span>
			')
		)
		->controller($oAdmin_Form_Controller)
		->execute();

	$aJSON = array(
		'status' => 'success',
		'html' => ob_get_clean()
	);

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_dir'))
	&& !is_null(Core_Array::getGet('queryString'))
)
{
	$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString')))));

	$aJSON[0] = array(
		'id' => 0,
		'label' => Core::_('Ipaddress_Visitor_Filter_Dir.root'),
	);

	if (strlen($sQuery))
	{
		$sQueryLike = '%' . str_replace(' ', '%', $sQuery) . '%';

		$oIpaddress_Visitor_Filter_Dirs = Core_Entity::factory('Ipaddress_Visitor_Filter_Dir');
		$oIpaddress_Visitor_Filter_Dirs->queryBuilder()
			->where('ipaddress_visitor_filter_dirs.name', 'LIKE', $sQueryLike)
			->limit(Core::$mainConfig['autocompleteItems']);

		$aIpaddress_Visitor_Filter_Dirs = $oIpaddress_Visitor_Filter_Dirs->findAll(FALSE);

		foreach ($aIpaddress_Visitor_Filter_Dirs as $oIpaddress_Visitor_Filter_Dir)
		{
			$aParentDirs = array();

			$aTmpDir = $oIpaddress_Visitor_Filter_Dir;

			// Добавляем все директории от текущей до родителя.
			do {
				$aParentDirs[] = $aTmpDir->name;
			} while ($aTmpDir = $aTmpDir->getParent());

			$sParents = implode(' → ', array_reverse($aParentDirs));

			$aJSON[] = array(
				'id' => $oIpaddress_Visitor_Filter_Dir->id,
				'label' => $sParents
			);
		}
	}

	Core::showJson($aJSON);
}

$additionalParams = "ipaddress_visitor_filter_dir_id={$oIpaddress_Visitor_Filter_Dir->id}";

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0, $additionalParams)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Ipaddress_Visitor_Filter.add_group'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, $additionalParams)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Ipaddress_Visitor_Filter.import'))
		->icon('fa fa-download')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/ipaddress/visitor/filter/import/index.php', NULL, NULL, 'ipaddress_visitor_filter_dir_id=' . $oIpaddress_Visitor_Filter_Dir->id)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/ipaddress/visitor/filter/import/index.php', NULL, NULL, 'ipaddress_visitor_filter_dir_id=' . $oIpaddress_Visitor_Filter_Dir->id)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

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
		->name(Core::_('Ipaddress.show_ip_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/ipaddress/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/ipaddress/index.php', NULL, NULL, '')
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Ipaddress_Visitor.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/ipaddress/visitor/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/ipaddress/visitor/index.php', NULL, NULL, '')
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Ipaddress_Visitor_Filter.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

// Крошки по группам
if ($oIpaddress_Visitor_Filter_Dir->id)
{
	$oIpaddress_Visitor_Filter_Dir_Breadcrumbs = $oIpaddress_Visitor_Filter_Dir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oIpaddress_Visitor_Filter_Dir_Breadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/ipaddress/visitor/filter/index.php', NULL, NULL, "ipaddress_visitor_filter_dir_id={$oIpaddress_Visitor_Filter_Dir_Breadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/ipaddress/visitor/filter/index.php', NULL, NULL, "ipaddress_visitor_filter_dir_id={$oIpaddress_Visitor_Filter_Dir_Breadcrumbs->id}"));
	}
	while ($oIpaddress_Visitor_Filter_Dir_Breadcrumbs = $oIpaddress_Visitor_Filter_Dir_Breadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('importFilters');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'importFilters')
{
	$oUserCurrent = Core_Auth::getCurrentUser();
	if (!$oUserCurrent->read_only)
	{
		if (isset($_FILES['json_file']) && intval($_FILES['json_file']['size']) > 0)
		{
			try {
				$content = Core_File::read($_FILES['json_file']['tmp_name']);

				$oIpaddress_Visitor_Filter_Import_Controller = Admin_Form_Action_Controller::factory(
					'Ipaddress_Visitor_Filter_Import_Controller', $oAdmin_Form_Action
				);

				$oIpaddress_Visitor_Filter_Import_Controller
					->content($content)
					->ipaddress_visitor_filter_dir_id($oIpaddress_Visitor_Filter_Dir->id)
					// ->execute()
					;

				$oAdmin_Form_Controller->addAction($oIpaddress_Visitor_Filter_Import_Controller);
			}
			catch (Exception $exc) {
				Core_Message::show($exc->getMessage(), "error");
			}
		}
	}
}

// Действие "Экспорт"
$oAdminFormActionExportFilters = $oAdmin_Form->Admin_Form_Actions->getByName('exportFilters');

if ($oAdminFormActionExportFilters && $oAdmin_Form_Controller->getAction() == 'exportFilters')
{
	$oIpaddress_Visitor_Filter_Export_Controller = Admin_Form_Action_Controller::factory(
		'Ipaddress_Visitor_Filter_Export_Controller', $oAdminFormActionExportFilters
	);

	$oIpaddress_Visitor_Filter_Export_Controller
		->controller($oAdmin_Form_Controller)
		->export();
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oIpaddress_Visitor_Filter_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Ipaddress_Visitor_Filter_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$oIpaddress_Visitor_Filter_Controller_Edit
		->addEntity(
			$oAdmin_Form_Entity_Breadcrumbs
		);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oIpaddress_Visitor_Filter_Controller_Edit);
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

	$oSite = Core_Entity::factory('Site', CURRENT_SITE);

	$Admin_Form_Action_Controller_Type_Move
		->title(Core::_('Ipaddress_Visitor_Filter.move_items_groups_title'))
		->selectCaption(Core::_('Ipaddress_Visitor_Filter.move_items_groups_ipaddress_visitor_filter_dir_id'))
		->value($oIpaddress_Visitor_Filter_Dir->id)
		->autocompletePath(Admin_Form_Controller::correctBackendPath('/{admin}/ipaddress/visitor/filter/index.php?autocomplete=1&show_dir=1'))
		->autocompleteEntityId($oSite->id)
		;

	$iCount = Core_Entity::factory('Ipaddress_Visitor_Filter_Dir')->getCount();

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

		$Ipaddress_Visitor_Filter_Controller_Edit = new Ipaddress_Visitor_Filter_Controller_Edit($oAdminFormActionMove);

		$Admin_Form_Action_Controller_Type_Move
			// Список директорий генерируется другим контроллером
			->selectOptions(array(' … ') + $Ipaddress_Visitor_Filter_Controller_Edit->fillIpaddressVisitorFilterDir(0, $aExclude));
	}
	else
	{
		$Admin_Form_Action_Controller_Type_Move->autocomplete(TRUE);
	}

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Move);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Ipaddress_Visitor_Filter_Dir')
);

$oAdmin_Form_Dataset->changeField('name', 'class', 'semi-bold');

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()));

	is_numeric($sGlobalSearch) && $oAdmin_Form_Dataset
			->addCondition(array('where' => array('ipaddress_visitor_filter_dirs.id', '=', intval($sGlobalSearch))))
			->addCondition(array('setOr' => array()));

	$oAdmin_Form_Dataset
			->addCondition(array('where' => array('ipaddress_visitor_filter_dirs.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset->addCondition(array('where' => array('parent_id', '=', $oIpaddress_Visitor_Filter_Dir->id)));
}

$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Ipaddress_Visitor_Filter')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()));

	is_numeric($sGlobalSearch) && $oAdmin_Form_Dataset
			->addCondition(array('where' => array('ipaddress_visitor_filters.id', '=', intval($sGlobalSearch))))
			->addCondition(array('setOr' => array()));

	$oAdmin_Form_Dataset
			->addCondition(array('where' => array('ipaddress_visitor_filters.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset->addCondition(array('where' => array('ipaddress_visitor_filter_dir_id', '=', $oIpaddress_Visitor_Filter_Dir->id)));
}

$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();