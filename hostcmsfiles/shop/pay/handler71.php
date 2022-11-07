<?php

/**
 * IntellectMoney
 */
class Shop_Payment_System_Handler71 extends Shop_Payment_System_Handler
{
	// Идентифкатор магазина, с которым будет работать обработчик
	private $_eShopId = 123456;

	// Секретный ключ (заполняется вручную пользователем на странице настроек магазина)
	private $_secretKey = "secret_key_string";

	// Валюта платежа RUR для работы или TST для тестирования системы
	private $_currencyName = "RUB";
	//private $_currencyName = "TST";

	// Код валюты в магазине HostCMS, которая была указана при регистрации магазина
	private $_intellectmoney_currency = 1;

	/* Отправлять данные для чеков (54-ФЗ) */
	protected $sendCheck = 0;

	/* Индивидуальный Номер Налогоплательщика, полученный в Федеральной налоговой службе */
	protected $inn = 'inn_string';

	/*
	Система налогообложения.
	Перечисление со значениями:
		0 => Common - Общая;
		1 => Simplified - Упрощенная доход, УСН доход;
		2 => SimplifiedMinusOutlay - Упрощенная доход минус расход, УСН доход - расход;
		3 => UnifiedImputedIncome - Единый налог на вмененный доход;
		4 => UnifiedAgricultural - Единый сельскохозяйственный налог;
		5 => Patent - Патентная система налогообложения;
	*/
	protected $sno = 0;

	/*
	Налог в ККТ по-умолчанию.
	Перечисление со значениями:
		1 => Vat20 - Ставка НДС 20%;
		2 => Vat10 - Ставка НДС 10%;
		3 => Vat120 - Ставка НДС расч. 20/120;
		4 => Vat110 - Ставка НДС расч. 10/110;
		5 => Vat0 - Ставка НДС 0%;
		6 => None - НДС не облагается;
	*/
	protected $default_vat = 6;

	/* Массив отношений ставки налога в заказе и названия налога (none, vat0, vat110 или vat120)*/
	protected $vat = array(
		0 => 6,
		10 => 4,
		20 => 3
	);

	/*
	Признак способа расчёта.
	Возможные значения параметра:
		1 => Prepay - Предоплата 100%;
		2 => PartialPrepay - Частичная предоплата;
		3 => Advance - Аванс;
		4 => Full - Полный расчёт;
		5 => PartialAndCredit - Частичный расчёт и кредит;
		6 => CreditTransfer - Передача в кредит;
		7 => CreditPayment - Оплата кредита;
	*/
	protected $payment_method = 4;

