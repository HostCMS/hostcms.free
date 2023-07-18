<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 54;
$sAdminFormAction = '/admin/shop/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title(Core::_('Shop.menu'))
	->pageTitle(Core::_('Shop.menu'));

if (!is_null(Core_Array::getGet('autocomplete'))
	&& Core_Array::getGet('shop_id')
	&& !is_null(Core_Array::getGet('queryString'))
)
{
	$sQuery = trim(
		Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))))
	);

	$aJSON = array();

	if (strlen($sQuery))
	{
		$sQueryLike = '%' . str_replace(' ', '%', $sQuery) . '%';

		$shop_id = intval(Core_Array::getGet('shop_id'));
		$oShop = Core_Entity::factory('Shop', $shop_id);

		$shop_warehouse_id = Core_Array::getGet('shop_warehouse_id');
		$price_mode = Core_Array::getGet('price_mode', 'shop'); // 'shop' or 'item'
		$datetime = Core_Array::getGet('datetime');

		$aTypes = Core_Array::getGet('types', array('items'));

		// Товары
		if (in_array('items', $aTypes))
		{
			$aAllPricesIDs = array();

			$aShop_Prices = $oShop->Shop_Prices->findAll(FALSE);
			foreach ($aShop_Prices as $oShop_Price)
			{
				$aAllPricesIDs[] = $oShop_Price->id;
			}

			// Указание валюты не обязательно
			$shop_currency_id = Core_Array::getGet('shop_currency_id');
			$oShop_Currency = is_numeric($shop_currency_id) && $shop_currency_id > 0
				? Core_Entity::factory('Shop_Currency', intval($shop_currency_id))
				: $oShop->Shop_Currency;

			$oShop_Controller = Shop_Controller::instance();

			$oShop_Items = $oShop->Shop_Items;
			$oShop_Items->queryBuilder()
				->select('shop_items.*', array(Core_QueryBuilder::expression("IF(shop_items.modification_id, CONCAT((SELECT m.name FROM shop_items AS m WHERE m.id = shop_items.modification_id AND m.deleted = 0), ', ', shop_items.name), shop_items.name)"), 'dataName'))
				->where('shop_items.shortcut_id', '=', 0)
				->where('shop_items.deleted', '=', 0)
				->havingOpen()
					->having('dataName', 'LIKE', $sQueryLike)
					->setOr()
					->having('shop_items.marking', 'LIKE', $sQueryLike)
					->setOr()
					->having('shop_items.id', 'LIKE', $sQueryLike)
				->havingClose()
				->limit(Core::$mainConfig['autocompleteItems'])
				->clearOrderBy()
				->orderBy(Core_QueryBuilder::raw('IF(shop_items.modification_id = 0, 0, 1)'), 'ASC')
				->orderBy('shop_items.name', 'ASC');

			$aShop_Items = $oShop_Items->findAll(FALSE);
			foreach ($aShop_Items as $oShop_Item)
			{
				$oShop_Item_Controller = new Shop_Item_Controller();
				$fCurrencyCoefficient = $price_mode == 'shop'
					? ($oShop_Item->Shop_Currency->id > 0 && $oShop->Shop_Currency->id > 0
						? $oShop_Controller->getCurrencyCoefficientInShopCurrency(
							$oShop_Item->Shop_Currency,
							$oShop_Currency
						)
						: 0
					)
					: 1;

				$price = $oShop_Item_Controller->getSpecialprice($oShop_Item->price, $oShop_Item, FALSE);

				$aPrice = $oShop_Item_Controller->calculatePriceInItemCurrency($price * $fCurrencyCoefficient, $oShop_Item);

				$measureName = $oShop_Item->shop_measure_id
					? htmlspecialchars($oShop_Item->Shop_Measure->name)
					: '';

				$aPrices = array();
				$aPrices[] = array(
					'id' => 0,
					'price' => $price * $fCurrencyCoefficient
				);

				foreach ($aAllPricesIDs as $shop_price_id)
				{
					$oShop_Item_Price = $oShop_Item->Shop_Item_Prices->getByShop_price_id($shop_price_id);

					$fTmp = !is_null($oShop_Item_Price)
						? $oShop_Item_Price->value
						: $price;

					$aPrices[] = array(
						'id' => $shop_price_id,
						'price' => $fTmp * $fCurrencyCoefficient
					);
				}

				$aWarehouses = array();

				//$rest = $oShop_Item->getRest();
				$rest = 0;

				$aShop_Warehouses = $oShop_Item->Shop->Shop_Warehouses->findAll(FALSE);
				foreach ($aShop_Warehouses as $oShop_Warehouse)
				{
					$count = $oShop_Warehouse->getRest($oShop_Item->id, $datetime);
					is_null($count) && $count = 0;

					$rest += $count;

					$aWarehouses[] = array(
						'id' => $oShop_Warehouse->id,
						'count' => $count
					);
				}

				$imageSmall = $oShop_Item->image_small
					? htmlspecialchars($oShop_Item->getSmallFileHref())
					: '';

				$oTmpCurrency = $price_mode == 'shop'
					? $oShop_Currency
					: $oShop_Item->Shop_Currency;

				$name = strval((string) $oShop_Item->name);

				if ($oShop_Item->modification_id)
				{
					$name .= ' → ' . htmlspecialchars($oShop_Item->Modification->name) . '';
				}
				elseif ($oShop_Item->shortcut_id)
				{
					$name .= ' → ' . htmlspecialchars($oShop_Item->Shop_Item->name) . '';
				}

				$aJSON[] = array(
					'type' => 'item',
					'id' => $oShop_Item->id,
					'label' => $name,
					'price' => $aPrice['price_tax'] - $aPrice['tax'],
					'price_formatWithCurrency' => $oTmpCurrency->formatWithCurrency($aPrice['price_tax'] - $aPrice['tax']),
					'price_with_tax' => $aPrice['price_tax'],
					'price_with_tax_formatWithCurrency' => $oTmpCurrency->formatWithCurrency($aPrice['price_tax']),
					'rate' => $aPrice['rate'],
					'marking' => strval($oShop_Item->marking), // NULL => ''
					'currency_id' => $oTmpCurrency->id,
					'currency' => $oTmpCurrency->sign,
					'measure' => $measureName,
					'measure_id' => $oShop_Item->shop_measure_id,
					'count' => $rest,
					'image_small' => $imageSmall,
					'aPrices' => $aPrices,
					'aWarehouses' => $aWarehouses,
					'link' => "/admin/shop/item/index.php?hostcms[action]=edit&hostcms[current]=1&hostcms[window]=id_content&shop_id={$oShop->id}&shop_dir_id={$oShop->shop_dir_id}&shop_group_id={$oShop_Item->shop_group_id}&hostcms[checked][1][{$oShop_Item->id}]=1"
				);
			}
		}

		// Доставки
		if (in_array('deliveries', $aTypes))
		{
			$oShop_Deliveries = $oShop->Shop_Deliveries;
			$oShop_Deliveries->queryBuilder()
				->where('shop_deliveries.name', 'LIKE', $sQueryLike)
				->where('shop_deliveries.active', '=', 1)
				->limit(Core::$mainConfig['autocompleteItems']);

			$aShop_Deliveries = $oShop_Deliveries->findAll(FALSE);

			foreach ($aShop_Deliveries as $oShop_Delivery)
			{
				$aJSON[] = array(
					'type' => 'delivery',
					'id' => $oShop_Delivery->id,
					'label' => strval((string) $oShop_Delivery->name),
					'price' => '',
					'price_with_tax' => '',
					'rate' => 0,
					'marking' => '',
					'currency' => '',
				);
			}
		}

		// Скидки
		if (in_array('discounts', $aTypes))
		{
			$datetime = Core_Date::timestamp2sql(time());

			$oShop_Purchase_Discounts = $oShop->Shop_Purchase_Discounts;

			$oShop_Purchase_Discounts->queryBuilder()
				->where('shop_purchase_discounts.name', 'LIKE', $sQueryLike)
				->where('shop_purchase_discounts.active', '=', 1)
				->where('shop_purchase_discounts.start_datetime', '<=', $datetime)
				->where('shop_purchase_discounts.end_datetime', '>=', $datetime)
				->limit(Core::$mainConfig['autocompleteItems']);

			$aShop_Purchase_Discounts = $oShop_Purchase_Discounts->findAll(FALSE);

			foreach ($aShop_Purchase_Discounts as $oShop_Purchase_Discount)
			{
				$aJSON[] = array(
					'type' => 'discount',
					'id' => $oShop_Purchase_Discount->id,
					'label' => strval((string) $oShop_Purchase_Discount->name),
					'price' => '',
					'price_with_tax' => '',
					'rate' => 0,
					'marking' => '',
					'currency' => '',
					'discount_type' => $oShop_Purchase_Discount->type,
					'discount_value' => $oShop_Purchase_Discount->value,
					'discount_position' => $oShop_Purchase_Discount->position,
				);
			}
		}
	}

	Core::showJson($aJSON);
}

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop.header_admin_forms'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Dir.shop_dir_top_menu_title'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop.show_sds_link'))
		->icon('fa fa-book')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Measure.mesures'))
				->icon('fa fa-tachometer')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sMeasuresFormPath = '/admin/shop/measure/index.php', NULL, NULL, $sAdditionalParam = "&shop_dir_id=" . intval(Core_Array::getGet('shop_dir_id', 0)))
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sMeasuresFormPath, NULL, NULL, $sAdditionalParam)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Country.shop_form_menu_countries'))
				->icon('fa fa-flag')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sCountriesFormPath = '/admin/shop/country/index.php', NULL, NULL, $sAdditionalParam = "&shop_dir_id=" . intval(Core_Array::getGet('shop_dir_id', 0)))
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sCountriesFormPath, NULL, NULL, $sAdditionalParam)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Codetype.title'))
				->icon('fa fa-code')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sCodesFormPath = '/admin/shop/codetype/index.php', NULL, NULL, $sAdditionalParam = "&shop_dir_id=" . intval(Core_Array::getGet('shop_dir_id', 0)))
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sCodesFormPath, NULL, NULL, $sAdditionalParam)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item_Type.title'))
				->icon('fa-solid fa-list-ol')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($sTypesFormPath = '/admin/shop/item/type/index.php', NULL, NULL, $sAdditionalParam = "shop_dir_id=" . intval(Core_Array::getGet('shop_dir_id', 0)))
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($sTypesFormPath, NULL, NULL, $sAdditionalParam)
				)
		)
	)->add(
	Admin_Form_Entity::factory('Menu')
	->name(Core::_('Shop.show_finance'))
	->icon('fa fa-usd')
	->add(
		Admin_Form_Entity::factory('Menu')
			->name(Core::_('Shop_Tax.show_tax_link'))
			->icon('fa fa-money')
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($sTaxFormPath = '/admin/shop/tax/index.php', NULL, NULL, '')
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($sTaxFormPath, NULL, NULL, '')
			)
	)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Currency.show_currency_link'))
		->icon('fa fa-eur')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($sCurrenciesFormPath = '/admin/shop/currency/index.php', NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($sCurrenciesFormPath, NULL, NULL, '')
		)
	)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

