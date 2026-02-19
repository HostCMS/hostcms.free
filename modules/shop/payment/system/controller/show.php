<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Выбор платежной системы.
 *
 * Доступные методы:
 *
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 * - addAllowedTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, разрешенных к передаче в генерируемый XML
 * - addForbiddenTags('/node/path', array('description')) массив тегов для элементов, указанных в первом аргументе, запрещенных к передаче в генерируемый XML
 *
 * Доступные пути для методов addAllowedTags/addForbiddenTags:
 *
 * - '/' или '/shop' Магазин
 * - '/shop/shop_payment_system' Платежная система
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Payment_System_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_delivery_id'
	);

	/**
	 * Shop_Payment_Systems object
	 * @var Shop_Payment_Systems_Model
	 */
	protected $_Shop_Payment_Systems = NULL;

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
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			if ($oSiteuser)
			{
				$this->addEntity($oSiteuser->clearEntities());

				$fAmount = $oSiteuser->getTransactionsAmount($oShop);

				if ($fAmount)
				{
					$oSiteuser->addEntity(
						Core::factory('Core_Xml_Entity')
							->name('transaction_amount')
							->value($fAmount)
					);
				}
			}
		}

		$this->_Shop_Payment_Systems = $oShop->Shop_Payment_Systems;
		$this->_Shop_Payment_Systems
			->queryBuilder()
			->select('shop_payment_systems.*')
			->where('shop_payment_systems.active', '=', 1);

		if (Core_Session::hasSessionId())
		{
			Core_Session::start();
			if (isset($_SESSION['hostcmsOrder']['coupon_text']))
			 {
				 Shop_Item_Controller::coupon($_SESSION['hostcmsOrder']['coupon_text']);
			 }
		}
	}

	/**
	 * Get Shop_Payment_Systems set
	 * @return _Shop_Payment_Systems_Model
	 */
	public function shopPaymentSystems()
	{
		return $this->_Shop_Payment_Systems;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Payment_System_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		if ($this->shop_delivery_id)
		{
			$this->_Shop_Payment_Systems
				->queryBuilder()
				->join('shop_delivery_payment_systems', 'shop_delivery_payment_systems.shop_payment_system_id', '=', 'shop_payment_systems.id')
				->where('shop_delivery_payment_systems.shop_delivery_id', '=', $this->shop_delivery_id);
		}

		$aSiteuser_Group_IDs = array(0);

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
			if ($oSiteuser)
			{
				$aSiteuser_Groups = $oSiteuser->Siteuser_Groups->findAll();
				foreach ($aSiteuser_Groups as $oSiteuser_Group)
				{
					$aSiteuser_Group_IDs[] = $oSiteuser_Group->id;
				}
			}
		}

		$this->_Shop_Payment_Systems
			->queryBuilder()
			->join('shop_payment_system_siteuser_groups', 'shop_payment_system_siteuser_groups.shop_payment_system_id', '=', 'shop_payment_systems.id')
			->where('shop_payment_system_siteuser_groups.siteuser_group_id', 'IN', $aSiteuser_Group_IDs)
			->groupBy('shop_payment_systems.id');

		$aShop_Payment_Systems = $this->_Shop_Payment_Systems->findAll();
		foreach ($aShop_Payment_Systems as $oShop_Payment_System)
		{
			$oShop_Payment_System->clearEntities();
			$this->applyForbiddenAllowedTags('/shop/shop_payment_system', $oShop_Payment_System);
			$this->addEntity($oShop_Payment_System);
		}

		return parent::show();
	}
}