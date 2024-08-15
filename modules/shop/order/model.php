<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Order_Model extends Core_Entity
{
	/**
	 * Values of all properties of item
	 * @var array
	 */
	protected $_propertyValues = NULL;

	/**
	 * Backend property
	 * @var int
	 */
	public $order_items = 1;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $reviews = 1;

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'invoice';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'comment' => array('through' => 'comment_shop_order'),
		'shop_order_item' => array(),
		'shop_item_reserved' => array(),
		'shop_siteuser_transaction' => array(),
		'shop_discountcard_bonus' => array(),
		'shop_discountcard_bonus_transaction' => array(),
		'shop_purchase_discount_coupon' => array(),
		'shop_order_history' => array(),
		'tag' => array('through' => 'tag_shop_order'),
		'tag_shop_order' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'shop_country_location_city_area_id' => 0,
		'shop_country_location_city_id' => 0,
		'shop_country_location_id' => 0,
		'shop_country_id' => 0,
		'unloaded' => 0
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'company_account' => array(),
		'shop' => array(),
		'shop_company' => array('model' => 'Company', 'foreign_key' => 'company_id'),
		'shop_country_location' => array(),
		'shop_country' => array(),
		'shop_country_location_city' => array(),
		'shop_country_location_city_area' => array(),
		'shop_delivery' => array(),
		'shop_delivery_condition' => array(),
		'siteuser' => array(),
		'shop_currency' => array(),
		'shop_order_status' => array(),
		'shop_payment_system' => array(),
		'source' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_orders.datetime' => 'DESC',
	);

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'datetime',
		'payment_datetime',
		'status_datetime',
	);

	/**
	 * TYPE
	 * @var int
	 */
	const TYPE = 5;

	/**
	 * Get Entity Type
	 * @return int
	 */
	public function getEntityType()
	{
		return self::TYPE;
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		$this->unpost();

		// Удалить зарезервированные товары
		$this->deleteReservedItems();

		return parent::markDeleted();
	}

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;

			$this->_preloadValues['guid'] = Core_Guid::get();
			$this->_preloadValues['ip'] = Core::getClientIp();

			$this->_preloadValues['datetime'] =
				$this->_preloadValues['acceptance_report_datetime'] =
				$this->_preloadValues['vat_invoice_datetime'] =
				$this->_preloadValues['status_datetime'] = Core_Date::timestamp2sql(time());

			$this->_preloadValues['siteuser_id'] = Core::moduleIsActive('siteuser') && isset($_SESSION['siteuser_id'])
				? intval($_SESSION['siteuser_id'])
				: 0;
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function companyBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->source_id)
		{
			$title = htmlspecialchars($this->Source->service);

			switch ($this->Source->service)
			{
				case 'google':
					echo ' <span title="' . $title . '" class="badge badge-ico badge-blue white"><i class="fa fa-google"></i></span>';
				break;
				case 'direct.yandex.ru':
					echo ' <span title="' . $title . '" class="badge badge-ico badge-darkorange white">Я</span>';
				break;
				case 'twitterfeed':
					echo ' <span title="' . $title . '" class="badge badge-ico badge-blue white"><i class="fa fa-twitter"></i></span>';
				break;
				default:
					echo ' <span title="' . $title . '" class="badge badge-ico badge-palegreen white"><i class="fa fa-tag"></i></span>';
				break;
			}
		}

		if (Core::moduleIsActive('tag'))
		{
			$aTags = $this->Tags->findAll(FALSE);

			foreach ($aTags as $oTag)
			{
				Core_Html_Entity::factory('Code')
					->value('<span class="badge badge-square badge-tag badge-max-width badge-lightgray margin-left-5" title="' . htmlspecialchars($oTag->name) . '"><i class="fa fa-tag"></i> ' . htmlspecialchars($oTag->name) . '</span>')
					->execute();
			}
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_order.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$sDir = $this->getOrderPath();

		// Удаляем значения доп. свойств
		$aPropertyValues = $this->getPropertyValues(FALSE);
		foreach ($aPropertyValues as $oPropertyValue)
		{
			$oPropertyValue->Property->type == 2 && $oPropertyValue->setDir($sDir);
			$oPropertyValue->delete();
		}

		if (Core::moduleIsActive('comment'))
		{
			// Удаляем комментарии
			$this->Comments->deleteAll(FALSE);
		}

		$this->Shop_Order_Items->deleteAll(FALSE);
		$this->Shop_Order_Histories->deleteAll(FALSE);

		$this->Shop_Purchase_Discount_Coupons->deleteAll(FALSE);

		// Удаляем связи с зарезервированными, прямая связь
		$this->Shop_Item_Reserveds->deleteAll(FALSE);

		$this->Shop_Discountcard_Bonuses->deleteAll(FALSE);
		$this->Shop_Discountcard_Bonus_Transactions->deleteAll(FALSE);

		if (Core::moduleIsActive('tag'))
		{
			// Удаляем метки
			$this->Tag_Shop_Orders->deleteAll(FALSE);
		}

		$this->source_id && $this->Source->delete();

		$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, $this->getEntityType());
		foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
		{
			$oShop_Warehouse_Entry->delete();
		}

		if (Core::moduleIsActive('lead'))
		{
			Core_QueryBuilder::update('leads')
				->set('shop_order_id', 0)
				->where('shop_order_id', '=', $this->id)
				->execute();
		}

		if (Core_File::isDir($sDir))
		{
			Core_File::deleteDir($sDir);
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Change cancel on opposite
	 * @return self
	 * @hostcms-event shop_order.onBeforeChangeStatusPaid
	 * @hostcms-event shop_order.onAfterChangeStatusPaid
	 */
	public function changeStatusPaid()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeStatusPaid', $this);

		if ($this->shop_payment_system_id)
		{
			$oShop_Payment_System_Handler = Shop_Payment_System_Handler::factory(
				$this->Shop_Payment_System
			);

			if ($oShop_Payment_System_Handler)
			{
				$oShop_Payment_System_Handler->shopOrder($this)->shopOrderBeforeAction(clone $this);
			}
		}

		$this->paid == 0
			? $this->paid()
			: $this->cancelPaid();

		if ($this->shop_payment_system_id)
		{
			if ($oShop_Payment_System_Handler)
			{
				$oShop_Payment_System_Handler->changedOrder('changeStatusPaid');
			}
		}

		$this->Shop->write_off_paid_items && $this->paid
			&& $this->post();

		Core_Event::notify($this->_modelName . '.onAfterChangeStatusPaid', $this);

		return $this;
	}

	/**
	 * Change posted
	 * @return self
	 * @hostcms-event shop_order.onBeforeChangeStatusPosted
	 * @hostcms-event shop_order.onAfterChangeStatusPosted
	 */
	public function changeStatusPosted()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeStatusPosted', $this);

		$this->posted == 0
			? $this->post()
			: $this->unpost();

		Core_Event::notify($this->_modelName . '.onAfterChangeStatusPosted', $this);

		return $this;
	}

	/**
	 * Get amount of order
	 * @return float
	 */
	public function getAmount()
	{
		$fAmount = 0;

		$aOrderItems = $this->Shop_Order_Items->findAll(FALSE);
		foreach ($aOrderItems as $oShop_Order_Item)
		{
			// Не установлен статус у товара или статус НЕ отмененный
			if (!$oShop_Order_Item->isCanceled())
			{
				$fAmount += $oShop_Order_Item->getAmount();
			}
		}

		return $fAmount;
	}

	/**
	 * Get quantity of items in an order
	 * @return float
	 */
	public function getQuantity()
	{
		$quantity = 0;

		$aOrderItems = $this->Shop_Order_Items->findAll(FALSE);
		foreach ($aOrderItems as $oShop_Order_Item)
		{
			$quantity += $oShop_Order_Item->quantity;
		}

		return $quantity;
	}

	/**
	 * Get sum of order
	 * @return float
	 */
	public function getSum()
	{
		return $this->getAmount();
	}

	/**
	 * Get order sum with currency name
	 * @return string
	 */
	public function sum()
	{
		return '<span>' . $this->Shop_Currency->formatWithCurrency($this->getAmount()) . '</span>';
	}

	/**
	 * Get order weight
	 * @return float
	 */
	public function getWeight()
	{
		$weight = 0;

		$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			if (!$oShop_Order_Item->isCanceled())
			{
				$weight += $oShop_Order_Item->Shop_Item->weight * $oShop_Order_Item->quantity;
			}
		}

		return Shop_Controller::instance()->round($weight);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function weightBackend()
	{
		return '<span>' . Core_Str::hideZeros($this->getWeight()) . '</span>';
	}

	public function smallAvatarBackend()
	{
		return $this->user_id
			? $this->User->smallAvatar()
			: '';
	}

	/**
	 * Change cancel on opposite
	 * @return self
	 * @hostcms-event shop_order.onBeforeChangeStatusCanceled
	 * @hostcms-event shop_order.onAfterChangeStatusCanceled
	 */
	public function changeStatusCanceled()
	{
		Core_Event::notify($this->_modelName . '.onBeforeChangeStatusCanceled', $this);

		if ($this->shop_payment_system_id)
		{
			$oShop_Payment_System_Handler = Shop_Payment_System_Handler::factory(
				Core_Entity::factory('Shop_Payment_System', $this->shop_payment_system_id)
			);

			if ($oShop_Payment_System_Handler)
			{
				$oShop_Payment_System_Handler->shopOrder($this)->shopOrderBeforeAction(clone $this);
			}
		}

		$this->canceled == 0
			? $this->cancel()
			: $this->uncancel();

		if ($this->shop_payment_system_id)
		{
			if ($oShop_Payment_System_Handler)
			{
				$oShop_Payment_System_Handler->changedOrder('cancelPaid');
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterChangeStatusCanceled', $this);

		return $this;
	}

	/**
	 * Cancel the order
	 * @return self
	 * @hostcms-event shop_order.onBeforeCancel
	 * @hostcms-event shop_order.onAfterCancel
	 */
	public function cancel()
	{
		Core_Event::notify($this->_modelName . '.onBeforeCancel', $this);

		if (!$this->canceled)
		{
			$this->canceled = 1;
			$this->save();

			// Удалить зарезервированные товары
			$this->deleteReservedItems();

			$this->historyPushCanceled();

			// Возврат бонусов
			$oShop_Discountcard_Bonus_Transactions = $this->Shop_Discountcard_Bonus_Transactions->findAll(FALSE);
			foreach ($oShop_Discountcard_Bonus_Transactions as $oShop_Discountcard_Bonus_Transaction)
			{
				$oShop_Discountcard_Bonus = Core_Entity::factory('Shop_Discountcard_Bonus')->getById($oShop_Discountcard_Bonus_Transaction->shop_discountcard_bonus_id);

				if (!is_null($oShop_Discountcard_Bonus) && $oShop_Discountcard_Bonus->written_off >= $oShop_Discountcard_Bonus_Transaction->amount)
				{
					$oShop_Discountcard_Bonus->written_off -= $oShop_Discountcard_Bonus_Transaction->amount;
					$oShop_Discountcard_Bonus->save();
				}
				else
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write('Shop_Order_Model: The transaction amount is greater than what was debited');
				}

				$oShop_Discountcard_Bonus_Transaction->delete();
			}

			// Удаляем отрицательный товар с бонусом
			$aShop_Order_Items = $this->Shop_Order_Items->getAllByType(5);
			foreach ($aShop_Order_Items as $oShop_Order_Item)
			{
				$oShop_Order_Item->markDeleted();
			}

			if (Core::moduleIsActive('webhook'))
			{
				Webhook_Controller::notify('onShopOrderCanceled', $this);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterCancel', $this);

		return $this->save();
	}

	/**
	 * Uncancel the order
	 * @return self
	 * @hostcms-event shop_order.onBeforeUncancel
	 * @hostcms-event shop_order.onAfterUncancel
	 */
	public function uncancel()
	{
		Core_Event::notify($this->_modelName . '.onBeforeUncancel', $this);

		if ($this->canceled)
		{
			$this->canceled = 0;
			$this->save();

			// Удалить зарезервированные товары
			$this->deleteReservedItems();

			// Резервируется при редактировании
			/*$this->Shop->reserve
				&& !$this->paid && !$this->posted
				&& $this->reserveItems();*/

			$this->historyPushCanceled();

			if (Core::moduleIsActive('webhook'))
			{
				Webhook_Controller::notify('onShopOrderUncanceled', $this);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterUncancel', $this);

		return $this->save();
	}


	/**
	 * Recalc delivery price by delivery conditions
	 * @return boolean
	 * @hostcms-event shop_order.onBeforeRecalcDelivery
	 * @hostcms-event shop_order.onAfterRecalcDelivery
	 */
	public function recalcDelivery()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRecalcDelivery', $this);

		$iOrderSum = $iOrderWeight = 0;

		$oShop_Controller = Shop_Controller::instance();

		$aOrderItems = $this->Shop_Order_Items->findAll(FALSE);
		$oShop = $this->Shop;

		foreach ($aOrderItems as $oShop_Order_Item)
		{
			if ($oShop_Order_Item->type == 0)
			{
				if (!$oShop_Order_Item->isCanceled())
				{
					$iOrderSum += $oShop_Order_Item->getPrice() * $oShop_Order_Item->quantity;
					$iOrderWeight += $oShop_Order_Item->Shop_Item->weight * $oShop_Order_Item->quantity;
				}
			}
		}

		$iOrderSum = $oShop_Controller->round($iOrderSum);
		$iOrderWeight = $oShop_Controller->round($iOrderWeight);

		$oShopDelivery = $this->Shop_Delivery;
		$iCountryId = $this->shop_country_id;
		$iLocationId = $this->shop_country_location_id;
		$iCityId = $this->shop_country_location_city_id;
		$iCityAreaId = $this->shop_country_location_city_area_id;

		for ($i = 0; $i < 5; $i++)
		{
			$sql = "
			SELECT `shop_delivery_conditions`.*,
			IF(`min_weight` > 0
				AND `max_weight` > 0
				AND `min_price` > 0
				AND `max_price` > 0, 1, 0) AS `orderfield`
			FROM `shop_deliveries`, `shop_delivery_conditions`
			WHERE `shop_id`='{$oShop->id}'
				AND `shop_deliveries`.`deleted` = 0
				AND `shop_delivery_conditions`.`deleted` = 0
				AND `shop_deliveries`.`id`=`shop_delivery_conditions`.`shop_delivery_id`
				AND `shop_delivery_conditions`.`shop_delivery_id`='{$this->shop_delivery_id}'
				AND `shop_country_id`='{$iCountryId}'
				AND `shop_country_location_id`='{$iLocationId}'
				AND `shop_country_location_city_id` = '{$iCityId}'
				AND `shop_country_location_city_area_id` = '{$iCityAreaId}'
				AND `min_weight` <= '{$iOrderWeight}'
				AND (`max_weight` >= '{$iOrderWeight}' OR `max_weight` = 0)
				AND `min_price` <= '{$iOrderSum}'
				AND (`max_price` >= '{$iOrderSum}' OR `max_price` = 0)
			ORDER BY
				`orderfield` DESC,
				`min_weight` DESC,
				`max_weight` DESC,
				`min_price` DESC,
				`max_price` DESC,
				`price` DESC
			";

			$aRows = Core_DataBase::instance()
				->setQueryType(0)
				->query($sql)
				->asObject('Shop_Delivery_Condition_Model')
				->result();

			$iRowCount = count($aRows);

			if ($iRowCount)
			{
				if ($iRowCount > 1)
				{
					Core::$log
						->clear()
						->status(1)
						->notify(TRUE)
						->write(Core::_('Shop_Order.cond_of_delivery_duplicate', $oShopDelivery->name, $aRows[0]->id));
				}

				$oShop_Delivery_Condition = $aRows[0];

				/*if ($this->shop_delivery_condition_id == $oShop_Delivery_Condition->id)
				{
					// Нашли то же условие доставки
				}
				else
				{*/
					// Нашли новое условие доставки
					$this->shop_delivery_condition_id = $oShop_Delivery_Condition->id;
					$this->save();

					// Update order's delivery item
					$oShop_Order_Item_Delivery = $this->Shop_Order_Items->getByType(1);
					if (is_null($oShop_Order_Item_Delivery))
					{
						$oShop_Order_Item_Delivery = Core_Entity::factory('Shop_Order_Item');
						$oShop_Order_Item_Delivery->shop_order_id = $this->id;
						$oShop_Order_Item_Delivery->type = 1;
					}

					$aPrice = $oShop_Delivery_Condition->getPriceArray();
					$oShop_Order_Item_Delivery->price = $aPrice['price'];
					$oShop_Order_Item_Delivery->quantity = 1;
					$oShop_Order_Item_Delivery->rate = $aPrice['rate'];
					$oShop_Order_Item_Delivery->marking = !is_null($oShop_Delivery_Condition->marking)
						? $oShop_Delivery_Condition->marking
						: '';
					$oShop_Order_Item_Delivery->name = Core::_('Shop_Delivery.delivery', $oShop_Delivery_Condition->Shop_Delivery->name);
					$oShop_Order_Item_Delivery->save();
				//}

				return TRUE;
			}
			else
			{
				switch ($i)
				{
					case 0 :
						$iCityAreaId = 0;
					break;
					case 1 :
						$iCityId = 0;
					break;
					case 2 :
						$iLocationId = 0;
					break;
					case 3 :
						$iCountryId = 0;
					break;
				}
			}
		}

		// Не нашли никаких условий доставки
		$this->shop_delivery_condition_id = 0;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterRecalcDelivery', $this);

		return TRUE;
	}

	/**
	 * Get orders by shop id
	 * @param int $shop_id shop id
	 * @return array
	 */
	public function getByShopId($shop_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_id', '=', $shop_id);

		return $this->findAll();
	}

	/**
	 * Show countries data in XML
	 * @var boolean
	 */
	protected $_showXmlCountry = FALSE;

	/**
	 * Show country in XML
	 * @param boolean $showXmlCountry
	 * @return self
	 */
	public function showXmlCountry($showXmlCountry = TRUE)
	{
		$this->_showXmlCountry = $showXmlCountry;
		return $this;
	}

	/**
	 * Show currency data in XML
	 * @var boolean
	 */
	protected $_showXmlCurrency = FALSE;

	/**
	 * Show currency in XML
	 * @param boolean $showXmlCurrency
	 * @return self
	 */
	public function showXmlCurrency($showXmlCurrency = TRUE)
	{
		$this->_showXmlCurrency = $showXmlCurrency;
		return $this;
	}

	/**
	 * Show siteuser data in XML
	 * @var boolean
	 */
	protected $_showXmlSiteuser = FALSE;

	/**
	 * Show siteuser in XML
	 * @param boolean $showXmlSiteuser
	 * @return self
	 */
	public function showXmlSiteuser($showXmlSiteuser = TRUE)
	{
		$this->_showXmlSiteuser = $showXmlSiteuser;
		return $this;
	}

	/**
	 * Show order items data in XML
	 * @var mixed
	 */
	protected $_showXmlItems = FALSE;

	/**
	 * Show items in XML
	 * @param mixed $showXmlItems TRUE|FALSE|'not canceled'
	 * @return self
	 */
	public function showXmlItems($showXmlItems = TRUE)
	{
		$this->_showXmlItems = $showXmlItems;
		return $this;
	}

	/**
	 * Show delivery data in XML
	 * @var boolean
	 */
	protected $_showXmlDelivery = FALSE;

	/**
	 * Show delivery in XML
	 * @param boolean $showXmlDelivery
	 * @return self
	 */
	public function showXmlDelivery($showXmlDelivery = TRUE)
	{
		$this->_showXmlDelivery = $showXmlDelivery;
		return $this;
	}

	/**
	 * Show payment systems data in XML
	 * @var boolean
	 */
	protected $_showXmlPaymentSystem = FALSE;

	/**
	 * Show payment system in XML
	 * @param boolean $showXmlPaymentSystem
	 * @return self
	 */
	public function showXmlPaymentSystem($showXmlPaymentSystem = TRUE)
	{
		$this->_showXmlPaymentSystem = $showXmlPaymentSystem;
		return $this;
	}

	/**
	 * Show order statuses data in XML
	 * @var boolean
	 */
	protected $_showXmlOrderStatus = FALSE;

	/**
	 * Show order's status in XML
	 * @param boolean $showXmlOrderStatus
	 * @return self
	 */
	public function showXmlOrderStatus($showXmlOrderStatus = TRUE)
	{
		$this->_showXmlOrderStatus = $showXmlOrderStatus;
		return $this;
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Sort properties values in XML
	 * @var mixed
	 */
	protected $_xmlSortPropertiesValues = TRUE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE, $xmlSortPropertiesValues = TRUE)
	{
		$this->_showXmlProperties = is_array($showXmlProperties)
			? array_combine($showXmlProperties, $showXmlProperties)
			: $showXmlProperties;

		$this->_xmlSortPropertiesValues = $xmlSortPropertiesValues;

		return $this;
	}

	/**
	 * Show media in XML
	 * @var boolean
	 */
	protected $_showXmlMedia = FALSE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlMedia($showXmlMedia = TRUE)
	{
		$this->_showXmlMedia = $showXmlMedia;

		return $this;
	}

	/**
	 * Show comments data in XML
	 * @var boolean
	 */
	protected $_showXmlComments = FALSE;

	/**
	 * Add comments XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlComments($showXmlComments = TRUE)
	{
		$this->_showXmlComments = $showXmlComments;
		return $this;
	}

	/**
	 * Show comments rating data in XML
	 * @var boolean
	 */
	protected $_showXmlCommentsRating = FALSE;

	/**
	 * Add Comments Rating XML to item
	 * @param boolean $showXmlComments mode
	 * @return self
	 */
	public function showXmlCommentsRating($showXmlCommentsRating = TRUE)
	{
		$this->_showXmlCommentsRating = $showXmlCommentsRating;
		return $this;
	}

	/**
	 * What comments show in XML? (active|inactive|all)
	 * @var string
	 */
	protected $_commentsActivity = 'active';

	/**
	 * Set comments filter rule
	 * @param string $commentsActivity (active|inactive|all)
	 * @return self
	 */
	public function commentsActivity($commentsActivity = 'active')
	{
		$this->_commentsActivity = $commentsActivity;
		return $this;
	}

	/**
	 * Show shop order properties in XML
	 * @var boolean
	 */
	protected $_showXmlCommentProperties = FALSE;

	/**
	 * Show shop order properties in XML
	 * @param boolean $showXmlCommentProperties mode
	 * @return self
	 */
	public function showXmlCommentProperties($showXmlCommentProperties = TRUE)
	{
		$this->_showXmlCommentProperties = is_array($showXmlCommentProperties)
			? array_combine($showXmlCommentProperties, $showXmlCommentProperties)
			: $showXmlCommentProperties;

		return $this;
	}

	/**
	 * Array of comments, [parent_id] => array(comments)
	 * @var array
	 */
	protected $_aComments = array();

	/**
	 * Set array of comments for getXml()
	 * @param array $aComments
	 * @return self
	 */
	public function setComments(array $aComments)
	{
		$this->_aComments = $aComments;
		return $this;
	}


	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_order.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event shop_order.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this
			->clearXmlTags();

		if ($this->shop_currency_id)
		{
			$amount = $this->getAmount();

			$this
				->addXmlTag('amount', $amount, array(
					'formatted' => $this->Shop_Currency->format($amount),
					'formattedWithCurrency' => $this->Shop_Currency->formatWithCurrency($amount))
				);

			$this->_showXmlCurrency && $this->addEntity($this->Shop_Currency);
		}

		$this
			->addXmlTag('payment_datetime', $this->payment_datetime == '0000-00-00 00:00:00'
				? $this->payment_datetime
				: Core_Date::strftime($this->Shop->format_datetime, Core_Date::sql2timestamp($this->payment_datetime)))
			->addXmlTag('status_datetime', $this->status_datetime == '0000-00-00 00:00:00'
				? $this->status_datetime
				: Core_Date::strftime($this->Shop->format_datetime, Core_Date::sql2timestamp($this->status_datetime)))
			->addXmlTag('date', $this->datetime == '0000-00-00 00:00:00'
				? $this->datetime
				: Core_Date::strftime($this->Shop->format_date, Core_Date::sql2timestamp($this->datetime)))
			->addXmlTag('datetime', $this->datetime == '0000-00-00 00:00:00'
				? $this->datetime
				: Core_Date::strftime($this->Shop->format_datetime, Core_Date::sql2timestamp($this->datetime)));

		$this->_isTagAvailable('dir')
			&& $this->addXmlTag('dir', Core_Page::instance()->shopCDN . $this->getOrderHref());

		$this->source_id && $this->addEntity(
			$this->Source->clearEntities()
		);

		if (($this->_showXmlComments || $this->_showXmlCommentsRating) && Core::moduleIsActive('comment'))
		{
			$this->_aComments = array();

			$gradeSum = $gradeCount = 0;

			$oComments = $this->Comments;
			$oComments->queryBuilder()
				->orderBy('datetime', 'DESC');

			// учитываем заданную активность комментариев
			$this->_commentsActivity = strtolower($this->_commentsActivity);
			if ($this->_commentsActivity != 'all')
			{
				$oComments->queryBuilder()
					->where('active', '=', $this->_commentsActivity == 'inactive' ? 0 : 1);
			}

			Core_Event::notify($this->_modelName . '.onBeforeSelectComments', $this, array($oComments));

			$aComments = $oComments->findAll();
			foreach ($aComments as $oComment)
			{
				if ($oComment->grade > 0)
				{
					$gradeSum += $oComment->grade;
					$gradeCount++;
				}

				$this->_showXmlComments
					&& $this->_aComments[$oComment->parent_id][] = $oComment;
			}

			// Средняя оценка
			$avgGrade = $gradeCount > 0
				? $gradeSum / $gradeCount
				: 0;

			$fractionalPart = $avgGrade - floor($avgGrade);
			$avgGradeRounded = floor($avgGrade);

			if ($fractionalPart >= 0.25 && $fractionalPart < 0.75)
			{
				$avgGradeRounded += 0.5;
			}
			elseif ($fractionalPart >= 0.75)
			{
				$avgGradeRounded += 1;
			}

			$this->_isTagAvailable('comments_count') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_count')
					->value(count($aComments))
			);

			$this->_isTagAvailable('comments_grade_sum') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_grade_sum')
					->value($gradeSum)
			);

			$this->_isTagAvailable('comments_grade_count') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_grade_count')
					->value($gradeCount)
			);

			$this->_isTagAvailable('comments_average_grade') && $this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('comments_average_grade')
					->addAttribute('value', $avgGrade)
					->value($avgGradeRounded)
			);

			$this->_showXmlComments
				&& $this->_addComments(0, $this);

			$this->_aComments = array();
		}

		if ($this->_showXmlProperties)
		{
			$aProperty_Values = is_array($this->_showXmlProperties)
				? Property_Controller_Value::getPropertiesValues($this->_showXmlProperties, $this->id, FALSE, $this->_xmlSortPropertiesValues)
				: $this->getPropertyValues(TRUE, array(), $this->_xmlSortPropertiesValues);

			foreach ($aProperty_Values as $oProperty_Value)
			{
				$this->_preparePropertyValue($oProperty_Value);
			}

			// Add all values
			$this->addEntities($aProperty_Values);
		}

		if ($this->_showXmlCountry && $this->shop_country_id)
		{
			$oShop_Country = $this->Shop_Country->clearEntities();

			if ($this->shop_country_location_id)
			{
				$oShop_Country_Location = $this->Shop_Country_Location;
				$oShop_Country->addEntity($oShop_Country_Location);

				if ($this->shop_country_location_city_id)
				{
					$oShop_Country_Location_City = $this->Shop_Country_Location_City;
					$oShop_Country_Location->addEntity($oShop_Country_Location_City);

					if ($this->shop_country_location_city_area_id)
					{
						$oShop_Country_Location_City_Area = $this->Shop_Country_Location_City_Area;
						$oShop_Country_Location_City->addEntity($oShop_Country_Location_City_Area);
					}
				}
			}

			$this->addEntity($oShop_Country);
		}

		if ($this->_showXmlDelivery && $this->shop_delivery_id)
		{
			$oShop_Delivery = $this->Shop_Delivery->clearEntities();
			$this->addEntity($oShop_Delivery);

			$this->shop_delivery_condition_id && $oShop_Delivery->addEntity(
				$this->Shop_Delivery_Condition->clearEntities()
			);
		}

		$this->_showXmlPaymentSystem && $this->shop_payment_system_id && $this->addEntity($this->Shop_Payment_System);

		$this->_showXmlOrderStatus && $this->shop_order_status_id && $this->addEntity($this->Shop_Order_Status);

		$this->_showXmlSiteuser && $this->siteuser_id && Core::moduleIsActive('siteuser') && $this->addEntity(
			$this->Siteuser->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
		);

		if ($this->_showXmlItems)
		{
			$total_amount = $total_tax = 0;

			$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);
			foreach ($aShop_Order_Items as $oShop_Order_Item)
			{
				if ($this->_showXmlItems !== 'not canceled' || !$oShop_Order_Item->isCanceled())
				{
					$this->addEntity(
						$oShop_Order_Item->clearEntities()
							->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
							->showXmlMedia($this->_showXmlMedia)
							->showXmlItem(TRUE)
					);

					$total_tax += $oShop_Order_Item->getTax() * $oShop_Order_Item->quantity;
					$total_amount += $oShop_Order_Item->getAmount();
				}
			}

			// Total order amount
			$total_amount = Shop_Controller::instance()->round($total_amount);
			$total_tax = Shop_Controller::instance()->round($total_tax);

			$this->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('total_amount')
					->value($total_amount)
					->addAttribute('formatted', $this->Shop_Currency->format($total_amount))
					->addAttribute('formattedWithCurrency', $this->Shop_Currency->formatWithCurrency($total_amount))
			)->addEntity(
				Core::factory('Core_Xml_Entity')
					->name('total_tax')
					->value($total_tax)
					->addAttribute('formatted', $this->Shop_Currency->format($total_tax))
					->addAttribute('formattedWithCurrency', $this->Shop_Currency->formatWithCurrency($total_tax))
			);
		}

		return $this;
	}

	/**
	 * Add comments into object XML
	 * @param int $parent_id parent comment id
	 * @param Core_Entity $parentObject object
	 * @return self
	 * @hostcms-event shop_item.onBeforeAddComments
	 * @hostcms-event shop_item.onAfterAddComments
	 */
	protected function _addComments($parent_id, $parentObject)
	{
		Core_Event::notify($this->_modelName . '.onBeforeAddComments', $this, array(
			$parent_id, $parentObject, $this->_aComments
		));

		if (isset($this->_aComments[$parent_id]))
		{
			foreach ($this->_aComments[$parent_id] as $oComment)
			{
				$parentObject->addEntity($oComment
					->clearEntities()
					->showXmlProperties($this->_showXmlCommentProperties, $this->_xmlSortPropertiesValues)
					// ->showXmlSiteuserProperties($this->_showXmlSiteuserProperties)
					// ->showXmlVotes($this->_showXmlVotes)
					->dateFormat($this->Shop->format_date)
					->dateTimeFormat($this->Shop->format_datetime)
				);

				$this->_addComments($oComment->id, $oComment);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterAddComments', $this, array(
			$parent_id, $parentObject, $this->_aComments
		));

		return $this;
	}

	/**
	 * Prepare Property Value
	 * @param Property_Value_Model $oProperty_Value
	 */
	protected function _preparePropertyValue($oProperty_Value)
	{
		switch ($oProperty_Value->Property->type)
		{
			case 2:
				$oProperty_Value
					->setHref($this->getOrderHref())
					->setDir($this->getOrderPath());
			break;
			case 5: // Элемент информационной системы
			case 12: // Товар интернет-магазина
			case 13: // Группа информационной системы
			case 14: // Группа интернет-магазина
				$oProperty_Value->showXmlMedia($this->_showXmlMedia);
			break;
			case 8:
				$oProperty_Value->dateFormat($this->Shop->format_date);
			break;
			case 9:
				$oProperty_Value->dateTimeFormat($this->Shop->format_datetime);
			break;
		}
	}

	protected $_oShop_Discountcard = NULL;

	/**
	 * Pay the order
	 * @return self
	 * @hostcms-event shop_order.onBeforePaid
	 * @hostcms-event shop_order.onAfterPaid
	 */
	public function paid()
	{
		Core_Event::notify($this->_modelName . '.onBeforePaid', $this);

		if (!$this->paid)
		{
			$this->paid = 1;
			$this->payment_datetime = Core_Date::timestamp2sql(time());
			$this->save();

			// Получаем/выпускаем карту до начисления бонусов в _paidTransaction()
			if ($this->siteuser_id && Core::moduleIsActive('siteuser'))
			{
				$aShop_Discountcards = $this->Siteuser->Shop_Discountcards->getAllByShop_id($this->shop_id, FALSE);

				if (isset($aShop_Discountcards[0]))
				{
					$aShop_Discountcards[0]->active
						&& $this->_oShop_Discountcard = $aShop_Discountcards[0];
				}
				else
				{
					$oShop = $this->Shop;

					if ($oShop->issue_discountcard)
					{
						$oShop_Discountcard = Core_Entity::factory('Shop_Discountcard');
						$oShop_Discountcard->shop_id = $this->shop_id;
						$oShop_Discountcard->siteuser_id = $this->siteuser_id;
						$oShop_Discountcard->setSiteuserAmount();
						$oShop_Discountcard->number = '';
						$oShop_Discountcard->save(); // create ID

						// Uses number template
						$oShop_Discountcard->number = $oShop_Discountcard->generate();

						$this->_oShop_Discountcard = $oShop_Discountcard;
					}
				}
			}

			// Списать товары
			$this->_paidTransaction();

			// Удалить зарезервированные товары
			$this->deleteReservedItems();

			// Уведомление о событии оплаты заказа
			$this->_createNotification();

			// Дисконтная карта
			$this->_paidShopDiscountcard();

			// Статус заказа от платежной системы
			if ($this->shop_payment_system_id
				&& $this->Shop_Payment_System->shop_order_status_id
				&& $this->shop_order_status_id != $this->Shop_Payment_System->shop_order_status_id)
			{
				$this->shop_order_status_id = $this->Shop_Payment_System->shop_order_status_id;
				$this->save();

				$this->historyPushChangeStatus();
				$this->notifyBotsChangeStatus();

				if (Core::moduleIsActive('webhook'))
				{
					Webhook_Controller::notify('onShopOrderChangeStatus', $this);
				}
			}

			// История
			$this->historyPushPaid();

			if (Core::moduleIsActive('webhook'))
			{
				Webhook_Controller::notify('onShopOrderPaid', $this);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterPaid', $this);

		return $this->save();
	}

	protected function _paidShopDiscountcard()
	{
		if ($this->_oShop_Discountcard)
		{
			$mode = $this->paid == 0 ? -1 : 1;

			// При вызове в paid() в данный момент модель не сохранена и заказ не числится оплаченным,
			// поэтому после создания карты ее сумма не включает текущий заказ
			$this->_oShop_Discountcard->amount += $this->getAmount() * $mode;

			$this->_oShop_Discountcard->save();

			// update level
			$this->_oShop_Discountcard->checkLevel();
		}

		return $this;
	}

	/**
	 * Create notification for subscribers
	 * @return self
	 */
	protected function _createNotification()
	{
		$oModule = Core::$modulesList['shop'];

		if ($oModule && Core::moduleIsActive('notification'))
		{
			$aUserIDs = array();

			$oNotification_Subscribers = Core_Entity::factory('Notification_Subscriber');
			$oNotification_Subscribers->queryBuilder()
				->where('notification_subscribers.module_id', '=', $oModule->id)
				->where('notification_subscribers.type', '=', 0)
				->where('notification_subscribers.entity_id', '=', $this->Shop->id);

			$aNotification_Subscribers = $oNotification_Subscribers->findAll(FALSE);

			foreach ($aNotification_Subscribers as $oNotification_Subscriber)
			{
				$aUserIDs[] = $oNotification_Subscriber->user_id;
			}

			// Ответственные сотрудники
			if (Core::moduleIsActive('siteuser') && $this->siteuser_id)
			{
				$aSiteuser_Users = $this->Siteuser->Siteuser_Users->findAll(FALSE);
				foreach ($aSiteuser_Users as $oSiteuser_User)
				{
					!in_array($oSiteuser_User->user_id, $aUserIDs)
						&& $aUserIDs[] = $oSiteuser_User->user_id;
				}
			}

			if (count($aUserIDs))
			{
				$sCompany = $this->getCustomerName();

				$oNotification = Core_Entity::factory('Notification');
				$oNotification
					->title(Core::_('Shop_Order.notification_paid_order', strip_tags($this->invoice), FALSE))
					->description(Core::_('Shop_Order.notification_new_order_description', strip_tags($sCompany), $this->Shop_Currency->formatWithCurrency($this->getAmount()), FALSE))
					->datetime(Core_Date::timestamp2sql(time()))
					->module_id($oModule->id)
					->type(2) // Оплаченный заказ
					->entity_id($this->id)
					->save();

				// Связываем уведомление с сотрудником
				foreach ($aUserIDs as $user_id)
				{
					Core_Entity::factory('User', $user_id)->add($oNotification);
				}
			}
		}

		return $this;
	}

	/**
	 * Cancel payment
	 * @return self
	 * @hostcms-event shop_order.onBeforeCancelPaid
	 * @hostcms-event shop_order.onAfterCancelPaid
	 */
	public function cancelPaid()
	{
		Core_Event::notify($this->_modelName . '.onBeforeCancelPaid', $this);

		if ($this->paid)
		{
			$this->paid = 0;
			$this->payment_datetime = '0000-00-00 00:00:00';

			// Получаем карту до списания бонусов в _paidTransaction()
			if ($this->siteuser_id && Core::moduleIsActive('siteuser'))
			{
				$aShop_Discountcards = $this->Siteuser->Shop_Discountcards->getAllByShop_id($this->shop_id);

				if (isset($aShop_Discountcards[0]))
				{
					$aShop_Discountcards[0]->active
						&& $this->_oShop_Discountcard = $aShop_Discountcards[0];
				}
			}

			// Вернуть списанные товары
			$this->_paidTransaction();

			// Удалить зарезервированные товары
			$this->deleteReservedItems();

			// Удалить электронные товары
			$this->_deleteDigitalItems();

			// Дисконтная карта
			$this->_paidShopDiscountcard();

			// История
			$this->historyPushPaid();

			if (Core::moduleIsActive('webhook'))
			{
				Webhook_Controller::notify('onShopOrderCancelPaid', $this);
			}
		}

		Core_Event::notify($this->_modelName . '.onAfterCancelPaid', $this);

		return $this->save();
	}

	/*
	 * Check shop order item statuses
	 * @return self
	 */
	public function checkShopOrderItemStatuses()
	{
		$aTmp = array();

		$aShop_Order_Items = $this->Shop_Order_Items->getAllByType(0, FALSE);

		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			if ($oShop_Order_Item->shop_order_item_status_id)
			{
				$aTmp[$oShop_Order_Item->shop_order_item_status_id][] = $oShop_Order_Item->id;
			}
		}

		// У всех товаров один статус
		if (count($aTmp) == 1)
		{
			$shop_order_item_status_id = key($aTmp);
			$oShop_Order_Item_Status = Core_Entity::factory('Shop_Order_Item_Status', $shop_order_item_status_id);

			if ($oShop_Order_Item_Status->shop_order_status_id != $this->shop_order_status_id)
			{
				if ($oShop_Order_Item_Status->shop_order_status_id)
				{
					$this->shop_order_status_id = $oShop_Order_Item_Status->shop_order_status_id;
					$this->save();

					$this->historyPushChangeStatus();
					$this->notifyBotsChangeStatus();

					if (Core::moduleIsActive('webhook'))
					{
						Webhook_Controller::notify('onShopOrderChangeStatus', $this);
					}
				}

				$oShop_Order_Item_Status->canceled
					&& $this->cancel();
			}
		}

		return $this;
	}

	/**
	 * Delete digital items
	 * @return self
	 */
	protected function _deleteDigitalItems()
	{
		$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			$oShop_Item = $oShop_Order_Item->Shop_Item;

			if ($oShop_Item->type == 1)
			{
				$aShop_Order_Item_Digitals = $oShop_Order_Item->Shop_Order_Item_Digitals->findAll(FALSE);

				foreach ($aShop_Order_Item_Digitals as $oShop_Order_Item_Digital)
				{
					$oShop_Item_Digital = $oShop_Order_Item_Digital->Shop_Item_Digital;

					if ($oShop_Item_Digital->id)
					{
						if ($oShop_Item_Digital->count != -1)
						{
							$oShop_Item_Digital->count += $oShop_Order_Item->quantity;
							$oShop_Item_Digital->save();
						}

						$oShop_Order_Item_Digital->delete();
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Reserve items for order
	 * @return self
	 */
	public function reserveItems()
	{
		$this->deleteReservedItems();

		$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			$oShop_Item_Reserved = Core_Entity::factory('Shop_Item_Reserved');
			$oShop_Item_Reserved->shop_order_id = $this->id;
			$oShop_Item_Reserved->shop_item_id = intval($oShop_Order_Item->shop_item_id);
			$oShop_Item_Reserved->shop_warehouse_id = intval($oShop_Order_Item->shop_warehouse_id);
			$oShop_Item_Reserved->count = $oShop_Order_Item->quantity;
			$oShop_Item_Reserved->save();
		}

		return $this;
	}

	/**
	 * Delete reserved items for order
	 * @return self
	 */
	public function deleteReservedItems()
	{
		$this->Shop_Item_Reserveds->deleteAll(FALSE);

		return $this;
	}

	/**
	 * Списание или возврат товара на склад, начисление и стронирование операций по лицевому счету
	 * @return self
	 * @hostcms-event shop_order.onAfterSaveShopPurchaseDiscountCoupon
	 * @hostcms-event shop_order.onAfterSaveSiteuserTransaction
	 */
	protected function _paidTransaction()
	{
		$oShop = $this->Shop;

		$mode = $this->paid == 0 ? -1 : 1;

		// Получаем список товаров заказа
		$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);

		$fTotalAmount = $fTotalDiscount = 0;

		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			$oShop_Item = $oShop_Order_Item->Shop_Item;

			$fAmount = $oShop_Order_Item->getAmount();

			// Электронный товар
			if ($oShop_Item->type == 1)
			{
				if ($this->paid == 1 && $oShop_Order_Item->Shop_Order_Item_Digitals->getCount(FALSE) == 0)
				{
					$oShop_Order_Item->addDigitalItems($oShop_Item);
				}
			}
			// Пополнение лицевого счета
			elseif ($oShop_Order_Item->type == 2 && Core::moduleIsActive('siteuser'))
			{
				// Проведение/стронирование транзакции
				$oShop_Siteuser_Transaction = Core_Entity::factory('Shop_Siteuser_Transaction');
				$oShop_Siteuser_Transaction->shop_id = $oShop->id;
				$oShop_Siteuser_Transaction->siteuser_id = $this->siteuser_id;
				$oShop_Siteuser_Transaction->active = 1;

				// Определяем коэффициент пересчета
				$fCurrencyCoefficient = $this->Shop_Currency->id > 0 && $oShop->Shop_Currency->id > 0
					? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
						$this->Shop_Currency, $oShop->Shop_Currency
					)
					: 0;

				$oShop_Siteuser_Transaction->amount = $fAmount * $mode;
				$oShop_Siteuser_Transaction->shop_currency_id = $this->shop_currency_id;
				$oShop_Siteuser_Transaction->amount_base_currency = $oShop_Siteuser_Transaction->amount * $fCurrencyCoefficient;
				$oShop_Siteuser_Transaction->shop_order_id = $this->id;
				$oShop_Siteuser_Transaction->type = 0;
				$oShop_Siteuser_Transaction->description = $oShop_Order_Item->name;
				$oShop_Siteuser_Transaction->save();
			}
			// Комплект
			elseif ($oShop_Item->type == 3 && $this->paid == 1)
			{
				$aShop_Item_Sets = $oShop_Item->Shop_Item_Sets->findAll(FALSE);
				foreach ($aShop_Item_Sets as $oShop_Item_Set)
				{
					$oShop_Order_Item->addDigitalItems(
						Core_Entity::factory('Shop_Item', $oShop_Item_Set->shop_item_set_id)
					);
				}
			}
			// Сертификат
			elseif ($oShop_Item->type == 4 && $this->paid == 1)
			{
				$oShop_Item_Certificate = $oShop_Item->Shop_Item_Certificate;

				if (!is_null($oShop_Item_Certificate->id) && $oShop_Item_Certificate->shop_purchase_discount_id)
				{
					$oShop_Purchase_Discount_Coupon = Core_Entity::factory('Shop_Purchase_Discount_Coupon');
					$oShop_Purchase_Discount_Coupon->shop_purchase_discount_id = $oShop_Item_Certificate->shop_purchase_discount_id;
					$oShop_Purchase_Discount_Coupon->shop_order_id = $this->id;
					$oShop_Purchase_Discount_Coupon->name = $oShop_Item->name;
					$oShop_Purchase_Discount_Coupon->start_datetime = Core_Date::timestamp2sql(time());
					$oShop_Purchase_Discount_Coupon->end_datetime = $oShop_Item_Certificate->Shop_Purchase_Discount->end_datetime;
					$oShop_Purchase_Discount_Coupon->text = '';
					$oShop_Purchase_Discount_Coupon->count = $oShop_Order_Item->quantity;
					$oShop_Purchase_Discount_Coupon->save();

					Core_Event::notify($this->_modelName . '.onAfterSaveShopPurchaseDiscountCoupon', $this, array($oShop_Purchase_Discount_Coupon, $oShop_Item));

					if (strlen($oShop->certificate_template))
					{
						$oCore_Templater = new Core_Templater();
						$coupon = $oCore_Templater
							->addObject('shop', $oShop)
							->addObject('this', $this)
							->addObject('coupon_id', $oShop_Purchase_Discount_Coupon->id)
							->setTemplate($oShop->certificate_template)
							->execute();
					}
					else
					{
						$coupon = 'GIFT-' . rand(100000, 999999) . Core_Str::generateChars(7);
					}

					$oShop_Purchase_Discount_Coupon->text = $coupon;
					$oShop_Purchase_Discount_Coupon->save();

					if (strlen(trim($this->email)))
					{
						$oCore_Meta = new Core_Meta();
						$oCore_Meta
							->addObject('shop', $oShop)
							->addObject('shop_item', $oShop_Item)
							->addObject('this', $this)
							->addObject('coupon', $oShop_Purchase_Discount_Coupon)
							->addObject('code', $coupon);

						$senderName = $oShop->Site->sender_name != ''
							? $oShop->Site->sender_name
							: $oShop->name;

						Core_Mail::instance()
							->clear()
							->from($oShop->getFirstEmail())
							->senderName($senderName)
							->to($this->email)
							->subject($oCore_Meta->apply($oShop->certificate_subject))
							->message($oCore_Meta->apply($oShop->certificate_text))
							->contentType('text/html')
							->header('X-HostCMS-Reason', 'Certificate')
							->header('Precedence', 'bulk')
							->messageId()
							->send();
					}
				}
				else
				{
					Core_Log::instance()->clear()
						->status(Core_Log::$ERROR)
						->write('Wrong Shop_Item_Certificate Settings');
				}
			}

			in_array($oShop_Order_Item->type, array(3, 4, 5))
				? $fTotalDiscount += $fAmount
				: $fTotalAmount += $fAmount;

			$oShop_Item->clearCache();
		}

		// Бонусы начисляем в отдельном цикле
		if ($this->paid == 1)
		{
			if ($fTotalAmount > 0 && $this->_oShop_Discountcard)
			{
				// Рассчитываем коэффициент скидки для уменьшения цены контретной строки заказа
				$multiplier = 1 - abs($fTotalDiscount) / $fTotalAmount;

				if ($multiplier <= 1)
				{
					foreach ($aShop_Order_Items as $oShop_Order_Item)
					{
						$oShop_Item = $oShop_Order_Item->Shop_Item;

						// Начисление/стронирование бонусов
						if ($oShop_Item->id)
						{
							$fAmount = $oShop_Order_Item->getAmount() * $multiplier;

							$oShop_Item_Controller = new Shop_Item_Controller();
							$aBonuses = $oShop_Item_Controller->getBonuses($oShop_Item, $fAmount);

							if ($aBonuses['total'])
							{
								foreach ($aBonuses['bonuses'] as $oShop_Bonus)
								{
									$oShop_Discountcard_Bonus = Core_Entity::factory('Shop_Discountcard_Bonus');
									$oShop_Discountcard_Bonus->shop_order_id = $this->id;

									$oShop_Discountcard_Bonus->datetime = $oShop_Bonus->accrual_date == '0000-00-00 00:00:00'
										? Core_Date::timestamp2sql(strtotime('+' . $oShop_Bonus->accrual_days . ' day'))
										: $oShop_Bonus->accrual_date;

									$oShop_Discountcard_Bonus->expired = Core_Date::timestamp2sql(
										strtotime('+' . $oShop_Bonus->expire_days . ' day', Core_Date::sql2timestamp($oShop_Discountcard_Bonus->datetime))
									);

									$oShop_Discountcard_Bonus->amount = $oShop_Bonus->type == 0
										? $fAmount * $oShop_Bonus->value / 100
										: $oShop_Bonus->value;

									// Тип зачисления по умолчанию
									$oShop_Discountcard_Bonus_Type = $oShop->Shop_Discountcard_Bonus_Types->getDefault();
									!is_null($oShop_Discountcard_Bonus_Type)
										&& $oShop_Discountcard_Bonus->shop_discountcard_bonus_type_id = $oShop_Discountcard_Bonus_Type->id;

									$this->_oShop_Discountcard->add($oShop_Discountcard_Bonus);
								}
							}
						}
					}
				}
			}
		}
		else
		{
			$this->Shop_Discountcard_Bonuses->deleteAll(FALSE);

			$this->Shop_Purchase_Discount_Coupons->deleteAll(FALSE);
		}

		// Проводки по складам
		if ($oShop->write_off_paid_items)
		{
			$this->paid
				&& $this->post();
		}

		// Транзакции пользователю за уровни партнерской программы
		if ($this->siteuser_id && Core::moduleIsActive('siteuser'))
		{
			$aSiteusers = array();
			// Получаем все дерево аффилиатов от текущего пользователя до самого верхнего в иерархии
			$level = 1; // Уровень начинается с 1
			$oSiteuserAffiliate = $this->Siteuser;
			do
			{
				$oSiteuserAffiliate = $oSiteuserAffiliate->Affiliate;

				if ($oSiteuserAffiliate->id)
				{
					$aSiteusers[$level] = $oSiteuserAffiliate;
				}
				else
				{
					break;
				}
				$level++;
			} while ($oSiteuserAffiliate->id && $level < 30);

			// Есть аффилиаты, приведшие пользователя
			if (count($aSiteusers))
			{
				// Сумма заказа
				$fOrderAmount = $this->getAmount();

				// Количество товара в заказе
				$iQuantity = $this->getQuantity();

				// Цикл по партнерским программам магазина
				$oAffiliate_Plans = $oShop->Affiliate_Plans;
				$oAffiliate_Plans->queryBuilder()
					->where('affiliate_plans.min_count_of_items', '<=', $iQuantity)
					->where('affiliate_plans.min_amount_of_items', '<=', $fOrderAmount);

				$aAffiliate_Plans = $oAffiliate_Plans->findAll();
				foreach ($aAffiliate_Plans as $oAffiliate_Plan)
				{
					// Не включать стоимость доставки в расчет вознаграждения, вычитаем из суммы заказа
					if ($oAffiliate_Plan->include_delivery == 0)
					{
						$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);
						foreach ($aShop_Order_Items as $oShop_Order_Item)
						{
							// Товар является доставкой
							if ($oShop_Order_Item->type == 1)
							{
								$fOrderAmount -= $oShop_Order_Item->getAmount();
							}
						}
					}

					$aAffiliate_Plan_Levels = $oAffiliate_Plan->Affiliate_Plan_Levels->findAll();
					foreach ($aAffiliate_Plan_Levels as $oAffiliate_Plan_Level)
					{
						if (isset($aSiteusers[$oAffiliate_Plan_Level->level]))
						{
							// Получаем сумму
							$sum = $oAffiliate_Plan_Level->type == 0
								? $fOrderAmount * ($oAffiliate_Plan_Level->percent / 100)
								: $oAffiliate_Plan_Level->value;

							if ($sum > 0)
							{
								// Транзакция начисление/списание бонусов
								$oShop_Siteuser_Transaction = Core_Entity::factory('Shop_Siteuser_Transaction');
								$oShop_Siteuser_Transaction->shop_id = $oShop->id;
								$oShop_Siteuser_Transaction->siteuser_id = $aSiteusers[$oAffiliate_Plan_Level->level]->id;
								$oShop_Siteuser_Transaction->active = 1;
								$oShop_Siteuser_Transaction->amount = $sum * $mode;

								// Сумма в виде процентов, расчитывается в валюте заказа
								if ($oAffiliate_Plan_Level->type == 0)
								{
									// Определяем коэффициент пересчета
									$fCurrencyCoefficient = $this->Shop_Currency->id > 0 && $oShop->Shop_Currency->id > 0
										? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
											$this->Shop_Currency, $oShop->Shop_Currency
										)
										: 0;

									$oShop_Siteuser_Transaction->amount_base_currency = $oShop_Siteuser_Transaction->amount * $fCurrencyCoefficient;
									$oShop_Siteuser_Transaction->shop_currency_id = $this->shop_currency_id;
								}
								else
								{
									// Фиксированное вознаграждение только в валюте магазина
									$oShop_Siteuser_Transaction->amount_base_currency = $oShop_Siteuser_Transaction->amount;
									$oShop_Siteuser_Transaction->shop_currency_id = $oShop->shop_currency_id;
								}

								$oShop_Siteuser_Transaction->shop_order_id = $this->id;
								$oShop_Siteuser_Transaction->type = 1;
								$oShop_Siteuser_Transaction->description = Core::_('Shop.form_edit_add_shop_special_prices_price', $this->id);
								$oShop_Siteuser_Transaction->save();

								Core_Event::notify($this->_modelName . '.onAfterSaveSiteuserTransaction', $this, array($oShop_Siteuser_Transaction));
							}
						}
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Add entries
	 * @return self
	 * @hostcms-event shop_order.onBeforePost
	 * @hostcms-event shop_order.onAfterPost
	 */
	public function post()
	{
		Core_Event::notify($this->_modelName . '.onBeforePost', $this);

		if (!$this->posted)
		{
			$oShop = $this->Shop;

			// Списание/начисление товаров
			if ($oShop->write_off_paid_items)
			{
				$aWriteoff = $aTmp = $aRecount = array();

				// Exists Shop_Warehouse_Entry
				$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, $this->getEntityType());

				foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
				{
					$aTmp[$oShop_Warehouse_Entry->shop_item_id][] = $oShop_Warehouse_Entry;
				}
				unset($aShop_Warehouse_Entries);

				// Получаем список товаров заказа
				$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);
				foreach ($aShop_Order_Items as $oShop_Order_Item)
				{
					$oShop_Item = $oShop_Order_Item->Shop_Item;
					$oShop_Warehouse = $oShop_Order_Item->shop_warehouse_id
						? $oShop_Order_Item->Shop_Warehouse
						: $oShop->Shop_Warehouses->getDefault();

					if (!is_null($oShop_Warehouse) && $oShop_Item->id)
					{
						$aWriteoff[] = array(
							'shop_item_id' => $oShop_Item->id,
							'shop_warehouse_id' => $oShop_Warehouse->id,
							'count' => $oShop_Order_Item->quantity
						);
					}

					// Комплект
					if ($oShop_Item->type == 3)
					{
						if (!is_null($oShop_Warehouse) && $oShop_Item->id)
						{
							$oShop_Warehouse = $oShop_Order_Item->shop_warehouse_id
								? $oShop_Order_Item->Shop_Warehouse
								: $oShop->Shop_Warehouses->getDefault();

							$aShop_Item_Sets = $oShop_Item->Shop_Item_Sets->findAll(FALSE);
							foreach ($aShop_Item_Sets as $oShop_Item_Set)
							{
								$oShop_Warehouse_Item = $oShop_Warehouse->Shop_Warehouse_Items->getByShopItemId($oShop_Item_Set->shop_item_set_id);

								if (!is_null($oShop_Warehouse_Item))
								{
									$aWriteoff[] = array(
										'shop_item_id' => $oShop_Item_Set->shop_item_set_id,
										'shop_warehouse_id' => $oShop_Warehouse->id,
										'count' => $oShop_Item_Set->count
									);
								}
							}
						}
					}
				}

				// Добавляем проводки для списания заказанных товаров
				foreach ($aWriteoff as $writeoff)
				{
					$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse')->getById($writeoff['shop_warehouse_id']);

					if (!is_null($oShop_Warehouse))
					{
						$shop_item_id = $writeoff['shop_item_id'];

						if (isset($aTmp[$shop_item_id]) && count($aTmp[$shop_item_id]))
						{
							$oShop_Warehouse_Entry = array_shift($aTmp[$shop_item_id]);
						}
						else
						{
							$oShop_Warehouse_Entry = Core_Entity::factory('Shop_Warehouse_Entry');
							$oShop_Warehouse_Entry->setDocument($this->id, $this->getEntityType());
							$oShop_Warehouse_Entry->shop_item_id = $shop_item_id;
						}

						$oShop_Warehouse_Entry->shop_warehouse_id = $oShop_Warehouse->id;
						$oShop_Warehouse_Entry->datetime = Core_Date::timestamp2sql(time()); // Отгрузка проводится текущей датой
						$oShop_Warehouse_Entry->value = -$writeoff['count'];
						$oShop_Warehouse_Entry->save();

						(!isset($aRecount[$oShop_Warehouse->id]) || !in_array($shop_item_id, $aRecount[$oShop_Warehouse->id]))
							&& $aRecount[$oShop_Warehouse->id][] = $shop_item_id;
					}
				}

				// Delete unused Shop_Warehouse_Entries
				foreach ($aTmp as $shop_item_id => $aTmpItems)
				{
					foreach ($aTmpItems as $oShop_Warehouse_Entry)
					{
						$oShop_Warehouse = $oShop_Warehouse_Entry->Shop_Warehouse;

						$oShop_Warehouse_Entry->delete();

						(!isset($aRecount[$oShop_Warehouse->id]) || !in_array($shop_item_id, $aRecount[$oShop_Warehouse->id]))
							&& $aRecount[$oShop_Warehouse->id][] = $shop_item_id;
					}
				}

				// Recount
				foreach ($aRecount as $shop_warehouse_id => $aItemsIDs)
				{
					$oShop_Warehouse = Core_Entity::factory('Shop_Warehouse', $shop_warehouse_id);

					foreach ($aItemsIDs as $shop_item_id)
					{
						// Удаляем все накопительные значения с датой больше, чем дата документа
						Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($shop_item_id, $oShop_Warehouse->id, $this->datetime);

						$rest = $oShop_Warehouse->getRest($shop_item_id);

						if (!is_null($rest))
						{
							// Recount
							$oShop_Warehouse->setRest($shop_item_id, $rest);
						}
					}
				}
			}

			$this->posted = 1;
			$this->save();

			// Удалить зарезервированные товары
			$this->deleteReservedItems();

			$this->historyPushPosted();
		}

		Core_Event::notify($this->_modelName . '.onAfterPost', $this);

		return $this;
	}

	/**
	 * Remove all shop warehouse entries by document
	 * @return self
	 */
	public function unpost()
	{
		if ($this->posted)
		{
			$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, $this->getEntityType());
			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$shop_item_id = $oShop_Warehouse_Entry->shop_item_id;

				$oShop_Warehouse = $oShop_Warehouse_Entry->Shop_Warehouse;

				// Удаляем все накопительные значения с датой больше, чем дата документа
				Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($shop_item_id, $oShop_Warehouse->id, $this->datetime);

				$oShop_Warehouse_Entry->delete();

				$rest = $oShop_Warehouse->getRest($shop_item_id);

				if (!is_null($rest))
				{
					// Recount
					$oShop_Warehouse->setRest($shop_item_id, $rest);
				}
			}

			$this->posted = 0;
			$this->save();

			$this->historyPushPosted();
		}

		return $this;
	}

	/**
	 * Get item path include CMS_FOLDER
	 * @return string
	 */
	public function getOrderPath()
	{
		return $this->Shop->getPath() . '/orders/' . Core_File::getNestingDirPath($this->id, $this->Shop->Site->nesting_level) . '/' . $this->id . '/';
	}

	/**
	 * Get item href
	 * @return string
	 */
	public function getOrderHref()
	{
		return '/' . $this->Shop->getHref() . '/orders/' . Core_File::getNestingDirPath($this->id, $this->Shop->Site->nesting_level) . '/' . $this->id . '/';
	}

	/**
	 * Create directory for item
	 * @return self
	 */
	public function createDir()
	{
		clearstatcache();

		if (!Core_File::isDir($this->getOrderPath()))
		{
			try
			{
				Core_File::mkdir($this->getOrderPath(), CHMOD, TRUE);
			} catch (Exception $e) {}
		}

		return $this;
	}

	/**
	 * Values of all properties of item
	 * Значения всех свойств товара
	 * @param boolean $bCache cache mode status
	 * @param array $aPropertiesId array of properties' IDs
	 * @param boolean $bSorting sort results, default FALSE
	 * @return array Property_Value
	 */
	public function getPropertyValues($bCache = TRUE, $aPropertiesId = array(), $bSorting = FALSE)
	{
		if ($bCache && !is_null($this->_propertyValues))
		{
			return $this->_propertyValues;
		}

		if (!is_array($aPropertiesId) || !count($aPropertiesId))
		{
			$aProperties = Core_Entity::factory('Shop_Order_Property_List', $this->shop_id)
				->Properties
				->findAll();

			$aPropertiesId = array();
			foreach ($aProperties as $oProperty)
			{
				$aPropertiesId[] = $oProperty->id;
			}
		}

		$aReturn = Property_Controller_Value::getPropertiesValues($aPropertiesId, $this->id, $bCache, $bSorting);

		// setHref()
		foreach ($aReturn as $oProperty_Value)
		{
			if ($oProperty_Value->Property->type == 2)
			{
				$oProperty_Value
					->setHref($this->getOrderHref())
					->setDir($this->getOrderPath());
			}
		}

		$bCache && $this->_propertyValues = $aReturn;

		return $aReturn;
	}

	/**
	 * Generate invoice by invoice_template or set invoice like id
	 * @return self
	 */
	public function createInvoice()
	{
		if (strlen($this->Shop->invoice_template))
		{
			$oCore_Templater = new Core_Templater();
			$this->invoice = $oCore_Templater
				->addObject('shop', $this->Shop)
				->addObject('this', $this)
				->addFunction('ordersToday', array($this, 'ordersToday'))
				->addFunction('ordersCurrentMonth', array($this, 'ordersCurrentMonth'))
				->addFunction('ordersCurrentYear', array($this, 'ordersCurrentYear'))
				->setTemplate($this->Shop->invoice_template)
				->execute();
		}
		else
		{
			$this->invoice = $this->id;
		}

		return $this;
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_order.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();
		$newObject->guid = Core_Guid::get();

		$newObject->datetime = $newObject->status_datetime
			= $newObject->acceptance_report_datetime = $newObject->vat_invoice_datetime = Core_Date::timestamp2sql(time());

		$newObject->payment_datetime = '0000-00-00 00:00:00';
		$newObject->canceled = $newObject->paid = $newObject->posted = 0;

		$newObject->acceptance_report = $newObject->vat_invoice = $newObject->id;

		$newObject->save();

		$newObject->createInvoice();
		$newObject->save();

		$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			$newObject->add(clone $oShop_Order_Item);
		}

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Add order CommerceML
	 * @param Core_SimpleXMLElement $oXml
	 * @hostcms-event shop_order.onBeforeGetCmlUserName
	 * @hostcms-event shop_order.onAddCmlSelectShopOrderItems
	 */
	public function addCml(Core_SimpleXMLElement $oXml)
	{
		$oOrderXml = $oXml->addChild('Документ');
		$oOrderXml->addChild('Ид', $this->id);
		$oOrderXml->addChild('Номер', $this->invoice);
		$datetime = explode(' ', $this->datetime);
		$date = $datetime[0];
		$time = $datetime[1];
		$oOrderXml->addChild('Дата', $date);
		$oOrderXml->addChild('ХозОперация', 'Заказ товара');
		$oOrderXml->addChild('Роль', 'Продавец');
		$oOrderXml->addChild('Валюта', $this->Shop_Currency->code);
		$oOrderXml->addChild('Курс', $this->Shop_Currency->id > 0 && $this->Shop->Shop_Currency->id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency($this->Shop_Currency, $this->Shop->Shop_Currency)
			: 0
		);
		$oOrderXml->addChild('Сумма', $this->getAmount());

		$oContractors = $oOrderXml->addChild('Контрагенты');
		$oContractor = $oContractors->addChild('Контрагент');

		$bCompany = strlen(trim($this->company)) > 0;

		Core_Event::notify($this->_modelName . '.onBeforeGetCmlUserName', $this);

		$sUserFullName = Core_Event::getLastReturn();

		if (!strlen((string) $sUserFullName))
		{
			$aTmpArray = array();
			$this->surname != '' && $aTmpArray[] = $this->surname;
			$this->name != '' && $aTmpArray[] = $this->name;
			$this->patronymic != '' && $aTmpArray[] = $this->patronymic;
			!count($aTmpArray) && $aTmpArray[] = $this->email;

			$sUserFullName = implode(' ', $aTmpArray);
		}

		// При отсутствии модуля "Пользователи сайта" ИД пользователя рассчитывается как crc32()
		$sContractorId = $this->siteuser_id
			? $this->siteuser_id
			: abs(Core::crc32($bCompany
					? $this->company
					: $sUserFullName
				)
			);

		$sContractorName = $bCompany
			? $this->company
			: $sUserFullName;

		!strlen($sContractorName)
			&& $sContractorName = 'Контрагент ' . $sContractorId;

		$oContractor->addChild('Ид', $sContractorId);
		$oContractor->addChild('Наименование', $sContractorName);
		$oContractor->addChild('Роль', 'Покупатель');

		$sFullAddress = $this->getFullAddress();

		if ($bCompany)
		{
			$oContractor->addChild('ОфициальноеНаименование', $this->company);
			$oContractor->addChild('ИНН', $this->tin);
			$oContractor->addChild('КПП', $this->kpp);
		}
		else
		{
			$oContractor->addChild('ПолноеНаименование', $sUserFullName);
			$oContractor->addChild('Фамилия', $this->surname);
			$oContractor->addChild('Имя', $this->name);
			$oContractor->addChild('Отчество', $this->patronymic);
			$oContractor->addChild('АдресРегистрации')->addChild('Представление', $sFullAddress);
		}

		// Адрес контрагента
		$oContractorAddress = $oContractor->addChild('Адрес');
		$oContractorAddress->addChild('Представление', $sFullAddress);
		$oAddressField = $oContractorAddress->addChild('АдресноеПоле');
		$oAddressField->addChild('Тип', 'Почтовый индекс');
		$oAddressField->addChild('Значение', $this->postcode);
		$oAddressField = $oContractorAddress->addChild('АдресноеПоле');
		$oAddressField->addChild('Тип', 'Страна');
		$oAddressField->addChild('Значение', $this->shop_country->name);
		$oAddressField = $oContractorAddress->addChild('АдресноеПоле');
		$oAddressField->addChild('Тип', 'Город');
		$oAddressField->addChild('Значение', $this->shop_country_location_city->name);
		$oAddressField = $oContractorAddress->addChild('АдресноеПоле');
		$oAddressField->addChild('Тип', 'Улица');
		$oAddressField->addChild('Значение', $this->address);

		// Контакты
		$oContacts = $oContractor->addChild('Контакты');
		if ($this->email != '')
		{
			$oContactEmail = $oContacts->addChild('Контакт');
			$oContactEmail->addChild('Тип', 'Электронная почта');
			$oContactEmail->addChild('Значение', $this->email);
		}

		if ($this->phone != '')
		{
			$oContactPhone = $oContacts->addChild('Контакт');
			$oContactPhone->addChild('Тип', 'Телефон рабочий');
			$oContactPhone->addChild('Значение', $this->phone);
		}

		if ($this->fax != '')
		{
			$oContactFax = $oContacts->addChild('Контакт');
			$oContactFax->addChild('Тип', 'Факс');
			$oContactFax->addChild('Значение', $this->fax);
		}

		// Представители
		if ($bCompany)
		{
			$oRepresentatives = $oContractor->addChild('Представители');
			$oRepresentative = $oRepresentatives->addChild('Представитель');
			$oRepresentative->addChild('Отношение', 'Контактное лицо');
			$oRepresentative->addChild('Ид', abs(Core::crc32($sUserFullName)));
			$oRepresentative->addChild('Наименование', $sUserFullName);
		}

		$oOrderProperties = $oOrderXml->addChild('ЗначенияРеквизитов');
		// Статус оплаты заказа
		$oOrderProperty = $oOrderProperties->addChild('ЗначениеРеквизита');
		$oOrderProperty->addChild('Наименование', 'Заказ оплачен');
		$oOrderProperty->addChild('Значение', $this->paid == 1 ? 'true' : 'false');

		// Заказ отменен
		$oOrderProperty = $oOrderProperties->addChild('ЗначениеРеквизита');
		$oOrderProperty->addChild('Наименование', 'Отменен');
		$oOrderProperty->addChild('Значение', $this->canceled == 1 ? 'true' : 'false');

		if ($this->shop_order_status_id)
		{
			// Статус заказа
			$oOrderProperty = $oOrderProperties->addChild('ЗначениеРеквизита');
			$oOrderProperty->addChild('Наименование', 'Статус заказа');
			$oOrderProperty->addChild('Значение', $this->Shop_Order_Status->name);

			if ($this->status_datetime != '0000-00-00 00:00:00')
			{
				// Статус заказа
				$oOrderProperty = $oOrderProperties->addChild('ЗначениеРеквизита');
				$oOrderProperty->addChild('Наименование', 'Дата изменения статуса');
				$oOrderProperty->addChild('Значение', $this->status_datetime);
			}
		}

		// Способ доставки
		$oOrderProperty = $oOrderProperties->addChild('ЗначениеРеквизита');
		$oOrderProperty->addChild('Наименование', 'Способ доставки');
		$oOrderProperty->addChild('Значение', $this->Shop_Delivery->name);

		// Метод оплаты
		$oOrderProperty = $oOrderProperties->addChild('ЗначениеРеквизита');
		$oOrderProperty->addChild('Наименование', 'Метод оплаты');
		$oOrderProperty->addChild('Значение', $this->Shop_Payment_System->name);

		// Адрес доставки
		$oOrderProperty = $oOrderProperties->addChild('ЗначениеРеквизита');
		$oOrderProperty->addChild('Наименование', 'Адрес доставки');
		$oOrderProperty->addChild('Значение', $sFullAddress);

		// Дополнительные свойства заказа
		$aPropertyValues = $this->getPropertyValues(FALSE);
		foreach ($aPropertyValues as $oPropertyValue)
		{
			$oOrderProperty = $oOrderProperties->addChild('ЗначениеРеквизита');
			$oOrderProperty->addChild('Наименование', $oPropertyValue->Property->name);

			// List
			if ($oPropertyValue->Property->type == 3 && Core::moduleIsActive('list'))
			{
				$oOrderProperty->addChild('Значение', $oPropertyValue->value != 0
					? $oPropertyValue->List_Item->value
					: ''
				);
			}
			// Informationsystem
			elseif ($oPropertyValue->Property->type == 5 && Core::moduleIsActive('informationsystem'))
			{
				$oOrderProperty->addChild('Значение', $oPropertyValue->value != 0
					? $oPropertyValue->Informationsystem_Item->name
					: ''
				);
			}
			// Shop
			elseif ($oPropertyValue->Property->type == 12 && Core::moduleIsActive('shop'))
			{
				$oOrderProperty->addChild('Значение', $oPropertyValue->value != 0
					? $oPropertyValue->Shop_Item->name
					: ''
				);
			}
			// Wysiwyg
			elseif ($oPropertyValue->Property->type == 6)
			{
				$oOrderProperty->addChild('Значение', strip_tags($oPropertyValue->value));
			}
			// Other type
			elseif ($oPropertyValue->Property->type != 2)
			{
				$oOrderProperty->addChild('Значение', $oPropertyValue->value);
			}
		}

		$oOrderXml->addChild('Время', $time);
		$oOrderXml->addChild('Комментарий', $this->description);

		$oOrderItemsXml = $oOrderXml->addChild('Товары');

		$aDiscount_Shop_Order_Items = array();

		$oShop_Order_Items = $this->Shop_Order_Items;

		Core_Event::notify($this->_modelName . '.onAddCmlSelectShopOrderItems', $this, array($oShop_Order_Items, $oXml));

		$aShop_Order_Items = $oShop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			if ($oShop_Order_Item->getPrice() >= 0)
			{
				$oShop_Item = $oShop_Order_Item->shop_item_id
					? $oShop_Order_Item->Shop_Item
					: NULL;

				$oCurrentItemXml = $oOrderItemsXml->addChild('Товар');
				$oCurrentItemXml->addChild('Ид',
					$oShop_Item && $oShop_Item->modification_id
						// GUID-основного-товара#GUID-самой-модификации
						? sprintf('%s#%s', $oShop_Item->Modification->guid, $oShop_Item->guid)
						: ($oShop_Order_Item->type == 1
							? $this->Shop_Delivery->guid //'ORDER_DELIVERY'
							: ($oShop_Item
								? $oShop_Item->guid
								: 'undefined'
							)
						)
				);
				$oCurrentItemXml->addChild('Артикул', $oShop_Order_Item->marking);
				$oCurrentItemXml->addChild('Наименование', $oShop_Order_Item->name);

				if ($oShop_Item && $oShop_Item->shop_measure_id)
				{
					$oShop_Measure = $oShop_Item->Shop_Measure;
					$oXmlMeasure = $oCurrentItemXml->addChild('БазоваяЕдиница', $oShop_Measure->name);
					$oShop_Measure->okei && $oXmlMeasure->addAttribute('Код', $oShop_Measure->okei);
					$oShop_Measure->description != '' && $oXmlMeasure->addAttribute('НаименованиеПолное', $oShop_Measure->description);
				}

				$oCurrentItemXml->addChild('ЦенаЗаЕдиницу', $oShop_Order_Item->getPrice());
				$oCurrentItemXml->addChild('Количество', $oShop_Order_Item->quantity);
				$oCurrentItemXml->addChild('Сумма', $oShop_Order_Item->getAmount());

				$oPropertyXml = $oCurrentItemXml->addChild('ЗначенияРеквизитов');

				$oValue = $oPropertyXml->addChild('ЗначениеРеквизита');
				$oValue->addChild('Наименование', 'ВидНоменклатуры');
				$oValue->addChild('Значение', $oShop_Order_Item->type == 1 ? 'Услуга' : 'Товар');

				$oValue = $oPropertyXml->addChild('ЗначениеРеквизита');
				$oValue->addChild('Наименование', 'ТипНоменклатуры');
				$oValue->addChild('Значение', $oShop_Order_Item->type == 1 ? 'Услуга' : 'Товар');

				// Дополнительные свойства
				if ($oShop_Item)
				{
					$aProperty_Values = $oShop_Item->getPropertyValues(FALSE);

					foreach ($aProperty_Values as $oProperty_Value)
					{
						$oProperty = $oProperty_Value->Property;

						if (!in_array($oProperty->type, array(2, 5, 12, 13, 10, 14)) && $oProperty_Value->value !== '')
						{
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
									$value = Core_Date::sql2date($oProperty_Value->value);
								break;

								case 9: // Datetime
									$value = Core_Date::sql2datetime($oProperty_Value->value);
								break;

								case 3: // List
									if (Core::moduleIsActive('list'))
									{
										$oList_Item = $oProperty->List->List_Items->getById(
											$oProperty_Value->value/*, FALSE*/
										);

										$value = !is_null($oList_Item)
											? $oList_Item->value
											: NULL;
									}
									else
									{
										$value = NULL;
									}
								break;

								case 7: // Checkbox
									$value = $oProperty_Value->value == 1 ? 'есть' : NULL;
								break;

								case 2: // File
								case 5: // Элемент информационной системы
								case 13: // Группа информационной системы
								case 12: // Товар интернет-магазина
								case 14: // Группа интернет-магазина
								case 10: // Hidden field
								default:
									$value = NULL;
								break;
							}

							if (!is_null($value))
							{
								$oValue = $oPropertyXml->addChild('ЗначениеРеквизита');
								$oValue->addChild('Наименование', $oProperty->name);
								$oValue->addChild('Значение', $value);
							}
						}
					}
				}

				if ($oShop_Order_Item->rate > 0)
				{
					$oShop_Tax = $oShop_Item && $oShop_Item->shop_tax_id
						? $oShop_Item->Shop_Tax
						: NULL;

					$taxName = $oShop_Tax ? $oShop_Tax->name : 'НДС';

					$oTaxRates = $oCurrentItemXml->addChild('СтавкиНалогов');
					$oTaxRate = $oTaxRates->addChild('СтавкаНалога');
					$oTaxRate->addChild('Наименование', $taxName);
					$oTaxRate->addChild('Ставка', $oShop_Order_Item->rate);

					$oTaxes = $oCurrentItemXml->addChild('Налоги');
					$oTax = $oTaxes->addChild('Налог');
					$oTax->addChild('Наименование', $taxName);
					$oTax->addChild('УчтеноВСумме', 'true');
					$oTax->addChild('Сумма', $oShop_Order_Item->getTax());
				}

				if ($oShop_Order_Item->shop_warehouse_id)
				{
					$oWarehouses = $oCurrentItemXml->addChild('Склады');
					$oWarehouse = $oWarehouses->addChild('Склад');
					$oWarehouse->addAttribute('Ид', $oShop_Order_Item->Shop_Warehouse->guid);
					$oWarehouse->addChild('Количество', $oShop_Order_Item->quantity);
				}
			}
			else
			{
				$aDiscount_Shop_Order_Items[] = $oShop_Order_Item;
			}
		}

		if (count($aDiscount_Shop_Order_Items))
		{
			$oDiscountXml = $oOrderXml->addChild('Скидки');

			foreach ($aDiscount_Shop_Order_Items as $oShop_Order_Item)
			{
				$oCurrentItemXml = $oDiscountXml->addChild('Скидка');
				$oCurrentItemXml->addChild('Наименование', $oShop_Order_Item->name);
				$oCurrentItemXml->addChild('Сумма', -1 * $oShop_Order_Item->getAmount());
				// https://v8.1c.ru/upload/integraciya/realizovannye-resheniya/commerceml_2_10_2.pdf страница 43
				// Сумма	СуммаТип	[0..1]
				// Общая сумма по документу. Налоги, скидки и дополнительные расходы включаются в данную сумму в зависимости от установок "УчтеноВСумме", поэтому true
				$oCurrentItemXml->addChild('УчтеноВСумме', 'true');
			}
		}

		return $this;
	}

	/**
	 * Get Popover Content
	 * @return string
	 * @hostcms-event shop_order.onBeforeOrderPopover
	 */
	public function orderPopover()
	{
		Core_Event::notify($this->_modelName . '.onBeforeOrderPopover', $this);

		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult))
		{
			return $eventResult;
		}

		ob_start();

		if (strlen($this->company))
		{
			?><div>
				<b><?php echo Core::_('Shop_Order.payer')?>:</b> <?php echo htmlspecialchars($this->company)?>
			</div><?php
		}

		$person = trim($this->surname . ' ' . $this->name . ' ' . $this->patronymic);

		if (strlen($person))
		{
			?><div>
				<b><?php echo Core::_('Shop_Order.order_card_contact_person')?>:</b> <?php echo htmlspecialchars($person)?>
			</div><?php
		}

		$sFullAddress = $this->getFullAddress();
		?><div>
			<b><?php echo Core::_('Shop_Order.order_card_address')?>:</b> <?php echo htmlspecialchars($sFullAddress)?>
		</div><?php

		if (strlen($this->phone))
		{
			?><div>
				<b><?php echo Core::_('Shop_Order.order_card_phone')?>:</b> <?php echo htmlspecialchars($this->phone)?>
			</div><?php
		}
		if (strlen($this->email))
		{
			?><div>
				<b><?php echo Core::_('Shop_Order.order_card_email')?>:</b> <?php echo htmlspecialchars($this->email)?>
			</div><?php
		}
		if ($this->shop_payment_system_id)
		{
			?><div>
				<b><?php echo Core::_('Shop_Order.order_card_paymentsystem')?>:</b> <?php echo htmlspecialchars($this->Shop_Payment_System->name)?>
			</div><?php
		}
		if ($this->shop_order_status_id)
		{
			?><div>
				<b><?php echo Core::_('Shop_Order.order_card_order_status')?>:</b> <?php echo htmlspecialchars($this->Shop_Order_Status->name)?>
			</div><?php
		}
		if (!is_null($this->description) && $this->description !== '')
		{
			?><div>
				<b><?php echo Core::_('Shop_Order.order_card_description')?>:</b> <?php echo htmlspecialchars($this->description)?>
			</div><?php
		}
		if ($this->payment_datetime != '0000-00-00 00:00:00')
		{
			?><div>
				<b><?php echo Core::_('Shop_Order.order_card_status_of_pay')?>:</b> <?php echo Core_Date::sql2datetime($this->payment_datetime)?>
			</div><?php
		}

		$sig = $this->shop_currency_id
			? htmlspecialchars((string) $this->Shop_Currency->sign)
			: '';

		?>
		<div class="row">
			<div class="col-xs-12 table-responsive">
				<table class="table table-hover">
					<thead class="bordered-palegreen">
						<tr>
							<th>
								<?php echo "№"?>
							</th>
							<th width="40"></th>
							<th>
								<?php echo Core::_("Shop_Order.table_description")?>
							</th>
							<th>
								<?php echo Core::_("Shop_Order.table_mark")?>
							</th>
							<th>
								<?php echo Core::_("Shop_Order.table_price") . ", " . $sig?>
							</th>
							<th width="10%">
								<?php echo Core::_("Shop_Order.table_amount")?>
							</th>
							<th width="10%">
								<?php echo Core::_("Shop_Order.table_nds_tax")?>
							</th>
							<th width="10%">
								<?php echo Core::_("Shop_Order.table_nds_value")?>
							</th>
							<th width="10%">
								<?php echo Core::_("Shop_Order.table_amount_value") . ", " . $sig?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$i = 1;

					$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);

					$fShopTaxValueSum = $fShopOrderItemSum = 0.0;

					if (count($aShop_Order_Items))
					{
						foreach ($aShop_Order_Items as $oShop_Order_Item)
						{
							$sShopTaxRate = $oShop_Order_Item->rate;

							$fShopTaxValue = $sShopTaxRate
								? $oShop_Order_Item->getTax()
								: 0;

							// Не установлен статус у товара или статус НЕ отмененный
							$bNotCanceled = !$oShop_Order_Item->isCanceled();

							$fItemAmount = $bNotCanceled
								? $oShop_Order_Item->getAmount()
								: 0;

							$fShopTaxValueSum += $fShopTaxValue;
							$fShopOrderItemSum += $fItemAmount;

							?>
							<tr>
								<td>
									<?php echo $i++?>
								</td>
								<td width="40" align="center"><?php
									if ($oShop_Order_Item->type == 0)
									{
										echo $oShop_Order_Item->Shop_Item->imgBackend();
									}
									?>
								</td>
								<td>
									<?php
									if ($oShop_Order_Item->shop_order_item_status_id)
									{
										?><i class="fa <?php echo $oShop_Order_Item->Shop_Order_Item_Status->canceled ? 'fa-times-circle' : 'fa-circle'?>" style="color: <?php echo htmlspecialchars($oShop_Order_Item->Shop_Order_Item_Status->color)?>" title="<?php echo htmlspecialchars($oShop_Order_Item->Shop_Order_Item_Status->name)?>"></i> <?php
									}
									echo htmlspecialchars($oShop_Order_Item->name);
									?>
								</td>
								<td>
									<?php echo htmlspecialchars((string) $oShop_Order_Item->marking)?>
								</td>
								<td>
									<?php echo number_format(Shop_Controller::instance()->round($oShop_Order_Item->getPrice()), 2, '.', '')?>
								</td>
								<td>
									<?php echo Core_Str::hideZeros($oShop_Order_Item->quantity)?>
								</td>
								<td>
									<?php echo $sShopTaxRate != 0 ? "{$sShopTaxRate}%" : '-'?>
								</td>
								<td>
									<?php echo $fShopTaxValue != 0 ? $fShopTaxValue : '-'?>
								</td>
								<td>
									<?php echo $bNotCanceled ? number_format($fItemAmount, 2, '.', '') : '-'?>
								</td>
							</tr>
							<?php
						}
					}
					?>
							<tr class="footer">
							<td width="80%" align="right" style="border-bottom: 1px solid #e9e9e9" colspan="6">
								<?php echo Core::_("Shop_Order.table_nds")?>
							</td>
							<td width="80%" align="right" style="border-bottom: 1px solid #e9e9e9" colspan="3">
								<?php echo $this->Shop_Currency->formatWithCurrency($fShopTaxValueSum)?>
							</td>
						</tr>
						<tr class="footer">
							<td align="right" colspan="6">
								<?php echo Core::_("Shop_Order.table_all_to_pay")?>
							</td>
							<td align="right" colspan="3">
								<?php echo $this->Shop_Currency->formatWithCurrency($fShopOrderItemSum)?>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<?php
		return ob_get_clean();
	}

	public function order_items($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$windowId = $oAdmin_Form_Controller->getWindowId();

		$siteuser_id = Core_Array::getGet('siteuser_id', 0, 'intval');

		if ($siteuser_id)
		{
			$onclick = "$.modalLoad({path: '/admin/shop/order/item/index.php',additionalParams: 'shop_id={shop_id}&shop_group_id={shop_group_id}&shop_dir_id={shop_dir_id}&shop_order_id={id}&{INTERNAL}', windowId: '{windowId}'}); return false";
		}
		else
		{

			$onclick = $oAdmin_Form_Field->onclick;
		}

		$link = $oAdmin_Form_Field->link;

		$aAdmin_Form_Fields = $oAdmin_Form_Controller->getAdminForm()->Admin_Form_Fields->findAll();

		$link = $oAdmin_Form_Controller->doReplaces($aAdmin_Form_Fields, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($aAdmin_Form_Fields, $this, $onclick, 'onclick');

		// Fix popover for form in tab
		$windowId == 'shop-orders' && $windowId = 'id_content';

		//data-toggle="popover-hover"
		//data-titleclass="bordered-lightgray"
		//data-placement="left" data-trigger="hover" data-html="true"
		//data-popover="hover"
		//data-container="#php echo $windowId

		?><a id="popover-hover" href="<?php echo $link?>" onclick="$('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_0_<?php echo $this->id?>').toggleHighlight();<?php echo $onclick?>" data-popover="hover" data-id="<?php echo $this->id?>" data-title="<?php echo htmlspecialchars(Core::_('Shop_Order.popover_title', $this->invoice, Core_Date::sql2datetime($this->datetime)))?>"><i class="fa fa-list" title=""></i></a><?php
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function order_itemsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Order_items->getCount();

		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-palegreen white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function reviewsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('comment'))
		{
			$count = $this->Comments->getCount();
			$count && Core_Html_Entity::factory('Span')
				->class('badge badge-ico white')
				->value($count < 100 ? $count : '∞')
				->title($count)
				->execute();
		}
	}

	/**
	 * Send order e-mails
	 * @return self
	 */
	public function sendMail()
	{
		if ($this->shop_payment_system_id && $this->Shop_Order_items->getCount())
		{
			$oShop_Payment_System_Handler = Shop_Payment_System_Handler::factory(
				$this->Shop_Payment_System
			);

			if ($oShop_Payment_System_Handler)
			{
				$oShop_Payment_System_Handler
					->shopOrder($this)
					->shopOrderBeforeAction(clone $this)
					->setMailSubjects()
					->setXSLs()
					->send();
			}
		}

		return $this;
	}

	public function date()
	{
		return Core_Date::sql2date($this->datetime);
	}

	public function getFullAddress()
	{
		$aAddress = array(
			$this->postcode,
			$this->Shop_Country->name,
			$this->Shop_Country_Location->name,
			$this->Shop_Country_Location_City->name,
			$this->Shop_Country_Location_City_Area->name,
			$this->address,
			$this->house,
			$this->flat
		);

		$aAddress = array_map('strval', $aAddress);
		$aAddress = array_map('trim', $aAddress);
		$aAddress = array_filter($aAddress, 'strlen');
		$sFullAddress = implode(', ', $aAddress);

		return $sFullAddress;
	}

	public function getCustomerName()
	{
		return strlen(trim($this->company))
			? $this->company
			: implode(' ', array_filter(array_map('trim', array($this->surname, $this->name, $this->patronymic)), 'strlen'));
	}

	/**
	 * Add discounts to the shop_order
	 * @param array $aOptions array('amount' => $amount, 'quantity' => $quantity, 'prices' => $aDiscountPrices, 'applyDiscounts' => TRUE, 'applyDiscountCards' => TRUE)
	 * @return self
	 */
	public function addPurchaseDiscount($aOptions)
	{
		$aOptions += array(
			'amount' => 0,
			'quantity' => 0,
			'weight' => 0,
			'prices' => array(),
			'applyDiscounts' => TRUE,
			'applyDiscountCards' => TRUE
		);

		if ($aOptions['amount'] > 0 && $aOptions['quantity'] > 0)
		{
			$amount = $aOptions['amount'];
			$quantity = $aOptions['quantity'];
			$weight = $aOptions['weight'];
			$aDiscountPrices = $aOptions['prices'];

			$oShop = $this->Shop;

			// Дисконтная карта
			$bApplyMaxDiscount = FALSE;
			$fDiscountcard = 0;

			if ($aOptions['applyDiscountCards'] && Core::moduleIsActive('siteuser') && $this->siteuser_id)
			{
				$oSiteuser = $this->Siteuser;

				$oShop_Discountcard = $oSiteuser->Shop_Discountcards->getByShop_id($oShop->id);
				if (!is_null($oShop_Discountcard)
					&& $oShop_Discountcard->active
					&& $oShop_Discountcard->shop_discountcard_level_id
				)
				{
					$oShop_Discountcard_Level = $oShop_Discountcard->Shop_Discountcard_Level;

					$bApplyMaxDiscount = $oShop_Discountcard_Level->apply_max_discount == 1;

					// Сумма скидки по дисконтной карте
					$fDiscountcard = $amount * ($oShop_Discountcard_Level->discount / 100);

					// Округляем до целых
					$oShop_Discountcard_Level->round
						&& $fDiscountcard = round($fDiscountcard);
				}
			}

			// Скидки от суммы заказа
			$fAppliedDiscountsAmount = 0;
			$bApplyShopPurchaseDiscounts = FALSE;

			if ($aOptions['applyDiscounts'])
			{
				$oShop_Purchase_Discount_Controller = new Shop_Purchase_Discount_Controller($oShop);
				$oShop_Purchase_Discount_Controller
					->amount($amount)
					->quantity($quantity)
					->weight($weight)
					->couponText($this->coupon)
					->siteuserId($this->siteuser_id ? $this->siteuser_id : 0)
					->prices($aDiscountPrices)
					->dateTime($this->datetime);

				// Получаем данные о купоне
				$shop_purchase_discount_coupon_id = $shop_purchase_discount_id = 0;
				if (strlen((string) $oShop_Purchase_Discount_Controller->couponText))
				{
					$oShop_Purchase_Discounts_For_Coupon = $oShop->Shop_Purchase_Discounts->getByCouponText(
						$oShop_Purchase_Discount_Controller->couponText
					);
					if (!is_null($oShop_Purchase_Discounts_For_Coupon))
					{
						// ID скидки по купону
						$shop_purchase_discount_id = $oShop_Purchase_Discounts_For_Coupon->id;
						// ID самого купона
						$shop_purchase_discount_coupon_id = $oShop_Purchase_Discounts_For_Coupon->shop_purchase_discount_coupon_id;
					}
				}

				$aShop_Purchase_Discounts = $oShop_Purchase_Discount_Controller->getDiscounts();

				// Вычисляем сумму скидок по скидкам от суммы заказа и по уровню дисконтной карты, применяется наибольшее из рассчитанных скидок
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
						$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
						$oShop_Order_Item->name = $oShop_Purchase_Discount->name;
						$oShop_Order_Item->quantity = 1;
						$oShop_Order_Item->type = 3; // 3 - Скидка от суммы заказа

						$discountAmount = $oShop_Purchase_Discount->getDiscountAmount();

						// Скидка больше суммы заказа
						$discountAmount > $amount && $discountAmount = $amount;

						$oShop_Order_Item->price = -1 * $discountAmount;

						// Inc total discount amount
						$fAppliedDiscountsAmount += $discountAmount;

						if ($oShop_Purchase_Discount->id == $shop_purchase_discount_id)
						{
							$oShop_Purchase_Discount_Coupon = Core_Entity::factory('Shop_Purchase_Discount_Coupon')->find(
								$shop_purchase_discount_coupon_id
							);

							if (!is_null($oShop_Purchase_Discount_Coupon->id))
							{
								// Списываем купон
								if ($oShop_Purchase_Discount_Coupon->count != -1 && $oShop_Purchase_Discount_Coupon->count != 0)
								{
									$oShop_Purchase_Discount_Coupon->count = $oShop_Purchase_Discount_Coupon->count - 1;
									$oShop_Purchase_Discount_Coupon->save();
								}

								// Сохраняем купон для заказа, мог быть задан выше как купон скидки на товар
								if (!is_null($this->coupon))
								{
									$this->coupon = $oShop_Purchase_Discount_Coupon->text;
									$this->save();
								}
							}
						}

						$this->add($oShop_Order_Item);
					}
				}
			}

			// Не применять максимальную скидку или сумму по карте больше, чем скидка от суммы заказа
			if (!$bApplyMaxDiscount || !$bApplyShopPurchaseDiscounts)
			{
				if ($fDiscountcard)
				{
					$fAmountForCard = $amount - $fAppliedDiscountsAmount;

					if ($fAmountForCard > 0)
					{
						// Рассчитываем от новой суммы, без учета примененных выше скидок от суммы заказа
						$fDiscountcard = $fAmountForCard * ($oShop_Discountcard_Level->discount / 100);

						// Округляем до целых
						$oShop_Discountcard_Level->round
							&& $fDiscountcard = round($fDiscountcard);

						$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
						$oShop_Order_Item->name = Core::_('Shop_Discountcard.shop_order_item_name', $oShop_Discountcard->number);
						$oShop_Order_Item->quantity = 1;
						$oShop_Order_Item->type = 4; // 4 - Скидка по дисконтной карте
						$oShop_Order_Item->price = -1 * Shop_Controller::instance()->round($fDiscountcard);

						$this->add($oShop_Order_Item);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Get the number of orders for today
	 * @return int
	 */
	public function ordersToday()
	{
		$tmp = date('Y-m-d');

		$oShop_Orders = $this->Shop->Shop_Orders;
		$oShop_Orders->queryBuilder()
			->where('datetime', '>', "{$tmp} 00:00:00")
			->where('datetime', '<', "{$tmp} 23:59:59");

		return $oShop_Orders->getCount(FALSE);
	}

	/**
	 * Get the number of orders for the current month
	 * @return int
	 */
	public function ordersCurrentMonth()
	{
		$tmp = date('Y-m');

		$oShop_Orders = $this->Shop->Shop_Orders;
		$oShop_Orders->queryBuilder()
			->where('datetime', '>', "{$tmp}-01 00:00:00")
			->where('datetime', '<', "{$tmp}-31 23:59:59");

		return $oShop_Orders->getCount(FALSE);
	}

	/**
	 * Get the number of orders for the current year
	 * @return int
	 */
	public function ordersCurrentYear()
	{
		$tmp = date('Y');

		$oShop_Orders = $this->Shop->Shop_Orders;
		$oShop_Orders->queryBuilder()
			->where('datetime', '>', "{$tmp}-01-01 00:00:00")
			->where('datetime', '<', "{$tmp}-12-31 23:59:59");

		return $oShop_Orders->getCount(FALSE);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_order_status_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		ob_start();

		$this->checkShopOrderStatusDeadline();

		$path = $oAdmin_Form_Controller->getPath();

		$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
		);

		Core_Html_Entity::factory('Span')
			->class('padding-left-10')
			->add(
				$oCore_Html_Entity_Dropdownlist
					->value($this->shop_order_status_id)
					->options(Shop_Order_Status_Controller_Edit::getDropdownlistOptions($this->shop_id))
					->onchange("$.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', action: 'apply', post: { 'hostcms[checked][0][{$this->id}]': 0, apply_check_0_{$this->id}_fv_{$oAdmin_Form_Field->id}: $(this).find('li[selected]').prop('id') }, windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
					->data('change-context', 'true')
				)
			->execute();

		if ($this->shop_order_status_deadline != '0000-00-00 00:00:00')
		{
			$bgColor = Core_Str::hex2lighter($this->Shop_Order_Status->color, 0.88);

			Core_Html_Entity::factory('Div')
				->style("color: {$this->Shop_Order_Status->color}; background-color: {$bgColor}")
				->class('margin-left-10 badge badge-square')
				->title(Core::_('Shop_Order_Status.deadline', Core_Date::sql2datetime($this->shop_order_status_deadline)))
				->value("<i class='fas fa-stopwatch margin-right-5'></i>" . Core_Date::sql2string($this->shop_order_status_deadline))
				->execute();
		}

		return ob_get_clean();
	}

	/**
	 * Get text values of property
	 * @param int $property_id property ID
	 * @param string $separator
	 * @return string
	 */
	public function getTextPropertyValue($property_id, $separator = ', ')
	{
		$aReturn = array();

		$oProperty = Core_Entity::factory('Property', $property_id);

		$aProperty_Values = $oProperty->getValues($this->id, FALSE);
		foreach ($aProperty_Values as $oProperty_Value)
		{
			// Дополнительные свойства
			switch ($oProperty->type)
			{
				case 2: // File
					if ($oProperty_Value->file_name != '')
					{
						$aReturn[] = $oProperty_Value->file_name;
					}
				break;
				case 3: // List
					$oProperty_Value->value
						&& $aReturn[] = Core_Entity::factory('List_Item', intval($oProperty_Value->value))->value;
				break;
				case 5: // Information system
					if ($oProperty_Value->value)
					{
						$oPropertyInformationsystemItem = Core_Entity::factory('Informationsystem_Item', intval($oProperty_Value->value));
						$aReturn[] = $oPropertyInformationsystemItem->name;
					}
				break;
				case 7: // Checkbox
					$aReturn[] = $oProperty_Value->value ? '✓' : '×';
				break;
				case 8: // Date
					$oProperty_Value->value != '0000-00-00 00:00:00'
						&& $aReturn[] = Core_Date::sql2date($oProperty_Value->value);
				break;
				case 9: // Datetime
					$oProperty_Value->value != '0000-00-00 00:00:00'
						&& $aReturn[] = Core_Date::sql2datetime($oProperty_Value->value);
				break;
				case 12: // Shop
					if ($oProperty_Value->value)
					{
						$oPropertyShopItem = Core_Entity::factory('Shop_Item', intval($oProperty_Value->value));
						$aReturn[] = $oPropertyShopItem->name;
					}
				break;
				default:
					$oProperty_Value->value !== ''
						&& $aReturn[] = $oProperty_Value->value;
			}
		}

		return count($aReturn) ? implode($separator, $aReturn) : '';
	}

	/**
	 * Get printlayout replaces
	 * @return array
	 * @hostcms-event shop_order.onAfterGetPrintlayoutReplaces
	 */
	public function getPrintlayoutReplaces()
	{
		$oShop_Company = $this->company_id
			? $this->Shop_Company
			: $this->Shop->Shop_Company;

		$oCompany = Core_Entity::factory('Company', $oShop_Company->id);

		$oCompany_Account = !is_null($oCompany)
			? $oCompany->Company_Accounts->getDefault()
			: NULL;

		$aReplace = array(
			// Core_Meta
			'this' => $this,
			'shop_order' => $this,
			'company' => !is_null($oCompany) ? $oCompany : new Core_Meta_Empty(),
			'company_account' => !is_null($oCompany_Account) ? $oCompany_Account : new Core_Meta_Empty(),
			'shop' => $this->Shop,
			'total_count' => 0,
		);

		$position = 1;

		$total_amount = $total_quantity = $total_tax = 0;

		$aShop_Order_Items = $this->Shop_Order_Items->findAll();
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			$oShop_Item = $oShop_Order_Item->Shop_Item;

			$node = new stdClass();
			$node->position = $position++;
			$node->item = $oShop_Item;
			$node->id = $oShop_Item->id;
			$node->name = htmlspecialchars((string) $oShop_Order_Item->name);

			$node->measure = $node->okei = '';

			if ($oShop_Item->shop_measure_id)
			{
				$node->measure = htmlspecialchars((string) $oShop_Item->Shop_Measure->name);
				$node->okei = htmlspecialchars((string) $oShop_Item->Shop_Measure->okei);
			}

			$price = $oShop_Order_Item->getPrice();
			$tax = $oShop_Order_Item->getTax();
			$amount = $oShop_Order_Item->getAmount();

			$node->price = $price - $tax;
			$node->price_tax = $tax;
			$node->price_tax_included = $price;
			$node->quantity = $oShop_Order_Item->quantity;
			$node->marking = htmlspecialchars((string) $oShop_Order_Item->marking);
			$node->rate = $oShop_Order_Item->rate;
			$node->rate_percent = $oShop_Order_Item->rate ? $oShop_Order_Item->rate . '%' : '';
			$node->amount = $amount - ($tax * $oShop_Order_Item->quantity);
			$node->tax = $tax * $oShop_Order_Item->quantity;
			$node->amount_tax_included = $amount;
			$node->warehouse_cell = !is_null($oShop_Order_Item->getCellName()) ? htmlspecialchars($oShop_Order_Item->getCellName()) : '';

			$aReplace['Items'][] = $node;

			$total_quantity += $oShop_Order_Item->quantity;
			$total_tax += $node->tax;
			$total_amount += $node->amount;

			$aReplace['total_count']++;
		}

		$aReplace['quantity'] = $total_quantity;
		$aReplace['tax'] = Shop_Controller::instance()->round($total_tax);
		$aReplace['amount'] = Shop_Controller::instance()->round($total_amount);
		$aReplace['amount_tax_included'] = Shop_Controller::instance()->round($this->getAmount());

		$lng = $this->Shop->Site->lng;

		$aReplace['amount_in_words'] = Core_Inflection::available($lng)
			? Core_Str::ucfirst(Core_Inflection::instance($lng)->currencyInWords($aReplace['amount_tax_included'], $this->Shop_Currency->code))
			: $aReplace['amount_tax_included'] . ' ' . $this->Shop_Currency->sign;

		$aReplace['delivery_name'] = $this->shop_delivery_id ? Core_Str::ucfirst($this->Shop_Delivery->name) : '';

		$aReplace['payment_name'] = $this->shop_payment_system_id ? Core_Str::ucfirst($this->Shop_Payment_System->name) : '';
		$aReplace['payment_status'] = $this->paid ? Core::_('Admin_Form.yes') : Core::_('Admin_Form.no');
		$aReplace['payment_date'] = $this->paid ? Core_Date::sql2date($this->payment_datetime) : '';
		$aReplace['payment_datetime'] = $this->paid ? Core_Date::sql2datetime($this->payment_datetime) : '';

		$aReplace['status_date'] = $this->status_datetime != '0000-00-00 00:00:00' ? Core_Date::sql2date($this->status_datetime) : '';
		$aReplace['status_datetime'] = $this->status_datetime != '0000-00-00 00:00:00' ? Core_Date::sql2datetime($this->status_datetime) : '';

		$aReplace['year'] = date('Y');
		$aReplace['month'] = date('m');
		$aReplace['day'] = date('d');

		Core_Event::notify($this->_modelName . '.onAfterGetPrintlayoutReplaces', $this, array($aReplace));
		$eventResult = Core_Event::getLastReturn();

		return !is_null($eventResult)
			? $eventResult
			: $aReplace;
	}

	/**
	 * Get property value for SEO-templates
	 * @param int $property_id Property ID
	 * @param strint $format string format, e.g. '%s: %s'. %1$s - Property Name, %2$s - List of Values
	 * @param string $separator separator
	 * @return string
	 */
	public function propertyValue($property_id, $format = '%2$s', $separator = ', ')
	{
		$oProperty = Core_Entity::factory('Property', $property_id);
		$aProperty_Values = $oProperty->getValues($this->id, FALSE);

		if (count($aProperty_Values))
		{
			$aTmp = array();

			foreach ($aProperty_Values as $oProperty_Value)
			{
				switch ($oProperty->type)
				{
					case 0: // Int
					case 1: // String
					case 4: // Textarea
					case 6: // Wysiwyg
					case 11: // Float
						$aTmp[] = $oProperty_Value->value;
					break;
					case 8: // Date
						$aTmp[] = Core_Date::strftime($this->Shop->format_date, Core_Date::sql2timestamp($oProperty_Value->value));
					break;
					case 9: // Datetime
						$aTmp[] = Core_Date::strftime($this->Shop->format_datetime, Core_Date::sql2timestamp($oProperty_Value->value));
					break;
					case 3: // List
						if ($oProperty_Value->value)
						{
							$oList_Item = $oProperty->List->List_Items->getById(
								$oProperty_Value->value, FALSE
							);

							!is_null($oList_Item) && $aTmp[] = $oList_Item->value;
						}
					break;
					case 7: // Checkbox
					break;
					case 5: // Informationsystem
						if ($oProperty_Value->value)
						{
							$aTmp[] = $oProperty_Value->Informationsystem_Item->name;
						}
					break;
					case 12: // Shop
						if ($oProperty_Value->value)
						{
							$aTmp[] = $oProperty_Value->Shop_Item->name;
						}
					break;
					case 2: // File
					case 10: // Hidden field
					default:
					break;
				}
			}

			if (count($aTmp))
			{
				return sprintf($format, $oProperty->name, implode($separator, $aTmp));
			}
		}

		return NULL;
	}

	/**
	 * Push paid history
	 * @return self
	 */
	public function historyPushPaid()
	{
		$oShop_Order_History = Core_Entity::factory('Shop_Order_History');
		$oShop_Order_History->shop_order_id = $this->id;
		$oShop_Order_History->shop_order_status_id = $this->shop_order_status_id;
		$oShop_Order_History->text = $this->paid
			? Core::_('Shop_Order.paid')
			: Core::_('Shop_Order.paid_cancel');
		$oShop_Order_History->color = '#53a93f';
		$oShop_Order_History->save();

		return $this;
	}

	/**
	 * Push posted history
	 * @return self
	 */
	public function historyPushPosted()
	{
		$oShop_Order_History = Core_Entity::factory('Shop_Order_History');
		$oShop_Order_History->shop_order_id = $this->id;
		$oShop_Order_History->shop_order_status_id = $this->shop_order_status_id;
		$oShop_Order_History->text = $this->posted
			? Core::_('Shop_Order.posted')
			: Core::_('Shop_Order.posted_cancel');
		$oShop_Order_History->color = '#5db2ff';
		$oShop_Order_History->save();

		return $this;
	}

	/**
	 * Push canceled history
	 * @return self
	 */
	public function historyPushCanceled()
	{
		$oShop_Order_History = Core_Entity::factory('Shop_Order_History');
		$oShop_Order_History->shop_order_id = $this->id;
		$oShop_Order_History->shop_order_status_id = $this->shop_order_status_id;
		$oShop_Order_History->text = $this->canceled
			? Core::_('Shop_Order.canceled')
			: Core::_('Shop_Order.canceled_cancel');
		$oShop_Order_History->color = '#d73d32';
		$oShop_Order_History->save();

		return $this;
	}

	/**
	 * Push change status history
	 * @return self
	 */
	public function historyPushChangeStatus()
	{
		$oShop_Order_History = Core_Entity::factory('Shop_Order_History');
		$oShop_Order_History->shop_order_id = $this->id;
		$oShop_Order_History->shop_order_status_id = $this->shop_order_status_id;
		$oShop_Order_History->text = Core::_('Shop_Order.change_status', $this->Shop_Order_Status->name, FALSE);
		$oShop_Order_History->color = $this->Shop_Order_Status->color;
		$oShop_Order_History->save();

		return $this;
	}

	/**
	 * Push change status history
	 * @return self
	 */
	public function historyPushChangeUser()
	{
		$oShop_Order_History = Core_Entity::factory('Shop_Order_History');
		$oShop_Order_History->shop_order_id = $this->id;
		$oShop_Order_History->shop_order_status_id = $this->shop_order_status_id;
		$oShop_Order_History->text = Core::_('Shop_Order.change_user', $this->User->getFullName(), FALSE);
		$oShop_Order_History->save();

		return $this;
	}

	/**
	 * Notify Bots
	 * @return self
	 */
	public function notifyBotsChangeStatus()
	{
		if (Core::moduleIsActive('bot'))
		{
			$oModule = Core::$modulesList['shop'];
			Bot_Controller::notify($oModule->id, 0, $this->shop_order_status_id, $this);
		}

		return $this;
	}

	/**
	 * Get responsible users
	 * @return array
	 */
	public function getResponsibleUsers()
	{
		return $this->user_id
			? array($this->User)
			: array();
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'shop_id' => $this->shop_id,
				'company_id' => $this->company_id,
				'shop_country_location_id' => $this->shop_country_location_id,
				'shop_country_id' => $this->shop_country_id,
				'shop_country_location_city_id' => $this->shop_country_location_city_id,
				'shop_country_location_city_area_id' => $this->shop_country_location_city_area_id,
				'shop_delivery_id' => $this->shop_delivery_id,
				'shop_delivery_condition_id' => $this->shop_delivery_condition_id,
				'siteuser_id' => $this->siteuser_id,
				'source_id' => $this->source_id,
				'name' => $this->name,
				'surname' => $this->surname,
				'patronymic' => $this->patronymic,
				'email' => $this->email,
				'acceptance_report' => $this->acceptance_report,
				'acceptance_report_datetime' => $this->acceptance_report_datetime,
				'vat_invoice' => $this->vat_invoice,
				'vat_invoice_datetime' => $this->vat_invoice_datetime,
				'company' => $this->company,
				'tin' => $this->tin,
				'kpp' => $this->kpp,
				'fax' => $this->fax,
				'shop_order_status_id' => $this->shop_order_status_id,
				'shop_currency_id' => $this->shop_currency_id,
				'shop_payment_system_id' => $this->shop_payment_system_id,
				'datetime' => $this->datetime,
				'paid' => $this->paid,
				'posted' => $this->posted,
				'payment_datetime' => $this->payment_datetime,
				'address' => $this->address,
				'house' => $this->house,
				'flat' => $this->flat,
				'postcode' => $this->postcode,
				'phone' => $this->phone,
				'description' => $this->description,
				'system_information' => $this->system_information,
				'canceled' => $this->canceled,
				'user_id' => $this->user_id,
				'invoice' => $this->invoice,
				'status_datetime' => $this->status_datetime,
				'guid' => $this->guid,
				'delivery_information' => $this->delivery_information,
				'ip' => $this->ip,
				'coupon' => $this->coupon,
				'unloaded' => $this->unloaded
			);

			$aBackup['shop_order_items'] = array();

			$aShop_Order_Items = $this->Shop_Order_Items->findAll(FALSE);
			foreach ($aShop_Order_Items as $oShop_Order_Item)
			{
				$aBackup['shop_order_items'][$oShop_Order_Item->id] = array(
					'shop_item_id' => $oShop_Order_Item->shop_item_id,
					'shop_order_id' => $oShop_Order_Item->shop_order_id,
					'name' => $oShop_Order_Item->name,
					'quantity' => $oShop_Order_Item->quantity,
					'price' => $oShop_Order_Item->price,
					'marking' => $oShop_Order_Item->marking,
					'rate' => $oShop_Order_Item->rate,
					'user_id' => $oShop_Order_Item->user_id,
					'hash' => $oShop_Order_Item->hash,
					'shop_item_digital_id' => $oShop_Order_Item->shop_item_digital_id,
					'type' => $oShop_Order_Item->type,
					'shop_warehouse_id' => $oShop_Order_Item->shop_warehouse_id
				);
			}

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->shop_id = Core_Array::get($aBackup, 'shop_id');
				$this->company_id = Core_Array::get($aBackup, 'company_id');
				$this->shop_country_location_id = Core_Array::get($aBackup, 'shop_country_location_id');
				$this->shop_country_id = Core_Array::get($aBackup, 'shop_country_id');
				$this->shop_country_location_city_id = Core_Array::get($aBackup, 'shop_country_location_city_id');
				$this->shop_country_location_city_area_id = Core_Array::get($aBackup, 'shop_country_location_city_area_id');
				$this->shop_delivery_id = Core_Array::get($aBackup, 'shop_delivery_id');
				$this->shop_delivery_condition_id = Core_Array::get($aBackup, 'shop_delivery_condition_id');
				$this->siteuser_id = Core_Array::get($aBackup, 'siteuser_id');
				$this->source_id = Core_Array::get($aBackup, 'source_id');
				$this->name = Core_Array::get($aBackup, 'name');
				$this->surname = Core_Array::get($aBackup, 'surname');
				$this->patronymic = Core_Array::get($aBackup, 'patronymic');
				$this->email = Core_Array::get($aBackup, 'email');
				$this->acceptance_report = Core_Array::get($aBackup, 'acceptance_report');
				$this->acceptance_report_datetime = Core_Array::get($aBackup, 'acceptance_report_datetime');
				$this->vat_invoice = Core_Array::get($aBackup, 'vat_invoice');
				$this->company = Core_Array::get($aBackup, 'company');
				$this->tin = Core_Array::get($aBackup, 'tin');
				$this->kpp = Core_Array::get($aBackup, 'kpp');
				$this->fax = Core_Array::get($aBackup, 'fax');
				$this->shop_order_status_id = Core_Array::get($aBackup, 'shop_order_status_id');
				$this->shop_currency_id = Core_Array::get($aBackup, 'shop_currency_id');
				$this->shop_payment_system_id = Core_Array::get($aBackup, 'shop_payment_system_id');
				$this->datetime = Core_Array::get($aBackup, 'datetime');
				$this->paid = Core_Array::get($aBackup, 'paid');
				$this->posted = Core_Array::get($aBackup, 'posted');
				$this->payment_datetime = Core_Array::get($aBackup, 'payment_datetime');
				$this->address = Core_Array::get($aBackup, 'address');
				$this->house = Core_Array::get($aBackup, 'house');
				$this->flat = Core_Array::get($aBackup, 'flat');
				$this->postcode = Core_Array::get($aBackup, 'postcode');
				$this->phone = Core_Array::get($aBackup, 'phone');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->system_information = Core_Array::get($aBackup, 'system_information');
				$this->canceled = Core_Array::get($aBackup, 'canceled');
				$this->user_id = Core_Array::get($aBackup, 'user_id');
				$this->invoice = Core_Array::get($aBackup, 'invoice');
				$this->status_datetime = Core_Array::get($aBackup, 'status_datetime');
				$this->guid = Core_Array::get($aBackup, 'guid');
				$this->delivery_information = Core_Array::get($aBackup, 'delivery_information');
				$this->ip = Core_Array::get($aBackup, 'ip');
				$this->coupon = Core_Array::get($aBackup, 'coupon');
				$this->unloaded = Core_Array::get($aBackup, 'unloaded');

				$this->save();

				if (isset($aBackup['shop_order_items']))
				{
					foreach ($aBackup['shop_order_items'] as $shop_order_item_id => $aShopOrderItem)
					{
						$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item')->getById($shop_order_item_id);

						if (is_null($oShop_Order_Item))
						{
							$oShop_Order_Item = Core_Entity::factory('Shop_Order_Item');
						}

						$oShop_Order_Item->shop_item_id = Core_Array::get($aShopOrderItem, 'shop_item_id');
						$oShop_Order_Item->shop_order_id = Core_Array::get($aShopOrderItem, 'shop_order_id');
						$oShop_Order_Item->name = Core_Array::get($aShopOrderItem, 'name');
						$oShop_Order_Item->quantity = Core_Array::get($aShopOrderItem, 'quantity');
						$oShop_Order_Item->price = Core_Array::get($aShopOrderItem, 'price');
						$oShop_Order_Item->marking = Core_Array::get($aShopOrderItem, 'marking');
						$oShop_Order_Item->rate = Core_Array::get($aShopOrderItem, 'rate');
						$oShop_Order_Item->user_id = Core_Array::get($aShopOrderItem, 'user_id');
						$oShop_Order_Item->hash = Core_Array::get($aShopOrderItem, 'hash');
						$oShop_Order_Item->shop_item_digital_id = Core_Array::get($aShopOrderItem, 'shop_item_digital_id');
						$oShop_Order_Item->type = Core_Array::get($aShopOrderItem, 'type');
						$oShop_Order_Item->shop_warehouse_id = Core_Array::get($aShopOrderItem, 'shop_warehouse_id');

						$oShop_Order_Item->save();
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Merge list item with another one
	 * @param List_Item_Model $oList_Item list item object
	 * @return self
	 */
	public function merge(Shop_Order_Model $oShop_Order)
	{
		// Основные
		$this->coupon == ''
			&& $oShop_Order->coupon != ''
			&& $this->coupon = $oShop_Order->coupon;

		$this->shop_payment_system_id == 0
			&& $oShop_Order->shop_payment_system_id > 0
			&& $this->shop_payment_system_id = $oShop_Order->shop_payment_system_id;

		$this->shop_order_status_id == 0
			&& $oShop_Order->shop_order_status_id > 0
			&& $this->shop_order_status_id = $oShop_Order->shop_order_status_id;

		$this->company_id == 0
			&& $oShop_Order->company_id > 0
			&& $this->company_id = $oShop_Order->company_id;

		$this->shop_delivery_id == 0
			&& $oShop_Order->shop_delivery_id > 0
			&& $this->shop_delivery_id = $oShop_Order->shop_delivery_id;

		$this->shop_delivery_condition_id == 0
			&& $oShop_Order->shop_delivery_condition_id > 0
			&& $this->shop_delivery_condition_id = $oShop_Order->shop_delivery_condition_id;

		// Реквизиты
		if ($this->postcode == '' && $this->address == '')
		{
			$this->shop_country_id == 0
				&& $oShop_Order->shop_country_id > 0
				&& $this->shop_country_id = $oShop_Order->shop_country_id;

			$this->shop_country_location_id == 0
				&& $oShop_Order->shop_country_location_id > 0
				&& $this->shop_country_location_id = $oShop_Order->shop_country_location_id;

			$this->shop_country_location_city_id == 0
				&& $oShop_Order->shop_country_location_city_id > 0
				&& $this->shop_country_location_city_id = $oShop_Order->shop_country_location_city_id;

			$this->shop_country_location_city_area_id == 0
				&& $oShop_Order->shop_country_location_city_area_id > 0
				&& $this->shop_country_location_city_area_id = $oShop_Order->shop_country_location_city_area_id;
			$this->postcode == ''
				&& $oShop_Order->postcode != ''
				&& $this->postcode = $oShop_Order->postcode;

			$this->address == ''
				&& $oShop_Order->address != ''
				&& $this->address = $oShop_Order->address;

			$this->house == ''
				&& $oShop_Order->house != ''
				&& $this->house = $oShop_Order->house;

			$this->flat == ''
				&& $oShop_Order->flat != ''
				&& $this->flat = $oShop_Order->flat;
		}

		if ($this->surname == '' && $this->name == '' && $this->patronymic == '')
		{
			$this->surname = $oShop_Order->surname;
			$this->name = $oShop_Order->name;
			$this->patronymic = $oShop_Order->patronymic;
		}

		$this->company == ''
			&& $oShop_Order->company != ''
			&& $this->company = $oShop_Order->company;

		$this->phone == ''
			&& $oShop_Order->phone != ''
			&& $this->phone = $oShop_Order->phone;

		$this->fax == ''
			&& $oShop_Order->fax != ''
			&& $this->fax = $oShop_Order->fax;

		$this->email == ''
			&& $oShop_Order->email != ''
			&& $this->email = $oShop_Order->email;

		$this->tin == ''
			&& $oShop_Order->tin != ''
			&& $this->tin = $oShop_Order->tin;

		$this->kpp == ''
			&& $oShop_Order->kpp != ''
			&& $this->kpp = $oShop_Order->kpp;

		// Описание
		$this->description == ''
			&& $oShop_Order->description != ''
			&& $this->description = $oShop_Order->description;

		$this->system_information == ''
			&& $oShop_Order->system_information != ''
			&& $this->system_information = $oShop_Order->system_information;

		$this->delivery_information == ''
			&& $oShop_Order->delivery_information != ''
			&& $this->delivery_information = $oShop_Order->delivery_information;

		$this->save();

		$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oNew_Shop_Order_Item)
		{
			if ($oNew_Shop_Order_Item->shop_item_id)
			{
				$oShop_Order_Items = $this->Shop_Order_Items;
				$oShop_Order_Items->queryBuilder()
					->where('shop_order_items.shop_item_id', '=', $oNew_Shop_Order_Item->shop_item_id)
					->where('shop_order_items.type', '=', $oNew_Shop_Order_Item->type);

				$oShop_Order_Item = $oShop_Order_Items->getFirst();
			}
			else
			{
				$oShop_Order_Item = NULL;
			}

			if (is_null($oShop_Order_Item))
			{
				$oNew_Shop_Order_Item->shop_order_id = $this->id;
				$oNew_Shop_Order_Item->save();
			}
			else
			{
				// Товар
				if ($oShop_Order_Item->type == 0)
				{
					$oShop_Order_Item->quantity += 1;
					$oShop_Order_Item->save();
				}

				$oNew_Shop_Order_Item->markDeleted();
			}
		}

		$oShop_Order->markDeleted();

		return $this;
	}

	/**
	 * Get entity description
	 * @return string
	 */
	public function getTrashDescription()
	{
		$aReturn = array();
		if (strlen($this->company))
		{
			$aReturn[] = Core::_('Shop_Order.payer') . ': ' . htmlspecialchars($this->company);
		}

		$person = trim($this->surname . ' ' . $this->name . ' ' . $this->patronymic);

		if ($person !== '')
		{
			$aReturn[] = Core::_('Shop_Order.order_card_contact_person') . ': ' . htmlspecialchars($person);
		}

		$aReturn[] = Core::_('Shop_Order.order_card_address') . ': ' . htmlspecialchars($this->getFullAddress());

		if ($this->phone !== '')
		{
			$aReturn[] = Core::_('Shop_Order.order_card_phone') . ': ' . htmlspecialchars($this->phone);
		}
		if ($this->email !== '')
		{
			$aReturn[] = Core::_('Shop_Order.order_card_email') . ': ' . htmlspecialchars($this->email);
		}
		if ($this->shop_payment_system_id)
		{
			$aReturn[] = Core::_('Shop_Order.order_card_paymentsystem') . ': ' . htmlspecialchars($this->Shop_Payment_System->name);
		}
		if ($this->shop_order_status_id)
		{
			$aReturn[] = Core::_('Shop_Order.order_card_order_status') . ': ' . htmlspecialchars($this->Shop_Order_Status->name);
		}
		if (!is_null($this->description) && $this->description !== '')
		{
			$aReturn[] = Core::_('Shop_Order.order_card_description') . ': ' . htmlspecialchars($this->description);
		}
		if ($this->payment_datetime != '0000-00-00 00:00:00')
		{
			 $aReturn[] = Core::_('Shop_Order.order_card_status_of_pay') . ': ' . Core_Date::sql2datetime($this->payment_datetime);
		}

		return implode("\n<br />", $aReturn);
	}

	/**
	 * Check shop order status deadline
	 * @return self
	 */
	public function checkShopOrderStatusDeadline()
	{
		if ($this->shop_order_status_deadline != '0000-00-00 00:00:00'
			&& Core_Date::sql2timestamp($this->shop_order_status_deadline) < time()
		)
		{
			if ($this->Shop_Order_Status->deadline_shop_order_status_id)
			{
				Core_Entity::factory('Shop_Order_Status', $this->Shop_Order_Status->deadline_shop_order_status_id)->setStatus($this);
			}
		}

		return $this;
	}

	/**
	 * Show content
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function showContent($oAdmin_Form_Controller)
	{
		ob_start();
		?>

		<div class="event-title"><?php echo htmlspecialchars(Core::_('Shop_Order.popover_title', $this->invoice, Core_Date::sql2date($this->datetime)))?></div>
		<div class="small"><?php echo Core::_('Shop_Order.table_amount_value')?>: <?php echo $this->sum()?></div>

		<?php
		return	ob_get_clean();
	}

	/**
	 * Apply tags for item
	 * @param string $sTags string of tags, separated by comma
	 * @return self
	 */
	public function applyTags($sTags)
	{
		$aTags = explode(',', $sTags);

		return $this->applyTagsArray($aTags);
	}

	/**
	 * Apply array tags for item
	 * @param array $aTags array of tags
	 * @return self
	 */
	public function applyTagsArray(array $aTags)
	{
		// Удаляем связь метками
		$this->Tag_Shop_Orders->deleteAll(FALSE);

		foreach ($aTags as $tag_name)
		{
			$tag_name = trim($tag_name);

			if ($tag_name != '')
			{
				$oTag = Core_Entity::factory('Tag')->getByName($tag_name, FALSE);

				if (is_null($oTag))
				{
					$oTag = Core_Entity::factory('Tag');
					$oTag->name = $oTag->path = $tag_name;
					$oTag->save();
				}

				$this->add($oTag);
			}
		}

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_order.onBeforeGetRelatedSite
	 * @hostcms-event shop_order.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}