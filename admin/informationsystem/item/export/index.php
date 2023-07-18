<?php
/**
 * Information systems.
*
* @package HostCMS
* @version 7.x
* @author Hostmake LLC
* @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
*/
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'informationsystem');

// Получаем параметры
$oInformationsystem = Core_Entity::factory('Informationsystem', Core_Array::getRequest('informationsystem_id', 0));
$oInformationsystem_Dir = $oInformationsystem->Informationsystem_Dir;
$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', Core_Array::getRequest('informationsystem_group_id', 0));

if (Core_Array::getPost('action') == 'export')
{
	$aSeparator = array(",", ";");
	$iSeparator = Core_Array::getPost('export_separator', 1, 'int');

	$oInformationsystem_Item_Export_Csv_Controller = new Informationsystem_Item_Export_Csv_Controller(
		Core_Array::getPost('informationsystem_id', 0),
		!is_null(Core_Array::getPost('export_external_properties_allow_items')),
		!is_null(Core_Array::getPost('export_external_properties_allow_groups'))
	);

	$oInformationsystem_Item_Export_Csv_Controller
		->separator($iSeparator > 1 ? "" : $aSeparator[$iSeparator])
		->encoding(Core_Array::getPost('export_encoding', "UTF-8"))
		->parentGroup(Core_Array::getPost('informationsystem_groups_parent_id', 0))
		->execute();
}

// Создаем экземпляры классов
$oAdmin_Form_Controller = Admin_Form_Controller::create();

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path('/admin/informationsystem/item/export/index.php');

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('Informationsystem_Item.export'))
	;

$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Informationsystem.menu'))
	->href($oAdmin_Form_Controller->getAdminLoadHref(
		'/admin/informationsystem/index.php'
	))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
		'/admin/informationsystem/index.php'
	))
);

// Крошки по директориям магазинов
if ($oInformationsystem_Dir->id)
{
	$oInformationsystemDirBreadcrumbs = $oInformationsystem_Dir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
		->name($oInformationsystemDirBreadcrumbs->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
				'/admin/informationsystem/index.php', NULL, NULL, "informationsystem_dir_id={$oInformationsystemDirBreadcrumbs->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
				'/admin/informationsystem/index.php', NULL, NULL, "informationsystem_dir_id={$oInformationsystemDirBreadcrumbs->id}"
		));
	}
	while ($oInformationsystemDirBreadcrumbs = $oInformationsystemDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oInformationsystem->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
			'/admin/informationsystem/item/index.php', NULL, NULL, "informationsystem_id={$oInformationsystem->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
			'/admin/informationsystem/item/index.php', NULL, NULL, "informationsystem_id={$oInformationsystem->id}"
		))
);

// Крошки по группам товаров
if ($oInformationsystem_Group->id)
{
	$oInformationsystemGroupBreadcrumbs = $oInformationsystem_Group;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oInformationsystemGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref(
				'/admin/informationsystem/item/index.php', NULL, NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystemGroupBreadcrumbs->id}"
			))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
				'/admin/informationsystem/item/index.php', NULL, NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystemGroupBreadcrumbs->id}"
			));
	}
	while ($oInformationsystemGroupBreadcrumbs = $oInformationsystemGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Informationsystem_Item.export'))
		->href($oAdmin_Form_Controller->getAdminLoadHref(
			$oAdmin_Form_Controller->getPath(), NULL, NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystem_Group->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
			$oAdmin_Form_Controller->getPath(), NULL, NULL, "informationsystem_id={$oInformationsystem->id}&informationsystem_group_id={$oInformationsystem_Group->id}"
		))
);

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($oAdmin_Form_Controller->getPath())
	->target('_blank');

$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);
$windowId = $oAdmin_Form_Controller->getWindowId();

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');

$oAdmin_Form_Entity_Form->add($oMainTab);

$oMainTab
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Radiogroup')
			->radio(array(
				Core::_('Informationsystem_Item.export_list_separator1'),
				Core::_('Informationsystem_Item.export_list_separator2')
			))
			->ico(array(
				'fa-bolt',
				'fa-bolt'
			))
			->name('export_separator')
			->value(1)
			->divAttr(array('class' => 'form-group col-xs-12', 'id' => 'export_separator'))
			->caption(Core::_('Informationsystem_Item.export_list_separator'))))
			->add(Admin_Form_Entity::factory('Div')->class('row')->add(
				Admin_Form_Entity::factory('Code')->html("<script>$(function() { $('#{$windowId} #export_list_separator').buttonset(); });</script>"))
			);

	$oMainTab->add(
		Admin_Form_Entity::factory('Div')->class('row')->add(
			Admin_Form_Entity::factory('Select')
				->name("export_encoding")
				->options(array(
					'UTF-8' => Core::_('Informationsystem_Item.input_file_encoding1'),
					'Windows-1251' => Core::_('Informationsystem_Item.input_file_encoding0')
				))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6', 'id' => 'import_price_encoding'))
				->caption(Core::_('Informationsystem_Item.export_encoding')))
		)
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Select')
			->name("informationsystem_groups_parent_id")
			->options(array(' … ') + Informationsystem_Item_Controller_Edit::fillInformationsystemGroup($oInformationsystem->id))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6', 'id' => 'informationsystem_groups_parent_id'))
			->caption(Core::_('Informationsystem_Item.export_parent_group'))
			->value($oInformationsystem_Group->id)))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Checkbox')
			->name("export_external_properties_allow_items")
			->caption(Core::_('Informationsystem_Item.export_external_properties_allow_items'))
			->divAttr(array('class' => 'form-group col-xs-12', 'id' => 'export_external_properties_allow_items'))
			->value(TRUE)))
	->add(Admin_Form_Entity::factory('Div')->class('row')->add(
		Admin_Form_Entity::factory('Checkbox')
			->name("export_external_properties_allow_groups")
			->caption(Core::_('Informationsystem_Item.export_external_properties_allow_groups'))
			->divAttr(array('class' => 'form-group col-xs-12', 'id' => 'export_external_properties_allow_groups'))
			->value(TRUE)))
	->add(Admin_Form_Entity::factory('Div')->class('row')
		->add(Core_Html_Entity::factory('Input')->type('hidden')->name('action')->value('export'))
		->add(Core_Html_Entity::factory('Input')->type('hidden')->name('informationsystem_group_id')->value(Core_Array::getGet('informationsystem_group_id')))
		->add(Core_Html_Entity::factory('Input')->type('hidden')->name('informationsystem_id')->value(Core_Array::getGet('informationsystem_id', 0)))
	);

$oAdmin_Form_Entity_Form->add(
		Admin_Form_Entity::factory('Button')
		->name('show_form')
		->type('submit')
		->class('applyButton btn btn-blue')
	)
	/*->add(
		Core_Html_Entity::factory('Script')
			->type("text/javascript")
			->value("ShowExport('{$windowId}', 0)")
	)*/;

$oAdmin_Form_Entity_Form->execute();
$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	//->content(iconv("UTF-8", "UTF-8//IGNORE//TRANSLIT", ob_get_clean()))
	->content(ob_get_clean())
	->title(Core::_('Informationsystem_Item.export'))
	->execute();