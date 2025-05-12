<?php
/**
 * HostCMS bootstrap file.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
define('CMS_FOLDER', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('HOSTCMS', TRUE);

// ini_set("memory_limit", "128M");
// ini_set("max_execution_time", "60");

// Константа запрещает выполнение ini_set, по умолчанию false - разрешено
define('DENY_INI_SET', FALSE);

// Запрещаем установку локали, указанной в параметрах сайта
// define('ALLOW_SET_LOCALE', FALSE);
setlocale(LC_NUMERIC, "POSIX");

if (!defined('DENY_INI_SET') || !DENY_INI_SET)
{
	ini_set('display_errors', 1);
}

//function_exists('date_default_timezone_set') && date_default_timezone_set(date_default_timezone_get());

require_once(CMS_FOLDER . 'modules/core/core.php');

Core::init();

date_default_timezone_set(Core::$mainConfig['timezone']);

if (Core_Auth::logged())
{
	// Observers
	Core_Event::attach('Xsl_Processor.onBeforeProcess', array('Xsl_Processor_Observer', 'onBeforeProcess'));
	Core_Event::attach('Xsl_Processor.onAfterProcess', array('Xsl_Processor_Observer', 'onAfterProcess'));
	Core_Event::attach('Tpl_Processor.onBeforeProcess', array('Tpl_Processor_Observer', 'onBeforeProcess'));
	Core_Event::attach('Tpl_Processor.onAfterProcess', array('Tpl_Processor_Observer', 'onAfterProcess'));
	Core_Event::attach('Core_Cache.onBeforeGet', array('Core_Cache_Observer', 'onBeforeGet'));
	Core_Event::attach('Core_Cache.onAfterGet', array('Core_Cache_Observer', 'onAfterGet'));
	Core_Event::attach('Core_Cache.onBeforeSet', array('Core_Cache_Observer', 'onBeforeSet'));
	Core_Event::attach('Core_Cache.onAfterSet', array('Core_Cache_Observer', 'onAfterSet'));
}


// Robokassa SMS observers
// Core_Event::attach('shop_order.onAfterChangeStatusPaid', array('Shop_Observer_Robokassa', 'onAfterChangeStatusPaid'));
// Core_Event::attach('Shop_Payment_System_Handler.onAfterProcessOrder', array('Shop_Observer_Robokassa', 'onAfterProcessOrder'));

// Core_Database::instance()->query("SET SESSION sql_mode = ''");
// Core_Database::instance()->query('SET SESSION wait_timeout = 120');

//Shop_Controller::instance()->floatFormat("%.0f");
//Shop_Controller::instance()->decimalDigits(0);

// Windows locale
//setlocale(LC_ALL, array ('ru_RU.utf-8', 'rus_RUS.utf8'));

/*Core_Event::attach('Shop_Controller_YandexMarket.onAfterGetPictures', array('Hostcms_YM_Observer', 'onAfterGetPictures'));

class Hostcms_YM_Observer
{
	static public function onAfterGetPictures($controller, $args)
	{
		$oShop_Item = $args[0];

		$oShop = $oShop_Item->Shop;

		$siteAlias = $oShop->Site->getCurrentAlias();

		$aPictures = array();

		// Если модификация с пустым изображением и включено копирование groupModifications, то берем изображение основного товара.
		if ($controller->groupModifications && $oShop_Item->modification_id && $oShop_Item->image_large == '')
		{
			$aPictures[] = $controller->protocol . '://' . Core_Str::xml($siteAlias->name . '/yml.php?photo=' . $oShop_Item->Modification->id);
		}
		elseif ($oShop_Item->image_large != '')
		{
			$aPictures[] = $controller->protocol . '://' . Core_Str::xml($siteAlias->name . '/yml.php?photo=' . $oShop_Item->id);
		}

		$oEntity = $controller->groupModifications && $oShop_Item->modification_id
			? $oShop_Item->Modification
			: $oShop_Item;

		if (is_array($controller->additionalImages))
		{
			$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $oShop->id);

			foreach ($controller->additionalImages as $tag_name)
			{
				$oProperty = $linkedObject->Properties->getByTag_name($tag_name);

				if ($oProperty && $oProperty->type == 2)
				{
					$aProperty_Values = $oProperty->getValues($oEntity->id);

					foreach ($aProperty_Values as $oProperty_Value)
					{
						if ($oProperty_Value->file != '')
						{
							$aPictures[] = $controller->protocol . '://' . Core_Str::xml($siteAlias->name . '/yml.php?photo=' . $oEntity->id) . '&property=' . Core_Str::xml($oProperty_Value->id);
						}
					}
				}
			}
		}

		return $aPictures;
	}
}*/

