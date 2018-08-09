<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Comment_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
			$object->parent_id = intval(Core_Array::getGet('parent_id'));
		}

		parent::setObject($object);

		return $this;
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		$modelName = $object->getModelName();

		$this->title(
			$this->_object->id
				? Core::_('Comment.edit_title', $this->_object->subject)
				: Core::_('Comment.add_title')
			);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$this->getField('text')
			->wysiwyg(TRUE)
			->rows(10)
			->divAttr(array('class' => 'form-group col-xs-12'));

		$oMainTab->move($this->getField('text'), $oMainRow1);

		$oMainTab->move($this->getField('author')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-8')), $oMainRow2);

		$oAdditionalTab->delete($this->getField('siteuser_id'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = $this->_object->Siteuser;

			$options = !is_null($oSiteuser->id)
				? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
				: array(0);

			$oSiteuserSelect = Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Order.siteuser_id'))
				->options($options)
				->name('siteuser_id')
				->class('siteuser-tag')
				->style('width: 100%')
				->divAttr(array('class' => 'form-group col-xs-6 col-sm-3 no-padding-right'));

			$oMainRow2->add($oSiteuserSelect);

			// Show button
			Siteuser_Controller_Edit::addSiteuserSelect2($oMainRow2, $oSiteuser, $this->_Admin_Form_Controller);
		}

		$oMainTab->move($this->getField('email')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3);
		$oMainTab->move($this->getField('phone')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3);
		$oMainTab->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow3);
		$oMainTab->move($this->getField('ip')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4);
		$oMainTab->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4);

		$oMainTab
			->delete($this->getField('grade'));

		$oMainRow4->add(
			Admin_Form_Entity::factory('Stars')
				->name('grade')
				->id('grade')
				->caption(Core::_('Comment.grade'))
				->value($this->_object->grade)
				->divAttr(array('class' => 'form-group stars col-xs-12 col-sm-4'))
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 */
	protected function _applyObjectProperty()
	{
		$this->_formValues['siteuser_id'] = intval(Core_Array::get($this->_formValues, 'siteuser_id'));

		parent::_applyObjectProperty();

		// Informationsystem_Item_Comment_Controller_Edit + Shop_Item_Comment_Controller_Edit Clears Cache

		return $this;
	}
}