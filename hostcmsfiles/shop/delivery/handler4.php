<?php

/**
 * Деловые Линии.
 */
class Shop_Delivery_Handler4 extends Shop_Delivery_Handler
{
	// Токен обращения к БД КЛАДР
	private $_token = '573eb8060a69dec54a8b4593';

	// Адрес БД КЛАДР
	private $_kladrUrl = 'http://kladr-api.ru/api.php';

	// Адрес Деловые Линии
	private $_delovyeLiniiUrl = 'https://api.dellin.ru/v1/public/calculator.json';

	// местоположение магазина (отправки), код КЛАДР (Ростов-на-Дону)
	private $_from = '6100000100000000000000000';

	// весовой коэффициент (расчет ведется в килограммах)
	private $_coefficient = 0.001;

	public function execute()
	{
		if(is_null($this->_shopCountry->id) || is_null($this->_shopLocation->id) || is_null($this->_shopCity->id))
		{
			throw new Exception("Не указаны данные доставки");
		}

		if($this->_shopCountry->id == 175)
		{
			$aParams = array(
				'query' => $this->_shopCity->name,
				'contentType' => 'city',
				'withParent' => 0,
				'limit' => 1,
				'token' => $this->_token,
			);

			$sUrl = $this->_kladrUrl . '?' . http_build_query($aParams);

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('GET')
				->url($sUrl)
				->execute();

			$oResponse = json_decode($Core_Http->getBody());

			$fOrderWeight = $this->_weight * $this->_coefficient;

			if($fOrderWeight == 0)
			{
				throw new Exception("Wrong order weight ({$fOrderWeight})");
			}

			if(is_object($oResponse) && is_array($oResponse->result) && count($oResponse->result) > 0)
			{
				$oKLADRCity = $oResponse->result[0];

				$aDLParams = array(
					'AppKey' => '3B39082A-518E-11E6-A107-00505683A6D3',
					'derivalPoint' => $this->_from,
					'arrivalPoint' => $oKLADRCity->id . '000000000000',
					'sizedWeight' => $fOrderWeight,
					'sizedVolume' => (($this->_volume) ? $this->_volume * pow(10, -9) : 1),
				);

				$json = json_encode($aDLParams);

				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('POST')
					->url($this->_delovyeLiniiUrl)
					->additionalHeader('Content-Type', 'application/json')
					->rawData($json)
					->execute();

				$oDLJSONData = json_decode($Core_Http->getBody());

				if (isset($oDLJSONData->errors))
				{
					throw new Exception("Ошибка: " . strval($oDLJSONData->errors));
				}

				$oReturn = new StdClass;

				$oReturn->price = strval($oDLJSONData->price);

				if($oReturn->price == 0)
				{
					throw new Exception("Цена не может быть 0");
				}

				$oReturn->description = strval($oDLJSONData->time->nominative);

				return $oReturn;
			}
			else
			{
				throw new Exception("Город {$this->_shopCity->name} не найден, либо произошла ошибка");
			}
		}
		else
		{
			throw new Exception("Расчет возможен только внутри РФ");
		}
	}
}