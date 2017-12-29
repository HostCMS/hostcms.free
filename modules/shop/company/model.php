<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Company_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Company_Model extends Company_Model
{
	/**
	 * Model name, e.g. 'book' for 'Book_Model'
	 * @var mixed
	 */
	protected $_modelName = 'shop_company';

	/**
	 * Table name, e.g. 'books' for 'Book_Model'
	 * @var mixed
	 */
	protected $_tableName = 'companies';

	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'shop_company';

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_company.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		// Directory_Addresses
		$aDirectory_Addresses = $this->Directory_Addresses->findAll();
		if (isset($aDirectory_Addresses[0]))
		{
			$this->addXmlTag('address', $aDirectory_Addresses[0]->value);
		}

		// Directory_Phones
		$aDirectory_Phones = $this->Directory_Phones->findAll();
		if (isset($aDirectory_Phones[0]))
		{
			$this->addXmlTag('phone', $aDirectory_Phones[0]->value);
		}
		
		// Directory_Emails
		$aDirectory_Emails = $this->Directory_Emails->findAll();
		if (isset($aDirectory_Emails[0]))
		{
			$this->addXmlTag('email', $aDirectory_Emails[0]->value);
		}		

		// Directory_Websites
		$aDirectory_Websites = $this->Directory_Websites->findAll();
		if (isset($aDirectory_Websites[0]))
		{
			$this->addXmlTag('site', $aDirectory_Websites[0]->value);
		}

		return parent::getXml();
	}
}