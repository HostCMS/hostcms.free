<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Экспорт в Yandex.Market для магазина.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию TRUE.
 * - outlets(array()) массив соответствия ID склада в системе и ID точки продаж в Яндекс.Маркет.
 * - paymentMethod(array('CASH_ON_DELIVERY' => 1, 'CARD_ON_DELIVERY' => 1, 'YANDEX' => 5)) массив соответствия способов оплаты (CASH_ON_DELIVERY, CARD_ON_DELIVERY, YANDEX) и ID платежных систем в системе управления.
 * - modifications(TRUE|FALSE) экспортировать модификации, по умолчанию TRUE.
 * - recommended(TRUE|FALSE) экспортировать рекомендованные товары, по умолчанию FALSE.
 * - checkAvailable(TRUE|FALSE) проверять остаток на складе, по умолчанию TRUE. Если FALSE, то товар будет выгружаться доступным назвисимо от остатка на складе.
 * - checkRest(TRUE|FALSE) не экспортировать товары с нулевым остатком, по умолчанию FALSE. Если TRUE, то товар будет выгружаться только при наличии остатка на складе.
 * - deliveryOptions(TRUE|FALSE) условия доставки, по умолчанию TRUE. У самого магазина должно быть указано хотя бы одно условие доставки.
 * - type('offer'|'vendor.model'|'book'|'audiobook'|'artist.title'|'tour'|'event-ticket') тип товара, по умолчанию 'offer'
 * - onStep(3000) количество товаров, выбираемых запросом за 1 шаг, по умолчанию 500
 * - stdOut() поток вывода, может использоваться для записи результата в файл. По умолчанию Core_Out_Std
 * - sno() система налогообложения (СНО) магазина. По умолчанию OSN — общая система налогообложения (ОСН).
 *
 *
 * <code>
 * $Shop_Controller_YandexMarket = new Shop_Controller_YandexMarket(
 * 	Core_Entity::factory('Shop', 1)
 * );
 *
 * $Shop_Controller_YandexMarket->show();
 * </code>
 *
 * <code>
 * $Shop_Controller_YandexMarket = new Shop_Controller_YandexMarket(
 * 	Core_Entity::factory('Shop', 1)
 * );
 *
 * // Write to file
 * $oCore_Out_File = new Core_Out_File();
 * $oCore_Out_File->filePath(CMS_FOLDER . "yandexmarket.xml");
 * $Shop_Controller_YandexMarket->stdOut($oCore_Out_File);
 *
 * $Shop_Controller_YandexMarket->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Controller_YandexMarket extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'itemsProperties',
		'outlets',
		'paymentMethod',
		'modifications',
		'recommended',
		'checkAvailable',
		'checkRest',
		'deliveryOptions',
		'type',
		'onStep',
		'protocol',
		'stdOut',
		'mode',
		'token',
		'sno',
		//'pattern',
		//'patternExpressions',
		//'patternParams'
	);

	/**
	 * Shop's items object
	 * @var Shop_Item_Model
	 */
	protected $_Shop_Items = NULL;

	/**
	 * Shop's groups object
	 * @var Shop_Group_Model
	 */
	protected $_Shop_Groups = NULL;

	/**
	 * Shop order object
	 * @var Shop_Order_Model
	 */
	protected $_Shop_Order = NULL;

	/**
	 * Array of siteuser's groups allowed for current siteuser
	 * @var array
	 */
	protected $_aSiteuserGroups = array();

	/**
	 * List's offer tags
	 * @var array
	 */
	public $aOfferTags = array(
		'adult' => 'adult',
		'cpa' => 'cpa',
		'age-year' => 'age-year',
		'age-month' => 'age-month',
		'barcode' => 'barcode',
	);

	/**
	 * List's vendor tags
	 * @var array
	 */
	public $aVendorTags = array(
		'typePrefix' => 'typePrefix',
		'model' => 'model',
		'adult' => 'adult',
		'cpa' => 'cpa',
		'rec' => 'rec',
		'expiry' => 'expiry',
		'weight' => 'weight',
		'dimensions' => 'dimensions',
		'age-year' => 'age-year',
		'age-month' => 'age-month',
	);

	/**
	 * List's book tags
	 * @var array
	 */
	public $aBookTags = array(
		//http://help.yandex.ru/partnermarket/offers.xml#book
		'author' => 'author',
		'publisher' => 'publisher',
		'series' => 'series',
		'year' => 'year',
		'ISBN' => 'ISBN',
		'volume' => 'volume',
		'part' => 'part',
		'language' => 'language',
		'binding' => 'binding',
		'page_extent' => 'page_extent',
		'table_of_contents' => 'table_of_contents',
		'age-year' => 'age-year',
		'age-month' => 'age-month',
	);

	/**
	 * List's audiobook tags
	 * @var array
	 */
	public $aAudiobookTags = array(
		//http://help.yandex.ru/partnermarket/offers.xml#audiobook
		'author' => 'author',
		'publisher' => 'publisher',
		'series' => 'series',
		'year' => 'year',
		'ISBN' => 'ISBN',
		'volume' => 'volume',
		'part' => 'part',
		'language' => 'language',
		'table_of_contents' => 'table_of_contents',
		'performed_by' => 'performed_by',
		'performance_type' => 'performance_type',
		'storage' => 'storage',
		'format' => 'format', //Время звучания задается в формате mm.ss (минуты.секунды).
		'recording_length' => 'recording_length',
		'age-year' => 'age-year',
		'age-month' => 'age-month',
	);

	/**
	 * List's artist.title tags
	 * @var array
	 */
	public $aArtistTitleTags = array(
		'artist' => 'artist',
		'title' => 'title',
		'year' => 'year',
		'media' => 'media',
		'starring' => 'starring',
		'director' => 'director',
		'originalName' => 'originalName',
		'country' => 'country',
		'adult' => 'adult',
		'age-year' => 'age-year',
		'age-month' => 'age-month',
		'barcode' => 'barcode',
	);

	/**
	 * List's tour tags
	 * @var array
	 */
	public $aTourTags = array(
		'worldRegion' => 'worldRegion',
		'country' => 'country',
		'region' => 'region',
		'days' => 'days',
		'dataTour' => 'dataTour', //Даты заездов. Предпочтительный формат: YYYY-MM-DD hh:mm:ss.
		'hotel_stars' => 'hotel_stars',
		'room' => 'room',
		'meal' => 'meal',
		'included' => 'included',
		'transport' => 'transport',
		'price_min' => 'price_min',
		'price_max' => 'price_max',
		'options' => 'options',
		'age-year' => 'age-year',
		'age-month' => 'age-month',
	);

	/**
	 * List's event ticket tags
	 * @var array
	 */
	public $aEventTicketTags = array(
		'place' => 'place',
		'hall' => 'hall',
		'hall_part' => 'hall_part',
		'date' => 'date', //Дата и время сеанса. Предпочтительный формат: YYYY-MM-DD hh:mm:ss.
		'is_premiere' => 'is_premiere',
		'is_kids' => 'is_kids',
		'age-year' => 'age-year',
		'age-month' => 'age-month',
	);

	/**
	 * Shop_Item_Controller
	 * @var Shop_Item_Controller
	 */
	protected $_Shop_Item_Controller = NULL;

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		$this->protocol = Core::httpsUses() ? 'https' : 'http';

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

		$this->_setShopItems();

		$this->_setShopGroups();

		$this->itemsProperties = $this->modifications = $this->deliveryOptions
			= $this->checkAvailable = TRUE;

		$this->recommended = $this->checkRest = $this->outlets = FALSE;

		$this->paymentMethod = array();

		$this->type = 'offer';
		$this->onStep = 500;

		$this->stdOut = new Core_Out_Std();

		$this->_Shop_Item_Controller = new Shop_Item_Controller();

		$this->mode = NULL;
		$this->token = '';
		$this->sno = 'OSN';

		Core_Session::close();
	}

	/**
	 * Prepare $this->Shop_Groups
	 * @return self
	 */
	protected function _setShopGroups()
	{
		$oShop = $this->getEntity();

		$this->_Shop_Groups = $oShop->Shop_Groups;
		$this->_Shop_Groups
			->queryBuilder()
			->where('shop_groups.siteuser_group_id', 'IN', $this->_aSiteuserGroups)
			//->where('shop_groups.active', '=', 1)
			->clearOrderBy()
			->orderBy('shop_groups.parent_id', 'ASC');

		return $this;
	}

	/**
	 * Add conditions for Shop_Item
	 * @param Shop_Item_Model $oShop_Item
	 * @return self
	 * @hostcms-event Shop_Controller_YandexMarket.onBeforeSelectShopItems
	 */
	protected function _addShopItemConditions($oShop_Item)
	{
		$dateTime = Core_Date::timestamp2sql(time());

		$oShop_Item
			->queryBuilder()
			->select('shop_items.*')
			->leftJoin('shop_groups', 'shop_groups.id', '=', 'shop_items.shop_group_id'/*,
				array(
						array('AND' => array('shop_groups.active', '=', 1)),
						array('OR' => array('shop_items.shop_group_id', '=', 0))
					)*/
			)
			// Активность группы или группа корневая
			->open()
				->where('shop_groups.active', '=', 1)
				->setOr()
				->where('shop_groups.id', 'IS', NULL)
			->close()
			->where('shop_items.shortcut_id', '=', 0)
			->where('shop_items.active', '=', 1)
			->where('shop_items.siteuser_id', 'IN', $this->_aSiteuserGroups)
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
			->where('shop_items.yandex_market', '=', 1)
			//->where('shop_items.price', '>', 0)
			->where('shop_items.modification_id', '=', 0);

		if ($this->checkRest)
		{
			$oShop_Item->queryBuilder()
				->join('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', 'shop_items.id')
				->groupBy('shop_items.id')
				->having(Core_QueryBuilder::expression('SUM(shop_warehouse_items.count)'), '>', 0);
		}

		Core_Event::notify(get_class($this) . '.onBeforeSelectShopItems', $this, array($oShop_Item));

		return $this;
	}

	/**
	 * Prepare $this->_Shop_Items
	 * @return self
	 */
	protected function _setShopItems()
	{
		$oShop = $this->getEntity();

		$this->_Shop_Items = $oShop->Shop_Items;
		$this->_Shop_Items
			->queryBuilder()
			->clearOrderBy()
			->orderBy('shop_items.id', 'ASC');

		$this->_addShopItemConditions($this->_Shop_Items);

		return $this;
	}

	/**
	 * Get items set
	 * @return Shop_Item_Model
	 */
	public function shopItems()
	{
		return $this->_Shop_Items;
	}

	/**
	 * Get groups set
	 * @return Shop_Item_Model
	 */
	public function shopGroups()
	{
		return $this->_Shop_Groups;
	}

	/**
	 * Show currencies
	 * @return self
	 */
	protected function _currencies()
	{
		$this->stdOut->write('<currencies>'. "\n");
		$aShop_Currencies = Core_Entity::factory('Shop_Currency')->findAll(FALSE);

		$aCurrenciesCodes = array(
			'RUR',
			'RUB',
			'USD',
			'BYR',
			'BYN',
			'KZT',
			'EUR',
			'UAH',
		);

		foreach ($aShop_Currencies as $oShop_Currency)
		{
			if (trim($oShop_Currency->code) != ''
			&& in_array($oShop_Currency->code, $aCurrenciesCodes))
			{
				$this->stdOut->write('<currency id="' . Core_Str::xml($oShop_Currency->code) .
					'" rate="' . Core_Str::xml($oShop_Currency->exchange_rate) .'"'. "/>\n");
			}
		}
		$this->stdOut->write('</currencies>'. "\n");

		return $this;
	}

	/**
	 * Cache of categories IDs
	 */
	protected $_aCategoriesId = array();

	/**
	 * get max group id
	 * @return int
	 */
	protected function _getMaxGroupId()
	{
		$oShop = $this->getEntity();

		$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
		$oCore_QueryBuilder_Select
			->from('shop_groups')
			->where('shop_groups.shop_id', '=', $oShop->id)
			->where('shop_groups.deleted', '=', 0);

		$aRow = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

		return $aRow['max_id'];
	}

	/**
	 * Show categories
	 * @return self
	 */
	protected function _categories()
	{
		$this->stdOut->write("<categories>\n");

		// Название магазина
		$oShop = $this->getEntity();

		//$this->stdOut->write('<category id="0">' . Core_Str::xml(!empty($oShop->yandex_market_name) ? $oShop->yandex_market_name : $oShop->Site->name) . "</category>\n");

		// Массив активных ID групп
		$this->_aCategoriesId = array();

		// Массив отключенных ID групп
		$aDisabledCategoriesId = array();

		$maxId = $this->_getMaxGroupId();

		$iFrom = 0;

		do {
			$this->_setShopGroups();
			$this->_Shop_Groups->queryBuilder()
				->where('shop_groups.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->onStep));

			$aShop_Groups = $this->_Shop_Groups->findAll(FALSE);
			foreach ($aShop_Groups as $oShop_Group)
			{
				if ($oShop_Group->active
					// Группа в корневой или в списке отключенных нет ее родителя
					&& ($oShop_Group->parent_id == 0 || !isset($aDisabledCategoriesId[$oShop_Group->parent_id]))
				)
				{
					$this->_aCategoriesId[$oShop_Group->id] = $oShop_Group->id;

					$group_parent_id = $oShop_Group->parent_id == '' || $oShop_Group->parent_id == 0
						? ''
						: ' parentId="' . $oShop_Group->parent_id . '"';

					$this->stdOut->write('<category id="' . $oShop_Group->id . '"' . $group_parent_id . '>' . Core_Str::xml($oShop_Group->name) . "</category>\n");
				}
				else
				{
					// Группа в отключенные если она сама отключена или родитель отключен
					$aDisabledCategoriesId[$oShop_Group->id] = $oShop_Group->id;
				}
			}

			$iFrom += $this->onStep;
		}
		while ($iFrom < $maxId);

		$this->stdOut->write("</categories>\n");

		unset($aShop_Groups);

		return $this;
	}

	/**
	 * Property_Model for <market_category>
	 * @var mixed
	 */
	protected $_MarketCategory = NULL;

	/**
	 * get max item id
	 * @return int
	 */
	protected function _getMaxId()
	{
		$oShop = $this->getEntity();

		$oCore_QueryBuilder_Select = Core_QueryBuilder::select(array('MAX(id)', 'max_id'));
		$oCore_QueryBuilder_Select
			->from('shop_items')
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_items.deleted', '=', 0);

		$aRow = $oCore_QueryBuilder_Select->execute()->asAssoc()->current();

		return $aRow['max_id'];
	}

	/**
	 * Show offers
	 * @return self
	 * @hostcms-event Shop_Controller_YandexMarket.onBeforeOffer
	 * @hostcms-event Shop_Controller_YandexMarket.onAfterOffer
	 */
	protected function _offers()
	{
		$this->stdOut->write("<offers>\n");

		//$offset = 0;

		$oShop = $this->getEntity();
		$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

		$this->_MarketCategory = $oShop_Item_Property_List->Properties->getByTag_name('market_category');

		$maxId = $this->_getMaxId();

		$iFrom = 0;

		do {
			$this->_setShopItems();
			$this->_Shop_Items->queryBuilder()
				->where('shop_items.id', 'BETWEEN', array($iFrom + 1, $iFrom + $this->onStep));

			$aShop_Items = $this->_Shop_Items->findAll(FALSE);

			foreach ($aShop_Items as $oShop_Item)
			{
				if (isset($this->_aCategoriesId[$oShop_Item->shop_group_id]))
				{
					if ($oShop_Item->price > 0)
					{
						$this->_showOffer($oShop_Item);
					}

					if ($this->modifications)
					{
						$iModificationOffset = 0;

						do {
							$oModifications = $oShop_Item->Modifications;
							$oModifications->queryBuilder()
								->where('shop_items.yandex_market', '=', 1)
								->clearOrderBy()
								->orderBy('shop_items.id', 'ASC')
								->offset($iModificationOffset)
								->limit($this->onStep);

							if ($this->checkRest)
							{
								$oModifications->queryBuilder()
									->select('shop_items.*')
									->join('shop_warehouse_items', 'shop_warehouse_items.shop_item_id', '=', 'shop_items.id')
									->groupBy('shop_items.id')
									->having(Core_QueryBuilder::expression('SUM(shop_warehouse_items.count)'), '>', 0);
							}

							$aModifications = $oModifications->findAll(FALSE);

							foreach ($aModifications as $oModification)
							{
								if ($oModification->price > 0)
								{
									$this->_showOffer($oModification);
								}
							}

							$iModificationOffset += $this->onStep;
						}
						while (count($aModifications) == $this->onStep);
					}
				}
			}

			//Core_File::flush();
			$iFrom += $this->onStep;
		}
		while ($iFrom < $maxId);

		$this->stdOut->write('</offers>'. "\n");

		return $this;
	}

	protected function _showOffer($oShop_Item)
	{
		$oShop = $this->getEntity();

		/* Устанавливаем атрибуты тега <offer>*/
		$tag_bid = $oShop_Item->yandex_market_bid
			? ' bid="' . Core_Str::xml($oShop_Item->yandex_market_bid) . '"'
			: '';

		$tag_cbid = $oShop_Item->yandex_market_cid
			? ' cbid="' . Core_Str::xml($oShop_Item->yandex_market_cid) . '"'
			: '';

		$available = !$this->checkAvailable || $oShop_Item->getRest() > 0 ? 'true' : 'false';

		$sType = $this->type != 'offer'
			? ' type="' . Core_Str::xml($this->type) . '"'
			: '';

		$this->stdOut->write('<offer id="' . $oShop_Item->id . '"'. $tag_bid . $tag_cbid . $sType . " available=\"{$available}\">\n");

		Core_Event::notify(get_class($this) . '.onBeforeOffer', $this, array($oShop_Item));

		/* URL */
		$this->stdOut->write('<url>' . Core_Str::xml($this->_shopPath . $oShop_Item->getPath()) . '</url>'. "\n");

		/* Определяем цену со скидкой */
		$aPrices = $this->_Shop_Item_Controller->calculatePriceInItemCurrency($oShop_Item->price, $oShop_Item);

		/* Цена */
		$this->stdOut->write('<price>' . $aPrices['price_discount'] . '</price>'. "\n");

		if ($aPrices['discount'] > 0)
		{
			/* Старая цена */
			$this->stdOut->write('<oldprice>' . ($aPrices['price'] + $aPrices['tax']) . '</oldprice>'. "\n");
		}

		/* CURRENCY */
		// Обязательно поле в модели:
		// (url?,buyurl?,price,wprice?,currencyId,xCategory?,categoryId+ ...
		$this->stdOut->write('<currencyId>'. Core_Str::xml($oShop_Item->Shop_Currency->code) . '</currencyId>'. "\n");

		/* Идентификатор категории */
		// Основной товар
		if ($oShop_Item->modification_id == 0)
		{
			$categoryId = $oShop_Item->shop_group_id;
		}
		else // Модификация, берем ID родительской группы
		{
			$categoryId = $oShop_Item->Modification->Shop_Group->id
				? $oShop_Item->Modification->Shop_Group->id
				: 0;
		}
		$this->stdOut->write('<categoryId>' . $categoryId . '</categoryId>'. "\n");

		if (!is_null($this->_MarketCategory))
		{
			$aProperty_Value_Market_Category = $this->_MarketCategory->getValues($oShop_Item->id);
			if (isset($aProperty_Value_Market_Category[0]))
			{
				$this->stdOut->write('<market_category>' .
					Core_Str::xml($aProperty_Value_Market_Category[0]->value) .
				'</market_category>'. "\n");
			}
		}

		/* PICTURE */
		if ($oShop_Item->image_large != '')
		{
			$this->stdOut->write('<picture>' . $this->protocol . '://' . Core_Str::xml($this->_siteAlias->name . $oShop_Item->getLargeFileHref()) . '</picture>'. "\n");
		}

		/* Delivery options */
		if ($this->deliveryOptions)
		{
			$this->stdOut->write('<store>' . ($oShop_Item->store == 1 ? 'true' : 'false') . '</store>'. "\n");
			$this->stdOut->write('<pickup>' . ($oShop_Item->pickup == 1 ? 'true' : 'false') . '</pickup>'. "\n");
			$this->stdOut->write('<delivery>' . ($oShop_Item->delivery == 1 ? 'true' : 'false') . '</delivery>'. "\n");

			$this->_deliveryOptions($oShop, $oShop_Item);
		}

		// (name, vendor?, vendorCode?)
		if (strlen($oShop_Item->name) > 0)
		{
			if ($this->type != 'vendor.model')
			{
				/* NAME */
				$this->stdOut->write('<name>' . Core_Str::xml($oShop_Item->name) . '</name>'. "\n");
			}

			if ($oShop_Item->shop_producer_id)
			{
				$this->stdOut->write('<vendor>' . Core_Str::xml($oShop_Item->Shop_Producer->name) . '</vendor>'. "\n");
			}

			if ($oShop_Item->vendorcode != '')
			{
				$this->stdOut->write('<vendorCode>' . Core_Str::xml($oShop_Item->vendorcode) . '</vendorCode>'. "\n");
			}
		}

		/* DESCRIPTION */
		$description = !empty($oShop_Item->description)
			? $oShop_Item->description
			: $oShop_Item->text;

		if (strlen($description))
		{
			$description = Core_Str::cutSentences(
				html_entity_decode(strip_tags($description), ENT_COMPAT, 'UTF-8'), 175
			);

			$this->stdOut->write('<description>' . Core_Str::xml($description) . '</description>'. "\n");
		}

		/* sales_notes */
		$sales_notes = mb_strlen($oShop_Item->yandex_market_sales_notes) > 0
			? $oShop_Item->yandex_market_sales_notes
			: $oShop->yandex_market_sales_notes_default;

		$this->stdOut->write('<sales_notes>' . Core_Str::xml(html_entity_decode(strip_tags($sales_notes), ENT_COMPAT, 'UTF-8')) . '</sales_notes>'. "\n");

		if ($oShop_Item->manufacturer_warranty)
		{
			$this->stdOut->write('<manufacturer_warranty>true</manufacturer_warranty>' . "\n");
		}

		if (trim($oShop_Item->country_of_origin) != '')
		{
			$this->stdOut->write('<country_of_origin>' . Core_Str::xml(html_entity_decode(strip_tags($oShop_Item->country_of_origin), ENT_COMPAT, 'UTF-8')) . '</country_of_origin>'. "\n");
		}

		// Элемент предназначен для обозначения товара, который можно скачать. Если указано значение параметра true, товарное предложение показывается во всех регионах независимо от регионов доставки, указанных магазином на странице Параметры размещения.
		if ($oShop_Item->type == 1)
		{
			$this->stdOut->write('<downloadable>true</downloadable>'. "\n");
		}

		/* adult */
		if ($oShop_Item->adult)
		{
			$this->stdOut->write('<adult>true</adult>' . "\n");
		}

		/* rec */
		if ($this->recommended)
		{
			$aTmp = array();

			$oItem_Associateds = $oShop_Item->Item_Associateds;
			$this->_addShopItemConditions($oItem_Associateds);

			$aItem_Associateds = $oItem_Associateds->findAll(FALSE);

			foreach ($aItem_Associateds as $oTmp_Shop_Item)
			{
				if ($oTmp_Shop_Item->price > 0)
				{
					$aTmp[] = $oTmp_Shop_Item->id;
				}
			}

			if (count($aTmp))
			{
				$this->stdOut->write('<rec>' . implode(',', $aTmp) . '</rec>'. "\n");
			}
		}

		$this->itemsProperties && $this->_addPropertyValue($oShop_Item);

		// outlets
		if (is_array($this->outlets) && count($this->outlets))
		{
			$aShop_Warehouse_Items = $oShop_Item->Shop_Warehouse_Items->getAllByShop_warehouse_id(array_keys($this->outlets), FALSE, 'IN');

			if (count($aShop_Warehouse_Items))
			{
				$this->stdOut->write('<outlets>' . "\n");

				foreach ($aShop_Warehouse_Items as $oShop_Warehouse_Item)
				{
					$this->stdOut->write('<outlet id="' . $this->outlets[$oShop_Warehouse_Item->shop_warehouse_id] . '" instock="' . intval($oShop_Warehouse_Item->count) . '" booking="false" />' . "\n");
				}

				$this->stdOut->write('</outlets>' . "\n");
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterOffer', $this, array($oShop_Item));

		$this->stdOut->write('</offer>'. "\n");
	}

	/**
	 * Show delivery options
	 * @param Shop_Model $oShop
	 * @param Shop_Item_Model $oShop_Item
	 * @return self
	 */
	protected function _deliveryOptions(Shop_Model $oShop, $oShop_Item = NULL)
	{
		$oShop_Item_Delivery_Options = $oShop->Shop_Item_Delivery_Options;

		$oShop_Item_Delivery_Options->queryBuilder()
			->where('shop_item_delivery_options.shop_item_id', '=', !is_null($oShop_Item) ? $oShop_Item->id : 0);

		$aShop_Item_Delivery_Options = $oShop_Item_Delivery_Options->findAll(FALSE);

		if (count($aShop_Item_Delivery_Options))
		{
			$this->stdOut->write('<delivery-options>');

			foreach ($aShop_Item_Delivery_Options as $oShop_Item_Delivery_Option)
			{
				$this->stdOut->write('<option cost="' . $oShop_Item_Delivery_Option->cost . '" days="' . $oShop_Item_Delivery_Option->day . '" order-before="' . $oShop_Item_Delivery_Option->order_before . '"/>' . "\n");
			}

			$this->stdOut->write('</delivery-options>');
		}

		return count($aShop_Item_Delivery_Options);
	}

	/**
	 * Допустимые значения возраста в годах
	 * @var array
	 */
	protected $_aAgeYears = array(0, 6, 12, 16, 18);

	/**
	 * Допустимые значения возраста в месяцах
	 * @var array
	 */
	protected $_aAgeMonthes = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

	/**
	 * Исключаемые теги
	 * @var array
	 */
	protected $_aForbid = array('age-month', 'age-year');

	/**
	 * Cache Properties
	 * @var array
	 */
	protected $_cacheProperties = array();

	/**
	 * Get Property_Model by ID
	 * @param int $property_id
	 */
	protected function _getProperty($property_id)
	{
		if (!isset($this->_cacheProperties[$property_id]))
		{
			$this->_cacheProperties[$property_id] = Core_Entity::factory('Property', $property_id);
		}

		return $this->_cacheProperties[$property_id];
	}

	/**
	 * Cache List Items
	 * @var array
	 */
	protected $_cacheListItems = array();

	/**
	 * Get List_Item value by ID
	 * @param int $property_id
	 */
	protected function _getCacheListItem($oProperty, $listItemId)
	{
		if (!isset($this->_cacheListItems[$oProperty->list_id][$listItemId]))
		{
			$this->_cacheListItems[$oProperty->list_id][$listItemId] = NULL;

			if ($listItemId)
			{
				$oList_Item = $oProperty->List->List_Items->getById(
					$listItemId/*, FALSE*/
				);

				!is_null($oList_Item)
					&& $this->_cacheListItems[$oProperty->list_id][$listItemId] = $oList_Item->value;
			}
		}

		return $this->_cacheListItems[$oProperty->list_id][$listItemId];
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

		$aProperty_Values = is_array($this->itemsProperties)
			? Property_Controller_Value::getPropertiesValues($this->itemsProperties, $oShop_Item->id, FALSE)
			: $oShop_Item->getPropertyValues(FALSE);

		$bAge = FALSE;

		foreach ($aProperty_Values as $oProperty_Value)
		{
			//$oProperty = $oProperty_Value->Property;
			$oProperty = $this->_getProperty($oProperty_Value->property_id);

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
					//$oList_Item = $oProperty->List->List_Items->getById(
					//	$oProperty_Value->value/*, FALSE*/
					//);

					//$value = !is_null($oList_Item)
					//	? $oList_Item->value
					//	: NULL;
					$value = $this->_getCacheListItem($oProperty, $oProperty_Value->value);
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

			if (!is_null($value))
			{
				$sTagName = 'param';

				$unit = $oProperty->type == 0 && $oProperty->Shop_Item_Property->shop_measure_id
					? ' unit="' . Core_Str::xml($oProperty->Shop_Item_Property->Shop_Measure->name) . '"'
					: '';

				$sAttr = ' name="' . Core_Str::xml($oProperty->name) . '"' . $unit;

				if ($this->type != 'offer')
				{
					switch ($this->type)
					{
						case 'vendor.model':
							$aTmpArray = $this->aVendorTags;
						break;
						case 'book':
							$aTmpArray = $this->aBookTags;
						break;
						case 'audiobook':
							$aTmpArray = $this->aAudiobookTags;
						break;
						case 'artist.title':
							$aTmpArray = $this->aArtistTitleTags;
						break;
						case 'tour':
							$aTmpArray = $this->aTourTags;
						break;
						case 'event-ticket':
							$aTmpArray = $this->aEventTicketTags;
						break;
						default:
							throw new Core_Exception("Wrong type '%type'",
								array('%type' => $this->type)
							);
					}

					if (isset($aTmpArray[$oProperty->tag_name]))
					{
						$sTagName = $aTmpArray[$oProperty->tag_name];
						$sAttr = '';
					}
				}

				if ($value !== '')
				{
					if (!in_array($sTagName, $this->_aForbid))
					{
						$this->stdOut->write('<' . $sTagName . $sAttr . '>'
							. Core_Str::xml(html_entity_decode(strip_tags($value), ENT_COMPAT, 'UTF-8'))
						. '</' . $sTagName . '>'. "\n");
					}
					elseif ($sTagName == 'age-year' && $value !== '' && in_array($value, $this->_aAgeYears))
					{
						$this->stdOut->write('<age unit="year">' . intval($value) . '</age>'. "\n");
						$bAge = TRUE;
					}
					elseif (!$bAge && $sTagName == 'age-month' && $value !== '' && in_array($value, $this->_aAgeMonthes))
					{
						$this->stdOut->write('<age unit="month">' . intval($value) . '</age>'. "\n");
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Parse URL and set controller properties
	 * @return self
	 * @hostcms-event Shop_Controller_YandexMarket.onBeforeParseUrl
	 * @hostcms-event Shop_Controller_YandexMarket.onAfterParseUrl
	 */
	public function parseUrl()
	{
		Core_Event::notify(get_class($this) . '.onBeforeParseUrl', $this);

		$action = Core_Array::getGet('action');

		$path = NULL;

		if (strlen($action))
		{
			$aParseUrl = parse_url($action);

			$path = $aParseUrl['path'];

			if (isset($aParseUrl['query']))
			{
				parse_str($aParseUrl['query'], $request);

				if ($this->token != Core_Array::get($request, 'auth-token'))
				{
					return $this->error404();
				}
			}
			else
			{
				return $this->error404();
			}
		}

		if ($path != '')
		{
			switch ($path)
			{
				case '/cart':
				case '/order/accept':
				case '/order/status':
				case '/order/shipment/status':
					$this->mode = $path;
				break;
				default:
					$this->error404();
			}
		}
		elseif (is_null($path))
		{
			$this->mode = 'show';
		}

		Core_Event::notify(get_class($this) . '.onAfterParseUrl', $this);

		return $this;
	}

	/**
	 * Define handler for 404 error
	 * @return self
	 */
	public function error404()
	{
		Core_Page::instance()->error404();

		return $this;
	}

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
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Controller_YandexMarket.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		switch ($this->mode)
		{
			case '/cart':
				$this->responseCart();
			break;
			case '/order/accept':
				$this->orderAccept();
			break;
			case '/order/status':
				$this->orderStatus();
			break;
			case '/order/shipment/status':
				$this->orderShipmentStatus();
			break;
			case 'show':
			default:
				$this->showYml();
			break;
		}
	}

	/**
	 * Response cart
	 * @return array
	 */
	public function responseCart()
	{
		$body = file_get_contents('php://input');

		$aResponse = json_decode($body, TRUE);

		$aAnswer = array();

		$oShop = $this->getEntity();

		$aVat = array(
			0 => 'NO_VAT',
			10 => 'VAT_10',
			18 => 'VAT_18',
		);

		if (isset($aResponse['cart']))
		{
			$sCurrency = $oShop->Shop_Currency->code == 'RUB'
				? 'RUR'
				: $oShop->Shop_Currency->code;

			$aAnswer['cart']['deliveryCurrency'] = strval($sCurrency);
			$aAnswer['cart']['taxSystem'] = strval($this->sno);

			isset($aResponse['cart']['delivery']['address']['postcode'])
				&& $postcode = $aResponse['cart']['delivery']['address']['postcode'];

			$aRegion = isset($aResponse['cart']['delivery']['region'])
				? $this->_getRegion($aResponse['cart']['delivery']['region'])
				: array();

			$aDeliveries = array();

			if (count($aRegion))
			{
				$oShop_Country = Core_Entity::factory('Shop_Country')->getByName($aRegion[0]);

				if (!is_null($oShop_Country))
				{
					$city = end($aRegion);

					$oShop_Country_Location_Cities = Core_Entity::factory('Shop_Country_Location_City');
					$oShop_Country_Location_Cities->queryBuilder()
						->select('shop_country_location_cities.*')
						->join('shop_country_locations', 'shop_country_locations.id', '=', 'shop_country_location_cities.shop_country_location_id')
						->where('shop_country_locations.shop_country_id', '=', $oShop_Country->id)
						->limit(1);

					$oShop_Country_Location_City = $oShop_Country_Location_Cities->getByName($city);

					if ($oShop_Country_Location_City)
					{
						$oShop_Delivery_Controller_Show = new Shop_Delivery_Controller_Show($oShop);
						$oShop_Delivery_Controller_Show
							->shop_country_id($oShop_Country->id)
							->shop_country_location_id($oShop_Country_Location_City->shop_country_location_id)
							->shop_country_location_city_id($oShop_Country_Location_City->id)
							->setUp();

						// Выбираем все типы доставки для данного магазина
						$aShop_Deliveries = $oShop->Shop_Deliveries->getAllByActive(1);

						$aDeliveryMethods = array(
							0 => 'PICKUP',
							1 => 'POST',
							2 => 'DELIVERY',
						);

						foreach ($aShop_Deliveries as $oShop_Delivery)
						{
							$aShop_Delivery_Conditions = $oShop_Delivery_Controller_Show->getShopDeliveryConditions($oShop_Delivery);

							foreach ($aShop_Delivery_Conditions as $key => $object)
							{
								// Не самовывоз или заданы outlets
								if ($aDeliveryMethods[$oShop_Delivery->method] != 'PICKUP' || $this->outlets)
								{
									$aTmpDelivery = array(
										'id' => $object->id,
										'price' => floatval($object->price),
										'paymentAllow' => FALSE,
										'type' => $aDeliveryMethods[$oShop_Delivery->method],
										'serviceName' => $object->name,
										'vat' => isset($object->shop_tax_id)
											? Core_Array::get($aVat, $object->Shop_Tax->rate, 'NO_VAT')
											: 'NO_VAT',
										/*'paymentMethods' => array(
										  "CARD_ON_DELIVERY",
										  "CASH_ON_DELIVERY",
										  "YANDEX" // Предоплата
										)*/
									);

									if ($aDeliveryMethods[$oShop_Delivery->method] == 'PICKUP')
									{
										$aShop_Warehouses = $oShop->Shop_Warehouses->getAllById(array_keys($this->outlets), FALSE, 'IN');

										foreach ($aShop_Warehouses as $oShop_Warehouse)
										{
											$aTmpDelivery['outlets'][] = array(
												'id' => intval($this->outlets[$oShop_Warehouse->id])
											);
										}
									}

									$aTmpDelivery['dates'] = array(
										'fromDate' => date('d-m-Y', strtotime('+' . $oShop_Delivery->days_from . ' day')),
										'toDate' => date('d-m-Y', strtotime('+' . $oShop_Delivery->days_to . ' day'))
									);

									$aDeliveries[] = $aTmpDelivery;
								}
							}
						}

						$aAnswer['cart']['deliveryOptions'] = $aDeliveries;
					}
					else
					{
						Core_Log::instance()->clear()
							->status(Core_Log::$ERROR)
							->write('Error: YML /cart: Can\'t find address ' . implode(', ', $aRegion));
					}
				}
			}

			if (isset($aResponse['cart']['items']))
			{
				foreach ($aResponse['cart']['items'] as $aItem)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->getById($aItem['offerId']);

					if (!is_null($oShop_Item))
					{
						$aShop_Warehouse_Items = $oShop_Item->Shop_Warehouse_Items->findAll(FALSE);

						$count = 0;

						foreach ($aShop_Warehouse_Items as $oShop_Warehouse_Item)
						{
							$count += $oShop_Warehouse_Item->count;
						}

						$aAnswer['cart']['items'][] = array(
							'count' => $count,
							'delivery' => count($aDeliveries) > 0,
							'feedId' => $aItem['feedId'],
							'offerId' => strval($oShop_Item->id),
							'price' => floatval($oShop_Item->price),
							'vat' => Core_Array::get($aVat, $oShop_Item->Shop_Tax->rate, 'NO_VAT')
						);
					}
				}
			}

			$aAnswer['cart']['paymentMethods'] = array(
			  "CARD_ON_DELIVERY",
			  "CASH_ON_DELIVERY",
			  // "YANDEX" // Предоплата
			);
		}

		Core::showJson($aAnswer);
	}

	/**
	 * Get region
	 * @param array $aParam
	 * @return array
	 */
	protected function _getRegion(array $aParam)
	{
		$aReturn = array($aParam['name']);

		isset($aParam['parent'])
			&& $aReturn = array_merge($this->_getRegion($aParam['parent']), $aReturn);

		return $aReturn;
	}

	/**
	 * Order accept
	 * @return array
	 */
	public function orderAccept()
	{
		$body = file_get_contents('php://input');

		$aResponse = json_decode($body, TRUE);

		$aAnswer = array();

		if (isset($aResponse['order']))
		{
			$oShop_Order = $this->createOrder($aResponse['order']);

			if(!is_null($oShop_Order->id))
			{
				$aAnswer['order'] = array(
					"accepted" => TRUE,
					"id" => strval($oShop_Order->id)
				);
			}
		}

		Core::showJson($aAnswer);
	}

	/**
	 * Order status
	 * @return self
	 */
	public function orderStatus()
	{
		$body = file_get_contents('php://input');

		$aResponse = json_decode($body, TRUE);

		if (isset($aResponse['order']['status']))
		{
			$oShop_Order = Core_Entity::factory('Shop_Order')->getBySystem_information(intval($aResponse['order']['id']));

			if (!is_null($oShop_Order))
			{
				$this->updateOrder($oShop_Order, $aResponse['order']);
			}
		}

		return $this;
	}

	/**
	 * Create order
	 * @param array $aOrderParams
	 * @return object
	 */
	public function createOrder(array $aOrderParams)
	{
		$oShop = $this->getEntity();

		$oShop_Order = Core_Entity::factory('Shop_Order');
		$oShop_Order
			->shop_id($oShop->id)
			->shop_currency_id($oShop->shop_currency_id);

		$aRegion = isset($aOrderParams['delivery']['region'])
			? $this->_getRegion($aOrderParams['delivery']['region'])
			: array();

		if (count($aRegion))
		{
			$oShop_Country = Core_Entity::factory('Shop_Country')->getByName($aRegion[0]);

			if (!is_null($oShop_Country))
			{
				$city = end($aRegion);

				$oShop_Country_Location_Cities = Core_Entity::factory('Shop_Country_Location_City');
				$oShop_Country_Location_Cities->queryBuilder()
					->select('shop_country_location_cities.*')
					->join('shop_country_locations', 'shop_country_locations.id', '=', 'shop_country_location_cities.shop_country_location_id')
					->where('shop_country_locations.shop_country_id', '=', $oShop_Country->id)
					->limit(1);

				$oShop_Country_Location_City = $oShop_Country_Location_Cities->getByName($city);

				if ($oShop_Country_Location_City)
				{
					$oShop_Order
						->shop_country_id($oShop_Country->id)
						->shop_country_location_id($oShop_Country_Location_City->shop_country_location_id)
						->shop_country_location_city_id($oShop_Country_Location_City->id);
				}
			}
		}

		$oShop_Order->system_information = intval($aOrderParams['id']);
		$oShop_Order->shop_payment_system_id = Core_Array::get($this->paymentMethod, $aOrderParams['paymentMethod'], 0);

		$oShop_Order->save();

		if (count($aOrderParams['items']))
		{
			foreach ($aOrderParams['items'] as $orderItem)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->find($orderItem['offerId']);

				if (!is_null($oShop_Item))
				{
					$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
					$oShop_Order_Item
						->shop_item_id($orderItem['offerId'])
						->quantity($orderItem['count']);

					$amountPurchaseDiscount = $amount = 0;

					// Prices
					$oShop_Item_Controller = new Shop_Item_Controller();

					Core::moduleIsActive('siteuser') && $oSiteuser
						&& $oShop_Item_Controller->siteuser($oSiteuser);

					$oShop_Item_Controller->count($orderItem['count']);

					$aPrices = $oShop_Item_Controller->getPrices($oShop_Item, TRUE);

					$amount += $aPrices['price_discount'] * $orderItem['count'];

					// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
					for ($i = 0; $i < $orderItem['count']; $i++)
					{
						$aDiscountPrices[] = $aPrices['price_discount'];
					}

					// Сумма для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $amountPurchaseDiscount += $aPrices['price_discount'] * $orderItem['count'];

					$oShop_Order_Item->price = $aPrices['price_discount'] - $aPrices['tax'];
					$oShop_Order_Item->rate = $aPrices['rate'];
					$oShop_Order_Item->name = $oShop_Item->name;
					$oShop_Order_Item->type = 0;
					$oShop_Order_Item->marking = $oShop_Item->marking;

					$oShop_Order->add($oShop_Order_Item);
				}
			}
		}

		$oShop_Order->invoice = $oShop_Order->id;
		$oShop_Order->save();

		return $oShop_Order;
	}

	/**
	 * Update order
	 * @param Shop_Order_Model $oShop_Order
	 * @param array $aOrderParams
	 * @return array
	 */
	public function updateOrder(Shop_Order_Model $oShop_Order, array $aOrderParams)
	{
		switch($aOrderParams['status'])
		{
			case 'CANCELLED':
				$oShop_Order->canceled = 1;
				$oShop_Order->save();
			break;
			case 'DELIVERED':
				$oShop_Order->paid = 1;
				$oShop_Order->save();
			break;
		}

		// Информация о покупателе
		if (isset($aOrderParams['buyer']))
		{
			$oShop_Order
				->name(isset($aOrderParams['buyer']['firstName']) ? $aOrderParams['buyer']['firstName'] : '')
				->surname(isset($aOrderParams['buyer']['lastName']) ? $aOrderParams['buyer']['lastName'] : '')
				->patronymic(isset($aOrderParams['buyer']['middleName']) ? $aOrderParams['buyer']['middleName'] : '')
				->email($aOrderParams['buyer']['email'])
				->phone($aOrderParams['buyer']['phone']);
		}

		$oShop_Order->save();

		Core::showJson('OK');
	}

	/**
	 * Order shipment status
	 * @return array
	 */
	public function orderShipmentStatus()
	{
		Core::showJson('OK');
	}

	/**
	 * Show UML built data
	 * @return self
	 * @hostcms-event Shop_Controller_YandexMarket.onBeforeRedeclaredShowYml
	 */
	public function showYml()
	{
		// Stop buffering
		ob_get_clean();
		header('Content-Type: raw/data');
		header("Cache-Control: no-cache, must-revalidate");
		header('X-Accel-Buffering: no');

		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShowYml', $this);

		$this->stdOut->open();

		$oShop = $this->getEntity();
		$oSite = $oShop->Site;

		!is_null(Core_Page::instance()->response) && Core_Page::instance()->response
			->header('Content-Type', "text/xml; charset={$oSite->coding}")
			->sendHeaders();

		$this->stdOut->write('<?xml version="1.0" encoding="' . $oSite->coding . '"?>' . "\n");
		$this->stdOut->write('<!DOCTYPE yml_catalog SYSTEM "shops.dtd">' . "\n");
		$this->stdOut->write('<yml_catalog date="' . date("Y-m-d H:i") . '">' . "\n");
		$this->stdOut->write("<shop>\n");

		// Название магазина
		$shop_name = trim(
			!empty($oShop->yandex_market_name)
				? $oShop->yandex_market_name
				: $oSite->name
		);

		$this->stdOut->write("<name>" . Core_Str::xml(mb_substr($shop_name, 0, 20)) . "</name>\n");

		// Название компании.
		$this->stdOut->write("<company>" . Core_Str::xml($oShop->Shop_Company->name) . "</company>\n");

		$this->_siteAlias = $oSite->getCurrentAlias();
		$this->_shopPath = $this->protocol . '://' . $this->_siteAlias->name . $oShop->Structure->getPath();

		$this->stdOut->write("<url>" . Core_Str::xml($this->_shopPath) . "</url>\n");
		$this->stdOut->write("<platform>HostCMS</platform>\n");
		$this->stdOut->write("<version>" . Core_Str::xml(CURRENT_VERSION) . "</version>\n");

		/* Валюты */
		$this->_currencies();

		/* Категории */
		$this->_categories();

		/* Delivery options */
		$this->deliveryOptions
			// Disable if there aren't shop's delivery options
			&& $this->deliveryOptions = $this->_deliveryOptions($oShop) > 0;

		//Core_File::flush();

		/* adult */
		if ($oShop->adult)
		{
			$this->stdOut->write('<adult>true</adult>' . "\n");
		}

		/* Товары */
		$this->_offers();

		$this->stdOut->write("</shop>\n");
		$this->stdOut->write('</yml_catalog>');

		$this->stdOut->close();

		//Core_File::flush();
	}
}