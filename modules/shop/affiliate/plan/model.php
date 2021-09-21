<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Affiliate_Plan_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Affiliate_Plan_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'affiliate_plan' => array(),
		'shop' => array()
	);

	/**
	 * Get affiliate plan by shop id.
	 * @param int $iShopId shop id
	 * @return Shop_Affiliate_Plan_Model|NULL
	 */
	public function getByShopId($iShopId)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_id', '=', $iShopId)
			->limit(1);

		$aObjects = $this->findAll();

		return isset($aObjects[0])
			? $aObjects[0]
			: NULL;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_affiliate_plan.onBeforeGetRelatedSite
	 * @hostcms-event shop_affiliate_plan.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}