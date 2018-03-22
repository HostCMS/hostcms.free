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

if ($Shop_Controller_Show->item == 0)
{
	$Shop_Controller_Show->itemsForbiddenTags(array('text'));

	// Producers
	if (Core_Array::getGet('producer_id'))
	{
		$iProducerId = intval(Core_Array::getGet('producer_id'));
		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('producer_id')->value($iProducerId)
		);

		$Shop_Controller_Show->shopItems()
			->queryBuilder()
			->select('shop_items.*')
			->where('shop_items.shop_producer_id', '=', $iProducerId);

		$Shop_Controller_Show->addCacheSignature('producer_id=' . $iProducerId);
	}

	if (Core_Array::getGet('filter') || Core_Array::getGet('sorting'))
	{
		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('filter')->value(1)
		);

		$oShop = $Shop_Controller_Show->getEntity();

		$sorting = intval(Core_Array::getGet('sorting'));
		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('sorting')->value($sorting)
		);
		$Shop_Controller_Show->addCacheSignature('sorting=' . $sorting);

		// Prices
		$price_from = intval(Core_Array::getGet('price_from'));
		$price_to = intval(Core_Array::getGet('price_to'));
		if ($price_from || $price_to || $sorting == 1 || $sorting == 2)
		{
			// Получаем список валют магазина
			$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll();

			$query_tax = 'IF(`shop_taxes`.`tax_is_included` IS NULL OR `shop_taxes`.`tax_is_included` = 1, 0, `shop_items`.`price` * `shop_taxes`.`rate` / 100)';
			$query_currency_switch = "`shop_items`.`price` + {$query_tax}";
			foreach ($aShop_Currencies as $oShop_Currency)
			{
				// Получаем коэффициент пересчета для каждой валюты
				$currency_coefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					$oShop_Currency, $oShop->Shop_Currency
				);

				$query_currency_switch = "IF (`shop_items`.`shop_currency_id` = '{$oShop_Currency->id}', IF (COUNT(`shop_discounts`.`id`), ((`shop_items`.`price` + {$query_tax}) * (1 - SUM(DISTINCT IF(`shop_discounts`.`type` = 0, `shop_discounts`.`value`, 0)) / 100)) * {$currency_coefficient} - SUM(DISTINCT IF(`shop_discounts`.`type`, `shop_discounts`.`value`, 0)), (`shop_items`.`price`) * {$currency_coefficient}), {$query_currency_switch})";
			}

			$current_date = date('Y-m-d H:i:s');
			$Shop_Controller_Show->shopItems()
				->queryBuilder()
				->select(array(Core_QueryBuilder::expression($query_currency_switch), 'absolute_price'))
				->leftJoin('shop_item_discounts', 'shop_items.id', '=', 'shop_item_discounts.shop_item_id')
				->leftJoin('shop_discounts', 'shop_item_discounts.shop_discount_id', '=', 'shop_discounts.id', array(
					array('AND ' => array('shop_discounts.active', '=', 1)),
					array('AND ' => array('shop_discounts.deleted', '=', 0)),
					array('AND' => array('shop_discounts.start_datetime', '<=', $current_date)),
					array('AND (' => array('shop_discounts.end_datetime', '>=', $current_date)),
					array('OR' => array('shop_discounts.end_datetime', '=', '0000-00-00 00:00:00')),
					array(')' => NULL)
				))
				->leftJoin('shop_taxes', 'shop_taxes.id', '=', 'shop_items.shop_tax_id')
				->groupBy('shop_items.id');

			if ($price_from)
			{
				$Shop_Controller_Show->shopItems()->queryBuilder()->having('absolute_price', '>=', $price_from);
				$Shop_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('price_from')->value($price_from)
				);
				$Shop_Controller_Show->addCacheSignature('price_from=' . $price_from);
			}
			if ($price_to)
			{
				$Shop_Controller_Show->shopItems()->queryBuilder()->having('absolute_price', '<=', $price_to);
				$Shop_Controller_Show->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('price_to')->value($price_to)
				);
				$Shop_Controller_Show->addCacheSignature('price_to=' . $price_to);
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

		$aProperties = $Shop_Controller_Show->group !== FALSE && is_null($Shop_Controller_Show->tag)
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
				$aPropertiesValue = array_map('strval', $aPropertiesValue);

				$aTmpProperties[] = array($oProperty, $aPropertiesValue);
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
					$to = Core_Array::get($tmpTo, $iKey);

					$aTmpProperties[] = array($oProperty, array(
							'from' => $sValue != ''
								? ($oProperty->type == 11 ? floatval($sValue) : intval($sValue))
								: '',
							'to' => $to != ''
								? ($oProperty->type == 11 ? floatval($to) : intval($to))
								: ''
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
			while(list(, list($oProperty, $aPropertyValues)) = each($aTmpProperties))
			{
				$tableName = $oProperty->createNewValue(0)->getTableName();

				!in_array($tableName, $aTableNames) && $aTableNames[] = $tableName;

				$Shop_Controller_Show->shopItems()->queryBuilder()
					->where('shop_item_properties.property_id', '=', $oProperty->id);

				if (!isset($aPropertyValues['from']))
				{
					// Для строк фильтр LIKE %...%
					if ($oProperty->type == 1)
					{
						foreach ($aPropertyValues as $propertyValue)
						{
							$Shop_Controller_Show->shopItems()->queryBuilder()
								->where($tableName . '.value', 'LIKE', "%{$propertyValue}%");
						}
					}
					else
					{
						// Checkbox
						$oProperty->type == 7 && $aPropertyValues[0] != '' && $aPropertyValues = array(1);

						$bCheckUnset = $oProperty->type != 7 && $oProperty->type != 3;

						$bCheckUnset && $Shop_Controller_Show->shopItems()->queryBuilder()->open();

						$Shop_Controller_Show->shopItems()->queryBuilder()
							->where(
								$tableName . '.value',
								count($aPropertyValues) == 1 ? '=' : 'IN',
								count($aPropertyValues) == 1 ? $aPropertyValues[0] : $aPropertyValues
							);

						$bCheckUnset && $Shop_Controller_Show->shopItems()->queryBuilder()
							->setOr()
							->where($tableName . '.value', 'IS', NULL)
							->close();
					}

					$Shop_Controller_Show->shopItems()->queryBuilder()
						->setOr();

					foreach ($aPropertyValues as $propertyValue)
					{
						$Shop_Controller_Show->addEntity(
							Core::factory('Core_Xml_Entity')
								->name('property_' . $oProperty->id)->value($propertyValue)
						);
						$Shop_Controller_Show->addCacheSignature("property{$oProperty->id}={$propertyValue}");
					}
				}
				else
				{
					$from = trim(Core_Array::get($aPropertyValues, 'from'));
					$from && $Shop_Controller_Show->shopItems()->queryBuilder()
						->open()
						->where($tableName . '.value', '>=', $from)
						->setOr()
						->where($tableName . '.value', 'IS', NULL)
						->close()
						->setAnd();

					$to = trim(Core_Array::get($aPropertyValues, 'to'));
					$to && $Shop_Controller_Show->shopItems()->queryBuilder()
						->open()
						->where($tableName . '.value', '<=', $to)
						->setOr()
						->where($tableName . '.value', 'IS', NULL)
						->close();

					$Shop_Controller_Show->shopItems()->queryBuilder()
						->setOr();

					$Shop_Controller_Show->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('property_' . $oProperty->id . '_from')->value($from)
					)->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('property_' . $oProperty->id . '_to')->value($to)
					);

					$Shop_Controller_Show
						->addCacheSignature("property{$oProperty->id}_from={$from}")
						->addCacheSignature("property{$oProperty->id}_to={$to}");
				}
			}

			$Shop_Controller_Show->shopItems()->queryBuilder()
				->close()
				->groupBy('shop_items.id');

			$havingCount > 1
				&& $Shop_Controller_Show->shopItems()->queryBuilder()
						->having(Core_Querybuilder::expression('COUNT(DISTINCT `shop_item_properties`.`property_id`)'), '=', $havingCount);

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
}
else
{
	if (Core_Array::getPost('add_comment') && Core_Array::get(Core_Page::instance()->libParams, 'showComments', 1))
	{
		$oShop = $Shop_Controller_Show->getEntity();

		$Shop_Controller_Show->cache(FALSE);

		$oLastComment = Core_Entity::factory('Comment')->getLastCommentByIp(
			Core_Array::get($_SERVER, 'REMOTE_ADDR')
		);

		$oXmlCommentTag = Core::factory('Core_Xml_Entity')
			->name('document');

		$siteuser_id = 0;
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if ($oSiteuser)
			{
				$siteuser_id = $oSiteuser->id;
			}
		}

		$oComment = Core_Entity::factory('Comment');

		$allowable_tags = '<b><strong><i><em><br><p><u><strike><ul><ol><li>';
		$oComment->parent_id = intval(Core_Array::getPost('parent_id', 0));
		$oComment->active = $oShop->comment_active;
		$oComment->author = Core_Str::stripTags(Core_Array::getPost('author'));
		$oComment->email = Core_Str::stripTags(Core_Array::getPost('email'));
		$oComment->phone = Core_Str::stripTags(Core_Array::getPost('phone'));
		$oComment->grade = intval(Core_Array::getPost('grade', 0));
		$oComment->subject = Core_Str::stripTags(Core_Array::getPost('subject'));
		$oComment->text = nl2br(Core_Str::stripTags(Core_Array::getPost('text'), $allowable_tags));
		$oComment->siteuser_id = $siteuser_id;

		$oShop_Item = Core_Entity::factory('Shop_Item', $Shop_Controller_Show->item);

		$oXmlCommentTag
			->addEntity($oComment)
			->addEntity($oShop_Item);

		if (is_null($oLastComment) || time() > Core_Date::sql2timestamp($oLastComment->datetime) + ADD_COMMENT_DELAY)
		{
			if ($oShop->use_captcha == 0 || $siteuser_id > 0 || Core_Captcha::valid(Core_Array::getPost('captcha_id'), Core_Array::getPost('captcha')))
			{
				// Antispam
				if (Core::moduleIsActive('antispam'))
				{
					$Antispam_Controller = new Antispam_Controller();
					$bAntispamAnswer = $Antispam_Controller
						->addText($oComment->author)
						->addText($oComment->email)
						->addText($oComment->phone)
						->addText($oComment->subject)
						->addText($oComment->text)
						->execute();
				}
				else
				{
					$bAntispamAnswer = TRUE;
				}

				if ($bAntispamAnswer)
				{
					$oComment->save();

					$oComment
						->dateFormat($oShop->format_date)
						->dateTimeFormat($oShop->format_datetime);

					$oShop_Item->add($oComment)->clearCache();

					$oXmlCommentTag->addEntity($oShop);

					// Отправка письма администратору
					$sText = Xsl_Processor::instance()
						->xml($oXmlCommentTag->getXml())
						->xsl(
							Core_Entity::factory('Xsl')
								->getByName(Core_Array::get(Core_Page::instance()->libParams, 'addCommentAdminMailXsl'))
						)
						->process();

					$aFrom = array_map('trim', explode(',', EMAIL_TO));

					Core_Mail::instance()
						->to(EMAIL_TO)
						->from($aFrom[0])
						->header('Reply-To', Core_Valid::email($oComment->email)
							? $oComment->email
							: $aFrom[0]
						)
						->subject(Core::_('Shop.comment_mail_subject'))
						->message(trim($sText))
						->contentType(Core_Array::get(Core_Page::instance()->libParams, 'commentMailNoticeType', 0) == 0
							? 'text/plain'
							: 'text/html'
						)
						->send();
				}
				else
				{
					$oXmlCommentTag->addEntity(Core::factory('Core_Xml_Entity')
						->name('error_antispam')->value(1)
					);

					$oComment->text = Core_Str::br2nl($oComment->text);
					$Shop_Controller_Show->addEntity($oComment);
				}
			}
			else
			{
				$oXmlCommentTag->addEntity(Core::factory('Core_Xml_Entity')
					->name('error_captcha')->value(1)
				);

				$oComment->text = Core_Str::br2nl($oComment->text);
				$Shop_Controller_Show->addEntity($oComment);
			}
		}
		else
		{
			$oXmlCommentTag->addEntity(Core::factory('Core_Xml_Entity')
				->name('error_time')->value(1)
			);

			$oComment->text = Core_Str::br2nl($oComment->text);
			$Shop_Controller_Show->addEntity($oComment);
		}

		// Результат добавления комментария
		$xsl_result = Xsl_Processor::instance()
			->xml($oXmlCommentTag->getXml())
			->xsl(Core_Entity::factory('Xsl')->getByName(
				Core_Array::get(Core_Page::instance()->libParams, 'addCommentNoticeXsl'))
			)
			->process();

		$Shop_Controller_Show->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('message')->value($xsl_result)
		);
	}
}

