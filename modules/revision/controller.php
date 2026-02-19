<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Revision controller
 *
 * @package HostCMS
 * @subpackage Revision
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Revision_Controller
{
	/**
	 * Get properties values
	 * @param Core_Entity $oObject
	 * @return array
	 */
	static public function getPropertyValues(Core_Entity $oObject)
	{
		$aReturn = array();

		$aProperty_Values = $oObject->getPropertyValues(FALSE);

		foreach ($aProperty_Values as $oProperty_Value)
		{
			$oProperty = $oProperty_Value->Property;

            if (!in_array($oProperty->type, array(2)))
			{
				$aReturn[$oProperty->id][] = $oProperty_Value->value;
			}
		}

		return $aReturn;
	}

	/**
	 * Set property values
	 * @param Core_Entity $oObject
	 * @param array $aData
	 */
	static public function setPropertyValues(Core_Entity $oObject, $aData)
	{
		$aExists = array();

		$aProperty_Values = $oObject->getPropertyValues(FALSE);

		foreach ($aProperty_Values as $oProperty_Value)
		{
			$oProperty = $oProperty_Value->Property;

			if (!in_array($oProperty->type, array(2)))
			{
				$aExists[$oProperty->id][] = $oProperty_Value;
			}
		}

		foreach ($aData as $property_id => $aValues)
		{
			$oProperty = Core_Entity::factory('Property')->getById($property_id);

			if (!is_null($oProperty) && !in_array($oProperty->type, array(2)))
			{
				foreach ($aValues as $value)
				{
					$oPV = isset($aExists[$property_id]) && count($aExists[$property_id])
						? array_shift($aExists[$property_id])
						: $oProperty->createNewValue($oObject->id);

					$oPV->value = $value;
					$oPV->save();
				}
			}
		}

		// Удаляем невостребованные значения
		foreach ($aExists as $aProperty_Value_Exists)
		{
			foreach ($aProperty_Value_Exists as $oProperty_Value)
			{
				$oProperty_Value->delete();
			}
		}
	}

	/**
	 * Get properties values
	 * @param Core_Entity $oObject
	 * @return array
	 */
	static public function getFieldValues(Core_Entity $oObject)
	{
		$aReturn = array();

		if (Core::moduleIsActive('field'))
		{
			$aFields = Field_Controller::getFields($oObject->getModelName());

			if (count($aFields))
			{
				$aFieldsIds = array();
				foreach ($aFields as $oField)
				{
					$aFieldsIds[] = $oField->id;
				}

				$aField_Values = Field_Controller_Value::getFieldsValues($aFieldsIds, $oObject->id, FALSE);

				foreach ($aField_Values as $oField_Value)
				{
					$oField = $oField_Value->Field;

					if (!in_array($oField->type, array(2)))
					{
						$aReturn[$oField->id][] = $oField_Value->value;
					}
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Set field values
	 * @param Core_Entity $oObject
	 * @param array $aData
	 */
	static public function setFieldValues(Core_Entity $oObject, $aData)
	{
		if (Core::moduleIsActive('field'))
		{
			$aFields = Field_Controller::getFields($oObject->getModelName());

			if (count($aFields))
			{
				$aFieldsIds = array();
				foreach ($aFields as $oField)
				{
					$aFieldsIds[] = $oField->id;
				}

				$aExists = array();

				$aField_Values = Field_Controller_Value::getFieldsValues($aFieldsIds, $oObject->id, FALSE);

				foreach ($aField_Values as $oField_Value)
				{
					$oField = $oField_Value->Field;

					if (!in_array($oField->type, array(2)))
					{
						$aExists[$oField->id][] = $oField_Value;
					}
				}

				foreach ($aData as $field_id => $aValues)
				{
					$oField = Core_Entity::factory('Field')->getById($field_id);

					if (!is_null($oField) && !in_array($oField->type, array(2)))
					{
						foreach ($aValues as $value)
						{
							$oFV = isset($aExists[$field_id]) && count($aExists[$field_id])
								? array_shift($aExists[$field_id])
								: $oField->createNewValue($oObject->id);

							$oFV->value = $value;
							$oFV->save();
						}
					}
				}

				// Удаляем невостребованные значения
				foreach ($aExists as $aField_Value_Exists)
				{
					foreach ($aField_Value_Exists as $oField_Value)
					{
						$oField_Value->delete();
					}
				}
			}
		}
	}

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