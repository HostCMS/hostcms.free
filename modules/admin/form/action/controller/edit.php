<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Action Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('admin_word_id');

		if (!$object->admin_form_id)
		{
			$object->admin_form_id = Core_Array::getGet('admin_form_id', 0);
		}

		parent::setObject($object);

		$oMainTab = $this->getTab('main');

		$oNameTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_('Admin_Form_Action.admin_form_tab_0'))
			->name('Name');

		$this
			->addTabBefore($oNameTab, $oMainTab);

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'));

		// Название и описание для всех языков
		$aAdmin_Languages = Core_Entity::factory('Admin_Language')->findAll();

		if (!empty($aAdmin_Languages))
		{
			foreach ($aAdmin_Languages as $oAdmin_Language)
			{
				$oAdmin_Word_Value = $this->_object->id
					? $this->_object->Admin_Word->getWordByLanguage($oAdmin_Language->id)
					: NULL;

				if ($oAdmin_Word_Value)
				{
					$name = $oAdmin_Word_Value->name;
					$description = $oAdmin_Word_Value->description;
				}
				else
				{
					$name = '';
					$description = '';
				}

				$oAdmin_Form_Entity_Input_Name = Admin_Form_Entity::factory('Input')
					->name('name_lng_' . $oAdmin_Language->id)
					->caption(
						Core::_('Admin_Form_Action.action_lng_name')
						. ' (' . htmlspecialchars($oAdmin_Language->shortname) . ')'
					)
					->value($name)
					->class('form-control')
					->format(
						array(
							'maxlen' => array('value' => 255),
							//'minlen' => array('value' => 1)
						)
					);

				$oAdmin_Form_Entity_Textarea_Description = Admin_Form_Entity::factory('Textarea')
					->name('description_lng_' . $oAdmin_Language->id)
					->caption(
						Core::_('Admin_Form_Action.action_lng_description')
						. ' (' . htmlspecialchars($oAdmin_Language->shortname) . ')'
					)
					->value($description)
					->rows(2);

				$oNameTab
					->add(
						Admin_Form_Entity::factory('Div')
							->class('row')
							->add($oAdmin_Form_Entity_Input_Name)
					)
					->add(
						Admin_Form_Entity::factory('Div')
							->class('row')
							->add($oAdmin_Form_Entity_Textarea_Description)
					);
			}
		}

		$this->getField('name')->class('form-control');
		$oMainTab->move($this->getField('name'), $oMainRow1);

		$this->getField('picture')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$this->getField('icon')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$this->getField('color')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oMainTab
			->move($this->getField('picture'), $oMainRow2)
			->move($this->getField('icon'), $oMainRow2)
			->move($this->getField('color'), $oMainRow2);

		$oMainTab->move($this->getField('single'), $oMainRow3);
		$oMainTab->move($this->getField('group'), $oMainRow4);

		$this->getField('sorting')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$this->getField('dataset')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

		$oMainTab
			->move($this->getField('sorting'), $oMainRow5)
			->move($this->getField('dataset'), $oMainRow5);

		$oMainTab
			->move($this->getField('confirm'), $oMainRow6);

		$oAdmin_Word_Value = $this->_object->Admin_Word->getWordByLanguage(CURRENT_LANGUAGE_ID);
		$form_name = $oAdmin_Word_Value ? $oAdmin_Word_Value->name : '';

		$title = is_null($this->_object->id)
			? Core::_('Admin_Form_Action.form_add_forms_event_title')
			: Core::_('Admin_Form_Action.form_edit_forms_event_title', $form_name);

		$this->title($title);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Admin_Form_Action_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		$aAdmin_Languages = Core_Entity::factory('Admin_Language')->findAll();

		if (!empty($aAdmin_Languages))
		{
			$oAdmin_Form_Action = $this->_object;
			foreach ($aAdmin_Languages as $oAdmin_Language)
			{
				if ($oAdmin_Form_Action->admin_word_id)
				{
					$oAdmin_Word = $oAdmin_Form_Action->Admin_Word;
				}
				else
				{
					$oAdmin_Word = Core_Entity::factory('Admin_Word');
					$oAdmin_Form_Action->add($oAdmin_Word);
				}

				$oAdmin_Word_Value = $oAdmin_Word->getWordByLanguage($oAdmin_Language->id);

				$name = Core_Array::getPost('name_lng_' . $oAdmin_Language->id);
				$description = Core_Array::getPost('description_lng_' . $oAdmin_Language->id);

				if (!$oAdmin_Word_Value)
				{
					$oAdmin_Word_Value = Core_Entity::factory('Admin_Word_Value');
					$oAdmin_Word_Value->admin_language_id = $oAdmin_Language->id;
				}

				$oAdmin_Word_Value->name = $name;
				$oAdmin_Word_Value->description = $description;
				$oAdmin_Word_Value->save();
				$oAdmin_Word->add($oAdmin_Word_Value);
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}