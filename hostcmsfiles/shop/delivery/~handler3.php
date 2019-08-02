<?php

/**
 * Доставка EMS.
 * API: http://www.emspost.ru/ru/corp_clients/dogovor_docements/api/
*/
class Shop_Delivery_Handler3 extends Shop_Delivery_Handler
{
	// Весовой коэффициент (расчет ведется в килограммах)
	protected $coefficient = 0.001;

	/* Тип международного отправления (обязательный для международной доставки). Допустимые значения:
		doc — документы (до 2-х килограм),
		att — товарные вложения.
	*/
	protected $type = 'att';

	/*	Идентификатор пункта отправления.
		Для получения списка допустимых идентификаторов используется метод ems.get.locations
	*/
	protected $from = 'city--rostov-na-donu';

	private function getData($aParams)
	{
		// https://otpravka-api.pochta.ru/api/rest/?
		$url = "http://emspost.ru/api/rest/?" . http_build_query($aParams);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, 156, 5000);
		$data = curl_exec($ch);
		curl_close($ch);

		$oResponse = json_decode($data);

		if(!is_object($oResponse) || $oResponse->rsp->stat != 'ok')
		{
			throw new Exception(is_object($oResponse) && isset($oResponse->rsp->stat)
				? $oResponse->rsp->stat . " ({$oResponse->rsp->err->msg})"
				: 'Wrong emspost.ru answer'
			);
		}

		return $oResponse;
	}

	public function __construct()
	{
		//Проверка сервиса. При отказе сервиса корзина работает с задержкой
		//$this->getData(array('method' => 'ems.test.echo'));
	}

	public function execute()
	{
		$oResponse = $this->getData(array('method' => 'ems.get.max.weight'));
		$fOrderWeight = $this->_weight * $this->coefficient;

		if ($fOrderWeight == 0)
		{
			throw new Exception('Ошибка, нулевой вес заказа!');
		}
		elseif ($fOrderWeight > $oResponse->rsp->max_weight)
		{
			throw new Exception("Превышение веса ({$fOrderWeight}), максимальный вес {$oResponse->rsp->max_weight}");
		}

		if($this->_shopCountry->id == 175)
		{
			// Рассчет по городам
			$oResponseCity = $this->getData(array('method'=>'ems.get.locations', 'type'=>'cities', 'plain'=>'true'));
			$oCity = NULL;
			foreach($oResponseCity->rsp->locations as $oObj)
			{
				if(mb_strtoupper($this->_shopCity->name) == $oObj->name)
				{
					$oCity = $oObj;
					break;
				}
			}

			if($oCity !== NULL)
			{
				$oResponseCity = $this->getData(array('method'=>'ems.calculate', 'from' => $this->from, 'to' => $oCity->value, 'weight' =>$fOrderWeight));
				$oReturn = new StdClass;
				$oReturn->price = $oResponseCity->rsp->price;
				$oReturn->description = "Минимальный срок доставки (дней): {$oResponseCity->rsp->term->min}, максимальный - {$oResponseCity->rsp->term->max}";
				return $oReturn;
			}
			else
			{
				// Рассчет по регионам
				$oResponseRegion = $this->getData(array('method'=>'ems.get.locations', 'type' => 'regions', 'plain' => 'true'));
				$oRegion = NULL;

				foreach($oResponseRegion->rsp->locations as $oObj)
				{
					$aRegionName = explode(' ', $oObj->name);

					$aBaseRegionName = explode(' ', $this->_shopLocation->name);

					$aBaseRegionName[0] == 'Москва' && $aBaseRegionName[0] = 'Московская';

					if (mb_strtoupper($aBaseRegionName[0]) == $aRegionName[0])
					{
						$oRegion = $oObj;
						break;
					}
				}

				if($oRegion !== NULL)
				{
					$oResponseRegion = $this->getData(array('method'=>'ems.calculate', 'from' => $this->from, 'to' => $oRegion->value, 'weight' =>$fOrderWeight));
					$oReturn = new StdClass;
					$oReturn->price = $oResponseRegion->rsp->price;
					$oReturn->description = "Минимальный срок доставки (дней): {$oResponseRegion->rsp->term->min}, максимальный - {$oResponseRegion->rsp->term->max}";
					return $oReturn;
				}
				else
				{
					throw new Exception("City or region not found");
				}
			}
		}
		else
		{
			$oResponse = $this->getData(array('method' => 'ems.get.locations','type' => 'countries','plain' => 'true'));
			$oCountry = NULL;
			foreach($oResponse->rsp->locations as $oObj)
			{
				if(mb_strtoupper($this->_shopCountry->name) == $oObj->name)
				{
					$oCountry = $oObj;
					break;
				}
			}

			if($oCountry !== NULL)
			{
				$oResponse = $this->getData(array('method' => 'ems.calculate', 'to' => $oCountry->value, 'weight' => $fOrderWeight,'type' => $this->type));
				return $oResponse->rsp->price;
			}
			else
			{
				throw new Exception("Country {$this->_shopCountry->name} not found");
			}
		}
	}
}