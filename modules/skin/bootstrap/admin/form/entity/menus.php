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
class Skin_Bootstrap_Admin_Form_Entity_Menus extends Admin_Form_Entity
{
	/**
	 * Execute all children
	 * @return self
	 */
	public function executeChildren()
	{
		foreach ($this->_children as $key => $oCore_Html_Entity)
		{
			$oCore_Html_Entity
				->position($key)
				->execute();
		}

		return $this;
	}
}