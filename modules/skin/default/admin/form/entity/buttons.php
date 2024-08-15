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
class Skin_Default_Admin_Form_Entity_Buttons extends Admin_Form_Entity
{
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Buttons.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Buttons.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);

		?><div class="formButtons sticky-actions"><?php
		$this->executeChildren();
		?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}