<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Module_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Module_Model extends Core_Entity
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
		'module' => array(),
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
	 * Get element by site and module 
	 * @param int $site_id site ID
	 * @param int $module_id module ID
	 * @return mixed
	 */
	public function getBySiteAndModule($site_id, $module_id)
	{
		$this->queryBuilder()
			// т.к. с учетом заданных в связи условий User_Group
			//->clear()
			->where('site_id', '=', $site_id)
			->where('module_id', '=', $module_id)
			->limit(1);

		$aUser_Module = $this->findAll();

		if (isset($aUser_Module[0]))
		{
			return $aUser_Module[0];
		}

		return NULL;
	}
}