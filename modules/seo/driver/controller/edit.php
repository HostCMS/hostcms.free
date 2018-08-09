<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Seo Driver Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Form
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		$title = $object->id
			? Core::_('Seo_Driver.edit_title')
			: Core::_('Seo_Driver.add_title');

		$this
			->addSkipColumn('expired_in');			
			
		parent::setObject($object);

		$this->title($title);

		return $this;
	}
}