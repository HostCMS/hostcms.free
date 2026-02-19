<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ корзины магазина.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - sortPropertiesValues(TRUE|FALSE) сортировать значения дополнительных свойств, по умолчанию TRUE.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE
 * - orderProperties(TRUE|FALSE|array()) выводить список дополнительных свойств заказа, по умолчанию FALSE.
 * - itemsMedia(TRUE|FALSE) выводить значения библиотеки файлов для товаров, по умолчанию FALSE
 * - modifications(TRUE|FALSE) показывать модификации для выбранных товаров, по умолчанию FALSE
 * - itemsForbiddenTags(array('description')) массив тегов товаров, запрещенных к передаче в генерируемый XML
 * - warehousesItems(TRUE|FALSE) выводить остаток на каждом складе для товара, по умолчанию TRUE
 * - taxes(TRUE|FALSE) выводить список налогов, по умолчанию FALSE
 * - specialprices(TRUE|FALSE) показывать специальные цены для выбранных товаров, по умолчанию FALSE
 * - associatedItems(TRUE|FALSE) показывать сопутствующие товары для выбранных товаров, по умолчанию FALSE
 * - favorite(TRUE|FALSE) выводить избранные товары, по умолчанию FALSE
 * - favoriteLimit(10) максимальное количество выводимых избранных товаров, по умолчанию 10
 * - favoriteOrder('ASC'|'DESC'|'RAND') направление сортировки избранных товаров, по умолчанию RAND
 * - viewed(TRUE|FALSE) выводить просмотренные товары, по умолчанию FALSE
 * - viewedLimit(10) максимальное количество выводимых просмотренных товаров, по умолчанию 10
 * - viewedOrder('ASC'|'DESC'|'RAND') направление сортировки просмотренных товаров, по умолчанию DESC
 * - calculateCounts(TRUE|FALSE) вычислять общее количество товаров и групп в корневой группе, по умолчанию FALSE
 * - applyDiscounts(TRUE|FALSE) применять скидки от суммы заказа, по умолчанию TRUE
 * - applyDiscountCards(TRUE|FALSE) применять дисконтные карты, по умолчанию TRUE
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 *
 * Доступные свойства:
 *
 * - amount сумма заказа с учетом скидок
 * - tax сумма налога
 * - quantity количество товаров в корзине
 * - weight вес товаров в корзине
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/shop' Магазин
 * - '/shop/shop_order_properties/property' Свойство в списке свойств заказов
 * - '/shop/shop_order_properties/property_dir' Раздел свойств в списке свойств заказов
 * - '/shop/shop_item_properties/property' Свойство в списке свойств товара
 * - '/shop/shop_item_properties/property_dir' Раздел свойств в списке свойств товара
 * - '/shop/favorite/shop_item' Избранные товары, если не указаны, используются правила для '/shop/shop_item'
 * - '/shop/viewed/shop_item' Просмотренные товары, если не указаны, используются правила для '/shop/shop_item'
 * - '/shop/shop_cart' Корзина магазина
 *
 * <code>
 * $Shop_Cart_Controller_Show = new Shop_Cart_Controller_Show(
 * 		Core_Entity::factory('Shop', 1)
 * 	);
 *
 * 	$Shop_Cart_Controller_Show
 * 		->xsl(
 * 			Core_Entity::factory('Xsl')->getByName('МагазинКорзина')
 * 		)
 * 		->show();
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Cart_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'couponText',
		'itemsProperties',
		'itemsPropertiesList',
		'sortPropertiesValues',
		'orderProperties',
		'itemsMedia',
		'modifications',
		'itemsForbiddenTags',
		'warehousesItems',
		'taxes',
		'specialprices',
		'associatedItems',
		'favorite',
		'favoriteLimit',
		'favoriteOrder',
		'viewed',
		'viewedLimit',
		'viewedOrder',
		'cartUrl',
		'amount',
		'tax',
		'quantity',
		'weight',
		'volume',
		'packageWeight',
		'packageVolume',
		'calculateCounts',
		'applyDiscounts',
		'applyDiscountCards',
	);

	/**
	 * List of properties for item
	 * @var array
	 */
	protected $_aItem_Properties = array();

	/**
	 * List of property directories for item
	 * @var array
	 */
	protected $_aItem_Property_Dirs = array();

	/**
	 * Get _aItem_Properties set
	 * @return array
	 */
	public function getItemProperties()
	{
		return $this->_aItem_Properties;
	}

	/**
	 * Get _aItem_Property_Dirs set
	 * @return array
	 */
	public function getItemPropertyDirs()
	{
		return $this->_aItem_Property_Dirs;
	}

	/**
	 * Current Siteuser
	 * @var Siteuser_Model|NULL
	 */
	protected $_oSiteuser = NULL;

	/**
	 * @var array
	 */
	protected $_aDiscountPrices = array();

	/**
	 * List of properties for order
	 * @var array
	 */
	protected $_aOrder_Properties = array();

	/**
	 * List of property directories for order
	 * @var array
	 */
	protected $_aOrder_Property_Dirs = array();

	/**
	 * Constructor.
	 * @param Shop_Model $oShop shop
	 */
	public function __construct(Shop_Model $oShop)
	{
		parent::__construct($oShop->clearEntities());

		if (Core::moduleIsActive('siteuser'))
		{
			// Если есть модуль пользователей сайта, $siteuser_id равен 0 или ID авторизованного
			$this->_oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			if ($this->_oSiteuser)
			{
				// Move goods from cookies to session
				$Shop_Cart_Controller = $this->_getCartController();
				$Shop_Cart_Controller->moveTemporaryCart($oShop);
			}
		}

		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_id')
				->value($this->_oSiteuser ? $this->_oSiteuser->id : 0)
		);

		$this->itemsProperties = $this->taxes = $this->specialprices
			= $this->calculateCounts = $this->associatedItems = $this->favorite = $this->viewed
			= $this->orderProperties = $this->modifications = $this->itemsMedia = FALSE;

		$this->itemsPropertiesList = $this->warehousesItems
			= $this->applyDiscounts = $this->applyDiscountCards = $this->sortPropertiesValues = TRUE;

		$this->itemsForbiddenTags = array();

		$this->viewedLimit = $this->favoriteLimit = 10;

		$this->favoriteOrder = 'RAND';
		$this->viewedOrder = 'DESC';

		$this->cartUrl = $oShop->Structure->getPath() . 'cart/';

		if (Core_Session::hasSessionId())
		{
			Core_Session::start();
			if (isset($_SESSION['hostcmsOrder']['coupon_text']))
			{
				$this->addCacheSignature('coupon=' . $_SESSION['hostcmsOrder']['coupon_text']);

				Shop_Item_Controller::coupon($_SESSION['hostcmsOrder']['coupon_text']);
			}
		}
	}

	/**
	 * Get Shop_Cart_Controller
	 * @return object
     */
	protected function _getCartController()
	{
		return Shop_Cart_Controller::instance();
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Cart_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$bTpl = $this->_mode == 'tpl';

		$oShop = $this->getEntity();

		$hasSessionId = Core_Session::hasSessionId();

		// Comparing, favorite and viewed goods
		if ($hasSessionId)
		{
			// Favorite goods
			$this->favorite && $this->_addFavorite();

			// Viewed goods
			$this->viewed && $this->_addViewed();
		}

		if (Core::moduleIsActive('property') && $this->orderProperties)
		{
			$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $oShop->id);

			$aProperties = $oShop_Order_Property_List->Properties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$oProperty->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_order_properties/property', $oProperty);
				$this->_aOrder_Properties[$oProperty->property_dir_id][] = $oProperty;

				$oShop_Order_Property = $oProperty->Shop_Order_Property;
				$oProperty
					->addEntity(
						Core::factory('Core_Xml_Entity')->name('prefix')->value($oShop_Order_Property->prefix)
					)
					->addEntity(
						Core::factory('Core_Xml_Entity')->name('display')->value($oShop_Order_Property->display)
					);
			}

			$aProperty_Dirs = $oShop_Order_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_order_properties/property_dir', $oProperty_Dir);
				$this->_aOrder_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			// Список свойств товаров
			$Shop_Order_Properties = Core::factory('Core_Xml_Entity')
				->name('shop_order_properties');

			$this->addEntity($Shop_Order_Properties);

			$this->_addOrdersPropertiesList(0, $Shop_Order_Properties);
		}

		$oShop->showXmlCounts($this->calculateCounts);

		// Coupon text
		!is_null($this->couponText) && $this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('coupon_text')
				->value($this->couponText)
		);

		//Активность модуля "Пользователи сайта"
		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('siteuser_exists')
				->value(Core::moduleIsActive('siteuser') ? 1 : 0)
		);

		if ($bTpl)
		{
			$this->assign('controller', $this);
			$this->assign('aShop_Carts', array());
			$this->assign('aShop_Purchase_Discounts', array());
			$this->assign('aShop_Discountcards', array());
		}

		// Список свойств товаров
		if (Core::moduleIsActive('property') && $this->itemsPropertiesList)
		{
			$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

			$aProperties = is_array($this->itemsPropertiesList) && count($this->itemsPropertiesList)
					? $oShop_Item_Property_List->Properties->getAllByid($this->itemsPropertiesList, FALSE, 'IN')
					: $oShop_Item_Property_List->Properties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$oProperty->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_item_properties/property', $oProperty);
				$this->_aItem_Properties[$oProperty->property_dir_id][] = $oProperty;

				$oShop_Item_Property = $oProperty->Shop_Item_Property;

				$oShop_Item_Property->shop_measure_id && $oProperty->addEntity(
					$oShop_Item_Property->Shop_Measure
				);
			}

			$aProperty_Dirs = $oShop_Item_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->applyForbiddenAllowedTags('/shop/shop_item_properties/property_dir', $oProperty_Dir);
				$this->_aItem_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
			}

			if (!$bTpl)
			{
				$Shop_Item_Properties = Core::factory('Core_Xml_Entity')
					->name('shop_item_properties');

				$this->addEntity($Shop_Item_Properties);

				$this->_addItemsPropertiesList(0, $Shop_Item_Properties);
			}
		}

		/*$quantityPurchaseDiscount = $amountPurchaseDiscount = */
		$this->quantity = $this->amount = $this->tax = $this->weight = $this->volume = $this->packageWeight = $this->packageVolume = 0;

		// Массив цен для расчета скидок каждый N-й со скидкой N%
		//$this->_aDiscountPrices = array();

		// Есть скидки на N-й товар, доступные для текущей даты
		//$bPositionDiscount = $oShop->Shop_Purchase_Discounts->checkAvailableWithPosition();

		$Shop_Cart_Controller = $this->_getCartController();

		$aShop_Carts = $Shop_Cart_Controller->getAll($oShop);
		foreach ($aShop_Carts as $oShop_Cart)
		{
			if (!$bTpl)
			{
				$oShop_Cart
					->clearEntities()
					->showXmlWarehousesItems($this->warehousesItems)
					->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues)
					->showXmlSpecialprices($this->specialprices)
					->showXmlAssociatedItems($this->associatedItems)
					->setItemsForbiddenTags($this->itemsForbiddenTags)
					->showXmlModifications($this->modifications)
					->showXmlMedia($this->itemsMedia);

				$this->applyForbiddenAllowedTags('/shop/shop_cart', $oShop_Cart);

				$this->addEntity($oShop_Cart);
			}
			else
			{
				$this->append('aShop_Carts', $oShop_Cart);
			}
		}

		$this->tax = $Shop_Cart_Controller->totalTax;
		$this->weight = $Shop_Cart_Controller->totalWeight;
		$this->volume = $Shop_Cart_Controller->totalVolume;
		$this->packageWeight = $Shop_Cart_Controller->totalPackageWeight;
		$this->packageVolume = $Shop_Cart_Controller->totalPackageVolume;
		$this->amount = $Shop_Cart_Controller->totalAmount;
		$this->quantity = $Shop_Cart_Controller->totalQuantity;
		// Массив цен для расчета скидок каждый N-й со скидкой N%
		$this->_aDiscountPrices = $Shop_Cart_Controller->totalDiscountPrices;

		$this->taxes && $oShop->showXmlTaxes(TRUE);

		$fAppliedDiscountsAmount = $this->_getDiscoutAmount($Shop_Cart_Controller->totalQuantityForPurchaseDiscount, $Shop_Cart_Controller->totalAmountForPurchaseDiscount);

		// Скидка больше суммы заказа
		$fAppliedDiscountsAmount > $this->amount
			&& $fAppliedDiscountsAmount = $this->amount;

		// Применяем скидку от суммы заказа
		$this->amount -= $fAppliedDiscountsAmount;

		// Бонусы
		if ($this->_oSiteuser)
		{
			$aSiteuserBonuses = $this->_oSiteuser->getBonuses($oShop);

			$max_bonus = Shop_Controller::instance()->round($this->amount * ($oShop->max_bonus / 100));

			$available_bonuses = $aSiteuserBonuses['total'] <= $max_bonus
				? $aSiteuserBonuses['total']
				: $max_bonus;

			$aSiteuserBonuses['total'] && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('siteuser_bonuses')
					->value($aSiteuserBonuses['total'])
			)->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('available_bonuses')
					->value($available_bonuses)
			);

			// Применяемые бонусы
			if (isset($_SESSION['hostcmsOrder']['bonuses']) && $_SESSION['hostcmsOrder']['bonuses'] > 0)
			{
				if ($_SESSION['hostcmsOrder']['bonuses'] > $available_bonuses)
				{
					$_SESSION['hostcmsOrder']['bonuses'] = $available_bonuses;
				}

				$this->addEntity(
					Core::factory('Core_Xml_Entity')
						->name('apply_bonuses')
						->value($_SESSION['hostcmsOrder']['bonuses'])
				);

				// Вычитаем бонусы
				$this->amount -= $_SESSION['hostcmsOrder']['bonuses'];
			}
		}

		// Total order amount
		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_amount')
				->value($this->amount)
				->addAttribute('formatted', $oShop->Shop_Currency->format($this->amount))
				->addAttribute('formattedWithCurrency', $oShop->Shop_Currency->formatWithCurrency($this->amount))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_discount')
				->value($fAppliedDiscountsAmount)
				->addAttribute('formatted', $oShop->Shop_Currency->format($fAppliedDiscountsAmount))
				->addAttribute('formattedWithCurrency', $oShop->Shop_Currency->formatWithCurrency($fAppliedDiscountsAmount))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_tax')
				->value($this->tax)
				->addAttribute('formatted', $oShop->Shop_Currency->format($this->tax))
				->addAttribute('formattedWithCurrency', $oShop->Shop_Currency->formatWithCurrency($this->tax))
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_quantity')
				->value($this->quantity)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_weight')
				->value($this->weight)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_volume')
				->value($this->volume)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_package_weight')
				->value($this->packageWeight)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_package_volume')
				->value($this->packageVolume)
		);

		return parent::show();
	}

	/**
	 * Add favorite goods
	 * @return self
	 * @hostcms-event Shop_Cart_Controller_Show.onBeforeAddFavoriteEntity
	 */
	protected function _addFavorite()
	{
		$oShop = $this->getEntity();

		$aFavorite = array();

		$aShop_Favorites = Shop_Favorite_Controller::instance()->getAll($oShop);
		foreach ($aShop_Favorites as $oShop_Favorite)
		{
			$aFavorite[] = $oShop_Favorite->shop_item_id;
		}

		if (count($aFavorite))
		{
			switch ($this->favoriteOrder)
			{
				case 'RAND':
					shuffle($aFavorite);
				break;
				case 'ASC':
					asort($aFavorite);
				break;
				case 'DESC':
					arsort($aFavorite);
				break;
				default:
					throw new Core_Exception("The favoriteOrder direction '%direction' doesn't allow",
						array('%direction' => $this->favoriteOrder)
					);
			}

			// Extract a slice of the array
			$aFavorite = array_slice($aFavorite, 0, $this->favoriteLimit);

			if ($this->_mode != 'tpl')
			{
				$this->addEntity(
					$oFavouriteEntity = Core::factory('Core_Xml_Entity')
						->name('favorite')
				);

				foreach ($aFavorite as $shop_item_id)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id, FALSE);
					if (!is_null($oShop_Item->id))
					{
						$oFavorite_Shop_Item = clone $oShop_Item;
						$oFavorite_Shop_Item
							->id($oShop_Item->id)
							->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues)
							->showXmlSpecialprices($this->specialprices);

						// Media
						$this->itemsMedia
							&& $oFavorite_Shop_Item->showXmlMedia($this->itemsMedia);

						//$this->applyItemsForbiddenTags($oFavorite_Shop_Item);
						$this->applyForbiddenAllowedTags('/shop/favorite/shop_item|/shop/shop_item', $oFavorite_Shop_Item);

						Core_Event::notify(get_class($this) . '.onBeforeAddFavoriteEntity', $this, array($oFavorite_Shop_Item));

						$oFavouriteEntity->addEntity($oFavorite_Shop_Item);
					}
				}
			}
			else
			{
				$this->append('aFavorite', $aFavorite);
			}
		}

		return $this;
	}

	/**
	 * Add viewed goods
	 * @return self
	 * @hostcms-event Shop_Cart_Controller_Show.onBeforeAddViewedEntity
	 */
	protected function _addViewed()
	{
		$oShop = $this->getEntity();

		$aViewed = Core_Array::get(Core_Array::getSession('hostcmsViewed', array()), $oShop->id, array());

		if (count($aViewed))
		{
			switch ($this->viewedOrder)
			{
				case 'RAND':
					shuffle($aViewed);
				break;
				case 'ASC':
					ksort($aViewed);
				break;
				case 'DESC':
					krsort($aViewed);
				break;
				default:
					throw new Core_Exception("The viewedOrder direction '%direction' doesn't allow",
						array('%direction' => $this->viewedOrder)
					);
			}

			// Extract a slice of the array
			$aViewed = array_slice($aViewed, 0, $this->viewedLimit);

			if ($this->_mode != 'tpl')
			{
				$this->addEntity(
					$oViewedEntity = Core::factory('Core_Xml_Entity')
						->name('viewed')
				);

				foreach ($aViewed as $view_item_id)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->find($view_item_id, FALSE);

					if (!is_null($oShop_Item->id) /*&& $oShop_Item->id != $this->item*/ && $oShop_Item->active)
					{
						$oViewed_Shop_Item = clone $oShop_Item;
						$oViewed_Shop_Item
							->id($oShop_Item->id)
							->showXmlProperties($this->itemsProperties, $this->sortPropertiesValues)
							->showXmlModifications($this->modifications)
							->showXmlSpecialprices($this->specialprices);

						//$this->applyItemsForbiddenTags($oViewed_Shop_Item);
						$this->applyForbiddenAllowedTags('/shop/viewed/shop_item|/shop/shop_item', $oViewed_Shop_Item);

						// Media
						$this->itemsMedia
							&& $oViewed_Shop_Item->showXmlMedia($this->itemsMedia);

						Core_Event::notify(get_class($this) . '.onBeforeAddViewedEntity', $this, array($oViewed_Shop_Item));

						$oViewedEntity->addEntity($oViewed_Shop_Item);
					}
				}
			}
			else
			{
				$this->append('aViewed', $aViewed);
			}
		}

		return $this;
	}

	/**
	 * Get Discount Amount
	 * @return decimal
	 */
	protected function _getDiscoutAmount($quantityPurchaseDiscount, $amountPurchaseDiscount)
	{
		$oShop = $this->getEntity();

		$bTpl = $this->_mode == 'tpl';

		// Скидки от суммы заказа и дисконтные карты
		$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
		$oShop_Purchase_Discount_Controller
			->applyDiscountCards($this->applyDiscountCards)
			->applyDiscounts($this->applyDiscounts)
			->amount($amountPurchaseDiscount)
			->quantity($quantityPurchaseDiscount)
			->weight($this->weight)
			->couponText($this->couponText)
			->siteuserId($this->_oSiteuser ? $this->_oSiteuser->id : 0)
			->prices($this->_aDiscountPrices);

		$aArray = $oShop_Purchase_Discount_Controller->calculateDiscounts();

		foreach ($aArray['discounts'] as $oShop_Purchase_Discount)
		{
			if (!$bTpl)
			{
				$this->addEntity($oShop_Purchase_Discount->clearEntities());
			}
			else
			{
				$this->append('aShop_Purchase_Discounts', $oShop_Purchase_Discount);
			}
		}

		if (!is_null($aArray['discountcard']))
		{
			$oShop_Discountcard = $aArray['discountcard'];

			if (!$bTpl)
			{
				$this->addEntity($oShop_Discountcard->clearEntities());
			}
			else
			{
				$this->append('aShop_Discountcards', $oShop_Discountcard);
			}
		}

		return $aArray['discountAmount'];
	}

	/**
	 * AJAX refresh little cart
	 * @return self
	 */
	public function refreshLittleCart()
	{
		if (Core::moduleIsActive('cache'))
		{
			$oShop = $this->getEntity();

			if ($oShop->Site->html_cache_use)
			{
				?><script>
				var parentNode = jQuery('script').last().parent();
				jQuery(function() {
					jQuery.ajax({
						context: parentNode,
						url: '<?php echo $this->cartUrl?>',
						type: 'POST',
						dataType: 'json',
						data: {'_': Math.round(new Date().getTime()), 'loadCart': 1},
						success: function (ajaxData) {
							jQuery(this).html(ajaxData);
						},
						error: function (){return false}
					});
				});
				</script><?php
			}
		}

		return $this;
	}

	/**
	 * Add items properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addItemsPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aItem_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aItem_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addItemsPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aItem_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aItem_Properties[$parent_id]);
		}

		return $this;
	}

/**
	 * Add order's properties to XML
	 * @param int $parent_id
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addOrdersPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aOrder_Property_Dirs[$parent_id]))
		{
			foreach ($this->_aOrder_Property_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addOrdersPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aOrder_Properties[$parent_id]))
		{
			$parentObject->addEntities($this->_aOrder_Properties[$parent_id]);
		}

		return $this;
	}
}