<?php
class Shop_Delivery_Handler4 extends Shop_Delivery_Handler
{
	// Токен обращения к БД КЛАДР
	private $_token = '5281f71e31608f6a0c000001';
	// Ключ обращения к БД КЛАДР
	private $_key = '33a4e73ff15a722b94a39830e418983558b733e3';
	// Адрес БД КЛАДР
	private $_kladrUrl = 'http://kladr-api.ru/api.php';
	// Адрес Деловые Линии
	private $_delovyeLiniiUrl = 'http://public.services.dellin.ru/calculatorService2/index.html';
	// местоположение магазина (отправки), код КЛАДР (Ростов-на-Дону)
	private $_from='6100000100000000000000000';
	// весовой коэффициент (расчет ведется в килограммах)
	private $_coefficient = 0.001;

	private function _getData($sUrl, $aParams)
	{
		$url = $sUrl . '?' . http_build_query($aParams);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, 156, 5000);
		$oResponse = curl_exec($ch);
		curl_close($ch);
		return $oResponse;
	}

	public function execute()
	{
		if(is_null($this->_shopCountry->id) || is_null($this->_shopLocation->id) || is_null($this->_shopCity->id))
		{
			throw new Exception("Не указаны данные доставки");
		}
	
		if($this->_shopCountry->id == 175)
		{
			$oJSONData = json_decode($this->_getData($this->_kladrUrl, array('token'=>$this->_token,'key'=>$this->_key,'limit'=>1,'contentType'=>'city','withParent'=>'0','query'=>$this->_shopCity->name)));
			
			$fOrderWeight = $this->_weight * $this->_coefficient;
			
			if($fOrderWeight == 0)
			{
				throw new Exception("Wrong order weight ({$fOrderWeight})");
			}
			
			if(is_object($oJSONData) && is_array($oJSONData->result) && count($oJSONData->result) > 0)
			{
				$oKLADRCity = $oJSONData->result[0];
				
				$oDLXMLData = new Core_SimpleXMLElement($this->_getData($this->_delovyeLiniiUrl, array('request'=>'xmlResult','derivalPoint'=>$this->_from,'arrivalPoint'=>$oKLADRCity->id . '000000000000','sizedWeight'=>$fOrderWeight,'sizedVolume'=>(($this->_volume) ? $this->_volume * pow(10, -9) : 1))));
		
				if($oDLXMLData->error) 
				{
					throw new Exception("Ошибка: ".strval($oDLXMLData->error));
				}
				
				$oReturn = new StdClass;
				$oReturn->price = strval($oDLXMLData->price);
				
				if($oReturn->price == 0)
				{
					throw new Exception("Цена не может быть 0");
				}
				
				$oReturn->description = strval($oDLXMLData->time);
				$aSteps = array();
				foreach($oDLXMLData->time->part as $str)
				{
					 $aSteps[] = strval($str);
				}
				$oReturn->description .= implode('. ', $aSteps);
				
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