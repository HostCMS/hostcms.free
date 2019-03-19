<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Modification_Create_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 * @hostcms-event Shop_Item_Modification_Create_Controller.onBeforeNewModificationSave
	 * @hostcms-event Shop_Item_Modification_Create_Controller.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		$oShopItemParent = Core_Entity::factory('Shop_Item', intval(Core_Array::getPost('shop_item_id', 0)));

		$oShop = $oShopItemParent->Shop;

		$iIndex = 0;

		$aPropertiesId = array();
		$aData = array();
		foreach ($_POST as $key => $sPostData)
		{
			if (strpos($key, 'property_') === 0)
			{
				$iPropertyID = explode('_', $key);

				$oProperty = Core_Entity::factory('Property')->find($iPropertyID[1]);

				$aPropertiesId[] = $iPropertyID[1];

				//$aList_Items = $oProperty->List->List_Items->getAllByActive(1);

				if (isset($_POST["property{$oProperty->id}list"]))
				{
					foreach ($_POST["property{$oProperty->id}list"] as $value)
					{
						$aData[$iIndex][] = array(
							'property' => $oProperty,
							'list_item' => Core_Entity::factory('List_Item', $value)
						);
					}
				}

				$iIndex++;
			}
		}

		$sName = Core_Array::getPost('name');

		$aResult = Core_Array::combine($aData);

		$iCount = 1;

		foreach ($aResult as $aTmpResult)
		{
			$sTmpName = $sName;
			foreach ($aTmpResult as $aTmpList)
			{
				$sTmpName = str_replace("{P{$aTmpList['property']->id}}", $aTmpList['list_item']->value, $sTmpName);
			}

			// Вставка модификации
			$oShopItem = Core_Entity::factory('Shop_Item');

			$oShopItem->name = $sTmpName;
			$oShopItem->modification_id = $oShopItemParent->id;
			$oShopItem->shop_group_id = 0;
			$oShopItem->price = Core_Array::getPost('price');
			$oShopItem->shop_currency_id = Core_Array::getPost('currency');
			$oShopItem->shop_measure_id = Core_Array::getPost('measure');
			$oShopItem->marking = str_replace("{N}", $iCount++, Core_Array::getPost('marking'));
			$oShopItem->shop_id = $oShopItemParent->shop_id;

			Core_Event::notify('Shop_Item_Modification_Create_Controller.onBeforeNewModificationSave', $this, array($oShopItem, $oShopItemParent));

			$oShopItem->save();

			// Копировать основные свойства
			if (!is_null(Core_Array::getPost('copy_main_properties')))
			{
				$oShopItem->datetime = $oShopItemParent->datetime;
				$oShopItem->start_datetime = $oShopItemParent->start_datetime;
				$oShopItem->end_datetime = $oShopItemParent->end_datetime;
				$oShopItem->shop_seller_id = $oShopItemParent->shop_seller_id;
				$oShopItem->shop_producer_id = $oShopItemParent->shop_producer_id;
				$oShopItem->shop_tax_id = $oShopItemParent->shop_tax_id;
				$oShopItem->description = $oShopItemParent->description;
				$oShopItem->text = $oShopItemParent->text;
				$oShopItem->image_large = $oShopItemParent->image_large;
				$oShopItem->image_small = $oShopItemParent->image_small;
				$oShopItem->length = $oShopItemParent->length;
				$oShopItem->width = $oShopItemParent->width;
				$oShopItem->height = $oShopItemParent->height;
				$oShopItem->weight = $oShopItemParent->weight;
				$oShopItem->type = $oShopItemParent->type;
				$oShopItem->min_quantity = $oShopItemParent->min_quantity;
				$oShopItem->max_quantity = $oShopItemParent->max_quantity;
				$oShopItem->quantity_step = $oShopItemParent->quantity_step;

				try
				{
					Core_File::copy($oShopItemParent->getLargeFilePath(), $oShopItem->getLargeFilePath());
				}
				catch (Exception $e) {}

				try
				{
					Core_File::copy($oShopItemParent->getSmallFilePath(), $oShopItem->getSmallFilePath());
				}
				catch (Exception $e) {}
			}

			// Копировать SEO-поля
			if (!is_null(Core_Array::getPost('copy_seo')))
			{
				$oShopItem->seo_title = $oShopItemParent->seo_title;
				$oShopItem->seo_description = $oShopItemParent->seo_description;
				$oShopItem->seo_keywords = $oShopItemParent->seo_keywords;
			}

			// Копировать поля вкладки "Экспорт/Импорт"
			if (!is_null(Core_Array::getPost('copy_export_import')))
			{
				$oShopItem->yandex_market = $oShopItemParent->yandex_market;
				$oShopItem->vendorcode = $oShopItemParent->vendorcode;
				$oShopItem->yandex_market_bid = $oShopItemParent->yandex_market_bid;
				$oShopItem->yandex_market_cid = $oShopItemParent->yandex_market_cid;
				$oShopItem->yandex_market_sales_notes = $oShopItemParent->yandex_market_sales_notes;
			}

			// Копировать дополнительные цены для товара
			if (!is_null(Core_Array::getPost('copy_prices_to_item')))
			{
				$aShop_Item_Prices = $oShopItemParent->Shop_Item_Prices->findAll();

				foreach ($aShop_Item_Prices as $oShop_Item_Price)
				{
					$oShop_Item_Price_Copy = clone $oShop_Item_Price;
					$oShop_Item_Price_Copy->shop_item_id = $oShopItem->id;
					$oShop_Item_Price_Copy->save();
				}
			}

			// Копировать специальные цены
			if (!is_null(Core_Array::getPost('copy_specials_prices_to_item')))
			{
				$aShop_Specialprices = $oShopItemParent->Shop_Specialprices->findAll();

				foreach ($aShop_Specialprices as $oShop_Specialprice)
				{
					$oShop_Specialprice_Copy = clone $oShop_Specialprice;
					$oShop_Specialprice_Copy->shop_item_id = $oShopItem->id;
					$oShop_Specialprice_Copy->save();
				}
			}

			// Копировать сопутствующие товары
			if (!is_null(Core_Array::getPost('copy_tying_products')))
			{
					$aShop_Item_Associated = $oShopItemParent->Shop_Item_Associateds->findAll();

					foreach ($aShop_Item_Associated as $oShop_Item_Associated)
					{
						$oShop_Item_Associated_Copy = clone $oShop_Item_Associated;
						$oShop_Item_Associated_Copy->shop_item_id = $oShopItem->id;
						$oShop_Item_Associated_Copy->save();
					}
			}

			// Копировать дополнительные свойства
			if (!is_null(Core_Array::getPost('copy_external_property')))
			{
				$aPropertyValues = $oShopItemParent->getPropertyValues();
				foreach ($aPropertyValues as $oPropertyValue)
				{
					// Не копируем св-во, по которому создается модификация
					if (!in_array($oPropertyValue->property_id, $aPropertiesId))
					{
						$oNewPropertyValue = clone $oPropertyValue;
						$oNewPropertyValue->entity_id = $oShopItem->id;
						$oNewPropertyValue->save();

						if ($oNewPropertyValue->Property->type == 2)
						{
							try
							{
								Core_File::copy($oShopItemParent->getItemPath() . $oPropertyValue->file, $oShopItem->getItemPath() . $oPropertyValue->file);
							}
							catch (Exception $e) {}

							try
							{
								Core_File::copy($oShopItemParent->getItemPath() . $oPropertyValue->file_small, $oShopItem->getItemPath() . $oPropertyValue->file_small);
							}
							catch (Exception $e) {}
						}
					}
				}
			}

			// Копировать метки
			if (!is_null(Core_Array::getPost('copy_tags')))
			{
				$aTags = $oShopItemParent->Tags->findAll(FALSE);
				foreach ($aTags as $oTag)
				{
					$oShopItem->add($oTag);
				}
			}

			// Копировать количество на складе
			if (!is_null(Core_Array::getPost('copy_warehouse_count')))
			{
				$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse')->getDefault();
				if (!is_null($oShop_Warehouse))
				{
					$oShop_Warehouse_Item = Core_Entity::factory('Shop_Warehouse_Item');
					$oShop_Warehouse_Item
						->shop_warehouse_id($oShop_Warehouse->id)
						->shop_item_id($oShopItem->id)
						->count(floatval(Core_Array::getPost('count')))
						->save();
				}
			}

			// Значения св-в для создаваемых модификаций
			foreach ($aTmpResult as $aTmpList)
			{
				$oPropertyValue = $aTmpList['property']->createNewValue($oShopItem->id);
				$oPropertyValue->value($aTmpList['list_item']->id);
				$oPropertyValue->save();
			}

			$oShopItem->save();
		}

		Core_Event::notify('Shop_Item_Modification_Create_Controller.onAfterExecute', $this, array($oShopItemParent));

		return $this;
	}
}