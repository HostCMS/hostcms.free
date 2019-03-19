<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision controller
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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