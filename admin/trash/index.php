<?php
/**
 * Trash.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'trash');

// Код формы
$iAdmin_Form_Id = 183;
$sAdminFormAction = '/admin/trash/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Trash.title'))
	->pageTitle(Core::_('Trash.title'));

if ($oAdmin_Form_Controller->getAction() == 'deleteAll')
{
	ob_start();

	$oAdmin_Form_Dataset = new Trash_Dataset();

	$aTables = $oAdmin_Form_Dataset
		->limit(9999)
		->fillTables()
		->getObjects();

	foreach ($aTables as $oTrash_Entity)
	{
		$oTrash_Entity->delete();
	}

	Core_Log::instance()->clear()
		->status(Core_Log::$SUCCESS)
		->write('All items have been completely deleted from Trash');

	$oAdmin_Form_Controller
		->clearChecked()
		->addMessage(ob_get_clean());
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Trash.empty_trash'))
		->icon('fa fa-trash')
		->class("btn btn-danger")
		->onclick(
			"res = confirm('" . htmlspecialchars(Core::_('Admin_Form.confirm_dialog', Core::_('Trash.empty_trash'))) . "'); if (res) { " . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteAll', NULL, '') . " } return res;"
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Источник данных 0
$oAdmin_Form_Dataset = new Trash_Dataset();

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();