<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Trash.
 *
 * @package HostCMS
 * @subpackage Trash
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Trash_Entity extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $id = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $table_name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $name = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $count = NULL;

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'trash';

	/**
	 * Load columns list
	 * @return self
	 */
	protected function _loadColumns()
	{
		return $this;
	}

	/**
	 * Get primary key name
	 * @return string
	 */
	public function getPrimaryKeyName()
	{
		return 'id';
	}

	/**
	 * Table columns
	 * @var array
	 */
	protected $_tableColums = array();

	/**
	 * Set table columns
	 * @param array $tableColums columns
	 * @return self
	 */
	public function setTableColums($tableColums)
	{
		$this->_tableColums = $tableColums;
		return $this;
	}

	/**
	 * Get table columns
	 * @return array
	 */
	public function getTableColumns()
	{
		return $this->_tableColums;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 */
	public function delete($primaryKey = NULL)
	{
		if ($this->table_name != '')
		{
			$Trash_Table_Dataset = new Trash_Table_Dataset($this->table_name);

			$totalCount = $Trash_Table_Dataset->getCount();
			$limit = 100;

			while ($totalCount > 0)
			{
				$aTrash_Table_Items = $Trash_Table_Dataset
					->limit($limit)
					->clear()
					->getObjects();

				foreach ($aTrash_Table_Items as $oTrash_Table_Item)
				{
					$oTrash_Table_Item->delete();
				}

				$totalCount -= $limit;
			}
		}

		return $this;
	}

	/**
	 * Turn off deleted status
	 * @return self
	 */
	public function undelete()
	{
		if ($this->table_name != '')
		{
			$Trash_Table_Dataset = new Trash_Table_Dataset($this->table_name);

			$totalCount = $Trash_Table_Dataset->getCount();
			$limit = 100;

			while ($totalCount > 0)
			{
				$aTrash_Table_Items = $Trash_Table_Dataset
					->limit($limit)
					->clear()
					->getObjects();

				foreach ($aTrash_Table_Items as $oTrash_Table_Item)
				{
					$oTrash_Table_Item->undelete();
				}

				$totalCount -= $limit;
			}
		}

		return $this;
	}
}