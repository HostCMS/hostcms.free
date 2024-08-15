<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Session_Destroy_Controller
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class User_Session_Destroy_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Execute
	 */
	public function execute($operation = NULL)
	{
		$oUser_Sessions = Core_Entity::factory('User_Session');
		$oUser_Sessions->queryBuilder()
			->where('user_sessions.id', '!=', session_id());

		$aUser_Sessions = $oUser_Sessions->findAll(FALSE);

		foreach ($aUser_Sessions as $oUser_Session)
		{
			$oUser_Session->destroy();
		}

		return $this;
	}
}