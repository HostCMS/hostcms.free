<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_Site_Module_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Department_Site_Module_Model extends Module_Model
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'modules';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'company_department_site_module';

	/**
	 * Backend property
	 * @var int
	 */
	public $access = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $key_field = 0;

	/**
	 * Save object. Use self::update() or self::create()
	 * @return User_Site_Module_Model
	 */
	public function save()
	{
		// Идентификатор группы пользователей
		$company_department_id = Core_Array::getGet('company_department_id');
		$oCompany_Department = Core_Entity::factory('Company_Department')->find($company_department_id);

		if (!is_null($oCompany_Department->id))
		{
			// Идентификатор сайта
			$site_id = Core_Array::getGet('site_id');
			$oSite = Core_Entity::factory('Site')->find($site_id);

			if (!is_null($oSite->id))
			{
				//$oModule = Core_Entity::factory('Module')->find($this->id);
				$oModule = $this;

				$oCompany_Department_Module = $oCompany_Department->getModuleAccess($oModule, $oSite);

				// Установили доступ
				if ($this->access)
				{
					if (is_null($oCompany_Department_Module))
					{
						$oCompany_Department_Module = Core_Entity::factory('Company_Department_Module');
						$oCompany_Department_Module->site_id = $oSite->id;
						$oCompany_Department_Module->module_id = $oModule->id;
						$oCompany_Department->add($oCompany_Department_Module);
					}
				}
				else // Сняли доступ
				{
					if (!is_null($oCompany_Department_Module))
					{
						$oCompany_Department_Module->delete();
					}
				}
			}
		}
	}

	/**
	 * Allow access user to module
	 */
	public function accessAllow()
	{
		$this->access = 1;
		$this->save();
	}

	/**
	 * Deny access user to module
	 */
	public function accessDeny()
	{
		$this->access = 0;
		$this->save();
	}
}