<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Events.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('datetime')
			->addSkipColumn('reminder_start')
			->addSkipColumn('parent_id')
			->addSkipColumn('result');

		if (!$object->id)
		{
			$object->parent_id = Core_Array::getGet('parent_id', 0, 'int');
			$object->crm_project_id = Core_Array::getGet('crm_project_id', 0, 'int');

			if (!is_null(Core_Array::getGet('event_status_id')))
			{
				$object->event_status_id = Core_Array::getGet('event_status_id', 0, 'int');
			}
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oAdditionalTab
			->add($oAdditionalRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oAdditionalRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oAdditionalRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oAdditionalRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		if ($this->_object->id)
		{
			$oAdditionalTab->move($this->getField('id')->divAttr(array('class' => 'form-group col-xs-12')), $oAdditionalRow1);
		}

		if (Core::moduleIsActive('crm_project'))
		{
			$oAdditionalTab->delete($this->getField('crm_project_id'));

			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$aCrmProjectsOptions = array(' … ');

			$aCrm_Projects = Core_Entity::factory('Crm_Project')->getAllBySite_id(CURRENT_SITE);
			foreach ($aCrm_Projects as $oCrm_Project)
			{
				$aCrmProjectsOptions[$oCrm_Project->id] = $oCrm_Project->name;
			}

			if (count($aCrm_Projects) < Core::$mainConfig['switchSelectToAutocomplete'])
			{
				$oSelect_Projects = Admin_Form_Entity::factory('Select');
				$oSelect_Projects
					->options($aCrmProjectsOptions)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->name('crm_project_id')
					->value($this->_object->crm_project_id)
					->caption(Core::_('Event.crm_project_id'));

				$oAdditionalRow2->add($oSelect_Projects);
			}
			else
			{
				$oCrmProjectInput = Admin_Form_Entity::factory('Input')
					->caption(Core::_('Event.crm_project_id'))
					->divAttr(array('class' => 'form-group col-xs-12'))
					->name('crm_project_name');

				if ($this->_object->crm_project_id)
				{
					$oCrm_Project = $this->_object->Crm_Project;
					$oCrmProjectInput->value($oCrm_Project->name . ' [' . $oCrm_Project->id . ']');
				}

				$oCrmProjectInputHidden = Admin_Form_Entity::factory('Input')
					->divAttr(array('class' => 'form-group col-xs-12 hidden'))
					->name('crm_project_id')
					->value($this->_object->crm_project_id)
					->type('hidden');

				$oCore_Html_Entity_Script_Crm_Project = Core_Html_Entity::factory('Script')
				->value("
					$('#" . $windowId . " [name = crm_project_name]').autocomplete({
						source: function(request, response) {
							$.ajax({
								url: '/admin/crm/project/index.php?autocomplete=1',
								dataType: 'json',
								data: {
									queryString: request.term
								},
								success: function(data) {
									response(data);
								}
							});
						},
						minLength: 1,
						create: function() {
							$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
								return $('<li></li>')
									.data('item.autocomplete', item)
									.append($('<a>').text(item.label))
									.appendTo(ul);
							}

							$(this).prev('.ui-helper-hidden-accessible').remove();
						},
						select: function(event, ui) {
							$('#" . $windowId . " [name = crm_project_id]').val(ui.item.id);
						},
						open: function() {
							$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
						},
						close: function() {
							$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
						}
					});
				");

				$oAdditionalRow2
					->add($oCrmProjectInput)
					->add($oCrmProjectInputHidden)
					->add($oCore_Html_Entity_Script_Crm_Project);
			}
		}

		$oMainTab
			->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')), $oAdditionalRow3)
			->move($this->getField('last_modified')->divAttr(array('class' => 'form-group col-xs-12')), $oAdditionalRow4);

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		// Ответственные сотрудники
		$aResponsibleEmployees = array();

		$oUser = Core_Auth::getCurrentUser();

		$iCreatorUserId = 0;

		if ($this->_object->id)
		{
			$aEventUsers = $this->_object->Event_Users->findAll();

			foreach ($aEventUsers as $oEventUser)
			{
				$aResponsibleEmployees[] = $oEventUser->user_id;

				// Идентификатор создателя дела
				$oEventUser->creator && $iCreatorUserId = $oEventUser->user_id;
			}
		}
		else
		{
			// Ответственные при создании дела через сделку
			if (Core::moduleIsActive('deal') && !is_null(Core_Array::getGet('deal_id')))
			{
				$iDealId = intval(Core_Array::getGet('deal_id'));

				$oDeal = Core_Entity::factory('Deal')->getById($iDealId);

				if (!is_null($oDeal))
				{
					$aResponsibleEmployees[] = $oDeal->user_id;

					$aDeal_Step_Users = $oDeal->getCurrentDealStepUsers();

					foreach ($aDeal_Step_Users as $oDeal_Step_User)
					{
						$aResponsibleEmployees[] = $oDeal_Step_User->user_id;
					}
				}
			}
			else
			{
				$aResponsibleEmployees[] = $oUser->id;
			}

			$iCreatorUserId = $oUser->id;

			$oEvent_Type = Core_Entity::factory('Event_Type')->getByDefault(1);
			!is_null($oEvent_Type) && $this->_object->event_type_id = $oEvent_Type->id;
		}

		// Если сотрудник является участником дела, но не его создателем, то возможен только просмотр информации о деле.
		if ($this->_object->id && $iCreatorUserId != $oUser->id)
		{
			$oMainTab->clear();
			$oAdditionalTab->clear();

			$this->title($this->_object->name);

			$oMainTab
				->add(Admin_Form_Entity::factory('Div')->class('row')
						->add($oDivLeft = Admin_Form_Entity::factory('Div')->class('col-xs-12 col-md-6 col-lg-7 left-block'))
						->add($oDivRight = Admin_Form_Entity::factory('Div')->class('col-xs-12 col-md-6 col-lg-5 right-block'))
				);

			$oMainTab
				->add(Admin_Form_Entity::factory('Script')
					->value('
						$(function(){
							var timer = setInterval(function(){
								if ($("#' . $windowId . ' .left-block").height())
								{
									clearInterval(timer);

									$("#' . $windowId . ' .right-block").find("#' . $windowId . '-event-notes").slimscroll({
										height: $("#' . $windowId . ' .left-block").height() + 150,
										color: "rgba(0, 0, 0, 0.3)",
										size: "5px"
									});
								}
							}, 500);
						});
					'));

			$oDivLeft
				->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow3_1 = Admin_Form_Entity::factory('Div')->class('row margin-top-20'))
				->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row profile-container'))
				->add($oMainClientsRow = Admin_Form_Entity::factory('Div')->class('row event-clients-row'))
				->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row profile-container'))
				->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row hidden'))
				->add($oMainRow10 = Admin_Form_Entity::factory('Div')->class('row profile-container'))
				->add($oMainRow11 = Admin_Form_Entity::factory('Div')->class('row'))
				;

			$oDivRight
				->add($oMainRowNotes = Admin_Form_Entity::factory('Div')->class('row'));

			$oMainRowNotes->add(
				Admin_Form_Entity::factory('Div')
					->class('col-xs-12')
					->add(
						Admin_Form_Entity::factory('Div')
							->controller($this->_Admin_Form_Controller)
							->id("{$windowId}-event-notes")
							->add(
								$this->_object->id
									? $this->_addEventNotes()
									: Admin_Form_Entity::factory('Code')->html(
										Core_Message::get(Core::_('Event.enable_after_save'), 'warning')
									)
							)
					)
					// ->execute()
			);

			$oMainRow1->add(
				Admin_Form_Entity::factory('Code')
					->html('<div class="form-group col-xs-12 semi-bold">' . nl2br(htmlspecialchars($this->_object->description)) . '</div>')
			);

			if (Core::moduleIsActive('dms'))
			{
				$oMainRow1->add(
					Admin_Form_Entity::factory('Code')
						->html('<div class="form-group col-xs-12">' . $this->_object->showDocuments($this->_Admin_Form_Controller) . '</div>')
				);
			}

			$deadlineClass = $this->_object->deadline()
				? 'deadline'
				: '';

			// Время
			if ($this->_object->all_day)
			{
				$oMainRow2->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12 col-md-3')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('
									<div class="form-group">
										<span class="caption">' . Core::_('Event.all_day_view') . '</span>
										<span class="darkgray"><i class="fa fa-clock-o ' . $deadlineClass . '" style="margin-right: 5px;"></i><span class="' . $deadlineClass . '">' . Event_Controller::getDate($this->_object->start) . '</span></span>
									</div>
								')
						)
				)->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12 col-md-3')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('
									<div class="form-group">
										<span class="caption">' . Core::_('Event.datetime_view') . '</span>
										<span class="darkgray"><i class="fa fa-clock-o" style="margin-right: 5px;"></i><span>' . Event_Controller::getDateTime($this->_object->datetime) . '</span></span>
									</div>
								')
						)
				);
			}
			else
			{
				$oMainRow2->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12 col-md-4')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('
									<div class="form-group">
										<span class="caption">' . Core::_('Event.start_view') . '</span>
										<span class="darkgray"><i class="fa fa-clock-o" style="margin-right: 5px;"></i><span>' . Event_Controller::getDateTime($this->_object->start) . '</span></span>
									</div>
								')
						)
				)->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12 col-md-4')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('
									<div class="form-group">
										<span class="caption">' . Core::_('Event.datetime_view') . '</span>
										<span class="darkgray"><i class="fa fa-clock-o" style="margin-right: 5px;"></i><span>' . Event_Controller::getDateTime($this->_object->datetime) . '</span></span>
									</div>
								')
						)
				)->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12 col-md-4')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('
									<div class="form-group">
										<span class="caption">' . Core::_('Event.deadline_view') . '</span>
										<span class="darkgray"><i class="fa fa-clock-o ' . $deadlineClass . '" style="margin-right: 5px;"></i><span class="' . $deadlineClass . '">' . Event_Controller::getDateTime($this->_object->deadline) . '</span></span>
									</div>
								')
						)
				);
			}

			// Тип
			if ($this->_object->event_type_id)
			{
				ob_start();
				$this->_object->showType();

				$oMainRow3->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12 col-md-4')
						->add(
							Admin_Form_Entity::factory('Code')
								->html(ob_get_clean())
						)
				);
			}

			// Группа
			if ($this->_object->event_group_id)
			{
				$oEvent_Group = Core_Entity::factory('Event_Group', $this->_object->event_group_id);

				$sEventGroupName = $oEvent_Group->name;
				$sEventGroupColor = $oEvent_Group->color;
			}
			else
			{
				$sEventGroupName = Core::_('Event.notGroup');
				$sEventGroupColor = '#aebec4';
			}

			$oMainRow3->add(
				Admin_Form_Entity::factory('Div')
					->class('col-xs-12 col-md-4')
					->add(
						Admin_Form_Entity::factory('Code')
							->html('
								<div class="event-group">
									<i class="fa fa-circle" style="margin-right: 5px; color: ' . htmlspecialchars($sEventGroupColor) . '"></i><span style="color: ' . htmlspecialchars($sEventGroupColor) . '">' . htmlspecialchars($sEventGroupName) . '</span>
								</div>
							')
					)
			);

			// Статус
			if ($this->_object->event_status_id)
			{
				$oEvent_Status = Core_Entity::factory('Event_Status', $this->_object->event_status_id);

				$sEventStatusName = $oEvent_Status->name;
				$sEventStatusColor = $oEvent_Status->color;
			}
			else
			{
				$sEventStatusName = Core::_('Event.notStatus');
				$sEventStatusColor = '#aebec4';
			}

			$oMainRow3->add(
				Admin_Form_Entity::factory('Div')
					->class('col-xs-12 col-md-4')
					->add(
						Admin_Form_Entity::factory('Code')
							->html('
								<div class="event-status">
									<i class="fa fa-circle" style="margin-right: 5px; color: ' . htmlspecialchars($sEventStatusColor) . '"></i><span style="color: ' . htmlspecialchars($sEventStatusColor) . '">' . htmlspecialchars($sEventStatusName) . '</span>
								</div>
							')
					)
			);

			$oMainRow3_1->add(
				Admin_Form_Entity::factory('Div')
					->class('col-xs-12 col-md-4')
					->add(
						Admin_Form_Entity::factory('Code')
							->html('
								<div class="form-group">
									<span class="caption">' . Core::_('Event.finish_view') . '</span>
									<span class="darkgray"><i class="fa fa-clock-o" style="margin-right: 5px;"></i><span>' . Event_Controller::getDateTime($this->_object->finish) . '</span></span>
								</div>
							')
					)
			);

			// Важное
			if ($this->_object->important)
			{
				$oMainRow3_1->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12 col-md-3')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('
									<div class="event-status">
										<i class="fa-solid fa-fire fire"></i><span class="darkorange"> ' . Core::_('Event.important_view') . '</span>
									</div>
								')
						)
				);
			}

			// Клиенты, связанные с событием
			$aEvent_Siteusers = $this->_object->Event_Siteusers->findAll(FALSE);
			if (count($aEvent_Siteusers))
			{
				$eventUsers = '';

				$oMainRow4->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('<h6 class="row-title before-darkorange">' . Core::_('Event.event_siteusers') . '</h6>')
						)
				);

				$aObjects = array();

				foreach ($aEvent_Siteusers as $oEvent_Siteuser)
				{
					if ($oEvent_Siteuser->siteuser_company_id)
					{
						$oSiteuser_Company = $oEvent_Siteuser->Siteuser_Company;

						$avatar = $oSiteuser_Company->getAvatar();

						$aDirectory_Phones = $oSiteuser_Company->Directory_Phones->findAll(FALSE);
						$phone = isset($aDirectory_Phones[0])
							? htmlspecialchars($aDirectory_Phones[0]->value)
							: '';

						$aDirectory_Emails = $oSiteuser_Company->Directory_Emails->findAll(FALSE);
						$email = isset($aDirectory_Emails[0])
							? htmlspecialchars($aDirectory_Emails[0]->value)
							: '';

						$aObjects[] = array(
							'id' => $oSiteuser_Company->id,
							'value' => 'company_' . $oSiteuser_Company->id,
							'name' => htmlspecialchars($oSiteuser_Company->name),
							'avatar' => $avatar,
							'phone' => $phone,
							'email' => $email,
							'type' => 'company',
						);
					}
					elseif ($oEvent_Siteuser->siteuser_person_id)
					{
						$oSiteuser_Person = $oEvent_Siteuser->Siteuser_Person;

						$avatar = $oSiteuser_Person->getAvatar();
						$fullName = $oSiteuser_Person->getFullName();

						$aDirectory_Phones = $oSiteuser_Person->Directory_Phones->findAll(FALSE);
						$phone = isset($aDirectory_Phones[0])
							? htmlspecialchars($aDirectory_Phones[0]->value)
							: '';

						$aDirectory_Emails = $oSiteuser_Person->Directory_Emails->findAll(FALSE);
						$email = isset($aDirectory_Emails[0])
							? htmlspecialchars($aDirectory_Emails[0]->value)
							: '';

						$aObjects[] = array(
							'id' => $oSiteuser_Person->id,
							'value' => 'person_' . $oSiteuser_Person->id,
							'name' => htmlspecialchars($fullName),
							'avatar' => $avatar,
							'phone' => $phone,
							'email' => $email,
							'type' => 'person',
						);
					}
				}

				$oAdmin_Form = Core_Entity::factory('Admin_Form', 220);
				$oAdminUser = Core_Auth::getCurrentUser();

				foreach ($aObjects as $aObject)
				{
					if (!is_null($aObject['id']))
					{
						$phone = strlen($aObject['phone'])
							? $aObject['phone']
							: '';

						$email = strlen($aObject['email'])
							? '<a href="mailto:' . $aObject['email'] . '">' . $aObject['email'] . '</a>'
							: '';

						$dataset = $aObject['type'] == 'company'
							? 0
							: 1;

						$imgLink = $oAdmin_Form->Admin_Form_Actions->checkAllowedActionForUser($oAdminUser, 'view')
							? '<a href="/admin/siteuser/representative/index.php?hostcms[action]=view&hostcms[checked][' . $dataset . '][' . $aObject['id'] . ']=1" onclick="$.modalLoad({path: \'/admin/siteuser/representative/index.php\', action: \'view\', operation: \'modal\', additionalParams: \'hostcms[checked][' . $dataset . '][' . $aObject['id'] . ']=1\', windowId: \'id_content\'}); return false"></a>'
							: '';

						$eventUsers .= '
							<div class="col-xs-12 col-sm-6 user-block">
								<div class="databox">
									<div class="databox-left no-padding">
										<div class="img-wrapper">
											<img class="databox-user-avatar" src="' . $aObject['avatar'] . '"/>' . $imgLink . '
										</div>
									</div>
									<div class="databox-right bg-whitesmoke">
										<div class="databox-text">
											<div class="semi-bold">' . $aObject['name'] . '</div>
											<div class="darkgray">' . $phone . '</div>
											<div>' . $email . '</div>
										</div>
										<div class="delete-responsible-user" onclick="$.dealRemoveUserBlock($(this))">
											<i class="fa fa-times"></i>
										</div>
									</div>
								</div>
								<input type="hidden" name="deal_siteusers[]" value="' . $aObject['value'] . '"/>
							</div>
						';
					}
				}

				$oMainClientsRow->add(
					Admin_Form_Entity::factory('Code')
					->html($eventUsers)
				);
			}

			// Место
			if (strlen($this->_object->place))
			{
				$oMainRow6->add(
						Admin_Form_Entity::factory('Div')
							->class('col-xs-12')
							->add(
								Admin_Form_Entity::factory('Code')
									->html('
										<i class="fa fa-map-marker azure"></i><span class="margin-left-5 azure">' . htmlspecialchars($this->_object->place) . '</span>
									')
							)
				);
			}

			// Ответственные сотрудники
			$aEvent_Users = $this->_object->Event_Users->findAll(FALSE);
			if (count($aEvent_Users))
			{
				$oMainRow7->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('<h6 class="row-title before-palegreen">' . Core::_('Event.event_users') . '</h6>')
						)
				);

				foreach ($aEvent_Users as $oEvent_User)
				{
					$oUser = $oEvent_User->User;

					$oEventCreator = $this->_object->getCreator();
					$bCreator = !is_null($oEventCreator) && $oEventCreator->id == $oUser->id;

					if ($oUser->id)
					{
						$aCompany_Department_Post_Users = $oUser->Company_Department_Post_Users->findAll();

						$sUserPost = isset($aCompany_Department_Post_Users[0])
							? $aCompany_Department_Post_Users[0]->Company_Post->name
							: '';

						$col = 4;

						if (count($aEvent_Users) < 4)
						{
							$col = 12 / count($aEvent_Users);
						}

						ob_start();
						?>
						<div class="col-xs-12 col-sm-6 col-lg-<?php echo $col?>">
							<div class="databox databox-graded">
								<div class="databox-left no-padding">
									<img class="databox-user-avatar" src="<?php echo $oUser->getAvatar()?>">
								</div>
								<div class="databox-right padding-top-20 bg-whitesmoke">
									<div class="databox-stat orange radius-bordered">
										<div class="databox-text black semi-bold darkgray event-user-view"><?php $oUser->showLink($this->_Admin_Form_Controller->getWindowId(), $oUser->getFullName()); echo ($bCreator ? '<i title="' . Core::_('Event.creator') . '" class="fa fa-star gold"></i>' : '')?></div>
										<div class="databox-text darkgray"><?php echo htmlspecialchars($sUserPost)?></div>
									</div>
								</div>
							</div>
						</div>
						<?php
						$oMainRow8->add(
							Admin_Form_Entity::factory('Code')
								->html(ob_get_clean())
						);
					}
				}
			}

			// Результат
			$oMainRow9->add(
				Admin_Form_Entity::factory('Input')
					->type('hidden')
					->name('id')
					->value($this->_object->id)
			);

			$oEvent_Type = $this->_object->Event_Type;

			$successfully = strlen(trim($oEvent_Type->successfully))
				? htmlspecialchars($oEvent_Type->successfully)
				: Core::_('Event_Type.successfully');

			$failed = strlen(trim($oEvent_Type->failed))
				? htmlspecialchars($oEvent_Type->failed)
				: Core::_('Event_Type.failed');

			// Файлы
			$aEvent_Attachments = $this->_object->Event_Attachments->findAll(FALSE);
			if (count($aEvent_Attachments))
			{
				$oMainRow10->add(
					Admin_Form_Entity::factory('Div')
						->class('col-xs-12')
						->add(
							Admin_Form_Entity::factory('Code')
								->html('<h6 class="row-title before-warning">' . Core::_('Event.event_files') . '</h6>')
						)
				);

				foreach ($aEvent_Attachments as $oEvent_Attachment)
				{
					$textSize = $oEvent_Attachment->getTextSize();

					ob_start();
					Core_Html_Entity::factory('I')
						->class(Core_File::getIcon($oEvent_Attachment->file_name))
						->execute();
					$icon_file_img = ob_get_clean();

					ob_start();
					Core_Html_Entity::factory('Strong')
						->value(" ({$textSize})")
						->execute();

					$oMainRow10->add(
						Admin_Form_Entity::factory('Code')
							->html('
								<div class="form-group col-xs-12">
									' . $icon_file_img . ' <a href="/admin/event/index.php?downloadFile=' . $oEvent_Attachment->id . '" target="_blank">' . $oEvent_Attachment->file_name . '</a> ' . ob_get_clean() . '
								</div>
							')
					);
				}
			}

			$oMainRow10->add(
				Admin_Form_Entity::factory('Radiogroup')
					->radio(array(
						1 => $successfully,
						-1 => $failed,
					))
					->ico(array(
						1 => 'fa-check',
						-1 => 'fa-ban',
					))
					->colors(array('btn-palegreen', 'btn-darkorange'))
					->name('completed')
					->divAttr(array('class' => 'form-group col-xs-12 type-states'))
					->value($this->_object->completed)
			)->add(Admin_Form_Entity::factory('Script')
				->value("
					$(function(){
						$('#{$windowId} .type-states').on('click', 'label.checkbox-inline', function(e) {
							e.preventDefault();

							var radio = $(this).find('input[type=radio]');
							radio.prop('checked', !radio.is(':checked'));
						});
					});")
			);

			return $this;
		}

		$completedIcon = '';

		if ($this->_object->completed == 1)
		{
			$completedIcon = 'fa-solid fa-circle-check palegreen';
		}
		elseif ($this->_object->completed == -1)
		{
			$completedIcon = 'fa-solid fa-xmark darkorange';
		}

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
		$oAdmin_Form_Entity_Code->html(Core::_('Event.edit_title', $this->_object->name) . '<i class="' . $completedIcon . ' margin-left-5"></i>');

		$this->pageTitle($this->_object->id
			? $oAdmin_Form_Entity_Code
			: Core::_('Event.add_title'));

		$this->title($this->_object->id
			? Core::_('Event.edit_title', $this->_object->name, FALSE)
			: Core::_('Event.add_title')
		);

		$bHideAdditionalRow = $this->_object->place == ''
			&& !$this->_object->reminder_value
			&& !$this->_object->Event_Attachments->getCount()
			&& !$this->_object->Crm_Notes->getCount()
			&& !$this->_object->Events->getCount();

		$oMainTab
			->add(Admin_Form_Entity::factory('Div')->class('row')
				->add($oDivLeft = Admin_Form_Entity::factory('Div')->class('col-xs-12 col-md-6 col-lg-7 left-block'))
				->add($oDivRight = Admin_Form_Entity::factory('Div')->class('col-xs-12 col-md-6 col-lg-5 right-block'))
			);

		$oDivLeft
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRowEventStartButtons = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRowDuration = Admin_Form_Entity::factory('Div')->class('row duration-row'))
			->add($oMainRowTimeSlider = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRowSettingsShow = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3_2 = Admin_Form_Entity::factory('Div')->class('row settings-row hidden'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRowAdditionalShow = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row additional-row' . ($bHideAdditionalRow ? ' hidden' : '')))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row additional-row event-user-id' . ($bHideAdditionalRow ? ' hidden' : '')))
			// ->add($oMainRowResultShow = Admin_Form_Entity::factory('Div')->class('row' . ($this->_object->completed ? ' hidden' : '')))
			->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row result-row hidden'))
			->add($oMainRowScripts = Admin_Form_Entity::factory('Div')->class('row'));

		$oDivRight
			// ->add($oMainRowAttachments = Admin_Form_Entity::factory('Div')->class('row additional-row'));
			->add($oMainRowAttachments = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->delete($this->getField('duration'))
			->delete($this->getField('duration_type'))
			->delete($this->getField('reminder_type'));

		$oAdditionalTab
			->delete($this->getField('event_type_id'))
			->delete($this->getField('event_group_id'))
			->delete($this->getField('event_status_id'));

		$oDivTimeSlider = Admin_Form_Entity::factory('Div')
			->id('ts')
			->class('time-slider');

		$oDivWrapTimeSlider = Admin_Form_Entity::factory('Div')
			->add($oDivTimeSlider)
			->class('col-xs-12')
			->style('margin-top: -50px; height: 130px');

		$oMainRowTimeSlider->add($oDivWrapTimeSlider);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		//Массив названий кнопок быстрого переключения даты начала события
		$masEventStartButtonTitle = array();
		$masEventStartButtonTitle["'" . Core_Date::timestamp2date(time()) . "'"] = Core::_('Event.eventStartButtonTitleToday');
		$masEventStartButtonTitle["'" . Core_Date::timestamp2date(time() + 3600 * 24) . "'"] = Core::_('Event.eventStartButtonTitleTomorrow');
		$masEventStartButtonTitle["'" . Core_Date::timestamp2date(time() + 3600 * 24 * 2) . "'"] = Core::_('Event.eventStartButtonTitleDayAfterTomorrow');
		$masEventStartButtonTitle["'" . Core_Date::timestamp2date(time() + 3600 * 24 * 3) . "'"] = Core::_('Event.eventStartButtonTitle3Days');

		$htmlEventStartButtons = '';
		$startDayNum = 0;

		// Формирование кнопок быстрого переключения даты начала события
		foreach ($masEventStartButtonTitle as $eventStartDate => $eventStartTitle)
		{
			$htmlEventStartButtons .= '<a href="#" data-start-day="' . $startDayNum . '" class="btn' . ((!$this->_object->id && !$startDayNum) || ($eventStartDate == ("'" . Core_Date::sql2date($this->_object->start) . "'")) ? ' active' : '') . '">' . $eventStartTitle . '</a>';
			$startDayNum++;
		}

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');

		$oAdmin_Form_Entity_Code->html(
			'<div class="col-xs-12 col-md-9 !text-center" style="z-index: 10">
				<div id="eventStartButtonsGroup" class="btn-group margin-bottom-15">' . $htmlEventStartButtons . '</div>
			</div>
			<script>
				$("#' . $windowId . ' #eventStartButtonsGroup").on({
					"mouseover": function (){
						!$(this).hasClass("active") && $(this).addClass("btn-default");
					},
					"mouseout": function (){
						$(this).removeClass("btn-default");
					}
				}, "[data-start-day]")
			</script>'
		);

		$oMainRowEventStartButtons->add($oAdmin_Form_Entity_Code);

		$oMainTab->move($this->getField('all_day')->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 margin-top-5 text-align-right')), $oMainRowEventStartButtons);

		$aDurationTypes = array(Core::_('Event.periodMinutes'), Core::_('Event.periodHours'), Core::_('Event.periodDays'));

		$oDiv_Duration = Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 col-sm-6 col-md-4 amount-currency')
			->add(Admin_Form_Entity::factory('Input')
				->name('duration')
				->id('duration')
				->value($this->_object->id ? $this->_object->duration : 1)
				->caption(Core::_("Event.duration"))
				->divAttr(array('class' => ''))
			)
			->add(
				Admin_Form_Entity::factory('Select')
					->class('form-control no-padding-left no-padding-right')
					->divAttr(array('class' => ''))
					->options($aDurationTypes)
					->name('duration_type')
					->value($this->_object->id ? $this->_object->duration_type : 1)
			);

		$oMainRowDuration->add($oDiv_Duration);

		$oMainTab->delete($this->getField('reminder_value'));

		$oDiv_ReminderValue = Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 col-sm-6 col-md-4 amount-currency')
			->add(Admin_Form_Entity::factory('Input')
				->name('reminder_value')
				->id('reminder_value')
				->value(!$this->_object->id || !$this->_object->reminder_value ? '' : $this->_object->reminder_value)
				->caption(Core::_("Event.reminder_value"))
				->divAttr(array('class' => ''))
			)
			->add(
				Admin_Form_Entity::factory('Select')
					->class('form-control no-padding-left no-padding-right')
					->divAttr(array('class' => ''))
					->options($aDurationTypes)
					->name('reminder_type')
					->value($this->_object->reminder_type)
			);

		$oMainRowDuration->add($oDiv_ReminderValue);

		$oMainTab
			->move($this->getField('busy')->divAttr(array('class' => 'form-group col-md-2 col-xs-6 margin-top-21')), $oMainRowDuration)
			->move($this->getField('important')->class('colored-danger')->divAttr(array('class' => 'form-group col-md-2 col-xs-6 margin-top-21')), $oMainRowDuration);

		$oMainRowSettingsShow->add(Admin_Form_Entity::factory('Span')
			->divAttr(array('class' => 'form-group col-xs-12'))
			->add(Admin_Form_Entity::factory('A')
				->value(Core::_("Event.show_settings"))
				->class('representative-show-link darkgray')
				->onclick("$.toggleEventFields($(this), '#{$windowId} .settings-row')")
			)
		);

		if (!$this->_object->id)
		{
			$date = Core_Array::getGet('date');

			!$date
				&& $date = time();

			$this->getField('start')->value(Core_Date::timestamp2datetime($date));
			$this->getField('deadline')->value(Core_Date::timestamp2datetime($date + 60 * 60));
		}

		$oMainTab
			->move($this->getField('start')->divAttr(array('class' => 'form-group col-xs-6')), $oMainRow3_2)
			->move($this->getField('deadline')->divAttr(array('class' => 'form-group col-xs-6')), $oMainRow3_2)
			->move($this->getField('finish')->divAttr(array('class' => 'form-group col-xs-6'))->disabled('disabled'), $oMainRow3_2);

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
		$oAdmin_Form_Entity_Code->html(
			'<script>
				var formatDateTimePicker = $("#' . $windowId . ' input[name=\'all_day\']").attr("checked") ? "' . Core::$mainConfig['datePickerFormat'] . '" : "' . Core::$mainConfig['dateTimePickerFormat'] . '";

				$(\'#' . $windowId . ' input[name="start"]\').parent().data("DateTimePicker").format(formatDateTimePicker);
				$(\'#' . $windowId . ' input[name="deadline"]\').parent().data("DateTimePicker").format(formatDateTimePicker);
			</script>'
		);

		$oMainRow3_2->add($oAdmin_Form_Entity_Code);

		$aMasEventTypes = array();

		$aEventTypes = Core_Entity::factory('Event_Type', 0)->findAll();

		foreach ($aEventTypes as $oEventType)
		{
			$aMasEventTypes[$oEventType->id] = array(
				'value' => $oEventType->name,
				'color' => $oEventType->color,
				'icon' => $oEventType->icon
			);
		}

		$oDropdownlistEventTypes = Admin_Form_Entity::factory('Dropdownlist')
			->options($aMasEventTypes)
			->name('event_type_id')
			->value($this->_object->event_type_id)
			->caption(Core::_('Event.event_type_id'))
			->divAttr(array('class' => 'form-group col-md-4 col-sm-4 col-xs-6'));

		$oMainRow4->add($oDropdownlistEventTypes);

		$aMasEventGroups = array(array('value' => Core::_('Event.notGroup'), 'color' => '#aebec4'));

		$aEventGroups = Core_Entity::factory('Event_Group', 0)->findAll();

		foreach ($aEventGroups as $oEventGroup)
		{
			$aMasEventGroups[$oEventGroup->id] = array('value' => $oEventGroup->name, 'color' => $oEventGroup->color);
		}

		$oDropdownlistEventGroups = Admin_Form_Entity::factory('Dropdownlist')
			->options($aMasEventGroups)
			->name('event_group_id')
			->value($this->_object->event_group_id)
			->caption(Core::_('Event.event_group_id'))
			->divAttr(array('class' => 'form-group col-md-4 col-sm-5 col-xs-6'));

		$oMainRow4->add($oDropdownlistEventGroups);

		$aMasEventStatuses = array(array('value' => Core::_('Event.notStatus'), 'color' => '#aebec4'));

		// При добавлении дела отображаем статусы, которые не являются завершающими
		$aEventStatuses = is_null($this->_object->id)
			? Core_Entity::factory('Event_Status')->getAllByFinal(0)
			: Core_Entity::factory('Event_Status')->findAll();

		foreach ($aEventStatuses as $oEventStatus)
		{
			$aMasEventStatuses[$oEventStatus->id] = array(
				'value' => $oEventStatus->name,
				'color' => $oEventStatus->color
			);
		}

		$oDropdownlistEventStatuses = Admin_Form_Entity::factory('Dropdownlist')
			->options($aMasEventStatuses)
			->name('event_status_id')
			->value($this->_object->event_status_id)
			->caption(Core::_('Event.event_status_id'))
			->divAttr(array('class' => 'form-group col-md-4 col-sm-4 col-xs-6'));

		$oMainRow4->add($oDropdownlistEventStatuses);

		$aSelectResponsibleEmployees = $oSite->Companies->getUsersOptions();

		$oSelectResponsibleEmployees = Admin_Form_Entity::factory('Select')
			->id($windowId . '-event_user_id')
			->multiple('multiple')
			->options($aSelectResponsibleEmployees)
			->name('event_user_id[]')
			->value($aResponsibleEmployees)
			->caption(Core::_('Event.event_user_id'))
			->style("width: 100%");

		$oScriptResponsibleEmployees = Admin_Form_Entity::factory('Script')
			->value('
				var eventUsersControlElememt = $("#' . $windowId . '-event_user_id")
					.data({
						// templateResultOptions - свойство-объект настроек выпадающего списка
						// templateResultOptions.excludedItems - массив идентификаторов элеметов, исключаемых из списка
						templateResultOptions: {
							excludedItems: [' . $iCreatorUserId . ']
						},
						// templateSelectionOptions - свойство-объект настроек отображаемых (выбранных) элементов
						// templateSelectionOptions.unavailableItems - массив идентификаторов выбранных элеметов, которые нельзя удалить
						templateSelectionOptions: {
							unavailableItems: [' . $iCreatorUserId . ']
						}
					})
					.select2({
						dropdownParent: $("#' . $windowId . '"),
						placeholder: "",
						//allowClear: true,
						//multiple: true,
						templateResult: $.templateResultItemResponsibleEmployees,
						escapeMarkup: function(m) { return m; },
						templateSelection: $.templateSelectionItemResponsibleEmployees,
						language: "' . Core_I18n::instance()->getLng() . '",
						width: "100%"
					});
					'
			);

		$oMainRow8
			->add($oSelectResponsibleEmployees)
			->add($oScriptResponsibleEmployees);

		// Массив установленных значений
		$aEventCompaniesPeople = array();

		$aExistSiteusers = array();

		if ($this->_object->id)
		{
			$aEventSiteusers = $this->_object->Event_Siteusers->findAll();

			foreach ($aEventSiteusers as $oEventSiteuser)
			{
				if ($oEventSiteuser->siteuser_company_id)
				{
					$aEventCompaniesPeople[] = 'company_' . $oEventSiteuser->siteuser_company_id;
					$aExistSiteusers[] = $oEventSiteuser->Siteuser_Company->siteuser_id;
				}
				else
				{
					$aEventCompaniesPeople[] = 'person_' . $oEventSiteuser->siteuser_person_id;
					$aExistSiteusers[] = $oEventSiteuser->Siteuser_Person->siteuser_id;
				}
			}
		}

		$siteuser_id = intval(Core_Array::getGet('siteuser_id'));

		if (Core::moduleIsActive('siteuser') && $siteuser_id && !$this->_object->id)
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getById($siteuser_id);

			if (!is_null($oSiteuser))
			{
				$aSiteuser_Companies = $oSiteuser->Siteuser_Companies->findAll(FALSE);

				foreach ($aSiteuser_Companies as $oSiteuser_Company)
				{
					$aEventCompaniesPeople[] = 'company_' . $oSiteuser_Company->id;
					$aExistSiteusers[] = $oSiteuser_Company->siteuser_id;
				}

				$aSiteuser_People = $oSiteuser->Siteuser_People->findAll(FALSE);

				foreach ($aSiteuser_People as $oSiteuser_Person)
				{
					$aEventCompaniesPeople[] = 'person_' . $oSiteuser_Person->id;
					$aExistSiteusers[] = $oSiteuser_Person->siteuser_id;
				}
			}
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$aMasSiteusers = array();

			if (count($aExistSiteusers))
			{
				$aSiteusers = $oSite->Siteusers->getAllById($aExistSiteusers, FALSE, 'IN');
				foreach ($aSiteusers as $oSiteuser)
				{
					$oOptgroupSiteuser = new stdClass();
					$oOptgroupSiteuser->attributes = array('label' => $oSiteuser->login, 'class' => 'siteuser');

					$aSiteuserCompanies = $oSiteuser->Siteuser_Companies->findAll();
					foreach ($aSiteuserCompanies as $oSiteuserCompany)
					{
						$oOptgroupSiteuser->children['company_' . $oSiteuserCompany->id] = array(
							'value' => $oSiteuserCompany->name . '%%%' . $oSiteuserCompany->getAvatar(),
							'attr' => array('class' => 'siteuser-company')
						);
					}

					$aSiteuserPeople = $oSiteuser->Siteuser_People->findAll();
					foreach ($aSiteuserPeople as $oSiteuserPerson)
					{
						$oOptgroupSiteuser->children['person_' . $oSiteuserPerson->id] = array(
							'value' => $oSiteuserPerson->getFullName() . '%%%' . $oSiteuserPerson->getAvatar(),
							'attr' => array('class' => 'siteuser-person')
						);
					}

					$aMasSiteusers[$oSiteuser->id] = $oOptgroupSiteuser;
				}
			}

			$oSelectSiteusers = Admin_Form_Entity::factory('Select')
				->id($windowId . '-event_siteuser_id')
				->multiple('multiple')
				->options($aMasSiteusers)
				->name('event_siteuser_id[]')
				->value($aEventCompaniesPeople)
				->caption(Core::_('Event.event_siteuser_id'))
				->style("width: 100%");

			$oScriptSiteusers = Admin_Form_Entity::factory('Script')
				->value('
					$("#' . $windowId . '-event_siteuser_id").select2({
						dropdownParent: $("#' . $windowId . '"),
						minimumInputLength: 1,
						placeholder: "",
						allowClear: true,
						multiple: true,
						ajax: {
							url: "/admin/siteuser/index.php?loadSiteusers&types[]=person&types[]=company",
							dataType: "json",
							type: "GET",
							processResults: function (data) {
								var aResults = [];
								$.each(data, function (index, item) {
									aResults.push({
										"id": item.id,
										"text": item.text
									});
								});
								return {
									results: aResults
								};
							}
						},
						templateResult: $.templateResultItemSiteusers,
						escapeMarkup: function(m) { return m; },
						templateSelection: $.templateSelectionItemSiteusers,
						language: "' . Core_I18n::instance()->getLng() . '",
						width: "100%"
					});'
				);

			$oMainRow5
				->add($oSelectSiteusers)
				->add($oScriptSiteusers);
		}

		$aLead_Events = Core::moduleIsActive('Lead')
			? $this->_object->Lead_Events->findAll(FALSE)
			:array();

		$aDeal_Events = Core::moduleIsActive('Deal')
			? $this->_object->Deal_Events->findAll(FALSE)
			: array();

		$fieldClass = 'form-group col-xs-12';

		$oEntityDiv = NULL;

		if (count($aLead_Events) || count($aDeal_Events))
		{
			$fieldClass = 'form-group col-xs-12 col-sm-9';

			$oEntityDiv = Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12 col-sm-3')
				->add(
					Admin_Form_Entity::factory('Span')
						->class('caption')
						->value(Core::_('Event.related_elements'))
				);
		}

		$oMainTab
			->move($this->getField('name')->rows(1)->divAttr(array('class' => $fieldClass)), $oMainRow1);

		if (!is_null($oEntityDiv))
		{
			$oMainRow1->add($oEntityDiv);

			if (count($aLead_Events))
			{
				foreach ($aLead_Events as $oLead_Event)
				{
					$oLead = $oLead_Event->Lead;

					$oEntityDiv->add(
						Admin_Form_Entity::factory('Code')
							->html('<span class="badge badge-square margin-right-5" style="color: ' . $oLead->Lead_Status->color . '; background-color: ' . Core_Str::hex2lighter($oLead->Lead_Status->color, 0.88) . '"><i class="fa fa-user-circle-o margin-right-5"></i><a style="color: inherit;" href="/admin/lead/index.php?hostcms[action]=edit&hostcms[checked][0][' . $oLead->id . ']=1" onclick="$.modalLoad({path: \'/admin/lead/index.php\', action: \'edit\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $oLead->id . ']=1\', windowId: \'' . $this->_Admin_Form_Controller->getWindowId() . '\'}); return false">' . htmlspecialchars($oLead->getFullName()) . '</a></span>')
					);
				}
			}

			if (count($aDeal_Events))
			{
				foreach ($aDeal_Events as $oDeal_Event)
				{
					$oDeal = $oDeal_Event->Deal;

					$oEntityDiv->add(
						Admin_Form_Entity::factory('Code')
							->html('<span class="badge badge-square margin-right-5" style="color: ' . $oDeal->Deal_Template->color . '; background-color: ' . Core_Str::hex2lighter($oDeal->Deal_Template->color, 0.88) . '"><i class="fa fa-user-circle-o margin-right-5"></i><a style="color: inherit;" href="/admin/deal/index.php?hostcms[action]=edit&hostcms[checked][0][' . $oDeal->id . ']=1" onclick="$.modalLoad({path: \'/admin/deal/index.php\', action: \'edit\', operation: \'modal\', additionalParams: \'hostcms[checked][0][' . $oDeal->id . ']=1\', windowId: \'' . $this->_Admin_Form_Controller->getWindowId() . '\'}); return false">' . htmlspecialchars($oDeal->name) . '</a></span>')
					);
				}
			}
		}

		$bHideAdditionalRow && $oMainRowAdditionalShow->add(Admin_Form_Entity::factory('Span')
			->divAttr(array('class' => 'form-group col-xs-12'))
			->add(Admin_Form_Entity::factory('A')
				->value(Core::_("Event.show_additional"))
				->class('representative-show-link darkgray')
				->onclick("$.toggleEventFields($(this), '#{$windowId} .additional-row')")
			)
		);

		$oMainTab
			->move($this->getField('description'), $oMainRow2)
			->move($this->getField('place')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow6)
			;

		if (Core::moduleIsActive('dms'))
		{
			$oMainRow2->add(
				Admin_Form_Entity::factory('Code')
					->html('<div class="form-group col-xs-12">' . $this->_object->showDocuments($this->_Admin_Form_Controller) . '</div>')
			);
		}

		// $oMainRowResultShow->add(Admin_Form_Entity::factory('Span')
		// 	->divAttr(array('class' => 'form-group col-xs-12'))
		// 	->add(Admin_Form_Entity::factory('A')
		// 		->value(Core::_("Event.show_results"))
		// 		->class('representative-show-link darkgray')
		// 		->onclick("$.toggleEventFields($(this), '#{$windowId} .result-row')")
		// 	)
		// );

		// Если сотрудник является участником дела, но не его создателем, то возможен только просмотр информации о деле.
		if ($this->_object->id && $iCreatorUserId != $oUser->id)
		{
			$oDivTimeSlider
				->add(
					Admin_Form_Entity::factory('Div')
						->class("disabled")
				);

			$this->getField('name')->disabled('disabled');
			$this->getField('description')->disabled('disabled');
			$this->getField('important')->disabled('disabled');
			$this->getField('start')->disabled('disabled');
			$this->getField('deadline')->disabled('disabled');
			$this->getField('duration')->disabled('disabled');
			$this->getField('all_day')->disabled('disabled');
			$this->getField('place')->disabled('disabled');
			// $this->getField('reminder_value')->disabled('disabled');
			$this->getField('completed')->disabled('disabled');
			$this->getField('busy')->disabled('disabled');

			$this->getField('result')->disabled('disabled');

			$oDropdownlistEventTypes->disabled(TRUE);
			$oDropdownlistEventGroups->disabled(TRUE);
			$oDropdownlistEventStatuses->disabled(TRUE);

			$oSelectSiteusers->disabled('disabled');
			$oSelectResponsibleEmployees->disabled('disabled');
		}

		if ($this->_object->event_type_id)
		{
			$oMainTab->delete($this->getField('completed'));

			/*$oDivLeft->add($oMainRow10 = Admin_Form_Entity::factory('Div')->class('row result-row' . (!$this->_object->completed ? ' hidden' : '')));

			$oEvent_Type = $this->_object->Event_Type;

			$successfully = strlen(trim($oEvent_Type->successfully))
				? htmlspecialchars($oEvent_Type->successfully)
				: Core::_('Event_Type.successfully');

			$failed = strlen(trim($oEvent_Type->failed))
				? htmlspecialchars($oEvent_Type->failed)
				: Core::_('Event_Type.failed');

			$oMainRow10->add(
				Admin_Form_Entity::factory('Radiogroup')
					->radio(array(
						1 => $successfully,
						-1 => $failed,
					))
					->ico(array(
						1 => 'fa-check',
						-1 => 'fa-ban',
					))
					->colors(array('btn-palegreen', 'btn-darkorange'))
					->name('completed')
					->divAttr(array('class' => 'form-group col-xs-12 type-states'))
					->value($this->_object->completed)
			)->add(Admin_Form_Entity::factory('Script')
				->value("
					$(function(){
						$('#{$windowId} .type-states').on('click', 'label.checkbox-inline', function(e) {
							e.preventDefault();

							var radio = $(this).find('input[type=radio]');
							radio.prop('checked', !radio.is(':checked'));
						});
					});")
			);*/
		}

		$aEvent_Attachments = $this->_object->Event_Attachments->findAll();

		// $oMainRowAttachments->add($oFileField);

		$countFiles = count($aEvent_Attachments)
			? '<span class="badge badge-azure">' . count($aEvent_Attachments) . '</span>'
			: '';

		$countNotes = ($count = $this->_object->Crm_Notes->getCount())
			? '<span class="badge badge-palegreen">' . $count . '</span>'
			: '';

		$countEvents = ($count = $this->_object->Events->getCount())
			? '<span class="badge badge-yellow">' . $count . '</span>'
			: '';

		ob_start();
		?>
		<div class="tabbable">
			<ul class="nav nav-tabs tabs-flat" id="eventTabs">
				<li class="active" data-type="timeline">
					<a data-toggle="tab" href="#<?php echo $windowId?>_timeline" data-path="/admin/event/timeline/index.php" data-window-id="<?php echo $windowId?>-event-timeline" data-additional="event_id=<?php echo $this->_object->id?>">
						<i class="fa fa-bars"></i>
					</a>
				</li>
				<li data-type="note">
					<a data-toggle="tab" href="#<?php echo $windowId?>_notes" data-path="/admin/event/note/index.php" data-window-id="<?php echo $windowId?>-event-notes" data-additional="event_id=<?php echo $this->_object->id?>">
						<?php echo Core::_("Event.tabNotes")?> <?php echo $countNotes?>
					</a>
				</li>
				<li>
					<a data-toggle="tab" href="#<?php echo $windowId?>_files">
						<?php echo Core::_("Event.attachment_header")?> <?php echo $countFiles?>
					</a>
				</li>
				<?php
				if (Core::moduleIsActive('event'))
				{
					?>
					<li data-type="event">
						<a data-toggle="tab" href="#<?php echo $windowId?>_events" data-path="/admin/event/index.php" data-window-id="<?php echo $windowId?>-related-events" data-additional="show_subs=1&hideMenu=1&parent_id=<?php echo $this->_object->id?>">
							<?php echo Core::_("Event.tabEvents")?> <?php echo $countEvents?>
						</a>
					</li>
					<?php
				}

				if (Core::moduleIsActive('dms'))
				{
				?>
					<li data-type="dms_document">
						<a data-toggle="tab" href="#<?php echo $windowId?>_documents" data-path="/admin/event/dms/document/index.php" data-window-id="<?php echo $windowId?>-event-dms-documents" data-additional="event_id=<?php echo $this->_object->id?>">
							<?php echo Core::_("Event.tabDmsDocuments")?> <?php echo ($count = $this->_object->Dms_Documents->getCount())
								? '<span class="badge badge-purple">' . $count . '</span>'
								: ''?>
						</a>
					</li>
				<?php
				}
				?>
			</ul>
			<div class="tab-content tabs-flat">
				<div id="<?php echo $windowId?>_timeline" class="tab-pane in active">
					<?php
						Admin_Form_Entity::factory('Div')
							->controller($this->_Admin_Form_Controller)
							->id("{$windowId}-event-timeline")
							->add(
								$this->_object->id
									? $this->_addEventTimeline()
									: Admin_Form_Entity::factory('Code')->html(
										Core_Message::get(Core::_('Event.enable_after_save'), 'warning')
									)
							)
							->execute();
					?>
				</div>
				<div id="<?php echo $windowId?>_notes" class="tab-pane">
					<?php
					Admin_Form_Entity::factory('Div')
						->controller($this->_Admin_Form_Controller)
						->id("{$windowId}-event-notes")
						->add(
							$this->_object->id
								? $this->_addEventNotes()
								: Admin_Form_Entity::factory('Code')->html(
									Core_Message::get(Core::_('Event.enable_after_save'), 'warning')
								)
						)
						->execute();
					?>
				</div>
				<div id="<?php echo $windowId?>_files" class="row tab-pane">
					<?php
					foreach ($aEvent_Attachments as $oEvent_Attachment)
					{
						$textSize = $oEvent_Attachment->getTextSize();

						ob_start();
						Core_Html_Entity::factory('Strong')
							->value(" ({$textSize})")
							->execute();

						Admin_Form_Entity::factory('File')
							->controller($this->_Admin_Form_Controller)
							->type('file')
							->caption(/*$icon_file_img . ' ' . */$oEvent_Attachment->file_name . ob_get_clean())
							->name("file_{$oEvent_Attachment->id}")
							->largeImage(
								array(
									'path' => '/admin/event/index.php?downloadFile=' . $oEvent_Attachment->id . '&filename=' . $oEvent_Attachment->file_name,
									'show_params' => FALSE,
									'originalName' => $oEvent_Attachment->file_name,
									'delete_onclick' => "$.adminLoad({path: '/admin/event/index.php', additionalParams: 'hostcms[checked][0][{$this->_object->id}]=1', operation: '{$oEvent_Attachment->id}', action: 'deleteFile', windowId: '{$windowId}'}); return false",
									'delete_href' => ''
								)
							)
							->smallImage(
								array('show' => FALSE)
							)
							->divAttr(array('id' => "file_{$oEvent_Attachment->id}", 'class' => 'input-group col-xs-12'))
							->execute();
					}

					$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
					$oAdmin_Form_Entity_Code->html('<div class="input-group-addon no-padding add-remove-property"><div class="no-padding-left col-lg-12"><div class="btn btn-palegreen inverted" onclick="$.cloneFile(\'' . $windowId . '\'); event.stopPropagation();"><i class="fa fa-plus-circle close"></i></div>
						<div class="btn btn-darkorange inverted" onclick="$(this).parents(\'#file\').remove(); event.stopPropagation();"><i class="fa fa-minus-circle close"></i></div>
						</div>
						</div>');

					Admin_Form_Entity::factory('File')
						->controller($this->_Admin_Form_Controller)
						->type('file')
						->name("file[]")
						->caption(Core::_('Event.attachment'))
						->largeImage(array('show_params' => FALSE))
						->smallImage(array('show' => FALSE))
						->divAttr(array('id' => 'file', 'class' => 'col-xs-12 col-sm-6'))
						->add($oAdmin_Form_Entity_Code)
						->execute();
					?>
				</div>
				<?php
				if (Core::moduleIsActive('event'))
				{
				?>
					<div id="<?php echo $windowId?>_events" class="tab-pane">
					<?php
						Admin_Form_Entity::factory('Div')
							->id("{$windowId}-related-events")
							->add(
								$this->_object->id
									? $this->_addEvents()
									: Admin_Form_Entity::factory('Code')->html(
										Core_Message::get(Core::_('Event.enable_after_save'), 'warning')
									)
							)
							->execute();
					?>
					</div>
				<?php
				}

				if (Core::moduleIsActive('dms'))
				{
				?>
					<div id="<?php echo $windowId?>_documents" class="tab-pane">
					<?php
						Admin_Form_Entity::factory('Div')
							->id("{$windowId}-event-dms-documents")
							->add(
								$this->_object->id
									? $this->_addEventDmsDocuments()
									: Admin_Form_Entity::factory('Code')->html(
										Core_Message::get(Core::_('Event.enable_after_save'), 'warning')
									)
							)
							->execute();
					?>
					</div>
				<?php
				}
				?>
			</div>
		</div>
		<?php
		$oMainRowAttachments->add(Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 margin-top-20')
			->add(
				Admin_Form_Entity::factory('Code')
					->html(ob_get_clean())
			)
		);

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
		$oAdmin_Form_Entity_Code->html(
			'<script>
				var aScripts = [
						\'css/timeslider.css\',
						\'js/timeslider/timeslider.js\'
					];

				$.getMultiContent(aScripts, \'/modules/skin/bootstrap/\').done(function() {
					// all scripts loaded
					if (window.currentDialog)
					{
						window.currentDialog.on("shown.bs.modal", function() {
							showTimeslider();
						});
					}
					else
					{
						$(function(){
							showTimeslider();
						});
					}

					function showTimeslider() {

						var oEventStartDate = new Date(+$(\'#' . $windowId . ' input[name="start"]\').parent().data("DateTimePicker").date()),
							timeZoneOffset = (oEventStartDate.getTimezoneOffset() * 60 * 1000 * -1),
							eventStartTime = oEventStartDate.getTime() + timeZoneOffset,

							eventStopTime = eventStartTime + getDurationMilliseconds(\'' . $windowId . '\'),
							oCurrentTime = Date.now() + timeZoneOffset,
							allDay = ' . ($this->_object->all_day ? 'true' : 'false') . ',
							startTimestampRuler = eventStartTime - 3600 * (allDay ? 1 : 12) * 1000,
							cellId = "cellId",
							dateTimeFormatString = "' . Core::$mainConfig['dateTimePickerFormat'] . '",
							jTimeSlider = $("#' . $windowId . ' #ts");

						jTimeSlider.TimeSlider({
							current_timestamp: oCurrentTime,
							start_timestamp: startTimestampRuler,
							hours_per_ruler: 24,
							update_timestamp_interval: 5000,
							init_cells: [
								{
									"id": cellId,
									"_id": cellId,
									"start": eventStartTime, //(current_time - (3600 * 6 * 1000) ),
									"stop": eventStopTime, //eventStartTime + 3600 * 1 * 1000,
									"style": {
										"background-color": "#CAEF4A"
									}
								},
							],
							on_resize_timecell_callback: function (id, start, end) {

								if (start > end)
								{
									start = end;
								}

								jTimeSlider.data("resizeTimeCell", true);

								setDuration(start, end, \'' . $windowId . '\');
								setStartAndDeadline(start - timeZoneOffset, end - timeZoneOffset, \'' . $windowId . '\');

								// Снимаем флажок "Весь день", если это необходимо
								cancelAllDay(\'' . $windowId . '\');

								jTimeSlider.removeData("resizeTimeCell");
							},
							// Обработчик сдвига ползунка
							on_move_timecell_callback: function (id, start, end) {

								var timeCellStartTimestamp = jTimeSlider.data("timeCellStartTimestamp"),
									limit = 1000 * 60 * 60,	// 1 час
									startRuler = +jTimeSlider.data("timeslider")["options"]["start_timestamp"],

									// Длина линейки в миллисекундах
									ruler_duration = +jTimeSlider.data("timeslider")["options"]["hours_per_ruler"] * 60 * 60 * 1000,

									// Значение правой границы диапозона
									stopRuler = startRuler + ruler_duration,
									changeDelta = -1,
									leftShift, timeCellDuration;

								if (limit >= Math.abs(start - startRuler) || start < startRuler )
								{
									if (start < startRuler)
									{
										end += (startRuler - start);
										start = startRuler
									}

									changeDelta = limit - (start - startRuler);

									// Сдвиг ползунка влево
									leftShift = true;
								}
								else if (limit >= Math.abs(stopRuler - end) || stopRuler < end)
								{
									if (end > stopRuler)
									{
										start -= end - stopRuler;
										end = stopRuler;
									}

									changeDelta = limit - (stopRuler - end);

									// Сдвиг ползунка вправо
									leftShift = false;
								}

								jTimeSlider.data({
									"timeCellStartTimestamp": start,
									"moveTimeCell": true
								});

								if (changeDelta >= 0)
								{
									if (jTimeSlider.data("repeatingIntervalId"))
									{
										clearInterval(jTimeSlider.data("repeatingIntervalId"));
									}

									timeCellDuration = end - start;

									jTimeSlider.data("rulerRepeating", true);

									var repeatingIntervalId = setInterval(function(){
										var timeCell = $("#' . $windowId . ' #" + cellId),
											startRuler = +jTimeSlider.data("timeslider")["options"]["start_timestamp"],
											stopRuler = startRuler + ruler_duration,
											start, end, newStart, newStop, newStartTimestamp;

										// Передвигаем ползунок слева направо
										if (leftShift)
										{
											start = +timeCell.attr("start_timestamp");
											newStart = start - changeDelta;
											newStop = newStart + timeCellDuration;
											newStartTimestamp = startRuler - changeDelta;

											if (start <= startRuler)
											{
												start = startRuler;
											}
										}
										else
										{
											end = +timeCell.attr("stop_timestamp");
											newStop = end + changeDelta,
											newStart = newStop - timeCellDuration,
											newStartTimestamp = startRuler + changeDelta;

											if (end >= stopRuler)
											{
												end = stopRuler;
											}
										}

										var timeCellOptions = {
											"_id": cellId,
											"start": newStart,
											"stop": newStop
										};

										// Изменяем границу полосы прокрутки
										jTimeSlider.TimeSlider("new_start_timestamp", newStartTimestamp);

										// Изменяем положение ползунка
										jTimeSlider.TimeSlider("edit", timeCellOptions);

										setEventStartButtons(newStart - timeZoneOffset, \'' . $windowId . '\');

										setStartAndDeadline(newStart - timeZoneOffset, newStop - timeZoneOffset, \'' . $windowId . '\');
									}, 25);

									jTimeSlider.data("repeatingIntervalId", repeatingIntervalId);
								}
								else if (jTimeSlider.data("repeatingIntervalId"))
								{
									clearInterval(jTimeSlider.data("repeatingIntervalId"));
									jTimeSlider.removeData("rulerRepeating");
								}

								// Устанавливаем вид кнопок быстрой установки даты
								setEventStartButtons(start - timeZoneOffset, \'' . $windowId . '\');

								// Устанавливаем значения полей начала/завершения события
								setStartAndDeadline(start - timeZoneOffset, end - timeZoneOffset, \'' . $windowId . '\');

								// Снимаем флажок "Весь день", если это необходимо
								cancelAllDay(\'' . $windowId . '\');

								jTimeSlider.removeData("moveTimeCell");
							}
						});

						$("#' . $windowId . ' #ts .bg-event").on("dblclick", function (event){

							var deltaMilliseconds = jTimeSlider.data("timeslider")["options"]["hours_per_ruler"] * 3600 * 1000 / $(this).width() * (event.pageX - $(this).offset().left),
								newStartCell = +jTimeSlider.data("timeslider")["options"]["start_timestamp"] + deltaMilliseconds - timeZoneOffset,
								newStopCell = newStartCell + getDurationMilliseconds(\'' . $windowId . '\');

							setStartAndDeadline(newStartCell, newStopCell, \'' . $windowId . '\');
							setEventStartButtons(newStartCell, \'' . $windowId . '\');
							// Снимаем флажок "Весь день", если это необходимо
							cancelAllDay(\'' . $windowId . '\');
						});

						function changeDurationThroughFields(event)
						{
							if (!$("#' . $windowId . ' input[name=\'all_day\']").data("clickAllDay"))
							{
								var $this = $(this);

								if ($.trim($this.val()))
								{
									$this.data("durationFieldChanged", true);

									changeDuration(event);

									$this.removeData("durationFieldChanged");
								}
							}
						}

						$(\'#' . $windowId . ' input[name="duration"]\').on("keyup", {cellId: cellId, timeZoneOffset: timeZoneOffset, windowId: \'' . $windowId . '\'}, changeDurationThroughFields);

						$(\'#' . $windowId . ' select[name="duration_type"]\').on("change", {cellId: cellId, timeZoneOffset: timeZoneOffset, windowId: \'' . $windowId . '\'}, changeDurationThroughFields);

						// Обработчик изменения даты-времени начала/завершения события
						$(\'#' . $windowId . ' input[class*="hasDatetimepicker"]\').parent().on("dp.change", function (event)
						{
							var jTimeSlider = $("#' . $windowId . ' #ts");

							// Не нажата кнопка быстрой установки начала события, не перемещается ползунок,
							// не прокручивается линейка при смещении ползунка к одному из ее концов, не изменяется ширина ползунка
							if (!($("#' . $windowId . ' #eventStartButtonsGroup").data("clickStartButton") || jTimeSlider.data("moveTimeCell")
								|| jTimeSlider.data("rulerRepeating") || jTimeSlider.data("resizeTimeCell")
								|| $("#' . $windowId . ' input[name=\'all_day\']").data("clickAllDay")))
							{
								var inputField = $("#' . $windowId . ' input.hasDatetimepicker", this),
									inputFieldName = inputField.attr("name"),
									startTimeCell = +$(\'#' . $windowId . ' input[name="start"]\').parent().data("DateTimePicker").date()
									stopTimeCell = +$(\'#' . $windowId . ' input[name="deadline"]\').parent().data("DateTimePicker").date(),
									startTimestampRuler;

								if (!startTimeCell || !stopTimeCell)
								{
									startTimeCell = 0;
									stopTimeCell = 0;
								}

								if (startTimeCell > stopTimeCell)
								{
									stopTimeCell = startTimeCell + getDurationMilliseconds(\'' . $windowId . '\');
									setStartAndDeadline(startTimeCell, stopTimeCell, \'' . $windowId . '\');
								}

								var	timeCellOptions = {
										"_id": cellId,
										"start": startTimeCell ? startTimeCell + timeZoneOffset : 1,
										"stop": stopTimeCell ? stopTimeCell + timeZoneOffset : 1
									};

								if (startTimeCell && !jTimeSlider.data("timeCellMouseDown"))
								{
									// Весь день
									if ($(\'#' . $windowId . ' input[name="all_day"]\').prop("checked") && $("#' . $windowId . ' input[name=\'all_day\']").data("clickAllDay"))
									{
										// Текущая дата-время
										var oCurrentDate = new Date();

										// Текущая дата без времени
										var oCurrentDateWithoutTime = new Date(oCurrentDate.getFullYear(), oCurrentDate.getMonth(), oCurrentDate.getDate());

										startTimestampRuler = +new Date(+oCurrentDateWithoutTime - 3600 * 1000);
									}
									else
									{
										startTimestampRuler = startTimeCell - 3600 * 24 * 1000 / 2
									}

									// Изменяем границу полосы прокрутки
									//jTimeSlider.TimeSlider("new_start_timestamp", startTimeCell + timeZoneOffset - 3600 * 24 * 1000 / 2);
									jTimeSlider.TimeSlider("new_start_timestamp", startTimestampRuler + timeZoneOffset);
								}

								// Изменяем положение ползунка
								jTimeSlider.TimeSlider("edit", timeCellOptions);

								// Продолжительность изменена не через поле "Продолжительность" или был нажат "Весь день"
								if(!($("#' . $windowId . ' input[name=\'all_day\']").data("clickAllDay") || $(\'#' . $windowId . ' input[name="duration"]\').data("durationFieldChanged") || $(\'#' . $windowId . ' select[name="duration_type"]\').data("durationFieldChanged")))
								{
									if (startTimeCell < stopTimeCell)
									{
										// Изменяем значение продолжительности
										setDuration(startTimeCell, stopTimeCell, \'' . $windowId . '\');
									}

									setEventStartButtons(startTimeCell, \'' . $windowId . '\');
								}
							}
						});

						// Обработчик нажатия кнопки быстрого перехода по дням
						$("#' . $windowId . ' #eventStartButtonsGroup").on("click touchstart", "[data-start-day]", function (event){

							event.preventDefault();

							$(this)
								.addClass("active")
								.removeClass("btn-default")
								.siblings(".active")
								.removeClass("active");

							var koef = +$(this).data("startDay"),
								millisecondsDay = 3600 * 24 * 1000,

								// Текущая дата-время
								oCurrentDate = new Date(),

								// Текущая дата без времени
								oCurrentDateWithoutTime = new Date(oCurrentDate.getFullYear(), oCurrentDate.getMonth(), oCurrentDate.getDate()),

								// Текущая дата-время начала события
								//oCurrentStartDate = new Date(+$("#' . $windowId . ' #" + cellId).attr("start_timestamp")),
								oCurrentStartDate = new Date(+$("#' . $windowId . ' #" + cellId).attr("start_timestamp") - timeZoneOffset),

								// Текущая дата начала события без времени
								oCurrentStartDateWithoutTime = new Date(oCurrentStartDate.getFullYear(), oCurrentStartDate.getMonth(), oCurrentStartDate.getDate()),

								// Новая дата начала события без времени
								// oNewStartDateWithoutTime = new Date(+oCurrentDate + millisecondsDay * koef);
								oNewStartDateWithoutTime = new Date(+oCurrentDateWithoutTime + millisecondsDay * koef);

							// Текущая и новая даты начала действия совпадают
							if (+oCurrentStartDateWithoutTime == +oNewStartDateWithoutTime)
							{
								return false;
							}

							var	newStartCell = +oCurrentDateWithoutTime + (oCurrentStartDate - oCurrentStartDateWithoutTime) + millisecondsDay * koef, // левая граница ползунка
								newStartRuler = ($(\'#' . $windowId . ' input[name="all_day"]\').prop("checked")
									? (+oNewStartDateWithoutTime - 3600 * 1000)
									: (newStartCell - millisecondsDay / 2 )) + timeZoneOffset, //левая граница полосы прокрутки
								newStopCell = newStartCell + getDurationMilliseconds(\'' . $windowId . '\'); //duration * durationMillisecondsKoef,

								timeCellOptions = {
									"_id": cellId,
									"start": newStartCell + timeZoneOffset,
									"stop": newStopCell + timeZoneOffset
								};

							// Изменяем границу полосы прокрутки
							jTimeSlider.TimeSlider("new_start_timestamp", newStartRuler);

							// Изменяем положение ползунка
							jTimeSlider.TimeSlider("edit", timeCellOptions);

							var eventStartButtonsGroup = $("#' . $windowId . ' #eventStartButtonsGroup");

							eventStartButtonsGroup.data("clickStartButton", true);

							//setStartAndDeadline(newStartCell - timeZoneOffset, newStopCell - timeZoneOffset, \'' . $windowId . '\');
							setStartAndDeadline(newStartCell, newStopCell, \'' . $windowId . '\');

							eventStartButtonsGroup.removeData("clickStartButton");
						});

						// $("#' . $windowId . ' .page-body").on("mouseup touchend", function (){
						$("#' . $windowId . '").on("mouseup touchend", function (){

								jTimeSlider.data({"timeCellMouseDown": false});

								if (jTimeSlider.data("repeatingIntervalId"))
								{
									clearInterval(jTimeSlider.data("repeatingIntervalId"));
								}
							}
						)
						.on(
							{
								"selectstart": function (event){

									if (jTimeSlider.data("timeCellMouseDown"))
									{
										event.preventDefault();
									}
								},

								"mousewheel": function(event) {

									if (jTimeSlider.data("mouseover"))
									{
										event.preventDefault();

										var originalEvent = event.originalEvent,
											delta = originalEvent.deltaY || originalEvent.detail || originalEvent.wheelDelta,
											// Правая граница линейки
											startRuler = +jTimeSlider.data("timeslider")["options"]["start_timestamp"],
											newStartRuler = startRuler + (delta > 0 ? 1 : -1) * 1000 * 60 * 120;

											// Изменяем границу полосы прокрутки
										jTimeSlider.TimeSlider("new_start_timestamp", newStartRuler);
									}
								}
							}
						);


						jTimeSlider.on("mousedown touchstart", "#' . $windowId . ' #t" + cellId, function (event){

									jTimeSlider.data({"timeCellMouseDown": true, "timeCellStartTimestamp": $("#' . $windowId . ' #" + cellId).attr("start_timestamp")});
								}
							)
							.on({
								"mouseover": function (){

									if (!jTimeSlider.data("mouseover"))
									{
										jTimeSlider.data({"mouseover": true})
									}
								},

								"mouseout": function (){

									jTimeSlider.removeData("mouseover");
								},
							}, ".bg-event"
						);

						$("#' . $windowId . ' input[name=\'all_day\']").on("click", function (){
							$("#' . $windowId . ' input[name=\'duration\']").parents(".form-group").toggleClass("invisible");
							// $("#' . $windowId . ' select[name=\'duration_type\']").parents(".form-group").toggleClass("invisible");

							var formatDateTimePicker,
								oNewTimestampStartEvent,
								oNewTimestampEndEvent,
								startTimestampRuler = 0,
								oOriginalDateStartEvent = new Date($(\'#' . $windowId . ' input[name="start"]\').parent().data("DateTimePicker").date()),
								oOriginalDateEndEvent = new Date($(\'#' . $windowId . ' input[name="deadline"]\').parent().data("DateTimePicker").date()),
								$this = $(this);

							$this.data("clickAllDay", true);

							// Установили чекбокс
							if (this.checked)
							{
								formatDateTimePicker = "' . Core::$mainConfig['datePickerFormat'] . '";
								oNewTimestampStartEvent = new Date(oOriginalDateStartEvent.getFullYear(), oOriginalDateStartEvent.getMonth(), oOriginalDateStartEvent.getDate()).getTime();
								oNewTimestampEndEvent = oNewTimestampStartEvent + 3600 * 1000 * 24 - 1,
								originalStartTimestampRuler = +jTimeSlider.data("timeslider")["options"]["start_timestamp"];


								startTimestampRuler = oNewTimestampStartEvent - 3600 * 1000;

								jTimeSlider.data({
									"originalTimestampStartEvent": oOriginalDateStartEvent.getTime(),
									"originalTimestampEndEvent": oOriginalDateEndEvent.getTime(),
									"originalStartTimestampRuler": originalStartTimestampRuler - timeZoneOffset
								});
							}
							else
							{
								formatDateTimePicker = "' . Core::$mainConfig['dateTimePickerFormat'] . '";

								// $(\'#' . $windowId . ' input[name="start"]\').parent().data("DateTimePicker").date(new Date(oOriginalDateStartEvent.getTime() + 3600 * 1000 * 10));

								if (!jTimeSlider.data("originalTimestampStartEvent"))
								{
									$("#' . $windowId . ' input[name=\'duration\']").val(30).keyup();
									$("#' . $windowId . ' select[name=\'duration_type\']").val(0);
								}

								oNewTimestampStartEvent = jTimeSlider.data("originalTimestampStartEvent")
									? jTimeSlider.data("originalTimestampStartEvent")
									: oOriginalDateStartEvent.getTime() + 3600 * 1000 * 10;
								oNewTimestampEndEvent = jTimeSlider.data("originalTimestampEndEvent")
									? jTimeSlider.data("originalTimestampEndEvent")
									: oNewTimestampStartEvent + 3600 * 1000 * 0.5 ; // по умолчанию - полчаса
								startTimestampRuler = jTimeSlider.data("originalStartTimestampRuler")
									? jTimeSlider.data("originalStartTimestampRuler")
									: +(new Date(oOriginalDateStartEvent.getFullYear(), oOriginalDateStartEvent.getMonth(), oOriginalDateStartEvent.getDate()));
							}

							var timeCellOptions = {
								"_id": cellId,
								"start": oNewTimestampStartEvent + timeZoneOffset,
								"stop": oNewTimestampEndEvent + timeZoneOffset
							};

							// Изменяем границу полосы прокрутки
							jTimeSlider.TimeSlider("new_start_timestamp", startTimestampRuler + timeZoneOffset);

							// Изменяем положение ползунка
							jTimeSlider.TimeSlider("edit", timeCellOptions);

							setStartAndDeadline(oNewTimestampStartEvent, oNewTimestampEndEvent, \'' . $windowId . '\');

							$(\'#' . $windowId . ' input[name="start"]\').parent().data("DateTimePicker").format(formatDateTimePicker);
							$(\'#' . $windowId . ' input[name="deadline"]\').parent().data("DateTimePicker").format(formatDateTimePicker);

							$this.removeData("clickAllDay");
						});
					}
				});
			</script>'
		);

		$oMainRowScripts->add($oAdmin_Form_Entity_Code);

		$oMainTab
			->add(Admin_Form_Entity::factory('Script')
				->value('
					$(function(){
						var leftBlockHeight,
							timer = setInterval(function(){

							leftBlockHeight = $("#' . $windowId . ' .left-block").height();
							if (leftBlockHeight)
							{
								clearInterval(timer);

								$("#' . $windowId . ' .right-block").find("#' . $windowId . '-event-notes, #' . $windowId . '-event-timeline").slimscroll({
									height: leftBlockHeight - 75,
									color: "rgba(0, 0, 0, 0.3)",
									size: "5px"
								});
							}
						}, 500);
					});
				'));

		return $this;
	}

	/**
	 * Add related events
	 * @return Admin_Form_Entity
	 */
	protected function _addEvents()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));

		$targetWindowId = $modalWindowId ? $modalWindowId : $windowId;

		return Admin_Form_Entity::factory('Script')
			->value("$(function() {
				$.adminLoad({ path: '/admin/event/index.php', additionalParams: 'show_subs=1&hideMenu=1&parent_id={$this->_object->id}&parentWindowId={$targetWindowId}', windowId: '{$targetWindowId}-related-events' });
			});");
	}

	/**
	 * Add timeline
	 * @return Admin_Form_Entity
	 */
	protected function _addEventTimeline()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));

		$targetWindowId = $modalWindowId ? $modalWindowId : $windowId;

		return Admin_Form_Entity::factory('Script')
			->value("$(function() {
				$.adminLoad({ path: '/admin/event/timeline/index.php', additionalParams: 'event_id={$this->_object->id}&parentWindowId={$targetWindowId}', windowId: '{$targetWindowId}-event-timeline' });
			});");
	}

	/**
	 * Add dms documents
	 * @return Admin_Form_Entity
	 */
	protected function _addEventDmsDocuments()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				$.adminLoad({ path: '/admin/event/dms/document/index.php', additionalParams: 'event_id=" . $this->_object->id . "', windowId: '{$windowId}-event-dms-documents' });
			});");
	}

	/**
	 * Add event notes
	 * @return Admin_Form_Entity
	 */
	protected function _addEventNotes()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));

		$targetWindowId = $modalWindowId ? $modalWindowId : $windowId;

		return Admin_Form_Entity::factory('Script')
			->value("$(function() {
				$.adminLoad({ path: '/admin/event/note/index.php', additionalParams: 'event_id=" . $this->_object->id . "', windowId: '{$targetWindowId}-event-notes' });
			});");
	}

	/**
	 * Call dms execution
	 * @return self
	 */
	protected function _callDmsExecution()
	{
		if (Core::moduleIsActive('dms') && $this->_object->completed != 0)
		{
			$oDms_Workflow_Execution_User = $this->_object->Dms_Workflow_Execution_Users->getFirst(FALSE);

			if ($oDms_Workflow_Execution_User)
			{
				$oDms_Workflow_Execution_User->Dms_Workflow_Execution->execute();
			}
		}

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Event_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$oCurrentUser = Core_Auth::getCurrentUser();

		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));
		$windowId = $modalWindowId ? $modalWindowId : $this->_Admin_Form_Controller->getWindowId();

		$bAddEvent = is_null($this->_object->id);

		// Значение завершенности дела до применения изменений
		$eventCompletedBefore = $bAddEvent ? 0 : $this->_object->completed;

		$oEventCreator = $this->_object->getCreator();

		if (!$bAddEvent && is_null($oEventCreator))
		{
			throw new Core_Exception('Error, the event has no creator!', array(), 0, FALSE);
		}

		// Запрещаем редактировать дело не его создателю
		if (!$bAddEvent /*&& !is_null($oEventCreator)*/ && $oEventCreator->id != $oCurrentUser->id)
		{
			$this->_object->completed = strval(Core_Array::get($this->_formValues, 'completed'));
			// $this->_object->result = strval(Core_Array::get($this->_formValues, 'result'));
			$this->_object->save();

			// Завершенность изменена
			if ($eventCompletedBefore != $this->_object->completed)
			{
				$this->_object->setFinish();

				// Отправляем уведомления ответственным сотрудникам
				$this->_object->changeCompletedSendNotification();

				// Notify Dms
				$this->_callDmsExecution();
			}

			return TRUE;
		}

		// Устанавливаем дату создания
		$bAddEvent
			&& $this->_object->datetime = Core_Date::timestamp2sql(time());

		// Установлен флажок "Весь день"
		if (Core_Array::getPost('all_day'))
		{
			$this->_formValues['deadline'] = $this->_formValues['deadline'] . ' 23:59:59';
		}

		$this->_formValues['last_modified'] = Core_Date::timestamp2sql(time());

		if ($iEventStatusId = intval(Core_Array::getPost('event_status_id', 0)))
		{
			$oEventStatus = Core_Entity::factory('Event_Status', $iEventStatusId);
			$oEventStatus->final &&	$this->_formValues['completed']	= 1;
		}

		// В режиме просмотра поля не будет
		$startEvent = Core_Array::get($this->_formValues, 'start');

		// Задано время начала события
		if (!empty($startEvent))
		{
			// Определение даты-времени отправления напоминания
			$reminderValue = intval(Core_Array::getPost('reminder_value', 0));

			// Задан период напоминания о событии
			if ($reminderValue)
			{
				$iReminderType = Core_Array::getPost('reminder_type', 0);

				// Определяем значение периода напоминания о событии в секундах
				switch ($iReminderType)
				{
					case 1: // Часы
						$iSecondsReminderValue = 60 * 60 * $reminderValue;
						break;
					case 2: // Дни
						$iSecondsReminderValue = 60 * 60 * 24 * $reminderValue;
						break;

					default: // Минуты
						$iSecondsReminderValue = 60 * $reminderValue;
				}

				$this->_object->reminder_start = Core_Date::timestamp2sql(Core_Date::datetime2timestamp($startEvent) - $iSecondsReminderValue);
			}
		}

		$previousObject = clone $this->_object;

		parent::_applyObjectProperty();

		if ($bAddEvent)
		{
			ob_start();
			$this->_addEventTimeline()->execute();
			$this->_addEventNotes()->execute();
			$this->_addEvents()->execute();
			Core::moduleIsActive('dms') && $this->_addEventDmsDocuments()->execute();
			?>
			<script>
				$(function(){
					$("#<?php echo $windowId?> a[data-additional='event_id=']").data('additional', 'event_id=<?php echo $this->_object->id?>');
				});
			</script>
			<?php
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());

			$this->_object->pushHistory(Core::_('Event.history_add_event'));
		}

		$oEvent = $this->_object;

		$aEventUserId = Core_Array::getPost('event_user_id', array());

		// To array
		!is_array($aEventUserId) && $aEventUserId = array();

		// Add creator
		!in_array($oCurrentUser->id, $aEventUserId) && $aEventUserId[] = $oCurrentUser->id;

		$aEventUserId = array_unique($aEventUserId);

		$aIssetUsers = array();

		// Массив идентификаторов пользователей, которым будет отправлено уведомление о исключении их из списка исполнителями дела
		$aNotificationEventExcludedUserId = array();

		// Менять список ответственных сотрудников может создатель дела
		//if (!$bAddEvent && ($oEventCreator = $oEvent->getCreator()) && $oEventCreator->id == $oCurrentUser->id)
		//{
			// Ответственные сотрудники
			$aEventUsers = $oEvent->Event_Users->findAll(FALSE);

			foreach ($aEventUsers as $oEventUser)
			{
				$iSearchIndex = array_search($oEventUser->user_id, $aEventUserId);

				if ($iSearchIndex === FALSE && $oEventUser->user_id != $oCurrentUser->id)
				{
					$aNotificationEventExcludedUserId[] = $oEventUser->user_id;
					$oEventUser->delete();
				}
				else
				{
					$aIssetUsers[] = $oEventUser->user_id;
				}
			}
		//}

		// Массив идентификаторов пользователей, которым будет отправлено уведомление о добавлении их исполнителями дела
		$aNotificationEventParticipantUserId = array();

		// Добавление исполнителей дела
		foreach ($aEventUserId as $iEventUserId)
		{
			if (!in_array($iEventUserId, $aIssetUsers))
			{
				$oEventUser = Core_Entity::factory('Event_User')
					->user_id($iEventUserId);

				// При добавлении события указываем создателя
				$bAddEvent
					&& $iEventUserId == $oCurrentUser->id
					&& $oEventUser->creator(1);

				$oEvent->add($oEventUser);

				$iEventUserId != $oCurrentUser->id
					&& $aNotificationEventParticipantUserId[] = $iEventUserId;
			}
		}

		// Замена загруженных ранее файлов на новые
		$aEvent_Attachments = $this->_object->Event_Attachments->findAll(FALSE);
		foreach ($aEvent_Attachments as $oEvent_Attachment)
		{
			$aExistFile = Core_Array::getFiles("file_{$oEvent_Attachment->id}");

			if (!is_null($aExistFile))
			{
				if (Core_File::isValidExtension($aExistFile['name'], Core::$mainConfig['availableExtension']))
				{
					$oEvent_Attachment->saveFile($aExistFile['tmp_name'], $aExistFile['name']);
				}
			}
		}

		// New values of property
		$aNewFiles = Core_Array::getFiles("file", array());

		// New values of property
		if (is_array($aNewFiles) && isset($aNewFiles['name']))
		{
			$iCount = count($aNewFiles['name']);

			for ($i = 0; $i < $iCount; $i++)
			{
				ob_start();

				$aFile = array(
					'name' => $aNewFiles['name'][$i],
					'tmp_name' => $aNewFiles['tmp_name'][$i],
					'size' => $aNewFiles['size'][$i]
				);

				$oCore_Html_Entity_Script = Core_Html_Entity::factory('Script')
					->value("$(\"#{$windowId} #file:has(input\\[name='file\\[\\]'\\])\").eq(0).remove();");

				if (intval($aFile['size']) > 0)
				{
					$oEvent_Attachment = Core_Entity::factory('Event_Attachment');

					$oEvent_Attachment->event_id = $this->_object->id;

					$oEvent_Attachment
						->saveFile($aFile['tmp_name'], $aFile['name']);

					if (!is_null($oEvent_Attachment->id))
					{
						$oCore_Html_Entity_Script
							->value("$(\"#{$windowId} #file\").find(\"input[name='file\\[\\]']\").eq(0).attr('name', 'file_{$oEvent_Attachment->id}');");

						$this->_object->pushHistory(Core::_('Event.history_add_file', $oEvent_Attachment->file_name));
					}
				}

				$oCore_Html_Entity_Script
					->execute();

				$this->_Admin_Form_Controller->addMessage(ob_get_clean());
			}
		}

		// Завершенность изменена
		if ($eventCompletedBefore != $this->_object->completed)
		{
			$this->_object->setFinish();

			// Notify Dms
			$this->_callDmsExecution();
		}

		// Создаем уведомление
		if (!is_null($oModule = Core_Entity::factory('Module')->getByPath('event')))
		{
			// Есть сотрудники, удаленные из списка исполнителей
			if (count($aNotificationEventExcludedUserId))
			{
				// Добавляем уведомление
				$oNotification = Core_Entity::factory('Notification')
					->title($oEvent->name)
					->description(Core::_('Event.notificationDescriptionType1', $oCurrentUser->getFullName()))
					->datetime(Core_Date::timestamp2sql(time()))
					->module_id($oModule->id)
					->type(1) // 1 - сотрудник исключен из списка исполнителей дела
					->entity_id($oEvent->id)
					->save();

				// Связываем уведомление с сотрудниками
				foreach ($aNotificationEventExcludedUserId as $iUserId)
				{
					Core_Entity::factory('User', $iUserId)->add($oNotification);
				}
			}

			// Есть исполнители
			if (count($aNotificationEventParticipantUserId))
			{
				$this->_object->notifyExecutors($aNotificationEventParticipantUserId);
			}

			// Завершенность изменена
			if ($eventCompletedBefore != $this->_object->completed)
			{
				// Отправляем уведомления ответственным сотрудникам
				$this->_object->changeCompletedSendNotification();
			}
		}

		// Клиенты, связанные с событием
		$aEventSiteusers = $oEvent->Event_Siteusers->findAll();

		$aEventSiteuserId = Core_Array::getPost('event_siteuser_id');

		!is_array($aEventSiteuserId) && $aEventSiteuserId = array();

		$aExcludeIndexes = array();
		foreach ($aEventSiteusers as $oEventSiteuser)
		{
			$iSearchIndex = array_search($oEventSiteuser->siteuser_company_id ? ('company_' . $oEventSiteuser->siteuser_company_id) : ('person_' . $oEventSiteuser->siteuser_person_id), $aEventSiteuserId);
			//$iSearchIndex = array_search($oEventSiteuser->id, $aEventSiteuserId);

			if ($iSearchIndex === FALSE)
			{
				$oEventSiteuser->delete();
			}
			else
			{
				$aExcludeIndexes[] = $iSearchIndex;
			}
		}

		foreach ($aEventSiteuserId as $key => $sEventSiteuserId)
		{
			if (!in_array($key, $aExcludeIndexes))
			{
				$aTmp = explode('_', $sEventSiteuserId);

				if (count($aTmp))
				{
					if ($aTmp[0] == 'company')
					{
						$iEventSiteuserCompanyId = intval($aTmp[1]);
						$iEventSiteuserPersonId = 0;
					}
					else
					{
						$iEventSiteuserCompanyId = 0;
						$iEventSiteuserPersonId = intval($aTmp[1]);
					}

					$oEventSiteuser = Core_Entity::factory('Event_Siteuser')
						->siteuser_company_id($iEventSiteuserCompanyId)
						->siteuser_person_id($iEventSiteuserPersonId);

					$oEvent->add($oEventSiteuser);
				}
			}
		}

		// Связывание дела со сделкой
		if (Core::moduleIsActive('deal') && $bAddEvent)
		{
			$iDealId = intval(Core_Array::getGet('deal_id'));

			if ($iDealId)
			{
				$oDeal = Core_Entity::factory('Deal', $iDealId);
				$oDeal->add($this->_object);
			}
		}

		// Связывание дела с лидом
		if (Core::moduleIsActive('lead') && $bAddEvent)
		{
			$iLeadId = intval(Core_Array::getGet('lead_id'));

			if ($iLeadId)
			{
				$oLead = Core_Entity::factory('Lead', $iLeadId);
				$oLead->add($this->_object);
			}
		}

		if ($previousObject->event_status_id != $this->_object->event_status_id)
		{
			$oEvent_Status = $this->_object->Event_Status;

			$event_status_name = $oEvent_Status->id
				? $oEvent_Status->name
				: Core::_('Event.notStatus');

			$event_status_color = $oEvent_Status->id
				? $oEvent_Status->color
				: '#aebec4';

			$this->_object->notifyBotsChangeStatus();
			$this->_object->pushHistory(Core::_('Event.history_change_status', $event_status_name), $event_status_color);
		}

		if (!$bAddEvent && $previousObject->event_type_id != $this->_object->event_type_id)
		{
			$this->_object->notifyBotsChangeType();
			$this->_object->pushHistory(Core::_('Event.history_change_type', $this->_object->Event_Type->name), $this->_object->Event_Type->color);
		}

		if ($previousObject->event_group_id != $this->_object->event_group_id)
		{
			$oEvent_Group = $this->_object->Event_Group;

			$event_group_name = $oEvent_Group->id
				? $oEvent_Group->name
				: Core::_('Event.notGroup');

			$event_group_color = $oEvent_Group->id
				? $oEvent_Group->color
				: '#aebec4';

			$this->_object->pushHistory(Core::_('Event.history_change_group', $event_group_name), $event_group_color);
		}

		if ($previousObject->important != $this->_object->important)
		{
			$important_color = $this->_object->important
				? '#e25822'
				: '#333333';

			$this->_object->pushHistory(Core::_('Event.history_change_important' . $this->_object->important), $important_color);
		}

		if ($previousObject->place != $this->_object->place)
		{
			if ($previousObject->place == '')
			{
				$place_text = Core::_('Event.history_change_place_from_empty', $this->_object->place);
			}
			elseif ($this->_object->place == '')
			{
				$place_text = Core::_('Event.history_change_place_to_empty', $previousObject->place);
			}
			else
			{
				$place_text = Core::_('Event.history_change_place', $previousObject->place, $this->_object->place);
			}

			$this->_object->pushHistory($place_text);
		}

		if ($previousObject->completed != $this->_object->completed)
		{
			$this->_object->pushHistory(Core::_('Event.history_change_completed' . $this->_object->completed));
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}


	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		$oUser = Core_Auth::getCurrentUser();

		//$parent_id = Core_Array::getGet('parent_id', 0);
		$siteuser_id = Core_Array::getGet('siteuser_id', 0);
		$bShow_subs = !is_null(Core_Array::getGet('show_subs'));

		// $windowId = $this->_Admin_Form_Controller->getWindowId();

		// Всегда id_content
		$sJsRefresh = '<script>

		$.updateCaldav();

		if ($("#id_content .kanban-board").length && typeof _windowSettings != \'undefined\') {
			$(\'#id_content #refresh-toggler\').click();
		}

		// CRM-Projects
		if ($("#id_content .timeline-crm").length && typeof _windowSettings != \'undefined\') {
			$.adminLoad({ path: \'/admin/crm/project/entity/index.php\', additionalParams: \'crm_project_id=' . $this->_object->crm_project_id . '\', windowId: \'id_content\' });
		}
		// /CRM-Projects

		var jA = $("li[data-type=timeline] a");
		if (jA.length)
		{
			$.adminLoad({ path: jA.eq(0).data("path"), additionalParams: jA.eq(0).data("additional"), windowId: jA.eq(0).data("window-id") });
		}';

		//if (!$parent_id && !$siteuser_id)
		if (!$bShow_subs && !$siteuser_id)
		{
			$sJsRefresh .= 'var jAEvents = $("li[data-type=event] a");
			if (jAEvents.length)
			{
				$.adminLoad({ path: jAEvents.eq(0).data("path"), additionalParams: jAEvents.eq(0).data("additional"), windowId: jAEvents.eq(0).data("window-id") });
			}';
		}

		$sJsRefresh .= '</script>';

		switch ($operation)
		{
			case 'save':
			case 'saveModal':
			case 'applyModal':
				$aEventUserId = Core_Array::get($this->_formValues, 'event_user_id');

				// Редактирование дела
				if (!is_null($this->_object->id))
				{
					$oEventCreator = $this->_object->getCreator();
					if (!is_null($oEventCreator) && $oEventCreator->id == $oUser->id)
					{
						// Заданы ответственные сотрудники
						if (is_array($aEventUserId) && count($aEventUserId))
						{
							$bError = TRUE;

							foreach ($aEventUserId as $iEventUserId)
							{
								//$aTmp = explode('_', $sEventUserId);

								// Идентификатор сотрудника
								//$iEventUserId = intval($aTmp[2]);

								// Проверяем задан ли создатель дела
								if ($iEventUserId == $oUser->id)
								{
									$bError = FALSE;
									break;
								}
							}

							if ($bError)
							{
								$this->addMessage(
									Core_Message::get(Core::_('Event.creatorNotDefined'), 'error')
								);
								return TRUE;
							}
						}
						else // Нет ответственных пользователей
						{
							$this->addMessage(
								Core_Message::get(Core::_('Event.notSetResponsibleEmployees'), 'error')
							);
							return TRUE;
						}
					}
				}

				$operation == 'saveModal' && $this->addMessage($sJsRefresh);
				$operation == 'applyModal' && $this->addContent($sJsRefresh);
			break;
			case 'markDeleted':
				$this->_object->markDeleted();
				$this->addMessage($sJsRefresh);
			break;
		}

		// Запрещаем сотрудникам доступ к делам, в которых они не принимают участие
		if (!is_null($this->_object->id) && !$this->_object->checkPermission2View($oUser)
			/*$this->_object->Event_Users->getCountByUser_id($oUser->id, FALSE) == 0*/)
		{
			return TRUE;
		}

		return parent::execute($operation);
	}

	/**
	 * Add save and apply buttons
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _addButtons()
	{
		$oUser = Core_Auth::getCurrentUser();

		$iCreatorUserId = 0;

		if ($this->_object->id)
		{
			$aEvent_Users = $this->_object->Event_Users->findAll(FALSE);

			foreach ($aEvent_Users as $oEvent_User)
			{
				// Идентификатор создателя дела
				$oEvent_User->creator && $iCreatorUserId = $oEvent_User->user_id;
			}
		}
		else
		{
			$iCreatorUserId = $oUser->id;
		}

		// Кнопки
		$oAdmin_Form_Entity_Buttons = parent::_addButtons();

		// Удаляем "Удалить"
		if ($this->_object->id && $iCreatorUserId != $oUser->id)
		{
			$aButtons = $oAdmin_Form_Entity_Buttons->getChildren();

			foreach ($aButtons as $key => $oButton)
			{
				if ($oButton->id == 'action-button-delete')
				{
					$oAdmin_Form_Entity_Buttons->deleteChild($key);
				}

				/*if ($oButton->id == 'action-button-back')
				{
					$oButton->onclick = $this->_Admin_Form_Controller->getAdminLoadAjax($this->_Admin_Form_Controller->getPath(), NULL, NULL, '');
				}*/
			}
		}

		return $oAdmin_Form_Entity_Buttons;
	}
}