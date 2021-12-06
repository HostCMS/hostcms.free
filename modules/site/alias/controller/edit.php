<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site_Alias Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Site
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'));

		$oMainTab
			->move($this->getField('name'), $oMainRow1)
			->move($this->getField('current'), $oMainRow2)
			->move($this->getField('redirect'), $oMainRow3);

		if (!$this->_object->id)
		{
			if (!$this->_object->Site->Site_Aliases->getCount())
			{
				$this->getField('current')->checked(TRUE);
			}

			$oGetKey = Admin_Form_Entity::factory('Checkbox')
				->name('get_key')
				->divAttr(array('class' => 'form-group col-xs-12'))
				->caption(Core::_('Site_Alias.get_key'))
				->value(1)
				->checked(TRUE);

			$oMainRow4->add($oGetKey);
		}

		$this->title($this->_object->id
			? Core::_('Site_Alias.site_edit_domen_form_title', $this->_object->name)
			: Core::_('Site_Alias.site_add_domen_form_title'));

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

		if (!is_null(Core_Array::getPost('get_key')))
		{
			$this->_object->getKey();
			Core_Message::show(Core::_('Site_Alias.getKey_success'), 'info');
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}