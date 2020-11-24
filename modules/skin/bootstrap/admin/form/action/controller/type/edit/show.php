<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Bootstrap.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Action_Controller_Type_Edit_Show extends Admin_Form_Action_Controller_Type_Edit_Show
{
	/**
	 * Check if $value is instance of Skin_Bootstrap_Admin_Form_Entity_Menus
	 * @return boolean
	 */
	static protected function _justMenus($value)
	{
		return $value instanceof Skin_Bootstrap_Admin_Form_Entity_Menus;
	}

	/**
	 * Show edit form
	 * @return boolean
	 */
	public function showEditForm()
	{
		$children = $this->children;

		ob_start();

		$aMenus = array_filter($this->children, array(__CLASS__, '_justMenus'));
		if (count($aMenus))
		{
			?><div class="table-toolbar">
				<?php
				foreach ($aMenus as $oAdmin_Form_Entity)
				{
					//if ($oAdmin_Form_Entity instanceof Skin_Bootstrap_Admin_Form_Entity_Menus)
					//{
						$oAdmin_Form_Entity->execute();
					//}
				}
				?>
				<div class="clear"></div>
			</div><?php
		}

		// Форма
		$this->_Admin_Form_Entity_Form
			->controller($this->Admin_Form_Controller)
			->class('adminForm')
			//->action($this->Admin_Form_Controller->getPath());
			// сломает редактирование
			->action($this->Admin_Form_Controller->getAdminLoadHref($this->Admin_Form_Controller->getPath()));

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
				->value(Core::_('Admin_Form.save'))
				->onclick(
					$this->Admin_Form_Controller->getAdminSendForm(NULL, 'save' . $sOperaionSufix)
				);

			$oAdmin_Form_Entity_Button_Apply = Admin_Form_Entity::factory('Button')
				->name('apply')
				->class('btn btn-palegreen')
				->type('submit')
				->value(Core::_('Admin_Form.apply'))
				->onclick(
					$this->Admin_Form_Controller->getAdminSendForm(NULL, 'apply' . $sOperaionSufix)
				);

			$oAdmin_Form_Entity_Buttons
				->add($oAdmin_Form_Entity_Button_Save)
				->add($oAdmin_Form_Entity_Button_Apply);

			$aChecked = $this->Admin_Form_Controller->getChecked();
			$aFirst = reset($aChecked);

			if (is_array($aFirst) && key($aFirst))
			{
				/*if ($sOperaion == 'modal')
				{
					$windowId = $this->Admin_Form_Controller->getWindowId();
					$modalJs = "$('#{$windowId}').parents('.bootbox').modal('hide');";
				}
				else
				{
					$modalJs = '';
				}*/

				$oAdmin_Form_Entity_Button_Delete = Admin_Form_Entity::factory('A')
					->class('btn btn-darkorange pull-right')
					->onclick("res = confirm('" . Core::_('Admin_Form.confirm_dialog', Core::_('Admin_Form.delete')) . "'); if (res) {"
						. $this->Admin_Form_Controller->getAdminSendForm('markDeleted', '') . " } else { return false }
					")
					->add(
						Admin_Form_Entity::factory('Code')
							->html('<i class="fa fa-trash no-margin-right"></i>')
					);

				$oAdmin_Form_Entity_Buttons->add($oAdmin_Form_Entity_Button_Delete);
			}

			$path = $this->Admin_Form_Controller->getPath();

			$oAdmin_Form_Entity_Button_Cancel = Admin_Form_Entity::factory('A')
				->class('btn btn-default pull-right margin-right-5')
				->onclick($this->Admin_Form_Controller->getAdminLoadAjax($path))
				->add(
					Admin_Form_Entity::factory('Code')
						->html('<i class="fa fa-arrow-circle-left no-margin-right darkgray"></i>')
				);

			$oAdmin_Form_Entity_Buttons->add($oAdmin_Form_Entity_Button_Cancel);
		}
		else
		{
			$oAdmin_Form_Entity_Buttons = $this->buttons;
		}

		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit_Show.onAfterAddButtons', $this, array($oAdmin_Form_Entity_Buttons));

		return $oAdmin_Form_Entity_Buttons;
	}
}