<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Producer_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Producer_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $discounts = 1;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item' => array(),
		'shop_filter_seo' => array(),
		'shop_tab' => array('through' => 'shop_tab_producer'),
		'shop_tab_producer' => array(),
		'shop_bonus' => array('through' => 'shop_producer_bonus'),
		'shop_discount' => array('through' => 'shop_producer_discount'),
		'shop_producer_discount' => array(),
		'shop_producer_bonus' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'active' => 1
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_producer_dir' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_producers.sorting' => 'ASC',
		'shop_producers.name' => 'ASC'
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
	 * Get path
	 * @return string
	 */
	public function getPath()
	{
		return $this->Shop->Producer_Structure->getPath() . rawurlencode((string) $this->path) . '/';
	}

	/**
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event shop_producer.onBeforeIndexing
	 * @hostcms-event shop_producer.onAfterIndexing
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

		$oSearch_Page->text = htmlspecialchars($this->name) . ' ' . $this->description . ' ' . htmlspecialchars($this->address) . ' ' . htmlspecialchars($this->phone) . ' ' . htmlspecialchars($this->fax) . ' ' . htmlspecialchars($this->site) . ' ' . htmlspecialchars($this->path);

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
		$oSearch_Page->module_value_type = 4; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id

		$oSearch_Page->siteuser_groups = array(intval($this->Shop->siteuser_group_id));

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		return $oSearch_Page;
	}

	/**
	 * Add item into search index
	 * @return self
	 */
	public function index()
	{
		if (Core::moduleIsActive('search') && $this->active)
		{
			Search_Controller::indexingSearchPages(array($this->indexing()));
		}

		return $this;
	}

	/**
	 * Remove item from search index
	 * @return self
	 */
	public function unindex()
	{
		if (Core::moduleIsActive('search'))
		{
			Search_Controller::deleteSearchPage($this->Shop->site_id, 3, 4, $this->id);
		}

		return $this;
	}

	/**
	 * Get producer file path
	 * @return string
	 */
	public function getProducerPath()
	{
		return $this->Shop->getPath() . '/producers/';
	}

	/**
	 * Change item status
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		return $this->save();
	}

	/**
	 * Switch indexing mode
	 * @return self
	 */
	public function changeIndexing()
	{
		$this->indexing = 1 - $this->indexing;
		$this->save();

		if ($this->indexing && Core::moduleIsActive('search'))
		{
			Search_Controller::indexingSearchPages(array(
				$this->indexing()
			));
		}

		return $this;
	}

	/**
	 * Get producer href
	 * @return string
	 */
	public function getProducerHref()
	{
		return '/' . $this->Shop->getHref() . '/producers/';
	}

	/**
	 * Get the path to the small image of the producer
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return $this->getProducerPath() . $this->image_small;
	}

	/**
	 * Get producer small file href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		$link = Core::moduleIsActive('cdn')
			? Cdn_Controller::link($this->getProducerHref() . $this->image_small)
			: NULL;

		return !is_null($link)
			? $link
			: $this->getProducerHref() . rawurlencode($this->image_small);
	}

	/**
	 * Get the path to the large image of the producer
	 * @return string
	 */
	public function getLargeFilePath()
	{
		return $this->getProducerPath() . $this->image_large;
	}

	/**
	 * Get producer large file href
	 * @return string
	 */
	public function getLargeFileHref()
	{
		$link = Core::moduleIsActive('cdn')
			? Cdn_Controller::link($this->getProducerHref() . $this->image_large)
			: NULL;

		return !is_null($link)
			? $link
			: $this->getProducerHref() . rawurlencode($this->image_large);
	}

	/**
	 * Specify large image for producer
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
		Core_File::upload($fileSourcePath, $this->getProducerPath() . $fileName);

		return $this;
	}

	/**
	 * Make url path
	 */
	public function makePath()
	{
		if ($this->Shop->url_type == 1)
		{
			try {
				Core::$mainConfig['translate'] && $sTranslated = Core_Str::translate($this->name);

				$this->path = Core::$mainConfig['translate'] && strlen((string) $sTranslated)
					? $sTranslated
					: $this->name;

				$this->path = Core_Str::transliteration($this->path);

			} catch (Exception $e) {
				$this->path = Core_Str::transliteration($this->name);
			}

			$this->checkDuplicatePath();
		}
		elseif ($this->id)
		{
			$this->path = $this->id;
		}
		else
		{
			$this->path = Core_Guid::get();
		}

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function imgBackend()
	{
		if (strlen($this->image_small) || strlen($this->image_large))
		{
			$srcImg = htmlspecialchars(strlen($this->image_small)
				? $this->getSmallFileHref()
				: $this->getLargeFileHref()
			);

			$dataContent = '<img class="backend-preview" src="' . $srcImg . '" />';

			return '<img data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="' . htmlspecialchars($dataContent) . '" class="backend-thumbnail" src="' . $srcImg . '" />';
		}
		else
		{
			return '<i class="fa-regular fa-image"></i>';
		}
	}

	/**
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		if (is_null($this->path) || $this->path === '')
		{
			$this->makePath();
		}
		elseif (in_array('path', $this->_changedColumns))
		{
			$this->checkDuplicatePath();
		}

		parent::save();

		if ($this->path == '' && !$this->deleted && $this->makePath())
		{
			$this->path != '' && $this->save();
		}

		return $this;
	}

	/**
	 * Specify small image for producer
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
		Core_File::upload($fileSourcePath, $this->getProducerPath() . $fileName);

		return $this;
	}

	/**
	 * Create directory for producer
	 * @return self
	 */
	public function createDir()
	{
		if (!Core_File::isDir($this->getProducerPath()))
		{
			try
			{
				Core_File::mkdir($this->getProducerPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete producer's large image
	 * @return self
	 * @hostcms-event shop_producer.onAfterDeleteLargeImage
	 */
	public function deleteLargeImage()
	{
		try
		{
			Core_File::delete($this->getLargeFilePath());
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteLargeImage', $this);

		$this->image_large = '';
		$this->save();

		return $this;
	}

	/**
	 * Delete producer's small image
	 * @return self
	 * @hostcms-event shop_producer.onAfterDeleteSmallImage
	 */
	public function deleteSmallImage()
	{
		try
		{
			Core_File::delete($this->getSmallFilePath());
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteSmallImage', $this);

		$this->image_small = '';
		$this->save();

		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_producer.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			if (Core_File::isFile($this->getLargeFilePath()))
			{
				$newObject->saveLargeImageFile($this->getLargeFilePath(), $this->image_large);
			}
		}
		catch (Exception $e) {}

		try
		{
			if (Core_File::isFile($this->getSmallFilePath()))
			{
				$newObject->saveSmallImageFile($this->getSmallFilePath(), $this->image_small);
			}
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

		$oShop_Producers = $this->Shop->Shop_Producers;
		$oShop_Producers
			->queryBuilder()
			->where('shop_producers.default', '=', 1);

		$aShop_Producers = $oShop_Producers->findAll();

		foreach ($aShop_Producers as $oShop_Producer)
		{
			$oShop_Producer->default = 0;
			$oShop_Producer->update();
		}

		$this->default = 1;
		$this->active = 1;
		return $this->save();
	}

	/**
	 * Get default producer
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getDefault($bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_producers.default', '=', 1)
			->limit(1);

		$aShop_Producers = $this->findAll($bCache);

		return isset($aShop_Producers[0])
			? $aShop_Producers[0]
			: NULL;
	}

	/**
	 * Move shop producer to another dir
	 * @param int $shop_producer_dir_id dir id
	 * @return self
	 */
	public function move($shop_producer_dir_id)
	{
		$this->shop_producer_dir_id = $shop_producer_dir_id;
		$this->checkDuplicatePath()->save();
		return $this;
	}

	/**
	 * Merge shop producers
	 * @param Shop_Producer_Model $oObject
	 * @return self
	 */
	public function merge(Shop_Producer_Model $oObject)
	{
		Core_QueryBuilder::update('shop_items')
			->set('shop_producer_id', $this->id)
			->where('shop_producer_id', '=', $oObject->id)
			->execute();

		Core_QueryBuilder::update('shop_filter_seos')
			->set('shop_producer_id', $this->id)
			->where('shop_producer_id', '=', $oObject->id)
			->execute();

		$oObject->markDeleted();

		return $this;
	}

	/**
	 * Check and correct duplicate path
	 * @return self
	 * @hostcms-event shop_producer.onAfterCheckDuplicatePath
	 */
	public function checkDuplicatePath()
	{
		if (strlen($this->path))
		{
			$oShop = $this->Shop;

			// Search the same item or group
			$oSameShopProducer = $oShop->Shop_Producers->getByPath($this->path);
			if (!is_null($oSameShopProducer) && $oSameShopProducer->id != $this->id)
			{
				$this->path = Core_Guid::get();
			}
		}
		else
		{
			$this->path = Core_Guid::get();
		}

		Core_Event::notify($this->_modelName . '.onAfterCheckDuplicatePath', $this);

		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_producer.onBeforeRedeclaredGetXml
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
	 * @hostcms-event shop_producer.onBeforeRedeclaredGetStdObject
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
			->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getProducerHref())
			->addXmlTag('url', $this->getPath());

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_producer.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Filter_Seos->deleteAll(FALSE);

		$this->Shop_Tab_Producers->deleteAll(FALSE);

		$this->Shop_Producer_Discounts->deleteAll(FALSE);
		$this->Shop_Producer_Bonuses->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function discountsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$countDiscount = $this->Shop_Producer_Discounts->getCountBySiteuser_id(0);
		$countBonuses = $this->Shop_Producer_Bonuses->getCount();

		$count = $countDiscount + $countBonuses;

		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-palegreen white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_producer.onBeforeGetRelatedSite
	 * @hostcms-event shop_producer.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}