<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Breadcrumbs extends Admin_Form_Entity
{
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$count = count($this->_children);

		foreach ($this->_children as $key => $oAdmin_Form_Entity)
		{
			$key == $count - 1 && $oAdmin_Form_Entity->separator = '';

			$oAdmin_Form_Entity->execute();
		}
	}
}