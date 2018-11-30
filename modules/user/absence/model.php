<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Absence_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Absence_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'user_absence';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array(),
		'user_absence_type' => array(),
		'employee' => array('foreign_key' => 'employee_id', 'model' => 'user')
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'user_absences.id' => 'ASC',
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function employee_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oEmployee = $this->Employee;

		ob_start();

		?><div class="contracrot"><div class="user-image"><img class="contracrot-ico" src="<?php echo $oEmployee->getAvatar()?>" /></div><div class="user-name" style="margin-top: 8px;"><a class="darkgray" href="/admin/user/index.php?hostcms[action]=view&hostcms[checked][0][<?php echo $oEmployee->id?>]=1" onclick="$.modalLoad({path: '/admin/user/index.php', action: 'view', operation: 'modal', additionalParams: 'hostcms[checked][0][<?php echo $oEmployee->id?>]=1', windowId: 'id_content'}); return false"><?php echo htmlspecialchars($oEmployee->getFullName())?></a></div></div><?php

		return ob_get_clean();
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function user_absence_type_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->user_absence_type_id
			? $this->User_Absence_Type->getTypeAbbrHtml()
			: '';
	}

	/**
	 * Check user access to admin form action
	 * @param User_Model $oUser user object
	 * @param string $actionName admin form action name
	 * @return bool
	 */
	public function checkBackendAccess($actionName, $oUser)
	{
		return $oUser->isHeadOfEmployee($this->Employee);
	}
}