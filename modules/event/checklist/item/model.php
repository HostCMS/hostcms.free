<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Event_Checklist_Item_Model
 *
 * @package HostCMS
 * @subpackage Event
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Event_Checklist_Item_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'event_checklist' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'event_checklist_items.id' => 'ASC',
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
}