<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib_Property_List_Value_Model
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Lib_Property_List_Value_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'lib_property' => array(),
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
}