<?php

/**
 * Яндекс.Деньги
 * Версия 1.4
 * Лицензионный договор:
 * Любое использование Вами программы означает полное и безоговорочное принятие Вами условий лицензионного договора, размещенного по адресу https://money.yandex.ru/doc.xml?id=527132 (далее – «Лицензионный договор»). Если Вы не принимаете условия Лицензионного договора в полном объёме, Вы не имеете права использовать программу в каких-либо целях.
 *
 * Откорректировано HostCMS 27.06.2017 г.
 */
class Shop_Payment_System_Handler5 extends Shop_Payment_System_Handler
{
    /* Тестовый или полный режим функциональности. */
    protected $ym_test_mode = 1; // 1 - тестовый, 0 - полный

    /* Сценарий оплаты. */
    protected $ym_smartpay = 1; // 1 - выбор оплаты на стороне Яндекс.Кассы , 0 - выбор оплаты на стороне магазина

    /* Режим приема средств */
    protected $ym_org_mode = 1; // 1 - На расчетный счет организации с заключением договора с Яндекс.Деньгами (юр.лицо), 0 - На счет физического лица в электронной валюте Яндекс.Денег'

    /* Только для физического лица! Идентификатор магазина в сервисе Яндекс.Деньги. Выдается менеджером сервиса. */
    protected $ym_account = 'xxx';

    /* Пароль магазина в сервисе Яндекс.Деньги. Выдается менеджером сервиса. */
    protected $ym_password = 'xxx';

    /* Отправлять в Яндекс.Кассу данные для чеков (54-ФЗ) */
    protected $sendCheck = 1;

    /* Передавать в кассу налог как (1 - Без НДС, 2 - 0%, 3 - 10%, 4 - 18%, 5 - Рассчётная ставка 10/110, 6 - Рассчётная ставка 18/118) */
    protected $kassaTaxRateDefault = 4;

    /* Массив отношений налога маназина и отправляемой таксы (формат id => #kassa ^^^^^)*/
    protected $kassaTaxRates = array(
        2 => 4,
        5 => 4,
        19 => 3,
        20 => 3,
        21 => 1,
    );

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
    protected $ym_shopid = 000000;

    /* Только для юридического лица! Идентификатор витрины вашего магазина в Яндекс.Деньгах (scid) */
    protected $ym_scid = 000000;

    // id валюты, в которой будет производиться рассчет суммы
    protected $ym_currency_id = 1; // 1 - рубли (RUR), 2 - евро (EUR), 3 - доллары (USD)

    /* Код валюты, в которой будет производиться оплата в Яндекс-Деньги  */
    protected $ym_orderSumCurrencyPaycash = 643; /* Возможные значения: 643 — рубль Российской Федерации; 10643 — тестовая валюта (демо-рублики сервиса Яндекс.Деньги) */

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

