<?php
/**
 * Trash.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'trash');

// Код формы
$iAdmin_Form_Id = 184;
$sAdminFormAction = '/admin/trash/table/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$tableName = Core_Array::getRequest('table', '', 'trim');
$modelName = Core_Inflection::getSingular($tableName);

$titleName = class_exists($modelName . '_Model')
	? Core::_($modelName . '.model_name')
	: $modelName;

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Trash_Table.title', $titleName))
	->pageTitle(Core::_('Trash_Table.title', $titleName));

$sTrashPath = '/admin/trash/index.php';

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Trash.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sTrashPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sTrashPath, NULL, NULL, '')
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Trash_Table.title', $titleName, FALSE))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath())
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

if (!class_exists($modelName . '_Model'))
{
	$oAdmin_Form_Controller->addMessage(Core_Message::get(Core::_('Trash_Table.model_unavailable', $modelName), 'error'));
}
else
{
	// Источник данных 0
	$oAdmin_Form_Dataset = new Trash_Table_Dataset($tableName);

	// Добавляем источник данных контроллеру формы
	$oAdmin_Form_Controller->addDataset(
		$oAdmin_Form_Dataset
	);

	// Фильтр
	if (isset($oAdmin_Form_Controller->request['admin_form_filter_1015']) && ($oAdmin_Form_Controller->request['admin_form_filter_1015'] != '')
		|| isset($oAdmin_Form_Controller->request['topFilter_1015']) && $oAdmin_Form_Controller->request['topFilter_1015'] != ''
	)
	{
		$value = isset($oAdmin_Form_Controller->request['topFilter_1015'])
			? $oAdmin_Form_Controller->request['topFilter_1015']
			: $oAdmin_Form_Controller->request['admin_form_filter_1015'];

		if ($value !== '')
		{
			$mFilterValue = $oAdmin_Form_Controller->convertLike(strval($value));

			$oEntity = $oAdmin_Form_Dataset->getEntity();

			$oAdmin_Form_Dataset->addCondition(
				array(
					'where' => array($tableName . '.' . $oEntity->getNameColumn(), 'LIKE', $mFilterValue)
				)
			);
		}
		
		function getName($value, $oAdmin_Form_Field)
		{
			return '';
		}

		$oAdmin_Form_Controller->addFilterCallback('getName', 'getName');
	}
}

// Показ формы
$oAdmin_Form_Controller->execute();