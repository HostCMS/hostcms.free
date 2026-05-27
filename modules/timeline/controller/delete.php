<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Timeline_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Timeline
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Timeline_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$entity_id = Core_Array::getGet('entity_id', 0, 'int');

		if ($entity_id)
		{
			$model = Core_Array::getGet('model', '', 'trim');

			switch ($model)
			{
				// Timeline
				case 'Crm_Note_Model':
					$oCrm_Note = Core_Entity::factory('Crm_Note', $entity_id);

					$oCrm_Note->Timeline->markDeleted();
					$oCrm_Note->markDeleted();
				break;
				// Events
				case 'Event_Model':
					if (Core::moduleIsActive('event'))
					{
						Core_Entity::factory('Event', $entity_id)->markDeleted();
					}
				break;
				// Deals
				case 'Deal_Model':
					if (Core::moduleIsActive('deal'))
					{
						Core_Entity::factory('Deal', $entity_id)->markDeleted();
					}
				break;
				// Dms_Documents
				case 'Dms_Document_Model':
					if (Core::moduleIsActive('dms'))
					{
						Core_Entity::factory('Dms_Document', $entity_id)->markDeleted();
					}
				break;
			}
		}

		return $this;
	}
}