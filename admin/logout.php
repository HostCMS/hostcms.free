<?php
/**
 * Administration center. Logout.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../bootstrap.php');

Core_Auth::systemInit();
Core_Auth::setCurrentSite();

Core_Log::instance()->clear()
	->status(Core_Log::$SUCCESS)
	->write(Core::_('Core.error_log_exit'));

Core_Auth::logout();

header('Location: /admin/index.php');