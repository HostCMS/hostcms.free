<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Crm_Project_Note_Model
 *
 * @package HostCMS
 * @subpackage Crm
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Crm_Project_Note_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'crm_project' => array(),
		'user' => array()
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'text';

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		'datetime',
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Check user access to admin form action
	 * @param string $actionName admin form action name
	 * @param User_Model $oUser user object
	 * @return bool
	 */
	public function checkBackendAccess($actionName, $oUser)
	{
		switch ($actionName)
		{
			case 'edit':
				if ($this->user_id == $oUser->id)
				{
					return TRUE;
				}
			break;
			case 'markDeleted':
				if ($this->user_id == $oUser->id || $this->Crm_Project->user_id == $oUser->id)
				{
					return TRUE;
				}
			break;
			case 'delete':
			case 'undelete':
				if ($oUser->superuser)
				{
					return TRUE;
				}
			break;
			case 'addNote':
				return is_null($this->id);
			break;
		}

		return FALSE;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event crm_project_note.onBeforeGetRelatedSite
	 * @hostcms-event crm_project_note.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Crm_Project->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}