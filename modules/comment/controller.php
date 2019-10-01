<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment_Controller
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Comment_Controller
{
	static protected $_config = NULL;
	
	static public function getConfig()
	{
		if (is_null(self::$_config))
		{
			self::$_config = Core_Config::instance()->get('comment_config', array()) + array(
				'gradeStep' => 1,
				'gradeLimit' => 10,
			);
		}
		
		return self::$_config;
	}
}