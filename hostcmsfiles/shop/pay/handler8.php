<?php
/**
 * RBK Money
 */
class Shop_Payment_System_Handler34 extends Shop_Payment_System_Handler
{
	// Приватный ключ для доступа к API
	protected $_apiKey = 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICIzaG14MU95bjJWc2psTEZRNTNLYk5Nek12RGZUcjdzcGxYelJDeDRERVR3In0.eyJqdGkiOiI4ZTRkNDZjNC0yZjcwLTQyYTctYjhiYS1kN2RmMTcyYzBlYWIiLCJleHAiOjE1NTYxNzkwMjQsIm5iZiI6MCwiaWF0IjoxNTI0NjQzMDI0LCJpc3MiOiJodHRwczovL2F1dGgucmJrLm1vbmV5L2F1dGgvcmVhbG1zL2V4dGVybmFsIiwiYXVkIjoia29mZmluZyIsInN1YiI6ImJiNzY2ODQ0LTY2NWQtNGY5Zi1hYTRiLTUwNzQ4NzU3NjI5YSIsInR5cCI6IkJlYXJlciIsImF6cCI6ImtvZmZpbmciLCJub25jZSI6ImFiMmM5NzJhLTk1MzktNDdkNy04NDRjLTBhYmJiM2RlYzAzMyIsImF1dGhfdGltZSI6MTUyNDY0MzAxNSwic2Vzc2lvbl9zdGF0ZSI6IjFmNWNlNzUwLWUxOTctNGU2OC05YzRhLTNiNjUwYjFjZjI0OCIsImFjciI6IjAiLCJhbGxvd2VkLW9yaWdpbnMiOlsiaHR0cHM6Ly9kYXNoYm9hcmQucmJrLm1vbmV5Il0sInJlc291cmNlX2FjY2VzcyI6eyJjb21tb24tYXBpIjp7InJvbGVzIjpbImludm9pY2VzLioucGF5bWVudHM6d3JpdGUiLCJjdXN0b21lcnMuKi5iaW5kaW5nczp3cml0ZSIsInBhcnR5OnJlYWQiLCJpbnZvaWNlcy4qLnBheW1lbnRzOnJlYWQiLCJjdXN0b21lcnM6d3JpdGUiLCJwYXJ0eTp3cml0ZSIsImN1c3RvbWVycy4qLmJpbmRpbmdzOnJlYWQiLCJjdXN0b21lcnM6cmVhZCIsImludm9pY2VzOndyaXRlIiwiaW52b2ljZXM6cmVhZCJdfSwidXJsLXNob3J0ZW5lciI6eyJyb2xlcyI6WyJzaG9ydGVuZWQtdXJsczp3cml0ZSIsInNob3J0ZW5lZC11cmxzOnJlYWQiXX0sImFjY291bnQiOnsicm9sZXMiOlsibWFuYWdlLWFjY291bnQiLCJtYW5hZ2UtYWNjb3VudC1saW5rcyIsInZpZXctcHJvZmlsZSJdfX0sIm5hbWUiOiLQkNC70LXQutGB0LDQvdC00YAg0JXQs9C-0YDQvtCyIiwicHJlZmVycmVkX3VzZXJuYW1lIjoiYWxleGFuZGVyQGhvc3RjbXMucnUiLCJnaXZlbl9uYW1lIjoi0JDQu9C10LrRgdCw0L3QtNGAIiwiZmFtaWx5X25hbWUiOiLQldCz0L7RgNC-0LIiLCJlbWFpbCI6ImFsZXhhbmRlckBob3N0Y21zLnJ1In0.lRGwYmDA1s1pR7WxA8UOqwY04-HiD181PQUw-uxVyZH2xOxUM0GoFnjkWZKhrU3iHjfQlKn-TMvRop00guzUOYWedK5Sn72epiS-AyhIq3BHBLD4n-ZRu6k9QZ1ni4XkPR1UCBp9W3iwLuGxM1gBxzUzRaqqLill_3QD3ZB-tnQDQb0QdFnu_pNhc3G1Feeu0ajm4gpYwWcqVBmsfdomFdv3yN53JtVGeB_NZ1Vd_pi8wn8EOrSjVJ7FrbQpqi2l45BECdoUnUtB09XUtVpMzEFfwo0lNNisw_Pezt8UU5OVnqfu9kbiJghSuY448jpW7B0ZiZ1OpXWJUa6kNkZqjvyBAdcf-D8NTBWmiQVbFPE9ldq5MrrorJYLZNig3l_x_l77g7HjVk06zCxAFzjHV-CpsPGVlUbWrRmj2BAFdm_C6e99TKMxe3wXa76gFDdGbxLpNucr7PXHF5DQ5SbpOAyc3OY18K3IYIEnHw7vea7kKVPzG367LIWRFUDo-5z564UmaObgpDxHM85x5wNgkaNng6zonuE6fAyMkwc6yD2LK0UtNqfJa74so9NlVqYV1F_D8qKRtjwC6kFE0HlfwVHhr5U9Fu-tqewdNxYpcZoQgveLJAEjdq9G6n1uMDVwfJpmA84IVd0wPR_bjXtTRGgYugJrN3Z7rw1PVC_0Q9M';

