<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Timeline
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Event_Controller_Timeline extends Admin_Form_Controller_View
{
	/**
	 * Executes the business logic.
	 * @return self
	 */
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

		$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('parentWindowId', '', 'str'));
		$windowId = $parentWindowId ? $parentWindowId : $oAdmin_Form_Controller->getWindowId();

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$oAdmin_Form_Controller->setDatasetLimits();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();

		$oCurrentUser = Core_Auth::getCurrentUser();

		$additionalParams = 'event_id={event_id}&secret_csrf=' . Core_Security::getCsrfToken();
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
				$aTmp[Core_Date::sql2date($oEntity->dataDatetime)][$key] = $oEntity;
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

		// if ($oEventParent->checkPermission2Edit($oCurrentUser))
		// {
			?>
			<div>
				<?php
					$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oCurrentUser);

					$bAddNoteAccess = FALSE;
					foreach ($aAdmin_Form_Actions as $aAdmin_Form_Action)
					{
						if ($aAdmin_Form_Action->name == 'addNote')
						{
							$bAddNoteAccess = TRUE;
							break;
						}
					}

					if ($bAddNoteAccess)
					{
				?>
				<form action="<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/event/timeline/index.php')?>?hostcms[action]=addNote&_=<?php echo time()?>&hostcms[checked][0][1-0]=1&event_id=<?php echo $event_id?>&parentWindowId=<?php echo htmlspecialchars($windowId)?>" method="POST" enctype='multipart/form-data' class="padding-bottom-10 dropzone-form dropzone-form-timeline">
					<div class="timeline-comment-wrapper">
						<?php
							Admin_Form_Entity::factory('Textarea')
								->name('text_note')
								->rows(5)
								->wysiwyg(Core::moduleIsActive('wysiwyg'))
								->wysiwygMode('short')
								/*->wysiwygOptions(array(
									'menubar' => 'false',
									'statusbar' => 'false',
									'plugins' => '"advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code wordcount"',
									'toolbar1' => '"bold italic underline alignleft aligncenter alignright alignjustify bullist numlist removeformat"',
								))*/
								->divAttr(array('class' => ''))
								->controller($oAdmin_Form_Controller)
								->execute();
						?>
						<div class="margin-top-10 crm-note-attachments-dropzone hidden">
							<!-- <div class="previews"></div> -->
							<div id="dropzone" class="dropzone-previews">
								<div class="dz-message needsclick"><i class="fa fa-arrow-circle-o-up"></i> <?php echo Core::_('Admin_Form.upload_file')?></div>
							</div>
						</div>
						<div class="timeline-comment-panel formButtons">
							<div class="timeline-comment-panel-file">
								<span class="margin-right-20" onclick="$.showDropzone(this, '<?php echo $windowId?>');"><i class="fa fa-paperclip fa-fw"></i> <?php echo Core::_('Crm_Note.file')?></span>
								<div class="checkbox">
									<label>
										<input name="result" type="checkbox" class="colored-blue" value="1" onclick="$('#<?php echo $windowId?> .event-completed').toggleClass('hidden')"/>
										<span class="text"><?php echo Core::_('Crm_Note.result')?></span>
									</label>
								</div>
								<?php
									echo $oEventParent->getCompletedDropdown($this->_Admin_Form_Controller);
								?>
							</div>
							<button id="sendForm" class="btn btn-primary btn-sm" type="submit">
								<?php echo Core::_('Crm_Note.send')?>
							</button>
						</div>
					</div>
				</form>

				<script>
					$(function() {
						// Кнопка "+" в заметках сделки
						$('#<?php echo $windowId?> .formButtons :input').on('click', function() { mainFormLocker.unlock() });

						var $form = $("#<?php echo $windowId?> .dropzone-form-timeline");
						$form.dropzone({
							url: $form.attr('action'),
							parallelUploads: 10,
							maxFilesize: <?php echo Core::$mainConfig['dropzoneMaxFilesize']?>,
							paramName: 'file',
							uploadMultiple: true,
							clickable: '#<?php echo $windowId?> .dropzone-form-timeline #dropzone',
							previewsContainer: '#<?php echo $windowId?> .dropzone-form-timeline #dropzone',
							autoProcessQueue: false,
							autoDiscover: false,
							previewTemplate:'<div class="dz-preview dz-file-preview"> <i class="fa fa-times darkorange dz-file-remove" data-dz-remove></i><div class="dz-image"><img data-dz-thumbnail/></div> <div class="dz-details"> <div class="dz-size"><span data-dz-size></span></div> <div class="dz-filename"><span data-dz-name></span></div> </div> <div class="dz-progress"> <span class="dz-upload" data-dz-uploadprogress></span> </div> <div class="dz-error-message"><span data-dz-errormessage></span></div> <div class="dz-success-mark"> <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>Check</title> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF"></path> </g> </svg> </div> <div class="dz-error-mark"> <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>Error</title> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z"></path> </g> </g> </svg> </div> </div>',
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
										$form.append('<input type="hidden" name="hostcms[window]" value="<?php echo htmlspecialchars($windowId)?>-event-timeline">');
										//$form.append('<input type="hidden" name="parentWindowId" value="<?php echo htmlspecialchars($windowId)?>">');
										dropzone.processQueue();
									}
									else
									{
										<?php echo $oAdmin_Form_Controller->checked(array(0 => array('1-0')))->getAdminSendForm(array('action' => 'addNote', 'additionalParams' => $additionalParams))?>
									}
								});

								dropzone.on('addedfile', function(file){
									$(dropzone.previewsContainer).addClass('dz-started');
								});

								dropzone.on('removedfile', function(file){
									if (dropzone.getQueuedFiles().length == 0)
									{
										$(dropzone.previewsContainer).removeClass('dz-started');
									}
								});
							},
							success : function(file, response){
								var $window = $("#<?php echo $oAdmin_Form_Controller->getWindowId()?>"),
									window_id = $window.parents('.tabbable').find('li[data-type="note"] > a').data('window-id');

								$.beforeContentLoad($window);
								$.insertContent($window, response.form_html);
								$.adminLoad({ path: hostcmsBackend + '/event/note/index.php', additionalParams: 'event_id=<?php echo $event_id?>', windowId: window_id });
							}
						});
					});
				</script>
				<?php
				}
				?>
			</div>
			<?php
		// }

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
							$class = $textColor = '';

							$iDatetime = Core_Date::sql2timestamp($oTmpEntity->dataDatetime);
							$time = date('H:i', Core_Date::sql2timestamp($oTmpEntity->dataDatetime));

							switch (get_class($oTmpEntity))
							{
								// Event histories
								case 'Event_History_Model':
									$badge = 'fa fa-history';

									$text = '<span style="color: ' . $oTmpEntity->color . '">' . $oTmpEntity->text . '</span>';
									$textColor = ' style="color: ' . $oTmpEntity->color . ' !important"';

								break;
								// Crm notes
								case 'Crm_Note_Model':
									$badge = 'fa fa-comment-o';
									$color = 'yellow';

									//$oCrm_Note = Core_Entity::factory('Crm_Note', $oTmpEntity->id);

									$text = $oTmpEntity->text;

									if (Core::moduleIsActive('crm'))
									{
										$files = $oTmpEntity->getFilesBlock($oTmpEntity->Event);

										if (!is_null($files))
										{
											$text .= '<div class="crm-note-attachment-wrapper">' . $files . '</div>';
										}
									}

									if ($oTmpEntity->result == 1)
									{
										$class = 'timeline-crm-note-result';
									}
									elseif ($oTmpEntity->result == -1)
									{
										$class = 'timeline-crm-note-result-unsuccessfull';
									}

									$iEntityAdminFormId = 324;

									$path = '/{admin}/event/timeline/index.php';
									$additionalParams = '';
									// $datasetId = 0;
								break;
								// Events
								case 'Event_Model':
									$badge = 'fa fa-tasks';
									$color = 'orange';

									$oEvent = Core_Entity::factory('Event', $oTmpEntity->id);

									$text = $oEvent->showContent($oAdmin_Form_Controller);

									$iEntityAdminFormId = 220;

									$path = '/{admin}/event/timeline/index.php';
									$additionalParams = "";
									// $datasetId = 0;
								break;
								case 'Dms_Document_Model':
									$badge = 'fa fa-columns';
									$color = 'purple';

									// $color = 'danger';

									// $oObject = Core_Entity::factory('Dms_Document', $oTmpEntity->id);

									ob_start();
									?>
									<div class="semi-bold">
										<span><?php echo htmlspecialchars($oTmpEntity->name)?></span><?php

										if (strlen($oTmpEntity->numberBackend()))
										{
											?><span class="margin-left-5">№ <?php echo $oTmpEntity->numberBackend()?></span><?php
										}

										if ($oTmpEntity->classify)
										{
											?><i class="fa fa-lock margin-left-5" style="color: #ed4e2a" title="<?php echo Core::_('Dms_Document.classify_1')?>"></i><?php
										}
									?></div><?php

									if (strlen($oTmpEntity->description))
									{
										?><div class="small gray"><?php echo nl2br(htmlspecialchars($oTmpEntity->description))?></div><?php
									}

									?><div>
										<?php
										if ($oTmpEntity->dms_document_type_id)
										{
											?><span class="margin-right-10"><?php echo $oTmpEntity->dms_document_type_idBackend()?></span><?php
										}
										echo $oTmpEntity->showDmsCommunication() . $oTmpEntity->showDmsWorkflowExecutions($oAdmin_Form_Controller)?>
									</div><?php

									if ($oTmpEntity->crm_project_id)
									{
										$oTmpEntity->showCrmProjects($oAdmin_Form_Controller);
									}

									$text = ob_get_clean();

									$iEntityAdminFormId = 278;

									$path = '/{admin}/event/timeline/index.php';
								break;
							}

							$badge = isset($oTmpEntity->user_id) && $oTmpEntity->user_id
								? '<img class="img-circle" src="' . $oTmpEntity->User->getAvatar() . '" width="30" height="30"/>'
								: '<i class="' . $badge . '"></i>';
							?>
							<li class="timeline-inverted <?php echo $class?>">
								<div class="timeline-badge <?php echo $color?>" <?php echo $textColor?>>
									<?php echo $badge?>
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
													$additionalParams = "event_id={$oEventParent->id}&parentWindowId={$windowId}&secret_csrf=" . Core_Security::getCsrfToken();

													$path = Admin_Form_Controller::correctBackendPath($path);

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
			Core_Message::show(Core::_('Admin_Form.timeline_empty'), 'warning');
		}

		return $this;
	}
}