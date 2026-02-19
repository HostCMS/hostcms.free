<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bootstrap internal view
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Internal_View extends Admin_View
{
	/**
	 * Show children elements
	 * @return self
	 */
	public function showChildren()
	{
		// Связанные с формой элементы (меню, строка навигации и т.д.)
		foreach ($this->_children as $oAdmin_Form_Entity)
		{
			if (!($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs
				|| $oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus))
			{
				$oAdmin_Form_Entity->execute();
			}
		}

		return $this;
	}

	/**
	 * Show
	 */
	public function show()
	{
		?>
		<div id="id_message"><?php /*echo $this->message*/?></div>
		<?php
		echo $this->content;
	}
}