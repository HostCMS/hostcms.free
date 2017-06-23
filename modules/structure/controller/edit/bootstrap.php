<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Structure.
 *
 * @package HostCMS 6\Structure
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2014 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Structure_Controller_Edit_Bootstrap extends Admin_Form_Action_Controller_Type_Edit_Bootstrap
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('data_template_id');

		if (is_null($object->id))
		{
			$object->parent_id = intval(Core_Array::getGet('parent_id'));
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oSeoTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Structure.seo_tab'))
			->name('Seo');

		$oSitemapTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Structure.sitemap_tab'))
			->name('Sitemap');

		$oPropertyTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Structure.additional_params_tab'))
			->name('Property');

		$this
			->addTabAfter($oSeoTab, $oMainTab)
			->addTabAfter($oSitemapTab, $oSeoTab)
			->addTabAfter($oPropertyTab, $oSitemapTab);

		$oMainTab
			// SEO
			->move($this->getField('seo_title')->divAttr(array('class' => 'form-group col-lg-12'))->rows(2), $oSeoTab)
			->move($this->getField('seo_description')->divAttr(array('class' => 'form-group col-lg-12'))->rows(2), $oSeoTab)
			->move($this->getField('seo_keywords')->divAttr(array('class' => 'form-group col-lg-12'))->rows(2), $oSeoTab)
		
		

			->delete($this->getField('changefreq'))
			->delete($this->getField('priority'));

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
			->divAttr(array('class' => 'form-group col-lg-6'));
			//->style('width: 220px');

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
			->divAttr(array('class' => 'form-group col-lg-6'));
			//->style('width: 220px');

		$oDiv_Clearfix = Admin_Form_Entity::factory('Div')->class('clearfix');				
			
		$oSitemapTab
			->add($oSelect_changefreq)
			->add($oDiv_Clearfix)
			->add($oSelect_priority)
			->add($oDiv_Clearfix);

		$oAdditionalTab
			->delete($this->getField('parent_id'))
			->delete($this->getField('structure_menu_id'));

		// Выбор родительского раздела
		$oSelect_Parent_Id = Admin_Form_Entity::factory('Select')
			->options(
				array(' … ') + $this->fillStructureList($this->_object->site_id, 0, $this->_object->id)
			)
			->name('parent_id')
			->value($this->_object->parent_id)
			->caption(Core::_('Structure.parent_id'))
			->divAttr(array('class' => 'form-group col-lg-4'));
			//->divAttr(array('style' => 'float: left'))
			//->style('width: 320px');

		// Выбор меню
		$aMenu = $this->_fillMenuList($this->_object->site_id);

		$oSelect_Menu_Id = Admin_Form_Entity::factory('Select')
			->options(
				count($aMenu) ? $aMenu : array(' … ')
			)
			->name('structure_menu_id')
			->value($this->_object->structure_menu_id)
			->caption(Core::_('Structure.structure_menu_id'))
			->divAttr(array('class' => 'form-group col-lg-4'));
			//->divAttr(array('style' => 'float: left'))
			//->style('width: 320px');

		$this->getField('show')			
			->divAttr(array('class' => 'form-group col-lg-4', 'style' => 'margin-top: 25px'));
									
		$this->getField('name')->divAttr = array('class' => 'form-group col-lg-11');
		
		$this->getField('name')->class('form-control input-lg');
		
		$oMainTab
			->addAfter($oSelect_Parent_Id, $this->getField('name'))
			->addAfter($oSelect_Menu_Id, $oSelect_Parent_Id)
			->addAfter(Admin_Form_Entity::factory('Separator'), $this->getField('show'));			

		// Выбор макета
		$Template_Controller_Edit = new Template_Controller_Edit($this->_Admin_Form_Action);

		$aTemplateOptions = $Template_Controller_Edit->fillTemplateList($this->_object->site_id);

		// Warning: TO DO: dynamic chain list template_dir -> template like Documents
		$oSelect_Template_Id = Admin_Form_Entity::factory('Select')
			->options(
				count($aTemplateOptions) ? $aTemplateOptions : array(' … ')
			)
			->name('template_id')
			->value($this->_object->template_id)
			->caption(Core::_('Structure.template_id'))
			->divAttr(array('class' => 'form-group col-lg-6', 'id' => 'template_id'));
		//	->style('width: 320px');
		
		//$oDiv_Clearfix = Admin_Form_Entity::factory('Div')
		//	->class('clearfix');			
			
		//	->style('width: 320px');

		$oAdditionalTab->delete($this->getField('template_id'));
				
		/*
		$oMainTab
			->addBefore($oSelect_Template_Id, $this->getField('path'))
			->addAfter(Admin_Form_Entity::factory('Separator'), $oSelect_Template_Id);
			*/
		
		$oMainTab
			//->addBefore($this->getField('show'), $this->getField('path'))
			->addAfter(Admin_Form_Entity::factory('Separator'), $this->getField('show'));	
			
		

		$this->getField('path')
			->divAttr(array('class' => 'form-group col-lg-4'))
			->style('font-weight: bold;');
			//->divAttr(array('style' => 'float: left'))
			//->style('width: 320px; font-weight: bold;');

		$this->getField('sorting')
			->divAttr(array('class' => 'form-group col-lg-4'));
			//->divAttr(array('style' => 'float: left'))
			//->style('width: 200px');

		$oMainTab
			->delete($this->getField('sorting'))
			->addAfter($this->getField('sorting'), $this->getField('path'));

		// Checkboxes
		$oMainTab
			->delete($this->getField('https'))
			->addAfter($this->getField('https'), $this->getField('indexing'));
			
			

		$this->getField('active')
			->divAttr(array('class' => 'form-group col-lg-4'));
			//->divAttr(array('style' => 'float: left'));

		$this->getField('indexing')
			->divAttr(array('class' => 'form-group col-lg-4'));
			
		$this->getField('https')
			->divAttr(array('class' => 'form-group col-lg-4'));

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
			//->style('width: 110px')
			->options(
				array(
					// Все
					0 => Core::_('Structure.all'),
					// Как у родителя
					-1 => Core::_('Structure.like_parent')
				) + $aSiteuser_Groups
			)
			->value($this->_object->siteuser_group_id);
			
		$oSelect_SiteUserGroup->divAttr(array('class' => 'form-group col-lg-4'));
		
		$oMainTab
			->addAfter($oSelect_SiteUserGroup, $this->getField('sorting'))
			->addAfter(Admin_Form_Entity::factory('Separator'), $oSelect_SiteUserGroup);

		// Structure type
		$oMainTab->delete($this->getField('type'));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$iLibId = 0;
		$iLibDirId = 0;

		$oRadio_Type = Admin_Form_Entity::factory('Radiogroup')
			->name('type')
			->id('structureType' . time())
			->caption(Core::_('Structure.type'))
			->value($this->_object->type)
			//->labelAttr(array('style' => 'font-weight: bold'))
			->divAttr(array('class' => 'form-group col-lg-12', 'id' => 'structure_types'))			
			->radio(
				array(
					0 => Core::_('Structure.static_page'),
					2 => Core::_('Structure.typical_dynamic_page'),
					1 => Core::_('Structure.dynamic_page')
				)
			)
			->onclick("SetViewStructure('{$windowId}', this.value, '{$this->_object->id}', '{$iLibDirId}', '{$iLibId}')");

			
		$oMainTab
			->add($oRadio_Type);
			/*->add(Admin_Form_Entity::factory('Code')
				->html("<script>$(function() {
					$('#{$windowId} #structure_types').buttonset();
				});</script>")
			);*/
				

		// Статичный документ
		$oAdditionalTab->delete($this->getField('document_id'));

		$oDocument = Core_Entity::factory('Document', $this->_object->document_id);

		// Контроллер редактирования документа
		$Document_Controller_Edit = new Document_Controller_Edit($this->_Admin_Form_Action);

		$Select_DocumentDir = Admin_Form_Entity::factory('Select')
			->name('document_dir_id')
			->id('document_dir_id')
			->caption(Core::_('Structure.document_dir_id'))
			//->style('width: 500px')
			->divAttr(array('class' => 'form-group col-lg-6', 'id' => 'document_dir'))
			->options(
				array(' … ') + $Document_Controller_Edit->fillDocumentDir($this->_object->site_id)
			)
			->value($oDocument->document_dir_id) //
			->onchange("$.ajaxRequest({path: '/admin/structure/index.php', context: 'document_id', callBack: $.loadSelectOptionsCallback, action: 'loadDocumentList', additionalParams: 'document_dir_id=' + this.value,windowId: '{$windowId}'}); return false");

		$aDocumentForDir = array(' … ');
		// intval() because $oDocument->document_dir_id may be NULL
		$aDocuments = Core_Entity::factory('Document_Dir', intval($oDocument->document_dir_id))
			->Documents->getBySiteId($this->_object->site_id);

		foreach ($aDocuments as $oTmpDocument)
		{
			$aDocumentForDir[$oTmpDocument->id] = $oTmpDocument->name;
		}

		ob_start();
		Core::factory('Core_Html_Entity_Img')
			->src('/admin/images/edit.gif')
			->id('editDocument')
			->class('pointer left5px')
			->onclick("$.openWindow({path: '/admin/document/index.php', additionalParams: 'document_dir_id=' + $('#{$windowId} #document_dir_id').val() + '&hostcms[checked][1][' + $('#{$windowId} #document_id').val() + ']=1&hostcms[action]=edit', dialogClass: 'hostcms6'})")
			->execute();

		$sDocumentEditImg = ob_get_clean();

		$Select_Document = Admin_Form_Entity::factory('Select')
			->name('document_id')
			->id('document_id')
			->caption(Core::_('Structure.document_id'))
			//->style('width: 500px')
			->divAttr(array('class' => 'form-group col-lg-6', 'id' => 'document'/*, 'style' => 'float: left'*/))
			->options($aDocumentForDir)
			->value($this->_object->document_id)
			->add(
				Admin_Form_Entity::factory('Code')->html($sDocumentEditImg)
			);

		$oMainTab
			->addAfter($Select_DocumentDir, $oRadio_Type)
			->addAfter($Select_Document, $Select_DocumentDir);

		// Типовая динамическая страница
		$oAdditionalTab->delete($this->getField('lib_id'));

		$Lib_Controller_Edit = new Lib_Controller_Edit($this->_Admin_Form_Action);

		$oLib = Core_Entity::factory('Lib', $this->_object->lib_id);

		$Select_LibDir = Admin_Form_Entity::factory('Select')
			->name('lib_dir_id')
			->caption(Core::_('Structure.lib_dir_id'))
			//->style('width: 500px')
			->divAttr(array('class' => 'form-group col-lg-6','id' => 'lib_dir'))
			->options(
				array(' … ') + $Lib_Controller_Edit->fillLibDir(0)
			)
			->value($oLib->lib_dir_id) //
			->onchange("$.ajaxRequest({path: '/admin/structure/index.php',context: 'lib_id', callBack: $.loadSelectOptionsCallback, action: 'loadLibList',additionalParams: 'lib_dir_id=' + this.value,windowId: '{$windowId}'}); return false");

		$aLibForDir = array(' … ');
		$aLibs = Core_Entity::factory('Lib_Dir', intval($oLib->lib_dir_id)) // Может быть NULL
			->Libs->findAll();

		foreach ($aLibs as $oTmpLib)
		{
			$aLibForDir[$oTmpLib->id] = $oTmpLib->name;
		}
		$objectId = intval($this->_object->id);
		$Select_Lib = Admin_Form_Entity::factory('Select')
			->name('lib_id')
			->id('lib_id')
			->caption(Core::_('Structure.lib_id'))
			//->style('width: 500px')
			->divAttr(array('class' => 'form-group col-lg-6', 'id' => 'lib'))
			->options($aLibForDir)
			->value($this->_object->lib_id)
			->onchange("$.ajaxRequest({path: '/admin/structure/index.php',context: 'lib_properties', callBack: $.loadDivContentAjaxCallback, objectId: {$objectId}, action: 'loadLibProperties',additionalParams: 'lib_id=' + this.value,windowId: '{$windowId}'}); return false")
			;

		$Div_Lib_Properies = Admin_Form_Entity::factory('Code');

		ob_start();
		// DIV для св-в типовой дин. страницы
		$Core_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
			->id('lib_properties')
			->execute();

		// Для выбранного стандартно
		$Core_Html_Entity_Div = Core::factory('Core_Html_Entity_Script')
			->type("text/javascript")
			->value("$('#{$windowId} #lib_id').change();")
			->execute();

		$Div_Lib_Properies
			->html(ob_get_clean());

		$oMainTab
			->addAfter($Select_LibDir, $oRadio_Type)
			->addAfter($Select_Lib, $Select_LibDir)
			->addAfter($Div_Lib_Properies, $Select_Lib)
			;

		// Динамическая страница
		$oTextarea_Structure_Source = Admin_Form_Entity::factory('Textarea')
			->name('structure_source')
			->divAttr(array('class' => 'form-group col-lg-12', 'id' => 'structure_source'))
			->caption(Core::_('Structure.structure_source'))
			->value($this->_object->getStructureFile())
			->rows(20)
			//->style('height: 400px;');
			->class('form-control codepress php');

		$oTextarea_StructureConfig_Source = Admin_Form_Entity::factory('Textarea')
			->name('structure_config_source')
			->divAttr(array('class' => 'form-group col-lg-12', 'id' => 'structure_config_source'))
			->caption(Core::_('Structure.structure_config_source'))
			->value($this->_object->getStructureConfigFile())
			->rows(20)
			//->style('height: 400px;')
			->class('form-control codepress php');

		$oMainTab
			->addAfter($oTextarea_Structure_Source, $oRadio_Type)
			->addAfter($oTextarea_StructureConfig_Source, $oTextarea_Structure_Source);

		// ---- Дополнительные свойства
		$oProperty_Controller_Tab = new Property_Controller_Tab($this->_Admin_Form_Controller);
		$oProperty_Controller_Tab
			->setObject($this->_object)
			->setDatasetId($this->getDatasetId())
			->linkedObject(Core_Entity::factory('Structure_Property_List', CURRENT_SITE))
			->setTab($oPropertyTab)
			->template_id($this->_object->template_id)
			->fillTab()
			;
		// ----

		$oMainTab
			->delete($this->getField('url'))
			->add($this->getField('url'));

		$this->getField('url')
			->divAttr(array('class' => 'form-group col-lg-12', 'id' => 'url'))
			//->style('width: 500px;')
			// clear standart url pattern
			->format(array('lib' => array()));

		$this->title($this->_object->id
			? Core::_('Structure.edit_title')
			: Core::_('Structure.add_title'));

		ob_start();
		Core::factory('Core_Html_Entity_Script')
			->type("text/javascript")
			->value("SetViewStructure('{$windowId}', '{$this->_object->type}', '{$this->_object->id}', '{$iLibDirId}', '{$iLibId}')")
			->execute();

		$oMainTab->add(
			Admin_Form_Entity::factory('Code')->html(ob_get_clean())
		);
		
		$oMainTab
			->addAfter($oSelect_Template_Id, $oRadio_Type)			
			->addAfter($oDiv_Clearfix, $oSelect_Template_Id);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Structure_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		// Path transliteration
		/*if ($this->_object->path == '')
		{
			$this->_object->path = Core_Str::transliteration($this->_object->name);
			$this->_object->save();
		}*/

		$this->_object->saveStructureFile(Core_Array::getPost('structure_source'));
		$this->_object->saveStructureConfigFile(Core_Array::getPost('structure_config_source'));

		// Lib properies
		if ($this->_object->type == 2 && $this->_object->lib_id)
		{
			$LA = array();

			$oLib = $this->_object->Lib;
			$aLib_Properties = $oLib->Lib_Properties->findAll();

			foreach ($aLib_Properties as $oLib_Property)
			{
				$propertyName = 'lib_property_id_' . $oLib_Property->id;

				$propertyValue = Core_Array::getPost($propertyName);

				switch ($oLib_Property->type)
				{
					case 1: // Флажок
					$propertyValue = !is_null($propertyValue);
					break;

					case 2: // XSL шаблон
					$propertyValue = Core_Entity::factory('Xsl', $propertyValue)->name;
					break;

					case 0: // Поле ввода
					case 3: // Список
					case 4: // SQL-запрос
					default:

					$propertyValue = strval($propertyValue);
					break;
				}

				$LA[$oLib_Property->varible_name] = $propertyValue;
			}

			// Сохраняем настройки
			if (count($LA) > 0)
			{
				try
				{
					$oLib->saveDatFile($LA, $this->_object->id);
				}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
					Core_Message::show(Core::_('Structure.save_lib_file_error'), 'error');
				}
			}
		}

		// ---- Дополнительные свойства
		$oProperty_Controller_Tab = new Property_Controller_Tab($this->_Admin_Form_Controller);
		$oProperty_Controller_Tab
			->setObject($this->_object)
			->linkedObject(Core_Entity::factory('Structure_Property_List', CURRENT_SITE))
			->applyObjectProperty()
			;
		// ----

		if (Core::moduleIsActive('search'))
		{
			Search_Controller::indexingSearchPages(array(
				$this->_object->indexing()
			));
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
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
		$aChildren = $oStructure->Structures->getBySiteId($iSiteId);

		if (count($aChildren))
		{
			foreach ($aChildren as $oStructure)
			{
				if ($bExclude != $oStructure->id)
				{
					$aReturn[$oStructure->id] = str_repeat('  ', $iLevel) . $oStructure->name;
					$aReturn += $this->fillStructureList($iSiteId, $oStructure->id, $bExclude, $iLevel + 1);
				}
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