// Идентификатор родительской группы
$iShopDirId = intval(Core_Array::getGet('shop_dir_id', 0));

// Представитель класса хлебных крошек
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Создаем первую хлебную крошку
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop.menu'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
);

// Крошки строим только если: мы не в корне или идет редактирование
if ($iShopDirId)
{
	// Далее генерируем цепочку хлебных крошек от текущей группы к корневой
	$oShopDir = Core_Entity::factory('Shop_Dir')->find($iShopDirId);

	// Массив хлебных крошек
	$aBreadcrumbs = array();

	do
	{
		$additionalParams = 'shop_dir_id=' . intval($oShopDir->id);

		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopDir->name)
			->href(
				$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
			)
			->onclick(
				$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
			);
	} while ($oShopDir = $oShopDir->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add(
			$oAdmin_Form_Entity_Breadcrumb
		);
	}

	// Добавляем все хлебные крошки контроллеру
	$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
}

// Действие редактирования
$oAdmin_Form_Action = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$Shop_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Shop_Controller_Edit', $oAdmin_Form_Action
	);

	// Хлебные крошки для контроллера редактирования
	$Shop_Controller_Edit
		->addEntity(
			$oAdmin_Form_Entity_Breadcrumbs
		);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

// Действие "Перестроить фильтр"
$oAdminFormActionRebuildFilter = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('rebuildFilter');

