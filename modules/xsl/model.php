<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Xsl_Model
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Xsl_Model extends Core_Entity
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
		'xsl_dir' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'format' => 1, 'sorting' => 0
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'xsls.sorting' => 'ASC',
		'xsls.name' => 'ASC'
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
		}
	}

	/**
	 * Get XSL file path
	 * @return string
	 */
	public function getXslFilePath()
	{
		return CMS_FOLDER . "hostcmsfiles/xsl/" . intval($this->id) . ".xsl";
	}

	/**
	 * Get DTD language path
	 * @param string $lng
	 * @return string
	 */
	public function getLngDtdPath($lng)
	{
		return CMS_FOLDER . "hostcmsfiles/xsl/" . intval($this->id) . "." . $lng . ".dtd";
	}

	/**
	 * Get DTD language file content
	 * @param string $lng
	 * @return string|NULL
	 */
	public function loadLngDtdFile($lng)
	{
		$path = $this->getLngDtdPath($lng);

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Specify XSL file content
	 * @param string $lng
	 * @param string $content content
	 */
	public function saveLngDtdFile($lng, $content)
	{
		$this->save();

		$content = trim($content);
		Core_File::write($this->getLngDtdPath($lng), $content);
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event xsl.onBeforeRedeclaredDelete
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
		$filename = $this->getXslFilePath();

		try
		{
			Core_File::delete($filename);

			// DTD
			$aLngs = Xsl_Controller::getLngs();
			foreach ($aLngs as $sLng)
			{
				$sDtdPath = $this->getLngDtdPath($sLng);

				if (is_file($sDtdPath))
				{
					Core_File::delete($sDtdPath);
				}
			}
		} catch (Exception $e) {}

		return parent::delete($primaryKey);
	}

	/**
	 * Specify XSL file content
	 * @param string $content content
	 */
	public function saveXslFile($content)
	{
		$this->save();

		$content = trim($content);
		Core_File::write($this->getXslFilePath(), $content);
	}

	/**
	 * Get XSL file content
	 * @return string|NULL
	 */
	public function loadXslFile()
	{
		$path = $this->getXslFilePath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event xsl.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		try
		{
			// XSL
			$content = Core_File::read($this->getXslFilePath());
			$content = str_replace('"lang://' . $this->id . '"', '"lang://' . $newObject->id . '"', $content);
			Core_File::write($newObject->getXslFilePath(), $content);

			// DTD
			$aLngs = Xsl_Controller::getLngs();
			foreach ($aLngs as $sLng)
			{
				$sDtd = $this->loadLngDtdFile($sLng);
				$sDtd != '' && $newObject->saveLngDtdFile($sLng, $sDtd);
			}

			//Core_File::copy($this->getXslFilePath(), $newObject->getXslFilePath());
		}
		catch (Exception $e) {}

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
		$oSearch_Page->site_id = 0; // XSL не принадлежит сайту
		$oSearch_Page->datetime = date('Y-m-d H:i:s');
		$oSearch_Page->module = 7;
		$oSearch_Page->module_id = 0;
		$oSearch_Page->inner = 1;
		$oSearch_Page->module_value_type = 0; // search_page_module_value_type
		$oSearch_Page->module_value_id = $this->id; // search_page_module_value_id
		$oSearch_Page->url = 'xsl-' . $this->id; // Уникальный номер
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
				'xsl_dir_id' => $this->xsl_dir_id,
				'description' => $this->description,
				'xsl' => $this->loadXslFile(),
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
				$this->xsl_dir_id = Core_Array::get($aBackup, 'xsl_dir_id');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->save();

				$this->saveXslFile(Core_Array::get($aBackup, 'xsl'));
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
		return '<a target="_blank" href="' . $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'exportXsls', NULL, 1, intval($this->id), 'xsl_dir_id=' . Core_Array::getGet('xsl_dir_id')) . '"><i class="fa fa-upload"></i></a>';
	}
}