<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Setting_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class User_Setting_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Type:
	 * 77 - Widgets
	 * 95 - Wallpapers
	 * 98 - Notes
	 * 99 - Shortcuts
	 */

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array(),
		'module' => array()
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
	 * Get settings by module ID
	 * @param int $module_id module ID
	 * @return array
	 */
	public function getByModuleId($module_id)
	{
		$this->queryBuilder()
			//->clear()
			->where('module_id', '=', $module_id);

		return $this->findAll();
	}

	/**
	 * Get user settings
	 * @param int $module_id module ID
	 * @param int $type type
	 * @param int $entity_id entity ID
	 * @return Users_Setting
	 */
	public function getByModuleIdAndTypeAndEntityId($module_id, $type, $entity_id = 0)
	{
		$this->queryBuilder()
			//->clear()
			->where('module_id', '=', $module_id)
			->where('type', '=', $type)
			->where('entity_id', '=', $entity_id)
			->limit(1);

		$aUsers_Setting = $this->findAll();

		return isset($aUsers_Setting[0])
			? $aUsers_Setting[0]
			: NULL;
	}
}