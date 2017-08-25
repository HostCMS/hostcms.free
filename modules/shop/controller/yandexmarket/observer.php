<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Yandexmarket observer
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Controller_Yandexmarket_Observer
{
	// Идентификатор кампании на Яндексе
	static public $campaignId = NULL;

	// Идентификатор статуса заказа "Передано в доставку"
	static public $deliveryStatusId = NULL;

	// Отладочный токен, срок жизни - 365 дней
	static public $token = NULL;

	// Идентификатор приложения авторизации в oauth.yandex.ru
	static public $clientId = NULL;

	// Отменен, в свойствах объекта уже измененные данные
	static public function onAfterChangedOrder($object, $args)
	{
		$oShop_Order = $object->getShopOrder();

		$orderId = intval($oShop_Order->system_information);

		if (!$oShop_Order->paid
			&& $oShop_Order->canceled
			&& $args[0] == 'cancelPaid'
			&& strlen($orderId)
		)
		{
			$sJson = json_encode(
				array(
					'order' => array(
						'status' => 'CANCELLED',
						'substatus' => 'SHOP_FAILED'
					)
				)
			);

			try
			{
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('PUT')
					->url("https://api.partner.market.yandex.ru/v2/campaigns/" . self::$campaignId . "/orders/{$orderId}/status")
					->additionalHeader('Authorization', 'OAuth oauth_token="' . self::$token . '" , oauth_client_id="' .  self::$clientId . '"')
					->additionalHeader('Content-Type', 'application/json')
					->rawData($sJson)
					->execute();
			}
			catch (Exception $e){}
		}

		// Смена статуса
		if (!$oShop_Order->paid
			&& !$oShop_Order->canceled
			&& $args[0] == 'apply'
			&& $oShop_Order->Shop_Order_Status->id == self::$deliveryStatusId
		)
		{
			$sJson = json_encode(
				array(
					'order' => array(
						'status' => 'DELIVERY'
					)
				)
			);

			try
			{
				$Core_Http = Core_Http::instance('curl')
					->clear()
					->method('PUT')
					->url("https://api.partner.market.yandex.ru/v2/campaigns/" . self::$campaignId . "/orders/{$orderId}/status")
					->additionalHeader('Authorization', 'OAuth oauth_token="' . self::$token . '" , oauth_client_id="' .  self::$clientId . '"')
					->additionalHeader('Content-Type', 'application/json')
					->rawData($sJson)
					->execute();
			}
			catch (Exception $e){}
		}
	}
}