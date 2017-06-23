<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Group_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Group_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'user' => array(),
		'user_module' => array(),
		'user_group_action_access' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'root_dir' => '/'
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
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event user_group.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Users->deleteAll(FALSE);
		$this->User_Modules->deleteAll(FALSE);
		$this->User_Group_Action_Accesses->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get group by name
	 * @param string $name name
	 * @return User_Group_Model
	 */
	public function getByName($name)
	{
		$this->queryBuilder()
			->clear()
			->where('name', '=', $name)
			->limit(1);

		return $this->find();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		// Копируем права доступа группы пользователей к модулю
		$aAllRelatedUserModules = $this->User_Modules->findAll();

		$user_id = Core_Type_Conversion::toInt($_SESSION['current_users_id']);

		foreach ($aAllRelatedUserModules as $oUserModule)
		{
			$oNewUserModule = clone $oUserModule;
			$oNewUserModule->user_id = $user_id;
			$newObject->add($oNewUserModule);
		}

		// Копируем права доступа группы пользователей к действиям
		$aAllRelatedUserGroupActionAccesses = $this->User_Group_Action_Accesses->findAll();

		foreach ($aAllRelatedUserGroupActionAccesses as $oUserGroupActionAccess)
		{
			$oNewUserGroupActionAccess = clone $oUserGroupActionAccess;
			$oNewUserGroupActionAccess->user_id = $user_id;
			$newObject->add($oNewUserGroupActionAccess);
		}

		return $newObject;
	}

	/**
	 * Isset module access
	 * @param Module_Model $oModule module
	 * @param Site_Model $oSite site
	 * @return boolean
	 */
	public function issetModuleAccess(Module_Model $oModule, Site_Model $oSite)
	{
		$oUser_Module = $this->getModuleAccess($oModule, $oSite);
		return !is_null($oUser_Module);
	}

	/**
	 * Get acces to module
	 * @param Module_Model $oModule module
	 * @param Site_Model $oSite site
	 * @return User_Module_Model
	 */
	public function getModuleAccess(Module_Model $oModule, Site_Model $oSite)
	{
		if (is_null($oModule->name))
		{
			throw new Core_Exception('Module does not exist');
		}

		$oUser_Module = $this->User_Modules->getBySiteAndModule(intval($oSite->id), intval($oModule->id));
		return $oUser_Module;
	}

	/**
	 * Get acces to form's action
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 * @param Site_Model $oSite site
	 * @return User_Group_Action_Access_Model
	 */
	public function getAdminFormActionAccess(Admin_Form_Action_Model $oAdmin_Form_Action, Site_Model $oSite)
	{
		if (is_null($oAdmin_Form_Action->name))
		{
			throw new Core_Exception('Action does not exist');
		}

		$oUser_Group_Action_Access = $this->User_Group_Action_Accesses->getBySiteAndAction(intval($oSite->id), intval($oAdmin_Form_Action->id));

		return $oUser_Group_Action_Access;
	}
}