<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Site_Form_Action_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Site_Form_Action_Model extends Admin_Form_Action_Model
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
	protected $_modelName = 'user_site_form_action';

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
	 * @return User_Site_Form_Action_Model
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
				$oAdmin_Form_Action = Core_Entity::factory('Admin_Form_Action')->find($this->id);
				$oUser_Group_Action_Access = $oUser_Group->getAdminFormActionAccess($oAdmin_Form_Action, $oSite);

				// Установили доступ
				if ($this->access)
				{
					if (is_null($oUser_Group_Action_Access))
					{
						$oUser_Group_Action_Access = Core_Entity::factory('User_Group_Action_Access');
						$oUser_Group_Action_Access->site_id = $oSite->id;
						$oUser_Group_Action_Access->admin_form_action_id = $oAdmin_Form_Action->id;
						$oUser_Group->add($oUser_Group_Action_Access);
					}
				}
				else // Сняли доступ
				{
					if (!is_null($oUser_Group_Action_Access))
					{
						$oUser_Group_Action_Access->delete();
					}
				}
			}
		}
	}
}