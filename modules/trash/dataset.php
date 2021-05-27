<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Trash Dataset.
 *
 * @package HostCMS
 * @subpackage Trash
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Trash_Dataset extends Admin_Form_Dataset
{
	/**
	 * Items count
	 * @var int
	 */
	protected $_count = NULL;

	/**
	 * Database instance
	 * @var Core_DataBase
	 */
	protected $_dataBase = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_dataBase = Core_DataBase::instance();
	}

	/**
	 * Get count of finded objects
	 * @return int
	 */
	public function getCount()
	{
		if (is_null($this->_count))
		{
			$this->fillTables();
			$this->_count = count($this->_objects);
		}

		return $this->_count;
	}

	/**
	 * Dataset objects list
	 * @var array
	 */
	protected $_objects = array();

	/**
	 * Get Objects
	 * @return array
	 */
	public function getObjects()
	{
		return $this->_objects;
	}

	/**
	 * Get new object
	 * @return object
	 */
	protected function _newObject()
	{
		return new Trash_Entity();
	}

	/**
	 * Load objects
	 * @return array
	 */
	public function load()
	{
		return array_slice($this->_objects, $this->_offset, $this->_limit);
	}

	/**
	 * Load data
	 * @return self
	 */
	public function fillTables()
	{
		$this->_objects = array();

		//$aTables = $this->_dataBase->getTables();
		$aTables = $this->_dataBase->query('SHOW TABLE STATUS')->asAssoc()->result();

		$aDbConfig = $this->_dataBase->getConfig();

		// information about all columns in all tables
		$aAllColumns = $this->_dataBase
			->query('SELECT `TABLE_NAME`, `COLUMN_NAME` FROM INFORMATION_SCHEMA.COLUMNS WHERE `table_schema` = ' . $this->_dataBase->quote($aDbConfig['database']))
			->asAssoc()
			->result();

		$aTableColumns = array();
		foreach ($aAllColumns as $aColumn)
		{
			$aTableColumns[$aColumn['TABLE_NAME']][] = $aColumn['COLUMN_NAME'];
		}

		$queryBuilder = Core_QueryBuilder::select();

		$aConfig = Core_Config::instance()->get('trash_config', array()) + array(
			'maxExactCount' => 100000
		);

		foreach ($aTables as $key => $aTableRow)
		{
			$sEngine = strtoupper(Core_Array::get($aTableRow, 'Engine'));
			$sComment = Core_Array::get($aTableRow, 'Comment');

			if ($sEngine != '' && $sComment != 'VIEW')
			{
				$name = Core_Array::get($aTableRow, 'Name');
				$iRows = Core_Array::get($aTableRow, 'Rows');

				$id = $key + 1;

				if (isset($aTableColumns[$name]) && in_array('deleted', $aTableColumns[$name]) && strpos($name, '~') !== 0)
				{
					if ($iRows < $aConfig['maxExactCount'] || $sEngine == 'MYISAM')
					{
						$row = $queryBuilder
							->clear()
							->select(array('COUNT(*)', 'count'))
							->from($name)
							->where('deleted', '=', 1)
							->execute()
							->asAssoc()
							->current();

						$count = $row['count'];
					}
					else
					{
						$count = '???';
					}

					if ($count)
					{
						$oTrash_Entity = $this->_objects[$id] = $this->_newObject();

						$oTrash_Entity->setTableColums(array(
							'id' => array(),
							'table_name' => array(),
							'name' => array(),
							'count' => array(),
						));

						$singular = Core_Inflection::getSingular($name);

						$oTrash_Entity->id = $id;
						$oTrash_Entity->table_name = $name;

						try {
							$oTrash_Entity->name = Core::_($singular . '.model_name');
						}
						catch (Exception $e) {
							$oTrash_Entity->name = 'Unknown';
						}

						$oTrash_Entity->count = $count;
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Get entity
	 * @return object
	 */
	public function getEntity()
	{
		return $this->_newObject();
	}

	/**
	 * Get object
	 * @param int $primaryKey ID
	 * @return object
	 */
	public function getObject($primaryKey)
	{
		!count($this->_objects) && $this->fillTables();
		return isset($this->_objects[$primaryKey])
			? $this->_objects[$primaryKey]
			: $this->_newObject();
	}
}