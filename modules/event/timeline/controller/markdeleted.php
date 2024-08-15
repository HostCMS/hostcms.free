<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Timeline_Controller_Markdeleted.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Timeline_Controller_Markdeleted extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return bool
	 */
	public function execute($operation = NULL)
	{
		$this->_object->markDeleted();

		switch (get_class($this->_object))
		{
			case 'Event_Model':
				$type = 'event';
			break;
			case 'Crm_Note_Model':
				$type = 'note';
			break;
			case 'Dms_Document_Model':
				$type = 'dms_document';
			break;
			default:
				$type = NULL;
		}

		if (!is_null($type))
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();
			$aExplodeWindowId = explode('-', $windowId);

			$this->addMessage("<script>$(function() {
				var jA = $('#" . $aExplodeWindowId[0] . " li[data-type=" . $type . "] a');
				if (jA.length)
				{
					$.adminLoad({ path: jA.data('path'), additionalParams: jA.data('additional'), windowId: jA.data('window-id') });
				}
			});</script>");
		}

		return FALSE;
	}
}