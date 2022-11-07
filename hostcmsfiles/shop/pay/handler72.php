<?php

/**
 * Platron
 */
class Shop_Payment_System_Handler72 extends Shop_Payment_System_Handler
{
	// Идентифкатор магазина, с которым будет работать обработчик
	private $_merchant_id = 1;

	// Секретный ключ (заполняется вручную пользователем на странице настроек магазина)
	private $_merchant_secret_key = "XXXXXXXXXXXXXXXX";

	// Валюта платежа RUB для работы или TST для тестирования системы
	//private $_currencyName = "RUB";
	private $_currencyName = "TST";

	// Код рублей
	private $_platron_currency = 1;

	/**
	 * Метод, вызываемый в коде ТДС через Shop_Payment_System_Handler::checkAfterContent($oShop);
	 */
	public function checkPaymentAfterContent()
	{
		if (isset($_REQUEST['pg_order_id']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getRequest('pg_order_id'));

			$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

			if (!is_null($oShop_Order->id))
			{
				// Вызов обработчика платежной системы
				Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
					->shopOrder($oShop_Order)
					->paymentProcessing();
			}
		}
	}
	
	/* Вызывается на 4-ом шаге оформления заказа*/
	public function execute()
	{
		parent::execute();

		$this->printNotification();

		return $this;
	}

	protected function _processOrder()
	{
		parent::_processOrder();

		// Установка XSL-шаблонов в соответствии с настройками в узле структуры
		$this->setXSLs();

		// Отправка писем клиенту и пользователю
		$this->send();

		return $this;
	}

