<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sql_Table_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Sql_Table_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
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

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab
			->move($this->getField('name')->class('form-control input-lg'), $oMainRow1);

		$oDataBase = Core_DataBase::instance();

		// Engines
		$aOptions = array();

		$aEngines = $oDataBase->getEngines();
		foreach ($aEngines as $engine)
		{
			$aOptions[$engine] = $engine;
		}

		$oSelect_Engines = Admin_Form_Entity::factory('Select')
			->options($aOptions)
			->name('engine')
			->value($this->_object->engine)
			->caption(Core::_('Sql_Table.engine'))
			->divAttr(array('class' => 'form-group col-xs-12'));

		$oMainTab->delete($this->getField('engine'));

		$oMainRow2->add($oSelect_Engines);

		// Collations
		$aOptions = array('' => '');

		$aCollations = $oDataBase->getCollations();
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

		if ($this->_object->getPrimaryKey() == '')
		{
			$aConfig = $oDataBase->getConfig();
			$query = 'SELECT DEFAULT_COLLATION_NAME FROM `INFORMATION_SCHEMA`.`SCHEMATA` WHERE SCHEMA_NAME = ' . $oDataBase->quote($aConfig['database']) . ' LIMIT 1';

			$result = $oDataBase->query($query)->asAssoc()->result();

			// Database collation
			$collation = isset($result[0]['DEFAULT_COLLATION_NAME'])
				? $result[0]['DEFAULT_COLLATION_NAME']
				: '';
		}
		else
		{
			$collation = $this->_object->collation;
		}

		$oSelect_Collations = Admin_Form_Entity::factory('Select')
			->options($aOptions)
			->name('collation')
			->value($collation)
			->caption(Core::_('Sql_Table.collation'))
			->divAttr(array('class' => 'form-group col-xs-12'));

		$oMainTab->delete($this->getField('collation'));

		$oMainRow3->add($oSelect_Collations);

		if ($this->_object->getPrimaryKey() != '')
		{
			$oMainTab
				->move($this->getField('auto_increment')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4);
		}
		else
		{
			$oMainTab->delete($this->getField('auto_increment'));
		}

		$row_format = $this->_object->getPrimaryKey() == ''
			? 'DYNAMIC'
			: strtoupper($this->_object->row_format);

		$oSelect_RowFormats = Admin_Form_Entity::factory('Select')
			->options(array(
				'FIXED' => 'FIXED',
				'DYNAMIC' => 'DYNAMIC',
				'REDUNDANT' => 'REDUNDANT',
				'COMPACT' => 'COMPACT',
				'COMPRESSED' => 'COMPRESSED'
			))
			->name('row_format')
			->value($row_format)
			->caption(Core::_('Sql_Table.row_format'))
			->divAttr(array('class' => 'form-group col-xs-12'));

		$oMainRow5->add($oSelect_RowFormats);

		$this->title($this->_object->name != ''
			? Core::_('Sql_Table.edit_title', $this->_object->name, FALSE)
			: Core::_('Sql_Table.add_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Sql_Table_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		if (isset($this->_formValues['name']) && $this->_formValues['name'] !== '')
		{
			$oldTableName = $this->_object->name;

			$bAdd = $oldTableName === '';

			$oDataBase = Core_DataBase::instance();

			$sEngine = isset($this->_formValues['engine']) && $this->_formValues['engine'] !== ''
				? strval($this->_formValues['engine'])
				: 'MyISAM';

			$sCollation = isset($this->_formValues['collation']) && $this->_formValues['collation'] !== ''
				? strval($this->_formValues['collation'])
				: 'utf8_general_ci';

			$sRowFormat = isset($this->_formValues['row_format']) && $this->_formValues['row_format'] !== ''
				? strval($this->_formValues['row_format'])
				: 'DYNAMIC';

			$iAutoincrement = isset($this->_formValues['auto_increment']) && $this->_formValues['auto_increment'] !== ''
				? intval($this->_formValues['auto_increment'])
				: 1;

			$table_name = $this->_formValues['name'];

			if ($bAdd)
			{
				// create
				$query = "CREATE TABLE IF NOT EXISTS " . $oDataBase->quoteTableName($table_name) . " (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					PRIMARY KEY (`id`)
				) ENGINE={$sEngine} DEFAULT CHARSET=utf8 COLLATE={$sCollation} ROW_FORMAT={$sRowFormat};
				";
				$oDataBase->query($query);
			}
			else
			{
				if ($this->_formValues['name'] !== $oldTableName)
				{
					$query = 'RENAME TABLE ' . $oDataBase->quoteTableName($oldTableName) . ' TO ' . $oDataBase->quoteTableName($table_name) . ';';
					$oDataBase->query($query);
				}

				$query = 'ALTER TABLE ' . $oDataBase->quoteTableName($table_name);

				// Engine
				if ($sEngine != '')
				{
					$query .= ' ENGINE=' . $sEngine;
				}

				// Collation
				if ($sCollation != '')
				{
					$collation = preg_replace('/[^a-z0-9_]/', '', $sCollation);

					$aCollation = explode('_', $collation);

					$query .= ' CHARACTER SET ' . $aCollation[0] . ' COLLATE ' . $collation;
				}

				// Row format
				if ($sRowFormat != '')
				{
					$query .= ' ROW_FORMAT=' . $sRowFormat;
				}

				// Row format
				if ($iAutoincrement)
				{
					$query .= ' AUTO_INCREMENT=' . $iAutoincrement;
				}

				$oDataBase->query($query);
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}