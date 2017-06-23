<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Delivery_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Delivery_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	var $conditions = 1;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_delivery_condition' => array(),
		'shop_delivery_payment_system' => array(),
		'shop_payment_system' => array('through' => 'shop_delivery_payment_system')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'type' => 0,
		'sorting' => 0,
		'active' => 1
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_deliveries.sorting' => 'ASC',
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Get the path to the delivery's image
	 * @return string
	 */
	public function getDeliveryFilePath()
	{
		return $this->getPath() . $this->image;
	}

	/**
	 * Get delivery file href
	 * @return string
	 */
	public function getDeliveryFileHref()
	{
		return $this->getHref() . rawurlencode($this->image);
	}

	/**
	 * Get delivery path
	 * @return string
	 */
	public function getPath()
	{
		return $this->Shop->getPath() . '/types_of_delivery/';
	}

	/**
	 * Get delivery href
	 * @return string
	 */
	public function getHref()
	{
		return '/' . $this->Shop->getHref() . '/types_of_delivery/';
	}

	/**
	 * Delete delivery image
	 */
	public function deleteImage()
	{
		try
		{
			Core_File::delete($this->getDeliveryFilePath());
		} catch (Exception $e) {}

		$this->image = '';
		$this->save();
	}

	/**
	 * Create directory for delivery files
	 * @return self
	 */
	public function createDir()
	{
		if (!is_dir($this->getPath()))
		{
			try
			{
				Core_File::mkdir($this->getPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Set image size
	 * @return self
	 */
	public function setImageSizes()
	{
		$path = $this->getDeliveryFilePath();

		if (is_file($path))
		{
			$aSizes = Core_Image::instance()->getImageSize($path);
			if ($aSizes)
			{
				$this->image_width = $aSizes['width'];
				$this->image_height = $aSizes['height'];
				$this->save();
			}
		}

		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$limit = 100;
		$offset = 0;

		$newObject = parent::copy();

		try
		{
			if (is_file($this->getDeliveryFilePath()))
			{
				Core_File::copy($this->getDeliveryFilePath(), $newObject->getDeliveryFilePath());
			}
		}
		catch (Exception $e) {}

		try
		{
			if (is_file($this->getHandlerFilePath()))
			{
				$sReplace = str_replace("Shop_Delivery_Handler" . $this->id, "Shop_Delivery_Handler" . $newObject->id, $this->loadHandlerFile());

				$this->saveHandlerFile($sReplace);

				Core_File::copy($this->getHandlerFilePath(), $newObject->getHandlerFilePath());
			}
		}
		catch (Exception $e) {}

		do {
			$oShop_Delivery_Conditions = $this->Shop_Delivery_Conditions;
			$oShop_Delivery_Conditions->queryBuilder()->offset($offset)->limit($limit);
			$aShop_Delivery_Conditions = $oShop_Delivery_Conditions->findAll(FALSE);

			foreach($aShop_Delivery_Conditions as $oShop_Delivery_Condition)
			{
				$oNew_Shop_Delivery_Condition = $oShop_Delivery_Condition->copy();
				$newObject->add($oNew_Shop_Delivery_Condition);
			}

			$offset += $limit;
		}
		while (count($aShop_Delivery_Conditions));

		return $newObject;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_delivery.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));
		
		$this->deleteImage();

		// Удаляем обработчик
		if(is_file($this->getHandlerFilePath()))
		{
			try
			{
				Core_File::delete($this->getHandlerFilePath());
			} catch (Exception $e) {}
		}

		$this->Shop_Delivery_Conditions->deleteAll(FALSE);
		$this->Shop_Delivery_Payment_Systems->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get path to handler file
	 * @return string
	 */
	public function getHandlerFilePath()
	{
		return CMS_FOLDER . "hostcmsfiles/shop/delivery/handler" . intval($this->id) . '.php';
	}

	/**
	 * Load content of handler
	 * @return mixed
	 */
	public function loadHandlerFile()
	{
		$path = $this->getHandlerFilePath();
		return is_file($path) ? Core_File::read($path) : NULL;
	}

	/**
	 * Save content of handler
	 * @param string $content content
	 * @return self
	 */
	public function saveHandlerFile($content)
	{
		$this->save();

		$sFilePath = $this->getHandlerFilePath();
		Core_File::mkdir(dirname($sFilePath), CHMOD, TRUE);
		Core_File::write($sFilePath, trim($content));

		return $this;
	}

	/**
	 * Change status
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_delivery.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getHref());

		return parent::getXml();
	}
}