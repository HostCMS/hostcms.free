<?php

if (Core::moduleIsActive('siteuser'))
{
	$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

	$Shop_Controller_Show = new Shop_Controller_Show($oShop);

	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

	if (is_null($oSiteuser))
	{
		return;
	}

	$Shop_Controller_Show->pattern = Core_Page::instance()->structure->getPath() . '({path})(page-{page}/)({item}/)({delete}/)';

	$Shop_Controller_Show
		->limit($oShop->items_on_page)
		->parseUrl();

	if (isset($Shop_Controller_Show->patternParams['item']))
	{
		$oShop_Item = $oShop->Shop_Items->getById($Shop_Controller_Show->patternParams['item']);
		if ($oShop_Item)
		{
			if ($oSiteuser->id == $oShop_Item->siteuser_id)
			{
				$Shop_Controller_Show->item = $oShop_Item->id;
				$Shop_Controller_Show->group = intval(Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item)->shop_group_id);

				// Удаление объявления
				if (isset($Shop_Controller_Show->patternParams['delete']))
				{
					$oShop_Item->markDeleted();
					$Shop_Controller_Show->item = NULL;
				}
			}
			else
			{
				$Shop_Controller_Show->error403();
				return;
			}
		}
		else
		{
			$Shop_Controller_Show->error404();
			return;
		}
	}
	else
	{
		$Shop_Controller_Show->group = FALSE;
	}

	$Shop_Controller_Show
		->shopItems()
		->queryBuilder()
		->where('shop_items.siteuser_id', '=', $oSiteuser->id);

	// Удаление фотографии
	if ($Shop_Controller_Show->item && !is_null(Core_Array::getGet('photo')))
	{
		$oShop_Item = Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item);

		$iPhoto = intval(Core_Array::getGet('photo'));

		if ($oShop_Item->siteuser_id == $oSiteuser->id)
		{
			if ($iPhoto)
			{
				$aPropertyValues = $oShop_Item->getPropertyValues();

				foreach ($aPropertyValues as $oPropertyValue)
				{
					if ($oPropertyValue->Property->type == 2 && $oPropertyValue->id == $iPhoto)
					{
						$oPropertyValue->setDir($oShop_Item->getItemPath());
						$oPropertyValue->delete();

						$bDeleted = TRUE;
					}
				}
			}
			elseif($oShop_Item->image_large)
			{
				$oShop_Item->deleteLargeImage()->deleteSmallImage();
				$bDeleted = TRUE;
			}

			if (isset($bDeleted))
			{
				$Shop_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
					->name('messages')
					->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('message')->value('Изображение успешно удалено.')
					)
				);

				if (!is_null(Core_Array::getGet('_')))
				{
					echo json_encode(array('delete' => TRUE));
					exit();
				}
			}
		}
	}

	// Текстовая информация для указания номера страницы, например "страница"
	$pageName = Core_Array::get(Core_Page::instance()->libParams, 'page')
		? Core_Array::get(Core_Page::instance()->libParams, 'page')
		: 'страница';

	// Разделитель в заголовке страницы
	$pageSeparator = Core_Array::get(Core_Page::instance()->libParams, 'separator')
		? Core_Page::instance()->libParams['separator']
		: ' / ';

	$aTitle = array($oShop->name);
	$aDescription = array($oShop->name);
	$aKeywords = array($oShop->name);

	if ($Shop_Controller_Show->group)
	{
		$oShop_Group = Core_Entity::factory('Shop_Group', $Shop_Controller_Show->group);

		do {
			$aTitle[] = $oShop_Group->seo_title != ''
				? $oShop_Group->seo_title
				: $oShop_Group->name;

			$aDescription[] = $oShop_Group->seo_description != ''
				? $oShop_Group->seo_description
				: $oShop_Group->name;

			$aKeywords[] = $oShop_Group->seo_keywords != ''
				? $oShop_Group->seo_keywords
				: $oShop_Group->name;

		} while($oShop_Group = $oShop_Group->getParent());
	}

	if ($Shop_Controller_Show->item)
	{
		$oShop_Item = Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item);

		$aTitle[] = $oShop_Item->seo_title != ''
			? $oShop_Item->seo_title
			: $oShop_Item->name;

		$aDescription[] = $oShop_Item->seo_description != ''
			? $oShop_Item->seo_description
			: $oShop_Item->name;

		$aKeywords[] = $oShop_Item->seo_keywords != ''
			? $oShop_Item->seo_keywords
			: $oShop_Item->name;
	}

	if ($Shop_Controller_Show->producer)
	{
		$oShop_Producer = Core_Entity::factory('Shop_Producer', $Shop_Controller_Show->producer);
		$aKeywords[] = $aDescription[] = $aTitle[] = $oShop_Producer->name;
	}

	if ($Shop_Controller_Show->page)
	{
		array_unshift($aTitle, $pageName . ' ' . ($Shop_Controller_Show->page + 1));
	}

	if (count($aTitle) > 1)
	{
		$aTitle = array_reverse($aTitle);
		$aDescription = array_reverse($aDescription);
		$aKeywords = array_reverse($aKeywords);

		Core_Page::instance()->title(implode($pageSeparator, $aTitle));
		Core_Page::instance()->description(implode($pageSeparator, $aDescription));
		Core_Page::instance()->keywords(implode($pageSeparator, $aKeywords));
	}

	Core_Page::instance()->object = $Shop_Controller_Show;
}