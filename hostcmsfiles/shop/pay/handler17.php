<?php

/**
 * Яндекс.Деньги
 */
class Shop_Payment_System_Handler17 extends Shop_Payment_System_Handler
{
	public $_rub_currency_id = 1;

	/* Идентификатор магазина в системе Яндекс.Деньги. Выдается оператором системы. */
	protected $_ShopID = 9999;

	/* Пароль магазина в системе Яндекс.Деньги. Выдается оператором системы. */
	protected $_shopPassword = 'xxxx';

	protected $_yandex_money_uri = 'https://demomoney.yandex.ru/eshop.xml';

	/* Номер витрины магазина в системе Яндекс.Деньги. Выдается оператором системы. */
	protected $_scid = 9999;

	/* Код валюты */
	/*
	Возможные значения:
	643 — рубль Российской Федерации;
	10643 — тестовая валюта (демо-рублики демо-системы «Яндекс.Деньги»)
	*/
	protected $_orderSumCurrencyPaycash = 643;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_POST['action']) && isset($_POST['invoiceId']) && isset($_POST['orderNumber']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getPost('orderNumber'));

			$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

			if (!is_null($oShop_Order->id))
			{
				header("Content-type: application/xml");

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

	/* вычисление суммы товаров заказа */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->_rub_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_rub_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount() );
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

	/* обработка ответа от платёжной системы */
	public function paymentProcessing()
	{
			$this->ProcessResult();
			return TRUE;
	}

	/* оплачивает заказ */
	function ProcessResult()
	{
		$invoiceId = Core_Array::getPost('invoiceId');

		if ($this->_shopOrder->system_information == '')
		{
			$this->_shopOrder->system_information = $invoiceId;
			$this->_shopOrder->save();
		}

		if ($this->_shopOrder->system_information == $invoiceId)
		{
			/* проверяем заказ */
			$code = $this->_checkOrder($_POST);
		}
		else
		{
			$code = 1000;
		}

		$response_params = $_POST;
		$response['requestDatetime'] = date("c");

		/* генерируем XML ответа */
		$response = $this->_genXMLResponseToYandex($response_params, $code);

		if (Core_Array::getPost('action', '') == 'paymentAviso' && $code == 0)
		{
			$oShop_Order = $this->_shopOrder;

			$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

			$oShop_Order->system_information = "Товар оплачен через Яндекс.Деньги.\n";
			$oShop_Order->paid();
			$this->setXSLs();
			$this->send();
		}

		/* даем ответ Яндексу */
		echo $response;
		die();
	}

	/* генерация XML-а подтверждений магазина */
	protected function _genXMLResponseToYandex($response_params, $code = 1000)
	{
		$action = Core_Array::get($response_params, 'action');
		$code = intval($code);
		$current_date_time = Core_Array::get($response_params, 'requestDatetime');
		$invoiceId = Core_Array::get($response_params, 'invoiceId');

		$response = '<?xml version="1.0" encoding="UTF-8"?>';
		$response .= '<' . htmlspecialchars($action) . 'Response performedDatetime="'. htmlspecialchars($current_date_time) .'" code="' . $code . '" invoiceId="' . htmlspecialchars($invoiceId) . '" shopId="' . $this->_ShopID . '"/>';

		return $response;
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$Sum = $this->getSumWithCoeff();

		$oSiteUser = Core::moduleIsActive('siteuser')
			? Core_Entity::factory('Siteuser')->getCurrent()
			: NULL;

		?>
		<h2>Оплата через систему Яндекс.Деньги</h2>

		<form method="POST" action="<?php echo $this->_yandex_money_uri?>">
		<input class="wide" name="scid" value="<?php echo $this->_scid?>" type="hidden">
		<input type="hidden" name="ShopID" value="<?php echo $this->_ShopID?>">
		<input type="hidden" name="CustomerNumber" value="<?php echo (is_null($oSiteUser) ? 0 : $oSiteUser->id)?>">
		<input type="hidden" name="orderNumber" value="<?php echo $this->_shopOrder->id?>">
		<input type="hidden" name="orderSumCurrencyPaycash" value="<?php echo $this->_orderSumCurrencyPaycash?>">

		<table border = "1" cellspacing = "0" width = "400" bgcolor = "#FFFFFF" align = "center" bordercolor = "#000000">
			<tr>
				<td>Сумма, руб.</td>
				<td> <input type="text" name="Sum" value="<?php echo $Sum?>" readonly="readonly"> </td>
			</tr>
			<tr>
				<td>Номер заказа</td>
				<td> <input type="text" name="AccountNumber" value="<?php echo $this->_shopOrder->invoice?>" readonly="readonly"> </td>
			</tr>
		</table>

		<table border="0" cellspacing="1" align="center" width="400" bgcolor="#CCCCCC" >
			<tr bgcolor="#FFFFFF">
				<td width="490"></td>
				<td width="48"><input type="submit" name = "BuyButton" value = "Submit"></td>
			</tr>
		</table>
		</form>
	<?php
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

	/* проверяем заказ */
	protected function _checkOrder($order_params)
	{
		$site_user_id = $this->_shopOrder->siteuser_id;

		$Sum = $this->getSumWithCoeff();

		if (isset($order_params['shopId']) && isset($order_params['customerNumber'])
			&& isset($order_params['orderSumAmount']) && isset($order_params['AccountNumber'])
			&& isset($order_params['orderSumCurrencyPaycash']) && isset($order_params['action'])
			&& isset($order_params['orderNumber']) && isset($order_params['orderSumBankPaycash'])
			&& isset($order_params['invoiceId'])
		)
		{
			if (Core_Array::get($order_params, 'shopId') == $this->_ShopID
				&& Core_Array::get($order_params, 'customerNumber') == $site_user_id
				&& Core_Array::get($order_params, 'orderSumAmount') == $Sum
				&& Core_Array::get($order_params, 'AccountNumber') == $this->_shopOrder->invoice
				&& Core_Array::get($order_params, 'orderSumCurrencyPaycash') == $this->_orderSumCurrencyPaycash
			)
			{
				$in_str = $order_params['action'] . ";"
				. sprintf("%.2f", $Sum) . ";"
				. $this->_orderSumCurrencyPaycash . ";"
				. $order_params['orderSumBankPaycash'] . ";"
				. $this->_ShopID . ";"
				. $order_params['invoiceId'] . ";"
				. $site_user_id . ";"
				. $this->_shopPassword;

				$hash = strtoupper(md5($in_str));

				return intval($hash != $order_params['md5']);
			}
			elseif ($order_params['action'] == 'checkOrder')
			{
				return 100;
			}
		}
		else
		{
			return 200;
		}

		return 1000;
	}
}