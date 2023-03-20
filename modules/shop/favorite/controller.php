<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Favorite_Controller
 *
 * Доступные методы:
 *
 * - shop_item_id($id) идентификатор товара
 * - siteuser_id($id) идентификатор пользователя сайта
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Favorite_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_item_id',
		'siteuser_id',
		'shop_favorite_list_id'
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

		$this->shop_favorite_list_id = 0;
	}

	/**
	 * Clear favorite operation's options
	 * @return Shop_Favorite_Controller
	 * @hostcms-event Shop_Favorite_Controller.onBeforeClear
	 * @hostcms-event Shop_Favorite_Controller.onAfterClear
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
	 * @hostcms-event Shop_Favorite_Controller.onBeforeMoveTemporaryFavorite
	 * @hostcms-event Shop_Favorite_Controller.onAfterMoveTemporaryFavorite
	 */
	public function moveTemporaryFavorite(Shop_Model $oShop)
	{
		Core_Event::notify(get_class($this) . '.onBeforeMoveTemporaryFavorite', $this);

		if ($this->siteuser_id)
		{
			$aShop_Favorite = $this->_getAllFromSession($oShop);

			if (count($aShop_Favorite))
			{
				foreach ($aShop_Favorite as $oShop_Favorite)
				{
					$this->clear()
						->shop_item_id($oShop_Favorite->shop_item_id)
						->siteuser_id($this->siteuser_id)
						->add();
				}
				$this->clearSessionFavorite();
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterMoveTemporaryFavorite', $this);

		return $this;
	}

	/**
	 * Get all goods in the favorite
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	public function getAll(Shop_Model $oShop)
	{
		$this->moveTemporaryFavorite($oShop);

		// Проверяем наличие данных о пользователе
		$aShop_Favorite = $this->siteuser_id
			? $this->_getAllFromDb($oShop)
			: $this->_getAllFromSession($oShop);

		return $aShop_Favorite;
	}

	/**
	 * Clear session favorite
	 * @return Shop_Favorite_Controller
	 */
	public function clearSessionFavorite()
	{
		if (Core_Session::hasSessionId())
		{
			Core_Session::start();
			if (isset($_SESSION['hostcmsFavorite']))
			{
				unset($_SESSION['hostcmsFavorite']);
			}
		}

		return $this;
	}

	/**
	 * Get all favorites from database
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	protected function _getAllFromDb(Shop_Model $oShop)
	{
		$aShop_Favorites = $oShop->Shop_Favorites->getAllBySiteuser_id($this->siteuser_id, FALSE);

		$aTmp_Shop_Favorite = array();
		foreach ($aShop_Favorites as $oShop_Favorite)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->find($oShop_Favorite->shop_item_id);

			if (!is_null($oShop_Item->id) && $oShop_Item->active)
			{
				$aTmp_Shop_Favorite[] = $oShop_Favorite;
			}
		}

		return $aTmp_Shop_Favorite;
	}

	/**
	 * Get all favorites from session
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	protected function _getAllFromSession(Shop_Model $oShop)
	{
		$aShop_Favorite = array();

		if (Core_Session::hasSessionId())
		{
			Core_Session::start();

			$shop_id = $oShop->id;

			$aFavorite = Core_Array::getSession('hostcmsFavorite', array());
			$aFavorite[$shop_id] = Core_Array::get($aFavorite, $shop_id, array());


			foreach ($aFavorite[$shop_id] as $shop_item_id)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item')->find($shop_item_id);

				if (!is_null($oShop_Item->id) && $oShop_Item->active)
				{
					// Temporary object
					$oShop_Favorite = Core_Entity::factory('Shop_Favorite');
					$oShop_Favorite->shop_item_id = $shop_item_id;
					$oShop_Favorite->siteuser_id = 0;
					$oShop_Favorite->shop_id = $shop_id;
					$aShop_Favorite[] = $oShop_Favorite;
				}
			}
		}

		return $aShop_Favorite;
	}

	/**
	 * Get item from favorite
	 * @return object
	 * @hostcms-event Shop_Favorite_Controller.onBeforeGet
	 * @hostcms-event Shop_Favorite_Controller.onAfterGet
	 */
	public function get()
	{
		Core_Event::notify(get_class($this) . '.onBeforeGet', $this);

		// Проверяем наличие данных о пользователе
		if ($this->siteuser_id)
		{
			$oShop_Favorite = Core_Entity::factory('Shop_Favorite')
				->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

			if (is_null($oShop_Favorite))
			{
				$oShop_Favorite = Core_Entity::factory('Shop_Favorite');
				$oShop_Favorite->shop_item_id = $this->shop_item_id;
				$oShop_Favorite->siteuser_id = $this->siteuser_id;
			}
		}
		else
		{
			$Shop_Item = Core_Entity::factory('Shop_Item', $this->shop_item_id);

			Core_Session::hasSessionId() && Core_Session::start();
			$aFavorite = Core_Array::getSession('hostcmsFavorite', array());
			$aFavorite[$Shop_Item->shop_id] = Core_Array::get($aFavorite, $Shop_Item->shop_id, array());

			$aReturn = Core_Array::get($aFavorite[$Shop_Item->shop_id], $this->shop_item_id, array()) + array(
				'shop_item_id' => $this->shop_item_id,
				'shop_id' => $Shop_Item->shop_id,
			);

			$oShop_Favorite = (object)$aReturn;
		}

		Core_Event::notify(get_class($this) . '.onAfterGet', $this);

		return $oShop_Favorite;
	}

	/**
	 * Delete item from favorite
	 * @return Shop_Favorite_Controller
	 * @hostcms-event Shop_Favorite_Controller.onBeforeDelete
	 * @hostcms-event Shop_Favorite_Controller.onAfterDelete
	 */
	public function delete()
	{
		Core_Event::notify(get_class($this) . '.onBeforeDelete', $this);

		// Проверяем наличие данных о пользователе
		if ($this->siteuser_id)
		{
			$oShop_Favorite = Core_Entity::factory('Shop_Favorite')
				->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

			!is_null($oShop_Favorite) && $oShop_Favorite->delete();
		}
		else
		{
			Core_Session::hasSessionId() && Core_Session::start();

			$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);

			if (!is_null($oShop_Item->id))
			{
				$oShop = $oShop_Item->Shop;

				if (isset($_SESSION['hostcmsFavorite'][$oShop->id]) && in_array($this->shop_item_id, $_SESSION['hostcmsFavorite'][$oShop->id]))
				{
					unset($_SESSION['hostcmsFavorite'][$oShop->id][
						array_search($this->shop_item_id, $_SESSION['hostcmsFavorite'][$oShop->id])
					]);
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterDelete', $this);

		return $this;
	}

	/**
	 * Add item into favorite
	 * @return Shop_Favorite_Controller
	 * @hostcms-event Shop_Favorite_Controller.onBeforeAdd
	 * @hostcms-event Shop_Favorite_Controller.onAfterAdd
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
	 * Update item in favorite
	 * @return Shop_Favorite_Controller
	 * @hostcms-event Shop_Favorite_Controller.onBeforeUpdate
	 * @hostcms-event Shop_Favorite_Controller.onAfterUpdate
	 */
	public function update()
	{
		Core_Event::notify(get_class($this) . '.onBeforeUpdate', $this);

		$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);

		if (!is_null($oShop_Item->id))
		{
			// Проверяем наличие данных о пользователе
			if ($this->siteuser_id)
			{
				$oShop_Favorite = Core_Entity::factory('Shop_Favorite')
					->getByShopItemIdAndSiteuserId($this->shop_item_id, $this->siteuser_id, FALSE);

				if (is_null($oShop_Favorite))
				{
					$oShop_Favorite = Core_Entity::factory('Shop_Favorite');
					$oShop_Favorite->shop_item_id = $this->shop_item_id;
					$oShop_Favorite->siteuser_id = $this->siteuser_id;
				}
				else
				{
					$oShop_Favorite->delete();
				}

				// Вставляем данные в таблицу избранного
				$oShop_Favorite->shop_id = $oShop_Item->shop_id;
				$oShop_Favorite->shop_favorite_list_id = $this->shop_favorite_list_id;
				$oShop_Favorite->save();
			}
			else
			{
				Core_Session::start();
				
				$oShop = $oShop_Item->Shop;

				if (isset($_SESSION['hostcmsFavorite'][$oShop->id]) && in_array($this->shop_item_id, $_SESSION['hostcmsFavorite'][$oShop->id]))
				{
					unset($_SESSION['hostcmsFavorite'][$oShop->id][
						array_search($this->shop_item_id, $_SESSION['hostcmsFavorite'][$oShop->id])
					]);
				}
				else
				{
					$_SESSION['hostcmsFavorite'][$oShop->id][] = $this->shop_item_id;
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterUpdate', $this);

		return $this;
	}
}