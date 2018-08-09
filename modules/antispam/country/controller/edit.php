<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam Country Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Antispam_Country_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->move($this->getField('code')->divAttr(array('class' => 'form-group col-xs-12')), $oMainRow1);

		$aAdmin_Languages = Core_Entity::factory('Admin_Language')->getAllByActive(1);

		foreach ($aAdmin_Languages as $oAdmin_Language)
		{
			$oAntispam_Country_Language = $this->_object->id
				? $this->_object->Antispam_Country_Languages->getByAdmin_language_id($oAdmin_Language->id)
				: NULL;

			$name = !is_null($oAntispam_Country_Language)
				? $oAntispam_Country_Language->name
				: '';

			$oAdmin_Form_Entity_Input_Name = Admin_Form_Entity::factory('Input')
				->name('name_lng_' . $oAdmin_Language->id)
				->caption(
					Core::_('Antispam_Country.form_forms_lng_name')
					. ' (' . htmlspecialchars($oAdmin_Language->shortname) . ')'
				)
				->value($name)
				->class('form-control input-lg');

			$oMainTab
				->add(
					Admin_Form_Entity::factory('Div')->class('row')
						->add($oAdmin_Form_Entity_Input_Name)
				);
		}

		$oMainTab
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->move($this->getField('allow')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow2);

		$this->title(
			$this->_object->id
				? Core::_('Antispam_Country.edit_title')
				: Core::_('Antispam_Country.add_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Antispam_Country_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$aAdmin_Languages = Core_Entity::factory('Admin_Language')->getAllByActive(1);

		foreach ($aAdmin_Languages as $oAdmin_Language)
		{
			$oAntispam_Country_Language = $this->_object->Antispam_Country_Languages->getByAdmin_language_id($oAdmin_Language->id);

			$name = Core_Array::getPost('name_lng_' . $oAdmin_Language->id);

			if (is_null($oAntispam_Country_Language->id))
			{
				$oAntispam_Country_Language = Core_Entity::factory('Antispam_Country_Language');
				$oAntispam_Country_Language->admin_language_id = $oAdmin_Language->id;
				$oAntispam_Country_Language->antispam_country_id = $this->_object->id;
			}

			$oAntispam_Country_Language->name = $name;
			$oAntispam_Country_Language->save();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}