<?php

/**
 * WebMoney
 */
class Shop_Payment_System_Handler63 extends Shop_Payment_System_Handler
{
	/* Кошельки */
	protected $_wmr = 'R123456789123';
	protected $_wmz = 'Z123456789123';

	/* Определяем валюты для WMR и WMZ */
	protected $_wmr_currency_id = 0;
	protected $_wmz_currency_id = 0;

	/* Определяем коэффициенты перерасчета для WMR и WMZ */
	protected $_wmr_coefficient_id = 1;
	protected $_wmz_coefficient_id = 1;

	/* Режим работы (0 - реальный, 1 - тестовый) */
	protected $_mode = 0;

	/* Секретные ключи для кошельков, должны совпадать с настройками
	 на https://merchant.webmoney.ru/conf/purses.asp */
	protected $_wmr_secret_key = 'hostmake';
	protected $_wmz_secret_key = 'hostmake';

	public function __construct(Shop_Payment_System_Model $oShop_Payment_System_Model)
	{
		parent::__construct($oShop_Payment_System_Model);
		$oCurrency = Core_Entity::factory('Shop_Currency')->getByCode('RUB');
		!is_null($oCurrency) && $this->_wmr_currency_id = $oCurrency->id;
		$oCurrency = Core_Entity::factory('Shop_Currency')->getByCode('USD');
		!is_null($oCurrency) && $this->_wmz_currency_id = $oCurrency->id;
	}

	/**
	 * Метод, вызываемый в коде ТДС через Shop_Payment_System_Handler::checkAfterContent($oShop);
	 */
	public function checkPaymentAfterContent()
	{
		if (isset($_POST['LMI_PAYEE_PURSE']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getPost('LMI_PAYMENT_NO'));

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

	public function paymentProcessing()
	{
		/* Пришло подтверждение оплаты, обработаем его */
		if (isset($_POST['LMI_PAYEE_PURSE']))
		{
			$this->ProcessResult();
			return true;
		}
	}

	public function getSumWithCoeff($iCurrencyID, $iCoefficient)
	{
		return Shop_Controller::instance()->round(($iCurrencyID > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $iCurrencyID)
			)
			: 0) * $this->_shopOrder->getAmount() * $iCoefficient);
	}

