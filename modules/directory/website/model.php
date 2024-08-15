<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Website_Model
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Website_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'directory_website';

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'user' => array('through' => 'user_directory_website'),
		'user_directory_website' => array(),

		'company' => array('through' => 'company_directory_website'),
		'company_directory_website' => array(),

		'siteuser_company' => array('through' => 'siteuser_company_directory_website'),
		'siteuser_company_directory_website' => array(),

		'siteuser_person' => array('through' => 'siteuser_people_directory_website'),
		'siteuser_person_directory_website' => array(),

		'lead' => array('through' => 'lead_directory_website'),
		'lead_directory_website' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'directory_websites.id' => 'ASC'
	);

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event directory_website.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Company_Directory_Websites->deleteAll(FALSE);

		if (Core::moduleIsActive('lead'))
		{
			$this->Lead_Directory_Websites->deleteAll(FALSE);
		}

		if (Core::moduleIsActive('siteuser'))
		{
			$this->Siteuser_Company_Directory_Websites->deleteAll(FALSE);
			$this->Siteuser_Person_Directory_Websites->deleteAll(FALSE);
		}

		$this->User_Directory_Websites->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}