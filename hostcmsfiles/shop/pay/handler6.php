<?php

/**
 * ASSIST
 *
 * Для автоматического уведомления системы о прохождении платежей в интерфейсе https://account.paysecure.ru
 * выберите "Настройки мерчантов", "Настройка отправки результатов платежей".
 * Установите флаг "Отправка результатов оплат" и "Отправлять только успешные".
 *
 * В "URL для отправки результатов" укажите http://www.site.ru/shop/cart/, заменим www.site.ru на адрес вашего сайта. Тип протокола: POST. Тип подписи: MD5.
 */
class Shop_Payment_System_Handler6 extends Shop_Payment_System_Handler
{
	/* Идентификатор сайта (Merchant_ID) в системе ASSIST, например, 123456 */
	private $_Merchant_ID = '123456';

	/* Секретное слово
	Секретное слово Вы придумываете самостоятельно и указываете его в личном кабинете
	(Настройки мерчантов, вкладка "Настройка отправки результатов платежей").
	Чтобы поле стало доступным для редактирования нужно отметить чекбокс "Отправка результатов оплат".
	Если сам сервис отправки результатов авторизации на сервер предприятия Вам не нужен,
	то после ввода секретного слова чекбокс нужно деактивировать, а затем нажать на кнопку "Сохранить" в правом нижнем углу. */
	private $_Secret_Word = 'xxx';

	/* Идентификатор валюты.
	Указывается ID валюты рубли (RUB)
	*/
	private $_assist_currency_id = 1;

	/*
	Использовать оплату кредитными картами
	1 - да, использовать;
	0 - не использовать, только Webmoney и Яндекс.Деньги.
	*/
	private $_use_credit_card = 1;

	/*
	Режим работы магазина:
	1 - тестовый режим;
	0 - боевой режим (не забудьте изменить режим магазина в системе ASSIST).
	*/
	private $_test_mode = 1;

