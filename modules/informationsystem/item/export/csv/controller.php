<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Information systems export CSV controller
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Informationsystem_Item_Export_Csv_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'guidItemPosition',
		'guidGroupPosition',
		'separator',
		'encoding',
		'parentGroup',
		'exportItemExternalProperties',
		'exportGroupExternalProperties',
		'exportItemFields',
		'exportGroupFields',
		'informationsystemId'
	);

	/**
	 * Additional properties of items
	 * Дополнительные свойства элементов
	 * @var array
	 */
	private $_aItem_Properties = array();

	/**
	 * Additional properties of item groups
	 * Дополнительные свойства групп элементов
	 * @var array
	 */
	private $_aGroup_Properties = array();

	/**
	 * Additional properties of items
	 * Дополнительные свойства элементов
	 * @var array
	 */
	private $_aItem_Fields = array();

	/**
	 * Additional properties of item groups
	 * Дополнительные свойства групп элементов
	 * @var array
	 */
	private $_aGroup_Fields = array();

	/**
	 * Item properties count
	 * Требуется хранить количество свойств отдельно, т.к. количество полей файла CSV для свойств не равно количеству свойств (из-за файлов)
	 * @var int
	 */
	private $_iItem_Properties_Count;

	/**
	 * Group properties count
	 * @var int
	 */
	private $_iGroup_Properties_Count;

	/**
	 * Item properties count
	 * Требуется хранить количество свойств отдельно, т.к. количество полей файла CSV для свойств не равно количеству свойств (из-за файлов)
	 * @var int
	 */
	private $_iItem_Fields_Count;

	/**
	 * Group properties count
	 * @var int
	 */
	private $_iGroup_Fields_Count;

	/**
	 * Base properties of items
	 * Основные свойства элементов
	 * @var array
	 */
	private $_aItemBaseProperties;

	/**
	 * Base properties of item groups
	 * Основные свойства групп элементов
	 * @var array
	 */
	private $_aGroupBaseProperties;

	/**
	 * CSV data
	 * @var array
	 */
	private $_aCurrentData;

	/**
	 * Data pointer
	 * @var int
	 */
	private $_iCurrentDataPosition;

	/**
	 * Кэш значений доп. св-в
	 * @var array
	 */
	protected $_cachePropertyValues = array();

	/**
	 * Кэш значений доп. св-в
	 * @var array
	 */
	protected $_cacheFieldValues = array();

	/**
	 * Constructor.
	 * @param int $iInformationsystemId informationsystem ID
	 * @param boolean $bItemPropertiesExport export item properties mode
	 * @param boolean $bGroupPropertiesExport export group properties mode
	 */
	public function __construct($iInformationsystemId, $bItemPropertiesExport = TRUE, $bGroupPropertiesExport = TRUE, $bItemFieldsExport = TRUE, $bGroupFieldsExport = TRUE)
	{
		parent::__construct();

		$this->informationsystemId = $iInformationsystemId;
		$this->exportItemExternalProperties = $bItemPropertiesExport;
		$this->exportGroupExternalProperties = $bGroupPropertiesExport;
		$this->exportItemFields = $bItemFieldsExport;
		$this->exportGroupFields = $bGroupFieldsExport;
		$this->_iItem_Properties_Count = $this->_iGroup_Properties_Count = $this->_iItem_Fields_Count = $this->_iGroup_Fields_Count = 0;

		$oInformationsystem = Core_Entity::factory('Informationsystem', $this->informationsystemId);

		// Устанавливаем лимит времени выполнения в 1 час
		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			if (Core::isFunctionEnable('set_time_limit') && ini_get('safe_mode') != 1 && ini_get('max_execution_time') < 3600)
			{
				@set_time_limit(3600);
			}
		}	

		// Заполняем дополнительные свойства элемента
		$this->exportItemExternalProperties
			&& $this->_aItem_Properties = Core_Entity::factory('Informationsystem_Item_Property_List', $this->informationsystemId)->Properties->findAll(FALSE);

		// Заполняем дополнительные свойства групп элементов
		$this->exportGroupExternalProperties
			&& $this->_aGroup_Properties = Core_Entity::factory('Informationsystem_Group_Property_List', $this->informationsystemId)->Properties->findAll(FALSE);

		// Заполняем пользовательские поля элементов
		$this->exportItemFields
			&& $this->_aItem_Fields = Field_Controller::getFields('informationsystem_item', $oInformationsystem->site_id);

		// Заполняем пользовательские поля групп элементов
		$this->exportGroupFields
			&& $this->_aGroup_Fields = Field_Controller::getFields('informationsystem_group', $oInformationsystem->site_id);

		$this->_iCurrentDataPosition = 0;

		$aGroupTitles = array_map(array($this, 'prepareCell'), $this->getGroupTitles());

		$this->_aCurrentData[$this->_iCurrentDataPosition] = $aGroupTitles;

		// Название раздела - Порядок сортировки раздела
		$this->_aGroupBaseProperties = array_pad(array(), count($aGroupTitles), '');

		// Добавляем в заголовок информацию о свойствах группы элементов
		foreach ($this->_aGroup_Properties as $oGroup_Property)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell($oGroup_Property->name);
			$this->_iGroup_Properties_Count++;

			if ($oGroup_Property->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Informationsystem_Item.import_file_description', $oGroup_Property->name));
				$this->_iGroup_Properties_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Informationsystem_Item.import_small_images', $oGroup_Property->name));
				$this->_iGroup_Properties_Count++;
			}
		}

		foreach ($this->_aGroup_Fields as $oField)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell($oField->name);
			$this->_iGroup_Fields_Count++;

			if ($oField->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Informationsystem_Item.import_file_description', $oField->name));
				$this->_iGroup_Fields_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Informationsystem_Item.import_small_images', $oField->name));
				$this->_iGroup_Fields_Count++;
			}
		}

		$this->guidItemPosition = count($aGroupTitles) + $this->_iGroup_Properties_Count + $this->_iGroup_Fields_Count;

		$aItemTitles = array_map(array($this, 'prepareCell'), $this->getItemTitles());

		// CML ID идентификатор элемента - Ярлыки
		$this->_aItemBaseProperties = array_pad(array(), count($aItemTitles), '');

		// 0-вая строка - заголовок CSV-файла
		$this->_aCurrentData[$this->_iCurrentDataPosition] = array_merge(
			$this->_aCurrentData[$this->_iCurrentDataPosition],
			$aItemTitles
		);

		// Добавляем в заголовок информацию о свойствах элементов
		foreach ($this->_aItem_Properties as $oItem_Property)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell($oItem_Property->name);
			$this->_iItem_Properties_Count++;

			if ($oItem_Property->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Informationsystem_Item.import_file_description', $oItem_Property->name));
				$this->_iItem_Properties_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Informationsystem_Item.import_small_images', $oItem_Property->name));
				$this->_iItem_Properties_Count++;
			}
		}

		foreach ($this->_aItem_Fields as $oField)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell($oField->name);
			$this->_iItem_Fields_Count++;

			if ($oField->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Informationsystem_Item.import_file_description', $oField->name));
				$this->_iItem_Fields_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = $this->prepareCell(Core::_('Informationsystem_Item.import_small_images', $oField->name));
				$this->_iItem_Fields_Count++;
			}
		}
	}

	/**
	 * Get Group Titles
	 * @return array
	 * @hostcms-event Informationsystem_Item_Export_Csv_Controller.onGetGroupTitles
	 */
	public function getGroupTitles()
	{
		$return = array(
			Core::_('Informationsystem_Exchange.group_name'),
			Core::_('Informationsystem_Exchange.group_guid'),
			Core::_('Informationsystem_Exchange.group_parent_guid'),
			Core::_('Informationsystem_Exchange.group_seo_title'),
			Core::_('Informationsystem_Exchange.group_seo_description'),
			Core::_('Informationsystem_Exchange.group_seo_keywords'),
			Core::_('Informationsystem_Exchange.group_description'),
			Core::_('Informationsystem_Exchange.group_path'),
			Core::_('Informationsystem_Exchange.group_image_large'),
			Core::_('Informationsystem_Exchange.group_image_small'),
			Core::_('Informationsystem_Exchange.group_sorting'),

			Core::_('Informationsystem_Exchange.group_seo_group_title_template'),
			Core::_('Informationsystem_Exchange.group_seo_group_keywords_template'),
			Core::_('Informationsystem_Exchange.group_seo_group_description_template'),

			Core::_('Informationsystem_Exchange.group_seo_item_title_template'),
			Core::_('Informationsystem_Exchange.group_seo_item_keywords_template'),
			Core::_('Informationsystem_Exchange.group_seo_item_description_template')
		);

		$this->guidGroupPosition = 1;

		Core_Event::notify(get_class($this) . '.onGetGroupTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Get Item Titles
	 * @return array
	 * @hostcms-event Informationsystem_Item_Export_Csv_Controller.onGetItemTitles
	 */
	public function getItemTitles()
	{
		$return = array(
			Core::_('Informationsystem_Exchange.item_guid'),
			Core::_('Informationsystem_Exchange.item_path'),
			Core::_('Informationsystem_Exchange.item_name'),
			Core::_('Informationsystem_Exchange.item_description'),
			Core::_('Informationsystem_Exchange.item_text'),
			Core::_('Informationsystem_Exchange.item_tags'),
			Core::_('Informationsystem_Exchange.item_active'),
			Core::_('Informationsystem_Exchange.item_sorting'),
			Core::_('Informationsystem_Exchange.item_seo_title'),
			Core::_('Informationsystem_Exchange.item_seo_description'),
			Core::_('Informationsystem_Exchange.item_seo_keywords'),
			Core::_('Informationsystem_Exchange.item_indexing'),
			Core::_('Informationsystem_Exchange.item_datetime'),
			Core::_('Informationsystem_Exchange.item_start_datetime'),
			Core::_('Informationsystem_Exchange.item_end_datetime'),
			Core::_('Informationsystem_Exchange.item_image_large'),
			Core::_('Informationsystem_Exchange.item_image_small'),
			Core::_('Informationsystem_Exchange.item_additional_group'),
			Core::_('Informationsystem_Exchange.item_siteuser_id')
		);
		Core_Event::notify(get_class($this) . '.onGetItemTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Get item data
	 * @param object $oInformationsystem_Item item
	 * @return array
	 * @hostcms-event Informationsystem_Item_Export_Csv_Controller.onAfterGetItemData
	 */
	public function getItemData($oInformationsystem_Item)
	{
		$aGroupData = $this->_aGroupBaseProperties;

		$oInformationsystem_Group = $oInformationsystem_Item->informationsystem_group_id
			? Core_Entity::factory('Shop_Group', $oInformationsystem_Item->informationsystem_group_id)
			: NULL;

		//!is_null($oInformationsystem_Group) && $aGroupData[0] = $oInformationsystem_Group->name;

		$aGroupData[1] = is_null($oInformationsystem_Group)
			? 'ID00000000'
			: $oInformationsystem_Group->guid;

		$result = array_merge(
			$aGroupData,
			array_pad(array(), $this->_iGroup_Properties_Count, ''),
			array_pad(array(), $this->_iGroup_Fields_Count, ''),
			$this->getItemBasicData($oInformationsystem_Item),
			$this->getPropertiesData($this->_aItem_Properties, $oInformationsystem_Item),
			$this->getFieldsData($this->_aItem_Fields, $oInformationsystem_Item)
		);

		Core_Event::notify(get_class($this) . '.onAfterGetItemData', $this, array($result, $oInformationsystem_Item));

		if (!is_null(Core_Event::getLastReturn()))
		{
			$result = Core_Event::getLastReturn();
		}

		return $result;
	}

	/**
	 * Get Basic Item Data
	 * @param object $oInformationsystem_Item
	 * @return array
	 * @hostcms-event Informationsystem_Item_Export_Csv_Controller.onAfterItemBasicData
	 */
	public function getItemBasicData($oInformationsystem_Item)
	{
		// Метки
		if (Core::moduleIsActive('tag'))
		{
			$aTmpTags = array();

			$aTags = $oInformationsystem_Item->Tags->findAll(FALSE);
			foreach ($aTags as $oTag)
			{
				$aTmpTags[] = $oTag->name;
			}

			$sTags = $this->prepareString(implode(',', $aTmpTags));
			unset($aTags);
			unset($aTmpTags);
		}
		else
		{
			$sTags = '';
		}

		// Ярлыки
		$aTmpShortcuts = array();
		$aShortcuts = $oInformationsystem_Item->Informationsystem_Items->findAll(FALSE);
		foreach ($aShortcuts as $oShortcut_Item)
		{
			$aTmpShortcuts[] = $oShortcut_Item->informationsystem_group_id
					? $oShortcut_Item->Informationsystem_Group->guid
					: 0;
			$oShortcut_Item->clear();
		}
		unset($aShortcuts);

		$result = array(
			$this->prepareCell($oInformationsystem_Item->guid),
			$this->prepareCell($oInformationsystem_Item->path),
			$this->prepareCell($oInformationsystem_Item->name),
			$this->prepareCell($oInformationsystem_Item->description),
			$this->prepareCell($oInformationsystem_Item->text),
			sprintf('"%s"', $sTags),
			$oInformationsystem_Item->active,
			$oInformationsystem_Item->sorting,
			$this->prepareCell($oInformationsystem_Item->seo_title),
			$this->prepareCell($oInformationsystem_Item->seo_description),
			$this->prepareCell($oInformationsystem_Item->seo_keywords),
			$this->prepareCell($oInformationsystem_Item->indexing),
			$oInformationsystem_Item->datetime == '0000-00-00 00:00:00'
				? '0000-00-00 00:00:00'
				: Core_Date::sql2datetime($oInformationsystem_Item->datetime),
			$oInformationsystem_Item->start_datetime == '0000-00-00 00:00:00'
				? '0000-00-00 00:00:00'
				: Core_Date::sql2datetime($oInformationsystem_Item->start_datetime),
			$oInformationsystem_Item->end_datetime == '0000-00-00 00:00:00'
				? '0000-00-00 00:00:00'
				: Core_Date::sql2datetime($oInformationsystem_Item->end_datetime),
			$this->prepareCell($oInformationsystem_Item->image_large == '' ? '' : $oInformationsystem_Item->getLargeFileHref()),
			$this->prepareCell($oInformationsystem_Item->image_small == '' ? '' : $oInformationsystem_Item->getSmallFileHref()),
			$this->prepareCell(implode(',', $aTmpShortcuts)),
			$oInformationsystem_Item->siteuser_id
		);

		Core_Event::notify(get_class($this) . '.onAfterItemBasicData', $this, array($result, $oInformationsystem_Item));

		if (!is_null(Core_Event::getLastReturn()))
		{
			$result = Core_Event::getLastReturn();
		}

		return $result;
	}

	/**
	 * Get block of Item/Group Property values
	 * @param array $aProperties
	 * @param object $object
	 * @return array
	 */
	public function getPropertiesData(array $aProperties, $object)
	{
		$aRow = array();

		foreach ($aProperties as $oProperty)
		{
			$oProperty_Value = isset($this->_cachePropertyValues[$object->id][$oProperty->id]) && is_array($this->_cachePropertyValues[$object->id][$oProperty->id])
				? array_shift($this->_cachePropertyValues[$object->id][$oProperty->id])
				: NULL;

			$aRow[] = $this->prepareCell(
				$oProperty_Value
					? $this->_getPropertyValue($oProperty, $oProperty_Value, $object)
					: ''
			);

			if ($oProperty->type == 2)
			{
				$aRow[] = $oProperty_Value
					? $this->prepareCell($oProperty_Value->file_description)
					: '';

				$aRow[] = $oProperty_Value
					? ($oProperty_Value->file_small == ''
						? ''
						: $this->prepareCell($oProperty_Value->getSmallFileHref())
					)
					: '';
			}

			$oProperty_Value && $oProperty_Value->clear();

			// Удаляем пустой массив для свойств, чтобы определить, что значения закончились
			if (isset($this->_cachePropertyValues[$object->id][$oProperty->id]) && !count($this->_cachePropertyValues[$object->id][$oProperty->id]))
			{
				unset($this->_cachePropertyValues[$object->id][$oProperty->id]);
			}
		}

		return $aRow;
	}

    /**
     * Get block of Item/Group Property values
     * @param array $aFields
     * @param object $object
     * @return array
     */
	public function getFieldsData(array $aFields, $object)
	{
		$aRow = array();

		foreach ($aFields as $oField)
		{
			$oField_Value = isset($this->_cacheFieldValues[$object->id][$oField->id]) && is_array($this->_cacheFieldValues[$object->id][$oField->id])
				? array_shift($this->_cacheFieldValues[$object->id][$oField->id])
				: NULL;

			$aRow[] = $this->prepareCell(
				$oField_Value
					? $this->_getFieldValue($oField, $oField_Value, $object)
					: ''
			);

			if ($oField->type == 2)
			{
				$aRow[] = $oField_Value
					? $this->prepareCell($oField_Value->file_description)
					: '';

				$aRow[] = $oField_Value
					? ($oField_Value->file_small == ''
						? ''
						: $this->prepareCell($oField_Value->getSmallFileHref())
					)
					: '';
			}

			$oField_Value && $oField_Value->clear();

			// Удаляем пустой массив для свойств, чтобы определить, что значения закончились
			if (isset($this->_cacheFieldValues[$object->id][$oField->id]) && !count($this->_cacheFieldValues[$object->id][$oField->id]))
			{
				unset($this->_cacheFieldValues[$object->id][$oField->id]);
			}
		}

		return $aRow;
	}

	protected $_cacheGetListValue = array();

	protected function _getListValue($list_item_id)
	{
		return $list_item_id && Core::moduleIsActive('list')
			? (isset($this->_cacheGetListValue[$list_item_id])
				? $this->_cacheGetListValue[$list_item_id]
				: $this->_cacheGetListValue[$list_item_id] = Core_Entity::factory('List_Item', $list_item_id)->value
			)
			: '';
	}

	/**
	 * Get value of Property_Value
	 * @param Property_Model $oProperty
	 * @param mixed $oProperty_Value
	 * @param mixed $object
	 * @return string
	 * @hostcms-event Informationsystem_Item_Export_Csv_Controller.onGetPropertyValueDefault
	 */
	protected function _getPropertyValue($oProperty, $oProperty_Value, $object)
	{
		switch ($oProperty->type)
		{
			case 0: // Int
			case 1: // String
			case 4: // Textarea
			case 6: // Wysiwyg
			case 7: // Checkbox
			case 10: // Hidden field
			case 11: // Float
				$result = $oProperty_Value->value;
			break;
			case 2: // File
				$href = method_exists($object, 'getItemHref')
					? $object->getItemHref()
					: $object->getGroupHref();

				$result = $oProperty_Value->file == ''
					? ''
					: $oProperty_Value
						->setHref($href)
						->getLargeFileHref();
			break;
			case 3: // List
				$result = $this->_getListValue($oProperty_Value->value);
			break;
			case 5: // Informationsystem
				$result = $oProperty_Value->value
					? $oProperty_Value->Informationsystem_Item->name
					: '';
			break;
			case 8: // Date
				$result = Core_Date::sql2date($oProperty_Value->value);
			break;
			case 9: // Datetime
				$result = Core_Date::sql2datetime($oProperty_Value->value);
			break;
			case 12: // Shop
				$result = $oProperty_Value->value
					? $oProperty_Value->Shop_Item->name
					: '';
			break;
			default:
				$result = $oProperty_Value->value;

				Core_Event::notify(get_class($this) . '.onGetPropertyValueDefault', $this, array($oProperty, $oProperty_Value, $object));

				if (!is_null(Core_Event::getLastReturn()))
				{
					$result = Core_Event::getLastReturn();
				}
		}

		return $result;
	}

	/**
	 * Get value of Property_Value
	 * @param Field_Model $oField
	 * @param mixed $oField_Value
	 * @param mixed $object
	 * @return string
	 * @hostcms-event Informationsystem_Item_Export_Csv_Controller.onGetFieldValueDefault
	 */
	protected function _getFieldValue($oField, $oField_Value, $object)
	{
		switch ($oField->type)
		{
			case 0: // Int
			case 1: // String
			case 4: // Textarea
			case 6: // Wysiwyg
			case 7: // Checkbox
			case 10: // Hidden field
			case 11: // Float
				$result = $oField_Value->value;
			break;
			case 2: // File
				$href = method_exists($object, 'getItemHref')
					? $object->getItemHref()
					: $object->getGroupHref();

				$result = $oField_Value->file == ''
					? ''
					: $oField_Value
						->setHref($href)
						->getLargeFileHref();
			break;
			case 3: // List
				$result = $this->_getListValue($oField_Value->value);
			break;
			case 5: // Informationsystem
				$result = $oField_Value->value
					? $oField_Value->Informationsystem_Item->name
					: '';
			break;
			case 8: // Date
				$result = Core_Date::sql2date($oField_Value->value);
			break;
			case 9: // Datetime
				$result = Core_Date::sql2datetime($oField_Value->value);
			break;
			case 12: // Shop
				$result = $oField_Value->value
					? $oField_Value->Shop_Item->name
					: '';
			break;
			default:
				$result = $oField_Value->value;

				Core_Event::notify(get_class($this) . '.onGetFieldValueDefault', $this, array($oField, $oField_Value, $object));

				if (!is_null(Core_Event::getLastReturn()))
				{
					$result = Core_Event::getLastReturn();
				}
		}

		return $result;
	}

	/**
	 * Array of titile line
	 * @var array
	 */
	protected $_aCurrentRow = array();

	/**
	 * Get Current Row
	 * @return array
	 */
	public function getCurrentRow()
	{
		return $this->_aCurrentRow;
	}

	/**
	 * Set Current Row
	 * @param array $array
	 * @return self
	 */
	public function setCurrentRow(array $array)
	{
		$this->_aCurrentRow = $array;
		return $this;
	}

	/**
	 * Get Basic Group Data
	 * @param object $oInformationsystem_Group
	 * @return array
	 * @hostcms-event Informationsystem_Item_Export_Csv_Controller.onAfterGroupBasicData
	 */
	public function getGroupBasicData($oInformationsystem_Group)
	{
		$result = array(
			$this->prepareCell($oInformationsystem_Group->name),
			$this->prepareCell($oInformationsystem_Group->guid),
			$this->prepareCell(is_null($oInformationsystem_Group->Informationsystem_Group->id) ? 'ID00000000' : $oInformationsystem_Group->Informationsystem_Group->guid),
			$this->prepareCell($oInformationsystem_Group->seo_title),
			$this->prepareCell($oInformationsystem_Group->seo_description),
			$this->prepareCell($oInformationsystem_Group->seo_keywords),
			$this->prepareCell($oInformationsystem_Group->description),
			$this->prepareCell($oInformationsystem_Group->path),
			$this->prepareCell($oInformationsystem_Group->image_large == '' ? '' : $oInformationsystem_Group->getLargeFileHref()),
			$this->prepareCell($oInformationsystem_Group->image_small == '' ? '' : $oInformationsystem_Group->getSmallFileHref()),
			$this->prepareCell($oInformationsystem_Group->sorting),

			$this->prepareCell($oInformationsystem_Group->seo_group_title_template),
			$this->prepareCell($oInformationsystem_Group->seo_group_keywords_template),
			$this->prepareCell($oInformationsystem_Group->seo_group_description_template),

			$this->prepareCell($oInformationsystem_Group->seo_item_title_template),
			$this->prepareCell($oInformationsystem_Group->seo_item_keywords_template),
			$this->prepareCell($oInformationsystem_Group->seo_item_description_template)
		);

		Core_Event::notify(get_class($this) . '.onAfterGroupBasicData', $this, array($result, $oInformationsystem_Group));

		if (!is_null(Core_Event::getLastReturn()))
		{
			$result = Core_Event::getLastReturn();
		}

		return $result;
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$oUser = Core_Auth::getCurrentUser();
		if (!$oUser->superuser && $oUser->only_access_my_own)
		{
			return FALSE;
		}

		$sFilename = 'Informationsystem_' . $this->informationsystemId . '_' . date("Y_m_d_H_i_s") . '.csv';

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename = " . $sFilename . ";");
		header("Content-Transfer-Encoding: binary");

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('Begin CSV export ' . $sFilename);

		$oInformationsystem = Core_Entity::factory('Informationsystem', $this->informationsystemId);

		foreach ($this->_aCurrentData as $aData)
		{
			$this->_printRow($aData);
		}
		$this->_aCurrentData = array();

		if ($this->parentGroup == 0)
		{
			$oInformationsystem_Groups = $oInformationsystem->Informationsystem_Groups;
			$oInformationsystem_Groups->queryBuilder()
				->where('parent_id', '=', 0);
		}
		else
		{
			$oInformationsystem_Groups = Core_Entity::factory('Informationsystem_Group', $this->parentGroup)->Informationsystem_Groups;
		}

		$oInformationsystem_Groups->queryBuilder()
			->where('shortcut_id', '=', 0);

		$aInformationsystemGroupsId = array_merge(array($this->parentGroup), $oInformationsystem_Groups->getGroupChildrenId(FALSE));

		foreach ($aInformationsystemGroupsId as $iInformationsystemGroupId)
		{
			$aTmpArray = array();

			$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $iInformationsystemGroupId);

			$oInformationsystem_Items = $oInformationsystem_Group->Informationsystem_Items;
			$oInformationsystem_Items->queryBuilder()
				->where('informationsystem_items.informationsystem_id', '=', $this->informationsystemId)
				->where('informationsystem_items.shortcut_id', '=', 0)
				->clearOrderBy()
				->orderBy('informationsystem_items.id', 'ASC');

			if ($iInformationsystemGroupId != 0)
			{
				// Кэш всех значений свойств группы
				$this->_cachePropertyValues[$oInformationsystem_Group->id] = array();
				foreach ($this->_aGroup_Properties as $oProperty)
				{
					$this->_cachePropertyValues[$oInformationsystem_Group->id][$oProperty->id] = $oProperty->getValues($oInformationsystem_Group->id, FALSE);
				}

				// Кэш всех значений полей группы
				$this->_cacheFieldValues[$oInformationsystem_Group->id] = array();
				foreach ($this->_aGroup_Fields as $oField)
				{
					$this->_cacheFieldValues[$oInformationsystem_Group->id][$oField->id] = $oField->getValues($oInformationsystem_Group->id, FALSE);
				}

				$aBasicGroupData = $this->getGroupBasicData($oInformationsystem_Group);

				$aTmpArray = array_merge(
					$aBasicGroupData,
					$this->getPropertiesData($this->_aGroup_Properties, $oInformationsystem_Group),
					$this->getFieldsData($this->_aGroup_Fields, $oInformationsystem_Group),
					$this->_aItemBaseProperties
				);

				// Пропускаем поля дополнительных свойств элемента
				for ($i = 0; $i < $this->_iItem_Properties_Count; $i++)
				{
					$aTmpArray[] = "";
				}

				for ($i = 0; $i < $this->_iItem_Fields_Count; $i++)
				{
					$aTmpArray[] = "";
				}

				$this->_printRow($aTmpArray);

				$iPropertyOffset = count($aBasicGroupData);

				// Оставшиеся множественные значения свойств
				while (count($this->_cachePropertyValues[$oInformationsystem_Group->id]))
				{
					$aCurrentPropertyLine = array_fill(0, $iPropertyOffset, '""');

					// CML ID группы
					$aCurrentPropertyLine[$this->guidGroupPosition] = $oInformationsystem_Group->guid;

					$aCurrentPropertyLine = array_merge($aCurrentPropertyLine, $this->getPropertiesData($this->_aGroup_Properties, $oInformationsystem_Group));
					$this->_printRow($aCurrentPropertyLine);
				}
				unset($this->_cachePropertyValues[$oInformationsystem_Group->id]);

				// Оставшиеся множественные значения полей
				while (count($this->_cacheFieldValues[$oInformationsystem_Group->id]))
				{
					$aCurrentFieldLine = array_fill(0, $iPropertyOffset + $this->_iGroup_Properties_Count, '""');

					// CML ID группы
					$aCurrentFieldLine[$this->guidGroupPosition] = $oInformationsystem_Group->guid;

					$aCurrentFieldLine = array_merge($aCurrentFieldLine, $this->getFieldsData($this->_aGroup_Fields, $oInformationsystem_Group));
					$this->_printRow($aCurrentFieldLine);
				}
				unset($this->_cacheFieldValues[$oInformationsystem_Group->id]);
			}

			$iPropertyOffset = count($this->_aGroupBaseProperties)
				+ $this->_iGroup_Properties_Count
				+ $this->_iGroup_Fields_Count
				+ count($this->_aItemBaseProperties);

			$offset = 0;
			$limit = 500;

			do {
				$oInformationsystem_Items
					->queryBuilder()
					->offset($offset)
					->limit($limit);

				$aInformationsystem_Items = $oInformationsystem_Items->findAll(FALSE);

				foreach ($aInformationsystem_Items as $oInformationsystem_Item)
				{
					// Set GUID
					if ($oInformationsystem_Item->guid == '')
					{
						$oInformationsystem_Item->guid = Core_Guid::get();
						$oInformationsystem_Item->save();
					}

					// Кэш всех значений свойств товара
					$this->_cachePropertyValues[$oInformationsystem_Item->id] = array();
					foreach ($this->_aItem_Properties as $oProperty)
					{
						$this->_cachePropertyValues[$oInformationsystem_Item->id][$oProperty->id] = $oProperty->getValues($oInformationsystem_Item->id, FALSE);
					}

					// Кэш всех значений полей товара
					$this->_cacheFieldValues[$oInformationsystem_Item->id] = array();
					foreach ($this->_aItem_Fields as $oField)
					{
						$this->_cacheFieldValues[$oInformationsystem_Item->id][$oField->id] = $oField->getValues($oInformationsystem_Item->id, FALSE);
					}

					$this->_printRow($this->getItemData($oInformationsystem_Item));

					// Оставшиеся множественные значения свойств
					while (count($this->_cachePropertyValues[$oInformationsystem_Item->id]))
					{
						$aCurrentPropertyLine = array_fill(0, $iPropertyOffset, '""');

						// CML ID ТОВАРА
						$aCurrentPropertyLine[$this->guidItemPosition] = $oInformationsystem_Item->guid;

						$aCurrentPropertyLine = array_merge($aCurrentPropertyLine, $this->getPropertiesData($this->_aItem_Properties, $oInformationsystem_Item));
						$this->_printRow($aCurrentPropertyLine);
					}
					unset($this->_cachePropertyValues[$oInformationsystem_Item->id]);

					// Оставшиеся множественные значения полей
					while (count($this->_cacheFieldValues[$oInformationsystem_Item->id]))
					{
						$aCurrentFieldLine = array_fill(0, $iPropertyOffset + $this->_iItem_Properties_Count, '""');

						// CML ID ТОВАРА
						$aCurrentFieldLine[$this->guidItemPosition] = $oInformationsystem_Item->guid;

						$aCurrentFieldLine = array_merge($aCurrentFieldLine, $this->getFieldsData($this->_aItem_Fields, $oInformationsystem_Item));
						$this->_printRow($aCurrentFieldLine);
					}
					unset($this->_cacheFieldValues[$oInformationsystem_Item->id]);
				}
				$offset += $limit;
			}
			while (count($aInformationsystem_Items));
		}

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('End CSV export ' . $sFilename);

		exit();
	}

	/**
	 * Prepare string
	 * @param string $string
	 * @return string
	 */
	public function prepareString($string)
	{
		return str_replace('"', '""', trim((string) $string));
	}

	/**
	 * Prepare cell
	 * @param string $string
	 * @return string
	 */
	public function prepareCell($string)
	{
		return sprintf('"%s"', $this->prepareString($string));
	}

	/**
	 * Prepare float
	 * @param mixed $string
	 * @return string
	 */
	public function prepareFloat($string)
	{
		return str_replace('.', ',', $string);
	}

	/**
	 * Print array
	 * @param array $aData
	 * @return self
	 */
	protected function _printRow($aData)
	{
		echo Core_Str::iconv('UTF-8', $this->encoding, implode($this->separator, $aData)."\n");
		return $this;
	}
}