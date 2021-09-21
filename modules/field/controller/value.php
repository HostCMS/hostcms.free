<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Fields.
 *
 * @package HostCMS
 * @subpackage Field
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Controller_Value
{
	/**
	 * Create object of $type class
	 * @param string $type type of the class
	 * @return mixed
	 */
	static public function factory($type)
	{
		if (!is_numeric($type))
		{
			throw new Core_Exception('Unknown Field_Controller_Value type "%type"', array('%type' => $type));
		}
		$fieldValueName = __CLASS__ . '_type' . ucfirst($type);
		return new $fieldValueName();
	}

	static protected $_cacheGetField = array();

	/**
	 * Get Field by ID
	 * @param int $iFieldId
	 * @return object|NULL
	 */
	static protected function _getField($iFieldId)
	{
		if (!isset(self::$_cacheGetField[$iFieldId]))
		{
			self::$_cacheGetField[$iFieldId] = Core_Entity::factory('Field')->getById($iFieldId);
		}

		return self::$_cacheGetField[$iFieldId];
	}

	/**
	 * Получение значений свойств $aFieldsId объекта $entityId
	 * @param array $aFieldsId fields ID
	 * @param int $entityId entity ID
	 * @param boolean $bCache cache mode
	 * @return array
	 */
	static public function getFieldsValues($aFieldsId, $entityId, $bCache = TRUE)
	{
		$aReturn = array();

		if (count($aFieldsId) > 0)
		{
			$aSelect = array();
			foreach ($aFieldsId as $iFieldId)
			{
				$oField = self::_getField($iFieldId);
				if (!is_null($oField))
				{
					$oFieldValue = self::factory($oField->type);
					$aSelect[$oFieldValue->getModelName()][] = $iFieldId;
				}
			}

			foreach ($aSelect as $sModelName => $aTmpFieldsId)
			{
				$oField_Values = Core_Entity::factory($sModelName);

				$oField_Values
					->queryBuilder()
					->where('entity_id', '=', $entityId)
					->where('field_id', 'IN', $aTmpFieldsId);

				$aReturn = array_merge($aReturn, $oField_Values->findAll($bCache));
			}
		}

		return $aReturn;
	}
}
