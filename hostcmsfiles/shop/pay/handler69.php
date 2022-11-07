<?php
/**
 * PayPal
 *
 * Для работы вам необходим подтвержденный корпоративный счет: https://www.paypal.com/ru/ru/cgi-bin/webscr/
 * Выберите: Мой счет  - Профиль - Дополнительные функции
 * Доступ к API-интерфейсу - Обновить
 * Вариант 2: запросить учетные данные API для создания своих собственных имени пользователя и пароля API.
 * Запросите подпись API - Подтвердить согласие и отправить
 */
class Shop_Payment_System_Handler69 extends Shop_Payment_System_Handler
{
	/**
	 * Демонстрационный режим TRUE|FALSE
	 */
	private $_SandboxFlag = FALSE;

	/**
	 * Имя пользователя API
	 */
	private $_Api_Username = '';

	/**
	 * Пароль API
	 */
	private $_Api_Password = '';

	/**
	 * Подпись
	 */
	private $_Api_Signature = '';

	/**
	 * Email продавца
	 */
	private $_Email = 'aaa@bbb.com';

	private $_Api_Endpoint = 'https://api-3t.paypal.com/nvp';
	private $_Paypal_Url = 'https://www.paypal.com/cgi-bin/webscr';
	private $_Post_Data = '';

	/**
	 * ID валюты, в которой будут проходить платежи
	 */
	private $_Default_Currency_Id = 1;

	public function __construct(Shop_Payment_System_Model $oShop_Payment_System_Model)
	{
		parent::__construct($oShop_Payment_System_Model);

		// Демонстрационный режим
		if ($this->_SandboxFlag)
		{
			$this->_Api_Endpoint = 'https://api-3t.sandbox.paypal.com/nvp';
			$this->_Paypal_Url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}
	}

