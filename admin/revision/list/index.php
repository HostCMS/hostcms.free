<?php
/**
 * Revision.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'revision');

// Код формы
$iAdmin_Form_Id = 206;
$sAdminFormAction = '/admin/revision/list/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$tableName = Core_Array::getRequest('table');
$singular = Core_Inflection::getSingular($tableName);

$titleName = class_exists($singular . '_Model')
	? Core::_($singular . '.model_name')
	: NULL;

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Revision_List.title', $titleName))
	->pageTitle(Core::_('Revision_List.title', $titleName));

$sRevisionPath = '/admin/revision/index.php';

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Revision.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sRevisionPath, NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sRevisionPath, NULL, NULL, '')
	)
)->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Revision_List.title', $titleName, FALSE))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath())
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Revision')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
$oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

if (!class_exists($singular . '_Model'))
{
	throw new Core_Exception('Wrong Model Name');
}

$oModel = Core_Entity::factory($singular);
$columnName = $oModel->getNameColumn();
$getPrimaryKeyName = $oModel->getPrimaryKeyName();

// Добавляем внешнее поле, доступное для сортировки и фильтрации
$oAdmin_Form_Dataset->addExternalField('name');

$oAdmin_Form_Dataset->addCondition(
		array('select' => array(
			array($tableName . '.' . $columnName, 'name'),
			'revisions.datetime',
			'revisions.user_id',
			'revisions.id',
			'revisions.entity_id'
		)
	)
)->addCondition(
		array('leftJoin' => array($tableName, $tableName . '.' . $getPrimaryKeyName, '=', 'revisions.entity_id'))
)->addCondition(
		array('where' => array('revisions.model', '=', $singular)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();