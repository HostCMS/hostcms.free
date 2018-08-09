<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Robokassa observer
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Observer_Robokassa
{
	/**
	 * Send SMS through Core_Http
	 * @param string $sUrl
	 */
	static protected function _sendSMS($sUrl)
	{
		try
		{
			$Core_Http = Core_Http::instance()
				->url($sUrl)
				->port(80)
				->timeout(5)
				->execute();

			$aResponce = json_decode($Core_Http->getBody(), TRUE);

			if (Core_Array::get($aResponce, 'errorCode') > 0)
			{
				$sLogMessage = "Ошибка отправки СМС от Robokassa, result:{$aResponce['result']}, count:{$aResponce['count']}, errorCode:{$aResponce['errorCode']}, errorMessage:{$aResponce['errorMessage']}";

				Core_Log::instance()
					->status(Core_Log::$WARNING)
					->write($sLogMessage);
			}
		}
		catch (Exception $e){}
	}

	/**
	 * Get Robokassa config
	 * @return array
	 */
	static protected function _getConfig()
	{
		$aConfig = Core_Config::instance()->get('shop_observer_robokassa') + array(
			'login' => '',
			'pass1' => '',
			'url' => '',
			'admin_paid_message' => 'Заказ %s оплачен!',
			'user_paid_message' => 'Ваш заказ %s оплачен!',
			'admin_process_order_message' => 'Поступил заказ %1$s на сумму %2$s %3$s',
			'user_process_order_message' => 'Ваш заказ %1$s на сумму %2$s %3$s',
		);

		return $aConfig;
	}

	// Заказ оплачен
	static public function onAfterChangeStatusPaid($object, $args)
	{
		$aConfig = self::_getConfig();

		if ($object->paid == 1 && strlen($aConfig['login']))
		{
			// Отправка сообщения куратору
			$sAdminMessageText = sprintf($aConfig['admin_paid_message'], $object->id);

			// Подпись
			$sAdminSignature = md5("{$aConfig['login']}:{$aConfig['admin_phone']}:{$sAdminMessageText}:{$aConfig['pass1']}");

			$urlAdminSend = $aConfig['url'] . '?login=' . $aConfig['login'] . '&phone=' . $aConfig['admin_phone'] . '&message=' . rawurlencode($sAdminMessageText) . '&signature=' . $sAdminSignature;

			self::_sendSMS($urlAdminSend);

			// Отправка сообщения пользователю
			$sUserMessageText = sprintf($aConfig['user_paid_message'], $object->id);

			// Подпись
			$sUserSignature = md5("{$aConfig['login']}:{$object->phone}:{$sUserMessageText}:{$aConfig['pass1']}");

			$urlUserSend = $aConfig['url'] . '?login=' . $aConfig['login'] . '&phone=' . $object->phone . '&message=' . rawurlencode($sUserMessageText) . '&signature=' . $sUserSignature;

			self::_sendSMS($urlUserSend);
		}
	}

	// Заказ поступил
	static public function onAfterProcessOrder($object, $args)
	{
		$aConfig = self::_getConfig();

		if (strlen($aConfig['login']))
		{
			$oShop_Order = $object->getShopOrder();

			$oShop = $oShop_Order->Shop;

			$fShopOrderItemSum = 0.0;

			$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll();

			if (count($aShop_Order_Items))
			{
				foreach ($aShop_Order_Items as $oShop_Order_Item)
				{
					$sItemAmount = $oShop_Order_Item->getAmount();
					$fShopOrderItemSum += $sItemAmount;
				}
			}

			$iTotalSum = sprintf("%.2f", $fShopOrderItemSum);

			// Отправка куратору
			$sAdminMessageText = sprintf($aConfig['admin_process_order_message'], $oShop_Order->id, $iTotalSum, $oShop->Shop_Currency->name);

			// Подпись
			$sAdminSignature = md5("{$aConfig['login']}:{$aConfig['admin_phone']}:{$sAdminMessageText}:{$aConfig['pass1']}");

			$urlAdminSend = $aConfig['url'] . '?login=' . $aConfig['login'] . '&phone=' . $aConfig['admin_phone'] . '&message=' . rawurlencode($sAdminMessageText) . '&signature=' .$sAdminSignature;

			self::_sendSMS($urlAdminSend);

			// Отправка пользователю
			$sUserMessageText = sprintf($aConfig['user_process_order_message'], $oShop_Order->id, $iTotalSum, $oShop->Shop_Currency->name);

			// Подпись
			$sUserSignature = md5("{$aConfig['login']}:{$oShop_Order->phone}:{$sUserMessageText}:{$aConfig['pass1']}");

			$urlUserSend = $aConfig['url'] . '?login=' . $aConfig['login'] . '&phone=' . $oShop_Order->phone . '&message=' . rawurlencode($sUserMessageText) . '&signature=' .$sUserSignature;

			self::_sendSMS($urlUserSend);
		}
	}
}