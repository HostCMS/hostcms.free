<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Template
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Template_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Get Languages
	 * @return array
	 */
	protected function _getLngs()
	{
		$aLngs = array();

		$aAdmin_Languages = Core_Entity::factory('Admin_Language')->getAllByActive(1);
		foreach ($aAdmin_Languages as $oAdmin_Language)
		{
			$aLngs[] = $oAdmin_Language->shortname;
		}

		return $aLngs;
	}

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		if (!$object->id)
		{
			$object->site_id = CURRENT_SITE;

			switch ($modelName)
			{
				case 'template':
					$object->template_id = intval(Core_Array::getGet('template_id', 0));

					!$object->template_id &&
						$object->template_dir_id = intval(Core_Array::getGet('template_dir_id', 0));
				break;
				case 'template_dir':
					$object->parent_id = intval(Core_Array::getGet('template_dir_id', 0));
				break;
			}
		}

		$this
			->addSkipColumn('timestamp')
			->addSkipColumn('data_template_id');

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oSelect_Dirs = Admin_Form_Entity::factory('Select');
		$oSelect_Templates = Admin_Form_Entity::factory('Select');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		switch ($modelName)
		{
			case 'template':
				$title = $this->_object->id
					? Core::_('Template.title_edit', $this->_object->name)
					: Core::_('Template.title_add');

				$oTemplateTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Template.tab_1'))
					->name('Template');

				$oStyleTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Template.tab_2'))
					->name('Style');

				$oJsTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Template.tab_3'))
					->name('Js');

				$oManifestTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Template.tab_4'))
					->name('Manifest');

				$oLanguageTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Template.tab_5'))
					->name('Language');

				$oMainTab
					->move($this->getField('name'), $oMainRow1);

				$this
					->addTabAfter($oTemplateTab, $oMainTab)
					->addTabAfter($oStyleTab, $oTemplateTab)
					->addTabAfter($oJsTab, $oStyleTab)
					->addTabAfter($oLanguageTab, $oJsTab);

				if (!$this->_object->template_id)
				{
					// Удаляем стандартный <input>
					$oAdditionalTab->delete(
						$this->getField('template_dir_id')
					);

					// Селектор с группой
					$oSelect_Dirs
						->options(
							array(' … ') + $this->fillTemplateDir()
						)
						->name('template_dir_id')
						->value($this->_object->template_dir_id)
						->caption(Core::_('Template.template_dir_id'));

					$oMainRow2->add($oSelect_Dirs);
				}

				// Удаляем стандартный <input>
				$oAdditionalTab->delete(
					 $this->getField('template_id')
				);

				// Селектор с родительским макетом
				$oSelect_Templates
					->options(
						array(' … ') + $this->fillTemplateParent(0, $this->_object->id)
					)
					->name('template_id')
					->value($this->_object->template_id)
					->caption(Core::_('Template.template_id'));

				$oMainRow3->add($oSelect_Templates);

				$this->getField('sorting')
					->divAttr(array('class' => 'form-group col-sm-5 col-md-4 col-lg-3'));

				$oMainTab
					->move($this->getField('sorting'), $oMainRow4);

				$oTemplate_Textarea = Admin_Form_Entity::factory('Textarea');

				$oTmpOptions = $oTemplate_Textarea->syntaxHighlighterOptions;
				$oTmpOptions['mode'] = 'application/x-httpd-php';

				$oTemplate_Textarea
					->value(
						$this->_object->loadTemplateFile()
					)
					->cols(140)
					->rows(30)
					->caption(Core::_('Template.template'))
					->name('template')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterOptions($oTmpOptions)
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oTemplateTab
					->add($oTemplateRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$oTemplateRow1->add($oTemplate_Textarea);

				$oLessCss_Textarea = Admin_Form_Entity::factory('Textarea');

				$oTmpOptions = $oLessCss_Textarea->syntaxHighlighterOptions;
				$oTmpOptions['mode'] = 'css';

				switch($this->_object->type)
				{
					// LESS
					case 1:
						$styleValue = is_file($this->_object->getTemplateLessFilePath())
							? $this->_object->loadTemplateLessFile()
							: $this->_object->loadTemplateCssFile();
					break;
					// SCSS
					case 2:
						$styleValue = version_compare(PHP_VERSION, '5.6') >= 0 && is_file($this->_object->getTemplateScssFilePath())
							? $this->_object->loadTemplateScssFile()
							: $this->_object->loadTemplateCssFile();
					break;
					// CSS
					default:
						$styleValue = $this->_object->loadTemplateCssFile();
				}

				$oLessCss_Textarea
					->value($styleValue)
					->rows(30)
					->caption(Core::_('Template.css'))
					->name('css')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterOptions($oTmpOptions)
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oStyleTab
					->add($oCssRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oCssRow2 = Admin_Form_Entity::factory('Div')->class('row'));

				$oCssRow1->add($oLessCss_Textarea);

				$aStyleTypes = array(0 => 'CSS', 1 => 'LESS');
				version_compare(PHP_VERSION, '5.6') >= 0 && $aStyleTypes[2] = 'SCSS';

				// $oMainTab->move($this->getField('less'), $oCssRow2);
				$oMainTab->delete($this->getField('type'));
				$oSelectStyleTypes = Admin_Form_Entity::factory('Select')
					->options($aStyleTypes)
					->name('type')
					->value($this->_object->type)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
					->caption(Core::_('Template.type'));

				$oCssRow2->add($oSelectStyleTypes);

				$oJs_Textarea = Admin_Form_Entity::factory('Textarea');

				$oTmpOptions = $oJs_Textarea->syntaxHighlighterOptions;
				$oTmpOptions['mode'] = 'text/javascript';

				$oJs_Textarea
					->value(
						$this->_object->loadTemplateJsFile()
					)
					->rows(30)
					->caption(Core::_('Template.js'))
					->name('js')
					->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
					->syntaxHighlighterOptions($oTmpOptions)
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oJsTab
					->add($oJsRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$oJsRow1->add($oJs_Textarea);

				if ($this->_object->type == 1)
				{
					$this->addTabAfter($oManifestTab, $oJsTab);

					$oManifestTab
						->add($oManifestRow1 = Admin_Form_Entity::factory('Div')->class('row'));

					$oTextarea_Manifest = Admin_Form_Entity::factory('Textarea');

					$oTmpOptions = $oTextarea_Manifest->syntaxHighlighterOptions;
					$oTmpOptions['mode'] = 'xml';

					$manifest = $this->_object->loadManifestFile();

					!strlen($manifest)
						&& $manifest = '<?xml version="1.0" encoding="UTF-8"?>' . "\n<manifest>\n</manifest>";

					$oTextarea_Manifest
						->value($manifest)
						->rows(30)
						->caption(Core::_('Template.manifest'))
						->name('manifest')
						->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
						->syntaxHighlighterOptions($oTmpOptions)
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oManifestRow1->add($oTextarea_Manifest);
				}

				$oLanguageTab
					->add($oLanguageRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$aLngs = $this->_getLngs();
				foreach ($aLngs as $sLng)
				{
					$oTextarea_Lng = Admin_Form_Entity::factory('Textarea');

					$oTmpOptions = $oTextarea_Lng->syntaxHighlighterOptions;
					$oTmpOptions['mode'] = 'php';

					$lng = $this->_object->loadLngFile($sLng);

					!strlen($lng) && $lng = <<<EOD
<?php
/**
 * Template i18n
 */
return array(
	'' => '',
);
EOD;

					$oTextarea_Lng
						->value($lng)
						->rows(30)
						->caption(Core::_('Template.language', $sLng))
						->name('lng_' . $sLng)
						->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
						->syntaxHighlighterOptions($oTmpOptions)
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oLanguageRow1->add($oTextarea_Lng);
				}
			break;
			case 'template_dir':
			default:
				$title = $this->_object->id
					? Core::_('Template_Dir.title_edit', $this->_object->name)
					: Core::_('Template_Dir.title_add');

				$oMainTab
					->move($this->getField('name'), $oMainRow1);

				// Удаляем стандартный <input>
				$oAdditionalTab->delete(
					 $this->getField('parent_id')
				);

				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillTemplateDir(0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Template_Dir.parent_id'));

				$oMainRow2->add($oSelect_Dirs);

				$this->getField('sorting')
					->divAttr(array('class' => 'form-group col-sm-5 col-md-4 col-lg-3'));

				$oMainTab
					->move($this->getField('sorting'), $oMainRow3);
			break;
		}

		$this->title(
			html_entity_decode($title)
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return this
	 * @hostcms-event Template_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$modelName == 'template'
				&& $this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		if ($modelName == 'template')
		{
			if ($this->_object->template_id)
			{
				$this->_object->template_dir_id = 0;
				$this->_object->save();
			}

			$this->_object
				->saveTemplateFile(Core_Array::getPost('template'))
				->saveTemplateJsFile(Core_Array::getPost('js'));

			$css = Core_Array::getPost('css');

			try
			{
				switch ($this->_object->type)
				{
					case 1:
						// Save LESS and rebuild CSS
						$this->_object->saveTemplateLessFile($css);
					break;
					case 2:
						// Save SCSS and rebuild CSS
						version_compare(PHP_VERSION, '5.6') >= 0 && $this->_object->saveTemplateScssFile($css);
					break;
					default:
						// Save just CSS
						$this->_object->saveTemplateCssFile($css);
				}
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}

			$manifest = Core_Array::getPost('manifest');
			!is_null($manifest)
				&& $this->_object->saveManifestFile($manifest);

			$aLngs = $this->_getLngs();
			foreach ($aLngs as $sLng)
			{
				$content = Core_Array::getPost('lng_' . $sLng);
				$this->_object->saveLngFile($sLng, $content);
			}

			$this->_object
				->rebuildCompressionCss()
				->updateTimestamp();

			// Delete all compressed JS
			if (Core::moduleIsActive('compression'))
			{
				Compression_Controller::instance('js')->deleteAllJs();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Build visual representation of templates tree
	 * @param int $iSiteId site ID
	 * @param int $itemplateId start template ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillTemplateList($iSiteId, $itemplateId = 0, $iLevel = 0)
	{
		$iSiteId = intval($iSiteId);

		$aTemplates = Core_Entity::factory('Template');

		$aTemplates->queryBuilder()
			//->clear()
			->where('site_id', '=', $iSiteId)
			->where('template_id', '=', $itemplateId)
			->orderBy('templates.sorting', 'ASC')
			->orderBy('templates.name', 'ASC');

		$aTemplates = $aTemplates->findAll();

		$aReturn = array();
		if (count($aTemplates))
		{
			foreach ($aTemplates as $children)
			{
				$aReturn[$children->id] = str_repeat('  ', $iLevel) . '[' . $children->id . '] ' . $children->name;
				$aReturn += $this->fillTemplateList($iSiteId, $children->id, $iLevel + 1);
			}
		}

		return $aReturn;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iTemplateDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillTemplateDir($iTemplateDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iTemplateDirParentId = intval($iTemplateDirParentId);
		$iLevel = intval($iLevel);

		$oTemplate_Dir = Core_Entity::factory('Template_Dir', $iTemplateDirParentId);

		$aReturn = array();

		// Дочерние разделы
		$childrenDirs = $oTemplate_Dir->Template_Dirs;
		$childrenDirs->queryBuilder()
			->where('site_id', '=', CURRENT_SITE);

		$childrenDirs = $childrenDirs->findAll();

		if (count($childrenDirs))
		{
			foreach ($childrenDirs as $childrenDir)
			{
				if ($bExclude != $childrenDir->id)
				{
					$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name;
					$aReturn += $this->fillTemplateDir($childrenDir->id, $bExclude, $iLevel + 1);
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iTemplateParentId parent template ID
	 * @param boolean $bExclude exclude template ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillTemplateParent($iTemplateParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iTemplateParentId = intval($iTemplateParentId);
		$iLevel = intval($iLevel);

		$oTemplate_Parent = Core_Entity::factory('Template', $iTemplateParentId);

		$aReturn = array();

		// Дочерние макеты
		$childrenTemplates = $oTemplate_Parent->Templates;
		$childrenTemplates->queryBuilder()
			->where('site_id', '=', CURRENT_SITE);

		$childrenTemplates = $childrenTemplates->findAll();

		if (count($childrenTemplates))
		{
			foreach ($childrenTemplates as $childrenTemplate)
			{
				if ($bExclude != $childrenTemplate->id)
				{
					$aReturn[$childrenTemplate->id] = str_repeat('  ', $iLevel) . $childrenTemplate->name;
					$aReturn += $this->fillTemplateParent($childrenTemplate->id, $bExclude, $iLevel + 1);
				}
			}
		}

		return $aReturn;
	}
}