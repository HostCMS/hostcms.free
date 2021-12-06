<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Structure_Model
 *
 * @package HostCMS
 * @subpackage Structure
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Structure_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'structure';

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'parent_id' => 0,
		'document_id' => 0,
		'lib_id' => 0,
		'type' => 0,
		'sorting' => 0,
		'https' => 0,
		'active' => 1,
		'indexing' => 1,
		'changefreq' => 2,
		'priority' => 0.5,
		'siteuser_group_id' => 0,
		'template_id' => 0,
		// Warning: Удалить после объединения
		'data_template_id' => 0,
		'show' => 1,
		'url' => ''
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'structure' => array('foreign_key' => 'parent_id'),
		'structure_menu' => array(),
		'template' => array(),
		'document' => array(),
		'lib' => array(),
		'site' => array(),
		'user' => array(),
		'siteuser' => array(),
		'siteuser_group' => array(),

		// Warning: Удалить
		'data_template' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'structure' => array('foreign_key' => 'parent_id')
	);

	/**
	 * One-to-one relations
	 * @var array
	 */
	protected $_hasOne = array(
		'forum' => array()
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'options'
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
			$this->_preloadValues['site_id'] = defined('CURRENT_SITE') ? CURRENT_SITE : 0;
		}
	}

	/**
	 * Values of all properties of structure node
	 * @var array
	 */
	protected $_propertyValues = NULL;

	/**
	 * Values of all properties of structure node
	 * Значения всех свойств узла структуры
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
			$aProperties = Core_Entity::factory('Structure_Property_List', $this->site_id)
				->Properties
				->findAll();

			$aPropertiesId = array();
			foreach ($aProperties as $oProperty)
			{
				$aPropertiesId[] = $oProperty->id;
			}
		}

		$aReturn = Property_Controller_Value::getPropertiesValues($aPropertiesId, $this->id, $bCache, $bSorting);

		// setHref()
		foreach ($aReturn as $oProperty_Value)
		{
			$this->_preparePropertyValue($oProperty_Value);
		}

		$bCache && $this->_propertyValues = $aReturn;

		return $aReturn;
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
					->setHref('/' . $this->getDirHref())
					->setDir($this->getDirPath());
			break;
			case 8:
				$oProperty_Value->dateFormat($this->Site->date_format);
			break;
			case 9:
				$oProperty_Value->dateTimeFormat($this->Site->date_time_format);
			break;
		}
	}

	/**
	 * Create directory for item
	 * @return self
	 */
	public function createDir()
	{
		if (!is_dir($this->getDirPath()))
		{
			try
			{
				Core_File::mkdir($this->getDirPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Get structure's lib file href
	 * @return string
	 */
	public function getLibFileHref()
	{
		return $this->Site->uploaddir . 'libs/lib_' . intval($this->lib_id) . '/structure_' . intval($this->id) . '/';
	}

	/**
	 * Get structure's lib file path
	 * @return string
	 */
	public function getLibFilePath()
	{
		return CMS_FOLDER . $this->getLibFileHref();
	}

	/**
	 * Get structure's file path
	 * @return string
	 */
	public function getStructureFilePath()
	{
		return CMS_FOLDER . "hostcmsfiles/structure/Structure" . intval($this->id) . ".php";
	}

	/**
	 * Get structure content
	 * @return string|NULL
	 */
	public function getStructureFile()
	{
		$path = $this->getStructureFilePath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Specify structure content
	 * @param string $content content
	 */
	public function saveStructureFile($content)
	{
		$this->save();
		Core_File::write($this->getStructureFilePath(), $content);
	}

	/**
	 * Get structure's config file path
	 * @return string
	 */
	public function getStructureConfigFilePath()
	{
		return CMS_FOLDER . "hostcmsfiles/structure/StructureConfig" . intval($this->id) . ".php";
	}

	/**
	 * Get structure config
	 * @return string
	 */
	public function getStructureConfigFile()
	{
		$path = $this->getStructureConfigFilePath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Specify structure config
	 * @param string $content config
	 */
	public function saveStructureConfigFile($content)
	{
		$this->save();
		Core_File::write($this->getStructureConfigFilePath(), $content);
	}

	/**
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		if (!$this->deleted && is_null($this->path))
		{
			$this->path = Core_Str::transliteration($this->name);
		}
		elseif (in_array('path', $this->_changedColumns))
		{
			$this->checkDuplicatePath();
		}

		parent::save();

		if (!$this->deleted && $this->path == '')
		{
			try {
				$path = Core_Str::transliteration(
					Core::$mainConfig['translate']
						? Core_Str::translate($this->name)
						: $this->name
				);
			} catch (Exception $e) {
				$path = '';
			}

			$this->path = strlen($path) ? $path : $this->id;
			$this->save();
		}

		return $this;
	}

	/**
	 * Delete Structure Config File
	 * @return self
	 */
	public function deleteConfigFile()
	{
		try
		{
			is_file($this->getStructureConfigFilePath())
				&& Core_File::delete($this->getStructureConfigFilePath());
		}
		catch (Exception $e) {}

		return $this;
	}

	/**
	 * Delete Structure File
	 * @return self
	 */
	public function deleteFile()
	{
		try
		{
			is_file($this->getStructureFilePath())
				&& Core_File::delete($this->getStructureFilePath());
		}
		catch (Exception $e) {}

		return $this;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event structure.onBeforeRedeclaredDelete
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

		$this
			->deleteConfigFile()
			->deleteFile();

		$aStructures = $this->Structures->findAll(FALSE);
		foreach ($aStructures as $oStructure)
		{
			$oStructure->delete();
		}

		// Delete proprties values
		// List of all properties
		$aProperties = Core_Entity::factory('Structure_Property_List', $this->site_id)->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			// Values of property
			$aProperty_Values = $oProperty->getValues($this->id);

			foreach ($aProperty_Values as $oProperty_Value)
			{
				$oProperty_Value->delete();
			}
		}

		// Delete structure directory for additional properties
		$sDirPath = $this->getDirPath();
		try
		{
			is_dir($sDirPath) && Core_File::deleteDir($sDirPath);
		}
		catch (Exception $e) {}

		// Lib .dat file
		if (!is_null($this->lib_id))
		{
			$sLibDatFile = $this->Lib->getLibDatFilePath($this->id);
			try
			{
				is_file($sLibDatFile) && Core_File::delete($sLibDatFile);
			}
			catch (Exception $e) {}
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->getChildCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square')
			->value($count)
			->execute();
	}

	/**
	 * Get count of substructures all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = 0;

		$aStructures = $this->Structures->findAll(FALSE);

		foreach ($aStructures as $oStructure)
		{
			$count++;
			$count += $oStructure->getChildCount();
		}

		return $count;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function pathBackend()
	{
		$sPath = $this->getPath();

		$oSite_Alias = Core_Entity::factory('Site', $this->site_id)->getCurrentAlias();
		if ($oSite_Alias)
		{
			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div');

			$oCore_Html_Entity_Div
				->class('hostcms-linkbox')
				->add(
					Core::factory('Core_Html_Entity_A')
						->href(($this->https ? 'https://' : 'http://') . $oSite_Alias->name . $sPath)
						->target("_blank")
						->value(htmlspecialchars(urldecode($sPath)))
				);

			!$this->active
				&& $oCore_Html_Entity_Div->class($oCore_Html_Entity_Div->class . ' line-through');

			$oCore_Html_Entity_Div->execute();
		}
		else
		{
			echo htmlspecialchars(urldecode($sPath));
		}
	}

	/**
	 * Get parent comment
	 * @return Structure_Model|NULL
	 */
	public function getParent()
	{
		if ($this->parent_id)
		{
			return Core_Entity::factory('Structure', $this->parent_id);
		}

		return NULL;
	}

	/**
	 * Get all nodes by site ID
	 * @param int $site_id site ID
	 * @return array
	 */
	public function getBySiteId($site_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('site_id', '=', $site_id);

		return $this->findAll();
	}

	/**
	 * Get all nodes by menu ID
	 * @param int $structure_menu_id menu ID
	 * @return array
	 */
	public function getByStructureMenuId($structure_menu_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('structure_menu_id', '=', $structure_menu_id);

		return $this->findAll();
	}

	/**
	 * Get active structure node by path and parent_id
	 * @param string $path
	 * @param int $parent_id
	 * @return
	 */
	public function getByPathAndParentId($path, $parent_id)
	{
		$this
			->queryBuilder()
			//->clear()
			->where('active', '=', 1)
			->where('path', 'LIKE', Core_DataBase::instance()->escapeLike($path))
			->where('parent_id', '=', $parent_id)
			->limit(1);

		$aStructure = $this->findAll();

		return count($aStructure) == 1 ? $aStructure[0] : NULL;
	}

	/**
	 * Get path for files
	 * @return string
	 * @hostcms-event structure.onBeforeGetPath
	 */
	public function getPath()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetPath', $this);

		$path = Core_Event::getLastReturn();

		if (is_null($path))
		{
			if ($this->path == '/')
			{
				return $this->path;
			}

			$path = rawurlencode($this->path) . '/';

			$path = $this->parent_id == 0
				? '/' . $path
				: $this->Structure->getPath() . $path;
		}

		return $path;
	}

	/**
	 * Get object directory href
	 * @return string
	 */
	public function getDirHref()
	{
		return $this->Site->uploaddir . 'structure_' . intval($this->Site->id) . '/' . Core_File::getNestingDirPath($this->id, $this->Site->nesting_level) . '/structure_' . $this->id . '/';
	}

	/**
	 * Get object directory path
	 * @return string
	 */
	public function getDirPath()
	{
		return CMS_FOLDER . $this->getDirHref();
	}

	/**
	 * Change status of activity for structure node
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		$this->save();
		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function structure_menu_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$path = $oAdmin_Form_Controller->getPath();

		$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
		);

		$aOptions = array();

		$aStructure_Menus = Core_Entity::factory('Structure_Menu')->getAllBySite_id(CURRENT_SITE);
		foreach ($aStructure_Menus as $oStructure_Menu)
		{
			$aOptions[$oStructure_Menu->id] = array(
				'value' => $oStructure_Menu->name,
				'color' => $oStructure_Menu->color ? $oStructure_Menu->color : '#aebec4'
			);
		}

		Core::factory('Core_Html_Entity_Span')
			->class('padding-left-10')
			->add(
				$oCore_Html_Entity_Dropdownlist
					->value($this->structure_menu_id)
					->options($aOptions)
					->onchange("$.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', action: 'apply', post: { 'hostcms[checked][0][{$this->id}]': 0, apply_check_0_{$this->id}_fv_{$oAdmin_Form_Field->id}: $(this).find('li[selected]').prop('id') }, windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
					->data('change-context', 'true')
				)
			->execute();

		return ob_get_clean();
	}

	/**
	 * Switch indexing mode
	 * @return self
	 */
	public function changeIndexing()
	{
		$this->indexing = 1 - $this->indexing;
		$this->save();
		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event structure.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->path = '';
		$newObject->save();

		$aPropertyValues = $this->getPropertyValues(FALSE);

		// Create destination dir
		count($aPropertyValues) && $newObject->createDir();

		foreach ($aPropertyValues as $oPropertyValue)
		{
			$oNewPropertyValue = clone $oPropertyValue;
			$oNewPropertyValue->entity_id = $newObject->id;
			$oNewPropertyValue->save();

			if ($oNewPropertyValue->Property->type == 2)
			{
				// Копируем файлы
				$oPropertyValue->setDir($this->getDirPath());
				$oNewPropertyValue->setDir($newObject->getDirPath());

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

		// Config file
		try
		{
			is_file($this->getStructureConfigFilePath())
				&& Core_File::copy($this->getStructureConfigFilePath(), $newObject->getStructureConfigFilePath());
		}
		catch (Exception $e) {}

		// File
		try
		{
			is_file($this->getStructureFilePath())
				&& Core_File::copy($this->getStructureFilePath(), $newObject->getStructureFilePath());
		}
		catch (Exception $e) {}

		// dat
		if ($this->lib_id)
		{
			$sLibDatFile = $this->Lib->getLibDatFilePath($this->id);
			try
			{
				is_file($sLibDatFile) && Core_File::copy($sLibDatFile, $newObject->Lib->getLibDatFilePath($newObject->id));
			}
			catch (Exception $e) {}
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event structure.onBeforeExecute
	 * @hostcms-event structure.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify($this->_modelName . '.onBeforeExecute', $this);

		include $this->getStructureFilePath();

		Core_Event::notify($this->_modelName . '.onAfterExecute', $this);

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
	 * @param boolean $showXmlProperties
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE, $xmlSortPropertiesValues = TRUE)
	{
		$this->_showXmlProperties = $showXmlProperties;

		$this->_xmlSortPropertiesValues = $xmlSortPropertiesValues;

		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event structure.onBeforeRedeclaredGetXml
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
	 * @hostcms-event structure.onBeforeRedeclaredGetStdObject
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
			->addXmlTag('link', $this->getPath())
			->addXmlTag('dir', Core_Page::instance()->structureCDN . '/' . $this->getDirHref());

		$this->type != 3
			&& $this->addForbiddenTag('url');

		if ($this->_showXmlProperties)
		{
			$this->addEntities($this->getPropertyValues(TRUE, array(), $this->_xmlSortPropertiesValues));
		}

		return $this;
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
				? $this->Structure->getSiteuserGroupId()
				: 0;
		}
		else
		{
			$result = $this->siteuser_group_id;
		}

		return intval($result);
	}

	/**
	 * Search indexation
	 * @return Search_Page
	 * @hostcms-event structure.onBeforeIndexing
	 * @hostcms-event structure.onAfterIndexing
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

		$oSearch_Page->text = htmlspecialchars($this->name) . ' ' .
			$this->id . ' ' .
			htmlspecialchars($this->seo_title) . ' ' .
			htmlspecialchars($this->seo_description) . ' ' .
			htmlspecialchars($this->seo_keywords) . ' ' .
			htmlspecialchars($this->path) . ' ';

		$oSearch_Page->title = strlen($this->seo_title) > 0
			? $this->seo_title
			: $this->name;

		// Для динамических страниц дата ставится текущая
		$date = date('Y-m-d H:i:s');

		// Страница статичная
		if ($this->type == 0)
		{
			$date = $this->Document->datetime;
			$oSearch_Page->text .= $this->Document->text . ' ';
		}

		if (Core::moduleIsActive('informationsystem'))
		{
			$oInformationsystem = Core_Entity::factory('Informationsystem')->getByStructure_id($this->id, FALSE);
			if ($oInformationsystem)
			{
				$oSearch_Page->text .= htmlspecialchars($oInformationsystem->name) . ' ' . $oInformationsystem->description . ' ';
			}
		}

		if (Core::moduleIsActive('shop'))
		{
			$oShop = Core_Entity::factory('Shop')->getByStructure_id($this->id, FALSE);
			if ($oShop)
			{
				$oSearch_Page->text .= htmlspecialchars($oShop->name) . ' ' .
					$oShop->description . ' ';
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
							$oSearch_Page->text .= htmlspecialchars($oInformationsystem_Item->name) . ' ';
						}
					}
				}
				// Other type
				elseif ($oPropertyValue->Property->type != 2)
				{
					$oSearch_Page->text .= htmlspecialchars($oPropertyValue->value) . ' ';
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

		$oSiteAlias = $this->Site->getCurrentAlias();
		if ($oSiteAlias)
		{
			$oSearch_Page->url = ($this->https ? 'https://' : 'http://') . $oSiteAlias->name . $this->getPath();
		}
		else
		{
			return NULL;
		}

		$oSearch_Page->size = mb_strlen($oSearch_Page->text);
		$oSearch_Page->site_id = $this->site_id;
		$oSearch_Page->datetime = $date;
		$oSearch_Page->module = 0;
		$oSearch_Page->module_id = $this->site_id;
		$oSearch_Page->inner = 0;
		$oSearch_Page->module_value_type = 0; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id

		$oSearch_Page->siteuser_groups = array($this->getSiteuserGroupId());

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		return $oSearch_Page;
	}

	/**
	 * Set SEO info to page from structure node
	 * @param Core_Page $oCore_Page page
	 * @return self
	 * @hostcms-event structure.onAfterSetCorePageSeo
	 */
	public function setCorePageSeo(Core_Page $oCore_Page)
	{
		$sTitle = trim($this->seo_title) != ''
			? $this->seo_title
			: $this->name;

		$sDescription = trim($this->seo_description) != ''
			? $this->seo_description
			: $this->name;

		$sKeywords = trim($this->seo_keywords) != ''
			? $this->seo_keywords
			: $this->name;

		$oCore_Page
			->title($sTitle)
			->description($sDescription)
			->keywords($sKeywords);

		Core_Event::notify($this->_modelName . '.onAfterSetCorePageSeo', $this, array($oCore_Page));

		return $this;
	}

	/**
	 * Get related object by type
	 * @hostcms-event structure.onBeforeGetRelatedObjectByType
	 * @hostcms-event structure.onAfterGetRelatedObjectByType
	 * @return object
	 */
	public function getRelatedObjectByType()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedObjectByType', $this);

		// Статичная страница
		if ($this->type == 0)
		{
			$return = $this->Document;
		}
		elseif ($this->type == 1)
		{
			$return = $this;
		}
		else
		{
			// Типовая динамическая страница
			$return = $this->Lib;
		}

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedObjectByType', $this, array(& $return));

		return $return;
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
	 * Clear tagged cache
	 * @return self
	 */
	public function clearCache()
	{
		if (Core::moduleIsActive('cache'))
		{
			Core_Cache::instance(Core::$mainConfig['defaultCache'])
				->deleteByTag('structure_' . $this->id)
				->deleteByTag('structure_' . $this->parent_id);
		}

		return $this;
	}

	/**
	 * Check and correct duplicate path
	 * @return self
	 */
	public function checkDuplicatePath()
	{
		$oSameStructures = Core_Entity::factory('Structure');
		$oSameStructures->queryBuilder()
			->where('site_id', '=', $this->site_id)
			->where('parent_id', '=', $this->parent_id)
			->where('path', 'LIKE', $this->path)
			->where('id', '!=', $this->id)
			->limit(1);

		$aSameStructures = $oSameStructures->findAll(FALSE);

		if (count($aSameStructures))
		{
			$this->path = Core_Guid::get();
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
				'structure_menu_id' => $this->structure_menu_id,
				'template_id' => $this->template_id,
				'site_id' => $this->site_id,
				'lib_id' => $this->lib_id,
				'parent_id' => $this->parent_id,
				'options' => $this->options,
				'name' => $this->name,
				'seo_title' => $this->seo_title,
				'seo_description' => $this->seo_description,
				'seo_keywords' => $this->seo_keywords,
				'show' => $this->show,
				'url' => $this->url,
				'sorting' => $this->sorting,
				'path' => $this->path,
				'type' => $this->type,
				'siteuser_group_id' => $this->siteuser_group_id,
				'https' => $this->https,
				'active' => $this->active,
				'indexing' => $this->indexing,
				'changefreq' => $this->changefreq,
				'priority' => $this->priority,
				'user_id' => $this->user_id
			);

			if ($this->type == 1)
			{
				$aBackup['structureFile'] = $this->getStructureFile();
				$aBackup['structureConfigFile'] = $this->getStructureConfigFile();
			}

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
				$this->structure_menu_id = Core_Array::get($aBackup, 'structure_menu_id');
				$this->template_id = Core_Array::get($aBackup, 'template_id');
				$this->site_id = Core_Array::get($aBackup, 'site_id');
				$this->lib_id = Core_Array::get($aBackup, 'lib_id');
				$this->parent_id = Core_Array::get($aBackup, 'parent_id');
				$this->options = Core_Array::get($aBackup, 'options');
				$this->name = Core_Array::get($aBackup, 'name');
				$this->seo_title = Core_Array::get($aBackup, 'seo_title');
				$this->seo_description = Core_Array::get($aBackup, 'seo_description');
				$this->seo_keywords = Core_Array::get($aBackup, 'seo_keywords');
				$this->show = Core_Array::get($aBackup, 'show');
				$this->url = Core_Array::get($aBackup, 'url');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->path = Core_Array::get($aBackup, 'path');
				$this->type = Core_Array::get($aBackup, 'type');
				$this->siteuser_group_id = Core_Array::get($aBackup, 'siteuser_group_id');
				$this->https = Core_Array::get($aBackup, 'https');
				$this->active = Core_Array::get($aBackup, 'active');
				$this->indexing = Core_Array::get($aBackup, 'indexing');
				$this->changefreq = Core_Array::get($aBackup, 'changefreq');
				$this->priority = Core_Array::get($aBackup, 'priority');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->save();

				if ($this->type == 1)
				{
					$this->saveStructureFile($aBackup['structureFile']);
					$this->saveStructureConfigFile($aBackup['structureConfigFile']);
				}
			}
		}

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event structure.onBeforeGetRelatedSite
	 * @hostcms-event structure.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function typeBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		switch($this->type)
		{
			case 0:
				$icon = 'fa fa-file-o';
			break;
			case 1:
				$icon = 'fa fa-file-code';
			break;
			case 2:
				$icon = 'fa fa-list-alt';
			break;
			case 3:
				$icon = 'fa fa-link';
			break;
			default:
				$icon = '—';
		}

		return '<i class="' . $icon . '">';
	}
}