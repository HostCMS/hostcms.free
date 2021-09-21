<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar_Controller
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Calendar_Controller
{
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
}