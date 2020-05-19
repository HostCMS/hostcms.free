<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Session_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Session_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'user_session';

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
		'user' => array(),
		'session' => array(),
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'user_sessions.id' => 'ASC',
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	public function user_idBackend()
	{
		return $this->User->showAvatarWithName();
	}
}