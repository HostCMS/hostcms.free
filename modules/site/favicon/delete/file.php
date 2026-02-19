<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site_Favicon_Delete_File
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Site_Favicon_Delete_File extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'site_favicon_id',
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		if ($this->site_favicon_id)
		{
			$oSite_Favicon = Core_Entity::factory('Site_Favicon', $this->site_favicon_id);
			if (!is_null($oSite_Favicon))
			{
				$oSite_Favicon->deleteFavicon();
			}
		}

		// Break execution for other
		return TRUE;
	}
}