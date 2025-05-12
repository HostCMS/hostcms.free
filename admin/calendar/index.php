<?php
/**
 * Calendar
 *
 * @package HostCMS
 * @subpackage Calendar
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'calendar');

$oAdmin_Form_Controller = Admin_Form_Controller::create();

$sAdminFormAction = '/{admin}/calendar/index.php';

$oUser = Core_Auth::getCurrentUser();

$oSite = Core_Entity::factory('Site', CURRENT_SITE);

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->title(Core::_('Calendar.title'))
	->setUp();

// Добавление события
if (!is_null(Core_Array::getRequest('addEntity')) && $moduleId = intval(Core_Array::getRequest('moduleId')))
{
	$oModule = Core_Entity::factory('Module', $moduleId)->loadModule();
	if (method_exists($oModule->Core_Module, 'calendarAddEvent'))
	{
		$eventId = intval(Core_Array::getRequest('eventId'));
		$oModule->Core_Module->calendarAddEvent($eventId);
	}
}

// Удаление события
if (!is_null(Core_Array::getRequest('eventDelete')))
{
	Core_Session::close();

	$aJson = array();

	$moduleId = intval(Core_Array::getRequest('moduleId'));

	$oModule = Core_Entity::factory('Module', $moduleId);

	if (method_exists($oModule->Core_Module, 'calendarEventDelete'))
	{
		$eventId = intval(Core_Array::getRequest('eventId'));

		if ($oModule->Core_Module->calendarEventDelete($eventId))
		{
			$aJson['message'] = Core::_('Calendar.deleteEvent_success');
		}
		else
		{
			$aJson['error'] = 1;
			$aJson['message'] = Core::_('Calendar.deleteEvent_error');
		}
	}

	Core::showJson($aJson);
}

// Перемещение события
if (!is_null(Core_Array::getRequest('eventDrop')))
{
	Core_Session::close();

	$aJson = array();

	$moduleId = intval(Core_Array::getRequest('moduleId'));

	$oModule = Core_Entity::factory('Module', $moduleId);

	if (method_exists($oModule->Core_Module, 'calendarEventDrop'))
	{
		$eventId = intval(Core_Array::getRequest('eventId'));
		$startTimestamp = intval(Core_Array::getRequest('startTimestamp'));

		$allDay = intval(Core_Array::getRequest('allDay'));

		if ($oModule->Core_Module->calendarEventDrop($eventId, $startTimestamp, $allDay))
		{
			//$aJson['message'] = Core::_('Calendar.changeEventStart_success');
		}
		else
		{
			$aJson['error'] = 1;
			$aJson['message'] = Core::_('Calendar.changeEventStart_error');
		}
	}

	Core::showJson($aJson);
}

// Изменение продолжительности события
if (!is_null(Core_Array::getRequest('eventResize')))
{
	Core_Session::close();

	$aJson = array();

	$moduleId = intval(Core_Array::getRequest('moduleId'));

	$oModule = Core_Entity::factory('Module', $moduleId);

	if (method_exists($oModule->Core_Module, 'calendarEventResize'))
	{
		$eventId = intval(Core_Array::getRequest('eventId'));
		$deltaSeconds = intval(Core_Array::getRequest('deltaSeconds'));

		if ($oModule->Core_Module->calendarEventResize($eventId, $deltaSeconds))
		{
			//$aJson['message'] = Core::_('Calendar.changeEventDuration_success');
		}
		else
		{
			$aJson['error'] = 1;
			$aJson['message'] = Core::_('Calendar.changeEventDuration_error');
		}
	}

	Core::showJson($aJson);
}

if (!is_null(Core_Array::getRequest('loadEvents')))
{
	Core_Session::close();

	$aJson = array();

	$start = Core_Array::getPost('start', 0, 'int');
	$end = Core_Array::getPost('end', 0, 'int');

	$aJson['events'] = Calendar_Controller::getCalendarEntities(Core_Date::timestamp2sql($start), Core_Date::timestamp2sql($end));
	$aJson['countEvents'] = count($aJson['events']);

	Core::showJson($aJson);
}

// Синхронизация каждые 10 минут
if (!is_null(Core_Array::getPost('updateCaldav')))
{
	Core_Session::close();

	Calendar_Controller::sync($oUser);

	Core::showJson('OK');
}

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module_Abstract::factory($sModule))
	->pageTitle(Core::_('Calendar.title'));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Добавляем крошку на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Calendar.title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

$sContent = '';
$sStatusMessage = '';

ob_start();

$aConfig = Core_Config::instance()->get('calendar_config')
	+ array(
		'entityLimit' => 5,
	);
?>
<script>
$(function () {
	var aScripts = [
		//'moment.min.js', // see bootstrap.php => datetime/moment.js
		'fullcalendar.min.js',
		'locale-all.js'
	];

	$.getMultiContent(aScripts, '/modules/skin/bootstrap/js/fullcalendar/').done(function() {
		// all scripts loaded
		/* initialize the external events
		-----------------------------------------------------------------*/
		$('#external-events .external-event').each(function () {

			// store data so the calendar knows to render an event upon drop
			$(this).data('event', {
				title: $.trim($(this).text()), // use the element's text as the event title
				stick: true // maintain when user navigates (see docs on the renderEvent method)
			});

			// make the event draggable using jQuery UI
			$(this).draggable({
				zIndex: 999,
				revert: true,      // will cause the event to go back to its
				revertDuration: 0  //  original position after the drag
			});
		});
		/* initialize the calendar
		-----------------------------------------------------------------*/
		var date = new Date(),
			d = date.getDate(),
			m = date.getMonth(),
			y = date.getFullYear();

		$('#calendar').fullCalendar({
			locale: '<?php echo Core_i18n::instance()->getLng()?>',
			timezone: 'local',
			height: 'auto',
			// themeSystem: 'bootstrap3',
			customButtons: {
				selectCaldav: {
					/*click: function() {
						$(this).popover({
							container: 'body',
							placement: 'left',
							title: 'Календари',
							html: true,
							// trigger: 'hover',
							content: "<ul id='popup-tab' class='nav nav-tabs bordered'><li class='active'><a href='#pop-1' data-toggle='tab'>Tab1 </a></li><li><a href='#pop-2' data-toggle='tab'>Tab 2</a></li></ul><div id='popup-tab-content' class='tab-content padding-10'><div class='tab-pane fade in active' id='pop-1'><p>Sed posuere consectetur est at lobortis. Aenean eu leo quam. </p></div><div class='tab-pane fade' id='pop-2'><p>Sed posuere consectetur est at lobortis. Aenean eu leo quam. </p></div></div>"
						}).popover('show');
					},*/
					click: function() {
						$('#caldav-dropdown').dropdown().toggle();
					},
					icon: 'fa fa-calendar'
				}
			},
			header: {
				left: 'prev,next today',
				center: 'title',
				right: 'month,agendaWeek,agendaDay,selectCaldav'
			},
			timeFormat: 'H:mm',
			navLinks: true,
			// Интервал (шаг) изменения времени при перетаскивании события
			snapDuration: '00:01:00',
			editable: true,
			droppable: true, // this allows things to be dropped onto the calendar
			/*
			drop: function () {
				// is the "remove after drop" checkbox checked?
				if ($('#drop-remove').is(':checked')) {
					// if so, remove the element from the "Draggable Events" list
					$(this).remove();
				}
			},*/
			dayClick: calendarDayClick,
			events: calendarEvents,
			eventLimit: <?php echo $aConfig['entityLimit']?>,
			eventClick: calendarEventClick,
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

		// ajax sync caldav
		$.updateCaldav();
	});
});
</script>

