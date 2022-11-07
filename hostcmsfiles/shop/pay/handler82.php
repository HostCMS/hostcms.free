<?php

/**
 * Оплата через Tinkoff
 * Инструкция по установке: https://oplata.tinkoff.ru/landing/develop/cms/hostcms
 */
class Shop_Payment_System_Handler82 extends Shop_Payment_System_Handler
{
	private $_gateway = 'https://rest-api-test.tinkoff.ru/v2/';
	//private $_gateway = 'https://securepay.tinkoff.ru/v2/';
	
	private $_terminalKey = '1517240177026DEMO';
	private $_secretKey = 'zwqcxc17fag63uk8';
	
	/**
	 * Печатать чеки
	 */
	protected $_enabled_taxation = true;
	/*
	 * Общая СН - osn
	 * Упрощенная СН (доходы) - usn_income
	 * Упрощенная СН (доходы минус расходы) - usn_income_outcome
	 * Единый налог на вмененный доход - envd
	 * Единый сельскохозяйственный налог - esn
	 * Патентная СН - patent
	 * */
	protected $_taxation = 'osn';

	public $vats = array(
		'none' => 'none',
		'0' => 'vat0',
		'10' => 'vat10',
		'18' => 'vat18',
	);
	protected $_currency_id = 1;
	public $customerEmail;

	public function checkPaymentBeforeContent()
	{
		if (isset($_POST['TerminalKey']) && $_POST['TerminalKey'] == $this->_terminalKey) {
			$order_id = intval(Core_Array::getRequest('OrderId'));
			$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

			if (!is_null($oShop_Order->id)) {
				try {
					// Вызов обработчика платежной системы
					Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
						->shopOrder($oShop_Order)
						->paymentProcessing();
				} catch (Exception $e) {
					die('NOTOK');
				}
			} else {
				die('NOTOK');
			}
		}
	}

	/*редирект */
	public function checkPaymentAfterContent()
	{
		if (isset($_GET['Success'])) {
			echo (filter_var($_GET['Success'], FILTER_VALIDATE_BOOLEAN) == true) ? 'Спасибо за заказ! Оплата через Tinkoff прошла успешно' :
				(isset($_GET['Message']) ? $_GET['Message'] : 'Ошибка оплаты. Повторите заказ');
			exit();
		}
	}

	/* обработка ответа от платёжной системы */
	public function paymentProcessing()
	{
		if (isset($_POST)) {
			$postRequest = $_POST;
			unset($postRequest['Token']);
			$token = $this->_genToken($postRequest);

			if ($_POST['Token'] != $token) {
				die('NOTOK');
			}

			if ($_POST['Status'] == 'CONFIRMED') {
				$this->_shopOrder->paid();
				$this->setXSLs();
				$this->send();

				ob_start();
				$this->changedOrder('changeStatusPaid');
				ob_get_clean();
			}

			die('OK');
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
		return Shop_Controller::instance()->round(($this->_currency_id > 0
			&& $this->_shopOrder->shop_currency_id > 0
				? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					$this->_shopOrder->Shop_Currency,
					Core_Entity::factory('Shop_Currency', $this->_currency_id)
				)
				: 0) * $this->_shopOrder->getAmount());
	}

