<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Delivery_Condition_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shop_country_id',
		'shop_country_location_id',
		'shop_country_location_city_id',
		'shop_country_location_city_area_id',
		'timeFrom',
		'timeTo',
		'totalWeight',
		'totalAmount',
	);

	/**
	 * Get delivery conditions
	 * @param Shop_Delivery_Model $oShop_Delivery delivery
	 * @return mixed
	 */
	public function getShopDeliveryCondition(Shop_Delivery_Model $oShop_Delivery)
	{
		$shop_country_id = $this->shop_country_id;
		$shop_country_location_id = $this->shop_country_location_id;
		$shop_country_location_city_id = $this->shop_country_location_city_id;
		$shop_country_location_city_area_id = $this->shop_country_location_city_area_id;

		// Выбираем все способы доставки для данного типа с заданными условиями
		$i = 0;
		while ($i <= 4)
		{
			$oShop_Delivery_Conditions = $oShop_Delivery->Shop_Delivery_Conditions;
			$oShop_Delivery_Conditions->queryBuilder()
				->select('shop_delivery_conditions.*')
				->where('active', '=', 1)
				// Поле orderfield внесено для того, чтобы поля со всеми заполенынми условиями были выше
				//->select(array(Core_QueryBuilder::expression('IF ( `min_weight` > 0 AND `max_weight` > 0 AND `min_price` > 0 AND `max_price` > 0, 1, 0)'), 'orderfield'))
				// Отрезаем по Стране, Области, Городу и Району
				->open()
					->where('shop_country_id_inverted', '=', '0')
					->where('shop_country_id', '=', $shop_country_id)
					->setOr()
					->where('shop_country_id_inverted', '=', '1')
					->where('shop_country_id', '!=', $this->shop_country_id) // здесь всегда исходное значение переменной!
				->close()
				->open()
					->where('shop_country_location_id_inverted', '=', '0')
					->where('shop_country_location_id', '=', $shop_country_location_id)
					->setOr()
					->where('shop_country_location_id_inverted', '=', '1')
					->where('shop_country_location_id', '!=', $this->shop_country_location_id) // здесь всегда исходное значение переменной!
				->close()
				->open()
					->where('shop_country_location_city_id_inverted', '=', '0')
					->where('shop_country_location_city_id', '=', $shop_country_location_city_id)
					->setOr()
					->where('shop_country_location_city_id_inverted', '=', '1')
					->where('shop_country_location_city_id', '!=', $this->shop_country_location_city_id) // здесь всегда исходное значение переменной!
				->close()
				->open()
					->where('shop_country_location_city_area_id_inverted', '=', '0')
					->where('shop_country_location_city_area_id', '=', $shop_country_location_city_area_id)
					->setOr()
					->where('shop_country_location_city_area_id_inverted', '=', '1')
					->where('shop_country_location_city_area_id', '!=', $this->shop_country_location_city_area_id) // здесь всегда исходное значение переменной!
				->close()
				// Основная обрезка по характеристикам заказа
				->where('min_weight', '<=', $this->totalWeight)
				->open()
					->where('max_weight', '>=', $this->totalWeight)
					->setOr()
					->where('max_weight', '=', 0)
				->close()
				// Price
				->where('min_price', '<=', $this->totalAmount)
				->open()
					->where('max_price', '>=', $this->totalAmount)
					->setOr()
					->where('max_price', '=', 0)
				->close();

			// Time
			if ($this->timeFrom != '' && $this->timeTo != '')
			{
				$oShop_Delivery_Conditions->queryBuilder()
					->open()
						->open()
							// Заданное время начала больше времени начала и меньше времени окончания
							->where('time_from', '<=', $this->timeFrom)
							->open()
								->where('time_to', '>=', $this->timeFrom)
								->setOr()
								->where('time_to', '=', '00:00:00')
							->close()
						->close()
						->setOr()
						->open()
							// Заданное время окончания больше времени начали и меньше времени окончания
							->where('time_from', '<=', $this->timeTo)
							->open()
								->where('time_to', '>=', $this->timeTo)
								->setOr()
								->where('time_to', '=', '00:00:00')
							->close()
						->close()
					->close();
			}

			$oShop_Delivery_Conditions->queryBuilder()
				// Сортируем вывод
				->orderBy(Core_QueryBuilder::expression('IF ( `min_weight` > 0 AND `max_weight` > 0 AND `min_price` > 0 AND `max_price` > 0, 1, 0)'), 'DESC')
				->orderBy('min_weight', 'DESC')
				->orderBy('max_weight', 'DESC')
				->orderBy('min_price', 'DESC')
				->orderBy('max_price', 'DESC')
				->orderBy('price', 'DESC')
				->limit(1);

			$aShop_Delivery_Conditions = $oShop_Delivery_Conditions->findAll();

			// Проверяем выбрали ли хотя бы одну запись
			if (count($aShop_Delivery_Conditions) > 0)
			{
				return $aShop_Delivery_Conditions[0];
			}
			else
			{
				switch ($i)
				{
					// По порядку цикла отменяем ограничения, начинаем с района и заканчиваем страной
					case 0 :
						$shop_country_location_city_area_id = 0;
					break;
					case 1 :
						$shop_country_location_city_id = 0;
					break;
					case 2 :
						$shop_country_location_id = 0;
					break;
					case 3 :
						$shop_country_id = 0;
					break;
				}
			}
			$i++;
		}

		return NULL;
	}
}