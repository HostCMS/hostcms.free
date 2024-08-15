<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo_Indexed_Model
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Seo_Indexed_Model extends Core_Entity
{
	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'seo_indexed';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'seo_site' => array()
	);

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event seo_indexed.onBeforeGetRelatedSite
	 * @hostcms-event seo_indexed.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Seo_Site->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}