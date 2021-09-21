<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract inflection
 *
 * @package HostCMS
 * @subpackage Core\Inflection
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Inflection
{
	/**
	 * Array of irregular form singular => plural
	 * @var array
	 */
	static public $pluralIrregular = array();

	/**
	 * Array of irregular form plural => singular, based on self::$pluralIrregular
	 * @var array
	 */
	static public $singularIrregular = array();

	/**
	 * List of language drivers
	 * @var array
	 */
	static protected $_drivers = array();
	
	/**
	 * Get driver instance
	 * @param string $lng language
	 * @return mixed
	 */
	static public function instance($lng = 'en')
	{
		if (!isset(self::$_drivers[$lng]))
		{
			$className = self::getClassName($lng);
			self::$_drivers[$lng] = new $className();
		}

		return self::$_drivers[$lng];
	}
	
	/**
	 * Get class name
	 * @param string $lng language
	 * @return string, e.g. Core_Inflection_En
	 */
	static public function getClassName($lng)
	{
		return __CLASS__ . '_' . ucfirst($lng);
	}

	/**
	 * Check if driver available
	 * @param string $lng language
	 * @return boolean
	 */
	static public function available($lng = 'en')
	{
		return class_exists(self::getClassName($lng));
	}

	/**
	 * Chech if $word is PLURAL and IRRIGUAL
	 * @return boolean
	 */
	public function isPluralIrrigular($word)
	{
		// self::$singularIrregular consists plural => singular
		return isset(self::$singularIrregular[$word]);
	}

	/**
	 * Chech if $word is SINGULAR and IRRIGUAL
	 * @return boolean
	 */
	public function isSingularIrrigular($word)
	{
		// self::$pluralIrregular consists singular => plural
		return isset(self::$pluralIrregular[$word]);
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

		$last = self::instance($lng)->plural(array_pop($aWord), $count);

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

		$last = self::instance($lng)->singular(array_pop($aWord), $count);

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
	public function plural($singularWord, $count = NULL)
	{
		if (is_null($count) && isset($this->_pluralCache[$singularWord]))
		{
			return $this->_pluralCache[$singularWord];
		}

		if (rand(0, self::$_maxObjects) == 0 && count($this->_pluralCache) > self::$_maxObjects)
		{
			$this->_pluralCache = array_slice($this->_pluralCache, floor(self::$_maxObjects / 4));
		}

		$plural = $this->_getPlural($singularWord, $count);
		is_null($count) && $this->_pluralCache[$singularWord] = $plural;

		return $plural;
	}

	/**
	 * Cache
	 * @var array
	 */
	protected $_singularCache = array();

	/**
	 * Get singular form by plural
	 * @param string $pluralWord word
	 * @param int count
	 * @return string
	 */
	public function singular($pluralWord, $count = NULL)
	{
		if (is_null($count) && isset($this->_singularCache[$pluralWord]))
		{
			return $this->_singularCache[$pluralWord];
		}

		if (rand(0, self::$_maxObjects) == 0 && count($this->_singularCache) > self::$_maxObjects)
		{
			$this->_singularCache = array_slice($this->_singularCache, floor(self::$_maxObjects / 4));
		}

		$singular = $this->_getSingular($pluralWord, $count);
		is_null($count) && $this->_singularCache[$pluralWord] = $singular;

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
		return self::instance($lng)->numberInWords($float);
	}
	
	abstract public function currencyInWords($float, $currencyCode);
}