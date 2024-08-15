<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Model
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
		//'event_note' => array(),
		'event' => array('foreign_key' => 'parent_id'),
		'event_history' => array(),
		'event_crm_note' => array(),
		'crm_note' => array('through' => 'event_crm_note'),
		'dms_document' => array('through' => 'event_dms_document'),
		'event_dms_document' => array(),
		'event_calendar_caldav' => array(),
		'tag' => array('through' => 'tag_event'),
		'tag_event' => array(),
		'event_checklist' => array()
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
	 * Belongs to relations
	 * @var array
	 */
	protected $_hasOne = array(
		'lead_event' => array(),
		'deal_event' => array(),
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
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'events.datetime' => 'DESC',
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
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$this->_preloadValues['guid'] = Core_Guid::get();
			$this->_preloadValues['last_modified'] = Core_Date::timestamp2sql(time());
		}
	}

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

		$oUser = Core_Auth::getCurrentUser();

		/*$nameColorClass = $this->deadline()
			? 'event-title-deadline'
			: '';

		$deadlineIcon = $this->deadline()
			? '<i class="fa fa-clock-o event-title-deadline"></i>'
			: '';*/

		$opacity = $this->completed != 0
			? 'opacity'
			: '';

		?><div class="d-flex align-items-center justify-content-between <?php echo $opacity?>"><?php
		
		// Название вместе с attachments в одном div
		?><div><?php
		if ($this->Event_Attachments->getCount(FALSE))
		{
			?><i class="fa fa-paperclip name-attachments"></i><?php
		}
		?><div class="semi-bold editable" style="display: inline-block;" id="apply_check_0_<?php echo $this->id?>_fv_1226"><?php echo htmlspecialchars((string) $this->name)?></div><?php
		?></div><?php
		
		echo $this->showChecklists();
		?></div><?php
		
		if ($this->description != '')
		{
			?><div class="event-description"><?php echo nl2br(htmlspecialchars((string) $this->description))?></div><?php
		}

		$this->relatedBackend($oAdmin_Form_Field, $oAdmin_Form_Controller);

		if (Core::moduleIsActive('tag'))
		{
			$aTags = $this->Tags->findAll(FALSE);

			foreach ($aTags as $oTag)
			{
				Core_Html_Entity::factory('Code')
					->value('<span class="badge badge-square badge-tag badge-max-width badge-lightgray margin-right-5" title="' . htmlspecialchars($oTag->name) . '"><i class="fa fa-tag"></i> ' . htmlspecialchars($oTag->name) . '</span>')
					->execute();
			}
		}

		?><div class="event-creator-wrapper"><div class="small2"><?php

		$oEventCreator = $this->getCreator();

		// Сотрудник - создатель дела
		if (!is_null($oEventCreator) && $oEventCreator->id != $oUser->id)
		{
			$currentColor = Core_Str::createColor($oEventCreator->id);

			?><span style="color: <?php echo $currentColor?>"><?php $oEventCreator->showLink($oAdmin_Form_Controller->getWindowId())?></span><?php
		}

		// Ответственные сотрудники
		$aEvent_Users = $this->Event_Users->findAll();

		if (count($aEvent_Users))
		{
			foreach ($aEvent_Users as $oEvent_User)
			{
				$oResponsibleUser = $oEvent_User->User;

				if (!is_null($oEventCreator) && !is_null($oResponsibleUser->id) && $oEventCreator->id != $oResponsibleUser->id)
				{
					$currentColor = Core_Str::createColor($oResponsibleUser->id);

					?>
					<div class="deal-responsible" style="color: <?php echo $currentColor?>"><?php $oResponsibleUser->showLink($oAdmin_Form_Controller->getWindowId())?></div><?php
				}
			}
		}

		?></div><span class="small darkgray text-align-right"><i class="fa fa-clock-o"></i><?php echo Core_Date::time2string(time() - Core_Date::sql2timestamp($this->datetime))?></span><?php
		?></div><?php

		return ob_get_clean();
	}

	/**
	 * Show event checklists
	 * @param bool $kanban
	 * @return string
	 */
	public function showChecklists($kanban = FALSE)
	{
		$iTotalCount = $iCompletedCount = 0;

		$aEvent_Checklists = $this->Event_Checklists->findAll(FALSE);
		foreach ($aEvent_Checklists as $oEvent_Checklist)
		{
			$iCompletedCount += $oEvent_Checklist->Event_Checklist_Items->getCountByCompleted(1, FALSE);
			$iTotalCount += $oEvent_Checklist->Event_Checklist_Items->getCount(FALSE);
		}

		if ($iTotalCount)
		{
			$completed = $iCompletedCount / $iTotalCount;

			if ($completed == 1)
			{
				$color = '#61ec02';
			}
			elseif ($completed >= 0.67)
			{
				$color = '#ecdd02';
			}
			elseif ($completed >= 0.34)
			{
				$color = '#ec9c02';
			}
			else
			{
				$color = '#ec4402';
			}

			$style = "min-width: 45px; color: " . Core_Str::hex2darker($color, 0.2) . "; background-color: " . Core_Str::hex2lighter($color, 0.88) . ';';

			$margin = !$kanban
				? ' margin-left: 5px;'
				: ' margin-bottom: 5px; margin-top: 5px;';

			$style .= $margin;

			return '<span class="badge badge-round badge-max-width" style="' . $style . '"><i class="fa-regular fa-square-check margin-right-5"></i>' . $iCompletedCount . '/' . $iTotalCount . '</span>';
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function event_type_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->event_type_id
			? $this->showType()
			: '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function event_status_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$path = $oAdmin_Form_Controller->getPath();

		$oUser = Core_Auth::getCurrentUser();

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
				->data('change-context', 'true')
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

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function deadlineBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$color = '';

		$bShowTime = $this->all_day ? FALSE : TRUE;

		if ($this->deadline != '0000-00-00 00:00:00')
		{
			if ($this->completed)
			{
				// $class = 'darkgray';
				$color = '#777';
			}
			elseif (Core_Date::sql2timestamp($this->deadline) < time())
			{
				// $class = 'badge badge-orange';
				$color = '#fb6e52';
			}
			elseif (Core_Date::timestamp2sqldate(Core_Date::sql2timestamp($this->deadline)) == Core_Date::timestamp2sqldate(time()))
			{
				// $class = 'badge badge-palegreen';
				$color = '#a0d468';
			}
			else
			{
				// $class = 'badge badge-lightgray';
				$color = '#999';
			}

			$text = Core_Date::timestamp2string(Core_Date::sql2timestamp($this->deadline), $bShowTime);
		}
		else
		{
			$color = '#888';

			$text = Core::_('Event.without_deadline');
		}

		$style = $color != ''
			? "border-color: " . $color . "; color: " . Core_Str::hex2darker($color, 0.2) . "; background-color: " . Core_Str::hex2lighter($color, 0.88)
			: '';

		return $this->completed != 1 && $style != ''
			? '<span class="badge badge-round badge-max-width" style="' . $style . '">' . $text . '</span>'
			: '';
	}

	/**
	 * Backend callback method
	 */
	public function relatedBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Core::moduleIsActive('deal')
			&& $this->showDeals($oAdmin_Form_Controller);

		Core::moduleIsActive('lead')
			&& $this->showLeads($oAdmin_Form_Controller);

		if (Core::moduleIsActive('dms'))
		{
			echo $this->showDocuments($oAdmin_Form_Controller);
		}

		Core::moduleIsActive('crm_project')
			&& $this->crm_project_id
			// && $this->Crm_Project->showKanbanLine($oAdmin_Form_Controller);
			&& $this->showCrmProjects($oAdmin_Form_Controller);
	}

	/**
	 * Show deal badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 */
	public function showDeals($oAdmin_Form_Controller)
	{
		$aDeal_Events = $this->Deal_Events->findAll(FALSE);

		foreach ($aDeal_Events as $oDeal_Event)
		{
			$oDeal = $oDeal_Event->Deal;

			if (!is_null($oDeal->Deal_Template->color) && !is_null($oDeal->name))
			{
				?><div class="related-events-wrapper">
					<div class="related-events" style="color: <?php echo $oDeal->Deal_Template->color?>; background-color:<?php echo Core_Str::hex2lighter($oDeal->Deal_Template->color, 0.88)?>"><i class="fa fa-handshake-o"></i></div>
					<div><a style="color: <?php echo $oDeal->Deal_Template->color?>" href="/admin/deal/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $oDeal->id?>]=1" onclick="$.modalLoad({path: '/admin/deal/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oDeal->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>', width: '90%'}); return false"><?php echo htmlspecialchars($oDeal->name)?></a></div>
				</div><?php
			}
		}
	}

	/**
	 * Show lead badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 */
	public function showLeads($oAdmin_Form_Controller)
	{
		$aLead_Events = $this->Lead_Events->findAll(FALSE);

		foreach ($aLead_Events as $oLead_Event)
		{
			$oLead = $oLead_Event->Lead;

			if (!is_null($oLead->Lead_Status->color))
			{
				?><div class="related-events-wrapper">
					<div class="related-events" style="color: <?php echo $oLead->Lead_Status->color?>; background-color:<?php echo Core_Str::hex2lighter($oLead->Lead_Status->color, 0.88)?>"><i class="fa fa-user-circle-o"></i></div>
					<div><a style="color: <?php echo $oLead->Lead_Status->color?>;" href="/admin/lead/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $oLead->id?>]=1" onclick="$.modalLoad({path: '/admin/lead/index.php', action: 'edit', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oLead->id?>]=1', windowId: '<?php echo $oAdmin_Form_Controller->getWindowId()?>', width: '90%'}); return false"><?php echo htmlspecialchars($oLead->getFullName())?></a></div>
				</div><?php
			}
		}
	}

	/**
	 * Show document badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 * @return string
	 */
	public function showDocuments($oAdmin_Form_Controller)
	{
		ob_start();

		$oUser = Core_Auth::getCurrentUser();

		$aDms_Workflow_Execution_Users = $this->Dms_Workflow_Execution_Users->findAll(FALSE);
		foreach ($aDms_Workflow_Execution_Users as $oDms_Workflow_Execution_User)
		{
			$oDms_Document = $oDms_Workflow_Execution_User->Dms_Workflow_Execution->Dms_Document;

			$name = $oDms_Document->Dms_Document_Type->name . ' ' . ($oDms_Document->number != '' ? $oDms_Document->numberBackend() : htmlspecialchars($oDms_Document->name));

			if ($oDms_Document->checkPermission2Edit($oUser) || $oDms_Document->checkPermission2View($oUser))
			{
				$action = NULL;

				if ($oDms_Document->checkPermission2Edit($oUser))
				{
					$action = 'edit';
				}
				elseif ($oDms_Document->checkPermission2View($oUser))
				{
					$action = 'view';
				}

				if (!is_null($oDms_Document->Dms_Document_Type->color) && !is_null($action))
				{
					?><div class="related-events-wrapper">
						<div class="related-events" style="color: <?php echo $oDms_Document->Dms_Document_Type->color?>; background-color:<?php echo Core_Str::hex2lighter($oDms_Document->Dms_Document_Type->color, 0.88)?>"><i class="fa fa-columns"></i></div>
						<div><a style="color: <?php echo $oDms_Document->Dms_Document_Type->color?>;" href="/admin/dms/document/index.php?hostcms[action]=edit&hostcms[checked][0][<?php echo $oDms_Document->id?>]=1" onclick="$.modalLoad({path: '/admin/dms/document/index.php', action: '<?php echo $action?>', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oDms_Document->id?>]=1', windowId: 'modal<?php echo $oDms_Document->id?>', width: '90%'}); return false"><?php echo htmlspecialchars($name)?></a></div>
					</div><?php
				}
			}
		}

		return ob_get_clean();
	}

	/**
	 * Show document badge
	 * @param Admin_Form_Controller_Model $oAdmin_Form_Controller
	 * @return string
	 */
	public function showCrmProjects($oAdmin_Form_Controller)
	{
		Core::moduleIsActive('crm_project')
			&& $this->Crm_Project->showBadge($oAdmin_Form_Controller);
	}

	/**
	 * Update kanban
	 * @param int $event_status_id
	 * @return array
	 */
	public function	updateKanban($event_status_id)
	{
		$aReturn = array();

		$oUser = Core_Auth::getCurrentUser();

		$queryBuilder = Core_QueryBuilder::select(array('COUNT(*)', 'count'))
			->from('events')
			->join('event_users', 'events.id', '=', 'event_users.event_id')
			->where('event_users.user_id', '=', $oUser->id)
			->where('events.event_status_id', '=', $event_status_id)
			->groupBy('events.id');

		$aRow = $queryBuilder->execute()->asAssoc()->result();

		$count = count($aRow);

		$aReturn['data'] = $count == 0
			? ''
			: array('count' => $count);

		return $aReturn;
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
				$sResult .= '<div class="profile-container tickets-container counterparty-block"><ul class="tickets-list">';

				foreach ($aEvent_Siteusers as $key => $oEvent_Siteuser)
				{
					$class = $key > 1 ? 'hidden' : '';

					if ($oEvent_Siteuser->siteuser_company_id)
					{
						$oSiteuser_Company = $oEvent_Siteuser->Siteuser_Company;

						$oSiteuser_Company->id
							&& $sResult .= $oSiteuser_Company->getProfileBlock($class);
					}
					elseif ($oEvent_Siteuser->siteuser_person_id)
					{
						$oSiteuser_Person = $oEvent_Siteuser->Siteuser_Person;

						$oSiteuser_Person->id
							&& $sResult .= $oSiteuser_Person->getProfileBlock($class);
					}
				}

				$sResult .= '</ul>';

				if ($key > 1)
				{
					$sResult .= '<div class="more" onclick="$.showCounterparty(this)">' . Core::_('Event.more') . ' <i class="fas fa-chevron-down"></div>';
				}

				$sResult .= '</div>';
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
	/* public function event_group_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
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
				->data('change-context', 'true')
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
	} */

	public function getCompletedDropdown($oAdmin_Form_Controller)
	{
		$oEvent_Type = $this->Event_Type;

		$successfully = $oEvent_Type->id && $oEvent_Type->successfully != ''
			? htmlspecialchars($oEvent_Type->successfully)
			: Core::_('Admin_Form.successfully');

		$failed = $oEvent_Type->id && $oEvent_Type->failed != ''
			? htmlspecialchars($oEvent_Type->failed)
			: Core::_('Admin_Form.failed');

		$aCompleted = array(
			1 => array(
				'value' => $successfully,
				'color' => '#a0d468',
				'icon' => 'fa-solid fa-circle-check fa-fw margin-right-5'
			),
			-1 => array(
				'value' => $failed,
				'color' => '#ed4e2a',
				'icon' => 'fa-solid fa-xmark fa-fw margin-right-5'
			)
		);

		return Admin_Form_Entity::factory('Dropdownlist')
			->options($aCompleted)
			->name('completed')
			->divAttr(array('class' => 'margin-left-10 event-completed hidden'))
			->controller($oAdmin_Form_Controller)
			->execute();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function importantBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$sExclamation = '<i class="fa-solid fa-fire ' . ($this->important ? 'fire' : 'fa-inactive') . '"></i>';

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

		$important_color = $this->important
			? '#e25822'
			: '#333333';

		$this->pushHistory(Core::_('Event.history_change_important' . $this->important), $important_color);

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
				Core_Entity::factory('User', $oEventUser->user_id)->add($oNotification);
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

		switch (intval($this->completed))
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

		if ($this->completed == 1 || $this->completed == -1)
		{
			$this->finish = Core_Date::timestamp2sql(time());
		}
		else
		{
			$this->finish = '0000-00-00 00:00:00';
		}

		$this->save();

		$this->changeCompletedSendNotification();

		$this->pushHistory(Core::_('Event.history_change_completed' . $this->completed));

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
				$ico = '<i class="fa fa-check-circle fa-active palegreen" title="' . Core::_('Event.complete') . '"></i>';
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
				Core_Entity::factory('User', $oEventUser->user_id)->add($oNotification);
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
						->description(Core::_('Event.notificationDescriptionType0', $oEventCreator->getFullName(), FALSE))
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
		//$this->Event_Notes->deleteAll(FALSE);
		$this->Events->deleteAll(FALSE);
		$this->Event_Histories->deleteAll(FALSE);
		$this->Crm_Notes->deleteAll(FALSE);
		$this->Event_Crm_Notes->deleteAll(FALSE);
		$this->Event_Checklists->deleteAll(FALSE);

		$this->Event_Dms_Documents->deleteAll(FALSE);
		$this->Event_Siteusers->deleteAll(FALSE);
		$this->Event_Calendar_Caldavs->deleteAll(FALSE);

		if (Core::moduleIsActive('deal'))
		{
			$this->Deal_Events->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('lead'))
		{
			$this->Lead_Events->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('tag'))
		{
			$this->Tag_Events->deleteAll(FALSE);
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
		if (!Core_File::isDir($this->getPath()))
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
		if (Core_File::isDir($this->getPath()))
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
		?><span class="badge badge-square margin-right-10" style="color: <?php echo $this->Event_Type->color?>; background-color:<?php echo Core_Str::hex2lighter($this->Event_Type->color, 0.88)?>"><i class="<?php echo htmlspecialchars((string) $this->Event_Type->icon)?>"></i> <?php echo htmlspecialchars((string) $this->Event_Type->name)?></span><?php
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
	 * Push history
	 * @return self
	 */
	public function pushHistory($text, $color = '#333333')
	{
		$oEvent_History = Core_Entity::factory('Event_History');
		$oEvent_History->event_id = $this->id;
		$oEvent_History->text = $text;
		$oEvent_History->color = $color;

		$oEvent_History->save();

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
	 * Show content
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function showContent($oAdmin_Form_Controller)
	{
		$oUser = Core_Auth::getCurrentUser();

		ob_start();

		// $path = $oAdmin_Form_Controller->getPath();

		$oEventCreator = $this->getCreator();

		// Временая метка создания дела
		// $iEventCreationTimestamp = Core_Date::sql2timestamp($oEvent->datetime);

		// Сотрудник - создатель дела
		$userIsEventCreator = !is_null($oEventCreator) && $oEventCreator->id == $oUser->id;

		// $oEvent_Type = $oEvent->Event_Type;

		if ($this->completed == 1 || $this->completed == -1)
		{
			?><span class="margin-right-5"><?php echo $this->getCompletedIco()?></span><?php
		}

		$this->event_type_id && $this->showType();

		// Менять статус дела может только его создатель
		if ($userIsEventCreator)
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
				->onchange("$.adminLoad({path: '/admin/event/index.php', additionalParams: 'hostcms[checked][0][{$this->id}]=0&eventStatusId=' + $(this).find('li[selected]').prop('id'), action: 'changeStatus', windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
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
			?>
			<div class="event-status">
				<i class="fa fa-circle" style="margin-right: 5px; color: <?php echo $sEventStatusColor?>"></i><span style="color: <?php echo $sEventStatusColor?>"><?php echo $sEventStatusName?></span>
			</div>
			<?php
		}

		$nameColorClass = $this->deadline()
			? 'event-title-deadline'
			: '';

		$deadlineIcon = $this->deadline()
			? '<i class="fa fa-clock-o event-title-deadline"></i>'
			: '';

		?>
		<div class="event-title <?php echo $nameColorClass?>"><?php echo htmlspecialchars($this->name)?></div>

		<?php
		if ($this->description != '')
		{
			?><div class="crm-description"><?php echo Core_Str::cutSentences(strip_tags($this->description), 250)?></div><?php
		}
		?>

		<div class="crm-description"><div class="crm-date"><?php

		echo $deadlineIcon;

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

		// $iDeltaTime = time() - $iEventCreationTimestamp;

		// ФИО создателя дела, если оным не является текущий сотрудник
		/*if (!$userIsEventCreator && !is_null($oEventCreator))
		{
			?><div class="<?php echo $oEventCreator->isOnline() ? 'online margin-left-20' : 'offline margin-left-20'?> margin-right-5"></div><?php
			?><span class="gray"><?php $oEventCreator->showLink($oAdmin_Form_Controller->getWindowId())?></span><?php
		}*/
		?>
		</div></div><?php

		return	ob_get_clean();
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

							// $oModule = Core_Entity::factory('Module')->getByPath('event');
							// $sUid = $this->id . '_' . $oModule->id;

							$sUrl = $Calendar_Caldav_Controller->getCalendar() . $this->guid . '.ics';

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

		// $oEventCreator = $this->getCreator();

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
			case 'deleteEntity': // Delete in the Project module
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
				: Core_Date::strftime($oSite->date_time_format, Core_Date::sql2timestamp($this->start)))
			->addXmlTag('deadline', $this->deadline == '0000-00-00 00:00:00'
				? $this->deadline
				: Core_Date::strftime($oSite->date_time_format, Core_Date::sql2timestamp($this->deadline)))
			->addXmlTag('datetime', $this->datetime == '0000-00-00 00:00:00'
				? $this->datetime
				: Core_Date::strftime($oSite->date_time_format, Core_Date::sql2timestamp($this->datetime)))
			->addXmlTag('date', $this->datetime == '0000-00-00 00:00:00'
				? $this->datetime
				: Core_Date::strftime($oSite->date_format, Core_Date::sql2timestamp($this->datetime)));

		$this->event_group_id
			&& $this->addEntity($this->Event_Group);

		$this->event_status_id
			&& $this->addEntity($this->Event_Status);

		$this->event_type_id
			&& $this->addEntity($this->Event_Type);

		return $this;
	}

	/**
	 * Apply tags for item
	 * @param string $sTags string of tags, separated by comma
	 * @return self
	 */
	public function applyTags($sTags)
	{
		$aTags = explode(',', $sTags);

		return $this->applyTagsArray($aTags);
	}

	/**
	 * Apply array tags for item
	 * @param array $aTags array of tags
	 * @return self
	 */
	public function applyTagsArray(array $aTags)
	{
		// Удаляем связь метками
		$this->Tag_Events->deleteAll(FALSE);

		foreach ($aTags as $tag_name)
		{
			$tag_name = trim($tag_name);

			if ($tag_name != '')
			{
				$oTag = Core_Entity::factory('Tag')->getByName($tag_name, FALSE);

				if (is_null($oTag))
				{
					$oTag = Core_Entity::factory('Tag');
					$oTag->name = $oTag->path = $tag_name;
					$oTag->save();
				}

				$this->add($oTag);
			}
		}

		return $this;
	}
}