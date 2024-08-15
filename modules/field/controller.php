<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Controller
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Field_Controller
{
	/**
	 * Array of model's fields
	 * @var array
	 */
	static protected $_fields = array();

	/**
	 * Fill Model's Fields Cache
	 * @param int $site_id
	 */
	static protected function _fillFields($site_id)
	{
		if (!isset(self::$_fields[$site_id]))
		{
			self::$_fields[$site_id] = array();

			$oFields = Core_Entity::factory('Field');
			$oFields->queryBuilder()
				->open()
					->where('fields.site_id', '=', $site_id)
					->setOr()
					->where('fields.site_id', '=', 0)
				->close()
				->clearOrderBy()
				->orderBy('sorting', 'ASC');

			$aFields = $oFields->findAll(FALSE);

			foreach ($aFields as $oField)
			{
				self::$_fields[$site_id][$oField->model][] = $oField;
			}
		}
	}

	/**
	 * Get Model Filelds
	 * @param string $modelName Model name
	 * @param int $site_id if NULL uses CURRENT_SITE, default NULL
	 * @return array
	 */
	static public function getFields($modelName, $site_id = NULL)
	{
		is_null($site_id)
			&& defined('CURRENT_SITE')
			&& $site_id = CURRENT_SITE;

		if ($site_id)
		{
			self::_fillFields($site_id);

			return isset(self::$_fields[$site_id][$modelName])
				? self::$_fields[$site_id][$modelName]
				: array();
		}

		return array();
	}

	/**
	 * Get Field's file path for object (without leading slash)
	 * @param object $object
	 * @return string
	 */
	static public function getPath($object)
	{
		$oSite = $object->getRelatedSite();

		$uploadDir = $oSite
			? $oSite->uploaddir
			: 'upload/';

		$nesting_level = $oSite
			? $oSite->nesting_level
			: 3;

		return $uploadDir . 'fields/' . Core_File::getNestingDirPath($object->id, $nesting_level) . '/' . $object->id . '/';
	}
}