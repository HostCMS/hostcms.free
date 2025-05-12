<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sql_Table_View_Apply
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Sql_Table_View_Apply extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 * @hostcms-event Sql_Table_View_Apply.onBeforeExecute
	 * @hostcms-event Sql_Table_View_Apply.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($this->_object));

		//print_r(get_object_vars($this->_object));

		$tableName = $this->_object->getTableName();

		$primaryKeyName = $this->_object->getPrimaryKeyName();

		$aFileds = $this->_object->getTableColumns();
		foreach ($aFileds as $key => $aRow)
		{
			$sInputName = 'apply_check_0_' . $this->_object->$primaryKeyName . '_fv_' . $key;

			$value = Core_Array::getPost($sInputName);

			if (!is_null($value))
			{
				// Если старое значение NULL, а пришла пустая строка, то не изменяем NULL => ''
				if (isset($aRow['null']) && $aRow['null'])
				{
					$windowId = $this->_Admin_Form_Controller->getWindowId();

					if (/*is_null($this->_object->$key) &&*/ $value === '')
					{
						$value = NULL;

						// add
						$this->_Admin_Form_Controller->addMessage(
							"<script>$('#{$windowId} span#{$sInputName}').addClass('editable-null');</script>"
						);
					}
					else
					{
						// remove
						$this->_Admin_Form_Controller->addMessage(
							"<script>$('#{$windowId} span#{$sInputName}').removeClass('editable-null');</script>"
						);
					}
				}

				Core_QueryBuilder::update($tableName)
					->set($key, $value)
					->where($primaryKeyName, '=', $this->_object->$primaryKeyName)
					->execute();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($this->_object));

		return $this;
	}
}