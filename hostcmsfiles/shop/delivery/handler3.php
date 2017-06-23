<?php

class Shop_Delivery_Handler3 extends Shop_Delivery_Handler
{
	protected $type = 'att';
	protected $coefficient = 0.001;
	protected $from='city--rostov-na-donu';
	
	private function getData($aParams)
	{
		$url = "http://emspost.ru/api/rest/?" . http_build_query($aParams);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, 156, 5000);
		$data = curl_exec($ch);
		curl_close($ch);
		$oResponse = json_decode($data);
		if(!(is_object($oResponse) && $oResponse->rsp->stat == 'ok'))
		{
			throw new Exception($oResponse->rsp->stat . " ({$oResponse->rsp->err->msg})");
		}
		return $oResponse;
	}
	
	public function __construct() 
	{
		//Проверка сервиса
       $this->getData(array('method'=>'ems.test.echo'));
	}
	
	public function execute()
	{
		$oResponse = $this->getData(array('method'=>'ems.get.max.weight'));
		$fOrderWeight = $this->_weight * $this->coefficient;
		
		if($fOrderWeight == 0 || $fOrderWeight > $oResponse->rsp->max_weight)
		{
			throw new Exception("Wrong order weight ({$fOrderWeight}) [{$oResponse->rsp->max_weight}]");
		}

		if($this->_shopCountry->id == 175)
		{
			$oResponse = $this->getData(array('method'=>'ems.get.locations', 'type'=>'cities', 'plain'=>'true'));
			$oCity = NULL;
			foreach($oResponse->rsp->locations as $oObj)
			{
				if(mb_strtoupper($this->_shopCity->name) == $oObj->name)
				{
					$oCity = $oObj;
					break;
				}
			}
			
			if($oCity !== NULL)
			{
				$oResponse = $this->getData(array('method'=>'ems.calculate','from'=>$this->from,'to'=>$oCity->value,'weight'=>$fOrderWeight));
				$oReturn = new StdClass;
				$oReturn->price = $oResponse->rsp->price;
				$oReturn->description = "Минимальный срок доставки (дней): {$oResponse->rsp->term->min}, максимальный - {$oResponse->rsp->term->max}";
				return $oReturn;
			}
			else
			{
				throw new Exception("City {$this->_shopCity->name} not found");
			}
		}
		else
		{
			$oResponse = $this->getData(array('method'=>'ems.get.locations','type'=>'countries','plain'=>'true'));
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
				$oResponse = $this->getData(array('method'=>'ems.calculate','to'=>$oCountry->value,'weight'=>$fOrderWeight,'type'=>$this->type));
				return $oResponse->rsp->price;
			}
			else
			{
				throw new Exception("Country {$this->_shopCountry->name} not found");
			}
		}
	}
}