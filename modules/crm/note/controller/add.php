<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Note_Controller_Add
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Note_Controller_Add extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$sCommentText = Core_Array::getRequest('text_note', '', 'trim');

		$oCrm_Note = Core_Entity::factory('Crm_Note');
		$oCrm_Note->text = $sCommentText;
		$oCrm_Note->datetime = Core_Date::timestamp2sql(time());
		$oCrm_Note->save();

		$this->_object = $oCrm_Note;
	}
}