	// Идентификатор магазина
	protected $_rbkShopId = '94ffc421-cdd4-4a3c-a2d9-8dfecab2f5c1';

	// Публичный ключ (Webhook)
	protected $_publicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAt2Th8ZbRwZPF3cNSmz0N
28gcL154DnxLcAyCx9h5KbAsXoxqCrXxf7zloVlGpjkkVJfwYtYadl4sEe96A2gW
Rw26y3hdqMTk8C36RW36M8E6xvMJH6u006Yqx62Z+0FlxpSvm0EUtToZ7uiAquky
ApK4ahNAfBH6peknLTE+bsGJnS/lI2ia6SqGF1bIJQc0vCG6heMuMbFJ8bVbLaM/
Wro7qrP/D7fLUlxO8ULrMOaDIaR0hl4g/8kOLuZPmK4lfBGhZMfs0ryWp94C5Mhs
pSqMMFRKvqb8e9/FP+iETvXiPkvIi1y38ODaelZ/X1Yohm6N8jP/lW+lXmfmxG+U
+wIDAQAB
-----END PUBLIC KEY-----';

	/* Идентификатор валюты, в которой будет производиться платеж.
	 Сумма к оплате будет пересчитана из валюты магазина в указанную валюту */
	protected $_rbkmoney_currency_id = 1;

	/* Определяем коэффициент перерасчета цены */
	protected $_coefficient = 1;

