<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Dms_Document_Controller_View
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Dms_Document_Controller_View extends Admin_Form_Controller_View
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
		$oAdmin_Form_Controller->setDatasetLimits()->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
		);

		$aEntities = $aDatasets[0]->load();

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

		if (count($aEntities))
		{
			?><ul class="timeline crm-note-list timeline-left timeline-no-vertical"><?php
			$prevDate = NULL;

			$i = 0;

			foreach ($aEntities as $oDms_Document)
			{
				$color = $aColors[$i % $iCountColors];

				$iDatetime = Core_Date::sql2timestamp($oDms_Document->created);
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
				<li class="timeline-inverted">
					<div class="timeline-badge palegreen">
						<i class="fa fa-columns"></i>
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
										if ($oAdmin_Form_Action->name == 'markDeleted' && !$oDms_Document->checkPermission2Delete($oUser))
										{
											continue;
										}

										if ($oAdmin_Form_Action->name == 'edit' && (!$oDms_Document->checkPermission2Edit($oUser) && !$oDms_Document->checkPermission2View($oUser)))
										{
											continue;
										}

										$action = $oAdmin_Form_Action->name;

										$path = '/admin/event/dms/document/index.php';

										$icon = $oAdmin_Form_Action->icon;

										if ($action == 'edit' && ($oDms_Document->checkPermission2Edit($oUser) || $oDms_Document->checkPermission2View($oUser)))
										{
											if ($oDms_Document->checkPermission2Edit($oUser))
											{
												$action = 'edit';
											}
											elseif ($oDms_Document->checkPermission2View($oUser))
											{
												$action = 'view';
												$path = '/admin/dms/document/index.php';
												$icon = 'fa fa-eye';
											}
										}

										$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

										$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
											? $Admin_Word_Value->name
											: '';

										$href = $oAdmin_Form_Controller->getAdminActionLoadHref($path, $action, NULL, 0, $oDms_Document->id, $additionalParams, 10, 1, NULL, NULL, 'list');

										$onclick = $action == 'edit' || $action == 'view'
											? $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $path, 'action' => $action, 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oDms_Document->id, 'additionalParams' => $additionalParams, 'width' => '90%'))
											: $oAdmin_Form_Controller->getAdminActionLoadAjax($path, $oAdmin_Form_Action->name, NULL, 0, $oDms_Document->id, $additionalParams, 10, 1, NULL, NULL, 'list');

										// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
										if ($oAdmin_Form_Action->confirm)
										{
											$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$windowId} #row_0_{$oDms_Document->id}').toggleHighlight(); } else {mainFormLocker.unlock(); {$onclick}} return res;";
										}
										?><a onclick="<?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($icon)?>"></i></a><?php
									}
								}
								?>
							</div>
						</div>
						<div class="timeline-body">
							<div class="semi-bold">
								<span><?php echo htmlspecialchars($oDms_Document->name)?></span><?php

								if (strlen($oDms_Document->numberBackend()))
								{
									?><span class="margin-left-5">№ <?php echo $oDms_Document->numberBackend()?></span><?php
								}

								if ($oDms_Document->classify)
								{
									?><i class="fa fa-lock margin-left-5" style="color: #ed4e2a" title="<?php echo Core::_('Dms_Document.classify_1')?>"></i><?php
								}
							?></div><?php

							if (strlen($oDms_Document->description))
							{
								?><div class="small gray"><?php echo nl2br(htmlspecialchars($oDms_Document->description))?></div><?php
							}

							?><div>
								<?php
								if ($oDms_Document->dms_document_type_id)
								{
									?><span class="margin-right-10"><?php echo $oDms_Document->dms_document_type_idBackend()?></span><?php
								}
								echo $oDms_Document->showDmsCommunication() . $oDms_Document->showDmsWorkflowExecutions($oAdmin_Form_Controller)?>
							</div><?php

							if ($oDms_Document->crm_project_id)
							{
								$oDms_Document->showCrmProjects($oAdmin_Form_Controller);
							}
							?>
							<div class="small gray view-item-info"><span class="gray"><?php $oDms_Document->User->showLink($oAdmin_Form_Controller->getWindowId())?></span><span class="pull-right"><?php echo date('H:i', $iDatetime)?></span></div>
						</div>
					</div>
				</li>
				<?php
			}
			?></ul><?php
		}
		?>

		<script>
			$(function(){
				$('#<?php echo $windowId?> :input').on('click', function() { mainFormLocker.unlock() });
			});
		</script>
		<?php

		return $this;
	}
}