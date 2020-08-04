<?php
/**
 * Antispam.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'antispam');

// Код формы
$iAdmin_Form_Id = 211;
$sAdminFormAction = '/admin/antispam/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Antispam.title'))
	->pageTitle(Core::_('Antispam.title'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Antispam.stopwords'))
		->icon('fa fa-ban')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/antispam/stopword/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/antispam/stopword/index.php', NULL, NULL, '')
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Antispam.countries_list'))
		->icon('fa fa-flag')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/antispam/country/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/antispam/country/index.php', NULL, NULL, '')
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Antispam.domains'))
		->icon('fa fa-globe')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/antispam/domain/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/antispam/domain/index.php', NULL, NULL, '')
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Antispam.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

// $oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Antispam_Log')
);

$oAdmin_Language = Core_Entity::factory('Admin_Language')->getByShortname(Core_Array::getSession('current_lng'));

// Ограничение по сайту
$oAdmin_Form_Dataset->addCondition(
	array('select' => array('antispam_logs.*',
		array('antispam_country_languages.name', 'country_name')
	))
)->addCondition(
		array('leftJoin' => array('antispam_country_languages', 'antispam_logs.antispam_country_id', '=', 'antispam_country_languages.antispam_country_id', array(
			array('AND' => array('antispam_country_languages.admin_language_id', '=', $oAdmin_Language->id))
		))
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();