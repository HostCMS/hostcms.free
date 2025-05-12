<?php
/**
 * IP addresses.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'ipaddress');

// Код формы
$iAdmin_Form_Id = 25;
$sAdminFormAction = '/{admin}/ipaddress/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oIpaddress_Dir = Core_Entity::factory('Ipaddress_Dir', Core_Array::getGet('ipaddress_dir_id', 0));

$sFormTitle = $oIpaddress_Dir->id
	? $oIpaddress_Dir->name
	: Core::_('Ipaddress.show_ip_title');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

if (Core_Array::getPost('block_ip') && Core_Array::getPost('ip'))
{
	$aJSON = array(
		'result' => ''
	);

	$ip = strval(Core_Array::getPost('ip'));
	$comment = strval(Core_Array::getPost('comment'));

	$bBlocked = $ip != '127.0.0.1'
		&& Ipaddress_Controller::instance()->isBlocked($ip);

	if (!$bBlocked)
	{
		$oIpaddress = Core_Entity::factory('Ipaddress');
		$oIpaddress->ip = $ip;
		$oIpaddress->deny_access = 1;
		$oIpaddress->comment = $comment;
		$oIpaddress->save();

		$aJSON['result'] = 'ok';
	}
	else
	{
		$aJSON['result'] = 'error';
	}

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
		'label' => Core::_('Ipaddress_Dir.root'),
	);

	if (strlen($sQuery))
	{
		$sQueryLike = '%' . str_replace(' ', '%', $sQuery) . '%';

		$oIpaddress_Dirs = Core_Entity::factory('Ipaddress_Dir');
		$oIpaddress_Dirs->queryBuilder()
			->where('ipaddress_dirs.name', 'LIKE', $sQueryLike)
			->limit(Core::$mainConfig['autocompleteItems']);

		$aIpaddress_Dirs = $oIpaddress_Dirs->findAll(FALSE);

		foreach ($aIpaddress_Dirs as $oIpaddress_Dir)
		{
			$aParentDirs = array();

			$aTmpDir = $oIpaddress_Dir;

			// Добавляем все директории от текущей до родителя.
			do {
				$aParentDirs[] = $aTmpDir->name;
			} while ($aTmpDir = $aTmpDir->getParent());

			$sParents = implode(' → ', array_reverse($aParentDirs));

			$aJSON[] = array(
				'id' => $oIpaddress_Dir->id,
				'label' => $sParents
			);
		}
	}

	Core::showJson($aJSON);
}

$additionalParams = "ipaddress_dir_id={$oIpaddress_Dir->id}";

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
		->name(Core::_('Ipaddress.add_dir'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0, $additionalParams)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Ipaddress.filter_menu'))
		->icon('fa-solid fa-filter')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/ipaddress/filter/index.php', NULL, NULL, '', $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/ipaddress/filter/index.php', NULL, NULL, '', $additionalParams)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Ipaddress_Filter.import'))
		->icon('fa fa-download')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/ipaddress/import/index.php', NULL, NULL, 'ipaddress_dir_id=' . $oIpaddress_Dir->id)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/ipaddress/import/index.php', NULL, NULL, 'ipaddress_dir_id=' . $oIpaddress_Dir->id)
		)
);

if (Core::moduleIsActive('counter'))
{
	$oAdmin_Form_Entity_Menus->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Ipaddress.visitor_menu'))
			->icon('fa-solid fa-users')
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/ipaddress/visitor/index.php', NULL, NULL, '', $additionalParams)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/ipaddress/visitor/index.php', NULL, NULL, '', $additionalParams)
			)
		);
}

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

$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Ipaddress.menu'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, ''))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, ''))
);

// Крошки по группам
if ($oIpaddress_Dir->id)
{
	$oIpaddress_Dir_Breadcrumbs = $oIpaddress_Dir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oIpaddress_Dir_Breadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/ipaddress/index.php', NULL, NULL, "ipaddress_dir_id={$oIpaddress_Dir_Breadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/ipaddress/index.php', NULL, NULL, "ipaddress_dir_id={$oIpaddress_Dir_Breadcrumbs->id}"));
	}
	while ($oIpaddress_Dir_Breadcrumbs = $oIpaddress_Dir_Breadcrumbs->getParent());

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

				$oIpaddress_Import_Controller = Admin_Form_Action_Controller::factory(
					'Ipaddress_Import_Controller', $oAdmin_Form_Action
				);

				$oIpaddress_Import_Controller
					->content($content)
					->ipaddress_dir_id($oIpaddress_Dir->id)
					// ->execute()
					;

				$oAdmin_Form_Controller->addAction($oIpaddress_Import_Controller);
			}
			catch (Exception $exc) {
				Core_Message::show($exc->getMessage(), "error");
			}
		}
	}
}

// Действие "Экспорт"
$oAdminFormActionExport = $oAdmin_Form->Admin_Form_Actions->getByName('exportFilters');

if ($oAdminFormActionExport && $oAdmin_Form_Controller->getAction() == 'exportFilters')
{
	$oIpaddress_Export_Controller = Admin_Form_Action_Controller::factory(
		'Ipaddress_Export_Controller', $oAdminFormActionExport
	);

	$oIpaddress_Export_Controller
		->controller($oAdmin_Form_Controller)
		->export();
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oIpaddress_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Ipaddress_Controller_Edit', $oAdmin_Form_Action
	);

	$oIpaddress_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oIpaddress_Controller_Edit);
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

// Действие "Объединить"
$oAdminFormActionMerge = $oAdmin_Form->Admin_Form_Actions->getByName('merge');

if ($oAdminFormActionMerge && $oAdmin_Form_Controller->getAction() == 'merge')
{
	$oAdmin_Form_Action_Controller_Type_Merge = new Admin_Form_Action_Controller_Type_Merge($oAdminFormActionMerge);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_Form_Action_Controller_Type_Merge);
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
		->title(Core::_('Ipaddress.move_items_groups_title'))
		->selectCaption(Core::_('Ipaddress.move_items_groups_ipaddress_dir_id'))
		->value($oIpaddress_Dir->id)
		->autocompletePath(Admin_Form_Controller::correctBackendPath('/{admin}/ipaddress/index.php?autocomplete=1&show_dir=1'))
		->autocompleteEntityId($oSite->id)
		;

	$iCount = Core_Entity::factory('Ipaddress_Dir')->getCount();

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

		$Ipaddress_Controller_Edit = new Ipaddress_Controller_Edit($oAdminFormActionMove);

		$Admin_Form_Action_Controller_Type_Move
			// Список директорий генерируется другим контроллером
			->selectOptions(array(' … ') + $Ipaddress_Controller_Edit->fillIpaddressDir(0, $aExclude));
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
	Core_Entity::factory('Ipaddress_Dir')
);

$oAdmin_Form_Dataset->changeField('name', 'class', 'semi-bold');

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('ipaddress_dirs.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('ipaddress_dirs.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset->addCondition(array('where' => array('parent_id', '=', $oIpaddress_Dir->id)));
}

if (isset($oAdmin_Form_Controller->request['admin_form_filter_89'])
		&& ($oAdmin_Form_Controller->request['admin_form_filter_89'] != '')
	|| isset($oAdmin_Form_Controller->request['topFilter_89'])
		&& $oAdmin_Form_Controller->request['topFilter_89'] != ''
)
{
	$value = isset($oAdmin_Form_Controller->request['topFilter_89'])
		? $oAdmin_Form_Controller->request['topFilter_89']
		: $oAdmin_Form_Controller->request['admin_form_filter_89'];

	$mFilterValue = $oAdmin_Form_Controller->convertLike(strval($value));

	$oAdmin_Form_Dataset->addCondition(
		array(
			'where' => array('ipaddress_dirs.name', 'LIKE', $mFilterValue)
		)
	);
}

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Ipaddress')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
			->addCondition(array('where' => array('ipaddresses.id', '=', is_numeric($sGlobalSearch) ? intval($sGlobalSearch) : 0)))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('ipaddresses.ip', 'LIKE', '%' . $sGlobalSearch . '%')))
			->addCondition(array('setOr' => array()))
			->addCondition(array('where' => array('ipaddresses.comment', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	// Ограничение источника 1 по родительской группе
	$oAdmin_Form_Dataset->addCondition(array('where' => array('ipaddress_dir_id', '=', $oIpaddress_Dir->id)));
}

if (isset($oAdmin_Form_Controller->request['admin_form_filter_89'])
		&& ($oAdmin_Form_Controller->request['admin_form_filter_89'] != '')
	|| isset($oAdmin_Form_Controller->request['topFilter_89'])
		&& $oAdmin_Form_Controller->request['topFilter_89'] != ''
)
{
	$value = isset($oAdmin_Form_Controller->request['topFilter_89'])
		? $oAdmin_Form_Controller->request['topFilter_89']
		: $oAdmin_Form_Controller->request['admin_form_filter_89'];

	$mFilterValue = $oAdmin_Form_Controller->convertLike(strval($value));

	$oAdmin_Form_Dataset->addCondition(
		array(
			'where' => array('ipaddresses.ip', 'LIKE', $mFilterValue)
		)
	);
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

$oAdmin_Form_Controller->addExternalReplace('{ipaddress_dir_id}', $oIpaddress_Dir->id);

// Показ формы
$oAdmin_Form_Controller->execute();
