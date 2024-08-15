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
class Skin_Bootstrap_Admin_Form_Entity_Div extends Skin_Default_Admin_Form_Entity_Div {
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Div.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Div.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$aAttr = $this->getAttrsString();

		?><div <?php echo implode(' ', $aAttr) ?>><?php echo htmlspecialchars((string) $this->value)?><?php
		$this->executeChildren();
		?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}