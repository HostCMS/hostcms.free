<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Controller
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class User_Controller
{
	/**
	 * Show popover
	 * @param object $object
	 * @param array $args
	 * @param array $options
	 */
	static public function onAfterShowContentPopover($object, $args, $options)
	{
		$windowId = $options[0]->getWindowId();

		?><script>
		$('#<?php echo $windowId?> [data-popover="hover"]').showUserPopover('<?php echo $windowId?>');
		</script><?php
	}

    /**
     * Show popover
     * @param $controller
     * @param array $args
     * @throws Core_Exception
     */
	static public function onAfterRedeclaredPrepareForm($controller, $args)
	{
		list($object, $Admin_Form_Controller) = $args;

		$windowId = $Admin_Form_Controller->getWindowId();

		$controller->issetTab('main')
			&& $controller->getTab('main')->add(Admin_Form_Entity::factory('Script')->value("$('#{$windowId} [data-popover=\"hover\"]').showUserPopover('{$windowId}');"));
	}

	/**
	 * Log access to the `user_sessions` table
	 */
	static public function logToUserSession()
	{
		$ip = Core::getClientIp();
		$sessionId = session_id();
		$userAgent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');

		$oUser = Core_Auth::getCurrentUser();

		$oDataBase = Core_QueryBuilder::update('user_sessions')
			->set('user_id', $oUser->id)
			->set('time', time())
			->set('user_agent', $userAgent)
			//->set('ip', $ip)
			->where('id', '=', $sessionId)
			->where('ip', '=', $ip)
			->execute();

		// Returns the number of rows affected by the last SQL statement
		// If nothing's really was changed affected rowCount will return 0.
		if ($oDataBase->getAffectedRows() == 0)
		{
			Core_QueryBuilder::insert('user_sessions')
				->ignore()
				->columns('id', 'user_id', 'time', 'user_agent', 'ip')
				->values($sessionId, $oUser->id, time(), $userAgent, $ip)
				->execute();
		}
	}

	/**
	 * Destroy Old User Sessions
	 */
	static public function destroyOldUserSessions()
	{
		// Destroy Old session
		$aConfig = Core_Config::instance()->get('user_config', array()) + array(
			'destroySessionDays' => 90
		);

		if ($aConfig['destroySessionDays'] > 0)
		{
			$days = intval($aConfig['destroySessionDays']);

			$oUser_Sessions = Core_Entity::factory('User_Session');
			$oUser_Sessions->queryBuilder()
				->where('time', '<', strtotime("-{$days} days"))
				->orderBy('id', 'ASC')
				->limit(100);

			$aUser_Sessions = $oUser_Sessions->findAll(FALSE);
			foreach ($aUser_Sessions as $oUser_Session)
			{
				$oUser_Session->destroy();
			}
		}
	}
}