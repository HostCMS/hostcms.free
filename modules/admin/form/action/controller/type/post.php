<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 * Типовой контроллер проведения
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Post extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		if (method_exists($this->_object, 'post') && method_exists($this->_object, 'unpost'))
		{
			$this->_object->posted && $this->_object->unpost();

			$this->_object->post();
		}

		return $this;
	}
}