/*class Hostcms_Shop_Order_Edit_Observer
{
    static public function onAfterRedeclaredPrepareForm($controller, $args)
    {
        switch (get_class($controller))
        {
            case 'Shop_Order_Controller_Edit':
				$controller->getField('company')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 hidden'));
				$controller->getField('fax')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 hidden'));
            break;
        }
   }
}

Core_Event::attach('Admin_Form_Action_Controller_Type_Edit.onAfterRedeclaredPrepareForm', array('Hostcms_Shop_Order_Edit_Observer', 'onAfterRedeclaredPrepareForm'));*/

/*class Hostcms_Cart_Observer
{
	static public function onAfterAddShopOrderItem($controller, $args)
	{
		list($oShop_Order_Item, $oShop_Cart) = $args;

		$oShop_Item_Parent = $oShop_Cart->Shop_Item;

		if ($oShop_Item_Parent->id && $oShop_Item_Parent->type == 3)
		{
			$oShop_Item_Controller = new Shop_Item_Controller();

			$setQuantity = $oShop_Order_Item->quantity;
			$setPrice = $oShop_Order_Item->price; // 10 000

			$aShop_Item_Sets = $oShop_Item_Parent->Shop_Item_Sets->findAll(FALSE);

			$totalItemPrices = 0;
			foreach ($aShop_Item_Sets as $oShop_Item_Set)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item', $oShop_Item_Set->shop_item_set_id);

				$oShop_Item = $oShop_Item->shortcut_id
					? $oShop_Item->Shop_Item
					: $oShop_Item;

				if (!is_null($oShop_Item->id))
				{
					$aPrices = $oShop_Item_Controller->getPrices($oShop_Item);
					$totalItemPrices += $aPrices['price_discount'] * $oShop_Item_Set->count;
				}
			}

			// var_dump($setPrice); // 10131.00"
			// var_dump($totalItemPrices); // float(16386)

			$delta = $totalItemPrices > 0
				? $setPrice / $totalItemPrices
				: 0;

			$createdAmount = 0;
			foreach ($aShop_Item_Sets as $oShop_Item_Set)
			{
				$oShop_Item = Core_Entity::factory('Shop_Item', $oShop_Item_Set->shop_item_set_id);

				$oShop_Item = $oShop_Item->shortcut_id
					? $oShop_Item->Shop_Item
					: $oShop_Item;

				if (!is_null($oShop_Item->id))
				{
					$oShop_Cart_New = clone $oShop_Cart;
					$oShop_Cart_New->shop_item_id = $oShop_Item->id;
					$oShop_Cart_New->quantity = $setQuantity * $oShop_Item_Set->count;

					// Заменяем цену на пропорциональную цене вхождения товара в комплекте
					$oNewShop_Order_Item = $controller->createOrderItem($oShop_Cart_New, $oShop_Item);
					$oNewShop_Order_Item->price = $oNewShop_Order_Item->price * $delta;
					$oNewShop_Order_Item->save();

					$createdAmount += $oNewShop_Order_Item->price * $oShop_Cart_New->quantity;
				}
			}

			// Разницу в копейках добавляем последнему товару
			if ($createdAmount < $setPrice)
			{
				$oNewShop_Order_Item->price += ($setPrice - $createdAmount) / $oShop_Cart_New->quantity;
				$oNewShop_Order_Item->save();
			}

			// var_dump($createdAmount);
			// die();

			$oShop_Order_Item->delete();
		}
	}
}

Core_Event::attach('Shop_Payment_System_Handler.onAfterAddShopOrderItem', array('Hostcms_Cart_Observer', 'onAfterAddShopOrderItem'));*/

