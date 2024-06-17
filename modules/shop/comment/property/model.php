<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Comment_Property_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Comment_Property_Model extends Core_Entity
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
		'shop' => array(),
		'property' => array(),
	);

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_comment_property.onBeforeGetRelatedSite
	 * @hostcms-event shop_comment_property.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}