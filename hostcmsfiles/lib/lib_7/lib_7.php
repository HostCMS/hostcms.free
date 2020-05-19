<?php

$Shop_Cart_Controller_Show = Core_Page::instance()->object;

$oShop = $Shop_Cart_Controller_Show->getEntity();

Shop_Payment_System_Handler::checkAfterContent($oShop);

Shop_Delivery_Handler::checkAfterContent($oShop);

// ------------------------------------------------
// Вывод информации о статусе платежа после его совершения и перенаправления с платежной системы
// ------------------------------------------------
if (isset($_REQUEST['payment'])
	|| isset($_GET['action']) && ($_GET['action'] == 'PaymentSuccess' || $_GET['action'] == 'PaymentFail')
	|| isset($_REQUEST['pg_order_id'])
)
{
	// Получаем ID заказа
	if (isset($_REQUEST['order_id']))
	{
		$order_id = intval(Core_Array::getRequest('order_id'));
	}
	//от Яндекса
	elseif(isset($_GET['orderNumber']))
	{
		$order_id = intval(Core_Array::getGet('orderNumber'));
	}
	//от PayPal
	elseif(isset($_REQUEST['invoice']))
	{
		$oShop_Order = Core_Entity::factory('Shop_Order')->getByGuid(Core_Array::getRequest('invoice'));
		$order_id = $oShop_Order ? intval($oShop_Order->id) : NULL;
	}
	//от IntellectMoney
	elseif(isset($_REQUEST['orderId']))
	{
		$order_id = intval(Core_Array::getGet('orderId'));
	}
	//от Platron
	elseif(isset($_REQUEST['pg_order_id']))
	{
		$order_id = intval(Core_Array::getRequest('pg_order_id'));
	}
	else
	{
		$order_id = intval(Core_Array::getRequest('InvId'));
	}

	$oShop_Order = Core_Entity::factory('Shop_Order')->find($order_id);

	if (Core::moduleIsActive('siteuser'))
	{
		$siteuser_id = 0;

		$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();
		if ($oSiteuser)
		{
			$siteuser_id = $oSiteuser->id;
		}
	}
	else
	{
		$siteuser_id = FALSE;
	}

	// Если заказ принадлежит текущему авторизированному пользователю
	if ($oShop_Order->siteuser_id == $siteuser_id)
	{
		if (Core_Array::getRequest('payment') == 'success' || Core_Array::getRequest('action') == 'PaymentSuccess' || Core_Array::getRequest('pg_order_id') > 0)
		{
			?><h1>Подтверждение платежа</h1>
			<p>Спасибо, информация об оплате заказа <strong>№ <?php echo $oShop_Order->invoice?></strong>
получена.</p>
			<?php
		}
		else
		{
			?><h1>Платеж не получен</h1>
			<p>К сожалению при оплате заказа <strong>№ <?php echo $oShop_Order->invoice?></strong> произошла ошибка.</p>
			<?php
		}
	}
	// Для случаев, когда отключен модуль "Пользователи сайта"
	elseif ($siteuser_id === FALSE)
	{
		?><h1>Подтверждение платежа</h1>
		<p>Благодарим за посещение нашего магазина!</p>
		<?php
	}
	else
	{
		?><h1>Ошибка</h1>
		<p>Неверный номер заказа!</p>
		<?php
	}

	// Прерываем выполнение типовой динамической страницы
	return TRUE;
}

Core_Session::start();

