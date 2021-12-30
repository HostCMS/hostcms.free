<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Property_Controller_Tab extends Core_Servant_Properties
{
	/**
	* Allowed object properties
	* @var array
	*/
	protected $_allowedProperties = array(
		'linkedObject',
		'template_id',
	);

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

		parent::__construct();

		$this->template_id = 0;
	}

	/**
	* Create and return an object of Property_Controller_Tab for current skin
	* @return object
	*/
	static public function factory(Admin_Form_Controller $Admin_Form_Controller)
	{
		$className = 'Skin_' . ucfirst(Core_Skin::instance()->getSkinName()) . '_' . __CLASS__;
		//die($className);

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

	protected $_property_values = array();

	/**
	* Show properties on tab
	* @return self
	*/
	public function fillTab()
	{
		$aTmp_Property_Values = $this->_object->id
			? $this->_object->getPropertyValues(FALSE, array(), TRUE)
			: array();

		$this->_property_values = array();
		foreach ($aTmp_Property_Values as $oProperty_Value)
		{
			$this->_property_values[$oProperty_Value->property_id][] = $oProperty_Value;
		}
		unset($aTmp_Property_Values);

		$this->_setPropertyDirs(0, $this->_tab);

		return $this;
	}

	public function imgBox($oAdmin_Form_Entity, $oProperty, $addFunction = '$.cloneProperty', $deleteOnclick = '$.deleteNewProperty(this)')
	{
		$oAdmin_Form_Entity
			->add($this->getImgAdd($oProperty, $addFunction))
			->add($this->getImgDelete($deleteOnclick));

		return $this;
	}

	/**
	* Show plus button
	* @param Property_Model $oProperty property
	* @param string $function function name
	* @return string
	*/
	public function getImgAdd($oProperty, $addFunction = '$.cloneProperty')
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		ob_start();
		Core::factory('Core_Html_Entity_Img')
			->src('/admin/images/action_add.gif')
			->id('add')
			->class('pointer left5px img_line')
			->onclick("{$addFunction}('{$windowId}', '{$oProperty->id}')")
			->execute();
		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code')->html(ob_get_clean());

		return $oAdmin_Form_Entity_Code;
	}

	/**
	* Show minus button
	* @param string $onclick onclick attribute value
	* @return string
	*/
	public function getImgDelete($onclick = '$.deleteNewProperty(this)')
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
	public function getImgDeletePath()
	{
		return "res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { mainFormLocker.unlock(); $.deleteProperty(this, {path: '{$this->_Admin_Form_Controller->getPath()}', action: 'deletePropertyValue', datasetId: '{$this->_datasetId}', objectId: '{$this->_object->id}'}) } else {return false}";
	}

	/**
	* List Options Cache
	* @var array
	*/
	protected $_cacheListOptions = array();

	/**
	* Add external properties container to $parentObject
	* @param int $property_dir_id ID of parent directory of properties
	* @param object $parentObject
	* @hostcms-event Property_Controller_Tab.onBeforeAddFormEntity
	* @hostcms-event Property_Controller_Tab.onBeforeCreatePropertyValue
	* @hostcms-event Property_Controller_Tab.onAfterCreatePropertyValue
	* @hostcms-event Property_Controller_Tab.onAfterCreatePropertyListValues
	* @hostcms-event Property_Controller_Tab.onSetPropertyType
	* @hostcms-event Property_Controller_Tab.onBeforeAddSection
	*/
	protected function _setPropertyDirs($property_dir_id, $parentObject)
	{
		$oAdmin_Form_Entity_Panel = Admin_Form_Entity::factory('Section')
			->caption($property_dir_id == 0
				? Core::_('Property_Dir.main_section')
				: htmlspecialchars(Core_Entity::factory('Property_Dir', $property_dir_id)->name)
			)
			->id('accordion_' . $property_dir_id)
			->class('property_dir');

		// Properties
		$oProperties = $this->_getProperties();
		$oProperties
			->queryBuilder()
			->where('property_dir_id', '=', $property_dir_id);

		$aProperties = $oProperties->findAll();

		foreach ($aProperties as $oProperty)
		{
			$this->_addIntoSection($oAdmin_Form_Entity_Panel, $oProperty);
		}

		// Property Dirs
		$oProperty_Dirs = $this->linkedObject->Property_Dirs;

		$oProperty_Dirs
			->queryBuilder()
			->where('parent_id', '=', $property_dir_id);

		$aProperty_Dirs = $oProperty_Dirs->findAll();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			$this->_setPropertyDirs($oProperty_Dir->id, $property_dir_id == 0 ? $this->_tab : $oAdmin_Form_Entity_Panel);
		}

		// Оставшиеся значения выводятся внизу
		if ($property_dir_id == 0 && count($this->_property_values))
		{
			foreach ($this->_property_values as $property_id => $aProperty_Values)
			{
				$this->_addIntoSection($oAdmin_Form_Entity_Panel, Core_Entity::factory('Property', $property_id));
			}
		}

		Core_Event::notify('Property_Controller_Tab.onBeforeAddSection', $this, array($oAdmin_Form_Entity_Panel, $property_dir_id));

		$oAdmin_Form_Entity_Panel->getCountChildren() && $parentObject->add($oAdmin_Form_Entity_Panel);
	}

	protected function _addIntoSection($oAdmin_Form_Entity_Panel, $oProperty)
	{
		/*$aProperty_Values = $this->_object->id
			? $oProperty->getValues($this->_object->id, FALSE)
			: array();*/

		if (isset($this->_property_values[$oProperty->id]))
		{
			$aProperty_Values = $this->_property_values[$oProperty->id];
			unset($this->_property_values[$oProperty->id]);
		}
		else
		{
			$aProperty_Values = array();
		}

		$oAdmin_Form_Entity_Panel->add(
			$oAdmin_Form_Entity_Section = Admin_Form_Entity::factory('Div')
				->class('section-' . $oProperty->id)
		);

		$oAdmin_Form_Entity = NULL;

		$iPropertyCounter = 0;

		switch ($oProperty->type)
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

				$width = 410;

				Core_Event::notify('Property_Controller_Tab.onBeforeCreatePropertyValue', $this, array($oProperty, $oAdmin_Form_Entity));

				$aFormat = $oProperty->obligatory
					? array('minlen' => array('value' => 1))
					: array();

				switch ($oProperty->type)
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
							'max_width' => $oProperty->image_large_max_width,
							'max_height' => $oProperty->image_large_max_height,
							'show_description' => TRUE,
						);

						$smallImage = array(
							'caption' => Core::_('Property.small_file_caption', $oProperty->name),
							'show' => !$oProperty->hide_small_image,
							'max_width' => $oProperty->image_small_max_width,
							'max_height' => $oProperty->image_small_max_height,
							'show_description' => TRUE
						);

						if (method_exists($this->linkedObject, 'getWatermarkDefaultPositionX')
							&& method_exists($this->linkedObject, 'getWatermarkDefaultPositionY'))
						{
							$largeImage['watermark_position_x'] = $this->linkedObject->getWatermarkDefaultPositionX();
							$largeImage['watermark_position_y'] = $this->linkedObject->getWatermarkDefaultPositionY();
						}

						$largeImage['place_watermark_checkbox_checked'] = $oProperty->watermark_default_use_large_image;
						$smallImage['place_watermark_checkbox_checked'] = $oProperty->watermark_default_use_small_image;

						$largeImage['preserve_aspect_ratio_checkbox_checked'] = $oProperty->preserve_aspect_ratio;
						$smallImage['preserve_aspect_ratio_checkbox_checked'] = $oProperty->preserve_aspect_ratio_small;

						$oAdmin_Form_Entity = Admin_Form_Entity::factory('File')
							->style('width: 340px')
							->largeImage($largeImage)
							->smallImage($smallImage)
							->crop(TRUE);

						// $width = 710;
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
							->template_id($this->template_id);
					break;

					case 7: // Checkbox
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox');
						count($aProperty_Values) && $oAdmin_Form_Entity->postingUnchecked(TRUE);
					break;

					case 8: // Date
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Date')->format($aFormat);
					break;

					case 9: // Datetime
						$oAdmin_Form_Entity = Admin_Form_Entity::factory('Datetime')->format($aFormat);
					break;
				}

				Core_Event::notify('Property_Controller_Tab.onAfterCreatePropertyValue', $this, array($oProperty, $oAdmin_Form_Entity));

				if ($oAdmin_Form_Entity)
				{
					$oAdmin_Form_Entity
						->name("property_{$oProperty->id}[]")
						->id("id_property_{$oProperty->id}_00{$iPropertyCounter}")
						->caption(htmlspecialchars($oProperty->name))
						->value(
							$this->_correctPrintValue($oProperty, $oProperty->default_value)
						)
						->divAttr(array(
							'class' => //($oProperty->type != 2 ? 'form-group' : 'input-group')
								'form-group'
								. (
									($oProperty->type == 7 || $oProperty->type == 8 || $oProperty->type == 9)
									? ' col-xs-12 col-sm-7 col-md-6 col-lg-5'
									: ' col-xs-12' // ' col-xs-12'
								)
								. ($oProperty->type == 7 ? ' margin-top-21' : '')
						));

					$oProperty->type == 7
						&& $oAdmin_Form_Entity->checked($oProperty->default_value == 1);

					//$oProperty->multiple && $oAdmin_Form_Entity->add($this->getImgAdd($oProperty));

					// Значений св-в нет для объекта
					if (count($aProperty_Values) == 0)
					{
						Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity, $oAdmin_Form_Entity_Section, $oProperty));

						$oDiv_Group = Admin_Form_Entity::factory('Div')
							->class($oProperty->multiple ? 'input-group' : '')
							->add($oAdmin_Form_Entity);

						$oAdmin_Form_Entity_Section->add(
							Admin_Form_Entity::factory('Div')
								->class('row')
								->id("property_{$oProperty->id}")
								->add($oDiv_Group)
						);

						$oProperty->multiple && $this->imgBox($oDiv_Group, $oProperty);
					}
					else
					{
						foreach ($aProperty_Values as $oProperty_Value)
						{
							$oNewAdmin_Form_Entity = clone $oAdmin_Form_Entity;

							switch ($oProperty->type)
							{
								default:
									$oNewAdmin_Form_Entity->value($oProperty_Value->value);
								break;

								case 2: // File
									$sDirHref = $this->linkedObject->getDirHref($this->_object);

									if ($oProperty_Value->file != '')
									{
										$oNewAdmin_Form_Entity->largeImage(
											Core_Array::union($oNewAdmin_Form_Entity->largeImage, array(
												'path' => $sDirHref . rawurlencode($oProperty_Value->file),
												'originalName' => $oProperty_Value->file_name,
												'delete_onclick' => $this->_Admin_Form_Controller->getAdminActionLoadAjax($this->_Admin_Form_Controller->getPath(), 'deletePropertyValue', "large_property_{$oProperty->id}_{$oProperty_Value->id}", $this->_datasetId, $this->_object->id)
											))
										);
									}
									// Description doesn't depend on loaded file
									$oNewAdmin_Form_Entity->largeImage(
										Core_Array::union($oNewAdmin_Form_Entity->largeImage, array(
											'description' => $oProperty_Value->file_description
										)
									));

									if ($oProperty_Value->file_small != '')
									{
										$oNewAdmin_Form_Entity->smallImage(
											Core_Array::union($oNewAdmin_Form_Entity->smallImage, array(
												'path' => $sDirHref . rawurlencode($oProperty_Value->file_small),
												'originalName' => $oProperty_Value->file_small_name,
												'delete_onclick' => $this->_Admin_Form_Controller->getAdminActionLoadAjax($this->_Admin_Form_Controller->getPath(), 'deletePropertyValue', "small_property_{$oProperty->id}_{$oProperty_Value->id}", $this->_datasetId, $this->_object->id),
												'create_small_image_from_large_checked' => FALSE,
											))
										);
									}

									// Description doesn't depend on loaded file
									$oNewAdmin_Form_Entity->smallImage(
										Core_Array::union($oNewAdmin_Form_Entity->smallImage, array(
											'description' => $oProperty_Value->file_small_description
										)
									));
								break;
								case 7: // Checkbox
									$oNewAdmin_Form_Entity->checked($oProperty_Value->value == 1);
								break;
								case 8: // Date
									$oNewAdmin_Form_Entity->value(
										$this->_correctPrintValue($oProperty, $oProperty_Value->value)
									);
								break;
								case 9: // Datetime
									$oNewAdmin_Form_Entity->value(
										$this->_correctPrintValue($oProperty, $oProperty_Value->value)
									);
								break;
							}

							$oNewAdmin_Form_Entity
								->name("property_{$oProperty->id}_{$oProperty_Value->id}")
								->id("id_property_{$oProperty->id}_{$oProperty_Value->id}");

							Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity, $oAdmin_Form_Entity_Section, $oProperty, $oProperty_Value));

							$oDiv_Group = Admin_Form_Entity::factory('Div')
								->class($oProperty->multiple ? 'input-group' : '')
								->add($oNewAdmin_Form_Entity);

							$oAdmin_Form_Entity_Section->add(
								Admin_Form_Entity::factory('Div')
									->class('row')
									->id("property_{$oProperty->id}")
									->add($oDiv_Group)
							);

							// Визуальный редактор клонировать запрещено
							$oProperty->multiple /*&& $oProperty->type != 6*/
								&& $this->imgBox($oDiv_Group, $oProperty, '$.cloneProperty', $this->getImgDeletePath());
						}
					}
				}
			break;

			case 3: // List
				if (Core::moduleIsActive('list'))
				{
					$oAdmin_Form_Entity_ListItems = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oProperty->name))
						->name("property_{$oProperty->id}[]")
						->value(
							$this->_correctPrintValue($oProperty, $oProperty->default_value)
						)
						->divAttr(array('class' => 'form-group col-xs-12'));

					// Перенесно в _fillList()
					/*$oProperty->obligatory
						&& $oAdmin_Form_Entity_ListItems->data('required', 1);*/

					$oAdmin_Form_Entity_ListItemsInput = Admin_Form_Entity::factory('Input')
						->caption(htmlspecialchars($oProperty->name))
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-8'))
						->id("id_property_{$oProperty->id}_00{$iPropertyCounter}") // id_property_ !!!
						->name("input_property_{$oProperty->id}[]");

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
					if (count($aProperty_Values) == 0)
					{
						Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_ListItems,$oAdmin_Form_Entity_Section, $oProperty));

						$this->_fillList($oProperty->default_value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_ListItems, $oAdmin_Form_Entity_ListItemsInput, $oAdmin_Form_Entity_Autocomplete_Select);
					}
					else
					{
						foreach ($aProperty_Values as $key => $oProperty_Value)
						{
							$value = $oProperty_Value->value;

							$oNewAdmin_Form_Entity_ListItems = clone $oAdmin_Form_Entity_ListItems;
							$oNewAdmin_Form_Entity_ListItems
								->id("id_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}") // id_ should be, see js!
								->name("property_{$oProperty->id}_{$oProperty_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_ListItemsInput = clone $oAdmin_Form_Entity_ListItemsInput;
							$oNewAdmin_Form_Entity_ListItemsInput
								->id("id_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}") // id_property_ !!!
								->name("input_property_{$oProperty->id}_{$oProperty_Value->id}");

							$oNewAdmin_Form_Entity_Autocomplete_Select = clone $oAdmin_Form_Entity_Autocomplete_Select;
							$oNewAdmin_Form_Entity_Autocomplete_Select
								->id($oNewAdmin_Form_Entity_ListItemsInput->id . '_mode'); // id_property_ !!!

							Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_ListItems, $oAdmin_Form_Entity_Section, $oProperty, $oProperty_Value));

							$this->_fillList($value, $oProperty, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_ListItems, $oNewAdmin_Form_Entity_ListItemsInput, $oNewAdmin_Form_Entity_Autocomplete_Select);
						}
					}
				}
			break;

			case 5: // ИС
				if (Core::moduleIsActive('informationsystem'))
				{
					// Группы
					$oAdmin_Form_Entity_InfGroups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oProperty->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("id_group_{$oProperty->id}_00{$iPropertyCounter}") // id_ should be, see js!
						->name("group_property_{$oProperty->id}[]")
						->filter(TRUE);

					// Элементы
					$oAdmin_Form_Entity_InfItems = Admin_Form_Entity::factory('Select')
						->id("id_property_{$oProperty->id}")
						->name("property_{$oProperty->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_InfItemsInput = Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_property_{$oProperty->id}_00{$iPropertyCounter}")
						->name("input_property_{$oProperty->id}[]");

					// Значений св-в нет для объекта
					if (count($aProperty_Values) == 0)
					{
						Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_Section, $oProperty));

						$this->_fillInformationSystem($oProperty->default_value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfItems, $oAdmin_Form_Entity_InfItemsInput);
					}
					else
					{
						foreach ($aProperty_Values as $key => $oProperty_Value)
						{
							$value = $oProperty_Value->value;

							$oNewAdmin_Form_Entity_Inf_Groups = clone $oAdmin_Form_Entity_InfGroups;
							$oNewAdmin_Form_Entity_Inf_Groups
								->id("id_group_{$oProperty->id}_{$oProperty_Value->id}"); // id_ should be, see js!

							$oNewAdmin_Form_Entity_InfItems = clone $oAdmin_Form_Entity_InfItems;
							$oNewAdmin_Form_Entity_InfItems
								->id("id_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}") // id_ should be, see js!
								->name("property_{$oProperty->id}_{$oProperty_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_InfItemsInput = clone $oAdmin_Form_Entity_InfItemsInput;
							$oNewAdmin_Form_Entity_InfItemsInput
								->id("input_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}")
								->name("input_property_{$oProperty->id}_{$oProperty_Value->id}");

							Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_Inf_Groups, $oAdmin_Form_Entity_Section, $oProperty, $oProperty_Value));

							$this->_fillInformationSystem($value, $oProperty, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Inf_Groups, $oNewAdmin_Form_Entity_InfItems, $oNewAdmin_Form_Entity_InfItemsInput);
						}
					}
				}
			break;

			case 13: // ИС, группа
				if (Core::moduleIsActive('informationsystem'))
				{
					// Группы
					$oAdmin_Form_Entity_InfGroups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oProperty->name))
						->id("id_property_{$oProperty->id}")
						->name("property_{$oProperty->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_InfGroupsInput = Admin_Form_Entity::factory('Input')
						->caption(htmlspecialchars($oProperty->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_property_{$oProperty->id}_00{$iPropertyCounter}")
						->name("input_property_{$oProperty->id}[]");

					// Значений св-в нет для объекта
					if (count($aProperty_Values) == 0)
					{
						Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_Section, $oProperty));

						$this->_fillInformationSystemGroup($oProperty->default_value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfGroupsInput);
					}
					else
					{
						foreach ($aProperty_Values as $key => $oProperty_Value)
						{
							$value = $oProperty_Value->value;

							$oNewAdmin_Form_Entity_Inf_Groups = clone $oAdmin_Form_Entity_InfGroups;
							$oNewAdmin_Form_Entity_Inf_Groups
								->id("id_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}") // id_ should be, see js!
								->name("property_{$oProperty->id}_{$oProperty_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_InfGroupsInput = clone $oAdmin_Form_Entity_InfGroupsInput;
							$oNewAdmin_Form_Entity_InfGroupsInput
								->id("input_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}")
								->name("input_property_{$oProperty->id}_{$oProperty_Value->id}");

							Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_Inf_Groups, $oAdmin_Form_Entity_Section, $oProperty, $oProperty_Value));

							$this->_fillInformationSystemGroup($value, $oProperty, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Inf_Groups, $oNewAdmin_Form_Entity_InfGroupsInput);
						}
					}
				}
			break;

			case 12: // Интернет-магазин
				if (Core::moduleIsActive('shop'))
				{
					// Группы
					$oAdmin_Form_Entity_Shop_Groups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oProperty->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("id_group_{$oProperty->id}_00{$iPropertyCounter}") // id_ should be, see js!
						->name("group_property_{$oProperty->id}[]")
						->filter(TRUE);

					// Элементы
					$oAdmin_Form_Entity_Shop_Items = Admin_Form_Entity::factory('Select')
						->id("id_property_{$oProperty->id}")
						->name("property_{$oProperty->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_Shop_Items_Input = Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_property_{$oProperty->id}_00{$iPropertyCounter}") // id_ should be, see js!
						->name("input_property_{$oProperty->id}[]");

					// Значений св-в нет для объекта
					if (count($aProperty_Values) == 0)
					{
						Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_Shop_Items, $oAdmin_Form_Entity_Section, $oProperty));

						$this->_fillShop($oProperty->default_value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Items, $oAdmin_Form_Entity_Shop_Items_Input);
					}
					else
					{
						foreach ($aProperty_Values as $key => $oProperty_Value)
						{
							$value = $oProperty_Value->value;

							$oNewAdmin_Form_Entity_Shop_Groups = clone $oAdmin_Form_Entity_Shop_Groups;
							$oNewAdmin_Form_Entity_Shop_Groups
								->id("id_group_{$oProperty->id}_{$oProperty_Value->id}"); // id_ should be, see js!

							$oNewAdmin_Form_Entity_Shop_Items = clone $oAdmin_Form_Entity_Shop_Items;
							$oNewAdmin_Form_Entity_Shop_Items
								->id("id_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}") // id_ should be, see js!
								->name("property_{$oProperty->id}_{$oProperty_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_Shop_Items_Input = clone $oAdmin_Form_Entity_Shop_Items_Input;
							$oNewAdmin_Form_Entity_Shop_Items_Input
								->id("input_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}")
								->name("input_property_{$oProperty->id}_{$oProperty_Value->id}");

							Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Section, $oProperty, $oProperty_Value));

							$this->_fillShop($value, $oProperty, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Shop_Groups, $oNewAdmin_Form_Entity_Shop_Items, $oNewAdmin_Form_Entity_Shop_Items_Input);
						}
					}
				}
			break;

			case 14: // Интернет-магазин, группа
				if (Core::moduleIsActive('shop'))
				{
					// Группы
					$oAdmin_Form_Entity_Shop_Groups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oProperty->name))
						->id("id_property_{$oProperty->id}")
						->name("property_{$oProperty->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_Shop_Groups_Input = Admin_Form_Entity::factory('Input')
						->caption(htmlspecialchars($oProperty->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_property_{$oProperty->id}_00{$iPropertyCounter}") // id_ should be, see js!
						->name("input_property_{$oProperty->id}[]");

					// Значений св-в нет для объекта
					if (count($aProperty_Values) == 0)
					{
						Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Section, $oProperty));

						$this->_fillShopGroup($oProperty->default_value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Groups_Input);
					}
					else
					{
						foreach ($aProperty_Values as $key => $oProperty_Value)
						{
							$value = $oProperty_Value->value;

							$oNewAdmin_Form_Entity_Shop_Groups = clone $oAdmin_Form_Entity_Shop_Groups;
							$oNewAdmin_Form_Entity_Shop_Groups
								->id("id_group_{$oProperty->id}_{$oProperty_Value->id}") // id_ should be, see js!
								->name("property_{$oProperty->id}_{$oProperty_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_Shop_Groups_Input = clone $oAdmin_Form_Entity_Shop_Groups_Input;
							$oNewAdmin_Form_Entity_Shop_Groups_Input
								->id("input_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}")
								->name("input_property_{$oProperty->id}_{$oProperty_Value->id}");

							Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Section, $oProperty, $oProperty_Value));

							$this->_fillShopGroup($value, $oProperty, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Shop_Groups, $oNewAdmin_Form_Entity_Shop_Groups_Input);
						}
					}
				}
			break;

			default:
				/*throw new Core_Exception(
					Core::_('Property.type_does_not_exist'),
						array('%d' => $oProperty->type)
				);*/
				Core_Event::notify('Property_Controller_Tab.onSetPropertyType', $this, array($oAdmin_Form_Entity_Section, $oProperty, $aProperty_Values));
		}

		if ($oProperty->multiple)
		{
			$oAdmin_Form_Entity_Section->add(Core::factory('Core_Html_Entity_Script')->value("
				$('.section-" . $oProperty->id . "').sortable({
					connectWith: '.section-" . $oProperty->id . "',
					items: '> div#property_" . $oProperty->id . "',
					scroll: false,
					placeholder: 'placeholder',
					tolerance: 'pointer',
					// appendTo: 'body',
					// helper: 'clone',
					helper: function(event, ui) {
						var jUi = $(ui),
							clone = jUi.clone();

						// установить актуальные выбранные элементы у склонированных списков
						jUi.find('select').each(function(index, object){
							clone.find('#' + object.id).val($(object).val());
						});

						return clone.css('position','absolute').get(0);
					},
					start: function(event, ui) {
						// Ghost show
						$('.section-" . $oProperty->id . "').find('div#property_" . $oProperty->id . ":hidden')
							.addClass('ghost-item')
							.css('opacity', .5)
							.show();
					},
					stop: function(event, ui) {
						// Ghost hide
						$('.section-" . $oProperty->id . "').find('div.ghost-item')
							.removeClass('ghost-item')
							.css('opacity', 1);
					}
				}).disableSelection();
			"));
		}

		return $this;
	}

	protected function _fillList($value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_ListItemsSelect, $oAdmin_Form_Entity_ListItemsInput, $oAdmin_Form_Entity_Autocomplete_Select)
	{
		$oList_Item = Core_Entity::factory('List_Item', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oProperty->default_value;

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oList = $oProperty->List;

		$iCountItems = $oList->List_Items->getCount();

		$bAutocomplete = $iCountItems > Core::$mainConfig['switchSelectToAutocomplete'];

		if (!$bAutocomplete)
		{
			if (!isset($this->_cacheListOptions[$oProperty->list_id]))
			{
				$this->_cacheListOptions[$oProperty->list_id] = array(' … ');
				$this->_cacheListOptions[$oProperty->list_id] += $oProperty->List->getListItemsTree();
			}

			$oProperty->obligatory
				&& $oAdmin_Form_Entity_ListItemsSelect->data('required', 1);

			$oAdmin_Form_Entity_ListItemsSelect
				->options($this->_cacheListOptions[$oProperty->list_id]);

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

			$oProperty->obligatory
				&& $oAdmin_Form_Entity_ListItemsInput->format(array('minlen' => array('value' => 1)));
		}

		$oDiv_Group = Admin_Form_Entity::factory('Div')
			->class($oProperty->multiple ? 'input-group' : '')
			->add($oAdmin_Form_Entity_ListItemsSelect)
			->add($oAdmin_Form_Entity_ListItemsInput)
			->add($oAdmin_Form_Entity_Autocomplete_Select);

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= id_property_{$oProperty->id}]').autocomplete({
					source: function(request, response) {
						var jInput = $(this.element),
							jTopParentDiv = jInput.parents('div[id ^= property]');

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
							jListItemDiv = jInput.parents('[id ^= property]').find('select[name ^= property_]');

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
								jListItemDiv = jInput.parents('[id ^= property]').find('select[name ^= property_]');

							jListItemDiv.empty().append($('<option>', { value: '', text: ''}).attr('selected', 'selected'));
						}
					},
					close: function() {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					}
				});
			")
		);

		$oProperty->multiple && $this->imgBox(
			$oDiv_Group,
			$oProperty,
			'$.cloneProperty',
			!$bIsNullValue
				? $this->getImgDeletePath()
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->id("property_{$oProperty->id}")
					->class('row')
					->add($oDiv_Group)
			);
	}

	protected function _fillInformationSystemGroup($value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfGroupsInput)
	{
		$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oProperty->default_value;

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oInformationsystem = $oProperty->Informationsystem;

		$aOptions = Informationsystem_Item_Controller_Edit::fillInformationsystemGroup($oProperty->informationsystem_id, 0);
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

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= input_property_{$oProperty->id}]').autocomplete({
					source: function(request, response) {
						var jInput = $(this.element),
							jTopParentDiv = jInput.parents('div[id ^= property]');

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
							jTopParentDiv = jInput.parents('div[id ^= property]'),
							jInfItemDiv = jTopParentDiv.find('select[name ^= property_]');

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

		$oProperty->multiple && $this->imgBox(
			$oDiv_Group,
			$oProperty,
			'$.clonePropertyInfSys',
			!$bIsNullValue
				? $this->getImgDeletePath()
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->id("property_{$oProperty->id}")
					->class('row')
					->add($oDiv_Group)
			);
	}

	/**
	* Fill information systems/items list
	* @param int $value informationsystem_item_id
	* @param Property_Model $oProperty property
	* @param Admin_Form_Entity_Select $oAdmin_Form_Entity_InfGroups
	* @param Admin_Form_Entity_Select $oAdmin_Form_Entity_InfItemsSelect
	*/
	protected function _fillInformationSystem($value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfItemsSelect, $oAdmin_Form_Entity_InfItemsInput)
	{
		$Informationsystem_Item = Core_Entity::factory('Informationsystem_Item', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oProperty->default_value;

		$group_id = $value == 0
			? 0
			: intval($Informationsystem_Item->informationsystem_group_id);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oInformationsystem = $oProperty->Informationsystem;

		// Groups
		$aOptions = Informationsystem_Item_Controller_Edit::fillInformationsystemGroup($oProperty->informationsystem_id, 0);
		$oAdmin_Form_Entity_InfGroups
			->value($Informationsystem_Item->informationsystem_group_id)
			->options(array(' … ') + $aOptions)
			->onchange("$.ajaxRequest({path: '/admin/informationsystem/item/index.php', context: '{$oAdmin_Form_Entity_InfItemsSelect->id}', callBack: $.loadSelectOptionsCallback, action: 'loadInformationItemList',additionalParams: 'informationsystem_group_id=' + this.value + '&informationsystem_id={$oProperty->informationsystem_id}',windowId: '{$windowId}'}); return false");

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

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= input_property_{$oProperty->id}]').autocomplete({
					source: function(request, response) {
						var jInput = $(this.element),
							jTopParentDiv = jInput.parents('div[id ^= property]'),
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
							jTopParentDiv = jInput.parents('div[id ^= property]'),
							jInfItemDiv = jTopParentDiv.find('select[name ^= property_]');

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

		$oProperty->multiple && $this->imgBox(
			$oDiv_Group,
			$oProperty,
			'$.clonePropertyInfSys',
			!$bIsNullValue
				? $this->getImgDeletePath()
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->id("property_{$oProperty->id}")
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

	protected function _fillShopGroup($value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Groups_Input)
	{
		$oShop_Group = Core_Entity::factory('Shop_Group', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oProperty->default_value;

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oShop = $oProperty->Shop;

		$aOptions = Shop_Item_Controller_Edit::fillShopGroup($oProperty->shop_id, 0);
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

		// autocomplete should be added always
		$oDiv_Group->add(
			Core::factory('Core_Html_Entity_Script')->value("
				$('#{$windowId} input[id ^= input_property_{$oProperty->id}]').autocomplete({
				source: function(request, response) {
					var jInput = $(this.element),
						jTopParentDiv = jInput.parents('div[id ^= property]'),
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
						jTopParentDiv = jInput.parents('div[id ^= property]'),
						jInfItemDiv = jTopParentDiv.find('select[name ^= property_]');

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

		$oProperty->multiple && $this->imgBox(
			$oDiv_Group,
			$oProperty,
			'$.clonePropertyInfSys',
			!$bIsNullValue
				? $this->getImgDeletePath()
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->id("property_{$oProperty->id}")
					->add($oDiv_Group)
			);
	}

	/**
	* Fill shops/items list
	* @param int $value shop_item_id
	* @param Property_Model $oProperty property
	* @param Admin_Form_Entity_Select $oAdmin_Form_Entity_Shop_Groups
	* @param Admin_Form_Entity_Select $oAdmin_Form_Entity_Shop_Items
	*/
	protected function _fillShop($value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Items, $oAdmin_Form_Entity_Shop_Items_Input)
	{
		$Shop_Item = Core_Entity::factory('Shop_Item', $value);

		$bIsNullValue = is_null($value);
		$bIsNullValue && $value = $oProperty->default_value;

		$group_id = $value == 0
			? 0
			: ($Shop_Item->modification_id
				? intval($Shop_Item->Modification->shop_group_id)
				: intval($Shop_Item->shop_group_id)
			);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oShop = $oProperty->Shop;

		// Groups
		$aOptions = Shop_Item_Controller_Edit::fillShopGroup($oProperty->shop_id, 0);
		$oAdmin_Form_Entity_Shop_Groups
			->value($group_id)
			->options(array(' … ') + $aOptions)
			->onchange("$.ajaxRequest({path: '/admin/shop/item/index.php', context: '{$oAdmin_Form_Entity_Shop_Items->id}', callBack: $.loadSelectOptionsCallback, action: 'loadShopItemList', additionalParams: 'shop_group_id=' + this.value + '&shop_id={$oProperty->shop_id}',windowId: '{$windowId}'}); return false");

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

			$aConfig = Core_Config::instance()->get('property_config', array()) + array(
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
				$('#{$windowId} input[id ^= input_property_{$oProperty->id}]').autocomplete({
				source: function(request, response) {
					var jInput = $(this.element),
						jTopParentDiv = jInput.parents('div[id ^= property]'),
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
						jTopParentDiv = jInput.parents('div[id ^= property]'),
						jInfItemDiv = jTopParentDiv.find('select[name ^= property_]');

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

		$oProperty->multiple && $this->imgBox(
			$oDiv_Group,
			$oProperty,
			'$.clonePropertyInfSys',
			!$bIsNullValue
				? $this->getImgDeletePath()
				: $this->getImgDelete()
		);

		$oAdmin_Form_Entity_Section
			->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->id("property_{$oProperty->id}")
					->add($oDiv_Group)
			);
	}

	/**
	* Get property list
	* @return object
	*/
	protected function _getProperties()
	{
		// Properties
		return $this->linkedObject->Properties;
	}

	protected $_aSortings = array();
	protected $_aSortingTree = array();

	protected function _setValue($oProperty_Value, $value)
	{
		!isset($this->_aSortings[$oProperty_Value->property_id])
			&& $this->_aSortings[$oProperty_Value->property_id] = 0;

		if ($oProperty_Value->id)
		{
			$sorting = $this->_aSortings[$oProperty_Value->property_id]++;
			if (isset($this->_aSortingTree[$oProperty_Value->property_id]))
			{
				$sorting = array_search($oProperty_Value->id, $this->_aSortingTree[$oProperty_Value->property_id]);
			}
		}
		else
		{
			$sorting = $this->_aSortings[$oProperty_Value->property_id]++;
		}

		$value = $this->_correctValue($oProperty_Value->Property, $value);

		$oProperty_Value
			->setValue($value)
			->sorting($sorting)
			->save();

		return $this;
	}

	/**
	* Apply object property
	* @hostcms-event Property_Controller_Tab.onBeforeApplyObjectProperty
	* @hostcms-event Property_Controller_Tab.onAfterApplyObjectProperty
	* @hostcms-event Property_Controller_Tab.onApplyObjectProperty
	*/
	public function applyObjectProperty()
	{
		$aProperties = $this->_getProperties()->findAll();

		Core_Event::notify('Property_Controller_Tab.onBeforeApplyObjectProperty', $this, array($this->_Admin_Form_Controller, $aProperties));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		foreach ($_POST as $key => $value)
		{
			if (strpos($key, 'property_') === 0)
			{
				$aTmp = explode('_', $key);
				if (count($aTmp) == 3)
				{
					$this->_aSortingTree[$aTmp[1]][] = $aTmp[2];
				}
			}
		}

		// Values already exist
		$aProperty_Values = $this->_object->getPropertyValues(FALSE);

		foreach ($aProperty_Values as $oProperty_Value)
		{
			$oProperty = $oProperty_Value->Property;

			switch ($oProperty->type)
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
					$value = Core_Array::getPost("property_{$oProperty->id}_{$oProperty_Value->id}");

					// 000227947
					if (!is_null($value))
					{
						$value === ''
							? $oProperty_Value->delete()
							: $this->_setValue($oProperty_Value, $value);
					}
				break;
				case 2: // File
					// Values already exist

					$aLargeFile = Core_Array::getFiles("property_{$oProperty->id}_{$oProperty_Value->id}");
					$aSmallFile = Core_Array::getFiles("small_property_{$oProperty->id}_{$oProperty_Value->id}");

					// ----
					$description = Core_Array::getPost("description_property_{$oProperty->id}_{$oProperty_Value->id}");
					if (!is_null($description))
					{
						$oProperty_Value->file_description = $description;
						$oProperty_Value->save();
					}

					$description_small = Core_Array::getPost("description_small_property_{$oProperty->id}_{$oProperty_Value->id}");

					if (!is_null($description_small))
					{
						$oProperty_Value->file_small_description = $description_small;
						$oProperty_Value->save();
					}
					// ----

					$this->_loadFiles($aLargeFile, $aSmallFile, $oProperty_Value, $oProperty, "property_{$oProperty->id}_{$oProperty_Value->id}");
				break;
			}
		}

		// New Values
		foreach ($aProperties as $oProperty)
		{
			// Values already exist
			//$aProperty_Values = $oProperty->getValues($this->_object->id, FALSE);

			switch ($oProperty->type)
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

					// New values of property
					$aNewValue = Core_Array::getPost("property_{$oProperty->id}", array());

					// Checkbox, значений раньше не было и не пришло новых значений
					if ($oProperty->type == 7 && count($aProperty_Values) == 0
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
								$oNewProperty_Value = $oProperty->createNewValue($this->_object->id);

								$this->_setValue($oNewProperty_Value, $newValue);

								ob_start();
								Core::factory('Core_Html_Entity_Script')
									->value("$(\"#{$windowId} *[name='property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'property_{$oProperty->id}_{$oNewProperty_Value->id}')")
									->execute();

								$this->_Admin_Form_Controller->addMessage(ob_get_clean());
							}
						}
					}
				break;

				case 2: // File
					// New values of property
					$aNewValueLarge = Core_Array::getFiles("property_{$oProperty->id}", array());
					$aNewValueSmall = Core_Array::getFiles("small_property_{$oProperty->id}", array());

					// New values of property
					if (is_array($aNewValueLarge) && isset($aNewValueLarge['name']))
					{
						$iCount = count($aNewValueLarge['name']);

						for ($i = 0; $i < $iCount; $i++)
						{
							$oFileValue = $oProperty->createNewValue($this->_object->id);

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
							$description = $this->_getEachPost("description_property_{$oProperty->id}");
							if (!is_null($description))
							{
								$oFileValue->file_description = $description;
							}

							$description_small = $this->_getEachPost("description_small_property_{$oProperty->id}");

							if (!is_null($description_small))
							{
								$oFileValue->file_small_description = $description_small;
							}
							// -------

							$oFileValue->save();

							$this->_loadFiles($aLargeFile, $aSmallFile, $oFileValue, $oProperty, "property_{$oProperty->id}");

							$this->_Admin_Form_Controller->addMessage(ob_get_clean());

							ob_start();
							Core::factory('Core_Html_Entity_Script')
								->value("$(\"#{$windowId} div[id^='file_large'] input[name='property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} div[id^='file_small'] input[name='small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'small_property_{$oProperty->id}_{$oFileValue->id}');" .
								// Description
								"$(\"#{$windowId} input[name='description_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'description_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='description_small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'description_small_property_{$oProperty->id}_{$oFileValue->id}');" .
								// Large
								"$(\"#{$windowId} input[name='large_max_width_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'large_max_width_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_max_height_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'large_max_height_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_preserve_aspect_ratio_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'large_preserve_aspect_ratio_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_place_watermark_checkbox_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'large_place_watermark_checkbox_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='watermark_position_x_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'watermark_position_x_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='watermark_position_y_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'watermark_position_y_property_{$oProperty->id}_{$oFileValue->id}');" .
								// Small
								"$(\"#{$windowId} input[name='small_max_width_small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'small_max_width_small_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='small_max_height_small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'small_max_height_small_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='small_preserve_aspect_ratio_small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'small_preserve_aspect_ratio_small_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='small_place_watermark_checkbox_small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'small_place_watermark_checkbox_small_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='create_small_image_from_large_small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'create_small_image_from_large_small_property_{$oProperty->id}_{$oFileValue->id}');"
								)
								->execute();

							$this->_Admin_Form_Controller->addMessage(ob_get_clean());
						}
					}
				break;

				default:
					/*throw new Core_Exception(
						Core::_('Property.type_does_not_exist'),
							array('%d' => $oProperty->type)
					);*/
					Core_Event::notify('Property_Controller_Tab.onApplyObjectProperty', $this, array($oProperty, $aProperty_Values));
			}
		}

		Core_Event::notify('Property_Controller_Tab.onAfterApplyObjectProperty', $this, array($this->_Admin_Form_Controller, $aProperties));
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
			//reset($this->_POST[$name]);
			//$val = current($this->_POST[$name]);
			$val = array_shift($this->_POST[$name]);

			return $val;
		}

		return $this->_POST[$name];
	}

	/**
	* Load files
	* @param array $aLargeFile large file data
	* @param array $aSmallFile small file data
	* @param Property_Value_File_Model $oFileValue value of file object
	* @param Property_Model $oProperty property
	* @param string $sPropertyName property name
	*/
	protected function _loadFiles($aLargeFile, $aSmallFile, $oFileValue, $oProperty, $sPropertyName)
	{
		$oFileValue->setDir(
			$this->linkedObject->getDirPath($this->_object)
		);

		$param = array();

		$aFileData = $aLargeFile;
		$aSmallFileData = $aSmallFile;

		$large_image = '';
		$small_image = '';

		$aCore_Config = Core::$mainConfig;

		$create_small_image_from_large = $this->_getEachPost("create_small_image_from_large_small_{$sPropertyName}");

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
				$large_image = !$this->linkedObject->changeFilename
					? $file_name
					: $this->linkedObject->getLargeFileName($this->_object, $oFileValue, $aFileData['name']);
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
				if (!$this->linkedObject->changeFilename)
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
					$small_image = $this->linkedObject
						->getSmallFileName($this->_object, $oFileValue, $aSmallFileData['name']);
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
				? $this->linkedObject->getDirPath($this->_object) . $large_image
				: '';

			// Путь к создаваемому файлу малого изображения;
			$param['small_image_target'] = !empty($small_image)
				? $this->linkedObject->getDirPath($this->_object) . $small_image
				: '';

			// Использовать большое изображение для создания малого
			$param['create_small_image_from_large'] = $create_small_image_from_large;

			// Значение максимальной ширины большого изображения
			$param['large_image_max_width'] = $this->_getEachPost("large_max_width_{$sPropertyName}");

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = $this->_getEachPost("large_max_height_{$sPropertyName}");

			// Значение максимальной ширины малого изображения;
			$param['small_image_max_width'] = $this->_getEachPost("small_max_width_small_{$sPropertyName}");

			// Значение максимальной высоты малого изображения;
			$param['small_image_max_height'] = $this->_getEachPost("small_max_height_small_{$sPropertyName}");

			// Путь к файлу с "водяным знаком"
			$param['watermark_file_path'] = $this->linkedObject->watermarkFilePath;

			// Позиция "водяного знака" по оси X
			$param['watermark_position_x'] = $this->_getEachPost("watermark_position_x_{$sPropertyName}");

			// Позиция "водяного знака" по оси Y
			$param['watermark_position_y'] = $this->_getEachPost("watermark_position_y_{$sPropertyName}");

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), FALSE - не наложить);
			$param['large_image_watermark'] = !is_null($this->_getEachPost("large_place_watermark_checkbox_{$sPropertyName}"));

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), FALSE - не наложить);
			$param['small_image_watermark'] = !is_null($this->_getEachPost("small_place_watermark_checkbox_small_{$sPropertyName}"));

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null($this->_getEachPost("large_preserve_aspect_ratio_{$sPropertyName}"));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = !is_null($this->_getEachPost("small_preserve_aspect_ratio_small_{$sPropertyName}"));

			$this->linkedObject->createPropertyDir($this->_object);

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
	* Correct save value by property type
	* @param Property $oProperty property
	* @param string $value value
	* @return string
	*/
	protected function _correctValue($oProperty, $value)
	{
		switch ($oProperty->type)
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
	* Correct print value by property type
	* @param Property $oProperty property
	* @param string $value value
	* @return string
	*/
	protected function _correctPrintValue($oProperty, $value)
	{
		switch ($oProperty->type)
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