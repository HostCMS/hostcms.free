<?php

/**
 * Acquiropay
 *
 * Система платежей и SMS информирования.
 *
 * Поддержка обработчика платежной системы осуществляется Acquiropay.
 * http://www.acquiropay.ru/
 */
class Shop_Payment_System_Handler80 extends Shop_Payment_System_Handler
{
	/**
	 * Код торговцы
	 */
	private $_mid = 12345678;

	/**
	 * Код продукта
	 */
	private $_pid = 1111;

	/**
	 * Секретный код
	 */
	private $_sw = 'xxxxxxxxxxxxxxxxx';

	/**
	 * Идентификатор валюты
	 */
	private $_acquiropay_currency = 1;

	public function execute()
	{
		parent::execute();
		$this->printNotification();
		return $this;
	}

	protected function _processOrder()
	{
		parent::_processOrder();
		$this->setXSLs();
		$this->send();
		return $this;
	}

	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_acquiropay_currency > 0 && $this->_shopOrder->shop_currency_id > 0 ? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency($this->_shopOrder->Shop_Currency, Core_Entity::factory('Shop_Currency', $this->_acquiropay_currency)) : 0) * $this->_shopOrder->getAmount());
	}

	public function paymentProcessing()
	{
		$this->ProcessResult();
		return TRUE;
	}

	public function ProcessResult()
	{
		$payment_id = Core_Array::getPost('payment_id');
		$merchant_id = Core_Array::getPost('merchant_id');
		$status = Core_Array::getPost('status');
		$cf = Core_Array::getPost('cf');
		$cf2 = Core_Array::getPost('cf2');
		$cf3 = Core_Array::getPost('cf3');
		$product_name = "Оплата заказа № $cf";
		$cardholder = Core_Array::getPost('cardholder');
		$amount = Core_Array::getPost('amount');
		$email = Core_Array::getPost('email');
		$datetime = Core_Array::getPost('datetime');
		$sw = $this->_sw;
		$str_md5 = md5($merchant_id . $payment_id . $status . $cf . $cf2 . $cf3 . $sw);
		$sign2 = Core_Array::getPost('sign');

		if ($str_md5 == $sign2) {
			$this->_shopOrder->system_information = sprintf("Товар оплачен через
	Acquiropay.\n\nИнформация:\n\nID магазина: %sНомер покупки (СКО):
	%s\nНомер счета магазина: %s\nНазначение платежа: %s\nСумма платежа:
	%s\nИмя пользователя: %s\nE-mail пользователя:
	%s\nВремя выполнения платежа: %s\n", $pid, $payment_id, $merchant_id, $product_name, $amount, $cardholder, $email, $datetime);
			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
		}
	}

	public function getNotification()
	{
		$order_sum = $this->getSumWithCoeff();
		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_acquiropay_currency);
		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';
		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://' . $site_alias . $shop_path . 'cart/';
		$mid = $this->_mid;
		$pid = $this->_pid;
		$sw = $this->_sw;
		$cf = $this->_shopOrder->id;
		$token = md5($mid . $pid . $order_sum . $cf . $sw);

		?>
		<h1>Оплата через систему Acquiropay</h1>
		<p>К оплате <strong><?php echo $order_sum . " " . $oShop_Currency->name ?></strong></p>
		<form id="pay" action="https://secure.acquiropay.com" name="pay" method="post">
			<input type='hidden' name='product_id' value='<?php echo $pid ?>'/>
			<input type='hidden' name='product_name' value='<?php echo "Оплата заказа № {$this->_shopOrder->invoice}" ?>'/>
			<input type='hidden' name='token' value='<?php echo $token ?>'/>
			<input type='hidden' name='amount' value='<?php echo $order_sum ?>'/>
			<input type='hidden' name='cf' value='<?php echo $cf ?>'>
			<input type='hidden' name='cf2' value=''/>
			<input type='hidden' name='cf3' value=''/>
			<input type='hidden' name='first_name' value='<?php echo $this->_shopOrder->name ?>'/>
			<input type='hidden' name='last_name' value='<?php echo $this->_shopOrder->surname ?>'/>
			<input type='hidden' name='middle_name' value='<?php echo $this->_shopOrder->patronymic ?>'/>
			<input type='hidden' name='email' value='<?php echo $this->_shopOrder->email ?>'/>
			<input type='hidden' name='phone' value=''/>
			<input type='hidden' name='country' value=''/>
			<input type='hidden' name='city' value=''/>
			<input type='hidden' name='cb_url'
				   value='<?php echo $handler_url . "?orderId={$this->_shopOrder->id}&payment=result" ?>'/>
			<input type='hidden' name='ok_url'
				   value='<?php echo $handler_url . "?orderId={$this->_shopOrder->id}&payment=success" ?>'/>
			<input type='hidden' name='ko_url'
				   value='<?php echo $handler_url . "?orderId={$this->_shopOrder->id}&payment=fail" ?>'/>
			<input name="submit" value="Перейти к оплате" type="submit"/>
		</form>
	<?php
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}