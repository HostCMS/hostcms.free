<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Controller
 *
 * @package HostCMS
 * @subpackage Field
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Controller
{
	/**
	 * Array of model's fields
	 * @var array|NULL
	 */
	static protected $_fields = NULL;

	/**
	 * Fill Model's Fields Cache
	 */
	static protected function _fillFields()
	{
		if (is_null(self::$_fields) && defined('CURRENT_SITE'))
		{
			self::$_fields = array();

			$oFields = Core_Entity::factory('Field');
			$oFields->queryBuilder()
				->open()
					->where('fields.site_id', '=', CURRENT_SITE)
					->setOr()
					->where('fields.site_id', '=', 0)
				->close()
				->clearOrderBy()
				->orderBy('sorting', 'ASC');

			$aFields = $oFields->findAll(FALSE);

			foreach ($aFields as $oField)
			{
				self::$_fields[$oField->model][] = $oField;
			}
		}
	}

	/**
	 * Get Model Filelds
	 * @param string $modelName Model name
	 * @return array
	 */
	static public function getFields($modelName)
	{
		self::_fillFields();

		return isset(self::$_fields[$modelName])
			? self::$_fields[$modelName]
			: array();
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