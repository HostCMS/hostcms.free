<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Action_Model
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Admin_Form_Action_Model extends Core_Entity
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
	protected $_modelName = 'admin_form_action';

	/**
	 * Word name in back-end form
	 */
	public $word_name = NULL;

	/**
	 * Additional attrs
	 * @var array
	 */
	public $attrs = array();

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
		'admin_form_action_dir' => array(),
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
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
	 * Get allowed actions for user
	 * @param User_Model $oUser User object
	 * @return array
	 */
	public function getAllowedActionsForUser(User_Model $oUser)
	{
		if (!$oUser->superuser)
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
	 * Cache for checkAllowedActionForUser
	 * @var array
	 */
	protected $_cacheCheckAllowedActionForUser = array();

	/**
	 * Check allowed action for user
	 * @param User_Model $oUser User object
	 * @param string $actionName
	 * @return boolean
	 */
	public function checkAllowedActionForUser(User_Model $oUser, $actionName)
	{
		if (!isset($this->_cacheCheckAllowedActionForUser[$oUser->id][$actionName]))
		{
			if (!$oUser->superuser)
			{
				$this
					->queryBuilder()
					->select('admin_form_actions.*')
					->join('company_department_action_accesses', 'admin_form_actions.id', '=', 'company_department_action_accesses.admin_form_action_id')
					->join('company_department_post_users', 'company_department_action_accesses.company_department_id', '=', 'company_department_post_users.company_department_id')
					->where('company_department_post_users.user_id', '=', $oUser->id)
					->where('company_department_action_accesses.site_id', '=', CURRENT_SITE)
					->where('admin_form_actions.name', '=', $actionName)
					->limit(1);
			}

			$aAdmin_Form_Actions = $this->findAll();

			$this->_cacheCheckAllowedActionForUser[$oUser->id][$actionName] = isset($aAdmin_Form_Actions[0]);
		}

		return $this->_cacheCheckAllowedActionForUser[$oUser->id][$actionName];
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event admin_form_action.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$newObjectWord = $this->Admin_Word->copy();
		$newObject->add($newObjectWord);

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Get caption of the action
	 * @return string|NULL
	 */
	public function getCaption($admin_language_id)
	{
		$oAdmin_Word = $this->Admin_Word->getWordByLanguage($admin_language_id);

		return !is_null($oAdmin_Word)
			? $oAdmin_Word->name
			: NULL;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->modal)
		{
			Core_Html_Entity::factory('Span')
				->class('badge badge-hostcms badge-square darkgray pull-right')
				->title(Core::_('Admin_Form_Action.modalBadge'))
				->value('<i class="fa fa-window-restore fa-fw"></i>')
				->execute();
		}
	}
}