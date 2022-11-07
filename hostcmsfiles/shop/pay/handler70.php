<?php

/**
 * Оплата через SMS сервиса AvisoSMS
 *
 * Указываем в коде данные для доступа к Aviso:
 *
 * // логин в системе AvisoSMS
 * private $_username = '';
 *
 * // SECURE_HASH (хэш код)
 * private $_secure_hash = '';

 * // ID сервиса
 * private $_service_id = '';
 *
 * // текст в SMS  после успешной оплаты
 * private $_success_message = 'Заказ оплачен';
 *
 * // false - для боевых платежей
 * private $_test = true;
 *
 * Обработчик платежей, указывается в Панели AvisoSMS
 * http://<ваш_сайт>/shop/cart/?aviso=1
 */
class Shop_Payment_System_Handler70 extends Shop_Payment_System_Handler
{
	// логин в системе AvisoSMS
	private $_username = '';

	// SECURE_HASH (хэш код)
	private $_secure_hash = '';

	// ID сервиса
	private $_service_id = '';

	// текст в SMS  после успешной оплаты
	private $_success_message = 'Заказ оплачен';

	// false - для боевых платежей
	private $_test = true;

	private $_currency_id = 1;

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
		return Shop_Controller::instance()->round(($this->_currency_id > 0
			&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount());
	}

	/* проверка оплаты платежа, оплачивает заказ */
	public function paymentProcessing()
	{
		$oShop_Order = Core_Entity::factory('Shop_Order')->find($_POST['merchant_order_id']);

		if(is_null($oShop_Order->id) || $oShop_Order->paid)
		{
			return FALSE;
		}

		$ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';

		$result = array(
			'error' => 1,
			'text' => '',
		);

		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if (isset($_POST['avisosms']))
			{
				ob_clean();
				die('checkout');

			}
			elseif (isset($_POST['avisosms_to']))
			{
				$phone = preg_replace('|[^0-9]+|si', '', $_POST['avisosms_to']);

				if (strlen($phone) == 11)
				{
					$description = 'Оплата заказа #' . $this->_shopOrder->id;

					$m_commerce = new AvisosmsMCommerce($this->_username, $this->_secure_hash, $this->_service_id);

					$m_commerce->test = $this->_test;

					$sum = $this->getSumWithCoeff();

					if ($m_commerce->createOrder($description, $sum, $this->_success_message, $phone, $this->_shopOrder->id)) {

						$result['text'] = 'На номер: ' . htmlspecialchars($_POST['avisosms_to']) . ' выслано смс с подтверждением платежа. Подтвердите платеж ответив на это смс. Ответ бесплатный. Не закрывайте окно браузера до завершения операции.';

						$result['error'] = 0;
					}
					else
					{
						$result['text'] = 'Ошибка! ';
						$result['text'] .= $m_commerce->error_message();
					}
				}
				else
				{
					$result['text'] = 'Ошибка! Некорректный номер (' . $_POST['avisosms_to'] . ') телефона.';
				}

				if ($ajax)
				{
					ob_clean();
					die(json_encode($result));
				}
			}
			else
			{
				$m_commerce = new AvisosmsMCommerce($this->_username, $this->_secure_hash, $this->_service_id);

				if ($m_commerce->updateOrderStatus()) {

					$oShop_Order->system_information = "Товар оплачен через AvisoSMS.\n";
					$oShop_Order->paid();

					$this->setXSLs();
					$this->send();
				}
			}
		}
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$sum = $this->getSumWithCoeff();

		$oShop_Currency = Core_Entity::factory('Shop_Currency')->find($this->_currency_id);

