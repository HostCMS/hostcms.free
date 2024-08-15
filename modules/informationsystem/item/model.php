<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Item_Model
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Informationsystem_Item_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'informationsystem_item';

	/**
	 * Callback property_id
	 * @var int
	 */
	public $reviews = 1;

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
		'informationsystem_item' => array('foreign_key' => 'shortcut_id'),
		'tag' => array('through' => 'tag_informationsystem_item'),
		'tag_informationsystem_item' => array(),
		'comment' => array('through' => 'comment_informationsystem_item'),
		'vote' => array('through' => 'vote_informationsystem_item'),
		'media_informationsystem_item' => array(),
		'media_item' => array('through' => 'media_informationsystem_item')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'informationsystem' => array(),
		'informationsystem_group' => array(),
		'informationsystem_item' => array('foreign_key' => 'shortcut_id'),
		'siteuser' => array(),
		'siteuser_group' => array(),
		'user' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'datetime',
		'start_datetime',
		'end_datetime'
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'description',
		'text'
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'indexing' => 1,
		'siteuser_id' => 0,
		'active' => 1,
		'showed' => 0,
		'shortcut_id' => 0,
		'siteuser_group_id' => -1,
		'start_datetime' => '0000-00-00 00:00:00',
		'end_datetime' => '0000-00-00 00:00:00',
		'image_large' => '',
		'image_small' => ''
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'informationsystem_items.sorting' => 'ASC',
		//'informationsystem_items.name' => 'ASC',
	);

	/**
	 * Has revisions
	 *
	 * @param boolean
	 */
	protected $_hasRevisions = TRUE;

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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['ip'] = Core::getClientIp();
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Inc items'count in group during creating item
	 * @var boolean
	 */
	protected $_incCountByCreate = TRUE;

	/**
	 * Inc items'count in group during creating item
	 * @param boolean $value
	 * @return self
	 */
	public function incCountByCreate($value = TRUE)
	{
		$this->_incCountByCreate = $value;

		return $this;
	}

	/**
	 * Apply tags for item
	 * @param string $sTags string of tags, separated by comma
	 * @return self
	 */
	public function applyTags($sTags)
	{
		$aTags = explode(',', $sTags);

		return $this->applyTagsArray($aTags);
	}

	/**
	 * Apply array tags for item
	 * @param array $aTags array of tags
	 * @return self
	 */
	public function applyTagsArray(array $aTags)
	{
		// Удаляем связь метками
		$this->Tag_Informationsystem_Items->deleteAll(FALSE);

		foreach ($aTags as $tag_name)
		{
			$tag_name = trim($tag_name);

			if ($tag_name != '')
			{
				$oTag = Core_Entity::factory('Tag')->getByName($tag_name, FALSE);

				if (is_null($oTag))
				{
					$oTag = Core_Entity::factory('Tag');
					$oTag->name = $oTag->path = $tag_name;
					$oTag->save();
				}
				$this->add($oTag);
			}
		}

		return $this;
	}

	/**
	 * Values of all properties of item
	 * @var array
	 */
	protected $_propertyValues = NULL;

	/**
	 * Values of all properties of item
	 * Значения всех свойств товара
	 * @param boolean $bCache cache mode status
	 * @param array $aPropertiesId array of properties' IDs
	 * @param boolean $bSorting sort results, default FALSE
	 * @return array Property_Value
	 */
	public function getPropertyValues($bCache = TRUE, $aPropertiesId = array(), $bSorting = FALSE)
	{
		if ($bCache && !is_null($this->_propertyValues))
		{
			return $this->_propertyValues;
		}

		if (!is_array($aPropertiesId) || !count($aPropertiesId))
		{
			$aProperties = Core_Entity::factory('Informationsystem_Item_Property_List', $this->informationsystem_id)
				->Properties
				->findAll();

			$aPropertiesId = array();
			foreach ($aProperties as $oProperty)
			{
				$aPropertiesId[] = $oProperty->id;
			}
		}

		$aProperty_Values = Property_Controller_Value::getPropertiesValues($aPropertiesId, $this->id, $bCache, $bSorting);

		// setHref()
		foreach ($aProperty_Values as $oProperty_Value)
		{
			$this->_preparePropertyValue($oProperty_Value);
		}

		$bCache && $this->_propertyValues = $aProperty_Values;

		return $aProperty_Values;
	}

	/**
	 * Prepare Property Value
	 * @param Property_Value_Model $oProperty_Value
	 */
	protected function _preparePropertyValue($oProperty_Value)
	{
		switch ($oProperty_Value->Property->type)
		{
			case 2:
				$oProperty_Value
					->setHref($this->getItemHref())
					->setDir($this->getItemPath());
			break;
			case 5: // Элемент информационной системы
			case 12: // Товар интернет-магазина
			case 13: // Группа информационной системы
			case 14: // Группа интернет-магазина
				$oProperty_Value->showXmlMedia($this->_showXmlMedia);
			break;
			case 8:
				$oProperty_Value->dateFormat($this->Informationsystem->format_date);
			break;
			case 9:
				$oProperty_Value->dateTimeFormat($this->Informationsystem->format_datetime);
			break;
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event informationsystem_item.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		// Удаляем значения доп. свойств
		$aPropertyValues = $this->getPropertyValues(FALSE);
		foreach ($aPropertyValues as $oPropertyValue)
		{
			$oPropertyValue->Property->type == 2 && $oPropertyValue->setDir($this->getItemPath());
			$oPropertyValue->delete();
		}

		if (Core::moduleIsActive('comment'))
		{
			// Удаляем комментарии
			$this->Comments->deleteAll(FALSE);
		}

		// Удаляем ярлыки
		$this->Informationsystem_Items->deleteAll(FALSE);

		if (Core::moduleIsActive('tag'))
		{
			// Удаляем теги
			$this->Tag_Informationsystem_Items->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('media'))
		{
			$this->Media_Informationsystem_Items->deleteAll(FALSE);
		}

		// Удаляем директорию информационного элемента
		$this->deleteDir();

		if (!is_null($this->Informationsystem_Group->id))
		{
			// Уменьшение количества элементов в группе
			$this->Informationsystem_Group->decCountItems();
		}

		// Remove from search index
		$this->unindex();

		return parent::delete($primaryKey);
	}

	/**
	 * Get item by group id
	 * @param int $group_id group id
	 * @return array
	 */
	public function getByGroupId($group_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('informationsystem_group_id', '=', $group_id)
			->where('shortcut_id', '=', 0);

		return $this->findAll();
	}

	/**
	 * Get item by group id and path
	 * @param int $group_id group id
	 * @param string $path path
	 * @return Informationsystem_Item|NULL
	 */
	public function getByGroupIdAndPath($group_id, $path)
	{
		$this->queryBuilder()
			//->clear()
			->where('informationsystem_items.path', 'LIKE', Core_DataBase::instance()->escapeLike($path))
			->where('informationsystem_items.informationsystem_group_id', '=', $group_id)
			->where('informationsystem_items.shortcut_id', '=', 0)
			->clearOrderBy()
			->limit(1);

		$aInformationsystem_Items = $this->findAll(FALSE);

		return isset($aInformationsystem_Items[0])
			? $aInformationsystem_Items[0]
			: NULL;
	}

	/**
	 * Move item to another group
	 * @param int $informationsystem_group_id group id
	 * @return self
	 * @hostcms-event informationsystem_item.onBeforeMove
	 */
	public function move($informationsystem_group_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($informationsystem_group_id));

		$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $informationsystem_group_id);

		if ($this->shortcut_id)
		{
			$oInformationsystem_Item = $oInformationsystem_Group->Informationsystem_Items->getByShortcut_id($this->shortcut_id);

			if (!is_null($oInformationsystem_Item))
			{
				return $this;
			}
		}

		$this->informationsystem_group_id && $this->Informationsystem_Group->decCountItems();

		$this->informationsystem_group_id = $informationsystem_group_id;

		$this->checkDuplicatePath()->save()->clearCache();

		$informationsystem_group_id && $oInformationsystem_Group->incCountItems();

		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event informationsystem_item.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->path = '';
		$newObject->showed = 0;
		$newObject->guid = Core_Guid::get();
		$newObject->save();

		// Существует файл большого изображения для оригинального элемента
		if (Core_File::isFile($this->getLargeFilePath()))
		{
			$newObject->saveLargeImageFile($this->getLargeFilePath(), $this->image_large);
		}

		// Существует файл малого изображения для оригинального элемента
		if (Core_File::isFile($this->getSmallFilePath()))
		{
			$newObject->saveSmallImageFile($this->getSmallFilePath(), $this->image_small);
		}

		$aPropertyValues = $this->getPropertyValues(FALSE);
		foreach ($aPropertyValues as $oPropertyValue)
		{
			$oNewPropertyValue = clone $oPropertyValue;
			$oNewPropertyValue->entity_id = $newObject->id;
			$oNewPropertyValue->save();

			if ($oNewPropertyValue->Property->type == 2)
			{
				$oPropertyValue->setDir($this->getItemPath());
				$oNewPropertyValue->setDir($newObject->getItemPath());

				if (Core_File::isFile($oPropertyValue->getLargeFilePath()))
				{
					try
					{
						Core_File::copy($oPropertyValue->getLargeFilePath(), $oNewPropertyValue->getLargeFilePath());
					} catch (Exception $e) {}
				}

				if (Core_File::isFile($oPropertyValue->getSmallFilePath()))
				{
					try
					{
						Core_File::copy($oPropertyValue->getSmallFilePath(), $oNewPropertyValue->getSmallFilePath());
					} catch (Exception $e) {}
				}
			}
		}

		if (Core::moduleIsActive('tag'))
		{
			$aTags = $this->Tags->findAll();
			foreach ($aTags as $oTag)
			{
				$newObject->add($oTag);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Get item path
	 * @return string
	 */
	public function getItemPath()
	{
		return $this->Informationsystem->getPath() . '/' . Core_File::getNestingDirPath($this->id, $this->Informationsystem->Site->nesting_level) . '/item_' . $this->id . '/';
	}

	/**
	 * Get href to the item dir
	 * @return string
	 */
	public function getItemHref()
	{
		return '/' . $this->Informationsystem->getHref() . '/' . Core_File::getNestingDirPath($this->id, $this->Informationsystem->Site->nesting_level) . '/item_' . $this->id . '/';
	}

	/**
	 * Get small file path
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return $this->getItemPath() . $this->image_small;
	}

	/**
	 * Get small file href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		return $this->getItemHref() . rawurlencode($this->image_small);
	}

	/**
	 * Get large file path
	 * @return string
	 */
	public function getLargeFilePath()
	{
		return $this->getItemPath() . $this->image_large;
	}

	/**
	 * Get large file href
	 * @return string
	 */
	public function getLargeFileHref()
	{
		return $this->getItemHref() . rawurlencode($this->image_large);
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
	 * Specify large image for item
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
		Core_File::upload($fileSourcePath, $this->getItemPath() . $fileName);
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
	 * Specify small image for item
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
		Core_File::upload($fileSourcePath, $this->getItemPath() . $fileName);
		$this->setSmallImageSizes();
		return $this;
	}

	/**
	 * Check and correct duplicate path
	 * @return self
	 * @hostcms-event informationsystem_item.onAfterCheckDuplicatePath
	 */
	public function checkDuplicatePath()
	{
		if (strlen($this->path))
		{
			$oInformationsystem = $this->InformationSystem;

			$oSameInformationsystemItem = $oInformationsystem->Informationsystem_Items->getByGroupIdAndPath($this->informationsystem_group_id, $this->path);
			if (!is_null($oSameInformationsystemItem) && $oSameInformationsystemItem->id != $this->id)
			{
				$this->path = Core_Guid::get();
			}

			$oSameInformationsystemGroup = $oInformationsystem->Informationsystem_Groups->getByParentIdAndPath($this->informationsystem_group_id, $this->path);
			if (!is_null($oSameInformationsystemGroup))
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
	 * Make url path
	 * @return self
	 */
	public function makePath()
	{
		if ($this->InformationSystem->url_type == 1)
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

		return $this;
	}

	/**
	 * Save object.
	 *
	 * @return self
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
	 * Create item's directory for files
	 * @return self
	 */
	public function createDir()
	{
		if (!Core_File::isDir($this->getItemPath()))
		{
			try
			{
				Core_File::mkdir($this->getItemPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete item's directory for files
	 * @return self
	 */
	public function deleteDir()
	{
		// Удаляем файл большого изображения элемента
		$this->deleteLargeImage();

		// Удаляем файл малого изображения элемента
		$this->deleteSmallImage();

		if (Core_File::isDir($this->getItemPath()))
		{
			try
			{
				Core_File::deleteDir($this->getItemPath());
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Delete item's large image
	 * @return self
	 */
	public function deleteLargeImage()
	{
		$fileName = $this->getLargeFilePath();
		if ($this->image_large != '' && Core_File::isFile($fileName))
		{
			try
			{
				Core_File::delete($fileName);
			} catch (Exception $e) {}

			$this->image_large = '';
			$this->save();
		}
		return $this;
	}

	/**
	 * Delete item's small image
	 * @return self
	 */
	public function deleteSmallImage()
	{
		$fileName = $this->getSmallFilePath();
		if ($this->image_small != '' && Core_File::isFile($fileName))
		{
			try
			{
				Core_File::delete($fileName);
			} catch (Exception $e) {}

			$this->image_small = '';
			$this->save();
		}
		return $this;
	}

	/**
	 * Change item status
	 * @return self
	 * @hostcms-event informationsystem_item.onBeforeChangeActive
	 * @hostcms-event informationsystem_item.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		$aItemShortcuts = $this->Informationsystem_Items->findAll();
		foreach ($aItemShortcuts as $oItemShortcut)
		{
			$oItemShortcut->active = 1 - $oItemShortcut->active;
			$oItemShortcut->save();
		}

		$this->active
			? $this->index()
			: $this->unindex();

		$this->clearCache();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Add item into search index
	 * @return self
	 */
	public function index()
	{
		if (Core::moduleIsActive('search')
			&& $this->indexing && $this->active)
		{
			$bStartDT = $this->start_datetime == '0000-00-00 00:00:00'
				|| Core_Date::sql2timestamp($this->start_datetime) <= time();

			$bEndDT = $this->end_datetime == '0000-00-00 00:00:00'
				|| Core_Date::sql2timestamp($this->end_datetime) > time();

			$bStartDT && $bEndDT
				&& Search_Controller::indexingSearchPages(array($this->indexing()));
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
			Search_Controller::deleteSearchPage($this->Informationsystem->site_id, 1, 2, $this->id);
		}

		return $this;
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		$this->clearCache();

		return parent::markDeleted();
	}

	/**
	 * Change indexation mode
	 *	@return self
	 */
	public function changeIndexation()
	{
		$this->indexing = 1 - $this->indexing;

		$this->active && $this->indexing
			? $this->index()
			: $this->unindex();

		return $this->save();
	}

	/**
	 * Create shortcut and move into group $group_id
	 * @param int $group_id group id
	 * @return Informationsystem_Item_Model Shortcut
	 */
	public function shortcut($group_id = NULL)
	{
		$oInformationsystem_ItemShortcut = Core_Entity::factory('Informationsystem_Item');

		$object = $this->shortcut_id
			? $this->Informationsystem_Item
			: $this;

		$oInformationsystem_ItemShortcut->informationsystem_id = $object->informationsystem_id;
		$oInformationsystem_ItemShortcut->shortcut_id = $object->id;
		$oInformationsystem_ItemShortcut->datetime = $object->datetime;
		$oInformationsystem_ItemShortcut->name = ''/*$object->name*/;
		$oInformationsystem_ItemShortcut->path = '';
		$oInformationsystem_ItemShortcut->indexing = 0;

		$oInformationsystem_ItemShortcut->informationsystem_group_id =
			is_null($group_id)
			? $object->informationsystem_group_id
			: $group_id;

		return $oInformationsystem_ItemShortcut->save()->clearCache();
	}

	/**
	 * Get path to item's files
	 * @return string
	 * @hostcms-event informationsystem_item.onBeforeGetPath
	 */
	public function getPath()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetPath', $this);

		$sPath = Core_Event::getLastReturn();

		if (is_null($sPath))
		{
			$sPath = ($this->path == ''
				? $this->id
				: rawurlencode($this->path)) . '/';

			if ($this->informationsystem_group_id)
			{
				$sPath = $this->Informationsystem_Group->getPath() . $sPath;
			}
		}

		return $sPath;
	}

	/**
	 * Get the ID of the user group
	 * @return int
	 */
	public function getSiteuserGroupId()
	{
		// как у родителя
		if ($this->siteuser_group_id == -1)
		{
			$result = $this->informationsystem_group_id
				? $this->Informationsystem_Group->getSiteuserGroupId()
				: $this->InformationSystem->siteuser_group_id;
		}
		else
		{
			$result = $this->siteuser_group_id;
		}

		return intval($result);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		$object = $this->shortcut_id
			? $this->Informationsystem_Item
			: $this;

		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div');

		if ($this->closed)
		{
			$oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('I')
					->class('fa fa-lock darkorange locked-item')
					->title(Core::_('Informationsystem_Item.closed'))
			);
		}

		$oCore_Html_Entity_Div->value(htmlspecialchars((string) $object->name));

		$bRightTime =
			($this->start_datetime == '0000-00-00 00:00:00' || time() > Core_Date::sql2timestamp($this->start_datetime))
			&& ($this->end_datetime == '0000-00-00 00:00:00' || time() < Core_Date::sql2timestamp($this->end_datetime));

		!$bRightTime && $oCore_Html_Entity_Div->class('wrongTime');

		// Зачеркнут в зависимости от статуса родительского инф. элемента или своего статуса
		if (!$object->active || !$this->active)
		{
			$oCore_Html_Entity_Div->class('inactive');
		}
		elseif ($bRightTime)
		{
			$oCurrentAlias = $object->Informationsystem->Site->getCurrentAlias();

			if ($oCurrentAlias)
			{
				$href = ($this->Informationsystem->Structure->https ? 'https://' : 'http://')
					. $oCurrentAlias->name
					. $object->Informationsystem->Structure->getPath()
					. $object->getPath();

				$oCore_Html_Entity_Div
					->add(
						Core_Html_Entity::factory('A')
							->href($href)
							->target('_blank')
							->add(
								Core_Html_Entity::factory('I')
									->class('fa fa-external-link')
							)
					);
			}
		}
		elseif (!$bRightTime)
		{
			$oCore_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('I')
						->class('fa fa-clock-o black')
				);
		}

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Search indexation
	 * @return Search_Page_Model
	 * @hostcms-event informationsystem_item.onBeforeIndexing
	 * @hostcms-event informationsystem_item.onAfterIndexing
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

		$oSearch_Page->text = $this->text . ' ' . $this->description . ' ' . htmlspecialchars((string) $this->name) . ' ' . $this->id . ' ' . htmlspecialchars((string) $this->seo_title) . ' ' . htmlspecialchars((string) $this->seo_description) . ' ' . htmlspecialchars((string) $this->seo_keywords) . ' ' . htmlspecialchars((string) $this->path) . ' ';

		$oSearch_Page->title = (string) $this->name;

		if (Core::moduleIsActive('comment'))
		{
			$aComments = $this->Comments->getAllByActive(1, FALSE);
			foreach ($aComments as $oComment)
			{
				$oSearch_Page->text .= htmlspecialchars((string)$oComment->author) . ' ' . $oComment->text . ' ';
			}
		}

		if (Core::moduleIsActive('tag'))
		{
			$aTags = $this->Tags->findAll(FALSE);
			foreach ($aTags as $oTag)
			{
				$oSearch_Page->text .= htmlspecialchars((string) $oTag->name) . ' ';
			}
		}

		$aPropertyValues = $this->getPropertyValues(FALSE);
		foreach ($aPropertyValues as $oPropertyValue)
		{
			if ($oPropertyValue->Property->indexing)
			{
				// List
				if ($oPropertyValue->Property->type == 3 && Core::moduleIsActive('list'))
				{
					if ($oPropertyValue->value != 0)
					{
						$oList_Item = $oPropertyValue->List_Item;
						$oList_Item->id && $oSearch_Page->text .= htmlspecialchars((string) $oList_Item->value) . ' ' . htmlspecialchars((string) $oList_Item->description) . ' ';
					}
				}
				// Informationsystem
				elseif ($oPropertyValue->Property->type == 5 && Core::moduleIsActive('informationsystem'))
				{
					if ($oPropertyValue->value != 0)
					{
						$oInformationsystem_Item = $oPropertyValue->Informationsystem_Item;
						if ($oInformationsystem_Item->id)
						{
							$oSearch_Page->text .= htmlspecialchars((string) $oInformationsystem_Item->name) . ' ' . $oInformationsystem_Item->description . ' ' . $oInformationsystem_Item->text . ' ';
						}
					}
				}
				// Shop
				elseif ($oPropertyValue->Property->type == 12 && Core::moduleIsActive('shop'))
				{
					if ($oPropertyValue->value != 0)
					{
						$oShop_Item = $oPropertyValue->Shop_Item;
						if ($oShop_Item->id)
						{
							$oSearch_Page->text .= htmlspecialchars((string) $oShop_Item->name) . ' ' . $oShop_Item->description . ' ' . $oShop_Item->text . ' ';
						}
					}
				}
				// Wysiwyg
				elseif ($oPropertyValue->Property->type == 6)
				{
					$oSearch_Page->text .= htmlspecialchars(strip_tags((string) $oPropertyValue->value)) . ' ';
				}
				// Other type
				elseif ($oPropertyValue->Property->type != 2)
				{
					$oSearch_Page->text .= htmlspecialchars((string) $oPropertyValue->value) . ' ';
				}
			}
		}

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
						$oList_Item->id && $oSearch_Page->text .= htmlspecialchars((string) $oList_Item->value) . ' ' . htmlspecialchars((string) $oList_Item->description) . ' ';
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
							$oSearch_Page->text .= htmlspecialchars((string) $oInformationsystem_Item->name) . ' ' . $oInformationsystem_Item->description . ' ' . $oInformationsystem_Item->text . ' ';
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
							$oSearch_Page->text .= htmlspecialchars((string) $oShop_Item->name) . ' ' . $oShop_Item->description . ' ' . $oShop_Item->text . ' ';
						}
					}
				}
				// Wysiwyg
				elseif ($oField_Value->Field->type == 6)
				{
					$oSearch_Page->text .= htmlspecialchars(strip_tags((string) $oField_Value->value)) . ' ';
				}
				// Other type
				elseif ($oField_Value->Field->type != 2)
				{
					$oSearch_Page->text .= htmlspecialchars((string) $oField_Value->value) . ' ';
				}
			}
		}

		$oSiteAlias = $this->Informationsystem->Site->getCurrentAlias();
		if ($oSiteAlias)
		{
			$oSearch_Page->url = ($this->Informationsystem->Structure->https ? 'https://' : 'http://')
				. $oSiteAlias->name
				. $this->Informationsystem->Structure->getPath()
				. $this->getPath();
		}
		else
		{
			return NULL;
		}

		$oSearch_Page->size = mb_strlen($oSearch_Page->text);
		$oSearch_Page->site_id = $this->Informationsystem->site_id;
		$oSearch_Page->datetime = !is_null($this->datetime) && $this->datetime != '0000-00-00 00:00:00'
			? $this->datetime
			: date('Y-m-d H:i:s');
		$oSearch_Page->module = 1;
		$oSearch_Page->module_id = $this->informationsystem_id;
		$oSearch_Page->inner = 0;
		$oSearch_Page->module_value_type = 2; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id
		$oSearch_Page->siteuser_groups = array($this->getSiteuserGroupId());

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		return $oSearch_Page;
	}

	/**
	 * Show comments data in XML
	 * @var boolean
	 */
	protected $_showXmlComments = FALSE;

	/**
	 * Add comments XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlComments($showXmlComments = TRUE)
	{
		$this->_showXmlComments = $showXmlComments;
		return $this;
	}

	/**
	 * Show comments rating data in XML
	 * @var boolean
	 */
	protected $_showXmlCommentsRating = FALSE;

	/**
	 * Add Comments Rating XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlCommentsRating($showXmlCommentsRating = TRUE)
	{
		$this->_showXmlCommentsRating = $showXmlCommentsRating;
		return $this;
	}

	/**
	 * What comments show in XML? (active|inactive|all)
	 * @var string
	 */
	protected $_commentsActivity = 'active';

	/**
	 * Set comments filter rule
	 * @param string $commentsActivity (active|inactive|all)
	 * @return self
	 */
	public function commentsActivity($commentsActivity = 'active')
	{
		$this->_commentsActivity = $commentsActivity;
		return $this;
	}

	/**
	 * Show tags data in XML
	 * @var boolean
	 */
	protected $_showXmlTags = FALSE;

	/**
	 * Add tags XML to item
	 * @param boolean $showXmlTags mode
	 * @return self
	 */
	public function showXmlTags($showXmlTags = TRUE)
	{
		$this->_showXmlTags = $showXmlTags;
		return $this;
	}

	/**
	 * Show user data in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuser = FALSE;

	/**
	 * Add site user XML to item
	 * @param boolean $showXmlSiteuser mode
	 * @return self
	 */
	public function showXmlSiteuser($showXmlSiteuser = TRUE)
	{
		$this->_showXmlSiteuser = $showXmlSiteuser;
		return $this;
	}

	/**
	 * Show votes in XML
	 * @var boolean
	 */
	protected $_showXmlVotes = FALSE;

	/**
	 * Add votes XML to item
	 * @param boolean $showXmlSiteuser mode
	 * @return self
	 */
	public function showXmlVotes($showXmlVotes = TRUE)
	{
		$this->_showXmlVotes = $showXmlVotes;
		return $this;
	}

	/**
	 * Show siteuser properties in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuserProperties = FALSE;

	/**
	 * Show siteuser properties in XML
	 * @param boolean $showXmlSiteuserProperties mode
	 * @return self
	 */
	public function showXmlSiteuserProperties($showXmlSiteuserProperties = TRUE)
	{
		$this->_showXmlSiteuserProperties = $showXmlSiteuserProperties;
		return $this;
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Sort properties values in XML
	 * @var mixed
	 */
	protected $_xmlSortPropertiesValues = TRUE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE, $xmlSortPropertiesValues = TRUE)
	{
		$this->_showXmlProperties = is_array($showXmlProperties)
			? array_combine($showXmlProperties, $showXmlProperties)
			: $showXmlProperties;

		$this->_xmlSortPropertiesValues = $xmlSortPropertiesValues;

		return $this;
	}

	/**
	 * Show media in XML
	 * @var boolean
	 */
	protected $_showXmlMedia = FALSE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlMedia($showXmlMedia = TRUE)
	{
		$this->_showXmlMedia = $showXmlMedia;

		return $this;
	}

	/**
	 * Show siteuser properties in XML
	 * @var boolean
	 */
	protected $_showXmlCommentProperties = FALSE;

	/**
	 * Show siteuser properties in XML
	 * @param boolean $showXmlCommentProperties mode
	 * @return self
	 */
	public function showXmlCommentProperties($showXmlCommentProperties = TRUE)
	{
		$this->_showXmlCommentProperties = is_array($showXmlCommentProperties)
			? array_combine($showXmlCommentProperties, $showXmlCommentProperties)
			: $showXmlCommentProperties;

		return $this;
	}

	/**
	 * Showing part of text in XML
	 * @var int
	 */
	protected $_showXmlPart = 1;

	/**
	 * Show part of text in XML
	 * @param int $showXmlPart
	 * @return self
	 */
	public function showXmlPart($showXmlPart = 1)
	{
		$this->_showXmlPart = $showXmlPart;
		return $this;
	}

	/**
	 * Array of comments, [parent_id] => array(comments)
	 * @var array
	 */
	protected $_aComments = array();

	/**
	 * Set array of comments for getXml()
	 * @param array $aComments
	 * @return self
	 */
	public function setComments(array $aComments)
	{
		$this->_aComments = $aComments;
		return $this;
	}

	public function getParts()
	{
		return explode('<!-- pagebreak -->', (string) $this->text);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event informationsystem_item.onBeforeRedeclaredGetXml
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
	 * @hostcms-event informationsystem_item.onBeforeRedeclaredGetStdObject
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
	 * @hostcms-event informationsystem_item.onBeforeSelectComments
	 */
	protected function _prepareData()
	{
		$oInformationsystem = $this->Informationsystem;

		$this->clearXmlTags();

		$this->_isTagAvailable('url')
			&& $this->addXmlTag('url', $this->Informationsystem->Structure->getPath() . $this->getPath());

		$this->_isTagAvailable('date')
			&& $this->addXmlTag('date', Core_Date::strftime($oInformationsystem->format_date, Core_Date::sql2timestamp($this->datetime)));

		/*$this->_isTagAvailable('datetime')
			&& */$this->addXmlTag('datetime', Core_Date::strftime($oInformationsystem->format_datetime, Core_Date::sql2timestamp($this->datetime)));

		/*$this->_isTagAvailable('start_datetime')
			&& */$this->addXmlTag('start_datetime', $this->start_datetime == '0000-00-00 00:00:00'
				? $this->start_datetime
				: Core_Date::strftime($oInformationsystem->format_datetime, Core_Date::sql2timestamp($this->start_datetime)));

		/*$this->_isTagAvailable('end_datetime')
			&& */$this->addXmlTag('end_datetime', $this->end_datetime == '0000-00-00 00:00:00'
				? $this->end_datetime
				: Core_Date::strftime($oInformationsystem->format_datetime, Core_Date::sql2timestamp($this->end_datetime)));

		$this->_isTagAvailable('dir')
			&& $this->addXmlTag('dir', Core_Page::instance()->informationsystemCDN . $this->getItemHref());

		// Отображается часть текста
		if ($this->_showXmlPart > 0 && $this->_isTagAvailable('text'))
		{
			$aParts = $this->getParts();
			$iPartsCount = count($aParts);

			if ($iPartsCount > 1)
			{
				$this->_showXmlPart > $iPartsCount && $this->_showXmlPart = $iPartsCount;

				if (Core::moduleIsActive('shortcode'))
				{
					$oShortcode_Controller = Shortcode_Controller::instance();
					$iCountShortcodes = $oShortcode_Controller->getCount();

					if ($iCountShortcodes)
					{
						$aParts[$this->_showXmlPart - 1] = $oShortcode_Controller->applyShortcodes($aParts[$this->_showXmlPart - 1]);
					}
				}

				$this->addForbiddenTag('text')
					->addXmlTag('parts_count', $iPartsCount)
					->addXmlTag('text', $aParts[$this->_showXmlPart - 1]);
			}

			unset($aParts);
		}

		if ($this->_showXmlVotes && Core::moduleIsActive('siteuser'))
		{
			$aRate = Vote_Controller::instance()->getRateByObject($this);

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('rate')
					->value($aRate['rate'])
					->addAttribute('likes', $aRate['likes'])
					->addAttribute('dislikes', $aRate['dislikes'])
			);

			if (!is_null($oCurrentSiteuser = Core_Entity::factory('Siteuser')->getCurrent()))
			{
				$oVote = $this->Votes->getBySiteuser_Id($oCurrentSiteuser->id);
				!is_null($oVote) && $this->addEntity($oVote);
			}
		}

		if ($this->_showXmlSiteuser && $this->siteuser_id && Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser->showXmlProperties($this->_showXmlSiteuserProperties, $this->_xmlSortPropertiesValues);
			$this->addEntity($this->Siteuser);
		}

		if ($this->_showXmlTags && Core::moduleIsActive('tag'))
		{
			$this->addEntities($this->Tags->findAll());
		}

		if (($this->_showXmlComments || $this->_showXmlCommentsRating) && Core::moduleIsActive('comment'))
		{
			$this->_aComments = array();

			$gradeSum = $gradeCount = 0;

			$oComments = $this->Comments;
			$oComments->queryBuilder()
				->orderBy('datetime', 'DESC');

			// учитываем заданную активность комментариев
			$this->_commentsActivity = strtolower($this->_commentsActivity);
			if ($this->_commentsActivity != 'all')
			{
				$oComments->queryBuilder()
					->where('active', '=', $this->_commentsActivity == 'inactive' ? 0 : 1);
			}

			Core_Event::notify($this->_modelName . '.onBeforeSelectComments', $this, array($oComments));

			$aComments = $oComments->findAll();
			foreach ($aComments as $oComment)
			{
				if ($oComment->grade > 0)
				{
					$gradeSum += $oComment->grade;
					$gradeCount++;
				}

				$this->_showXmlComments
					&& $this->_aComments[$oComment->parent_id][] = $oComment;
			}

			// Средняя оценка
			$avgGrade = $gradeCount > 0
				? $gradeSum / $gradeCount
				: 0;

			$fractionalPart = $avgGrade - floor($avgGrade);
			$avgGradeRounded = floor($avgGrade);

			if ($fractionalPart >= 0.25 && $fractionalPart < 0.75)
			{
				$avgGradeRounded += 0.5;
			}
			elseif ($fractionalPart >= 0.75)
			{
				$avgGradeRounded += 1;
			}

			$this->_isTagAvailable('comments_count') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_count')
					->value(count($aComments))
			);

			$this->_isTagAvailable('comments_grade_sum') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_grade_sum')
					->value($gradeSum)
			);

			$this->_isTagAvailable('comments_grade_count') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_grade_count')
					->value($gradeCount)
			);

			$this->_isTagAvailable('comments_average_grade') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_average_grade')
					->addAttribute('value', $avgGrade)
					->value($avgGradeRounded)
			);

			$this->_showXmlComments
				&& $this->_addComments(0, $this);

			$this->_aComments = array();
		}

		if ($this->_showXmlProperties)
		{
			if (is_array($this->_showXmlProperties))
			{
				$aProperty_Values = Property_Controller_Value::getPropertiesValues($this->_showXmlProperties, $this->id, FALSE, $this->_xmlSortPropertiesValues);

				foreach ($aProperty_Values as $oProperty_Value)
				{
					$this->_preparePropertyValue($oProperty_Value);
				}
			}
			else
			{
				$aProperty_Values = $this->getPropertyValues(TRUE, array(), $this->_xmlSortPropertiesValues);
				// Add all values
				//$this->addEntities($aProperty_Values);
			}

			$aListIDs = array();

			foreach ($aProperty_Values as $oProperty_Value)
			{
				// List_Items
				if ($oProperty_Value->Property->type == 3)
				{
					$aListIDs[] = $oProperty_Value->value;
				}

				$this->addEntity($oProperty_Value);
			}

			// Cache necessary List_Items
			if (count($aListIDs))
			{
				$oList_Items = Core_Entity::factory('List_Item');
				$oList_Items->queryBuilder()
					->where('id', 'IN', $aListIDs)
					->clearOrderBy();

				$oList_Items->findAll(TRUE);
			}
		}

		if ($this->_showXmlMedia && Core::moduleIsActive('media'))
		{
			$aEntities = Media_Item_Controller::getValues($this);
			foreach ($aEntities as $oEntity)
			{
				$oMedia_Item = $oEntity->Media_Item;
				$this->addEntity($oMedia_Item->setCDN(Core_Page::instance()->informationsystemCDN));
			}
		}

		return $this;
	}

	/**
	 * Add comments into object XML
	 * @param int $parent_id parent comment id
	 * @param Core_Entity $parentObject object
	 * @return self
	 * @hostcms-event informationsystem_item.onBeforeAddComments
	 * @hostcms-event informationsystem_item.onAfterAddComments
	 */
	protected function _addComments($parent_id, $parentObject)
	{
		Core_Event::notify($this->_modelName . '.onBeforeAddComments', $this, array(
			$parent_id, $parentObject, $this->_aComments
		));

		if (isset($this->_aComments[$parent_id]))
		{
			foreach ($this->_aComments[$parent_id] as $oComment)
			{
				$parentObject->addEntity(
					$oComment
						->clearEntities()
						->showXmlProperties($this->_showXmlCommentProperties, $this->_xmlSortPropertiesValues)
						->showXmlSiteuserProperties($this->_showXmlSiteuserProperties)
						->showXmlVotes($this->_showXmlVotes)
						->dateFormat($this->InformationSystem->format_date)
						->dateTimeFormat($this->InformationSystem->format_datetime)
				);

				$this->_addComments($oComment->id, $oComment);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterAddComments', $this, array(
			$parent_id, $parentObject, $this->_aComments
		));

		return $this;
	}

	/**
	 * Create item
	 * @return self
	 */
	public function create()
	{
		$return = parent::create();

		if ($this->_incCountByCreate && !is_null($this->Informationsystem_Group->id))
		{
			// Увеличение количества элементов в группе
			$this->Informationsystem_Group->incCountItems();
		}

		return $return;
	}

	/**
	 * Clear tagged cache
	 * @return self
	 */
	public function clearCache()
	{
		if (Core::moduleIsActive('cache'))
		{
			// Clear item's cache
			Core_Cache::instance(Core::$mainConfig['defaultCache'])
				->deleteByTag('informationsystem_item_' . $this->id);

			// Clear group's cache
			$this->informationsystem_group_id
				? $this->Informationsystem_Group->clearCache()
				: Core_Cache::instance(Core::$mainConfig['defaultCache'])
					->deleteByTag('informationsystem_group_0');

			// Static cache
			$oSite = $this->Informationsystem->Site;
			if ($oSite->html_cache_use)
			{
				$oSiteAlias = $oSite->getCurrentAlias();
				if ($oSiteAlias)
				{
					if ($this->informationsystem_group_id)
					{
						$url = $oSiteAlias->name
							. $this->Informationsystem->Structure->getPath()
							. $this->Informationsystem_Group->getPath();
					}
					else
					{
						$url = $oSiteAlias->name
							. $this->Informationsystem->Structure->getPath();
							//. $this->getPath();
					}

					$oCache_Static = Core_Cache::instance('static');
					$oCache_Static->delete($url);
				}
			}
		}

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function reviewsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('comment'))
		{
			$count = $this->Comments->getCount();
			$count && Core_Html_Entity::factory('Span')
				->class('badge badge-ico badge-azure white')
				->value($count < 100 ? $count : '∞')
				->title($count)
				->execute();
		}
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
				'name' => $this->name,
				'informationsystem_group_id' => $this->informationsystem_group_id,
				'datetime' => $this->datetime,
				'start_datetime' => $this->start_datetime,
				'end_datetime' => $this->end_datetime,
				'active' => $this->active,
				'indexing' => $this->indexing,
				'sorting' => $this->sorting,
				'ip' => $this->ip,
				'showed' => $this->showed,
				'siteuser_id' => $this->siteuser_id,
				'shortcut_id' => $this->shortcut_id,
				'path' => $this->path,
				'description' => $this->description,
				'text' => $this->text,
				'seo_title' => $this->seo_title,
				'seo_description' => $this->seo_description,
				'seo_keywords' => $this->seo_keywords,
				'siteuser_group_id' => $this->siteuser_group_id,
				'user_id' => $this->user_id
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
				$this->name = Core_Array::get($aBackup, 'name');
				$this->informationsystem_group_id = Core_Array::get($aBackup, 'informationsystem_group_id');
				$this->datetime = Core_Array::get($aBackup, 'datetime');
				$this->start_datetime = Core_Array::get($aBackup, 'start_datetime');
				$this->end_datetime = Core_Array::get($aBackup, 'end_datetime');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->indexing = Core_Array::get($aBackup, 'indexing');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->ip = Core_Array::get($aBackup, 'ip');
				$this->showed = Core_Array::get($aBackup, 'showed');
				$this->siteuser_id = Core_Array::get($aBackup, 'siteuser_id');
				$this->shortcut_id = Core_Array::get($aBackup, 'shortcut_id');
				$this->path = Core_Array::get($aBackup, 'path');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->text = Core_Array::get($aBackup, 'text');
				$this->seo_title = Core_Array::get($aBackup, 'seo_title');
				$this->seo_description = Core_Array::get($aBackup, 'seo_description');
				$this->seo_keywords = Core_Array::get($aBackup, 'seo_keywords');
				$this->siteuser_group_id = Core_Array::get($aBackup, 'siteuser_group_id');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->save();
			}
		}

		return $this;
	}

	/*public function __destruct()
	{
		echo "\nd";
	}*/

	/**
	 * Backend callback method
	 * @return string
	 */
	public function imgBackend()
	{
		if ($this->shortcut_id)
		{
			return '<i class="fa-solid fa-link"></i>';
		}
		elseif (strlen($this->image_small))
		{
			$srcImg = htmlspecialchars($this->getSmallFileHref());
			$dataContent = '<img class="backend-preview" src="' . $srcImg . '"/>';

			return '<img data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="' . htmlspecialchars($dataContent) . '" class="backend-thumbnail" src="' . $srcImg . '" />';
		}
		elseif (strlen($this->image_large))
		{
			$srcImg = htmlspecialchars($this->getLargeFileHref());
			$dataContent = '<img class="backend-preview" src="' . $srcImg . '" />';

			return '<img data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="' . htmlspecialchars($dataContent) . '" class="backend-thumbnail" src="' . $srcImg . '" />';
		}
		else
		{
			return '<i class="fa-regular fa-image"></i>';
		}
	}

	/**
	 * Get property value for SEO-templates
	 * @param int $property_id Property ID
	 * @param strint $format string format, e.g. '%s: %s'. %1$s - Property Name, %2$s - List of Values
	 * @param int $property_id Property ID
	 * @return string
	 */
	public function propertyValue($property_id, $format = '%2$s', $separator = ', ')
	{
		$oProperty = Core_Entity::factory('Property', $property_id);
		$aProperty_Values = $oProperty->getValues($this->id, FALSE);

		if (count($aProperty_Values))
		{
			$aTmp = array();

			foreach ($aProperty_Values as $oProperty_Value)
			{
				switch ($oProperty->type)
				{
					case 0: // Int
					case 1: // String
					case 4: // Textarea
					case 6: // Wysiwyg
					case 11: // Float
						$aTmp[] = $oProperty_Value->value;
					break;
					case 8: // Date
						$aTmp[] = Core_Date::strftime($this->Informationsystem->format_date, Core_Date::sql2timestamp($oProperty_Value->value));
					break;
					case 9: // Datetime
						$aTmp[] = Core_Date::strftime($this->Informationsystem->format_datetime, Core_Date::sql2timestamp($oProperty_Value->value));
					break;
					case 3: // List
						if ($oProperty_Value->value)
						{
							$oList_Item = $oProperty->List->List_Items->getById(
								$oProperty_Value->value, FALSE
							);

							!is_null($oList_Item) && $aTmp[] = $oList_Item->value;
						}
					break;
					case 7: // Checkbox
					break;
					case 5: // Informationsystem
						if ($oProperty_Value->value)
						{
							$aTmp[] = $oProperty_Value->Informationsystem_Item->name;
						}
					break;
					case 12: // Shop
						if ($oProperty_Value->value)
						{
							$aTmp[] = $oProperty_Value->Shop_Item->name;
						}
					break;
					case 2: // File
					case 10: // Hidden field
					default:
					break;
				}
			}

			if (count($aTmp))
			{
				return sprintf($format, $oProperty->name, implode($separator, $aTmp));
			}
		}

		return NULL;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event informationsystem_item.onBeforeGetRelatedSite
	 * @hostcms-event informationsystem_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Informationsystem->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}

	/**
	 * Check activity of item and parent groups
	 * @return bool
	 */
	public function isActive()
	{
		if (!$this->active)
		{
			return FALSE;
		}

		if ($this->informationsystem_group_id)
		{
			$oTmpGroup = $this->Informationsystem_Group;

			// Все директории от текущей до родителя.
			do {
				if (!$oTmpGroup->active)
				{
					return FALSE;
				}
			} while ($oTmpGroup = $oTmpGroup->getParent());
		}

		return TRUE;
	}
}