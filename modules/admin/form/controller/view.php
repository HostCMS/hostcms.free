<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Controller_View
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Controller_View
{
	protected $_Admin_Form_Controller = NULL;
	
	public function __construct($oAdmin_Form_Controller)
	{
		$this->_Admin_Form_Controller = $oAdmin_Form_Controller;
	}
	
	
}