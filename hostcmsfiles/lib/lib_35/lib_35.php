<?php

if (!Core::moduleIsActive('siteuser'))
{
	?><h1>Пользователи сайта</h1>
	<p>Функционал недоступен, приобретите более старшую редакцию.</p>
	<p>Модуль &laquo;<a href="http://www.hostcms.ru/hostcms/modules/users/">Пользователи сайта</a>&raquo; доступен в редакциях &laquo;<a href="http://www.hostcms.ru/hostcms/editions/corporation/">Корпорация</a>&raquo; и &laquo;<a href="http://www.hostcms.ru/hostcms/editions/business/">Бизнес</a>&raquo;.</p>
	<?php
	return;
}

$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

if (is_null($oSiteuser))
{
	?><h1>Вы не авторизованы!</h1>
	<p>Для просмотра заказов необходимо авторизироваться.</p>
	<?php
	return;
}

if (!Core::moduleIsActive('shop'))
{
	?><h1>Мои объявления</h1>
	<p>Список объявлении временно недоступен.</p>
	<?php
	return;
}

$Shop_Controller_Show = Core_Page::instance()->object;

if (isset($Shop_Controller_Show->patternParams['delete']))
{
	$sPath = Core_Entity::factory('Structure', CURRENT_STRUCTURE_ID)->getPath();

	?><h1>Объявление успешно удалено</h1>
	<p>Если Ваш браузер поддерживает автоматическое перенаправление через 3 секунды Вы перейдете к <a href="<?php echo $sPath ?>">списку объявлений</a>.</p>
	<script type="text/javascript">setTimeout(function(){ location = '<?php echo $sPath ?>' }, 3000);</script>
	<?php
	return;
}

$xslName = $Shop_Controller_Show->item
	? Core_Array::get(Core_Page::instance()->libParams, 'shopItemXsl')
	: Core_Array::get(Core_Page::instance()->libParams, 'shopXsl');

$Shop_Controller_Show->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('ТекущаяГруппа')->value($Shop_Controller_Show->group)
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('show_comments')->value(Core_Array::get(Core_Page::instance()->libParams, 'showComments', 1))
)->addEntity(
	Core::factory('Core_Xml_Entity')
		->name('show_add_comments')->value(Core_Array::get(Core_Page::instance()->libParams, 'showAddComment', 2))
)->addEntity(Core_Page::instance()->structure);

$Shop_Controller_Show->groupsMode('none');

