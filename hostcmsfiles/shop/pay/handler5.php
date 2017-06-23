<?php

/**
 * Яндекс.Деньги
 * Версия 1.2.1
 * Лицензионный договор:
 *	Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу https://money.yandex.ru/doc.xml?id=527132 (далее – «Лицензионный договор»). Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.
 *
 * Откорректировано HostCMS 27.04.16г.
 *
 */
class Shop_Payment_System_Handler5 extends Shop_Payment_System_Handler
{
	/* Тестовый или полный режим функциональности. */
	protected $ym_test_mode = 0; // 1 - тестовый, 0 - полный

	/* режим приема средств */
	protected $ym_org_mode = 1; // 1 - На расчетный счет организации с заключением договора с Яндекс.Деньгами (юр.лицо), 0 - На счет физического лица в электронной валюте Яндекс.Денег'

	/* Только для физического лица! Идентификатор магазина в системе Яндекс.Деньги. Выдается оператором системы. */
	protected $ym_account = 'xxx';

	/* Пароль магазина в системе Яндекс.Деньги. Выдается оператором системы. */
	protected $ym_password = 'yyy';

	/* Способы оплаты */
	protected $ym_method_pc = 1; /* электронная валюта Яндекс.Деньги. 1 - используется, 0 - нет */
	protected $ym_method_ac = 1; /* банковские карты VISA, MasterCard, Maestro. 1 - используется, 0 - нет */
	protected $ym_method_gp = 1; /* Только для юридического лица! Наличными в кассах и терминалах партнеров. 1 - используется, 0 - нет */
	protected $ym_method_mc = 1; /* Только для юридического лица! Оплата со счета мобильного телефона. 1 - используется, 0 - нет */
	protected $ym_method_wm = 1; /* Только для юридического лица! Электронная валюта WebMoney. 1 - используется, 0 - нет */
	protected $ym_method_ab = 1; /* Только для юридического лица! АльфаКлик. 1 - используется, 0 - нет */
	protected $ym_method_sb = 1; /* Только для юридического лица! Сбербанк Онлайн. 1 - используется, 0 - нет */
	protected $ym_method_ma = 1; /* Только для юридического лица! MasterPass. 1 - используется, 0 - нет */
	protected $ym_method_pb = 1; /* Только для юридического лица! Интернет-банк Промсвязьбанка. 1 - используется, 0 - нет */
	protected $ym_method_qw = 1; /* Только для юридического лица! Оплата через QIWI Wallet. 1 - используется, 0 - нет */
	protected $ym_method_qp = 1; /* Только для юридического лица! Оплата через доверительный платеж (Куппи.ру). 1 - используется, 0 - нет */

	/* Только для юридического лица! Идентификатор вашего магазина в Яндекс.Деньгах (ShopID) */
	protected $ym_shopid = 'zzz';

	/* Только для юридического лица! Идентификатор витрины вашего магазина в Яндекс.Деньгах (scid) */
	protected $ym_scid = 'www';

	// id валюты магазина, в которой будет производиться рассчет суммы
	protected $ym_currency_id = 1;

	/* Код валюты, в которой будет производиться оплата в Яндекс-Деньги  */
	protected $ym_orderSumCurrencyPaycash = 643; /* Возможные значения: 643 — рубль Российской Федерации; 10643 — тестовая валюта (демо-рублики демо-системы «Яндекс.Деньги») */

	/* Тип отобрадения формы оплаты. 1 - Форма с выбором способа оплаты на сайте магазина, 0 - Форма с выбором способа оплаты на стороне Яндекс.Кассы */
	protected $ym_payment_type = 0;

