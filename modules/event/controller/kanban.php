<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Kanban
 *
 * @package HostCMS
 * @subpackage Event
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Controller_Kanban extends Admin_Form_Controller_View
{
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
				<?php $this->_Admin_Form_Controller->showChangeViews()?>
			</div>
			<div class="clear"></div>
		</div>
		<?php
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

		$oAdmin_Form_Controller->applyEditable();
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

		$windowId = $oAdmin_Form_Controller->getWindowId();

		$oUser = Core_Auth::getCurrentUser();

		$oEvent_Statuses = Core_Entity::factory('Event_Status');
		$oEvent_Statuses->queryBuilder()
			->where('event_statuses.final', '=', 0)
			->clearOrderBy()
			->orderBy('event_statuses.sorting', 'ASC');

		$aEvent_Statuses = $oEvent_Statuses->findAll(FALSE);

		// Add default status
		$oDefaultStatus = new stdClass();
		$oDefaultStatus->id = 0;
		$oDefaultStatus->name = Core::_('Event.notStatus');
		$oDefaultStatus->color = '#aebec4';
		array_unshift($aEvent_Statuses, $oDefaultStatus);

		?><style><?php
		foreach ($aEvent_Statuses as $oEvent_Status)
		{
			$aStatuses[$oEvent_Status->id] = array(
				'name' => $oEvent_Status->name,
				'color' => $oEvent_Status->color
			);
			?>.event-status-<?php echo $oEvent_Status->id?> .well.bordered-left { border-left-color: <?php echo htmlspecialchars($oEvent_Status->color)?>} <?php
		}
		?></style><?php

		// Устанавливаем ограничения на источники
		$oAdmin_Form_Controller->setDatasetConditions();

		$aDatasets = $oAdmin_Form_Controller->getDatasets();

		$aEntities = $aDatasets[0]->load();
		?>
		<div class="container kanban-board">
			<div class="horizon-prev"><img src="/admin/images/scroll/l-arrow.png"></div>
			<div class="horizon-next"><img src="/admin/images/scroll/r-arrow.png"></div>
			<div class="row">
			<?php
				foreach ($aStatuses as $iEventStatusId => $aEventStatus)
				{
					?><div class="kanban-col col-xs-12 col-sm-4 col-md-3">
						<h5 style="color: <?php echo htmlspecialchars($aEventStatus['color'])?>; padding-bottom: 5px; border-bottom: 2px solid <?php echo htmlspecialchars($aEventStatus['color'])?>"><?php echo htmlspecialchars($aEventStatus['name'])?></h5>
						<ul id="entity-list-<?php echo $iEventStatusId?>" data-step-id="<?php echo $iEventStatusId?>" class="kanban-list connectedSortable event-status-<?php echo $iEventStatusId?>">
						<?php
						foreach ($aEntities as $key => $oEntity)
						{
							if ($oEntity->event_status_id == $iEventStatusId)
							{
								$oEventCreator = $oEntity->getCreator();
								$userIsEventCreator = !is_null($oEventCreator) && $oEventCreator->id == $oUser->id;
							?>
							<li id="event-<?php echo $oEntity->id?>" data-id="<?php echo $oEntity->id?>">
								<div class="well bordered-left">
									<div class="drag-handle"></div>
									<div class="row">
										<div class="col-xs-12 col-sm-6">
											<?php echo $oEntity->showType()?>
										</div>
										<div class="col-xs-12 col-sm-6 well-avatar text-align-right">
											<?php
											if (!$userIsEventCreator && $oEventCreator)
											{
											?>
												<img src="<?php echo $oEventCreator->getAvatar()?>" title="<?php echo htmlspecialchars($oEventCreator->getFullName())?>"/>
											<?php
											}
											?>

											<img src="<?php echo $oUser->getAvatar()?>" title="<?php echo htmlspecialchars($oUser->getFullName())?>"/>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-12 well-body">
											<span class="editable" id="apply_check_0_<?php echo $oEntity->id?>_fv_1226"><?php echo htmlspecialchars($oEntity->name)?></span>
										</div>
									</div>
									<?php
									if (strlen($oEntity->description))
									{
									?>
									<div class="row">
										<div class="col-xs-12 well-description">
											<span><?php echo htmlspecialchars($oEntity->description)?></span>
										</div>
									</div>
									<?php
									}
									?>
									<div class="row">
										<div class="col-xs-12">
										<?php echo $oEntity->relatedBackend(NULL, $oAdmin_Form_Controller)?>
										</div>
									</div>
									<div class="row">
										<div class="col-xs-12 well-description">
											<div class="event-date">
											<?php
											if ($oEntity->all_day)
											{
												echo Event_Controller::getDate($oEntity->start);
											}
											else
											{
												if (!is_null($oEntity->start) && $oEntity->start != '0000-00-00 00:00:00')
												{
													echo Event_Controller::getDateTime($oEntity->start);
												}

												if (!is_null($oEntity->start) && $oEntity->start != '0000-00-00 00:00:00'
													&& !is_null($oEntity->deadline) && $oEntity->deadline != '0000-00-00 00:00:00'
												)
												{
													echo ' — ';
												}

												if (!is_null($oEntity->deadline) && $oEntity->deadline != '0000-00-00 00:00:00')
												{
													?><strong><?php echo Event_Controller::getDateTime($oEntity->deadline)?></strong><?php
												}
											}
											?>
											</div>
										</div>
									</div>
									<?php
									if (strlen($oEntity->place))
									{
									?>
									<div class="row">
										<div class="col-xs-12 well-description">
											<span class="kanban-place"><i class="fa fa-map-marker black"></i> <?php echo htmlspecialchars($oEntity->place)?></span>
										</div>
									</div>
									<?php
									}
									?>
									<div class="edit-entity" onclick="$.modalLoad({path: '/admin/event/index.php', action: 'edit',operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oEntity->id?>]=1', windowId: 'id_content'});"><i class="fa fa-pencil"></i></div>
								</div>
							</li>
							<?php
							}
						}
						?>
						</ul>
					</div><?php
				}
				?>
			</div>

			<div class="kanban-action-wrapper hidden">
				<div class="kanban-actions text-align-center">
					<?php
					$oEvent_Statuses = Core_Entity::factory('Event_Status');
					$oEvent_Statuses->queryBuilder()
						->where('event_statuses.final', '=', 1)
						->clearOrderBy()
						->orderBy('event_statuses.sorting', 'ASC');

					$aEvent_Statuses = $oEvent_Statuses->findAll(FALSE);

					$count = count($aEvent_Statuses);

					$width = $count
						? 90 / $count
						: 100;

					$deleteWidth = $width == 100
						? 100
						: 10;

					$deleteColor = '#777';

					foreach ($aEvent_Statuses as $oEvent_Status)
					{
						?>
						<ul id="entity-list-<?php echo $oEvent_Status->id?>" data-step-id="<?php echo $oEvent_Status->id?>" data-id="<?php echo $oEvent_Status->id?>" data-background="<?php echo htmlspecialchars(Core_Str::hex2lighter($oEvent_Status->color, 0.88))?>" data-old-background="<?php echo htmlspecialchars($oEvent_Status->color)?>" style="width: <?php echo $width?>%; background-color: <?php echo htmlspecialchars($oEvent_Status->color)?>; color: #fff;" class="connectedSortable kanban-action-item"><div class="kanban-action-item-name"><?php echo htmlspecialchars($oEvent_Status->name)?></div><div class="return hidden"><i class="fa fa-undo"></i> <?php echo htmlspecialchars($oEvent_Status->name)?></div></ul>
						<?php
					}
					?>

					<ul data-id="0" data-background="<?php echo htmlspecialchars(Core_Str::hex2lighter($deleteColor, 0.88))?>" data-old-background="<?php echo htmlspecialchars($deleteColor)?>" style="width: <?php echo $deleteWidth?>%; background-color: <?php echo htmlspecialchars($deleteColor)?>; color: #fff;" class="connectedSortable kanban-action-item"><div class="kanban-action-item-name"><i class="fa fa-trash"></i></div><div class="return hidden"><i class="fa fa-undo"></i></div></ul>
				</div>
			</div>
		</div>
		<script>
		$(function() {
			$.sortableKanban({path: '/admin/event/index.php', container: '.kanban-board', windowId: '<?php echo $windowId?>'});
			$.showKanban('.kanban-board');
		});
		</script>
		<?php

		return $this;
	}
}