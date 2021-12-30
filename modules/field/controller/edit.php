<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Field Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 *
	 * @var array
	 */
	static protected $_aDirTree = array();

	protected $_types = array();

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 * @hostcms-event Field_Controller_Edit.onAfterConstruct
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->_types = array(
			0 => Core::_('Field.type0'),
			11 => Core::_('Field.type11'),
			1 => Core::_('Field.type1'),
			2 => Core::_('Field.type2'),
			3 => Core::_('Field.type3'),
			4 => Core::_('Field.type4'),
			5 => Core::_('Field.type5'),
			13 => Core::_('Field.type13'),
			12 => Core::_('Field.type12'),
			14 => Core::_('Field.type14'),
			6 => Core::_('Field.type6'),
			7 => Core::_('Field.type7'),
			8 => Core::_('Field.type8'),
			9 => Core::_('Field.type9'),
			10 => Core::_('Field.type10')
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
	 * Get Field Types
	 * @return array
	 */
	public function getTypes()
	{
		return $this->_types;
	}

	/**
	 * Set Field Types
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

		switch ($modelName)
		{
			case 'field':

				if (!$object->id)
				{
					$object->field_dir_id = Core_Array::getGet('field_dir_id');
				}
			break;
			case 'field_dir':
			default:
				// Значения директории для добавляемого объекта
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('field_dir_id');
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
		$model = Core_Array::getGet('model');

		$oSelect_Dirs = Admin_Form_Entity::factory('Select');

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		// $oMainTab->delete($this->getField('sorting'));
		$sModel = Core_Array::getGet('model');
		$oMainTab->delete($this->getField('model'));
		$oModel_Field = $this->getField('model');

		$oModel_Field
			->value($sModel)
			->readonly('readonly')
			->class('form-control');

		$oAdditionalTab
			->add($oRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab->add($this->getField('model'));

		$oAdditionalTab->move($this->getField('model')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oRow1);

		switch ($modelName)
		{
			case 'field':
				$title = $object->id
					? Core::_('Field.edit_title', $object->name)
					: Core::_('Field.add_title');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oFormatTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Field.tab_format'))
					->name('Format');

				$this
					->addTabAfter($oFormatTab, $oMainTab);

				$oMainTab->move($this->getField('name')->class('form-control input-lg'), $oMainRow1);

				$aExclude = array();

				// Селектор с группой
				$oSelect_Dirs
					->options(
						array(' … ') + $this::fillFieldDir($model,0, $aExclude)
					)
					->name('field_dir_id')
					->value($this->_object->field_dir_id)
					->caption(Core::_('Field.field_dirs_add_form_group'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-4'));

				$oMainRow2->add($oSelect_Dirs);

				$oAdditionalTab->delete($this->getField('field_dir_id'));

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
					->caption(Core::_('Field.type'))
					->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [{$sRadiogroupOnChangeList}])")
					->divAttr(array('class' => 'form-group col-xs-12 col-md-4'));

				// $oMainTab->addBefore($oSelect_Type, $this->getField('description'));

				$oMainRow2->add($oSelect_Type);

				$oMainTab->delete($this->getField('site_id'));

				$oUser_Controller_Edit = new User_Controller_Edit($this->_Admin_Form_Action);

				// Список сайтов
				$oSelect_Sites = Admin_Form_Entity::factory('Select');

				$aSites = array(0 => Core::_('Field.not_restrict')) + $oUser_Controller_Edit->fillSites();

				$oSelect_Sites
					->options($aSites)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-4'))
					->name('site_id')
					->value($this->_object->site_id)
					->caption(Core::_('Field.site_name'));

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
							->caption(Core::_('Field.list_id'))
							->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'));

						$oMainRow3->add($oSelect_Lists);
					}
					else
					{
						$oList = Core_Entity::factory('List', $this->_object->list_id);

						$oListInput = Admin_Form_Entity::factory('Input')
							->caption(Core::_('Field.list_id'))
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
							->add($oCore_Html_Entity_Script)
							;
					}
				}

				$oMainRow2->add($oSelect_Sites);

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
						->caption(Core::_('Field.informationsystem_id'))
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
						->caption(Core::_('Field.shop_id'))
						->divAttr(array('class' => 'form-group col-xs-12 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-9 hidden-10 hidden-11 hidden-13'));

					$oMainRow5->add($oSelect_Shops);
				}

				$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
				$oAdmin_Form_Entity_Code->html(
					"<script>radiogroupOnChange('{$windowId}', " . intval($this->_object->type) . ", [{$sRadiogroupOnChangeList}])</script>"
				);

				$oMainTab->add($oAdmin_Form_Entity_Code);

				$oMainTab
					->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow6)
					->move($this->getField('tag_name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow7)
					->move($this->getField('default_value')->class('form-control')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 hidden-2 hidden-5 hidden-7 hidden-8 hidden-9 hidden-12 hidden-13 hidden-14')), $oMainRow7)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow7)
					->move($this->getField('multiple')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow8)
					->move($this->getField('obligatory')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow8)
					->move($this->getField('visible')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow8)
					;

				$oDefault_Value_Date = Admin_Form_Entity::factory('Date')
					->value($this->_object->default_value == '0000-00-00 00:00:00' ? '' : $this->_object->default_value)
					->name('default_value_date')
					->caption(Core::_('Field.default_value'))
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-3 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-9 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'));

				$oMainRow7->add($oDefault_Value_Date);

				$oDefault_Value_DateTime = Admin_Form_Entity::factory('DateTime')
					->value($this->_object->default_value == '0000-00-00 00:00:00' ? '' : $this->_object->default_value)
					->name('default_value_datetime')
					->caption(Core::_('Field.default_value'))
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-3 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-7 hidden-8 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'));

				$oMainRow7->add($oDefault_Value_DateTime);

				$oDefault_Value_Checkbox = Admin_Form_Entity::factory('Checkbox')
					->value(1)
					->checked($this->_object->default_value == 1)
					->caption(Core::_('Field.default_value'))
					->name('default_value_checked')
					->divAttr(array('class' => 'form-group col-sm-6 col-md-4 col-lg-4 hidden-0 hidden-1 hidden-2 hidden-3 hidden-4 hidden-5 hidden-6 hidden-8 hidden-9 hidden-10 hidden-11 hidden-12 hidden-13 hidden-14'));

				$oMainRow7->add($oDefault_Value_Checkbox);

				$oFormatTab
					->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow10 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow11 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow12 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow13 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow14 = Admin_Form_Entity::factory('Div')->class('row'));

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
					->move($this->getField('image_large_max_width'), $oMainRow9)
					->move($this->getField('image_large_max_height'), $oMainRow9)
					->move($this->getField('preserve_aspect_ratio'), $oMainRow10)
					->move($this->getField('image_small_max_width'), $oMainRow11)
					->move($this->getField('image_small_max_height'), $oMainRow11)
					->move($this->getField('preserve_aspect_ratio_small'), $oMainRow12)
					->move($this->getField('hide_small_image'), $oMainRow12)
					// ->move($this->getField('watermark_default_use_large_image')->divAttr(array('class' => 'form-group col-xs-12 col-md-6')), $oMainRow13)
					// ->move($this->getField('watermark_default_use_small_image')->divAttr(array('class' => 'form-group col-xs-12 col-md-6')), $oMainRow13)
					->move($this->getField('change_filename')->divAttr(array('class' => 'form-group col-xs-12 col-md-6')), $oMainRow13);
			break;
			case 'field_dir':
			default:
				$title = $this->_object->id
					? Core::_('Field_Dir.field_dir_edit_form_title', $this->_object->name)
					: Core::_('Field_Dir.field_dir_add_form_title');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainTab->move($this->getField('name')->class('form-control input-lg'), $oMainRow1);
				$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3);
				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4);

				$aExclude = array($this->_object->id);

				// Удаляем стандартный <input>
				$oAdditionalTab->delete(
					 $this->getField('parent_id')
				);

				$oSelect_Dirs
					->options(
						array(' … ') + $this::fillFieldDir($model, 0, $aExclude, 0)
					)
					->name('parent_id')
					->value($this->_object->parent_id)
					->caption(Core::_('Field_Dir.parent_name'));

				$oMainRow2->add($oSelect_Dirs);
			break;
		}

		$this->title($title);

		return $this;
	}

	/*public function execute($operation = NULL)
	{
		return parent::execute($operation);
	}*/

	/**
	 * Create visual tree of the directories
	 * @param int $iFieldDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillFieldDir($model, $iFieldDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iFieldDirParentId = intval($iFieldDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('field_dirs')
				->where('model', '=', $model)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				self::$_aDirTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset(self::$_aDirTree[$iFieldDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aDirTree[$iFieldDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']';
					$aReturn += self::fillFieldDir($model, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aDirTree = array();

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Field_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$bNewProperty = is_null($this->_object->id);

		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'field':
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

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}