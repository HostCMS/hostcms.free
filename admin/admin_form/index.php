<?php
/**
 * Admin forms.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

// Код формы
$iAdmin_Form_Id = 1;
$sAdminFormAction = '/admin/admin_form/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

if (Core_Auth::logged())
{
	if (!is_null(Core_Array::getPost('autosave')))
	{
		$aReturn = array(
			'id' => 0,
			'status' => 'error'
		);

		$admin_form_id = Core_Array::getPost('admin_form_id', 0, 'intval');
		$dataset = Core_Array::getPost('dataset', 0, 'intval');
		$entity_id = Core_Array::getPost('entity_id', 0, 'intval');
		//$prev_entity_id = Core_Array::getPost('prev_entity_id', 0, 'intval');
		$json = Core_Array::getPost('json', '', 'strval');

		if (strval($json))
		{
			try {
				// Произошло сохранение с присвоением ID
				$oAdmin_Form_Autosave = /*$prev_entity_id == 0 && $entity_id
					? Core_Entity::factory('Admin_Form_Autosave')->getObject($admin_form_id, $dataset, $prev_entity_id)
					: */Core_Entity::factory('Admin_Form_Autosave')->getObject($admin_form_id, $dataset, $entity_id);

				if (is_null($oAdmin_Form_Autosave))
				{
					$oAdmin_Form_Autosave = Core_Entity::factory('Admin_Form_Autosave');
					$oAdmin_Form_Autosave->admin_form_id = $admin_form_id;
					$oAdmin_Form_Autosave->dataset = $dataset;
					$oAdmin_Form_Autosave->entity_id = $entity_id;
				}

				$oAdmin_Form_Autosave->json = $json;
				$oAdmin_Form_Autosave->datetime = Core_Date::timestamp2sql(time());
				$oAdmin_Form_Autosave->save();

				$aReturn = array(
					'id' => $oAdmin_Form_Autosave->id,
					'status' => 'success'
				);
			}
			catch (Exception $e)
			{
				$aReturn = array(
					'status' => 'error'
				);
			}
		}

		Core::showJson($aReturn);
	}

	if (!is_null(Core_Array::getPost('show_autosave')))
	{
		$aReturn = array(
			'id' => 0,
			'json' => '',
			'text' => '',
			'status' => 'error'
		);

		$admin_form_id = Core_Array::getPost('admin_form_id', 0, 'intval');
		$dataset = Core_Array::getPost('dataset', 0, 'intval');
		$entity_id = Core_Array::getPost('entity_id', 0, 'intval');

		$oAdmin_Form_Autosave = Core_Entity::factory('Admin_Form_Autosave')->getObject($admin_form_id, $dataset, $entity_id);

		if (!is_null($oAdmin_Form_Autosave))
		{
			$text = '<div class="alert alert-info admin-form-autosave"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><i class="fa-fw fa fa-warning"></i> ' . Core::_('Admin_Form_Autosave.autosave_success') . ' <a href="#">' . Core::_('Admin_Form_Autosave.autosave_link') . '</a></div>';

			$aReturn = array(
				'id' => $oAdmin_Form_Autosave->id,
				'json' => $oAdmin_Form_Autosave->json,
				'text' => $text,
				'status' => 'success'
			);
		}

		Core::showJson($aReturn);
	}

	if (!is_null(Core_Array::getPost('delete_autosave')))
	{
		$aReturn = array(
			'status' => 'error'
		);

		$admin_form_autosave_id = Core_Array::getPost('admin_form_autosave_id', 0, 'intval');

		$oAdmin_Form_Autosave = Core_Entity::factory('Admin_Form_Autosave')->getById($admin_form_autosave_id);

		if (!is_null($oAdmin_Form_Autosave))
		{
			$oAdmin_Form_Autosave->delete();

			$aReturn['status'] = 'success';
		}

		Core::showJson($aReturn);
	}
}

Core_Auth::authorization($sModule = 'admin_form');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Admin_Form.show_forms_title'))
	->pageTitle(Core::_('Admin_Form.show_forms_title'));

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$sLanguagePath = '/admin/admin_form/language/index.php';

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.show_form_menu_admin_forms_top1'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.show_form_menu_admin_forms_top2'))
		->icon('fa fa-flag')
		->img('/admin/images/languages.gif')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sLanguagePath, '', NULL, 0, 0)

		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sLanguagePath, '', NULL, 0, 0)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Admin_Form.show_form_fields_menu_admin_forms'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oAdmin_Form_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Admin_Form_Controller_Edit', $oAdmin_Form_Action
	);

	$oAdmin_Form_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_Form_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oAdmin_FormControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oAdmin_FormControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Admin_Form')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

$oAdmin_Form_Dataset->addCondition(
	array('select' => array('admin_forms.*', array('admin_word_values.name', 'name')))
)->addCondition(
	array('leftJoin' => array('admin_words', 'admin_forms.admin_word_id', '=', 'admin_words.id'))
)->addCondition(
	array('leftJoin' => array('admin_word_values', 'admin_words.id', '=', 'admin_word_values.admin_word_id'))
)->addCondition(
	array('open' => array())
)->addCondition(
	array('where' => array('admin_word_values.admin_language_id', '=', CURRENT_LANGUAGE_ID))
)->addCondition(
	array('setOr' => array())
)->addCondition(
	array('where' => array('admin_forms.admin_word_id', '=', 0))
)->addCondition(
	array('close' => array())
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();