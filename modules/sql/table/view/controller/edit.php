<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sql_Table_View_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Table_View_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->skipColumns = array();
	}

	/**
	 * Add user_id field
	 * @param Admin_Form_Entity_Model $oAdmin_Form_Entity
	 * @param string $sTabName
	 */
	protected function _addUserIdField($oAdmin_Form_Entity, $sTabName)
	{
		// Nothing to do
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$aFields = $this->_object->getTableColumns();

		foreach ($aFields as $columnName => $columnArray)
		{
			$oField = $this->getField($columnName)->caption($columnName);

			// Remove 'lib'
			$aFormat = $oField->format;
			if (isset($aFormat['lib']))
			{
				unset($aFormat['lib']);
				$oField->format($aFormat);
			}

			/*switch ($columnArray['type'])
			{
				case 'int':
					$oField->divAttr(array('class' => 'form-group col-xs-12 col-md-6 col-lg-4'));
				break;
			}*/

			switch ($columnArray['datatype'])
			{
				case 'date':
				case 'time':
				case 'datetime':
				case 'timestamp':
					$oField->divAttr(array('class' => 'form-group col-xs-12 col-md-6 col-lg-4'));
				break;
			}
		}

		$primaryKeyName = $this->_object->getPrimaryKeyName();

		$oMainTab
			->add(
				// Оригинальное имя поля
				Admin_Form_Entity::factory('Input')
					->name('source_pk')
					->value($this->_object->$primaryKeyName)
					->divAttr(array('class' => 'hidden'))
			);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Sql_Table_View_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$primaryKeyName = $this->_object->getPrimaryKeyName();

		$tableName = $this->_object->getTableName();

		$oDataBase = Core_DataBase::instance();

		$aValues = array();
		$aFields = $this->_object->getTableColumns();
		//echo '<pre>'; var_dump($aFields); echo '</pre>';
		foreach ($aFields as $columnName => $columnArray)
		{
			$value = Core_Array::get($this->_formValues, $columnName);

			if ($columnName == $primaryKeyName && $value == '')
			{
				continue;
			}

			if ($value == '' && $columnArray['null'])
			{
				$value = NULL;
			}
			else
			{
				$value = $this->_correctValue($value, $columnArray);
			}

			$aValues[$columnName] = $value;
		}

		// Экранируем значения
		$aValues = array_map(array($oDataBase, 'quote'), $aValues);

		// INSERT
		if ($this->_formValues['source_pk'] === '')
		{
			// Экранируем ключи
			$aKeys = array_keys($aValues);
			$aKeys = array_map(array($oDataBase, 'quoteColumnName'), $aKeys);

			$query = 'INSERT INTO ' . $oDataBase->quoteTableName($tableName) . '(' . implode(', ', $aKeys) . ') VALUES (' . implode(', ', $aValues) . ')';

			$oDataBase->setQueryType(1)->query($query);

			$new_pk = $oDataBase->getInsertId();
		}
		else
		{
			$aQueryValues = array();
			foreach ($aValues as $key => $value)
			{
				$aQueryValues[] = ' ' . $oDataBase->quoteColumnName($key) . ' = ' . $value;
			}

			$query = 'UPDATE ' . $oDataBase->quoteTableName($tableName) . ' SET' . implode(', ', $aQueryValues) . ' WHERE ' . $oDataBase->quoteColumnName($primaryKeyName) . ' = ' . $oDataBase->quote(Core_Array::get($this->_formValues, 'source_pk'));

			$oDataBase->setQueryType(2)->query($query);

			$new_pk = $this->_formValues[$primaryKeyName];
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();
		?><script><?php
		?>$.appendInput('<?php echo Core_Str::escapeJavascriptVariable($windowId)?>', '<?php echo Core_Str::escapeJavascriptVariable($primaryKeyName)?>', '<?php echo Core_Str::escapeJavascriptVariable($new_pk)?>');
		$.appendInput('<?php echo Core_Str::escapeJavascriptVariable($windowId)?>', 'source_pk', '<?php echo Core_Str::escapeJavascriptVariable($new_pk)?>');<?php
		?></script><?php

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}