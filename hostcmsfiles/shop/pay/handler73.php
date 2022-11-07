<?php

/**
 * A1Pay
 */
class Shop_Payment_System_Handler73 extends Shop_Payment_System_Handler
{
	// Секретный ключ
	private $_password = "12345";

	// Идентификатор рублей = 1
	private $_currency_id = 1;

	// Ключ, присваеваемый магазину системой A1Pay, при создании кнопки
	private $_key = "qwertyuiop";

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
		return Shop_Controller::instance()->round(($this->_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount());
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
		if($this->_shopOrder->paid)
		{
			return FALSE;
		}

		// чтение полученных параметров
		$tid = to_str(Core_Array::getRequest("tid", ''));
		$name = to_str(Core_Array::getRequest("name", ''));
		$comment = to_str(Core_Array::getRequest("comment", ''));
		$partner_id = to_str(Core_Array::getRequest("partner_id", ''));
		$service_id = to_str(Core_Array::getRequest("service_id", ''));
		$type = to_str(Core_Array::getRequest("type", ''));
		$partner_income = to_str(Core_Array::getRequest("partner_income", ''));
		$system_income = to_str(Core_Array::getRequest("system_income", ''));
		$a1pay_hash = to_str(Core_Array::getRequest("check", ''));

		$data = array
		(
			'tid' => $tid,
			'name' => $name,
			'comment' => $comment,
			'partner_id' => $partner_id,
			'service_id' => $service_id,
			'order_id' => $this->_shopOrder->id,
			'type' => $type,
			'partner_income' => $partner_income,
			'system_income' => $system_income
		);

		$our_hash = md5(join('', array_values($data)) . $this->_password);

		$sum =  $this->getSumWithCoeff();

		if($a1pay_hash == $our_hash
		&& Shop_Controller::instance()->round(Core_Array::getPost('partner_income', 0)) == $sum)
		{
			$this->_shopOrder->system_information = sprintf("Товар оплачен через A1Pay.\n\nИнформация:\n\nID транзакции A1Pay: %s\nId партнера: %s\nId сервиса: %s\nId заказа: %s\nТип оплаты: %s\n", $tid, $partner_id, $service_id, $this->_shopOrder->id, $type);

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
		}
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$sum = $this->getSumWithCoeff();

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_currency_id);

		if(!is_null($oShop_Currency->id))
		{
			?>
			<h1>Оплата через систему A1Pay</h1>
			<p>Сумма к оплате составляет <strong><?php echo $sum?> <?php echo $oShop_Currency->name?></strong></p>
			<p>Для оплаты нажмите кнопку "Оплатить".</p>
			<p style="color: rgb(112, 112, 112);">Внимание! Нажимая
			&laquo;Оплатить&raquo; Вы подтверждаете передачу контактных данных на
			сервер A1Pay для оплаты.</p>
			<form name="form1" action="https://partner.a1pay.ru/a1lite/input"
				method="POST" accept-charset="utf-8"><input type="hidden" name="key"
				value="<?php echo $this->_key?>" /> <input type="hidden" name="cost"
				value="<?php echo $sum?>" /> <input type="hidden" name="name"
				value="Заказ №<?php echo $this->_shopOrder->id?>" /> <input type="hidden"
				name="default_email" value="" /> <input type="hidden" name="order_id"
				value="<?php echo $this->_shopOrder->id?>" /> <input type="hidden" name="comment"
				value="accept_a1pay" /> <input type="submit" name="submit"
				value="Оплатить <?php echo $sum?> <?php echo $oShop_Currency->name?>">
			</form>
			<?php
		}
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}