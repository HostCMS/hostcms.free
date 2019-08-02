<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Action_Model
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Model extends Core_Entity
{
	/**
	 * Word name in back-end form
	 */
	public $word_name = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'company_department_action_access' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'admin_word' => array(),
		'admin_form' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'admin_form_actions.sorting' => 'ASC'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'dataset' => -1,
		'single' => 1,
		'group' => 1,
		'sorting' => 0
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event admin_form_action.onBeforeRedeclaredDelete
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

		if (Core::moduleIsActive('company'))
		{
			$this->Company_Department_Action_Accesses->deleteAll(FALSE);
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Get form action by name
	 * @param String $name name
	 * @return Admin_Form_Action
	 */
	public function getByName($name)
	{
		$this->queryBuilder()
			// т.к. с учетом заданных в связи условий формы
			//->clear()
			->where('name', '=', $name)
			->limit(1);

		$aAdmin_Form_Actions = $this->findAll();

		if (isset($aAdmin_Form_Actions[0]))
		{
			return $aAdmin_Form_Actions[0];
		}

		return NULL;
	}

	/**
	 * Get allowed actions for user
	 * @param User_Model $oUser User object
	 * @return array Admin_Form_Action
	 */
	public function getAllowedActionsForUser(User_Model $oUser)
	{
		if ($oUser->superuser != 1)
		{
			$this
				->queryBuilder()
				->select('admin_form_actions.*')
				->join('company_department_action_accesses', 'admin_form_actions.id', '=', 'company_department_action_accesses.admin_form_action_id')
				->join('company_department_post_users', 'company_department_action_accesses.company_department_id', '=', 'company_department_post_users.company_department_id')
				->where('company_department_post_users.user_id', '=', $oUser->id)
				->where('company_department_action_accesses.site_id', '=', CURRENT_SITE)
				->groupBy('admin_form_actions.id');
		}

		$aAdmin_Form_Actions = $this->findAll();

		return $aAdmin_Form_Actions;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$newObjectWord = $this->Admin_Word->copy();
		$newObject->add($newObjectWord);

		return $newObject;
	}
}