	/* генерит форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$amount = ceil($this->getSumWithCoeff());
		$this->customerEmail = Core_Array::get($this->_orderParams, 'email', '');
		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_currency_id);

		$requestData = array(
			'OrderId' => $this->_shopOrder->id,
			'Amount' => $amount * 100,
			'DATA' => array(
				'Email' => $this->customerEmail,
				'Connection_type' => 'hostcms'
			),
		);

		if ($this->_enabled_taxation) {
			$requestData['Receipt'] = $this->getReceipt();
		}

		$request = $this->buildQuery('Init', $requestData);

		$this->logs($requestData, $request);

		$request = json_decode($request);

		if (isset($request->PaymentURL)) {
			?>
			<h1>Оплата через систему Tinkoff</h1>
			<p>Сумма к оплате составляет <strong><?php echo $amount ?><?php echo htmlspecialchars($oShop_Currency->name)?></strong></p>
			<p>Для оплаты нажмите кнопку "Оплатить".</p>
			<p style="color: rgb(112, 112, 112);">Внимание! Сумма оплаты округлена в большую сторону. <br>Нажимая
				&laquo;Оплатить&raquo; Вы подтверждаете передачу контактных данных на
				сервер Tinkoff для оплаты.</p>
			<form name="form1" action="<?php echo htmlspecialchars($request->PaymentURL)?>" method="POST" accept-charset="utf-8">
				<input type="submit" name="submit" value="Оплатить <?php echo $amount ?> <?php echo htmlspecialchars($oShop_Currency->name)?>">
			</form>
			<?php
		}
		else
		{
			echo "Ошибка!";
			if (isset($request->Message))
			{
				var_dump($request);
				//echo htmlspecialchars($request->Message);
			}
		}
	}

	function logs($requestData, $request)
	{
		// log send
		$log = '[' . date('D M d H:i:s Y', time()) . '] ';
		$log .= json_encode($requestData, JSON_UNESCAPED_UNICODE);
		$log .= "\n";
		file_put_contents(dirname(__FILE__) . "/tinkoff.log", $log, FILE_APPEND);

		$log = '[' . date('D M d H:i:s Y', time()) . '] ';
		$log .= $request;
		$log .= "\n";
		file_put_contents(dirname(__FILE__) . "/tinkoff.log", $log, FILE_APPEND);
	}

	public function getReceipt()
	{
		return array(
			'Email' => $this->customerEmail,
			'Taxation' => $this->_taxation,
			'Items' => $this->getReceiptItems(),
		);
	}

	public function getReceiptItems()
	{
		$order = Core_Entity::factory('Shop_Order', $this->_shopOrder->id);

		$products = $order->Shop_Order_Items->findAll();
		$receiptItems = array();

		foreach ($products as $product) {

			if ($product->price <0) continue;

			$price = $product->getPrice();
			$format = number_format($price, 2, '.', ''); // 2 = 0,00 количеству знаков после запятой в строке шаблона - xsl:value-of select="format-number(sum(exsl:node-set($subTotals)/sum), '### ##0,00', 'my')"/>
			$price_round = round($format *100);

			$quantity = $product->quantity;
			$quantity_round = round($quantity, 2);

			if (!$product->Shop_Item->shop_tax_id) {
				$tax = $this->vats['none'];
			} else {
				$tax = $this->vats[$product->rate] ? $this->vats[$product->rate] : $this->vats['none'];
			}

			if ($price_round)
			{
				$receiptItems[] = array(
					'Name' => $product->name,
					"Price" => $price_round,
					"Quantity" => $product->quantity,
					"Amount" => $price_round * $quantity_round,
					"Tax" => $tax,
				);
			}
		}

		$receiptItems = $this->balance_amount($order, $receiptItems);
		return $receiptItems;
	}

	function balance_amount($order, $receiptItems)
	{
		$itemsWithoutShipping = $receiptItems;

		//Shipping
		if ($order->shop_delivery_condition_id || $order->shop_delivery_id) {
			$shipping = array_pop($itemsWithoutShipping);
		}

		$sum = 0;

		foreach ($itemsWithoutShipping as $item) {
			$sum += $item['Amount'];
		}

		if (isset($shipping)) {
			$sum += $shipping['Amount'];
		}

		$total = ceil($this->getSumWithCoeff()) * 100;

		if ($sum != $total) {
			$sumAmountNew = 0;
			$difference = $total - $sum;
			$amountNews = array();

			foreach ($itemsWithoutShipping as $key => $item) {
				$itemsAmountNew = $item['Amount'] + floor($difference * $item['Amount'] / $sum);
				$amountNews[$key] = $itemsAmountNew;
				$sumAmountNew += $itemsAmountNew;
			}

			if (isset($shipping)) {
				$sumAmountNew += $shipping['Amount'];
			}

			if ($sumAmountNew != $total) {
				$max_key = array_keys($amountNews, max($amountNews))[0];	// ключ макс значения
				$amountNews[$max_key] = max($amountNews) + ($total - $sumAmountNew);
			}

			foreach ($amountNews as $key => $item) {
				$receiptItems[$key]['Amount'] = $amountNews[$key];
			}
		}
		return $receiptItems;
	}

	/**
	 * Builds a query string and call sendRequest method.
	 * Could be used to custom API call method.
	 *
	 * @param string $path API method name
	 * @param mixed $args query params
	 *
	 * @return mixed
	 * @throws HttpException
	 */
	public function buildQuery($path, $args)
	{
		$url = $this->_gateway;
		if (is_array($args)) {
			if (!array_key_exists('TerminalKey', $args)) {
				$args['TerminalKey'] = $this->_terminalKey;
			}
			if (!array_key_exists('Token', $args)) {
				$args['Token'] = $this->_genToken($args);
			}
		}
		$url = $this->_combineUrl($url, $path);


		return $this->_sendRequest($url, $args);
	}

	/**
	 * Generates token
	 *
	 * @param array $args array of query params
	 *
	 * @return string
	 */
	private function _genToken($args)
	{
		$token = '';
		$args['Password'] = $this->_secretKey;
		ksort($args);

		foreach ($args as $arg) {
			if (!is_array($arg)) {
				$token .= $arg;
			}
		}
		$token = hash('sha256', $token);

		return $token;
	}

	/**
	 * Combines parts of URL. Simply gets all parameters and puts '/' between
	 *
	 * @return string
	 */
	private function _combineUrl()
	{
		$args = func_get_args();
		$url = '';
		foreach ($args as $arg) {
			if (is_string($arg)) {
				if ($arg[strlen($arg) - 1] !== '/') {
					$arg .= '/';
				}
				$url .= $arg;
			} else {
				continue;
			}
		}

		return $url;
	}

	/**
	 * Main method. Call API with params
	 *
	 * @param string $api_url API Url
	 * @param array $args API params
	 *
	 * @return mixed
	 * @throws HttpException
	 */
	private function _sendRequest($api_url, $args)
	{
		$this->_error = '';
		//todo add string $args support
		//$proxy = 'http://192.168.5.22:8080';
		//$proxyAuth = '';
		if (is_array($args)) {
			$args = json_encode($args);
		}
		//Debug::trace($args);

		if ($curl = curl_init()) {
			curl_setopt($curl, CURLOPT_URL, $api_url);
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
			));

			$out = curl_exec($curl);
			$this->_response = $out;

			$json = json_decode($out);
			if ($json) {
				if (@$json->ErrorCode !== "0") {
					$this->_error = @$json->Details;
				} else {
					$this->_paymentUrl = @$json->PaymentURL;
					$this->_paymentId = @$json->PaymentId;
					$this->_status = @$json->Status;
				}
			}

			curl_close($curl);

			return $out;

		} else {
			throw new HttpException(
				'Can not create connection to ' . $api_url . ' with args '
				. $args, 404
			);
		}
	}
}