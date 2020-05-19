<?php

$oShop = Core_Entity::factory('Shop', Core_Array::get(Core_Page::instance()->libParams, 'shopId'));

$Shop_Controller_Show = new Shop_Controller_Show($oShop);

/* Количество */
$on_page = intval(Core_Array::getGet('on_page'));
if ($on_page > 0 && $on_page < 150)
{
	$limit = $on_page;

	$Shop_Controller_Show->addEntity(
		Core::factory('Core_Xml_Entity')
			->name('on_page')->value($on_page)
	);
}
else
{
	$limit = $oShop->items_on_page;
}

$Shop_Controller_Show
	//->warehouseMode('in-stock')
	->limit($limit)
	->parseUrl();

// Обработка скачивания файла электронного товара
$guid = Core_Array::getGet('download_file');
if (strlen($guid))
{
	$oShop_Order_Item_Digital = Core_Entity::factory('Shop_Order_Item_Digital')->getByGuid($guid);

	if (!is_null($oShop_Order_Item_Digital) && $oShop_Order_Item_Digital->Shop_Order_Item->Shop_Order->shop_id == $oShop->id)
	{
		$iDay = 7;

		// Проверяем, доступна ли ссылка (Ссылка доступна в течение недели после оплаты)
		if (Core_Date::sql2timestamp($oShop_Order_Item_Digital->Shop_Order_Item->Shop_Order->payment_datetime) > time() - 24 * 60 * 60 * $iDay)
		{
			$oShop_Item_Digital = $oShop_Order_Item_Digital->Shop_Item_Digital;
			if ($oShop_Item_Digital->filename != '')
			{
				Core_File::download($oShop_Item_Digital->getFullFilePath(), $oShop_Item_Digital->filename);
				exit();
			}
		}
		else
		{
			Core_Message::show(Core::_('Shop_Order_Item_Digital.time_is_up', $iDay));
		}
	}

	Core_Page::instance()->response->status(404)->sendHeaders()->showBody();
	exit();
}

// Быстрый фильтр
if (Core_Array::getRequest('fast_filter'))
{
	$aJson = array();

	if ($oShop->filter)
	{
		$Shop_Controller_Show->modificationsList(TRUE);

		// В корне выводим из всех групп
		if ($Shop_Controller_Show->group == 0)
		{
			$Shop_Controller_Show->group(FALSE);
		}

		// Запрещаем выбор модификаций при выключенном modificationsList
		!$Shop_Controller_Show->modificationsList && $Shop_Controller_Show->forbidSelectModifications();

		foreach ($_POST as $key => $value)
		{
			if (strpos($key, 'property_') === 0)
			{
				$Shop_Controller_Show->removeFilter('property', substr($key, 9));
			}
			elseif (strpos($key, 'price_') === 0)
			{
				$Shop_Controller_Show->removeFilter('price');
			}
		}

		// Remove all checkboxes
		$aFilterProperties = $Shop_Controller_Show->getFilterProperties();
		foreach ($aFilterProperties as $propertyId => $aTmpProperties)
		{
			if (isset($aTmpProperties[0]) && $aTmpProperties[0][0]->type == 7)
			{
				$Shop_Controller_Show->removeFilter('property', $propertyId);
			}
		}

		// Prices
		$Shop_Controller_Show->setFilterPricesConditions($_POST);

		// Additional properties
		$Shop_Controller_Show->setFilterPropertiesConditions($_POST);

		if (Core_Array::getPost('producer_id'))
		{
			$iProducerId = intval(Core_Array::getPost('producer_id'));
			$Shop_Controller_Show->producer($iProducerId);
		}

		$Shop_Controller_Show->applyItemCondition();

		$Shop_Controller_Show->group !== FALSE && $Shop_Controller_Show->applyGroupCondition();

		$Shop_Controller_Show->applyFilter();

		$Shop_Controller_Show
			->shopItems()
			->queryBuilder()
			->where('shortcut_id', '=', 0)
			->clearGroupBy()
			->clearOrderBy();

		$aJson['count'] = intval($Shop_Controller_Show->shopItems()->getCount(FALSE, 'shop_items.id', TRUE));
	}

	Core::showJson($aJson);
}

// Сравнение товаров
if (Core_Array::getRequest('compare'))
{
	$shop_item_id = intval(Core_Array::getRequest('compare'));

	if (Core_Entity::factory('Shop_Item', $shop_item_id)->shop_id == $oShop->id)
	{
		Core_Session::start();
		if (isset($_SESSION['hostcmsCompare'][$oShop->id][$shop_item_id]))
		{
			unset($_SESSION['hostcmsCompare'][$oShop->id][$shop_item_id]);
		}
		else
		{
			$_SESSION['hostcmsCompare'][$oShop->id][$shop_item_id] = 1;
		}
	}

	Core_Page::instance()->response
		->status(200)
		->header('Pragma', "no-cache")
		->header('Cache-Control', "private, no-cache")
		->header('Vary', "Accept")
		->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
		->header('X-Powered-By', 'HostCMS')
		->header('Content-Disposition', 'inline; filename="files.json"');

	Core_Page::instance()->response
		->body(json_encode('OK'))
		->header('Content-type', 'application/json; charset=utf-8');

	Core_Page::instance()->response
		->sendHeaders()
		->showBody();

	exit();
}

