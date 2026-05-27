<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Syntaxhighlighter Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Syntaxhighlighter
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Syntaxhighlighter_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
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
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1)
			->move($this->getField('driver')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2)
			->move($this->getField('default')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow3)
			;

		$this->title($this->_object->id
			? Core::_('Syntaxhighlighter.edit_title', $this->_object->name, FALSE)
			: Core::_('Syntaxhighlighter.add_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Syntaxhighlighter_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}