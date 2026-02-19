<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Webauthn_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class User_Webauthn_Model extends Core_Entity
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['ip'] = Core::getClientIp();
			$this->_preloadValues['user_agent'] = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '');
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	// public function browserBackend()
 	public function browserBackend()
	{
		$browser = !is_null($this->user_agent)
			? Core_Browser::getBrowser($this->user_agent)
			: '—';

		if (!is_null($browser))
		{
			$ico = Core_Browser::getBrowserIco($browser);

			!is_null($ico)
				&& $browser = '<i class="' . $ico . '"></i> ' . $browser;
		}

		return $browser;
	}
}