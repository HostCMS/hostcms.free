<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Button.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Button.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		if (!is_null($this->onclick) && is_object($this->_Admin_Form_Controller))
		{
			// $this->onclick = 'var $form = $($(this).parents("form")[0]); console.log($form);  $.toogleInputsActive($form, true); setTimeout(function(){ $.toogleInputsActive($form, false) }, 1000); ' . $this->onclick;
			$this->onclick = 'var $form = $(this).closest("form"); $.toogleInputsActive($form, true); setTimeout(function(){ $.toogleInputsActive($form, false) }, 1000); ' . $this->onclick;
		}

		$aAttr = $this->getAttrsString();
		?><input <?php echo implode(' ', $aAttr) ?>/> <?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}
