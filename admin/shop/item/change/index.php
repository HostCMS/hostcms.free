<?php
/**
* Online shop.
*
* @package HostCMS
* @version 7.x
* @author Hostmake LLC
* @copyright © 2005-2026, https://www.hostcms.ru
*/
require_once('../../../../bootstrap.php');

Core_Auth::authorization($sModule = 'shop');

// Получаем параметры
$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
$oShopDir = $oShop->Shop_Dir;
$oShopGroup = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

$oAdmin_Form_Controller = Admin_Form_Controller::create();
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Контроллер формы
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path('/{admin}/shop/item/change/index.php');

ob_start();

$oAdmin_View = Admin_View::create();
$oAdmin_View
	->module(Core_Module_Abstract::factory($sModule))
	->pageTitle(Core::_('Shop_Item.change_prices_for_shop_group'));

// Первая крошка на список магазинов
$oAdmin_Form_Entity_Breadcrumbs->add(
		Admin_Form_Entity::factory('Breadcrumb')
			->name(Core::_('Shop.menu'))
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/index.php'))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/index.php'))
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
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/index.php', NULL, NULL, "shop_dir_id={$oShopDirBreadcrumbs->id}"));
	} while ($oShopDirBreadcrumbs = $oShopDirBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на список товаров и групп товаров магазина
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name($oShop->name)
		->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}"))
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
			->href($oAdmin_Form_Controller->getAdminLoadHref('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"))
			->onclick($oAdmin_Form_Controller->getAdminLoadAjax('/{admin}/shop/item/index.php', NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroupBreadcrumbs->id}"));
	} while ($oShopGroupBreadcrumbs = $oShopGroupBreadcrumbs->getParent());

	$aBreadcrumbs = array_reverse($aBreadcrumbs);

	foreach ($aBreadcrumbs as $oBreadcrumb)
	{
		$oAdmin_Form_Entity_Breadcrumbs->add($oBreadcrumb);
	}
}

// Крошка на текущую форму
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Shop_Item.change_prices_for_shop_group'))
		->href($oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
		->onclick($oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, "shop_id={$oShop->id}&shop_group_id={$oShopGroup->id}"))
);

$oAdmin_Form_Entity_Form = Admin_Form_Entity::factory('Form')
		->controller($oAdmin_Form_Controller)
		->action($oAdmin_Form_Controller->getPath());

$oAdmin_View->addChild($oAdmin_Form_Entity_Breadcrumbs);

$increase_price_rate = Core_Array::getPost('increase_price_rate', '0.00', 'float');
$increase_price_rate = str_replace(',', '.', $increase_price_rate);
$increase_price_rate = number_format(floatval($increase_price_rate), 2);

$multiply_price_rate = Core_Array::getPost('multiply_price_rate', '1.00', 'float');
$multiply_price_rate = str_replace(',', '.', $multiply_price_rate);
$multiply_price_rate = number_format(floatval($multiply_price_rate), 2);

$bDeleteDiscount = Core_Array::getPost('flag_delete_discount', 0, 'int');
$iDiscountID = Core_Array::getPost('shop_discount_id', 0, 'int');

$bDeleteBonus = Core_Array::getPost('flag_delete_bonus', 0, 'int');
$iBonusID = Core_Array::getPost('shop_bonus_id', 0, 'int');

$iProducerID = Core_Array::getPost('shop_producers_list_id', 0, 'int');

$iParentGroup = Core_Array::getPost('shop_groups_parent_id', 0, 'int');

$bSpecialPrices = Core_Array::getPost('flag_include_spec_prices', 0, 'int');
$bIncludeModifications = Core_Array::getPost('flag_include_modifications', 0, 'int');
$bIncludeShortcutParentItem = Core_Array::getPost('flag_include_shortcut_parent_item', 0, 'int');

