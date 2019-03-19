<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Property_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 *
	 * @var array
	 */
	protected $_types = array();

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 * @hostcms-event Property_Controller_Edit.onAfterConstruct
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		$this->_allowedProperties[] = 'linkedObject';

		parent::__construct($oAdmin_Form_Action);

		$this->_types = array(
			0 => Core::_('Property.type0'),
			11 => Core::_('Property.type11'),
			1 => Core::_('Property.type1'),
			2 => Core::_('Property.type2'),
			3 => Core::_('Property.type3'),
			4 => Core::_('Property.type4'),
			5 => Core::_('Property.type5'),
			12 => Core::_('Property.type12'),
			6 => Core::_('Property.type6'),
			7 => Core::_('Property.type7'),
			8 => Core::_('Property.type8'),
			9 => Core::_('Property.type9'),
			10 => Core::_('Property.type10')
		);

		// Delete list type if module is not active
		if (!Core::moduleIsActive('list'))
		{
			unset($this->_types[3]);
		}
		// Delete informationsystem type if module is not active
		if (!Core::moduleIsActive('informationsystem'))
		{
			unset($this->_types[5]);
		}
		// Delete shop type if module is not active
		if (!Core::moduleIsActive('shop'))
		{
			unset($this->_types[12]);
		}

		Core_Event::notify(get_class($this) . '.onAfterConstruct', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Get Property Types
	 * @return array
	 */
	public function getTypes()
	{
		return $this->_types;
	}

	/**
	 * Set Property Types
	 * @param array $types
	 * @return self
	 */
	public function setTypes(array $types)
	{
		$this->_types = $types;
		return $this;
	}

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		$bNewProperty = !$object->id;

		if ($bNewProperty && $modelName == 'property')
		{
			$object->image_large_max_width = $this->linkedObject->getLargeImageMaxWidth();
			$object->image_large_max_height = $this->linkedObject->getLargeImageMaxHeight();
			$object->image_small_max_width = $this->linkedObject->getSmallImageMaxWidth();
			$object->image_small_max_height = $this->linkedObject->getSmallImageMaxHeight();

			if (method_exists($this->linkedObject, 'preserveAspectRatioOfLargeImage')
				&& method_exists($this->linkedObject, 'preserveAspectRatioOfSmallImage'))
			{
				$object->preserve_aspect_ratio = $this->linkedObject->preserveAspectRatioOfLargeImage();
				$object->preserve_aspect_ratio_small = $this->linkedObject->preserveAspectRatioOfSmallImage();
			}

			if (method_exists($this->linkedObject, 'layWatermarOnLargeImage')
				&& method_exists($this->linkedObject, 'layWatermarOnSmallImage'))
			{
				$object->watermark_default_use_large_image = $this->linkedObject->layWatermarOnLargeImage();
				$object->watermark_default_use_small_image = $this->linkedObject->layWatermarOnSmallImage();
			}
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 * @hostcms-event Property_Controller_Edit.onBeforePrepareForm
	 * @hostcms-event Property_Controller_Edit.onAfterPrepareForm
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		Core_Event::notify('Property_Controller_Edit.onBeforePrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));
		
		$bNewProperty = !$this->_object->id;

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');
		$oSelect_Dirs = Admin_Form_Entity::factory('Select');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		$this->getField('name')
			->divAttr(array('class' => 'form-group col-xs-12'));
		$oMainTab
			->move($this->getField('name'), $oMainRow1);

		switch ($modelName)
		{
			case 'property':

			$oMainTab
				->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow10 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow11 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow12 = Admin_Form_Entity::factory('Div')->class('row'));

				$title = $this->_object->id
					? Core::_('Property.edit_title', $this->_object->name)
					: Core::_('Property.add_title');

				!$this->_object->id && $this->_object->property_dir_id = Core_Array::getGet('property_dir_id');

				$oFormatTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Property.tab_format'))
					->name('Format');

				$this
					->addTabAfter($oFormatTab, $oMainTab);

				// Удаляем стандартный <input>
				$oMainTab->delete($this->getField('type'));

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$aListTypes = $this->getTypes();

				$sRadiogroupOnChangeList = implode(',', array_keys($aListTypes));

				// Селектор с группой
				$oSelect_Type = Admin_Form_Entity::factory('Select')
					->options($aListTypes)
					->name('type')
					->value($this->_object->type)
					->caption(Core::_('Property.type'))
					->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [{$sRadiogroupOnChangeList}])")
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('property_dir_id'));

				// Селектор с группой
				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillPropertyDir()
					)
					->name('property_dir_id')
					->value($this->_object->property_dir_id)
					->caption(Core::_('Property_Dir.parent_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow2
					->add($oSelect_Type)
					->add($oSelect_Dirs);

				// Список
				if (Core::moduleIsActive('list'))
				{
					$oAdditionalTab->delete($this->getField('list_id'));

					$oList_Controller_Edit = new List_Controller_Edit($this->_Admin_Form_Action);
					// Селектор с группой
					$oSelect_Lists = Admin_Form_Entity::factory('Select')
						->options(
							array(' … ') + $oList_Controller_Edit->fillLists(CURRENT_SITE)
						)
						->name('list_id')
						->value($this->_object->list_id)
						->caption(Core::_('Property.list_id'))
						->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12'));

					$oMainRow3->add($oSelect_Lists);
				}

				// Информационные системы
				if (Core::moduleIsActive('informationsystem'))
				{
					$oAdditionalTab->delete($this->getField('informationsystem_id'));

					$oInformationsystem_Controller_Edit = new Informationsystem_Controller_Edit($this->_Admin_Form_Action);
					// Селектор с группой
					$oSelect_Informationsystems = Admin_Form_Entity::factory('Select')
						->options(
							array(' … ') + $oInformationsystem_Controller_Edit->fillInformationsystems(CURRENT_SITE)
						)
						->name('informationsystem_id')
						->value($this->_object->informationsystem_id)
						->caption(Core::_('Property.informationsystem_id'))
						->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12'));

					$oMainRow4->add($oSelect_Informationsystems);
				}

				// Магазин
				if (Core::moduleIsActive('shop'))
				{
					$oAdditionalTab->delete($this->getField('shop_id'));

					$oShop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);
					// Селектор с группой
					$oSelect_Shops = Admin_Form_Entity::factory('Select')
						->options(
							array(' … ') + $oShop_Controller_Edit->fillShops(CURRENT_SITE)
						)
						->name('shop_id')
						->value($this->_object->shop_id)
						->caption(Core::_('Property.shop_id'))
						->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11'));

					$oMainRow5->add($oSelect_Shops);
				}

				$this->getField('description')
					->divAttr(array('class' => 'form-group col-xs-12'))
					->wysiwyg(Core::moduleIsActive('wysiwyg'));

				$oMainTab->move($this->getField('description'), $oMainRow6);

				$this->getField('default_value')
					->divAttr(array('class' => 'form-group col-xs-12 hidden-2 hidden-5 hidden-7 hidden-8 hidden-9 hidden-12'));

				$oMainTab->move($this->getField('default_value'), $oMainRow7);

				$oDefault_Value_Date = Admin_Form_Entity::factory('Date')
					->value($this->_object->default_value)
					->name('default_value_date')
					->caption(Core::_('Property.default_value'))
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-3 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-9 hidden-10 hidden-11 hidden-12'));

				$oMainRow8->add($oDefault_Value_Date);

				$oDefault_Value_DateTime = Admin_Form_Entity::factory('DateTime')
					->value($this->_object->default_value)
					->name('default_value_datetime')
					->caption(Core::_('Property.default_value'))
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-3 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-10 hidden-11 hidden-12'));

				$oMainRow9->add($oDefault_Value_DateTime);

				$oDefault_Value_Checkbox = Admin_Form_Entity::factory('Checkbox')
					->value($this->_object->default_value)
					->caption(Core::_('Property.default_value'))
					->name('default_value_checked')
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-4 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12'));

				$oMainRow10->add($oDefault_Value_Checkbox);

				$this->getField('tag_name')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				// Для тегов проверка на длину только при редактировании.
				!$bNewProperty && $this->getField('tag_name')->format(
						array(
							'maxlen' => array('value' => 255),
							'minlen' => array('value' => 1)
						)
					);

				$this->getField('sorting')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainTab
					->move($this->getField('tag_name'), $oMainRow11)
					->move($this->getField('sorting'), $oMainRow11)
					->move($this->getField('multiple'), $oMainRow12);

				$oFormatTab
					->add($oMainRow13 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow14 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow15 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow16 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow17 = Admin_Form_Entity::factory('Div')->class('row'));

				$oAdditionalTab
					->add($oMainRow18 = Admin_Form_Entity::factory('Div')->class('row'))
					// ->add($oMainRow19 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				// Formats
				$this->getField('image_large_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$this->getField('image_large_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$this->getField('image_small_max_width')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$this->getField('image_small_max_height')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$this->getField('preserve_aspect_ratio')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$this->getField('preserve_aspect_ratio_small')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$this->getField('hide_small_image')
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainTab
					->move($this->getField('image_large_max_width'), $oMainRow13)
					->move($this->getField('image_large_max_height'), $oMainRow13)
					->move($this->getField('preserve_aspect_ratio'), $oMainRow14)
					->move($this->getField('image_small_max_width'), $oMainRow15)
					->move($this->getField('image_small_max_height'), $oMainRow15)
					->move($this->getField('preserve_aspect_ratio_small'), $oMainRow16)
					->move($this->getField('hide_small_image'), $oMainRow16)
					->move($this->getField('watermark_default_use_large_image')->divAttr(array('class' => 'form-group col-xs-12 col-md-6')), $oMainRow17)
					->move($this->getField('watermark_default_use_small_image')->divAttr(array('class' => 'form-group col-xs-12 col-md-6')), $oMainRow17)
					->move($this->getField('guid'), $oMainRow18);

				$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
				$oAdmin_Form_Entity_Code->html(
					"<script>radiogroupOnChange('{$windowId}', " . intval($this->_object->type) . ", [{$sRadiogroupOnChangeList}])</script>"
				);

				$oMainTab->add($oAdmin_Form_Entity_Code);
			break;
			case 'property_dir':
			default:
				$title = $this->_object->id
					? Core::_('Property_Dir.edit_title', $this->_object->name)
					: Core::_('Property_Dir.add_title');

				// Значения директории для добавляемого объекта
				!$this->_object->id && $this->_object->parent_id = Core_Array::getGet('property_dir_id');

				$oAdditionalTab->delete($this->getField('parent_id'));

				$this->getField('name')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainTab
					->move($this->getField('name'), $oMainRow1);

				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillPropertyDir(0, $this->_object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Property_Dir.parent_id'))
					->divAttr(array('class' => 'form-group col-xs-12'));

				$oMainRow2->add($oSelect_Dirs);

				$oMainTab
					->move($this->getField('description'), $oMainRow3)
					->move($this->getField('sorting'), $oMainRow4);

			break;
		}

		$this->title($title);

		Core_Event::notify('Property_Controller_Edit.onAfterPrepareForm', $this, array($this->_object, $this->_Admin_Form_Controller));
		
		return $this;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iPropertyDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	public function fillPropertyDir($iPropertyDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iPropertyDirParentId = intval($iPropertyDirParentId);
		$iLevel = intval($iLevel);

		$childrenDirs = $this->linkedObject->Property_Dirs->getByParentId($iPropertyDirParentId);

		$aReturn = array();

		foreach ($childrenDirs as $childrenDir)
		{
			if ($bExclude != $childrenDir->id)
			{
				$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name;
				$aReturn += $this->fillPropertyDir($childrenDir->id, $bExclude, $iLevel+1);
			}
		}

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Property_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$bNewProperty = is_null($this->_object->id);

		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'property':
				if ($bNewProperty && trim($this->_object->tag_name) == '')
				{
					 $this->_object->tag_name = Core_Str::transliteration(
						Core::$mainConfig['translate']
							? Core_Str::translate($this->_object->name)
							: $this->_object->name
						);
				}

				switch ($this->_object->type)
				{
					case 7: // Флажок
						$this->_object->default_value = Core_Array::getPost('default_value_checked', 0);
					break;
					case 8: // Дата
						$this->_object->default_value = strlen(Core_Array::getPost('default_value_date'))
							? Core_Date::date2sql(Core_Array::getPost('default_value_date'))
							: '0000-00-00 00:00:00';
					break;
					case 9: // Дата-время
						$this->_object->default_value = strlen(Core_Array::getPost('default_value_datetime'))
							? Core_Date::datetime2sql(Core_Array::getPost('default_value_datetime'))
							: '0000-00-00 00:00:00';
					break;
				}
				$this->_object->save();
			break;
			case 'property_dir':
			break;
		}

		if (!Core_Array::getPost('id'))
		{
			$this->linkedObject->add($this->_object);
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}