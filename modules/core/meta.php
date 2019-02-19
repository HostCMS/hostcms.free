<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Apply Meta-tags templates
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		return $string;
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
			$object = $this->_objects[$matches[2]];

			if (isset($matches[3]))
			{
				$fieldNames = $matches[3];

				// shop.company.name => object = shop, fieldName = company.name
				$aTmpExplode = explode('.', $fieldNames);

				foreach ($aTmpExplode as $fieldName)
				{
					if (isset($object->$fieldName))
					{
						$return = $object->$fieldName;
					}
					elseif (method_exists($object, $fieldName))
					{
						$attr = array();

						if (isset($matches[4]) && $matches[4] != '')
						{
							preg_match_all('/\s*(?:(?:"([^"]*)")|(?:\'([^\']*)\')|([^"\'\s]+))/', $matches[4], $matchesAttr);

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
						}

						$return = call_user_func_array(array($object, $fieldName), $attr);
					}
					else
					{
						// skip replacing
						$return = $matches[0];
					}

					// shop.company.name => first iteration $object is shop, second iteration $object is company
					$object = $return;
				}

				return is_null($functionName)
					? strip_tags($return)
					: call_user_func($functionName, strip_tags($return));
					//: $functionName($return);
			}
			else
			{
				return $object;
			}
		}
		elseif (isset($this->_functions[$matches[2]]))
		{
			return call_user_func($this->_functions[$matches[2]]);
		}
		else
		{
			preg_match_all('/\s*([a-zA-Z]*)\s*\(([^\)]*)\)/', $matches[2], $matchesAttr);

			if (isset($matchesAttr[1][0]) && is_callable($matchesAttr[1][0]))
			{
				return call_user_func_array($matchesAttr[1][0], strlen($matchesAttr[2][0]) ? explode(',', $matchesAttr[2][0]) : NULL);
			}
			else
			{
				// skip replacing
				return $matches[0];
			}
		}
	}
}