if ($oAdminFormActionRebuildFilter && $oAdmin_Form_Controller->getAction() == 'rebuildFilter')
{
	$oShop_Filter_Controller_Rebuild = Admin_Form_Action_Controller::factory(
		'Shop_Filter_Controller_Rebuild', $oAdminFormActionRebuildFilter
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShop_Filter_Controller_Rebuild);
}

// Действие "Удалить файл watermark"
$oAdminFormActionDeleteWatermarkFile = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteWatermarkFile');

if ($oAdminFormActionDeleteWatermarkFile && $oAdmin_Form_Controller->getAction() == 'deleteWatermarkFile')
{
	$oShopControllerDeleteWatermarkFile = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAdminFormActionDeleteWatermarkFile
	);

	$oShopControllerDeleteWatermarkFile
		->methodName('deleteWatermarkFile')
		->divId(array('preview_large_watermark_file', 'delete_large_watermark_file'));

	// Добавляем контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oShopControllerDeleteWatermarkFile);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop_Dir')
);
$oAdmin_Form_Dataset->changeField('name', 'class', 'semi-bold');
$oAdmin_Form_Dataset
	->addCondition(
		array('select' =>
			array('*', array(Core_QueryBuilder::expression("''"), 'email'))
		)
	)
	->addCondition(
		array(
			'where' => array('parent_id', '=', $iShopDirId)
		)
	)
	->addCondition(
		array(
			'where' => array('site_id', '=', CURRENT_SITE)
		)
	);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Shop')
);

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset->addCondition(
		array('select' => array('shops.*'))
	)
	->addCondition(
		array(
			'leftJoin' => array('shop_currencies', 'shop_currencies.id', '=', 'shops.shop_currency_id')
		)
	)
	->addCondition(
		array(
			'where' => array('shop_dir_id', '=', $iShopDirId)
		)
	)
	->addCondition(
		array(
			'where' => array('site_id', '=', CURRENT_SITE)
		)
	)
	->changeField('name', 'link', '/admin/shop/item/index.php?shop_id={id}&shop_dir_id={shop_dir_id}')
	->changeField('name', 'onclick', "$.adminLoad({path: '/admin/shop/item/index.php', additionalParams: 'shop_id={id}&shop_dir_id={shop_dir_id}', windowId: '{windowId}'}); return false");

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();