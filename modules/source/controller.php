<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Source.
 *
 * @package HostCMS
 * @subpackage Source
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Source_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'type',
		'service',
		'campaign',
		'ad',
		'source',
		'medium',
		'content',
		'term'
	);

	/**
	 * Save setting into cookies
	 * @return self
	 */
	public function apply()
	{
		if (!headers_sent())
		{
			$domain = strtolower(Core_Array::get($_SERVER, 'HTTP_HOST'));

			if (!empty($domain))
			{
				// Обрезаем www у домена
				strpos($domain, 'www.') === 0 && $domain = substr($domain, 4);

				$domain = strpos($domain, '.') !== FALSE && !Core_Valid::ip($domain)
					? '.' . $domain
					: '';

				$expired = time() + 31536000;

				foreach ($this->_allowedProperties as $propertyName)
				{
					!is_null($this->$propertyName) && setcookie('hostcms_source_' . $propertyName, $this->$propertyName, $expired, '/', $domain);
				}
			}
		}

		return $this;
	}

	/**
	 * Get source ID
	 * @return int
	 */
	public function getId()
	{
		if (isset($_COOKIE['hostcms_source_type']))
		{
			$oSource = Core_Entity::factory('Source');

			foreach ($this->_allowedProperties as $propertyName)
			{
				$cookieName = 'hostcms_source_' . $propertyName;

				isset($_COOKIE[$cookieName]) && $oSource->$propertyName = $_COOKIE[$cookieName];
			}

			$oSource->save();

			return $oSource->id;
		}

		return 0;
	}
}