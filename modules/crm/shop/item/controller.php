<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Shop_Item_Controller
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Crm_Shop_Item_Controller extends Admin_Form_Controller_View
{
	/**
	 * Additional params
	 * @var string
	 */
	protected $_additionalParams = NULL;

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
	 * Executes the business logic.
	 * @return self
	 */
	public function execute()
	{
		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;

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
						?>

						<div class="message" data-user-id="<?php echo $oUser->id?>" data-message-id="<?php echo $oEntity->id?>">
							<span class="d-flex palegreen">
								<i class="avatar fa-solid fa-cart-shopping"></i>
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
												$aAllowedActions = array('edit', 'markDeleted');

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

												$href = $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, NULL, 0, $oEntity->id, $additionalParams, 10, 1, NULL, NULL, 'list');

												$entityAdditionalParams = $additionalParams . "&parentWindowId={$this->_windowId}";

												$onclick = $oAdmin_Form_Action->name == 'edit'
													? $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => $oAdmin_Form_Controller->getPath(), 'action' => $oAdmin_Form_Action->name, 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $oEntity->id, 'additionalParams' => $entityAdditionalParams, 'width' => '90%'))
													: $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), $oAdmin_Form_Action->name, NULL, 0, $oEntity->id, $entityAdditionalParams, 10, 1, NULL, NULL, 'list');

												// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
												if ($oAdmin_Form_Action->confirm)
												{
													$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$this->_windowId} #row_0_{$oEntity->id}').toggleHighlight(); } else {mainFormLocker.unlock(); {$onclick}} return res;";
												}
												?><a onclick="<?php echo htmlspecialchars($onclick)?>" href="<?php echo htmlspecialchars($href)?>" title="<?php echo htmlspecialchars($name)?>"><i class="<?php echo htmlspecialchars($oAdmin_Form_Action->icon)?>"></i></a><?php
											}
										}
										?>
									</span>
								</div>
								<div class="message-body">
									<?php echo $oEntity->showContent($oAdmin_Form_Controller)?>
								</div>
								<div class="message-footer">
									<span class="timestamp"><?php echo date('H:i', $iDatetime)?></span>
								</div>
							</div>
						</div>
					<?php
					}
				}
				?>
			</div>
		</div>
		<?php

		return $this;
	}
}