	/**
	 * Метод, вызываемый в коде ТДС через Shop_Payment_System_Handler::checkAfterContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_SERVER['HTTP_CONTENT_SIGNATURE']))
		{
			// Данные, которые пришли в теле сообщения
			$content = file_get_contents('php://input');

			// Достаем сигнатуру из заголовка и декодируем
			$signatureFromHeader = $this->getSignatureFromHeader($_SERVER['HTTP_CONTENT_SIGNATURE']);

			// Декодируем данные
			$decodedSignature = $this->urlsafe_b64decode($signatureFromHeader);

			if(!$this->verificationSignature($content, $decodedSignature, $this->_publicKey))
			{
				http_response_code(400);
				echo json_encode(['message' => 'Webhook notification signature mismatch']);
				exit();
			}

			// Преобразуем данные в массив
			$aResponse = json_decode($content, TRUE);

			if (isset($aResponse['eventType']) && $aResponse['eventType'] == 'PaymentProcessed'
				&& isset($aResponse['invoice']))
			{
				// Получаем ID заказа
				$order_id = intval($aResponse['invoice']['metadata']['order_id']);

				$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

				if (!is_null($oShop_Order->id))
				{
					$request = json_encode(
						array(
							'reason' => 'capture'
						)
					);

					try {
						$Core_Http = Core_Http::instance('curl')
							->clear()
							->method('POST')
							->timeout(30)
							->url("https://api.rbk.money/v1/processing/invoices/{$aResponse['invoice']['id']}/payments/{$aResponse['payment']['id']}/capture")
							->additionalHeader('X-Request-ID', uniqid())
							->additionalHeader('Authorization', 'Bearer ' . $this->_apiKey)
							->additionalHeader('Content-type', 'application/json; charset=utf-8')
							->additionalHeader('Accept', 'application/json')
							->rawData($request)
							->execute();

						$aHeaders = $Core_Http->parseHeaders();

						$sStatus = Core_Array::get($aHeaders, 'status');

						$iStatusCode = $Core_Http->parseHttpStatusCode($sStatus);

						if ($iStatusCode == 202)
						{
							// Вызов обработчика платежной системы
							Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
								->shopOrder($oShop_Order)
								->paymentProcessing();
						}
						else
						{
							$oShop_Order->system_information = "RBKMoney неверный ответ! Код: {$iStatusCode}";
							$oShop_Order->save();
						}
					}
					catch (Exception $e) {}
				}
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
		$this->shopOrderBeforeAction(clone $this->_shopOrder);

		$this->_shopOrder->system_information = sprintf("Товар оплачен через RBK Money");

		$this->_shopOrder->paid();

		ob_start();
		$this->changedOrder('changeStatusPaid');
		ob_get_clean();
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$oShop_Order = $this->_shopOrder;

		$sum = $this->getSumWithCoeff() * 100;

		$aVat = array(
			0 => '0%',
			10 => '10%',
			18 => '18%'
		);

		$oSite_Alias = $oShop_Order->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';

		$shop_path = $oShop_Order->Shop->Structure->getPath();
		$handler_url = 'http://' . $site_alias . $shop_path . "cart/?order_id={$oShop_Order->id}";

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_rbkmoney_currency_id);

		if(!is_null($oShop_Currency->id))
		{
			$serviceName = 'Оплата счета N ' . $oShop_Order->invoice;

			$aItems = $this->getOrderItems($oShop_Order);

			$dueDate = date('Y-m-d\TH:i:s\Z', Core_Date::sql2timestamp($oShop_Order->datetime));

			$aData = array(
				'shopID' => $this->_rbkShopId,
				'dueDate' => $dueDate,
				'amount' => $sum,
				'currency' => $oShop_Currency->code,
				'product' => 'Счет N ' . $oShop_Order->invoice,
				'description' => $serviceName,
				'metadata' => array(
					'cms' => 'HostCMS',
					'module' => 'shop',
					'order_id' => $oShop_Order->id
				)
			);

			foreach ($aItems as $aItem)
			{
				$aData['cart'][] = array(
					'product' => $aItem['name'],
					'quantity' => intval($aItem['quantity']),
					'price' =>  $aItem['price'] * 100,
					'taxMode' => array(
						'type' => 'InvoiceLineTaxVAT',
						'rate' => Core_Array::get($aVat, $aItem['tax'], 'none')
					)
				);
			}

			$request = json_encode($aData);

			try {
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('POST')
					->timeout(30)
					->url("https://api.rbk.money/v1/processing/invoices")
					->additionalHeader('X-Request-ID', uniqid())
					->additionalHeader('Authorization', 'Bearer ' . $this->_apiKey)
					->additionalHeader('Content-type', 'application/json; charset=utf-8')
					->additionalHeader('Accept', 'application/json')
					->rawData($request)
					->execute();

				$aAnswer = json_decode($Core_Http->getBody(), TRUE);

				if (isset($aAnswer['invoice']) && isset($aAnswer['invoice']['id'])
					&& isset($aAnswer['invoiceAccessToken']) && isset($aAnswer['invoiceAccessToken']['payload'])
				)
				{
					$invoice_id = strval($aAnswer['invoice']['id']);
					$access_token = strval($aAnswer['invoiceAccessToken']['payload']);

					?>
					<form action="<?php echo $handler_url?>" method="GET">
						<script src="https://checkout.rbk.money/checkout.js" class="rbkmoney-checkout"
								data-invoice-id="<?php echo $invoice_id?>"
								data-invoice-access-token="<?php echo $access_token?>"
								data-payment-flow-hold="true"
								data-hold-expiration="capture"
								data-name="<?php echo strval($aAnswer['invoice']['product'])?>"
								data-email="<?php echo $oShop_Order->email?>"
								data-logo="https://checkout.rbk.money/images/logo.png"
								data-label="Оплатить с карты"
								data-description="<?php echo strval($aAnswer['invoice']['description'])?>"
								data-pay-button-label="Оплатить">
						</script>
					</form>
					<?php
				}
			}
			catch (Exception $e) {}
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

	public function getOrderItems($oShop_Order)
	{
		$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll(FALSE);

		// Расчет сумм скидок, чтобы потом вычесть из цены каждого товара
		$discount = $amount = $quantity = 0;
		foreach ($aShop_Order_Items as $key => $oShop_Order_Item)
		{
			if ($oShop_Order_Item->price <= 0)
			{
				$discount -= $oShop_Order_Item->getAmount();
				unset($aShop_Order_Items[$key]);
			}
			else
			{
				$amount += $oShop_Order_Item->getAmount();
				$quantity += $oShop_Order_Item->quantity;
			}
		}

		$discount = $amount != 0 && $quantity != 0
			? round(
				abs($discount) / $amount, 4, PHP_ROUND_HALF_DOWN
			)
			: 0;

		//echo "\ndiscount = ", $discount, "\n";

		// -----------------
		$aItems = array();

		// Рассчитываемая сумма с учетом скидок
		$calcAmount = 0;

		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			if ($oShop_Order_Item->quantity)
			{
				$price = number_format(
					Shop_Controller::instance()->round($oShop_Order_Item->price + $oShop_Order_Item->getTax()) * (1 - $discount),
					2, '.', ''
				);

				$calcAmount += $price * $oShop_Order_Item->quantity;

				$aItems[] = array(
					'name' => mb_substr($oShop_Order_Item->name, 0, 128),
					'shop_item_id' => $oShop_Order_Item->shop_item_id,
					'quantity' => $oShop_Order_Item->quantity,
					'price' =>  $price,
					'tax' => $oShop_Order_Item->rate
				);
			}
		}

		$totalAmount = $oShop_Order->getAmount();
		if ($calcAmount != $totalAmount)
		{
			//echo "\nРАЗНИЦА В СУММАХ! calc = {$calcAmount}, total= {$totalAmount}";

			$delta = $totalAmount - $calcAmount;

			end($aItems);
			$lastKey = key($aItems);

			$deltaPerOneItem = round($delta / $aItems[$lastKey]['quantity'], 2);
			//echo "\ndelta=", $deltaPerOneItem;

			$aItems[$lastKey]['price'] += $deltaPerOneItem;

			$calcAmount += $deltaPerOneItem * $aItems[$lastKey]['quantity'];

			// Если опять не равны, то добавляем новый товар
			if ($calcAmount < $totalAmount)
			{
				$aItems[] = array(
					'name' => 'Округление',
					'shop_item_id' => 0,
					'quantity' => 1,
					'price' => round($totalAmount - $calcAmount, 2),
					// Ставка налога текстовая
					'tax' => 0
				);
			}
		}

		return $aItems;
	}

	public function getSignatureFromHeader($contentSignature)
	{
		$signature = preg_replace("/alg=(\S+);\sdigest=/", '', $contentSignature);

		if (empty($signature)) {
			throw new Exception('Signature is missing');
		}

		return $signature;
	}

	public function urlsafe_b64decode($string)
	{
		return base64_decode(strtr($string, '-_,', '+/='));
	}

	// Проверяем сигнатуру
	public function verificationSignature($data, $signature, $public_key)
	{
		if (empty($data) || empty($signature) || empty($public_key)) {
			return FALSE;
		}

		$public_key_id = openssl_get_publickey($public_key);
		if (empty($public_key_id)) {
			return FALSE;
		}

		$verify = openssl_verify($data, $signature, $public_key_id, OPENSSL_ALGO_SHA256);

		return ($verify == 1);
	}
}