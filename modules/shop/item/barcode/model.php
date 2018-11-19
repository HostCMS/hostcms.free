<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Barcode_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Barcode_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_item' => array(),
	);

	/**
	 * Set barcode type
	 * @return int
	 */
	public function setType()
	{
		$bNumeric = is_numeric($this->value);

		$this->type = 0;

		if ($bNumeric)
		{
			$lenght = strlen($this->value);

			// EAN-8
			if ($lenght == 8)
			{
				if ($this->isEAN8($this->value))
				{
					$this->type = 1;
				}
			}
			// EAN-13
			elseif ($lenght == 13)
			{
				if ($this->isEAN13($this->value))
				{
					$this->type = 2;
				}
			}
			// ITF-14
			elseif ($lenght == 14)
			{
				if ($this->isITF14($this->value))
				{
					$this->type = 3;
				}
			}
		}
		else
		{
			// EAN-128/GS1-128
			if ($this->isEAN128($this->value))
			{
				$this->type = 4;
			}
			// CODE39
			elseif ($this->isCODE39($this->value))
			{
				$this->type = 5;
			}
		}

		return $this;
	}

	/*
	 * Check EAN-8 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isEAN8($value)
	{
		return strlen($value) == 8 && $this->_validEAN($value);
	}

	/*
	 * Check EAN-13 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isEAN13($value)
	{
		return strlen($value) == 13 && $this->_validEAN($value);
	}

	/*
	 * Check ITF-14 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isITF14($value)
	{
		return strlen($value) == 14 && $this->_validEAN($value);
	}

	/*
	 * Check ITF-14 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isCODE39($value)
	{
		$return = FALSE;

		strlen($value)
			&& strlen($value) <= 43
			&& $return = TRUE;

		return $return;
	}

	/*
	 * Check EAN-128/GS1-128 barcode
	 * @param string $value barcode
	 * @return bool
	 */
	public function isEAN128($value)
	{
		$return = FALSE;

		// Find '(' in barcode
		if (strpos($value, '(') !== FALSE)
		{
			$return = TRUE;
		}

		return $return;
	}

	/*
	 * Check valid EAN control sum
	 * @param string $value barcode
	 * @return bool
	 */
	protected function _validEAN($value)
	{
		$calculation = 0;

		for ($i = 0; $i < (strlen($value) - 1); $i++)
		{
			$calculation += $i % 2 ? $value{$i} * 1 : $value{$i} * 3;
		}

		return substr(10 - substr($calculation, -1), -1) == substr($value, -1);
	}

	/*
	 * Check valid CODE39 control symbol (mod 43 check digit)
	 * @param string $value barcode
	 * @return bool
	 */
	protected function _getCODE39ControlSymbol($value)
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
			'С' => 12,
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
			if (isset($aDigitsReference[$value{$i}]))
			{
				$calculation += $aDigitsReference[$value{$i}];
			}
		}

		return array_search($calculation % 43, $aDigitsReference);
	}
















}