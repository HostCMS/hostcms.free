<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Affiliate_Plan_Model
 *
 * @package HostCMS
 * @subpackage Affiliate
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
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

		$this->clearXmlTags()
			->addEntities($this->Affiliate_Plan_Levels->findAll());

		return parent::getXml();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function levelsBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Affiliate_Plan_Levels->getCount();
		$count && Core::factory('Core_Html_Entity_Span')
			->class('badge badge-ico badge-azure white')
			->value($count)
			->execute();
	}
}