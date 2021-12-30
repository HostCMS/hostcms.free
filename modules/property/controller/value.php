<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Property_Controller_Value
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
			throw new Core_Exception('Unknown Property_Controller_Value type "%type"', array('%type' => $type));
		}
		$propertyValueName = __CLASS__ . '_type' . ucfirst($type);
		return new $propertyValueName();
	}

	static protected $_cacheGetProperty = array();

	/**
	 * Get Property by ID
	 * @param int $iPropertyId
	 * @return object|NULL
	 */
	static protected function _getProperty($iPropertyId)
	{
		if (!isset(self::$_cacheGetProperty[$iPropertyId]))
		{
			self::$_cacheGetProperty[$iPropertyId] = Core_Entity::factory('Property')->getById($iPropertyId);
		}

		return self::$_cacheGetProperty[$iPropertyId];
	}

	/**
	 * Получение значений свойств $aProperiesId объекта $entityId
	 * @param array $aProperiesId properties ID
	 * @param int $entityId entity ID
	 * @param boolean $bCache cache mode
	 * @return array
	 */
	static public function getPropertiesValues($aProperiesId, $entityId, $bCache = TRUE, $bSorting = FALSE)
	{
		$aReturn = array();

		if (count($aProperiesId) > 0)
		{
			$aSelect = array();
			foreach ($aProperiesId as $iPropertyId)
			{
				$oProperty = self::_getProperty($iPropertyId);
				if (!is_null($oProperty))
				{
					$oPropertyValue = self::factory($oProperty->type);
					$aSelect[$oPropertyValue->getModelName()][] = $iPropertyId;
				}
			}

			// Вариант на UNION
			/*foreach ($aSelect as $sModelName => $aTmpProperiesId)
			{
				$oProperty_Values = Core_Entity::factory($sModelName);

				$iFirstProperyId = array_shift($aTmpProperiesId);

				$oProperty_Values
					->queryBuilder()
					->where('property_id', '=', $iFirstProperyId)
					->where('entity_id', '=', $entityId);

				foreach ($aTmpProperiesId as $iTmpProperyId)
				{
					$queryBuilder = Core_QueryBuilder::select()
						->from(Core_Inflection::getPlural($sModelName))
						->where('property_id', '=', $iTmpProperyId)
						->where('entity_id', '=', $entityId);

					$oProperty_Values
						->queryBuilder()
						->union($queryBuilder);
				}

				$aReturn = array_merge($aReturn, $oProperty_Values->findAll());
			}*/

			foreach ($aSelect as $sModelName => $aTmpProperiesId)
			{
				$oProperty_Values = Core_Entity::factory($sModelName);

				$oProperty_Values
					->queryBuilder()
					->where('entity_id', '=', $entityId)
					->where('property_id', 'IN', $aTmpProperiesId);

				$bSorting && $oProperty_Values
					->queryBuilder()
					->clearOrderBy()
					->orderBy('sorting', 'ASC');

				$aReturn = array_merge($aReturn, $oProperty_Values->findAll($bCache));
			}
		}

		return $aReturn;
	}
}
