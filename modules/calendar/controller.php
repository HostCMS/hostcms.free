<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Controller
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Controller
{
	/**
	 * Create context menu
	 */
	static public function createContextMenu()
	{
		// Формируем контекстное меню календаря
		$aModules = Core_Entity::factory('Module')->getAllByActive(1);

		$aContextMenuActions = array();

		// Для каждого модуля получаем массив действий для отображения в контекстном меню календаря
		foreach ($aModules as $oModule)
		{
			$oModule->loadModule();
			if (!is_null($oModule->Core_Module) && method_exists($oModule->Core_Module, 'getCalendarContextMenuActions'))
			{
				$aContextMenuActions = array_merge($aContextMenuActions, $oModule->Core_Module->getCalendarContextMenuActions());
			}
		}

		// Есть список элементов контекстного меню
		if (count($aContextMenuActions))
		{
		?>
			<script>
			// Контекстное меню отсутствует
			if (!$('#calendarContextMenu').length)
			{
				var calendarContextMenuDiv = $('<div id="calendarContextMenu" class="dropdown clearfix context-menu" style="display:none">'),
					calendarContextMenuUl = $('<ul class="dropdown-menu dropdown-info" role="menu" aria-labelledby="dropdownMenu" style="display:block;position:static;margin-bottom:5px;">');
				<?php
				foreach ($aContextMenuActions as $sContextMenuAction)
				{
					?>calendarContextMenuUl.append("<li><?php echo addcslashes($sContextMenuAction, '"')?></li>");<?php
				}
				?>
				calendarContextMenuDiv.append(calendarContextMenuUl);

				$('body').append(calendarContextMenuDiv);
			}
			</script>
		<?php
		}
	}

	/**
	 * Get calendar entities
	 * @param string $start
	 * @param string $end
	 * @return array
	 */
	static public function getCalendarEntities($start, $end)
	{
		$aReturn = array();

		$aModules = Core_Entity::factory('Module')->getAllByActive(1);

		// Для каждого модуля получаем события для отображения в календаре
		foreach ($aModules as $oModule)
		{
			$oModule->loadModule();
			if (!is_null($oModule->Core_Module) && method_exists($oModule->Core_Module, 'getCalendarEvents'))
			{
				$aReturn = array_merge($aReturn, $oModule->Core_Module->getCalendarEvents($start, $end));
			}
		}

		return $aReturn;
	}

	/**
	 * Get upload calendar entities
	 * @param string $last_modified
	 * @return array
	 */
	static public function getUploadCalendarEntities($last_modified)
	{
		$aReturn = array();

		$aModules = Core_Entity::factory('Module')->getAllByActive(1);

		// Для каждого модуля получаем события для отображения в календаре
		foreach ($aModules as $oModule)
		{
			$oModule->loadModule();
			if (!is_null($oModule->Core_Module) && method_exists($oModule->Core_Module, 'getUploadCalendarEvents'))
			{
				$aReturn = array_merge($aReturn, $oModule->Core_Module->getUploadCalendarEvents($last_modified));
			}
		}

		return $aReturn;
	}

	/**
	 * Sync all user calendars
	 */
	static public function sync(User_Model $oUser)
	{
		$aCalendar_Caldav_Users = $oUser->Calendar_Caldav_Users->findAll(FALSE);

		foreach ($aCalendar_Caldav_Users as $oCalendar_Caldav_User)
		{
			if ($oCalendar_Caldav_User->Calendar_Caldav->active)
			{
				$synchronized_datetime = Core_Date::sql2timestamp($oCalendar_Caldav_User->synchronized_datetime);

				if (strtotime('+1 minutes', $synchronized_datetime) < time())
				{
					try {
						$oCalendar_Caldav_User->Calendar_Caldav->sync($oCalendar_Caldav_User);
					}
					catch (Exception $e){
						//Core_Message::show($e->getMessage(), 'error');
					}
				}
			}
		}
	}
}