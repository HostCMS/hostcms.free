<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Table_View_Field
{
	/**
	 * Get caption of the field
	 * @return string|NULL
	 */
	public function getCaption($admin_language_id)
	{
		return $this->name;
	}
}