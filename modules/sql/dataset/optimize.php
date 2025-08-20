<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
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
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		if (!$this->_loaded)
		{
			$aTables = $this->_database->getTables();

			foreach ($aTables as $key => $sTable)
			{
				$aTables[$key] = $this->_database->quoteTableName($sTable);
			}

			try
			{
				$this->_database->setQueryType(0)
					->query("OPTIMIZE TABLE " . implode(',', $aTables));
					
				if (!is_null(Core_Array::getRequest('debug')))
				{
					echo '<p><b>Select Query</b>: <pre>', Core_DataBase::instance()->getLastQuery(), '</pre></p>';
				}
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}

			$this->_objects = $this->_database->asObject(NULL)->result();

			foreach ($aTables as $sTable)
			{
				// Сбрасывать для этих таблиц AUTO_INCREMENT нельзя
				if (strpos($sTable, 'admin_') !== 0)
				{
					try
					{
						$this->_database->setQueryType(5)
							->query("ALTER TABLE {$sTable} AUTO_INCREMENT = 1");

						if (!is_null(Core_Array::getRequest('debug')))
						{
							echo '<p><b>Reset AUTO_INCREMENT</b>: <pre>', Core_DataBase::instance()->getLastQuery(), '</pre></p>';
						}
					}
					catch (Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
					}
				}
			}
			
			$this->_loaded = TRUE;
		}

		return $this->_objects;
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