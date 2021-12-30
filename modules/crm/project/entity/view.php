<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Entity_View
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Project_Entity_View extends Admin_Form_Controller_View
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
		?>
		<div class="table-toolbar">
			<?php $this->_Admin_Form_Controller->showFormMenus()?>
			<div class="table-toolbar-right pull-right">
				<?php $this->_Admin_Form_Controller->pageSelector()?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
		foreach ($aAdminFormControllerChildren as $oAdmin_Form_Entity)
		{
			$oAdmin_Form_Entity->execute();
		}

		$this->_showContent();
		?>
		<div class="row margin-bottom-20">
			<div class="col-xs-12 col-sm-6 col-md-8 text-align-left timeline-board">
				<?php $this->_Admin_Form_Controller->pageNavigation()?>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-4 text-align-right">
				<?php $this->_Admin_Form_Controller->pageSelector()?>
			</div>
		</div>
		<?php
		$content = ob_get_clean();

		$oAdmin_View
			->content($content)
			->message($oAdmin_Form_Controller->getMessage())
			->show();

		//$oAdmin_Form_Controller->applyEditable();
		$oAdmin_Form_Controller->showSettings();

		return $this;
	}

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

		$oUser = Core_Auth::getCurrentUser();

		if (is_null($oUser))
		{
			return FALSE;
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$oAdmin_Form_Controller->setDatasetLimits();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');

		$aEntities = $aDatasets[0]->load();
		?>
			<ul class="timeline timeline-left timeline-crm">
				<?php
				foreach ($aEntities as $oEntity)
				{
					switch ($oEntity->type)
					{
						// Events
						case 0:
							$badge = 'fa fa-tasks';

							$color = 'palegreen';

							$oEvent = Core_Entity::factory('Event', $oEntity->id);

							// $title = htmlspecialchars($oEvent->name);
							// $title = '';

							ob_start();

							// $path = $oAdmin_Form_Controller->getPath();

							$oEventCreator = $oEvent->getCreator();

							// Временая метка создания дела
							$iEventCreationTimestamp = Core_Date::sql2timestamp($oEvent->datetime);

							// Сотрудник - создатель дела
							$userIsEventCreator = !is_null($oEventCreator) && $oEventCreator->id == $oUser->id;

							$oEvent_Type = $oEvent->Event_Type;

							$oEvent->event_type_id && $oEvent->showType();

							// Менять статус дела может только его создатель
							if ($userIsEventCreator)
							{
								// Список статусов дел
								$aEvent_Statuses = Core_Entity::factory('Event_Status')->findAll();

								$aMasEventStatuses = array(array('value' => Core::_('Event.notStatus'), 'color' => '#aebec4'));

								foreach ($aEvent_Statuses as $oEvent_Status)
								{
									$aMasEventStatuses[$oEvent_Status->id] = array('value' => $oEvent_Status->name, 'color' => $oEvent_Status->color);
								}

								$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

								$oCore_Html_Entity_Dropdownlist
									->value($oEvent->event_status_id)
									->options($aMasEventStatuses)
									//->class('btn-group event-status')
									->onchange("$.adminLoad({path: '/admin/event/index.php', additionalParams: 'hostcms[checked][0][{$oEvent->id}]=0&eventStatusId=' + $(this).find('li[selected]').prop('id'), action: 'changeStatus', windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
									->execute();
							}
							else
							{
								if ($oEvent->event_status_id)
								{
									$oEvent_Status = Core_Entity::factory('Event_Status', $oEvent->event_status_id);

									$sEventStatusName = htmlspecialchars($oEvent_Status->name);
									$sEventStatusColor = htmlspecialchars($oEvent_Status->color);
								}
								else
								{
									$sEventStatusName = Core::_('Event.notStatus');
									$sEventStatusColor = '#aebec4';
								}
								?>
								<div class="event-status">
									<i class="fa fa-circle" style="margin-right: 5px; color: <?php echo $sEventStatusColor?>"></i><span style="color: <?php echo $sEventStatusColor?>"><?php echo $sEventStatusName?></span>
								</div>
								<?php
							}

							$nameColorClass = $oEvent->deadline()
								? 'event-title-deadline'
								: '';

							$deadlineIcon = $oEvent->deadline()
								? '<i class="fa fa-clock-o event-title-deadline"></i>'
								: '';

							?>
							<div class="event-title <?php echo $nameColorClass?>"><?php echo $deadlineIcon, htmlspecialchars($oEvent->name)?></div>

							<div class="event-description"><?php echo Core_Str::cutSentences(strip_tags($oEvent->description), 250)?></div>

							<div class="crm-date"><?php
							if ($oEvent->all_day)
							{
								echo Event_Controller::getDate($oEvent->start);
							}
							else
							{
								if (!is_null($oEvent->start) && $oEvent->start != '0000-00-00 00:00:00')
								{
									echo Event_Controller::getDateTime($oEvent->start);
								}

								if (!is_null($oEvent->start) && $oEvent->start != '0000-00-00 00:00:00'
									&& !is_null($oEvent->deadline) && $oEvent->deadline != '0000-00-00 00:00:00'
								)
								{
									echo ' — ';
								}

								if (!is_null($oEvent->deadline) && $oEvent->deadline != '0000-00-00 00:00:00')
								{
									?><strong><?php echo Event_Controller::getDateTime($oEvent->deadline);?></strong><?php
								}
							}

							$iDeltaTime = time() - $iEventCreationTimestamp;

							// ФИО создателя дела, если оным не является текущий сотрудник
							if (!$userIsEventCreator && !is_null($oEventCreator))
							{
								?>
								<div class="<?php echo $oEventCreator->isOnline() ? 'online margin-left-20' : 'offline margin-left-20'?>"></div>
								<a href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][<?php echo $oEventCreator->id?>]=1" onclick="$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oEventCreator->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>'}); return false"><?php echo htmlspecialchars($oEventCreator->getFullName());?></a><?php
							}
							?>
							</div><?php

							$text = ob_get_clean();

							$iEntityAdminFormId = 220;
							$entityPath = '/admin/event/index.php';
							// $additionalParams = "";
							// $datasetId = 0;
						break;
						// Deals
						case 1:
							$badge = 'fa fa-handshake-o';

							$color = 'darkorange';

							$oDeal = Core_Entity::factory('Deal', $oEntity->id);

							ob_start();
							echo $oDeal->nameBackend(NULL, $oAdmin_Form_Controller);
							$text = ob_get_clean();

							$iEntityAdminFormId = 226;
							$entityPath = '/admin/deal/index.php';
							// $additionalParams = "";
							// $datasetId = 0;
						break;
						// Notes
						case 2:
							$badge = 'fa fa-comment-o';

							$color = 'warning';

							$oCrm_Project_Note = Core_Entity::factory('Crm_Project_Note', $oEntity->id);

							$oUser = $oCrm_Project_Note->User;
							$iDatetime = Core_Date::sql2timestamp($oCrm_Project_Note->datetime);

							ob_start();
							echo nl2br(htmlspecialchars($oCrm_Project_Note->text));
							?>
							<div class="small gray"><span><a class="gray" href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][<?php echo $oUser->id?>]=1" onclick="$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oUser->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>'}); return false" title="<?php echo htmlspecialchars($oUser->getFullName())?>"><?php echo htmlspecialchars($oUser->getFullName())?></a></span><span class="pull-right"><?php echo date('H:i', $iDatetime)?></span></div>
							<?php
							$text = ob_get_clean();

							$iEntityAdminFormId = 312;
							$entityPath = '/admin/crm/project/note/index.php';
							// $additionalParams = "";
							// $datasetId = 0;
						break;
					}
					?>
					<li class="timeline-inverted">
						<div class="timeline-badge <?php echo $color?>">
							<i class="<?php echo $badge?>"></i>
						</div>
						<div class="timeline-panel">
							<div class="timeline-header bordered-bottom bordered-<?php echo $color?>">
								<div class="pull-right timeline-entity-actions">
									<?php
									$oEntity_Admin_Form = Core_Entity::factory('Admin_Form', $iEntityAdminFormId);

									// Отображать в списке действий
									if ($oEntity_Admin_Form->show_operations)
									{
										$aAllowed_Admin_Form_Actions = $oEntity_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

										foreach ($aAllowed_Admin_Form_Actions as $oAdmin_Form_Action)
										{
											if ($oAdmin_Form_Action->name == 'edit')
											{
												// Отображаем действие, только если разрешено.
												if (!$oAdmin_Form_Action->single)
												{
													continue;
												}

												$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

												$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
													? $Admin_Word_Value->name
													: '';

												// $path = '/admin/crm/project/entity/index.php';
												$additionalParams = "hostcms[checked][0][{$oEntity->id}]=1&crm_project_id={$crm_project_id}";

												$href = $oAdmin_Form_Controller->getAdminActionLoadHref($entityPath, $oAdmin_Form_Action->name, NULL, 0, intval($oEntity->id), $additionalParams, 10, 1, NULL, NULL, 'list');

												$onclick = "$.modalLoad({path: '{$entityPath}', action: 'edit', operation: 'modal', additionalParams: '{$additionalParams}', windowId: '{$windowId}'}); return false";

												?><a onclick="<?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
											}
										}
									}

									// Отображать в списке действий
									if ($oAdmin_Form->show_operations)
									{
										$aAllowed_Admin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

										foreach ($aAllowed_Admin_Form_Actions as $oAdmin_Form_Action)
										{
											// Отображаем действие, только если разрешено.
											if (!$oAdmin_Form_Action->single)
											{
												continue;
											}

											$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

											$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
												? $Admin_Word_Value->name
												: '';

											$path = '/admin/crm/project/entity/index.php';
											$additionalParams = "type={$oEntity->type}&entity_id={$oEntity->id}&crm_project_id={$crm_project_id}";

											$href = $oAdmin_Form_Controller->getAdminActionLoadHref($path, $oAdmin_Form_Action->name, NULL, 0, intval($oEntity->id), $additionalParams, 10, 1, NULL, NULL, 'entity');

											$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($path, $oAdmin_Form_Action->name, NULL, 0, intval($oEntity->id), $additionalParams, 10, 1, NULL, NULL, 'entity');

											// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
											if ($oAdmin_Form_Action->confirm)
											{
												$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$windowId} #row_0_{$oEntity->id}').toggleHighlight(); } else {{$onclick}} return res;";
											}
											?><a onclick="<?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
										}
									}
									?>
								</div>
							</div>
							<div class="timeline-body">
								<?php echo $text?>
							</div>
						</div>
					</li>
					<?php
				}
				?>
			</ul>
		<?php
		return $this;
	}
}