<?php

/**
 * Cash24
 *
 * Cash24 - это интернет-касса, которая дает возможность интернет-магазину принимать оплату за товары или услуги
 * всеми современными видами электронных платежей:
 * электронные деньги, банковские карты, денежные переводы, оплата в терминалах самообслуживания,
 * оплата со счета мобильного и другие способы, а также вести учет операций и управлять транзакциями в личном кабинете.
 *
 * Поддержка обработчика платежной системы осуществляется Cash24.
 * http://cash24.ru/
 *
 */
class Shop_Payment_System_Handler25 extends Shop_Payment_System_Handler
{
	/**
	 * ID мерчанта, полученный при регистрации в системе Cash24
	 */
	protected $_cash24_merchant_id = '';

	/**
	 * 1 - тестовый режим, 0 - рабочий режим
	 */
	protected $_cash24_test_mode = 0;

	/**
	 * секретный ключ
	 */
	protected $_cash24_secret_key = '';

	/**
	 *command ключ
	 */
	protected $_cash24_command_key = '';

	protected $_cash24_method = '';

	/**
	 * Метод, вызываемый в коде ТДС через Shop_Payment_System_Handler::checkAfterContent($oShop);
	 */
	public function checkPaymentAfterContent()
	{
		if (isset($_POST['order']))
		{
			$order_id = intval(Core_Array::getPost('order'));

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
	
	/**
	 * @return Shop_Payment_System_Handler|Shop_Payment_System_Handler9
	 * Метод, запускающий выполнение обработчика. Вызывается на 4-ом шаге оформления заказа
	 */
	public function execute()
	{
		parent::execute();
		$this->printNotification();

		return $this;
	}

	/**
	 * @return Shop_Payment_System_Handler|Shop_Payment_System_Handler9
	 */
	protected function _processOrder()
	{
		parent::_processOrder();

		// Установка XSL-шаблонов в соответствии с настройками в узле структуры
		$this->setXSLs();

		// Отправка писем клиенту и пользователю
		$this->send();

		return $this;
	}

	/**
	 * Вычисление суммы товаров заказа
	 * @return mixed
	 */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round((1 > 0
			&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', 1)
			)
			: 0) * $this->_shopOrder->getAmount() * 1);
	}

	/**
	 * @return mixed
	 */
	public function getInvoice()
	{
		return $this->getNotification();
	}

