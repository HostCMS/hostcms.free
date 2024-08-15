<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision controller
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Revision_Controller
{
	/**
	 * Delete old revisions
	 */
	static public function deleteOldRevisions()
	{
		$aConfig = Core_Config::instance()->get('revision_config', array()) + array(
			'storeDays' => 60
		);

		Core_QueryBuilder::delete('revisions')
			->where('datetime', '<', Core_Date::timestamp2sql(strtotime('-' . $aConfig['storeDays'] . ' days')))
			->execute();
	}

	/**
	 * Get revision
	 * @param object $oModel model for revision
	 * @param int $limit limit, default 20
	 * @param array $aFields list of fields
	 * @return array
	 */
	static public function getRevisions($oModel, $limit = 20, array $aFields = array())
	{
		$oRevisions = Core_Entity::factory('Revision');
		$oRevisions->queryBuilder()
			->where('revisions.model', '=', $oModel->getModelName())
			->where('revisions.entity_id', '=', $oModel->getPrimaryKey())
			->limit($limit)
			->clearOrderBy()
			->orderBy('revisions.datetime', 'DESC');

		count($aFields)
			&& call_user_func_array(array($oRevisions->queryBuilder()->clearSelect(), 'columns'), $aFields);

		return $oRevisions->findAll(FALSE);
	}

	/**
	 * Create revision
	 * @param object $oModel model for revision
	 * @param array $aValues values array
	 */
	static public function backup($oModel, array $aValues)
	{
		$oRevision = Core_Entity::factory('Revision');
		$oRevision
			->model($oModel->getModelName())
			->entity_id($oModel->getPrimaryKey())
			->value(json_encode($aValues))
			->datetime(Core_Date::timestamp2sql(time()))
			->save();

		self::deleteOldRevisions();
	}

	/**
	 * Delete revision
	 * @param sting $modelName model name for revision
	 * @param int $entity_id entity ID
	 */
	static public function delete($modelName, $entity_id)
	{
		$oRevisions = Core_Entity::factory('Revision');
		$oRevisions->queryBuilder()
			->where('revisions.model', '=', $modelName)
			->where('revisions.entity_id', '=', $entity_id)
			->where('revisions.deleted', '=', 0);

		$oRevisions->deleteAll(FALSE);
	}
}