<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Timeline
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Controller_Timeline extends Admin_Form_Controller_View
{
	public function execute()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;

		$oAdmin_View = Admin_View::create($oAdmin_Form_Controller->Admin_View)
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

		$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('parentWindowId'));
		$windowId = $parentWindowId ? $parentWindowId : $oAdmin_Form_Controller->getWindowId();

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$oAdmin_Form_Controller->setDatasetLimits();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();

		$oCurrentUser = Core_Auth::getCurrentUser();

		$additionalParams = 'event_id={event_id}';
		$externalReplace = $oAdmin_Form_Controller->getExternalReplace();

		foreach ($externalReplace as $replace_key => $replace_value)
		{
			$additionalParams = str_replace($replace_key, $replace_value, $additionalParams);
		}

		$aTmp = array();

		if (!is_null($aEntities) && count($aEntities))
		{
			foreach ($aEntities as $key => $oEntity)
			{
				$aTmp[Core_Date::sql2date($oEntity->datetime)][$key] = $oEntity;
			}
		}

		$aColors = array(
			'palegreen',
			'warning',
			'info',
			'maroon',
			'darkorange',
			'blue',
			'danger'
		);
		$iCountColors = count($aColors);

		$event_id = intval(Core_Array::getGet('event_id', 0));
		$oEventParent = Core_Entity::factory('Event', $event_id);

		if ($oEventParent->checkPermission2Edit($oCurrentUser))
		{
			?>
			<div>
				<form action="/admin/event/timeline/index.php?hostcms[action]=addNote&_=<?php echo time()?>&hostcms[checked][0][1-0]=1&event_id=<?php echo $event_id?>" method="POST" enctype='multipart/form-data' class="padding-bottom-10 dropzone-form dropzone-form-timeline">
					<div class="timeline-comment-wrapper">
						<!-- <textarea rows="3" name="text_note" type="text" class="form-control" placeholder="<?php echo Core::_('Event_Note.note_placeholder')?>"></textarea>-->
						<?php
							Admin_Form_Entity::factory('Textarea')
								->name('text_note')
								->rows(5)
								->wysiwyg(Core::moduleIsActive('wysiwyg'))
								->wysiwygOptions(array(
									'menubar' => 'false',
									'statusbar' => 'false',
									'plugins' => '"advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code wordcount"',
									'toolbar1' => '"bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat"',
									// 'statusbar' => true
								))
								->divAttr(array('class' => ''))
								->controller($oAdmin_Form_Controller)
								->execute();
						?>
						<div class="margin-top-10 crm-note-attachments-dropzone hidden">
							<!-- <div class="previews"></div> -->
							<div id="dropzone">
								<div class="dz-message needsclick"><i class="fa fa-arrow-circle-o-up"></i> <?php echo Core::_('Admin_Form.upload_file')?></div>
							</div>
						</div>
						<div class="timeline-comment-panel formButtons">
							<div class="timeline-comment-panel-file">
								<span class="margin-right-20" onclick="$.showDropzone(this, '<?php echo $windowId?>');"><i class="fa fa-paperclip fa-fw"></i> <?php echo Core::_('Crm_Note.file')?></span>
								<div class="checkbox">
									<label>
										<input name="result" type="checkbox" class="colored-blue" value="1"/>
										<span class="text"><?php echo Core::_('Crm_Note.result')?></span>
									</label>
								</div>
							</div>
							<button id="sendForm" class="btn btn-palegreen btn-sm" type="submit">
								<?php echo Core::_('Crm_Note.send')?>
							</button>
						</div>
					</div>
				</form>
			</div>
			<?php
		}

		if (count($aTmp))
		{
			?>
			<div class="cmr-note-timeline-wrapper overflow-hidden">
				<ul class="timeline cmr-note-timeline timeline-left timeline-no-vertical">
					<?php
					$i = $j = 0;
					foreach ($aTmp as $datetime => $aTmpEntities)
					{
						$color = $aColors[$i % $iCountColors];

						?>
						<li class="timeline-node">
						<a class="badge badge-<?php echo $color?>"><?php echo Core_Date::timestamp2string(Core_Date::date2timestamp($datetime), FALSE)?></a>
						</li>
						<?php
						foreach ($aTmpEntities as $key => $oTmpEntity)
						{
							$datasetId = 0;
							$iEntityAdminFormId = 0;
							$path = '';
							$additionalParams = '';
							$class = '';

							$iDatetime = Core_Date::sql2timestamp($oTmpEntity->datetime);
							$time = date('H:i', Core_Date::sql2timestamp($oTmpEntity->datetime));

							switch (get_class($oTmpEntity))
							{
								// Event histories
								case 'Event_History_Model':
									$badge = 'fa fa-history';

									$text = '<span style="color: ' . $oTmpEntity->color . '">' . $oTmpEntity->text . '</span>';

								break;
								// Crm notes
								case 'Crm_Note_Model':
									$badge = 'fa fa-comment-o';

									//$oCrm_Note = Core_Entity::factory('Crm_Note', $oTmpEntity->id);

									$text = $oTmpEntity->text;
									$files = $oTmpEntity->getFilesBlock($oTmpEntity->Event);

									if (!is_null($files))
									{
										$text .= '<div class="crm-note-attachment-wrapper">' . $files . '</div>';
									}

									$oTmpEntity->result && $class = 'timeline-crm-note-result';

									$iEntityAdminFormId = 324;

									$path = '/admin/event/timeline/index.php';
									$additionalParams = '';
									// $datasetId = 0;
								break;
								// Events
								case 'Event_Model':
									$badge = 'fa fa-tasks';

									$oEvent = Core_Entity::factory('Event', $oTmpEntity->id);

									// $title = htmlspecialchars($oEvent->name);
									$title = '';

									/*ob_start();

									$path = $oAdmin_Form_Controller->getPath();

									$oEventCreator = $oEvent->getCreator();

									// Сотрудник - создатель дела
									$userIsEventCreator = !is_null($oEventCreator) && $oEventCreator->id == $oUser->id;

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

									// $iDeltaTime = time() - $iEventCreationTimestamp;

									// ФИО создателя дела, если оным не является текущий сотрудник
									if (!$userIsEventCreator && !is_null($oEventCreator))
									{
										?><div class="<?php echo $oEventCreator->isOnline() ? 'online margin-left-20' : 'offline margin-left-20'?>"></div><?php
										$oEventCreator->showLink($oAdmin_Form_Controller->getWindowId());
									}
									?>
									</div><?php

									$text = ob_get_clean();*/

									$text = $oEvent->showContent($oAdmin_Form_Controller);

									$iEntityAdminFormId = 220;

									$path = '/admin/event/timeline/index.php';
									$additionalParams = "";
									// $datasetId = 0;
								break;
							}
							?>
							<li class="timeline-inverted <?php echo $class?>">
								<div class="timeline-badge <?php echo $color?>">
									<i class="<?php echo $badge?>"></i>
								</div>
								<div class="timeline-panel">
									<div class="timeline-header">
										<div class="pull-right timeline-entity-actions">
											<?php
											$oEntity_Admin_Form = Core_Entity::factory('Admin_Form')->getById($iEntityAdminFormId);

											// Отображать в списке действий
											if (!is_null($oEntity_Admin_Form) && $oEntity_Admin_Form->show_operations)
											{
												$aAllowed_Admin_Form_Actions = $oEntity_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

												foreach ($aAllowed_Admin_Form_Actions as $oAdmin_Form_Action)
												{
													$aAllowedActions = array('edit', 'markDeleted', 'deleteEntity');

													// Отображаем действие, только если разрешено.
													if (!$oAdmin_Form_Action->single || !in_array($oAdmin_Form_Action->name, $aAllowedActions))
													{
														continue;
													}

													if (method_exists($oTmpEntity, 'checkBackendAccess') && !$oTmpEntity->checkBackendAccess($oAdmin_Form_Action->name, $oUser))
													{
														continue;
													}

													$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

													$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
														? $Admin_Word_Value->name
														: '';

													// $additionalParams = "type={$oTmpEntity->type}&entity_id={$key}&event_id={$oEventParent->id}";
													$additionalParams = "event_id={$oEventParent->id}&parentWindowId={$windowId}";

													$href = $oAdmin_Form_Controller->getAdminActionLoadHref($path, $oAdmin_Form_Action->name, NULL, $datasetId, $key, $additionalParams, 10, 1, NULL, NULL, 'list');

													$onclick = $oAdmin_Form_Action->name == 'edit'
														? $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $path, 'action' => $oAdmin_Form_Action->name, 'operation' => 'modal', 'datasetKey' => $datasetId, 'datasetValue' => $key, 'additionalParams' => $additionalParams, 'width' => '90%'))
														: $oAdmin_Form_Controller->getAdminActionLoadAjax($path, $oAdmin_Form_Action->name, NULL, $datasetId, $key, $additionalParams, 10, 1, NULL, NULL, 'list');

													// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
													if ($oAdmin_Form_Action->confirm)
													{
														$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$windowId} #row_0_{$key}').toggleHighlight(); } else {mainFormLocker.unlock(); {$onclick}} return res;";
													}
													?><a onclick="<?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
												}
											}
											?>
										</div>
									</div>
									<div class="timeline-body">
										<span><?php echo $text?></span>
										<div class="timeline-body-footer small">
											<?php
											$oUserAuthor = NULL;

											if (get_class($oTmpEntity) == 'Event_Model')
											{
												$oUserAuthor = $oTmpEntity->getCreator();
											}
											else
											{
												if ($oTmpEntity->user_id)
												{
													$oUserAuthor = $oTmpEntity->User;
												}
											}

											if (!is_null($oUserAuthor))
											{
												?><span class="timeline-user"><?php $oUserAuthor->showLink($oAdmin_Form_Controller->getWindowId())?></span><?php
											}
											?><span class="timeline-date pull-right"><?php echo date('H:i', $iDatetime)?></span>
										</div>
									</div>
								</div>
							</li>
							<?php
							$j++;
						}
						$i++;
					}
					?>
				</ul>
				<div class="row margin-bottom-20margin-top-10 pull-right">
					<div class="col-xs-12 text-align-left timeline-board">
						<?php $this->_Admin_Form_Controller->pageNavigation()?>
					</div>
					<!-- <div class="col-xs-12 col-sm-6 col-md-4 text-align-right">
						<?php $this->_Admin_Form_Controller->pageSelector()?>
					</div> -->
				</div>
			</div>
			<?php
		}
		else
		{
			Core_Message::show(Core::_('Siteuser.timeline_empty'), 'warning');
		}
		?>

		<script>
			$(function() {
				// Кнопка "+" в заметках сделки
				$('#<?php echo $windowId?> .formButtons :input').on('click', function() { mainFormLocker.unlock() });

				var $form = $("#<?php echo $windowId?> .dropzone-form-timeline");
				$form.dropzone({
					url: $form.attr('action'),
					parallelUploads: 10,
					maxFilesize: 5,
					paramName: 'file',
					uploadMultiple: true,
					clickable: '#<?php echo $windowId?> .dropzone-form-timeline #dropzone',
					previewsContainer: '#<?php echo $windowId?> .dropzone-form-timeline #dropzone',
					autoProcessQueue: false,
					autoDiscover: false,
					init: function() {
						var dropzone = this;

						$("#<?php echo $windowId?> .dropzone-form-timeline button#sendForm").on("click", function(e) {
							e.preventDefault();
							e.stopPropagation();

							// Сохраним из визуальных редакторов данные
							if (typeof tinyMCE != 'undefined')
							{
								tinyMCE.triggerSave();
							}

							if (dropzone.getQueuedFiles().length)
							{
								$form.append('<input type="hidden" name="hostcms[window]" value="<?php echo $windowId?>-event-timeline">');
								dropzone.processQueue();
							}
							else
							{
								<?php echo $oAdmin_Form_Controller->checked(array(0 => array('1-0')))->getAdminSendForm(array('action' => 'addNote', 'additionalParams' => $additionalParams))?>
							}
						});
					},
					success : function(file, response){
						var $window = $("#<?php echo $oAdmin_Form_Controller->getWindowId()?>"),
							window_id = $window.parents('.tabbable').find('li[data-type="note"] > a').data('window-id');

						$.beforeContentLoad($window);
						$.insertContent($window, response.form_html);
						$.adminLoad({ path: '/admin/event/note/index.php', additionalParams: 'event_id=<?php echo $event_id?>', windowId: window_id });
					}
				});
			});
		</script>
		<?php

		return $this;
	}
}