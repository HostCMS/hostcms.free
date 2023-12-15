<?php

require_once CMS_FOLDER . 'modules/vendor/Hmac.php';

/**
 * Prodamus
 */
class Shop_Payment_System_Handler56 extends Shop_Payment_System_Handler
{
	// Адрес платежной страницы
	protected $_url = 'http://demo.payform.ru/';

	// Секретный ключ
	protected $_secret_key = 'b1ce1276d066a5e6176527e831216e5703649e7c72c3530b2e756f3d73a077d6';

	// Код валюты в магазине HostCMS для валюты платежа
	protected $_prodamus_currency = 1;

	// Тестовая оплата при включенном рабочем режиме. 0 - выключен, 1 - включен
	protected $_demo_mode = 0;

	/*
	Признак способа расчёта.
	Возможные значения параметра:
		1 - полная предварительная оплата до момента передачи предмета расчёта;
		2 - частичная предварительная оплата до момента передачи
			предмета расчёта;
		3 - аванс;
		4 - полная оплата в момент передачи предмета расчёта;
		5 - частичная оплата предмета расчёта в момент его передачи
			с последующей оплатой в кредит;
		6 - передача предмета расчёта без его оплаты в момент
			его передачи с последующей оплатой в кредит;
		7 - оплата предмета расчёта после его передачи с оплатой в кредит.
	*/
	protected $payment_method = 4;

	/*
	Признак предмета расчёта.
	Возможные значения параметра:
		1 - товар;
		2 - подакцизный товар;
		3 - работа;
		4 - услуга;
		5 - ставка азартной игры;
		6 - выигрыш азартной игры;
		7 - лотерейный билет;
		8 - выигрыш лотереи;
		9 - предоставление РИД;
		10 - платёж;
		11 - агентское вознаграждение;
		12 - составной предмет расчёта;
		13 - иной предмет расчёта.
	*/
	protected $payment_object = 1;

	/*
	ставка НДС, с возможными значениями (при необходимоти заменить):
		0 – без НДС;
		1 – НДС по ставке 0%;
		2 – НДС чека по ставке 10%;
		3 – НДС чека по ставке 18%;
		4 – НДС чека по расчетной ставке 10/110;
		5 – НДС чека по расчетной ставке 18/118.
		6 - НДС чека по ставке 20%;
		7 - НДС чека по расчётной ставке 20/120.
	*/
	protected $prodamus_vat = array(
		0 => 0,
		10 => 2,
		18 => 3,
		20 => 6
	);

	protected $default_vat = 6;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		$headers = apache_request_headers();

		/*Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('Prodamus HEADERS: ' . var_export($headers, true));

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('Prodamus POST: ' . var_export($_POST, true));*/

		// Получаем ID заказа
		$order_id = Core_Array::getRequest('order_num', 0, 'int');

        if ($order_id)
        {
    		$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

    		if (!is_null($oShop_Order->id))
    		{
        		if (isset($headers['Sign']) && Hmac::verify($_POST, $this->_secret_key, $headers['Sign']))
        		{
      				// Вызов обработчика платежной системы
    				Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
    					->shopOrder($oShop_Order)
    					->paymentProcessing();

            		http_response_code(200);
            		echo 'success';
        		}
        		else
        		{
        			$oShop_Order->system_information = 'Prodamus хэш не совпал!';
        			$oShop_Order->save();

        			http_response_code(403);
        			echo "bad sign\n";
        		}
    		}

    		exit();
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

    /**
     * Вычисление суммы товаров заказа
     */
    public function getSumWithCoeff()
    {
        if ($this->_prodamus_currency > 0 && $this->_shopOrder->shop_currency_id > 0) {
            $sum = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
                $this->_shopOrder->Shop_Currency,
                Core_Entity::factory('Shop_Currency', $this->_prodamus_currency)
            );
        } else {
            $sum = 0;
        }

        return Shop_Controller::instance()->round($sum * $this->_shopOrder->getAmount());
    }

