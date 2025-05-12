<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Producer_Discount_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Producer_Discount_Model extends Core_Entity
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
		'shop_producer' => array(),
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
		return $this->Shop_Producer->name;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_item_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->Shop_Producer->id;
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
			$oShop_Producer_Discounts = Core_Entity::factory('Shop_Producer_Discount');
			$oShop_Producer_Discounts->queryBuilder()
				->select(array('COUNT(*)', 'dataCount'))
				->where('shop_producer_discounts.siteuser_id', '=', $this->siteuser_id)
				->where('shop_producer_discounts.shop_producer_id', '>', 0)
				->groupBy('shop_producer_discounts.shop_discount_id');

			$aShop_Producer_Discounts = $oShop_Producer_Discounts->findAll(FALSE);

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

			foreach ($aShop_Producer_Discounts as $key => $oShop_Producer_Discount)
			{
				$color = $aColors[$key % $iCountColors];

				$oShop_Discount = $oShop_Producer_Discount->Shop_Discount;

				?><div class="margin-bottom-5 d-flex align-items-center personal-discount"><?php
					Core_Html_Entity::factory('Span')
						->class("badge badge-{$color} margin-right-10")
						->title(Core::_('Shop_Discount_Siteuser.quantity'))
						->value($oShop_Producer_Discount->dataCount)
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
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_itemsBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$shop_id = Core_Array::getGet('shop_id', 0, 'int');
		$shop_group_id = Core_Array::getGet('shop_group_id', 0, 'int');

		$additionalParam = "shop_producer_discount_id={$this->id}&siteuser_id={$this->siteuser_id}&shop_id={$shop_id}&shop_group_id={$shop_group_id}";

		$link = $oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/discount/siteuser/producer/index.php', NULL, NULL, $additionalParam);
		$onclick = $oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/discount/siteuser/producer/index.php', NULL, NULL, $additionalParam);

		ob_start();

		Core_Html_Entity::factory('A')
			->href($link)
			->onclick($onclick)
			->add(
				Core_Html_Entity::factory('I')->class('fa-solid fa-bars')
			)
			->execute();

		return ob_get_clean();
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
	public function imgBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<i class="fa fa-industry icon-separator"></i>';
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
	 * @hostcms-event shop_group_discount.onBeforeGetRelatedSite
	 * @hostcms-event shop_group_discount.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Producer->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}