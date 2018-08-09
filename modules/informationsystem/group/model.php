<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Group_Model
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Group_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'informationsystem_group';

	/**
	 * Backend property
	 * @var mixed
	 */
	public $img = 0;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $comment_field = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $datetime = NULL;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $adminComment = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'informationsystem_item' => array(),
		'informationsystem_group' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'informationsystem_group' => array('foreign_key' => 'parent_id'),
		'informationsystem' => array(),
		'siteuser' => array(),
		'siteuser_group' => array(),
		'user' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 *
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'seo_group_title_template',
		'seo_group_keywords_template',
		'seo_group_description_template',
		'seo_item_title_template',
		'seo_item_keywords_template',
		'seo_item_description_template'
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
			$this->_preloadValues['guid'] = Core_Guid::get();
		}
	}

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
		'indexing' => 1,
		'siteuser_id' => 0,
		'active' => 1,
		'image_large' => '',
		'image_small' => ''
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'description'
	);

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
			$aProperties = Core_Entity::factory('Informationsystem_Group_Property_List', $this->informationsystem_id)
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
					$oProperty_Value->dateFormat($this->Informationsystem->format_date);
				break;
				case 9:
					$oProperty_Value->dateTimeFormat($this->Informationsystem->format_datetime);
				break;
			}
		}

		$bCache && $this->_propertyValues[$iMd5] = $aReturn;

		return $aReturn;
	}

	/**
	 * Check and correct duplicate path
	 * @return self
	 */
	public function checkDuplicatePath()
	{
		$oInformationsystem = $this->InformationSystem;

		// Search the same item or group
		$oSameInformationsystemGroup = $oInformationsystem->Informationsystem_Groups->getByParentIdAndPath($this->parent_id, $this->path);
		if (!is_null($oSameInformationsystemGroup) && $oSameInformationsystemGroup->id != $this->id)
		{
			$this->path = Core_Guid::get();
		}

		$oSameInformationsystemItem = $oInformationsystem->Informationsystem_Items->getByGroupIdAndPath($this->parent_id, $this->path);
		if (!is_null($oSameInformationsystemItem))
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
		if ($this->InformationSystem->url_type == 1)
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
	 * Get group path with separator
	 * @return string
	 */
	public function groupPathWithSeparator($separator = ' → ', $offset = 0)
	{
		$aParentGroups = array();

		$aTmpGroup = $this;

		// Добавляем все директории от текущей до родителя.
		do {
			$aParentGroups[] = $aTmpGroup->name;
		} while ($aTmpGroup = $aTmpGroup->getParent());

		$offset > 0
			&& $aParentGroups = array_slice($aParentGroups, $offset);

		$sParents = implode($separator, array_reverse($aParentGroups));

		return $sParents;
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
	 * @hostcms-event informationsystem_group.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$aInformationsystem_Items = $this->Informationsystem_Items->deleteAll(FALSE);
		$aInformationsystem_Groups = $this->Informationsystem_Groups->deleteAll(FALSE);

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		// Удаляем значения доп. свойств
		$aPropertyValues = $this->getPropertyValues();
		foreach ($aPropertyValues as $oPropertyValue)
		{
			$oPropertyValue->Property->type == 2 && $oPropertyValue->setDir($this->getGroupPath());
			$oPropertyValue->delete();
		}

		// Удаляем директорию информационной группы
		$this->deleteDir();

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

		$this->_changeCopiedName && $newObject->path(Core_Guid::get())->save();

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

		$aChildrenGroups = $this->Informationsystem_Groups->findAll();
		foreach ($aChildrenGroups as $oChildrenGroup)
		{
			$oChild = $oChildrenGroup->copy();
			$oChild->parent_id = $newObject->id;
			$oChild->save();
		}

		$aInformationsystem_Items = $this->Informationsystem_Items->findAll();
		foreach ($aInformationsystem_Items as $oInformationsystem_Item)
		{
			$newObject->add($oInformationsystem_Item->copy());
			// Recount for current group
			$this->decCountItems();
		}

		$aPropertyValues = $this->getPropertyValues();
		foreach ($aPropertyValues as $oPropertyValue)
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
	 * Get parent group
	 * @return Informationsystem_Group_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Informationsystem_Group', $this->parent_id)
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
			//->clear()
			->where('parent_id', '=', $parent_id);

		return $this->findAll($bCache);
	}

	/**
	 * Get group by parent id and path
	 * @param int $parent_id parent id
	 * @param string $path path
	 * @return Informationsystem_Group|NULL
	 */
	public function getByParentIdAndPath($parent_id, $path)
	{
		$this->queryBuilder()
			//->clear()
			->where('path', 'LIKE', $path)
			->where('parent_id', '=', $parent_id)
			->limit(1)
			->clearOrderBy();

		$aInformationsystem_Groups = $this->findAll(FALSE);

		return isset($aInformationsystem_Groups[0])
			? $aInformationsystem_Groups[0]
			: NULL;
	}

	/**
	 * Get group path
	 * @return string
	 */
	public function getGroupPath()
	{
		return $this->Informationsystem->getPath() . '/' . Core_File::getNestingDirPath($this->id, $this->Informationsystem->Site->nesting_level) . '/group_' . $this->id . '/';
	}

	/**
	 * Get group href
	 * @return string
	 */
	public function getGroupHref()
	{
		return '/' . $this->Informationsystem->getHref() . '/' . Core_File::getNestingDirPath($this->id, $this->Informationsystem->Site->nesting_level) . '/group_' . $this->id . '/';
	}

	/**
	 * Get small file path
	 * @return string
	 */
	public function getSmallFilePath()
	{
		return $this->getGroupPath() . $this->image_small;
	}

	/**
	 * Get small file href
	 * @return string
	 */
	public function getSmallFileHref()
	{
		return $this->getGroupHref() . rawurlencode($this->image_small);
	}

	/**
	 * Get large file path
	 * @return string
	 */
	public function getLargeFilePath()
	{
		return $this->getGroupPath() . $this->image_large;
	}

	/**
	 * Get large file href
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
	 * Move group to another group
	 * @param int $parent_id group id
	 * @return self
	 * @hostcms-event informationsystem_group.onBeforeMove
	 */
	public function move($parent_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($parent_id));

		$this->parent_id = $parent_id;
		return $this->save();
	}

	/**
	 * Create group directory
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
	 * Delete group directory
	 * @return self
	 */
	public function deleteDir()
	{
		// Удаляем файл большого изображения группы
		$this->deleteLargeImage();

		// Удаляем файл малого изображения группы
		$this->deleteSmallImage();

		if (is_dir($this->getGroupPath()))
		{
			try
			{
				Core_File::deleteDir($this->getGroupPath());
			} catch (Exception $e) {}
		}
		return $this;
	}

	/**
	 * Delete group's large image
	 * @return self
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
	 * Change group status
	 * @return Informationsystem_Group_Model
	 * @hostcms-event informationsystem_group.onBeforeChangeActive
	 * @hostcms-event informationsystem_group.onAfterChangeActive
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
	 * Remove item from search index
	 * @return self
	 */
	public function unindex()
	{
		if (Core::moduleIsActive('search'))
		{
			Search_Controller::deleteSearchPage(1, 1, $this->id);
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
	 * Switch indexation mode
	 * @return self
	 */
	public function changeIndexation()
	{
		$this->indexing = 1 - $this->indexing;
		return $this->save();
	}

	/**
	 * Get group path
	 * @return string
	 * @hostcms-event informationsystem_group.onBeforeGetPath
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
				? $this->Informationsystem_Group->getSiteuserGroupId()
				: $this->Informationsystem->siteuser_group_id;
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

		if ($this->parent_id != 0 /*&& $this->parent_id != $this->id*/ && !is_null($this->Informationsystem_Group->id))
		{
			$this->Informationsystem_Group->modifyCountItems($int, FALSE);
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
	 * Update subgroups count
	 * @param int $int group count
	 * @return self
	 */
	public function modifyCountGroups($int = 1)
	{
		$this->subgroups_count += $int;
		$this->subgroups_total_count += $int;
		$this->save();

		if ($this->parent_id != 0 && !is_null($this->Informationsystem_Group->id))
		{
			$this->Informationsystem_Group->modifyCountGroups($int);
		}
		return $this;
	}

	/**
	 * Create group
	 * @return Core_ORM
	 */
	public function create()
	{
		$return = parent::create();

		if ($this->parent_id != 0)
		{
			// Увеличение количества элементов в группе
			$this->Informationsystem_Group->incCountGroups();
		}
		return $return;
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
			$oCore_Html_Entity_Div
				->style("text-decoration: line-through");
		}

		$oCore_Html_Entity_Div
			->add(
				Core::factory('Core_Html_Entity_A')
					->href($link)
					->onclick($onclick)
					->value(htmlspecialchars($this->name))
			);

		if ($this->active == 1)
		{
			$oCurrentAlias = $this->Informationsystem->Site->getCurrentAlias();

			if ($oCurrentAlias)
			{
				$href = ($this->Informationsystem->Structure->https ? 'https://' : 'http://')
					. $oCurrentAlias->name
					. $this->Informationsystem->Structure->getPath()
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
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event informationsystem_group.onBeforeIndexing
	 * @hostcms-event informationsystem_group.onAfterIndexing
	 */
	public function indexing()
	{
		$oSearch_Page = new stdClass();

		Core_Event::notify($this->_modelName . '.onBeforeIndexing', $this, array($oSearch_Page));

		$oSearch_Page->text = htmlspecialchars($this->name) . ' ' . $this->description . ' ' . $this->id . ' ' . htmlspecialchars($this->seo_title) . ' ' . htmlspecialchars($this->seo_description) . ' ' . htmlspecialchars($this->seo_keywords) . ' ' . htmlspecialchars($this->path) . ' ';

		$oSearch_Page->title = $this->name;

		$aPropertyValues = $this->getPropertyValues(FALSE);
		foreach ($aPropertyValues as $oPropertyValue)
		{
			// List
			if ($oPropertyValue->Property->type == 3 && Core::moduleIsActive('list'))
			{
				if ($oPropertyValue->value != 0)
				{
					$oList_Item = $oPropertyValue->List_Item;
					$oList_Item->id && $oSearch_Page->text .= htmlspecialchars($oList_Item->value) . ' ' . htmlspecialchars($oList_Item->description) . ' ';
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
			// Wysiwyg
			elseif ($oPropertyValue->Property->type == 6)
			{
				$oSearch_Page->text .= htmlspecialchars(strip_tags($oPropertyValue->value)) . ' ';
			}
			// Other type
			elseif ($oPropertyValue->Property->type != 2)
			{
				$oSearch_Page->text .= htmlspecialchars($oPropertyValue->value) . ' ';
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
		$oSearch_Page->datetime = date('Y-m-d H:i:s');
		$oSearch_Page->module = 1;
		$oSearch_Page->module_id = $this->informationsystem_id;
		$oSearch_Page->inner = 0;
		$oSearch_Page->module_value_type = 1; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id

		$oSearch_Page->siteuser_groups = array($this->getSiteuserGroupId());

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		//$oSearch_Page->save();

		return $oSearch_Page;
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
	 * @hostcms-event informationsystem_group.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->clearXmlTags();

		!isset($this->_forbiddenTags['url'])
			&& $this->addXmlTag('url', $this->Informationsystem->Structure->getPath() . $this->getPath());

		!isset($this->_forbiddenTags['dir'])
			&& $this->addXmlTag('dir', Core_Page::instance()->informationsystemCDN . $this->getGroupHref());

		if ($this->_showXmlProperties)
		{
			if (is_array($this->_showXmlProperties))
			{
				$aProperty_Values = Property_Controller_Value::getPropertiesValues($this->_showXmlProperties, $this->id);
				foreach ($aProperty_Values as $oProperty_Value)
				{
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
	 * Get IDs of child groups
	 * @return array
	 *
	 * <code>
	 *	// Дочерние группы $informationsystem_group_id магазина 1
	 *	$informationsystem_group_id = 777;
	 *	$oInformationsystem_Groups = Core_Entity::factory('Informationsystem', $informationsystem_id)->Informationsystem_Groups;
	 *	$oInformationsystem_Groups->queryBuilder()
	 *		->where('parent_id', '=', $informationsystem_group_id);
	 *
	 *	$aChildrenId = $oInformationsystem_Groups->getGroupChildrenId();
	 *	foreach ($aChildrenId as $iGroupId)
	 *	{
	 *		var_dump($iGroupId);
	 *	}
	 * </code>
	 */
	public function getGroupChildrenId()
	{
		//$aGroupIDs = array($this->id);
		$aGroupIDs = array();

		$aInformationsystem_Groups = $this->findAll();
		foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
		{
			$aGroupIDs = array_merge($aGroupIDs, array($oInformationsystem_Group->id), $oInformationsystem_Group->Informationsystem_Groups->getGroupChildrenId());
		}

		return $aGroupIDs;
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
				->deleteByTag('informationsystem_group_' . $this->id)
				->deleteByTag('informationsystem_group_' . $this->parent_id);

			// Static cache
			$oSite = $this->Informationsystem->Site;
			if ($oSite->html_cache_use)
			{
				$oSiteAlias = $oSite->getCurrentAlias();
				if ($oSiteAlias)
				{
					$url = $oSiteAlias->name
						. $this->Informationsystem->Structure->getPath()
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
				'informationsystem_id' => $this->informationsystem_id,
				'siteuser_id' => $this->siteuser_id,
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
				$this->siteuser_id = Core_Array::get($aBackup, 'siteuser_id');
				$this->save();
			}
		}

		return $this;
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
						$aTmp[] = strftime($this->Informationsystem->format_date, Core_Date::sql2timestamp($oProperty_Value->value));
					break;
					case 9: // Datetime
						$aTmp[] = strftime($this->Informationsystem->format_datetime, Core_Date::sql2timestamp($oProperty_Value->value));
					break;
					case 3: // List
						$oList_Item = $oProperty->List->List_Items->getById(
							$oProperty_Value->value, FALSE
						);

						!is_null($oList_Item) && $aTmp[] = $oList_Item->value;
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
}