<?php
/**
 * Administration center users.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

// Код формы
$iAdmin_Form_Id = 8;
$sAdminFormAction = '/admin/user/index.php';
$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

if (Core_Auth::logged())
{
	Core_Auth::checkBackendBlockedIp();

	// Контроллер формы
	$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

	// Workdays
	$bStartUserWorkday = !is_null(Core_Array::getPost('startUserWorkday'));
	$bPauseUserWorkday = !is_null(Core_Array::getPost('pauseUserWorkday'));
	$bStopUserWorkday = !is_null(Core_Array::getPost('stopUserWorkday'));

	if ($bStartUserWorkday || $bPauseUserWorkday || $bStopUserWorkday)
	{
		$aJSON = array(
			'error' => 0
		);

		$oUser = Core_Auth::getCurrentUser();

		$currentDay = Core_Date::timestamp2sqldate(time());

		$oUser_Workday = $oUser->User_Workdays->getByDate($currentDay, FALSE);

		// Начало рабочего дня
		if ($bStartUserWorkday)
		{
			if (is_null($oUser_Workday) || $oUser->isUserWorkdayAvailable($currentDay))
			{
				$oUser_Workday = Core_Entity::factory('User_Workday');
				$oUser_Workday->user_id = $oUser->id;
				$oUser_Workday->date = $currentDay;
				$oUser_Workday->begin = date('H:i' . ':00', time());
				$oUser_Workday->save();

				$aJSON['result'] = $oUser->getStatusWorkday($currentDay);
			}
			else
			{
				$aJSON['error'] = 1; // День уже был начат
			}
		}
		// Перерыв
		elseif ($bPauseUserWorkday)
		{
			if (!is_null($oUser_Workday))
			{
				$oUser_Workday_Break = $oUser_Workday->User_Workday_Breaks->getLast(FALSE);

				if (!is_null($oUser_Workday_Break) && $oUser_Workday_Break->end == '00:00:00')
				{
					$oUser_Workday_Break->end = date('H:i' . ':00', time());
					$oUser_Workday_Break->save();
				}
				else
				{
					$oUser_Workday_Break = Core_Entity::factory('User_Workday_Break');
					$oUser_Workday_Break->user_workday_id = $oUser_Workday->id;
					$oUser_Workday_Break->begin = date('H:i' . ':00', time());
					$oUser_Workday_Break->save();
				}

				$aJSON['result'] = $oUser->getStatusWorkday($currentDay);
			}
			else
			{
				$aJSON['error'] = 2; // День не был начат
			}
		}
		// Конец рабочего дня
		elseif ($bStopUserWorkday)
		{
			$workdayStatus = $oUser->getStatusWorkday($currentDay);

			if ($workdayStatus != 1)
			{
				if (!is_null($oUser_Workday))
				{
					if ($oUser_Workday->end == '00:00:00')
					{
						$oUser_Workday->end = date('H:i' . ':00', time());
						$oUser_Workday->notify_day_end = 1;
						$oUser_Workday->approved = 1;
						$oUser_Workday->save();

						$oUser_Workday_Break = $oUser_Workday->User_Workday_Breaks->getLast();

						if (!is_null($oUser_Workday_Break) && $oUser_Workday_Break->end == '00:00:00')
						{
							$oUser_Workday_Break->end = date('H:i' . ':00', time());
							$oUser_Workday_Break->save();
						}
						/*else
						{
							$aJSON['error'] = 3; // Перерывов не было
						}*/

						$aJSON['result'] = $oUser->getStatusWorkday($currentDay);
					}
					else
					{
						$aJSON['error'] = 1; // День уже был закончен
					}
				}
				else
				{
					$aJSON['error'] = 2; // День не был начат
				}
			}
			else
			{
				$aJSON['error'] = 4; // День окончен, но не завершен сотрудником
			}
		}

		Core::showJson($aJSON);
	}

	if (!is_null(Core_Array::getGet('addEditUserAbsence')))
	{
		ob_start();

		$bShowForm = TRUE;

		$formSettings = Core_Array::getPost('hostcms', array())
			+ array(
				'window' => 'id_content',
			);

		$oUser = Core_Auth::getCurrentUser();

		$company_id = intval(Core_Array::getGet('company_id', 0));
		$absence_id = intval(Core_Array::getGet('absence_id', 0));

		$oCompany = Core_Entity::factory('Company')->find($company_id);

		$aSelectResponsibleUsers = array();

		if (!is_null($oCompany->id))
		{
			$oOptgroupCompany = new stdClass();
			$oOptgroupCompany->attributes = array('label' => $oCompany->name, 'class' => 'company');

			$aCompany_Departments = $oUser->getDepartmentsHeadedBy($oCompany);

			$aChildren = array();

			foreach ($aCompany_Departments as $oCompany_Department)
			{
				$iMarginLeft = 15;

				$aDepartmentUsers = $oCompany_Department->Users->findAll();

				if(count($aDepartmentUsers))
				{
					$oOptgroup = new stdClass();
					$oOptgroup->attributes = array(
						'label' => $oCompany_Department->name,
						'class' => 'company-department',
						'style' => "margin-left: {$iMarginLeft}px"
					);
					$oOptgroup->children = array();

					foreach ($aDepartmentUsers as $oDepartmentUser)
					{
						if ($oDepartmentUser->id != $oUser->id)
						{
							$aUserCompanyPosts = array();
							$aObjectUserCompanyPosts = $oDepartmentUser->getCompanyPostsByDepartment($oCompany_Department->id);

							foreach ($aObjectUserCompanyPosts as $oObjectUserCompanyPost)
							{
								$aUserCompanyPosts[] = $oObjectUserCompanyPost->name;
							}
							$sUserCompanyPosts = implode('###', $aUserCompanyPosts);

							$sOptionValue = $oDepartmentUser->getFullName() . '%%%' . $oCompany_Department->name
								. '%%%' . (!empty($sUserCompanyPosts) ? $sUserCompanyPosts : '')
								. '%%%' . $oDepartmentUser->getAvatar() . '?rand=' . rand();

							$oOptgroup->children[$oDepartmentUser->id] = array(
								'value' => $sOptionValue,
								'attr' => array('class' => 'user-name', 'style' => "margin-left: {$iMarginLeft}px")
							);
						}
					}

					$aChildren['company_department_' . $oCompany_Department->id] = $oOptgroup;
				}

				$aChildren += $oCompany->fillDepartmentsAndUsers($oCompany->id, $oCompany_Department->id);
			}

			$oOptgroupCompany->children = $aChildren;

			$aSelectResponsibleUsers[] = $oOptgroupCompany;
		}

		$start_date = $end_date = $reason = $employee_id = $user_absence_type_id = $author = $datetime = '';
		$user_absence_type_name = Core::_('User_Absence_Type.none');
		$user_absence_type_color = '#aebec4';

		if ($absence_id)
		{
			$oUser_Absence = Core_Entity::factory('User_Absence')->find($absence_id);

			if (!is_null($oUser_Absence->id))
			{
				$start_date = Core_Date::sql2date($oUser_Absence->start);
				$end_date = Core_Date::sql2date($oUser_Absence->end);
				$reason = $oUser_Absence->reason;
				$employee_id = $oUser_Absence->employee_id;
				$user_absence_type_id = $oUser_Absence->user_absence_type_id;
				$datetime = Core_Date::sql2datetime($oUser_Absence->datetime);

				if ($oUser_Absence->user_absence_type_id)
				{
					$user_absence_type_name = $oUser_Absence->User_Absence_Type->name;
					$user_absence_type_color = $oUser_Absence->User_Absence_Type->color;
				}

				$oAuthorUser = $oUser_Absence->User;

				ob_start();
				?><div class="contracrot"><div class="user-image"><img class="contracrot-ico" src="<?php echo $oAuthorUser->getAvatar()?>" /></div><div class="user-name" style="margin-top: 8px;"><a class="darkgray" href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][<?php echo $oAuthorUser->id?>]=1" onclick="$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oAuthorUser->id?>]=1', windowId: 'id_content'}); return false"><?php echo htmlspecialchars($oAuthorUser->getFullName())?></a></div></div><?php
				$author = ob_get_clean();

				$oEmployee = Core_Entity::factory('User')->find($employee_id);

				if (!is_null($oEmployee->id) && !$oUser->isHeadOfEmployee($oEmployee))
				{
					$bShowForm = FALSE;
				}
			}
		}

		if ($bShowForm)
		{
			$sCurrentLng = Core_I18n::instance()->getLng();

			?><form class="show-add-absence-form" method="POST" action="<?php echo $sAdminFormAction?>">
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<select id="employee_id" class="form-control" name="employee_id" data-placeholder="<?php echo Core::_('User_Absence.select_user')?>">
							<option></option>
							<?php
							showOptions($aSelectResponsibleUsers);
							?>
						</select>
					</div>
				</div>
				<div class="row margin-top-20">
					<div class="col-xs-12">
						<label for="user_absence_type"><?php echo Core::_('User_Absence.user_absence_type_id')?></label>
						<div id="user_absence_type" class="btn-group" style="display: block;">
							<a data-toggle="dropdown" style="color: <?php echo $user_absence_type_color?>" href="javascript:void(0);" aria-expanded="true">
								<i class="fa fa-circle"></i><?php echo htmlspecialchars($user_absence_type_name)?><i class="fa fa-angle-down icon-separator-left"></i>
							</a>
							<ul class="dropdown-menu form-element">
								<?php
								$aUser_Absence_Types = Core_Entity::factory('User_Absence_Type')->findAll(FALSE);
								foreach ($aUser_Absence_Types as $oUser_Absence_Type)
								{
									$selected = '';

									if ($user_absence_type_id == $oUser_Absence_Type->id)
									{
										$selected = 'selected="selected"';
									}
									?>
									<li id="<?php echo $oUser_Absence_Type->id?>" <?php echo $selected?>>
										<a href="javascript:void(0);" style="color: <?php echo htmlspecialchars($oUser_Absence_Type->color)?>"><i class="fa fa-circle"></i><?php echo htmlspecialchars($oUser_Absence_Type->name)?></a>
									</li>
									<?php
								}
								?>
							</ul>
						</div>
					</div>
				</div>
				<div class="row margin-top-20">
					<div class="col-xs-12 col-sm-3">
						<label for="start"><?php echo Core::_('User_Absence.start')?></label>
						<input name="start" id="start" value="<?php echo $start_date?>" class="form-control input-sm" type="text">
					</div>
					<div class="col-xs-12 col-sm-3">
						<label for="end"><?php echo Core::_('User_Absence.end')?></label>
						<input name="end" id="end" value="<?php echo $end_date?>" class="form-control input-sm" type="text">
					</div>
					<?php
					if (strlen($datetime))
					{
						?>
						<div class="col-xs-12 col-sm-4">
							<label for="datetime"><?php echo Core::_('User_Absence.datetime')?></label>
							<div><i class="fa fa-clock-o" style="margin-right: 5px"></i><span> <?php echo $datetime?></div>
						</div>
						<?php
					}
					?>
				</div>
				<div class="row">
					<div class="col-xs-12 margin-top-20">
						<label for="reason"><?php echo Core::_('User_Absence.reason')?></label>
						<div class="form-group margin-top-5">
							<textarea id="reason" name="reason" class="form-control" rows="5" placeholder="<?php echo Core::_('User_Absence.reason')?>"><?php echo htmlspecialchars($reason)?></textarea>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 col-sm-6">
						<button type="button" class="btn btn-success" onclick="<?php echo $oAdmin_Form_Controller->checked(array(0 => array(0)))->windowId($formSettings['window'])->getAdminSendForm(NULL, 'sendAddAbsenceRequest')?>"><?php echo Core::_('User_Absence.add_absence')?></button>
					</div>
					<?php
					if (strlen($author))
					{
						?><div class="col-xs-12 col-sm-6">
							<?php echo $author?>
						</div><?php
					}
					?>
				</div>

				<input type="hidden" name="sendAddAbsenceRequest" value="1" />
				<input type="hidden" name="user_absence_type_id" value="<?php echo $user_absence_type_id?>" />
				<input type="hidden" name="user_absence_id" value="<?php echo $absence_id?>" />
			</form>
			<script>
			(function($) {
				$(".show-add-absence-form #employee_id").select2({
					placeholder: "",
					allowClear: true,
					templateResult: $.templateResultItemResponsibleEmployees,
					escapeMarkup: function(m) { return m; },
					templateSelection: $.templateSelectionItemResponsibleEmployees,
					language: "<?php echo $sCurrentLng?>",
					width: "100%",
					placeholder: function(){
						$(this).data('placeholder');
					}
				});

				$(".show-add-absence-form #employee_id").val('<?php echo $employee_id?>').trigger('change.select2');

				$(".select2-container").css('width', '100%');

				$('.show-add-absence-form #start').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: 'DD.MM.YYYY', defaultDate: moment()});
				$('.show-add-absence-form #end').datetimepicker({locale: '<?php echo $sCurrentLng?>', format: 'DD.MM.YYYY', defaultDate: moment()});

				$('#user_absence_type ul.dropdown-menu li').on('click', function(){
					$('input[name="user_absence_type_id"]').val($(this).attr('id'));
				});
			})(jQuery);
			</script>
		<?php
		}
		Core::showJson(
			array('error' => '', 'form_html' => ob_get_clean())
		);
	}

	if (!is_null(Core_Array::getPost('sendAddAbsenceRequest')))
	{
		ob_start();

		$oUser = Core_Auth::getCurrentUser();

		if (!is_null($oUser))
		{
			$start = strval(Core_Array::getPost('start'));
			$end = strval(Core_Array::getPost('end'));
			$reason = strval(Core_Array::getPost('reason'));
			$user_absence_type_id = intval(Core_Array::getPost('user_absence_type_id'));
			$employee_id = intval(Core_Array::getPost('employee_id'));
			$user_absence_id = intval(Core_Array::getPost('user_absence_id'));

			$oEmployee = Core_Entity::factory('User')->find($employee_id);

			if (!is_null($oEmployee->id) && $oUser->isHeadOfEmployee($oEmployee))
			{
				$oUser_Absence = $user_absence_id
					? Core_Entity::factory('User_Absence', $user_absence_id)
					: Core_Entity::factory('User_Absence');

				$oUser_Absence->employee_id = $employee_id;
				$oUser_Absence->user_absence_type_id = $user_absence_type_id;
				$oUser_Absence->start = date('Y-m-d', Core_Date::date2timestamp($start));
				$oUser_Absence->end = date('Y-m-d', Core_Date::date2timestamp($end));
				$oUser_Absence->reason = $reason;
				$oUser_Absence->datetime = Core_Date::timestamp2sql(time());
				$oUser_Absence->user_id = $oUser->id;
				$oUser_Absence->save();

				?>
				<div class="row">
					<div class="col-xs-12 another-time-answer">
						<i class="fa fa-check-circle fa-5x palegreen"></i>
						<span><?php echo Core::_('User_Absence.request_success')?></span>
					</div>
				</div>

				<script>
				$(function() {
					 setTimeout(function() {
						// Close modal window
						// $('.another-time-answer').parents('.modal').remove();
						bootbox.hideAll();

						var month = +$('[name="month"]').val(),
							year = +$('[name="year"]').val();

						$.adminLoad({path: '/admin/user/timesheet/index.php', additionalParams: 'month=' + month + '&year=' + year, windowId : 'id_content'});

						// Переключение на активную вкладку
						window.activeTabHref && $('ul#agregate-user-info a[href="' + window.activeTabHref + '"]').click();
					}, 1500);
				});
				</script>
				<?php
			}
		}

		Core::showJson(
			array('error' => '', 'form_html' => ob_get_clean())
		);
	}

	// Показ формы запроса на завершение рабочего дня с другим временем
	if (!is_null(Core_Array::getGet('showAnotherTimeModalForm')))
	{
		ob_start();

		$formSettings = Core_Array::getPost('hostcms', array())
			+ array(
				'window' => 'id_content',
			);

		?>
		<form class="show-another-time-form" method="POST" action="<?php echo $sAdminFormAction?>">
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
						<textarea id="reason" name="reason" class="form-control" rows="10" placeholder="<?php echo Core::_('User_Workday.reason')?>"></textarea>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-12">
					<button type="button" class="btn btn-success" onclick="<?php echo $oAdmin_Form_Controller->checked(array(0 => array(0)))->windowId($formSettings['window'])->getAdminSendForm(NULL, 'sendAnotherTimeRequest')?>"><?php echo Core::_('User_Workday.send_request')?></button>
				</div>
			</div>
			<input type="hidden" name="sendAnotherTimeRequest" value="1" />
		</form>

		<script>
		$(function() {
			$('#workdayEndTime').wickedpicker({
				now: '<?php echo $currentTime?>',
				twentyFour: true,  //Display 24 hour format, defaults to false
				upArrow: 'wickedpicker__controls__control-up',  //The up arrow class selector to use, for custom CSS
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

		Core::showJson(
			array('error' => '', 'form_html' => ob_get_clean())
		);
	}

	if (!is_null(Core_Array::getPost('sendAnotherTimeRequest')))
	{
		ob_start();

		$oUser = Core_Auth::getCurrentUser();

		if (!is_null($oUser))
		{
			// Time 00 : 00
			$time = Core_Array::getPost('workdayEndTime');
			$reason = trim(Core_Array::getPost('reason'));

			if ($time && preg_match('/^\d{2} : \d{2}$/', $time))
			{
				$oModule = Core::$modulesList['user'];

				$name = strlen($oUser->getFullName())
					? $oUser->getFullName()
					: $oUser->login;

				$oUser_Workday = $oUser->User_Workdays->getLast();

				if (!is_null($oUser_Workday) && is_null($oUser_Workday->sent_request) && $oUser_Workday->end == '00:00:00')
				{
					$time = str_replace(' ', '', $time);

					$oUser_Workday->end = $time . ':00';
					$oUser_Workday->reason = $reason;
					$oUser_Workday->sent_request = Core_Date::timestamp2sql(time());
					$oUser_Workday->save();

					$aSent = array();

					$aCompany_Department_Post_Users = $oUser->Company_Department_Post_Users->findAll();
					foreach ($aCompany_Department_Post_Users as $oCompany_Department_Post_User)
					{
						$oCompany_Department = $oCompany_Department_Post_User->Company_Department;

						// Список руководителей
						$aUser_Heads = $oCompany_Department->getHeads();

						foreach ($aUser_Heads as $oUser_Head)
						{
							if (!in_array($oUser_Head->id, $aSent))
							{
								$oNotification = Core_Entity::factory('Notification');
								$oNotification
									->title(Core::_('User_Workday.notification_head_request_title', htmlspecialchars($name)))
									->description(Core::_('User_Workday.notification_head_request_description', $time, $reason))
									->datetime(Core_Date::timestamp2sql(time()))
									->module_id($oModule->id)
									->type(1) // 1 - напоминание о завершении дня с другим временем
									->entity_id($oUser_Head->id)
									->save();

								// Связываем уведомление с сотрудником
								$oUser_Head->add($oNotification);

								$aSent[] = $oUser_Head->id;
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
						$('.another-time-answer').parents('.modal').remove();

						var month = +$('[name="month"]').val(),
							year = +$('[name="year"]').val();

						$.adminLoad({path: '/admin/user/timesheet/index.php', additionalParams: 'month=' + month + '&year=' + year, windowId : 'id_content'});

						// Переключение на активную вкладку
						window.activeTabHref && $('ul#agregate-user-info a[href="' + window.activeTabHref + '"]').click();
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

		Core::showJson(
			array('error' => '', 'form_html' => ob_get_clean())
		);
	}

	// Показ формы утверждения запроса на завершение рабочего дня с другим временем
	if (!is_null(Core_Array::getGet('showAnotherTimeDetailsApprovalForm')))
	{
		$iWorkdayId = intval(Core_Array::getGet('workdayId'));

		$oUser_Workday = Core_Entity::factory('User_Workday')->find($iWorkdayId);

		$bShowForm = TRUE;

		if (!is_null($oUser_Workday->id))
		{
			$formSettings = Core_Array::getPost('hostcms', array())
				+ array(
					'window' => 'id_content',
				);

			ob_start();
			$oEmployee = $oUser_Workday->User;

			$oUser = Core_Auth::getCurrentUser();

			// Сотрудник, отправивший заявку, не является подчиненным авторизованного сотрудника
			if (!$oUser->isHeadOfEmployee($oEmployee))
			{
				$bShowForm = FALSE;
			}
		}
		else
		{
			$bShowForm = FALSE;
		}

		// Проверить право сотрудника утверждать заявку на завершение рабочего дня с другим временем от другого сотрудника
		if ($bShowForm)
		{
			$iWorkdayEndTimeInSeconds = Core_Date::sql2timestamp($oUser_Workday->date . ' ' . $oUser_Workday->end);
		?>
		<form class="show-another-time-form form-horizontal form-bordered" method="POST" action="<?php echo $sAdminFormAction?>">
			<div class="form-group">
				<div class="col-xs-12">
					<div class="user">
						<div class="user_info">
							<img src="<?php echo $oEmployee->getAvatar()?>" height="30px" class="img-circle pull-left margin-right-10" />
							<div class="user_details">
								<?php
								$name = strlen($oEmployee->getFullName())
									? $oEmployee->getFullName()
									: $oEmployee->login;
								?>
								<div class="user_name semi-bold"><?php echo htmlspecialchars($name); ?></div>
								<?php
								$aCompany_Posts = $oEmployee->Company_Posts->findAll();

								if (count($aCompany_Posts))
								{
									?><div class="posts small"><?php

									$aCompanyPostName = array();

									foreach ($aCompany_Posts as $oCompany_Post)
									{
										$aCompanyPostName[] = htmlspecialchars($oCompany_Post->name);
									}

									echo implode(', ', $aCompanyPostName);
									?></div><?php
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group margin-top-20">
				<label for="select-another-time-approve" class="col-sm-4 control-label no-padding-right"><?php echo Core::_('User_Workday.end_time')?></label>
				<div id="select-another-time-approve" class="col-sm-4">
					<input style="z-index: 110" id="workdayEndTimeApproval" name="workdayEndTime" value="<?php echo $currentTime = date('H', $iWorkdayEndTimeInSeconds) . ' : ' . date('i', $iWorkdayEndTimeInSeconds)?>" class="form-control" type="text"  />
				</div>
			</div>
			<div class="form-group margin-top-10">
				<label for="request_send_time" class="col-sm-4 control-label no-padding-right"><?php echo Core::_('User_Workday.request_send_time')?></label>
				<div class="col-sm-4">
					<input id="request_send_time" name="request_send_time" value="<?php echo Core_Date::sql2datetime($oUser_Workday->sent_request);?>" class="form-control" type="text" disabled="disabled"/>
				</div>
			</div>
			<div class="form-group margin-top-10">
				<label for="reason" class="col-sm-4 control-label no-padding-right"><?php echo Core::_('User_Workday.approve_reason')?></label>
				<div id="reason" class="col-sm-4"><?php echo htmlspecialchars($oUser_Workday->reason)?></div>
			</div>
			<div class="form-group">
				<div class="col-xs-12">
					<button type="button" class="btn btn-success" onclick="<?php echo $oAdmin_Form_Controller->checked(array(0 => array(0)))->windowId($formSettings['window'])->getAdminSendForm(NULL, 'sendWorkdayEndTimeApproval')?>"><?php echo Core::_('User_Workday.approve')?></button>
				</div>
			</div>
			<input type="hidden" name="sendWorkdayEndTimeApproval" value="1" />
			<input type="hidden" name="employee_id" value="<?php echo $oEmployee->id?>" />
			<input type="hidden" name="workday_id" value="<?php echo $oUser_Workday->id?>" />
		</form>
		<script>
			$(function() {
				$('#workdayEndTimeApproval').wickedpicker({
						now: '<?php echo $currentTime?>',
						twentyFour: true,  //Display 24 hour format, defaults to false
						upArrow: 'wickedpicker__controls__control-up',  //The up arrow class selector to use, for custom CSS
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
		}
		else // Не существует рабочего дня с переданным id
		{
		?>
			<script>
				// Close modal window
				$('#<?php echo $formSettings['window']?>').parents('.modal').remove();
			</script>
		<?php
		}
		Core::showJson(
			array('error' => '', 'form_html' => ob_get_clean())
		);
	}

	if (!is_null(Core_Array::getPost('sendWorkdayEndTimeApproval')))
	{
		$formSettings = Core_Array::getPost('hostcms', array())
			+ array(
				'window' => 'id_content',
			);

		$employee_id = intval(Core_Array::getPost('employee_id'));
		$workday_id = intval(Core_Array::getPost('workday_id'));

		$oUser = Core_Auth::getCurrentUser();

		$oEmployee = Core_Entity::factory('User', $employee_id);

		// Сотрудник, отправивший заявку, является подчиненным авторизованного сотрудника
		if ($oUser->isHeadOfEmployee($oEmployee))
		{
			$oUser_Workday = Core_Entity::factory('User_Workday')->getById($workday_id);
			if (!is_null($oUser_Workday) && $oUser_Workday->user_id == $oEmployee->id)
			{
				$oModule = Core::$modulesList['user'];

				$time = Core_Array::getPost('workdayEndTime');

				if ($time && preg_match('/^\d{2} : \d{2}$/', $time))
				{
					$time = str_replace(' ', '', $time);

					$oUser_Workday->approved = 1;
					$oUser_Workday->end = $time . ':00';
					$oUser_Workday->save();

					$oNotification = Core_Entity::factory('Notification');
					$oNotification
						->title(Core::_('User_Workday.notification_request_approve_title', Core_Date::sql2date($oUser_Workday->date)))
						->description(Core::_('User_Workday.notification_request_approve_description', $time))
						->datetime(Core_Date::timestamp2sql(time()))
						->module_id($oModule->id)
						->type(1) // 1 - напоминание о подтверждении дня
						->entity_id($oEmployee->id)
						->save();

					// Связываем уведомление с сотрудником
					$oEmployee->add($oNotification);
				}
			}
		}

		?>
		<div class="row">
			<div class="col-xs-12 another-time-answer">
				<i class="fa fa-check-circle fa-5x palegreen"></i>
				<span><?php echo Core::_('User_Workday.approve_success')?></span>
			</div>
		</div>
		<script>
		$(function() {
			 setTimeout(function() {
				// Close modal window
				$('#<?php echo $formSettings['window']?>').parents('.modal').remove();

				var month = +$('[name="month"]').val(),
					year = +$('[name="year"]').val();

				$.adminLoad({path: '/admin/user/timesheet/index.php', additionalParams: 'month=' + month + '&year=' + year, windowId : 'id_content'});

				// Переключение на активную вкладку
				window.activeTabHref && $('ul#agregate-user-info a[href="' + window.activeTabHref + '"]').click();
			}, 1500);

			// Меняем статус дня
			$('li.workday #workdayControl')
				.toggleClass('denied ready')
				.data('status', 0);
		});
		</script>
		<?php

		Core::showJson(
			array('error' => '', 'form_html' => ob_get_clean())
		);
	}

	// Смена бэкграунда
	if (!is_null(Core_Array::getPost('wallpaper-id')))
	{
		$oUser = Core_Auth::getCurrentUser();

		if (!is_null($oUser))
		{
			$oModule = Core_Entity::factory('Module')->getByPath('user');

			if (!is_null($oModule))
			{
				$type = 95;
				$oUser_Settings = $oUser->User_Settings;
				$oUser_Settings->queryBuilder()
					->where('user_settings.module_id', '=', $oModule->id)
					->where('user_settings.type', '=', $type)
					->where('user_settings.active', '=', 1)
					->limit(1);

				$aUser_Settings = $oUser_Settings->findAll(FALSE);

				if (isset($aUser_Settings[0]))
				{
					$oUser_Setting = $aUser_Settings[0];
				}
				else
				{
					$oUser_Setting = Core_Entity::factory('User_Setting');
					$oUser_Setting->module_id = $oModule->id;
					$oUser_Setting->type = $type;
					$oUser_Setting->active = 1;
				}

				$oUser_Setting->entity_id = intval(Core_Array::getPost('wallpaper-id'));
				$oUser_Setting->save();
			}
		}

		Core::showJson('OK');
	}

	// Bookmarks
	if (!is_null(Core_Array::getPost('add_bookmark')) && Core_Array::getPost('name'))
	{
		$oUser = Core_Auth::getCurrentUser();

		if (!is_null($oUser))
		{
			$oUser_Bookmark = Core_Entity::factory('User_Bookmark');
			$oUser_Bookmark->module_id = intval(Core_Array::getPost('module_id', 0));
			$oUser_Bookmark->name = strval(Core_Array::getPost('name'));
			$oUser_Bookmark->path = strval(Core_Array::getPost('path'));
			$oUser_Bookmark->user_id = $oUser->id;
			$oUser_Bookmark->save();
		}

		Core::showJson('OK');
	}

	if (!is_null(Core_Array::getPost('remove_bookmark')) && Core_Array::getPost('bookmark_id'))
	{
		$oUser = Core_Auth::getCurrentUser();

		$bookmark_id = intval(Core_Array::getPost('bookmark_id'));

		$oUser_Bookmark = $oUser->User_Bookmarks->getById($bookmark_id);

		if (!is_null($oUser_Bookmark))
		{
			$oUser_Bookmark->markDeleted();
			$message = 'OK';
		}
		else
		{
			$message = 'Error';
		}

		Core::showJson($message);
	}

	// NavSideBar
	if (!is_null(Core_Array::getPost('loadNavSidebarMenu')))
	{
		ob_start();

		Core_Auth::setCurrentSite();

		Core_Skin::instance()->navSidebarMenu();

		$oAdmin_Answer = Core_Skin::instance()->answer();
		$oAdmin_Answer
			->content(ob_get_clean())
			->ajax(TRUE)
			->execute();

		exit();
	}

	if (!is_null(Core_Array::getPost('loadWallpaper')))
	{
		$aJSON = array();

		$wallpaper_id = Core_Array::getPost('loadWallpaper', 0, 'int');

		if ($wallpaper_id)
		{
			$oUser_Wallpaper = Core_Entity::factory('User_Wallpaper')->getById($wallpaper_id);

			if (!is_null($oUser_Wallpaper))
			{
				$aJSON = array(
					'id' => $oUser_Wallpaper->id,
					'original_path' => $oUser_Wallpaper->image_large != '' ? htmlspecialchars($oUser_Wallpaper->getLargeImageFileHref()) : '',
					'src' => $oUser_Wallpaper->image_large != '' ? htmlspecialchars($oUser_Wallpaper->getSmallImageFileHref()) : '',
					'color' => htmlspecialchars($oUser_Wallpaper->color)
				);
			}
		}

		Core::showJson($aJSON);
	}

	// Avatar
	if (!is_null(Core_Array::getGet('loadUserAvatar')))
	{
		$id = intval(Core_Array::getGet('loadUserAvatar'));
		$oUser = Core_Entity::factory('User')->getById($id);
		if ($oUser)
		{
			$name = $oUser->name != '' && $oUser->surname != ''
				? $oUser->name . ' ' . $oUser->surname
				: $oUser->login;
		}
		else
		{
			Core_Message::show('Wrong ID', 'error');
			die();
		}

		// Get initials
		$initials = Core_Str::getInitials($name);

		$bgColor = Core_Str::createColor($id);

		Core_Image::avatar($initials, $bgColor, $width = 130, $height = 130);
	}

	if (!is_null(Core_Array::getPost('setNotificationsRead')))
	{
		$aJSON = array('success' => 0);

		$oUser = Core_Auth::getCurrentUser();

		if (!is_null($oUser))
		{
			$oCore_QueryBuilder = Core_QueryBuilder::update('notification_users')
				->set('read', 1)
				->where('user_id', '=', $oUser->id)
				->where('read', '=', 0)
				->execute();

			$aJSON['success'] = 1;
		}

		Core::showJson($aJSON);
	}

	if (!is_null(Core_Array::getPost('showPopover')))
	{
		$aJSON = array(
			'html' => ''
		);

		$user_id = Core_Array::getPost('user_id', 0, 'int');

		$oUser = Core_Entity::factory('User')->getById($user_id);

		if (!is_null($oUser))
		{
			$aJSON['html'] = $oUser->getProfilePopupBlock();
		}

		Core::showJson($aJSON);
	}
}

Core_Auth::authorization($sModule = 'user');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);

$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('User.ua_show_users_title'))
	->pageTitle(Core::_('User.ua_show_users_title'));

if (!is_null(Core_Array::getPost('generate-password')))
{
	Core::showJson(
		array(
			'password' => Core_Password::get()
		)
	);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

$sUserSiteChoosePath = '/admin/user/site/index.php';

$sActionAdditionalParam = '&mode=action';

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Admin_Form.add'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('User.wallpaper'))
		->icon('fa fa-image')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/user/wallpaper/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/user/wallpaper/index.php', NULL, NULL, '')
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('User.session'))
		->icon('fa fa-history')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/user/session/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/user/session/index.php', NULL, NULL, '')
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs
->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('User.ua_show_users_title'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath())
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath())
	)
);

// Добавляем все хлебные крошки контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oUser_Controller_Edit = Admin_Form_Action_Controller::factory(
		'User_Controller_Edit', $oAdmin_Form_Action
	);

	$oUser_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUser_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oUserControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserControllerApply);
}

// Действие "Просмотр"
$oAdminFormActionView = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('view');

if ($oAdminFormActionView && $oAdmin_Form_Controller->getAction() == 'view')
{
	$oUserControllerView = Admin_Form_Action_Controller::factory(
		'User_Controller_View', $oAdminFormActionView
	);

	$oUserControllerView
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserControllerView);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Удалить файл изображения"
$oAdminFormActionDeleteImageFile = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteImageFile');

if ($oAdminFormActionDeleteImageFile && $oAdmin_Form_Controller->getAction() == 'deleteImageFile')
{
	$oUserControllerDeleteImageFile = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAdminFormActionDeleteImageFile
	);

	$oUserControllerDeleteImageFile
		->methodName('deleteImageFile')
		->divId(array('preview_large_image', 'delete_large_image'));

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oUserControllerDeleteImageFile);
}

// Действие "Завершить с другим временем"
/*
$oAnotherTimeShowModal = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('showAnotherTimeModal');

if ($oAnotherTimeShowModal && $oAdmin_Form_Controller->getAction() == 'showAnotherTimeModal')
{
	$oUser_Workday_Controller_End = Admin_Form_Action_Controller::factory(
		'User_Workday_Controller_End', $oAnotherTimeShowModal
	);

	// Добавляем контроллер добавления перехода контроллеру формы
	$oAdmin_Form_Controller->addAction($oUser_Workday_Controller_End);
}*/

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('User')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addCondition(array('where' => array('user_id', '=', $oUser->id)));

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array(
		'select' => array('users.*', array(Core_QueryBuilder::expression('CONCAT_WS(" ", `surname`, `name`, `patronymic`)'), 'fullname'))
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Показ формы
$oAdmin_Form_Controller->execute();

/**
 * Show options.
 */
function showOptions($aOptions)
{
	foreach ($aOptions as $key => $aValue)
	{
		if (is_object($aValue))
		{
			showOptgroup($aValue);
		}
		else
		{
			if (is_array($aValue))
			{
				$value = Core_Array::get($aValue, 'value');
				$attr = Core_Array::get($aValue, 'attr', array());
			}
			else
			{
				$value = $aValue;
				$attr = array();
			}

			/*(!is_array($this->value) && $this->value == $key
				|| is_array($this->value) && in_array($key, $this->value))
			&& $attr['selected'] = 'selected';*/

			showOption($key, $value, $attr);
		}
	}
}

/**
 * Show optgroup.
 */
function showOptgroup(stdClass $oOptgroup)
{
	?><optgroup<?php
	if (isset($oOptgroup->attributes) && is_array($oOptgroup->attributes))
	{
		foreach ($oOptgroup->attributes as $attrKey => $attrValue)
		{
			echo ' ', $attrKey, '=', '"', htmlspecialchars($attrValue, ENT_COMPAT, 'UTF-8'), '"';
		}
	}
	?>><?php
	if (isset($oOptgroup->children) && is_array($oOptgroup->children))
	{
		showOptions($oOptgroup->children);
	}
	?></optgroup><?php
}

/**
 * Show option
 * @param string $key key
 * @param string $value value
 * @param array $aAttr attributes
 */
function showOption($key, $value, array $aAttr = array())
{
	?><option value="<?php echo htmlspecialchars($key)?>"<?php
	foreach ($aAttr as $attrKey => $attrValue)
	{
		echo ' ', $attrKey, '=', '"', htmlspecialchars($attrValue, ENT_COMPAT, 'UTF-8'), '"';
	}
	?>><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8')?></option><?php
}
