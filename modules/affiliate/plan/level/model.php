<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Affiliate_Plan_Level_Model
 *
 * @package HostCMS
 * @subpackage Affiliate
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Affiliate_Plan_Level_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'affiliate_plan' => array(),
		'user' => array()
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
 		'type' => 0,
		'level' => 1
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'affiliate_plan_levels.level' => 'ASC'
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
	 * Save object.
	 *
	 * @return Core_Entity
	 */
	public function save()
	{
		$this->type == 0
			? $this->value = 0
			: $this->percent = 0;

		return parent::save();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event affiliate_plan_level.onBeforeGetRelatedSite
	 * @hostcms-event affiliate_plan_level.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Affiliate_Plan->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}