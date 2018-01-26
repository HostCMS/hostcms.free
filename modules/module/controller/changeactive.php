<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Modules.
 *
 * @package HostCMS
 * @subpackage Module
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Module_Controller_Changeactive extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$this->_object->changeActive();

		$this->addMessage('<script type="text/javascript">$.loadNavSidebarMenu({moduleName: \'' . Core_Str::escapeJavascriptVariable($this->_object->path) . '\'})</script>');

		return FALSE;
	}
}