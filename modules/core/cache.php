<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract cache
 *
 * @package HostCMS
 * @subpackage Core\Cache
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Cache
{
	/**
	 * The singleton instances.
	 * @var array
	 */
	static public $instance = array();

	/**
	 * Driver's configuration
	 */
	protected $_config = NULL;

	/**
	 * Pack format
	 * @var string
	 */
	protected $_format = 'i*';

	/**
	 * Cleaning Cache Tags Frequency
	 * @var int
	 */
	protected $_cleaningFrequency = 5000;
	
	/**
	 * Typical cache parameters
	 */
	static public $aCaches = array(
		'expire' => 86400,
		'size' => 262144,
		'active' => TRUE,
		'tags' => TRUE,
		'compress' => FALSE
	);

	/**
	 * Check cache availability
	 * @return boolean
	 */
	abstract public function available();

	/**
	 * Check if data exists
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @return NULL|TRUE|FALSE
	 */
	public function check($key, $cacheName = 'default')
	{
		return NULL;
	}

	/**
	 * Get data from cache
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @param string $defaultValue default value if index does not exist
	 * @return mixed
	 */
	abstract public function get($key, $cacheName = 'default', $defaultValue = NULL);

	/**
	 * Set data in cache
	 * @param string $key key name
	 * @param mixed $value value
	 * @param string $cacheName cache name
	 * @return Core_Cache_Memory
	 */
	abstract public function set($key, $value, $cacheName = 'default', array $tags = array());

	/**
	 * Delete key from cache
	 * @param string $key key name
	 * @param string $cacheName cache name
	 * @return Core_Cache_Memory
	 */
	abstract public function delete($key, $cacheName = 'default');

	/**
	 * Delete all keys from cache
	 * @param string $cacheName cache name
	 * @return Core_Cache_Memory
	 */
	abstract public function deleteAll($cacheName = 'default');

	/**
	 * Get a count of keys in cache $cacheName
	 * @param string $cacheName cache name
	 * @return integer
	 */
	abstract public function getCount($cacheName = 'default');

	/**
	 * Constructor.
	 * @param array $config Driver's configuration
	 */
	protected function __construct(array $config)
	{
		// Set config
		$this->_config = $config + array(
			'name' => 'Undefined',
			'checksum' => FALSE
		);
	}

	/**
	 * Check cache config isset
	 * @param string $cacheName
	 * @return boolean
	 */
	protected function _issetCacheConfig($cacheName)
	{
		return isset($this->_config['caches'][$cacheName]);
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name
	 * @return object
	 */
	static public function instance($name = 'default')
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			$aConfig = Core::$config->get('core_cache');

			if (!isset($aConfig[$name]) || !isset($aConfig[$name]['driver']))
			{
				throw new Core_Exception('Cache \'%name\' configuration doesn\'t defined', array('%name' => $name));
			}

			$driver = $aConfig[$name]['driver'];
			self::$instance[$name] = new $driver($aConfig[$name]);

			/*if (!self::$instance[$name]->available())
			{

			}*/
		}

		return self::$instance[$name];
	}

	/**
	 * Serialize and add packed checksum
	 * @param string $value value
	 * @return string
	 */
	protected function _pack($value)
	{
		$value = serialize($value);
		if ($this->_config['checksum'])
		{
			$value = pack($this->_format, Core::crc32($value)) . $value;
		}
		return $value;
	}

	/**
	 * Unserialize and check checksum
	 * @param string $value value
	 * @return string
	 */
	protected function _unPack($value)
	{
		if (is_null($value))
		{
			return NULL;
		}

		if ($this->_config['checksum'])
		{
			$sPackedHash = substr($value, 0, 4);
			$aUnpackedHash = unpack($this->_format, $sPackedHash);

			$hash = isset($aUnpackedHash[1])
				? $aUnpackedHash[1]
				// Была ошибка при записи файла.
				: 0;

			$value = substr($value, 4);

			// Если нужно расчитывать контролькую сумму
			if (Core::crc32($value) != $hash)
			{
				return NULL;
			}
		}

		// Десериализуем данные
		return @unserialize($value);
	}

	/**
	 * Get list of caches
	 * @return array
	 */
	public function getCachesList()
	{
		return $this->_config['caches'];
	}

	/**
	 * Get key by array of $object's properties $aVars
	 * @param array $aVars array of properties
	 * @param object $object object
	 * @return string
	 */
	public function getKey($aVars, $object)
	{
		$key = '';
		foreach ($aVars as $varKey => $varValue)
		{
			$key .= $varKey . '=' . $object->$varValue . ',';
		}
		return $key;
	}

	/**
	 * Save array of tags into table
	 * @param string cache cache name
	 * @param string $actualKey cache key
	 * @param array $tags array of tags
	 * @return self
	 */
	protected function _saveTags($cacheName, $actualKey, array $tags, $expire)
	{
		if ($this->_config['caches'][$cacheName]['tags'])
		{
			$this->deleteTags($actualKey);

			foreach ($tags as $tag)
			{
				$oCache_Tag = Core_Entity::factory('Cache_Tag');
				$oCache_Tag->tag = Core::crc32($tag);
				$oCache_Tag->cache = Core::crc32($cacheName);
				$oCache_Tag->hashcrc32 = Core::crc32($actualKey);
				$oCache_Tag->hash = $actualKey;
				$oCache_Tag->expire = Core_Date::timestamp2sql($expire);
				$oCache_Tag->save();
			}
			
			if (rand(0, $this->_cleaningFrequency) == 0)
			{
				$iLimit = intval($this->_cleaningFrequency);
				
				$cleaningDate = Core_Date::timestamp2sql(time());
				
				Core_DataBase::instance()->setQueryType(3)
					->query("DELETE LOW_PRIORITY QUICK FROM `cache_tags` WHERE `expire` < '{$cleaningDate}' LIMIT {$iLimit}");
			}
		}

		return $this;
	}

	/**
	 * Delete Cache_Tags
	 * @param string $actualKey cache key
	 * @return self
	 */
	public function deleteTags($actualKey)
	{
		Core_QueryBuilder::delete('cache_tags')
			->where('hashcrc32', '=', Core::crc32($actualKey))
			->execute();

		return $this;
	}

	/**
	 * Delete cache items by tag
	 * @param string $tag
	 * @return self
	 */
	public function deleteByTag($tag)
	{
		$limit = 1000;

		do {
			$oCache_Tags = Core_Entity::factory('Cache_Tag');
			$oCache_Tags->queryBuilder()
				->where('tag', '=', Core::crc32($tag))
				->limit($limit);

			$aCache_Tags = $oCache_Tags->findAll(FALSE);
			foreach ($aCache_Tags as $oCache_Tag)
			{
				$this->_deleteByTag($oCache_Tag);
				$oCache_Tag->delete();
			}
		}
		while (count($aCache_Tags) == $limit);

		return $this;
	}

	/**
	 * Delete cache items by $oCache_Tag
	 * @param Cache_Tag_Model $oCache_Tag
	 * @return self
	 */
	protected function _deleteByTag(Cache_Tag_Model $oCache_Tag)
	{
		$this->_delete($oCache_Tag->hash);

		return $this;
	}

	/**
	 * Clear all tags for $cacheName
	 * @param string $cacheName cache name
	 * @return self
	 */
	public function clearTags($cacheName)
	{
		// Clear tagged cache
		Core_QueryBuilder::delete('cache_tags')
			->where('cache', '=', Core::crc32($cacheName))
			->execute();

		return $this;
	}
}
