<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Div extends Skin_Default_Admin_Form_Entity_Div {
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		?><div <?php echo implode(' ', $aAttr) ?>><?php echo htmlspecialchars((string) $this->value)?><?php
		$this->executeChildren();
		?></div><?php
	}
}