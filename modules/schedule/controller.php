<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Schedule.
 *
 * @package HostCMS
 * @subpackage Schedule
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Schedule_Controller
{
	/**
	 * Executes the business logic.
	 */
	public function execute(Schedule_Model $oSchedule)
	{
		$oSchedule->interval
			? $oSchedule->start_datetime = Core_Date::timestamp2sql(time() + $oSchedule->interval)
			: $oSchedule->completed = 1;

		$oSchedule->save();

		$oModule = $oSchedule->Module;

		if ($oModule->active)
		{
			// Запускаем обработку
			$oCore_Module = $oModule->loadModule()->Core_Module;

			if (!is_null($oCore_Module))
			{
				$aScheduleActions = $oCore_Module->getScheduleActions();

				$sAction = isset($aScheduleActions[$oSchedule->action])
					? $aScheduleActions[$oSchedule->action]
					: 'code: ' . $oSchedule->action;

				// Log Begin
				Core_Log::instance()
					->status(Core_Log::$MESSAGE)
					->write(Core::_('Schedule.log_message_begin', $oModule->name, $oSchedule->entity_id, $sAction));

				try {
					$oCore_Module->callSchedule($oSchedule->action, $oSchedule->entity_id);
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
				}

				// Log End
				Core_Log::instance()
					->status(Core_Log::$MESSAGE)
					->write(Core::_('Schedule.log_message_end', $oModule->name, $oSchedule->entity_id, $sAction));
			}
			else
			{
				Core_Log::instance()
					->status(Core_Log::$ERROR)
					->write(sprintf('Schedule_Controller: Core_Module is NULL, module: %s', $oModule->path));
			}
		}
		else
		{
			// Log Module was disabled
			Core_Log::instance()
				->status(Core_Log::$MESSAGE)
				->write(Core::_('Schedule.log_message_disabled', $oModule->name));
		}
	}

	/**
	 * Module Actions Cache
	 * @var array
	 */
	static protected $_getModuleActions = array();

	/**
	 * Get array of module actions
	 * @param int $moduleId Module Id
	 * @return mixed array|NULL
	 */
	public function getModuleActions($moduleId)
	{
		if ($moduleId)
		{
			if (isset(self::$_getModuleActions[$moduleId]))
			{
				return self::$_getModuleActions[$moduleId];
			}

			$oModule = Core_Entity::factory('Module')->find($moduleId);

			if (!is_null($oModule->id))
			{
				$oCore_Module = Core_Module::factory($oModule->path);
				if ($oCore_Module)
				{
					$aReturn = array();
					$aScheduleActions = $oCore_Module->getScheduleActions();
					if (count($aScheduleActions))
					{
						foreach ($aScheduleActions as $key => $value)
						{
							$aReturn[$key] = Core::_($oModule->path . '.' . 'schedule-' . $value);
						}
					}

					return self::$_getModuleActions[$moduleId] = $aReturn;
				}
			}
		}

		return NULL;
	}
}