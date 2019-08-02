<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Affiliate_Plan_Level_Model
 *
 * @package HostCMS
 * @subpackage Affiliate
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
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
}