	/**
	 * Печатает форму отправки запроса на сайт платёжной системы
	 * @return mixed|void
	 */
	public function getNotification()
	{
		$merchant_id = $this->_cash24_merchant_id; // id мерчанта
		$merchant_com = $this->_cash24_command_key;
		$order_id = $this->_shopOrder->id; // номер заказа
		$amount = $this->getSumWithCoeff(); // сумма покупки

		$test_mode = $this->_cash24_test_mode; // тестовый режим

		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';
		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$result_url = 'http://' . $site_alias . $shop_path . 'cart/'; //url на который будет отправлено уведомление о состоянии платежа
		$success_url = $result_url . '?order_id=' . $order_id . '&payment=success'; //url на который будет перенаправлен плательщик после успешной оплаты
		$fail_url = $result_url . '?order_id=' . $order_id . "&payment=fail"; //url на который будет перенаправлен плательщик при отказе от оплаты

		$time = time() + 24 * 60 * 60;

		$arParams = array();
		$arParams['amount'] = $amount;
		$arParams['currency'] = "RUB";
		$arParams['email'] = $_SESSION['hostcmsOrder']['email'];
		$arParams['description'] = 'Order #'.$order_id;
		$arParams['order'] = $order_id;
		$arParams['success'] = htmlspecialchars($success_url);
		$arParams['cancel'] = htmlspecialchars($fail_url);
		$arParams['callback'] = htmlspecialchars($result_url);
		$arParams['method'] = $this->_cash24_method;
		$arParams['phone'] = $_SESSION['hostcmsOrder']['phone'];
		$arParams['wallet'] = '';
		$arParams['expires'] = gmdate('Y-m-d', $time) . 'T' . gmdate('H:i:s', $time);

		$respurl = $this->_cash24_test_mode == '1'
			? "http://api.staging.cash24.ru/1.0/"
			: "https://api.cash24.ru/1.0/";

		function createInvoice($arParams,$auth,$key)
		{
			$auth = $auth;
			$commandSecretKey = $key;

			$request = '';
			$toSign = 'create-invoice';
			foreach ($arParams as $key => $val)
			{
				$request .= "<$key>" . $val . "</$key>\r\n";
				if ($key != 'expires' && $key != 'phone')
				$toSign .= '-' . $val;
			}

			$sign = md5(htmlspecialchars_decode($toSign) . '-' . $commandSecretKey);

			$xml = '<?xml version="1.0" encoding="utf-8"?>
			<cash24 xmlns="http://api.cash24.ru/1.0/">
			<request xmlns="http://api.cash24.ru/1.0/create-invoice/">
			' . $request . '
			</request>
			<envelope>
			<auth>' . $auth . '</auth>
			<sign>' . $sign . '</sign>
			</envelope>
			</cash24>';

			return trim($xml);
		}

		$headers = array(
			"Content-type: text/xml"
		);

		$k = createInvoice($arParams,$merchant_id,$merchant_com);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $respurl);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $k);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );

		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		$out = curl_exec($curl);
		$oXml = simplexml_load_string($out);

		$turl = $url;

		//формируем строку url
		if (isset($oXml->response->url))
		{
			$url = $oXml->response->url;

			if (!empty($oXml->envelope->error))
			{
				return htmlspecialchars($oXml->envelope->text);
			}
			ob_start();
			?><h1>Сейчас Вы будете перенаправлены для оплаты на сайт платежной системы...</h1>
			<form action="<?php echo $url?>" name="pay" method="post">
			</form>
			<script>document.pay.submit();</script>
			<?php

			return ob_get_clean();
		}
		else
		{
			return 'Ошибка ответа от cash24';
		}
	}

	/**
	 * @return bool
	 * обработка ответа от платёжной системы
	 */
	public function paymentProcessing()
	{
		$this->ProcessResult();

		return TRUE;
	}

	/**
	 * @return bool
	 * оплачивает заказ
	 */
	function ProcessResult()
	{
		$request=$_POST;
		$amount = $this->_shopOrder->getAmount();

		if ($amount > $request['amount']){
			exit;
		}

		$key = $this->_cash24_command_key;
		$toSign = 'callback';
		$toSign .= '-' . $request['status'];
		$toSign .= '-' . $request['reason'];
		$toSign .= '-' . $request['amount'];
		$toSign .= '-' . $request['currency'];
		$toSign .= '-' . $request['order'];
		$toSign .= '-' . $request['url'];
		$toSign .= '-' . $request['wallet'];
		$toSign .= '-' . $request['method'];
		$toSign .= '-' . $_request['available-for-refund'];
		$toSign .= '-' . $request['refund-made-amount'];
		$toSign .= '-' . $request['salt'];
		$toSign .= '-' . $key;

		if (strtoupper(md5($toSign)) != $request['sign'])
		{
			return -1;
			exit;
		}
		$status = $request['status'];

		if ($request['status']=='20') {
			$this->_shopOrder->system_information = sprintf("Товар оплачен системой Cash24");
			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
			exit;
		}
		if ($request['status']=='40') {
			$this->_shopOrder->system_information = sprintf("Товар оплачен системой Cash24");
			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
			exit;
		}
		if ($request['status']=='30') {
			$this->_shopOrder->system_information = sprintf("Товар не оплачен системой Cash24");
			$this->_shopOrder->save();
			$this->setXSLs();
			$this->send();
			exit;
		}
		if ($request['status']=='50') {
			$this->_shopOrder->system_information = sprintf("Товар возвращен системой Cash24");
			$this->_shopOrder->save();
			$this->setXSLs();
			$this->send();
			exit;
		}
	}
}