$oMainTab = Admin_Form_Entity::factory('Tab')->name('main');
$oMainTab->add(Admin_Form_Entity::factory('Div')->class('row')
		->add(Admin_Form_Entity::factory('Radiogroup')
			->radio(array(0 => Core::_('Shop_Item.add_price_to_digit')))
			->caption(Core::_('Shop_Item.select_price_form'))
			->ico(array('fa-plus'))
			->colors(array('btn-sky'))
			->name('type_of_change')
			->divAttr(array('class' => 'form-group col-xs-12 rounded-radio-group'))
			->add(Admin_Form_Entity::factory('Input')
				->name('increase_price_rate')
				->caption('&nbsp;')
				->value($increase_price_rate)
				->divAttr(array('class' => 'form-group d-inline-block margin-left-10'))
			)
			->add(Admin_Form_Entity::factory('Span')
				->value($oShop->Shop_Currency->sign)
				->divAttr(array('class' => 'form-group d-inline-block'))
			)
		)
	)
	->add(Admin_Form_Entity::factory('Div')->class('row')
		->add(Admin_Form_Entity::factory('Radiogroup')
			->radio(array(1 => Core::_('Shop_Item.multiply_price_to_digit')))
			->name('type_of_change')
			->ico(array(1 => 'fa-asterisk'))
			->divAttr(array('class' => 'form-group col-xs-12 rounded-radio-group'))
			->add(Admin_Form_Entity::factory('Input')
				->name("multiply_price_rate")
				->divAttr(array('class' => 'form-group d-inline-block margin-left-10'))
				->value($multiply_price_rate)
			)
		)
	);

$oMainTab->add($oMainBlock = Admin_Form_Entity::factory('Div')->class('well with-header margin-bottom-10'));
$oMainBlock
	->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
		->class('header bordered-palegreen')
		->value(Core::_('Shop_Item.change_price_main_header'))
	)
	->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

	$oMainRow1
		->add(Admin_Form_Entity::factory('Checkbox')
			->name('flag_include_modifications')
			->caption(Core::_('Shop_Item.flag_include_modifications'))
			->value(1)
			->checked($bIncludeModifications)
		);

	$oMainRow2
		->add(Admin_Form_Entity::factory('Checkbox')
			->name('flag_include_shortcut_parent_item')
			->caption(Core::_('Shop_Item.flag_include_shortcut_parent_item'))
			->value(1)
			->checked($bIncludeShortcutParentItem)
		);

	$oMainRow3
		->add(Admin_Form_Entity::factory('Checkbox')
			->name('flag_include_spec_prices')
			->caption(Core::_('Shop_Item.flag_include_spec_prices'))
			->value(1)
			->checked($bSpecialPrices)
		);

	$oMainRow4
		->add(Admin_Form_Entity::factory('Select')
			->name('shop_groups_parent_id')
			->caption(Core::_('Shop_Item.select_parent_group'))
			->options(array(Core::_('Shop_Item.load_parent_group')) + Shop_Item_Controller_Edit::fillShopGroup($oShop->id))
			->divAttr(array('class' => 'form-group col-xs-12'))
			->filter(TRUE)
			->value($iParentGroup)
		);

	$oMainRow5
		->add(Admin_Form_Entity::factory('Select')
			->name('shop_producers_list_id')
			->caption(Core::_('Shop_Item.shop_producer_id'))
			->options(Shop_Item_Controller_Edit::fillProducersList($oShop->id))
			->divAttr(array('class' => 'form-group col-xs-12'))
			->filter(TRUE)
			->value($iProducerID)
		);

if (Core::moduleIsActive('siteuser'))
{
	$aShop_Prices = $oShop->Shop_Prices->findAll(FALSE);

	if (count($aShop_Prices))
	{
		$oMainTab->add($oPriceBlock = Admin_Form_Entity::factory('Div')->class('well with-header margin-bottom-10'));

		$oPriceBlock
			->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
				->class('header bordered-azure')
				->value(Core::_('Shop_Item.apply_to'))
			)
			->add($oShopPriceRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		foreach ($aShop_Prices as $oShop_Price)
		{
			$oShopPriceRow1->add(Admin_Form_Entity::factory('Checkbox')
				->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
				->name('shop_price_' . $oShop_Price->id)
				->caption(htmlspecialchars($oShop_Price->name))
				->value(1)
				->checked(Core_Array::getPost('shop_price_' . $oShop_Price->id, 0, 'int'))
			);
		}
	}
}

// Получение списка скидок
$aDiscounts = array('...');

$aShop_Discounts = $oShop->Shop_Discounts->findAll(FALSE);
foreach ($aShop_Discounts as $oShop_Discount)
{
	$aDiscounts[$oShop_Discount->id] = $oShop_Discount->getOptions();
}

$oMainTab->add($oDiscountBlock = Admin_Form_Entity::factory('Div')->class('well with-header margin-bottom-10'));

