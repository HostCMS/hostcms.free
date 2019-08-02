<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Cache observers
 *
 * @package HostCMS
 * @subpackage Core\Cache
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Cache_Observer
{
	/**
	 * onBeforeSet callback method
	 * @param Core_Cache $object
	 * @param array $args array of arguments
	 */
	static public function onBeforeSet($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();
		$oCore_Registry->set('Core_Cache.onBeforeSet', Core::getmicrotime());
	}

	/**
	 * onAfterSet callback method
	 * @param Core_Cache $object
	 * @param array $args array of arguments
	 */
	static public function onAfterSet($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();
		$time = Core::getmicrotime() - $oCore_Registry->get('Core_Cache.onBeforeSet', 0);

		$oCore_Registry->set('Core_Cache.setTime',
			$oCore_Registry->get('Core_Cache.setTime', 0) + $time
		);

		$oCore_Registry->set('Core_Cache.setCount',
			$oCore_Registry->get('Core_Cache.setCount', 0) + 1
		);
	}

	/**
	 * onBeforeGet callback method
	 * @param Core_Cache $object
	 * @param array $args array of arguments
	 */
	static public function onBeforeGet($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();
		$oCore_Registry->set('Core_Cache.onBeforeGet', Core::getmicrotime());
	}

	/**
	 * onAfterGet callback method
	 * @param Core_Cache $object
	 * @param array $args array of arguments
	 */
	static public function onAfterGet($object, $args)
	{
		$oCore_Registry = Core_Registry::instance();
		$time = Core::getmicrotime() - $oCore_Registry->get('Core_Cache.onBeforeGet', 0);

		$oCore_Registry->set('Core_Cache.getTime',
			$oCore_Registry->get('Core_Cache.getTime', 0) + $time
		);

		$oCore_Registry->set('Core_Cache.getCount',
			$oCore_Registry->get('Core_Cache.getCount', 0) + 1
		);
	}
}
