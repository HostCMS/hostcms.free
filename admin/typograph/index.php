<?php
/**
 * Typograph.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'typograph');

$sAdminFormAction = '/admin/typograph/index.php';

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('typograph.title'))
	//->pageTitle(Core::_('typograph.title'))
	;

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module::factory($sModule))
	->pageTitle(Core::_('typograph.title'))
	->addMessage(
		Core_Message::show(Core::_('typograph.warning'))
	);

$sText = Typograph_Controller::instance()
	->process(Core_Array::getPost('text'), Core_Array::getPost('trailing_punctuation', FALSE));

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
	->controller($oAdmin_Form_Controller)
	->action($sAdminFormAction)
	->add(
		Admin_Form_Entity::factory('Textarea')
			->name('text')
			->caption(Core::_('typograph.text'))
			->rows(15)
			->value($sText)
	)
	->add(
		Admin_Form_Entity::factory('Checkbox')
			->name('trailing_punctuation')
			->caption(Core::_('typograph.trailing_punctuation'))
			->value(Core_Array::getPost('trailing_punctuation'))
	);

// Оттипографированный текст
if ($oAdmin_Form_Controller->getAction() == 'process')
{
	ob_start();

	Core::factory('Core_Html_Entity_Div')
		//->class('row')
		->add(
			Core::factory('Core_Html_Entity_Div')
				->class('form-group col-lg-12')
				->add(
					Core::factory('Core_Html_Entity_Div')
						->class('typograph_result')
						->value($sText)
				)
		)
		->execute();

	$oAdmin_Form_Entity_Form->add(
		Admin_Form_Entity::factory('Code')
			->html(ob_get_clean())
	);
}

$oAdmin_Form_Entity_Form
	->add(
		Admin_Form_Entity::factory('Button')
			->name('process')
			->type('submit')
			->value(Core::_('typograph.process'))
			->class('applyButton btn btn-blue')
			->onclick(
				$oAdmin_Form_Controller->getAdminSendForm('process')
			)
	)
	->execute();

$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->message($oAdmin_View->message)
	->title(Core::_('Typograph.title'))
	->module($sModule)
	->execute();