<?php
/**
 * Admin forms.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

// Код формы
$iAdmin_Form_Id = 1;
$sAdminFormAction = '/admin/admin_form/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

if (Core_Auth::logged())
{
	Core_Auth::checkBackendBlockedIp();

	// Контроллер формы
	$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

	$oAdmin_Form_Controller->formSettings();

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

	if (!is_null(Core_Array::getPost('showAdminFormSettingsModal')))
	{
		$aJSON = array(
			'html' => ''
		);

		$admin_form_id = Core_Array::getPost('admin_form_id', 0, 'int');

		$oAdmin_Form = Core_Entity::factory('Admin_Form')->getById($admin_form_id);
		$oUser = Core_Auth::getCurrentUser();

		if (!is_null($oAdmin_Form) && !is_null($oUser))
		{
			$oAdmin_Language = $oAdmin_Form_Controller->getAdminLanguage();

			$oAdmin_Word_Value = $oAdmin_Form->Admin_Word->getWordByLanguage($oAdmin_Language->id);

			$formName = $oAdmin_Word_Value ? $oAdmin_Word_Value->name : '';
			ob_start();
			?>
			<div class="modal fade" id="adminFormSettingsModal<?php echo $oAdmin_Form->id?>" tabindex="-1" role="dialog" aria-labelledby="adminFormSettingsModalLabel">
				<div class="modal-dialog modal-lg" role="document">
					<div class="modal-content no-padding-bottom">
						<form action="/admin/admin_form/index.php" method="POST">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								<h4 class="modal-title"><?php echo Core::_('Admin_Form.setting_modal_title', $formName)?></h4>
							</div>
							<div class="modal-body">
								<div class="row margin-bottom-10 admin-form-settings-modal">
									<?php
										$aAvailableFields = $oAdmin_Form->getAvailableFieldsForUser($oUser->id);

										$aAdmin_Form_Fields = $oAdmin_Form->Admin_Form_Fields->getAllByView(1, FALSE, '!=');
										foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
										{
											$fieldName = htmlspecialchars($oAdmin_Form_Field->getCaption($oAdmin_Language->id));

											if ($fieldName == '' && $oAdmin_Form_Field->ico != '')
											{
												$fieldName = '<i class="' . htmlspecialchars($oAdmin_Form_Field->ico) . '"></i>';
											}

											// Нет названия, нет иконки - выводим тире
											$fieldName == '' && $fieldName = '—';

											$bChecked = isset($aAvailableFields[$oAdmin_Form_Field->id])
												? TRUE
												: !count($aAvailableFields) && $oAdmin_Form_Field->show_by_default;

											$class = $bChecked
												? ' admin-field-checked'
												: '';

											Admin_Form_Entity::factory('Checkbox')
												->caption($fieldName)
												->divAttr(array('class' => 'form-group col-xs-12 col-sm-4' . $class))
												->name('admin_form_field' . $oAdmin_Form_Field->id)
												->value(1)
												->controller($oAdmin_Form_Controller)
												->checked($bChecked)
												->data('id', $oAdmin_Form_Field->id)
												->onclick('$.selectAdminFormSetting(this)')
												->execute();
										}
									?>
								</div>
							</div>
							<div class="modal-footer">
								<div class="pull-left">
									<span class="admin-form-field-check admin-form-field-check-all sky margin-right-5" onclick="$.selectAdminFormSettings(<?php echo $oAdmin_Form->id?>, 1)"><?php echo Core::_('Admin_form.select_all')?></span>
									<span class="admin-form-field-check" onclick="$.selectAdminFormSettings(<?php echo $oAdmin_Form->id?>, 0)"><?php echo Core::_('Admin_form.disable_all')?></span>
								</div>

								<button type="button" class="btn btn-success" onclick="mainFormLocker.unlock(); <?php echo $oAdmin_Form_Controller->clearChecked()->getAdminSendForm(array('action' => 'applyFormFieldSettings', 'additionalParams' => 'hostcms[checked][0][' . $oAdmin_Form->id . ']=1'))?>"><?php echo Core::_('Admin_Form.apply')?></button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<?php
			$aJSON['html'] = ob_get_clean();
		}

		Core::showJson($aJSON);
	}

	if (!is_null(Core_Array::getPost('saveAdminFieldWidth')))
	{
		$aJSON = array(
			'status' => 'error'
		);

		$admin_form_field_id = Core_Array::getPost('admin_form_field_id', 0, 'int');
		$width = Core_Array::getPost('width', 0, 'int');

		$oAdmin_Form_Field = Core_Entity::factory('Admin_Form_Field')->getById($admin_form_field_id);

		$oUser = Core_Auth::getCurrentUser();

		if ($width && !is_null($oAdmin_Form_Field) && !is_null($oUser))
		{
			$oAdmin_Form = $oAdmin_Form_Field->Admin_Form;

			// Если не было полей для формы сохранено
			$aAdmin_Form_Field_Settings = $oAdmin_Form->Admin_Form_Field_Settings->getAllByUser_id($oUser->id, FALSE);
			if (!count($aAdmin_Form_Field_Settings))
			{
				$oAdmin_Form_Fields = $oAdmin_Form->Admin_Form_Fields->getAllByshow_by_default(1, FALSE);
				foreach ($oAdmin_Form_Fields as $oTmpAdmin_Form_Field)
				{
					$oAdmin_Form_Field_Setting = Core_Entity::factory('Admin_Form_Field_Setting');
					$oAdmin_Form_Field_Setting->admin_form_id = $oAdmin_Form->id;
					$oAdmin_Form_Field_Setting->admin_form_field_id = $oTmpAdmin_Form_Field->id;
					$oAdmin_Form_Field_Setting->user_id = $oUser->id;
					$oAdmin_Form_Field_Setting->save();
				}
			}

			$oAdmin_Form_Field_Setting = $oAdmin_Form_Field->Admin_Form_Field_Settings->getByUser_id($oUser->id, FALSE);

			if (!$oAdmin_Form_Field_Setting)
			{
				$oAdmin_Form_Field_Setting = Core_Entity::factory('Admin_Form_Field_Setting');
				$oAdmin_Form_Field_Setting->admin_form_id = $oAdmin_Form->id;
				$oAdmin_Form_Field_Setting->admin_form_field_id = $oAdmin_Form_Field->id;
				$oAdmin_Form_Field_Setting->user_id = $oUser->id;
			}

			$oAdmin_Form_Field_Setting->width = $width;
			$oAdmin_Form_Field_Setting->save();

			$aJSON['status'] = 'success';
		}

		Core::showJson($aJSON);
	}

	if ($oAdmin_Form_Controller->getAction() == 'applyFormFieldSettings')
	{
		$oUser = Core_Auth::getCurrentUser();

		$oAdmin_Form = NULL;

		$aChecked = $oAdmin_Form_Controller->getChecked();

		if (!is_null($oUser) && isset($aChecked[0]))
		{
			$key = key($aChecked[0]);
			$oAdmin_Form = Core_Entity::factory('Admin_Form')->getById($key);

			if (!is_null($oAdmin_Form))
			{
				$aExistFormFieldSettings = array();

				$oAdmin_Form_Field_Settings = $oAdmin_Form->Admin_Form_Field_Settings;
				$oAdmin_Form_Field_Settings->queryBuilder()
					->where('user_id', '=', $oUser->id);

				$aAdmin_Form_Field_Settings = $oAdmin_Form_Field_Settings->findAll(FALSE);
				foreach ($aAdmin_Form_Field_Settings as $oAdmin_Form_Field_Setting)
				{
					$aExistFormFieldSettings[$oAdmin_Form_Field_Setting->admin_form_field_id] = $oAdmin_Form_Field_Setting;
				}

				$oAdmin_Form_Fields = $oAdmin_Form->Admin_Form_Fields->findAll(FALSE);
				foreach ($oAdmin_Form_Fields as $oAdmin_Form_Field)
				{
					if (isset($_POST['admin_form_field' . $oAdmin_Form_Field->id]))
					{
						if (isset($aExistFormFieldSettings[$oAdmin_Form_Field->id]))
						{
							unset($aExistFormFieldSettings[$oAdmin_Form_Field->id]);
						}
						else
						{
							// Создаем новый
							$oAdmin_Form_Field_Setting = Core_Entity::factory('Admin_Form_Field_Setting');
							$oAdmin_Form_Field_Setting->admin_form_id = $oAdmin_Form->id;
							$oAdmin_Form_Field_Setting->admin_form_field_id = $oAdmin_Form_Field->id;
							$oAdmin_Form_Field_Setting->user_id = $oUser->id;
							$oAdmin_Form_Field_Setting->save();
						}
					}
				}

				// Удаляем оставшиеся, которые не были отмечены
				foreach ($aExistFormFieldSettings as $oAdmin_Form_Field_Setting)
				{
					$oAdmin_Form_Field_Setting->delete();
				}

				$content = "<script>$(function() {
					$('#adminFormSettingsModal{$oAdmin_Form->id}').modal('hide');
					mainFormLocker.unlock();
					$.loadingScreen('show');
					$('#id_content #refresh-toggler').click();
				});</script>";
			}
			else
			{
				$content = '';
			}

			Core::showJson(array(
				'error' => $content,
				'form_html' => '',
				'title' => ''
			));
		}
	}
}

Core_Auth::authorization($sModule = 'admin_form');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
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
			$oAdmin_Form_Controller->getAdminActionLoadHref(array('path' => $oAdmin_Form_Controller->getPath(), 'action' => 'edit', 'datasetKey' => 0, 'datasetValue' => 0))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax(array('path' => $oAdmin_Form_Controller->getPath(), 'action' => 'edit', 'datasetKey' => 0, 'datasetValue' => 0))
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.show_form_menu_admin_forms_top2'))
		->icon('fa fa-flag')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $sLanguagePath))

		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $sLanguagePath))
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
			$oAdmin_Form_Controller->getAdminLoadHref(array('path' => $oAdmin_Form_Controller->getPath()))
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax(array('path' => $oAdmin_Form_Controller->getPath()))
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
	&& $oAdmin_Form_Dataset->addUserConditions();

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