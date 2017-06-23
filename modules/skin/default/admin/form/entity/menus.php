<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Menus extends Admin_Form_Entity
{
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		?><table cellpadding="0" cellspacing="0" border="0" class="main_ul"><tr><?php
		$this->executeChildren();
		?></tr></table><?php
	}
}