<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Group_Property_List_Model
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Informationsystem_Group_Property_List_Model extends Informationsystem_Model
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'informationsystems';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'informationsystem_group_property_list';

	/**
	 * Backend property
	 * @var mixed
	 */
	public $changeFilename = TRUE;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $watermarkFilePath = '';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'property' => array(
			'through' => 'informationsystem_group_property',
			'foreign_key' => 'informationsystem_id',
			'dependent_key' => 'property_id'
		),
		'property_dir' => array(
			'through' => 'informationsystem_group_property_dir',
			'foreign_key' => 'informationsystem_id',
			'dependent_key' => 'property_dir_id'
		)
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		$this->changeFilename = $this->change_filename;
		$this->watermarkFilePath = $this->getWatermarkFilePath();
	}

	/**
	 * Get large image max width
	 * @return int
	 */
	public function getLargeImageMaxWidth()
	{
		return $this->group_image_large_max_width;
	}

	/**
	 * Get large image max height
	 * @return int
	 */
	public function getLargeImageMaxHeight()
	{
		return $this->group_image_large_max_height;
	}

	/**
	 * Get small image max width
	 * @return int
	 */
	public function getSmallImageMaxWidth()
	{
		return $this->group_image_small_max_width;
	}

	/**
	 * Get small image max height
	 * @return int
	 */
	public function getSmallImageMaxHeight()
	{
		return $this->group_image_small_max_height;
	}

	/**
	 * Get object directory href
	 * @param Core_Entity $object
	 * @return string
	 */
	public function getDirHref(Core_Entity $object)
	{
		return $object->getGroupHref();
	}

	/**
	 * Get object directory path
	 * @param Core_Entity $object
	 * @return string
	 */
	public function getDirPath(Core_Entity $object)
	{
		return $object->getGroupPath();
	}

	/**
	 * Create object directory
	 * @param Core_Entity $object
	 * @return self
	 */
	public function createPropertyDir(Core_Entity $object)
	{
		$object->createDir();
		return $this;
	}

	/**
	 * Get property large image file name
	 * @param Core_Entity $object entity
	 * @param Property_Value_Model $oFileValue entity of property_value
	 * @param string $originalFileName original file name
	 * @return string
	 */
	public function getLargeFileName(Core_Entity $object, $oFileValue, $originalFileName)
	{
		return Property_Controller::getLargeFileName($object, $oFileValue, $originalFileName);
	}

	/**
	 * Get property small image file name
	 * @param Core_Entity $object entity
	 * @param Property_Value_Model $oFileValue entity of property_value
	 * @param string $originalFileName original file name
	 * @return string
	 */
	public function getSmallFileName(Core_Entity $object, $oFileValue, $originalFileName)
	{
		return Property_Controller::getSmallFileName($object, $oFileValue, $originalFileName);
	}

	/**
	 * Check if watermark should be laid on large image
	 * @return int
	 */
	public function layWatermarOnLargeImage()
	{
		return $this->watermark_default_use_large_image;
	}

	/**
	 * Check if watermark should be laid on small image
	 * @return int
	 */
	public function layWatermarOnSmallImage()
	{
		return $this->watermark_default_use_small_image;
	}
}