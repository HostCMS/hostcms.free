<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision.
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Revision_Entity extends Core_Entity
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
	protected $_modelName = 'revision';

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
	public function getTableColums()
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
		$offset = 0;
		$limit = 100;

		$singular = Core_Inflection::getSingular($this->table_name);

		do {
			$oRevisions = Core_Entity::factory('Revision');
			$oRevisions
				->queryBuilder()
				->where('model', '=', $singular)
				->offset($offset)
				->limit($limit);

			$aRevisions = $oRevisions->findAll(FALSE);
			foreach ($aRevisions as $oRevision)
			{
				$oRevision->markDeleted();
			}

			// $offset += $limit;
		}
		while (count($aRevisions));

		return $this;
	}
}