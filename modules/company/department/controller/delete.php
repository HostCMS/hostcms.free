<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Company
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Department_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($sOperation = NULL)
	{
		if (!is_null($sOperation) && $sOperation == 'deleteDepartment' && $this->_object->id)
		{
			Core_Entity::factory('Company_Department', $this->_object->id)->markDeleted();
		}

		return $this;
	}
}