		if(!is_null($oShop_Currency->id))
		{

			$phone = preg_replace('|[^0-9+-]+|si', '', $this->_shopOrder->phone);

			$form = '
<script>
$(function() {
	$("form.avisosms").live("submit", function() {
		$.post(location.href, $(this).serialize(), function (data) {
			var d = $.parseJSON(data);
			if (d["error"] == 0) {
				$("div.avisosms").html( d["text"] + "<p></p>" );
				$("img.avisosms").show();
				$("form.avisosms").hide();
				aviso_check();
			} else {
				$("p.avisosms").html( d["text"] );
			}
		});
		return false;
	});
});

function aviso_check() {
	$.post(location.href, {avisosms: 0, merchant_order_id: ' . $this->_shopOrder->id . '}, function(data) {
		if (data == "checkout") { } else {
			$("img.avisosms").hide();
			$("div.avisosms").html("Ваш платеж успешно подтвержден. Спасибо за покупку!");
			location.href = "?payment=success&order_id=' . $this->_shopOrder->id . '";
		}
	});

	if ($("img.avisosms").is(":visible"))
		setTimeout("aviso_check()", 5000);
}
</script>
	<h1>Оплата со счета мобильного телефона</h1>
	<p>Номер вашего заказа: <strong>№ ' . $this->_shopOrder->id . '</strong></p>
	<p>Сумма к оплате составляет <strong>' . $sum . ' ' . $oShop_Currency->name . '</strong></p>

	' . ($this->_shopOrder->paid ? '
	<p>Ваш заказ уже оплачен.</p>
	' : '
	<form method="POST" class="avisosms">
	<p>
		Проверьте номер телефона и нажмите кнопку "Оплатить".
	</p>
	<p>
		На этот номер телефона придет SMS для подтверждения платежа
	</p>
	<p class="avisosms"></p>
	<p>
    	<input type="text" name="avisosms_to" value="' . $phone . '">
  	</p>
  	<p>
  		<input type="submit" value="Оплатить">
  	</p>
  	<input type="hidden" name="merchant_order_id" value="' . $this->_shopOrder->id . '">
  	</form>

 	<div class="avisosms"></div>
	<img class="avisosms" style="display:none" alt="Ждем ответ от сервера..." title="Ждем ответ от сервера..." width="124" height="124"
			  src="data:image/gif;base64,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" />

');
			echo $form;
		}
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}

/**
 * Класс для работы с сервисом AvisoSMS Мобильная коммерция
 *
 * Документация: http://avisosms.ru/m-commerce/api/
 *
 * @author
 * @copyright
 * @license
 */
class AvisosmsMCommerce {

	/**
	 * @var  Ссылка до API
	 */
	public $url         = 'https://api.avisosms.ru/mc/';

	/**
	 * @var  Имя пользователя в системе AvisoSMS
	 */
	public $username    = NULL;

	/**
	 * @var  Ключ доступа. Указывается в настройках аккаунта (Настройки удалённого доступа)
	 */
	public $secure_hash  = NULL;

	/**
	 * @var  ID сервиса
	 */
	public $service_id  = NULL;

	/**
	 * @var  Время ожидания ответа от сервера в секундах
	 */
	public $timeout     = 10;

	/**
	 * @var  Кодировка приложения
	 */
	public $out_charset = 'UTF-8';

	/**
	 * @var  Расшифровка статусов заказов.
	 */
	public $order_status    = array(
		'success' => 'Заказ успешно оплачен',
		'failure' => 'Заказ не был оплачен',
		'cancel'  => 'Заказ был отменён пользователем со стороны сотового оператора',
		'pending' => 'Заказ обрабатывается',
	);

	/**
	 * @var  Расшифровка статусов.
	 */
	private $_status    = array(
		'0' => 'Нет ошибок. Операция произведена успешно',
		'1' => 'Неожиданная ошибка. Этой ошибки быть не должно',
		'2' => 'Эта ошибка может возникнуть, если для данного номера не доступна услуга мобильной коммерции',
		'3' => 'Некоторые параметры переданы неверно или не переданы',
		'4' => 'Ошибка авторизации',
		'5' => 'Ошибка проверки цифровой подписи',
		'255' => 'Ошибка соединения с сервером',
	);

	private $_response = NULL;
	private $_error_message = NULL;

	/**
	 * @var  Режим тестирования
	 */
	public $test        = FALSE;
	public $debug_text  = '';

