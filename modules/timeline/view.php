<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Timeline_View
 *
 * @package HostCMS
 * @subpackage Timeline
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Timeline_View extends Admin_Form_Controller_View
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

		// $oSortingField = $oAdmin_Form_Controller->getSortingField();

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

		$aEntities = $aDatasets[0]->load();

		$additionalParams = "secret_csrf=" . Core_Security::getCsrfToken();

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
			'gray inverted',
			'palegreen inverted',
			'orange inverted',
			'sky inverted',
			'green inverted',
		);
		$iCountColors = count($aColors);

		?>
		<div class="timeline-wrapper ai-wrapper">
			<div class="timeline-wrapper-form">
				<?php
				$bAddNoteAccess = FALSE;

				$oCurrentUser = Core_Auth::getCurrentUser();
				$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oCurrentUser);
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
					$formAction = Admin_Form_Controller::correctBackendPath('/{admin}/timeline/index.php') . '?' . $additionalParams . 'hostcms[action]=addNote&_=' . time() . '&hostcms[checked][0][1-0]=1&parentWindowId=' . htmlspecialchars($windowId);
					?>
					<form action="<?php echo $formAction?>" method="POST" enctype='multipart/form-data' class="dropzone-form dropzone-form-timeline">
						<div class="compose-box">
							<div class="compose-wrapper">
								<div id="quote-preview" class="quote-preview hidden">
									<div class="quote-content">
										<span id="quote-author-name" class="quote-author"></span>
										<span id="quote-text-preview"></span>
									</div>
									<span id="cancel-quote" class="cancel-quote" title="Отменить ответ">✖</span>
								</div>

								<input type="text" id="new-message-topic" name="subject_note" class="form-control hidden" placeholder="<?php echo Core::_('Crm_Note.subject')?>">

								<?php

								$aMentionUsers = array();

								$oUsers = Core_Entity::factory('User');
								$oUsers->queryBuilder()
									->where('users.id', '!=', $oUser->id);

								$aUsers = $oUsers->findAll(FALSE);
								foreach ($aUsers as $oTmpUser)
								{
									$aPartsFullName = array();

									!empty($oTmpUser->name) && $aPartsFullName[] = $oTmpUser->name;
									!empty($oTmpUser->surname) && $aPartsFullName[] = $oTmpUser->surname;

									$name = count($aPartsFullName)
										? implode(' ', $aPartsFullName)
										: '';

									$aMentionUsers[] = array(
										'id' => $oTmpUser->id,
										'name' => $name,
										'login' => $oTmpUser->login
									);
								}

								Admin_Form_Entity::factory('Textarea')
									->id('new-message-text')
									->name('text_note')
									->rows(6)
									->wysiwyg(Core::moduleIsActive('wysiwyg'))
									->wysiwygMode('short')
									->wysiwygMentions($aMentionUsers)
									->wysiwygMentionTemplate('<span class="mention" data-user-id="${selectedEmp.id}" contenteditable="false">@${mentionText}</span>&nbsp;')
									->wysiwygContentStyle('
										body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, 	sans-serif; font-size: 14px; color: #1f2937; }
										.mention { color: #2563eb; background-color: #eff6ff; padding: 2px 6px; border-radius: 4px; font-weight: 500; }
									')
									->divAttr(array('class' => ''))
									->controller($oAdmin_Form_Controller)
									->execute();
								?>

								<div class="margin-top-10 crm-note-attachments-dropzone hidden">
									<div id="dropzone" class="dropzone-previews">
										<div class="dz-message needsclick"><i class="fa-regular fa-circle-up"></i> <?php echo Core::_('Admin_Form.upload_file')?></div>
									</div>
								</div>

								<div class="compose-actions">
									<span id="textarea-emoji-btn" class="icon-btn" title="<?php echo Core::_('Crm_Note.add_emoji')?>"><i class="fa-regular fa-face-grin"></i></span>

									<span id="start-video-record-btn" class="icon-btn" title="<?php echo Core::_('Crm_Note.record_video')?>"><i class="fa-solid fa-video"></i></span>
									<div id="video-record-modal" class="ai-modal hidden"> <div class="video-record-container">
											<div class="video-header">
												<h3><?php echo Core::_('Crm_Note.record_video')?></h3>
												<button id="close-video-modal" class="ai-modal-close">&times;</button>
											</div>

											<div class="video-preview-wrapper">
												<video id="video-preview" autoplay muted playsinline></video>
												<div id="recording-indicator" class="recording-indicator hidden">
													<span class="red-dot"></span> <span id="record-timer">00:00 / 10:00</span>
												</div>
											</div>

											<div class="video-controls">
												<span id="btn-start-record" class="btn-primary">🔴 <?php echo Core::_('Crm_Note.start_record')?></span>
												<span id="btn-stop-record" class="btn-danger hidden"><?php echo Core::_('Crm_Note.stop_record')?></span>
												<span id="btn-send-video" class="btn-success hidden"><?php echo Core::_('Crm_Note.add_record_file')?></span>
												<span id="btn-cancel-video" class="btn-secondary hidden"><?php echo Core::_('Crm_Note.cancel_record')?></span>
											</div>
										</div>
									</div>

									<span id="start-audio-record-btn" class="icon-btn" title="<?php echo Core::_('Crm_Note.record_audio')?>"><i class="fa-solid fa-microphone"></i></span>

									<div id="audio-record-modal" class="ai-modal hidden">
										<div class="video-record-container">
											<div class="video-header">
												<h3>Голосовое сообщение</h3>
												<button id="close-audio-modal" class="ai-modal-close">&times;</button>
											</div>

											<div>
												<div id="audio-recording-indicator" class="hidden">
													<span class="red-dot"></span>
													<span id="audio-record-timer">00:00 / 10:00</span>
												</div>
											</div>

											<div class="video-controls">
												<button id="btn-start-audio-record" class="btn-primary">🔴 <?php echo Core::_('Crm_Note.start_record')?></button>
												<button id="btn-stop-audio-record" class="btn-danger hidden"><?php echo Core::_('Crm_Note.stop_record')?></button>
												<button id="btn-send-audio" class="btn-success hidden"><?php echo Core::_('Crm_Note.add_record_file')?></button>
												<button id="btn-cancel-audio" class="btn-secondary hidden"><?php echo Core::_('Crm_Note.cancel_record')?></button>
											</div>
										</div>
									</div>

									<span class="icon-btn" title="<?php echo Core::_('Crm_Note.file')?>" onclick="$.showDropzone(this, '<?php echo $windowId?>');"><i class="fa-solid fa-paperclip"></i></span>
									<span id="toggle-topic-btn" class="icon-btn" title="<?php echo Core::_('Crm_Note.show_theme')?>"><i class="fa-solid fa-tag"></i></span>

									<button id="sendForm" class="btn btn-primary btn-sm" type="submit">
										<?php echo Core::_('Crm_Note.send')?>
									</button>
								</div>
							</div>
						</div>
						<input type="hidden" name="parent_id" value="0"/>
						<!-- <input type="hidden" name="parent_timeline_id" value="0"/> -->
					</form>

					<script>
						$(function() {
							// Кнопка "+" в заметках сделки
							$('#<?php echo $windowId?> .compose-wrapper :input').on('click', function() { mainFormLocker.unlock() });

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
								previewTemplate:'<div class="dz-preview dz-file-preview"> <i class="fa-solid fa-xmark darkorange dz-file-remove" data-dz-remove></i><div class="dz-image"><img data-dz-thumbnail/></div> <div class="dz-details"> <div class="dz-size"><span data-dz-size></span></div> <div class="dz-filename"><span data-dz-name></span></div> </div> <div class="dz-progress"> <span class="dz-upload" data-dz-uploadprogress></span> </div> <div class="dz-error-message"><span data-dz-errormessage></span></div> <div class="dz-success-mark"> <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>Check</title> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF"></path> </g> </svg> </div> <div class="dz-error-mark"> <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>Error</title> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z"></path> </g> </g> </svg> </div> </div>',
								init: function() {
									var dropzone = this;

									$("#<?php echo $windowId?> .dropzone-form-timeline button#sendForm").on("click", function(e) {
										e.preventDefault();
										e.stopPropagation();

										// Сохраним из визуальных редакторов данные
										if (typeof wysiwyg !== 'undefined') {
											wysiwyg.saveAll($(this));
										}

										if (dropzone.getQueuedFiles().length)
										{
											$form.append('<input type="hidden" name="hostcms[window]" value="<?php echo htmlspecialchars($windowId)?>">');
											$form.append('<input type="hidden" name="hostcms[action]" value="addNote">');
											dropzone.processQueue();
										}
										else
										{
											<?php echo $oAdmin_Form_Controller
												->checked(array(0 => array('1-0')))
												->getAdminSendForm(array('action' => 'addNote', 'additionalParams' => $additionalParams))
											?>
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
								success : function(file, response) {
									var $window = $("#<?php echo $oAdmin_Form_Controller->getWindowId()?>"),
										window_id = $window.parents('.tabbable').find('li[data-type="note"] > a').data('window-id');

									$.beforeContentLoad($window);
									$.insertContent($window, response.form_html);
								}
							});
						});
					</script>
				<?php
				}
				?>
			</div>

			<div class="cmr-note-timeline-wrapper overflow-hidden">
				<?php
				$i = $j = 0;

				foreach ($aTmp as $datetime => $aTmpEntities)
				{
					$color = $aColors[$i % $iCountColors];
					?>
						<div class="text-align-center margin-bottom-20"><a class="badge badge-<?php echo $color?>"><?php echo Core_Date::timestamp2string(Core_Date::date2timestamp($datetime), FALSE)?></a></div>
					<?php
					foreach ($aTmpEntities as $oEntity)
					{
						echo self::_showMessage($oEntity, $oAdmin_Form_Controller, $oUser, $oAdmin_Form, $oAdmin_Language, $windowId, $color);

						$j++;
					}

					$i++;
				}
				?>
			</div>
			<?php echo Crm_Note_Controller::getEmojiBlock() ?>
			<?php
			if (Core::moduleIsActive('ai'))
			{
				echo Ai_Controller::getAiPromptBlock();
			}
			?>
		</div>

		<script>
			const wrapperTimeline = document.getElementById('id_content').querySelector('.timeline-wrapper');
			crmNotesOnDOMReady(crmNotesCallback, wrapperTimeline);
		</script>

		<?php
		return $this;
	}

	/**
	 * Show message block
	 * @param Core_Entity $oEntity
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @param User_Model $oUser
	 * @param Admin_Form_Model $oAdmin_Form
	 * @param Admin_Language_Model $oAdmin_Language
	 * @param string $windowId
	 * @param string $color
	 * @param integer $parent_id
	 * @return string
	 */
	static protected function _showMessage($oEntity, Admin_Form_Controller $oAdmin_Form_Controller, User_Model $oUser, Admin_Form_Model $oAdmin_Form, Admin_Language_Model $oAdmin_Language, $windowId, $color, $parent_id = 0)
	{
		$iDatetime = Core_Date::sql2timestamp($oEntity->dataDatetime);

		$class = '';

		//switch ($oEntity->type)
		switch (get_class($oEntity))
		{
			// Notes
			case 'Crm_Note_Model':
				$iDatetime = Core_Date::sql2timestamp($oEntity->datetime);

				$badge = 'fa-regular fa-comment';

				$color = 'warning';

				$oObject = Core_Entity::factory('Crm_Note', $oEntity->id);
				$oObject->result && $class = 'timeline-crm-note-result';

				ob_start();

				if ($oObject->subject != '')
				{
					echo "<b>", $oObject->subject, "</b>", "<br/>";
				}

				echo nl2br($oObject->text);

				$files = $oObject->getFilesBlock($oObject->Timeline);

				if (!is_null($files))
				{
					?><div class="crm-note-attachment-wrapper"><?php echo $files?></div><?php
				}
				?>
				<!-- <div class="timeline-body-footer small gray"><span class="timeline-user"><?php $oObject->User->showLink($oAdmin_Form_Controller->getWindowId())?></span><span class="timeline-date pull-right"><?php echo date('H:i', $iDatetime)?></span></div> -->
				<?php
				$text = ob_get_clean();

				$iEntityAdminFormId = 312;
				$entityPath = Admin_Form_Controller::correctBackendPath('/{admin}/timeline/note/index.php');
			break;
			// Deals
			case 'Deal_Model':
				$badge = 'fa-regular fa-handshake';

				$color = 'info';

				$oObject = Core_Entity::factory('Deal', $oEntity->id);

				ob_start();
				echo $oObject->nameBackend(NULL, $oAdmin_Form_Controller, TRUE);
				$text = ob_get_clean();

				$iEntityAdminFormId = 226;
				$entityPath = Admin_Form_Controller::correctBackendPath('/{admin}/deal/index.php');
			break;
			// Events
			case 'Event_Model':
				$badge = 'fa-solid fa-tasks';

				$color = 'success';

				$oObject = Core_Entity::factory('Event', $oEntity->id);

				ob_start();

				$oEventCreator = $oObject->getCreator();

				// Временая метка создания дела
				// $iEventCreationTimestamp = Core_Date::sql2timestamp($oObject->datetime);

				// Сотрудник - создатель дела
				$userIsEventCreator = !is_null($oEventCreator) && $oEventCreator->id == $oUser->id;

				// $oEvent_Type = $oObject->Event_Type;

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
						<i class="fa-solid fa-circle" style="margin-right: 5px; color: <?php echo $sEventStatusColor?>"></i><span style="color: <?php echo $sEventStatusColor?>"><?php echo $sEventStatusName?></span>
					</div>
					<?php
				}

				$nameColorClass = $oObject->deadline()
					? 'event-title-deadline'
					: '';

				$deadlineIcon = $oObject->deadline()
					? '<i class="fa-regular fa-clock event-title-deadline"></i>'
					: '';

				?>
				<div class="event-title <?php echo $nameColorClass?>"><?php echo $deadlineIcon, htmlspecialchars((string) $oObject->name)?></div>

				<div class="event-description"><?php echo Core_Str::cutSentences(strip_tags((string) $oObject->description), 250)?></div>

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
				/*if (!$userIsEventCreator && !is_null($oEventCreator))
				{
					$userColor = Core_Str::createColor($oEventCreator->id);

					?><div class="<?php echo $oEventCreator->isOnline() ? 'online' : 'offline'?> margin-left-20 margin-right-5"></div>
					<span style="color: <?php echo $userColor?>"><?php $oEventCreator->showLink($oAdmin_Form_Controller->getWindowId());?></span>
					<?php
				}*/
				?>
				</div><?php

				$text = ob_get_clean();

				$iEntityAdminFormId = 220;
				$entityPath = Admin_Form_Controller::correctBackendPath('/{admin}/event/index.php');
			break;
			case 'Dms_Document_Model':
				$badge = 'fa-solid fa-columns';

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

		$badge = get_class($oObject) == 'Crm_Note_Model' && isset($oObject->user_id) && $oObject->user_id
			? '<img class="avatar" src="' . $oObject->User->getAvatar() . '"/>'
			: '<i class="avatar ' . $badge . '"></i>';

		$timelineId = get_class($oObject) == 'Crm_Note_Model'
			? intval($oObject->Timeline->id)
			: 0;

		ob_start();

		?><div class="message <?php echo $class?>" data-user-id="<?php echo $oUser->id?>" data-message-id="<?php echo $oObject->id?>" data-parent-id="<?php echo $parent_id?>" data-timeline-id="<?php echo $timelineId?>">
			<span class="d-flex <?php echo $color?>">
				<?php echo $badge?>
			</span>

			<div class="message-content">
				<div class="message-header">
					<?php
					$oUserAuthor = NULL;

					if (get_class($oObject) == 'Event_Model')
					{
						$oUserAuthor = $oObject->getCreator();
					}
					elseif (get_class($oObject) == 'Deal_Model')
					{
						$oUserAuthor = $oObject->Creator;
					}
					else
					{
						if ($oObject->user_id)
						{
							$oUserAuthor = $oObject->User;
						}
					}

					if (!is_null($oUserAuthor))
					{
						echo $oUserAuthor->showCrmTitleLine($windowId);
					}
					?>

					<span class="message-actions">
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

										$additionalParams = "hostcms[checked][0][{$oEntity->id}]=1&secret_csrf=" . Core_Security::getCsrfToken();

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
								if (!$oAdmin_Form_Action->single
									|| !method_exists($oObject, 'checkBackendAccess')
									|| !$oObject->checkBackendAccess($oAdmin_Form_Action->name, $oUser)
								)
								{
									continue;
								}

								$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

								$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
									? $Admin_Word_Value->name
									: '';

								$model = get_class($oEntity);

								$path = '/{admin}/timeline/index.php';
								// $additionalParams = "type={$oEntity->type}&entity_id={$oEntity->id}&secret_csrf=" . Core_Security::getCsrfToken();
								$additionalParams = "model={$model}&entity_id={$oEntity->id}&secret_csrf=" . Core_Security::getCsrfToken();

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
					</span>
				</div>
				<div class="message-body">
					<?php echo $text?>
				</div>
				<div class="message-footer">
					<span class="timestamp"><?php echo Core_Date::timestamp2string($iDatetime)?></span>

					<?php
					if (get_class($oObject) == 'Crm_Note_Model')
					{
						$subscribeActive = '';
						$subscribeTitle = Core::_('Crm_Note.subscribe');
						$subscribeIcon = '<i class="fa-solid fa-bell"></i>';

						$oTimeline = $oObject->Timeline;

						if ($oTimeline)
						{
							$oTimeline_User = $oTimeline->Timeline_Users->getByUser_id($oUser->id);

							if (!is_null($oTimeline_User))
							{
								$subscribeActive = 'active';
								$subscribeTitle = Core::_('Crm_Note.unsubscribe');
								$subscribeIcon = '<i class="fa-solid fa-bell-slash"></i>';
							}
						}
						?>

						<span class="action-btn reply"><i class="fa-solid fa-reply"></i> <?php echo Core::_('Crm_Note.reply')?></span>
						<span class="action-btn quote-text"><i class="fa-regular fa-message"></i> <?php echo Core::_('Crm_Note.quote')?></span>

						<?php
						if (Core::moduleIsActive('ai'))
						{
							echo Ai_Controller::showAiPrompts();
						}
						?>

						<span class="action-btn add-emoji"><i class="fa-regular fa-face-grin"></i></span>
						<span class="action-btn subscribe <?php echo $subscribeActive?>" data-subscribe="<?php echo Core::_('Crm_Note.subscribe')?>" data-unsubscribe="<?php echo Core::_('Crm_Note.unsubscribe')?>" title="<?php echo $subscribeTitle?>"><?php echo $subscribeIcon?></span>

						<?php
						// Reactions
						echo Crm_Note_Controller::showReactions($oUser, $oObject);
						?>

						<div class="reaction-tooltip"></div>
					<?php
					}
					?>
				</div>

				<?php
				if (get_class($oObject) == 'Crm_Note_Model')
				{
					$aCrm_Notes = $oObject->Crm_Notes->findAll(FALSE);
					if (count($aCrm_Notes))
					{
						?><div class="message-replies"><?php
							foreach ($aCrm_Notes as $oCrm_Note)
							{
								echo self::_showMessage($oCrm_Note, $oAdmin_Form_Controller, $oUser, $oAdmin_Form, $oAdmin_Language, $windowId, $color, $oCrm_Note->parent_id);
							}
						?></div><?php
					}
				}
				?>
			</div>
		</div><?php

		return ob_get_clean();
	}
}