<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Shop_Print_Form_Handler
{
	/**
	 * Print form
	 * @var Shop_Print_Form_Model
	 */
	protected $_Shop_Print_Form_Model = NULL;

	/**
	 * Shop order
	 * @var Shop_Order_Model
	 */
	protected $_Shop_Order = NULL;

	/**
	 * Full customer address
	 * @var string
	 */
	protected $_address = NULL;

	/**
	 * Create instance of print form
	 * @param Shop_Print_Form_Model $oShop_Print_Form_Model print form
	 * @return mixed
	 */
	static public function factory(Shop_Print_Form_Model $oShop_Print_Form_Model)
	{
		require_once($oShop_Print_Form_Model->getPrintFormFilePath());

		$name = 'Shop_Print_Form_Handler' . intval($oShop_Print_Form_Model->id);

		if (class_exists($name))
		{
			return new $name($oShop_Print_Form_Model);
		}
		return NULL;
	}

	/**
	 * Constructor.
	 * @param Shop_Print_Form_Model $oShop_Print_Form_Model print form
	 */
	public function __construct(Shop_Print_Form_Model $oShop_Print_Form_Model)
	{
		$this->_Shop_Print_Form_Model = $oShop_Print_Form_Model;
	}

	/**
	 * Set order
	 * @param Shop_Order_Model $oShop_Order
	 * @return self
	 */
	public function shopOrder(Shop_Order_Model $oShop_Order)
	{
		$this->_Shop_Order = $oShop_Order;
		return $this;
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Shop_Print_Form_Handler.onBeforeExecute
	 * @hostcms-event Shop_Print_Form_Handler.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify('Shop_Print_Form_Handler.onBeforeExecute', $this);

		if (!$this->_Shop_Order->id)
		{
			throw new Core_Exception("Shop order does not exist.");
		}

		$aFullAddress = array(
			trim($this->_Shop_Order->postcode),
			$this->_Shop_Order->Shop_Country->name,
			$this->_Shop_Order->Shop_Country_Location->name,
			$this->_Shop_Order->Shop_Country_Location_City->name,
			$this->_Shop_Order->Shop_Country_Location_City_Area->name,
			trim($this->_Shop_Order->address),
			trim($this->_Shop_Order->house),
			trim($this->_Shop_Order->flat)
		);

		$aFullAddress = array_filter($aFullAddress);
		$this->_address = htmlspecialchars(implode(', ', $aFullAddress));

		Core_Event::notify('Shop_Print_Form_Handler.onAfterExecute', $this);

		return $this;
	}
}