<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			// $this->onclick = 'var $form = $($(this).parents("form")[0]); console.log($form);  $.toogleInputsActive($form, true); setTimeout(function(){ $.toogleInputsActive($form, false) }, 1000); ' . $this->onclick;
			$this->onclick = 'var $form = $(this).closest("form"); $.toogleInputsActive($form, true); setTimeout(function(){ $.toogleInputsActive($form, false) }, 1000); ' . $this->onclick;
		}

		$aAttr = $this->getAttrsString();
		?><input <?php echo implode(' ', $aAttr) ?>/> <?php
	}
}
