<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online store cart operations.
 * Работа с корзиной интернет-магазина.
 *
 * Доступные методы:
 *
 * - shop_item_id($id) идентификатор товара
 * - quantity($value) количество товара
 * - marking($value) артикул товара в заказе, если отличается от артикула товара
 * - postpone(TRUE|FALSE) товар отложен
 * - shop_warehouse_id($id) идентификатор склада
 * - siteuser_id($id) идентификатор пользователя сайта
 * - checkStock(TRUE|FALSE) проверять наличие товара на складе, по умолчанию FALSE
 * - getLastError() возвращает последний статус ошибки: FALSE - без ошибок, 1 - Shop item id установлен в NULL, 2 - не найден добавляемый товар, 3 - Пользователю запрещен доступ к товару, 4 - передано нулевое количество товара или товара нет на складе.
 *
 * Доступные свойства:
 *
 * - totalQuantity общее количество неотложенного товара
 * - totalAmount сумма неотложенного товара
 * - totalTax налог неотложенного товара
 * - totalWeight суммарный вес неотложенного товара
 * - totalVolume суммарный объем неотложенного товара
 * - totalQuantityForPurchaseDiscount общее количество неотложенного товара для расчета скидки от суммы заказа
 * - totalAmountForPurchaseDiscount сумма неотложенного товара для расчета скидки от суммы заказа
 * - totalDiscountPrices цены товаров для расчета скидки на N-й товар
 *
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Cart_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_item_id',
		'quantity',
		'marking',
		'postpone',
		'shop_warehouse_id',
		'siteuser_id',
		'checkStock',

		'totalQuantity',
		'totalAmount',
		'totalTax',
		'totalWeight',
		'totalVolume',
		'totalQuantityForPurchaseDiscount',
		'totalAmountForPurchaseDiscount',
		'totalDiscountPrices',
	);

	/**
	 * Last error, default FALSE
	 * 1 - Shop item id is NULL
	 * 2 - Shop item doesn't exist
	 * 3 - Siteuser doesn't have access to the shop item
	 * 4 - zero quantity or shop item out of stock
	 */
	protected $_error = FALSE;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->clear();

		$this->siteuser_id = 0;
		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

			!is_null($oSiteuser)
				&& $this->siteuser_id = $oSiteuser->id;
		}

		$this->checkStock = FALSE;
	}

	/**
	 * Get Last Error
	 * @return boolean|int
	 */
	public function getLastError()
	{
		return $this->_error;
	}

	/**
	 * Clear cart operation's options
	 * @return Shop_Cart_Controller
	 * @hostcms-event Shop_Cart_Controller.onBeforeClear
	 * @hostcms-event Shop_Cart_Controller.onAfterClear
	 */
	public function clear()
	{
		Core_Event::notify(get_class($this) . '.onBeforeClear', $this);

		$this->shop_item_id = NULL;

		$this->quantity = 1;
		$this->postpone = $this->shop_warehouse_id = 0;
		$this->marking = '';

		$this->_error = FALSE;

		Core_Event::notify(get_class($this) . '.onAfterClear', $this);

		return $this;
	}

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Move goods from session cart to database
	 * @param Shop_Model $oShop shop
	 * @return self
	 * @hostcms-event Shop_Cart_Controller.onBeforeMoveTemporaryCart
	 * @hostcms-event Shop_Cart_Controller.onAfterMoveTemporaryCart
	 * @hostcms-event Shop_Cart_Controller.onAfterMoveTemporaryCartItem
	 */
	public function moveTemporaryCart(Shop_Model $oShop)
	{
		Core_Event::notify(get_class($this) . '.onBeforeMoveTemporaryCart', $this);

		if ($this->siteuser_id)
		{
			$aShop_Cart = $this->_getAllFromSession($oShop);

			if (count($aShop_Cart))
			{
				foreach ($aShop_Cart as $oShop_Cart)
				{
					$this->clear()
						->shop_item_id($oShop_Cart->shop_item_id)
						->quantity($oShop_Cart->quantity)
						->marking($oShop_Cart->marking)
						->postpone($oShop_Cart->postpone)
						->shop_warehouse_id($oShop_Cart->shop_warehouse_id)
						->siteuser_id($this->siteuser_id)
						->add();

					Core_Event::notify(get_class($this) . '.onAfterMoveTemporaryCartItem', $this, array($oShop_Cart));
				}
				$this->clearSessionCart();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterMoveTemporaryCart', $this);

		return $this;
	}

	/**
	 * Get all goods in the cart
	 * @param Shop_Model $oShop shop
	 * @return array
	 * @hostcms-event Shop_Cart_Controller.onBeforeGetAll
	 * @hostcms-event Shop_Cart_Controller.onAfterGetAll
	 */
	public function getAll(Shop_Model $oShop)
	{
		Core_Event::notify(get_class($this) . '.onBeforeGetAll', $this);

		// Проверяем наличие данных о пользователе
		$aShop_Cart = $this->siteuser_id
			? $this->_getAllFromDb($oShop)
			: $this->_getAllFromSession($oShop);

		// Есть скидки на N-й товар, доступные для текущей даты
		$bPositionDiscount = $oShop->Shop_Purchase_Discounts->checkAvailableWithPosition();

		$this->totalAmount = $this->totalQuantity = $this->totalTax = $this->totalWeight = $this->totalVolume
			= $this->totalQuantityForPurchaseDiscount = $this->totalAmountForPurchaseDiscount
			= 0;

		$aDiscountPrices = array();

		$oShop_Item_Controller = new Shop_Item_Controller();

		foreach ($aShop_Cart as $oShop_Cart)
		{
			if ($oShop_Cart->postpone == 0 && $oShop_Cart->shop_item_id)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item', $oShop_Cart->shop_item_id);

				$bSkipItem = $oShop_Item->type == 4;

				$this->totalQuantity += $oShop_Cart->quantity;

				// Количество для скидок от суммы заказа рассчитывается отдельно
				$oShop_Item->apply_purchase_discount && !$bSkipItem
					&& $this->totalQuantityForPurchaseDiscount += $oShop_Cart->quantity;

				// Prices
				if (Core::moduleIsActive('siteuser') && $this->siteuser_id)
				{
					$oShop_Item_Controller->siteuser(
						Core_Entity::factory('Siteuser', $this->siteuser_id)
					);
				}

				$oShop_Item_Controller->count($oShop_Cart->quantity);
				$aPrices = $oShop_Item_Controller->getPrices($oShop_Item);
				$this->totalAmount += $aPrices['price_discount'] * $oShop_Cart->quantity;

				if ($bPositionDiscount && !$bSkipItem)
				{
					// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
					for ($i = 0; $i < $oShop_Cart->quantity; $i++)
					{
						$aDiscountPrices[] = $aPrices['price_discount'];
					}
				}

				// Сумма для скидок от суммы заказа рассчитывается отдельно
				$oShop_Item->apply_purchase_discount && !$bSkipItem
					&& $this->totalAmountForPurchaseDiscount += $aPrices['price_discount'] * $oShop_Cart->quantity;

				$this->totalTax += $aPrices['tax'] * $oShop_Cart->quantity;

				$this->totalWeight += $oShop_Item->weight * $oShop_Cart->quantity;

				$this->totalVolume += Shop_Controller::convertSizeMeasure($oShop_Item->length, $oShop->size_measure, 0)
					* Shop_Controller::convertSizeMeasure($oShop_Item->width, $oShop->size_measure, 0)
					* Shop_Controller::convertSizeMeasure($oShop_Item->height, $oShop->size_measure, 0);
			}
		}

		$this->totalDiscountPrices = $aDiscountPrices;

		Core_Event::notify(get_class($this) . '.onAfterGetAll', $this, array($aShop_Cart));

		$eventResult = Core_Event::getLastReturn();

		return !is_null($eventResult) ? $eventResult : $aShop_Cart;
	}

	/**
	 * Clear session cart
	 * @return Shop_Cart_Controller
	 */
	public function clearSessionCart()
	{
		Core_Session::start();
		if (isset($_SESSION['hostcmsCart']))
		{
			unset($_SESSION['hostcmsCart']);
		}
		return $this;
	}

	/**
	 * Get all carts from database
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	protected function _getAllFromDb(Shop_Model $oShop)
	{
		$aShop_Carts = $oShop->Shop_Carts->getBySiteuserId($this->siteuser_id, FALSE);

		$aTmp_Shop_Cart = array();
		foreach ($aShop_Carts as $oShop_Cart)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Cart->shop_item_id);

			if (!is_null($oShop_Item->id) && $oShop_Item->active)
			{
				// Проверять остаток для обычных товаров
				if ($this->checkStock && $oShop_Item->type != 1)
				{
					$iRest = $oShop_Item->getRest() - $oShop_Item->getReserved();

					// Reduce quantity
					if ($iRest < $oShop_Cart->quantity)
					{
						$oShop_Cart->quantity = $iRest;
						$oShop_Cart->save();
					}

					// Check new quantity
					if ($oShop_Cart->quantity <= 0)
					{
						$oShop_Cart->delete();
						$oShop_Cart = NULL;
					}
				}

				!is_null($oShop_Cart)
					&& $aTmp_Shop_Cart[] = $oShop_Cart;
			}
			else
			{
				$oShop_Cart->delete();
			}
		}

		return $aTmp_Shop_Cart;
	}

	/**
	 * Get all carts from session
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	protected function _getAllFromSession(Shop_Model $oShop)
	{
		$aShop_Cart = array();

		if (Core_Session::hasSessionId())
		{
			$isActive = Core_Session::isActive();
			!$isActive && Core_Session::start();

			$shop_id = $oShop->id;

			$aCart = Core_Array::getSession('hostcmsCart', array());
			$aCart[$shop_id] = Core_Array::get($aCart, $shop_id, array());

			foreach ($aCart[$shop_id] as $shop_item_id => $aCartItem)
			{
				$aCartItem += array(
					'quantity' => 0,
					'postpone' => 0,
					'marking' => '',
					'shop_warehouse_id' => 0
				);

				$oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id);

				if (!is_null($oShop_Item->id) && $oShop_Item->active)
				{
					// Temporary object
					$oShop_Cart = Core_Entity::factory('Shop_Cart');
					$oShop_Cart->shop_item_id = $shop_item_id;
					$oShop_Cart->quantity = $aCartItem['quantity'];
					$oShop_Cart->postpone = $aCartItem['postpone'];
					$oShop_Cart->marking = $aCartItem['marking'];
					$oShop_Cart->shop_id = $shop_id;
					$oShop_Cart->shop_warehouse_id = $aCartItem['shop_warehouse_id'];
					$oShop_Cart->siteuser_id = 0;

					// Проверять остаток для обычных товаров
					if ($this->checkStock && $oShop_Item->type != 1)
					{
						$iRest = $oShop_Item->getRest() - $oShop_Item->getReserved();

						// Reduce quantity
						if ($iRest < $oShop_Cart->quantity)
						{
							$oShop_Cart->quantity = $iRest;
						}

						// Check new quantity
						if ($oShop_Cart->quantity <= 0)
						{
							$oShop_Cart = NULL;
						}
					}

					!is_null($oShop_Cart)
						&& $aShop_Cart[] = $oShop_Cart;
				}
			}

			!$isActive && Core_Session::close();
		}

		return $aShop_Cart;
	}

	/**
	 * Get item from cart
	 * @return object
	 * @hostcms-event Shop_Cart_Controller.onBeforeGet
	 * @hostcms-event Shop_Cart_Controller.onAfterGet
	 */
	public function get()
	{
		Core_Event::notify(get_class($this) . '.onBeforeGet', $this);

		// Проверяем наличие данных о пользователе
		if ($this->siteuser_id)
		{
			$oShop_Cart = Core_Entity::factory('Shop_Cart')
				->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

			if (is_null($oShop_Cart))
			{
				$oShop_Cart = Core_Entity::factory('Shop_Cart');
				$oShop_Cart->shop_item_id = $this->shop_item_id;
				$oShop_Cart->siteuser_id = $this->siteuser_id;
				$oShop_Cart->quantity = 0;
			}
		}
		else
		{
			Core_Session::start();

			$Shop_Item = Core_Entity::factory('Shop_Item', $this->shop_item_id);

			$aCart = Core_Array::getSession('hostcmsCart', array());
			$aCart[$Shop_Item->shop_id] = Core_Array::get($aCart, $Shop_Item->shop_id, array());

			$aReturn = Core_Array::get($aCart[$Shop_Item->shop_id], $this->shop_item_id, array()) + array(
				'shop_item_id' => $this->shop_item_id,
				'quantity' => 0,
				'postpone' => 0,
				'marking' => '',
				'shop_id' => $Shop_Item->shop_id,
				'shop_warehouse_id' => 0
			);

			$oShop_Cart = (object)$aReturn;
		}

		Core_Event::notify(get_class($this) . '.onAfterGet', $this);

		return $oShop_Cart;
	}

	/**
	 * Delete item from cart
	 * @return Shop_Cart_Controller
	 * @hostcms-event Shop_Cart_Controller.onBeforeDelete
	 * @hostcms-event Shop_Cart_Controller.onAfterDelete
	 */
	public function delete()
	{
		Core_Event::notify(get_class($this) . '.onBeforeDelete', $this);

		// Проверяем наличие данных о пользователе
		if ($this->siteuser_id)
		{
			$oShop_Cart = Core_Entity::factory('Shop_Cart')
				->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

			!is_null($oShop_Cart) && $oShop_Cart->delete();
		}
		else
		{
			Core_Session::start();
			$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);
			if (isset($_SESSION['hostcmsCart'][$oShop_Item->shop_id][$this->shop_item_id]))
			{
				unset($_SESSION['hostcmsCart'][$oShop_Item->shop_id][$this->shop_item_id]);
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterDelete', $this);

		return $this;
	}

	/**
	 * Add item into cart
	 * @return Shop_Cart_Controller
	 * @hostcms-event Shop_Cart_Controller.onBeforeAdd
	 * @hostcms-event Shop_Cart_Controller.onAfterAdd
	 */
	public function add()
	{
		Core_Event::notify(get_class($this) . '.onBeforeAdd', $this);

		if (is_null($this->shop_item_id))
		{
			$this->_error = 1;
			throw new Core_Exception('Shop item id is NULL.');
		}

		$oItem_In_Cart = $this->get();

		// Увеличиваем на количество уже в корзине
		$this->quantity = Shop_Controller::convertDecimal($this->quantity) + $oItem_In_Cart->quantity;

		Core_Event::notify(get_class($this) . '.onAfterAdd', $this);

		return $this->update();
	}

	/**
	 * Update item in cart
	 * @return Shop_Cart_Controller
	 * @hostcms-event Shop_Cart_Controller.onBeforeUpdate
	 * @hostcms-event Shop_Cart_Controller.onAfterUpdate
	 */
	public function update()
	{
		$this->_error = FALSE;

		Core_Event::notify(get_class($this) . '.onBeforeUpdate', $this);

		$this->quantity = Shop_Controller::convertDecimal($this->quantity);

		$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);

		if (!is_null($oShop_Item->id))
		{
			$aSiteuserGroups = array(0, -1);
			if (Core::moduleIsActive('siteuser'))
			{
				$oSiteuser = Core_Entity::factory('Siteuser', $this->siteuser_id);

				if ($oSiteuser)
				{
					$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
					foreach ($aSiteuser_Groups as $oSiteuser_Group)
					{
						$aSiteuserGroups[] = $oSiteuser_Group->id;
					}
				}
			}

			// Проверяем право пользователя добавить этот товар в корзину
			if (in_array($oShop_Item->getSiteuserGroupId(), $aSiteuserGroups))
			{
				$this->quantity = $oShop_Item->type == 2
					? floatval($this->quantity)
					: intval($this->quantity);

				// 1. Check STEP. DECIMAL, > 0, NOT $oShop_Item->quantity_step
				if ($oShop_Item->quantity_step > 0)
				{
					$iStep = $this->quantity / $oShop_Item->quantity_step;

					if (!is_int($iStep))
					{
						$this->quantity = ceil($iStep) * $oShop_Item->quantity_step;
					}
				}

				// 2. Check MIN quantity
				if ($this->quantity < $oShop_Item->min_quantity)
				{
					$this->quantity = $oShop_Item->min_quantity;
				}

				// 3. Check MAX quantity (DECIMAL, $oShop_Item->max_quantity > 0, NOT $oShop_Item->max_quantity)
				if ($oShop_Item->max_quantity > 0 && $this->quantity > $oShop_Item->max_quantity)
				{
					$this->quantity = $oShop_Item->max_quantity;
				}

				// Нужно получить реальное количество товара, если товар электронный
				if ($oShop_Item->type == 1)
				{
					// Получаем количество электронного товара на складе
					$iShop_Item_Digitals = $oShop_Item->Shop_Item_Digitals->getCountDigitalItems();

					if ($iShop_Item_Digitals != -1 && $iShop_Item_Digitals < $this->quantity)
					{
						$this->quantity = $iShop_Item_Digitals;
					}
				}

				// Повторно после изменений quantity выше
				$this->quantity = $oShop_Item->type == 2
					? floatval($this->quantity)
					: intval($this->quantity);

				// Проверять остаток для обычных товаров
				if ($this->checkStock && $oShop_Item->type != 1)
				{
					$iRest = $oShop_Item->getRest() - $oShop_Item->getReserved();
					$iRest < $this->quantity && $this->quantity = $iRest;
				}

				if ($this->quantity > 0)
				{
					// Проверяем наличие данных о пользователе
					if ($this->siteuser_id)
					{
						$oShop_Cart = Core_Entity::factory('Shop_Cart')
							->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

						if (is_null($oShop_Cart))
						{
							$oShop_Cart = Core_Entity::factory('Shop_Cart');
							$oShop_Cart->shop_item_id = $this->shop_item_id;
							$oShop_Cart->siteuser_id = $this->siteuser_id;
						}

						// Вставляем данные в таблицу корзины
						$oShop_Cart->quantity = $this->quantity;
						$oShop_Cart->postpone = $this->postpone;
						strlen($this->marking) && $oShop_Cart->marking = $this->marking;
						$oShop_Cart->shop_id = $oShop_Item->shop_id;
						$oShop_Cart->shop_warehouse_id = $this->shop_warehouse_id;
						$oShop_Cart->save();
					}
					else
					{
						Core_Session::start();
						$_SESSION['hostcmsCart'][$oShop_Item->shop_id][$this->shop_item_id] = array(
							'quantity' => $this->quantity,
							'postpone' => $this->postpone,
							'marking' => $this->marking,
							'siteuser_id' => $this->siteuser_id,
							'shop_warehouse_id' => $this->shop_warehouse_id
						);
					}
				}
				else
				{
					$this->_error = 4;
					$this->delete();
				}
			}
			else
			{
				$this->_error = 3;
			}
		}
		else
		{
			$this->_error = 2;
		}

		Core_Event::notify(get_class($this) . '.onAfterUpdate', $this);

		return $this;
	}
}