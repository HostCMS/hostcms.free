<?php

/**
 * Обновление валют на текущий день по курсу ЦБ.
 *
 * @package HostCMS 7\cron
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023, https://www.hostcms.ru
 */

require_once(dirname(__FILE__) . '/../' . 'bootstrap.php');

setlocale(LC_NUMERIC, 'POSIX');

// При увеличении курса ЦБ на 2% установите значение 1.02
$coefficient = 1;

$url = 'http://www.cbr.ru/scripts/XML_daily.asp';

$Core_Http = Core_Http::instance()
	->url($url)
	->port(80)
	->timeout(10)
	->userAgent('Mozilla/5.0 (Windows NT 5.1; rv:26.0) Gecko/20100101 Firefox/26.0')
	->execute();

$xml = $Core_Http->getDecompressedBody();

$oXml = @simplexml_load_string($xml);

if (is_object($oXml))
{
	$fDate = Core_Date::date2sql($oXml->attributes()->Date);

	$oDefaultCurrency = Core_Entity::factory('Shop_Currency')->getBydefault(1);

	foreach ($oXml->Valute as $Valute)
	{
		$exchangeRate[strval($Valute->CharCode)] = floatval((str_replace(',', '.', $Valute->Value))) / floatval(str_replace(',', '.', $Valute->Nominal));
	}

	if ($oDefaultCurrency->code != 'RUB'
		&& $oDefaultCurrency->code != 'RUR'
		&& !isset($exchangeRate[$oDefaultCurrency->code])
	)
	{
		throw new Exception('Default currency does not exist in the XML');
	}

	// любая валюта по умолчанию равна 1
	$oDefaultCurrency->exchange_rate(1)->date($fDate)->save();

	/* Рубль - не всегда валюта по умолчанию, но он всегда отсутствует во входящем XML.
	 * Итак, если:
			валюта по умолчанию НЕ рубль
			И рубль присутсвует в списке валют
		ставим рублю его котировку, относительно валюты по умолчанию
	 */
	if ($oDefaultCurrency->code != 'RUB' && $oDefaultCurrency->code != 'RUR')
	{
		$fRubRate = 1.0 / $exchangeRate[$oDefaultCurrency->code];

		$oRubCurrency = Core_Entity::factory('Shop_Currency')->getByCode('RUB');
		is_null($oRubCurrency) && $oRubCurrency = Core_Entity::factory('Shop_Currency')->getByCode('RUR');

		!is_null($oRubCurrency)
			&& $oRubCurrency
				->exchange_rate($fRubRate)
				->date($fDate)
				->save();
	}

	foreach ($exchangeRate as $code => $rate)
	{
		$rate *= $coefficient;

		// ищем текущую валюту в магазине
		$oCurrentCurrency = Core_Entity::factory('Shop_Currency')->getByCode($code);
		if(is_null($oCurrentCurrency))
		{
			// валюта не найдена, пропускаем итерацию
			continue;
		}

		if ($oDefaultCurrency->code == 'RUR' || $oDefaultCurrency->code == 'RUB')
		{
			$oCurrentCurrency->exchange_rate = $rate;
			$oCurrentCurrency->date($fDate);
			$oCurrentCurrency->save();
		}
		elseif (isset($exchangeRate[$oDefaultCurrency->code]))
		{
			$oCurrentCurrency->exchange_rate = $rate * $fRubRate;
			$oCurrentCurrency->date($fDate);
			$oCurrentCurrency->save();
		}

		echo "Updated currency {$code} rate is {$oCurrentCurrency->exchange_rate}\n";
	}
}

echo "OK\n";