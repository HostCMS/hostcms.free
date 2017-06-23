<?php

/**
 * LiqPay
 */
class Shop_Payment_System_Handler20 extends Shop_Payment_System_Handler
{
	protected $_public_key = '';
	protected $_private_key = '';

	/**
	 * Международное название валюты из списка валют магазина
	 * @var string
	 */
	protected $_currency_name = 'RUB';

	/**
	 * Идентификатор валюты
	 * @var string
	 */
	protected $_currency_id = 1;

	public function __construct(Shop_Payment_System_Model $oShop_Payment_System_Model)
	{
		parent::__construct($oShop_Payment_System_Model);
		$oCurrency = Core_Entity::factory('Shop_Currency')->getByCode($this->_currency_name);
		!is_null($oCurrency) && $this->_currency_id = $oCurrency->id;
	}

	/**
	 * Метод, вызываемый в коде ТДС через Shop_Payment_System_Handler::checkAfterContent($oShop);
	 */
	public function checkPaymentAfterContent()
	{
		if (isset($_REQUEST['order_id']))
		{
			// Получаем ID заказа
			$aTmpExplode = explode('_', (Core_Array::getRequest('order_id')));
			if(count($aTmpExplode) == 3)
			{
				$order_id = $aTmpExplode[2];

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
	}

	/**
	 * Метод, запускающий выполнение обработчика
	 * @return self
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

	/*
	 * Обработка статуса оплаты
	 */
	function ProcessResult()
	{
		if($this->_shopOrder->paid)
		{
			return FALSE;
		}

		$public_key = $_POST['public_key'];
		$amount = $_POST['amount'];
		$currency = $_POST['currency'];
		$description = $_POST['description'];
		$order_id = $_POST['order_id'];
		$type = $_POST['type'];
		$status = $_POST['status'];

		$liqpay = new LiqPay($this->_public_key, $this->_private_key);
		$our_signature = $liqpay->cnb_signature(array(
			'amount'         => $amount,
			'currency'       => $currency,
			'description'    => $description,
			'order_id'       => $order_id,
			'type'           => $type
		));

		$lp_signature = $_POST['signature'];

		$status_t = array('success'=>'успешный платеж','failure'=>'неуспешный платеж','wait_secure'=>'платеж на проверке','sandbox'=>'тестовый платеж');
		$r_stat = $status_t[$status];

		if($lp_signature != '' && $our_signature == $lp_signature && $status == 'success')
		{
			$this->_shopOrder->system_information = sprintf("Заказ оплачен через LiqPay\n\nID платежа в системе LiqPay:\t{$_POST['transaction_id']}\nТелефон плательщика в международном формате:\t{$_POST['sender_phone']}\nСтатус платежа:\t{$r_stat}\n\n");

			$this->_shopOrder->paid();
			$this->setXSLs();
			$this->send();
		}
		else
		{
			$this->_shopOrder->system_information = sprintf("Заказ НЕ оплачен через LiqPay\n\nID платежа в системе LiqPay:\t{$_POST['transaction_id']}\nТелефон плательщика в международном формате:\t{$_POST['sender_phone']}\nСтатус платежа:\t{$r_stat}\n\n");
			$this->_shopOrder->save();
		}
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

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

	public function getNotification()
	{
		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
		$sum = $this->getSumWithCoeff();

		if (is_null($oSite_Alias))
		{
			throw new Core_Exception('Site does not have default alias!');
		}

		$shop_path = $this->_shopOrder->Shop->Structure->getPath();
		$handler_url = 'http://' . $oSite_Alias->name . $shop_path . "cart/";

		// формируем форму оплаты
		$liqpay = new LiqPay($this->_public_key, $this->_private_key);
		echo $liqpay->cnb_form(array(
			'amount'      => $sum,
			'currency'    => $this->_currency_name,
			'description' => "Оплата заказа №{$this->_shopOrder->id}",
			'order_id'    => "order_id_{$this->_shopOrder->id}",
			'type'        => 'buy',
			//'sandbox'     => 1,
			'server_url'  => $handler_url
		));
	}
}

class LiqPay
{
    protected $_supportedCurrencies = array('EUR','UAH','USD','RUB','RUR');

    protected $_supportedParams = array('public_key','amount','currency','description',
			'order_id','result_url','server_url','type',
			'signature','language','sandbox');
    private $_public_key;
    private $_private_key;
    public function __construct($public_key, $private_key)
    {
			if (empty($public_key)) {
					throw new Exception('public_key is empty');
			}

			if (empty($private_key)) {
					throw new Exception('private_key is empty');
			}

			$this->_public_key = $public_key;
			$this->_private_key = $private_key;
    }
    public function api($url, $params = array())
    {
			$url = 'https://www.liqpay.com/api/'.$url;

			$public_key = $this->_public_key;
			$private_key = $this->_private_key;
			$data = json_encode(array_merge(compact('public_key'), $params));
			$signature = base64_encode(sha1($private_key.$data.$private_key, 1));
			$postfields = "data={$data}&signature={$signature}";

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$postfields);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

			$server_output = curl_exec($ch);

			curl_close($ch);

			return json_decode($server_output);
    }
    public function cnb_form($params)
    {
			$public_key = $params['public_key'] = $this->_public_key;
			$private_key = $this->_private_key;

			if (!isset($params['amount'])) {
				throw new Exception('Amount is null');
			}
			if (!isset($params['currency'])) {
				throw new Exception('Currency is null');
			}
			if (!in_array($params['currency'], $this->_supportedCurrencies)) {
				throw new Exception('Currency is not supported');
			}
			if ($params['currency'] == 'RUR') {
				$params['currency'] = 'RUB';
			}
			if (!isset($params['description'])) {
				throw new Exception('Description is null');
			}

			$params['signature'] = $this->cnb_signature($params);

			$language = 'ru';
			if (isset($params['language']) && $params['language'] == 'en') {
				$language = 'en';
			}

			$inputs = array();
			foreach ($params as $key => $value) {
				if (!in_array($key, $this->_supportedParams)) {
						continue;
				}
				$inputs[] = sprintf('<input type="hidden" name="%s" value="%s" />', $key, $value);
			}

			return sprintf('
						<form method="post" action="https://www.liqpay.com/api/pay" accept-charset="utf-8">
								%s
								<input type="image" src="//static.liqpay.com/buttons/p1%s.radius.png" name="btn_text" />
						</form>
				',
				join("\r\n", $inputs),
				$language
			);
    }
    public function cnb_signature($params)
    {
			$public_key = $params['public_key'] = $this->_public_key;
			$private_key = $this->_private_key;

			if ($params['currency'] == 'RUR') {
				$params['currency'] = 'RUB';
			}

			$amount = $params['amount'];
			$currency = $params['currency'];
			$description = $params['description'];

			$order_id = '';
			if (isset($params['order_id'])) {
				$order_id = $params['order_id'];
			}

			$type = '';
			if (isset($params['type'])) {
				$type = $params['type'];
			}

			$result_url = '';
			if (isset($params['result_url'])) {
				$result_url = $params['result_url'];
			}

			$server_url = '';
			if (isset($params['server_url'])) {
				$server_url = $params['server_url'];
			}

			$signature = $this->str_to_sign(
				$private_key.
				$amount.
				$currency.
				$public_key.
				$order_id.
				$type.
				$description.
				$result_url.
				$server_url
			);

			return $signature;
    }
    public function str_to_sign($str)
    {
			$signature = base64_encode(sha1($str,1));
			return $signature;
    }
}