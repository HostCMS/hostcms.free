<?php

/**
 * Импорт RSS
 *
 * @package HostCMS 6\cron
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

/* Адреса RSS-каналов */
$aSourcesList = array(
	'https://www.hostcms.ru/news/rss/',
);

/* Идентификатор информационной системы, в которую помещаются элементы */
$informationsystem_id = 1;

/* Группа, в которую помещается новый элемент */
$informationsystem_group_id = 0;

$oInformationsystem = Core_Entity::factory('Informationsystem', $informationsystem_id);
$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $informationsystem_group_id);

$oSite = $oInformationsystem->Site;

define('CURRENT_SITE', $oSite->id);
Core::initConstants($oSite);

$iImported = 0;

$oCore_Rss_Read = new Core_Rss_Read();

foreach ($aSourcesList as $url)
{
	$aRssData = $oCore_Rss_Read
		->clear()
		->loadUrl($url)
		->parse();

	foreach ($aRssData['items'] as $itemKey => $aItem)
	{
		$oSameItem = $oInformationsystem->Informationsystem_Items->getByName($aItem['title']);

		/* Если не найдено элементов с таким же именем */
		if (is_null($oSameItem))
		{
			$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
			$oInformationsystem_Item->name = $aItem['title'];
			$oInformationsystem_Item->description = $aItem['description'];
			$oInformationsystem_Item->text = $aItem['description'] . "<p>Источник: <a href=\"{$aItem['link']}\">{$aItem['link']}</a>";
			$oInformationsystem_Item->datetime = Core_Date::timestamp2sql(strtotime($aItem['pubdate']));
			$oInformationsystem_Item->informationsystem_group_id = intval($oInformationsystem_Group->id);
			$oInformationsystem_Item->path = '';

			if (strlen($oInformationsystem_Item->name))
			{
				// Save informationsystem item
				$oInformationsystem->add($oInformationsystem_Item);

				if (isset($aItem['enclosure']['url']) && strpos($aItem['enclosure']['url'], 'http') === 0)
				{
					$Core_Http = Core_Http::instance()
						->url($aItem['enclosure']['url'])
						->execute();

					// Определяем расширение файла
					$ext = Core_File::getExtension($aItem['enclosure']['url']);

					$temp_file = tempnam(TMP_DIR, 'rss') . '.' . $ext;
					Core_File::write($temp_file, $Core_Http->getDecompressedBody());

					$param = array();

					// Путь к файлу-источнику большого изображения;
					$param['large_image_source'] = $temp_file;

					$large_image = 'information_items_' . $oInformationsystem_Item->id . '.' . $ext;
					$small_image = 'small_' . $large_image;

					// Оригинальное имя файла большого изображения
					$param['large_image_name'] = $large_image;

					// Оригинальное имя файла малого изображения
					$param['small_image_name'] = $small_image;

					// Путь к создаваемому файлу большого изображения;
					$param['large_image_target'] = $oInformationsystem_Item->getItemPath() . $large_image;

					// Путь к создаваемому файлу малого изображения;
					$param['small_image_target'] = $oInformationsystem_Item->getItemPath() . $small_image;

					// Использовать большое изображение для создания малого
					$param['create_small_image_from_large'] = TRUE;
					$param['watermark_file_path'] = $oInformationsystem->getWatermarkFilePath();
					$param['watermark_position_x'] = $oInformationsystem->watermark_default_position_x;
					$param['watermark_position_y'] = $oInformationsystem->watermark_default_position_y;
					$param['large_image_preserve_aspect_ratio'] = $oInformationsystem->preserve_aspect_ratio;
					$param['small_image_max_width'] = $oInformationsystem->image_small_max_width;
					$param['small_image_max_height'] = $oInformationsystem->image_small_max_height;
					$param['small_image_watermark'] = $oInformationsystem->watermark_default_use_small_image;
					$param['small_image_preserve_aspect_ratio'] = $oInformationsystem->preserve_aspect_ratio_small;
					$param['large_image_max_width'] = $oInformationsystem->image_large_max_width;
					$param['large_image_max_height'] = $oInformationsystem->image_large_max_height;
					$param['large_image_watermark'] = $oInformationsystem->watermark_default_use_large_image;

					$oInformationsystem_Item->createDir();

					$result = Core_File::adminUpload($param);

					if ($result['large_image'])
					{
						$oInformationsystem_Item->image_large = $large_image;
						$oInformationsystem_Item->setLargeImageSizes();
					}

					if ($result['small_image'])
					{
						$oInformationsystem_Item->image_small = $small_image;
						$oInformationsystem_Item->setSmallImageSizes();
					}

					$oInformationsystem_Item->save();

					Core_File::delete($temp_file);

					$iImported++;
				}
			}
		}
	}
}

printf('Imported %d items', $iImported);