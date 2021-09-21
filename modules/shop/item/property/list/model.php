<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Property_List_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Property_List_Model extends Shop_Model
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
	protected $_modelName = 'shop_item_property_list';

	/**
	 * Callback property_id
	 * @var boolean
	 */
	public $changeFilename = TRUE;

	/**
	 * Callback property_id
	 * @var string
	 */
	public $watermarkFilePath = '';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'property' => array(
			'through' => 'shop_item_property',
			'foreign_key' => 'shop_id',
			'dependent_key' => 'property_id'
		),
		'property_dir' => array(
			'through' => 'shop_item_property_dir',
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
	 * Get object directory href
	 * @param Core_Entity $object
	 * @return string
	 */
	public function getDirHref(Core_Entity $object)
	{
		return $object->getItemHref();
	}

	/**
	 * Get object directory path
	 * @param Core_Entity $object
	 * @return string
	 */
	public function getDirPath(Core_Entity $object)
	{
		return $object->getItemPath();
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
		return 'shop_property_file_' . $object->id . '_' . $oFileValue->id . '.' . Core_File::getExtension($originalFileName);
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
		return 'small_shop_property_file_' . $object->id . '_' . $oFileValue->id . '.' . Core_File::getExtension($originalFileName);
	}

	/**
	 * Получение свойств товара, доступных группе $shop_group_id
	 * @param int $shop_group_id идентификатор группы
	 * @param array $property_ids массив идентификаторов дополнительных свойств, доступных для выборки, по умолчанию NULL
	 * @param boolean $bCache кэшировать результаты, по умочланию TRUE
	 * @return array
	 */
	public function getPropertiesForGroup($shop_group_id, $property_ids = NULL, $bCache = TRUE)
	{
		$oProperties = $this->Properties;
		if ($shop_group_id !== FALSE)
		{
			$oProperties
				->queryBuilder()
				->join('shop_item_property_for_groups', 'shop_item_property_for_groups.shop_item_property_id', '=', 'shop_item_properties.id')
				->where('shop_item_property_for_groups.shop_id', '=', $this->id)
				->where('shop_item_property_for_groups.shop_group_id', is_array($shop_group_id) ? 'IN' : '=', $shop_group_id);
				
			is_array($shop_group_id)
				&& $oProperties
					->queryBuilder()
					->groupBy('properties.id');
		}

		if (is_array($property_ids) && count($property_ids))
		{
			$oProperties
				->queryBuilder()
				->where('properties.id', 'IN', $property_ids);
		}

		return $oProperties->findAll($bCache);
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