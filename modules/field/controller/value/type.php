<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Fields.
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
abstract class Field_Controller_Value_Type
{
	/**
	 * Field
	 * @var Field_Model
	 */
	protected $_field = NULL;

	/**
	 * Set field
	 * @param Field_Model $oField field
	 * @return self
	 */
	public function setField(Field_Model $oField)
	{
		$this->_field = $oField;
		return $this;
	}

	/**
	 * Get model name
	 * @return string
	 */
	public function getModelName()
	{
		return $this->_modelName;
	}

	/**
	 * Get table name
	 * @return string
	 */
	public function getTableName()
	{
		return $this->_tableName;
	}

	/**
	 * Get Field_Value as object
	 * @return object
	 */
	public function getFieldValueObject()
	{
		$pluralName = Core_Inflection::getPlural($this->_modelName);
		return $this->_field->$pluralName;
	}

	/**
	 * Get all values for entity
	 * @param int $entityId entity ID
	 * @param boolean $bCache cache mode
	 * @return array
	 */
	public function getValues($entityId, $bCache = TRUE)
	{
		$oField_Values = $this->getFieldValueObject();

		$oField_Values
			->queryBuilder()
			->where('entity_id', '=', $entityId);

		return $oField_Values->findAll($bCache);
	}

	/**
	 * Create new value for field
	 * @param int $entityId entity ID
	 * @return Field_Value
	 */
	public function createNewValue($entityId)
	{
		$oField_Value = Core_Entity::factory($this->_modelName);

		$oField_Value->field_id = $this->_field->id;
		$oField_Value->entity_id = $entityId;

		return $oField_Value;
	}

	/**
	 * Get Field_Value by ID
	 * @param string $valueId value ID
	 * @return mixed
	 */
	public function getValueById($valueId)
	{
		$pluralName = Core_Inflection::getPlural($this->_modelName);

		$oField_Values = $this->_field->$pluralName;

		$oField_Values
			->queryBuilder()
			->where('id', '=', $valueId)
			->limit(1);

		$aField_Values = $oField_Values->findAll();

		return isset($aField_Values[0])
			? $aField_Values[0]
			: NULL;
	}

	/**
	 * Get Field_Value by value
	 * @param string $value value
	 * @param string $condition condition
	 * @param boolean $bCache use cache
	 * @return array
	 */
	public function getValuesByValue($value, $condition = '=', $bCache = TRUE)
	{
		$pluralName = Core_Inflection::getPlural($this->_modelName);

		$oField_Values = $this->_field->$pluralName;

		$oField_Values
			->queryBuilder()
			->where('value', $condition, $value);

		return $oField_Values->findAll($bCache);
	}

	/**
	 * Convert object to string
	 * @return string
	 */
	public function __toString()
	{
		return get_class($this) . '; Model: ' . $this->_modelName . '; Table: ' . $this->_tableName;
	}
}