<?php
/**
 * RBK Money
 * Личный кабинет https://dashboard.rbk.money/
 */
class Shop_Payment_System_Handler8 extends Shop_Payment_System_Handler
{
	/**
	 * Приватный ключ для доступа к API
	 * Доступен в разделе "API ключ", поле "Ваш приватный ключ для доступа к API"
	 */
	protected $_apiKey = '%apiKey%';

	/**
	 * Идентификатор магазина
	 * Доступен в разделе "Детали магазина", строка "Идентификатор"
	 */
	protected $_rbkShopId = '%rbkShopId%';

	/**
	 * Публичный ключ (Webhook)
	 * Создается в разделе "Webhooks". При создании выбираем события:
	 *
	 * - PaymentProcessed
	 * - PaymentCaptured
	 * - PaymentCancelled
	 *
	 * После сохранения ключ будет в поле "Публичный ключ". Нажмите "Скопировать ключ"
	 */
	protected $_publicKey = '%publicKey%';

	/**
	 * Идентификатор валюты, в которой будет производиться платеж.
	 * Сумма к оплате будет пересчитана из валюты магазина в указанную валюту
	 */
	protected $_rbkmoney_currency_id = 1;

	/**
	 * Определяем коэффициент перерасчета цены
	 */
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
			$signatureFromHeader = $this->_getSignatureFromHeader($_SERVER['HTTP_CONTENT_SIGNATURE']);

			// Декодируем данные
			$decodedSignature = $this->_urlsafe_b64decode($signatureFromHeader);

			if (!$this->_verificationSignature($content, $decodedSignature, $this->_publicKey))
			{
				http_response_code(400);
				echo json_encode(['message' => 'Webhook notification signature mismatch']);
				exit();
			}

			// Преобразуем данные в массив
			$aResponse = json_decode($content, TRUE);

			// PaymentProcessed - платеж успешно обработан (средства захолдированы)
			// PaymentCaptured - платеж успешно принят (захолдированные средства списаны)
			// PaymentCancelled - платеж отменен (захолдированные средства возвращены)
			if (isset($aResponse['eventType']) && $aResponse['eventType'] == 'PaymentProcessed'
				&& isset($aResponse['invoice']))
			{
				// Получаем ID заказа
				$order_id = intval($aResponse['invoice']['metadata']['order_id']);

				$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

				if (!is_null($oShop_Order->id))
				{
					$request = json_encode(
						array('reason' => 'capture')
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
								->paymentProcessing($aResponse);
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

	/**
	 * Вызывается на 4-ом шаге оформления заказа
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

	/**
	 * Вычисление суммы товаров заказа
	 */
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

	/**
	 * Обработка ответа от платёжной системы
	 */
	public function paymentProcessing($aResponse)
	{
		$amount = isset($aResponse['invoice']['amount'])
			? intval($aResponse['invoice']['amount']) / 100
			: '';

		$currency = isset($aResponse['invoice']['currency'])
			? strval($aResponse['invoice']['currency'])
			: '';

		$id = isset($aResponse['invoice']['id'])
			? strval($aResponse['invoice']['id'])
			: '';

		$this->shopOrderBeforeAction(clone $this->_shopOrder);

		$this->_shopOrder->system_information = sprintf("Товар оплачен через RBK Money. Данные платежа - amount:{$amount}, currency:{$currency}, id:{$id}");

		$this->_shopOrder->paid();

		ob_start();
		$this->changedOrder('changeStatusPaid');
		ob_get_clean();

		return TRUE;
	}

	/**
	 * Печатает форму отправки запроса на сайт платёжной системы
	 */
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

		if (!is_null($oShop_Currency->id))
		{
			$serviceName = 'Оплата счета N ' . $oShop_Order->invoice;

			$aItems = $this->_getOrderItems($oShop_Order);

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
								data-invoice-id="<?php echo htmlspecialchars($invoice_id)?>"
								data-invoice-access-token="<?php echo htmlspecialchars($access_token)?>"
								data-payment-flow-hold="true"
								data-hold-expiration="capture"
								data-name="<?php echo htmlspecialchars(strval($aAnswer['invoice']['product']))?>"
								data-email="<?php echo htmlspecialchars($oShop_Order->email)?>"
								data-logo="https://checkout.rbk.money/images/logo.png"
								data-label="Оплатить с карты"
								data-description="<?php echo htmlspecialchars(strval($aAnswer['invoice']['description']))?>"
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

	/**
	 * Получаем товары из заказа
	 */
	protected function _getOrderItems($oShop_Order)
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
			$delta = $totalAmount - $calcAmount;

			end($aItems);
			$lastKey = key($aItems);

			$deltaPerOneItem = round($delta / $aItems[$lastKey]['quantity'], 2);

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

	/**
	 * Получаем сигнатуру из заголовка
	 */
	protected function _getSignatureFromHeader($contentSignature)
	{
		$signature = preg_replace("/alg=(\S+);\sdigest=/", '', $contentSignature);

		if (empty($signature)) {
			throw new Exception('Signature is missing');
		}

		return $signature;
	}

	/**
	 * Декодируем сигнатуру
	 */
	protected function _urlsafe_b64decode($string)
	{
		return base64_decode(strtr($string, '-_,', '+/='));
	}

	/**
	 * Проверяем сигнатуру
	 */
	protected function _verificationSignature($data, $signature, $public_key)
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