<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price_Setting_Convolution_Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Price_Setting_Convolution_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shopId',
		'limit',
		'date',
		'position',
		'timeout',
	);

	protected $_aShop_Price_SettingIDs = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this->position = 0;
		$this->timeout = 30;
	}

	protected $_currentShop_Price_Setting = NULL;
	protected $_currentShop_Price_SettingId = NULL;
	protected $_counter = 0;

	protected function _getShop_Price_Setting()
	{
		if (is_null($this->_currentShop_Price_Setting) || $this->_counter > $this->limit)
		{
			$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting');
			$oShop_Price_Setting->shop_id = $this->shopId;
			$oShop_Price_Setting->number = '';
			$oShop_Price_Setting->description = Core::_('Shop_Price_Setting_Convolution.description', $this->date);
			$oShop_Price_Setting->datetime = Core_Date::date2sql($this->date);
			$oShop_Price_Setting->posted = 0;
			$oShop_Price_Setting->save();

			$oShop_Price_Setting->number = $oShop_Price_Setting->id;
			$oShop_Price_Setting->save();

			$this->_currentShop_Price_Setting = $oShop_Price_Setting;
			$this->_aShop_Price_SettingIDs[] = $this->_currentShop_Price_SettingId = $oShop_Price_Setting->id;
			$this->_counter = 0;
		}

		$this->_counter++;

		return $this->_currentShop_Price_Setting;
	}

	/**
	 * Executes the business logic.
	 * @return array
	 */
	public function execute()
	{
		$timeout = Core::getmicrotime();

		$sDate = Core_Date::date2sql($this->date);

		$oShop = Core_Entity::factory('Shop', $this->shopId);

		$aPrices = array(0);

		$aShop_Prices = $oShop->Shop_Prices->findAll(FALSE);
		foreach ($aShop_Prices as $oShop_Price)
		{
			$aPrices[] = $oShop_Price->id;
		}

		$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

		$oShop_Items = $oShop->Shop_Items;
		$oShop_Items
			->queryBuilder()
			->where('shop_items.shortcut_id', '=', 0)
			->limit($this->limit)
			->offset($this->position)
			->clearOrderBy()
			->orderBy('id', 'ASC');

		$aShop_Items = $oShop_Items->findAll(FALSE);

		foreach ($aShop_Items as $oShop_Item)
		{
			$oShop_Price_Setting = $this->_getShop_Price_Setting();

			foreach ($aPrices as $shop_price_id)
			{
				$price = $Shop_Price_Entry_Controller->getPrice($shop_price_id, $oShop_Item->id, $sDate);

				is_null($price)
					&& $price = $oShop_Item->price;

				$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
				$oShop_Price_Setting_Item->shop_price_setting_id = $oShop_Price_Setting->id;
				$oShop_Price_Setting_Item->shop_price_id = $shop_price_id;
				$oShop_Price_Setting_Item->shop_item_id = $oShop_Item->id;
				$oShop_Price_Setting_Item->old_price = $price;
				$oShop_Price_Setting_Item->new_price = $price;
				$oShop_Price_Setting_Item->save();
			}

			$this->position++;

			if (Core::getmicrotime() - $timeout + 3 > $this->timeout)
			{
				break;
			}
		}

		if (count($aShop_Items) == 0)
		{
			return 'finish';
		}

		return 'continue';
	}

	public function postNext()
	{
		if (count($this->_aShop_Price_SettingIDs))
		{
			$id = array_shift($this->_aShop_Price_SettingIDs);
			Core_Entity::factory('Shop_Price_Setting', $id)->post();

			return TRUE;
		}

		Core_QueryBuilder::update('shop_price_settings')
			->set('deleted', 1)
			->where('shop_id', '=', $this->shopId)
			->where('deleted', '=', 0)
			->where('datetime', '<', Core_Date::date2sql($this->date))
			->execute();

		return FALSE;
	}

	/**
	 * Execute some routine before serialization
	 * @return array
	 */
	public function __sleep()
	{
		$this->_currentShop_Price_Setting = NULL;

		return array_keys(
			get_object_vars($this)
		);
	}

	/**
	 * Reestablish any database connections that may have been lost during serialization and perform other reinitialization tasks
	 * @return self
	 */
	public function __wakeup()
	{
		date_default_timezone_set(Core::$mainConfig['timezone']);

		if ($this->_currentShop_Price_SettingId)
		{
			$this->_currentShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting', $this->_currentShop_Price_SettingId);
		}

		return $this;
	}
}