<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule_Controller_Execute
 *
 * @package HostCMS
 * @subpackage Schedule
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2025 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Schedule_Controller_Execute extends Admin_Form_Action_Controller
{
	/**
	 * Execute schedule
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			set_time_limit(90000);
			ini_set("max_execution_time", "90000");
			ini_set("memory_limit", "512M");
		}

		$oSchedule_Controller = new Schedule_Controller();
		$oSchedule_Controller->execute($this->_object);

		return $this;
	}
}