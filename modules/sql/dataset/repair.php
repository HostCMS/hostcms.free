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
class Sql_Dataset_Repair extends Admin_Form_Dataset
{
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
		$aRepair = array();
		
		foreach ($aTables as $key => $sTable)
		{
			$aTableStatus = $this->_database->setQueryType(0)
				->asAssoc()
				->query("SHOW TABLE STATUS LIKE " . $this->_database->quote($sTable))
				->current();

			// Just for MyISAM
			if (strtolower(Core_Array::get($aTableStatus, 'Engine')) == 'myisam')
			{
				$aRepair[] = $this->_database->quoteColumnName($sTable);
			}
		}

		if (count($aRepair))
		{
			try
			{
				$this->_database->setQueryType(0)
					->query("REPAIR TABLE " . implode(',', $aRepair));
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}

			$this->_objects = $this->_database->asObject(NULL)->result();
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