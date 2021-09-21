<?php
/**
 * Back end
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once ('../bootstrap.php');

Core_Auth::systemInit();
Core_Auth::setCurrentSite();

// Check IP addresses
$sRemoteAddr = Core_Array::get($_SERVER, 'REMOTE_ADDR', '127.0.0.1');
$aIp = array($sRemoteAddr);
$HTTP_X_FORWARDED_FOR = Core_Array::get($_SERVER, 'HTTP_X_FORWARDED_FOR');
if (!is_null($HTTP_X_FORWARDED_FOR) && $sRemoteAddr != $HTTP_X_FORWARDED_FOR)
{
	$aIp[] = $HTTP_X_FORWARDED_FOR;
}

if (Core::moduleIsActive('ipaddress'))
{
	$oIpaddress_Controller = new Ipaddress_Controller();

	$bBlocked = $oIpaddress_Controller->isBackendBlocked($aIp);

	if ($bBlocked)
	{
		$oCore_Response = new Core_Response();

		$oCore_Response
			->status(403)
			->header('Pragma', 'no-cache')
			->header('Cache-Control', 'private, no-cache')
			->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
			->header('X-Powered-By', 'HostCMS')
			->body('HostCMS: Error 403. Access Forbidden!')
			->sendHeaders()
			->showBody();
		exit();
	}
}

ob_start();

Core_Session::start();
if (!is_null(Core_Array::getGet('skinName')))
{
	$skinName = Core_Array::getGet('skinName');

	$aConfig = Core_Config::instance()->get('skin_config');
	if (isset($aConfig[$skinName]))
	{
		Core::$mainConfig['skin'] = $_SESSION['skin'] = $skinName;
	}
	else
	{
		throw new Core_Exception('Skin does not allow.');
	}
}
elseif (isset($_SESSION['skin']))
{
	Core::$mainConfig['skin'] = $_SESSION['skin'];
}

$oAdmin_Answer = Core_Skin::instance()->answer();

if (!is_null(Core_Array::getPost('submit')))
{
	$bDeviceTracking = isset($_POST['ip']);

	$_COOKIE['hostcms_device_tracking'] = $bDeviceTracking ? 'on' : 'off';
	setcookie('hostcms_device_tracking', $_COOKIE['hostcms_device_tracking'], time() + 31536000, '/');

	try {
		$authResult = Core_Auth::login(
			Core_Array::getPost('login'), Core_Array::getPost('password'), $bDeviceTracking
		);

		Core_Auth::setCurrentSite();
	}
	catch (Exception $e)
	{
		$oAdmin_Answer->message(
			Core_Message::get($e->getMessage(), 'error')
		);
	}
}

if (!Core_Auth::logged())
{
	$title = Core::_('Admin.authorization_title');

	if (isset($authResult))
	{
		if ($authResult == FALSE)
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				//->notify(FALSE)
				->write(Core::_('Core.error_log_authorization_error'));

			$oAdmin_Answer->message(
				Core_Message::get(
					Core::_('Admin.authorization_error_valid_user', Core_Array::get($_SERVER, 'REMOTE_ADDR', 'undefined'))
				, 'error')
			);
		}
		// если пользователю сейчас запрещен ввод пароля
		elseif (is_array($authResult) && $authResult['result'] == -1)
		{
			$error_admin_access = $authResult['value'];

			$oAdmin_Answer->message(
				Core_Message::get(
					Core::_('Admin.authorization_error_access_temporarily_unavailable', $error_admin_access),
				'error')
			);
		}
	}

	Core_Skin::instance()->authorization();
}
else
{
	$title = Core::_('Admin.index_title', 'HostCMS', Core_Auth::logged() ? strip_tags(CURRENT_VERSION) : 6);
	Core::initConstants(Core_Entity::factory('Site', CURRENT_SITE));
	Core_Skin::instance()->index();
}

$oAdmin_Answer
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title($title)
	->module('dashboard')
	->execute();