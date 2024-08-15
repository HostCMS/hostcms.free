<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Autosave_Model
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Admin_Form_Autosave_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'admin_form' => array(),
		'user' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Get admin form autosave object
	 * @param int $admin_form_id admin form id
	 * @param int $dataset dataset
	 * @param int $entity_id entity id
	 * @return Admin_Form_Autosave_Model|NULL
	 */
	public function getObject($admin_form_id, $dataset, $entity_id)
	{
		$oUser = Core_Auth::getCurrentUser();

		$oAdmin_Form_Autosave = Core_Entity::factory('Admin_Form_Autosave');
		$oAdmin_Form_Autosave->queryBuilder()
			->where('admin_form_autosaves.user_id', '=', $oUser->id)
			->where('admin_form_autosaves.admin_form_id', '=', $admin_form_id)
			->where('admin_form_autosaves.dataset', '=', $dataset)
			->where('admin_form_autosaves.entity_id', '=', $entity_id);

		return $oAdmin_Form_Autosave->getLast(FALSE);
	}
}