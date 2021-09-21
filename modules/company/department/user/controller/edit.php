<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Company
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Department_User_Controller_Edit extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($sOperation = NULL)
	{
		// Действие "Добавить/редактировать должность сотрудника в отделе"
		if (is_null($sOperation))
		{
			$iCompanyId = intval(Core_Array::getGet('company_id'));
			$iDepartmentId = intval(Core_Array::getGet('department_id'));

			$oDepartment = Core_Entity::factory('Company_Department')->find($iDepartmentId);

			//$iUserId = intval(Core_Array::getGet('user_id'));
			$iUserId = intval($this->_object->id);

			$iCompanyPostId = intval(Core_Array::getGet('company_post_id'));

			if (!is_null($oDepartment->id) && $oDepartment->company_id == $iCompanyId)
			{
				$oCompany = $oDepartment->Company;

				$aMasDepartmentUsers = array();

				$oUser = Core_Entity::factory('User');
				$oUser
					->queryBuilder()
					->orderBy(Core_QueryBuilder::expression('CONCAT(`surname`, `name`, `patronymic`)'));

				$aUsers = $oUser->findAll();

				foreach ($aUsers as $oUser)
				{
					$aMasDepartmentUsers[$oUser->id] = $oUser->getFullName();
				}

				$aMasCompanyPosts = array();
				$aCompanyPosts = Core_Entity::factory('Company_Post')->findAll();

				foreach ($aCompanyPosts as $oCompanyPost)
				{
					$aMasCompanyPosts[$oCompanyPost->id] = $oCompanyPost->name;
				}

				$iDepartmentPostHead = 0;

				if ($iDepartmentId && $iCompanyPostId && $iUserId)
				{
					$oCompany_Department_Post_User = Core_Entity::factory('Company_Department_Post_User');
					$oCompany_Department_Post_User
						->queryBuilder()
						->where('company_department_post_users.company_department_id', '=', $iDepartmentId)
						->where('company_department_post_users.company_post_id', '=', $iCompanyPostId)
						->where('company_department_post_users.user_id', '=', $iUserId);

					$aCompany_Department_Post_Users = $oCompany_Department_Post_User->findAll();

					if (isset($aCompany_Department_Post_Users[0]))
					{
						$iDepartmentPostHead = $aCompany_Department_Post_Users[0]->head;
					}
				}

				// Форма добавления сотрудника в отдел
				ob_start();
				Admin_Form_Entity::factory('Div')
					->controller($this->_Admin_Form_Controller)
					->id('editUserDepartmentModal')
					->add(
					 	Admin_Form_Entity::factory('Form')
							->action($this->_Admin_Form_Controller->getPath())
							->add(
								Admin_Form_Entity::factory('Div')
									->class('row')
									->add(
										Admin_Form_Entity::factory('Select')
											->name('user_id')
											->caption(Core::_('User.caption'))
											->options($aMasDepartmentUsers)
											->value($iUserId)
											->filter(TRUE)
											->caseSensitive(FALSE)
									)
							)
							->add(
								Admin_Form_Entity::factory('Div')
									->class('row')
									->add(
										Admin_Form_Entity::factory('Select')
											->name('department_id')
											->caption(Core::_('Company_Department.caption'))
											->options(
												array(' … ') + $oCompany->fillDepartments()
											)
											->value($iDepartmentId)
											->filter(TRUE)
											->caseSensitive(FALSE)
									)
							)
							->add(
								Admin_Form_Entity::factory('Div')
									->class('row')
									->add(
										Admin_Form_Entity::factory('Select')
											->name('company_post_id')
											->caption(Core::_('Company_Post.caption'))
											->options($aMasCompanyPosts)
											->value($iCompanyPostId)
											->filter(TRUE)
											->caseSensitive(FALSE)
									)
							)
							->add(
								Admin_Form_Entity::factory('Div')
									->class('row')
									->add(
										Admin_Form_Entity::factory('Checkbox')
											->class('form-control')
											->name('head')
											->caption(Core::_('User.head'))
											->checked($iDepartmentPostHead ? $iDepartmentPostHead : NULL)
											->value($iDepartmentPostHead)
											->divAttr(array('class' => 'form-group col-lg-12 col-md-6 col-sm-6'))
									)
							)
							->add(
								Admin_Form_Entity::factory('Input')
									->divAttr(array('class' => ''))
									->type('hidden')
									->name('company_id')
									->value($iCompanyId)
							)
							->add(
								Admin_Form_Entity::factory('Input')
									->divAttr(array('class' => ''))
									->type('hidden')
									->name('original_department_id')
									->value($oDepartment->id)
							)
							->add(
								Admin_Form_Entity::factory('Input')
									->divAttr(array('class' => ''))
									->type('hidden')
									->name('original_company_post_id')
									->value($iCompanyPostId)
							)
							->add(
								Admin_Form_Entity::factory('Input')
									->divAttr(array('class' => ''))
									->type('hidden')
									->name('original_user_id')
									->value($iUserId)
							)
							->add(
								Admin_Form_Entity::factory('Code')
									->html('<script>$.companyChangeFilterFieldWindowId("editUserDepartmentModal")</script>')
							)
					)
					->execute();

				$sAddEditUserFormContent = Core_Str::escapeJavascriptVariable(ob_get_clean());

				ob_start();

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				Admin_Form_Entity::factory('Code')
					->html('
						<script>
							bootbox.dialog({
								message: \'' . $sAddEditUserFormContent . '\',
								title: "' . ($iUserId ? Core::_('Company_Department.edit_user_title') : Core::_('Company_Department.add_user_title')) . '",
								className: "modal-darkorange",
								backdrop: false,
								buttons: {
									success: {
										label: "' . ($iUserId ? Core::_('Company_Department.edit') : Core::_('Company_Department.add')) . '",
										className: "btn-blue",
										callback: function() {
													$.adminSendForm({buttonObject: $(this).find(\'form\'), operation: \'editUserDepartment\', action: \'editUserDepartment\', additionalParams: \'hostcms[checked][1][' . $iUserId . ']=1\', windowId: \'' . $windowId . '\'});
												}
									},
									cancel: {
										label: "' . Core::_('Company_Department.cancel') . '",
										className: "btn-default",
										//callback: function() {}
									}
								},
								onEscape: true
							});

						</script>'
					)
					->execute();

				$this->addMessage(ob_get_clean());

				// Break execution for other
				return TRUE;
			}
		}
		elseif ($sOperation == 'editUserDepartment')
		{
			$iDepartmentId = intval(Core_Array::getPost('department_id'));
			$oDepartment = Core_Entity::factory('Company_Department')->find($iDepartmentId);

			$iCompanyId = intval(Core_Array::getPost('company_id'));

			if (!is_null($oDepartment->id) && $oDepartment->company_id == $iCompanyId)
			{
				$iUserId = intval(Core_Array::getPost('user_id'));

				$oUser = Core_Entity::factory('User', $iUserId);

				$iCompanyPostId = intval(Core_Array::getPost('company_post_id'));

				$oCompany_Post = Core_Entity::factory('Company_Post', $iCompanyPostId);

				$iOriginalDepartmentId = intval(Core_Array::getPost('original_department_id'));
				$iOriginalCompanyPostId = intval(Core_Array::getPost('original_company_post_id'));
				$iOriginalUserId = intval(Core_Array::getPost('original_user_id'));

				$iHead = !is_null(Core_Array::getPost('head')) ? 1 : 0;

				$oCompany_Department_Post_Users = $oDepartment->Company_Department_Post_Users;

				// Получаем запись о должностей сотрудника в отделе
				$oCompany_Department_Post_Users
					->queryBuilder()
					->where('user_id', '=', $iUserId)
					->where('company_post_id', '=', $iCompanyPostId);

				$aCompany_Department_Post_Users = $oCompany_Department_Post_Users->findAll();

				// Уже есть запись о должности сотрудника в отделе, согласно переданным данным
				if (isset($aCompany_Department_Post_Users[0]))
				{
					// Сотрудник является начальником в отделе на этой должности
					if ($aCompany_Department_Post_Users[0]->head != $iHead && $iOriginalUserId)
					{
						$sStatusMessage = $iHead
							? Core_Message::get(Core::_('Company_Department.addUserToHeads_success', $oUser->getFullName(), $oDepartment->name))
							: Core_Message::get(Core::_('Company_Department.deleteUserFromHeads_success', $oUser->getFullName(), $oDepartment->name));

						$oCompany_Department_Post_User = $aCompany_Department_Post_Users[0];
					}
					elseif (!$iOriginalUserId)
					{
						$sStatusMessage = Core_Message::get(Core::_('Company_Department.addUserToHeads_error', $oUser->getFullName(), $oCompany_Post->name, $oDepartment->name), 'error');
					}
				}
				else // Запись о должности сотрудника в отделе отсутствует
				{
					// Редактируем информацию о должности сотрудника
					if ($iUserId == $iOriginalUserId)
					{
						$oOriginal_Department = Core_Entity::factory('Company_Department')->find($iOriginalDepartmentId);
						$oOriginal_Company_Department_Post_Users = $oOriginal_Department->Company_Department_Post_Users;

						// Новый вариант - начало

						// Получаем запись о прежней должности сотрудника в отделе
						$oOriginal_Company_Department_Post_Users
							->queryBuilder()
							->where('user_id', '=', $iOriginalUserId)
							->where('company_post_id', '=', $iOriginalCompanyPostId);

						$aOriginal_Company_Department_Post_Users = $oOriginal_Company_Department_Post_Users->findAll();

						if (isset($aOriginal_Company_Department_Post_Users[0]))
						{
							$oCompany_Department_Post_User = $aOriginal_Company_Department_Post_Users[0];
						}
					}
					else // Добавление должности сотрудника
					{
						$oCompany_Department_Post_User = Core_Entity::factory('Company_Department_Post_User');
						$sStatusMessage = Core_Message::get(Core::_('Company_Department.addUserToDepartmentPost_success', $oUser->getFullName(), $oCompany_Post->name, $oDepartment->name));
					}
				}

				if (isset($oCompany_Department_Post_User))
				{
					$oCompany_Department_Post_User
						->company_id($iCompanyId)
						->company_post_id($iCompanyPostId)
						->user_id($iUserId)
						->head($iHead);

					$oDepartment->add($oCompany_Department_Post_User);
				}
			}
			return $this;
		}
	}
}