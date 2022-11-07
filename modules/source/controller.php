<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Source.
 *
 * @package HostCMS
 * @subpackage Source
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
					!is_null($this->$propertyName) && !is_array($this->$propertyName)
						&& Core_Cookie::set('hostcms_source_' . $propertyName, $this->$propertyName, array('expires' => $expired, 'path' => '/', 'domain' => $domain));
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

			// Удалять Emoji
			$bRemoveEmoji = strtolower(Core_Array::get(Core_DataBase::instance()->getConfig(), 'charset')) != 'utf8mb4';

			foreach ($this->_allowedProperties as $propertyName)
			{
				$cookieName = 'hostcms_source_' . $propertyName;

				isset($_COOKIE[$cookieName])
					&& $oSource->$propertyName = $bRemoveEmoji ? Core_Str::removeEmoji($_COOKIE[$cookieName]) : $_COOKIE[$cookieName];
			}
			$oSource->save();

			return $oSource->id;
		}

		return 0;
	}
}