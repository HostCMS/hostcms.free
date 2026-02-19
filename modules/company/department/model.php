<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Company_Department_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'user' => array('through' => 'company_department_post_user'),
		'company_department_module' => array(),
		'company_department_action_access' => array(),
		'company_department_post_user' => array(),
		'company_department_directory_email' => array(),
		'directory_email' => array('through' => 'company_department_directory_email'),
		'company_department_directory_phone' => array(),
		'directory_phone' => array('through' => 'company_department_directory_phone'),
		'company_department' => array('foreign_key' => 'parent_id'),
		'deal_template_step_access_department' => array(),
		'dms_nomenclature_dir' => array(),
		'dms_document_type_department_access' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'company' => array(),
		'user' => array(),
		'company_department' => array('foreign_key' => 'parent_id')
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
	 * Get heads
	 * @return array
	 */
	public function getHeads()
	{
		return $this->_getHead(1);
	}

	/**
	 * Get employees without heads
	 * @return array
	 */
	public function getEmployeesWithoutHeads()
	{
		return $this->_getHead(0);
	}

	/**
	 * Get head
	 * @param boolean $isHead
	 * @return array
	 */
	protected function _getHead($isHead)
	{
		$oUser = Core_Entity::factory('User');
		$oUser->queryBuilder()
			->distinct()
			->join('company_department_post_users', 'users.id', '=', 'company_department_post_users.user_id')
			->where('company_department_post_users.company_department_id', '=', $this->id)
			->where('company_department_post_users.head', '=', $isHead ? 1 : 0);

		return $oUser->findAll();
	}

	/**
	 * Show deal templates step access
	 * @param int $deal_template_step_id
	 * @param Deal_Template_Step_Access_Department_Model $oDeal_Template_Step_Access_Department
	 */
	public function showDealTemplateStepAccess($deal_template_step_id, $oDeal_Template_Step_Access_Department = NULL)
	{
		if (is_null($oDeal_Template_Step_Access_Department))
		{
			$oDeal_Template_Step_Access_Department = Core_Entity::factory('Deal_Template_Step_Access_Department')->find($deal_template_step_id);
		}

		echo '<div class="department"><span class="department_name">' . htmlspecialchars($this->name) . '</span><span class="icons_permissions">';
		//<i class="fa fa-circle"></i><i class="fa fa-circle"></i><i class="fa fa-circle"></i><i class="fa fa-circle"></i>'
		for ($bitNumber = 0; $bitNumber < 4 ; $bitNumber++)
		{

			$bitValue = !is_null($oDeal_Template_Step_Access_Department->id) ? Core_Bit::getBit($oDeal_Template_Step_Access_Department->access, $bitNumber) : 0;

			switch($bitNumber)
			{
				case 0:
					$actionName = 'create';
					$actionTitle = Core::_('Deal_Template_Step.actionTitleCreate');
					break;

				case 1:
					$actionName = 'edit';
					$actionTitle = Core::_('Deal_Template_Step.actionTitleEdit');
					break;

				case 2:
					$actionName = 'show';
					$actionTitle = Core::_('Deal_Template_Step.actionTitleShow');
					break;

				case 3:
					$actionName = 'delete';
					$actionTitle = Core::_('Deal_Template_Step.actionTitleDelete');
					break;
			}

			echo '<i id="department_' . $oDeal_Template_Step_Access_Department->company_department_id . '_' . $deal_template_step_id . '_' . $bitNumber. '" title="' . $actionTitle . '" data-action="' . $actionName . '" data-allowed="' . $bitValue . '" class="fa ' . ($bitValue ? 'fa-circle' : 'fa-circle-o'). '"></i>';

		}
		echo '</span></div>';
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event company_department.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Company_Department_Post_Users->deleteAll(FALSE);
		$this->Company_Departments->deleteAll(FALSE);
		$this->Company_Department_Action_Accesses->deleteAll(FALSE);
		$this->Company_Department_Modules->deleteAll(FALSE);

		$this->Directory_Emails->deleteAll(FALSE);
		$this->Directory_Phones->deleteAll(FALSE);

		if (Core::moduleIsActive('deal'))
		{
			$this->Deal_Template_Step_Access_Departments->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('dms'))
		{
			$this->Dms_Document_Type_Department_Accesses->deleteAll(FALSE);
		}

		return parent::delete($primaryKey);
	}


	/**
	 * Isset module access
	 * @param Module_Model $oModule module
	 * @param Site_Model $oSite site
	 * @return boolean
	 */
	public function issetModuleAccess(Module_Model $oModule, Site_Model $oSite)
	{
		$oCompany_Department_Module = $this->getModuleAccess($oModule, $oSite);
		return !is_null($oCompany_Department_Module);
	}

	/**
	 * Get acces to module
	 * @param Module_Model $oModule module
	 * @param Site_Model $oSite site
	 * @return Company_Department_Module
	 */
	public function getModuleAccess(Module_Model $oModule, Site_Model $oSite)
	{
		if (is_null($oModule->name))
		{
			throw new Core_Exception('Module does not exist');
		}

		$oCompany_Department_Modules = $this->Company_Department_Modules;
		$oCompany_Department_Modules
			->queryBuilder()
			//->join('company_department_modules', 'company_departments.id', '=', 'company_department_modules.company_department_id')
			->where('company_department_modules.site_id', '=', intval($oSite->id))
			->where('company_department_modules.module_id', '=', intval($oModule->id))
			->limit(1);

		$aCompany_Department_Modules = $oCompany_Department_Modules->findAll();

		return isset($aCompany_Department_Modules[0]) ? $aCompany_Department_Modules[0] : NULL;
	}

	/**
	 * Get acces to form's action
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 * @param Site_Model $oSite site
	 * @param bool $bCache cache
	 * @return User_Group_Action_Access_Model
	 */
	public function getAdminFormActionAccess(Admin_Form_Action_Model $oAdmin_Form_Action, Site_Model $oSite, $bCache = TRUE)
	{
		if (is_null($oAdmin_Form_Action->name))
		{
			throw new Core_Exception('Action does not exist');
		}

		$oCompany_Department_Action_Access = $this->Company_Department_Action_Accesses->getBySiteAndAction(intval($oSite->id), intval($oAdmin_Form_Action->id), $bCache);

		return $oCompany_Department_Action_Access;
	}

	/**
	 * Get parent
	 * @return Company_Department_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Company_Department', $this->parent_id)
			: NULL;
	}

	/**
	 * Get children
	 * @param boolean $bCache cache
	 * @return array
	 */
	public function getChildren($bCache = TRUE)
	{
		$aReturn = $aCompany_Departments = $this->Company_Departments->findAll($bCache);
		foreach ($aCompany_Departments as $oCompany_Department)
		{
			$aReturn = array_merge($aReturn, $oCompany_Department->getChildren($bCache));
		}

		return $aReturn;
	}
}