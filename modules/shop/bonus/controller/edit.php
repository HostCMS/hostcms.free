<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Bonus Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Bonus_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		parent::setObject($object);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->getField('description')
			->rows(7)
			->wysiwyg(Core::moduleIsActive('wysiwyg'));

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2);
		$oMainTab->move($this->getField('start_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-3')), $oMainRow3);
		$oMainTab->move($this->getField('end_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-3')), $oMainRow3);

		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow4);

		$oMainTab->delete($this->getField('type'));
		$oMainTab->delete($this->getField('value'));

		$oMainRow1->add(Admin_Form_Entity::factory('Div')
			->class('col-xs-12 col-sm-6 col-md-4 col-lg-2 input-group select-group')
			->add(Admin_Form_Entity::factory('Code')
				->html('<div class="caption">' . Core::_('Shop_Bonus.value') . '</div>')
			)
			->add(Admin_Form_Entity::factory('Input')
				->name('value')
				->value($this->_object->value)
				->divAttr(array('class' => ''))
				->class('form-control semi-bold')
			)
			->add(Admin_Form_Entity::factory('Select')
				->name('type')
				->divAttr(array('class' => ''))
				->options(array(
					'%',
					$this->_object->Shop->Shop_Currency->name
				))
				->value($this->_object->type)
				->class('form-control input-group-addon')
			)
		);

		$oMainTab->move($this->getField('min_amount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-4 col-lg-2')), $oMainRow1);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$accrualValue = $this->_object->accrual_date != '0000-00-00 00:00:00' ? 0 : 1;

		$oShopBonusAccrual = Admin_Form_Entity::factory('Radiogroup')
			->name('accrual')
			->id('accrual' . time())
			->caption(Core::_('Shop_Bonus.accrual'))
			->value($accrualValue)
			->divAttr(array('class' => 'pull-left'))
			->radio(array(
				0 => Core::_('Shop_Bonus.from'),
				1 => Core::_('Shop_Bonus.through')
			))
			->ico(
				array(
					0 => 'fa-calendar',
					1 => 'fa-arrows-h',
			))
			->colors(
				array(
					0 => 'btn-sky',
					1 => 'btn-pink'
				)
			)
			->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1])");

		$oMainRow1->add($oShopBonusDiv = Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 col-sm-12 col-md-12 col-lg-8')
			->add($oShopBonusAccrual)
		);

		$oMainTab
			->move($this->getField('accrual_date')->divAttr(array('class' => 'pull-left margin-left-10 hidden-1'))->size(15), $oShopBonusDiv)
			->move($this->getField('accrual_days')->divAttr(array('class' => 'pull-left margin-left-10 hidden-0'))->size(6), $oShopBonusDiv)
			->move($this->getField('expire_days')->divAttr(array('class' => 'pull-left margin-left-10'))->size(6), $oShopBonusDiv);

		$this->title(
			$this->_object->id
				? Core::_('Shop_Bonus.edit_title', $this->_object->name)
				: Core::_('Shop_Bonus.add_title')
		);

		$oMainTab->add(
			Admin_Form_Entity::factory('Code')
				->html("<script>radiogroupOnChange('{$windowId}', '{$accrualValue}', [0,1])</script>")
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Bonus_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		Core_Array::get($this->_formValues, 'accrual')
			? $this->_formValues['accrual_date'] = ''
			: $this->_formValues['accrual_days'] = 0;

		parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}