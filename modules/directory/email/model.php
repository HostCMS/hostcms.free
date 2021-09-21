<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Email_Model
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Directory_Email_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'directory_email';

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
		'user' => array('through' => 'user_directory_email'),
		'user_directory_email' => array(),
		'company_department' => array('through' => 'company_department_directory_email'),
		'company_department_directory_email' => array(),
		'company' => array('through' => 'company_directory_email'),
		'company_directory_email' => array(),
		'siteuser_company' => array('through' => 'siteuser_company_directory_email'),
		'siteuser_company_directory_email' => array(),
		'siteuser_person' => array('through' => 'siteuser_people_directory_email'),
		'siteuser_person_directory_email' => array(),
		'lead' => array('through' => 'lead_directory_email'),
		'lead_directory_email' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'directory_email_type' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'directory_emails.id' => 'ASC',
	);

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event directory_email.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Company_Directory_Emails->deleteAll(FALSE);
		$this->Company_Department_Directory_Emails->deleteAll(FALSE);

		if (Core::moduleIsActive('lead'))
		{
			$this->Lead_Directory_Emails->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser_Company_Directory_Emails->deleteAll(FALSE);
			$this->Siteuser_Person_Directory_Emails->deleteAll(FALSE);
		}

		$this->User_Directory_Emails->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event directory_email.onBeforeRedeclaredGetXml
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
	 * @hostcms-event directory_email.onBeforeRedeclaredGetStdObject
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
			->addXmlTag('name', $this->Directory_Email_Type->name);

		return $this;
	}
}