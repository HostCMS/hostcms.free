<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Ipaddress_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$this->title(
			$this->_object->id
				? Core::_('Ipaddress.edit_title')
				: Core::_('Ipaddress.add_title')
		);

		$this->getField('ip')
			// clear standart url pattern
			->format(array('lib' => array()));

		return $this;
	}
}