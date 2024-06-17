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
class Skin_Default_Admin_Form_Entity_Separator extends Admin_Form_Entity
{
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Separator.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Separator.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		?><div style="clear: both"></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}
