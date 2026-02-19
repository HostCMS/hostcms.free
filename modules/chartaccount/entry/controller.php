<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Entry_Controller.
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Chartaccount_Entry_Controller
{
	/**
	 * Last error
	 * @var mixed
	 */
	static protected $_lastError = NULL;

	static public function deleteEntriesByDocumentId($documentId)
	{
		$aChartaccount_Entries = Core_Entity::factory('Chartaccount_Entry')->getAllByDocument_id($documentId);

		foreach ($aChartaccount_Entries as $oChartaccount_Entry)
		{
			$oChartaccount_Entry->delete();
		}
	}

	/**
	 * Get entries
	 * @param array $options
	 * @return object
	 */
	static protected function _getEntries(array $options)
	{
		$oChartaccount_Entries = Core_Entity::factory('Chartaccount_Entry');

		if (count($options))
		{
			$bJoined = FALSE;
			foreach ($options as $fieldName => $fieldValue)
			{
				if ($fieldName == 'date_from')
				{
					$oChartaccount_Entries->queryBuilder()->where('chartaccount_entries.datetime', '>=', $fieldValue . ' 00:00:00');
					$fieldName = NULL;
				}

				if ($fieldName == 'date_to')
				{
					//var_dump($fieldValue);
					$oChartaccount_Entries->queryBuilder()->where('chartaccount_entries.datetime', '<=', $fieldValue . ' 23:59:59');
					$fieldName = NULL;
				}

				if ($fieldName == 'dchartaccount')
				{
					$oChartaccount = Core_Entity::factory('Chartaccount')->getByCode($fieldValue);

					if (!is_null($oChartaccount))
					{
						$fieldName = 'dchartaccount_id';
						$fieldValue = $oChartaccount->id;
					}
					else
					{
						//throw new Core_Exception("Chartaccount_Entry_Controller: Debit Chartaccount_Model is NULL");
						self::$_lastError = "Chartaccount_Entry_Controller: Debit '{$fieldValue}' Chartaccount_Model is NULL";
						return NULL;
					}
				}

				if ($fieldName == 'cchartaccount')
				{
					$oChartaccount = Core_Entity::factory('Chartaccount')->getByCode($fieldValue);

					if (!is_null($oChartaccount))
					{
						$fieldName = 'cchartaccount_id';
						$fieldValue = $oChartaccount->id;
					}
					else
					{
						//throw new Core_Exception("Chartaccount_Entry_Controller: Credit Chartaccount_Model is NULL");
						self::$_lastError = "Chartaccount_Entry_Controller: Credit '{$fieldValue}' Chartaccount_Model is NULL";
						return NULL;
					}
				}

				if ($fieldName == 'subcount' && is_array($fieldValue) && count($fieldValue))
				{
					!$bJoined
						&& $oChartaccount_Entries->queryBuilder()
							->join('chartaccount_entry_subcounts', 'chartaccount_entries.id', '=', 'chartaccount_entry_subcounts.chartaccount_entry_id')
							->groupBy('chartaccount_entries.id');

					count($fieldValue) > 1 && $oChartaccount_Entries->queryBuilder()->open();

					foreach ($fieldValue as $scType => $scValue)
					{
						$oChartaccount_Entries->queryBuilder()
							->where('chartaccount_entry_subcounts.type', '=', $scType)
							->where('chartaccount_entry_subcounts.value', '=', $scValue);

						count($fieldValue) > 1 && $oChartaccount_Entries->queryBuilder()->setOr();
					}
					count($fieldValue) > 1 && $oChartaccount_Entries->queryBuilder()->close();

					$fieldName = NULL;
				}

				if ($fieldName == 'debit_sc' && is_array($fieldValue))
				{
					if (isset($options['dchartaccount_id']))
					{
						$oChartaccount = Core_Entity::factory('Chartaccount')->getById($options['dchartaccount_id']);
					}
					elseif (isset($options['dchartaccount']))
					{
						$oChartaccount = Core_Entity::factory('Chartaccount')->getByCode($options['dchartaccount']);
					}
					else
					{
						$oChartaccount = NULL;
					}

					if ($oChartaccount)
					{
						$fieldName = NULL;

						for ($i = 0; $i < 3; $i++)
						{
							$subcountFieldName = 'sc' . $i;

							$value = Core_Array::get($fieldValue, $oChartaccount->$subcountFieldName);

							!is_null($value)
								&& $oChartaccount_Entries->queryBuilder()->where('chartaccount_entries.d' . $subcountFieldName, '=', $value);
						}
					}
					else
					{
						self::$_lastError = "Chartaccount_Entry_Controller: debit_sc Chartaccount_Model is NULL";
						return NULL;
						//throw new Core_Exception();
					}
				}

				if ($fieldName == 'credit_sc' && is_array($fieldValue))
				{
					if (isset($options['cchartaccount_id']))
					{
						$oChartaccount = Core_Entity::factory('Chartaccount')->getById($options['cchartaccount_id']);
					}
					elseif (isset($options['cchartaccount']))
					{
						$oChartaccount = Core_Entity::factory('Chartaccount')->getByCode($options['cchartaccount']);
					}
					else
					{
						$oChartaccount = NULL;
					}

					if ($oChartaccount)
					{
						$fieldName = NULL;

						for ($i = 0; $i < 3; $i++)
						{
							$subcountFieldName = 'sc' . $i;

							$value = Core_Array::get($fieldValue, $oChartaccount->$subcountFieldName);

							!is_null($value)
								&& $oChartaccount_Entries->queryBuilder()->where('chartaccount_entries.c' . $subcountFieldName, '=', $value);
						}
					}
					else
					{
						//throw new Core_Exception("Chartaccount_Entry_Controller: credit_sc Chartaccount_Model is NULL");
						self::$_lastError = "Chartaccount_Entry_Controller: credit_sc Chartaccount_Model is NULL";
						return NULL;
					}
				}

				!is_null($fieldName)
					&& $oChartaccount_Entries->queryBuilder()->where('chartaccount_entries.' . $fieldName, '=', $fieldValue);
			}
		}

		return $oChartaccount_Entries;
	}

	/**
	 * Get entries
	 * @param array $options
	 * @return array
	 */
	static public function getEntries(array $options)
	{
		if (count($options))
		{
			$oChartaccount_Entries = self::_getEntries($options);

			if ($oChartaccount_Entries)
			{
				return $oChartaccount_Entries->findAll(FALSE);
			}
			elseif (!is_null(self::$_lastError))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$MESSAGE)
					->write(self::$_lastError);

				self::$_lastError = NULL;
			}
		}

		return array();
	}

	/**
	 * Get entries amount
	 * @param array $options
	 * @return int|float
	 */
	static public function getEntriesAmount(array $options)
	{
		// $amount = 0;

		if (Core::moduleIsActive('chartaccount') && count($options))
		{
			// var_dump($options);
			$oChartaccount_Entries = self::_getEntries($options);

			if ($oChartaccount_Entries)
			{
				$oChartaccount_Entries->queryBuilder()
					->clearSelect()
					->select(array('SUM(amount)', 'dataTotalAmount'));

				$aReturn = $oChartaccount_Entries->findAll(FALSE);

// echo htmlspecialchars(Core_DataBase::instance()->getLastQuery());

				return isset($aReturn[0])
					? $aReturn[0]->dataTotalAmount
					: 0;
			}
			elseif (!is_null(self::$_lastError))
			{
				Core_Log::instance()->clear()
					->status(Core_Log::$MESSAGE)
					->write(self::$_lastError);

				self::$_lastError = NULL;
			}
		}

		return 0;
	}

	/**
	 * Insert entries
	 * @param int $document_id
	 * @param int $company_id
	 * @param array $aEntries
	 */
	static public function insertEntries($document_id, $company_id, $aEntries)
	{
		$aIDs = array();

		foreach ($aEntries as $aEntry)
		{
			$oChartaccount_Entry = self::insertEntry($document_id, $company_id, $aEntry);
			$aIDs[] = $oChartaccount_Entry->id;
		}

		$oChartaccount_Entry = Core_Entity::factory('Chartaccount_Entry');
		$oChartaccount_Entry->queryBuilder()
			->where('chartaccount_entries.document_id', '=', $document_id)
			->where('chartaccount_entries.id', 'NOT IN', $aIDs);

		$aChartaccount_Entries = $oChartaccount_Entry->findAll(FALSE);
		foreach ($aChartaccount_Entries as $oChartaccount_Entry)
		{
			$oChartaccount_Entry->delete();
		}
	}

	static protected $_alreadyAffectedEntries = array();

	/**
	 * Insert entry
	 * @param int $document_id
	 * @param int $company_id
	 * @param array $aEntry
	 * @return object
	 */
	static public function insertEntry($document_id, $company_id, $aEntry)
	{
		$oDChartaccount = Core_Entity::factory('Chartaccount')->getByCode($aEntry['debit']);
		$oCChartaccount = Core_Entity::factory('Chartaccount')->getByCode($aEntry['credit']);

		$aChartaccount_Entries = self::getEntries(
			array(
				'document_id' => $document_id,
				'dchartaccount_id' => $oDChartaccount->id,
				'cchartaccount_id' => $oCChartaccount->id
			)
		);

		$oChartaccount_Entry = NULL;

		// Find not affected
		foreach ($aChartaccount_Entries as $oTmpChartaccount_Entry)
		{
			if (!in_array($oTmpChartaccount_Entry->id, self::$_alreadyAffectedEntries))
			{
				$oChartaccount_Entry = $oTmpChartaccount_Entry;
				self::$_alreadyAffectedEntries[] = $oTmpChartaccount_Entry->id;
			}
		}

		if (is_null($oChartaccount_Entry))
		{
			$oChartaccount_Entry = Core_Entity::factory('Chartaccount_Entry');
			$oChartaccount_Entry->document_id = $document_id;
			$oChartaccount_Entry->dchartaccount_id = $oDChartaccount->id;
			$oChartaccount_Entry->cchartaccount_id = $oCChartaccount->id;
		}

		if (isset($aEntry['amount']))
		{
			$oChartaccount_Entry->amount = $aEntry['amount'];
		}

		if (isset($aEntry['description']))
		{
			$oChartaccount_Entry->description = $aEntry['description'];
		}

		if (isset($aEntry['datetime']))
		{
			$oChartaccount_Entry->datetime = $aEntry['datetime'];
		}

		$oChartaccount_Entry->company_id = $company_id;

		// Субконто, связанные с проводкой
		$aSubcounts = array();

		for ($i = 0; $i < 3; $i++)
		{
			$fieldName = 'sc' . $i;
			$dFieldName = 'd' . $fieldName;
			$cFieldName = 'c' . $fieldName;

			$oChartaccount_Entry->$dFieldName = Core_Array::get($aEntry['debit_sc'], $oDChartaccount->$fieldName, 0);
			$oChartaccount_Entry->$dFieldName
				&& $aSubcounts[$oDChartaccount->$fieldName . '_' . $oChartaccount_Entry->$dFieldName] = array($oDChartaccount->$fieldName, $oChartaccount_Entry->$dFieldName);

			$oChartaccount_Entry->$cFieldName = Core_Array::get($aEntry['credit_sc'], $oCChartaccount->$fieldName, 0);
			$oChartaccount_Entry->$cFieldName
				&& $aSubcounts[$oCChartaccount->$fieldName . '_' . $oChartaccount_Entry->$cFieldName] = array($oCChartaccount->$fieldName, $oChartaccount_Entry->$cFieldName);
		}

		$oChartaccount_Entry->save();

		$aChartaccount_Entry_Subcounts = $oChartaccount_Entry->Chartaccount_Entry_Subcounts->findAll(FALSE);

		foreach ($aSubcounts as $aSubcount)
		{
			$oChartaccount_Entry_Subcount = array_shift($aChartaccount_Entry_Subcounts);

			if (is_null($oChartaccount_Entry_Subcount))
			{
				$oChartaccount_Entry_Subcount = Core_Entity::factory('Chartaccount_Entry_Subcount');
				$oChartaccount_Entry_Subcount->chartaccount_entry_id = $oChartaccount_Entry->id;
			}
			$oChartaccount_Entry_Subcount->type = $aSubcount[0];
			$oChartaccount_Entry_Subcount->value = $aSubcount[1];
			$oChartaccount_Entry_Subcount->save();
		}

		// Удаляем оставшиеся
		foreach ($aChartaccount_Entry_Subcounts as $oChartaccount_Entry_Subcount)
		{
			$oChartaccount_Entry_Subcount->delete();
		}

		return $oChartaccount_Entry;
	}
}