<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin_Form_Action Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'admin_form_action':
				if (!$object->id)
				{
					$object->admin_form_action_dir_id = Core_Array::getGet('admin_form_action_dir_id');
				}
			break;
			case 'admin_form_action_dir':
				$this
					->addSkipColumn('name');
			break;
		}

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

		$modelName = $this->_object->getModelName();

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

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
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'));

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
					->class('form-control input-lg')
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

		$oAdmin_Word_Value = $this->_object->Admin_Word->getWordByLanguage(CURRENT_LANGUAGE_ID);
		$form_name = $oAdmin_Word_Value ? $oAdmin_Word_Value->name : '';

		switch ($modelName)
		{
			case 'admin_form_action':
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
				$oMainTab->move($this->getField('modal'), $oMainRow5);


				$this->getField('sorting')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$this->getField('dataset')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainTab
					->move($this->getField('sorting'), $oMainRow6)
					->move($this->getField('dataset'), $oMainRow6);

				// Удаляем стандартный <input>
				$oAdditionalTab->delete($this->getField('admin_form_action_dir_id'));

				// Селектор с группой
				$oSelect_Dirs = Admin_Form_Entity::factory('Select');
				$oSelect_Dirs
					->options(
						array(' … ') + $this->fillAdminFormActionDir($this->_object)
					)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->name('admin_form_action_dir_id')
					->value($this->_object->admin_form_action_dir_id)
					->caption(Core::_('Admin_Form_Action.admin_form_action_dir_id'));

				$oMainRow6->add($oSelect_Dirs);

				$oMainTab
					->move($this->getField('confirm'), $oMainRow7);

				$this->title(is_null($this->_object->id)
					? Core::_('Admin_Form_Action.form_add_forms_event_title')
					: Core::_('Admin_Form_Action.form_edit_forms_event_title', $form_name, FALSE)
				);
			break;
			case 'admin_form_action_dir':
				$this->title(is_null($this->_object->id)
					? Core::_('Admin_Form_Action_Dir.form_add_forms_event_title')
					: Core::_('Admin_Form_Action_Dir.form_edit_forms_event_title', $form_name, FALSE)
				);
			break;
		}

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

	/**
	 * Create visual tree of the directories
	 * @param boolean $bExclude exclude group ID
	 * @return array
	 */
	public function fillAdminFormActionDir($oObject, $bExclude = FALSE)
	{
		$aReturn = array();

		$oAdmin_Form_Action_Dirs = Core_Entity::factory('Admin_Form_Action_Dir');
		$oAdmin_Form_Action_Dirs->queryBuilder()
			->where('admin_form_id', '=', $oObject->admin_form_id);

		$aAdmin_Form_Action_Dirs = $oAdmin_Form_Action_Dirs->findAll(FALSE);

		foreach ($aAdmin_Form_Action_Dirs as $oAdmin_Form_Action_Dir)
		{
			if ($bExclude != $oAdmin_Form_Action_Dir->id)
			{
				$aReturn[$oAdmin_Form_Action_Dir->id] = $oAdmin_Form_Action_Dir->getWordName() . ' [' . $oAdmin_Form_Action_Dir->id . ']';
			}
		}

		return $aReturn;
	}
}