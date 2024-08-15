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
class Skin_Default_Admin_Form_Entity_Menus extends Admin_Form_Entity
{
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Menus.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Menus.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		?><table cellpadding="0" cellspacing="0" border="0" class="main_ul"><tr><?php
		$this->executeChildren();
		?></tr></table><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}