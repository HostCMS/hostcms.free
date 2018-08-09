<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract inflection
 *
 * @package HostCMS
 * @subpackage Core\Inflection
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Inflection
{
	/**
	 * List of language drivers
	 * @var array
	 */
	static protected $_drivers = array();

	/**
	 * Get driver instance
	 * @param string $lng driver name
	 * @return mixed
	 */
	static protected function _getDriver($lng = 'en')
	{
		if (!isset(self::$_drivers[$lng]))
		{
			$className = __CLASS__ . '_' . ucfirst($lng);
			self::$_drivers[$lng] = new $className();
		}

		return self::$_drivers[$lng];
	}

	/**
	 * Get plural form of word
	 * @param string $word word
	 * @param int $count
	 * @param string $lng driver
	 * @return string
	 */
	static public function getPlural($word, $count = NULL, $lng = 'en')
	{
		$aWord = explode('_', $word);

		$last = self::_getDriver($lng)->__getPlural(array_pop($aWord), $count);

		return isset($aWord[0])
			? implode('_', $aWord) . '_' . $last
			: $last;
	}

	/**
	 * Get singular form of word
	 * @param string $word word
	 * @param int $count
	 * @param string $lng driver
	 * @return string
	 */
	static public function getSingular($word, $count = NULL, $lng = 'en')
	{
		$aWord = explode('_', $word);

		$last = self::_getDriver($lng)->__getSingular(array_pop($aWord), $count);

		return isset($aWord[0])
			? implode('_', $aWord) . '_' . $last
			: $last;
	}

	/**
	 * Maximum count of objects
	 * Максимальное количество объектов
	 * @var int
	 */
	static protected $_maxObjects = 512;

	/**
	 * Cache
	 * @var array
	 */
	protected $_pluralCache = array();

	/**
	 * Get plural form by singular
	 * @param string $word word
	 * @param int $count
	 * @return string
	 */
	protected function __getPlural($word, $count = NULL)
	{
		if (is_null($count) && isset($this->_pluralCache[$word]))
		{
			return $this->_pluralCache[$word];
		}

		if (rand(0, self::$_maxObjects) == 0 && count($this->_pluralCache) > self::$_maxObjects)
		{
			$this->_pluralCache = array_slice($this->_pluralCache, floor(self::$_maxObjects / 4));
		}

		$plural = $this->_getPlural($word, $count);
		is_null($count) && $this->_pluralCache[$word] = $plural;

		return $plural;
	}

	/**
	 * Cache
	 * @var array
	 */
	protected $_singularCache = array();

	/**
	 * Get singular form by plural
	 * @param string $word word
	 * @param int count
	 * @return string
	 */
	protected function __getSingular($word, $count = NULL)
	{
		if (is_null($count) && isset($this->_singularCache[$word]))
		{
			return $this->_singularCache[$word];
		}

		if (rand(0, self::$_maxObjects) == 0 && count($this->_singularCache) > self::$_maxObjects)
		{
			$this->_singularCache = array_slice($this->_singularCache, floor(self::$_maxObjects / 4));
		}

		$singular = $this->_getSingular($word, $count);
		is_null($count) && $this->_singularCache[$word] = $singular;

		return $singular;
	}

	/**
	 * Number to str
	 * @param float $float
	 * @param string $lng
	 * @return string
	 */
	static public function num2str($float, $lng = 'en')
	{
		return self::_getDriver($lng)->_num2str($float);
	}
}