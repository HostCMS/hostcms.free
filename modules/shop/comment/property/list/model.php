<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Comment_Property_List_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Comment_Property_List_Model extends Shop_Model
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'shops';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'shop_comment_property_list';

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
			'through' => 'shop_comment_property',
			'foreign_key' => 'shop_id',
			'dependent_key' => 'property_id'
		),
		'property_dir' => array(
			'through' => 'shop_comment_property_dir',
			'foreign_key' => 'shop_id',
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
		return $this->image_large_max_width;
	}

	/**
	 * Get large image max height
	 * @return int
	 */
	public function getLargeImageMaxHeight()
	{
		return $this->image_large_max_height;
	}

	/**
	 * Get small image max width
	 * @return int
	 */
	public function getSmallImageMaxWidth()
	{
		return $this->image_small_max_width;
	}

	/**
	 * Get small image max height
	 * @return int
	 */
	public function getSmallImageMaxHeight()
	{
		return $this->image_small_max_height;
	}

	/**
	 * Get object's directory href
	 * @param Core_Entity $object object
	 * @return string
	 */
	public function getDirHref(Core_Entity $object)
	{
		return $object->getHref();
	}

	/**
	 * Get object's directory path
	 * @param Core_Entity $object object
	 * @return string
	 */
	public function getDirPath(Core_Entity $object)
	{
		return $object->getPath();
	}

	/**
	 * Create directory for object
	 * @param Core_Entity $object object
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
		return 'comment_property_' . $oFileValue->id . '.' . Core_File::getExtension($originalFileName);
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
		return 'small_comment_property_' . $oFileValue->id . '.' . Core_File::getExtension($originalFileName);
	}

	/**
	 * The position of watermark on the X axis
	 * @return string
	 */
	public function getWatermarkDefaultPositionX()
	{
		return $this->watermark_default_position_x;
	}

	/**
	 * The position of watermark on the Y axis
	 * @return string
	 */
	public function getWatermarkDefaultPositionY()
	{
		return $this->watermark_default_position_y;
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

	/**
	 * Preserve aspect ratio of large image
	 * @return int
	 */
	/*public function preserveAspectRatioOfLargeImage()
	{
		return $this->preserve_aspect_ratio;
	}*/

	/**
	 * Preserve aspect ratio of small image
	 * @return int
	 */
	/*public function preserveAspectRatioOfSmallImage()
	{
		return $this->preserve_aspect_ratio_small;
	}*/
}