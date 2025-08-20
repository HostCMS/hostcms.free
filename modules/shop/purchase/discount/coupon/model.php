<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Purchase_Discount_Coupon_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Purchase_Discount_Coupon_Model extends Core_Entity
{
	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'count' => 1,
		'active' => 1
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_purchase_discount' => array(),
		'shop_purchase_discount_coupon_dir' => array(),
		'shop_order' => array(),
		'siteuser' => array(),
		'user' => array()
	);

	/**
	 * Backend property
	 * @var string
	 */
	public $img = 0;

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
			$this->_preloadValues['end_datetime'] = Core_Date::timestamp2sql(strtotime('+1 month'));
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
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_discount.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		$newObject = parent::copy();

		$newObject->generateCode();
		$newObject->save();

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}

	/**
	 * Move discount to another dir
	 * @param int $iShopPurchaseDiscountCouponDirId target dir id
	 * @return Core_Entity
	 * @hostcms-event shop_discount.onBeforeMove
	 * @hostcms-event shop_discount.onAfterMove
	 */
	public function move($iShopPurchaseDiscountCouponDirId)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($iShopPurchaseDiscountCouponDirId));

		$this->shop_purchase_discount_coupon_dir_id = $iShopPurchaseDiscountCouponDirId;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

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
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function siteuser_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$sResult = '';

		if (Core::moduleIsActive('siteuser') && $this->siteuser_id)
		{
			$oSiteuser = $this->Siteuser;

			if ($oSiteuser->login != '' || $oSiteuser->email != '')
			{
				$sResult .= '<div class="margin-bottom-5">';

				if ($oSiteuser->login != '')
				{
					$colorLogin = Core_Str::createColor($oSiteuser->id);

					$sResult .= '<span class="badge badge-square badge-max-width margin-right-5" style="background-color: ' . Core_Str::hex2lighter($colorLogin, 0.88) . '; color: ' . $colorLogin . '">' . htmlspecialchars($oSiteuser->login) . '</span>';
				}

				if ($oSiteuser->email != '')
				{
					$colorEmail = Core_Str::createColor($oSiteuser->email);

					$sResult .= '<span class="badge badge-square badge-max-width margin-right-5" style="background-color: ' . Core_Str::hex2lighter($colorEmail, 0.88) . '; color: ' . $colorEmail . '">' . htmlspecialchars($oSiteuser->email) . '</span>';
				}

				$sResult .= '</div>';
			}

			$aSiteuserCompanies = $oSiteuser->Siteuser_Companies->findAll();
			$aSiteuserPersons = $oSiteuser->Siteuser_People->findAll();

			if (count($aSiteuserCompanies) || count($aSiteuserPersons))
			{
				$sResult .= '<div class="profile-container tickets-container"><ul class="tickets-list">';

				foreach ($aSiteuserCompanies as $oSiteuserCompany)
				{
					$oSiteuserCompany->id
						&& $sResult .= $oSiteuserCompany->getProfileBlock();
				}

				foreach ($aSiteuserPersons as $oSiteuserPerson)
				{
					$oSiteuserPerson->id
						&& $sResult .= $oSiteuserPerson->getProfileBlock();
				}

				$sResult .= '</ul></div>';
			}
		}

		return $sResult;
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