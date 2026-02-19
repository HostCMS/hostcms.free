<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Apply Meta-tags templates, e.g. {uppercaseFirst group.name} or {uppercaseFirst group.nameField}, {this.seoFilter ": " ", "}, {date(d.m.Y)}
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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

			isset($this->_functions[$functionName])
				&& $functionName = $this->_functions[$functionName];

			if (/*!function_exists($functionName) && */!is_callable($functionName))
			{
				// skip replacing
				return $matches[0];
			}
		}
		else
		{
			$functionName = NULL;
		}

		// var_dump($matches);

		$fieldReturn = $this->_executeField($matches[2], isset($matches[3]) ? $matches[3] : '');

		if (!is_null($fieldReturn))
		{
			return is_null($functionName)
				? $fieldReturn
				: call_user_func($functionName, $fieldReturn);
				//: $functionName($return);
		}
		elseif (!is_null($functionName)) // "{ifexist 'aaaaaa'}"
		{
			$attr = $matches[2] != ''
				? $this->_parseArgs(implode(array_slice($matches, 2)))
				: array();

			return call_user_func_array($functionName, $attr);
		}
		elseif (isset($this->_functions[$matches[2]])) // "{mb_strtolower ifexist 'aaaaaa'}"
		{
			$attr = isset($matches[3]) && $matches[3] != ''
				? $this->_parseArgs($matches[3])
				: array();

			return call_user_func_array($this->_functions[$matches[2]], $attr);
		}
		else // generateChars(7)
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
	 * Execute field
	 * @param string $fieldName
	 * @param string $attr
	 * @return mixed
	 */
	protected function _executeField($fieldName, $attr = '')
	{
		// shop.company.name => objectName = shop, fieldName = company + name
		$aTmpExplode = explode('.', $fieldName);
		$objectName = array_shift($aTmpExplode);

		// object
		if (isset($this->_objects[$objectName]))
		{
			//if (isset($this->_objects['Items'])) print_r($this->_objects['Items']);
			$object = $this->_objects[$objectName];

			if (count($aTmpExplode))
			{
				foreach ($aTmpExplode as $subFieldName)
				{
					// xyzField
					if (substr($subFieldName, -5) === 'Field' && ($sTmp = substr($subFieldName, 0, -5)) && isset($object->$sTmp))
					{
						$return = $object->$sTmp;
					}
					// ->xyz()
					//is_callable(array($object, $subFieldName)) вернёт true для произвольного метода объекта, который реализует метод __call(), даже если метод не определили в классе
					elseif (is_object($object) && (method_exists($object, $subFieldName) || method_exists($object, '__isset') && $object->__isset($subFieldName)))
					{
						$aAttr = $attr != ''
							? $this->_parseArgs($attr)
							: array();

						$return = call_user_func_array(array($object, $subFieldName), $aAttr);
					}
					// ->xyz
					elseif (isset($object->$subFieldName))
					{
						$return = $object->$subFieldName;
					}
					// Items.0.item.name
					elseif (is_array($object) && is_numeric($subFieldName) && isset($object[$subFieldName]))
					{
						$return = $object[$subFieldName];
					}
					else
					{
						// skip replacing
						//$return = $matches[0];
						$return = NULL;
					}

					// shop.company.name => first iteration $object is shop, second iteration $object is company
					$object = $return;
				}

				return strip_tags((string) $return);
			}
			else
			{
				return $object;
			}
		}

		return NULL;
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

				if (strpos($value, '.') !== FALSE)
				{
					$tmpValue = $this->_executeField($value);
					!is_null($tmpValue) && $value = $tmpValue;
				}

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