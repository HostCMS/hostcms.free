<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Mail Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Mail
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Mail_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		if (!Core::moduleIsActive('lead'))
		{
			$this->addSkipColumn('create_leads');
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

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name'), $oMainRow1)
			->move($this->getField('login')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2);

		$oMainTab->delete($this->getField('password'));

		$oMainRow2->add(
			Admin_Form_Entity::factory('Password')
				->caption(Core::_('Mail.password'))
				->name('password')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->value($this->_object->password)
				->format(
					array(
						'minlen' => array('value' => 1)
					)
				)
		);

		$oMainTab
			->move($this->getField('email')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow2)
			->move($this->getField('imap')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('pop3')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('smtp')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3);

		$oMainTab
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4)
			->move($this->getField('ssl')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow5)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow6)
			->move($this->getField('default')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow7);

		$oMainTab->delete($this->getField('lead_days'));

		if (Core::moduleIsActive('lead'))
		{
			$oCRMTab = Admin_Form_Entity::factory('Tab')
				->caption(Core::_('Mail.tab_crm'))
				->name('CRM');

			$oCRMTab
				->add($oCRMTabRow1 = Admin_Form_Entity::factory('Div')->class('row'));

			$this
				->addTabAfter($oCRMTab, $oMainTab);

			$oMainTab->move($this->getField('create_leads')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oCRMTabRow1);

			$oCRMTabRow1->add(
				Admin_Form_Entity::factory('Code')
					->html('
						<div class="form-group col-xs-12 col-sm-4">' . Core::_('Mail.process_mails') . ' <a href="#" id="lead_days" data-type="select" data-url="" data-value="' . $this->_object->lead_days . '">' . $this->_object->lead_days . ' </a> ' . Core::_('Mail.days') . '</div>
						<script>
							$.fn.editable.defaults.mode = "inline";
							$(document).ready(function() {
								$("#lead_days").editable({
									showbuttons: false,
									source: [
										{value: 1, text: 1},
										{value: 3, text: 3},
										{value: 5, text: 5},
										{value: 7, text: 7},
										{value: 10, text: 10},
										{value: 14, text: 14}
									],
									success: function(response, newValue) {
										$(".lead-days").val(newValue);
									}
								});
							});
						</script>
				')
			);

			$oAdditionalTab->delete($this->getField('crm_source_id'));

			$aMasCrmSources = array(array('value' => Core::_('Admin.none'), 'color' => '#aebec4'));

			if (Core::moduleIsActive('crm'))
			{
				$aCrm_Sources = Core_Entity::factory('Crm_Source')->findAll();
				foreach ($aCrm_Sources as $oCrm_Source)
				{
					$aMasCrmSources[$oCrm_Source->id] = array(
						'value' => $oCrm_Source->name,
						'color' => $oCrm_Source->color,
						'icon' => $oCrm_Source->icon
					);
				}
			}

			$oDropdownlistCrmSources = Admin_Form_Entity::factory('Dropdownlist')
				->options($aMasCrmSources)
				->name('crm_source_id')
				->value($this->_object->crm_source_id)
				->caption(Core::_('Mail.crm_source_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

			$oCRMTabRow1
				->add($oDropdownlistCrmSources);

			$oLeadDaysInputHidden = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->class('lead-days')
				->name('lead_days')
				->value($this->_object->lead_days)
				->type('hidden');

			$oCRMTabRow1->add($oLeadDaysInputHidden);
		}

		$this->title($this->_object->id
			? Core::_('Mail.edit_title', $this->_object->name, FALSE)
			: Core::_('Mail.add_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Mail_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 * @return self
	 */
	protected function _applyObjectProperty()
	{
		$this->_formValues['lead_days'] = Core_Array::getPost('lead_days', 0, 'int');

		parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Fill mails list
	 * @return array
	 */
	public function fillMails()
	{
		$aReturn = array();

		$oMails = Core_Entity::factory('Mail');
		$oMails->queryBuilder()
			->where('mails.site_id', '=', CURRENT_SITE)
			// ->where('mails.active', '=', 1)
			;

		$aMails = $oMails->findAll(FALSE);

		foreach ($aMails as $oMail)
		{
			$aReturn[$oMail->id] = $oMail->name;
		}

		return $aReturn;
	}
}