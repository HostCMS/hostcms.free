<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Bootstrap.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Action_Controller_Type_Edit_Show extends Admin_Form_Action_Controller_Type_Edit_Show
{
	/**
	 * Show edit form
	 * @return boolean
	 */
	public function showEditForm()
	{
		$children = $this->children;
		$Admin_Form_Controller = $this->Admin_Form_Controller;

		ob_start();

		if (count($this->children))
		{
			?><div class="table-toolbar">
				<?php
				foreach ($this->children as $oAdmin_Form_Entity)
				{
					if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus)
					{
						$oAdmin_Form_Entity->execute();
					}
				}
				?>
				<div class="clear"></div>
			</div><?php
		}

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

		// Закладки
		if (count($this->tabs))
		{
			$oAdmin_Form_Entity_Tabs = Admin_Form_Entity::factory('Tabs');
			$oAdmin_Form_Entity_Tabs->formId($this->formId);

			// Все закладки к форме
			$oAdmin_Form_Entity_Form->add(
				$oAdmin_Form_Entity_Tabs
			);

			// Add all tabs to $oAdmin_Form_Entity_Tabs
			foreach ($this->tabs as $oAdmin_Form_Tab_Entity)
			{
				if ($oAdmin_Form_Tab_Entity
					->deleteEmptyItems()
					->getCountChildren() > 0)
				{
					$oAdmin_Form_Entity_Tabs->add(
						$oAdmin_Form_Tab_Entity
					);
				}
			}
		}

		// Кнопки
		!is_null($this->buttons) && $oAdmin_Form_Entity_Form->add(
			$this->_addButtons()
		);

		$oAdmin_Form_Entity_Form->execute();

		return ob_get_clean();
	}

	/**
	 * Add save and apply buttons
	 * @return Admin_Form_Entity_Buttons
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit_Show.onBeforeAddButtons
	 * @hostcms-event Admin_Form_Action_Controller_Type_Edit_Show.onAfterAddButtons
	 */
	protected function _addButtons()
	{
		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit_Show.onBeforeAddButtons', $this);

		if ($this->buttons === TRUE)
		{
			$sOperaion = $this->Admin_Form_Controller->getOperation();
			$sOperaionSufix = $sOperaion == 'modal'
				? 'Modal'
				: '';

			// Кнопки
			$oAdmin_Form_Entity_Buttons = Admin_Form_Entity::factory('Buttons');

			// Кнопка Сохранить
			$oAdmin_Form_Entity_Button_Save = Admin_Form_Entity::factory('Button')
				->name('save')
				->class('btn btn-blue')
				->value(Core::_('admin_form.save'))
				->onclick(
					$this->Admin_Form_Controller->getAdminSendForm(NULL, 'save' . $sOperaionSufix)
				);

			$oAdmin_Form_Entity_Button_Apply = Admin_Form_Entity::factory('Button')
				->name('apply')
				->class('btn btn-palegreen')
				->type('submit')
				->value(Core::_('admin_form.apply'))
				->onclick(
					$this->Admin_Form_Controller->getAdminSendForm(NULL, 'apply' . $sOperaionSufix)
				);

			$oAdmin_Form_Entity_Buttons
				->add($oAdmin_Form_Entity_Button_Save)
				->add($oAdmin_Form_Entity_Button_Apply);
		}
		else
		{
			$oAdmin_Form_Entity_Buttons = $this->buttons;
		}

		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit_Show.onAfterAddButtons', $this, array($oAdmin_Form_Entity_Buttons));

		return $oAdmin_Form_Entity_Buttons;
	}
}