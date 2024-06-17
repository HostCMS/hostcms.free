<?php
/**
 * Property_Controller
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Property_Controller
{
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
	 * @param Core_Entity $object entity
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