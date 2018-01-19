<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Apply Meta-tags templates
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Meta
{
	protected $_objects = array();

	protected $_replaceFunctions = array(
		'toUpper' => 'mb_strtoupper',
		'toLower' => 'mb_strtolower',
	);

	/**
	 *
	 */
	public function addObject($name, $object)
	{
		$this->_objects[$name] = $object;

		return $this;
	}

	public function apply($str)
	{
		//
		// (\s(?:\"[^\"]*))

		// {group.name}
		// {toLower group.name}
		//$pattern = '/\{([A-Za-z0-9_-]*\s)?([^\}\.]+)\.([^\}\s]+)(\s"?[^"\}]+"?)*\}/u';
		//$pattern = '/\{([A-Za-z0-9_-]*\s)?([^\}\.]+)\.([^\}\s]+)(?:\s+([a-zA-Z0-9_\-]+|"[^"\}]+"))*\}/';
		$pattern = '/\{([A-Za-z0-9_-]*\s)?([^\}\.]+)\.([^\}\s]+)(?:\s+([^\}]+))*\}/';

		$string = preg_replace_callback($pattern, array($this, '_callback'), $str);

		return $string;
	}

	protected function _callback($matches)
	{
		// function
		if ($matches[1] != '')
		{
			$functionName = rtrim($matches[1]);

			isset($this->_replaceFunctions[$functionName])
				&& $functionName = $this->_replaceFunctions[$functionName];

			if (!function_exists($functionName))
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
			$fieldName = $matches[3];

			if (isset($object->$fieldName))
			{
				return is_null($functionName)
					? $object->$fieldName
					: $functionName($object->$fieldName);
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

				//print_r($attr);

				return is_null($functionName)
					? call_user_func_array(array($object, $fieldName), $attr) //$object->$fieldName()
					: $functionName(call_user_func_array(array($object, $fieldName), $attr));
			}
			else
			{
				// skip replacing
				return $matches[0];
			}
		}
		else
		{
			// skip replacing
			return $matches[0];
		}
	}
}