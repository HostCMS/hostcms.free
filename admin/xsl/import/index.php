<?php
/**
 * Xsl.
*
* @package HostCMS
* @version 7.x
* @author Hostmake LLC
* @copyright © 2005-2024, https://www.hostcms.ru
*/
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'xsl');

$xsl_dir_id = Core_Array::getGet('xsl_dir_id', 0);

$oAdmin_Form_Controller = Admin_Form_Controller::create();

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path('/admin/xsl/index.php');

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module_Abstract::factory($sModule))
	->pageTitle(Core::_('Xsl.import'));

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Xsl.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/xsl/index.php')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/xsl/index.php')
		)
);

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Xsl.import'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL)
		)
);

$oSite = Core_Entity::factory('Site', CURRENT_SITE);

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action('/admin/xsl/index.php')
	->enctype('multipart/form-data');

$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

$oAdmin_Form_Entity_Form->add($oMainTab
	->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(Admin_Form_Entity::factory('File')
			->name("json_file")
			->caption(Core::_('Xsl.import_file'))
			->largeImage(array('show_params' => FALSE))
			->smallImage(array('show' => FALSE))
			->divAttr(array('class' => 'form-group col-xs-12')))
	)
);

$oAdmin_Form_Entity_Form->add(
	Core_Html_Entity::factory('Input')
		->name('hostcms[checked][1][0]')
		->value(1)
		->type('hidden')
);

$oAdmin_Form_Entity_Form->add(
	Admin_Form_Entity::factory('Button')
	->name('show_form')
	->type('submit')
	->value(Core::_('Xsl.import_button'))
	->class('applyButton btn btn-blue')
	->onclick($oAdmin_Form_Controller->getAdminSendForm('importXsls', NULL, 'xsl_dir_id=' . $xsl_dir_id))
);

$oAdmin_Form_Entity_Form->execute();
$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->module($sModule)
	//->content(iconv("UTF-8", "UTF-8//IGNORE//TRANSLIT", ob_get_clean()))
	->content(ob_get_clean())
	->title(Core::_('Xsl.import'))
	->execute();