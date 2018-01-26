<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Button extends Admin_Form_Entity_Input
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->type('button')->class('adminButton');
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		if (!is_null($this->onclick) && is_object($this->_Admin_Form_Controller))
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();
			$this->onclick = "$.toogleInputsActive('{$windowId}', false); setTimeout(function(){ $.toogleInputsActive('{$windowId}', true) }, 1000); {$this->onclick}";
		}

		$aAttr = $this->getAttrsString();

		?><input <?php echo implode(' ', $aAttr) ?>/> <?php
	}
}
