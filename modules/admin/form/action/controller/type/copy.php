<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Typical copying controller
 *
 * Типовой контроллер копирования с изменением имени копируемого элемента.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Copy extends Admin_Form_Action_Controller
{
	/**
	 * Key name for saving in Core_Registry
	 * @var string
	 */
	protected $_keyName = NULL;

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$this->_object
			->changeCopiedName(TRUE)
			->copy();

		return $this;
	}
}