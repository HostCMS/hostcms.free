<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Payment_System_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Payment_System_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_delivery_payment_system' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_currency' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'active' => 1,
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_payment_systems.sorting' => 'ASC'
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Get the path to the payment system
	 * @return string
	 */
	public function getPaymentSystemFilePath()
	{
		return CMS_FOLDER . "hostcmsfiles/shop/pay/handler" . intval($this->id) . ".php";
	}

	/**
	 * Get content of the payment system file
	 * @return string|NULL
	 */
	public function loadPaymentSystemFile()
	{
		$path = $this->getPaymentSystemFilePath();
		return is_file($path) ? Core_File::read($path) : NULL;
	}

	/**
	 * Specify content of the payment system file
	 * @param string $content content
	 * @return self
	 */
	public function savePaymentSystemFile($content)
	{
		$this->save();

		$sPaymentSystemFilePath = $this->getPaymentSystemFilePath();
		Core_File::mkdir(dirname($sPaymentSystemFilePath), CHMOD, TRUE);
		Core_File::write($sPaymentSystemFilePath, trim($content));

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_payment_system.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		try
		{
			Core_File::delete($this->getPaymentSystemFilePath());
		} catch (Exception $e) {}

		$this->Shop_Delivery_Payment_Systems->deleteAll(FALSE);

		// Удаляем файл изображения
		$this->deleteImage();

		return parent::delete($primaryKey);
	}

	/**
	 * Change status of activity for payment system
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Get the path to the payment system`s image
	 * @return string
	 */
	public function getPaymentSystemImageFilePath()
	{
		return $this->getPath() . $this->image;
	}

	/**
	 * Get delivery file href
	 * @return string
	 */
	public function getPaymentSystemImageFileHref()
	{
		return $this->getHref() . rawurlencode($this->image);
	}

	/**
	 * Get delivery path
	 * @return string
	 */
	public function getPath()
	{
		return $this->Shop->getPath() . '/payments/';
	}

	/**
	 * Get delivery href
	 * @return string
	 */
	public function getHref()
	{
		return '/' . $this->Shop->getHref() . '/payments/';
	}

	/**
	 * Delete delivery image
	 */
	public function deleteImage()
	{
		try
		{
			Core_File::delete($this->getPaymentSystemImageFilePath());
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
		$path = $this->getPaymentSystemImageFilePath();

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
		$newObject = parent::copy();

		try
		{
			Core_File::copy($this->getPaymentSystemFilePath(), $newObject->getPaymentSystemFilePath());
		} catch (Exception $e) {}

		return $newObject;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_payment_system.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getHref());

		return parent::getXml();
	}
}