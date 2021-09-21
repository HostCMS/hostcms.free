<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Fields.
 * Контроллер удаления значения поля
 *
 * @package HostCMS
 * @subpackage Field
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Controller_Delete_Value extends Admin_Form_Action_Controller
{
	protected $_model = NULL;

	public function model($model)
	{
		$this->_model = $model;
		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 * @hostcms-event Field_Controller_Delete_Value.onBeforeDeleteSmallFile
	 * @hostcms-event Field_Controller_Delete_Value.onBeforeDeleteLargeFile
	 * @hostcms-event Field_Controller_Delete_Value.onBeforeDelete
	 */
	public function execute($operation = NULL)
	{
		preg_match('/(\w*)field_(\d*)_(\d*)/i', $operation, $matches);

		if (count($matches) == 4)
		{
			$fieldId = $matches[2];
			$fieldValueId = $matches[3];

			$oField = Core_Entity::factory('Field')->find($fieldId);

			if (!is_null($oField))
			{
				$oValue = $oField->getValueById($fieldValueId);

				if (!is_null($oValue))
				{
					if ($oValue->field_id == $this->_object->id)
					{
						if ($oField->type == 2)
						{
							if (!is_null($this->_model))
							{
								$oObject = Core_Entity::factory($this->_model, $oValue->entity_id);
								$oValue->setDir(
									CMS_FOLDER . Field_Controller::getPath($oObject)
								);
							}
							else
							{
								throw new Core_Exception('Field_Controller_Delete_Value: Model is NULL');
							}
						}

						$windowId = $this->_Admin_Form_Controller->getWindowId();

						if ($matches[1] == 'small_')
						{
							Core_Event::notify('Field_Controller_Delete_Value.onBeforeDeleteSmallFile', $this, array($oField, $oValue));

							$oValue->deleteSmallFile();

							ob_start();
							Core::factory('Core_Html_Entity_Script')
								->value("$(\"#{$windowId} #preview_small_id_field_{$oField->id}_{$oValue->id},#{$windowId} #delete_small_id_field_{$oField->id}_{$oValue->id}\").remove()")
								->execute();
							$this->addMessage(ob_get_clean());
						}
						elseif ($matches[1] == 'large_')
						{
							Core_Event::notify('Field_Controller_Delete_Value.onBeforeDeleteLargeFile', $this, array($oField, $oValue));

							$oValue->deleteLargeFile();

							ob_start();
							Core::factory('Core_Html_Entity_Script')
								->value("$(\"#{$windowId} #preview_large_id_field_{$oField->id}_{$oValue->id}, #{$windowId} #delete_large_id_field_{$oField->id}_{$oValue->id}\").remove()")
								->execute();
							$this->addMessage(ob_get_clean());
						}
						else
						{
							Core_Event::notify('Field_Controller_Delete_Value.onBeforeDelete', $this, array($oField, $oValue));

							$oValue->delete();
						}

						$this->addMessage(Core_Message::get(Core::_('Field.deleteFieldValue_success')));
					}
					else
					{
						// Значение поля принадлежит другому объекту
						$this->addMessage(Core_Message::get(Core::_('Field.value_other_owner'), 'error'));
					}
				}
				else
				{
					// Значение поля не найдено
					$this->addMessage(Core_Message::get(Core::_('Field.value_not_found'), 'error'));
				}
			}
			else
			{
				// Поле не найдено
				$this->addMessage(Core::_('Field.field_not_found'));
			}
		}

		// Break execution for other
		return TRUE;
	}
}