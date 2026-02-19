<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_User_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Company_Department_User_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $sOperation Operation name
	 * @return self
	 */
	public function execute($sOperation = NULL)
	{
		if (!is_null($sOperation) && $sOperation == 'deleteUserFromDepartment' && $this->_object->id)
		{
			$iDepartmentId = intval(Core_Array::getGet('department_id'));
			$iCompanyPostId = intval(Core_Array::getGet('company_post_id'));
			$oUser = Core_Entity::factory('User', $this->_object->id);

			$oCompany_Department_Post_Users = $oUser->Company_Department_Post_Users;
			$oCompany_Department_Post_Users
				->queryBuilder()
				->where('company_department_post_users.company_department_id', '=', $iDepartmentId)
				->where('company_department_post_users.company_post_id', '=', $iCompanyPostId);

			$aCompany_Department_Post_Users = $oCompany_Department_Post_Users->findAll();

			if (isset($aCompany_Department_Post_Users[0]))
			{
				$aCompany_Department_Post_Users[0]->delete();
			}
		}

		return $this;
	}
}