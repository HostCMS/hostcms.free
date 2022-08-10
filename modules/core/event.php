<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event system adds function calls for extending functionality.
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Event
{
	/**
	 * List of attached observers
	 * @var array
	 */
	static protected $_attached = array();

	/**
	 * Attach observer
	 * @param string $eventName event name
	 * @param string $function function name
	 * @param mixed $options additional options, default NULL
	 *
	 * <code>
	 * function my_function($object, $args)
	 * {
	 * 	// do something
	 * }
	 * $function = 'my_function';
	 * // Attach observer $function for event 'Class.onBeforeDelete'
	 * Core_Event::attach('Class.onBeforeDelete', $function);
	 * </code>
	 */
	static public function attach($eventName, $function, $options = NULL)
	{
		self::$_attached[$eventName][] = array($function, $options);
	}
	
	/**
	 * Attach observer to the beginning of the queue
	 * @param string $eventName event name
	 * @param string $function function name
	 * @param mixed $options additional options, default NULL
	 *
	 * <code>
	 * class my_class
	 * {
	 * 	static public function my_function($object, $args)
	 * 	{
	 * 		// do something
	 * 	}
	 * }
	 * // Attach observer my_class::my_function for event 'Class.onBeforeDelete'
	 * Core_Event::attach('Class.onBeforeDelete', array('my_class', 'my_function'));
	 * </code>
	 */
	static public function attachFirst($eventName, $function, $options = NULL)
	{
		!isset(self::$_attached[$eventName])
			&& self::$_attached[$eventName] = array();
			
		array_unshift(self::$_attached[$eventName], array($function, $options));
	}

	/**
	 * Detach observer
	 * @param string $eventName event name
	 * @param string $function function name
	 *
	 * <code>
	 * $function = 'my_function';
	 * // Detach observer $function from event 'Class.onBeforeDelete'
	 * Core_Event::detach('Class.onBeforeDelete', $function);
	 * </code>
	 */
	static public function detach($eventName, $function)
	{
		if (isset(self::$_attached[$eventName]))
		{
			foreach (self::$_attached[$eventName] as $key => $aValue)
			{
				if ($function === $aValue[0])
				{
					unset(self::$_attached[$eventName][$key]);
				}
			}
		}
	}

	/**
	 * Last returned value
	 * @var misex
	 */
	static protected $_lastReturn = NULL;

	/**
	 * Get the last returned value
	 *
	 * <code>
	 * $value = Core_Event::getLastReturn();
	 * </code>
	 *
	 * @return mixed
	 */
	static public function getLastReturn()
	{
		return self::$_lastReturn;
	}

	/**
	 * Notify all observers. If observer return FALSE, the cycle will stop.
	 *
	 * <code>
	 * // Call event 'Class.onBeforeDelete'
	 * Core_Event::notify('Class.onBeforeDelete', $this, array($primaryKey));
	 * </code>
	 *
	 * @param $eventName Name of event
	 * @param $object
	 * @param $args
	 */
	static public function notify($eventName, $object = NULL, $args = array())
	{
		self::$_lastReturn = NULL;

		if (isset(self::$_attached[$eventName]))
		{
			foreach (self::$_attached[$eventName] as $aValue)
			{
				self::$_lastReturn = call_user_func($aValue[0], $object, $args, $aValue[1]);
				if (self::$_lastReturn === FALSE)
				{
					break;
				}
			}

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Get count of observers
	 * @param string $eventName event name
	 *
	 * <code>
	 * echo Core_Event::getCount('Class.onBeforeDelete');
	 * </code>
	 */
	static public function getCount($eventName)
	{
		return isset(self::$_attached[$eventName])
			? count(self::$_attached[$eventName])
			: 0;
	}
}