$oDiscountBlock
	->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
		->class('header bordered-maroon')
		->value(Core::_('Shop_Item.change_price_discount_header'))
	)
	->add($oDiscountRow1 = Admin_Form_Entity::factory('Div')->class('row'))
	->add($oDiscountRow2 = Admin_Form_Entity::factory('Div')->class('row'));

$oDiscountRow1->add(Admin_Form_Entity::factory('Select')
	->options($aDiscounts)
	->caption(Core::_('Shop_Item.select_discount_type'))
	->name('shop_discount_id')
	->divAttr(array('class' => 'form-group col-xs-12'))
	->filter(TRUE)
	->value($iDiscountID)
);
$oDiscountRow2->add(Admin_Form_Entity::factory('Checkbox')
	->name('flag_delete_discount')
	->caption(Core::_('Shop_Item.flag_delete_discount'))
	->class('form-control colored-danger times')
	->value(1)
	->checked($bDeleteDiscount)
);

// Получение бонусов
if (Core::moduleIsActive('siteuser'))
{
	$aBonuses = array(" … ");
	$aShop_Bonuses = $oShop->Shop_Bonuses->findAll();
	foreach ($aShop_Bonuses as $oShop_Bonus)
	{
		$aBonuses[$oShop_Bonus->id] = $oShop_Bonus->getOptions();
	}

	$oMainTab->add($oBonusBlock = Admin_Form_Entity::factory('Div')->class('well with-header margin-bottom-10'));

	$oBonusBlock
		->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
			->class('header bordered-orange')
			->value(Core::_('Shop_Item.change_price_bonus_header'))
		)
		->add($oBonusRow1 = Admin_Form_Entity::factory('Div')->class('row'))
		->add($oBonusRow2 = Admin_Form_Entity::factory('Div')->class('row'));

	$oBonusRow1->add(Admin_Form_Entity::factory('Select')
		->options($aBonuses)
		->caption(Core::_('Shop_Item.select_bonus_type'))
		->name('shop_bonus_id')
		->divAttr(array('class' => 'form-group col-xs-12'))
		->filter(TRUE)
		->value($iBonusID)
	);

	$oBonusRow2->add(Admin_Form_Entity::factory('Checkbox')
		->name('flag_delete_bonus')
		->caption(Core::_('Shop_Item.flag_delete_bonus'))
		->class('form-control colored-danger times')
		->value(1)
		->checked($bDeleteBonus)
	);
}

$oAdmin_Form_Entity_Form
	->add($oMainTab)
	->add(
		Admin_Form_Entity::factory('Button')
		->name('do_accept_new_price')
		->type('submit')
		->class('applyButton btn btn-blue')
		->onclick($oAdmin_Form_Controller->getAdminSendForm('do_accept_new_price'))
	);

$oUser = Core_Auth::getCurrentUser();

$limit = 500;

