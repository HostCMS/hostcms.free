<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Comment_Property_Dir_Model
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Informationsystem_Comment_Property_Dir_Model extends Core_Entity
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
		'informationsystem' => array(),
		'property_dir' => array(),
	);

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event informationsystem_comment_property_dir.onBeforeGetRelatedSite
	 * @hostcms-event informationsystem_comment_property_dir.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Informationsystem->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}