// Избранное
if (Core_Array::getRequest('favorite'))
{
	$shop_item_id = intval(Core_Array::getRequest('favorite'));

	if (Core_Entity::factory('Shop_Item', $shop_item_id)->shop_id == $oShop->id)
	{
		Core_Session::start();
		Core_Session::setMaxLifeTime(86400 * 30);
		if (isset($_SESSION['hostcmsFavorite'][$oShop->id]) && in_array($shop_item_id, $_SESSION['hostcmsFavorite'][$oShop->id]))
		{
			unset($_SESSION['hostcmsFavorite'][$oShop->id][
				array_search($shop_item_id, $_SESSION['hostcmsFavorite'][$oShop->id])
			]);
		}
		else
		{
			$_SESSION['hostcmsFavorite'][$oShop->id][] = $shop_item_id;
		}
	}

	Core_Page::instance()->response
		->status(200)
		->header('Pragma', "no-cache")
		->header('Cache-Control', "private, no-cache")
		->header('Vary', "Accept")
		->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
		->header('X-Powered-By', 'HostCMS')
		->header('Content-Disposition', 'inline; filename="files.json"');

	Core_Page::instance()->response
		->body(json_encode('OK'))
		->header('Content-type', 'application/json; charset=utf-8');

	Core_Page::instance()->response
		->sendHeaders()
		->showBody();

	exit();
}

// Viewed items
if ($Shop_Controller_Show->item && $Shop_Controller_Show->viewed)
{
	// Core_Session::start();
	// Core_Session::setMaxLifeTime(28800, TRUE);

	$Shop_Controller_Show->addIntoViewed();
}

if (!is_null(Core_Array::getGet('vote')))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
	$entity_id = intval(Core_Array::getGet('id'));

	if ($entity_id && !is_null($oSiteuser))
	{
		$entity_type = strval(Core_Array::getGet('entity_type'));
		$vote = intval(Core_Array::getGet('vote'));

		$oObject = Vote_Controller::instance()->getVotedObject($entity_type, $entity_id);

		if (!is_null($oObject))
		{
			$oVote = $oObject->Votes->getBySiteuser_Id($oSiteuser->id);

			$vote_value = $vote ? 1 : -1;

			$deleteVote = 0;
			// Пользователь не голосовал ранее
			if (is_null($oVote))
			{
				$oVote = Core_Entity::factory('Vote');
				$oVote->siteuser_id = $oSiteuser->id;
				$oVote->value = $vote_value;

				$oObject->add($oVote);
			}
			// Пользователь голосовал ранее, но поставил противоположную оценку
			elseif ($oVote->value != $vote_value)
			{
				$oVote->value = $vote_value;
				$oVote->save();
			}
			// Пользователь голосовал ранее и поставил такую же оценку как и ранее, обнуляем его голосование, как будто он вообще не голосовал
			else
			{
				$deleteVote = 1;
				$oVote->delete();
			}

			Core_Entity::factory('Shop_Item', $entity_id)->clearCache();

			$aVotingStatistic = Vote_Controller::instance()->getRate($entity_type, $entity_id);

			Core_Page::instance()->response
			->body(
				json_encode(array('value' => $oVote->value, 'item' => $oObject->id, 'entity_type' => $entity_type,
					'likes' => $aVotingStatistic['likes'], 'dislikes' => $aVotingStatistic['dislikes'],
					'rate' => $aVotingStatistic['rate'], 'delete_vote' => $deleteVote)
				)
			);
		}
	}

	Core_Page::instance()->response
		->status(200)
		->header('Pragma', "no-cache")
		->header('Cache-Control', "private, no-cache")
		->header('Vary', 'Accept')
		->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
		->header('X-Powered-By', 'HostCMS')
		->header('Content-Disposition', 'inline; filename="files.json"');

	if (strpos(Core_Array::get($_SERVER, 'HTTP_ACCEPT', ''), 'application/json') !== FALSE)
	{
		Core_Page::instance()->response->header('Content-type', 'application/json; charset=utf-8');
	}
	else
	{
		Core_Page::instance()->response
			->header('X-Content-Type-Options', 'nosniff')
			->header('Content-type', 'text/plain; charset=utf-8');
	}

	if(Core_Array::getRequest('_'))
	{
		Core_Page::instance()->response
			->sendHeaders()
			->showBody();
		exit();
	}
}

Core_Page::instance()->object = $Shop_Controller_Show;