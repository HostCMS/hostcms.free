<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Entity_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Project_Entity_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$entity_id = intval(Core_Array::getGet('entity_id'));

		if ($entity_id)
		{
			$type = intval(Core_Array::getGet('type'));

			switch ($type)
			{
				// Events
				case 0:
					if (Core::moduleIsActive('event'))
					{
						Core_Entity::factory('Event', $entity_id)->markDeleted();
					}
				break;
				// Deals
				case 1:
					if (Core::moduleIsActive('deal'))
					{
						Core_Entity::factory('Deal', $entity_id)->markDeleted();
					}
				break;
				// Notes
				case 2:
					Core_Entity::factory('Crm_Note', $entity_id)->markDeleted();
				break;
				// Files
				case 3:
					Core_Entity::factory('Crm_Project_Attachment', $entity_id)->delete();
				break;
			}
		}

		return $this;
	}
}