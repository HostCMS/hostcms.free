<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Entity_View
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Crm_Project_Entity_View extends Admin_Form_Controller_View
{
	/**
	 * Execute
	 * @return self
	 */
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
		<div class="row margin-bottom-20 margin-top-10">
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
				$iDatetime = Core_Date::sql2timestamp($oEntity->datetime);

				$class = '';

				switch ($oEntity->type)
				{
					// Events
					case 0:
						$badge = 'fa fa-tasks';

						$color = 'success';

						$oObject = Core_Entity::factory('Event', $oEntity->id);

						ob_start();

						$oEventCreator = $oObject->getCreator();

						// Временая метка создания дела
						$iEventCreationTimestamp = Core_Date::sql2timestamp($oObject->datetime);

						// Сотрудник - создатель дела
						$userIsEventCreator = !is_null($oEventCreator) && $oEventCreator->id == $oUser->id;

						$oEvent_Type = $oObject->Event_Type;

						$oObject->event_type_id && $oObject->showType();

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
								->value($oObject->event_status_id)
								->options($aMasEventStatuses)
								//->class('btn-group event-status')
								->onchange("$.adminLoad({path: hostcmsBackend + '/event/index.php', additionalParams: 'hostcms[checked][0][{$oObject->id}]=0&eventStatusId=' + $(this).find('li[selected]').prop('id'), action: 'changeStatus', windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
								->execute();
						}
						else
						{
							if ($oObject->event_status_id)
							{
								$oEvent_Status = Core_Entity::factory('Event_Status', $oObject->event_status_id);

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

						$nameColorClass = $oObject->deadline()
							? 'event-title-deadline'
							: '';

						$deadlineIcon = $oObject->deadline()
							? '<i class="fa fa-clock-o event-title-deadline"></i>'
							: '';

						?>
						<div class="event-title <?php echo $nameColorClass?>"><?php echo $deadlineIcon, htmlspecialchars($oObject->name)?></div>

						<div class="event-description"><?php echo Core_Str::cutSentences(strip_tags($oObject->description), 250)?></div>

						<div class="crm-date"><?php
						if ($oObject->all_day)
						{
							echo Event_Controller::getDate($oObject->start);
						}
						else
						{
							if (!is_null($oObject->start) && $oObject->start != '0000-00-00 00:00:00')
							{
								echo Event_Controller::getDateTime($oObject->start);
							}

							if (!is_null($oObject->start) && $oObject->start != '0000-00-00 00:00:00'
								&& !is_null($oObject->deadline) && $oObject->deadline != '0000-00-00 00:00:00'
							)
							{
								echo ' — ';
							}

							if (!is_null($oObject->deadline) && $oObject->deadline != '0000-00-00 00:00:00')
							{
								?><strong><?php echo Event_Controller::getDateTime($oObject->deadline)?></strong><?php
							}
						}

						// ФИО создателя дела, если оным не является текущий сотрудник
						if (!$userIsEventCreator && !is_null($oEventCreator))
						{
							$userColor = Core_Str::createColor($oEventCreator->id);

							?><div class="<?php echo $oEventCreator->isOnline() ? 'online' : 'offline'?> margin-left-20 margin-right-5"></div><span style="color: <?php echo $userColor?>"><?php $oEventCreator->showLink($oAdmin_Form_Controller->getWindowId());?></span><?php
						}
						?>
						</div><?php

						$text = ob_get_clean();

						$iEntityAdminFormId = 220;
						$entityPath = Admin_Form_Controller::correctBackendPath('/{admin}/event/index.php');
					break;
					// Deals
					case 1:
						$badge = 'fa fa-handshake-o';

						$color = 'info';

						$oObject = Core_Entity::factory('Deal', $oEntity->id);

						ob_start();
						echo $oObject->nameBackend(NULL, $oAdmin_Form_Controller, TRUE);
						$text = ob_get_clean();

						$iEntityAdminFormId = 226;
						$entityPath = Admin_Form_Controller::correctBackendPath('/{admin}/deal/index.php');
					break;
					// Notes
					case 2:
						$badge = 'fa fa-comment-o';

						$color = 'warning';

						$oObject = Core_Entity::factory('Crm_Note', $oEntity->id);
						$oObject->result && $class = 'timeline-crm-note-result';

						ob_start();
						echo nl2br($oObject->text);

						$files = $oObject->getFilesBlock($oObject->Crm_Project);

						if (!is_null($files))
						{
							?><div class="crm-note-attachment-wrapper"><?php echo $files?></div><?php
						}
						?>
						<div class="timeline-body-footer small gray"><span class="timeline-user"><?php $oObject->User->showLink($oAdmin_Form_Controller->getWindowId())?></span><span class="timeline-date pull-right"><?php echo date('H:i', $iDatetime)?></span></div>
						<?php
						$text = ob_get_clean();

						$iEntityAdminFormId = 312;
						$entityPath = Admin_Form_Controller::correctBackendPath('/{admin}/crm/project/note/index.php');
					break;
					case 3:
						$badge = 'fa fa-file-text-o';

						$color = 'maroon';

						$oObject = Core_Entity::factory('Crm_Project_Attachment', $oEntity->id);

						$src = Admin_Form_Controller::correctBackendPath('/{admin}/crm/project/attachment/index.php?crm_project_id=') . $oObject->crm_project_id . '&crm_project_attachment_id=' . $oObject->id . '&rand=' . time();

						$ext = Core_File::getExtension($oObject->getFilePath());

						$dataSrc = in_array($ext, array('png', 'jpg', 'jpeg', 'webp', 'gif'))
							? 'data-popover="hover-file" data-src="' . $src. '"'
							: '';

						ob_start();
						?>
						<div><i class="<?php echo Core_File::getIcon($oObject->file_name)?> margin-right-5"></i><a target="_blank" <?php echo $dataSrc?> href="<?php echo $src?>"><?php echo nl2br($oObject->file_name)?></a></div>
						<div class="small gray"><span class="gray"><?php $oObject->User->showLink($oAdmin_Form_Controller->getWindowId())?></span><span class="pull-right"><?php echo date('H:i', $iDatetime)?></span></div>
						<?php
						$text = ob_get_clean();

						$iEntityAdminFormId = 326;
						$entityPath = Admin_Form_Controller::correctBackendPath('/{admin}/crm/project/attachment/index.php');
					break;
					case 4:
						$badge = 'fa fa-columns';

						$color = 'danger';

						$oObject = Core_Entity::factory('Dms_Document', $oEntity->id);

						ob_start();
						if (strlen($oObject->numberBackend()))
						{
							?><div><?php echo $oObject->numberBackend()?></div><?php
						}

						echo $oObject->nameBackend(NULL, $oAdmin_Form_Controller, TRUE);

						if ($oObject->dms_document_type_id)
						{
							?><div class="margin-top-5"><?php echo $oObject->dms_document_type_idBackend()?></div><?php
						}

						$text = ob_get_clean();

						$iEntityAdminFormId = 278;
						$entityPath = Admin_Form_Controller::correctBackendPath('/{admin}/dms/document/index.php');
					break;
				}

				$badge = isset($oObject->user_id) && $oObject->user_id
					? '<img class="img-circle" src="' . $oObject->User->getAvatar() . '" width="30" height="30"/>'
					: '<i class="' . $badge . '"></i>';

				?>
				<li class="timeline-inverted <?php echo $class?>">
					<div class="timeline-badge <?php echo $color?>">
						<?php echo $badge?>
					</div>
					<div class="timeline-panel">
						<div class="timeline-header">
							<div class="pull-right timeline-entity-actions">
								<?php
								$oEntity_Admin_Form = Core_Entity::factory('Admin_Form', $iEntityAdminFormId);

								if (!in_array($oEntity_Admin_Form->id, array(326)))
								{
									// Отображать в списке действий
									if ($oEntity_Admin_Form->show_operations)
									{
										$aAllowed_Admin_Form_Actions = $oEntity_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

										foreach ($aAllowed_Admin_Form_Actions as $oAdmin_Form_Action)
										{
											if ($oAdmin_Form_Action->name == 'edit'
												&& (!method_exists($oObject, 'checkBackendAccess') || $oObject->checkBackendAccess($oAdmin_Form_Action->name, $oUser))
											)
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

												$additionalParams = "hostcms[checked][0][{$oEntity->id}]=1&crm_project_id={$crm_project_id}&secret_csrf=" . Core_Security::getCsrfToken();

												$href = $oAdmin_Form_Controller->getAdminActionLoadHref($entityPath, $oAdmin_Form_Action->name, NULL, 0, intval($oEntity->id), $additionalParams, 10, 1, NULL, NULL, 'list');

												$onclick = "$.modalLoad({path: '{$entityPath}', action: 'edit', operation: 'modal', additionalParams: '{$additionalParams}', windowId: '{$windowId}', width: '90%'}); return false";

												?><a onclick="<?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
											}
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
										if (!$oAdmin_Form_Action->single || !method_exists($oObject, 'checkBackendAccess') || !$oObject->checkBackendAccess($oAdmin_Form_Action->name, $oUser))
										{
											continue;
										}

										$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

										$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
											? $Admin_Word_Value->name
											: '';

										$path = '/{admin}/crm/project/entity/index.php';
										$additionalParams = "type={$oEntity->type}&entity_id={$oEntity->id}&crm_project_id={$crm_project_id}&secret_csrf=" . Core_Security::getCsrfToken();

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

		<script>
			$('[data-popover="hover-file"]').on('mouseenter', function(event) {
				var $this = $(this);

				if (!$this.data("bs.popover"))
				{
					$this.popover({
						placement: 'top',
						trigger: 'manual',
						html: true,
						content: function() {
							return '<img src="' + $(this).data('src') +'" style="max-width:200px" />';
						},
						container: "#<?php echo $windowId?>"
					});

					$this.attr('data-popoverAttached', true);

					$this.on('hide.bs.popover', function(e) {
						$this.attr('data-popoverAttached')
							? $this.removeAttr('data-popoverAttached')
							: e.preventDefault();
					})
					.on('show.bs.popover', function(e) {
						!$this.attr('data-popoverAttached') && e.preventDefault();
					})
					.on('shown.bs.popover', function(e) {
						$('#' + $this.attr('aria-describedby')).on('mouseleave', function(e) {
							!$this.parent().find(e.relatedTarget).length && $this.popover('destroy');
						});
					})
					.on('mouseleave', function(e) {
						!$(e.relatedTarget).parent('#' + $this.attr('aria-describedby')).length
						&& $this.attr('data-popoverAttached')
						&& $this.popover('destroy');
					});

					$this.popover('show');
				}
			});
		</script>

		<?php
		return $this;
	}
}