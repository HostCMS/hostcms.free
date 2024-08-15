<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment_Shop_Order_Model
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Comment_Shop_Order_Model extends Core_Entity
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
		'shop_order' => array(),
		'comment' => array()
	);

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event comment_shop_order.onBeforeGetRelatedSite
	 * @hostcms-event comment_shop_order.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Order->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}