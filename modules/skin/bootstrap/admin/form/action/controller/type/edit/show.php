<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Bootstrap.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		/*if ($this->buttons === TRUE)
		{
			// перенесено в Admin_Form_Action_Controller_Type_Edit
		}
		else
		{*/
			$oAdmin_Form_Entity_Buttons = $this->buttons;
		//}

		Core_Event::notify('Admin_Form_Action_Controller_Type_Edit_Show.onAfterAddButtons', $this, array($oAdmin_Form_Entity_Buttons));

		return $oAdmin_Form_Entity_Buttons;
	}
}