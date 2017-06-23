<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Group_Action_Access_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Group_Action_Access_Model extends Core_Entity 
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
		'user_group' => array(),
		'admin_form_action' => array(),
		'site' => array(),
		'user' => array()
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
	 * Get element by site and action
	 * @param int $site_id site ID
	 * @param int $admin_form_action_id action ID
	 * @return mixed
	 */
	public function getBySiteAndAction($site_id, $admin_form_action_id)
	{
		$this->queryBuilder()
			// т.к. с учетом заданных в связи условий User_Group
			//->clear()
			->where('site_id', '=', $site_id)
			->where('admin_form_action_id', '=', $admin_form_action_id)
			->limit(1);

		$aUser_Group_Action_Access = $this->findAll();

		if (isset($aUser_Group_Action_Access[0]))
		{
			return $aUser_Group_Action_Access[0];
		}

		return NULL;
	}
}