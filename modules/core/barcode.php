<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Barcodes
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Core_Barcode
{
	/**
	 * Generate EAN-8
	 * @param string $prefix e.g. '200'
	 * @param string $id Entity Id
	 * @return string
	 */
	static public function generateEAN8($prefix, $id)
	{
		return self::_generateEAN($prefix, $id, 8);
	}

	/**
	 * Generate EAN-13
	 * @param string $prefix e.g. '200'
	 * @param string $id Entity Id
	 * @return string
	 */
	static public function generateEAN13($prefix, $id)
	{
		return self::_generateEAN($prefix, $id, 13);
	}

	/**
	 * Generate EAN
	 * @param string $prefix e.g. '200'
	 * @param string $id Entity Id
	 * @param int $len EAN len, e.g. 13
	 * @return string
	 */
	static protected function _generateEAN($prefix, $id, $len)
	{
		$barcode = $prefix;

		$padLen = $len - strlen($prefix) - 1;

		if ($padLen > 0)
		{
			// Cut first xxx digits
			strlen($id) > $padLen
				&& $id = substr($id, strlen($id) - $padLen);

			$barcode .= str_pad($id, $padLen, '0', STR_PAD_LEFT);
			$barcode .= self::_calculateEANCrc($barcode);
		}

		return $barcode;
	}

	/*
	 * Check EAN-8 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	static public function isEAN8($value)
	{
		return is_scalar($value) && strlen($value) == 8 && self::_validEAN($value);
	}

	/*
	 * Check EAN-13 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	static public function isEAN13($value)
	{
		return is_scalar($value) && strlen($value) == 13 && self::_validEAN($value);
	}

	/*
	 * Check ITF-14 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	static public function isITF14($value)
	{
		return is_scalar($value) && strlen($value) == 14 && self::_validEAN($value);
	}

	/*
	 * Check ITF-14 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	static public function isCODE39($value)
	{
		return is_scalar($value) && strlen($value)
			&& strlen($value) <= 43;
	}

	/*
	 * Check EAN-128/GS1-128 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	static public function isEAN128($value)
	{
		// Find '(' in barcode
		return is_scalar($value)
			&& strpos($value, '(') !== FALSE;
	}

	/*
	 * Check valid EAN control sum
	 * @param string $value barcode
	 * @return bool
	 */
	static protected function _validEAN($value)
	{
		return self::_calculateEANCrc(substr($value, 0, -1)) == substr($value, -1);
	}

	/**
	 * Calculate EAN CRC
	 * @param string $value EAN without CRC, e.g. 7dig for EAN-8, 12dig for EAN-13
	 * @return string CRC dig
	 */
	static protected function _calculateEANCrc($value)
	{
		$calculation = 0;

		for ($i = 0; $i < strlen($value); $i++)
		{
			if (!is_numeric($value[$i]))
			{
				return FALSE;
			}

			$calculation += $i % 2
				? $value[$i] * 3
				: $value[$i];
		}

		return substr(10 - substr($calculation, -1), -1);
	}

	/*
	 * Check valid CODE39 control symbol (mod 43 check digit)
	 * @param string $value barcode
	 * @return bool
	 */
	static protected function _getCODE39ControlSymbol($value)
	{
		$calculation = 0;

		$aDigitsReference = array(
			0 => 0,
			1 => 1,
			2 => 2,
			3 => 3,
			4 => 4,
			5 => 5,
			6 => 6,
			7 => 7,
			8 => 8,
			9 => 9,
			'A' => 10,
			'B' => 11,
			'C' => 12,
			'D' => 13,
			'E' => 14,
			'F' => 15,
			'G' => 16,
			'H' => 17,
			'I' => 18,
			'J' => 19,
			'K' => 20,
			'L' => 21,
			'M' => 22,
			'N' => 23,
			'O' => 24,
			'P' => 25,
			'Q' => 26,
			'R' => 27,
			'S' => 28,
			'T' => 29,
			'U' => 30,
			'V' => 31,
			'W' => 32,
			'X' => 33,
			'Y' => 34,
			'Z' => 35,
			'-' => 36,
			'.' => 37,
			' ' => 38,
			'$' => 39,
			'/' => 40,
			'+' => 41,
			'%' => 42
		);

		for ($i = 0; $i < (strlen($value)); $i++)
		{
			if (isset($aDigitsReference[$value[$i]]))
			{
				$calculation += $aDigitsReference[$value[$i]];
			}
		}

		return array_search($calculation % 43, $aDigitsReference);
	}
}