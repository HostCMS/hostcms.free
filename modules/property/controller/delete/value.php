<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties.
 * Контроллер удаления значения дополнительного свойства
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Property_Controller_Delete_Value extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'linkedObject',
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 * @hostcms-event Property_Controller_Delete_Value.onBeforeDeleteSmallFile
	 * @hostcms-event Property_Controller_Delete_Value.onBeforeDeleteLargeFile
	 * @hostcms-event Property_Controller_Delete_Value.onBeforeDelete
	 */
	public function execute($operation = NULL)
	{
		if (!is_array($this->linkedObject))
		{
			$this->linkedObject = array($this->linkedObject);
		}

		preg_match('/(\w*)property_(\d*)_(\d*)/i', $operation, $matches);

		if (count($matches) == 4)
		{
			$propertyId = $matches[2];
			$valueId = $matches[3];

			$oProperty = Core_Entity::factory('Property')->find($propertyId);

			if (!is_null($oProperty->id))
			{
				$oValue = $oProperty->getValueById($valueId);

				if (!is_null($oValue))
				{
					if ($oValue->entity_id == $this->_object->id)
					{
						if ($oProperty->type == 2)
						{
							$oValue->setDir($this->linkedObject[$this->_datasetId]->getDirPath($this->_object));
						}

						$windowId = $this->_Admin_Form_Controller->getWindowId();

						if ($matches[1] == 'small_')
						{
							Core_Event::notify('Property_Controller_Delete_Value.onBeforeDeleteSmallFile', $this, array($oProperty, $oValue));

							$oValue->deleteSmallFile();

							ob_start();
							Core_Html_Entity::factory('Script')
								->value("$(\"#{$windowId} #preview_small_id_property_{$oProperty->id}_{$oValue->id},#{$windowId} #delete_small_id_property_{$oProperty->id}_{$oValue->id}\").remove()")
								->execute();
							$this->addMessage(ob_get_clean());
						}
						elseif ($matches[1] == 'large_')
						{
							Core_Event::notify('Property_Controller_Delete_Value.onBeforeDeleteLargeFile', $this, array($oProperty, $oValue));

							$oValue->deleteLargeFile();

							ob_start();
							Core_Html_Entity::factory('Script')
								->value("$(\"#{$windowId} #preview_large_id_property_{$oProperty->id}_{$oValue->id}, #{$windowId} #delete_large_id_property_{$oProperty->id}_{$oValue->id}\").remove()")
								->execute();
							$this->addMessage(ob_get_clean());
						}
						else
						{
							Core_Event::notify('Property_Controller_Delete_Value.onBeforeDelete', $this, array($oProperty, $oValue));

							$oValue->delete();
						}

						$this->addMessage(Core_Message::get(Core::_('Property.deletePropertyValue_success')));
					}
					else
					{
						// Значение св-ва принадлежит другому объекту
						$this->addMessage(Core_Message::get(Core::_('Property.value_other_owner'), 'error'));
					}
				}
				else
				{
					// Значение св-ва не найдено
					$this->addMessage(Core_Message::get(Core::_('Property.value_not_found'), 'error'));
				}
			}
			else
			{
				// Св-во не найдено
				$this->addMessage(Core::_('Property.property_not_found'));
			}
		}

		// Break execution for other
		return TRUE;
	}
}