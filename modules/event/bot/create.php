<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Bot_Create
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Bot_Create extends Bot_Controller
{
	/**
	 * Bot module color
	 * @var string
	 */
	protected $_color = '#fb6e52';

	/**
	 * Get bot module fields
	 * @return array
	 */
	public function getFields()
	{
		$aOptions = array();

		$aEvent_Types = Core_Entity::factory('Event_Type')->findAll();
		foreach ($aEvent_Types as $oEvent_Type)
		{
			$aOptions[$oEvent_Type->id] = array(
				'value' => $oEvent_Type->name,
				'color' => $oEvent_Type->color,
				'icon' => $oEvent_Type->icon
			);
		}

		$this->_fields = array(
			'responsible' => array(
				'caption' => Core::_('Event.responsible'),
				'type' => 'checkbox',
				'value' => TRUE,
				'obligatory' => FALSE
			),
			'employees' => array(
				'caption' => Core::_('Event.additional_receivers'),
				'type' => 'users',
				'obligatory' => FALSE
			),
			'type' => array(
				'caption' => Core::_('Event.type'),
				'type' => 'dropdown',
				'options' => $aOptions,
				'value' => FALSE,
				'obligatory' => TRUE
			),
			'theme' => array(
				'caption' => Core::_('Event.theme'),
				'type' => 'input',
				'value' => FALSE,
				'obligatory' => TRUE
			),
			'text' => array(
				'caption' => Core::_('Event.text'),
				'type' => 'wysiwyg',
				'value' => FALSE,
				'obligatory' => TRUE
			)
		);

		return parent::getFields();
	}

	/**
	 * Check available
	 */
	public function available()
	{
		return Core::moduleIsActive('event');
	}

	/**
	 * Execute business logic
	 */
	public function execute()
	{
		$aSettings = json_decode($this->_oBot_Module->json, TRUE);

		if (method_exists($this->_oObject, 'getResponsibleUsers'))
		{
			$aResponsibleUsers = isset($aSettings['responsible']) && $aSettings['responsible']
				? $this->_oObject->getResponsibleUsers()
				: array();
		}
		else
		{
			Core_Log::instance()->clear()
				->status(Core_Log::$ERROR)
				->write("Event_Bot_Create: method getResponsibleUsers() doesn`t exist in model");
		}

		if (isset($aSettings['employees']))
		{
			foreach ($aSettings['employees'] as $user_id)
			{
				$oUser = Core_Entity::factory('User')->getById($user_id);

				if (!is_null($oUser) && !in_array($oUser, $aResponsibleUsers))
				{
					$aResponsibleUsers[] = $oUser;
				}
			}
		}

		if (count($aResponsibleUsers))
		{
			$oCore_Meta = new Core_Meta();
			$oCore_Meta
				->addObject('object', $this->_oObject)
				->addObject('settings', $aSettings);

			$oEvent = Core_Entity::factory('Event');
			$oEvent->name = isset($aSettings['theme']) && strlen(trim($aSettings['theme'])) ? $oCore_Meta->apply($aSettings['theme']) : Core::_('Admin_Form.non_subject');
			$oEvent->description = isset($aSettings['text']) && strlen(trim($aSettings['text'])) ? $oCore_Meta->apply($aSettings['text']) : '';
			$oEvent->event_type_id = isset($aSettings['type']) && strlen(trim($aSettings['type'])) ? $aSettings['type'] : 0;
			$oEvent->datetime = Core_Date::timestamp2sql(time());
			$oEvent->start = Core_Date::timestamp2sql(time());
			$oEvent->save();

			foreach ($aResponsibleUsers as $oUser)
			{
				$oEvent_User = Core_Entity::factory('Event_User');
				$oEvent_User->user_id = $oUser->id;
				$oEvent->add($oEvent_User);
			}

			if (Core::moduleIsActive('siteuser') && $this->_oObject->siteuser_id)
			{
				$oSiteuser = $this->_oObject->Siteuser;

				$aSiteuser_Companies = $oSiteuser->Siteuser_Companies->findAll(FALSE);
				foreach ($aSiteuser_Companies as $oSiteuser_Company)
				{
					$oEvent_Siteuser = Core_Entity::factory('Event_Siteuser');
					$oEvent_Siteuser->siteuser_company_id = $oSiteuser_Company->id;
					$oEvent_Siteuser->siteuser_person_id = 0;
					$oEvent->add($oEvent_Siteuser);
				}

				$aSiteuser_People = $oSiteuser->Siteuser_People->findAll(FALSE);
				foreach ($aSiteuser_People as $oSiteuser_Person)
				{
					$oEvent_Siteuser = Core_Entity::factory('Event_Siteuser');
					$oEvent_Siteuser->siteuser_company_id = 0;
					$oEvent_Siteuser->siteuser_person_id = $oSiteuser_Person->id;
					$oEvent->add($oEvent_Siteuser);
				}
			}
		}
	}
}