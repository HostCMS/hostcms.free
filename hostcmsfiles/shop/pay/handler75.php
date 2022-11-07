<?php

/**
 * Onpay
 */
class Shop_Payment_System_Handler75 extends Shop_Payment_System_Handler
{
	private $_currencyName = "RUB";
	private $_rubleCurrencyId = 1;

	// Секретный ключ. Получается при регистрации в системе Onpay
	private $_secretKey = "0123456789";
	// Идентификатор пользователя. Получается при регистрации в системе Onpay
	private $_userId = "someuser";

	public function __construct(Shop_Payment_System_Model $oShop_Payment_System_Model)
	{
		parent::__construct($oShop_Payment_System_Model);
		$oCurrency = Core_Entity::factory('Shop_Currency')->getByCode('RUB');
		!is_null($oCurrency) && $this->_rubleCurrencyId = $oCurrency->id;
	}

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_REQUEST['type']) && isset($_POST['pay_for']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getPost('pay_for'));

			$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

			if (!is_null($oShop_Order->id))
			{
				header("Content-type: application/xml");

				// Вызов обработчика платежной системы
				Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
					->shopOrder($oShop_Order)
					->paymentProcessing();
				exit();
			}
		}
	}

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
		/* Пришло подтверждение оплаты, обработаем его */
		if (isset($_POST['type']))
		{
			$this->ProcessResult();
			return true;
		}
	}

	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_rubleCurrencyId > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_rubleCurrencyId)
			)
			: 0) * $this->_shopOrder->getAmount());
	}

	public function toFloat($amount)
	{ 
		$amount = round(floatval($amount), 2);
		$amount = sprintf('%01.2f', $amount);

		if (substr($amount, -1) == '0')
		{
			$amount = sprintf('%01.1f', $amount);
		}
		return $amount;
	}
	
	public function getNotification()
	{
		$im_sum = $this->getSumWithCoeff();
		$currency_name = Core_Entity::factory('Shop_Currency', $this->_rubleCurrencyId)->code;
		$destinationUrl = sprintf("http://secure.onpay.ru/pay/%s/", htmlspecialchars($this->_userId));
		$order_id = $this->_shopOrder->id;

		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';
		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://'.$site_alias.$shop_path.'cart/';

		/* Сумма платежа.
		Внимание! При формировании контрольной подписи число содержит не менее 1-го знака после запятой. Т.е. «100» в подписи будет «100.0», а не «100». Внимание! Все суммы в платежках и платежах округляются вниз до 2-ух знаков после запятой. Т.е., число «100.1155» будет округлено до «100.11». */
		$sum_for_md5 = $this->toFloat($im_sum);
		$md5check = md5("fix;{$sum_for_md5};{$currency_name};{$order_id};yes;{$this->_secretKey}");
		$email = $this->_shopOrder->email;

		?>
		<h1>Оплата через систему OnPay</h1>
		<!-- Форма для оплаты через OnPay -->
		<p>К оплате <strong><?php echo $im_sum . " " . $currency_name ?></strong></p>
		<form id="pay" name="pay" method="post" action="<?php echo $destinationUrl ?>">
			<input id="pay_for" type="hidden" name="pay_for" value="<?php echo htmlspecialchars($order_id) ?>">
			<input id="pay_mode" type="hidden" name="pay_mode" value="fix">
			<input id="price" type="hidden" name="price" value="<?php echo htmlspecialchars($im_sum) ?>">
			<input id="currency" type="hidden" name="currency" value="<?php echo htmlspecialchars($currency_name) ?>">
			<input id="successUrl" type="hidden" name="url_success" value="<?php echo htmlspecialchars($handler_url) ?>">
			<input id="convert" type="hidden" name="convert" value="yes">
			<input id="md5" type="hidden" name="md5" value="<?php echo htmlspecialchars($md5check) ?>">
			<input id="Email" type="hidden" name="user_email" value="<?php echo htmlspecialchars($email) ?>">
			<input name="submit" value="Перейти к оплате" type="submit"/>
		</form>
		<?php
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

	function ProcessResult()
	{
		if(!is_null($type = Core_Array::getRequest('type')) && $type == 'check')
		{
			$result = 'Very bad!';
			$order_amount = Core_Array::getRequest('order_amount', '');
			$order_currency = Core_Array::getRequest('order_currency', '');
			$pay_for = Core_Array::getRequest('pay_for');
			$md5 = Core_Array::getRequest('md5');
			$sum = floatval($order_amount);

			if(is_null($this->_shopOrder) || $this->_shopOrder->paid)
			{
				$result = sprintf("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>2</code>\n<pay_for>%s</pay_for>\n<comment>%s</comment>\n<md5>%s</md5>\n</result>", $pay_for, 'Error code pay_for: ' . $pay_for, strtoupper(md5("{$type};{$pay_for};{$order_amount};{$order_currency};2;{$this->_secretKey}")));
			}
			else
			{
				$result = sprintf("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>0</code>\n<pay_for>%s</pay_for>\n<comment>OK</comment>\n<md5>$md5</md5>\n</result>", $pay_for, strtoupper(md5("{$type};{$pay_for};{$order_amount};{$order_currency};0;{$this->_secretKey}")));
			}
		}

		if(!is_null($type = Core_Array::getRequest('type')) && $type == 'pay')
		{
			$result = "Bad pay;";
			$onpay_id = Core_Array::getRequest('onpay_id');
			$pay_for = Core_Array::getRequest('pay_for');
			$order_amount = Core_Array::getRequest('order_amount');
			$order_currency = Core_Array::getRequest('order_currency');
			$balance_amount = Core_Array::getRequest('balance_amount');
			$balance_currency = Core_Array::getRequest('balance_currency');
			$exchange_rate = Core_Array::getRequest('exchange_rate');
			$paymentDateTime = Core_Array::getRequest('paymentDateTime');
			$md5 = Core_Array::getRequest('md5');
			$key = $this->_secretKey;

			$md5fb = strtoupper(md5(sprintf("%s;%s;%s;%s;%s;%s", $type, $pay_for, $onpay_id, $order_amount, $order_currency, $key)));

			if ($md5fb != $md5 || ($order_amount != $this->getSumWithCoeff()))
			{
				$result = sprintf("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>7</code>\n <comment>Md5 signature is wrong or price mismatch</comment>\n<onpay_id>%s</onpay_id>\n <pay_for>%s</pay_for>\n<order_id>%s</order_id>\n<md5>%s</md5>\n</result>", $onpay_id, $pay_for, $pay_for, strtoupper(md5(sprintf("%s;%s;%s;%s;%s;%s;%s;%s", $type, $pay_for, $onpay_id, $pay_for, $order_amount, $order_currency, 7, $key))));
			}
			else
			{
				$result = sprintf("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<result>\n<code>0</code>\n <comment>OK</comment>\n<onpay_id>%s</onpay_id>\n <pay_for>%s</pay_for>\n<order_id>%s</order_id>\n<md5>%s</md5>\n</result>", $onpay_id, $pay_for, $pay_for, strtoupper(md5(sprintf("%s;%s;%s;%s;%s;%s;%s;%s", $type, $pay_for, $onpay_id, $pay_for, $order_amount, $order_currency, 0, $key))));

				$this->_shopOrder->system_information = sprintf("Товар оплачен через OnPay.\n\nИнформация:\n\nНомер покупки (СКО): %s\nСумма платежа: %s\nНомер транзакции в системе OnPay: %s\n\n", $pay_for, $order_amount, $onpay_id);

				$this->_shopOrder->paid();
				$this->setXSLs();
				$this->send();
			}
		}

		echo $result;
	}
}