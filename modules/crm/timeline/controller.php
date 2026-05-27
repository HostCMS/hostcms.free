<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Timeline_Controller
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Crm_Timeline_Controller extends Admin_Form_Controller_View
{
	/**
	 * Tab name
	 * @var string
	 */
	protected $_tabName = NULL;

	/**
	 * Additional params
	 * @var string
	 */
	protected $_additionalParams = NULL;

	/**
	 * Form path
	 * @var string
	 */
	protected $_formPath = NULL;

	/**
	 * Note path
	 * @var string
	 */
	protected $_notePath = NULL;

	/**
	 * Window ID
	 * @var string
	 */
	protected $_windowId = NULL;

	/**
	 * Entity name, e.g. 'Lead'
	 * @var string
	 */
	protected $_entityName = NULL;

	/**
	 * Get completed dropdown
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function getCompletedDropdown()
	{
		return Crm_Note_Controller::getCrmCompletedDropdown($this->_Admin_Form_Controller);
	}

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

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$oUser = Core_Auth::getCurrentUser();

		if (is_null($oUser))
		{
			return FALSE;
		}

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$oAdmin_Form_Controller->setDatasetLimits();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();

		$additionalParams = $this->_additionalParams;

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
		<div class="timeline-wrapper">
			<div>
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
					$formAction = Admin_Form_Controller::correctBackendPath($this->_formPath) . '?' . $additionalParams . 'hostcms[action]=addNote&_=' . time() . '&hostcms[checked][0][1-0]=1&parentWindowId=' . htmlspecialchars($this->_windowId);
					?>
					<form action="<?php echo $formAction?>" method="POST" enctype='multipart/form-data' class="padding-bottom-10 dropzone-form dropzone-form-timeline">
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
								Admin_Form_Entity::factory('Textarea')
									->id('new-message-text')
									->name('text_note')
									->rows(6)
									->wysiwyg(Core::moduleIsActive('wysiwyg'))
									->wysiwygMode('short')
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
									<span class="icon-btn" title="<?php echo Core::_('Crm_Note.file')?>" onclick="$.showDropzone(this, '<?php echo $this->_windowId?>');"><i class="fa-solid fa-paperclip"></i></span>
									<span id="toggle-topic-btn" class="icon-btn" title="<?php echo Core::_('Crm_Note.show_theme')?>"><i class="fa-solid fa-tag"></i></span>

									<div class="checkbox">
										<label>
											<input name="result" type="checkbox" class="colored-blue" value="1" onclick="$('#<?php echo $this->_windowId?> .crm-note-completed').toggleClass('hidden')"/>
											<span class="text"><?php echo Core::_('Crm_Note.result')?></span>
										</label>
									</div>
									<?php
										echo $this->getCompletedDropdown();
									?>

									<button id="sendForm" class="btn btn-primary btn-sm" type="submit">
										<?php echo Core::_('Crm_Note.send')?>
									</button>
								</div>
							</div>
						</div>
						<input type="hidden" name="parent_id" value="0"/>
					</form>

					<script>
						$(function() {
							// Кнопка "+" в заметках сделки
							$('#<?php echo $this->_windowId?> .compose-wrapper :input').on('click', function() { mainFormLocker.unlock() });

							var $form = $("#<?php echo $this->_windowId?> .dropzone-form-timeline");
							$form.dropzone({
								url: $form.attr('action'),
								parallelUploads: 10,
								maxFilesize: <?php echo Core::$mainConfig['dropzoneMaxFilesize']?>,
								paramName: 'file',
								uploadMultiple: true,
								clickable: '#<?php echo $this->_windowId?> .dropzone-form-timeline #dropzone',
								previewsContainer: '#<?php echo $this->_windowId?> .dropzone-form-timeline #dropzone',
								autoProcessQueue: false,
								autoDiscover: false,
								previewTemplate:'<div class="dz-preview dz-file-preview"> <i class="fa-solid fa-xmark darkorange dz-file-remove" data-dz-remove></i><div class="dz-image"><img data-dz-thumbnail/></div> <div class="dz-details"> <div class="dz-size"><span data-dz-size></span></div> <div class="dz-filename"><span data-dz-name></span></div> </div> <div class="dz-progress"> <span class="dz-upload" data-dz-uploadprogress></span> </div> <div class="dz-error-message"><span data-dz-errormessage></span></div> <div class="dz-success-mark"> <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>Check</title> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF"></path> </g> </svg> </div> <div class="dz-error-mark"> <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>Error</title> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z"></path> </g> </g> </svg> </div> </div>',
								init: function() {
									var dropzone = this;

									$("#<?php echo $this->_windowId?> .dropzone-form-timeline button#sendForm").on("click", function(e) {
										e.preventDefault();
										e.stopPropagation();

										// Сохраним из визуальных редакторов данные
										if (typeof wysiwyg !== 'undefined') {
											wysiwyg.saveAll($(this));
										}

										if (dropzone.getQueuedFiles().length)
										{
											$form.append('<input type="hidden" name="hostcms[window]" value="<?php echo htmlspecialchars($this->_tabName)?>">');
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
									$.adminLoad({
										path: '<?php echo Admin_Form_Controller::correctBackendPath($this->_notePath)?>',
										additionalParams: '<?php echo $additionalParams?>',
										windowId: window_id
									});
								}
							});
						});
					</script>
				<?php
				}
				?>
			</div>
			<?php
			if (count($aTmp))
			{
				?>
				<div class="cmr-note-timeline-wrapper overflow-hidden">
					<?php
					$i = $j = 0;
					foreach ($aTmp as $datetime => $aTmpEntities)
					{
						$color = $aColors[$i % $iCountColors];
						?>
							<div class="text-align-center margin-bottom-20"><a class="badge badge-<?php echo $color?>"><?php echo Core_Date::timestamp2string(Core_Date::date2timestamp($datetime), FALSE)?></a></div>
						<?php
						foreach ($aTmpEntities as $key => $oEntity)
						{
							echo $this->_showMessage($oEntity, $key, $additionalParams, $oAdmin_Form_Controller, $oUser, $oAdmin_Form, $oAdmin_Language, $color);

							$j++;
						}
						$i++;
					}
					?>
					<div class="row margin-bottom-20 margin-top-10 pull-right">
						<div class="col-xs-12 text-align-left timeline-board">
							<?php $this->_Admin_Form_Controller->pageNavigation()?>
						</div>
					</div>
				</div>

			<?php
		}
		else
		{
			Core_Message::show(Core::_('Admin_Form.timeline_empty'), 'warning');
		}

		echo Crm_Note_Controller::getEmojiBlock();

		?>
		<script>
			const wrapperTimeline = document.getElementById('<?php echo $this->_tabName?>').querySelector('.timeline-wrapper');
			crmNotesOnDOMReady(crmNotesCallback, wrapperTimeline);
		</script>

		</div><?php

		return $this;
	}

	/**
	 * Show message
	 * @param Core_Entity $oEntity
	 * @param int $key
	 * @param string $additionalParams
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @param User_Model $oUser
	 * @param Admin_Form_Model $oAdmin_Form
	 * @param Admin_Language_Model $oAdmin_Language
	 * @param string $color
	 * @param integer $parent_id
	 * @return string
	 */
	protected function _showMessage($oEntity, $key, $additionalParams, Admin_Form_Controller $oAdmin_Form_Controller, User_Model $oUser, Admin_Form_Model $oAdmin_Form, Admin_Language_Model $oAdmin_Language, $color, $parent_id = 0)
	{
		$datasetId = $iEntityAdminFormId = 0;
		$path = $badge = $entityAdditionalParams = $class = $textColor = '';
		$iDatetime = Core_Date::sql2timestamp($oEntity->dataDatetime);

		switch (get_class($oEntity))
		{
			// Crm notes
			case 'Crm_Note_Model':
				$badge = 'fa-regular fa-comment';
				$color = 'yellow';

				$text = '';

				if ($oEntity->subject != '')
				{
					$text .= "<b>" . $oEntity->subject . "</b><br/>";
				}

				$text .= $oEntity->text;

				if (Core::moduleIsActive('crm'))
				{
					$files = $oEntity->getFilesBlock($oEntity->{$this->_entityName});

					if (!is_null($files))
					{
						$text .= '<div class="crm-note-attachment-wrapper">' . $files . '</div>';
					}
				}

				if ($oEntity->result == 1)
				{
					$class = 'timeline-crm-note-result';
				}
				elseif ($oEntity->result == -1)
				{
					$class = 'timeline-crm-note-result-unsuccessfull';
				}

				$iEntityAdminFormId = 324;

				$path = $this->_formPath;
			break;
			// Events
			case 'Event_Model':
				$badge = 'fa-solid fa-list-check';
				$color = 'orange';

				$text = $oEntity->showContent($oAdmin_Form_Controller);

				$iEntityAdminFormId = 220;

				$path = $this->_formPath;
			break;
			case 'Event_History_Model':
				$badge = 'fa-solid fa-clock-rotate-left';

				$text = '<span style="color: ' . $oEntity->color . '">' . $oEntity->text . '</span>';
				$textColor = ' style="color: ' . $oEntity->color . ' !important"';
			break;
			// Deals
			case 'Deal_Model':
				$badge = 'fa-regular fa-handshake';
				$color = 'purple';

				$text = $oEntity->showContent($oAdmin_Form_Controller);

				$iEntityAdminFormId = 226;

				$path = $this->_formPath;
			break;
			case 'Deal_History_Model':
				$badge = 'fa-solid fa-clock-rotate-left';
				$text = $oEntity->text;
				$textColor = ' style="color: ' . $oEntity->color . '"';
			break;
			case 'Deal_Shop_Item_Model':
				$badge = 'fa-solid fa-cart-shopping';
				$color = 'palegreen';

				$text = $oEntity->showContent($oAdmin_Form_Controller);

				$iEntityAdminFormId = 273;

				$path = $this->_formPath;
			break;
			case 'Deal_Step_Model':
				$badge = 'fa-solid fa-retweet fa-rotate-90';

				$text = '<div><div class="deal-template-step-name deal-template-timeline" style="color: ' . Core_Str::hex2darker($oEntity->Deal_Template_Step->color, 0.2) . '; outline-color: ' . $oEntity->Deal_Template_Step->color . '; background-color: ' . Core_Str::hex2lighter($oEntity->Deal_Template_Step->color, 0.88) . '">' . htmlspecialchars((string) $oEntity->Deal_Template_Step->name) . '</div></div>';

				if ($oEntity->comment != '')
				{
					$text .= '<span class="small">' . nl2br(htmlspecialchars($oEntity->comment)) . '</span>';
				}

				$textColor = ' style="color: ' . $oEntity->Deal_Template_Step->color . '"';
			break;
			// DMS
			case 'Dms_Document_Model':
				$badge = 'fa-solid fa-table-columns';
				$color = 'purple';

				ob_start();
				?>
				<div class="semi-bold">
					<span><?php echo htmlspecialchars($oEntity->name)?></span><?php

					if (strlen($oEntity->numberBackend()))
					{
						?><span class="margin-left-5">№ <?php echo $oEntity->numberBackend()?></span><?php
					}

					if ($oEntity->classify)
					{
						?><i class="fa-solid fa-lock margin-left-5" style="color: #ed4e2a" title="<?php echo Core::_('Dms_Document.classify_1')?>"></i><?php
					}
				?></div><?php

				if (strlen($oEntity->description))
				{
					?><div class="small gray"><?php echo nl2br(htmlspecialchars($oEntity->description))?></div><?php
				}

				?><div>
					<?php
					if ($oEntity->dms_document_type_id)
					{
						?><span class="margin-right-10"><?php echo $oEntity->dms_document_type_idBackend()?></span><?php
					}
					echo $oEntity->showDmsCommunication() . $oEntity->showDmsWorkflowExecutions($oAdmin_Form_Controller)?>
				</div><?php

				if ($oEntity->crm_project_id)
				{
					$oEntity->showCrmProjects($oAdmin_Form_Controller);
				}

				$text = ob_get_clean();

				$iEntityAdminFormId = 278;

				$path = $this->_formPath;
			break;
			// Leads
			case 'Lead_History_Model':
				$badge = 'fa-solid fa-clock-rotate-left';

				$text = $oEntity->text;
				$textColor = ' style="color: ' . $oEntity->color . ' !important"';
			break;
			case 'Lead_Shop_Item_Model':
				$badge = 'fa-solid fa-cart-shopping';
				$color = 'palegreen';

				$text = $oEntity->showContent($oAdmin_Form_Controller);

				$iEntityAdminFormId = 273;

				$path = $this->_formPath;
			break;
			case 'Lead_Step_Model':
				$badge = 'fa-solid fa-retweet fa-rotate-90';

				$text = '<div><div class="deal-template-step-name deal-template-timeline" style="color: ' . Core_Str::hex2darker($oEntity->Lead_Status->color, 0.2) . '; border-color: ' . $oEntity->Lead_Status->color . '; background-color: ' . Core_Str::hex2lighter($oEntity->Lead_Status->color, 0.88) . '">' . htmlspecialchars((string) $oEntity->Lead_Status->name) . '</div></div>';

				$textColor = ' style="color: ' . $oEntity->Lead_Status->color . ' !important"';
			break;
			// Siteuser
			case 'Siteuser_Email_Model':
				$badge = 'fa-regular fa-envelope';
				$color = 'azure';

				$text = $oEntity->subjectBackend(NULL, $oAdmin_Form_Controller);

				$iEntityAdminFormId = 276;

				$path = $this->_formPath;
			break;
			// Shop
			case 'Shop_Order_Model':
				$badge = 'fa-solid fa-basket-shopping';
				$color = 'palegreen';

				$text = $oEntity->showContent($oAdmin_Form_Controller);

				$iEntityAdminFormId = 75;

				$path = $this->_formPath;
			break;
		}

		$badge = get_class($oEntity) == 'Crm_Note_Model' && isset($oEntity->user_id) && $oEntity->user_id
			? '<img class="avatar" src="' . $oEntity->User->getAvatar() . '"/>'
			: '<i class="avatar ' . $badge . '"></i>';

		$message_id = get_class($oEntity) == 'Crm_Note_Model'
			? $oEntity->id
			: 0;

		ob_start();

		?>
		<div class="message <?php echo $class?>" data-user-id="<?php echo $oUser->id?>" data-message-id="<?php echo $message_id?>" data-parent-id="<?php echo $parent_id?>">
			<span class="d-flex <?php echo $color?>" <?php echo $textColor?>>
				<?php echo $badge?>
			</span>
			<div class="message-content">
				<div class="message-header">
					<?php
					$oUserAuthor = NULL;

					if (get_class($oEntity) == 'Event_Model')
					{
						$oUserAuthor = $oEntity->getCreator();
					}
					else
					{
						if ($oEntity->user_id)
						{
							$oUserAuthor = $oEntity->User;
						}
					}

					if (!is_null($oUserAuthor))
					{
						echo $oUserAuthor->showCrmTitleLine();
					}
					?>

					<span class="message-actions">
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

									if (method_exists($oEntity, 'checkBackendAccess') && !$oEntity->checkBackendAccess($oAdmin_Form_Action->name, $oUser))
									{
										continue;
									}

									$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

									$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
										? $Admin_Word_Value->name
										: '';

									$entityAdditionalParams = $additionalParams . "&parentWindowId={$this->_windowId}";

									$path = Admin_Form_Controller::correctBackendPath($path);

									$href = $oAdmin_Form_Controller->getAdminActionLoadHref($path, $oAdmin_Form_Action->name, NULL, $datasetId, $key, $entityAdditionalParams, 10, 1, NULL, NULL, 'list');

									$onclick = $oAdmin_Form_Action->name == 'edit'
										? $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $path, 'action' => $oAdmin_Form_Action->name, 'operation' => 'modal', 'datasetKey' => $datasetId, 'datasetValue' => $key, 'additionalParams' => $entityAdditionalParams, 'width' => '90%'))
										: $oAdmin_Form_Controller->getAdminActionLoadAjax($path, $oAdmin_Form_Action->name, NULL, $datasetId, $key, $entityAdditionalParams, 10, 1, NULL, NULL, 'list');

									// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
									if ($oAdmin_Form_Action->confirm)
									{
										$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$this->_windowId} #row_0_{$key}').toggleHighlight(); } else {mainFormLocker.unlock(); {$onclick}} return res;";
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
					<span class="timestamp"><?php echo date('H:i', $iDatetime)?></span>

					<?php
					if (get_class($oEntity) == 'Crm_Note_Model')
					{
						?>
						<span class="action-btn reply"><i class="fa-solid fa-reply"></i> <?php echo Core::_('Crm_Note.reply')?></span>
						<span class="action-btn quote-text"><i class="fa-regular fa-message"></i> <?php echo Core::_('Crm_Note.quote')?></span>
						<span class="action-btn add-emoji"><i class="fa-regular fa-face-grin"></i></span>
						<?php
						// Reactions
						echo Crm_Note_Controller::showReactions($oUser, $oEntity);

						?><div class="reaction-tooltip"></div><?php
					}
					?>
				</div>

				<?php
				if (get_class($oEntity) == 'Crm_Note_Model')
				{
					$aCrm_Notes = $oEntity->Crm_Notes->findAll(FALSE);
					if (count($aCrm_Notes))
					{
						?><div class="message-replies"><?php
							foreach ($aCrm_Notes as $oCrm_Note)
							{
								echo self::_showMessage($oCrm_Note, $key, $additionalParams, $oAdmin_Form_Controller, $oUser, $oAdmin_Form, $oAdmin_Language, $color, $oCrm_Note->parent_id);
							}
						?></div><?php
					}
				}
				?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}
}