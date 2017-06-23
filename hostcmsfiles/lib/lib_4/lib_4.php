<?php

$Shop_Controller_Show = Core_Page::instance()->object;

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
);

$Shop_Controller_Show
	->tags(TRUE)
	->comments(TRUE)
	->associatedItems(TRUE)
	->modifications(TRUE);

$siteuser_id = 0;
if (Core::moduleIsActive('siteuser'))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

	if ($oSiteuser)
	{
		$siteuser_id = $oSiteuser->id;
	}
}

if ($Shop_Controller_Show->item == 0)
{
	$Shop_Controller_Show->itemsForbiddenTags(array('text'));

	$oShop = $Shop_Controller_Show->getEntity();

	if (Core_Array::getGet('filter') || Core_Array::getGet('sorting'))
	{
		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('filter')->value(1)
		);

		$sorting = intval(Core_Array::getGet('sorting'));
		$sorting && $Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('sorting')->value($sorting)
		);

		// Prices
		$price_from = intval(Core_Array::getGet('price_from'));
		$price_to = intval(Core_Array::getGet('price_to'));
		if ($price_from || $price_to || $sorting == 1 || $sorting == 2)
		{
			// Получаем список валют магазина
			$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll();

			$query_currency_switch = 'price';
			foreach ($aShop_Currencies as $oShop_Currency)
			{
				// Получаем коэффициент пересчета для каждой валюты
				$currency_coefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					$oShop_Currency, $oShop->Shop_Currency
				);

				$query_currency_switch = "IF (`shop_items`.`shop_currency_id` = '{$oShop_Currency->id}', IF (shop_discounts.value, IF(shop_discounts.type, price * {$currency_coefficient} - shop_discounts.value, price * (100 - shop_discounts.value) * {$currency_coefficient} / 100), shop_items.price * {$currency_coefficient}), {$query_currency_switch})";
			}

			$current_date = date('Y-m-d H:i:s');
			$Shop_Controller_Show->shopItems()
				->queryBuilder()
				->select(array(Core_QueryBuilder::expression($query_currency_switch), 'absolute_price'))
				->leftJoin('shop_item_discounts', 'shop_items.id', '=', 'shop_item_discounts.shop_item_id')
				->leftJoin('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id', array(
					array('AND ' => array('shop_discounts.active', '=', 1)),
					array('AND ' => array('shop_discounts.deleted', '=', 0)),
					array('AND (' => array('shop_discounts.end_datetime', '>=', $current_date)),
					array('OR' => array('shop_discounts.end_datetime', '=', '0000-00-00 00:00:00')),
					array('AND' => array('shop_discounts.start_datetime', '<=', $current_date)),
					array(')' => NULL)
				))
				->groupBy('shop_items.id');

			if ($price_from)
			{
				$Shop_Controller_Show->shopItems()->queryBuilder()->having('absolute_price', '>=', $price_from) && $Shop_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('price_from')->value($price_from)
				);
			}
			if ($price_to)
			{
				$Shop_Controller_Show->shopItems()->queryBuilder()->having('absolute_price', '<=', $price_to);
				$Shop_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('price_to')->value($price_to)
				);
			}

			$Shop_Controller_Show->shopItems()->queryBuilder()
				->clearOrderBy()
				->orderBy('absolute_price', $sorting == 1 ? 'ASC' : 'DESC');
		}

		$sorting == 3 && $Shop_Controller_Show->shopItems()->queryBuilder()
			->clearOrderBy()
			->orderBy('shop_items.name', 'ASC');

		// Additional properties
		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

		$aProperties = $Shop_Controller_Show->group !== FALSE
			? $oShop_Item_Property_List->getPropertiesForGroup($Shop_Controller_Show->group)
			: $oShop_Item_Property_List->Properties->findAll();

		$aTmpProperties = array();
		$havingCount = 0;
		foreach ($aProperties as $oProperty)
		{
			// Св-во может иметь несколько значений
			$aPropertiesValue = Core_Array::getGet('property_' . $oProperty->id);
			if ($aPropertiesValue)
			{
				!is_array($aPropertiesValue) && $aPropertiesValue = array($aPropertiesValue);
				foreach ($aPropertiesValue as $sPropertyValue)
				{
					$aTmpProperties[] = array($oProperty, strval($sPropertyValue));
				}
				$havingCount++;
			}
			elseif (!is_null(Core_Array::getGet('property_' . $oProperty->id . '_from')))
			{
				$tmpFrom = Core_Array::getGet('property_' . $oProperty->id . '_from');
				$tmpTo = Core_Array::getGet('property_' . $oProperty->id . '_to');

				!is_array($tmpFrom) && $tmpFrom = array($tmpFrom);
				!is_array($tmpTo) && $tmpTo = array($tmpTo);

				// From ... to ...
				foreach ($tmpFrom as $iKey => $sValue)
				{
					$aTmpProperties[] = array($oProperty, array(
						'from' => $oProperty->type == 11 ? floatval($sValue) : intval($sValue),
						'to' => $oProperty->type == 11 ? floatval(Core_Array::get($tmpTo, $iKey)) : intval(Core_Array::get($tmpTo, $iKey))
					));
				}
				$havingCount++;
			}
		}

		if (count($aTmpProperties))
		{
			$aTableNames = array();

			$Shop_Controller_Show->shopItems()->queryBuilder()
				->leftJoin('shop_item_properties', 'shop_items.shop_id', '=', 'shop_item_properties.shop_id')
				->setAnd()
				->open();

			reset($aTmpProperties);
			while(list(, list($oProperty, $propertyValue)) = each($aTmpProperties))
			{
				$tableName = $oProperty->createNewValue(0)->getTableName();

				!in_array($tableName, $aTableNames) && $aTableNames[] = $tableName;

				$Shop_Controller_Show->shopItems()->queryBuilder()
					->where('shop_item_properties.property_id', '=', $oProperty->id);

				if (!is_array($propertyValue))
				{
					$Shop_Controller_Show->shopItems()->queryBuilder()
						->where($tableName . '.value', '=', $propertyValue)
						->setOr();

					$Shop_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('property_' . $oProperty->id)->value($propertyValue)
					);
				}
				else
				{
					$from = trim(strval(Core_Array::get($propertyValue, 'from')));
					$from && $Shop_Controller_Show->shopItems()->queryBuilder()
						->where($tableName . '.value', '>=', $from)
						->setAnd();

					$to = trim(strval(Core_Array::get($propertyValue, 'to')));
					$to && $Shop_Controller_Show->shopItems()->queryBuilder()
						->where($tableName . '.value', '<=', $to);

					$Shop_Controller_Show->shopItems()->queryBuilder()
						->setOr();

					$Shop_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('property_' . $oProperty->id . '_from')->value($from)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('property_' . $oProperty->id . '_to')->value($to)
					);
				}
			}

			$Shop_Controller_Show->shopItems()->queryBuilder()
				->close()
				->groupBy('shop_items.id');
				
			$havingCount > 1
				&& $Shop_Controller_Show->shopItems()->queryBuilder()
						->having('COUNT(shop_item_properties.id)', '=', $havingCount);

			foreach ($aTableNames as $tableName)
			{
				$Shop_Controller_Show->shopItems()->queryBuilder()
					->leftJoin($tableName, 'shop_items.id', '=', $tableName . '.entity_id',
						array(
							array('AND' => array('shop_item_properties.property_id', '=', Core_QueryBuilder::expression($tableName . '.property_id')))
						)
					);
			}
		}
	}

	// Добавление объявления
	if ($Shop_Controller_Show->group && Core_Array::getPost('send_ad'))
	{
		$oShop_Item = Core_Entity::factory('Shop_Item');

		$oShop_Item->active = Core_Array::get(Core_Page::instance()->libParams, 'addedItemActive', 0);
		$oShop_Item->name = Core_Str::stripTags(strval(Core_Array::getPost('name')));
		$oShop_Item->text = nl2br(Core_Str::stripTags(strval(Core_Array::getPost('text'))));
		$oShop_Item->price = floatval(Core_Array::getPost('price', 0));
		$oShop_Item->path = '';

		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);
		$aProperties = $oShop_Item_Property_List->getPropertiesForGroup($Shop_Controller_Show->group);

		if ($oShop->use_captcha == 0 || $siteuser_id > 0 || Core_Captcha::valid(Core_Array::getPost('captcha_id'), Core_Array::getPost('captcha')))
		{
			$oShop_Item->shop_id = $oShop->id;
			$oShop_Item->shop_currency_id = $oShop->shop_currency_id;
			$oShop_Item->siteuser_id = $siteuser_id;

			Core_Entity::factory('Shop_Group', $Shop_Controller_Show->group)
				->add($oShop_Item);

			$oShop_Item->createDir();

			$aFileData = Core_Array::getFiles("image", array());

			// New values of property
			if (is_array($aFileData) && isset($aFileData['name']))
			{
				if (Core_File::isValidExtension($aFileData['name'], array('JPG', 'JPEG', 'GIF', 'PNG')))
				{
					try
					{
						$sLargeImageFile = 'shop_items_catalog_image' . $oShop_Item->id . '.' . Core_File::getExtension($aFileData['name']);

						Core_Image::instance()->resizeImage($aFileData['tmp_name'], $oShop->image_large_max_width, $oShop->image_large_max_height, $oShop_Item->getItemPath() . $sLargeImageFile, NULL, $oShop->preserve_aspect_ratio);
						$oShop_Item->image_large = $sLargeImageFile;
						$oShop_Item->setLargeImageSizes();

						Core_Image::instance()->resizeImage($aFileData['tmp_name'], $oShop->image_small_max_width, $oShop->image_small_max_height, $oShop_Item->getItemPath() . 'small_'.$sLargeImageFile,  NULL, $oShop->preserve_aspect_ratio_small);
						$oShop_Item->image_small = 'small_'.$sLargeImageFile;
						$oShop_Item->setSmallImageSizes();
					}
					catch (Exception $e) {};
				}
			}

			foreach ($aProperties as $oProperty)
			{
				// Поле не скрытое
				if ($oProperty->type != 10)
				{
					$oProperty_Value = $oProperty->createNewValue($oShop_Item->id);

					// Дополнительные свойства
					switch ($oProperty->type)
					{
						case 0: // Int
						case 3: // List
						case 5: // Information system
							$oProperty_Value->value(intval(Core_Array::getPost("property_{$oProperty->id}")));
							$oProperty_Value->save();
						break;
						case 1: // String
						case 4: // Textarea
						case 6: // Wysiwyg
							$oProperty_Value->value(Core_Str::stripTags(
								strval(Core_Array::getPost("property_{$oProperty->id}"))
							));
							$oProperty_Value->save();
						break;
						case 8: // Date
							$date = strval(Core_Array::getPost("property_{$oProperty->id}"));
							$date = Core_Date::date2sql($date);
							$oProperty_Value->value($date);
							$oProperty_Value->save();
						break;
						case 9: // Datetime
							$datetime = strval(Core_Array::getPost("property_{$oProperty->id}"));
							$datetime = Core_Date::datetime2sql($datetime);
							$oProperty_Value->value($datetime);
							$oProperty_Value->save();
						break;
						case 2: // File
							$aFileData = Core_Array::getFiles("property_{$oProperty->id}", array());

							// New values of property
							if (is_array($aFileData) && isset($aFileData['name']))
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

											Core_Image::instance()->resizeImage($aFileData['tmp_name'][$key], $oShop->image_large_max_width, $oShop->image_large_max_height, $oShop_Item_Property_List->getDirPath($oShop_Item) . $oShop_Item_Property_List->getLargeFileName($oShop_Item, $oFileValue, $sFileName));

											$oFileValue->file = $oShop_Item_Property_List->getLargeFileName($oShop_Item, $oFileValue, $sFileName);

											Core_Image::instance()->resizeImage($aFileData['tmp_name'][$key], $oShop->image_small_max_width, $oShop->image_small_max_height, $oShop_Item_Property_List->getDirPath($oShop_Item) . $oShop_Item_Property_List->getSmallFileName($oShop_Item, $oFileValue, $sFileName));

											$oFileValue->file_small = $oShop_Item_Property_List->getSmallFileName($oShop_Item, $oFileValue, $sFileName);

											$oFileValue->save();
 										}
										catch (Exception $e) {};
									}
								}
							}
						break;
						case 7: // Checkbox
							$oProperty_Value->value(is_null(Core_Array::getPost("property_{$oProperty->id}")) ? 0 : 1);
							$oProperty_Value->save();
						break;
					}
				}
			}

			$Shop_Controller_Show->addEntity(Core::factory('Core_Xml_Entity')
				->name('messages')->addEntity(Core::factory('Core_Xml_Entity')
					->name('message')->value('Объявление успешно добавлено.')
				));
		}
		else
		{
			$oAddItem = Core::factory('Core_Xml_Entity')
				->name('add_item');

			$oAddItem->addEntity(Core::factory('Core_Xml_Entity')
					->name('name')
					->value($oShop_Item->name))
				->addEntity(Core::factory('Core_Xml_Entity')
					->name('text')
					->value($oShop_Item->text))
				->addEntity(Core::factory('Core_Xml_Entity')
					->name('price')
					->value($oShop_Item->price));

            foreach ($aProperties as $oProperty)
            {
				$val = Core_Array::getPost("property_{$oProperty->id}");
				
				if (!is_array($val))
				{
					$oAddItem->addEntity(Core::factory('Core_Xml_Entity')
						->name('property')
						->addEntity(Core::factory('Core_Xml_Entity')
							->name('id')
							->value($oProperty->id))
						->addEntity(Core::factory('Core_Xml_Entity')
							->name('value')
							->value($oProperty->type == 0
								? intval($val)
								: strval($val)
							)
						));
				}
            }

			$Shop_Controller_Show->addEntity(Core::factory('Core_Xml_Entity')
				->name('errors')->addEntity(Core::factory('Core_Xml_Entity')
					->name('error')->value('Вы неверно ввели число подтверждения отправки формы!')
				));

			$Shop_Controller_Show->addEntity($oAddItem);
		}
	}
}

$Shop_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	->itemsProperties(TRUE)
	->show();