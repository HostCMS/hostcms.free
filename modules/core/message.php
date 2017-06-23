<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Message helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Message
{
	/**
	 * Show message.
	 *
	 * <code>
	 * // Success message
	 * Core_Message::show(Core::_('constant.name'));
	 *
	 * // Error message
	 * Core_Message::show(Core::_('constant.message', 'value1', 'value2'), 'error');
	 * </code>
	 * @param $message Message text
	 * @param $type Message type
	 */
	static public function show($message, $type = 'message')
	{
		echo self::get($message, $type);
	}

	/**
	 * Get message.
	 *
	 * <code>
	 * echo Core_Message::get(Core::_('constant.name'));
	 * echo Core_Message::get(Core::_('constant.message', 'value1', 'value2'));
	 * </code>
	 * @param $message Message text
	 * @param $type Message type
	 * @see Core_Message::show()
	 * @return string
	 */
	static public function get($message, $type = 'message')
	{
		$args = func_get_args();

		return call_user_func_array(array(Core_Skin::instance(), 'getMessage'), $args);
	}
}