	/*
	Признак предмета расчёта.
	Возможные значения параметра:
		1 => Product - Товар;
		2 => Excisable - Подакцизный товар;
		3 => Job - Работа;
		4 => Service - Услуга;
		5 => GamblingBet - Ставка азартной игры;
		6 => GamblingGain - Выигрыш азартной игры;
		7 => LotteryTicket - Лотерейный билет;
		8 => LotteryWinnings - Выигрыш лотереи;
		9 => Rid - Предоставление РИД;
		10 => Payment - Платёж;
		11 => AgentComission - Агентское вознаграждение;
		12 => Composite - Составной предмет расчета;
		13 => Other - Иной предмет расчета;
	*/
	protected $payment_object = 1;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_REQUEST['orderId']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getRequest('orderId'));

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
		return Shop_Controller::instance()->round(($this->_intellectmoney_currency > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_intellectmoney_currency)
			)
			: 0) * $this->_shopOrder->getAmount() );
	}

	/* обработка ответа от платёжной системы */
	public function paymentProcessing()
	{
			$this->ProcessResult();

			return TRUE;
	}

	/* оплачивает заказ */
	public function ProcessResult()
	{
		if ($this->_shopOrder->paid
			|| is_null($paymentStatus = Core_Array::getPost('paymentStatus'))
			/*|| $paymentStatus != 5*/)
		{
			return FALSE;
		}

		if ($paymentStatus != 5)
		{
			echo "OK";
			die();
		}

		$eshopId = Core_Array::getPost('eshopId');
		$serviceName = Core_Array::getPost('serviceName');
		$eshopAccount = Core_Array::getPost('eshopAccount');
		$recipientAmount = Core_Array::getPost('recipientAmount');
		$recipientCurrency = Core_Array::getPost('recipientCurrency');
		$paymentStatus = Core_Array::getPost('paymentStatus');
		$userName = Core_Array::getPost('userName');
		$userEmail = Core_Array::getPost('userEmail');
		$paymentData = Core_Array::getPost('paymentData');

		$str_md5 = md5(sprintf("%s::%s::%s::%s::%s::%s::%s::%s::%s::%s::%s",
			$eshopId,$this->_shopOrder->id,$serviceName,
			$eshopAccount,$recipientAmount,$recipientCurrency,
			$paymentStatus,$userName,$userEmail,
			$paymentData,$this->_secretKey
		));

		if ($str_md5 == Core_Array::getPost('hash', ''))
		{
			$this->_shopOrder->system_information = sprintf("Товар оплачен через IntellectMoney.\n\nИнформация:\n\nID магазина: %sНомер покупки (СКО): %s\nНомер счета магазина: %s\nНазначение платежа: %s\nСумма платежа: %s\nВалюта платежа: %s\nИмя пользователя: %s\nE-mail пользователя: %s\nВремя выполнения платежа: %s\n",
				$eshopId,Core_Array::getPost('paymentId', ''),$eshopAccount,
				$serviceName,$recipientAmount,$recipientCurrency,
				$userName,$userEmail,$paymentData
			);

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
		}
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$order_sum = $this->getSumWithCoeff();

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_intellectmoney_currency);

		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$site_alias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';
		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://'.$site_alias.$shop_path.'cart/';

		if ($this->sendCheck)
		{
			$aShop_Order_Items = $this->_shopOrder->Shop_Order_Items->findAll(FALSE);

			// Расчет сумм скидок, чтобы потом вычесть из цены каждого товара
			$discount = $amount = 0;
			foreach ($aShop_Order_Items as $key => $oShop_Order_Item)
			{
				if ($oShop_Order_Item->price < 0)
				{
					$discount -= $oShop_Order_Item->getAmount();
					unset($aShop_Order_Items[$key]);
				}
				elseif ($oShop_Order_Item->type == 0)
				{
					$amount += $oShop_Order_Item->getAmount();
				}
			}

			$discount = $amount != 0
				? abs($discount) / $amount
				: 0;

			$aPositions = array();

			foreach ($aShop_Order_Items as $oShop_Order_Item)
			{
				$aPositions[] = array(
					'text' => mb_substr($oShop_Order_Item->name, 0, 128),
					'quantity' => $oShop_Order_Item->quantity,
					'tax' => Core_Array::get($this->vat, $oShop_Order_Item->rate, $this->default_vat),
					'price' => number_format($oShop_Order_Item->getAmount() * ($oShop_Order_Item->type == 0 ? 1 - $discount : 1), 2, '.', ''),
					'paymentMethodType' => $this->payment_method,
					'paymentSubjectType' => $this->payment_object
				);
			}

			$merchantReceipt = array(
				'inn' => $this->inn,
				'group' => "Main",
				'content' => array(
					'checkClose' => array(
						'payments' => array(
							array(
								'type' => 2,
								'amount' => $order_sum
							)
						),
						'taxationSystem' => $this->sno
					),
					'type' => 1,
					'positions' => $aPositions,
					'customerContact' => $this->_shopOrder->email
				)
			);

			$sMerchantReceiptJson = json_encode($merchantReceipt);
		}

		if(!is_null($oShop_Currency->id))
		{
			?>
			<h1>Оплата через систему IntellectMoney</h1>
			<!-- Форма для оплаты через IntellectMoney -->
			<p>К оплате <strong><?php echo $order_sum . " " . $oShop_Currency->name?></strong></p>
			<form id="pay" name="pay" method="post" action="https://merchant.intellectmoney.ru/ru/">
				<input id="orderId" type="hidden" name="orderId" value="<?php echo $this->_shopOrder->id?>">
				<input id="eshopId" type="hidden" name="eshopId" value="<?php echo $this->_eShopId?>">
				<input id="serviceName" type="hidden" name="serviceName" value="Оплата заказа № <?php echo $this->_shopOrder->id?>">
				<input id="recipientAmount" type="hidden" name="recipientAmount" value="<?php echo $order_sum?>">
				<input id="recipientCurrency" type="hidden" name="recipientCurrency" value="<?php echo $this->_currencyName?>">
				<input id="successUrl" type="hidden" name="successUrl" value="<?php echo $handler_url."?orderId={$this->_shopOrder->invoice}&payment=success"?>">
				<input id="failUrl" type="hidden" name="failUrl" value="<?php echo $handler_url."?orderId={$this->_shopOrder->invoice}&payment=fail"?>">
				<input id="userName" type="hidden" name="userName" value="<?php echo implode(' ', array($this->_shopOrder->surname, $this->_shopOrder->name, $this->_shopOrder->patronymic))?>">
				<input id="userEmail" type="hidden" name="userEmail" value="<?php echo $this->_shopOrder->email?>">
				<?php if ($this->sendCheck){ ?> <input type="hidden" name="merchantReceipt" value="<?php echo htmlspecialchars($sMerchantReceiptJson)?>"><?php } ?>
				<input name="submit" value="Перейти к оплате" type="submit"/>
			</form>
			<?php
		}
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}