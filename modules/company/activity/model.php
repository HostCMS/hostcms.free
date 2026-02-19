<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Activity_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Company_Activity_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'dms_document' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'company' => array(),
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
	public function nameBackend($oAdmin_Form_Field)
	{
		return '<i class="fa fa-circle" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '"></i> '
			. '<span class="editable" id="apply_check_0_' . $this->id . '_fv_' . $oAdmin_Form_Field->id . '">' . htmlspecialchars($this->name) . '</span>';
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event company_activity.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		if (Core::moduleIsActive('dms'))
		{
			Core_QueryBuilder::update('dms_documents')
				->set('company_activity_id', 0)
				->where('company_activity_id', '=', $this->id)
				->execute();
		}

		return parent::delete($primaryKey);
	}
}