<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision.
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Revision_Entity extends Core_Empty_Entity
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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 */
	public function delete($primaryKey = NULL)
	{
		$offset = 0;
		$limit = 500;

		$singular = Core_Inflection::getSingular($this->table_name);

		$oUser = Core_Auth::getCurrentUser();

		do {
			$oRevisions = Core_Entity::factory('Revision');
			$oRevisions
				->queryBuilder()
				->clearSelect()
				->select('id', 'model', 'user_id', 'deleted')
				->where('model', '=', $singular)
				->clearOrderBy()
				->orderBy('id', 'ASC')
				->offset($offset)
				->limit($limit);

			$aRevisions = $oRevisions->findAll(FALSE);
			foreach ($aRevisions as $oRevision)
			{
				if (!$oUser || $oUser->checkObjectAccess($oRevision))
				{
					$oRevision->markDeleted();
				}
				else
				{
					$offset++;
				}
			}

			// $offset += $limit;
		}
		while (count($aRevisions));

		return $this;
	}
}