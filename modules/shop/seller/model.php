<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Seller_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Seller_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'siteuser_id' => 0,
		'sorting' => 0
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'siteuser' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_sellers.sorting' => 'ASC',
		'shop_sellers.name' => 'ASC',
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'description',
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
	 * Get path for files
	 * @return string
	 */
	public function getPath()
	{
		return 'sellers/' . rawurlencode($this->path) . '/';
	}

	/**
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event shop_seller.onBeforeIndexing
	 * @hostcms-event shop_seller.onAfterIndexing
	 */
	public function indexing()
	{
		$oSearch_Page = new stdClass();

		Core_Event::notify($this->_modelName . '.onBeforeIndexing', $this, array($oSearch_Page));

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		$oSearch_Page->text = htmlspecialchars($this->name) . ' ' . $this->description . ' ' . htmlspecialchars($this->address) . ' ' . htmlspecialchars($this->phone) . ' ' . htmlspecialchars($this->fax) . ' ' . htmlspecialchars($this->path);

		$oSearch_Page->title = $this->name;

		if (Core::moduleIsActive('field'))
		{
			$aField_Values = Field_Controller_Value::getFieldsValues($this->getFieldIDs(), $this->id);
			foreach ($aField_Values as $oField_Value)
			{
				// List
				if ($oField_Value->Field->type == 3 && Core::moduleIsActive('list'))
				{
					if ($oField_Value->value != 0)
					{
						$oList_Item = $oField_Value->List_Item;
						$oList_Item->id && $oSearch_Page->text .= htmlspecialchars($oList_Item->value) . ' ' . htmlspecialchars($oList_Item->description) . ' ';
					}
				}
				// Informationsystem
				elseif ($oField_Value->Field->type == 5 && Core::moduleIsActive('informationsystem'))
				{
					if ($oField_Value->value != 0)
					{
						$oInformationsystem_Item = $oField_Value->Informationsystem_Item;
						if ($oInformationsystem_Item->id)
						{
							$oSearch_Page->text .= htmlspecialchars($oInformationsystem_Item->name) . ' ' . $oInformationsystem_Item->description . ' ' . $oInformationsystem_Item->text . ' ';
						}
					}
				}
				// Shop
				elseif ($oField_Value->Field->type == 12 && Core::moduleIsActive('shop'))
				{
					if ($oField_Value->value != 0)
					{
						$oShop_Item = $oField_Value->Shop_Item;
						if ($oShop_Item->id)
						{
							$oSearch_Page->text .= htmlspecialchars($oShop_Item->name) . ' ' . $oShop_Item->description . ' ' . $oShop_Item->text . ' ';
						}
					}
				}
				// Wysiwyg
				elseif ($oField_Value->Field->type == 6)
				{
					$oSearch_Page->text .= htmlspecialchars(strip_tags($oField_Value->value)) . ' ';
				}
				// Other type
				elseif ($oField_Value->Field->type != 2)
				{
					$oSearch_Page->text .= htmlspecialchars($oField_Value->value) . ' ';
				}
			}
		}

		$oSiteAlias = $this->Shop->Site->getCurrentAlias();
		if ($oSiteAlias)
		{
			$oSearch_Page->url = ($this->Shop->Structure->https ? 'https://' : 'http://')
				. $oSiteAlias->name
				. $this->Shop->Structure->getPath()
				. $this->getPath();
		}
		else
		{
			return NULL;
		}

		$oSearch_Page->size = mb_strlen($oSearch_Page->text);
		$oSearch_Page->site_id = $this->Shop->site_id;
		$oSearch_Page->datetime = date('Y-m-d H:i:s');
		$oSearch_Page->module = 3;
		$oSearch_Page->module_id = $this->shop_id;
		$oSearch_Page->inner = 0;
		$oSearch_Page->module_value_type = 3; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id

		$oSearch_Page->siteuser_groups = array(intval($this->Shop->siteuser_group_id));

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		//$oSearch_Page->save();

		return $oSearch_Page;
	}

	/**
	 * Get seller path
	 * @return string
	 */
	public function getSellerPath()
	{
		return $this->Shop->getPath()	. '/sellers/';
	}

	/**
	 * Get seller href
	 * @return string
	 */
	public function getSellerHref()
	{
		return '/' . $this->Shop->getHref() . '/sellers/';
	}

	/**
	 * Get seller small file path
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return $this->getSellerPath() . $this->image_small;
	}

	/**
	 * Get seller small file href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		return $this->getSellerHref() . rawurlencode($this->image_small);
	}

	/**
	 * Get seller large file href
	 * @return string
	 */
	public function getLargeFilePath()
	{
		return $this->getSellerPath() . $this->image_large;
	}

	/**
	 * Get seller large file href
	 * @return string
	 */
	public function getLargeFileHref()
	{
		return $this->getSellerHref() . rawurlencode($this->image_large);
	}

	/**
	 * Set large image sizes
	 * @return self
	 */
	public function setLargeImageSizes()
	{
		$path = $this->getLargeFilePath();

		if (Core_File::isFile($path))
		{
			$aSizes = Core_Image::instance()->getImageSize($path);
			if ($aSizes)
			{
				$this->image_large_width = $aSizes['width'];
				$this->image_large_height = $aSizes['height'];
				$this->save();
			}
		}

		return $this;
	}

	/**
	 * Specify large image for seller
	 * @param string $fileSourcePath source file
	 * @param string $fileName target file name
	 * @return self
	 */
	public function saveLargeImageFile($fileSourcePath, $fileName)
	{
		$fileName = Core_File::filenameCorrection($fileName);
		$this->createDir();

		$this->image_large = $fileName;
		$this->save();
		Core_File::upload($fileSourcePath, $this->getSellerPath() . $fileName);
		$this->setLargeImageSizes();
		return $this;
	}

	/**
	 * Set small image sizes
	 * @return self
	 */
	public function setSmallImageSizes()
	{
		$path = $this->getSmallFilePath();

		if (Core_File::isFile($path))
		{
			$aSizes = Core_Image::instance()->getImageSize($path);
			if ($aSizes)
			{
				$this->image_small_width = $aSizes['width'];
				$this->image_small_height = $aSizes['height'];
				$this->save();
			}
		}

		return $this;
	}

	/**
	 * Specify small image for seller
	 * @param string $fileSourcePath source file
	 * @param string $fileName target file name
	 * @return self
	 */
	public function saveSmallImageFile($fileSourcePath, $fileName)
	{
		$fileName = Core_File::filenameCorrection($fileName);
		$this->createDir();

		$this->image_small = $fileName;
		$this->save();
		Core_File::upload($fileSourcePath, $this->getSellerPath() . $fileName);
		$this->setSmallImageSizes();
		return $this;
	}

	/**
	 * Create directory for seller
	 * @return self
	 */
	public function createDir()
	{
		if (!Core_File::isDir($this->getSellerPath()))
		{
			try
			{
				Core_File::mkdir($this->getSellerPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete seller's large image
	 */
	public function deleteLargeImage()
	{
		try
		{
			Core_File::delete($this->getLargeFilePath());
		} catch (Exception $e) {}

		$this->image_large = '';
		$this->save();
	}

	/**
	 * Delete seller's small image
	 * @return self
	 */
	public function deleteSmallImage()
	{
		try
		{
			Core_File::delete($this->getSmallFilePath());
		} catch (Exception $e) {}

		$this->image_small = '';
		$this->save();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_seller.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			Core_File::copy($this->getLargeFilePath(), $newObject->getLargeFilePath());
		}
		catch (Exception $e) {}

		try
		{
			Core_File::copy($this->getSmallFilePath(), $newObject->getSmallFilePath());
		}
		catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Switch default status
	 * @return self
	 */
	public function changeDefaultStatus()
	{
		$this->save();

		$oShop_Sellers = $this->Shop->Shop_Sellers;
		$oShop_Sellers
			->queryBuilder()
			->where('shop_sellers.default', '=', 1);

		$aShop_Sellers = $oShop_Sellers->findAll();

		foreach ($aShop_Sellers as $oShop_Seller)
		{
			$oShop_Seller->default = 0;
			$oShop_Seller->update();
		}

		$this->default = 1;
		return $this->save();
	}

	/**
	 * Get default seller
	 * @param boolean $bCache cache mode
	 * @return Shop_Seller_Model|NULL
	 */
	public function getDefault($bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_sellers.default', '=', 1)
			->limit(1);

		$aShop_Sellers = $this->findAll($bCache);

		return isset($aShop_Sellers[0])
			? $aShop_Sellers[0]
			: NULL;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_seller.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_seller.onBeforeRedeclaredGetStdObject
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
		$this->clearXmlTags()
			->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getSellerHref());

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_seller.onBeforeGetRelatedSite
	 * @hostcms-event shop_seller.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}