<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Measure Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Measure_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$this->title($this->_object->id
			? Core::_('Shop_Measure.mesures_edit_form_title', $this->_object->name, FALSE)
			: Core::_('Shop_Measure.mesures_add_form_title')
		);

		return $this;
	}
}