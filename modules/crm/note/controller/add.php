<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Note_Controller_Add
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Crm_Note_Controller_Add extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$sSubject = Core_Array::getRequest('subject_note', '', 'trim');
		$sText = Core_Array::getRequest('text_note', '', 'trim');

		$parent_id = Core_Array::getRequest('parent_id', 0, 'int');

		$oCrm_Note = Core_Entity::factory('Crm_Note');
		$oCrm_Note->subject = $sSubject;
		$oCrm_Note->text = $sText;
		$oCrm_Note->parent_id = $parent_id;
		$oCrm_Note->datetime = Core_Date::timestamp2sql(time());

		$result = Core_Array::getPost('result', 0, 'int');

		if ($result)
		{
			$oCrm_Note->result = Core_Array::getPost('completed', 0, 'int');
		}

		$oCrm_Note->save();

		$this->_object = $oCrm_Note;
	}
}