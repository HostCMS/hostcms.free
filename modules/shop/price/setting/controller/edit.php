<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Price_Setting Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Price_Setting_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		if (!$object->id)
		{
			$object->shop_id = Core_Array::getGet('shop_id');
		}

		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
		$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oAdmin_Form_Controller = $this->_Admin_Form_Controller;

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopPriceBlock = Admin_Form_Entity::factory('Div')->class('well with-header'))
			->add($oShopItemBlock = Admin_Form_Entity::factory('Div')->class('well with-header'))
			;

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))->class('input-lg'), $oMainRow1);

		// Печать
		$printlayoutsButton = '
			<div class="btn-group">
				<a class="btn btn-labeled btn-success" href="javascript:void(0);"><i class="btn-label fa fa-print"></i>' . Core::_('Printlayout.print') . '</a>
				<a class="btn btn-palegreen dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-palegreen">
		';

		$moduleName = $oAdmin_Form_Controller->module->getModuleName();

		$oModule = Core_Entity::factory('Module')->getByPath($moduleName);

		if (!is_null($oModule))
		{
			$printlayoutsButton .= Printlayout_Controller::getPrintButtonHtml($this->_Admin_Form_Controller, $oModule->id, 10, 'hostcms[checked][0][' . $this->_object->id . ']=1&shop_price_id=0&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id);
		}

		$printlayoutsButton .= '
				</ul>
			</div>
		';

		$oMainRow1
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12 col-sm-2 margin-top-21 text-align-center print-price')
				->add(
					Admin_Form_Entity::factory('Code')->html($printlayoutsButton)
				)
		);

		$oShop_Warehouse_Select = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Price_Setting.print_price_id'))
			->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-3')
			)
			->options(self::fillPricesList($oShop))
			->class('form-control select-warehouse')
			->name('print_price_id')
			->onchange('$.changePrintButton(this)')
			->value(0);

		$oMainRow1->add($oShop_Warehouse_Select);

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

		$aColors = array(
			'danger',
			'azure',
			'yellow',
			'purple',
			'sky',
			'darkorange',
		);

		// Удаляем поле с идентификатором ответственного сотрудника
		$oAdditionalTab->delete($this->getField('user_id'));

		$aSelectResponsibleUsers = array();

		$oSite = Core_Entity::factory('Site', CURRENT_SITE);

		$aCompanies = $oSite->Companies->findAll();
		foreach ($aCompanies as $oCompany)
		{
			$oOptgroupCompany = new stdClass();
			$oOptgroupCompany->attributes = array('label' => htmlspecialchars($oCompany->name), 'class' => 'company');
			$oOptgroupCompany->children = $oCompany->fillDepartmentsAndUsers($oCompany->id);

			$aSelectResponsibleUsers[] = $oOptgroupCompany;
		}

		$oSelectResponsibleUsers = Admin_Form_Entity::factory('Select')
			->id('user_id')
			->options($aSelectResponsibleUsers)
			->name('user_id')
			->value($this->_object->user_id)
			->caption(Core::_('Shop_Price_Setting.user_id'))
			->divAttr(array('class' => ''));

		$oScriptResponsibleUsers = Admin_Form_Entity::factory('Script')
			->value('$("#user_id").selectUser({
						placeholder: "",
						language: "' . Core_i18n::instance()->getLng() . '"
					});'
			);

		$oMainRow3
			->add(
				Admin_Form_Entity::factory('Div')
					->add(
						Admin_Form_Entity::factory('Div')
							->class('hidden-sm hidden-md hidden-lg padding-top-40')
					)
					->add($oSelectResponsibleUsers)
					->class('form-group col-xs-12 col-sm-4')
			)
			->add($oScriptResponsibleUsers);

		$oMainTab->move($this->getField('posted')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2 margin-top-21')), $oMainRow3);

		$aExistPriceIDs = $this->_object->id ? array() : array(0);
		$aPrices = array();

		$aShop_Price_Setting_Items = $this->_object->Shop_Price_Setting_Items->findAll(FALSE);
		foreach ($aShop_Price_Setting_Items as $oShop_Price_Setting_Item)
		{
			$aExistPriceIDs[] = $oShop_Price_Setting_Item->shop_price_id;

			$aPrices[$oShop_Price_Setting_Item->shop_item_id][$oShop_Price_Setting_Item->shop_price_id] = $oShop_Price_Setting_Item;
		}

		$oShopPriceBlock
			->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
				->class('header bordered-azure')
				->value(Core::_('Shop_Price_Setting.shop_price_header'))
			)
			->add($oShopPriceRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		// Розничная цена
		$oShopPriceRow1->add(
				$oShop_Price_Checkbox = Admin_Form_Entity::factory('Checkbox')
					->id(0)
					->caption(Core::_('Shop_Price_Setting.basic'))
					->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
					->class('form-control')
					->name("shop_price_0")
					->onclick("$.toggleShopPrice(0)")
		);

		in_array(0, $aExistPriceIDs) && $oShop_Price_Checkbox->checked('checked');

		$aAllPricesIDs = array(0);

		$aShop_Prices = $oShop->Shop_Prices->findAll(FALSE);
		foreach ($aShop_Prices as $oShop_Price)
		{
			$oShopPriceRow1->add(
				$oShop_Price_Checkbox = Admin_Form_Entity::factory('Checkbox')
					->id($oShop_Price->id)
					->caption($oShop_Price->name)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-3'))
					->class('form-control')
					->onclick("$.toggleShopPrice({$oShop_Price->id})")
					->name("shop_price_{$oShop_Price->id}")
			);

			in_array($oShop_Price->id, $aExistPriceIDs) && $oShop_Price_Checkbox->checked('checked');

			$aAllPricesIDs[] = $oShop_Price->id;
		}

		$oShopItemBlock
			->add($oHeaderDiv = Admin_Form_Entity::factory('Div')
				->class('header bordered-palegreen')
				->value(Core::_('Shop_Price_Setting.shop_item_header'))
			)
			->add($oShopItemRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oShopItemRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$hiddenPrice0 = !in_array(0, $aExistPriceIDs)
			? 'hidden'
			: '';

		$itemTable = '
		<div class="table-scrollable">
			<table class="table table-striped table-hover shop-item-table deals-aggregate-user-info">
				<thead>
					<tr>
						<th rowspan="2" scope="col">' . Core::_('Shop_Price_Setting.position') . '</th>
						<th rowspan="2" scope="col">' . Core::_('Shop_Price_Setting.name') . '</th>
						<th rowspan="2" scope="col">' . Core::_('Shop_Price_Setting.measure') . '</th>
						<th rowspan="2" scope="col">' . Core::_('Shop_Price_Setting.currency') . '</th>
						<th colspan="3" class="border-bottom-success toggle-shop-price-0 ' . $hiddenPrice0 . '" scope="col">' . Core::_('Shop_Price_Setting.basic') . '</th>
		';

		foreach ($aShop_Prices as $key => $oShop_Price)
		{
			$color = isset($aColors[$key])
				? $aColors[$key]
				: 'success';

			$hidden = !in_array($oShop_Price->id, $aExistPriceIDs)
				? 'hidden'
				: '';

			$itemTable .= '
				<th colspan="3" class="border-bottom-' . $color . ' toggle-shop-price-' . $oShop_Price->id . ' ' . $hidden . '" scope="col">' . htmlspecialchars($oShop_Price->name) . '</th>
			';
		}

		$itemTable .= '
						<th rowspan="2" scope="col">  </th>
					</tr>
					<tr>
						<th class="toggle-shop-price-0 ' . $hiddenPrice0 . '">' . Core::_('Shop_Price_Setting.old') . '</th>
						<th class="toggle-shop-price-0 ' . $hiddenPrice0 . '">' . Core::_('Shop_Price_Setting.percent') . '</th>
						<th class="toggle-shop-price-0 ' . $hiddenPrice0 . '">' . Core::_('Shop_Price_Setting.new') . '</th>
		';

		foreach ($aShop_Prices as $oShop_Price)
		{
			$hidden = !in_array($oShop_Price->id, $aExistPriceIDs)
				? 'hidden'
				: '';

			$itemTable .= '
				<th class="toggle-shop-price-' . $oShop_Price->id . ' ' . $hidden . '">' . Core::_('Shop_Price_Setting.old') . '</th>
				<th class="toggle-shop-price-' . $oShop_Price->id . ' ' . $hidden . '">' . Core::_('Shop_Price_Setting.percent') . '</th>
				<th class="toggle-shop-price-' . $oShop_Price->id . ' ' . $hidden . '">' . Core::_('Shop_Price_Setting.new') . '</th>
			';
		}

		$itemTable .= '
					</tr>
				</thead>
				<tbody>
		';

		$i = 1;
		foreach ($aPrices as $shop_item_id => $aTmpSettingItems)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->getById($shop_item_id);

			if (!is_null($oShop_Item))
			{
				$oShop_Item = $oShop_Item->shortcut_id
					? $oShop_Item->Shop_Item
					: $oShop_Item;

				$currencyName = strlen($oShop_Item->Shop_Currency->name)
					? htmlspecialchars($oShop_Item->Shop_Currency->name)
					: '<i class="fa fa-exclamation-triangle darkorange" title="' . Core::_('Shop_Item.shop_item_not_currency') . '"></i>';

				$measureName = $oShop_Item->Shop_Measure->name;

				$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'deleteShopItem', NULL, 0, $oShop_Item->id, "shop_price_setting_id={$this->_object->id}&shop_item_id={$shop_item_id}");

				$externalLink = '';

				$oSiteAlias = $oShop->Site->getCurrentAlias();
				if ($oSiteAlias)
				{
					$sItemUrl = ($oShop->Structure->https ? 'https://' : 'http://')
						. $oSiteAlias->name
						. $oShop->Structure->getPath()
						. $oShop_Item->getPath();

					$externalLink = '<a class="margin-left-5" target="_blank" href="' . $sItemUrl .  '"><i class="fa fa-external-link"></i></a>';
				}

				$itemTable .= '
						<tr id="shop-item-' . $oShop_Item->id . '" data-item-id="' . $oShop_Item->id . '">
							<td class="index">' . $i . '</td>
							<td>' . htmlspecialchars($oShop_Item->name) . $externalLink . '</td>
							<td>' . htmlspecialchars($measureName) . '</td>
							<td>' . $currencyName . '</td>
				';

				foreach ($aAllPricesIDs as $shop_price_id)
				{
					$hidden = !in_array($shop_price_id, $aExistPriceIDs)
						? 'hidden'
						: '';

					$oShop_Price_Setting_Item = isset($aTmpSettingItems[$shop_price_id])
						? $aTmpSettingItems[$shop_price_id]
						: NULL;

					if (!is_null($oShop_Price_Setting_Item))
					{
						$name = 'shop_item_new_price_' . $oShop_Price_Setting_Item->id;
						$old_price = $oShop_Price_Setting_Item->old_price;
						$new_price = $oShop_Price_Setting_Item->new_price;
					}
					else
					{
						$name = "shop_item_new_price[{$oShop_Item->id}][{$shop_price_id}]";
						// $old_price = 0; // получать или из цен, или из ->price для 0-й

						$oShop_Item_Price = $oShop_Item->Shop_Item_Prices->getByShop_price_id($shop_price_id);

						$old_price = !is_null($oShop_Item_Price)
							? $oShop_Item_Price->value
							: $oShop_Item->price;

						$new_price = '';
					}

					$itemTable .= '
						<td width="80" class="toggle-shop-price-' . $shop_price_id . ' old-price-' . $shop_price_id . ' ' . $hidden . '">' . htmlspecialchars($old_price) . '</td>
						<td width="80" class="toggle-shop-price-' . $shop_price_id . ' ' . $hidden . '"><span class="percent-diff-' . $shop_price_id . '"></span></td>
						<td width="80" class="toggle-shop-price-' . $shop_price_id . ' ' . $hidden . '"><input data-shop-price-id="' . $shop_price_id . '" class="set-item-new-price form-control" name="' . $name . '" value="' . htmlspecialchars($new_price) . '" ' . ($hidden == 'hidden' ? 'disabled' : '') . ' /></td>
					';
				}

				$itemTable .= '
						<td><a class="delete-associated-item" onclick="res = confirm(\'' . Core::_('Shop_Price_Setting.delete_dialog') . '\'); if (res) {' . $onclick . '} return res;"><i class="fa fa-times-circle darkorange"></i></a></td>
					</tr>
				';
			}

			$i++;
		}

		$itemTable .= '
				</tbody>
			</table>
		</div>
		';

		$oShopItemRow1->add(
			Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12'))
				->class('add-shop-item form-control')
				->placeholder(Core::_('Shop_Price_Setting.add_item_placeholder'))
				->name('set_item_name')
		);

		$oShopItemRow2
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12')
				->add(
					Admin_Form_Entity::factory('Code')->html($itemTable)
				)
		);

		$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
			->value("$('.add-shop-item').autocompleteShopItem('{$oShop->id}', 0, function(event, ui) {
				$('.shop-item-table > tbody').append(
					$('<tr id=\"shop-item-' + ui.item.id + '\" data-item-id=\"' + ui.item.id + '\"><td class=\"index\"></td><td>' + $.escapeHtml(ui.item.label) + '<input type=\'hidden\' name=\'shop_item_id[]\' value=\'' + (typeof ui.item.id !== 'undefined' ? ui.item.id : 0) + '\'/>' + '</td><td>' + $.escapeHtml(ui.item.measure) + '</td><td>' + ui.item.currency + '</td></tr>')
				);

				var aExistItems = [];
				$.each($('input[name ^= shop_price_]:checked'), function (index, item) {
					aExistItems.push(parseInt($(this).prop('id')));
				});

				if ($.isArray(ui.item.aPrices))
				{
					$.each(ui.item.aPrices, function (index, aArray) {

						var shop_price_id = parseInt(aArray.id),
							old_price = aArray.price,
							hidden = $.inArray(shop_price_id, aExistItems) == -1
								? 'hidden'
								: '',
							disabled = $.inArray(shop_price_id, aExistItems) == -1
								? 'disabled'
								: '';

						$('.shop-item-table > tbody tr:last-child').append($('<td width=\"80\" class=\"toggle-shop-price-' + shop_price_id + ' old-price-' + shop_price_id + ' ' + hidden + '\">' + old_price + '</td><td class=\"toggle-shop-price-' + shop_price_id + ' ' + hidden + '\"><span class=\"percent-diff-' + shop_price_id + '\"></span></td><td width=\"80\" class=\"toggle-shop-price-' + shop_price_id + ' ' + hidden + '\"><input data-shop-price-id=\"' + shop_price_id + '\" class=\"set-item-new-price form-control\" name=\"shop_item_new_price[' + ui.item.id + '][' + shop_price_id + ']\" value=\"\" ' + disabled + ' /></td>'));
					});
				}

				$('.shop-item-table > tbody tr:last-child').append($('<td><a class=\"delete-associated-item\" onclick=\"$(this).parents(\'tr\').remove()\"><i class=\"fa fa-times-circle darkorange\"></i></a></td>'));

				ui.item.value = '';

				$.prepareShopPrices();
			  });

			$.changeShopPrices($('.set-item-new-price'));

			$.prepareShopPrices();
		");

		$oShopItemRow2->add($oCore_Html_Entity_Script);

		$title = $this->_object->id
			? Core::_('Shop_Price_Setting.edit_form_title', $this->_object->number)
			: Core::_('Shop_Price_Setting.add_form_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Price_Setting_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->addSkipColumn('posted');

		$this->_object->user_id = intval(Core_Array::getPost('user_id'));

		parent::_applyObjectProperty();

		if ($this->_object->number == '')
		{
			$this->_object->number = $this->_object->id;
			$this->_object->save();
		}

		$Shop_Price_Entry_Controller = new Shop_Price_Entry_Controller();

		$bNeedsRePost = FALSE;

		// Существующие товары
		$aShop_Price_Setting_Items = $this->_object->Shop_Price_Setting_Items->findAll(FALSE);
		foreach ($aShop_Price_Setting_Items as $oShop_Price_Setting_Item)
		{
			$price = Core_Array::getPost('shop_item_new_price_' . $oShop_Price_Setting_Item->id);

			// Может быть в случае, если при импорте добавлили один товар дважды
			if (is_null($price))
			{
				$oShop_Price_Setting_Item->delete();
				
				$bNeedsRePost = TRUE;
			}
			elseif ($price !== '')
			{
				$oShop_Price_Setting_Item->new_price != $price && $bNeedsRePost = TRUE;
				
				$old_price = $Shop_Price_Entry_Controller->getPrice($oShop_Price_Setting_Item->shop_price_id, $oShop_Price_Setting_Item->shop_item_id, $this->_object->datetime);

				is_null($old_price)
					&& $old_price = $oShop_Price_Setting_Item->Shop_Item->price;

				$oShop_Price_Setting_Item->old_price = $old_price;
				$oShop_Price_Setting_Item->new_price = $price;
				$oShop_Price_Setting_Item->save();
			}
		}

		// Новые товары
		$aAddShopItems = Core_Array::getPost('shop_item_new_price', array());
		
		count($aAddShopItems) && $bNeedsRePost = TRUE;
		
		foreach ($aAddShopItems as $shop_item_id => $aTmpPrices)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->getById($shop_item_id);

			if (!is_null($oShop_Item))
			{
				$oShop_Item = $oShop_Item->shortcut_id
					? $oShop_Item->Shop_Item
					: $oShop_Item;

				foreach ($aTmpPrices as $shop_price_id => $value)
				{
					if ($value != '')
					{
						$oShop_Item_Price = $shop_price_id
							? $oShop_Item->Shop_Item_Prices->getByShop_price_id($shop_price_id)
							: NULL;

						$old_price = $Shop_Price_Entry_Controller->getPrice($shop_price_id, $oShop_Item->id, $this->_object->datetime);

						is_null($old_price)
							&& $old_price = $oShop_Item->price;

						$oShop_Price_Setting_Item = Core_Entity::factory('Shop_Price_Setting_Item');
						$oShop_Price_Setting_Item
							->shop_price_setting_id($this->_object->id)
							->shop_price_id($shop_price_id)
							->shop_item_id($oShop_Item->id)
							->old_price($old_price)
							->new_price($value)
							->save();
					}
				}
			}
		}

		// Проводим документ
		/*Core_Array::getPost('posted')
			? $this->_object->post()
			: $this->_object->unpost();*/
			
		/*if (Core_Array::getPost('posted') && !$this->posted)
		{
			$this->_object->post();
		}
		elseif (!Core_Array::getPost('posted') && $this->posted)
		{
			$this->_object->unpost();
		}
		elseif ($bNeedsRePost)
		{
			$this->_object->unpost();
			$this->_object->post();
		}*/
		
		($bNeedsRePost || !Core_Array::getPost('posted')) && $this->_object->unpost();
		Core_Array::getPost('posted') && $this->_object->post();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Fill prices list
	 * @return array
	 */
	public function fillPricesList(Shop_Model $oShop)
	{
		$aReturn = array(Core::_('Shop_Warehouse_Incoming.basic'));

		// if (Core::moduleIsActive('siteuser'))
		// {
			$aShop_Prices = $oShop->Shop_Prices->findAll();

			foreach ($aShop_Prices as $oShop_Price)
			{
				$aReturn[$oShop_Price->id] = $oShop_Price->name . ' [' . $oShop_Price->id . ']';
			}
		// }

		return $aReturn;
	}
}