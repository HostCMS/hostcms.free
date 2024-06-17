<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Department_Action_Access_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Company_Department_Action_Access_Model extends Core_Entity
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
		'company_department_id' => array(),
		'admin_form_action' => array(),
		'site' => array(),
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

	/**
	 * Get element by site and action
	 * @param int $site_id site ID
	 * @param int $admin_form_action_id action ID
	 * @param bool $bCache cache
	 * @return mixed
	 */
	public function getBySiteAndAction($site_id, $admin_form_action_id, $bCache = TRUE)
	{
		$this->queryBuilder()
			// т.к. с учетом заданных в связи условий
			//->clear()
			->where('site_id', '=', $site_id)
			->where('admin_form_action_id', '=', $admin_form_action_id)
			->limit(1);

		$aCompany_Department_Action_Access = $this->findAll($bCache);

		return isset($aCompany_Department_Action_Access[0]) ? $aCompany_Department_Action_Access[0] : NULL;
	}
}