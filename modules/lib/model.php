<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Model
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Lib_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $properties = 1;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'lib_dir' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'lib_property' => array(),
		'template_section_lib' => array()
	);

	/**
	 * Has revisions
	 *
	 * @var boolean
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
		}
	}

	/**
	 * Get href for files
	 * @return string
	 */
	public function getHref()
	{
		return 'upload/libs/lib_' . intval($this->id) . '/';
	}

	/**
	 * Get path for files
	 * @return string
	 */
	public function getPath()
	{
		return CMS_FOLDER . $this->getHref();
	}

	/**
	 * Get file path
	 * @return string
	 */
	public function getFilePath()
	{
		return $this->getPath() . $this->file;
	}

	/**
	 * Get file href
	 * @return string
	 */
	public function getFileHref()
	{
		return '/' . $this->getHref() . $this->file;
	}

	/**
	 * Create files directory
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

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
	 * Save file
	 * @param string $fileSourcePath source file
	 * @param string $fileName file name
	 * @return self
	 */
	public function saveFile($fileSourcePath, $fileName)
	{
		$this->createDir();

		$filePath = $this->getFilePath();

		// Delete old file
		if ($this->file != '' && Core_File::isFile($filePath))
		{
			$this->deleteFile();
		}

		$this->file = $this->id . '.' . Core_File::getExtension(basename($fileName));
		$this->save();

		Core_File::upload($fileSourcePath, $this->getFilePath());

		return $this;
	}

	/**
	 * Delete file
	 * @return self
	 * @hostcms-event lib.onAfterDeleteFile
	 */
	public function deleteFile()
	{
		try
		{
			Core_File::delete($this->getFilePath());
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteFile', $this);

		$this->file = '';
		$this->save();

		return $this;
	}

	/**
	 * Get lib's directory path
	 * @return string
	 */
	public function getLibPath()
	{
		return CMS_FOLDER . "hostcmsfiles/lib/lib_" . intval($this->id) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Get lib file path
	 * @return string
	 */
	public function getLibFilePath()
	{
		return $this->getLibPath() . "lib_" . intval($this->id) . ".php";
	}

	/**
	 * Get configuration file path
	 * @return string
	 */
	public function getLibConfigFilePath()
	{
		return $this->getLibPath() . "lib_config_" . intval($this->id) . ".php";
	}

	/**
	 * Get dat file path
	 * @param int $structure_id structure id
	 * @return string
	 */
	public function getLibDatFilePath($structure_id)
	{
		return $this->getLibPath() . "lib_values_" . intval($structure_id) . ".dat";
	}

	/**
	 * Save dat file
	 * @param array $array data
	 * @param int $structure_id structure id
	 */
	public function saveDatFile(array $array, $structure_id)
	{
		$this->save();

		$oStructure = Core_Entity::factory('Structure', $structure_id);
		$oStructure->options = json_encode($array);
		$oStructure->save();
	}

	/**
	 * Get array for options
	 * @param int $structure_id structure id
	 * @return array
	 */
	public function getDat($structure_id)
	{
		$return = array();

		$oStructure = Core_Entity::factory('Structure', $structure_id);

		if (!is_null($oStructure->options))
		{
			$return = json_decode($oStructure->options, TRUE);
		}
		// Backward compatibility
		else
		{
			$datContent = $this->loadDatFile($structure_id);
			if ($datContent)
			{
				$array = @unserialize(strval($datContent));
				$return = Core_Type_Conversion::toArray($array);
			}
		}

		return $return;
	}

	/**
	 * Read dat file content
	 * @param int $structure_id structure id
	 * @return string|NULL
	 */
	public function loadDatFile($structure_id)
	{
		$path = $this->getLibDatFilePath($structure_id);

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
     * @hostcms-event lib.onBeforeRedeclaredDelete
	 * @hostcms-event lib.onAfterDeleteLibFile
	 * @hostcms-event lib.onAfterDeleteLibConfigFile
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

		// Удаляем код и настройки
		try
		{
			Core_File::delete($this->getLibFilePath());
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteLibFile', $this);

		try
		{
			Core_File::delete($this->getLibConfigFilePath());
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteLibConfigFile', $this);

		try
		{
			Core_File::deleteDir($this->getLibPath());
		} catch (Exception $e) {}

		$this->Lib_Properties->deleteAll(FALSE);

		$this->deleteFile();

		return parent::delete($primaryKey);
	}

	/**
	 * Save lib content
	 * @param string $content content
	 */
	public function saveLibFile($content)
	{
		$this->save();

		Core_File::mkdir(dirname($sLibFilePath = $this->getLibFilePath()), CHMOD, TRUE);

		$content = trim($content);
		Core_File::write($sLibFilePath, $content);

		clearstatcache();

		Core_Cache::opcacheReset();
	}

	/**
	 * Save config content
	 * @param string $content content
	 */
	public function saveLibConfigFile($content)
	{
		$this->save();

		Core_File::mkdir(dirname($sLibConfigFilePath = $this->getLibConfigFilePath()), CHMOD, TRUE);

		$content = trim($content);
		Core_File::write($sLibConfigFilePath, $content);
	}

	/**
	 * Get lib file content
	 * @return string|NULL
	 */
	public function loadLibFile()
	{
		$path = $this->getLibFilePath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get config file content
	 * @return string|NULL
	 */
	public function loadLibConfigFile()
	{
		$path = $this->getLibConfigFilePath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event lib.onBeforeExecute
	 * @hostcms-event lib.onAfterExecute
	 */
	public function execute()
	{
		//$bLogged = Core_Auth::logged();
		//$bLogged && $fBeginTimeConfig = Core::getmicrotime();

		Core_Event::notify($this->_modelName . '.onBeforeExecute', $this);

		include $this->getLibFilePath();

		Core_Event::notify($this->_modelName . '.onAfterExecute', $this);

		/*$bLogged && Core_Page::instance()->addFrontendExecutionTimes(
			Core::_('Core.time_page', Core::getmicrotime() - $fBeginTimeConfig)
		);*/

		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event lib.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			Core_File::copy($this->getLibFilePath(), $newObject->getLibFilePath());
		} catch (Exception $e) {}

		try
		{
			Core_File::copy($this->getLibConfigFilePath(), $newObject->getLibConfigFilePath());
		} catch (Exception $e) {}

		// $aLib_Properties = $this->Lib_Properties->findAll();

		// foreach ($aLib_Properties as $oLib_Property)
		// {
		// 	$newObject->add($oLib_Property->copy());
		// }

		$aTmp = array();

		$aLib_Properties = $this->Lib_Properties->findAll(FALSE);
		foreach ($aLib_Properties as $oLib_Property)
		{
			$oNew_Lib_Property = clone $oLib_Property;
			$newObject->add($oNew_Lib_Property);

			$aTmp[$oLib_Property->id] = $oNew_Lib_Property->id;

			$aLib_Property_List_Values = $oLib_Property->Lib_Property_List_Values->findAll(FALSE);
			foreach ($aLib_Property_List_Values as $oLib_Property_List_Value)
			{
				$newObject->add(clone $oLib_Property_List_Value);
			}
		}

		$aNew_Lib_Properties = $newObject->Lib_Properties->findAll(FALSE);
		foreach ($aNew_Lib_Properties as $oLib_Property)
		{
			$oLib_Property->parent_id = Core_Array::get($aTmp, $oLib_Property->parent_id, 0);
			$oLib_Property->save();
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Search indexation
	 * @return Search_Page_Model
	 * @hostcms-event xsl.onBeforeIndexing
	 * @hostcms-event xsl.onAfterIndexing
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

		$oSearch_Page->text = $this->name . ' ' . $this->description;

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

		$oSearch_Page->size = mb_strlen($oSearch_Page->text);
		$oSearch_Page->site_id = 0; // Lib не принадлежит сайту
		$oSearch_Page->datetime = date('Y-m-d H:i:s');
		$oSearch_Page->module = 9;
		$oSearch_Page->module_id = 0;
		$oSearch_Page->inner = 1;
		$oSearch_Page->module_value_type = 0; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id
		$oSearch_Page->url = 'lib-' . $this->id; // Уникальный номер
		$oSearch_Page->siteuser_groups = array(0);

		Core_Event::notify($this->_modelName . '.onAfterIndexing', $this, array($oSearch_Page));

		return $oSearch_Page;
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
				'lib_dir_id' => $this->lib_dir_id,
				'description' => $this->description,
				'lib' => $this->loadLibFile(),
				'lib_config' => $this->loadLibConfigFile(),
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
				$this->lib_dir_id = Core_Array::get($aBackup, 'lib_dir_id');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->save();

				$this->saveLibFile(Core_Array::get($aBackup, 'lib'));
				$this->saveLibConfigFile(Core_Array::get($aBackup, 'lib_config'));
			}
		}

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function exportBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<a target="_blank" href="' . $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportLibs', NULL, 1, intval($this->id), 'lib_dir_id=' . Core_Array::getGet('lib_dir_id')) . '"><i class="fa fa-upload"></i></a>';
	}

	/**
	 * Show section fields attrs
	 * @param string $fieldName
	 * @return string
	 */
	public function showSectionFieldAttrs($fieldName, $position = 0, $prefix = '')
	{
		$userAccess = Core_Page::instance()->template->checkUserAccess();

		$bShow = isset($_GET['hostcmsAction']) && $_GET['hostcmsAction'] == 'SHOW_DESIGN';

		if ($userAccess && $bShow)
		{
			$oTemplate_Section_Lib = Core_Page::instance()->templateSectionLib;

			$return = ' data-editable-' . $oTemplate_Section_Lib->id . '="' . htmlspecialchars($fieldName) . '"';

			if ($position && $prefix != '')
			{
				$return .= ' data-prefix="' . htmlspecialchars($prefix) . '"';
			}

			return $return;
		}

		return '';
	}

	/**
	 * Show section fields attrs
	 * @param string $fieldName
	 * @return string
	 */
	public function showSectionFieldDesign($fieldName, $position = 0, $prefix = '')
	{
		$return = '';

		$userAccess = Core_Page::instance()->template->checkUserAccess();

		if ($userAccess) // && preg_match(, $fieldName)
		{
			$bShow = isset($_GET['hostcmsAction']) && $_GET['hostcmsAction'] == 'SHOW_DESIGN';

			$oTemplate_Section_Lib = Core_Page::instance()->templateSectionLib;

			$return = ' id="' . $oTemplate_Section_Lib->id . '_' . htmlspecialchars($fieldName) . ($position ? '_' . $position : '') . '"';

			if ($bShow)
			{
				$return .= ' data-design-' . $oTemplate_Section_Lib->id . '="' . htmlspecialchars($fieldName) . '"';
			}

			// Классы
			$aFieldClasses = $oTemplate_Section_Lib->field_classes != ''
				? json_decode($oTemplate_Section_Lib->field_classes, TRUE)
				: array();

			$field_classes = isset($aFieldClasses[$fieldName])
				? $aFieldClasses[$fieldName]
				: '';

			if ($position)
			{
				$field_classes = isset($aFieldClasses[$fieldName . '_' . $position])
					? $aFieldClasses[$fieldName . '_' . $position]
					: '';
			}

			$return .= ' class="' . $prefix . htmlspecialchars($fieldName) . ' ' . $field_classes . '"';

			// Стили
			$aFieldStyles = $oTemplate_Section_Lib->field_styles != ''
				? json_decode($oTemplate_Section_Lib->field_styles, TRUE)
				: array();

			$field_styles = isset($aFieldStyles[$fieldName])
				? $aFieldStyles[$fieldName]
				: '';

			if ($position)
			{
				$field_styles = isset($aFieldStyles[$fieldName . '_' . $position])
					? $aFieldStyles[$fieldName . '_' . $position]
					: '';
			}

			$return .= ' style="' . $field_styles . '"';

			if ($position)
			{
				$return .= ' data-position="' . $position . '"';
			}
		}

		return $return;
	}

	/**
	 * Backend badge
	 */
	public function propertiesBadge()
	{
		$count = $this->Lib_Properties->getCountByParent_id(0, FALSE);
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-azure white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}
}