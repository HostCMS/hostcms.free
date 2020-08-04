<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Comment Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Comment
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			->wysiwyg(Core::moduleIsActive('wysiwyg'))
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
				->id('object_siteuser_id')
				->options($options)
				->name('siteuser_id')
				->class('siteuser-tag')
				->style('width: 100%')
				->divAttr(array('class' => 'form-group col-xs-12'));

			$oMainRow2
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12 col-sm-4 no-padding')
						->add($oSiteuserSelect)
				);

			// Show button
			Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
		}

		$oMainTab
			->move($this->getField('email')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('phone')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow3)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4 margin-top-21')), $oMainRow3);

		$oMainTab->delete($this->getField('ip'));

		$oIp = Admin_Form_Entity::factory('Input')
			->caption(Core::_('Comment.ip'))
			->name('ip')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
			->value($this->_object->ip)
			->format(array('maxlen' => array('value' => 46), 'lib' => array('value' => 'ip')));

		$oMainRow4->add($oIp);

		// Show button
		Ipaddress_Controller_Edit::addBlockButton($oIp, $this->_object->ip, Core::_('Comment.ban_comment', $this->_object->subject), $this->_Admin_Form_Controller);

		$oMainTab->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow4);

		$oMainTab
			->delete($this->getField('grade'));

		$aConfig = Comment_Controller::getConfig();

		$oMainRow4->add(
			Admin_Form_Entity::factory('Stars')
				->name('grade')
				->id('grade')
				->caption(Core::_('Comment.grade'))
				->value($this->_object->grade)
				->divAttr(array('class' => 'form-group stars col-xs-12 col-sm-4'))
				->step($aConfig['gradeStep'])
				->stars($aConfig['gradeLimit'])
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