	/* вычисление суммы товаров заказа */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_platron_currency > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_platron_currency)
			)
			: 0) * $this->_shopOrder->getAmount());
	}

	/* обработка ответа от платёжной системы */
	public function paymentProcessing()
	{
			$this->ProcessResult();

			return TRUE;
	}

	/* оплачивает заказ */
	function ProcessResult()
	{
		if($this->_shopOrder->paid)
		{
			return FALSE;
		}

		$order_sum = $this->getSumWithCoeff();

		// Сравниваем хэши
		if (self::check(Core_Array::getRequest('pg_sig', ''), "", $_REQUEST, $this->_merchant_secret_key)
			&& Core_Array::getRequest('pg_result', 0) == 1
			&& Core_Array::getRequest('pg_net_amount', 0) == $order_sum)
		{
			$this->_shopOrder->system_information = sprintf("Товар оплачен через Platron.\n\nИнформация:\n\nИдентификатор платежа в системе Platron: %s\n", Core_Array::getRequest('pg_payment_id'));

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
			
			$oAnswr = new Core_SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n<response></response>");
			$sRand = rand(21,43433);
			$oAnswr->addChild('pg_salt',$sRand);
			$oAnswr->addChild('pg_status','ok');
			$oAnswr->addChild('pg_sig',self::make('', array('pg_salt'=>$sRand,'pg_status'=>'ok'), $this->_merchant_secret_key));
			header('Content-type: text/xml; charset=UTF-8');
			echo "\xEF\xBB\xBF";
			echo $oAnswr->asXML();
		}
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$order_sum = $this->getSumWithCoeff();

		/*$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';
		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://'.$site_alias.$shop_path.'cart/';*/

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_platron_currency);

		$array_of_params = array(
			'pg_merchant_id' => $this->_merchant_id,
			'pg_order_id' => $this->_shopOrder->id,
			'pg_amount' => $order_sum,
			'pg_lifetime' => 86400,
			'pg_description' => "Оплата заказа №{$this->_shopOrder->id}",
			'pg_salt' => rand(21,43433),
			'zzz' => "Перейти к оплате"
		);

		$sig = self::make('payment.php', $array_of_params, $this->_merchant_secret_key);

		if(!is_null($oShop_Currency->id))
		{
			?>
			<h1>Оплата через систему Platron</h1>
			<!-- Форма для оплаты через Platron -->
			<p>К оплате <strong><?php echo $order_sum . " " . $oShop_Currency->name?></strong></p>
			<form method="post" action="https://www.platron.ru/payment.php">

				<input type="hidden" name="pg_merchant_id" value="<?php echo $array_of_params['pg_merchant_id']?>">
				<input type="hidden" name="pg_order_id" value="<?php echo $array_of_params['pg_order_id']?>">
				<input type="hidden" name="pg_amount" value="<?php echo $array_of_params['pg_amount']?>">
				<input type="hidden" name="pg_lifetime" value="<?php echo $array_of_params['pg_lifetime']?>">
				<input type="hidden" name="pg_description" value="<?php echo $array_of_params['pg_description']?>">
				<input type="hidden" name="pg_salt" value="<?php echo $array_of_params['pg_salt']?>">
				<input type="hidden" name="pg_sig" value="<?php echo $sig?>">

				<input name="submit" value="Перейти к оплате" type="submit"/>
			</form>
			<?php
		}
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

	// *****************************
	//  Далее идут методы Platron'а
	// *****************************

	public static function getScriptNameFromUrl($url)
	{
		$path = parse_url($url, PHP_URL_PATH);
		$len  = strlen($path);
		if($len == 0  ||  '/' == $path[$len-1]) {
			return "";
		}
		return basename($path);
	}

	/**
	 * Get name of currently executed script (need to check signature of incoming message using self::check)
	 *
	 * @return string
	 */
	public static function getOurScriptName ()
	{
		return self::getScriptNameFromUrl($_SERVER['PHP_SELF']);
	}

	/**
	 * Creates a signature
	 *
	 * @param array $arrParams  associative array of parameters for the signature
	 * @param string $strSecretKey
	 * @return string
	 */
	public static function make($strScriptName, $arrParams, $strSecretKey)
	{
		return md5(self::makeSigStr($strScriptName, $arrParams, $strSecretKey));
	}

	/**
	 * Verifies the signature
	 *
	 * @param string $signature
	 * @param array $arrParams  associative array of parameters for the signature
	 * @param string $strSecretKey
	 * @return bool
	 */
	public static function check($signature, $strScriptName, $arrParams, $strSecretKey)
	{
		return (string)$signature === self::make($strScriptName, $arrParams, $strSecretKey);
	}

	private static function makeSigStr($strScriptName, $arrParams, $strSecretKey) {
		unset($arrParams['pg_sig']);

		ksort($arrParams);

		array_unshift($arrParams, $strScriptName);
		array_push($arrParams, $strSecretKey);

		return join(';', $arrParams);
	}


	/**
	 * make the signature for XML
	 *
	 * @param string|SimpleXMLElement $xml
	 * @param string $strSecretKey
	 * @return string
	 */
	public static function makeXML($strScriptName, $xml, $strSecretKey)
	{
		$arrFlatParams = self::makeFlatParamsXML($xml);
		return self::make($strScriptName, $arrFlatParams, $strSecretKey);
	}

	/**
	 * Returns flat array of XML params
	 *
	 * @param (string|SimpleXMLElement) $xml
	 * @return array
	 */
	private static function makeFlatParamsXML($xml, $parent_name = '')
	{
		if(! $xml instanceof SimpleXMLElement) {
			$xml = new SimpleXMLElement($xml);
		}

		$arrParams = array();
		$i = 0;
		foreach($xml->children() as $tag) {

			$i++;
			if('pg_sig' == $tag->getName())
				continue;

			/**
			 * Имя делаем вида tag001subtag001
			 * Чтобы можно было потом нормально отсортировать и вложенные узлы не запутались при сортировке
			 */
			$name = $parent_name . $tag->getName().sprintf('%03d', $i);

			if($tag->children()) {
				$arrParams = array_merge($arrParams, self::makeFlatParamsXML($tag, $name));
				continue;
			}

			$arrParams += array($name => (string)$tag);
		}

		return $arrParams;
	}

}