if ($oAdmin_Form_Controller->getAction() == 'do_accept_new_price')
{
	if (!$oUser->read_only)
	{
		$aShop_Prices = Core::moduleIsActive('siteuser')
			? $oShop->Shop_Prices->findAll(FALSE)
			: array();

		// Если только увеличение цены в N раз и не указаны скидки или бонусы, не включая модификации и для всего магазина
		if (Core_Array::getPost('type_of_change') == 1 && !$iDiscountID && !$iBonusID && !($bIncludeModifications && $iParentGroup) && !$bIncludeShortcutParentItem)
		{
			// Розничная
			$offset = 0;

			do {
				$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting');
				$oShop_Price_Setting->shop_id = $oShop->id;
				$oShop_Price_Setting->number = '';
				$oShop_Price_Setting->posted = 0;
				$oShop_Price_Setting->description = Core::_('Shop_Item.change_prices_for_shop_group');
				$oShop_Price_Setting->datetime = Core_Date::timestamp2sql(time());
				$oShop_Price_Setting->save();

				$oShop_Price_Setting->number = $oShop_Price_Setting->id;
				$oShop_Price_Setting->save();

				$oCore_QueryBuilder_Select = Core_QueryBuilder::select(
						intval($oShop_Price_Setting->id),
						0,
						'shop_items.id',
						'shop_items.price',
						array(Core_QueryBuilder::expression('`shop_items`.`price` * ' . Core_DataBase::instance()->quote($multiply_price_rate)), 'new_price')
					)
					->from('shop_items')
					->where('shop_items.shop_id', '=', $oShop->id)
					->where('shop_items.deleted', '=', 0)
					->where('shop_items.shortcut_id', '=', 0)
					->clearOrderBy()
					->orderBy('shop_items.id', 'ASC')
					->limit($limit)
					->offset($offset);

				// Учитывать модификации не установлено
				!$bIncludeModifications
					&& $oCore_QueryBuilder_Select->where('shop_items.modification_id', '=', 0);

				!$oUser->superuser && $oUser->only_access_my_own
					&& $oCore_QueryBuilder_Select->where('shop_items.user_id', '=', $oUser->id);

				$iProducerID
					&& $oCore_QueryBuilder_Select->where('shop_items.shop_producer_id', '=', $iProducerID);

				$iParentGroup
					&& $oCore_QueryBuilder_Select->where('shop_group_id', 'IN', array_merge(array($iParentGroup), Core_Entity::factory('Shop_Group', $iParentGroup)->Shop_Groups->getGroupChildrenId()));

				$oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('shop_price_setting_items')
					->columns('shop_price_setting_id', 'shop_price_id', 'shop_item_id', 'old_price', 'new_price')
					->select($oCore_QueryBuilder_Select)
					->execute();

				$rows = Core_DataBase::instance()->getAffectedRows();

				// Цена для группы пользователей $oShop_Price
				foreach ($aShop_Prices as $oShop_Price)
				{
					if (!is_null(Core_Array::getPost('shop_price_' . $oShop_Price->id)))
					{
						$oCore_QueryBuilder_Select = Core_QueryBuilder::select(
								intval($oShop_Price_Setting->id),
								intval($oShop_Price->id),
								'shop_items.id',
								'shop_item_prices.value',
								array(Core_QueryBuilder::expression('`shop_item_prices`.`value` * ' . Core_DataBase::instance()->quote($multiply_price_rate)), 'new_price')
							)
							->from('shop_items')
							->join('shop_item_prices', 'shop_item_prices.shop_item_id', '=', 'shop_items.id')
							->where('shop_item_prices.shop_price_id', '=', $oShop_Price->id)
							->where('shop_items.shop_id', '=', $oShop->id)
							->where('shop_items.deleted', '=', 0)
							->where('shop_items.shortcut_id', '=', 0)
							->clearOrderBy()
							->orderBy('shop_items.id', 'ASC')
							->limit($limit)
							->offset($offset);

						// Учитывать модификации не установлено
						!$bIncludeModifications
							&& $oCore_QueryBuilder_Select->where('shop_items.modification_id', '=', 0);

						!$oUser->superuser && $oUser->only_access_my_own
							&& $oCore_QueryBuilder_Select->where('shop_items.user_id', '=', $oUser->id);

						$iProducerID
							&& $oCore_QueryBuilder_Select->where('shop_items.shop_producer_id', '=', $iProducerID);

						$iParentGroup
							&& $oCore_QueryBuilder_Select->where('shop_group_id', 'IN', array_merge(array($iParentGroup), Core_Entity::factory('Shop_Group', $iParentGroup)->Shop_Groups->getGroupChildrenId()));

						$oCore_QueryBuilder_Insert = Core_QueryBuilder::insert('shop_price_setting_items')
							->columns('shop_price_setting_id', 'shop_price_id', 'shop_item_id', 'old_price', 'new_price')
							->select($oCore_QueryBuilder_Select)
							->execute();
					}
				}

				// Проводим документ
				$oShop_Price_Setting->post();

				$offset += $limit;
			}
			while ($rows == $limit);

			// Special Prices
			if ($bSpecialPrices)
			{
				$oCore_QueryBuilder_Update = Core_QueryBuilder::update('shop_specialprices')
					->set('shop_specialprices.price', Core_QueryBuilder::expression('`shop_specialprices`.`price` * ' . Core_DataBase::instance()->quote($multiply_price_rate)))
					->join('shop_items', 'shop_specialprices.shop_item_id', '=', 'shop_items.id')
					->where('shop_items.shop_id', '=', $oShop->id)
					->where('shop_items.shortcut_id', '=', 0)
					->where('shop_items.deleted', '=', 0);

				// Учитывать модификации не установлено
				!$bIncludeModifications
					&& $oCore_QueryBuilder_Update->where('shop_items.modification_id', '=', 0);

				!$oUser->superuser && $oUser->only_access_my_own
					&& $oCore_QueryBuilder_Update->where('shop_items.user_id', '=', $oUser->id);

				$iProducerID
					&& $oCore_QueryBuilder_Update->where('shop_items.shop_producer_id', '=', $iProducerID);

				$iParentGroup
					&& $oCore_QueryBuilder_Update->where('shop_group_id', 'IN', array_merge(array($iParentGroup), Core_Entity::factory('Shop_Group', $iParentGroup)->Shop_Groups->getGroupChildrenId()));

				$oCore_QueryBuilder_Update->execute();
			}
		}
		else
		{
			$oShop_Items = Core_Entity::factory('Shop', $oShop->id)->Shop_Items;
			$oShop_Items
				->queryBuilder()
				//->where('shop_items.shortcut_id', '=', 0)
				->where('shop_items.modification_id', '=', 0);

			!$bIncludeShortcutParentItem
				&& $oShop_Items->queryBuilder()->where('shop_items.shortcut_id', '=', 0);

			$iParentGroup
				&& $oShop_Items->queryBuilder()->where('shop_group_id', 'IN', array_merge(array($iParentGroup), Core_Entity::factory('Shop_Group', $iParentGroup)->Shop_Groups->getGroupChildrenId()));

			$iProducerID
				&& $oShop_Items->queryBuilder()->where('shop_producer_id', '=', $iProducerID);

			Core_Event::notify('Shop_Item_Change.onBeforeSelectShopItems', NULL, array($oShop_Items));

			$oShop_Price_Setting = Core_Entity::factory('Shop_Price_Setting');
			$oShop_Price_Setting->shop_id = $oShop->id;
			$oShop_Price_Setting->number = '';
			$oShop_Price_Setting->posted = 0;
			$oShop_Price_Setting->description = Core::_('Shop_Item.change_prices_for_shop_group');
			$oShop_Price_Setting->datetime = Core_Date::timestamp2sql(time());
			$oShop_Price_Setting->save();

			$oShop_Price_Setting->number = $oShop_Price_Setting->id;
			$oShop_Price_Setting->save();

			// Идентификаторы цен, которые также пересчитывать
			$aShop_Price_IDs = array();
			foreach ($aShop_Prices as $oShop_Price)
			{
				if (!is_null(Core_Array::getPost('shop_price_' . $oShop_Price->id)))
				{
					$aShop_Price_IDs[] = $oShop_Price->id;
				}
			}

			$aAppliedIDs = array();

			// Step-by-step
			$offset = 0;
			do {
				$oShop_Items->queryBuilder()
					->offset($offset)
					->limit($limit);

				$aShop_Items = $oShop_Items->findAll(FALSE);
				foreach ($aShop_Items as $oShop_Item)
				{
					$oShop_Item->shortcut_id && $oShop_Item = $oShop_Item->Shop_Item;

					if (!in_array($oShop_Item->id, $aAppliedIDs))
					{
						$aAppliedIDs[] = $oShop_Item->id;
						applySettings($oShop_Price_Setting, $oUser, $oShop_Item, $aShop_Price_IDs, $increase_price_rate, $multiply_price_rate, $iDiscountID, $iBonusID, $bSpecialPrices);

						if ($bIncludeModifications)
						{
							$aShopItemModifications = $oShop_Item->Modifications->findAll(FALSE);
							foreach ($aShopItemModifications as $oShopItemModification)
							{
								applySettings($oShop_Price_Setting, $oUser, $oShopItemModification, $aShop_Price_IDs, $increase_price_rate, $multiply_price_rate, $iDiscountID, $iBonusID, $bSpecialPrices);
							}
						}
					}
				}

				// Inc offset
				$offset += $limit;
			}
			while (count($aShop_Items));

			$oShop_Price_Setting->post();
		}

		Core_Message::show(Core::_('Shop_Item.accepted_prices'));
	}
	else
	{
		Core_Message::show(Core::_('User.demo_mode'), 'error');
	}
}

