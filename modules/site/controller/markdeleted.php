<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sites.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Site_Controller_Markdeleted extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		// Delete current site
		if ($this->_object->id == CURRENT_SITE)
		{
			$this->addMessage(
				Core_Message::get(Core::_('Site.delete_current_site'), 'error')
			);

			return NULL;
		}

		// Delete last site
		if (Core_Entity::factory('Site')->getCount() == 1)
		{
			$this->addMessage(
				Core_Message::get(Core::_('Site.delete_last_site'), 'error')
			);

			return NULL;
		}

		$oUsers = Core_Entity::factory('User');
		$oUsers->queryBuilder()
			->select('users.*')
			->join('user_groups', 'users.user_group_id', '=', 'user_groups.id')
			->where('users.superuser', '=', 1)
			->where('user_groups.site_id', '!=', $this->_object->id);

		// All superusers belong to current site
		if ($oUsers->getCount() == 0)
		{
			$this->addMessage(
				Core_Message::get(Core::_('Site.delete_site_all_superusers_belongs'), 'error')
			);

			return NULL;
		}

		$this->addMessage('<script type="text/javascript">$.loadSiteList()</script>');

		$this->_object->markDeleted();

		return FALSE;
	}
}