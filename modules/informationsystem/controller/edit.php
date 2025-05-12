<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Informationsystem_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'informationsystem':
				// Исключение поля из формы и обработки
				$this->addSkipColumn('watermark_file');

				if (!$object->id)
				{
					$object->informationsystem_dir_id = Core_Array::getGet('informationsystem_dir_id');
				}
			break;
			case 'informationsystem_dir':
			default:
				// Значения директории для добавляемого объекта
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('informationsystem_dir_id');
				}
			break;
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

		$object = $this->_object;

		$modelName = $object->getModelName();

		$oSelect_Dirs = Admin_Form_Entity::factory('Select');

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		switch ($modelName)
		{
			case 'informationsystem':

				$title = $object->id
					? Core::_('Informationsystem.edit_title', $object->name, FALSE)
					: Core::_('Informationsystem.add_title');

				$oMainTab
					// ->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oInformationsystemTabSorting = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem.information_systems_form_tab_2'))
					->name('Sorting');

				$oInformationsystemTabFormats = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem.information_systems_form_tab_3'))
					->name('Formats');

				$oInformationsystemTabImage = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem.information_systems_form_tab_4'))
					->name('Image');

				$oInformationsystemTabSeoTemplates = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem.tab_seo_templates'))
					->name('Seo_Templates');

				$oInformationsystemTabSorting
					->add($oSortingRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSortingRow2 = Admin_Form_Entity::factory('Div')->class('row'));

				$oInformationsystemTabFormats
					->add($oFormatsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oInformationsystemTabImage
					->add($oImageItemsBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
					->add($oImageGroupsBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
					// ->add($oImageRowSize1 = Admin_Form_Entity::factory('Div')->class('row'))
					// ->add($oImageRowSize2 = Admin_Form_Entity::factory('Div')->class('row'))
					// ->add($oImageRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					// ->add($oImageRowSize3 = Admin_Form_Entity::factory('Div')->class('row'))
					// ->add($oImageRowSize4 = Admin_Form_Entity::factory('Div')->class('row'))
					// ->add($oImageRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow7 = Admin_Form_Entity::factory('Div')->class('row'));


				$oImageItemsBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-palegreen')
						->value(Core::_("Informationsystem.watermark_item_header"))
					)
					->add($oImageItemsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageItemsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageItemsRow3 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oImageGroupsBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-darkorange')
						->value(Core::_("Informationsystem.watermark_group_header"))
					)
					->add($oImageGroupsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageGroupsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageGroupsRow3 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oInformationsystemTabSeoTemplates
					->add($oInformationsystemGroupBlock = Admin_Form_Entity::factory('Div')->class('well with-header'))
					->add($oInformationsystemItemBlock = Admin_Form_Entity::factory('Div')->class('well with-header'))
					->add($oInformationsystemRootBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));

				$oInformationsystemGroupBlock
					->add($oInformationsystemGroupHeaderDiv = Admin_Form_Entity::factory('Div')
						->class('header bordered-darkorange')
						->value(Core::_("Informationsystem.seo_group_header"))
					)
					->add($oInformationsystemGroupBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemGroupBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemGroupBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemGroupBlockRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$oInformationsystemGroupHeaderDiv
					->add(Admin_Form_Entity::factory('Code')->html(
						Informationsystem_Controller::showGroupButton()
					));

				$oInformationsystemItemBlock
					->add($oInformationsystemItemHeaderDiv = Admin_Form_Entity::factory('Div')
						->class('header bordered-palegreen')
						->value(Core::_("Informationsystem.seo_item_header"))
					)
					->add($oInformationsystemItemBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemItemBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemItemBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemItemBlockRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$oInformationsystemItemHeaderDiv
					->add(Admin_Form_Entity::factory('Code')->html(
						Informationsystem_Controller::showItemButton()
					));

				$oInformationsystemRootBlock
					->add($oInformationsystemRootHeaderDiv = Admin_Form_Entity::factory('Div')
						->class('header bordered-warning')
						->value(Core::_("Informationsystem.seo_root_header"))
					)
					->add($oInformationsystemRootBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemRootBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemRootBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemRootBlockRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$oInformationsystemRootHeaderDiv
					->add(Admin_Form_Entity::factory('Code')->html(
						Informationsystem_Controller::showRootButton()
					));

				$this
					->addTabAfter($oInformationsystemTabSorting, $oMainTab)
					->addTabAfter($oInformationsystemTabFormats, $oInformationsystemTabSorting)
					->addTabAfter($oInformationsystemTabSeoTemplates, $oInformationsystemTabFormats)
					->addTabAfter($oInformationsystemTabImage, $oInformationsystemTabSeoTemplates);

				$oAdditionalTab->delete($this->getField('informationsystem_dir_id'));

				// Селектор с группой
				$oSelect_Dirs
					->options(
						array(' … ') + $this->_fillInformationsystemDir()
					)
					->name('informationsystem_dir_id')
					->value($this->_object->informationsystem_dir_id)
					->caption(Core::_('Informationsystem.information_systems_dirs_add_form_group'));

				$oMainRow2->add($oSelect_Dirs);

				$this->getField('description')
					->rows(10)
					->wysiwyg(Core::moduleIsActive('wysiwyg'))
					->template_id($this->_object->Structure->template_id
						? $this->_object->Structure->template_id
						: 0);

				$oMainTab->move($this->getField('description'), $oMainRow3);

				// Удаляем стандартный <input>
				$oAdditionalTab->delete(
					 $this->getField('site_id')
				);

				$oUser_Controller_Edit = new User_Controller_Edit($this->_Admin_Form_Action);

				// Список узлов структуры
				$oAdditionalTab->delete($this->getField('structure_id'));

				$Structure_Controller_Edit = new Structure_Controller_Edit($this->_Admin_Form_Action);

				$oSelect_Structure = Admin_Form_Entity::factory('Select')
					->name('structure_id')
					->caption(Core::_('Informationsystem.structure_name'))
					->options(
						array(' … ') + $Structure_Controller_Edit->fillStructureList($this->_object->site_id)
					)
					->divAttr(array('class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
					->value($this->_object->structure_id);

				$oMainRow4->add($oSelect_Structure);

				// Список групп пользователей сайта
				$oAdditionalTab->delete($this->getField('siteuser_group_id'));

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
					->caption(Core::_('Informationsystem.siteuser_group_id'))
					->options(
						array(Core::_('Informationsystem.information_all')) + $aSiteuser_Groups
					)
					->divAttr(array('class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
					->value($this->_object->siteuser_group_id);

				$oMainRow4->add($oSelect_SiteUserGroup);

				// Список сайтов
				$oSelect_Sites = Admin_Form_Entity::factory('Select');
				$oSelect_Sites
					->options($oUser_Controller_Edit->fillSites())
					->divAttr(array('class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
					->name('site_id')
					->value($this->_object->site_id)
					->caption(Core::_('Informationsystem.site_name'));

				$oMainRow4->add($oSelect_Sites);

				$oMainTab->move($this->getField('items_on_page')->divAttr(array('class' => 'form-group col-sm-12 col-md-4 col-lg-4')), $oMainRow5);

				// Тип формирования URL информационных элементов
				$oMainTab->delete($this->getField('url_type'));

				$oSelect_UrlType = Admin_Form_Entity::factory('Select')
					->name('url_type')
					->caption(Core::_('Informationsystem.url_type'))
					->options(
						array(
							Core::_('Informationsystem.url_type_identificater'),
							Core::_('Informationsystem.url_type_transliteration'),
							Core::_('Informationsystem.url_type_date')
						)
					)
					->divAttr(array('class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
					->value($this->_object->url_type)
					->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1,2]); window.dispatchEvent(new Event('resize'));");

				$oMainRow5->add($oSelect_UrlType);

				$oMainTab
					->move($this->getField('path_date_format')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 hidden-0 hidden-1')), $oMainRow5)
					->move($this->getField('apply_tags_automatically')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow6)
					->move($this->getField('apply_keywords_automatically')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow7)
					;

				// Удаляем с основной вкладки поля сортировки
				$oMainTab
					->delete($this->getField('items_sorting_field'))
					->delete($this->getField('items_sorting_direction'))
					->delete($this->getField('groups_sorting_field'))
					->delete($this->getField('groups_sorting_direction'));

				// Список полей сортировки элементов
				$oSelect_ItemsSortingField = Admin_Form_Entity::factory('Select')
					->options(array(Core::_('Informationsystem.information_date'),
						Core::_('Informationsystem.show_information_groups_name'),
						Core::_('Informationsystem.show_information_propertys_order')
						)
					)
					->name('items_sorting_field')
					->value($this->_object->items_sorting_field)
					->caption(Core::_('Informationsystem.information_systems_add_form_order_field'))
					->divAttr(array('class' => "form-group col-lg-6 col-md-6 col-sm-6"));


				// Направление сортировки элементов
				$oSelect_ItemsSortingDirection = Admin_Form_Entity::factory('Select')
					->options(array(Core::_('Informationsystem.sort_to_increase'),
						Core::_('Informationsystem.sort_to_decrease'))
					)
					->name('items_sorting_direction')
					->value($this->_object->items_sorting_direction)
					->caption(Core::_('Informationsystem.information_systems_add_form_order_type'))
					->divAttr(array('class' => "form-group col-lg-6 col-md-6 col-sm-6"));


				// Список полей сортировки групп
				$oSelect_GroupsSortingField = Admin_Form_Entity::factory('Select')
					->options(array(Core::_('Informationsystem.show_information_groups_name'),
						Core::_('Informationsystem.show_information_propertys_order'))
					)
					->name('groups_sorting_field')
					->value($this->_object->groups_sorting_field)
					->caption(Core::_('Informationsystem.is_sort_field_group_title'))
					->divAttr(array('class' => "form-group col-lg-6 col-md-6 col-sm-6"));

				// Направление сортировки групп
				$oSelect_GroupsSortingDirection = Admin_Form_Entity::factory('Select')
					->options(array(Core::_('Informationsystem.sort_to_increase'),
						Core::_('Informationsystem.sort_to_decrease'))
					)
					->name('groups_sorting_direction')
					->value($this->_object->groups_sorting_direction)
					->caption(Core::_('Informationsystem.is_sort_order_group_type'))
					->divAttr(array('class' => "form-group col-lg-6 col-md-6 col-sm-6"));

				// Добавление полей сортировки на вкладку "Сортировка"
				$oSortingRow1
					->add($oSelect_ItemsSortingField)
					->add($oSelect_ItemsSortingDirection);
				$oSortingRow2
					->add($oSelect_GroupsSortingField)
					->add($oSelect_GroupsSortingDirection);

				// Форматы
				$this->getField('format_date')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$this->getField('format_datetime')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainTab
					->move($this->getField('format_date'), $oFormatsRow1)
					->move($this->getField('format_datetime'), $oFormatsRow1);

				$this->getField('image_large_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$this->getField('image_large_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainTab
					->move($this->getField('image_large_max_width'), $oImageItemsRow1)
					->move($this->getField('image_large_max_height'), $oImageItemsRow1);

				$this->getField('image_small_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$this->getField('image_small_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainTab
					->move($this->getField('image_small_max_width'), $oImageItemsRow2)
					->move($this->getField('image_small_max_height'), $oImageItemsRow2);

				$this->getField('group_image_large_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$this->getField('group_image_large_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainTab
					->move($this->getField('group_image_large_max_width'), $oImageGroupsRow1)
					->move($this->getField('group_image_large_max_height'), $oImageGroupsRow1);

				$this->getField('group_image_small_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$this->getField('group_image_small_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$oMainTab
					->move($this->getField('group_image_small_max_width'), $oImageGroupsRow2)
					->move($this->getField('group_image_small_max_height'), $oImageGroupsRow2);

				$oMainTab
					->move($this->getField('typograph_default_items')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oFormatsRow2)
					->move($this->getField('typograph_default_groups')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oFormatsRow2)
					->move($this->getField('use_captcha'), $oFormatsRow3);

				// Seo templates
				$oMainTab
					->move($this->getField('seo_group_title_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemGroupBlockRow1)
					->move($this->getField('seo_group_description_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemGroupBlockRow2)
					->move($this->getField('seo_group_keywords_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemGroupBlockRow3)
					->move($this->getField('seo_group_h1_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemGroupBlockRow4)
					->move($this->getField('seo_item_title_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemItemBlockRow1)
					->move($this->getField('seo_item_description_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemItemBlockRow2)
					->move($this->getField('seo_item_keywords_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemItemBlockRow3)
					->move($this->getField('seo_item_h1_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemItemBlockRow4)
					->move($this->getField('seo_root_title_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemRootBlockRow1)
					->move($this->getField('seo_root_description_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemRootBlockRow2)
					->move($this->getField('seo_root_keywords_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemRootBlockRow3)
					->move($this->getField('seo_root_h1_template')->divAttr(array('class' => 'form-group col-xs-12'))->rows(1), $oInformationsystemRootBlockRow4);

				// Изображение
				$oWatermarkFileField = Admin_Form_Entity::factory('File');

				$watermarkPath = $this->_object->watermark_file != '' && Core_File::isFile($this->_object->getWatermarkFilePath())
					? $this->_object->getWatermarkFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oWatermarkFileField
					->type('file')
					->caption(Core::_('Informationsystem.watermark_file'))
					->name('watermark_file')
					->id('watermark_file')
					->largeImage(
						array(
							'path' => $watermarkPath,
							'show_params' => FALSE,
							'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteWatermarkFile', windowId: '{$windowId}'}); return false",
						)
					)
					->smallImage(
						array(
							'show' => FALSE
						)
					);

				$oImageRow1->add($oWatermarkFileField);

				$oMainTab
					->move($this->getField('preserve_aspect_ratio')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oImageItemsRow3)
					->move($this->getField('preserve_aspect_ratio_small')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oImageItemsRow3)
					->move($this->getField('preserve_aspect_ratio_group')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oImageGroupsRow3)
					->move($this->getField('preserve_aspect_ratio_group_small')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oImageGroupsRow3)
					->move($this->getField('watermark_default_use_large_image')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oImageRow4)
					->move($this->getField('watermark_default_use_small_image')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oImageRow4)
					->move($this->getField('watermark_default_position_x')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oImageRow5)
					->move($this->getField('watermark_default_position_y')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oImageRow5)
					->move($this->getField('create_small_image')->divAttr(array('class' => 'form-group col-xs-12')), $oImageRow6)
					->move($this->getField('change_filename')->divAttr(array('class' => 'form-group col-xs-12')), $oImageRow7);

				$oMainTab->add(
					Admin_Form_Entity::factory('Code')
						->html("<script>radiogroupOnChange('{$windowId}', '{$this->_object->url_type}', [0,1,2])</script>")
				);
			break;
			case 'informationsystem_dir':
			default:

				$title = $this->_object->id
					? Core::_('Informationsystem_Dir.information_systems_dir_edit_form_title', $this->_object->name, FALSE)
					: Core::_('Informationsystem_Dir.information_systems_dir_add_form_title');

				// Удаляем стандартный <input>
				$oAdditionalTab->delete(
					 $this->getField('parent_id')
				);

				$oSelect_Dirs
					->options(
						array(' … ') + $this->_fillInformationsystemDir(0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Informationsystem_Dir.parent_name'));

				$oMainTab->addAfter($oSelect_Dirs, $this->getField('description'));
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$modelName = $this->_object->getModelName();

			if ($modelName == 'informationsystem')
			{
				$oInformationsystem = Core_Entity::factory('Informationsystem');

				$iStructureId = intval(Core_Array::get($this->_formValues, 'structure_id'));

				$oInformationsystem->queryBuilder()
					->where('informationsystems.structure_id', '=', $iStructureId);

				$aInformationsystems = $oInformationsystem->findAll();

				$iCount = count($aInformationsystems);

				if ($iStructureId
					&& $iCount
					&& (!$this->_object->id || $iCount > 1 || $aInformationsystems[0]->id != $this->_object->id)
				)
				{
					$oStructure = Core_Entity::factory('Structure', $iStructureId);

					$this->addMessage(
						Core_Message::get(
							Core::_('Informationsystem.structureIsExist', $oStructure->name),
							'error'
						)
					);

					return TRUE;
				}
			}
		}

		return parent::execute($operation);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Informationsystem_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'informationsystem':
				// Backup revision
				if (Core::moduleIsActive('revision') && $this->_object->id)
				{
					$this->_object->backupRevision();
				}

			break;
		}

		parent::_applyObjectProperty();

		if (
			// Поле файла существует
			!is_null($aFileData = Core_Array::getFiles('watermark_file', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0)
		{
			if (Core_File::isValidExtension($aFileData['name'], array('png')))
			{
				$this->_object->saveWatermarkFile($aFileData['tmp_name']);
			}
			else
			{
				$this->addMessage(
					Core_Message::get(
						Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		Core::moduleIsActive('wysiwyg') && Wysiwyg_Controller::uploadImages($this->_formValues, $this->_object, $this->_Admin_Form_Controller);

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iInformationsystemDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	protected function _fillInformationsystemDir($iInformationsystemDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iInformationsystemDirParentId = intval($iInformationsystemDirParentId);
		$iLevel = intval($iLevel);

		$oInformationsystem_Dir = Core_Entity::factory('Informationsystem_Dir', $iInformationsystemDirParentId);

		$aReturn = array();

		// Дочерние разделы
		$childrenDirs = $oInformationsystem_Dir->Informationsystem_Dirs;
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
					$aReturn += $this->_fillInformationsystemDir($childrenDir->id, $bExclude, $iLevel+1);
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Fill list of information systems for site
	 * @param int $iSiteId site ID
	 * @return array
	 */
	public function fillInformationsystems($iSiteId)
	{
		$iSiteId = intval($iSiteId);

		$aReturn = array();

		$aObjects = Core_Entity::factory('Site', $iSiteId)->Informationsystems->findAll();

		foreach ($aObjects as $oObject)
		{
			$aReturn[$oObject->id] = $oObject->name;
		}

		return $aReturn;
	}
}