	public function getNotification()
	{
		$wmr_sum = $this->getSumWithCoeff($this->_wmr_currency_id, $this->_wmr_coefficient_id);
		$wmz_sum = $this->getSumWithCoeff($this->_wmz_currency_id, $this->_wmz_coefficient_id);

		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';

		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://' . $site_alias . $shop_path . 'cart/';

		ob_start();

		?>
		<h1>Оплата через систему WebMoney</h1>
		<!-- Форма для оплаты через WMR -->
		<p>К оплате <strong><?php echo $wmr_sum?> WMR</strong></p>
		<form id="pay" name="pay" method="post" action="https://merchant.webmoney.ru/lmi/payment.asp">
			<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?php echo $wmr_sum?>">
			<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?php echo base64_encode("Оплата счета N {$this->_shopOrder->invoice} через WMR")?>">
			<input type="hidden" name="LMI_PAYMENT_NO" value="<?php echo htmlspecialchars($this->_shopOrder->invoice)?>">
			<input type="hidden" name="LMI_PAYEE_PURSE" value="<?php echo $this->_wmr?>">
			<input type="hidden" name="LMI_SIM_MODE" value="0">
			<input type="hidden" name="LMI_RESULT_URL" value="<?php echo htmlspecialchars($handler_url)?>">
			<input type="hidden" name="LMI_SUCCESS_URL" value="<?php echo htmlspecialchars($handler_url."?order_id={$this->_shopOrder->invoice}&payment=success")?>">
			<input type="hidden" name="LMI_SUCCESS_METHOD" value="POST">
			<input type="hidden" name="LMI_FAIL_URL" value="<?php echo htmlspecialchars($handler_url."?order_id={$this->_shopOrder->invoice}&payment=fail")?>">
			<input type="hidden" name="LMI_FAIL_METHOD" value="POST">
			<input type="hidden" name="step_4" value="1">
			<input type="hidden" name="system_of_pay_id" value="<?php echo $this->_shopOrder->shop_payment_system_id?>">
			<input type="hidden" name="order_id" value="<?php echo htmlspecialchars($this->_shopOrder->invoice)?>">
			<input type="hidden" name="purse" value="1">
			<div style="margin: 10px 0px; float: left" class="shop_button_block red_button_block">
				<input name="submit" value="Перейти к оплате в WMR" type="submit"/>
			</div>
			<div style="clear: both;"></div>
		</form>
		<!-- Форма для оплаты через WMZ -->
		<p>К оплате <strong><?php echo $wmz_sum?> WMZ</strong></p>
		<form id="pay" name="pay" method="post" action="https://merchant.webmoney.ru/lmi/payment.asp">
			<input type="hidden" name="LMI_PAYMENT_AMOUNT" value="<?php echo $wmz_sum?>">
			<input type="hidden" name="LMI_PAYMENT_DESC_BASE64" value="<?php echo base64_encode("Оплата счета N {$this->_shopOrder->invoice} через WMZ")?>">
			<input type="hidden" name="LMI_PAYMENT_NO" value="<?php echo htmlspecialchars($this->_shopOrder->invoice)?>">
			<input type="hidden" name="LMI_PAYEE_PURSE" value="<?php echo $this->_wmz?>">
			<input type="hidden" name="LMI_SIM_MODE" value="0">
			<input type="hidden" name="LMI_RESULT_URL" value="<?php echo htmlspecialchars($handler_url)?>">
			<input type="hidden" name="LMI_SUCCESS_URL" value="<?php echo htmlspecialchars($handler_url."?order_id={$this->_shopOrder->invoice}&payment=success")?>">
			<input type="hidden" name="LMI_SUCCESS_METHOD" value="POST">
			<input type="hidden" name="LMI_FAIL_URL" value="<?php echo htmlspecialchars($handler_url."?order_id={$this->_shopOrder->invoice}&payment=fail")?>">
			<input type="hidden" name="LMI_FAIL_METHOD" value="POST">
			<input type="hidden" name="step_4" value="1">
			<input type="hidden" name="system_of_pay_id" value="<?php echo $this->_shopOrder->shop_payment_system_id?>">
			<input type="hidden" name="order_id" value="<?php echo htmlspecialchars($this->_shopOrder->invoice)?>">
			<input type="hidden" name="purse" value="2">
			<div style="margin: 10px 0px; float: left" class="shop_button_block red_button_block">
				<input name="submit" value="Перейти к оплате в WMZ" type="submit"/>
			</div>
		</form>
		<?php

		return ob_get_clean();
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

	// Вывод сообщения об успешности/неуспешности оплаты
	function ShowResultMessage()
	{
		// Отсутствует в WebMoney
		return;
	}

	/*
	* Обработка статуса оплаты
	*/
	function ProcessResult()
	{
		// Заказ не найден, либо оплачен
		if(is_null($this->_shopOrder) || $this->_shopOrder->paid)
		{
			return FALSE;
		}

		switch(Core_Array::getPost('purse', 0))
		{
			case 1: //WMR
			$sKey = $this->_wmr_secret_key;
			$sCurrencyName = 'WMR';
			$iCoefficient = $this->_wmr_coefficient_id;
			$sOrderSum = $this->getSumWithCoeff($this->_wmr_currency_id, $iCoefficient);
			break;
			case 2: //WMZ
			$sKey = $this->_wmz_secret_key;
			$sCurrencyName = 'WMZ';
			$iCoefficient = $this->_wmz_coefficient_id;
			$sOrderSum = $this->getSumWithCoeff($this->_wmz_currency_id, $iCoefficient);
			break;
		}

		if (Core_Array::getPost('LMI_PAYMENT_AMOUNT', '') == $sOrderSum)
		{
			// Проверяем целостность данных
			$sSelfHash = hash('sha256', Core_Array::getPost('LMI_PAYEE_PURSE', '').
				Core_Array::getPost('LMI_PAYMENT_AMOUNT', '') .
				Core_Array::getPost('LMI_PAYMENT_NO', '') .
				$this->_mode .
				Core_Array::getPost('LMI_SYS_INVS_NO', '') .
				Core_Array::getPost('LMI_SYS_TRANS_NO', '') .
				Core_Array::getPost('LMI_SYS_TRANS_DATE', '') .
				$sKey .
				Core_Array::getPost('LMI_PAYER_PURSE', '') .
				Core_Array::getPost('LMI_PAYER_WM', '')
			);

			// Сравниваем хэши
			if (mb_strtoupper($sSelfHash) == mb_strtoupper(Core_Array::getPost('LMI_HASH', '')))
			{
				$this->_shopOrder->system_information = sprintf("Товар оплачен через WebMoney.\nАтрибуты:\nКошелек продавца: %s\nСумма: %s %s\nНомер покупки: %s\nРежим (0 - реальный, 1 - тестовый): %s\nНомер счета для покупателя: %s\nНомер платежа: %s\nКошелек покупателя: %s\nWM-идентификатор покупателя: %s\n",
					Core_Array::getPost('LMI_PAYEE_PURSE', ''),
					Core_Array::getPost('LMI_PAYMENT_AMOUNT', ''),
					$sCurrencyName,
					Core_Array::getPost('LMI_PAYMENT_NO', ''),
					Core_Array::getPost('LMI_MODE', ''),
					Core_Array::getPost('LMI_SYS_INVS_NO', ''),
					Core_Array::getPost('LMI_SYS_TRANS_NO', ''),
					Core_Array::getPost('LMI_PAYER_PURSE', ''),
					Core_Array::getPost('LMI_PAYER_WM', ''));

					$this->_shopOrder->paid();
					$this->setXSLs();
					$this->send();
			}
			else
			{
				$this->_shopOrder->system_information = 'WM хэш не совпал!';
				$this->_shopOrder->save();
			}
		}
		else
		{
			$this->_shopOrder->system_information = 'WM сумма не совпала!';
			$this->_shopOrder->save();
		}
	}
}