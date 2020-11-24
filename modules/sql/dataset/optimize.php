<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * SQL.
 *
 * @package HostCMS
 * @subpackage Sql
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$aTables[$key] = $this->_database->quoteTableName($sTable);
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

		$this->_objects = $this->_database->asObject(NULL)->result();

		foreach ($aTables as $sTable)
		{
			// Сбрасывать для этих таблиц AUTO_INCREMENT нельзя
			if (strpos($sTable, 'admin_form') === FALSE
				&& strpos($sTable, 'admin_language') === FALSE
				&& strpos($sTable, 'admin_word') === FALSE)
			{
				try
				{
					$this->_database->setQueryType(5)
						->query("ALTER TABLE {$sTable} AUTO_INCREMENT = 1");
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
				}
			}
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