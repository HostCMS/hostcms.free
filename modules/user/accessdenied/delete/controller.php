<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Accessdenied_Delete_Controller
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class User_Accessdenied_Delete_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Execute
	 */
	public function execute($operation = NULL)
	{
		$limit = 500;
		do {
			$oUser_Accessdenieds = Core_Entity::factory('User_Accessdenied');
			$oUser_Accessdenieds->queryBuilder()
				->clear()
				->limit($limit);

			$aUser_Accessdenieds = $oUser_Accessdenieds->findAll(FALSE);
			foreach ($aUser_Accessdenieds as $oUser_Accessdenied)
			{
				$oUser_Accessdenied->delete();
			}
		} while (count($aUser_Accessdenieds) == $limit);

		return $this;
	}
}