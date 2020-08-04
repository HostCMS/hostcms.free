<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Model
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'informationsystem';

	/**
	 * Backend property
	 * @var mixed
	 */
	public $img = 1;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'informationsystem_item' => array(),
		'informationsystem_group' => array(),
		'informationsystem_group_property' => array(),
		'informationsystem_group_property_dir' => array(),
		'informationsystem_item_property' => array(),
		'informationsystem_item_property_dir' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'informationsystem_dir' => array(),
		'siteuser_group' => array(),
		'structure' => array(),
		'site' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'informationsystem_dir_id' => 0,
		'use_captcha' => 1,
		'watermark_file' => '',
		'watermark_default_use_large_image' => 0,
		'watermark_default_use_small_image' => 0,
		'watermark_default_position_x' => '50%',
		'watermark_default_position_y' => '100%',
		'items_on_page' => 10,
		'structure_id' => 0,
		'format_date' => '%d.%m.%Y',
		'format_datetime' => '%d.%m.%Y %H:%M:%S',
		'image_large_max_width' => 300,
		'image_large_max_height' => 300,
		'image_small_max_width' => 70,
		'image_small_max_height' => 70,
		'url_type' => 0,
		'typograph_default_items' => 0,
		'typograph_default_groups' => 0,
		'apply_tags_automatically' => 1,
		'change_filename' => 1,
		'apply_keywords_automatically' => 1,
		'group_image_large_max_width' => 300,
		'group_image_large_max_height' => 300,
		'group_image_small_max_width' => 70,
		'group_image_small_max_height' => 70,
		'preserve_aspect_ratio' => 1,
		'preserve_aspect_ratio_small' => 1,
		'siteuser_group_id' => 0
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will be shown.
	 *
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'items_sorting_direction',
		'items_sorting_field',
		'groups_sorting_direction',
		'groups_sorting_field',
		'image_large_max_width',
		'image_large_max_height',
		'image_small_max_width',
		'image_small_max_height',
		'siteuser_group_id',
		'watermark_file',
		'watermark_default_use_large_image',
		'watermark_default_use_small_image',
		'watermark_default_position_x',
		'watermark_default_position_y',
		'create_small_image',
		'typograph_default_items',
		'typograph_default_groups',
		'apply_tags_automatically',
		'change_filename',
		'apply_keywords_automatically',
		'group_image_small_max_width',
		'group_image_large_max_width',
		'group_image_small_max_height',
		'group_image_large_max_height',
		'preserve_aspect_ratio',
		'preserve_aspect_ratio_small',
		'preserve_aspect_ratio_group',
		'preserve_aspect_ratio_group_small',
		'seo_group_title_template',
		'seo_group_keywords_template',
		'seo_group_description_template',
		'seo_item_title_template',
		'seo_item_keywords_template',
		'seo_item_description_template'
	);

	/**
	 * List of Shortcodes tags
	 * @var array
	 */
	protected $_shortcodeTags = array(
		'description'
	);

	/**
	 * Tree of groups
	 * @array
	 */
	protected $_groupsTree = array();

	/**
	 * Cache of groups
	 * @var array
	 */
	protected $_cacheGroups = array();

	/**
	 * Cache of items
	 * @var array
	 */
	protected $_cacheItems = array();

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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Calculate counts
	 * @var boolean
	 */
	protected $_showXmlCounts = TRUE;

	/**
	 * Add comments XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlCounts($showXmlCounts = TRUE)
	{
		$this->_showXmlCounts = $showXmlCounts;
		return $this;
	}

	/**
	 * Get information system by structure id
	 * @param int $structure_id structure id
	 * @return Informationsystem|NULL
	 */
	public function getByStructureId($structure_id)
	{
		$this->queryBuilder()
			->clear()
			->where('structure_id', '=', $structure_id)
			->limit(1);

		$aInformationsystems = $this->findAll();

		return isset($aInformationsystems[0])
			? $aInformationsystems[0]
			: NULL;
	}

	/**
	 * Get information system href
	 * @return string
	 */
	public function getHref()
	{
		return $this->Site->uploaddir . 'information_system_' . intval($this->id);
	}

	/**
	 * Get information system path
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Get watermark path
	 * @return string|NULL
	 */
	public function getWatermarkFilePath()
	{
		return $this->watermark_file != ''
			? $this->getPath() . '/' . $this->watermark_file
			: NULL;
	}

	/**
	 * Get watermark href
	 * @return string
	 */
	public function getWatermarkFileHref()
	{
		return '/' . $this->getHref() . '/' . $this->watermark_file;
	}

	/**
	 * Specify watermark file for information system
	 * @param string $fileSourcePath source file
	 */
	public function saveWatermarkFile($fileSourcePath)
	{
		$this->watermark_file = 'information_system_watermark' . $this->id . '.png';
		$this->save();
		Core_File::upload($fileSourcePath, $this->getWatermarkFilePath());
	}

	/**
	 * Create files directory
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

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
	 * Delete information system directory
	 * @return self
	 */
	public function deleteDir()
	{
		// Удаляем директории информационных групп
		$aInformationsystem_Groups = $this->Informationsystem_Groups->findAll(FALSE);
		foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
		{
			$oInformationsystem_Group->deleteDir();
		}

		// Удаляем директории информационных элементов
		$aInformationsystem_Items = $this->Informationsystem_Items->findAll(FALSE);
		foreach ($aInformationsystem_Items as $oInformationsystem_Item)
		{
			$oInformationsystem_Item->deleteDir();
		}

		$this->deleteWatermarkFile();

		if (is_dir($this->getPath()))
		{
			try
			{
				Core_File::deleteDir($this->getPath());
			} catch (Exception $e) {}
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
		parent::save();

		// Создание директории для Watermark
		$sWatermarkDirPath = dirname($this->getWatermarkFilePath());

		if ($sWatermarkDirPath && !is_dir($sWatermarkDirPath))
		{
			$this->createDir();
		}

		return $this;
	}

	/**
	 * Delete watermark file
	 * @return self
	 */
	public function deleteWatermarkFile()
	{
		try
		{
			is_file($this->getWatermarkFilePath()) && Core_File::delete($this->getWatermarkFilePath());
		} catch (Exception $e) {}

		$this->watermark_file = '';
		$this->save();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event informationsystem.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		// Fix bug with 'deleted' relations
		$this->deleted = 0;
		$this->save();

		// Доп. свойства информационных элементов
		$oInformationsystem_Item_Property_List = Core_Entity::factory('Informationsystem_Item_Property_List', $this->id);

		$oInformationsystem_Item_Property_List->Properties->deleteAll(FALSE);
		$oInformationsystem_Item_Property_List->Property_Dirs->deleteAll(FALSE);

		// Доп. свойства информационных групп
		$oInformationsystem_Group_Property_List = Core_Entity::factory('Informationsystem_Group_Property_List', $this->id);
		$oInformationsystem_Group_Property_List->Properties->deleteAll(FALSE);
		$oInformationsystem_Group_Property_List->Property_Dirs->deleteAll(FALSE);

		$this->Informationsystem_Item_Property_Dirs->deleteAll(FALSE);
		$this->Informationsystem_Item_Properties->deleteAll(FALSE);
		$this->Informationsystem_Group_Property_Dirs->deleteAll(FALSE);
		$this->Informationsystem_Group_Properties->deleteAll(FALSE);

		// Удаляем информационные группы
		$this->Informationsystem_Groups->deleteAll(FALSE);

		// Удаляем информационные элементы
		$this->Informationsystem_Items->deleteAll(FALSE);

		// Удаляем директорию информационной системы
		$this->deleteDir();

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event informationsystem.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			is_file($this->getWatermarkFilePath()) && Core_File::copy($this->getWatermarkFilePath(), $newObject->getWatermarkFilePath());
		} catch (Exception $e) {}

		// Копирование доп. свойств и разделов доп. свойств информационных элементов
		$oInformationsystem_Item_Property_List = Core_Entity::factory('Informationsystem_Item_Property_List', $this->id);

		// Linked object for new IS
		$oNewObject_Informationsystem_Item_Property_List = Core_Entity::factory('Informationsystem_Item_Property_List', $newObject->id);

		$oProperty_Dir = $oInformationsystem_Item_Property_List->Property_Dirs;
		$aProperty_Dirs = $oProperty_Dir->findAll();
		$aMatchProperty_Dirs = array();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			$oNewProperty_Dir = clone $oProperty_Dir;
			$oNewObject_Informationsystem_Item_Property_List->add($oNewProperty_Dir);

			$aMatchProperty_Dirs[$oProperty_Dir->id] = $oNewProperty_Dir;
		}

		$oNewProperty_Dirs = $oNewObject_Informationsystem_Item_Property_List->Property_Dirs->findAll();
		foreach ($oNewProperty_Dirs as $oNewProperty_Dir)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]))
			{
				$oNewProperty_Dir->parent_id = $aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]->id;
				$oNewProperty_Dir->save();
			}
		}

		$oProperty = $oInformationsystem_Item_Property_List->Properties;
		$aProperties = $oProperty->findAll();
		foreach ($aProperties as $oProperty)
		{
			$oNewProperty = clone $oProperty;
			$oNewObject_Informationsystem_Item_Property_List->add($oNewProperty);
		}

		$oNewProperties = $oNewObject_Informationsystem_Item_Property_List->Properties->findAll();
		foreach ($oNewProperties as $oNewProperty)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty->property_dir_id]))
			{
				$oNewProperty->property_dir_id = $aMatchProperty_Dirs[$oNewProperty->property_dir_id]->id;
				$oNewProperty->save();
			}
		}

		// Копирование доп. свойств и разделов доп. свойств групп информационных элементов
		$oInformationsystem_Group_Property_List = Core_Entity::factory('Informationsystem_Group_Property_List', $this->id);
		$oNewObject_Informationsystem_Group_Property_List = Core_Entity::factory('Informationsystem_Group_Property_List', $newObject->id);

		$oProperty_Dir = $oInformationsystem_Group_Property_List->Property_Dirs;
		//$oProperty_Dir->queryBuilder()->where('parent_id', '=', 0);
		$aProperty_Dirs = $oProperty_Dir->findAll();

		$aMatchProperty_Dirs = array();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			$oNewProperty_Dir = clone $oProperty_Dir;

			$oNewObject_Informationsystem_Group_Property_List->add($oNewProperty_Dir);

			$aMatchProperty_Dirs[$oProperty_Dir->id] = $oNewProperty_Dir;
		}

		$oNewProperty_Dirs = $oNewObject_Informationsystem_Group_Property_List->Property_Dirs->findAll();

		foreach ($oNewProperty_Dirs as $oNewProperty_Dir)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]))
			{
				$oNewProperty_Dir->parent_id = $aMatchProperty_Dirs[$oNewProperty_Dir->parent_id]->id;
				$oNewProperty_Dir->save();
			}
		}

		$oProperty = $oInformationsystem_Group_Property_List->Properties;
		$aProperties = $oProperty->findAll();

		foreach ($aProperties as $oProperty)
		{
			$oNewProperty = clone $oProperty;
			$oNewObject_Informationsystem_Group_Property_List->add($oNewProperty);
		}

		$oNewProperties = $oNewObject_Informationsystem_Group_Property_List->Properties->findAll();
		foreach ($oNewProperties as $oNewProperty)
		{
			if (isset($aMatchProperty_Dirs[$oNewProperty->property_dir_id]))
			{
				$oNewProperty->property_dir_id = $aMatchProperty_Dirs[$oNewProperty->property_dir_id]->id;
				$oNewProperty->save();
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Recount items and subgroups
	 * @return Informationsystem_Model
	 * @hostcms-event informationsystem.onBeforeRecount
	 * @hostcms-event informationsystem.onAfterRecount
	 * @hostcms-event informationsystem.onBeforeSelectCountGroupsInRecount
	 * @hostcms-event informationsystem.onBeforeSelectCountItemsInRecount
	 */
	public function recount()
	{
		$information_system_id = $this->id;

		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			@set_time_limit(90000);
			ini_set('max_execution_time', '90000');
		}

		Core_Event::notify($this->_modelName . '.onBeforeRecount', $this);

		$this->_groupsTree = array();
		$queryBuilder = Core_QueryBuilder::select('id', 'parent_id')
			->from('informationsystem_groups')
			->where('informationsystem_groups.informationsystem_id', '=', $information_system_id)
			//->where('informationsystem_groups.active', '=', 1) // Пресчитываем для всех групп, включая отключенные
			->where('informationsystem_groups.deleted', '=', 0);

		$aInformationsystem_Groups = $queryBuilder->execute()->asAssoc()->result();
		foreach ($aInformationsystem_Groups as $aInformationsystem_Group)
		{
			$this->_groupsTree[$aInformationsystem_Group['parent_id']][] = $aInformationsystem_Group['id'];
		}

		$this->_cacheGroups = array();

		$queryBuilder = Core_QueryBuilder::select('parent_id', array('COUNT(id)', 'count'))
			->from('informationsystem_groups')
			->where('informationsystem_groups.informationsystem_id', '=', $information_system_id)
			->where('informationsystem_groups.active', '=', 1)
			->where('informationsystem_groups.deleted', '=', 0)
			->groupBy('parent_id');

		Core_Event::notify($this->_modelName . '.onBeforeSelectCountGroupsInRecount', $this, array($queryBuilder));

		$aInformationsystem_Groups = $queryBuilder->execute()->asAssoc()->result();
		foreach ($aInformationsystem_Groups as $aInformationsystem_Group)
		{
			$this->_cacheGroups[$aInformationsystem_Group['parent_id']] = $aInformationsystem_Group['count'];
		}

		$this->_cacheItems = array();

		$current_date = date('Y-m-d H:i:s');

		$queryBuilder->clear()
			->select('informationsystem_group_id', array('COUNT(id)', 'count'))
			->from('informationsystem_items')
			->where('informationsystem_items.informationsystem_id', '=', $information_system_id)
			->where('informationsystem_items.active', '=', 1)
			->where('informationsystem_items.start_datetime', '<=', $current_date)
			->open()
				->where('informationsystem_items.end_datetime', '>=', $current_date)
				->setOr()
				->where('informationsystem_items.end_datetime', '=', '0000-00-00 00:00:00')
			->close()
			->where('informationsystem_items.deleted', '=', 0)
			->groupBy('informationsystem_group_id');

		Core_Event::notify($this->_modelName . '.onBeforeSelectCountItemsInRecount', $this, array($queryBuilder));

		$aInformationsystem_Items = $queryBuilder->execute()->asAssoc()->result();

		foreach ($aInformationsystem_Items as $Informationsystem_Item)
		{
			$this->_cacheItems[$Informationsystem_Item['informationsystem_group_id']] = $Informationsystem_Item['count'];
		}

		// DISABLE KEYS
		Core_DataBase::instance()->setQueryType(5)->query("ALTER TABLE `informationsystem_groups` DISABLE KEYS");

		$this->_callSubgroup();

		// ENABLE KEYS
		Core_DataBase::instance()->setQueryType(5)->query("ALTER TABLE `informationsystem_groups` ENABLE KEYS");

		$this->_groupsTree = $this->_cacheGroups = $this->_cacheItems = array();

		Core_Event::notify($this->_modelName . '.onAfterRecount', $this);

		return $this;
	}

	/**
	 * Calculate groups and items count in parent group
	 * @param int $parent_id parent group id
	 * @return array
	 */
	protected function _callSubgroup($parent_id = 0)
	{
		$return = array(
			'subgroups' => 0,
			'subgroups_total' => 0,
			'items' => 0,
			'items_total' => 0
		);

		if (isset($this->_groupsTree[$parent_id]))
		{
			foreach ($this->_groupsTree[$parent_id] as $groupId)
			{
				$aTmp = $this->_callSubgroup($groupId);
				$return['subgroups_total'] += $aTmp['subgroups_total'];
				$return['items_total'] += $aTmp['items_total'];
			}
		}

		if (isset($this->_cacheGroups[$parent_id]))
		{
			$return['subgroups'] = $this->_cacheGroups[$parent_id];
			$return['subgroups_total'] += $return['subgroups'];
		}

		if (isset($this->_cacheItems[$parent_id]))
		{
			$return['items'] = $this->_cacheItems[$parent_id];
			$return['items_total'] += $return['items'];
		}

		if ($parent_id)
		{
			$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $parent_id);
			$oInformationsystem_Group->subgroups_count = $return['subgroups'];
			$oInformationsystem_Group->subgroups_total_count = $return['subgroups_total'];
			$oInformationsystem_Group->items_count = $return['items'];
			$oInformationsystem_Group->items_total_count = $return['items_total'];
			$oInformationsystem_Group->setCheck(FALSE)->save();
		}

		return $return;
	}

	/**
	 * Delete empty groups in UPLOAD path for informationsystem
	 */
	public function deleteEmptyDirs()
	{
		Core_File::deleteEmptyDirs($this->getPath());
		return FALSE;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event informationsystem.onBeforeRedeclaredGetXml
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
	 * @hostcms-event informationsystem.onBeforeRedeclaredGetStdObject
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
			->addXmlTag('http', '//' . Core_Array::get($_SERVER, 'SERVER_NAME'))
			->addXmlTag('url', $this->Structure->getPath())
			->addXmlTag('captcha_id', $this->use_captcha ? Core_Captcha::getCaptchaId() : 0);

		if ($this->_showXmlCounts)
		{
			$oInformationsystem_Items = $this->Informationsystem_Items;
			$oInformationsystem_Items->queryBuilder()
				->where('informationsystem_items.informationsystem_group_id', '=', 0);
			$iCountItems = $oInformationsystem_Items->getCount();

			$aInformationsystem_Groups = $this->Informationsystem_Groups->getByParentId(0, FALSE);
			$iCountGroups = count($aInformationsystem_Groups);

			$array = array(
				'items_count' => $iCountItems,
				'items_total_count' => $iCountItems,
				'subgroups_count' => $iCountGroups,
				'subgroups_total_count' => $iCountGroups
			);

			foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
			{
				$array['items_total_count'] += $oInformationsystem_Group->items_total_count;
				$array['subgroups_total_count'] += $oInformationsystem_Group->subgroups_total_count;
			}

			$this
				->addXmlTag('items_count', $array['items_count'])
				->addXmlTag('items_total_count', $array['items_total_count'])
				->addXmlTag('subgroups_count', $array['subgroups_count'])
				->addXmlTag('subgroups_total_count', $array['subgroups_total_count']);
		}

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		!$this->structure_id && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-darkorange badge-ico white')
			->add(Core::factory('Core_Html_Entity_I')->class('fa fa-chain-broken'))
			->execute();

		$countInformationsystemGroups = $this->Informationsystem_Groups->getCount();
		$countInformationsystemGroups && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square')
			->value('<i class="fa fa-folder-open-o"></i> ' . $countInformationsystemGroups)
			->title(Core::_('Informationsystem.all_groups_count', $countInformationsystemGroups))
			->execute();

		$countInformationsystemItems = $this->Informationsystem_Items->getCount();
		$countInformationsystemItems && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square')
			->value('<i class="fa fa-file-o"></i> ' . $countInformationsystemItems)
			->title(Core::_('Informationsystem.all_items_count', $countInformationsystemItems))
			->execute();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function pathBackend()
	{
		$this->structure_id && $this->Structure->pathBackend();
	}
}