<?php
/**
 * Payler
 */
class Shop_Payment_System_Handler29 extends Shop_Payment_System_Handler
{
	/*
	 * Платежный ключ в системе Payler
	 */
	protected $_payler_key = 'a16fea31-b463-48ed-8440-3f683c9490fc';

	/*
	 * Платежный пароль в системе Payler
	 */
	protected $_payler_password = 'Y34yEz9ttW';

	/*
	 * Тип транзакции. Определяет количество стадий платежа.
	 */
	protected $_payler_payment_type = 'OneStep';

	/*
	 * Идентификатор валюты. Буквенный трехзначный код валюты согласно ISO4271.
	 * Допустимые значения: USD, EUR, RUB
	 */
	protected $_payler_currency_name = 'RUB';

	/*
	 * Код валюты в магазине HostCMS, которая была указана при регистрации магазина
	 */
	protected $_currency_id = 1;

	/**
	 * Определяем коэффициент перерасчета цены
	 */
	protected $_coefficient = 1;

	/*
	 * Режим работы, 1 - Тестовый, 0 - Рабочий
	 */
	protected $_payler_test = 1;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_REQUEST['order_id']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getRequest('order_id'));

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
	 * Базовый адрес API
	 */
	protected function _getBaseUrl()
	{
		return 'https://' . ($this->_payler_test ? 'sandbox' : 'secure') . '.payler.com/gapi/';
	}

    /**
     * Call API
     * @return array
     */
    protected function _sendRequest($method, $aParams = array())
    {
		$headers = array(
			'Content-type: application/x-www-form-urlencoded',
			'Cache-Control: no-cache',
			'charset="utf-8"',
		);

        $url = $this->_getBaseUrl() . $method;
        $data  = http_build_query($aParams, '', '&');

		$options = array (
			CURLOPT_URL => $url,
			CURLOPT_POST => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => 45,
			CURLOPT_VERBOSE => 0,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => 0,
		);

		$ch = curl_init();
		curl_setopt_array($ch, $options);
		$json = curl_exec($ch);
		curl_close($ch);
		if ($json == FALSE)
		{
			die('Curl error: ' . curl_error($ch) . '<br>');
		}

		$result = @json_decode($json, TRUE);

		return $result;
    }

	/*
	 * Метод, запускающий выполнение обработчика
	 */
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

	/**
	 * Вычисление суммы товаров заказа
	 */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount() * $this->_coefficient);
	}

	/*
	 * Обработка ответа от платёжной системы
	 */
	public function paymentProcessing()
	{
		if ($this->_shopOrder->paid)
		{
			return FALSE;
		}

		$data = array (
			'key' => $this->_payler_key,
			'order_id' => $this->_shopOrder->id
		);

		$aReturn = $this->_sendRequest('GetStatus', $data);

		if (isset($aReturn['status']) && $aReturn['status'] == 'Charged')
		{
			header('HTTP/1.0 200 OK');
			$this->_shopOrder->system_information = sprintf("Заказ оплачен через Payler\nСтатус платежа:\t{$aReturn['status']}\n\n");

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
		}
		else
		{
			header('HTTP/1.0 400 Bad request');
			$this->_shopOrder->system_information = sprintf("Заказ НЕ оплачен через Payler\nДанные:{$aReturn}");
			$this->_shopOrder->save();
		}

		return TRUE;
	}

	/**
	 * Печатает форму отправки запроса на сайт платёжной системы
	 */
	public function getNotification()
	{
		$oShop_Order = $this->_shopOrder;

		$sum = $this->getSumWithCoeff();

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_currency_id);

		if (!is_null($oShop_Currency->id))
		{
			$product = 'Оплата заказа №' . $oShop_Order->invoice;
			$data = array (
				'key' => $this->_payler_key,
				'type' => $this->_payler_payment_type,
				'order_id' => $oShop_Order->id,
				'currency' => $this->_payler_currency_name,
				'amount' => $sum * 100, // сумма в копейках
				'product' => $product,
				'email' => $oShop_Order->email
			);

			$aReturn = $this->_sendRequest('StartSession', $data);

			if (isset($aReturn['session_id']))
			{
				?>
				<h1>Оплата через систему Payler</h1>
				<p>К оплате <strong><?php echo $sum . " " . htmlspecialchars($oShop_Currency->name)?></strong></p>
				<form action="<?php echo $this->_getBaseUrl() . 'Pay'?>" name="payler_form_redirect" method="POST">
					<input type="hidden" name="session_id" value="<?php echo $aReturn['session_id']?>" />
					<input type="submit" name="submit" value="Оплатить заказ"/>
				</form>
				<?php
			}
			elseif (isset($aReturn['error']['message']))
			{
				?><h1>Ошибка, <?php echo $aReturn['error']['message']?></h1><?php
			}
			else
			{
				?><h1>Неизвестная ошибка</h1><?php
			}
		}
		else
		{
			?><h1>Валюта не найдена</h1><?php
		}
	}
}