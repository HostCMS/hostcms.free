<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Daily Foreign Exchange Rates for U.S. Dollar (USD)
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Currency_Driver_Floatrates extends Shop_Currency_Driver
{
	/**
	 * Executes the business logic.
	 * @return self
	 */
	public function execute()
	{
		$url = 'http://www.floatrates.com/daily/usd.xml';

		$Core_Http = Core_Http::instance()
			->url($url)
			->port(80)
			->timeout(10)
			->additionalHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0')
			->execute();

		$xml = $Core_Http->getDecompressedBody();

		$oXml = @simplexml_load_string($xml);

		if (is_object($oXml))
		{
			$aExchangeRate = array();

			$sDate = Core_Date::date2sql($oXml->pubDate);

			$oDefaultCurrency = Core_Entity::factory('Shop_Currency')->getBydefault(1);

			if (is_null($oDefaultCurrency))
			{
				throw new Exception('Default currency does not exist!');
			}

			// получаем данные о котировках их XML
			foreach ($oXml->item as $oItem)
			{
				$aExchangeRate[strval($oItem->targetCurrency)] = str_replace(',', '.', $oItem->inverseRate);
			}

			if ($oDefaultCurrency->code == 'USD')
			{
				$exchangeRate = 1;
			}
			else
			{
				if (isset($aExchangeRate[$oDefaultCurrency->code]))
				{
					$exchangeRate = 1 / $aExchangeRate[$oDefaultCurrency->code];

					// USD-to-USD
					$aExchangeRate['USD'] = 1;
				}
				else
				{
					throw new Exception('Default currency does not exist in inner XML');
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

		return $this;
	}
}