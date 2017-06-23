<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam Stopword Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Stopword_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
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
				? Core::_('Antispam_Stopword.edit_title')
				: Core::_('Antispam_Stopword.add_title')
		);

		return $this;
	}
}