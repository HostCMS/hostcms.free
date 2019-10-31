<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event. Backend's Index Pages and Widget.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Module_Event_Module extends Event_Module
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
	protected $_moduleName = 'event';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->_adminPages = array(
			0 => array('title' => Core::_('Event.widget_title'))
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
		$this->_path = "/admin/index.php?ajaxWidgetLoad&moduleId={$oModule->id}&type={$type}";

		Core_Session::close();

		$oUser = Core_Auth::getCurrentUser();

		switch ($type)
		{
			case 1: // Завершение дела
				if ($ajax)
				{
					$iEventId = intval(Core_Array::getPost('eventId'));

					$aJson = array();

					$oEvent = Core_Entity::factory('Event', $iEventId);

					if ($oEvent->Event_Users->getCountByUser_id($oUser->id) > 0)
					{
						$oEvent
							->completed(1)
							->save()
							->changeCompletedSendNotification();

						$aJson['eventId'] = $iEventId;
					}
					Core::showJson($aJson);
				}
			break;
			case 2: // Изменение статуса дела
				if ($ajax)
				{
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
					$aJson = array();

					$sEventName = Core_Array::getPost('event_name');

					$aJson['event_name'] = $sEventName;

					$oEvent = Core_Entity::factory('Event');
					$oEvent->name = $sEventName;

					// Тип дел по умолчанию
					$oDefaultEventType = Core_Entity::factory('Event_Type')->getDefault();

					if (!is_null($oDefaultEventType))
					{
						$oEvent->event_type_id = $oDefaultEventType->id;
					}

					$iCurrentTimestamp = time();

					$oEvent->datetime = Core_Date::timestamp2sql($iCurrentTimestamp);
					$oEvent->start = Core_Date::timestamp2sql($iCurrentTimestamp);
					$oEvent->save();

					$oUser = Core_Auth::getCurrentUser();

					$oEventUser = Core_Entity::factory('Event_User')
						->user_id($oUser->id)
						->creator(1);

					$oEvent->add($oEventUser);

					Core::showJson($aJson);
				}
			break;
			case 4:
				$aJson = array();

				$iRequestUserId = intval(Core_Array::getPost('currentUserId'));

				if (!is_null($oUser) && $oUser->id == $iRequestUserId)
				{
					$aJson['userId'] = $oUser->id;
					$aJson['newEvents'] = array();

					$dateTime = date('Y-m-d');

					$aEvents = $oUser->Events->getToday(FALSE);

					foreach ($aEvents as $oEvent)
					{
						$aEvent = array(
							'id' => $oEvent->id,
							'name' => $oEvent->name,
							'start' => Event_Controller::getDateTime($oEvent->start),
							'finish' => Event_Controller::getDateTime($oEvent->finish),
							'href' => "/admin/event/index.php?hostcms[action]=edit&hostcms[operation]=&hostcms[current]=1&hostcms[checked][0][{$oEvent->id}]=1",
							'onclick' => "$(this).parents('li.open').click(); $.adminLoad({path: '/admin/event/index.php?hostcms[action]=edit&amp;hostcms[operation]=&amp;hostcms[current]=1&amp;hostcms[checked][0][{$oEvent->id}]=1'}); return false",
							'icon' => $oEvent->Event_Type->icon,
							'background-color' => $oEvent->Event_Type->color
						);

						$aJson['newEvents'][] = $aEvent;
					}
				}

				Core::showJson($aJson);
			break;

			default:
				if ($ajax)
				{
					$this->_content();
				}
				else
				{

				?><div class="col-xs-12 col-sm-6" id="eventsAdminPage" data-hostcmsurl="<?php echo htmlspecialchars($this->_path)?>">
					<script>
					$.widgetLoad({ path: '<?php echo $this->_path?>', context: $('#eventsAdminPage') });
					$.eventsWidgetPrepare();
					</script>
				</div>
				<?php
				}
		}

		return TRUE;
	}

	protected function _content()
	{
		$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);

		?>
		<div class="widget events">
			<div class="widget-header bordered-bottom bordered-themeprimary">
				<i class="widget-icon fa fa-tasks themeprimary"></i>
				<span class="widget-caption themeprimary"><?php echo Core::_('Event.events_title')?></span>
				<div class="widget-buttons">
					<a data-toggle="maximize">
						<i class="fa fa-expand gray"></i>
					</a>
					<a data-toggle="upload" data-module-id="<?php echo $oModule->id?>">
						<i class="fa fa-refresh gray"></i>
					</a>
					<a href="#" data-toggle="toggle-actions">
						<i class="fa fa-plus darkgray" title="<?php echo Core::_('Event.titleAddEvent');?>"></i>
						<i class="fa fa-search darkgray hidden" title="<?php echo Core::_('Event.titleSearch');?>"></i>
					</a>
				</div>
			</div><!--Widget Header-->

			<div class="widget-body no-padding">
				<div class="task-container">
					<div class="task-search">
						<span class="search-event input-icon">
							<input type="text" class="form-control" placeholder="<?php echo Core::_('Event.placeholderSearch');?>">
							<i class="fa fa-search gray"></i>
						</span>

						<span class="add-event hidden">
							<form>
								<div class="input-group input-icon">
									<input type="text" name="event_name" class="form-control" placeholder="<?php echo Core::_('Event.placeholderEventName');?>">
									<i class="fa fa-plus gray"></i>
									<span id="sendForm" class="input-group-addon bg-azure bordered-azure" onclick="$(this).parents('form').submit()">
										<i class="fa fa-check no-margin"></i>
									</span>
								</div>
							</form>
						</span>
					</div>
					<div class="tasks-list-container">
						<ul class="tasks-list">
						<?php
						$oUser = Core_Auth::getCurrentUser();

						// Дела пользователя (сотрудника)
						$oEvents = $oUser->Events;

						$oEvents->queryBuilder()
							->where('completed', '=', 0)
							->orderBy('start', 'DESC')
							->orderBy('important', 'DESC')
							->limit(15);

						$aEvents = $oEvents->findAll();

						?>
						<li id="event-0" class="task-item empty-item gray<?php echo count($aEvents) ? ' hidden' : '' ?>">
							<?php echo Core::_('Event.widget_empty') ?>
						</li>
						<?php

						if (count($aEvents))
						{
							// Список статусов дел
							$aEvent_Statuses = Core_Entity::factory('Event_Status', 0)->findAll();

							foreach ($aEvents as $oEvent)
							{
								$oEvent_User = $oEvent->Event_Users->getByUser_id($oUser->id);
								?>
								<li id="event-<?php echo $oEvent_User->event_id?>" class="task-item">
									<div class="task-check">
										<i class="fa <?php echo $oEvent->completed ? 'fa-check-square-o success' : 'fa-square-o'?> fa-lg" title="<?php echo Core::_('Event.titleCompleted')?>"></i>
									</div>
									<div class="task-state">
										<?php
										$aColorEventTypes = array('success', 'primary', 'azure', 'magenta', 'sky');

										$oEvent->event_type_id && $oEvent->showType();

										if ($oEvent->deadline())
										{
											?><div class="btn-group"><i class="fa fa-exclamation-circle red margin-right-10"></i></div><?php
										}
										// Список статусов дел
										$aEventStatuses = Core_Entity::factory('Event_Status')->findAll();

										$aMasEventStatuses = array(
											array(
												'value' => Core::_('Event.notStatus'),
												'color' => '#aebec4'
											)
										);

										foreach ($aEventStatuses as $oEventStatus)
										{
											$aMasEventStatuses[$oEventStatus->id] = array(
												'value' => $oEventStatus->name,
												'color' => $oEventStatus->color
											);
										}

										$iAdmin_Form_Id = 220;
										$sAdminFormAction = '/admin/event/index.php';

										$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);
										$oAdmin_Form_Action = $oAdmin_Form
											->Admin_Form_Actions
											->getByName('changeStatus');

										if ($oAdmin_Form_Action)
										{
											$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
											$oAdmin_Form_Controller
												->path($sAdminFormAction)
												->window('id_content')
												->checked(array(0 => array(0)));

											$path = $oAdmin_Form_Controller->getPath();

											$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

											$oCore_Html_Entity_Dropdownlist
												->value($oEvent->event_status_id)
												->options($aMasEventStatuses)
												// ->onchange('$.eventsWidgetChangeStatus(this)')
												->onchange("$.adminLoad({path: '{$path}', additionalParams: 'hostcms[checked][0][{$oEvent->id}]=0&eventStatusId=' + $(this).find('li[selected]').prop('id'), action: 'changeStatus', windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
												->execute();
										}
										?>
									</div>
									<div class="task-time"><i class="fa fa-clock-o"></i> <?php echo Core_Date::time2string(time() - Core_Date::sql2timestamp($oEvent->datetime)) ?></div>
									<div class="task-body">
										<?php
										$deadlineIcon = $oEvent->deadline()
											? '<i class="fa fa-clock-o event-title-deadline"></i>'
											: '';
										?>
										<span class="task-title editable" id="apply_check_0_<?php echo $oEvent->id ?>_fv_1226"><?php echo $deadlineIcon, htmlspecialchars($oEvent->name);?></span>
										<?php
											$isCreator = is_null($oEvent_User) ? 0 : $oEvent_User->creator;
											// Текущий пользователь - не создатель дела
											if (!$isCreator)
											{
												$oEvent_Creator = $oEvent->Event_Users->getByCreator(1);

												if (!is_null($oEvent_Creator))
												{
													// Создатель дела
													$oUser_Creator = $oEvent_Creator->User;
												?>
												<div class="<?php echo $oUser_Creator->isOnline() ? 'online margin-left-5 margin-right-5' : 'offline margin-left-5 margin-right-5'; ?>"></div><span class="task-creator"><a href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][<?php echo $oUser_Creator->id?>]=1" onclick="$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oUser_Creator->id?>]=1', windowId: 'id_content'}); return false"><?php echo htmlspecialchars($oUser_Creator->getFullName())?></a></span>
												<?php
												}
											}
										?>
										<span class="task-description" style="display: block; color: #999; font-size: 11px; line-height: 17px"><?php echo htmlspecialchars($oEvent->description);?></span>
									</div>
								</li>
								<?php
							}
						}
						?>
						</ul>
					</div>
				</div>
			</div><!--Widget Body-->
			<script>
				var obj = $('#eventsAdminPage .tasks-list')
					.slimscroll({
						//height: '500px',
						height: 'auto',
						color: 'rgba(0,0,0,0.3)',
						size: '5px',
						wheelStep: 5
					});

				//$('#eventsAdminPage .tasks-list').on('slimscroll', function(){console.log('$(this).children().length = ' + ($(this).children().length - 1), '$(this)[0].scrollHeight = ' + $(this)[0].scrollHeight, '$(this).outerHeight() = ' + $(this).outerHeight())});

				var slimScrollBarTop = $('#eventsAdminPage').data('slimScrollBarTop');

				if (slimScrollBarTop)
				{
					$('#eventsAdminPage .tasks-list').slimscroll({scrollTo: slimScrollBarTop});
				}

				<?php
				$oModule = Core_Entity::factory('Module')->getByPath($this->_moduleName);
				?>
				$('#eventsAdminPage').data({'moduleId': <?php echo $oModule->id?>});

				(function($){
					$('#eventsAdminPage .editable').editable({windowId: '#eventsAdminPage', path: '/admin/event/index.php'});
				})(jQuery);
			</script>
		</div><!--Widget -->
		<?php

		return $this;
	}
}