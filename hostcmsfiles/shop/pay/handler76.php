<?php

/**
 * Модуль разработан в компании GateOn предназначен для Host CMS v.6.6.x
 * Сайт разработчикa: www.gateon.net
 * E-mail: www@smartbyte.pro
 * Interkassa 2.0
 * Last update 15.03.2017
 * Версия 1.4
 */
class Shop_Payment_System_Handler76 extends Shop_Payment_System_Handler
{
    // Идентификатор кассы
    protected $ik_co_id = "Ваш идентификатор кассы";

    //Секретный ключ
    protected $ik_secret_key = "Секретный ключ";

    //Тестовый ключ (
    protected $ik_test_key = "Тестовый ключ";

    // Код валюты в магазине HostCMS для валюты платежа в личном кабинете Интеркассы
    protected $interkassa_currency = 1; //вставьте сюда число из Интернет-магазины-> Финансы -> Валюты

    /**
     * Метод, вызываемый в коде настроек ТДС через Shop_Payment_System_Handler::checkBeforeContent($oShop);
     */
    public static function checkPaymentBeforeContent()
    {
        if (isset($_POST['ik_co_id'])) {
            // Получаем ID заказа
            $ik_pm_no = intval(Core_Array::getRequest('ik_pm_no'));
            $order = Core_Entity::factory('Shop_Order')->find($ik_pm_no);
            if (!is_null($order->id)) {
                // Вызов обработчика платежной системы
                Shop_Payment_System_Handler::factory($order->Shop_Payment_System)->shopOrder($order)->paymentProcessing();
            }
        }
    }

    public function getInvoice()
    {
        return $this->getNotification();
    }

    public function execute()
    {
        parent::execute();
        $this->printNotification();
        return $this;
    }

    protected function _processOrder()
    {
        $this->wrlog('_processOrder');
        parent::_processOrder();
        $this->setXSLs();
        // Отправка писем клиенту и пользователю
        $this->send();
        return $this;
    }

    public function paymentProcessing()
    {
        // Пришло подтверждение оплаты, обработаем его
        if (isset($_POST['ik_pm_no'])) {
            $this->ProcessResult();
        }
    }


    //Обработка ответа Интеркассы

    public function ProcessResult()
    {
        if (count($_POST) && $this->checkIP()) {
            if ($_POST['ik_inv_st'] == 'success' && $this->ik_co_id == $_POST['ik_co_id'] && isset($_POST['ik_sign'])) {
                $ik_co_id = Core_Array::getRequest('ik_co_id');
                $ik_am = Core_Array::getRequest('ik_am');
                $ik_pm_no = Core_Array::getRequest('ik_pm_no');
                $ik_inv_st = Core_Array::getRequest('ik_inv_st');
                $ik_cur = Core_Array::getRequest('ik_cur');

                if (isset($_POST['ik_pw_via']) && $_POST['ik_pw_via'] == 'test_interkassa_test_xts') {
                    $secret_key = $this->ik_test_key;
                } else {
                    $secret_key = $this->ik_secret_key;
                }

                $request = $_POST;
                $request_sign = $request['ik_sign'];
                unset($request['ik_sign']);

                //удаляем все поле которые не принимают участия в формировании цифровой подписи
                foreach ($request as $key => $value) {
                    if (!preg_match('/ik_/', $key)) continue;
                    $request[$key] = $value;
                }

                //формируем цифровую подпись
                ksort($request, SORT_STRING);
                array_push($request, $secret_key);
                $str = implode(':', $request);
                $sign = base64_encode(md5($str, true));

                //Если подписи совпадают то осуществляется смена статуса заказа в админке
                if ($request_sign == $sign) {
                    $this->_shopOrder->system_information = sprintf("
                            Заказ оплачен через Интеркассу.\n
                            Детали платежа:\n
                            Идентификатор кассы: %s\n
                            Номер заказа в магазине: %s\n
                            Сумма платежа: %s\n
                            Валюта платежа: %s\n
                            Номер счета в системе Interkassa: %s\n
                            Дата и время выполнения платежа: %s\n
                            Статус платежа: %s - Платеж зачислен\n",
                        $ik_co_id,
                        $this->_shopOrder->id,
                        $ik_am,
                        $ik_cur,
                        $ik_pm_no,
                        date("Y-m-d H:i:s"),
                        $ik_inv_st
                    );

                    $this->_shopOrder->paid();
                    $this->setXSLs();
                    $this->send();
                    return true;
                } else {
                    $this->_shopOrder->system_information = 'Цифровая подпись не совпала: ' . $sign;
                    $this->_shopOrder->save();
                }
            } else {
                $this->_shopOrder->system_information = 'Ответ Интеркассы не верен';
                $this->_shopOrder->cancelPaid();
                $this->_shopOrder->save();
            }

        } else {
            $this->_shopOrder->system_information = 'Попытка взлома с айпи:' . $_SERVER['REMOTE_ADDR'];
            $this->_shopOrder->cancelPaid();
            $this->_shopOrder->save();
        }

    }

