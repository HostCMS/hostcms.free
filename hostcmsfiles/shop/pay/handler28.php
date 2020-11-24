<?php

/**
 * WEBPAY
 */
class Shop_Payment_System_Handler28 extends Shop_Payment_System_Handler
{
	/*
	 * Идентификатор магазина в системе WEBPAY
	 */
	protected $_wsb_storeid = 12345678;

	/*
	 * Секретный ключ магазина в системе WEBPAY. Сгенерируйте сложный символьный ключ.
	 * Это поле может содержать случайную последовательность символов за исключение знака &
	 */
	protected $_secret_key = 'webpaySecretKey';

	/*
	 * Идентификатор языка формы оплаты
	 * Допустимые значения: 'russian', 'english'
	 * При отсутствии значения определяется по настройкам браузера.
	 */
	protected $_wsb_language_id = NULL;

	/*
	 * Идентификатор валюты. Буквенный трехзначный код валюты согласно ISO4271.
	 * Допустимые значения: BYN, USD, EUR, RUB
	 * Для тестовой среды только BYN
	 */
	protected $_wsb_currency_name = 'RUB';

	/*
	 * Код валюты в магазине HostCMS, которая была указана при регистрации магазина
	 */
	private $_wsb_currency_id = 1;

	/*
	 * Режим работы, 1 - Тестовый, 0 - Рабочий
	 */
	protected $_wsb_test = 1;

	/*
	 * Базовый URL сервиса для тестирования
	 */
	protected $_base_test_uri = 'https://securesandbox.webpay.by';

	/*
	 * Базовый URL сервиса
	 */
	protected $_base_uri = 'https://payment.webpay.by';

	/*
	 * Версия формы оплаты
	 */
	protected $_wsb_version = 2;

	/**
	 * Определяем коэффициент перерасчета цены
	 */
	protected $_coefficient = 1;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_REQUEST['site_order_id']) && isset($_REQUEST['transaction_id']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getRequest('site_order_id'));

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
		return $this->_wsb_test
			? $this->_base_test_uri
			: $this->_base_uri;
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
		return Shop_Controller::instance()->round(($this->_wsb_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_wsb_currency_id)
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

		if (!isset($_POST['wsb_signature']))
		{
			return FALSE;
		}

		$webpay_signature = $_POST['wsb_signature'];

		$our_signature = md5($_POST['batch_timestamp'] . $_POST['currency_id'] . $_POST['amount'] . $_POST['payment_method'] . $_POST['order_id'] . $_POST['site_order_id'] . $_POST['transaction_id'] . $_POST['payment_type'] . $_POST['rrn'] . $this->_secret_key);

		// payment_type - тип транзакции. Успешной оплате соответствуют значения: 1 и 4
		if ($our_signature == $webpay_signature && in_array($_POST['payment_type'], array(1, 4)))
		{
			header('HTTP/1.0 200 OK');
			$this->_shopOrder->system_information = sprintf("Заказ оплачен через WEBPAY\nID платежа в системе WEBPAY:\t{$_POST['transaction_id']}\nСтатус платежа:\t{$_POST['payment_type']}\n\n");

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
		}
		else
		{
			header('HTTP/1.0 400 Bad request');
			$this->_shopOrder->system_information = sprintf("Заказ НЕ оплачен через WEBPAY\nID платежа в системе WEBPAY:\t{$_POST['transaction_id']}\nСтатус платежа:\t{$_POST['payment_type']}\n\n");
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

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_wsb_currency_id);

		$wsb_seed = time();

		if (!is_null($oShop_Currency->id))
		{
			?>
			<h1>Оплата через систему WEBPAY</h1>
			<p>К оплате <strong><?php echo $sum . " " . htmlspecialchars($oShop_Currency->name)?></strong></p>
			<!-- Форма для оплаты через WEBPAY -->
			<form action="<?php echo $this->_getBaseUrl()?>" method="post">
				<input type="hidden" name="*scart">
				<input type="hidden" name="wsb_version" value="<?php echo $this->_wsb_version?>">
				<?php
				if (!is_null($this->_wsb_language_id))
				{
					?><input type="hidden" name="wsb_language_id" value="<?php echo htmlspecialchars($this->_wsb_language_id)?>"><?php
				}
				?>
				<input type="hidden" name="wsb_storeid" value="<?php echo $this->_wsb_storeid?>">
				<input type="hidden" name="wsb_store" value="<?php echo htmlspecialchars(mb_substr($oShop_Order->Shop->name, 0, 64))?>">
				<input type="hidden" name="wsb_order_num" value="<?php echo $oShop_Order->id?>">
				<?php
				if ($this->_wsb_test)
				{
					?><input type="hidden" name="wsb_test" value="1"><?php
				}
				?>
				<input type="hidden" name="wsb_currency_id" value="<?php echo htmlspecialchars($this->_wsb_currency_name)?>">
				<input type="hidden" name="wsb_seed" value="<?php echo $wsb_seed?>">
				<input type="hidden" name="wsb_customer_name" value="<?php echo htmlspecialchars(implode(' ', array($oShop_Order->surname, $oShop_Order->name, $oShop_Order->patronymic)))?>">
				<input type="hidden" name="wsb_customer_address" value="<?php echo htmlspecialchars($oShop_Order->getFullAddress())?>">
				<input type="hidden" name="wsb_order_contract" value="Заказ №<?php echo htmlspecialchars($oShop_Order->invoice)?>">
				<input type="hidden" name="wsb_email" value="<?php echo htmlspecialchars($oShop_Order->email)?>">
				<?php
				$wsb_total = 0;

				$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll(FALSE);

				// Расчет сумм скидок, чтобы потом вычесть из цены каждого товара
				$discount = $amount = 0;
				foreach ($aShop_Order_Items as $key => $oShop_Order_Item)
				{
					if ($oShop_Order_Item->price < 0)
					{
						$discount -= $oShop_Order_Item->getAmount();
						unset($aShop_Order_Items[$key]);
					}
					elseif ($oShop_Order_Item->shop_item_id)
					{
						$amount += $oShop_Order_Item->getAmount();
					}
				}

				$discount = $amount != 0
					? abs($discount) / $amount
					: 0;

				foreach ($aShop_Order_Items as $key => $oShop_Order_Item)
				{
					$price = number_format($oShop_Order_Item->getAmount() * ($oShop_Order_Item->shop_item_id ? 1 - $discount : 1), 2, '.', '');
					?>
					<input type="hidden" name="wsb_invoice_item_name[<?php echo $key?>]" value="<?php echo htmlspecialchars($oShop_Order_Item->name)?>">
					<input type="hidden" name="wsb_invoice_item_quantity[<?php echo $key?>]" value="<?php echo $oShop_Order_Item->quantity?>">
					<input type="hidden" name="wsb_invoice_item_price[<?php echo $key?>]" value="<?php echo $price?>">
					<?php

					$wsb_total += $price * $oShop_Order_Item->quantity;
				}

				$wsb_signature = sha1($wsb_seed . $this->_wsb_storeid . $oShop_Order->id . $this->_wsb_test . $this->_wsb_currency_name . $wsb_total . $this->_secret_key);
				?>
				<input type="hidden" name="wsb_total" value="<?php echo $wsb_total?>">
				<input type="hidden" name="wsb_signature" value="<?php echo $wsb_signature?>">

				<input name="submit" value="Перейти к оплате" type="submit"/>
			</form>
			<?php
		}
		else
		{
			?><h1>Валюта не найдена</h1><?php
		}
	}
}