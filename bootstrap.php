<?php
/**
 * HostCMS bootstrap file.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
define('CMS_FOLDER', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('HOSTCMS', TRUE);
//define('USE_HOSTCMS_5', TRUE);

// ini_set("memory_limit", "32M");
// ini_set("max_execution_time", "120");

// Константа запрещает выполнение ini_set, по умолчанию false - разрешено
define('DENY_INI_SET', FALSE);

// Запрещаем установку локали, указанной в параметрах сайта
// define('ALLOW_SET_LOCALE', FALSE);
setlocale(LC_NUMERIC, "POSIX");

if (!defined('DENY_INI_SET') || !DENY_INI_SET)
{
	ini_set('display_errors', 1);

	if (version_compare(PHP_VERSION, '5.3', '<'))
	{
		/* Решение проблемы trict Standards: Implicit cloning object of class 'kernel' because of 'zend.ze1_compatibility_mode' */
		ini_set('zend.ze1_compatibility_mode', 0);

		set_magic_quotes_runtime(0);
		ini_set('magic_quotes_gpc', 0);
		ini_set('magic_quotes_sybase', 0);
		ini_set('magic_quotes_runtime', 0);
	}
}

//function_exists('date_default_timezone_set') && date_default_timezone_set(date_default_timezone_get());

$config_path = CMS_FOLDER . 'hostcmsfiles/config_db.php';
if (defined('USE_HOSTCMS_5') && USE_HOSTCMS_5 && is_file($config_path))
{
	require_once($config_path);
}

require_once(CMS_FOLDER . 'modules/core/core.php');

Core::init();

date_default_timezone_set(Core::$mainConfig['timezone']);

if (Core_Auth::logged())
{
	// Observers
	Core_Event::attach('Xsl_Processor.onBeforeProcess', array('Xsl_Processor_Observer', 'onBeforeProcess'));
	Core_Event::attach('Xsl_Processor.onAfterProcess', array('Xsl_Processor_Observer', 'onAfterProcess'));
	Core_Event::attach('Core_Cache.onBeforeGet', array('Core_Cache_Observer', 'onBeforeGet'));
	Core_Event::attach('Core_Cache.onAfterGet', array('Core_Cache_Observer', 'onAfterGet'));
	Core_Event::attach('Core_Cache.onBeforeSet', array('Core_Cache_Observer', 'onBeforeSet'));
	Core_Event::attach('Core_Cache.onAfterSet', array('Core_Cache_Observer', 'onAfterSet'));
}

if (defined('USE_HOSTCMS_5') && USE_HOSTCMS_5)
{
	require_once(CMS_FOLDER . 'modules/hostcms5/Kernel/Kernel.php');
}

// Robokassa SMS observers
// Core_Event::attach('shop_order.onAfterChangeStatusPaid', array('Shop_Observer_Robokassa', 'onAfterChangeStatusPaid'));
// Core_Event::attach('Shop_Payment_System_Handler.onAfterProcessOrder', array('Shop_Observer_Robokassa', 'onAfterProcessOrder'));

/** Yandex.Market **/
// Идентификатор кампании на Яндексе
Shop_Controller_Yandexmarket_Observer::$campaignId = 21421819;
// Идентификатор статуса заказа "Передано в доставку"
Shop_Controller_Yandexmarket_Observer::$deliveryStatusId = 2;
// Отладочный токен, срок жизни - 365 дней
Shop_Controller_Yandexmarket_Observer::$token = 'AQAAAAAfQUt4AASAHcOqHpWtUkV0mucpfef1gdA';
// Идентификатор приложения авторизации в oauth.yandex.ru
Shop_Controller_Yandexmarket_Observer::$clientId = '88f09f274e9f4a46ad5374685af5bb5f';
Core_Event::attach('Shop_Payment_System_Handler.onAfterChangedOrder', array('Shop_Controller_Yandexmarket_Observer', 'onAfterChangedOrder'));

// Windows locale
//setlocale(LC_ALL, array ('ru_RU.utf-8', 'rus_RUS.utf8'));