if ($Shop_Controller_Show->item != 0)
{
	// Редактирование объявления
	if (Core_Array::getPost('update'))
	{
		$oShop = $Shop_Controller_Show->getEntity();
		$oShop_Item = Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item);

		$oShop_Item->name = Core_Str::stripTags(Core_Array::getPost('name'));
		$oShop_Item->text = Core_Str::stripTags(Core_Array::getPost('text'));
		$oShop_Item->price = floatval(Core_Array::getPost('price', 0));
		$oShop_Item->datetime = Core_Date::timestamp2sql(time());

		$aFileData = Core_Array::getFiles('image', array());

		// New values of property
		if (isset($aFileData['name']) && Core_File::isValidExtension($aFileData['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
		{
			$oShop_Item->deleteLargeImage()->deleteSmallImage();

			try
			{
				$sLargeImageFile = 'shop_items_catalog_image' . $oShop_Item->id . '.' . Core_File::getExtension($aFileData['name']);

				Core_Image::instance()->resizeImage($aFileData['tmp_name'], $oShop->image_large_max_width, $oShop->image_large_max_height, $oShop_Item->getItemPath() . $sLargeImageFile);
				$oShop_Item->image_large = $sLargeImageFile;
				$oShop_Item->setLargeImageSizes();

				Core_Image::instance()->resizeImage($aFileData['tmp_name'], $oShop->image_small_max_width, $oShop->image_small_max_height, $oShop_Item->getItemPath() . 'small_'.$sLargeImageFile);
				$oShop_Item->image_small = 'small_'.$sLargeImageFile;
				$oShop_Item->setSmallImageSizes();
			}
			catch (Exception $e) {};
		}

		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);
		$aProperties = $oShop_Item_Property_List->getPropertiesForGroup($Shop_Controller_Show->group);

		foreach ($aProperties as $oProperty)
		{
			// If the field is not hidden
			if ($oProperty->type != 10)
			{
				$aProperty_Values = $oProperty->getValues($oShop_Item->id, FALSE);
				$oProperty_Value = isset($aProperty_Values[0])
					? $aProperty_Values[0]
					: $oProperty->createNewValue($oShop_Item->id);

				$value = Core_Array::getPost("property_{$oProperty->id}");

				// Isset value or checkbox
				if (!is_null($value) || $oProperty->type == 7)
				{
					// Дополнительные свойства
					switch ($oProperty->type)
					{
						case 0: // Int
						case 3: // List
						case 5: // Information system
							$oProperty_Value->value(intval($value));
							$oProperty_Value->save();
						break;
						case 1: // String
						case 4: // Textarea
						case 6: // Wysiwyg
							$oProperty_Value->value(Core_Str::stripTags(strval($value)));
							$oProperty_Value->save();
						break;
						case 7: // Checkbox
							$oProperty_Value->value(is_null($value) ? 0 : 1);
							$oProperty_Value->save();
						break;
						case 11: // Float
							$oProperty_Value->value(floatval(str_replace(',', '.', $value)));
							$oProperty_Value->save();
						break;
						case 8: // Date
							$date = Core_Date::date2sql(strval($value));
							$oProperty_Value->value($date);
							$oProperty_Value->save();
						break;
						case 9: // Datetime
							$datetime = Core_Date::datetime2sql(strval($value));
							$oProperty_Value->value($datetime);
							$oProperty_Value->save();
						break;
					}
				}
				// File
				elseif ($oProperty->type == 2)
				{
					$aFileData = Core_Array::getFiles("property_{$oProperty->id}", array());

					foreach ($aProperty_Values as $oProperty_Value)
					{
						$aFilePropertyValue = Core_Array::getFiles("property_value_{$oProperty_Value->id}", array());

						if (isset($aFilePropertyValue['name']) && Core_File::isValidExtension($aFilePropertyValue['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
						{
							foreach ($aFileData as $key => $dataValue)
							{
								$aFileData[$key][] = $aFilePropertyValue[$key];
							}

							$oProperty_Value->setDir($oShop_Item->getItemPath());
							$oProperty_Value->delete();
						}
					}

					// New values of property
					if (isset($aFileData['name']))
					{
						foreach ($aFileData['name'] as $key => $sFileName)
						{
							$oFileValue = $oProperty->createNewValue($oShop_Item->id);

							if (Core_File::isValidExtension($sFileName, array('JPG', 'JPEG', 'GIF', 'PNG')))
							{
								$oFileValue->file_name = Core_Str::stripTags($sFileName);
								$oFileValue->file_small_name = Core_Str::stripTags($sFileName);
								$oFileValue->save();

								try
								{
									$oShop_Item_Property_List->createPropertyDir($oShop_Item);

									Core_Image::instance()->resizeImage($aFileData['tmp_name'][$key], $oShop->image_large_max_width, $oShop->image_large_max_height, $oShop_Item->getItemPath() . $oShop_Item_Property_List->getLargeFileName($oShop_Item, $oFileValue, $sFileName));
									$oFileValue->file = $oShop_Item_Property_List->getLargeFileName($oShop_Item, $oFileValue, $sFileName);

									Core_Image::instance()->resizeImage($aFileData['tmp_name'][$key], $oShop->image_small_max_width, $oShop->image_small_max_height, $oShop_Item->getItemPath() . $oShop_Item_Property_List->getSmallFileName($oShop_Item, $oFileValue, $sFileName));
									$oFileValue->file_small = $oShop_Item_Property_List->getSmallFileName($oShop_Item, $oFileValue, $sFileName);

									$oFileValue->save();
								}
								catch (Exception $e) {};
							}
						}
					}
				}
			}
		}

		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('messages')
				->addEntity(Core::factory('Core_Xml_Entity')
					->name('message')
					->value('Объявление успешно отредактировано.')
			)
		);
	}
}

// В корне выводим из всех групп
/* if ($Shop_Controller_Show->group == 0)
{
	$Shop_Controller_Show->group(FALSE);
} */

$Shop_Controller_Show
	->cache(FALSE)
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->itemsProperties(TRUE)
	->show();