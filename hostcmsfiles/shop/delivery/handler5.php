<?php
/*
	СДЭК http://www.edostavka.ru/clients/integrator.html
*/
class Shop_Delivery_Handler5 extends Shop_Delivery_Handler
{
	// Login (для индивидуальных тарифов)
	private $_authLogin = '';

	// Пароль (для индивидуальных тарифов)
	private $_secure = '';

	// местоположение магазина (отправки), почтовый индекс, Ростов-на-Дону
	private $_from = '344000';

	// весовой коэффициент (расчет ведется в килограммах)
	// По умолчанию вес в системе указан в граммах. При указани в килограммах - измените коэффициент на 1
	private $_coefficient = 0.001;

	// Дата планируемой отправки
	private $_dateExecute;

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
		89 => 'Блиц-экспресс 24'
	);

	public function __construct() {
	     $this->_dateExecute = date('Y-m-d');
	}

	private function _getData($aParams)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://api.edostavka.ru/calculator/calculate_price_by_json.php');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aParams));
		curl_setopt($ch, 156, 5000);
		$oResponse = curl_exec($ch);
		curl_close($ch);
		return json_decode($oResponse);
	}

	public function execute()
	{
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
						'version' => '1.0',
						'tariffId' => $tariffId,
						'senderCityPostCode' => $this->_from,
						'receiverCityPostCode' => $this->_postcode,
						'goods' => array(
							0 => array(
								'weight' => $fOrderWeight,
								'volume' => ($this->_volume ? $this->_volume * pow(10, -9) : 1)
							)
						)
					);

					// Авторизация для индивидуальных тарифов
					if ($this->_authLogin && $this->_secure)
					{
						$data[] = array(
							'dateExecute' => $this->_dateExecute,
							'authLogin' => $this->_authLogin,
							'secure' => md5($this->_dateExecute . '&' . $this->_secure),
						);
					}

					// Отправляем запрос к СДЭК
					$oResponse = $this->_getData($data);

					if(is_object($oResponse) && property_exists($oResponse, 'result') && !property_exists($oResponse, 'error'))
					{
						$oCurrentDeliveryType = new StdClass();
						$oCurrentDeliveryType->price = floatval($oResponse->result->price);
						$oCurrentDeliveryType->description = $tariffDescription . " Минимальный срок доставки: {$oResponse->result->deliveryPeriodMin}, максимальный: {$oResponse->result->deliveryPeriodMax} дней";
						$aRetObjs[] = $oCurrentDeliveryType;
					}
					else
					{
						foreach ($oResponse->error as $oError)
						{
							echo "СДЭК: " . $oError->text . "<br/>";
						}

						$bError = TRUE;
					}
				}
			}
		}

		return $aRetObjs;
	}
}