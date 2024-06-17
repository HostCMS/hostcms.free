<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Compare_Controller
 *
 * Доступные методы:
 *
 * - shop_item_id($id) идентификатор товара
 * - siteuser_id($id) идентификатор пользователя сайта
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Compare_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_item_id',
		'siteuser_id'
	);

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
	}

	/**
	 * Clear compare operation's options
	 * @return Shop_Compare_Controller
	 * @hostcms-event Shop_Compare_Controller.onBeforeClear
	 * @hostcms-event Shop_Compare_Controller.onAfterClear
	 */
	public function clear()
	{
		Core_Event::notify(get_class($this) . '.onBeforeClear', $this);

		$this->shop_item_id = NULL;

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
	 * Move goods from session to database
	 * @param Shop_Model $oShop shop
	 * @return self
	 * @hostcms-event Shop_Compare_Controller.onBeforeMoveTemporaryCompare
	 * @hostcms-event Shop_Compare_Controller.onAfterMoveTemporaryCompare
	 */
	public function moveTemporaryCompare(Shop_Model $oShop)
	{
		Core_Event::notify(get_class($this) . '.onBeforeMoveTemporaryCompare', $this);

		if ($this->siteuser_id)
		{
			$aShop_Compares = $this->_getAllFromSession($oShop);

			if (count($aShop_Compares))
			{
				foreach ($aShop_Compares as $oShop_Compare)
				{
					$this->clear()
						->shop_item_id($oShop_Compare->shop_item_id)
						->siteuser_id($this->siteuser_id)
						->add();
				}

				$this->clearSessionCompare();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterMoveTemporaryCompare', $this);

		return $this;
	}

	/**
	 * Get all goods in the compare
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	public function getAll(Shop_Model $oShop)
	{
		$this->moveTemporaryCompare($oShop);

		// Проверяем наличие данных о пользователе
		$aShop_Compares = $this->siteuser_id
			? $this->_getAllFromDb($oShop)
			: $this->_getAllFromSession($oShop);

		return $aShop_Compares;
	}

	/**
	 * Clear session compare
	 * @return Shop_Compare_Controller
	 */
	public function clearSessionCompare()
	{
		if (Core_Session::hasSessionId())
		{
			Core_Session::start();
			if (isset($_SESSION['hostcmsCompare']))
			{
				unset($_SESSION['hostcmsCompare']);
			}
		}

		return $this;
	}

	/**
	 * Get all compares from database
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	protected function _getAllFromDb(Shop_Model $oShop)
	{
		$aShop_Compares = $oShop->Shop_Compares->getAllBySiteuser_id($this->siteuser_id, FALSE);

		$aTmp_Shop_Compares = array();
		foreach ($aShop_Compares as $oShop_Compare)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Compare->shop_item_id);

			if (!is_null($oShop_Item->id) && $oShop_Item->active)
			{
				$aTmp_Shop_Compares[] = $oShop_Compare;
			}
		}

		return $aTmp_Shop_Compares;
	}

	/**
	 * Get all compares from session
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	protected function _getAllFromSession(Shop_Model $oShop)
	{
		$aShop_Compares = array();

		if (Core_Session::hasSessionId())
		{
			Core_Session::start();

			$shop_id = $oShop->id;

			$aCompare = Core_Array::getSession('hostcmsCompare', array());
			$aCompare[$shop_id] = Core_Array::get($aCompare, $shop_id, array());

			foreach ($aCompare[$shop_id] as $shop_item_id => $value)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id);

				if (!is_null($oShop_Item->id) && $oShop_Item->active)
				{
					// Temporary object
					$oShop_Compare = Core_Entity::factory('Shop_Compare');
					$oShop_Compare->shop_item_id = $shop_item_id;
					$oShop_Compare->siteuser_id = 0;
					$oShop_Compare->shop_id = $shop_id;
					$aShop_Compares[] = $oShop_Compare;
				}
			}
		}

		return $aShop_Compares;
	}

	/**
	 * Get item from compare
	 * @return object
	 * @hostcms-event Shop_Compare_Controller.onBeforeGet
	 * @hostcms-event Shop_Compare_Controller.onAfterGet
	 */
	public function get()
	{
		Core_Event::notify(get_class($this) . '.onBeforeGet', $this);

		// Проверяем наличие данных о пользователе
		if ($this->siteuser_id)
		{
			$oShop_Compare = Core_Entity::factory('Shop_Compare')
				->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

			if (is_null($oShop_Compare))
			{
				$oShop_Compare = Core_Entity::factory('Shop_Compare');
				$oShop_Compare->shop_item_id = $this->shop_item_id;
				$oShop_Compare->siteuser_id = $this->siteuser_id;
			}
		}
		else
		{
			$Shop_Item = Core_Entity::factory('Shop_Item', $this->shop_item_id);

			Core_Session::hasSessionId() && Core_Session::start();
			$aCompare = Core_Array::getSession('hostcmsCompare', array());
			$aCompare[$Shop_Item->shop_id] = Core_Array::get($aCompare, $Shop_Item->shop_id, array());

			$aReturn = Core_Array::get($aCompare[$Shop_Item->shop_id], $this->shop_item_id, array()) + array(
				'shop_item_id' => $this->shop_item_id,
				'shop_id' => $Shop_Item->shop_id,
			);

			$oShop_Compare = (object)$aReturn;
		}

		Core_Event::notify(get_class($this) . '.onAfterGet', $this);

		return $oShop_Compare;
	}

	/**
	 * Delete item from compare
	 * @return Shop_Compare_Controller
	 * @hostcms-event Shop_Compare_Controller.onBeforeDelete
	 * @hostcms-event Shop_Compare_Controller.onAfterDelete
	 */
	public function delete()
	{
		Core_Event::notify(get_class($this) . '.onBeforeDelete', $this);

		// Проверяем наличие данных о пользователе
		if ($this->siteuser_id)
		{
			$oShop_Compare = Core_Entity::factory('Shop_Compare')
				->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

			!is_null($oShop_Compare) && $oShop_Compare->delete();
		}
		else
		{
			Core_Session::hasSessionId() && Core_Session::start();

			$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);

			if (!is_null($oShop_Item->id))
			{
				$oShop = $oShop_Item->Shop;

				if (isset($_SESSION['hostcmsCompare'][$oShop->id][$this->shop_item_id]))
				{
					unset($_SESSION['hostcmsCompare'][$oShop->id][$this->shop_item_id]);
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterDelete', $this);

		return $this;
	}

	/**
	 * Add item into compare
	 * @return Shop_Compare_Controller
	 * @hostcms-event Shop_Compare_Controller.onBeforeAdd
	 * @hostcms-event Shop_Compare_Controller.onAfterAdd
	 */
	public function add()
	{
		Core_Event::notify(get_class($this) . '.onBeforeAdd', $this);

		if (is_null($this->shop_item_id))
		{
			throw new Core_Exception('Shop item id is NULL.');
		}

		Core_Event::notify(get_class($this) . '.onAfterAdd', $this);

		return $this->update();
	}

	/**
	 * Update item in compare
	 * @return Shop_Compare_Controller
	 * @hostcms-event Shop_Compare_Controller.onBeforeUpdate
	 * @hostcms-event Shop_Compare_Controller.onAfterUpdate
	 */
	public function update()
	{
		Core_Event::notify(get_class($this) . '.onBeforeUpdate', $this);

		$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);

		if (!is_null($oShop_Item->id))
		{
			$oShop = $oShop_Item->Shop;
			
			// Проверяем наличие данных о пользователе
			if ($this->siteuser_id)
			{
				$oShop_Compare = Core_Entity::factory('Shop_Compare')
					->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

				if (is_null($oShop_Compare))
				{
					$oShop_Compare = Core_Entity::factory('Shop_Compare');
					$oShop_Compare->shop_item_id = $this->shop_item_id;
					$oShop_Compare->siteuser_id = $this->siteuser_id;
				}
				else
				{
					$oShop_Compare->delete();
				}

				// Вставляем данные в таблицу избранного
				$oShop_Compare->shop_id = $oShop_Item->shop_id;
				$oShop_Compare->save();
			}
			else
			{
				Core_Session::start();

				if (isset($_SESSION['hostcmsCompare'][$oShop->id][$this->shop_item_id]))
				{
					unset($_SESSION['hostcmsCompare'][$oShop->id][$this->shop_item_id]);
				}
				else
				{
					$_SESSION['hostcmsCompare'][$oShop->id][$this->shop_item_id] = 1;
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterUpdate', $this);

		return $this;
	}
}