<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Controller_Timesheet
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Controller_Timesheet extends Admin_Form_Controller_View
{
	public function execute()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_View = Admin_View::create($this->_Admin_Form_Controller->Admin_View)
			->pageTitle($oAdmin_Form_Controller->pageTitle)
			->module($oAdmin_Form_Controller->module);

		$aAdminFormControllerChildren = array();

		foreach ($oAdmin_Form_Controller->getChildren() as $oAdmin_Form_Entity)
		{
			if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs
				|| $oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus)
			{
				$oAdmin_View->addChild($oAdmin_Form_Entity);
			}
			else
			{
				$aAdminFormControllerChildren[] = $oAdmin_Form_Entity;
			}
		}

		// При показе формы могут быть добавлены сообщения в message, поэтому message показывается уже после отработки формы
		ob_start();
		foreach ($aAdminFormControllerChildren as $oAdmin_Form_Entity)
		{
			$oAdmin_Form_Entity->execute();
		}

		$this->_showContent();
		$content = ob_get_clean();

		$oAdmin_View
			->content($content)
			->message($oAdmin_Form_Controller->getMessage())
			->show();

		//$oAdmin_Form_Controller->applyEditable();
		$oAdmin_Form_Controller->showSettings();

		return $this;
	}

	protected $_aUserSiteCompanyDepartmentsId = array();

	protected $_iCountDaysInMonth = 0;

	protected $_iCountUserAbsenceTypes = 0;

	/**
	 * Show form content in administration center
	 * @return self
	 */
	protected function _showContent()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

		$oAdmin_Language = $oAdmin_Form_Controller->getAdminLanguage();

		$aAdmin_Form_Fields = $oAdmin_Form->Admin_Form_Fields->findAll();

		$oSortingField = $oAdmin_Form_Controller->getSortingField();

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();

		$oUser = Core_Auth::getCurrentUser();

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		// Компании, связанные с сайтом
		$aCompanies = $oSite->Companies->findAll();

		// Массив идентификторов компаний, связанных с сайтом
		$aCompaniesId = array();
		foreach ($aCompanies as $oCompany)
		{
			$aCompaniesId[] = $oCompany->id;
		}

		// Отделы, в которых сотрудник является руководителем
		$oCompany_Department_Post_Users = $oUser->Company_Department_Post_Users;

		$oCompany_Department_Post_Users
			->queryBuilder()
			->where('company_department_post_users.head', '=', 1)
			->groupBy('company_department_post_users.company_department_id');

		$aCompany_Department_Post_Users = $oCompany_Department_Post_Users->findAll();

		foreach ($aCompany_Department_Post_Users as $oCompany_Department_Post_User)
		{
			in_array($oCompany_Department_Post_User->company_id, $aCompaniesId)
				&& $this->_aUserSiteCompanyDepartmentsId[$oCompany_Department_Post_User->company_id][] = $oCompany_Department_Post_User->company_department_id;
		}

		$iMonth = intval(Core_Array::getGet('month', date('n')));
		$iYear = intval(Core_Array::getGet('year', date('Y')));

		// Число дней в месяце
		$this->_iCountDaysInMonth = date('t', Core_Date::sql2timestamp($iYear . '-' . str_pad($iMonth, 2, '0', STR_PAD_LEFT) . '-01'));

		$this->_iCountUserAbsenceTypes = Core_Entity::factory('User_Absence_Type')->getCount(FALSE);
		?>
		<div class="row">
			<div class="col-xs-12">
				<div class="well">
					<div class="row">
						<div class="col-xs-12">
							<div class="tabbable agregate-user-tab">
									<ul id="agregate-user-info" class="nav nav-pills tabs-flat">
										<li class="nav-item">
											<select name="month">
												<?php
												for ($i = 1; $i <= 12; $i++)
												{
													?><option value="<?php echo $i?>"<?php echo $iMonth == $i ? ' selected="selected"' : ''?>><?php echo Core::_('Core.capitalMonth' . $i)?></option><?php
												}
												?>
											</select>
											<select name="year">
												<?php
												for ($i = 0, $iTmpYear = date('Y'); $i < 15; $i++, $iTmpYear--)
												{
													?><option value="<?php echo $iTmpYear?>" <?php echo $iTmpYear == $iYear ? ' selected="selected"' : ''?>><?php echo $iTmpYear?></option><?php
												}
												?>
											</select>
										</li>
										<?php
										if ($oUser->isHeadOfEmployee($oUser))
										{
										?>
										<li class="nav-item">
											<div class="btn-group margin-left-10 absence-list">
												<a class="btn btn-success" href="javascript:void(0);"><?php echo Core::_('User_Absence.absence_button')?></a>
												<a class="btn btn-palegreen dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
												<ul class="dropdown-menu dropdown-palegreen">
													<li>
														<a class="add-absence-button" data-action="add_absence"  href="javascript:void(0);"><i class="fa fa-fw fa-plus"></i><?php echo Core::_('Admin_Form.add')?></a>
													</li>
													<li>
														<a href="/admin/user/absence/index.php" onclick="<?php echo $oAdmin_Form_Controller->getAdminLoadAjax('/admin/user/absence/index.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')?>"><i class="fa fa-fw fa-list"></i><?php echo Core::_('User_Absence.absence_list')?></a>
													</li>
													<li>
														<a href="/admin/user/absence/type/index.php" onclick="<?php echo $oAdmin_Form_Controller->getAdminLoadAjax('/admin/user/absence/type/index.php', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'list')?>"><i class="fa fa-fw fa-circle"></i><?php echo Core::_('User_Absence_Type.title')?></a>
													</li>
												</ul>
											</div>
										</li>
									<?php
										}

									if (count($this->_aUserSiteCompanyDepartmentsId))
									{
										$sActiveClassName = count($this->_aUserSiteCompanyDepartmentsId) > 1 ? '' : 'active';

										foreach (array_reverse($this->_aUserSiteCompanyDepartmentsId, TRUE) as $iCompanyId => $aCompanyDepartmentsId)
										{
											$oCompany = Core_Entity::factory('Company', $iCompanyId);
											?>
											<li class="<?php echo $sActiveClassName?> pull-right">
												<a data-toggle="tab" href="#company-<?php echo $iCompanyId?>" aria-expanded="true">
													<?php echo htmlspecialchars($oCompany->name)?>
												</a>
											</li>
											<?php
											strlen($sActiveClassName) == 0 && $sActiveClassName = 'active';
										}
									}
									?>
									</ul>
									<?php


								// Показ таблицы
								$this->_showTable($oUser, $iMonth, $iYear);
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
		$(function () {
			// Добавление/редактирование отсутствия сотрудника
			$('#agregate-user-info .add-absence-button, .deals-aggregate-user-info td[id]').on('click', function(){
				var activeTabHref = $('ul#agregate-user-info').find('li.active a').attr('href'),
					split = activeTabHref.split('-'),
					companyId = split[1] ? split[1] : 0,
					absence_id = typeof $(this).attr('id') === "undefined" ? 0 : $(this).attr('id'),
					title = absence_id ? '<?php echo Core::_('User_Absence.edit_absence_title')?>': '<?php echo Core::_('User_Absence.add_absence_title')?>';

				$.modalLoad({title: title, path: '/admin/user/index.php', additionalParams: 'addEditUserAbsence&company_id=' + companyId + '&absence_id=' + absence_id, width: '50%', windowId: 'id_content'});
			});
		});
		</script>
		<?php

		return $this;
	}

	protected function _showHeader($iMonth, $iYear)
	{
		?>
		<tr>
			<th rowspan="2"></th>
			<th rowspan="2"><?php echo Core::_('User_Workday.fio')?></th>
			<th rowspan="2"><?php echo Core::_('User_Workday.days')?></th>
			<th rowspan="2"><?php echo Core::_('User_Workday.hours')?></th>
			<?php
			for ($i = 1; $i <= $this->_iCountDaysInMonth; $i++)
			{
				?>
				<th><span class="month-day"><?php echo $i?></span></th>
				<?php
			}
			?>
			<th colspan="<?php echo $this->_iCountUserAbsenceTypes?>" class="border-bottom-success"><?php echo Core::_('User_Absence.absence')?></th>
		</tr>
		<tr>
			<?php
			for ($i = 1; $i <= $this->_iCountDaysInMonth; $i++)
			{
				$timestamp = Core_Date::sql2timestamp(implode('-', array($iYear, $iMonth < 10 ? '0' . $iMonth : $iMonth, $i < 10 ? '0' . $i : $i)));

				$dayNumber = date('N', $timestamp);
				?>
				<th class="border-top-none<?php echo ($dayNumber == 6 || $dayNumber == 7) ? ' timesheet-holiday' : '' ?>"><?php echo Core::_('Admin_Form.short_day' . $dayNumber)?></th>
				<?php
			}

			$aUser_Absence_Types = Core_Entity::factory('User_Absence_Type')->findAll(FALSE);

			foreach ($aUser_Absence_Types as $oUser_Absence_Type)
			{
				?>
				<th style="background-color: <?php echo Core_Str::hex2lighter($oUser_Absence_Type->color, 0.80)?>; color: <?php echo $oUser_Absence_Type->color?>"><?php echo htmlspecialchars($oUser_Absence_Type->abbr)?></th>
				<?php
			}
			?>
		</tr>
		<?php

		return $this;
	}

	protected function _showTable(User_Model $oUser, $iMonth, $iYear)
	{
		ob_start();
		$this->_showHeader($iMonth, $iYear);
		$header = ob_get_clean();

		if (count($this->_aUserSiteCompanyDepartmentsId))
		{
			?>
			<div class="tab-content tabs-flat">
				<?php
				$sActiveClassName = 'active';

				$colspan = $this->_iCountDaysInMonth + 5 + $this->_iCountUserAbsenceTypes;

				foreach ($this->_aUserSiteCompanyDepartmentsId as $iCompanyId => $aCompanyDepartmentsId)
				{
				?>
				<div id="company-<?php echo $iCompanyId?>" class="tab-pane <?php echo $sActiveClassName?>">
					<div class="table-scrollable">
						<table class="table table-hover deals-aggregate-user-info">
							<thead>
								<?php
								echo $header;
								?>
							</thead>
							<tbody>
							<?php

							$this->_showTimesheetTableRow($oUser, $iMonth, $iYear, 0, $iCompanyId);

							$oCompany = Core_Entity::factory('Company', $iCompanyId);
							$this->_showDepartmentTimesheet($oCompany, $oUser, $aCompanyDepartmentsId, FALSE, NULL, array(), $colspan, $iMonth, $iYear);
							?>
							</tbody>
						</table>
					</div>
				</div>
				<?php
					strlen($sActiveClassName) && $sActiveClassName = '';
				}
				?>
			</div>
			<?php
		}
		else
		{
			?>
			<div class="table-scrollable margin-top-10">
				<table class="table table-hover deals-aggregate-user-info">
					<thead>
						<?php
						echo $header;
						?>
					</thead>
					<tbody>
						<?php $this->_showTimesheetTableRow($oUser, $iMonth, $iYear);?>
					</tbody>
				</table>
			</div>
			<?php
		}

		?>
		<script>
		$(function () {
			$('.timesheet-workday-period[data-toggle="popover"]').popover({
				container: 'body',
				trigger: 'hover',
				title: '<?php echo Core::_('User_Workday.breaks_title')?>'
			});

			// Обработчик изменения месяца/года
			$('[name="month"],[name="year"]').on('change', function(){

				var month = +$('[name="month"]').val(),
					year = +$('[name="year"]').val();

				window.activeTabHref = $('ul#agregate-user-info').find('li.active a').attr('href');

				$.adminLoad({path: '/admin/user/timesheet/index.php', additionalParams: 'month=' + month + '&year=' + year, windowId : 'id_content'});
			});

			// Переключение на активную вкладку после выбора месяца/года
			window.activeTabHref && $('ul#agregate-user-info a[href="' + window.activeTabHref + '"]').click();

			$('[data-action="showAnotherTimeDetailsApprovalForm"]').on('click', function() {
				var iWorkdayId = +$(this).data('workday-id');

				$.modalLoad({title: '<?php echo Core::_('User_Workday.approval_another_time_modal_title')?>', path: '/admin/user/index.php', additionalParams: 'showAnotherTimeDetailsApprovalForm&workdayId=' + iWorkdayId, width: '50%', windowId: 'id_content', onHide: function(){$(".wickedpicker").remove();}});
			});
		})
		</script>
		<?php

		return $this;
	}

	protected function _showTimesheetTableRow(User_Model $oUser, $iMonth = NULL, $iYear = NULL, $iDepartmentId = NULL, $iCompanyId = NULL)
	{
		?>
		<tr>
			<td>
				<img src="<?php echo $oUser->getAvatar()?>" height="30px" class="img-circle" />
			</td>
			<td style="min-width: 200px">
			<?php
			$name = strlen($oUser->getFullName())
				? $oUser->getFullName()
				: $oUser->login;

			$name = htmlspecialchars($name);

			echo "<a href=\"/admin/user/index.php?hostcms[action]=view&amp;hostcms[checked][0][{$oUser->id}]=1\" onclick=\"$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][{$oUser->id}]=1', windowId: 'deal-notes'}); return false\" title=\"{$name}\">{$name}</a>";

			$oCurrentUser = Core_Auth::getCurrentUser();

			if (!is_null($iCompanyId))
			{
				// Для авторизованного сотрудника показываем его должности в данной компании
				if ($oCurrentUser->id == $oUser->id)
				{
					//$aCompany_Posts = $oUser->Company_Posts->findAll();
					$aCompany_Posts = $oUser->getCompanyPostsByCompany($iCompanyId);
				}
				else // Для подчиненных показываем должность в отделе
				{
					$aCompany_Posts = $oUser->getCompanyPostsByDepartment($iDepartmentId);
				}
			}
			else
			{
				$aCompany_Posts = $oUser->Company_Posts->findAll();
			}

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

			if (is_null($iMonth))
			{
				$iMonth = date('n');
			}

			$sMonth = $iMonth < 10 ? '0' . $iMonth : $iMonth;

			if (is_null($iYear))
			{
				$iYear = date('Y');
			}

			// Расчет параметров
			$aUserWorkdayInfo = array();

			$sBeginDate = implode('-', array($iYear, $sMonth, '01'));
			$sEndDate = implode('-', array($iYear, $sMonth, $this->_iCountDaysInMonth));

			$oUser_Workdays = $oUser->User_Workdays;

			$oUser_Workdays->queryBuilder()
				->where('date', '>=', $sBeginDate)
				->where('date', '<=', $sEndDate);

			$aUser_Workdays = $oUser_Workdays->findAll(FALSE);

			$iTotalCountDurationInSeconds = 0;

			foreach($aUser_Workdays as $oUser_Workday)
			{
				// echo ' ' . intval(explode('-', $oUser_Workday->date)[2]);
				// День месяца
				$aExplode = explode('-', $oUser_Workday->date);
				$iDayOfMonth = intval($aExplode[2]);

				if (!isset($aUserWorkdayInfo[$iDayOfMonth]))
				{
					$aUserWorkdayInfo[$iDayOfMonth] = array(
						'id' => 0,
						'date' => '',
						'end' => '00:00',
						'duration' => 0,
						'workhours' => array(),
						'breaks' => array(),
						'sent_request' => 0,
						'approved' => 0
					);
				}

				$iWorkdayBreakDuration = 0;

				$aUserWorkdayInfo[$iDayOfMonth]['id'] = $oUser_Workday->id;
				$aUserWorkdayInfo[$iDayOfMonth]['date'] = $oUser_Workday->date;
				$aUserWorkdayInfo[$iDayOfMonth]['end'] = $oUser_Workday->end;

				$aUser_Workday_Breaks = $oUser_Workday->User_Workday_Breaks->findAll(FALSE);

				// Перерывы
				foreach ($aUser_Workday_Breaks as $oUser_Workday_Break)
				{
					$iWorkdayBreakDuration += strtotime($oUser_Workday_Break->end) - strtotime($oUser_Workday_Break->begin);

					$aUserWorkdayInfo[$iDayOfMonth]['breaks'][] = array(
						'begin' => implode(':', array_slice(explode(':', $oUser_Workday_Break->begin), 0, 2)),
						'end' => implode(':', array_slice(explode(':', $oUser_Workday_Break->end), 0, 2))
					);
				}

				// День не завершен и день не текущий, значит он не был завершен корректно
				if ($oUser_Workday->end != '00:00:00' || $oUser_Workday->date == date('Y-m-d'))
				{
					$sDatetimeWorkdayBegin = $oUser_Workday->date . ' ' . $oUser_Workday->begin;
					$iTimestampWorkdayBegin = Core_Date::sql2timestamp($sDatetimeWorkdayBegin);

					$sDatetimeWorkdayEnd = $oUser_Workday->date . ' ' . $oUser_Workday->end;
					$iTimestampWorkdayEnd = $oUser_Workday->end != '00:00:00' ? Core_Date::sql2timestamp($sDatetimeWorkdayEnd) : time();

					if ($iTimestampWorkdayEnd > $iTimestampWorkdayBegin)
					{
						$aUserWorkdayInfo[$iDayOfMonth]['duration'] += $iTimestampWorkdayEnd - $iTimestampWorkdayBegin - $iWorkdayBreakDuration;
					}

					$iTotalCountDurationInSeconds += $aUserWorkdayInfo[$iDayOfMonth]['duration'];
				}
				else
				{
					$aUserWorkdayInfo[$iDayOfMonth]['duration'] = FALSE;
				}

				$aUserWorkdayInfo[$iDayOfMonth]['workhours'][] = array(
					'begin' => implode(':', array_slice(explode(':', $oUser_Workday->begin), 0, 2)),
					'end' => implode(':', array_slice(explode(':', $oUser_Workday->end), 0, 2))
				);

				$aUserWorkdayInfo[$iDayOfMonth]['approved'] = $oUser_Workday->approved;
				$aUserWorkdayInfo[$iDayOfMonth]['sent_request'] = $oUser_Workday->sent_request;
				$aUserWorkdayInfo[$iDayOfMonth]['user_id'] = $oUser_Workday->user_id;
				//}
			}

			// Общее число рабочих дней
			$iTotalCountWorkdays = count($aUserWorkdayInfo);

			?>
			</td>
			<td><?php echo $iTotalCountWorkdays?></td>
			<td><?php echo floor($iTotalCountDurationInSeconds / 60 / 60) ?></td>

			<?php
			$aAbsenceInfo = $this->_getAbsenceInfo($oUser, $iYear, $sMonth);

			//$oCurrentUser = Core_Auth::getCurrentUser();

			for ($i = 1; $i <= $this->_iCountDaysInMonth; $i++)
			{
				$sDateOfWorkday = implode('-', array($iYear, $sMonth, $i < 10 ? '0' . $i : $i));
				$iDayOfWeekNumber = date('N', Core_Date::sql2timestamp($sDateOfWorkday));

				$style = '';
				$id = '';

				if (isset($aUserWorkdayInfo[$i]['duration']))
				{
					if ($aUserWorkdayInfo[$i]['duration'] === FALSE)
					{
						$sDuration = '?? : ??';
					}
					else
					{
						$iWorkdayDurationInMinutes = $aUserWorkdayInfo[$i]['duration'] / 60;
						$iWorkdayDurationInMinutesMod = $iWorkdayDurationInMinutes % 60;

						$sDuration = floor($iWorkdayDurationInMinutes / 60) . ' : '
							. ($iWorkdayDurationInMinutesMod < 10
								? '0' . $iWorkdayDurationInMinutesMod
								: $iWorkdayDurationInMinutesMod
							);
					}
					// echo $aUserWorkdayInfo[$i]['duration'];

					// Флаг, определяющий показ знака о необходимости завершить рабочий день с другим временем
					$bShowExclamation = $aUserWorkdayInfo[$i]['approved'] == 0
						&& ($aUserWorkdayInfo[$i]['date'] != date('Y-m-d') || !is_null($aUserWorkdayInfo[$i]['sent_request']));

					//$bNotCurrentUser = $aUserWorkdayInfo[$i]['user_id'] != $oCurrentUser->id;
					$bSelfHead = $oCurrentUser->isHeadOfEmployee($oCurrentUser);

					$pointer = $bSelfHead ? 'pointer' : '';

					$sWorkdayDurationInfo = '<span class="semi-bold">' . $sDuration . '</span>'
					. ($bShowExclamation
						? ' <i class="fa fa-exclamation-triangle ' . $pointer . ' danger"'
							. ($bSelfHead ? ' data-action="showAnotherTimeDetailsApprovalForm" data-workday-id="' : '')
							. $aUserWorkdayInfo[$i]['id'] . '"></i>'
						: ''
					);

					$aWorkhoursInfo = array();

					foreach($aUserWorkdayInfo[$i]['workhours'] as $aWorkhours)
					{
						$aWorkhoursInfo[] = $aWorkhours['begin'] . '-' . (!$bShowExclamation && $aWorkhours['end'] != '00:00' ? $aWorkhours['end'] : '??:??');
					}

					$aBreaksInfo = array();

					foreach($aUserWorkdayInfo[$i]['breaks'] as $aBreaks)
					{
						$aBreaksInfo[] = $aBreaks['begin'] . '-' . $aBreaks['end'];
					}

					$sWorkdayDurationInfo .= ' <small class="timesheet-workday-period"' . (count($aBreaksInfo) ? ' data-toggle="popover" data-placement="bottom" data-content="' . implode('<br/>', $aBreaksInfo) . '" data-html="true"' : '' ) . '>' . implode('<br />', $aWorkhoursInfo) . '</small>';
				}
				else
				{
					$aTmp = isset($aAbsenceInfo[$sDateOfWorkday])
						? $aAbsenceInfo[$sDateOfWorkday]
						: array(
							'abbr' => '—',
							'background-color' => '#ffffff',
							'color' => '#262626'
						);

					$sWorkdayDurationInfo = $aTmp['abbr'];

					if ($sWorkdayDurationInfo != '—')
					{
						$style = 'style="background-color: ' . $aTmp['background-color'] . ' !important; color: ' . $aTmp['color'] . ' !important; cursor: pointer;"';

						$id = 'id="' . $aTmp['id'] . '"';
					}
				}
				?>
				<td <?php echo $id?> <?php echo ($iDayOfWeekNumber == 6 || $iDayOfWeekNumber == 7 ? 'class="timesheet-holiday"' : ''), $style?>><?php echo $sWorkdayDurationInfo?></td>
				<?php
			}

			$aUser_Absence_Types = Core_Entity::factory('User_Absence_Type')->findAll(FALSE);
			foreach ($aUser_Absence_Types as $oUser_Absence_Type)
			{
				$iCountValues = isset($this->_aFillAbsenceTypes[$oUser->id][$oUser_Absence_Type->id])
					? intval($this->_aFillAbsenceTypes[$oUser->id][$oUser_Absence_Type->id])
					: '—';
				?>
				<td class="semi-bold"><?php echo $iCountValues?></td>
				<?php
			}
			?>
		</tr>
		<?php

		return $this;
	}

	protected $_aFillAbsenceTypes = array();

	/**
	 * Информация об отсутствии
	*/
	protected function _getAbsenceInfo(User_Model $oUser, $year, $month)
	{
		$year = intval($year);
		$month = intval($month);

		$aReturn = array();

		$sMonthFirstDay = "{$year}-{$month}-01";
		$sMonthLastDay = "{$year}-{$month}-{$this->_iCountDaysInMonth}";

		$this->_aFillAbsenceTypes[$oUser->id] = array();

		$oUser_Absences = $oUser->User_Absences;
		$oUser_Absences->queryBuilder()
			->open()
				->where('start', '>=', $sMonthFirstDay)
				->setOr()
				->where('end', '<=', $sMonthLastDay)
			->close();

		$aUser_Absences = $oUser_Absences->findAll(FALSE);
		foreach ($aUser_Absences as $oUser_Absence)
		{
			$sStart = Core_Date::sql2timestamp($oUser_Absence->start) < Core_Date::sql2timestamp($sMonthFirstDay)
				? $sMonthFirstDay
				: $oUser_Absence->start;

			$sEnd = Core_Date::sql2timestamp($oUser_Absence->end) > Core_Date::sql2timestamp($sMonthLastDay)
				? $sMonthLastDay
				: $oUser_Absence->end;

			$aInterval = $this->_dateInterval($sStart, $sEnd);

			$aTmp = $oUser_Absence->user_absence_type_id
				? array(
					'id' => intval($oUser_Absence->id),
					'abbr' => htmlspecialchars($oUser_Absence->User_Absence_Type->abbr),
					'background-color' => Core_Str::hex2lighter($oUser_Absence->User_Absence_Type->color, 0.80),
					'color' => $oUser_Absence->User_Absence_Type->color
				)
				: array(
					'id' => 0,
					'abbr' => 'NA',
					'background-color' => '#333',
					'color' => '#FFF'
				);

			foreach ($aInterval as $sDate)
			{
				$aReturn[$sDate] = $aTmp;

				isset($this->_aFillAbsenceTypes[$oUser->id][$oUser_Absence->user_absence_type_id])
					? $this->_aFillAbsenceTypes[$oUser->id][$oUser_Absence->user_absence_type_id]++
					: $this->_aFillAbsenceTypes[$oUser->id][$oUser_Absence->user_absence_type_id] = 1;
			}
		}

		return $aReturn;
	}

	/**
	 * Расчет интервала отсутствия
	*/
	protected function _dateInterval($start, $end, $step = '+1 day', $format = 'Y-m-d')
	{
		$aReturn = array();

		$current = strtotime($start);
		$end = strtotime($end);

		while($current <= $end) {
			$aReturn[] = date($format, $current);
			$current = strtotime($step, $current);
		}

		return $aReturn;
	}

	/**
	 * Показ табеля рабочего времени сотрудников отдела и всего дерева его подотделов
	 * @param array $aDepartmentsId массив идентификаторов отделов
	 */
	protected function _showDepartmentTimesheet(Company_Model $oCompany, User_Model $oUser, $aDepartmentsId = array(), $bShow = FALSE, $aDepartment = NULL, $aDepartmentNamePath = array(), $colspan = 0, $iMonth = NULL, $iYear = NULL)
	{
		$bShowTimesheetCurrentUser = FALSE;

		if (is_null($aDepartment))
		{
			// Построение массива, содержащего структуру компании и сотрудников
			$aDepartment = $oCompany->getDepartmentUsersPostsTree(0);

			if (!isset($aDepartment['departments']))
			{
				return;
			}

			$bShowTimesheetCurrentUser = TRUE;
		}

		if (is_array($aDepartment) && count($aDepartment))
		{
			if (isset($aDepartment["department"]))
			{
				$aDepartmentNamePath[] = htmlspecialchars($aDepartment["department"]->name);

				if (($bShow || $bShow = in_array($aDepartment["department"]->id, $aDepartmentsId)) && isset($aDepartment["users"]) && count($aDepartment["users"]))
				{
					?>
					<tr id="deals-aggregate-department-<?php echo $aDepartment["department"]->id?>">
						<td class="deals-aggregate-user-department" colspan="<?php echo $colspan?>">
						<?php
							echo implode(' - ', $aDepartmentNamePath);
						?>
						</td>
					</tr>
					<?php
					foreach ($aDepartment["users"] as $aUserInfo)
					{
						if ($aUserInfo['user']->id != $oUser->id)
						{
							$this->_showTimesheetTableRow($aUserInfo['user'], $iMonth, $iYear, $aDepartment["department"]->id);
						}
					}
				}
			}

			if (isset($aDepartment["departments"]))
			{
				foreach ($aDepartment["departments"] as $aDepartmentInfo)
				{
					$this->_showDepartmentTimesheet($oCompany, $oUser, $aDepartmentsId, $bShow, $aDepartmentInfo, $aDepartmentNamePath, $colspan, $iMonth, $iYear);
				}
			}
		}
	}
}