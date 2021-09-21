<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Information systems export CSV controller
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Item_Export_Csv_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'separator',
		'encoding',
		'parentGroup',
		'exportItemExternalProperties',
		'exportGroupExternalProperties',
		'informationsystemId',
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
	 * Base properties of items
	 * Основные свойства элементов
	 * @var array
	 */
	private $_aItemBase_Properties;

	/**
	 * Base properties of item groups
	 * Основные свойства групп элементов
	 * @var array
	 */
	private $_aGroupBase_Properties;

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
	 * Constructor.
	 * @param int $iInformationsystemId informationsystem ID
	 * @param boolean $bItemPropertiesExport export item properties mode
	 * @param boolean $bGroupPropertiesExport export group properties mode
	 */
	public function __construct($iInformationsystemId, $bItemPropertiesExport = TRUE, $bGroupPropertiesExport = TRUE)
	{
		parent::__construct();

		$this->informationsystemId = $iInformationsystemId;
		$this->exportItemExternalProperties = $bItemPropertiesExport;
		$this->exportGroupExternalProperties = $bGroupPropertiesExport;
		$this->_iItem_Properties_Count = $this->_iGroup_Properties_Count = 0;

		// Устанавливаем лимит времени выполнения в 1 час
		(!defined('DENY_INI_SET') || !DENY_INI_SET)
			&& function_exists('set_time_limit') && ini_get('safe_mode') != 1 && @set_time_limit(3600);

		// Заполняем дополнительные свойства элемента
		$this->exportItemExternalProperties
			&& $this->_aItem_Properties = Core_Entity::factory('Informationsystem_Item_Property_List', $this->informationsystemId)->Properties->findAll(FALSE);

		// Заполняем дополнительные свойства групп элементов
		$this->exportGroupExternalProperties
			&& $this->_aGroup_Properties = Core_Entity::factory('Informationsystem_Group_Property_List', $this->informationsystemId)->Properties->findAll(FALSE);

		// Название раздела - Порядок сортировки раздела
		$this->_aGroupBase_Properties = array(
			"", "", "", "", "", "", "", "", "", "", ""
		);

		// CML ID идентификатор элемента - Ярлыки
		$this->_aItemBase_Properties = array(
			"", "", "", "", "", "", "", "", "", "",
			"", "", "", "", "", "", "", "", ""
		);

		$this->_iCurrentDataPosition = 0;

		// 0-вая строка - заголовок CSV-файла
		$this->_aCurrentData[$this->_iCurrentDataPosition] = array(
			// 11 cells
			'"' . Core::_('Informationsystem_Item_Export.category_name') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_guid_id') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_parent_guid_id') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_meta_title') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_meta_description') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_meta_keywords') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_description') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_path') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_large_image') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_small_image') . '"',
			'"' . Core::_('Informationsystem_Item_Export.category_sorting') . '"',
			// 19
			'"' . Core::_('Informationsystem_Item_Export.item_guid_id') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_path') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_name') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_description') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_text') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_tags') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_activity') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_sorting') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_meta_title') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_meta_description') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_meta_keywords') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_indexing') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_date') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_start_date') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_end_date') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_large_image') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_small_image') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_shortcuts') . '"',
			'"' . Core::_('Informationsystem_Item_Export.item_siteuser_id') . '"',
		);

		// Добавляем в заголовок информацию о свойствах элементов
		foreach ($this->_aItem_Properties as $oItem_Property)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString($oItem_Property->name));
			$this->_iItem_Properties_Count++;

			if ($oItem_Property->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Informationsystem_Item.import_file_description', $oItem_Property->name)));
				$this->_iItem_Properties_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Informationsystem_Item.import_small_images', $oItem_Property->name)));
				$this->_iItem_Properties_Count++;
			}
		}

		// Добавляем в заголовок информацию о свойствах группы элементов
		foreach ($this->_aGroup_Properties as $oGroup_Property)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString($oGroup_Property->name));
			$this->_iGroup_Properties_Count++;

			if ($oGroup_Property->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Informationsystem_Item.import_file_description', $oGroup_Property->name)));
				$this->_iGroup_Properties_Count++;

				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Informationsystem_Item.import_small_images', $oGroup_Property->name)));
				$this->_iGroup_Properties_Count++;
			}
		}
	}

	/**
	 * Get item data
	 * @param int $oInformationsystem_Item item
	 * @return array
	 */
	protected function _getItemData($oInformationsystem_Item)
	{
		$aItemProperties = $aGroupProperties = array();

		foreach ($this->_aItem_Properties as $oProperty)
		{
			$aProperty_Values = $oProperty->getValues($oInformationsystem_Item->id, FALSE);
			$iProperty_Values_Count = count($aProperty_Values);

			$aItemProperties[] = sprintf('"%s"', $this->prepareString(
				$iProperty_Values_Count > 0
					? $this->_getPropertyValue($oProperty, $aProperty_Values[0], $oInformationsystem_Item)
					: ''
			));

			if ($oProperty->type == 2)
			{
				$aItemProperties[] = $iProperty_Values_Count
					? sprintf('"%s"', $aProperty_Values[0]->file_description)
					: '';

				$aItemProperties[] = $iProperty_Values_Count
					? ($aProperty_Values[0]->file_small == '' ? '' : sprintf('"%s"', $aProperty_Values[0]->getSmallFileHref()))
					: '';
			}
		}

		for ($i = 0; $i < $this->_iGroup_Properties_Count; $i++)
		{
			$aGroupProperties[] = "";
		}

		$aTmpArray = $this->_aGroupBase_Properties;

		$aTmpArray[1] = is_null($oInformationsystem_Item->Informationsystem_Group->id)
			? 'ID00000000'
			: $oInformationsystem_Item->Informationsystem_Group->guid;

		// У ИЭ нет необходимости дублировать данные о группе
		/*if ($oInformationsystem_Item->Informationsystem_Group->id)
		{
			$aTmpArray[3] = sprintf('"%s"', $this->prepareString($oInformationsystem_Item->Informationsystem_Group->seo_title));
			$aTmpArray[4] = sprintf('"%s"', $this->prepareString($oInformationsystem_Item->Informationsystem_Group->seo_description));
			$aTmpArray[5] = sprintf('"%s"', $this->prepareString($oInformationsystem_Item->Informationsystem_Group->seo_keywords));
		}*/

		// Ярлыки
		$aTmpShortcuts = array();
		$aShortcuts = $oInformationsystem_Item->Informationsystem_Items->findAll(FALSE);
		foreach ($aShortcuts as $oShortcut_Item)
		{
			$aTmpShortcuts[] = $oShortcut_Item->guid;
		}
		unset($aShortcuts);

		return array_merge($aTmpArray,
			array(
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->guid)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->path)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->name)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->description)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->text)),
				sprintf('"%s"', (Core::moduleIsActive('tag') ? $this->prepareString(implode(",", $oInformationsystem_Item->Tags->findAll(FALSE))) : "")),
				sprintf('"%s"', $oInformationsystem_Item->active),
				sprintf('"%s"', $oInformationsystem_Item->sorting),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->seo_title)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->seo_description)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->seo_keywords)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->indexing)),
				sprintf('"%s"', $oInformationsystem_Item->datetime == '0000-00-00 00:00:00'
					? '0000-00-00 00:00:00'
					: Core_Date::sql2datetime($oInformationsystem_Item->datetime)
				),
				sprintf('"%s"', $oInformationsystem_Item->start_datetime == '0000-00-00 00:00:00'
					? '0000-00-00 00:00:00'
					: Core_Date::sql2datetime($oInformationsystem_Item->start_datetime)
				),
				sprintf('"%s"', $oInformationsystem_Item->end_datetime == '0000-00-00 00:00:00'
					? '0000-00-00 00:00:00'
					: Core_Date::sql2datetime($oInformationsystem_Item->end_datetime)
				),
				sprintf('"%s"', ($oInformationsystem_Item->image_large == '') ? '' : $oInformationsystem_Item->getLargeFileHref()),
				sprintf('"%s"', ($oInformationsystem_Item->image_small == '') ? '' : $oInformationsystem_Item->getSmallFileHref()),
				sprintf('"%s"', implode(',', $aTmpShortcuts)),
				sprintf('"%s"', $oInformationsystem_Item->siteuser_id)
			),
			$aItemProperties,
			$aGroupProperties
		);
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
				$result = $oProperty_Value->file == ''
					? ''
					: $oProperty_Value->setHref($object->getItemHref())->getLargeFileHref();
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
	 * Executes the business logic.
	 */
	public function execute()
	{
		$oUser = Core_Auth::getCurrentUser();
		if ($oUser->only_access_my_own)
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
				->where('shortcut_id', '=', 0);

			if ($iInformationsystemGroupId != 0)
			{
				$aTmpArray = array(
					sprintf('"%s"', $this->prepareString($oInformationsystem_Group->name)),
					sprintf('"%s"', $this->prepareString($oInformationsystem_Group->guid)),
					sprintf('"%s"', $this->prepareString(is_null($oInformationsystem_Group->Informationsystem_Group->id) ? 'ID00000000' : $oInformationsystem_Group->Informationsystem_Group->guid)),
					sprintf('"%s"', $this->prepareString($oInformationsystem_Group->seo_title)),
					sprintf('"%s"', $this->prepareString($oInformationsystem_Group->seo_description)),
					sprintf('"%s"', $this->prepareString($oInformationsystem_Group->seo_keywords)),
					sprintf('"%s"', $this->prepareString($oInformationsystem_Group->description)),
					sprintf('"%s"', $this->prepareString($oInformationsystem_Group->path)),
					sprintf('"%s"', ($oInformationsystem_Group->image_large == '') ? '' : $oInformationsystem_Group->getLargeFileHref()),
					sprintf('"%s"', ($oInformationsystem_Group->image_small == '') ? '' : $oInformationsystem_Group->getSmallFileHref()),
					sprintf('"%s"', $this->prepareString($oInformationsystem_Group->sorting))
				);

				// Пропускаем поля элемента
				foreach ($this->_aItemBase_Properties as $sNullData)
				{
					$aTmpArray[] = $sNullData;
				}

				// Пропускаем поля дополнительных свойств элемента
				for ($i = 0; $i < $this->_iItem_Properties_Count; $i++)
				{
					$aTmpArray[] = "";
				}

				// Выводим данные о дополнительных свойствах групп
				foreach ($this->_aGroup_Properties as $oGroup_Property)
				{
					$aProperty_Values = $oGroup_Property->getValues($oInformationsystem_Group->id, FALSE);
					$iProperty_Values_Count = count($aProperty_Values);

					$aTmpArray[] = sprintf('"%s"', $this->prepareString($iProperty_Values_Count > 0 ? ($oGroup_Property->type != 2
						? ($oGroup_Property->type == 3 && $aProperty_Values[0]->value != 0 && Core::moduleIsActive('list')
							? $aProperty_Values[0]->List_Item->value
							: ($oGroup_Property->type == 8
								? Core_Date::sql2date($aProperty_Values[0]->value)
								: ($oGroup_Property->type == 9
									? Core_Date::sql2datetime($aProperty_Values[0]->value)
									: $aProperty_Values[0]->value)))
									: ($aProperty_Values[0]->file == ''
										? ''
										: $aProperty_Values[0]->setHref($oInformationsystem_Group->getGroupHref())->getLargeFileHref()))
											: ''));

					if ($oGroup_Property->type == 2)
					{
						$aTmpArray[] = $iProperty_Values_Count
							? sprintf('"%s"', $aProperty_Values[0]->file_description)
							: '';

						$aTmpArray[] = $iProperty_Values_Count
							? ($aProperty_Values[0]->file_small == ''
								? ''
								: $aProperty_Values[0]->setHref($oInformationsystem_Group->getGroupHref())->getSmallFileHref()
							)
							: '';
					}
				}

				$this->_printRow($aTmpArray);
			}
			else
			{
				$oInformationsystem_Items->queryBuilder()->where('informationsystem_id', '=', $this->informationsystemId);
			}

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

					$this->_printRow($this->_getItemData($oInformationsystem_Item));

					$iPropertyFieldOffset = count($this->_aGroupBase_Properties) + count($this->_aItemBase_Properties);

					$aCurrentPropertyLine = array_fill(0, $iPropertyFieldOffset, '""');

					// GUID элемента
					$aCurrentPropertyLine[11] = $oInformationsystem_Item->guid;

					foreach ($this->_aItem_Properties as $oItem_Property)
					{
						$aProperty_Values = $oItem_Property->getValues($oInformationsystem_Item->id, FALSE);
						array_shift($aProperty_Values);

						if (count($aProperty_Values))
						{
							foreach ($aProperty_Values as $oProperty_Value)
							{
								$aCurrentPropertyLine[$iPropertyFieldOffset] = sprintf('"%s"', $this->prepareString(
										$this->_getPropertyValue($oItem_Property, $oProperty_Value, $oInformationsystem_Item)
									)
								);

								if ($oItem_Property->type == 2)
								{
									$aCurrentPropertyLine[$iPropertyFieldOffset + 1] = sprintf('"%s"', $this->prepareString($oProperty_Value->file_description));

									$aCurrentPropertyLine[$iPropertyFieldOffset + 2] = sprintf('"%s"', $this->prepareString($oProperty_Value->setHref($oInformationsystem_Item->getItemHref())->getSmallFileHref()));
								}

								$this->_printRow($aCurrentPropertyLine);
							}
						}

						if ($oItem_Property->type == 2)
						{
							// File
							$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
							$iPropertyFieldOffset++;

							// Description
							$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
							$iPropertyFieldOffset++;

							// Small File
							$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
							$iPropertyFieldOffset++;
						}
						else
						{
							$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
							$iPropertyFieldOffset++;
						}
					}
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
		return str_replace('"', '""', trim($string));
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