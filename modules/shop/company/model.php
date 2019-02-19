<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Company_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Company_Model extends Company_Model
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'shop_company';

	/**
	 * Table name
	 * @var mixed
	 */
	protected $_tableName = 'companies';

	/**
	 * Name of the tag in XML
	 * @var string
	 */
	protected $_tagName = 'shop_company';

	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'deleted',
		'user_id',
		/*'~address',
		'~phone',
		'~fax',
		'~site',
		'~email'*/
	);

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
			$aCompanyAddress = array(
				$aDirectory_Addresses[0]->postcode,
				$aDirectory_Addresses[0]->country,
				$aDirectory_Addresses[0]->city,
				$aDirectory_Addresses[0]->value
			);

			$aCompanyAddress = array_filter($aCompanyAddress, 'strlen');
			$sFullCompanyAddress = implode(', ', $aCompanyAddress);

			$this->addXmlTag('address', $sFullCompanyAddress);
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

	static protected $_oldFields = array('address', 'phone', 'fax', 'site', 'email');

	public function __get($property)
	{
		if (in_array($property, self::$_oldFields))
		{
			switch ($property)
			{
				case 'address':
					// Directory_Addresses
					$aDirectory_Addresses = $this->Directory_Addresses->findAll();
					$return = isset($aDirectory_Addresses[0])
						? $aDirectory_Addresses[0]->value
						: '';
				break;
				case 'phone':
					// Directory_Phones
					$aDirectory_Phones = $this->Directory_Phones->findAll();
					$return = isset($aDirectory_Phones[0])
						? $aDirectory_Phones[0]->value
						: '';
				break;
				case 'email':
					// Directory_Emails
					$aDirectory_Emails = $this->Directory_Emails->findAll();
					$return = isset($aDirectory_Emails[0])
						? $aDirectory_Emails[0]->value
						: '';
				break;
				case 'site':
					// Directory_Websites
					$aDirectory_Websites = $this->Directory_Websites->findAll();
					$return = isset($aDirectory_Websites[0])
						? $aDirectory_Websites[0]->value
						: '';
				break;
				default:
					$return = NULL;
			}

			return $return;
		}

		return parent::__get($property);
	}

	public function __call($name, $arguments)
	{
		if (in_array($name, self::$_oldFields))
		{
			//$this->$name = $arguments[0];
			return $this;
		}

		return parent::__call($name, $arguments);
	}

	public function __isset($property)
	{
		if (in_array($property, self::$_oldFields))
		{
			return TRUE;
		}

		return parent::__isset($property);
	}
}