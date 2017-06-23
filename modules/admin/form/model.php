<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Model
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $actions = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $fields = 0;

	/**
	 * Backend property
	 * @var string
	 */
	public $name = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'admin_word' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'admin_form_setting' => array(),
		'admin_form_field' => array(),
		'admin_form_action' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'on_page' => ON_PAGE,
		'show_operations' => 1,
		'show_group_operations' => 1,
		'default_order_direction' => 1
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event admin_form.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Admin_Word->delete();

		$this->Admin_Form_Fields->deleteAll(FALSE);
		$this->Admin_Form_Actions->deleteAll(FALSE);
		$this->Admin_Form_Settings->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get setting for user
	 * @param int $user_id user id
	 * @return Admin_Form_Setting|NULL
	 */
	public function getSettingForUser($user_id)
	{
		$admin_form_settings = $this->admin_form_settings;
		$admin_form_settings->queryBuilder()
			->where('user_id', '=', $user_id);

		$aAdmin_Form_Setting = $admin_form_settings->findAll();

		$count = count($aAdmin_Form_Setting);

		if ($count == 0)
		{
			return NULL;
		}
		elseif ($count == 1)
		{
			return $aAdmin_Form_Setting[0];
		}
		else
		{
			array_shift($aAdmin_Form_Setting);

			foreach ($aAdmin_Form_Setting as $oAdmin_Form_Setting)
			{
				$oAdmin_Form_Setting->delete();
			}
		}
	}
}