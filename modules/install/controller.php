<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Install.
 *
 * @package HostCMS
 * @subpackage Install
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Install_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Template path
	 * @var string
	 */
	protected $_sTemplatePath = null;

	/**
	 * Get template path
	 * @return string
	 */
	public function getTemplatePath()
	{
		return $this->_sTemplatePath;
	}

	/**
	 * Set template path
	 * @param string $_sTemplatePath path
	 * @return self
	 */
	public function setTemplatePath($_sTemplatePath)
	{
		$this->_sTemplatePath = $_sTemplatePath;
		return $this;
	}

	/**
	 * Replace macroses in file
	 * @param string $str file path
	 * @param array $aReplace list of macroses
	 * @return string
	 */
	public function macroReplace($str, $aReplace)
	{
		if (count($aReplace) > 0)
		{
			$str = strtr($str, $aReplace);
			/*foreach ($aReplace as $key => $value)
			{
				$str = str_replace($key, $value, $str);
			}*/
		}

		return $str;
	}

	/**
	 * Replace macroses in file
	 * @param string $filename file path
	 * @param array $aReplace list of macroses
	 * @return string
	 */
	public function loadFile($filename, $aReplace = array())
	{
		$filecontent = file_get_contents($filename);

		if ($filecontent && count($aReplace) > 0)
		{
			$filecontent = $this->macroReplace($filecontent, $aReplace);
		}

		return $filecontent;
	}

	/**
	 * Replace macroses in file
	 * @param string $filename file path
	 * @param array $aReplace list of macroses
	 */
	public function replaceFile($filename, $aReplace = array())
	{
		$filecontent = $this->loadFile($filename, $aReplace);
		file_put_contents($filename, $filecontent);
	}

	/**
	* Определение расширения файла по его названию без расширения
	*
	* @param $path_file_without_extension - путь к файлу без расширения
	*/
	public function getFileExtension($path_file_without_extension)
	{
		clearstatcache();

		// Директория, в которой находится файл
		$dirname = dirname($path_file_without_extension);

		// Имя файла
		$filename = basename($path_file_without_extension);

		$extension = '';

		if (is_dir($dirname) && !is_link($dirname) && $handle = @opendir($dirname))
		{
			// Шаблон сравнения
			$str = "/^{$filename}.([a-zA-Z]*)$/";

			// Просматриваем файлы директории
			while (FALSE !== ($file = readdir($handle)))
			{
				if (preg_match($str, $file , $regs) && isset($regs[1]) && $regs[1] != '' )
				{
					$extension = $regs[1];
					break;
				}
			}
			closedir($handle);

			return $extension;
		}
	}

	 /**
	 * Копирование изображений для ИЭ
	 *
	 * @param $targetInformationsystemItemId - идентификатор нового созданого ИЭ
	 * @param $sourceInformationsystemId - идентификатор копируемой ИС
	 * @param $sourceInformationsystemItemId - идентификатор копируемого ИЭ
	 * @param string $fileName - имя большого файла изображения
	 * @param string $smallFileName - имя малого файла изображения
	 */
	public function moveInformationsystemItemImage($targetInformationsystemItemId, $sourceInformationsystemId, $sourceInformationsystemItemId, $fileName = NULL, $smallFileName = NULL)
	{
		$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $targetInformationsystemItemId);

		$sDir = $this->getTemplatePath() . "tmp/upload/information_system_{$sourceInformationsystemId}/" . Core_File::getNestingDirPath($sourceInformationsystemItemId, 3) . "/item_{$sourceInformationsystemItemId}/";

		if (is_null($fileName))
		{
			$information_item_image_from = $sDir . "information_items_{$sourceInformationsystemItemId}";

			// Получаем расширение файла
			$ext = $this->getFileExtension($information_item_image_from);

			if (!empty($ext))
			{
				$ext = '.' . $ext;
			}
			$information_item_image_from .= $ext;
		}
		else
		{
			$information_item_image_from = $sDir . $fileName;

			$ext = '.' . Core_File::getExtension($fileName);
		}

		// Путь к директории хранения файлов информационного элемента
		$item_dir = $oInformationsystem_Item->getItemPath();

		if (is_file($information_item_image_from))
		{
			$information_item_image_to = $item_dir . "item_{$targetInformationsystemItemId}" . $ext;
 			Core_File::copy($information_item_image_from, $information_item_image_to);

			$oInformationsystem_Item->image_large = basename($information_item_image_to);

			$aImageSize = Core_Image::instance()->getImageSize($information_item_image_to);
			if ($aImageSize)
			{
				$oInformationsystem_Item->image_large_width = $aImageSize['width'];
				$oInformationsystem_Item->image_large_height = $aImageSize['height'];
			}
		}

		if (is_null($smallFileName))
		{
			$information_item_small_image_from = $sDir . "small_information_items_{$sourceInformationsystemItemId}";

			// Получаем расширение файла
			$ext = $this->getFileExtension($information_item_small_image_from);

			if (!empty($ext))
			{
				$ext = '.' . $ext;
			}
			$information_item_small_image_from .= $ext;
		}
		else
		{
			$information_item_small_image_from = $sDir . $smallFileName;

			$ext = '.' . Core_File::getExtension($smallFileName);
		}

		if (is_file($information_item_small_image_from))
		{
			$information_item_small_image_to = $item_dir . "small_item_{$targetInformationsystemItemId}" . $ext;
			Core_File::copy($information_item_small_image_from, $information_item_small_image_to);

			$oInformationsystem_Item->image_small = basename($information_item_small_image_to);

			$aImageSize = Core_Image::instance()->getImageSize($information_item_small_image_to);
			if ($aImageSize)
			{
				$oInformationsystem_Item->image_small_width = $aImageSize['width'];
				$oInformationsystem_Item->image_small_height = $aImageSize['height'];
			}
		}

		$oInformationsystem_Item->save();
	}

	 /**
	 * Копирование изображений для ИГ
	 *
	 * @param $targetInformationsystemGroupId - идентификатор новой созданной ИГ
	 * @param $sourceInformationsystemId - идентификатор копирумой ИС
	 * @param $sourceInformationsystemGroupId - идентификатор копируемой ИГ
	 * @param string $fileName - имя большого файла изображения
	 * @param string $smallFileName - имя малого файла изображения
	 */
	public function moveInformationsystemGroupImage($targetInformationsystemGroupId, $sourceInformationsystemId, $sourceInformationsystemGroupId, $fileName = NULL, $smallFileName = NULL)
	{
		$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $targetInformationsystemGroupId);

		$sDir = $this->getTemplatePath() . "tmp/upload/information_system_{$sourceInformationsystemId}/" . Core_File::getNestingDirPath($sourceInformationsystemGroupId, 3) . "/group_{$sourceInformationsystemGroupId}/";

		if (is_null($fileName))
		{
			$information_group_image_from = $sDir . "information_groups_{$sourceInformationsystemGroupId}";

			// Получаем расширение файла
			$ext = $this->getFileExtension($information_group_image_from);

			if (!empty($ext))
			{
				$ext = '.' . $ext;
			}
			$information_group_image_from .= $ext;
		}
		else
		{
			$information_group_image_from = $sDir . $fileName;

			$ext = '.' . Core_File::getExtension($fileName);
		}

		// Путь к директории хранения файлов информационной группы
		$group_dir = $oInformationsystem_Group->getGroupPath();

		if (is_file($information_group_image_from))
		{
			$information_group_image_to = $group_dir . "group_{$targetInformationsystemGroupId}" . $ext;

 			Core_File::copy($information_group_image_from, $information_group_image_to);

			$oInformationsystem_Group->image_large = basename($information_group_image_to);

		}

		if (is_null($smallFileName))
		{
			$information_group_small_image_from = $sDir . "small_information_groups_{$sourceInformationsystemGroupId}";

			// Получаем расширение файла
			$ext = $this->getFileExtension($information_group_small_image_from);

			if (!empty($ext))
			{
				$ext = '.' . $ext;
			}
			$information_group_small_image_from .= $ext;
		}
		else
		{
			$information_group_small_image_from = $sDir . $smallFileName;

			$ext = '.' . Core_File::getExtension($smallFileName);
		}

		if (is_file($information_group_small_image_from))
		{
			$information_group_small_image_to = $group_dir . "small_group_{$targetInformationsystemGroupId}" . $ext;
			Core_File::copy($information_group_small_image_from, $information_group_small_image_to);

			$oInformationsystem_Group->image_small = basename($information_group_small_image_to);
		}

		$oInformationsystem_Group->save();
	}

	/**
	 * Копирование изображений для доп. свойств ИЭ
	 *
	 * @param $informationsystemItemId - Идентификатор нового ИЭ
	 * @param $informationsystemItemPropertyId - Идентификатор нового доп. свойства типа "Файл"
	 * @param $copyInformationsystemId - Идентификатор копирумой ИС
	 * @param $copyInformationsystemItemId - Идентификатор копируемого ИЭ
	 * @param $copyInformationsystemPropertyValueId - Идентификатор значения доп. свойства типа "Файл" копируемого ИЭ
	 */
	public function moveInformationsystemItemPropertyImage($informationsystemItemId, $informationsystemItemPropertyId, $copyInformationsystemId, $copyInformationsystemItemId, $copyInformationsystemPropertyValueId)
	{
		$informationsystemItemId = intval($informationsystemItemId);
		$informationsystemItemPropertyId = intval($informationsystemItemPropertyId);
		$copyInformationsystemId = intval($copyInformationsystemId);
		$copyInformationsystemItemId = intval($copyInformationsystemItemId);
		$copyInformationsystemPropertyValueId = intval($copyInformationsystemPropertyValueId);

		$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item', $informationsystemItemId);

		$item_dir = $oInformationsystem_Item->getItemPath();

		$information_item_property_image_from = $this->getTemplatePath() . "tmp/upload/information_system_{$copyInformationsystemId}/" . Core_File::getNestingDirPath($copyInformationsystemItemId, 3) . "/item_{$copyInformationsystemItemId}/information_items_property_{$copyInformationsystemPropertyValueId}";

		$ext = $this->getFileExtension($information_item_property_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$information_item_property_image_from .= $ext;

		$oProperty = Core_Entity::factory('Property')->find($informationsystemItemPropertyId);

		$oValue = $oProperty->createNewValue($informationsystemItemId);
		$oValue->save();

		if (is_file($information_item_property_image_from))
		{
			$information_item_property_image_to = $item_dir . "information_items_property_{$oValue->id}" . $ext;
			Core_File::copy($information_item_property_image_from, $information_item_property_image_to);

			$oValue->file_name = $oValue->file = basename($information_item_property_image_to);
		}

		$information_item_property_small_image_from = $this->getTemplatePath() . "tmp/upload/information_system_{$copyInformationsystemId}/" . Core_File::getNestingDirPath($copyInformationsystemItemId, 3) . "/item_{$copyInformationsystemItemId}/small_information_items_property_{$copyInformationsystemPropertyValueId}";

		$ext = $this->getFileExtension($information_item_property_small_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$information_item_property_small_image_from .= $ext;

		if (is_file($information_item_property_small_image_from))
		{
			$information_item_property_small_image_to = $item_dir . "small_information_items_property_{$oValue->id}" . $ext;
			Core_File::copy($information_item_property_small_image_from, $information_item_property_small_image_to);

			$oValue->file_small_name = $oValue->file_small = basename($information_item_property_small_image_to);
		}

		$oValue->save();
	}

	/**
	 * Копирование изображений для доп. свойств ИГ
	 *
	 * @param $informationsystemGroupId - Идентификатор новой ИГ
	 * @param $informationsystemGroupPropertyId - Идентификатор нового доп. свойства типа "Файл"
	 * @param $copyInformationsystemId - Идентификатор копирумой ИС
	 * @param $copyInformationsystemGroupId - Идентификатор копируемой ИГ
	 * @param $copyInformationsystemPropertyValueId - Идентификатор значения доп. свойства типа "Файл" копируемой ИГ
	 */
	public function moveInformationsystemGroupPropertyImage($informationsystemGroupId, $informationsystemGroupPropertyId, $copyInformationsystemId, $copyInformationsystemGroupId, $copyInformationsystemPropertyValueId)
	{
		$informationsystemGroupId = intval($informationsystemGroupId);
		$informationsystemGroupPropertyId = intval($informationsystemGroupPropertyId);
		$copyInformationsystemId = intval($copyInformationsystemId);
		$copyInformationsystemGroupId = intval($copyInformationsystemGroupId);
		$copyInformationsystemPropertyValueId = intval($copyInformationsystemPropertyValueId);

		$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $informationsystemGroupId);

		$group_dir = $oInformationsystem_Group->getGroupPath();

		$information_group_property_image_from = $this->getTemplatePath() . "tmp/upload/information_system_{$copyInformationsystemId}/" . Core_File::getNestingDirPath($copyInformationsystemGroupId, 3) . "/group_{$copyInformationsystemGroupId}/information_groups_property_{$copyInformationsystemPropertyValueId}";

		$ext = $this->getFileExtension($information_group_property_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}

		$information_group_property_image_from .= $ext;

		$oProperty = Core_Entity::factory('Property')->find($informationsystemGroupPropertyId);

		$oValue = $oProperty->createNewValue($informationsystemGroupId);
		$oValue->save();

		if (is_file($information_group_property_image_from))
		{
			$information_group_property_image_to = $group_dir . "information_groups_property_" . $oValue->id . $ext;
			Core_File::copy($information_group_property_image_from, $information_group_property_image_to);

			$oValue->file_name = $oValue->file = basename($information_group_property_image_to);
		}

		$information_group_property_small_image_from = $this->getTemplatePath() . "tmp/upload/information_system_{$copyInformationsystemId}/" . Core_File::getNestingDirPath($copyInformationsystemGroupId, 3) . "/item_{$copyInformationsystemGroupId}/small_information_groups_property_{$copyInformationsystemPropertyValueId}";

		$ext = $this->getFileExtension($information_group_property_small_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$information_group_property_small_image_from .= $ext;

		if (is_file($information_group_property_small_image_from))
		{
			$information_group_property_small_image_to = $group_dir . "small_information_items_property_" . $oValue->id . $ext;
			Core_File::copy($information_group_property_small_image_from, $information_group_property_small_image_to);

			$oValue->file_small_name = $oValue->file_small = basename($information_group_property_small_image_to);
		}

		$oValue->save();
	}

	 /**
	 * Копирование изображений для магазина
	 *
	 * @param $targetShopItemId - идентификатор нового созданого товара
	 * @param $sourceShopId - идентификатор копируемого магазина
	 * @param $sourceShopItemId - идентификатор копируемого товара
	 * @param string $fileName - имя большого файла изображения
	 * @param string $smallFileName - имя малого файла изображения
	 */
	public function moveShopItemImage($targetShopItemId, $sourceShopId, $sourceShopItemId, $fileName = NULL, $smallFileName = NULL)
	{
		$oShop_Item = Core_Entity::factory('Shop_Item', $targetShopItemId);

		$sDir = $this->getTemplatePath() . "tmp/upload/shop_{$sourceShopId}/" . Core_File::getNestingDirPath($sourceShopItemId, 3) . "/item_{$sourceShopItemId}/";

		if (is_null($fileName))
		{
			$shop_item_image_from = $sDir . "shop_items_catalog_image{$sourceShopItemId}";

			// Получаем расширение файла
			$ext = $this->getFileExtension($shop_item_image_from);

			if (!empty($ext))
			{
				$ext = '.' . $ext;
			}
			$shop_item_image_from .= $ext;
		}
		else
		{
			$shop_item_image_from = $sDir . $fileName;

			$ext = '.' . Core_File::getExtension($fileName);
		}

		// Путь к директории хранения файлов товара
		$item_dir = $oShop_Item->getItemPath();

		if (is_file($shop_item_image_from))
		{
			$shop_item_image_to = $item_dir . "item_{$targetShopItemId}" . $ext;

 			Core_File::copy($shop_item_image_from, $shop_item_image_to);

			$oShop_Item->image_large = basename($shop_item_image_to);

			$aImageSize = Core_Image::instance()->getImageSize($shop_item_image_to);
			if ($aImageSize)
			{
				$oShop_Item->image_large_width = $aImageSize['width'];
				$oShop_Item->image_large_height = $aImageSize['height'];
			}
		}

		if (is_null($smallFileName))
		{
			$shop_item_small_image_from = $sDir . "small_shop_items_catalog_image{$sourceShopItemId}";

			// Получаем расширение файла
			$ext = $this->getFileExtension($shop_item_small_image_from);

			if (!empty($ext))
			{
				$ext = '.' . $ext;
			}
			$shop_item_small_image_from .= $ext;
		}
		else
		{
			$shop_item_small_image_from = $sDir . $smallFileName;

			$ext = '.' . Core_File::getExtension($smallFileName);
		}

		if (is_file($shop_item_small_image_from))
		{
			$shop_item_small_image_to = $item_dir . "small_item_{$targetShopItemId}" . $ext;
			Core_File::copy($shop_item_small_image_from, $shop_item_small_image_to);

			$oShop_Item->image_small = basename($shop_item_small_image_to);

			$aImageSize = Core_Image::instance()->getImageSize($shop_item_small_image_to);
			if ($aImageSize)
			{
				$oShop_Item->image_small_width = $aImageSize['width'];
				$oShop_Item->image_small_height = $aImageSize['height'];
			}
		}
		$oShop_Item->save();
	}

	/**
	 * Копирование изображений для группы товаров
	 *
	 * @param int $shopGroupId - идентификатор новой созданной группы магазина
	 * @param int $copyShopId - идентификатор копирумого магазина
	 * @param int $copyGroupId - идентификатор копируемой группы товаров
	 * @param string $fileName - имя большого файла изображения
	 * @param string $smallFileName - имя малого файла изображения
	 */
	public function moveShopGroupImage($shopGroupId, $copyShopId, $copyGroupId, $fileName = NULL, $smallFileName = NULL)
	{
		$oShop_Group = Core_Entity::factory('Shop_Group', $shopGroupId);

		$sDir = $this->getTemplatePath() . "tmp/upload/shop_{$copyShopId}/" . Core_File::getNestingDirPath($copyGroupId, 3) . "/group_{$copyGroupId}/";

		if (is_null($fileName))
		{
			$shop_group_image_from = $sDir . "shop_group_image{$copyGroupId}";

			// Получаем расширение файла
			$ext = $this->getFileExtension($shop_group_image_from);

			if (!empty($ext))
			{
				$ext = '.' . $ext;
			}
			$shop_group_image_from .= $ext;
		}
		else
		{
			$shop_group_image_from = $sDir . $fileName;

			$ext = '.' . Core_File::getExtension($fileName);
		}

		$group_dir = $oShop_Group->getGroupPath();

		if (is_file($shop_group_image_from))
		{
			$shop_group_image_to = $group_dir . "group_{$shopGroupId}" . $ext;
			Core_File::copy($shop_group_image_from, $shop_group_image_to);

			$aImageSize = Core_Image::instance()->getImageSize($shop_group_image_to);

			if ($aImageSize)
			{
				$oShop_Group->image_large_width = $aImageSize['width'];
				$oShop_Group->image_large_height = $aImageSize['height'];
			}

			$oShop_Group->image_large = basename($shop_group_image_to);
		}

		if (is_null($smallFileName))
		{
			$shop_group_small_image_from = $sDir . "small_shop_group_image{$copyGroupId}";

			// Получаем расширение файла
			$ext = $this->getFileExtension($shop_group_small_image_from);

			if (!empty($ext))
			{
				$ext = '.' . $ext;
			}
			$shop_group_small_image_from .= $ext;
		}
		else
		{
			$shop_group_small_image_from = $sDir . $smallFileName;

			$ext = '.' . Core_File::getExtension($smallFileName);
		}

		if (is_file($shop_group_small_image_from))
		{
			$shop_group_small_image_to = $group_dir . "small_group_{$shopGroupId}" . $ext;
			Core_File::copy($shop_group_small_image_from, $shop_group_small_image_to);

			$aImageSize = Core_Image::instance()->getImageSize($shop_group_small_image_to);

			if ($aImageSize)
			{
				$oShop_Group->image_small_width = $aImageSize['width'];
				$oShop_Group->image_small_height = $aImageSize['height'];
			}

			$oShop_Group->image_small = basename($shop_group_small_image_to);
		}

		// Обновляем информацию о группе товаров после создания изображений
		$oShop_Group->save();
	}

	/**
	 * Копирование изображений для доп. свойств товара
	 *
	 * @param $shopItemId - идентификатор нового созданого товара
	 * @param $shopItemPropertyId - идентификатор нового созданного доп. свойства
	 * @param $copyShopId - идентификатор копирумого магазина
	 * @param $copyShopItemId - идентификатор копируемого товара
	 * @param $copyShopItemPropertyId - идентификатор доп. свойства типа "Файл" копируемого товара
	 */
	public function moveShopItemPropertyImage($shopItemId, $shopItemPropertyId, $copyShopId, $copyShopItemId, $copyShopItemPropertyId)
	{
		$oShop_Item = Core_Entity::factory('Shop_Item', $shopItemId);
		$item_dir = $oShop_Item->getItemPath();

		$shop_item_property_image_from = $this->getTemplatePath() . "tmp/upload/shop_{$copyShopId}/" . Core_File::getNestingDirPath($copyShopItemId, 3) . "/item_{$copyShopItemId}/shop_property_file_{$copyShopItemId}_{$copyShopItemPropertyId}";

		// Получаем расширение файла
		$ext = $this->getFileExtension($shop_item_property_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$shop_item_property_image_from .= $ext;

		$oProperty = Core_Entity::factory('Property')->find($shopItemPropertyId);

		$oValue = $oProperty->createNewValue($shopItemId);
		$oValue->save();

		if (is_file($shop_item_property_image_from))
		{
			$shop_item_property_image_to = $item_dir . "shop_property_file_{$shopItemId}_{$oValue->id}" . $ext;
			Core_File::copy($shop_item_property_image_from, $shop_item_property_image_to);

			$oValue->file_name = $oValue->file = basename($shop_item_property_image_to);
		}

		$shop_item_property_small_image_from = $this->getTemplatePath() . "tmp/upload/shop_{$copyShopId}/" . Core_File::getNestingDirPath($copyShopItemId, 3) . "/item_{$copyShopItemId}/small_shop_property_file_{$copyShopItemId}_{$copyShopItemPropertyId}";

		// Получаем расширение файла
		$ext = $this->getFileExtension($shop_item_property_small_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}

		$shop_item_property_small_image_from .= $ext;

		if (is_file($shop_item_property_small_image_from))
		{
			$shop_item_property_small_image_to = $item_dir . "small_shop_property_file_{$shopItemId}_{$oValue->id}" . $ext;

			Core_File::copy($shop_item_property_small_image_from, $shop_item_property_small_image_to);

			$oValue->file_small_name = $oValue->file_small = basename($shop_item_property_small_image_to);
		}

		$oValue->save();
	}

	/**
	 * Копирование изображений для доп. свойств групп товара
	 *
	 * @param $shopGroupId - идентификатор новой созданой группы товара
	 * @param $shopGroupPropertyId - идентификатор нового созданного доп. свойства
	 * @param $copyShopId - идентификатор копирумого магазина
	 * @param $copyShopGroupId - идентификатор копируемой группы товара
	 * @param $copyShopGroupPropertyId - идентификатор доп. свойства типа "Файл" копируемой группы
	 */
	public function moveShopGroupPropertyImage($shopGroupId, $shopGroupPropertyId, $copyShopId, $copyShopGroupId, $copyShopGroupPropertyId)
	{
		$oShop_Group = Core_Entity::factory('Shop_Group', $shopGroupId);
		$group_dir = $oShop_Group->getGroupPath();

		$shop_group_property_image_from = $this->getTemplatePath() . "tmp/upload/shop_{$copyShopId}/" . Core_File::getNestingDirPath($copyShopGroupId, 3) . "/group_{$copyShopGroupId}/shop_property_file_{$copyShopGroupId}_{$copyShopGroupPropertyId}";

		// Получаем расширение файла
		$ext = $this->getFileExtension($shop_group_property_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$shop_group_property_image_from .= $ext;

		$oProperty = Core_Entity::factory('Property')->find($shopGroupPropertyId);

		$oValue = $oProperty->createNewValue($shopGroupId);
		$oValue->save();

		if (is_file($shop_group_property_image_from))
		{
			$shop_group_property_image_to = $group_dir . "shop_property_file_{$shopGroupId}_{$oValue->id}" . $ext;
			Core_File::copy($shop_group_property_image_from, $shop_group_property_image_to);

			$oValue->file_name = $oValue->file = basename($shop_group_property_image_to);
		}

		$shop_group_property_small_image_from = $this->getTemplatePath() . "tmp/upload/shop_{$copyShopId}/" . Core_File::getNestingDirPath($copyShopGroupId, 3) . "/group_{$copyShopGroupId}/small_shop_property_file_{$copyShopGroupId}_{$copyShopGroupPropertyId}";

		// Получаем расширение файла
		$ext = $this->getFileExtension($shop_group_property_small_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}

		$shop_group_property_small_image_from .= $ext;

		if (is_file($shop_group_property_small_image_from))
		{
			$shop_group_property_small_image_to = $group_dir . "small_shop_property_file_{$shopGroupId}_{$oValue->id}" . $ext;

			Core_File::copy($shop_group_property_small_image_from, $shop_group_property_small_image_to);

			$oValue->file_small_name = $oValue->file_small = basename($shop_group_property_small_image_to);
		}

		$oValue->save();
	}

	/**
	 * Копирование значения доп. свойства типа "Файл" узла структуры
	 *
	 * @param $structureId - Идентификатор нового созданого узла структуры
	 * @param $structurePropertyImageId - Идентификатор нового созданного доп. свойства
	 * @param $copySiteId - Идентификатор сайта, которому принадлежит копируемая структура
	 * @param $copyStructureId - Идентификатор копируемого узла структуры
	 * @param $copyStructurePropertyValueId - Идентификатор значения доп. свойства, значение которого копируется
	 */

	public function MoveStructureItemPropertyImage($structureId, $structurePropertyImageId, $copySiteId, $copyStructureId, $copyStructurePropertyValueId)
	{
		$structureId = intval($structureId);
		$structurePropertyImageId = intval($structurePropertyImageId);
		$copySiteId = intval($copySiteId);
		$copyStructureId = intval($copyStructureId);
		$copyStructurePropertyValueId = intval($copyStructurePropertyValueId);

		$oStructure = Core_Entity::factory('Structure', $structureId);
		$structure_dir = $oStructure->getDirPath();

		$structure_file_path_from = $this->GetTemplatePath() . "tmp/upload/structure_{$copySiteId}/" . Core_File::getNestingDirPath($copyStructureId, 3) . '/structure_' . $copyStructureId . '/structure_property_image_' . $copyStructurePropertyValueId;

		// Получаем расширение файла
		$ext = $this->getFileExtension($structure_file_path_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$structure_file_path_from .= $ext;

		$oProperty = Core_Entity::factory('Property')->find($structurePropertyImageId);

		$oValue = $oProperty->createNewValue($structureId);
		$oValue->save();

		if (is_file($structure_file_path_from))
		{
			$structure_file_path_to = $structure_dir . "structure_property_image_{$oValue->id}" . $ext;
			Core_File::copy($structure_file_path_from, $structure_file_path_to);

			$oValue->file_name = $oValue->file = basename($structure_file_path_to);
		}

		$structure_small_file_path_from = $this->GetTemplatePath() . "tmp/upload/structure_{$copySiteId}/" . Core_File::getNestingDirPath($copyStructureId, 3) . '/structure_' . $copyStructureId . '/small_structure_property_image_' . $copyStructurePropertyValueId;

		// Получаем расширение файла
		$ext = $this->getFileExtension($structure_small_file_path_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$structure_small_file_path_from .= $ext;

		if (is_file($structure_small_file_path_from))
		{
			$structure_small_file_path_to = $structure_dir . "small_structure_property_image_{$oValue->id}" . $ext;
			Core_File::copy($structure_small_file_path_from, $structure_small_file_path_to);

			$oValue->file_small_name = $oValue->file_small = basename($structure_small_file_path_to);
		}

		$oValue->save();
	}

	/**
	 * Копирование изображений для группы товаров
	 *
	 * @param int $shopProducerId - идентификатор нового созданного производителя
	 * @param int $copyShopId - идентификатор копирумого магазина
	 * @param int $copyProducerId - идентификатор копируемого производителя
	 */
	public function moveProducerImage($shopProducerId, $copyShopId, $copyProducerId)
	{
		$oShop_Producer = Core_Entity::factory('Shop_Producer', $shopProducerId);
		$producer_dir = $oShop_Producer->getProducerPath();

		$shop_producer_image_from = $this->getTemplatePath() . "tmp/upload/shop_{$copyShopId}/producers/shop_producer_image{$copyProducerId}";

		// Получаем расширение файла
		$ext = $this->getFileExtension($shop_producer_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$shop_producer_image_from .= $ext;

		if (is_file($shop_producer_image_from))
		{
			$shop_producer_image_to = $producer_dir . "shop_producer_image{$shopProducerId}" . $ext;
			Core_File::copy($shop_producer_image_from, $shop_producer_image_to);

			$oShop_Producer->image_large = basename($shop_producer_image_to);
		}

		$shop_producer_small_image_from = $this->getTemplatePath() . "tmp/upload/shop_{$copyShopId}/producers/small_shop_producer_image{$copyProducerId}";

		// Получаем расширение файла
		$ext = $this->getFileExtension($shop_producer_small_image_from);

		if (!empty($ext))
		{
			$ext = '.' . $ext;
		}
		$shop_producer_small_image_from .= $ext;

		if (is_file($shop_producer_small_image_from))
		{
			$shop_producer_small_image_to = $producer_dir . "small_shop_group_image{$shopProducerId}" . $ext;
			Core_File::copy($shop_producer_small_image_from, $shop_producer_small_image_to);

			$oShop_Producer->image_small = basename($shop_producer_small_image_to);
		}

		// Обновляем информацию о производителе после создания изображений
		$oShop_Producer->save();
	}
}