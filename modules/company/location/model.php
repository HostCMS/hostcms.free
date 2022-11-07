<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Location_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Location_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'company_location' => array('foreign_key' => 'parent_id'),
		'dms_case' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'company' => array(),
		'company_department' => array(),
		'company_location' => array('foreign_key' => 'parent_id'),
		'responsible' => array('model' => 'User', 'foreign_key' => 'responsible_user_id'),
		'user' => array(),
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
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$return = '<a href="' . $link . '" onclick="' . $onclick . '">' . htmlspecialchars($this->name) . '</a>';

		$count = $this->getChildCount();
		$count
			&& $return .= '<span class="badge badge-hostcms badge-square margin-left-5">' . $count . '</span>';

		return $return;
	}

	/**
	 * Get parent dms document type
	 * @return Company_Location|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Company_Location', $this->parent_id)
			: NULL;
	}

	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Company_Locations->getCount();

		$aCompany_Locations = $this->Company_Locations->findAll(FALSE);
		foreach ($aCompany_Locations as $oCompany_Location)
		{
			$count += $oCompany_Location->getChildCount();
		}

		return $count;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	public function company_department_idBackend()
	{
		return htmlspecialchars((string) $this->Company_Department->name);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
 	public function responsible_user_idBackend()
	{
		return $this->responsible_user_id
			? $this->Responsible->showAvatarWithName()
			: '';
	}

	/**
	 * Get path name to root
	 * @param string $sGlue separator
	 * @return string
	 */
	public function getPathName2Root($sGlue = ' / ')
	{
		$sReturn = $this->name;

		if (!is_null($oParentCompanyLocation = $this->getParent()))
		{
			$sReturn = $oParentCompanyLocation->getPathName2Root() . $sGlue . $sReturn;
		}

		return $sReturn;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event company_location.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Company_Locations->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}