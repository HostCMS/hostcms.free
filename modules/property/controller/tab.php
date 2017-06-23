<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Properties.
 *
 * @package HostCMS
 * @subpackage Property
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

	/**
	 * Show properties on tab
	 * @return self
	 */
	public function fillTab()
	{
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
		return "res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { $.deleteProperty(this, {path: '{$this->_Admin_Form_Controller->getPath()}', action: 'deletePropertyValue', datasetId: '{$this->_datasetId}', objectId: '{$this->_object->id}'}) } else {return false}";
	}

	/**
	 * List Options Cache
	 * @var array
	 */
	protected $_cacheListOptions = array();

	/**
	 * Add external properties container to $parentObject
	 * @param int $parent_id ID of parent directory of properties
	 * @param object $parentObject
	 * @hostcms-event Property_Controller_Tab.onBeforeAddFormEntity
	 * @hostcms-event Property_Controller_Tab.onAfterCreatePropertyListValues
	 * @hostcms-event Property_Controller_Tab.onSetPropertyType
	 */
	protected function _setPropertyDirs($parent_id = 0, $parentObject)
	{
		$oAdmin_Form_Entity_Section = Admin_Form_Entity::factory('Section')
			->caption($parent_id == 0
				? Core::_('Property_Dir.main_section')
				: htmlspecialchars(Core_Entity::factory('Property_Dir', $parent_id)->name)
			)
			->id('accordion_' . $parent_id);

		// Properties
		$oProperties = $this->_getProperties();
		$oProperties
			->queryBuilder()
			->where('property_dir_id', '=', $parent_id);

		$aProperties = $oProperties->findAll();
		foreach ($aProperties as $oProperty)
		{
			$aProperty_Values = $this->_object->id
				? $oProperty->getValues($this->_object->id, FALSE)
				: array();

			$oAdmin_Form_Entity = NULL;

			switch ($oProperty->type)
			{
				case 0: // Int
				case 1: // String
				case 2: // File
				case 3: // List
				case 4: // Textarea
				case 6: // Wysiwyg
				case 7: // Checkbox
				case 8: // Date
				case 9: // Datetime
				case 10: // Hidden field
				case 11: // Float

					$width = 410;

					switch ($oProperty->type)
					{
						case 0: // Int
							$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
								->format(array('lib' => array(
									'value' => 'integer'
								)));
						break;
						case 11: // Float
							$oAdmin_Form_Entity = Admin_Form_Entity::factory('Input')
								->format(array('lib' => array(
									'value' => 'decimal'
								)));
						break;
						case 1: // String
						case 10: // Hidden field
						default:
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

							if (method_exists($this->linkedObject, 'layWatermarOnLargeImage')
								&& method_exists($this->linkedObject, 'layWatermarOnSmallImage'))
							{
								$largeImage['place_watermark_checkbox_checked'] = $this->linkedObject->layWatermarOnLargeImage();
								$smallImage['place_watermark_checkbox_checked'] = $this->linkedObject->layWatermarOnSmallImage();
							}

							if (method_exists($this->linkedObject, 'preserveAspectRatioOfLargeImage')
								&& method_exists($this->linkedObject, 'preserveAspectRatioOfSmallImage'))
							{
								$largeImage['preserve_aspect_ratio_checkbox_checked'] = $this->linkedObject->preserveAspectRatioOfLargeImage();
								$smallImage['preserve_aspect_ratio_checkbox_checked'] = $this->linkedObject->preserveAspectRatioOfSmallImage();
							}

							$oAdmin_Form_Entity = Admin_Form_Entity::factory('File')
								->style('width: 340px')
								->largeImage($largeImage)
								->smallImage($smallImage);

							$width = 710;
						break;

						case 3: // List
							if (Core::moduleIsActive('list'))
							{
								if (!isset($this->_cacheListOptions[$oProperty->list_id]))
								{
									$this->_cacheListOptions[$oProperty->list_id] = array(' … ');

									$aListItems = $oProperty->List->List_Items->getAllByActive(1, FALSE);
									foreach ($aListItems as $oListItem)
									{
										$this->_cacheListOptions[$oProperty->list_id][$oListItem->id] = $oListItem->value;
									}
								}

								$oAdmin_Form_Entity = Admin_Form_Entity::factory('Select')
									->options($this->_cacheListOptions[$oProperty->list_id]);

								Core_Event::notify('Property_Controller_Tab.onAfterCreatePropertyListValues', $this, array($oProperty, $oAdmin_Form_Entity));

								//unset($aOptions);
							}
						break;

						case 4: // Textarea
							$oAdmin_Form_Entity = Admin_Form_Entity::factory('Textarea');
						break;

						case 6: // Wysiwyg
							$oAdmin_Form_Entity = Admin_Form_Entity::factory('Textarea')
								->wysiwyg(TRUE)
								->template_id($this->template_id);
						break;

						case 7: // Checkbox
							$oAdmin_Form_Entity = Admin_Form_Entity::factory('Checkbox');

							count($aProperty_Values) && $oAdmin_Form_Entity->postingUnchecked(TRUE);
						break;

						case 8: // Date
							$oAdmin_Form_Entity = Admin_Form_Entity::factory('Date');
						break;

						case 9: // Datetime
							$oAdmin_Form_Entity = Admin_Form_Entity::factory('Datetime');
						break;
					}

					if ($oAdmin_Form_Entity)
					{
						$oAdmin_Form_Entity->name("property_{$oProperty->id}[]")
							->caption(htmlspecialchars($oProperty->name))
							->value(
								$this->_correctPrintValue($oProperty, $oProperty->default_value)
							)
							->divAttr(array(
								'class' => ($oProperty->type != 2 ? 'form-group' : '') . (
									($oProperty->type == 7 || $oProperty->type == 8 || $oProperty->type == 9)
									? ' col-sm-7 col-md-6 col-lg-5'
									: ' col-sm-12')
							));

						//$oProperty->multiple && $oAdmin_Form_Entity->add($this->getImgAdd($oProperty));

						// Значений св-в нет для объекта
						if (count($aProperty_Values) == 0)
						{
							$oAdmin_Form_Entity_Section->add(
								Admin_Form_Entity::factory('Div')
									->class('row')
									->id("property_{$oProperty->id}")
									/*->divAttr(array(
										'id' => "property_{$oProperty->id}",
									))*/
									->add($oAdmin_Form_Entity)
							);

							$oProperty->multiple && $this->imgBox($oAdmin_Form_Entity, $oProperty);

							Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oAdmin_Form_Entity, $oAdmin_Form_Entity_Section, $oProperty));
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

									case 8: // Date
										$oNewAdmin_Form_Entity->value(
											//Core_Date::sql2date($oProperty_Value->value)
											$this->_correctPrintValue($oProperty, $oProperty_Value->value)
										);
									break;

									case 9: // Datetime
										$oNewAdmin_Form_Entity->value(
											//Core_Date::sql2datetime($oProperty_Value->value)
											$this->_correctPrintValue($oProperty, $oProperty_Value->value)
										);
									break;
								}

								$oNewAdmin_Form_Entity
									->name("property_{$oProperty->id}_{$oProperty_Value->id}")
									->id("property_{$oProperty->id}_{$oProperty_Value->id}");

								Core_Event::notify('Property_Controller_Tab.onBeforeAddFormEntity', $this, array($oNewAdmin_Form_Entity, $oAdmin_Form_Entity_Section, $oProperty));

								$oAdmin_Form_Entity_Section->add(
									Admin_Form_Entity::factory('Div')
										->class('row')
										->id("property_{$oProperty->id}")
										/*->divAttr(array(
											'id' => "property_{$oProperty->id}",
										))*/
										->add($oNewAdmin_Form_Entity)
								);

								// Визуальный редактор клонировать запрещено
								$oProperty->multiple && $oProperty->type != 6
									&& $this->imgBox($oNewAdmin_Form_Entity, $oProperty, '$.cloneProperty', $this->getImgDeletePath());
							}
						}
					}
				break;

				case 5: // ИС
					// Директории
					$oAdmin_Form_Entity_InfGroups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oProperty->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("group_{$oProperty->id}[]")
						->filter(TRUE);

					// Элементы
					$oAdmin_Form_Entity_InfItems = Admin_Form_Entity::factory('Select')
						->name("property_{$oProperty->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_InfItemsInput = Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_property_{$oProperty->id}[]")
						->name("input_property_{$oProperty->id}[]");

					// Значений св-в нет для объекта
					if (count($aProperty_Values) == 0)
					{
						$this->_fillInformationSystem($oProperty->default_value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_InfGroups, $oAdmin_Form_Entity_InfItems, $oAdmin_Form_Entity_InfItemsInput);
					}
					else
					{
						foreach ($aProperty_Values as $key => $oProperty_Value)
						{
							$value = $oProperty_Value->value;

							$oNewAdmin_Form_Entity_InfGroups = clone $oAdmin_Form_Entity_InfGroups;
							$oNewAdmin_Form_Entity_InfGroups
								->id("group_{$oProperty->id}_{$oProperty_Value->id}");

							$oNewAdmin_Form_Entity_InfItems = clone $oAdmin_Form_Entity_InfItems;
							$oNewAdmin_Form_Entity_InfItems
								->id("property_{$oProperty->id}_{$oProperty_Value->id}_{$key}")
								->name("property_{$oProperty->id}_{$oProperty_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_InfItemsInput = clone $oAdmin_Form_Entity_InfItemsInput;
							$oNewAdmin_Form_Entity_InfItemsInput
								->id("input_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}")
								->name("input_property_{$oProperty->id}_{$oProperty_Value->id}");

							$this->_fillInformationSystem($value, $oProperty, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_InfGroups, $oNewAdmin_Form_Entity_InfItems, $oNewAdmin_Form_Entity_InfItemsInput);
						}
					}

				break;

				case 12: // Интернет-магазин
					// Директории
					$oAdmin_Form_Entity_Shop_Groups = Admin_Form_Entity::factory('Select')
						->caption(htmlspecialchars($oProperty->name))
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("group_{$oProperty->id}[]")
						->filter(TRUE);

					// Элементы
					$oAdmin_Form_Entity_Shop_Items = Admin_Form_Entity::factory('Select')
						->name("property_{$oProperty->id}[]")
						->value(NULL)
						->divAttr(array('class' => 'form-group col-xs-12'))
						->filter(TRUE);

					$oAdmin_Form_Entity_Shop_Items_Input = Admin_Form_Entity::factory('Input')
						->divAttr(array('class' => 'form-group col-xs-12'))
						->id("input_property_{$oProperty->id}[]")
						->name("input_property_{$oProperty->id}[]");

					// Значений св-в нет для объекта
					if (count($aProperty_Values) == 0)
					{
						$this->_fillShop($oProperty->default_value, $oProperty, $oAdmin_Form_Entity_Section, $oAdmin_Form_Entity_Shop_Groups, $oAdmin_Form_Entity_Shop_Items, $oAdmin_Form_Entity_Shop_Items_Input);
					}
					else
					{
						foreach ($aProperty_Values as $key => $oProperty_Value)
						{
							$value = $oProperty_Value->value;

							$oNewAdmin_Form_Entity_Shop_Groups = clone $oAdmin_Form_Entity_Shop_Groups;

							$oNewAdmin_Form_Entity_Shop_Items = clone $oAdmin_Form_Entity_Shop_Items;
							$oNewAdmin_Form_Entity_Shop_Items
								->id("property_{$oProperty->id}_{$oProperty_Value->id}_{$key}")
								->name("property_{$oProperty->id}_{$oProperty_Value->id}")
								->value($value);

							$oNewAdmin_Form_Entity_Shop_Items_Input = clone $oAdmin_Form_Entity_Shop_Items_Input;
							$oNewAdmin_Form_Entity_Shop_Items_Input
								->id("input_property_{$oProperty->id}_{$oProperty_Value->id}_{$key}")
								->name("input_property_{$oProperty->id}_{$oProperty_Value->id}");

							$this->_fillShop($value, $oProperty, $oAdmin_Form_Entity_Section, $oNewAdmin_Form_Entity_Shop_Groups, $oNewAdmin_Form_Entity_Shop_Items, $oNewAdmin_Form_Entity_Shop_Items_Input);
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
		}

		// Property Dirs
		$oProperty_Dirs = $this->linkedObject->Property_Dirs;

		$oProperty_Dirs
			->queryBuilder()
			->where('parent_id', '=', $parent_id);

		$aProperty_Dirs = $oProperty_Dirs->findAll();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			$this->_setPropertyDirs($oProperty_Dir->id, $parent_id == 0 ? $this->_tab : $oAdmin_Form_Entity_Section);
		}

		$oAdmin_Form_Entity_Section->getCountChildren() && $parentObject->add($oAdmin_Form_Entity_Section);
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

		if ($iCountItems < Core::$mainConfig['switchSelectToAutocomplete'])
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
				$aOptions[$oInformationsystem_Item->id] = !$oInformationsystem_Item->shortcut_id
					? $oInformationsystem_Item->name
					: $oInformationsystem_Item->Informationsystem_Item->name;
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

		$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
		->type("text/javascript")
		->value("
			$('[id ^= input_property_{$oProperty->id}]').autocomplete({
				  source: function(request, response) {

					var jInput = $(this.element),
						jTopParentDiv = jInput.parents('[id ^= property]'),
						jInfGroupDiv = jTopParentDiv.find('[id ^= group_]'),
						selectedVal = $(':selected', jInfGroupDiv).val();

					$.ajax({
					  url: '/admin/informationsystem/item/index.php?autocomplete=1&informationsystem_id={$oInformationsystem->id}&informationsystem_group_id=' + selectedVal + '',
					  dataType: 'json',
					  data: {
						queryString: request.term
					  },
					  success: function( data ) {
						response( data );
					  }
					});
				  },
				  minLength: 1,
				  create: function() {
					$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
						return $('<li></li>')
							.data('item.autocomplete', item)
							.append($('<a>').text(item.label))
							.appendTo(ul);
					}

					 $(this).prev('.ui-helper-hidden-accessible').remove();
				  },
				  select: function( event, ui ) {
					var jInput = $(this),
						jTopParentDiv = jInput.parents('[id ^= property]'),
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
		");

		$oDiv_Group = Admin_Form_Entity::factory('Div')
			->class('input-group')
			->add($oAdmin_Form_Entity_InfGroups)
			->add($oAdmin_Form_Entity_InfItemsSelect)
			->add($oAdmin_Form_Entity_InfItemsInput)
			->add($oCore_Html_Entity_Script);

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
			->select('id', 'shortcut_id', 'modification_id', 'name');

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
			->onchange("$.ajaxRequest({path: '/admin/shop/item/index.php', context: '{$oAdmin_Form_Entity_Shop_Items->id}', callBack: $.loadSelectOptionsCallback, action: 'loadShopItemList',additionalParams: 'shop_group_id=' + this.value + '&shop_id={$oProperty->shop_id}',windowId: '{$windowId}'}); return false");

		// Items
		$oShop_Items = $oShop->Shop_Items;

		$oShop_Items
			->queryBuilder()
			->clearOrderBy()
			->where('shop_items.shop_group_id', '=', $group_id)
			->where('shop_items.modification_id', '=', 0);

		$iCountItems = $oShop_Items->getCount();

		if ($iCountItems < Core::$mainConfig['switchSelectToAutocomplete'])
		{
			$aShop_Items = self::getShopItems($oShop_Items);

			$aConfig = Core_Config::instance()->get('property_config', array()) + array(
				'select_modifications' => TRUE,
			);

			$aOptions = array(' … ');
			foreach ($aShop_Items as $oShop_Item)
			{
				$aOptions[$oShop_Item->id] = !$oShop_Item->shortcut_id
					? $oShop_Item->name
					: $oShop_Item->Shop_Item->name;

				// Shop Item's modifications
				if ($aConfig['select_modifications'])
				{
					$oModifications = $oShop_Item->Modifications;

					$oModifications
						->queryBuilder()
						->clearOrderBy()
						->clearSelect()
						->select('id', 'shortcut_id', 'name');

					$aModifications = $oModifications->findAll(FALSE);

					foreach ($aModifications as $oModification)
					{
						$aOptions[$oModification->id] = ' — ' . $oModification->name;
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

		$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
		->type("text/javascript")
		->value("
			$('[id ^= input_property_{$oProperty->id}]').autocomplete({
				  source: function(request, response) {

					var jInput = $(this.element),
						jTopParentDiv = jInput.parents('[id ^= property]'),
						jInfGroupDiv = jTopParentDiv.find('[id ^= group_]'),
						selectedVal = $(':selected', jInfGroupDiv).val();

					$.ajax({
					  url: '/admin/shop/item/index.php?autocomplete=1&shop_id={$oShop->id}&shop_group_id=' + selectedVal + '',
					  dataType: 'json',
					  data: {
						queryString: request.term
					  },
					  success: function( data ) {
						response( data );
					  }
					});
				  },
				  minLength: 1,
				  create: function() {
					$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
						return $('<li></li>')
							.data('item.autocomplete', item)
							.append($('<a>').text(item.label))
							.appendTo(ul);
					}

					 $(this).prev('.ui-helper-hidden-accessible').remove();
				  },
				  select: function( event, ui ) {
					var jInput = $(this),
						jTopParentDiv = jInput.parents('[id ^= property]'),
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
		");

		$oDiv_Group = Admin_Form_Entity::factory('Div')
			->class('input-group')
			->add($oAdmin_Form_Entity_Shop_Groups)
			->add($oAdmin_Form_Entity_Shop_Items)
			->add($oAdmin_Form_Entity_Shop_Items_Input)
			->add($oCore_Html_Entity_Script);

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
	 * @return array
	 */
	protected function _getProperties()
	{
		// Properties
		return $this->linkedObject->Properties;
	}

	/**
	 * Apply object property
	 * @hostcms-event Property_Controller_Tab.onApplyObjectProperty
	 */
	public function applyObjectProperty()
	{
		$aProperties = $this->_getProperties()->findAll();

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		foreach ($aProperties as $oProperty)
		{
			// Values already exist
			$aProperty_Values = $oProperty->getValues($this->_object->id);

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

					// Values already exist
					foreach ($aProperty_Values as $oProperty_Value)
					{
						$value = Core_Array::getPost("property_{$oProperty->id}_{$oProperty_Value->id}");

						// 000227947
						if (!is_null($value))
						{
							$value = $this->_correctValue($oProperty, $value);

							$oProperty_Value
								->setValue($value)
								->save();
						}
					}

					// New values of property
					$aNewValue = Core_Array::getPost("property_{$oProperty->id}", array());

					// Checkbox, значений раньше не было и не пришло новых значений
					if ($oProperty->type == 7 && count($aProperty_Values) == 0
						&& is_array($aNewValue) && !count($aNewValue))
					{
						$aNewValue = array(0);
					}

					// New values of property
					if (is_array($aNewValue))
					{
						foreach ($aNewValue as $newValue)
						{
							$oNewValue = $oProperty->createNewValue($this->_object->id);

							$newValue = $this->_correctValue($oProperty, $newValue);

							$oNewValue
								->setValue($newValue)
								->save();

							ob_start();
							Core::factory('Core_Html_Entity_Script')
								->type("text/javascript")
								->value("$(\"#{$windowId} *[name='property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'property_{$oProperty->id}_{$oNewValue->id}')")
								->execute();

							$this->_Admin_Form_Controller->addMessage(ob_get_clean());
						}
					}

				break;

				case 2: // File

					// Values already exist
					foreach ($aProperty_Values as $oFileValue)
					{
						$aLargeFile = Core_Array::getFiles("property_{$oProperty->id}_{$oFileValue->id}");
						$aSmallFile = Core_Array::getFiles("small_property_{$oProperty->id}_{$oFileValue->id}");

						// ----
						$description = Core_Array::getPost("description_property_{$oProperty->id}_{$oFileValue->id}");
						if (!is_null($description))
						{
							$oFileValue->file_description = $description;
							$oFileValue->save();
						}

						$description_small = Core_Array::getPost("description_small_property_{$oProperty->id}_{$oFileValue->id}");

						if (!is_null($description_small))
						{
							$oFileValue->file_small_description = $description_small;
							$oFileValue->save();
						}
						// ----

						$this->_loadFiles($aLargeFile, $aSmallFile, $oFileValue, $oProperty, "property_{$oProperty->id}_{$oFileValue->id}");
					}

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
								->type("text/javascript")
								->value("$(\"#{$windowId} div[id^='file_large'] input[name='property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} div[id^='file_small'] input[name='small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'small_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='description_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'description_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='description_small_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'description_small_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_max_width_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'large_max_width_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_max_height_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'large_max_height_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_preserve_aspect_ratio_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'large_preserve_aspect_ratio_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='large_place_watermark_checkbox_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'large_place_watermark_checkbox_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='watermark_position_x_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'watermark_position_x_property_{$oProperty->id}_{$oFileValue->id}');" .
								"$(\"#{$windowId} input[name='watermark_position_y_property_{$oProperty->id}\\[\\]']\").eq(0).attr('name', 'watermark_position_y_property_{$oProperty->id}_{$oFileValue->id}');"
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
			list(, $val) = each($this->_POST[$name]);
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
					// Существует ли большое изображение
					$param['large_image_isset'] = TRUE;
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
			$param['large_image_max_width'] = $this->_getEachPost("large_max_width_{$sPropertyName}", 0);

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = $this->_getEachPost("large_max_height_{$sPropertyName}", 0);

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
			default:
				$value = htmlspecialchars($value);
		}
		return $value;
	}
}