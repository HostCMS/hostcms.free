<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Shop_Payment_System_Handler
{
	/**
	 * DEPRECATED as of HostCMS 6.9.2
	 * @var int
	 */
	protected $_bonusMultiplier = 1;

	/**
	 * Create instance of payment system
	 * @param Shop_Payment_System_Model $oShop_Payment_System_Model payment system
	 * @return mixed
	 */
	static public function factory(Shop_Payment_System_Model $oShop_Payment_System_Model)
	{
		$path = $oShop_Payment_System_Model->getPaymentSystemFilePath();

		if (Core_File::isFile($path))
		{
			require_once($path);

			$name = 'Shop_Payment_System_Handler' . intval($oShop_Payment_System_Model->id);

			if (class_exists($name))
			{
				return new $name($oShop_Payment_System_Model);
			}
		}

		return NULL;
	}

	/**
	 * Call ->checkPaymentBeforeContent() on each shop's Shop_Payment_System_Handlers
	 * @param Shop_Model $oShop
	 */
	static public function checkBeforeContent(Shop_Model $oShop)
	{
		self::_check($oShop, 'checkPaymentBeforeContent');
	}

	/**
	 * Call ->checkPaymentAfterContent() on each shop's Shop_Payment_System_Handlers
	 * @param Shop_Model $oShop
	 */
	static public function checkAfterContent(Shop_Model $oShop)
	{
		return self::_check($oShop, 'checkPaymentAfterContent');
	}

	/**
	 * Protected method to call $methodName on each shop's Shop_Payment_System_Handlers
	 */
	static protected function _check(Shop_Model $oShop, $methodName)
	{
		$aShop_Payment_Systems = $oShop->Shop_Payment_Systems->getAllByActive(1);

		foreach ($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$oHandler = self::factory($oShop_Payment_System);
			if ($oHandler && method_exists($oHandler, $methodName))
			{
				$oHandler->$methodName();
			}
		}
	}

	/**
	 * @var array
	 */
	protected $_aDiscountPrices = array();

	/**
	 * @var array
	 */
	protected $_quantityPurchaseDiscount = NULL;

	/**
	 * @var array
	 */
	protected $_amountPurchaseDiscount = NULL;

	/**
	 * @var array
	 */
	protected $_quantity = NULL;

	/**
	 * @var array
	 */
	protected $_amount = NULL;

	/**
	 * @var array
	 */
	protected $_weight = NULL;

	/**
	 * List of properties
	 * @var array
	 */
	protected $_aProperties = array();

	/**
	 * Property directories
	 * @var array
	 */
	protected $_aProperty_Dirs = array();

	/**
	 * Params of the order
	 * @var array
	 */
	protected $_orderParams = NULL;

	/**
	 * Set order params
	 * @param array $orderParams
	 * @return self
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeOrderParams
	 * @hostcms-event Shop_Payment_System_Handler.onAfterOrderParams
	 */
	public function orderParams($orderParams)
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeOrderParams', $this, array($orderParams));

		$this->_orderParams = $orderParams + array(
			'invoice' => NULL,
			'acceptance_report' => NULL,
			'vat_invoice' => NULL,
			'coupon_text' => NULL,
		);

		Core_Event::notify('Shop_Payment_System_Handler.onAfterOrderParams', $this, array($orderParams));

		return $this;
	}

	/**
	 * Round prices
	 * @var boolean
	 */
	protected $_round = TRUE;

	/**
	 * Round prices
	 * @param boolean $round
	 * @return self
	 */
	public function round($round)
	{
		$this->_round = $round;
		return $this;
	}

	/**
	 * Round prices
	 * @var boolean
	 */
	protected $_applyDiscounts = TRUE;

	/**
	 * Apply Discounts
	 * @param boolean $applyDiscounts
	 * @return self
	 */
	public function applyDiscounts($applyDiscounts)
	{
		$this->_applyDiscounts = $applyDiscounts;
		return $this;
	}

	/**
	 * Apply Discount Cards
	 * @var boolean
	 */
	protected $_applyDiscountCards = TRUE;

	/**
	 * Apply Discount Cards
	 * @param boolean $applyDiscountCards
	 * @return self
	 */
	public function applyDiscountCards($applyDiscountCards)
	{
		$this->_applyDiscountCards = $applyDiscountCards;
		return $this;
	}

	/**
	 * Payment system
	 * @var Shop_Payment_System_Model
	 */
	protected $_Shop_Payment_System_Model = NULL;

	/**
	 * Constructor.
	 * @param Shop_Payment_System_Model $oShop_Payment_System_Model payment system
	 */
	public function __construct(Shop_Payment_System_Model $oShop_Payment_System_Model)
	{
		$this->_Shop_Payment_System_Model = $oShop_Payment_System_Model;
	}

	/**
	 * Allow upload files for order's property
	 * @var boolean
	 */
	protected $_allowOrderPropertyFiles = FALSE;

	/**
	 * Allow upload files for order's property
	 * @param boolean $allowOrderPropertyFiles
	 * @return self
	 */
	public function allowOrderPropertyFiles($allowOrderPropertyFiles)
	{
		$this->_allowOrderPropertyFiles = $allowOrderPropertyFiles;
		return $this;
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeExecute
	 * @hostcms-event Shop_Payment_System_Handler.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeExecute', $this);

		Core_Session::start();

		if (isset($_SESSION['hostcmsOrder']['coupon_text']))
		{
			Shop_Item_Controller::coupon($_SESSION['hostcmsOrder']['coupon_text']);
		}

		!isset($_SESSION['last_order_id']) && $_SESSION['last_order_id'] = 0;

		// Если заказ еще не был оформлен
		if ($_SESSION['last_order_id'] == 0)
		{
			// Оформить новый заказ
			$this->_processOrder();

			$_SESSION['last_order_id'] = $this->_shopOrder->id;

			// Уведомление о событии создания заказа
			$this->createNotification();
		}
		else
		{
			$this->shopOrder(
				Core_Entity::factory('Shop_Order', intval($_SESSION['last_order_id']))
			);

			// Пользователь сменил платежную систему
			if (!$this->_shopOrder->paid
				&& $this->_shopOrder->shop_payment_system_id != intval(Core_Array::get($this->_orderParams, 'shop_payment_system_id', 0)))
			{
				$this->_shopOrder->shop_payment_system_id = intval(Core_Array::get($this->_orderParams, 'shop_payment_system_id', 0));
				$this->_shopOrder->save();
			}
		}

		Core_Event::notify('Shop_Payment_System_Handler.onAfterExecute', $this);

		if (Core_Event::getLastReturn() !== 'final')
		{
			$this->userExecute();
		}

		return $this;
	}

	/**
	 * Клиентская логика обработки платежа
	 * @return self
	 */
	public function userExecute()
	{
		return $this;
	}

	/**
	 * Объект заказа до изменения.
	 */
	protected $_shopOrderBeforeAction = NULL;

	/**
	 * Set order before change
	 * @param Shop_Order_Model $oShopOrderBeforeAction
	 * @return self
	 */
	public function shopOrderBeforeAction(Shop_Order_Model $oShopOrderBeforeAction)
	{
		$this->_shopOrderBeforeAction = $oShopOrderBeforeAction;
		return $this;
	}

	/**
	 * Get order before change
	 * @return Shop_Order_Model
	 */
	public function getShopOrderBeforeAction()
	{
		return $this->_shopOrderBeforeAction;
	}

	/**
	 * Объект заказа
	 */
	protected $_shopOrder = NULL;

	/**
	 * Set order
	 * @param Shop_Order_Model $oShop_Order
	 * @return self
	 */
	public function shopOrder(Shop_Order_Model $oShop_Order)
	{
		$this->_shopOrder = $oShop_Order;
		return $this;
	}

	/**
	 * Get Shop_Order Model
	 * @return Shop_Order_Model
	 */
	public function getShopOrder()
	{
		return $this->_shopOrder;
	}

	/**
	 * Create a new order by $this->_orderParams
	 */
	public function createOrder()
	{
		$oShop = $this->_Shop_Payment_System_Model->Shop;

		$this->_shopOrder = Core_Entity::factory('Shop_Order');
		$this->_shopOrder->shop_country_id = Core_Array::get($this->_orderParams, 'shop_country_id', 0, 'int');
		$this->_shopOrder->shop_country_location_id = Core_Array::get($this->_orderParams, 'shop_country_location_id', 0, 'int');
		$this->_shopOrder->shop_country_location_city_id = Core_Array::get($this->_orderParams, 'shop_country_location_city_id', 0, 'int');
		$this->_shopOrder->shop_country_location_city_area_id = Core_Array::get($this->_orderParams, 'shop_country_location_city_area_id', 0, 'int');
		$this->_shopOrder->postcode = Core_Array::get($this->_orderParams, 'postcode', '', 'trim');
		$this->_shopOrder->address = Core_Array::get($this->_orderParams, 'address', '', 'trim');
		$this->_shopOrder->house = Core_Array::get($this->_orderParams, 'house', '', 'trim');
		$this->_shopOrder->flat = Core_Array::get($this->_orderParams, 'flat', '', 'trim');
		$this->_shopOrder->surname = Core_Array::get($this->_orderParams, 'surname', '', 'trim');
		$this->_shopOrder->name = Core_Array::get($this->_orderParams, 'name', '', 'trim');
		$this->_shopOrder->patronymic = Core_Array::get($this->_orderParams, 'patronymic', '', 'trim');
		$this->_shopOrder->company = Core_Array::get($this->_orderParams, 'company', '', 'trim');
		$this->_shopOrder->phone = Core_Array::get($this->_orderParams, 'phone', '', 'trim');
		$this->_shopOrder->fax = Core_Array::get($this->_orderParams, 'fax', '', 'trim');
		$this->_shopOrder->email = Core_Array::get($this->_orderParams, 'email', '', 'trim');
		$this->_shopOrder->description = Core_Array::get($this->_orderParams, 'description', '', 'trim');
		$this->_shopOrder->system_information = Core_Array::get($this->_orderParams, 'system_information', '', 'trim');
		$this->_shopOrder->delivery_information = Core_Array::get($this->_orderParams, 'delivery_information', '', 'trim');

		$shop_delivery_condition_id = Core_Array::get($this->_orderParams, 'shop_delivery_condition_id', 0, 'int');
		$this->_shopOrder->shop_delivery_condition_id = $shop_delivery_condition_id;

		$shop_delivery_id = Core_Array::get($this->_orderParams, 'shop_delivery_id', 0, 'int');
		!$shop_delivery_id && $shop_delivery_condition_id && $shop_delivery_id = Core_Entity::factory('Shop_Delivery_Condition', $shop_delivery_condition_id)->shop_delivery_id;
		$this->_shopOrder->shop_delivery_id = intval($shop_delivery_id);

		$this->_shopOrder->shop_payment_system_id = Core_Array::get($this->_orderParams, 'shop_payment_system_id', 0, 'int');
		$this->_shopOrder->shop_currency_id = intval($oShop->shop_currency_id);
		$this->_shopOrder->shop_order_status_id = intval($oShop->shop_order_status_id);
		$this->_shopOrder->tin = Core_Array::get($this->_orderParams, 'tin', '', 'trim');
		$this->_shopOrder->kpp = Core_Array::get($this->_orderParams, 'kpp', '', 'trim');

		if (isset($this->_orderParams['company_id']))
		{
			$this->_shopOrder->company_id = intval($this->_orderParams['company_id']);
		}
		elseif ($oShop->shop_company_id)
		{
			$oCompany = $oShop->Company;
			$oCompany_Account = $oCompany->Company_Accounts->getDefault();

			$this->_shopOrder->company_id = $oCompany->id;
			$this->_shopOrder->company_account_id = !is_null($oCompany_Account)
				? $oCompany_Account->id
				: 0;
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			$oSiteuser && $this->_shopOrder->siteuser_id = $oSiteuser->id;
		}

		// UTM, Openstat or From
		$oUser = Core_Auth::getCurrentUser();
		if (is_null($oUser))
		{
			$oSource_Controller = new Source_Controller();
			$this->_shopOrder->source_id = $oSource_Controller->getId();
		}

		// Номер заказа
		$sInvoice = Core_Array::get($this->_orderParams, 'invoice', '', 'str');
		$bInvoice = strlen($sInvoice) > 0;
		$bInvoice && $this->_shopOrder->invoice = $sInvoice;

		// Номер акта
		$sAcceptanceReport = Core_Array::get($this->_orderParams, 'acceptance_report', '', 'str');
		$bAcceptance_report = strlen($sAcceptanceReport) > 0;
		$bAcceptance_report && $this->_shopOrder->acceptance_report = $sAcceptanceReport;

		// Номер с/ф
		$sVatInvoice = Core_Array::get($this->_orderParams, 'vat_invoice', '', 'str');
		$bVat_invoice = strlen($sVatInvoice) > 0;
		$bVat_invoice && $this->_shopOrder->vat_invoice = $sVatInvoice;

		$oShop->add($this->_shopOrder);

		// Additional order properties
		$aOrderParamProperties = Core_Array::get($this->_orderParams, 'properties');

		if (is_array($aOrderParamProperties))
		{
			foreach ($aOrderParamProperties as $aTmp)
			{
				if (is_array($aTmp) && count($aTmp) == 2)
				{
					$iProperty_id = $aTmp[0];
					$value = $aTmp[1];

					$oProperty = Core_Entity::factory('Property', $iProperty_id);
					$oProperty_Value = $oProperty->createNewValue($this->_shopOrder->id);

					// Дополнительные свойства
					switch ($oProperty->type)
					{
						case 0: // Int
						case 3: // List
						case 5: // Information system
						case 12: // Shop
							$oProperty_Value->value(intval($value));
							$oProperty_Value->save();
						break;
						case 11: // Float
							$oProperty_Value->value(floatval($value));
							$oProperty_Value->save();
						break;
						case 1: // String
						case 4: // Textarea
						case 6: // Wysiwyg
						case 10: // Hidden
							$oProperty_Value->value(strval($value));
							$oProperty_Value->save();
						break;
						case 8: // Date
							$date = strval($value);
							$date = Core_Date::date2sql($date);
							$oProperty_Value->value($date);
							$oProperty_Value->save();
						break;
						case 9: // Datetime
							$datetime = strval($value);
							$datetime = Core_Date::datetime2sql($datetime);
							$oProperty_Value->value($datetime);
							$oProperty_Value->save();
						break;
						case 2: // File
							if ($this->_allowOrderPropertyFiles || 1)
							{
								$aFileData = $value;

								$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $oShop->id);

								// New values of property
								if (is_array($aFileData) && isset($aFileData['name']))
								{
									if (Core_File::isValidExtension($aFileData['name'], Core::$mainConfig['availableExtension']))
									{
										$oProperty_Value->file_name = Core_Str::stripTags($aFileData['name']);
										$oProperty_Value->save();

										$oShop_Order_Property_List->createPropertyDir($this->_shopOrder);

										try
										{
											$oProperty_Value->file = $oShop_Order_Property_List->getLargeFileName($this->_shopOrder, $oProperty_Value, $aFileData['name']);

											// not moveUploadedFile(), see lib_7.php
											Core_File::upload($aFileData['tmp_name'], $oShop_Order_Property_List->getDirPath($this->_shopOrder) . $oProperty_Value->file);

											$oProperty_Value->save();
										}
										catch (Exception $e) {};
									}
								}
							}
						break;
						case 7: // Checkbox
							$oProperty_Value->value(is_null($value) ? 0 : 1);
							$oProperty_Value->save();
						break;
					}
				}
			}
		}

		$this->shopOrder($this->_shopOrder);

		// Если не установлен модуль пользователей сайта - записываем в сессию
		// идентификатор вставленного заказа, чтобы далее можно было посмотреть квитаницию
		// об оплате или счет.
		//if (!Core::moduleIsActive('siteuser'))
		//{
			Core_Session::start();
			$_SESSION['order_' . $this->_shopOrder->id] = TRUE;
		//}

		// Номер заказа
		!$bInvoice && $this->_shopOrder->createInvoice();

		// Номер акта
		!$bAcceptance_report && $this->_shopOrder->acceptance_report($this->_shopOrder->id);

		// Номер с/ф
		!$bVat_invoice && $this->_shopOrder->vat_invoice($this->_shopOrder->id);

		$this->_shopOrder->save();

		return $this;
	}

	/**
	 * Создание нового заказа на основе данных, указанных в orderParams
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeProcessOrder
	 * @hostcms-event Shop_Payment_System_Handler.onAfterItemGetPrices
	 * @hostcms-event Shop_Payment_System_Handler.onAfterProcessOrder
	 * @hostcms-event Shop_Payment_System_Handler.onAfterAddShopOrderItem
	 */
	protected function _processOrder()
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeProcessOrder', $this);

		if (!count($this->_orderParams) /*is_null($this->_orderParams)*/)
		{
			throw new Core_Exception('orderParams is empty.');
		}

		$oShop = $this->_Shop_Payment_System_Model->Shop;

		// Create new order
		$this->createOrder();

		$this->_quantityPurchaseDiscount = $this->_amountPurchaseDiscount
			= $this->_quantity = $this->_amount = $this->_weight = 0;

		Core::moduleIsActive('siteuser')
			&& $oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

		// Массив цен для расчета скидок каждый N-й со скидкой N%
		$this->_aDiscountPrices = array();

		// Есть скидки на N-й товар, доступные для текущей даты
		$bPositionDiscount = $oShop->Shop_Purchase_Discounts->checkAvailableWithPosition();

		// Prices
		$oShop_Item_Controller = new Shop_Item_Controller();

		$Shop_Cart_Controller = Shop_Cart_Controller::instance();

		$aShop_Cart = $Shop_Cart_Controller->getAll($oShop);
		foreach ($aShop_Cart as $oShop_Cart)
		{
			if ($oShop_Cart->Shop_Item->id)
			{
				if ($oShop_Cart->postpone == 0)
				{
					$oShop_Item = $oShop_Cart->Shop_Item;

					$bSkipItem = $oShop_Item->type == 4;

					$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
					$oShop_Order_Item->quantity = $oShop_Cart->quantity;
					$oShop_Order_Item->shop_measure_id = $oShop_Item->shop_measure_id;
					$oShop_Order_Item->shop_item_id = $oShop_Cart->shop_item_id;
					$oShop_Order_Item->shop_warehouse_id = intval($oShop_Cart->shop_warehouse_id);

					Core::moduleIsActive('siteuser') && $oSiteuser
						&& $oShop_Item_Controller->siteuser($oSiteuser);

					$oShop_Item_Controller->count($oShop_Cart->quantity);

					$aPrices = $oShop_Item_Controller->getPrices($oShop_Item, $this->_round);

					Core_Event::notify('Shop_Payment_System_Handler.onAfterItemGetPrices', $this, array($aPrices, $oShop_Cart));

					$eventResult = Core_Event::getLastReturn();
					is_array($eventResult) && $aPrices = $eventResult;

					$this->_quantity += $oShop_Cart->quantity;

					$this->_amount += $aPrices['price_discount'] * $oShop_Cart->quantity;

					$this->_weight += $oShop_Item->weight * $oShop_Cart->quantity;

					if ($bPositionDiscount && !$bSkipItem)
					{
						// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
						for ($i = 0; $i < $oShop_Cart->quantity; $i++)
						{
							$this->_aDiscountPrices[] = $aPrices['price_discount'];
						}
					}

					if ($oShop_Item->apply_purchase_discount && !$bSkipItem)
					{
						$bApplyPurchaseDiscount = TRUE;
						foreach ($aPrices['discounts'] as $oShop_Discount)
						{
							if ($oShop_Discount->not_apply_purchase_discount)
							{
								$bApplyPurchaseDiscount = FALSE;
								break;
							}
						}

						if ($bApplyPurchaseDiscount)
						{
							// Сумма для скидок от суммы заказа рассчитывается отдельно
							$this->_amountPurchaseDiscount += $aPrices['price_discount'] * $oShop_Cart->quantity;

							// Количество для скидок от суммы заказа рассчитывается отдельно
							$this->_quantityPurchaseDiscount += $oShop_Cart->quantity;
						}
					}

					$oShop_Order_Item->price = $aPrices['price_discount'] - $aPrices['tax'];
					$oShop_Order_Item->rate = $aPrices['rate'];
					$oShop_Order_Item->name = $oShop_Item->name;
					$oShop_Order_Item->type = 0;
					$oShop_Order_Item->marking = strlen((string) $oShop_Cart->marking)
						? $oShop_Cart->marking
						: $oShop_Item->marking;

					// Статус товаров по умолчанию.
					if ($oShop->shop_order_status_id
						&& $oShop->shop_order_status_id == $this->_shopOrder->shop_order_status_id
						&& $this->_shopOrder->Shop_Order_Status->shop_order_item_status_id
					)
					{
						$oShop_Order_Item->shop_order_item_status_id = $this->_shopOrder->Shop_Order_Status->shop_order_item_status_id;
					}

					$this->_shopOrder->add($oShop_Order_Item);

					// Save coupon
					if (isset($aPrices['coupon']))
					{
						$this->_shopOrder->coupon = $aPrices['coupon'];
						$this->_shopOrder->save();
					}

					Core_Event::notify('Shop_Payment_System_Handler.onAfterAddShopOrderItem', $this, array($oShop_Order_Item, $oShop_Cart));

					// Delete item from the cart
					$Shop_Cart_Controller
						->shop_item_id($oShop_Cart->shop_item_id)
						->delete();

					$oShop_Item->clearCache();
				}
			}
			else
			{
				$oShop_Cart->delete();
			}
		}

		// Reserved
		$oShop->reserve && !$this->_shopOrder->paid
			&& $this->_shopOrder->reserveItems();

		if ($this->_amount > 0)
		{
			// Add a discount to the purchase
			if (!is_null($this->_orderParams['coupon_text']))
			{
				$this->_shopOrder->coupon = $this->_orderParams['coupon_text'];
			}

			$this->_addPurchaseDiscount();
		}

		$this->_addDelivery();

		// Удаляем истекшие товары
		Core_QueryBuilder::delete('shop_item_reserved')
			->leftJoin('shop_items', 'shop_item_reserved.shop_item_id', '=', 'shop_items.id')
			->where('shop_items.shop_id', '=', $oShop->id)
			->where('shop_item_reserved.datetime', '<', Core_Date::timestamp2sql(time() - $oShop->reserve_hours * 60 * 60))
			->execute();

		// Частичная оплата с лицевого счета
		if (Core::moduleIsActive('siteuser'))
		{
			// Списание бонусов
			$this->applyBonuses();

			// Частичная оплата с лицевого счета
			if (isset($this->_orderParams['partial_payment_by_personal_account'])
			&& $this->_orderParams['partial_payment_by_personal_account']
			&& $this->_shopOrder->Siteuser->id)
			{
				$this->applyPartialPayment();
			}
		}

		// Удаление купона из сессии
		if (isset($_SESSION['hostcmsOrder']['coupon_text']))
		{
			unset($_SESSION['hostcmsOrder']['coupon_text']);
		}

		// Удаление примененных бонусов из сессии
		if (isset($_SESSION['hostcmsOrder']['bonuses']))
		{
			unset($_SESSION['hostcmsOrder']['bonuses']);
		}

		Core_Event::notify('Shop_Payment_System_Handler.onAfterProcessOrder', $this);

		return $this;
	}

	/**
	 * Create Notifications
	 * @return self
	 */
	public function createNotification()
	{
		$oModule = Core::$modulesList['shop'];

		if ($oModule && Core::moduleIsActive('notification'))
		{
			$aUserIDs = array();

			$oNotification_Subscribers = Core_Entity::factory('Notification_Subscriber');
			$oNotification_Subscribers->queryBuilder()
				->where('notification_subscribers.module_id', '=', $oModule->id)
				->where('notification_subscribers.type', '=', 0)
				->where('notification_subscribers.entity_id', '=', $this->_shopOrder->Shop->id);

			$aNotification_Subscribers = $oNotification_Subscribers->findAll(FALSE);

			foreach ($aNotification_Subscribers as $oNotification_Subscriber)
			{
				$aUserIDs[] = $oNotification_Subscriber->user_id;
			}

			// Ответственные сотрудники
			!in_array($this->_shopOrder->user_id, $aUserIDs) && $aUserIDs[] = $this->_shopOrder->user_id;

			if (Core::moduleIsActive('siteuser') && $this->_shopOrder->siteuser_id)
			{
				$aSiteuser_Users = $this->_shopOrder->Siteuser->Siteuser_Users->findAll(FALSE);
				foreach ($aSiteuser_Users as $oSiteuser_User)
				{
					!in_array($oSiteuser_User->user_id, $aUserIDs)
						&& $aUserIDs[] = $oSiteuser_User->user_id;
				}
			}

			if (count($aUserIDs))
			{
				$sCompany = $this->_shopOrder->getCustomerName();

				$oNotification = Core_Entity::factory('Notification');
				$oNotification
					->title(Core::_('Shop_Order.notification_new_order', strip_tags($this->_shopOrder->invoice), FALSE))
					->description(Core::_('Shop_Order.notification_new_order_description', strip_tags($sCompany), $this->_shopOrder->sum(), FALSE))
					->datetime(Core_Date::timestamp2sql(time()))
					->module_id($oModule->id)
					->type(1) // Новый заказ
					->entity_id($this->_shopOrder->id)
					->save();

				foreach ($aUserIDs as $user_id)
				{
					// Связываем уведомление с сотрудником
					Core_Entity::factory('User', $user_id)->add($oNotification);
				}
			}
		}

		return $this;
	}

	/**
	 * Apply Bonuses
	 * @return self
	 */
	public function applyBonuses()
	{
		if (isset($_SESSION['hostcmsOrder']['bonuses']) && $_SESSION['hostcmsOrder']['bonuses'] > 0)
		{
			$oShop = $this->_Shop_Payment_System_Model->Shop;

			// Получаем доступные бонусы
			$aSiteuserBonuses = $this->_shopOrder->Siteuser->getBonuses($oShop);

			// Уменьшаем количество, если запрошенного количества бонусов нет
			$requestedBonuses = $aSiteuserBonuses['total'] <= $_SESSION['hostcmsOrder']['bonuses']
				? $aSiteuserBonuses['total']
				: $_SESSION['hostcmsOrder']['bonuses'];

			if ($requestedBonuses)
			{
				$fCurrencyCoefficient = $this->_shopOrder->Shop_Currency->id > 0 && $oShop->Shop_Currency->id > 0
					? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
						$this->_shopOrder->Shop_Currency,
						$oShop->Shop_Currency
					)
					: 0;

				// Сумма заказа в валюте магазина
				$fOrderAmount = $this->_shopOrder->getAmount() * $fCurrencyCoefficient;

				$max_bonus = Shop_Controller::instance()->round($fOrderAmount * ($oShop->max_bonus / 100));

				$available_bonuses = $requestedBonuses <= $max_bonus
					? $requestedBonuses
					: $max_bonus;

				// Списание бонусов
				$writtenOff = 0;
				foreach ($aSiteuserBonuses['bonuses'] as $oShop_Discountcard_Bonus)
				{
					$delta = $oShop_Discountcard_Bonus->amount - $oShop_Discountcard_Bonus->written_off;

					// На текущем этапе будут списаны все необходимые бонусы
					if ($available_bonuses - $writtenOff <= $delta)
					{
						$written_off = $available_bonuses - $writtenOff;

						$oShop_Discountcard_Bonus->written_off += $written_off;
						$oShop_Discountcard_Bonus->save();

						// Вносим списание
						$this->_addShopDiscountcardBonusTransaction($oShop_Discountcard_Bonus->id, $written_off);

						break;
					}
					else
					{
						$written_off = $delta;

						$oShop_Discountcard_Bonus->written_off += $written_off;
						$oShop_Discountcard_Bonus->save();

						// Вносим списание
						$this->_addShopDiscountcardBonusTransaction($oShop_Discountcard_Bonus->id, $written_off);
					}

					$writtenOff += $delta;
				}

				// Списание оплаченной суммы из цены заказа
				$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
				$oShop_Order_Item->name = Core::_('Shop_Bonus.paid_by_bonuses');
				$oShop_Order_Item->quantity = 1;
				$oShop_Order_Item->rate = 0;
				$oShop_Order_Item->price = $available_bonuses * -1;
				$oShop_Order_Item->marking = '';
				$oShop_Order_Item->type = 5; // 5 - Списание бонусов в счет оплаты счета
				$this->_shopOrder->add($oShop_Order_Item);
			}
		}

		return $this;
	}

	/**
	 *
	 */
	protected function _addShopDiscountcardBonusTransaction($shop_discountcard_bonus_id, $amount)
	{
		$oShop_Discountcard_Bonus_Transaction = Core_Entity::factory('Shop_Discountcard_Bonus_Transaction');
		$oShop_Discountcard_Bonus_Transaction->shop_order_id = $this->_shopOrder->id;
		$oShop_Discountcard_Bonus_Transaction->shop_discountcard_bonus_id = $shop_discountcard_bonus_id;
		$oShop_Discountcard_Bonus_Transaction->amount = $amount;
		$oShop_Discountcard_Bonus_Transaction->save();

		return $this;
	}

	/**
	 * Apply Partial Payment by Siteuser's account
	 * @return self
	 */
	public function applyPartialPayment()
	{
		$oShop = $this->_Shop_Payment_System_Model->Shop;

		// Остаток на счете пользователя в валюте магазина
		$fSiteuserAmount = $this->_shopOrder->Siteuser->getTransactionsAmount($oShop);

		// На счете есть средства
		if ($fSiteuserAmount)
		{
			$fCurrencyCoefficient = $this->_shopOrder->Shop_Currency->id > 0 && $oShop->Shop_Currency->id > 0
				? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					$this->_shopOrder->Shop_Currency,
					$oShop->Shop_Currency
				)
				: 0;

			// Сумма заказа в валюте магазина
			$fOrderAmount = $this->_shopOrder->getAmount() * $fCurrencyCoefficient;

			// Сумма заказа меньше или равна средствам
			$fPartialPaymentAmount = $fSiteuserAmount > $fOrderAmount
				? $fOrderAmount
				: $fSiteuserAmount;

			// Проведение транзакции по списанию предоплаты
			$oShop_Siteuser_Transaction = Core_Entity::factory('Shop_Siteuser_Transaction');
			$oShop_Siteuser_Transaction->shop_id = $oShop->id;
			$oShop_Siteuser_Transaction->siteuser_id = $this->_shopOrder->Siteuser->id;
			$oShop_Siteuser_Transaction->active = 1;

			$oShop_Siteuser_Transaction->amount_base_currency =
				$oShop_Siteuser_Transaction->amount = $fPartialPaymentAmount * -1;

			$oShop_Siteuser_Transaction->shop_currency_id = $this->_shopOrder->shop_currency_id;
			$oShop_Siteuser_Transaction->shop_order_id = $this->_shopOrder->id;
			$oShop_Siteuser_Transaction->type = 0;
			$oShop_Siteuser_Transaction->description = Core::_('Shop_Siteuser_Transaction.paid_by_personal_account');
			$oShop_Siteuser_Transaction->save();

			// Списание оплаченной суммы из цены заказа
			$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
			$oShop_Order_Item->name = Core::_('Shop_Siteuser_Transaction.paid_by_personal_account');
			$oShop_Order_Item->quantity = 1;
			$oShop_Order_Item->rate = 0;
			$oShop_Order_Item->price = $fPartialPaymentAmount * -1;
			$oShop_Order_Item->marking = '';
			$oShop_Order_Item->type = 6; // 6 - Частичная оплата с лицевого счета
			$this->_shopOrder->add($oShop_Order_Item);

			// Оплачена полная сумма
			if ($fPartialPaymentAmount == $fOrderAmount)
			{
				$oBefore = clone $this->_shopOrder;

				$this->_shopOrder->paid();

				// Установка XSL-шаблонов в соответствии с настройками в узле структуры
				$this->setXSLs();

				// Отправка писем клиенту и пользователю
				$this->send();

				ob_start();

				$this
					->shopOrderBeforeAction($oBefore)
					->changedOrder('changeStatusPaid');

				ob_get_clean();
			}
		}

		return $this;
	}

	/**
	 * Add a discount to the purchase
	 * @return self
	 */
	protected function _addPurchaseDiscount()
	{
		$this->_shopOrder->addPurchaseDiscount(
			array(
				'amount' => $this->_amountPurchaseDiscount,
				'quantity' => $this->_quantityPurchaseDiscount,
				'weight' => $this->_weight,
				'prices' => $this->_aDiscountPrices,
				'applyDiscounts' => $this->_applyDiscounts,
				'applyDiscountCards' => $this->_applyDiscountCards
			)
		);

		return $this;
	}

	/**
	 * Get delivery name
	 * @param Shop_Delivery_Model $oShop_Delivery
	 * @return string
	 */
	protected function _getDeliveryName(Shop_Delivery_Model $oShop_Delivery, $shop_delivery_condition_name = NULL)
	{
		return is_null($shop_delivery_condition_name)
			? Core::_('Shop_Delivery.delivery', $oShop_Delivery->name)
			: Core::_('Shop_Delivery.delivery_with_condition', $oShop_Delivery->name, $shop_delivery_condition_name);
	}

	/**
	 * Add a delivery into the order
	 * @return self
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeAddDelivery
	 * @hostcms-event Shop_Payment_System_Handler.onAfterAddDelivery
	 */
	protected function _addDelivery()
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeAddDelivery', $this);

		$shop_delivery_condition_id = intval(Core_Array::get($this->_orderParams, 'shop_delivery_condition_id', 0));
		$shop_delivery_id = intval(Core_Array::get($this->_orderParams, 'shop_delivery_id', 0));

		// Добавляем стоимость доставки как отдельный товар
		// Доставка может прийти как сущесвтующий shop_delivery_condition_id, так и shop_delivery_id + название рассчитанного условия доставки
		if ($shop_delivery_condition_id || $shop_delivery_id)
		{
			if ($shop_delivery_condition_id)
			{
				$oShop_Delivery_Condition = Core_Entity::factory('Shop_Delivery_Condition', $shop_delivery_condition_id);

				$oShop_Delivery = $oShop_Delivery_Condition->Shop_Delivery;

				$aPrice = $oShop_Delivery_Condition->getPriceArray();
				$price = $aPrice['price'];
				$rate = $aPrice['rate'];
				$marking = !is_null($oShop_Delivery_Condition->marking)
					? $oShop_Delivery_Condition->marking
					: '';

				$shop_delivery_condition_name = NULL;
			}
			// Доставка рассчитывалась кодом
			else
			{
				$oShop_Delivery = Core_Entity::factory('Shop_Delivery', $shop_delivery_id);

				$price = floatval(Core_Array::get($this->_orderParams, 'shop_delivery_price', 0));
				$rate = intval(Core_Array::get($this->_orderParams, 'shop_delivery_rate', 0));
				$marking = '';

				$shop_delivery_condition_name = strval(Core_Array::get($this->_orderParams, 'shop_delivery_name'));

				$this->_shopOrder->delivery_information = trim(
					$this->_shopOrder->delivery_information . "\n" . $shop_delivery_condition_name
				);
			}

			$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
			$oShop_Order_Item->name = $this->_getDeliveryName($oShop_Delivery, $shop_delivery_condition_name);
			$oShop_Order_Item->quantity = 1;
			$oShop_Order_Item->rate = $rate;
			$oShop_Order_Item->price = $price;
			$oShop_Order_Item->marking = $marking;
			$oShop_Order_Item->type = 1;
			$this->_shopOrder->add($oShop_Order_Item);
		}
		else
		{
			$oShop_Order_Item = NULL;
		}

		Core_Event::notify('Shop_Payment_System_Handler.onAfterAddDelivery', $this, array($oShop_Order_Item));

		return $this;
	}

	/**
	 * XSL данных о заказе
	 */
	protected $_xsl = NULL;

	/**
	 * Set XSL for order data
	 * @param Xsl_Model $oXsl
	 * @return self
	 */
	public function xsl(Xsl_Model $oXsl)
	{
		$this->_xsl = $oXsl;
		return $this;
	}

	/**
	 * Get invoice form
	 * @return mixed
	 */
	public function getInvoice()
	{
		return $this->_processXml();
	}

	/**
	 * Get notification form
	 * @return mixed
	 */
	public function getNotification()
	{
		return $this->_processXml();
	}

	/**
	 * Shows invoice
	 * @return self
	 */
	public function printInvoice()
	{
		echo $this->getInvoice();
		return $this;
	}

	/**
	 * Shows notification
	 * @return self
	 */
	public function printNotification()
	{
		echo $this->getNotification();
		return $this;
	}

	/**
	 * Prepare XML
	 * @return Shop_Model
	 * @hostcms-event Shop_Payment_System_Handler.onBeforePrepareXml
	 * @hostcms-event Shop_Payment_System_Handler.onAfterPrepareXml
	 */
	protected function _prepareXml()
	{
		$oShop = $this->_shopOrder->Shop->clearEntities();

		Core_Event::notify('Shop_Payment_System_Handler.onBeforePrepareXml', $this, array($oShop));

		// Список свойств заказа
		$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $oShop->id);

		$this->_aProperties = array();
		$aProperties = $oShop_Order_Property_List->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			$oProperty->clearEntities();
			$this->_aProperties[$oProperty->property_dir_id][] = $oProperty;

			$oShop_Order_Property = $oProperty->Shop_Order_Property;
			$oProperty
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('prefix')->value($oShop_Order_Property->prefix)
				)
				->addEntity(
					Core::factory('Core_Xml_Entity')->name('display')->value($oShop_Order_Property->display)
				);
		}

		$this->_aProperty_Dirs = array();
		$aProperty_Dirs = $oShop_Order_Property_List->Property_Dirs->findAll();
		foreach ($aProperty_Dirs as $oProperty_Dir)
		{
			$oProperty_Dir->clearEntities();
			$this->_aProperty_Dirs[$oProperty_Dir->parent_id][] = $oProperty_Dir;
		}

		// Список свойств
		$Shop_Order_Properties = Core::factory('Core_Xml_Entity')
			->name('properties');

		$oShop->addEntity($Shop_Order_Properties);

		$this->_addPropertiesList(0, $Shop_Order_Properties);

		$oCompany = $this->_shopOrder->company_id
			? $this->_shopOrder->Shop_Company // Returns Company_Model
			: $oShop->Company;

		$oShop
			->addEntity($oCompany)
			->addEntity(
				$oShop->Site->clearEntities()->showXmlAlias()
			)
			->addEntity(
				$this->_shopOrder->clearEntities()
					->showXmlCurrency(TRUE)
					->showXmlCountry(TRUE)
					->showXmlItems('not canceled')
					->showXmlDelivery(TRUE)
					->showXmlPaymentSystem(TRUE)
					->showXmlOrderStatus(TRUE)
					->showXmlProperties(TRUE)
					->showXmlSiteuser(TRUE)
			);

		Core_Event::notify('Shop_Payment_System_Handler.onAfterPrepareXml', $this, array($oShop));

		return $oShop;
	}

	/**
	 * Add list of user's properties to XML
	 * @param int $parent_id parent directory
	 * @param object $parentObject
	 * @return self
	 */
	protected function _addPropertiesList($parent_id, $parentObject)
	{
		if (isset($this->_aProperty_Dirs[$parent_id]))
		{
			foreach ($this->_aProperty_Dirs[$parent_id] as $oProperty_Dir)
			{
				$parentObject->addEntity($oProperty_Dir);
				$this->_addPropertiesList($oProperty_Dir->id, $oProperty_Dir);
			}
		}

		if (isset($this->_aProperties[$parent_id]))
		{
			$parentObject->addEntities($this->_aProperties[$parent_id]);
		}

		return $this;
	}

	/**
	 * Process XML
	 * @return mixed
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeProcessXml
	 * @hostcms-event Shop_Payment_System_Handler.onAfterProcessXml
	 */
	protected function _processXml()
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeProcessXml', $this);

		$sXml = $this->_prepareXml()->getXml();

		// debug xml
		// echo "<pre>" . htmlspecialchars($sXml) . "</pre>";

		$return = Xsl_Processor::instance()
			->xml($sXml)
			->xsl($this->_xsl)
			->process();

		$this->_shopOrder->clearEntities();

		Core_Event::notify('Shop_Payment_System_Handler.onAfterProcessXml', $this);

		return $return;
	}

	/**
	 * XSL письма администратору о заказе
	 */
	protected $_xslAdminMail = NULL;

	/**
	 * Set XSL for admin's e-mail
	 * @param Xsl_Model $oXsl
	 * @return self
	 */
	public function xslAdminMail(Xsl_Model $oXsl)
	{
		$this->_xslAdminMail = $oXsl;
		return $this;
	}

	/**
	 * XSL письма пользователю о заказе
	 */
	protected $_xslSiteuserMail = NULL;

	/**
	 * Set XSL for user's e-mail
	 * @param Xsl_Model $oXsl
	 * @return self
	 */
	public function xslSiteuserMail(Xsl_Model $oXsl)
	{
		$this->_xslSiteuserMail = $oXsl;
		return $this;
	}

	/**
	 * Content-type письма администратору о заказе
	 */
	protected $_adminMailContentType = 'text/html';

	/**
	 * Set Content-type of admin's e-mail
	 * @param string $contentType Content-type
	 * @return self
	 */
	public function adminMailContentType($contentType)
	{
		$this->_adminMailContentType = $contentType;
		return $this;
	}

	/**
	 * Content-type письма пользователю о заказе
	 */
	protected $_siteuserMailContentType = 'text/html';

	/**
	 * Set Content-type of user's e-mail
	 * @param string $contentType Content-type
	 * @return self
	 */
	public function siteuserMailContentType($contentType)
	{
		$this->_siteuserMailContentType = $contentType;
		return $this;
	}

	/**
	 * Тема письма администратору о заказе
	 */
	protected $_adminMailSubject = NULL;

	/**
	 * Set subject to shop's administrator e-mail
	 * @param string $subject subject
	 * @return self
	 */
	public function adminMailSubject($subject)
	{
		$this->_adminMailSubject = $subject;
		return $this;
	}

	/**
	 * Имя отправителя
	 */
	protected $_senderName = NULL;

	/**
	 * Set subject to user e-mail
	 * @param string $subject subject
	 * @return self
	 */
	public function senderName($senderName)
	{
		$this->_senderName = $senderName;
		return $this;
	}

	/**
	 * Адреса отправителя
	 */
	protected $_from = NULL;

	/**
	 * Set FROM
	 * @param string $from
	 * @return self
	 */
	public function from($from)
	{
		$this->_from = $from;
		return $this;
	}

	/**
	 * Тема письма пользователю о заказе
	 */
	protected $_siteuserMailSubject = NULL;

	/**
	 * Set subject to user e-mail
	 * @param string $subject subject
	 * @return self
	 */
	public function siteuserMailSubject($subject)
	{
		$this->_siteuserMailSubject = $subject;
		return $this;
	}

	/**
	 * Get user mail
	 * @return Core_Mail
	 */
	public function getSiteuserEmail()
	{
		return Core_Mail::instance()->clear();
	}

	/**
	 * Get admin e-mail
	 * @return Core_Mail
	 */
	public function getAdminEmail()
	{
		return Core_Mail::instance()->clear();
	}

	/**
	 * Set XSLs to e-mail
	 * @return self
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeSetXSLs
	 * @hostcms-event Shop_Payment_System_Handler.onAfterSetXSLs
	 */
	public function setXSLs()
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeSetXSLs', $this);

		$oShopOrder = $this->_shopOrder;
		$oStructure = $oShopOrder->Shop->Structure;
		$libParams = $oStructure->Lib->getDat($oStructure->id);

		$this->xslAdminMail(
			Core_Entity::factory('Xsl')->getByName(
				Core_Array::get($libParams, 'orderAdminNotificationXsl')
			)
		)
		->xslSiteuserMail(
			Core_Entity::factory('Xsl')->getByName(
				Core_Array::get($libParams, 'orderUserNotificationXsl')
			)
		);

		Core_Event::notify('Shop_Payment_System_Handler.onAfterSetXSLs', $this);

		return $this;
	}

	/**
	 * Send emails about order
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeSend
	 * @hostcms-event Shop_Payment_System_Handler.onAfterSend
	 */
	public function send()
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeSend', $this);

		if (is_null($this->_shopOrder))
		{
			throw new Core_Exception('send(): shopOrder is empty.');
		}

		$oShopOrder = $this->_shopOrder;
		$oShop = $oShopOrder->Shop;

		// Проверяем необходимость отправить письмо администратору
		if ($oShop->send_order_email_admin)
		{
			$oCore_Mail_Admin = $this->getAdminEmail();
			$this->sendAdminEmail($oCore_Mail_Admin);
		}

		if ($oShop->send_order_email_user)
		{
			$oCore_Mail_Siteuser = $this->getSiteuserEmail();
			$this->sendSiteuserEmail($oCore_Mail_Siteuser);
		}

		Core_Event::notify('Shop_Payment_System_Handler.onAfterSend', $this);

		return $this;
	}

	/**
	 * Get array of admin emails
	 * @return array
	 * @hostcms-event Shop_Payment_System_Handler.onGetAdminEmails
	 */
	public function getAdminEmails()
	{
		Core_Event::notify('Shop_Payment_System_Handler.onGetAdminEmails', $this);

		$lastReturn = Core_Event::getLastReturn();

		if (is_array($lastReturn))
		{
			return $lastReturn;
		}

		$oShop = $this->_shopOrder->Shop;

		return trim($oShop->email) != ''
			? explode(',', $oShop->email)
			: array(EMAIL_TO);
	}

	/**
	 * Send e-mail to shop's administrator
	 * @param Core_Mail $oCore_Mail mail
	 * @return self
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeSendAdminEmail
	 * @hostcms-event Shop_Payment_System_Handler.onAfterSendAdminEmail
	 */
	public function sendAdminEmail(Core_Mail $oCore_Mail)
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeSendAdminEmail', $this, array($oCore_Mail));

		if ($this->_xslAdminMail)
		{
			$oShopOrder = $this->_shopOrder;
			$oShop = $oShopOrder->Shop;

			// В адрес "ОТ КОГО" для администратора указывается адрес магазина,
			// а в Reply-To указывается email пользователя
			$from = !is_null($this->_from)
				? $this->_from
				: $this->_getEmailFrom();

			$replyTo = Core_Valid::email($oShopOrder->email)
				? $oShopOrder->email
				: $from;

			$this->xsl($this->_xslAdminMail);
			$sInvoice = $this->_processXml();
			$sInvoice = str_replace(">", ">\n", $sInvoice);

			// Тема письма администратору
			$date_str = Core_Date::sql2datetime($oShopOrder->datetime);
			$admin_subject = !is_null($this->_adminMailSubject)
				? $this->_adminMailSubject
				: sprintf($oShop->order_admin_subject, $oShopOrder->invoice, $oShop->name, $date_str);

			$senderName = !is_null($this->_senderName)
				? $this->_senderName
				: ($oShop->Site->sender_name != ''
					? $oShop->Site->sender_name
					: $oShop->name
				);

			$oCore_Mail
				->from($from)
				->senderName($senderName)
				->header('Reply-To', $replyTo)
				->subject($admin_subject)
				->message($sInvoice)
				->contentType($this->_adminMailContentType)
				->header('X-HostCMS-Reason', 'Order')
				->header('Precedence', 'bulk')
				->messageId();

			// Attach order property files
			$aProperty_Values = $oShopOrder->getPropertyValues(FALSE);
			foreach ($aProperty_Values as $oProperty_Value)
			{
				if ($oProperty_Value->Property->type == 2)
				{
					$sPath = $oProperty_Value->getLargeFilePath();

					if (Core_File::isFile($sPath))
					{
						$oCore_Mail->attach(array(
							'filepath' => $sPath,
							'filename' => $oProperty_Value->file_name
						));
					}
				}
			}

			$aAdminEmails = array_map('trim', $this->getAdminEmails());
			foreach ($aAdminEmails as $key => $sEmail)
			{
				// Delay 0.350s for second mail and others
				$key > 0 && usleep(350000);

				$sEmail = trim($sEmail);
				if (Core_Valid::email($sEmail))
				{
					$oCore_Mail->to($sEmail)->send();
				}
			}

			// Ответственные сотрудники
			if (Core::moduleIsActive('siteuser') && $this->_shopOrder->siteuser_id)
			{
				$aUsers = $this->_shopOrder->Siteuser->Users->findAll(FALSE);
				foreach ($aUsers as $key => $oUser)
				{
					$aDirectory_Emails = $oUser->Directory_Emails->findAll(FALSE);
					foreach ($aDirectory_Emails as $oDirectory_Email)
					{
						$sEmail = trim($oDirectory_Email->value);

						if (!in_array($sEmail, $aAdminEmails))
						{
							// Delay 0.350s for second mail and others
							$key > 0 && usleep(350000);

							if (Core_Valid::email($sEmail))
							{
								$oCore_Mail->to($sEmail)->send();
							}
						}
					}
				}
			}
		}

		Core_Event::notify('Shop_Payment_System_Handler.onAfterSendAdminEmail', $this, array($oCore_Mail));

		return $this;
	}

	/**
	 * Attach digital items to mail
	 * @param Core_Mail $oCore_Mail mail
	 * @return self
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeAttachDigitalItems
	 * @hostcms-event Shop_Payment_System_Handler.onAfterAttachDigitalItems
	 */
	protected function _attachDigitalItems(Core_Mail $oCore_Mail)
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeAttachDigitalItems', $this, array($oCore_Mail));

		$aShop_Order_Items = $this->_shopOrder->Shop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			// Digital items
			$aShop_Order_Item_Digitals = $oShop_Order_Item->Shop_Order_Item_Digitals->findAll(FALSE);
			foreach ($aShop_Order_Item_Digitals as $oShop_Order_Item_Digital)
			{
				$oShop_Item_Digital = $oShop_Order_Item_Digital->Shop_Item_Digital;

				if ($oShop_Item_Digital->filename != '' && Core_File::isFile($oShop_Item_Digital->getFullFilePath()))
				{
					$oCore_Mail->attach(array(
						'filepath' => $oShop_Item_Digital->getFullFilePath(),
						'filename' => $oShop_Item_Digital->filename,
					));
				}
			}
		}

		Core_Event::notify('Shop_Payment_System_Handler.onAfterAttachDigitalItems', $this, array($oCore_Mail));

		return $this;
	}

	/**
	 * Get sender email
	 * @return string
	 */
	protected function _getEmailFrom()
	{
		return $this->_shopOrder->Shop->getFirstEmail();
	}

	/**
	 * Send e-mail to user
	 * @param Core_Mail $oCore_Mail mail
	 * @return self
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeSendSiteuserEmail
	 * @hostcms-event Shop_Payment_System_Handler.onAfterSendSiteuserEmail
	 */
	public function sendSiteuserEmail(Core_Mail $oCore_Mail)
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeSendSiteuserEmail', $this, array($oCore_Mail));

		if ($this->_xslSiteuserMail)
		{
			$oShopOrder = $this->_shopOrder;
			$oShop = $oShopOrder->Shop;

			$to = $oShopOrder->email;

			if (Core_Valid::email($to))
			{
				// Адрес "ОТ КОГО" для пользователя
				$from = !is_null($this->_from)
					? $this->_from
					: $this->_getEmailFrom();

				$this->xsl($this->_xslSiteuserMail);
				$sInvoice = $this->_processXml();
				$sInvoice = str_replace(">", ">\n", $sInvoice);

				$date_str = Core_Date::sql2datetime($oShopOrder->datetime);
				// Тема письма пользователю
				$user_subject = !is_null($this->_siteuserMailSubject)
					? $this->_siteuserMailSubject
					: sprintf($oShop->order_user_subject, $oShopOrder->invoice, $oShop->name, $date_str);

				$senderName = !is_null($this->_senderName)
					? $this->_senderName
					: ($oShop->Site->sender_name != ''
						? $oShop->Site->sender_name
						: $oShop->name
					);

				// Attach digitals items
				if ($this->_shopOrder->paid == 1 && $this->_shopOrder->Shop->attach_digital_items == 1)
				{
					$this->_attachDigitalItems($oCore_Mail);
				}

				$oCore_Mail
					->from($from)
					->senderName($senderName)
					->to($to)
					->subject($user_subject)
					->message($sInvoice)
					->contentType($this->_siteuserMailContentType)
					->header('X-HostCMS-Reason', 'OrderConfirm')
					->header('Precedence', 'bulk')
					->messageId()
					->send();

				if (Core::moduleIsActive('siteuser') && $oShopOrder->siteuser_id)
				{
					$aConfig = Core_Config::instance()->get('siteuser_config', array());

					if (!isset($aConfig['save_emails']) || $aConfig['save_emails'])
					{
						$oSiteuser_Email = Core_Entity::factory('Siteuser_Email');
						$oSiteuser_Email->siteuser_id = $oShopOrder->siteuser_id;
						$oSiteuser_Email->subject = $user_subject;
						$oSiteuser_Email->email = $to;
						$oSiteuser_Email->from = $from;
						$oSiteuser_Email->type = $this->_siteuserMailContentType == 'text/html' ? 1 : 0;
						$oSiteuser_Email->text = $sInvoice;
						$oSiteuser_Email->save();
					}
				}
			}
		}

		Core_Event::notify('Shop_Payment_System_Handler.onAfterSendSiteuserEmail', $this, array($oCore_Mail));

		return $this;
	}

	/**
	 * Массив с режимами, при использовании которых должно происходить уведомление о покупке
	 * - apply - применение изменений заказа из списка заказа, включая изменение статуса
	 * - edit - редактирование заказа
	 * - changeStatusPaid - изменение статуса оплаты из списка заказов
	 * - cancelPaid - отмена заказа
	 */
	protected $_notificationModes = array('changeStatusPaid', 'edit');

	/**
	 * Set $this->_notificationModes
	 * @param array array of modes, e.g. array('changeStatusPaid', 'edit', 'changeStatusPaid')
	 * @return self
	 */
	public function setNotificationModes(array $notificationModes)
	{
		$this->_notificationModes = $notificationModes;
		return $this;
	}

	/**
	 * Set Mail Subjects
	 * @return self
	 */
	public function setMailSubjects()
	{
		$oShop = $this->getShopOrder()->Shop;

		$date_str = Core_Date::sql2datetime($this->getShopOrder()->datetime);

		// Изменение темы письма при отмене заказа
		if ($this->getShopOrder()->canceled)
		{
			$this->adminMailSubject(
				sprintf($oShop->cancel_admin_subject, $this->getShopOrder()->invoice, $oShop->name, $date_str)
			);

			$this->siteuserMailSubject(
				sprintf($oShop->cancel_user_subject, $this->getShopOrder()->invoice, $oShop->name, $date_str)
			);
		}
		// Изменение темы письма при оплате
		elseif ($this->getShopOrder()->paid)
		{
			$this->adminMailSubject(
				sprintf($oShop->confirm_admin_subject, $this->getShopOrder()->invoice, $oShop->name, $date_str)
			);

			$this->siteuserMailSubject(
				sprintf($oShop->confirm_user_subject, $this->getShopOrder()->invoice, $oShop->name, $date_str)
			);
		}

		return $this;
	}

	/**
	 * Уведомление об операциях с заказом
	 * @param string $mode режим изменения:
	 * - apply - применение изменений заказа из списка заказа, включая изменение статуса
	 * - edit - редактирование заказа
	 * - changeStatusPaid - изменение статуса оплаты из списка заказов
	 * - cancelPaid - отмена заказа
	 * @hostcms-event Shop_Payment_System_Handler.onBeforeChangedOrder
	 * @hostcms-event Shop_Payment_System_Handler.onAfterChangedOrder
	 */
	public function changedOrder($mode)
	{
		Core_Event::notify('Shop_Payment_System_Handler.onBeforeChangedOrder', $this, array($mode));

		if (in_array($mode, $this->_notificationModes))
		{
			if ($this->getShopOrderBeforeAction()->paid != $this->getShopOrder()->paid
				|| $this->getShopOrderBeforeAction()->canceled != $this->getShopOrder()->canceled)
			{
				$this->setMailSubjects();

				// Установка XSL-шаблонов в соответствии с настройками в узле структуры
				$this->setXSLs();

				// Отправка писем клиенту и пользователю
				$this->send();
			}
		}

		// Отмена заказа, снимаем зарезервированные товары
		if ($mode == 'cancelPaid')
		{
			$this->getShopOrder()->deleteReservedItems();
		}

		Core_Event::notify('Shop_Payment_System_Handler.onAfterChangedOrder', $this, array($mode));

		return $this;
	}

	/**
	 * Backward compatibility, see createNotifications()
	 * @return self
	 */
	protected function _createNotification()
	{
		return $this->createNotification();
	}

	/**
	 * Backward compatibility, see applyBonuses()
	 * @return self
	 */
	protected function _applyBonuses()
	{
		return $this->applyBonuses();
	}
}