	/**
	 * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
	 */
	public function checkPaymentBeforeContent()
	{
		if (isset($_POST['action']) && isset($_POST['invoiceId']) && isset($_POST['orderNumber']) || isset($_POST['sha1_hash']))
		{
			// Получаем ID заказа
			$order_id = isset($_POST['sha1_hash'])
				? intval(Core_Array::getPost('label'))
				: intval(Core_Array::getPost('orderNumber'));

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

	/*
	 * Метод, запускающий выполнение обработчика
	 */
	public function execute()
	{
		parent::execute();

		$this->printNotification();

		return $this;
	}

	/*
	 * Вычисление суммы товаров заказа
	 */
	public function getSumWithCoeff()
	{
		return Shop_Controller::instance()->round(($this->ym_currency_id > 0
				&& $this->_shopOrder->shop_currency_id > 0
			? Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
				$this->_shopOrder->Shop_Currency,
				Core_Entity::factory('Shop_Currency', $this->ym_currency_id)
			)
			: 0) * $this->_shopOrder->getAmount());
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

	/*
	 * Обработка ответа от платёжной системы
	 */
	public function paymentProcessing()
	{
		$this->ProcessResult();
		return TRUE;
	}

	public function checkSign($callbackParams)
	{
		if ($this->ym_org_mode)
		{
			$string = $callbackParams['action'].';'.$callbackParams['orderSumAmount'].';'.$callbackParams['orderSumCurrencyPaycash'].';'.$callbackParams['orderSumBankPaycash'].';'.$callbackParams['shopId'].';'.$callbackParams['invoiceId'].';'.$callbackParams['customerNumber'].';'.$this->ym_password;

			return strtoupper($callbackParams['md5']) == strtoupper(md5($string));
		}
		else
		{
			$string = $callbackParams['notification_type'].'&'.$callbackParams['operation_id'].'&'.$callbackParams['amount'].'&'.$callbackParams['currency'].'&'.$callbackParams['datetime'].'&'.$callbackParams['sender'].'&'.$callbackParams['codepro'].'&'.$this->ym_password.'&'.$callbackParams['label'];

			$check = (sha1($string) == $callbackParams['sha1_hash']);

			if (!$check){
				header('HTTP/1.0 401 Unauthorized');
				return FALSE;
			}

			return TRUE;
		}
	}

	public function sendCode($callbackParams, $code, $message = '')
	{
		if (!$this->ym_org_mode)
		{
			return;
		}

		header("Content-type: text/xml; charset=utf-8");
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
			<'.$callbackParams['action'].'Response performedDatetime="'.date("c").'" code="'.$code.'" invoiceId="'.$callbackParams['invoiceId'].'" shopId="'.$this->ym_shopid.'" techmessage="'.$message.'"/>';
		echo $xml;
	}

	/*
	 * Оплачивает заказ
	 */
	function ProcessResult()
	{
		if ($this->checkSign($_POST))
		{
			if ($_POST['action'] == 'paymentAviso' || !$this->ym_org_mode)
			{
				$order_id = intval(Core_Array::getPost(isset($_POST["label"]) ? "label" : "orderNumber"));
				if ($order_id > 0)
				{
					$oShop_Order = $this->_shopOrder;

					$sHostcmsSum = sprintf("%.2f", $this->getSumWithCoeff());
					$sYandexSum = Core_Array::getRequest('orderSumAmount', '');

					if ($sHostcmsSum == $sYandexSum)
					{
						$this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

						$oShop_Order->system_information = "Заказ оплачен через систему Яндекс.Касса.\n";
						$oShop_Order->paid();

						$this->setXSLs();
						$this->send();
					}
					else
					{
						$this->sendCode($_POST, 1, 'Bad amount');
					}
				}
				$this->sendCode($_POST, 0, 'Order completed.');
			}
			else
			{
				$this->sendCode($_POST, 0, 'Order is exist.');
			}
		}
		else
		{
			$this->sendCode($_POST, 1, 'md5 bad');
		}

		die();
	}

	/*
	 * Печатает форму отправки запроса на сайт платёжной системы
	 */
	public function getNotification()
	{
		$sum = $this->getSumWithCoeff();

		$oSiteuser = Core::moduleIsActive('siteuser')
			? Core_Entity::factory('Siteuser')->getCurrent()
			: NULL;

		$oSite_Alias = $this->_shopOrder->Shop->Site->getCurrentAlias();

		$sSiteAlias = !is_null($oSite_Alias) ? $oSite_Alias->name : '';

		$sShopPath = $this->_shopOrder->Shop->Structure->getPath();

		$sHandlerUrl = 'http://' . $sSiteAlias . $sShopPath . "cart/?order_id={$this->_shopOrder->id}";

		$successUrl = $sHandlerUrl . "&payment=success";
		$failUrl = $sHandlerUrl . "&payment=fail";

		?>
		<h2>Оплата через систему Яндекс.Деньги</h2>

		<form method="POST" action="<?php echo $this->getFormUrl()?>">
			<?php if ($this->ym_org_mode){ ?>
				<input class="wide" name="scid" value="<?php echo $this->ym_scid?>" type="hidden">
				<input type="hidden" name="ShopID" value="<?php echo $this->ym_shopid?>">
				<input type="hidden" name="CustomerNumber" value="<?php echo (is_null($oSiteuser) ? 0 : $oSiteuser->id)?>">
				<input type="hidden" name="orderNumber" value="<?php echo $this->_shopOrder->id?>">
				<input type="hidden" name="shopSuccessURL" value="<?php echo $successUrl?>">
				<input type="hidden" name="shopFailURL" value="<?php echo $failUrl?>">
				<input type="hidden" name="cms_name" value="hostcms">
			<?php }else {?>
				   <input type="hidden" name="receiver" value="<?php echo $this->ym_account?>">
				   <input type="hidden" name="formcomment" value="<?php echo $sSiteAlias?>">
				   <input type="hidden" name="short-dest" value="<?php echo $sSiteAlias?>">
				   <input type="hidden" name="writable-targets" value="false">
				   <input type="hidden" name="comment-needed" value="true">
				   <input type="hidden" name="label" value="<?php echo $this->_shopOrder->id?>">
				   <input type="hidden" name="quickpay-form" value="shop">
					<input type="hidden" name="successUrl" value="<?php echo $successUrl?>">

				   <input type="hidden" name="targets" value="Заказ <?php echo $this->_shopOrder->id?>">
				   <input type="hidden" name="sum" value="<?php echo $sum?>" data-type="number" >
				   <input type="hidden" name="comment" value="<?php echo $this->_shopOrder->description?>" >
				   <input type="hidden" name="need-fio" value="true">
				   <input type="hidden" name="need-email" value="true" >
				   <input type="hidden" name="need-phone" value="false">
				   <input type="hidden" name="need-address" value="false">

			<?php } ?>
				<style>
					.ym_table tr td{
						padding: 10px;
					}
					.ym_table td{
						padding: 10px;
					}
				</style>
				<table class="ym_table" bgcolor="#FFFFFF" align="left">
					<tr>
						<td>Сумма, руб.</td>
						<td>
							<input type="text" name="Sum" value="<?php echo $sum?>" readonly="readonly">
						</td>
					</tr>

					<?php if ($this->ym_payment_type) { ?>
						<tr>
							<td>Способ оплаты</td>
							<td>
									<select name="paymentType">
									<?php if ($this->ym_method_pc){?>
										<option value="PC">Оплата из кошелька в Яндекс.Деньгах</option>
									<?php } ?>
									<?php if ($this->ym_method_ac){?>
										<option value="AC">Оплата с произвольной банковской карты</option>
									<?php } ?>
									<?php if ($this->ym_method_gp && $this->ym_org_mode){?>
										<option value="GP">Оплата наличными через кассы и терминалы</option>
									<?php } ?>
									<?php if ($this->ym_method_mc && $this->ym_org_mode){?>
										<option value="MC">Платеж со счета мобильного телефона</option>
									<?php } ?>
									<?php if ($this->ym_method_ab && $this->ym_org_mode){?>
										<option value="AB">Оплата через Альфа-Клик</option>
									<?php } ?>
									<?php if ($this->ym_method_sb && $this->ym_org_mode){?>
										<option value="SB">Оплата через Сбербанк: оплата по SMS или Сбербанк Онлайн</option>
									<?php } ?>
									<?php if ($this->ym_method_wm && $this->ym_org_mode){?>
										<option value="WM">Оплата из кошелька в системе WebMoney</option>
									<?php } ?>
									<?php if ($this->ym_method_ma && $this->ym_org_mode){?>
										<option value="MA">Оплата через MasterPass</option>
									<?php } ?>
									<?php if ($this->ym_method_pb && $this->ym_org_mode){?>
										<option value="PB">Оплата через интернет-банк Промсвязьбанка</option>
									<?php } ?>
									<?php if ($this->ym_method_qw && $this->ym_org_mode){?>
										<option value="QW">Оплата через QIWI Wallet</option>
									<?php } ?>
									<?php if ($this->ym_method_qp && $this->ym_org_mode){?>
										<option value="QP">Оплата через доверительный платеж (Куппи.ру)</option>
									<?php } ?>
								</select>
							</td>
						</tr>
					<?php } else { ?>
						<input name="paymentType" value="" type="hidden">
					<?php } ?>

					<tr bgcolor="#FFFFFF">
						<td><input class="button" type="submit" name="BuyButton" value="Оплатить"></td>
					</tr>
				</table>
		</form>
	<?php
	}

	public function getInvoice()
	{
		return $this->getNotification();
	}

	public function getFormUrl()
	{
		$sUrl = 'https://';
		$this->ym_test_mode && $sUrl .= 'demo';

		return $this->ym_org_mode
			? $sUrl . 'money.yandex.ru/eshop.xml'
			: $sUrl . 'money.yandex.ru/quickpay/confirm.xml';
	}
}