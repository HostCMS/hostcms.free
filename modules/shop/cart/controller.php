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
 * - totalPackageWeight суммарный вес c упаковкой неотложенного товара
 * - totalPackageVolume суммарный объем с упаковкой неотложенного товара
 * - totalQuantityForPurchaseDiscount общее количество неотложенного товара для расчета скидки от суммы заказа
 * - totalAmountForPurchaseDiscount сумма неотложенного товара для расчета скидки от суммы заказа
 * - totalDiscountPrices цены товаров для расчета скидки на N-й товар
 *
 * <code>
 * $Shop_Cart_Controller = Shop_Cart_Controller::instance();
 * $aShop_Cart = $Shop_Cart_Controller->getAll($oShop);
 * </code>
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
abstract class Shop_Cart_Controller extends Core_Servant_Properties
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
		'totalPackageWeight',
		'totalPackageVolume',
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
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name = 'default')
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$aConfig = Core::$config->get('shop_cart_config', array()) + array(
				'default' => array(
					'driver' => 'default'
				)
			);

			if (!isset($aConfig[$name]))
			{
				throw new Core_Exception("Shop cart configuration '%driverName' doesn't defined.", array('%driverName' => $name));
			}

			$aConfigDriver = defined('CURRENT_SITE') && isset($aConfig[$name][CURRENT_SITE])
				? $aConfig[$name][CURRENT_SITE]
				: $aConfig[$name];

			if (!isset($aConfigDriver['driver']))
			{
				throw new Core_Exception("Driver configuration '%driverName' doesn't defined.", array('%driverName' => $name));
			}

			$driver = self::_getDriverName($aConfigDriver['driver']);

			self::$instance[$name] = new $driver(
				Core_Array::get($aConfig, $name, array())
			);
		}

		return self::$instance[$name];
	}

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return string
     */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

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
		Core_Event::notify('Shop_Cart_Controller.onBeforeClear', $this);

		$this->shop_item_id = NULL;

		$this->quantity = 1;
		$this->postpone = $this->shop_warehouse_id = 0;
		$this->marking = '';

		$this->_error = FALSE;

		Core_Event::notify('Shop_Cart_Controller.onAfterClear', $this);

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
		Core_Event::notify('Shop_Cart_Controller.onBeforeAdd', $this);

		if (is_null($this->shop_item_id))
		{
			$this->_error = 1;
			throw new Core_Exception('Shop item id is NULL.');
		}

		$oItem_In_Cart = $this->get();

		// Увеличиваем на количество уже в корзине
		$this->quantity = Shop_Controller::convertDecimal($this->quantity) + $oItem_In_Cart->quantity;

		Core_Event::notify('Shop_Cart_Controller.onAfterAdd', $this);

		return $this->update();
	}

	/**
	 * Move goods from session cart to database
	 * @param Shop_Model $oShop shop
	 * @return self
	 * @hostcms-event Shop_Cart_Controller.onBeforeMoveTemporaryCart
	 * @hostcms-event Shop_Cart_Controller.onAfterMoveTemporaryCart
	 * @hostcms-event Shop_Cart_Controller.onAfterMoveTemporaryCartItem
	 */
	abstract public function moveTemporaryCart(Shop_Model $oShop);

	/**
	 * Get all goods in the cart
	 * @param Shop_Model $oShop shop
	 * @return array
	 * @hostcms-event Shop_Cart_Controller.onBeforeGetAll
	 * @hostcms-event Shop_Cart_Controller.onAfterGetAll
	 */
	abstract public function getAll(Shop_Model $oShop);

	/**
	 * Get item from cart
	 * @return object
	 * @hostcms-event Shop_Cart_Controller.onBeforeGet
	 * @hostcms-event Shop_Cart_Controller.onAfterGet
	 */
	abstract public function get();

	/**
	 * Delete item from cart
	 * @return Shop_Cart_Controller
	 * @hostcms-event Shop_Cart_Controller.onBeforeDelete
	 * @hostcms-event Shop_Cart_Controller.onAfterDelete
	 */
	abstract public function delete();

	/**
	 * Update item in cart
	 * @return Shop_Cart_Controller
	 * @hostcms-event Shop_Cart_Controller.onBeforeUpdate
	 * @hostcms-event Shop_Cart_Controller.onAfterUpdate
	 */
	abstract public function update();
}