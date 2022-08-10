<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Post_Controller_Edit
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Post_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->getField('description')
			->rows(7)
			->wysiwyg(Core::moduleIsActive('wysiwyg'));

		$oMainTab
			->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow2)
			->move($this->getField('sorting')->divAttr(array('class' => 'form-group col-xs-12 col-md-3')), $oMainRow3);

		$title = $this->_object->id
			? Core::_('Company_Post.edit_title')
			: Core::_('Company_Post.add_title');

		$this->title($title);

		return $this;
	}
}