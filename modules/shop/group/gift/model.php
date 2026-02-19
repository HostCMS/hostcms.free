<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Group_Gift_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
 class Shop_Group_Gift_Model extends Core_Entity
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
		'shop_group' => array(),
		'shop_gift' => array(),
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
		}
	}

	/**
	 * Get element by Gift id
	 * @param int $iGiftId id
	 * @return Shop_Gift|NULL
	 */
	public function getByGiftId($iGiftId)
	{
		$this->queryBuilder()
			//->clear()
			->where('shop_group_gifts.shop_gift_id', '=', $iGiftId)
			->limit(1);

		$aShop_Giftes = $this->findAll();

		return isset($aShop_Giftes[0])
			? $aShop_Giftes[0]
			: NULL;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_group_gift.onBeforeGetRelatedSite
	 * @hostcms-event shop_group_gift.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Gift->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}