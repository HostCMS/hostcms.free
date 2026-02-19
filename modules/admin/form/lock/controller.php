<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Lock_Controller
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Admin_Form_Lock_Controller
{
	/**
	 * Lock form
	 * @param int $admin_form_id admin form id
	 * @param int $dataset dataset
	 * @param int $entity_id entity id
	 * @return Admin_Form_Lock_Model|NULL
	 */
	static public function lock($admin_form_id, $dataset, $entity_id)
	{
		$oAdmin_Form_Lock = Core_Entity::factory('Admin_Form_Lock')->getObject($admin_form_id, $dataset, $entity_id);

		if (is_null($oAdmin_Form_Lock))
		{
			$oAdmin_Form_Lock = Core_Entity::factory('Admin_Form_Lock');
			$oAdmin_Form_Lock->admin_form_id = $admin_form_id;
			$oAdmin_Form_Lock->dataset = $dataset;
			$oAdmin_Form_Lock->entity_id = $entity_id;
		}

		$oAdmin_Form_Lock->datetime = Core_Date::timestamp2sql(time());
		$oAdmin_Form_Lock->save();

		return $oAdmin_Form_Lock;
	}

	/**
	 * Unlock form
	 * @param int $admin_form_id admin form id
	 * @param int $dataset dataset
	 * @param int $entity_id entity id
	 * @return bool
	 */
	static public function unlock($admin_form_id, $dataset, $entity_id)
	{
		$oAdmin_Form_Lock = Core_Entity::factory('Admin_Form_Lock')->getObject($admin_form_id, $dataset, $entity_id);

		if (!is_null($oAdmin_Form_Lock))
		{
			$oAdmin_Form_Lock->delete();
		}

		return TRUE;
	}
}