<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Related_Event
 *
 * @package HostCMS
 * @subpackage Event
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Controller_Related_Event extends Admin_Form_Controller_View
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

		$oUser = Core_Auth::getCurrentUser();

		// При показе формы могут быть добавлены сообщения в message, поэтому message показывается уже после отработки формы
		ob_start();
		?>
		<div class="table-toolbar">
			<?php $oAdmin_Form->Admin_Form_Actions->checkAllowedActionForUser($oUser, 'edit') && $this->_Admin_Form_Controller->showFormMenus()?>
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

		$sShowNavigation = $oAdmin_Form_Controller->getTotalCount() > $oAdmin_Form_Controller->limit;

		if ($sShowNavigation)
		{
		?><div class="DTTTFooter">
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-8"></div>
				<div class="col-xs-12 col-sm-6 col-md-4">
					<?php $oAdmin_Form_Controller->pageNavigation()?>
				</div>
			</div>
		</div><?php
		}

		$content = ob_get_clean();

		$oAdmin_View
			->content($content)
			->message($oAdmin_Form_Controller->getMessage())
			->show();

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

		if (is_null($oUser))
		{
			return FALSE;
		}

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $oAdmin_Form_Controller->getWindowId();

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();
		?>
			<div class="row">
				<div class="col-xs-12">
					<table class="admin-table table table-hover table-striped">
						<tbody>
						<?php
						$additionalParams = Core_Str::escapeJavascriptVariable(
							str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
						);

						foreach ($aEntities as $oEvent)
						{
							$sCompletedIco = $oEvent->getCompletedIco();

							$sImportantIco = '<i class="fa fa-exclamation-circle ' . ($oEvent->important ? 'red' : 'fa-inactive') . '"></i>';

							$sImportantHtml = $oEvent->checkPermission2ChangeImportant($oUser)
								? '<a href="' . $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'changeImportant', NULL, 0, intval($oEvent->id)) . '" onclick="mainFormLocker.unlock(); ' . $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'changeImportant', NULL, 0, intval($oEvent->id)) . '">' . $sImportantIco . '</a>'
								: $sImportantIco;
							?>
							<tr class="related-event-row">
								<td class="text-center" width="30px">
									<a href="<?php echo $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'changeCompleted', NULL, 0, intval($oEvent->id))?>" onclick="mainFormLocker.unlock(); <?php echo $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'changeCompleted', NULL, 0, intval($oEvent->id))?>"><?php echo $sCompletedIco?></a>
								</td>
								<td class="text-center" width="30px"><?php echo $sImportantHtml?></td>
								<td><?php echo $oEvent->nameBackend(NULL, $oAdmin_Form_Controller)?></td>
								<td class="hidden-xxs" width="25%">
									<?php
									$aEvent_Users =	$oEvent->Users->findAll();

									foreach ($aEvent_Users as $oEvent_User)
									{
									?>
									<div class="user-info">
										<div class="user-image">
											<img src="<?php echo $oEvent_User->getAvatar()?>" />
										</div>
										<div class="user-name">
											<a class="darkgray" href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][<?php echo $oEvent_User->id?>]=1" onclick="$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oEvent_User->id?>]=1', windowId: 'id_content'}); return false"><?php echo htmlspecialchars($oEvent_User->getFullName())?></a>
										</div>
									</div>
									<?php
									}
									?>
								</td>
								<td class="hidden-xxs" style="text-align:left; width: 25%">
									<?php echo $oEvent->event_group_idBackend(NULL, $oAdmin_Form_Controller)?>

									<div class="pull-right related-event-actions">
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

											if (method_exists($oEvent, 'checkBackendAccess')
												&& !$oEvent->checkBackendAccess($oAdmin_Form_Action->name, $oUser))
											{
												continue;
											}

											$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

											$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
												? $Admin_Word_Value->name
												: '';

											$onclick = $oAdmin_Form_Action->name == 'edit'
												// ? "$.modalLoad({path: '{$oAdmin_Form_Controller->getPath()}', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][{$oEvent->id}]=1&{$additionalParams}', windowId: '{$windowId}'}); return false"
												? $oAdmin_Form_Controller->getAdminActionModalLoad($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, 'modal', 0, $oEvent->id, $additionalParams)
												: $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, NULL, 0, $oEvent->id);

											// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
											if ($oAdmin_Form_Action->confirm)
											{
												$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$windowId} #row_0_{$oEvent->id}').toggleHighlight(); } else {{$onclick}} return res;";
											}
											?>
											<span onclick="mainFormLocker.unlock(); <?php echo $onclick?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></span>
											<?php
										}
									}
									?>
									</div>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div>
			</div>

			<script>
			$(function(){
				$('#<?php echo $windowId?> :input').on('click', function() { mainFormLocker.unlock() });
			});
			</script>
		<?php

		return $this;
	}
}