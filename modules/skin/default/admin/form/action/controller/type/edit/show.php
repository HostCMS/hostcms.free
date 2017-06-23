<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Default.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Action_Controller_Type_Edit_Show extends Admin_Form_Action_Controller_Type_Edit_Show
{
	/**
	 * Show edit form
	 * @return boolean
	 */
	public function showEditForm()
	{
		ob_start();

		$children = $this->children;
		$Admin_Form_Controller = $this->Admin_Form_Controller;

		// Заголовок формы добавляется до вывода крошек, которые могут быть добавлены в контроллере
		array_unshift($children,
			Admin_Form_Entity::factory('Title')
				->name($this->title)
			);

		// Форма
		$oAdmin_Form_Entity_Form = $this->form->controller(
			$Admin_Form_Controller
		);

		$oAdmin_Form_Entity_Form
			->id($this->formId)
			->class('adminForm')
			->action(
				$Admin_Form_Controller->getPath()
			);

		foreach ($children as $oAdmin_Form_Entity)
		{
			//$oAdmin_Form_Entity->execute();
			$oAdmin_Form_Entity_Form->add($oAdmin_Form_Entity);
		}

		// Закладки
		$oAdmin_Form_Entity_Tabs = Admin_Form_Entity::factory('Tabs');
		$oAdmin_Form_Entity_Tabs->formId($this->formId);

		// Все закладки к форме
		$oAdmin_Form_Entity_Form->add(
			$oAdmin_Form_Entity_Tabs
		);

		// Add all tabs to $oAdmin_Form_Entity_Tabs
		foreach ($this->tabs as $oAdmin_Form_Tab_Entity)
		{
			if ($oAdmin_Form_Tab_Entity->getCountChildren() > 0)
			{
				$oAdmin_Form_Entity_Tabs->add(
					$oAdmin_Form_Tab_Entity
				);
			}
		}

		// Кнопки
		!is_null($this->buttons) && $oAdmin_Form_Entity_Form->add(
			$this->_addButtons()
		)
		->execute();

		return ob_get_clean();
	}

	/**
	 * Add save and apply buttons
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _addButtons()
	{
		if ($this->buttons === TRUE)
		{
			// Кнопки
			$oAdmin_Form_Entity_Buttons = Admin_Form_Entity::factory('Buttons');

			// Кнопка Сохранить
			$oAdmin_Form_Entity_Button_Save = Admin_Form_Entity::factory('Button')
				->name('save')
				->class('saveButton')
				->value(Core::_('admin_form.save'))
				->onclick(
					$this->Admin_Form_Controller->getAdminSendForm(NULL, 'save')
				);

			$oAdmin_Form_Entity_Button_Apply = Admin_Form_Entity::factory('Button')
				->name('apply')
				->class('applyButton')
				->type('submit')
				->value(Core::_('admin_form.apply'))
				->onclick(
					$this->Admin_Form_Controller->getAdminSendForm(NULL, 'apply')
				);

			$oAdmin_Form_Entity_Buttons
				->add($oAdmin_Form_Entity_Button_Save)
				->add($oAdmin_Form_Entity_Button_Apply);
		}
		else
		{
			$oAdmin_Form_Entity_Buttons = $this->buttons;
		}

		return $oAdmin_Form_Entity_Buttons;
	}
}