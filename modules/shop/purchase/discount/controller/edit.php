<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Purchase_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Purchase_Discount_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			case 'shop_purchase_discount':
				if (!$object->id)
				{
					$object->shop_purchase_discount_dir_id = Core_Array::getGet('shop_purchase_discount_dir_id', 0);
				}
			break;
			case 'shop_purchase_discount_dir':
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('shop_purchase_discount_dir_id', 0);
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
			case 'shop_purchase_discount':
				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
					->add($oSiteuserGroupBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'));

				$oConditionsBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-palegreen')
						->value(Core::_("Shop_Purchase_Discount.conditions"))
					)
					->add($oConditionsBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oConditionsBlockRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					;

				$oAdditionalTab->delete($this->getField('shop_currency_id'));
				$oMainTab
					->delete($this->getField('mode'))
					->delete($this->getField('value'))
					->delete($this->getField('type'))
					->delete($this->getField('position'));

				$oMainRow1->add(Admin_Form_Entity::factory('Div')
					->class('col-xs-12 col-sm-6 col-md-3 col-lg-2 input-group select-group')
					->add(Admin_Form_Entity::factory('Code')
						->html('<div class="caption">' . Core::_('Shop_Purchase_Discount.value') . '</div>')
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

				$oPositionSelectField = Admin_Form_Entity::factory('Select')
					->id('position')
					->name('position')
					->caption(Core::_('Shop_Purchase_Discount.position'))
					->options(array(0 => Core::_('Shop_Purchase_Discount.total-amount'), 2 => 2, 3 => 3, 4 => 4, 5 => 5))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 col-lg-2'))
					->value($this->_object->position);

				$oMainRow1->add($oPositionSelectField);

				$oMainTab
					->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-6 col-md-3')), $oMainRow1)
					->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-6 col-md-3')), $oMainRow1);

				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oConditionsBlockRow1->add(
					Admin_Form_Entity::factory('Radiogroup')
						->name('mode')
						->value($this->_object->mode)
						->radio(array(
							Core::_('Shop_Purchase_Discount.order_discount_case_and'),
							Core::_('Shop_Purchase_Discount.order_discount_case_or'),
							Core::_('Shop_Purchase_Discount.order_discount_case_accumulative')
						))
						->ico(
							array(
								'fa-solid fa-chevron-up',
								'fa-solid fa-chevron-down',
								'fa-solid fa-shopping-cart',
							)
						)
						->divAttr(array('class' => 'form-group col-xs-12 rounded-radio-group'))
						->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1,2]); window.dispatchEvent(new Event('resize'));")
				);

				$oMainTab
					->move($this->getField('min_amount')->divAttr(array('class' => 'form-group col-xs-4 col-sm-3')), $oConditionsBlockRow1)
					->move($this->getField('max_amount')->divAttr(array('class' => 'form-group col-xs-4 col-sm-3')), $oConditionsBlockRow1);

				$oConditionsBlockRow1->add(
					Admin_Form_Entity::factory('Select')
						->name('shop_currency_id')
						->caption(Core::_('Shop_Purchase_Discount.shop_currency_id'))
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
					->move($this->getField('min_weight')->caption(Core::_('Shop_Purchase_Discount.min_weight', $measureName))->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 hidden-2')), $oConditionsBlockRow3)
					->move($this->getField('max_weight')->caption(Core::_('Shop_Purchase_Discount.max_weight', $measureName))->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 hidden-2')), $oConditionsBlockRow3);

				if (Core::moduleIsActive('siteuser'))
				{
					$oMainTab->move($this->getField('first_order')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oConditionsBlockRow4);
				}
				else
				{
					$oMainTab->delete($this->getField('first_order'));
				}

				$oMainTab
					->move($this->getField('coupon')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oConditionsBlockRow4);

				// Группа доступа
				$aSiteuser_Groups = array(0 => Core::_('Shop_Discount.all'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $aSiteuser_Groups + $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
				}

				$oSiteuserGroupBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-azure')
						->value(Core::_("Shop_Purchase_Discount.siteuser_groups"))
					)
					->add($oSiteuserGroupBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$aTmp = array();

				$aShop_Purchase_Discount_Siteuser_Groups = $this->_object->Shop_Purchase_Discount_Siteuser_Groups->findAll(FALSE);
				foreach ($aShop_Purchase_Discount_Siteuser_Groups as $oShop_Purchase_Discount_Siteuser_Group)
				{
					!in_array($oShop_Purchase_Discount_Siteuser_Group->siteuser_group_id, $aTmp)
						&& $aTmp[] = $oShop_Purchase_Discount_Siteuser_Group->siteuser_group_id;
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
				$oAdditionalTab->delete($this->getField('shop_purchase_discount_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Purchase_Discount.shop_purchase_discount_dir_id'))
					->options(array(' … ') + self::fillShopPurchaseDiscountDir($this->_object->shop_id))
					->name('shop_purchase_discount_dir_id')
					->value($this->_object->shop_purchase_discount_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3'));

				$oMainRow7->add($oGroupSelect);

				$oMainTab
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-3')), $oMainRow7)
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 margin-top-21')), $oMainRow7);

				$oMainTab->add(
					Admin_Form_Entity::factory('Code')
						->html("<script>radiogroupOnChange('{$windowId}', '{$this->_object->mode}', [0,1,2]);
						radiogroupOnChange('{$windowId}', {$this->_object->type}, [0,1], 'maxHidden', 'maxShown')</script>")
				);

				$title = $this->_object->id
					? Core::_('Shop_Purchase_Discount.edit_order_discount_form_title', $this->_object->name, FALSE)
					: Core::_('Shop_Purchase_Discount.add_order_discount_form_title');
			break;
			case 'shop_purchase_discount_dir':
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
					->options(array(' … ') + self::fillShopPurchaseDiscountDir($this->_object->shop_id, 0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow3->add($oGroupSelect);

				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

				$title = $this->_object->id
					? Core::_('Shop_Purchase_Discount_Dir.edit_title', $this->_object->name, FALSE)
					: Core::_('Shop_Purchase_Discount_Dir.add_title');
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Purchase_Discount_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_purchase_discount':
				// Группа доступа
				$aSiteuser_Groups = array(0 => Core::_('Structure.all'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $aSiteuser_Groups + $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
				}

				$aTmp = array();

				$aShop_Purchase_Discount_Siteuser_Groups = $this->_object->Shop_Purchase_Discount_Siteuser_Groups->findAll(FALSE);
				foreach ($aShop_Purchase_Discount_Siteuser_Groups as $oShop_Purchase_Discount_Siteuser_Group)
				{
					!in_array($oShop_Purchase_Discount_Siteuser_Group->siteuser_group_id, $aTmp)
						&& $aTmp[] = $oShop_Purchase_Discount_Siteuser_Group->siteuser_group_id;
				}

				foreach ($aSiteuser_Groups as $siteuser_group_id => $name)
				{
					$bSiteuserGroupChecked = Core_Array::getPost('siteuser_group_' . $siteuser_group_id);

					if ($bSiteuserGroupChecked)
					{
						if (!in_array($siteuser_group_id, $aTmp))
						{

							$oShop_Purchase_Discount_Siteuser_Group = Core_Entity::factory('Shop_Purchase_Discount_Siteuser_Group');
							$oShop_Purchase_Discount_Siteuser_Group->siteuser_group_id = $siteuser_group_id;
							$this->_object->add($oShop_Purchase_Discount_Siteuser_Group);
						}
					}
					else
					{
						if (in_array($siteuser_group_id, $aTmp))
						{
							$oShop_Purchase_Discount_Siteuser_Group = $this->_object->Shop_Purchase_Discount_Siteuser_Groups->getObject($this->_object, $siteuser_group_id);

							!is_null($oShop_Purchase_Discount_Siteuser_Group)
								&& $oShop_Purchase_Discount_Siteuser_Group->delete();
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
	 * @param int $iShopDicsountDirParentId parent ID
	 * @param array $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillShopPurchaseDiscountDir($iShopId, $iShopDicsountDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShopId = intval($iShopId);
		$iShopDicsountDirParentId = intval($iShopDicsountDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_purchase_discount_dirs')
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

		if (isset(self::$_aGroupTree[$iShopDicsountDirParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iShopDicsountDirParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']' ;
					$aReturn += self::fillShopPurchaseDiscountDir($iShopId, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}
}