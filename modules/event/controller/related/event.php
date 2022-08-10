<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Related_Event
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		// $aEntities = array_reverse($aEntities);

		?>
			<div class="row">
				<div class="col-xs-12">
					<?php
					foreach ($aEntities as $oEvent)
					{
						$iDatetime = Core_Date::sql2timestamp($oEvent->datetime);

						?><div class="well well-shop-items well-bordered">
							<div class="pull-right lead-shop-item-actions">
							<?php
							// Отображать в списке действий
							if ($oAdmin_Form->show_operations)
							{
								$aAllowed_Admin_Form_Actions = $oAdmin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

								$path = $oAdmin_Form_Controller->getPath();

								foreach ($aAllowed_Admin_Form_Actions as $oAdmin_Form_Action)
								{
									$aAllowedActions = array('edit', 'markDeleted');

									// Отображаем действие, только если разрешено.
									if (!$oAdmin_Form_Action->single || !in_array($oAdmin_Form_Action->name, $aAllowedActions))
									{
										continue;
									}

									if (method_exists($oEvent, 'checkBackendAccess') && !$oEvent->checkBackendAccess($oAdmin_Form_Action->name, $oUser))
									{
										continue;
									}

									$Admin_Word_Value = $oAdmin_Form_Action->Admin_Word->getWordByLanguage($oAdmin_Language->id);

									$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
										? $Admin_Word_Value->name
										: '';

									$href = $oAdmin_Form_Controller->getAdminActionLoadHref($path, $oAdmin_Form_Action->name, NULL, 0, $oEvent->id, $additionalParams, 10, 1, NULL, NULL, 'list');

									$onclick = $oAdmin_Form_Action->name == 'edit'
										? $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $path, 'action' => $oAdmin_Form_Action->name, 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oEvent->id, 'additionalParams' => $additionalParams, 'width' => '90%'))
										: $oAdmin_Form_Controller->getAdminActionLoadAjax($path, $oAdmin_Form_Action->name, NULL, 0, $oEvent->id, $additionalParams, 10, 1, NULL, NULL, 'list');

									// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
									if ($oAdmin_Form_Action->confirm)
									{
										$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$windowId} #row_0_{$oEvent->id}').toggleHighlight(); } else {mainFormLocker.unlock(); {$onclick}} return res;";
									}
									?><a onclick="<?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
								}
							}
							?>
						</div>
						<?php echo $oEvent->showContent($oAdmin_Form_Controller)?>
						<div class="small gray well-info">
							<?php
							$oEventCreator = $oEvent->getCreator();

							if (!is_null($oEventCreator))
							{
								?><span class="gray"><?php $oEventCreator->showLink($oAdmin_Form_Controller->getWindowId())?></span><?php
							}
							?>
							<span class="pull-right"><?php echo date('H:i', $iDatetime)?></span>
						</div>
					</div><?php
					}
					?>
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