<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Group_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Group_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'shop_group';

	/**
	 * Backend property
	 * @var int
	 */
	public $img = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $related = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $modifications = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $discounts = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $type = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $reviews = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $status = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $count = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $key = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $adminPrice = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_group' => array('foreign_key' => 'parent_id'),
		'shop_item' => array(),
		'shop_item_property_for_group' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_group' => array('foreign_key' => 'parent_id'),
		'shop' => array(),
		'siteuser_group' => array(),
		'siteuser' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'active' => 1,
		'indexing' => 1
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'description'
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
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * Values of all properties of group
	 * @var array
	 */
	protected $_propertyValues = NULL;

	/**
	 * Values of all properties of element
	 * @param boolean $bCache cache mode
	 * @param array $aPropertiesId array of properties' IDs
	 * @return array Property_Value
	 */
	public function getPropertyValues($bCache = TRUE, $aPropertiesId = array())
	{
		$iMd5 = md5(serialize($aPropertiesId));

		if ($bCache && isset($this->_propertyValues[$iMd5]))
		{
			return $this->_propertyValues[$iMd5];
		}

		if (!is_array($aPropertiesId) || !count($aPropertiesId))
		{
			$aProperties = Core_Entity::factory('Shop_Group_Property_List', $this->shop_id)
				->Properties
				->findAll();

			$aPropertiesId = array();
			foreach ($aProperties as $oProperty)
			{
				$aPropertiesId[] = $oProperty->id;
			}
		}

		$aReturn = Property_Controller_Value::getPropertiesValues($aPropertiesId, $this->id, $bCache);

		// setHref()
		foreach ($aReturn as $oProperty_Value)
		{
			switch ($oProperty_Value->Property->type)
			{
				case 2:
					$oProperty_Value
						->setHref($this->getGroupHref())
						->setDir($this->getGroupPath());
				break;
				case 8:
					$oProperty_Value->dateFormat($this->Shop->format_date);
				break;
				case 9:
					$oProperty_Value->dateTimeFormat($this->Shop->format_datetime);
				break;
			}
		}

		$bCache && $this->_propertyValues[$iMd5] = $aReturn;

		return $aReturn;
	}

	/**
	 * Move group to another
	 * @param int $parent_id group id
	 * @return self
	 * @hostcms-event shop_group.onBeforeMove
	 */
	public function move($parent_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($parent_id));

		$this->parent_id = $parent_id;
		return $this->save();
	}

	/**
	 * Get parent
	 * @return Shop_Group_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Group', $this->parent_id)
			: NULL;
	}

	/**
	 * Get group by parent id
	 * @param int $parent_id parent id
	 * @param boolean $bCache cache mode
	 * @return array
	 */
	public function getByParentId($parent_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			->where('parent_id', '=', $parent_id);
		return $this->findAll($bCache);
	}

	/**
	 * Get group by parent group id and path
	 * @param int $parent_id parent group id
	 * @param string $path path
	 * @return Shop_Group|NULL
	 */
	public function getByParentIdAndPath($parent_id, $path)
	{
		$this->queryBuilder()
			//->clear()
			->where('path', 'LIKE', $path)
			->where('parent_id', '=', $parent_id)
			->clearOrderBy()
			->limit(1);

		$aShop_Groups = $this->findAll(FALSE);

		return isset($aShop_Groups[0])
			? $aShop_Groups[0]
			: NULL;
	}

	/**
	 * Get group path
	 * @return string
	 */
	public function getGroupPath()
	{
		return $this->Shop->getPath() . '/' . Core_File::getNestingDirPath($this->id, $this->Shop->Site->nesting_level) . '/group_' . $this->id . '/';
	}

	/**
	 * Get group href
	 * @return string
	 */
	public function getGroupHref()
	{
		return '/' . $this->Shop->getHref() . '/' . Core_File::getNestingDirPath($this->id, $this->Shop->Site->nesting_level) . '/group_' . $this->id . '/';
	}

	/**
	 * Create directory for group
	 * @return self
	 */
	public function createDir()
	{
		if (!is_dir($this->getGroupPath()))
		{
			try
			{
				Core_File::mkdir($this->getGroupPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}
		return $this;
	}

	/**
	 * Get the path to the small image of group
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return $this->getGroupPath() . $this->image_small;
	}

	/**
	 * Get the path to the small image href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		return $this->getGroupHref() . rawurlencode($this->image_small);
	}

	/**
	 * Get the path to the large image of group
	 * @return string
	 */
	public function getLargeFilePath()
	{
		return $this->getGroupPath() . $this->image_large;
	}

	/**
	 * Get the path to the large image href
	 * @return string
	 */
	public function getLargeFileHref()
	{
		return $this->getGroupHref() . rawurlencode($this->image_large);
	}

	/**
	 * Specify large image for group
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
		Core_File::upload($fileSourcePath, $this->getGroupPath() . $fileName);
		return $this;
	}

	/**
	 * Specify small image for group
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
		Core_File::upload($fileSourcePath, $this->getGroupPath() . $fileName);
		return $this;
	}

	/**
	 * Delete group's large image
	 */
	public function deleteLargeImage()
	{
		$fileName = $this->getLargeFilePath();
		if ($this->image_large != '' && is_file($fileName))
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
	 * Delete group's small image
	 * @return self
	 */
	public function deleteSmallImage()
	{
		$fileName = $this->getSmallFilePath();
		if ($this->image_small != '' && is_file($fileName))
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
	 * Set large image sizes
	 * @return self
	 */
	public function setLargeImageSizes()
	{
		$path = $this->getLargeFilePath();

		if (is_file($path))
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
	 * Set small image sizes
	 * @return self
	 */
	public function setSmallImageSizes()
	{
		$path = $this->getSmallFilePath();

		if (is_file($path))
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
	 * Change status of activity for group
	 * @return self
	 * @hostcms-event shop_group.onBeforeChangeActive
	 * @hostcms-event shop_group.onAfterChangeActive
	 */
	public function changeActive()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeActive', $this);

		$this->active = 1 - $this->active;
		$this->save();

		$this->index();

		$this->clearCache();

		Core_Event::notify($this->_modelName . '.onAfterChangeActive', $this);

		return $this;
	}

	/**
	 * Add group into search index
	 * @return self
	 */
	public function index()
	{
		if (Core::moduleIsActive('search') && $this->indexing && $this->active)
		{
			Search_Controller::indexingSearchPages(array($this->indexing()));
		}

		return $this;
	}

	/**
	 * Remove group from search index
	 * @return self
	 */
	public function unindex()
	{
		if (Core::moduleIsActive('search'))
		{
			Search_Controller::deleteSearchPage(3, 1, $this->id);
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
	 * Get group path
	 * @return string
	 * @hostcms-event shop_group.onBeforeGetPath
	 */
	public function getPath()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetPath', $this);

		$sPath = Core_Event::getLastReturn();

		if (is_null($sPath))
		{
			$sPath = rawurlencode($this->path) . '/';

			if (!is_null($oParentGroup = $this->getParent()))
			{
				$sPath = $oParentGroup->getPath() . $sPath;
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
			$result = $this->parent_id
				? $this->Shop_Group->getSiteuserGroupId()
				: $this->Shop->siteuser_group_id;
		}
		else
		{
			$result = $this->siteuser_group_id;
		}
		return intval($result);
	}

	/**
	 * Увеличение на 1 количества элементов в группе и во всех родительских группах
	 */
	public function incCountItems()
	{
		return $this->modifyCountItems(1);
	}
	/**
	 * Уменьшение на 1 количества элементов в группе и во всех родительских группах
	 */
	public function decCountItems()
	{
		return $this->modifyCountItems(-1);
	}

	/**
	 * Modify count of items in group
	 * @param int $int value
	 * @param boolean $self change items_count
	 * @return self
	 */
	public function modifyCountItems($int = 1, $self = TRUE)
	{
		$self && $this->items_count += $int;
		$this->items_total_count += $int;
		$this->save();

		if ($this->parent_id != 0 /*&& $this->parent_id != $this->id*/ && !is_null($this->Shop_Group->id))
		{
			$this->Shop_Group->modifyCountItems($int, FALSE);
		}
		return $this;
	}

	/**
	 * Увеличение на 1 количества подгрупп в группе и во всех родительских группах
	 */
	public function incCountGroups()
	{
		return $this->modifyCountGroups(1);
	}

	/**
	 * Уменьшение на 1 количества подгрупп в группе и во всех родительских группах
	 */
	public function decCountGroups()
	{
		return $this->modifyCountGroups(-1);
	}

	/**
	 * Change count of groups on $int
	 * @param int $int
	 * @return self
	 */
	public function modifyCountGroups($int = 1)
	{
		$this->subgroups_count += $int;
		$this->subgroups_total_count += $int;
		$this->save();

		if ($this->parent_id != 0 && !is_null($this->Shop_Group->id))
		{
			$this->Shop_Group->modifyCountGroups($int);
		}
		return $this;
	}

	/**
	 * Insert new object data into database
	 * @return Core_ORM
	 */
	public function create()
	{
		$return = parent::create();

		if ($this->parent_id != 0)
		{
			// Увеличение количества элементов в группе
			$this->Shop_Group->incCountGroups();
		}
		return $return;
	}

	/**
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event shop_group.onBeforeIndexing
	 * @hostcms-event shop_group.onAfterIndexing
	 */
	public function indexing()
	{
		//$oSearch_Page = Core_Entity::factory('Search_Page');
		$oSearch_Page = new stdClass();

		Core_Event::notify($this->_modelName . '.onBeforeIndexing', $this, array($oSearch_Page));

		$oSearch_Page->text = htmlspecialchars($this->name) . ' ' . $this->description . ' ' . $this->id . ' ' . htmlspecialchars($this->seo_title) . ' ' . htmlspecialchars($this->seo_description) . ' ' . htmlspecialchars($this->seo_keywords) . ' ' . htmlspecialchars($this->path) . ' ';

		$oSearch_Page->title = $this->name;

		$aPropertyValues = $this->getPropertyValues();
		foreach ($aPropertyValues as $oPropertyValue)
		{
			// List
			if ($oPropertyValue->Property->type == 3 && Core::moduleIsActive('list'))
			{
				if ($oPropertyValue->value != 0)
				{
					$oList_Item = $oPropertyValue->List_Item;
					$oList_Item->id && $oSearch_Page->text .= htmlspecialchars($oList_Item->value) . ' ';
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
						$oSearch_Page->text .= htmlspecialchars($oInformationsystem_Item->name) . ' ' . $oInformationsystem_Item->description . ' ' . $oInformationsystem_Item->text . ' ';
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
						$oSearch_Page->text .= htmlspecialchars($oShop_Item->name) . ' ' . $oShop_Item->description . ' ' . $oShop_Item->text . ' ';
					}
				}
			}
			// Other type
			elseif ($oPropertyValue->Property->type != 2)
			{
				$oSearch_Page->text .= htmlspecialchars($oPropertyValue->value) . ' ';
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
		$oSearch_Page->module_value_type = 1; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id

		$oSearch_Page->siteuser_groups = array($this->getSiteuserGroupId());

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		//$oSearch_Page->save();

		return $oSearch_Page;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function name($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div');

		if ($this->active == 0)
		{
			$oCore_Html_Entity_Div->style("text-decoration: line-through");
		}

		$oCore_Html_Entity_Div->add(
			Core::factory('Core_Html_Entity_A')
				->href($link)
				->onclick($onclick)
				->value(htmlspecialchars($this->name))
		);

		if ($this->active == 1)
		{
			$oCurrentAlias = $this->Shop->Site->getCurrentAlias();

			if ($oCurrentAlias)
			{
				$href = ($this->Shop->Structure->https ? 'https://' : 'http://')
					. $oCurrentAlias->name
					. $this->Shop->Structure->getPath()
					. $this->getPath();

				$oCore_Html_Entity_Div->add(
					Core::factory('Core_Html_Entity_A')
						->href($href)
						->target('_blank')
						->add(
							Core::factory('Core_Html_Entity_I')
							->class('fa fa-external-link')
						)
				);
			}
		}

		$this->items_total_count > 0 && $oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_Span')
					->class('badge badge-hostcms badge-square')
					->value($this->items_total_count)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Check and correct duplicate path
	 * @return self
	 */
	public function checkDuplicatePath()
	{
		$oShop = $this->Shop;

		// Search the same item or group
		$oSameShopGroup = $oShop->Shop_Groups->getByParentIdAndPath($this->parent_id, $this->path);
		if (!is_null($oSameShopGroup) && $oSameShopGroup->id != $this->id)
		{
			$this->path = Core_Guid::get();
		}

		$oSameShopItem = $oShop->Shop_Items->getByGroupIdAndPath($this->parent_id, $this->path);
		if (!is_null($oSameShopItem))
		{
			$this->path = Core_Guid::get();
		}

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
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		if (is_null($this->path))
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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event shop_group.onBeforeRedeclaredDelete
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
		$aPropertyValues = $this->getPropertyValues();
		foreach($aPropertyValues as $oPropertyValue)
		{
			$oPropertyValue->delete();
		}

		$this->Shop_Groups->deleteAll(FALSE);
		$this->Shop_Items->deleteAll(FALSE);
		$this->Shop_Item_Property_For_Groups->deleteAll(FALSE);

		// Remove from search index
		$this->unindex();

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$newObject->guid = Core_Guid::get();
		$this->_changeCopiedName && $newObject->path(Core_Guid::get());
		$newObject->save();

		// Существует файл большого изображения для оригинального элемента
		if (is_file($this->getLargeFilePath()))
		{
			$newObject->saveLargeImageFile($this->getLargeFilePath(), $this->image_large);
		}

		// Существует файл малого изображения для оригинального элемента
		if (is_file($this->getSmallFilePath()))
		{
			$newObject->saveSmallImageFile($this->getSmallFilePath(), $this->image_small);
		}

		$aChildrenGroups = $this->Shop_Groups->findAll();
		foreach($aChildrenGroups as $oChildrenGroup)
		{
			$oChild = $oChildrenGroup->copy();
			$oChild->parent_id = $newObject->id;
			$oChild->save();
		}

		$aShop_Items = $this->Shop_Items->findAll();
		foreach($aShop_Items as $oShop_Item)
		{
			$newObject->add($oShop_Item->copy());
			// Recount for current group
			$this->decCountItems();
		}

		$aPropertyValues = $this->getPropertyValues();
		foreach($aPropertyValues as $oPropertyValue)
		{
			$oNewPropertyValue = clone $oPropertyValue;
			$oNewPropertyValue->entity_id = $newObject->id;
			$oNewPropertyValue->save();

			if ($oNewPropertyValue->Property->type == 2)
			{
				$oPropertyValue->setDir($this->getGroupPath());
				$oNewPropertyValue->setDir($newObject->getGroupPath());

				if (is_file($oPropertyValue->getLargeFilePath()))
				{
					try
					{
						Core_File::copy($oPropertyValue->getLargeFilePath(), $oNewPropertyValue->getLargeFilePath());
					} catch (Exception $e) {}
				}

				if (is_file($oPropertyValue->getSmallFilePath()))
				{
					try
					{
						Core_File::copy($oPropertyValue->getSmallFilePath(), $oNewPropertyValue->getSmallFilePath());
					} catch (Exception $e) {}
				}
			}
		}

		return $newObject;
	}

	/**
	 * Get IDs of child groups
	 * @param boolean $bCache cache mode
	 * @return array
	 *
	 * <code>
	 *	// Дочерние группы $shop_group_id магазина 1
	 *	$shop_group_id = 777;
	 *	$oShop_Groups = Core_Entity::factory('Shop', $shop_id)->Shop_Groups;
	 *	$oShop_Groups->queryBuilder()
	 *		->where('parent_id', '=', $shop_group_id);
	 *
	 *	$aChildrenId = $oShop_Groups->getGroupChildrenId(FALSE);
	 *	foreach ($aChildrenId as $iGroupId)
	 *	{
	 *		var_dump($iGroupId);
	 *	}
	 * </code>
	 */
	public function getGroupChildrenId($bCache = TRUE)
	{
		//$aGroupIDs = array($this->id);
		$aGroupIDs = array();

		$aShop_Groups = $this->findAll($bCache);
		foreach ($aShop_Groups as $oShop_Group)
		{
			$aGroupIDs = array_merge(
				$aGroupIDs,
				array($oShop_Group->id),
				$oShop_Group->Shop_Groups->getGroupChildrenId($bCache)
			);
		}

		return $aGroupIDs;
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE)
	{
		$this->_showXmlProperties = is_array($showXmlProperties)
			? array_combine($showXmlProperties, $showXmlProperties)
			: $showXmlProperties;

		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_group.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->clearXmlTags();

		!isset($this->_forbiddenTags['url'])
			&& $this->addXmlTag('url', $this->Shop->Structure->getPath() . $this->getPath());

		!isset($this->_forbiddenTags['dir'])
			&& $this->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getGroupHref());

		if ($this->_showXmlProperties)
		{
			if (is_array($this->_showXmlProperties))
			{
				$aProperty_Values = Property_Controller_Value::getPropertiesValues($this->_showXmlProperties, $this->id);
				foreach ($aProperty_Values as $oProperty_Value)
				{
					if ($oProperty_Value->Property->type == 2)
					{
						$oProperty_Value
							->setHref($this->getGroupHref())
							->setDir($this->getGroupPath());
					}

					/*isset($this->_showXmlProperties[$oProperty_Value->property_id]) && */$this->addEntity(
						$oProperty_Value
					);
				}
			}
			else
			{
				$aProperty_Values = $this->getPropertyValues();
				// Add all values
				$this->addEntities($aProperty_Values);
			}
		}

		return parent::getXml();
	}

	/**
	 * Clear tagged cache
	 * @return self
	 */
	public function clearCache()
	{
		if (Core::moduleIsActive('cache'))
		{
			Core_Cache::instance(Core::$mainConfig['defaultCache'])
				->deleteByTag('shop_group_' . $this->id)
				->deleteByTag('shop_group_' . $this->parent_id);

			// Static cache
			$oSite = $this->Shop->Site;
			if ($oSite->html_cache_use)
			{
				$oSiteAlias = $oSite->getCurrentAlias();
				if ($oSiteAlias)
				{
					$url = $oSiteAlias->name
						. $this->Shop->Structure->getPath()
						. $this->getPath();

					$oCache_Static = Core_Cache::instance('static');
					$oCache_Static->delete($url);
				}
			}
		}

		return $this;
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
				'parent_id' => $this->parent_id,
				'path' => $this->path,
				'sorting' => $this->sorting,
				'active' => $this->active,
				'indexing' => $this->indexing,
				'description' => $this->description,
				'seo_title' => $this->seo_title,
				'seo_description' => $this->seo_description,
				'seo_keywords' => $this->seo_keywords,
				'shop_id' => $this->shop_id,
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
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->path = Core_Array::get($aBackup, 'path');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->indexing = Core_Array::get($aBackup, 'indexing');
				$this->seo_title = Core_Array::get($aBackup, 'seo_title');
				$this->seo_description = Core_Array::get($aBackup, 'seo_description');
				$this->seo_keywords = Core_Array::get($aBackup, 'seo_keywords');
				$this->save();
			}
		}

		return $this;
	}
}