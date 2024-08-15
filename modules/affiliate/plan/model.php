<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Affiliate_Plan_Model
 *
 * @package HostCMS
 * @subpackage Affiliate
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Affiliate_Plan_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var int
	 */
	public $levels = 0;

	/**
	 * Backend property
	 * @var int
	 */
	public $accepted;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'siteuser_group' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop' => array('through' => 'shop_affiliate_plan'),
		'shop_affiliate_plan' => array(),
		'affiliate_plan_level' => array(),
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'min_count_of_items' => 0,
		'min_amount_of_items' => 0
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
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return self
	 * @hostcms-event affiliate_plan.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Affiliate_Plans->deleteAll(FALSE);
		$this->Affiliate_Plan_Levels->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Change affiliate plan status for shop to on
	 */
	public function changeStatusOn()
	{
		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));

		$oShopAffiliatePlan =	$this
			->Shop_Affiliate_Plans
			->getByShop_id($oShop->id);

		if (is_null($oShopAffiliatePlan))
		{
			$this->add($oShop);
		}
	}

	/**
	 * Change affiliate plan status for shop to off
	 */
	public function changeStatusOff()
	{
		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));

		$oShopAffiliatePlan =	$this
			->Shop_Affiliate_Plans
			->getByShop_id($oShop->id);

		if (!is_null($oShopAffiliatePlan))
		{
			$oShopAffiliatePlan->delete();
		}
	}

	/**
	 * Change affiliate plan status for shop
	 * @return self
	 */
	public function changeStatus()
	{
		$oAffiliatePlan = Core_Entity::factory('Affiliate_Plan', $this->id);
		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));

		$oShopAffiliatePlan = $oAffiliatePlan
			->Shop_Affiliate_Plans
			->getByShop_id($oShop->id);

		if (is_null($oShopAffiliatePlan))
		{
			$oAffiliatePlan->add($oShop);
		}
		else
		{
			$oShopAffiliatePlan->delete();
		}
		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event affiliate_plan.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event affiliate_plan.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addEntities($this->Affiliate_Plan_Levels->findAll());

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function levelsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Affiliate_Plan_Levels->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-azure white')
			->value($count)
			->execute();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event affiliate_plan.onBeforeGetRelatedSite
	 * @hostcms-event affiliate_plan.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}