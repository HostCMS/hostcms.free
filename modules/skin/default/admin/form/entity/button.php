<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$this->onclick = 'var $form = $(this).parents("form"); $.toogleInputsActive($form, true); setTimeout(function(){ $.toogleInputsActive($form, false) }, 1000); ' . $this->onclick;
		}

		$aAttr = $this->getAttrsString();
		?><input <?php echo implode(' ', $aAttr) ?>/> <?php
	}
}
