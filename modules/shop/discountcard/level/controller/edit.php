<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Level Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discountcard_Level_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oMainTab
			->move($this->getField('name')->class('form-control input-lg')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('discount')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oMainRow2);

		$oMainTab->delete($this->getField('amount'));

		$oShop_Currency = $this->_object->Shop->Shop_Currency;

		$oMainRow2->add(
			Admin_Form_Entity::factory('Input')
				->caption(Core::_('Shop_Discountcard_Level.amount', htmlspecialchars($oShop_Currency->name)))
				->name('amount')
				->value($this->_object->amount)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
		);

		$oMainTab->delete($this->getField('level'));

		$oMainRow2
			->add(
				Admin_Form_Entity::factory('Input')
					->name('level')
					->id('level')
					->value($this->_object->level)
					->caption(Core::_('Shop_Discountcard_Level.level'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'))
			);

		$sColorValue = ($this->_object->id && $this->getField('color')->value)
			? $this->getField('color')->value
			: '#aebec4';

		$this->getField('color')
			->class('form-control colorpicker minicolors-input')
			->value($sColorValue);

		$oScript = Admin_Form_Entity::factory('Script')
			->value("$('.colorpicker').each(function () {
				$(this).minicolors({
					control: $(this).attr('data-control') || 'hue',
					defaultValue: $(this).attr('data-defaultValue') || '',
					inline: $(this).attr('data-inline') === 'true',
					letterCase: $(this).attr('data-letterCase') || 'lowercase',
					opacity: $(this).attr('data-opacity'),
					position: $(this).attr('data-position') || 'bottom left',
					change: function (hex, opacity) {
						if (!hex) return;
						if (opacity) hex += ', ' + opacity;
						try {
							console.log(hex);
						} catch (e) { }
					},
					theme: 'bootstrap'
				});
			});"
		);

		$oMainTab
			->move($this->getField('color')->set('data-control', 'hue')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('apply_max_discount')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3)
			->add($oScript);

		$title = $this->_object->id
			? Core::_('Shop_Discountcard_Level.edit_title', $this->_object->name)
			: Core::_('Shop_Discountcard_Level.add_title');

		$this->title($title);

		return $this;
	}
}