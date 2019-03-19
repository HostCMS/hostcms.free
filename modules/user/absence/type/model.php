<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Absence_Model
 *
 * @package HostCMS
 * @subpackage User
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class User_Absence_Type_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'user_absence_type';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'user_absence' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'user_absence_types.sorting' => 'ASC',
	);

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend()
	{
		return '<i class="fa fa-circle" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4' ) . '"></i> '
			. '<span class="editable" id="apply_check_0_' . $this->id . '_fv_1255">' . htmlspecialchars($this->name) . '</span>';
	}
	
	/**
	 * Backend callback method
	 * @return string
	 */
	public function abbrBackend()
	{
		return $this->getTypeAbbrHtml();
	}
	
	/**
	 * Get Abbr inside label
	 * @return string
	 */
	public function getTypeAbbrHtml()
	{
		return '<span class="label" title="' . htmlspecialchars($this->name) . '" style="background-color: ' . Core_Str::hex2lighter($this->color, 0.8)
			. '; color: ' . $this->color . '">' . htmlspecialchars($this->abbr) . '</span>';
	}
	
	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event user_absence_type.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->User_Absences->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}