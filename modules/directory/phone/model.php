<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Phone_Model
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Phone_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'directory_phone';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'user' => array('through' => 'user_directory_phone'),
		'user_directory_phone' => array(),

		'company_department' => array('through' => 'company_department_directory_phone'),
		'company_department_directory_phone' => array(),

		'company' => array('through' => 'company_directory_phone'),
		'company_directory_phone' => array(),

		'siteuser_company' => array('through' => 'siteuser_company_directory_phone'),
		'siteuser_company_directory_phone' => array(),

		'siteuser_person' => array('through' => 'siteuser_people_directory_phone'),
		'siteuser_person_directory_phone' => array(),

		'lead' => array('through' => 'lead_directory_phone'),
		'lead_directory_phone' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'directory_phone_type' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'directory_phones.id' => 'ASC'
	);

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event directory_phone.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Company_Directory_Phones->deleteAll(FALSE);
		$this->Company_Department_Directory_Phones->deleteAll(FALSE);

		if (Core::moduleIsActive('lead'))
		{
			$this->Lead_Directory_Phones->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser_Company_Directory_Phones->deleteAll(FALSE);
			$this->Siteuser_Person_Directory_Phones->deleteAll(FALSE);
		}

		$this->User_Directory_Phones->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event directory_phone.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event directory_phone.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addXmlTag('name', $this->Directory_Phone_Type->name);

		return $this;
	}

	/**
	 * Save object.
	 * @return self
	 */
	public function save()
	{
		if (in_array('value', $this->_changedColumns))
		{
			$this->value = Directory_Phone_Controller::format($this->value);
		}

		return parent::save();
	}
}