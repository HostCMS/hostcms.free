<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Controller_Markdeleted.
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Controller_Markdeleted extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return bool
	 */
	public function execute($operation = NULL)
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

		return FALSE;
	}
}