// В корне выводим из всех групп
/* if ($Shop_Controller_Show->group == 0)
{
	$Shop_Controller_Show->group(FALSE)->forbidSelectModifications();
}*/

/* Производители */
$oShop = $Shop_Controller_Show->getEntity();

// XML-сущность, к которй будут добавляться производители
$oProducersXmlEntity = Core::factory('Core_Xml_Entity')->name('producers');

// Добавляем XML-сущность контроллеру показа
$Shop_Controller_Show->addEntity($oProducersXmlEntity);

// Список производителей
$oShop_Producers = $oShop->Shop_Producers;
$oShop_Producers->queryBuilder()
	->select('shop_producers.*')
	->distinct()
	->join('shop_items', 'shop_items.shop_producer_id', '=', 'shop_producers.id')
	->where('shop_items.shop_group_id', '=', $Shop_Controller_Show->group)
	->where('shop_items.deleted', '=', 0);

$aShop_Producers = $oShop_Producers->findAll();
foreach ($aShop_Producers as $oShop_Producer)
{
	// Добавляем производителя потомком XML-сущности
	$oProducersXmlEntity->addEntity(
		$oShop_Producer->clearEntities()
	);
}

$Shop_Controller_Show
	->xsl(
		Core_Entity::factory('Xsl')->getByName($xslName)
	)
	// Выводить свойства товаров
	->itemsProperties(TRUE)
	// Выводить специальные цены
	->specialprices(TRUE)
	// Выводить модификации на уровне с товаром
	//->modificationsList(TRUE)
	// Режим вывода групп
	//->groupsMode('none')
	// Выводить доп. св-ва групп
	//->groupsProperties(TRUE)
	// Фильтровать по ярлыкам
	//->filterShortcuts(TRUE)
	// Учет остатка товаров на складе
	//->warehouseMode('in-stock')
	// Только доступные элементы списков в фильтре
	//->itemsPropertiesListJustAvailable(TRUE)
	->show();