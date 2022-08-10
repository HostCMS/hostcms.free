<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Note
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Controller_Note extends Admin_Form_Controller_View
{
	/**
	 * Executes the business logic.
	 * @return self
	 */
	public function execute()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;
		$oAdmin_Form = $oAdmin_Form_Controller->getAdminForm();

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

		$oSortingField = $oAdmin_Form_Controller->getSortingField();

		$oUser = Core_Auth::getCurrentUser();

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		// $windowId = $oAdmin_Form_Controller->getWindowId();
		$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('parentWindowId'));
		$windowId = $parentWindowId ? $parentWindowId : $oAdmin_Form_Controller->getWindowId();

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetLimits()->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();

		$additionalParams = 'event_id={event_id}';
		$externalReplace = $oAdmin_Form_Controller->getExternalReplace();
		foreach ($externalReplace as $replace_key => $replace_value)
		{
			$additionalParams = str_replace($replace_key, $replace_value, $additionalParams);
		}

		$event_id = intval(Core_Array::getGet('event_id', 0));
		$oEvent = Core_Entity::factory('Event', $event_id);

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
		?>
		<div class="deal-note-board">
			<div class="">
				<div>
					<form action="/admin/event/note/index.php?hostcms[action]=addNote&_=<?php echo time()?>&hostcms[checked][0][0]=1&event_id=<?php echo $event_id?>" method="POST" enctype='multipart/form-data' class="padding-bottom-10 dropzone-form dropzone-form-note">
						<div class="timeline-comment-wrapper">
							<?php
								Admin_Form_Entity::factory('Textarea')
									->name('text_note')
									->rows(5)
									->wysiwyg(Core::moduleIsActive('wysiwyg'))
									->wysiwygOptions(array(
										'menubar' => 'false',
										'statusbar' => 'false',
										'plugins' => '"advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code wordcount"',
										'toolbar1' => '"bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | removeformat"'
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

						<script>
						$(function(){
							// Кнопка "+" в заметках сделки
							$('#<?php echo $windowId?> .formButtons :input').on('click', function() { mainFormLocker.unlock() });
						});
						</script>
					</form>

					<script>
					$(function() {
						var $form = $("#<?php echo $windowId?> .dropzone-form-note");
						$form.dropzone({
							url: $form.attr('action'),
							parallelUploads: 10,
							maxFilesize: 5,
							paramName: 'file',
							uploadMultiple: true,
							clickable: '#<?php echo $windowId?> .dropzone-form-note #dropzone',
							previewsContainer: '#<?php echo $windowId?> .dropzone-form-note #dropzone',
							autoProcessQueue: false,
							autoDiscover: false,
							init: function() {
								var dropzone = this;

								$(".dropzone-form-note button#sendForm").on("click", function(e) {
									e.preventDefault();
									e.stopPropagation();

									// Сохраним из визуальных редакторов данные
									if (typeof tinyMCE != 'undefined')
									{
										tinyMCE.triggerSave();
									}

									if (dropzone.getQueuedFiles().length)
									{
										$form.append('<input type="hidden" name="hostcms[window]" value="<?php echo $windowId?>">');
										dropzone.processQueue();
									}
									else
									{
										<?php echo $oAdmin_Form_Controller->checked(array(0 => array(0)))->getAdminSendForm(array('action' => 'addNote', 'additionalParams' => $additionalParams))?>
									}
								});
							},
							success : function(file, response){
								var $window = $("#<?php echo $oAdmin_Form_Controller->getWindowId()?>"),
									window_id = $('li[data-type="timeline"] > a').data('window-id');

								$.beforeContentLoad($window);
								$.insertContent($window, response.form_html);
								$.adminLoad({ path: '/admin/event/timeline/index.php', additionalParams: 'event_id=<?php echo $event_id?>', windowId: window_id });
							}
						});
					});
					</script>
				</div>

				<?php
				if (count($aEntities))
				{
					?><ul class="timeline crm-note-list cmr-note-timeline timeline-left timeline-no-vertical"><?php
					$prevDate = NULL;

					$i = 0;

					foreach ($aEntities as $oEntity)
					{
						$color = $aColors[$i % $iCountColors];

						$class = '';
						$oEntity->result && $class = 'timeline-crm-note-result';

						$iDatetime = Core_Date::sql2timestamp($oEntity->datetime);
						$sDate = Core_Date::timestamp2date($iDatetime);

						if ($prevDate != $sDate)
						{
							?><li class="timeline-node">
								<a class="badge badge-<?php echo $color?>"><?php echo Core_Date::timestamp2string(Core_Date::date2timestamp($sDate), FALSE)?></a>
							</li><?php

							$prevDate = $sDate;
							$i++;
						}
						?>
						<li class="timeline-inverted <?php echo $class?>">
							<div class="timeline-badge palegreen">
								<img class="img-circle" src="<?php echo $oEntity->User->getAvatar()?>" width="30" height="30"/>
							</div>
							<div class="timeline-panel">
								<div class="timeline-header bordered-bottom bordered-palegreen">
									<div class="pull-right timeline-entity-actions">
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

												$onclick = $oAdmin_Form_Action->name == 'edit'
													? "$.modalLoad({path: '{$oAdmin_Form_Controller->getPath()}', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][{$oEntity->id}]=1&event_id={$oEvent->id}&parentWindowId={$windowId}', windowId: '{$windowId}', width: '90%'}); return false"
													: $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, NULL, 0, intval($oEntity->id));

												// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
												if ($oAdmin_Form_Action->confirm)
												{
													$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$windowId} #row_0_{$oEntity->id}').toggleHighlight(); } else {mainFormLocker.unlock(); {$onclick}} return res;";
												}
												?><a onclick="mainFormLocker.unlock(); <?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
											}
										}
										?>
									</div>
								</div>
								<div class="timeline-body">
									<?php
										$text = $oEntity->text;
										$files = $oEntity->getFilesBlock($oEvent);

										if (!is_null($files))
										{
											$text .= '<div class="crm-note-attachment-wrapper">' . $files . '</div>';
										}

										echo $text;
									?>

									<div class="timeline-body-footer small gray"><span class="timeline-user"><?php $oEntity->User->showLink($oAdmin_Form_Controller->getWindowId())?></span><span class="timeline-date pull-right"><?php echo date('H:i', $iDatetime)?></span></div>
								</div>
							</div>
						</li>
						<?php
					}
					?></ul><?php
				}
				?>
			</div>
		</div>
		<?php

		return $this;
	}
}