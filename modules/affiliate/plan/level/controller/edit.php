<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Affiliate_Plan_Level Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Affiliate
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Affiliate_Plan_Level_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->affiliate_plan_id = intval(Core_Array::getGet('affiliate_plan_id'));
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab->move($this->getField('level')->class('form-control')->divAttr(array('class' => 'form-group col-xs-3')), $oMainRow1);

		$oMainTab->move($this->getField('percent')->divAttr(array('class' => 'form-group col-xs-3 hidden-1')), $oMainRow1);

		$oMainTab->delete($this->getField('type'));

		$oMainTab->move($this->getField('value')->divAttr(array('class' => 'form-group col-xs-3 hidden-0')), $oMainRow1);

		$oTypeField = Admin_Form_Entity::factory('Select');
		$oTypeField
			->name('type')
			->divAttr(array('class' => 'form-group col-xs-3'))
			->caption(Core::_('Affiliate_Plan_Level.type'))
			->options(array(
				Core::_('Affiliate_Plan_Level.form_edit_affiliate_values_type_percent'),
				Core::_('Affiliate_Plan_Level.form_edit_affiliate_values_type_summ')
			))
			->value($this->_object->type)
			->onchange("radiogroupOnChange('{$windowId}', $(this).val(), [0,1])");

		$oMainRow1->add($oTypeField);

		$oAdditionalTab->delete($this->getField('affiliate_plan_id'));

		$oAffiliatePlanField = Admin_Form_Entity::factory('Select');
		$oAffiliatePlanField
			->name('affiliate_plan_id')
			->caption(Core::_('Affiliate_Plan_Level.affiliate_plan_id'))
			->divAttr(array('class' => 'form-group col-xs-12'))
			->options(
				$this->_fillAffiliatePlans($this->_object->Affiliate_Plan->site_id)
			)
			->value($this->_object->affiliate_plan_id);

		$oMainRow2->add($oAffiliatePlanField);

		// Заголовок формы
		$title = $this->_object->id
			? Core::_('Affiliate_Plan_Level.edit_affiliate_value')
			: Core::_('Affiliate_Plan_Level.add_affiliate_value');

		$this->title($title);

		$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
		$oAdmin_Form_Entity_Code->html(
			"<script>radiogroupOnChange('{$windowId}', " . intval($this->_object->type) . ", [0,1])</script>"
		);

		$oMainTab->add($oAdmin_Form_Entity_Code);

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$level = Core_Array::getPost('level');

			if (strlen($level))
			{
				$affiliate_plan_id = Core_Array::getPost('affiliate_plan_id');

				$oSameAffiliatePlanLevel = Core_Entity::factory('Affiliate_Plan', $affiliate_plan_id)->Affiliate_Plan_Levels->getByLevel($level);

				if (!is_null($oSameAffiliatePlanLevel) && $oSameAffiliatePlanLevel->id != Core_Array::getPost('id'))
				{
					$this->addMessage(
						Core_Message::get(Core::_('Affiliate_Plan_Level.error_level'), 'error')
					);
					return TRUE;
				}
			}
		}

		return parent::execute($operation);
	}

	/**
	 * Fill affiliate plans list
	 * @param int $iSiteId site ID
	 * @return array
	 */
	protected function _fillAffiliatePlans($iSiteId)
	{
		$oAffiliatePlan = Core_Entity::factory('Affiliate_Plan');

		$oAffiliatePlan->queryBuilder()
			->where('site_id', '=', $iSiteId)
			->orderBy('name');

		$aAffiliatePlans = $oAffiliatePlan->findAll();

		$aReturn = array();

		foreach ($aAffiliatePlans as $oAffiliatePlan)
		{
			$aReturn[$oAffiliatePlan->id] = $oAffiliatePlan->name;
		}

		return $aReturn;
	}
}