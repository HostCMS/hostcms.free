<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Note_Controller
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Crm_Note_Controller extends Admin_Form_Controller_View
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
	 * Timeline path
	 * @var string
	 */
	protected $_timelinePath = NULL;

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
		return self::getCrmCompletedDropdown($this->_Admin_Form_Controller);
	}

	/**
	 * Get completed dropdown
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	static public function getCrmCompletedDropdown($oAdmin_Form_Controller)
	{
		$aCompleted = array(
			1 => array(
				'value' => Core::_('Admin_Form.successfully'),
				'color' => '#a0d468',
				'icon' => 'fa-regular fa-circle-check fa-fw margin-right-5'
			),
			-1 => array(
				'value' => Core::_('Admin_Form.failed'),
				'color' => '#ed4e2a',
				'icon' => 'fa-solid fa-xmark fa-fw margin-right-5'
			)
		);

		return Admin_Form_Entity::factory('Dropdownlist')
			->options($aCompleted)
			->name('completed')
			->divAttr(array('class' => 'margin-left-10 crm-note-completed hidden'))
			->controller($oAdmin_Form_Controller)
			->execute();
	}

	/**
	 * Get emoji list
	 * @return array
	 */
	static public function getEmoji()
	{
		$aConfig = Core_Config::instance()->get('core_emoji');

		empty($aConfig) && $aConfig = array(
			'👍',
			'👎',
			'❤️',
			'🔥',
			'✅',
			'🎉',
			'🚀',
			'👀',
			'💡',
			'🤔',
			'📌',
			'❓',
			'❗',
			'👌',
			'🔔',
			'👏',
			'🤝',
			'📎',
			'🔒',
			'⏳'
		);

		return $aConfig;
	}

	/**
	 * Get emoji block
	 * @return string
	 */
	static public function getEmojiBlock()
	{
		$aEmoji = self::getEmoji();

		ob_start();

		?><div id="emoji-picker" class="emoji-picker hidden"><?php
			foreach ($aEmoji as $emoji)
			{
				?><span class="emoji-option"><?php echo $emoji?></span><?php
			}
		?></div><?php

		return ob_get_clean();
	}

	/**
	 * Show reactions for user
	 * @param User_Model $oUser
	 * @param Core_Entity $oEntity
	 * @return string
	 */
	static public function showReactions(User_Model $oUser, $oEntity)
	{
		ob_start();

		?><div class="reactions">
			<?php
				$aTmpReactions = array();

				$aCrm_Note_Reactions = $oEntity->Crm_Note_Reactions->findAll(FALSE);
				foreach ($aCrm_Note_Reactions as $oCrm_Note_Reaction)
				{
					if (!isset($aTmpReactions[$oCrm_Note_Reaction->emoji]))
					{
						$aTmpReactions[$oCrm_Note_Reaction->emoji] = array(
							'count' => 0,
							'reacted' => 0
						);
					}

					$aTmpReactions[$oCrm_Note_Reaction->emoji]['count']++;

					if ($oCrm_Note_Reaction->user_id == $oUser->id)
					{
						$aTmpReactions[$oCrm_Note_Reaction->emoji]['reacted'] = 1;
					}
				}

				// Сортировка по count по убыванию
				uasort($aTmpReactions, function($a, $b) {
					return $b['count'] - $a['count'];
				});

				foreach ($aTmpReactions as $emoji => $aReaction)
				{
					$reactedClass = $aReaction['reacted']
						? 'reaction user-reacted'
						: 'reaction';

					?><span class="<?php echo $reactedClass?>"><?php echo $emoji?> <?php echo $aReaction['count']?></span><?php
				}
			?>
		</div><?php

		return ob_get_clean();
	}

	/**
	 * Get users for current reaction
	 * @param Crm_Note_Model $oCrm_Note
	 * @param string $current_emoji
	 * @return string
	 */
	static public function getReactionUsers(Crm_Note_Model $oCrm_Note, $current_emoji)
	{
		ob_start();

		if (!is_null($oCrm_Note) && $current_emoji != '')
		{
			$aTmp = array();

			$aCrm_Note_Reactions = $oCrm_Note->Crm_Note_Reactions->findAll(FALSE);
			foreach ($aCrm_Note_Reactions as $oCrm_Note_Reaction)
			{
				if ($oCrm_Note_Reaction->user_id && $oCrm_Note_Reaction->emoji != '')
				{
					$oUser = $oCrm_Note_Reaction->User;

					$aPartsFullName = array();

					!empty($oUser->name) && $aPartsFullName[] = $oUser->name;
					!empty($oUser->surname) && $aPartsFullName[] = $oUser->surname;

					!count($aPartsFullName) && $aPartsFullName[] = $oUser->login;

					$author = implode(' ', $aPartsFullName);

					if (!isset($aTmpReactions[$oCrm_Note_Reaction->emoji]))
					{
						$aTmp[$oCrm_Note_Reaction->emoji][] = $author;
					}
				}
			}

			foreach ($aTmp as $emoji => $aAuthors)
			{
				if ($current_emoji == $emoji)
				{
					foreach ($aAuthors as $author)
					{
						?><span>
							<?php echo htmlspecialchars($author)?></br>
						</span><?php
					}
				}
			}
		}

		return ob_get_clean();
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

		$total_count = $oAdmin_Form_Controller->getTotalCount();

		if ($total_count)
		{
			?><div class="row margin-bottom-20 margin-top-10">
				<div class="col-xs-12 col-sm-6 col-md-8 text-align-left">
					<?php $this->_Admin_Form_Controller->pageNavigation()?>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-4 text-align-right">
					<?php $this->_Admin_Form_Controller->pageSelector()?>
				</div>
			</div><?php
		}
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

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetLimits()->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();

		$additionalParams = $this->_additionalParams;

		$externalReplace = $oAdmin_Form_Controller->getExternalReplace();
		foreach ($externalReplace as $replace_key => $replace_value)
		{
			$additionalParams = str_replace($replace_key, $replace_value, $additionalParams);
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

				$oUser = Core_Auth::getCurrentUser();
				$aAdmin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);
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
					$formAction = Admin_Form_Controller::correctBackendPath($this->_formPath) . '?' . $additionalParams . 'hostcms[action]=addNote&_=' . time() . '&hostcms[checked][0][0]=1&parentWindowId=' . htmlspecialchars($this->_windowId);
					?>
					<form action="<?php echo $formAction?>" method="POST" enctype='multipart/form-data' class="padding-bottom-10 dropzone-form dropzone-form-note">
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

							var $form = $("#<?php echo $this->_windowId?> .dropzone-form-note");
							$form.dropzone({
								url: $form.attr('action'),
								parallelUploads: 10,
								maxFilesize: <?php echo Core::$mainConfig['dropzoneMaxFilesize']?>,
								paramName: 'file',
								uploadMultiple: true,
								clickable: '#<?php echo $this->_windowId?> .dropzone-form-note #dropzone',
								previewsContainer: '#<?php echo $this->_windowId?> .dropzone-form-note #dropzone',
								autoProcessQueue: false,
								autoDiscover: false,
								previewTemplate:'<div class="dz-preview dz-file-preview"> <i class="fa-solid fa-xmark darkorange dz-file-remove" data-dz-remove></i><div class="dz-image"><img data-dz-thumbnail/></div> <div class="dz-details"> <div class="dz-size"><span data-dz-size></span></div> <div class="dz-filename"><span data-dz-name></span></div> </div> <div class="dz-progress"> <span class="dz-upload" data-dz-uploadprogress></span> </div> <div class="dz-error-message"><span data-dz-errormessage></span></div> <div class="dz-success-mark"> <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>Check</title> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <path d="M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z" stroke-opacity="0.198794158" stroke="#747474" fill-opacity="0.816519475" fill="#FFFFFF"></path> </g> </svg> </div> <div class="dz-error-mark"> <svg width="54px" height="54px" viewBox="0 0 54 54" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <title>Error</title> <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g stroke="#747474" stroke-opacity="0.198794158" fill="#FFFFFF" fill-opacity="0.816519475"> <path d="M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z"></path> </g> </g> </svg> </div> </div>',
								init: function() {
									var dropzone = this;

									$("#<?php echo $this->_windowId?> .dropzone-form-note button#sendForm").on("click", function(e) {
										e.preventDefault();
										e.stopPropagation();

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
											<?php echo $oAdmin_Form_Controller->checked(array(0 => array(0)))->getAdminSendForm(array('action' => 'addNote', 'additionalParams' => $additionalParams))?>
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
										$timeline = $window.find('li[data-type="timeline"]');

									$.beforeContentLoad($window);
									$.insertContent($window, response.form_html);

									// View mode doesn't have `timeline` tab
									if ($timeline.length)
									{
										window_id = $timeline.find('a').data('window-id');
										$.adminLoad({
											path: '<?php echo Admin_Form_Controller::correctBackendPath($this->_timelinePath)?>',
											additionalParams: '<?php echo $additionalParams?>',
											windowId: window_id
										});
									}
								}
							});
						});
					</script>
					<?php
				}
				?>
			</div>

			<?php
			if (count($aEntities))
			{
				$prevDate = NULL;

				$i = 0;

				foreach ($aEntities as $oEntity)
				{
					$color = $aColors[$i % $iCountColors];

					$iDatetime = Core_Date::sql2timestamp($oEntity->datetime);
					$sDate = Core_Date::timestamp2date($iDatetime);

					if ($prevDate != $sDate)
					{
						?><div class="text-align-center margin-bottom-20">
							<a class="badge badge-<?php echo $color?>"><?php echo Core_Date::timestamp2string(Core_Date::date2timestamp($sDate), FALSE)?></a>
						</div><?php

						$prevDate = $sDate;
						$i++;
					}

					echo self::_showMessage($oEntity, $additionalParams, $oAdmin_Form_Controller, $oUser, $oAdmin_Form, $oAdmin_Language);
				}
			}
			?>

			<?php echo Crm_Note_Controller::getEmojiBlock()?>

			<script>
				const wrapperNote = document.getElementById('<?php echo $this->_tabName?>').querySelector('.timeline-wrapper');
				crmNotesOnDOMReady(crmNotesCallback, wrapperNote);
			</script>
		</div>
		<?php

		return $this;
	}

	/**
	 * Show message
	 * @param Core_Entity $oEntity
	 * @param string $additionalParams
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @param User_Model $oUser
	 * @param Admin_Form_Model $oAdmin_Form
	 * @param Admin_Language_Model $oAdmin_Language
	 * @param integer $parent_id
	 * @return string
	 */
	public function _showMessage($oEntity, $additionalParams, Admin_Form_Controller $oAdmin_Form_Controller, User_Model $oUser, Admin_Form_Model $oAdmin_Form, Admin_Language_Model $oAdmin_Language, $parent_id = 0)
	{
		ob_start();

		$class = '';

		$iDatetime = Core_Date::sql2timestamp($oEntity->datetime);

		if ($oEntity->result == 1)
		{
			$class = 'timeline-crm-note-result';
		}
		elseif ($oEntity->result == -1)
		{
			$class = 'timeline-crm-note-result-unsuccessfull';
		}

		$badge = isset($oEntity->user_id) && $oEntity->user_id
			? '<img class="avatar" src="' . $oEntity->User->getAvatar() . '" />'
			: '<i class="avatar fa-regular fa-comment"></i>';
		?>

		<div class="message <?php echo $class?>" data-user-id="<?php echo $oUser->id?>" data-message-id="<?php echo $oEntity->id?>" data-parent-id="<?php echo $parent_id?>">
			<span class="d-flex yellow">
				<?php echo $badge?>
			</span>
			<div class="message-content">
				<div class="message-header">
					<?php
					$oUserAuthor = NULL;

					if ($oEntity->user_id)
					{
						$oUserAuthor = $oEntity->User;
					}

					if (!is_null($oUserAuthor))
					{
						echo $oUserAuthor->showCrmTitleLine();
					}
					?>

					<span class="message-actions">
						<?php
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

								if (method_exists($oEntity, 'checkBackendAccess') && !$oEntity->checkBackendAccess($oAdmin_Form_Action->name, $oUser))
								{
									continue;
								}

								$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

								$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
									? $Admin_Word_Value->name
									: '';

								$href = $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, NULL, 0, intval($oEntity->id));

								$entityAdditionalParams = $additionalParams . "&parentWindowId={$this->_windowId}";

								$onclick = $oAdmin_Form_Action->name == 'edit'
									? $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $oAdmin_Form_Controller->getPath(), 'action' => $oAdmin_Form_Action->name, 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oEntity->id, 'additionalParams' => $entityAdditionalParams, 'width' => '90%'))
									: $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, NULL, 0, $oEntity->id, $entityAdditionalParams, 10, 1, NULL, NULL, 'list');

								// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
								if ($oAdmin_Form_Action->confirm)
								{
									$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$this->_windowId} #row_0_{$oEntity->id}').toggleHighlight(); } else {mainFormLocker.unlock(); {$onclick}} return res;";
								}
								?><a onclick="mainFormLocker.unlock(); <?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
							}
						}
						?>
					</span>
				</div>
				<div class="message-body">
					<?php
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

					echo $text;
					?>
				</div>
				<div class="message-footer">
					<span class="timestamp"><?php echo date('H:i', $iDatetime)?></span>
					<span class="action-btn reply"><i class="fa-solid fa-reply"></i> <?php echo Core::_('Crm_Note.reply')?></span>
					<span class="action-btn quote-text"><i class="fa-regular fa-message"></i> <?php echo Core::_('Crm_Note.quote')?></span>
					<span class="action-btn add-emoji"><i class="fa-regular fa-face-grin"></i></span>

					<?php
					// Reactions
					echo Crm_Note_Controller::showReactions($oUser, $oEntity);
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
								echo self::_showMessage($oCrm_Note, $additionalParams, $oAdmin_Form_Controller, $oUser, $oAdmin_Form, $oAdmin_Language, $oCrm_Note->parent_id);
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