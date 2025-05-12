<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Event_Controller
{
	/**
	 * Get datetime
	 * @param string $datetime SQL datetime
	 * @return string
	 */
	static public function getDateTime($datetime)
	{
		if ($datetime == '0000-00-00 00:00:00')
		{
			return '∞';
		}

		$timestamp = Core_Date::sql2timestamp($datetime);

		return trim(self::getDate($datetime) . ' ' . date('H:i', $timestamp));
	}

	/**
	 * Get get date
	 * @param string $datetime SQL datetime
	 * @return string
	 */
	static public function getDate($datetime)
	{
		$timestamp = Core_Date::sql2timestamp($datetime);

		$day = date('j-m-Y', $timestamp) !== date('j-m-Y')
			? date('j', $timestamp)
			: '';

		$month = strlen($day)
			? Core::_('Event.month_' . date('m', $timestamp), $day)
			: '';

		$year = date('Y', $timestamp) != date('Y')
			? ' ' . date('Y', $timestamp)
			: '';

		return $month . $year;
	}

	/**
	 * Show related events
	 * @param Core_Entity $oEntity entity
	 * @return mixed
	 */
	static public function showRelatedEvents(Core_Entity $oEntity)
	{
		$countEvents = $oEntity->Events->getCountByCompleted(0, FALSE);

		if ($countEvents)
		{
			// Просрочено
			$oEvents = $oEntity->Events;
			$oEvents->queryBuilder()
				->where('events.completed', '=', 0)
				->where('events.deadline', '<', Core_Date::timestamp2sql(time()));

			$count = $oEvents->getCount(FALSE);

			if ($count)
			{
				$class = 'deadline';
				$events = Core::_('Event.event_deadline');
			}
			else
			{
				// Сегодня
				$today = Core_Date::timestamp2sqldate(time());

				$oEvents = $oEntity->Events;
				$oEvents->queryBuilder()
					->where('events.completed', '=', 0)
					->where('events.deadline', 'BETWEEN', array($today . ' 00:00:00', $today . ' 23:59:59'));

				$count = $oEvents->getCount(FALSE);

				if ($count)
				{
					$class = 'today';
					$events = Core::_('Event.event_today');
				}
				else
				{
					// Скоро
					$oEvents = $oEntity->Events;
					$oEvents->queryBuilder()
						->where('events.completed', '=', 0)
						->where('events.deadline', '>', $today . ' 23:59:59');

					$count = $oEvents->getCount(FALSE);

					if ($count)
					{
						$class = 'empty';
						$events = Core::_('Event.event_empty');
					}
				}
			}
		}
		else
		{
			$class = 'empty';
			$events = Core::_('Event.no_events');
		}

		?><span class="<?php echo $class?>"><?php echo $events?>
			<?php
			if ($countEvents)
			{
				?><span class="count-events"><?php echo $count?></span><?php
			}
			?>
		</span><?php
	}

	/**
	 * Show crm projects filter
	 * @return self
	 */
	static public function showCrmProjectFilter()
	{
		ob_start();

		$oUser = Core_Auth::getCurrentUser();

		$crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');

		?><div class="crm-project-filter-wrapper"><?php
			$oCrm_Projects = Core_Entity::factory('Crm_Project');
			$oCrm_Projects->queryBuilder()
				->join('event_crm_projects', 'event_crm_projects.crm_project_id', '=', 'crm_projects.id')
				->join('events', 'events.id', '=', 'event_crm_projects.event_id')
				->join('event_users', 'event_users.event_id', '=', 'events.id')
				->where('crm_projects.site_id', '=', CURRENT_SITE)
				->where('event_users.user_id', '=', $oUser->id)
				->groupBy('crm_projects.id');

			$aCrm_Projects = $oCrm_Projects->findAll(FALSE);
			foreach ($aCrm_Projects as $oCrm_Project)
			{
				$icon = $oCrm_Project->crm_icon_id
					? $oCrm_Project->Crm_Icon->value
					: 'fa-solid fa-tasks';

				$color = $oCrm_Project->color != ''
					? $oCrm_Project->color
					: '#aebec4';

				$additionalParams = $crm_project_id == $oCrm_Project->id
					? ''
					: "crm_project_id={$oCrm_Project->id}";

				$onclick = "mainFormLocker.unlock(); $.adminLoad({additionalParams: '{$additionalParams}',current: '1',sortingDirection: '1',windowId: 'id_content',path: hostcmsBackend + '/event/index.php'}); return false";

				$active = $crm_project_id == $oCrm_Project->id
					? 'active'
					: '';

				?><div onclick="<?php echo $onclick?>" class="crm-project-filter-item <?php echo $active?>" title="<?php echo htmlspecialchars($oCrm_Project->name)?>" style="background-color: <?php echo htmlspecialchars($color)?>"><i class="<?php echo htmlspecialchars($icon)?>"></i></div><?php
			}
		?></div><?php

		return ob_get_clean();
	}
}