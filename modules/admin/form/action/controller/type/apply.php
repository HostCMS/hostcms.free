<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 * Типовой контроллер применения изменений в списке сущностей
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Apply extends Admin_Form_Action_Controller
{
	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 * @hostcms-event Admin_Form_Action_Controller_Type_Apply.onBeforeExecute
	 * @hostcms-event Admin_Form_Action_Controller_Type_Apply.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		// Получение списка полей объекта
		//$aColumns = $this->_object->getTableColumns();

		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($this->_object));

		$aAdmin_Form_Fields = $this->_Admin_Form_Action->Admin_Form->Admin_Form_Fields->findAll();

		$bChanged = FALSE;

		foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
		{
			$this->_apply($oAdmin_Form_Field)
				&& $bChanged = TRUE;
		}

		$bChanged && $this->_object->save();

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($this->_object));

		// Clear cache
		if (method_exists($this->_object, 'clearCache'))
		{
			$this->_object->clearCache();
		}

		return $this;
	}
	
	protected function _apply($oAdmin_Form_Field)
	{
		$bChanged = FALSE;
		
		$sInputName = 'apply_check_' . $this->_datasetId . '_' . $this->_object->getPrimaryKey() . '_fv_' . $oAdmin_Form_Field->id;

		$value = Core_Array::getPost($sInputName);

		if (!is_null($value))
		{
			$columnName = $oAdmin_Form_Field->name;

			if (property_exists($this->_object, $columnName) || isset($this->_object->$columnName))
			{
				$aTableColumns = $this->_object->getTableColumns();
				
				/*if (isset($aTableColumns[$columnName]))
				{
					print_r($aTableColumns[$columnName]);
				}*/
				
				switch ($oAdmin_Form_Field->type)
				{
					case 5: // Datetime
						$value = $value != ''
							? Core_Date::datetime2sql($value)
							: '0000-00-00 00:00:00';
					break;
					case 6: // Date
						$value = $value != ''
							? Core_Date::date2sql($value)
							: '0000-00-00';
					break;
				}
				
				$this->_object->$columnName = $value;
				$bChanged = TRUE;
				
				// Backend Callback Method, HostCMS 6.7.9+
				if ($oAdmin_Form_Field->type == 10 && method_exists($this->_object, $columnName))
				{
					$this->_object->$columnName($value);
				}
			}
			//else/*if (method_exists($this->_object, $columnName))*/
			elseif (method_exists($this->_object, $columnName))
			{
				$this->_object->$columnName($value);
				$bChanged = TRUE;
			}
		}
		
		return $bChanged;
	}
}