/*class Shop_Order_Observer
{
	static public function onCalltextitemscount($object, $args)
	{
		return Core_Inflection::instance('ru')->numberInWords($object->Shop_Order_Items->getCount(FALSE));
	}
}
Core_Event::attach('shop_order.onCalltextitemscount', array('Shop_Order_Observer', 'onCalltextitemscount'));*/

/*class Hostcms_Shop_Order_Observer
{
	static public function onAfterProcessOrder($controller)
	{
		if (Core::moduleIsActive('field'))
		{
			$oShop_Order = $controller->getShopOrder();

			$aCompanies = array();

			$aShop_Order_Items = $oShop_Order->Shop_Order_Items->getAllByType(0, FALSE);
			foreach ($aShop_Order_Items as $oShop_Order_Item)
			{
				$oShop_Item = $oShop_Order_Item->Shop_Item;

				// Если модификация - берем родителя, так как у модификации shop_group_id всегда 0
				$oShop_Item = $oShop_Item->modification_id
					? $oShop_Item->Modification
					: $oShop_Item;

				$company_id = self::_getCompanyId($oShop_Item->shop_group_id);

				$aCompanies[$company_id ? $company_id : $oShop_Order->Shop->shop_company_id][] = $oShop_Order_Item;
			}

			$oNew_Shop_Order = $oShop_Order;
			foreach ($aCompanies as $company_id => $aShop_Order_Items)
			{
				// Second iterations
				if (!$oNew_Shop_Order)
				{
					$oNew_Shop_Order = clone $oShop_Order;
					$oNew_Shop_Order->company_id = $company_id;
					$oNew_Shop_Order->save();

					$oNew_Shop_Order->createInvoice();
					$oNew_Shop_Order->save();

					foreach ($aShop_Order_Items as $oShop_Order_Item)
					{
						$oShop_Order_Item->shop_order_id = $oNew_Shop_Order->id;
						$oShop_Order_Item->save();
					}
				}

				$oNew_Shop_Order->company_id = $company_id;
				$oNew_Shop_Order->save();

				$oNew_Shop_Order = NULL;
			}
		}
	}

	static protected function _getCompanyId($shop_group_id)
	{
		$oShop_Group = Core_Entity::factory('Shop_Group')->getById($shop_group_id, FALSE);

		if (!is_null($oShop_Group))
		{
			$oField = Core_Entity::factory('Field')->getByTag_name('company_id');

			$aTmpGroup = $oShop_Group;

			do {
				if (!is_null($oField))
				{
					$aField_Values = $oField->getValues($aTmpGroup->id, FALSE);

					if (isset($aField_Values[0]) && $aField_Values[0]->value)
					{
						return $aField_Values[0]->value;
					}
				}
			} while ($aTmpGroup = $aTmpGroup->getParent());
		}

		return 0;
	}
}

Core_Event::attach('Shop_Payment_System_Handler.onAfterProcessOrder', array('Hostcms_Shop_Order_Observer', 'onAfterProcessOrder'));*/

/*class Hostcms_ASMP_MegaImport_Observer
{
	static public function onBeforeImportPrices($controller, $args)
	{
		list($oShop_Item, $aProces) = $args;

		if (Core::moduleIsActive('field'))
		{
			$oField = Core_Entity::factory('Field')->getByTag_name('fix_price');

			if (!is_null($oField))
			{
				$aField_Values = $oField->getValues($oShop_Item->id, FALSE);

				// Не импортировать цены, очищаем присланный массив
				if (isset($aField_Values[0]) && $aField_Values[0]->value)
				{
					return array();
				}
			}
		}
	}
}

Core_Event::attach('ASMP_MegaImport_Controller.onBeforeImportPrices', array('Hostcms_ASMP_MegaImport_Observer', 'onBeforeImportPrices'));*/

/*Core_Router::addGlobalMiddleware('test', function(Core_Command_Controller $oController, callable $next) {
	// Выполнить действие

	return $next();
});*/


/*Core_Event::attach('shop_item.onCallmultiLng', function($object, $args) {
	var_dump($args);
});*/