<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Array helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Array
{
	/**
	 * Get value for $key in array $array. If value does not exist will return defaultValue.
	 *
 	 * <code>
	 * $array = array('fruit' => 'apple', 'baz' => 'quz');
	 * // Return 'apple'
	 * $value = Core_Array::get($array, 'fruit');
	 *
	 * // Return NULL
	 * $value = Core_Array::get($array, 'foo');
	 *
	 * // Return 'bar'
	 * $value = Core_Array::get($array, 'foo', 'bar');
	 * </code>
	 * @param array $array array
	 * @param string $key key
	 * @param mixed $defaultValue default value
	 * @param mixed $filter filter, e.g. 'str'|'string'|'strval', 'int'|'integer'|'intval', 'float'|'floatval', 'bool'|'boolean'|'boolval', 'trim', 'array'
	 * @return mixed
	 */
	static public function get($array, $key, $defaultValue = NULL, $filter = NULL)
	{
		return self::_filter(
			is_array($array) && array_key_exists($key, $array)
				? $array[$key]
				: $defaultValue,
			$filter
		);
	}

	/**
	 * Filter Value
	 * @param mixed $value
	 * @param mixed $filter filter, e.g. 'str'|'string'|'strval', 'int'|'integer'|'intval', 'float'|'floatval', 'bool'|'boolean'|'boolval', 'trim', 'array'
	 * @return mixed
	 */
	static protected function _filter($value, $filter)
	{
		if (!is_null($filter))
		{
			switch ($filter)
			{
				case 'str':
				case 'string':
				case 'strval':
					$value = is_scalar($value)
						? strval($value)
						: '';
				break;
				case 'trim':
					$value = is_scalar($value)
						? trim($value)
						: '';
				break;
				case 'int':
				case 'integer':
				case 'intval':
					$value = is_scalar($value)
						? intval($value)
						: 0;
				break;
				case 'float':
				case 'floatval':
					$value = is_scalar($value)
						? floatval($value)
						: 0.0;
				break;
				case 'bool':
				case 'boolean':
				case 'boolval':
					$value = is_scalar($value)
						? (function_exists('boolval')
							? boolval($value)
							: (bool)$value
						)
						: FALSE;
				break;
				case 'array':
					$value = is_array($value)
						? $value
						: array();
				break;
				default:
					throw new Core_Exception('Core_Array wrong \'%name\' filter name', array('%name' => $filter));

			}
		}

		return $value;
	}

	/**
	 * Set value $defaultValue for $key in array $array.
	 * @param array $array array
	 * @param mixed $key key
	 * @param mixed $defaultValue value
	 *
	 * <code>
	 * $array = array('fruit' => 'apple');
	 * Core_Array::set($array, 'baz', 'quz');
	 * </code>
	 */
	static public function set(& $array, $key, $defaultValue = NULL)
	{
		$array[$key] = $defaultValue;
	}

	/**
	 * Get value for $key in array $_REQUEST. If value does not exist will return defaultValue.
	 * @param $key key
	 * @param $defaultValue default value
	 * @param mixed $filter filter, e.g. 'str'|'string'|'strval', 'int'|'integer'|'intval', 'float'|'floatval', 'bool'|'boolean'|'boolval', 'trim', 'array'
	 * <code>
	 * // Return value for 'foo' or NULL if $key does not exist
	 * $value = Core_Array::getRequest('foo');
	 * </code>
	 * <code>
	 * // Return value for 'foo' or 'bar' if $key does not exist
	 * $value = Core_Array::getRequest('foo', 'bar');
	 * </code>
	 * @return mixed
	 */
	static public function getRequest($key, $defaultValue = NULL, $filter = NULL)
	{
		return self::get($_REQUEST, $key, $defaultValue, $filter);
	}

	/**
	 * Get value for $key in array $_POST. If value does not exist will return defaultValue.
	 * @param $key key
	 * @param $defaultValue default value
	 * @param mixed $filter filter, e.g. 'str'|'string'|'strval', 'int'|'integer'|'intval', 'float'|'floatval', 'bool'|'boolean'|'boolval', 'trim', 'array'
	 * <code>
	 * // Return value for 'foo' or NULL if $key does not exist
	 * $value = Core_Array::getPost('foo');
	 * </code>
	 * <code>
	 * // Return value for 'foo' or 'bar' if $key does not exist
	 * $value = Core_Array::getPost('foo', 'bar');
	 * </code>
	 * <code>
	 * // Return value for 'foo' or 'bar' if $key does not exist
	 * $value = Core_Array::getPost('foo', 'bar', 'trim');
	 * </code>
	 * @return mixed
	 */
	static public function getPost($key, $defaultValue = NULL, $filter = NULL)
	{
		return self::get($_POST, $key, $defaultValue, $filter);
	}

	/**
	 * Get value for $key in array $_GET. If value does not exist will return defaultValue.
	 *
	 * <code>
	 * // Return value for 'foo' or NULL if $key does not exist
	 * $value = Core_Array::getGet('foo');
	 * </code>
	 * <code>
	 * // Return value for 'foo' or 'bar' if $key does not exist
	 * $value = Core_Array::getGet('foo', 'bar');
	 * </code>
	 *
	 * @param string $key key
	 * @param mixed $defaultValue default value
	 * @param mixed $filter filter, e.g. 'str'|'string'|'strval', 'int'|'integer'|'intval', 'float'|'floatval', 'bool'|'boolean'|'boolval', 'trim', 'array'
	 * @return mixed
	 */
	static public function getGet($key, $defaultValue = NULL, $filter = NULL)
	{
		return self::get($_GET, $key, $defaultValue, $filter);
	}
	
	/**
	 * Get value for $key in array $_COOKIE. If value does not exist will return defaultValue.
	 *
	 * <code>
	 * // Return value for 'foo' or NULL if $key does not exist
	 * $value = Core_Array::getCookie('foo');
	 * </code>
	 * <code>
	 * // Return value for 'foo' or 'bar' if $key does not exist
	 * $value = Core_Array::getCookie('foo', 'bar');
	 * </code>
	 *
	 * @param string $key key
	 * @param mixed $defaultValue default value
	 * @param mixed $filter filter, e.g. 'str'|'string'|'strval', 'int'|'integer'|'intval', 'float'|'floatval', 'bool'|'boolean'|'boolval', 'trim', 'array'
	 * @return mixed
	 */
	static public function getCookie($key, $defaultValue = NULL, $filter = NULL)
	{
		return self::get($_COOKIE, $key, $defaultValue, $filter);
	}

	/**
	 * Get value for $key in array $_SESSION. If value does not exist will return defaultValue.
	 *
	 * <code>
	 * // Return value for 'foo' or NULL if $key does not exist
	 * $value = Core_Array::getSession('foo');
	 * </code>
	 * <code>
	 * // Return value for 'foo' or 'bar' if $key does not exist
	 * $value = Core_Array::getSession('foo', 'bar');
	 * </code>
	 *
	 * @param string $key key
	 * @param mixed $defaultValue default value
	 * @param mixed $filter filter, e.g. 'str'|'string'|'strval', 'int'|'integer'|'intval', 'float'|'floatval', 'bool'|'boolean'|'boolval', 'trim', 'array'
	 * @return mixed
	 */
	static public function getSession($key, $defaultValue = NULL, $filter = NULL)
	{
		return isset($_SESSION)
			? self::get($_SESSION, $key, $defaultValue, $filter)
			: $defaultValue;
	}

	/**
	 * Get value for $key in array $_FILES. If value does not exist will return defaultValue.
	 * @param $key key
	 * @param $defaultValue default value
	 * <code>
	 * // Return value for 'foo' or NULL if $key does not exist
	 * $value = Core_Array::getFiles('foo');
	 * </code>
	 * <code>
	 * // Return value for 'foo' or array() if $key does not exist
	 * $value = Core_Array::getFiles('foo', array());
	 * </code>
	 * @return mixed
	 */
	static public function getFiles($key, $defaultValue = NULL)
	{
		return self::get($_FILES, $key, $defaultValue);
	}

	/**
	 * Convert all values of array to int
	 * @param $array array
	 * <code>
	 * $array = (1, 2, '3', 'a' => '321')
	 * $array = Core_Array::toInt($array);
	 * var_dump($array);
	 * </code>
	 * @return mixed
	 */
	static public function toInt($array)
	{
		$array = Core_Type_Conversion::toArray($array);

		if (count($array) > 0)
		{
			foreach ($array as $key => $value)
			{
				$array[$key] = intval($value);
			}
		}

		return $array;
	}

	/**
	 * Union arrays $array1 and $array2. If $array1 is not array will return $array2
	 * @param mixed $array1
	 * @param array $array2
	 * @return array
	 */
	static public function union($array1, array $array2)
	{
		return is_array($array1) ? $array1 + $array2 : $array2;
	}

	/**
	 * Перемешивание элементов массива. Если передан hash - перемешивание будет осуществлено в соответствии с этим значением.
	 *
	 * @param array $array массив
	 * @param int $hash числовое значение
	 * <code>
	 * <?php
	 * $array = array('field1', 'field2', 'field3');
	 * $hash = 10;
	 *
	 * $aNew = Core_Array::randomShuffle($array, $hash);
	 *
	 * print_r($aNew);
	 * ?>
	 * </code>
	 * @return array перемешанный массив
	 */
	static public function randomShuffle($array, $hash = NULL)
	{
		$array = Core_Type_Conversion::toArray($array);

		$hash = is_null($hash)
			? rand()
			: intval($hash);

		$n = count($array);

		for ($i = 0; $i < $n; $i++)
		{
			$position = $i + $hash % ($n - $i);
			$temp = $array[$i];
			$array[$i] = $array[$position];
			$array[$position] = $temp;
		}

		return $array;
	}
	
	/**
	 * Возвращает случайное значение из массива
	 *
	 * @param array $array массив
	 * <code>
	 * <?php
	 * $array = array('field1', 'field2', 'field3');
	 *
	 * $value = Core_Array::randomValue($array);
	 *
	 * var_dump($value);
	 * ?>
	 * </code>
	 * @return mixed
	 */
	static public function randomValue(array $array)
	{
		$key = array_rand($array);
		return $array[$key];
	}

	/**
	 * Get the value of the last element
	 *
	 * @param array $array массив
	 * <code>
	 * <?php
	 * $array = array('field1', 'field2', 'field3');
	 * $lastItem = Core_Array::end($array);
	 *
	 * var_dump($lastItem);
	 * ?>
	 * </code>
	 * @return mixed
	 */
	static public function end(array $array)
	{
		// array needs to be a reference
		return end($array);
	}

	/**
	 * Get the value of the first element
	 *
	 * @param array $array массив
	 * <code>
	 * <?php
	 * $array = array('field1', 'field2', 'field3');
	 * $firstItem = Core_Array::first($array);
	 *
	 * var_dump($firstItem);
	 * ?>
	 * </code>
	 * @return mixed
	 */
	static public function first(array $array)
	{
		// array needs to be a reference
		return reset($array);
	}

	/**
	 * Combine Arrays
	 * @param array $aArray
	 * @param int $_iIndex index
	 * <code>
	 * <?php
	 * $arrays = array(
	 * 		0 => array('aaa', 'bbb', 'ccc'),
	 * 		22 => array(111, 222, 333)
	 * 	);
	 * $aReturn = Core_Array::combine($arrays);
	 *
	 * print_r($aReturn);
	 * ?>
	 * </code>
	 * @return array
	 */
	static public function combine($aArray, $_iIndex = 0)
	{
		static $aKeys;
		static $iCount;
		static $aTmp = array();
		static $aReturn = array();

		if (!$_iIndex)
		{
			$aKeys = array_keys($aArray);
			$iCount = count($aArray);
			// Явно очищаем массивы, назвисимо от static
			$aReturn = $aTmp = array();
		}

		if ($_iIndex < $iCount)
		{
			foreach ($aArray[$aKeys[$_iIndex]] as $xxx => $sValue)
			{
				$aTmp[$aKeys[$_iIndex]] = $sValue;
				self::combine($aArray, $_iIndex + 1);
				array_pop($aTmp);
			}
		}
		else
		{
			$aReturn[] = $aTmp;
		}
		return $aReturn;
	}

	/**
	 * Join multi-level array elements with a string
	 * @param string $glue
	 * @param array $array The array of strings to implode.
	 * @return string
	 */
	static public function implode($glue, array $array)
	{
		$aReturn = array();
		foreach ($array as $value)
		{
			$aReturn[] = is_array($value)
				? self::implode($glue, $value)
				: $value;
		}

		return implode($glue, $aReturn);
	}

	/**
	 * Convert array to the literal notation for the JS-object, e.g. array('foo' => 'bar', 'baz' => true) to string 'foo': 'bar', 'baz': true
	 * @param array $array The array of strings to implode.
	 * @return string
	 */
	static public function array2jsObject(array $array)
	{
		$aReturn = array();

		foreach ($array as $key => $value)
		{
			if ($value === TRUE)
			{
				$value = 'true';
			}
			elseif ($value === FALSE)
			{
				$value = 'false';
			}
			elseif (is_null($value))
			{
				$value = 'null';
			}
			else
			{
				$value = "'" . Core_Str::escapeJavascriptVariable($value) . "'";
			}

			$aReturn[] = "'" . Core_Str::escapeJavascriptVariable($key) . "': " . $value;
		}

		return implode(', ', $aReturn);
	}
	
	/**
	 * Find value by key in multidimensional array
	 * @param array $array
	 * @param mixed $key
	 * @return NULL|mixed Null or value
	 */
	static public function findByKey(array $array, $key)
	{
		if (isset($array[$key]))
		{
			return $array[$key];
		}
		else
		{
			foreach ($array as $subArray)
			{
				if (is_array($subArray))
				{
					$result = self::findByKey($subArray, $key);

					if (!is_null($result))
					{
						return $result;
					}
				}
			}
		}

		return NULL;
	}
}