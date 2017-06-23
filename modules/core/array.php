<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Array helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * @return mixed
	 */
	static public function get($array, $key, $defaultValue = NULL)
	{
		return is_array($array) && array_key_exists($key, $array) ? $array[$key] : $defaultValue;
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
	static public function getRequest($key, $defaultValue = NULL)
	{
		return self::get($_REQUEST, $key, $defaultValue);
	}

	/**
	 * Get value for $key in array $_POST. If value does not exist will return defaultValue.
	 * @param $key key
	 * @param $defaultValue default value
	 * <code>
	 * // Return value for 'foo' or NULL if $key does not exist
	 * $value = Core_Array::getPost('foo');
	 * </code>
	 * <code>
	 * // Return value for 'foo' or 'bar' if $key does not exist
	 * $value = Core_Array::getPost('foo', 'bar');
	 * </code>
	 * @return mixed
	 */
	static public function getPost($key, $defaultValue = NULL)
	{
		return self::get($_POST, $key, $defaultValue);
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
	 * @return mixed
	 */
	static public function getGet($key, $defaultValue = NULL)
	{
		return self::get($_GET, $key, $defaultValue);
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
	 * @return mixed
	 */
	static public function getSession($key, $defaultValue = NULL)
	{
		return isset($_SESSION)
			? self::get($_SESSION, $key, $defaultValue)
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
	 * Get the value of the last element
	 *
	 * @param array $array массив
	 * <code>
	 * <?php
	 * $array = array('field1', 'field2', 'field3');
	 * $lastItem = Core_Array::end($array);
	 *
	 * print_r($lastItem);
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
	 * Combine arrays
	 * @param array $aArray
	 * @param int $iIndex index
	 * <code>
	 * <?php
	 * $arrays = array(
	 * 		array('aaa', 'bbb', 'ccc'),
	 * 		array(111, 222, 333)
	 * 	);
	 * $aReturn = Core_Array::combine($array);
	 *
	 * print_r($aReturn);
	 * ?>
	 * </code>
	 * @return array
	 */
	static public function combine($aArray, $iIndex = 0)
	{
		static $aKeys;
		static $iCount;
		static $aTmp = array();
		static $aReturn = array();

		if (!$iIndex)
		{
			$aKeys = array_keys($aArray);
			$iCount = count($aArray);
		}

		if ($iIndex < $iCount)
		{
			foreach ($aArray[$aKeys[$iIndex]] as $sValue)
			{
				array_push($aTmp, $sValue);
				self::combine($aArray, $iIndex + 1);
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
}