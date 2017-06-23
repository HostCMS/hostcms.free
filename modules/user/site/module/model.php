<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Site_Module_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Site_Module_Model extends Module_Model
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
	protected $_modelName = 'user_site_module';
	
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
		$user_group_id = Core_Array::getGet('user_group_id');
		$oUser_Group = Core_Entity::factory('User_Group')->find($user_group_id);

		if (!is_null($oUser_Group->id))
		{
			// Идентификатор сайта
			$site_id = Core_Array::getGet('site_id');
			$oSite = Core_Entity::factory('Site')->find($site_id);

			if (!is_null($oSite->id))
			{
				//$oModule = Core_Entity::factory('Module')->find($this->id);
				$oModule = $this;

				$oUser_Module = $oUser_Group->getModuleAccess($oModule, $oSite);

				// Установили доступ
				if ($this->access)
				{
					if (is_null($oUser_Module))
					{
						$oUser_Module = Core_Entity::factory('User_Module');
						$oUser_Module->site_id = $oSite->id;
						$oUser_Module->module_id = $oModule->id;
						$oUser_Group->add($oUser_Module);
					}
				}
				else // Сняли доступ
				{
					if (!is_null($oUser_Module))
					{
						$oUser_Module->delete();
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