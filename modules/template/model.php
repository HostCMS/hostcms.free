<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Model
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
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
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $template_sections = 1;

	/**
	 * Backward compatibility
	 * @var NULL
	 */
	public $data_template_id = NULL;

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
		'type' => 0,
		'sorting' => 0
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'templates.sorting' => 'ASC'
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
	 * Executes the business logic.
	 * @hostcms-event template.onBeforeExecute
	 * @hostcms-event template.onAfterExecute
	 */
	public function execute()
	{
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
		return CMS_FOLDER . $this->getDir() . '/template.htm';
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
		Core_File::write($this->getTemplateFilePath(), trim((string) $content));
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

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get directory for template
	 * @return string
	 */
	public function getDir()
	{
		return 'templates/template' . intval($this->id);
	}

	/**
	 * Get href to template's CSS file
	 * @return string
	 */
	public function getTemplateCssFileHref()
	{
		return '/' . $this->getDir() . '/style.css';
	}

	/**
	 * Get path to template's CSS file
	 * @return string
	 */
	public function getTemplateCssFilePath()
	{
		return CMS_FOLDER . $this->getDir() . '/style.css';
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

		Core_File::write($this->getTemplateCssFilePath(), trim((string) $content));

		return $this;
	}

	/**
	 * Get CSS for template
	 * @return string|NULL
	 */
	public function loadTemplateCssFile()
	{
		$path = $this->getTemplateCssFilePath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get href to template's LESS file
	 * @return string
	 */
	public function getTemplateLessFileHref()
	{
		return '/' . $this->getDir() . '/style.less';
	}

	/**
	 * Get path to template's LESS file
	 * @return string
	 */
	public function getTemplateLessFilePath()
	{
		return CMS_FOLDER . $this->getDir() . '/style.less';
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

		Core_File::write($this->getTemplateLessFilePath(), trim((string) $content));

		if ($this->type == 1)
		{
			// Rebuild CSS
			$css = strlen($content)
				? Template_Preprocessor::factory('less')->compile($content)
				: '';

			$this->saveTemplateCssFile($css);
		}

		return $this;
	}

	/**
	 * Get LESS for template
	 * @return string|NULL
	 */
	public function loadTemplateLessFile()
	{
		$path = $this->getTemplateLessFilePath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get href to template's SCSS file
	 * @return string
	 */
	public function getTemplateScssFileHref()
	{
		return '/' . $this->getDir() . '/style.scss';
	}

	/**
	 * Get path to template's SCSS file
	 * @return string
	 */
	public function getTemplateScssFilePath()
	{
		return CMS_FOLDER . $this->getDir() . '/style.scss';
	}

	/**
	 * Specify SCSS for template and rebuild CSS
	 * @param string $content SCSS
	 * @return self
	 */
	public function saveTemplateScssFile($content)
	{
		$this->save();
		$this->_createDir();

		Core_File::write($this->getTemplateScssFilePath(), trim((string) $content));

		if ($this->type == 2 && strlen($content))
		{
			$css = Template_Preprocessor::factory('scss')->compile($content);
			$this->saveTemplateCssFile($css);
		}

		return $this;
	}

	/**
	 * Get LESS for template
	 * @return string|NULL
	 */
	public function loadTemplateScssFile()
	{
		$path = $this->getTemplateScssFilePath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get href to template's JS file
	 * @return string
	 */
	public function getTemplateJsFileHref()
	{
		return '/' . $this->getDir() . '/script.js';
	}

	/**
	 * Get path to template's JS file
	 * @return string
	 */
	public function getTemplateJsFilePath()
	{
		return CMS_FOLDER . $this->getDir() . '/script.js';
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
		Core_File::write($this->getTemplateJsFilePath(), trim((string) $content));
		return $this;
	}

	/**
	 * Get JS for template
	 * @return string|NULL
	 */
	public function loadTemplateJsFile()
	{
		$path = $this->getTemplateJsFilePath();

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Get manifest path
	 * @return string
	 */
	public function getManifestPath()
	{
		return CMS_FOLDER . $this->getDir() . '/manifest.xml';
	}

	/**
	 * Get manifest file content
	 * @return string|NULL
	 */
	public function loadManifestFile()
	{
		$path = $this->getManifestPath();

		return Core_File::isFile($path)
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

		$content = trim((string) $content);
		Core_File::write($this->getManifestPath(), $content);
	}

	/**
	 * Create directory for template
	 * @return self
	 */
	protected function _createLngDir($lng)
	{
		$sDirPath = dirname($this->getLngPath($lng));

		if (!Core_File::isDir($sDirPath))
		{
			try
			{
				Core_File::mkdir($sDirPath, CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Get language path
	 * @param string $lng
	 * @return string
	 */
	public function getLngPath($lng)
	{
		return CMS_FOLDER . "templates/template" . intval($this->id) . "/i18n/" . $lng . ".php";
	}

	/**
	 * Get language file content
	 * @param string $lng
	 * @return string|NULL
	 */
	public function loadLngFile($lng)
	{
		$path = $this->getLngPath($lng);

		return Core_File::isFile($path)
			? Core_File::read($path)
			: NULL;
	}

	/**
	 * Set language file content
	 * @param string $lng
	 * @param string $content content
	 */
	public function saveLngFile($lng, $content)
	{
		$this->save();
		$this->_createLngDir($lng);
		$content = trim((string) $content);
		Core_File::write($this->getLngPath($lng), $content);
	}

	/**
	 * Create directory for template
	 * @return self
	 */
	protected function _createDir()
	{
		$sDirPath = dirname($this->getTemplateFilePath());

		if (!Core_File::isDir($sDirPath))
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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event template.onBeforeRedeclaredDelete
	 * @hostcms-event template.onAfterDeleteTemplateFile
	 * @hostcms-event template.onAfterDeleteTemplateCssFile
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
			Core_File::isFile($path) && Core_File::delete($path);
		}
		catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteTemplateFile', $this);

		try
		{
			$path = $this->getTemplateCssFilePath();
			Core_File::isFile($path) && Core_File::delete($path);
		}
		catch (Exception $e) {}

		Core_Event::notify($this->_modelName . '.onAfterDeleteTemplateCssFile', $this);

		try
		{
			Core_File::isDir(CMS_FOLDER . $this->getDir()) && Core_File::deleteDir(CMS_FOLDER . $this->getDir());
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
	 * @hostcms-event template.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$newObject->saveTemplateFile($this->loadTemplateFile());
		$newObject->saveTemplateCssFile($this->loadTemplateCssFile());
		$newObject->saveTemplateLessFile($this->loadTemplateLessFile());
		$newObject->saveTemplateJsFile($this->loadTemplateJsFile());
		$newObject->saveManifestFile($this->loadManifestFile());
		$newObject->saveLngFile($oSite->lng, $this->loadLngFile($oSite->lng));

		// Template_Sections
		$aTemplate_Sections = $this->Template_Sections->findAll(FALSE);

		foreach ($aTemplate_Sections as $oTemplate_Section)
		{
			$oNew_Template_Section = clone $oTemplate_Section;
			$newObject->add($oNew_Template_Section);

			// Template_Section_Libs
			$aTemplate_Section_Libs = $oTemplate_Section->Template_Section_Libs->findAll(FALSE);

			foreach ($aTemplate_Section_Libs as $oTemplate_Section_Lib)
			{
				$oNew_Template_Section_Lib = clone $oTemplate_Section_Lib;
				$oNew_Template_Section->add($oNew_Template_Section_Lib);
			}
		}

		$aTemplates = $this->Templates->findAll();

		foreach ($aTemplates as $oTemplate)
		{
			$subTemplate = $oTemplate->copy();
			$subTemplate->template_id = $newObject->id;
			$subTemplate->save();
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->loadTemplateCssFile() != '')
		{
			switch ($this->type)
			{
				case 0:
				default:
					$css = 'CSS';
				break;
				case 1:
					$css = 'LESS';
				break;
				case 2:
					$css = 'SCSS';
				break;
			}

			Core_Html_Entity::factory('Span')
				->class('label label-info')
				->value($css)
				->execute();
		}

		if ($this->loadTemplateJsFile() != '')
		{
			Core_Html_Entity::factory('Span')
				->class('label label-warning')
				->value('JS')
				->execute();
		}

		$count = $this->Templates->getCount();
		$count > 0 && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function template_sectionsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Template_Sections->getCount();

		$count && Core_Html_Entity::factory('Span')
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
				->where('path', '=', $sTemplatePath)
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
				$oUser = Core_Auth::getCurrentUser();

				try
				{
					$this->_checkUserAccess = $oUser->checkModuleAccess(array('template'), $this->Site)
						&& $oUser->checkObjectAccess($this);
				}
				catch (Exception $e)
				{
					$this->_checkUserAccess = FALSE;
				}
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
			$bUserAccess = $this->checkUserAccess();
			// $bUserAccess = Core::checkPanel() && Core_Auth::logged();

			if ($bUserAccess && isset($_GET['hostcmsAction']) && $_GET['hostcmsAction'] == 'SHOW_DESIGN')
			{
				?><div class="hostcmsSection" id="hostcmsSection<?php echo $oTemplate_Section->id?>" style="border-color: <?php echo Core_Str::hex2rgba($oTemplate_Section->color, 0.8)?>">
					<div class="hostcmsSectionIcon" data-template-section-id="<?php echo $oTemplate_Section->id?>" onclick="hQuery.showWidgetPanel(<?php echo $oTemplate_Section->id?>)"><i class="fa-solid fa-plus"></i></div>
					<div class="hostcmsSectionPanel" style="display: none">
						<div class="draggable-indicator">
							<svg width="16px" height="16px" viewBox="0 0 32 32"><rect height="4" width="4" y="4" x="4" /><rect height="4" width="4" y="12" x="4" /><rect height="4" width="4" y="4" x="12"/><rect height="4" width="4" y="12" x="12"/><rect height="4" width="4" y="4" x="20"/><rect height="4" width="4" y="12" x="20"/><rect height="4" width="4" y="4" x="28"/><rect height="4" width="4" y="12" x="28"/></svg>
						</div>
						<?php
						// Добавление виджета в секцию
						$sPathAddWidget = Admin_Form_Controller::correctBackendPath('/{admin}/template/section/lib/index.php');
						$sTitleAddWidget = Core::_('Template_Section.add_widget');
						$sAdditionalAddWidget = "hostcms[action]=edit&template_section_id={$oTemplate_Section->id}&hostcms[checked][0][0]=1";
						$sOnclickAddWidget = "hQuery.openWindow({path: '{$sPathAddWidget}', additionalParams: '{$sAdditionalAddWidget}', title: '" . Core_Str::escapeJavascriptVariable($sTitleAddWidget) . "', dialogClass: 'hostcms6'}); return false";
						?>
						<div><a href="<?php echo "{$sPathAddWidget}?{$sAdditionalAddWidget}"?>" onclick="<?php echo $sOnclickAddWidget ?>" alt="<?php echo $sTitleAddWidget?>" title="<?php echo $sTitleAddWidget?>"><i class="fa-solid fa-fw fa-plus"></i></a></div>
						<?php
						// Настройки секции
						$sPath = Admin_Form_Controller::correctBackendPath('/{admin}/template/section/index.php');
						$sTitleSectionSettings = Core::_('Template_Section.section_settings', $oTemplate_Section->name);
						$sAdditionalSectionSettings = "hostcms[action]=edit&template_id={$this->id}&template_dir_id={$this->Template_Dir->id}&hostcms[checked][0][{$oTemplate_Section->id}]=1";
						$sOnclickSectionSettings = "hQuery.openWindow({path: '{$sPath}', additionalParams: '{$sAdditionalSectionSettings}', title: '" . Core_Str::escapeJavascriptVariable($sTitleSectionSettings) . "', dialogClass: 'hostcms6'}); return false";
						?>
						<div><a href="<?php echo "{$sPath}?{$sAdditionalSectionSettings}"?>" onclick="<?php echo $sOnclickSectionSettings ?>" alt="<?php echo $sTitleSectionSettings?>" title="<?php echo $sTitleSectionSettings?>"><i class="fa-solid fa-fw fa-gear"></i></a></div>
					</div><?php
			}

			echo $oTemplate_Section->prefix;

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

			echo $oTemplate_Section->suffix;

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
	 * Обратная совместимость с версией до 6.8.9
	 */
	public function less($less)
	{
		$this->type = $less ? 1 : 0;
		return $this;
	}

	public function showManifest()
	{
		if ($this->type == 1)
		{
			$manifest = $this->loadManifestFile();

			if ($manifest != '')
			{
				$less = $this->loadTemplateLessFile();

				if ($less != '')
				{
					try
					{
						$oTemplate_Preprocessor_Less = Template_Preprocessor::factory('less');
						$oTemplate_Preprocessor_Less->compile($less);
						$this->_lessVariables = $oTemplate_Preprocessor_Less->getPreprocessor()->getVariables();
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
							<label for="<?php echo htmlspecialchars($fieldName)?>"><?php echo htmlspecialchars(strval($oOptionName[0]))?></label>
							<?php
							switch ($fieldType)
							{
								case 'select':
									?>
									<select name="<?php echo htmlspecialchars($fieldName)?>" class="form-control" data-template="<?php echo $this->id?>">
										<?php
											$aSelectOptions = $oOption->xpath('select/option');

											if (isset($aSelectOptions[0]))
											{
												foreach ($aSelectOptions as $key => $oOption)
												{
													$value = !is_null($oOption->attributes()->value)
														? strval($oOption->attributes()->value)
														: $key;

													$sName = strval($oOption[0]);

													$sSelected = $value == $lessFieldValue
														? 'selected="selected"'
														: '';

													?>
													<option value="<?php echo htmlspecialchars($value)?>" <?php echo $sSelected?>><?php echo htmlspecialchars($sName)?></option>
													<?php
												}
											}
											else
											{
											?>
												<option value="">...</option>
											<?php
											}
										?>
									</select>
									<?php
								break;
								default:
									?><input type="text" class="form-control <?php echo $fieldType == 'color' ? 'colorpicker' : ''?>" name="<?php echo htmlspecialchars((string) $fieldName)?>" value="<?php echo htmlspecialchars((string) $lessFieldValue)?>" <?php echo $fieldType == 'color' && ($lessFieldType == 'rgb' || $lessFieldType == 'rgba') ? 'data-format="rgb"' : '' ?> <?php echo $fieldType == 'color' && $lessFieldType == 'rgba' ? 'data-rgba="true"' : '' ?> data-template="<?php echo $this->id?>" /><?php
							}
							?>
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
				$this->template_dir_id = Core_Array::get($aBackup, 'template_dir_id');
				$this->template_id = Core_Array::get($aBackup, 'template_id');
				$this->sorting = Core_Array::get($aBackup, 'sorting');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
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

	/**
	 * Get i18n value
	 */
	public function _($name)
	{
		$aValues = $this->_getLngFile(Core::getLng());
		return isset($aValues[$name]) ? $aValues[$name] : $name;
	}

	protected $_i18n = array();

	/**
	 * Include lng file
	 * @param string $className class name
	 * @param string $lng language name
	 * @return array
	 */
	protected function _getLngFile($lng)
	{
		if (!isset($this->_i18n[$lng]))
		{
			$this->_i18n[$lng] = array();

			$path = CMS_FOLDER . $this->getDir() . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . $lng . '.php';
			$path = Core_File::pathCorrection($path);

			if (Core_File::isFile($path))
			{
				$this->_i18n[$lng] = require($path);
			}
		}

		return $this->_i18n[$lng];
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event template.onBeforeGetRelatedSite
	 * @hostcms-event template.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}