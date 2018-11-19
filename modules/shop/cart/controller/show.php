<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ корзины магазина.
 *
 * Доступные методы:
 *
 * - itemsProperties(TRUE|FALSE|array()) выводить значения дополнительных свойств товаров, по умолчанию FALSE. Может принимать массив с идентификаторами дополнительных свойств, значения которых необходимо вывести.
 * - itemsPropertiesList(TRUE|FALSE|array()) выводить список дополнительных свойств товаров, по умолчанию TRUE
 * - taxes(TRUE|FALSE) выводить список налогов, по умолчанию FALSE
 * - specialprices(TRUE|FALSE) показывать специальные цены для выбранных товаров, по умолчанию FALSE
 * - associatedItems(TRUE|FALSE) показывать сопутствующие товары для выбранных товаров, по умолчанию FALSE
 * - calculateCounts(TRUE|FALSE) вычислять общее количество товаров и групп в корневой группе, по умолчанию FALSE
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
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'taxes',
		'specialprices',
		'associatedItems',
		'cartUrl',
		'amount',
		'tax',
		'quantity',
		'weight',
		'calculateCounts',
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

		$this->itemsPropertiesList = TRUE;

		$this->cartUrl = $oShop->Structure->getPath() . 'cart/';
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

		$bXsl = !is_null($this->_xsl);

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

		if (!$bXsl)
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

			if ($bXsl)
			{
				$Shop_Item_Properties = Core::factory('Core_Xml_Entity')
					->name('shop_item_properties');

				$this->addEntity($Shop_Item_Properties);

				$this->_addItemsPropertiesList(0, $Shop_Item_Properties);
			}
		}

		$quantityPurchaseDiscount = $amountPurchaseDiscount = $quantity = $amount = $tax = $weight = 0;

		// Массив цен для расчета скидок каждый N-й со скидкой N%
		$aDiscountPrices = array();

		// Извлекаем все активные скидки с ценами на N-й товар, доступные для текущей даты
		$oShop_Purchase_Discounts = $oShop->Shop_Purchase_Discounts;
		$oShop_Purchase_Discounts->queryBuilder()
			->where('active', '=', 1)
			->where('position', '>', 0)
			->where('start_datetime', '<=', Core_Date::timestamp2sql(time()))
			->where('end_datetime', '>=', Core_Date::timestamp2sql(time()))
			->clearOrderBy()
			->limit(1);

		// Есть скидки на N-й товар
		$bPositionDiscount = $oShop_Purchase_Discounts->getCount() > 0;

		$Shop_Cart_Controller = $this->_getCartController();

		$aShop_Carts = $Shop_Cart_Controller->getAll($oShop);
		foreach ($aShop_Carts as $oShop_Cart)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Cart->shop_item_id);
			if (!is_null($oShop_Item->id))
			{
				if ($bXsl)
				{
					$this->addEntity(
						$oShop_Cart
							->clearEntities()
							->showXmlProperties($this->itemsProperties)
							->showXmlSpecialprices($this->specialprices)
							->showXmlAssociatedItems($this->associatedItems)
					);
				}
				else
				{
					$this->append('aShop_Carts', $oShop_Cart);
				}

				if ($oShop_Cart->postpone == 0)
				{
					$quantity += $oShop_Cart->quantity;

					// Количество для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $quantityPurchaseDiscount += $oShop_Cart->quantity;

					// Prices
					$oShop_Item_Controller = new Shop_Item_Controller();
					if (Core::moduleIsActive('siteuser'))
					{
						$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
						$oSiteuser && $oShop_Item_Controller->siteuser($oSiteuser);
					}

					$oShop_Item_Controller->count($oShop_Cart->quantity);
					$aPrices = $oShop_Item_Controller->getPrices($oShop_Cart->Shop_Item);
					$amount += $aPrices['price_discount'] * $oShop_Cart->quantity;

					if ($bPositionDiscount)
					{
						// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
						for ($i = 0; $i < $oShop_Cart->quantity; $i++)
						{
							$aDiscountPrices[] = $aPrices['price_discount'];
						}
					}

					// Сумма для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $amountPurchaseDiscount += $aPrices['price_discount'] * $oShop_Cart->quantity;

					$tax += $aPrices['tax'] * $oShop_Cart->quantity;

					$weight += $oShop_Cart->Shop_Item->weight * $oShop_Cart->quantity;
				}
			}
			else
			{
				$oShop_Cart->delete();
			}
		}

		// Дисконтная карта
		$bApplyMaxDiscount = FALSE;
		$fDiscountcard = 0;
		if (Core::moduleIsActive('siteuser') && $this->_oSiteuser)
		{
			$oSiteuser = $this->_oSiteuser;

			$oShop_Discountcard = $oSiteuser->Shop_Discountcards->getByShop_id($oShop->id);
			if (!is_null($oShop_Discountcard) && $oShop_Discountcard->shop_discountcard_level_id)
			{
				$oShop_Discountcard_Level = $oShop_Discountcard->Shop_Discountcard_Level;

				$bApplyMaxDiscount = $oShop_Discountcard_Level->apply_max_discount == 1;

				// Сумма скидки по дисконтной карте
				$fDiscountcard = $amount * ($oShop_Discountcard_Level->discount / 100);
			}
		}
		
		// Скидки от суммы заказа
		$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
		$oShop_Purchase_Discount_Controller
			->amount($amountPurchaseDiscount)
			->quantity($quantityPurchaseDiscount)
			->couponText($this->couponText)
			->siteuserId($this->_oSiteuser ? $this->_oSiteuser->id : 0)
			->prices($aDiscountPrices);

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
		
		$fAppliedDiscountsAmount = 0;
		
		// Если решили применять скидку от суммы заказа
		if ($bApplyShopPurchaseDiscounts)
		{
			foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
			{
				if ($bXsl)
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

		$this->taxes && $oShop->showXmlTaxes(TRUE);

		// Скидка больше суммы заказа
		$fAppliedDiscountsAmount > $amount && $fAppliedDiscountsAmount = $amount;

		// Не применять максимальную скидку или сумму по карте больше, чем скидка от суммы заказа
		if (!$bApplyMaxDiscount || !$bApplyShopPurchaseDiscounts)
		{
			if ($fDiscountcard)
			{
				$fAmountForCard = $amount - $fAppliedDiscountsAmount;

				if ($fAmountForCard > 0)
				{
					$oShop_Discountcard->discountAmount(
						Shop_Controller::instance()->round($fAmountForCard * ($oShop_Discountcard_Level->discount / 100))
					);
					
					if ($bXsl)
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

		// Скидка больше суммы заказа
		$fAppliedDiscountsAmount > $amount && $fAppliedDiscountsAmount = $amount;
		
		$this->amount = $amount - $fAppliedDiscountsAmount;
		$this->tax = $tax;
		$this->quantity = $quantity;
		$this->weight = $weight;

		// Total order amount
		$this->addEntity(
			Core::factory('Core_Xml_Entity')
				->name('total_amount')
				->value($this->amount)
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