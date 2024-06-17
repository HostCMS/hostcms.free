<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Messenger_Model
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Messenger_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'directory_messenger';

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
		'user' => array('through' => 'user_directory_messenger'),
		'user_directory_messenger' => array(),
		'siteuser_company' => array('through' => 'siteuser_company_directory_messenger'),
		'siteuser_company_directory_messenger' => array(),
		'siteuser_person' => array('through' => 'siteuser_people_directory_messenger'),
		'siteuser_person_directory_messenger' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'directory_messenger_type' => array(),
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'directory_messengers.id' => 'ASC'
	);

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event directory_messenger.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->User_Directory_Messengers->deleteAll(FALSE);

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser_Company_Directory_Messengers->deleteAll(FALSE);
			$this->Siteuser_Person_Directory_Messengers->deleteAll(FALSE);
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event directory_messenger.onBeforeRedeclaredGetXml
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
	 * @hostcms-event directory_messenger.onBeforeRedeclaredGetStdObject
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
			->addXmlTag('name', $this->Directory_Messenger_Type->name);

		return $this;
	}
}