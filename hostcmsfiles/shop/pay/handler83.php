<?php

require_once CMS_FOLDER . 'modules/vendor/Hmac.php';

/**
 * ВТБ
 */
class Shop_Payment_System_Handler83 extends Shop_Payment_System_Handler
{
	// Адрес платежной страницы
	protected $_url = 'https://vtb.rbsuat.com/payment/rest/';

	// Логин учетной записи API продавца.
	protected $_userName = 'logointeres-api';

	// Пароль учетной записи API продавца.
	protected $_password = 'logointeres';

	// Токен
	protected $_token = '';

	// Callback token
	protected $_callback_token = 'l3gh1eucuub9qqqaumh4dgd5st';

	/*
	Признак способа расчёта.
	Возможные значения параметра:
		1 - полная предварительная оплата до момента передачи предмета расчёта;
		2 - частичная предварительная оплата до момента передачи
			предмета расчёта;
		3 - аванс;
		4 - полная оплата в момент передачи предмета расчёта;
		5 - частичная оплата предмета расчёта в момент его передачи
			с последующей оплатой в кредит;
		6 - передача предмета расчёта без его оплаты в момент
			его передачи с последующей оплатой в кредит;
		7 - оплата предмета расчёта после его передачи с оплатой в кредит.
	*/
	protected $payment_method = 4;

	/*
	Признак предмета расчёта.
	Возможные значения параметра:
		1 - товар;
		2 - подакцизный товар;
		3 - работа;
		4 - услуга;
		5 - ставка азартной игры;
		6 - выигрыш азартной игры;
		7 - лотерейный билет;
		8 - выигрыш лотереи;
		9 - предоставление РИД;
		10 - платёж;
		11 - агентское вознаграждение;
		12 - составной предмет расчёта;
		13 - иной предмет расчёта.
	*/
	protected $payment_object = 1;

	/*
	ставка НДС, с возможными значениями (при необходимоти заменить):
		0 – без НДС;
		1 – НДС по ставке 0%;
		2 – НДС чека по ставке 10%;
		3 – НДС чека по ставке 18%;
		4 – НДС чека по расчетной ставке 10/110;
		5 – НДС чека по расчетной ставке 18/118.
		6 - НДС чека по ставке 20%;
		7 - НДС чека по расчётной ставке 20/120.
	*/
	protected $taxType = array(
		0 => 0,
		10 => 2,
		18 => 3,
		20 => 6
	);

	protected $default_vat = 6;

	/**
	 * Идентификатор валюты, в которой будет производиться платеж.
	 * Сумма к оплате будет пересчитана из валюты магазина в указанную валюту
	 */
	protected $_vtb_currency_id = 1;

	/**
	 * Определяем коэффициент перерасчета цены
	 */
	protected $_coefficient = 1;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('VTB GET: ' . var_export($_GET, true));

		// Получаем ID заказа
		$order_id = Core_Array::getRequest('orderNumber', 0, 'int');

        if ($order_id)
        {
    		$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

    		if (!is_null($oShop_Order->id))
    		{
				$checksum = Core_Array::getRequest('checksum', '', 'trim');

				if ($checksum != '')
				{
					$aData = array();

					ksort($_GET);

					foreach ($_GET as $key => $value)
					{
						if ($key == 'checksum')
						{
							continue;
						}

						$aData[] = $key;
						$aData[] = $value;
					}

					$data = implode(';', $aData) . ';';

					$hmac = hash_hmac('sha256', $data, $this->_callback_token);

					if (strtoupper($hmac) == $checksum)
					{
						// Вызов обработчика платежной системы
						Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
							->shopOrder($oShop_Order)
							->paymentProcessing();

						http_response_code(200);
						echo 'success';
					}
					else
					{
						$oShop_Order->system_information = 'ВТБ хэш не совпал!';
						$oShop_Order->save();

						http_response_code(403);
						echo "bad sign\n";
					}
				}
    		}

    		exit();
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

	/**
	 * Вычисление суммы товаров заказа
	 */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_vtb_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_vtb_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount() * $this->_coefficient);
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

	public function getNotification()
	{
		$oShop_Order = $this->_shopOrder;

		Core_Session::start();

		if (!isset($_SESSION["vtb_{$oShop_Order->id}"]['formUrl']))
		{
			$sum = $this->getSumWithCoeff() * 100;

			$oSite_Alias = $oShop_Order->Shop->Site->getCurrentAlias();
			$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';

			$shop_path = $oShop_Order->Shop->Structure->getPath();
			$handler_url = 'https://' . $site_alias . $shop_path . "cart/?order_id={$oShop_Order->id}";

			$aData = array(
				'amount' => $sum,
				'orderNumber' => $oShop_Order->id,
				'email' => $oShop_Order->email,
				'language' => 'ru',
				'returnUrl' => $handler_url,
				'failUrl' => $handler_url
			);

			if ($this->_token !== '')
			{
				$aData['token'] = $this->_token;
			}
			else
			{
				$aData['userName'] = $this->_userName;
				$aData['password'] = $this->_password;
			}

			try {
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('POST')
					->timeout(10)
					->url($this->_url . 'register.do')
					->additionalHeader('Content-type', 'application/x-www-form-urlencoded');

				foreach ($aData as $param => $value)
				{
					$Core_Http->data($param, $value);
				}

				$Core_Http->execute();

				$aAnswer = json_decode($Core_Http->getDecompressedBody(), TRUE);

				if (isset($aAnswer['formUrl']) && $aAnswer['formUrl'] !== '')
				{
					$_SESSION["vtb_{$oShop_Order->id}"]['formUrl'] = $aAnswer['formUrl'];
				}
			}
			catch (Exception $e) {
			}
		}

		ob_start();

		if (isset($_SESSION["vtb_{$oShop_Order->id}"]['formUrl']))
		{
			?>
			<p>Сумма к оплате составляет <strong><?php echo $this->_shopOrder->sum()?></strong></p>

			<p>Для оплаты нажмите кнопку "Оплатить".</p>

			<!-- <form action="<?php echo htmlspecialchars($_SESSION["vtb_{$oShop_Order->id}"]['formUrl'])?>" method="GET">
				<input type="submit" value="Оплатить" class="btn btn-primary">
			</form> -->

			<button type="button" onclick="window.location = '<?php echo htmlspecialchars($_SESSION["vtb_{$oShop_Order->id}"]['formUrl'])?>'" class="btn btn-primary">Оплатить</button>
			<?php
		}
		else
		{
			?>
			<h2>Ошибка регистрации заказа!</h2>
			<?php
		}

		return ob_get_clean();
	}

	/**
	 * Обработка ответа платёжного сервиса
	 */
	public function paymentProcessing()
	{
	    $this->processResult();

	    return true;
	}

	public function ProcessResult()
	{
		if ($this->_shopOrder->paid)
		{
			return FALSE;
		}

		$this->_shopOrder->system_information = sprintf("Товар оплачен через ВТБ.\n\n");
		$this->_shopOrder->paid();
		$this->setXSLs();
		$this->send();
	}
}