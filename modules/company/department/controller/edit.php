<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Company_Department_Controller_Edit extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $sOperation Operation name
	 * @return self
	 */
	public function execute($sOperation = NULL)
	{
		// Показываем форму добавления отдела
		// Edit
		if (is_null($sOperation))
		{
			$iCompanyId = intval(Core_Array::getGet('company_id'));
			$iDepartmentId = $this->_object->id;

			$oCompany = Core_Entity::factory('Company', $iCompanyId);

			$oDepartment = Core_Entity::factory('Company_Department', $iDepartmentId ? $iDepartmentId : NULL);

			ob_start();
			// Форма добавления/редактирования отдела

			// Email'ы отдела
			$aDirectory_Email_Types = Core_Entity::factory('Directory_Email_Type')->findAll();

			$aMasDirectoryEmailTypes = array();

			foreach ($aDirectory_Email_Types as $oDirectory_Email_Type)
			{
				$aMasDirectoryEmailTypes[$oDirectory_Email_Type->id] = $oDirectory_Email_Type->name;
			}

			$aCompany_Department_Directory_Emails = $oDepartment->Company_Department_Directory_Emails->findAll();

			// Email'ы сотрудника
			$oDepartmentEmailsRow = Directory_Controller_Tab::instance('email')
				->title(Core::_('Directory_Email.emails'))
				->relation($oDepartment->Company_Department_Directory_Emails)
				->execute();

			// Телефоны
			$oDepartmentPhonesRow = Directory_Controller_Tab::instance('phone')
				->title(Core::_('Directory_Phone.phones'))
				->relation($oDepartment->Company_Department_Directory_Phones)
				->execute();

			// Массив идентификаторов отделов, исключаемых из списка
			$aExcludeDepartments = array();

			// Из списка отделов исключаем редактируемый отдел и его дочерние отделы
			if (!is_null($oDepartment))
			{
				$aExcludeDepartments[] = $oDepartment->id;

				$aDependentDepartments = $oDepartment->getChildren();

				foreach($aDependentDepartments as $oDependentDepartment)
				{
					$aExcludeDepartments[] = $oDependentDepartment->id;
				}
			}

			Admin_Form_Entity::factory('Div')
				->controller($this->_Admin_Form_Controller)
				->id('editDepartmentModal')
				->class('tabbable')
				->add(
					Admin_Form_Entity::factory('Form')
						->action($this->_Admin_Form_Controller->getPath())
						->add(
							Admin_Form_Entity::factory('Code')
								->html('<ul id="editDepartmentTabModal" class="nav nav-tabs">
											<li class="active">
												<a href="#editDepartmentMainTab" data-toggle="tab" aria-expanded="true">' . Core::_('Admin_Form.form_forms_tab_1') . '</a>
											</li>

											<li class="tab-red">
												<a href="#editDepartmentAdditionalTab" data-toggle="tab" aria-expanded="false">' . Core::_('Admin_Form.form_forms_tab_2') . '</a>
											</li>
										</ul>')
						)
						->add(
							Admin_Form_Entity::factory('Div')
								->class('tab-content')
								->add(
									Admin_Form_Entity::factory('Div')
										->id('editDepartmentMainTab')
										->class('tab-pane active')
										->add(
											Admin_Form_Entity::factory('Div')
												->class('row')
												->add(
													Admin_Form_Entity::factory('Input')
														->type('text')
														->class('form-control')
														->name('name')
														->value($oDepartment->name)
														->caption(Core::_('Company_Department.name'))
														->divAttr(array('class' => 'form-group col-lg-12 col-md-6 col-sm-6'))
												)
										)
										->add(
											Admin_Form_Entity::factory('Div')
												->class('row')
												->add(
													Admin_Form_Entity::factory('Select')
														->name('department_parent_id')
														->caption(Core::_('Company_Department.parent_id'))
														->options(
															array(' … ') + $oCompany->fillDepartments(0, $aExcludeDepartments)
														)
														->value($oDepartment->parent_id ? $oDepartment->parent_id : 0)
														->filter(TRUE)
														->caseSensitive(FALSE)
												)
										)
										->add(
											Admin_Form_Entity::factory('Div')
												->class('row')
												->add(
													Admin_Form_Entity::factory('Input')
														->type('text')
														->class('form-control')
														->name('address')
														->value($oDepartment->address)
														->caption(Core::_('Company_Department.address'))
														->divAttr(array('class' => 'form-group col-lg-12 col-md-6 col-sm-6'))
												)
										)
										->add(
											Admin_Form_Entity::factory('Div')
												->class('row')
												->add(
													Admin_Form_Entity::factory('Textarea')
														->class('form-control')
														->name('description')
														->value($oDepartment->description)
														->caption(Core::_('Company_Department.description'))
														->divAttr(array('class' => 'form-group col-lg-12 col-md-6 col-sm-6'))
												)
										)
										->add(
											Admin_Form_Entity::factory('Input')
												->divAttr(array('class' => ''))
												->type('hidden')
												->name('department_id')
												->value($iDepartmentId)
										)
										->add(
											Admin_Form_Entity::factory('Input')
												->divAttr(array('class' => ''))
												->type('hidden')
												->name('company_id')
												->value($iCompanyId)
										)
								)
								->add(
									Admin_Form_Entity::factory('Div')
										->id('editDepartmentAdditionalTab')
										->class('tab-pane')
										->add($oDepartmentEmailsRow)
										->add($oDepartmentPhonesRow)
								)
						)
						->add(
							Admin_Form_Entity::factory('Code')
								->html('<script>$.companyChangeFilterFieldWindowId("editDepartmentModal")</script>')
						)
				)
				->execute();

			$seditDepartmentFormContent = Core_Str::escapeJavascriptVariable(ob_get_clean());

			ob_start();

			$windowId = $this->_Admin_Form_Controller->getWindowId();

			Admin_Form_Entity::factory('Code')
				->controller($this->_Admin_Form_Controller)
				->html('
					<script>
						var depatmentId = ' . ($oDepartment->id ? $oDepartment->id : 0) . ';

						var dialog = bootbox.dialog({
							//message: $("#editDepartmentModal").html(),
							message: \'' . $seditDepartmentFormContent . '\',
							title: depatmentId ? "' . Core::_('Company_Department.edit_form_title') . '" : "' . Core::_('Company_Department.add_form_title') . '",
							className: "modal-darkorange",
							backdrop: false,
							buttons: {
								success: {
									label: depatmentId ? "' . Core::_('Company_Department.edit') . '" : "' . Core::_('Company_Department.add') . '",
									className: "btn-blue",
									callback: function() {

										$.adminSendForm({buttonObject: $(this).find(\'form\'), operation: \'editDepartment\', action: \'editDepartment\', additionalParams: \'hostcms[checked][0][\' + depatmentId + \']=1\', windowId: \'' . $windowId . '\'});
									}
								},
								cancel: {
									label: "' . Core::_('Company_Department.cancel') . '",
									className: "btn-default",
									callback: function() {
										if (confirm("' . Core::_('Company_Department.close_modal_confirm') . '"))
										{
											dialog.modal(\'hide\');
										}

										return false;
									}
								}
							},
							onEscape: function() {
								if (confirm("' . Core::_('Company_Department.close_modal_confirm') . '"))
								{
									dialog.modal(\'hide\');
								}

								return false;
							}
						});
					</script>'
				)
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		elseif ($sOperation == 'editDepartment') // Edit
		{
			$sDepartmentName = trim(Core_Array::getPost('name'));
			$iDepartmentParentId = intval(Core_Array::getPost('department_parent_id'));
			$sDepartmentAddress = trim(Core_Array::getPost('address'));
			$sDepartmentDescription = trim(Core_Array::getPost('description'));
			$iCompanyId = intval(Core_Array::getPost('company_id'));
			$iDepartmentId = $this->_object->id;

			$oDepartment = Core_Entity::factory('Company_Department', $iDepartmentId ? $iDepartmentId : NULL);

			$aDependentDepartments = $oDepartment->getChildren();

			foreach($aDependentDepartments as $oDependentDepartment)
			{
				// Ошибка! Перемещение отдела в его дочерний отдел
				if ($iDepartmentParentId == $oDependentDepartment->id)
				{
					$sStatusMessage = Core_Message::get(Core::_('Company_Department.editDepartment_errorDependetDepartment'), 'error');

					$this->addMessage($sStatusMessage);

					return TRUE;
				}
			}

			$oDepartment
				->parent_id($iDepartmentParentId)
				->company_id($iCompanyId)
				->name($sDepartmentName)
				->address($sDepartmentAddress)
				->description($sDepartmentDescription)
				->save();

			// Электронные адреса, установленные значения
			$aCompany_Department_Directory_Emails = $oDepartment->Company_Department_Directory_Emails->findAll();

			foreach ($aCompany_Department_Directory_Emails as $oCompany_Department_Directory_Email)
			{
				$sEmail = trim(Core_Array::getPost("email#{$oCompany_Department_Directory_Email->id}"));

				if (!empty($sEmail))
				{
					$oDirectory_Email = $oCompany_Department_Directory_Email->Directory_Email;
					$oDirectory_Email
						->directory_email_type_id(intval(Core_Array::getPost("email_type#{$oCompany_Department_Directory_Email->id}", 0)))
						->value($sEmail)
						->save();
				}
				else
				{
					// Удаляем пустую строку с полями
					$oCompany_Department_Directory_Email->Directory_Email->delete();
				}
			}

			// Электронные адреса, новые значения
			$aEmails = Core_Array::getPost('email');
			$aEmail_Types = Core_Array::getPost('email_type');

			if (count($aEmails))
			{
				foreach ($aEmails as $key => $sEmail)
				{
					$sEmail = trim($sEmail);

					if (!empty($sEmail))
					{
						$oDirectory_Email = Core_Entity::factory('Directory_Email')
							->directory_email_type_id(intval(Core_Array::get($aEmail_Types, $key)))
							->value($sEmail)
							->save();

						$oDepartment->add($oDirectory_Email);
					}
				}
			}

			// Телефоны, установленные значения
			$aCompany_Department_Directory_Phones = $oDepartment->Company_Department_Directory_Phones->findAll();
			foreach ($aCompany_Department_Directory_Phones as $oCompany_Department_Directory_Phone)
			{
				$sPhone = trim(Core_Array::getPost("phone#{$oCompany_Department_Directory_Phone->id}"));

				if (!empty($sPhone))
				{
					$oDirectory_Phone = $oCompany_Department_Directory_Phone->Directory_Phone;
					$oDirectory_Phone
						->directory_phone_type_id(intval(Core_Array::getPost("phone_type#{$oCompany_Department_Directory_Phone->id}", 0)))
						->value($sPhone)
						->save();
				}
				else
				{
					$oCompany_Department_Directory_Phone->Directory_Phone->delete();
				}
			}

			// Телефоны, новые значения
			$aPhones = Core_Array::getPost('phone');
			$aPhone_Types = Core_Array::getPost('phone_type');

			if (count($aPhones))
			{
				foreach ($aPhones as $key => $sPhone)
				{
					$sPhone = trim($sPhone);

					if (!empty($sPhone))
					{
						$oDirectory_Phone = Core_Entity::factory('Directory_Phone')
							->directory_phone_type_id(intval(Core_Array::get($aPhone_Types, $key)))
							->value($sPhone)
							->save();

						$oDepartment->add($oDirectory_Phone);
					}
				}
			}

			return $this;
		}
		elseif ($sOperation == 'changeParentDepartment') // Edit
		{
			$iCompanyId = intval(Core_Array::getGet('company_id'));
			$iDepartmentId = $this->_object->id;
			$iNewParentDepartmentId = intval(Core_Array::getGet('new_parent_id'));

			if ($iDepartmentId)
			{
				$oDepartment = Core_Entity::factory('Company_Department', $iDepartmentId)
					->company_id($iCompanyId)
					->parent_id($iNewParentDepartmentId)
					->save();

				$sStatusMessage = Core_Message::get(Core::_('Company_Department.changeParentDepartment_success', $oDepartment->name));
			}

			Core::showJson(
				array('error' => $sStatusMessage, 'form_html' => '')
			);
		}
	}
}