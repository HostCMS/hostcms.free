<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Fields.
 *
 * @package HostCMS
 * @subpackage Field
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Field_Controller_Tab
{
	/**
	* POST data
	* @var array
	*/
	protected $_POST = array();

	/**
	* Form controller
	* @var Admin_Form_Controller
	*/
	protected $_Admin_Form_Controller = NULL;

	/**
	* Get _Admin_Form_Controller
	* @return Admin_Form_Controller
	*/
	public function getAdmin_Form_Controller()
	{
		return $this->_Admin_Form_Controller;
	}

	/**
	* Constructor.
	* @param Admin_Form_Controller $Admin_Form_Controller controller
	*/
	public function __construct(Admin_Form_Controller $Admin_Form_Controller)
	{
		$this->_Admin_Form_Controller = $Admin_Form_Controller;

		// We use each for advance the array cursor
		$this->_POST = $_POST;
	}

	/**
	* Create and return an object of Field_Controller_Tab for current skin
	* @return object
	*/
	static public function factory(Admin_Form_Controller $Admin_Form_Controller)
	{
		$className = 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__;

		if (!class_exists($className))
		{
			throw new Core_Exception("Class '%className' does not exist",
				array('%className' => $className));
		}

		return new $className($Admin_Form_Controller);
	}

	/**
	* Object
	* @var object
	*/
	protected $_object = NULL;

	/**
	* Set object
	* @param Core_Entity $object object
	* @return self
	*/
	public function setObject(Core_Entity $object)
	{
		$this->_object = $object;
		return $this;
	}

	/**
	* Get object
	* @return Core_Entity
	*/
	public function getObject()
	{
		return $this->_object;
	}

	/**
	* Dataset ID
	* @var int
	*/
	protected $_datasetId = NULL;

	/**
	* Set ID of dataset
	* @param int $datasetId ID of dataset
	* @return self
	*/
	public function setDatasetId($datasetId)
	{
		$this->_datasetId = $datasetId;
		return $this;
	}

	/**
	* Tab
	* @var Skin_Default_Admin_Form_Entity_Tab
	*/
	protected $_tab = NULL;

	/**
	* Set tab
	* @param Skin_Default_Admin_Form_Entity_Tab $tab tab
	* @return self
	*/
	public function setTab(Skin_Default_Admin_Form_Entity_Tab $tab)
	{
		$this->_tab = $tab;
		return $this;
	}

	protected $_field_values = array();

	/**
	* Show fields on tab
	* @return self
	*/
	public function fillTab()
	{
		$aTmp_Field_Values = $this->_object->id
			? $this->getFieldValues(FALSE)
			: array();

		$this->_field_values = array();
		foreach ($aTmp_Field_Values as $oField_Value)
		{
			$this->_field_values[$oField_Value->field_id][] = $oField_Value;
		}
		unset($aTmp_Field_Values);

		$this->_setFieldDirs(0, $this->_tab);

		return $this;
	}

	/**
	* Get field values
	* @param bool $bCache cache
	* @param array $aFieldsId field ids
	* @return array
	*/
	public function getFieldValues($bCache = TRUE, $aFieldsId = array())
	{
		if (!is_array($aFieldsId) || !count($aFieldsId))
		{
			$aFields = $this->_getFields()->findAll();

			$aFieldsId = array();
			foreach ($aFields as $oField)
			{
				$aFieldsId[] = $oField->id;
			}
		}

		$aReturn = Field_Controller_Value::getFieldsValues($aFieldsId, $this->_object->id, $bCache);

		// setHref()
		/*foreach ($aReturn as $oField_Value)
		{
			$this->_preparePropertyValue($oField_Value);
		}*/

		//$bCache && $this->_propertyValues = $aReturn;

		return $aReturn;
	}

	public function imgBox($oAdmin_Form_Entity, $oField, $addFunction = '$.cloneField', $deleteOnclick = '$.deleteNewField(this)')
	{
		$oAdmin_Form_Entity
			->add($this->getImgAdd($oField, $addFunction))
			->add($this->getImgDelete($deleteOnclick));

		return $this;
	}

	/**
	* Show plus button
	* @param Field_Model $oField field
	* @param string $function function name
	* @return string
	*/
	public function getImgAdd($oField, $addFunction = '$.cloneField')
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		ob_start();
		Core::factory('Core_Html_Entity_Img')
			->src('/admin/images/action_add.gif')
			->id('add')
			->class('pointer left5px img_line')
			->onclick("{$addFunction}('{$windowId}', '{$oField->id}')")
			->execute();
		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code')->html(ob_get_clean());

		return $oAdmin_Form_Entity_Code;
	}

	/**
	* Show minus button
	* @param string $onclick onclick attribute value
	* @return string
	*/
	public function getImgDelete($onclick = '$.deleteNewField(this)')
	{
		ob_start();
		Core::factory('Core_Html_Entity_Img')
			->src('/admin/images/action_delete.gif')
			->id('delete')
			->class('pointer left5px img_line')
			->onclick($onclick)
			->execute();

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code')
			->html(ob_get_clean());

		return $oAdmin_Form_Entity_Code;
	}

	/**
	* Get path to delete image
	* @return string
	*/
	public function getImgDeletePath($oField_Value)
	{
		$oField = $oField_Value->Field;

		return "res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { mainFormLocker.unlock(); $.deleteField(this, {path: '/admin/field/modelfield/index.php', action: 'deleteFieldValue', fieldId: '{$oField->id}', fieldValueId: '{$oField_Value->id}', fieldDirId: '{$oField->field_dir_id}', model: '{$oField->model}'}) } else {return false}";
	}

	public function getImgDeleteFilePath($oField_Value, $prefix)
	{
		$oField = $oField_Value->Field;

		return $this->_Admin_Form_Controller->getAdminActionLoadAjax('/admin/field/modelfield/index.php', 'deleteFieldValue', "{$prefix}_field_{$oField->id}_{$oField_Value->id}", 1, $oField->id, "fieldId={$oField->id}&fieldValueId={$oField_Value->id}&fieldDirId={$oField->field_dir_id}&model={$oField->model}");
	}

	/**
	* List Options Cache
	* @var array
	*/
	protected $_cacheListOptions = array();

	/**
	* Add external fields container to $parentObject
	* @param int $field_dir_id ID of parent directory of fields
	* @param object $parentObject
	* @hostcms-event Field_Controller_Tab.onBeforeAddFormEntity
	* @hostcms-event Field_Controller_Tab.onBeforeCreateFieldValue
	* @hostcms-event Field_Controller_Tab.onAfterCreateFieldValue
	* @hostcms-event Field_Controller_Tab.onAfterCreateFieldListValues
	* @hostcms-event Field_Controller_Tab.onSetFieldType
	* @hostcms-event Field_Controller_Tab.onBeforeAddSection
	*/
	protected function _setFieldDirs($field_dir_id, $parentObject)
	{
		$oAdmin_Form_Entity_Section = Admin_Form_Entity::factory('Section')
			->caption($field_dir_id == 0
				? Core::_('Field_Dir.main_section')
				: htmlspecialchars(Core_Entity::factory('Field_Dir', $field_dir_id)->name)
			)
			->id('accordion_' . $field_dir_id)
			->class('field_dir');

		// Fields
		$oFields = $this->_getFields();
		$oFields
			->queryBuilder()
			->where('field_dir_id', '=', $field_dir_id);

		$aFields = $oFields->findAll();

		foreach ($aFields as $oField)
		{
			$this->_addIntoSection($oAdmin_Form_Entity_Section, $oField);
		}

		// Field Dirs
		$oField_Dirs = Core_Entity::factory('Field_Dir');
		$oField_Dirs->queryBuilder()
			->where('model', '=', $this->_object->getModelName())
			->where('parent_id', '=', $field_dir_id)
			->clearOrderBy()
			->orderBy('sorting', 'ASC');

		$aField_Dirs = $oField_Dirs->findAll();
		foreach ($aField_Dirs as $oField_Dir)
		{
			$this->_setFieldDirs($oField_Dir->id, $field_dir_id == 0 ? $this->_tab : $oAdmin_Form_Entity_Section);
		}

		// Оставшиеся значения выводятся внизу
		if ($field_dir_id == 0 && count($this->_field_values))
		{
			foreach ($this->_field_values as $field_id => $aField_Values)
			{
				$this->_addIntoSection($oAdmin_Form_Entity_Section, Core_Entity::factory('Field', $field_id));
			}
		}

		Core_Event::notify('Field_Controller_Tab.onBeforeAddSection', $this, array($oAdmin_Form_Entity_Section, $field_dir_id));

		$oAdmin_Form_Entity_Section->getCountChildren() && $parentObject->add($oAdmin_Form_Entity_Section);
	}

	protected function _addIntoSection($oAdmin_Form_Entity_Section, $oField)
	{
		/*$aField_Values = $this->_object->id
			? $oField->getValues($this->_object->id, FALSE)
			: array();*/

		if (isset($this->_field_values[$oField->id]))
		{
			$aField_Values = $this->_field_values[$oField->id];
			unset($this->_field_values[$oField->id]);
		}
		else
		{
			$aField_Values = array();
		}

		$oAdmin_Form_Entity = NULL;

		$iFieldCounter = 0;

		switch ($oField->type)
		{
			case 0: // Int
			case 1: // String
			case 2: // File
			/*case 3: // List*/
			case 4: // Textarea
			case 6: // Wysiwyg
			case 7: // Checkbox
			case 8: // Date
			case 9: // Datetime
			case 10: // Hidden field
			case 11: // Float

				Core_Event::notify('Field_Controller_Tab.onBeforeCreateFieldValue', $this, array($oField, $oAdmin_Form_Entity));

				$aFormat = $oField->obligatory
					? array('minlen' => array('value' => 1))
					: array();

				switch ($oField->type)
				{
					case 0: // Int
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
							->format($aFormat + array('lib' => array(
								'value' => 'integer'
							)));
					break;
					case 11: // Float
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
							->format($aFormat + array('lib' => array(
								'value' => 'decimal'
							)));
					break;
					case 1: // String
					default:
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')->format($aFormat);
					break;
					case 10: // Hidden field
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input');
					break;
					case 2: // File
						$largeImage = array(
							'max_width' => $oField->image_large_max_width,
							'max_height' => $oField->image_large_max_height,
							'show_description' => TRUE,
						);

						$smallImage = array(
							'caption' => Core::_('Field.small_file_caption', $oField->name),
							'show' => !$oField->hide_small_image,
							'max_width' => $oField->image_small_max_width,
							'max_height' => $oField->image_small_max_height,
							'show_description' => TRUE
						);

						/*if (method_exists($this->_object, 'getWatermarkDefaultPositionX')
							&& method_exists($this->_object, 'getWatermarkDefaultPositionY'))
						{
							$largeImage['watermark_position_x'] = $this->_object->getWatermarkDefaultPositionX();
							$largeImage['watermark_position_y'] = $this->_object->getWatermarkDefaultPositionY();
						}*/

						// $largeImage['place_watermark_checkbox_checked'] = $oField->watermark_default_use_large_image;
						// $smallImage['place_watermark_checkbox_checked'] = $oField->watermark_default_use_small_image;

						$largeImage['preserve_aspect_ratio_checkbox_checked'] = $oField->preserve_aspect_ratio;
						$smallImage['preserve_aspect_ratio_checkbox_checked'] = $oField->preserve_aspect_ratio_small;

						$oAdmin_Form_Entity = Admin_Form_Entity::factory('File')
							->style('width: 340px')
							->largeImage($largeImage)
							->smallImage($smallImage)
							->crop(TRUE);
					break;

					/*case 3: // List
						// see below
					break;*/

					case 4: // Textarea
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Textarea')->format($aFormat);
					break;

					case 6: // Wysiwyg
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Textarea')
							->rows(8)
							->wysiwyg(Core::moduleIsActive('wysiwyg'))
							// ->template_id($this->template_id)
							;
					break;

					case 7: // Checkbox
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox');
						count($aField_Values) && $oAdmin_Form_Entity->postingUnchecked(TRUE);
					break;

					case 8: // Date
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Date')->format($aFormat);
					break;

					case 9: // Datetime
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Datetime')->format($aFormat);
					break;
				}

				Core_Event::notify('Field_Controller_Tab.onAfterCreateFieldValue', $this, array($oField, $oAdmin_Form_Entity));

				if ($oAdmin_Form_Entity)
				{
					$oAdmin_Form_Entity
						->name("field_{$oField->id}[]")
						->id("id_field_{$oField->id}_00{$iFieldCounter}")
						->caption(htmlspecialchars($oField->name) . ($oField->visible ? '' : ' <i class="fa fa-eye-slash fa-inactive"></i>'))
						->value(
							$this->_correctPrintValue($oField, $oField->default_value)
						)
						->divAttr(array(
							'class' => ($oField->type != 2 ? 'form-group' : 'input-group')
								. (
									($oField->type == 7 || $oField->type == 8 || $oField->type == 9)
									? ' col-xs-12 col-sm-7 col-md-6 col-lg-5'
									: ' col-xs-12'
								)
								. ($oField->type == 7 ? ' margin-top-21' : '')
						));

					$oField->type == 7
						&& $oAdmin_Form_Entity->checked($oField->default_value == 1);

					//$oField->multiple && $oAdmin_Form_Entity->add($this->getImgAdd($oField));

					// Значений св-в нет для объекта
					if (count($aField_Values) == 0)
					{
						Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity, $oAdmin_Form_Entity_Section, $oField));

						$oAdmin_Form_Entity_Section->add(
							Admin_Form_Entity::factory('Div')
								->class('row')
								->id("field_{$oField->id}")
								->add($oAdmin_Form_Entity)
						);

						$oField->multiple && $this->imgBox($oAdmin_Form_Entity, $oField);
					}
					else
					{
						foreach ($aField_Values as $oField_Value)
						{
							$oNewAdmin_Form_Entity = clone $oAdmin_Form_Entity;

							switch ($oField->type)
							{
								default:
									$oNewAdmin_Form_Entity->value($oField_Value->value);
								break;

								case 2: // File
									$sDirHref = '/' . Field_Controller::getPath($this->_object);

									if ($oField_Value->file != '')
									{
										$oNewAdmin_Form_Entity->largeImage(
											Core_Array::union($oNewAdmin_Form_Entity->largeImage, array(
												'path' => $sDirHref . rawurlencode($oField_Value->file),
												'originalName' => $oField_Value->file_name,
												// 'delete_onclick' => $this->_Admin_Form_Controller->getAdminActionLoadAjax('/admin/field/modelfield/index.php', 'deleteFieldValue', "large_field_{$oField->id}_{$oField_Value->id}", $this->_datasetId, $this->_object->id)
												'delete_onclick' => $this->getImgDeleteFilePath($oField_Value, 'large')
											))
										);
									}
									// Description doesn't depend on loaded file
									$oNewAdmin_Form_Entity->largeImage(
										Core_Array::union($oNewAdmin_Form_Entity->largeImage, array(
											'description' => $oField_Value->file_description
										)
									));

									if ($oField_Value->file_small != '')
									{
										$oNewAdmin_Form_Entity->smallImage(
											Core_Array::union($oNewAdmin_Form_Entity->smallImage, array(
												'path' => $sDirHref . rawurlencode($oField_Value->file_small),
												'originalName' => $oField_Value->file_small_name,
												// 'delete_onclick' => $this->_Admin_Form_Controller->getAdminActionLoadAjax('/admin/field/modelfield/index.php', 'deleteFieldValue', "small_field_{$oField->id}_{$oField_Value->id}", $this->_datasetId, $this->_object->id),
												'delete_onclick' => $this->getImgDeleteFilePath($oField_Value, 'small'),
												'create_small_image_from_large_checked' => FALSE,
											))
										);
									}

									// Description doesn't depend on loaded file
									$oNewAdmin_Form_Entity->smallImage(
										Core_Array::union($oNewAdmin_Form_Entity->smallImage, array(
											'description' => $oField_Value->file_small_description
										)
									));
								break;
								case 7: // Checkbox
									$oNewAdmin_Form_Entity->checked($oField_Value->value == 1);
								break;
								case 8: // Date
									$oNewAdmin_Form_Entity->value(
										$this->_correctPrintValue($oField, $oField_Value->value)
									);
								break;
								case 9: // Datetime
									$oNewAdmin_Form_Entity->value(
										$this->_correctPrintValue($oField, $oField_Value->value)
									);
								break;
							}

							$oNewAdmin_Form_Entity
								->name("field_{$oField->id}_{$oField_Value->id}")
								->id("id_field_{$oField->id}_{$oField_Value->id}");

							Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity, $oAdmin_Form_Entity_Section, $oField, $oField_Value));

							$oAdmin_Form_Entity_Section->add(
								Admin_Form_Entity::factory('Div')
									->class('row')
									->id("field_{$oField->id}")
									->add($oNewAdmin_Form_Entity)
							);

							// Визуальный редактор клонировать запрещено
							$oField->multiple /*&& $oField->type != 6*/
								&& $this->imgBox($oNewAdmin_Form_Entity, $oField, '$.cloneField', $this->getImgDeletePath($oField_Value));
						}
					}
				}
			break;

			case 3: // List
				if (Core::moduleIsActive('list'))
				{
					$oAdmin_Form_Entity_ListItems = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oField->name))
						->name("field_{$oField->id}[]")
						// ->value(NULL)
						->value(
							$this->_correctPrintValue($oField, $oField->default_value)
						)
						->divAttr(array('class' => 'form-group col-xs-12'));

					// Перенесно в _fillList()
					/*$oField->obligatory
						&& $oAdmin_Form_Entity_ListItems->data('required', 1);*/

					$oAdmin_Form_Entity_ListItemsInput = Admin_Form_Entity::factory('Input')
						->caption(htmlspecialchars($oField->name))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-8'))
						->id("id_field_{$oField->id}_00{$iFieldCounter}") // id_field_ !!!
						->name("input_field_{$oField->id}[]");

					$oAdmin_Form_Entity_Autocomplete_Select = Admin_Form_Entity::factory('Select')
						->id($oAdmin_Form_Entity_ListItemsInput->id . '_mode')
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
						->options(array(
							0 => Core::_('Admin_Form.autocomplete_mode0'),
							1 => Core::_('Admin_Form.autocomplete_mode1'),
							2 => Core::_('Admin_Form.autocomplete_mode2'),
							3 => Core::_('Admin_Form.autocomplete_mode3')
						))
						->caption(Core::_('Admin_Form.autocomplete_mode'));

					// Значений св-в нет для объекта
					if (count($aField_Values) == 0)
					{
						Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_ListItems,$oAdmin_Form_Entity_Section, $oField));

						$this->_fillList($oField->default_value, $oField, NULL, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_ListItems, $oAdmin_Form_Entity_ListItemsInput, $oAdmin_Form_Entity_Autocomplete_Select);
					}
					else
					{
						foreach ($aField_Values as $key => $oField_Value)
						{
							$value = $oField_Value->value;

							$oNewAdmin_Form_Entity_ListItems = clone $oAdmin_Form_Entity_ListItems;
							$oNewAdmin_Form_Entity_ListItems
								->id("id_field_{$oField->id}_{$oField_Value->id}_{$key}") // id_ should be, see js!
								->name("field_{$oField->id}_{$oField_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_ListItemsInput = clone $oAdmin_Form_Entity_ListItemsInput;
							$oNewAdmin_Form_Entity_ListItemsInput
								->id("id_field_{$oField->id}_{$oField_Value->id}_{$key}") // id_field_ !!!
								->name("input_field_{$oField->id}_{$oField_Value->id}");

							$oNewAdmin_Form_Entity_Autocomplete_Select = clone $oAdmin_Form_Entity_Autocomplete_Select;
							$oNewAdmin_Form_Entity_Autocomplete_Select
								->id($oNewAdmin_Form_Entity_ListItemsInput->id . '_mode'); // id_field_ !!!

							Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_ListItems, $oAdmin_Form_Entity_Section, $oField, $oField_Value));

							$this->_fillList($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_ListItems, $oNewAdmin_Form_Entity_ListItemsInput, $oNewAdmin_Form_Entity_Autocomplete_Select);
						}
					}
				}
			break;

			case 5: // ИС
				if (Core::moduleIsActive('informationsystem'))
				{
					// Группы
					$oAdmin_Form_Entity_InfGroups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oField->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("id_group_{$oField->id}_00{$iFieldCounter}") // id_ should be, see js!
						->name("group_field_{$oField->id}[]")
						->filter(TRUE);

					// Элементы
					$oAdmin_Form_Entity_InfItems = Admin_Form_Entity::factory('Select')
						->id("id_field_{$oField->id}")
						->name("field_{$oField->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_InfItemsInput = Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_field_{$oField->id}_00{$iFieldCounter}")
						->name("input_field_{$oField->id}[]");

					// Значений св-в нет для объекта
					if (count($aField_Values) == 0)
					{
						Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_Section, $oField));

						$this->_fillInformationSystem($oField->default_value, $oField, NULL, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfItems, $oAdmin_Form_Entity_InfItemsInput);
					}
					else
					{
						foreach ($aField_Values as $key => $oField_Value)
						{
							$value = $oField_Value->value;

							$oNewAdmin_Form_Entity_Inf_Groups = clone $oAdmin_Form_Entity_InfGroups;
							$oNewAdmin_Form_Entity_Inf_Groups
								->id("id_group_{$oField->id}_{$oField_Value->id}"); // id_ should be, see js!

							$oNewAdmin_Form_Entity_InfItems = clone $oAdmin_Form_Entity_InfItems;
							$oNewAdmin_Form_Entity_InfItems
								->id("id_field_{$oField->id}_{$oField_Value->id}_{$key}") // id_ should be, see js!
								->name("field_{$oField->id}_{$oField_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_InfItemsInput = clone $oAdmin_Form_Entity_InfItemsInput;
							$oNewAdmin_Form_Entity_InfItemsInput
								->id("input_field_{$oField->id}_{$oField_Value->id}_{$key}")
								->name("input_field_{$oField->id}_{$oField_Value->id}");

							Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_Inf_Groups, $oAdmin_Form_Entity_Section, $oField, $oField_Value));

							$this->_fillInformationSystem($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Inf_Groups, $oNewAdmin_Form_Entity_InfItems, $oNewAdmin_Form_Entity_InfItemsInput);
						}
					}
				}
			break;

			case 13: // ИС, группа
				if (Core::moduleIsActive('informationsystem'))
				{
					$oAdmin_Form_Entity_InfGroups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oField->name))
						->id("id_field_{$oField->id}")
						->name("field_{$oField->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_InfGroupsInput = Admin_Form_Entity::factory('Input')
						->caption(htmlspecialchars($oField->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_field_{$oField->id}_00{$iFieldCounter}")
						->name("input_field_{$oField->id}[]");

					// Значений св-в нет для объекта
					if (count($aField_Values) == 0)
					{
						Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_Section, $oField));

						$this->_fillInformationSystemGroup($oField->default_value, $oField, NULL, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfGroupsInput);
					}
					else
					{
						foreach ($aField_Values as $key => $oField_Value)
						{
							$value = $oField_Value->value;

							$oNewAdmin_Form_Entity_Inf_Groups = clone $oAdmin_Form_Entity_InfGroups;
							$oNewAdmin_Form_Entity_Inf_Groups
								->id("id_field_{$oField->id}_{$oField_Value->id}_{$key}") // id_ should be, see js!
								->name("field_{$oField->id}_{$oField_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_InfGroupsInput = clone $oAdmin_Form_Entity_InfGroupsInput;
							$oNewAdmin_Form_Entity_InfGroupsInput
								->id("input_field_{$oField->id}_{$oField_Value->id}_{$key}")
								->name("input_field_{$oField->id}_{$oField_Value->id}");

							Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_Inf_Groups, $oAdmin_Form_Entity_Section, $oField, $oField_Value));

							$this->_fillInformationSystemGroup($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Inf_Groups, $oNewAdmin_Form_Entity_InfGroupsInput);
						}
					}
				}
			break;

			case 12: // Интернет-магазин
				if (Core::moduleIsActive('shop'))
				{
					// Группы
					$oAdmin_Form_Entity_Shop_Groups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oField->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("id_group_{$oField->id}_00{$iFieldCounter}") // id_ should be, see js!
						->name("group_field_{$oField->id}[]")
						->filter(TRUE);

					// Элементы
					$oAdmin_Form_Entity_Shop_Items = Admin_Form_Entity::factory('Select')
						->id("id_field_{$oField->id}")
						->name("field_{$oField->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_Shop_Items_Input = Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_field_{$oField->id}_00{$iFieldCounter}") // id_ should be, see js!
						->name("input_field_{$oField->id}[]");

					// Значений св-в нет для объекта
					if (count($aField_Values) == 0)
					{
						Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_Shop_Items, $oAdmin_Form_Entity_Section, $oField));

						$this->_fillShop($oField->default_value, $oField, NULL, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Items, $oAdmin_Form_Entity_Shop_Items_Input);
					}
					else
					{
						foreach ($aField_Values as $key => $oField_Value)
						{
							$value = $oField_Value->value;

							$oNewAdmin_Form_Entity_Shop_Groups = clone $oAdmin_Form_Entity_Shop_Groups;
							$oNewAdmin_Form_Entity_Shop_Groups
								->id("id_group_{$oField->id}_{$oField_Value->id}"); // id_ should be, see js!

							$oNewAdmin_Form_Entity_Shop_Items = clone $oAdmin_Form_Entity_Shop_Items;
							$oNewAdmin_Form_Entity_Shop_Items
								->id("id_field_{$oField->id}_{$oField_Value->id}_{$key}") // id_ should be, see js!
								->name("field_{$oField->id}_{$oField_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_Shop_Items_Input = clone $oAdmin_Form_Entity_Shop_Items_Input;
							$oNewAdmin_Form_Entity_Shop_Items_Input
								->id("input_field_{$oField->id}_{$oField_Value->id}_{$key}")
								->name("input_field_{$oField->id}_{$oField_Value->id}");

							Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Section, $oField, $oField_Value));

							$this->_fillShop($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Shop_Groups, $oNewAdmin_Form_Entity_Shop_Items, $oNewAdmin_Form_Entity_Shop_Items_Input);
						}
					}
				}
			break;

			case 14: // Интернет-магазин, группа
				if (Core::moduleIsActive('shop'))
				{
					// Группы
					$oAdmin_Form_Entity_Shop_Groups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oField->name))
						->id("id_field_{$oField->id}")
						->name("field_{$oField->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_Shop_Groups_Input = Admin_Form_Entity::factory('Input')
						->caption(htmlspecialchars($oField->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_field_{$oField->id}_00{$iFieldCounter}") // id_ should be, see js!
						->name("input_field_{$oField->id}[]");

					// Значений св-в нет для объекта
					if (count($aField_Values) == 0)
					{
						Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Section, $oField));

						$this->_fillShopGroup($oField->default_value, $oField, NULL, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Groups_Input);
					}
					else
					{
						foreach ($aField_Values as $key => $oField_Value)
						{
							$value = $oField_Value->value;

							$oNewAdmin_Form_Entity_Shop_Groups = clone $oAdmin_Form_Entity_Shop_Groups;
							$oNewAdmin_Form_Entity_Shop_Groups
								->id("id_group_{$oField->id}_{$oField_Value->id}") // id_ should be, see js!
								->name("field_{$oField->id}_{$oField_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_Shop_Groups_Input = clone $oAdmin_Form_Entity_Shop_Groups_Input;
							$oNewAdmin_Form_Entity_Shop_Groups_Input
								->id("input_field_{$oField->id}_{$oField_Value->id}_{$key}")
								->name("input_field_{$oField->id}_{$oField_Value->id}");

							Core_Event::notify('Field_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Section, $oField, $oField_Value));

							$this->_fillShopGroup($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Shop_Groups, $oNewAdmin_Form_Entity_Shop_Groups_Input);
						}
					}
				}
			break;

			default:
				/*throw new Core_Exception(
					Core::_('Field.type_does_not_exist'),
						array('%d' => $oField->type)
				);*/
				Core_Event::notify('Field_Controller_Tab.onSetFieldType', $this, array($oAdmin_Form_Entity_Section, $oField, $aField_Values));
		}

		return $this;
	}

	protected function _fillList($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_ListItemsSelect, $oAdmin_Form_Entity_ListItemsInput, $oAdmin_Form_Entity_Autocomplete_Select)
	{
		$oList_Item = Core_Entity::factory('List_Item', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oField->default_value;

		// $windowId = $this->_Admin_Form_Controller->getWindowId();

		$oList = $oField->List;

		$iCountItems = $oList->List_Items->getCount();

		$bAutocomplete = $iCountItems > Core::$mainConfig['switchSelectToAutocomplete'];

		if (!$bAutocomplete)
		{
			if (!isset($this->_cacheListOptions[$oField->list_id]))
			{
				$this->_cacheListOptions[$oField->list_id] = array(' … ');
				$this->_cacheListOptions[$oField->list_id] += $oField->List->getListItemsTree();
			}

			$oField->obligatory
				&& $oAdmin_Form_Entity_ListItemsSelect->data('required', 1);

			$oAdmin_Form_Entity_ListItemsSelect
				->options($this->_cacheListOptions[$oField->list_id]);

			$oAdmin_Form_Entity_ListItemsInput
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-8 hidden'));

			$oAdmin_Form_Entity_Autocomplete_Select
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 hidden'));
		}
		else
		{
			$oAdmin_Form_Entity_ListItemsSelect
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->options(array($value => $oList_Item->id));

			$oAdmin_Form_Entity_ListItemsInput->value($oList_Item->value);

			$oField->obligatory
				&& $oAdmin_Form_Entity_ListItemsInput->format(array('minlen' => array('value' => 1)));
		}

		$input_group = $oField->multiple ? 'input-group' : '';

		$oDiv_Group = Admin_Form_Entity::factory('Div')
			->class($input_group)
			->add($oAdmin_Form_Entity_ListItemsSelect)
			->add($oAdmin_Form_Entity_ListItemsInput)
			->add($oAdmin_Form_Entity_Autocomplete_Select);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= id_field_{$oField->id}]').autocomplete({
					source: function(request, response) {
						var jInput = $(this.element),
							jTopParentDiv = jInput.parents('div[id ^= field]');

						$.ajax({
							url: '/admin/list/item/index.php?autocomplete=1&show_parents=1&list_id={$oList->id}&mode=' + $('#{$windowId} #' + jInput.attr('id') + '_mode').val(),
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
						var jInput = $(this),
							jListItemDiv = jInput.parents('[id ^= field]').find('select[name ^= field_]');

							jListItemDiv.empty().append($('<option>', {value: ui.item.id, text: ui.item.label}).attr('selected', 'selected'));
					},
					open: function() {
						$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
					},
					change: function(event, ui) {
						// Set to empty value
						if (ui.item === null)
						{
							var jInput = $(this),
								jListItemDiv = jInput.parents('[id ^= field]').find('select[name ^= field_]');

							jListItemDiv.empty().append($('<option>', { value: '', text: ''}).attr('selected', 'selected'));
						}
					},
					close: function() {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					}
				});
			")
		);

		$oField->multiple && $this->imgBox(
			$oDiv_Group,
			$oField,
			'$.cloneField',
			$oField_Value//!$bIsNullValue
				? $this->getImgDeletePath($oField_Value)
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->id("field_{$oField->id}")
					->class('row')
					->add($oDiv_Group)
			);
	}

	protected function _fillInformationSystemGroup($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfGroupsInput)
	{
		$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oField->default_value;

		$oInformationsystem = $oField->Informationsystem;

		$aOptions = Informationsystem_Item_Controller_Edit::fillInformationsystemGroup($oField->informationsystem_id, 0);
		$oAdmin_Form_Entity_InfGroups
			->value($oInformationsystem_Group->id)
			->options(array(' … ') + $aOptions);

		$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;

		$iCountGroups = $oInformationsystem_Groups->getCount();

		$bAutocomplete = $iCountGroups > Core::$mainConfig['switchSelectToAutocomplete'];

		if (!$bAutocomplete)
		{
			$oAdmin_Form_Entity_InfGroupsInput
				->divAttr(array('class' => 'form-group col-xs-12 hidden'));
		}
		else
		{
			$oAdmin_Form_Entity_InfGroups
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->options(array($value => $oInformationsystem_Group->name));

			$oAdmin_Form_Entity_InfGroupsInput->value(
				!is_null($oInformationsystem_Group->id)
					? $oInformationsystem_Group->name . ' [' . $oInformationsystem_Group->id . ']'
					: ''
			);
		}

		$oDiv_Group = Admin_Form_Entity::factory('Div')
			->class('input-group')
			->add($oAdmin_Form_Entity_InfGroups)
			->add($oAdmin_Form_Entity_InfGroupsInput);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= input_field_{$oField->id}]').autocomplete({
					source: function(request, response) {
						var jInput = $(this.element),
							jTopParentDiv = jInput.parents('div[id ^= field]');

						$.ajax({
							url: '/admin/informationsystem/item/index.php?autocomplete=1&show_group=1&informationsystem_id={$oInformationsystem->id}',
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
						var jInput = $(this),
							jTopParentDiv = jInput.parents('div[id ^= field]'),
							jInfItemDiv = jTopParentDiv.find('select[name ^= field_]');

							jInfItemDiv.empty().append($('<option>', { value: ui.item.id, text: ui.item.label }).attr('selected', 'selected'));
					},
					open: function() {
						$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
					},
					close: function() {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					}
				});
			")
		);

		$oField->multiple && $this->imgBox(
			$oDiv_Group,
			$oField,
			'$.cloneFieldInfSys',
			$oField_Value // !$bIsNullValue
				? $this->getImgDeletePath($oField_Value)
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->id("field_{$oField->id}")
					->class('row')
					->add($oDiv_Group)
			);
	}

	/**
	* Fill information systems/items list
	* @param int $value informationsystem_item_id
	* @param Field_Model $oField field
	* @param Admin_Form_Entity_Select $oAdmin_Form_Entity_InfGroups
	* @param Admin_Form_Entity_Select $oAdmin_Form_Entity_InfItemsSelect
	*/
	protected function _fillInformationSystem($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfItemsSelect, $oAdmin_Form_Entity_InfItemsInput)
	{
		$Informationsystem_Item = Core_Entity::factory('Informationsystem_Item', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oField->default_value;

		$group_id = $value == 0
			? 0
			: intval($Informationsystem_Item->informationsystem_group_id);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oInformationsystem = $oField->Informationsystem;

		// Groups
		$aOptions = Informationsystem_Item_Controller_Edit::fillInformationsystemGroup($oField->informationsystem_id, 0);
		$oAdmin_Form_Entity_InfGroups
			->value($Informationsystem_Item->informationsystem_group_id)
			->options(array(' … ') + $aOptions)
			->onchange("$.ajaxRequest({path: '/admin/informationsystem/item/index.php', context: '{$oAdmin_Form_Entity_InfItemsSelect->id}', callBack: $.loadSelectOptionsCallback, action: 'loadInformationItemList',additionalParams: 'informationsystem_group_id=' + this.value + '&informationsystem_id={$oField->informationsystem_id}',windowId: '{$windowId}'}); return false");

		// Items
		$oInformationsystem_Items = $oInformationsystem->Informationsystem_Items;

		$oInformationsystem_Items
			->queryBuilder()
			->clearOrderBy()
			->where('informationsystem_items.informationsystem_group_id', '=', $group_id);

		$iCountItems = $oInformationsystem_Items->getCount();

		$bAutocomplete = $iCountItems > Core::$mainConfig['switchSelectToAutocomplete'];

		if (!$bAutocomplete)
		{
			// Remove `count` from select list
			$oInformationsystem_Items->queryBuilder()
				->clearSelect()
				->select('informationsystem_items.*');

			switch ($oInformationsystem->items_sorting_direction)
			{
				case 1:
					$items_sorting_direction = 'DESC';
				break;
				case 0:
				default:
					$items_sorting_direction = 'ASC';
			}

			// Определяем поле сортировки информационных элементов
			switch ($oInformationsystem->items_sorting_field)
			{
				case 1:
					$oInformationsystem_Items
						->queryBuilder()
						->orderBy('informationsystem_items.name', $items_sorting_direction)
						->orderBy('informationsystem_items.sorting', $items_sorting_direction);
				break;
				case 2:
					$oInformationsystem_Items
						->queryBuilder()
						->orderBy('informationsystem_items.sorting', $items_sorting_direction)
						->orderBy('informationsystem_items.name', $items_sorting_direction);
				break;
				case 0:
				default:
					$oInformationsystem_Items
						->queryBuilder()
						->orderBy('informationsystem_items.datetime', $items_sorting_direction)
						->orderBy('informationsystem_items.sorting', $items_sorting_direction);
			}

			$aInformationsystem_Items = $oInformationsystem_Items->findAll(FALSE);

			$aOptions = array(' … ');
			foreach ($aInformationsystem_Items as $oInformationsystem_Item)
			{
				$sName = Informationsystem_Controller_Load_Select_Options::getOptionName(
					!$oInformationsystem_Item->shortcut_id
						? $oInformationsystem_Item
						: $oInformationsystem_Item->Informationsystem_Item
				);

				$aOptions[$oInformationsystem_Item->id] = $oInformationsystem_Item->active
					? $sName
					: array(
						'value' => $sName,
						'attr' => array('class' => 'darkgray line-through')
					);
			}

			$oAdmin_Form_Entity_InfItemsSelect->options($aOptions);

			$oAdmin_Form_Entity_InfItemsInput
				->divAttr(array('class' => 'form-group col-xs-12 hidden'));
		}
		else
		{
			$oAdmin_Form_Entity_InfItemsSelect
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->options(array($value => $Informationsystem_Item->name));

			$oAdmin_Form_Entity_InfItemsInput->value($Informationsystem_Item->name);
		}

		$oDiv_Group = Admin_Form_Entity::factory('Div')
			->class('input-group')
			->add($oAdmin_Form_Entity_InfGroups)
			->add($oAdmin_Form_Entity_InfItemsSelect)
			->add($oAdmin_Form_Entity_InfItemsInput);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= input_field_{$oField->id}]').autocomplete({
					source: function(request, response) {
						var jInput = $(this.element),
							jTopParentDiv = jInput.parents('div[id ^= field]'),
							jInfGroupDiv = jTopParentDiv.find('[id ^= id_group_]'),
							selectedVal = $(':selected', jInfGroupDiv).val();

						$.ajax({
							url: '/admin/informationsystem/item/index.php?autocomplete=1&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id=' + selectedVal + '',
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
						var jInput = $(this),
							jTopParentDiv = jInput.parents('div[id ^= field]'),
							jInfItemDiv = jTopParentDiv.find('select[name ^= field_]');

							jInfItemDiv.empty().append($('<option>', { value: ui.item.id, text: ui.item.label }).attr('selected', 'selected'));
					},
					open: function() {
						$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
					},
					close: function() {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					}
				});
			")
		);

		$oField->multiple && $this->imgBox(
			$oDiv_Group,
			$oField,
			'$.cloneFieldInfSys',
			$oField_Value //!$bIsNullValue
				? $this->getImgDeletePath($oField_Value)
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->id("field_{$oField->id}")
					->class('row')
					->add($oDiv_Group)
			);
	}

	static public function getShopItems(Shop_Item_Model $oShop_Item)
	{
		$oShop = $oShop_Item->Shop;

		$offset = 0;
		$limit = 1000;

		switch ($oShop->items_sorting_direction)
		{
			case 1:
				$items_sorting_direction = 'DESC';
			break;
			case 0:
			default:
				$items_sorting_direction = 'ASC';
		}

		$oShop_Item
			->queryBuilder()
			//->where('shop_items.modification_id', '=', 0)
			->clearOrderBy()
			->clearSelect()
			->select('id', 'shortcut_id', 'modification_id', 'name', 'marking', 'active');

		// Определяем поле сортировки информационных элементов
		switch ($oShop->items_sorting_field)
		{
			case 1:
				$oShop_Item
					->queryBuilder()
					->orderBy('shop_items.name', $items_sorting_direction)
					->orderBy('shop_items.sorting', $items_sorting_direction);
				break;
			case 2:
				$oShop_Item
					->queryBuilder()
					->orderBy('shop_items.sorting', $items_sorting_direction)
					->orderBy('shop_items.name', $items_sorting_direction);
				break;
			case 0:
			default:
				$oShop_Item
					->queryBuilder()
					->orderBy('shop_items.datetime', $items_sorting_direction)
					->orderBy('shop_items.sorting', $items_sorting_direction);
		}

		$objects = array();

		do {
			$oShop_Item
				->queryBuilder()
				->offset($offset)
				->limit($limit);

			$aTmpObjects = $oShop_Item->findAll(FALSE);

			count($aTmpObjects)
				&& $objects = array_merge($objects, $aTmpObjects);

			$offset += $limit;
		}
		while (count($aTmpObjects));

		return $objects;
	}

	protected function _fillShopGroup($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Groups_Input)
	{
		$oShop_Group = Core_Entity::factory('Shop_Group', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oField->default_value;

		$oShop = $oField->Shop;

		$aOptions = Shop_Item_Controller_Edit::fillShopGroup($oField->shop_id, 0);
		$oAdmin_Form_Entity_Shop_Groups
			->value($oShop_Group->id)
			->options(array(' … ') + $aOptions);

		$oShop_Groups = $oShop->Shop_Groups;

		$iCountGroups = $oShop_Groups->getCount();

		$bAutocomplete = $iCountGroups > Core::$mainConfig['switchSelectToAutocomplete'];

		if (!$bAutocomplete)
		{
			$oAdmin_Form_Entity_Shop_Groups_Input
				->divAttr(array('class' => 'form-group col-xs-12 hidden'));
		}
		else
		{
			$oAdmin_Form_Entity_Shop_Groups
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->options(array($value => $oShop_Group->name));

			$oAdmin_Form_Entity_Shop_Groups_Input->value(
				!is_null($oShop_Group->id)
					? $oShop_Group->name . ' [' . $oShop_Group->id . ']'
					: ''
			);
		}

		$oDiv_Group = Admin_Form_Entity::factory('Div')
			->class('input-group')
			->add($oAdmin_Form_Entity_Shop_Groups)
			->add($oAdmin_Form_Entity_Shop_Groups_Input);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= input_field_{$oField->id}]').autocomplete({
				source: function(request, response) {
					var jInput = $(this.element),
						jTopParentDiv = jInput.parents('div[id ^= field]'),
						jInfGroupDiv = jTopParentDiv.find('[id ^= id_group_]'),
						selectedVal = $(':selected', jInfGroupDiv).val();

					$.ajax({
					url: '/admin/shop/item/index.php?autocomplete=1&show_group=1&shop_id={$oShop->id}',
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
					var jInput = $(this),
						jTopParentDiv = jInput.parents('div[id ^= field]'),
						jInfItemDiv = jTopParentDiv.find('select[name ^= field_]');

						jInfItemDiv.empty().append($('<option>', { value: ui.item.id, text: ui.item.label }).attr('selected', 'selected'));
				},
				open: function() {
					$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
				},
				close: function() {
					$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
				}
			});")
		);

		$oField->multiple && $this->imgBox(
			$oDiv_Group,
			$oField,
			'$.cloneFieldInfSys',
			$oField_Value //!$bIsNullValue
				? $this->getImgDeletePath($oField_Value)
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->id("field_{$oField->id}")
					->add($oDiv_Group)
			);
	}

	/**
	* Fill shops/items list
	* @param int $value shop_item_id
	* @param Field_Model $oField field
	* @param Admin_Form_Entity_Select $oAdmin_Form_Entity_Shop_Groups
	* @param Admin_Form_Entity_Select $oAdmin_Form_Entity_Shop_Items
	*/
	protected function _fillShop($value, $oField, $oField_Value, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Items, $oAdmin_Form_Entity_Shop_Items_Input)
	{
		$Shop_Item = Core_Entity::factory('Shop_Item', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oField->default_value;

		$group_id = $value == 0
			? 0
			: ($Shop_Item->modification_id
				? intval($Shop_Item->Modification->shop_group_id)
				: intval($Shop_Item->shop_group_id)
			);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oShop = $oField->Shop;

		// Groups
		$aOptions = Shop_Item_Controller_Edit::fillShopGroup($oField->shop_id, 0);
		$oAdmin_Form_Entity_Shop_Groups
			->value($group_id)
			->options(array(' … ') + $aOptions)
			->onchange("$.ajaxRequest({path: '/admin/shop/item/index.php', context: '{$oAdmin_Form_Entity_Shop_Items->id}', callBack: $.loadSelectOptionsCallback, action: 'loadShopItemList', additionalParams: 'shop_group_id=' + this.value + '&shop_id={$oField->shop_id}',windowId: '{$windowId}'}); return false");

		// Items
		$oShop_Items = $oShop->Shop_Items;

		$oShop_Items
			->queryBuilder()
			->clearOrderBy()
			->where('shop_items.shop_group_id', '=', $group_id)
			->where('shop_items.modification_id', '=', 0);

		$iCountItems = $oShop_Items->getCount();

		$bAutocomplete = $iCountItems > Core::$mainConfig['switchSelectToAutocomplete'];

		if (!$bAutocomplete)
		{
			$aShop_Items = self::getShopItems($oShop_Items);

			$aConfig = Core_Config::instance()->get('field_config', array()) + array(
				'select_modifications' => TRUE,
			);

			$aOptions = array(' … ');
			foreach ($aShop_Items as $oShop_Item)
			{
				$sName = Shop_Controller_Load_Select_Options::getOptionName(
					!$oShop_Item->shortcut_id
						? $oShop_Item
						: $oShop_Item->Shop_Item
				);

				$aOptions[$oShop_Item->id] = $oShop_Item->active
					? $sName
					: array(
						'value' => $sName,
						'attr' => array('class' => 'darkgray line-through')
					);

				// Shop Item's modifications
				if ($aConfig['select_modifications'])
				{
					$oModifications = $oShop_Item->Modifications;

					$oModifications
						->queryBuilder()
						->clearOrderBy()
						->clearSelect()
						->select('id', 'shortcut_id', 'modification_id', 'name', 'marking', 'active');

					$aModifications = $oModifications->findAll(FALSE);

					foreach ($aModifications as $oModification)
					{
						$sName = Shop_Controller_Load_Select_Options::getOptionName($oModification);

						$aOptions[$oModification->id] = $oModification->active
							? $sName
							: array(
								'value' => $sName,
								'attr' => array('class' => 'darkgray line-through')
							);
					}
				}
			}

			$oAdmin_Form_Entity_Shop_Items->options($aOptions);

			$oAdmin_Form_Entity_Shop_Items_Input
				->divAttr(array('class' => 'form-group col-xs-12 hidden'));
		}
		else
		{
			$oAdmin_Form_Entity_Shop_Items
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->options(array($value => $Shop_Item->name));

			$oAdmin_Form_Entity_Shop_Items_Input->value($Shop_Item->name);
		}

		$oDiv_Group = Admin_Form_Entity::factory('Div')
			->class('input-group')
			->add($oAdmin_Form_Entity_Shop_Groups)
			->add($oAdmin_Form_Entity_Shop_Items)
			->add($oAdmin_Form_Entity_Shop_Items_Input);

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= input_field_{$oField->id}]').autocomplete({
				source: function(request, response) {
					var jInput = $(this.element),
						jTopParentDiv = jInput.parents('div[id ^= field]'),
						jInfGroupDiv = jTopParentDiv.find('[id ^= id_group_]'),
						selectedVal = $(':selected', jInfGroupDiv).val();

					$.ajax({
					url: '/admin/shop/item/index.php?autocomplete=1&shop_id={$oShop->id}&shop_group_id=' + selectedVal + '',
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
					var jInput = $(this),
						jTopParentDiv = jInput.parents('div[id ^= field]'),
						jInfItemDiv = jTopParentDiv.find('select[name ^= field_]');

						jInfItemDiv.empty().append($('<option>', { value: ui.item.id, text: ui.item.label }).attr('selected', 'selected'));
				},
				open: function() {
					$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
				},
				close: function() {
					$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
				}
			});")
		);

		$oField->multiple && $this->imgBox(
			$oDiv_Group,
			$oField,
			'$.cloneFieldInfSys',
			$oField_Value //!$bIsNullValue
				? $this->getImgDeletePath($oField_Value)
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->id("field_{$oField->id}")
					->add($oDiv_Group)
			);
	}

	/**
	* Get field list
	* @return object
	*/
	protected function _getFields()
	{
		$oFields = Core_Entity::factory('Field');
		$oFields->queryBuilder()
			->where('fields.model', '=', $this->_object->getModelName())
			->open()
				->where('fields.site_id', '=', CURRENT_SITE)
				->setOr()
				->where('fields.site_id', '=', 0)
			->close()
			->clearOrderBy()
			->orderBy('sorting', 'ASC');

		return $oFields;
	}

	protected function _setValue($oField_Value, $value)
	{
		$value = $this->_correctValue($oField_Value->Field, $value);

		$oField_Value
			->setValue($value)
			->save();

		return $this;
	}

	/**
	* Apply object field
	* @hostcms-event Field_Controller_Tab.onBeforeApplyObjectProperty
	* @hostcms-event Field_Controller_Tab.onAfterApplyObjectProperty
	* @hostcms-event Field_Controller_Tab.onApplyObjectProperty
	*/
	public function applyObjectProperty()
	{
		$aFields = $this->_getFields()->findAll();

		Core_Event::notify('Field_Controller_Tab.onBeforeApplyObjectProperty', $this, array($this->_Admin_Form_Controller, $aFields));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Values already exist
		$aField_Values = $this->getFieldValues(FALSE);

		foreach ($aField_Values as $oField_Value)
		{
			$oField = $oField_Value->Field;

			switch ($oField->type)
			{
				case 0: // Int
				case 1: // String
				case 3: // List
				case 4: // Textarea
				case 5: // ИС
				case 6: // Wysiwyg
				case 7: // Checkbox
				case 8: // Date
				case 9: // Datetime
				case 10: // Hidden field
				case 11: // Float
				case 12: // Shop
				case 13: // IS group
				case 14: // Shop group
					$value = Core_Array::getPost("field_{$oField->id}_{$oField_Value->id}");

					// 000227947
					if (!is_null($value))
					{
						$value === ''
							? $oField_Value->delete()
							: $this->_setValue($oField_Value, $value);
					}
				break;
				case 2: // File
					// Values already exist

					$aLargeFile = Core_Array::getFiles("field_{$oField->id}_{$oField_Value->id}");
					$aSmallFile = Core_Array::getFiles("small_field_{$oField->id}_{$oField_Value->id}");

					// ----
					$description = Core_Array::getPost("description_field_{$oField->id}_{$oField_Value->id}");
					if (!is_null($description))
					{
						$oField_Value->file_description = $description;
						$oField_Value->save();
					}

					$description_small = Core_Array::getPost("description_small_field_{$oField->id}_{$oField_Value->id}");

					if (!is_null($description_small))
					{
						$oField_Value->file_small_description = $description_small;
						$oField_Value->save();
					}
					// ----

					$this->_loadFiles($aLargeFile, $aSmallFile, $oField_Value, $oField, "field_{$oField->id}_{$oField_Value->id}");
				break;
			}
		}

		// New Values
		foreach ($aFields as $oField)
		{
			switch ($oField->type)
			{
				case 0: // Int
				case 1: // String
				case 3: // List
				case 4: // Textarea
				case 5: // ИС
				case 6: // Wysiwyg
				case 7: // Checkbox
				case 8: // Date
				case 9: // Datetime
				case 10: // Hidden field
				case 11: // Float
				case 12: // Shop
				case 13: // IS group
				case 14: // Shop group

					// New values of field
					$aNewValue = Core_Array::getPost("field_{$oField->id}", array());

					// Checkbox, значений раньше не было и не пришло новых значений
					if ($oField->type == 7 && count($aField_Values) == 0
						&& is_array($aNewValue) && !count($aNewValue))
					{
						$aNewValue = array(0);
					}

					if (is_array($aNewValue))
					{
						foreach ($aNewValue as $newValue)
						{
							if ($newValue !== '')
							{
								$oNewField_Value = $oField->createNewValue($this->_object->id);

								$this->_setValue($oNewField_Value, $newValue);

								ob_start();
								Core::factory('Core_Html_Entity_Script')
									->value("$(\"#{$windowId} *[name='field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'field_{$oField->id}_{$oNewField_Value->id}')")
									->execute();

								$this->_Admin_Form_Controller->addMessage(ob_get_clean());
							}
						}
					}
				break;

				case 2: // File
					// New values of field
					$aNewValueLarge = Core_Array::getFiles("field_{$oField->id}", array());
					$aNewValueSmall = Core_Array::getFiles("small_field_{$oField->id}", array());

					// New values of field
					if (is_array($aNewValueLarge) && isset($aNewValueLarge['name']))
					{
						$iCount = count($aNewValueLarge['name']);

						for ($i = 0; $i < $iCount; $i++)
						{
							$oFileValue = $oField->createNewValue($this->_object->id);

							ob_start();

							$aLargeFile = array(
								'name' => $aNewValueLarge['name'][$i],
								'type' => $aNewValueLarge['type'][$i],
								'tmp_name' => $aNewValueLarge['tmp_name'][$i],
								'error' => $aNewValueLarge['error'][$i],
								'size' => $aNewValueLarge['size'][$i],
							);

							$aSmallFile = isset($aNewValueSmall['name'][$i])
								? array(
									'name' => $aNewValueSmall['name'][$i],
									'type' => $aNewValueSmall['type'][$i],
									'tmp_name' => $aNewValueSmall['tmp_name'][$i],
									'error' => $aNewValueSmall['error'][$i],
									'size' => $aNewValueSmall['size'][$i],
								)
								: NULL;

							// -------
							$description = $this->_getEachPost("description_field_{$oField->id}");
							if (!is_null($description))
							{
								$oFileValue->file_description = $description;
							}

							$description_small = $this->_getEachPost("description_small_field_{$oField->id}");

							if (!is_null($description_small))
							{
								$oFileValue->file_small_description = $description_small;
							}
							// -------

							$oFileValue->save();

							$this->_loadFiles($aLargeFile, $aSmallFile, $oFileValue, $oField, "field_{$oField->id}");

							$this->_Admin_Form_Controller->addMessage(ob_get_clean());

							ob_start();
							Core::factory('Core_Html_Entity_Script')
								->value("$(\"#{$windowId} div[id^='file_large'] input[name='field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} div[id^='file_small'] input[name='small_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'small_field_{$oField->id}_{$oFileValue->id}');" .
								// Description
								"$(\"#{$windowId} input[name='description_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'description_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='description_small_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'description_small_field_{$oField->id}_{$oFileValue->id}');" .
								// Large
								"$(\"#{$windowId} input[name='large_max_width_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'large_max_width_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_max_height_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'large_max_height_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_preserve_aspect_ratio_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'large_preserve_aspect_ratio_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_place_watermark_checkbox_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'large_place_watermark_checkbox_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='watermark_position_x_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'watermark_position_x_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='watermark_position_y_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'watermark_position_y_field_{$oField->id}_{$oFileValue->id}');" .
								// Small
								"$(\"#{$windowId} input[name='small_max_width_small_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'small_max_width_small_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='small_max_height_small_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'small_max_height_small_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='small_preserve_aspect_ratio_small_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'small_preserve_aspect_ratio_small_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='small_place_watermark_checkbox_small_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'small_place_watermark_checkbox_small_field_{$oField->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='create_small_image_from_large_small_field_{$oField->id}\\[\\]']\").eq(0).attr('name', 'create_small_image_from_large_small_field_{$oField->id}_{$oFileValue->id}');"
								)
								->execute();

							$this->_Admin_Form_Controller->addMessage(ob_get_clean());
						}
					}
				break;

				default:
					/*throw new Core_Exception(
						Core::_('Field.type_does_not_exist'),
							array('%d' => $oField->type)
					);*/
					Core_Event::notify('Field_Controller_Tab.onApplyObjectProperty', $this, array($oField, $aField_Values));
			}
		}

		Core_Event::notify('Field_Controller_Tab.onAfterApplyObjectProperty', $this, array($this->_Admin_Form_Controller, $aFields));
	}

	/**
	* Return value by key from POST
	* @param string $name key
	* @return string
	*/
	protected function _getEachPost($name)
	{
		if (!isset($this->_POST[$name]))
		{
			return NULL;
		}

		if (is_array($this->_POST[$name]))
		{
			$val = array_shift($this->_POST[$name]);

			return $val;
		}

		return $this->_POST[$name];
	}

	/**
	* Load files
	* @param array $aLargeFile large file data
	* @param array $aSmallFile small file data
	* @param Field_Value_File_Model $oFileValue value of file object
	* @param Field_Model $oField field
	* @param string $sFieldName field name
	*/
	protected function _loadFiles($aLargeFile, $aSmallFile, $oFileValue, $oField, $sFieldName)
	{
		$sPath = CMS_FOLDER . Field_Controller::getPath($this->_object);
		$oFileValue->setDir($sPath);

		$param = array();

		$aFileData = $aLargeFile;
		$aSmallFileData = $aSmallFile;

		$large_image = '';
		$small_image = '';

		$aCore_Config = Core::$mainConfig;

		$create_small_image_from_large = $this->_getEachPost("create_small_image_from_large_small_{$sFieldName}");

		$bLargeImageIsCorrect =
			// Поле файла большого изображения существует
			!is_null($aFileData)
			// и передан файл
			&& intval($aFileData['size']) > 0;

		if ($bLargeImageIsCorrect)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'], $aCore_Config['availableExtension']))
			{
				// Удаление файла большого изображения
				if ($oFileValue->file)
				{
					$oFileValue
						->deleteLargeFile()
						//->deleteSmallFile()
						;
				}

				$file_name = $aFileData['name'];

				// Не преобразовываем название загружаемого файла
				$large_image = !$oField->change_filename
					? $file_name
					// : $this->linkedObject->getLargeFileName($this->_object, $oFileValue, $aFileData['name']);
					: 'field' . $oFileValue->id . '.' . Core_File::getExtension($aFileData['name']);
			}
			else
			{
				$this->_Admin_Form_Controller->addMessage(
					Core_Message::get(
						Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		$bSmallImageIsCorrect =
			// Поле файла малого изображения существует
			!is_null($aSmallFileData)
			&& $aSmallFileData['size'];

		// Задано малое изображение и при этом не задано создание малого изображения
		// из большого или задано создание малого изображения из большого и
		// при этом не задано большое изображение.
		if ($bSmallImageIsCorrect || $create_small_image_from_large && $bLargeImageIsCorrect)
		{
			// Удаление файла малого изображения
			if ($oFileValue->file_small)
			{
				$oFileValue->deleteSmallFile();
			}

			// Явно указано малое изображение
			if ($bSmallImageIsCorrect
				&& Core_File::isValidExtension($aSmallFileData['name'], $aCore_Config['availableExtension']))
			{
				// задано изображение
				if ($oFileValue->file != '')
				{
					$create_large_image = FALSE;
				}
				else // ранее не было задано большое изображение
				{
					$create_large_image = empty($large_image);
				}

				$file_name = $aSmallFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oField->change_filename)
				{
					if ($create_large_image)
					{
						$large_image = $file_name;
						$small_image = 'small_' . $large_image;
					}
					else
					{
						$small_image = $file_name;
					}
				}
				else
				{
					// $small_image = $this->linkedObject->getSmallFileName($this->_object, $oFileValue, $aSmallFileData['name']);
					$small_image = 'small_field' . $oFileValue->id . '.' . Core_File::getExtension($aFileData['name']);
				}
			}
			elseif ($create_small_image_from_large && $bLargeImageIsCorrect)
			{
				$small_image = 'small_' . $large_image;
				//$param['small_image_source'] = $aFileData['tmp_name'];
				// Имя большого изображения
				$param['small_image_name'] = $aFileData['name'];
			}
			// Тип загружаемого файла является недопустимым для загрузки файла
			else
			{
				$this->_Admin_Form_Controller->addMessage(
					Core_Message::get(
						Core::_('Core.extension_does_not_allow', Core_File::getExtension($aSmallFileData['name'])),
						'error'
					)
				);
			}
		}

		if ($bLargeImageIsCorrect || $bSmallImageIsCorrect)
		{
			if ($bLargeImageIsCorrect)
			{
				// Путь к файлу-источнику большого изображения;
				$param['large_image_source'] = $aFileData['tmp_name'];
				// Оригинальное имя файла большого изображения
				$param['large_image_name'] = $aFileData['name'];
			}

			if ($bSmallImageIsCorrect)
			{
				// Путь к файлу-источнику малого изображения;
				$param['small_image_source'] = $aSmallFileData['tmp_name'];
				// Оригинальное имя файла малого изображения
				$param['small_image_name'] = $aSmallFileData['name'];
			}

			// Путь к создаваемому файлу большого изображения;
			$param['large_image_target'] = !empty($large_image)
				? $sPath . $large_image
				: '';

			// Путь к создаваемому файлу малого изображения;
			$param['small_image_target'] = !empty($small_image)
				? $sPath . $small_image
				: '';

			// Использовать большое изображение для создания малого
			$param['create_small_image_from_large'] = $create_small_image_from_large;

			// Значение максимальной ширины большого изображения
			$param['large_image_max_width'] = $this->_getEachPost("large_max_width_{$sFieldName}");

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = $this->_getEachPost("large_max_height_{$sFieldName}");

			// Значение максимальной ширины малого изображения;
			$param['small_image_max_width'] = $this->_getEachPost("small_max_width_small_{$sFieldName}");

			// Значение максимальной высоты малого изображения;
			$param['small_image_max_height'] = $this->_getEachPost("small_max_height_small_{$sFieldName}");

			// Путь к файлу с "водяным знаком"
			// $param['watermark_file_path'] = $this->linkedObject->watermarkFilePath;

			// Позиция "водяного знака" по оси X
			// $param['watermark_position_x'] = $this->_getEachPost("watermark_position_x_{$sFieldName}");

			// Позиция "водяного знака" по оси Y
			// $param['watermark_position_y'] = $this->_getEachPost("watermark_position_y_{$sFieldName}");

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), FALSE - не наложить);
			// $param['large_image_watermark'] = !is_null($this->_getEachPost("large_place_watermark_checkbox_{$sFieldName}"));

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), FALSE - не наложить);
			// $param['small_image_watermark'] = !is_null($this->_getEachPost("small_place_watermark_checkbox_small_{$sFieldName}"));

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null($this->_getEachPost("large_preserve_aspect_ratio_{$sFieldName}"));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = !is_null($this->_getEachPost("small_preserve_aspect_ratio_small_{$sFieldName}"));

			// $this->linkedObject->createFieldDir($this->_object);
			Core_File::mkdir($sPath, CHMOD, TRUE);

			try
			{
				$result = Core_File::adminUpload($param);

				if ($result['large_image'])
				{
					$oFileValue->file = $large_image;
					$oFileValue->file_name = is_null($param['large_image_name'])
						? ''
						: $param['large_image_name'];
				}

				if ($result['small_image'])
				{
					$oFileValue->file_small = $small_image;
					$oFileValue->file_small_name = is_null($param['small_image_name'])
						? ''
						: $param['small_image_name'];
				}

				$oFileValue->save();
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}
		}
	}

	/**
	* Correct save value by field type
	* @param Field $oField field
	* @param string $value value
	* @return string
	*/
	protected function _correctValue($oField, $value)
	{
		switch ($oField->type)
		{
			case 0: // Int
			case 7: // Checkbox
			case 3: // List
				$value = intval($value);
			break;
			case 1: // String
			case 4: // Textarea
			case 6: // Wysiwyg
				$value = strval($value);
			break;
			case 11: // Float
				$value = floatval(
					str_replace(',', '.', $value)
				);
			break;
			case 8: // Date
				$value = $value == ''
					? '0000-00-00 00:00:00'
					: Core_Date::date2sql($value);
			break;
			case 9: // Datetime
				$value = $value == ''
					? '0000-00-00 00:00:00'
					: Core_Date::datetime2sql($value);
			break;
		}

		return $value;
	}

	/**
	* Correct print value by field type
	* @param Field $oField field
	* @param string $value value
	* @return string
	*/
	protected function _correctPrintValue($oField, $value)
	{
		switch ($oField->type)
		{
			case 7: // Checkbox
				$value = 1;
			break;
			case 8: // Date
				$value = $value == '0000-00-00 00:00:00'
					? ''
					: Core_Date::date2sql($value);
			break;
			case 9: // Datetime
				$value = $value == '0000-00-00 00:00:00'
					? ''
					: Core_Date::datetime2sql($value);
			break;
		}

		return $value;
	}
}