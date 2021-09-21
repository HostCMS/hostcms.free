<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Apply Meta-tags templates
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * @param string $str
	 * @return string
	 */
	public function apply($str)
	{
		//$pattern = '/\{([:A-Za-z0-9_-]*\s)?([^\}\.]+)(?:\.([^\}\s]+))?(?:\s+([^\}]+))*\}/';
		$pattern = '/\{([:A-Za-z0-9_-]*\s)?([^\}\.]+)(?:\.([^\}\s]+))*(?:\s+([^\}]+))*\}/';

		$string = preg_replace_callback($pattern, array($this, '_callback'), $str);

		while (strstr($string, '  '))
		{
			$string = str_replace('  ', ' ', $string);
		}

		return trim($string);
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

		// object
		if (isset($this->_objects[$matches[2]]))
		{
			//if (isset($this->_objects['Items'])) print_r($this->_objects['Items']);
			$object = $this->_objects[$matches[2]];

			if (isset($matches[3]))
			{
				$fieldNames = $matches[3];

				// shop.company.name => object = shop, fieldName = company.name
				$aTmpExplode = explode('.', $fieldNames);

				foreach ($aTmpExplode as $fieldName)
				{
					if (is_callable(array($object, $fieldName)))
					{
						$attr = isset($matches[4]) && $matches[4] != ''
							? $this->_parseArgs($matches[4])
							: array();

						$return = call_user_func_array(array($object, $fieldName), $attr);
					}
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

				$return = strip_tags($return);

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
			$attr = isset($matches[4]) && $matches[4] != ''
				? $this->_parseArgs($matches[4])
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