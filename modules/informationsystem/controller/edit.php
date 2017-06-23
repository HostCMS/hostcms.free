<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		switch ($modelName)
		{
			case 'informationsystem':

				$title = $object->id
					? Core::_('Informationsystem.edit_title')
					: Core::_('Informationsystem.add_title');

				$oInformationsystemTabSorting = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem.information_systems_form_tab_2'))
					->name('Sorting');

				$oInformationsystemTabFormats = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem.information_systems_form_tab_3'))
					->name('Formats');

				$oInformationsystemTabImage = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem.information_systems_form_tab_4'))
					->name('Image');

				$oInformationsystemTabSorting
					->add($oSortingRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSortingRow2 = Admin_Form_Entity::factory('Div')->class('row'));

				$oInformationsystemTabFormats
					->add($oFormatsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oFormatsRow8 = Admin_Form_Entity::factory('Div')->class('row'));

				$oInformationsystemTabImage
					->add($oImageRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oImageRow8 = Admin_Form_Entity::factory('Div')->class('row'));

				$this
					->addTabAfter($oInformationsystemTabSorting, $oMainTab)
					->addTabAfter($oInformationsystemTabFormats, $oInformationsystemTabSorting)
					->addTabAfter($oInformationsystemTabImage, $oInformationsystemTabFormats);

				$oAdditionalTab->delete($this->getField('informationsystem_dir_id'));

				// Селектор с группой
				$oSelect_Dirs
					->options(
						array(' … ') + $this->_fillInformationsystemDir()
					)
					->name('informationsystem_dir_id')
					->value($this->_object->informationsystem_dir_id)
					->caption(Core::_('Informationsystem.information_systems_dirs_add_form_group'));

				$oMainTab->addAfter(
					$oSelect_Dirs, $this->getField('name')
				);

				$this->getField('description')
					->wysiwyg(TRUE)
					->template_id($this->_object->Structure->template_id
						? $this->_object->Structure->template_id
						: 0);

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

				$oMainTab->addAfter(
					$oSelect_Structure, $this->getField('description')
				);

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

				$oMainTab->addAfter(
					$oSelect_SiteUserGroup, $oSelect_Structure
				);

				// Список сайтов
				$oSelect_Sites = Admin_Form_Entity::factory('Select');
				$oSelect_Sites
					->options($oUser_Controller_Edit->fillSites())
					->divAttr(array('class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
					->name('site_id')
					->value($this->_object->site_id)
					->caption(Core::_('Informationsystem.site_name'));

				$oMainTab->addAfter(
					$oSelect_Sites, $oSelect_SiteUserGroup
				);

				$this->getField('items_on_page')
					->divAttr(array('class' => 'form-group col-sm-12 col-md-4 col-lg-4'));

				// Тип формирования URL информационных элементов
				$oMainTab->delete($this->getField('url_type'));

				$oSelect_UrlType = Admin_Form_Entity::factory('Select')
					->name('url_type')
					->caption(Core::_('Informationsystem.url_type'))
					->options(
						array(Core::_('Informationsystem.url_type_identificater'),
							Core::_('Informationsystem.url_type_transliteration'))
					)
					->divAttr(array('class' => 'form-group col-sm-12 col-md-4 col-lg-4'))
					->value($this->_object->url_type);

				$oMainTab->addAfter(
					$oSelect_UrlType, $this->getField('items_on_page')
				);

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
					->move($this->getField('image_large_max_width'), $oFormatsRow2)
					->move($this->getField('image_large_max_height'), $oFormatsRow2);

				$this->getField('image_small_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$this->getField('image_small_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainTab
					->move($this->getField('image_small_max_width'), $oFormatsRow3)
					->move($this->getField('image_small_max_height'), $oFormatsRow3);

				$this->getField('group_image_large_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$this->getField('group_image_large_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainTab
					->move($this->getField('group_image_large_max_width'), $oFormatsRow4)
					->move($this->getField('group_image_large_max_height'), $oFormatsRow4);

				$this->getField('group_image_small_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$this->getField('group_image_small_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));
				$oMainTab
					->move($this->getField('group_image_small_max_width'), $oFormatsRow5)
					->move($this->getField('group_image_small_max_height'), $oFormatsRow5);

				$oMainTab
					->move($this->getField('use_captcha'), $oFormatsRow6)
					->move($this->getField('typograph_default_items'), $oFormatsRow7)
					->move($this->getField('typograph_default_groups'), $oFormatsRow8);

				// Изображение
				$oWatermarkFileField = Admin_Form_Entity::factory('File');

				$watermarkPath =
					is_file($this->_object->getWatermarkFilePath())
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
					->move($this->getField('preserve_aspect_ratio'), $oImageRow2)
					->move($this->getField('preserve_aspect_ratio_small'), $oImageRow3)
					->move($this->getField('preserve_aspect_ratio_group'), $oImageRow4)
					->move($this->getField('preserve_aspect_ratio_group_small'), $oImageRow5)
					->move($this->getField('watermark_default_use_large_image'), $oImageRow6)
					->move($this->getField('watermark_default_use_small_image'), $oImageRow7)
					->move($this->getField('watermark_default_position_x'), $oImageRow8)
					->move($this->getField('watermark_default_position_y'), $oImageRow8);

				$this->getField('watermark_default_position_x')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$this->getField('watermark_default_position_y')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

			break;
			case 'informationsystem_dir':
			default:

				$title = $this->_object->id
					? Core::_('Informationsystem_Dir.information_systems_dir_edit_form_title')
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
		parent::_applyObjectProperty();

		if(
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

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
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