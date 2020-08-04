<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ корзины магазина.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE
 * - itemsForbiddenTags(array('description')) массив тегов товаров, запрещенных к передаче в генерируемый XML
 * - warehousesItems(TRUE|FALSE) выводить остаток на каждом складе для товара, по умолчанию TRUE
 * - taxes(TRUE|FALSE) выводить список налогов, по умолчанию FALSE
 * - specialprices(TRUE|FALSE) показывать специальные цены для выбранных товаров, по умолчанию FALSE
 * - associatedItems(TRUE|FALSE) показывать сопутствующие товары для выбранных товаров, по умолчанию FALSE
 * - calculateCounts(TRUE|FALSE) вычислять общее количество товаров и групп в корневой группе, по умолчанию FALSE
 * - applyDiscounts(TRUE|FALSE) применять скидки от суммы заказа, по умолчанию TRUE
 * - applyDiscountCards(TRUE|FALSE) применять дисконтные карты, по умолчанию TRUE
 *
 * Доступные свойства:
 *
 * - amount сумма заказа с учетом скидок
 * - tax сумма налога
 * - quantity количество товаров в корзине
 * - weight вес товаров в корзине
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
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'itemsForbiddenTags',
		'warehousesItems',
		'taxes',
		'specialprices',
		'associatedItems',
		'cartUrl',
		'amount',
		'tax',
		'quantity',
		'weight',
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
			= $this->calculateCounts = $this->associatedItems = FALSE;

		$this->itemsPropertiesList = $this->warehousesItems
			= $this->applyDiscounts = $this->applyDiscountCards = TRUE;
			
		$this->itemsForbiddenTags = array();

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
	 * @return Shop_Cart_Controller
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
		if ($this->itemsPropertiesList)
		{
			$oShop_Item_Property_List = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

			$aProperties = is_array($this->itemsPropertiesList) && count($this->itemsPropertiesList)
					? $oShop_Item_Property_List->Properties->getAllByid($this->itemsPropertiesList, FALSE, 'IN')
					: $oShop_Item_Property_List->Properties->findAll();

			foreach ($aProperties as $oProperty)
			{
				$this->_aItem_Properties[$oProperty->property_dir_id][] = $oProperty->clearEntities();

				$oShop_Item_Property = $oProperty->Shop_Item_Property;

				$oShop_Item_Property->shop_measure_id && $oProperty->addEntity(
					$oShop_Item_Property->Shop_Measure
				);
			}

			$aProperty_Dirs = $oShop_Item_Property_List->Property_Dirs->findAll();
			foreach ($aProperty_Dirs as $oProperty_Dir)
			{
				$oProperty_Dir->clearEntities();
				$this->_aItem_Property_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir->clearEntities();
			}

			if (!$bTpl)
			{
				$Shop_Item_Properties = Core::factory('Core_Xml_Entity')
					->name('shop_item_properties');

				$this->addEntity($Shop_Item_Properties);

				$this->_addItemsPropertiesList(0, $Shop_Item_Properties);
			}
		}

		$quantityPurchaseDiscount = $amountPurchaseDiscount = $this->quantity = $this->amount = $this->tax = $this->weight = 0;

		// Массив цен для расчета скидок каждый N-й со скидкой N%
		$this->_aDiscountPrices = array();

		// Есть скидки на N-й товар, доступные для текущей даты
		$bPositionDiscount = $oShop->Shop_Purchase_Discounts->checkAvailableWithPosition();

		$Shop_Cart_Controller = $this->_getCartController();

		$aShop_Carts = $Shop_Cart_Controller->getAll($oShop);
		foreach ($aShop_Carts as $oShop_Cart)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item', $oShop_Cart->shop_item_id);

			if (!$bTpl)
			{
				$this->addEntity(
					$oShop_Cart
						->clearEntities()
						->showXmlWarehousesItems($this->warehousesItems)
						->showXmlProperties($this->itemsProperties)
						->showXmlSpecialprices($this->specialprices)
						->showXmlAssociatedItems($this->associatedItems)
						->setItemsForbiddenTags($this->itemsForbiddenTags)
				);
			}
			else
			{
				$this->append('aShop_Carts', $oShop_Cart);
			}

			if ($oShop_Cart->postpone == 0)
			{
				$this->quantity += $oShop_Cart->quantity;

				// Количество для скидок от суммы заказа рассчитывается отдельно
				$oShop_Item->apply_purchase_discount
					&& $quantityPurchaseDiscount += $oShop_Cart->quantity;

				// Prices
				$oShop_Item_Controller = new Shop_Item_Controller();
				if (Core::moduleIsActive('siteuser'))
				{
					$this->_oSiteuser
						&& $oShop_Item_Controller->siteuser($this->_oSiteuser);
				}

				$oShop_Item_Controller->count($oShop_Cart->quantity);
				$aPrices = $oShop_Item_Controller->getPrices($oShop_Cart->Shop_Item);
				$this->amount += $aPrices['price_discount'] * $oShop_Cart->quantity;

				if ($bPositionDiscount)
				{
					// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
					for ($i = 0; $i < $oShop_Cart->quantity; $i++)
					{
						$this->_aDiscountPrices[] = $aPrices['price_discount'];
					}
				}

				// Сумма для скидок от суммы заказа рассчитывается отдельно
				$oShop_Item->apply_purchase_discount
					&& $amountPurchaseDiscount += $aPrices['price_discount'] * $oShop_Cart->quantity;

				$this->tax += $aPrices['tax'] * $oShop_Cart->quantity;

				$this->weight += $oShop_Cart->Shop_Item->weight * $oShop_Cart->quantity;
			}
		}

		$this->taxes && $oShop->showXmlTaxes(TRUE);

		$fAppliedDiscountsAmount = $this->_getDiscoutAmount($quantityPurchaseDiscount, $amountPurchaseDiscount);

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
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_discount')
				->value($fAppliedDiscountsAmount)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_tax')
				->value($this->tax)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_quantity')
				->value($this->quantity)
		)->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_weight')
				->value($this->weight)
		);

		return parent::show();
	}

	/**
	 * Get Discount Amount
	 * @return decimal
	 */
	protected function _getDiscoutAmount($quantityPurchaseDiscount, $amountPurchaseDiscount)
	{
		$oShop = $this->getEntity();

		$bTpl = $this->_mode == 'tpl';

		// Дисконтная карта
		$bApplyMaxDiscount = $bApplyShopPurchaseDiscounts = FALSE;
		$fDiscountcard = $fAppliedDiscountsAmount = 0;

		if ($this->applyDiscountCards && Core::moduleIsActive('siteuser') && $this->_oSiteuser)
		{
			$oShop_Discountcard = $this->_oSiteuser->Shop_Discountcards->getByShop_id($oShop->id);
			if (!is_null($oShop_Discountcard) && $oShop_Discountcard->shop_discountcard_level_id)
			{
				$oShop_Discountcard_Level = $oShop_Discountcard->Shop_Discountcard_Level;

				$bApplyMaxDiscount = $oShop_Discountcard_Level->apply_max_discount == 1;

				// Сумма скидки по дисконтной карте
				$fDiscountcard = $this->amount * ($oShop_Discountcard_Level->discount / 100);
			}
		}

		if ($this->applyDiscounts)
		{
			// Скидки от суммы заказа
			$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
			$oShop_Purchase_Discount_Controller
				->amount($amountPurchaseDiscount)
				->quantity($quantityPurchaseDiscount)
				->couponText($this->couponText)
				->siteuserId($this->_oSiteuser ? $this->_oSiteuser->id : 0)
				->prices($this->_aDiscountPrices);

			$aShop_Purchase_Discounts = $oShop_Purchase_Discount_Controller->getDiscounts();

			// Если применять только максимальную скидку, то считаем сумму скидок по скидкам от суммы заказа
			if ($bApplyMaxDiscount)
			{
				$totalPurchaseDiscount = 0;

				foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
				{
					$totalPurchaseDiscount += $oShop_Purchase_Discount->getDiscountAmount();
				}

				$bApplyShopPurchaseDiscounts = $totalPurchaseDiscount > $fDiscountcard;
			}
			else
			{
				$bApplyShopPurchaseDiscounts = TRUE;
			}

			// Если решили применять скидку от суммы заказа
			if ($bApplyShopPurchaseDiscounts)
			{
				foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
				{
					if (!$bTpl)
					{
						$this->addEntity($oShop_Purchase_Discount->clearEntities());
					}
					else
					{
						$this->append('aShop_Purchase_Discounts', $oShop_Purchase_Discount);
					}

					$fAppliedDiscountsAmount += $oShop_Purchase_Discount->getDiscountAmount();
				}
			}

			// Скидка больше суммы заказа
			$fAppliedDiscountsAmount > $this->amount && $fAppliedDiscountsAmount = $this->amount;
		}

		// Не применять максимальную скидку или сумма по карте больше, чем скидка от суммы заказа
		if (!$bApplyMaxDiscount || !$bApplyShopPurchaseDiscounts)
		{
			if ($fDiscountcard)
			{
				$fAmountForCard = $this->amount - $fAppliedDiscountsAmount;

				if ($fAmountForCard > 0)
				{
					$oShop_Discountcard->discountAmount(
						Shop_Controller::instance()->round($fAmountForCard * ($oShop_Discountcard_Level->discount / 100))
					);

					if (!$bTpl)
					{
						$this->addEntity($oShop_Discountcard->clearEntities());
					}
					else
					{
						$this->append('aShop_Discountcards', $oShop_Discountcard);
					}

					$fAppliedDiscountsAmount += $oShop_Discountcard->getDiscountAmount();
				}
			}
		}

		return $fAppliedDiscountsAmount;
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
}