<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo Driver Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Seo_Driver_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('expired_in');

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

		$this->title($this->_object->id
			? Core::_('Seo_Driver.edit_title')
			: Core::_('Seo_Driver.add_title'));

		return $this;
	}
}