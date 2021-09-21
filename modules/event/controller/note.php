<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Note
 *
 * @package HostCMS
 * @subpackage Event
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		$total_count = $oAdmin_Form_Controller->getTotalCount();

		if ($total_count)
		{
			?><div class="row margin-bottom-20">
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

		$oCurrentUser = Core_Auth::getCurrentUser();

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

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
			<div class="row">
				<?php
				if ($oEvent->checkPermission2Edit($oCurrentUser))
				{
				?>
				<div class="col-xs-12 margin-bottom-20">
					<form action="/admin/event/note/index.php" method="POST" class="padding-bottom-10">
						<div class="input-group">
							<textarea rows="2" name="text_note" type="text" class="form-control" placeholder="<?php echo Core::_('Event_Note.note_placeholder')?>"></textarea>
							<span class="input-group-btn padding-left-30 formButtons">
								<button id="sendForm" class="btn btn-default" type="submit" onclick="<?php echo $oAdmin_Form_Controller
									->checked(array(0 => array(0)))
									->getAdminSendForm(array('action' => 'addEventNote', 'additionalParams' => $additionalParams))?>">
									<i class="fa fa-plus fa-fw"></i>
								</button>
							</span>
						</div>

						<script>
						$(function(){
							// Кнопка "+" в заметках сделки
							$('#<?php echo $windowId?> .formButtons :input').on('click', function() { mainFormLocker.unlock() });
						});
						</script>
					</form>
				</div>
				<?php
				}

				if (count($aEntities))
				{
					?><ul class="timeline timeline-left"><?php
					$prevDate = NULL;

					$i = 0;

					foreach ($aEntities as $oEntity)
					{
						$color = $aColors[$i % $iCountColors];

						$oUser = $oEntity->User;

						$iDatetime = Core_Date::sql2timestamp($oEntity->datetime);
						$sDate = Core_Date::timestamp2date($iDatetime);

						if ($prevDate != $sDate)
						{
							?><li class="timeline-node">
								<a class="label label-<?php echo $color?>"><?php echo Core_Date::timestamp2string(Core_Date::date2timestamp($sDate), FALSE)?></a>
							</li><?php

							$prevDate = $sDate;
							$i++;
						}
						?>
						<li class="timeline-inverted">
							<div class="timeline-badge palegreen">
								<img class="img-circle" src="<?php echo $oUser->getAvatar()?>" width="30" height="30"/>
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

												if (method_exists($oEntity, 'checkBackendAccess')
													&& !$oEntity->checkBackendAccess($oAdmin_Form_Action->name, $oCurrentUser))
												{
													continue;
												}

												$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

												$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
													? $Admin_Word_Value->name
													: '';

												$href = $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, NULL, 0, intval($oEntity->id));

												$onclick = $oAdmin_Form_Action->name == 'edit'
													? "$.modalLoad({path: '{$oAdmin_Form_Controller->getPath()}', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][{$oEntity->id}]=1&event_id={$oEvent->id}', windowId: '{$windowId}'}); return false"
													: $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, NULL, 0, intval($oEntity->id));

												// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
												if ($oAdmin_Form_Action->confirm)
												{
													$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$windowId} #row_0_{$oEntity->id}').toggleHighlight(); } else {{$onclick}} return res;";
												}
												?><a onclick="mainFormLocker.unlock(); <?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
											}
										}
										?>
									</div>
								</div>
								<div class="timeline-body">
									<?php echo nl2br(htmlspecialchars($oEntity->text))?>

									<div class="small gray"><span><a class="gray" href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][<?php echo $oUser->id?>]=1" onclick="$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oUser->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>'}); return false" title="<?php echo htmlspecialchars($oUser->getFullName())?>"><?php echo htmlspecialchars($oUser->getFullName())?></a></span><span class="pull-right"><?php echo date('H:i', $iDatetime)?></span></div>
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