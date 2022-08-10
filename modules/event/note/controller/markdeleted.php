<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Note_Controller_Markdeleted.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Event_Note_Controller_Markdeleted extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return bool
	 */
	public function execute($operation = NULL)
	{
		$iEventId = intval(Core_Array::getGet('event_id', 0));
		$oEvent = Core_Entity::factory('Event')->getById($iEventId);

		if (!is_null($oEvent))
		{
			$this->_object->markDeleted();

			$windowId = $this->_Admin_Form_Controller->getWindowId();
			$aExplodeWindowId = explode('-', $windowId);

			$this->addMessage("<script>$(function() {
				var jA = $('#" . $aExplodeWindowId[0] . " li[data-type=timeline] a');
				if (jA.length)
				{
					$.adminLoad({ path: jA.data('path'), additionalParams: jA.data('additional'), windowId: jA.data('window-id') });
				}
			});</script>");
		}

		return FALSE;
	}
}