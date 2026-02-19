<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Gift_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Gift_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'shop_gift':
				if (!$object->id)
				{
					$object->shop_gift_dir_id = Core_Array::getGet('shop_gift_dir_id', 0);
				}
			break;
			case 'shop_gift_dir':
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('shop_gift_dir_id', 0);
				}
			break;
		}

		return parent::setObject($object);
	}
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_gift':
				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
					->add($oSiteuserGroupBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

				$oConditionsBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-palegreen')
						->value(Core::_("Shop_Gift.conditions"))
					)
					->add($oConditionsBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsBlockRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oSiteuserGroupBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-azure')
						->value(Core::_("Shop_Gift.siteuser_groups"))
					)
					->add($oSiteuserGroupBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$oAdditionalTab->delete($this->getField('shop_currency_id'));
				$oMainTab
					->delete($this->getField('mode'))
					->delete($this->getField('value'))
					->delete($this->getField('type'));

				$oMainRow1->add(Admin_Form_Entity::factory('Div')
					->class('col-xs-12 col-sm-6 col-md-3 col-lg-2 input-group select-group')
					->add(Admin_Form_Entity::factory('Code')
						->html('<div class="caption">' . Core::_('Shop_Gift.value') . '</div>')
					)
					->add(Admin_Form_Entity::factory('Input')
						->name('value')
						->value($this->_object->value)
						->divAttr(array('class' => ''))
						->class('form-control semi-bold')
						->add(Core_Html_Entity::factory('Select')
							->name('type')
							->options(array(
								'%',
								$this->_object->Shop->Shop_Currency->sign
							))
							->value($this->_object->type)
							->class('form-control input-group-addon')
							->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1], 'maxHidden', 'maxShown')")
						)
					)
				);

				$this->getField('max_discount')
					->add(
						Core_Html_Entity::factory('Span')
							->class('input-group-addon dimension_patch')
							->value(htmlspecialchars((string) $this->_object->Shop->Shop_Currency->sign))
					);

				$oMainTab->move($this->getField('max_discount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2 col-lg-2 maxHidden-1')), $oMainRow1);

				$oMainTab
					->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-3 col-lg-2')), $oMainRow1)
					->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-3 col-lg-2')), $oMainRow1);

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oConditionsBlockRow1->add(
					Admin_Form_Entity::factory('Radiogroup')
						->name('mode')
						->value($this->_object->mode)
						->radio(array(
							Core::_('Shop_Gift.order_discount_case_and'),
							Core::_('Shop_Gift.order_discount_case_or')
						))
						->ico(
							array(
								'fa-solid fa-chevron-up',
								'fa-solid fa-chevron-down'
							)
						)
						->divAttr(array('class' => 'form-group col-xs-12 rounded-radio-group'))
						->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1]); window.dispatchEvent(new Event('resize'));")
				);

				$oMainTab
					->move($this->getField('min_amount')->divAttr(array('class' => 'form-group col-xs-4 col-sm-3')), $oConditionsBlockRow1)
					->move($this->getField('max_amount')->divAttr(array('class' => 'form-group col-xs-4 col-sm-3')), $oConditionsBlockRow1);

				$oConditionsBlockRow1->add(
					Admin_Form_Entity::factory('Select')
						->name('shop_currency_id')
						->caption(Core::_('Shop_Gift.shop_currency_id'))
						->options(
							Shop_Controller::fillCurrencies()
						)
						->divAttr(array('class' => 'form-group col-xs-4 col-sm-3'))
						->value(
							is_null($this->_object->id)
								? $this->_object->Shop->shop_currency_id
								: $this->_object->shop_currency_id
						)
				);

				$measureName = $this->_object->Shop->Shop_Measure->name;

				$oMainTab
					->move($this->getField('min_count')->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 hidden-2')), $oConditionsBlockRow2)
					->move($this->getField('max_count')->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 hidden-2')), $oConditionsBlockRow2)
					->move($this->getField('min_weight')->caption(Core::_('Shop_Gift.min_weight', $measureName))->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 hidden-2')), $oConditionsBlockRow3)
					->move($this->getField('max_weight')->caption(Core::_('Shop_Gift.max_weight', $measureName))->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 hidden-2')), $oConditionsBlockRow3);

				$oMainTab
					->move($this->getField('coupon')
					->divAttr(array('class' => 'form-group margin-top-21 col-xs-12 col-sm-6 col-md-4 col-lg-2'))->onclick("$.toggleCoupon(this)"), $oConditionsBlockRow4);

				$hidden = !$this->_object->coupon
					? ' hidden'
					: '';

				$oMainTab->move($this->getField('coupon_text')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 col-lg-3' . $hidden)), $oConditionsBlockRow4);

				$aShopItemOptions = array();

				$aShopGiftShopItems = Shop_Gift_Controller::getEntitiesByType($this->_object, 0);
				foreach ($aShopGiftShopItems as $oShop_Gift_Entity)
				{
					$oShop_Item = Core_Entity::factory('Shop_Item')->getById($oShop_Gift_Entity->entity_id, FALSE);

					if (!is_null($oShop_Item))
					{
						$aShopItemOptions[$oShop_Item->id] = array(
							'value' => $oShop_Item->name . ' [' . $oShop_Item->id . ']',
							'attr' => array('selected' => 'selected')
						);
					}
				}

				$oSelectShopItems = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Gift.shop_item'))
					->options($aShopItemOptions)
					->name('shop_gift_shop_item_id[]')
					->class('shop-gift-items')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$htmlItems = '<script>
					$(function(){
						$("#' . $windowId . ' .shop-gift-items").select2({
							dropdownParent: $("#' . $windowId . '"),
							language: "' . Core_I18n::instance()->getLng() . '",
							minimumInputLength: 1,
							placeholder: "' . Core::_('Shop_Gift.select_item') . '",
							tags: true,
							allowClear: true,
							multiple: true,
							ajax: {
								url: "/admin/shop/gift/index.php?autocomplete=1&show_items=1&shop_id=' . $this->_object->shop_id . '",
								dataType: "json",
								type: "GET",
								processResults: function (data) {
									var aResults = [];
									$.each(data, function (index, item) {
										aResults.push(item);
									});
									return {
										results: aResults
									};
								}
							}
						});
					});</script>';

				$oMainRow3
					->add($oSelectShopItems)
					->add(Admin_Form_Entity::factory('Code')->html($htmlItems));

				$aShopGroupOptions = array();

				$aShopGiftShopGroups = Shop_Gift_Controller::getEntitiesByType($this->_object, 1);
				foreach ($aShopGiftShopGroups as $oShop_Gift_Entity)
				{
					$oShop_Group = Core_Entity::factory('Shop_Group')->getById($oShop_Gift_Entity->entity_id, FALSE);

					if (!is_null($oShop_Group))
					{
						$sParents = $oShop_Group->groupPathWithSeparator();

						$aShopGroupOptions[$oShop_Group->id] = array(
							'value' => $sParents . ' [' . $oShop_Group->id . ']',
							'attr' => array('selected' => 'selected')
						);
					}
				}

				$oSelectShopGroups = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Gift.shop_group'))
					->options($aShopGroupOptions)
					->name('shop_gift_shop_group_id[]')
					->class('shop-gift-groups')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$htmlGroups = '<script>
					$(function(){
						$("#' . $windowId . ' .shop-gift-groups").select2({
							dropdownParent: $("#' . $windowId . '"),
							language: "' . Core_I18n::instance()->getLng() . '",
							minimumInputLength: 1,
							placeholder: "' . Core::_('Shop_Gift.select_group') . '",
							tags: true,
							allowClear: true,
							multiple: true,
							ajax: {
								url: "/admin/shop/gift/index.php?autocomplete=1&show_groups=1&shop_id=' . $this->_object->shop_id . '",
								dataType: "json",
								type: "GET",
								processResults: function (data) {
									var aResults = [];
									$.each(data, function (index, item) {
										aResults.push(item);
									});
									return {
										results: aResults
									};
								}
							}
						});
					});</script>';

				$oMainRow4
					->add($oSelectShopGroups)
					->add(Admin_Form_Entity::factory('Code')->html($htmlGroups));

				// Группа доступа
				$aSiteuser_Groups = array(0 => Core::_('Shop_Gift.all'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $aSiteuser_Groups + $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
				}

				$aTmp = array();

				$aShop_Gift_Siteuser_Groups = $this->_object->Shop_Gift_Siteuser_Groups->findAll(FALSE);
				foreach ($aShop_Gift_Siteuser_Groups as $oShop_Gift_Siteuser_Group)
				{
					!in_array($oShop_Gift_Siteuser_Group->siteuser_group_id, $aTmp)
						&& $aTmp[] = $oShop_Gift_Siteuser_Group->siteuser_group_id;
				}

				foreach ($aSiteuser_Groups as $siteuser_group_id => $name)
				{
					$oSiteuserGroupBlockRow1->add($oCheckbox = Admin_Form_Entity::factory('Checkbox')
						->divAttr(array('class' => 'form-group col-xs-12 col-md-4'))
						->name('siteuser_group_' . $siteuser_group_id)
						->caption(htmlspecialchars($name))
					);

					(!$this->_object->id || in_array($siteuser_group_id, $aTmp))
						&& $oCheckbox->checked('checked');
				}

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('shop_gift_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Gift.shop_gift_dir_id'))
					->options(array(' … ') + self::fillShopGiftDir($this->_object->shop_id))
					->name('shop_gift_dir_id')
					->value($this->_object->shop_gift_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3'));

				$oMainRow5->add($oGroupSelect);

				$oMainTab
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-3')), $oMainRow5)
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 margin-top-21')), $oMainRow5);

				$oMainTab->add(
					Admin_Form_Entity::factory('Code')
						->html("<script>radiogroupOnChange('{$windowId}', '{$this->_object->mode}', [0,1]);
						radiogroupOnChange('{$windowId}', {$this->_object->type}, [0,1], 'maxHidden', 'maxShown')</script>")
				);

				$title = $this->_object->id
					? Core::_('Shop_Gift.edit_title', $this->_object->name, FALSE)
					: Core::_('Shop_Gift.add_title');
			break;
			case 'shop_gift_dir':
				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('description')->rows(9)->wysiwyg(Core::moduleIsActive('wysiwyg'));
				$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('parent_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Discount_Dir.parent_id'))
					->options(array(' … ') + self::fillShopGiftDir($this->_object->shop_id, 0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow3->add($oGroupSelect);

				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

				$title = $this->_object->id
					? Core::_('Shop_Gift_Dir.edit_title', $this->_object->name, FALSE)
					: Core::_('Shop_Gift_Dir.add_title');
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Gift_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_gift':
				// Товары
				$aShopGiftShopItemIds = Core_Array::getPost('shop_gift_shop_item_id', array());
				!is_array($aShopGiftShopItemIds) && $aShopGiftShopItemIds = array();

				$aTmp = array();

				$aShop_Gift_Shop_Items = Shop_Gift_Controller::getEntitiesByType($this->_object, 0);
				foreach ($aShop_Gift_Shop_Items as $oShop_Gift_Entity)
				{
					if (!in_array($oShop_Gift_Entity->entity_id, $aShopGiftShopItemIds))
					{
						$oShop_Gift_Entity->delete();
					}
					else
					{
						$aTmp[] = $oShop_Gift_Entity->entity_id;
					}
				}

				// Новые товары
				$aNewShopGiftShopItemIds = array_diff($aShopGiftShopItemIds, $aTmp);
				foreach ($aNewShopGiftShopItemIds as $iNewShopGiftShopItemId)
				{
					$oShop_Gift_Entity = Core_Entity::factory('Shop_Gift_Entity');
					$oShop_Gift_Entity->shop_gift_id = $this->_object->id;
					$oShop_Gift_Entity->type = 0;
					$oShop_Gift_Entity->entity_id = $iNewShopGiftShopItemId;
					$oShop_Gift_Entity->save();
				}

				// Группы
				$aShopGiftShopGroupIds = Core_Array::getPost('shop_gift_shop_group_id', array());
				!is_array($aShopGiftShopGroupIds) && $aShopGiftShopGroupIds = array();

				$aTmp = array();

				$aShop_Gift_Shop_Groups = Shop_Gift_Controller::getEntitiesByType($this->_object, 1);
				foreach ($aShop_Gift_Shop_Groups as $oShop_Gift_Entity)
				{
					if (!in_array($oShop_Gift_Entity->entity_id, $aShopGiftShopGroupIds))
					{
						$oShop_Gift_Entity->delete();
					}
					else
					{
						$aTmp[] = $oShop_Gift_Entity->entity_id;
					}
				}

				// Новые группы
				$aNewShopGiftShopGroupIds = array_diff($aShopGiftShopGroupIds, $aTmp);
				foreach ($aNewShopGiftShopGroupIds as $iNewShopGiftShopGroupId)
				{
					$oShop_Gift_Entity = Core_Entity::factory('Shop_Gift_Entity');
					$oShop_Gift_Entity->shop_gift_id = $this->_object->id;
					$oShop_Gift_Entity->type = 1;
					$oShop_Gift_Entity->entity_id = $iNewShopGiftShopGroupId;
					$oShop_Gift_Entity->save();
				}

				// Группа доступа
				$aSiteuser_Groups = array(0 => Core::_('Structure.all'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $aSiteuser_Groups + $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
				}

				$aTmp = array();

				$aShop_Gift_Siteuser_Groups = $this->_object->Shop_Gift_Siteuser_Groups->findAll(FALSE);
				foreach ($aShop_Gift_Siteuser_Groups as $oShop_Gift_Siteuser_Group)
				{
					!in_array($oShop_Gift_Siteuser_Group->siteuser_group_id, $aTmp)
						&& $aTmp[] = $oShop_Gift_Siteuser_Group->siteuser_group_id;
				}

				foreach ($aSiteuser_Groups as $siteuser_group_id => $name)
				{
					$bSiteuserGroupChecked = Core_Array::getPost('siteuser_group_' . $siteuser_group_id);

					if ($bSiteuserGroupChecked)
					{
						if (!in_array($siteuser_group_id, $aTmp))
						{

							$oShop_Gift_Siteuser_Group = Core_Entity::factory('Shop_Gift_Siteuser_Group');
							$oShop_Gift_Siteuser_Group->siteuser_group_id = $siteuser_group_id;
							$this->_object->add($oShop_Gift_Siteuser_Group);
						}
					}
					else
					{
						if (in_array($siteuser_group_id, $aTmp))
						{
							$oShop_Gift_Siteuser_Group = $this->_object->Shop_Gift_Siteuser_Groups->getObject($this->_object, $siteuser_group_id);

							!is_null($oShop_Gift_Siteuser_Group)
								&& $oShop_Gift_Siteuser_Group->delete();
						}
					}
				}
			break;
		}

		Core::moduleIsActive('wysiwyg') && Wysiwyg_Controller::uploadImages($this->_formValues, $this->_object, $this->_Admin_Form_Controller);

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Redirect groups tree
	 * @var array
	 */
	static protected $_aGroupTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iShopId shop ID
	 * @param int $iShopGiftDirParentId parent ID
	 * @param array $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillShopGiftDir($iShopId, $iShopGiftDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShopId = intval($iShopId);
		$iShopGiftDirParentId = intval($iShopGiftDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_gift_dirs')
				->where('shop_id', '=', $iShopId)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				self::$_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset(self::$_aGroupTree[$iShopGiftDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iShopGiftDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillShopGiftDir($iShopId, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}
}