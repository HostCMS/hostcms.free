<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field_Controller
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Field_Controller
{
	/**
	 * Array of model's fields
	 * @var array
	 */
	static protected $_fields = array();

	/**
	 * Types array
	 * @var array|NULL
	 */
	static protected $_types = NULL;

	static public function getTypes()
	{
		if (is_null(self::$_types))
		{
			self::$_types = array(
				0 => Core::_('Field.type0'),
				15 => Core::_('Field.type15'),
				11 => Core::_('Field.type11'),
				1 => Core::_('Field.type1'),
				2 => Core::_('Field.type2'),
				3 => Core::_('Field.type3'),
				4 => Core::_('Field.type4'),
				5 => Core::_('Field.type5'),
				13 => Core::_('Field.type13'),
				12 => Core::_('Field.type12'),
				14 => Core::_('Field.type14'),
				6 => Core::_('Field.type6'),
				7 => Core::_('Field.type7'),
				8 => Core::_('Field.type8'),
				9 => Core::_('Field.type9'),
				10 => Core::_('Field.type10')
			);

			// Delete list type if module is not active
			if (!Core::moduleIsActive('list'))
			{
				unset(self::$_types[3]);
			}
			// Delete informationsystem type if module is not active
			if (!Core::moduleIsActive('informationsystem'))
			{
				unset(self::$_types[5]);
				unset(self::$_types[13]);
			}
			// Delete shop type if module is not active
			if (!Core::moduleIsActive('shop'))
			{
				unset(self::$_types[12]);
				unset(self::$_types[14]);
			}
		}

		return self::$_types;
	}

	/**
	 * Set Field Types
	 * @param array $types
	 */
	static public function setTypes(array $types)
	{
		self::$_types = $types;
	}

	/**
	 * Fill Model's Fields Cache
	 */
	static protected function _fillFieldDirs()
	{
		$aReturn = array();

		$oField_Dirs = Core_Entity::factory('Field_Dir');
		$oField_Dirs->queryBuilder()
			->clearOrderBy()
			->orderBy('sorting', 'ASC');

		$aField_Dirs = $oField_Dirs->findAll(FALSE);

		foreach ($aField_Dirs as $oField_Dir)
		{
			$aReturn[$oField_Dir->model][] = $oField_Dir;
		}

		return $aReturn;
	}

	/**
	 * Get Model Filelds
	 * @param string $modelName Model name
	 * @return array
	 */
	static public function getFieldDirs($modelName)
	{
		$aField_Dirs = self::_fillFieldDirs();

		return isset($aField_Dirs[$modelName])
			? $aField_Dirs[$modelName]
			: array();
	}

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