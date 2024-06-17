<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Экспорт в VendorYML для магазина.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию TRUE.
 *
 * <code>
 * $Shop_Controller_YandexVendor = new Shop_Controller_YandexVendor(
 * 	Core_Entity::factory('Shop', 1)
 * );
 *
 * $Shop_Controller_YandexVendor->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Controller_YandexVendor extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'itemsProperties',
		'protocol'
	);

	/**
	 * Shop's producers object
	 * @var Shop_Item_Model
	 */
	protected $_Shop_Producers = NULL;

	/**
	 * Array of siteuser's groups allowed for current siteuser
	 * @var array
	 */
	protected $_aSiteuserGroups = array();

	/**
	 * Current site alias
	 * @var string
	 */
	protected $_siteAlias = NULL;

	/**
	 * Shop URL
	 * @var string
	 */
	protected $_shopPath = NULL;

	/**
	 * Информация о ссылках.
	 * @var array
	 */
	public $aModelUrls = array(
		'instructionUrl',
		'driversUrl',
	);

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		$this->protocol = Core::httpsUses() ? 'https' : 'http';

		$this->_Shop_Producers = $oShop->Shop_Producers;

		$siteuser_id = 0;

		$this->_aSiteuserGroups = array(0, -1);

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if ($oSiteuser)
			{
				$siteuser_id = $oSiteuser->id;

				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll(FALSE);
				foreach ($aSiteuser_Groups as $oSiteuser_Group)
				{
					$this->_aSiteuserGroups[] = $oSiteuser_Group->id;
				}
			}
		}

		$this->itemsProperties = TRUE;
	}

	/**
	 * Get producers set
	 * @return Shop_Item_Model
	 */
	public function shopProducers()
	{
		return $this->_Shop_Producers;
	}

	/**
	 * Show vendors
	 * @return self
	 */
	protected function _vendors()
	{
		$oShop = $this->getEntity();

		$offset = 0;
		$limit = 100;

		do {
			$oShop_Producers = $this->_Shop_Producers;
			$oShop_Producers
				->queryBuilder()
				->where('shop_producers.active', '=', 1)
				->offset($offset)
				->limit($limit);

			$aShop_Producers = $oShop_Producers->findAll(FALSE);

			foreach ($aShop_Producers as $oShop_Producer)
			{
				echo '<vendor name="' . Core_Str::xml($oShop_Producer->name) . '">' . "\n";

				if ($oShop_Producer->site)
				{
					echo '<url>' . Core_Str::xml($oShop_Producer->site) . '</url>' . "\n";
				}

				$this
					->_categories($oShop_Producer)
					->_models($oShop_Producer);

				echo '</vendor>' . "\n";
			}

			$offset += $limit;
		}
		while (count($aShop_Producers));

		return $this;
	}

	/**
	 * Show categories
	 * @param Shop_Producer_Model $oShop_Producer
	 * @return self
	 */
	protected function _categories(Shop_Producer_Model $oShop_Producer)
	{
		$oShop = $this->getEntity();

		$oShop_Groups = $oShop->Shop_Groups;
		$oShop_Groups
			->queryBuilder()
			->select('shop_groups.*')
			->join('shop_items', 'shop_items.shop_group_id', '=', 'shop_groups.id')
			->where('shop_items.shop_producer_id', '=', $oShop_Producer->id)
			->where('shop_groups.shortcut_id', '=', 0)
			->groupBy('shop_groups.id');

		$aShop_Groups = $oShop_Groups->findAll(FALSE);

		echo "<categories>\n";

		foreach ($aShop_Groups as $oShop_Group)
		{
			$group_parent_id = $oShop_Group->parent_id == '' || $oShop_Group->parent_id == 0 ? '' : ' parentId="' . $oShop_Group->parent_id . '"';

			echo '<category id="' . $oShop_Group->id . '"' . $group_parent_id . '>' . Core_Str::xml($oShop_Group->name) . "</category>\n";
		}
		echo "</categories>\n";

		return $this;
	}

	/**
	 * Show models
	 * @param Shop_Producer_Model $oShop_Producer
	 * @return self
	 */
	protected function _models(Shop_Producer_Model $oShop_Producer)
	{
		$oShop = $this->getEntity();

		$offset = 0;
		$limit = 100;

		$oShop_Item_Controller = new Shop_Item_Controller();

		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

		foreach ($this->aModelUrls as $modelTagName)
		{
			$aModelProperties[$modelTagName] = $oShop_Item_Property_List->Properties->getByTag_name($modelTagName);
		}

		do {
			/* Группы товаров и товары производителя */
			$dateTime = Core_Date::timestamp2sql(time());
			$oShop_Items = $oShop_Producer->Shop_Items;
			$oShop_Items
				->queryBuilder()
				->select('shop_items.*')
				->leftJoin('shop_groups', 'shop_groups.id', '=', 'shop_items.shop_group_id')
				->where('shop_groups.active', '=', 1)
				->where('shop_groups.id', '>', 0)
				->where('shop_groups.deleted', '=', 0)
				->where('shop_items.shortcut_id', '=', 0)
				->where('shop_items.deleted', '=', 0)
				->where('shop_items.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
				->open()
				->where('shop_items.start_datetime', '<', $dateTime)
				->setOr()
				->where('shop_items.start_datetime', '=', '0000-00-00 00:00:00')
				->close()
				->setAnd()
				->open()
				->where('shop_items.end_datetime', '>', $dateTime)
				->setOr()
				->where('shop_items.end_datetime', '=', '0000-00-00 00:00:00')
				->close()
				->where('shop_items.price', '>', 0)
				->offset($offset)
				->limit($limit);

			$aShop_Items = $oShop_Items->findAll(FALSE);

			if (count($aShop_Items))
			{
				echo '<models>' . "\n";

				foreach ($aShop_Items as $oShop_Item)
				{
					echo '<model id="' . $oShop_Item->id . '" categoryId="' . $oShop_Item->shop_group_id . '">' . "\n";

					//Наименование модели.
					echo '<name>' . Core_Str::xml($oShop_Item->name) . '</name>' . "\n";

					//Код товара (указывается код, присвоенный производителем).
					if ($oShop_Item->marking)
					{
						echo '<vendorCode>' . Core_Str::xml($oShop_Item->marking) . '</vendorCode>' . "\n";
					}

					//URL модели на сайте производителя.
					echo '<promoUrl>' . Core_Str::xml($this->_shopPath . $oShop_Item->getPath()) . '</promoUrl>' . "\n";

					//URL изображения модели на сайте производителя.
					if ($oShop_Item->image_large)
					{
						echo '<pictureUrl>' . $this->protocol . '://' . Core_Str::xml($this->_siteAlias->name . $oShop_Item->getLargeFileHref()) . '</pictureUrl>' . "\n";
					}

					/* Дополнительные изображения */
					$oImageProperty = Core_Entity::factory('Property')->getByTag_name('vendor_image');
					if (!is_null($oImageProperty))
					{
						$aImageValues = $oImageProperty->getValues($oShop_Item->id);
						if (isset($aImageValues[0]))
						{
							foreach ($aImageValues as $oImageValue)
							{
								if ($oImageValue->file)
								{
									echo '<pictureUrl>' . $this->protocol . '://' . Core_Str::xml($this->_siteAlias->name . $oShop_Item->getItemHref() . $oImageValue->file) . '</pictureUrl>' . "\n";
								}
							}
						}
					}

					/*
					* Ссылка на инструкцию для данной модели на сайте производителя.
					* Ссылка на драйверы для данной модели на сайте производителя.
					*/
					foreach ($this->aModelUrls as $modelTagName)
					{
						$oUrl = $aModelProperties[$modelTagName];

						if (!is_null($oUrl) && $oUrl->type == 1 && $oUrl->multiple == 0)
						{
							$aUrlValues = $oUrl->getValues($oShop_Item->id);

							if (isset($aUrlValues[0]) && strlen(trim($aUrlValues[0]->value)))
							{
								echo '<'. $modelTagName . '>' . Core_Str::xml($this->_getValue($aUrlValues[0])) . '</'. $modelTagName . '>' . "\n";
							}
						}
					}

					// Штрих-код товара.
					$oBarcodeProperty = Core_Entity::factory('Property')->getByTag_name('vendor_barcode');
					if (!is_null($oBarcodeProperty))
					{
						$aBarcodeValues = $oBarcodeProperty->getValues($oShop_Item->id);
						if (isset($aBarcodeValues[0]))
						{
							foreach ($aBarcodeValues as $oBarcodeValue)
							{
								if ($oBarcodeValue->value)
								{
									echo '<barcode>' . Core_Str::xml($oBarcodeValue->value) . '</barcode>' . "\n";
								}
							}
						}
					}

					// Дата анонсирования модели.
					echo '<announceDate>' . date('Y-m-d', Core_Date::sql2timestamp($oShop_Item->start_datetime)) . '</announceDate>' . "\n";

					// Дата начала официальных продаж.
					echo '<inStockDate>' . date('Y-m-d', Core_Date::sql2timestamp($oShop_Item->start_datetime)) . '</inStockDate>' . "\n";

					// Актуальность модели
					$bIsActual = $oShop_Item->active == 1 ? 'true' : 'false';
					echo '<isActual>' . Core_Str::xml($bIsActual) . '</isActual>' . "\n";

					// Рекомендованная цена.
					$aPrices = $oShop_Item_Controller->calculatePriceInItemCurrency($oShop_Item->price, $oShop_Item);

					echo '<recomendedPrice currency="' . $oShop_Item->Shop_Currency->code . '">' . $aPrices['price_discount'] . '</recomendedPrice>' . "\n";

					// Дата добавления описания модели в каталог.
					echo '<addDate>' . date('Y-m-d', Core_Date::sql2timestamp($oShop_Item->datetime)) . '</addDate>' . "\n";

					// Дата изменения спецификации модели.
					echo '<updateDate>' . date('Y-m-d', Core_Date::sql2timestamp($oShop_Item->datetime)) . '</updateDate>' . "\n";

					// Описание модели.
					if ($oShop_Item->description)
					{
						echo '<description>' . Core_Str::xml(htmlspecialchars($oShop_Item->description)) . '</description>' . "\n";
					}

					//Параметры модели.
					$this->itemsProperties && $this->_addPropertyValue($oShop_Item);

					echo '</model>' . "\n";
				}

				echo '</models>' . "\n";
			}

			Core_File::flush();

			$offset += $limit;
		}
		while (count($aShop_Items));

		return $this;
	}

	/**
	 * Print Shop_Item properties
	 * @param Shop_Item_Model $oShop_Item
	 * @return self
	 */
	protected function _addPropertyValue(Shop_Item_Model $oShop_Item)
	{
		// Доп. св-ва выводятся в <param>
		// <param name="Максимальный формат">А4</param>
		//$aProperty_Values = $oShop_Item->getPropertyValues(FALSE);

		if (is_array($this->itemsProperties))
		{
			$aProperty_Values = Property_Controller_Value::getPropertiesValues($this->itemsProperties, $oShop_Item->id, FALSE);
		}
		else
		{
			$aProperty_Values = $oShop_Item->getPropertyValues(FALSE);
		}

		$aDisabledProperties = array_merge($this->aModelUrls, array('vendor_barcode'));

		foreach ($aProperty_Values as $oProperty_Value)
		{
			$oProperty = $oProperty_Value->Property;

			switch ($oProperty->type)
			{
				case 0: // Int
				case 1: // String
				case 4: // Textarea
				case 6: // Wysiwyg
				case 11: // Float
					$value = $oProperty_Value->value;
				break;
				
				case 8: // Date
					$value = $oProperty_Value->value != '0000-00-00 00:00:00'
						? Core_Date::sql2date($oProperty_Value->value)
						: NULL;
				break;

				case 9: // Datetime
					$value = $oProperty_Value->value != '0000-00-00 00:00:00'
						? Core_Date::sql2datetime($oProperty_Value->value)
						: NULL;
				break;

				case 3: // List
					$value = NULL;

					$oList_Item = $oProperty->List->List_Items->getById(
						$oProperty_Value->value, FALSE
					);

					!is_null($oList_Item) && $value = $oList_Item->value;
				break;

				case 7: // Checkbox
					$value = $oProperty_Value->value == 1 ? 'есть' : NULL;
				break;

				case 2: // File
				case 5: // ИС
				case 10: // Hidden field
				default:
					$value = NULL;
				break;
			}

			if (!is_null($value) && !in_array($oProperty->tag_name, $aDisabledProperties))
			{
				$sTagName = 'param';

				$unit = $oProperty->type == 0 && $oProperty->Shop_Item_Property->Shop_Measure->id
					? ' unit="' . Core_Str::xml($oProperty->Shop_Item_Property->Shop_Measure->name) . '"'
					: '';

				$sAttr = ' name="' . Core_Str::xml($oProperty->name) . '"' . $unit;

				echo '<' . $sTagName . $sAttr . '>' . Core_Str::xml(html_entity_decode(strip_tags($value), ENT_COMPAT, 'UTF-8')) . '</' . $sTagName . '>'. "\n";
			}
		}

		return $this;
	}

	/**
	 * Get value depends on type, e.g. list item for list-type property
	 * @param Core_Entity $oProperty_Value
	 * @return mixed
	 */
	protected function _getValue(Core_Entity $oProperty_Value)
	{
		$oProperty = $oProperty_Value->Property;

		switch ($oProperty->type)
		{
			case 0: // Int
			case 1: // String
			case 4: // Textarea
			case 6: // Wysiwyg
			case 8: // Date
			case 9: // Datetime
				$value = $oProperty_Value->value;
			break;

			case 3: // List
				$value = NULL;

				$oList_Item = $oProperty->List->List_Items->getById(
					$oProperty_Value->value, FALSE
				);

				!is_null($oList_Item) && $value = $oList_Item->value;
			break;

			case 7: // Checkbox
				$value = $oProperty_Value->value == 1 ? 'да' : NULL;
			break;

			case 2: // File
			case 5: // ИС
			case 10: // Hidden field
			default:
				$value = NULL;
			break;
		}

		return $value;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Controller_YandexVendor.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oShop = $this->getEntity();
		$oSite = $oShop->Site;

		!is_null(Core_Page::instance()->response) && Core_Page::instance()->response
			->header('Content-Type', "text/xml; charset={$oSite->coding}")
			->sendHeaders();

		$this->_siteAlias = $oSite->getCurrentAlias();
		$this->_shopPath = $this->protocol . '://' . $this->_siteAlias->name . $oShop->Structure->getPath();

		echo '<?xml version="1.0" encoding="' . $oSite->coding . '"?>' . "\n";
		echo '<yml_catalog xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" date="' . date('c') . '" version="1.0" xsi:noNamespaceSchemaLocation="VendorYML-1.0.xsd">' . "\n";

		/* Производители */
		$this->_vendors();

		echo '</yml_catalog>'."\n";

		Core_File::flush();
	}
}