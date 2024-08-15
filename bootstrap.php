<?php
/**
 * HostCMS bootstrap file.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2024 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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