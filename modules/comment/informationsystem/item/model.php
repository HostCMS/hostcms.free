<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment_Informationsystem_Item_Model
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Comment_Informationsystem_Item_Model extends Core_Entity
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
		'informationsystem_item' => array(),
		'comment' => array()
	);

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event comment_informationsystem_item.onBeforeGetRelatedSite
	 * @hostcms-event comment_informationsystem_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Informationsystem_Item->Informationsystem->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}