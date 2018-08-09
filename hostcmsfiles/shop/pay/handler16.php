<?php

/**
 * Обновленный обработчик Qiwi (REST)
 */
class Shop_Payment_System_Handler16 extends Shop_Payment_System_Handler
{
	//Идентификатор магазина из вкладки "Данные магазина" https://ishop.qiwi.com/options/merchants.action
	protected $_shop_id = "xxx";

	//REST ID из вкладки "Данные магазина" https://ishop.qiwi.com/options/merchants.action
	protected $_rest_id = "yyy";

	//REST пароль из вкладки "Данные магазина" https://ishop.qiwi.com/options/merchants.action
	protected $_rest_pwd = "zzz";

	//successUrl
	protected $_success_url = "http://demo.hostcms.ru/shop/cart/";

	//failUrl
	protected $_fail_url = "http://demo.hostcms.ru/shop/cart/";

	//noticeKey
	protected $_key = "bbb";

	private $_currency_id = 1;
	private $_lifetime = 5; // время жизни счета в днях

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_REQUEST['command']) && $_REQUEST['command'] == 'bill')
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getRequest('bill_id'));

			$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

			if (!is_null($oShop_Order->id))
			{
				// Вызов обработчика платежной системы
				Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
					->shopOrder($oShop_Order)
					->paymentProcessing();

				exit();
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
		$this->ProcessResult();

		return TRUE;
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

	public function getInvoice()
	{
		return $this->getNotification();
	}


	public function getNotification()
	{
		$sQiwiSum = $this->getSumWithCoeff();

		$aData = array(
			"user" => "tel:+" . $this->_shopOrder->phone,
			"amount" => $sQiwiSum,
			"ccy" => "RUB",
			"comment" => $this->_shopOrder->description,
			"lifetime" => date('Y-m-d\TH:i:s', Core_Date::sql2timestamp($this->_shopOrder->datetime) + $this->_lifetime * 60 * 60 * 24), //время жизни счета
			"pay_source" => "qw",
			"prv_name" => $this->_shopOrder->Shop->name //название продавца
			);

		$ch = curl_init('https://w.qiwi.com/api/v2/prv/'. $this->_shop_id .'/bills/' . $this->_shopOrder->invoice);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aData));
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $this->_rest_id . ":" . $this->_rest_pwd);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array (
			"Accept: application/json"
		));

		$server_output = curl_exec($ch);

		curl_close($ch);

		$aResponse = json_decode($server_output, true);
		//https://w.qiwi.com/order/external/main.action?shop=289720&transaction=172&successUrl=http://demo2.hostcms.ru/shop/cart/&failUrl=http://demo2.hostcms.ru/shop/cart/&qiwi_phone=79612732997

		$url = 'https://w.qiwi.com/order/external/main.action?shop=' . $this->_shop_id . '&transaction=' . $this->_shopOrder->invoice .'&successUrl=' . $this->_success_url . '&failUrl=' . $this->_fail_url . '&qiwi_phone=' . $this->_shopOrder->phone;

		ob_start();
		?><h1>Сейчас Вы будете перенаправлены для оплаты на сайт платежной системы...</h1>
		<form action="<?php echo $url; ?>" name="pay" method="post">
		</form>
		<script>document.pay.submit();</script>
		<?php

		return ob_get_clean();
	}

	/*
	 * Обработка статуса оплаты
	*/
	function ProcessResult()
	{
		$oShop_Order = Core_Entity::factory('Shop_Order')->find(Core_Array::getRequest('bill_id', 0));

		if(is_null($oShop_Order->id) || $oShop_Order->paid)
		{
			// Заказ не найден
			return FALSE;
		}

		$HTTP_X_API_SIGNATURE = Core_Array::get($_SERVER, 'HTTP_X_API_SIGNATURE', '');

		$command = Core_Array::getRequest('command', 0);
		$bill_id = Core_Array::getRequest('bill_id', 0);
		$status = Core_Array::getRequest('status', 0);
		$error = Core_Array::getRequest('error', 0);
		$amount = Core_Array::getRequest('amount', 0);
		$user = Core_Array::getRequest('user', 0);
		$prv_name = Core_Array::getRequest('prv_name', 0);
		$ccy = Core_Array::getRequest('ccy', 0);
		$comment = Core_Array::getRequest('comment', 0);

		//sign=HMAC-SHA1(key,{amount}|{bill_id}|{ccy}|{command}|{comment}|{error}|{prv_name}|{status}|{user})

		$sign = $amount . '|' . $bill_id . '|' . $ccy . '|' . $command . '|' . $comment . '|' . $error . '|' . $prv_name . '|' . $status . '|' . $user;

		$hash_gen = base64_encode(hash_hmac('sha1', $sign, $this->_key, true));

		if ($hash_gen == $HTTP_X_API_SIGNATURE)
		{
			$iCode = 0;

			switch (Core_Array::getRequest('status'))
			{
				case 'paid':
					$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

					$oShop_Order->system_information = "Счет оплачен через Qiwi.\n";
					$oShop_Order->paid();

					ob_start();
					$this->changedOrder('changeStatusPaid');
					ob_get_clean();
				break;
				case 'rejected':
					$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

					$oShop_Order->system_information = "Счет отменен клиентом.\n";
					$oShop_Order->canceled();

					ob_start();
					$this->changedOrder('changeStatusСanceled');
					ob_get_clean();
				break;
				case 'unpaid':
					$oShop_Order->system_information = 'Ошибка при проведении оплаты. Счет не оплачен.';
					$oShop_Order->save();
				break;
				case 'expired':
					$oShop_Order->system_information = 'Время жизни счета истекло. Счет не оплачен.';
					$oShop_Order->save();
				break;
			}
		}
		else
		{
			$iCode = 151;

			$oShop_Order->system_information = 'Ошибка 151';
			$oShop_Order->save();
		}

		Core_Page::instance()
			->response
			->status(200)
			->header('Content-Type', 'text/xml')
			->body('<?xml version="1.0" encoding="UTF-8"?>')
			->body('<result><result_code>')
			->body($iCode)
			->body('</result_code></result>')
			->sendHeaders()
			->showBody();

		die();
	}

}