	/**
	 * Метод, вызываемый в коде ТДС через Shop_Payment_System_Handler::checkAfterContent($oShop);
	 */
	public function checkPaymentAfterContent()
	{
		if (isset($_REQUEST['payment_type']))
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

	/* вычисление суммы товаров заказа */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_Default_Currency_Id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_Default_Currency_Id)
			)
			: 0) * $this->_shopOrder->getAmount() );
	}

	/* обработка ответа от платежной системы */
	public function paymentProcessing()
	{
		$oShop_Order = $this->_shopOrder;

		if(is_null($oShop_Order->id) || $oShop_Order->paid)
		{
			// Заказ не найден
			return FALSE;
		}

		foreach ($_POST as $key => $value)
		{
			$this->_Post_Data .= $key . "=" . urlencode($value) . "&";
		}

		$this->_Post_Data .= "cmd=_notify-validate";
		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($cURL, CURLOPT_URL, $this->_Paypal_Url);
		curl_setopt($cURL, CURLOPT_ENCODING, 'gzip');
		curl_setopt($cURL, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($cURL, CURLOPT_POST, true); // POST back
		curl_setopt($cURL, CURLOPT_POSTFIELDS, $this->_Post_Data);
		curl_setopt($cURL, CURLOPT_HEADER, false);
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($cURL, CURLOPT_FORBID_REUSE, true);
		curl_setopt($cURL, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($cURL, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($cURL, CURLOPT_TIMEOUT, 60);
		curl_setopt($cURL, CURLINFO_HEADER_OUT, true);
		curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
			'Connection: close',
			'Expect: ',
			'User-Agent: HostCMS',
		));

		$sResponse = curl_exec($cURL);
		curl_close($cURL);

		if ($sResponse == 'VERIFIED')
		{
			if (Core_Array::getRequest('receiver_email') == $this->_Email
			&& Core_Array::getRequest('txn_type') == 'web_accept')
			{
				if ($this->_shopOrder->paid)
				{
					return FALSE;
				}

				$sCurrentCurrencyCode = $this->_getCurrencyCode(Core_Entity::factory('Shop_Currency', $this->_Default_Currency_Id));

				if (Core_Array::getRequest('mc_gross') == $this->_shopOrder->getAmount()
				&& Core_Array::getRequest('mc_currency') == $sCurrentCurrencyCode)
				{
					$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

					$oShop_Order->system_information = sprintf("Товар оплачен через PayPal.\nАтрибуты:\nTransaction ID: %s\nAmount: %s %s", Core_Array::getRequest('txn_id'), Core_Array::getRequest('mc_gross'), $sCurrentCurrencyCode);

					$oShop_Order->paid();

					ob_start();
					$this->changedOrder('changeStatusPaid');
					ob_get_clean();
				}
				else
				{
					$this->_shopOrder->system_information = sprintf("Ошибка оплаты через PayPal.\nАтрибуты:\nCurrency: %s\nTransaction ID: %s\nAmount: %s %s", Core_Array::getRequest('mc_currency'), Core_Array::getRequest('txn_id'), Core_Array::getRequest('mc_gross'), $sCurrentCurrencyCode);

					$this->_shopOrder->save();
				}

				return TRUE;
			}
			else
			{
				$this->_shopOrder->system_information = sprintf("Ошибка оплаты через PayPal. receiver_email: %s\ntxn_type: %s", Core_Array::getRequest('receiver_email'), Core_Array::getRequest('txn_type'));
				$this->_shopOrder->save();
			}
		}
		else
		{
			$this->_shopOrder->system_information = sprintf("Ошибка оплаты через PayPal. Ответ: %s", $sResponse);
			$this->_shopOrder->save();
		}
	}

	/**
	 * Корректировка валюты
	 */
	protected function _getCurrencyCode($oShop_Currency)
	{
		$code = $oShop_Currency->code;
		$code == 'RUR' && $code = 'RUB';

		return $code;
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';
		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://' . $site_alias . $shop_path . 'cart/';

		$default_sum = $this->getSumWithCoeff();

		$oShop_Currency = Core_Entity::factory('Shop_Currency', $this->_Default_Currency_Id);

		?>
		<h1>Оплата через систему PayPal</h1>

		<!-- Форма для оплаты -->
		<form id="pay" name="pay" method="post" action="<?php echo $this->_Paypal_Url?>">
			<!-- Обязательный параметр. Должен иметь значение "_xclick" -->
			<input name="cmd" type="hidden" value="_xclick" />
			<input name="rm" type="hidden" value="2" />
			<!-- Обязательный параметр. E-mail продавца -->
			<input name="business" type="hidden" value="<?php echo $this->_Email?>">
			<!-- Наименование товара, которое будет показано покупателю -->
			<input type="hidden" name="item_name" value="Order N <?php echo $this->_shopOrder->invoice?>">
			<input type="hidden" name="invoice" value="<?php echo $this->_shopOrder->id?>">
			<!-- Сумма к оплате -->
			<input type="hidden" name="amount" value="<?php echo $default_sum?>">
			<input type="hidden" name="item_number" value="1" />
			<!-- Код валюты -->
			<input type="hidden" name="currency_code" value="<?php echo htmlspecialchars($this->_getCurrencyCode($oShop_Currency))?>" />
			<!-- Ссылка возврата -->
			<input type="hidden" name="notify_url" value="<?php echo $handler_url . "?payment=success&order_id=" . $this->_shopOrder->id?>" />
			<input type="hidden" name="return" value="<?php echo $handler_url . "?payment=success&order_id=" . $this->_shopOrder->id?>" />
			<!-- Для определения платежной системы на странице корзины -->
			<input type="hidden" name="order_id" value="<?php echo $this->_shopOrder->id?>">

			<p>
				Сумма к оплате: <strong><?php echo $default_sum . ' ' . htmlspecialchars($oShop_Currency->name)?></strong>
			</p>

			<p>
				<input style="border: 0" type="image" name="submit" src="https://www.paypal.com/ru_RU/i/btn/btn_xpressCheckout.gif" />
			</p>
		</form>
		<?php
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}