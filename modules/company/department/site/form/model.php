<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_Site_Form_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Company_Department_Site_Form_Model extends Admin_Form_Model
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'admin_forms';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'company_department_site_form';

	/**
	 * Backend property
	 * @var string
	 */
	public $name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $img = 0;

	/**
	 * Backend property
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$company_department_id = Core_Array::getGet('company_department_id', 0, 'int');
		$oCompany_Department = Core_Entity::factory('Company_Department', $company_department_id);

		if (!is_null($oCompany_Department->id))
		{
			// Идентификатор сайта
			$site_id = Core_Array::getGet('site_id');
			$oSite = Core_Entity::factory('Site')->find($site_id);

			if (!is_null($oSite->id))
			{
				$oAdmin_Form = Core_Entity::factory('Admin_Form', $this->id);
				$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->findAll();

				foreach ($aAdmin_Form_Actions as $oAdmin_Form_Action)
				{
					$oCompany_Department_Action_Access = $oCompany_Department->getAdminFormActionAccess($oAdmin_Form_Action, $oSite, FALSE);

					if (!is_null($oCompany_Department_Action_Access))
					{
						$color = $oAdmin_Form_Action->color != ''
							? 'badge-' . htmlspecialchars($oAdmin_Form_Action->color)
							: '';

						$name = $oAdmin_Form_Action->icon == ''
							? htmlspecialchars($oAdmin_Form_Action->name)
							: '<i class="' . $oAdmin_Form_Action->icon . '"></i>';

						Core_Html_Entity::factory('Span')
							->class('badge badge-square ' . $color)
							->value($name)
							->title(htmlspecialchars($oAdmin_Form_Action->name))
							->execute();
					}
				}
			}
		}
	}

	/**
	 * Set acces user to form
	 * @param int $access_value value
	 */
	protected function _setAccess($access_value, $action_name = NULL)
	{
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $this->id);
		$aAdmin_Form_Actions = !is_null($action_name)
			? $oAdmin_Form->Admin_Form_Actions->getAllByName($action_name)
			: $oAdmin_Form->Admin_Form_Actions->findAll();

		foreach ($aAdmin_Form_Actions as $oAdmin_Form_Action)
		{
			$oUser_Site_Form_Action = Core_Entity::factory('Company_Department_Site_Form_Action', $oAdmin_Form_Action->id);
			$oUser_Site_Form_Action->access = $access_value;
			$oUser_Site_Form_Action->save();
		}

		return $this;
	}

	/**
	 * Allow access user to form
	 * @return self
	 */
	public function allowActions()
	{
		$this->_setAccess(1);
		return $this;
	}

	/**
	 * Deny access user to form
	 * @return self
	 */
	public function denyActions()
	{
		$this->_setAccess(0);
		return $this;
	}

	/**
	 * Allow all `viewForm` for user to form
	 * @return self
	 */
	public function allowViewForm()
	{
		$this->_setAccess(1, 'viewForm');
		return $this;
	}

	/**
	 * Disallow all `viewForm` for user to form
	 * @return self
	 */
	public function disallowViewForm()
	{
		$this->_setAccess(0, 'viewForm');
		return $this;
	}

	/**
	 * Allow all `edit` for user to form
	 * @return self
	 */
	public function allowEdit()
	{
		$this->_setAccess(1, 'edit');
		return $this;
	}

	/**
	 * Disallow all `edit` for user to form
	 * @return self
	 */
	public function disallowEdit()
	{
		$this->_setAccess(0, 'edit');
		return $this;
	}

	/**
	 * Allow all `copy` for user to form
	 * @return self
	 */
	public function allowCopy()
	{
		$this->_setAccess(1, 'copy');
		return $this;
	}

	/**
	 * Disallow all `copy` for user to form
	 * @return self
	 */
	public function disallowCopy()
	{
		$this->_setAccess(0, 'copy');
		return $this;
	}

	/**
	 * Allow all `markDeleted` or `delete` for user to form
	 * @return self
	 */
	public function allowDelete()
	{
		!$this->_setAccess(1, 'markDeleted')
			&& $this->_setAccess(1, 'delete');

		return $this;
	}

	/**
	 * Disallow all `markDeleted` or `delete` for user to form
	 * @return self
	 */
	public function disallowDelete()
	{
		!$this->_setAccess(0, 'markDeleted')
			&& $this->_setAccess(0, 'delete');

		return $this;
	}

	/**
	 * Allow all `apply` for user to form
	 * @return self
	 */
	public function allowApply()
	{
		$this->_setAccess(1, 'apply');
		return $this;
	}

	/**
	 * Disallow all `apply` for user to form
	 * @return self
	 */
	public function disallowApply()
	{
		$this->_setAccess(0, 'apply');
		return $this;
	}
}