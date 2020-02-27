<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Information systems import CSV controller
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Item_Import_Csv_Controller extends Core_Servant_Properties
{
	/**
	 * Array of inserted groups
	 * @var array
	 */
	protected $_aInsertedGroupIDs = array();

	/**
	 * Array of property values
	 * @var array
	 */
	protected $_aClearedPropertyValues = array();

	/**
	 * Array of updated groups
	 * @var array
	 */
	protected $_aUpdatedGroupIDs = array();

	/**
	 * Array of inserted items
	 * @var array
	 */
	protected $_aInsertedItemIDs = array();

	/**
	 * Array of updated items
	 * @var array
	 */
	protected $_aUpdatedItemIDs = array();

	/**
	 * ID of current informationsystem
	 * @var int
	 */
	protected $_iCurrentInformationsystemId = 0;

	/**
	 * ID of current group
	 * @var int
	 */
	protected $_iCurrentGroupId = 0;

	/**
	 * Current informationsystem
	 * @var Informationsystem_Model
	 */
	protected $_oCurrentInformationsystem;

	/**
	 * Current group
	 * @var Informationsystem_Group_Model
	 */
	protected $_oCurrentGroup;

	/**
	 * Current item
	 * @var Informationsystem_Item_Model
	 */
	protected $_oCurrentItem;

	/**
	 * Current tags
	 * @var string
	 */
	protected $_sCurrentTags;

	/**
	 * List of small parts of external properties
	 * @var array
	 */
	protected $_aExternalPropertiesSmall = array();

	/**
	 * List of descriptions of external properties
	 * @var array
	 */
	protected $_aExternalPropertiesDesc = array();

	/**
	 * List of external properties
	 * @var array
	 */
	protected $_aExternalProperties = array();

	/**
	 * List of additional group
	 * @var array
	 */
	protected $_aAdditionalGroups = array();

	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		// Кодировка импорта
		'encoding',
		// Файл импорта
		'file',
		// Позиция в файле импорта
		'seek',
		// Ограничение импорта по времени
		'time',
		// Ограничение импорта по количеству
		'step',
		// Настройка CSV: разделитель
		'separator',
		// Настройка CSV: ограничитель
		'limiter',
		// Настройка CSV: первая строка - название полей
		'firstlineheader',
		// Настройка CSV: массив соответствий полей CSV сущностям системы HostCMS
		'csv_fields',
		// Путь к картинкам
		'imagesPath',
		// Действие с существующими информационными элементами:
		// 1 - обновить существующие информационные элементы
		// 2 - не обновлять существующие информационные элементы
		// 3 - удалить содержимое информационной системы до импорта
		'importAction',
		// Флаг, указывающий, включена ли индексация
		'searchIndexation',
		'deleteImage'
	);

	/**
	 * Count of inserted items
	 * @var int
	 */
	protected $_InsertedItemsCount;

	/**
	 * Count of updated items
	 * @var int
	 */
	protected $_UpdatedItemsCount;

	/**
	 * Count of inserted groups
	 * @var int
	 */
	protected $_InsertedGroupsCount;

	/**
	 * Count of updated groups
	 * @var int
	 */
	protected $_UpdatedGroupsCount;

	/**
	 * Path of the big image
	 * @var string
	 */
	protected $_sBigImageFile = '';

	/**
	 * Path of the small image
	 * @var string
	 */
	protected $_sSmallImageFile = '';

	/**
	 * Get inserted items count
	 * @return int
	 */
	public function getInsertedItemsCount()
	{
		return $this->_InsertedItemsCount;
	}

	/**
	 * Get inserted groups count
	 * @return int
	 */
	public function getInsertedGroupsCount()
	{
		return $this->_InsertedGroupsCount;
	}

	/**
	 * Get updated items count
	 * @return int
	 */
	public function getUpdatedItemsCount()
	{
		return $this->_UpdatedItemsCount;
	}

	/**
	 * Get updated groups count
	 * @return int
	 */
	public function getUpdatedGroupsCount()
	{
		return $this->_UpdatedGroupsCount;
	}

	/**
	 * Increment inserted groups
	 * @param int $iGroupId group ID
	 * @return self
	 */
	protected function _incInsertedGroups($iGroupId)
	{
		if (!in_array($iGroupId, $this->_aInsertedGroupIDs))
		{
			$this->_aInsertedGroupIDs[] = $iGroupId;
			$this->_InsertedGroupsCount++;
		}
		return $this;
	}

	/**
	 * Increment updated groups
	 * @param int $iGroupId group ID
	 * @return self
	 */
	protected function _incUpdatedGroups($iGroupId)
	{
		if (!in_array($iGroupId, $this->_aUpdatedGroupIDs))
		{
			$this->_aUpdatedGroupIDs[] = $iGroupId;
			$this->_UpdatedGroupsCount++;
		}
		return $this;
	}

	/**
	 * Increment inserted items
	 * @param int $iItemId item ID
	 * @return self
	 */
	protected function _incInsertedItems($iItemId)
	{
		if (!in_array($iItemId, $this->_aInsertedItemIDs))
		{
			$this->_aInsertedItemIDs[] = $iItemId;
			$this->_InsertedItemsCount++;
		}
		return $this;
	}

	/**
	 * Increment updated items
	 * @param int $iItemId item ID
	 * @return self
	 */
	protected function _incUpdatedItems($iItemId)
	{
		if (!in_array($iItemId, $this->_aUpdatedItemIDs))
		{
			$this->_aUpdatedItemIDs[] = $iItemId;
			$this->_UpdatedItemsCount++;
		}
		return $this;
	}

	/**
	* Set $this->_oCurrentItem
	* @param Informationsystem_Item_Model $oCurrentItem
	* @return self
	*/
	public function setCurrentItem(Informationsystem_Item_Model $oCurrentItem)
	{
		$this->_oCurrentItem = $oCurrentItem;
		return $this;
	}

	/**
	 * Initialization
	 * @return self
	 */
	protected function init()
	{
		$this->_oCurrentInformationsystem = Core_Entity::factory('Informationsystem')->find($this->_iCurrentInformationsystemId);

		// Инициализация текущей группы
		$this->_oCurrentGroup = Core_Entity::factory('Informationsystem_Group', $this->_iCurrentGroupId);
		$this->_oCurrentGroup->informationsystem_id = $this->_oCurrentInformationsystem->id;

		// Инициализация текущего инфоэлемента
		$this->_oCurrentItem = Core_Entity::factory('Informationsystem_Item');
		$this->_oCurrentItem->informationsystem_group_id = intval($this->_oCurrentGroup->id);

		return $this;
	}

	/**
	 * CSV config
	 * @var array
	 */
	protected $_aConfig = NULL;

	/**
	 * Constructor.
	 * @param int $iCurrentInformationsystemId Informationsystem ID
	 * @param int $iCurrentGroupId current group ID
	 */
	public function __construct($iCurrentInformationsystemId, $iCurrentGroupId = 0)
	{
		parent::__construct();

		$this->_aConfig = Core_Config::instance()->get('informationsystem_csv', array()) + array(
			'maxTime' => 20,
			'maxCount' => 100
		);

		$this->_iCurrentInformationsystemId = $iCurrentInformationsystemId;
		$this->_iCurrentGroupId = $iCurrentGroupId;

		$this->time = $this->_aConfig['maxTime'];
		$this->step = $this->_aConfig['maxCount'];

		$this->init();

		// Единожды в конструкторе, чтобы после __wakeup() не обнулялось
		$this->_InsertedItemsCount = 0;
		$this->_UpdatedItemsCount = 0;
		$this->_InsertedGroupsCount = 0;
		$this->_UpdatedGroupsCount = 0;
	}

	/**
	 * Save group
	 * @param Informationsystem_Group_Model $oInformationsystem_Group group
	 * @return Informationsystem_Group
	 */
	protected function _doSaveGroup(Informationsystem_Group_Model $oInformationsystem_Group)
	{
		is_null($oInformationsystem_Group->path) && $oInformationsystem_Group->path = '';
		$this->_incInsertedGroups($oInformationsystem_Group->save()->id);
		return $oInformationsystem_Group;
	}

	/**
	* Импорт CSV
	* @hostcms-event Informationsystem_Item_Import_Csv_Controller.onBeforeImport
	* @hostcms-event Informationsystem_Item_Import_Csv_Controller.onAfterImport
	* @hostcms-event Informationsystem_Item_Import_Csv_Controller.onBeforeFindByMarking
	* @hostcms-event Informationsystem_Item_Import_Csv_Controller.onAfterFindByMarking
	* @hostcms-event Informationsystem_Item_Import_Csv_Controller.oBeforeAdminUpload
	* @hostcms-event Informationsystem_Item_Import_Csv_Controller.onBeforeImportGroupProperty
	* @hostcms-event Informationsystem_Item_Import_Csv_Controller.onBeforeImportItemProperty
	*/
	public function import()
	{
		Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.onBeforeImport', $this, array($this->_oCurrentInformationsystem));

		if ($this->importAction == 3)
		{
			Core_QueryBuilder::update('informationsystem_groups')
				->set('deleted', 1)
				->where('informationsystem_id', '=', $this->_oCurrentInformationsystem->id)
				->execute();

			Core_QueryBuilder::update('informationsystem_items')
				->set('deleted', 1)
				->where('informationsystem_id', '=', $this->_oCurrentInformationsystem->id)
				->execute();
		}

		$fInputFile = fopen($this->file, 'rb');

		if ($fInputFile === FALSE)
		{
			throw new Core_Exception("");
		}

		// Remove first BOM
		if ($this->seek == 0)
		{
			$BOM = fgets($fInputFile, 4); // length - 1 байт

			if ($BOM === "\xEF\xBB\xBF")
			{
				$this->seek = 3;
			}
			else
			{
				fseek($fInputFile, 0);
			}
		}
		else
		{
			fseek($fInputFile, $this->seek);
		}

		$iCounter = 0;

		$timeout = Core::getmicrotime();

		$aCsvLine = array();

		while ((Core::getmicrotime() - $timeout + 3 < $this->time)
			&& $iCounter < $this->step
			&& ($aCsvLine = $this->getCSVLine($fInputFile)))
		{
			if (count($aCsvLine) == 1
			&& (is_null($aCsvLine[0]) || $aCsvLine[0] == ''))
			{
				continue;
			}

			foreach ($aCsvLine as $iKey => $sData)
			{
				if (!isset($this->csv_fields[$iKey]))
				{
					continue;
				}

				if ($sData != '')
				{
					switch ($this->csv_fields[$iKey])
					{
						// Идентификатор группы
						case 'informationsystem_groups_id':
							if (intval($sData))
							{
								$oTmpObject = Core_Entity::factory('Informationsystem_Group')->find($sData);

								if (!is_null($oTmpObject->id))
								{
									$this->_oCurrentGroup = $oTmpObject;
								}
							}
						break;
						// Название группы
						case 'informationsystem_groups_value':
							// Позиция GUID
							$sNeedKeyCML = array_search('informationsystem_groups_guid', $this->csv_fields);
							// Позиция названия группы
							$sNeedKeyName = array_search('informationsystem_groups_value', $this->csv_fields);

							// Группа была ранее найдена по CML GROUP ID и CML GROUP ID идет раньше,
							// чем название группы, тогда просто обновляем название группы
							if ($sNeedKeyCML !== FALSE
								&& $sNeedKeyCML < $sNeedKeyName
								// Для новой группы "CML ID|Название группы", id будет пустым
								/*&& $this->_oCurrentGroup->id*/)
							{
								// Меняем название на переданное
								$this->_oCurrentGroup->name = $sData;
								$this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
							}
							else
							{
								// CML_ID родительской (!) группы
								$sNeedKeyParentCMLId = array_search('informationsystem_groups_parent_guid', $this->csv_fields);
								if ($sNeedKeyParentCMLId !== FALSE
									&& ($sCMLID = Core_Array::get($aCsvLine, $sNeedKeyParentCMLId, '')) != '')
								{
									if ($sCMLID == 'ID00000000')
									{
										$oTmpParentObject = Core_Entity::factory('Informationsystem_Group', 0);
									}
									else
									{
										$oTmpParentObject = $this->_oCurrentInformationsystem->Informationsystem_Groups->getByGuid($sCMLID, FALSE);

										if (is_null($oTmpParentObject))
										{
											$oTmpParentObject = Core_Entity::factory('Informationsystem_Group', 0);
										}
									}

									$oTmpObject = $this->_oCurrentInformationsystem->Informationsystem_Groups;
									$oTmpObject->queryBuilder()
										->where('parent_id', '=', $oTmpParentObject->id)
										->where('name', '=', $sData)
										->where('shortcut_id', '=', 0)
										->limit(1);
								}
								else
								{
									$oTmpObject = $this->_oCurrentInformationsystem->Informationsystem_Groups;
									$oTmpObject->queryBuilder()
										->where('parent_id', '=', intval($this->_oCurrentGroup->id))
										->where('name', '=', $sData)
										->where('shortcut_id', '=', 0)
										->limit(1);
								}

								$aTmpObject = $oTmpObject->findAll(FALSE);

								if (count($aTmpObject))
								{
									// Группа нашлась
									$this->_oCurrentGroup = $aTmpObject[0];
								}
								else
								{
									// Группа не нашлась
									$oTmpObject = Core_Entity::factory('Informationsystem_Group');
									$oTmpObject->name = $sData;

									$sNeedKeyParentCML = array_search('informationsystem_groups_parent_guid', $this->csv_fields);

									if ($sNeedKeyParentCML !== FALSE
										// Если явно переданный CML Parent ID идет до названия
										&& $sNeedKeyParentCML < $sNeedKeyName)
									{
										$oTmpObject->parent_id = intval($this->_oCurrentGroup->parent_id);
									}
									else
									{
										$oTmpObject->parent_id = intval($this->_oCurrentGroup->id);
									}

									$oTmpObject->informationsystem_id = $this->_oCurrentInformationsystem->id;

									// Переданные GUID для новой группы
									if ($sNeedKeyCML !== FALSE
										// CML ID идет раньше названия группы, тогда он присваивается новой группе
										&& $sNeedKeyCML < $sNeedKeyName)
									{
										$oTmpObject->guid = strval(Core_Array::get($aCsvLine, $sNeedKeyCML, ''));
									}

									$this->_oCurrentGroup = $this->_doSaveGroup($oTmpObject);
								}
							}

							$this->_oCurrentItem->informationsystem_group_id = $this->_oCurrentGroup->id;

						break;
						// Путь группы
						case 'informationsystem_groups_path':
							$oTmpObject = Core_Entity::factory('Informationsystem_Group');
							$oTmpObject
								->queryBuilder()
								->where('parent_id', '=', intval($this->_oCurrentGroup->id))
								->where('informationsystem_id', '=', intval($this->_oCurrentInformationsystem->id))
								->where('path', '=', $sData);

							$oTmpObject = $oTmpObject->findAll(FALSE);

							if (count($oTmpObject))
							{
								// Группа найдена, делаем текущей
								$this->_oCurrentGroup = $oTmpObject[0];
							}
							else
							{
								// Группа не найдена, обновляем путь для текущей группы
								$this->_oCurrentGroup->path = $sData;
								$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
							}
						break;
						// Порядок сортировки группы
						case 'informationsystem_groups_order':
							$this->_oCurrentGroup->sorting = intval($sData);
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// Описание группы
						case 'informationsystem_groups_description':
							$this->_oCurrentGroup->description = $sData;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// SEO Title группы
						case 'informationsystem_groups_seo_title':
							$this->_oCurrentGroup->seo_title = $sData;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// SEO Description группы
						case 'informationsystem_groups_seo_description':
							$this->_oCurrentGroup->seo_description = $sData;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// SEO Keywords группы
						case 'informationsystem_groups_seo_keywords':
							$this->_oCurrentGroup->seo_keywords = $sData;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// Активность группы
						case 'informationsystem_groups_activity':
							$this->_oCurrentGroup->active = intval($sData) >= 1 ? 1 : 0;
							$this->_oCurrentGroup->id && $this->_oCurrentGroup->save() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
						break;
						// Картинка группы
						case 'informationsystem_groups_image':
							// Для гарантии получения идентификатора группы
							$this->_oCurrentGroup->save();
							$this->_incUpdatedGroups($this->_oCurrentGroup->id);

							// Папка назначения
							$sDestinationFolder = $this->_oCurrentGroup->getGroupPath();

							// Файл-источник
							$sSourceFile = $this->imagesPath . (
								strtoupper($this->encoding) == 'UTF-8'
									? $sData
									: Core_File::convertfileNameToLocalEncoding($sData)
							);
							$sSourceFileBaseName = basename($sSourceFile, '');

							$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

							if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
							{
								// Создаем папку назначения
								$this->_oCurrentGroup->createDir();

								if ($bHttp)
								{
									// Файл из WEB'а, создаем временный файл
									$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");
									// Копируем содержимое WEB-файла в локальный временный файл
									file_put_contents($sTempFileName, file_get_contents($sSourceFile));
									// Файл-источник равен временному файлу
									$sSourceFile = $sTempFileName;
								}
								else
								{
									$sSourceFile = CMS_FOLDER . $sSourceFile;
								}

								if (!$this->_oCurrentInformationsystem->change_filename)
								{
									$sTargetFileName = $sSourceFileBaseName;
								}
								else
								{
									$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
									$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
										? '.jpg'
										: ".{$sTargetFileExtension}";

									$sTargetFileName = "informationsystem_group_image{$this->_oCurrentGroup->id}{$sTargetFileExtension}";
								}

								// Создаем массив параметров для загрузки картинок элементу
								$aPicturesParam = array();
								$aPicturesParam['large_image_isset'] = TRUE;
								$aPicturesParam['large_image_source'] = $sSourceFile;
								$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
								$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;

								$aPicturesParam['watermark_file_path'] = $this->_oCurrentInformationsystem->getWatermarkFilePath();
								$aPicturesParam['watermark_position_x'] = $this->_oCurrentInformationsystem->watermark_default_position_x;
								$aPicturesParam['watermark_position_y'] = $this->_oCurrentInformationsystem->watermark_default_position_y;
								$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio;

								// Проверяем, передали ли нам малое изображение
								$iSmallImageIndex = array_search('informationsystem_groups_small_image', $this->csv_fields);

								$bCreateSmallImage = $iSmallImageIndex === FALSE || strval($this->csv_fields[$iSmallImageIndex]) == '';

								if ($bCreateSmallImage)
								{
									// Малое изображение не передано, создаем его из большого
									$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
									$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
									$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
									$aPicturesParam['create_small_image_from_large'] = TRUE;
									$aPicturesParam['small_image_max_width'] = $this->_oCurrentInformationsystem->group_image_small_max_width;
									$aPicturesParam['small_image_max_height'] = $this->_oCurrentInformationsystem->group_image_small_max_height;
									$aPicturesParam['small_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_small_image;
									$aPicturesParam['small_image_preserve_aspect_ratio'] = $aPicturesParam['large_image_preserve_aspect_ratio'];
								}
								else
								{
									$aPicturesParam['create_small_image_from_large'] = FALSE;
								}

								$aPicturesParam['large_image_max_width'] = $this->_oCurrentInformationsystem->group_image_large_max_width;
								$aPicturesParam['large_image_max_height'] = $this->_oCurrentInformationsystem->group_image_large_max_height;
								$aPicturesParam['large_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_large_image;

								// Удаляем старое большое изображение
								if ($this->_oCurrentGroup->image_large)
								{
									try
									{
										Core_File::delete($this->_oCurrentGroup->getLargeFilePath());
									} catch (Exception $e) {}
								}

								// Удаляем старое малое изображение
								if ($bCreateSmallImage && $this->_oCurrentGroup->image_small)
								{
									try
									{
										Core_File::delete($this->_oCurrentGroup->getSmallFilePath());
									} catch (Exception $e) {}
								}

								try {
									Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
									$aTmpReturn = Core_Event::getLastReturn();
									is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

									$result = Core_File::adminUpload($aPicturesParam);
								}
								catch (Exception $e)
								{
									Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
										? $e->getMessage()
										: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
									, 'error');

									$result = array('large_image' => FALSE, 'small_image' => FALSE);
								}

								if ($result['large_image'])
								{
									$this->_oCurrentGroup->image_large = $sTargetFileName;

									$this->_oCurrentGroup->id
										//&& $this->_oCurrentGroup->setLargeImageSizes()
										&& $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								if ($result['small_image'])
								{
									$this->_oCurrentGroup->image_small = "small_{$sTargetFileName}";

									$this->_oCurrentGroup->id
										//&& $this->_oCurrentGroup->setSmallImageSizes()
										&& $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								if (strpos(basename($sSourceFile), "CMS") === 0)
								{
									// Файл временный, подлежит удалению
									Core_File::delete($sSourceFile);
								}
							}
						break;
						// Малая картинка группы
						case 'informationsystem_groups_small_image':
							// Для гарантии получения идентификатора группы
							$this->_oCurrentGroup->save();
							$this->_incUpdatedGroups($this->_oCurrentGroup->id);

							// Папка назначения
							$sDestinationFolder = $this->_oCurrentGroup->getGroupPath();

							// Файл-источник
							$sSourceFile = $this->imagesPath . (
								strtoupper($this->encoding) == 'UTF-8'
									? $sData
									: Core_File::convertfileNameToLocalEncoding($sData)
							);
							$sSourceFileBaseName = basename($sSourceFile, '');

							$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

							if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
							{
								// Создаем папку назначения
								$this->_oCurrentGroup->createDir();

								if ($bHttp)
								{
									// Файл из WEB'а, создаем временный файл
									$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");
									// Копируем содержимое WEB-файла в локальный временный файл
									file_put_contents($sTempFileName, file_get_contents($sSourceFile));
									// Файл-источник равен временному файлу
									$sSourceFile = $sTempFileName;
								}
								else
								{
									$sSourceFile = CMS_FOLDER . $sSourceFile;
								}

								if (!$this->_oCurrentInformationsystem->change_filename)
								{
									$sTargetFileName = "small_{$sSourceFileBaseName}";
								}
								else
								{
									$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
									$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
										? '.jpg'
										: ".{$sTargetFileExtension}";

									$sTargetFileName = "small_informationsystem_group_image{$this->_oCurrentGroup->id}{$sTargetFileExtension}";
								}

								$aPicturesParam = array();
								$aPicturesParam['small_image_source'] = $sSourceFile;
								$aPicturesParam['small_image_name'] = $sSourceFileBaseName;
								$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileName;
								$aPicturesParam['create_small_image_from_large'] = FALSE;
								$aPicturesParam['small_image_max_width'] = $this->_oCurrentInformationsystem->group_image_small_max_width;
								$aPicturesParam['small_image_max_height'] = $this->_oCurrentInformationsystem->group_image_small_max_height;
								$aPicturesParam['small_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_small_image;
								$aPicturesParam['watermark_file_path'] = $this->_oCurrentInformationsystem->getWatermarkFilePath();
								$aPicturesParam['watermark_position_x'] = $this->_oCurrentInformationsystem->watermark_default_position_x;
								$aPicturesParam['watermark_position_y'] = $this->_oCurrentInformationsystem->watermark_default_position_y;
								$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio;

								// Удаляем старое малое изображение
								if ($this->_oCurrentGroup->image_small)
								{
									try
									{
										Core_File::delete($this->_oCurrentGroup->getSmallFilePath());
									} catch (Exception $e) {}
								}

								try {
									Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
									$aTmpReturn = Core_Event::getLastReturn();
									is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

									$result = Core_File::adminUpload($aPicturesParam);
								}
								catch (Exception $e)
								{
									Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
										? $e->getMessage()
										: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
									, 'error');

									$result = array('small_image' => FALSE);
								}

								if ($result['small_image'])
								{
									$this->_oCurrentGroup->image_small = $sTargetFileName;

									$this->_oCurrentGroup->id && $this->_oCurrentGroup->setSmallImageSizes() && $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								if (strpos(basename($sSourceFile), "CMS") === 0)
								{
									// Файл временный, подлежит удалению
									Core_File::delete($sSourceFile);
								}
							}
						break;
						// Передан GUID группы
						case 'informationsystem_groups_guid':
							if ($sData == 'ID00000000')
							{
								$oTmpObject = array(Core_Entity::factory("Informationsystem_Group", 0));
							}
							else
							{
								$oTmpObject = $this->_oCurrentInformationsystem->Informationsystem_Groups;
								$oTmpObject->queryBuilder()
									->where('guid', '=', $sData)
									->where('shortcut_id', '=', 0)
									->limit(1);

								$oTmpObject = $oTmpObject->findAll(FALSE);
							}

							if (count($oTmpObject))
							{
								// группа найдена
								$this->_oCurrentGroup = $oTmpObject[0];
								$this->_oCurrentItem->informationsystem_group_id = $this->_oCurrentGroup->id;
							}
							else
							{
								// группа не найдена, присваиваем informationsystem_groups_guid текущей группе
								$this->_oCurrentGroup->guid = $sData;
								$this->_oCurrentGroup->id && $this->_doSaveGroup($this->_oCurrentGroup);
							}
						break;
						// Передан GUID родительской группы
						case 'informationsystem_groups_parent_guid':
							$oTmpObject = $sData != 'ID00000000'
								? $this->_oCurrentInformationsystem->Informationsystem_Groups->getByGuid($sData, FALSE)
								: Core_Entity::factory('Informationsystem_Group', 0);

							if (!is_null($oTmpObject))
							{
								if ($oTmpObject->id != $this->_oCurrentGroup->id)
								{
									$this->_oCurrentGroup->parent_id = $oTmpObject->id;
									$this->_oCurrentGroup->id
										&& $this->_oCurrentGroup->save()
										&& $this->_incUpdatedGroups($this->_oCurrentGroup->id);
								}

								//$this->_oCurrentItem->Informationsystem_Group_id = $oTmpObject->id;
							}
						break;
						// Дополнительные группы для инфоэлемента (CML_ID), где нужно создавать ярлыки
						case 'additional_group':
							$aShortcuts = explode(',', $sData);
							$this->_aAdditionalGroups = array_merge($this->_aAdditionalGroups, $aShortcuts);
						break;
						// Идентификатор инфоэлемента
						case 'informationsystem_items_item_id':
							$oTmpObject = Core_Entity::factory("Informationsystem_Item")->find($sData);
							if (!is_null($oTmpObject->id))
							{
								//$this->_oCurrentItem->id = $oTmpObject->id;
								$this->_oCurrentItem = $oTmpObject;
							}
						break;
						// Передано название инфоэлемента
						case 'informationsystem_items_name':
							$this->_oCurrentItem->name = $sData;
						break;
						// Передана дата добавления инфоэлемента
						case 'informationsystem_items_date_time':
							if (preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData))
							{
								$this->_oCurrentItem->datetime = $sData;
							}
							else
							{
								$this->_oCurrentItem->datetime = Core_Date::datetime2sql($sData);
							}
						break;
						// Передано описание инфоэлемента
						case 'informationsystem_items_description':
							$this->_oCurrentItem->description = $sData;
						break;
						// Передан текст инфоэлемента
						case 'informationsystem_items_text':
							$this->_oCurrentItem->text = $sData;
						break;
						// Передана большая картинка инфоэлемента, обработка будет после вставки инфоэлемента
						case 'informationsystem_items_image':
							$this->_sBigImageFile = $sData;
						break;
						// Передана малая картинка инфоэлемента, обработка будет после вставки инфоэлемента
						case 'informationsystem_items_small_image':
							$this->_sSmallImageFile = $sData;
						break;
						// Переданы метки инфоэлемента, обработка будет после вставки инфоэлемента
						case 'informationsystem_items_label':
							$this->_sCurrentTags = $sData;
						break;
						// Передана активность инфоэлемента
						case 'informationsystem_items_is_active':
							$this->_oCurrentItem->active = $sData;
						break;
						// Передан порядок сортировки инфоэлемента
						case 'informationsystem_items_order':
							$this->_oCurrentItem->sorting = $sData;
						break;
						// Передан путь инфоэлемента
						case 'informationsystem_items_path':
							// Товар не был найден ранее, например, по артикулу
							if (!$this->_oCurrentItem->id)
							{
								$oTmpObject = $this->_oCurrentInformationsystem->Informationsystem_Items;
								$oTmpObject->queryBuilder()
									->where('path', '=', $sData)
									->where('informationsystem_group_id', '=', $this->_oCurrentGroup->id);

								$oTmpObject = $oTmpObject->findAll(FALSE);

								count($oTmpObject) && $this->_oCurrentItem = $oTmpObject[0];
							}

							$this->_oCurrentItem->path = $sData;
						break;
						// Передан Seo Title для инфоэлемента
						case 'informationsystem_items_seo_title':
							$this->_oCurrentItem->seo_title = $sData;
						break;
						// Передан Seo Description для инфоэлемента
						case 'informationsystem_items_seo_description':
							$this->_oCurrentItem->seo_description = $sData;
						break;
						// Передан Seo Keywords для инфоэлемента
						case 'informationsystem_items_seo_keywords':
							$this->_oCurrentItem->seo_keywords = $sData;
						break;
						// Передан флаг индексации инфоэлемента
						case 'informationsystem_items_indexation':
							$this->_oCurrentItem->indexing = $sData;
						break;
						// Передан идентификатор пользователя сайта
						case 'site_users_id':
							$this->_oCurrentItem->siteuser_id = $sData;
						break;
						case 'informationsystem_items_putend_date':
							// Передана дата завершения публикации, проверяем ее на соответствие стандарту времени MySQL
							$this->_oCurrentItem->end_datetime = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData)
								? $sData
								: Core_Date::datetime2sql($sData);
						break;
						case 'informationsystem_items_putoff_date':
							// Передана дата завершения публикации, проверяем ее на соответствие стандарту времени MySQL
							$this->_oCurrentItem->start_datetime = preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData)
								? $sData
								: Core_Date::datetime2sql($sData);
						break;
						case 'informationsystem_items_guid':
							// Товар не был найден ранее, например, по артикулу
							if (!$this->_oCurrentItem->id)
							{
								$oTmpObject = $this->_oCurrentInformationsystem->Informationsystem_Items;
								$oTmpObject->queryBuilder()
									->where('guid', '=', $sData)
									->limit(1);

								$oTmpObject = $oTmpObject->findAll(FALSE);

								count($oTmpObject) && $this->_oCurrentItem = $oTmpObject[0];
							}

							$this->_oCurrentItem->guid = $sData;
						break;
						default:
							$sFieldName = $this->csv_fields[$iKey];

							if (strpos($sFieldName, "propsmall-") === 0)
							{
								// Дополнительный файл дополнительного свойства/Малое изображение картинки дополнительного свойства
								$aPropertySmallInfo = explode("-", $sFieldName);

								$this->_aExternalPropertiesSmall[$aPropertySmallInfo[1]] = $sData;
							}

							if (strpos($sFieldName, "propdesc-") === 0)
							{
								// Описание дополнительного свойства
								$aTmpExplode = explode('-', $sFieldName);
								$this->_aExternalPropertiesDesc[$aTmpExplode[1]] = $sData;
							}

							if (strpos($sFieldName, "prop-") === 0)
							{
								// Основной файл дополнительного свойства/Большое изображение картинки дополнительного свойства
								$aPropertyInfo = explode("-", $sFieldName);

								$this->_aExternalProperties[$aPropertyInfo[1]] = $sData;
							}

							if (strpos($sFieldName, "prop_group-") === 0)
							{
								// Дополнительное свойство группы
								$iPropertyId = explode("-", $sFieldName);

								$iPropertyId = $iPropertyId[1];

								$oProperty = Core_Entity::factory('Property', $iPropertyId);

								Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.onBeforeImportGroupProperty', $this, array($this->_oCurrentInformationsystem, $this->_oCurrentGroup, $oProperty, $sData));

								$aPropertyValues = $oProperty->getValues($this->_oCurrentGroup->id, FALSE);

								$oProperty_Value = isset($aPropertyValues[0])
									? $aPropertyValues[0]
									: $oProperty->createNewValue($this->_oCurrentGroup->id);

								switch ($oProperty->type)
								{
									// Файл
									case 2:
										// Для гарантии получения идентификатора группы
										$this->_oCurrentGroup->save();
										$this->_incUpdatedGroups($this->_oCurrentGroup->id);

										// Папка назначения
										$sDestinationFolder = $this->_oCurrentGroup->getGroupPath();

										// Файл-источник
										$sSourceFile = $this->imagesPath . (
											strtoupper($this->encoding) == 'UTF-8'
												? $sData
												: Core_File::convertfileNameToLocalEncoding($sData)
										);
										$sSourceFileBaseName = basename($sSourceFile, '');

										$bHttp = strpos(strtolower($sSourceFile), 'http://') === 0;

										if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
										{
											// Создаем папку назначения
											$this->_oCurrentGroup->createDir();

											if ($bHttp)
											{
												// Файл из WEB'а, создаем временный файл
												$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");
												// Копируем содержимое WEB-файла в локальный временный файл
												file_put_contents($sTempFileName, file_get_contents($sSourceFile));
												// Файл-источник равен временному файлу
												$sSourceFile = $sTempFileName;
											}
											else
											{
												$sSourceFile = CMS_FOLDER . ltrim($sSourceFile, '/\\');
											}

											if (!$this->_oCurrentInformationsystem->change_filename)
											{
												$sTargetFileName = $sSourceFileBaseName;
											}
											else
											{
												$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
												$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
													? '.jpg'
													: ".{$sTargetFileExtension}";

												$oProperty_Value->save();
												$sTargetFileName = "informationsystem_property_file_{$this->_oCurrentGroup->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
											}

											// Создаем массив параметров для загрузки картинок элементу
											$aPicturesParam = array();
											$aPicturesParam['large_image_isset'] = TRUE;
											$aPicturesParam['large_image_source'] = $sSourceFile;
											$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
											$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;

											$aPicturesParam['watermark_file_path'] = $this->_oCurrentInformationsystem->getWatermarkFilePath();
											$aPicturesParam['watermark_position_x'] = $this->_oCurrentInformationsystem->watermark_default_position_x;
											$aPicturesParam['watermark_position_y'] = $this->_oCurrentInformationsystem->watermark_default_position_y;
											$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio;

											// Малое изображение для дополнительных свойств создается всегда
											$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
											$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
											$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
											$aPicturesParam['create_small_image_from_large'] = TRUE;
											$aPicturesParam['small_image_max_width'] = $this->_oCurrentInformationsystem->group_image_small_max_width;
											$aPicturesParam['small_image_max_height'] = $this->_oCurrentInformationsystem->group_image_small_max_height;
											$aPicturesParam['small_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_small_image;
											$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio_small;

											$aPicturesParam['large_image_max_width'] = $this->_oCurrentInformationsystem->group_image_large_max_width;
											$aPicturesParam['large_image_max_height'] = $this->_oCurrentInformationsystem->group_image_large_max_height;
											$aPicturesParam['large_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_large_image;

											// Удаляем старое большое изображение
											if ($oProperty_Value->file != '')
											{
												try
												{
													Core_File::delete($sDestinationFolder . $oProperty_Value->file);
												} catch (Exception $e) {
												}
											}

											// Удаляем старое малое изображение
											if ($oProperty_Value->file_small != '')
											{
												try
												{
													Core_File::delete($sDestinationFolder . $oProperty_Value->file_small);
												} catch (Exception $e) {
												}
											}

											try {
												Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
												$aTmpReturn = Core_Event::getLastReturn();
												is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

												$result = Core_File::adminUpload($aPicturesParam);
											}
											catch (Exception $e)
											{
												Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
													? $e->getMessage()
													: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
												, 'error');

												$result = array('large_image' => FALSE, 'small_image' => FALSE);
											}

											if ($result['large_image'])
											{
												$oProperty_Value->file = $sTargetFileName;
												$oProperty_Value->file_name = '';
											}

											if ($result['small_image'])
											{
												$oProperty_Value->file_small = "small_{$sTargetFileName}";
												$oProperty_Value->file_small_name = '';
											}

											if (strpos(basename($sSourceFile), "CMS") === 0)
											{
												// Файл временный, подлежит удалению
												Core_File::delete($sSourceFile);
											}
										}
									break;
									// Список
									case 3:
										if (Core::moduleIsActive('list'))
										{
											$oListItem = Core_Entity::factory('List', $oProperty->list_id)
												->List_Items
												->getByValue($sData, FALSE);

											if (is_null($oListItem))
											{
												$oListItem = Core_Entity::factory('List_Item')
													->list_id($oProperty->list_id)
													->value($sData)
													->save();
											}

											$oProperty_Value->setValue($oListItem->id);
										}
									break;
									case 5: // Informationsystem
										$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getByName($sData);
										if ($oInformationsystem_Item)
										{
											$oProperty_Value->setValue($oInformationsystem_Item->id);
										}
										elseif (is_numeric($sData))
										{
											$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getById($sData);

											$oInformationsystem_Item && $oProperty_Value->setValue($oInformationsystem_Item->id);
										}
									break;
									case 8:
										if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sData))
										{
											$sData = Core_Date::datetime2sql($sData);
										}

										$oProperty_Value->setValue($sData);
									break;
									case 9:
										if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sData))
										{
											$sData = Core_Date::datetime2sql($sData);
										}

										$oProperty_Value->setValue($sData);
									break;
									case 11: // Float
										$sData = Shop_Controller::convertDecimal($sData);
										$oProperty_Value->setValue($sData);
									break;
									case 12: // Shop
										$oShop_Item = $oProperty->Shop->Shop_Items->getByName($sData);
										if ($oShop_Item)
										{
											$oProperty_Value->setValue($oShop_Item->id);
										}
										elseif (is_numeric($sData))
										{
											$oShop_Item = $oProperty->Shop->Shop_Items->getById($sData);

											$oShop_Item && $oProperty_Value->setValue($oShop_Item->id);
										}
									break;
									default:
										$oProperty_Value->setValue($sData);
									break;
								}

								$oProperty_Value->save();
							}
						break;
					}
				}
			}

			!$this->_oCurrentItem->id
				&& is_null($this->_oCurrentItem->path)
				&& $this->_oCurrentItem->path = '';

			if ($this->_oCurrentItem->id && $this->importAction == 2)
			{
				// если сказано - оставить без изменений, затираем все изменения
				$this->_oCurrentItem = Core_Entity::factory('Informationsystem_Item')->find($this->_oCurrentItem->id);
				$this->_sBigImageFile = '';
				$this->_sSmallImageFile = '';
				$this->deleteImage = 0;
			}

			// Обязательно после обработки тегов, т.к. иначе ORM сохранит товар косвенно.
			$this->_oCurrentItem->informationsystem_id = $this->_oCurrentInformationsystem->id;

			if (($this->_oCurrentItem->id
			//&& $this->importAction == 1
			&& !is_null($this->_oCurrentItem->name)
			&& $this->_oCurrentItem->save()))
			{
				$this->_incUpdatedItems($this->_oCurrentItem->id);
			}
			elseif (!is_null($this->_oCurrentItem->name)
			&& $this->_oCurrentItem->save())
			{
				$this->_incInsertedItems($this->_oCurrentItem->id);
			}

			$aTagsName = array();
			/*if (!$this->_oCurrentItem->id)
			{*/
			if (Core::moduleIsActive('tag'))
			{
				// Вставка тэгов автоматически разрешена
				if ($this->_sCurrentTags == '' && $this->_oCurrentInformationsystem->apply_tags_automatically)
				{
					$sTmpString = '';
					$sTmpString .= $this->_oCurrentItem->name ? ' ' . $this->_oCurrentItem->name : '';
					$sTmpString .= $this->_oCurrentItem->description ? ' ' . $this->_oCurrentItem->description : '';
					$sTmpString .= $this->_oCurrentItem->text ? ' ' . $this->_oCurrentItem->text : '';

					// получаем хэш названия и описания группы
					$aText = Core_Str::getHashes($sTmpString, array ('hash_function' => 'crc32'));

					$aText = array_unique($aText);

					// Получаем список меток
					$aTags = $this->_getTags();

					if (count($aTags))
					{
						// Удаляем уже существующие связи с метками
						$this->_oCurrentItem->Tag_Informationsystem_Items->deleteAll(FALSE);

						foreach ($aTags as $iTagId => $sTagName)
						{
							$aTmpTags = Core_Str::getHashes($sTagName, array ('hash_function' => 'crc32'));
							$aTmpTags = array_unique($aTmpTags);

							if (count($aText) >= count($aTmpTags))
							{
								// Расчитываем пересечение
								$iIntersect = count(array_intersect($aText, $aTmpTags));

								$iCoefficient = count($aTmpTags) != 0
									? $iIntersect / count($aTmpTags)
									: 0;

								// Найдено полное вхождение
								if ($iCoefficient == 1)
								{
									// Если тэг еще не учтен
									if (!in_array($sTagName, $aTmpTags))
									{
										// Добавляем в массив
										$aTagsName[] = $sTagName;

										// Add relation
										$this->_oCurrentItem->add(
											Core_Entity::factory('Tag', $iTagId)
										);
									}
								}
							}
						}
					}
				}
				elseif ($this->_sCurrentTags != '')
				{
					$this->_oCurrentItem->id && $this->_oCurrentItem->applyTags($this->_sCurrentTags);
				}
			}
			//}

			if ($this->_oCurrentItem->seo_keywords == '' && count($aTagsName))
			{
				$this->_oCurrentItem->seo_keywords = implode(", ", $aTagsName);
				$this->_oCurrentItem->save();
			}

			if ($this->searchIndexation && $this->_oCurrentGroup->id)
			{
				Core_Entity::factory('Informationsystem_Group', $this->_oCurrentGroup->id)->index();
			}

			if ($this->_oCurrentItem->id)
			{
				// Обрабатываем ярлыки
				if (count($this->_aAdditionalGroups))
				{
					$this->_aAdditionalGroups = array_map('trim', $this->_aAdditionalGroups);

					$oInformationsystem_Groups = $this->_oCurrentInformationsystem->Informationsystem_Groups;
					$oInformationsystem_Groups
						->queryBuilder()
						->where('guid', 'IN', $this->_aAdditionalGroups)
						->where('shortcut_id', '=', 0);

					$aInformationsystem_Groups = $oInformationsystem_Groups->findAll(FALSE);

					foreach ($aInformationsystem_Groups as $oInformationsystem_Group)
					{
						$oInformationsystem_Items = $this->_oCurrentInformationsystem->Informationsystem_Items;
						$oInformationsystem_Items->queryBuilder()
							->where('shortcut_id', '=', $this->_oCurrentItem->id)
							->where('informationsystem_group_id', '=', $oInformationsystem_Group->id)
							->limit(1);

						$iCountShortcuts = $oInformationsystem_Items->getCount(FALSE);

						if (!$iCountShortcuts)
						{
							Core_Entity::factory('Informationsystem_Item')
								->informationsystem_group_id($oInformationsystem_Group->id)
								->shortcut_id($this->_oCurrentItem->id)
								->informationsystem_id($this->_oCurrentInformationsystem->id)
								->save();
						}
					}
				}

				if (/*!is_null($this->_sBigImageFile) && */$this->_sBigImageFile != ''/* && $this->importAction != 2*/)
				{
					// Папка назначения
					$sDestinationFolder = $this->_oCurrentItem->getItemPath();

					// Файл-источник
					$sSourceFile = $this->imagesPath . (
						strtoupper($this->encoding) == 'UTF-8'
							? $this->_sBigImageFile
							: Core_File::convertfileNameToLocalEncoding($this->_sBigImageFile)
					);
					$sSourceFileBaseName = basename($sSourceFile, '');

					$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

					if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension'])
						|| $bHttp)
					{
						// Удаляем папку назначения вместе со всеми старыми файлами
						//Core_File::deleteDir($sDestinationFolder);

						// Создаем папку назначения
						$this->_oCurrentItem->createDir();

						if ($bHttp)
						{
							// Файл из WEB'а, создаем временный файл
							$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");
							// Копируем содержимое WEB-файла в локальный временный файл
							file_put_contents($sTempFileName, file_get_contents($sSourceFile));
							// Файл-источник равен временному файлу
							$sSourceFile = $sTempFileName;
						}
						else
						{
							$sSourceFile = CMS_FOLDER . trim(Core_File::pathCorrection($sSourceFile), DIRECTORY_SEPARATOR);
						}

						if (!$this->_oCurrentInformationsystem->change_filename)
						{
							$sTargetFileName = $sSourceFileBaseName;
						}
						else
						{
							$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
							$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
								? '.jpg'
								: ".{$sTargetFileExtension}";

							$sTargetFileName = "informationsystem_items_catalog_image{$this->_oCurrentItem->id}{$sTargetFileExtension}";
						}

						if ($this->_oCurrentItem->image_large != '')
						{
							try
							{
								Core_File::delete($sDestinationFolder . $this->_oCurrentItem->image_large);
							} catch (Exception $e) {}
						}

						// Создаем массив параметров для загрузки картинок элементу
						$aPicturesParam = array();
						$aPicturesParam['large_image_isset'] = TRUE;
						$aPicturesParam['large_image_source'] = $sSourceFile;
						$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
						$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
						$aPicturesParam['watermark_file_path'] = $this->_oCurrentInformationsystem->getWatermarkFilePath();
						$aPicturesParam['watermark_position_x'] = $this->_oCurrentInformationsystem->watermark_default_position_x;
						$aPicturesParam['watermark_position_y'] = $this->_oCurrentInformationsystem->watermark_default_position_y;
						$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio;

						// Проверяем, передали ли нам малое изображение
						if (is_null($this->_oCurrentItem->image_small) || $this->_oCurrentItem->image_small == '')
						{
							// Малое изображение не передано, создаем его из большого
							$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
							$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
							$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
							$aPicturesParam['create_small_image_from_large'] = TRUE;
							$aPicturesParam['small_image_max_width'] = $this->_oCurrentInformationsystem->image_small_max_width;
							$aPicturesParam['small_image_max_height'] = $this->_oCurrentInformationsystem->image_small_max_height;
							$aPicturesParam['small_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_small_image;
							$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio_small;
						}
						else
						{
							$aPicturesParam['create_small_image_from_large'] = FALSE;
						}

						$aPicturesParam['large_image_max_width'] = $this->_oCurrentInformationsystem->image_large_max_width;
						$aPicturesParam['large_image_max_height'] = $this->_oCurrentInformationsystem->image_large_max_height;
						$aPicturesParam['large_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_large_image;

						try
						{
							Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
							$aTmpReturn = Core_Event::getLastReturn();
							is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

							$result = Core_File::adminUpload($aPicturesParam);
						}
						catch (Exception $e)
						{
							Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
								? $e->getMessage()
								: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
							, 'error');

							$result = array('large_image' => FALSE, 'small_image' => FALSE);
						}

						if ($result['large_image'])
						{
							$this->_oCurrentItem->image_large = $sTargetFileName;
							$this->_oCurrentItem->setLargeImageSizes();
						}

						if ($result['small_image'])
						{
							$this->_oCurrentItem->image_small = "small_{$sTargetFileName}";
							$this->_oCurrentItem->setSmallImageSizes();
						}

						if (strpos(basename($sSourceFile), "CMS") === 0)
						{
							// Файл временный, подлежит удалению
							Core_File::delete($sSourceFile);
						}
					}
				}
				elseif ($this->deleteImage)
				{
					// Удалить текущее большое изображение
					if ($this->_oCurrentItem->image_large != '')
					{
						try
						{
							Core_File::delete($this->_oCurrentItem->getItemPath() . $this->_oCurrentItem->image_large);
						} catch (Exception $e) {}
					}
				}

				if ($this->_sSmallImageFile != ''
				|| ($this->_sBigImageFile != ''
				&& !$this->deleteImage))
				{
					$this->_sSmallImageFile == '' && $this->_sSmallImageFile = $this->_sBigImageFile;

					// Папка назначения
					$sDestinationFolder = $this->_oCurrentItem->getItemPath();

					// Файл-источник
					$sSourceFile = $this->imagesPath . (
						strtoupper($this->encoding) == 'UTF-8'
							? $this->_sSmallImageFile
							: Core_File::convertfileNameToLocalEncoding($this->_sSmallImageFile)
					);

					$sSourceFileBaseName = basename($sSourceFile, '');

					$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

					if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
					{
						// Создаем папку назначения
						$this->_oCurrentItem->createDir();

						if ($bHttp)
						{
							// Файл из WEB'а, создаем временный файл
							$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");
							// Копируем содержимое WEB-файла в локальный временный файл
							file_put_contents($sTempFileName, file_get_contents($sSourceFile));
							// Файл-источник равен временному файлу
							$sSourceFile = $sTempFileName;
						}
						else
						{
							$sSourceFile = CMS_FOLDER . trim(Core_File::pathCorrection($sSourceFile), DIRECTORY_SEPARATOR);
						}

						if (!$this->_oCurrentInformationsystem->change_filename)
						{
							$sTargetFileName = "small_{$sSourceFileBaseName}";
						}
						else
						{
							$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
							$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
								? '.jpg'
								: ".{$sTargetFileExtension}";

							$sTargetFileName = "small_informationsystem_items_catalog_image{$this->_oCurrentItem->id}{$sTargetFileExtension}";
						}

						if (is_file($sSourceFile) && filesize($sSourceFile))
						{
							// Удаляем старое малое изображение
							if ($this->_oCurrentItem->image_small != '')
							{
								try
								{
									Core_File::delete($this->_oCurrentItem->getItemPath() . $this->_oCurrentItem->image_small);
								} catch (Exception $e) {}
							}

							$aPicturesParam = array();
							$aPicturesParam['small_image_source'] = $sSourceFile;
							$aPicturesParam['small_image_name'] = $sSourceFileBaseName;
							$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileName;
							$aPicturesParam['create_small_image_from_large'] = FALSE;
							$aPicturesParam['small_image_max_width'] = $this->_oCurrentInformationsystem->image_small_max_width;
							$aPicturesParam['small_image_max_height'] = $this->_oCurrentInformationsystem->image_small_max_height;
							$aPicturesParam['small_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_small_image;
							$aPicturesParam['watermark_file_path'] = $this->_oCurrentInformationsystem->getWatermarkFilePath();
							$aPicturesParam['watermark_position_x'] = $this->_oCurrentInformationsystem->watermark_default_position_x;
							$aPicturesParam['watermark_position_y'] = $this->_oCurrentInformationsystem->watermark_default_position_y;
							$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio_small;

							try {
								Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
								$aTmpReturn = Core_Event::getLastReturn();
								is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

								$result = Core_File::adminUpload($aPicturesParam);
							}
							catch (Exception $e)
							{
								Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
									? $e->getMessage()
									: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
								, 'error');

								$result = array('small_image' => FALSE);
							}

							if ($result['small_image'])
							{
								$this->_oCurrentItem->image_small = $sTargetFileName;
								$this->_oCurrentItem->setSmallImageSizes();
							}
						}

						if (strpos(basename($sSourceFile), "CMS") === 0)
						{
							// Файл временный, подлежит удалению
							Core_File::delete($sSourceFile);
						}
					}

					$this->_sSmallImageFile = '';
				}
				elseif ($this->deleteImage)
				{
					if ($this->_oCurrentItem->image_small != '')
					{
						try
						{
							Core_File::delete($this->_oCurrentItem->getItemPath() . $this->_oCurrentItem->image_small);
						} catch (Exception $e) {}
					}
				}
				$this->_sBigImageFile = '';

				// WARNING
				foreach ($this->_aExternalProperties as $iPropertyID => $sPropertyValue)
				{
					$oProperty = Core_Entity::factory('Property')->find($iPropertyID);

					Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.onBeforeImportItemProperty', $this, array($this->_oCurrentInformationsystem, $this->_oCurrentItem, $oProperty, $sPropertyValue));

					$aPropertyValues = $oProperty->getValues($this->_oCurrentItem->id, FALSE);

					if (!isset($this->_aClearedPropertyValues[$this->_oCurrentItem->id]) || !in_array($oProperty->guid, $this->_aClearedPropertyValues[$this->_oCurrentItem->id]))
					{
						foreach ($aPropertyValues as $oPropertyValue)
						{
							$oProperty->type == 2 && $oPropertyValue->setDir($this->_oCurrentItem->getItemPath());
							$oPropertyValue->delete();
						}

						$aPropertyValues = array();

						$this->_aClearedPropertyValues[$this->_oCurrentItem->id][] = $oProperty->guid;
					}

					if ($oProperty->multiple)
					{
						$oProperty_Value = $oProperty->createNewValue($this->_oCurrentItem->id);
					}
					else
					{
						$oProperty_Value = isset($aPropertyValues[0])
							? $aPropertyValues[0]
							: $oProperty->createNewValue($this->_oCurrentItem->id);
					}

					switch ($oProperty->type)
					{
						// Файл
						case 2:

							// Папка назначения
							$sDestinationFolder = $this->_oCurrentItem->getItemPath();

							// Файл-источник
							$sSourceFile = $this->imagesPath . (
								strtoupper($this->encoding) == 'UTF-8'
									? $sPropertyValue
									: Core_File::convertfileNameToLocalEncoding($sPropertyValue)
							);

							$sSourceFileBaseName = basename($sSourceFile, '');

							$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://") === 0;

							if (Core_File::isValidExtension($sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
							{
								// Создаем папку назначения
								$this->_oCurrentItem->createDir();

								if ($bHttp)
								{
									// Файл из WEB'а, создаем временный файл
									$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");
									// Копируем содержимое WEB-файла в локальный временный файл
									file_put_contents($sTempFileName, file_get_contents($sSourceFile));
									// Файл-источник равен временному файлу
									$sSourceFile = $sTempFileName;
								}
								else
								{
									$sSourceFile = CMS_FOLDER . ltrim($sSourceFile, '/\\');
								}

								if (!$this->_oCurrentInformationsystem->change_filename)
								{
									$sTargetFileName = $sSourceFileBaseName;
								}
								else
								{
									$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
									$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
										? '.jpg'
										: ".{$sTargetFileExtension}";

									$oProperty_Value->save();
									$sTargetFileName = "informationsystem_property_file_{$this->_oCurrentItem->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
									//$sTargetFileName = "shop_property_file_{$this->_oCurrentItem->id}_{$oProperty->id}{$sTargetFileExtension}";
								}

								// Создаем массив параметров для загрузки картинок элементу
								$aPicturesParam = array();
								$aPicturesParam['large_image_isset'] = TRUE;
								$aPicturesParam['large_image_source'] = $sSourceFile;
								$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
								$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
								$aPicturesParam['watermark_file_path'] = $this->_oCurrentInformationsystem->getWatermarkFilePath();
								$aPicturesParam['watermark_position_x'] = $this->_oCurrentInformationsystem->watermark_default_position_x;
								$aPicturesParam['watermark_position_y'] = $this->_oCurrentInformationsystem->watermark_default_position_y;
								$aPicturesParam['large_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio;
								//$aPicturesParam['large_image_max_width'] = $this->_oCurrentInformationsystem->image_large_max_width;
								$aPicturesParam['large_image_max_width'] = $oProperty->image_large_max_width;
								//$aPicturesParam['large_image_max_height'] = $this->_oCurrentInformationsystem->image_large_max_height;
								$aPicturesParam['large_image_max_height'] = $oProperty->image_large_max_height;
								$aPicturesParam['large_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_large_image;

								if (isset($this->_aExternalPropertiesSmall[$iPropertyID]))
								{
									// Малое изображение передано
									$aPicturesParam['create_small_image_from_large'] = FALSE;
								}
								else
								{
									// Малое изображение не передано
									$aPicturesParam['create_small_image_from_large'] = TRUE;
									$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
									$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
									$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
									$aPicturesParam['small_image_max_width'] = $oProperty->image_small_max_width;
									$aPicturesParam['small_image_max_height'] = $oProperty->image_small_max_height;
									$aPicturesParam['small_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_small_image;
									$aPicturesParam['small_image_preserve_aspect_ratio'] = $aPicturesParam['large_image_preserve_aspect_ratio'];
								}

								// Удаляем старое большое изображение
								if ($oProperty_Value->file != '')
								{
									try
									{
										Core_File::delete($sDestinationFolder . $oProperty_Value->file);
									} catch (Exception $e) {}
								}

								// Удаляем старое малое изображение
								if ($oProperty_Value->file_small != '')
								{
									try
									{
										Core_File::delete($sDestinationFolder . $oProperty_Value->file_small);
									} catch (Exception $e) {}
								}

								try {
									Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
									$aTmpReturn = Core_Event::getLastReturn();
									is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

									$aResult = Core_File::adminUpload($aPicturesParam);
								}
								catch (Exception $e)
								{
									Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
										? $e->getMessage()
										: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
									, 'error');

									$aResult = array('large_image' => FALSE, 'small_image' => FALSE);
								}

								if ($aResult['large_image'])
								{
									$oProperty_Value->file = $sTargetFileName;
									$oProperty_Value->file_name = '';
								}

								if ($aResult['small_image'])
								{
									$oProperty_Value->file_small = "small_{$sTargetFileName}";
									$oProperty_Value->file_small_name = '';
								}

								if (isset($this->_aExternalPropertiesDesc[$iPropertyID]))
								{
									$oProperty_Value->file_description = $this->_aExternalPropertiesDesc[$iPropertyID];
								}

								clearstatcache();

								if (strpos(basename($sSourceFile), "CMS") === 0
									&& is_file($sSourceFile)
								)
								{
									// Файл временный, подлежит удалению
									Core_File::delete($sSourceFile);
								}
							}
						break;
						// Список
						case 3:
							if (Core::moduleIsActive('list'))
							{
								$oListItem = Core_Entity::factory('List_Item');
								$oListItem
									->queryBuilder()
									->where('list_id', '=', $oProperty->list_id)
									->where('value', '=', $sPropertyValue)
								;
								$oListItem = $oListItem->findAll(FALSE);

								if (count($oListItem))
								{
									$oProperty_Value->setValue($oListItem[0]->id);
								}
								else
								{
									$oProperty_Value->setValue(Core_Entity::factory('List_Item')
										->list_id($oProperty->list_id)
										->value($sPropertyValue)
										->save()
										->id
									);
								}
							}
						break;
						case 5: // Informationsystem
							$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getByName($sPropertyValue);
							if ($oInformationsystem_Item)
							{
								$oProperty_Value->setValue($oInformationsystem_Item->id);
							}
							elseif (is_numeric($sPropertyValue))
							{
								$oInformationsystem_Item = $oProperty->Informationsystem->Informationsystem_Items->getById($sPropertyValue);

								$oInformationsystem_Item && $oProperty_Value->setValue($oInformationsystem_Item->id);
							}
						break;
						case 8:
							if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $sPropertyValue))
							{
								$sPropertyValue = Core_Date::datetime2sql($sPropertyValue);
							}

							$oProperty_Value->setValue($sPropertyValue);
						break;
						case 9:
							if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $sPropertyValue))
							{
							 $sPropertyValue = Core_Date::datetime2sql($sPropertyValue);
							}

							$oProperty_Value->setValue($sPropertyValue);
						break;
						case 11: // Float
							$sPropertyValue = Shop_Controller::convertDecimal($sPropertyValue);
							$oProperty_Value->setValue($sPropertyValue);
						break;
						case 12: // Shop
							$oShop_Item = $oProperty->Shop->Shop_Items->getByName($sPropertyValue);
							if ($oShop_Item)
							{
								$oProperty_Value->setValue($oShop_Item->id);
							}
							elseif (is_numeric($sPropertyValue))
							{
								$oShop_Item = $oProperty->Shop->Shop_Items->getById($sPropertyValue);

								$oShop_Item && $oProperty_Value->setValue($oShop_Item->id);
							}
						break;
						default:
							Core_Event::notify(get_class($this) . '.onPreparePropertyValueDefault', $this, array($this->_oCurrentItem, $oProperty, $sPropertyValue));

							if (!is_null(Core_Event::getLastReturn()))
							{
								$sPropertyValue = Core_Event::getLastReturn();
							}

							$oProperty_Value->setValue($sPropertyValue);
						break;
					}

					$oProperty_Value->save();
				}

				foreach ($this->_aExternalPropertiesSmall as $iPropertyID => $sPropertyValue)
				{
					$oProperty = Core_Entity::factory('Property')->find($iPropertyID);

					$aPropertyValues = $oProperty->getValues($this->_oCurrentItem->id, FALSE);

					$oProperty_Value = isset($aPropertyValues[0])
						? $aPropertyValues[0]
						: $oProperty->createNewValue($this->_oCurrentItem->id);

					// Папка назначения
					$sDestinationFolder = $this->_oCurrentItem->getItemPath();

					// Файл-источник
					$sSourceFile = $this->imagesPath . $sPropertyValue;

					$sSourceFileBaseName = basename($sSourceFile, '');

					$bHttp = strpos(strtolower($sSourceFile), "http://") === 0 || strpos(strtolower($sSourceFile), "https://");

					if (Core_File::isValidExtension( $sSourceFile, Core::$mainConfig['availableExtension']) || $bHttp)
					{
						// Создаем папку назначения
						$this->_oCurrentItem->createDir();

						if ($bHttp)
						{
							// Файл из WEB'а, создаем временный файл
							$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "CMS");
							// Копируем содержимое WEB-файла в локальный временный файл
							file_put_contents($sTempFileName, file_get_contents($sSourceFile));
							// Файл-источник равен временному файлу
							$sSourceFile = $sTempFileName;
						}
						else
						{
							$sSourceFile = CMS_FOLDER . $sSourceFile;
						}

						if (!$this->_oCurrentInformationsystem->change_filename)
						{
							$sTargetFileName = "small_{$sSourceFileBaseName}";
						}
						else
						{
							$sTargetFileExtension = Core_File::getExtension($sSourceFileBaseName);
							$sTargetFileExtension = $sTargetFileExtension == '' || strlen($sTargetFileExtension) > 5
								? '.jpg'
								: ".{$sTargetFileExtension}";

							$oProperty_Value->save();
							$sTargetFileName = "small_informationsystem_property_file_{$this->_oCurrentItem->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
						}

						$aPicturesParam = array();
						$aPicturesParam['small_image_source'] = $sSourceFile;
						$aPicturesParam['small_image_name'] = $sSourceFileBaseName;
						$aPicturesParam['small_image_target'] = $sDestinationFolder . $sTargetFileName;
						$aPicturesParam['create_small_image_from_large'] = FALSE;
						$aPicturesParam['small_image_max_width'] = $this->_oCurrentInformationsystem->image_small_max_width;
						$aPicturesParam['small_image_max_height'] = $this->_oCurrentInformationsystem->image_small_max_height;
						$aPicturesParam['small_image_watermark'] = $this->_oCurrentInformationsystem->watermark_default_use_small_image;
						$aPicturesParam['watermark_file_path'] = $this->_oCurrentInformationsystem->getWatermarkFilePath();
						$aPicturesParam['watermark_position_x'] = $this->_oCurrentInformationsystem->watermark_default_position_x;
						$aPicturesParam['watermark_position_y'] = $this->_oCurrentInformationsystem->watermark_default_position_y;
						$aPicturesParam['small_image_preserve_aspect_ratio'] = $this->_oCurrentInformationsystem->preserve_aspect_ratio;

						// Удаляем старое малое изображение
						if ($oProperty_Value->file_small != '')
						{
							try
							{
								Core_File::delete($sDestinationFolder . $oProperty_Value->file_small);
							} catch (Exception $e) {}
						}

						try {
							Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.oBeforeAdminUpload', $this, array($aPicturesParam));
							$aTmpReturn = Core_Event::getLastReturn();
							is_array($aTmpReturn) && $aPicturesParam = $aTmpReturn;

							$aResult = Core_File::adminUpload($aPicturesParam);
						}
						catch (Exception $e)
						{
							Core_Message::show(strtoupper($this->encoding) == 'UTF-8'
								? $e->getMessage()
								: @iconv($this->encoding, "UTF-8//IGNORE//TRANSLIT", $e->getMessage())
							, 'error');

							$aResult = array('large_image' => FALSE, 'small_image' => FALSE);
						}

						if ($aResult['small_image'])
						{
							$oProperty_Value->file_small = $sTargetFileName;
							$oProperty_Value->file_small_name = '';
						}

						if (strpos(basename($sSourceFile), "CMS") === 0)
						{
							// Файл временный, подлежит удалению
							Core_File::delete($sSourceFile);
						}
					}

					$oProperty_Value->save();
				}
			} // end fields

			$iCounter++;

			//$this->_oCurrentItem->clear();
			$this->_oCurrentItem = Core_Entity::factory('Informationsystem_Item');
			$this->_oCurrentGroup = Core_Entity::factory('Informationsystem_Group', $this->_iCurrentGroupId);
			$this->_oCurrentGroup->informationsystem_id = $this->_oCurrentInformationsystem->id;

			$this->_oCurrentItem->informationsystem_group_id = $this->_oCurrentGroup->id;

			$this->_sBigImageFile = $this->_sSmallImageFile = '';

			// Очищаем временные массивы
			$this->_aExternalPropertiesSmall =
				$this->_aExternalProperties =
				$this->_aExternalPropertiesDesc =
				$this->_aAdditionalGroups = array();

			// Список меток для текущего инфоэлемента
			$this->_sCurrentTags = '';
		} // end line

		$iCurrentSeekPosition = !$aCsvLine ? $aCsvLine : ftell($fInputFile);

		fclose($fInputFile);

		Core_Event::notify('Informationsystem_Item_Import_Csv_Controller.onAfterImport', $this, array($this->_oCurrentInformationsystem, $iCurrentSeekPosition));

		return $iCurrentSeekPosition;
	}

	/**
	 * Array of cached tags
	 */
	protected $_aTags = NULL;

	/**
	 * Get cached tags of array
	 * @return array
	 */
	protected function _getTags()
	{
		if (is_null($this->_aTags))
		{
			$aTags = Core_Entity::factory('Tag')->findAll(FALSE);

			foreach ($aTags as $oTag)
			{
				$this->_aTags[$oTag->id] = $oTag->name;
			}
		}

		return $this->_aTags;
	}

	/**
	 * Convert object to string
	 * @return string
	 */
	public function __toString()
	{
		$aReturn = array();

		foreach ($this->_allowedProperties as $propertyName)
		{
			$aReturn[] = $propertyName . '=' . $this->$propertyName;
		}

		return implode(', ', $aReturn) . "<br/>";
	}

	/**
	 * Get CSV line from file
	 * @param handler file descriptor
	 * @return array
	 */
	public function getCSVLine($fileDescriptor)
	{
		if (strtoupper($this->encoding) != 'UTF-8' && defined('ALT_SITE_LOCALE'))
		{
			setlocale(LC_ALL, ALT_SITE_LOCALE);
		}

		$aCsvLine = @fgetcsv($fileDescriptor, 0, $this->separator, $this->limiter);

		if ($aCsvLine === FALSE)
		{
			return $aCsvLine;
		}

		setlocale(LC_ALL, SITE_LOCAL);
		setlocale(LC_NUMERIC, 'POSIX');

		return self::CorrectToEncoding($aCsvLine, 'UTF-8', $this->encoding);
	}

	/**
	 * Clear object
	 * @return self
	 */
	public function clear()
	{
		$this->_oCurrentInformationsystem =
		$this->_oCurrentGroup =
		$this->_oCurrentItem = NULL;

		$this->_aTags = NULL;

		return $this;
	}

	/**
	 * Execute some routine before serialization
	 * @return array
	 */
	public function __sleep()
	{
		$this->clear();

		return array_keys(
			get_object_vars($this)
		);
	}

	/**
	 * Reestablish any database connections that may have been lost during serialization and perform other reinitialization tasks
	 * @return self
	 */
	public function __wakeup()
	{
		date_default_timezone_set(Core::$mainConfig['timezone']);

		// Инициализация текущей группы
		$this->_oCurrentGroup = Core_Entity::factory('Informationsystem_Group', $this->_iCurrentGroupId
			? $this->_iCurrentGroupId
			: NULL);

		$this->init();

		$this->_oCurrentGroup->informationsystem_id = $this->_oCurrentInformationsystem->id;

		// Инициализация текущего инфоэлемента
		$this->_oCurrentItem = Core_Entity::factory('Informationsystem_Item');
		$this->_oCurrentItem->informationsystem_group_id = intval($this->_oCurrentGroup->id);

		return $this;
	}

	/**
	 * Correct CSV-line encoding
	 * @param array $sLine current CSV-file line
	 * @param string $encodeTo detination encoding
	 * @param string $encodeFrom source encoding
	 * @return array
	 */
	public static function CorrectToEncoding($sLine, $encodeTo, $encodeFrom = 'UTF-8')
	{
		if (is_array($sLine))
		{
			foreach ($sLine as $key => $value)
			{
				$sLine[$key] = self::CorrectToEncoding($value, $encodeTo, $encodeFrom);
			}
		}
		else
		{
			// Если кодировки не совпадают
			if (strtoupper($encodeTo) != strtoupper($encodeFrom))
			{
				// Перекодируем в указанную кодировку
				$sLine = @iconv($encodeFrom, $encodeTo . "//IGNORE//TRANSLIT", $sLine);
			}
		}

		return $sLine;
	}
}