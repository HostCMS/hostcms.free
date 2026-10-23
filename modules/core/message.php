<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Message helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
	 * @param string $message Message text
	 * @param string $type Message type
	 */
	static public function show($message, $type = 'success')
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
	 * @param string $message Message text
	 * @param string $type Message type
	 * @see Core_Message::show()
	 * @return string
	 */
	static public function get($message, $type = 'success')
	{
		$args = func_get_args();

		return call_user_func_array(array(Core_Skin::instance(), 'getMessage'), $args);
	}
}