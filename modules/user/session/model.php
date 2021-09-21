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
		'user' => array()
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
			$this->_preloadValues['time'] = time();
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

	/**
	 * Backend callback method
	 * @return string
	 */
	public function imgBackend()
	{
		!is_null($this->dataSession) && Core::factory('Core_Html_Entity_Span')
			->value('<i class="fa fa-check-circle green" title="Session exists"></i>')
			->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	public function timeBackend()
	{
		return Core_Date::timestamp2string($this->time);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	public function osBackend()
	{
		return !is_null($this->user_agent)
			? Core_Browser::getOs($this->user_agent)
			: '—';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	public function deviceBackend()
	{
		$return = '—';

		if (!is_null($this->user_agent))
		{
			$device = Core_Browser::getDevice($this->user_agent);

			switch ($device)
			{
				case 0:
					$icon = 'fa-desktop';
				break;
				case 1:
					$icon = 'fa-tablet';
				break;
				case 2:
					$icon = 'fa-mobile-phone';
				break;
				case 3:
					$icon = 'fa-tv';
				break;
				case 3:
					$icon = 'fa-clock-0';
				break;
			}

			$return = '<i class="fa ' . $icon . '" title="' . Core::_('User_Session.device' . $device) . '"></i>';
		}

		return $return;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
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

	/**
	 * Destroy user session
	 */
 	public function destroy()
	{
		Core_Session::destroy($this->id);
		$this->delete();

		return $this;
	}
}