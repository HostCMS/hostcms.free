<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Digital_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Digital_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $iternal_order = NULL;

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'count' => -1
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_order_item_digital' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_item' => array(),
		'user' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Get digital items count
	 * @return int Count of digital items or -1 for unlimited
	 */
	public function getCountDigitalItems()
	{
		$sum = 0;

		$aShop_Item_Digitals = $this->getBySorting();
		foreach ($aShop_Item_Digitals as $oShop_Item_Digital)
		{
			// Если хотя бы у одного электронного товара количество равно -1 (бесконечность), то считаем что весь товар неограничен
			if ($oShop_Item_Digital->count == -1)
			{
				$sum = -1;
				break;
			}

			$sum += $oShop_Item_Digital->count;
		}

		return $sum;
	}

	/**
	 * Get the most suits digital item
	 * @return array
	 */
	public function getBySorting()
	{
		$this->queryBuilder()
			->select('*')
			->select(
				array(Core_QueryBuilder::expression("IF(`count` = '-1', 2, IF(`count` = '0', 3, 1))"), 'iternal_order')
			)
			->orderBy('iternal_order', 'ASC')
			->orderBy('count', 'ASC')
			->orderBy('id');

		return $this->findAll(FALSE);
	}

	/**
	 * Get file path
	 * @return string
	 */
	public function getFilePath()
	{
		// fix trouble with deleted Shop_Item
		$oShop_Item = Core_Entity::factory('Shop_Item', $this->shop_item_id);

		return $oShop_Item->Shop->getPath() . '/eitems/item_catalog_' . $this->shop_item_id . '/';
	}

	/**
	 * Get file href
	 * @return string
	 */
	public function getFileHref()
	{
		return '/' . $this->Shop_Item->Shop->getHref() . '/eitems/item_catalog_' . $this->Shop_Item->id . '/';
	}

	/**
	 * Get full file path
	 * @return string
	 */
	public function getFullFilePath()
	{
		return $this->getFilePath() . $this->id . (Core_File::getExtension($this->filename) != ''
			? '.' . Core_File::getExtension($this->filename)
			: ''
		);
	}

	/**
	 * Get full file href
	 * @return string
	 */
	public function getFullFileHref()
	{
		return $this->getFileHref() . $this->id . rawurlencode(
			Core_File::getExtension($this->filename) != ''
				? '.' . Core_File::getExtension($this->filename)
				: ''
		);
	}

	/**
	 * Create directory for item
	 * @return self
	 */
	public function createDir()
	{
		if (!is_dir($this->getFilePath()))
		{
			try
			{
				Core_File::mkdir($this->getFilePath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete digital item's file
	 */
	public function deleteFile()
	{
		try
		{
			$path = $this->getFullFilePath();
			if (is_file($path))
			{
				Core_File::delete($path);
			}
		} catch (Exception $e) {}

		$this->filename = '';
		$this->save();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_item_digital.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Order_Item_Digitals->deleteAll(FALSE);

		try
		{
			$path = $this->getFullFilePath();

			if (is_file($path))
			{
				Core_File::delete($path);
			}
		} catch (Exception $e){}

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_item_digital.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event shop_item_digital.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		if ($this->filename != '')
		{
			$this->clearXmlTags()
				->addXmlTag('path', $this->getFullFilePath());
		}

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_item_digital.onBeforeGetRelatedSite
	 * @hostcms-event shop_item_digital.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}