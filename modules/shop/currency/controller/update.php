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
class Shop_Currency_Controller_Update extends Admin_Form_Action_Controller
{
	/**
	 * Exchange rate array
	 * @var array
	 */
	private $_exchangeRate = array();

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);
	}
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$url = 'http://www.cbr.ru/scripts/XML_daily.asp';

		$Core_Http = Core_Http::instance()
			->url($url)
			->port(80)
			->timeout(10)
			->additionalHeader('User-Agent', 'Mozilla/5.0 (Windows NT 5.1; rv:26.0) Gecko/20100101 Firefox/26.0')
			->execute();

		$xml = $Core_Http->getBody();

		$oXml = @simplexml_load_string($xml);

		if (is_object($oXml))
		{
			$fDate = Core_Date::date2sql($oXml->attributes()->Date);

			$oDefaultCurrency = Core_Entity::factory('Shop_Currency')->getBydefault(1);

			// валюты по умолчанию нет, нет смысла считать дальше
			if(is_null($oDefaultCurrency))
			{
				throw new Exception('Валюта по умолчанию не задана. Невозможно рассчитать курс');
			}

			// получаем данные о котировках их XML
			foreach ($oXml->Valute as $Valute)
			{
				$this->_exchangeRate[strval($Valute->CharCode)] = floatval((str_replace(',', '.', $Valute->Value))) / floatval(str_replace(',', '.', $Valute->Nominal));
			}

			if ($oDefaultCurrency->code != 'RUR'
				&& $oDefaultCurrency->code != 'RUB'
				&& !isset($this->_exchangeRate[$oDefaultCurrency->code])
			)
			{
				throw new Exception('Валюта по умолчанию отсутствует во входящем XML');
			}

			// любая валюта по умолчанию равна 1
			$oDefaultCurrency->exchange_rate(1)->date($fDate)->save();

			/* Рубль - не всегда валюта по умолчанию, но он всегда отсутствует во входящем XML.
			 * Итак, если:
					валюта по умолчанию НЕ рубль
					И рубль присутсвует в списке валют
				ставим рублю его котировку, относительно валюты по умолчанию
			 */
			if ($oDefaultCurrency->code != 'RUR' && $oDefaultCurrency->code != 'RUB')
			{
				$fRubRate = 1.0 / $this->_exchangeRate[$oDefaultCurrency->code];

				$oRubCurrency = Core_Entity::factory('Shop_Currency')->getByCode('RUB');
				is_null($oRubCurrency) && $oRubCurrency = Core_Entity::factory('Shop_Currency')->getByCode('RUR');

				!is_null($oRubCurrency)
					&& $oRubCurrency
						->exchange_rate($fRubRate)
						->date($fDate)
						->save();
			}

			foreach ($this->_exchangeRate as $code => $rate)
			{
				// ищем текущую валюту в магазине
				$oCurrentCurrency = Core_Entity::factory('Shop_Currency')->getByCode($code);
				if(is_null($oCurrentCurrency))
				{
					// валюта не найдена, пропускаем итерацию
					continue;
				}

				if ($oDefaultCurrency->code == 'RUB' || $oDefaultCurrency->code == 'RUR')
				{
					$oCurrentCurrency->exchange_rate = $rate;
					$oCurrentCurrency->date($fDate);
					$oCurrentCurrency->save();
				}
				elseif(isset($this->_exchangeRate[$oDefaultCurrency->code]))
				{
					$oCurrentCurrency->exchange_rate = $rate * $fRubRate;
					$oCurrentCurrency->date($fDate);
					$oCurrentCurrency->save();
				}
				else
				{
					throw new Exception("Валюта по умолчанию ({$oDefaultCurrency->code}) отсутствует во входящем XML с котировками");
				}
			}
		}

		return $this;
	}
}