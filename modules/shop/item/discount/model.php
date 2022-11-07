<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Discount_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Discount_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Callback property_id
	 * @var int
	 */
	public $shop_items = 0;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_discount' => array(),
		'shop_item' => array(),
		'siteuser' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
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
		}
	}

	/**
	 * Get element by discount id
	 * @param int $iDiscountId id
	 * @return Shop_Discount|NULL
	 */
	public function getByDiscountId($iDiscountId)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_discount_id', '=', $iDiscountId)
			->limit(1);

		$aShop_Discounts = $this->findAll();

		return isset($aShop_Discounts[0])
			? $aShop_Discounts[0]
			: NULL;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_item_nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->Shop_Item->nameBackend();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_discount_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->shop_discount_id)
		{
			return $this->Shop_Discount->nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller);
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function discountsBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('siteuser') && $this->siteuser_id)
		{
			$oShop_Item_Discounts = Core_Entity::factory('Shop_Item_Discount');
			$oShop_Item_Discounts->queryBuilder()
				->select(array('COUNT(*)', 'dataCount'))
				->where('shop_item_discounts.siteuser_id', '=', $this->siteuser_id)
				->where('shop_item_discounts.shop_item_id', '>', 0)
				->groupBy('shop_item_discounts.shop_discount_id');

			$aShop_Item_Discounts = $oShop_Item_Discounts->findAll(FALSE);

			$aColors = array(
				'palegreen',
				'azure',
				'warning',
				'pink',
				'maroon',
				'darkorange',
				'sky'
			);
			$iCountColors = count($aColors);

			foreach ($aShop_Item_Discounts as $key => $oShop_Item_Discount)
			{
				$color = $aColors[$key % $iCountColors];

				$oShop_Discount = $oShop_Item_Discount->Shop_Discount;

				?><div class="margin-bottom-5 d-flex align-items-center personal-discount"><?php
					Core_Html_Entity::factory('Span')
						->class("badge badge-{$color} margin-right-10")
						->title(Core::_('Shop_Discount_Siteuser.quantity'))
						->value($oShop_Item_Discount->dataCount)
						->execute();

					echo $oShop_Discount->nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller);
				?></div><?php
			}
		}
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
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_itemsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->dataCount;
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-darkorange white')
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
	public function dataLoginBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('siteuser') && $this->siteuser_id)
		{
			return htmlspecialchars($this->dataLogin);
		}
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_item_discount.onBeforeGetRelatedSite
	 * @hostcms-event shop_item_discount.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Item->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}