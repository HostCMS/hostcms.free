<?php

/**
 * PayAnyWay
 *
 *
 * Для установки платежного модуля PayAnyWay необходимо произвести следующие действия:
 * 1. В код настроек типовой динамической страницы корзины добавляем содержимое файла page_config.php
 * 2. В код типовой динамической страницы корзины добавляем содержимое файла page.php
 * 3. Добавляем обработчик платежной системы. Вставляем полностью код из файла payanyway.php
 *    Внимание! Имя класса зависит от идентификатора платежной системы, например, для платежной системы 23 имя будет
 *    class Shop_Payment_System_Handler23 extends Shop_Payment_System_Handler
 *
 *  Измените настройки модуля PayAnyWay:
 *
 *     Номер счета - номер счета в платежной системе PayAnyWay
 *     Код проверки целостности данных - должен совпадать с кодом, указанным в настройках счета
 *     Тестовый режим - идентификатор режима ("0" или "1")
 *     URL сервера оплаты - возможны два варианта:
 * 	- demo.moneta.ru (для тестового аккаунта на demo.moneta.ru)
 * 	- www.payanyway.ru (для рабочего аккаунта в платежной системе PayAnyWay)
 *
 * 4. Зайдите в ваш акаунт в платежной системе и перейдите в раздел «Счета» -> «Управление» -> «Редактировать счет»
 *    Впишите следующий адрес в поле «Pay URL»: http://имя_вашего_сайта/shop/cart/
 *
 * Удачных платежей!
 *
 */
class Shop_Payment_System_Handler23 extends Shop_Payment_System_Handler
{
	public $_rub_currency_id = 1;

	/* Номер счета в платежной системе PayAnyWay */
	private $_MNT_ID = '11493408';

	/* Код проверки целостности данных */
	private $_MNT_DATAINTEGRITY_CODE = '12345';

	/* URL сервера оплаты */
	private $_MNT_PAYMENT_URL = 'www.payanyway.ru';

