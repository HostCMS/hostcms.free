<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Model
 *
 * @package HostCMS
 * @subpackage Event
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Model extends Core_Entity
{
	/**
	 * Коды для типа уведомлений
	 * 0 - сотрудник добален исполнителем дела
	 * 1 - сотрудник исключен из списка исполнителей дела
	 * 2 - дело завершено
	 * 3 - дело возобновлено
	 * 4 - дело стало важным (дело отмечено как важное)
	 * 5 - дело стало незначительным (дело отмечено как неважное)
	 * 6 - статус дела изменен
	 * 7 - группа дела изменена
	 * 8 - дело провалено
	*/

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'event_attachment' => array(),
		'event_siteuser' => array(),
		'event_user' => array(),
		'user' => array('through' => 'event_user'),
		'deal_event' => array(),
		'lead_event' => array(),
		'dms_workflow_execution_user' => array(),
		'event_note' => array(),
		'event' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'event_type' => array(),
		'event_group' => array(),
		'event_status' => array(),
		'event' => array('foreign_key' => 'parent_id'),
		'crm_project' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'datetime',
		'start',
		'deadline',
	);

	/**
	 * Backend property
	 * @var mixed
	 */
	public $status = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $counterparty = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $group = NULL;

	/**
	 * Check deadline
	 * @return bool
	 */
	public function deadline()
	{
		return $this->completed == 0
			&& $this->deadline != '0000-00-00 00:00:00' && Core_Date::sql2timestamp($this->deadline) < time();
	}

	/**
	 * Get parent event
	 * @return Event|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Event', $this->parent_id)
			: NULL;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$path = $oAdmin_Form_Controller->getPath();

		$oUser = Core_Auth::getCurrentUser();

		$nameColorClass = $this->deadline()
			? 'event-title-deadline'
			: '';

		$deadlineIcon = $this->deadline()
			? '<i class="fa fa-clock-o event-title-deadline"></i>'
			: '';

		?><div class="semi-bold editable <?php echo $nameColorClass?>" style="display: inline-block;" id="apply_check_0_<?php echo $this->id?>_fv_1226"><?php echo $deadlineIcon, htmlspecialchars($this->name)?></div>
		<?php if ($this->parent_id)
		{
			?>
			<span class="small gray"> → <?php echo htmlspecialchars($this->Event->name)?></span>
			<?php
		}
		if ($this->description != '')
		{
			?><div class="event-description"><?php echo nl2br(htmlspecialchars($this->description))?></div><?php
		}
		?><div class="event-title"><?php
		$this->event_type_id && $this->showType();

		// Менять статус дела может только его создатель
		if ($this->checkPermission2ChangeStatus($oUser))
		{
			// Список статусов дел
			$aEvent_Statuses = Core_Entity::factory('Event_Status')->findAll();

			$aMasEventStatuses = array(array('value' => Core::_('Event.notStatus'), 'color' => '#aebec4'));

			foreach ($aEvent_Statuses as $oEvent_Status)
			{
				$aMasEventStatuses[$oEvent_Status->id] = array('value' => $oEvent_Status->name, 'color' => $oEvent_Status->color);
			}

			$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

			$oCore_Html_Entity_Dropdownlist
				->value($this->event_status_id)
				->options($aMasEventStatuses)
				//->class('btn-group event-status')
				->onchange("mainFormLocker.unlock(); $.adminLoad({path: '{$path}', additionalParams: 'hostcms[checked][0][{$this->id}]=0&eventStatusId=' + $(this).find('li[selected]').prop('id'), action: 'changeStatus', windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
				->execute();
		}
		else
		{
			if ($this->event_status_id)
			{
				$oEvent_Status = Core_Entity::factory('Event_Status', $this->event_status_id);

				$sEventStatusName = htmlspecialchars($oEvent_Status->name);
				$sEventStatusColor = htmlspecialchars($oEvent_Status->color);
			}
			else
			{
				$sEventStatusName = Core::_('Event.notStatus');
				$sEventStatusColor = '#aebec4';
			}
			?><div class="event-status margin-right-10"><i class="fa fa-circle margin-right-5" style="color: <?php echo $sEventStatusColor?>"></i><span style="color: <?php echo $sEventStatusColor?>"><?php echo $sEventStatusName?></span></div><?php
		}

		?><div class="event-date"><?php
		if ($this->all_day)
		{
			echo Event_Controller::getDate($this->start);
		}
		else
		{
			if (!is_null($this->start) && $this->start != '0000-00-00 00:00:00')
			{
				echo Event_Controller::getDateTime($this->start);
			}

			if (!is_null($this->start) && $this->start != '0000-00-00 00:00:00'
				&& !is_null($this->deadline) && $this->deadline != '0000-00-00 00:00:00'
			)
			{
				echo ' — ';
			}

			if (!is_null($this->deadline) && $this->deadline != '0000-00-00 00:00:00')
			{
				?><strong><?php echo Event_Controller::getDateTime($this->deadline);?></strong><?php
			}
		}
		?></div><?php

		$oEventCreator = $this->getCreator();

		// Сотрудник - создатель дела
		if (!(!is_null($oEventCreator) && $oEventCreator->id == $oUser->id) && !is_null($oEventCreator))
		{
			?><div class="event-date margin-left-10"><div class="<?php echo $oEventCreator->isOnline() ? 'online' : 'offline'?> margin-right-5"></div><a href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][<?php echo $oEventCreator->id?>]=1" onclick="$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oEventCreator->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>'}); return false"><?php echo htmlspecialchars($oEventCreator->getFullName());?></a></div><?php
		}

		?><span class="small darkgray pull-right"><i class="fa fa-clock-o"></i><?php echo Core_Date::time2string(time() - Core_Date::sql2timestamp($this->datetime))?></span><?php
		?></div><?php

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function relatedBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Core::moduleIsActive('deal')
			&& $this->showDeals($oAdmin_Form_Controller);

		Core::moduleIsActive('lead')
			&& $this->showLeads($oAdmin_Form_Controller);

		Core::moduleIsActive('dms')
			&& $this->showDocuments($oAdmin_Form_Controller);

		Core::moduleIsActive('crm_project')
			&& $this->showCrmProject($oAdmin_Form_Controller);
	}

	/**
	 * Show deal badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 * @return string
	 */
	public function showDeals($oAdmin_Form_Controller)
	{
		$aDeal_Events = $this->Deal_Events->findAll(FALSE);

		foreach ($aDeal_Events as $oDeal_Event)
		{
			$oDeal = $oDeal_Event->Deal;

			?><span class="label label-related margin-right-5" style="color: <?php echo $oDeal->Deal_Template->color?>; background-color:<?php echo Core_Str::hex2lighter($oDeal->Deal_Template->color, 0.88)?>"><i class="fa fa-handshake-o margin-right-5"></i><a style="color: inherit;" href="/admin/deal/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $oDeal->id?>]=1" onclick="$.modalLoad({path: '/admin/deal/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oDeal->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>'}); return false"><?php echo htmlspecialchars($oDeal->name)?></a></span><?php
		}
	}

	/**
	 * Show lead badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 * @return string
	 */
	public function showLeads($oAdmin_Form_Controller)
	{
		$aLead_Events = $this->Lead_Events->findAll(FALSE);

		foreach ($aLead_Events as $oLead_Event)
		{
			$oLead = $oLead_Event->Lead;

			?><span class="label label-related margin-right-5" style="color: <?php echo $oLead->Lead_Status->color?>; background-color:<?php echo Core_Str::hex2lighter($oLead->Lead_Status->color, 0.88)?>"><i class="fa fa-user-circle-o margin-right-5"></i><a style="color: inherit;" href="/admin/lead/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $oLead->id?>]=1" onclick="$.modalLoad({path: '/admin/lead/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oLead->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>'}); return false"><?php echo htmlspecialchars($oLead->getFullName())?></a></span><?php
		}
	}

	/**
	 * Show document badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 * @return string
	 */
	public function showDocuments($oAdmin_Form_Controller)
	{
		$aDms_Workflow_Execution_Users = $this->Dms_Workflow_Execution_Users->findAll();

		foreach ($aDms_Workflow_Execution_Users as $oDms_Workflow_Execution_User)
		{
			$oDms_Document = $oDms_Workflow_Execution_User->Dms_Workflow_Execution->Dms_Document;

			$name = $oDms_Document->Dms_Document_Type->name . ' ' . $oDms_Document->numberBackend();

			?><span class="label label-related margin-right-5" style="color: <?php echo $oDms_Document->Dms_Document_Type->color?>; background-color:<?php echo Core_Str::hex2lighter($oDms_Document->Dms_Document_Type->color, 0.88)?>"><i class="fa fa-columns margin-right-5"></i><a style="color: inherit;" href="/admin/dms/document/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $oDms_Document->id?>]=1" onclick="$.modalLoad({path: '/admin/dms/document/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oDms_Document->id?>]=1', windowId: 'modal<?php echo $oDms_Document->id?>'}); return false"><?php echo htmlspecialchars($name)?></a></span><?php
		}
	}

	/**
	 * Show crm project badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 * @return string
	 */
	public function showCrmProject($oAdmin_Form_Controller)
	{
		if ($this->crm_project_id)
		{
			$oCrm_Project = $this->Crm_Project;

			$color = strlen($oCrm_Project->color)
				? htmlspecialchars($oCrm_Project->color)
				: '#aebec4';

			?><span class="label label-related margin-right-5" style="color: <?php echo $color?>; background-color:<?php echo Core_Str::hex2lighter($color, 0.88)?>"><i class="fa fa-folder-o margin-right-5"></i><a style="color: inherit;" href="/admin/crm/project/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $oCrm_Project->id?>]=1" onclick="$.modalLoad({path: '/admin/crm/project/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oCrm_Project->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>'}); return false"><?php echo htmlspecialchars($oCrm_Project->name)?></a></span><?php
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function counterpartyBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$sResult = '';

		if (Core::moduleIsActive('siteuser'))
		{
			$aEvent_Siteusers = $this->Event_Siteusers->findAll(FALSE);

			if (count($aEvent_Siteusers))
			{
				$sResult .= '<div class="profile-container tickets-container"><ul class="tickets-list">';

				foreach ($aEvent_Siteusers as $oEvent_Siteuser)
				{
					if ($oEvent_Siteuser->siteuser_company_id)
					{
						$oSiteuser_Company = $oEvent_Siteuser->Siteuser_Company;

						$sResult .= $oSiteuser_Company->getProfileBlock();
					}
					elseif ($oEvent_Siteuser->siteuser_person_id)
					{
						$oSiteuser_Person = $oEvent_Siteuser->Siteuser_Person;

						$sResult .= $oSiteuser_Person->getProfileBlock();
					}
				}

				$sResult .= '</div"></ul>';
			}
		}

		return $sResult;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function event_group_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$path = $oAdmin_Form_Controller->getPath();

		$oEventCreator = $this->getCreator();
		$oCurrentUser = Core_Auth::getCurrentUser();

		// Сотрудник - создатель дела
		$userIsEventCreator = (!is_null($oEventCreator) && $oEventCreator->id == $oCurrentUser->id);

		// Менять группу дела может только его создатель
		if ($userIsEventCreator)
		{
			$aMasEventGroups = array(array('value' => Core::_('Event.notGroup'), 'color' => '#aebec4'));

			// Группы дел
			$aEventGroups = Core_Entity::factory('Event_Group')->findAll();

			foreach ($aEventGroups as $oEventGroup)
			{
				$aMasEventGroups[$oEventGroup->id] = array('value' => $oEventGroup->name, 'color' => $oEventGroup->color);
			}

			$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

			$oCore_Html_Entity_Dropdownlist
				->value($this->event_group_id)
				->options($aMasEventGroups)
				->onchange("mainFormLocker.unlock(); $.adminLoad({path: '{$path}', additionalParams: 'hostcms[checked][0][{$this->id}]=0&eventGroupId=' + $(this).find('li[selected]').prop('id'), action: 'changeGroup', windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
				->execute();
		}
		else
		{
			if ($this->event_group_id)
			{
				$oEventGroup = Core_Entity::factory('Event_Group', $this->event_group_id);

				$sEventGroupName = htmlspecialchars($oEventGroup->name);
				$sEventGroupColor = htmlspecialchars($oEventGroup->color);
			}
			else
			{
				$sEventGroupName = Core::_('Event.notGroup');
				$sEventGroupColor = '#aebec4';
			}
			?>
			<div class="event-group">
				<i class="fa fa-circle" style="margin-right: 5px; color: <?php echo $sEventGroupColor?>"></i><span style="color: <?php echo $sEventGroupColor?>"><?php echo $sEventGroupName?></span>
			</div>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function importantBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$sExclamation = '<i class="fa fa-exclamation-circle ' . ($this->important ? 'red' : 'fa-inactive') . '"></i>';

		// Создатель дела
		$oEventCreator = $this->getCreator();

		// Авторизованный сотрудник
		$oUserCurrent = Core_Auth::getCurrentUser();

		// Изменять важность дела может только его создатель
		if (!is_null($oEventCreator) && $oEventCreator->id == $oUserCurrent->id)
		{
			ob_start();

			Admin_Form_Entity::factory('a')
				->href("/admin/event/index.php?hostcms[action]=changeImportant&hostcms[checked][0][{$this->id}]=0")
				->onclick("$.adminLoad({path: '/admin/event/index.php',additionalParams: 'hostcms[checked][0][{$this->id}]=0', action: 'changeImportant', windowId: '{$oAdmin_Form_Controller->getWindowId()}'}); return false;")
				->add(
					Admin_Form_Entity::factory('Code')
						->html($sExclamation)
				)
				->execute();

			$sExclamation = ob_get_clean();
		}

		return $sExclamation;
	}

	/**
	 * Change important
	 * @return self
	 * @hostcms-event event.onBeforeChangeImportant
	 * @hostcms-event event.onAfterChangeImportant
	 */
	public function changeImportant()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeImportant', $this);

		$this->important = 1 - $this->important;
		$this->save();

		$this->changeImportantSendNotification();

		Core_Event::notify($this->_modelName . '.onAfterChangeImportant', $this);

		return $this;
	}

	/*
	 * Send notification about change important
	 * @return self
	 */
	public function changeImportantSendNotification()
	{
		$oModule = Core_Entity::factory('Module')->getByPath('event');

		if (!is_null($oModule))
		{
			// Ответственные сотрудники
			$oEventUsers = $this->Event_Users;

			// Текущий сотрудник
			$oUser = Core_Auth::getCurrentUser();

			$oEventUsers
				->queryBuilder()
				->where('user_id', '!=', $oUser->id);

			$aEventUsers = $oEventUsers->findAll();

			// Добавляем уведомление
			$oNotification = Core_Entity::factory('Notification')
				->title(strip_tags($this->name))
				->description($this->important ? Core::_('Event.notificationDescriptionType4') : Core::_('Event.notificationDescriptionType5'))
				->datetime(Core_Date::timestamp2sql(time()))
				->module_id($oModule->id)
				->type($this->important ? 4 : 5) // 4 - дело стало важным, 5 - дело стало незначительным
				->entity_id($this->id)
				->save();

			// Связываем уведомление с ответственными сотрудниками
			foreach ($aEventUsers as $oEventUser)
			{
				Core_Entity::factory('User', $oEventUser->user_id)
					->add($oNotification);
			}
		}
	}

	/**
	 * Set event finish
	 * @return self
	 */
	public function setFinish()
	{
		$this->finish = $this->completed == 0
			? '0000-00-00 00:00:00'
			: Core_Date::timestamp2sql(time());

		return $this->save();
	}

	/**
	 * Change completed
	 * @return self
	 * @hostcms-event event.onBeforeChangeCompleted
	 * @hostcms-event event.onAfterChangeCompleted
	 */
	public function changeCompleted()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeCompleted', $this);

		switch ($this->completed)
		{
			case 0:
				$this->completed = 1;
			break;
			case 1:
				$this->completed = -1;
			break;
			case -1:
				$this->completed = 0;
			break;
		}

		if ($this->completed = 1 || $this->completed = -1)
		{
			$this->finish = Core_Date::timestamp2sql(time());
		}

		// $this->completed = 1 - $this->completed;
		// !$this->completed && $this->event_status_id = 0;
		$this->save();

		$this->changeCompletedSendNotification();

		Core_Event::notify($this->_modelName . '.onAfterChangeCompleted', $this);

		return $this;
	}

	/**
	 * Change completed button
	 * @return self
	 * @hostcms-event event.onBeforeChangeCompletedButton
	 * @hostcms-event event.onAfterChangeCompletedButton
	 */
	public function changeCompletedButton()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeCompletedButton', $this);

		$this->completed = $this->completed == 0
			? 1
			: 0;

		$this->save();

		$this->changeCompletedSendNotification();

		Core_Event::notify($this->_modelName . '.onAfterChangeCompletedButton', $this);

		return $this;
	}

	/**
	 * Get completed ico
	 * @return string
	 */
	public function getCompletedIco()
	{
		switch ($this->completed)
		{
			case 0:
				$ico = '<i class="fa fa-circle-o fa-inactive" title="' . Core::_('Event.in_process') . '"></i>';
			break;
			case 1:
				$ico = '<i class="fa fa-dot-circle-o fa-active azure" title="' . Core::_('Event.complete') . '"></i>';
			break;
			case -1:
				$ico = '<i class="fa fa-times darkorange" title="' . Core::_('Event.failed') . '"></i>';
			break;
			default:
				$ico = '—';
		}

		return $ico;
	}

	/*
	 * Change completed notification
	 * @return self
	 */
	public function changeCompletedSendNotification()
	{
		$oModule = Core_Entity::factory('Module')->getByPath('event');

		if (!is_null($oModule))
		{
			switch ($this->completed)
			{
				case 0:
				default:
					$description = Core::_('Event.notificationDescriptionType3');
					$type = 3;
				break;
				case 1:
					$description = Core::_('Event.notificationDescriptionType2');
					$type = 2;
				break;
				case -1:
					$description = Core::_('Event.notificationDescriptionType8');
					$type = 8;
				break;
			}

			// Добавляем уведомление
			$oNotification = Core_Entity::factory('Notification')
				->title(strip_tags($this->name))
				->description($description)
				->datetime(Core_Date::timestamp2sql(time()))
				->module_id($oModule->id)
				->type($type) // 2 - дело завершено, 3 - дело возобновлено, 8 - провалено
				->entity_id($this->id)
				->save();

			// Текущий сотрудник
			$oUser = Core_Auth::getCurrentUser();

			// Ответственные сотрудники
			$oEventUsers = $this->Event_Users;

			$oEventUsers
				->queryBuilder()
				->where('user_id', '!=', $oUser->id);

			$aEventUsers = $oEventUsers->findAll();

			// Связываем уведомление с ответственными сотрудниками
			foreach ($aEventUsers as $oEventUser)
			{
				Core_Entity::factory('User', $oEventUser->user_id)
					->add($oNotification);
			}
		}
	}

	/**
	 * Get creator
	 * @return User_Model|NULL
	 */
	public function getCreator()
	{
		$oEvent_User = $this->Event_Users->getByCreator(1);

		return !is_null($oEvent_User)
			? $oEvent_User->User
			: NULL;
	}

	/**
	 * Уведомить сотрудников о добавлении в исполнители дела
	 * @param array $aUserIDs
	 * @return self
	 */
	public function notifyExecutors(array $aUserIDs)
	{
		// Есть исполнители
		if (count($aUserIDs))
		{
			$oEventCreator = $this->getCreator();

			if ($oEventCreator && !is_null($oModule = Core_Entity::factory('Module')->getByPath('event')))
			{
				$oNotifications = Core_Entity::factory('Notification');
				$oNotifications->queryBuilder()
					->where('notifications.module_id', '=', $oModule->id)
					->where('notifications.type', '=', 0)
					->where('notifications.entity_id', '=', $this->id);

				$oNotification = $oNotifications->getFirst(FALSE);

				if (is_null($oNotification))
				{
					// Добавляем уведомление
					$oNotification = Core_Entity::factory('Notification')
						->title($this->name)
						->description(Core::_('Event.notificationDescriptionType0', $oEventCreator->getFullName()))
						->datetime(Core_Date::timestamp2sql(time()))
						->module_id($oModule->id)
						->type(0) // 0 - сотрудник добавлен исполнителем дела
						->entity_id($this->id)
						->save();
				}

				// Связываем уведомление с сотрудниками
				foreach ($aUserIDs as $iUserId)
				{
					Core_Entity::factory('User', $iUserId)->add($oNotification);
				}
			}
		}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event event.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Event_Attachments->deleteAll(FALSE);
		$this->Event_Users->deleteAll(FALSE);
		$this->Event_Notes->deleteAll(FALSE);
		$this->Events->deleteAll(FALSE);

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Event_Siteusers->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('deal'))
		{
			$this->Deal_Events->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('lead'))
		{
			$this->Lead_Events->deleteAll(FALSE);
		}

		/*if (Core::moduleIsActive('dms'))
		{
			$this->Dms_Workflow_Execution_Users->deleteAll(FALSE);
		}*/

		$this->deleteDir();

		return parent::delete($primaryKey);
	}

	/**
	 * Get message files href
	 * @return string
	 */
	public function getHref()
	{
		 return 'upload/private/events/' . Core_File::getNestingDirPath($this->id, 3) . '/event_' . $this->id . '/';
	}

	/**
	 * Get path for files
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Create message files directory
	 * @return self
	 */
	public function createDir()
	{
		if (!is_dir($this->getPath()))
		{
			try
			{
				Core_File::mkdir($this->getPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete message files directory
	 * @return self
	 */
	public function deleteDir()
	{
		if (is_dir($this->getPath()))
		{
			try
			{
				Core_File::deleteDir($this->getPath());
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete message attachment file
	 * @param $event_attachment_id attachment id
	 * @return self
	 */
	public function deleteFile($event_attachment_id)
	{
		$oEvent_Attachment = $this->Event_Attachments->getById($event_attachment_id);
		if ($oEvent_Attachment)
		{
			$oEvent_Attachment->delete();
		}

		return $this;
	}

	/**
	 * Show type badge
	 * @return string
	 */
	public function showType()
	{
		?><span class="badge badge-square margin-right-10" style="color: <?php echo $this->Event_Type->color?>; background-color:<?php echo Core_Str::hex2lighter($this->Event_Type->color, 0.88)?>"><i class="<?php echo htmlspecialchars($this->Event_Type->icon)?>"></i> <span class="hidden-xxs hidden-xs"><?php echo htmlspecialchars($this->Event_Type->name)?></span></span><?php
	}

	/**
	 * Get today
	 * @param bool $bCache cache
	 * return array
	 */
	public function getToday($bCache = TRUE)
	{
		$dateTime = date('Y-m-d');

		$this->queryBuilder()
			->where('events.completed', '=', 0)
			->open()
				->where('events.start', '>', $dateTime . ' 00:00:00')
				//->setOr()
				->where('events.deadline', '<', $dateTime . ' 23:59:59')
				->setOr()
				// ->where('events.all_day', '=', 1)
				->where('events.start', '<', $dateTime . ' 23:59:59')
				->where('events.deadline', '>', $dateTime . ' 00:00:00')
			->close()
			->clearOrderBy()
			->orderBy('start', 'DESC')
			->orderBy('important', 'DESC');

		return $this->findAll($bCache);
	}

	/**
	 * Notify Bots
	 * @return self
	 */
	public function notifyBotsChangeStatus()
	{
		if (Core::moduleIsActive('bot'))
		{
			$oModule = Core::$modulesList['event'];
			Bot_Controller::notify($oModule->id, 0, $this->event_status_id, $this);
		}

		return $this;
	}

	/**
	 * Notify Bots
	 * @return self
	 */
	public function notifyBotsChangeType()
	{
		if (Core::moduleIsActive('bot'))
		{
			$oModule = Core::$modulesList['event'];
			Bot_Controller::notify($oModule->id, 1, $this->event_type_id, $this);
		}

		return $this;
	}

	/**
	 * Get responsible users
	 * @return array
	 */
	public function getResponsibleUsers()
	{
		$aReturn = array();

		$aEvent_Users = $this->Event_Users->findAll();
		foreach ($aEvent_Users as $oEvent_User)
		{
			$aReturn[] = $oEvent_User->User;
		}

		return $aReturn;
	}

	/*
	 * Get start datetime
	 * @return string
	 */
	public function getStartDatetime()
	{
		return $this->start;
	}

	/*
	 * Get end datetime
	 * @return string
	 */
	public function getEndDatetime()
	{
		return $this->deadline;
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		$oUser = Core_Auth::getCurrentUser();

		$aCalendar_Caldavs = Core_Entity::factory('Calendar_Caldav')->getAllByActive(1);
		foreach ($aCalendar_Caldavs as $oCalendar_Caldav)
		{
			$oCalendar_Caldav_User = $oCalendar_Caldav->Calendar_Caldav_Users->getByUser_id($oUser->id);

			if (!is_null($oCalendar_Caldav_User)
				&& !is_null($oCalendar_Caldav_User->caldav_server)
				&& !is_null($oCalendar_Caldav_User->username)
				&& !is_null($oCalendar_Caldav_User->password)
			)
			{
				try {
					$Calendar_Caldav_Controller = Calendar_Caldav_Controller::instance($oCalendar_Caldav->driver);

					$sResponse = $Calendar_Caldav_Controller
						->setUrl($oCalendar_Caldav_User->caldav_server)
						->setUsername($oCalendar_Caldav_User->username)
						->setPassword($oCalendar_Caldav_User->password)
						->setData($oCalendar_Caldav_User->data)
						->connect();

					if (!is_null($sResponse))
					{
						$aCalendars = $Calendar_Caldav_Controller->findCalendars();

						if (count($aCalendars))
						{
							$Calendar_Caldav_Controller->setCalendar(array_shift($aCalendars));

							$oModule = Core_Entity::factory('Module')->getByPath('event');

							$sUid = $this->id . '_' . $oModule->id;
							$sUrl = $Calendar_Caldav_Controller->getCalendar() . $sUid . '.ics';

							$Calendar_Caldav_Controller->delete($sUrl);
						}
					}
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
				}
			}
		}

		return parent::markDeleted();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event event.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$aEvent_Users = $this->Event_Users->findAll(FALSE);
		foreach ($aEvent_Users as $oEvent_User)
		{
			$newObject->add(clone $oEvent_User);
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$aEvent_Siteusers = $this->Event_Siteusers->findAll(FALSE);

			foreach ($aEvent_Siteusers as $oEvent_Siteuser)
			{
				$newObject->add(clone $oEvent_Siteuser);
			}
		}

		if (Core::moduleIsActive('deal'))
		{
			$aDeal_Events = $this->Deal_Events->findAll(FALSE);

			foreach ($aDeal_Events as $oDeal_Event)
			{
				$newObject->add(clone $oDeal_Event);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	// Проверка права сотрудника на редактирование дела
	// Редактировать дело может только его создатель
	public function checkPermission2Edit($oUser = NULL)
	{
		// Добавление дела
		if (!$this->id)
		{
			return TRUE;
		}

		if (is_null($oUser))
		{
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}
		}

		$oEventCreator = $this->getCreator();

		return (!is_null($oEventCreator) && $oEventCreator->id == $oUser->id);
	}

	// Проверка права сотрудника на просмотр дела
	// Просматривать дело может любой из его участников
	public function checkPermission2View($oUser = NULL)
	{
		if (is_null($oUser))
		{
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}
		}

		return $this->Event_Users->getCountByUser_id($oUser->id, FALSE) != 0;
	}

	// Проверка права сотрудника на удаление дела
	// Удалять дело может только его создатель
	public function checkPermission2Delete($oUser = NULL)
	{
		if (is_null($oUser))
		{
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}
		}

		$oEventCreator = $this->getCreator();

		return (!is_null($oEventCreator) && $oEventCreator->id == $oUser->id);
	}

	// Проверка права сотрудника на копирование дела
	// Копировать дело может только его создатель
	public function checkPermission2Copy($oUser = NULL)
	{
		if (is_null($oUser))
		{
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}
		}

		$oEventCreator = $this->getCreator();

		return (!is_null($oEventCreator) && $oEventCreator->id == $oUser->id);
	}

	// Проверка права сотрудника на изменение важности дела
	// Важность дела может изменять только его создатель
	public function checkPermission2ChangeImportant($oUser = NULL)
	{
		if (is_null($oUser))
		{
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}
		}

		$oEventCreator = $this->getCreator();

		return (!is_null($oEventCreator) && $oEventCreator->id == $oUser->id);
	}

	// Проверка права сотрудника на изменение завершенности дела
	// Завершенность дела может изменять любой из его участников
	public function checkPermission2ChangeCompleted($oUser = NULL)
	{
		if (is_null($oUser))
		{
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}
		}

		return $this->Event_Users->getCountByUser_id($oUser->id, FALSE) != 0;
	}

	// Проверка права сотрудника на изменение статуса дела
	// Статус дела может изменять только его создатель
	public function checkPermission2ChangeStatus($oUser = NULL)
	{
		if (is_null($oUser))
		{
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}
		}

		$oEventCreator = $this->getCreator();

		return (!is_null($oEventCreator) && $oEventCreator->id == $oUser->id);
	}

	// Проверка права сотрудника на изменение группы, к которой принадлежит дел
	// Группу, к которой принадлежит дело, может изменять только его создатель
	public function checkPermission2ChangeGroup($oUser = NULL)
	{
		if (is_null($oUser))
		{
			$oUser = Core_Auth::getCurrentUser();

			if (is_null($oUser))
			{
				return FALSE;
			}
		}

		$oEventCreator = $this->getCreator();

		return (!is_null($oEventCreator) && $oEventCreator->id == $oUser->id);
	}

	/**
	 * Check user access to admin form action
	 * @param string $actionName admin form action name
	 * @param User_Model $oUser user object
	 * @return bool
	 */
	public function checkBackendAccess($actionName, $oUser)
	{
		switch ($actionName)
		{
			case 'edit':
				return $this->checkPermission2Edit($oUser) || $this->checkPermission2View($oUser);
			break;

			case 'copy':
				return $this->checkPermission2Copy($oUser);
			break;

			case 'markDeleted':
				return $this->checkPermission2Delete($oUser);
			break;

			case 'changeStatus':
				return $this->checkPermission2ChangeStatus($oUser);
			break;

			case 'changeImportant':
				return $this->checkPermission2ChangeImportant($oUser);
			break;

			case 'changeCompleted':
				return $this->checkPermission2ChangeCompleted($oUser);
			break;

			case 'changeGroup':
				return $this->checkPermission2ChangeGroup($oUser);
			break;

			case 'addEvent':
				return is_null($this->id);
			break;
		}

		return TRUE;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event event.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event event.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$this
			->clearXmlTags()
			->addXmlTag('start', $this->start == '0000-00-00 00:00:00'
				? $this->start
				: strftime($oSite->date_time_format, Core_Date::sql2timestamp($this->start)))
			->addXmlTag('deadline', $this->deadline == '0000-00-00 00:00:00'
				? $this->deadline
				: strftime($oSite->date_time_format, Core_Date::sql2timestamp($this->deadline)))
			->addXmlTag('datetime', $this->datetime == '0000-00-00 00:00:00'
				? $this->datetime
				: strftime($oSite->date_time_format, Core_Date::sql2timestamp($this->datetime)))
			->addXmlTag('date', $this->datetime == '0000-00-00 00:00:00'
				? $this->datetime
				: strftime($oSite->date_format, Core_Date::sql2timestamp($this->datetime)));

		$this->event_group_id
			&& $this->addEntity($this->Event_Group);

		$this->event_status_id
			&& $this->addEntity($this->Event_Status);

		$this->event_type_id
			&& $this->addEntity($this->Event_Type);

		return $this;
	}
}