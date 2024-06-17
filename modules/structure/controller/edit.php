<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Structure Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Structure
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Structure_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('shortcut_id')
			->addSkipColumn('options');

		if ($object->shortcut_id != 0)
		{
			$object = $object->Shortcut;
		}

		if (!$object->id)
		{
			$object->parent_id = intval(Core_Array::getGet('parent_id'));
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		if ($this->_object->shortcut_id != 0)
		{
			$this->_object = $this->_object->Shortcut;
		}

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oSeoTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Structure.seo_tab'))
			->name('Seo');

		$oSitemapTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Structure.sitemap_tab'))
			->name('Sitemap');

		$oPropertyTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Admin_Form.tabProperties'))
			->name('Property');

		$this
			->addTabAfter($oSeoTab, $oMainTab)
			->addTabAfter($oSitemapTab, $oSeoTab)
			->addTabAfter($oPropertyTab, $oSitemapTab);

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow13 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow71 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow10 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow11 = Admin_Form_Entity::factory('Div')->id('lib_properties')->class('hidden-0 hidden-1 hidden-3'))
			->add($oMainRow12 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$template_id = $this->_object->type == 0
			? $this->_object->Document->template_id
			: $this->_object->template_id;

		// $oMainTab->delete($this->getField('options'));

		// -!- Row --
		$this->getField('name')
			->divAttr(array('class' => 'form-group col-xs-12'))
			->class('input-lg form-control');
		$oMainTab
			->move($this->getField('name'), $oMainRow1);

		// -!- Row --
		// Выбор родительского раздела
		$oSelect_Parent_Id = Admin_Form_Entity::factory('Select')
			->options(
				array(' … ') + $this->fillStructureList($this->_object->site_id, 0, $this->_object->id)
			)
			->name('parent_id')
			->value($this->_object->parent_id)
			->caption(Core::_('Structure.parent_id'))
			->divAttr(array('style' => 'float: left'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		// Выбор меню
		$aMenu = $this->_fillMenuList($this->_object->site_id);

		$oSelect_Menu_Id = Admin_Form_Entity::factory('Select')
			->options(
				count($aMenu) ? $aMenu : array(' … ')
			)
			->name('structure_menu_id')
			->value($this->_object->structure_menu_id)
			->caption(Core::_('Structure.structure_menu_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$this->getField('show')
			->divAttr(array('class' => 'form-group col-sm-4 col-md-4 col-lg-4 margin-top-21'));

		$oAdditionalTab
			->delete($this->getField('parent_id'))
			->delete($this->getField('structure_menu_id'));

		$oMainRow2
			->add($oSelect_Parent_Id)
			->add($oSelect_Menu_Id);
		$oMainTab->move($this->getField('show'), $oMainRow2);

		// -!- Row --
		// Группа доступа
		$oAdditionalTab
			->delete($this->getField('siteuser_group_id'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
			$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->site_id);
		}
		else
		{
			$aSiteuser_Groups = array();
		}

		$oSelect_SiteUserGroup = Admin_Form_Entity::factory('Select')
			->name('siteuser_group_id')
			->caption(Core::_('Structure.siteuser_group_id'))
			->options(
				array(
					// Все
					0 => Core::_('Structure.all'),
					// Как у родителя
					-1 => Core::_('Structure.like_parent')
				) + $aSiteuser_Groups
			)
			->value($this->_object->siteuser_group_id)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-4 col-lg-4'));

		$this->getField('sorting')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oMainTab
			->move($this->getField('path'), $oMainRow3)
			->move($this->getField('sorting'), $oMainRow3);
		$oMainRow3->add($oSelect_SiteUserGroup);

		// -!- Row --

		// Structure type
		$oMainTab->delete($this->getField('type'));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// $iLibId = 0;
		// $iLibDirId = 0;

		$oRadio_Type = Admin_Form_Entity::factory('Radiogroup')
			->name('type')
			->id('structureType' . time())
			->caption(Core::_('Structure.type'))
			->value($this->_object->type)
			->divAttr(array('id' => 'structure_types', 'class' => 'form-group col-xs-12 rounded-radio-group'))
			->radio(
				array(
					0 => Core::_('Structure.static_page'),
					2 => Core::_('Structure.typical_dynamic_page'),
					1 => Core::_('Structure.dynamic_page'),
					3 => Core::_('Structure.link')
				)
			)
			->buttonset(TRUE)
			->ico(
				array(
					0 => 'fa-regular fa-file-lines fa-fw',
					2 => 'fa-regular fa-rectangle-list fa-fw',
					1 => 'fa-solid fa-code fa-fw',
					3 => 'fa-solid fa-link fa-fw'
				)
			)
			->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1,2,3]); window.dispatchEvent(new Event('resize'));");

		// Статичный документ
		$oAdditionalTab->delete($this->getField('document_id'));

		$this->getField('url')
			->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2'))
			// clear standart url pattern
			->format(array('lib' => array()));

		// Checkboxes
		$this->getField('active')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));
		$this->getField('indexing')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oMainTab
			->move($this->getField('active'), $oMainRow4)
			->move($this->getField('indexing'), $oMainRow4);

		$oMainTab->delete($this->getField('https'));

		$oHttps = Admin_Form_Entity::factory('Checkbox')
			->name('https')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->caption(Core::_('Structure.https'))
			->value(1)
			->checked($this->_object->id
				? $this->_object->https
				: $this->_object->Site->https
			);

		$oMainRow4->add($oHttps);

		$oMainRow5->add($oRadio_Type);

		$oDocument = $this->_object->Document;

		// Контроллер редактирования документа
		$Document_Controller_Edit = new Document_Controller_Edit($this->_Admin_Form_Action);

		$Select_DocumentDir = Admin_Form_Entity::factory('Select')
			->name('document_dir_id')
			->caption(Core::_('Structure.document_dir_id'))
			->divAttr(array('class' => 'form-group col-lg-6 hidden-1 hidden-2 hidden-3'))
			->options(
				array(' … ') + $Document_Controller_Edit->fillDocumentDir($this->_object->site_id)
			)
			->value($oDocument->document_dir_id)
			->onchange("$.ajaxRequest({path: '/admin/structure/index.php', context: 'document_id', callBack: $.loadSelectOptionsCallback, action: 'loadDocumentList', additionalParams: 'document_dir_id=' + this.value,windowId: '{$windowId}'}); return false");

		$oMainRow6->add($Select_DocumentDir);

		$aDocumentForDir = array(' … ');
		// intval() because $oDocument->document_dir_id can be NULL

		$oDocuments = Core_Entity::factory('Document_Dir', intval($oDocument->document_dir_id))->Documents;
		$oDocuments->queryBuilder()
			->select('id', 'name')
			->where('documents.site_id', '=', $this->_object->site_id)
			->clearOrderBy()
			->orderBy('documents.name');

		$aDocuments = $oDocuments->findAll(FALSE);
		foreach ($aDocuments as $oTmpDocument)
		{
			$aDocumentForDir[$oTmpDocument->id] = $oTmpDocument->name;
		}

		$Select_Document = Admin_Form_Entity::factory('Select')
			->id('document_id')
			->name('document_id')
			->caption(Core::_('Structure.document_id'))
			->divAttr(array('class' => 'form-group col-lg-6 hidden-1 hidden-2 hidden-3'))
			->options($aDocumentForDir)
			->value($this->_object->document_id)
			->onchange("$('#{$windowId} .document-edit').removeClass('hidden'); $.ajaxRequest({path: '/admin/structure/index.php', context: '{$this->_formId}', callBack: $.loadDocumentText, additionalParams: 'loadDocumentText&document_id=' + this.value, windowId: '{$windowId}'}); return false");
			;

		$Select_Document
			->add(
				Admin_Form_Entity::factory('A')
					->target('_blank')
					->href(
						$oDocument->id
							? $this->_Admin_Form_Controller->getAdminActionLoadHref('/admin/document/index.php', 'edit', NULL, 1, intval($oDocument->id), 'document_dir_id=' . intval($oDocument->document_dir_id))
							: ''
					)
					->class('document-edit input-group-addon blue' . ($oDocument->id ? '' : ' hidden'))
					->value('<i class="fa fa-pencil"></i>')
			);

		$oMainRow7->add($Select_Document);

		$oTextarea_Document_Text = Admin_Form_Entity::factory('Textarea')
			->name('document_text')
			->divAttr(array('class' => 'form-group col-xs-12 hidden-1 hidden-2 hidden-3'))
			->caption(Core::_('Structure.document_text'))
			->value($oDocument->text)
			->wysiwyg(Core::moduleIsActive('wysiwyg'))
			->template_id($template_id)
			->rows(20);

		$oMainRow71->add($oTextarea_Document_Text);

		// -!- Row --
		// Выбор макета
		$Template_Controller_Edit = new Template_Controller_Edit($this->_Admin_Form_Action);

		$aTemplateOptions = $Template_Controller_Edit->fillTemplateList($this->_object->site_id);

		$oSelect_Template = Admin_Form_Entity::factory('Select')
			->options(
				count($aTemplateOptions) ? $aTemplateOptions : array(' … ')
			)
			->name('template_id')
			->id('template_id')
			->value($template_id)
			->caption(Core::_('Structure.template_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-lg-6 hidden-3'))
			->onchange("$('#{$windowId} .template-edit').attr('href', '/admin/template/index.php?hostcms[action]=edit&hostcms[checked][1][' + this.value + ']=1'); return false");

		$oSelect_Template
			->add(
				Admin_Form_Entity::factory('A')
					->target('_blank')
					->class('template-edit input-group-addon blue')
					->value('<i class="fa fa-pencil"></i>')
			);

		$oMainRow13->add($oSelect_Template);

		$oAdditionalTab->delete($this->getField('template_id'));

		$this->getField('path')
			->id('path')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oSite = $this->_object->Site;
		$oSiteAlias = $oSite->getCurrentAlias();

		if ($oSiteAlias)
		{
			$this->getField('path')->add(
				$pathLink = Admin_Form_Entity::factory('A')
					->id('pathLink')
					->class('input-group-addon blue')
					->value('<i class="fa fa-external-link"></i>')
			);

			if ($this->_object->id)
			{
				$pathLink
					->target('_blank')
					->href(($this->_object->https ? 'https://' : 'http://') . $oSiteAlias->name . $this->_object->getPath());
			}
		}

		// -!- Row --
		$oMainTab
			->move($this->getField('url'), $oMainRow8);

		// Типовая динамическая страница
		$oAdditionalTab->delete($this->getField('lib_id'));

		$Lib_Controller_Edit = new Lib_Controller_Edit($this->_Admin_Form_Action);

		$oLib = Core_Entity::factory('Lib', $this->_object->lib_id);

		$Select_LibDir = Admin_Form_Entity::factory('Select')
			->name('lib_dir_id')
			->caption(Core::_('Structure.lib_dir_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-lg-6 hidden-0 hidden-1 hidden-3'))
			->options(
				array(' … ') + $Lib_Controller_Edit->fillLibDir(0)
			)
			->value($oLib->lib_dir_id)
			->onchange("$('#{$windowId} .lib-edit').addClass('hidden'); $.ajaxRequest({path: '/admin/structure/index.php',context: 'lib_id', callBack: $.loadSelectOptionsCallback, action: 'loadLibList',additionalParams: 'lib_dir_id=' + this.value,windowId: '{$windowId}'}); return false");

		$aLibForDir = array(' … ');
		$aLibs = Core_Entity::factory('Lib_Dir', intval($oLib->lib_dir_id)) // Может быть NULL
			->Libs->findAll();

		foreach ($aLibs as $oTmpLib)
		{
			$aLibForDir[$oTmpLib->id] = '[' . $oTmpLib->id . '] ' . $oTmpLib->name;
		}
		$objectId = intval($this->_object->id);

		$Select_Lib = Admin_Form_Entity::factory('Select')
			->name('lib_id')
			->id('lib_id')
			->caption(Core::_('Structure.lib_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-lg-6 hidden-0 hidden-1 hidden-3'))
			->options($aLibForDir)
			->value($this->_object->lib_id)
			->onchange("$.ajaxRequest({path: '/admin/structure/index.php', context: '{$this->_formId}', callBack: $.loadDivContentAjaxCallback, objectId: {$objectId}, action: 'loadLibProperties', additionalParams: 'lib_id=' + this.value, windowId: '{$windowId}'}); return false")
			;

		$Select_Lib
			->add(
				Admin_Form_Entity::factory('A')
					->target('_blank')
					->href(
						$oLib->id
							? $this->_Admin_Form_Controller->getAdminActionLoadHref('/admin/lib/index.php', 'edit', NULL, 1, intval($oLib->id), 'lib_dir_id=' . intval($oLib->lib_dir_id))
							: ''
					)
					->class('lib-edit input-group-addon blue ' . ($oLib->id ? '' : ' hidden'))
					->value('<i class="fa fa-pencil"></i>')
			);

		$Div_Lib_Properties = Admin_Form_Entity::factory('Code');

		ob_start();
		// DIV для св-в типовой дин. страницы
		// Для выбранного стандартно
		Core_Html_Entity::factory('Script')
			->value("$('#{$windowId} #lib_id').change(); $('#{$windowId} #template_id').change();")
			->execute();

		$Div_Lib_Properties
			->html(ob_get_clean());

		$oMainRow9
			->add($Select_LibDir)
			->add($Select_Lib);

		$oMainRow11->add($Div_Lib_Properties);

		// Динамическая страница
		$oTextarea_Structure_Source = Admin_Form_Entity::factory('Textarea');

		$oTmpOptions = $oTextarea_Structure_Source->syntaxHighlighterOptions;
		// $oTmpOptions['mode'] = 'application/x-httpd-php';
		$oTmpOptions['mode'] = '"ace/mode/php"';

		$oTextarea_Structure_Source
			->name('structure_source')
			->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-2 hidden-3'))
			->caption(Core::_('Structure.structure_source'))
			->value($this->_object->getStructureFile())
			->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
			->syntaxHighlighterOptions($oTmpOptions)
			->rows(30);

		$oTextarea_StructureConfig_Source = Admin_Form_Entity::factory('Textarea');

		$oTmpOptions = $oTextarea_StructureConfig_Source->syntaxHighlighterOptions;
		// $oTmpOptions['mode'] = 'application/x-httpd-php';
		$oTmpOptions['mode'] = '"ace/mode/php"';

		$oTextarea_StructureConfig_Source
			->name('structure_config_source')
			->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-2 hidden-3'))
			->caption(Core::_('Structure.structure_config_source'))
			->value($this->_object->getStructureConfigFile())
			->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
			->syntaxHighlighterOptions($oTmpOptions)
			->rows(30);

		$oMainRow12
			->add($oTextarea_Structure_Source)
			->add($oTextarea_StructureConfig_Source);

		// -- SEO
		$oSeoTab
			->add($oSeoRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSeoRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSeoRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('seo_title')->rows(2), $oSeoRow1)
			->move($this->getField('seo_description')->rows(2), $oSeoRow2)
			->move($this->getField('seo_keywords')->rows(2), $oSeoRow3);

		// -- Sitemap
		$oSelect_changefreq = Admin_Form_Entity::factory('Select')
			->options(
				array(
					0 => Core::_('Structure.sitemap_refrashrate_always'),
					1 => Core::_('Structure.sitemap_refrashrate_hourly'),
					2 => Core::_('Structure.sitemap_refrashrate_daily'),
					3 => Core::_('Structure.sitemap_refrashrate_weekly'),
					4 => Core::_('Structure.sitemap_refrashrate_monthly'),
					5 => Core::_('Structure.sitemap_refrashrate_yearly'),
					6 => Core::_('Structure.sitemap_refrashrate_never')
				)
			)
			->name('changefreq')
			->value($this->_object->changefreq)
			->caption(Core::_('Structure.changefreq'))
			->divAttr(array('class' => 'form-group col-xs-4'));

		$oSelect_priority = Admin_Form_Entity::factory('Select')
			->options(
				array(
					'0' => Core::_('Structure.sitemap_priority_small'),
					'0.1' => Core::_('Structure.sitemap_priority_0_1'),
					'0.2' => Core::_('Structure.sitemap_priority_0_2'),
					'0.3' => Core::_('Structure.sitemap_priority_0_3'),
					'0.4' => Core::_('Structure.sitemap_priority_0_4'),
					'0.5' => Core::_('Structure.sitemap_priority_normal'),
					'0.6' => Core::_('Structure.sitemap_priority_0_6'),
					'0.7' => Core::_('Structure.sitemap_priority_0_7'),
					'0.8' => Core::_('Structure.sitemap_priority_0_8'),
					'0.9' => Core::_('Structure.sitemap_priority_0_9'),
					'1' => Core::_('Structure.sitemap_priority_above_normal')
				)
			)
			->name('priority')
			->value($this->_object->priority)
			->caption(Core::_('Structure.priority'))
			->divAttr(array('class' => 'form-group col-xs-4'));

		$oSitemapTab
			->add($oSitemapRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oSitemapRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->delete($this->getField('changefreq'))
			->delete($this->getField('priority'));

		$oSitemapRow1->add($oSelect_changefreq);
		$oSitemapRow2->add($oSelect_priority);

		// ---- Дополнительные свойства
		Property_Controller_Tab::factory($this->_Admin_Form_Controller)
			->setObject($this->_object)
			->setDatasetId($this->getDatasetId())
			->linkedObject(Core_Entity::factory('Structure_Property_List', CURRENT_SITE))
			->setTab($oPropertyTab)
			->template_id($template_id)
			->fillTab();

		if (Core::moduleIsActive('media'))
		{
			$oMediaTab = Admin_Form_Entity::factory('Tab')
				->caption(Core::_("Admin_Form.tabMedia"))
				->name('Media');

			$this->addTabAfter($oMediaTab, $oPropertyTab);

			Media_Controller_Tab::factory($this->_Admin_Form_Controller)
				->setObject($this->_object)
				->setDatasetId($this->getDatasetId())
				->setTab($oMediaTab)
				->fillTab();
		}

		$this->title($this->_object->id
			? Core::_('Structure.edit_title', $this->_object->name, FALSE)
			: Core::_('Structure.add_title')
		);

		$oMainTab->add(
			Admin_Form_Entity::factory('Code')
				->html("<script>radiogroupOnChange('{$windowId}', '{$this->_object->type}', [0,1,2,3])</script>")
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Structure_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$bNewObject = is_null($this->_object->id) && is_null(Core_Array::getPost('id'));

		// Backup structure revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		// Динамическая страница
		$structure_source = Core_Array::getPost('structure_source');
		$structure_config_source = Core_Array::getPost('structure_config_source');
		if ($this->_object->type == 1 || $structure_source !== '' || $structure_config_source !== '')
		{
			$this->_object->saveStructureFile($structure_source);
			$this->_object->saveStructureConfigFile($structure_config_source);
		}
		else
		{
			$this->_object
				->deleteConfigFile()
				->deleteFile();
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Страница
		if ($this->_object->type == 0)
		{
			if ($this->_object->document_id)
			{
				$oDocument = $this->_object->Document;

				// Backup document revision
				if (Core::moduleIsActive('revision') && $this->_object->id)
				{
					$oDocument->backupRevision();
				}
			}
			else
			{
				$oDocument = Core_Entity::factory('Document');
				$oDocument->document_dir_id = $this->_formValues['document_dir_id'];
				$oDocument->name = $this->_formValues['name'];
			}

			$oDocument->template_id = $this->_formValues['template_id'];
			$oDocument->text = $this->_formValues['document_text'];
			$oDocument->datetime = Core_Date::timestamp2sql(time());
			$oDocument->save();

			if (!$this->_object->document_id)
			{
				?><script>
				$('#<?php echo $windowId?> #document_id')
					.append($("<option></option>")
						.attr("value", <?php echo htmlspecialchars($oDocument->id)?>)
						.attr("selected", "selected")
						.text("<?php echo htmlspecialchars($oDocument->name)?>"))
				</script><?php
			}

			$this->_object->document_id = $oDocument->id;
			$this->_object->save();
		}

		// Lib properties
		if ($this->_object->type == 2 && $this->_object->lib_id)
		{
			$oLib = $this->_object->Lib;

			$JSON = Structure_Controller_Libproperties::getJson($this->_object);

			// Сохраняем настройки
			$this->_object->options = $JSON;
			$this->_object->save();

			// Backward compatibility
			$datFile = $oLib->getLibDatFilePath($this->_object->id);
			if (Core_File::isFile($datFile))
			{
				try
				{
					Core_File::delete($datFile);
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
				}
			}
		}

		// ---- Дополнительные свойства
		Property_Controller_Tab::factory($this->_Admin_Form_Controller)
			->setObject($this->_object)
			->linkedObject(Core_Entity::factory('Structure_Property_List', CURRENT_SITE))
			->applyObjectProperty();
		// ----

		if (Core::moduleIsActive('search'))
		{
			Search_Controller::indexingSearchPages(array(
				$this->_object->indexing()
			));
		}

		if (Core::moduleIsActive('media'))
		{
			Media_Controller_Tab::factory($this->_Admin_Form_Controller)
				->setObject($this->_object)
				->applyObjectProperty();
		}

		$this->_object->clearCache();

		$oSite = $this->_object->Site;
		$oSiteAlias = $oSite->getCurrentAlias();
		if ($oSiteAlias)
		{
			$sUrl = ($this->_object->https ? 'https://' : 'http://')
				. $oSiteAlias->name
				. $this->_object->getPath();

			$this->_Admin_Form_Controller->addMessage(
				Core_Html_Entity::factory('Script')
					->value("$('#{$windowId} input#path').val('" . Core_Str::escapeJavascriptVariable($this->_object->path) . "');
					$('#{$windowId} a#pathLink').attr('href', '" . Core_Str::escapeJavascriptVariable($sUrl) . "').attr('target', '_blank')")
				->execute()
			);
		}

		if ($bNewObject && Core::moduleIsActive('media'))
		{
			ob_start();
			$this->_fillMedia()->execute();
			$this->_Admin_Form_Controller->addMessage(ob_get_clean());
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/*
	 * Add shop documents
	 * @return Admin_Form_Entity
	 */
	protected function _fillMedia()
	{
		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));
		$windowId = $modalWindowId ? $modalWindowId : $this->_Admin_Form_Controller->getWindowId();

		$modelName = $this->_object->getModelName();

		return Admin_Form_Entity::factory('Script')
			->value("$(function (){
				mainFormLocker.unlock();
				$.adminLoad({ path: '/admin/media/index.php', additionalParams: 'entity_id=" . $this->_object->id . "&type=" . $modelName . "&dataset_id=" . $this->getDatasetId() . "&parentWindowId=" . $windowId . "&_module=0', windowId: '{$windowId}-media-items', loadingScreen: false });
			});");
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iSiteId site ID
	 * @param int $iParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillStructureList($iSiteId, $iParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iSiteId = intval($iSiteId);
		$iParentId = intval($iParentId);
		$iLevel = intval($iLevel);

		$oStructure = Core_Entity::factory('Structure', $iParentId);

		$aReturn = array();

		// Дочерние разделы
		$oStructures = $oStructure->Structures;
		$oStructures->queryBuilder()
			->where('structures.site_id', '=', $iSiteId)
			->where('structures.shortcut_id', '=', 0)
			->clearOrderBy()
			->orderBy('structures.sorting', 'ASC');

		$aChildren = $oStructures->findAll(FALSE);

		foreach ($aChildren as $oStructure)
		{
			if ($bExclude != $oStructure->id)
			{
				$aReturn[$oStructure->id] = str_repeat('  ', $iLevel) . $oStructure->name . ' [' . $oStructure->path . ']';
				$aReturn += $this->fillStructureList($iSiteId, $oStructure->id, $bExclude, $iLevel + 1);
			}
		}

		return $aReturn;
	}

	/**
	 * Fill menus list
	 * @param int $iSiteId site ID
	 * @return array
	 */
	protected function _fillMenuList($iSiteId)
	{
		$iSiteId = intval($iSiteId);

		$aReturn = array();
		$aChildren = Core_Entity::factory('Structure_Menu')->getBySiteId($iSiteId);

		if (count($aChildren))
		{
			foreach ($aChildren as $oMenu)
			{
				$aReturn[$oMenu->id] = $oMenu->name;
			}
		}

		return $aReturn;
	}
}