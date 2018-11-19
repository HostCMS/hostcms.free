<?php

/**
 * Доставка Почтой России с расчетом стоимости и различными видами отправлений
 */
class Shop_Delivery_Handler1 extends Shop_Delivery_Handler
{
	// тестовый режим
	private $_testMode = FALSE;

	// весовой коэффициент (расчет ведется в граммах)
	private $_coefficient = 1;

	// ограничение доставки в 100 кг
	private $_maxWeight = 100000;

	// местоположение магазина (отправки), почтовый индекс, Ростов-на-Дону
	private $_from = '344000';
	
	protected $_insurance = TRUE;  // Страхование   TRUE/FALSE 
	
	protected $_iBase = 'Partial'; // База для расчета страховки (только при оценке товарного вложения больше 0!) Partial / Full

	// Список тарифов
	private $_tariffList = array(
		// Внутренние отправления
		'ПростаяБандероль',
		'ЗаказнаяБандероль',
		'ЗаказнаяБандероль1Класс',
		'ЦеннаяБандероль',
		'ЦеннаяПосылка',
		'ЦеннаяБандероль1Класс',
		'Посылка1Класс',
		'EMS',
		'ПосылкаОнлайн',
		'КурьерОнлайн',
		'ПростоеПисьмо',
		'ЗаказноеПисьмо',
		'ЦенноеПисьмо',
		'ПростойМультиконверт',
		'ЗаказнойМультиконверт',

		// Международные отправления
		'МждМешокМ',
		'МждМешокМАвиа',
		'МждМешокМЗаказной',
		'МждМешокМАвиаЗаказной',
		'МждБандероль',
		'МждБандерольАвиа',
		'МждБандерольЗаказная',
		'МждБандерольАвиаЗаказная',
		'МждМелкийПакет',
		'МждМелкийПакетАвиа',
		'МждМелкийПакетЗаказной',
		'МждМелкийПакетАвиаЗаказной',
		'EMS_МждДокументы',
		'EMS_МждТовары',
		'МждПосылка',
		'МждПосылкаАвиа'
	);

	private function getData($aParams)
	{
		$aParams['o'] = 'json';

		if(!$this->_testMode)
		{
			$url = "http://api.postcalc.ru?";
			$aParams['st'] = 'site';
			$aParams['ml'] = 'email';
			$aParams['person'] = 'person';
			
			if($this->_insurance)
			{
				$aParams['v'] = $this->_amount;
				$aParams['ib'] = $this->_iBase;
			}
		}
		else
		{
			$url = "http://test.postcalc.ru?";
		}

		$url = $url . http_build_query($aParams);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, 156, 5000);
		curl_setopt($ch, CURLOPT_ENCODING , "");
		$data = curl_exec($ch);
		curl_close($ch);

		$oResponse = json_decode($data);

		if(!is_object($oResponse) || (is_object($oResponse) && $oResponse->Status != 'OK'))
		{
			if(is_object($oResponse))
			{
				throw new Exception($oResponse->Status . " ({$oResponse->Message})");
			}
			else
			{
				throw new Exception("Ошибка");
			}
		}

		return $oResponse;
	}

	public function execute()
	{
		$fOrderWeight = $this->_weight * $this->_coefficient;
		
		if($fOrderWeight == 0 || $fOrderWeight > $this->_maxWeight)
		{
			$errorDescription = ($fOrderWeight == 0 ? "Вес равен нулю" : "Вес превышает максимально допустимые 100 кг.");
			throw new Exception("Неправильный вес ({$fOrderWeight}) [{$errorDescription}]");
		}

		if(is_null($this->_postcode) || $this->_postcode=='')
		{
			throw new Exception("Индекс места назначения не указан");
		}

		if(!is_null($this->_shopCountry->id) && $this->_shopCountry->id == 175)
		{
			$oResponse = $this->getData(array('f'=>$this->_from,'c' => 'RU','t'=>$this->_postcode,'w'=>$fOrderWeight));

			$aRetObjs = array();

			foreach($oResponse->Отправления as $sTariff => $oDeliveryType)
			{
				if (in_array($sTariff, $this->_tariffList))
				{
					if (is_object($oDeliveryType) && property_exists($oDeliveryType, 'Название')
						&& property_exists($oDeliveryType, 'Тариф') && !property_exists($oDeliveryType, 'НетРасчета')
					)
					{
						$oCurrentDeliveryType = new StdClass();
						$oCurrentDeliveryType->price = floatval($oDeliveryType->Доставка);
						$oCurrentDeliveryType->description = strval($oDeliveryType->Название);
						$aRetObjs[] = $oCurrentDeliveryType;
					}
				}
			}

			return $aRetObjs;
		}
		else
		{
			$oResponse = $this->getData(array('f'=>$this->_from,'c'=>$this->_shopCountry->alpha2,'w'=>$fOrderWeight));

			$aRetObjs = array();

			foreach($oResponse as $sTariff => $oDeliveryType)
			{
				if (in_array($sTariff, $this->_tariffList))
				{
					if(is_object($oDeliveryType) && property_exists($oDeliveryType, 'Название')
						&& property_exists($oDeliveryType, 'Тариф')  && !property_exists($oDeliveryType, 'НетРасчета')
					)
					{
						$oCurrentDeliveryType = new StdClass();
						$oCurrentDeliveryType->price = floatval($oDeliveryType->Доставка);
						$oCurrentDeliveryType->description = strval($oDeliveryType->Название);
						$aRetObjs[] = $oCurrentDeliveryType;
					}
				}
			}

			return $aRetObjs;
		}
	}
}