<?php

$oInformationsystem = Core_Entity::factory('Informationsystem',
	Core_Array::get(Core_Page::instance()->libParams, 'informationsystemId')
);

// Группа, в которую помещается новый элемент
$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group')->find(
	Core_Array::get(Core_Page::instance()->libParams, 'informationGroupId')
);

$aSourcesList = explode("\n",
	trim(Core_Array::get(Core_Page::instance()->libParams, 'sourcesList'))
);

$iImported = 0;

$oCore_Rss_Read = new Core_Rss_Read();

foreach ($aSourcesList as $url)
{
	try
	{
		$aRssData = $oCore_Rss_Read
			->clear()
			->loadUrl($url)
			->parse();

		foreach ($aRssData['items'] as $itemKey => $aItem)
		{
			$oSameItem = $oInformationsystem->Informationsystem_Items->getByName($aItem['title'], FALSE);

			/* Если не найдено элементов с таким же именем */
			if (is_null($oSameItem))
			{
				$oInformationsystem_Item = Core_Entity::factory('Informationsystem_Item');
				$oInformationsystem_Item->name = $aItem['title'];
				$oInformationsystem_Item->path = '';
				$oInformationsystem_Item->description = $aItem['description'];
				$oInformationsystem_Item->text = Core_Array::get($aItem, 'yandex:full-text', $aItem['description'])
					. "<p>Источник: <a href=\"{$aItem['link']}\">{$aItem['link']}</a>";
				$oInformationsystem_Item->datetime = Core_Date::timestamp2sql(strtotime($aItem['pubdate']));

				$oInformationsystem_Item->informationsystem_group_id = !is_null($oInformationsystem_Group->id)
					? $oInformationsystem_Group->id : 0;

				if (strlen($oInformationsystem_Item->name))
				{
					// Save informationsystem item
					$oInformationsystem->add($oInformationsystem_Item);

					if (isset($aItem['enclosure']['url']))
					{
						// Если ссылка не начинается с http://
						strpos($aItem['enclosure']['url'], 'http://') !== 0 && $aItem['enclosure']['url'] = 'http://' . $aItem['enclosure']['url'];
					
						$Core_Http = Core_Http::instance()
							->url($aItem['enclosure']['url'])
							->port(80)
							->timeout(5)
							->execute();

						// Определяем расширение файла
						$ext = Core_File::getExtension($aItem['enclosure']['url']);
						
						$temp_file = tempnam(TMP_DIR, "rss") . '.' . $ext;
						Core_File::write($temp_file, $Core_Http->getBody());

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
						$param['small_image_max_width'] = $oInformationsystem->group_image_small_max_width;
						$param['small_image_max_height'] = $oInformationsystem->group_image_small_max_height;
						$param['small_image_watermark'] = $oInformationsystem->watermark_default_use_small_image;
						$param['small_image_preserve_aspect_ratio'] = $oInformationsystem->preserve_aspect_ratio_small;
						$param['large_image_max_width'] = $oInformationsystem->group_image_large_max_width;
						$param['large_image_max_height'] = $oInformationsystem->group_image_large_max_height;
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
	catch (Exception $e) {
		Core_Message::show('<p>Error while reading url ' . htmlspecialchars($url) . ', ' . $e->getMessage(), 'error');
	};
}
?>
<h1>Импорт информационных элементов</h1>
<p>Проимпортировано <?php echo $iImported?> элементов</p>