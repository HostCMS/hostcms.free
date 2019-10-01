<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Import CML Controller (1С)
 * 2.0.8 - http://v8.1c.ru/edi/edi_stnd/90/CML208.XSD
 * 2.1.0 - http://v8.1c.ru/edi/edi_stnd/90/CML210.XSD
 *
 * Доступные методы:
 *
 * - importGroups(TRUE|FALSE) импортировать группы товаров, по умолчанию TRUE
 * - createShopItems(TRUE|FALSE) создавать новые товары, по умолчанию TRUE
 * - updateFields(array()) массив полей товара, которые необходимо обновлять при импорте CML товара, если не заполнен, то обновляются все поля. Пример массива array('marking', 'barcode', 'name', 'shop_group_id', 'text', 'description', 'images', 'taxes', 'shop_producer_id', 'prices', 'warehouses')
 * - skipProperties(array()) массив названий свойств, которые исключаются из импорта.
 * - searchIndexation(TRUE|FALSE) использовать событийную индексацию, по умолчанию FALSE
 * - itemDescription() имя поля товара, в которое загружать описание товаров, может принимать значения description, text. По умолчанию text
 * - shortDescription() название тега, из которого загружать описание товара, например МалоеОписание или КраткоеОписание, для импорта из свойства товара используйте конструкцию вида "ЗначенияСвойств/ЗначенияСвойства[./Ид='8f4f5254-31f4-11e9-7792-fa163e79bc3b']/Значение". По умолчанию МалоеОписание
 * - timeout(30) время выполнения шага импорта, получается из настроек PHP.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Import_Cml_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'importGroups',
		'createShopItems',
		'updateFields',
		'skipProperties',
		'searchIndexation',
		'itemDescription',
		'shortDescription',
		'iShopId',
		'iShopGroupId',
		'sShopDefaultPriceName',
		'sShopDefaultPriceGUID',
		'sPicturesPath',
		'importAction',
		'namespace',
		'timeout',
		'debug'
	);

	/**
	 * Return data
	 * @var array
	 */
	protected $_aReturn = array(
		'insertDirCount' => 0,
		'insertItemCount' => 0,
		'updateDirCount' => 0,
		'updateItemCount' => 0,
		'status' => 'success'
	);

	/**
	 * XML
	 * @var SimpleXMLElement
	 */
	protected $_oSimpleXMLElement = NULL;

	/**
	 * Get $this->_oSimpleXMLElement
	 * @return SimpleXMLElement
	 */
	public function getSimpleXMLElement()
	{
		return $this->_oSimpleXMLElement;
	}

	/**
	 * List of predefined base properties
	 * @var array
	 */
	protected $_aPredefinedBaseProperties = array(
		"HOSTCMS_TITLE",
		"HOSTCMS_DESCRIPTION",
		"HOSTCMS_KEYWORDS",
		"HOSTCMS_МЕТКИ",
		"YANDEX_MARKET",
		"ПРОДАВЕЦ",
		"ПРОИЗВОДИТЕЛЬ",
		"АКТИВНОСТЬ");

	/**
	 * List of predefined base properties (ЗначениеРеквизита)
	 * @var array
	 */
	protected $_aBaseAttributes = array(
		'ВЕС' => 'weight',
		'ДЛИНА' => 'length',
		'ШИРИНА' => 'width',
		'ВЫСОТА' => 'height',
	);

	/**
	 * List of predefined additional properties
	 * @var array
	 */
	protected $aPredefinedAdditionalProperties = array();

	/**
	 * List of base properties
	 * @var array
	 */
	protected $_aBaseProperties = array();

	/**
	 * Tax for default price
	 * @var object
	 */
	protected $_oTaxForBasePrice = NULL;

	/**
	 * Values of property
	 * @var array
	 */
	protected $_aPropertyValues = array();

	//protected $_temporaryPropertyFile = '';

	/**
	 * CML config
	 * @var array
	 */
	protected $_aConfig = NULL;

	/**
	 * Constructor.
	 * @param string $sXMLFilePath file path
	 */
	public function __construct($sXMLFilePath)
	{
		parent::__construct();

		$this->_aConfig = Core_Config::instance()->get('shop_cml', array()) + array(
			'predefinedAdditionalProperties' => array(),
			'catalogName' => array('Основной каталог товаров', 'Каталог товаров')
		);

		$this->aPredefinedAdditionalProperties = is_array($this->_aConfig['predefinedAdditionalProperties'])
			? $this->_aConfig['predefinedAdditionalProperties']
			: array();

		$this->_oSimpleXMLElement = new SimpleXMLElement(
			Core_File::read($sXMLFilePath), defined('LIBXML_PARSEHUGE') ? LIBXML_PARSEHUGE : 0
		);
		$this->sShopDefaultPriceName = 'РОЗНИЧНАЯ';
		$this->sShopDefaultPriceGUID = '';

		$this->itemDescription = 'text';
		$this->shortDescription = 'МалоеОписание';
		//$this->shortDescription = "ЗначенияСвойств/ЗначенияСвойства[./Ид='8f4f5254-31f4-11e9-7792-fa163e79bc3b']/Значение";

		$this->updateFields = $this->skipProperties = array();

		$this->importGroups = $this->createShopItems = TRUE;

		$this->searchIndexation = FALSE;

		$this->importAction = 1;

		$this->timeout = (!defined('DENY_INI_SET') || !DENY_INI_SET)
			? ini_get('max_execution_time')
			: 30;

		Core_File::mkdir(CMS_FOLDER . TMP_DIR . '1c_exchange_files');
	}

	/**
	 * Import group
	 * @param SimpleXMLElement $oXMLNode node
	 * @param int $iParentId parent ID
	 * @return self
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onBeforeImportShopGroup
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onAfterImportShopGroup
	 */
	protected function _importGroups($oXMLNode, $iParentId = 0)
	{
		foreach ($this->xpath($oXMLNode, 'Группа') as $oXMLGroupNode)
		{
			Core_Event::notify('Shop_Item_Import_Cml_Controller.onBeforeImportShopGroup', $this, array($oXMLGroupNode));

			$oShopGroup = Core_Entity::factory('Shop', $this->iShopId)
				->Shop_Groups
				->getByGuid(strval($oXMLGroupNode->Ид), FALSE);

			is_null($oShopGroup)
				&& $oShopGroup = Core_Entity::factory('Shop_Group');

			$oShopGroup->name = strval($oXMLGroupNode->Наименование);
			$oShopGroup->guid = strval($oXMLGroupNode->Ид);

			if (count($aDescriptionArray = $this->xpath($oXMLGroupNode, 'Описание')))
			{
				$oShopGroup->description = strval($aDescriptionArray[0]);
			}

			$oShopGroup->parent_id = $iParentId;
			$oShopGroup->shop_id = $this->iShopId;

			is_null($oShopGroup->id)
				? $this->_aReturn['insertDirCount']++
				: $this->_aReturn['updateDirCount']++;

			is_null($oShopGroup->path) && $oShopGroup->path= '';

			$oShopGroup->save()->clearCache();

			// Indexation
			$this->searchIndexation
				&& $oShopGroup->index();

			// Указание Картинка для группы не соответсвует формату обмена CML!
			$PictureData = strval($oXMLGroupNode->Картинка);

			if (strlen($PictureData)
				&& Core_File::isValidExtension($PictureData, Core::$mainConfig['availableExtension'])
			)
			{
				// Папка назначения
				$sDestinationFolder = $oShopGroup->getGroupPath();

				// Создаем папку назначения
				$oShopGroup->createDir();

				$sSourceFile = CMS_FOLDER . $this->sPicturesPath . ltrim($PictureData, '/\\');
				$sSourceFileBaseName = basename($sSourceFile, '');

				$oShop = $oShopGroup->Shop;

				if (!$oShop->change_filename)
				{
					$sTargetFileName = $sSourceFileBaseName;
				}
				else
				{
					$sTargetFileExtension = Core_File::getExtension($PictureData);

					if ($sTargetFileExtension != '')
					{
						$sTargetFileExtension = ".{$sTargetFileExtension}";
					}

					$sTargetFileName = "shop_group_image{$oShopGroup->id}{$sTargetFileExtension}";
				}

				// Создаем массив параметров для загрузки картинок элементу
				$aPicturesParam = array();
				$aPicturesParam['large_image_isset'] = TRUE;
				$aPicturesParam['large_image_source'] = $sSourceFile;
				$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
				$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;

				$aPicturesParam['watermark_file_path'] = $oShop->getWatermarkFilePath();
				$aPicturesParam['watermark_position_x'] = $oShop->watermark_default_position_x;
				$aPicturesParam['watermark_position_y'] = $oShop->watermark_default_position_y;
				$aPicturesParam['large_image_preserve_aspect_ratio'] = $oShop->preserve_aspect_ratio;

				$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
				$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
				$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
				$aPicturesParam['create_small_image_from_large'] = TRUE;
				$aPicturesParam['small_image_max_width'] = $oShop->group_image_small_max_width;
				$aPicturesParam['small_image_max_height'] = $oShop->group_image_small_max_height;
				$aPicturesParam['small_image_watermark'] = $oShop->watermark_default_use_small_image;
				$aPicturesParam['small_image_preserve_aspect_ratio'] = $aPicturesParam['large_image_preserve_aspect_ratio'];

				$aPicturesParam['large_image_max_width'] = $oShop->group_image_large_max_width;
				$aPicturesParam['large_image_max_height'] = $oShop->group_image_large_max_height;
				$aPicturesParam['large_image_watermark'] = $oShop->watermark_default_use_large_image;

				// Удаляем старое большое изображение
				if ($oShopGroup->image_large)
				{
					try
					{
						Core_File::delete($oShopGroup->getLargeFilePath());
					} catch (Exception $e) {}
				}

				// Удаляем старое малое изображение
				if ($oShopGroup->image_small)
				{
					try
					{
						Core_File::delete($oShopGroup->getSmallFilePath());
					} catch (Exception $e) {}
				}

				try {
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
					$oShopGroup->image_large = $sTargetFileName;
					$oShopGroup->setLargeImageSizes();
				}

				if ($result['small_image'])
				{
					$oShopGroup->image_small = "small_{$sTargetFileName}";
					$oShopGroup->setSmallImageSizes();
				}
				$oShopGroup->save();
			}

			// Дочерние группы
			foreach ($this->xpath($oXMLGroupNode, 'Группы') as $oSubGroup)
			{
				$this->_importGroups($oSubGroup, $oShopGroup->id);
			}

			Core_Event::notify('Shop_Item_Import_Cml_Controller.onAfterImportShopGroup', $this, array($oXMLGroupNode));
		}

		return $this;
	}

	/**
	 * Check if property exists in array and save it if so
	 * @return self
	 */
	protected function _addPredefinedAdditionalProperty($oShop_Item, SimpleXMLElement $oPropertyValue, $sValue, $bForcedAdd = FALSE)
	{
		if (is_null($oShop_Item))
		{
			return $this;
		}

		$sPropertyGUID = strval($oPropertyValue->Наименование);

		$sUpperGUID = mb_strtoupper($sPropertyGUID);

		if (isset($this->_aBaseAttributes[$sUpperGUID]))
		{
			$sFieldName = $this->_aBaseAttributes[$sUpperGUID];
			$oShop_Item->$sFieldName = Shop_Controller::convertDecimal($sValue);
			$oShop_Item->save();

			return $this;
		}

		if ($sPropertyGUID == 'ОписаниеВФорматеHTML' && strlen($sValue))
		{
			$oShop_Item->text = $sValue;
			$oShop_Item->save();
			return $this;
		}

		/*// для совместимости с МойСклад
		// не импортируется, т.к. затирает значение поля "Описание"
		if (mb_strtoupper($sPropertyGUID) == 'ПОЛНОЕ НАИМЕНОВАНИЕ')
		{
			$oShop_Item->description = $sValue;
			$oShop_Item->save();
			return $this;
		}*/

		if ($bForcedAdd
			|| (in_array(mb_strtoupper($sPropertyGUID), array_map('mb_strtoupper', $this->aPredefinedAdditionalProperties)) !== FALSE)
		)
		{
			$oProperty = $this->_getProperty($oPropertyValue);

			!is_null($oProperty)
				&& $this->_addItemPropertyValue($oShop_Item, $oProperty, $sValue);
		}
		return $this;
	}

	/**
	 * Add tax info
	 * @param SimpleXMLElement $oTax tax
	 * @return Shop_Tax
	 */
	protected function _addTax($oTax)
	{
		$sTaxName = strval($oTax->Наименование);
		$sTaxRate = strval($oTax->Ставка);
		$sTaxGUID = md5(mb_strtoupper($sTaxName));

		$oShopTax = Core_Entity::factory('Shop_Tax')->getByGuid($sTaxGUID, FALSE);

		if (is_null($oShopTax))
		{
			$oShopTax = Core_Entity::factory('Shop_Tax');
			$oShopTax->name = $sTaxName;
			$oShopTax->guid = $sTaxGUID;
			$oShopTax->rate = (
				$sTaxRate == ''
					? 0
					: ($sTaxRate == 'Без налога' || $sTaxRate == 'Без НДС'
						? 0
						: intval($sTaxRate)
					)
			);
			$oShopTax->tax_is_included = 0;
		}

		$oShopTax->save();

		return $oShopTax;
	}

	/**
	 * Add property to item
	 * @param Shop_Item_Model $oShopItem item
	 * @param Property_Model $oProperty
	 * @param string $sValue property value
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onAddItemPropertyValueDefault
	 */
	protected function _addItemPropertyValue(Shop_Item_Model $oShopItem, Property_Model $oProperty, $sValue)
	{
		$oShop = Core_Entity::factory('Shop', $this->iShopId);

		$oShop->Shop_Item_Property_For_Groups->allowAccess($oProperty->Shop_Item_Property->id, ($oShopItem->modification_id == 0
			? intval($oShopItem->Shop_Group->id)
			: intval($oShopItem->Modification->Shop_Group->id)
		));

		// Свойство список, но из 1С приходит не в виде справочника
		if (isset($this->_aPropertyValues[$oProperty->id])
			// Значение меняем/устанавливаем только в случае явного наличия элемента в значениях справочника!
			&& !isset($this->_aPropertyValues[$oProperty->id][$sValue])
		)
		{
			return $this;
		}

		// в _aPropertyValues не всегда выгружается весь перечень значений справочника!
		$value = isset($this->_aPropertyValues[$oProperty->id][$sValue])
			? $this->_aPropertyValues[$oProperty->id][$sValue]
			: $sValue;

		switch ($oProperty->type)
		{
			// целое число
			case 0:
				$changedValue = intval(Shop_Controller::instance()->convertPrice($value));
			break;
			// Файл
			case 2:
				$changedValue = NULL;
			break;
			// Список
			case 3:
				if (Core::moduleIsActive('list') && $oProperty->list_id)
				{
					$oListItem = Core_Entity::factory('List', $oProperty->list_id)
						->List_Items
						->getByValue($value, FALSE);

					if (is_null($oListItem))
					{
						$oListItem = Core_Entity::factory('List_Item')
							->list_id($oProperty->list_id)
							->value($value)
							->save();
					}
					$changedValue = $oListItem->id;
				}
				else
				{
					$changedValue = NULL;
				}
			break;
			case 7:
				$value = mb_strtolower($value);

				if ($value == 'true' || $value == 'да')
				{
					$changedValue = 1;
				}
				elseif ($value == 'false' || $value == 'нет')
				{
					$changedValue = 0;
				}
				else
				{
					$changedValue = (boolean)$value === TRUE ? 1 : 0;
				}
			break;
			case 8:
				if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $value))
				{
					$changedValue = Core_Date::datetime2sql($value);
				}
				else
				{
					$changedValue = NULL;
				}
			break;
			case 9:
				if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $value))
				{
					$changedValue = Core_Date::datetime2sql($value);
				}
				else
				{
					$changedValue = NULL;
				}
			break;
			default:
				Core_Event::notify(get_class($this) . '.onAddItemPropertyValueDefault', $this, array($oShopItem, $oProperty, $value));

				$changedValue = is_null(Core_Event::getLastReturn())
					? nl2br($value)
					: Core_Event::getLastReturn();
			break;
		}

		if (!is_null($changedValue))
		{
			$aPropertyValues = $oProperty->getValues($oShopItem->id, FALSE);
			if ($oProperty->multiple)
			{
				foreach ($aPropertyValues as $oProperty_Value)
				{
					if ($oProperty_Value->value == $changedValue)
					{
						return $this;
					}
				}

				$oProperty_Value = $oProperty->createNewValue($oShopItem->id);
			}
			else
			{
				$oProperty_Value = isset($aPropertyValues[0])
					? $aPropertyValues[0]
					: $oProperty->createNewValue($oShopItem->id);
			}

			/*if ($oProperty->type == 7)
			{
				mb_strtoupper($value) == 'ДА' ? $value = 1 : $value = intval($value);
			}*/

			$oProperty_Value->setValue($changedValue);
			$oProperty_Value->save();
		}

		return $this;
	}

	/**
	 * Import images
	 * @param Shop_Item_Model $oShopItem item
	 * @param array $oImages
	 */
	public function importImages(Shop_Item_Model $oShopItem, $oImages)
	{
		if (count($oImages))
		{
			$bFirstPicture = TRUE;
			$sGUID = 'ADDITIONAL-IMAGES';

			$oShop = $oShopItem->Shop;

			clearstatcache();

			$this->debug && Core_Log::instance()->clear()
				->status(Core_Log::$MESSAGE)
				->write(sprintf('1С, импорт %d изображений товара ID=%d', count($oImages), $oShopItem->id));

			// Обрабатываем изображения для товара
			foreach ($oImages as $PictureData)
			{
				if (Core_File::isValidExtension($PictureData, Core::$mainConfig['availableExtension']))
				{
					// Папка назначения
					$sDestinationFolder = $oShopItem->getItemPath();

					if (!$bFirstPicture)
					{
						$sFileName = basename($PictureData);
						$sFileDescription = strval($PictureData->attributes()->Описание);

						$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

						$oProperty = $oShop_Item_Property_List->Properties->getByGuid($sGUID, FALSE);

						if (is_null($oProperty))
						{
							$oProperty = Core_Entity::factory('Property');
							$oProperty->name = 'Images';
							$oProperty->type = 2;
							$oProperty->description = '';
							$oProperty->tag_name = 'images';
							$oProperty->guid = $sGUID;

							// Для вновь создаваемого допсвойства размеры берем из магазина
							$oProperty->image_large_max_width = $oShop->image_large_max_width;
							$oProperty->image_large_max_height = $oShop->image_large_max_height;
							$oProperty->image_small_max_width = $oShop->image_small_max_width;
							$oProperty->image_small_max_height = $oShop->image_small_max_height;

							$oShop_Item_Property_List->add($oProperty);
						}

						$oShop->Shop_Item_Property_For_Groups->allowAccess($oProperty->Shop_Item_Property->id, ($oShopItem->modification_id == 0
							? intval($oShopItem->Shop_Group->id)
							: intval($oShopItem->Modification->Shop_Group->id)
						));

						$aPropertyValues = $oProperty->getValues($oShopItem->id, FALSE);

						$oProperty_Value = NULL;
						foreach ($aPropertyValues as $oTmpPropertyValue)
						{
							// Ранее загруженное значение ищем по имени файла
							if ($oTmpPropertyValue->file_name == $sFileName)
							{
								$oProperty_Value = $oTmpPropertyValue;
								break;
							}
						}

						is_null($oProperty_Value) && $oProperty_Value = $oProperty->createNewValue($oShopItem->id);

						$oProperty_Value->save();

						/*$oProperty_Value = isset($aPropertyValues[0])
							? $aPropertyValues[0]
							: $oProperty->createNewValue($oShopItem->id);*/

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
					}
					else
					{
						if ($oShopItem->image_large != '' && is_file($sDestinationFolder . $oShopItem->image_large))
						{
							try
							{
								Core_File::delete($sDestinationFolder . $oShopItem->image_large);
							} catch (Exception $e) {}
						}

						// Удаляем старое малое изображение
						if ($oShopItem->image_small != '' && is_file($sDestinationFolder . $oShopItem->image_small))
						{
							try
							{
								Core_File::delete($sDestinationFolder . $oShopItem->image_small);
							} catch (Exception $e) {}
						}
					}

					clearstatcache();

					// Удаляем папку назначения вместе со всеми старыми файлами
					//Core_File::deleteDir($sDestinationFolder);

					// Создаем папку назначения
					$oShopItem->createDir();

					// Файл-источник
					$sSourceFile = CMS_FOLDER . $this->sPicturesPath . ltrim($PictureData, '/\\');

					if (is_file($sSourceFile))
					{
						$sSourceFileBaseName = basename($PictureData);

						if (!$oShop->change_filename)
						{
							$sTargetFileName = $sSourceFileBaseName;
						}
						else
						{
							$sTargetFileExtension = Core_File::getExtension($PictureData);

							if ($sTargetFileExtension != '')
							{
								$sTargetFileExtension = ".{$sTargetFileExtension}";
							}

							if (!$bFirstPicture)
							{
								$sTargetFileName = "shop_property_file_{$oShopItem->id}_{$oProperty_Value->id}{$sTargetFileExtension}";
							}
							else
							{
								$sTargetFileName = "shop_items_catalog_image{$oShopItem->id}{$sTargetFileExtension}";
							}
						}

						$aPicturesParam = array();
						$aPicturesParam['large_image_isset'] = TRUE;
						$aPicturesParam['large_image_source'] = $sSourceFile;
						$aPicturesParam['large_image_name'] = $sSourceFileBaseName;
						$aPicturesParam['large_image_target'] = $sDestinationFolder . $sTargetFileName;
						$aPicturesParam['watermark_file_path'] = $oShop->getWatermarkFilePath();
						$aPicturesParam['watermark_position_x'] = $oShop->watermark_default_position_x;
						$aPicturesParam['watermark_position_y'] = $oShop->watermark_default_position_y;
						$aPicturesParam['large_image_preserve_aspect_ratio'] = $oShop->preserve_aspect_ratio;
						$aPicturesParam['small_image_source'] = $aPicturesParam['large_image_source'];
						$aPicturesParam['small_image_name'] = $aPicturesParam['large_image_name'];
						$aPicturesParam['small_image_target'] = $sDestinationFolder . "small_{$sTargetFileName}";
						$aPicturesParam['create_small_image_from_large'] = TRUE;

						if (!$bFirstPicture)
						{
							$aPicturesParam['large_image_max_width'] = $oProperty->image_large_max_width;
							$aPicturesParam['large_image_max_height'] = $oProperty->image_large_max_height;
							$aPicturesParam['small_image_max_width'] = $oProperty->image_small_max_width;
							$aPicturesParam['small_image_max_height'] = $oProperty->image_small_max_height;
						}
						else
						{
							$aPicturesParam['large_image_max_width'] = $oShop->image_large_max_width;
							$aPicturesParam['large_image_max_height'] = $oShop->image_large_max_height;
							$aPicturesParam['small_image_max_width'] = $oShop->image_small_max_width;
							$aPicturesParam['small_image_max_height'] = $oShop->image_small_max_height;
						}

						$aPicturesParam['small_image_watermark'] = $oShop->watermark_default_use_small_image;
						$aPicturesParam['small_image_preserve_aspect_ratio'] = $oShop->preserve_aspect_ratio_small;

						$aPicturesParam['large_image_watermark'] = $oShop->watermark_default_use_large_image;

						try
						{
							$result = Core_File::adminUpload($aPicturesParam);
						}
						catch (Exception $exc)
						{
							$result = array('large_image' => FALSE, 'small_image' => FALSE);
						}

						if ($result['large_image'])
						{
							if (!$bFirstPicture)
							{
								$oProperty_Value->file = $sTargetFileName;
								$oProperty_Value->file_name = $sFileName;
								$oProperty_Value->file_description = $sFileDescription;
								$oProperty_Value->save();
							}
							else
							{
								$oShopItem->image_large = $sTargetFileName;
								$oShopItem->setLargeImageSizes();
							}
						}

						if ($result['small_image'])
						{
							if (!$bFirstPicture)
							{
								$oProperty_Value->file_small = "small_{$sTargetFileName}";
								$oProperty_Value->file_small_name = '';
								$oProperty_Value->save();
							}
							else
							{
								$oShopItem->image_small = "small_{$sTargetFileName}";
								$oShopItem->setSmallImageSizes();
							}
						}
					}

					$oShopItem->save() && $bFirstPicture = FALSE;
				}
			}
		}

		return $this;
	}

	/**
	 * _getProperty() cache
	 * @var array
	 */
	protected $_cacheProperty = array();

	/**
	 * Получение объекта свойства по CML ID или названию свойства (для схемы 2.0.5)
	 * @param SimpleXMLElement $oPropertyValue
	 * @return Property_Model|NULL
	 */
	protected function _getProperty(SimpleXMLElement $oPropertyValue)
	{
		$sPropertyGUID = strval($oPropertyValue->Ид);
		$sPropertyName = strval($oPropertyValue->Наименование);

		if (strlen($sPropertyGUID)
			&& isset($this->_cacheProperty[$sPropertyGUID]))
		{
			return $this->_cacheProperty[$sPropertyGUID];
		}

		if ($sPropertyName == '' || !in_array($sPropertyName, $this->skipProperties))
		{
			$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $this->iShopId);

			$oProperty = strlen($sPropertyGUID)
				? $oShop_Item_Property_List->Properties->getByGuid($sPropertyGUID, FALSE)
				// В версии 2.0.5 для ХарактеристикиТовара/ХарактеристикаТовара не передается ИД, только Наименование
				: $oShop_Item_Property_List->Properties->getByName($sPropertyName, FALSE);

			if ($oProperty && in_array($oProperty->name, $this->skipProperties))
			{
				$oProperty = NULL;
			}
		}
		else
		{
			$oProperty = NULL;
		}

		if (strlen($sPropertyGUID))
		{
			$this->_cacheProperty[$sPropertyGUID] = $oProperty;
		}

		return $oProperty;
	}

	/**
	 * Import property values
	 *
	 * ЗначенияСвойств/ЗначенияСвойства
	 * ХарактеристикиТовара/ХарактеристикаТовара
	 *
	 * @param Shop_Item_Model $oShop_Item item
	 * @param SimpleXMLElement $oPropertyValue
	 * @retrun self
	 */
	protected function _importPropertyValues(Shop_Item_Model $oShop_Item, SimpleXMLElement $oPropertyValue)
	{
		$sPropertyGUID = strval($oPropertyValue->Ид);

		$sValue = strval($oPropertyValue->Значение);

		if (isset($this->_aBaseProperties[$sPropertyGUID]))
		{
			switch (mb_strtoupper($this->_aBaseProperties[$sPropertyGUID]))
			{
				case 'HOSTCMS_TITLE':
					$oShop_Item->seo_title = $sValue;
				break;
				case 'HOSTCMS_DESCRIPTION':
					$oShop_Item->seo_description = $sValue;
				break;
				case 'HOSTCMS_KEYWORDS':
					$oShop_Item->seo_keywords = $sValue;
				break;
				case 'HOSTCMS_МЕТКИ':
					$oShop_Item->applyTags($sValue);
				break;
				case 'YANDEX_MARKET':
					$oShop_Item->yandex_market = $sValue;
				break;
				case 'ПРОДАВЕЦ':
					if (trim($sValue) != '')
					{
						$oProperty = $this->_getProperty($oPropertyValue);

						$sPropertyValue = $oProperty && isset($this->_aPropertyValues[$oProperty->id][$sValue])
							? $this->_aPropertyValues[$oProperty->id][$sValue]
							: $sValue;

						$oSeller = Core_Entity::factory('Shop', $this->iShopId)->Shop_Sellers->getByName($sPropertyValue, FALSE);

						if (is_null($oSeller))
						{
							$oSeller = Core_Entity::factory('Shop_Seller');
							$oSeller
								->shop_id($this->iShopId)
								->name($sPropertyValue)
								->path(Core_Guid::get())
								->save();
						}

						$oShop_Item->shop_seller_id = $oSeller->id;
					}
				break;
				case 'ПРОИЗВОДИТЕЛЬ':
					if (trim($sValue) != '')
					{
						$oProperty = $this->_getProperty($oPropertyValue);

						$sPropertyValue = $oProperty && isset($this->_aPropertyValues[$oProperty->id][$sValue])
							? $this->_aPropertyValues[$oProperty->id][$sValue]
							: $sValue;

						$this->_setProducer($sPropertyValue, $oShop_Item);
					}
				break;
				case 'АКТИВНОСТЬ':
					$oShop_Item->active = $sValue;
				break;
			}

			$oShop_Item->save();
		}
		elseif ($sValue != '')
		{
			$oProperty = $this->_getProperty($oPropertyValue);

			!is_null($oProperty)
				&& $this->_addItemPropertyValue($oShop_Item, $oProperty, $sValue);
		}

		return $this;
	}

	/**
	 * Определяет namespace документа, если был указан, то устаналивает его и возвращает $object->xpath($path) с учетом namespace
	 * @param SimpleXMLElement $object
	 * @param string $path
	 * @return array|false
	 */
	public function xpath(SimpleXMLElement $object, $path)
	{
		if ($this->namespace)
		{
			$object->registerXPathNamespace('w', $this->namespace);
			$sXmlns = 'w:';

			// namespace указываем перед каждым элементом xpath
			$aExplode = explode('/', $path);
			foreach ($aExplode as $key => $value)
			{
				$aExplode[$key] = $sXmlns . $value;
			}

			$sOriginalPath = $path;

			$path = implode('/', $aExplode);
		}

		$return = $object->xpath($path);

		if ($this->namespace && ($return === FALSE || !count($return)))
		{
			$return = $object->xpath($sOriginalPath);
		}

		!is_array($return) && $return = array();

		return $return;
	}

	/**
	 * Is new Shop_Item
	 * @var boolean
	 */
	protected $_bNewShopItem = TRUE;

	/**
	 * Check if necessary to update the field
	 * @param string $fieldName
	 * @return boolean
	 */
	protected function _checkUpdateField($fieldName)
	{
		return $this->_bNewShopItem
			|| !count($this->updateFields)
			|| in_array($fieldName, $this->updateFields);
	}

	/**
	 * Import import.xml, offers.xml
	 * @return array
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onBeforeImport
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onBeforeImportShopItem
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onAfterImportShopItem
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onBeforeImportShopItemPrice
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onBeforeOffer
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onAfterOffersShopItem
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onAfterImport
	 */
	public function import()
	{
		Core_Event::notify('Shop_Item_Import_Cml_Controller.onBeforeImport', $this);

		if (is_null($this->iShopId))
		{
			throw new Core_Exception(Core::_('Shop_Item.error_shop_id'));
		}

		if (is_null($this->iShopGroupId))
		{
			throw new Core_Exception(Core::_('Shop_Item.error_parent_directory'));
		}

		/*
		 Удаляем товары/группы только при получении import.xml
		*/
		if ($this->importAction == 0
			&& count((array)$this->_oSimpleXMLElement->Классификатор)
			&& count((array)$this->_oSimpleXMLElement->ПакетПредложений) == 0
			&& count((array)$this->_oSimpleXMLElement->ИзмененияПакетаПредложений) == 0
		)
		{
			Core_QueryBuilder::update('shop_groups')
				->set('deleted', 1)
				->where('shop_id', '=', $this->iShopId)
				->execute();

			Core_QueryBuilder::update('shop_items')
				->set('deleted', 1)
				->where('shop_id', '=', $this->iShopId)
				->execute();
		}

		$oShop = Core_Entity::factory('Shop', $this->iShopId);

		$aNamespaces = $this->_oSimpleXMLElement->getNamespaces(true);
		if (count($aNamespaces))
		{
			reset($aNamespaces);
			$this->namespace = current($aNamespaces);
		}

		$timeout = Core::getmicrotime();

		// CML 2.x
		if (
			isset($this->_oSimpleXMLElement->attributes()->ВерсияСхемы)
			&& version_compare(strval($this->_oSimpleXMLElement->attributes()->ВерсияСхемы), '2.0', '>=')
		)
		{
			// Файл import.xml
			if (
				!isset($this->_oSimpleXMLElement->ПакетПредложений)
				&& !isset($this->_oSimpleXMLElement->ИзмененияПакетаПредложений)
			)
			{
				Core_Session::start();
				$importPosition = Core_Array::getSession('importPosition', 0);
				Core_Session::close();

				$currentMonth = date('n');
				$sTmpPath = CMS_FOLDER . TMP_DIR . '1c_exchange_files/' . 'month-' . $currentMonth . '/';

				if (isset($this->_oSimpleXMLElement->Классификатор) && $importPosition == 0)
				{
					$classifier = $this->_oSimpleXMLElement->Классификатор;

					// Импортируем группы товаров
					if ($this->importGroups)
					{
						// Наименования каталогов по умолчанию, если указано иное,
						// то в качестве корневой будет создана группа с тем названием
						if (!in_array(strval($this->_oSimpleXMLElement->Каталог->Наименование), $this->_aConfig['catalogName']))
						{
							$sCatalogId = strval($this->_oSimpleXMLElement->Каталог->Ид);
							$sCatalogName = strval($this->_oSimpleXMLElement->Каталог->Наименование);

							if (strlen($sCatalogId))
							{
								$oTmpGroup = $oShop->Shop_Groups->getByGuid($sCatalogId);

								if (is_null($oTmpGroup))
								{
									$oTmpGroup = Core_Entity::factory('Shop_Group');
									$oTmpGroup->parent_id = $this->iShopGroupId;
									$oTmpGroup->name = $sCatalogName;
									$oTmpGroup->guid = $sCatalogId;
									$oShop->add($oTmpGroup);
								}

								$this->iShopGroupId = $oTmpGroup->id;
							}
						}

						foreach ($this->xpath($classifier, 'Группы') as $Groups)
						{
							$this->_importGroups($Groups, $this->iShopGroupId);
						}
					}

					// Импортируем дополнительные свойства товаров
					$this->_importProperties($classifier);

					// Сохраняем классификатор
					$sJsonFilePath = $sTmpPath . Core_File::filenameCorrection($this->_oSimpleXMLElement->Классификатор->Ид) . '.json';

					Core_File::mkdir($sTmpPath, CHMOD, TRUE);

					Core_File::write($sJsonFilePath, json_encode(
						array(
							'_aPropertyValues' => $this->_aPropertyValues,
							'_aBaseProperties' => $this->_aBaseProperties
						)
					));
				}
				// Классификатора не было, но указан его ИД
				elseif (isset($this->_oSimpleXMLElement->Каталог->ИдКлассификатора))
				{
					$sJsonFilePath = $sTmpPath . Core_File::filenameCorrection($this->_oSimpleXMLElement->Каталог->ИдКлассификатора) . '.json';

					if (is_file($sJsonFilePath))
					{
						$aJSON = json_decode(Core_File::read($sJsonFilePath), TRUE);

						$this->_aPropertyValues = Core_Array::get($aJSON, '_aPropertyValues', array());
						$this->_aBaseProperties = Core_Array::get($aJSON, '_aBaseProperties', array());
					}
				}

				$xPath = $importPosition == 0
					? 'Товары/Товар'
					: 'Товары/Товар[position() > ' . $importPosition . ']';

				foreach ($this->xpath($this->_oSimpleXMLElement->Каталог, $xPath) as $oXmlItem)
				{
					Core_Event::notify('Shop_Item_Import_Cml_Controller.onBeforeImportShopItem', $this, array($oXmlItem));

					// If onBeforeImportShopItem returned FALSE, skip item
					if (Core_Event::getLastReturn() === FALSE)
					{
						continue;
					}

					$sGUID = strval($oXmlItem->Ид);
					$sGUIDmod = FALSE;

					if (strpos($sGUID, '#') !== FALSE)
					{
						$sTmp = explode('#', $sGUID);
						$sGUID = $sTmp[0];
						$sGUIDmod = $sTmp[1];
					}

					// Товар может быть идентифицирован произвольным (например GUID или внутрисистемным) идентификатором, Штрихкодом, Артикулом. Контрагент может использовать любой удобный с его точки зрения  идентификатор - на выбор

					// Search by GUID
					$oShopItem = $oShop->Shop_Items->getByGuid($sGUID, FALSE);

					// Search by Barcode
					if (is_null($oShopItem) && strval($oXmlItem->Штрихкод))
					{
						$oTmpItemsByBarcode = $oShop->Shop_Items;
						$oTmpItemsByBarcode->queryBuilder()
							->select('shop_items.*')
							->join('shop_item_barcodes', 'shop_item_barcodes.shop_item_id', '=', 'shop_items.id')
							->where('shop_item_barcodes.value', '=', strval($oXmlItem->Штрихкод));

						$oShopItem = $oTmpItemsByBarcode->getFirst(FALSE);
					}

					// Search by Marking
					if (is_null($oShopItem) && trim(strval($oXmlItem->Артикул)) != '')
					{
						$oShopItem = $oShop->Shop_Items->getByMarking(strval($oXmlItem->Артикул));
					}

					$sItemName = strval($oXmlItem->Наименование);

					$this->_bNewShopItem = is_null($oShopItem);

					if ($this->_bNewShopItem)
					{
						// Не создавать товары, переходим к следующему
						if (!$this->createShopItems)
						{
							continue;
						}

						// Создаем товар
						$oShopItem = Core_Entity::factory('Shop_Item')->guid($sGUID);
						// Минимально необходимы данные
						$oShopItem->name = $sItemName;
						$oShopItem->path = '';
						$oShopItem->shop_id($this->iShopId)->save();
						$this->_aReturn['insertItemCount']++;
					}
					else
					{
						$this->_aReturn['updateItemCount']++;
					}

					// Если передан GUID модификации и товар, для которого загружается модификация, уже существует
					if (strlen($sGUIDmod) && $oShopItem->id)
					{
						$oModificationItem = $oShopItem->Modifications->getByGuid($sGUIDmod, FALSE);

						$this->_bNewShopItem = is_null($oModificationItem);
						// Модификация у товара не найдена, создаем ее
						if ($this->_bNewShopItem)
						{
							// Если товар - модификация, оставляем лишь базовые данные, название и идентификатор магазина/группы товаров
							$oModificationItem = Core_Entity::factory('Shop_Item')
								->guid($sGUIDmod)
								->modification_id($oShopItem->id)
								->shop_id($oShop->id)
								->shop_group_id(0)
								->save();
						}

						// Подменяем товар на модификацию
						$oShopItem = $oModificationItem;
					}

					// Отключение товара после определения модификация или нет
					$oAttributes = $oXmlItem->attributes();
					if (isset($oAttributes['Статус']) && $oAttributes['Статус'] == 'Удален'
						|| isset($oXmlItem->Статус) && strval($oXmlItem->Статус) == 'Удален')
					{
						$oShopItem->active = 0;
						$oShopItem->save();
						continue;
					}

					$this->_checkUpdateField('name') && $oShopItem->name = $sItemName;

					// Barcode
					if ($this->_checkUpdateField('barcode') && strval($oXmlItem->Штрихкод))
					{
						$oShop_Item_Barcode = $oShopItem->Shop_Item_Barcodes->getByvalue(strval($oXmlItem->Штрихкод), FALSE);

						if (is_null($oShop_Item_Barcode))
						{
							$oShop_Item_Barcode = Core_Entity::factory('Shop_Item_Barcode');
							$oShop_Item_Barcode->value = strval($oXmlItem->Штрихкод);
							$oShop_Item_Barcode->setType();
							$oShopItem->add($oShop_Item_Barcode);
						}
					}

					// Marking
					$this->_checkUpdateField('marking') && strval($oXmlItem->Артикул) != ''
						&& $oShopItem->marking = strval($oXmlItem->Артикул);

					// БазоваяЕдиница
					$this->_importBaseMeasure($oXmlItem, $oShopItem);

					if ($this->_checkUpdateField('shop_group_id'))
					{
						// Массив CML ID групп, в которые помещается товар
						$aGroupsCmlIDs = array();
						foreach ($this->xpath($oXmlItem, 'Группы/Ид') as $oXmlGroupId)
						{
							$aGroupsCmlIDs[] = strval($oXmlGroupId);
						}

						$firstGroupCmlId = count($aGroupsCmlIDs)
							? array_shift($aGroupsCmlIDs)
							: NULL;

						// Остальные группы из массива обрабатываются ниже
						if (!is_null($firstGroupCmlId)
							&& !is_null($oShop_Group = $oShop->Shop_Groups->getByGuid($firstGroupCmlId, FALSE))
						)
						{
							// Группа указана в файле и существует в магазине
							$sGUIDmod === FALSE
								? $oShopItem->shop_group_id = $oShop_Group->id
								: $oShopItem->Modification->shop_group_id($oShop_Group->id)->save();

							$this->_bNewShopItem && $oShop_Group->incCountItems();
						}
						else
						{
							// Группа не указана в файле, размещаем в корне (iShopGroupId)
							$sGUIDmod === FALSE
								? $oShopItem->shop_group_id = $this->iShopGroupId
								: $oShopItem->Modification->shop_group_id($this->iShopGroupId)->save();
						}
					}

					$oShopItem->shop_id = $oShop->id;

					// check item path
					$oShopItem->path == '' && $oShopItem->makePath();

					$oSameShopItem = $oShop
						->Shop_Items
						->getByGroupIdAndPath($oShopItem->shop_group_id, $oShopItem->path);

					if (!is_null($oSameShopItem) && $oSameShopItem->id != $oShopItem->id)
					{
						$oShopItem->path = Core_Guid::get();
					}
					else
					{
						$oSameShopGroup = Core_Entity::factory('Shop', $this->iShopId)->Shop_Groups->getByParentIdAndPath($oShopItem->shop_group_id, $oShopItem->path);

						if (!is_null($oSameShopGroup))
						{
							$oShopItem->path = Core_Guid::get();
						}
					}

					if ($oShopItem->modification_id)
					{
						$oShopItem->shop_group_id = 0;
					}

					if ($oShopItem->id && $this->importAction == 1 && !is_null($oShopItem->name))
					{
						$oShopItem->save();
					}
					elseif (!is_null($oShopItem->name))
					{
						is_null($oShopItem->path) && $oShopItem->path = '';

						$oShopItem->save()->clearCache();

						// Indexation
						$this->searchIndexation
							&& $oShopItem->index();
					}
					else
					{
						throw new Core_Exception(Core::_('Shop_Item.error_save_without_name'));
					}

					// В остальные группы помещается ярлык
					if ($this->_checkUpdateField('shop_group_id') && count($aGroupsCmlIDs))
					{
						foreach ($aGroupsCmlIDs as $sGroupCmlID)
						{
							$oTmpShopGroup = $oShop->Shop_Groups->getByGuid($sGroupCmlID, FALSE);

							if (!is_null($oTmpShopGroup))
							{
								$aShopItems = $oShop->Shop_Items;
								$aShopItems->queryBuilder()
									->where('shortcut_id', '=', $oShopItem->id)
									->where('shop_group_id', '=', $oTmpShopGroup->id)
									->limit(1);

								$iCountShortcuts = $aShopItems->getCount(FALSE);

								if (!$iCountShortcuts)
								{
									Core_Entity::factory('Shop_Item')
										->shop_group_id($oTmpShopGroup->id)
										->shortcut_id($oShopItem->id)
										->shop_id($oShop->id)
										->save();
								}
							}
						}
					}

					// Обрабатываем описание товара
					foreach ($this->xpath($oXmlItem, 'Описание') as $DescriptionData)
					{
						if ($this->itemDescription == 'text' && $this->_checkUpdateField('text'))
						{
							$oShopItem->text = nl2br(strval($DescriptionData));
						}
						elseif ($this->itemDescription == 'description' && $this->_checkUpdateField('description'))
						{
							$oShopItem->description = nl2br(strval($DescriptionData));
						}
						$oShopItem->save();
					}

					// Обрабатываем "малое описание" товара. Данный тег не соответствует стандарту CommerceML!
					foreach ($this->xpath($oXmlItem, $this->shortDescription) as $DescriptionData)
					{
						$oShopItem->description = nl2br(strval($DescriptionData));
						$oShopItem->save();
					}

					// Картинки основного товара
					$this->_checkUpdateField('images') && $this->importImages($oShopItem, $this->xpath($oXmlItem, 'Картинка'));

					// До обработки свойств из 1С нужно записать значения "по умолчанию" для всех свойств, заданных данной группе товара
					/*$aProperties = Core_Entity::factory('Shop_Item_Property_List', $oShop->id)->Properties;
					$aProperties
						->queryBuilder()
						->join('shop_item_property_for_groups', 'shop_item_property_for_groups.shop_item_property_id', '=', 'shop_item_properties.id')
						->where('shop_item_property_for_groups.shop_id', '=', $oShop->id)
						->where('shop_item_property_for_groups.shop_group_id', '=', $oShopItem->Shop_Group->id);

					$aProperties = $aProperties->findAll(FALSE);

					foreach ($aProperties as $oProperty)
					{
						if ($oProperty->type == 2)
						{
							continue;
						}

						$aPropertyValues = $oProperty->getValues($oShopItem->id, FALSE);

						if (count($aPropertyValues) == 0)
						{
							$oProperty_Value = $oProperty->createNewValue($oShopItem->id);
							$oProperty_Value->setValue($oProperty->default_value);
							$oProperty_Value->save();
						}
					}*/

					// Добавляем значения для общих свойств всех товаров
					foreach ($this->xpath($oXmlItem, 'ЗначенияСвойств/ЗначенияСвойства') as $ItemPropertyValue)
					{
						$this->_importPropertyValues($oShopItem, $ItemPropertyValue);
					}

					foreach ($this->xpath($oXmlItem, 'ХарактеристикиТовара/ХарактеристикаТовара') as $oItemProperty)
					{
						$this->_importPropertyValues($oShopItem, $oItemProperty);
					}

					foreach ($this->xpath($oXmlItem, 'ЗначенияРеквизитов/ЗначениеРеквизита') as $oItemProperty)
					{
						$this->_addPredefinedAdditionalProperty($oShopItem, $oItemProperty, strval($oItemProperty->Значение));
					}

					// Налоги
					if ($this->_checkUpdateField('taxes'))
					{
						foreach ($this->xpath($oXmlItem, 'СтавкиНалогов/СтавкаНалога') as $oTax)
						{
							$oShopTax = $this->_addTax($oTax);
							$oShopItem->shop_tax_id = $oShopTax->id;
							$oShopItem->save();
						}
					}

					// Производитель
					if ($this->_checkUpdateField('shop_producer_id'))
					{
						if (isset($oXmlItem->ТорговаяМарка))
						{
							$this->_setProducer(strval($oXmlItem->ТорговаяМарка), $oShopItem);
						}
						elseif (isset($oXmlItem->Изготовитель))
						{
							$this->_setProducer(strval($oXmlItem->Изготовитель->Наименование), $oShopItem);
						}
					}

					Core_Event::notify('Shop_Item_Import_Cml_Controller.onAfterImportShopItem', $this, array($oShopItem, $oXmlItem));

					$importPosition++;

					// Прерываем этап импорта
					if ($this->timeout && (Core::getmicrotime() - $timeout + 2 > $this->timeout))
					{
						Core_Session::start();
						$_SESSION['importPosition'] = $importPosition;
						Core_Session::close();

						$this->_aReturn['status'] = 'progress';
						return $this->_aReturn;
					}
				}
			}
			// Файл offers.xml
			elseif (
				(isset($this->_oSimpleXMLElement->ПакетПредложений)
					|| isset($this->_oSimpleXMLElement->ИзмененияПакетаПредложений)
				)
				&& !isset($this->_oSimpleXMLElement->Каталог)
			)
			{
				$classifier = $this->_oSimpleXMLElement->Классификатор;

				// Импортируем дополнительные свойства товаров
				$this->_importProperties($classifier);

				$packageOfProposals = isset($this->_oSimpleXMLElement->ПакетПредложений)
					? $this->_oSimpleXMLElement->ПакетПредложений
					: $this->_oSimpleXMLElement->ИзмененияПакетаПредложений;

				// Обработка специальных цен
				foreach ($this->xpath($packageOfProposals, 'ТипыЦен/ТипЦены') as $oPrice)
				{
					$oShopPrice = Core_Entity::factory('Shop', $this->iShopId)
						->Shop_Prices
						->getByGuid(strval($oPrice->Ид), FALSE);

					if (is_null($oShopPrice))
					{
						$oShopPrice = Core_Entity::factory('Shop_Price');
						$oShopPrice->shop_id = $this->iShopId;
						$oShopPrice->guid = strval($oPrice->Ид);
						$oShopPrice->percent = 100;
					}

					$oShopPrice->name = strval($oPrice->Наименование);

					// Если это основная цена, обновляем информацию о налоге
					if (mb_strtoupper($oShopPrice->name) == mb_strtoupper($this->sShopDefaultPriceName))
					{
						$sTaxGUID = md5(mb_strtoupper($oPrice->Налог->Наименование));
						$oShopTax = Core_Entity::factory('Shop_Tax')->getByGuid($sTaxGUID, FALSE);

						// для совместимости с МойСклад
						if (!is_null($oShopTax))
						{
							// В связи с разницей логик HostCMS и 1С по хранению налогов, поле "учтено в сумме" больше не будет импортироваться
							$iInSum = strval($oPrice->Налог->УчтеноВСумме);
							strtoupper($iInSum) == 'TRUE' ? $oShopTax->tax_is_included = 1 : $oShopTax->tax_is_included = 0;
							$this->_oTaxForBasePrice = $oShopTax->save();
						}

						$this->sShopDefaultPriceGUID = $oShopPrice->guid;
					}
					else
					{
						$oShopPrice->save();
					}
				}

				// Обработка складов
				foreach ($this->xpath($packageOfProposals, 'Склады/Склад') as $oWarehouse)
				{
					$sWarehouseGuid = strval($oWarehouse->Ид);

					$oShopWarehouse = $oShop
						->Shop_Warehouses
						->getByGuid($sWarehouseGuid, FALSE);

					if (is_null($oShopWarehouse))
					{
						$oShopWarehouse = Core_Entity::factory('Shop_Warehouse');
						$oShopWarehouse->shop_id = $oShop->id;
						$oShopWarehouse->guid = $sWarehouseGuid;
						$oShopWarehouse->name = strval($oWarehouse->Наименование);
						$oShopWarehouse->address = strval($oWarehouse->Адрес->Представление);
						$oShopWarehouse->save();
					}

					/*foreach ($this->xpath($oWarehouse, 'Адрес/АдресноеПоле') as $oWarehouseAddressField)
					{
						//echo "Адресное поле: " . strval($oWarehouseAddressField->Тип) . " - " . strval($oWarehouseAddressField->Значение) . "<br/>";
					}*/
				}

				// Обработка предложений
				foreach ($this->xpath($packageOfProposals, 'Предложения/Предложение') as $oProposal)
				{
					Core_Event::notify('Shop_Item_Import_Cml_Controller.onBeforeOffer', $this, array($oProposal));

					// If onBeforeOffer returned FALSE, skip item
					if (Core_Event::getLastReturn() === FALSE)
					{
						continue;
					}

					$sItemGUID = strval($oProposal->Ид);

					$sGUIDmod = FALSE;
					if (strpos($sItemGUID, '#') !== FALSE)
					{
						$aItemGUID = explode('#', $sItemGUID);
						$sItemGUID = $aItemGUID[0];
						$sGUIDmod = $aItemGUID[1];
					}

					// Товар может быть идентифицирован произвольным (например GUID или внутрисистемным) идентификатором, Штрихкодом, Артикулом. Контрагент может использовать любой удобный с его точки зрения  идентификатор - на выбор

					// Основной товар (не модификация)
					$oShopItem = $oShop->Shop_Items->getByGuid($sItemGUID, FALSE);

					// Search by Barcode
					if (is_null($oShopItem) && strval($oProposal->Штрихкод))
					{
						$oTmpItemsByBarcode = $oShop->Shop_Items;
						$oTmpItemsByBarcode->queryBuilder()
							->select('shop_items.*')
							->join('shop_item_barcodes', 'shop_item_barcodes.shop_item_id', '=', 'shop_items.id')
							->where('shop_item_barcodes.value', '=', strval($oProposal->Штрихкод));

						$oShopItem = $oTmpItemsByBarcode->getFirst(FALSE);
					}

					// Search by Marking
					if (is_null($oShopItem) && strval($oProposal->Артикул))
					{
						$oShopItem = $oShop->Shop_Items->getByMarking(strval($oProposal->Артикул));
					}

					if (!is_null($oShopItem))
					{
						// Если передан GUID модификации
						if (strlen($sGUIDmod))
						{
							$oModificationItem = $oShopItem->Modifications->getByGuid($sGUIDmod, FALSE);

							// Модификация у товара не найдена, создаем ее
							if (is_null($oModificationItem))
							{
								$oModificationItem = Core_Entity::factory('Shop_Item')
									->guid($sGUIDmod)
									->modification_id($oShopItem->id)
									->shop_id($this->iShopId)
									->shop_group_id(0)
									->save();
							}

							// Подменяем товар на модификацию
							$oShopItem = $oModificationItem;

							// Для модификации обновляется название и артикул
							$this->_checkUpdateField('marking') && strval($oProposal->Артикул) != ''
								&& $oShopItem->marking = strval($oProposal->Артикул);

							$this->_checkUpdateField('name') && $oShopItem->name = strval($oProposal->Наименование);
						}

						// Товар найден, начинаем обновление
						// Данные указываются при импорте import.xml, из offers.xml не обновляются
						//$oShopItem->marking = strval($oProposal->Артикул);
						//$oShopItem->name = strval($oProposal->Наименование);

						// БазоваяЕдиница
						$this->_importBaseMeasure($oProposal, $oShopItem);

						// Картинки предложений (модификаций)
						$this->importImages($oShopItem, $this->xpath($oProposal, 'Картинка'));

						// Добавляем значения для общих свойств всех товаров
						foreach ($this->xpath($oProposal, 'ЗначенияСвойств/ЗначенияСвойства') as $ItemPropertyValue)
						{
							$this->_importPropertyValues($oShopItem, $ItemPropertyValue);
						}

						// Обработка характеристик товара из файла offers для совместимости с МойСклад
						foreach ($this->xpath($oProposal, 'ХарактеристикиТовара/ХарактеристикаТовара') as $oItemProperty)
						{
							$this->_importPropertyValues($oShopItem, $oItemProperty);
						}

						if ($this->_checkUpdateField('prices'))
						{
							foreach ($this->xpath($oProposal, 'Цены/Цена') as $oPrice)
							{
								Core_Event::notify(get_class($this) . '.onBeforeImportShopItemPrice', $this, array($oShopItem, $packageOfProposals, $oProposal, $oPrice));

								if (is_null(Core_Event::getLastReturn()))
								{
									// Ищем цену
									$oShopPrice = $oShop
										->Shop_Prices
										->getByGuid(strval($oPrice->ИдТипаЦены), FALSE);

									if (!is_null($oShopPrice)
										&& $this->sShopDefaultPriceGUID != strval($oPrice->ИдТипаЦены))
									{
										$itemPrice = strval($oPrice->ЦенаЗаЕдиницу);

										// Валюта товара в основной цене
										$baseCurrencyNode = $this->xpath($oProposal, "Цены/Цена[ИдТипаЦены='{$this->sShopDefaultPriceGUID}']");

										if (isset($baseCurrencyNode[0]))
										{
											// Валюта у цены по умолчанию
											$sCurrency = strval($baseCurrencyNode[0]->Валюта);

											// Валюта не указана у самого предложения, смотрим в ТипыЦен/ТипЦены
											if (!strlen($sCurrency))
											{
												$topCurrencyNode = $this->xpath($packageOfProposals, "ТипыЦен/ТипЦены[Ид='{$this->sShopDefaultPriceGUID}']");

												is_object($topCurrencyNode)
													&& $sCurrency = strval($topCurrencyNode->Валюта);
											}

											// Указан числовой код валюты, получаем по нему
											if (is_numeric($sCurrency) && isset($this->_aCurrencyCodes[$sCurrency]))
											{
												$sCurrency = $this->_aCurrencyCodes[$sCurrency];
											}

											$oItem_Shop_Currency = Core_Entity::factory('Shop_Currency')
												->getByLike($sCurrency, FALSE);

											// Валюта у самого товара
											$sCurrency = strval($oPrice->Валюта);

											// Валюта не указана у самого предложения, смотрим в ТипыЦен/ТипЦены
											if (!strlen($sCurrency))
											{
												$topCurrencyNode = $this->xpath($packageOfProposals, "ТипыЦен/ТипЦены[Ид='" . strval($oPrice->ИдТипаЦены) . "']");

												is_object($topCurrencyNode)
													&& $sCurrency = strval($topCurrencyNode->Валюта);
											}

											// Указан числовой код валюты, получаем по нему
											if (is_numeric($sCurrency) && isset($this->_aCurrencyCodes[$sCurrency]))
											{
												$sCurrency = $this->_aCurrencyCodes[$sCurrency];
											}

											// Валюта спеццены
											$oPrice_Currency = Core_Entity::factory('Shop_Currency')
												->getByLike($sCurrency, FALSE);

											if (!is_null($oItem_Shop_Currency)
												&& !is_null($oPrice_Currency)
												&& $oItem_Shop_Currency->exchange_rate
												&& $oPrice_Currency->exchange_rate)
											{
												$currencyCoefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency($oPrice_Currency, $oItem_Shop_Currency);

												$itemPrice *= $currencyCoefficient;
											}
										}

										$oShop_Price_Setting = $this->_getPrices();

										$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
										$oShop_Price_Setting_Item->shop_price_id = $oShopPrice->id;
										$oShop_Price_Setting_Item->shop_item_id = $oShopItem->id;

										$oShop_Item_Price = $oShopItem
											->Shop_Item_Prices
											->getByPriceId($oShopPrice->id, FALSE);

										$old_price = !is_null($oShop_Item_Price)
											? $oShop_Item_Price->value
											: $oShopItem->price;

										$oShop_Price_Setting_Item->old_price = $old_price;
										$oShop_Price_Setting_Item->new_price = $itemPrice;
										$oShop_Price_Setting->add($oShop_Price_Setting_Item);

										/*if (is_null($oShop_Item_Price))
										{
											$oShop_Item_Price = Core_Entity::factory('Shop_Item_Price');
											$oShop_Item_Price->shop_item_id = $oShopItem->id;
											$oShop_Item_Price->shop_price_id = $oShopPrice->id;
										}
										$oShop_Item_Price->value = $itemPrice;

										$oShopItem->add($oShop_Item_Price);*/
									}
									elseif ($this->sShopDefaultPriceGUID == strval($oPrice->ИдТипаЦены))
									{
										$sCurrency = strval($oPrice->Валюта);

										// Валюта не указана у самого предложения, смотрим в ТипыЦен/ТипЦены
										if (!strlen($sCurrency))
										{
											$topCurrencyNode = $this->xpath($packageOfProposals, "ТипыЦен/ТипЦены[Ид='" . strval($oPrice->ИдТипаЦены) . "']");

											is_object($topCurrencyNode)
												&& $sCurrency = strval($topCurrencyNode->Валюта);
										}

										// Указан числовой код валюты, получаем по нему
										if (is_numeric($sCurrency) && isset($this->_aCurrencyCodes[$sCurrency]))
										{
											$sCurrency = $this->_aCurrencyCodes[$sCurrency];
										}

										$oShop_Currency = Core_Entity::factory('Shop_Currency')->getByLike($sCurrency, FALSE);

										if (is_null($oShop_Currency))
										{
											$oShop_Currency = Core_Entity::factory('Shop_Currency');
											$oShop_Currency->name = $sCurrency;
											$oShop_Currency->code = $sCurrency;
											$oShop_Currency->exchange_rate = 1;
										}

										$oShop_Price_Setting = $this->_getPrices();

										$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
										$oShop_Price_Setting_Item->shop_price_id = 0;
										$oShop_Price_Setting_Item->shop_item_id = $oShopItem->id;
										$oShop_Price_Setting_Item->old_price = $oShopItem->price;
										$oShop_Price_Setting_Item->new_price = Shop_Controller::instance()->convertPrice(strval($oPrice->ЦенаЗаЕдиницу));
										$oShop_Price_Setting->add($oShop_Price_Setting_Item);

										// $oShopItem->price = Shop_Controller::instance()->convertPrice(strval($oPrice->ЦенаЗаЕдиницу));

										// Set ->shop_currency_id
										$oShopItem->add($oShop_Currency);

										if (!is_null($this->_oTaxForBasePrice))
										{
											$oShopItem->add($this->_oTaxForBasePrice);
											$this->_oTaxForBasePrice = NULL;
										}

										// Импортируется только через "БазоваяЕдиница"
										/*if (($sMeasureName = strval($oPrice->Единица)) != '')
										{
											if (is_null($oShop_Measure = Core_Entity::factory('Shop_Measure')->getByName($sMeasureName, FALSE)))
											{
												$oShop_Measure = Core_Entity::factory('Shop_Measure')->name($sMeasureName)->save();
											}
											$oShopItem->add($oShop_Measure);
										}*/
									}
								}
							}
						}

						if ($this->_checkUpdateField('warehouses'))
						{
							$aWarehouses = $this->xpath($oProposal, 'Склад');

							// Явно переданы остатки по каждому складу
							if (count($aWarehouses))
							{
								/* <Склад ИдСклада="xxx" КоличествоНаСкладе="10"></Склад>
								<Склад ИдСклада="yyy" КоличествоНаСкладе="15"></Склад> */

								foreach ($aWarehouses as $oWarehouseCount)
								{
									$sWarehouseGuid = strval($oWarehouseCount['ИдСклада']);
									$sWarehouseCount = strval($oWarehouseCount['КоличествоНаСкладе']);

									$oShopWarehouse = Core_Entity::factory('Shop', $this->iShopId)
										->Shop_Warehouses
										->getByGuid($sWarehouseGuid, FALSE);

									if (!is_null($oShopWarehouse))
									{
										$oShop_Warehouse_Inventory = $this->_getInventory($oShopWarehouse->id);

										$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
										$oShop_Warehouse_Inventory_Item->shop_item_id = $oShopItem->id;
										$oShop_Warehouse_Inventory_Item->count = floatval($sWarehouseCount);
										$oShop_Warehouse_Inventory->add($oShop_Warehouse_Inventory_Item);

										/*$oShop_Warehouse_Item = $oShopWarehouse->Shop_Warehouse_Items->getByShopItemId($oShopItem->id, FALSE);
										if (is_null($oShop_Warehouse_Item))
										{
											$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item')
												->shop_warehouse_id($oShopWarehouse->id)
												->shop_item_id($oShopItem->id);
										}
										$oShop_Warehouse_Item->count(floatval($sWarehouseCount))->save();*/
									}
								}
							}
							// Общее количество на складе по умолчанию
							else
							{
								$iItemCount = 0;

								foreach ($this->xpath($oProposal, 'Количество') as $oCount)
								{
									$iItemCount = $oCount;
								}

								// если нет тега "Количество", ставим количество товара на главном складе равным нулю
								// Ищем главный склад
								$oWarehouse = Core_Entity::factory('Shop', $this->iShopId)->Shop_Warehouses->getByDefault("1", FALSE);

								if (is_null($oWarehouse))
								{
									// Склад не обнаружен
									$oWarehouse = Core_Entity::factory('Shop_Warehouse');
									$oWarehouse->name = Core::_("Shop_Warehouse.warehouse_default_name");
									$oWarehouse->active = 1;
									$oWarehouse->default = 1;
									$oWarehouse->shop_id = $this->iShopId;
									$oWarehouse->save();
								}

								$oShop_Warehouse_Inventory = $this->_getInventory($oWarehouse->id);

								$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
								$oShop_Warehouse_Inventory_Item->shop_item_id = $oShopItem->id;
								$oShop_Warehouse_Inventory_Item->count = floatval($iItemCount);
								$oShop_Warehouse_Inventory->add($oShop_Warehouse_Inventory_Item);

								/*$oShop_Warehouse_Item = $oWarehouse->Shop_Warehouse_Items->getByShopItemId($oShopItem->id, FALSE);
								if (is_null($oShop_Warehouse_Item))
								{
									$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item')
										->shop_warehouse_id($oWarehouse->id)
										->shop_item_id($oShopItem->id);
								}

								$oShop_Warehouse_Item->count(floatval($iItemCount))->save();*/
							}
						}

						$oShopItem->save()->clearCache();

						$this->_aReturn['updateItemCount']++;

						Core_Event::notify('Shop_Item_Import_Cml_Controller.onAfterOffersShopItem', $this, array($oShopItem, $oProposal));
					}
				}
			}
		}
		// Файл 1C v.7.xx
		elseif (count((array)$this->_oSimpleXMLElement->Каталог))
		{
			$catalog = $this->_oSimpleXMLElement->Каталог;
			//$this->xpath($oItemProperty, 'ВариантыЗначений/Справочник')
			foreach ($this->xpath($catalog, 'Свойство') as $oXmlProperty)
			{
				$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $this->iShopId);
				$oShopProperty = $oShop_Item_Property_List
					->Properties
					->getByGuid(strval($oXmlProperty->attributes()->Идентификатор), FALSE);

				if (is_null($oShopProperty))
				{
					$oProperty = Core_Entity::factory('Property');
					$oProperty->name = strval($oXmlProperty->attributes()->Наименование);
					$oProperty->type = 1;
					$oProperty->tag_name = Core_Str::transliteration(strval($oXmlProperty->attributes()->Наименование));
					$oProperty->guid = strval($oXmlProperty->attributes()->Идентификатор);
					$oShop_Item_Property_List->add($oProperty);
				}
			}

			$aGroupList = $aGroupListTree = array();
			foreach ($this->xpath($catalog, 'Группа') as $oXmlGroup)
			{
				$sParentGUID = strval($oXmlGroup->attributes()->Родитель) == '' ? 0 : strval($oXmlGroup->attributes()->Родитель);
				$aGroupList[strval($oXmlGroup->attributes()->Идентификатор)] = $oXmlGroup;
				$aGroupListTree[$sParentGUID][] = strval($oXmlGroup->attributes()->Идентификатор);
			}
			$aStack = array(0 => 0);
			while (count($aStack) > 0)
			{
				$sStackEnd = end($aStack);
				unset($aStack[count($aStack) - 1]);
				if (isset($aGroupListTree[$sStackEnd]))
				{
					foreach ($aGroupListTree[$sStackEnd] as $sGroupGUID)
					{
						$oShopGroup = Core_Entity::factory('Shop', $this->iShopId)->Shop_Groups->getByGuid($sGroupGUID, FALSE);
						if (is_null($oShopGroup))
						{
							$oShopGroup = Core_Entity::factory('Shop_Group');
							$oShopGroup->guid = strval($aGroupList[$sGroupGUID]->attributes()->Идентификатор);
							$oShopGroup->shop_id = $this->iShopId;
							$this->_aReturn['insertDirCount']++;
						}
						else
						{
							$this->_aReturn['updateDirCount']++;
						}
						is_null($oShopGroup->path) && $oShopGroup->path= '';
						$oShopGroup->name = strval($aGroupList[$sGroupGUID]->attributes()->Наименование);
						$oShopGroup->parent_id = $sStackEnd === 0 ? 0 : Core_Entity::factory('Shop', $this->iShopId)->Shop_Groups->getByGuid($sStackEnd, FALSE)->id;
						$oShopGroup->save();
						$aStack[count($aStack)] = $sGroupGUID;
					}
				}
			}

			foreach ($this->xpath($catalog, 'Товар') as $oXmlItem)
			{
				$oShopItem = $oShop->Shop_Items->getByGuid(strval($oXmlItem->attributes()->Идентификатор), FALSE);

				if (is_null($oShopItem))
				{
					// Создаем товар
					$oShopItem = Core_Entity::factory('Shop_Item')
						->guid(strval($oXmlItem->attributes()->Идентификатор));

					$oShopItem->shop_id = $this->iShopId;
					$oShopItem->guid = strval($oXmlItem->attributes()->Идентификатор);
					$this->_aReturn['insertItemCount']++;
				}
				else
				{
					$this->_aReturn['updateItemCount']++;
				}
				is_null($oShopItem->path) && $oShopItem->path = '';

				$oShopItem->name = strval($oXmlItem->attributes()->Наименование);

				$oShopItem->marking = strval($oXmlItem->attributes()->ИдентификаторВКаталоге);

				$oShopGroup = Core_Entity::factory('Shop', $this->iShopId)
					->Shop_Groups
					->getByGuid(strval($oXmlItem->attributes()->Родитель), FALSE);

				if (is_null($oShopGroup))
				{
					$oShopGroup = Core_Entity::factory('Shop_Group', 0);
				}

				$oShopItem->shop_group_id = $oShopGroup->id;

				$oShopMeasure = Core_Entity::factory('Shop_Measure')
					->getByName(strval($oXmlItem->attributes()->Единица), FALSE);

				!is_null($oShopMeasure)
					&& $oShopItem->shop_measure_id = $oShopMeasure->id;

				$oShopItem->save();

				foreach ($this->xpath($oXmlItem, 'ЗначениеСвойства') as $oXmlPropertyValue)
				{
					$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $this->iShopId);
					$oShopProperty = $oShop_Item_Property_List
						->Properties
						->getByGuid(strval($oXmlPropertyValue->attributes()->ИдентификаторСвойства), FALSE);

					if (!is_null($oShopProperty) && $oShopProperty->type != 2 && $oShopProperty->type != 3)
					{
						if (is_null(Core_Entity::factory('Shop', $this->iShopId)
							->Shop_Item_Property_For_Groups
							->getByShopItemPropertyIdAndGroupId($oShopProperty->Shop_Item_Property->id, $oShopItem->shop_group_id)))
						{
							Core_Entity::factory('Shop_Item_Property_For_Group')
								->shop_group_id($oShopItem->shop_group_id)
								->shop_item_property_id($oShopProperty->Shop_Item_Property->id)
								->shop_id($this->iShopId)
								->save();
						}

						$aPropertyValues = $oShopProperty->getValues($oShopItem->id, FALSE);

						$oProperty_Value = isset($aPropertyValues[0])
							? $aPropertyValues[0]
							: $oShopProperty->createNewValue($oShopItem->id);

						$sValue = strval($oXmlPropertyValue->attributes()->Значение);
						$oProperty_Value->setValue($sValue);
						$oProperty_Value->save();
					}
				}
			}

			$offers = $this->_oSimpleXMLElement->ПакетПредложений;
			foreach ($this->xpath($offers, 'Предложение') as $oXmlOffer)
			{
				$oShopItem = $oShop
					->Shop_Items
					->getByGuid(strval($oXmlOffer->attributes()->ИдентификаторТовара), FALSE);

				if (!is_null($oShopItem))
				{
					$oShop_Price_Setting = $this->_getPrices();

					$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
					$oShop_Price_Setting_Item->shop_price_id = 0;
					$oShop_Price_Setting_Item->shop_item_id = $oShopItem->id;
					$oShop_Price_Setting_Item->old_price = $oShopItem->price;
					$oShop_Price_Setting_Item->new_price = Shop_Controller::instance()->convertPrice(strval($oXmlOffer->attributes()->Цена));
					$oShop_Price_Setting->add($oShop_Price_Setting_Item);

					// $oShopItem->price = Shop_Controller::instance()->convertPrice(strval($oXmlOffer->attributes()->Цена));

					if (!is_null($oShop_Currency = Core_Entity::factory('Shop_Currency')->getByLike(strval($oXmlOffer->attributes()->Валюта), FALSE)))
					{
						$oShopItem->shop_currency_id = $oShop_Currency->id;
					}
					$oShopItem->save();

					$oWarehouse = Core_Entity::factory('Shop', $this->iShopId)->Shop_Warehouses->getByDefault(1, FALSE);
					if (!is_null($oWarehouse))
					{
						$oShop_Warehouse_Inventory = $this->_getInventory($oWarehouse->id);

						$oShop_Warehouse_Inventory_Item = Core_Entity::factory('Shop_Warehouse_Inventory_Item');
						$oShop_Warehouse_Inventory_Item->shop_item_id = $oShopItem->id;
						$oShop_Warehouse_Inventory_Item->count = strval($oXmlOffer->attributes()->Количество);
						$oShop_Warehouse_Inventory->add($oShop_Warehouse_Inventory_Item);

						/*$oShop_Warehouse_Item = $oWarehouse->Shop_Warehouse_Items->getByShopItemId($oShopItem->id, FALSE);
						if (is_null($oShop_Warehouse_Item))
						{
							$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item')
								->shop_warehouse_id($oWarehouse->id)
								->shop_item_id($oShopItem->id);
						}
						$oShop_Warehouse_Item->count(strval($oXmlOffer->attributes()->Количество))->save();*/
					}
				}
			}
		}

		Core_Session::start();
		$_SESSION['importPosition'] = 0;
		Core_Session::close();

		// Пересчет количества товаров в группах
		$oShop->recount();

		// Post all
		$this->postAll();

		Core_Event::notify('Shop_Item_Import_Cml_Controller.onAfterImport', $this);

		return $this->_aReturn;
	}

	/**
	 * Import list of properties
	 * @param object $classifier
	 * @return self
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onBeforeCreateProperty
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onAfterCreateProperty
	 */
	protected function _importProperties($classifier)
	{
		$oShop = Core_Entity::factory('Shop', $this->iShopId);

		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $this->iShopId);
		foreach ($this->xpath($classifier, 'Свойства/Свойство') as $oItemProperty)
		{
			$sPropertyGUID = strval($oItemProperty->Ид);
			$sPropertyName = strval($oItemProperty->Наименование);

			if ($sPropertyName == '' || !in_array($sPropertyName, $this->skipProperties))
			{
				$oProperty = strlen($sPropertyGUID)
					? $oShop_Item_Property_List->Properties->getByGuid($sPropertyGUID, FALSE)
					// В версии 2.0.5 для ХарактеристикиТовара/ХарактеристикаТовара не передается ИД, только Наименование
					: $oShop_Item_Property_List->Properties->getByName($sPropertyName, FALSE);

				if (is_null($oProperty)
					&& strlen($sPropertyGUID)
					// Свойство может быть найдено по GUID
					&& !in_array($sPropertyName, $this->skipProperties)
				)
				{
					$oProperty = Core_Entity::factory('Property');
					$oProperty->name = $sPropertyName;
					$oProperty->guid = $sPropertyGUID;

					if (strval($oItemProperty->ТипЗначений) == 'Справочник' && Core::moduleIsActive('list'))
					{
						$oProperty->type = 3;

						// Check if list exists
						$oList = $oShop->Site->Lists->getByName($sPropertyName, FALSE);

						// Create new List
						if (is_null($oList))
						{
							$oList = Core_Entity::factory('List');
							$oList->name = $sPropertyName;
							$oList->list_dir_id = 0;
							$oList->site_id = $oShop->site_id;
							$oList->save();
						}

						$oProperty->list_id = $oList->id;
					}
					else
					{
						$oProperty->type = 1;
					}

					$sTagName = Core_Str::transliteration($oProperty->name);

					// Уже может быть свойство с таким же tag_name внтури одного магазина,
					// например, разные справочники с одинаковым названием, но разными значениями
					$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $this->iShopId);
					$iCount = $linkedObject->Properties->getCountBytag_name($sTagName, FALSE);

					// Добавляем к названию тега "-{количество+1}"
					if ($iCount)
					{
						$iCount = $linkedObject->Properties->getCountBytag_name($sTagName . '-%', FALSE, 'LIKE');
						// +2, т.к. одно свойство без минуса уже было найдено, а счет ведем с единицы
						$sTagName .= '-' . ($iCount + 2);
					}

					$oProperty->tag_name = $sTagName;

					// Для вновь создаваемого допсвойства размеры берем из магазина
					$oProperty->image_large_max_width = $oShop->image_large_max_width;
					$oProperty->image_large_max_height = $oShop->image_large_max_height;
					$oProperty->image_small_max_width = $oShop->image_small_max_width;
					$oProperty->image_small_max_height = $oShop->image_small_max_height;

					Core_Event::notify('Shop_Item_Import_Cml_Controller.onBeforeCreateProperty', $this, array($oProperty, $oItemProperty));

					$oShop_Item_Property_List->add($oProperty);

					Core_Event::notify('Shop_Item_Import_Cml_Controller.onAfterCreateProperty', $this, array($oProperty, $oItemProperty));
				}

				$this->_cacheProperty[$sPropertyGUID] = $oProperty;

				if (strval($oItemProperty->ТипЗначений) == 'Справочник')
				{
					$this->_aPropertyValues[$oProperty->id] = array();
				}

				foreach ($this->xpath($oItemProperty, 'ВариантыЗначений/Справочник') as $oValue)
				{
					$listValue = strval($oValue->Значение);
					$this->_aPropertyValues[$oProperty->id][strval($oValue->ИдЗначения)] = $listValue;

					if ($oProperty->type == 3 && $oProperty->list_id && Core::moduleIsActive('list'))
					{
						$oList_Item = $oProperty->List->List_Items->getByValue($listValue, FALSE);

						if (is_null($oList_Item))
						{
							$oList_Item = Core_Entity::factory('List_Item');
							$oList_Item->value = $listValue;
							$oList_Item->list_id = $oProperty->list_id;
							$oList_Item->save();
						}
					}
				}

				if (in_array(mb_strtoupper($sPropertyName), $this->_aPredefinedBaseProperties))
				{
					// Основное свойство товара
					$this->_aBaseProperties[strval($oItemProperty->Ид)] = strval($oItemProperty->Наименование);
				}
			}
		}

		return $this;
	}

	/**
	 * Импорт "БазоваяЕдиница"
	 * @param object $oNode
	 * @param Shop_Item_Model $oShopItem
	 * @return self
	 */
	protected function _importBaseMeasure($oNode, Shop_Item_Model $oShopItem)
	{
		if (isset($oNode->БазоваяЕдиница))
		{
			$okei = trim(strval($oNode->БазоваяЕдиница->attributes()->Код));

			// Получаем по коду ОКЕЙ
			$oShopMeasure = strlen($okei)
				? Core_Entity::factory('Shop_Measure')->getByOkei($okei, FALSE)
				: NULL;

			// Получаем по названию
			if (is_null($oShopMeasure))
			{
				$sMeasure = trim(strval($oNode->БазоваяЕдиница));
				$sMeasureFull = trim(strval($oNode->БазоваяЕдиница->attributes()->НаименованиеПолное));

				$sMeasure == '' && $sMeasure = $sMeasureFull;

				if ($sMeasure != '')
				{
					$oShopMeasure = Core_Entity::factory('Shop_Measure')->getByName($sMeasure, FALSE);

					if (is_null($oShopMeasure))
					{
						$oShopMeasure = Core_Entity::factory('Shop_Measure');
						$oShopMeasure->name = $sMeasure;
						$oShopMeasure->description = $sMeasureFull;
						$oShopMeasure->okei = $okei;
						$oShopMeasure->save();
					}
				}
			}

			!is_null($oShopMeasure)
				&& $oShopItem->shop_measure_id = $oShopMeasure->id;
		}

		return $this;
	}

	/**
	 * Set Producer
	 * @param string $producerName
	 * @param Shop_Item_Model $shopItem
	 * @return self
	 */
	protected function _setProducer($producerName, Shop_Item_Model $shopItem)
	{
		if (strlen($producerName))
		{
			$oProducer = Core_Entity::factory('Shop', $this->iShopId)
				->Shop_Producers
				->getByName($producerName, FALSE);

			if (is_null($oProducer))
			{
				$oProducer = Core_Entity::factory('Shop_Producer')
					->shop_id($this->iShopId)
					->name($producerName)
					->save();
			}

			$shopItem->shop_producer_id = $oProducer->id;
			$shopItem->save();

			if ($shopItem->modification_id)
			{
				$shopItem->Modification->shop_producer_id = $oProducer->id;
				$shopItem->Modification->save();
			}
		}

		return $this;
	}

	/**
	 * Import orders.xml
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onBeforeImportOrders
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onBeforeImportShopOrder
	 * @hostcms-event Shop_Item_Import_Cml_Controller.onAfterImportShopOrder
	 */
	public function importOrders()
	{
		Core_Event::notify('Shop_Item_Import_Cml_Controller.onBeforeImportOrders', $this);

		if (is_null($this->iShopId))
		{
			throw new Core_Exception(Core::_('Shop_Item.error_shop_id'));
		}

		$oShop = Core_Entity::factory('Shop', $this->iShopId);

		foreach ($this->xpath($this->_oSimpleXMLElement, 'Документ') as $oDocument)
		{
			Core_Event::notify('Shop_Item_Import_Cml_Controller.onBeforeImportShopOrder', $this, array($oDocument));

			$sInvoice = strval($oDocument->Номер);
			$oShop_Order = $oShop->Shop_Orders->getByInvoice($sInvoice);

			if (!is_null($oShop_Order))
			{
				$sOperation = strval($oDocument->ХозОперация);
				if ($sOperation == 'ЗаказТовара' || $sOperation == 'Заказ товара')
				{
					foreach ($this->xpath($oDocument, 'ЗначенияРеквизитов/ЗначениеРеквизита') as $oProperty_Value)
					{
						$sName = strval($oProperty_Value->Наименование);
						$sValue = strval($oProperty_Value->Значение);

						switch ($sName)
						{
							case 'ПометкаУдаления':
								if ($sValue == 'true')
								{
									$oShop_Order->markDeleted();
								}
							break;
							case 'Статус заказа':
								if (!$oShop_Order->shop_order_status_id
									|| $oShop_Order->Shop_Order_Status->name != $sValue)
								{
									$oShop_Order_Status = Core_Entity::factory('Shop_Order_Status')->getByName($sValue, FALSE);

									// Create new
									if (is_null($oShop_Order_Status))
									{
										$oShop_Order_Status = Core_Entity::factory('Shop_Order_Status');
										$oShop_Order_Status->name = $sValue;
										$oShop_Order_Status->save();
									}

									$oShop_Order->shop_order_status_id = $oShop_Order_Status->id;
									$oShop_Order->save();
								}
							break;
						}
					}
				}
			}
			else
			{
				$this->debug && Core_Log::instance()->clear()
					->status(Core_Log::$MESSAGE)
					->write(sprintf('1С, заказ %s не найден', $sInvoice));
			}

			Core_Event::notify('Shop_Item_Import_Cml_Controller.onAfterImportShopOrder', $this, array($oDocument, $oShop_Order));
		}
	}

	/**
	 * Коды валют для МойСклад
	 * @var array
	 */
	protected $_aCurrencyCodes = array(
		'971' => 'AFN',
		'978' => 'EUR',
		'008' => 'ALL',
		'012' => 'DZD',
		'840' => 'USD',
		'978' => 'EUR',
		'973' => 'AOA',
		'951' => 'XCD',
		'951' => 'XCD',
		'032' => 'ARS',
		'051' => 'AMD',
		'533' => 'AWG',
		'036' => 'AUD',
		'978' => 'EUR',
		'944' => 'AZN',
		'044' => 'BSD',
		'048' => 'BHD',
		'050' => 'BDT',
		'052' => 'BBD',
		'974' => 'BYR',
		'978' => 'EUR',
		'084' => 'BZD',
		'952' => 'XOF',
		'060' => 'BMD',
		'064' => 'BTN',
		'356' => 'INR',
		'068' => 'BOB',
		'984' => 'BOV',
		'840' => 'USD',
		'977' => 'BAM',
		'072' => 'BWP',
		'578' => 'NOK',
		'986' => 'BRL',
		'840' => 'USD',
		'096' => 'BND',
		'975' => 'BGN',
		'952' => 'XOF',
		'108' => 'BIF',
		'116' => 'KHR',
		'950' => 'XAF',
		'124' => 'CAD',
		'132' => 'CVE',
		'136' => 'KYD',
		'950' => 'XAF',
		'950' => 'XAF',
		'990' => 'CLF',
		'152' => 'CLP',
		'156' => 'CNY',
		'036' => 'AUD',
		'036' => 'AUD',
		'170' => 'COP',
		'970' => 'COU',
		'174' => 'KMF',
		'950' => 'XAF',
		'976' => 'CDF',
		'554' => 'NZD',
		'188' => 'CRC',
		'952' => 'XOF',
		'191' => 'HRK',
		'931' => 'CUC',
		'192' => 'CUP',
		'532' => 'ANG',
		'978' => 'EUR',
		'203' => 'CZK',
		'208' => 'DKK',
		'262' => 'DJF',
		'951' => 'XCD',
		'214' => 'DOP',
		'840' => 'USD',
		'818' => 'EGP',
		'222' => 'SVC',
		'840' => 'USD',
		'950' => 'XAF',
		'232' => 'ERN',
		'978' => 'EUR',
		'230' => 'ETB',
		'978' => 'EUR',
		'238' => 'FKP',
		'208' => 'DKK',
		'242' => 'FJD',
		'978' => 'EUR',
		'978' => 'EUR',
		'978' => 'EUR',
		'953' => 'XPF',
		'978' => 'EUR',
		'950' => 'XAF',
		'270' => 'GMD',
		'981' => 'GEL',
		'978' => 'EUR',
		'936' => 'GHS',
		'292' => 'GIP',
		'978' => 'EUR',
		'208' => 'DKK',
		'951' => 'XCD',
		'978' => 'EUR',
		'840' => 'USD',
		'320' => 'GTQ',
		'826' => 'GBP',
		'324' => 'GNF',
		'952' => 'XOF',
		'328' => 'GYD',
		'332' => 'HTG',
		'840' => 'USD',
		'036' => 'AUD',
		'978' => 'EUR',
		'340' => 'HNL',
		'344' => 'HKD',
		'348' => 'HUF',
		'352' => 'ISK',
		'356' => 'INR',
		'360' => 'IDR',
		'960' => 'XDR',
		'364' => 'IRR',
		'368' => 'IQD',
		'978' => 'EUR',
		'826' => 'GBP',
		'376' => 'ILS',
		'978' => 'EUR',
		'388' => 'JMD',
		'392' => 'JPY',
		'826' => 'GBP',
		'400' => 'JOD',
		'398' => 'KZT',
		'404' => 'KES',
		'036' => 'AUD',
		'408' => 'KPW',
		'410' => 'KRW',
		'414' => 'KWD',
		'417' => 'KGS',
		'418' => 'LAK',
		'978' => 'EUR',
		'422' => 'LBP',
		'426' => 'LSL',
		'710' => 'ZAR',
		'430' => 'LRD',
		'434' => 'LYD',
		'756' => 'CHF',
		'978' => 'EUR',
		'978' => 'EUR',
		'446' => 'MOP',
		'807' => 'MKD',
		'969' => 'MGA',
		'454' => 'MWK',
		'458' => 'MYR',
		'462' => 'MVR',
		'952' => 'XOF',
		'978' => 'EUR',
		'840' => 'USD',
		'978' => 'EUR',
		'478' => 'MRO',
		'480' => 'MUR',
		'978' => 'EUR',
		'965' => 'XUA',
		'484' => 'MXN',
		'979' => 'MXV',
		'840' => 'USD',
		'498' => 'MDL',
		'978' => 'EUR',
		'496' => 'MNT',
		'978' => 'EUR',
		'951' => 'XCD',
		'504' => 'MAD',
		'943' => 'MZN',
		'104' => 'MMK',
		'516' => 'NAD',
		'710' => 'ZAR',
		'036' => 'AUD',
		'524' => 'NPR',
		'978' => 'EUR',
		'953' => 'XPF',
		'554' => 'NZD',
		'558' => 'NIO',
		'952' => 'XOF',
		'566' => 'NGN',
		'554' => 'NZD',
		'036' => 'AUD',
		'840' => 'USD',
		'578' => 'NOK',
		'512' => 'OMR',
		'586' => 'PKR',
		'840' => 'USD',
		'590' => 'PAB',
		'840' => 'USD',
		'598' => 'PGK',
		'600' => 'PYG',
		'604' => 'PEN',
		'608' => 'PHP',
		'554' => 'NZD',
		'985' => 'PLN',
		'978' => 'EUR',
		'840' => 'USD',
		'634' => 'QAR',
		'978' => 'EUR',
		'946' => 'RON',
		'643' => 'RUB',
		'810' => 'RUR',
		'646' => 'RWF',
		'978' => 'EUR',
		'654' => 'SHP',
		'951' => 'XCD',
		'951' => 'XCD',
		'978' => 'EUR',
		'978' => 'EUR',
		'951' => 'XCD',
		'882' => 'WST',
		'978' => 'EUR',
		'678' => 'STD',
		'682' => 'SAR',
		'952' => 'XOF',
		'941' => 'RSD',
		'690' => 'SCR',
		'694' => 'SLL',
		'702' => 'SGD',
		'532' => 'ANG',
		'994' => 'XSU',
		'978' => 'EUR',
		'978' => 'EUR',
		'090' => 'SBD',
		'706' => 'SOS',
		'710' => 'ZAR',
		'728' => 'SSP',
		'978' => 'EUR',
		'144' => 'LKR',
		'938' => 'SDG',
		'968' => 'SRD',
		'578' => 'NOK',
		'748' => 'SZL',
		'752' => 'SEK',
		'947' => 'CHE',
		'756' => 'CHF',
		'948' => 'CHW',
		'760' => 'SYP',
		'901' => 'TWD',
		'972' => 'TJS',
		'834' => 'TZS',
		'764' => 'THB',
		'840' => 'USD',
		'952' => 'XOF',
		'554' => 'NZD',
		'776' => 'TOP',
		'780' => 'TTD',
		'788' => 'TND',
		'949' => 'TRY',
		'934' => 'TMT',
		'840' => 'USD',
		'036' => 'AUD',
		'800' => 'UGX',
		'980' => 'UAH',
		'784' => 'AED',
		'826' => 'GBP',
		'840' => 'USD',
		'997' => 'USN',
		'840' => 'USD',
		'940' => 'UYI',
		'858' => 'UYU',
		'860' => 'UZS',
		'548' => 'VUV',
		'937' => 'VEF',
		'704' => 'VND',
		'840' => 'USD',
		'840' => 'USD',
		'953' => 'XPF',
		'504' => 'MAD',
		'886' => 'YER',
		'967' => 'ZMW',
		'932' => 'ZWL',
		'955' => 'XBA',
		'956' => 'XBB',
		'957' => 'XBC',
		'958' => 'XBD',
		'963' => 'XTS',
		'999' => 'XXX',
		'959' => 'XAU',
		'964' => 'XPD',
		'962' => 'XPT',
		'961' => 'XAG'
	);

	protected $_aShop_Warehouse_Inventory_Ids = array();

	protected function _getInventory($shop_warehouse_id)
	{
		if (!isset($this->_aShop_Warehouse_Inventory_Ids[$shop_warehouse_id]))
		{
			$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory');
			$oShop_Warehouse_Inventory->shop_warehouse_id = $shop_warehouse_id;
			$oShop_Warehouse_Inventory->description = Core::_('Shop_Exchange.shop_warehouse_inventory');
			$oShop_Warehouse_Inventory->number = '';
			$oShop_Warehouse_Inventory->posted = 0;
			$oShop_Warehouse_Inventory->save();

			$oShop_Warehouse_Inventory->number = $oShop_Warehouse_Inventory->id;
			$oShop_Warehouse_Inventory->save();

			$this->_aShop_Warehouse_Inventory_Ids[$shop_warehouse_id] = $oShop_Warehouse_Inventory->id;
		}

		return Core_Entity::factory('Shop_Warehouse_Inventory', $this->_aShop_Warehouse_Inventory_Ids[$shop_warehouse_id]);
	}

	protected $_oShop_Price_Setting_Id = NULL;

	protected function _getPrices()
	{
		if (is_null($this->_oShop_Price_Setting_Id))
		{
			$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting');
			$oShop_Price_Setting->shop_id = $this->iShopId;
			$oShop_Price_Setting->number = '';
			$oShop_Price_Setting->posted = 0;
			$oShop_Price_Setting->description = Core::_('Shop_Exchange.shop_price_setting');
			$oShop_Price_Setting->save();

			$oShop_Price_Setting->number = $oShop_Price_Setting->id;
			$oShop_Price_Setting->save();

			$this->_oShop_Price_Setting_Id = $oShop_Price_Setting->id;
		}

		return Core_Entity::factory('Shop_Price_Setting', $this->_oShop_Price_Setting_Id);
	}

	public function postAll()
	{
		foreach ($this->_aShop_Warehouse_Inventory_Ids as $shop_warehouse_id => $shop_warehouse_inventory_id)
		{
			$oShop_Warehouse_Inventory = Core_Entity::factory('Shop_Warehouse_Inventory', $shop_warehouse_inventory_id);
			$oShop_Warehouse_Inventory->post();
		}

		if (!is_null($this->_oShop_Price_Setting_Id))
		{
			$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting', $this->_oShop_Price_Setting_Id);
			$oShop_Price_Setting->post();
		}
	}
}