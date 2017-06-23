<?php

/**
 * IntellectMoney
 */
class Shop_Payment_System_Handler13 extends Shop_Payment_System_Handler
{
	// Идентифкатор магазина, с которым будет работать обработчик
	private $_eShopId = 123456;

	// Секретный ключ (заполняется вручную пользователем на странице настроек магазина)
	private $_secretKey = "secret_key_string";

	// Валюта платежа RUR для работы или TST для тестирования системы
	private $_currencyName = "RUB";
	//private $_currencyName = "TST";

	// Код валюты в магазине HostCMS, которая была указана при регистрации магазина
	private $_intellectmoney_currency = 1;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_REQUEST['orderId']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getRequest('orderId'));

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
		return Shop_Controller::instance()->round(($this->_intellectmoney_currency > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_intellectmoney_currency)
			)
			: 0) * $this->_shopOrder->getAmount() );
	}

	/* обработка ответа от платёжной системы */
	public function paymentProcessing()
	{
			$this->ProcessResult();

			return TRUE;
	}

	/* оплачивает заказ */
	public function ProcessResult()
	{
		if ($this->_shopOrder->paid
			|| is_null($paymentStatus = Core_Array::getPost('paymentStatus'))
			/*|| $paymentStatus != 5*/)
		{
			return FALSE;
		}

		if ($paymentStatus != 5)
		{
			echo "OK";
			die();
		}

		$eshopId = Core_Array::getPost('eshopId');
		$serviceName = Core_Array::getPost('serviceName');
		$eshopAccount = Core_Array::getPost('eshopAccount');
		$recipientAmount = Core_Array::getPost('recipientAmount');
		$recipientCurrency = Core_Array::getPost('recipientCurrency');
		$paymentStatus = Core_Array::getPost('paymentStatus');
		$userName = Core_Array::getPost('userName');
		$userEmail = Core_Array::getPost('userEmail');
		$paymentData = Core_Array::getPost('paymentData');

		$str_md5 = md5(sprintf("%s::%s::%s::%s::%s::%s::%s::%s::%s::%s::%s",
			$eshopId,$this->_shopOrder->id,$serviceName,
			$eshopAccount,$recipientAmount,$recipientCurrency,
			$paymentStatus,$userName,$userEmail,
			$paymentData,$this->_secretKey
		));

		if ($str_md5 == Core_Array::getPost('hash', ''))
		{
			$this->_shopOrder->system_information = sprintf("Товар оплачен через IntellectMoney.\n\nИнформация:\n\nID магазина: %sНомер покупки (СКО): %s\nНомер счета магазина: %s\nНазначение платежа: %s\nСумма платежа: %s\nВалюта платежа: %s\nИмя пользователя: %s\nE-mail пользователя: %s\nВремя выполнения платежа: %s\n",
				$eshopId,Core_Array::getPost('paymentId', ''),$eshopAccount,
				$serviceName,$recipientAmount,$recipientCurrency,
				$userName,$userEmail,$paymentData
			);

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
		}
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$order_sum = $this->getSumWithCoeff();

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_intellectmoney_currency);

		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';
		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://'.$site_alias.$shop_path.'cart/';

		if(!is_null($oShop_Currency->id))
		{
			?>
			<h1>Оплата через систему IntellectMoney</h1>
			<!-- Форма для оплаты через IntellectMoney -->
			<p>К оплате <strong><?php echo $order_sum . " " . $oShop_Currency->name?></strong></p>
			<form id="pay" name="pay" method="post" action="https://merchant.intellectmoney.ru/ru/">
				<input id="orderId" type="hidden" name="orderId" value="<?php echo $this->_shopOrder->id?>">
				<input id="eshopId" type="hidden" name="eshopId" value="<?php echo $this->_eShopId?>">
				<input id="serviceName" type="hidden" name="serviceName" value="Оплата заказа № <?php echo $this->_shopOrder->id?>">
				<input id="recipientAmount" type="hidden" name="recipientAmount" value="<?php echo $order_sum?>">
				<input id="recipientCurrency" type="hidden" name="recipientCurrency" value="<?php echo $this->_currencyName?>">
				<input id="successUrl" type="hidden" name="successUrl" value="<?php echo $handler_url."?orderId={$this->_shopOrder->invoice}&payment=success"?>">
				<input id="failUrl" type="hidden" name="failUrl" value="<?php echo $handler_url."?orderId={$this->_shopOrder->invoice}&payment=fail"?>">
				<input id="userName" type="hidden" name="userName" value="<?php echo implode(' ', array($this->_shopOrder->surname, $this->_shopOrder->name, $this->_shopOrder->patronymic))?>">
				<input id="userEmail" type="hidden" name="userEmail" value="<?php echo $this->_shopOrder->email?>">
				<input name="submit" value="Перейти к оплате" type="submit"/>
			</form>
			<?php
		}
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}