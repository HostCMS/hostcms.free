<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Bootstrap internal view
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * @return string
	 */
	public function show()
	{
		?>
		<div id="id_message"><?php /*echo $this->message*/?></div>
		<?php
		echo $this->content;
	}
}