    public function getNotification()
    {

        //Получаем сумму платежа в зависимости от валюты
        if ($this->interkassa_currency > 0 && $this->_shopOrder->shop_currency_id > 0) {
            $CurrencyCoefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency($this->_shopOrder->Shop_Currency, Core_Entity::factory('Shop_Currency', $this->interkassa_currency));
        } else {
            $CurrencyCoefficient = 0;
        }
        $ik_am = Shop_Controller::instance()->round($CurrencyCoefficient * $this->_shopOrder->getAmount());

        //Формируем урлы взаимодействия и уведомления
        $current_alias = $this->_shopOrder->Shop->Site->getCurrentAlias();
        $path = $this->_shopOrder->Shop->Structure->getPath();
        $handler_url = 'http://' . $current_alias->name . $path . "cart/?orderId={$this->_shopOrder->id}";
        $ik_suc_u = $handler_url . "&payment=success";
        $ik_ia_u = $handler_url . "&payment=success";
        $ik_fal_u = $handler_url . "&payment=fail";
        $ik_pnd_u = $handler_url . "&payment=fail";


        //Получаем валюту
        $currency = Core_Entity::factory('Shop_Currency')->find($this->interkassa_currency);
        if ($currency->code == 'RUR') {
            $ik_cur = 'RUB';
        } else {
            $ik_cur = $currency->code;
        }

        $ik_pm_no = $this->_shopOrder->id;
        $ik_desc = '#' . $this->_shopOrder->id;

        $dataSet = array(
            'ik_co_id' => $this->ik_co_id,
            'ik_am' => $ik_am,
            'ik_pm_no' => $ik_pm_no,
            'ik_desc' => $ik_desc,
            'ik_cur' => $ik_cur,
            'ik_suc_u' => $ik_suc_u,
            'ik_fal_u' => $ik_fal_u,
            'ik_pnd_u' => $ik_pnd_u,
            'ik_ia_u' => $ik_ia_u
        );

        ksort($dataSet, SORT_STRING);
        array_push($dataSet, $this->ik_secret_key);
        $str = implode(':', $dataSet);
        $sign = base64_encode(md5($str, true));


        ?>
        <h1>Оплата с помощью Интеркассы</h1>

        <p><a href="http://www.interkassa.com/" target="_blank">
                <img src="http://www.interkassa.com/img/logo-ru.png" border="0" alt="Система электронных платежей">
            </a></p>
        <p>Сумма заказа составляет: <strong><?php echo $this->_shopOrder->sum() ?></strong></p>
        <p>Для оплаты нажмите кнопку "Оплатить".</p>

		<style>
		.ik_button {
			background: #27ae60;
			border-top: 2px solid #27ae60;
			border-bottom: 2px solid #1f9952;
			border-left: none;
			border-right: none;
			font-family: "Segoe UI", Arial, Helvetica, sans-serif;
			font-size: 14px;
			color: #fff;
			font-weight: 700;
			outline: 0;
			text-shadow: none;
			padding: 4px 15px;
			margin: 0;
			filter: none;
		}

		.ik_button:hover {
			background-color: #57d68d;
			border-top-color: #57d68d;
			border-bottom-color: #27ae60;
		}
		</style>
		
        <form action="https://sci.interkassa.com" method="POST" accept-charset='UTF-8'>
            <input type="hidden" name="ik_co_id" value="<?php echo $this->ik_co_id ?>">
            <input type="hidden" name="ik_am" value="<?php echo $ik_am ?>">
            <input type="hidden" name="ik_pm_no" value="<?php echo $ik_pm_no ?>">
            <input type="hidden" name="ik_desc" value="<?php echo $ik_desc ?>">
            <input type="hidden" name="ik_cur" value="<?php echo $ik_cur; ?>"/>
            <input type="hidden" name="ik_suc_u" value="<?php echo $ik_suc_u ?>">
            <input type="hidden" name="ik_fal_u" value="<?php echo $ik_fal_u ?>">
            <input type="hidden" name="ik_pnd_u" value="<?php echo $ik_pnd_u ?>">
            <input type="hidden" name="ik_ia_u" value="<?php echo $ik_ia_u ?>">
            <input type="hidden" name="ik_sign" value="<?php echo $sign ?>">
            <input type="submit" class="ik_button" value="Оплатить">
        </form>
        <?php

    }


    public function wrlog($content)
    {
        $file = 'log.txt';
        $doc = fopen($file, 'a');

        file_put_contents($file, PHP_EOL . '====================' . date("H:i:s") . '=====================', FILE_APPEND);
        if (is_array($content)) {
            foreach ($content as $k => $v) {
                if (is_array($v)) {
                    $this->wrlog($v);
                } else {
                    file_put_contents($file, PHP_EOL . $k . '=>' . $v, FILE_APPEND);
                }
            }
        } else {
            file_put_contents($file, PHP_EOL . $content, FILE_APPEND);
        }
        fclose($doc);
    }

    public function checkIP()
    {
        $ip_stack = array(
            'ip_begin' => '151.80.190.97',
            'ip_end' => '151.80.190.104'
        );

        if (ip2long($_SERVER['REMOTE_ADDR']) < ip2long($ip_stack['ip_begin']) || ip2long($_SERVER['REMOTE_ADDR']) > ip2long($ip_stack['ip_end'])) {
            $this->wrlog('REQUEST IP' . $_SERVER['REMOTE_ADDR'] . 'doesnt match');
        }
        return true;
    }
}