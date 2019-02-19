<?php
/**
 * Online shop.
 *
 * @package HostCMS
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
require_once('../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Код формы
$iAdmin_Form_Id = 65;
$sFormAction = '/admin/shop/item/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));
$oShopDir = $oShop->Shop_Dir;

$sFormTitle = $oShop->name;

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module::factory($sModule))
	->setUp()
	->path($sFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

if (!is_null(Core_Array::getGet('loadBarcodesList')) && !is_null(Core_Array::getGet('term')))
{
	$aJSON = array();

	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('term'))));

	if (strlen($sQuery))
	{
		try {
			$Core_Http = Core_Http::instance('curl')
				->clear()
				->url('http://barcode.hostcms.ru/api/')
				->method('POST')
				->port(80)
				->timeout(5)
				->additionalHeader('Barrequest', 'ubggjgfnfv')
				->data('barcode', $sQuery)
				->execute();

			$aResponse = json_decode($Core_Http->getBody(), TRUE);

			if (count($aResponse))
			{
				foreach ($aResponse as $aBarcode)
				{
					$aJSON[] = array(
						'id' => $aBarcode['barcode'],
						'text' => $aBarcode['barcode'],
						'name' => $aBarcode['name'],
					);
				}
			}
		}
		catch (Exception $e) {}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('shortcuts')) && !is_null(Core_Array::getGet('term')))
{
	$aJSON = array();

	$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('term')))));
	$iShopId = intval(Core_Array::getGet('shop_id'));
	$oShop = Core_Entity::factory('Shop', $iShopId);

	if (strlen($sQuery))
	{
		$oShop_Groups = $oShop->Shop_Groups;
		$oShop_Groups->queryBuilder()
			->where('shop_groups.name', 'LIKE', '%' . $sQuery . '%')
			->limit(Core::$mainConfig['autocompleteItems']);

		$aShop_Groups = $oShop_Groups->findAll(FALSE);

		foreach ($aShop_Groups as $oShop_Group)
		{
			$sParents = $oShop_Group->groupPathWithSeparator();

			$aJSON[] = array(
				'id' => $oShop_Group->id,
				'text' => $sParents . ' [' . $oShop_Group->id . ']',
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_move_groups'))
	&& !is_null(Core_Array::getGet('queryString'))
	&& Core_Array::getGet('entity_id')
)
{
	$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString')))));
	$entity_id = intval(Core_Array::getGet('entity_id'));
	$mode = intval(Core_Array::getGet('mode'));

	$oShop = Core_Entity::factory('Shop', $entity_id);

	$aExclude = strlen(Core_Array::getGet('exclude'))
		? json_decode(Core_Array::getGet('exclude'), TRUE)
		: array();

	$aJSON = array();

	if (strlen($sQuery))
	{
		$aJSON[0] = array(
			'id' => 0,
			'label' => Core::_('Shop_Item.root') . ' [0]'
		);

		$oShop_Groups = $oShop->Shop_Groups;
		$oShop_Groups->queryBuilder()
			->limit(Core::$mainConfig['autocompleteItems']);

		switch ($mode)
		{
			// Вхождение
			case 0:
			default:
				$oShop_Groups->queryBuilder()->where('shop_groups.name', 'LIKE', '%' . $sQuery . '%');
			break;
			// Вхождение с начала
			case 1:
				$oShop_Groups->queryBuilder()->where('shop_groups.name', 'LIKE', $sQuery . '%');
			break;
			// Вхождение с конца
			case 2:
				$oShop_Groups->queryBuilder()->where('shop_groups.name', 'LIKE', '%' . $sQuery);
			break;
			// Точное вхождение
			case 3:
				$oShop_Groups->queryBuilder()->where('shop_groups.name', '=', $sQuery);
			break;
		}

		count($aExclude) && $oShop_Groups->queryBuilder()
			->where('shop_groups.id', 'NOT IN', $aExclude);

		$aShop_Groups = $oShop_Groups->findAll();

		foreach ($aShop_Groups as $oShop_Group)
		{
			$sParents = $oShop_Group->groupPathWithSeparator();

			$aJSON[] = array(
				'id' => $oShop_Group->id,
				'label' => $sParents . ' [' . $oShop_Group->id . ']'
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_shortcut_groups'))
	&& !is_null(Core_Array::getGet('queryString'))
	&& Core_Array::getGet('entity_id')
)
{
	$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString')))));
	$entity_id = intval(Core_Array::getGet('entity_id'));
	$mode = intval(Core_Array::getGet('mode'));

	$oShop = Core_Entity::factory('Shop', $entity_id);

	$aJSON = array();

	if (strlen($sQuery))
	{
		$aJSON[0] = array(
			'id' => 0,
			'label' => Core::_('Shop_Item.root') . ' [0]'
		);

		$oShop_Groups = $oShop->Shop_Groups;
		$oShop_Groups->queryBuilder()
			->limit(Core::$mainConfig['autocompleteItems']);

		switch ($mode)
		{
			// Вхождение
			case 0:
			default:
				$oShop_Groups->queryBuilder()->where('shop_groups.name', 'LIKE', '%' . $sQuery . '%');
			break;
			// Вхождение с начала
			case 1:
				$oShop_Groups->queryBuilder()->where('shop_groups.name', 'LIKE', $sQuery . '%');
			break;
			// Вхождение с конца
			case 2:
				$oShop_Groups->queryBuilder()->where('shop_groups.name', 'LIKE', '%' . $sQuery);
			break;
			// Точное вхождение
			case 3:
				$oShop_Groups->queryBuilder()->where('shop_groups.name', '=', $sQuery);
			break;
		}

		$aShop_Groups = $oShop_Groups->findAll();

		foreach ($aShop_Groups as $oShop_Group)
		{
			$sParents = $oShop_Group->groupPathWithSeparator();

			$aJSON[] = array(
				'id' => $oShop_Group->id,
				'label' => $sParents . ' [' . $oShop_Group->id . ']'
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete'))
	&& !is_null(Core_Array::getGet('show_modification'))
	&& !is_null(Core_Array::getGet('queryString'))
)
{
	$sQuery = trim(Core_Str::stripTags(strval(Core_Array::getGet('queryString'))));
	$iShopItemId = intval(Core_Array::getGet('shop_item_id'));
	$oShop_Item = Core_Entity::factory('Shop_Item', $iShopItemId);

	$aJSON = array(
		'id' => 0,
		'label' => Core::_('Shop_Item.modifications_root') . ' [0]'
	);

	if (strlen($sQuery))
	{
		$aTmp = Shop_Item_Controller_Edit::fillModificationList($oShop_Item, $sQuery);

		foreach ($aTmp as $key => $value)
		{
			$key && $aJSON[] = array(
				'id' => $key,
				'label' => $value
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getGet('autocomplete')) && !is_null(Core_Array::getGet('queryString')))
{
	$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString')))));
	$iShopId = intval(Core_Array::getGet('shop_id'));
	$oShop = Core_Entity::factory('Shop', $iShopId);

	$aJSON = array();

	if (strlen($sQuery))
	{
		if (is_null(Core_Array::getGet('show_group')))
		{
			$iShopGroupId = intval(Core_Array::getGet('shop_group_id'));

			$aConfig = Core_Config::instance()->get('property_config', array()) + array(
				'select_modifications' => TRUE,
			);

			$oShop_Items = $oShop->Shop_Items;
			$oShop_Items->queryBuilder()
				->where('shop_items.shop_group_id', '=', $iShopGroupId)
				->where('shop_items.name', 'LIKE', '%' . $sQuery . '%')
				->limit(Core::$mainConfig['autocompleteItems']);

			$aShop_Items = $oShop_Items->findAll(FALSE);

			foreach ($aShop_Items as $oShop_Item)
			{
				$aJSON[] = array(
					'id' => $oShop_Item->id,
					'label' => $oShop_Item->name,
				);

				// Shop Item's modifications
				if ($aConfig['select_modifications'])
				{
					$oModifications = $oShop_Item->Modifications;

					$oModifications
						->queryBuilder()
						->clearOrderBy()
						->clearSelect()
						->select('id', 'shortcut_id', 'name');

					$aModifications = $oModifications->findAll(FALSE);

					foreach ($aModifications as $oModification)
					{
						$aJSON[] = array(
							'id' => $oModification->id,
							'label' => ' — ' . $oModification->name
						);
					}
				}
			}
		}
		else
		{
			$aJSON = array(
				'id' => 0,
				'label' => Core::_('Shop_Item.root'),
			);

			$oShop_Groups = $oShop->Shop_Groups;
			$oShop_Groups->queryBuilder()
				->where('shop_groups.name', 'LIKE', '%' . $sQuery . '%')
				->limit(Core::$mainConfig['autocompleteItems']);

			$aShop_Groups = $oShop_Groups->findAll(FALSE);

			foreach ($aShop_Groups as $oShop_Group)
			{
				$aParentGroups = array();

				$aTmpGroup = $oShop_Group;

				// Добавляем все директории от текущей до родителя.
				do {
					$aParentGroups[] = $aTmpGroup->name;
				} while ($aTmpGroup = $aTmpGroup->getParent());

				$sParents = implode(' → ', array_reverse($aParentGroups));

				$aJSON[] = array(
					'id' => $oShop_Group->id,
					'label' => $sParents . ' [' . $oShop_Group->id . ']',
				);
			}
		}
	}

	Core::showJson($aJSON);
}

// Меню формы
$oMenu = Admin_Form_Entity::factory('Menus');

$additionalParams = "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}";

// Элементы меню
$oMenu->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Item.links_items'))
		->icon('fa fa-file-text-o')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.links_items_add'))
				->icon('fa fa-plus')
				->img('/admin/images/page_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref
					(
						$oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0
					)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax
					(
						$oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0
					)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.shops_add_form_link_properties'))
				->icon('fa fa-cogs')
				->img('/admin/images/page_gear.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/property/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/property/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.properties_item_for_groups_link'))
				->icon('fa fa-folder-open-o')
				->img('/admin/images/folder_page_gear.gif')
				->href(
          $oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/property/for/group/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
          $oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/property/for/group/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.items_catalog_add_form_comment_link'))
				->icon('fa fa-comments')
				->img('/admin/images/comments.gif')
				->href(
          $oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/comment/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
          $oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/comment/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.change_prices_for_shop_group'))
				->icon('fa fa-usd')
				->img('/admin/images/service.gif')
				->href(
          $oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/change/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
          $oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/change/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.import_price_list_link'))
				->icon('fa fa-download')
				->img('/admin/images/import.gif')
				->href(
          $oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/import/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
          $oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/import/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.export_shop'))
				->icon('fa fa-upload')
				->img('/admin/images/export.gif')
				->href(
          $oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/export/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
          $oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/export/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.item_cards'))
				->icon('fa fa-tag')
				->img('/admin/images/export.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/card/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/card/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.item_warehouse'))
				->icon('fa fa-balance-scale')
				->img('/admin/images/export.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/item/warehouse/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/item/warehouse/index.php', NULL, NULL, $additionalParams)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Group.links_groups'))
		->icon('fa fa-folder-o')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Group.links_groups_add'))
				->icon('fa fa-plus')
				->img('/admin/images/folder_add.gif')
				->href(
					$oAdmin_Form_Controller->getAdminActionLoadHref
					(
						$oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0
					)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminActionLoadAjax
					(
						$oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0
					)
				)
		)
    ->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Group.properties'))
				->icon('fa fa-cogs')
				->img('/admin/images/folder_gear.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/group/property/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/group/property/index.php', NULL, NULL, $additionalParams)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Item.shops_link_orders'))
		->icon('fa fa-shopping-cart')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/index.php', NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/index.php', NULL, NULL, $additionalParams)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Item.show_delivery_on'))
		->icon('fa fa-truck')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/delivery/index.php', NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/delivery/index.php', NULL, NULL, $additionalParams)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Item.show_sds_link'))
		->icon('fa fa-book')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.show_prices_title'))
				->icon('fa fa-usd')
				->img('/admin/images/prices.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/price/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/price/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.system_of_pays'))
				->icon('fa fa-credit-card')
				->img('/admin/images/payment.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/payment/system/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/payment/system/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.print_forms'))
				->icon('fa fa-print')
				->img('/admin/images/payment.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/print/form/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/print/form/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.show_producers_link'))
				->icon('fa fa-building-o')
				->img('/admin/images/company.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/producer/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/producer/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.show_sellers_link'))
				->icon('fa fa-trademark')
				->img('/admin/images/company.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/seller/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/seller/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.main_menu_warehouses_list'))
				->icon('fa fa-balance-scale')
				->img('/admin/images/company.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/warehouse/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/warehouse/index.php', NULL, NULL, $additionalParams)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Item.show_reports_title'))
		->icon('fa fa-book')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.show_sales_order_link'))
				->icon('fa fa-book')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/report/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/report/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.show_brands_order_link'))
				->icon('fa fa-book')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/order/report/producer/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/order/report/producer/index.php', NULL, NULL, $additionalParams)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Item.shop_menu_title'))
		->icon('fa fa-money')
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.show_discount_link'))
				->icon('fa fa-money')
				->img('/admin/images/money.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/discount/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/discount/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.order_discount_show_title'))
				->icon('fa fa-money')
				->img('/admin/images/money.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/purchase/discount/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/purchase/discount/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.bonus_link'))
				->icon('fa fa-star')
				->img('/admin/images/money.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/bonus/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/bonus/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.coupon_group_link'))
				->icon('fa fa-ticket')
				->img('/admin/images/money.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/purchase/discount/coupon/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/purchase/discount/coupon/index.php', NULL, NULL, $additionalParams)
				)
		)
		->add(
			Admin_Form_Entity::factory('Menu')
				->name(Core::_('Shop_Item.disountcard_link'))
				->icon('fa fa-credit-card-alt')
				->img('/admin/images/money.gif')
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/discountcard/index.php', NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/discountcard/index.php', NULL, NULL, $additionalParams)
				)
		)
)->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Shop_Item.affiliate_menu_title'))
		->icon('fa fa-group')
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref('/admin/shop/affiliate/plan/index.php', NULL, NULL, $additionalParams)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax('/admin/shop/affiliate/plan/index.php', NULL, NULL, $additionalParams)
		)
);

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oMenu);

$sGlobalSearch = trim(strval(Core_Array::getGet('globalSearch')));

$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html('
			<div class="row search-field margin-bottom-20">
				<div class="col-xs-12">
					<form action="' . $oAdmin_Form_Controller->getPath() . '" method="GET">
						<input type="text" name="globalSearch" class="form-control" placeholder="' . Core::_('Admin.placeholderGlobalSearch') . '" value="' . htmlspecialchars($sGlobalSearch) . '" />
						<i class="fa fa-search no-margin" onclick="$(this).siblings(\'input[type=submit]\').click()"></i>
						<i class="fa fa-times-circle no-margin" onclick="' . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), '', '', $additionalParams) . '"></i>
						<input type="submit" class="hidden" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('', '', $additionalParams) . '" />
					</form>
				</div>
			</div>
		')
);

$sGlobalSearch = Core_DataBase::instance()->escapeLike($sGlobalSearch);

// Хлебные крошки
$oBreadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Первая крошка на список магазинов
$oBreadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name(Core::_('Shop.menu'))
	->href($oAdmin_Form_Controller->getAdminLoadHref(
		'/admin/shop/index.php', NULL, NULL, ''
	))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
		'/admin/shop/index.php', NULL, NULL, ''
	))
);

// Крошки по директориям магазинов
if ($oShopDir->id)
{
	$oShopDirBreadcrumbs = $oShopDir;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
		->name($oShopDirBreadcrumbs->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref(
				'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
		))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax(
				'/admin/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"
		));
	}while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oBreadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oBreadcrumbs->add(
Admin_Form_Entity::factory('Breadcrumb')
	->name($oShop->name)
	->href($oAdmin_Form_Controller->getAdminLoadHref
	(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}"
	))
	->onclick($oAdmin_Form_Controller->getAdminLoadAjax
	(
		$oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}"
	))
);

// Крошки по группам товаров
if ($oShopGroup->id)
{
	$oShopGroupBreadcrumbs = $oShopGroup;

	$aBreadcrumbs = array();

	do
	{
		$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
			->name($oShopGroupBreadcrumbs->name)
			->href($oAdmin_Form_Controller->getAdminLoadHref
			(
				'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"
			))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax
			(
				'/admin/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"
			));
	}while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oBreadcrumbs->add($oBreadcrumb);
	}
}

$oAdmin_Form_Controller->addEntity($oBreadcrumbs);

// Действие "Редактировать"
$oEditAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('edit');

if ($oEditAction)
{
	$oEditController = Admin_Form_Action_Controller::factory(
		'Shop_Item_Controller_Edit', $oEditAction
	);
	$oEditController->addEntity($oBreadcrumbs);
	$oAdmin_Form_Controller->addAction($oEditController);
}

// Действие "Создать ярлык"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('shortcut');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'shortcut' && $oEditAction)
{
	$oShortcutController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Shortcut', $oAction
	);

	$oShortcutController
		->title(Core::_('Shop_Item.shortcut_creation_window_caption'))
		->selectCaption(Core::_('Shop_Item.add_item_shortcut_shop_groups_id'))
		->value($oShopGroup->id);

	$iCount = $oShop->Shop_Groups->getCount();

	if ($iCount < Core::$mainConfig['switchSelectToAutocomplete'])
	{
		$oShortcutController->selectOptions(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id));
	}
	else
	{
		$oShortcutController->autocomplete(TRUE);
	}

	$oAdmin_Form_Controller->addAction($oShortcutController);
}

// Действие "Загрузка элементов магазина"
$oAdminFormActionLoadShopItemList = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('loadShopItemList');

if ($oAdminFormActionLoadShopItemList && $oAdmin_Form_Controller->getAction() == 'loadShopItemList')
{
	$oShop_Controller_Load_Select_Options = Admin_Form_Action_Controller::factory(
		'Shop_Controller_Load_Select_Options',  $oAdminFormActionLoadShopItemList
	);

	$oShop_Controller_Load_Select_Options
		->model(
			//Core_Entity::factory('Shop_Item')->shop_id($oShop->id)
			$oShop->Shop_Items
		)
		->defaultValue(' … ')
		->addCondition(
			array('where' => array('shop_group_id', '=', $oShopGroup->id))
		)->addCondition(
			array('where' => array('shop_id', '=', $oShop->id))
		)->addCondition(
			array('where' => array('modification_id', '=', 0))
		);

	$oAdmin_Form_Controller->addAction($oShop_Controller_Load_Select_Options);
}

// Действие "Применить"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oApplyController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAction
	);

	$oAdmin_Form_Controller->addAction($oApplyController);
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

// Действие "Перенести"
$oAdminFormActionMove = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('move');

if ($oAdminFormActionMove && $oAdmin_Form_Controller->getAction() == 'move')
{
	$Admin_Form_Action_Controller_Type_Move = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Move', $oAdminFormActionMove
	);

	$Admin_Form_Action_Controller_Type_Move
		->title(Core::_('Informationsystem_Item.move_items_groups_title'))
		->selectCaption(Core::_('Informationsystem_Item.move_items_groups_information_groups_id'))
		->value($oShopGroup->id);

	$iCount = $oShop->Shop_Groups->getCount();

	if ($iCount < Core::$mainConfig['switchSelectToAutocomplete'])
	{
		$aExclude = array();

		$aChecked = $oAdmin_Form_Controller->getChecked();

		foreach ($aChecked as $datasetKey => $checkedItems)
		{
			// Exclude just dirs
			if ($datasetKey == 0)
			{
				foreach ($checkedItems as $key => $value)
				{
					$aExclude[] = $key;
				}
			}
		}

		$Admin_Form_Action_Controller_Type_Move
			// Список директорий генерируется другим контроллером
			->selectOptions(array(' … ') + Shop_Item_Controller_Edit::fillShopGroup($oShop->id, 0, $aExclude));
	}
	else
	{
		$Admin_Form_Action_Controller_Type_Move->autocomplete(TRUE);
	}

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Admin_Form_Action_Controller_Type_Move);
}

// Действие "Скидка"
$oAdminFormActionApplyDiscount = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('apply_discount');

if ($oAdminFormActionApplyDiscount && $oAdmin_Form_Controller->getAction() == 'apply_discount')
{
	$Shop_Item_Controller_Apply_Discount = Admin_Form_Action_Controller::factory(
		'Shop_Item_Controller_Apply_Discount', $oAdminFormActionApplyDiscount
	);

	$Shop_Item_Controller_Apply_Discount
		->title(Core::_('Shop_Item.apply_discount_items_title'))
		->Shop($oShop);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($Shop_Item_Controller_Apply_Discount);
}

// Действие "Удаление значения свойства"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deletePropertyValue');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deletePropertyValue')
{
	$oDeletePropertyValueController = Admin_Form_Action_Controller::factory(
		'Property_Controller_Delete_Value', $oAction
	);

	$oDeletePropertyValueController
		->linkedObject(array(
				Core_Entity::factory('Shop_Group_Property_List', $oShop->id),
				Core_Entity::factory('Shop_Item_Property_List', $oShop->id)
			));

	$oAdmin_Form_Controller->addAction($oDeletePropertyValueController);
}

// Действие "Удаление файла большого изображения"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteLargeImage');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteLargeImage')
{
	$oDeleteLargeImageController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteLargeImageController
		->methodName('deleteLargeImage')
		->divId(array('preview_large_image', 'delete_large_image'));

	// Добавляем контроллер удаления изображения к контроллеру формы
	$oAdmin_Form_Controller->addAction($oDeleteLargeImageController);
}

// Действие "Удаление файла малого изображения"
$oAction = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteSmallImage');

if ($oAction && $oAdmin_Form_Controller->getAction() == 'deleteSmallImage')
{
	$oDeleteSmallImageController = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Delete_File', $oAction
	);

	$oDeleteSmallImageController
		->methodName('deleteSmallImage')
		->divId(array('preview_small_image', 'delete_small_image'));

	$oAdmin_Form_Controller->addAction($oDeleteSmallImageController);
}

// Удаление сопутствующих товаров с вкладки
$oAdminFormActionDeleteAssociated = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteAssociated');

if ($oAdminFormActionDeleteAssociated && $oAdmin_Form_Controller->getAction() == 'deleteAssociated')
{
	$Shop_Item_Associated_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Item_Associated_Controller_Delete', $oAdminFormActionDeleteAssociated
	);

	$oAdmin_Form_Controller->addAction($Shop_Item_Associated_Controller_Delete);
}

// Удаление товаров из комплекта
$oAdminFormActionDeleteSet = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id)
	->Admin_Form_Actions
	->getByName('deleteSetItem');

if ($oAdminFormActionDeleteSet && $oAdmin_Form_Controller->getAction() == 'deleteSetItem')
{
	$Shop_Item_Set_Controller_Delete = Admin_Form_Action_Controller::factory(
		'Shop_Item_Set_Controller_Delete', $oAdminFormActionDeleteSet
	);

	$oAdmin_Form_Controller->addAction($Shop_Item_Set_Controller_Delete);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Shop_Group'));
$oAdmin_Form_Dataset
	->changeField('name', 'class', 'semi-bold')
	->addCondition(array(
			'select' => array('*', array(Core_QueryBuilder::expression("''"), 'adminPrice'), array(Core_QueryBuilder::expression("''"), 'adminRest')
		))
	)
	->addCondition(array('where' => array('shop_id', '=', $oShop->id)))
	->changeField('related', 'type', 1)
	->changeField('modifications', 'type', 1)
	->changeField('discounts', 'type', 1)
	->changeField('type', 'type', 1)
	->changeField('reviews', 'type', 1)
	->changeField('adminPrice', 'type', 1);

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()))
		->addCondition(array('where' => array('shop_groups.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_groups.path', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_groups.seo_title', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_groups.seo_description', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_groups.seo_keywords', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('shop_groups.parent_id', '=', $oShopGroup->id)));
}

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(Core_Entity::factory('Shop_Item'));

$oAdmin_Form_Dataset
	->addCondition(
		array('select' => array('shop_items.*', array('shop_items.price', 'adminPrice'), array('SUM(shop_warehouse_items.count)', 'adminRest'), array(Core_QueryBuilder::expression('IF(shortcut_id, 0, 1)'), 'related'), array(Core_QueryBuilder::expression('IF(shortcut_id, 0, 1)'), 'modifications'), array(Core_QueryBuilder::expression('IF(shortcut_id, 0, 1)'), 'discounts'), array(Core_QueryBuilder::expression('IF(shortcut_id, 0, 1)'), 'reviews')))
	)
	->addCondition(
		array('leftJoin' => array('shop_warehouse_items', 'shop_items.id', '=', 'shop_warehouse_items.shop_item_id'))
	)
	->addCondition(array('where' => array('shop_items.shop_id', '=', $oShop->id)))
	->addCondition(array('groupBy' => array('shop_items.id')));

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(
			array('leftJoin' => array('shop_item_barcodes', 'shop_items.id', '=', 'shop_item_barcodes.shop_item_id'))
		)
		->addCondition(array('open' => array()))
		->addCondition(array('where' => array('shop_items.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_items.path', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_items.marking', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_item_barcodes.value', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_items.seo_title', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_items.seo_description', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('setOr' => array()))
		->addCondition(array('where' => array('shop_items.seo_keywords', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	$oAdmin_Form_Dataset
		->addCondition(array('where' => array('shop_items.shop_group_id', '=', $oShopGroup->id)))
		->addCondition(array('where' => array('shop_items.modification_id', '=', 0)));
}

$oShop_Producers = $oShop->Shop_Producers;
$oShop_Producers->queryBuilder()
	->distinct()
	->select('shop_producers.*')
	->join('shop_items', 'shop_producers.id', '=', 'shop_items.shop_producer_id')
	// ->where('shop_producers.active', '=', 1)
	->where('shop_items.shop_group_id', '=', $oShopGroup->id)
	->where('shop_items.modification_id', '=', 0)
	->where('shop_items.shortcut_id', '=', 0)
	//->groupBy('shop_producers.id')
	->clearOrderBy()
	->orderBy('shop_producers.sorting', 'ASC')
	->orderBy('shop_producers.name', 'ASC');

$aShop_Producers = $oShop_Producers->findAll(FALSE);

if (count($aShop_Producers))
{
	$options = '';

	foreach ($aShop_Producers as $oShop_Producer)
	{
		$options .= $oShop_Producer->id . "=" . $oShop_Producer->name . "\n";
	}

	$oAdmin_Form_Dataset
		->changeField('shop_producer_id', 'list', $options);
}

// Change field type
if (Core_Entity::factory('Shop', $oShop->id)->Shop_Warehouses->getCount() == 1)
{
	$oAdmin_Form_Dataset->changeField('adminRest', 'type', 2);
}

// Change field type
$oAdmin_Form_Dataset
	->changeField('img', 'type', 10)
	->changeField('active', 'list', "1=" . Core::_('Admin_Form.yes') . "\n" . "0=" . Core::_('Admin_Form.no'));

$oAdmin_Form_Controller->addDataset($oAdmin_Form_Dataset);

$oAdmin_Form_Controller->addExternalReplace('{shop_group_id}', $oShopGroup->id);

// Показ формы
$oAdmin_Form_Controller->execute();