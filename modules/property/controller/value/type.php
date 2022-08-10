<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Property_Controller_Value_Type
{
	/**
	 * Property
	 * @var Property_Model
	 */
	protected $_property = NULL;

	/**
	 * Set property
	 * @param Property_Model $oProperty property
	 * @return self
	 */
	public function setProperty(Property_Model $oProperty)
	{
		$this->_property = $oProperty;
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
	 * Get Property_Value as object
	 * @return object
	 */
	public function getPropertyValueObject()
	{
		$pluralName = Core_Inflection::getPlural($this->_modelName);
		return $this->_property->$pluralName;
	}

	/**
	 * Get all values for entity
	 * @param int $entityId entity ID
	 * @param boolean $bCache cache mode
	 * @return array
	 */
	public function getValues($entityId, $bCache = TRUE, $bSorting = FALSE)
	{
		$oProperty_Values = $this->getPropertyValueObject();

		$oProperty_Values
			->queryBuilder()
			->where('entity_id', '=', $entityId);

		$bSorting && $oProperty_Values
			->queryBuilder()
			->clearOrderBy()
			->orderBy('sorting', 'ASC');

		return $oProperty_Values->findAll($bCache);
	}

	/**
	 * Create new value for property
	 * @param int $entityId entity ID
	 * @return Property_Value
	 */
	public function createNewValue($entityId)
	{
		$oProperty_Value = Core_Entity::factory($this->_modelName);

		$oProperty_Value->property_id = $this->_property->id;
		$oProperty_Value->entity_id = $entityId;

		return $oProperty_Value;
	}

	/**
	 * Get Property_Value by ID
	 * @param string $valueId value ID
	 * @return mixed
	 */
	public function getValueById($valueId)
	{
		$pluralName = Core_Inflection::getPlural($this->_modelName);

		$oProperty_Values = $this->_property->$pluralName;

		$oProperty_Values
			->queryBuilder()
			->where('id', '=', $valueId)
			->limit(1);

		$aProperty_Values = $oProperty_Values->findAll();

		return isset($aProperty_Values[0])
			? $aProperty_Values[0]
			: NULL;
	}

	/**
	 * Get Property_Value by value
	 * @param string $value value
	 * @param string $condition condition
	 * @param boolean $bCache use cache
	 * @return array
	 */
	public function getValuesByValue($value, $condition = '=', $bCache = TRUE)
	{
		$pluralName = Core_Inflection::getPlural($this->_modelName);

		$oProperty_Values = $this->_property->$pluralName;

		$oProperty_Values
			->queryBuilder()
			->where('value', $condition, $value);

		return $oProperty_Values->findAll($bCache);
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