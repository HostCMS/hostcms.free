<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Payment_System_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Payment_System_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

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
		'shop_order_status' => array(),
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		switch ($this->type)
		{
			case 0:
			default:
				$color = 'palegreen';
			break;
			case 1:
				$color = 'azure';
			break;
			case 2:
				$color = 'warning';
			break;
			case 3:
				$color = 'pink';
			break;
		}

		$name = Core::_('Shop_Payment_System.type' . $this->type);

		Core_Html_Entity::factory('Span')
			->class("badge badge-square badge-{$color}")
			->title($name)
			->value($name)
			->execute();
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'shop_currency_id' => $this->shop_currency_id,
				'shop_id' => $this->shop_id,
				'name' => $this->name,
				'sorting' => $this->sorting,
				'description' => $this->description,
				'active' => $this->active,
				'type' => $this->type,
				'handler' => $this->loadPaymentSystemFile()
			);

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->shop_currency_id = Core_Array::get($aBackup, 'shop_currency_id');
				$this->shop_id = Core_Array::get($aBackup, 'shop_id');
				$this->name = Core_Array::get($aBackup, 'name');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->type = Core_Array::get($aBackup, 'type');
				$this->save();

				$this->savePaymentSystemFile(Core_Array::get($aBackup, 'handler'));
			}
		}

		return $this;
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
		return Core_File::isFile($path) ? Core_File::read($path) : NULL;
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
		if (!Core_File::isDir($this->getPath()))
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

		if (Core_File::isFile($path))
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
	 * Specify large image for group
	 * @param string $fileSourcePath source file
	 * @param string $fileName target file name
	 * @return self
	 */
	public function saveImageFile($fileSourcePath, $fileName)
	{
		$fileName = Core_File::filenameCorrection($fileName);

		// Определяем расширение файла
		$ext = Core_File::getExtension($fileName);

		$image = 'shop_payment' . $this->id . '.' . ($ext == '' ? '' : $ext);

		$this->createDir();

		$this->image = $image;
		$this->save();
		Core_File::upload($fileSourcePath, $this->getPath() . $image);
		return $this;
	}


	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_payment_system.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			if (Core_File::isFile($this->getPaymentSystemImageFilePath()))
			{
				$newObject->saveImageFile($this->getPaymentSystemImageFilePath(), $this->image);
			}
		}
		catch (Exception $e) {}

		try
		{
			if (Core_File::isFile($this->getPaymentSystemFilePath()))
			{
				$content = str_replace("Shop_Payment_System_Handler" . $this->id, "Shop_Payment_System_Handler" . $newObject->id, $this->loadPaymentSystemFile());

				$newObject->savePaymentSystemFile($content);
			}
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

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

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event shop_payment_system.onBeforeRedeclaredGetStdObject
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
		$this->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getHref());

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_payment_system.onBeforeGetRelatedSite
	 * @hostcms-event shop_payment_system.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}