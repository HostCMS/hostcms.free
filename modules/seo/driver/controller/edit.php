<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo Driver Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Seo
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		$title = $this->_object->id
			? Core::_('Seo_Driver.edit_title')
			: Core::_('Seo_Driver.add_title');

		$this->title($title);

		return $this;
	}
}