<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount_Siteuser_Group_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Discount_Siteuser_Group_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		if ($this->_object->id && $this->_object->siteuser_id)
		{
			$this->_object->delete();

			return $this;
		}
	}
}