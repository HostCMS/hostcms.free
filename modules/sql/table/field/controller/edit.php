<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sql_Table_Field_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Sql_Table_Field_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$aType = $object->Type != ''
			? Core_DataBase::instance()->getColumnType($object->Type)
			: array(
				'datatype' => '',
				'defined_max_length' => '',
				'max_length' => '',
				'binary' => FALSE,
				'unsigned' => FALSE,
				'zerofill' => FALSE
			);

		// double unsigned => double
		list($aType['datatype']) = explode(' ', $aType['datatype']);

		$oAdditionalTab
			->move($this->getField('Field')->class('form-control input-lg'), $oMainRow1);

		$oMainTab->delete($this->getField('Type'));

		$oSelect_type_name = Admin_Form_Entity::factory('Select')
			->options(
				array(
					'TINYINT' => 'TINYINT',
					'SMALLINT' => 'SMALLINT',
					'MEDIUMINT' => 'MEDIUMINT',
					'INT' => 'INT',
					'BIGINT' => 'BIGINT',
					'DECIMAL' => 'DECIMAL',
					'FLOAT' => 'FLOAT',
					'DOUBLE' => 'DOUBLE',
					'REAL' => 'REAL',
					'BIT' => 'BIT',
					'BOOLEAN' => 'BOOLEAN',
					'SERIAL' => 'SERIAL',
					'DATE' => 'DATE',
					'DATETIME' => 'DATETIME',
					'TIMESTAMP' => 'TIMESTAMP',
					'TIME' => 'TIME',
					'YEAR' => 'YEAR',
					'CHAR' => 'CHAR',
					'VARCHAR' => 'VARCHAR',
					'TINYTEXT' => 'TINYTEXT',
					'TEXT' => 'TEXT',
					'MEDIUMTEXT' => 'MEDIUMTEXT',
					'LONGTEXT' => 'LONGTEXT',
					'BINARY' => 'BINARY',
					'VARBINARY' => 'VARBINARY',
					'TINYBLOB' => 'TINYBLOB',
					'BLOB' => 'BLOB',
					'MEDIUMBLOB' => 'MEDIUMBLOB',
					'LONGBLOB' => 'LONGBLOB',
					'ENUM' => 'ENUM',
					'SET' => 'SET',
					'GEOMETRY' => 'GEOMETRY',
					'POINT' => 'POINT',
					'LINESTRING' => 'LINESTRING',
					'POLYGON' => 'POLYGON',
					'MULTIPOINT' => 'MULTIPOINT',
					'MULTILINESTRING' => 'MULTILINESTRING',
					'MULTIPOLYGON' => 'MULTIPOLYGON',
					'GEOMETRYCOLLECTION' => 'GEOMETRYCOLLECTION',
					'JSON' => 'JSON'
				)
			)
			->name('type_name')
			->value(strtoupper($aType['datatype']))
			->caption(Core::_('sql_table_field.type_name'))
			->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

		$oMainRow2
			->add($oSelect_type_name)
			->add(
				Admin_Form_Entity::factory('Input')
					->name('type_length')
					->value($aType['defined_max_length'])
					->caption(Core::_('sql_table_field.type_length'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
			);

		// Collations
		$aCollations = Core_DataBase::instance()->getCollations();

		//echo "<pre>"; echo $object->Type; print_r($aType); echo "</pre>";
		//echo "<pre>"; var_dump($this->_object); echo "</pre>";
		//echo "<pre>"; print_r($aCollations); echo "</pre>";

		$aOptions = array('' => '');

		foreach ($aCollations as $charset => $aTmpCollations)
		{
			$aOptions[$charset] = array(
				'value' => $charset,
				'attr' => array('disabled' => 'disabled', 'class' => 'semi-bold black')
			);

			foreach ($aTmpCollations as $collationName)
			{
				$aOptions[$collationName] = $collationName;
			}
		}

		$oSelect_Collations = Admin_Form_Entity::factory('Select')
			->options($aOptions)
			->name('Collation')
			->value($this->_object->Collation)
			->caption(Core::_('sql_table_field.Collation'))
			->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

		$oMainTab->delete($this->getField('Collation'));

		$oMainRow3->add($oSelect_Collations);

		// Attrs
		if ($aType['binary'])
		{
			$attrValue = 'BINARY';
		}
		elseif ($aType['unsigned'])
		{
			$attrValue = 'UNSIGNED';
			$aType['zerofill'] && $attrValue .= ' ZEROFILL';
		}
		elseif ($this->_object->Extra == 'on update CURRENT_TIMESTAMP')
		{
			$attrValue = 'on update CURRENT_TIMESTAMP';
		}
		else
		{
			$attrValue = 'NONE';
		}

		$oSelect_Attrs = Admin_Form_Entity::factory('Select')
			->options(array(
				'NONE' => '',
				'BINARY' => 'BINARY',
				'UNSIGNED' => 'UNSIGNED',
				'UNSIGNED ZEROFILL' => 'UNSIGNED ZEROFILL',
				'on update CURRENT_TIMESTAMP' => 'on update CURRENT_TIMESTAMP',
			))
			->name('Attr')
			->value($attrValue)
			->caption(Core::_('sql_table_field.Attr'))
			->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

		$oMainRow3->add($oSelect_Attrs);

		// Default
		if ($this->_object->Null == 'YES' && is_null($this->_object->Default))
		{
			$defaultValue = 'NULL';
		}
		elseif ($this->_object->Default == 'CURRENT_TIMESTAMP')
		{
			$defaultValue = 'CURRENT_TIMESTAMP';
		}
		elseif ($this->_object->Default != '')
		{
			$defaultValue = 'USER_DEFINED';
		}
		else
		{
			$defaultValue = 'NONE';
		}

		$oSelect_Default = Admin_Form_Entity::factory('Select')
			->options(array(
				'NONE' => '',
				'USER_DEFINED' => Core::_('sql_table_field.user_defined'),
				'NULL' => 'NULL',
				'CURRENT_TIMESTAMP' => 'CURRENT_TIMESTAMP',
			))
			->name('Default_type')
			->value($defaultValue)
			->caption(Core::_('sql_table_field.Default_type'))
			->divAttr(array('class' => 'form-group col-xs-12 col-md-6'))
			->onchange("radiogroupOnChange('{$windowId}', $(this).val(), ['NONE','USER_DEFINED','NULL','CURRENT_TIMESTAMP'])");

		$oMainRow4->add($oSelect_Default);

		$oMainTab
			->move($this->getField('Default')->divAttr(array('class' => 'form-group col-xs-12 col-md-6 hidden-NONE hidden-NULL hidden-CURRENT_TIMESTAMP')), $oMainRow4);

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
		$oAdmin_Form_Entity_Code->html(
			"<script>radiogroupOnChange('{$windowId}', '" . $defaultValue . "', ['NONE','USER_DEFINED','NULL','CURRENT_TIMESTAMP'])</script>"
		);

		$oMainTab->add($oAdmin_Form_Entity_Code);

		// NULL
		$oMainTab
			->move($this->getField('Null')->checked($this->_object->Null == 'YES'), $oMainRow5);

		// Autoincrement
		$oMainRow6->add(
			Admin_Form_Entity::factory('Checkbox')
				->name('Autoincrement')
				->value(1)
				->checked(strtolower($this->_object->Extra) == 'auto_increment')
				->caption(Core::_('sql_table_field.Autoincrement'))
				->divAttr(array('class' => 'form-group col-xs-12'))
		);

		// Comment
		$oMainTab
			->move($this->getField('Comment'), $oMainRow7);

		$oMainTab
			->delete($this->getField('Key'))
			->delete($this->getField('Extra'))
			->delete($this->getField('Privileges'))
			->add(
				// Оригинальное имя поля
				Admin_Form_Entity::factory('Input')
					->name('id')
					->value($this->_object->Field)
					->divAttr(array('class' => 'hidden'))
			)
			->add(
				// Таблица
				Admin_Form_Entity::factory('Input')
					->name('table_name')
					->value($this->_object->getTableName())
					->divAttr(array('class' => 'hidden'))
			);

		$this->title($this->_object->Field != ''
			? Core::_('Sql_Table_Field.edit_title', $this->_object->Field, FALSE)
			: Core::_('Sql_Table_Field.add_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Sql_Table_Field_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		if (isset($this->_formValues['id']))
		{
			$table_name = $this->_formValues['table_name'];

			$oDataBase = Core_DataBase::instance();

			$query = 'ALTER TABLE ' . $oDataBase->quoteTableName($table_name);

			if ($this->_formValues['id'] != '')
			{
				$query .= ' CHANGE ' . $oDataBase->quoteColumnName($this->_formValues['id']);
			}
			else
			{
				$query .= ' ADD';
			}

			$aType = Core_DataBase::instance()->getColumnType($this->_formValues['type_name']);
			// double unsigned => double
			list($aType['datatype']) = explode(' ', $aType['datatype']);

			//print_r($aType);
			$sField = $oDataBase->quoteColumnName($this->_formValues['Field']);

			$query .= ' ' . $sField;

			$query .= ' ' . preg_replace('/[^A-Z]/', '', Core_Array::get($this->_formValues, 'type_name'));

			$type_length = preg_replace('/[^0-9,]/', '', Core_Array::get($this->_formValues, 'type_length'));

			// Required length for some types
			if ($type_length == '' && in_array($aType['datatype'], array('char', 'varchar', 'binary', 'varbinary')))
			{
				$type_length = $aType['max_length'];
			}

			if ($type_length != ''
				&& $aType['type'] != 'bool'
				&& !in_array($aType['datatype'], array('double', 'real', 'serial', 'date', 'datetime', 'year', 'tinyblob', 'blob', 'mediumblob', 'longblob', 'enum', 'set', 'point', 'multipoint', 'multilinestring', 'multipolygon', 'ge3ometrycollection', 'json'))
			)
			{
				$query .= '(' . $type_length . ')';
			}

			// Attr
			switch ($this->_formValues['Attr'])
			{
				case 'BINARY':
					if ($aType['type'] == 'string')
					{
						$query .= ' BINARY';
					}
				break;
				case 'UNSIGNED':
				case 'UNSIGNED ZEROFILL':
					if ($aType['type'] == 'int' || $aType['type'] == 'float')
					{
						$query .= ' ' . $this->_formValues['Attr'];
					}
				break;
				case 'on update CURRENT_TIMESTAMP':
					if ($aType['datatype'] == 'timestamp')
					{
						$query .= ' ' . $this->_formValues['Attr'];
					}
				break;
			}

			// Collation
			if ($this->_formValues['Collation'] != '')
			{
				if ($aType['type'] == 'string')
				{
					$collation = preg_replace('/[^a-z0-9_]/', '', $this->_formValues['Collation']);

					$aCollation = explode('_', $collation);

					$query .= ' CHARACTER SET ' . $aCollation[0] . ' COLLATE ' . $collation;
				}
			}

			$null = Core_Array::get($this->_formValues, 'Null');

			$query .= $null ? ' NULL' : ' NOT NULL';

			switch ($this->_formValues['Default_type'])
			{
				case 'USER_DEFINED':
					$query .= ' DEFAULT ' . $oDataBase->escape($this->_formValues['Default']);
				break;
				case 'NULL':
					$null
						&& $query .= ' DEFAULT NULL';
				break;
				case 'CURRENT_TIMESTAMP':
					$query .= ' DEFAULT CURRENT_TIMESTAMP';
				break;
			}

			$bAutoincrement = Core_Array::get($this->_formValues, 'Autoincrement');
			if ($bAutoincrement)
			{
				$query .= ' AUTO_INCREMENT';
			}

			if (trim($this->_formValues['Comment']) != '')
			{
				$query .= ' COMMENT ' . $oDataBase->escape($this->_formValues['Comment']);
			}

			if ($bAutoincrement)
			{
				$aIndexes = $oDataBase->asAssoc()->getIndexes($table_name, 'PRIMARY');

				if (!count($aIndexes))
				{
					$query .= ', add PRIMARY KEY (' . $sField . ')';
				}
			}

			$oDataBase->query($query);

			$windowId = $this->_Admin_Form_Controller->getWindowId();
			?><script><?php
			?>$.appendInput('<?php echo Core_Str::escapeJavascriptVariable($windowId)?>', 'id', '<?php echo Core_Str::escapeJavascriptVariable($this->_formValues['Field'])?>');<?php
			?></script><?php
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}