if (Core_Array::getPost('oneStepCheckout'))
{
	// Сбрасываем информацию о последнем заказе
	$_SESSION['last_order_id'] = 0;

	if (!is_null(Core_Array::getRequest('apply_bonuses')))
	{
		$_SESSION['hostcmsOrder']['bonuses'] = trim(strval(Core_Array::getRequest('bonuses')));
	}

	// Оформление в один шаг
	$Shop_Cart_Controller = Shop_Cart_Controller::instance();
	$aShop_Cart = $Shop_Cart_Controller->getAll($oShop);
	foreach ($aShop_Cart as $oShop_Cart)
	{
		$Shop_Cart_Controller
			->shop_item_id($oShop_Cart->shop_item_id)
			->delete();
	}

	$shop_item_id = intval(Core_Array::getRequest('shop_item_id'));

	if ($shop_item_id)
	{
		Shop_Cart_Controller::instance()
			->shop_item_id($shop_item_id)
			->quantity(Core_Array::getRequest('count', 1))
			->add();
	}

	$_SESSION['hostcmsOrder'] = array();

	$_SESSION['hostcmsOrder']['shop_country_id'] = intval(Core_Array::getPost('shop_country_id', 0));
	$_SESSION['hostcmsOrder']['shop_country_location_id'] = intval(Core_Array::getPost('shop_country_location_id', 0));
	$_SESSION['hostcmsOrder']['shop_country_location_city_id'] = intval(Core_Array::getPost('shop_country_location_city_id', 0));
	$_SESSION['hostcmsOrder']['shop_country_location_city_area_id'] = intval(Core_Array::getPost('shop_country_location_city_area_id', 0));
	$_SESSION['hostcmsOrder']['postcode'] = Core_Str::stripTags(strval(Core_Array::getPost('postcode')));
	$_SESSION['hostcmsOrder']['address'] = Core_Str::stripTags(strval(Core_Array::getPost('address')));
	$_SESSION['hostcmsOrder']['house'] = Core_Str::stripTags(strval(Core_Array::getPost('house')));
	$_SESSION['hostcmsOrder']['flat'] = Core_Str::stripTags(strval(Core_Array::getPost('flat')));
	$_SESSION['hostcmsOrder']['surname'] = Core_Str::stripTags(strval(Core_Array::getPost('surname')));
	$_SESSION['hostcmsOrder']['name'] = Core_Str::stripTags(strval(Core_Array::getPost('name')));
	$_SESSION['hostcmsOrder']['patronymic'] = Core_Str::stripTags(strval(Core_Array::getPost('patronymic')));
	$_SESSION['hostcmsOrder']['phone'] = Core_Str::stripTags(strval(Core_Array::getPost('phone')));
	$_SESSION['hostcmsOrder']['email'] = Core_Str::stripTags(strval(Core_Array::getPost('email')));
	$_SESSION['hostcmsOrder']['description'] = Core_Str::stripTags(strval(Core_Array::getPost('description')));

	// Additional order properties
	$_SESSION['hostcmsOrder']['properties'] = array();

	$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $oShop->id);

	$aProperties = $oShop_Order_Property_List->Properties->findAll();
	foreach ($aProperties as $oProperty)
	{
		// Св-во может иметь несколько значений
		$aPropertiesValue = Core_Array::getPost('property_' . $oProperty->id);

		if (!is_null($aPropertiesValue))
		{
			!is_array($aPropertiesValue) && $aPropertiesValue = array($aPropertiesValue);
			foreach ($aPropertiesValue as $sPropertyValue)
			{
				$_SESSION['hostcmsOrder']['properties'][] = array($oProperty->id, $sPropertyValue);
			}
		}
	}

	$shop_delivery_condition_id = strval(Core_Array::getPost('shop_delivery_condition_id', 0));

	if (is_numeric($shop_delivery_condition_id))
	{
		$_SESSION['hostcmsOrder']['shop_delivery_condition_id'] = intval($shop_delivery_condition_id);

		$oShop_Delivery_Condition = Core_Entity::factory('Shop_Delivery_Condition', $_SESSION['hostcmsOrder']['shop_delivery_condition_id']);
		$_SESSION['hostcmsOrder']['shop_delivery_id'] = $oShop_Delivery_Condition->shop_delivery_id;
	}

	$_POST['step'] = 4;
}

