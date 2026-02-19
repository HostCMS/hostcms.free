<?php
/**
 * Field.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

// Код формы
$iAdmin_Form_Id = 320;
$sAdminFormAction = '/{admin}/field/modelfield/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$field_dir_id = Core_Array::getGet('field_dir_id', 0, 'int');
$model = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getRequest('model', '', 'string'));

if (Core_Auth::logged())
{
	// Контроллер формы
	$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

	$oAdmin_Form_Controller->formSettings();

	// Действие "Удаление значения поля"
	if ($oAdmin_Form_Controller->getAction() == 'deleteFieldValue')
	{
		Core_Auth::setCurrentSite();

		ob_start();

		$oAdmin_Form_Action = $oAdmin_Form
			->Admin_Form_Actions
			->getByName('deleteFieldValue');

		$oUser = Core_Auth::getCurrentUser();

		$oField_Controller_Delete_Value = Admin_Form_Action_Controller::factory(
				'Field_Controller_Delete_Value', $oAdmin_Form_Action
			)
			->controller($oAdmin_Form_Controller)
			->model($model);

		try
		{
			foreach ($oAdmin_Form_Controller->checked as $datasetKey => $checkedItems)
			{
				foreach ($checkedItems as $checkedItemId => $v1)
				{
					$oFields = Core_Entity::factory('Field');
					$oFields->queryBuilder()
						->where('model', '=', $model)
						->where('id', '=', $checkedItemId)
						->limit(1);

					$aFields = $oFields->findAll(FALSE);

					if (isset($aFields[0]))
					{
						$oField_Controller_Delete_Value
							->setDatasetId($datasetKey)
							->setObject($aFields[0])
							->execute($oAdmin_Form_Controller->operation);

						$oAdmin_Form_Controller->addMessage(
							$oField_Controller_Delete_Value->getMessage()
						);
					}
				}
			}
		}
		catch (Exception $e)
		{
			$oAdmin_Form_Controller->addMessage(Core_Message::get($e->getMessage(), 'error'));
		}

		Core::showJson(
			array(
				'error' => ob_get_clean() . $oAdmin_Form_Controller->getMessage(),
				'form_html' => '',
				'title' => '',
				'module' => 'field'
			)
		);
	}
}

Core_Auth::authorization($sModule = 'field');

$titleName = class_exists($model . '_Model')
	? Core::_($model . '.model_name')
	: NULL;

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Field_Modelfield.title', $titleName))
	->pageTitle(Core::_('Field_Modelfield.title', $titleName));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Field.add'))
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
		->name(Core::_('Field_Dir.dir_add'))
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

$sFieldPath = '/{admin}/field/index.php';

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Field.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sFieldPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sFieldPath, NULL, NULL, '')
	)
)
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($model)
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, 'model=' . $model)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, 'model=' . $model)
		)
);

if ($field_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oFieldDir = Core_Entity::factory('Field_Dir')->find($field_dir_id);

	if (!is_null($oFieldDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'field_dir_id=' . intval($oFieldDir->id) .'&'. 'model=' . $model;

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oFieldDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oFieldDir = $oFieldDir->getParent());

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

// Действие "Перенести"
$oAdminFormActionMove = $oAdmin_Form->Admin_Form_Actions->getByName('move');

if ($oAdminFormActionMove && $oAdmin_Form_Controller->getAction() == 'move')
{
	$oFieldControllerMove = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Move', $oAdminFormActionMove
	);

	$oField_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Field_Controller_Edit', $oAdminFormActionMove
	);

	$oFieldControllerMove
		->title(Core::_('Field.move_field_dir_title'))
		->selectCaption(Core::_('Field.move_field_dir_id'))
		->value($field_dir_id);

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
	$oFieldControllerMove
		->selectOptions(array(' … ') + $oField_Controller_Edit::fillFieldDir($model, 0, $aExclude, 0));

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oFieldControllerMove);
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oField_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Field_Controller_Edit', $oAdmin_Form_Action
	);

	$oField_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oField_Controller_Edit);
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

// Действие "Удаление значения поля"
/*$oAction = $oAdmin_Form->Admin_Form_Actions->getByName('deleteFieldValue');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteFieldValue')
{
	$Field_Controller_Delete_Value = Admin_Form_Action_Controller::factory(
		'Field_Controller_Delete_Value', $oAction
	)
	->model($model);

	$oAdmin_Form_Controller->addAction($Field_Controller_Delete_Value);
}*/

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Field_Dir')
);

$oAdmin_Form_Dataset->changeField('name', 'class', 'semi-bold');

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' => array('parent_id', '=', $field_dir_id))
)->addCondition(
	array('where' => array('model', '=', $model))
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Field')
);

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' => array('field_dir_id', '=', $field_dir_id))
)->addCondition(
	array('where' => array('model', '=', $model))
)
->changeField('name', 'type', 1);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();