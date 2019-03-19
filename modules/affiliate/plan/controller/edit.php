<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Affiliate_Plan Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Affiliate
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Affiliate_Plan_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->site_id = CURRENT_SITE;
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'));

		$oAdditionalTab = $this->getTab('additional');

		$oAdditionalTab->delete($this->getField('site_id'));

		$oAdditionalTab->delete($this->getField('siteuser_group_id'));

		$Site_Controller_Edit = new Site_Controller_Edit($this->_Admin_Form_Action);

		$oSiteField = Admin_Form_Entity::factory('Select')
			->name('site_id')
			->caption(Core::_('Affiliate_Plan.site_id'))
			->options($Site_Controller_Edit->fillSites())
			->value($this->_object->site_id);

		$oMainTab->move($this->getField('description'), $oMainRow1);
		$oMainRow2->add($oSiteField);

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
			$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups(CURRENT_SITE);
		}
		else
		{
			$aSiteuser_Groups = array();
		}

		$oSiteUserGroupField = Admin_Form_Entity::factory('Select')
			->name('siteuser_group_id')
			->caption(Core::_('Affiliate_Plan.siteuser_group_id'))
			->options($aSiteuser_Groups)
			->value($this->_object->siteuser_group_id)
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

		$oMainRow3->add($oSiteUserGroupField);
		$oMainTab->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow3);
		
		$oMainTab->move($this->getField('min_count_of_items')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow4);
		$oMainTab->move($this->getField('min_amount_of_items')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow4);
		
		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow5);
		$oMainTab->move($this->getField('include_delivery')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')), $oMainRow5);

		// Заголовок формы
		$title = $this->_object->id
			? Core::_('Affiliate_Plan.affiliate_form_edit')
			: Core::_('Affiliate_Plan.affiliate_form_add');

		$this->title($title);

		return $this;
	}
}