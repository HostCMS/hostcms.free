<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Controller_Add extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'event_name',
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		if (strlen($this->event_name))
		{
			$oUser = Core_Auth::getCurrentUser();

			$oEvent_Type = Core_Entity::factory('Event_Type')->getDefault();

			$type = !is_null($oEvent_Type)
				? $oEvent_Type->id
				: 0;

			$oEvent = Core_Entity::factory('Event');
			$oEvent->name = $this->event_name;
			$oEvent->datetime = Core_Date::timestamp2sql(time());
			$oEvent->start = Core_Date::timestamp2sql(time());
			$oEvent->event_type_id = $type;
			$oEvent->save();

			$oEvent_User = Core_Entity::factory('Event_User');
			$oEvent_User->event_id = $oEvent->id;
			$oEvent_User->creator = 1;
			$oEvent_User->user_id = $oUser->id;
			$oEvent_User->save();
		}

		return NULL;
	}
}