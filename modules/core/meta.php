<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Apply Meta-tags templates, e.g. {uppercaseFirst group.name} or {uppercaseFirst group.nameField}, {this.seoFilter ": " ", "}, {date(d.m.Y)}
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_Meta
{
	/**
	 * Array of objects [name] => $object
	 * @var array
	 */
	protected $_objects = array();

	/**
	 * Array of functions [name] => callable
	 * @var array
	 */
	protected $_functions = array();

	/**
	 * Predefined functions
	 * @var array
	 */
	protected $_replaceFunctions = array(
		'toUpper' => 'mb_strtoupper',
		'toLower' => 'mb_strtolower',
		'uppercaseFirst' => 'Core_Str::ucfirst',
		'lowercaseFirst' => 'Core_Str::lcfirst',
	);

	/**
	 * Constructor.
	 * @hostcms-event Core_Meta.onAfterConstruct
	 */
	public function __construct()
	{
		Core_Event::notify('Core_Meta.onAfterConstruct', $this);
	}

	/**
	 * Add object
	 * @param string $name
	 * @param string $object
	 * @return self
	 */
	public function addObject($name, $object)
	{
		$this->_objects[$name] = $object;
		return $this;
	}

	/**
	 * Add function
	 * @param string $name
	 * @param callable $callable
	 * @return self
	 */
	public function addFunction($name, $callable)
	{
		$this->_functions[$name] = $callable;
		return $this;
	}

	/**
	 * Apply template, e.g. {group.name}, {toLower group.name}, {toLower group.groupPathWithSeparator " / " 1}
	 *
	 * @param string $string
	 * @return string
	 */
	public function apply($string)
	{
		if (!is_null($string) && strlen($string))
		{
			//$pattern = '/\{([:A-Za-z0-9_-]*\s)?([^\}\.]+)(?:\.([^\}\s]+))?(?:\s+([^\}]+))*\}/';
			//$pattern = '/\{([:A-Za-z0-9_\-]*\s)?([^\}\.]+)(?:\.([^\}\s]+))*(?:\s+([^\}]+))*\}/';
			$pattern = '/\{([:A-Za-z0-9_\-]*\s)?([^\}\s]+)(?:\s+([^\}]+))*\}/';

			$string = preg_replace_callback($pattern, array($this, '_callback'), $string);

			while (strstr($string, '  '))
			{
				$string = str_replace('  ', ' ', $string);
			}
		}

		return trim((string) $string);
	}

	/**
	 * Replace callback function
	 * @param array $matches
	 * @return string
	 */
	protected function _callback($matches)
	{
		// function
		if ($matches[1] != '')
		{
			$functionName = rtrim($matches[1]);

			isset($this->_replaceFunctions[$functionName])
				&& $functionName = $this->_replaceFunctions[$functionName];

			if (!function_exists($functionName) && !is_callable($functionName))
			{
				// skip replacing
				return $matches[0];
			}
		}
		else
		{
			$functionName = NULL;
		}

		// shop.company.name => objectName = shop, fieldName = company + name
		$aTmpExplode = explode('.', $matches[2]);
		$objectName = array_shift($aTmpExplode);

		// object
		if (isset($this->_objects[$objectName]))
		{
			//if (isset($this->_objects['Items'])) print_r($this->_objects['Items']);
			$object = $this->_objects[$objectName];

			if (count($aTmpExplode))
			{
				/*$fieldNames = $matches[3];

				// shop.company.name => object = shop, fieldName = company.name
				$aTmpExplode = explode('.', $fieldNames);*/

				foreach ($aTmpExplode as $fieldName)
				{
					// xyzField
					if (substr($fieldName, -5) === 'Field' && ($sTmp = substr($fieldName, 0, -5)) && isset($object->$sTmp))
					{
						$return = $object->$sTmp;
					}
					// ->xyz()
					//is_callable(array($object, $fieldName)) вернёт true для произвольного метода объекта, который реализует метод __call(), даже если метод не определили в классе
					elseif (method_exists($object, $fieldName) || method_exists($object, '__isset') && $object->__isset($fieldName))
					{
						$attr = isset($matches[3]) && $matches[3] != ''
							? $this->_parseArgs($matches[3])
							: array();

						$return = call_user_func_array(array($object, $fieldName), $attr);
					}
					// ->xyz
					elseif (isset($object->$fieldName))
					{
						$return = $object->$fieldName;
					}
					// Items.0.item.name
					elseif (is_array($object) && is_numeric($fieldName) && isset($object[$fieldName]))
					{
						$return = $object[$fieldName];
					}
					else
					{
						// skip replacing
						$return = $matches[0];
					}

					// shop.company.name => first iteration $object is shop, second iteration $object is company
					$object = $return;
				}

				$return = strip_tags((string) $return);

				return is_null($functionName)
					? $return
					: call_user_func($functionName, $return);
					//: $functionName($return);
			}
			else
			{
				return $object;
			}
		}
		elseif (isset($this->_functions[$matches[2]]))
		{
			$attr = isset($matches[3]) && $matches[3] != ''
				? $this->_parseArgs($matches[3])
				: array();

			return call_user_func_array($this->_functions[$matches[2]], $attr);
		}
		else
		{
			preg_match_all('/\s*([a-zA-Z]*)\s*\(([^\)]*)\)/', $matches[2], $matchesAttr);

			if (isset($matchesAttr[1][0]))
			{
				$functionName = isset($this->_functions[$matchesAttr[1][0]])
					? $this->_functions[$matchesAttr[1][0]]
					: $matchesAttr[1][0];

				return is_callable($functionName)
					? call_user_func_array($functionName, strlen($matchesAttr[2][0]) ? explode(',', $matchesAttr[2][0]) : NULL)
					: $matches[0];
			}
			else
			{
				// skip replacing
				return $matches[0];
			}
		}
	}

	/**
	 * Parse args
	 * @param string $str
	 * @return array
	 */
	protected function _parseArgs($str)
	{
		preg_match_all('/\s*(?:(?:"([^"]*)")|(?:\'([^\']*)\')|([^"\'\s]+))/', $str, $matchesAttr);

		$attr = array();
		if (isset($matchesAttr[0]))
		{
			foreach ($matchesAttr[0] as $key => $attrName)
			{
				$value = $matchesAttr[1][$key] != ''
					? $matchesAttr[1][$key]
					: (
						$matchesAttr[2][$key] != ''
							? $matchesAttr[2][$key]
							: $matchesAttr[3][$key]
					);

				($value === 'true' || $value === 'TRUE') && $value = TRUE;
				($value === 'false' || $value === 'FALSE') && $value = FALSE;
				($value === 'null' || $value === 'NULL') && $value = NULL;

				$attr[] = $value;
			}
		}

		return $attr;
	}

	/**
	 * Clear meta
	 * @return self
	 */
	public function clear()
	{
		$this->_objects = $this->_functions = array();
		return $this;
	}
}