	/* Тестовый режим, 0 - обычный, 1 - тестовый */
	private $_MNT_TEST_MODE = '0';

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_REQUEST['MNT_OPERATION_ID']))
		{
			// Получаем ID заказа
			$order_id = intval(Core_Array::getRequest('MNT_TRANSACTION_ID'));

			$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

			if (!is_null($oShop_Order->id))
			{
				// Вызов обработчика платежной системы
				Shop_Payment_System_Handler::factory($oShop_Order->Shop_Payment_System)
					->shopOrder($oShop_Order)
					->paymentProcessing();
			}
			exit();
		}
	}

	/* Вызывается на 4-ом шаге оформления заказа*/
	public function execute()
	{
		parent::execute();

		$this->printNotification();

		return $this;
	}

	public function paymentProcessing()
	{
		/* обработка ответа от платёжной системы */
		if (isset($_REQUEST['MNT_OPERATION_ID']))
		{
			$this->ProcessResult();
			return true;
		}
		else
		{
			$this->ShowResultMessage();
			return true;
		}
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

	/* оплачивает заказ */
	function ProcessResult()
	{
		$order_id = intval(Core_Array::getRequest('MNT_TRANSACTION_ID'));
		$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);
		if ($oShop_Order)
		{
			$signature = md5(
				Core_Array::getRequest('MNT_ID') .
				Core_Array::getRequest('MNT_TRANSACTION_ID') .
				Core_Array::getRequest('MNT_OPERATION_ID') .
				Core_Array::getRequest('MNT_AMOUNT') .
				Core_Array::getRequest('MNT_CURRENCY_CODE') .
				Core_Array::getRequest('MNT_TEST_MODE') .
				$this->_MNT_DATAINTEGRITY_CODE
			);

			if (Core_Array::getRequest('MNT_SIGNATURE') == $signature)
			{
				$Sum = number_format($this->getSumWithCoeff(), 2, '.', '');
				if ($Sum == Core_Array::getRequest('MNT_AMOUNT'))
				{
					$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

					$oShop_Order->system_information = "Товар оплачен через PayAnyWay.\n";
					$oShop_Order->paid();

					ob_start();
					$this->changedOrder('changeStatusPaid');
					ob_get_clean();

					// данные для кассы
					$kassa_inventory = null;
					$kassa_customer = null;
					$kassa_delivery = null;

					$kassa_customer = $oShop_Order->email;
					$inventory = array();
					$aShopOrderItems = $oShop_Order->Shop_Order_Items->findAll();
					if(count($aShopOrderItems))
					{
						foreach ($aShopOrderItems as $key => $oShopOrderItem)
						{
							$sItemAmount = $oShopOrderItem->getAmount();
							$inventory[] = array("name" => str_replace('&quot;', "'", htmlspecialchars($oShopOrderItem->name)), "price" => floatval(number_format($sItemAmount, 2, '.', '')), "quantity" => $oShopOrderItem->quantity, "vatTag" => 1105);
						}
						$kassa_inventory = json_encode($inventory);
					}

					// сформировать xml ответ
					if (is_array($inventory) && count($inventory) && $kassa_inventory) {
						header("Content-type: application/xml");
						$resultCode = 200;
						$signature = md5($resultCode . Core_Array::getRequest('MNT_ID') . Core_Array::getRequest('MNT_TRANSACTION_ID') . $this->_MNT_DATAINTEGRITY_CODE);
						$result = '<?xml version="1.0" encoding="UTF-8" ?>';
						$result .= '<MNT_RESPONSE>';
						$result .= '<MNT_ID>' . Core_Array::getRequest('MNT_ID') . '</MNT_ID>';
						$result .= '<MNT_TRANSACTION_ID>' . Core_Array::getRequest('MNT_TRANSACTION_ID') . '</MNT_TRANSACTION_ID>';
						$result .= '<MNT_RESULT_CODE>' . $resultCode . '</MNT_RESULT_CODE>';
						$result .= '<MNT_SIGNATURE>' . $signature . '</MNT_SIGNATURE>';

						if ($kassa_inventory || $kassa_customer || $kassa_delivery) {
							$result .= '<MNT_ATTRIBUTES>';
						}

						if ($kassa_inventory) {
							$result .= '<ATTRIBUTE>';
							$result .= '<KEY>INVENTORY</KEY>';
							$result .= '<VALUE>' . $kassa_inventory . '</VALUE>';
							$result .= '</ATTRIBUTE>';
						}

						if ($kassa_customer) {
							$result .= '<ATTRIBUTE>';
							$result .= '<KEY>CUSTOMER</KEY>';
							$result .= '<VALUE>' . $kassa_customer . '</VALUE>';
							$result .= '</ATTRIBUTE>';
						}

						if ($kassa_delivery) {
							$result .= '<ATTRIBUTE>';
							$result .= '<KEY>DELIVERY</KEY>';
							$result .= '<VALUE>' . $kassa_delivery . '</VALUE>';
							$result .= '</ATTRIBUTE>';
						}

						if ($kassa_inventory || $kassa_customer || $kassa_delivery) {
							$result .= '</MNT_ATTRIBUTES>';
						}

						$result .= '</MNT_RESPONSE>';
					}
					else {
						$result = "SUCCESS";
					}

					die($result);

				}
				else
				{
					die('FAIL');
				}
			}
			else
			{
				die('FAIL');
			}
		}
		else
		{
			die('FAIL');
		}
	}

	// Вывод сообщения об успешности/неуспешности оплаты
	function ShowResultMessage()
	{
		$oShop_Order = Core_Entity::factory('Shop_Order')->find(Core_Array::getRequest('MNT_TRANSACTION_ID', 0));

		if(is_null($oShop_Order->id))
		{
			// Заказ не найден
			return FALSE;
		}

		$sStatus = $oShop_Order->paid == 1 ? "оплачен" : "не оплачен";

		?><h1>Заказ <?php echo $sStatus?></h1>
		<p>Заказ <strong>№ <?php echo $oShop_Order->invoice?></strong> <?php echo $sStatus?>.</p>
		<?php
	}

	private function cleanProductName($value)
	{
		$result = preg_replace('/[^0-9a-zA-Zа-яА-Я ]/ui', '', htmlspecialchars_decode($value));
		$result = trim(mb_substr($result, 0, 12));
		return $result;
	}

	/* печатает форму отправки запроса на сайт платёжной системы */
	public function getNotification()
	{
		$Sum = number_format($this->getSumWithCoeff(), 2, '.', '');
		$Signature = md5("{$this->_MNT_ID}{$this->_shopOrder->id}{$Sum}{$this->_shopOrder->Shop_Currency->code}{$this->_MNT_TEST_MODE}{$this->_MNT_DATAINTEGRITY_CODE}");

		$clientEmail = $this->_shopOrder->email;
		$inventory = array();
		$aShopOrderItems = $this->_shopOrder->Shop_Order_Items->findAll();
		if(count($aShopOrderItems))
		{
			foreach ($aShopOrderItems as $key => $oShopOrderItem)
			{
				$sItemAmount = $oShopOrderItem->getAmount();
				$inventory[] = array("n" => $this->cleanProductName($oShopOrderItem->name), "p" => floatval(number_format($sItemAmount, 2, '.', '')), "q" => $oShopOrderItem->quantity, "t" => 1105);
			}
		}

		$data = array("customer" => $clientEmail, "items" => $inventory);

		$jsonData = preg_replace_callback('/\\\\u(\w{4})/', function ($matches) {
			return html_entity_decode('&#x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
		}, json_encode($data));

		?>
		<h2>Оплата через систему PayAnyWay</h2>
		<form method="POST" action="https://<?php echo $this->_MNT_PAYMENT_URL; ?>/assistant.htm">
			<input type="hidden" name="MNT_ID" value='<?php echo $this->_MNT_ID; ?>'>
			<input type="hidden" name="MNT_TRANSACTION_ID" value='<?php echo $this->_shopOrder->id; ?>'>
			<input type="hidden" name="MNT_CURRENCY_CODE" value='<?php echo $this->_shopOrder->Shop_Currency->code; ?>'>
			<input type="hidden" name="MNT_AMOUNT" value='<?php echo $Sum; ?>'>
			<input type="hidden" name="MNT_TEST_MODE" value='<?php echo $this->_MNT_TEST_MODE; ?>'>
			<input type="hidden" name="MNT_SIGNATURE" value='<?php echo $Signature; ?>'>
			<input type="hidden" name="MNT_CUSTOM1" value='<?php if (count($inventory) <= 10) echo '1'; ?>'>
			<input type="hidden" name="MNT_CUSTOM2" value='<?php if (count($inventory) <= 10) echo $jsonData; ?>'>

			<table border = "1" cellspacing = "0" width = "400" bgcolor = "#FFFFFF" align = "center" bordercolor = "#000000">
				<tr>
					<td>Сумма, руб.</td>
					<td><?php echo $Sum; ?></td>
				</tr>
				<tr>
					<td>Номер заказа</td>
					<td><?php echo $this->_shopOrder->invoice; ?></td>
				</tr>
			</table>

			<input type="submit" name = "BuyButton" value = "Оплатить"></td>
		</form>
		<?php
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}
}