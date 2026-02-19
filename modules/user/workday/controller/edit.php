<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * User_Workday_Controller_Edit
 *
 * @package HostCMS
 * @subpackage User
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class User_Workday_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('sent_request')
			->addSkipColumn('notify_day_end')
			->addSkipColumn('notify_day_expired');

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

		// $windowId = $this->_Admin_Form_Controller->getWindowId();

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('date')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('begin')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('end')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow1)
			->move($this->getField('approved')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21')), $oMainRow1)
			->move($this->getField('reason')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2)
			;

		$this->title($this->_object->id
			? Core::_('User_Workday.edit_title')
			: Core::_('User_Workday.add_title')
		);

		return $this;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return bool
     */
	public function execute($operation = NULL)
	{
		// Всегда id_content
		$sJsRefresh = '<script>
		if ($("#id_content .deals-aggregate-user-info").length && typeof _windowSettings != \'undefined\') {
			$(\'#id_content #refresh-toggler\').click();
		}</script>';

		switch ($operation)
		{
			case 'saveModal':
			case 'applyModal':
				$operation == 'saveModal' && $this->addMessage($sJsRefresh);
				$operation == 'applyModal' && $this->addContent($sJsRefresh);
			break;
			case 'markDeleted':
				$this->_object->markDeleted();
				$this->addMessage($sJsRefresh);
			break;
		}

		return parent::execute($operation);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onBeforeApplyObjectProperty
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit.onAfterApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$oUser = Core_Auth::getCurrentUser();

		$bSelfHead = $oUser->isHeadOfEmployee($oUser);

		return $bSelfHead
			? parent::_applyObjectProperty()
			: $this;
	}
}