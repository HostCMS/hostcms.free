<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Information systems export CSV controller
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		$this->_iItem_Properties_Count = 0;
		$this->_iGroup_Properties_Count = 0;

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
			"","","","","","","","",""
		);

		// CML ID идентификатор элемента - Ярлыки
		$this->_aItemBase_Properties = array(
			"", "", "", "", "", "", "", "", "", "",
			"", "", "", "", "", "", "", "", ""
		);

		$this->_iCurrentDataPosition = 0;

		// 0-вая строка - заголовок CSV-файла
		$this->_aCurrentData[$this->_iCurrentDataPosition] = array(
			// 9 cells
			'"Название раздела"',
			'"GUID идентификатор группы элементов"',
			'"GUID идентификатор родительской группы элементов"',
			'"Заголовок раздела(title)"',
			'"Описание раздела(description)"',
			'"Ключевые слова раздела(keywords)"',
			'"Описание раздела"',
			'"Путь для раздела"',
			'"Порядок сортировки раздела"',
			// 19
			'"GUID идентификатор элемента"',
			'"Название элемента"',
			'"Описание элемента"',
			'"Текст для элемента"',
			'"Метки"',
			'"Активность элемента"',
			'"Порядок сортировки элемента"',
			'"Путь к элементу"',
			'"Заголовок (title)"',
			'"Значение мета-тега description для страницы с элементом"',
			'"Значение мета-тега keywords для страницы с элементом"',
			'"Флаг индексации"',
			'"Дата"',
			'"Дата публикации"',
			'"Дата завершения публикации"',
			'"Файл изображения для элемента"',
			'"Файл малого изображения для элемента"',
			'"Ярлыки"',
			'"Идентификатор пользователя сайта"',
		);

		// Добавляем в заголовок информацию о свойствах элементов
		foreach ($this->_aItem_Properties as $oItem_Property)
		{
			$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString($oItem_Property->name));
			$this->_iItem_Properties_Count++;

			if ($oItem_Property->type == 2)
			{
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Informationsystem_Item.import_small_images') . $oItem_Property->name));
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
				$this->_aCurrentData[$this->_iCurrentDataPosition][] = sprintf('"%s"', $this->prepareString(Core::_('Informationsystem_Item.import_small_images') . $oGroup_Property->name));
				$this->_iGroup_Properties_Count++;
			}
		}
	}

	/**
	 * Get item data
	 * @param int $oInformationsystem_Item item
	 * @return array
	 */
	private function getItemData($oInformationsystem_Item)
	{
		$aItemProperties = $aGroupProperties = array();

		foreach ($this->_aItem_Properties as $oItem_Property)
		{
			$aProperty_Values = $oItem_Property->getValues($oInformationsystem_Item->id, FALSE);
			$iProperty_Values_Count = count($aProperty_Values);

			$aItemProperties[] = sprintf('"%s"', $this->prepareString($iProperty_Values_Count > 0
				? ($oItem_Property->type != 2
					? ($oItem_Property->type == 3 && $aProperty_Values[0]->value != 0 && Core::moduleIsActive('list')
						? $aProperty_Values[0]->List_Item->value
						: ($oItem_Property->type == 8
							? Core_Date::sql2date($aProperty_Values[0]->value)
							: ($oItem_Property->type == 9
								? Core_Date::sql2datetime($aProperty_Values[0]->value)
								: $aProperty_Values[0]->value)))
								: ($aProperty_Values[0]->file == '' ? '' : $aProperty_Values[0]->setHref($oInformationsystem_Item->getItemHref())->getLargeFileHref())
								)
								: ''));

			if ($oItem_Property->type == 2)
			{
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

		if ($oInformationsystem_Item->Informationsystem_Group->id)
		{
			$aTmpArray[3] = sprintf('"%s"', $this->prepareString($oInformationsystem_Item->Informationsystem_Group->seo_title));
			$aTmpArray[4] = sprintf('"%s"', $this->prepareString($oInformationsystem_Item->Informationsystem_Group->seo_description));
			$aTmpArray[5] = sprintf('"%s"', $this->prepareString($oInformationsystem_Item->Informationsystem_Group->seo_keywords));
		}

		// Ярлыки
		$aShortcuts = $oInformationsystem_Item->Informationsystem_Items->findAll(FALSE);
		$aTmpShortcuts = array();
		foreach ($aShortcuts as $oShortcut_Item)
		{
			$aTmpShortcuts[] = $oShortcut_Item->guid;
		}
		unset($aShortcuts);

		return array_merge($aTmpArray,
			array(
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->guid)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->name)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->description)),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->text)),
				sprintf('"%s"', (Core::moduleIsActive('tag') ? $this->prepareString(implode(",", $oInformationsystem_Item->Tags->findAll(FALSE))) : "")),
				sprintf('"%s"', $oInformationsystem_Item->active),
				sprintf('"%s"', $oInformationsystem_Item->sorting),
				sprintf('"%s"', $this->prepareString($oInformationsystem_Item->path)),
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

		$aInformationsystemGroupsId = array_merge(array($this->parentGroup), $oInformationsystem_Groups->getGroupChildrenId());

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
					$aProperty_Values = $oGroup_Property->getValues($oInformationsystem_Group->id);
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
			$limit = 100;

			do {
				$oInformationsystem_Items->queryBuilder()->offset($offset)->limit($limit);
				$aInformationsystem_Items = $oInformationsystem_Items->findAll(FALSE);

				foreach ($aInformationsystem_Items as $oInformationsystem_Item)
				{
					$this->_printRow($this->getItemData($oInformationsystem_Item));

					$iPropertyFieldOffset = count($this->_aGroupBase_Properties) + count($this->_aItemBase_Properties);

					$aCurrentPropertyLine = array_fill(0, $iPropertyFieldOffset, '""');

					// GUID элемента
					$aCurrentPropertyLine[9] = $oInformationsystem_Item->guid;

					foreach ($this->_aItem_Properties as $oItem_Property)
					{
						$aProperty_Values = $oItem_Property->getValues($oInformationsystem_Item->id, FALSE);
						array_shift($aProperty_Values);

						if (count($aProperty_Values))
						{
							foreach ($aProperty_Values as $oProperty_Value)
							{
								$aCurrentPropertyLine[$iPropertyFieldOffset] = sprintf('"%s"', $this->prepareString(($oItem_Property->type != 2
									? ($oItem_Property->type == 3 && $oProperty_Value->value != 0 && Core::moduleIsActive('list')
										? $oProperty_Value->List_Item->value
										: ($oItem_Property->type == 8
											? Core_Date::sql2date($oProperty_Value->value)
											: ($oItem_Property->type == 9
												? Core_Date::sql2datetime($oProperty_Value->value)
												: $oProperty_Value->value)))
												: ($oProperty_Value->file == '' ? '' : $oProperty_Value->setHref($oInformationsystem_Item->getItemHref())->getLargeFileHref())
												)));

								if ($oItem_Property->type == 2)
								{
									$aCurrentPropertyLine[$iPropertyFieldOffset+1] = sprintf('"%s"', $this->prepareString($oProperty_Value->setHref($oInformationsystem_Item->getItemHref())->getSmallFileHref()));
								}

								$this->_printRow($aCurrentPropertyLine);
							}
						}

						if ($oItem_Property->type==2)
						{
							$aCurrentPropertyLine[$iPropertyFieldOffset] = '""';
							$aCurrentPropertyLine[$iPropertyFieldOffset+1] = '""';
							$iPropertyFieldOffset+=2;
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
		echo Informationsystem_Item_Import_Csv_Controller::CorrectToEncoding(implode($this->separator, $aData)."\n", $this->encoding);
		return $this;
	}
}