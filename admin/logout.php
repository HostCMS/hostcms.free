<?php
/**
 * Administration center. Logout.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
require_once('../bootstrap.php');

Core_Auth::systemInit();
Core_Auth::setCurrentSite();

if (Core_Security::checkCsrf(Core_Array::getGet('secret_csrf', '', 'str'), Core::$mainConfig['csrf_lifetime']))
{
	/*switch (Core_Security::getCsrfError())
	{
		case 'wrong-length':
			throw new Core_Exception(Core::_('Core.csrf_wrong_token'), array(), 0, FALSE);
		break;
		case 'wrong-token':
		default:
			throw new Core_Exception(Core::_('Core.csrf_wrong_token'), array(), 0, FALSE);
		break;
		case 'timeout':
			throw new Core_Exception(Core::_('Core.csrf_token_timeout'), array(), 0, FALSE);
		break;
	}*/
	
	Core_Log::instance()->clear()
		->status(Core_Log::$SUCCESS)
		->write(Core::_('Core.error_log_exit'));

	Core_Auth::logout();
}

header('Location: /admin/index.php');