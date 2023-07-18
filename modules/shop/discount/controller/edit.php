<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discount Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discount_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			case 'shop_discount':
				if (!$object->id)
				{
					$object->shop_discount_dir_id = Core_Array::getGet('shop_discount_dir_id', 0);
				}
			break;
			case 'shop_discount_dir':
				if (!$object->id)
				{
					$object->parent_id = Core_Array::getGet('shop_discount_dir_id', 0);
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

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_discount':
				// Создаем вкладку
				$oShopDiscountTabExportImport = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Discount.tab_export'))
					->name('ExportImport');

				// Добавляем вкладку
				$this
					->addTabAfter($oShopDiscountTabExportImport, $oMainTab);

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					//->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oDaysBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
					->add($oSiteuserGroupBlock = Admin_Form_Entity::factory('Div')->class('well with-header well-sm'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'));

				$oShopDiscountTabExportImport
					->add($oShopDiscountTabExportImportRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				//Переносим GUID на "Экспорт/Импорт"
				$oMainTab->move($this->getField('guid'), $oShopDiscountTabExportImport);

				$oShopDiscountTabExportImport->move($this->getField('guid'), $oShopDiscountTabExportImportRow1);

				$this->getField('description')->rows(7)->wysiwyg(Core::moduleIsActive('wysiwyg'));
				$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);

				$sColorValue = ($this->_object->id && $this->getField('color')->value)
				? $this->getField('color')->value
				: '#aebec4';

				$this->getField('color')
					->colorpicker(TRUE)
					->value($sColorValue);

				$oMainTab->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2')), $oMainRow3);
				$oMainTab->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2')), $oMainRow3);
				$oMainTab->move($this->getField('start_time')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2')), $oMainRow3);
				$oMainTab->move($this->getField('end_time')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-2')), $oMainRow3);
				$oMainTab->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3')), $oMainRow3);

				$oDaysBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-palegreen')
						->value(Core::_("Shop_Discount.days"))
					)
					->add($oDaysBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainTab->move($this->getField('day1')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2')), $oDaysBlockRow1);
				$oMainTab->move($this->getField('day2')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2')), $oDaysBlockRow1);
				$oMainTab->move($this->getField('day3')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2')), $oDaysBlockRow1);
				$oMainTab->move($this->getField('day4')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2')), $oDaysBlockRow1);
				$oMainTab->move($this->getField('day5')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-3')), $oDaysBlockRow1);
				$oMainTab->move($this->getField('day6')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2'))->class('colored-danger'), $oDaysBlockRow1);
				$oMainTab->move($this->getField('day7')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4 col-md-3 col-lg-2'))->class('colored-danger'), $oDaysBlockRow1);

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
						->value(Core::_("Shop_Discount.siteuser_groups"))
					)
					->add($oSiteuserGroupBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$aTmp = array();

				$aShop_Discount_Siteuser_Groups = $this->_object->Shop_Discount_Siteuser_Groups->findAll(FALSE);
				foreach ($aShop_Discount_Siteuser_Groups as $oShop_Discount_Siteuser_Group)
				{
					!in_array($oShop_Discount_Siteuser_Group->siteuser_group_id, $aTmp)
						&& $aTmp[] = $oShop_Discount_Siteuser_Group->siteuser_group_id;
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

				$oMainTab
					->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow5)
					->move($this->getField('not_apply_purchase_discount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21'))->class('colored-danger times'), $oMainRow5)
					->move($this->getField('public')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow5);

				$oMainTab
					->move($this->getField('url')->divAttr(array('class' => 'form-group col-xs-12 col-sm-8'))->placeholder('https://'), $oMainRow6)
					->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 col-md-3 col-lg-2')), $oMainRow6);

				$oMainTab
					->delete($this->getField('value'))
					->delete($this->getField('type'));

				// Удаляем группу
				$oAdditionalTab->delete($this->getField('shop_discount_dir_id'));

				$oGroupSelect = Admin_Form_Entity::factory('Select');

				$oGroupSelect
					->caption(Core::_('Shop_Discount.shop_discount_dir_id'))
					->options(array(' … ') + self::fillShopDiscountDir($this->_object->shop_id))
					->name('shop_discount_dir_id')
					->value($this->_object->shop_discount_dir_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-lg-4'));

				$oMainRow1->add($oGroupSelect);

				$oMainRow1->add(Admin_Form_Entity::factory('Div')
					->class('col-xs-12 col-sm-4 col-md-3 col-lg-2 form-group input-group select-group')
					->add(Admin_Form_Entity::factory('Code')
						->html('<div class="caption">' . Core::_('Shop_Discount.value') . '</div>')
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
						)
					)
				);

				$oMainTab->move($this->getField('coupon')
					->divAttr(array('class' => 'form-group margin-top-21 col-xs-12 col-sm-6 col-md-4 col-lg-3'))->onclick("$.toggleCoupon(this)"), $oMainRow1);

				$hidden = !$this->_object->coupon
					? ' hidden'
					: '';

				$oMainTab->move($this->getField('coupon_text')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3 col-lg-3' . $hidden)), $oMainRow1);

				$title = $this->_object->id
					? Core::_('Shop_Discount.item_discount_edit_form_title', $this->_object->name, FALSE)
					: Core::_('Shop_Discount.item_discount_add_form_title');
			break;
			case 'shop_discount_dir':
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
					->options(array(' … ') + self::fillShopDiscountDir($this->_object->shop_id, 0, array($this->_object->id)))
					->name('parent_id')
					->value($this->_object->parent_id)
					->divAttr(array('class' => 'form-group col-xs-12 col-md-6'));

				$oMainRow3->add($oGroupSelect);

				$oMainTab->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

				$title = $this->_object->id
					? Core::_('Shop_Discount_Dir.edit_title', $this->_object->name, FALSE)
					: Core::_('Shop_Discount_Dir.add_title');
			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Discount_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$modelName = $this->_object->getModelName();

		switch ($modelName)
		{
			case 'shop_discount':
				// Скидка не может быть больше 100%
				if ($this->_object->type == 0 && $this->_object->value > 100)
				{
					$this->_object->value = 0;
					$this->_object->save();

					$this->addMessage(
						Core_Message::get(Core::_('Shop_Discount.percent_error'), 'error')
					);
				}

				// Группа доступа
				$aSiteuser_Groups = array(0 => Core::_('Structure.all'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $aSiteuser_Groups + $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Shop->site_id);
				}

				$aTmp = array();

				$aShop_Discount_Siteuser_Groups = $this->_object->Shop_Discount_Siteuser_Groups->findAll(FALSE);
				foreach ($aShop_Discount_Siteuser_Groups as $oShop_Discount_Siteuser_Group)
				{
					!in_array($oShop_Discount_Siteuser_Group->siteuser_group_id, $aTmp)
						&& $aTmp[] = $oShop_Discount_Siteuser_Group->siteuser_group_id;
				}

				foreach ($aSiteuser_Groups as $siteuser_group_id => $name)
				{
					$bSiteuserGroupChecked = Core_Array::getPost('siteuser_group_' . $siteuser_group_id);

					if ($bSiteuserGroupChecked)
					{
						if (!in_array($siteuser_group_id, $aTmp))
						{

							$oShop_Discount_Siteuser_Group = Core_Entity::factory('Shop_Discount_Siteuser_Group');
							$oShop_Discount_Siteuser_Group->siteuser_group_id = $siteuser_group_id;
							$this->_object->add($oShop_Discount_Siteuser_Group);
						}
					}
					else
					{
						if (in_array($siteuser_group_id, $aTmp))
						{
							$oShop_Discount_Siteuser_Group = $this->_object->Shop_Discount_Siteuser_Groups->getObject($this->_object, $siteuser_group_id);

							!is_null($oShop_Discount_Siteuser_Group)
								&& $oShop_Discount_Siteuser_Group->delete();
						}
					}
				}
			break;
		}

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
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillShopDiscountDir($iShopId, $iShopDicsountDirParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iShopId = intval($iShopId);
		$iShopDicsountDirParentId = intval($iShopDicsountDirParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('shop_discount_dirs')
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
					$aReturn += self::fillShopDiscountDir($iShopId, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}
}