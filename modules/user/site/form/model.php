<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Site_Form_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Site_Form_Model extends Admin_Form_Model
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
	protected $_modelName = 'user_site_form';

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
	 * Set acces user to form
	 * @param int $access_value value
	 */
	protected function _setAccess($access_value)
	{
		$oAdmin_Form = Core_Entity::factory('Admin_Form', $this->id);
		$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->findAll();

		foreach ($aAdmin_Form_Actions as $oAdmin_Form_Actions)
		{
			$oUser_Site_Form_Action = Core_Entity::factory('User_Site_Form_Action', $oAdmin_Form_Actions->id);
			$oUser_Site_Form_Action->access = $access_value;
			$oUser_Site_Form_Action->save();
		}
	}

	/**
	 * Allow acces user to form
	 * @return self
	 */
	public function allowActions()
	{
		$this->_setAccess(1);
		return $this;
	}

	/**
	 * Deny acces user to form
	 * @return self
	 */
	public function denyActions()
	{
		$this->_setAccess(0);
		return $this;
	}
}