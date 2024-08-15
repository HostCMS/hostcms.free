<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site copying controller
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Site_Controller_Copy extends Admin_Form_Action_Controller_Type_Copy
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		parent::execute($operation);

		$this->addMessage('<script>$.loadSiteList()</script>');

		return $this;
	}
}