<?php
/**
 * Property_Controller
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Property_Controller
{
	/**
	 * Types array
	 * @var array|NULL
	 */
	static protected $_types = NULL;

	/**
	 * Get Property Types
	 * @return array
	 */
	static public function getTypes()
	{
		if (is_null(self::$_types))
		{
			self::$_types = array(
				0 => Core::_('Property.type0'),
				15 => Core::_('Property.type15'),
				11 => Core::_('Property.type11'),
				1 => Core::_('Property.type1'),
				2 => Core::_('Property.type2'),
				3 => Core::_('Property.type3'),
				4 => Core::_('Property.type4'),
				5 => Core::_('Property.type5'),
				13 => Core::_('Property.type13'),
				12 => Core::_('Property.type12'),
				14 => Core::_('Property.type14'),
				6 => Core::_('Property.type6'),
				7 => Core::_('Property.type7'),
				8 => Core::_('Property.type8'),
				9 => Core::_('Property.type9'),
				10 => Core::_('Property.type10')
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
	 * Set Property Types
	 * @param array $types
	 */
	static public function setTypes(array $types)
	{
		self::$_types = $types;
	}

	/**
	 * Get property large image file name
	 * @param Core_Entity $oEntity entity
	 * @param Property_Value_File_Model $oProperty_Value entity of property_value
	 * @param string $originalFileName original file name
	 * @return string
	 */
	static public function getLargeFileName(Core_Entity $oEntity, $oProperty_Value, $originalFileName)
	{
		$modelName = $oEntity->getModelName();

		$oProperty = $oProperty_Value->Property;

		$name = $oProperty->prefix_large_file != ''
			? $oProperty->prefix_large_file
			: $modelName . '_property_';

		return $name . $oProperty_Value->id . '.' . Core_File::getExtension($originalFileName);
	}

    /**
     * Get property small image file name
     * @param Core_Entity $oEntity
     * @param Property_Value_File_Model $oProperty_Value entity of property_value
     * @param string $originalFileName original file name
     * @return string
     */
	static public function getSmallFileName(Core_Entity $oEntity, $oProperty_Value, $originalFileName)
	{
		$modelName = $oEntity->getModelName();

		$oProperty = $oProperty_Value->Property;

		$name = $oProperty->prefix_small_file != ''
			? $oProperty->prefix_small_file
			: 'small_' . $modelName . '_property_';

		return $name . $oProperty_Value->id . '.' . Core_File::getExtension($originalFileName);
	}
}