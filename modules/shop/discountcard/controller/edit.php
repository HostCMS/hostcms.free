<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discountcard_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			// $object->number = $object->generate();
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->getField('number')
			->format(
				array(
					'minlen' => array('value' => 0)
				)
			);

		$oMainTab
			->move($this->getField('number')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-lg-3')), $oMainRow2);

		$oMainTab->delete($this->getField('amount'));

		$oShop_Currency = $this->_object->Shop->Shop_Currency;

		$oMainRow2->add(
			Admin_Form_Entity::factory('Input')
				->caption(Core::_('Shop_Discountcard.amount', htmlspecialchars($oShop_Currency->name)))
				->name('amount')
				->value($this->_object->amount)
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-lg-3'))
		);

		$oAdditionalTab->delete($this->getField('siteuser_id'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = $this->_object->Siteuser;

			$options = !is_null($oSiteuser->id)
				? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
				: array(0);

			$oSiteuserSelect = Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Discountcard.siteuser_id'))
				->id('object_siteuser_id')
				->options($options)
				->name('siteuser_id')
				->class('siteuser-tag')
				->style('width: 100%')
				->divAttr(array('class' => 'form-group col-xs-12'));

			$oMainRow2
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12 col-sm-6 col-lg-3 no-padding')
						->add($oSiteuserSelect)
				);

			// Show button
			Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
		}

		$oAdditionalTab->delete($this->getField('shop_discountcard_level_id'));

		$oMainRow2->add(
			Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Discountcard.shop_discountcard_level_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-lg-3'))
				->options($this->fillLevels($this->_object->Shop))
				->name('shop_discountcard_level_id')
				->value($this->_object->shop_discountcard_level_id)
		);

		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

		$title = $this->_object->id
			? Core::_('Shop_Discountcard.edit_title', $this->_object->number)
			: Core::_('Shop_Discountcard.add_title');

		$this->title($title);

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return mixed
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$siteuser_id = Core_Array::get($this->_formValues, 'siteuser_id', 0, 'int');

			if ($siteuser_id)
			{
				$oSiteuser = Core_Entity::factory('Siteuser')->getById($siteuser_id);

				if (!is_null($oSiteuser) && $oSiteuser->Shop_Discountcards->getCount(FALSE))
				{
					Core_Message::show(Core::_('Shop_Discountcard.card_already_exist'), 'error');

					return TRUE;
				}
			}
		}

		return parent::execute($operation);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Discountcard_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->_formValues['siteuser_id'] = Core_Array::get($this->_formValues, 'siteuser_id', 0, 'int');

		$bSiteuser = $this->_object->siteuser_id == 0;

		parent::_applyObjectProperty();

		$bSiteuser && $this->_object->setSiteuserAmount()->save();
		$this->_object->checkLevel();

		if (!strlen($this->_object->number))
		{
			$this->_object->number = $this->_object->generate();
			$this->_object->save();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Get shop discount card levels array
	 * @return array
	 */
	public function fillLevels(Shop_Model $oShop)
	{
		$oShop_Discountcard_Levels = Core_Entity::factory('Shop_Discountcard_Level');
		$oShop_Discountcard_Levels->queryBuilder()
			->where('shop_discountcard_levels.shop_id', '=', $oShop->id)
			->clearOrderBy()
			->orderBy('level');

		$aShop_Discountcard_Levels = $oShop_Discountcard_Levels->findAll();

		$aReturn = array('...');

		foreach ($aShop_Discountcard_Levels as $oShop_Discountcard_Level)
		{
			$aReturn[$oShop_Discountcard_Level->id] = htmlspecialchars($oShop_Discountcard_Level->name);
		}

		return $aReturn;
	}
}