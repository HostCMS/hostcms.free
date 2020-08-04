<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site_Alias Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Site_Alias_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		// При добавлении объекта
		if (!$object->id)
		{
			$object->site_id = Core_Array::getGet('site_id');
		}

		parent::setObject($object);

		$this->title($this->_object->id
			? Core::_('Site_Alias.site_edit_domen_form_title', $this->_object->name)
			: Core::_('Site_Alias.site_add_domen_form_title'));

		if (!$this->_object->id)
		{
			if (!$this->_object->Site->Site_Aliases->getCount())
			{
				$this->getField('current')->checked(TRUE);
			}
		}

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields
	 * @hostcms-event Site_Alias_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		isset($this->_formValues['name'])
			&& $this->_formValues['name'] = str_replace(array('http://', 'https://', '*.'), '*.', trim($this->_formValues['name'], " \t\n\r\0\x0B/"));

		parent::_applyObjectProperty();

		if (preg_match('/[а-яёА-ЯЁ]+/u', $this->_object->name, $matches))
		{
			$this->_object->name = Core_Str::idnToAscii($this->_object->name);
			$this->_object->save();
		}

		if (!is_null(Core_Array::getPost('current')))
		{
			$this->_object->setCurrent();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}