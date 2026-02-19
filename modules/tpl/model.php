<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tpl_Model
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Tpl_Model extends Core_Entity
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
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'tpl_dir' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'tpls.sorting' => 'ASC',
		'tpls.name' => 'ASC'
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
	 * Get tpl file path
	 * @return string
	 */
	public function getTplFilePath()
	{
		return CMS_FOLDER . "hostcmsfiles/tpl/" . intval($this->id) . ".tpl";
	}

	/**
	 * Get tpl config language path
	 * @param string $lng
	 * @return string
	 */
	public function getLngConfigPath($lng)
	{
		return CMS_FOLDER . "hostcmsfiles/tpl/" . intval($this->id) . "." . $lng . ".config";
	}

	/**
	 * Load tpl config language file content
	 * @param string $lng
	 * @return string|NULL
	 */
	public function loadLngConfigFile($lng)
	{
		$path = $this->getLngConfigPath($lng);

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Specify tpl config language file content
	 * @param string $lng
	 * @param string $content content
	 */
	public function saveLngConfigFile($lng, $content)
	{
		$this->save();

		$content = trim($content);
		Core_File::write($this->getLngConfigPath($lng), $content);
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event tpl.onBeforeRedeclaredDelete
	 * @hostcms-event tpl.onAfterDeleteTplFile
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		// Удаляем файл
		$filename = $this->getTplFilePath();

		try
		{
			Core_File::delete($filename);
		} catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteTplFile', $this);

		return parent::delete($primaryKey);
	}

	/**
	 * Specify tpl file content
	 * @param string $content content
	 */
	public function saveTplFile($content)
	{
		$this->save();

		$content = trim($content);
		Core_File::write($this->getTplFilePath(), $content);
	}

	/**
	 * Load tpl file content
	 * @return string|NULL
	 */
	public function loadTplFile()
	{
		$path = $this->getTplFilePath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event tpl.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			Core_File::copy($this->getTplFilePath(), $newObject->getTplFilePath());
		}
		catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Search indexation
	 * @return Search_Page_Model
	 * @hostcms-event tpl.onBeforeIndexing
	 * @hostcms-event tpl.onAfterIndexing
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
		$oSearch_Page->site_id = 0; // Tpl не принадлежит сайту
		$oSearch_Page->datetime = date('Y-m-d H:i:s');
		$oSearch_Page->module = 8;
		$oSearch_Page->module_id = 0;
		$oSearch_Page->inner = 1;
		$oSearch_Page->module_value_type = 0; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id
		$oSearch_Page->url = 'tpl-' . $this->id; // Уникальный номер
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
				'tpl_dir_id' => $this->tpl_dir_id,
				'description' => $this->description,
				'tpl' => $this->loadTplFile(),
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
				$this->tpl_dir_id = Core_Array::get($aBackup, 'tpl_dir_id');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->save();

				$this->saveTplFile(Core_Array::get($aBackup, 'tpl'));
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
		return '<a target="_blank" href="' . $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportTpls', NULL, 1, intval($this->id), 'tpl_dir_id=' . Core_Array::getGet('tpl_dir_id')) . '"><i class="fa fa-upload"></i></a>';
	}
}