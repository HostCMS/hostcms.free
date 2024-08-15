<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Antispam Stopword Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Antispam
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Antispam_Stopword_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$oMainTab = $this->getTab('main');

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			;

		if (!$this->_object->id)
		{
			// Удаляем стандартный <input>
			$oMainTab->delete($this->getField('value'));

			$oTextarea = Admin_Form_Entity::factory('Textarea')
				->cols(140)
				->rows(5)
				->caption(Core::_('Antispam_Stopword.values'))
				->divAttr(array('class' => 'form-group col-xs-12'))
				->name('value');

			$oMainRow1->add($oTextarea);
		}

		$oMainTab->delete($this->getField('type'));

		$oSelect_Type = Admin_Form_Entity::factory('Select')
			->options(
				array(
					0 => Core::_('Antispam_Stopword.type0'),
					1 => Core::_('Antispam_Stopword.type1')
				)
			)
			->name('type')
			->value($this->_object->type)
			->caption(Core::_('Antispam_Stopword.type'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3'));

		$oMainRow2->add($oSelect_Type);

		$oMainTab
			->move($this->getField('case_sensitive')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21')), $oMainRow2)
			->move($this->getField('active')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow3);

		$this->title(
			$this->_object->id
				? Core::_('Antispam_Stopword.edit_title', $this->_object->value, FALSE)
				: Core::_('Antispam_Stopword.add_title')
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Antispam_Stopword_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$id = $this->_object->id;

		if (!$id)
		{
			$sValue = trim(Core_Array::getPost('value'));

			// Массив значений списка
			$aStopWords = explode("\n", $sValue);

			foreach ($aStopWords as $sValue)
			{
				$sValue = trim($sValue);

				$oSame_Antispam_Stopword = Core_Entity::factory('Antispam_Stopword')->getByValue($sValue, FALSE);

				if (is_null($oSame_Antispam_Stopword))
				{
					$oNew_StopWord = Core_Entity::factory('Antispam_Stopword');
					$oNew_StopWord->value = $sValue;
					$oNew_StopWord->save();
				}
			}
		}
		else
		{
			parent::_applyObjectProperty();
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}
}