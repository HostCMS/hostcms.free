<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Float digits format
	 * @var string
	 */
	protected $_floatFormat = "%.2f";

	/**
	 * Set float format
	 * @param string $floatFormat format
	 * @return self
	 */
	public function floatFormat($floatFormat)
	{
		$this->_floatFormat = $floatFormat;
		return $this;
	}

	/**
	 * Number of decimal digits
	 * @var int
	 */
	protected $_decimalDigits = 2;

	/**
	 * Set number of decimal digits
	 * @param string $floatFormat format
	 * @return self
	 */
	public function decimalDigits($decimalDigits)
	{
		$this->_decimalDigits = $decimalDigits;
		return $this;
	}

	/**
	 * Use Banker's Rounding. Default TRUE
	 * @var boolean
	 */
	protected $_bankersRounding = TRUE;

	/**
	 * Use Banker's Rounding
	 * @param boolean $bankersRounding
	 * @return self
	 */
	public function bankersRounding($bankersRounding = TRUE)
	{
		$this->_bankersRounding = $bankersRounding;
		return $this;
	}

	/**
	 * Banker's Round half to even
	 * TRUE - Round half to even. 23.5 => 24, 24.5 => 24
	 * FALSE - Round half to odd. 23.5 => 23, 24.5 => 25
	 * @var boolean
	 */
	protected $_bankersRoundHalfToEven = TRUE;

	/**
	 * Banker's Round half to even
	 * TRUE - Round half to even. 23.5 => 24, 24.5 => 24
	 * FALSE - Round half to odd. 23.5 => 23, 24.5 => 25
	 * @param boolean $bankersRoundHalfToEven
	 * @return self
	 */
	public function bankersRoundHalfToEven($bankersRoundHalfToEven = TRUE)
	{
		$this->_bankersRoundHalfToEven = $bankersRoundHalfToEven;
		return $this;
	}

	/**
	 * Banker's Round
	 * depends on $this->_bankersRoundHalfToEven
	 *
	 * @param float $value The value to round
	 * @param int $precision The optional number of decimal digits to round to
	 * @return float The rounded value
	 */
	public function bRound($value, $precision = 0)
	{
		$m = pow(10, $precision);
		$n = $precision ? $value * $m : $value;
		$i = floor($n);
		$f = $n - $i;
		$e = 0.00001;

		$r = ($f > 0.5 - $e && $f < 0.5 + $e)
			? ($this->_bankersRoundHalfToEven
				? (($i % 2 == 0) ? $i : $i + 1)
				: (($i % 2 == 0) ? $i + 1 : $i)
			)
			: round($n);

		return $precision
			? $r / $m
			: $r;
	}

	/**
	 * Округление цен к формату, приведенного в $this->_floatFormat
	 *
	 * @param float $value цена
	 * @return string
	 */
	public function round($value)
	{
		return sprintf($this->_floatFormat, $this->_bankersRounding
			? $this->bRound($value, $this->_decimalDigits)
			: round($value, $this->_decimalDigits)
		);
	}

	/**
	 * Convert float
	 * @param mixed $value
	 * @return mixed
	 */
	public function convertFloat($value)
	{
		$value = preg_replace('/[^0-9.,\-]/u', '', $value);
		$value = str_replace(array(',', '-'), '.', $value);
		return $value;
	}

	/**
	 * Convert price
	 * @param mixed $price price
	 * @param int $decimalPlaces precision
	 * @return mixed
	 */
	public function convertPrice($price, $decimalPlaces = 2)
	{
		$price = $this->convertFloat($price);

		$decimalPlaces = intval($decimalPlaces);
		preg_match("/((\d+(\.)\d{1,{$decimalPlaces}})|\d+)/u", $price, $array_price);
		return isset($array_price[1]) ? floatval($array_price[1]) : 0;
	}

	/**
	 * Определение коэффициента пересчета валюты $oItem_Currency в валюту $oShop_Currency
	 *
	 * @param Shop_Currency_Model $oItem_Currency исходная валюта
	 * @param Shop_Currency_Model $oShop_Currency требуемая валюта
	 * @return float
	 */
	public function getCurrencyCoefficientInShopCurrency(Shop_Currency_Model $oItem_Currency, Shop_Currency_Model $oShop_Currency)
	{
		// Определяем коэффициент пересчета в базовую валюту
		$fItemExchangeRate = $oItem_Currency->exchange_rate;
		if ($fItemExchangeRate == 0)
		{
			throw new Core_Exception('Method getCurrencyCoefficientInShopCurrency(): Item "%id" currency exchange rate is 0.', array('%id' => $oItem_Currency->id));
		}

		// Определяем коэффициент пересчета в валюту магазина
		$fShopExchangeRate = $oShop_Currency->exchange_rate;
		if ($fShopExchangeRate == 0)
		{
			throw new Core_Exception('Method getCurrencyCoefficientInShopCurrency(): Shop currency %id exchange rate is 0.', array('%id' => $oShop_Currency->id));
		}

		// Без округления
		//return round($fItemExchangeRate / $fShopExchangeRate, $this->_decimalDigits);
		return $fItemExchangeRate / $fShopExchangeRate;
	}

	/**
	 * Конвертирование значения из одной меры размера в другую
	 * @param string $value значение для конвертации
	 * @param int $sourceMeasure исходная мера
	 * @param int $destMeasure целевая мера
	 */
	static public function convertSizeMeasure($value, $sourceMeasure, $destMeasure = 0)
	{
		$sourceMeasure = intval($sourceMeasure);
		$destMeasure = intval($destMeasure);

		if ($sourceMeasure < 0 || $sourceMeasure > 4 || $destMeasure < 0 || $destMeasure > 4)
		{
			throw new Core_Exception('Method convertSizeMeasure(): Measure %id is out of range.', array('%id' => $destMeasure));
		}

		$aTmp = array(
			0 => 1, // мм
			1 => 10, // см
			2 => 1000, // м
			3 => 25.4, // дюйм
			4 => 304.8 // фут
		);

		return $aTmp[$sourceMeasure] * $value / $aTmp[$destMeasure];
	}
}