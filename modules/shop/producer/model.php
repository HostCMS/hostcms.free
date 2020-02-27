<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Producer_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Producer_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_item' => array(),
		'shop_filter_seo' => array(),
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
	 * Get producer path
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
		return $this->getProducerHref() . rawurlencode($this->image_small);
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
		return $this->getProducerHref() . rawurlencode($this->image_large);
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

				$this->path = Core::$mainConfig['translate'] && strlen($sTranslated)
					? $sTranslated
					: $this->name;

				$this->path = Core_Str::transliteration($this->path);

			} catch (Exception $e) {
				$this->path = Core_Str::transliteration($this->name);
			}
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
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		is_null($this->path) && $this->makePath();

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
		if (!is_dir($this->getProducerPath()))
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
	 * Delete producer's small image
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
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			Core_File::copy($this->getLargeFilePath(), $newObject->getLargeFilePath());
		} catch (Exception $e) {}

		try
		{
			Core_File::copy($this->getSmallFilePath(), $newObject->getSmallFilePath());
		} catch (Exception $e) {}

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
		$this->save();
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
			->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getProducerHref());

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

		return parent::delete($primaryKey);
	}
}