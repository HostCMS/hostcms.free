<?php

/**
 * СДЭК http://www.edostavka.ru/clients/integrator.html
 * Тестовые данные https://api-docs.cdek.ru/29923849.html
 */
class Shop_Delivery_Handler5 extends Shop_Delivery_Handler
{
	// client_id (для индивидуальных тарифов)
	private $_client_id = '';

	// client_secret (для индивидуальных тарифов)
	private $_client_secret = '';

	private $_url = 'https://api.cdek.ru/v2/';

	// местоположение магазина (отправки), почтовый индекс, Ростов-на-Дону
	private $_from = '344000';

	// весовой коэффициент (расчет ведется в килограммах)
	// По умолчанию вес в системе указан в граммах. При указани в килограммах - измените коэффициент на 0.001
	private $_coefficient = 1;

	// Объем по умолчанию (в куб.м)
	// private $_defaultVolume = 0.001;

	// список тарифов
	private $_tariffList = array(
		1 => 'Экспресс лайт дверь-дверь',
		3 => 'Супер-экспресс до 18',
		4 => 'Рассылка',
		5 => 'Экономичный экспресс склад-склад',
		7 => 'Международный экспресс документы',
		8 => 'Международный экспресс грузы',
		10 => 'Экспресс лайт склад-склад',
		11 => 'Экспресс лайт склад-дверь',
		12 => 'Экспресс лайт дверь-склад',
		15 => 'Экспресс тяжеловесы склад-склад',
		16 => 'Экспресс тяжеловесы склад-дверь',
		17 => 'Экспресс тяжеловесы дверь-склад',
		18 => 'Экспресс тяжеловесы дверь-дверь',
		57 => 'Супер-экспресс до 9',
		58 => 'Супер-экспресс до 10',
		59 => 'Супер-экспресс до 12',
		60 => 'Супер-экспресс до 14',
		61 => 'Супер-экспресс до 16',
		62 => 'Магистральный экспресс склад-склад',
		63 => 'Магистральный супер-экспресс склад-склад',
		66 => 'Блиц-экспресс 01',
		67 => 'Блиц-экспресс 02',
		68 => 'Блиц-экспресс 03',
		69 => 'Блиц-экспресс 04',
		70 => 'Блиц-экспресс 05',
		71 => 'Блиц-экспресс 06',
		72 => 'Блиц-экспресс 07',
		73 => 'Блиц-экспресс 08',
		74 => 'Блиц-экспресс 09',
		75 => 'Блиц-экспресс 10',
		76 => 'Блиц-экспресс 11',
		77 => 'Блиц-экспресс 12',
		78 => 'Блиц-экспресс 13',
		79 => 'Блиц-экспресс 14',
		80 => 'Блиц-экспресс 15',
		81 => 'Блиц-экспресс 16',
		82 => 'Блиц-экспресс 17',
		83 => 'Блиц-экспресс 18',
		84 => 'Блиц-экспресс 19',
		85 => 'Блиц-экспресс 20',
		86 => 'Блиц-экспресс 21',
		87 => 'Блиц-экспресс 22',
		88 => 'Блиц-экспресс 23',
		89 => 'Блиц-экспресс 24',
		136 => 'Посылка склад-склад',
		137 => 'Посылка склад-дверь',
		138 => 'Посылка дверь-склад',
		139 => 'Посылка дверь-дверь'
	);

	public function __construct(Shop_Delivery_Model $oShop_Delivery_Model) {
		parent::__construct($oShop_Delivery_Model);
	}

	protected $_token = NULL;

	protected function _getToken()
	{
		if(is_null($this->_token))
		{
			$request = http_build_query(array(
				'grant_type' => 'client_credentials',
				'client_id' => $this->_client_id,
				'client_secret' => $this->_client_secret
			));

			$Core_Http = Core_Http::instance('curl')
				->clear()
				->method('POST')
				->timeout(10)
				->url($this->_url . 'oauth/token?parameters')
				->additionalHeader('Content-type', 'application/json; charset=utf-8')
				->additionalHeader('Accept', 'application/json')
				->rawData($request)
				->execute();

			$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

			if (isset($aAnswer['access_token']))
			{
				$this->_token = $aAnswer['access_token'];
			}
		}

		return $this;
	}

	private function _getData($aParams)
	{
		$Core_Http = Core_Http::instance('curl')
			->clear()
			->method('POST')
			->timeout(10)
			->url($this->_url . 'calculator/tariff')
			->additionalHeader('Content-type', 'application/json; charset=utf-8')
			->additionalHeader('Authorization', "Bearer {$this->_token}")
			->additionalHeader('Accept', 'application/json')
			->rawData(json_encode($aParams))
			->execute();

		return json_decode($Core_Http->getDecompressedBody());
	}

	public function execute()
	{
		$this->_getToken();

		$fOrderWeight = $this->_weight * $this->_coefficient;

		if($fOrderWeight == 0)
		{
			throw new Exception("Неправильный вес (Вес равен нулю)");
		}

		if($this->_postcode == '')
		{
			throw new Exception("Индекс места назначения не указан");
		}

		$aRetObjs = array();

		$bError = FALSE;

		if(!is_null($this->_shopCountry->id) && $this->_shopCountry->id == 175)
		{
			foreach($this->_tariffList as $tariffId  =>  $tariffDescription)
			{
				if (!$bError)
				{
					// Основные параметры
					$data = array(
						// 'version' => '1.0',
						'tariff_code' => $tariffId,
						'from_location' => array(
							'postal_code' => $this->_from
						),
						'to_location' => array(
							'postal_code' => $this->_postcode
						),
						'packages' => array(
							0 => array(
								'weight' => $fOrderWeight,
								// 'volume' => ($this->_volume ? $this->_volume * pow(10, -9) : $this->_defaultVolume)
							)
						)
					);

					// Отправляем запрос к СДЭК
					$oResponse = $this->_getData($data);

					if(is_object($oResponse))
					{
						if (property_exists($oResponse, 'delivery_sum') && !property_exists($oResponse, 'errors'))
						{
							$oCurrentDeliveryType = new StdClass();
							$oCurrentDeliveryType->price = floatval($oResponse->delivery_sum);
							$oCurrentDeliveryType->description = $tariffDescription . ": Минимальный срок доставки: {$oResponse->period_min}, максимальный: {$oResponse->period_max} дней";
							$aRetObjs[] = $oCurrentDeliveryType;
						}
						elseif (isset($oResponse->errors))
						{
							foreach ($oResponse->errors as $oError)
							{
								?><div class="alert alert-danger" role="alert"><?php echo "СДЭК: " . $tariffDescription . ', ' . $oError->message?></div><?php
							}

							$bError = TRUE;
						}
					}
					else
					{
						$bError = TRUE;
					}
				}
			}
		}

		return $aRetObjs;
	}
}