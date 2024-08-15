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
class Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs extends Admin_Form_Entity
{
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$count = count($this->_children);

		foreach ($this->_children as $key => $oAdmin_Form_Entity)
		{
			$key == $count - 1 && $oAdmin_Form_Entity->separator = '';

			$oAdmin_Form_Entity->execute();
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}