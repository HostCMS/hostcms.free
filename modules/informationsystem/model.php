<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Model
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
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

		return $newObject;
	}

	/**
	 * Recount items and subgroups
	 * @return Informationsystem_Model
	 */
	public function recount()
	{
		$information_system_id = $this->id;

		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			@set_time_limit(90000);
			ini_set('max_execution_time', '90000');
		}

		$this->_groupsTree = array();
		$queryBuilder = Core_QueryBuilder::select('id', 'parent_id')
			->from('informationsystem_groups')
			->where('informationsystem_groups.informationsystem_id', '=', $information_system_id)
			->where('informationsystem_groups.active', '=', 1)
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

		return $this;
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
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event informationsystem.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

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

		return parent::getXml();
	}

	/**
	 * Backend callback method
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
}