                // Вызов обработчика платежного сервиса
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
     * Обработка ответа платёжного сервиса
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
        die();
    }

    /*
     * Оплачивает заказ
     */
    public function ProcessResult()
    {
        if ($this->checkSign($_POST))
        {
            if (isset($_POST['action']) || !$this->ym_org_mode)
            {
                $order_id = intval(Core_Array::getPost(isset($_POST["label"]) ? "label" : "orderNumber"));
                if ($order_id > 0)
                {
                    $oShop_Order = $this->_shopOrder;

                    $sHostcmsSum = sprintf("%.2f", $this->getSumWithCoeff());
                    $sYandexSum = Core_Array::getRequest('orderSumAmount', '');

                    if ($sHostcmsSum == $sYandexSum)
                    {
                        if ($_POST['action'] == 'paymentAviso') {
                            $this->shopOrder($oShop_Order)->shopOrderBeforeAction(clone $oShop_Order);

                            $oShop_Order->system_information = "Заказ оплачен через сервис Яндекс.Касса.\n";
                            $oShop_Order->paid();

                            $this->setXSLs();
                            $this->send();
                        }
                    }
                    else
                    {
                        $this->sendCode($_POST, 100, 'Bad amount');
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
    }

    /*
     * Печатает форму отправки запроса на сайт платёжной сервиса
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

        if ($this->sendCheck)
		{
            $receipt = array(
                'customerContact' => (
					isset($this->_shopOrder->email)
						? $this->_shopOrder->email
						: (
							isset($this->_shopOrder->phone)
								? $this->_shopOrder->phone
								: 'noData'
						)
				),
                'items' => array(),
            );

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
				else
				{
					if ($oShop_Order_Item->shop_item_id)
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
				$tax_id = $oShop_Order_Item->shop_item_id
					? $oShop_Order_Item->Shop_Item->shop_tax_id
					: false;

				/*if (strpos('Доставка', $oShop_Order_Item->name) != false) {

				}*/

				$receipt['items'][] = array(
					'quantity' => $oShop_Order_Item->quantity,
					'text' => mb_substr($oShop_Order_Item->name, 0, 128),
					'tax' => $tax_id
						? Core_Array::get($this->kassaTaxRates, $tax_id, $this->kassaTaxRateDefault)
						: 1,
					'price' => array(
						'amount' => number_format($oShop_Order_Item->getAmount() * ($oShop_Order_Item->shop_item_id ? 1 - $discount : 1), 2, '.', ''),
						'currency' => 'RUB'
					),
				);
			}
        }

        ?>
        <form method="POST" id="frmYandexMoney" action="<?php echo $this->getFormUrl()?>">
            <?php if ($this->ym_org_mode){ ?>
                <input class="wide" name="scid" value="<?php echo $this->ym_scid?>" type="hidden">
                <input type="hidden" name="ShopID" value="<?php echo $this->ym_shopid?>">
                <input type="hidden" name="CustomerNumber" value="<?php echo (is_null($oSiteuser) ? 0 : $oSiteuser->id)?>">
                <input type="hidden" name="orderNumber" value="<?php echo $this->_shopOrder->id?>">
                <input type="hidden" name="shopSuccessURL" value="<?php echo htmlspecialchars($successUrl)?>">
                <input type="hidden" name="shopFailURL" value="<?php echo htmlspecialchars($failUrl)?>">
                <input type="hidden" name="cms_name" value="hostcms">
                <?php if (isset ($this->_shopOrder->email)){ ?> <input type="hidden" name="cps_email" value="<?php echo htmlspecialchars($this->_shopOrder->email)?>"> <?php } ?>
                <?php if (isset ($this->_shopOrder->phone)){ ?> <input type="hidden" name="cps_phone" value="<?php echo htmlspecialchars($this->_shopOrder->phone)?>"> <?php } ?>
                <?php if (isset ($this->sendCheck) && $this->sendCheck){ ?> <input type="hidden" name="ym_merchant_receipt" value="<?php echo htmlspecialchars(json_encode($receipt))?>"><?php } ?>

            <?php }else {?>
                <input type="hidden" name="receiver" value="<?php echo htmlspecialchars($this->ym_account)?>">
                <input type="hidden" name="formcomment" value="<?php echo htmlspecialchars($sSiteAlias)?>">
                <input type="hidden" name="short-dest" value="<?php echo htmlspecialchars($sSiteAlias)?>">
                <input type="hidden" name="writable-targets" value="false">
                <input type="hidden" name="comment-needed" value="true">
                <input type="hidden" name="label" value="<?php echo $this->_shopOrder->id?>">
                <input type="hidden" name="quickpay-form" value="shop">
                <input type="hidden" name="successUrl" value="<?php echo htmlspecialchars($successUrl)?>">

                <input type="hidden" name="targets" value="Заказ <?php echo $this->_shopOrder->id?>">
                <input type="hidden" name="sum" value="<?php echo $sum?>" data-type="number" >
                <input type="hidden" name="comment" value="<?php echo htmlspecialchars($this->_shopOrder->description)?>" >
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
            <table class="ym_table" border = "1" cellspacing = "20" width = "80%" bgcolor = "#FFFFFF" align = "center" bordercolor = "#000000">
                <tr>
                    <td>Сумма, руб.</td>
                    <td> <input type="text" name="Sum" value="<?php echo $sum?>" readonly="readonly"> </td>
                </tr>
                <?php if (!$this->ym_smartpay || !$this->ym_org_mode ){ ?>
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
                <?php } ?>
            </table>
            <?php if ($this->ym_smartpay && $this->ym_org_mode){ ?>
                <table border="0" cellspacing="1" align="center"  width = "80%">
                    <tr>
                        <td align="center">
                            <a href="#" id="button-confirm"><img width="165" height="76" src="data:image/png;base64,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" /></a>
                        </td>
                    </tr>
                </table>
            <?php }else{ ?>
                <table border="0" cellspacing="1" align="center"  width = "80%" bgcolor="#CCCCCC" >
                    <tr bgcolor="#FFFFFF">
                        <td width="490"></td>
                        <td width="48"><input type="submit" name = "BuyButton" value = "Оплатить"></td>
                    </tr>
                </table>
            <?php } ?>
        </form>
        <script type="text/javascript"><!--
            $('#button-confirm').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $('#frmYandexMoney').submit();
            });
            //-->
        </script>
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