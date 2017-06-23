<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Internationalization
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_I18n
{
	/**
	 * Current language
	 * @var string
	 */
	protected $_lng = NULL;

	/**
	 * Default language
	 * @var string
	 */
	protected $_defaultLng = 'ru';

	/**
	 * The singleton instance.
	 * @var mixed
	 */
	static protected $_instance;

	/**
	 * Cache
	 * @var array
	 */
	protected $_cache = array();

	/**
	 * Set target language
	 * @param string $lng language short name
	 * @return self
	 */
	public function setLng($lng)
	{
		$this->_lng = basename(strtolower($lng));
		return $this;
	}

	/**
	 * Get current language
	 */
	public function getLng()
	{
		return $this->_lng;
	}

	/**
	 * Constructor.
	 */
	protected function __construct()
	{
		defined('DEFAULT_LNG') && $this->_defaultLng = DEFAULT_LNG;

		if (!empty($_SESSION['current_lng']))
		{
			$this->setLng(strval($_SESSION['current_lng']));
		}
		elseif (defined('CURRENT_LNG'))
		{
			$this->setLng(CURRENT_LNG);
		}
		else
		{
			$this->setLng($this->_defaultLng);
		}
	}

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (!isset(self::$_instance))
		{
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Expand LNG
	 * @param string $className class name
	 * @param array $value
	 * @param mixed $lng language
	 * @return self
	 */
	public function expandLng($className, array $value, $lng = NULL)
	{
		$lng = is_null($lng)
			? $this->getLng()
			: basename($lng);

		$className = basename(strtolower($className));

		if ($className == '')
		{
			throw new Core_Exception('Classname is empty.');
		}

		$this->loadLng($lng, $className);

		$this->_cache[$lng][$className] = $value + $this->_cache[$lng][$className];

		return $this;
	}

	/**
	 * Load LNG into cache
	 * @param mixed $lng language
	 * @param string $className class name
	 * @return self
	 */
	public function loadLng($lng, $className)
	{
		if (!isset($this->_cache[$lng][$className]))
		{
			$this->_cache[$lng][$className] = $this->getLngFile($className, $lng);
		}

		return $this;
	}

	/**
	 * Get text for key
	 *
	 * <code>
	 * $value = Core_I18n::instance()->get('Constant.menu');
	 * </code>
	 *
	 * <code>
	 * $value = Core_I18n::instance()->get('Constant.menu', 'en');
	 * </code>
	 *
	 * Be careful when using short alias, function Core::_() has different parameters.
	 * <code>
	 * $value = Core::_('Constant.menu');
	 * </code>
	 * @param string $key module name dot key name, e.g. 'Constant.menu'
	 * @param mixed $lng language, default NULL
	 * @param mixed $count default NULL
	 * @return string
	 */
	public function get($key, $lng = NULL, $count = NULL)
	{
		$aKey = explode('.', $key, 2);

		if (count($aKey) == 2)
		{
			list($className, $textName) = $aKey;

			$lng = is_null($lng)
				? $this->getLng()
				: basename($lng);

			$className = basename(strtolower($className));

			if ($className == '')
			{
				//throw new Core_Exception(, array('%key' => $textName, '%language' => $lng));
				return "Error! model name is empty.";
			}

			$this->loadLng($lng, $className);

			if (isset($this->_cache[$lng][$className][$textName]))
			{
				return $this->_cache[$lng][$className][$textName];
			}
			/*
			// Warning: Temporary switch off
			elseif ($lng != $this->_defaultLng)
			{
				return $this->get($key, $this->_defaultLng);
			}
			*/
			else
			{
				//throw new Core_Exception(, array('%key' => $textName, '%language' => $lng));
				return "Key '" . htmlspecialchars($textName) . "' in '" . htmlspecialchars($lng) . "' language does not exist for model '" . htmlspecialchars($className) . "'.";
			}
		}
		else
		{
			//throw new Core_Exception("Wrong argument '%key'", array('%key' => $key));
			return "Wrong argument '" . htmlspecialchars($key) . "'.";
		}

		return NULL;
	}

	/**
	 * Include lng file
	 * @param string $className class name
	 * @param string $lng language name
	 * @return array
	 */
	public function getLngFile($className, $lng)
	{
		$className = basename(
			strtolower($className)
		);

		$lng = basename($lng);

		$aPath = explode('_', $className);

		$path = Core::$modulesPath;
		$path .= implode(DIRECTORY_SEPARATOR, $aPath) . DIRECTORY_SEPARATOR;
		$path .= 'i18n' . DIRECTORY_SEPARATOR . $lng . '.php';

		$path = Core_File::pathCorrection($path);

		if (is_file($path))
		{
			return require($path);
		}

		throw new Core_Exception("Language file '%className' with path '%path' does not exist.",
			array('%className' => $className, '%path' => Core_Exception::cutRootPath($path)));
	}
}
