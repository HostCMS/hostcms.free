<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Memory cache driver
 *
 * @package HostCMS
 * @subpackage Core\Cache
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Cache_Memory extends Core_Cache
{
	/**
	 * Maximum number of objects
	 * Максимальное количество объектов в каждом кэше
	 * @var integer
	 */
	static protected $_maxObjects = 128;

	/**
	 * Cache storage
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Check cache availability
	 * @return boolean
	 */
	public function available()
	{
		return TRUE;
	}

	/**
	 * Check if data exists
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @return NULL|TRUE|FALSE
	 */
	public function check($key, $cacheName = 'default')
	{
		return isset($this->_data[$cacheName][$key]);
	}
	
	/**
	 * Get data from cache
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @param string $defaultValue default value if index does not exist
	 * @return mixed
	 * @hostcms-event Core_Cache.onBeforeGet
	 * @hostcms-event Core_Cache.onAfterGet
	 */
	public function get($key, $cacheName = 'default', $defaultValue = NULL)
	{
		Core_Event::notify('Core_Cache.onBeforeGet', $this);

		$return = $this->check($key, $cacheName)
			? $this->_data[$cacheName][$key]
			: $defaultValue;

		Core_Event::notify('Core_Cache.onAfterGet', $this);

		return $return;
	}

	/**
	 * Set data in cache
	 * @param string $key key name
	 * @param mixed $value value
	 * @param string $cacheName cache name
	 * @return Core_Cache_Memory
	 * @hostcms-event Core_Cache.onBeforeSet
	 * @hostcms-event Core_Cache.onAfterSet
	 */
	public function set($key, $value, $cacheName = 'default', array $tags = array())
	{
		Core_Event::notify('Core_Cache.onBeforeSet', $this, array($key, $value, $cacheName));

		// Delete old items
		if (/*rand(0, self::$_maxObjects) == 0 && */isset($this->_data[$cacheName]) && count($this->_data[$cacheName]) > self::$_maxObjects)
		{
			$this->_data[$cacheName] = array_slice($this->_data[$cacheName], floor(self::$_maxObjects / 2));
		}

		$this->_data[$cacheName][$key] = $value;

		Core_Event::notify('Core_Cache.onAfterSet', $this, array($key, $value, $cacheName));

		return $this;
	}

	/**
	 * Delete key from cache
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @return Core_Cache_Memory
	 */
	public function delete($key, $cacheName = 'default')
	{
		if (isset($this->_data[$cacheName][$key]))
		{
			unset($this->_data[$cacheName][$key]);
		}

		return $this;
	}

	/**
	 * Delete all keys from cache
	 * @param string $cacheName cache name
	 * @return Core_Cache_Memory
	 */
	public function deleteAll($cacheName = 'default')
	{
		if (isset($this->_data[$cacheName]))
		{
			unset($this->_data[$cacheName]);
		}

		return $this;
	}

	/**
	 * Get a count of keys in cache $cacheName
	 * @param string $cacheName cache name
	 * @return integer
	 */
	public function getCount($cacheName = 'default')
	{
		if (isset($this->_data[$cacheName]))
		{
			return count($this->_data[$cacheName]);
		}

		return 0;
	}
}