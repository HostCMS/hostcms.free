<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Property Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			13 => Core::_('Property.type13'),
			12 => Core::_('Property.type12'),
			14 => Core::_('Property.type14'),
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
			unset($this->_types[13]);
		}
		// Delete shop type if module is not active
		if (!Core::moduleIsActive('shop'))
		{
			unset($this->_types[12]);
			unset($this->_types[14]);
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
				->add($oMainRow12 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow13 = Admin_Form_Entity::factory('Div')->class('row'))
				->add($oMainRow14 = Admin_Form_Entity::factory('Div')->class('row'));

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

				$oMainRow2->add($oSelect_Type);

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('property_dir_id'));

				$aResult = $this->propertyDirShow($this->linkedObject, 'property_dir_id');
				foreach ($aResult as $resultItem)
				{
					$oMainRow2->add($resultItem);
				}

				// Список
				if (Core::moduleIsActive('list'))
				{
					$oAdditionalTab->delete($this->getField('list_id'));

					$oList_Controller_Edit = new List_Controller_Edit($this->_Admin_Form_Action);

					$oSite = Core_Entity::factory('Site', CURRENT_SITE);
					$iCountLists = $oSite->Lists->getCount();

					if ($iCountLists < Core::$mainConfig['switchSelectToAutocomplete'])
					{
						// Селектор с группой
						$oSelect_Lists = Admin_Form_Entity::factory('Select')
							->options(
								array(' … ') + $oList_Controller_Edit->fillLists(CURRENT_SITE)
							)
							->name('list_id')
							->value($this->_object->list_id)
							->caption(Core::_('Property.list_id'))
							->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'));

						$oMainRow3->add($oSelect_Lists);
					}
					else
					{
						$oList = Core_Entity::factory('List', $this->_object->list_id);

						$oListInput = Admin_Form_Entity::factory('Input')
							->caption(Core::_('Property.list_id'))
							->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'))
							->name('list_name');

						$this->_object->list_id
							&& $oListInput->value($oList->name . ' [' . $oList->id . ']');

						$oListInputHidden = Admin_Form_Entity::factory('Input')
							->divAttr(array('class' => 'form-group col-xs-12 hidden'))
							->name('list_id')
							->value($this->_object->list_id)
							->type('hidden');

						$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
							->value("
								$('#{$windowId} [name = list_name]').autocomplete({
									source: function(request, response) {
										$.ajax({
											url: '/admin/list/index.php?autocomplete=1&show_list=1&site_id={$oSite->id}',
											dataType: 'json',
											data: {
												queryString: request.term
											},
											success: function(data) {
												response(data);
											}
										});
									},
									minLength: 1,
									create: function() {
										$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
											return $('<li></li>')
												.data('item.autocomplete', item)
												.append($('<a>').text(item.label))
												.appendTo(ul);
										}
										$(this).prev('.ui-helper-hidden-accessible').remove();
									},
									select: function(event, ui) {
										$('#{$windowId} [name = list_id]').val(ui.item.id);
									},
									open: function() {
										$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
									},
									close: function() {
										$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
									}
								});
							");

						$oMainRow3
							->add($oListInput)
							->add($oListInputHidden)
							->add($oCore_Html_Entity_Script);
					}
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
						->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12 hidden-14'));

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
						->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11 hidden-13'));

					$oMainRow5->add($oSelect_Shops);
				}

				$this->getField('description')
					->divAttr(array('class' => 'form-group col-xs-12'))
					->rows(7)
					->wysiwyg(Core::moduleIsActive('wysiwyg'));

				$oMainTab->move($this->getField('description'), $oMainRow6);

				$this->getField('default_value')
					->divAttr(array('class' => 'form-group col-xs-12 hidden-2 hidden-5 hidden-7 hidden-8 hidden-9 hidden-12 hidden-13 hidden-14'));

				$oMainTab->move($this->getField('default_value'), $oMainRow7);

				$oDefault_Value_Date = Admin_Form_Entity::factory('Date')
					->value($this->_object->default_value == '0000-00-00 00:00:00' ? '' : $this->_object->default_value)
					->name('default_value_date')
					->caption(Core::_('Property.default_value'))
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-3 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-9 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'));

				$oMainRow8->add($oDefault_Value_Date);

				$oDefault_Value_DateTime = Admin_Form_Entity::factory('DateTime')
					->value($this->_object->default_value == '0000-00-00 00:00:00' ? '' : $this->_object->default_value)
					->name('default_value_datetime')
					->caption(Core::_('Property.default_value'))
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-3 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'));

				$oMainRow9->add($oDefault_Value_DateTime);

				$oDefault_Value_Checkbox = Admin_Form_Entity::factory('Checkbox')
					->value(1)
					->checked($this->_object->default_value == 1)
					->caption(Core::_('Property.default_value'))
					->name('default_value_checked')
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-4 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'));

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
					->move($this->getField('multiple'), $oMainRow12)
					->move($this->getField('obligatory'), $oMainRow13)
					->move($this->getField('indexing'), $oMainRow14);

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

				$aResult = $this->propertyDirShow($this->linkedObject, 'parent_id');
				foreach ($aResult as $resultItem)
				{
					$oMainRow2->add($resultItem);
				}

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
	 * Показ списка групп или поле ввода с autocomplete для большого количества групп
	 * @param string $fieldName имя поля группы
	 * @return array  массив элементов, для добавления в строку
	 */
	public function propertyDirShow($linkedObject, $fieldName)
	{
		$return = array();

		$iCountDirs = $linkedObject->Property_Dirs->getCount();

		switch (get_class($this->_object))
		{
			case 'Property_Model':
				$aExclude = array();
				$class = 'form-group col-xs-12 col-md-6';
			break;
			case 'Property_Dir_Model':
			default:
				$aExclude = array($this->_object->id);
				$class = 'form-group col-xs-12';
		}

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		if ($iCountDirs < Core::$mainConfig['switchSelectToAutocomplete'])
		{
			$oPropertyDirSelect = Admin_Form_Entity::factory('Select');
			$oPropertyDirSelect
				->caption(Core::_('Property_Dir.parent_id'))
				->options(array(' … ') + self::fillPropertyDir($linkedObject, 0, $aExclude))
				->name($fieldName)
				->value($this->_object->$fieldName)
				->divAttr(array('class' => $class));

			$return = array($oPropertyDirSelect);
		}
		else
		{
			$oProperty_Dir = Core_Entity::factory('Property_Dir', $this->_object->$fieldName);

			$oPropertyDirInput = Admin_Form_Entity::factory('Input')
				->caption(Core::_('Property_Dir.parent_id'))
				->divAttr(array('class' => $class))
				->name('property_dir_name');

			$this->_object->$fieldName
				&& $oPropertyDirInput->value($oProperty_Dir->name . ' [' . $oProperty_Dir->id . ']');

			$oPropertyDirInputHidden = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->name($fieldName)
				->value($this->_object->$fieldName)
				->type('hidden');

			$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
				->value("
					$('#{$windowId} [name = property_dir_name]').autocomplete({
						source: function(request, response) {
							$.ajax({
								url: '/admin/property/index.php?autocomplete=1&show_dir=1&linkedObjectName=" . $linkedObject->getModelName() . "&linkedObjectId=" . $linkedObject->getPrimaryKey() . "',
								dataType: 'json',
								data: {
									queryString: request.term
								},
								success: function(data) {
									response(data);
								}
							});
						},
						minLength: 1,
						create: function() {
							$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
								return $('<li></li>')
									.data('item.autocomplete', item)
									.append($('<a>').text(item.label))
									.appendTo(ul);
							}
							$(this).prev('.ui-helper-hidden-accessible').remove();
						},
						select: function(event, ui) {
							$('#{$windowId} [name = {$fieldName}]').val(ui.item.id);
						},
						open: function() {
							$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
						},
						close: function() {
							$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
						}
					});
				");

			$return = array($oPropertyDirInput, $oPropertyDirInputHidden, $oCore_Html_Entity_Script);
		}

		return $return;
	}

	/**
	 * Create visual tree of the directories
	 * @param object $linkedObject
	 * @param int $iPropertyDirParentId parent directory ID
	 * @param array $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillPropertyDir($linkedObject, $iPropertyDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iPropertyDirParentId = intval($iPropertyDirParentId);
		$iLevel = intval($iLevel);

		$childrenDirs = $linkedObject->Property_Dirs->getByParentId($iPropertyDirParentId);

		$countExclude = count($aExclude);

		$aReturn = array();
		foreach ($childrenDirs as $childrenDir)
		{
			if ($countExclude == 0 || !in_array($childrenDir->id, $aExclude))
			{
				$aReturn[$childrenDir->id] = str_repeat('  ', $iLevel) . $childrenDir->name . ' [' . $childrenDir->id . ']';
				$aReturn += self::fillPropertyDir($linkedObject, $childrenDir->id, $aExclude, $iLevel + 1);
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
					Core::$mainConfig['translate'] && $sTranslated = Core_Str::translate($this->_object->name);

					$this->_object->tag_name = Core::$mainConfig['translate'] && strlen($sTranslated)
						? $sTranslated
						: $this->_object->name;
					
					$this->_object->tag_name = Core_Str::transliteration($this->_object->tag_name);
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