$oAdmin_Form_Entity_Form->execute();
$content = ob_get_clean();

ob_start();
$oAdmin_View
	->content($content)
	->show();

Core_Skin::instance()
	->answer()
	->ajax(Core_Array::getRequest('_', FALSE))
	->content(ob_get_clean())
	->title(Core::_('Shop_Item.change_prices_for_shop_group'))
	->execute();

function applySettings(Shop_Price_Setting_Model $oShop_Price_Setting, User_Model $oUser, Shop_Item_Model $oShop_Item, array $aShop_Price_IDs, $sTextAddition, $sTextMultiplication, $iDiscountID, $iBonusID, $bSpecialPrices)
{
	// Проверка через user_id на право выполнения действия над объектом
	if ($oUser->checkObjectAccess($oShop_Item))
	{
		$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
		$oShop_Price_Setting_Item->shop_price_setting_id = $oShop_Price_Setting->id;
		$oShop_Price_Setting_Item->shop_price_id = 0; // Розничная
		$oShop_Price_Setting_Item->shop_item_id = $oShop_Item->id;
		$oShop_Price_Setting_Item->old_price = $oShop_Item->price;

		if (Core_Array::getPost('type_of_change', 0) == 0)
		{
			if ($oShop_Item->shop_currency_id != 0 && $oShop_Item->Shop->shop_currency_id != 0)
			{
				$iCoefficient = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(
					$oShop_Item->Shop->Shop_Currency, $oShop_Item->Shop_Currency
				);
			}
			else
			{
				$iCoefficient = 0;
			}
		}

		$oShop_Price_Setting_Item->new_price = Core_Array::getPost('type_of_change', 0) == 0
			? $oShop_Item->price + $sTextAddition * $iCoefficient
			: $oShop_Item->price * $sTextMultiplication;

		$oShop_Price_Setting_Item->save();

		if ($bSpecialPrices)
		{
			$aShop_Specialprices = $oShop_Item->Shop_Specialprices->findAll(FALSE);
			foreach ($aShop_Specialprices as $oShop_Specialprice)
			{
				if ($oShop_Specialprice->price)
				{
					$oShop_Specialprice->price = Core_Array::getPost('type_of_change', 0) == 0
						? $oShop_Specialprice->price + $sTextAddition * $iCoefficient
						: $oShop_Specialprice->price * $sTextMultiplication;

					$oShop_Specialprice->save();
				}
			}
		}

		// Цены для групп пользователей
		if (count($aShop_Price_IDs))
		{
			$aShop_Item_Prices = $oShop_Item->Shop_Item_Prices->getAllByshop_price_id($aShop_Price_IDs, FALSE, 'IN');

			foreach ($aShop_Item_Prices as $oShop_Item_Price)
			{
				$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
				$oShop_Price_Setting_Item->shop_price_setting_id = $oShop_Price_Setting->id;
				$oShop_Price_Setting_Item->shop_price_id = $oShop_Item_Price->shop_price_id;
				$oShop_Price_Setting_Item->shop_item_id = $oShop_Item->id;
				$oShop_Price_Setting_Item->old_price = $oShop_Item_Price->value;

				$oShop_Price_Setting_Item->new_price = Core_Array::getPost('type_of_change', 0) == 0
					? $oShop_Item_Price->value + $sTextAddition * $iCoefficient
					: $oShop_Item_Price->value * $sTextMultiplication;

				$oShop_Price_Setting_Item->save();
			}
		}

		if ($iDiscountID)
		{
			$oShop_Discount = Core_Entity::factory('Shop_Discount', $iDiscountID);

			if (!is_null(Core_Array::getPost('flag_delete_discount')))
			{
				$oShop_Item->remove($oShop_Discount);
			}
			else
			{
				$bIsNull = is_null($oShop_Item->Shop_Item_Discounts->getByDiscountId($iDiscountID));

				if ($bIsNull)
				{
					// Устанавливаем скидку товару
					$oShop_Item->add($oShop_Discount);
				}
			}
		}

		if ($iBonusID)
		{
			$oShop_Bonus = Core_Entity::factory('Shop_Bonus', $iBonusID);

			if (!is_null(Core_Array::getPost('flag_delete_bonus')))
			{
				$oShop_Item->remove($oShop_Bonus);
			}
			else
			{
				$bIsNull = is_null($oShop_Item->Shop_Item_Bonuses->getByShop_bonus_id($iBonusID));

				if ($bIsNull)
				{
					// Устанавливаем бонус товару
					$oShop_Item->add($oShop_Bonus);
				}
			}
		}
	}
}