switch (Core_Array::getPost('recount') ? 0 : Core_Array::getPost('step'))
{
	// Адрес доставки
	case 1:
		// Сбрасываем информацию о последнем заказе
		$_SESSION['last_order_id'] = 0;

		if (!is_null(Core_Array::getRequest('apply_bonuses')))
		{
			$_SESSION['hostcmsOrder']['bonuses'] = trim(strval(Core_Array::getRequest('bonuses')));
		}

		$Shop_Address_Controller_Show = new Shop_Address_Controller_Show($oShop);

		$Shop_Address_Controller_Show->xsl(
				Core_Entity::factory('Xsl')->getByName(
					Core_Array::get(Core_Page::instance()->libParams, 'deliveryAddressXsl')
				)
			)
			->show();
	break;
	// Способ доставки
	case 2:
		$_SESSION['hostcmsOrder']['shop_country_id'] = intval(Core_Array::getPost('shop_country_id', 0));
		$_SESSION['hostcmsOrder']['shop_country_location_id'] = intval(Core_Array::getPost('shop_country_location_id', 0));
		$_SESSION['hostcmsOrder']['shop_country_location_city_id'] = intval(Core_Array::getPost('shop_country_location_city_id', 0));
		$_SESSION['hostcmsOrder']['shop_country_location_city_area_id'] = intval(Core_Array::getPost('shop_country_location_city_area_id', 0));
		$_SESSION['hostcmsOrder']['postcode'] = Core_Str::stripTags(strval(Core_Array::getPost('postcode')));
		$_SESSION['hostcmsOrder']['address'] = Core_Str::stripTags(strval(Core_Array::getPost('address')));
		$_SESSION['hostcmsOrder']['house'] = Core_Str::stripTags(strval(Core_Array::getPost('house')));
		$_SESSION['hostcmsOrder']['flat'] = Core_Str::stripTags(strval(Core_Array::getPost('flat')));
		$_SESSION['hostcmsOrder']['surname'] = Core_Str::stripTags(strval(Core_Array::getPost('surname')));
		$_SESSION['hostcmsOrder']['name'] = Core_Str::stripTags(strval(Core_Array::getPost('name')));
		$_SESSION['hostcmsOrder']['patronymic'] = Core_Str::stripTags(strval(Core_Array::getPost('patronymic')));
		$_SESSION['hostcmsOrder']['company'] = Core_Str::stripTags(strval(Core_Array::getPost('company')));
		$_SESSION['hostcmsOrder']['phone'] = Core_Str::stripTags(strval(Core_Array::getPost('phone')));
		$_SESSION['hostcmsOrder']['fax'] = Core_Str::stripTags(strval(Core_Array::getPost('fax')));
		$_SESSION['hostcmsOrder']['email'] = Core_Str::stripTags(strval(Core_Array::getPost('email')));
		$_SESSION['hostcmsOrder']['description'] = Core_Str::stripTags(strval(Core_Array::getPost('description')));
		$_SESSION['hostcmsOrder']['tin'] = Core_Str::stripTags(strval(Core_Array::getPost('tin')));
		$_SESSION['hostcmsOrder']['kpp'] = Core_Str::stripTags(strval(Core_Array::getPost('kpp')));

		// Additional order properties
		$_SESSION['hostcmsOrder']['properties'] = array();

		$oShop_Order_Property_List = Core_Entity::factory('Shop_Order_Property_List', $oShop->id);

		$aProperties = $oShop_Order_Property_List->Properties->findAll();
		foreach ($aProperties as $oProperty)
		{
			// Св-во может иметь несколько значений
			$aPropertiesValue = $oProperty->type != 2
				? Core_Array::getPost('property_' . $oProperty->id)
				: Core_Array::getFiles('property_' . $oProperty->id, array());

			if (!is_null($aPropertiesValue))
			{
				// Not file
				if ($oProperty->type != 2)
				{
					!is_array($aPropertiesValue) && $aPropertiesValue = array($aPropertiesValue);
					foreach ($aPropertiesValue as $sPropertyValue)
					{
						$_SESSION['hostcmsOrder']['properties'][] = array($oProperty->id, $sPropertyValue);
					}
				}
				// Files
				elseif (is_array($aPropertiesValue))
				{
					if (isset($aPropertiesValue['name']))
					{
						if (Core_File::isValidExtension($aPropertiesValue['name'], Core::$mainConfig['availableExtension']))
						{
							try
							{
								$sTempFileName = tempnam(CMS_FOLDER . TMP_DIR, "ord");

								if ($sTempFileName !== FALSE)
								{
									// Copy uploaded file to the temp dir
									Core_File::copy($aPropertiesValue['tmp_name'], $sTempFileName);
									Core_File::delete($aPropertiesValue['tmp_name']);

									// Replace path
									$aPropertiesValue['tmp_name'] = $sTempFileName;

									// Save to session
									$_SESSION['hostcmsOrder']['properties'][] = array($oProperty->id, $aPropertiesValue);
								}
							}
							catch (Exception $e) {};
						}
					}
				}
			}
		}

		$Shop_Delivery_Controller_Show = new Shop_Delivery_Controller_Show($oShop);

		$Shop_Delivery_Controller_Show
			->shop_country_id($_SESSION['hostcmsOrder']['shop_country_id'])
			->shop_country_location_id($_SESSION['hostcmsOrder']['shop_country_location_id'])
			->shop_country_location_city_id($_SESSION['hostcmsOrder']['shop_country_location_city_id'])
			->shop_country_location_city_area_id($_SESSION['hostcmsOrder']['shop_country_location_city_area_id'])
			->couponText(
				Core_Str::stripTags(
					Core_Array::get(Core_Array::get($_SESSION, 'hostcmsOrder', array()), 'coupon_text')
				)
			)
			->postcode($_SESSION['hostcmsOrder']['postcode'])
			->setUp()
			->xsl(
				Core_Entity::factory('Xsl')->getByName(
					Core_Array::get(Core_Page::instance()->libParams, 'deliveryXsl')
				)
			)
			->show();
	break;
	// Форма оплаты
	case 3:
		$Shop_Payment_System_Controller_Show = new Shop_Payment_System_Controller_Show($oShop);

		$shop_delivery_condition_id = strval(Core_Array::getPost('shop_delivery_condition_id', 0));

		if (is_numeric($shop_delivery_condition_id))
		{
			$_SESSION['hostcmsOrder']['shop_delivery_condition_id'] = intval($shop_delivery_condition_id);

			$oShop_Delivery_Condition = Core_Entity::factory('Shop_Delivery_Condition', $_SESSION['hostcmsOrder']['shop_delivery_condition_id']);
			$_SESSION['hostcmsOrder']['shop_delivery_id'] = $oShop_Delivery_Condition->shop_delivery_id;
		}
		else
		{
			$_SESSION['hostcmsOrder']['shop_delivery_condition_id'] = 0;

			// в shop_delivery_condition_id тогда "10-123#", ID элемента массива в сессии, в котором
			// хранится стоимость доставки, налог, название специфичного условия доставки
			list($shopDeliveryInSession) = explode('#', $shop_delivery_condition_id);

			list($shop_delivery_id, $position) = explode('-', $shopDeliveryInSession);

			$oShop_Delivery = $oShop->Shop_Deliveries->getById($shop_delivery_id);

			if (!is_null($oShop_Delivery))
			{
				$oShop_Delivery_Handler = Shop_Delivery_Handler::factory($oShop_Delivery);
				$oShop_Delivery_Handler->process($position);
			}
		}

		$Shop_Payment_System_Controller_Show
			->shop_delivery_id(Core_Array::get($_SESSION['hostcmsOrder'], 'shop_delivery_id'))
			->xsl(
				Core_Entity::factory('Xsl')->getByName(
					Core_Array::get(Core_Page::instance()->libParams, 'paymentSystemXsl')
				)
			)
			->show();
	break;
	// Окончание оформления заказа
	case 4:
		// Проверяем наличие товара в корзины
		$Shop_Cart_Controller = Shop_Cart_Controller::instance();

		$aShop_Cart = $Shop_Cart_Controller->getAll($oShop);

		// А корзине есть товары или заполнен номер последнего заказа
		if (count($aShop_Cart) || Core_Array::get($_SESSION, 'last_order_id'))
		{
			$shop_payment_system_id
				= $_SESSION['hostcmsOrder']['shop_payment_system_id']
				= intval(Core_Array::getPost('shop_payment_system_id', 0));

			// Оплата бонусами с лицевого счета
			$_SESSION['hostcmsOrder']['partial_payment_by_personal_account'] = Core_Array::getPost('partial_payment_by_personal_account', 0);

			// Если выбрана платежная система
			if ($shop_payment_system_id > 0)
			{
				$oShop_Payment_System = $oShop->Shop_Payment_Systems->getById($shop_payment_system_id);

				if ($oShop_Payment_System)
				{
					Shop_Payment_System_Handler::factory(
						$oShop_Payment_System
					)
					//->allowOrderPropertyFiles(TRUE)
					->orderParams($_SESSION['hostcmsOrder'])
					->execute();
				}
				else
				{
					?><h1>Ошибка! Не выбрана платежная система.</h1><?php
				}
			}
			else
			{
				?><h1>Ошибка! Не указана ни одна платежная система.</h1><?php
			}

			// Иначе не прерываем и выводим последний шаг - обычная корзина
			break;
		}
	default:
		$xslName = Core_Array::get(Core_Page::instance()->libParams, 'cartXsl');

		$Shop_Cart_Controller_Show
			->couponText(
				Core_Str::stripTags(Core_Array::get(Core_Array::getSession('hostcmsOrder', array()), 'coupon_text'))
			)
			->xsl(
				Core_Entity::factory('Xsl')->getByName($xslName)
			)
			// ->itemsForbiddenTags(array('description', 'text'))
			->show();
}

// Блок авторизации пользователя
if (Core::moduleIsActive('siteuser'))
{
	$oSiteuser = Core_Entity::factory('Siteuser')->getCurrent();

	if (is_null($oSiteuser))
	{
		// Авторизация
		$Siteuser_Controller_Show = new Siteuser_Controller_Show(
			Core_Entity::factory('Siteuser')
		);

		$Siteuser_Controller_Show
			->location(Core::$url['path'])
			->xsl(
				Core_Entity::factory('Xsl')->getByName(
					Core_Array::get(Core_Page::instance()->libParams, 'userAuthorizationXsl')
				)
			)
			->show();

		// Регистрация
		$Siteuser_Controller_Show = new Siteuser_Controller_Show(
			Core_Entity::factory('Siteuser')
		);

		$Siteuser_Controller_Show->xsl(
				Core_Entity::factory('Xsl')->getByName(
					Core_Array::get(Core_Page::instance()->libParams, 'userRegistrationXsl')
				)
			)
			->location(Core::$url['path'])
			->fastRegistration(TRUE)
			->properties(TRUE)
			//->showMaillists(TRUE)
			->show();
	}
}