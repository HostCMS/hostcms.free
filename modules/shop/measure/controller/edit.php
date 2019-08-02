<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Measure Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Measure_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$title = $this->_object->id
			? Core::_('Shop_Measure.mesures_edit_form_title', $this->_object->name)
			: Core::_('Shop_Measure.mesures_add_form_title');

		$this->title($title);

		return $this;
	}
}