	/**
	 * @var  Кодировка скрипта
	 */
	const CHARSET = 'UTF-8';

	/**
	 * Конструктор
	 *
	 *      // Создаем новый объект для работы с avisosms m_commerce
	 *      $m_commerce = new avisosms_m_commerce($username, $secure_hash, $service_id);
	 *      // Включаем тестовый режим
	 *      $m_commerce->test = TRUE;
	 *      $m_commerce->debug = TRUE;
	 *
	 * @param   string  Имя пользователя в системе AvisoSMS.
	 * @param   string  Ключ доступа. Указывается в настройках платформы.
	 * @param   string  ID сервиса. Указывается в личном кабинете.
	 *
	 * @return  boolean TRUE
	 */
	public function __construct($username, $secure_hash, $service_id)
	{
		$this->username     = $username;
		$this->secure_hash  = $secure_hash;
		$this->service_id   = $service_id;
		return TRUE;
	}

	/**
	 * Создание заказа
	 *
	 *      if ($m_commerce->createOrder($description, $price, $success_message, $phone, ''))
	 *      {
	 *          // Заказ создан успешно (status = 0)
	 *          $response = $m_commerce->response();
	 *          var_dump($response);
	 *      }
	 *      else
	 *      {
	 *          // Ошибка создания заказа (status > 0)
	 *          echo 'Ошибка: '.$m_commerce->error_message();
	 *          var_dump($m_commerce->response());
	 *      }
	 *
	 * @param   string  Описание заказа. Максимальная длина 100 символов, минимальная - 10.
	 * @param   string  Сумма заказа. Дробные числа указываются через точку. Максимум до сотых долей.
	 * @param   string  Сообщение, отправляемое пользователю, в случае успешного завершения оплаты.
	 * @param   string  Телефон абонента.
	 * @param   string  Необязательный параметр. ID платежа в системе магазина. До 100 знаков.
	 *
	 * @return  boolean Возвращает TRUE, если status = 0, иначе FALSE
	 */
	public function createOrder($description, $price, $success_message, $phone, $merchant_order_id = '')
	{
		$data = array(
			'description'       => $description,
			'price'             => (float)number_format($price, 2, '.', ''),
			'success_message'   => $success_message,
			'phone'             => $phone,
			'merchant_order_id' => $merchant_order_id,
		);
		return $this->send($data, 'create_order');
	}

	/**
	 * Запрос статуса заказа
	 *
	 *      if ($m_commerce->getOrderStatus('4d2c8957f612fc6f3c0003e4'))
	 *      {
	 *          // Данные получены успешно (status = 0)
	 *          $response = $m_commerce->response();
	 *          var_dump($response);
	 *      }
	 *      else
	 *      {
	 *          // Ошибка получение данных (status > 0)
	 *          echo 'Ошибка: '.$m_commerce->error_message();
	 *          var_dump($m_commerce->response());
	 *      }
	 *
	 * @param   string  ID заказа
	 *
	 * @return  boolean Возвращает TRUE, если status = 0, иначе FALSE
	 */
	public function getOrderStatus($phone, $order_id)
	{
		$data = array(
			'phone'             => $phone,
			'order_id'          => $order_id,
		);
		return $this->send($data, 'get_order_info');
	}