	public function getNotification()
	{
		$aItems = array();

		$aShop_Order_Items = $this->_shopOrder->Shop_Order_Items->findAll(FALSE);

		// Расчет сумм скидок, чтобы потом вычесть из цены каждого товара
		$discount = $amount = 0;
		foreach ($aShop_Order_Items as $key => $oShop_Order_Item)
		{
			// Товар в заказе не имеет статус отмененного
			if (!method_exists($oShop_Order_Item, 'isCanceled') || !$oShop_Order_Item->isCanceled())
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
		}

		$discount = $amount != 0
			? abs($discount) / $amount
			: 0;

		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			/*if (strpos('Доставка', $oShop_Order_Item->name) != false) {

			}*/

			// Товар в заказе не имеет статус отмененного
			if (!method_exists($oShop_Order_Item, 'isCanceled') || !$oShop_Order_Item->isCanceled())
			{
				$aItems[] = array(
					// id товара в системе интернет-магазина
					'sku' => $oShop_Order_Item->shop_item_id,
					'name' => mb_substr($oShop_Order_Item->name, 0, 128),
					'price' => number_format($oShop_Order_Item->getAmount() * ($oShop_Order_Item->type == 0 ? 1 - $discount : 1), 2, '.', ''),
					'quantity' => $oShop_Order_Item->quantity,
					'tax' => array(
					  'tax_type' => Core_Array::get($this->prodamus_vat, $oShop_Order_Item->rate, $this->default_vat)
					),
					'paymentMethod' => $this->payment_method,
					'paymentObject' => $this->payment_object,
				);
			}
		}

		$data = array(
			// Номер заказ в системе интернет-магазина
			'order_id' => $this->_shopOrder->id,

			// Мобильный телефон клиента
			'customer_phone' => $this->_shopOrder->phone,

			// E-mail адрес клиента
			'customer_email' => $this->_shopOrder->email,

			'products' => $aItems,

			// Для интернет-магазинов доступно только действие "Оплата"
			'do' => 'pay',

			// Код системы интернет-магазина, запросить у поддержки,
			'sys' => 'hostcms',

			// метод оплаты, выбранный клиентом
			// 	     если есть возможность выбора на стороне интернет-магазина,
			// 	     иначе клиент выбирает метод оплаты на стороне платежной формы
			//       варианты (при необходимости прописать значение):
			// 	AC - банковская карта
			// 	PC - Яндекс.Деньги
			// 	QW - Qiwi Wallet
			// 	WM - Webmoney
			// 	GP - платежный терминал
			// 	ACEURNMBX - Оплата в EUR картой всех стран мира, кроме РФ
			'payment_method' => 'AC',

			// тип плательщика, с возможными значениями:
			//     FROM_INDIVIDUAL - Физическое лицо
			//     FROM_LEGAL_ENTITY - Юридическое лицо
			//     FROM_FOREIGN_AGENCY - Иностранная организация
			//     (не обязательно. если форма работает в режиме самозанятого
			//      значение по умолчанию: FROM_INDIVIDUAL)
			'npd_income_type' => 'FROM_INDIVIDUAL'
		);

		$this->_demo_mode == 1 && $data['demo_mode'] = 1;

		$data['signature'] = Hmac::create($data, $this->_secret_key);

		$link = sprintf('%s?%s', $this->_url, http_build_query($data));

		ob_start();
		?><a class="btn btn-primary" target="_blank" href="<?php echo $link?>">Оплатить</a><?php

		return ob_get_clean();
	}

	/**
	 * Обработка ответа платёжного сервиса
	 */
	public function paymentProcessing()
	{
	    $this->processResult();

	    return true;
	}

	public function ProcessResult()
	{
		if ($this->_shopOrder->paid)
		{
			return FALSE;
		}

		$this->_shopOrder->system_information = sprintf("Товар оплачен через Prodamus.\n\n");
		$this->_shopOrder->paid();
		$this->setXSLs();
		$this->send();
	}
}