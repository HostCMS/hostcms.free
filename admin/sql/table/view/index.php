<?php
/**
 * SQL.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'sql');

// Код формы
$iAdmin_Form_Id = 315;
$sAdminFormAction = '/{admin}/sql/table/view/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$tableName = Sql_Controller::sanitizeIdentifiers(Core_Array::getRequest('table'));

$aAdmin_Form_Fields = array();
if (strlen($tableName))
{
	$aFileds = Core_DataBase::instance()->getColumns($tableName);

	$bSetPK = FALSE;
	foreach ($aFileds as $key => $aRow)
	{
		// Set temporary key and order field for the Admin_Form
		if (!$bSetPK && $aRow['key'] == 'PRI')
		{
			$oAdmin_Form->key_field
				= $oAdmin_Form->default_order_field
				= $aRow['name'];

			$bSetPK = TRUE;
		}
		$oAdmin_Form_Field = new Sql_Table_View_Field();
		$oAdmin_Form_Field->id = $key;
		$oAdmin_Form_Field->admin_form_id = $iAdmin_Form_Id;
		$oAdmin_Form_Field->name = $aRow['name'];
		$oAdmin_Form_Field->caption = $aRow['name'];
		$oAdmin_Form_Field->type = 1;
		$oAdmin_Form_Field->filter_type = 0;
		$oAdmin_Form_Field->allow_filter = 1;
		$oAdmin_Form_Field->allow_sorting = 1;
		$oAdmin_Form_Field->show_by_default = 1;
		$oAdmin_Form_Field->view = 0;
		$oAdmin_Form_Field->editable = 1;
		$oAdmin_Form_Field->ico = '';
		$oAdmin_Form_Field->format = '';
		$oAdmin_Form_Field->width = '';

		switch ($aRow['type'])
		{
			case 'int':
				$oAdmin_Form_Field->width = '95px';
			break;
			case 'string':
				if (is_numeric($aRow['defined_max_length'])
					&& $aRow['defined_max_length'] <= 10)
				{
					$oAdmin_Form_Field->width = $aRow['defined_max_length'] <= 3
						? '35px'
						: ($aRow['defined_max_length'] * 10) . 'px';
				}
				else
				{
					$oAdmin_Form_Field->width = $aRow['defined_max_length'] <= 10 && $aRow['defined_max_length'] > 0
						? ($aRow['defined_max_length'] * 10) . 'px'
						: '95px';
				}
			break;
		}

		switch ($aRow['datatype'])
		{
			case 'tinyint':
				$oAdmin_Form_Field->width = '40px';
			break;
			case 'smallint':
				$oAdmin_Form_Field->width = '55px';
			break;
			case 'date':
				$oAdmin_Form_Field->width = '85px';
			break;
			case 'datetime':
				$oAdmin_Form_Field->width = '135px';
			break;
		}

		if ($aRow['type'] == 'int')
		{
			$oAdmin_Form_Field->filter_condition = 1;
		}

		//$oAdmin_Form_Field->class = $aRow['max_length'] > 1000 ? 'truncated' : '';
		$oAdmin_Form_Field->class = 'truncated' . ($aRow['key'] == 'PRI' ? ' semi-bold' : '');
		$aAdmin_Form_Fields[$oAdmin_Form_Field->id] = $oAdmin_Form_Field;
	}
}

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setAdminFormFields($aAdmin_Form_Fields)
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Sql_Table_View.title', $tableName))
	->pageTitle(Core::_('Sql_Table_View.title', $tableName));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

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

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

$oAdmin_Form_Controller->addEntity(
	Sql_Controller::getFieldsIcon($tableName)
)->addEntity(
	Sql_Controller::getIndexesIcon($tableName)
);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Sql.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/sql/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/sql/index.php', NULL, NULL, '')
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Sql.manage_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/{admin}/sql/table/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/sql/table/index.php', NULL, NULL, '')
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Sql_Table_View.title', $tableName))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, 'table=' . $tableName)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, 'table=' . $tableName)
	)
);

$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oSql_Table_View_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Sql_Table_View_Controller_Edit', $oAdmin_Form_Action
	);

	$oSql_Table_View_Controller_Edit->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oSql_Table_View_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Sql_Table_View_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Sql_Table_View_Dataset();
$oAdmin_Form_Dataset->table($tableName);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();