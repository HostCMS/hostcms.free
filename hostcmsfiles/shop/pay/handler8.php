<?php

/**
 * RBK Money
 */
class Shop_Payment_System_Handler8 extends Shop_Payment_System_Handler
{
	/* Идентификатор сайта в системе RBK Money, например 12345 */
	private $_rbkmoney_id = '12345';

	/* Идентификатор валюты, в которой будет производиться платеж.
	 Сумма к оплате будет пересчитана из валюты магазина в указанную валюту */
	private $_rbkmoney_currency_id = 1;

	/* Секретные ключи для кошельков, должны совпадать с настройками сайта на www.rbkmoney.ru */
	private $_rbkmoney_secret_key = 'hostmake';

	/* Определяем коэффициент перерасчета цены */
	private $_coefficient = 1;

	/**
	 * Метод, вызываемый в коде ТДС через Shop_Payment_System_Handler::checkAfterContent($oShop);
	 */
	public function checkPaymentAfterContent()
	{
		if (isset($_POST['paymentStatus']) && !isset($_REQUEST['paymentType']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getPost('orderId'));

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
		return Shop_Controller::instance()->round(($this->_rbkmoney_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_rbkmoney_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount() * $this->_coefficient);
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
		if(is_null($eshopId = Core_Array::getPost('eshopId'))
			|| $eshopId != $this->_rbkmoney_id
			|| $this->_shopOrder->paid
			|| is_null($paymentStatus = Core_Array::getPost('paymentStatus'))
			|| $paymentStatus != 5)
		{
			return FALSE;
		}

		$serviceName = Core_Array::getPost('serviceName');
		$eshopAccount = Core_Array::getPost('eshopAccount');
		$recipientAmount = Core_Array::getPost('recipientAmount');
		$recipientCurrency = Core_Array::getPost('recipientCurrency');
		$userName = Core_Array::getPost('userName');
		$userEmail = Core_Array::getPost('userEmail');
		$paymentData = Core_Array::getPost('paymentData');

		$str_md5 = md5(sprintf("%s::%s::%s::%s::%s::%s::%s::%s::%s::%s::%s",
			$eshopId, $this->_shopOrder->id, $serviceName, $eshopAccount,
			$recipientAmount, $recipientCurrency, $paymentStatus, $userName,
			$userEmail, $paymentData, $this->_rbkmoney_secret_key));

		if ($str_md5 == Core_Array::getPost('hash', ''))
		{
			$this->shopOrderBeforeAction(clone $this->_shopOrder);

			$this->_shopOrder->system_information = sprintf("Товар оплачен через RBK Money.\nАтрибуты:\nНомер сайта продавца: %s\nВнутренний номер покупки продавца: %s\nСумма платежа: %s\nВалюта платежа: %s\nНомер счета в системе RBK Money: %s\nДата и время выполнения платежа: %s\nСтатус платежа: 5 - Платеж зачислен\n",
				$eshopId, $this->_shopOrder->id, $recipientAmount,
				$recipientCurrency, $eshopAccount, $paymentData);

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();

			ob_start();
			$this->changedOrder('changeStatusPaid');
			ob_get_clean();
		}
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$sum = $this->getSumWithCoeff();

		// RBK Money needs comma separator
		$sum = number_format($sum, 2, ',', '');

		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';

		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://'.$site_alias.$shop_path . "cart/?order_id={$this->_shopOrder->id}";

		$successUrl = $handler_url . "&payment=success";
		$failUrl = $handler_url . "&payment=fail";

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_rbkmoney_currency_id);

		if(!is_null($oShop_Currency->id))
		{
			$serviceName = 'Оплата счета N ' . $this->_shopOrder->id;

			?>
			<h1>Оплата через систему RBK Money</h1>
			<p>Сумма к оплате составляет <strong><?php echo $sum?> <?php echo $oShop_Currency->name?></strong></p>

			<p>Для оплаты нажмите кнопку "Оплатить".</p>

			<form action="https://rbkmoney.ru/acceptpurchase.aspx" name="pay" method="post">
				<input type="hidden" name="eshopId" value="<?php echo $this->_rbkmoney_id?>">
				<input type="hidden" name="orderId" value="<?php echo $this->_shopOrder->id?>">
				<input type="hidden" name="serviceName" value="<?php echo $serviceName?>">
				<input type="hidden" name="recipientAmount" value="<?php echo $sum?>">
				<input type="hidden" name="recipientCurrency" value="<?php echo $oShop_Currency->code?>">
				<input type="hidden" name="user_email" value="<?php echo $this->_shopOrder->email?>">
				<input type="hidden" name="successUrl" value="<?php echo $successUrl?>">
				<input type="hidden" name="failUrl" value="<?php echo $failUrl?>">
				<input type="hidden" name="hash" value= "<?php echo md5(
					$this->_rbkmoney_id . '::' .
					$sum . '::' .
					$oShop_Currency->code . '::' .
					$this->_shopOrder->email . '::' .
					$serviceName . '::' .
					$this->_shopOrder->id . '::' .
					'' . '::' .
					$this->_rbkmoney_secret_key)?>">
				<input type="submit" name="button" value="Оплатить">
			</form>
			<?php
		}
		else
		{
			?><h1>Не найдена валюта с идентификатором <?php $this->_rbkmoney_currency_id?>!</h1><?php
		}
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}