<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment_Controller
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Comment_Controller
{
	/**
	 * Config
	 * @var array|NULL
	 */
	static protected $_config = NULL;

	/**
	 * Get confgi
	 * @return array|NULL
	 */
	static public function getConfig()
	{
		if (is_null(self::$_config))
		{
			self::$_config = Core_Config::instance()->get('comment_config', array()) + array(
				'gradeStep' => 1,
				'gradeLimit' => 5,
			);
		}

		return self::$_config;
	}
}