	public function __construct(Shop_Payment_System_Model $oShop_Payment_System_Model)
	{
		parent::__construct($oShop_Payment_System_Model);

		if (!$this->_assist_currency_id)
		{
			$oCurrency = Core_Entity::factory('Shop_Currency')->getByCode('RUB');
			!is_null($oCurrency) && $this->_assist_currency_id = $oCurrency->id;
		}
	}

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_REQUEST['merchant_id'])
			&& isset($_REQUEST['responsecode'])
			&& $_REQUEST['responsecode'] == 'AS000'
		)
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getRequest('ordernumber'));

			$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

			if (!is_null($oShop_Order->id))
			{
				// Вызов обработчика платежной системы
				Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
					->shopOrder($oShop_Order)
					->paymentProcessing();
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

	public function paymentProcessing()
	{
		$this->ProcessResult();
		return TRUE;
	}

	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_assist_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_assist_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount());
	}

	public function getNotification()
	{
		$sum = $this->getSumWithCoeff();
		$oThisCurrency = Core_Entity::factory('Shop_Currency', $this->_assist_currency_id);

		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';

		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://' . $site_alias . $shop_path . 'cart/';

		// Определяем город
		$oCity = Core_Entity::factory('Shop_Country_Location_City', $this->_shopOrder->shop_country_location_city_id);

		$OrderNumber = $this->_shopOrder->id;

		$sCheckValue = strtoupper(
			md5(strtoupper(md5($this->_Secret_Word) . md5("{$this->_Merchant_ID}{$OrderNumber}{$sum}RUB")))
		);

		?>
		<h1>Оплата через систему ASSIST</h1>

		<p>
		<a href="http://www.assist.ru" target="_blank">
		<img src="http://www.assist.ru/images/assist_logo.gif" width="155" height="49" border="0" alt="Система электронных платежей">
		</a>
		</p>

		<p>Сумма к оплате составляет <strong><?php echo $sum?> <?php echo $oThisCurrency->name?></strong></p>

		<p>Для оплаты нажмите кнопку "Оплатить".</p>

		<p style="color: rgb(112, 112, 112);">
		Внимание! Нажимая &laquo;Оплатить&raquo; Вы подтверждаете передачу контактных данных на сервер ASSIST.RU для оплаты.
		</p>

		<form name="form1" action="https://payments.paysecure.ru/pay/order.cfm" method="POST">
		<input type="hidden" name="Merchant_ID" value="<?php echo $this->_Merchant_ID?>">
		<input type="hidden" name="OrderNumber" value="<?php echo $OrderNumber?>">
		<input type="hidden" name="TestMode" value="<?php echo $this->_test_mode ?>">
		<input type="hidden" name="OrderAmount" value="<?php echo $sum?>">
		<input type="hidden" name="OrderCurrency" value="RUB">
		<input type="hidden" name="Language" value="RU">
		<input type="hidden" name="OrderComment" value="Оплата счета N <?php echo $this->_shopOrder->invoice?>">
		<input type="hidden" name="Delay" value="0">
		<input type="hidden" name="CheckValue" value="<?php echo $sCheckValue?>">

		<input type="hidden" name="URL_RETURN" value="<?php echo $handler_url?>">

		<!-- Производить оплату по кредитной карте -->
		<input type="hidden" type="text" name="CardPayment" value="<?php echo $this->_use_credit_card ?>">

		<!-- оплата кредитной картой с использованием -->
		<input type="hidden" type="text" name="AssistIDPayment" value="<?php echo $this->_use_credit_card ?>">

		<!-- Использовать платежную систему WebMoney Transfer -->
		<input type="hidden" type="text" name="WMPayment" value="1">

		<!-- Использовать платежную систему YandexMoney -->
		<input type="hidden" type="text" name="YMPayment" value="1">

		<!-- Использовать платежную систему QIWI -->
		<input type="hidden" type="text" name="QIWIPayment" value="1">

		<!-- Оплата с помощью средств на счету мобильного телефона (оператор МТС) -->
		<input type="hidden" type="text" name="QIWIMtsPayment" value="1">

		<!-- Оплата с помощью средств на счету мобильного телефона (оператор Мегафон) -->
		<input type="hidden" type="text" name="QIWIMegafonPayment" value="1">

		<!-- Оплата с помощью средств на счету мобильного телефона (оператор БиЛайн) -->
		<input type="hidden" type="text" name="QIWIBeelinePayment" value="1">

		<input type="hidden" name="Lastname" value="<?php echo htmlspecialchars(
			strlen($this->_shopOrder->surname)
				? $this->_shopOrder->surname
				: 'Иванов'
			)?>">
		<input type="hidden" name="Firstname" value="<?php echo htmlspecialchars(
			strlen($this->_shopOrder->name)
				? $this->_shopOrder->name
				: 'Иван'
			)?>">
		<input type="hidden" name="Middlename" value="<?php echo htmlspecialchars($this->_shopOrder->patronymic)?>">

		<input type="hidden" name="Email" value="<?php echo htmlspecialchars($this->_shopOrder->email)?>">
		<input type="hidden" name="Address" value="<?php echo htmlspecialchars($oCity->name)?>, <?php echo htmlspecialchars($this->_shopOrder->address)?>">
		<input type="hidden" name="WorkPhone" value="<?php echo htmlspecialchars($this->_shopOrder->phone)?>">

		<input type="hidden" name="City" value="<?php echo htmlspecialchars($oCity->name)?>">
		<input type="hidden" name="Zip" value="<?php echo htmlspecialchars($this->_shopOrder->postcode)?>">

		<input type="submit" name="Submit" value="Оплатить <?php echo $sum?> <?php echo $oThisCurrency->name?>">
		</form>
		<?php
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

	// Вывод сообщения об успешности/неуспешности оплаты
	public function ShowResultMessage()
	{
		return;
	}

	/**
	 * Обработка статуса оплаты
	 */
	public function ProcessResult()
	{
		$oShop_Order = $this->_shopOrder;

		if (is_null($oShop_Order->id) || $oShop_Order->paid)
		{
			// Заказ не найден
			return FALSE;
		}

		$sAssistHash = Core_Array::getRequest('checkvalue', '');
		$sAssistSum = Core_Array::getRequest('amount', '');

		$sHostcmsSum = $this->getSumWithCoeff();

		if ($sAssistSum == $sHostcmsSum)
		{
			$OrderNumber = $oShop_Order->id;

			$sHostcmsHash = strtoupper(
				md5(strtoupper(md5($this->_Secret_Word) . md5("{$this->_Merchant_ID}{$OrderNumber}{$sAssistSum}RUBApproved")))
			);

			// Сравниваем хэши
			if ($sHostcmsHash == strtoupper($sAssistHash))
			{
				$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

				$oShop_Order->system_information = "Товар оплачен через ASSIST.\n";
				$oShop_Order->paid();
				//$this->setXSLs();
				//$this->send();

				echo '<?xml version="1.0" encoding="UTF-8"?>
				<pushpaymentresult firstcode="0" secondcode="0">
					<order>
						<billnumber>' . intval(Core_Array::getRequest('billnumber')) . '</billnumber>
						<packetdate>' . gmdate('D, d M Y H:i:s', time()) . ' GMT' . '</packetdate>
					</order>
				</pushpaymentresult>';

				ob_start();
				$this->changedOrder('changeStatusPaid');
				ob_get_clean();
			}
			else
			{
				$oShop_Order->system_information = 'ASSIST хэш не совпал!' . "\n" .
					'ordernumber: ' . Core_Array::getRequest('ordernumber', '') . "\n" .
					'orderstate: ' . Core_Array::getRequest('orderstate', '') . "\n" .
					'amount: ' . Core_Array::getRequest('amount', '') . "\n" .
					'currency: ' . Core_Array::getRequest('currency', '') . "\n";
				$oShop_Order->save();
			}
		}
		else
		{
			$oShop_Order->system_information = 'ASSIST сумма не совпала!';
			$oShop_Order->save();
		}
	}
}