<div class="row">
	<div class="col-xs-12">
		<div class="widget flat">
			<!--Widget Header-->
			<div class="widget-body">
				<div id='calendar'></div>
			</div>
			<!--Widget Body-->
		</div>
	</div>
</div>

<div class="dropdown" id="caldav-list" >
	<ul id="caldav-dropdown" class="dropdown-menu fc-dropdown-menu">
		<?php
			$oCalendar_Caldavs = Core_Entity::factory('Calendar_Caldav');
			$oCalendar_Caldavs
				->queryBuilder()
				->clearOrderBy()
				->where('calendar_caldavs.active', '=', 1)
				->orderBy('calendar_caldavs.sorting', 'ASC');

			$aCalendar_Caldavs = $oCalendar_Caldavs->findAll(FALSE);

			foreach ($aCalendar_Caldavs as $oCalendar_Caldav)
			{
				$oCalendar_Caldav_User = $oCalendar_Caldav->Calendar_Caldav_Users->getByUser_id($oUser->id);

				$id = !is_null($oCalendar_Caldav_User)
					? $oCalendar_Caldav_User->id
					: 0;

				?>
				<li><a class="caldav-<?php echo htmlspecialchars($oCalendar_Caldav->driver)?>" onclick="$.modalLoad({path: '<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/calendar/caldav/user/index.php')?>', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $id?>]=1&calendar_caldav_id=<?php echo $oCalendar_Caldav->id?>', windowId: 'id_content'}); return false"><i class="<?php echo htmlspecialchars($oCalendar_Caldav->icon)?>"></i><?php echo htmlspecialchars($oCalendar_Caldav->name)?></a></li>
				<?php
			}
		?>
		<li role="separator" class="divider"></li>
		<li><a class="caldav-settings" href="<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/calendar/caldav/index.php')?>" onclick="$.adminLoad({path: '<?php Admin_Form_Controller::correctBackendPath('/{admin}/calendar/caldav/index.php')?>',action: '',operation: '',additionalParams: '',current: '1',sortingFieldId: '1290',sortingDirection: '1',windowId: 'id_content'}); return false"><i class="fa fa-cog"></i><?php echo Core::_('Calendar.settings')?></a></li>
	</ul>
</div>

<script>
$(function () {
	setTimeout( function(){
		$('#caldav-list').detach().insertAfter($('.fc-selectCaldav-button'));
	}, 1000);
});
</script>
<?php
$sContent = ob_get_clean();

ob_start();

$oAdmin_View
	->content($sContent)
	->show();

Calendar_Controller::createContextMenu();

Core_Skin::instance()->answer()
	->module($sModule)
	->ajax(Core_Array::getRequest('_', FALSE))
	->message($sStatusMessage)
	->content(ob_get_clean())
	->title(Core::_('Calendar.title'))
	->execute();