	/**
	 * Уведомление о статусе
	 *
	 *      if ($m_commerce->updateOrderStatus())
	 *      {
	 *          // Данные получены, проверка secure_hash пройдена
	 *          // Можно обрабатывать полученные данные
	 *          $response = $m_commerce->response();
	 *          var_dump($response);
	 *      }
	 *      else
	 *      {
	 *          // Переданные данные не верны.
	 *          die('Ошибка: '.$m_commerce->error_message());
	 *      }
	 *
	 * @param   array  Массив с переданными данными (если получаются самостоятельно)
	 *
	 * @return  boolean Возвращает TRUE, если status = 0, иначе FALSE
	 */
	public function updateOrderStatus($data = NULL)
	{
		$options = array(
			'order_id' => '',
			'order_status' => '',
			'phone' => '',
			'sign' => '',
			'merchant_order_id' => '',
		);

		if (is_null($data))
		{
			$data = file_get_contents("php://input");
			$data = json_decode($data);
		}
		//phone, order_status, service_id, username и SECURE_HASH

		$this->_response = array_merge($options, (array) $data);

		//phone, order_status, service_id, username и SECURE_HASH
		$signatureString = $this->_response['phone'] . $this->_response['order_status'] . $this->service_id . $this->username . $this->secure_hash;
		$signature = md5 ( $signatureString );

		$this->debug_text .= 'Полученные данные: <pre>' . print_r($data, true). '</pre><br />';
		$this->debug_text .= 'Ожидаемая цифровая подпись: ' . $signature . '<br />';
		$this->debug_text .= 'Полученная цифровая подпись: ' . $this->_response['sign'] . '<br />';
		$this->debug_text .= 'Цифровая подпись генерирутся из строки: ' . $signatureString . '<br />';

		if (empty($this->_response['sign']) || $this->_response['sign'] != $signature) {
			$this->_error_message = 'Неверный ключ доступа.';
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * Обращение к API
	 *
	 * @param   array       Массив с данными
	 * @param   string      Название функции
	 * @return  boolean Возвращает TRUE, если status = 0, иначе FALSE
	 */
	public function send($data, $postfix)
	{
		if ($this->test) {
			$data['test'] = true;
		}

		if ($this->out_charset <> self::CHARSET) {
			foreach($data as $k => $v) {
				$data[$k] = iconv($this->out_charset, self::CHARSET, $v);
			}
		}

		$data['username'] = $this->username;
		$data['service_id'] = $this->service_id;
		$data['access_key'] = '';
		$data['sign'] = md5($data['phone'] . $data['service_id'] . $data['username'] . $this->secure_hash);

		$url = $this->url.$postfix.'/';
		$json_data = json_encode($data);

		$this->debug_text .= 'Запрос: <b>' . $postfix . '</b><br />';
		//$this->debug_text .= '<pre>' . print_r($data, true) . '</pre>';
		$this->debug_text .= 'Цифровая подпись: '. $data['sign'] . '<br />';
		$this->debug_text .= 'Строка для подписи: '. ($data['phone'] . $data['service_id'] . $data['username'] . $this->secure_hash). '<br />';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_COOKIE, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

		$result = curl_exec($ch);

		$this->debug_text .= 'Передаем запрос '.$url.': <br><pre>'.print_r($data, true).'</pre>';
		$this->debug_text .= 'Получаем ответ: <br><pre>'.print_r(json_decode($result), true).'</pre>';
		$this->_response = array('status' => 255);

		//echo $this->debug_text;
		if (curl_errno($ch))
		{
			$this->_error_message = curl_error($ch);
		}
		else
		{
			$this->_response = array_merge($this->_response, (array)json_decode($result, TRUE));
			$this->_error_message = $this->_status[$this->_response['status']];
		}

		curl_close($ch);
		return !$this->_response['status'];
	}

	/**
	 * Возвращает ответ от сервера
	 *
	 * @return  array   Ответ от сервера
	 */
	public function response()
	{
		$data = $this->_response;
		if ($this->out_charset <> self::CHARSET) foreach($data as $k => $v) {
			$data[$k] = iconv(self::CHARSET, $this->out_charset, $v);
		}
		return $data;
	}

	/**
	 * Возвращает текст ошибки
	 *
	 * @return  string   Текст ошибки
	 */
	public function error_message()
	{
		return ($this->out_charset == self::CHARSET) ? $this->_error_message : iconv(self::CHARSET, $this->out_charset, $this->_error_message);
	}

	/**
	 * Возвращает текстовый статус заказа
	 *
	 * @param   string      Статус
	 * @return  string
	 */
	public function order_status($status)
	{
		if (!isset($this->order_status[$status])) {
			return NULL;
		}
		return ($this->out_charset == self::CHARSET) ? $this->order_status[$status] : iconv(self::CHARSET, $this->out_charset, $this->order_status[$status]);
	}
}