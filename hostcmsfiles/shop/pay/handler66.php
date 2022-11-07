<?php

/**
 * Оплата с лицевого счета
 */
class Shop_Payment_System_Handler66 extends Shop_Payment_System_Handler
{
	/**
	 * Метод, вызываемый в коде ТДС через Shop_Payment_System_Handler::checkAfterContent($oShop);
	 */
	public function checkPaymentAfterContent()
	{
		if (isset($_REQUEST['Pay']))
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
	public function getSum()
	{
		return Shop_Controller::instance()->round($this->_shopOrder->getAmount());
	}

	/* обработка ответа от платёжной системы */
	public function paymentProcessing()
	{
			$this->ProcessResult();

			return TRUE;
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		?>
		<h1>Оплата с лицевого счета пользователя</h1>
		<?php

		$sum = $this->getSum();

		$oSiteuser = Core_Entity::factory('Siteuser', 0)->getCurrent();

		$sum_currency = (
				$this->_shopOrder->Shop->shop_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				$this->_shopOrder->Shop->Shop_Currency
			) : 0) * $sum;

		if($this->_shopOrder->paid)
		{
			?>
			<p>Ваш заказ был оплачен <?php echo Core_Date::sql2datetime($this->_shopOrder->payment_datetime)?>.</p>
			<?php
		}
		elseif($oSiteuser->getTransactionsAmount($this->_shopOrder->Shop) < $sum_currency)
		{
			?>
			<div id="error">На лицевом счете недостаточно средств для проведения операции. Изменить способ оплаты вы можете в личном кабинете.</div>
			<?php
		}
		else
		{
			$currency_name = $this->_shopOrder->Shop_Currency->name;

			$shop_path = $this->_shopOrder->Shop->Structure->getPath();
			$handler_url = $shop_path . 'cart/';

			?>
			<p>Сумма к оплате с лицевого счета составляет <?php echo $sum?> <?php echo $currency_name?></p>
			<form action="<?php echo $handler_url?>" method="post">
			<input type="hidden" name="order_id" value="<?php echo $this->_shopOrder->id?>" />
			<input type="submit" name="Pay" value="Оплатить <?php echo $sum?> <?php echo $currency_name?>" class="button" />
			</form>
			<?php
		}
	}

	 /* оплачивает заказ */
	function ProcessResult()
	{
		$oSiteuser = Core_Entity::factory('Siteuser', 0)->getCurrent();

		$oShop_Order = $this->_shopOrder;

		$sum = $this->getSum();

		$sum_currency = (
				$this->_shopOrder->Shop->shop_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				$this->_shopOrder->Shop->Shop_Currency
			) : 0) * $sum;

		if($this->_shopOrder->paid || $sum_currency == 0 || $oSiteuser->getTransactionsAmount($this->_shopOrder->Shop) < $sum_currency)
		{
			return FALSE;
		}

		$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

		// Проведение транзакции
		$oShop_Siteuser_Transaction = Core_Entity::factory('Shop_Siteuser_Transaction');
		$oShop_Siteuser_Transaction->shop_id = $this->_shopOrder->Shop->id;
		$oShop_Siteuser_Transaction->siteuser_id = $oSiteuser->id;

		try
		{
			$oShop_Siteuser_Transaction->active = 1;
			$oShop_Siteuser_Transaction->amount = $sum * -1;
			$oShop_Siteuser_Transaction->amount_base_currency = $sum_currency * -1;
			$oShop_Siteuser_Transaction->shop_currency_id = $this->_shopOrder->shop_currency_id;
			$oShop_Siteuser_Transaction->shop_order_id = $this->_shopOrder->id;
			$oShop_Siteuser_Transaction->type = 0;
			$oShop_Siteuser_Transaction->description = sprintf("Оплата заказа N %s", $this->_shopOrder->invoice);
			$oShop_Siteuser_Transaction->save();

			$this->_shopOrder->paid();
			//$this->setXSLs();
			//$this->send();

			?>
			<p>Спасибо, Ваш заказ оформлен. Подробная информация отправлена на Ваш электронный адрес.</p>
			<?php

			ob_start();
			$this->changedOrder('changeStatusPaid');
			ob_get_clean();
		}
		catch(Exception $exc)
		{
			?>
			<p>Ошибка: <?php echo $exc->getMessage()?></p>
			<?php
		}
	}
}