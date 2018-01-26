<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Model
 *
 * @package HostCMS
 * @subpackage Template
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $img = 1;

	/**
	 * Backend property
	 * @var int
	 */
	public $template_sections = 1;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'template_dir' => array(),
		'template' => array(),
		'site' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'template' => array(),
		'template_section' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'less' => 0,
		'sorting' => 0,
		'data_template_id' => 0
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'templates.sorting' => 'ASC'
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
		}
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event template.onBeforeExecute
	 * @hostcms-event template.onAfterExecute
	 */
	public function execute()
	{
		// Совместимость с HostCMS 5
		if (defined('USE_HOSTCMS_5') && USE_HOSTCMS_5)
		{
			$kernel = & singleton('kernel');
		}

		Core_Event::notify($this->_modelName . '.onBeforeExecute', $this);

		include $this->getTemplateFilePath();

		Core_Event::notify($this->_modelName . '.onAfterExecute', $this);

		return $this;
	}

	/**
	 * Get all site templates
	 * @param int $site_id site ID
	 * @return array
	 */
	public function getBySiteId($site_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('site_id', '=', $site_id)
			->orderBy('name');

		return $this->findAll();
	}

	/**
	 * Get template file path
	 * @return string
	 */
	public function getTemplateFilePath()
	{
		return CMS_FOLDER . $this->_getDir() . '/template.htm';
	}

	/**
	 * Specify template content
	 * @param string $content content
	 * @return self
	 */
	public function saveTemplateFile($content)
	{
		$this->save();
		$this->_createDir();
		Core_File::write($this->getTemplateFilePath(), trim($content));
		return $this;
	}

	/**
	 * Update Timestamp
	 * @return self
	 */
	public function updateTimestamp()
	{
		$this->timestamp = Core_Date::timestamp2sql(time());
		$this->save();

		$aTemplates = $this->Templates->findAll(FALSE);
		foreach ($aTemplates as $oTemplate)
		{
			$oTemplate->updateTimestamp();
		}

		return $this;
	}

	/**
	 * Get template
	 * @return string|NULL
	 */
	public function loadTemplateFile()
	{
		$path = $this->getTemplateFilePath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get directory for template
	 * @return string
	 */
	protected function _getDir()
	{
		return 'templates/template' . intval($this->id);
	}

	/**
	 * Get href to template's CSS file
	 * @return string
	 */
	public function getTemplateCssFileHref()
	{
		return '/' . $this->_getDir() . '/style.css';
	}

	/**
	 * Get path to template's CSS file
	 * @return string
	 */
	public function getTemplateCssFilePath()
	{
		return CMS_FOLDER . $this->_getDir() . '/style.css';
	}

	/**
	 * Specify CSS for template
	 * @param string $content CSS
	 * @return self
	 */
	public function saveTemplateCssFile($content)
	{
		$this->save();
		$this->_createDir();

		Core_File::write($this->getTemplateCssFilePath(), trim($content));

		return $this;
	}

	/**
	 * Get CSS for template
	 * @return string|NULL
	 */
	public function loadTemplateCssFile()
	{
		$path = $this->getTemplateCssFilePath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get href to template's LESS file
	 * @return string
	 */
	public function getTemplateLessFileHref()
	{
		return '/' . $this->_getDir() . '/style.less';
	}

	/**
	 * Get path to template's LESS file
	 * @return string
	 */
	public function getTemplateLessFilePath()
	{
		return CMS_FOLDER . $this->_getDir() . '/style.less';
	}

	/**
	 * Specify LESS for template and rebuild CSS
	 * @param string $content LESS
	 * @return self
	 */
	public function saveTemplateLessFile($content)
	{
		$this->save();
		$this->_createDir();

		Core_File::write($this->getTemplateLessFilePath(), trim($content));

		// Rebuild CSS
		$oTemplate_Less = $this->_getTemplateLess();
		$css = $oTemplate_Less->compile($content);
		$this->saveTemplateCssFile($css);

		return $this;
	}

	/**
	 * Get LESS for template
	 * @return string|NULL
	 */
	public function loadTemplateLessFile()
	{
		$path = $this->getTemplateLessFilePath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get href to template's JS file
	 * @return string
	 */
	public function getTemplateJsFileHref()
	{
		return '/' . $this->_getDir() . '/script.js';
	}

	/**
	 * Get path to template's JS file
	 * @return string
	 */
	public function getTemplateJsFilePath()
	{
		return CMS_FOLDER . $this->_getDir() . '/script.js';
	}

	/**
	 * Specify JS for template
	 * @param string $content JS
	 * @return self
	 */
	public function saveTemplateJsFile($content)
	{
		$this->save();
		$this->_createDir();
		Core_File::write($this->getTemplateJsFilePath(), trim($content));
		return $this;
	}

	/**
	 * Get JS for template
	 * @return string|NULL
	 */
	public function loadTemplateJsFile()
	{
		$path = $this->getTemplateJsFilePath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get manifest path
	 * @return string
	 */
	public function getManifestPath()
	{
		return CMS_FOLDER . $this->_getDir() . '/manifest.xml';
	}

	/**
	 * Get manifest file content
	 * @return string|NULL
	 */
	public function loadManifestFile()
	{
		$path = $this->getManifestPath();

		return is_file($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Specify manifest file content
	 * @param string $content content
	 */
	public function saveManifestFile($content)
	{
		$this->save();

		$content = trim($content);
		Core_File::write($this->getManifestPath(), $content);
	}

	/**
	 * Create directory for template
	 * @return self
	 */
	protected function _createDir()
	{
		$sDirPath = dirname($this->getTemplateFilePath());

		if (!is_dir($sDirPath))
		{
			try
			{
				Core_File::mkdir($sDirPath, CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Get parent template
	 * @return Template_Model|NULL
	 */
	public function getParent()
	{
		if ($this->template_id)
		{
			return Core_Entity::factory('Template', $this->template_id);
		}
		return NULL;
	}

	/**
	 * Used when transferring templates for layouts
	 * Используется при переносе шаблонов к макетам
	 * @param int $data_template_id template ID
	 * @return Template_Model|NULL
	 */
	public function getByDataTemplateId($data_template_id)
	{
		$oTemplates = $this->Templates;
		$oTemplates->queryBuilder()
			//->clear()
			->where('data_template_id', '=', $data_template_id)
			->limit(1);

		$aTemplates = $oTemplates->findAll(FALSE);

		return isset($aTemplates[0])
			? $aTemplates[0]
			: NULL;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event template.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		// Удаляем файл макета
		try
		{
			$path = $this->getTemplateFilePath();
			is_file($path) && Core_File::delete($path);
		}
		catch (Exception $e) {}

		try
		{
			$path = $this->getTemplateCssFilePath();
			is_file($path) && Core_File::delete($path);
		}
		catch (Exception $e) {}

		try
		{
			is_dir(CMS_FOLDER . $this->_getDir()) && Core_File::deleteDir(CMS_FOLDER . $this->_getDir());
		}
		catch (Exception $e) {}

		$this->Templates->deleteAll(FALSE);
		$this->Template_Sections->deleteAll(FALSE);

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$newObject->saveTemplateCssFile($this->loadTemplateCssFile());
		$newObject->saveTemplateFile($this->loadTemplateFile());

		$aTemplates = $this->Templates->findAll();

		foreach ($aTemplates as $oTemplate)
		{
			$subTemplate = $oTemplate->copy();
			$subTemplate->template_id = $newObject->id;
			$subTemplate->save();
			//$newObject->add();
		}

		return $newObject;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Templates->getCount();
		$count > 0 && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-hostcms badge-square')
			->value($count)
			->execute();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function template_sectionsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Template_Sections->getCount();

		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-ico badge-darkorange white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Rebuild Compression Css
	 * @return self
	 */
	public function rebuildCompressionCss()
	{
		// Обновляем сохраненные минимизированные CSS
		if (Core::moduleIsActive('compression'))
		{
			$oCompression_Controller = Compression_Controller::instance('css');

			$sTemplatePath = $this->getTemplateCssFileHref();

			$oCompression_Css = Core_Entity::factory('Compression_Css');
			$oCompression_Css
				->queryBuilder()
				->where('path', 'LIKE', $sTemplatePath)
				->groupBy('filename');

			$aCompression_Css_With_Path = $oCompression_Css->findAll(FALSE);

			foreach ($aCompression_Css_With_Path as $oCompression_Css)
			{
				$oCompression_Controller->clear();

				$aCompression_Css = Core_Entity::factory('Compression_Css')->getAllByFilename(
					$oCompression_Css->filename
				);

				// Все файлы, использованные при создании этого CSS
				foreach ($aCompression_Css as $oTmpCompression_Css)
				{
					$oCompression_Controller->addCss(
						$oTmpCompression_Css->path
					);
				}

				$oCompression_Controller->buildCss($oCompression_Css->filename, TRUE);
			}
		}

		return $this;
	}

	/**
	 * checkUserAccess cache
	 * @var boolean
	 */
	protected $_checkUserAccess = NULL;

	/**
	 * Check current user acccess
	 * @return boolean
	 */
	public function checkUserAccess()
	{
		if (is_null($this->_checkUserAccess))
		{
			if (Core::checkPanel() && Core_Auth::logged())
			{
				$oUser = Core_Entity::factory('User')->getCurrent();
				$this->_checkUserAccess = $oUser->checkModuleAccess(array('template'), $this->Site)
					&& $oUser->checkObjectAccess($this);
			}
			else
			{
				$this->_checkUserAccess = FALSE;
			}
		}

		return $this->_checkUserAccess;
	}

	/**
	 * Show Section by Name
	 * @param string $sectionName
	 * @return self
	 */
	public function showSection($sectionName)
	{
		$oTemplate_Section = $this->Template_Sections->getByAlias($sectionName);

		if (!is_null($oTemplate_Section))
		{
			//$bUserAccess = $this->checkUserAccess();
			$bUserAccess = Core::checkPanel() && Core_Auth::logged();

			if ($bUserAccess)
			{
				// Настройки секции
				$sPath = '/admin/template/section/index.php';
				$sAdditionalSectionSettings = "hostcms[action]=edit&template_id={$this->id}&template_dir_id={$this->Template_Dir->id}&hostcms[checked][0][{$oTemplate_Section->id}]=1";
				$sOnclickSectionSettings = "hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditionalSectionSettings}', dialogClass: 'hostcms6'}); return false";
				$sTitleSectionSettings = htmlspecialchars(Core::_('Template_Section.section_settings', $oTemplate_Section->name));

				// Добавление виджета в секцию
				$sPathAddWidget = '/admin/template/section/lib/index.php';
				$sAdditionalAddWidget = "hostcms[action]=edit&template_section_id={$oTemplate_Section->id}&hostcms[checked][0][0]=1";
				$sOnclickAddWidget = "hQuery.openWindow({path: '{$sPathAddWidget}', additionalParams: '{$sAdditionalAddWidget}', dialogClass: 'hostcms6'}); return false";
				$sTitleAddWidget = Core::_('Template_Section.add_widget');
				?>

				<div class="hostcmsSection" id="hostcmsSection<?php echo $oTemplate_Section->id?>" style="border-color: <?php echo Core_Str::hex2rgba($oTemplate_Section->color, 0.8)?>">
					<div class="hostcmsSectionPanel">
						<div class="draggable-indicator">
							<svg width="16px" height="16px" viewBox="0 0 32 32">
								<rect height="4" width="4" y="4" x="4" />
								<rect height="4" width="4" y="12" x="4" />
								<rect height="4" width="4" y="4" x="12"/>
								<rect height="4" width="4" y="12" x="12"/>
								<rect height="4" width="4" y="4" x="20"/>
								<rect height="4" width="4" y="12" x="20"/>
								<rect height="4" width="4" y="4" x="28"/>
								<rect height="4" width="4" y="12" x="28"/>
							</svg>
						</div>
						<div><a href="<?php echo "{$sPathAddWidget}?{$sAdditionalAddWidget}"?>" onclick="<?php echo $sOnclickAddWidget ?>" alt="<?php echo $sTitleAddWidget ?>" title="<?php echo $sTitleAddWidget ?>"><i class="fa fa-fw fa-plus"></i></a></div>

						<div><a href="<?php echo "{$sPath}?{$sAdditionalSectionSettings}"?>" onclick="<?php echo $sOnclickSectionSettings ?>" alt="<?php echo $sTitleSectionSettings ?>" title="<?php echo $sTitleSectionSettings ?>"><i class="fa fa-fw fa-cog"></i></a></div>
					</div>
				<?php
			}

			$oTemplate_Section_Libs = $oTemplate_Section->Template_Section_Libs;
			$oTemplate_Section_Libs->queryBuilder()
				//->where('template_section_libs.active', '=', 1)
				->clearOrderBy()
				->orderBy('template_section_libs.sorting', 'ASC');

			$aTemplate_Section_Libs = $oTemplate_Section_Libs->findAll(FALSE);

			foreach ($aTemplate_Section_Libs as $oTemplate_Section_Lib)
			{
				$oTemplate_Section_Lib->execute();
			}

			if ($bUserAccess)
			{
				?></div><?php
			}
		}
		else
		{
			throw new Core_Exception('Section %name does not exist!', array('%name' => $sectionName));
		}
	}

	protected $_lessVariables = NULL;

	/**
	 * Get Template_Less
	 * @return Template_Less
	 */
	protected function _getTemplateLess()
	{
		$oTemplate_Less = new Template_Less();
		$oTemplate_Less->setImportDir(array(CMS_FOLDER));
		return $oTemplate_Less;
	}

	public function showManifest()
	{
		if ($this->less)
		{
			$manifest = $this->loadManifestFile();

			if (strlen($manifest))
			{
				$less = $this->loadTemplateLessFile();

				if (strlen($less))
				{
					try
					{
						$oTemplate_Less = $this->_getTemplateLess();
						$oTemplate_Less->compile($less);
						$this->_lessVariables = $oTemplate_Less->getVariables();
					}
					catch (Exception $e)
					{
						Core_Message::show($e->getMessage(), 'error');
					}

					// print_r($this->_lessVariables);

					?><div class="row panel-heading">
						<div class="col-xs-12"><?php echo htmlspecialchars($this->name)?></div>
					</div><?php

					$oXml = @simplexml_load_string($manifest);

					if (is_object($oXml))
					{
						$this->_parseManifest($oXml);
					}
				}
			}
		}
	}

	protected function _parseManifest($oXml)
	{
		$aSections = $oXml->xpath('section');

		foreach ($aSections as $oSection)
		{
			// Отображение секции только при наличии в ней опций
			if (count($oSection->xpath('option')))
			{
				$oSectionName = $oSection->xpath('caption[@lng="' . 'ru' .'"]');
				if (isset($oSectionName[0]))
				{
					?><div class="row panel-section-heading">
						<div class="col-xs-12">
							<?php echo strval($oSectionName[0])?>
						</div>
					</div>
					<?php
				}

				$this->_parseManifest($oSection);
			}
		}

		$aOptions = $oXml->xpath('option');

		foreach ($aOptions as $oOption)
		{
			$oOptionName = $oOption->xpath('caption[@lng="' . 'ru' .'"]');

			if (isset($oOptionName[0]))
			{
				$fieldName = strval($oOption->attributes()->name);
				$fieldType = strval($oOption->attributes()->type);

				if (isset($this->_lessVariables[$fieldName]))
				{
					$lessFieldValue = $this->_lessVariables[$fieldName]['value'];
					$lessFieldType = $this->_lessVariables[$fieldName]['type'];
				}
				else
				{
					$lessFieldValue = $lessFieldType = NULL;
				}

				if (!is_array($lessFieldValue))
				{
					?><div class="row panel-item">
						<div class="col-xs-12">
							<label for="<?php echo $fieldName?>"><?php echo strval($oOptionName[0])?></label>
							<input type="text" class="form-control <?php echo $fieldType == 'color' ? 'colorpicker' : ''?>" name="<?php echo $fieldName?>" value="<?php echo htmlspecialchars($lessFieldValue)?>" <?php echo $fieldType == 'color' && ($lessFieldType == 'rgb' || $lessFieldType == 'rgba') ? 'data-format="rgb"' : '' ?> <?php echo $fieldType == 'color' && $lessFieldType == 'rgba' ? 'data-rgba="true"' : '' ?> data-template="<?php echo $this->id ?>" />
						</div>
					</div>
					<?php
				}
			}
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
				'template_dir_id' => $this->template_dir_id,
				'template_id' => $this->template_id,
				'sorting' => $this->sorting,
				'template' => $this->loadTemplateFile(),
				'css' => $this->loadTemplateCssFile(),
				'less' => $this->loadTemplateLessFile(),
				'js' => $this->loadTemplateJsFile(),
				'manifest' => $this->loadManifestFile(),
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
				$this->save();

				$this->saveTemplateFile(Core_Array::get($aBackup, 'template'));
				$this->saveTemplateLessFile(Core_Array::get($aBackup, 'less'));
				$this->saveTemplateCssFile(Core_Array::get($aBackup, 'css'));
				$this->saveTemplateJsFile(Core_Array::get($aBackup, 'js'));
				$this->saveManifestFile(Core_Array::get($aBackup, 'manifest'));
			}
		}

		return $this;
	}
}