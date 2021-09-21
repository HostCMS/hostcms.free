<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Workday_Controller_End
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Workday_Controller_End extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		// Показ формы
		if ($operation == 'showModalForm')
		{
			ob_start();
			?>
			<form class="show-another-time-form" method="POST" action="<?php echo $this->_Admin_Form_Controller->getPath()?>">
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<label for="select-another-time"><?php echo Core::_('User_Workday.select_time')?></label>
						<div id="select-another-time" class="input-group margin-top-5">
							<input id="workdayEndTime" name="workdayEndTime" value="<?php echo $currentTime = date('H') . ' : ' . date('i')?>" class="form-control" type="text" style="z-index: 110" />

						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 margin-top-20">
						<label for="reason"><?php echo Core::_('User_Workday.reason')?></label>
						<div class="form-group margin-top-5">
							<textarea id="reason" name="reason" class="form-control" rows="10"></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						<button type="button" class="btn btn-success" onclick="<?php echo $this->_Admin_Form_Controller->getAdminSendForm(array('operation' => 'sendRequest'))?>"><?php echo Core::_('User_Workday.send_request')?></button>
					</div>
				</div>
			</form>

			<script>
			$(function() {
				$('#workdayEndTime').wickedpicker({
					now: '<?php echo $currentTime?>',
					twentyFour: true, //Display 24 hour format, defaults to false
					upArrow: 'wickedpicker__controls__control-up', //The up arrow class selector to use, for custom CSS
					downArrow: 'wickedpicker__controls__control-down', //The down arrow class selector to use, for custom CSS
					close: 'wickedpicker__close', //The close class selector to use, for custom CSS
					hoverState: 'hover-state', //The hover state class to use, for custom CSS
					title: '<?php echo Core::_('User_Worktime.time') ?>', //The Wickedpicker's title,
					showSeconds: false, //Whether or not to show seconds,
					timeSeparator: ' : ', // The string to put in between hours and minutes (and seconds)
					secondsInterval: 1, //Change interval for seconds, defaults to 1,
					minutesInterval: 1, //Change interval for minutes, defaults to 1
					clearable: false //Make the picker's input clearable (has clickable 'x')
				});
			});
			</script>
			<?php

			$this->addContent(ob_get_clean());

			return TRUE;
		}
		elseif ($operation == 'sendRequest')
		{
			ob_start();

			$oUser = Core_Auth::getCurrentUser();

			if (!is_null($oUser))
			{
				// Time 00 : 00
				$time = Core_Array::getPost('workdayEndTime');
				$reason = strip_tags(Core_Array::getPost('reason'));

				if ($time && preg_match('/^\d{2} : \d{2}$/', $time) && strlen($reason))
				{
					$oModule = Core::$modulesList['user'];

					$name = strlen($oUser->getFullName())
						? $oUser->getFullName()
						: $oUser->login;

					$oUser_Workday = $oUser->User_Workdays->getLast();

					if (!is_null($oUser_Workday))
					{
						if ($oUser_Workday->end == '00:00:00')
						{
							$time = str_replace(' ', '', $time);

							$oUser_Workday->end = $time . ':00';
							$oUser_Workday->reason = $reason;
							$oUser_Workday->save();

							$aCompany_Department_Post_Users = $oUser->Company_Department_Post_Users->findAll();
							foreach ($aCompany_Department_Post_Users as $oCompany_Department_Post_User)
							{
								$oCompany_Department = $oCompany_Department_Post_User->Company_Department;

								// Список руководителей
								$aUser_Heads = $oCompany_Department->getHeads();

								foreach ($aUser_Heads as $oUser_Head)
								{
									$oNotification = Core_Entity::factory('Notification');
									$oNotification
										->title(Core::_('User_Workday.notification_head_request_title', strip_tags($name)))
										->description(Core::_('User_Workday.notification_head_request_description', $time, $reason))
										->datetime(Core_Date::timestamp2sql(time()))
										->module_id($oModule->id)
										->type(1) // 1 - напоминание о завершении дня с другим временем
										->entity_id($oUser_Head->id)
										->save();

									// Связываем уведомление с сотрудником
									$oUser_Head->add($oNotification);
								}
							}
						}
					}
					?>
					<div class="row">
						<div class="col-xs-12 another-time-answer">
							<i class="fa fa-check-circle fa-5x palegreen"></i>
							<span><?php echo Core::_('User_Workday.request_success')?></span>
						</div>
					</div>

					<script>
					$(function() {
						setTimeout(function() {
							// Close modal window
							// $('.another-time-answer').parents('.modal').remove();
							bootbox.hideAll();
						}, 1500);

						// Меняем статус дня
						$('li.workday #workdayControl')
							.toggleClass('denied ready')
							.data('status', 0);
					});
					</script>
				<?php
				}
			}

			$this->addContent(ob_get_clean());

			return TRUE;
		}
	}
}