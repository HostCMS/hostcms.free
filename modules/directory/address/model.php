<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Address_Model
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Directory_Address_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'directory_address';

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
		'company' => array('through' => 'company_directory_address'),
		'company_directory_address' => array(),
		'siteuser_company' => array('through' => 'siteuser_company_directory_address'),
		'siteuser_company_directory_address' => array(),
		'lead' => array('through' => 'lead_directory_address'),
		'lead_directory_address' => array()
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'directory_address_type' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'directory_addresses.id' => 'ASC'
	);

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event directory_address.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Company_Directory_Addresses->deleteAll(FALSE);

		if (Core::moduleIsActive('lead'))
		{
			$this->Lead_Directory_Addresses->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser_Company_Directory_Addresses->deleteAll(FALSE);
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event directory_address.onBeforeRedeclaredGetXml
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
	 * @hostcms-event directory_address.onBeforeRedeclaredGetStdObject
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
		$this->clearXmlTags();

		$aFullAddress = array(
			$this->postcode,
			$this->country,
			$this->city,
			$this->value
		);

		$aFullAddress = array_filter($aFullAddress, 'strlen');
		$sFullAddress = implode(', ', $aFullAddress);

		$this->addXmlTag('full_address', $sFullAddress);

		$this->directory_address_type_id
			&& $this->addXmlTag('name', $this->Directory_Address_Type->name);

		return $this;
	}
}