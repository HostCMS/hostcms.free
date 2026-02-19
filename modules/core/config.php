<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Module configurations
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Config
{
	/**
	 * Loaded values
	 * @var array
	 */
	private $_values = array();

	/**
	 * The singleton instance.
	 * @var mixed
	 */
	static private $_instance = NULL;

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
	 * Correct config name
	 * @param string $name
	 * @return string
	 */
	protected function _correctName($name)
	{
		return basename(strtolower($name));
	}

	/**
	 * Config types
	 * @var array
	 */
	protected $_type = array();

	/**
	 * Set config type, e.g. array('curl')
	 * @param array $array
	 * @return self
	 */
	public function type(array $array)
	{
		$this->_type = $array;

		return $this;
	}

	/**
	 * Get config, e.g. 'Core_Myconfig' requires modules/core/config/myconfig.php
	 * @param string $name, e.g. 'Core_Myconfig'
	 * @param mixed $defaultValue
	 * @return mixed Config or NULL
	 *
	 * <code>
	 * // Get config values + default values if necessary value does not exist
	 * $aConfigValues = Core_Config::instance()->get('mymodule_config') + array('foo' => 'baz');
	 *
	 * echo $aConfigValues['foo'];
	 * </code>
	 */
	public function get($name, $defaultValue = NULL)
	{
		$name = $this->_correctName($name);

		if (!isset($this->_values[$name]))
		{
			clearstatcache();

			$path = $this->getPath($name);

			$this->_values[$name] = Core_File::isFile($path)
				? require_once($path)
				: $defaultValue;
		}

		return $this->_values[$name];
	}

	/**
	 * Defined constants
	 * @var array
	 */
	protected $_definedConstants = array();

	/**
	 * Set config value
	 * @param string $name Config name, e.g. 'Core_Myconfig' set modules/core/config/myconfig.php
	 * @param array $value Config value, e.g. array('key' => 'value')
	 * @return Core_Config
	 *
	 * <code>
	 * Core_Config::instance()->set('mymodule_config', array('foo' => 'bar'));
	 * </code>
	 */
	public function set($name, $value)
	{
		$this->_values[$name] = $value;

		$path = $this->getPath($name);

		// Create destination dir
		Core_File::mkdir(dirname($path), CHMOD, TRUE);

		// Категоризированный массив констант, используется для определения имени константы по числовому значению
		!count($this->_definedConstants)
			&& $this->_definedConstants = get_defined_constants(TRUE);

		$content = "<?php\n\nreturn " . $this->_toString($value) . ";";

		Core_File::write($path, $content);

		clearstatcache();

		Core_Cache::opcacheReset();

		return $this;
	}

	/**
	 * Replace config without changing config file
	 * @param string $name Config name, e.g. 'Core_Myconfig' set modules/core/config/myconfig.php
	 * @param array $value Config value, e.g. array('key' => 'value')
	 * @return Core_Config
	 */
	public function replace($name, $value)
	{
		$this->_values[$name] = $value;
		return $this;
	}

	/**
	 * Get config path
	 * @param string $name
	 * @return string
	 */
	public function getPath($name)
	{
		$aConfig = explode('_', $name);
		$sFileName = array_pop($aConfig);

		$path = Core::$modulesPath
			. implode(DIRECTORY_SEPARATOR, $aConfig) . DIRECTORY_SEPARATOR
			. 'config' . DIRECTORY_SEPARATOR . $sFileName . '.php';

		$path = Core_File::pathCorrection($path);

		return $path;
	}

	/**
	 * Convert value to string
	 * @param mixed $value Value
	 * @param $level Level of tabs, default 1
	 * @return string
	 */
	protected function _toString($value, $level = 1)
	{
		if (is_array($value))
		{
			$sTabs = str_repeat("\t", $level);

			if (Core_Array::isList($value))
			{
				$value = array_map(array($this, '_escape'), $value);
				return "array(" . implode(", ", array_values($value)) . ")";
			}
			else
			{
				$aTmp = array();
				foreach ($value as $tmpKey => $tmpValue)
				{
					$name = $this->_escape($tmpKey);

					if (is_numeric($tmpKey) && count($this->_type))
					{
						foreach ($this->_type as $configType)
						{
							if (isset($this->_definedConstants[$configType]))
							{
								// without escape, e.g. CURLOPT_PROXY not 'CURLOPT_PROXY'
								$name = array_search($tmpKey, $this->_definedConstants[$configType]);
								break;
							}
						}
					}

					if (in_array($tmpKey, array('dirMode', 'fileMode'), TRUE) && is_numeric($tmpValue))
					{
						$tmpValue = '0' . decoct($tmpValue);
					}
					else
					{
						$tmpValue = $this->_toString($tmpValue, $level + 1);
					}

					$aTmp[] = $sTabs . $name . " => " . $tmpValue;
				}

				return "array(\n" . implode(",\n", $aTmp) . "\n" . str_repeat("\t", $level - 1) . ")";
			}
		}
		else
		{
			return $this->_escape($value);
		}
	}

	/**
	 * Escape key or value
	 * @param mixed $value
     * @return float|int|string
     */
	protected function _escape($value)
	{
		if (is_int($value) || is_float($value))
		{
			return $value;
		}
		elseif (is_string($value))
		{
			if (strpos($value, CMS_FOLDER) === 0)
			{
				$value = substr($value, strlen(CMS_FOLDER));
				$prefix = 'CMS_FOLDER . ';
			}
			else
			{
				$prefix = '';
			}
			return "{$prefix}'" . str_replace("'", "\'", $value) . "'";
		}
		elseif (is_null($value))
		{
			return 'NULL';
		}
		elseif (is_bool($value))
		{
			return $value === TRUE ? 'TRUE' : 'FALSE';
		}

		return "''";
	}
}