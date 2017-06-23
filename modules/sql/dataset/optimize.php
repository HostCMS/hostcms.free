<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Sql_Dataset_Optimize extends Admin_Form_Dataset
{
	/**
	 * Count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Database instance
	 * @var Core_DataBase
	 */
	protected $_database = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_database = Core_DataBase::instance();
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		return count($this->_objects);
	}

	/**
	 * Dataset objects list
	 * @var array
	 */
	protected $_objects = array();

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		$aTables = $this->_database->getTables();

		foreach ($aTables as $key => $sTable)
		{
			$aTables[$key] = $this->_database->quoteColumnName($sTable);
		}

		try
		{
			$this->_database->setQueryType(0)
				->query("OPTIMIZE TABLE " . implode(',', $aTables));
		}
		catch (Exception $e)
		{
			Core_Message::show($e->getMessage(), 'error');
		}

		$return = $this->_objects = $this->_database->asObject(NULL)->result();

		foreach ($return as $row)
		{
			$sTableName = $this->_database->quoteColumnName($row->Table);

			// Сбрасывать для этих таблиц AUTO_INCREMENT нельзя
			if (strpos($row->Table, 'admin_form') === FALSE
			&& strpos($row->Table, 'admin_language') === FALSE
			&& strpos($row->Table, 'admin_word') === FALSE)
			{
				try
				{
					// Get table engine
					$aExplode = explode('.', $row->Table);
					$aTableStatus = $this->_database->setQueryType(0)
						->asAssoc()
						->query("SHOW TABLE STATUS LIKE " . $this->_database->quote(end($aExplode)))
						->current();

					// Just for MyISAM
					//if (strtolower(Core_Array::get($aTableStatus, 'Engine')) == 'myisam')
					//{
						$this->_database->setQueryType(5)
							->query("ALTER TABLE {$sTableName} AUTO_INCREMENT = 1");
					//}
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
				}
			}

			try
			{
				// Проверка на дублирующиеся индексы
				$aTableIndexes = $this->_database
					->setQueryType(NULL)
					->query("SHOW INDEX FROM {$sTableName}")
					->asAssoc()->result();

				$aIndexes = array();
				foreach ($aTableIndexes as $aIndex)
				{
					$aIndexes[$aIndex['Key_name']][] = $aIndex['Column_name'];
				}

				 while ($aIndexRow1 = array_shift($aIndexes))
				 {
					foreach ($aIndexes as $aIndexKey2 => $aIndexRow2)
					{
						$aArray_intersect = array_intersect($aIndexRow1, $aIndexRow2);

						// Пересеченный массив идентичен двум исходным
						if (count($aArray_intersect) == count($aIndexRow1) && count($aArray_intersect) == count($aIndexRow2))
						{
							Core_Message::show(Core::_('Sql.drop_index', $aIndexKey2, $row->Table));

							$this->_database->setQueryType(5)
								->query("ALTER TABLE {$sTableName} DROP INDEX " . $this->_database->quoteColumnName($aIndexKey2));
						}
					}
				}
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}
		}

		return $return;
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		return $this->_objects[$primaryKey];
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		$stdClass = new stdClass();
		$stdClass->Table = $stdClass->Op = $stdClass->Msg_type = $stdClass->Msg_text = '';
		return $stdClass;
	}
}