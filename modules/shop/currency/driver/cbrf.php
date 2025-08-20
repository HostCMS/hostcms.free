<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * ЦБ РФ драйвер обновления валют
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Currency_Driver_Cbrf extends Shop_Currency_Driver
{
	/**
	 * Executes the business logic.
	 * @return self
	 */
	public function execute()
	{
		$url = 'https://www.cbr.ru/scripts/XML_daily.asp';

		$Core_Http = Core_Http::instance()
			->url($url)
			// ->port(80)
			->timeout(10)
			->additionalHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0')
			->execute();

		$xml = $Core_Http->getDecompressedBody();

		$oXml = @simplexml_load_string($xml);

		if (is_object($oXml))
		{
			$aExchangeRate = array();

			$sDate = Core_Date::date2sql($oXml->attributes()->Date);

			$oDefaultCurrency = Core_Entity::factory('Shop_Currency')->getBydefault(1);

			if (is_null($oDefaultCurrency))
			{
				throw new Core_Exception('Default currency does not exist!');
			}

			foreach ($oXml->Valute as $Valute)
			{
				$aExchangeRate[strval($Valute->CharCode)] = str_replace(',', '.', $Valute->Value) / str_replace(',', '.', $Valute->Nominal);
			}

			if ($oDefaultCurrency->code != 'RUR'
				&& $oDefaultCurrency->code != 'RUB'
				&& !isset($aExchangeRate[$oDefaultCurrency->code])
			)
			{
				throw new Core_Exception('Default currency does not exist in inner XML');
			}

			if ($oDefaultCurrency->code == 'RUB' || $oDefaultCurrency->code == 'RUR')
			{
				$exchangeRate = 1;
			}
			else
			{
				if (isset($aExchangeRate[$oDefaultCurrency->code]))
				{
					$exchangeRate = 1 / $aExchangeRate[$oDefaultCurrency->code];

					// RUB-to-RUB
					$aExchangeRate['RUB'] = $aExchangeRate['RUR'] = 1;
				}
				else
				{
					throw new Core_Exception('Default currency does not exist in inner XML');
				}
			}

			// валюта по умолчанию всегда равна 1
			$oDefaultCurrency
				->exchange_rate(1)
				->date($sDate)
				->save();

			foreach ($aExchangeRate as $code => $rate)
			{
				$oCurrentCurrency = Core_Entity::factory('Shop_Currency')->getByCode($code);

				if (is_null($oCurrentCurrency))
				{
					continue;
				}

				$oCurrentCurrency->exchange_rate = number_format($rate * $exchangeRate, 6, '.', '');
				$oCurrentCurrency->date($sDate);
				$oCurrentCurrency->save();
			}
		}
		else
		{
			throw new Core_Exception('Wrong answer');
		}

		return $this;
	}
}