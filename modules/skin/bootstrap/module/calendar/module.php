<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Calendar. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Calendar_Module extends Calendar_Module
{
	/**
	 * Name of the skin
	 * @var string
	 */
	protected $_skinName = 'bootstrap';

	/**
	 * Name of the module
	 * @var string
	 */
	protected $_moduleName = 'calendar';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			0 => array('title' => Core::_('Calendar.widget_title'))
		);
	}

	/**
	 * Show admin widget
	 * @param int $type
	 * @param boolean $ajax
	 * @return self
	 */
	public function adminPage($type = 0, $ajax = FALSE)
	{
		$type = intval($type);
		$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);
		$path = "/admin/index.php?ajaxWidgetLoad&moduleId={$oModule->id}&type={$type}";

		switch ($type)
		{
			case 1: // Завершение дела
				if ($ajax)
				{
					Core_Session::close();

					$iEventId = intval(Core_Array::getPost('eventId'));

					$aJson = array();

					Core_Entity::factory('Event', $iEventId)
						->completed(1)
						->save();

					$aJson['eventId'] = $iEventId;
					Core::showJson($aJson);
				}
				break;

			case 2: // Изменение статуса дела
				if ($ajax)
				{
					Core_Session::close();

					$iEventId = intval(Core_Array::getPost('eventId'));
					$iEventStatusId = intval(Core_Array::getPost('eventStatusId'));

					Core_Entity::factory('Event', $iEventId)
						->event_status_id($iEventStatusId)
						->save();

					$oEventStatus = Core_Entity::factory('Event_Status', $iEventStatusId);

					$aJson = array();
					$aJson['finalStatus'] = $oEventStatus->final;

					Core::showJson($aJson);
				}

				break;

			case 3: // Добавление дела
				if ($ajax)
				{
					Core_Session::close();

					$aJson = array();

					//$iEventId = intval(Core_Array::getPost('eventId'));
					$sEventName = Core_Array::getPost('event_name');

					$aJson['event_name'] = $sEventName;

					$oEvent = Core_Entity::factory('Event');
					$oEvent->name = $sEventName;
					$oEvent->type = 0; // 0 - личное

					$iCurrentTimestamp = time();

					$oEvent->datetime = Core_Date::timestamp2sql($iCurrentTimestamp);
					$oEvent->start = Core_Date::timestamp2sql($iCurrentTimestamp);

					$oSite = Core_Entity::factory('Site', CURRENT_SITE);

					// Компании, связанные с текущим сайтом
					$aCompanies = $oSite->Companies->findAll();

					$aCompaniesId = array();

					foreach ($aCompanies as $oCompany)
					{
						$aCompaniesId[] = $oCompany->id;
					}

					$oUser = Core_Auth::getCurrentUser();

					// Получаем список должностей пользователя (сотрудника)
					$oCompany_Department_Post_Users = $oUser->Company_Department_Post_Users;

					$oCompany_Department_Post_Users
						->queryBuilder()
						->where('company_id', 'IN', $aCompaniesId);

					$aCompany_Department_Post_Users = $oCompany_Department_Post_Users->findAll();

					$oEvent->save();

					if (isset($aCompany_Department_Post_Users[0]))
					{
						$aResponsibleEmployees[] = $aCompany_Department_Post_Users[0]->company_id . '_' . $aCompany_Department_Post_Users[0]->company_department_id . '_' . $oUser->id;

						$oEventUser = Core_Entity::factory('Event_User')
							->company_id($aCompany_Department_Post_Users[0]->company_id)
							->company_department_id($aCompany_Department_Post_Users[0]->company_department_id)
							->user_id($oUser->id)
							->creator(1);

						$oEvent->add($oEventUser);
					}

					Core::showJson($aJson);
				}
				break;

			default:

				Calendar_Controller::createContextMenu();

				if ($ajax)
				{
					$this->_content();
				}
				else
				{
				?><div class="col-xs-12 col-sm-6" id="calendarAdminPage" data-hostcmsurl="<?php echo htmlspecialchars($path)?>">
					<script>
					$.widgetLoad({ path: '<?php echo $path?>', context: $('#calendarAdminPage') });
					</script>
				</div>
				<?php
				}
		}

		return TRUE;
	}

	/**
	 * Content
	 * @return self
	 */
	protected function _content()
	{
		$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);

		?>
		<div class="widget">
			<div class="widget-header bordered-bottom bordered-sky">
				<i class="widget-icon fa fa-calendar sky"></i>
				<span class="widget-caption sky"><?php echo Core::_('Calendar.widget_title')?></span>
				<div class="widget-buttons">
					<a data-toggle="maximize">
						<i class="fa fa-expand gray"></i>
					</a>
					<a data-toggle="upload" onclick="$(this).find('i').addClass('fa-spin'); $.widgetLoad({ path: '/admin/index.php?ajaxWidgetLoad&moduleId=<?php echo $oModule->id?>&type=0', context: $('#calendarAdminPage')});">
						<i class="fa-solid fa-rotate gray"></i>
					</a>
				</div>
			</div><!--Widget Header-->

			<div class="widget-body">
				<div id='calendar'></div>
			</div><!--Widget Body-->
			<script>
				var aScripts = [
					//'moment.min.js', // see bootstrap.php => datetime/moment.js
					'fullcalendar.min.js',
					'locale-all.js'
				];

				$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/fullcalendar/').done(function() {
					// all scripts loaded
					var date = new Date(),
						d = date.getDate(),
						m = date.getMonth(),
						y = date.getFullYear();

					 $('#calendar').fullCalendar({
						locale: '<?php echo Core_I18n::instance()->getLng()?>',
						timezone: 'local',
						//height: 'auto',
						height: 'parent',
						//aspectRatio: 1.05,

						scrollTime: "08:00:00",
						/*
						businessHours: {
							// days of week. an array of zero-based day of week integers (0=Sunday)
							//dow: [ 1, 2, 3, 4 ], // Monday - Thursday

							start: '08:00', // a start time (10am in this example)
							end: '20:00', // an end time (6pm in this example)
						},
						*/
						header: {
							left: 'prev,next today',
							center: 'title',
							right: 'month,agendaWeek,agendaDay'
						},
						defaultView: 'agendaDay', //'month',
						timeFormat: 'H:mm',
						navLinks: true,
						// Интервал (шаг) изменения времени при перетаскивании события
						snapDuration: '00:01:00',
						editable: true,
						droppable: true, // this allows things to be dropped onto the calendar
						dayClick: calendarDayClick,
						eventClick: calendarEventClick,
						events: calendarEvents,
						eventRender: calendarEventRender,
						eventDragStart: calendarEventDragStart,
						eventResizeStart: calendarEventResizeStart,
						// Изменение продолжительности
						eventResize: calendarEventResize,
						// Изменеие даты начала
						eventDrop: calendarEventDrop,
						// Удаление события из DOM
						eventDestroy: calendarEventDestroy,
						defaultDate: date
					});
				});
			</script>
		</div><!--Widget -->
		<?php
		return $this;
	}
}