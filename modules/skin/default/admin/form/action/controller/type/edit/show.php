<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Default.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		// Заголовок формы добавляется до вывода крошек, которые могут быть добавлены в контроллере
		/*array_unshift($children,
			Admin_Form_Entity::factory('Title')
				->name($this->title)
			);*/

		// Форма
		$this->_Admin_Form_Entity_Form
			->controller($this->Admin_Form_Controller)
			->class('adminForm')
			->action(
				$this->Admin_Form_Controller->getPath()
			);

		foreach ($children as $oAdmin_Form_Entity)
		{
			$this->_Admin_Form_Entity_Form->add($oAdmin_Form_Entity);
		}

		// Закладки Admin_Form_Entity_Tabs
		if (!is_null($this->tabs))
		{
			// Все закладки к форме
			$this->_Admin_Form_Entity_Form->add($this->tabs);
		}

		// Кнопки
		!is_null($this->buttons) && $this->_Admin_Form_Entity_Form->add(
			$this->_addButtons()
		);

		$this->_Admin_Form_Entity_Form->execute();

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
					$this->Admin_Form_Controller->getAdminSendForm(array('operation' => 'save'))
				);

			$oAdmin_Form_Entity_Button_Apply = Admin_Form_Entity::factory('Button')
				->name('apply')
				->class('applyButton')
				->type('submit')
				->value(Core::_('admin_form.apply'))
				->onclick(
					$this->Admin_Form_Controller->getAdminSendForm(array('operation' => 'apply'))
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