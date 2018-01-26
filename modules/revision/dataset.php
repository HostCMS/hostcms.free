<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision Dataset.
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Revision_Dataset extends Admin_Form_Dataset
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
		return new Revision_Entity();
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

		$queryBuilder = Core_QueryBuilder::select();

		foreach ($aTables as $key => $aTableRow)
		{
			$name = Core_Array::get($aTableRow, 'Name');
			$id = $key + 1;

			$singular = Core_Inflection::getSingular($name);

			$row = $queryBuilder
				->clear()
				->select(array('COUNT(*)', 'count'))
				->from('revisions')
				->where('model', '=', $singular)
				->where('deleted', '=', 0)
				->execute()
				->asAssoc()
				->current();

			$count = $row['count'];

			if ($count)
			{
				$oRevision_Entity = $this->_objects[$id] = $this->_newObject();

				$oRevision_Entity->setTableColums(array(
					'id' => array(),
					'table_name' => array(),
					'name' => array(),
					'count' => array(),
				));

				$singular = Core_Inflection::getSingular($name);

				$oRevision_Entity->id = $id;
				$oRevision_Entity->table_name = $name;

				try {
					$oRevision_Entity->name = Core::_($singular . '.model_name');
				}
				catch (Exception $e) {
					$oRevision_Entity->name = 'Unknown';
				}

				$oRevision_Entity->count = $count;
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