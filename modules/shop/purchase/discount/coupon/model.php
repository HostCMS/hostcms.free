<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Purchase_Discount_Coupon_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Purchase_Discount_Coupon_Model extends Core_Entity
{
	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'count' => -1,
		'active' => 1
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_purchase_discount' => array(),
		'shop_order' => array(),
		'user' => array()
	);

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

			$this->_preloadValues['start_datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['end_datetime'] = '2030-12-31 23:59:59';
		}
	}

	/**
	 * Generate Unique Random Coupon Code
	 * @return self
	 * @hostcms-event shop_purchase_discount_coupon.onAfterGenerateCode
	 */
	public function generateCode()
	{
		$this->text = sprintf("%03d-%03d-%03d-%03d", rand(0, 999), rand(0, 999), rand(0, 999), rand(0, 999));

		Core_Event::notify($this->_modelName . '.onAfterGenerateCode', $this);

		return $this;
	}

	/**
	 * Change status of activity for coupon
	 * @return self
	 */
	public function changeStatus()
	{
		$this->active = 1 - $this->active;
		$this->save();
		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_purchase_discount_coupon.onBeforeGetRelatedSite
	 * @hostcms-event shop_purchase_discount_coupon.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Purchase_Discount->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}