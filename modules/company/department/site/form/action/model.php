<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_Site_Form_Action_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Company_Department_Site_Form_Action_Model extends Admin_Form_Action_Model
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'admin_form_actions';
	
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'company_department_site_form_action';

	/**
	 * Backend property
	 * @var string
	 */
	public $text_name = NULL;
	
	/**
	 * Backend property
	 * @var string
	 */
	public $action_name = NULL;
	
	/**
	 * Backend property
	 * @var int
	 */
	public $access = 0;

	/**
	 * Save object. Use self::update() or self::create()
	 */
	public function save()
	{
		// Идентификатор группы пользователей
		$company_department_id = Core_Array::getGet('company_department_id');
		$oCompany_Department = Core_Entity::factory('Company_Department', $company_department_id);

		if (!is_null($oCompany_Department->id))
		{
			// Идентификатор сайта
			$site_id = Core_Array::getGet('site_id');
			$oSite = Core_Entity::factory('Site')->find($site_id);

			if (!is_null($oSite->id))
			{
				$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action')->find($this->id);
				$oCompany_Department_Action_Access = $oCompany_Department->getAdminFormActionAccess($oAdmin_Form_Action, $oSite);

				// Установили доступ
				if ($this->access)
				{
					if (is_null($oCompany_Department_Action_Access))
					{
						$oCompany_Department_Action_Access = Core_Entity::factory('Company_Department_Action_Access');
						$oCompany_Department_Action_Access->site_id = $oSite->id;
						$oCompany_Department_Action_Access->admin_form_action_id = $oAdmin_Form_Action->id;
						$oCompany_Department->add($oCompany_Department_Action_Access);
					}
				}
				else // Сняли доступ
				{
					if (!is_null($oCompany_Department_Action_Access))
					{
						$oCompany_Department_Action_Access->delete();
					}
				}
			}
		}
	}
}